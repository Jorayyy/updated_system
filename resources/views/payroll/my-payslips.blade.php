<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Payslips') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Year Filter -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="flex items-center gap-4">
                        <select name="year" class="border-gray-300 rounded-md shadow-sm">
                            @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            View
                        </button>
                    </form>
                </div>
            </div>

            <!-- YTD Summary -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">YTD Gross Pay</div>
                    <div class="text-2xl font-bold text-gray-800">₱{{ number_format($ytdSummary['gross'], 2) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">YTD Net Pay</div>
                    <div class="text-2xl font-bold text-green-600">₱{{ number_format($ytdSummary['net'], 2) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">YTD Deductions</div>
                    <div class="text-2xl font-bold text-red-600">₱{{ number_format($ytdSummary['deductions'], 2) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">YTD Overtime</div>
                    <div class="text-2xl font-bold text-purple-600">₱{{ number_format($ytdSummary['overtime'], 2) }}</div>
                </div>
            </div>

            <!-- Payslips List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Payslip History</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pay Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Days Worked</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross Pay</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Deductions</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net Pay</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payrolls as $payroll)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $payroll->payrollPeriod->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $payroll->payrollPeriod->start_date->format('M d') }} - 
                                                {{ $payroll->payrollPeriod->end_date->format('M d, Y') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $payroll->payrollPeriod->pay_date->format('M d, Y') }}
                                        </td>
                                        @if($payroll->is_posted || in_array($payroll->status, ['released', 'paid']))
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                {{ $payroll->days_worked }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                ₱{{ number_format($payroll->gross_pay, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                                ₱{{ number_format($payroll->total_deductions, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-green-600">
                                                ₱{{ number_format($payroll->net_pay, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <a href="{{ route('payroll.my-payslip', $payroll) }}" 
                                                    class="text-indigo-600 hover:text-indigo-900 text-sm mr-2">View</a>
                                                <a href="{{ route('payroll.my-payslip-pdf', $payroll) }}" 
                                                    class="text-green-600 hover:text-green-900 text-sm">PDF</a>
                                            </td>
                                        @else
                                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Payslip not posted yet
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No payslips found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $payrolls->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
