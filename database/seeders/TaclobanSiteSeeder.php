<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Site;
use App\Models\PayrollGroup;
use App\Models\Attendance;
use App\Models\DailyTimeRecord;
use App\Models\Account;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TaclobanSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure a default account exists
        $account = Account::first() ?? Account::create([
            'name' => 'Tacloban Operations',
            'description' => 'Main operations account for Tacloban site',
            'is_active' => true,
        ]);

        // 1. Create TACLOBAN SITE Group
        $group = PayrollGroup::updateOrCreate(
            ['name' => 'TACLOBAN SITE'],
            [
                'period_type' => 'weekly',
                'is_active' => true,
                'description' => 'Weekly payroll group for Tacloban operations'
            ]
        );

        // 2. Create TACLOBAN OFFICE Site
        $site = Site::updateOrCreate(
            ['name' => 'TACLOBAN OFFICE'],
            [
                'location' => 'Tacloban City',
                'is_active' => true
            ]
        );

        // 3. Create a Payroll Period for March
        // Since it's Weekly, let's create a period for the current week or a recent one
        $periodStart = Carbon::create(2026, 3, 15);
        $periodEnd = Carbon::create(2026, 3, 21);
        $period = PayrollPeriod::updateOrCreate(
            [
                'payroll_group_id' => $group->id,
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
            ],
            [
                'pay_date' => $periodEnd->copy()->addDays(5)->toDateString(),
                'remarks' => 'Tacloban Site - March Week 3',
                'status' => 'draft'
            ]
        );

        // 4. Create 5 Employees for Tacloban
        for ($i = 1; $i <= 5; $i++) {
            $empEmail = "tacloban_emp{$i}@example.com";
            $user = User::updateOrCreate(
                ['email' => $empEmail],
                [
                    'employee_id' => "TAC-2026-00{$i}",
                    'name' => "Tacloban Employee {$i}",
                    'first_name' => "Tacloban",
                    'last_name' => "Employee {$i}",
                    'password' => Hash::make('password'),
                    'role' => 'employee',
                    'site_id' => $site->id,
                    'payroll_group_id' => $group->id,
                    'account_id' => $account->id,
                    'is_active' => true,
                    'hourly_rate' => 120.00,
                    'monthly_salary' => 18000.00,
                ]
            );

            // 5. Generate daily Attendance & DTR data (March 2 to March 24)
            // March 2nd was a Monday
            $currentDate = Carbon::create(2026, 3, 2);
            $yesterday = Carbon::create(2026, 3, 24);

            while ($currentDate->lte($yesterday)) {
                $dateStr = $currentDate->toDateString();
                
                // Skip Sundays
                if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                    
                    // Simple 8am-5pm schedule
                    $timeIn = $currentDate->copy()->setTime(8, 0, 0);
                    $timeOut = $currentDate->copy()->setTime(17, 0, 0);

                    // Create Raw Attendance
                    $attendance = Attendance::updateOrCreate(
                        ['user_id' => $user->id, 'date' => $dateStr],
                        [
                            'time_in' => $timeIn,
                            'time_out' => $timeOut,
                            'status' => 'present',
                            'current_step' => 'time_out',
                            'total_work_minutes' => 540, // 9 hours total (inc break)
                        ]
                    );

                    // Create Processed DTR record
                    // If the date falls within our $period range, link it
                    $dtrPeriodId = ($currentDate->gte($periodStart) && $currentDate->lte($periodEnd)) ? $period->id : null;

                    DailyTimeRecord::updateOrCreate(
                        ['user_id' => $user->id, 'date' => $dateStr],
                        [
                            'attendance_id' => $attendance->id,
                            'payroll_period_id' => $dtrPeriodId,
                            'time_in' => $timeIn,
                            'time_out' => $timeOut,
                            'net_work_minutes' => 480, // 8 hours net
                            'actual_work_minutes' => 540,
                            'attendance_status' => 'present',
                            'status' => 'pending', // Set to pending to test the APPROVAL FEATURE
                        ]
                    );
                }
                $currentDate->addDay();
            }
        }

        $this->command->info('Tacloban Site group, employees, and 3-week attendance history seeded!');
    }
}
