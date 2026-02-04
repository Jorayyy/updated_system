<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manual Attendance Entry') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('attendance.store') }}">
                        @csrf

                        <div class="space-y-6">
                            <!-- Employee -->
                            <div>
                                <x-input-label for="user_id" :value="__('Employee')" />
                                <select name="user_id" id="user_id" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('user_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }} ({{ $employee->employee_id }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                            </div>

                            <!-- Date -->
                            <div>
                                <x-input-label for="date" :value="__('Date')" />
                                <x-text-input type="date" name="date" id="date" 
                                    class="mt-1 block w-full"
                                    :value="old('date', date('Y-m-d'))" 
                                    max="{{ date('Y-m-d') }}"
                                    required />
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>

                            <!-- Time In -->
                            <div>
                                <x-input-label for="time_in" :value="__('Time In')" />
                                <x-text-input type="time" name="time_in" id="time_in" 
                                    class="mt-1 block w-full"
                                    :value="old('time_in')" />
                                <x-input-error :messages="$errors->get('time_in')" class="mt-2" />
                            </div>

                            <!-- Time Out -->
                            <div>
                                <x-input-label for="time_out" :value="__('Time Out')" />
                                <x-text-input type="time" name="time_out" id="time_out" 
                                    class="mt-1 block w-full"
                                    :value="old('time_out')" />
                                <p class="text-xs text-gray-500 mt-1">Leave empty if employee hasn't clocked out yet</p>
                                <x-input-error :messages="$errors->get('time_out')" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select name="status" id="status" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Late</option>
                                    <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="on_leave" {{ old('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                    <option value="half_day" {{ old('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                            <!-- Remarks -->
                            <div>
                                <x-input-label for="remarks" :value="__('Remarks')" />
                                <textarea name="remarks" id="remarks" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Reason for manual entry...">{{ old('remarks') }}</textarea>
                                <x-input-error :messages="$errors->get('remarks')" class="mt-2" />
                            </div>

                            <!-- Submit -->
                            <div class="flex items-center justify-end gap-4 pt-4 border-t">
                                <a href="{{ route('attendance.manage') }}" class="text-gray-600 hover:text-gray-800">
                                    Cancel
                                </a>
                                <x-primary-button>
                                    {{ __('Save Attendance') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
