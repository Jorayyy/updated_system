@extends('layouts.app')

@section('title', 'Pending DTR Approvals')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="{ 
    selectedDtrs: [],
    showRejectModal: false,
    rejectId: null,
    allDtrIds: {{ json_encode($pendingDtrs->pluck('id')) }},
    toggleAll() {
        if (this.selectedDtrs.length === this.allDtrIds.length) {
            this.selectedDtrs = [];
        } else {
            this.selectedDtrs = [...this.allDtrIds];
        }
    },
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
            const isJson = response.headers.get('content-type')?.includes('application/json');
            const result = isJson ? await response.json() : null;
            
            if (response.ok && result?.success) {
                location.reload();
            } else {
                alert(result?.message || `Error (${response.status}): ${response.statusText}`);
            }
        } catch (e) {
            console.error('Action failed:', e);
            alert('An error occurred: ' + e.message);
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
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center">
                <svg class="w-8 h-8 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Pending DTR Approvals
            </h1>
            <p class="text-sm text-gray-500 mt-1">Review and validate Daily Time Records awaiting approval</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('dtr-approval.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to All
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-yellow-50 text-yellow-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pending Approval</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['pending'] ?? 0) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-blue-50 text-blue-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Correction Pending</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['correction_pending'] ?? 0) }}</p>
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
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Approved</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['approved'] ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Action Bar -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-8 p-6">
        <div class="flex flex-col md:flex-row items-end gap-4">
            <div class="w-full md:w-1/3">
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Filter by Payroll Period</label>
                <form method="GET" id="filterForm">
                    <select name="payroll_period_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" onchange="this.form.submit()">
                        <option value="">All Periods</option>
                        @foreach($payrollPeriods as $period)
                            <option value="{{ $period->id }}" {{ request('payroll_period_id') == $period->id ? 'selected' : '' }}>
                                {{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="flex-grow flex justify-end">
                @if(request('payroll_period_id') && $pendingDtrs->isNotEmpty())
                    <form action="{{ route('dtr-approval.approve-all-period', request('payroll_period_id')) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-green-600 border border-transparent rounded-xl font-bold text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-lg shadow-green-100 transition-all active:scale-95" onclick="return confirm('Approve ALL pending DTRs for this period?')">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                            Approve All for Period
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Pending List -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden min-h-[400px]">
        <div class="px-6 py-4 border-b border-gray-100 bg-white flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 flex items-center">
                Pending Records 
                <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">{{ $pendingDtrs->total() }}</span>
            </h2>
            <button type="button" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring ring-green-300 disabled:opacity-25 transition"
                    @click="bulkApprove()"
                    :disabled="selectedDtrs.length === 0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Approve Selected <template x-if="selectedDtrs.length > 0"><span x-text="'(' + selectedDtrs.length + ')'"></span></template>
            </button>
        </div>

        <div class="overflow-x-auto">
            @if($pendingDtrs->isEmpty())
                <div class="py-24 flex flex-col items-center justify-center text-center">
                    <div class="p-6 rounded-full bg-green-50 text-green-500 mb-6 transform transition-transform hover:rotate-12">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">All Caught Up!</h3>
                    <p class="text-gray-500 mt-2 max-sm">No pending DTRs to approve.</p>
                </div>
            @else
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-bold border-b border-gray-100">
                            <th class="px-6 py-4">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           @click="toggleAll()" 
                                           :checked="selectedDtrs.length === allDtrIds.length && allDtrIds.length > 0"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                    Select
                                </div>
                            </th>
                            <th class="px-6 py-4">Employee</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Logs</th>
                            <th class="px-6 py-4 text-center">Worked (Hrs)</th>
                            <th class="px-6 py-4 text-center">Late</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @foreach($pendingDtrs as $dtr)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <input type="checkbox" x-model="selectedDtrs" value="{{ $dtr->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900">{{ $dtr->user->name ?? 'N/A' }}</span>
                                        <span class="text-xs text-gray-500">{{ $dtr->user->employee_id ?? '' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    {{ $dtr->dtr_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2 text-gray-700 font-mono text-xs">
                                        <span class="bg-blue-50 px-1.5 py-0.5 rounded text-blue-700 border border-blue-100">{{ $dtr->time_in ?? '--:--' }}</span>
                                        <span class="text-gray-300">→</span>
                                        <span class="bg-gray-50 px-1.5 py-0.5 rounded text-gray-700 border border-gray-200">{{ $dtr->time_out ?? '--:--' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-gray-900">
                                    {{ number_format($dtr->total_hours_worked, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($dtr->late_minutes > 0)
                                        <span class="px-2 py-0.5 rounded bg-red-50 text-red-600 text-xs font-bold border border-red-100">{{ $dtr->late_minutes }}m</span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusConfig = [
                                            'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                                            'correction_pending' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800'],
                                        ];
                                        $cfg = $statusConfig[$dtr->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $cfg['bg'] }} {{ $cfg['text'] }}">
                                        {{ ucfirst(str_replace('_', ' ', $dtr->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('dtr-approval.show', $dtr) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <button type="button" @click="approveSingle('{{ $dtr->id }}')" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Approve">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                        <button type="button" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" @click="rejectId = '{{ $dtr->id }}'; showRejectModal = true" title="Reject">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $pendingDtrs->links() }}
        </div>
    </div>
</div>

<!-- Reject Modal (Tailwind/Alpine) -->
<div x-cloak x-show="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRejectModal = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
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
                <form :action="`/dtr-approval/${rejectId}/reject`" method="POST" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1 tracking-wider">Reason for Rejection <span class="text-red-500">*</span></label>
                        <textarea name="reason" rows="4" class="w-full rounded-xl border-gray-200 focus:ring-red-500 focus:border-red-500 placeholder-gray-400" required placeholder="Describe the issue..."></textarea>
                    </div>
                    <div class="mt-8 flex justify-end gap-3 pb-2">
                        <button type="button" @click="showRejectModal = false" class="px-5 py-2.5 rounded-xl border border-gray-100 bg-gray-50 text-gray-700 font-bold hover:bg-gray-100 transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 shadow-md transition-all shadow-red-200">Reject DTR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

