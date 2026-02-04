<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payslip Details') }}
            </h2>
            <a href="{{ route('payslip.index') }}" class="text-indigo-600 hover:text-indigo-900">
                ← Back to My Payslips
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Period Info Card --}}
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg text-white p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-indigo-200 text-sm">Pay Period</p>
                        <p class="text-2xl font-bold">
                            {{ $payroll->payrollPeriod->start_date->format('M d') }} - {{ $payroll->payrollPeriod->end_date->format('M d, Y') }}
                        </p>
                        <p class="text-indigo-200 text-sm mt-1">Pay Date: {{ $payroll->payrollPeriod->pay_date->format('F d, Y') }}</p>
                        @if($payroll->is_manually_adjusted)
                            <div class="mt-2 text-xs font-semibold uppercase tracking-wider text-amber-200">
                                <span class="bg-indigo-700 bg-opacity-30 px-2 py-1 rounded border border-indigo-400">
                                    <i class="fas fa-edit mr-1"></i> Manually Adjusted
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="text-right">
                        <a href="{{ route('payslip.download', $payroll) }}" 
                           class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 rounded-lg font-semibold text-sm hover:bg-indigo-50 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download PDF
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Work Summary --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700">Work Summary</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <p class="text-2xl font-bold text-gray-900">{{ $payroll->days_worked ?? 0 }}</p>
                                    <p class="text-sm text-gray-500">Days Worked</p>
                                </div>
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <p class="text-2xl font-bold text-gray-900">{{ number_format($payroll->hours_worked ?? 0, 1) }}</p>
                                    <p class="text-sm text-gray-500">Hours Worked</p>
                                </div>
                                <div class="text-center p-3 bg-blue-50 rounded-lg">
                                    <p class="text-2xl font-bold text-blue-600">{{ number_format($payroll->overtime_hours ?? 0, 1) }}</p>
                                    <p class="text-sm text-gray-500">OT Hours</p>
                                </div>
                                <div class="text-center p-3 bg-red-50 rounded-lg">
                                    <p class="text-2xl font-bold text-red-600">{{ $payroll->late_minutes ?? 0 }}</p>
                                    <p class="text-sm text-gray-500">Late (mins)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Earnings --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 border-b border-gray-200 bg-green-50">
                            <h3 class="text-lg font-semibold text-green-700">Earnings</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Basic Pay</span>
                                    <span class="font-medium">₱{{ number_format($payroll->basic_pay ?? 0, 2) }}</span>
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
