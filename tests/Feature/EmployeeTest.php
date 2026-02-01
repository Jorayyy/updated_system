<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_view_employees_index(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);

        $response = $this->actingAs($hr)->get(route('employees.index'));

        $response->assertStatus(200);
    }

    public function test_employee_cannot_view_employees_index(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->get(route('employees.index'));

        $response->assertStatus(403);
    }

    public function test_hr_can_create_employee(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);

        $response = $this->actingAs($hr)->post(route('employees.store'), [
            'employee_id' => 'EMP-001',
            'name' => 'Test Employee',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'employee',
            'department' => 'IT',
            'position' => 'Developer',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'employee_id' => 'EMP-001',
            'email' => 'test@example.com',
        ]);
    }

    public function test_hr_can_update_employee(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($hr)->put(route('employees.update', $employee), [
            'employee_id' => $employee->employee_id,
            'name' => 'Updated Name',
            'email' => $employee->email,
            'role' => 'employee',
            'department' => 'HR',
            'position' => 'HR Assistant',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'name' => 'Updated Name',
            'department' => 'HR',
        ]);
    }

    public function test_hr_can_toggle_employee_status(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $employee = User::factory()->create(['role' => 'employee', 'is_active' => true]);

        $response = $this->actingAs($hr)->post(route('employees.toggle-status', $employee));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'is_active' => false,
        ]);
    }
}
