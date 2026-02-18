<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    Timekeeping Management
                </h2>
                <p class="text-sm text-gray-500 mt-1">Monitor real-time employee attendance and manage daily logs</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r-xl shadow-sm flex items-center gap-3 animate-fade-in-down">
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span class="font-bold text-sm">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <!-- Transactions Today -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all group relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-[0.05] group-hover:scale-110 transition-transform">
                        <svg class="w-16 h-16 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">New Logs Today</p>
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-black text-blue-600">{{ $stats['total_today'] }}</span>
                        <span class="text-xs font-bold text-emerald-500 mb-1 flex items-center">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            Tracked
                        </span>
                    </div>
                </div>

                <!-- Active Employees -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all group relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-[0.05] group-hover:scale-110 transition-transform">
                        <svg class="w-16 h-16 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Active Now</p>
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-black text-emerald-600">{{ $stats['active_employees'] }}</span>
                        <span class="text-xs font-bold text-gray-400 mb-1">Employees</span>
                    </div>
                </div>

                <!-- On Break -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all group relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-[0.05] group-hover:scale-110 transition-transform">
                        <svg class="w-16 h-16 text-amber-600" fill="currentColor" viewBox="0 0 24 24"><path d="M20 3H4v10c0 2.21 1.79 4 4 4h6c2.21 0 4-1.79 4-4v-3h2c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm0 5h-2V5h2v3zM4 19h16v2H4z"/></svg>
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">On Break</p>
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-black text-amber-600">{{ $stats['on_break'] }}</span>
                        <div class="flex flex-col">
                            <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- In Meeting -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all group relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-[0.05] group-hover:scale-110 transition-transform">
                        <svg class="w-16 h-16 text-indigo-600" fill="currentColor" viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">In Meeting</p>
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-black text-indigo-600">{{ $stats['in_meeting'] }}</span>
                        <span class="text-xs font-bold text-gray-400 mb-1">Sessions</span>
                    </div>
                </div>

                <!-- TK Complaints -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all group relative overflow-hidden ring-2 ring-red-50">
                    <div class="absolute top-0 right-0 p-4 opacity-[0.05] group-hover:scale-110 transition-transform">
                        <svg class="w-16 h-16 text-red-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Pending Complaints</p>
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-black {{ $stats['pending_complaints'] > 0 ? 'text-red-600' : 'text-gray-300' }}">{{ $stats['pending_complaints'] }}</span>
                        <div class="flex flex-col mb-1">
                            <span class="text-[10px] font-bold text-gray-400 tracking-wider">TICKETS</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Add Transaction Form -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                        <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Manual Entry</h3>
                        </div>
                        <div class="p-6">
                            <form action="{{ route('timekeeping.admin-store') }}" method="POST" class="space-y-5">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Employee</label>
                                    <select name="user_id" required class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-all">
                                        <option value="">Select Employee</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Type</label>
                                    <select name="transaction_type" required class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-all">
                                        @foreach($transactionTypes as $category => $types)
                                            <optgroup label="{{ $category }}" class="font-bold text-gray-400">
                                                @foreach($types as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Time</label>
                                    <input type="datetime-local" name="transaction_time" required 
                                           value="{{ now()->format('Y-m-d\TH:i') }}"
                                           class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Internal Notes</label>
                                    <textarea name="notes" rows="3" placeholder="Optional context..." class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"></textarea>
                                </div>
                                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-blue-700 transition-all shadow-md shadow-blue-100 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    Log Transaction
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Transactions List -->
                <div class="lg:col-span-9">

                    <!-- TK Complaints Section -->
                    <div class="bg-white rounded-2xl shadow-sm border-2 {{ $tkComplaints->count() > 0 ? 'border-red-50' : 'border-gray-50' }} mb-8 overflow-hidden">
                        <div class="px-6 py-4 border-b {{ $tkComplaints->count() > 0 ? 'bg-red-50/50 border-red-100' : 'bg-gray-50/50 border-gray-100' }} flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-1.5 {{ $tkComplaints->count() > 0 ? 'bg-red-100' : 'bg-gray-100' }} rounded-lg transition-colors">
                                    <svg class="w-5 h-5 {{ $tkComplaints->count() > 0 ? 'text-red-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-black {{ $tkComplaints->count() > 0 ? 'text-red-800' : 'text-gray-500' }} uppercase tracking-wider">Active Timekeeping Complaints</h3>
                            </div>
                            @if($tkComplaints->count() > 0)
                                <a href="{{ route('concerns.index', ['category' => 'timekeeping']) }}" class="text-xs font-bold text-red-600 hover:text-red-800 transition-colors uppercase">View All Tickets &rarr;</a>
                            @endif
                        </div>
                        <div class="divide-y divide-red-50">
                            @forelse($tkComplaints as $complaint)
                                <div class="p-6 flex items-start justify-between gap-4 hover:bg-red-50/20 transition-colors">
                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-700 font-bold shrink-0 shadow-sm">
                                            {{ substr($complaint->reporter->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-sm font-black text-gray-900 capitalize">{{ $complaint->reporter->name }}</span>
                                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md {{ $complaint->status == 'open' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ $complaint->status }}
                                                </span>
                                            </div>
                                            <h4 class="text-sm font-bold text-gray-800 mb-1">{{ $complaint->title }}</h4>
                                            <p class="text-xs text-gray-500 line-clamp-2 max-w-2xl leading-relaxed">{{ $complaint->description }}</p>
                                            <div class="mt-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $complaint->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2 shrink-0">
                                        <a href="{{ route('concerns.show', $complaint) }}" class="px-4 py-2 bg-white text-gray-700 font-bold text-xs rounded-xl border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm text-center">
                                            View Details
                                        </a>
                                        @if($complaint->isOpen())
                                            <form action="{{ route('concerns.status', $complaint) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="resolved">
                                                <input type="hidden" name="resolution_notes" value="Resolved via Timekeeping Management Hub.">
                                                <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl hover:bg-emerald-700 transition-all shadow-md shadow-emerald-100 border border-emerald-500">
                                                    Approve & Resolve
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="px-6 py-10 text-center">
                                    <div class="p-3 bg-gray-50 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">No active JK complaints found.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 p-6">
                        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                            <div class="lg:col-span-1">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Search</label>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       placeholder="Name or ID..."
                                       class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
                                <input type="date" name="date" value="{{ request('date', today()->toDateString()) }}" 
                                       class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                                <select name="category" class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-all">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                                <select name="status" class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-all">
                                    <option value="">Any Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-gray-100 text-gray-700 font-bold py-2.5 px-4 rounded-xl hover:bg-gray-200 transition-all flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                    Filter
                                </button>
                                <a href="{{ route('timekeeping.admin-index') }}" class="p-2.5 bg-gray-50 text-gray-400 rounded-xl hover:text-red-500 transition-colors" title="Clear Filters">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-50/50">
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Employee</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Time Recorded</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Transaction</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Notes</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($transactions as $transaction)
                                        <tr class="{{ $transaction->isVoided() ? 'bg-red-50/50' : 'hover:bg-gray-50/50' }} transition-colors group">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-gray-600 font-black text-xs group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                                                        {{ substr($transaction->user->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-bold text-gray-900">{{ $transaction->user->name }}</div>
                                                        <div class="text-[10px] font-bold text-gray-400 tracking-tighter uppercase">{{ $transaction->user->employee_id }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-bold text-gray-900">{{ $transaction->transaction_time->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ $transaction->transaction_time->format('h:i A') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg border {{ $transaction->color_badge }}">
                                                    {{ $transaction->label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-600 line-clamp-1 max-w-[200px]" title="{{ $transaction->notes }}">
                                                    {{ $transaction->notes ?? '-' }}
                                                </div>
                                                @if($transaction->isVoided())
                                                    <div class="text-[10px] font-bold text-red-500 uppercase mt-1">Void Reason: {{ $transaction->void_reason }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-1">
                                                @if($transaction->isVoided())
                                                    <span class="text-xs font-bold text-red-400 italic">Transaction Voided</span>
                                                @else
                                                    <button type="button" 
                                                            onclick="openEditLogModal({{ json_encode([
                                                                'id' => $transaction->id,
                                                                'employee' => $transaction->user->name,
                                                                'time' => $transaction->transaction_time->format('Y-m-d\TH:i'),
                                                                'type' => $transaction->transaction_type,
                                                                'notes' => $transaction->notes
                                                            ]) }})"
                                                            class="inline-flex items-center px-3 py-1.5 bg-white text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-lg text-xs font-bold transition-all border border-emerald-100 hover:border-emerald-600 shadow-sm">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                        Edit
                                                    </button>
                                                    
                                                    <button type="button" onclick="openVoidModal({{ $transaction->id }})" 
                                                            class="inline-flex items-center px-3 py-1.5 bg-white text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg text-xs font-bold transition-all border border-rose-100 hover:border-rose-600 shadow-sm">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                        Void
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-16 text-center">
                                                <div class="flex flex-col items-center justify-center">
                                                    <div class="p-4 bg-gray-50 rounded-full mb-4">
                                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    </div>
                                                    <p class="text-gray-400 font-bold">No transactions logged for this period.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($transactions->hasPages())
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                                {{ $transactions->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Void Modal -->
    <div id="void-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative mx-auto w-full max-w-md bg-white rounded-2xl shadow-2xl animate-pop-in">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-red-100 rounded-xl">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Void Transaction</h3>
                        <p class="text-sm text-gray-500">This action cannot be undone once confirmed.</p>
                    </div>
                </div>

                <form id="void-form" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Reason for Voiding</label>
                        <textarea name="void_reason" rows="4" required
                                  class="w-full text-sm border-gray-200 rounded-xl focus:ring-red-500 focus:border-red-500 transition-all resize-none"
                                  placeholder="Provide a mandatory reason for auditing..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeVoidModal()" 
                                class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-4 py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 shadow-md shadow-red-100 transition-all">
                            Confirm Void
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Log Modal -->
    <div id="edit-log-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-8 pointer-events-none">
            <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-sm pointer-events-auto"></div>
            <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md p-8 relative pointer-events-auto transform transition-all border border-gray-100">
                <div class="flex items-start gap-4 mb-6">
                    <div class="p-3 bg-emerald-50 rounded-2xl">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Edit Log Entry</h3>
                        <p id="edit-log-employee" class="text-xs font-bold text-gray-400 uppercase tracking-widest"></p>
                    </div>
                </div>

                <form id="edit-log-form" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Transaction Type</label>
                            <select name="transaction_type" id="edit-log-type" required
                                    class="w-full text-sm font-bold border-gray-100 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
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
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Timestamp</label>
                            <input type="datetime-local" name="transaction_time" id="edit-log-time" required
                                   class="w-full text-sm font-bold border-gray-100 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Internal Note</label>
                            <textarea name="notes" id="edit-log-notes" rows="2"
                                      class="w-full text-sm border-gray-100 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 resize-none"
                                      placeholder="Reason for change..."></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="closeEditLogModal()" 
                                class="flex-1 px-4 py-3 bg-gray-50 text-gray-600 font-bold rounded-2xl hover:bg-gray-100 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-4 py-3 bg-emerald-600 text-white font-bold rounded-2xl hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openEditLogModal(data) {
            document.getElementById('edit-log-form').action = `/timekeeping/${data.id}/update`;
            document.getElementById('edit-log-employee').innerText = data.employee;
            document.getElementById('edit-log-time').value = data.time;
            document.getElementById('edit-log-type').value = data.type;
            document.getElementById('edit-log-notes').value = data.notes || '';
            
            document.getElementById('edit-log-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeEditLogModal() {
            document.getElementById('edit-log-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openVoidModal(transactionId) {
            document.getElementById('void-form').action = `/timekeeping/${transactionId}/void`;
            document.getElementById('void-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeVoidModal() {
            document.getElementById('void-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close on backdrop click (outside click)
        window.addEventListener('mousedown', function(e) {
            const editModal = document.getElementById('edit-log-modal');
            const voidModal = document.getElementById('void-modal');
            
            // Only close if the click was exactly on the modal wrapper OR its pointer-events-auto backdrop
            if (e.target.id === 'edit-log-modal') closeEditLogModal();
            if (e.target.id === 'void-modal') closeVoidModal();
            
            // Support for the common 'backdrop' div inside
            if (e.target.classList.contains('bg-gray-900/60')) {
                if (!editModal.classList.contains('hidden')) closeEditLogModal();
                if (!voidModal.classList.contains('hidden')) closeVoidModal();
            }
        });
    </script>
    <style>
        @keyframes pop-in {
            0% { opacity: 0; transform: scale(0.95) translateY(10px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        .animate-pop-in {
            animation: pop-in 0.2s ease-out forwards;
        }
    </style>
    @endpush
</x-app-layout>
