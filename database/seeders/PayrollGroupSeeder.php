<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayrollGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PayrollGroupSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating Payroll Groups...');

        $groups = [
            ['name' => 'Administrative Team', 'description' => 'HR, Accounting, and Management'],
            ['name' => 'Customer Support', 'description' => 'Inbound and Outbound Support'],
            ['name' => 'Core Operations', 'description' => 'Main Production Staff'],
            ['name' => 'Security Dept', 'description' => 'Facility Security'],
            ['name' => 'Accounting Dept', 'description' => 'Finance and Payroll'],
        ];

        foreach ($groups as $groupData) {
            $group = PayrollGroup::firstOrCreate(
                ['name' => $groupData['name']],
                ['description' => $groupData['description']]
            );
            $this->command->info("Created/Found Group: {$group->name}");
        }

        // If company has no employees (because Faker skipped them), create 20 dummy ones
        if (User::where('role', 'employee')->count() < 5) {
            $this->command->warn('No employees found. Creating 25 dummy employees for production testing...');
            $password = Hash::make('password');
            $depts = ['Operations', 'Support', 'Security'];
            
            for ($i = 1; $i <= 25; $i++) {
                $dept = $depts[array_rand($depts)];
                $groupId = null;
                
                // Simple mapping
                if ($dept == 'Operations') $groupId = PayrollGroup::where('name', 'Core Operations')->first()->id;
                if ($dept == 'Support') $groupId = PayrollGroup::where('name', 'Customer Support')->first()->id;
                if ($dept == 'Security') $groupId = PayrollGroup::where('name', 'Security Dept')->first()->id;

                User::create([
                    'employee_id' => 'EMP-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'name' => "Employee " . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'email' => "employee{$i}@mebshiyas.com",
                    'password' => $password,
                    'role' => 'employee',
                    'department' => $dept,
                    'position' => 'Staff',
                    'site_id' => 1,
                    'monthly_salary' => 18000 + (rand(0, 10) * 1000),
                    'is_active' => true,
                    'payroll_group_id' => $groupId
                ]);
            }
            $this->command->info('25 Employees created successfully.');
        } else {
            // Distribute existing users if they aren't assigned
            $this->command->info('Distributing existing users into groups...');
            $groupList = PayrollGroup::all();
            User::whereNull('payroll_group_id')->where('role', 'employee')->each(function($user) use ($groupList) {
                $user->update(['payroll_group_id' => $groupList->random()->id]);
            });
        }
    }
}
