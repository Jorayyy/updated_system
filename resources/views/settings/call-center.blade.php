<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('settings.index') }}" class="mr-4 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Call Center Operations Settings') }}
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

            <form action="{{ route('settings.call-center.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Shift Types Configuration -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Shift Types
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure available shift types for agents</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Shift Types (comma-separated)</label>
                                <textarea name="shift_types" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500" placeholder="Morning,Mid,Night,Graveyard,Flexi">{{ $settings['shift_types'] ?? 'Morning,Mid,Night,Graveyard,Flexi' }}</textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enter shift names separated by commas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AUX Codes Configuration -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            AUX Codes
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Define auxiliary status codes for agents</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">AUX Codes (comma-separated)</label>
                                <textarea name="aux_codes" rows="4" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500" placeholder="Break,Lunch,Meeting,Training,Coaching,System Issue,Personal,Restroom">{{ $settings['aux_codes'] ?? 'Break,Lunch,Meeting,Training,Coaching,System Issue,Personal,Restroom' }}</textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Common AUX codes: Break, Lunch, Meeting, Training, Coaching, System Issue, Personal, Restroom</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overtime Settings -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Overtime Settings
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure overtime limits and controls</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Daily OT (hours)</label>
                                <input type="number" name="max_daily_ot_hours" step="0.5" min="0" max="8" value="{{ $settings['max_daily_ot_hours'] ?? 4 }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maximum overtime hours allowed per day</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Weekly OT (hours)</label>
                                <input type="number" name="max_weekly_ot_hours" step="0.5" min="0" max="40" value="{{ $settings['max_weekly_ot_hours'] ?? 16 }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maximum overtime hours allowed per week</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Monthly OT (hours)</label>
                                <input type="number" name="max_monthly_ot_hours" step="1" min="0" max="100" value="{{ $settings['max_monthly_ot_hours'] ?? 40 }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maximum overtime hours allowed per month</p>
                            </div>
                            <div class="flex items-center">
                                <label class="flex items-center">
                                    <input type="checkbox" name="require_ot_approval" value="1" {{ ($settings['require_ot_approval'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-purple-600 shadow-sm focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Require OT Pre-Approval</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedule Adherence -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Schedule Adherence
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure schedule adherence thresholds</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Target Adherence (%)</label>
                                <input type="number" name="schedule_adherence_target" step="1" min="0" max="100" value="{{ $settings['schedule_adherence_target'] ?? 95 }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Target schedule adherence percentage</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Warning Threshold (%)</label>
                                <input type="number" name="adherence_warning_threshold" step="1" min="0" max="100" value="{{ $settings['adherence_warning_threshold'] ?? 85 }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Threshold that triggers adherence warnings</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="track_schedule_adherence" value="1" {{ ($settings['track_schedule_adherence'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-purple-600 shadow-sm focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable Schedule Adherence Tracking</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Account/Campaign Assignment -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            Account/Campaign Assignment
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure account and campaign management</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="enable_multi_account" value="1" {{ ($settings['enable_multi_account'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-purple-600 shadow-sm focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Allow agents to be assigned to multiple accounts</span>
                                </label>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="track_account_hours" value="1" {{ ($settings['track_account_hours'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-purple-600 shadow-sm focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Track hours per account/campaign</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('settings.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                        Cancel
                    </a>
                    <button type="submit" class="ml-4 px-6 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
