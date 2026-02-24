<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('concerns.show', $concern) }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Concern - {{ $concern->ticket_number }}
            </h2>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('concerns.update', $concern) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title', $concern->title) }}" required
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror"
                                   placeholder="Brief description of the concern">
                            @error('title')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category, Priority & Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                                    Category <span class="text-red-500">*</span>
                                </label>
                                <select name="category" id="category" required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('category') border-red-500 @enderror">
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ old('category', $concern->category) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                                    Priority <span class="text-red-500">*</span>
                                </label>
                                <select name="priority" id="priority" required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('priority') border-red-500 @enderror">
                                    @foreach($priorities as $key => $label)
                                        <option value="{{ $key }}" {{ old('priority', $concern->priority) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select name="status" id="status" required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-500 @enderror">
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', $concern->status) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                                    Location / Site
                                </label>
                                <select name="location" id="location" 
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('location') border-red-500 @enderror">
                                    <option value="">Select Location</option>
                                    @foreach($sites as $id => $name)
                                        <option value="{{ $name }}" {{ old('location', $concern->location) == $name ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('location')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div id="date_affected_container" class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-gray-50 rounded-xl border border-gray-200" style="{{ old('category', $concern->category) == 'timekeeping' ? '' : 'display: none;' }}">
                            <div>
                                <label for="date_affected" class="block text-sm font-medium text-gray-700 mb-1">
                                    Date Affected <span class="text-red-500 font-bold">*</span>
                                </label>
                                <input type="date" name="date_affected" id="date_affected" value="{{ old('date_affected', $concern->date_affected ? $concern->date_affected->format('Y-m-d') : '') }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('date_affected') border-red-500 @enderror">
                                @error('date_affected')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="affected_punch" class="block text-sm font-medium text-gray-700 mb-1">
                                    Affected Punch <span class="text-red-500 font-bold">*</span>
                                </label>
                                <select name="affected_punch" id="affected_punch" 
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('affected_punch') border-red-500 @enderror">
                                    <option value="">Select Punch</option>
                                    <option value="TIME IN" {{ old('affected_punch', $concern->affected_punch) == 'TIME IN' ? 'selected' : '' }}>TIME IN</option>
                                    <option value="TIME OUT" {{ old('affected_punch', $concern->affected_punch) == 'TIME OUT' ? 'selected' : '' }}>TIME OUT</option>
                                </select>
                                @error('affected_punch')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
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
                                        dateAffectedContainer.style.display = 'grid';
                                        dateAffectedInput.setAttribute('required', 'required');
                                        affectedPunchSelect.setAttribute('required', 'required');
                                    } else {
                                        dateAffectedContainer.style.display = 'none';
                                        dateAffectedInput.removeAttribute('required');
                                        affectedPunchSelect.removeAttribute('required');
                                    }
                                }

                                categorySelect.addEventListener('change', toggleFields);
                                toggleFields();
                            });
                        </script>

                        <!-- Assign To -->
                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">
                                Assign To
                            </label>
                            <select name="assigned_to" id="assigned_to"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Unassigned</option>
                                @foreach($assignees as $assignee)
                                    <option value="{{ $assignee->id }}" {{ old('assigned_to', $concern->assigned_to) == $assignee->id ? 'selected' : '' }}>
                                        {{ $assignee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description" id="description" rows="6" required
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                                      placeholder="Provide detailed information about the concern...">{{ old('description', $concern->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ticket Info -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Ticket Information</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Ticket #:</span>
                                    <span class="font-mono font-medium">{{ $concern->ticket_number }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Reporter:</span>
                                    <span class="font-medium">{{ $concern->reporter->name ?? 'Unknown' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Created:</span>
                                    <span>{{ $concern->created_at->format('M d, Y H:i') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Updated:</span>
                                    <span>{{ $concern->updated_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t">
                            <button type="button" 
                                    onclick="confirmDelete()"
                                    class="text-red-600 hover:text-red-800 font-medium">
                                Delete Concern
                            </button>
                            <div class="flex items-center gap-4">
                                <a href="{{ route('concerns.show', $concern) }}" class="text-gray-600 hover:text-gray-800">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                                    Update Concern
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Hidden Delete Form -->
                <form id="delete-concern-form" action="{{ route('concerns.destroy', $concern) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="admin_password" id="delete_admin_password">
                </form>

                <script>
                    // Add a prompt for admin password since it's required in the controller
                    document.getElementById('delete-concern-form').addEventListener('submit', function(e) {
                        const password = prompt('Please enter your admin password to confirm deletion:');
                        if (password) {
                            document.getElementById('delete_admin_password').value = password;
                        } else {
                            e.preventDefault();
                        }
                    });

                    function confirmDelete() {
                        const password = prompt('Please enter your admin password to confirm deletion:');
                        if (password) {
                            document.getElementById('delete_admin_password').value = password;
                            document.getElementById('delete-concern-form').submit();
                        }
                    }
                </script>
            </div>
        </div>
    </div>
</x-app-layout>
