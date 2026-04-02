<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Account;
use App\Models\PayrollGroup;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminAndEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Default Account
        $account = Account::firstOrCreate(
            ['name' => 'Default Account'],
            [
                'client_name' => 'Default Client',
                'description' => 'Global default account for initial setup',
                'is_active' => true,
            ]
        );

        // 2. Create 'Weekly' Payroll Group
        $weeklyPayrollGroup = PayrollGroup::firstOrCreate(
            ['name' => 'Weekly'],
            [
                'description' => 'Weekly payroll processing',
                'period_type' => 'weekly',
                'is_active' => true,
            ]
        );

        // 3. Create Super Admin
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'employee_id' => 'ADMIN-001',
                'name' => 'Super Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'account_id' => $account->id,
                'payroll_group_id' => $weeklyPayrollGroup->id,
                'is_active' => true,
            ]
        );

        // 4. Create Employee
        User::updateOrCreate(
            ['email' => 'employee@example.com'],
            [
                'employee_id' => 'EMP-001',
                'name' => 'Test Employee',
                'first_name' => 'Test',
                'last_name' => 'Employee',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'account_id' => $account->id,
                'payroll_group_id' => $weeklyPayrollGroup->id,
                'is_active' => true,
            ]
        );

        $this->command->info('Default Admin and Employee accounts created successfully.');
    }
}
