<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance Management') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Today's Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Total Employees</div>
                    <div class="text-2xl font-bold text-indigo-600">{{ $stats['total_employees'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Present Today</div>
                    <div class="text-2xl font-bold text-green-600">{{ $stats['present_today'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Late Today</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['late_today'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">On Leave</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['on_leave'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Absent</div>
                    <div class="text-2xl font-bold text-red-600">{{ $stats['absent'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Search employee..."
                            class="border-gray-300 rounded-md shadow-sm">
                        <select name="department" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">All Status</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                            <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                        </select>
                        <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" 
                            class="border-gray-300 rounded-md shadow-sm">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Filter
                        </button>
                        <a href="{{ route('attendance.manage') }}" class="text-gray-600 hover:text-gray-800">Reset</a>
                    </form>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Showing attendance for: <strong>{{ \Carbon\Carbon::parse(request('date', date('Y-m-d')))->format('l, F d, Y') }}</strong>
                    </div>
                    <a href="{{ route('attendance.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        Manual Entry
                    </a>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account/Site</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Time In</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Time Out</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Breaks</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Work Hours</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">OT/UT</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $attendance->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $attendance->user->employee_id }} • {{ $attendance->user->department ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-xs font-bold text-indigo-600 uppercase">{{ $attendance->user->account->name ?? '-' }}</div>
                                            <div class="text-[10px] text-gray-400 uppercase tracking-tighter">{{ $attendance->user->site->name ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                            @if($attendance->time_in)
                                                <span class="{{ $attendance->status == 'late' ? 'text-red-600' : 'text-green-600' }}">
                                                    {{ $attendance->time_in->format('h:i A') }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                            @if($attendance->time_out)
                                                {{ $attendance->time_out->format('h:i A') }}
                                            @else
                                                <span class="text-yellow-600">Working</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                            <div class="group relative inline-block">
                                                <span class="cursor-help border-b border-dotted border-gray-400">
                                                    @php
                                                        $breakCount = 0;
                                                        if($attendance->first_break_out) $breakCount++;
                                                        if($attendance->lunch_break_out) $breakCount++;
                                                        if($attendance->second_break_out) $breakCount++;
                                                    @endphp
                                                    {{ $breakCount }}
                                                    @if($attendance->total_break_minutes > 0)
                                                        <span class="text-xs text-gray-500">({{ round($attendance->total_break_minutes) }}m)</span>
                                                    @endif
                                                </span>
                                                <!-- Custom Tooltip -->
                                                <div class="hidden group-hover:block absolute z-50 w-48 p-2 mt-1 -ml-20 text-xs text-white bg-gray-800 rounded-lg shadow-xl">
                                                    <div class="space-y-1">
                                                        <div class="flex justify-between border-b border-gray-600 pb-1 mb-1">
                                                            <span class="font-bold">Break Details</span>
                                                        </div>
                                                        @if($attendance->first_break_out)
                                                            <div class="flex justify-between">
                                                                <span>1st:</span>
                                                                <span>{{ $attendance->first_break_out->format('H:i') }} - {{ $attendance->first_break_in ? $attendance->first_break_in->format('H:i') : '...' }}</span>
                                                            </div>
                                                        @endif
                                                        @if($attendance->lunch_break_out)
                                                            <div class="flex justify-between">
                                                                <span>Lunch:</span>
                                                                <span>{{ $attendance->lunch_break_out->format('H:i') }} - {{ $attendance->lunch_break_in ? $attendance->lunch_break_in->format('H:i') : '...' }}</span>
                                                            </div>
                                                        @endif
                                                        @if($attendance->second_break_out)
                                                            <div class="flex justify-between">
                                                                <span>2nd:</span>
                                                                <span>{{ $attendance->second_break_out->format('H:i') }} - {{ $attendance->second_break_in ? $attendance->second_break_in->format('H:i') : '...' }}</span>
                                                            </div>
                                                        @endif
                                                        @if(!$attendance->first_break_out && !$attendance->lunch_break_out && !$attendance->second_break_out)
                                                            <div class="text-gray-400 italic">No breaks recorded</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center font-medium">
                                            {{ number_format($attendance->total_work_minutes / 60, 1) }} hrs
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                            @if($attendance->overtime_minutes > 0)
                                                <span class="text-green-600">+{{ number_format($attendance->overtime_minutes / 60, 1) }}</span>
                                            @elseif($attendance->undertime_minutes > 0)
                                                <span class="text-red-600">-{{ number_format($attendance->undertime_minutes / 60, 1) }}</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($attendance->status == 'present') bg-green-100 text-green-800
                                                @elseif($attendance->status == 'late') bg-yellow-100 text-yellow-800
                                                @elseif($attendance->status == 'absent') bg-red-100 text-red-800
                                                @elseif($attendance->status == 'on_leave') bg-blue-100 text-blue-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <a href="{{ route('attendance.show', $attendance) }}" 
                                                class="text-indigo-600 hover:text-indigo-900 text-sm mr-2">View</a>
                                            <a href="{{ route('attendance.edit', $attendance) }}" 
                                                class="text-yellow-600 hover:text-yellow-900 text-sm">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">No attendance records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
