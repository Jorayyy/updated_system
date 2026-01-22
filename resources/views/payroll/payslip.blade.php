<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Payslip') }}
                </h2>
                <p class="text-sm text-gray-500">{{ $payroll->payrollPeriod->name }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('payroll.payslip-pdf', $payroll) }}" 
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                    Download PDF
                </a>
                <a href="{{ url()->previous() }}" class="text-gray-600 hover:text-gray-800">
                    &larr; Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <!-- Header -->
                    <div class="text-center border-b pb-6 mb-6">
                        <h1 class="text-2xl font-bold text-gray-800">MEBS Call Center</h1>
                        <p class="text-gray-500">Tacloban City, Leyte, Philippines</p>
                        <h2 class="text-xl font-semibold mt-4">PAYSLIP</h2>
                        <p class="text-gray-600">{{ $payroll->payrollPeriod->name }}</p>
                    </div>

                    <!-- Employee Info -->
                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Employee Information</h3>
                            <div class="space-y-1">
                                <p><span class="text-gray-500">Employee ID:</span> <span class="font-medium">{{ $payroll->user->employee_id }}</span></p>
                                <p><span class="text-gray-500">Name:</span> <span class="font-medium">{{ $payroll->user->name }}</span></p>
                                <p><span class="text-gray-500">Department:</span> <span class="font-medium">{{ $payroll->user->department ?? '-' }}</span></p>
                                <p><span class="text-gray-500">Position:</span> <span class="font-medium">{{ $payroll->user->position ?? '-' }}</span></p>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Pay Period</h3>
                            <div class="space-y-1">
                                <p><span class="text-gray-500">Period:</span> <span class="font-medium">{{ $payroll->payrollPeriod->start_date->format('M d') }} - {{ $payroll->payrollPeriod->end_date->format('M d, Y') }}</span></p>
                                <p><span class="text-gray-500">Pay Date:</span> <span class="font-medium">{{ $payroll->payrollPeriod->pay_date->format('M d, Y') }}</span></p>
                                <p><span class="text-gray-500">Days Worked:</span> <span class="font-medium">{{ $payroll->days_worked }}</span></p>
                                <p><span class="text-gray-500">Hours Worked:</span> <span class="font-medium">{{ $payroll->hours_worked }}</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings & Deductions -->
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <!-- Earnings -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Earnings</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Basic Pay</span>
                                    <span class="font-medium">₱{{ number_format($payroll->basic_pay, 2) }}</span>
                                </div>
                                @if($payroll->overtime_pay > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Overtime Pay ({{ $payroll->overtime_hours }} hrs)</span>
                                        <span class="font-medium">₱{{ number_format($payroll->overtime_pay, 2) }}</span>
                                    </div>
                                @endif
                                @if($payroll->allowances > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Allowances</span>
                                        <span class="font-medium">₱{{ number_format($payroll->allowances, 2) }}</span>
                                    </div>
                                @endif
                                @if($payroll->bonuses > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Bonuses</span>
                                        <span class="font-medium">₱{{ number_format($payroll->bonuses, 2) }}</span>
                                    </div>
                                @endif
                                @if($payroll->holiday_pay > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Holiday Pay</span>
                                        <span class="font-medium">₱{{ number_format($payroll->holiday_pay, 2) }}</span>
                                    </div>
                                @endif
                                @if($payroll->night_differential > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Night Differential</span>
                                        <span class="font-medium">₱{{ number_format($payroll->night_differential, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between pt-2 border-t font-bold text-lg">
                                    <span>Gross Pay</span>
                                    <span>₱{{ number_format($payroll->gross_pay, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Deductions -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Deductions</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">SSS Contribution</span>
                                    <span class="font-medium text-red-600">₱{{ number_format($payroll->sss_contribution, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">PhilHealth Contribution</span>
                                    <span class="font-medium text-red-600">₱{{ number_format($payroll->philhealth_contribution, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Pag-IBIG Contribution</span>
                                    <span class="font-medium text-red-600">₱{{ number_format($payroll->pagibig_contribution, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Withholding Tax</span>
                                    <span class="font-medium text-red-600">₱{{ number_format($payroll->withholding_tax, 2) }}</span>
                                </div>
                                @if($payroll->late_deductions > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Late Deductions</span>
                                        <span class="font-medium text-red-600">₱{{ number_format($payroll->late_deductions, 2) }}</span>
                                    </div>
                                @endif
                                @if($payroll->absent_deductions > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Absent Deductions</span>
                                        <span class="font-medium text-red-600">₱{{ number_format($payroll->absent_deductions, 2) }}</span>
                                    </div>
                                @endif
                                @if($payroll->undertime_deductions > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Undertime Deductions</span>
                                        <span class="font-medium text-red-600">₱{{ number_format($payroll->undertime_deductions, 2) }}</span>
                                    </div>
                                @endif
                                @if($payroll->other_deductions > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Other Deductions</span>
                                        <span class="font-medium text-red-600">₱{{ number_format($payroll->other_deductions, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between pt-2 border-t font-bold text-lg">
                                    <span>Total Deductions</span>
                                    <span class="text-red-600">₱{{ number_format($payroll->total_deductions, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Pay -->
                    <div class="bg-green-50 rounded-lg p-6 text-center">
                        <p class="text-gray-600 mb-2">Net Pay</p>
                        <p class="text-4xl font-bold text-green-600">₱{{ number_format($payroll->net_pay, 2) }}</p>
                    </div>

                    <!-- Remarks -->
                    @if($payroll->remarks)
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Remarks</h4>
                            <p class="text-gray-700">{{ $payroll->remarks }}</p>
                        </div>
                    @endif

                    <!-- Footer -->
                    <div class="mt-8 pt-6 border-t text-center text-sm text-gray-500">
                        <p>This is a computer-generated payslip. No signature required.</p>
                        <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
