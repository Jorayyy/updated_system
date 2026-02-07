<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Attendance') }}: {{ $attendance->user->name }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Employee Info -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Employee</div>
                                <div class="font-medium">{{ $attendance->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $attendance->user->employee_id }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Date</div>
                                <div class="font-medium">{{ $attendance->date->format('l, F d, Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('attendance.update', $attendance) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Time In -->
                            <div>
                                <x-input-label for="time_in" :value="__('Time In')" />
                                <x-text-input type="time" name="time_in" id="time_in" 
                                    class="mt-1 block w-full"
                                    :value="old('time_in', $attendance->time_in ? $attendance->time_in->format('H:i') : '')" />
                                <x-input-error :messages="$errors->get('time_in')" class="mt-2" />
                            </div>

                            <!-- Time Out -->
                            <div>
                                <x-input-label for="time_out" :value="__('Time Out')" />
                                <x-text-input type="time" name="time_out" id="time_out" 
                                    class="mt-1 block w-full"
                                    :value="old('time_out', $attendance->time_out ? $attendance->time_out->format('H:i') : '')" />
                                <x-input-error :messages="$errors->get('time_out')" class="mt-2" />
                            </div>

                            <!-- Breaks Section -->
                            <div class="border-t border-b py-4 space-y-4 bg-blue-50/30 px-4 rounded-lg">
                                <h3 class="font-bold text-sm text-blue-800 uppercase tracking-widest flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Break Management
                                </h3>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- 1st Break -->
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-gray-700">1st Break OUT</label>
                                        <x-text-input type="time" name="first_break_out" class="block w-full text-sm" 
                                            :value="old('first_break_out', $attendance->first_break_out ? $attendance->first_break_out->format('H:i') : '')" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-gray-700">1st Break IN</label>
                                        <x-text-input type="time" name="first_break_in" class="block w-full text-sm" 
                                            :value="old('first_break_in', $attendance->first_break_in ? $attendance->first_break_in->format('H:i') : '')" />
                                    </div>

                                    <!-- Lunch Break -->
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-gray-700">Lunch Break OUT</label>
                                        <x-text-input type="time" name="lunch_break_out" class="block w-full text-sm" 
                                            :value="old('lunch_break_out', $attendance->lunch_break_out ? $attendance->lunch_break_out->format('H:i') : '')" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-gray-700">Lunch Break IN</label>
                                        <x-text-input type="time" name="lunch_break_in" class="block w-full text-sm" 
                                            :value="old('lunch_break_in', $attendance->lunch_break_in ? $attendance->lunch_break_in->format('H:i') : '')" />
                                    </div>

                                    <!-- 2nd Break -->
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-gray-700">2nd Break OUT</label>
                                        <x-text-input type="time" name="second_break_out" class="block w-full text-sm" 
                                            :value="old('second_break_out', $attendance->second_break_out ? $attendance->second_break_out->format('H:i') : '')" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-gray-700">2nd Break IN</label>
                                        <x-text-input type="time" name="second_break_in" class="block w-full text-sm" 
                                            :value="old('second_break_in', $attendance->second_break_in ? $attendance->second_break_in->format('H:i') : '')" />
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select name="status" id="status" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="present" {{ old('status', $attendance->status) == 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="late" {{ old('status', $attendance->status) == 'late' ? 'selected' : '' }}>Late</option>
                                    <option value="absent" {{ old('status', $attendance->status) == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="on_leave" {{ old('status', $attendance->status) == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                    <option value="half_day" {{ old('status', $attendance->status) == 'half_day' ? 'selected' : '' }}>Half Day</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                            <!-- Work Minutes Override -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="total_work_minutes" :value="__('Work Minutes (Override)')" />
                                    <x-text-input type="number" name="total_work_minutes" id="total_work_minutes" 
                                        class="mt-1 block w-full"
                                        :value="old('total_work_minutes', $attendance->total_work_minutes)" 
                                        min="0" />
                                    <p class="text-xs text-gray-500 mt-1">Leave as calculated or override manually</p>
                                    <x-input-error :messages="$errors->get('total_work_minutes')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="total_break_minutes" :value="__('Break Minutes')" />
                                    <x-text-input type="number" name="total_break_minutes" id="total_break_minutes" 
                                        class="mt-1 block w-full"
                                        :value="old('total_break_minutes', $attendance->total_break_minutes)" 
                                        min="0" />
                                    <x-input-error :messages="$errors->get('total_break_minutes')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Overtime/Undertime -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="overtime_minutes" :value="__('Overtime Minutes')" />
                                    <x-text-input type="number" name="overtime_minutes" id="overtime_minutes" 
                                        class="mt-1 block w-full"
                                        :value="old('overtime_minutes', $attendance->overtime_minutes)" 
                                        min="0" />
                                    <x-input-error :messages="$errors->get('overtime_minutes')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="undertime_minutes" :value="__('Undertime Minutes')" />
                                    <x-text-input type="number" name="undertime_minutes" id="undertime_minutes" 
                                        class="mt-1 block w-full"
                                        :value="old('undertime_minutes', $attendance->undertime_minutes)" 
                                        min="0" />
                                    <x-input-error :messages="$errors->get('undertime_minutes')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div>
                                <x-input-label for="remarks" :value="__('Remarks')" />
                                <textarea name="remarks" id="remarks" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Reason for editing...">{{ old('remarks', $attendance->remarks) }}</textarea>
                                <x-input-error :messages="$errors->get('remarks')" class="mt-2" />
                            </div>

                            <!-- Submit -->
                            <div class="flex items-center justify-end gap-4 pt-4 border-t">
                                <a href="{{ route('attendance.manage') }}" class="text-gray-600 hover:text-gray-800">
                                    Cancel
                                </a>
                                <x-primary-button>
                                    {{ __('Update Attendance') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
