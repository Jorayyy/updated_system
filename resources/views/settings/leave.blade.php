<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('settings.index') }}" class="mr-4 text-gray-500 hover:text-gray-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Leave Management Settings') }}
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

            <form action="{{ route('settings.leave.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Leave Credits Policy -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Leave Credits Policy
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Configure how leave credits are managed</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Carryover Days</label>
                                <input type="number" name="max_leave_carryover" step="0.5" min="0" max="30" value="{{ $settings['max_leave_carryover'] ?? 5 }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Maximum days that can be carried over to next year</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Carryover Expiry (months)</label>
                                <input type="number" name="carryover_expiry_months" step="1" min="0" max="12" value="{{ $settings['carryover_expiry_months'] ?? 3 }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Months after year-end when carryover expires (0 = never)</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="allow_leave_carryover" value="1" {{ ($settings['allow_leave_carryover'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Allow Leave Credits Carryover</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="prorate_leave_credits" value="1" {{ ($settings['prorate_leave_credits'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Pro-rate Leave Credits for New Hires</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Leave Filing Rules -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Leave Filing Rules
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Configure leave filing requirements</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Min Advance Filing (days)</label>
                                <input type="number" name="min_advance_leave_filing" step="1" min="0" max="30" value="{{ $settings['min_advance_leave_filing'] ?? 3 }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Minimum days in advance to file planned leave</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Consecutive Leave Days</label>
                                <input type="number" name="max_consecutive_leave_days" step="1" min="1" max="30" value="{{ $settings['max_consecutive_leave_days'] ?? 10 }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Maximum consecutive days for single leave request</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="allow_backdated_leave" value="1" {{ ($settings['allow_backdated_leave'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Allow Backdated Leave Filing</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="require_leave_attachment" value="1" {{ ($settings['require_leave_attachment'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Require Supporting Documents (for sick leave, etc.)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Half-Day Leave -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Half-Day Leave Settings
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Configure half-day leave options</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="allow_half_day_leave" value="1" {{ ($settings['allow_half_day_leave'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Allow Half-Day Leave</span>
                            </label>
                            <div class="ml-6 text-sm text-gray-500">
                                <p>When enabled, employees can file for half-day leave (AM or PM)</p>
                                <p class="mt-1">Half-day leave will consume 0.5 credits from the leave balance</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval Hierarchy -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Approval Hierarchy
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Configure leave approval workflow</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Approval Levels</label>
                                <select name="leave_approval_levels" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="1" {{ ($settings['leave_approval_levels'] ?? 1) == 1 ? 'selected' : '' }}>1 Level (Direct Supervisor)</option>
                                    <option value="2" {{ ($settings['leave_approval_levels'] ?? 1) == 2 ? 'selected' : '' }}>2 Levels (Supervisor + Manager)</option>
                                    <option value="3" {{ ($settings['leave_approval_levels'] ?? 1) == 3 ? 'selected' : '' }}>3 Levels (Supervisor + Manager + HR)</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Number of approval levels required</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Auto-Approve After (days)</label>
                                <input type="number" name="auto_approve_after_days" step="1" min="0" max="14" value="{{ $settings['auto_approve_after_days'] ?? 0 }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Days before auto-approval (0 = disabled)</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="notify_approver_email" value="1" {{ ($settings['notify_approver_email'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Notify Approvers via Email</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="allow_approver_modification" value="1" {{ ($settings['allow_approver_modification'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Allow Approvers to Modify Leave Dates</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Leave Balance Display -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Leave Balance Display
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Configure how leave balances are displayed</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="show_leave_balance_dashboard" value="1" {{ ($settings['show_leave_balance_dashboard'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Show Leave Balance on Dashboard</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="show_pending_leaves_calendar" value="1" {{ ($settings['show_pending_leaves_calendar'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Show Team Leaves on Calendar</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('settings.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                        Cancel
                    </a>
                    <button type="submit" class="ml-4 px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
