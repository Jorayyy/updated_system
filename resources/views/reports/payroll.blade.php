<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('reports.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Payroll Report') }}
                </h2>
            </div>
            @if($periodId)
                <a href="{{ route('reports.payroll.export', ['period_id' => $periodId]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export CSV
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Period Selector -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.payroll') }}" class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-64">
                            <x-input-label for="period_id" :value="__('Payroll Period')" />
                            <select id="period_id" name="period_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select a period...</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->id }}" {{ $periodId == $period->id ? 'selected' : '' }}>
                                        {{ $period->name }} ({{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-primary-button>View Report</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            @if($totals)
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-gray-500">Total Gross Pay</div>
                        <div class="text-2xl font-bold text-gray-900">₱{{ number_format($totals['gross'], 2) }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-gray-500">Total Deductions</div>
                        <div class="text-2xl font-bold text-red-600">₱{{ number_format($totals['deductions'], 2) }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-gray-500">Total Net Pay</div>
                        <div class="text-2xl font-bold text-green-600">₱{{ number_format($totals['net'], 2) }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-gray-500">Employees</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $payrolls->count() }}</div>
                    </div>
                </div>

                <!-- Government Contributions Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Government Contributions Summary</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center p-4 bg-gray-50 rounded">
                                <div class="text-sm text-gray-500">SSS</div>
                                <div class="text-xl font-bold">₱{{ number_format($totals['sss'], 2) }}</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded">
                                <div class="text-sm text-gray-500">PhilHealth</div>
                                <div class="text-xl font-bold">₱{{ number_format($totals['philhealth'], 2) }}</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded">
                                <div class="text-sm text-gray-500">Pag-IBIG</div>
                                <div class="text-xl font-bold">₱{{ number_format($totals['pagibig'], 2) }}</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded">
                                <div class="text-sm text-gray-500">Withholding Tax</div>
                                <div class="text-xl font-bold">₱{{ number_format($totals['tax'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Payroll Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    @if($payrolls->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Basic</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">OT</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($payrolls as $payroll)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $payroll->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $payroll->user->employee_id }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm text-gray-900">₱{{ number_format($payroll->basic_pay, 2) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm text-gray-900">₱{{ number_format($payroll->overtime_pay, 2) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">₱{{ number_format($payroll->gross_pay, 2) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm text-red-600">₱{{ number_format($payroll->total_deductions, 2) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-bold text-green-600">₱{{ number_format($payroll->net_pay, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-center text-gray-500 py-8">Select a payroll period to view the report.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
