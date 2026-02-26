<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-slate-800">
            Employee <span class="text-blue-600">Dashboard</span>
        </h2>
    </x-slot>

    <div class="space-y-10">
        <div class="max-w-7xl mx-auto space-y-10">
            {{-- Personal Overview Header --}}
            <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-10 rounded-[2.5rem] shadow-sm relative overflow-hidden group">
                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
                    <div class="flex flex-col md:flex-row items-center gap-8 flex-1">
                        {{-- Hub Identity --}}
                        <div class="text-center md:text-left">
                            <h3 class="text-4xl font-bold text-slate-900 mb-3">Welcome, {{ auth()->user()->name }}</h3>
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-center md:justify-start text-slate-400">
                                    <span class="text-sm font-medium">Your summary and activities</span>
                                </div>
                                <div class="flex items-center justify-center md:justify-start text-slate-400">
                                    <span class="text-sm font-medium">{{ now()->format('l, F j, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Stats Badges --}}
                    <div class="flex flex-wrap justify-center items-center gap-4 bg-slate-50/50 p-6 rounded-[2rem] border border-slate-100">
                        <div class="flex flex-col items-center px-4 border-r border-slate-200">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Houred Today</span>
                            <span class="text-lg font-bold text-slate-900">{{ $todayAttendance ? $todayAttendance->formatted_work_time : '0.00' }}</span>
                        </div>
                        <div class="flex flex-col items-center px-4 border-r border-slate-200">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Status</span>
                            <span class="px-3 py-1 bg-{{ ($todayAttendance && ($todayAttendance->status == 'present' || $todayAttendance->status == 'late')) ? 'emerald' : 'slate' }}-100 text-{{ ($todayAttendance && ($todayAttendance->status == 'present' || $todayAttendance->status == 'late')) ? 'emerald' : 'slate' }}-600 text-[10px] font-bold uppercase rounded-lg">
                                {{ $todayAttendance ? ucfirst($todayAttendance->status) : 'Offline' }}
                            </span>
                        </div>
                        <div class="flex flex-col items-center px-4">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Attendance Rate</span>
                            <span class="text-lg font-bold text-blue-600">{{ number_format(($daysPresent / max(1, $daysPresent + $daysLate + $daysAbsent)) * 100, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                {{-- Main Content - 8 Cols --}}
                <div class="lg:col-span-8 space-y-10">
                    <!-- Today's Attendance -->
                    <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-10 rounded-[2.5rem] shadow-sm">
                        <div class="flex justify-between items-center mb-8 pb-6 border-b border-slate-100">
                            <h3 class="text-2xl font-bold text-slate-900">Today's <span class="text-blue-600">Attendance</span></h3>
                            <a href="{{ route('attendance.index') }}" class="text-xs font-bold text-slate-400 hover:text-blue-600 transition-all">Full History &rarr;</a>
                        </div>

                        @if($todayAttendance)
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                                <div class="space-y-1 border-r border-slate-100 lg:pr-6">
                                    <span class="text-xs font-medium text-slate-400">Login</span>
                                    <p class="text-2xl font-bold text-slate-900">{{ $todayAttendance->time_in ? $todayAttendance->time_in->format('h:i A') : '--:--' }}</p>
                                </div>
                                <div class="space-y-1 lg:border-r border-slate-100 lg:px-6">
                                    <span class="text-xs font-medium text-slate-400">Logout</span>
                                    <p class="text-2xl font-bold text-slate-900">{{ $todayAttendance->time_out ? $todayAttendance->time_out->format('h:i A') : '--:--' }}</p>
                                </div>
                                <div class="space-y-1 border-r border-slate-100 pr-6 pl-6 hidden lg:block">
                                    <span class="text-xs font-medium text-slate-400">Breaks</span>
                                    <p class="text-2xl font-bold text-slate-900">{{ $todayAttendance->breaks->count() }}</p>
                                </div>
                                <div class="space-y-1 lg:pl-6 pl-6">
                                    <span class="text-xs font-medium text-slate-400">Total Work</span>
                                    <p class="text-2xl font-bold text-blue-600">{{ $todayAttendance->formatted_work_time }}</p>
                                </div>
                            </div>
                        @else
                            <div class="py-12 flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-3xl flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <p class="text-sm font-bold text-slate-400 uppercase">No Activity Recorded</p>
                                <p class="text-sm font-medium text-slate-500 mt-2 max-w-xs">Attendance will show here once you clock in.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Summary Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-6 rounded-3xl shadow-sm hover:translate-y-[-4px] transition-all duration-300">
                            <span class="text-xs font-medium text-slate-400">Present</span>
                            <p class="text-4xl font-bold text-emerald-500 mt-1">{{ $daysPresent }}</p>
                        </div>
                        <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-6 rounded-3xl shadow-sm hover:translate-y-[-4px] transition-all duration-300">
                            <span class="text-xs font-medium text-slate-400">Late</span>
                            <p class="text-4xl font-bold text-amber-500 mt-1">{{ $daysLate }}</p>
                        </div>
                        <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-6 rounded-3xl shadow-sm hover:translate-y-[-4px] transition-all duration-300">
                            <span class="text-xs font-medium text-slate-400">Absent</span>
                            <p class="text-4xl font-bold text-rose-500 mt-1">{{ $daysAbsent }}</p>
                        </div>
                        <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-6 rounded-3xl shadow-sm hover:translate-y-[-4px] transition-all duration-300">
                            <span class="text-xs font-medium text-slate-400">Leaves</span>
                            <p class="text-4xl font-bold text-blue-500 mt-1">{{ $approvedLeaves }}</p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-10 rounded-[2.5rem] shadow-sm relative overflow-hidden group">
                        <div class="relative z-10">
                            <div class="mb-8">
                                <h3 class="text-2xl font-bold text-slate-900 mb-2">Quick <span class="text-blue-600 italic font-medium">Actions</span></h3>
                                <p class="text-sm font-medium text-slate-500">Frequently used features</p>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <a href="{{ route('attendance.index') }}" class="flex flex-col items-center justify-center p-6 bg-white/50 border border-slate-100 rounded-2xl hover:bg-blue-50 hover:border-blue-200 transition-all group/btn">
                                    <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center mb-3 shadow-lg shadow-blue-500/20 group-hover/btn:scale-110 transition-transform">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 group-hover/btn:text-blue-600 transition-colors">Attendance</span>
                                </a>
                                <a href="{{ route('leaves.index') }}" class="flex flex-col items-center justify-center p-6 bg-white/50 border border-slate-100 rounded-2xl hover:bg-amber-50 hover:border-amber-200 transition-all group/btn">
                                    <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center mb-3 shadow-lg shadow-amber-500/20 group-hover/btn:scale-110 transition-transform">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 group-hover/btn:text-amber-600 transition-colors">Leaves</span>
                                </a>
                                <a href="{{ route('overtime-requests.index') }}" class="flex flex-col items-center justify-center p-6 bg-white/50 border border-slate-100 rounded-2xl hover:bg-emerald-50 hover:border-emerald-200 transition-all group/btn">
                                    <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center mb-3 shadow-lg shadow-emerald-500/20 group-hover/btn:scale-110 transition-transform">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 group-hover/btn:text-emerald-600 transition-colors">Overtime</span>
                                </a>
                                <a href="{{ route('payslip.index') }}" class="flex flex-col items-center justify-center p-6 bg-white/50 border border-slate-100 rounded-2xl hover:bg-rose-50 hover:border-rose-200 transition-all group/btn">
                                    <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center mb-3 shadow-lg shadow-rose-500/20 group-hover/btn:scale-110 transition-transform">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 group-hover/btn:text-rose-600 transition-colors">Payslip</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Side Section - 4 Cols --}}
                <div class="lg:col-span-4 space-y-10">
                    <!-- Announcements Side Bar -->
                    <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-10 rounded-[2.5rem] shadow-sm">
                        <div class="flex justify-between items-center mb-10 pb-6 border-b border-slate-100">
                            <h3 class="text-2xl font-bold text-slate-900">Announcements</h3>
                            <a href="{{ route('announcements.index') }}" class="text-xs font-bold text-slate-400 hover:text-blue-600 transition-all">View All &rarr;</a>
                        </div>
                        
                        @if(isset($announcements) && $announcements->count() > 0)
                            <div class="space-y-8">
                                @foreach($announcements->take(3) as $announcement)
                                    <div class="group/item">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-bold uppercase rounded border border-blue-100">
                                                {{ $announcement->category ?? 'General' }}
                                            </span>
                                            <span class="text-[10px] font-medium text-slate-400">
                                                {{ $announcement->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-900 group-hover/item:text-blue-600 transition-colors">{{ $announcement->title }}</h4>
                                        <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">{{ Str::limit(strip_tags($announcement->content), 80) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-12 text-center">
                                <p class="text-sm font-medium text-slate-400">No announcements yet.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Alerts / Updates (Standardized Action Items) -->
                    @if((isset($pendingAcknowledgementReview) && $pendingAcknowledgementReview) || (isset($latestPolicy) && $latestPolicy))
                        <div class="bg-rose-50/40 backdrop-blur-xl border border-rose-100/60 p-10 rounded-[2.5rem] shadow-sm">
                            <div class="flex items-center gap-3 mb-8">
                                <div class="w-2 h-6 bg-rose-500 rounded-full"></div>
                                <h3 class="text-xl font-bold text-slate-900">Notifications</h3>
                            </div>

                            <div class="space-y-6">
                                {{-- Pending Review --}}
                                @if(isset($pendingAcknowledgementReview) && $pendingAcknowledgementReview)
                                    <a href="{{ route('performance-reviews.show', $pendingAcknowledgementReview) }}" class="block group/alert">
                                        <div class="p-5 bg-white rounded-2xl border border-rose-100 shadow-sm group-hover/alert:border-rose-300 group-hover/alert:shadow-md transition-all">
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></div>
                                                <span class="text-[10px] font-bold text-rose-600 uppercase">Action Required</span>
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-900">Performance Review</h4>
                                            <p class="text-xs text-slate-500 mt-1">Please acknowledge your review</p>
                                        </div>
                                    </a>
                                @endif

                                {{-- Latest Policy --}}
                                @if(isset($latestPolicy) && $latestPolicy)
                                    <a href="{{ route('hr-policies.show', $latestPolicy) }}" class="block group/alert">
                                        <div class="p-5 bg-white rounded-2xl border border-blue-100 shadow-sm group-hover/alert:border-blue-300 group-hover/alert:shadow-md transition-all">
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                                                <span class="text-[10px] font-bold text-blue-600 uppercase">New Policy</span>
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-900">{{ $latestPolicy->title }}</h4>
                                            <p class="text-xs text-slate-500 mt-1">Effective {{ $latestPolicy->effective_date->format('M d') }}</p>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Extended Tables Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <!-- Leave Balances -->
                <div x-data="{ expanded: true }" class="bg-white/40 backdrop-blur-xl border border-white/60 rounded-[2.5rem] shadow-sm overflow-hidden">
                    <div class="p-8">
                        <div class="flex justify-between items-center cursor-pointer group" @click="expanded = !expanded">
                            <h3 class="text-xl font-bold text-slate-900">
                                Leave <span class="text-blue-600">Balances</span>
                            </h3>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-slate-400 transform transition-transform group-hover:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div x-show="expanded" x-collapse>
                            @if($leaveBalances->count() > 0)
                                <div class="mt-8 space-y-4 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                                    @foreach($leaveBalances as $balance)
                                        <div class="flex justify-between items-center p-5 bg-white/50 rounded-2xl border border-slate-100 group/item hover:bg-blue-50/50 transition-colors">
                                            <div>
                                                <div class="text-[11px] font-bold text-slate-900 uppercase group-hover/item:text-blue-600 transition-colors">{{ $balance->leaveType->name }}</div>
                                                <div class="text-[10px] font-medium text-slate-400 mt-1">
                                                    Used: {{ $balance->used_days }} / {{ $balance->allocated_days }} days
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-2xl font-bold text-blue-600 leading-none">{{ $balance->remaining_days }}</div>
                                                <div class="text-[9px] font-bold text-slate-400 uppercase mt-1">Days Left</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-8 p-8 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 text-center">
                                    <p class="text-sm font-medium text-slate-400">No records found.</p>
                                </div>
                            @endif
                            <div class="mt-8 pt-6 border-t border-slate-100">
                                <a href="{{ route('leaves.create') }}" class="flex items-center justify-center w-full py-4 bg-blue-600 text-white text-xs font-bold uppercase tracking-wider rounded-2xl hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all">
                                    Request Leave
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Leave Requests -->
                <div x-data="{ expanded: true }" class="bg-white/40 backdrop-blur-xl border border-white/60 rounded-[2.5rem] shadow-sm overflow-hidden">
                    <div class="p-8">
                        <div class="flex justify-between items-center cursor-pointer group" @click="expanded = !expanded">
                            <h3 class="text-xl font-bold text-slate-900">
                                Recent <span class="text-amber-600">Leaves</span>
                            </h3>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-slate-400 transform transition-transform group-hover:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div x-show="expanded" x-collapse>
                            @if($recentLeaveRequests->count() > 0)
                                <div class="mt-8 space-y-4 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                                    @foreach($recentLeaveRequests as $request)
                                        <div class="flex justify-between items-center p-5 bg-white/50 rounded-2xl border border-slate-100 hover:bg-amber-50/50 transition-colors">
                                            <div>
                                                <div class="text-[11px] font-bold text-slate-900 uppercase">{{ $request->leaveType->name }}</div>
                                                <div class="text-[10px] font-medium text-slate-400 mt-1">
                                                    {{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-lg 
                                                @if($request->status == 'approved') bg-emerald-100 text-emerald-600
                                                @elseif($request->status == 'pending') bg-amber-100 text-amber-600
                                                @else bg-rose-100 text-rose-600 @endif">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-8 p-8 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 text-center">
                                    <p class="text-sm font-medium text-slate-400">No recent leaves.</p>
                                </div>
                            @endif
                            <div class="mt-8 pt-6 border-t border-slate-100">
                                <a href="{{ route('leaves.index') }}" class="flex items-center justify-center w-full py-4 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-2xl hover:bg-slate-800 transition-all">
                                    View History
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Payslips -->
                <div x-data="{ expanded: true }" class="bg-white/40 backdrop-blur-xl border border-white/60 rounded-[2.5rem] shadow-sm overflow-hidden">
                    <div class="p-8">
                        <div class="flex justify-between items-center cursor-pointer group" @click="expanded = !expanded">
                            <h3 class="text-xl font-bold text-slate-900">
                                Recent <span class="text-rose-600">Payslips</span>
                            </h3>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-slate-400 transform transition-transform group-hover:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div x-show="expanded" x-collapse>
                            @if($recentPayslips->count() > 0)
                                <div class="mt-8 space-y-4 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                                    @foreach($recentPayslips as $payroll)
                                        <div class="flex justify-between items-center p-5 bg-white/50 rounded-2xl border border-slate-100 group/payslip hover:bg-rose-50 hover:border-rose-200 transition-all">
                                            <div>
                                                <div class="text-[9px] font-bold text-slate-400 uppercase mb-1">Period</div>
                                                <div class="text-[10px] font-bold text-slate-700">
                                                    {{ $payroll->payrollPeriod->start_date->format('M d') }} - {{ $payroll->payrollPeriod->end_date->format('M d, Y') }}
                                                </div>
                                                <div class="text-xl font-bold text-emerald-600 mt-1">
                                                    ₱{{ number_format($payroll->net_pay, 2) }}
                                                </div>
                                            </div>
                                            <a href="{{ route('payslip.show', $payroll) }}" class="p-3 bg-slate-100 rounded-xl hover:bg-rose-500 hover:text-white transition-all group/btn">
                                                <svg class="w-5 h-5 text-slate-400 group-hover/btn:text-white group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-8 p-8 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 text-center">
                                    <p class="text-sm font-medium text-slate-400">No payslips found.</p>
                                </div>
                            @endif
                            <div class="mt-8 pt-6 border-t border-slate-100">
                                <a href="{{ route('payslip.index') }}" class="flex items-center justify-center w-full py-4 bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider rounded-2xl hover:bg-slate-900 hover:text-white transition-all">
                                    Payroll Center
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

