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
                    Timekeeping & Concerns Hub
                </h2>
                <p class="text-sm text-gray-500 mt-1">Unified management for employee time logs and concerns/tickets</p>
            </div>
            
            <!-- Tab Navigation (Desktop) -->
            <div class="flex bg-gray-100 p-1 rounded-xl group border border-gray-200">
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'logs']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeTab === 'logs' ? 'bg-white text-blue-600 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700' }}">
                   Real-time Logs
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'tickets']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeTab === 'tickets' ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700' }}">
                   All Tickets
                   @if($stats['pending_complaints'] > 0)
                        <span class="ml-1 px-1.5 py-0.5 text-[10px] bg-red-500 text-white rounded-full">{{ $stats['pending_complaints'] }}</span>
                   @endif
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-2 sm:py-4 px-2 sm:px-4 lg:px-8">
        <div class="max-w-full mx-auto">
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
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'tickets', 'ticket_status' => 'open']) }}" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all group relative overflow-hidden ring-2 ring-red-50 block">
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
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                @if($activeTab === 'logs')
                    <!-- Quick Actions & Anomalies Widget (Replaces Manual Entry Form) -->
                    <div class="lg:col-span-3 space-y-6">
                        <!-- Quick Actions -->
                        <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl shadow-lg p-6 text-white overflow-hidden relative">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                            </div>
                            <h3 class="text-lg font-black uppercase tracking-wider mb-1 relative z-10">Timekeeping</h3>
                            <p class="text-indigo-100 text-sm mb-6 relative z-10">Manage employee logs and corrections.</p>
                            
                            <button onclick="openManualEntryModal()" class="w-full bg-white text-indigo-700 font-bold py-3 px-4 rounded-xl hover:bg-indigo-50 transition-all shadow-md flex items-center justify-center gap-2 relative z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Log Manual Entry
                            </button>
                        </div>

                        <!-- Anomalies Widget -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="bg-gray-50/50 px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-xs font-black text-gray-500 uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    Flagged Anomalies
                                </h3>
                                @if(isset($anomalies) && $anomalies->count() > 0)
                                    <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-700 text-[10px] font-bold">{{ $anomalies->count() }}</span>
                                @endif
                            </div>
                            
                            <div class="divide-y divide-gray-50 max-h-[400px] overflow-y-auto">
                                @forelse($anomalies ?? [] as $anomaly)
                                    <div class="p-4 hover:bg-amber-50/10 transition-colors">
                                        <div class="flex items-start gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-xs shrink-0">
                                                !
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between mb-1">
                                                    <h4 class="text-xs font-bold text-gray-800 truncate">{{ $anomaly->user->name }}</h4>
                                                    <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($anomaly->date)->format('M d') }}</span>
                                                </div>
                                                <p class="text-[10px] text-red-500 font-medium bg-red-50 inline-block px-1.5 py-0.5 rounded">
                                                    Missing Time Out
                                                </p>
                                                <div class="mt-2">
                                                     <button onclick="openManualEntryModal({{ $anomaly->user_id }})" 
                                                     class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-colors group">
                                                        <span>Resolve</span>
                                                        <svg class="w-3 h-3 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                                     </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-8 text-center">
                                        <div class="inline-flex p-3 bg-emerald-50 rounded-full mb-3">
                                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <p class="text-xs font-bold text-gray-400">All clean! No anomalies found.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Transactions List -->
                    <div class="lg:col-span-9">

                        <!-- TK Complaints Section -->
                        <div class="bg-white rounded-2xl shadow-sm border-2 {{ $tkComplaints->count() > 0 ? 'border-red-50' : 'border-gray-50' }} mb-8 overflow-hidden">
                            <div class="px-6 py-4 border-b {{ $tkComplaints->count() > 0 ? 'bg-red-50/50 border-red-100' : 'bg-gray-50/50 border-gray-100' }} flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-1.5 {{ $tkComplaints->count() > 0 ? 'bg-red-100' : 'bg-gray-100' }} rounded-lg transition-colors">
                                        <svg class="w-5 h-5 {{ $tkComplaints->count() > 0 ? 'text-red-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xs font-black {{ $tkComplaints->count() > 0 ? 'text-red-800' : 'text-gray-500' }} uppercase tracking-widest whitespace-nowrap">Active Timekeeping Complaints</h3>
                                </div>
                                <a href="{{ request()->fullUrlWithQuery(['tab' => 'tickets', 'ticket_category' => 'timekeeping']) }}" class="text-xs font-bold text-red-600 hover:text-red-800 transition-colors uppercase whitespace-nowrap">View All TK &rarr;</a>
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
                                        <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">No active TK complaints found.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 p-6">
                            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                                <input type="hidden" name="tab" value="logs">
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
                                            <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest min-w-[180px]">Employee</th>
                                            <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest min-w-[140px]">Time Recorded</th>
                                            <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest min-w-[120px]">Transaction</th>
                                            <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest min-w-[200px]">Notes</th>
                                            <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest min-w-[120px]">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white uppercase">
                                        @forelse($transactions as $transaction)
                                            <tr class="{{ $transaction->isVoided() ? 'bg-red-50/50' : 'hover:bg-gray-50/50' }} transition-colors group">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-gray-600 font-black text-xs group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                                                            {{ substr($transaction->user->name, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-bold text-gray-900">{{ $transaction->user->name }}</div>
                                                            <div class="text-[10px] font-bold text-gray-400 tracking-tighter">{{ $transaction->user->employee_id }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-bold text-gray-900">{{ $transaction->transaction_time->format('M d, Y') }}</div>
                                                    <div class="text-[10px] text-gray-400 font-bold">{{ $transaction->transaction_time->format('h:i A') }}</div>
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

                                                    @if(auth()->user()->isAdmin())
                                                        <form action="{{ route('timekeeping.destroy', $transaction) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to PERMANENTLY delete this log? This cannot be undone.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="inline-flex items-center px-3 py-1.5 bg-white text-red-600 hover:bg-red-600 hover:text-white rounded-lg text-xs font-bold transition-all border border-red-100 hover:border-red-600 shadow-sm">
                                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                                Delete
                                                            </button>
                                                        </form>
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
                @elseif($activeTab === 'tickets')
                    <!-- ALL TICKETS TAB (REDESIGNED) -->
                    <div class="lg:col-span-12">
                        <!-- Header & Filters -->
                        <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 overflow-hidden mb-6">
                            <div class="p-6 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                                <div>
                                    <h3 class="text-lg font-black text-indigo-900 leading-tight">Ticket Management</h3>
                                    <p class="text-sm text-gray-500 mt-1">Track, filter, and resolve employee concerns efficiently.</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('concerns.create') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 hover:shadow-indigo-200 gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        New Ticket
                                    </a>
                                </div>
                            </div>

                            <!-- Advanced Toolbar -->
                            <div class="bg-gray-50/50 p-4 border-b border-gray-100">
                                <form method="GET" class="flex flex-col lg:flex-row gap-4 items-start lg:items-center">
                                    <input type="hidden" name="tab" value="tickets">
                                    
                                    <!-- Search Input -->
                                    <div class="relative flex-1 w-full lg:max-w-md">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                        <input type="text" name="ticket_search" value="{{ request('ticket_search') }}" 
                                               class="pl-10 w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-shadow py-2.5" 
                                               placeholder="Search ticket #, employee, or subject...">
                                    </div>

                                    <!-- Filters Group -->
                                    <div class="flex flex-wrap items-center gap-3 flex-1 w-full">
                                        <!-- Status Filter -->
                                        <select name="ticket_status" onchange="this.form.submit()" class="rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm py-2.5 pl-3 pr-10">
                                            <option value="">All Statuses</option>
                                            @foreach($concernStatuses as $key => $label)
                                                <option value="{{ $key }}" {{ request('ticket_status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>

                                        <!-- Category Filter -->
                                        <select name="ticket_category" onchange="this.form.submit()" class="rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm py-2.5 pl-3 pr-10">
                                            <option value="">All Categories</option>
                                            @foreach($concernCategories as $key => $label)
                                                <option value="{{ $key }}" {{ request('ticket_category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>

                                        <!-- Priority Filter -->
                                        <select name="ticket_priority" onchange="this.form.submit()" class="rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm py-2.5 pl-3 pr-10">
                                            <option value="">All Priorities</option>
                                            @foreach($concernPriorities as $key => $label)
                                                <option value="{{ $key }}" {{ request('ticket_priority') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="hidden sm:inline-flex lg:hidden p-2.5 bg-indigo-600 text-white rounded-xl shadow-sm hover:bg-indigo-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </button>
                                        
                                        @if(request()->anyFilled(['ticket_search', 'ticket_status', 'ticket_category', 'ticket_priority']))
                                            <a href="{{ request()->url() }}?tab=tickets" class="text-sm font-bold text-red-500 hover:text-red-700 underline decoration-2 underline-offset-2 ml-auto lg:ml-0">Clear</a>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Results Table -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50/80">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[240px]">Employee / Ticket</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[280px]">Subject & Details</th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Priority</th>
                                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @forelse($concerns as $concern)
                                            <tr class="group hover:bg-indigo-50/30 transition-colors duration-150">
                                                <!-- Employee Column -->
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-md ring-2 ring-white">
                                                            {{ substr($concern->reporter->name, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-bold text-gray-900">{{ $concern->reporter->name }}</div>
                                                            <div class="flex items-center gap-2 mt-0.5">
                                                                <span class="text-[10px] font-mono font-bold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ $concern->reporter->employee_id }}</span>
                                                                <span class="text-[10px] font-bold text-indigo-400">#{{ $concern->ticket_number }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Subject Column -->
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-bold text-gray-800 group-hover:text-indigo-700 transition-colors line-clamp-1" title="{{ $concern->title }}">{{ $concern->title }}</span>
                                                        <div class="flex items-center gap-2">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200 uppercase tracking-tight">
                                                                {{ $concernCategories[$concern->category] ?? $concern->category }}
                                                            </span>
                                                            <span class="text-[10px] text-gray-400">{{ $concern->created_at->diffForHumans() }}</span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Status Column -->
                                                <td class="px-6 py-4 text-center">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold capitalize shadow-sm border {{ $concern->status_badge }}">
                                                        {{ $concern->status }}
                                                    </span>
                                                </td>

                                                <!-- Priority Column -->
                                                <td class="px-6 py-4 text-center">
                                                    @if($concern->priority == 'high' || $concern->priority == 'urgent' || $concern->priority == 'critical')
                                                        <div class="flex flex-col items-center">
                                                            <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">{{ $concern->priority }}</span>
                                                            <div class="h-1 w-8 bg-rose-200 rounded-full mt-1 overflow-hidden">
                                                                <div class="h-full bg-rose-500 w-full animate-pulse"></div>
                                                            </div>
                                                        </div>
                                                    @elseif($concern->priority == 'medium')
                                                        <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">{{ $concern->priority }}</span>
                                                    @else
                                                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">{{ $concern->priority }}</span>
                                                    @endif
                                                </td>

                                                <!-- Actions Column -->
                                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="{{ route('concerns.show', $concern) }}" class="p-2 text-gray-400 hover:text-indigo-600 bg-gray-50 hover:bg-indigo-50 rounded-lg transition-all border border-transparent hover:border-indigo-100" title="View Details">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-16 text-center">
                                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                                        <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                                                            <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                        </div>
                                                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-1">No Tickets Found</h3>
                                                        <p class="text-xs text-gray-500 leading-relaxed">Adjust your filters or search criteria. If you're looking for a specific ticket, try searching by ID.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($concerns->hasPages())
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                                    <div class="text-xs text-gray-500">
                                        Showing <span class="font-bold">{{ $concerns->firstItem() ?? 0 }}</span> to <span class="font-bold">{{ $concerns->lastItem() ?? 0 }}</span> of <span class="font-bold">{{ $concerns->total() }}</span> tickets
                                    </div>
                                    <div class="scale-90 origin-right">
                                        {{ $concerns->links() }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modern Void Modal -->
    <div id="void-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeVoidModal()">
        <div class="relative mx-auto w-full max-w-md bg-white rounded-2xl shadow-2xl animate-pop-in" onclick="event.stopPropagation()">
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
    <div id="edit-log-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeEditLogModal()">
        <div class="relative mx-auto w-full max-w-md bg-white rounded-[2rem] shadow-2xl p-8 animate-pop-in pointer-events-auto border border-gray-100" onclick="event.stopPropagation()">
            <div class="flex items-start gap-4 mb-6">
                <div class="p-3 bg-emerald-50 rounded-2xl">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div>
                    <h3 id="edit-log-title" class="text-lg font-black text-gray-900">Edit Log Entry</h3>
                    <p id="edit-log-employee" class="text-xs font-bold text-gray-400 uppercase tracking-widest"></p>
                </div>
            </div>

            <form id="edit-log-form" method="POST">
                @csrf
                <input type="hidden" name="_method" id="edit-log-method" value="PATCH">
                <input type="hidden" name="user_id" id="edit-log-user-id" disabled>
                
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

    <div id="manual-entry-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/60 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md p-8 transform scale-95 opacity-0 transition-all duration-300 animate-pop-in">
            <div class="flex items-start gap-4 mb-6">
                <div class="p-3 bg-blue-50 rounded-2xl">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900">Manual Entry</h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Create a new time log</p>
                </div>
            </div>

            <form action="{{ route('timekeeping.admin-store') }}" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Employee</label>
                        <select name="user_id" required id="manual-entry-user"
                                class="w-full text-sm font-bold border-gray-100 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-gray-50/50">
                            <option value="">Select Employee...</option>
                            @foreach($employees as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Transaction Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="transaction_type" value="time_in" class="peer sr-only" required>
                                <div class="p-3 text-center border-2 border-gray-100 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all">
                                    <span class="text-xs font-bold text-gray-500 peer-checked:text-blue-700">Time In</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="transaction_type" value="time_out" class="peer sr-only" required>
                                <div class="p-3 text-center border-2 border-gray-100 rounded-xl peer-checked:border-amber-500 peer-checked:bg-amber-50 transition-all">
                                    <span class="text-xs font-bold text-gray-500 peer-checked:text-amber-700">Time Out</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Timestamp</label>
                        <input type="datetime-local" name="transaction_time" required
                               class="w-full text-sm font-bold border-gray-100 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Reason / Notes</label>
                        <textarea name="notes" rows="2" required placeholder="Why is this being logged manually?"
                                  class="w-full text-sm border-gray-100 rounded-xl focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeManualEntryModal()" 
                            class="flex-1 px-4 py-3 bg-gray-50 text-gray-600 font-bold rounded-2xl hover:bg-gray-100 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">
                        Create Log
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openManualEntryModal(userId = null) {
            const modal = document.getElementById('manual-entry-modal');
            const userSelect = document.getElementById('manual-entry-user');
            
            if (userId && userSelect) {
                userSelect.value = userId;
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            // Reset form if needed, though native form reset might be better on close.
            // document.querySelector('#manual-entry-modal form').reset();
        }

        function closeManualEntryModal() {
            const modal = document.getElementById('manual-entry-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        function openEditLogModal(data) {
            document.getElementById('edit-log-form').action = `/timekeeping/${data.id}/update`;
            document.getElementById('edit-log-employee').innerText = data.employee;
            document.getElementById('edit-log-time').value = data.time;
            document.getElementById('edit-log-type').value = data.type;
            document.getElementById('edit-log-notes').value = data.notes || '';
            
            const modal = document.getElementById('edit-log-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeEditLogModal() {
            const modal = document.getElementById('edit-log-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        function openVoidModal(transactionId) {
            document.getElementById('void-form').action = `/timekeeping/${transactionId}/void`;
            const modal = document.getElementById('void-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeVoidModal() {
            const modal = document.getElementById('void-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
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
