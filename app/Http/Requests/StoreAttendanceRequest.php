<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date|before_or_equal:today',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i|after:time_in',
            'break_start' => 'nullable|date_format:H:i|after:time_in',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'notes' => 'nullable|string|max:500',
            'status' => 'required|in:present,absent,late,half_day,on_leave',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select an employee.',
            'user_id.exists' => 'The selected employee is invalid.',
            'date.before_or_equal' => 'Attendance date cannot be in the future.',
            'time_out.after' => 'Time out must be after time in.',
            'break_end.after' => 'Break end must be after break start.',
        ];
    }
}
