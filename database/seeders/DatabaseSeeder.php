<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\CompanySetting;
use App\Models\Attendance;
use App\Models\PayrollPeriod;
use App\Models\Payroll;
use App\Services\DtrService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@mebs.com',
            'password' => Hash::make('password'),
            'employee_id' => 'MEBS-0001',
            'role' => 'admin',
            'department' => 'Executive',
            'position' => 'Site Director',
            'date_hired' => now()->subYears(5),
            'monthly_salary' => 150000,
            'is_active' => true,
        ]);

        // Create HR User
        $hr = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@mebs.com',
            'password' => Hash::make('password'),
            'employee_id' => 'MEBS-0002',
            'role' => 'hr',
            'department' => 'Human Resources',
            'position' => 'Senior HR Manager',
            'date_hired' => now()->subYears(3),
            'monthly_salary' => 85000,
            'hourly_rate' => 488.51,
            'meal_allowance' => 5000,
            'transportation_allowance' => 3000,
            'communication_allowance' => 2000,
            'is_active' => true,
        ]);

        // Create Sample Call Center Employees
        $employees = [
            /* --- Operations Department --- */
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan@mebs.com',
                'employee_id' => 'MEBS-0003',
                'department' => 'Operations',
                'position' => 'Customer Service Representative',
                'monthly_salary' => 22000,
                'hourly_rate' => 126.44,
                'meal_allowance' => 2000,
                'transportation_allowance' => 1000,
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria@mebs.com',
                'employee_id' => 'MEBS-0004',
                'department' => 'Operations',
                'position' => 'Technical Support Representative',
                'monthly_salary' => 25000,
                'hourly_rate' => 143.68,
                'meal_allowance' => 2000,
                'transportation_allowance' => 1500,
            ],
            [
                'name' => 'Pedro Reyes',
                'email' => 'pedro@mebs.com',
                'employee_id' => 'MEBS-0005',
                'department' => 'Operations',
                'position' => 'Subject Matter Expert',
                'monthly_salary' => 30000,
                'hourly_rate' => 172.41,
                'meal_allowance' => 2500,
                'transportation_allowance' => 1500,
            ],
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos@mebs.com',
                'employee_id' => 'MEBS-0006',
                'department' => 'Operations',
                'position' => 'Team Leader',
                'monthly_salary' => 45000,
                'hourly_rate' => 258.62,
                'meal_allowance' => 3000,
                'transportation_allowance' => 2000,
                'communication_allowance' => 1000,
            ],
            [
                'name' => 'Elena Rodriguez',
                'email' => 'elena@mebs.com',
                'employee_id' => 'MEBS-0007',
                'department' => 'Operations',
                'position' => 'Operations Manager',
                'monthly_salary' => 85000,
                'hourly_rate' => 488.51,
                'meal_allowance' => 5000,
                'transportation_allowance' => 5000,
                'communication_allowance' => 3000,
            ],

            /* --- Quality & Training --- */
            [
                'name' => 'Ana Garcia',
                'email' => 'ana@mebs.com',
                'employee_id' => 'MEBS-0008',
                'department' => 'Quality Assurance',
                'position' => 'QA Analyst',
                'monthly_salary' => 32000,
                'hourly_rate' => 183.91,
                'meal_allowance' => 2500,
                'transportation_allowance' => 1500,
            ],
            [
                'name' => 'Ricardo Lim',
                'email' => 'ricardo@mebs.com',
                'employee_id' => 'MEBS-0009',
                'department' => 'Training',
                'position' => 'Product Trainer',
                'monthly_salary' => 38000,
                'hourly_rate' => 218.39,
                'meal_allowance' => 2500,
                'transportation_allowance' => 1500,
                'communication_allowance' => 500,
            ],

            /* --- Workforce Management --- */
            [
                'name' => 'Sonia Bautista',
                'email' => 'sonia@mebs.com',
                'employee_id' => 'MEBS-0010',
                'department' => 'Workforce Management',
                'position' => 'Real-Time Analyst',
                'monthly_salary' => 35000,
                'hourly_rate' => 201.15,
                'meal_allowance' => 2500,
                'transportation_allowance' => 1500,
            ],
            [
                'name' => 'Mark Torres',
                'email' => 'mark@mebs.com',
                'employee_id' => 'MEBS-0011',
                'department' => 'Workforce Management',
                'position' => 'WFM Scheduler',
                'monthly_salary' => 32000,
                'hourly_rate' => 183.91,
                'meal_allowance' => 2500,
                'transportation_allowance' => 1500,
            ],

            /* --- IT & Support --- */
            [
                'name' => 'Victor Magtanggol',
                'email' => 'victor@mebs.com',
                'employee_id' => 'MEBS-0012',
                'department' => 'IT Support',
                'position' => 'IT Helpdesk Technician',
                'monthly_salary' => 28000,
                'hourly_rate' => 160.92,
                'meal_allowance' => 2000,
                'transportation_allowance' => 1000,
            ],
        ];

        foreach ($employees as $employeeData) {
            User::create([
                'name' => $employeeData['name'],
                'email' => $employeeData['email'],
                'password' => Hash::make('password'),
                'employee_id' => $employeeData['employee_id'],
                'role' => 'employee',
                'department' => $employeeData['department'],
                'position' => $employeeData['position'],
                'date_hired' => now()->subMonths(rand(3, 24)),
                'monthly_salary' => $employeeData['monthly_salary'],
                'hourly_rate' => $employeeData['hourly_rate'],
                'meal_allowance' => $employeeData['meal_allowance'] ?? 0,
                'transportation_allowance' => $employeeData['transportation_allowance'] ?? 0,
                'communication_allowance' => $employeeData['communication_allowance'] ?? 0,
                'is_active' => true,
            ]);
        }

        // Create Leave Types
        $leaveTypes = [
            [
                'name' => 'Vacation Leave',
                'code' => 'VL',
                'description' => 'Annual vacation leave for rest and personal matters',
                'max_days' => 15,
                'is_paid' => true,
                'requires_attachment' => false,
                'color' => '#10B981',
                'is_active' => true,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'description' => 'Leave for health-related absences',
                'max_days' => 15,
                'is_paid' => true,
                'requires_attachment' => true,
                'color' => '#EF4444',
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Leave',
                'code' => 'EL',
                'description' => 'Leave for emergency situations',
                'max_days' => 5,
                'is_paid' => true,
                'requires_attachment' => false,
                'color' => '#F59E0B',
                'is_active' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'ML',
                'description' => 'Leave for female employees during pregnancy and childbirth',
                'max_days' => 105,
                'is_paid' => true,
                'requires_attachment' => true,
                'color' => '#EC4899',
                'is_active' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'PL',
                'description' => 'Leave for male employees whose spouse gives birth',
                'max_days' => 7,
                'is_paid' => true,
                'requires_attachment' => true,
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => 'Leave Without Pay',
                'code' => 'LWOP',
                'description' => 'Unpaid leave for personal matters',
                'max_days' => 30,
                'is_paid' => false,
                'requires_attachment' => false,
                'color' => '#6B7280',
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $leaveTypeData) {
            LeaveType::create($leaveTypeData);
        }

        // Create Leave Balances for all employees
        $allLeaveTypes = LeaveType::all();
        $allUsers = User::where('role', '!=', 'admin')->get();

        foreach ($allUsers as $user) {
            foreach ($allLeaveTypes as $leaveType) {
                // Skip maternity leave for now (would need gender field)
                if ($leaveType->code === 'ML') continue;
                
                LeaveBalance::create([
                    'user_id' => $user->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => date('Y'),
                    'allocated_days' => $leaveType->max_days,
                    'used_days' => 0,
                    'remaining_days' => $leaveType->max_days,
                ]);
            }
        }

        // Create Company Settings
        $settings = [
            // Company Information (group: company)
            ['key' => 'company_name', 'value' => 'MEBS Call Center', 'type' => 'string', 'group' => 'company'],
            ['key' => 'company_address', 'value' => 'Tacloban City, Leyte, Philippines', 'type' => 'string', 'group' => 'company'],
            ['key' => 'company_email', 'value' => 'info@mebs.com', 'type' => 'string', 'group' => 'company'],
            ['key' => 'company_phone', 'value' => '+63 123 456 7890', 'type' => 'string', 'group' => 'company'],
            ['key' => 'company_tin', 'value' => '000-123-456-000', 'type' => 'string', 'group' => 'company'],
            ['key' => 'company_sss_number', 'value' => '03-1234567-8', 'type' => 'string', 'group' => 'company'],
            ['key' => 'company_philhealth_number', 'value' => '01-234567891-2', 'type' => 'string', 'group' => 'company'],
            ['key' => 'company_pagibig_number', 'value' => '1234-5678-9012', 'type' => 'string', 'group' => 'company'],
            
            // Attendance Settings (group: attendance)
            ['key' => 'work_start_time', 'value' => '08:00', 'type' => 'string', 'group' => 'attendance'],
            ['key' => 'work_end_time', 'value' => '17:00', 'type' => 'string', 'group' => 'attendance'],
            ['key' => 'grace_period_minutes', 'value' => '15', 'type' => 'integer', 'group' => 'attendance'],
            ['key' => 'regular_work_hours', 'value' => '8', 'type' => 'integer', 'group' => 'attendance'],
            ['key' => 'lunch_break_minutes', 'value' => '60', 'type' => 'integer', 'group' => 'attendance'],
            ['key' => 'short_break_minutes', 'value' => '15', 'type' => 'integer', 'group' => 'attendance'],
            ['key' => 'enable_breaks', 'value' => '1', 'type' => 'boolean', 'group' => 'attendance'],
            ['key' => 'max_overtime_hours', 'value' => '4', 'type' => 'integer', 'group' => 'attendance'],
            
            // Payroll Settings (group: payroll)
            ['key' => 'overtime_rate_multiplier', 'value' => '1.25', 'type' => 'float', 'group' => 'payroll'],
            ['key' => 'holiday_rate_multiplier', 'value' => '2.0', 'type' => 'float', 'group' => 'payroll'],
            ['key' => 'special_holiday_rate', 'value' => '1.30', 'type' => 'float', 'group' => 'payroll'],
            ['key' => 'night_diff_rate', 'value' => '0.10', 'type' => 'float', 'group' => 'payroll'],
            ['key' => 'night_diff_start', 'value' => '22:00', 'type' => 'string', 'group' => 'payroll'],
            ['key' => 'night_diff_end', 'value' => '06:00', 'type' => 'string', 'group' => 'payroll'],
            ['key' => 'sss_employer_share', 'value' => '0.095', 'type' => 'float', 'group' => 'payroll'],
            ['key' => 'philhealth_employer_share', 'value' => '0.025', 'type' => 'float', 'group' => 'payroll'],
            ['key' => 'pagibig_employer_share', 'value' => '100', 'type' => 'float', 'group' => 'payroll'],
            ['key' => 'payroll_cutoff_type', 'value' => 'semi_monthly', 'type' => 'string', 'group' => 'payroll'],
            ['key' => 'first_cutoff_day', 'value' => '15', 'type' => 'integer', 'group' => 'payroll'],
            ['key' => 'second_cutoff_day', 'value' => '30', 'type' => 'integer', 'group' => 'payroll'],
            ['key' => 'pay_day_offset', 'value' => '5', 'type' => 'integer', 'group' => 'payroll'],
        ];

        foreach ($settings as $setting) {
            CompanySetting::create($setting);
        }
        
        // Seed Philippine Holidays
        $this->call(HolidaySeeder::class);

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('Admin: admin@mebs.com / password');
        $this->command->info('HR: hr@mebs.com / password');
        $this->command->info('Employee: juan@mebs.com / password');

        // ============================================
        // COMPREHENSIVE TEST DATA (August 2025 - Present)
        // ============================================
        $this->command->info('');
        $this->command->info('Generating comprehensive test data from August 2025...');

        $allEmployees = User::where('role', '!=', 'admin')->get();
        $vacationLeave = LeaveType::where('code', 'VL')->first();
        $sickLeave = LeaveType::where('code', 'SL')->first();
        $emergencyLeave = LeaveType::where('code', 'EL')->first();

        // Generate Attendance Records from August 1, 2025 to Today
        $startDate = Carbon::create(2025, 8, 1);
        $endDate = Carbon::now();

        $this->command->info('Generating attendance records...');

        foreach ($allEmployees as $employee) {
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Skip weekends
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }

                // Random chance of absence (5%)
                if (rand(1, 100) <= 5) {
                    Attendance::create([
                        'user_id' => $employee->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'status' => 'absent',
                        'current_step' => 'time_out',
                        'total_work_minutes' => 0,
                        'total_break_minutes' => 0,
                        'remarks' => 'No show',
                    ]);
                    $currentDate->addDay();
                    continue;
                }

                // Generate random times with slight variations
                $baseHour = rand(7, 8);
                $baseMinute = rand(30, 59);
                if ($baseHour == 8) $baseMinute = rand(0, 15);

                $timeIn = $currentDate->copy()->setTime($baseHour, $baseMinute, rand(0, 59));
                $firstBreakOut = $timeIn->copy()->addHours(2)->addMinutes(rand(0, 15));
                $firstBreakIn = $firstBreakOut->copy()->addMinutes(rand(10, 15));
                $lunchOut = $firstBreakIn->copy()->addHours(2)->addMinutes(rand(0, 15));
                $lunchIn = $lunchOut->copy()->addMinutes(rand(55, 65));
                $secondBreakOut = $lunchIn->copy()->addHours(2)->addMinutes(rand(0, 15));
                $secondBreakIn = $secondBreakOut->copy()->addMinutes(rand(10, 15));
                $timeOut = $secondBreakIn->copy()->addHours(1)->addMinutes(rand(30, 60));

                // Calculate work and break minutes
                $totalBreakMinutes = $firstBreakIn->diffInMinutes($firstBreakOut) + 
                                    $lunchIn->diffInMinutes($lunchOut) + 
                                    $secondBreakIn->diffInMinutes($secondBreakOut);
                $totalWorkMinutes = $timeOut->diffInMinutes($timeIn) - $totalBreakMinutes;

                // Determine status
                $status = 'present';
                $isLate = $timeIn->hour > 8 || ($timeIn->hour == 8 && $timeIn->minute > 15);
                if ($isLate) $status = 'late';

                $overtimeMinutes = max(0, $totalWorkMinutes - 480);
                $undertimeMinutes = max(0, 480 - $totalWorkMinutes);

                Attendance::create([
                    'user_id' => $employee->id,
                    'date' => $currentDate->format('Y-m-d'),
                    'time_in' => $timeIn,
                    'first_break_out' => $firstBreakOut,
                    'first_break_in' => $firstBreakIn,
                    'lunch_break_out' => $lunchOut,
                    'lunch_break_in' => $lunchIn,
                    'second_break_out' => $secondBreakOut,
                    'second_break_in' => $secondBreakIn,
                    'time_out' => $timeOut,
                    'status' => $status,
                    'current_step' => 'time_out',
                    'total_work_minutes' => $totalWorkMinutes,
                    'total_break_minutes' => $totalBreakMinutes,
                    'overtime_minutes' => $overtimeMinutes,
                    'undertime_minutes' => $undertimeMinutes,
                ]);

                $currentDate->addDay();
            }
        }

        $this->command->info('Attendance records created!');

        // Generate Leave Requests
        $this->command->info('Generating leave requests...');
        
        $leaveStatuses = ['pending', 'approved', 'approved', 'approved', 'rejected'];
        $leaveReasons = [
            'VL' => ['Family vacation', 'Personal errands', 'Rest day', 'Travel', 'Home renovation'],
            'SL' => ['Flu symptoms', 'Medical checkup', 'Dental appointment', 'Fever', 'Stomach ache'],
            'EL' => ['Family emergency', 'Home emergency', 'Personal emergency'],
        ];

        foreach ($allEmployees as $employee) {
            // Generate 2-5 leave requests per employee
            $numLeaves = rand(2, 5);
            
            for ($i = 0; $i < $numLeaves; $i++) {
                $leaveType = collect([$vacationLeave, $sickLeave, $emergencyLeave])->random();
                $startDate = Carbon::create(2025, rand(8, 12), rand(1, 28));
                
                // If date is in future months, use current year properly
                if ($startDate->month > 12) {
                    $startDate->month = 12;
                }
                
                $totalDays = rand(1, 3);
                $endDate = $startDate->copy()->addDays($totalDays - 1);
                
                $status = $leaveStatuses[array_rand($leaveStatuses)];
                $reasons = $leaveReasons[$leaveType->code] ?? ['Personal reason'];
                
                LeaveRequest::create([
                    'user_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_days' => $totalDays,
                    'reason' => $reasons[array_rand($reasons)],
                    'status' => $status,
                    'approved_by' => $status !== 'pending' ? $hr->id : null,
                    'approved_at' => $status !== 'pending' ? $startDate->copy()->subDays(rand(1, 3)) : null,
                    'rejection_reason' => $status === 'rejected' ? 'Insufficient leave balance' : null,
                ]);
            }
        }

        $this->command->info('Leave requests created!');

        // Generate Payroll Periods and Payrolls
        $this->command->info('Generating payroll periods and payrolls...');

        // Create bi-monthly payroll periods from August 2025
        $periodStart = Carbon::create(2025, 8, 1);
        $periodEnd = Carbon::now();

        while ($periodStart->lt($periodEnd)) {
            // First half (1-15)
            $firstHalfEnd = $periodStart->copy()->day(15);
            $payDate1 = $firstHalfEnd->copy()->addDays(5);
            
            $period1 = PayrollPeriod::create([
                'start_date' => $periodStart,
                'end_date' => $firstHalfEnd,
                'pay_date' => $payDate1,
                'status' => 'completed',
                'period_type' => 'semi_monthly',
                'processed_by' => $hr->id,
                'processed_at' => $payDate1,
            ]);

            // Generate payrolls for all employees for this period
            foreach ($allEmployees as $employee) {
                $workDays = rand(9, 11);
                $workMinutes = $workDays * 480 + rand(-60, 120);
                $overtimeMinutes = rand(0, 240);
                $lateMinutes = rand(0, 60);
                $undertimeMinutes = rand(0, 30);

                $dailyRate = $employee->monthly_salary / 22;
                $hourlyRate = ($employee->hourly_rate > 0) ? $employee->hourly_rate : ($dailyRate / 8);
                
                $basicPay = ($workMinutes / 60) * $hourlyRate;
                $overtimePay = ($overtimeMinutes / 60) * $hourlyRate * 1.25;
                
                // Call Center Allowances (split semi-monthly)
                $allowances = ($employee->meal_allowance + $employee->transportation_allowance + $employee->communication_allowance) / 2;
                
                $grossPay = $basicPay + $overtimePay + $allowances;

                $sss = $grossPay * 0.045;
                $philhealth = $grossPay * 0.025;
                $pagibig = 100;
                $lateDeductions = ($lateMinutes / 60) * $hourlyRate;
                $undertimeDeductions = ($undertimeMinutes / 60) * $hourlyRate;
                $totalDeductions = $sss + $philhealth + $pagibig + $lateDeductions + $undertimeDeductions;
                $netPay = $grossPay - $totalDeductions;

                Payroll::create([
                    'user_id' => $employee->id,
                    'payroll_period_id' => $period1->id,
                    'total_work_days' => $workDays,
                    'total_work_minutes' => $workMinutes,
                    'total_overtime_minutes' => $overtimeMinutes,
                    'total_undertime_minutes' => $undertimeMinutes,
                    'total_late_minutes' => $lateMinutes,
                    'total_absent_days' => rand(0, 1),
                    'basic_pay' => $basicPay,
                    'overtime_pay' => $overtimePay,
                    'holiday_pay' => 0,
                    'allowances' => $allowances,
                    'gross_pay' => $grossPay,
                    'sss_contribution' => $sss,
                    'philhealth_contribution' => $philhealth,
                    'pagibig_contribution' => $pagibig,
                    'withholding_tax' => 0,
                    'late_deductions' => $lateDeductions,
                    'undertime_deductions' => $undertimeDeductions,
                    'absent_deductions' => 0,
                    'other_deductions' => 0,
                    'total_deductions' => $totalDeductions,
                    'net_pay' => $netPay,
                    'status' => 'released',
                ]);
            }

            // Second half (16-end of month)
            $secondHalfStart = $periodStart->copy()->day(16);
            $secondHalfEnd = $periodStart->copy()->endOfMonth();
            $payDate2 = $secondHalfEnd->copy()->addDays(5);

            $period2 = PayrollPeriod::create([
                'start_date' => $secondHalfStart,
                'end_date' => $secondHalfEnd,
                'pay_date' => $payDate2,
                'status' => 'completed',
                'period_type' => 'semi_monthly',
                'processed_by' => $hr->id,
                'processed_at' => $payDate2,
            ]);

            // Generate payrolls for second half
            foreach ($allEmployees as $employee) {
                $workDays = rand(9, 12);
                $workMinutes = $workDays * 480 + rand(-60, 120);
                $overtimeMinutes = rand(0, 240);
                $lateMinutes = rand(0, 60);
                $undertimeMinutes = rand(0, 30);

                $dailyRate = $employee->monthly_salary / 22;
                $hourlyRate = ($employee->hourly_rate > 0) ? $employee->hourly_rate : ($dailyRate / 8);
                
                $basicPay = ($workMinutes / 60) * $hourlyRate;
                $overtimePay = ($overtimeMinutes / 60) * $hourlyRate * 1.25;
                
                // Call Center Allowances (split semi-monthly)
                $allowances = ($employee->meal_allowance + $employee->transportation_allowance + $employee->communication_allowance) / 2;

                $grossPay = $basicPay + $overtimePay + $allowances;

                $sss = $grossPay * 0.045;
                $philhealth = $grossPay * 0.025;
                $pagibig = 100;
                $lateDeductions = ($lateMinutes / 60) * $hourlyRate;
                $undertimeDeductions = ($undertimeMinutes / 60) * $hourlyRate;
                $totalDeductions = $sss + $philhealth + $pagibig + $lateDeductions + $undertimeDeductions;
                $netPay = $grossPay - $totalDeductions;

                Payroll::create([
                    'user_id' => $employee->id,
                    'payroll_period_id' => $period2->id,
                    'total_work_days' => $workDays,
                    'total_work_minutes' => $workMinutes,
                    'total_overtime_minutes' => $overtimeMinutes,
                    'total_undertime_minutes' => $undertimeMinutes,
                    'total_late_minutes' => $lateMinutes,
                    'total_absent_days' => rand(0, 1),
                    'basic_pay' => $basicPay,
                    'overtime_pay' => $overtimePay,
                    'holiday_pay' => 0,
                    'allowances' => $allowances,
                    'gross_pay' => $grossPay,
                    'sss_contribution' => $sss,
                    'philhealth_contribution' => $philhealth,
                    'pagibig_contribution' => $pagibig,
                    'withholding_tax' => 0,
                    'late_deductions' => $lateDeductions,
                    'undertime_deductions' => $undertimeDeductions,
                    'absent_deductions' => 0,
                    'other_deductions' => 0,
                    'total_deductions' => $totalDeductions,
                    'net_pay' => $netPay,
                    'status' => 'released',
                ]);
            }

            // Move to next month
            $periodStart->addMonth()->day(1);
        }

        $this->command->info('Payroll periods and payrolls created!');
        $this->command->info('');

        // FINAL STEP: Generate DTRs from the attendance records we just created
        $this->command->info('Generating Daily Time Records (DTRs) from attendance data...');
        $dtrService = app(DtrService::class);
        $periods = PayrollPeriod::all();
        $totalCreated = 0;
        foreach ($periods as $period) {
            $this->command->info("   Processing {$period->period_label}...");
            $result = $dtrService->generateDtrForPeriod($period);
            $count = $result['total_dtrs_created'] ?? 0;
            $this->command->info("   - Created $count records");
            $totalCreated += $count;
        }
        $this->command->info("DTR Generation Complete! Total created: $totalCreated");

        $this->command->info('');
        $this->command->info('=== TEST DATA GENERATION COMPLETE ===');
        $this->command->info('Generated data includes:');
        $this->command->info('- Attendance records from Aug 2025 to present');
        $this->command->info('- DTR records linked to payroll periods');
        $this->command->info('- Leave requests (various statuses)');
        $this->command->info('- Payroll periods (bi-monthly)');
        $this->command->info('- Payslips for all employees');
    }
}
