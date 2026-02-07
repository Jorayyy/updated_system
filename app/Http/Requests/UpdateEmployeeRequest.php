<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isHr());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employeeId = $this->route('employee')->id ?? $this->route('employee');
        
        return [
            'employee_id' => 'required|string|max:50|unique:users,employee_id,' . $employeeId,
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $employeeId,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,hr,employee',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hourly_rate' => 'nullable|numeric|min:0|max:999999.99',
            'daily_rate' => 'nullable|numeric|min:0|max:999999.99',
            'monthly_salary' => 'nullable|numeric|min:0|max:99999999.99',
            'date_hired' => 'nullable|date|before_or_equal:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.unique' => 'This Employee ID is already taken.',
            'email.unique' => 'This email address is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
