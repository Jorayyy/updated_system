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

                            <hr class="my-6">
                            <h3 class="text-lg font-medium text-indigo-900 bg-indigo-50 p-2 rounded">Standard Adjustment Values & Formulas</h3>
                            <p class="text-sm text-gray-500 mb-2">Set default amounts (₱) or formulas for manual payroll entries.</p>
                            
                            <div class="bg-blue-50 p-3 rounded text-xs text-blue-800 mb-4 border border-blue-100">
                                <p class="font-bold mb-1">Available Variables for Formulas:</p>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    <div><code>{basic}</code> - Period Basic Pay</div>
                                    <div><code>{days}</code> - Total Days Worked</div>
                                    <div><code>{daily}</code> - Daily Rate</div>
                                    <div><code>{hourly}</code> - Hourly Rate</div>
                                    <div><code>{late}</code> - Late Minutes</div>
                                    <div><code>{absent}</code> - Absent Days</div>
                                    <div><code>{att_inc}</code> - Attendance Incentive (User Profile)</div>
                                    <div><code>{perf_inc}</code> - Perfect Attendance (User Profile)</div>
                                </div>
                                <p class="mt-2 italic">Example: <code>{basic} * 0.1</code> (10% of basic) or <code>{days} * 50</code></p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($adjustmentTypes as $adj)
                                    <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-sm relative group">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex-1">
                                                <x-input-label for="adj_name_{{ $adj->id }}" :value="__('Label')" />
                                                <input type="text" name="adj_name_{{ $adj->id }}" id="adj_name_{{ $adj->id }}" 
                                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold"
                                                    value="{{ old('adj_name_' . $adj->id, $adj->name) }}">
                                            </div>
                                            @if(!$adj->is_system_default)
                                                <button type="button" onclick="confirmDeleteAdjustment({{ $adj->id }})" 
                                                    class="ml-2 text-rose-400 hover:text-rose-600 transition p-1">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <x-input-label for="adj_formula_{{ $adj->id }}" :value="__('Default Formula / Amount (₱)')" />
                                        <input type="text" name="adj_formula_{{ $adj->id }}" id="adj_formula_{{ $adj->id }}" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono"
                                            value="{{ old('adj_formula_' . $adj->id, $adj->default_formula) }}">
                                        
                                        <div class="mt-2 flex items-center justify-between">
                                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">
                                                Code: {{ $adj->code }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest {{ $adj->type == 'earning' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                {{ $adj->type }} → {{ str_replace('_', ' ', $adj->target_field) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Add New Adjustment Button -->
                            <div class="mt-6 flex justify-center">
                                <button type="button" onclick="openAddAdjustmentModal()" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-xl font-black text-xs text-indigo-600 uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition duration-150">
                                    <i class="fas fa-plus-circle mr-2"></i> Add New Adjustment Category
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-12 bg-slate-50 p-6 rounded-b-lg border-t border-slate-100">
                            <x-primary-button>
                                {{ __('Commit All Settings') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Adjustment Modal -->
    <div id="addAdjustmentModal" class="fixed inset-0 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4 backdrop-blur-sm bg-slate-900/40">
            <!-- Overlay click-to-close handler -->
            <div class="fixed inset-0" onclick="closeAddAdjustmentModal()"></div>
            
            <!-- Modal Content Section -->
            <div class="relative bg-white rounded-[2rem] shadow-2xl max-w-md w-full max-h-[90vh] flex flex-col transform transition-all border border-slate-100 overflow-hidden">
                <!-- Inner Scrollable Content -->
                <div class="p-8 overflow-y-auto">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight leading-none">New Category</h3>
                        <button type="button" onclick="closeAddAdjustmentModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition duration-300">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form action="{{ route('settings.payroll.adjustment-types.store') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="new_adj_name" :value="__('Display Name')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1" />
                                <x-text-input id="new_adj_name" name="name" type="text" class="block w-full border-slate-200 rounded-xl focus:ring-indigo-500" placeholder="e.g. Rice Subsidy" required />
                            </div>
                            
                            <div>
                                <x-input-label for="new_adj_code" :value="__('Unique Code')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1" />
                                <x-text-input id="new_adj_code" name="code" type="text" class="block w-full border-slate-200 rounded-xl focus:ring-indigo-500 font-mono text-sm" placeholder="e.g. ADD_RICE" required />
                                <p class="text-[9px] text-slate-400 mt-2 italic font-bold">Use UPPERCASE with underscores (e.g., BONUS_YEARLY)</p>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <x-input-label for="new_adj_type" :value="__('Type Classification')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1" />
                                    <select id="new_adj_type" name="type" class="block w-full border-slate-200 rounded-xl shadow-sm focus:ring-indigo-500 text-sm font-bold text-slate-600">
                                        <option value="earning">Earning (+)</option>
                                        <option value="deduction">Deduction (-)</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="new_adj_target" :value="__('Payroll Pillar')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1" />
                                    <select id="new_adj_target" name="target_field" class="block w-full border-slate-200 rounded-xl shadow-sm focus:ring-indigo-500 text-sm font-bold text-slate-600">
                                        <option value="bonus">Bonus / Incentives</option>
                                        <option value="allowances">Allowances</option>
                                        <option value="loan_deductions">Loan Repayments</option>
                                        <option value="other_deductions">Miscellaneous Deductions</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <x-input-label for="new_adj_formula" :value="__('Default Computation')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1" />
                                <x-text-input id="new_adj_formula" name="default_formula" type="text" class="block w-full border-slate-200 rounded-xl focus:ring-indigo-500 font-mono text-sm" placeholder="0 or {basic} * 0.05" required />
                                <div class="mt-3 p-3 bg-indigo-50 rounded-lg border border-indigo-100">
                                    <p class="text-[9px] font-bold text-indigo-700 leading-relaxed uppercase tracking-tighter">
                                        <i class="fas fa-info-circle mr-1 text-indigo-400"></i> Supported tags: {basic}, {days}, {daily}, {hourly}, {late}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 flex gap-4 pt-4 border-t border-slate-50">
                            <button type="button" onclick="closeAddAdjustmentModal()" class="flex-1 px-6 py-4 bg-slate-100 text-slate-500 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-200 transition duration-300">
                                Cancel
                            </button>
                            <button type="submit" class="flex-1 px-6 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-indigo-700 transition duration-300 shadow-xl shadow-indigo-200 flex items-center justify-center gap-2">
                                <i class="fas fa-save shadow-sm"></i> Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    <form id="deleteAdjustmentForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function openAddAdjustmentModal() {
            document.getElementById('addAdjustmentModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAddAdjustmentModal() {
            document.getElementById('addAdjustmentModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function confirmDeleteAdjustment(id) {
            if (confirm('Are you sure you want to delete this adjustment type? This will remove it from the available presets in the payroll editor.')) {
                const form = document.getElementById('deleteAdjustmentForm');
                form.action = `/settings/payroll/adjustment-types/${id}`;
                form.submit();
            }
        }
    </script>
</x-app-layout>
