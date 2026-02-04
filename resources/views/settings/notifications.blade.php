<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('settings.index') }}" class="mr-4 text-gray-500 hover:text-gray-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notification Settings') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('settings.notifications.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Email Notifications -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Email Notifications
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Configure which events trigger email notifications</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Leave & Attendance</h4>
                            <div class="ml-4 space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="email_leave_request" value="1" {{ ($settings['email_leave_request'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700">New Leave Request (to approver)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="email_leave_approval" value="1" {{ ($settings['email_leave_approval'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700">Leave Approved/Rejected (to employee)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="email_attendance_issue" value="1" {{ ($settings['email_attendance_issue'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700">Attendance Issues (tardiness, undertime)</span>
                                </label>
                            </div>

                            <h4 class="text-sm font-medium text-gray-700 mb-3 mt-6">Payroll</h4>
                            <div class="ml-4 space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="email_payslip_ready" value="1" {{ ($settings['email_payslip_ready'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700">Payslip Ready for Viewing</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="email_payroll_processing" value="1" {{ ($settings['email_payroll_processing'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700">Payroll Processing Started (to HR/Admin)</span>
                                </label>
                            </div>

                            <h4 class="text-sm font-medium text-gray-700 mb-3 mt-6">System</h4>
                            <div class="ml-4 space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="email_account_created" value="1" {{ ($settings['email_account_created'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700">New Account Created (welcome email)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="email_password_change" value="1" {{ ($settings['email_password_change'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700">Password Changed</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- In-App Notifications -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            In-App Notifications
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Configure system notification bell alerts</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_pending_approvals" value="1" {{ ($settings['notify_pending_approvals'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Pending Approval Reminders</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_schedule_changes" value="1" {{ ($settings['notify_schedule_changes'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Schedule Changes</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_announcements" value="1" {{ ($settings['notify_announcements'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Company Announcements</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_birthday_anniversary" value="1" {{ ($settings['notify_birthday_anniversary'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Birthday & Work Anniversary Reminders</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Automated Reports -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Automated Reports
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Configure automated report generation</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="flex items-center mb-4">
                                    <input type="checkbox" name="send_daily_attendance_report" value="1" {{ ($settings['send_daily_attendance_report'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500">
                                    <span class="ml-2 text-sm text-gray-700">Daily Attendance Summary</span>
                                </label>
                                <div class="ml-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Send Time</label>
                                    <input type="time" name="daily_report_time" value="{{ $settings['daily_report_time'] ?? '18:00' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                </div>
                            </div>
                            <div>
                                <label class="flex items-center mb-4">
                                    <input type="checkbox" name="send_weekly_summary_report" value="1" {{ ($settings['send_weekly_summary_report'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500">
                                    <span class="ml-2 text-sm text-gray-700">Weekly Summary Report</span>
                                </label>
                                <div class="ml-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Send Day</label>
                                    <select name="weekly_report_day" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                        <option value="1" {{ ($settings['weekly_report_day'] ?? 1) == 1 ? 'selected' : '' }}>Monday</option>
                                        <option value="2" {{ ($settings['weekly_report_day'] ?? 1) == 2 ? 'selected' : '' }}>Tuesday</option>
                                        <option value="3" {{ ($settings['weekly_report_day'] ?? 1) == 3 ? 'selected' : '' }}>Wednesday</option>
                                        <option value="4" {{ ($settings['weekly_report_day'] ?? 1) == 4 ? 'selected' : '' }}>Thursday</option>
                                        <option value="5" {{ ($settings['weekly_report_day'] ?? 1) == 5 ? 'selected' : '' }}>Friday</option>
                                        <option value="6" {{ ($settings['weekly_report_day'] ?? 1) == 6 ? 'selected' : '' }}>Saturday</option>
                                        <option value="0" {{ ($settings['weekly_report_day'] ?? 1) == 0 ? 'selected' : '' }}>Sunday</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Report Recipients (comma-separated emails)</label>
                            <textarea name="report_recipients" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" placeholder="hr@company.com, admin@company.com">{{ $settings['report_recipients'] ?? '' }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Leave blank to send to all admins</p>
                        </div>
                    </div>
                </div>

                <!-- Reminder Settings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Reminder Settings
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Configure automatic reminders</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pending Approval Reminder (days)</label>
                                <input type="number" name="pending_approval_reminder_days" step="1" min="1" max="7" value="{{ $settings['pending_approval_reminder_days'] ?? 2 }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                <p class="text-xs text-gray-500 mt-1">Remind approvers after X days</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Leave Expiry Warning (days)</label>
                                <input type="number" name="leave_expiry_warning_days" step="1" min="7" max="60" value="{{ $settings['leave_expiry_warning_days'] ?? 30 }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                <p class="text-xs text-gray-500 mt-1">Warn about expiring leave credits X days before</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('settings.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                        Cancel
                    </a>
                    <button type="submit" class="ml-4 px-6 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
