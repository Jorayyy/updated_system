<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Payroll Period') }}
            </h2>
            <a href="{{ route('payroll.computation.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Back to Command Center
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between relative">
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 bg-indigo-600 text-white rounded-full flex items-center justify-center mx-auto mb-2 font-bold shadow-lg ring-4 ring-indigo-100 relative z-10">1</div>
                        <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Define Period</span>
                    </div>
                    <div class="w-[80%] absolute top-5 left-[10%] -z-10 h-0.5 bg-gray-200"></div>
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 bg-white border-2 border-gray-300 text-gray-500 rounded-full flex items-center justify-center mx-auto mb-2 font-bold relative z-10">2</div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Process DTR</span>
                    </div>
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 bg-white border-2 border-gray-300 text-gray-500 rounded-full flex items-center justify-center mx-auto mb-2 font-bold relative z-10">3</div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Review & Post</span>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl border border-gray-100">
                <div class="p-8">
                    <div class="mb-8 border-b pb-6">
                        <h3 class="text-lg font-bold text-gray-900">Step 1: Set Payroll Range</h3>
                        <p class="text-sm text-gray-600 mt-1">Define the date range and payroll type. The system will automatically suggest end dates and pay dates based on your selection.</p>
                    </div>

                    <form method="POST" action="{{ route('payroll.store-period') }}">
                        @csrf

                        <div class="space-y-6">
                            <!-- Cover Year and Month -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="cover_year" :value="__('Cover Year')" />
                                    <select name="cover_year" id="cover_year" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                            <option value="{{ $y }}" {{ old('cover_year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                    <x-input-error :messages="$errors->get('cover_year')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="cover_month" :value="__('Cover Month')" />
                                    <select name="cover_month" id="cover_month" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}" {{ old('cover_month', date('F')) == $month ? 'selected' : '' }}>{{ $month }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('cover_month')" class="mt-2" />
                                </div>
                            </div>
                            
                            <!-- Cut-Off Label -->
                            <div>
                                <x-input-label for="cut_off_label" :value="__('Cut-Off Label')" />
                                <x-text-input id="cut_off_label" name="cut_off_label" type="text" class="mt-1 block w-full" 
                                    :value="old('cut_off_label')" placeholder="e.g. 1st Cut-off, 2nd Cut-off, Month Long" />
                                <x-input-error :messages="$errors->get('cut_off_label')" class="mt-2" />
                            </div>

                            <!-- Period Type (Hidden but kept for compatibility) -->
                            <input type="hidden" name="period_type" id="period_type" value="weekly">
                            <div class="p-4 bg-blue-50 border-l-4 border-blue-400 text-blue-800 text-sm font-medium rounded-r-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Payroll cycle is set to <span class="font-bold ml-1">Working Week</span> (Monday to Friday)
                                </div>
                            </div>

                            <!-- Payroll Group -->
                            <div>
                                <x-input-label for="payroll_group_id" :value="__('Payroll Group (Optional)')" />
                                <div class="text-xs text-gray-500 mb-1">Select a group to limit this period to specific employees. Leave empty for global payroll.</div>
                                <select name="payroll_group_id" id="payroll_group_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Global / All Unassigned --</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ old('payroll_group_id', $selectedGroupId ?? '') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('payroll_group_id')" class="mt-2" />
                            </div>

                            <!-- Date Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="start_date" :value="__('Start Date')" />
                                    <x-text-input type="date" name="start_date" id="start_date" 
                                        class="mt-1 block w-full"
                                        :value="old('start_date')" required />
                                    <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="end_date" :value="__('End Date')" />
                                    <x-text-input type="date" name="end_date" id="end_date" 
                                        class="mt-1 block w-full"
                                        :value="old('end_date')" required />
                                    <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Pay Date -->
                            <div>
                                <x-input-label for="pay_date" :value="__('Pay Date')" />
                                <x-text-input type="date" name="pay_date" id="pay_date" 
                                    class="mt-1 block w-full"
                                    :value="old('pay_date')" required />
                                <p class="text-xs text-gray-500 mt-1">The date employees get paid (Optimized for <span class="text-blue-600 font-bold">Every Friday</span> for weekly payroll)</p>
                                <x-input-error :messages="$errors->get('pay_date')" class="mt-2" />
                            </div>

                            <!-- Description -->
                            <div>
                                <x-input-label for="description" :value="__('Notes/Description')" />
                                <textarea id="description" name="description" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Optional notes for this payroll period...">{{ old('description') }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <!-- Info Box -->
                            <div class="p-5 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100 flex gap-4">
                                <div class="bg-blue-100 p-2 rounded-lg h-fit text-blue-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <h4 class="text-sm font-bold text-blue-900 uppercase tracking-tight">System Workflow Guide</h4>
                                    <ul class="text-sm text-blue-800 space-y-1.5 list-disc list-inside">
                                        <li><span class="font-semibold">Creation:</span> Period starts as a <span class="px-2 py-0.5 bg-blue-100 rounded text-xs">Draft</span>.</li>
                                        <li><span class="font-semibold">Calculation:</span> Use the <span class="font-bold">Process</span> button on the next screen to scan all DTRs.</li>
                                        <li><span class="font-semibold">Release:</span> Once approved, employees can view their payslips in their Profile.</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="flex items-center justify-end gap-4 pt-4 border-t">
                                <a href="{{ route('payroll.computation.dashboard') }}" class="text-gray-600 hover:text-gray-800">
                                    Cancel
                                </a>
                                <x-primary-button>
                                    {{ __('Create Payroll Period') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const payDate = document.getElementById('pay_date');

            function updateDates() {
                if (!startDate.value) return;
                
                const start = new Date(startDate.value);
                
                // End date is always 4 days after (Monday -> Friday)
                const end = new Date(start);
                end.setDate(end.getDate() + 4);
                
                // Pay date is exactly 7 days after the start date (Next Monday)
                // BUT user wants Payday ALWAYS Friday.
                // If the period ends on Friday, the payday is the NEXT Friday.
                const pay = new Date(end);
                pay.setDate(pay.getDate() + 7); // Move to next week Friday

                endDate.value = end.toISOString().split('T')[0];
                payDate.value = pay.toISOString().split('T')[0];
            }

            startDate.addEventListener('change', updateDates);
            
            // Initial call if start date is pre-filled
            if(startDate.value) updateDates();
        });
    </script>
    @endpush
</x-app-layout>
