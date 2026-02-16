<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use App\Models\Payroll;
use Illuminate\Database\Seeder;

class PayrollAdjustmentSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codes = [
            'BONUS_PERF'  => '{basic} * 0.10',              // 10% Performance Bonus
            'BONUS_ATT'   => '{att_inc} + ({days} * 25)',   // Profile incentive + ₱25 per day worked
            'BONUS_REF'   => '1000.00',                     // Standard ₱1,000 Referral
            'BONUS_13TH'  => '{basic} / 12',                // Standard 13th Month formula
            'BONUS_SPEC'  => '{site_inc} + 500',            // Site incentive plus fixed ₱500
            'ADD_COLA'    => '0.00',                        // COLA
            'DED_LOAN'    => '500.00',                      // Standard ₱500 Loan payment
            'DED_UNIF'    => '200.00',                      // ₱200 Uniform
            'DED_ID'      => '150.00',                      // ₱150 ID
            'DED_CA'      => '{daily} * 1',                 // Deduct 1 full day for Cash Advance
            'DED_SAVINGS' => '500.00',                      // MEBS SAVINGS (From Payslip Example)
            'DED_LUNCH'   => '60.00',                       // MISSED LUNCH PUNCH (From Payslip Example)
            'DED_OTHER'   => '0.00',
        ];

        foreach ($codes as $code => $formula) {
            $key = 'adj_val_' . strtolower($code);
            
            \App\Models\CompanySetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => (string) $formula,
                    'type' => 'string',
                    'group' => 'payroll_adjustments'
                ]
            );
        }
    }
}
