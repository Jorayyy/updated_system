<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leave Request Management') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Statistics -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 transition-colors duration-200">
                    <div class="text-sm text-gray-500">Pending</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 transition-colors duration-200">
                    <div class="text-sm text-gray-500">Approved</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['approved'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 transition-colors duration-200">
                    <div class="text-sm text-gray-500">Rejected</div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['rejected'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 transition-colors duration-200">
                    <div class="text-sm text-gray-500">Total This Month</div>
                    <div class="text-3xl font-bold text-indigo-600">{{ $stats['total_month'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 transition-colors duration-200">
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-200">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Context</th>
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
                                    <tr class="{{ $leave->status == 'pending' ? 'bg-yellow-50' : '' }} transition-colors duration-200">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $leave->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $leave->user->employee_id }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @if($leave->is_transaction)
                                                <span class="px-2 py-0.5 text-[10px] bg-amber-50 text-amber-600 border border-amber-200 rounded font-bold uppercase tracking-tight">
                                                    Transaction
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 text-[10px] bg-indigo-50 text-indigo-600 border border-indigo-200 rounded font-bold uppercase tracking-tight">
                                                    Leave Request
                                                </span>
                                            @endif
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
                                                <a href="{{ $leave->is_transaction ? route('transactions.show', $leave) : route('leaves.admin-show', $leave) }}" 
                                                    class="inline-flex items-center px-2 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-md text-xs font-medium transition-colors border border-indigo-100">
                                                    View
                                                </a>
                                                
                                                @if($leave->status == 'pending' && auth()->user()->isSuperAdmin())
                                                    <button type="button" 
                                                        onclick="confirmApprove('{{ $leave->is_transaction ? route('transactions.admin-approve', $leave) : route('leaves.admin-approve', $leave) }}')"
                                                        class="inline-flex items-center px-2 py-1 bg-green-50 text-green-700 hover:bg-green-100 rounded-md text-xs font-medium transition-colors border border-green-100">
                                                        Approve
                                                    </button>
                                                    
                                                    <button type="button" 
                                                        onclick="confirmReject('{{ $leave->is_transaction ? route('transactions.reject', $leave) : route('leaves.reject', $leave) }}')"
                                                        class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded-md text-xs font-medium transition-colors border border-red-100">
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
                </div>
            </div>
        </div>
    </div>

    <form id="submission-form" method="POST" class="hidden">
        @csrf
        @method('PATCH')
        <input type="hidden" name="rejection_reason" id="submission-reason">
    </form>

    @push('scripts')
    <script>
        function confirmApprove(url) {
            Swal.fire({
                title: 'Approve Leave Request?',
                text: "This will finalize the leave request and update the employee's balanced credits.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10B981',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('submission-form');
                    form.action = url;
                    form.submit();
                }
            });
        }

        function confirmReject(url) {
            Swal.fire({
                title: 'Reject Leave Request?',
                text: "Please provide a reason for rejection:",
                icon: 'warning',
                input: 'textarea',
                inputPlaceholder: 'Enter rejection reason here...',
                inputAttributes: {
                    'aria-label': 'Enter rejection reason'
                },
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Reject Request',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to provide a reason!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('submission-form');
                    form.action = url;
                    document.getElementById('submission-reason').value = result.value;
                    form.submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
