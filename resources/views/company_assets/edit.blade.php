<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Company Asset') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('company-assets.update', $companyAsset) }}">
                        @csrf
                        @method('PUT')

                        <!-- Asset Name -->
                        <div class="mb-4">
                            <x-input-label for="asset_name" :value="__('Asset Name / Model')" />
                            <x-text-input id="asset_name" class="block mt-1 w-full" type="text" name="asset_name" :value="old('asset_name', $companyAsset->name)" required />
                            <x-input-error :messages="$errors->get('asset_name')" class="mt-2" />
                        </div>

                        <!-- Serial Number -->
                        <div class="mb-4">
                            <x-input-label for="serial_number" :value="__('Serial Number')" />
                            <x-text-input id="serial_number" class="block mt-1 w-full" type="text" name="serial_number" :value="old('serial_number', $companyAsset->serial_number)" />
                            <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
                        </div>

                        <!-- Type -->
                        <div class="mb-4">
                            <x-input-label for="type" :value="__('Type (Laptop, Phone, Monitor, etc.)')" />
                            <x-text-input id="type" class="block mt-1 w-full" type="text" name="type" :value="old('type', $companyAsset->type)" required />
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Assigned To -->
                        <div class="mb-4">
                            <x-input-label for="user_id" :value="__('Assign To (Optional)')" />
                            <select id="user_id" name="user_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">- Unassigned -</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ old('user_id', $companyAsset->employee_id) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="active" {{ in_array(old('status', $companyAsset->status), ['available', 'assigned', 'active']) ? 'selected' : '' }}>Active (Good Condition)</option>
                                <option value="maintenance" {{ old('status', $companyAsset->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="retired" {{ old('status', $companyAsset->status) == 'retired' ? 'selected' : '' }}>Retired</option>
                                <option value="lost" {{ old('status', $companyAsset->status) == 'lost' ? 'selected' : '' }}>Lost/Stolen</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('company-assets.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">{{ __('Cancel') }}</a>
                            <x-primary-button>
                                {{ __('Update Asset') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>