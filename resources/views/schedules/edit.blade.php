<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-red-800 text-white p-4 -m-4 sm:-m-6 lg:-m-8 mb-4">
            <h2 class="font-bold text-lg uppercase tracking-wider">
                MAASIN ADMINS
            </h2>
            <a href="{{ route('schedules.index') }}" class="hover:opacity-80">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-blue-50/50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100">
                <div class="p-8">
                    {{-- User Details Section --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-2 mb-8 text-sm">
                        <div class="grid grid-cols-2 border-b border-gray-50 pb-2">
                            <span class="font-semibold text-gray-500 uppercase text-[10px] tracking-widest">Employee ID</span>
                            <span class="font-bold text-blue-800">{{ $user->employee_id }}</span>
                        </div>
                        <div class="grid grid-cols-2 border-b border-gray-50 pb-2">
                            <span class="font-semibold text-gray-500 uppercase text-[10px] tracking-widest">Employee Name</span>
                            <span class="font-bold text-gray-800 uppercase">{{ $user->name }}</span>
                        </div>
                        <div class="grid grid-cols-2 border-b border-gray-50 pb-2">
                            <span class="font-semibold text-gray-500 uppercase text-[10px] tracking-widest">Company</span>
                            <span class="text-gray-800">{{ $user->site->name ?? 'Mancao Electronic Connect Business Solutions OPC' }}</span>
                        </div>
                        <div class="grid grid-cols-2 border-b border-gray-50 pb-2">
                            <span class="font-semibold text-gray-500 uppercase text-[10px] tracking-widest">Classification</span>
                            <span class="font-bold text-gray-800 uppercase">{{ $user->classification ?? 'STAFF' }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('schedules.update', $user->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                            {{-- Day Dropdowns --}}
                            @php
                                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            @endphp

                            <div class="space-y-4">
                                @foreach($days as $day)
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $day }}</label>
                                        <select name="{{ $day }}_schedule" class="w-full border-gray-200 rounded text-sm focus:ring-red-500 focus:border-red-500">
                                            <option value="Rest day" {{ $user->{$day.'_schedule'} == 'Rest day' ? 'selected' : '' }}>Rest day</option>
                                            @foreach($schedules as $shift)
                                                @php
                                                    $shiftTime = \Carbon\Carbon::parse($shift->work_start_time)->format('H:i') . ' to ' . \Carbon\Carbon::parse($shift->work_end_time)->format('H:i');
                                                @endphp
                                                <option value="{{ $shiftTime }}" {{ $user->{$day.'_schedule'} == $shiftTime ? 'selected' : '' }}>
                                                    {{ $shiftTime }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Special Options Section --}}
                            <div>
                                <div class="space-y-8">
                                    <div>
                                        <div class="flex items-center mb-2">
                                            <label class="font-black text-[11px] text-gray-800 uppercase tracking-widest mr-2">Special 1 Hour Schedule Only</label>
                                            <input type="hidden" name="special_1_hour_only" value="0">
                                            <input type="checkbox" name="special_1_hour_only" value="1" {{ $user->special_1_hour_only ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        </div>
                                        <ul class="text-[10px] text-gray-500 list-disc ml-4 space-y-1 bg-gray-50 p-3 rounded border border-gray-100 italic">
                                            <li>employees who render duty of 1 hour per day but is being paid for a whole day duty</li>
                                            <li>must render duty within the plotted schedule</li>
                                            <li>duty outside the plotted schedule, employee will be tag as absent</li>
                                            <li>if undertime, employee will be tag as absent</li>
                                            <li>late deduction is applied in dtr, turn off late deduction in payroll</li>
                                            <li>no overtime computation</li>
                                        </ul>
                                    </div>

                                    <div>
                                        <div class="flex items-center mb-2">
                                            <label class="font-black text-[11px] text-gray-800 uppercase tracking-widest mr-2">Special Case As Long as With In & Out Present Policy</label>
                                            <input type="hidden" name="special_case_policy" value="0">
                                            <input type="checkbox" name="special_case_policy" value="1" {{ $user->special_case_policy ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        </div>
                                        <ul class="text-[10px] text-gray-500 list-disc ml-4 space-y-1 bg-gray-50 p-3 rounded border border-gray-100 italic">
                                            <li>employees who had in & out regardless of total hrs & is being paid for a whole day duty</li>
                                            <li>no overtime computation</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-12">
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-4 rounded shadow-lg transition-all transform hover:-translate-y-0.5 active:translate-y-0 uppercase tracking-widest text-xs flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                SAVE CHANGES
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
