<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Today's Attendance Status -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 sm:p-6">
                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-6">Today's Attendance</h3>
                    @if($todayAttendance)
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm transition-all hover:shadow-md">
                                <div class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">Time In</div>
                                <div class="text-2xl font-black text-slate-900 tracking-tight">
                                    {{ $todayAttendance->time_in ? $todayAttendance->time_in->format('h:i A') : '-' }}
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm transition-all hover:shadow-md">
                                <div class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">Time Out</div>
                                <div class="text-2xl font-black text-slate-900 tracking-tight">
                                    {{ $todayAttendance->time_out ? $todayAttendance->time_out->format('h:i A') : '-' }}
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm transition-all hover:shadow-md">
                                <div class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">Status</div>
                                <div class="mt-1">
                                    <span class="px-3 py-1 text-xs font-black uppercase tracking-wider rounded-full 
                                        @if($todayAttendance->status == 'present') bg-emerald-100 text-emerald-800
                                        @elseif($todayAttendance->status == 'late') bg-amber-100 text-amber-800
                                        @else bg-slate-100 text-slate-800 @endif">
                                        {{ ucfirst($todayAttendance->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm transition-all hover:shadow-md">
                                <div class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">Work Time</div>
                                <div class="text-2xl font-black text-slate-900 tracking-tight">{{ $todayAttendance->formatted_work_time }}</div>
                            </div>
                        </div>
                    @else
                        <div class="p-8 bg-slate-50 rounded-2xl border border-dashed border-slate-300 text-center">
                            <p class="text-slate-500 font-bold uppercase tracking-wide">You haven't timed in today.</p>
                        </div>
                    @endif
                    <div class="mt-6 pt-6 border-t border-slate-100 flex justify-end">
                        <a href="{{ route('attendance.index') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                            Go to Attendance Center
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Monthly Summary -->
            <div x-data="{ expanded: true }" class="mb-8">
                <div class="flex justify-between items-center mb-6 cursor-pointer group" @click="expanded = !expanded">
                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="w-2 h-8 bg-emerald-500 rounded-full"></span>
                        Monthly Summary
                    </h3>
                    <svg :class="{ 'rotate-180': expanded }" class="w-6 h-6 text-slate-400 transform transition-transform group-hover:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                <div x-show="expanded" x-collapse class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Present Card -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="flex items-center gap-4">
                            <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Present</div>
                                <div class="text-3xl font-black text-emerald-600 tracking-tighter">{{ $daysPresent }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Late Card -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="flex items-center gap-4">
                            <div class="p-4 rounded-2xl bg-amber-50 text-amber-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Late</div>
                                <div class="text-3xl font-black text-amber-600 tracking-tighter">{{ $daysLate }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Absent Card -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="flex items-center gap-4">
                            <div class="p-4 rounded-2xl bg-rose-50 text-rose-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Absent</div>
                                <div class="text-3xl font-black text-rose-600 tracking-tighter">{{ $daysAbsent }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Hours Card -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="flex items-center gap-4">
                            <div class="p-4 rounded-2xl bg-blue-50 text-blue-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Work Hours</div>
                                <div class="text-3xl font-black text-blue-600 tracking-tighter">{{ $totalWorkHours }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Leave Balances -->
                <div x-data="{ expanded: true }" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center cursor-pointer group" @click="expanded = !expanded">
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">
                                Leave Balances
                                <span class="ml-2 text-xs font-bold text-slate-400">({{ $leaveBalances->count() }})</span>
                            </h3>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-slate-400 transform transition-transform group-hover:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div x-show="expanded" x-collapse>
                            @if($leaveBalances->count() > 0)
                                <div class="mt-6 space-y-4 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                                    @foreach($leaveBalances as $balance)
                                        <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-slate-100 group/item hover:bg-indigo-50/50 transition-colors">
                                            <div>
                                                <div class="text-sm font-black text-slate-800 uppercase tracking-wide group-hover/item:text-indigo-600 transition-colors">{{ $balance->leaveType->name }}</div>
                                                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-tight mt-0.5">
                                                    Used: {{ $balance->used_days }} / {{ $balance->allocated_days }} days
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-2xl font-black text-indigo-600 tracking-tighter">{{ $balance->remaining_days }}</div>
                                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Days Left</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-6 p-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-center">
                                    <p class="text-xs font-bold text-slate-500 uppercase">No leave balances found.</p>
                                </div>
                            @endif
                            <div class="mt-6 pt-6 border-t border-slate-100">
                                <a href="{{ route('leaves.create') }}" class="flex items-center justify-center w-full py-3 bg-indigo-50 text-indigo-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600 hover:text-white transition-all">
                                    Request Leave
                                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Leave Requests -->
                <div x-data="{ expanded: true }" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center cursor-pointer group" @click="expanded = !expanded">
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">
                                Leave Activity
                                <span class="ml-2 text-xs font-bold text-slate-400">({{ $recentLeaveRequests->count() }})</span>
                            </h3>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-slate-400 transform transition-transform group-hover:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div x-show="expanded" x-collapse>
                            @if($recentLeaveRequests->count() > 0)
                                <div class="mt-6 space-y-4 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                                    @foreach($recentLeaveRequests as $request)
                                        <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-slate-100 hover:bg-slate-100 transition-colors">
                                            <div>
                                                <div class="text-sm font-black text-slate-800 uppercase tracking-wide">{{ $request->leaveType->name }}</div>
                                                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-tight mt-0.5">
                                                    {{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}
                                                </div>
                                            </div>
                                            <span class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-xl 
                                                @if($request->status == 'approved') bg-emerald-100 text-emerald-800
                                                @elseif($request->status == 'pending') bg-amber-100 text-amber-800
                                                @else bg-rose-100 text-rose-800 @endif">
                                                {{ $request->status }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-6 p-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-center">
                                    <p class="text-xs font-bold text-slate-500 uppercase">No leave activity.</p>
                                </div>
                            @endif
                            <div class="mt-6 pt-6 border-t border-slate-100">
                                <a href="{{ route('leaves.index') }}" class="flex items-center justify-center w-full py-3 bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-900 hover:text-white transition-all">
                                    View All History
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Payslips -->
                <div x-data="{ expanded: true }" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center cursor-pointer group" @click="expanded = !expanded">
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">
                                Recent Payslips
                                <span class="ml-2 text-xs font-bold text-slate-400">({{ $recentPayslips->count() }})</span>
                            </h3>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-slate-400 transform transition-transform group-hover:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div x-show="expanded" x-collapse>
                            @if($recentPayslips->count() > 0)
                                <div class="mt-6 space-y-4 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                                    @foreach($recentPayslips as $payroll)
                                        <div class="flex justify-between items-center p-4 bg-slate-900 text-white rounded-2xl shadow-lg shadow-slate-200">
                                            <div>
                                                <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Period</div>
                                                <div class="text-xs font-black uppercase tracking-tight">
                                                    {{ $payroll->payrollPeriod->start_date->format('M d') }} - {{ $payroll->payrollPeriod->end_date->format('M d, Y') }}
                                                </div>
                                                <div class="text-xl font-black text-emerald-400 tracking-tighter mt-1">
                                                    ₱{{ number_format($payroll->net_pay, 2) }}
                                                </div>
                                            </div>
                                            <a href="{{ route('payslip.show', $payroll) }}" class="p-3 bg-white/10 rounded-xl hover:bg-white/20 transition-all">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-6 p-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-center">
                                    <p class="text-xs font-bold text-slate-500 uppercase">No payslips posted.</p>
                                </div>
                            @endif
                            <div class="mt-6 pt-6 border-t border-slate-100">
                                <a href="{{ route('payslip.index') }}" class="flex items-center justify-center w-full py-3 bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-900 hover:text-white transition-all">
                                    Payroll Center
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Updates & Assets Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Notifications & Requests -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Action Items & Updates</h3>
                        
                        <!-- Pending Review -->
                        @if(isset($pendingAcknowledgementReview) && $pendingAcknowledgementReview)
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4 rounded-r-md">
                                <div class="flex justify-between items-start">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm leading-5 font-medium text-yellow-800">
                                                Performance Review Pending
                                            </h3>
                                            <div class="mt-2 text-sm leading-5 text-yellow-700">
                                                <p>You have a performance review waiting for your acknowledgement.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ route('performance-reviews.show', $pendingAcknowledgementReview) }}" class="text-sm font-medium text-yellow-800 hover:text-yellow-600 underline">
                                        View
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Latest Policy -->
                        @if(isset($latestPolicy) && $latestPolicy)
                            <div class="mb-4 p-4 border rounded-lg bg-blue-50 border-blue-100 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wide">New Policy</span>
                                    <h4 class="font-medium text-gray-900">{{ $latestPolicy->title }}</h4>
                                    <p class="text-xs text-gray-500">{{ $latestPolicy->effective_date->format('M d, Y') }}</p>
                                </div>
                                <a href="{{ route('hr-policies.show', $latestPolicy) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Read</a>
                            </div>
                        @endif

                        <!-- Shift Change Requests -->
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2 mt-4">Recent Shift Requests</h4>
                        @if(isset($myShiftRequests) && $myShiftRequests->count() > 0)
                            <div class="space-y-3">
                                @foreach($myShiftRequests as $req)
                                    <div class="flex justify-between items-center text-sm">
                                        <div>
                                            <span class="font-medium text-gray-700">{{ $req->requested_date->format('M d') }}</span>
                                            <span class="text-gray-500 mx-1">&rarr;</span>
                                            <span class="text-gray-600">{{ $req->new_schedule }}</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $req->status == 'approved' ? 'bg-green-100 text-green-800' : ($req->status == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($req->status) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-400 italic">No recent requests.</p>
                        @endif
                         <div class="mt-3 pt-3 border-t border-gray-100">
                            <a href="{{ route('shift-change-requests.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Manage Shift Requests &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- My Assets -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">My Assigned Assets</h3>
                            <span class="bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs font-bold">{{ isset($myAssets) ? $myAssets->count() : 0 }}</span>
                        </div>
                        
                        @if(isset($myAssets) && $myAssets->count() > 0)
                            <div class="space-y-4 max-h-64 overflow-y-auto pr-1">
                                @foreach($myAssets as $asset)
                                    <div class="flex items-start p-3 border rounded-lg hover:bg-gray-50 transition border-gray-200">
                                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-md mr-3">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-900">{{ $asset->asset_name }}</h4>
                                            <p class="text-xs text-gray-500">{{ $asset->type }} • {{ $asset->serial_number ?? 'No Serial' }}</p>
                                            <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-50 text-green-700 border border-green-100">
                                                Active
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                             <div class="text-center py-8 text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p>No assets assigned.</p>
                            </div>
                        @endif
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <a href="{{ route('company-assets.index') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">View Asset Details →</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div x-data="{ expanded: true }" class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div x-show="expanded" x-collapse>
                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <a href="{{ route('attendance.index') }}" class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg transition text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Attendance
                            </a>
                            <a href="{{ route('dtr.index') }}" class="flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg transition text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                My DTR
                            </a>
                            <a href="{{ route('leaves.create') }}" class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg transition text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Request Leave
                            </a>
                            <a href="{{ route('payroll.my-payslips') }}" class="flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg transition text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                My Payslips
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
