<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayrollAdjustmentType;
use App\Models\CompanySetting;
use App\Models\Payroll;

class PayrollAdjustmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adjustments = [
            // Earnings/Additions -> bonus
            ['code' => 'BONUS_PERF',  'name' => 'Performance Bonus',     'type' => 'earning',   'target_field' => 'bonus'],
            ['code' => 'BONUS_ATT',   'name' => 'Attendance Incentive',  'type' => 'earning',   'target_field' => 'bonus'],
            ['code' => 'BONUS_REF',   'name' => 'Referral Bonus',        'type' => 'earning',   'target_field' => 'bonus'],
            ['code' => 'BONUS_13TH',  'name' => '13th Month Pay',       'type' => 'earning',   'target_field' => 'bonus'],
            ['code' => 'BONUS_SPEC',  'name' => 'Special Allowance',     'type' => 'earning',   'target_field' => 'bonus'],
            ['code' => 'ADD_COLA',    'name' => 'COLA (Cost of Living)', 'type' => 'earning',   'target_field' => 'bonus'],
            
            // Deductions
            ['code' => 'DED_LOAN',    'name' => 'Loan Repayment',        'type' => 'deduction', 'target_field' => 'loan_deductions'],
            ['code' => 'DED_UNIF',    'name' => 'Uniform Deduction',     'type' => 'deduction', 'target_field' => 'other_deductions'],
            ['code' => 'DED_ID',      'name' => 'ID Replacement',        'type' => 'deduction', 'target_field' => 'other_deductions'],
            ['code' => 'DED_CA',      'name' => 'Cash Advance Payment',  'type' => 'deduction', 'target_field' => 'other_deductions'],
            ['code' => 'DED_SAVINGS', 'name' => 'MEBS Savings',          'type' => 'deduction', 'target_field' => 'other_deductions'],
            ['code' => 'DED_LUNCH',   'name' => 'Lunch Break Deduction', 'type' => 'deduction', 'target_field' => 'other_deductions'],
            ['code' => 'DED_OTHER',   'name' => 'Other Adjustment',      'type' => 'deduction', 'target_field' => 'other_deductions'],
        ];

        foreach ($adjustments as $adj) {
            $settingKey = 'adj_val_' . strtolower($adj['code']);
            $defaultFormula = CompanySetting::where('key', $settingKey)->first()->value ?? '0';

            PayrollAdjustmentType::updateOrCreate(
                ['code' => $adj['code']],
                [
                    'name' => $adj['name'],
                    'type' => $adj['type'],
                    'target_field' => $adj['target_field'],
                    'default_formula' => $defaultFormula,
                    'is_system_default' => true,
                ]
            );
        }
    }
}
