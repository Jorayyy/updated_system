<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Regular Payroll') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('payslip.index') }}" method="GET" id="payrollForm">
                        <div class="flex flex-col md:flex-row items-center gap-4">
                            <label for="payroll_id" class="font-bold text-gray-700">
                                Payroll Period-Payslip
                            </label>
                            
                            <div class="flex-1 w-full relative">
                                <select name="payroll_id" id="payroll_id" 
                                        onchange="document.getElementById('payrollForm').submit()"
                                        class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm pr-10">
                                    <option value="">Select Payroll Period-To View Payslip</option>
                                    @foreach($payrollPeriods as $p)
                                        <option value="{{ $p['id'] }}" {{ request('payroll_id') == $p['id'] ? 'selected' : '' }}>
                                            {{ $p['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($selectedPayroll)
            <div class="mt-6">
                <!-- Action Buttons -->
                <div class="mb-4 flex gap-2 no-print">
                    <button onclick="window.print()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm transition shadow-sm">
                        Print
                    </button>
                    <a href="{{ route('payslip.download', $selectedPayroll) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm transition shadow-sm">
                        Download PDF
                    </a>
                </div>

                <div class="bg-white shadow-lg overflow-hidden border border-gray-300 mx-auto" id="printable-payslip" style="width: 1050px;">
                    <!-- Header -->
                    <div class="bg-[#002B49] text-white p-6 flex items-center justify-between min-h-[120px]">
                        <!-- Left: Logo -->
                        <div class="flex-shrink-0 w-[15%]">
                            @php 
                                $logo = \App\Models\CompanySetting::firstWhere('key', 'app_logo')->value ?? null; 
                            @endphp
                            @if($logo)
                                <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="h-14 w-auto filter brightness-0 invert">
                            @else
                                <div class="h-12 w-12 bg-white/20 rounded-full flex items-center justify-center font-bold text-xl italic border-2 border-white/30 text-white">M</div>
                            @endif
                        </div>

                        <!-- Center: Company Name -->
                        <div class="flex-grow text-center px-4">
                            <h1 class="text-xl font-black tracking-[0.2em] uppercase leading-tight">Mancao Electronic Connect Business Solutions OPC</h1>
                        </div>

                        <!-- Right: Period & Employee Details -->
                        <div class="flex-shrink-0 w-[25%] text-right space-y-1">
                            <p class="text-[10px] font-bold tracking-widest opacity-80 uppercase">{{ $selectedPayroll->payrollPeriod->start_date->format('M d, Y') }} - {{ $selectedPayroll->payrollPeriod->end_date->format('M d, Y') }}</p>
                            <div class="pt-1">
                                <p class="text-[10px] font-medium opacity-70 leading-none">{{ $selectedPayroll->user->employee_id ?? 'N/A' }}</p>
                                <p class="text-sm font-black mt-0.5 tracking-tight uppercase whitespace-nowrap overflow-hidden text-ellipsis">{{ strtoupper($selectedPayroll->user->name) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Tables -->
                    <div class="p-0 border-t border-gray-300">
                        <div class="flex border-b border-gray-300">
                            <!-- Left Column: Earnings -->
                            <div class="w-[32%] border-r border-gray-300 flex flex-col">
                                <table class="w-full text-[10px] leading-normal table-fixed border-collapse">
                                    <thead>
                                        <tr class="bg-gray-100 border-b border-gray-300 font-black">
                                            <th class="p-2 py-3 text-left w-[45%] uppercase tracking-tighter">Earnings</th>
                                            <th class="p-2 py-3 text-center uppercase tracking-tighter">Day(s)/Hours</th>
                                            <th class="p-2 py-3 text-right uppercase tracking-tighter">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 italic">
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase">Salary</td><td class="p-2 text-center text-gray-400 text-[9px] font-medium">daily rate</td><td class="p-2 text-right font-black not-italic">{{ number_format($selectedPayroll->basic_pay, 2) }}</td></tr>
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase">Net Basic</td><td class="p-2 text-center">-</td><td class="p-2 text-right font-black not-italic">{{ number_format($selectedPayroll->basic_pay, 2) }}</td></tr>
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase">Leave</td><td class="p-2 text-center text-gray-800">0</td><td class="p-2 text-right font-black not-italic">0.00</td></tr>
                                        <tr class="bg-gray-50/50"><td class="p-2 font-bold text-gray-700 not-italic uppercase text-[9px]">Basic (less leave)</td><td class="p-2 text-center text-gray-300">---</td><td class="p-2 text-right font-black not-italic">{{ number_format($selectedPayroll->basic_pay, 2) }}</td></tr>
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase">Nontax Allowance</td><td class="p-2 text-center">-</td><td class="p-2 text-right font-black not-italic">{{ number_format($selectedPayroll->nontax_allowance ?? 200, 2) }}</td></tr>
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase">Taxable Allowance</td><td class="p-2 text-center font-black not-italic">0</td><td class="p-2 text-right font-black not-italic">0.00</td></tr>
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase">Cola</td><td class="p-2 text-center">0</td><td class="p-2 text-right font-black not-italic">0.00</td></tr>
                                    </tbody>
                                </table>

                                <div class="mt-auto">
                                    <table class="w-full text-[10px] table-fixed border-t border-gray-300">
                                        <thead class="bg-gray-100 border-b border-gray-300 font-black">
                                            <tr><th class="p-2 py-3 text-left uppercase tracking-tighter">Leave Used</th><th class="p-2 py-3 text-right">#</th></tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 uppercase font-bold text-gray-600">
                                            <tr><td class="p-2 px-3">VL</td><td class="p-2 text-right text-gray-900 px-3">0</td></tr>
                                            <tr><td class="p-2 px-3">SL</td><td class="p-2 text-right text-gray-900 px-3">0</td></tr>
                                            <tr><td class="p-2 px-3 italic text-gray-400">Other Leave</td><td class="p-2 text-right font-black text-gray-900 px-3">0</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Middle Column: Deductions -->
                            <div class="w-[28%] border-r border-gray-300 flex flex-col">
                                <table class="w-full text-[10px] leading-normal table-fixed border-collapse">
                                    <thead>
                                        <tr class="bg-gray-100 border-b border-gray-300 font-black">
                                            <th class="p-2 py-3 text-left w-1/2 uppercase tracking-tighter">Deductions</th>
                                            <th class="p-2 py-3 text-right uppercase tracking-tighter">Amount</th>
                                            <th class="p-2 py-3 text-right uppercase tracking-tighter">YTD</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 italic">
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase">SSS</td><td class="p-2 text-right font-black not-italic">0.00</td><td class="p-2 text-right text-gray-400 font-medium not-italic">0.00</td></tr>
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase">PHILHEALTH</td><td class="p-2 text-right font-black not-italic">0.00</td><td class="p-2 text-right text-gray-400 font-medium not-italic">0.00</td></tr>
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase tracking-tighter">Pagibig</td><td class="p-2 text-right font-black not-italic">0.00</td><td class="p-2 text-right text-gray-400 font-medium not-italic">0.00</td></tr>
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase italic leading-none">WTAX <br><span class="text-[7.5px] font-normal tracking-tight opacity-60">(TAXCODE: S/ME)</span></td><td class="p-2 text-right font-black not-italic">0.00</td><td class="p-2 text-right text-gray-400 font-medium not-italic">0.00</td></tr>
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase">Absent</td><td class="p-2 text-right font-black not-italic">{{ number_format($selectedPayroll->absent_deduction ?? 0, 2) }}</td><td class="bg-gray-50/20"></td></tr>
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase">Late</td><td class="p-2 text-right font-black not-italic">{{ number_format($selectedPayroll->late_deduction ?? 0, 2) }}</td><td class="bg-gray-50/20"></td></tr>
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase">Undertime</td><td class="p-2 text-right font-black not-italic">{{ number_format($selectedPayroll->undertime_deduction ?? 0, 2) }}</td><td class="bg-gray-50/20"></td></tr>
                                        <tr><td class="p-2 font-bold text-gray-700 not-italic uppercase">Overbreak</td><td class="p-2 text-right font-black not-italic">0.00</td><td class="bg-gray-50/20"></td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Right Column: Other Additions -->
                            <div class="flex-1 flex flex-col">
                                <div class="flex-1">
                                    <table class="w-full text-[10px] table-fixed border-collapse">
                                        <thead>
                                            <tr class="bg-gray-100 border-b border-gray-300 font-black">
                                                <th class="p-2 py-3 text-left uppercase tracking-tighter">Other Additions</th>
                                                <th class="p-2 py-3 text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50">
                                            <tr class="bg-gray-50/40"><td colspan="2" class="p-2 font-black text-gray-400 uppercase text-[9px] tracking-widest pl-4">Taxable</td></tr>
                                            <tr><td colspan="2" class="p-4 text-center italic text-gray-300 text-[11px] tracking-widest font-light uppercase">--- none ---</td></tr>
                                            <tr class="bg-gray-50/40"><td colspan="2" class="p-2 font-black text-gray-400 uppercase text-[9px] tracking-widest pl-4">Non-Taxable</td></tr>
                                            <tr>
                                                <td class="p-3 pl-6 font-bold text-gray-700 uppercase">Night Differential</td>
                                                <td class="p-3 text-right font-black text-gray-900 pr-6">{{ number_format($selectedPayroll->night_differential ?? 200, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="bg-[#002B49] p-4 text-white mt-auto">
                                    <div class="flex justify-between items-center text-xs font-black px-4">
                                        <span class="uppercase tracking-[0.2em] italic opacity-80 text-[10px]">Total Additions</span>
                                        <span class="text-xl font-black">₱ {{ number_format($selectedPayroll->nontax_allowance ?? 200, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Sections: Breakdown and Footer -->
                        <div class="flex text-[9px]">
                            <!-- OT Breakdown -->
                            <div class="w-[32%] border-r border-gray-300 flex flex-col min-h-[300px]">
                                <table class="w-full text-[9px] table-fixed border-collapse">
                                    <thead class="bg-gray-100 border-b border-gray-300 font-bold uppercase tracking-tighter text-gray-600">
                                        <tr>
                                            <th class="p-2 py-3 text-left w-[35%]">Overtime Breakdown</th>
                                            <th class="p-1 px-1 text-center font-black">Reg</th>
                                            <th class="p-1 px-1 text-center font-black">OT</th>
                                            <th class="p-1 px-1 text-center font-black">R-ND</th>
                                            <th class="p-1 px-1 text-center font-black">O-ND</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 uppercase font-bold text-gray-500 italic">
                                        @foreach(['Regular', 'Restday', 'Regular Holiday', 'Double Regular Holiday', 'Special Holiday', 'Restday Holiday', 'Special RD', 'Double Reg RD'] as $breakdown)
                                        <tr>
                                            <td class="p-2 px-3 text-[8.5px] not-italic text-gray-600">{{ $breakdown }}</td>
                                            <td class="p-2 text-center text-gray-900 font-black not-italic">0</td>
                                            <td class="p-2 text-center text-gray-900 font-black not-italic">0</td>
                                            <td class="p-2 text-center text-gray-900 font-black not-italic">0</td>
                                            <td class="p-2 text-center text-gray-900 font-black not-italic">0</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex-1 flex flex-col">
                                <div class="flex flex-1">
                                    <!-- Loans -->
                                    <div class="w-[45%] border-r border-gray-300 p-0 flex flex-col border-b border-gray-300">
                                        <table class="w-full table-fixed border-collapse">
                                            <thead class="bg-gray-100 border-b border-gray-300 font-bold uppercase text-[9px] text-gray-600">
                                                <tr>
                                                    <th class="p-2 py-3 text-left pl-4">Loan(s)</th>
                                                    <th class="p-2 py-3 text-right">Amnt</th>
                                                    <th class="p-2 py-3 text-right pr-4">Bal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-300 italic text-center uppercase tracking-widest font-light">
                                                <tr><td colspan="3" class="p-12 text-[10px]">--- none ---</td></tr>
                                            </tbody>
                                        </table>

                                        <div class="p-6 mt-auto border-t border-gray-100 bg-gray-50/30">
                                            <div class="flex justify-between items-center">
                                                <span class="font-black text-gray-400 uppercase text-[8px] tracking-wider">Working Schedule ND</span>
                                                <span class="font-black text-gray-900 text-[10px]">0</span>
                                            </div>
                                            <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
                                                <span class="font-black text-gray-500 uppercase text-[9px] tracking-[0.15em]">Total Overtime</span>
                                                <span class="text-[15px] font-black text-gray-900 italic">₱ 0.00</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Other Deductions & Totals -->
                                    <div class="w-[55%] flex flex-col border-b border-gray-300">
                                        <table class="w-full table-fixed border-collapse">
                                            <thead class="bg-gray-100 border-b border-gray-300 font-bold uppercase text-[9px] text-gray-600">
                                                <tr><th class="p-2 py-3 text-left pl-6 tracking-widest">Other Deduction(s)</th></tr>
                                            </thead>
                                            <tbody class="text-gray-300 italic text-center uppercase tracking-widest font-light">
                                                <tr><td class="p-10 text-[10px]">--- none ---</td></tr>
                                            </tbody>
                                        </table>
                                        
                                        <div class="mt-auto p-6 space-y-3 bg-white">
                                            <div class="flex justify-between font-black text-gray-500 uppercase text-[9px] tracking-widest px-2">
                                                <span>Total Earning(s)</span>
                                                <span class="text-gray-900 border-b border-gray-200">{{ number_format($selectedPayroll->gross_pay, 2) }}</span>
                                            </div>
                                            <div class="flex justify-between font-black text-gray-500 uppercase text-[9px] tracking-widest px-2">
                                                <span>Total Deduction(s)</span>
                                                <span class="text-gray-900 border-b border-gray-200">{{ number_format($selectedPayroll->total_deductions, 2) }}</span>
                                            </div>
                                            <div class="pt-4 px-2">
                                                <div class="flex justify-between items-center font-black text-[#002B49] px-4 py-4 bg-indigo-50/80 rounded-lg ring-1 ring-indigo-100 shadow-sm">
                                                    <span class="text-[10px] tracking-[0.25em] uppercase opacity-70 italic">Net Pay</span>
                                                    <span class="text-2xl font-black">₱ {{ number_format($selectedPayroll->net_pay, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Financial Footer -->
                                <div class="bg-[#002B49] text-white p-4 flex justify-between items-center ring-1 ring-white/10">
                                    <div class="flex gap-10 pl-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-400 uppercase text-[7px] tracking-widest opacity-80">Gross Pay</span>
                                            <span class="font-black text-[14px] leading-tight">{{ number_format($selectedPayroll->gross_pay, 2) }}</span>
                                        </div>
                                        <div class="flex flex-col border-l border-white/20 pl-10">
                                            <span class="font-bold text-gray-400 uppercase text-[7px] tracking-widest opacity-80">Taxable Income</span>
                                            <span class="font-black text-[14px] leading-tight">{{ number_format($selectedPayroll->taxable_income ?? $selectedPayroll->net_pay, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-10 text-right pr-4">
                                        <div class="flex flex-col border-r border-white/20 pr-10">
                                            <span class="font-bold text-gray-400 uppercase text-[7px] tracking-widest opacity-80">YTD Gross</span>
                                            <span class="font-black text-[14px] leading-tight">{{ number_format($selectedPayroll->ytd_gross ?? 22805.50, 2) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-400 uppercase text-[7px] tracking-widest opacity-80">YTD Taxable</span>
                                            <span class="font-black text-[14px] leading-tight">{{ number_format($selectedPayroll->ytd_taxable ?? 14820.15, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Acknowledgment Footer -->
                        <div class="p-8 py-10 text-center bg-white">
                            <p class="text-[9px] font-black text-[#002B49] italic tracking-[0.05em] mb-10 opacity-80">I acknowledge to have received the amount stated here within with no further claim for services rendered.</p>
                            <div class="max-w-[280px] mx-auto border-t-2 border-gray-900 pt-2">
                                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-[#002B49]">{{ strtoupper($selectedPayroll->user->name) }}</p>
                                <p class="text-[7.5px] text-gray-500 uppercase font-bold mt-1 tracking-widest">Employee Signature Over Printed Name</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <style>
        @media print {
            body { background-color: white !important; }
            .no-print { display: none !important; }
            .py-12 { padding-top: 0 !important; padding-bottom: 0 !important; }
            .max-w-7xl { max-width: 100% !important; margin: 0 !important; }
            .shadow-lg { shadow: none !important; border: none !important; }
            .bg-gray-100 { background: white !important; }
            #printable-payslip { margin: 0 !important; border: 1px solid #ccc !important; width: 100% !important; }
        }
    </style>
</x-app-layout>
