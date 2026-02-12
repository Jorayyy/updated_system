@extends('layouts.app')

@section('title', 'DTR Approval')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="{ 
    showGenerateModal: false, 
    showRejectModal: false,
    rejectId: null,
    rejectReason: '',
    selectedDtrs: [],
    bulkApprove() {
        if (this.selectedDtrs.length === 0) return;
        if (confirm(`Approve ${this.selectedDtrs.length} selected DTR record(s)?`)) {
            this.performAction('/dtr-approval/bulk-approve', { dtr_ids: this.selectedDtrs });
        }
    },
    approveSingle(id) {
        if (confirm('Approve this DTR record?')) {
            this.performAction(`/dtr-approval/${id}/approve`);
        }
    },
    submitRejection() {
        if (!this.rejectReason) {
            alert('Please provide a reason for rejection');
            return;
        }
        this.performAction(`/dtr-approval/${this.rejectId}/reject`, { reason: this.rejectReason });
    },
    async performAction(url, data = null) {
        try {
            const options = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            };
            if (data) options.body = JSON.stringify(data);
            
            const response = await fetch(url, options);
            const result = await response.json();
            
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Action failed');
            }
        } catch (e) {
            alert('An error occurred');
        }
    }
}">
    <!-- Session Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-xl shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('payroll.computation.dashboard') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-2 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Command Center
            </a>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center">
                <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                DTR Approval
            </h1>
            <p class="text-sm text-gray-500 mt-1">Review and validate employee Daily Time Records</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('dtr-approval.pending') }}" class="inline-flex items-center px-4 py-2 border border-yellow-300 rounded-lg shadow-sm text-sm font-medium text-yellow-800 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                <span class="relative flex h-3 w-3 mr-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
                </span>
                Pending ({{ $stats['pending'] ?? 0 }})
            </a>
            <a href="{{ route('dtr-approval.corrections') }}" class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-lg shadow-sm text-sm font-medium text-blue-800 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Corrections ({{ $stats['correction_pending'] ?? 0 }})
            </a>
            <button @click="showGenerateModal = true" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:scale-105 active:scale-95">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                Generate DTRs
            </button>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-blue-50 text-blue-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase">Total DTRs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total'] ?? 0) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-green-50 text-green-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase">Approved</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['approved'] ?? 0) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-yellow-50 text-yellow-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending'] ?? 0) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-red-50 text-red-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase">Rejected</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($stats['rejected'] ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-8 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filter Records
            </h3>
            <a href="{{ route('dtr-approval.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Reset All</a>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('dtr-approval.index') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-600 uppercase">Payroll Period</label>
                    <select name="payroll_period_id" class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" onchange="this.form.submit()">
                        <option value="">All Periods</option>
                        @foreach($payrollPeriods as $period)
                            <option value="{{ $period->id }}" {{ request('payroll_period_id') == $period->id ? 'selected' : '' }}>
                                {{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-600 uppercase">Employee</label>
                    <select name="user_id" class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" onchange="this.form.submit()">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-600 uppercase">Status</label>
                    <select name="status" class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="correction_pending" {{ request('status') == 'correction_pending' ? 'selected' : '' }}>Correction Pending</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-600 uppercase">From Date</label>
                    <input type="date" name="date_from" class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" value="{{ request('date_from') }}" onchange="this.form.submit()">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-600 uppercase">To Date</label>
                    <input type="date" name="date_to" class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" value="{{ request('date_to') }}" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden min-h-[400px]">
        <div class="px-6 py-4 border-b border-gray-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-gray-900">DTR Records</h2>
            <div class="flex items-center gap-2">
                <button type="button" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                        @click="bulkApprove()" 
                        :disabled="selectedDtrs.length === 0">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Approve Selected <template x-if="selectedDtrs.length > 0"><span x-text="'(' + selectedDtrs.length + ')'"></span></template>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="dtrTable">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-bold">
                        <th class="px-6 py-4">Select</th>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4 text-center">Type</th>
                        <th class="px-6 py-4">Logs (In/Out)</th>
                        <th class="px-6 py-4 text-center">Worked</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm border-t border-gray-100 divide-y divide-gray-100">
                    @forelse($dtrs as $dtr)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                @if(in_array($dtr->status, ['draft', 'pending']))
                                    <input type="checkbox" 
                                           class="dtr-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                           value="{{ $dtr->id }}"
                                           x-model="selectedDtrs">
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">{{ $dtr->user->name ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-500">{{ $dtr->user->employee_id ?? '' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                {{ $dtr->date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $dayColors = [
                                        'regular' => 'bg-gray-100 text-gray-800',
                                        'rest_day' => 'bg-blue-100 text-blue-800',
                                        'special_holiday' => 'bg-yellow-100 text-yellow-800',
                                        'regular_holiday' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $dayColors[$dtr->day_type] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $dtr->day_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2 text-gray-700">
                                    <span class="font-mono">{{ $dtr->time_in ?? '--:--' }}</span>
                                    <span class="text-gray-300">→</span>
                                    <span class="font-mono">{{ $dtr->time_out ?? '--:--' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-gray-900 whitespace-nowrap">
                                {{ number_format($dtr->total_hours_worked, 2) }} hrs
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusConfig = [
                                        'draft' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                        'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                                        'approved' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                        'rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                        'correction_pending' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800'],
                                    ];
                                    $cfg = $statusConfig[$dtr->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $cfg['bg'] }} {{ $cfg['text'] }}">
                                    {{ ucfirst(str_replace('_', ' ', $dtr->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('dtr-approval.show', $dtr) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    @if(in_array($dtr->status, ['draft', 'pending']))
                                        <button type="button" 
                                                class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors" 
                                                @click="approveSingle('{{ $dtr->id }}')"
                                                title="Approve">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                        <button type="button" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Reject"
                                                @click="rejectId = '{{ $dtr->id }}'; showRejectModal = true">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-xl font-bold text-gray-400">No DTR records found</p>
                                    <p class="text-gray-400 mt-1">Try adjusting your filters to find what you're looking for.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $dtrs->links() }}
        </div>
    </div>

<!-- Generate DTR Modal (Tailwind/Alpine) -->
<div x-cloak x-show="showGenerateModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showGenerateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showGenerateModal = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="showGenerateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10 text-blue-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.642.257a6 6 0 01-3.86.517l-2.387-.477a2 2 0 00-1.022.547l-1.162 1.162a2 2 0 00.597 3.332l2.362.472a4 4 0 002.573-.344l.642-.257a4 4 0 012.573-.344l2.362.472a2 2 0 00.597-3.332l-1.162-1.162zM10 21h4v-1h-4v1zm2-19v1m0 4v1m0 4v1m-4-7h8m-8 4h8"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Generate DTR Records</h3>
                        <div class="mt-2 text-sm text-gray-500">Manual generation for a specific period and employees.</div>
                    </div>
                </div>
                <form action="{{ route('dtr-approval.generate') }}" method="POST" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Payroll Period <span class="text-red-500">*</span></label>
                        <select name="payroll_period_id" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Period</option>
                            @foreach($payrollPeriods as $period)
                                <option value="{{ $period->id }}">
                                    {{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Employees (optional)</label>
                        <select name="user_ids[]" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 overflow-y-auto" multiple size="5">
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-400 italic font-medium">Hold Ctrl (Windows) or Cmd (Mac) for multiple select.</p>
                    </div>
                    <div class="mt-8 flex justify-end gap-3 pb-2 px-1">
                        <button type="button" @click="showGenerateModal = false" class="px-6 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-md transition-all shadow-blue-200">Generate Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal (Tailwind/Alpine) -->
<div x-cloak x-show="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRejectModal = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10 text-red-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Reject DTR</h3>
                        <p class="text-sm text-gray-500 mt-1">Please provide a reason why this DTR is being rejected.</p>
                    </div>
                </div>
                <div class="mt-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Reason for Rejection <span class="text-red-500">*</span></label>
                        <textarea x-model="rejectReason" rows="4" class="w-full rounded-xl border-gray-200 focus:ring-red-500 focus:border-red-500 placeholder-gray-400" required placeholder="e.g., Working hours don't match logs..."></textarea>
                    </div>
                    <div class="mt-8 flex justify-end gap-3 pb-2 px-1">
                        <button type="button" @click="showRejectModal = false; rejectReason = ''" class="px-6 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="button" @click="submitRejection()" class="px-6 py-2.5 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 shadow-md transition-all shadow-red-200">Reject DTR</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection
