<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('settings.index') }}" class="mr-4 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('System Settings') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('settings.system.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Regional Settings -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Regional Settings
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure timezone and locale preferences</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">System Timezone</label>
                                <select name="system_timezone" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                    <option value="Asia/Manila" {{ ($settings['system_timezone'] ?? 'Asia/Manila') == 'Asia/Manila' ? 'selected' : '' }}>Asia/Manila (PHT UTC+8)</option>
                                    <option value="America/New_York" {{ ($settings['system_timezone'] ?? 'Asia/Manila') == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST/EDT)</option>
                                    <option value="America/Chicago" {{ ($settings['system_timezone'] ?? 'Asia/Manila') == 'America/Chicago' ? 'selected' : '' }}>America/Chicago (CST/CDT)</option>
                                    <option value="America/Denver" {{ ($settings['system_timezone'] ?? 'Asia/Manila') == 'America/Denver' ? 'selected' : '' }}>America/Denver (MST/MDT)</option>
                                    <option value="America/Los_Angeles" {{ ($settings['system_timezone'] ?? 'Asia/Manila') == 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles (PST/PDT)</option>
                                    <option value="Europe/London" {{ ($settings['system_timezone'] ?? 'Asia/Manila') == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT/BST)</option>
                                    <option value="Australia/Sydney" {{ ($settings['system_timezone'] ?? 'Asia/Manila') == 'Australia/Sydney' ? 'selected' : '' }}>Australia/Sydney (AEST/AEDT)</option>
                                    <option value="UTC" {{ ($settings['system_timezone'] ?? 'Asia/Manila') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                </select>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Default timezone for the system</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Currency</label>
                                <select name="system_currency" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                    <option value="PHP" {{ ($settings['system_currency'] ?? 'PHP') == 'PHP' ? 'selected' : '' }}>Philippine Peso (₱ PHP)</option>
                                    <option value="USD" {{ ($settings['system_currency'] ?? 'PHP') == 'USD' ? 'selected' : '' }}>US Dollar ($ USD)</option>
                                    <option value="EUR" {{ ($settings['system_currency'] ?? 'PHP') == 'EUR' ? 'selected' : '' }}>Euro (€ EUR)</option>
                                    <option value="GBP" {{ ($settings['system_currency'] ?? 'PHP') == 'GBP' ? 'selected' : '' }}>British Pound (£ GBP)</option>
                                    <option value="AUD" {{ ($settings['system_currency'] ?? 'PHP') == 'AUD' ? 'selected' : '' }}>Australian Dollar ($ AUD)</option>
                                    <option value="SGD" {{ ($settings['system_currency'] ?? 'PHP') == 'SGD' ? 'selected' : '' }}>Singapore Dollar ($ SGD)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date & Time Format -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Date & Time Format
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure how dates and times are displayed</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Format</label>
                                <select name="date_format" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                    <option value="M d, Y" {{ ($settings['date_format'] ?? 'M d, Y') == 'M d, Y' ? 'selected' : '' }}>{{ now()->format('M d, Y') }} (M d, Y)</option>
                                    <option value="d/m/Y" {{ ($settings['date_format'] ?? 'M d, Y') == 'd/m/Y' ? 'selected' : '' }}>{{ now()->format('d/m/Y') }} (d/m/Y)</option>
                                    <option value="m/d/Y" {{ ($settings['date_format'] ?? 'M d, Y') == 'm/d/Y' ? 'selected' : '' }}>{{ now()->format('m/d/Y') }} (m/d/Y)</option>
                                    <option value="Y-m-d" {{ ($settings['date_format'] ?? 'M d, Y') == 'Y-m-d' ? 'selected' : '' }}>{{ now()->format('Y-m-d') }} (Y-m-d)</option>
                                    <option value="d-M-Y" {{ ($settings['date_format'] ?? 'M d, Y') == 'd-M-Y' ? 'selected' : '' }}>{{ now()->format('d-M-Y') }} (d-M-Y)</option>
                                    <option value="F d, Y" {{ ($settings['date_format'] ?? 'M d, Y') == 'F d, Y' ? 'selected' : '' }}>{{ now()->format('F d, Y') }} (F d, Y)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time Format</label>
                                <select name="time_format" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                    <option value="h:i A" {{ ($settings['time_format'] ?? 'h:i A') == 'h:i A' ? 'selected' : '' }}>{{ now()->format('h:i A') }} (12-hour with AM/PM)</option>
                                    <option value="H:i" {{ ($settings['time_format'] ?? 'h:i A') == 'H:i' ? 'selected' : '' }}>{{ now()->format('H:i') }} (24-hour)</option>
                                    <option value="g:i A" {{ ($settings['time_format'] ?? 'h:i A') == 'g:i A' ? 'selected' : '' }}>{{ now()->format('g:i A') }} (12-hour no leading zero)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Week Start Day</label>
                            <select name="week_start_day" class="w-full md:w-1/2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                <option value="0" {{ ($settings['week_start_day'] ?? 0) == 0 ? 'selected' : '' }}>Sunday</option>
                                <option value="1" {{ ($settings['week_start_day'] ?? 0) == 1 ? 'selected' : '' }}>Monday</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Security Settings
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure security and session settings</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Session Timeout (minutes)</label>
                                <input type="number" name="session_timeout_minutes" step="5" min="5" max="480" value="{{ $settings['session_timeout_minutes'] ?? 120 }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Idle time before automatic logout</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Login Attempts</label>
                                <input type="number" name="max_login_attempts" step="1" min="3" max="10" value="{{ $settings['max_login_attempts'] ?? 5 }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Before account lockout</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="require_2fa" value="1" {{ ($settings['require_2fa'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-gray-600 shadow-sm focus:ring-gray-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Require Two-Factor Authentication for Admins</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="log_all_activities" value="1" {{ ($settings['log_all_activities'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-gray-600 shadow-sm focus:ring-gray-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Log All User Activities (Audit Trail)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Data Management -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                            </svg>
                            Data Management
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure data retention and backup settings</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Audit Log Retention (days)</label>
                                <input type="number" name="audit_log_retention_days" step="30" min="30" max="730" value="{{ $settings['audit_log_retention_days'] ?? 365 }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">How long to keep audit logs</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notification Retention (days)</label>
                                <input type="number" name="notification_retention_days" step="7" min="7" max="90" value="{{ $settings['notification_retention_days'] ?? 30 }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">How long to keep read notifications</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Mode -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Maintenance Mode
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Control system availability</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="maintenance_mode" value="1" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-yellow-600 shadow-sm focus:ring-yellow-500">
                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Enable Maintenance Mode</span>
                            </label>
                            <div class="ml-6 p-4 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                    <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    When enabled, only administrators can access the system. Regular users will see a maintenance page.
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Maintenance Message</label>
                                <textarea name="maintenance_message" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" placeholder="We are currently performing scheduled maintenance. Please try again later.">{{ $settings['maintenance_message'] ?? 'We are currently performing scheduled maintenance. Please try again later.' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('settings.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                        Cancel
                    </a>
                    <button type="submit" class="ml-4 px-6 py-2 bg-gray-700 text-white text-sm font-medium rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
