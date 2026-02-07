<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Leave Type') }}: {{ $leaveType->name }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('leave-types.update', $leaveType) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Leave Type Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" 
                                    :value="old('name', $leaveType->name)" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Code -->
                            <div>
                                <x-input-label for="code" :value="__('Code')" />
                                <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" 
                                    :value="old('code', $leaveType->code)" required maxlength="10" />
                                <p class="text-xs text-gray-500 mt-1">Short code for the leave type (max 10 characters)</p>
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>

                            <!-- Description -->
                            <div>
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description" name="description" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $leaveType->description) }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <!-- Max Days -->
                            <div>
                                <x-input-label for="max_days" :value="__('Maximum Days Per Year')" />
                                <x-text-input id="max_days" name="max_days" type="number" class="mt-1 block w-full" 
                                    :value="old('max_days', $leaveType->max_days)" required min="0" max="365" />
                                <x-input-error :messages="$errors->get('max_days')" class="mt-2" />
                            </div>

                            <!-- Color -->
                            <div>
                                <x-input-label for="color" :value="__('Color')" />
                                <div class="flex items-center gap-3 mt-1">
                                    <input type="color" id="color" name="color" 
                                        value="{{ old('color', $leaveType->color ?? '#6366f1') }}"
                                        class="h-10 w-20 border-gray-300 rounded cursor-pointer">
                                    <span class="text-sm text-gray-500">Used for visual identification in calendars and lists</span>
                                </div>
                                <x-input-error :messages="$errors->get('color')" class="mt-2" />
                            </div>

                            <!-- Options -->
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="is_paid" name="is_paid" value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        {{ old('is_paid', $leaveType->is_paid) ? 'checked' : '' }}>
                                    <label for="is_paid" class="ml-2 text-sm text-gray-700">
                                        Paid Leave
                                        <span class="text-gray-500">(Employee receives salary during this leave)</span>
                                    </label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" id="requires_attachment" name="requires_attachment" value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        {{ old('requires_attachment', $leaveType->requires_attachment) ? 'checked' : '' }}>
                                    <label for="requires_attachment" class="ml-2 text-sm text-gray-700">
                                        Requires Attachment
                                        <span class="text-gray-500">(e.g., Medical certificate for Sick Leave)</span>
                                    </label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" id="is_active" name="is_active" value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        {{ old('is_active', $leaveType->is_active) ? 'checked' : '' }}>
                                    <label for="is_active" class="ml-2 text-sm text-gray-700">
                                        Active
                                        <span class="text-gray-500">(Employees can request this leave type)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="flex items-center justify-end gap-4 pt-4 border-t">
                                <a href="{{ route('leave-types.index') }}" class="text-gray-600 hover:text-gray-800">
                                    Cancel
                                </a>
                                <x-primary-button>
                                    {{ __('Update Leave Type') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
