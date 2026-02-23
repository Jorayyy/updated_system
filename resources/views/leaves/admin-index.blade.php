<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leave Management Hub') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 focus:outline-none">
            
            <!-- Unified Hub Header & Pill Switcher (Matching Image 1 & 2) -->
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Leave Management Hub</h1>
                    <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mt-1">Centralized Administration</p>
                </div>

                <!-- PILL STYLE TAB SWITCHER -->
                <div class="inline-flex p-1.5 bg-slate-100 rounded-full border border-slate-200/50">
                    <a href="{{ route('leaves.manage', ['tab' => 'requests']) }}" 
                       class="px-8 py-2.5 rounded-full text-[11px] font-black uppercase tracking-widest transition-all duration-300 {{ $activeTab === 'requests' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/50' : 'text-slate-600 hover:text-slate-800' }}">
                        Requests
                    </a>
                    
                    @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('leaves.manage', ['tab' => 'credits', 'year' => request('year', date('Y'))]) }}" 
                       class="px-8 py-2.5 rounded-full text-[11px] font-black uppercase tracking-widest transition-all duration-300 {{ $activeTab === 'credits' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/50' : 'text-slate-600 hover:text-slate-800' }}">
                        Credits
                    </a>
                    @endif

                    <a href="{{ route('leaves.manage', ['tab' => 'types']) }}" 
                       class="px-8 py-2.5 rounded-full text-[11px] font-black uppercase tracking-widest transition-all duration-300 {{ $activeTab === 'types' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/50' : 'text-slate-600 hover:text-slate-800' }}">
                        Policies
                    </a>
                </div>
            </div>

            <!-- Tab-Specific Content (High Fidelity 3/12 Layout) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- SIDEBAR (3/12) -->
                <div class="lg:col-span-3 space-y-6">
                    @if($activeTab === 'requests')
                        <!-- Quick Actions Box -->
                        <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-[2.5rem] shadow-xl p-8 text-white overflow-hidden relative group">
                            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all"></div>
                            <h3 class="text-lg font-black uppercase tracking-wider mb-1 relative z-10">Leave Control</h3>
                            <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mb-8 relative z-10 opacity-70">Entry & Policy Access</p>
                            
                            <a href="{{ route('leaves.create') }}" class="w-full bg-white text-indigo-700 font-extrabold py-4 px-4 rounded-full hover:bg-slate-50 transition-all shadow-lg flex items-center justify-center gap-2 relative z-10 uppercase text-[10px] tracking-[0.2em]">
                                Create Request
                            </a>
                        </div>

                        <!-- Policy Legend Widget -->
                        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                            <div class="bg-slate-50/50 px-6 py-4 border-b border-slate-100">
                                <h3 class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">Active Policies</h3>
                            </div>
                            <div class="px-6 py-4 space-y-4">
                                @foreach($leaveTypes->take(5) as $type)
                                    <div class="flex items-center justify-between group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-1.5 h-1.5 rounded-full shadow-sm" style="background-color: {{ $type->color }}"></div>
                                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ $type->name }}</span>
                                        </div>
                                        <span class="text-[9px] font-mono font-black text-slate-300 uppercase">{{ $type->code }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('leaves.manage', ['tab' => 'types']) }}" class="block px-6 py-3 bg-slate-50 text-[9px] font-black text-slate-600 uppercase tracking-widest text-center hover:text-indigo-600 transition-colors">
                                View All Policies &rarr;
                            </a>
                        </div>
                    @elseif($activeTab === 'credits')
                        <!-- Balance Management Box -->
                        <div class="bg-emerald-600 rounded-[2.5rem] shadow-xl p-8 text-white overflow-hidden relative group border border-emerald-500/20">
                            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all"></div>
                            <h3 class="text-lg font-black uppercase tracking-[0.1em] mb-1 relative z-10 text-white">Balance Master</h3>
                            <p class="text-emerald-50 text-[10px] font-black uppercase tracking-widest mb-8 relative z-10 opacity-80">Yearly Reset & Migration</p>
                            
                            <div class="space-y-3 relative z-10">
                                <button type="button" onclick="document.getElementById('bulkAllocateModal').classList.remove('hidden')"
                                    class="w-full bg-white text-emerald-700 font-black py-4 px-4 rounded-full hover:bg-emerald-50 hover:scale-[1.02] active:scale-95 transition-all shadow-lg flex items-center justify-center gap-3 uppercase text-[10px] tracking-[0.2em]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Bulk Allocate
                                </button>
                                <button type="button" onclick="document.getElementById('carryOverModal').classList.remove('hidden')"
                                    class="w-full bg-emerald-500/20 text-white border border-white/30 font-black py-4 px-4 rounded-full hover:bg-emerald-500/40 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-3 uppercase text-[10px] tracking-[0.2em] backdrop-blur-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    Carry Over
                                </button>
                            </div>
                        </div>

                        <!-- Credits Stats Widget -->
                        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                            <div class="bg-slate-50/50 px-6 py-4 border-b border-slate-100 text-center">
                                <h3 class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">Staff Coverage</h3>
                            </div>
                            <div class="p-8 text-center space-y-6">
                                <div>
                                    <div class="text-4xl font-black text-slate-800 leading-none mb-1">{{ $employeesWithCredits }}</div>
                                    <div class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Setup Complete</div>
                                </div>
                                <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-emerald-500 h-full rounded-full shadow-[0_0_8px_rgba(16,185,129,0.3)]" style="width: {{ $totalEmployees > 0 ? ($employeesWithCredits / $totalEmployees) * 100 : 0 }}%"></div>
                                </div>
                                @if($employeesWithoutCredits > 0)
                                    <div class="flex items-center justify-center gap-2 px-4 py-2 bg-rose-50 rounded-full cursor-help" title="These employees have no balances for the selected year">
                                        <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
                                        <span class="text-[9px] font-black text-rose-600 uppercase tracking-tight">{{ $employeesWithoutCredits }} MISSING BALANCES</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @elseif($activeTab === 'types')
                        <!-- Policy Engine Box -->
                        <div class="bg-slate-900 rounded-[2.5rem] shadow-xl p-8 text-white overflow-hidden relative group border border-slate-800">
                            <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/20 transition-all"></div>
                            <h3 class="text-lg font-black uppercase tracking-[0.1em] mb-1 relative z-10 text-white">Policy Engine</h3>
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-8 relative z-10 opacity-80">Category Definitions</p>
                            
                            <a href="{{ route('leave-types.create') }}" class="w-full bg-indigo-600 text-white font-black py-4 px-4 rounded-full hover:bg-white hover:text-indigo-600 hover:scale-[1.02] active:scale-95 transition-all shadow-lg flex items-center justify-center gap-3 relative z-10 uppercase text-[10px] tracking-[0.2em]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add Category
                            </a>
                        </div>

                        <!-- System Specs Info -->
                        <div class="bg-slate-100/50 rounded-[2.5rem] p-6 border border-slate-200/50">
                            <div class="text-[10px] font-black text-slate-600 uppercase tracking-widest mb-4">Leave Architecture</div>
                            <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-5 h-5 rounded-lg bg-white flex items-center justify-center text-[10px] shadow-sm font-black border border-slate-100">1</div>
                                    <p class="text-[10px] text-slate-600 font-bold uppercase leading-relaxed tracking-tight">Policies define the global rules for each category.</p>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="w-5 h-5 rounded-lg bg-white flex items-center justify-center text-[10px] shadow-sm font-black border border-slate-100">2</div>
                                    <p class="text-[10px] text-slate-600 font-bold uppercase leading-relaxed tracking-tight">Credits transfer rules to individual users yearly.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Common System Info -->
                    <div class="bg-white rounded-[2.5rem] p-6 border border-slate-100 shadow-sm relative overflow-hidden group">
                        <div class="text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 flex justify-between">
                            <span>Last Updated</span>
                            <span class="text-indigo-600">{{ now()->format('H:i') }}</span>
                        </div>
                        <p class="text-[11px] text-slate-600 font-black uppercase leading-tight tracking-tight">
                            System is in live synchronization Mode.
                        </p>
                    </div>
                </div>

                <!-- MAIN CONTENT (9/12) -->
                <div class="lg:col-span-9 space-y-6">
                    @if($activeTab === 'requests')
                        <!-- Unified Command Bar (Filters) -->
                        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-4 overflow-x-auto">
                            <form method="GET" class="flex flex-wrap items-center gap-3 min-w-max">
                                <input type="hidden" name="tab" value="requests">
                                
                                <div class="w-64 relative">
                                    <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <input type="text" name="search" value="{{ request('search') }}" 
                                        placeholder="SEARCH EMPLOYEE..."
                                        class="w-full h-11 bg-slate-50 border-slate-100 rounded-full text-[11px] font-black uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-slate-100 focus:border-slate-200 transition-all placeholder:text-slate-400 pl-12 pr-6 shadow-sm">
                                </div>

                                <select name="status" class="h-11 bg-slate-50 border-slate-100 rounded-full text-[10px] font-black uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-slate-100 focus:border-slate-200 pr-10 pl-6 transition-all shadow-sm">
                                    <option value="">ALL STATUS</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>PENDING</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>APPROVED</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>REJECTED</option>
                                </select>

                                <select name="leave_type" class="h-11 bg-slate-50 border-slate-100 rounded-full text-[10px] font-black uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-slate-100 focus:border-slate-200 pr-10 pl-6 transition-all shadow-sm">
                                    <option value="">ALL TYPES</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('leave_type') == $type->id ? 'selected' : '' }}>{{ $type->code }}</option>
                                    @endforeach
                                </select>

                                <select name="department" class="h-11 bg-slate-50 border-slate-100 rounded-full text-[10px] font-black uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-slate-100 focus:border-slate-200 pr-10 pl-6 transition-all shadow-sm">
                                    <option value="">ALL DEPTS</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ strtoupper($dept) }}</option>
                                    @endforeach
                                </select>

                                <button type="submit" class="h-11 px-8 bg-slate-900 text-white rounded-full text-[11px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-xl shadow-slate-200 active:scale-95 flex items-center gap-3">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                    FILTER
                                </button>
                                <a href="{{ route('leaves.manage', ['tab' => 'requests']) }}" class="h-11 w-11 flex items-center justify-center bg-white text-slate-400 rounded-full border border-slate-100 shadow-sm hover:text-rose-500 hover:border-rose-100 transition-all group active:scale-95" title="Reset Filters">
                                    <svg class="w-4 h-4 transition-transform group-hover:rotate-180 duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </a>
                            </form>
                        </div>

                        <!-- Requests Table -->
                        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden min-h-[500px]">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-200 bg-slate-100/50">
                                            <th class="px-8 py-6 text-[10px] font-black text-slate-600 uppercase tracking-widest">Employee</th>
                                            <th class="px-8 py-6 text-[10px] font-black text-slate-600 uppercase tracking-widest">Type</th>
                                            <th class="px-8 py-6 text-[10px] font-black text-slate-600 uppercase tracking-widest text-center">Days</th>
                                            <th class="px-8 py-6 text-[10px] font-black text-slate-600 uppercase tracking-widest text-center">Status</th>
                                            <th class="px-8 py-6 text-right text-[10px] font-black text-slate-600 uppercase tracking-widest">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @forelse($leaveRequests as $leave)
                                            <tr class="hover:bg-slate-50/50 transition-all duration-200 group font-black uppercase">
                                                <td class="px-8 py-5">
                                                    <div class="text-[12px] text-slate-800 tracking-tight">{{ $leave->user->name }}</div>
                                                    <div class="text-[9px] text-slate-600 font-bold tracking-tighter">{{ $leave->user->employee_id }}</div>
                                                </td>
                                                <td class="px-8 py-5">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] tracking-tighter" style="background-color: {{ $leave->leaveType->color ?? '#e5e7eb' }}15; color: {{ $leave->leaveType->color ?? '#374151' }};">
                                                        {{ $leave->leaveType->name }}
                                                    </span>
                                                </td>
                                                <td class="px-8 py-5 text-center text-[12px] text-slate-800">{{ $leave->total_days }}</td>
                                                <td class="px-8 py-5 text-center">
                                                    <span class="px-3 py-1 text-[10px] tracking-widest rounded-full border {{ $leave->status == 'approved' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : ($leave->status == 'rejected' ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-amber-50 text-amber-600 border-amber-100') }}">
                                                        {{ $leave->status }}
                                                    </span>
                                                </td>
                                                <td class="px-8 py-5 text-right flex justify-end gap-2">
                                                    @if(strtolower($leave->status) === 'pending')
                                                        <button type="button" 
                                                            onclick="confirmApprove('{{ $leave->is_transaction ? route('transactions.hr-approve', $leave) : route('leaves.hr-approve', $leave) }}')"
                                                            class="h-9 w-9 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-2xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm"
                                                            title="Quick Approve">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        </button>
                                                        <button type="button" 
                                                            onclick="confirmReject('{{ $leave->is_transaction ? route('transactions.reject', $leave) : route('leaves.reject', $leave) }}')"
                                                            class="h-9 w-9 flex items-center justify-center bg-rose-50 text-rose-600 rounded-2xl hover:bg-rose-600 hover:text-white transition-all shadow-sm"
                                                            title="Quick Reject">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    @endif
                                                    <a href="{{ $leave->is_transaction ? route('transactions.show', $leave) : route('leaves.admin-show', $leave) }}" 
                                                       class="h-9 w-9 flex items-center justify-center bg-slate-50 text-slate-600 rounded-2xl hover:bg-slate-900 hover:text-white transition-all shadow-sm"
                                                       title="View Submission">
                                                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-8 py-24 text-center">
                                                    <div class="flex flex-col items-center justify-center opacity-40">
                                                        <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                        <span class="text-[10px] text-slate-600 font-black tracking-widest uppercase">No pending requests found</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-4">{{ $leaveRequests->links() }}</div>

                    @elseif($activeTab === 'credits')
                        <!-- Credits Control Bar -->
                        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-4 overflow-x-auto">
                            <form method="GET" class="flex flex-wrap items-center gap-3 min-w-max">
                                <input type="hidden" name="tab" value="credits">
                                <select name="year" class="h-11 bg-slate-50 border-slate-100 rounded-full text-[11px] font-black uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-slate-100 pr-10 pl-6 transition-all shadow-sm">
                                    @for($y = date('Y') + 1; $y >= date('Y') - 3; $y--) <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>CYCLE {{ $y }}</option> @endfor
                                </select>
                                
                                <select name="department" class="h-11 bg-slate-50 border-slate-100 rounded-full text-[10px] font-black uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-slate-100 focus:border-slate-200 pr-10 pl-6 transition-all shadow-sm">
                                    <option value="">ALL DEPARTMENTS</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ strtoupper($dept) }}</option>
                                    @endforeach
                                </select>

                                <div class="w-64 relative">
                                    <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="SEARCH STAFF..." class="w-full h-11 bg-slate-50 border-slate-100 rounded-full text-[11px] font-black uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-slate-100 transition-all placeholder:text-slate-400 pl-12 pr-6 shadow-sm">
                                </div>
                                <button type="submit" class="h-11 px-8 bg-slate-900 text-white rounded-full text-[11px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-xl shadow-slate-200 active:scale-95 flex items-center gap-3">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                    FILTER
                                </button>
                            </form>
                        </div>

                        <!-- Credits Table -->
                        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden min-h-[500px]">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-200 bg-slate-100/50">
                                            <th class="px-8 py-6 text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">Employee</th>
                                            @foreach($allCreditsLeaveTypes as $type)
                                                <th class="px-4 py-6 text-center text-[10px] font-black text-slate-600 uppercase tracking-widest">{{ $type->code }}</th>
                                            @endforeach
                                            <th class="px-8 py-6 text-right text-[10px] font-black text-slate-600 uppercase tracking-widest">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @forelse($employees as $employee)
                                            <tr class="hover:bg-slate-50/50 transition-all duration-200 group font-black uppercase">
                                                <td class="px-8 py-5">
                                                    <div class="text-[12px] text-slate-800 tracking-tight">{{ $employee->name }}</div>
                                                    <div class="text-[9px] text-slate-600 font-bold tracking-tighter">{{ $employee->employee_id }}</div>
                                                </td>
                                                @foreach($allCreditsLeaveTypes as $type)
                                                    @php $balance = $employee->leaveBalances->where('leave_type_id', $type->id)->first(); @endphp
                                                    <td class="px-4 py-5 text-center">
                                                        @if($balance)
                                                            <span class="text-[12px] {{ $balance->remaining_days > 0 ? 'text-indigo-600' : 'text-slate-300' }} font-mono">
                                                                {{ number_format($balance->remaining_days, 1) }}
                                                            </span>
                                                        @else
                                                            <span class="text-slate-200 text-[10px]">—</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="px-8 py-5 text-right flex justify-end gap-2">
                                                    <button type="button" onclick="openAdjustModal({{ $employee->id }}, '{{ $employee->name }}')" class="h-9 w-9 flex items-center justify-center bg-slate-50 text-slate-600 rounded-2xl hover:bg-amber-500 hover:text-white transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg></button>
                                                    <a href="{{ route('leave-credits.edit', ['employee' => $employee->id, 'year' => $year]) }}" class="h-9 w-9 flex items-center justify-center bg-slate-50 text-slate-600 rounded-2xl hover:bg-slate-900 hover:text-white transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ 2 + count($allCreditsLeaveTypes) }}" class="px-8 py-24 text-center">
                                                    <div class="flex flex-col items-center justify-center opacity-40">
                                                        <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                        <span class="text-[10px] text-slate-600 font-black tracking-widest uppercase">No staff records for {{ $year }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-4">{{ $employees->links() }}</div>

                    @elseif($activeTab === 'types')
                        <div class="bg-indigo-50/50 p-8 rounded-[2.5rem] border border-indigo-100 flex items-center justify-between shadow-sm">
                            <div>
                                <h3 class="text-sm font-black text-indigo-900 uppercase tracking-[0.2em] mb-1">Entitlement Architecture</h3>
                                <p class="text-[10px] text-indigo-600/70 font-black uppercase tracking-widest">Global policy definitions for the current fiscal cycle.</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden min-h-[500px]">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-200 bg-slate-100/50">
                                            <th class="px-8 py-6 text-[10px] font-black text-slate-600 uppercase tracking-widest">Policy Name</th>
                                            <th class="px-4 py-6 text-center text-[10px] font-black text-slate-600 uppercase tracking-widest">Code</th>
                                            <th class="px-4 py-6 text-center text-[10px] font-black text-slate-600 uppercase tracking-widest">Max Days</th>
                                            <th class="px-4 py-6 text-center text-[10px] font-black text-slate-600 uppercase tracking-widest">Status</th>
                                            <th class="px-8 py-6 text-right text-[10px] font-black text-slate-600 uppercase tracking-widest">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach($types as $type)
                                            <tr class="hover:bg-slate-50/50 transition-all duration-200 group font-black uppercase">
                                                <td class="px-8 py-5">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-2 h-2 rounded-full shadow-sm" style="background-color: {{ $type->color }}"></div>
                                                        <div class="text-[12px] text-slate-800 tracking-tight">{{ $type->name }}</div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-5 text-center text-[10px] font-mono text-slate-600">{{ $type->code }}</td>
                                                <td class="px-4 py-5 text-center text-[11px] text-slate-600">{{ $type->max_days }} DAYS</td>
                                                <td class="px-4 py-5 text-center">
                                                    <span class="px-3 py-1 text-[9px] tracking-widest rounded-full border {{ $type->is_active ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-rose-50 text-rose-600 border-rose-100' }}">
                                                        {{ $type->is_active ? 'ACTIVE' : 'INACTIVE' }}
                                                    </span>
                                                </td>
                                                <td class="px-8 py-5 text-right flex justify-end gap-2">
                                                    <a href="{{ route('leave-types.edit', $type) }}" class="h-9 w-9 flex items-center justify-center bg-slate-50 text-slate-600 rounded-2xl hover:bg-slate-900 hover:text-white transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-4">{{ $types->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($activeTab === 'credits' && auth()->user()->isSuperAdmin())
    <!-- Credits Modals -->
    <!-- Bulk Allocate Modal -->
    <div id="bulkAllocateModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-md overflow-y-auto h-full w-full hidden z-50 transition-all duration-300">
        <div class="relative top-20 mx-auto p-10 border-0 w-[450px] shadow-[0_20px_50px_rgba(0,0,0,0.1)] rounded-[3rem] bg-white focus:outline-none">
            <div class="mb-8">
                <h3 class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] mb-2">Automated Processing</h3>
                <div class="text-2xl font-black text-slate-800 uppercase tracking-tight">Bulk Allocation</div>
                <p class="text-[10px] text-slate-600 font-bold uppercase tracking-widest mt-1">Initialize credits for the work year</p>
            </div>

            <form action="{{ route('leave-credits.bulk-allocate') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-4">
                    <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-600 uppercase mb-3 tracking-[0.2em]">Target Fiscal Year</label>
                        <select name="year" class="w-full bg-white border-slate-200 rounded-full text-[11px] font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/10 transition-all py-3 px-6">
                            @for($y = date('Y') + 1; $y >= date('Y'); $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>CYCLE {{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-600 uppercase mb-3 tracking-[0.2em]">Allocation Strategy</label>
                        <select name="allocation_type" class="w-full bg-white border-slate-200 rounded-full text-[11px] font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/10 transition-all py-3 px-6">
                            <option value="missing_only">SMART FILL (EMPTY ONLY)</option>
                            <option value="all">FULL RESET (OVERWRITE ALL)</option>
                        </select>
                        <p class="mt-3 text-[9px] text-rose-400 font-black uppercase leading-relaxed tracking-tighter italic px-2">Warning: Overwrite will delete existing balances for the year.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-4">
                    <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-full text-[11px] font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 hover:bg-black transition-all active:scale-95">
                        Execute Allocation
                    </button>
                    <button type="button" onclick="document.getElementById('bulkAllocateModal').classList.add('hidden')"
                        class="w-full py-2 text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] hover:text-slate-600 transition-colors">
                        Cancel Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Carry Over Modal -->
    <div id="carryOverModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-md overflow-y-auto h-full w-full hidden z-50 transition-all duration-300">
        <div class="relative top-20 mx-auto p-10 border-0 w-[450px] shadow-[0_20px_50px_rgba(0,0,0,0.1)] rounded-[3rem] bg-white focus:outline-none">
            <div class="mb-8">
                <h3 class="text-[10px] font-black text-amber-600 uppercase tracking-[0.2em] mb-2">Balance Migration</h3>
                <div class="text-2xl font-black text-slate-800 uppercase tracking-tight">Carry Over</div>
                <p class="text-[10px] text-slate-600 font-bold uppercase tracking-widest mt-1">Transfer remaining days to next year</p>
            </div>

            <form action="{{ route('leave-credits.carry-over') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-4">
                    <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-600 uppercase mb-3 tracking-[0.2em]">Category to Transfer</label>
                        <select name="leave_type_id" class="w-full bg-white border-slate-200 rounded-full text-[11px] font-black uppercase tracking-widest focus:ring-4 focus:ring-amber-500/10 transition-all py-3 px-6" required>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-3 tracking-[0.2em]">Source</label>
                            <select name="from_year" class="w-full bg-white border-slate-200 rounded-full text-[11px] font-black uppercase tracking-widest focus:ring-4 focus:ring-amber-500/10 transition-all py-3 px-6 text-center">
                                @for($y = date('Y'); $y >= date('Y') - 1; $y--) <option value="{{ $y }}">{{ $y }}</option> @endfor
                            </select>
                        </div>
                        <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-3 tracking-[0.2em]">Target</label>
                            <select name="to_year" class="w-full bg-white border-slate-200 rounded-full text-[11px] font-black uppercase tracking-widest focus:ring-4 focus:ring-amber-500/10 transition-all py-3 px-6 text-center">
                                <option value="{{ date('Y') + 1 }}">{{ date('Y') + 1 }}</option>
                                <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-600 uppercase mb-3 tracking-[0.2em]">Policy Capping (Days)</label>
                        <input type="number" name="max_carryover" value="5" min="0" step="0.5" class="w-full bg-white border-slate-200 rounded-full text-[11px] font-black focus:ring-4 focus:ring-amber-500/10 transition-all py-3 px-6 text-center">
                        <p class="mt-3 text-[8px] text-slate-600 uppercase font-black tracking-widest italic text-center">Maximum days allowed forward.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-4">
                    <button type="submit" class="w-full bg-amber-500 text-white py-4 rounded-full text-[11px] font-black uppercase tracking-[0.2em] shadow-xl shadow-amber-100 hover:bg-amber-600 transition-all active:scale-95">
                        Start Transfer
                    </button>
                    <button type="button" onclick="document.getElementById('carryOverModal').classList.add('hidden')"
                        class="w-full py-2 text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] hover:text-slate-600 transition-colors">
                        Dismiss
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Adjust Modal -->
    <div id="adjustModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-md overflow-y-auto h-full w-full hidden z-50 transition-all duration-300">
        <div class="relative top-20 mx-auto p-10 border-0 w-[450px] shadow-[0_20px_50px_rgba(0,0,0,0.1)] rounded-[3rem] bg-white focus:outline-none">
            <div class="mb-8 text-center">
                <h3 class="text-[10px] font-black text-slate-600 uppercase tracking-[0.3em] mb-2 leading-none">Manual Correction</h3>
                <div id="adjustEmployeeName" class="text-xl font-black text-slate-800 uppercase tracking-tight"></div>
            </div>

            <form id="adjustForm" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <div class="space-y-4">
                    <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-600 uppercase mb-3 tracking-[0.2em]">Select Leave Type</label>
                        <select name="leave_type_id" class="w-full bg-white border-slate-200 rounded-full text-[11px] font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/10 transition-all py-3 px-6" required>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-3 tracking-[0.2em]">Action</label>
                            <select name="adjustment_type" class="w-full bg-white border-slate-200 rounded-full text-[11px] font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/10 transition-all py-3 px-6" required>
                                <option value="add">ADD (+)</option>
                                <option value="deduct">DEDUCT (-)</option>
                                <option value="set">SET TO</option>
                            </select>
                        </div>
                        <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-3 tracking-[0.2em]">Days</label>
                            <input type="number" name="amount" step="0.5" min="0.5" class="w-full bg-white border-slate-200 rounded-full text-[11px] font-black focus:ring-4 focus:ring-indigo-500/10 transition-all py-3 px-6 text-center" required>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-600 uppercase mb-3 tracking-[0.2em]">Audit Remarks</label>
                        <textarea name="remarks" class="w-full bg-white border-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/10 transition-all py-3 px-6" rows="2" placeholder="STATE REASON FOR MANUAL ADJUSTMENT..."></textarea>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-4">
                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-full text-[11px] font-black uppercase tracking-[0.1em] shadow-xl shadow-indigo-100 hover:bg-slate-900 transition-all active:scale-95">
                        Commit Adjustments
                    </button>
                    <button type="button" onclick="document.getElementById('adjustModal').classList.add('hidden')"
                        class="w-full py-2 text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] hover:text-slate-600 transition-colors">
                        Cancel Change
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Shared Actions Form -->
    <form id="submission-form" method="POST" class="hidden">
        @csrf
        @method('PATCH')
        <input type="hidden" name="rejection_reason" id="submission-reason">
    </form>

    @push('scripts')
    <script>
        function confirmApprove(url) {
            Swal.fire({
                title: 'Final Approval?',
                text: "This will deduct credits and finalize the request.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10B981',
                confirmButtonText: 'Yes, Approve',
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
                title: 'Decline Request?',
                text: "Specify reason for record:",
                icon: 'warning',
                input: 'text',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Reject',
                inputValidator: (value) => !value && 'Reason is required'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('submission-form');
                    form.action = url;
                    document.getElementById('submission-reason').value = result.value;
                    form.submit();
                }
            });
        }

        function openAdjustModal(id, name) {
            document.getElementById('adjustEmployeeName').innerText = name;
            document.getElementById('adjustForm').action = `/leave-credits/${id}/adjust`;
            document.getElementById('adjustModal').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
