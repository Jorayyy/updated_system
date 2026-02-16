<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Adjust Payroll: ') . $payroll->user->name }}
            </h2>
            <a href="javascript:void(0)" onclick="window.history.back()" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <form action="{{ route('payroll.computation.update', $payroll) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    {{-- Employee Payroll Profile (Moved from Employee Edit) --}}
                    <div class="mb-10 bg-indigo-50/50 p-6 rounded-xl border border-indigo-100 shadow-sm">
                        <div class="flex items-center gap-2 mb-6 border-b border-indigo-200 pb-3">
                            <div class="p-2 bg-indigo-600 rounded-lg shadow-sm">
                                <i class="fas fa-id-card text-white"></i>
                            </div>
                            <h3 class="text-sm font-black text-indigo-900 uppercase tracking-widest whitespace-nowrap">Base Rate & Incentive Structure</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                             <div>
                                <label for="monthly_salary" class="block text-[10px] font-black text-indigo-600 uppercase mb-1">Monthly Salary</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-indigo-400 font-bold z-20">₱</span>
                                    <input type="number" step="0.01" name="monthly_salary" id="monthly_salary" value="{{ old('monthly_salary', $payroll->user->monthly_salary) }}"
                                        class="block w-full bg-indigo-50/50 border-indigo-200 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm font-bold text-indigo-900" 
                                        style="padding-left: 3rem !important;" readonly>
                                </div>
                                <p class="text-[9px] text-indigo-400 mt-1 uppercase font-bold tracking-tighter">Calculated: Daily × 26</p>
                            </div>
                            <div>
                                <label for="daily_rate" class="block text-[10px] font-black text-indigo-600 uppercase mb-1">Daily Rate</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-indigo-400 font-bold z-20">₱</span>
                                    <input type="number" step="0.01" name="daily_rate" id="daily_rate" value="{{ old('daily_rate', $payroll->user->daily_rate) }}"
                                        class="block w-full bg-white border-indigo-400 border-2 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm font-bold text-indigo-900 ring-2 ring-indigo-50 shadow-inner"
                                        style="padding-left: 3rem !important;">
                                </div>
                                <p class="text-[9px] text-indigo-800 mt-1 uppercase font-black animate-pulse tracking-tighter">← EDIT THIS FIELD</p>
                            </div>
                            <div>
                                <label for="hourly_rate" class="block text-[10px] font-black text-indigo-600 uppercase mb-1">Hourly Rate</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-indigo-400 font-bold z-20">₱</span>
                                    <input type="number" step="0.01" name="hourly_rate" id="hourly_rate" value="{{ old('hourly_rate', $payroll->user->hourly_rate) }}"
                                        class="block w-full bg-indigo-50/50 border-indigo-200 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm font-bold text-indigo-900" 
                                        style="padding-left: 3rem !important;" readonly>
                                </div>
                                <p class="text-[9px] text-indigo-400 mt-1 uppercase font-bold tracking-tighter">Calculated: Daily ÷ 8</p>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-lg border border-indigo-100">
                            <p class="text-[10px] font-black text-indigo-900 uppercase tracking-widest mb-4 flex items-center">
                                <i class="fas fa-gift mr-2 text-indigo-500"></i> Modular Bonuses & Incentives
                            </p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label for="perfect_attendance_bonus" class="block text-[9px] font-bold text-gray-500 uppercase">Perfect Attend. (Flat)</label>
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none text-indigo-400 font-bold z-20 text-[10px]">₱</span>
                                        <input type="number" step="0.01" name="perfect_attendance_bonus" id="perfect_attendance_bonus" value="{{ old('perfect_attendance_bonus', $payroll->user->perfect_attendance_bonus) }}"
                                            class="block w-full border-gray-200 rounded-md shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500" style="padding-left: 1.5rem !important;">
                                    </div>
                                </div>
                                <div>
                                    <label for="attendance_incentive" class="block text-[9px] font-bold text-gray-500 uppercase">Attend. Incent. (Daily)</label>
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none text-indigo-400 font-bold z-20 text-[10px]">₱</span>
                                        <input type="number" step="0.01" name="attendance_incentive" id="attendance_incentive" value="{{ old('attendance_incentive', $payroll->user->attendance_incentive) }}"
                                            class="block w-full border-gray-200 rounded-md shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500" style="padding-left: 1.5rem !important;">
                                    </div>
                                </div>
                                <div>
                                    <label for="site_incentive" class="block text-[9px] font-bold text-gray-500 uppercase">Site Incentive (Daily)</label>
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none text-indigo-400 font-bold z-20 text-[10px]">₱</span>
                                        <input type="number" step="0.01" name="site_incentive" id="site_incentive" value="{{ old('site_incentive', $payroll->user->site_incentive) }}"
                                            class="block w-full border-gray-200 rounded-md shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500" style="padding-left: 1.5rem !important;">
                                    </div>
                                </div>
                                <div>
                                    <label for="cola" class="block text-[9px] font-bold text-gray-500 uppercase">COLA (Daily)</label>
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none text-indigo-400 font-bold z-20 text-[10px]">₱</span>
                                        <input type="number" step="0.01" name="cola" id="cola" value="{{ old('cola', $payroll->user->cola) }}"
                                            class="block w-full border-gray-200 rounded-md shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500" style="padding-left: 1.5rem !important;">
                                    </div>
                                </div>
                                <div>
                                    <label for="meal_allowance" class="block text-[9px] font-bold text-gray-500 uppercase">Meal Allowance</label>
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none text-indigo-400 font-bold z-20 text-[10px]">₱</span>
                                        <input type="number" step="0.01" name="meal_allowance" id="meal_allowance" value="{{ old('meal_allowance', $payroll->user->meal_allowance) }}"
                                            class="block w-full border-gray-200 rounded-md shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500" style="padding-left: 1.5rem !important;">
                                    </div>
                                </div>
                                <div>
                                    <label for="transportation_allowance" class="block text-[9px] font-bold text-gray-500 uppercase">Transpo Allowance</label>
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none text-indigo-400 font-bold z-20 text-[10px]">₱</span>
                                        <input type="number" step="0.01" name="transportation_allowance" id="transportation_allowance" value="{{ old('transportation_allowance', $payroll->user->transportation_allowance) }}"
                                            class="block w-full border-gray-200 rounded-md shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500" style="padding-left: 1.5rem !important;">
                                    </div>
                                </div>
                                <div>
                                    <label for="communication_allowance" class="block text-[9px] font-bold text-gray-500 uppercase">Comm Allowance</label>
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none text-indigo-400 font-bold z-20 text-[10px]">₱</span>
                                        <input type="number" step="0.01" name="communication_allowance" id="communication_allowance" value="{{ old('communication_allowance', $payroll->user->communication_allowance) }}"
                                            class="block w-full border-gray-200 rounded-md shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500" style="padding-left: 1.5rem !important;">
                                    </div>
                                </div>
                                <div>
                                    <label for="other_allowance" class="block text-[9px] font-bold text-gray-500 uppercase">Other Allowance</label>
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none text-indigo-400 font-bold z-20 text-[10px]">₱</span>
                                        <input type="number" step="0.01" name="other_allowance" id="other_allowance" value="{{ old('other_allowance', $payroll->user->other_allowance) }}"
                                            class="block w-full border-gray-200 rounded-md shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500" style="padding-left: 1.5rem !important;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        {{-- Earnings Section --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-green-700 border-b pb-2">Automatic Earnings (from DTR)</h3>
                            
                            <div>
                                <x-input-label for="basic_pay" :value="__('Basic Pay')" />
                                <div class="relative mt-1">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 font-bold z-20">₱</span>
                                    <x-text-input id="basic_pay" name="basic_pay" type="number" step="0.01" class="block w-full bg-gray-50" style="padding-left: 3rem !important;" :value="old('basic_pay', $payroll->basic_pay)" required />
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1 italic uppercase font-bold tracking-tight">From approved DTR hours & rate</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="overtime_pay" :value="__('Overtime Pay')" />
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 font-bold z-20">₱</span>
                                        <x-text-input id="overtime_pay" name="overtime_pay" type="number" step="0.01" class="block w-full bg-gray-50" style="padding-left: 3rem !important;" :value="old('overtime_pay', $payroll->overtime_pay)" required />
                                    </div>
                                </div>
                                <div>
                                    <x-input-label for="holiday_pay" :value="__('Holiday Pay')" />
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 font-bold z-20">₱</span>
                                        <x-text-input id="holiday_pay" name="holiday_pay" type="number" step="0.01" class="block w-full bg-gray-50" style="padding-left: 3rem !important;" :value="old('holiday_pay', $payroll->holiday_pay)" required />
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="night_diff_pay" :value="__('Night Diff.')" />
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 font-bold z-20">₱</span>
                                        <x-text-input id="night_diff_pay" name="night_diff_pay" type="number" step="0.01" class="block w-full bg-gray-50" style="padding-left: 3rem !important;" :value="old('night_diff_pay', $payroll->night_diff_pay)" required />
                                    </div>
                                </div>
                                <div>
                                    <x-input-label for="rest_day_pay" :value="__('Rest Day')" />
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 font-bold z-20">₱</span>
                                        <x-text-input id="rest_day_pay" name="rest_day_pay" type="number" step="0.01" class="block w-full bg-gray-50" style="padding-left: 3rem !important;" :value="old('rest_day_pay', $payroll->rest_day_pay)" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Manual Adjustments --}}
                        <div class="space-y-4 bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                            <h3 class="text-lg font-bold text-indigo-700 border-b border-indigo-200 pb-2">Manual Adjustments</h3>

                            {{-- Bonus / Additions Dropdown --}}
                            <div class="p-3 bg-white rounded border border-green-200">
                                <label class="block font-medium text-sm text-green-700 mb-1">Bonus / Additions</label>
                                <div class="mb-2">
                                    <select class="adjustment-selector block w-full text-xs border-gray-300 rounded-md" data-target="bonus">
                                        <option value="">-- Apply Preset --</option>
                                        @foreach($adjustmentTypes->where('target_field', 'bonus') as $adj)
                                            <option value="{{ $adj->code }}" data-formula="{{ $adj->default_formula }}">{{ $adj->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 text-sm z-20">₱</span>
                                    <x-text-input id="bonus" name="bonus" type="number" step="0.01" class="block w-full bg-green-50/30 border-green-100 font-bold" style="padding-left: 3rem !important;" :value="old('bonus', $payroll->bonus)" required />
                                </div>
                                {{-- Breakdown Display --}}
                                <div id="breakdown-bonus" class="mt-1 flex flex-wrap gap-1"></div>
                                
                                <label class="block font-medium text-sm text-green-700 mt-4 mb-1">Allowances</label>
                                <div class="mb-2">
                                    <select class="adjustment-selector block w-full text-xs border-gray-300 rounded-md" data-target="allowances">
                                        <option value="">-- Apply Preset --</option>
                                        @foreach($adjustmentTypes->where('target_field', 'allowances') as $adj)
                                            <option value="{{ $adj->code }}" data-formula="{{ $adj->default_formula }}">{{ $adj->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 text-sm z-20">₱</span>
                                    <x-text-input id="allowances" name="allowances" type="number" step="0.01" class="block w-full bg-green-50/30 border-green-100 font-bold" style="padding-left: 3rem !important;" :value="old('allowances', $payroll->allowances)" required />
                                </div>
                                {{-- Breakdown Display --}}
                                <div id="breakdown-allowances" class="mt-1 flex flex-wrap gap-1"></div>
                            </div>

                            {{-- Deductions Dropdowns --}}
                            <div class="p-3 bg-white rounded border border-red-200 mt-4">
                                <label class="block font-medium text-sm text-red-700 mb-1">Loan Deductions</label>
                                <div class="mb-2">
                                    <select class="adjustment-selector block w-full text-xs border-gray-300 rounded-md" data-target="loan_deductions">
                                        <option value="">-- Apply Preset --</option>
                                        @foreach($adjustmentTypes->where('target_field', 'loan_deductions') as $adj)
                                            <option value="{{ $adj->code }}" data-formula="{{ $adj->default_formula }}">{{ $adj->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 text-sm z-20">₱</span>
                                    <x-text-input id="loan_deductions" name="loan_deductions" type="number" step="0.01" class="block w-full bg-red-50/30 border-red-100 font-bold" style="padding-left: 3rem !important;" :value="old('loan_deductions', $payroll->loan_deductions)" required />
                                </div>
                                {{-- Breakdown Display --}}
                                <div id="breakdown-loan_deductions" class="mt-1 flex flex-wrap gap-1"></div>

                                <label class="block font-medium text-sm text-red-700 mt-4 mb-1">Other Deductions</label>
                                <div class="mb-2">
                                    <select class="adjustment-selector block w-full text-xs border-gray-300 rounded-md" data-target="other_deductions">
                                        <option value="">-- Apply Preset --</option>
                                        @foreach($adjustmentTypes->where('target_field', 'other_deductions') as $adj)
                                            <option value="{{ $adj->code }}" data-formula="{{ $adj->default_formula }}">{{ $adj->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 text-sm z-20">₱</span>
                                    <x-text-input id="other_deductions" name="other_deductions" type="number" step="0.01" class="block w-full bg-red-50/30 border-red-100 font-bold" style="padding-left: 3rem !important;" :value="old('other_deductions', $payroll->other_deductions)" required />
                                </div>
                                {{-- Breakdown Display --}}
                                <div id="breakdown-other_deductions" class="mt-1 flex flex-wrap gap-1"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Deductions Hidden/Footer --}}
                    <div class="mt-8 pt-8 border-t">
                        <h3 class="text-lg font-bold text-gray-700 mb-4">Statutory Deductions (Fixed/Calculated)</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <x-input-label for="sss_contribution" :value="__('SSS')" />
                                <div class="relative mt-1">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 font-bold z-20">₱</span>
                                    <x-text-input id="sss_contribution" name="sss_contribution" type="number" step="0.01" class="block w-full bg-gray-50" style="padding-left: 3rem !important;" :value="old('sss_contribution', $payroll->sss_contribution)" required />
                                </div>
                            </div>
                            <div>
                                <x-input-label for="philhealth_contribution" :value="__('PhilHealth')" />
                                <div class="relative mt-1">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 font-bold z-20">₱</span>
                                    <x-text-input id="philhealth_contribution" name="philhealth_contribution" type="number" step="0.01" class="block w-full bg-gray-50" style="padding-left: 3rem !important;" :value="old('philhealth_contribution', $payroll->philhealth_contribution)" required />
                                </div>
                            </div>
                            <div>
                                <x-input-label for="pagibig_contribution" :value="__('Pag-IBIG')" />
                                <div class="relative mt-1">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 font-bold z-20">₱</span>
                                    <x-text-input id="pagibig_contribution" name="pagibig_contribution" type="number" step="0.01" class="block w-full bg-gray-50" style="padding-left: 3rem !important;" :value="old('pagibig_contribution', $payroll->pagibig_contribution)" required />
                                </div>
                            </div>
                            <div>
                                <x-input-label for="withholding_tax" :value="__('Tax')" />
                                <div class="relative mt-1">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 font-bold z-20">₱</span>
                                    <x-text-input id="withholding_tax" name="withholding_tax" type="number" step="0.01" class="block w-full bg-gray-50" style="padding-left: 3rem !important;" :value="old('withholding_tax', $payroll->withholding_tax)" required />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <x-input-label for="adjustment_reason" :value="__('Reason for Adjustment')" />
                        <textarea id="adjustment_reason" name="adjustment_reason" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3" required placeholder="Explain why these changes are being made...">{{ old('adjustment_reason', $payroll->adjustment_reason) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('adjustment_reason')" />
                    </div>

                    <div class="flex items-center justify-end border-t pt-6">
                        <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                            {{ __('Cancel') }}
                        </x-secondary-button>
                        <x-primary-button>
                            {{ __('Save Adjustments') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
            
            <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Note:</strong> Gross Pay, Total Deductions, and Net Pay will be automatically recalculated based on your inputs. Attendance-based deductions (Late, Undertime, Absences) are preserved from the original computation.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectors = document.querySelectorAll('.adjustment-selector');
            const plusButtons = document.querySelectorAll('.plus-btn');
            const reasonBox = document.getElementById('adjustment_reason');
            const adjustmentSettings = @json($adjustmentSettings);
            const context = @json($formulaContext);
            
            // Handle Rate Synchronization (Daily to Monthly/Hourly)
            const dailyInput = document.getElementById('daily_rate');
            const monthlyInput = document.getElementById('monthly_salary');
            const hourlyInput = document.getElementById('hourly_rate');

            if (dailyInput) {
                dailyInput.addEventListener('input', function() {
                    const daily = parseFloat(this.value) || 0;
                    if (monthlyInput) monthlyInput.value = (daily * 26).toFixed(2);
                    if (hourlyInput) hourlyInput.value = (daily / 8).toFixed(2);
                    
                    // Update context for formulas
                    context.daily = daily;
                    context.hourly = daily / 8;
                    context.basic = monthlyInput ? parseFloat(monthlyInput.value) : (daily * 26);
                });
            }

            // Track added items in an object
            const itemsTrack = {
                bonus: [],
                allowances: [],
                loan_deductions: [],
                other_deductions: []
            };

            // Initialize with current values as "Starting Balance"
            ['bonus', 'allowances', 'loan_deductions', 'other_deductions'].forEach(id => {
                const el = document.getElementById(id);
                if (el && parseFloat(el.value) > 0) {
                    itemsTrack[id].push({
                        label: 'Starting Balance',
                        amount: parseFloat(el.value)
                    });
                    updateBreakdownDisplay(id);
                }
            });

            function evaluateFormula(formula) {
                if (!formula) return 0;
                if (!isNaN(formula) && !isNaN(parseFloat(formula))) return parseFloat(formula);
                try {
                    let expression = formula.toString();
                    Object.keys(context).forEach(key => {
                        const regex = new RegExp('{' + key + '}', 'g');
                        expression = expression.replace(regex, context[key]);
                    });
                    expression = expression.replace(/\s+/g, '');
                    if (/[^-+*/().0-9]/.test(expression)) return 0;
                    return new Function('return ' + expression)();
                } catch (e) {
                    return 0;
                }
            }

            function updateBreakdownDisplay(targetId) {
                const container = document.getElementById('breakdown-' + targetId);
                if (!container) return;
                
                container.innerHTML = '';
                itemsTrack[targetId].forEach((item, index) => {
                    const badge = document.createElement('span');
                    badge.className = `inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border ${targetId.includes('deduction') ? 'bg-red-50 text-red-700 border-red-100' : 'bg-green-50 text-green-700 border-green-100'}`;
                    badge.innerHTML = `
                        ${item.label}: ₱${item.amount.toFixed(2)}
                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none" onclick="removeItem('${targetId}', ${index})">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    `;
                    container.appendChild(badge);
                });

                // Update the actual input value
                const total = itemsTrack[targetId].reduce((sum, item) => sum + item.amount, 0);
                const input = document.getElementById(targetId);
                if (input) {
                    input.value = total.toFixed(2);
                }

                // Update Reason Box
                updateReasonBox();
            }

            window.removeItem = function(targetId, index) {
                itemsTrack[targetId].splice(index, 1);
                updateBreakdownDisplay(targetId);
            };

            function updateReasonBox() {
                let allLabels = [];
                Object.values(itemsTrack).forEach(list => {
                    list.forEach(item => {
                        if (!allLabels.includes(item.label)) {
                            allLabels.push(item.label);
                        }
                    });
                });
                
                if (allLabels.length > 0) {
                    reasonBox.value = "Included: " + allLabels.join(', ');
                } else {
                    reasonBox.value = "";
                }
            }

            function addAdjustment(value, targetId, selectedText, formula) {
                if (value && targetId) {
                    const calculatedValue = evaluateFormula(formula || '0');

                    // Add to track
                    itemsTrack[targetId].push({
                        label: selectedText,
                        amount: calculatedValue
                    });

                    updateBreakdownDisplay(targetId);

                    // Feedback pulse
                    const input = document.getElementById(targetId);
                    input.classList.add('ring-4', 'ring-indigo-300');
                    setTimeout(() => input.classList.remove('ring-4', 'ring-indigo-300'), 800);
                }
            }

            // Handle Selection Change (Direct Addition)
            selectors.forEach(selector => {
                selector.addEventListener('change', function() {
                    const val = this.value;
                    if (val) {
                        const targetId = this.getAttribute('data-target');
                        const selectedOption = this.options[this.selectedIndex];
                        const selectedText = selectedOption.text;
                        const formula = selectedOption.getAttribute('data-formula');
                        
                        addAdjustment(val, targetId, selectedText, formula);
                        
                        // Reset the dropdown so they can pick again
                        this.value = "";
                    }
                });
            });
        });
    </script>
</x-app-layout>
