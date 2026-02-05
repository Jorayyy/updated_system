<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($status['ip_blocked'] ?? false)
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded shadow-sm flex items-start gap-3">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="font-bold underline text-lg">PUNCHING DISABLED</p>
                        <p class="mt-1">{{ $status['ip_message'] }}</p>
                    </div>
                </div>
            @endif

            <!-- Current Status Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                @if($status['attendance'])
                                    Attendance for {{ $status['attendance']->date->format('l, F d, Y') }}
                                @else
                                    Today - {{ now()->format('l, F d, Y') }}
                                @endif
                            </h3>
                            <p class="text-gray-500">
                                Current Time: <span id="currentTime" class="font-mono">{{ now()->format('h:i:s A') }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            @if($status['status'] === 'not_started')
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">Not Started</span>
                            @elseif($status['status'] === 'working')
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">Working</span>
                            @elseif($status['status'] === 'on_break')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">On Break</span>
                            @elseif($status['status'] === 'completed')
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">Day Completed</span>
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
                                            bg-blue-50 border-blue-300
                                            @break
                                        @case('yellow')
                                            bg-yellow-50 border-yellow-300
                                            @break
                                        @case('green')
                                            bg-green-50 border-green-300
                                            @break
                                        @case('orange')
                                            bg-orange-50 border-orange-300
                                            @break
                                        @case('pink')
                                            bg-pink-50 border-pink-300
                                            @break
                                        @case('cyan')
                                            bg-cyan-50 border-cyan-300
                                            @break
                                        @case('lime')
                                            bg-lime-50 border-lime-300
                                            @break
                                        @case('red')
                                            bg-red-50 border-red-300
                                            @break
                                        @default
                                            bg-gray-50 border-gray-300
                                    @endswitch
                                @elseif($step['is_next'])
                                    bg-white border-indigo-400 shadow-md
                                @else
                                    bg-gray-50 border-gray-200 opacity-60
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
                                            bg-gray-200 text-gray-500
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
                                        <div class="font-semibold text-gray-900">{{ $step['label'] }}</div>
                                        @if($step['time'])
                                            <div class="text-sm text-gray-500">{{ $step['time'] }}</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action button for next step -->
                                @if($step['is_next'])
                                    @if($status['ip_blocked'] ?? false)
                                        <button type="button" disabled class="px-4 py-2 bg-gray-400 text-white rounded-lg font-medium cursor-not-allowed opacity-50" title="IP Restricted">
                                            {{ $step['action'] }}
                                        </button>
                                    @else
                                        <form action="{{ route('attendance.step') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                                                {{ $step['action'] }}
                                            </button>
                                        </form>
                                    @endif
                                @elseif($step['is_completed'])
                                    <span class="text-sm font-medium 
                                        @switch($step['color'])
                                            @case('blue')
                                                text-blue-600
                                                @break
                                            @case('yellow')
                                                text-yellow-600
                                                @break
                                            @case('green')
                                                text-green-600
                                                @break
                                            @case('orange')
                                                text-orange-600
                                                @break
                                            @case('pink')
                                                text-pink-600
                                                @break
                                            @case('cyan')
                                                text-cyan-600
                                                @break
                                            @case('lime')
                                                text-lime-600
                                                @break
                                            @case('red')
                                                text-red-600
                                                @break
                                            @default
                                                text-gray-600
                                        @endswitch
                                    ">✓ Done</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Quick Time Out Button (skip all remaining steps) -->
                    @if($status['status'] === 'working' || $status['status'] === 'on_break')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <form action="{{ route('attendance.time-out') }}" method="POST" class="flex items-center justify-between">
                                @csrf
                                <div class="text-sm text-gray-500">
                                    Need to leave early?
                                </div>
                                <button type="submit" 
                                    @if($status['ip_blocked'] ?? false) disabled @endif
                                    onclick="return confirm('This will skip all remaining breaks and clock you out. Are you sure?')"
                                    class="px-4 py-2 {{ ($status['ip_blocked'] ?? false) ? 'bg-gray-400 cursor-not-allowed opacity-50' : 'bg-red-600 hover:bg-red-700' }} text-white rounded-lg font-medium transition">
                                    🚪 Quick Time Out
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Summary (when completed) -->
                    @if($status['status'] === 'completed' && $status['attendance'])
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-4">Today's Summary</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="text-xs text-gray-500 uppercase">Work Time</div>
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ $status['attendance']->formatted_work_time }}
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="text-xs text-gray-500 uppercase">Break Time</div>
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ $status['attendance']->formatted_break_time }}
                                    </div>
                                </div>
                                @if($status['attendance']->overtime_minutes > 0)
                                <div class="bg-green-50 p-3 rounded-lg">
                                    <div class="text-xs text-green-600 uppercase">Overtime</div>
                                    <div class="text-lg font-bold text-green-700">
                                        {{ floor($status['attendance']->overtime_minutes / 60) }}h {{ $status['attendance']->overtime_minutes % 60 }}m
                                    </div>
                                </div>
                                @endif
                                @if($status['attendance']->undertime_minutes > 0)
                                <div class="bg-red-50 p-3 rounded-lg">
                                    <div class="text-xs text-red-600 uppercase">Undertime</div>
                                    <div class="text-lg font-bold text-red-700">
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">This Week's Attendance</h3>
                        <a href="{{ route('attendance.history') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                            View Full History →
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IN</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">OUT</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Work</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($weeklyAttendance as $attendance)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $attendance->date->format('D, M d') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {{ $attendance->time_out ? $attendance->time_out->format('h:i A') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {{ $attendance->formatted_work_time }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($attendance->status == 'present') bg-green-100 text-green-800
                                                @elseif($attendance->status == 'late') bg-yellow-100 text-yellow-800
                                                @elseif($attendance->status == 'absent') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($attendance->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
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
