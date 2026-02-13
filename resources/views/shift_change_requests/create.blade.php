<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Shift Change Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('shift-change-requests.store') }}">
                        @csrf

                        <!-- Requested Date -->
                        <div class="mb-4">
                            <x-input-label for="requested_date" :value="__('Date of Shift')" />
                            <x-text-input id="requested_date" class="block mt-1 w-full" type="date" name="requested_date" :value="old('requested_date')" required />
                            <x-input-error :messages="$errors->get('requested_date')" class="mt-2" />
                        </div>

                        <!-- Current Schedule -->
                        <div class="mb-4">
                            <x-input-label for="current_schedule" :value="__('Current Schedule (e.g., 8:00 AM - 5:00 PM)')" />
                            <x-text-input id="current_schedule" class="block mt-1 w-full" type="text" name="current_schedule" :value="old('current_schedule')" required />
                            <x-input-error :messages="$errors->get('current_schedule')" class="mt-2" />
                        </div>

                        <!-- New Schedule -->
                        <div class="mb-4">
                            <x-input-label for="new_schedule" :value="__('Proposed Schedule (e.g., 9:00 AM - 6:00 PM)')" />
                            <x-text-input id="new_schedule" class="block mt-1 w-full" type="text" name="new_schedule" :value="old('new_schedule')" required />
                            <x-input-error :messages="$errors->get('new_schedule')" class="mt-2" />
                        </div>

                        <!-- Reason -->
                        <div class="mb-4">
                            <x-input-label for="reason" :value="__('Reason for Change')" />
                            <textarea name="reason" id="reason" rows="4" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('shift-change-requests.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <x-primary-button>
                                {{ __('Submit Request') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
