<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $period->name }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}
                    | Pay Date: {{ $period->pay_date->format('M d, Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 text-sm rounded-full 
                    @if($period->status == 'draft') bg-gray-100 text-gray-800
                    @elseif($period->status == 'processing') bg-yellow-100 text-yellow-800
                    @elseif($period->status == 'completed') bg-green-100 text-green-800
                    @else bg-red-100 text-red-800 @endif">
                    {{ ucfirst($period->status) }}
                </span>
                <a href="{{ route('payroll.periods') }}" class="text-gray-600 hover:text-gray-800">
                    &larr; Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Employees</div>
                    <div class="text-2xl font-bold text-indigo-600">{{ $period->payrolls->count() }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Total Gross</div>
                    <div class="text-2xl font-bold text-gray-800">₱{{ number_format($period->payrolls->sum('gross_pay'), 2) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Total Deductions</div>
                    <div class="text-2xl font-bold text-red-600">₱{{ number_format($period->payrolls->sum('total_deductions'), 2) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Total Net</div>
                    <div class="text-2xl font-bold text-green-600">₱{{ number_format($period->payrolls->sum('net_pay'), 2) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Total Overtime</div>
                    <div class="text-2xl font-bold text-purple-600">₱{{ number_format($period->payrolls->sum('overtime_pay'), 2) }}</div>
                </div>
            </div>

            <!-- Actions -->
            @if($period->status == 'draft')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-4 flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <strong>Note:</strong> Review all payroll records before processing. 
                            Once processed, individual records will be finalized.
                        </div>
                        <form action="{{ route('payroll.process-period', $period) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to process this payroll period? This will calculate payroll for all active employees.')">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700">
                                Process Payroll
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if($period->status == 'completed')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-4 flex items-center justify-between">
                        <div class="text-sm text-green-600">
                            <strong>✓ Completed:</strong> This payroll period has been processed. 
                            Employees can now view their payslips.
                        </div>
                        <a href="{{ route('payroll.report', $period) }}" 
                            class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700">
                            Download Report (PDF)
                        </a>
                    </div>
                </div>
            @endif

            <!-- Payroll Records Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Payroll Records</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Days</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Basic</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">OT</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">SSS</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PhilHealth</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pag-IBIG</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tax</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net Pay</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($period->payrolls as $payroll)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $payroll->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $payroll->user->employee_id }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            {{ $payroll->days_worked }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                                            ₱{{ number_format($payroll->basic_pay, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-purple-600">
                                            ₱{{ number_format($payroll->overtime_pay, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium">
                                            ₱{{ number_format($payroll->gross_pay, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600">
                                            ₱{{ number_format($payroll->sss_contribution, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600">
                                            ₱{{ number_format($payroll->philhealth_contribution, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600">
                                            ₱{{ number_format($payroll->pagibig_contribution, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600">
                                            ₱{{ number_format($payroll->withholding_tax, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-bold text-green-600">
                                            ₱{{ number_format($payroll->net_pay, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <a href="{{ route('payroll.payslip', $payroll) }}" 
                                                class="text-indigo-600 hover:text-indigo-900 text-sm">Payslip</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="px-6 py-4 text-center text-gray-500">
                                            No payroll records found. Click "Process Payroll" to generate records.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr class="font-bold">
                                    <td class="px-4 py-3 text-sm">TOTALS</td>
                                    <td class="px-4 py-3 text-sm text-center">-</td>
                                    <td class="px-4 py-3 text-sm text-right">₱{{ number_format($period->payrolls->sum('basic_pay'), 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-purple-600">₱{{ number_format($period->payrolls->sum('overtime_pay'), 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right">₱{{ number_format($period->payrolls->sum('gross_pay'), 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-red-600">₱{{ number_format($period->payrolls->sum('sss_contribution'), 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-red-600">₱{{ number_format($period->payrolls->sum('philhealth_contribution'), 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-red-600">₱{{ number_format($period->payrolls->sum('pagibig_contribution'), 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-red-600">₱{{ number_format($period->payrolls->sum('withholding_tax'), 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-green-600">₱{{ number_format($period->payrolls->sum('net_pay'), 2) }}</td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
