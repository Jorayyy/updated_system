<?php

namespace Database\Factories;

use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollPeriodFactory extends Factory
{
    protected $model = PayrollPeriod::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-3 months', 'now');
        $endDate = (clone $startDate)->modify('+14 days');
        $payDate = (clone $endDate)->modify('+5 days');
        
        return [
            'name' => $startDate->format('F Y') . ' - ' . ($startDate->format('d') <= 15 ? '1st Half' : '2nd Half'),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'pay_date' => $payDate->format('Y-m-d'),
            'type' => 'semi_monthly',
            'status' => $this->faker->randomElement(['draft', 'processing', 'completed']),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
