<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('settings.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Company Information') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('settings.company.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Company Name -->
                            <div>
                                <x-input-label for="company_name" :value="__('Company Name')" />
                                <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" 
                                    :value="old('company_name', $settings['company_name'] ?? '')" required />
                                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                            </div>

                            <!-- Company Address -->
                            <div>
                                <x-input-label for="company_address" :value="__('Company Address')" />
                                <textarea id="company_address" name="company_address" rows="3" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                                <x-input-error :messages="$errors->get('company_address')" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Company Phone -->
                                <div>
                                    <x-input-label for="company_phone" :value="__('Phone Number')" />
                                    <x-text-input id="company_phone" name="company_phone" type="text" class="mt-1 block w-full" 
                                        :value="old('company_phone', $settings['company_phone'] ?? '')" />
                                    <x-input-error :messages="$errors->get('company_phone')" class="mt-2" />
                                </div>

                                <!-- Company Email -->
                                <div>
                                    <x-input-label for="company_email" :value="__('Email Address')" />
                                    <x-text-input id="company_email" name="company_email" type="email" class="mt-1 block w-full" 
                                        :value="old('company_email', $settings['company_email'] ?? '')" />
                                    <x-input-error :messages="$errors->get('company_email')" class="mt-2" />
                                </div>
                            </div>

                            <hr class="my-6">
                            <h3 class="text-lg font-medium text-gray-900">Government Registration Numbers</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- TIN -->
                                <div>
                                    <x-input-label for="company_tin" :value="__('TIN')" />
                                    <x-text-input id="company_tin" name="company_tin" type="text" class="mt-1 block w-full" 
                                        :value="old('company_tin', $settings['company_tin'] ?? '')" />
                                    <x-input-error :messages="$errors->get('company_tin')" class="mt-2" />
                                </div>

                                <!-- SSS -->
                                <div>
                                    <x-input-label for="company_sss" :value="__('SSS Employer Number')" />
                                    <x-text-input id="company_sss" name="company_sss" type="text" class="mt-1 block w-full" 
                                        :value="old('company_sss', $settings['company_sss'] ?? '')" />
                                    <x-input-error :messages="$errors->get('company_sss')" class="mt-2" />
                                </div>

                                <!-- PhilHealth -->
                                <div>
                                    <x-input-label for="company_philhealth" :value="__('PhilHealth Employer Number')" />
                                    <x-text-input id="company_philhealth" name="company_philhealth" type="text" class="mt-1 block w-full" 
                                        :value="old('company_philhealth', $settings['company_philhealth'] ?? '')" />
                                    <x-input-error :messages="$errors->get('company_philhealth')" class="mt-2" />
                                </div>

                                <!-- Pag-IBIG -->
                                <div>
                                    <x-input-label for="company_pagibig" :value="__('Pag-IBIG Employer Number')" />
                                    <x-text-input id="company_pagibig" name="company_pagibig" type="text" class="mt-1 block w-full" 
                                        :value="old('company_pagibig', $settings['company_pagibig'] ?? '')" />
                                    <x-input-error :messages="$errors->get('company_pagibig')" class="mt-2" />
                                </div>
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
