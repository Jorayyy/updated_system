<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PayrollGroup;
use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;
use App\Models\Department;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductionTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create a Default Account/Organization
        $account = Account::firstOrCreate(
            ['name' => 'Main Organization'],
            [
                'description' => 'Primary Production Test Organization',
                'is_active' => true,
            ]
        );

        // 2. Create Departments
        $depts = ['Operations', 'Human Resources', 'Accounting', 'IT Support'];
        foreach ($depts as $name) {
            Department::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
        $deptId = Department::where('name', 'Operations')->first()->id;

        // 3. Create Payroll Groups
        $groups = [
            ['name' => 'Weekly Staff', 'period_type' => 'weekly'],
            ['name' => 'Semi-Monthly Staff', 'period_type' => 'semi_monthly'],
            ['name' => 'Monthly Management', 'period_type' => 'monthly'],
        ];

        foreach ($groups as $gData) {
            $group = PayrollGroup::firstOrCreate(
                ['name' => $gData['name']],
                ['period_type' => $gData['period_type'], 'is_active' => true]
            );

            // 4. Create an Active Period for each group
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->startOfMonth()->addDays(14); // Mid-month for simulation
            
            if ($gData['period_type'] === 'weekly') {
                $start = Carbon::now()->subDays(7)->startOfDay();
                $end = Carbon::now()->startOfDay();
            }

            $period = PayrollPeriod::firstOrCreate(
                [
                    'payroll_group_id' => $group->id,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                ],
                [
                    'pay_date' => $end->addDays(2)->toDateString(),
                    'remarks' => "Simulation Period for {$group->name}",
                    'status' => 'draft',
                ]
            );

            // 5. Create 3 Employees for this Group
            for ($i = 1; $i <= 3; $i++) {
                $empName = $gData['name'] . " Emp " . $i;
                $user = User::firstOrCreate(
                    ['email' => strtolower(str_replace(' ', '.', $empName)) . '@example.com'],
                    [
                        'name' => $empName,
                        'password' => Hash::make('password'),
                        'role' => 'employee',
                        'payroll_group_id' => $group->id,
                        'account_id' => $account->id,
                        'department_id' => $deptId,
                        'employee_id' => strtoupper(Str::random(8)),
                        'is_active' => true,
                        'monthly_salary' => 25000 + ($i * 1000),
                        'hourly_rate' => 150,
                    ]
                );

                // 6. Create DTRs for this Employee in this Period
                // Let's create 3 days of pending DTRs
                for ($d = 0; $d < 3; $d++) {
                    $date = Carbon::parse($period->start_date)->addDays($d);
                    
                    // Skip weekends for realism
                    if ($date->isWeekend()) continue;

                    DailyTimeRecord::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'date' => $date->toDateString(),
                        ],
                        [
                            'payroll_period_id' => $period->id,
                            'time_in' => $date->copy()->setTime(8, 0, 0),
                            'time_out' => $date->copy()->setTime(17, 0, 0),
                            'net_work_minutes' => 480, // 8 hours
                            'status' => 'pending',
                        ]
                    );
                }
            }
        }

        $this->command->info('DTR Approval Test Data seeded successfully!');
    }
}
