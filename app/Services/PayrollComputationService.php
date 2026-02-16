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
use Illuminate\Support\Facades\Cache;

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
    public function computeFromDtr(User $user, PayrollPeriod $period, ?Collection $dtrs = null, ?Collection $loans = null, ?Collection $transactions = null, bool $manualMode = false): array
    {
        try {
            DB::beginTransaction();

            // Get approved DTRs for the period if not provided
            if ($dtrs === null) {
                $dtrs = DailyTimeRecord::where('user_id', $user->id)
                    ->where('payroll_period_id', $period->id)
                    ->where('status', 'approved')
                    ->orderBy('date')
                    ->get();
            }

            // Calculate metrics from DTRs (even if empty, will return 0s)
            $metrics = $this->calculateDtrMetrics($dtrs);

            // Get pay rates
            $rates = $this->getPayRates($user, $period);

            if ($manualMode) {
                 // In manual mode, we init everything to zero
                 $earnings = [
                     'basic_pay' => 0,
                     'overtime_pay' => 0,
                     'holiday_pay' => 0,
                     'night_diff_pay' => 0,
                     'rest_day_pay' => 0,
                     'allowances' => 0,
                     'bonuses' => 0,
                 ];

                 $deductions = [
                     'sss' => 0,
                     'philhealth' => 0,
                     'pagibig' => 0,
                     'tax' => 0,
                     'late' => 0,
                     'undertime' => 0,
                     'absent' => 0,
                     'leave_without_pay' => 0,
                     'loans' => 0,
                     'other' => 0,
                 ];

                 $grossPay = 0;
                 $totalDeductions = 0;
                 $netPay = 0;

            } else {
                // Calculate earnings
                $earnings = $this->calculateEarnings($user, $metrics, $rates, $period, $transactions);
                
                // ... (rest of logic)
                // Apply "No Work, No Pay" rule
                if ($metrics['work_days'] <= 0 && $metrics['overtime_minutes'] <= 0 && $metrics['holiday_days'] <= 0) {
                    $earnings['basic_pay'] = 0;
                    $earnings['allowances'] = 0;
                    $earnings['bonuses'] = 0;
                    $earnings['holiday_pay'] = 0;
                    $earnings['night_diff_pay'] = 0;
                    $earnings['rest_day_pay'] = 0;
                }

                // Calculate deductions
                $deductions = $this->calculateDeductions($user, $metrics, $rates, $earnings, $period, $loans, $transactions);

                // If earnings are zero, zero out mandatory government deductions for this period
                if (($earnings['basic_pay'] <= 0 && $earnings['overtime_pay'] <= 0)) {
                    $deductions['sss'] = 0;
                    $deductions['philhealth'] = 0;
                    $deductions['pagibig'] = 0;
                    $deductions['tax'] = 0;
                }
                
                // RULE: If Weekly, suppress government deductions by default (assume 4th week processing or manual)
                // Unless explicitly enabled via global setting or flag
                if ($period->period_type === 'weekly') {
                     $deductions['sss'] = 0;
                     $deductions['philhealth'] = 0;
                     $deductions['pagibig'] = 0;
                     // Only keep Tax if taxable > 5000/week (approx) but usually 0 for weekly minimum wage
                     // Let's keep tax logic as is, it has brackets starting at 20k monthly (~5k weekly)
                }

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
            }

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
                'new_values' => [
                    'gross_pay' => $grossPay,
                    'net_pay' => $netPay,
                    'source' => 'dtr',
                ],
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
    public function computePayrollForPeriod(PayrollPeriod $period, ?array $userIds = null, bool $manualMode = false): array
    {
        // Reset progress
        Cache::put("payroll_progress_{$period->id}", [
            'status' => 'processing',
            'percentage' => 0,
            'current' => 0,
            'total' => 0,
            'message' => 'Initializing...'
        ], 3600);

        $results = [
            'computed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
            'success' => [],
        ];

        // Get employees
        $query = User::where('is_active', true)
            ->whereIn('role', ['employee', 'hr', 'admin', 'super_admin']);
            
        // Filter by Payroll Group if defined
        if ($period->payroll_group_id) {
            $query->where('payroll_group_id', $period->payroll_group_id);
        } else {
            $query->whereNull('payroll_group_id');
        }

        if ($userIds) {
            $query->whereIn('id', $userIds);
        }

        $employees = $query->get();
        $totalEmployees = $employees->count(); // Total count for progress
        
        Cache::put("payroll_progress_{$period->id}", [
            'status' => 'processing',
            'percentage' => 0,
            'current' => 0,
            'total' => $totalEmployees,
            'message' => 'Fetching data...'
        ], 3600);
        
        $employeeIds = $employees->pluck('id');

        // Optimized: Batch fetch all necessary data once
        $pendingDtrCounts = DailyTimeRecord::where('payroll_period_id', $period->id)
            ->whereIn('user_id', $employeeIds)
            ->whereIn('status', ['draft', 'pending', 'correction_pending'])
            ->select('user_id', DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->pluck('count', 'user_id');

        $allApprovedDtrs = DailyTimeRecord::where('payroll_period_id', $period->id)
            ->whereIn('user_id', $employeeIds)
            ->where('status', 'approved')
            ->orderBy('date')
            ->get()
            ->groupBy('user_id');

        $allLoans = \App\Models\Loan::whereIn('user_id', $employeeIds)
            ->where('status', 'approved')
            ->where('remaining_balance', '>', 0)
            ->get()
            ->groupBy('user_id');

        $allTransactions = \App\Models\EmployeeTransaction::whereIn('user_id', $employeeIds)
            ->where('status', 'approved')
            ->whereBetween('effective_date', [$period->start_date, $period->end_date])
            ->get()
            ->groupBy('user_id');

        $processedCount = 0;

        foreach ($employees as $employee) {
            $processedCount++;
            
            // Update Progress in Cache (every 5 employees or so to reduce cache writes)
            if ($processedCount % 1 == 0 || $processedCount == $totalEmployees) {
                 $percentage = ($processedCount / $totalEmployees) * 100;
                 Cache::put("payroll_progress_{$period->id}", [
                    'status' => 'processing',
                    'percentage' => round($percentage),
                    'current' => $processedCount,
                    'total' => $totalEmployees,
                    'message' => "Processing {$employee->name}..."
                ], 3600);
            }

            $pendingCount = $pendingDtrCounts[$employee->id] ?? 0;

            // In Manual Mode, ignore pending DTR checks
            if (!$manualMode && $pendingCount > 0) {
                $results['skipped']++;
                $results['errors'][$employee->id] = 'Has pending DTRs';
                continue;
            }

            // Pass pre-fetched data to optimize performance
            $result = $this->computeFromDtr(
                $employee, 
                $period, 
                $allApprovedDtrs->get($employee->id, new Collection()),
                $allLoans->get($employee->id, new Collection()),
                $allTransactions->get($employee->id, new Collection()),
                $manualMode
            );

            if ($result['success']) {
                $results['computed']++;
                $results['success'][] = $employee->id;
            } else {
                $results['failed']++;
                $results['errors'][$employee->id] = $result['message'];
            }
        }

        // Finalize Progress
        Cache::put("payroll_progress_{$period->id}", [
            'status' => 'completed',
            'percentage' => 100,
            'current' => $totalEmployees,
            'total' => $totalEmployees,
            'message' => 'Completed!'
        ], 3600);

        // Update period status
        if ($results['computed'] > 0) {
            $period->update([
                'status' => 'completed',
                'payroll_computed_at' => now(),
            ]);
        } else {
             // If nothing computed, reset to draft
             $period->update(['status' => 'draft']);
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
    protected function calculateEarnings(User $user, array $metrics, array $rates, PayrollPeriod $period, ?Collection $transactions = null): array
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

        // Custom Incentives from Employee Profile
        // Site Incentive (Daily)
        if ($user->site_incentive > 0) {
            $allowances += ($user->site_incentive * $metrics['work_days']);
        }

        // Attendance Incentive (Daily)
        if ($user->attendance_incentive > 0) {
            $allowances += ($user->attendance_incentive * $metrics['work_days']);
        }

        // COLA (Daily)
        if ($user->cola > 0) {
            $allowances += ($user->cola * $metrics['work_days']);
        }

        // Other Allowance (Flat)
        $allowances += $user->other_allowance ?? 0;

        // Perfect Attendance Bonus Logic (Flat)
        if ($metrics['absent_days'] == 0 && $metrics['late_minutes'] == 0 && $metrics['undertime_minutes'] == 0 && $metrics['work_days'] > 0) {
            $allowances += $user->perfect_attendance_bonus ?? 0;
        }

        // Bonuses (check for any active bonuses)
        $bonuses = $this->calculateBonuses($user, $period, $transactions);

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
    protected function calculateBonuses(User $user, PayrollPeriod $period, ?Collection $transactions = null): float
    {
        // Use pre-fetched transactions if provided
        if ($transactions !== null) {
            return $transactions->where('type', 'bonus')->sum('amount');
        }

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
    protected function calculateDeductions(User $user, array $metrics, array $rates, array $earnings, PayrollPeriod $period, ?Collection $loans = null, ?Collection $transactions = null): array
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
        $loanDeductions = $this->calculateLoanDeductions($user, $period, $loans);

        // Other deductions from transactions
        if ($transactions !== null) {
            $otherDeductions = $transactions->where('type', 'deduction')->sum('amount');
        } else {
            $otherDeductions = \App\Models\EmployeeTransaction::where('user_id', $user->id)
                ->where('type', 'deduction')
                ->where('status', 'approved')
                ->whereBetween('effective_date', [$period->start_date, $period->end_date])
                ->sum('amount');
        }

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
    protected function calculateLoanDeductions(User $user, PayrollPeriod $period, ?Collection $loans = null): float
    {
        if ($loans === null) {
            $loans = \App\Models\Loan::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('remaining_balance', '>', 0)
                ->get();
        }

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
    public function approvePayroll(Payroll $payroll, int $approvedById): array
    {
        try {
            $approver = User::findOrFail($approvedById);
            
            // Hierarchy Check
            if (!$approver->isSuperAdmin() && !$approver->canManage($payroll->user)) {
                return [
                    'success' => false, 
                    'message' => 'Hierarchy Restriction: You cannot approve payroll for users with equal or higher rank.'
                ];
            }

            $payroll->update([
                'status' => 'approved',
                'approved_by' => $approvedById,
                'approved_at' => now(),
            ]);

            event(new PayrollApproved($payroll, $approvedById));

            return ['success' => true, 'message' => 'Payroll approved'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Release payroll
     */
    public function releasePayroll(Payroll $payroll, int $releasedById): array
    {
        try {
            $releaser = User::findOrFail($releasedById);
            
            // Hierarchy Check
            if (!$releaser->isSuperAdmin() && !$releaser->canManage($payroll->user)) {
                return [
                    'success' => false, 
                    'message' => 'Hierarchy Restriction: You cannot release payroll for users with equal or higher rank.'
                ];
            }

            $payroll->update([
                'status' => 'released',
                'released_by' => $releasedById,
                'released_at' => now(),
            ]);

            event(new PayrollReleased($payroll, $releasedById));

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

        $results = ['success' => 0, 'failed' => 0];

        foreach ($payrolls as $payroll) {
            $result = $this->approvePayroll($payroll, $approvedBy);
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        if ($results['failed'] === 0 && $results['success'] > 0) {
            // Keep status as processing until released (completed)
            $period->touch();
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

    /**
     * Complete payroll period (Compatibility with PayrollService)
     */
    public function completePayrollPeriod(PayrollPeriod $period): void
    {
        $period->update(['status' => 'completed']);
        
        // Mark all payrolls as approved if they aren't already
        Payroll::where('payroll_period_id', $period->id)
            ->where('status', 'computed')
            ->update(['status' => 'approved']);

        // Log action
        \App\Models\AuditLog::create([
            'user_id' => auth()->id() ?? null,
            'action' => 'payroll_period_completed',
            'model_type' => 'PayrollPeriod',
            'model_id' => $period->id,
            'new_values' => ['status' => 'completed'],
            'ip_address' => request()->ip() ?? 'system',
            'user_agent' => request()->userAgent() ?? 'System',
        ]);
    }
}
