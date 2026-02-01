<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('transactions.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Transaction History') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Filters -->
                    <form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search transaction #..."
                               class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        <select name="type" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">All Types</option>
                            @foreach($types as $key => $type)
                                <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $type['name'] }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">All Status</option>
                            @foreach($statuses as $key => $status)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded text-sm hover:bg-gray-300 dark:hover:bg-gray-600">
                            Filter
                        </button>
                        <a href="{{ route('transactions.history') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 text-sm">Clear</a>
                    </form>

                    <!-- Transactions Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Transaction #</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Filed</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($transactions as $transaction)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="{{ route('transactions.show', $transaction) }}" class="font-mono text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $transaction->transaction_number }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $transaction->type_name }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            @if($transaction->effective_date)
                                                {{ $transaction->effective_date->format('M d, Y') }}
                                                @if($transaction->effective_date_end && $transaction->effective_date_end->ne($transaction->effective_date))
                                                    - {{ $transaction->effective_date_end->format('M d, Y') }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($transaction->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                                @elseif($transaction->status === 'hr_approved') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                                @elseif($transaction->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                                @elseif($transaction->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                                @endif">
                                                {{ $transaction->status_label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $transaction->created_at->format('M d, Y h:i A') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <a href="{{ route('transactions.show', $transaction) }}" class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                                            @if($transaction->canBeCancelled())
                                                <form action="{{ route('transactions.cancel', $transaction) }}" method="POST" class="inline ml-2"
                                                      onsubmit="return confirm('Are you sure you want to cancel this transaction?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Cancel</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No transactions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($transactions->hasPages())
                        <div class="mt-4">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
