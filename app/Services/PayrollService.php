<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Models\CompanySetting;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Compute payroll for a single employee
     */
    public function computePayroll(User $user, PayrollPeriod $period): Payroll
    {
        // Get attendance records for the period
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->get();

        // Calculate attendance metrics
        $metrics = $this->calculateAttendanceMetrics($attendances, $period);

        // Calculate earnings
        $earnings = $this->calculateEarnings($user, $metrics, $period);

        // Calculate deductions
        $deductions = $this->calculateDeductions($user, $metrics, $earnings);

        // Calculate net pay
        $grossPay = $earnings['basic_pay'] + $earnings['overtime_pay'] + 
                   $earnings['holiday_pay'] + $earnings['allowances'];
        
        $totalDeductions = $deductions['sss'] + $deductions['philhealth'] + 
                          $deductions['pagibig'] + $deductions['tax'] + 
                          $deductions['late'] + $deductions['undertime'] + 
                          $deductions['absent'] + $deductions['other'];
        
        $netPay = $grossPay - $totalDeductions;

        // Create or update payroll record
        return Payroll::updateOrCreate(
            [
                'user_id' => $user->id,
                'payroll_period_id' => $period->id,
            ],
            [
                'total_work_days' => $metrics['work_days'],
                'total_work_minutes' => $metrics['work_minutes'],
                'total_overtime_minutes' => $metrics['overtime_minutes'],
                'total_undertime_minutes' => $metrics['undertime_minutes'],
                'total_late_minutes' => $metrics['late_minutes'],
                'total_absent_days' => $metrics['absent_days'],
                'basic_pay' => $earnings['basic_pay'],
                'overtime_pay' => $earnings['overtime_pay'],
                'holiday_pay' => $earnings['holiday_pay'],
                'allowances' => $earnings['allowances'],
                'gross_pay' => $grossPay,
                'sss_contribution' => $deductions['sss'],
                'philhealth_contribution' => $deductions['philhealth'],
                'pagibig_contribution' => $deductions['pagibig'],
                'withholding_tax' => $deductions['tax'],
                'late_deductions' => $deductions['late'],
                'undertime_deductions' => $deductions['undertime'],
                'absent_deductions' => $deductions['absent'],
                'other_deductions' => $deductions['other'],
                'total_deductions' => $totalDeductions,
                'net_pay' => $netPay,
                'status' => 'computed',
            ]
        );
    }

    /**
     * Process payroll for all active employees
     */
    public function processPayrollPeriod(PayrollPeriod $period): array
    {
        $employees = User::where('is_active', true)
            ->where('role', 'employee')
            ->get();

        $results = [
            'success' => [],
            'failed' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                try {
                    $payroll = $this->computePayroll($employee, $period);
                    $results['success'][] = [
                        'employee' => $employee->name,
                        'net_pay' => $payroll->net_pay,
                    ];
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'employee' => $employee->name,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $period->update([
                'status' => 'processing',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            AuditLog::log(
                'payroll_processed',
                PayrollPeriod::class,
                $period->id,
                null,
                ['status' => 'processing', 'employees_processed' => count($results['success'])],
                'Payroll period processed'
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Calculate attendance metrics
     */
    protected function calculateAttendanceMetrics($attendances, PayrollPeriod $period): array
    {
        $workDays = $attendances->whereIn('status', ['present', 'late', 'half_day'])->count();
        $workMinutes = $attendances->sum('total_work_minutes');
        $overtimeMinutes = $attendances->sum('overtime_minutes');
        $undertimeMinutes = $attendances->sum('undertime_minutes');
        $absentDays = $attendances->where('status', 'absent')->count();

        // Calculate late minutes 
        $lateMinutes = 0;
        $workStartTime = CompanySetting::getValue('work_start_time', '21:00');

        foreach ($attendances->where('status', 'late') as $attendance) {
            if ($attendance->time_in) {
                $expectedTimeIn = $attendance->date->copy()->setTimeFromTimeString($workStartTime);
                if ($attendance->time_in->gt($expectedTimeIn)) {
                    $lateMinutes += $expectedTimeIn->diffInMinutes($attendance->time_in);
                }
            }
        }

        // Calculate expected work days in period (excluding weekends)
        $expectedDays = 0;
        $currentDate = $period->start_date->copy();
        while ($currentDate <= $period->end_date) {
            if (!$currentDate->isWeekend()) {
                $expectedDays++;
            }
            $currentDate->addDay();
        }

        // Calculate absent days (expected - actual work days)
        $absentDays = max(0, $expectedDays - $workDays - $attendances->where('status', 'on_leave')->count());

        return [
            'work_days' => $workDays,
            'work_minutes' => $workMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'late_minutes' => $lateMinutes,
            'absent_days' => $absentDays,
            'expected_days' => $expectedDays,
        ];
    }

    /**
     * Calculate earnings
     */
    protected function calculateEarnings(User $user, array $metrics, PayrollPeriod $period): array
    {
        // Get pay rates
        $dailyRate = $user->daily_rate > 0 ? $user->daily_rate : ($user->monthly_salary / 22);
        $hourlyRate = $user->hourly_rate > 0 ? $user->hourly_rate : ($dailyRate / 8);
        $overtimeRate = $hourlyRate * 1.25; // 125% for regular overtime

        // Calculate basic pay based on period type
        if ($period->period_type === 'monthly') {
            $basicPay = $user->monthly_salary;
        } elseif ($period->period_type === 'semi_monthly') {
            $basicPay = $user->monthly_salary / 2;
        } else {
            // Weekly or daily based
            $basicPay = $dailyRate * $metrics['work_days'];
        }

        // Calculate overtime pay
        $overtimePay = ($metrics['overtime_minutes'] / 60) * $overtimeRate;

        // Holiday pay (simplified - would need a holidays table for accurate calculation)
        $holidayPay = 0;

        // Allowances (could be fetched from employee profile or settings)
        $allowances = 0;

        return [
            'basic_pay' => round($basicPay, 2),
            'overtime_pay' => round($overtimePay, 2),
            'holiday_pay' => round($holidayPay, 2),
            'allowances' => round($allowances, 2),
            'daily_rate' => $dailyRate,
            'hourly_rate' => $hourlyRate,
        ];
    }

    /**
     * Calculate deductions
     */
    protected function calculateDeductions(User $user, array $metrics, array $earnings): array
    {
        $monthlyGross = $earnings['basic_pay'] * 2; // For semi-monthly, estimate monthly

        // SSS Contribution (2024 rates - simplified)
        $sss = $this->calculateSSS($monthlyGross);

        // PhilHealth Contribution (2024 rate: 5% split between employer/employee)
        $philhealth = $this->calculatePhilHealth($monthlyGross);

        // Pag-IBIG Contribution
        $pagibig = $this->calculatePagIBIG($monthlyGross);

        // Withholding Tax (simplified BIR tax table)
        $taxableIncome = $monthlyGross - $sss - $philhealth - $pagibig;
        $tax = $this->calculateWithholdingTax($taxableIncome);

        // Late deductions (per minute rate)
        $hourlyRate = $earnings['hourly_rate'];
        $minuteRate = $hourlyRate / 60;
        $lateDeduction = $metrics['late_minutes'] * $minuteRate;

        // Undertime deductions
        $undertimeDeduction = $metrics['undertime_minutes'] * $minuteRate;

        // Absent deductions
        $absentDeduction = $metrics['absent_days'] * $earnings['daily_rate'];

        // For semi-monthly payroll, divide government deductions by 2
        $sss = $sss / 2;
        $philhealth = $philhealth / 2;
        $pagibig = $pagibig / 2;
        $tax = $tax / 2;

        return [
            'sss' => round($sss, 2),
            'philhealth' => round($philhealth, 2),
            'pagibig' => round($pagibig, 2),
            'tax' => round(max(0, $tax), 2),
            'late' => round($lateDeduction, 2),
            'undertime' => round($undertimeDeduction, 2),
            'absent' => round($absentDeduction, 2),
            'other' => 0,
        ];
    }

    /**
     * Calculate SSS contribution (2024 rates - employee share)
     */
    protected function calculateSSS(float $monthlyGross): float
    {
        // Simplified SSS table
        if ($monthlyGross <= 4250) return 180;
        if ($monthlyGross <= 4749.99) return 202.50;
        if ($monthlyGross <= 5249.99) return 225;
        if ($monthlyGross <= 5749.99) return 247.50;
        if ($monthlyGross <= 6249.99) return 270;
        if ($monthlyGross <= 6749.99) return 292.50;
        if ($monthlyGross <= 7249.99) return 315;
        if ($monthlyGross <= 7749.99) return 337.50;
        if ($monthlyGross <= 8249.99) return 360;
        if ($monthlyGross <= 8749.99) return 382.50;
        if ($monthlyGross <= 9249.99) return 405;
        if ($monthlyGross <= 9749.99) return 427.50;
        if ($monthlyGross <= 10249.99) return 450;
        if ($monthlyGross <= 10749.99) return 472.50;
        if ($monthlyGross <= 11249.99) return 495;
        if ($monthlyGross <= 11749.99) return 517.50;
        if ($monthlyGross <= 12249.99) return 540;
        if ($monthlyGross <= 12749.99) return 562.50;
        if ($monthlyGross <= 13249.99) return 585;
        if ($monthlyGross <= 13749.99) return 607.50;
        if ($monthlyGross <= 14249.99) return 630;
        if ($monthlyGross <= 14749.99) return 652.50;
        if ($monthlyGross <= 15249.99) return 675;
        if ($monthlyGross <= 15749.99) return 697.50;
        if ($monthlyGross <= 16249.99) return 720;
        if ($monthlyGross <= 16749.99) return 742.50;
        if ($monthlyGross <= 17249.99) return 765;
        if ($monthlyGross <= 17749.99) return 787.50;
        if ($monthlyGross <= 18249.99) return 810;
        if ($monthlyGross <= 18749.99) return 832.50;
        if ($monthlyGross <= 19249.99) return 855;
        if ($monthlyGross <= 19749.99) return 877.50;
        if ($monthlyGross <= 20249.99) return 900;
        if ($monthlyGross <= 20749.99) return 922.50;
        if ($monthlyGross <= 21249.99) return 945;
        if ($monthlyGross <= 21749.99) return 967.50;
        if ($monthlyGross <= 22249.99) return 990;
        if ($monthlyGross <= 22749.99) return 1012.50;
        if ($monthlyGross <= 23249.99) return 1035;
        if ($monthlyGross <= 23749.99) return 1057.50;
        if ($monthlyGross <= 24249.99) return 1080;
        if ($monthlyGross <= 24749.99) return 1102.50;
        if ($monthlyGross <= 25249.99) return 1125;
        if ($monthlyGross <= 25749.99) return 1147.50;
        if ($monthlyGross <= 26249.99) return 1170;
        if ($monthlyGross <= 26749.99) return 1192.50;
        if ($monthlyGross <= 27249.99) return 1215;
        if ($monthlyGross <= 27749.99) return 1237.50;
        if ($monthlyGross <= 28249.99) return 1260;
        if ($monthlyGross <= 28749.99) return 1282.50;
        if ($monthlyGross <= 29249.99) return 1305;
        if ($monthlyGross <= 29749.99) return 1327.50;
        
        return 1350; // Maximum
    }

    /**
     * Calculate PhilHealth contribution (employee share - 2024)
     */
    protected function calculatePhilHealth(float $monthlyGross): float
    {
        // 5% total contribution rate, 2.5% employee share
        // Minimum: 10,000 | Maximum: 100,000
        $basis = max(10000, min(100000, $monthlyGross));
        return $basis * 0.025;
    }

    /**
     * Calculate Pag-IBIG contribution (employee share)
     */
    protected function calculatePagIBIG(float $monthlyGross): float
    {
        if ($monthlyGross <= 1500) {
            return $monthlyGross * 0.01;
        }
        
        // 2% for income above 1500, max of 100
        return min(100, $monthlyGross * 0.02);
    }

    /**
     * Calculate withholding tax (BIR 2024 rates)
     */
    protected function calculateWithholdingTax(float $taxableIncome): float
    {
        // Monthly tax table for individuals
        if ($taxableIncome <= 20833) return 0;
        if ($taxableIncome <= 33332) return ($taxableIncome - 20833) * 0.15;
        if ($taxableIncome <= 66666) return 1875 + ($taxableIncome - 33333) * 0.20;
        if ($taxableIncome <= 166666) return 8541.80 + ($taxableIncome - 66667) * 0.25;
        if ($taxableIncome <= 666666) return 33541.80 + ($taxableIncome - 166667) * 0.30;
        
        return 183541.80 + ($taxableIncome - 666667) * 0.35;
    }

    /**
     * Mark payroll period as completed
     */
    public function completePayrollPeriod(PayrollPeriod $period): void
    {
        DB::beginTransaction();
        try {
            // Update all payrolls in this period to approved
            Payroll::where('payroll_period_id', $period->id)
                ->update(['status' => 'approved']);

            $period->update(['status' => 'completed']);

            AuditLog::log(
                'payroll_completed',
                PayrollPeriod::class,
                $period->id,
                ['status' => 'processing'],
                ['status' => 'completed'],
                'Payroll period completed'
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Release payroll (mark as released)
     */
    public function releasePayroll(Payroll $payroll): void
    {
        $payroll->update(['status' => 'released']);

        AuditLog::log(
            'payroll_released',
            Payroll::class,
            $payroll->id,
            ['status' => $payroll->getOriginal('status')],
            ['status' => 'released'],
            'Payroll released to employee'
        );
    }
}
