<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_view_payroll_index(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);

        $response = $this->actingAs($hr)->get(route('payroll.index'));

        $response->assertStatus(200);
    }

    public function test_employee_cannot_view_payroll_management(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->get(route('payroll.index'));

        $response->assertStatus(403);
    }

    public function test_employee_can_view_own_payslips(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->get(route('payroll.my-payslips'));

        $response->assertStatus(200);
    }

    public function test_hr_can_create_payroll_period(): void
    {
        $hr = User::factory()->create(['role' => 'accounting']);

        $response = $this->actingAs($hr)->post(route('payroll.store-period'), [
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-15',
            'pay_date' => '2026-01-20',
            'period_type' => 'semi_monthly',
            'cover_month' => 'January',
            'cover_year' => 2026,
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('payroll_periods', [
            'start_date' => '2026-01-01 00:00:00',
            'end_date' => '2026-01-15 00:00:00',
            'period_type' => 'semi_monthly',
        ]);
    }

    public function test_employee_can_view_own_payslip(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $period = PayrollPeriod::factory()->create();
        $payroll = Payroll::factory()->create([
            'user_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'status' => 'released',
            'is_posted' => true,
        ]);

        $response = $this->actingAs($employee)->get(route('payroll.my-payslip', $payroll));

        $response->assertStatus(200);
    }

    public function test_employee_cannot_view_other_payslip(): void
    {
        $employee1 = User::factory()->create(['role' => 'employee']);
        $employee2 = User::factory()->create(['role' => 'employee']);
        $period = PayrollPeriod::factory()->create();
        $payroll = Payroll::factory()->create([
            'user_id' => $employee2->id,
            'payroll_period_id' => $period->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($employee1)->get(route('payroll.my-payslip', $payroll));

        $response->assertStatus(403);
    }
}
