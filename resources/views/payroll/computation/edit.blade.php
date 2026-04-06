<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <nav class="flex text-gray-500 text-xs mb-1 uppercase tracking-widest font-black" aria-label="Breadcrumb">
                    <ol class="list-none p-0 inline-flex">
                        <li class="flex items-center">
                            <a href="{{ route('payroll.index') }}" class="hover:text-indigo-600 transition-colors">Payroll</a>
                            <svg class="fill-current w-3 h-3 mx-2" viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                        </li>
                        <li class="flex items-center">
                             <a href="{{ route('payroll.show-period', $payroll->payroll_period_id) }}" class="hover:text-indigo-600 transition-colors">Computation</a>
                             <svg class="fill-current w-3 h-3 mx-2" viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                        </li>
                        <li class="text-indigo-600">Adjustment</li>
                    </ol>
                </nav>
                <h2 class="font-black text-2xl text-gray-900 leading-tight">
                    {{ $payroll->user->name }} <span class="text-gray-400 font-light ml-2">#{{ $payroll->user->employee_id ?? 'EMP-'.$payroll->user->id }}</span>
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right mr-4 hidden md:block">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Period Coverage</p>
                    <p class="text-xs font-bold text-gray-700">{{ $payroll->payrollPeriod->formatted_range }}</p>
                </div>
                <div class="h-10 w-[1px] bg-gray-200 mr-2 hidden md:block"></div>
                <a href="javascript:history.back()" class="bg-white border-2 border-gray-200 px-4 py-2 rounded-xl text-xs font-black uppercase text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all">
                    Cancel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('payroll.computation.update', $payroll) }}" method="POST" id="payrollForm">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
                    {{-- Left Sidebar: DTR Context & Profile --}}
                    <div class="space-y-6">
                        {{-- 1. DTR Basis Card --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                                <h3 class="text-[11px] font-black text-gray-900 uppercase tracking-[0.2em] flex items-center gap-2">
                                    <i class="fas fa-clock text-indigo-500"></i> DTR Basis
                                </h3>
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-[9px] font-black uppercase">Approved DTR</span>
                            </div>
                            <div class="p-6 grid grid-cols-2 gap-4">
                                <div class="bg-indigo-50/30 p-3 rounded-xl border border-indigo-100/50">
                                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1 text-center">Work Days</p>
                                    <p class="text-xl font-black text-indigo-700 text-center">{{ $payroll->total_work_days ?? 0 }}d</p>
                                </div>
                                <div class="bg-blue-50/30 p-3 rounded-xl border border-blue-100/50 font-center">
                                    <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-1 text-center">OT Hours</p>
                                    <p class="text-xl font-black text-blue-700 text-center">{{ number_format(($payroll->total_overtime_minutes ?? 0)/60, 1) }}h</p>
                                </div>
                                <div class="bg-red-50/30 p-3 rounded-xl border border-red-100/50">
                                    <p class="text-[9px] font-black text-red-400 uppercase tracking-widest mb-1 text-center">Late/Undert.</p>
                                    <p class="text-xl font-black text-red-700 text-center">{{ ($payroll->total_late_minutes + $payroll->total_undertime_minutes) }}m</p>
                                </div>
                                <div class="bg-red-900/10 p-3 rounded-xl border border-red-900/10">
                                    <p class="text-[9px] font-black text-red-950 uppercase tracking-widest mb-1 text-center">Absents</p>
                                    <p class="text-xl font-black text-red-900 text-center">{{ $payroll->total_absent_days ?? 0 }}d</p>
                                </div>
                            </div>
                            <div class="px-6 pb-6 bg-white">
                                <p class="text-[10px] leading-relaxed text-gray-400 italic text-center">The values above are read-only and were synchronized from the employee's approved Daily Time Records.</p>
                            </div>
                        </div>

                        {{-- 2. Rates Card --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="text-[11px] font-black text-gray-900 uppercase tracking-[0.2em] flex items-center gap-2">
                                    <i class="fas fa-money-bill-wave text-green-500"></i> Pay Rates
                                </h3>
                            </div>
                            <div class="p-6 space-y-5">
                                <div>
                                    <label for="daily_rate" class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Daily Rate (Primary Factor)</label>
                                    <div class="relative group">
                                        <span class="absolute left-4 inset-y-0 flex items-center text-gray-400 font-black z-20 transition-colors group-focus-within:text-indigo-600">₱</span>
                                        <input type="number" step="0.01" name="daily_rate" id="daily_rate" value="{{ old('daily_rate', $payroll->user->daily_rate) }}" 
                                            class="block w-full pl-10 border-2 border-gray-100 rounded-xl bg-gray-50 font-black text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 shadow-sm transition-all text-lg">
                                    </div>
                                    <p class="mt-2 text-[9px] font-black text-indigo-600 uppercase flex items-center gap-1">
                                        <i class="fas fa-info-circle"></i> Edits here update Monthly/Hourly automatically
                                    </p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                     <div>
                                        <label class="block text-[9px] font-black text-gray-400 uppercase mb-1 tracking-widest">Monthly Basic</label>
                                        <div class="relative">
                                            <span class="absolute left-3 inset-y-0 flex items-center text-gray-300 font-bold text-xs">₱</span>
                                            <input type="number" id="monthly_salary" value="{{ ($payroll->user->daily_rate ?? 0) * 26 }}" readonly
                                                class="block w-full pl-7 bg-gray-100 border-none rounded-lg text-xs font-bold text-gray-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-400 uppercase mb-1 tracking-widest">Hourly Rate</label>
                                        <div class="relative">
                                            <span class="absolute left-3 inset-y-0 flex items-center text-gray-300 font-bold text-xs">₱</span>
                                            <input type="number" id="hourly_rate" value="{{ ($payroll->user->daily_rate ?? 0) / 8 }}" readonly
                                                class="block w-full pl-7 bg-gray-100 border-none rounded-lg text-xs font-bold text-gray-500">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Incentive Profile Card (Live Context) --}}
                         <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest flex items-center gap-2 text-slate-600">
                                    <i class="fas fa-database text-indigo-500"></i> Shared Context
                                </h3>
                            </div>
                            <div class="p-6 space-y-5">
                                <p class="text-[11px] text-slate-500 font-medium leading-relaxed italic">These values are used as variables in the calculation presets below. Update them here for immediate effect.</p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5 tracking-wider">Attendance Inc. (Daily)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 inset-y-0 flex items-center text-slate-400 font-bold text-sm">₱</span>
                                            <input type="number" step="0.01" name="attendance_incentive" id="attendance_incentive" value="{{ old('attendance_incentive', $payroll->user->attendance_incentive) }}"
                                                class="block w-full pl-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5 tracking-wider">Perfect Attend. (Flat)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 inset-y-0 flex items-center text-slate-400 font-bold text-sm">₱</span>
                                            <input type="number" step="0.01" name="perfect_attendance_bonus" id="perfect_attendance_bonus" value="{{ old('perfect_attendance_bonus', $payroll->user->perfect_attendance_bonus) }}"
                                                class="block w-full pl-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5 tracking-wider">Site Incentive (Daily)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 inset-y-0 flex items-center text-slate-400 font-bold text-sm">₱</span>
                                            <input type="number" step="0.01" name="site_incentive" id="site_incentive" value="{{ old('site_incentive', $payroll->user->site_incentive) }}"
                                                class="block w-full pl-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none">
                                        </div>
                                    </div>
                                    <div class="pt-2">
                                        <div class="grid grid-cols-2 gap-3 uppercase tracking-tighter">
                                             <div class="bg-slate-50 border border-slate-100 p-3 rounded-xl text-center">
                                                <p class="text-[9px] font-bold text-slate-400 mb-1">COLA</p>
                                                <div class="flex items-center justify-center gap-1">
                                                    <span class="text-slate-400 font-bold text-xs">₱</span>
                                                    <input type="number" step="0.01" name="cola" id="cola" value="{{ $payroll->user->cola }}" class="bg-transparent border-none p-0 text-center w-full text-xs font-bold text-slate-700 focus:ring-0">
                                                </div>
                                             </div>
                                             <div class="bg-slate-50 border border-slate-100 p-3 rounded-xl text-center">
                                                <p class="text-[9px] font-bold text-slate-400 mb-1">Meal</p>
                                                <div class="flex items-center justify-center gap-1">
                                                    <span class="text-slate-400 font-bold text-xs">₱</span>
                                                    <input type="number" step="0.01" name="meal_allowance" id="meal_allowance" value="{{ $payroll->user->meal_allowance }}" class="bg-transparent border-none p-0 text-center w-full text-xs font-bold text-slate-700 focus:ring-0">
                                                </div>
                                             </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Main Calculation Area --}}
                    <div class="xl:col-span-3 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                             {{-- Earnings Container --}}
                             <div class="space-y-6">
                                <div class="flex items-center gap-3 mb-2">
                                     <div class="bg-green-600 p-2 rounded-xl text-white shadow-lg shadow-green-100">
                                         <i class="fas fa-plus"></i>
                                     </div>
                                     <h2 class="text-xl font-black text-gray-900 uppercase">Gross Earnings</h2>
                                </div>

                                {{-- Auto Earnings --}}
                                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm transition-all hover:shadow-md">
                                    <div class="mb-6 flex items-center justify-between">
                                        <h4 class="text-[11px] font-black text-green-700 uppercase tracking-widest flex items-center gap-2">
                                            <span class="w-1 h-3 bg-green-500 rounded"></span> Auto-Calculated (DTR)
                                        </h4>
                                    </div>
                                    <div class="space-y-6">
                                        <div class="relative group">
                                            <label for="basic_pay" class="text-[9px] font-black text-gray-400 uppercase tracking-[0.15em] mb-2 block">Monthly Basic / Fixed Account Rate</label>
                                            <div class="relative">
                                                <span class="absolute left-5 inset-y-0 flex items-center text-gray-300 font-black text-xl group-focus-within:text-green-600 transition-colors">₱</span>
                                                <input type="number" step="0.01" name="basic_pay" id="basic_pay" value="{{ old('basic_pay', $payroll->basic_pay) }}" 
                                                    class="block w-full pl-12 border-2 border-gray-50 bg-gray-50/50 rounded-2xl h-16 font-black text-2xl text-gray-900 focus:bg-white focus:border-green-500 focus:ring-[10px] focus:ring-green-50 transition-all outline-none">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Overtime Pay</label>
                                                <input type="number" step="0.01" name="overtime_pay" id="overtime_pay" value="{{ $payroll->overtime_pay }}" class="w-full border-gray-200 rounded-xl bg-gray-50/50 font-black text-gray-800 focus:border-green-500">
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Holiday Pay</label>
                                                <input type="number" step="0.01" name="holiday_pay" id="holiday_pay" value="{{ $payroll->holiday_pay }}" class="w-full border-gray-200 rounded-xl bg-gray-50/50 font-black text-gray-800 focus:border-green-500">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Manual Additions --}}
                                <div class="bg-white rounded-3xl p-8 border border-green-200 shadow-lg shadow-green-50/30 ring-4 ring-green-50/20">
                                    <div class="mb-6 flex items-center justify-between">
                                        <h4 class="text-[11px] font-black text-green-700 uppercase tracking-widest flex items-center gap-2">
                                            <span class="w-1 h-3 bg-green-500 rounded"></span> Manual Adjustments (Presets)
                                        </h4>
                                    </div>
                                    <div class="space-y-6">
                                         <div>
                                            <p class="text-[9px] font-black text-gray-400 uppercase mb-3 tracking-widest text-center">Select Preset to Auto-Inject</p>
                                            <div class="grid grid-cols-2 gap-2 mb-4">
                                                @foreach($adjustmentTypes->where('target_field', 'bonus') as $adj)
                                                    <button type="button" 
                                                        class="preset-btn px-4 py-3 bg-green-50/50 border border-green-100 rounded-xl text-[10px] font-black text-green-700 uppercase tracking-tight hover:bg-green-600 hover:text-white hover:shadow-lg transition-all"
                                                        data-code="{{ $adj->code }}" data-formula="{{ $adj->default_formula }}" data-name="{{ $adj->name }}" data-target="bonus">
                                                        {{ $adj->name }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="relative group">
                                            <label for="bonus" class="text-[9px] font-black text-green-700 uppercase tracking-[0.15em] mb-2 block">Total Bonus / Incentives</label>
                                            <div class="relative">
                                                <span class="absolute left-5 inset-y-0 flex items-center text-green-300 font-black text-xl">₱</span>
                                                <input type="number" step="0.01" name="bonus" id="bonus" value="{{ old('bonus', $payroll->bonus) }}" 
                                                    class="block w-full pl-12 border-2 border-green-100 bg-white rounded-2xl h-16 font-black text-2xl text-green-900 focus:border-green-500 focus:ring-4 focus:ring-green-50 transition-all outline-none">
                                            </div>
                                            <div id="breakdown-bonus" class="mt-4 flex flex-wrap gap-2"></div>
                                        </div>

                                        <div class="relative group">
                                            <label for="allowances" class="text-[9px] font-black text-green-700 uppercase tracking-[0.15em] mb-2 block">Allowances</label>
                                            <div class="relative">
                                                <span class="absolute left-5 inset-y-0 flex items-center text-green-300 font-black text-xl">₱</span>
                                                <input type="number" step="0.01" name="allowances" id="allowances" value="{{ old('allowances', $payroll->allowances) }}" 
                                                    class="block w-full pl-12 border-2 border-green-50 bg-gray-50/50 rounded-2xl h-12 font-black text-xl text-gray-900 focus:bg-white focus:border-green-500 transition-all outline-none">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             </div>

                             {{-- Deductions Container --}}
                             <div class="space-y-6">
                                <div class="flex items-center gap-3 mb-2">
                                     <div class="bg-red-600 p-2 rounded-xl text-white shadow-lg shadow-red-100">
                                         <i class="fas fa-minus"></i>
                                     </div>
                                     <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Deductions</h2>
                                </div>

                                {{-- Statutory Fixed --}}
                                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm opacity-80 hover:opacity-100 transition-all">
                                    <div class="mb-6">
                                        <h4 class="text-[11px] font-black text-red-700 uppercase tracking-widest flex items-center gap-2">
                                            <span class="w-1 h-3 bg-red-400 rounded"></span> Statutory (Govt)
                                        </h4>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100">
                                            <label class="block text-[8px] font-black text-gray-400 uppercase mb-1">SSS Contribution</label>
                                            <input type="number" step="0.01" name="sss_contribution" id="sss_contribution" value="{{ $payroll->sss_contribution }}" class="w-full bg-transparent border-none p-0 font-black text-gray-900 focus:ring-0">
                                        </div>
                                        <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100">
                                            <label class="block text-[8px] font-black text-gray-400 uppercase mb-1">PhilHealth</label>
                                            <input type="number" step="0.01" name="philhealth_contribution" id="philhealth_contribution" value="{{ $payroll->philhealth_contribution }}" class="w-full bg-transparent border-none p-0 font-black text-gray-900 focus:ring-0">
                                        </div>
                                        <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100">
                                            <label class="block text-[8px] font-black text-gray-400 uppercase mb-1">Pag-IBIG</label>
                                            <input type="number" step="0.01" name="pagibig_contribution" id="pagibig_contribution" value="{{ $payroll->pagibig_contribution }}" class="w-full bg-transparent border-none p-0 font-black text-gray-900 focus:ring-0">
                                        </div>
                                        <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100">
                                            <label class="block text-[8px] font-black text-gray-400 uppercase mb-1">Withholding Tax</label>
                                            <input type="number" step="0.01" name="withholding_tax" id="withholding_tax" value="{{ $payroll->withholding_tax }}" class="w-full bg-transparent border-none p-0 font-black text-gray-900 focus:ring-0">
                                        </div>
                                    </div>
                                </div>

                                {{-- Manual Loans/Deductions --}}
                                <div class="bg-white rounded-3xl p-8 border border-red-200 shadow-lg shadow-red-50/30 ring-4 ring-red-50/20">
                                    <div class="mb-6 flex items-center justify-between">
                                        <h4 class="text-[11px] font-black text-red-700 uppercase tracking-widest flex items-center gap-2">
                                            <span class="w-1 h-3 bg-red-500 rounded"></span> Loans & Penalties
                                        </h4>
                                    </div>
                                    <div class="space-y-6">
                                         <div>
                                            <div class="grid grid-cols-2 gap-2 mb-4">
                                                @foreach($adjustmentTypes->where('target_field', 'loan_deductions') as $adj)
                                                    <button type="button" 
                                                        class="preset-btn px-4 py-3 bg-red-50/50 border border-red-100 rounded-xl text-[10px] font-black text-red-700 uppercase tracking-tight hover:bg-red-600 hover:text-white hover:shadow-lg transition-all"
                                                        data-code="{{ $adj->code }}" data-formula="{{ $adj->default_formula }}" data-name="{{ $adj->name }}" data-target="loan_deductions">
                                                        {{ $adj->name }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="relative group">
                                            <label for="loan_deductions" class="text-[9px] font-black text-red-700 uppercase tracking-[0.15em] mb-2 block">Total Loan Deductions</label>
                                            <div class="relative">
                                                <span class="absolute left-5 inset-y-0 flex items-center text-red-300 font-black text-xl">₱</span>
                                                <input type="number" step="0.01" name="loan_deductions" id="loan_deductions" value="{{ old('loan_deductions', $payroll->loan_deductions) }}" 
                                                    class="block w-full pl-12 border-2 border-red-100 bg-white rounded-2xl h-16 font-black text-2xl text-red-900 focus:border-red-500 focus:ring-4 focus:ring-red-50 transition-all outline-none">
                                            </div>
                                            <div id="breakdown-loan_deductions" class="mt-4 flex flex-wrap gap-2"></div>
                                        </div>

                                        <div class="relative group">
                                            <label for="other_deductions" class="text-[9px] font-black text-red-700 uppercase tracking-[0.15em] mb-2 block">Other Custom Deductions</label>
                                            <div class="relative">
                                                <span class="absolute left-5 inset-y-0 flex items-center text-red-300 font-black text-xl">₱</span>
                                                <input type="number" step="0.01" name="other_deductions" id="other_deductions" value="{{ old('other_deductions', $payroll->other_deductions) }}" 
                                                    class="block w-full pl-12 border-2 border-red-50 bg-gray-50/50 rounded-2xl h-12 font-black text-xl text-gray-900 focus:bg-white focus:border-red-500 transition-all outline-none">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             </div>
                        </div>

                        {{-- Footer Summary & Reason --}}
                        <div class="bg-gray-900 rounded-[3rem] p-10 mt-10 shadow-2xl relative overflow-hidden">
                            {{-- Decorative abstract background --}}
                            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                            <div class="absolute bottom-0 left-0 w-64 h-64 bg-green-500/10 rounded-full -ml-32 -mb-32 blur-3xl"></div>
                            
                            <div class="relative z-10">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                                    <div>
                                         <label for="adjustment_reason" class="block font-black text-[10px] text-indigo-400 uppercase mb-4 tracking-[0.3em]">Audit Trail / Change Log</label>
                                         <textarea id="adjustment_reason" name="adjustment_reason" rows="4" 
                                            class="block w-full bg-white/5 border-white/10 rounded-3xl text-white font-medium text-sm focus:bg-white/10 focus:ring-0 focus:border-indigo-500 transition-all placeholder-gray-600"
                                            required placeholder="Briefly explain the reason for this manual adjustment (e.g., 'Retroactive incentive for March', 'Uniform replacement deduction')...">{{ old('adjustment_reason', $payroll->adjustment_reason) }}</textarea>
                                         <p class="mt-3 text-[10px] text-gray-500 font-bold uppercase tracking-widest flex items-center gap-2">
                                             <i class="fas fa-user-shield text-indigo-500"></i> Authenticated Admin: {{ auth()->user()->name }}
                                         </p>
                                    </div>

                                    <div class="text-right">
                                         <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.5em] mb-2">Final Payable Amount</p>
                                         <div class="flex items-center justify-end gap-3 mb-6">
                                             <span class="text-2xl font-light text-gray-600">₱</span>
                                             <span id="display-net-pay" class="text-7xl font-black text-white tracking-tighter tabular-nums tracking-tighter tabular-nums drop-shadow-md">0.00</span>
                                         </div>
                                         <div class="flex justify-end gap-4">
                                              <button type="submit" class="group relative px-12 py-5 bg-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] text-black shadow-xl hover:bg-indigo-600 hover:text-white active:scale-95 transition-all overflow-hidden">
                                                  <span class="relative z-10 transition-colors duration-300">Finalize Adjustments</span>
                                                  <div class="absolute inset-0 w-0 bg-indigo-700 transition-all group-hover:w-full duration-500"></div>
                                              </button>
                                         </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Core DOM Elements
            const form = document.getElementById('payrollForm');
            const reasonBox = document.getElementById('adjustment_reason');
            const netDisplay = document.getElementById('display-net-pay');
            
            // Formula Engine Context
            const adjustmentSettings = @json($adjustmentTypes);
            const initialContext = @json($formulaContext);
            let context = { ...initialContext };

            // Field Tracking State
            const track = {
                bonus: [],
                loan_deductions: []
            };

            // 1. Initialize Tracked Fields with loaded values
            ['bonus', 'loan_deductions'].forEach(fieldId => {
                const el = document.getElementById(fieldId);
                const val = parseFloat(el.value) || 0;
                if (val > 0) {
                    track[fieldId].push({ label: 'Previous Balance', amount: val });
                    updateTrackUI(fieldId);
                }
            });

            // 2. Real-time context synchronization
            const contextSyncMap = {
                'daily_rate': 'daily',
                'perfect_attendance_bonus': 'perf_inc',
                'attendance_incentive': 'att_inc',
                'site_incentive': 'site_inc',
                'basic_pay': 'basic',
                'cola': 'cola'
            };

            Object.keys(contextSyncMap).forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', function() {
                        const val = parseFloat(this.value) || 0;
                        const ctxKey = contextSyncMap[id];
                        context[ctxKey] = val;
                        
                        // Side effect: Rate Sync
                        if (id === 'daily_rate') {
                            document.getElementById('monthly_salary').value = (val * 26).toFixed(2);
                            document.getElementById('hourly_rate').value = (val / 8).toFixed(2);
                            context.hourly = val / 8;
                        }
                        
                        recalculateNet();
                    });
                }
            });

            // 3. Formula Evaluation Engine
            function runFormula(formula) {
                if (!formula || formula === "0" || formula === "") return 0;
                try {
                    let expr = formula.toString();
                    // Replace {tags} with context values safely
                    Object.keys(context).forEach(key => {
                        const val = context[key] !== null ? context[key] : 0;
                        expr = expr.split('{' + key + '}').join(val);
                    });
                    // Final cleanup and safety check
                    expr = expr.replace(/{[a-zA-Z0-9_]+}/g, '0').replace(/\s+/g, '');
                    return Function('"use strict"; return (' + expr + ')')();
                } catch (e) {
                    console.error('Formula Error:', formula, e);
                    return 0;
                }
            }

            // 4. Update UI for badges/breakdown
            function updateTrackUI(targetId) {
                const container = document.getElementById('breakdown-' + targetId);
                if (!container) return;
                
                container.innerHTML = '';
                let total = 0;

                track[targetId].forEach((item, index) => {
                    total += item.amount;
                    const badge = document.createElement('div');
                    badge.className = `flex items-center gap-2 px-3 py-1.5 rounded-full border text-[10px] font-black uppercase transition-all shadow-sm ${targetId === 'bonus' ? 'bg-green-100 text-green-700 border-green-200' : 'bg-red-100 text-red-700 border-red-200 hover:bg-red-600 hover:text-white'}`;
                    badge.innerHTML = `
                        <span>${item.label}: ₱${item.amount.toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                        <button type="button" class="ml-1 opacity-40 hover:opacity-100 transition-opacity" data-target="${targetId}" data-index="${index}">
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    `;
                    
                    // Attach removal logic
                    badge.querySelector('button').addEventListener('click', function() {
                        track[targetId].splice(index, 1);
                        updateTrackUI(targetId);
                    });
                    
                    container.appendChild(badge);
                });

                // Set actual input value
                document.getElementById(targetId).value = total.toFixed(2);
                recalculateNet();
                updateAuditLog();
            }

            // 5. Global Recalculation (Net Pay)
            function recalculateNet() {
                const fields = [
                   'basic_pay', 'overtime_pay', 'holiday_pay', 'bonus', 'allowances', // Earnings
                   'sss_contribution', 'philhealth_contribution', 'pagibig_contribution', 'withholding_tax',
                   'loan_deductions', 'other_deductions' // Deductions
                ];

                let earnings = 0;
                let deductions = 0;

                fields.forEach(id => {
                    const val = parseFloat(document.getElementById(id).value) || 0;
                    if (['loan_deductions', 'other_deductions', 'sss_contribution', 'philhealth_contribution', 'pagibig_contribution', 'withholding_tax'].includes(id)) {
                        deductions += val;
                    } else {
                        earnings += val;
                    }
                });

                const net = earnings - deductions;
                netDisplay.innerText = net.toLocaleString(undefined, { minimumFractionDigits: 2 });
                
                // Color transition if negative
                if (net < 0) {
                    netDisplay.classList.add('text-red-500');
                    netDisplay.classList.remove('text-white');
                } else {
                    netDisplay.classList.remove('text-red-500');
                    netDisplay.classList.add('text-white');
                }
            }

            // 6. Audit Log Generation
            function updateAuditLog() {
                let items = [];
                Object.values(track).forEach(list => list.forEach(i => {
                    if (i.label !== 'Previous Balance') items.push(i.label);
                }));
                
                if (items.length > 0) {
                    const current = reasonBox.value.split('-- Added:')[0].trim();
                     reasonBox.value = (current ? current + "\n\n" : "") + "-- Added: " + items.join(', ');
                }
            }

            // 7. Preset Button Interactions
            document.querySelectorAll('.preset-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const amt = runFormula(this.dataset.formula);
                    const target = this.dataset.target;
                    const name = this.dataset.name;
                    
                    track[target].push({ label: name, amount: amt });
                    updateTrackUI(target);
                    
                    // Add success pulse
                    btn.classList.add('bg-white', 'scale-105');
                    setTimeout(() => btn.classList.remove('bg-white', 'scale-105'), 200);
                });
            });

            // Initial manual calc sync
            ['basic_pay', 'overtime_pay', 'holiday_pay', 'allowances', 'other_deductions', 'sss_contribution', 'philhealth_contribution', 'pagibig_contribution', 'withholding_tax'].forEach(id => {
                 document.getElementById(id).addEventListener('input', recalculateNet);
            });

            recalculateNet(); // Run once on load
        });
    </script>
</x-app-layout>
