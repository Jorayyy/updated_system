<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New HR Policy') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('hr-policies.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Title -->
                        <div class="mb-4">
                            <x-input-label for="title" :value="__('Policy Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Content -->
                        <div class="mb-4">
                            <x-input-label for="content" :value="__('Policy Content / Description')" />
                            <textarea name="content" id="content" rows="10" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>{{ old('content') }}</textarea>
                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                        </div>

                        <!-- Effective Date -->
                        <div class="mb-4">
                            <x-input-label for="effective_date" :value="__('Effective Date')" />
                            <x-text-input id="effective_date" class="block mt-1 w-full" type="date" name="effective_date" :value="old('effective_date')" required />
                            <x-input-error :messages="$errors->get('effective_date')" class="mt-2" />
                        </div>

                        <!-- Attachment -->
                        <div class="mb-4">
                            <x-input-label for="attachment" :value="__('PDF Attachment (Optional)')" />
                            <input id="attachment" type="file" name="attachment" accept=".pdf,.doc,.docx" class="block mt-1 w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100" />
                            <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
                        </div>

                        <!-- Published Status -->
                        <div class="block mt-4 mb-4">
                            <label for="is_published" class="inline-flex items-center">
                                <input id="is_published" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600">{{ __('Publish Immediately') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('hr-policies.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <x-primary-button>
                                {{ __('Create Policy') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
