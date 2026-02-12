<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $timeIn = $this->faker->dateTimeBetween('-30 days', 'now');
        $timeOut = (clone $timeIn)->modify('+8 hours');
        
        return [
            'user_id' => User::factory(),
            'date' => $timeIn->format('Y-m-d'),
            'time_in' => $timeIn->format('H:i:s'),
            'time_out' => $timeOut->format('H:i:s'),
            'status' => $this->faker->randomElement(['present', 'late', 'half_day']),
            // 'total_hours' => 8.0, // Column missing
            // 'overtime_hours' => 0, // Column might be missing or different
            'remarks' => null,
        ];
    }

    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'present',
        ]);
    }

    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'time_in' => null,
            'time_out' => null,
            'status' => 'absent',
            'total_hours' => 0,
        ]);
    }
}
