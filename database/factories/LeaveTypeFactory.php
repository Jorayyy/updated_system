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
            'description' => $this->faker->sentence(),
            'days_per_year' => $this->faker->numberBetween(5, 15),
            'is_paid' => $this->faker->boolean(80),
            'is_active' => true,
        ];
    }

    public function vacationLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Vacation Leave',
            'days_per_year' => 15,
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
