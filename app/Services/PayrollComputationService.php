<?php

namespace App\Services;

use App\Events\PayrollComputed;
use App\Events\PayrollApproved;
use App\Events\PayrollReleased;
use App\Models\AuditLog;
use App\Models\CompanySetting;
use App\Models\DailyTimeRecord;
use App\Models\Holiday;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\LeaveAutomationService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Enhanced Payroll Computation Service
 * 
 * Integrates with DailyTimeRecord for accurate payroll computation:
 * - Uses approved DTRs as source of truth
 * - Calculates premiums for holiday/overtime work
 * - Handles deductions for late/undertime
 * - Generates detailed payroll breakdown
 */
class PayrollComputationService
{
    protected array $settings = [];

    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Load payroll settings
     */
    protected function loadSettings(): void
    {
        $this->settings = [
            'overtime_multiplier' => (float) CompanySetting::getValue('overtime_multiplier', 1.25),
            'night_diff_multiplier' => (float) CompanySetting::getValue('night_diff_multiplier', 1.10),
            'holiday_multiplier' => (float) CompanySetting::getValue('holiday_multiplier', 2.0),
            'special_holiday_multiplier' => (float) CompanySetting::getValue('special_holiday_multiplier', 1.30),
            'rest_day_multiplier' => (float) CompanySetting::getValue('rest_day_multiplier', 1.30),
            'late_deduction_enabled' => CompanySetting::getValue('late_deduction_enabled', true),
            'undertime_deduction_enabled' => CompanySetting::getValue('undertime_deduction_enabled', true),
            'standard_work_hours' => (int) CompanySetting::getValue('standard_work_minutes', 480) / 60,
            'include_government_deductions' => CompanySetting::getValue('include_government_deductions', true),
        ];
    }

