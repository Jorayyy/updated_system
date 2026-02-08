<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Leave Request Management') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Statistics -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-yellow-400">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending</div>
                    <div class="text-3xl font-black text-yellow-600">{{ $stats['pending'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-green-400">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Approved</div>
                    <div class="text-3xl font-black text-green-600">{{ $stats['approved'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-red-400">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rejected</div>
                    <div class="text-3xl font-black text-red-600">{{ $stats['rejected'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-indigo-400">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">This Month</div>
                    <div class="text-3xl font-black text-indigo-600">{{ $stats['total_month'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 shadow-sm border border-gray-100">
                <div class="p-4">
                    <form method="GET" class="flex flex-wrap items-center gap-3">
                        <div class="flex-1 min-w-[200px]">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Search employee..."
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                        <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm min-w-[120px]">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        <select name="campaign_id" class="border-gray-300 rounded-md shadow-sm text-sm font-bold text-blue-600">
                            <option value="">All Campaigns</option>
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>
                            @endforeach
                        </select>
                        <div class="flex items-center gap-2">
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border-gray-300 rounded-md shadow-sm text-sm">
                            <span class="text-gray-400">to</span>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm font-semibold transition">
                            Apply Filters
                        </button>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Employee Info</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Job Assignment</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Leave Particulars</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-sm">
                            @forelse($leaveRequests as $leave)
                                <tr class="{{ $leave->status == 'pending' ? 'bg-yellow-50/30' : '' }} hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ $leave->user->name }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $leave->user->employee_id }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-bold text-blue-600 uppercase">{{ $leave->user->campaign->name ?? 'N/A' }}</div>
                                        <div class="text-[10px] text-gray-500 italic">{{ $leave->user->designation->name ?? 'No Role' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span class="text-xs font-semibold text-gray-800">{{ $leave->leaveType->name }}</span>
                                            <span class="text-[10px] bg-indigo-100 px-1.5 rounded text-indigo-700 font-bold">{{ $leave->total_days }} {{ Str::plural('DAY', $leave->total_days) }}</span>
                                        </div>
                                        <div class="text-[10px] text-gray-500">
                                            {{ $leave->start_date->format('M d, Y') }} - {{ $leave->end_date->format('M d, Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 text-[10px] font-black rounded-full border-2
                                            @if($leave->status == 'pending') border-yellow-200 bg-yellow-50 text-yellow-700
                                            @elseif($leave->status == 'approved') border-green-200 bg-green-50 text-green-700
                                            @elseif($leave->status == 'rejected') border-red-200 bg-red-50 text-red-700
                                            @else border-gray-200 bg-gray-50 text-gray-700 @endif">
                                            {{ strtoupper($leave->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('leaves.admin-show', $leave) }}" 
                                               class="text-gray-400 hover:text-indigo-600 transition" 
                                               title="View Details">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            
                                            @if($leave->status == 'pending' && auth()->user()->isSuperAdmin())
                                                <form action="{{ route('leaves.admin-approve', $leave) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to approve this leave request?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="bg-green-100 hover:bg-green-200 text-green-700 px-2 py-1 rounded text-[10px] font-bold transition flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        APPROVE
                                                    </button>
                                                </form>
                                                <button type="button" onclick="openRejectModal({{ $leave->id }})" 
                                                        class="text-red-500 hover:text-red-700 transition" title="Reject Leave">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="text-gray-400 font-medium">No leave requests matching your criteria.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="reject-modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-6 border w-96 shadow-2xl rounded-xl bg-white">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 15.31c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Reject Leave Request</h3>
                <form id="reject-form" method="POST" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <textarea name="rejection_reason" rows="3" required 
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 text-sm" 
                            placeholder="Reason for rejection (mandatory)"></textarea>
                    </div>
                    <div class="flex justify-center gap-3">
                        <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 shadow-lg shadow-red-200 transition">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openRejectModal(leaveId) {
            document.getElementById('reject-form').action = "/leaves/" + leaveId + "/reject";
            document.getElementById('reject-modal').classList.remove('hidden');
        }
        function closeRejectModal() {
            document.getElementById('reject-modal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
