<?php

namespace Database\Factories;

use App\Models\Payroll;
use App\Models\User;
use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollFactory extends Factory
{
    protected $model = Payroll::class;

    public function definition(): array
    {
        $basicSalary = $this->faker->randomFloat(2, 15000, 80000);
        $allowances = $this->faker->randomFloat(2, 1000, 5000);
        $overtime = $this->faker->randomFloat(2, 0, 5000);
        $grossPay = $basicSalary + $allowances + $overtime;
        
        $sssContribution = $this->faker->randomFloat(2, 400, 1200);
        $philhealthContribution = $this->faker->randomFloat(2, 200, 800);
        $pagibigContribution = 100;
        $withholdingTax = $this->faker->randomFloat(2, 0, 5000);
        $otherDeductions = $this->faker->randomFloat(2, 0, 1000);
        $totalDeductions = $sssContribution + $philhealthContribution + $pagibigContribution + $withholdingTax + $otherDeductions;
        
        $netPay = $grossPay - $totalDeductions;
        
        return [
            'user_id' => User::factory(),
            'payroll_period_id' => PayrollPeriod::factory(),
            'basic_pay' => $basicSalary,
            // 'hourly_rate' => $basicSalary / 176, // Assuming 22 days * 8 hours
            // 'total_hours_worked' => $this->faker->randomFloat(2, 160, 200),
            // 'overtime_hours' => $this->faker->randomFloat(2, 0, 20),
            'overtime_pay' => $overtime,
            'allowances' => $allowances,
            'gross_pay' => $grossPay,
            'sss_contribution' => $sssContribution,
            'philhealth_contribution' => $philhealthContribution,
            'pagibig_contribution' => $pagibigContribution,
            'withholding_tax' => $withholdingTax,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'status' => 'approved',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }
}
