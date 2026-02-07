<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveTypeRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:100|unique:leave_types,name',
            'code' => 'required|string|max:20|unique:leave_types,code',
            'description' => 'nullable|string|max:500',
            'days_per_year' => 'required|integer|min:0|max:365',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
            'requires_approval' => 'boolean',
            'can_carry_over' => 'boolean',
            'max_carry_over_days' => 'nullable|integer|min:0|max:365',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A leave type with this name already exists.',
            'code.unique' => 'A leave type with this code already exists.',
            'days_per_year.max' => 'Days per year cannot exceed 365.',
        ];
    }
}
