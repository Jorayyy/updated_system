<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Leave Requests') }}
            </h2>
            <a href="{{ route('leaves.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors duration-200">
                New Leave Request
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Leave Balances -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 transition-colors duration-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">My Leave Balances</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($leaveBalances as $balance)
                            <div class="border border-gray-200 rounded-lg p-4 bg-white transition-colors duration-200">
                                <div class="text-sm text-gray-500">{{ $balance->leaveType->name }}</div>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl font-bold text-indigo-600">{{ $balance->balance }}</span>
                                    <span class="text-sm text-gray-400">/ {{ $balance->leaveType->max_days }} days</span>
                                </div>
                                <div class="text-xs text-gray-400 mt-1">Used: {{ $balance->used }} | Earned: {{ $balance->earned }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 transition-colors duration-200">
                <div class="p-6">
                    <form method="GET" class="flex flex-wrap items-center gap-4">
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
                        <select name="year" class="border-gray-300 rounded-md shadow-sm">
                            @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                                <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors duration-200">
                            Filter
                        </button>
                        <a href="{{ route('leaves.index') }}" class="text-gray-600 hover:text-gray-800 transition-colors duration-200">
                            Clear
                        </a>
                    </form>
                </div>
            </div>

            <!-- Leave Requests List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-200">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leave Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date From</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date To</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Days</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($leaveRequests as $leave)
                                    <tr class="transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full" style="background-color: {{ $leave->leaveType->color ?? '#e5e7eb' }}20; color: {{ $leave->leaveType->color ?? '#374151' }}">
                                                {{ $leave->leaveType->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leave->start_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leave->end_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900">{{ $leave->total_days }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <div class="max-w-xs truncate" title="{{ $leave->reason }}">{{ $leave->reason }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($leave->status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($leave->status == 'approved') bg-green-100 text-green-800
                                                @elseif($leave->status == 'rejected') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($leave->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                            <a href="{{ route('leaves.show', $leave) }}" class="text-indigo-600 hover:text-indigo-900 mr-2 transition-colors duration-200">
                                                View
                                            </a>
                                            @if($leave->status == 'pending')
                                                <form action="{{ route('leaves.cancel', $leave) }}" method="POST" class="inline" 
                                                    onsubmit="return confirm('Are you sure you want to cancel this leave request?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors duration-200">
                                                        Cancel
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No leave requests found</td>
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
</x-app-layout>
