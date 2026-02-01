<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leave Request Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Pending</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Approved</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['approved'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Rejected</div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['rejected'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Total This Month</div>
                    <div class="text-3xl font-bold text-indigo-600">{{ $stats['total_month'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Search employee..."
                            class="border-gray-300 rounded-md shadow-sm">
                        <select name="status" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <select name="leave_type" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">All Types</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ request('leave_type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        <select name="department" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                            class="border-gray-300 rounded-md shadow-sm">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" 
                            class="border-gray-300 rounded-md shadow-sm">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Filter
                        </button>
                        <a href="{{ route('leaves.manage') }}" class="text-gray-600 hover:text-gray-800">Clear</a>
                    </form>
                </div>
            </div>

            <!-- Leave Requests Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leave Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Days</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">HR</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Admin</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($leaveRequests as $leave)
                                    <tr class="{{ $leave->status == 'pending' ? 'bg-yellow-50' : '' }}">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $leave->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $leave->user->employee_id }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $leave->user->department ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full" 
                                                style="background-color: {{ $leave->leaveType->color ?? '#e5e7eb' }}20; color: {{ $leave->leaveType->color ?? '#374151' }}">
                                                {{ $leave->leaveType->name }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center font-medium">
                                            {{ $leave->total_days }}
                                            @if($leave->is_half_day)
                                                <span class="text-xs text-gray-400">(½)</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($leave->hr_status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($leave->hr_status == 'approved') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($leave->hr_status ?? 'pending') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($leave->admin_status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($leave->admin_status == 'approved') bg-purple-100 text-purple-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($leave->admin_status ?? 'pending') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            @if($leave->hr_status === 'approved')
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">✓</span>
                                            @elseif($leave->hr_status === 'rejected')
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">✗</span>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            @if($leave->admin_status === 'approved')
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">✓</span>
                                            @elseif($leave->admin_status === 'rejected')
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">✗</span>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($leave->status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($leave->status == 'approved') bg-green-100 text-green-800
                                                @elseif($leave->status == 'rejected') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($leave->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('leaves.admin-show', $leave) }}" 
                                                    class="text-indigo-600 hover:text-indigo-900 text-sm">View</a>
                                                @if($leave->status == 'pending')
                                                    @if(auth()->user()->isHr() && $leave->hr_status == 'pending')
                                                        <form action="{{ route('leaves.hr-approve', $leave) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="text-blue-600 hover:text-blue-900 text-sm">
                                                                HR Approve
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if(auth()->user()->isAdmin() && $leave->admin_status == 'pending')
                                                        <form action="{{ route('leaves.admin-approve', $leave) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="text-purple-600 hover:text-purple-900 text-sm">
                                                                Admin Approve
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <button type="button" 
                                                        onclick="openRejectModal({{ $leave->id }})"
                                                        class="text-red-600 hover:text-red-900 text-sm">
                                                        Reject
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">No leave requests found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $leaveRequests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="reject-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Leave Request</h3>
                <form id="reject-form" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                        <textarea name="rejection_reason" rows="4" required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Please provide a reason for rejecting this leave request..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeRejectModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openRejectModal(leaveId) {
            const modal = document.getElementById('reject-modal');
            const form = document.getElementById('reject-form');
            form.action = `/leaves/${leaveId}/reject`;
            modal.classList.remove('hidden');
        }

        function closeRejectModal() {
            const modal = document.getElementById('reject-modal');
            modal.classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('reject-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
    </script>
    @endpush
</x-app-layout>