    /**
     * Compute payroll for a single employee using DTR data
     */
    public function computeFromDtr(User $user, PayrollPeriod $period): array
    {
        try {
            DB::beginTransaction();

            // Get approved DTRs for the period
            $dtrs = DailyTimeRecord::where('user_id', $user->id)
                ->where('payroll_period_id', $period->id)
                ->where('status', 'approved')
                ->orderBy('date')
                ->get();

            if ($dtrs->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No approved DTRs found for this period',
                ];
            }

            // Calculate metrics from DTRs
            $metrics = $this->calculateDtrMetrics($dtrs);

            // Get pay rates
            $rates = $this->getPayRates($user, $period);

            // Calculate earnings
            $earnings = $this->calculateEarnings($user, $metrics, $rates, $period);

            // Calculate deductions
            $deductions = $this->calculateDeductions($user, $metrics, $rates, $earnings, $period);

            // Calculate totals
            $grossPay = $earnings['basic_pay'] + 
                       $earnings['overtime_pay'] + 
                       $earnings['holiday_pay'] + 
                       $earnings['night_diff_pay'] +
                       $earnings['rest_day_pay'] +
                       $earnings['allowances'] +
                       $earnings['bonuses'];

            $totalDeductions = $deductions['sss'] + 
                              $deductions['philhealth'] + 
                              $deductions['pagibig'] + 
                              $deductions['tax'] + 
                              $deductions['late'] + 
                              $deductions['undertime'] + 
                              $deductions['absent'] + 
                              $deductions['leave_without_pay'] +
                              $deductions['loans'] +
                              $deductions['other'];

            $netPay = $grossPay - $totalDeductions;

            // Create/Update payroll record
            $payroll = Payroll::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'payroll_period_id' => $period->id,
                ],
                [
                    // Attendance Metrics
                    'total_work_days' => $metrics['work_days'],
                    'total_work_minutes' => $metrics['total_work_minutes'],
                    'total_overtime_minutes' => $metrics['overtime_minutes'],
                    'total_undertime_minutes' => $metrics['undertime_minutes'],
                    'total_late_minutes' => $metrics['late_minutes'],
                    'total_absent_days' => $metrics['absent_days'],
                    
                    // Earnings
                    'basic_pay' => round($earnings['basic_pay'], 2),
                    'overtime_pay' => round($earnings['overtime_pay'], 2),
                    'holiday_pay' => round($earnings['holiday_pay'], 2),
                    'night_diff_pay' => round($earnings['night_diff_pay'], 2),
                    'rest_day_pay' => round($earnings['rest_day_pay'], 2),
                    'bonus' => round($earnings['bonuses'], 2),
                    'allowances' => round($earnings['allowances'], 2),
                    'gross_pay' => round($grossPay, 2),
                    
                    // Government Deductions
                    'sss_contribution' => round($deductions['sss'], 2),
                    'philhealth_contribution' => round($deductions['philhealth'], 2),
                    'pagibig_contribution' => round($deductions['pagibig'], 2),
                    'withholding_tax' => round($deductions['tax'], 2),
                    
                    // Other Deductions
                    'late_deductions' => round($deductions['late'], 2),
                    'undertime_deductions' => round($deductions['undertime'], 2),
                    'absent_deductions' => round($deductions['absent'], 2),
                    'loan_deductions' => round($deductions['loans'], 2),
                    'leave_without_pay_deductions' => round($deductions['leave_without_pay'], 2),
                    'other_deductions' => round($deductions['other'], 2),
                    'total_deductions' => round($totalDeductions, 2),
                    
                    // Net Pay
                    'net_pay' => round($netPay, 2),
                    
                    // Status & Metadata
                    'status' => 'computed',
                    'computed_at' => now(),
                    'computed_by' => auth()->id(),
                    'computation_source' => 'dtr',
                    'is_manually_adjusted' => false,
                    'adjustment_reason' => null,
                    'adjusted_by' => null,
                    'adjusted_at' => null,
                    'computation_details' => json_encode([
                        'metrics' => $metrics,
                        'rates' => $rates,
                        'earnings_breakdown' => $earnings,
                        'deductions_breakdown' => $deductions,
                    ]),
                ]
            );

            // Log computation
            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'payroll_computed',
                'model_type' => 'Payroll',
                'model_id' => $payroll->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'gross_pay' => $grossPay,
                    'net_pay' => $netPay,
                    'source' => 'dtr',
                ]),
                'ip_address' => request()->ip() ?? 'system',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            DB::commit();

            // Fire event
            event(new PayrollComputed($payroll));

            Log::channel('payroll')->info('Payroll computed from DTR', [
                'payroll_id' => $payroll->id,
                'user_id' => $user->id,
                'period_id' => $period->id,
                'gross_pay' => $grossPay,
                'net_pay' => $netPay,
            ]);

            return [
                'success' => true,
                'payroll' => $payroll,
                'breakdown' => [
                    'metrics' => $metrics,
                    'earnings' => $earnings,
                    'deductions' => $deductions,
                    'gross_pay' => round($grossPay, 2),
                    'total_deductions' => round($totalDeductions, 2),
                    'net_pay' => round($netPay, 2),
                ],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('payroll')->error('Payroll computation failed', [
                'user_id' => $user->id,
                'period_id' => $period->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Computation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Bulk compute payroll for period
     */
    public function computePayrollForPeriod(PayrollPeriod $period, ?array $userIds = null): array
    {
        $results = [
            'computed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        // Get employees
        $query = User::where('is_active', true)
            ->whereIn('role', ['employee', 'hr']);

        if ($userIds) {
            $query->whereIn('id', $userIds);
        }

        $employees = $query->get();

        foreach ($employees as $employee) {
            // Check if all DTRs are approved
            $pendingDtrs = DailyTimeRecord::where('user_id', $employee->id)
                ->where('payroll_period_id', $period->id)
                ->whereIn('status', ['draft', 'pending', 'correction_pending'])
                ->count();

            if ($pendingDtrs > 0) {
                $results['skipped']++;
                $results['errors'][$employee->id] = 'Has pending DTRs';
                continue;
            }

            $result = $this->computeFromDtr($employee, $period);

            if ($result['success']) {
                $results['computed']++;
            } else {
                $results['failed']++;
                $results['errors'][$employee->id] = $result['message'];
            }
        }

        // Update period status
        if ($results['computed'] > 0) {
            $period->update([
                'status' => 'processing',
                'payroll_computed_at' => now(),
            ]);
        }

        return $results;
    }

    /**
     * Calculate metrics from DTR collection
     */
    protected function calculateDtrMetrics(Collection $dtrs): array
    {
        return [
            'work_days' => $dtrs->whereIn('attendance_status', ['present', 'late', 'half_day'])->count(),
            'present_days' => $dtrs->where('attendance_status', 'present')->count(),
            'late_days' => $dtrs->where('attendance_status', 'late')->count(),
            'absent_days' => $dtrs->where('attendance_status', 'absent')->count(),
            'leave_days' => $dtrs->where('attendance_status', 'on_leave')->count(),
            'holiday_days' => $dtrs->where('day_type', 'holiday')->count(),
            'special_holiday_days' => $dtrs->where('day_type', 'special_holiday')->count(),
            'rest_day_worked' => $dtrs->where('day_type', 'rest_day')
                ->where('attendance_status', 'present')->count(),
            'total_work_minutes' => $dtrs->sum('total_work_minutes'),
            'total_hours_worked' => $dtrs->sum('total_hours_worked'),
            'late_minutes' => $dtrs->sum('late_minutes'),
            'undertime_minutes' => $dtrs->sum('undertime_minutes'),
            'overtime_minutes' => $dtrs->sum('overtime_minutes'),
            'night_diff_minutes' => $dtrs->sum('night_diff_minutes') ?? 0,
            'total_break_minutes' => $dtrs->sum('total_break_minutes'),
        ];
    }

    /**
     * Get pay rates for employee
     */
    protected function getPayRates(User $user, PayrollPeriod $period): array
    {
        $monthlyRate = $user->monthly_salary ?? 0;
        $dailyRate = $user->daily_rate > 0 ? $user->daily_rate : ($monthlyRate / 22);
        $hourlyRate = $user->hourly_rate > 0 ? $user->hourly_rate : ($dailyRate / $this->settings['standard_work_hours']);
        $minuteRate = $hourlyRate / 60;

        return [
            'monthly' => $monthlyRate,
            'daily' => round($dailyRate, 2),
            'hourly' => round($hourlyRate, 2),
            'minute' => round($minuteRate, 4),
            'overtime' => round($hourlyRate * $this->settings['overtime_multiplier'], 2),
            'night_diff' => round($hourlyRate * $this->settings['night_diff_multiplier'], 2),
            'holiday' => round($dailyRate * $this->settings['holiday_multiplier'], 2),
            'special_holiday' => round($dailyRate * $this->settings['special_holiday_multiplier'], 2),
            'rest_day' => round($dailyRate * $this->settings['rest_day_multiplier'], 2),
        ];
    }

    /**
     * Calculate earnings breakdown
     */
    protected function calculateEarnings(User $user, array $metrics, array $rates, PayrollPeriod $period): array
    {
        // Basic pay based on period type
        if ($period->period_type === 'monthly') {
            $basicPay = $rates['monthly'];
        } elseif ($period->period_type === 'semi_monthly') {
            $basicPay = $rates['monthly'] / 2;
        } else {
            $basicPay = $rates['daily'] * $metrics['work_days'];
        }

        // Overtime pay
        $overtimePay = ($metrics['overtime_minutes'] / 60) * $rates['overtime'];

        // Holiday pay (premium for working on holidays)
        $holidayPay = $metrics['holiday_days'] * ($rates['holiday'] - $rates['daily']);

        // Special holiday pay
        $specialHolidayPay = $metrics['special_holiday_days'] * ($rates['special_holiday'] - $rates['daily']);
        $holidayPay += $specialHolidayPay;

        // Night differential
        $nightDiffPay = ($metrics['night_diff_minutes'] / 60) * ($rates['night_diff'] - $rates['hourly']);

        // Rest day premium
        $restDayPay = $metrics['rest_day_worked'] * ($rates['rest_day'] - $rates['daily']);

        // Allowances (from user profile)
        $allowances = $user->meal_allowance ?? 0;
        $allowances += $user->transportation_allowance ?? 0;
        $allowances += $user->communication_allowance ?? 0;

        // Bonuses (check for any active bonuses)
        $bonuses = $this->calculateBonuses($user, $period);

        return [
            'basic_pay' => $basicPay,
            'overtime_pay' => $overtimePay,
            'holiday_pay' => $holidayPay,
            'night_diff_pay' => $nightDiffPay,
            'rest_day_pay' => $restDayPay,
            'allowances' => $allowances,
            'bonuses' => $bonuses,
        ];
    }

    /**
     * Calculate bonuses for the period
     */
    protected function calculateBonuses(User $user, PayrollPeriod $period): float
    {
        // Check employee_transactions for bonuses in this period
        $bonuses = \App\Models\EmployeeTransaction::where('user_id', $user->id)
            ->where('type', 'bonus')
            ->where('status', 'approved')
            ->whereBetween('effective_date', [$period->start_date, $period->end_date])
            ->sum('amount');

        return $bonuses;
    }

    /**
     * Calculate deductions breakdown
     */
    protected function calculateDeductions(User $user, array $metrics, array $rates, array $earnings, PayrollPeriod $period): array
    {
        // Estimate monthly for government deductions
        $monthlyGross = $period->period_type === 'monthly' 
            ? $earnings['basic_pay'] 
            : $earnings['basic_pay'] * 2;

        // Government deductions (if enabled)
        $sss = 0;
        $philhealth = 0;
        $pagibig = 0;
        $tax = 0;

        if ($this->settings['include_government_deductions']) {
            $sss = $this->calculateSSS($monthlyGross);
            $philhealth = $this->calculatePhilHealth($monthlyGross);
            $pagibig = $this->calculatePagIBIG($monthlyGross);
            
            $taxableIncome = $monthlyGross - $sss - $philhealth - $pagibig;
            $tax = $this->calculateWithholdingTax($taxableIncome);

            // For semi-monthly, divide by 2
            if ($period->period_type === 'semi_monthly') {
                $sss /= 2;
                $philhealth /= 2;
                $pagibig /= 2;
                $tax /= 2;
            }
        }

        // Late deduction
        $lateDeduction = 0;
        if ($this->settings['late_deduction_enabled'] && $metrics['late_minutes'] > 0) {
            $lateDeduction = $metrics['late_minutes'] * $rates['minute'];
        }

        // Undertime deduction
        $undertimeDeduction = 0;
        if ($this->settings['undertime_deduction_enabled'] && $metrics['undertime_minutes'] > 0) {
            $undertimeDeduction = $metrics['undertime_minutes'] * $rates['minute'];
        }

        // Absent deduction
        $absentDeduction = $metrics['absent_days'] * $rates['daily'];

        // Leave Without Pay deduction (using LeaveAutomationService)
        $leaveWithoutPayDeduction = 0;
        try {
            $leaveService = app(LeaveAutomationService::class);
            $leaveDeductions = $leaveService->calculateLeaveDeductions(
                $user, 
                Carbon::parse($period->start_date), 
                Carbon::parse($period->end_date)
            );
            $leaveWithoutPayDeduction = $leaveDeductions['leave_without_pay_amount'];
        } catch (\Exception $e) {
            Log::channel('payroll')->warning('Failed to calculate leave deductions', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Loan deductions
        $loanDeductions = $this->calculateLoanDeductions($user, $period);

        // Other deductions from transactions
        $otherDeductions = \App\Models\EmployeeTransaction::where('user_id', $user->id)
            ->where('type', 'deduction')
            ->where('status', 'approved')
            ->whereBetween('effective_date', [$period->start_date, $period->end_date])
            ->sum('amount');

        return [
            'sss' => max(0, $sss),
            'philhealth' => max(0, $philhealth),
            'pagibig' => max(0, $pagibig),
            'tax' => max(0, $tax),
            'late' => $lateDeduction,
            'undertime' => $undertimeDeduction,
            'absent' => $absentDeduction,
            'leave_without_pay' => $leaveWithoutPayDeduction,
            'loans' => $loanDeductions,
            'other' => $otherDeductions,
        ];
    }

    /**
     * Calculate loan deductions due this period
     */
    protected function calculateLoanDeductions(User $user, PayrollPeriod $period): float
    {
        $loans = \App\Models\Loan::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('remaining_balance', '>', 0)
            ->get();

        $totalDeduction = 0;

        foreach ($loans as $loan) {
            // Check if payment is due this period
            if ($loan->payment_start_date <= $period->end_date) {
                $totalDeduction += $loan->monthly_amortization ?? 0;
            }
        }

        return $totalDeduction;
    }

    /**
     * SSS Contribution calculation (2024 rates)
     */
    protected function calculateSSS(float $monthlyGross): float
    {
        // Simplified - using bracket approach
        $brackets = [
            4250 => 180, 4750 => 202.50, 5250 => 225, 5750 => 247.50,
            6250 => 270, 6750 => 292.50, 7250 => 315, 7750 => 337.50,
            8250 => 360, 8750 => 382.50, 9250 => 405, 9750 => 427.50,
            10250 => 450, 10750 => 472.50, 11250 => 495, 11750 => 517.50,
            12250 => 540, 12750 => 562.50, 13250 => 585, 13750 => 607.50,
            14250 => 630, 14750 => 652.50, 15250 => 675, 15750 => 697.50,
            16250 => 720, 16750 => 742.50, 17250 => 765, 17750 => 787.50,
            18250 => 810, 18750 => 832.50, 19250 => 855, 19750 => 877.50,
            20250 => 900, 20750 => 922.50, 21250 => 945, 21750 => 967.50,
            22250 => 990, 22750 => 1012.50, 23250 => 1035, 23750 => 1057.50,
            24250 => 1080, 24750 => 1102.50, 25250 => 1125, 25750 => 1147.50,
            26250 => 1170, 26750 => 1192.50, 27250 => 1215, 27750 => 1237.50,
            28250 => 1260, 28750 => 1282.50, 29250 => 1305, 29750 => 1327.50,
        ];

        foreach ($brackets as $ceiling => $contribution) {
            if ($monthlyGross <= $ceiling) {
                return $contribution;
            }
        }

        return 1350; // Maximum
    }

    /**
     * PhilHealth Contribution (2024)
     */
    protected function calculatePhilHealth(float $monthlyGross): float
    {
        $basis = max(10000, min(100000, $monthlyGross));
        return $basis * 0.025; // 2.5% employee share
    }

    /**
     * Pag-IBIG Contribution
     */
    protected function calculatePagIBIG(float $monthlyGross): float
    {
        if ($monthlyGross <= 1500) {
            return $monthlyGross * 0.01;
        }
        return min(100, $monthlyGross * 0.02);
    }

    /**
     * Withholding Tax (BIR 2024)
     */
    protected function calculateWithholdingTax(float $taxableIncome): float
    {
        if ($taxableIncome <= 20833) return 0;
        if ($taxableIncome <= 33332) return ($taxableIncome - 20833) * 0.15;
        if ($taxableIncome <= 66666) return 1875 + ($taxableIncome - 33333) * 0.20;
        if ($taxableIncome <= 166666) return 8541.80 + ($taxableIncome - 66667) * 0.25;
        if ($taxableIncome <= 666666) return 33541.80 + ($taxableIncome - 166667) * 0.30;
        return 183541.80 + ($taxableIncome - 666667) * 0.35;
    }

    /**
     * Approve payroll
     */
    public function approvePayroll(Payroll $payroll, int $approvedBy): array
    {
        try {
            $payroll->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            event(new PayrollApproved($payroll, $approvedBy));

            return ['success' => true, 'message' => 'Payroll approved'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Release payroll
     */
    public function releasePayroll(Payroll $payroll, int $releasedBy): array
    {
        try {
            $payroll->update([
                'status' => 'released',
                'released_by' => $releasedBy,
                'released_at' => now(),
            ]);

            event(new PayrollReleased($payroll, $releasedBy));

            // Create notification for employee
            \App\Models\Notification::create([
                'user_id' => $payroll->user_id,
                'type' => 'payroll_released',
                'title' => 'Payslip Available',
                'message' => sprintf(
                    'Your payslip for the period ending %s is now available.',
                    $payroll->payrollPeriod->end_date->format('M d, Y')
                ),
                'data' => json_encode(['payroll_id' => $payroll->id]),
            ]);

            return ['success' => true, 'message' => 'Payroll released'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Bulk approve payrolls for period
     */
    public function approvePayrollsForPeriod(PayrollPeriod $period, int $approvedBy): array
    {
        $payrolls = Payroll::where('payroll_period_id', $period->id)
            ->where('status', 'computed')
            ->get();

        $results = ['approved' => 0, 'failed' => 0];

        foreach ($payrolls as $payroll) {
            $result = $this->approvePayroll($payroll, $approvedBy);
            if ($result['success']) {
                $results['approved']++;
            } else {
                $results['failed']++;
            }
        }

        if ($results['failed'] === 0 && $results['approved'] > 0) {
            $period->update(['status' => 'approved']);
        }

        return $results;
    }

    /**
     * Bulk release payrolls for period
     */
    public function releasePayrollsForPeriod(PayrollPeriod $period, int $releasedBy): array
    {
        $payrolls = Payroll::where('payroll_period_id', $period->id)
            ->where('status', 'approved')
            ->get();

        $results = ['released' => 0, 'failed' => 0];

        foreach ($payrolls as $payroll) {
            $result = $this->releasePayroll($payroll, $releasedBy);
            if ($result['success']) {
                $results['released']++;
            } else {
                $results['failed']++;
            }
        }

        if ($results['failed'] === 0 && $results['released'] > 0) {
            $period->update(['status' => 'completed']);
        }

        return $results;
    }

    /**
     * Get payroll summary for period
     */
    public function getPeriodSummary(PayrollPeriod $period): array
    {
        $payrolls = Payroll::where('payroll_period_id', $period->id)->get();

        return [
            'total_employees' => $payrolls->count(),
            'total_gross_pay' => $payrolls->sum('gross_pay'),
            'total_net_pay' => $payrolls->sum('net_pay'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_sss' => $payrolls->sum('sss_contribution'),
            'total_philhealth' => $payrolls->sum('philhealth_contribution'),
            'total_pagibig' => $payrolls->sum('pagibig_contribution'),
            'total_tax' => $payrolls->sum('withholding_tax'),
            'total_late_deductions' => $payrolls->sum('late_deductions'),
            'total_undertime_deductions' => $payrolls->sum('undertime_deductions'),
            'total_absent_deductions' => $payrolls->sum('absent_deductions'),
            'status_breakdown' => [
                'computed' => $payrolls->where('status', 'computed')->count(),
                'approved' => $payrolls->where('status', 'approved')->count(),
                'released' => $payrolls->where('status', 'released')->count(),
            ],
        ];
    }
}
