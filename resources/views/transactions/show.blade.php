<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ url()->previous() }}" class="text-gray-500 hover:text-gray-700 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $transaction->transaction_number }}
                </h2>
            </div>
            @if($transaction->canBeCancelled() && $transaction->user_id === auth()->id())
                <form action="{{ route('transactions.cancel', $transaction) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to cancel this transaction?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                        Cancel Request
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Main Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">
                                {{ $transaction->type_name }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Filed by {{ $transaction->user->name }} on {{ $transaction->created_at->format('M d, Y h:i A') }}
                            </p>
                        </div>
                        <span class="px-3 py-1 text-sm rounded-full 
                            @if($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($transaction->status === 'hr_approved') bg-blue-100 text-blue-800
                            @elseif($transaction->status === 'approved') bg-green-100 text-green-800
                            @elseif($transaction->status === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $transaction->status_label }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Employee Info -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Employee</h4>
                            <p class="text-gray-900">{{ $transaction->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $transaction->user->employee_id }}</p>
                        </div>

                        <!-- Dates -->
                        @if($transaction->effective_date)
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Effective Date</h4>
                                <p class="text-gray-900">
                                    {{ $transaction->effective_date->format('F d, Y') }}
                                    @if($transaction->effective_date_end && $transaction->effective_date_end->ne($transaction->effective_date))
                                        - {{ $transaction->effective_date_end->format('F d, Y') }}
                                    @endif
                                </p>
                                @if($transaction->days_count)
                                    <p class="text-sm text-gray-500">{{ $transaction->days_count }} day(s)</p>
                                @endif
                            </div>
                        @endif

                        <!-- Time -->
                        @if($transaction->time_from || $transaction->time_to)
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Time</h4>
                                <p class="text-gray-900">
                                    {{ $transaction->time_from ? \Carbon\Carbon::parse($transaction->time_from)->format('h:i A') : '' }}
                                    @if($transaction->time_from && $transaction->time_to) - @endif
                                    {{ $transaction->time_to ? \Carbon\Carbon::parse($transaction->time_to)->format('h:i A') : '' }}
                                </p>
                            </div>
                        @endif

                        <!-- Leave Type -->
                        @if($transaction->leaveType)
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Leave Type</h4>
                                <p class="text-gray-900">{{ $transaction->leaveType->name }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Type-specific details -->
                    @if($transaction->details)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-500 mb-3">Additional Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($transaction->details as $key => $value)
                                    <div>
                                        <span class="text-sm text-gray-500">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                        <span class="text-gray-900 ml-2">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Reason -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Reason / Details</h4>
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $transaction->reason }}</p>
                    </div>

                    <!-- Attachment -->
                    @if($transaction->attachment)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Attachment</h4>
                            <div class="flex items-center">
                                <a href="{{ asset('storage/' . $transaction->attachment) }}" target="_blank"
                                   class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 rounded-md hover:bg-blue-100 transition">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    View Attachment
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Rejection Reason -->
                    @if($transaction->isRejected() && $transaction->rejection_reason)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-red-800 mb-2">Rejection Reason</h4>
                                <p class="text-red-700">{{ $transaction->rejection_reason }}</p>
                                <p class="text-sm text-red-600 mt-2">
                                    Rejected by {{ $transaction->rejectedByUser->name ?? 'System' }} 
                                    on {{ $transaction->rejected_at->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approval Timeline -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Approval Timeline</h4>
                    
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        
                        <!-- Filed -->
                        <div class="relative flex items-start mb-6">
                            <div class="absolute left-0 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-12">
                                <p class="font-medium text-gray-900">Request Filed</p>
                                <p class="text-sm text-gray-500">{{ $transaction->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>

                        <!-- HR Approval -->
                        <div class="relative flex items-start mb-6">
                            <div class="absolute left-0 w-8 h-8 rounded-full 
                                {{ $transaction->hr_approved_at ? 'bg-green-500' : ($transaction->isRejected() ? 'bg-red-500' : 'bg-gray-300') }} 
                                flex items-center justify-center">
                                @if($transaction->hr_approved_at)
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @elseif($transaction->isRejected())
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-12">
                                <p class="font-medium text-gray-900">HR Approval</p>
                                @if($transaction->hr_approved_at)
                                    <p class="text-sm text-green-600">
                                        Approved by {{ $transaction->hrApprover->name ?? 'System' }} 
                                        on {{ $transaction->hr_approved_at->format('M d, Y h:i A') }}
                                    </p>
                                @elseif($transaction->isRejected())
                                    <p class="text-sm text-red-600">Rejected</p>
                                @elseif($transaction->isCancelled())
                                    <p class="text-sm text-gray-500">Cancelled</p>
                                @else
                                    <p class="text-sm text-yellow-600">Awaiting HR approval</p>
                                @endif
                            </div>
                        </div>

                        <!-- Admin Approval -->
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-8 h-8 rounded-full 
                                {{ $transaction->admin_approved_at ? 'bg-green-500' : ($transaction->isRejected() ? 'bg-red-500' : 'bg-gray-300') }} 
                                flex items-center justify-center">
                                @if($transaction->admin_approved_at)
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @elseif($transaction->isRejected())
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-12">
                                <p class="font-medium text-gray-900">Admin Approval (Final)</p>
                                @if($transaction->admin_approved_at)
                                    <p class="text-sm text-green-600">
                                        Approved by {{ $transaction->adminApprover->name ?? 'System' }} 
                                        on {{ $transaction->admin_approved_at->format('M d, Y h:i A') }}
                                    </p>
                                @elseif($transaction->isRejected())
                                    <p class="text-sm text-red-600">Rejected</p>
                                @elseif($transaction->isCancelled())
                                    <p class="text-sm text-gray-500">Cancelled</p>
                                @elseif($transaction->status === 'hr_approved')
                                    <p class="text-sm text-yellow-600">Awaiting Admin approval</p>
                                @else
                                    <p class="text-sm text-gray-500">Pending HR approval first</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin/HR Actions -->
            @if((auth()->user()->isAdmin() || auth()->user()->isHr()) && !$transaction->isApproved() && !$transaction->isRejected() && !$transaction->isCancelled())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Actions</h4>
                        
                        <div class="flex flex-wrap gap-3">
                            @if(auth()->user()->isHr() && $transaction->needsHrApproval())
                                <form action="{{ route('transactions.hr-approve', $transaction) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        HR Approve
                                    </button>
                                </form>
                            @endif

                            @if(auth()->user()->isAdmin())
                                <form action="{{ route('transactions.admin-approve', $transaction) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                        {{ $transaction->needsHrApproval() ? 'Approve (Skip HR)' : 'Final Approve' }}
                                    </button>
                                </form>
                            @endif

                            <button type="button" onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Reject
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Reject Modal -->
                <div id="reject-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Transaction</h3>
                        <form action="{{ route('transactions.reject', $transaction) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
                                <textarea name="rejection_reason" rows="3" required
                                          class="w-full border-gray-300 rounded-md shadow-sm"
                                          placeholder="Enter reason..."></textarea>
                            </div>
                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" 
                                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                    Reject
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
