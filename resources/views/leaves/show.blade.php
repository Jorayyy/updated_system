<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Leave Request Details') }}
            </h2>
            @if(auth()->user()->isHr() || auth()->user()->isAdmin())
                <a href="{{ route('leaves.manage') }}" class="text-gray-600 hover:text-gray-800">
                    &larr; Back to List
                </a>
            @else
                <a href="{{ route('leaves.index') }}" class="text-gray-600 hover:text-gray-800">
                    &larr; Back to List
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Status Banner -->
                    <div class="mb-6 p-4 rounded-lg 
                        @if($leave->status == 'pending') bg-yellow-50 border border-yellow-200
                        @elseif($leave->status == 'approved') bg-green-50 border border-green-200
                        @elseif($leave->status == 'rejected') bg-red-50 border border-red-200
                        @else bg-gray-50 border border-gray-200 @endif">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium 
                                    @if($leave->status == 'pending') text-yellow-800
                                    @elseif($leave->status == 'approved') text-green-800
                                    @elseif($leave->status == 'rejected') text-red-800
                                    @else text-gray-800 @endif">
                                    Status: {{ ucfirst($leave->status) }}
                                </span>
                                @if($leave->approved_by)
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $leave->status == 'approved' ? 'Approved' : 'Processed' }} by {{ $leave->approver->name ?? 'Unknown' }}
                                        on {{ $leave->approved_at ? $leave->approved_at->format('M d, Y h:i A') : '-' }}
                                    </p>
                                @endif
                            </div>
                            @if($leave->status == 'pending' && auth()->id() == $leave->user_id)
                                <form action="{{ route('leaves.cancel', $leave) }}" method="POST" 
                                    onsubmit="return confirm('Are you sure you want to cancel this leave request?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Cancel Request
                                    </button>
                                </form>
                            @endif
                        </div>
                        @if($leave->rejection_reason)
                            <div class="mt-2 p-2 bg-white rounded text-sm">
                                <span class="font-medium text-red-700">Rejection Reason:</span>
                                <p class="text-gray-700">{{ $leave->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Leave Details -->
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Leave Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Leave Type</label>
                                <p class="mt-1">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium" 
                                        style="background-color: {{ $leave->leaveType->color ?? '#e5e7eb' }}20; color: {{ $leave->leaveType->color ?? '#374151' }}">
                                        {{ $leave->leaveType->name }}
                                    </span>
                                    @if($leave->leaveType->is_paid)
                                        <span class="ml-2 text-xs text-green-600">(Paid)</span>
                                    @else
                                        <span class="ml-2 text-xs text-gray-500">(Unpaid)</span>
                                    @endif
                                </p>
                            </div>

                            <!-- Total Days -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Total Days</label>
                                <p class="mt-1 text-2xl font-bold text-indigo-600">
                                    {{ $leave->total_days }}
                                    @if($leave->is_half_day)
                                        <span class="text-sm font-normal text-gray-500">(Half Day - {{ $leave->half_day_period }})</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <!-- Start Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Start Date</label>
                                <p class="mt-1 text-lg">{{ $leave->start_date->format('l, F d, Y') }}</p>
                            </div>

                            <!-- End Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500">End Date</label>
                                <p class="mt-1 text-lg">{{ $leave->end_date->format('l, F d, Y') }}</p>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Reason</label>
                            <div class="mt-1 p-4 bg-gray-50 rounded-lg">
                                <p class="text-gray-700 whitespace-pre-wrap">{{ $leave->reason }}</p>
                            </div>
                        </div>

                        <!-- Attachment -->
                        @if($leave->attachment_path)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Attachment</label>
                                <div class="mt-1">
                                    <a href="{{ Storage::url($leave->attachment_path) }}" target="_blank" 
                                        class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm">
                                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        View Attachment
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Request Info -->
                        <div class="pt-6 border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-6 text-sm text-gray-500">
                                <div>
                                    <span class="font-medium">Requested On:</span>
                                    {{ $leave->created_at->format('M d, Y h:i A') }}
                                </div>
                                <div>
                                    <span class="font-medium">Last Updated:</span>
                                    {{ $leave->updated_at->format('M d, Y h:i A') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
