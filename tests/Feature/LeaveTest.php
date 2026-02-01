<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_view_leaves_page(): void
    {
        $user = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($user)->get(route('leaves.index'));

        $response->assertStatus(200);
    }

    public function test_employee_can_create_leave_request(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $leaveType = LeaveType::factory()->create();
        LeaveBalance::create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'year' => date('Y'),
            'total_days' => 15,
            'used_days' => 0,
            'remaining_days' => 15,
        ]);

        $response = $this->actingAs($user)->post(route('leaves.store'), [
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'reason' => 'Family vacation',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'status' => 'pending',
        ]);
    }

    public function test_hr_can_approve_leave_request(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $employee = User::factory()->create(['role' => 'employee']);
        $leaveType = LeaveType::factory()->create();
        
        $leaveRequest = LeaveRequest::factory()->create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hr)->patch(route('leaves.approve', $leaveRequest));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
        ]);
    }

    public function test_hr_can_reject_leave_request(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $employee = User::factory()->create(['role' => 'employee']);
        $leaveType = LeaveType::factory()->create();
        
        $leaveRequest = LeaveRequest::factory()->create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hr)->patch(route('leaves.reject', $leaveRequest), [
            'rejection_reason' => 'Insufficient staffing',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'rejected',
        ]);
    }

    public function test_employee_cannot_approve_leave_request(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $leaveType = LeaveType::factory()->create();
        
        $leaveRequest = LeaveRequest::factory()->create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($employee)->patch(route('leaves.approve', $leaveRequest));

        $response->assertStatus(403);
    }
}
