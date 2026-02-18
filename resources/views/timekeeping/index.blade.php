<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Timekeeping / Transactions') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quick Actions Panel -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Record Transaction</h3>
                            
                            <!-- Submit TK Complaint Button -->
                            <div class="mb-6">
                                <a href="{{ route('concerns.user-create', ['category' => 'timekeeping', 'title' => 'TK Discrepancy - ' . now()->format('M d, Y')]) }}" 
                                   class="flex items-center justify-center gap-2 w-full bg-red-50 text-red-700 font-bold py-3 px-4 rounded-xl border-2 border-red-100 hover:bg-red-100 transition-all duration-200 shadow-sm group">
                                    <svg class="w-5 h-5 text-red-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Report TK Complaint
                                </a>
                                <p class="text-[10px] text-gray-400 mt-2 text-center italic">Report errors in logs, missing entries, or system issues.</p>
                            </div>

                            <form action="{{ route('timekeeping.store') }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
                                        <select name="transaction_type" required class="w-full rounded-md border-gray-300 shadow-sm">
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
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                        <textarea name="notes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Add any notes..."></textarea>
                                    </div>
                                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
                                        Record Transaction
                                    </button>
                                </div>
                            </form>

                            <!-- Quick Buttons -->
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Quick Actions</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    <form action="{{ route('timekeeping.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="transaction_type" value="break_start">
                                        <button type="submit" class="w-full bg-yellow-500 text-white py-2 px-3 rounded text-sm hover:bg-yellow-600 transition">
                                            Start Break
                                        </button>
                                    </form>
                                    <form action="{{ route('timekeeping.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="transaction_type" value="break_end">
                                        <button type="submit" class="w-full bg-green-500 text-white py-2 px-3 rounded text-sm hover:bg-green-600 transition">
                                            End Break
                                        </button>
                                    </form>
                                    <form action="{{ route('timekeeping.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="transaction_type" value="aux_meeting">
                                        <button type="submit" class="w-full bg-blue-500 text-white py-2 px-3 rounded text-sm hover:bg-blue-600 transition">
                                            Meeting
                                        </button>
                                    </form>
                                    <form action="{{ route('timekeeping.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="transaction_type" value="aux_available">
                                        <button type="submit" class="w-full bg-emerald-500 text-white py-2 px-3 rounded text-sm hover:bg-emerald-600 transition">
                                            Available
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Summary -->
                    <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Summary</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Time In</dt>
                                    <dd class="text-sm font-medium text-gray-900">
                                        {{ $summary['time_in'] ? $summary['time_in']->format('h:i A') : '--:--' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Time Out</dt>
                                    <dd class="text-sm font-medium text-gray-900">
                                        {{ $summary['time_out'] ? $summary['time_out']->format('h:i A') : '--:--' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Total Break</dt>
                                    <dd class="text-sm font-medium text-gray-900">
                                        {{ floor($summary['total_break_minutes'] / 60) }}h {{ $summary['total_break_minutes'] % 60 }}m
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Productive Time</dt>
                                    <dd class="text-sm font-medium text-green-600">
                                        {{ floor($summary['productive_minutes'] / 60) }}h {{ $summary['productive_minutes'] % 60 }}m
                                    </dd>
                                </div>
                            </dl>

                            @if(count($summary['aux_breakdown']) > 0)
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Activity Breakdown</h4>
                                    <div class="space-y-1">
                                        @foreach($summary['aux_breakdown'] as $activity => $count)
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-500">{{ $activity }}</span>
                                                <span class="text-gray-700">{{ $count }}x</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Transactions List -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Transaction History</h3>
                                
                                <!-- Filter -->
                                <form method="GET" class="flex items-center gap-2">
                                    <input type="date" name="date" value="{{ request('date', today()->toDateString()) }}" 
                                           class="rounded-md border-gray-300 text-sm">
                                    <button type="submit" class="bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-300">
                                        Filter
                                    </button>
                                </form>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse($transactions as $transaction)
                                            <tr class="{{ $transaction->isVoided() ? 'opacity-50' : '' }}">
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $transaction->transaction_time->format('h:i:s A') }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs rounded-full {{ $transaction->color_badge }}">
                                                        {{ $transaction->label }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">
                                                    {{ $transaction->notes ?? '-' }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                                    @if($transaction->isVoided())
                                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                            Voided
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                            Active
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                                    No transactions recorded for this date
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
</x-app-layout>
