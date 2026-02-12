<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_view_attendance_page(): void
    {
        $user = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertStatus(200);
    }

    public function test_employee_can_time_in(): void
    {
        Carbon::setTestNow(now()->setHour(14)); // Ensure mid-day to avoid night shift logic
        $user = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($user)->post(route('attendance.step'));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => today()->toDateTimeString(), // SQLite stores as datetime
        ]);
        Carbon::setTestNow(); // Reset
    }

    public function test_hr_can_view_attendance_management(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);

        $response = $this->actingAs($hr)->get(route('attendance.manage'));

        $response->assertStatus(200);
    }

    public function test_employee_cannot_view_attendance_management(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->get(route('attendance.manage'));

        $response->assertStatus(403);
    }

    public function test_employee_can_view_attendance_history(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        
        // Create some attendance records
        Attendance::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('attendance.history'));

        $response->assertStatus(200);
    }
}
