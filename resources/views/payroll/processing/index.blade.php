@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Accounting: Manual Payroll Processing') }}
        </h2>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
            <div class="p-6 bg-white border-b border-gray-200 rounded-lg shadow-sm">
                <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Draft Periods</div>
                <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $pendingPeriods->where('status', 'draft')->count() }}</div>
                <p class="mt-1 text-xs text-gray-400">Ready for processing into payslips</p>
            </div>
            <div class="p-6 bg-white border-b border-gray-200 rounded-lg shadow-sm">
                <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Payroll Groups</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ $groups->count() }}</div>
                <p class="mt-1 text-xs text-gray-400">Active payroll distribution groups</p>
            </div>
            <div class="p-6 bg-white border-b border-gray-200 rounded-lg shadow-sm">
                <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Current Processing</div>
                <div class="mt-2 text-3xl font-bold text-yellow-500">{{ $pendingPeriods->where('status', 'processing')->count() }}</div>
                <p class="mt-1 text-xs text-gray-400">Batches currently in computation</p>
            </div>
        </div>

        <!-- Pending Periods Table -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg mb-8 text-black">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 border-b pb-2">Step 1: Select a Period for Finalization</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-700 font-bold uppercase tracking-wider border-b">
                            <tr>
                                <th class="px-6 py-3">Payroll Group</th>
                                <th class="px-6 py-3">Period Range</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($pendingPeriods as $period)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $period->payrollGroup->name ?? 'Global/Mixed' }}</div>
                                        <div class="text-xs text-gray-500">Code: {{ $period->payrollGroup->code ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-gray-900 font-medium">
                                            {{ \Carbon\Carbon::parse($period->start_date)->format('M d, Y') }} - 
                                            {{ \Carbon\Carbon::parse($period->end_date)->format('M d, Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span @class([
                                            'px-2 py-1 rounded text-xs font-bold uppercase',
                                            'bg-yellow-100 text-yellow-800' => $period->status === 'draft',
                                            'bg-blue-100 text-blue-800 animate-pulse' => $period->status === 'processing',
                                        ])>
                                            {{ $period->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('payroll.processing.select', $period) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            Manual Process
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">
                                        No pending draft periods found. All current periods are already completed or finalized.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection