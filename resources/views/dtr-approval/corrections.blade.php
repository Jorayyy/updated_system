@extends('layouts.app')

@section('title', 'DTR Correction Requests')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="{ 
    selectedDtrs: [],
    showRejectModal: false,
    rejectId: null,
    bulkApprove() {
        if (!confirm('Approve ' + this.selectedDtrs.length + ' correction requests?')) return;
        
        fetch('{{ route('dtr-approval.bulk-approve-corrections') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: this.selectedDtrs })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error approving corrections');
            }
        });
    },
    approveSingle(id) {
        if (!confirm('Approve this correction request?')) return;
        
        fetch(`/dtr-approval/${id}/approve-correction`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center">
                <svg class="w-8 h-8 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Correction Requests
            </h1>
            <p class="text-sm text-gray-500 mt-1">Review employee requested changes to their time records</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('dtr-approval.index') }}" class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-lg shadow-sm text-sm font-medium text-blue-800 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm font-bold">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to All
            </a>
        </div>
    </div>

    <!-- Corrections List Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden min-h-[500px]">
        <div class="px-6 py-4 border-b border-gray-100 bg-white flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 flex items-center tracking-tight">
                Correction Queue
                <span class="ml-2 px-3 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">{{ $corrections->total() }}</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            @if($corrections->isEmpty())
                <div class="py-32 flex flex-col items-center justify-center text-center">
                    <div class="p-6 rounded-full bg-blue-50 text-blue-500 mb-6 transform transition-transform hover:scale-110">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">All Clear!</h3>
                    <p class="text-gray-500 mt-2 max-w-sm">There are no pending correction requests from employees at this time.</p>
                </div>
            @else
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-bold border-b border-gray-100">
                            <th class="px-6 py-4">Employee</th>
                            <th class="px-6 py-4">DTR Date</th>
                            <th class="px-6 py-4">Changes Requested</th>
                            <th class="px-6 py-4">Reason</th>
                            <th class="px-6 py-4">Requested At</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @foreach($corrections as $dtr)
                            @php $correctionData = json_decode($dtr->correction_data, true) ?? []; @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900">{{ $dtr->user->name ?? 'N/A' }}</span>
                                        <span class="text-xs text-gray-500">{{ $dtr->user->employee_id ?? '' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">
                                    {{ $dtr->dtr_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($correctionData as $field => $value)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold uppercase tracking-tight border border-indigo-100 shadow-sm">
                                                {{ ucfirst(str_replace('_', ' ', $field)) }}: <span class="ml-1 text-indigo-900">{{ $value }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <p class="text-xs text-gray-600 leading-relaxed italic line-clamp-2">"{{ $dtr->correction_reason }}"</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400">
                                    {{ $dtr->correction_requested_at?->diffForHumans() ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('dtr-approval.show', $dtr) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Review Full Comparison">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <button type="button" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors approve-correction" data-id="{{ $dtr->id }}" title="Approve Changes">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                        <button type="button" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors reject-correction" data-id="{{ $dtr->id }}" @click="rejectId = '{{ $dtr->id }}'; showRejectModal = true" title="Reject Request">
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
            {{ $corrections->links() }}
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
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Reject Correction</h3>
                        <p class="text-sm text-gray-500 mt-1">Provide a reason for rejecting the employee's requested changes.</p>
                    </div>
                </div>
                <form :action="`/dtr-approval/${rejectId}/reject-correction`" method="POST" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1 tracking-wider">Reason for Rejection <span class="text-red-500">*</span></label>
                        <textarea name="reason" rows="4" class="w-full rounded-xl border-gray-200 focus:ring-red-500 focus:border-red-500 placeholder-gray-400" required placeholder="Why is this correction being denied?"></textarea>
                    </div>
                    <div class="mt-8 flex justify-end gap-3 pb-2">
                        <button type="button" @click="showRejectModal = false" class="px-5 py-2.5 rounded-xl border border-gray-100 bg-gray-50 text-gray-700 font-bold hover:bg-gray-100 transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 shadow-md transition-all shadow-red-200 font-bold">Reject Correction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Approve correction
    document.querySelectorAll('.approve-correction').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Approve this correction request? This will update the DTR record immediately.')) {
                const id = this.dataset.id;
                fetch(`/dtr-approval/${id}/approve-correction`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            }
        });
    });
});
</script>
@endpush
