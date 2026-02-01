<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900/50">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Employees</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalEmployees }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/50">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Present</div>
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $presentToday }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/50">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">On Leave</div>
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $onLeaveToday }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 dark:bg-red-900/50">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Absent</div>
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $absentToday }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Pending Leave Requests -->
                <div x-data="{ expanded: true }" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Pending Leave Requests
                                <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">({{ $pendingLeaveRequests->count() }})</span>
                            </h3>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div x-show="expanded" x-collapse>
                            @if($pendingLeaveRequests->count() > 0)
                                <div class="mt-4 space-y-3 max-h-64 overflow-y-auto">
                                    @foreach($pendingLeaveRequests as $request)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">{{ $request->user->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $request->leaveType->name }} - {{ $request->start_date->format('M d') }} to {{ $request->end_date->format('M d') }}
                                                </div>
                                            </div>
                                            <a href="{{ route('leaves.admin-show', $request) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium">
                                                View
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <a href="{{ route('leaves.manage') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium">
                                        View all leave requests →
                                    </a>
                                </div>
                            @else
                                <p class="mt-4 text-gray-500 dark:text-gray-400">No pending leave requests.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Attendance -->
                <div x-data="{ expanded: true }" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Today's Attendance
                                <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">({{ $recentAttendances->count() }})</span>
                            </h3>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div x-show="expanded" x-collapse>
                            @if($recentAttendances->count() > 0)
                                <div class="mt-4 space-y-3 max-h-64 overflow-y-auto">
                                    @foreach($recentAttendances as $attendance)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">{{ $attendance->user->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    In: {{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }}
                                                    @if($attendance->time_out)
                                                        | Out: {{ $attendance->time_out->format('h:i A') }}
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($attendance->status == 'present') bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300
                                                @elseif($attendance->status == 'late') bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300
                                                @else bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-300 @endif">
                                                {{ ucfirst($attendance->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <a href="{{ route('attendance.manage') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium">
                                        View all attendance records →
                                    </a>
                                </div>
                            @else
                                <p class="mt-4 text-gray-500 dark:text-gray-400">No attendance records for today.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Payroll Period -->
            @if($currentPayrollPeriod)
                <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Current Payroll Period</h3>
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $currentPayrollPeriod->period_label }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Status: <span class="font-medium capitalize">{{ $currentPayrollPeriod->status }}</span>
                                    | Pay Date: {{ $currentPayrollPeriod->pay_date->format('M d, Y') }}
                                </div>
                            </div>
                            <a href="{{ route('payroll.show-period', $currentPayrollPeriod) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md transition">
                                View Period
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div x-data="{ expanded: true }" class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                        <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div x-show="expanded" x-collapse>
                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <a href="{{ route('employees.create') }}" class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg transition text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span class="hidden sm:inline">Add Employee</span>
                            </a>
                            <a href="{{ route('attendance.create') }}" class="flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg transition text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="hidden sm:inline">Manual Attendance</span>
                            </a>
                            <a href="{{ route('payroll.create-period') }}" class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg transition text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="hidden sm:inline">Create Payroll</span>
                            </a>
                            <a href="{{ route('dtr.admin-index') }}" class="flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg transition text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="hidden sm:inline">Generate DTR</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
