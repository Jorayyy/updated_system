<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\TimekeepingTransaction;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PopulateTestData extends Command
{
    protected $signature = 'db:populate-test-data';
    protected $description = 'Purges non-admin users and generates 800 test employees with attendance data';

    public function handle()
    {
        $this->info('Starting data cleanup...');

        // 1. Identify non-admin/hr users to delete
        $employees = User::whereNotIn('role', ['admin', 'hr'])->get();
        $employeeIds = $employees->pluck('id')->toArray();

        $this->info("Found " . count($employeeIds) . " non-admin/hr users to remove.");

        if (count($employeeIds) > 0) {
            // 2. Clear related tables for these employees
            Attendance::whereIn('user_id', $employeeIds)->delete();
            TimekeepingTransaction::whereIn('user_id', $employeeIds)->delete();
            Payroll::whereIn('user_id', $employeeIds)->delete();
            DB::table('leave_requests')->whereIn('user_id', $employeeIds)->delete();
            DB::table('loans')->whereIn('user_id', $employeeIds)->delete();
            DB::table('notifications')->whereIn('notifiable_id', $employeeIds)->where('notifiable_type', User::class)->delete();
            DB::table('audit_logs')->whereIn('user_id', $employeeIds)->delete();
            DB::table('attendance_breaks')->whereIn('attendance_id', function($query) use ($employeeIds) {
                $query->select('id')->from('attendances')->whereIn('user_id', $employeeIds);
            })->delete();
            
            // Delete users
            User::whereIn('id', $employeeIds)->delete();
        }
        
        $this->info('Cleanup completed.');

        // 3. Generate 800 Users
        $this->info('Generating 800 test employees...');
        
        $newUsers = [];
        $password = Hash::make('password123');
        $departments = ['Development', 'Quality Assurance', 'Operations', 'Support', 'Marketing'];
        $positions = ['Junior Specialist', 'Senior Specialist', 'Lead', 'Coordinator'];

        // Start from 101 to avoid MEBS-0001 (Admin) and MEBS-0002 (HR)
        for ($i = 101; $i <= 900; $i++) {
            $firstName = "Employee";
            $lastName = $i;
            $email = "employee{$i}@example.com";
            $empId = "MEBS-" . str_pad($i, 4, '0', STR_PAD_LEFT);

            $newUsers[] = [
                'employee_id' => $empId,
                'name' => "$firstName $lastName",
                'email' => $email,
                'password' => $password,
                'role' => 'employee',
                'department' => $departments[array_rand($departments)],
                'position' => $positions[array_rand($positions)],
                'hourly_rate' => rand(150, 450),
                'daily_rate' => rand(1200, 3600),
                'monthly_salary' => rand(25000, 75000),
                'date_hired' => Carbon::create(2025, 1, 1),
                'birthday' => Carbon::create(1990, 1, 1)->addYears(rand(0, 15)),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($i % 100 == 0) {
                DB::table('users')->insert($newUsers);
                $newUsers = [];
                $this->info("Inserted $i users...");
            }
        }
        if (!empty($newUsers)) {
            DB::table('users')->insert($newUsers);
        }

        // 4. Generate Attendance Data
        $this->info('Generating attendance data (Aug 2025 to present)...');
        
        $allUserIds = User::whereIn('role', ['employee', 'admin', 'hr'])->pluck('id')->toArray();
        $startDate = Carbon::create(2025, 8, 1);
        $endDate = Carbon::now();

        $bar = $this->output->createProgressBar(count($allUserIds));
        $bar->start();

        foreach ($allUserIds as $userId) {
            $user = User::find($userId);
            $attendances = [];
            $currentDate = $startDate->copy();
            
            // Randomly decide shift
            // Admin and HR are always day shift (8AM-5PM)
            if ($user->role === 'admin' || $user->role === 'hr') {
                $isNightShift = false;
            } else {
                $isNightShift = (rand(1, 10) <= 9); // 90% night shift for employees
            }

            while ($currentDate->lte($endDate)) {
                if ($currentDate->isWeekday()) {
                    
                    if ($isNightShift) {
                        $startHour = rand(21, 23);
                        $clockIn = $currentDate->copy()->setHour($startHour)->setMinute(rand(0, 15));
                        $clockOut = $clockIn->copy()->addHours(9);
                    } else {
                        $startHour = rand(8, 10);
                        $clockIn = $currentDate->copy()->setHour($startHour)->setMinute(rand(0, 15));
                        $clockOut = $clockIn->copy()->addHours(9);
                    }

                    $fbOut = $clockIn->copy()->addHours(2)->addMinutes(rand(0, 5));
                    $fbIn = $fbOut->copy()->addMinutes(15);
                    $lbOut = $clockIn->copy()->addHours(4)->addMinutes(rand(0, 10));
                    $lbIn = $lbOut->copy()->addMinutes(60);
                    $sbOut = $clockIn->copy()->addHours(6)->addMinutes(20)->addMinutes(rand(0, 5));
                    $sbIn = $sbOut->copy()->addMinutes(15);

                    $attendances[] = [
                        'user_id' => $userId,
                        'date' => $currentDate->format('Y-m-d'),
                        'time_in' => $clockIn->format('Y-m-d H:i:s'),
                        'first_break_out' => $fbOut->format('Y-m-d H:i:s'),
                        'first_break_in' => $fbIn->format('Y-m-d H:i:s'),
                        'lunch_break_out' => $lbOut->format('Y-m-d H:i:s'),
                        'lunch_break_in' => $lbIn->format('Y-m-d H:i:s'),
                        'second_break_out' => $sbOut->format('Y-m-d H:i:s'),
                        'second_break_in' => $sbIn->format('Y-m-d H:i:s'),
                        'time_out' => $clockOut->format('Y-m-d H:i:s'),
                        'status' => 'present',
                        'total_work_minutes' => 480,
                        'total_break_minutes' => 90,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                $currentDate->addDay();
                
                if (count($attendances) >= 50) {
                    DB::table('attendances')->insert($attendances);
                    $attendances = [];
                }
            }
            
            if (!empty($attendances)) {
                DB::table('attendances')->insert($attendances);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nDone! 800 employees and their history generated.");
    }
}
