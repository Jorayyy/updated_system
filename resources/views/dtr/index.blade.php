<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Daily Time Record (DTR)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Month/Year Filter -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <select name="month" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                        <select name="year" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            View
                        </button>
                        <a href="{{ route('dtr.pdf', ['month' => $month, 'year' => $year]) }}" 
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            Download PDF
                        </a>
                    </form>
                </div>
            </div>

            <!-- Employee Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Employee ID</div>
                            <div class="font-bold text-gray-900 dark:text-white">{{ $user->employee_id }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Name</div>
                            <div class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Department</div>
                            <div class="font-bold text-gray-900 dark:text-white">{{ $user->department ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Position</div>
                            <div class="font-bold text-gray-900 dark:text-white">{{ $user->position ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Present</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $summary['present_days'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Late</div>
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $summary['late_days'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Absent</div>
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $summary['absent_days'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Leave</div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $summary['leave_days'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Work Hours</div>
                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $summary['total_work_hours'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Overtime</div>
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $summary['total_overtime_hours'] }}</div>
                </div>
            </div>

            <!-- DTR Table with All Break Times -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        DTR for {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th rowspan="2" class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase border-r dark:border-gray-600">Date</th>
                                    <th rowspan="2" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase border-r dark:border-gray-600">Day</th>
                                    <th rowspan="2" class="px-2 py-2 text-center text-xs font-medium text-blue-600 dark:text-blue-400 uppercase border-r dark:border-gray-600 bg-blue-50 dark:bg-blue-900/30">IN</th>
                                    <th colspan="2" class="px-2 py-2 text-center text-xs font-medium text-yellow-600 dark:text-yellow-400 uppercase border-r dark:border-gray-600 bg-yellow-50 dark:bg-yellow-900/30">1st Break</th>
                                    <th colspan="2" class="px-2 py-2 text-center text-xs font-medium text-orange-600 dark:text-orange-400 uppercase border-r dark:border-gray-600 bg-orange-50 dark:bg-orange-900/30">Lunch</th>
                                    <th colspan="2" class="px-2 py-2 text-center text-xs font-medium text-cyan-600 dark:text-cyan-400 uppercase border-r dark:border-gray-600 bg-cyan-50 dark:bg-cyan-900/30">2nd Break</th>
                                    <th rowspan="2" class="px-2 py-2 text-center text-xs font-medium text-red-600 dark:text-red-400 uppercase border-r dark:border-gray-600 bg-red-50 dark:bg-red-900/30">OUT</th>
                                    <th rowspan="2" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase border-r dark:border-gray-600">Hours</th>
                                    <th rowspan="2" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase border-r dark:border-gray-600">OT/UT</th>
                                    <th rowspan="2" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <th class="px-1 py-1 text-center text-xs text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/30">Out</th>
                                    <th class="px-1 py-1 text-center text-xs text-green-600 dark:text-green-400 border-r dark:border-gray-600 bg-green-50 dark:bg-green-900/30">In</th>
                                    <th class="px-1 py-1 text-center text-xs text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/30">Out</th>
                                    <th class="px-1 py-1 text-center text-xs text-pink-600 dark:text-pink-400 border-r dark:border-gray-600 bg-pink-50 dark:bg-pink-900/30">In</th>
                                    <th class="px-1 py-1 text-center text-xs text-cyan-600 dark:text-cyan-400 bg-cyan-50 dark:bg-cyan-900/30">Out</th>
                                    <th class="px-1 py-1 text-center text-xs text-lime-600 dark:text-lime-400 border-r dark:border-gray-600 bg-lime-50 dark:bg-lime-900/30">In</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @php
                                    $startDate = \Carbon\Carbon::create($year, $month, 1);
                                    $endDate = $startDate->copy()->endOfMonth();
                                    $attendanceByDate = $attendances->keyBy(fn($a) => $a->date->format('Y-m-d'));
                                @endphp
                                @for($date = $startDate->copy(); $date <= $endDate; $date->addDay())
                                    @php
                                        $attendance = $attendanceByDate->get($date->format('Y-m-d'));
                                        $isWeekend = $date->isWeekend();
                                    @endphp
                                    <tr class="{{ $isWeekend ? 'bg-gray-50 dark:bg-gray-900/30' : '' }}">
                                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white border-r dark:border-gray-700">{{ $date->format('d') }}</td>
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center border-r dark:border-gray-700">{{ $date->format('D') }}</td>
                                        @if($attendance)
                                            <!-- Time IN -->
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center border-r dark:border-gray-700 bg-blue-50/50 dark:bg-blue-900/10">
                                                <span class="text-blue-700 dark:text-blue-300">{{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }}</span>
                                            </td>
                                            <!-- 1st Break Out -->
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center bg-yellow-50/50 dark:bg-yellow-900/10">
                                                <span class="text-yellow-700 dark:text-yellow-300">{{ $attendance->first_break_out ? $attendance->first_break_out->format('h:i A') : '-' }}</span>
                                            </td>
                                            <!-- 1st Break In -->
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center border-r dark:border-gray-700 bg-green-50/50 dark:bg-green-900/10">
                                                <span class="text-green-700 dark:text-green-300">{{ $attendance->first_break_in ? $attendance->first_break_in->format('h:i A') : '-' }}</span>
                                            </td>
                                            <!-- Lunch Out -->
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center bg-orange-50/50 dark:bg-orange-900/10">
                                                <span class="text-orange-700 dark:text-orange-300">{{ $attendance->lunch_break_out ? $attendance->lunch_break_out->format('h:i A') : '-' }}</span>
                                            </td>
                                            <!-- Lunch In -->
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center border-r dark:border-gray-700 bg-pink-50/50 dark:bg-pink-900/10">
                                                <span class="text-pink-700 dark:text-pink-300">{{ $attendance->lunch_break_in ? $attendance->lunch_break_in->format('h:i A') : '-' }}</span>
                                            </td>
                                            <!-- 2nd Break Out -->
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center bg-cyan-50/50 dark:bg-cyan-900/10">
                                                <span class="text-cyan-700 dark:text-cyan-300">{{ $attendance->second_break_out ? $attendance->second_break_out->format('h:i A') : '-' }}</span>
                                            </td>
                                            <!-- 2nd Break In -->
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center border-r dark:border-gray-700 bg-lime-50/50 dark:bg-lime-900/10">
                                                <span class="text-lime-700 dark:text-lime-300">{{ $attendance->second_break_in ? $attendance->second_break_in->format('h:i A') : '-' }}</span>
                                            </td>
                                            <!-- Time OUT -->
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center border-r dark:border-gray-700 bg-red-50/50 dark:bg-red-900/10">
                                                <span class="text-red-700 dark:text-red-300">{{ $attendance->time_out ? $attendance->time_out->format('h:i A') : '-' }}</span>
                                            </td>
                                            <!-- Hours -->
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center font-medium text-gray-900 dark:text-white border-r dark:border-gray-700">
                                                {{ number_format($attendance->total_work_minutes / 60, 1) }}
                                            </td>
                                            <!-- OT/UT -->
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center border-r dark:border-gray-700">
                                                @if($attendance->overtime_minutes > 0)
                                                    <span class="text-green-600 dark:text-green-400">+{{ number_format($attendance->overtime_minutes / 60, 1) }}</span>
                                                @elseif($attendance->undertime_minutes > 0)
                                                    <span class="text-red-600 dark:text-red-400">-{{ number_format($attendance->undertime_minutes / 60, 1) }}</span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <!-- Status -->
                                            <td class="px-2 py-2 whitespace-nowrap text-center">
                                                <span class="px-2 py-1 text-xs rounded-full 
                                                    @if($attendance->status == 'present') bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300
                                                    @elseif($attendance->status == 'late') bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300
                                                    @elseif($attendance->status == 'absent') bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300
                                                    @elseif($attendance->status == 'on_leave') bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300
                                                    @else bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-300 @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                                </span>
                                            </td>
                                        @else
                                            <td class="px-2 py-2 text-center text-gray-400 border-r dark:border-gray-700">-</td>
                                            <td class="px-2 py-2 text-center text-gray-400">-</td>
                                            <td class="px-2 py-2 text-center text-gray-400 border-r dark:border-gray-700">-</td>
                                            <td class="px-2 py-2 text-center text-gray-400">-</td>
                                            <td class="px-2 py-2 text-center text-gray-400 border-r dark:border-gray-700">-</td>
                                            <td class="px-2 py-2 text-center text-gray-400">-</td>
                                            <td class="px-2 py-2 text-center text-gray-400 border-r dark:border-gray-700">-</td>
                                            <td class="px-2 py-2 text-center text-gray-400 border-r dark:border-gray-700">-</td>
                                            <td class="px-2 py-2 text-center text-gray-400 border-r dark:border-gray-700">-</td>
                                            <td class="px-2 py-2 text-center text-gray-400 border-r dark:border-gray-700">-</td>
                                            <td class="px-2 py-2 text-center">
                                                @if($isWeekend)
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">Weekend</span>
                                                @elseif($date->isFuture())
                                                    <span class="text-xs text-gray-400">-</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300">Absent</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Legend -->
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Legend:</h4>
                        <div class="flex flex-wrap gap-4 text-xs">
                            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-blue-500 rounded"></span> IN</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-yellow-500 rounded"></span> 1st Break Out</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-green-500 rounded"></span> 1st Break In</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-orange-500 rounded"></span> Lunch Out</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-pink-500 rounded"></span> Lunch In</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-cyan-500 rounded"></span> 2nd Break Out</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-lime-500 rounded"></span> 2nd Break In</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-500 rounded"></span> OUT</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
