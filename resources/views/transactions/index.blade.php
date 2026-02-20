<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transactions') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Quick Stats -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-gray-600">Select a transaction type to file a new request</p>
                </div>
                <a href="{{ route('transactions.history') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    View Transaction History
                </a>
            </div>

            <!-- Transaction Types Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($types as $key => $type)
                    @php
                        $url = route('transactions.create', $key);
                        if ($key === 'timekeeping_complaint') {
                            $url = route('concerns.user-create', ['category' => 'timekeeping']);
                        } elseif ($key === 'payroll_complaint') {
                            $url = route('concerns.user-create', ['category' => 'payroll']);
                        }
                    @endphp
                    <a href="{{ $url }}" 
                       class="block bg-white rounded-lg shadow-sm hover:shadow-md transition border-2 border-transparent hover:border-{{ $type['color'] }}-500 overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-{{ $type['color'] }}-100 flex items-center justify-center">
                                    @switch($type['icon'])
                                        @case('calendar')
                                            <svg class="w-6 h-6 text-{{ $type['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            @break
                                        @case('clock')
                                            <svg class="w-6 h-6 text-{{ $type['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            @break
                                        @case('plus-circle')
                                            <svg class="w-6 h-6 text-{{ $type['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            @break
                                        @case('currency-dollar')
                                            <svg class="w-6 h-6 text-{{ $type['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            @break
                                        @case('briefcase')
                                            <svg class="w-6 h-6 text-{{ $type['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            @break
                                        @case('arrow-down')
                                            <svg class="w-6 h-6 text-{{ $type['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                            </svg>
                                            @break
                                        @case('x-circle')
                                            <svg class="w-6 h-6 text-{{ $type['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            @break
                                        @case('exclamation')
                                            <svg class="w-6 h-6 text-{{ $type['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                            @break
                                        @case('refresh')
                                            <svg class="w-6 h-6 text-{{ $type['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            @break
                                        @default
                                            <svg class="w-6 h-6 text-{{ $type['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                    @endswitch
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-base font-medium text-gray-900">{{ $type['name'] }}</h3>
                                    @if(isset($pendingCounts[$key]) && $pendingCounts[$key] > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                            {{ $pendingCounts[$key] }} pending
                                        </span>
                                    @endif
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Recent Transactions -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Transactions</h3>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    @php
                        $recentTransactions = \App\Models\EmployeeTransaction::where('user_id', auth()->id())
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get()
                            ->map(function($txn) {
                                return [
                                    'id' => $txn->id,
                                    'number' => $txn->transaction_number,
                                    'type' => $txn->type_name,
                                    'created_at' => $txn->created_at,
                                    'status' => $txn->status,
                                    'status_label' => $txn->status_label,
                                    'url' => route('transactions.show', $txn)
                                ];
                            });
                        
                        // Also include TK Concerns
                        $recentConcerns = \App\Models\Concern::where('reported_by', auth()->id())
                            ->where('category', 'timekeeping')
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get()
                            ->map(function($con) {
                                return [
                                    'id' => $con->id,
                                    'number' => $con->ticket_number,
                                    'type' => 'Timekeeping (TK) Complaint',
                                    'created_at' => $con->created_at,
                                    'status' => $con->status,
                                    'status_label' => ucfirst(str_replace('_', ' ', $con->status)),
                                    'url' => route('concerns.show', $con)
                                ];
                            });
                            
                        $unifiedTransactions = $recentTransactions->concat($recentConcerns)
                            ->sortByDesc('created_at')
                            ->take(5);
                    @endphp
                    
                    @if($unifiedTransactions->isEmpty())
                        <div class="p-8 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p>No transactions yet. File your first request above.</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-200">
                            @foreach($unifiedTransactions as $txn)
                                <a href="{{ $txn['url'] }}" class="block hover:bg-gray-50 transition">
                                    <div class="px-4 py-4 flex items-center justify-between">
                                        <div class="flex items-center">
                                            <span class="font-mono text-sm text-blue-600">{{ $txn['number'] }}</span>
                                            <span class="mx-2 text-gray-300">|</span>
                                            <span class="text-sm text-gray-900">{{ $txn['type'] }}</span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="text-xs text-gray-500">{{ $txn['created_at']->diffForHumans() }}</span>
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($txn['status'] === 'pending' || $txn['status'] === 'open') bg-yellow-100 text-yellow-800
                                                @elseif($txn['status'] === 'hr_approved' || $txn['status'] === 'in_progress') bg-blue-100 text-blue-800
                                                @elseif($txn['status'] === 'approved' || $txn['status'] === 'resolved') bg-green-100 text-green-800
                                                @elseif($txn['status'] === 'rejected' || $txn['status'] === 'cancelled') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ $txn['status_label'] }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
