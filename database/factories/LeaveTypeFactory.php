<?php

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Vacation Leave', 'Sick Leave', 'Emergency Leave', 'Maternity Leave', 'Paternity Leave']),
            'code' => $this->faker->unique()->word,
            'description' => $this->faker->sentence(),
            'max_days' => $this->faker->numberBetween(5, 15),
            'is_paid' => $this->faker->boolean(80),
            'is_active' => true,
        ];
    }

    public function vacationLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Vacation Leave',
            'code' => 'VL',
            'max_days' => 15,
            'is_paid' => true,
        ]);
    }

    public function sickLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Sick Leave',
            'days_per_year' => 15,
            'is_paid' => true,
        ]);
    }
}
