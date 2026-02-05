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
                <div class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Employees</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalEmployees }}</p>
                                <p class="mt-1 text-xs text-gray-500">Active staff</p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Present Today -->
                <div class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Present</p>
                                <p class="mt-2 text-3xl font-bold text-green-600">{{ $presentToday }}</p>
                                <p class="mt-1 text-xs text-gray-500">Checked in today</p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <!-- Progress bar -->
                        <div class="mt-3">
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-green-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $totalEmployees > 0 ? ($presentToday / $totalEmployees) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- On Leave -->
                <div class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">On Leave</p>
                                <p class="mt-2 text-3xl font-bold text-blue-600">{{ $onLeaveToday }}</p>
                                <p class="mt-1 text-xs text-gray-500">Approved leaves</p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Absent -->
                <div class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Absent</p>
                                <p class="mt-2 text-3xl font-bold text-red-600">{{ $absentToday }}</p>
                                <p class="mt-1 text-xs text-gray-500">Not checked in</p>
                            </div>
                            <div class="p-3 bg-red-100 rounded-lg">
                                <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
                <!-- Pending Leave Requests - Takes 2 columns -->
                <div x-data="{ expanded: true }" class="xl:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-xl">
                    <div class="p-5 border-b border-gray-100">
                        <div class="flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-amber-200 rounded-lg">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Pending Leave Requests</h3>
                                    <p class="text-sm text-gray-500">{{ $pendingLeaveRequests->count() }} requests awaiting approval</p>
                                </div>
                            </div>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <div x-show="expanded" x-collapse>
                        @if($pendingLeaveRequests->count() > 0)
                            <div class="divide-y divide-gray-100">
                                @foreach($pendingLeaveRequests as $request)
                                    <div class="p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-medium">
                                                    {{ substr($request->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $request->user->name }}</p>
                                                    <p class="text-sm text-gray-500">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $request->leaveType->name }}
                                                        </span>
                                                        <span class="ml-2">{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                            <a href="{{ route('leaves.admin-show', $request) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition text-sm font-medium">
                                                Review
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="p-4 bg-gray-50 border-t border-gray-100">
                                <a href="{{ route('leaves.manage') }}" class="flex items-center justify-center gap-2 text-indigo-600 hover:text-indigo-700 text-sm font-medium transition">
                                    View all leave requests
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </a>
                            </div>
                        @else
                            <div class="p-8 text-center">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p class="text-gray-500 font-medium">All caught up!</p>
                                <p class="text-sm text-gray-400 mt-1">No pending leave requests to review.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Today's Attendance Summary -->
                <div x-data="{ expanded: true }" class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
                    <div class="p-5 border-b border-gray-100">
                        <div class="flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Today's Attendance</h3>
                                    <p class="text-sm text-gray-500">{{ $recentAttendances->count() }} check-ins</p>
                                </div>
                            </div>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <div x-show="expanded" x-collapse>
                        @if($recentAttendances->count() > 0)
                            <div class="max-h-80 overflow-y-auto">
                                <div class="divide-y divide-gray-100">
                                    @foreach($recentAttendances as $attendance)
                                        <div class="p-3 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center text-white text-sm font-medium flex-shrink-0">
                                                    {{ substr($attendance->user->name, 0, 1) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $attendance->user->name }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        In: {{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }}
                                                        @if($attendance->time_out)
                                                            • Out: {{ $attendance->time_out->format('h:i A') }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <span class="px-2 py-1 text-xs rounded-full font-medium
                                                    @if($attendance->status == 'present') bg-green-100 text-green-700
                                                    @elseif($attendance->status == 'late') bg-yellow-100 text-yellow-700
                                                    @else bg-gray-100 text-gray-700 @endif">
                                                    {{ ucfirst($attendance->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 border-t border-gray-100">
                                <a href="{{ route('attendance.manage') }}" class="flex items-center justify-center gap-2 text-indigo-600 hover:text-indigo-700 text-sm font-medium transition">
                                    View all records
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </a>
                            </div>
                        @else
                            <div class="p-8 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-500 font-medium">No attendance yet</p>
                                <p class="text-sm text-gray-400 mt-1">Employees haven't checked in today.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Current Payroll Period -->
            @if($currentPayrollPeriod)
                <div class="mb-6 bg-gradient-to-r from-blue-600 via-slate-600 to-gray-700 overflow-hidden shadow-lg sm:rounded-xl">
                    <div class="p-6 text-white">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white/80 text-sm font-medium">Current Payroll Period</p>
                                    <h3 class="text-xl font-bold">{{ $currentPayrollPeriod->period_label }}</h3>
                                    <div class="flex items-center gap-4 mt-1 text-sm text-white/80">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Status: <span class="capitalize font-medium text-white">{{ $currentPayrollPeriod->status }}</span>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Pay Date: {{ $currentPayrollPeriod->pay_date->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('payroll.show-period', $currentPayrollPeriod) }}" class="inline-flex items-center justify-center gap-2 bg-white text-indigo-600 px-6 py-3 rounded-xl font-semibold hover:bg-gray-100 transition shadow-lg">
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
            <div x-data="{ expanded: true }" class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-5 border-b border-gray-100">
                    <div class="flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-100 rounded-lg">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                                <p class="text-sm text-gray-500">Frequently used actions</p>
                            </div>
                        </div>
                        <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div x-show="expanded" x-collapse>
                    <div class="p-5">
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                            <a href="{{ route('employees.create') }}" class="group flex flex-col items-center gap-3 p-4 bg-gray-50 rounded-lg hover:bg-blue-100 transition-all">
                                <div class="p-3 bg-blue-200 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 text-center">Add Employee</span>
                            </a>

                            <a href="{{ route('attendance.create') }}" class="group flex flex-col items-center gap-3 p-4 bg-gray-50 rounded-lg hover:bg-green-100 transition-all">
                                <div class="p-3 bg-green-200 rounded-lg">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 text-center">Manual Attendance</span>
                            </a>

                            <a href="{{ route('payroll.create-period') }}" class="group flex flex-col items-center gap-3 p-4 bg-gray-50 rounded-lg hover:bg-indigo-100 transition-all">
                                <div class="p-3 bg-indigo-200 rounded-lg">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 text-center">Create Payroll</span>
                            </a>

                            <a href="{{ route('dtr.admin-index') }}" class="group flex flex-col items-center gap-3 p-4 bg-gray-50 rounded-lg hover:bg-slate-100 transition-all">
                                <div class="p-3 bg-slate-200 rounded-lg">
                                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 text-center">Generate DTR</span>
                            </a>

                            <a href="{{ route('reports.index') }}" class="group flex flex-col items-center gap-3 p-4 bg-gray-50 rounded-xl hover:bg-amber-100 transition-all">
                                <div class="p-3 bg-amber-200 rounded-xl group-hover:bg-amber-300 transition-colors">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 text-center">View Reports</span>
                            </a>

                            <a href="{{ route('holidays.index') }}" class="group flex flex-col items-center gap-3 p-4 bg-gray-50 rounded-lg hover:bg-neutral-100 transition-all">
                                <div class="p-3 bg-neutral-200 rounded-lg">
                                    <svg class="w-6 h-6 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 text-center">Manage Holidays</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
