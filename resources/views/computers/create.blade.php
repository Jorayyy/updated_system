<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('computers.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Register New PC') }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('computers.store') }}">
                @csrf

                <div class="space-y-6">
                    <!-- PC Number -->
                    <div>
                        <label for="pc_number" class="block text-sm font-medium text-gray-700 mb-2">
                            PC Number <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="pc_number" id="pc_number" value="{{ old('pc_number') }}" 
                            class="w-full rounded-lg border-gray-300 @error('pc_number') border-red-500 @enderror"
                            placeholder="e.g., PC-001" required>
                        @error('pc_number')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Name/Description
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" 
                            class="w-full rounded-lg border-gray-300"
                            placeholder="e.g., Workstation A">
                    </div>

                    <!-- Location -->
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                            Location
                        </label>
                        <input type="text" name="location" id="location" value="{{ old('location') }}" 
                            class="w-full rounded-lg border-gray-300"
                            placeholder="e.g., Floor 2, Area A">
                    </div>

                    <!-- Specs -->
                    <div>
                        <label for="specs" class="block text-sm font-medium text-gray-700 mb-2">
                            Specifications
                        </label>
                        <textarea name="specs" id="specs" rows="3" 
                            class="w-full rounded-lg border-gray-300"
                            placeholder="e.g., Intel i5, 8GB RAM, 256GB SSD">{{ old('specs') }}</textarea>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes
                        </label>
                        <textarea name="notes" id="notes" rows="2" 
                            class="w-full rounded-lg border-gray-300"
                            placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('computers.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Register PC
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
