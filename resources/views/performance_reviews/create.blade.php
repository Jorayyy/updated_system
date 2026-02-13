<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Performance Review') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('performance-reviews.store') }}">
                        @csrf
                        <input type="hidden" name="reviewer_id" value="{{ auth()->id() }}">

                        <!-- Employee -->
                        <div class="mb-4">
                            <x-input-label for="employee_id" :value="__('Employee')" />
                            <select id="employee_id" name="employee_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="">Select Employee</option>
                                @foreach($employees ?? [] as $employee)
                                    <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('employee_id')" class="mt-2" />
                        </div>

                        <!-- Review Date -->
                        <div class="mb-4">
                            <x-input-label for="review_date" :value="__('Review Date')" />
                            <x-text-input id="review_date" class="block mt-1 w-full" type="date" name="review_date" :value="old('review_date', date('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('review_date')" class="mt-2" />
                        </div>

                        <!-- Review Period -->
                        <div class="mb-4">
                            <x-input-label for="review_period" :value="__('Review Period (e.g. Q1 2026, Annual 2025)')" />
                            <x-text-input id="review_period" class="block mt-1 w-full" type="text" name="review_period" :value="old('review_period')" required />
                            <x-input-error :messages="$errors->get('review_period')" class="mt-2" />
                        </div>

                        <!-- Rating -->
                        <div class="mb-4">
                            <x-input-label for="rating" :value="__('Rating (1-5)')" />
                            <select id="rating" name="rating" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Very Good</option>
                                <option value="3">3 - Satisfactory</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                            <x-input-error :messages="$errors->get('rating')" class="mt-2" />
                        </div>

                        <!-- Comments -->
                        <div class="mb-4">
                            <x-input-label for="comments" :value="__('Feedback / Comments')" />
                            <textarea name="comments" id="comments" rows="5" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('comments') }}</textarea>
                            <x-input-error :messages="$errors->get('comments')" class="mt-2" />
                        </div>

                        <!-- Goals -->
                        <div class="mb-4">
                            <x-input-label for="goals" :value="__('Goals for Next Period')" />
                            <textarea name="goals" id="goals" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('goals') }}</textarea>
                            <x-input-error :messages="$errors->get('goals')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('performance-reviews.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <x-primary-button>
                                {{ __('Save Review') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
