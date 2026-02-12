<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\DailyTimeRecord;
use App\Models\PayrollPeriod;
use App\Models\Payroll;
use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollSimulationSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Cleaning up old transaction data...');
        
        // SQLite foreign key syntax is different, but for SQLite usually we rely on Schema Disable
        
        // Truncate logic for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            Attendance::truncate();
            DailyTimeRecord::truncate();
            Payroll::truncate();
            Payslip::truncate();
            PayrollPeriod::truncate();
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
             // Disable foreign key checks to allow truncation (MySQL)
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Attendance::truncate();
            DailyTimeRecord::truncate();
            Payroll::truncate();
            Payslip::truncate();
            PayrollPeriod::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // Ensure we have employees
        $employees = User::where('role', 'employee')->get();
        if ($employees->isEmpty()) {
            $this->command->info('No employees found. Seeding default employees...');
            $this->call(UserSeeder::class);
            $employees = User::where('role', 'employee')->get();
        }

        $startDate = Carbon::create(2026, 1, 1);
        $endDate = Carbon::now(); // Feb 13, 2026 based on context

        $this->command->info("Simulating data from {$startDate->toDateString()} to {$endDate->toDateString()}...");

        // Generate Payroll Periods (Weekly, ends on Friday)
        // Adjust start to first Monday of the year or logic for weekly.
        // If payslips are released Friday, the period usually ends a bit earlier or is cut off?
        // User said "Payroll generated (weekly Friday) -> Payslips released".
        // Let's assume the period is Mon-Fri of that week, or PrevSat-Fri?
        // "attendance done in 9 pm to 7 am Monday to Friday"
        // Let's assume Weekly Period = Monday to Sunday (but work is Mon-Fri).
        // Pay Date = Friday of that week (advance?) or Next Friday?
        // Usually payroll covers a cutoff. 
        // User says: "Payroll generated (weekly Friday) -> Payslips released"
        // Let's assume the period is the CURRENT week (Mon-Fri) and paid on Friday.

        $currentDate = $startDate->copy();
        
        // Find the first Monday on or before Jan 1
        if (!$currentDate->isMonday()) {
            $currentDate->previous(Carbon::MONDAY);
        }

        while ($currentDate->lte($endDate)) {
            $periodStart = $currentDate->copy();
            $periodEnd = $currentDate->copy()->addDays(6); // Mon-Sun
            
            // Pay Date is the Friday of this week
            $payDate = $periodStart->copy()->addDays(4);

            // Skip future periods
            if ($periodStart->gt($endDate)) {
                break;
            }

            $status = 'completed'; // Default to completed for past periods
            if ($periodEnd->gt($endDate)) {
                // Determine status for current/future week
                if ($payDate->gt($endDate)) {
                    $status = 'draft'; // Future pay date
                } else {
                    $status = 'processing'; // Current week
                }
            }

            // Create Period
            $period = PayrollPeriod::create([
                'start_date' => $periodStart,
                'end_date' => $periodEnd,
                'pay_date' => $payDate,
                'status' => $status,
                // 'type' => 'weekly', // Removed column
                // 'description' => 'Weekly Payroll ' . $periodStart->format('M d') . ' - ' . $periodEnd->format('M d'), // Removed column if not in DB
            ]);

            $this->command->info("Created Period: {$period->period_label} (Status: {$status})");

            // Generate Attendance for Mon-Fri within this period
            $workDays = [];
            $tempDate = $periodStart->copy();
            while ($tempDate->lte($periodEnd)) {
                if ($tempDate->isWeekday()) { // Mon-Fri
                    $workDays[] = $tempDate->copy();
                }
                $tempDate->addDay();
            }

            // For each employee
            foreach ($employees as $employee) {
                // 1. Generate Attendance & DTRs
                foreach ($workDays as $workDay) {
                    if ($workDay->gt($endDate)) continue;

                    // Randomize attendance
                    // Shift: 9:00 PM to 7:00 AM (Next Day)
                    // 21:00 - 07:00
                    
                    // 90% chance of attendance
                    if (rand(1, 100) <= 90) {
                        $timeIn = $workDay->copy()->setTime(21, 0, 0);
                        // Add some randomness (-10 mins to +30 mins)
                        $timeIn->addMinutes(rand(-10, 30)); 

                        $timeOut = $workDay->copy()->addDay()->setTime(7, 0, 0);
                        // Add randomness (-10 to +60 mins)
                        $timeOut->addMinutes(rand(-10, 60));

                        $attendance = Attendance::create([
                            'user_id' => $employee->id,
                            'date' => $workDay->toDateString(),
                            'time_in' => $timeIn,
                            'time_out' => $timeOut,
                            'status' => 'present',
                            'source' => 'biometric', // Simulated
                        ]);

                        // Generate DTR immediately (simulating nightly job)
                        // Simplified DTR creation logic for seeder
                        $late = $timeIn->gt($workDay->copy()->setTime(21, 15, 0)) ? $timeIn->diffInMinutes($workDay->copy()->setTime(21, 0, 0)) : 0;
                        
                        DailyTimeRecord::create([
                            'user_id' => $employee->id,
                            'payroll_period_id' => $period->id,
                            'date' => $workDay->toDateString(),
                            'time_in' => $timeIn->toTimeString(),
                            'time_out' => $timeOut->toTimeString(),
                            'attendance_id' => $attendance->id,
                            'scheduled_minutes' => 480, // 8 hours ex breaks
                            'actual_work_minutes' => 480, // Simplified
                            'net_work_minutes' => 480 - $late,
                            'late_minutes' => $late,
                            'undertime_minutes' => 0,
                            'overtime_minutes' => 0,
                            'status' => 'approved', // Auto-approve for simulation
                            'day_type' => 'regular',
                        ]);
                    } else {
                        // Absent
                        DailyTimeRecord::create([
                            'user_id' => $employee->id,
                            'payroll_period_id' => $period->id,
                            'date' => $workDay->toDateString(),
                            'status' => 'approved',
                            'attendance_status' => 'absent',
                            'day_type' => 'regular',
                            'is_auto_generated' => true,
                        ]);
                    }
                }

                // 2. Compute Payroll & generate Payslip (Only if period is completed)
                if ($status === 'completed') {
                    // Simple simulated payroll entry
                    $gross = $employee->monthly_salary ? ($employee->monthly_salary / 4) : 5000;
                    $net = $gross * 0.9; // Deductions
                    
                   $payroll = Payroll::create([
                        'user_id' => $employee->id,
                        'payroll_period_id' => $period->id,
                        'gross_pay' => $gross,
                        'net_pay' => $net,
                        'total_deductions' => $gross - $net,
                        'status' => 'released',
                        'is_posted' => true,
                        'posted_at' => $payDate,
                   ]);

                   // Create Payslip logic here if separate table exists (Usually integrated or on-the-fly)
                   // Assuming specific Payslip model interaction is automated via events or manual
                }
            }

            $currentDate->addWeek();
        }

        $this->command->info('Payroll Simulation Complete!');
    }
}
