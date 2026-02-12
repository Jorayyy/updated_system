<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Models\Site;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

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
        $site = Site::create(['name' => 'Main Site', 'is_active' => true]);
        $hrAccount = Account::create(['name' => 'HR Account', 'site_id' => $site->id, 'hierarchy_level' => 100, 'system_role' => 'hr', 'is_active' => true]);
        $empAccount = Account::create(['name' => 'Emp Account', 'site_id' => $site->id, 'hierarchy_level' => 10, 'system_role' => 'employee', 'is_active' => true]);
        $department = Department::create(['name' => 'IT', 'is_active' => true]);
        
        $hr = User::factory()->create(['role' => 'hr', 'account_id' => $hrAccount->id]);

        $response = $this->actingAs($hr)->post(route('employees.store'), [
            'employee_id' => 'EMP-001',
            'name' => 'Test Employee',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'employee',
            'department_id' => $department->id,
            'department' => 'Legacy Ignored',
            'account_id' => $empAccount->id,
            'site_id' => $site->id,
            'position' => 'Developer',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'employee_id' => 'EMP-001',
            'email' => 'test@example.com',
            'department' => 'IT', // Derived from ID
        ]);
    }

    public function test_hr_can_update_employee(): void
    {
        $site = Site::create(['name' => 'Main Site', 'is_active' => true]);
        $hrAccount = Account::create(['name' => 'HR Account', 'site_id' => $site->id, 'hierarchy_level' => 100, 'system_role' => 'hr', 'is_active' => true]);
        $empAccount = Account::create(['name' => 'Emp Account', 'site_id' => $site->id, 'hierarchy_level' => 10, 'system_role' => 'employee', 'is_active' => true]);
        $department = Department::create(['name' => 'HR', 'is_active' => true]);
        
        $hr = User::factory()->create(['role' => 'hr', 'account_id' => $hrAccount->id]);
        $employee = User::factory()->create(['role' => 'employee', 'account_id' => $empAccount->id]);

        $response = $this->actingAs($hr)->put(route('employees.update', $employee), [
            'employee_id' => $employee->employee_id,
            'name' => 'Updated Name',
            'email' => $employee->email,
            'role' => 'employee',
            'department_id' => $department->id,
            'account_id' => $empAccount->id,
            'site_id' => $site->id,
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
        $site = Site::create(['name' => 'Main Site', 'is_active' => true]);
        $hrAccount = Account::create(['name' => 'HR Account', 'site_id' => $site->id, 'hierarchy_level' => 100, 'system_role' => 'hr', 'is_active' => true]);
        $empAccount = Account::create(['name' => 'Emp Account', 'site_id' => $site->id, 'hierarchy_level' => 10, 'system_role' => 'employee', 'is_active' => true]);

        $hr = User::factory()->create(['role' => 'hr', 'account_id' => $hrAccount->id]);
        $employee = User::factory()->create(['role' => 'employee', 'is_active' => true, 'account_id' => $empAccount->id]);

        $response = $this->actingAs($hr)->post(route('employees.toggle-status', $employee), [
            'admin_password' => 'password',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'is_active' => false,
        ]);
    }
}
