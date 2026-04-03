<x-app-layout>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-container { padding: 0 !important; max-width: 100% !important; border: 0 !important; box-shadow: none !important; }
            .bg-slate-100 { background-color: white !important; }
        }
        .double-border-t { border-top: 4px double #334155; }
        .double-border-b { border-bottom: 4px double #334155; }
        .grid-cols-13 { grid-template-columns: repeat(13, minmax(0, 1fr)); }
    </style>

    <div class="py-2 bg-slate-100 min-h-screen font-sans text-[10px]">
        <!-- Top Actions (Hidden on Print) -->
        <div class="max-w-[1100px] mx-auto px-2 no-print mb-2 text-gray-900">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <a href="javascript:void(0)" onclick="window.history.back()"
                       class="p-1.5 bg-white border border-slate-200 rounded text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <h2 class="text-base font-black text-slate-900 leading-tight">Admin Payslip View</h2>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $payroll->payrollPeriod->name }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    @if(auth()->user()->isAdmin() || auth()->user()->isHr())
                        <a href="{{ route('payroll.computation.edit', $payroll) }}" 
                            class="inline-flex items-center px-4 py-1.5 bg-blue-600 text-white text-[9px] font-black uppercase rounded shadow-sm hover:bg-blue-700 transition tracking-widest">
                            <i class="fas fa-edit mr-1"></i> Adjust Payslip
                        </a>
                    @endif
                    <button onclick="window.print()" class="inline-flex items-center px-4 py-1.5 bg-rose-600 text-white text-[9px] font-black uppercase rounded shadow-sm hover:bg-rose-700 transition tracking-widest">
                        Print
                    </button>
                    <a href="{{ route('payroll.payslip-pdf', $payroll) }}" class="inline-flex items-center px-4 py-1.5 bg-indigo-600 text-white text-[9px] font-black uppercase rounded shadow-sm hover:bg-indigo-700 transition tracking-widest">
                        Download PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Official Payslip Mockup -->
        <div class="max-w-[1100px] mx-auto bg-white border border-slate-800 shadow-lg print-container overflow-hidden text-gray-900">
            <!-- HEADER STRIPE -->
            <div class="bg-[#002B49] text-white px-4 py-2 flex justify-between items-center border-b border-slate-900">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/10 rounded flex items-center justify-center border border-white/20">
                        <svg class="w-5 h-5 text-white/50" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"></path></svg>
                    </div>
                    <h1 class="text-sm font-[1000] uppercase tracking-tight italic">
                        {{ App\Models\CompanySetting::getValue('company_name', 'Mancao Electronic Connect Business Solutions') }}
                    </h1>
                </div>
                <div class="text-right leading-tight">
                    <div class="text-[11px] font-[1000] text-sky-400">
                        {{ $payroll->payrollPeriod->start_date->format('Y-M-d') }} - {{ $payroll->payrollPeriod->end_date->format('Y-M-d') }}
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-white/90">
                        {{ $payroll->user->employee_id }} | {{ $payroll->user->name }}
                    </div>
                </div>
            </div>

            <!-- MAIN GRID -->
            <div class="grid grid-cols-12 auto-rows-min text-[10px] font-medium text-slate-900">
                
                {{-- EARNINGS COLUMN (Left) --}}
                <div class="col-span-12 lg:col-span-5 border-r border-slate-200">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-2 py-1 uppercase font-[1000] tracking-wider text-[#002B49]">EARNINGS</th>
                                <th class="text-left px-2 py-1 uppercase font-[1000] tracking-wider text-[#002B49] border-l border-slate-200">DAY(S)/HR</th>
                                <th class="text-right px-2 py-1 uppercase font-[1000] tracking-wider text-[#002B49] border-l border-slate-200">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="px-2 py-0.5 font-bold">Salary</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-slate-400">daily rate</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black">{{ number_format($payroll->user->daily_rate ?? 0, 2) }}</td>
                            </tr>
                            <tr class="bg-indigo-50/30">
                                <td class="px-2 py-0.5 font-bold">Net Basic</td>
                                <td class="px-2 py-0.5 border-l border-slate-200"></td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black">{{ number_format($payroll->basic_pay, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="px-2 py-0.5 font-bold">Leave Pay</td>
                                <td class="px-2 py-0.5 border-l border-slate-200"></td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black">0.00</td>
                            </tr>
                            <tr class="bg-slate-50/50">
                                <td class="px-2 py-0.5 font-bold uppercase text-[9px]">Basic <span class="lowercase font-normal text-slate-400">(net)</span></td>
                                <td class="px-2 py-0.5 border-l border-slate-200"></td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black">{{ number_format($payroll->basic_pay, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="px-2 py-0.5 font-bold">Taxable Allow.</td>
                                <td class="px-2 py-0.5 border-l border-slate-200"></td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black">{{ number_format($payroll->allowances ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="px-2 py-0.5 font-bold">COLA</td>
                                <td class="px-2 py-0.5 border-l border-slate-200"></td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black opacity-30">0.00</td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- LEAVE USED Section --}}
                    <div class="border-t border-slate-200">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="text-left px-2 py-1 uppercase font-[1000] tracking-wider text-[#002B49]" colspan="2">LEAVE USED</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 italic">
                                <tr>
                                    <td class="px-2 py-0.5 font-bold w-2/3">Vacation Leave</td>
                                    <td class="px-2 py-0.5 text-center font-black">0</td>
                                </tr>
                                <tr>
                                    <td class="px-2 py-0.5 font-bold w-2/3">Sick Leave</td>
                                    <td class="px-2 py-0.5 text-center font-black">0</td>
                                </tr>
                                <tr>
                                    <td class="px-2 py-0.5 font-bold w-2/3 uppercase text-[8px]">Others</td>
                                    <td class="px-2 py-0.5 text-center font-black">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- DEDUCTIONS COLUMN (Middle) --}}
                <div class="col-span-12 lg:col-span-4 border-r border-slate-200">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-2 py-1 uppercase font-[1000] tracking-wider text-[#002B49]">DEDUCTIONS</th>
                                <th class="text-right px-2 py-1 uppercase font-[1000] tracking-wider text-[#002B49] border-l border-slate-200">AMOUNT</th>
                                <th class="text-right px-2 py-1 uppercase font-[1000] tracking-wider text-[#002B49] border-l border-slate-200">YTD</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="px-2 py-0.5 font-bold">SSS Contribution</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black">{{ number_format($payroll->sss_contribution, 2) }}</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right text-slate-400 italic">{{ number_format($ytdSum['total_sss'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="px-2 py-0.5 font-bold">PHILHEALTH</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black">{{ number_format($payroll->philhealth_contribution, 2) }}</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right text-slate-400 italic">{{ number_format($ytdSum['total_philhealth'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="px-2 py-0.5 font-bold">PAGIBIG</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black">{{ number_format($payroll->pagibig_contribution, 2) }}</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right text-slate-400 italic">{{ number_format($ytdSum['total_pagibig'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="px-2 py-0.5 font-bold">WTAX <span class="text-[7px] text-slate-400">(S/ME)</span></td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black">{{ number_format($payroll->withholding_tax, 2) }}</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right text-slate-400 italic">{{ number_format($ytdSum['total_tax'] ?? 0, 2) }}</td>
                            </tr>
                            <tr class="bg-rose-50/30">
                                <td class="px-2 py-0.5 font-bold">ABSENT/LATE</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black text-rose-700">{{ number_format($payroll->absent_deductions + $payroll->late_deductions, 2) }}</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right text-slate-200 italic">0.00</td>
                            </tr>
                            <tr class="bg-rose-50/30">
                                <td class="px-2 py-0.5 font-bold uppercase text-[8px]">Undertime</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black text-rose-700">{{ number_format($payroll->undertime_deductions, 2) }}</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right text-slate-200 italic">0.00</td>
                            </tr>
                            <tr>
                                <td class="px-2 py-0.5 font-bold opacity-40">OVERBREAK</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right font-black opacity-40">0</td>
                                <td class="px-2 py-0.5 border-l border-slate-200 text-right opacity-20">0</td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- LOANS Section --}}
                    <div class="border-t border-slate-200">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="text-left px-2 py-1 uppercase font-[1000] tracking-wider text-[#002B49]">LOAN(S)</th>
                                    <th class="text-center px-1 py-1 uppercase font-[1000] text-[#002B49] border-l border-slate-200 text-[8px]">PMT#</th>
                                    <th class="text-right px-2 py-1 uppercase font-[1000] tracking-wider text-[#002B49] border-l border-slate-200">AMT</th>
                                    <th class="text-right px-2 py-1 uppercase font-[1000] tracking-wider text-[#002B49] border-l border-slate-200">BAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center italic text-slate-200 uppercase py-2 font-black tracking-widest" colspan="4">NONE</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ADDITIONS & SUMMARY (Right) --}}
                <div class="col-span-12 lg:col-span-3 flex flex-col h-full divide-y divide-slate-100">
                    {{-- OTHER ADDITIONS --}}
                    <div class="flex-1">
                        <div class="bg-slate-50 px-2 py-1 border-b border-slate-200 font-[1000] uppercase tracking-wider text-[#002B49]">
                            ADDITIONS
                        </div>
                        <div class="p-2 space-y-1">
                            @if($payroll->bonus > 0 || $payroll->night_diff_pay > 0 || $payroll->rest_day_pay > 0 || $payroll->holiday_pay > 0)
                                @if($payroll->bonus > 0)
                                <div class="flex justify-between items-center text-[9px] border-b border-slate-50">
                                    <span class="font-bold text-slate-500">Bonus</span>
                                    <span class="font-black text-blue-700">{{ number_format($payroll->bonus, 2) }}</span>
                                </div>
                                @endif
                                @if($payroll->night_diff_pay > 0)
                                <div class="flex justify-between items-center text-[9px] border-b border-slate-50">
                                    <span class="font-bold text-slate-500">ND Pay</span>
                                    <span class="font-black text-blue-700">{{ number_format($payroll->night_diff_pay, 2) }}</span>
                                </div>
                                @endif
                                @if($payroll->rest_day_pay > 0)
                                <div class="flex justify-between items-center text-[9px] border-b border-slate-50">
                                    <span class="font-bold text-slate-500">Rest Day</span>
                                    <span class="font-black text-blue-700">{{ number_format($payroll->rest_day_pay, 2) }}</span>
                                </div>
                                @endif
                            @else
                                <div class="text-center text-[9px] text-slate-300 italic py-2">No Other Earnings</div>
                            @endif
                        </div>
                    </div>

                    {{-- TOTAL SUMMARY --}}
                    <div class="bg-slate-50/50 p-2 space-y-1 mt-auto border-t border-slate-200">
                        <div class="flex justify-between items-center border-b border-slate-200 pb-0.5">
                            <span class="font-black uppercase text-slate-400">Gross</span>
                            <span class="font-[1000] text-slate-900">{{ number_format($payroll->gross_pay, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-200 pb-0.5">
                            <span class="font-black uppercase text-slate-400">Total Ded.</span>
                            <span class="font-[1000] text-rose-600">{{ number_format($payroll->total_deductions, 2) }}</span>
                        </div>
                        <div class="flex flex-col items-end pt-1">
                            <span class="font-black text-[8px] uppercase text-[#002B49]/50 leading-none">Net Compensation</span>
                            <span class="text-lg font-[1000] text-[#002B49] leading-tight tabular-nums">₱ {{ number_format($payroll->net_pay, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- OVERTIME BREAKDOWN (Condensed) --}}
                <div class="col-span-12 border-t border-slate-300">
                    <table class="w-full border-collapse">
                        <thead class="bg-slate-50">
                            <tr class="divide-x divide-slate-200 border-b border-slate-200">
                                <th class="text-left px-2 py-1 uppercase font-[1000] text-[#002B49] w-[15%]">OT TYPE</th>
                                <th class="px-2 py-1 uppercase font-[1000] text-[#002B49]">REGULAR</th>
                                <th class="px-2 py-1 uppercase font-[1000] text-[#002B49]">OT PAY</th>
                                <th class="px-2 py-1 uppercase font-[1000] text-[#002B49]">REG w/ ND</th>
                                <th class="px-2 py-1 uppercase font-[1000] text-[#002B49]">OT w/ ND</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-bold text-center">
                            @php $hasOt = ($payroll->overtime_pay > 0 || $payroll->night_diff_pay > 0 || $payroll->holiday_pay > 0 || $payroll->rest_day_pay > 0); @endphp
                            @if($hasOt)
                                @if($payroll->overtime_pay > 0)
                                <tr class="divide-x divide-slate-100">
                                    <td class="text-left px-2 py-0.5 text-slate-600 uppercase text-[9px]">Regular OT</td>
                                    <td>0.00</td><td class="bg-indigo-50/20">{{ number_format($payroll->overtime_pay, 2) }}</td><td>0.00</td><td>0.00</td>
                                </tr>
                                @endif
                                @if($payroll->holiday_pay > 0)
                                <tr class="divide-x divide-slate-100">
                                    <td class="text-left px-2 py-0.5 text-slate-600 uppercase text-[9px]">Holiday Pay</td>
                                    <td>{{ number_format($payroll->holiday_pay, 2) }}</td><td>0.00</td><td>0.00</td><td>0.00</td>
                                </tr>
                                @endif
                            @else
                                <tr><td colspan="5" class="py-1 text-slate-300 italic">No breakdown available</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- FOOTER / SIGNATURE --}}
                <div class="col-span-12 px-4 py-2 bg-slate-50/50 flex justify-between items-end border-t border-slate-200">
                    <div class="flex gap-10">
                        <div>
                            <div class="text-[7px] font-black uppercase text-slate-400 mb-0.5">Taxable Gross</div>
                            <div class="text-xs font-black text-rose-900 leading-none">{{ number_format($payroll->gross_pay - ($payroll->sss_contribution + $payroll->philhealth_contribution + $payroll->pagibig_contribution), 2) }}</div>
                        </div>
                        <div>
                            <div class="text-[7px] font-black uppercase text-slate-400 mb-0.5">YTD Gross</div>
                            <div class="text-xs font-black text-slate-600 leading-none italic">{{ number_format($ytdSum['total_gross'] ?? 0, 2) }}</div>
                        </div>
                    </div>
                    <div class="text-[8px] font-bold text-slate-400 uppercase italic opacity-60 max-w-[50%] text-right">
                        I acknowledge receipt of full payment for services rendered.
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 no-print opacity-30">
            <p class="text-[8px] font-black uppercase tracking-widest leading-none">
                {{ date('Y-M-d H:i:s') }} • MEBS CLOUD INFRASTRUCTURE
            </p>
        </div>
    </div>
</x-app-layout>
