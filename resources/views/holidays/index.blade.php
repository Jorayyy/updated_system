<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Holidays') }} - {{ $year }}
            </h2>
            <div class="flex gap-2">
                <form action="{{ route('holidays.generate-recurring') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year + 1 }}">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        Generate {{ $year + 1 }} Holidays
                    </button>
                </form>
                <a href="{{ route('holidays.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    Add Holiday
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8" 
             x-data="{ 
                viewMode: 'calendar', 
                currentMonth: {{ (int)date('m') }},
                scrollMonth(direction) {
                    if (direction === 'next') {
                        this.currentMonth = this.currentMonth === 12 ? 1 : this.currentMonth + 1;
                    } else {
                        this.currentMonth = this.currentMonth === 1 ? 12 : this.currentMonth - 1;
                    }
                }
             }">
            @php
                $months = [
                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                ];
                $holidaysByMonth = $holidays->groupBy(function($holiday) {
                    return (int)$holiday->date->format('m');
                });
            @endphp
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Year Navigation & View Toggle -->
            <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Holidays <span class="text-indigo-600">{{ $year }}</span></h2>
                    <div class="flex gap-1">
                        <a href="{{ route('holidays.index', ['year' => $year - 1]) }}" class="p-2 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 shadow-sm transition-all hover:-translate-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </a>
                        <a href="{{ route('holidays.index', ['year' => $year + 1]) }}" class="p-2 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 shadow-sm transition-all hover:translate-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 bg-gray-100 p-1.5 rounded-2xl shadow-inner">
                    <button @click="viewMode = 'calendar'" 
                        :class="viewMode === 'calendar' ? 'bg-white shadow-md text-indigo-600 translate-z-1' : 'text-gray-500 hover:text-gray-700'"
                        class="flex items-center px-5 py-2 rounded-xl text-sm font-bold transition-all duration-300">
                        Calendar
                    </button>
                    <button @click="viewMode = 'table'" 
                        :class="viewMode === 'table' ? 'bg-white shadow-md text-indigo-600' : 'text-gray-500 hover:text-gray-700'"
                        class="flex items-center px-5 py-2 rounded-xl text-sm font-bold transition-all duration-300">
                        List View
                    </button>
                </div>
            </div>

            <!-- Calendar Carousel View -->
            <div x-show="viewMode === 'calendar'" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="relative">
                
                <!-- Carousel Month Selector (Horizontal Scroll) -->
                <div class="flex overflow-x-auto no-scrollbar gap-2 mb-6 pb-2 snap-x">
                    @foreach($months as $num => $name)
                        <button @click="currentMonth = {{ $num }}" 
                            class="flex-none snap-center px-6 py-2 rounded-2xl font-black text-sm uppercase tracking-widest transition-all duration-300"
                            :class="currentMonth === {{ $num }} ? 'bg-indigo-600 text-white shadow-xl scale-105' : 'bg-white text-gray-400 hover:text-indigo-600 shadow-sm'">
                            {{ $name }}
                        </button>
                    @endforeach
                </div>

                <!-- Single Month Card -->
                <div class="calendar-container">
                    @foreach($months as $monthNum => $monthName)
                        <div x-show="currentMonth === {{ $monthNum }}" 
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0 translate-x-12"
                             x-transition:enter-end="opacity-100 translate-x-0"
                             class="bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 overflow-hidden mb-12">
                            
                            <div class="p-4 sm:p-8">
                                <div class="text-center mb-8">
                                    <h3 class="text-4xl font-black text-indigo-900 uppercase tracking-tighter">{{ $monthName }}</h3>
                                    <p class="text-indigo-400 font-bold uppercase tracking-[0.3em] text-[10px] mt-1">{{ $year }}</p>
                                </div>

                                @php
                                    $monthHolidays = $holidaysByMonth->get($monthNum, collect());
                                    $firstDay = \Carbon\Carbon::create($year, $monthNum, 1);
                                    $daysInMonth = $firstDay->daysInMonth;
                                    $startPadding = $firstDay->dayOfWeek;
                                @endphp

                                <!-- Robust Grid for Days Header -->
                                <div style="display: grid; grid-template-columns: repeat(7, 1fr);" class="mb-6">
                                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                                        <div class="text-[10px] font-black text-indigo-300 text-center uppercase tracking-tighter">{{ $dayName }}</div>
                                    @endforeach
                                </div>

                                <!-- Robust Grid for Days -->
                                <div style="display: grid; grid-template-columns: repeat(7, 1fr); row-gap: 0.5rem; column-gap: 0.5rem;">
                                    @for($i = 0; $i < $startPadding; $i++)
                                        <div class="h-12 w-full"></div>
                                    @endfor

                                    @for($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $currentDate = \Carbon\Carbon::create($year, $monthNum, $day);
                                            $holiday = $monthHolidays->first(function($h) use ($currentDate) {
                                                return $h->date->isSameDay($currentDate);
                                            });
                                            $isToday = $currentDate->isToday();
                                            $isWeekend = $currentDate->isWeekend();
                                            
                                            // Determine background and text colors
                                            $bgClass = 'bg-transparent';
                                            $textClass = 'text-gray-700';
                                            $borderClass = 'border-transparent';
                                            $shadowClass = '';

                                            if ($holiday) {
                                                $textClass = 'text-white font-bold';
                                                $shadowClass = 'shadow-md';
                                                if ($holiday->type == 'regular') {
                                                    $bgClass = 'bg-red-600';
                                                } elseif ($holiday->type == 'special') {
                                                    $bgClass = 'bg-yellow-500';
                                                } else {
                                                    $bgClass = 'bg-blue-600';
                                                }
                                            } elseif ($isToday) {
                                                $bgClass = 'bg-indigo-50';
                                                $textClass = 'text-indigo-700 font-bold';
                                                $borderClass = 'border-indigo-600';
                                            } elseif ($isWeekend) {
                                                $textClass = 'text-red-500 font-medium';
                                                $bgClass = 'bg-red-50/30';
                                            }
                                        @endphp
                                        <div class="flex items-center justify-center relative group">
                                            <div class="w-10 h-10 sm:w-11 sm:h-11 flex flex-col items-center justify-center rounded-xl transition-all duration-300 relative border-2 {{ $bgClass }} {{ $textClass }} {{ $borderClass }} {{ $shadowClass }} hover:scale-110">
                                                <span class="text-sm sm:text-base">{{ $day }}</span>
                                                @if($holiday)
                                                    <div class="absolute bottom-1 w-1 h-1 bg-white/50 rounded-full"></div>
                                                @endif
                                            </div>
                                            
                                            @if($holiday)
                                                <div class="hidden group-hover:block absolute bottom-full mb-2 left-1/2 -translate-x-1/2 w-48 bg-gray-900 text-white p-3 rounded-xl shadow-2xl z-50 text-center">
                                                    <div class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">{{ $holiday->type_name }}</div>
                                                    <div class="text-xs font-bold leading-tight">{{ $holiday->name }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- List View (Legacy Table) -->                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-5 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Date</th>
                            <th class="px-6 py-5 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Event</th>
                            <th class="px-6 py-5 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Category</th>
                            <th class="px-6 py-5 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($holidays as $holiday)
                            <tr class="hover:bg-indigo-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-black text-gray-900">{{ $holiday->date->format('M d') }}</span>
                                    <span class="text-xs text-gray-400 ml-1 font-bold">{{ $holiday->date->format('D') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-800">{{ $holiday->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter border
                                        {{ $holiday->type == 'regular' ? 'bg-red-50 text-red-600 border-red-100' : ($holiday->type == 'special' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-blue-50 text-blue-600 border-blue-100') }}">
                                        {{ $holiday->type_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('holidays.edit', $holiday) }}" class="text-xs font-black text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-4 py-2 rounded-xl transition-all">Edit</a>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
