<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('schedules.index') }}" class="flex items-center justify-center bg-gray-600 hover:bg-gray-700 text-white rounded-lg p-2 transition-all shadow-md group" title="Back to Schedules">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h2 class="font-bold text-xl text-green-900 leading-tight uppercase tracking-tight flex items-center">
                    SHIFT TABLE
                </h2>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('shifts.create', ['category' => 'Regular/Wholeday']) }}" 
                   style="background-color: #00A651 !important; color: #FFFFFF !important; opacity: 1 !important; visibility: visible !important; display: inline-flex !important;"
                   class="items-center px-3 py-2 border-none rounded-md font-black text-[10px] uppercase tracking-widest shadow-md hover:brightness-110 active:scale-95 transition-all duration-200 no-underline whitespace-nowrap">
                    <div style="background-color: #FFFFFF !important;" class="rounded-full p-1 mr-2 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" style="color: #00A651 !important;" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    Regular/Wholeday
                </a>

                <a href="{{ route('shifts.create', ['category' => 'Half Day']) }}" 
                   style="background-color: #FFFFFF !important; color: #4B5563 !important; opacity: 1 !important; visibility: visible !important; display: inline-flex !important; border: 1px solid #E5E7EB !important;"
                   class="items-center px-3 py-2 rounded-md font-black text-[10px] uppercase tracking-widest shadow-sm hover:bg-gray-50 active:scale-95 transition-all duration-200 no-underline whitespace-nowrap">
                    <div style="background-color: #00A651 !important;" class="rounded-full p-1 mr-2 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    Half Day
                </a>

                <a href="{{ route('shifts.create', ['category' => 'Rest day/Holiday']) }}" 
                   style="background-color: #FFFFFF !important; color: #4B5563 !important; opacity: 1 !important; visibility: visible !important; display: inline-flex !important; border: 1px solid #E5E7EB !important;"
                   class="items-center px-3 py-2 rounded-md font-black text-[10px] uppercase tracking-widest shadow-sm hover:bg-gray-50 active:scale-95 transition-all duration-200 no-underline whitespace-nowrap">
                    <div style="background-color: #00A651 !important;" class="rounded-full p-1 mr-2 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    Rest day/Holiday
                </a>

                <a href="{{ route('shifts.create', ['category' => 'Controlled Flexi']) }}" 
                   style="background-color: #FFFFFF !important; color: #4B5563 !important; opacity: 1 !important; visibility: visible !important; display: inline-flex !important; border: 1px solid #E5E7EB !important;"
                   class="items-center px-3 py-2 rounded-md font-black text-[10px] uppercase tracking-widest shadow-sm hover:bg-gray-50 active:scale-95 transition-all duration-200 no-underline whitespace-nowrap">
                    <div style="background-color: #00A651 !important;" class="rounded-full p-1 mr-2 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    Controlled Flexi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900">
                    @if(session('success'))
                        <div class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-2 rounded border border-green-200">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="space-y-4">
                        @foreach($departments as $dept)
                            <div x-data="{ open: false }" class="border-t-4 border-green-600 shadow-md">
                                <div @click="open = !open" class="cursor-pointer bg-gray-100/80 p-3 flex items-center hover:bg-gray-200 transition-colors border border-gray-200 rounded-sm">
                                    <div class="bg-red-600 rounded-full p-1 mr-4 shadow-sm flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                    <span class="font-bold text-gray-700 uppercase tracking-widest text-sm">
                                        Department: {{ $dept->name }}
                                    </span>
                                </div>

                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                     class="p-4 bg-white">
                                     
                                     @php
                                         $groupedShifts = $dept->shifts->groupBy('category');
                                     @endphp

                                     @forelse($groupedShifts as $category => $shifts)
                                        <div class="mb-6">
                                            <h4 class="text-red-700 font-bold flex items-center mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $category }} Schedules
                                            </h4>
                                            
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-100">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-4 py-2 text-left text-sm font-bold text-gray-600 uppercase tracking-tight">Shift</th>
                                                            <th class="px-4 py-2 text-left text-sm font-bold text-gray-600 uppercase tracking-tight">Break(s)</th>
                                                            <th class="px-4 py-2 text-left text-sm font-bold text-gray-600 uppercase tracking-tight text-center">Registered Hours</th>
                                                            <th class="px-4 py-2 text-left text-sm font-bold text-gray-600 uppercase tracking-tight">Description</th>
                                                            <th class="px-4 py-2 text-center text-sm font-bold text-gray-600 uppercase tracking-tight">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-50">
                                                        @foreach($shifts as $shift)
                                                            <tr class="hover:bg-yellow-50 transition-colors">
                                                                <td class="px-4 py-4 text-sm text-gray-700 font-medium">
                                                                    {{ \Carbon\Carbon::parse($shift->time_in)->format('H:i') }} to {{ \Carbon\Carbon::parse($shift->time_out)->format('H:i') }}
                                                                </td>
                                                                <td class="px-4 py-4 text-sm text-gray-600">
                                                                    <div class="flex flex-col space-y-1">
                                                                        <span class="flex justify-between w-48 italic">lunch break: <span class="font-bold ml-1">{{ $shift->lunch_break_minutes }}min(s).</span></span>
                                                                        <span class="flex justify-between w-48 italic">1st break: <span class="font-bold ml-1">{{ sprintf("%02d", $shift->first_break_minutes) }}min(s).</span></span>
                                                                        <span class="flex justify-between w-48 italic">2nd break: <span class="font-bold ml-1">{{ sprintf("%02d", $shift->second_break_minutes) }}min(s).</span></span>
                                                                    </div>
                                                                </td>
                                                                <td class="px-4 py-4 text-sm text-gray-900 font-bold text-center">
                                                                    {{ floatval($shift->registered_hours) }} hr(s)
                                                                </td>
                                                                <td class="px-4 py-4 text-sm text-gray-500 italic">
                                                                    {{ $shift->description ?? '-' }}
                                                                </td>
                                                                <td class="px-4 py-4 text-center">
                                                                    <div class="flex justify-center items-center space-x-3">
                                                                        <a href="{{ route('shifts.edit', $shift) }}" class="text-yellow-600 hover:text-yellow-700 transition-colors">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                                                                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                                                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                                                            </svg>
                                                                        </a>
                                                                        <form action="{{ route('shifts.destroy', $shift) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this shift?');" class="inline">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="text-pink-600 hover:text-pink-700 transition-colors">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                                </svg>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                     @empty
                                        <p class="text-gray-500 italic text-sm text-center py-4">No working schedules defined for this department.</p>
                                     @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
