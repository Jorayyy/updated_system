<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('payslip.index') }}" 
                   class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="text-xl font-black text-slate-900 leading-tight tracking-tight">
                        {{ __('Statement of Account') }}
                    </h2>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">Reference #PAY-ORD-{{ str_pad($payroll->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('payslip.download', $payroll) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg shadow-md shadow-indigo-100 hover:bg-indigo-700 transition transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Statement
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Summary Header --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                <div class="bg-slate-900 px-8 py-10 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"></path></svg>
                    </div>
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-end md:items-center gap-6">
                        <div>
                            <span class="px-3 py-1 bg-white/10 rounded-full text-[10px] font-black uppercase tracking-widest text-indigo-300 border border-white/10 mb-4 inline-block">Period Overview</span>
                            <p class="text-3xl font-black tracking-tight">
                                {{ $payroll->payrollPeriod->start_date->format('F d') }} — {{ $payroll->payrollPeriod->end_date->format('d, Y') }}
                            </p>
                            <p class="text-slate-400 font-medium mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Release Date: {{ $payroll->payrollPeriod->pay_date ? $payroll->payrollPeriod->pay_date->format('F d, Y') : 'N/A' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Net Compensation</p>
                            <p class="text-5xl font-black text-indigo-400 leading-none">₱{{ number_format($payroll->net_pay, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Main Breakdown --}}
                <div class="lg:col-span-2 space-y-8">
                    {{-- Performance Metrics --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Attendance & Performance</h3>
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Days Present</p>
                                    <p class="text-2xl font-black text-slate-900">{{ $payroll->total_work_days ?? 0 }}</p>
                                </div>
                                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Hours</p>
                                    <p class="text-2xl font-black text-slate-900">{{ number_format(($payroll->total_work_minutes ?? 0) / 60, 1) }}</p>
                                </div>
                                <div class="bg-indigo-50/50 rounded-2xl p-4 border border-indigo-100/50">
                                    <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">OT Earned</p>
                                    <p class="text-2xl font-black text-indigo-600">{{ number_format(($payroll->total_overtime_minutes ?? 0) / 60, 1) }}h</p>
                                </div>
                                <div class="bg-rose-50/50 rounded-2xl p-4 border border-rose-100/50">
                                    <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mb-1">Mins Late</p>
                                    <p class="text-2xl font-black text-rose-600">{{ $payroll->total_late_minutes ?? 0 }}m</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Earnings Detail --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-emerald-50/30 flex items-center justify-between">
                            <h3 class="text-sm font-black text-emerald-800 uppercase tracking-widest">Income Breakdown</h3>
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center group">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700">Base Compensation</span>
                                        <span class="text-[10px] font-medium text-slate-400 tracking-wide">Standard hourly rate x actual hours</span>
                                    </div>
                                    <span class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">₱{{ number_format($payroll->basic_pay ?? 0, 2) }}</span>
                                </div>
                                @if(($payroll->overtime_pay ?? 0) > 0)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Overtime Pay</span>
                                        <span class="font-medium text-blue-600">₱{{ number_format($payroll->overtime_pay, 2) }}</span>
                                    </div>
                                @endif
                                @if(($payroll->holiday_pay ?? 0) > 0)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Holiday Pay</span>
                                        <span class="font-medium text-purple-600">₱{{ number_format($payroll->holiday_pay, 2) }}</span>
                                    </div>
                                @endif
                                @if(($payroll->night_diff_pay ?? 0) > 0)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Night Differential</span>
                                        <span class="font-medium">₱{{ number_format($payroll->night_diff_pay, 2) }}</span>
                                    </div>
                                @endif
                                @if(($payroll->rest_day_pay ?? 0) > 0)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Rest Day Pay</span>
                                        <span class="font-medium">₱{{ number_format($payroll->rest_day_pay, 2) }}</span>
                                    </div>
                                @endif
                                @if(($payroll->allowances ?? 0) > 0)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Allowances</span>
                                        <span class="font-medium">₱{{ number_format($payroll->allowances, 2) }}</span>
                                    </div>
                                @endif
                                @if(($payroll->bonus ?? 0) > 0)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Bonus</span>
                                        <span class="font-medium text-green-600">₱{{ number_format($payroll->bonus, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between items-center py-3 bg-green-50 rounded-lg px-3 mt-2">
                                    <span class="font-semibold text-gray-900">Gross Pay</span>
                                    <span class="font-bold text-lg text-green-600">₱{{ number_format($payroll->gross_pay ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Deductions --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 border-b border-gray-200 bg-red-50">
                            <h3 class="text-lg font-semibold text-red-700">Deductions</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                {{-- Government Contributions --}}
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Government Contributions</p>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-gray-600">SSS</span>
                                    <span class="font-medium text-red-600">₱{{ number_format($payroll->sss_contribution ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-gray-600">PhilHealth</span>
                                    <span class="font-medium text-red-600">₱{{ number_format($payroll->philhealth_contribution ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Pag-IBIG</span>
                                    <span class="font-medium text-red-600">₱{{ number_format($payroll->pagibig_contribution ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Withholding Tax</span>
                                    <span class="font-medium text-red-600">₱{{ number_format($payroll->withholding_tax ?? 0, 2) }}</span>
                                </div>

                                @if(($payroll->late_deduction ?? 0) > 0 || ($payroll->undertime_deduction ?? 0) > 0 || ($payroll->absent_deduction ?? 0) > 0)
                                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mt-4">Attendance Deductions</p>
                                    @if(($payroll->late_deduction ?? 0) > 0)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-gray-600">Late Deduction</span>
                                            <span class="font-medium text-red-600">₱{{ number_format($payroll->late_deduction, 2) }}</span>
                                        </div>
                                    @endif
                                    @if(($payroll->undertime_deduction ?? 0) > 0)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-gray-600">Undertime Deduction</span>
                                            <span class="font-medium text-red-600">₱{{ number_format($payroll->undertime_deduction, 2) }}</span>
                                        </div>
                                    @endif
                                    @if(($payroll->absent_deduction ?? 0) > 0)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-gray-600">Absent Deduction</span>
                                            <span class="font-medium text-red-600">₱{{ number_format($payroll->absent_deduction, 2) }}</span>
                                        </div>
                                    @endif
                                @endif

                                @if(($payroll->loan_deduction ?? 0) > 0 || ($payroll->leave_without_pay_deduction ?? 0) > 0 || ($payroll->other_deduction ?? 0) > 0)
                                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mt-4">Other Deductions</p>
                                    @if(($payroll->loan_deduction ?? 0) > 0)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-gray-600">Loan Payment</span>
                                            <span class="font-medium text-red-600">₱{{ number_format($payroll->loan_deduction, 2) }}</span>
                                        </div>
                                    @endif
                                    @if(($payroll->leave_without_pay_deduction ?? 0) > 0)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-gray-600">LWOP Deduction</span>
                                            <span class="font-medium text-red-600">₱{{ number_format($payroll->leave_without_pay_deduction, 2) }}</span>
                                        </div>
                                    @endif
                                    @if(($payroll->other_deduction ?? 0) > 0)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-gray-600">Other Deductions</span>
                                            <span class="font-medium text-red-600">₱{{ number_format($payroll->other_deduction, 2) }}</span>
                                        </div>
                                    @endif
                                @endif

                                <div class="flex justify-between items-center py-3 bg-red-50 rounded-lg px-3 mt-2">
                                    <span class="font-semibold text-gray-900">Total Deductions</span>
                                    <span class="font-bold text-lg text-red-600">₱{{ number_format($payroll->total_deductions ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Net Pay Card --}}
                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg shadow-lg text-white p-6">
                        <p class="text-green-100 text-sm">Net Pay</p>
                        <p class="text-3xl font-bold mt-1">₱{{ number_format($payroll->net_pay ?? 0, 2) }}</p>
                        <div class="mt-4 pt-4 border-t border-green-400 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-green-100">Gross</span>
                                <span>₱{{ number_format($payroll->gross_pay ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-100">Deductions</span>
                                <span>-₱{{ number_format($payroll->total_deductions ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Info --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h4 class="text-sm font-semibold text-gray-700 mb-4">Payslip Info</h4>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <p class="text-gray-500">Status</p>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ ucfirst($payroll->status) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-gray-500">Released On</p>
                                    <p class="font-medium">{{ $payroll->released_at ? $payroll->released_at->format('M d, Y g:i A') : '-' }}</p>
                                </div>
                                @if($payroll->email_sent_at)
                                    <div>
                                        <p class="text-gray-500">Email Sent</p>
                                        <p class="font-medium text-green-600">{{ $payroll->email_sent_at->format('M d, Y') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h4 class="text-sm font-semibold text-gray-700 mb-4">Actions</h4>
                            <div class="space-y-2">
                                <a href="{{ route('payslip.download', $payroll) }}" 
                                   class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm font-medium">
                                    Download PDF
                                </a>
                                <a href="{{ route('payslip.view', $payroll) }}" target="_blank"
                                   class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition text-sm font-medium">
                                    View PDF in Browser
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Help --}}
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-yellow-800 mb-2">Questions?</h4>
                        <p class="text-sm text-yellow-700">
                            If you have any questions about your payslip, please contact the HR department.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
