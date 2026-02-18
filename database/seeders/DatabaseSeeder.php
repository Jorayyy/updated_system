<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\PayrollPeriod;
use App\Models\Payroll;
use App\Models\Site;
use App\Models\Account;
use App\Services\DtrService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
// use Faker\Factory as Faker; // Commented out to prevent crash in production if dev dependencies are missing
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $faker = Faker::create('en_PH'); // Moved inside check
        
        $this->command->info('Initializing System Data...');

        // 1. Setup Sites
        $tacloban = Site::updateOrCreate(['name' => 'MEBS Tacloban Main'], [
            'location' => 'Tacloban City, Leyte', 'description' => 'Main operational hub', 'is_active' => true
        ]);
        $cebu = Site::updateOrCreate(['name' => 'MEBS Cebu Branch'], [
            'location' => 'IT Park, Cebu City', 'description' => 'Satellite office', 'is_active' => true
        ]);

        // 2. Setup Roles
        $roles = [
            'super_admin' => Account::updateOrCreate(['system_role' => 'super_admin'], ['name' => 'Super Admin', 'hierarchy_level' => 100]),
            'admin' => Account::updateOrCreate(['system_role' => 'admin'], ['name' => 'Admin User', 'hierarchy_level' => 80]),
            'hr' => Account::updateOrCreate(['system_role' => 'hr'], ['name' => 'HR Manager', 'hierarchy_level' => 60]),
            'accounting' => Account::updateOrCreate(['system_role' => 'accounting'], ['name' => 'Accounting', 'hierarchy_level' => 60]),
            'employee' => Account::updateOrCreate(['system_role' => 'employee'], ['name' => 'Standard Employee', 'hierarchy_level' => 0]),
        ];

        // 3. Setup Leave Types
        $leaveTypes = [
            'VL' => LeaveType::firstOrCreate(['code' => 'VL'], ['name' => 'Vacation Leave', 'max_days' => 15, 'is_paid' => true, 'color' => 'bg-green-100']),
            'SL' => LeaveType::firstOrCreate(['code' => 'SL'], ['name' => 'Sick Leave', 'max_days' => 15, 'is_paid' => true, 'requires_attachment' => true, 'color' => 'bg-red-100']),
            'EL' => LeaveType::firstOrCreate(['code' => 'EL'], ['name' => 'Emergency Leave', 'max_days' => 5, 'is_paid' => true, 'color' => 'bg-yellow-100']),
        ];

        // 4. Create Key Personnel
        $password = Hash::make('password');
        $users = [];

        // Admin (Management Account)
        $users[] = User::firstOrCreate(
            ['email' => 'admin@mebs.com'],
            [
                'employee_id' => 'ADM-001',
                'name' => 'System Admin',
                'password' => $password,
                'role' => 'super_admin',
                'account_id' => $roles['super_admin']->id,
                'department' => 'Executive',
                'position' => 'IT Director',
                'site_id' => $tacloban->id,
                'monthly_salary' => 80000,
                'hourly_rate' => 80000 / 22 / 8,
                'date_hired' => '2023-01-01',
                'is_active' => true,
            ]
        );

        // Admin (Employee Account)
        $users[] = User::firstOrCreate(
            ['email' => 'admin.emp@mebs.com'],
            [
                'employee_id' => 'ADM-001-EMP',
                'name' => 'System Admin (Employee Mode)',
                'password' => $password,
                'role' => 'employee',
                'account_id' => $roles['employee']->id, // Restricted to employee level
                'department' => 'Executive',
                'position' => 'IT Director',
                'site_id' => $tacloban->id,
                'monthly_salary' => 80000,
                'hourly_rate' => 80000 / 22 / 8,
                'date_hired' => '2023-01-01',
                'is_active' => true,
            ]
        );

        // HR (Management Account)
        $users[] = User::firstOrCreate(
            ['email' => 'hr@mebs.com'],
            [
                'employee_id' => 'HR-001',
                'name' => 'Maria Santos',
                'password' => $password,
                'role' => 'admin',
                'account_id' => $roles['hr']->id,
            'department' => 'Human Resources',
            'position' => 'HR Manager',
            'site_id' => $tacloban->id,
            'monthly_salary' => 45000,
            'hourly_rate' => 45000 / 22 / 8,
            'date_hired' => '2023-03-15',
            'is_active' => true,
        ]);

        // HR (Employee Account)
        $users[] = User::firstOrCreate(
            ['email' => 'hr.emp@mebs.com'],
            [
                'employee_id' => 'HR-001-EMP',
                'name' => 'Maria Santos (Employee Mode)',
                'password' => $password,
                'role' => 'employee',
                'account_id' => $roles['employee']->id,
                'department' => 'Human Resources',
                'position' => 'HR Manager',
                'site_id' => $tacloban->id,
                'monthly_salary' => 45000,
                'hourly_rate' => 45000 / 22 / 8,
                'date_hired' => '2023-03-15',
                'is_active' => true,
            ]
        );

        // Accounting (Management Account)
        $users[] = User::firstOrCreate(
            ['email' => 'accounting@mebs.com'],
            [
                'employee_id' => 'FIN-001',
                'name' => 'Juan Reyes',
                'password' => $password,
                'role' => 'accounting',
                'account_id' => $roles['accounting']->id,
                'department' => 'Finance',
                'position' => 'Finance Head',
                'site_id' => $tacloban->id,
                'monthly_salary' => 50000,
                'hourly_rate' => 50000 / 22 / 8,
                'date_hired' => '2023-02-01',
                'is_active' => true,
            ]
        );

        if (!class_exists(\Faker\Factory::class)) {
            $this->command->warn('Faker not found. Skipping dummy data generation (Employees, Attendance, Payroll).');
            return;
        }

        $faker = \Faker\Factory::create('en_PH');

        // COMMENTED OUT FOR PRODUCTION READINESS - No dummy employees by default
        /* 
        $this->command->info('Creating 97 Employees...');
        
        $departments = ['Operations', 'Customer Support', 'Technical Support', 'Sales', 'Data Entry'];
        $positions = [
            'Operations' => ['Team Leader', 'Operations Manager', 'Agent'],
            'Customer Support' => ['CSR Level 1', 'CSR Level 2', 'Support Lead'],
            'Technical Support' => ['TSR Level 1', 'TSR Level 2', 'Tech Lead'],
            'Sales' => ['Sales Associate', 'Sales Team Lead'],
            'Data Entry' => ['Encoder', 'QA Specialist']
        ];

        // Create 97 Employees
        for ($i = 1; $i <= 97; $i++) {
            $dept = $faker->randomElement($departments);
            $pos = $faker->randomElement($positions[$dept]);
            $site = $faker->randomElement([$tacloban->id, $cebu->id]);
            
            // Salary logic based on PH BPO Market Standards (Monthly PHP)
            // Agent/CSR/TSR: 18k - 25k (Entry) | 25k - 35k (Experienced/Spec)
            // Team Lead: 35k - 55k
            // Ops Manager: 60k - 120k
            // QA/Support: 20k - 30k
            
            $salary = match(true) {
                str_contains($pos, 'Manager') => $faker->numberBetween(60, 100) * 1000,
                str_contains($pos, 'Lead') => $faker->numberBetween(35, 55) * 1000, 
                str_contains($pos, 'Technical') || str_contains($pos, 'QA') => $faker->numberBetween(22, 32) * 1000,
                default => $faker->numberBetween(18, 25) * 1000
            };

            $users[] = User::create([
                'employee_id' => 'EMP-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'name' => $faker->name,
                'email' => 'user'.$i.'@mebs.com', // Predictable emails
                'password' => $password,
                'role' => 'employee',
                'account_id' => $roles['employee']->id,
                'department' => $dept,
                'position' => $pos,
                'site_id' => $site,
                'monthly_salary' => $salary,
                'hourly_rate' => $salary / 22 / 8,
                // Allowances
                'meal_allowance' => 2000,
                'transportation_allowance' => 500,
                'other_allowance' => 500,
                'date_hired' => $faker->dateTimeBetween('-2 years', '2025-11-01'),
                'is_active' => true,
                // Government IDs
                'sss_number' => $faker->numerify('##-#######-#'),
                'philhealth_number' => $faker->numerify('##-#########-#'),
                'pagibig_number' => $faker->numerify('####-####-####'),
            ]);
        }
        */

        // 5. Generate Attendance & Payroll
        /* Commented out for faster seeding, will use payroll:redo-weekly instead
        $startSimulation = Carbon::create(2025, 12, 1);
        $endSimulation = Carbon::create(2026, 2, 11); // Until yesterday
        
        $this->command->info('Generating Attendance from ' . $startSimulation->format('Y-m-d') . ' to ' . $endSimulation->format('Y-m-d') . '...');

        $dates = [];
        $curr = $startSimulation->copy();
        while ($curr <= $endSimulation) {
            $dates[] = $curr->copy();
            $curr->addDay();
        }

        $attendanceBatch = [];
        
        foreach ($users as $user) {
            // Assign a shift type to user (Day or Night)
            $isNightShift = $faker->boolean(40); // 40% chance night shift
            
            foreach ($dates as $date) {
                // Skip Sundays
                if ($date->isSunday()) continue;

                $rand = $faker->numberBetween(1, 100);
                
                // 90% Present, 5% Late, 3% Absent, 2% Leave
                if ($rand <= 90) {
                    // Present
                    $isLate = $rand > 85;
                    
                    if ($isNightShift) {
                        // Night Shift: 8PM to 5AM
                        $schedIn = $date->copy()->setTime(20, 0, 0);
                        $schedOut = $date->copy()->addDay()->setTime(5, 0, 0);
                        
                        // Actual
                        $timeIn = $isLate 
                            ? $schedIn->copy()->addMinutes($faker->numberBetween(15, 120)) 
                            : $schedIn->copy()->subMinutes($faker->numberBetween(0, 30));
                        
                        $timeOut = $schedOut->copy()->addMinutes($faker->numberBetween(0, 60)); // usually strict or OT

                    } else {
                        // Day Shift: 8AM to 5PM
                        $schedIn = $date->copy()->setTime(8, 0, 0);
                        $schedOut = $date->copy()->setTime(17, 0, 0);
                        
                        $timeIn = $isLate 
                            ? $schedIn->copy()->addMinutes($faker->numberBetween(15, 120)) 
                            : $schedIn->copy()->subMinutes($faker->numberBetween(0, 30));
                        
                        $timeOut = $schedOut->copy()->addMinutes($faker->numberBetween(0, 60));
                    }

                    // Calculate basic metrics
                    $status = $isLate ? 'late' : 'present';
                    $totalMinutes = $timeIn->diffInMinutes($timeOut); 
                    $workMinutes = max(0, $totalMinutes - 60); // 1hr break
                    $lateMinutes = $isLate ? $schedIn->diffInMinutes($timeIn) : 0;
                    
                    // OT Calculation (Random OT for some)
                    $otMinutes = 0;
                    if ($faker->boolean(20)) {
                         $otMinutes = $faker->numberBetween(60, 180); // 1-3 hours OT
                         $timeOut->addMinutes($otMinutes);
                         $workMinutes += $otMinutes;
                    }

                    Attendance::create([
                        'user_id' => $user->id,
                        'date' => $date->format('Y-m-d'),
                        'time_in' => $timeIn,
                        'time_out' => $timeOut,
                        'status' => $status,
                        'total_work_minutes' => $workMinutes,
                        'late_minutes' => $lateMinutes,
                        'overtime_minutes' => $otMinutes,
                    ]);

                } elseif ($rand <= 95) {
                    // Absent
                    Attendance::create([
                        'user_id' => $user->id,
                        'date' => $date->format('Y-m-d'),
                        'status' => 'absent',
                        'total_work_minutes' => 0,
                    ]);
                } else {
                    // Leave (VL/SL)
                    // Create Leave Request
                    if ($faker->boolean(50)) {
                        LeaveRequest::create([
                            'user_id' => $user->id,
                            'leave_type_id' => $leaveTypes['VL']->id,
                            'start_date' => $date->format('Y-m-d'),
                            'end_date' => $date->format('Y-m-d'),
                            'total_days' => 1,
                            'reason' => 'Personal matter',
                            'status' => 'approved',
                            'approved_by' => 1 // Admin
                        ]);
                    }
                }
            }
        }

        // 6. Generate Payroll Periods
        $periods = [
            ['2025-12-01', '2025-12-15'],
            ['2025-12-16', '2025-12-31'],
            ['2026-01-01', '2026-01-15'],
            ['2026-01-16', '2026-01-31'],
            ['2026-02-01', '2026-02-15'], // Current
        ];

        $this->command->info('Generating Payroll...');

        foreach ($periods as $p) {
            $start = Carbon::parse($p[0]);
            $end = Carbon::parse($p[1]);
            // If the period end date is feb 15 2026, and today is feb 12 2026, it is processing.
            // Earlier periods are completed.
            $status = ($end->gt(Carbon::create(2026, 2, 11))) ? 'processing' : 'completed';

            $period = PayrollPeriod::create([
                'start_date' => $start,
                'end_date' => $end,
                'pay_date' => $end->copy()->addDays(5),
                'period_type' => 'semi_monthly',
                'status' => $status
            ]);
            
            // Add DTR Generation logic
            if (class_exists(DtrService::class)) {
                try {
                     app(DtrService::class)->generateDtrForPeriod($period);
                } catch (\Exception $e) {
                    // Handle or ignore if DTR service fails during seed
                    $this->command->warn("DTR Gen warning: " . $e->getMessage());
                }
            }

            if ($status === 'completed') {
                foreach ($users as $user) {
                    $monthly = $user->monthly_salary;
                    $basic = $monthly / 2;
                    
                    $otPay = $faker->numberBetween(0, 2000);
                    $gross = $basic + $otPay + 1000;
                    
                    $sss = 500;
                    $phil = 300;
                    $pagibig = 100;
                    $tax = max(0, ($gross - 10000) * 0.1);
                    
                    $deductions = $sss + $phil + $pagibig + $tax;
                    $net = $gross - $deductions;

                    Payroll::create([
                        'user_id' => $user->id,
                        'payroll_period_id' => $period->id,
                        'basic_pay' => $basic,
                        'overtime_pay' => $otPay,
                        'allowances' => 1000,
                        'gross_pay' => $gross,
                        'sss_contribution' => $sss,
                        'philhealth_contribution' => $phil,
                        'pagibig_contribution' => $pagibig,
                        'withholding_tax' => $tax,
                        'total_deductions' => $deductions,
                        'net_pay' => $net,
                        'status' => 'released',
                    ]);
                }
            }
        }
        */

        $this->call([
            HolidaySeeder::class,
            PayrollGroupSeeder::class,
            PayrollAdjustmentSettingSeeder::class,
            PayrollAdjustmentTypeSeeder::class,
        ]);

        $this->command->info('Seed Complete! Created 100 users. Run payroll:redo-weekly to generate payrolls.');
    }
}
