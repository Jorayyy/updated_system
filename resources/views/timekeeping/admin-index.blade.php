<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Timekeeping Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_today'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Transactions Today</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['active_employees'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Logged In Today</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['on_break'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Currently On Break</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['in_meeting'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">In Meeting</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Add Transaction Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Transaction</h3>
                            
                            <form action="{{ route('timekeeping.admin-store') }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employee</label>
                                        <select name="user_id" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm">
                                            <option value="">Select Employee</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Transaction Type</label>
                                        <select name="transaction_type" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm">
                                            @foreach($transactionTypes as $category => $types)
                                                <optgroup label="{{ $category }}">
                                                    @foreach($types as $key => $label)
                                                        <option value="{{ $key }}">{{ $label }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date & Time</label>
                                        <input type="datetime-local" name="transaction_time" required 
                                               value="{{ now()->format('Y-m-d\TH:i') }}"
                                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                                        <textarea name="notes" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm"></textarea>
                                    </div>
                                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition text-sm">
                                        Add Transaction
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Transactions List -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <!-- Filters -->
                            <form method="GET" class="flex flex-wrap items-center gap-3 mb-4">
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       placeholder="Search employee..."
                                       class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                <select name="user_id" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="date" name="date" value="{{ request('date', today()->toDateString()) }}" 
                                       class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                <select name="category" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <select name="status" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                                </select>
                                <button type="submit" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded text-sm hover:bg-gray-300 dark:hover:bg-gray-600">
                                    Filter
                                </button>
                                <a href="{{ route('timekeeping.admin-index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 text-sm">Clear</a>
                            </form>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Employee</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Transaction</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Notes</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @forelse($transactions as $transaction)
                                            <tr class="{{ $transaction->isVoided() ? 'opacity-50 bg-red-50 dark:bg-red-900/10' : '' }}">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $transaction->user->name }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->user->employee_id }}</div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ $transaction->transaction_time->format('M d, Y') }}
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->transaction_time->format('h:i:s A') }}</div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs rounded-full {{ $transaction->color_badge }}">
                                                        {{ $transaction->label }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                                    {{ $transaction->notes ?? '-' }}
                                                    @if($transaction->isVoided())
                                                        <div class="text-xs text-red-500">Void: {{ $transaction->void_reason }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                                    @if($transaction->isVoided())
                                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                            Voided
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                            Active
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                                    @if(!$transaction->isVoided())
                                                        <button type="button" onclick="openVoidModal({{ $transaction->id }})" 
                                                                class="text-red-600 hover:text-red-800 text-sm">
                                                            Void
                                                        </button>
                                                    @else
                                                        <span class="text-gray-400 text-sm">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                    No transactions found
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
        </div>
    </div>

    <!-- Void Modal -->
    <div id="void-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Void Transaction</h3>
            <form id="void-form" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for Voiding</label>
                    <textarea name="void_reason" rows="3" required
                              class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm"
                              placeholder="Enter reason..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeVoidModal()" 
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Void Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openVoidModal(transactionId) {
            document.getElementById('void-form').action = `/timekeeping/${transactionId}/void`;
            document.getElementById('void-modal').classList.remove('hidden');
        }

        function closeVoidModal() {
            document.getElementById('void-modal').classList.add('hidden');
        }

        document.getElementById('void-modal').addEventListener('click', function(e) {
            if (e.target === this) closeVoidModal();
        });
    </script>
    @endpush
</x-app-layout>
