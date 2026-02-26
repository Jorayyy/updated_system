@extends('layouts.app')

@section('title', 'DTR Approval Center')

@section('content')
<div class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8">
    <!-- Breadcrumbs & Header -->
    <div class="max-w-[1600px] mx-auto mb-8">
        <div class="flex items-center text-[10px] font-medium text-slate-400 gap-1.5 uppercase tracking-widest mb-4">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
            <span>HOME</span>
            <span class="text-slate-300 mx-1">/</span>
            <span>APPROVALS</span>
            <span class="text-slate-300 mx-1">/</span>
            <span class="text-indigo-600 font-bold">DTR CENTER</span>
        </div>
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight uppercase leading-none mb-2">DTR Approval Center</h1>
                <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Manage, Review and Finalize Employee Daily Time Records</p>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('dtr-approval.index', ['status' => 'pending']) }}" class="bg-amber-100 text-amber-700 px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-tighter hover:bg-amber-200 transition-all text-center">
                    Pending ({{ $stats['pending'] ?? 0 }})
                </a>
                <a href="{{ route('dtr-approval.index', ['status' => 'correction_pending']) }}" class="bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-tighter hover:bg-blue-200 transition-all text-center">
                    Corrections ({{ $stats['correction_pending'] ?? 0 }})
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Overview -->
    <div class="max-w-[1600px] mx-auto grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Records</span>
            <span class="text-2xl font-black text-slate-800">{{ $stats['total'] ?? 0 }}</span>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <span class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Approved</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-indigo-600">{{ $stats['approved'] ?? 0 }}</span>
                <span class="text-[10px] font-bold text-slate-400 italic">Synced</span>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <span class="block text-[10px] font-black text-amber-400 uppercase tracking-widest mb-1">Pending Review</span>
            <span class="text-2xl font-black text-amber-600">{{ $stats['pending'] ?? 0 }}</span>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <span class="block text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">Correction Needed</span>
            <span class="text-2xl font-black text-rose-600">{{ $stats['correction_pending'] ?? 0 }}</span>
        </div>
    </div>

    <!-- Workflow Selector -->
    <div class="max-w-[1600px] mx-auto mb-10">
        <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
            Approval Batches by Payroll Group
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($payrollGroups as $group)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden group hover:shadow-md transition-all hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ $group->name }}</h3>
                                <div class="flex gap-2 mt-1">
                                    <span class="text-[8px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded font-bold uppercase">{{ $group->period_type }}</span>
                                    @if($group->period_type === 'weekly')
                                        <form action="{{ route('dtr-approval.advance-week', $group) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-[8px] bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded font-bold uppercase hover:bg-emerald-100" title="Add next week in advance">+ Advance Week</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                        </div>

                        <form action="{{ route('dtr-approval.index') }}" method="GET" class="space-y-4">
                            <input type="hidden" name="payroll_group_id" value="{{ $group->id }}">
                            
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1.5 ml-1 leading-none">Status</label>
                                <select name="status" class="w-full bg-slate-50 border-0 text-xs rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 font-bold text-slate-700">
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Approvals</option>
                                    <option value="correction_pending" {{ request('status') == 'correction_pending' ? 'selected' : '' }}>Correction Requests</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved Records</option>
                                    <option value="">View All Records</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1.5 ml-1 leading-none">Payroll Period</label>
                                <select name="payroll_period_id" id="period_select_{{ $group->id }}" class="w-full bg-slate-50 border-0 text-xs rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 font-medium text-slate-700">
                                    @forelse($payrollPeriods->where('payroll_group_id', $group->id) as $period)
                                        <option value="{{ $period->id }}" {{ request('payroll_period_id') == $period->id ? 'selected' : '' }}>
                                            {{ $period->name ?? optional($period->start_date)->format('M d') . ' - ' . optional($period->end_date)->format('M d, Y') }}
                                        </option>
                                    @empty
                                        <option value="">No Active Periods</option>
                                    @endforelse
                                </select>
                                @if($payrollPeriods->where('payroll_group_id', $group->id)->isEmpty())
                                    <a href="{{ route('payroll-groups.show', $group->id) }}" class="text-[9px] text-indigo-600 font-bold uppercase mt-1 block hover:underline">
                                        + Generate New Period in Group Settings
                                    </a>
                                @endif
                            </div>

                            <div class="flex gap-2 pt-2">
                                <button type="submit" class="flex-1 bg-slate-900 text-white font-black py-4 px-4 rounded-xl shadow-sm transition-all hover:bg-black hover:shadow-lg uppercase tracking-widest text-[10px] flex items-center justify-center gap-2 group/btn">
                                    Review Table
                                    <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                </button>
                                
                                <button type="button" 
                                    onclick="generateDtr('{{ $group->id }}')" 
                                    class="bg-indigo-50 text-indigo-600 font-black px-4 rounded-xl hover:bg-indigo-100 transition-all flex items-center justify-center"
                                    title="Generate/Sync Records">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl border-2 border-dashed border-slate-200 p-20 text-center">
                    <p class="text-slate-500 font-bold uppercase tracking-widest leading-none mb-1">No Payroll Groups found</p>
                    <p class="text-slate-400 text-[10px] font-medium">Please create a payroll group in settings to begin.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Active Review Table -->
    @if(request('payroll_group_id'))
        <div id="review-section" class="max-w-[1600px] mx-auto bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden mb-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="bg-indigo-600 px-8 py-6 flex justify-between items-center text-white">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black uppercase tracking-tight">Reviewing: {{ $payrollGroups->find(request('payroll_group_id'))->name ?? 'Selected Group' }}</h3>
                        <p class="text-indigo-100 text-[10px] font-bold uppercase tracking-widest flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            {{ $dtrs->total() }} Records Found for Selected Status
                        </p>
                    </div>
                </div>
                <div class="flex gap-4">
                    @if(request('payroll_period_id'))
                    <button type="button" 
                        onclick="generateDtr('{{ request('payroll_group_id') }}', '{{ request('payroll_period_id') }}')" 
                        class="bg-white/10 text-white border border-white/20 px-6 py-2.5 rounded-lg text-[10px] font-black uppercase hover:bg-white/20 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Sync DTR Records
                    </button>

                    <form action="{{ route('dtr-approval.approve-all-period', request('payroll_period_id')) }}" method="POST" onsubmit="return confirm('APPROVE ALL PENDING RECORDS FOR THIS PERIOD? \n\nThis will process all records shown below that are not yet approved.')">
                        @csrf
                        <button type="submit" class="bg-white text-indigo-600 px-6 py-2.5 rounded-lg text-[10px] font-black uppercase shadow-lg hover:bg-indigo-50 transition-colors">
                            Approve Entire Period
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs font-medium text-slate-700">
                    <thead class="bg-slate-50 border-b border-slate-100 text-[10px] uppercase font-bold text-slate-500 italic tracking-widest">
                        <tr>
                            <th class="px-8 py-4 text-left">Employee Name</th>
                            <th class="px-6 py-4 text-center">Date</th>
                            <th class="px-6 py-4 text-center">Punch Times</th>
                            <th class="px-6 py-4 text-center">Work Hours</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Review Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($dtrs as $dtr)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="font-black text-slate-800 uppercase tracking-tight leading-none mb-1">{{ $dtr->user->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $dtr->user->employee_id }} • {{ $dtr->user->department }}</div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="text-slate-800 font-bold">{{ $dtr->date->format('M d, Y') }}</div>
                                    <div class="text-[9px] text-slate-400 uppercase font-black">{{ $dtr->date->format('l') }}</div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="inline-flex gap-2">
                                        <span class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded font-black text-[10px]">{{ $dtr->time_in ? $dtr->time_in->format('h:i A') : '--:--' }}</span>
                                        <span class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded font-black text-[10px]">{{ $dtr->time_out ? $dtr->time_out->format('h:i A') : '--:--' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center font-black text-slate-900 border-x border-slate-50 bg-slate-50/10">
                                    {{ number_format($dtr->total_work_minutes/60, 2) }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @if($dtr->status === 'approved')
                                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-[9px] font-black uppercase">Approved</span>
                                    @elseif($dtr->status === 'correction_pending')
                                        <span class="px-3 py-1 bg-rose-100 text-rose-700 rounded-lg text-[9px] font-black uppercase">Correction Needed</span>
                                    @else
                                        <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-lg text-[9px] font-black uppercase">{{ $dtr->status }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('dtr-approval.show', $dtr) }}" class="text-slate-400 hover:text-indigo-600 transition-colors" title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                        @if($dtr->status !== 'approved')
                                            <form action="{{ route('dtr-approval.approve', $dtr) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="level" value="final">
                                                <button type="submit" class="text-emerald-500 hover:text-emerald-700 transition-colors" title="Approve">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                </button>
                                            </form>
                                            <form action="{{ route('dtr-approval.reject', $dtr) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="level" value="final">
                                                <button type="submit" class="text-rose-400 hover:text-rose-600 transition-colors" title="Needs Correction">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-20 text-center">
                                    <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mb-4">No records match these criteria</p>
                                    @if(request('payroll_period_id'))
                                        <button type="button" 
                                            onclick="generateDtr('{{ request('payroll_group_id') }}', '{{ request('payroll_period_id') }}')" 
                                            class="bg-indigo-600 text-white px-8 py-3 rounded-xl text-[10px] font-black uppercase shadow-lg hover:bg-indigo-700 transition-all inline-flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            Generate Initial Records for this Period
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($dtrs->hasPages())
                <div class="px-8 py-4 bg-slate-50 border-t border-slate-100">
                    {{ $dtrs->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

<form id="generate-form" action="{{ route('dtr-approval.generate') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="payroll_group_id" id="gen_group_id">
    <input type="hidden" name="payroll_period_id" id="gen_period_id">
</form>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if(request('payroll_group_id'))
            const reviewSection = document.getElementById('review-section');
            if (reviewSection) {
                reviewSection.scrollIntoView({ behavior: 'smooth' });
            }
        @endif
    });

    function generateDtr(groupId, providedPeriodId = null) {
        let periodId = providedPeriodId;
        if (!periodId) {
            const periodSelect = document.getElementById('period_select_' + groupId);
            periodId = periodSelect.value;
        }
        
        const appScope = document.querySelector('html').__x.$data;
        
        if(!periodId) {
            if(appScope) appScope.addNotification('error', 'Please select a payroll period first.');
            else alert('Please select a payroll period first.');
            return;
        }

        if (confirm('GENERATE DTR RECORDS? \n\nThis will re-sync all attendance logs and recalculate hours for this group. Existing unapproved records will be refreshed.')) {
            if(appScope) appScope.addNotification('info', 'Generation started. Please wait...');
            
            document.getElementById('gen_group_id').value = groupId;
            document.getElementById('gen_period_id').value = periodId;
            document.getElementById('generate-form').submit();
        }
    }

    // Add notification on successful table load
    document.addEventListener("DOMContentLoaded", function() {
        const appScope = document.querySelector('html').__x.$data;
        @if(request('payroll_group_id'))
            const count = {{ $dtrs->total() }};
            if(appScope) {
                appScope.addNotification('success', 'Loaded ' + count + ' records for review.');
            }
            
            const reviewSection = document.getElementById('review-section');
            if (reviewSection) {
                reviewSection.scrollIntoView({ behavior: 'smooth' });
            }
        @endif
    });
</script>
@endsection

