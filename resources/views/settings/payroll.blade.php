<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('settings.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payroll Settings') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('settings.payroll.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">Work Schedule</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Work Hours Per Day -->
                                <div>
                                    <x-input-label for="work_hours_per_day" :value="__('Work Hours Per Day')" />
                                    <x-text-input id="work_hours_per_day" name="work_hours_per_day" type="number" step="0.5" class="mt-1 block w-full" 
                                        :value="old('work_hours_per_day', $settings['work_hours_per_day'] ?? 8)" required />
                                    <x-input-error :messages="$errors->get('work_hours_per_day')" class="mt-2" />
                                </div>

                                <!-- Work Days Per Month -->
                                <div>
                                    <x-input-label for="work_days_per_month" :value="__('Work Days Per Month')" />
                                    <x-text-input id="work_days_per_month" name="work_days_per_month" type="number" step="0.5" class="mt-1 block w-full" 
                                        :value="old('work_days_per_month', $settings['work_days_per_month'] ?? 22)" required />
                                    <x-input-error :messages="$errors->get('work_days_per_month')" class="mt-2" />
                                </div>
                            </div>

                            <hr class="my-6">
                            <h3 class="text-lg font-medium text-gray-900">Rate Multipliers</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Overtime Rate Multiplier -->
                                <div>
                                    <x-input-label for="overtime_rate_multiplier" :value="__('Overtime Rate Multiplier')" />
                                    <x-text-input id="overtime_rate_multiplier" name="overtime_rate_multiplier" type="number" step="0.01" class="mt-1 block w-full" 
                                        :value="old('overtime_rate_multiplier', $settings['overtime_rate_multiplier'] ?? 1.25)" required />
                                    <p class="mt-1 text-sm text-gray-500">Standard OT is 1.25 (125%)</p>
                                    <x-input-error :messages="$errors->get('overtime_rate_multiplier')" class="mt-2" />
                                </div>

                                <!-- Night Diff Rate -->
                                <div>
                                    <x-input-label for="night_diff_rate" :value="__('Night Differential Rate (%)')" />
                                    <x-text-input id="night_diff_rate" name="night_diff_rate" type="number" step="0.01" class="mt-1 block w-full" 
                                        :value="old('night_diff_rate', $settings['night_diff_rate'] ?? 10)" required />
                                    <p class="mt-1 text-sm text-gray-500">Additional % for night shift (10pm-6am)</p>
                                    <x-input-error :messages="$errors->get('night_diff_rate')" class="mt-2" />
                                </div>
                            </div>

                            <hr class="my-6">
                            <h3 class="text-lg font-medium text-gray-900">Deduction Rates</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Late Deduction -->
                                <div>
                                    <x-input-label for="late_deduction_per_minute" :value="__('Late Deduction Per Minute (₱)')" />
                                    <x-text-input id="late_deduction_per_minute" name="late_deduction_per_minute" type="number" step="0.01" class="mt-1 block w-full" 
                                        :value="old('late_deduction_per_minute', $settings['late_deduction_per_minute'] ?? 0)" required />
                                    <x-input-error :messages="$errors->get('late_deduction_per_minute')" class="mt-2" />
                                </div>

                                <!-- Undertime Deduction -->
                                <div>
                                    <x-input-label for="undertime_deduction_per_minute" :value="__('Undertime Deduction Per Minute (₱)')" />
                                    <x-text-input id="undertime_deduction_per_minute" name="undertime_deduction_per_minute" type="number" step="0.01" class="mt-1 block w-full" 
                                        :value="old('undertime_deduction_per_minute', $settings['undertime_deduction_per_minute'] ?? 0)" required />
                                    <x-input-error :messages="$errors->get('undertime_deduction_per_minute')" class="mt-2" />
                                </div>
                            </div>

                            <hr class="my-6">
                            <h3 class="text-lg font-medium text-gray-900">Government Contributions</h3>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="sss_enabled" value="1" 
                                            {{ old('sss_enabled', $settings['sss_enabled'] ?? true) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-600">SSS</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="philhealth_enabled" value="1" 
                                            {{ old('philhealth_enabled', $settings['philhealth_enabled'] ?? true) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-600">PhilHealth</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="pagibig_enabled" value="1" 
                                            {{ old('pagibig_enabled', $settings['pagibig_enabled'] ?? true) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-600">Pag-IBIG</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="tax_enabled" value="1" 
                                            {{ old('tax_enabled', $settings['tax_enabled'] ?? true) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-600">Withholding Tax</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Save Changes') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
