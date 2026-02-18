<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Payroll Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Quick Link / Dropdown Section (Matches Screenshot) -->
            <div class="bg-gray-50 border-t-4 border-green-200 rounded-lg shadow-sm">
                <div class="p-4 border-b border-gray-100 bg-white rounded-t-lg">
                    <h3 class="text-sm font-bold text-green-800 uppercase tracking-wider">Regular Payroll</h3>
                </div>
                <div class="p-8 bg-white rounded-b-lg">
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                        <div class="w-full md:w-1/3">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Payroll Period-Payslip</label>
                        </div>
                        <div class="w-full md:w-2/3" x-data="{ payrollId: '' }">
                            <div class="flex gap-2">
                                <select 
                                    x-model="payrollId"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                >
                                    <option value="">Select Payroll Period-To View Payslip</option>
                                    @foreach($allPayrolls as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->payrollPeriod->start_date->format('F d Y') }} to {{ $p->payrollPeriod->end_date->format('F d Y') }} (Paydate:{{ $p->payrollPeriod->pay_date->format('Y-m-d') }})
                                        </option>
                                    @endforeach
                                </select>
                                <button 
                                    @click="if(payrollId) window.location.href = '/payroll/my-payslip/' + payrollId"
                                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors font-bold shadow-sm flex-shrink-0"
                                    :disabled="!payrollId"
                                    :class="!payrollId ? 'opacity-50 cursor-not-allowed' : ''"
                                >
                                    Go
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Year Summary -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-gray-800">Earnings Summary ({{ $year }})</h3>
                        <form method="GET" x-ref="yearForm" class="flex items-center gap-2">
                            <select name="year" @change="$refs.yearForm.submit()" class="text-xs border-gray-200 rounded-lg">
                                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-100/50">
                            <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-1">Gross Pay</p>
                            <p class="text-xl font-black text-gray-900">₱{{ number_format($ytdSummary['gross'], 2) }}</p>
                        </div>
                        <div class="p-4 bg-green-50 rounded-xl border border-green-100/50">
                            <p class="text-[10px] font-bold text-green-600 uppercase tracking-wider mb-1">Net Pay</p>
                            <p class="text-xl font-black text-gray-900">₱{{ number_format($ytdSummary['net'], 2) }}</p>
                        </div>
                        <div class="p-4 bg-red-50 rounded-xl border border-red-100/50">
                            <p class="text-[10px] font-bold text-red-600 uppercase tracking-wider mb-1">Deductions</p>
                            <p class="text-xl font-black text-gray-900">₱{{ number_format($ytdSummary['deductions'], 2) }}</p>
                        </div>
                        <div class="p-4 bg-purple-50 rounded-xl border border-purple-100/50">
                            <p class="text-[10px] font-bold text-purple-600 uppercase tracking-wider mb-1">Overtime</p>
                            <p class="text-xl font-black text-gray-900">₱{{ number_format($ytdSummary['overtime'], 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Recent History (Brief) -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <h3 class="font-bold text-gray-800 mb-6">Recent History</h3>
                    <div class="space-y-3">
                        @forelse($payrolls->take(5) as $payroll)
                            <div class="flex items-center justify-between p-3 rounded-lg border border-gray-50 hover:bg-gray-50 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xs uppercase">
                                        {{ $payroll->payrollPeriod->start_date->format('M') }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">{{ $payroll->payrollPeriod->start_date->format('M d') }} - {{ $payroll->payrollPeriod->end_date->format('M d, Y') }}</p>
                                        <p class="text-[10px] text-gray-500">Paid: {{ $payroll->payrollPeriod->pay_date->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <p class="text-xs font-black text-green-600">₱{{ number_format($payroll->net_pay, 2) }}</p>
                                    <a href="{{ route('payroll.my-payslip', $payroll) }}" class="p-2 rounded-lg bg-gray-100 text-gray-500 hover:bg-blue-600 hover:text-white transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6">
                                <p class="text-sm text-gray-400">No records found for this period.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
</x-app-layout>
