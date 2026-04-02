@extends("layouts.app")

@section("header")
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-8">
        <div>
            <h1 class="text-3xl font-black text-rose-800 tracking-tighter uppercase mb-1">DTR Dashboard</h1>
            <p class="text-rose-600 font-bold text-xs uppercase tracking-widest flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                Daily Time Record Management System
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-white/80 backdrop-blur border border-rose-100 px-4 py-2 rounded shadow-sm text-right">
                <div class="text-[10px] font-black text-rose-400 uppercase leading-none mb-1">Current Date</div>
                <div class="text-sm font-black text-rose-800">{{ now()->format("F d, Y") }}</div>
            </div>
            @if(request("payroll_group_id"))
                <a href="{{ route("dtr-approval.index") }}" class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-2.5 rounded shadow-sm hover:bg-rose-100 transition-all text-xs font-black uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 15l-3-3m0 0l3-3m-3 3h8m-1 7a9 9 0 110-18 9 9 0 010 18z"></path></svg>
                    Back to Groups
                </a>
            @endif
        </div>
    </div>
@endsection

@section("content")
<div class="max-w-7xl mx-auto px-4 pb-12">
    @if(session("info"))
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded shadow-sm animate-bounce-in">
            <div class="flex items-center gap-3 italic text-blue-800 font-bold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session("info") }}
            </div>
        </div>
    @endif

    @if(session("error"))
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 mb-6 rounded shadow-sm animate-bounce-in">
            <div class="flex items-center justify-between gap-3 italic text-rose-800 font-bold text-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    {{ session("error") }}
                </div>
                
                @if(session("show_redo_edit"))
                    <div class="flex items-center gap-2">
                        <form action="{{ route('dtr-approval.redo-period', session('period_id')) }}" method="POST" onsubmit="return confirm('WARNING: This will delete ALL DTR records for this period and let you re-generate them. Continue?')">
                            @csrf
                            <button type="submit" class="bg-rose-600 text-white px-3 py-1.5 rounded text-[10px] uppercase font-black hover:bg-rose-700 transition-all shadow-sm">Redo</button>
                        </form>
                        <form action="{{ route('dtr-approval.edit-open', session('period_id')) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-[10px] uppercase font-black hover:bg-indigo-700 transition-all shadow-sm">Edit</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if(session("success"))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded shadow-sm animate-bounce-in">
            <div class="flex items-center gap-3 italic text-emerald-800 font-bold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session("success") }}
            </div>
        </div>
    @endif

    @if(!request("payroll_group_id"))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($payrollGroups as $group)
                <div class="bg-white rounded shadow-sm border border-rose-200 overflow-hidden group hover:shadow-md transition-all">
                    <div class="bg-rose-100 px-4 py-2 border-b border-rose-200 flex justify-between items-center relative overflow-hidden">
                        <div class="absolute inset-y-0 left-0 w-1 bg-rose-500"></div>
                        <h3 class="text-[11px] font-black text-rose-800 uppercase tracking-wider pl-2">{{ $group->name }}</h3>
                        <span class="text-[9px] bg-white text-rose-600 px-1.5 py-0.5 rounded font-black uppercase border border-rose-100 shadow-sm">
                            {{ $group->period_type }}
                        </span>
                    </div>

                    <div class="p-5">
                        <form action="{{ route("dtr-approval.index") }}" method="GET" class="space-y-4">
                            <input type="hidden" name="payroll_group_id" value="{{ $group->id }}">
                            <div class="flex items-center gap-4">
                                <label class="w-24 text-[10px] font-black text-rose-600 uppercase text-right tracking-tight">Option</label>
                                <div class="flex-1 relative">
                                    <select name="status" class="w-full bg-white border border-slate-200 text-[11px] rounded p-1.5 focus:ring-1 focus:ring-rose-500 font-bold text-slate-700 appearance-none">
                                        <option value="process" {{ request("status") == "process" ? "selected" : "" }}>PHASE 1: Review & Approve (Drafts)</option>
                                        <option value="pending" {{ request("status") == "pending" ? "selected" : "" }}>PHASE 2: View Completed DTRs</option>
                                        <option value="correction_pending" {{ request("status") == "correction_pending" ? "selected" : "" }}>Check System Discrepancies</option>
                                        <option value="clear" class="text-rose-600 font-black">RESET: CLEAR ALL DATA</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <label class="w-24 text-[10px] font-black text-rose-600 uppercase text-right tracking-tight">Period</label>
                                <div class="flex-1 relative">
                                    <select name="payroll_period_id" id="period_select_{{ $group->id }}" class="w-full bg-rose-50/30 border border-slate-200 text-[11px] rounded p-1.5 focus:ring-1 focus:ring-rose-500 font-medium text-slate-700 appearance-none">
                                        @forelse($payrollPeriods->where("payroll_group_id", $group->id) as $period)
                                            <option value="{{ $period->id }}" {{ request("payroll_period_id") == $period->id ? "selected" : "" }}>
                                                {{ $period->remarks ?? optional($period->start_date)->format("M d") . " - " . optional($period->end_date)->format("M d, Y") }}
                                            </option>
                                        @empty
                                            <option value="">No Active Periods</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 mt-2 border-t border-slate-50">
                                <button type="submit" class="bg-rose-600 text-white font-black py-2.5 px-8 rounded shadow-md transition-all hover:bg-rose-700 active:scale-95 uppercase tracking-widest text-[11px] flex items-center gap-2 border-0">
                                    Generate
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center">
                    <h2 class="text-xl font-black text-rose-800 uppercase tracking-tight">No Payroll Groups Found</h2>
                </div>
            @endforelse
        </div>
    @else
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dtr-approval.index') }}" class="bg-white border border-rose-200 text-rose-600 px-4 py-2 rounded text-[10px] font-black uppercase tracking-widest hover:bg-rose-50 transition-all flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Groups
                </a>
                <div class="flex flex-col">
                    <h2 class="text-xs font-black text-rose-600 uppercase tracking-[0.2em]">
                        @if(request('status') === 'pending')
                            VIEWING COMPLETED DATAS (PHASE 2)
                        @elseif(request('status') === 'process')
                            REVIEW & APPROVE DRAFTS (PHASE 1)
                        @else
                            SYSTEM DISCREPANCY CHECK
                        @endif
                        ({{ $dtrs->total() }} Records)
                    </h2>
                    @if(isset($period))
                        <span class="text-[9px] font-bold uppercase tracking-wider {{ in_array($period->status, ['completed', 'processed', 'finalized']) ? 'text-emerald-600' : 'text-amber-600' }}">
                            Period Status: {{ strtoupper($period->status) }} 
                            @if(in_array($period->status, ['completed', 'processed', 'finalized']))
                                (Manual calculation ready)
                            @else
                                (Pending review)
                            @endif
                        </span>
                    @endif
                </div>
                
                @if(isset($period) && in_array($period->status, ['completed', 'processed', 'finalized']))
                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-tighter flex items-center gap-1 border border-emerald-200">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        Period Completed
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @if(isset($period) && in_array($period->status, ['completed', 'processed', 'finalized']))
                    @if(!$period->is_published)
                        <form action="{{ route('dtr-approval.publish', $period->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-indigo-600 text-white font-black px-6 py-2 rounded text-[10px] uppercase tracking-wider hover:bg-indigo-700 shadow-sm flex items-center gap-1.5 transition-all outline outline-2 outline-offset-2 outline-indigo-100">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Publish to Portals
                            </button>
                        </form>
                    @else
                        <div class="flex items-center gap-2">
                            <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded text-[10px] font-black uppercase tracking-widest border border-indigo-200 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                Live on Portal
                            </span>
                            <form action="{{ route('dtr-approval.unpublish', $period->id) }}" method="POST" onsubmit="return confirm('Archive from portal view?')">
                                @csrf
                                <button type="submit" class="text-slate-400 hover:text-rose-600 p-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                                </button>
                            </form>
                        </div>
                    @endif
                @endif

                <a href="{{ route('dtr-approval.create', ['payroll_group_id' => request('payroll_group_id')]) }}" class="bg-indigo-600 text-white font-black px-4 py-2 rounded text-[10px] uppercase tracking-wider hover:bg-indigo-700 shadow-sm flex items-center gap-1.5 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Manual Entry
                </a>

                @if(request('status') === 'process' && $dtrs->count() > 0 && !(isset($period) && in_array($period->status, ['completed', 'processed', 'finalized'])))
                    <button onclick="approvePerfectRecords()" class="bg-indigo-600 text-white font-black px-4 py-2 rounded text-[10px] uppercase tracking-wider hover:bg-indigo-700 shadow-sm flex items-center gap-1.5 transition-all">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" /><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        Approve Perfect Records
                    </button>
                    
                    <button onclick="approveBatch()" class="bg-emerald-600 text-white font-black px-4 py-2 rounded text-[10px] uppercase tracking-wider hover:bg-emerald-700 shadow-sm flex items-center gap-1.5 transition-all">
                        Batch Approve
                    </button>
                @endif
            </div>
        </div>

        <div class="bg-white rounded shadow-sm border border-rose-100 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-rose-50 border-b border-rose-100">
                        <tr>
                            <th class="px-6 py-4 w-10">
                                @if(request('status') === 'process' && !(isset($period) && in_array($period->status, ['completed', 'processed', 'finalized'])))
                                    <input type="checkbox" id="select-all" class="rounded border-rose-300 text-rose-600">
                                @endif
                            </th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-rose-700">Employee</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-rose-700">Date</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-rose-700">Punches</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-rose-700">Net Work</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-rose-700 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($dtrs as $dtr)
                            @php
                                $hasIssue = !$dtr->time_in || !$dtr->time_out || $dtr->late_minutes > 0 || $dtr->undertime_minutes > 0 || (isset($dtr->overbreak_minutes) && $dtr->overbreak_minutes > 0) || $dtr->attendance_status === 'absent';
                                $isLocked = request('status') !== 'process' || (isset($period) && in_array($period->status, ['completed', 'processed', 'finalized']));
                            @endphp
                            <tr class="hover:bg-rose-50/50 {{ $hasIssue ? 'bg-amber-50/40' : '' }}" id="dtr-row-{{ $dtr->id }}">
                                <td class="px-6 py-4">
                                    @if(!$isLocked)
                                        <input type="checkbox" value="{{ $dtr->id }}" class="dtr-checkbox rounded border-slate-200 text-rose-600" data-has-issue="{{ $hasIssue ? 'true' : 'false' }}">
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-black text-xs text-slate-800 uppercase">
                                    <div class="flex items-center gap-2">
                                        @if($hasIssue)
                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse" title="Requires Review"></span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-emerald-500" title="Perfect Record"></span>
                                        @endif
                                        <a href="{{ route('dtr-approval.show', $dtr->id) }}" class="hover:text-rose-600 transition-colors">
                                            {{ $dtr->user->full_name ?? $dtr->user->name }}
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-black text-slate-700">{{ $dtr->date->format("M d, Y") }}</div>
                                    <div class="text-[9px] font-bold text-rose-400 uppercase">{{ $dtr->date->format("l") }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-[10px] font-bold text-slate-500">
                                        IN: <span class="{{ !$dtr->time_in && $dtr->attendance_status !== 'absent' ? 'text-red-600 underline' : '' }}">{{ $dtr->time_in ? $dtr->time_in->format("h:i A") : "--:--" }}</span> | 
                                        OUT: <span class="{{ !$dtr->time_out && $dtr->attendance_status !== 'absent' ? 'text-red-600 underline' : '' }}">{{ $dtr->time_out ? $dtr->time_out->format("h:i A") : "--:--" }}</span>
                                    </div>
                                    @if($dtr->late_minutes > 0 || $dtr->undertime_minutes > 0)
                                        <div class="flex gap-2 mt-1">
                                            @if($dtr->late_minutes > 0) <span class="text-[8px] bg-red-100 text-red-700 px-1 rounded font-black">LATE: {{ $dtr->late_minutes }}m</span> @endif
                                            @if($dtr->undertime_minutes > 0) <span class="text-[8px] bg-amber-100 text-amber-700 px-1 rounded font-black">UT: {{ $dtr->undertime_minutes }}m</span> @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-black text-xs text-slate-800">
                                    {{ number_format($dtr->net_work_minutes / 60, 2) }} HRS
                                    @if($dtr->attendance_status === 'absent')
                                        <span class="block text-[8px] text-red-600 uppercase">Absent</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if(request('status') === 'pending' || (isset($period) && in_array($period->status, ['completed', 'processed', 'finalized'])))
                                        <div class="flex items-center justify-end gap-3">
                                            <span class="text-emerald-600 bg-emerald-50 px-2 py-1 rounded text-[9px] font-black border border-emerald-100 uppercase italic">Finalized</span>
                                            <a href="{{ route('dtr-approval.edit', $dtr->id) }}" class="text-rose-600 hover:text-rose-800 font-black text-[10px] uppercase tracking-wider flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Edit
                                            </a>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="approveDtr('{{ $dtr->id }}')" class="bg-rose-600 text-white font-black px-3 py-1.5 rounded text-[9px] uppercase hover:bg-rose-700">Approve</button>
                                            <form action="{{ route('dtr-approval.destroy', $dtr->id) }}" method="POST" onsubmit="return confirm('Permanently delete this DTR record?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors p-1" title="Delete record">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-4">
                                        <div class="inline-flex items-center justify-center w-16 h-16 bg-rose-50 rounded-full">
                                            <svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                            </svg>
                                        </div>
                                        @if(request('status') === 'pending')
                                            <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">DTR Records Not Found</h3>
                                            <p class="text-xs text-slate-500 max-w-sm mx-auto italic font-medium leading-relaxed">Please Review and Approve Payroll Period to view DTR's.</p>
                                        @elseif(request('status') === 'correction_pending')
                                            <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">No Discrepancies Found</h3>
                                            <p class="text-xs text-slate-500 max-w-sm mx-auto italic font-medium leading-relaxed">Great job! There are no records currently awaiting correction or flagged with discrepancies for this selection.</p>
                                        @else
                                            <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">DTR Records Not Found</h3>
                                            <p class="text-xs text-slate-500 max-w-sm mx-auto italic font-medium leading-relaxed">The payroll period has been reset or hasn't been processed yet. You must generate the Daily Time Records from raw attendance logs to proceed.</p>
                                            
                                            <form action="{{ route('dtr-approval.generate') }}" method="POST" class="mt-4">
                                                @csrf
                                                <input type="hidden" name="payroll_period_id" value="{{ request('payroll_period_id') }}">
                                                <input type="hidden" name="payroll_group_id" value="{{ request('payroll_group_id') }}">
                                                <button type="submit" class="bg-rose-600 text-white font-black px-8 py-3 rounded-lg text-xs uppercase tracking-[0.1em] hover:bg-rose-700 shadow-lg shadow-rose-100 transition-all flex items-center gap-2 active:scale-95 group">
                                                    <svg class="w-4 h-4 transition-transform group-hover:rotate-180 duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                    Generate DTR Records Now
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script>
    function approveDtr(id) {
        if(!confirm('Approve this DTR record?')) return;
        
        fetch("{{ url("dtr-approval") }}/" + id + "/approve", {
            method: "POST",
            headers: { 
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json",
                "Accept": "application/json"
            }
        })
        .then(r => r.json())
        .then(d => { 
            if(d.success) { 
                const row = document.getElementById("dtr-row-" + id);
                if(row) row.remove();
                alert(d.message || 'DTR approved successfully');
            } else {
                alert('Error: ' + (d.message || 'Unknown error occurred'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('A system error occurred. Please check the console.');
        });
    }

    function approvePerfectRecords() {
        const perfectCheckboxes = Array.from(document.querySelectorAll(".dtr-checkbox[data-has-issue='false']"));
        const ids = perfectCheckboxes.map(cb => cb.value);
        
        if (ids.length === 0) {
            alert('No perfect records found on this page to approve.');
            return;
        }

        if (!confirm(`Approve all ${ids.length} perfect records (no lates, no absences, no missing logs)?`)) return;

        fetch("{{ route('dtr-approval.bulk-approve') }}", {
            method: "POST",
            headers: { 
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({ 
                dtr_ids: ids,
                level: 'final'
            })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                ids.forEach(id => {
                    const row = document.getElementById("dtr-row-" + id);
                    if (row) row.remove();
                });
                alert(d.message || `Successfully approved ${ids.length} records.`);
                if(document.querySelectorAll(".dtr-checkbox").length === 0) {
                    window.location.reload();
                }
            } else {
                alert('Error: ' + (d.message || 'Unknown error occurred'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('A system error occurred.');
        });
    }

    function approveBatch() {
        const ids = Array.from(document.querySelectorAll(".dtr-checkbox:checked")).map(cb => cb.value);
        if (ids.length === 0) {
            alert('Please select at least one record to approve.');
            return;
        }

        if(!confirm(`Are you sure you want to approve ${ids.length} selected records?`)) return;

        fetch("{{ route("dtr-approval.bulk-approve") }}", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json", 
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({ 
                dtr_ids: ids,
                level: 'final'
            })
        })
        .then(r => r.json())
        .then(d => { 
            if(d.success) {
                alert(d.message || 'Bulk approval successful');
                window.location.reload(); 
            } else {
                alert('Error: ' + (d.message || 'Some records could not be approved'));
                window.location.reload();
            }
        })
        .catch(err => {
            console.error(err);
            alert('A system error occurred during bulk approval.');
        });
    }
    document.getElementById("select-all")?.addEventListener("change", function() {
        document.querySelectorAll(".dtr-checkbox").forEach(cb => cb.checked = this.checked);
    });
</script>
@endsection
