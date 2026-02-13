<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Shift Change Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">
                            @if(auth()->user()->hasRole('super_admin'))
                                All Shift Change Requests
                            @else
                                My Requests
                            @endif
                        </h3>
                        <a href="{{ route('shift-change-requests.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Request Change
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    @if(auth()->user()->hasRole('super_admin'))
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    @endif
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Schedule</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Schedule</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    @if(auth()->user()->hasRole('super_admin'))
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($requests as $req)
                                <tr class="hover:bg-gray-50 transition">
                                    @if(auth()->user()->hasRole('super_admin'))
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs ring-2 ring-white">
                                                    {{ substr($req->employee->name ?? 'U', 0, 1) }}
                                                </div>
                                                <span class="text-sm font-medium text-gray-900">{{ $req->employee->name ?? 'Unknown' }}</span>
                                            </div>
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $req->requested_date->format('M d, Y') }}
                                        <span class="text-xs text-gray-400 block">{{ $req->requested_date->format('l') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $req->current_schedule }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $req->new_schedule }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $req->reason }}">{{ $req->reason }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($req->status === 'approved')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                                Approved
                                            </span>
                                        @elseif($req->status === 'rejected')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    @if(auth()->user()->hasRole('super_admin'))
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if($req->status === 'pending')
                                                <div class="flex justify-end gap-2">
                                                    <form action="{{ route('shift-change-requests.approve', $req) }}" method="POST" onsubmit="return confirm('Approve this request?');">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-full transition">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('shift-change-requests.reject', $req) }}" method="POST" onsubmit="return confirm('Reject this request?');">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-full transition">
                                                            Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs italic">
                                                    Processed by {{ $req->approver->name ?? 'Admin' }}
                                                </span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->hasRole('super_admin') ? '7' : '5' }}" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <p>No shift change requests found.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
