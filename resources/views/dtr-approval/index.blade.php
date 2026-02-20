@extends('layouts.app')

@section('title', 'DTR Approval')

@section('content')
<div class="min-h-screen bg-white p-4 sm:p-6 lg:p-8">
    <!-- Breadcrumbs & Header -->
    <div class="max-w-[1600px] mx-auto mb-12">
        <div class="flex items-center text-[10px] font-medium text-slate-400 gap-1.5 uppercase tracking-widest mb-4">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
            <span>HOME</span>
            <span class="text-slate-300 mx-1">></span>
            <span>HR MANAGEMENT</span>
            <span class="text-slate-300 mx-1">></span>
            <span class="text-slate-400">DTR CENTER</span>
        </div>
        <div class="flex items-baseline gap-2">
            <h1 class="text-3xl font-bold text-slate-700">DTR Center</h1>
            <span class="text-slate-400 text-sm font-medium">Daily Time Record Approval</span>
        </div>
    </div>

    <!-- Grid Layout for Groups -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-0 gap-y-0 max-w-[1600px] mx-auto border-t border-l border-slate-100">
        @php
            // Use real PayrollGroups from the system
            $displayGroups = $payrollGroups;
        @endphp

        @forelse($displayGroups as $group)
            <div class="bg-white p-0 border-r border-b border-slate-100 min-h-[400px]">
                <!-- Card Header -->
                <div class="px-8 py-4 flex justify-between items-center bg-white">
                    <h2 class="text-slate-800 font-bold uppercase tracking-tight text-[11px]">
                        {{ str_contains(strtoupper($group->name), 'GROUP') ? strtoupper($group->name) : strtoupper($group->name) . ' GROUP' }}
                    </h2>
                    <div class="bg-white px-4 py-1.5 rounded border border-slate-200 text-[10px] font-bold text-slate-500 uppercase cursor-pointer hover:bg-slate-50 transition-colors">
                        Click Me To Filter OR Individual Processing
                    </div>
                </div>

                <!-- Card Body -->
                <div class="px-16 py-12 space-y-8">
                    <form action="{{ route('dtr.admin-index') }}" method="GET" class="space-y-8">
                        <input type="hidden" name="payroll_group_id" value="{{ $group->id }}">
                        
                        <!-- Option Select -->
                        <div class="flex items-center gap-8">
                            <label class="w-32 text-right font-bold text-slate-700 text-[11px] uppercase tracking-tighter">Option</label>
                            <select name="status" class="flex-1 bg-white border border-slate-300 text-[11px] rounded p-2.5 focus:ring-0 focus:border-slate-400 transition-all text-slate-600">
                                <option value="processed" selected>View Processed DTR</option>
                                <option value="pending">View Pending DTR</option>
                                <option value="">View All DTR</option>
                            </select>
                        </div>

                        <!-- Payroll Period Select -->
                        <div class="flex items-center gap-8">
                            <label class="w-32 text-right font-bold text-slate-700 text-[11px] uppercase tracking-tighter">Payroll Period</label>
                            <select name="payroll_period_id" class="flex-1 bg-white border border-slate-300 text-[11px] rounded p-2.5 focus:ring-0 focus:border-slate-400 transition-all text-slate-600">
                                @forelse($payrollPeriods->where('payroll_group_id', $group->id) as $period)
                                    <option value="{{ $period->id }}">
                                        {{ optional($period->start_date)->format('F d Y') }} to {{ optional($period->end_date)->format('F d Y') }} (Paydate:{{ optional($period->pay_date)->format('Y-m-d') }})
                                    </option>
                                @empty
                                    <option value="">No payroll periods for this group</option>
                                @endforelse
                            </select>
                        </div>

                        <!-- Generate Button -->
                        <div class="flex justify-end pt-8">
                            <button type="submit" class="bg-[#fcfdfd] border border-slate-100 text-[#fcfdfd] px-8 py-3 rounded text-[11px] font-bold uppercase transition-all hover:border-slate-200">
                                Generate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-2 p-32 text-center text-slate-400 border-r border-b border-slate-100">
                <div class="flex flex-col items-center">
                    <svg class="w-16 h-16 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="text-lg font-medium text-slate-500 mb-1">No payroll groups found.</p>
                    <p class="text-sm text-slate-400 mb-8">You need to create a payroll group first to start reviewing DTRs.</p>
                    <a href="{{ route('payroll-groups.index') }}" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-sm hover:bg-indigo-700 transition-colors">
                        Manage Payroll Groups
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
