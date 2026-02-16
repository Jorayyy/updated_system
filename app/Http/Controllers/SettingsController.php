<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $settings = CompanySetting::all()->groupBy('group');
        
        return view('settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($request->settings as $setting) {
            $existing = CompanySetting::where('key', $setting['key'])->first();
            if ($existing) {
                $existing->update(['value' => $setting['value'] ?? '']);
            }
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Company information settings
     */
    public function company()
    {
        $settings = CompanySetting::getByGroup('company');
        return view('settings.company', compact('settings'));
    }

    /**
     * Update company settings
     */
    public function updateCompany(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'company_tin' => 'nullable|string|max:50',
            'company_sss' => 'nullable|string|max:50',
            'company_philhealth' => 'nullable|string|max:50',
            'company_pagibig' => 'nullable|string|max:50',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('company_logo')) {
            // Store in 'public/uploads' directly instead of 'storage/app/public'
            // This bypasses the need for symlinks on shared hosting
            $file = $request->file('company_logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = 'uploads/company/' . $filename;
            
            // Ensure directory exists in public folder
            if (!file_exists(public_path('uploads/company'))) {
                mkdir(public_path('uploads/company'), 0755, true);
            }

            $file->move(public_path('uploads/company'), $filename);

            // Update setting with new path (relative to public root so asset() works correctly)
            CompanySetting::setValue('company_logo', $path, 'string', 'company');
        }

        foreach ($request->only([
            'company_name', 'company_address', 'company_phone', 'company_email', 'company_website',
            'company_tin', 'company_sss', 'company_philhealth', 'company_pagibig'
        ]) as $key => $value) {
            CompanySetting::setValue($key, $value ?? '', 'string', 'company');
        }

        return back()->with('success', 'Company settings updated successfully.');
    }

    /**
     * Payroll settings
     */
    public function payroll()
    {
        $settings = CompanySetting::getByGroup('payroll');
        $adjustmentTypes = \App\Models\PayrollAdjustmentType::all();
        
        return view('settings.payroll', compact('settings', 'adjustmentTypes'));
    }

    /**
     * Update payroll settings
     */
    public function updatePayroll(Request $request)
    {
        $rules = [
            'work_hours_per_day' => 'required|numeric|min:1|max:24',
            'work_days_per_month' => 'required|numeric|min:1|max:31',
            'overtime_rate_multiplier' => 'required|numeric|min:1|max:5',
            'night_diff_rate' => 'required|numeric|min:0|max:100',
            'night_diff_start' => 'required|date_format:H:i',
            'night_diff_end' => 'required|date_format:H:i',
            'late_deduction_per_minute' => 'required|numeric|min:0',
            'undertime_deduction_per_minute' => 'required|numeric|min:0',
            'holiday_rate_regular' => 'required|numeric|min:1|max:5',
            'holiday_rate_special' => 'required|numeric|min:1|max:5',
            'rest_day_rate' => 'required|numeric|min:1|max:5',
            'sss_enabled' => 'nullable',
            'philhealth_enabled' => 'nullable',
            'pagibig_enabled' => 'nullable',
            'tax_enabled' => 'nullable',
        ];

        // Add rules for each dynamic adjustment type
        $adjustmentTypes = \App\Models\PayrollAdjustmentType::all();
        foreach ($adjustmentTypes as $adj) {
            $rules['adj_formula_' . $adj->id] = 'required|string|max:255';
            $rules['adj_name_' . $adj->id] = 'required|string|max:100';
        }

        $request->validate($rules);

        CompanySetting::setValue('work_hours_per_day', $request->work_hours_per_day, 'decimal', 'payroll');
        CompanySetting::setValue('work_days_per_month', $request->work_days_per_month, 'decimal', 'payroll');
        CompanySetting::setValue('overtime_rate_multiplier', $request->overtime_rate_multiplier, 'decimal', 'payroll');
        CompanySetting::setValue('night_diff_rate', $request->night_diff_rate, 'decimal', 'payroll');
        CompanySetting::setValue('night_diff_start', $request->night_diff_start, 'string', 'payroll');
        CompanySetting::setValue('night_diff_end', $request->night_diff_end, 'string', 'payroll');
        CompanySetting::setValue('late_deduction_per_minute', $request->late_deduction_per_minute, 'decimal', 'payroll');
        CompanySetting::setValue('undertime_deduction_per_minute', $request->undertime_deduction_per_minute, 'decimal', 'payroll');
        CompanySetting::setValue('holiday_rate_regular', $request->holiday_rate_regular, 'decimal', 'payroll');
        CompanySetting::setValue('holiday_rate_special', $request->holiday_rate_special, 'decimal', 'payroll');
        CompanySetting::setValue('rest_day_rate', $request->rest_day_rate, 'decimal', 'payroll');
        CompanySetting::setValue('sss_enabled', $request->boolean('sss_enabled'), 'boolean', 'payroll');
        CompanySetting::setValue('philhealth_enabled', $request->boolean('philhealth_enabled'), 'boolean', 'payroll');
        CompanySetting::setValue('pagibig_enabled', $request->boolean('pagibig_enabled'), 'boolean', 'payroll');
        CompanySetting::setValue('tax_enabled', $request->boolean('tax_enabled'), 'boolean', 'payroll');

        // Update dynamic adjustment types
        foreach ($adjustmentTypes as $adj) {
            $adj->update([
                'name' => $request->get('adj_name_' . $adj->id),
                'default_formula' => $request->get('adj_formula_' . $adj->id),
            ]);
        }
        
        // Clear cache
        \Illuminate\Support\Facades\Cache::forget('payroll_adjustment_codes');

        return back()->with('success', 'Payroll settings updated successfully.');
    }

    /**
     * Add a new adjustment type
     */
    public function addAdjustmentType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:payroll_adjustment_types,code',
            'type' => 'required|in:earning,deduction',
            'target_field' => 'required|in:bonus,allowances,loan_deductions,other_deductions',
            'default_formula' => 'required|string|max:255',
        ]);

        \App\Models\PayrollAdjustmentType::create($validated);
        \Illuminate\Support\Facades\Cache::forget('payroll_adjustment_codes');

        return back()->with('success', 'Adjustment type added successfully.');
    }

    /**
     * Delete an adjustment type
     */
    public function deleteAdjustmentType(\App\Models\PayrollAdjustmentType $type)
    {
        if ($type->is_system_default) {
            return back()->with('error', 'Cannot delete system default adjustment types.');
        }

        $type->delete();
        \Illuminate\Support\Facades\Cache::forget('payroll_adjustment_codes');

        return back()->with('success', 'Adjustment type deleted successfully.');
    }

    /**
     * Attendance settings
     */
    public function attendance()
    {
        $settings = CompanySetting::getByGroup('attendance');
        return view('settings.attendance', compact('settings'));
    }

    /**
     * Update attendance settings
     */
    public function updateAttendance(Request $request)
    {
        $request->validate([
            'work_start_time' => 'required|date_format:H:i',
            'work_end_time' => 'required|date_format:H:i',
            'grace_period_minutes' => 'required|integer|min:0|max:60',
            'break_duration_minutes' => 'required|integer|min:0|max:180',
            'require_break' => 'boolean',
            'allow_early_time_in' => 'boolean',
            'early_time_in_minutes' => 'nullable|integer|min:0|max:120',
            'auto_timeout_enabled' => 'boolean',
            'auto_timeout_time' => 'nullable|date_format:H:i',
            'attendance_ip_restriction' => 'boolean',
        ]);

        CompanySetting::setValue('work_start_time', $request->work_start_time, 'string', 'attendance');
        CompanySetting::setValue('work_end_time', $request->work_end_time, 'string', 'attendance');
        CompanySetting::setValue('grace_period_minutes', $request->grace_period_minutes, 'integer', 'attendance');
        CompanySetting::setValue('break_duration_minutes', $request->break_duration_minutes, 'integer', 'attendance');
        CompanySetting::setValue('require_break', $request->boolean('require_break'), 'boolean', 'attendance');
        CompanySetting::setValue('allow_early_time_in', $request->boolean('allow_early_time_in'), 'boolean', 'attendance');
        CompanySetting::setValue('early_time_in_minutes', $request->early_time_in_minutes ?? 30, 'integer', 'attendance');
        CompanySetting::setValue('auto_timeout_enabled', $request->boolean('auto_timeout_enabled'), 'boolean', 'attendance');
        CompanySetting::setValue('auto_timeout_time', $request->auto_timeout_time ?? '23:59', 'string', 'attendance');
        CompanySetting::setValue('attendance_ip_restriction', $request->boolean('attendance_ip_restriction'), 'boolean', 'attendance');

        return back()->with('success', 'Attendance settings updated successfully.');
    }

    /**
     * Call Center specific settings
     */
    public function callCenter()
    {
        $settings = CompanySetting::getByGroup('call_center');
        return view('settings.call-center', compact('settings'));
    }

    /**
     * Update call center settings
     */
    public function updateCallCenter(Request $request)
    {
        $request->validate([
            'shift_types' => 'nullable|string|max:500',
            'default_shift_hours' => 'required|numeric|min:4|max:12',
            'allow_shift_bidding' => 'boolean',
            'track_aux_codes' => 'boolean',
            'aux_codes' => 'nullable|string|max:1000',
            'required_break_per_shift' => 'required|integer|min:0|max:4',
            'break_interval_hours' => 'required|numeric|min:1|max:8',
            'max_consecutive_work_days' => 'required|integer|min:5|max:7',
            'min_rest_between_shifts' => 'required|integer|min:8|max:24',
            'overtime_requires_approval' => 'boolean',
            'max_overtime_hours_daily' => 'required|numeric|min:0|max:8',
            'max_overtime_hours_weekly' => 'required|numeric|min:0|max:40',
            'account_assignment_enabled' => 'boolean',
            'track_schedule_adherence' => 'boolean',
            'adherence_threshold_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        CompanySetting::setValue('shift_types', $request->shift_types ?? 'Morning,Mid,Night,Graveyard', 'string', 'call_center');
        CompanySetting::setValue('default_shift_hours', $request->default_shift_hours, 'decimal', 'call_center');
        CompanySetting::setValue('allow_shift_bidding', $request->boolean('allow_shift_bidding'), 'boolean', 'call_center');
        CompanySetting::setValue('track_aux_codes', $request->boolean('track_aux_codes'), 'boolean', 'call_center');
        CompanySetting::setValue('aux_codes', $request->aux_codes ?? 'Break,Lunch,Meeting,Training,Coaching,System Issue', 'string', 'call_center');
        CompanySetting::setValue('required_break_per_shift', $request->required_break_per_shift, 'integer', 'call_center');
        CompanySetting::setValue('break_interval_hours', $request->break_interval_hours, 'decimal', 'call_center');
        CompanySetting::setValue('max_consecutive_work_days', $request->max_consecutive_work_days, 'integer', 'call_center');
        CompanySetting::setValue('min_rest_between_shifts', $request->min_rest_between_shifts, 'integer', 'call_center');
        CompanySetting::setValue('overtime_requires_approval', $request->boolean('overtime_requires_approval'), 'boolean', 'call_center');
        CompanySetting::setValue('max_overtime_hours_daily', $request->max_overtime_hours_daily, 'decimal', 'call_center');
        CompanySetting::setValue('max_overtime_hours_weekly', $request->max_overtime_hours_weekly, 'decimal', 'call_center');
        CompanySetting::setValue('account_assignment_enabled', $request->boolean('account_assignment_enabled'), 'boolean', 'call_center');
        CompanySetting::setValue('track_schedule_adherence', $request->boolean('track_schedule_adherence'), 'boolean', 'call_center');
        CompanySetting::setValue('adherence_threshold_percent', $request->adherence_threshold_percent ?? 85, 'decimal', 'call_center');

        return back()->with('success', 'Call center settings updated successfully.');
    }

    /**
     * Leave Management settings
     */
    public function leave()
    {
        $settings = CompanySetting::getByGroup('leave');
        return view('settings.leave', compact('settings'));
    }

    /**
     * Update leave settings
     */
    public function updateLeave(Request $request)
    {
        $request->validate([
            'leave_year_start_month' => 'required|integer|min:1|max:12',
            'auto_reset_leave_credits' => 'boolean',
            'allow_leave_carryover' => 'boolean',
            'max_carryover_days' => 'nullable|numeric|min:0|max:30',
            'advance_leave_filing_days' => 'required|integer|min:0|max:30',
            'emergency_leave_allowed' => 'boolean',
            'require_attachment_for_sick_leave' => 'boolean',
            'sick_leave_attachment_days' => 'nullable|integer|min:1|max:10',
            'allow_half_day_leave' => 'boolean',
            'max_consecutive_leave_days' => 'required|integer|min:1|max:30',
            'leave_approval_hierarchy' => 'nullable|string|max:100',
        ]);

        CompanySetting::setValue('leave_year_start_month', $request->leave_year_start_month, 'integer', 'leave');
        CompanySetting::setValue('auto_reset_leave_credits', $request->boolean('auto_reset_leave_credits'), 'boolean', 'leave');
        CompanySetting::setValue('allow_leave_carryover', $request->boolean('allow_leave_carryover'), 'boolean', 'leave');
        CompanySetting::setValue('max_carryover_days', $request->max_carryover_days ?? 5, 'decimal', 'leave');
        CompanySetting::setValue('advance_leave_filing_days', $request->advance_leave_filing_days, 'integer', 'leave');
        CompanySetting::setValue('emergency_leave_allowed', $request->boolean('emergency_leave_allowed'), 'boolean', 'leave');
        CompanySetting::setValue('require_attachment_for_sick_leave', $request->boolean('require_attachment_for_sick_leave'), 'boolean', 'leave');
        CompanySetting::setValue('sick_leave_attachment_days', $request->sick_leave_attachment_days ?? 3, 'integer', 'leave');
        CompanySetting::setValue('allow_half_day_leave', $request->boolean('allow_half_day_leave'), 'boolean', 'leave');
        CompanySetting::setValue('max_consecutive_leave_days', $request->max_consecutive_leave_days, 'integer', 'leave');
        CompanySetting::setValue('leave_approval_hierarchy', $request->leave_approval_hierarchy ?? 'team_lead,hr,admin', 'string', 'leave');

        return back()->with('success', 'Leave settings updated successfully.');
    }

    /**
     * Notification settings
     */
    public function notifications()
    {
        $settings = CompanySetting::getByGroup('notifications');
        return view('settings.notifications', compact('settings'));
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'email_notifications_enabled' => 'boolean',
            'notify_on_leave_request' => 'boolean',
            'notify_on_leave_approval' => 'boolean',
            'notify_on_attendance_issue' => 'boolean',
            'notify_on_payroll_release' => 'boolean',
            'notify_on_schedule_change' => 'boolean',
            'daily_report_enabled' => 'boolean',
            'daily_report_time' => 'nullable|date_format:H:i',
            'weekly_summary_enabled' => 'boolean',
            'weekly_summary_day' => 'nullable|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ]);

        CompanySetting::setValue('email_notifications_enabled', $request->boolean('email_notifications_enabled'), 'boolean', 'notifications');
        CompanySetting::setValue('notify_on_leave_request', $request->boolean('notify_on_leave_request'), 'boolean', 'notifications');
        CompanySetting::setValue('notify_on_leave_approval', $request->boolean('notify_on_leave_approval'), 'boolean', 'notifications');
        CompanySetting::setValue('notify_on_attendance_issue', $request->boolean('notify_on_attendance_issue'), 'boolean', 'notifications');
        CompanySetting::setValue('notify_on_payroll_release', $request->boolean('notify_on_payroll_release'), 'boolean', 'notifications');
        CompanySetting::setValue('notify_on_schedule_change', $request->boolean('notify_on_schedule_change'), 'boolean', 'notifications');
        CompanySetting::setValue('daily_report_enabled', $request->boolean('daily_report_enabled'), 'boolean', 'notifications');
        CompanySetting::setValue('daily_report_time', $request->daily_report_time ?? '08:00', 'string', 'notifications');
        CompanySetting::setValue('weekly_summary_enabled', $request->boolean('weekly_summary_enabled'), 'boolean', 'notifications');
        CompanySetting::setValue('weekly_summary_day', $request->weekly_summary_day ?? 'monday', 'string', 'notifications');

        return back()->with('success', 'Notification settings updated successfully.');
    }

    /**
     * System settings
     */
    public function system()
    {
        $settings = CompanySetting::getByGroup('system');
        return view('settings.system', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function updateSystem(Request $request)
    {
        $request->validate([
            'system_timezone' => 'required|string|max:50',
            'system_currency' => 'required|string|max:10',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
            'week_start_day' => 'required|integer|in:0,1',
            'session_timeout_minutes' => 'required|integer|min:5|max:480',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'require_2fa' => 'nullable',
            'log_all_activities' => 'nullable',
            'audit_log_retention_days' => 'required|integer|min:30|max:730',
            'notification_retention_days' => 'required|integer|min:7|max:90',
            'maintenance_mode' => 'nullable',
            'maintenance_message' => 'nullable|string|max:500',
            'automation_dtr_enabled' => 'nullable|boolean',
            'automation_dtr_day' => 'nullable|string|max:20',
        ]);

        CompanySetting::setValue('system_timezone', $request->system_timezone, 'string', 'system');
        CompanySetting::setValue('system_currency', $request->system_currency, 'string', 'system');
        CompanySetting::setValue('date_format', $request->date_format, 'string', 'system');
        CompanySetting::setValue('time_format', $request->time_format, 'string', 'system');
        CompanySetting::setValue('week_start_day', $request->week_start_day, 'integer', 'system');
        CompanySetting::setValue('session_timeout_minutes', $request->session_timeout_minutes, 'integer', 'system');
        CompanySetting::setValue('max_login_attempts', $request->max_login_attempts, 'integer', 'system');
        CompanySetting::setValue('require_2fa', $request->has('require_2fa'), 'boolean', 'system');
        CompanySetting::setValue('log_all_activities', $request->has('log_all_activities'), 'boolean', 'system');
        CompanySetting::setValue('audit_log_retention_days', $request->audit_log_retention_days, 'integer', 'system');
        CompanySetting::setValue('notification_retention_days', $request->notification_retention_days, 'integer', 'system');
        CompanySetting::setValue('maintenance_mode', $request->has('maintenance_mode'), 'boolean', 'system');
        CompanySetting::setValue('maintenance_message', $request->maintenance_message ?? 'We are currently performing scheduled maintenance. Please try again later.', 'string', 'system');
        
        CompanySetting::setValue('automation_dtr_enabled', $request->has('automation_dtr_enabled') ? $request->automation_dtr_enabled : true, 'boolean', 'system');
        CompanySetting::setValue('automation_dtr_day', $request->automation_dtr_day ?? 'Friday', 'string', 'system');

        return back()->with('success', 'System settings updated successfully.');
    }
}
