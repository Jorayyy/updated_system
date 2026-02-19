<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Admin Dashboard
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Welcome back, {{ auth()->user()->name }}! Here's what's happening today.
                </p>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ now()->format('l, F j, Y') }}
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards - Enhanced with animations -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total Employees -->
                <div class="group bg-white overflow-hidden shadow-sm sm:rounded-3xl hover:shadow-lg transition-all duration-300 border border-slate-100">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Employees</p>
                                <p class="mt-1 text-3xl font-black text-slate-900 leading-none">{{ $totalEmployees }}</p>
                                <p class="mt-2 text-[10px] font-black text-slate-400 uppercase">Active staff</p>
                            </div>
                            <div class="p-3 bg-blue-50 rounded-2xl">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Present Today -->
                <div class="group bg-white overflow-hidden shadow-sm sm:rounded-3xl hover:shadow-lg transition-all duration-300 border border-slate-100">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Present</p>
                                <p class="mt-1 text-3xl font-black text-emerald-600 leading-none">{{ $presentToday }}</p>
                                <p class="mt-2 text-[10px] font-black text-slate-400 uppercase">Checked in today</p>
                            </div>
                            <div class="p-3 bg-emerald-50 rounded-2xl">
                                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <!-- Progress bar -->
                        <div class="mt-4">
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500 shadow-sm" style="width: {{ $totalEmployees > 0 ? ($presentToday / $totalEmployees) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- On Leave -->
                <div class="group bg-white overflow-hidden shadow-sm sm:rounded-3xl hover:shadow-lg transition-all duration-300 border border-slate-100">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">On Leave</p>
                                <p class="mt-1 text-3xl font-black text-blue-600 leading-none">{{ $onLeaveToday }}</p>
                                <p class="mt-2 text-[10px] font-black text-slate-400 uppercase">Approved leaves</p>
                            </div>
                            <div class="p-3 bg-blue-50 rounded-2xl">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Absent -->
                <div class="group bg-white overflow-hidden shadow-sm sm:rounded-3xl hover:shadow-lg transition-all duration-300 border border-slate-100">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Absent</p>
                                <p class="mt-1 text-3xl font-black text-rose-600 leading-none">{{ $absentToday }}</p>
                                <p class="mt-2 text-[10px] font-black text-slate-400 uppercase">Not checked in</p>
                            </div>
                            <div class="p-3 bg-rose-50 rounded-2xl">
                                <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Content Grid (New Features) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <!-- Shift Change Requests -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-slate-100 hover:shadow-md transition-shadow">
                    <div class="p-5 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <h3 class="font-black text-slate-900 uppercase text-xs tracking-widest">Shift Requests</h3>
                        </div>
                         @if(isset($pendingShiftRequests) && $pendingShiftRequests->count() > 0)
                            <span class="bg-amber-100 text-amber-800 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter">{{ $pendingShiftRequests->count() }} Pending</span>
                        @endif
                    </div>
                    <div class="p-4">
                         @if(isset($pendingShiftRequests) && $pendingShiftRequests->count() > 0)
                            <div class="space-y-3">
                                @foreach($pendingShiftRequests as $req)
                                    <div class="flex justify-between items-start pb-3 border-b border-slate-50 last:border-0 last:pb-0">
                                        <div>
                                            <p class="text-xs font-black text-slate-900 uppercase tracking-tight">{{ $req->employee->name }}</p>
                                            <p class="text-[10px] font-black text-slate-500 mt-0.5 uppercase tracking-tighter">{{ $req->requested_date->format('M d') }} &bull; <span class="text-slate-700">{{ $req->new_schedule }}</span></p>
                                        </div>
                                        <a href="{{ route('shift-change-requests.index') }}" class="text-[10px] font-black bg-indigo-50 text-indigo-700 px-2 py-1 rounded-lg hover:bg-indigo-100 transition uppercase tracking-tighter">Review</a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">No pending shift requests.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Asset Overview -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-slate-100 hover:shadow-md transition-shadow">
                    <div class="p-5 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <h3 class="font-black text-slate-900 uppercase text-xs tracking-widest">Company Assets</h3>
                        </div>
                        <a href="{{ route('company-assets.index') }}" class="text-[10px] font-black text-slate-500 hover:text-slate-900 uppercase tracking-tighter">Manage &rarr;</a>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-center w-1/2 border-r border-slate-100">
                                <span class="block text-2xl font-black text-slate-900 leading-none">{{ $assignedAssetsCount ?? 0 }}</span>
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-loose">Assigned</span>
                            </div>
                            <div class="text-center w-1/2">
                                <span class="block text-2xl font-black text-slate-900 leading-none">{{ ($totalAssetsCount ?? 0) - ($assignedAssetsCount ?? 0) }}</span>
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-loose">Available</span>
                            </div>
                        </div>
                        <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                            @php
                                $percent = ($totalAssetsCount ?? 0) > 0 ? (($assignedAssetsCount ?? 0) / $totalAssetsCount) * 100 : 0;
                            @endphp
                            <div class="h-full bg-blue-500 shadow-sm transition-all duration-500" style="width: {{ $percent }}%"></div>
                        </div>
                        <p class="text-[10px] font-black text-center mt-3 text-slate-400 uppercase tracking-widest">{{ $totalAssetsCount ?? 0 }} Total Assets Recorded</p>
                    </div>
                </div>

                <!-- Performance Reviews Stats -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-slate-100 hover:shadow-md transition-shadow">
                    <div class="p-5 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                        <div class="flex items-center gap-2">
                             <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <h3 class="font-black text-slate-900 uppercase text-xs tracking-widest">Performance</h3>
                        </div>
                    </div>
                    <div class="p-4 flex flex-col justify-center h-40">
                         <div class="text-center">
                            <p class="text-4xl font-black text-purple-600 mb-1 leading-none">{{ $pendingReviewsCount ?? 0 }}</p>
                            <p class="text-xs font-black text-slate-600 uppercase tracking-tight">Pending Acknowledgements</p>
                            <p class="text-[10px] font-black text-slate-400 mt-2 uppercase tracking-tighter line-clamp-2 px-4">Reviews submitted but not yet signed by employees</p>
                         </div>
                         <div class="mt-4 text-center">
                            <a href="{{ route('performance-reviews.index') }}" class="text-[10px] font-black text-purple-600 hover:text-purple-800 uppercase tracking-widest underline decoration-2 underline-offset-4">Go to Reviews &rarr;</a>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
                <!-- Pending Leave Requests - Takes 2 columns -->
                <div x-data="{ expanded: true }" class="xl:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-slate-100">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-amber-100 rounded-2xl">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Pending Leaves</h3>
                                    <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest leading-none mt-1">{{ $pendingLeaveRequests->count() }} requests awaiting approval</p>
                                </div>
                            </div>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-slate-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <div x-show="expanded" x-collapse>
                        @if($pendingLeaveRequests->count() > 0)
                            <div class="divide-y divide-slate-50">
                                @foreach($pendingLeaveRequests as $request)
                                    <div class="p-4 hover:bg-slate-50 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl flex items-center justify-center text-white text-lg font-black shadow-sm">
                                                    {{ substr($request->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ $request->user->name }}</p>
                                                    <p class="flex items-center gap-2 mt-1">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black bg-blue-100 text-blue-800 uppercase tracking-tighter">
                                                            {{ $request->leaveType->name }}
                                                        </span>
                                                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                            <a href="{{ route('leaves.admin-show', $request) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition text-[10px] font-black uppercase tracking-widest shadow-sm">
                                                Review
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="p-4 bg-slate-50/50 border-t border-slate-100">
                                <a href="{{ route('leaves.manage') }}" class="flex items-center justify-center gap-2 text-indigo-600 hover:text-indigo-800 text-[10px] font-black uppercase tracking-widest transition underline decoration-2 underline-offset-4">
                                    View all leave requests
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </a>
                            </div>
                        @else
                            <div class="p-12 text-center">
                                <div class="w-16 h-16 bg-emerald-50 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p class="text-slate-900 font-black uppercase text-xs tracking-widest leading-none">All caught up!</p>
                                <p class="text-[10px] font-black text-slate-400 mt-2 uppercase tracking-widest">No pending leave requests to review.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Today's Attendance Summary -->
                <div x-data="{ expanded: true }" class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-slate-100">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-100 rounded-2xl">
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Attendance</h3>
                                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest leading-none mt-1">{{ $recentAttendances->count() }} check-ins today</p>
                                </div>
                            </div>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-slate-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <div x-show="expanded" x-collapse>
                        @if($recentAttendances->count() > 0)
                            <div class="max-h-80 overflow-y-auto custom-scrollbar">
                                <div class="divide-y divide-slate-50">
                                    @foreach($recentAttendances as $attendance)
                                        <div class="p-3 hover:bg-slate-50 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-slate-900 rounded-2xl flex items-center justify-center text-white text-xs font-black flex-shrink-0 shadow-sm">
                                                    {{ substr($attendance->user->name, 0, 1) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-black text-slate-900 truncate uppercase tracking-tight">{{ $attendance->user->name }}</p>
                                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">
                                                        {{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }}
                                                        @if($attendance->time_out)
                                                            • {{ $attendance->time_out->format('h:i A') }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <span class="px-2 py-1 text-[9px] rounded-lg font-black uppercase tracking-tighter
                                                    @if($attendance->status == 'present') bg-emerald-100 text-emerald-700
                                                    @elseif($attendance->status == 'late') bg-amber-100 text-amber-700
                                                    @else bg-slate-100 text-slate-700 @endif leading-none">
                                                    {{ $attendance->status }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50/50 border-t border-slate-100">
                                <a href="{{ route('attendance.manage') }}" class="flex items-center justify-center gap-2 text-indigo-600 hover:text-indigo-800 text-[10px] font-black uppercase tracking-widest transition underline decoration-2 underline-offset-4">
                                    View all records
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </a>
                            </div>
                        @else
                            <div class="p-12 text-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-slate-900 font-black uppercase text-xs tracking-widest leading-none">No records</p>
                                <p class="text-[10px] font-black text-slate-400 mt-2 uppercase tracking-widest leading-relaxed">No employees have joined yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Current Payroll Period -->
            @if($currentPayrollPeriod)
                <div class="mb-6 bg-slate-900 overflow-hidden shadow-xl sm:rounded-3xl border border-slate-800">
                    <div class="p-6 text-white">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                            <div class="flex items-center gap-5">
                                <div class="p-4 bg-white/10 rounded-2xl backdrop-blur-md border border-white/10 shadow-inner">
                                    <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white/50 text-[10px] font-black uppercase tracking-widest leading-none mb-1">Current Payroll Period</p>
                                    <h3 class="text-2xl font-black tracking-tight text-white">{{ $currentPayrollPeriod->period_label }}</h3>
                                    <div class="flex items-center gap-6 mt-2">
                                        <span class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-white/70">
                                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                            Status: <span class="text-white">{{ $currentPayrollPeriod->status }}</span>
                                        </span>
                                        <span class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-white/70">
                                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Pay Date: <span class="text-white">{{ $currentPayrollPeriod->pay_date->format('M d, Y') }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('payroll.show-period', $currentPayrollPeriod) }}" class="inline-flex items-center justify-center gap-3 bg-white text-slate-900 px-8 py-4 rounded-2xl font-black uppercase text-[11px] tracking-widest hover:bg-slate-100 transition shadow-lg shrink-0">
                                <span>Manage Payroll</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div x-data="{ expanded: true }" class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-slate-100">
                <div class="p-5 border-b border-slate-100">
                    <div class="flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-50 rounded-2xl">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Quick Actions</h3>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mt-1">Frequently used administrative tasks</p>
                            </div>
                        </div>
                        <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-slate-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div x-show="expanded" x-collapse>
                    <div class="p-5">
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                            <a href="{{ route('employees.create') }}" class="group flex flex-col items-center gap-4 p-5 bg-slate-50 rounded-2xl hover:bg-blue-50 transition-all border border-transparent hover:border-blue-100">
                                <div class="p-3 bg-blue-100 rounded-xl group-hover:bg-blue-200 transition-colors shadow-sm">
                                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                </div>
                                <span class="text-[10px] font-black text-slate-700 text-center uppercase tracking-widest leading-tight">Add Employee</span>
                            </a>

                            <a href="{{ route('attendance.create') }}" class="group flex flex-col items-center gap-4 p-5 bg-slate-50 rounded-2xl hover:bg-emerald-50 transition-all border border-transparent hover:border-emerald-100">
                                <div class="p-3 bg-emerald-100 rounded-xl group-hover:bg-emerald-200 transition-colors shadow-sm">
                                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <span class="text-[10px] font-black text-slate-700 text-center uppercase tracking-widest leading-tight">Log Attendance</span>
                            </a>

                            <a href="{{ route('payroll.create-period') }}" class="group flex flex-col items-center gap-4 p-5 bg-slate-50 rounded-2xl hover:bg-indigo-50 transition-all border border-transparent hover:border-indigo-100">
                                <div class="p-3 bg-indigo-100 rounded-xl group-hover:bg-indigo-200 transition-colors shadow-sm">
                                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <span class="text-[10px] font-black text-slate-700 text-center uppercase tracking-widest leading-tight">Run Payroll</span>
                            </a>

                            <a href="{{ route('dtr.admin-index') }}" class="group flex flex-col items-center gap-4 p-5 bg-slate-50 rounded-2xl hover:bg-slate-200 transition-all border border-transparent hover:border-slate-300">
                                <div class="p-3 bg-slate-200 rounded-xl group-hover:bg-slate-300 transition-colors shadow-sm">
                                    <svg class="w-7 h-7 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <span class="text-[10px] font-black text-slate-700 text-center uppercase tracking-widest leading-tight">Gen DTR</span>
                            </a>

                            <a href="{{ route('reports.index') }}" class="group flex flex-col items-center gap-4 p-5 bg-slate-50 rounded-2xl hover:bg-amber-50 transition-all border border-transparent hover:border-amber-100">
                                <div class="p-3 bg-amber-100 rounded-xl group-hover:bg-amber-200 transition-colors shadow-sm">
                                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <span class="text-[10px] font-black text-slate-700 text-center uppercase tracking-widest leading-tight">Reports</span>
                            </a>

                            <a href="{{ route('holidays.index') }}" class="group flex flex-col items-center gap-4 p-5 bg-slate-50 rounded-2xl hover:bg-neutral-200 transition-all border border-transparent hover:border-neutral-300">
                                <div class="p-3 bg-neutral-200 rounded-xl group-hover:bg-neutral-300 transition-colors shadow-sm">
                                    <svg class="w-7 h-7 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                </div>
                                <span class="text-[10px] font-black text-slate-700 text-center uppercase tracking-widest leading-tight">Holidays</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
