<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('settings.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Attendance Settings') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('settings.attendance.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">Work Schedule</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Work Start Time -->
                                <div>
                                    <x-input-label for="work_start_time" :value="__('Work Start Time')" />
                                    <x-text-input id="work_start_time" name="work_start_time" type="time" class="mt-1 block w-full" 
                                        :value="old('work_start_time', $settings['work_start_time'] ?? '08:00')" required />
                                    <x-input-error :messages="$errors->get('work_start_time')" class="mt-2" />
                                </div>

                                <!-- Work End Time -->
                                <div>
                                    <x-input-label for="work_end_time" :value="__('Work End Time')" />
                                    <x-text-input id="work_end_time" name="work_end_time" type="time" class="mt-1 block w-full" 
                                        :value="old('work_end_time', $settings['work_end_time'] ?? '17:00')" required />
                                    <x-input-error :messages="$errors->get('work_end_time')" class="mt-2" />
                                </div>
                            </div>

                            <hr class="my-6">
                            <h3 class="text-lg font-medium text-gray-900">Time Policies</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Grace Period -->
                                <div>
                                    <x-input-label for="grace_period_minutes" :value="__('Grace Period (minutes)')" />
                                    <x-text-input id="grace_period_minutes" name="grace_period_minutes" type="number" class="mt-1 block w-full" 
                                        :value="old('grace_period_minutes', $settings['grace_period_minutes'] ?? 15)" required />
                                    <p class="mt-1 text-sm text-gray-500">Time allowed after work start before marked late</p>
                                    <x-input-error :messages="$errors->get('grace_period_minutes')" class="mt-2" />
                                </div>

                                <!-- Break Duration -->
                                <div>
                                    <x-input-label for="break_duration_minutes" :value="__('Break Duration (minutes)')" />
                                    <x-text-input id="break_duration_minutes" name="break_duration_minutes" type="number" class="mt-1 block w-full" 
                                        :value="old('break_duration_minutes', $settings['break_duration_minutes'] ?? 60)" required />
                                    <p class="mt-1 text-sm text-gray-500">Standard lunch break duration</p>
                                    <x-input-error :messages="$errors->get('break_duration_minutes')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Require Break -->
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="require_break" value="1" 
                                        {{ old('require_break', $settings['require_break'] ?? true) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Require employees to log break time') }}</span>
                                </label>
                            </div>

                            <hr class="my-6">
                            <h3 class="text-lg font-medium text-gray-900">IP Restriction</h3>

                            <!-- IP Restriction -->
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="attendance_ip_restriction" value="1" 
                                        {{ old('attendance_ip_restriction', $settings['attendance_ip_restriction'] ?? false) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Restrict attendance recording to allowed IPs only') }}</span>
                                </label>
                                <p class="mt-1 text-sm text-gray-500 ml-6">
                                    When enabled, employees can only record attendance from registered IP addresses.
                                    <a href="{{ route('settings.allowed-ips') }}" class="text-indigo-600 hover:text-indigo-700">Manage allowed IPs</a>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Save Changes') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
