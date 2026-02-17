<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Site;
use App\Models\Account;
use App\Models\PayrollGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CleanSystemSeeder extends Seeder
{
    public function run()
    {
        // 1. Clear Existing Data (Users and their associated data)
        // Database-agnostic foreign key check toggle
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        DB::table('users')->truncate();
        DB::table('attendances')->truncate();
        DB::table('daily_time_records')->truncate();
        DB::table('payrolls')->truncate();
        DB::table('payroll_periods')->truncate();
        DB::table('audit_logs')->truncate();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // 2. Ensure basic prerequisites exist
        $tacloban = Site::firstOrCreate(['name' => 'MEBS Tacloban Main'], [
            'location' => 'Tacloban City, Leyte', 'is_active' => true
        ]);

        $roles = [
            'super_admin' => Account::firstOrCreate(['system_role' => 'super_admin'], ['name' => 'Super Admin', 'hierarchy_level' => 100]),
            'admin' => Account::firstOrCreate(['system_role' => 'admin'], ['name' => 'Admin User', 'hierarchy_level' => 80]),
            'accounting' => Account::firstOrCreate(['system_role' => 'accounting'], ['name' => 'Accounting', 'hierarchy_level' => 60]),
            'employee' => Account::firstOrCreate(['system_role' => 'employee'], ['name' => 'Standard Employee', 'hierarchy_level' => 0]),
        ];

        $payrollGroups = [
            'Administrative Team' => PayrollGroup::firstOrCreate(['name' => 'Administrative Team']),
            'BPO' => PayrollGroup::firstOrCreate(['name' => 'BPO'], ['description' => 'Graveyard shift group']),
        ];

        $password = Hash::make('password');

        // 3. Recreate the EXACT 8 Users found locally
        $usersToCreate = [
            [
                'email' => 'admin@mebs.com',
                'name' => 'System Admin',
                'role' => 'super_admin',
                'account_id' => $roles['super_admin']->id,
                'monthly_salary' => 55000.00,
                'hourly_rate' => 316.09,
                'employee_id' => 'ADM-001',
                'payroll_group_id' => $payrollGroups['Administrative Team']->id,
            ],
            [
                'email' => 'hr@mebs.com',
                'name' => 'Maria Santos',
                'role' => 'admin',
                'account_id' => $roles['admin']->id,
                'monthly_salary' => 45000.00,
                'hourly_rate' => 258.62,
                'employee_id' => 'ADM-002',
                'payroll_group_id' => $payrollGroups['Administrative Team']->id,
            ],
            [
                'email' => 'accounting@mebs.com',
                'name' => 'Juan Reyes',
                'role' => 'accounting',
                'account_id' => $roles['accounting']->id,
                'monthly_salary' => 32000.00,
                'hourly_rate' => 183.91,
                'employee_id' => 'ADM-003',
                'payroll_group_id' => $payrollGroups['Administrative Team']->id,
            ],
            [
                'email' => 'accounting@mebshiyas.com',
                'name' => 'Accounting Head',
                'role' => 'accounting',
                'account_id' => $roles['accounting']->id,
                'monthly_salary' => 28000.00,
                'hourly_rate' => 160.92,
                'employee_id' => 'ACC-001',
                'payroll_group_id' => $payrollGroups['Administrative Team']->id,
            ],
            [
                'email' => 'hercules.bpo@test.mebs',
                'name' => 'Hercules Power',
                'role' => 'employee',
                'account_id' => $roles['employee']->id,
                'monthly_salary' => 55000.00,
                'hourly_rate' => 316.09,
                'employee_id' => 'BPO-001',
                'payroll_group_id' => $payrollGroups['BPO']->id,
            ],
            [
                'email' => 'apollo.bpo@test.mebs',
                'name' => 'Althea Hera',
                'role' => 'employee',
                'account_id' => $roles['employee']->id,
                'monthly_salary' => 45000.00,
                'hourly_rate' => 258.62,
                'employee_id' => 'BPO-002',
                'payroll_group_id' => $payrollGroups['BPO']->id,
            ],
            [
                'email' => 'ares.bpo@test.mebs',
                'name' => 'Apollo Sun',
                'role' => 'employee',
                'account_id' => $roles['employee']->id,
                'monthly_salary' => 32000.00,
                'hourly_rate' => 183.91,
                'employee_id' => 'BPO-003',
                'payroll_group_id' => $payrollGroups['BPO']->id,
            ],
            [
                'email' => 'zeus.bpo@test.mebs',
                'name' => 'Athena Wisdom',
                'role' => 'employee',
                'account_id' => $roles['employee']->id,
                'monthly_salary' => 28000.00,
                'hourly_rate' => 160.92,
                'employee_id' => 'BPO-004',
                'payroll_group_id' => $payrollGroups['BPO']->id,
            ],
        ];

        foreach ($usersToCreate as $user) {
            User::create(array_merge($user, [
                'password' => $password,
                'site_id' => $tacloban->id,
                'department' => $user['role'] === 'employee' ? 'BPO Operations' : 'Management',
                'position' => $user['role'] === 'employee' ? 'Customer Support' : 'Manager',
                'is_active' => true,
                'date_hired' => '2024-01-01',
            ]));
        }
    }
}
