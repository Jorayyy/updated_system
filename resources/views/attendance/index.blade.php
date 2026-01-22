<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Attendance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Current Status Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Today - {{ now()->format('l, F d, Y') }}
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                Current Time: <span id="currentTime" class="font-mono">{{ now()->format('h:i:s A') }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            @if($status['status'] === 'not_started')
                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded-full text-sm">Not Started</span>
                            @elseif($status['status'] === 'working')
                                <span class="px-3 py-1 bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300 rounded-full text-sm">Working</span>
                            @elseif($status['status'] === 'on_break')
                                <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300 rounded-full text-sm">On Break</span>
                            @elseif($status['status'] === 'completed')
                                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300 rounded-full text-sm">Day Completed</span>
                            @endif
                        </div>
                    </div>

                    <!-- Sequential Steps UI -->
                    <div class="space-y-3">
                        @foreach($status['steps'] as $stepKey => $step)
                            <div class="flex items-center justify-between p-3 rounded-lg border-2 transition-all
                                @if($step['is_completed'])
                                    @switch($step['color'])
                                        @case('blue')
                                            bg-blue-50 dark:bg-blue-900/30 border-blue-300 dark:border-blue-700
                                            @break
                                        @case('yellow')
                                            bg-yellow-50 dark:bg-yellow-900/30 border-yellow-300 dark:border-yellow-700
                                            @break
                                        @case('green')
                                            bg-green-50 dark:bg-green-900/30 border-green-300 dark:border-green-700
                                            @break
                                        @case('orange')
                                            bg-orange-50 dark:bg-orange-900/30 border-orange-300 dark:border-orange-700
                                            @break
                                        @case('pink')
                                            bg-pink-50 dark:bg-pink-900/30 border-pink-300 dark:border-pink-700
                                            @break
                                        @case('cyan')
                                            bg-cyan-50 dark:bg-cyan-900/30 border-cyan-300 dark:border-cyan-700
                                            @break
                                        @case('lime')
                                            bg-lime-50 dark:bg-lime-900/30 border-lime-300 dark:border-lime-700
                                            @break
                                        @case('red')
                                            bg-red-50 dark:bg-red-900/30 border-red-300 dark:border-red-700
                                            @break
                                        @default
                                            bg-gray-50 dark:bg-gray-700/30 border-gray-300 dark:border-gray-600
                                    @endswitch
                                @elseif($step['is_next'])
                                    bg-white dark:bg-gray-700 border-indigo-400 dark:border-indigo-500 shadow-md
                                @else
                                    bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 opacity-60
                                @endif
                            ">
                                <div class="flex items-center gap-3">
                                    <!-- Step indicator -->
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold
                                        @if($step['is_completed'])
                                            @switch($step['color'])
                                                @case('blue')
                                                    bg-blue-500 text-white
                                                    @break
                                                @case('yellow')
                                                    bg-yellow-500 text-white
                                                    @break
                                                @case('green')
                                                    bg-green-500 text-white
                                                    @break
                                                @case('orange')
                                                    bg-orange-500 text-white
                                                    @break
                                                @case('pink')
                                                    bg-pink-500 text-white
                                                    @break
                                                @case('cyan')
                                                    bg-cyan-500 text-white
                                                    @break
                                                @case('lime')
                                                    bg-lime-500 text-white
                                                    @break
                                                @case('red')
                                                    bg-red-500 text-white
                                                    @break
                                                @default
                                                    bg-gray-500 text-white
                                            @endswitch
                                        @elseif($step['is_next'])
                                            bg-indigo-500 text-white animate-pulse
                                        @else
                                            bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-400
                                        @endif
                                    ">
                                        @if($step['is_completed'])
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @else
                                            <span>○</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Step label -->
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $step['label'] }}</div>
                                        @if($step['time'])
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $step['time'] }}</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action button for next step -->
                                @if($step['is_next'])
                                    <form action="{{ route('attendance.step') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                                            {{ $step['action'] }}
                                        </button>
                                    </form>
                                @elseif($step['is_completed'])
                                    <span class="text-sm font-medium 
                                        @switch($step['color'])
                                            @case('blue')
                                                text-blue-600 dark:text-blue-400
                                                @break
                                            @case('yellow')
                                                text-yellow-600 dark:text-yellow-400
                                                @break
                                            @case('green')
                                                text-green-600 dark:text-green-400
                                                @break
                                            @case('orange')
                                                text-orange-600 dark:text-orange-400
                                                @break
                                            @case('pink')
                                                text-pink-600 dark:text-pink-400
                                                @break
                                            @case('cyan')
                                                text-cyan-600 dark:text-cyan-400
                                                @break
                                            @case('lime')
                                                text-lime-600 dark:text-lime-400
                                                @break
                                            @case('red')
                                                text-red-600 dark:text-red-400
                                                @break
                                            @default
                                                text-gray-600 dark:text-gray-400
                                        @endswitch
                                    ">✓ Done</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Quick Time Out Button (skip all remaining steps) -->
                    @if($status['status'] === 'working' || $status['status'] === 'on_break')
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <form action="{{ route('attendance.time-out') }}" method="POST" class="flex items-center justify-between">
                                @csrf
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Need to leave early?
                                </div>
                                <button type="submit" 
                                    onclick="return confirm('This will skip all remaining breaks and clock you out. Are you sure?')"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                                    🚪 Quick Time Out
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Summary (when completed) -->
                    @if($status['status'] === 'completed' && $status['attendance'])
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Today's Summary</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Work Time</div>
                                    <div class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $status['attendance']->formatted_work_time }}
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Break Time</div>
                                    <div class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $status['attendance']->formatted_break_time }}
                                    </div>
                                </div>
                                @if($status['attendance']->overtime_minutes > 0)
                                <div class="bg-green-50 dark:bg-green-900/30 p-3 rounded-lg">
                                    <div class="text-xs text-green-600 dark:text-green-400 uppercase">Overtime</div>
                                    <div class="text-lg font-bold text-green-700 dark:text-green-300">
                                        {{ floor($status['attendance']->overtime_minutes / 60) }}h {{ $status['attendance']->overtime_minutes % 60 }}m
                                    </div>
                                </div>
                                @endif
                                @if($status['attendance']->undertime_minutes > 0)
                                <div class="bg-red-50 dark:bg-red-900/30 p-3 rounded-lg">
                                    <div class="text-xs text-red-600 dark:text-red-400 uppercase">Undertime</div>
                                    <div class="text-lg font-bold text-red-700 dark:text-red-300">
                                        {{ floor($status['attendance']->undertime_minutes / 60) }}h {{ $status['attendance']->undertime_minutes % 60 }}m
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- This Week's Attendance -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">This Week's Attendance</h3>
                        <a href="{{ route('attendance.history') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm">
                            View Full History →
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">IN</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">OUT</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Work</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($weeklyAttendance as $attendance)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $attendance->date->format('D, M d') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $attendance->time_out ? $attendance->time_out->format('h:i A') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $attendance->formatted_work_time }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($attendance->status == 'present') bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300
                                                @elseif($attendance->status == 'late') bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300
                                                @elseif($attendance->status == 'absent') bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300
                                                @else bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-300 @endif">
                                                {{ ucfirst($attendance->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No attendance records this week.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Clock Script -->
    <script>
        function updateClock() {
            const now = new Date();
            const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', options);
        }
        setInterval(updateClock, 1000);
    </script>
</x-app-layout>
