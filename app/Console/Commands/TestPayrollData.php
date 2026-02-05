<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;
use App\Services\DtrService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestPayrollData extends Command
{
    protected $signature = 'db:test-payroll';
    protected $description = 'Populate test data for January 2026 payroll testing';

    public function handle(DtrService $dtrService)
    {
        $this->info('Starting test data population for Jan 2026...');

        // 1. Create a Test Employee if needed
        $testUser = User::where('email', 'tester@mebs.com')->first();
        if (!$testUser) {
            $testUser = User::create([
                'name' => 'Payroll Tester',
                'email' => 'tester@mebs.com',
                'password' => Hash::make('password'),
                'employee_id' => 'TEST-0001',
                'role' => 'employee',
                'department' => 'Operations',
                'position' => 'Senior Agent',
                'monthly_salary' => 30000,
                'hourly_rate' => 172.41,
                'daily_rate' => 1363.64,
                'date_hired' => Carbon::create(2025, 1, 1),
                'is_active' => true,
            ]);
            $this->info('Created test user: tester@mebs.com');
        } else {
            // Update rates just in case
            $testUser->update([
                'monthly_salary' => 30000,
                'hourly_rate' => 172.41,
                'daily_rate' => 1363.64,
            ]);
        }

        // 2. Generate Attendance from Jan 1 to Feb 5
        // Current date in context is Feb 6, 2026
        $startDate = Carbon::create(2026, 1, 1);
        $endDate = Carbon::create(2026, 2, 5);
        $currentDate = $startDate->copy();

        $attendancesCreated = 0;
        while ($currentDate->lte($endDate)) {
            // Only weekdays
            if ($currentDate->isWeekday()) {
                $exists = Attendance::where('user_id', $testUser->id)
                    ->whereDate('date', $currentDate)
                    ->exists();

                if (!$exists) {
                    Attendance::create([
                        'user_id' => $testUser->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'time_in' => $currentDate->copy()->setHour(8)->setMinute(0),
                        'time_out' => $currentDate->copy()->setHour(17)->setMinute(0),
                        'status' => 'present',
                        'total_work_minutes' => 480,
                        'total_break_minutes' => 60,
                    ]);
                    $attendancesCreated++;
                }
            }
            $currentDate->addDay();
        }
        $this->info("Generated $attendancesCreated attendance records for the test user.");

        // 3. Create Payroll Periods for January and February
        $periods = [
            [
                'start_date' => '2026-01-01',
                'end_date' => '2026-01-15',
                'pay_date' => '2026-01-20',
                'period_type' => 'semi_monthly',
            ],
            [
                'start_date' => '2026-01-16',
                'end_date' => '2026-01-31',
                'pay_date' => '2026-02-05',
                'period_type' => 'semi_monthly',
            ],
            [
                'start_date' => '2026-02-01',
                'end_date' => '2026-02-15',
                'pay_date' => '2026-02-20',
                'period_type' => 'semi_monthly',
            ]
        ];

        foreach ($periods as $p) {
            $period = PayrollPeriod::where('start_date', $p['start_date'])
                ->where('end_date', $p['end_date'])
                ->first();

            if (!$period) {
                $period = PayrollPeriod::create([
                    'start_date' => $p['start_date'],
                    'end_date' => $p['end_date'],
                    'pay_date' => $p['pay_date'],
                    'period_type' => $p['period_type'],
                    'status' => 'draft',
                ]);
                $this->info("Created payroll period: {$p['start_date']} to {$p['end_date']}");
            }

            // 4. Generate DTRs for this period
            $dtrService->generateDtrForPeriod($period);
            
            // 5. Auto-approve the DTRs so we can run computation
            $dtrs = DailyTimeRecord::where('payroll_period_id', $period->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'approved',
                    'approved_by' => User::where('role', 'admin')->first()?->id ?? User::first()->id,
                    'approved_at' => now(),
                ]);
            
            $this->info("Generated and approved DTRs for period: " . $period->period_label);
        }

        $this->info('Test data population complete. You can now test the Payroll Generation feature.');
    }
}
