<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('computers.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit PC') }}: {{ $computer->pc_number }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('computers.update', $computer) }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- PC Number -->
                    <div>
                        <label for="pc_number" class="block text-sm font-medium text-gray-700 mb-2">
                            PC Number <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="pc_number" id="pc_number" value="{{ old('pc_number', $computer->pc_number) }}" 
                            class="w-full rounded-lg border-gray-300 @error('pc_number') border-red-500 @enderror"
                            required>
                        @error('pc_number')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Name/Description
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $computer->name) }}" 
                            class="w-full rounded-lg border-gray-300">
                    </div>

                    <!-- Location -->
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                            Location
                        </label>
                        <input type="text" name="location" id="location" value="{{ old('location', $computer->location) }}" 
                            class="w-full rounded-lg border-gray-300">
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status" class="w-full rounded-lg border-gray-300" required>
                            <option value="available" {{ old('status', $computer->status) === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="in_use" {{ old('status', $computer->status) === 'in_use' ? 'selected' : '' }}>In Use</option>
                            <option value="maintenance" {{ old('status', $computer->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="retired" {{ old('status', $computer->status) === 'retired' ? 'selected' : '' }}>Retired</option>
                        </select>
                    </div>

                    <!-- Specs -->
                    <div>
                        <label for="specs" class="block text-sm font-medium text-gray-700 mb-2">
                            Specifications
                        </label>
                        <textarea name="specs" id="specs" rows="3" 
                            class="w-full rounded-lg border-gray-300">{{ old('specs', $computer->specs) }}</textarea>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes
                        </label>
                        <textarea name="notes" id="notes" rows="2" 
                            class="w-full rounded-lg border-gray-300">{{ old('notes', $computer->notes) }}</textarea>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $computer->is_active) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                    </div>

                    <!-- Current User Info -->
                    @if($computer->currentUser)
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <strong>Currently assigned to:</strong> {{ $computer->currentUser->name }} ({{ $computer->currentUser->employee_id }})
                                <br>
                                <strong>Since:</strong> {{ $computer->assigned_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('computers.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Update PC
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
