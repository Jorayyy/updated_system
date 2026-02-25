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
                        Working Schedule Reference ({{ $category }})
                    </h3>
                </div>

                <form method="POST" action="{{ route('shifts.store') }}" class="space-y-6 max-w-4xl">
                    @csrf
                    <input type="hidden" name="category" value="{{ $category }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm pt-2">Payroll Groups</label>
                        <div class="md:col-span-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($groups as $group)
                                    <label class="inline-flex items-center space-x-3 cursor-pointer group">
                                        <input type="checkbox" name="payroll_group_ids[]" value="{{ $group->id }}" 
                                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 transition-all duration-200">
                                        <span class="text-xs font-black text-gray-700 uppercase tracking-widest group-hover:text-blue-600">{{ $group->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('payroll_group_ids')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">Time IN</label>
                        <div class="md:col-span-2 flex items-center space-x-2">
                            <select name="time_in_hh" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                @for($i=1; $i<=12; $i++)
                                    <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}</option>
                                @endfor
                            </select>
                            <span class="font-bold">:</span>
                            <select name="time_in_mm" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                @for($i=0; $i<60; $i+=5)
                                    <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}</option>
                                @endfor
                            </select>
                            <select name="time_in_p" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                <option value="AM">AM</option>
                                <option value="PM">PM</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">Time OUT</label>
                        <div class="md:col-span-2 flex items-center space-x-2">
                            <select name="time_out_hh" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                @for($i=1; $i<=12; $i++)
                                    <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}</option>
                                @endfor
                            </select>
                            <span class="font-bold">:</span>
                            <select name="time_out_mm" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                @for($i=0; $i<60; $i+=5)
                                    <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}</option>
                                @endfor
                            </select>
                            <select name="time_out_p" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                <option value="AM">AM</option>
                                <option value="PM" selected>PM</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">Lunch Break</label>
                        <div class="md:col-span-2">
                            <select name="lunch_break_minutes" class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                <option value="0">0</option>
                                <option value="15">15</option>
                                <option value="30">30</option>
                                <option value="45">45</option>
                                <option value="60" selected>60</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">1st Break</label>
                        <div class="md:col-span-2">
                            <select name="first_break_minutes" class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                <option value="0">0</option>
                                <option value="15" selected>15</option>
                                <option value="30">30</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">2nd Break</label>
                        <div class="md:col-span-2">
                            <select name="second_break_minutes" class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                <option value="0">0</option>
                                <option value="15" selected>15</option>
                                <option value="30">30</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">Registered Hours</label>
                        <div class="md:col-span-2">
                            <select name="registered_hours" class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm text-red-700">
                                @for($i=0; $i<=12; $i++)
                                    <option value="{{ $i }}" {{ $i == 8 ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <label class="font-bold text-gray-700 uppercase tracking-tight text-sm">Description</label>
                        <div class="md:col-span-2">
                            <input type="text" name="description" placeholder="Description" class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm italic">
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
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
