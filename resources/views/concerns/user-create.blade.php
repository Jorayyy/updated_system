<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Submit a Concern') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('concerns.user-store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="space-y-6">
                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">
                                    Subject / Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="title" id="title" value="{{ old('title', request('title')) }}" required
                                       placeholder="Brief description of your concern"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('title')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Category -->
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700">
                                        Category <span class="text-red-500">*</span>
                                    </label>
                                    <select name="category" id="category" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select a category</option>
                                        @foreach($categories as $key => $label)
                                            <option value="{{ $key }}" {{ old('category', request('category')) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Priority -->
                                <div>
                                    <label for="priority" class="block text-sm font-medium text-gray-700">
                                        Priority <span class="text-red-500">*</span>
                                    </label>
                                    <select name="priority" id="priority" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach($priorities as $key => $label)
                                            <option value="{{ $key }}" {{ old('priority', 'medium') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('priority')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Location (Optional) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="location" class="block text-sm font-medium text-gray-700">
                                        Location / Site
                                    </label>
                                    <select name="location" id="location"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select a Location / Site (Optional)</option>
                                        @foreach($sites as $id => $name)
                                            <option value="{{ $name }}" {{ old('location') == $name ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('location')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Date Affected (TK Specific) -->
                                <div id="date_affected_container" class="space-y-4" style="{{ old('category', request('category')) == 'timekeeping' ? '' : 'display: none;' }}">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="date_affected" class="block text-sm font-medium text-gray-700">
                                                Date Affected <span class="text-red-500 font-bold">*</span>
                                            </label>
                                            <input type="date" name="date_affected" id="date_affected" value="{{ old('date_affected') }}"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('date_affected') border-red-500 @enderror">
                                            @error('date_affected')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="affected_punch" class="block text-sm font-medium text-gray-700">
                                                Affected Punch <span class="text-red-500 font-bold">*</span>
                                            </label>
                                            <select name="affected_punch" id="affected_punch"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('affected_punch') border-red-500 @enderror">
                                                <option value="">Select Punch Type</option>
                                                @php
                                                    $punches = ['IN', '1st BREAK OUT', '1st BREAK IN', 'LUNCH BREAK OUT', 'LUNCH BREAK IN', '2nd BREAK OUT', '2nd BREAK IN', 'OUT'];
                                                @endphp
                                                @foreach($punches as $punch)
                                                    <option value="{{ $punch }}" {{ old('affected_punch') == $punch ? 'selected' : '' }}>{{ $punch }}</option>
                                                @endforeach
                                            </select>
                                            @error('affected_punch')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <p class="text-xs text-blue-600 font-bold italic flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                        Select the date and specific punch time involving the discrepancy.
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">
                                    Description <span class="text-red-500">*</span>
                                </label>
                                <textarea name="description" id="description" rows="5" required
                                          placeholder="Please provide detailed information about your concern. Include any relevant details that can help us address it faster."
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', request('description')) }}</textarea>
                                @error('description')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Attachment -->
                            <div>
                                <label for="attachment" class="block text-sm font-medium text-gray-700">
                                    Attachment (Optional)
                                </label>
                                <input type="file" name="attachment" id="attachment"
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="mt-1 text-xs text-gray-500">Max size: 5MB. Formats: JPG, PNG, PDF, DOCX.</p>
                                @error('attachment')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_confidential" id="is_confidential" value="1" 
                                       {{ old('is_confidential') ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_confidential" class="ml-2 block text-sm text-gray-900">
                                    Mark as Confidential (Only high-level HR/Admin can see)
                                </label>
                            </div>

                            <!-- Info Box -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="text-sm text-blue-700">
                                        <p class="font-medium">What happens next?</p>
                                        <ul class="mt-1 list-disc list-inside space-y-1">
                                            <li>Your concern will be assigned a ticket number</li>
                                            <li>HR/Admin will review and assign to the appropriate person</li>
                                            <li>You'll be able to track the status and add comments</li>
                                            <li>You'll be notified when there are updates</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('concerns.my') }}" 
                                   class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                                    Cancel
                                </a>
                                <button type="submit" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                    Submit Concern
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category');
            const dateAffectedContainer = document.getElementById('date_affected_container');
            const dateAffectedInput = document.getElementById('date_affected');
            const affectedPunchSelect = document.getElementById('affected_punch');

            function toggleFields() {
                const category = categorySelect.value;
                if (category === 'timekeeping') {
                    dateAffectedContainer.style.display = 'block';
                    dateAffectedInput.setAttribute('required', 'required');
                    affectedPunchSelect.setAttribute('required', 'required');
                } else {
                    dateAffectedContainer.style.display = 'none';
                    dateAffectedInput.removeAttribute('required');
                    affectedPunchSelect.removeAttribute('required');
                }
            }

            categorySelect.addEventListener('change', toggleFields);
            
            // Run on load to handle pre-filled forms
            toggleFields();
        });
    </script>
</x-app-layout>
