<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 leading-tight tracking-tight">
                    {{ __('Financial Hub') }}
                </h2>
                <p class="text-sm text-slate-500 mt-1">Manage your earnings, deductions and historical payslips</p>
            </div>
            
            <div class="flex items-center gap-3">
                <form action="{{ route('payslip.index') }}" method="GET" class="flex items-center">
                    <div class="relative">
                        <select name="year" id="year" onchange="this.form.submit()" 
                                class="pl-3 pr-10 py-2 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg bg-white shadow-sm appearance-none font-medium text-slate-700">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>FY {{ $y }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </form>
                
                <a href="{{ route('payslip.ytd-summary', ['year' => $year]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-lg shadow-sm transition-all duration-200 ease-in-out transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    YTD Statement
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- YTD Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <span class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </span>
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">Gross</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">YTD Accumulation</p>
                        <p class="text-2xl font-bold text-slate-900">₱{{ number_format($ytdSummary['total_gross'], 2) }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <span class="p-2 bg-rose-50 text-rose-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </span>
                        <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded">Tax/Ded</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Deductions</p>
                        <p class="text-2xl font-bold text-slate-900">₱{{ number_format($ytdSummary['total_deductions'], 2) }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </span>
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded">Net</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Take-home Pay</p>
                        <p class="text-2xl font-bold text-indigo-600">₱{{ number_format($ytdSummary['total_net'], 2) }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <span class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </span>
                        <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded">Cycles</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Processed Periods</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $ytdSummary['total_periods'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Contributions Tracker --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-8 overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Statutory Contributions Analysis (FY {{ $year }})</h3>
                    <div class="flex gap-2">
                        <div class="h-2 w-2 rounded-full bg-indigo-500"></div>
                        <div class="h-2 w-2 rounded-full bg-slate-300"></div>
                        <div class="h-2 w-2 rounded-full bg-slate-300"></div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 text-center">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">SSS</p>
                            <p class="text-lg font-bold text-slate-900">₱{{ number_format($ytdSummary['total_sss'], 2) }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 text-center">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">PhilHealth</p>
                            <p class="text-lg font-bold text-slate-900">₱{{ number_format($ytdSummary['total_philhealth'], 2) }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 text-center">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Pag-IBIG</p>
                            <p class="text-lg font-bold text-slate-900">₱{{ number_format($ytdSummary['total_pagibig'], 2) }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 text-center">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">W/H Tax</p>
                            <p class="text-lg font-bold text-slate-900">₱{{ number_format($ytdSummary['total_tax'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payslips Grid --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Payroll History - {{ $year }}</h3>
                    <span class="text-xs text-slate-400 font-medium">{{ $payslips->total() }} Records found</span>
                </div>

                @if($payslips->isEmpty())
                    <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-16 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-50 rounded-full mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <p class="text-slate-600 font-bold text-lg">No payslips available</p>
                        <p class="text-slate-400 text-sm max-w-xs mx-auto">Records for the selected year will appear here once they are processed and released by HR.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($payslips as $payslip)
                            <div class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl hover:border-indigo-100 transition-all duration-300 overflow-hidden flex flex-col">
                                <div class="p-5 flex-1">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        </div>
                                        <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">
                                            {{ $payslip->status }}
                                        </span>
                                    </div>

                                    <h4 class="text-slate-900 font-bold text-base group-hover:text-indigo-600 transition-colors">
                                        {{ $payslip->payrollPeriod->start_date->format('M d') }} - {{ $payslip->payrollPeriod->end_date->format('M d, Y') }}
                                    </h4>
                                    <p class="text-xs text-slate-400 font-medium mb-4 capitalize">{{ $payslip->payrollPeriod->type }} cycle • Paid {{ $payslip->payrollPeriod->payout_date->format('M d, Y') }}</p>
                                    
                                    <div class="space-y-3 pt-4 border-t border-slate-50">
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="text-slate-500 font-medium">Gross Earnings</span>
                                            <span class="text-slate-700 font-bold">₱{{ number_format($payslip->gross_pay, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="text-slate-500 font-medium">Total Reductions</span>
                                            <span class="text-rose-500 font-bold">-₱{{ number_format($payslip->total_deductions, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center pt-2">
                                            <span class="text-xs font-black text-slate-400 uppercase tracking-tighter">Net Take-home</span>
                                            <span class="text-xl font-black text-slate-900">₱{{ number_format($payslip->net_pay, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-slate-50 p-2 flex gap-2 border-t border-slate-100">
                                    <a href="{{ route('payslip.show', $payslip) }}" 
                                       class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-white text-slate-700 text-xs font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition shadow-sm">
                                        <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View
                                    </a>
                                    <a href="{{ route('payslip.download', $payslip) }}" 
                                       class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition shadow-indigo-100 shadow-md">
                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Download
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($payslips->hasPages())
                        <div class="mt-8">
                            {{ $payslips->appends(['year' => $year])->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
