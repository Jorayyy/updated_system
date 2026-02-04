<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Payslips') }}
            </h2>
            <div class="flex items-center space-x-4">
                <form action="{{ route('payslip.index') }}" method="GET" class="flex items-center space-x-2">
                    <label for="year" class="text-sm text-gray-600">Year:</label>
                    <select name="year" id="year" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm text-sm">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('payslip.ytd-summary', ['year' => $year]) }}" 
                   class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition">
                    Download YTD Summary
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- YTD Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">YTD Gross Pay</p>
                                <p class="text-xl font-semibold text-gray-900">₱{{ number_format($ytdSummary['total_gross'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 text-red-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">YTD Deductions</p>
                                <p class="text-xl font-semibold text-gray-900">₱{{ number_format($ytdSummary['total_deductions'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">YTD Net Pay</p>
                                <p class="text-xl font-semibold text-green-600">₱{{ number_format($ytdSummary['total_net'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pay Periods</p>
                                <p class="text-xl font-semibold text-gray-900">{{ $ytdSummary['total_periods'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contributions Summary --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700">{{ $year }} Government Contributions</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500">SSS</p>
                            <p class="text-lg font-semibold text-gray-900">₱{{ number_format($ytdSummary['total_sss'], 2) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500">PhilHealth</p>
                            <p class="text-lg font-semibold text-gray-900">₱{{ number_format($ytdSummary['total_philhealth'], 2) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500">Pag-IBIG</p>
                            <p class="text-lg font-semibold text-gray-900">₱{{ number_format($ytdSummary['total_pagibig'], 2) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500">Withholding Tax</p>
                            <p class="text-lg font-semibold text-gray-900">₱{{ number_format($ytdSummary['total_tax'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payslips List --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700">{{ $year }} Payslips</h3>
                </div>

                @if($payslips->isEmpty())
                    <div class="p-12 text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="mt-4 text-lg font-medium">No payslips available for {{ $year }}</p>
                        <p class="mt-1">Your payslips will appear here once released.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                        @foreach($payslips as $payslip)
                            <div class="border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <p class="text-sm text-gray-500">{{ $payslip->payrollPeriod->start_date->format('M d') }} - {{ $payslip->payrollPeriod->end_date->format('M d, Y') }}</p>
                                            <p class="text-xs text-gray-400">Pay Date: {{ $payslip->payrollPeriod->pay_date->format('M d, Y') }}</p>
                                        </div>
                                        <div class="flex flex-col items-end">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ ucfirst($payslip->status) }}
                                            </span>
                                            @if($payslip->is_manually_adjusted)
                                                <span class="mt-1 text-[10px] font-bold text-amber-600 uppercase">Adjusted</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Gross Pay</span>
                                            <span class="font-medium">₱{{ number_format($payslip->gross_pay, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Deductions</span>
                                            <span class="font-medium text-red-600">-₱{{ number_format($payslip->total_deductions, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between pt-2 border-t border-gray-200">
                                            <span class="font-semibold text-gray-900">Net Pay</span>
                                            <span class="font-bold text-green-600">₱{{ number_format($payslip->net_pay, 2) }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex space-x-2">
                                        <a href="{{ route('payslip.show', $payslip) }}" 
                                           class="flex-1 text-center px-3 py-2 bg-indigo-100 text-indigo-700 text-sm font-medium rounded-md hover:bg-indigo-200 transition">
                                            View
                                        </a>
                                        <a href="{{ route('payslip.download', $payslip) }}" 
                                           class="flex-1 text-center px-3 py-2 bg-green-100 text-green-700 text-sm font-medium rounded-md hover:bg-green-200 transition">
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($payslips->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $payslips->appends(['year' => $year])->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
