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

    <div class="py-12">
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
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                        </div>

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

                        <!-- Submit -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <form action="{{ route('concerns.destroy', $concern) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this concern? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    Delete Concern
                                </button>
                            </form>
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
            </div>
        </div>
    </div>
</x-app-layout>
