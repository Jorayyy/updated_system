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
            <div class="bg-white shadow-sm sm:rounded-lg mb-6 overflow-visible">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                @if($status['attendance'])
                                    Attendance for {{ $status['attendance']->date->format('l, F d, Y') }}
                                @else
                                    Today - <span id="currentDay">{{ now()->format('l, F d, Y') }}</span>
                                @endif
                            </h3>
                            <p class="text-gray-500 text-sm">
                                Real Time: <span id="currentRealTime" class="font-mono font-bold">{{ now()->format('l, F d, Y h:i:s A') }}</span>
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

                    <!-- Punch Selection UI (Radio Button List) -->
                    <form action="{{ route('attendance.step') }}" method="POST">
                        @csrf
                        <div class="space-y-1 mb-8 overflow-hidden rounded-xl border border-gray-100 shadow-sm">
                            @foreach($status['steps'] as $stepKey => $step)
                                <label class="flex items-center justify-between p-3 transition-all cursor-pointer hover:bg-slate-50 border-b border-gray-100 last:border-0
                                    @if($step['is_completed'])
                                        bg-gray-50/50 opacity-60
                                    @elseif($step['is_next'])
                                        bg-white ring-1 ring-inset ring-blue-100
                                    @else
                                        bg-white
                                    @endif
                                " style="background-color: 
                                    @if($stepKey == 'time_in' || $stepKey == 'time_out')
                                        #ebf5ff
                                    @elseif(str_contains($stepKey, 'first_break'))
                                        #fffcf0
                                    @elseif(str_contains($stepKey, 'lunch_break'))
                                        #fff5f5
                                    @elseif(str_contains($stepKey, 'second_break'))
                                        #f0fff4
                                    @endif
                                    !important;">
                                    <div class="flex items-center gap-4">
                                        {{-- Row Background Highlight based on image --}}
                                        <div class="w-24 px-3 py-1.5 text-center rounded text-[11px] font-black uppercase tracking-widest text-slate-700
                                            @if($stepKey == 'time_in' || $stepKey == 'time_out')
                                                bg-blue-500 text-white
                                            @elseif(str_contains($stepKey, 'first_break'))
                                                bg-yellow-100
                                            @elseif(str_contains($stepKey, 'lunch_break'))
                                                bg-pink-100
                                            @elseif(str_contains($stepKey, 'second_break'))
                                                bg-green-100
                                            @endif
                                        ">
                                            {{ $step['label'] }}
                                        </div>
                                        
                                        @if($step['time'])
                                            <div class="text-xs font-bold text-blue-600 flex items-center bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2.5"/></svg>
                                                Punched: {{ $step['time'] }}
                                            </div>
                                        @endif
                                    </div>

                                    @if(!$step['is_completed'])
                                        <div class="flex items-center gap-2">
                                            @if($step['is_next'])
                                                <span class="text-[10px] font-bold text-blue-500 animate-pulse uppercase tracking-wider">Suggested</span>
                                            @endif
                                            <input type="radio" name="step" value="{{ $stepKey }}" 
                                                class="w-6 h-6 text-indigo-600 border-gray-300 focus:ring-indigo-500 ring-offset-2"
                                                {{ $step['is_next'] ? 'checked' : '' }}>
                                        </div>
                                    @else
                                        <div class="text-emerald-500 pr-1">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        </div>
                                    @endif
                                </label>
                            @endforeach
                        </div>

                        <!-- Big PUNCH Button -->
                        <div class="mt-6">
                            <button type="submit" class="w-full py-5 px-6 bg-yellow-400 hover:bg-yellow-500 text-black rounded-xl font-black text-2xl uppercase tracking-widest shadow-xl shadow-yellow-500/30 transition-all active:scale-95 flex items-center justify-center group overflow-visible relative border-4 border-red-600" style="min-height: 80px;">
                                <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                <span class="relative z-10 flex items-center gap-3">
                                    <svg class="w-6 h-6 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 010 18z"/></svg>
                                    PUNCH
                                </span>
                            </button>
                        </div>
                    </form>
                    
                    @if($status['attendance'])
                        <div class="mt-8 flex justify-end">
                            <form action="{{ route('attendance.time-out') }}" method="POST" onsubmit="return confirm('WARNING: This will immediately clock you out and complete your day even if you missed lunch or breaks. Continue?')">
                                @csrf
                                <input type="hidden" name="reason" value="Immediate Dismissal">
                                <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-700 underline uppercase tracking-tighter">
                                    Force Immediate Checkout
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Today's Summary (when completed) -->
            @if($status['status'] === 'completed' && $status['attendance'])
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border-t-4 border-blue-500">
                    <div class="p-6">
                        <h4 class="font-black text-gray-900 mb-6 uppercase tracking-widest text-sm flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Clock In Summary
                        </h4>
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
                    </div>
            @endif

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
            
            // Format for Real Time (Full Date & Time)
            const realTimeOptions = { 
                weekday: 'long', 
                month: 'long', 
                day: 'numeric', 
                year: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit', 
                hour12: true 
            };
            document.getElementById('currentRealTime').textContent = now.toLocaleDateString('en-US', realTimeOptions);
            
            // Update the day badge if it's currently the "Today" header
            const dayEl = document.getElementById('currentDay');
            if (dayEl) {
                const dayOptions = { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' };
                dayEl.textContent = now.toLocaleDateString('en-US', dayOptions);
            }
        }
        setInterval(updateClock, 1000);
        updateClock(); // Run immediately
    </script>
</x-app-layout>
