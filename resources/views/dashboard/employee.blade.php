<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Today's Attendance Status -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Today's Attendance</h3>
                    @if($todayAttendance)
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Time In</div>
                                <div class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ $todayAttendance->time_in ? $todayAttendance->time_in->format('h:i A') : '-' }}
                                </div>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Time Out</div>
                                <div class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ $todayAttendance->time_out ? $todayAttendance->time_out->format('h:i A') : '-' }}
                                </div>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Status</div>
                                <div class="mt-1">
                                    <span class="px-2 py-1 text-sm rounded-full 
                                        @if($todayAttendance->status == 'present') bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300
                                        @elseif($todayAttendance->status == 'late') bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300
                                        @else bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-300 @endif">
                                        {{ ucfirst($todayAttendance->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Work Time</div>
                                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $todayAttendance->formatted_work_time }}</div>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">You haven't timed in today.</p>
                    @endif
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('attendance.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium">
                            Go to Attendance →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Monthly Summary -->
            <div x-data="{ expanded: true }" class="mb-6">
                <div class="flex justify-between items-center mb-4 cursor-pointer" @click="expanded = !expanded">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Monthly Summary</h3>
                    <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                <div x-show="expanded" x-collapse class="grid grid-cols-2 md:grid-cols-4 gap-4">
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
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $daysPresent }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900/50">
                                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Late</div>
                                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $daysLate }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-red-100 dark:bg-red-900/50">
                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Absent</div>
                                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $daysAbsent }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/50">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hours</div>
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalWorkHours }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Leave Balances -->
                <div x-data="{ expanded: true }" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Leave Balances ({{ date('Y') }})
                                <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">({{ $leaveBalances->count() }})</span>
                            </h3>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div x-show="expanded" x-collapse>
                            @if($leaveBalances->count() > 0)
                                <div class="mt-4 space-y-3 max-h-64 overflow-y-auto">
                                    @foreach($leaveBalances as $balance)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">{{ $balance->leaveType->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    Used: {{ $balance->used_days }} / {{ $balance->allocated_days }} days
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $balance->remaining_days }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">remaining</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-4 text-gray-500 dark:text-gray-400">No leave balances found.</p>
                            @endif
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('leaves.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium">
                                    Request Leave →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Leave Requests -->
                <div x-data="{ expanded: true }" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Recent Leave Requests
                                <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">({{ $recentLeaveRequests->count() }})</span>
                            </h3>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div x-show="expanded" x-collapse>
                            @if($recentLeaveRequests->count() > 0)
                                <div class="mt-4 space-y-3 max-h-64 overflow-y-auto">
                                    @foreach($recentLeaveRequests as $request)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">{{ $request->leaveType->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($request->status == 'approved') bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300
                                                @elseif($request->status == 'pending') bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300
                                                @else bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300 @endif">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-4 text-gray-500 dark:text-gray-400">No leave requests.</p>
                            @endif
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('leaves.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium">
                                    View all leave requests →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
