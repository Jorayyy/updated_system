<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-transparent">
            <h2 class="font-bold text-xl text-green-900 leading-tight flex items-center">
                <span class="mr-2">ℹ️</span>
                {{ App\Models\CompanySetting::getValue('company_name', 'Mancao Electronic Connect Business Solutions OPC') }}
            </h2>
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700">Home</a>
                <span>&gt;</span>
                <a href="{{ route('shifts.index') }}" class="hover:text-gray-700">Time</a>
                <span>&gt;</span>
                <span class="text-gray-400">Shift Table</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-white min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-8">
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-700 flex items-center border-b pb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Working Schedule Reference ({{ $shift->category }})
                    </h3>
                </div>

                <form method="POST" action="{{ route('shifts.update', $shift) }}" class="space-y-6 max-w-4xl">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="category" value="{{ $shift->category }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">Target Payroll Group</label>
                        <div class="md:col-span-2">
                            <select name="payroll_group_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 py-2">
                                <option value="">No Group Selected</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ $shift->payroll_group_id == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('payroll_group_id')" class="mt-2" />
                        </div>
                    </div>

                    @php
                        $dtIn = \Carbon\Carbon::parse($shift->time_in);
                        $time_in_hh = $dtIn->format('h');
                        $time_in_mm = $dtIn->format('i');
                        $time_in_p  = $dtIn->format('A');

                        $dtOut = \Carbon\Carbon::parse($shift->time_out);
                        $time_out_hh = $dtOut->format('h');
                        $time_out_mm = $dtOut->format('i');
                        $time_out_p  = $dtOut->format('A');
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">Time IN</label>
                        <div class="md:col-span-2 flex items-center space-x-2">
                            <select name="time_in_hh" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                @for($i=1; $i<=12; $i++)
                                    <option value="{{ sprintf('%02d', $i) }}" {{ sprintf('%02d', $i) == $time_in_hh ? 'selected' : '' }}>{{ sprintf('%02d', $i) }}</option>
                                @endfor
                            </select>
                            <span class="font-bold">:</span>
                            <select name="time_in_mm" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                @for($i=0; $i<60; $i+=5)
                                    <option value="{{ sprintf('%02d', $i) }}" {{ sprintf('%02d', $i) == $time_in_mm ? 'selected' : '' }}>{{ sprintf('%02d', $i) }}</option>
                                @endfor
                            </select>
                            <select name="time_in_p" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                <option value="AM" {{ $time_in_p == 'AM' ? 'selected' : '' }}>AM</option>
                                <option value="PM" {{ $time_in_p == 'PM' ? 'selected' : '' }}>PM</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">Time OUT</label>
                        <div class="md:col-span-2 flex items-center space-x-2">
                            <select name="time_out_hh" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                @for($i=1; $i<=12; $i++)
                                    <option value="{{ sprintf('%02d', $i) }}" {{ sprintf('%02d', $i) == $time_out_hh ? 'selected' : '' }}>{{ sprintf('%02d', $i) }}</option>
                                @endfor
                            </select>
                            <span class="font-bold">:</span>
                            <select name="time_out_mm" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                @for($i=0; $i<60; $i+=5)
                                    <option value="{{ sprintf('%02d', $i) }}" {{ sprintf('%02d', $i) == $time_out_mm ? 'selected' : '' }}>{{ sprintf('%02d', $i) }}</option>
                                @endfor
                            </select>
                            <select name="time_out_p" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                <option value="AM" {{ $time_out_p == 'AM' ? 'selected' : '' }}>AM</option>
                                <option value="PM" {{ $time_out_p == 'PM' ? 'selected' : '' }}>PM</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">Lunch Break</label>
                        <div class="md:col-span-2">
                            <select name="lunch_break_minutes" class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                @foreach([0, 15, 30, 45, 60] as $m)
                                    <option value="{{ $m }}" {{ $shift->lunch_break_minutes == $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">1st Break</label>
                        <div class="md:col-span-2 flex items-center space-x-4">
                            <select name="first_break_minutes" class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                @foreach([0, 15, 30] as $m)
                                    <option value="{{ $m }}" {{ $shift->first_break_minutes == $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                            <div class="flex items-center">
                                <input type="checkbox" name="has_first_break" id="has_first_break" value="1" {{ $shift->has_first_break ? 'checked' : '' }} class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                <label for="has_first_break" class="ml-2 text-xs text-gray-600 font-bold uppercase">Enabled</label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">2nd Break</label>
                        <div class="md:col-span-2 flex items-center space-x-4">
                            <select name="second_break_minutes" class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                @foreach([0, 15, 30] as $m)
                                    <option value="{{ $m }}" {{ $shift->second_break_minutes == $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                            <div class="flex items-center">
                                <input type="checkbox" name="has_second_break" id="has_second_break" value="1" {{ $shift->has_second_break ? 'checked' : '' }} class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                <label for="has_second_break" class="ml-2 text-xs text-gray-600 font-bold uppercase">Enabled</label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">Registered Hours</label>
                        <div class="md:col-span-2">
                            <select name="registered_hours" class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm text-red-700 text-center font-bold">
                                @for($i=0; $i<=12; $i++)
                                    <option value="{{ $i }}" {{ floatval($shift->registered_hours) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">Description</label>
                        <div class="md:col-span-2">
                            <input type="text" name="description" value="{{ $shift->description }}" placeholder="Description" class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm italic">
                        </div>
                    </div>

                    <div class="flex justify-end pt-6 space-x-3">
                        <a href="{{ route('shifts.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-md shadow-sm transition flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back
                        </a>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-md shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
