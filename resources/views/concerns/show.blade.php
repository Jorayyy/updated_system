<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('concerns.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $concern->ticket_number }} - {{ $concern->title }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('concerns.edit', $concern) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600">
                    Edit
                </a>
                <form action="{{ route('concerns.destroy', $concern) }}" method="POST" class="inline" id="delete-form-{{ $concern->id }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="admin_password" id="admin_password_{{ $concern->id }}">
                    <button type="button" 
                        onclick="const password = prompt('Critical Action: This concern will be permanently deleted. Please enter your ADMIN PASSWORD to continue:'); if(password) { document.getElementById('admin_password_{{ $concern->id }}').value = password; document.getElementById('delete-form-{{ $concern->id }}').submit(); }"
                        class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Concern Details -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-4">
                                    <span class="px-3 py-1 text-sm rounded-full {{ $concern->status_badge }}">
                                        {{ $statuses[$concern->status] ?? $concern->status }}
                                    </span>
                                    <span class="px-3 py-1 text-sm rounded-full {{ $concern->priority_badge }}">
                                        {{ $priorities[$concern->priority] ?? $concern->priority }}
                                    </span>
                                    <span class="px-3 py-1 text-sm rounded-full {{ $concern->category_badge }}">
                                        {{ $categories[$concern->category] ?? $concern->category }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="prose max-w-none">
                                <h3 class="text-lg font-semibold mb-2">Description</h3>
                                <div class="bg-gray-50 rounded-lg p-4 whitespace-pre-wrap">{{ $concern->description }}</div>
                            </div>

                            @if($concern->resolution_notes)
                                <div class="mt-6 border-t pt-6">
                                    <h3 class="text-lg font-semibold mb-2 text-green-700">Resolution Notes</h3>
                                    <div class="bg-green-50 rounded-lg p-4 whitespace-pre-wrap">{{ $concern->resolution_notes }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Comments & Notes</h3>
                            
                            <!-- Comment Form -->
                            <form action="{{ route('concerns.comment', $concern) }}" method="POST" class="mb-6">
                                @csrf
                                <textarea name="comment" rows="3" 
                                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                          placeholder="Add a comment..."></textarea>
                                <div class="mt-2 flex items-center justify-between">
                                    <label class="flex items-center gap-2 text-sm text-gray-600">
                                        <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        Internal note (not visible to reporter)
                                    </label>
                                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                        Add Comment
                                    </button>
                                </div>
                            </form>

                            <!-- Comments List -->
                            <div class="space-y-4">
                                @forelse($concern->comments as $comment)
                                    <div class="border rounded-lg p-4 {{ $comment->is_internal ? 'bg-yellow-50 border-yellow-200' : 'bg-white' }}">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-gray-900">{{ $comment->user->name }}</span>
                                                @if($comment->is_internal)
                                                    <span class="px-2 py-0.5 text-xs bg-yellow-200 text-yellow-800 rounded-full">Internal</span>
                                                @endif
                                            </div>
                                            <span class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-gray-700 whitespace-pre-wrap">{{ $comment->comment }}</p>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No comments yet</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                            
                            <!-- Status Update -->
                            <form action="{{ route('concerns.status', $concern) }}" method="POST" class="mb-4">
                                @csrf
                                @method('PATCH')
                                <label class="block text-sm font-medium text-gray-700 mb-1">Change Status</label>
                                <select name="status" class="w-full border-gray-300 rounded-md shadow-sm mb-2">
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ $concern->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @if(in_array($concern->status, ['open', 'in_progress', 'pending']))
                                    <textarea name="resolution_notes" rows="2" class="w-full border-gray-300 rounded-md shadow-sm mb-2" placeholder="Resolution notes (optional)"></textarea>
                                @endif
                                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                    Update Status
                                </button>
                            </form>

                            <!-- Assignment -->
                            <form action="{{ route('concerns.assign', $concern) }}" method="POST" class="mb-4">
                                @csrf
                                @method('PATCH')
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                                <select name="assigned_to" class="w-full border-gray-300 rounded-md shadow-sm mb-2">
                                    <option value="">Unassigned</option>
                                    @foreach($assignees as $assignee)
                                        <option value="{{ $assignee->id }}" {{ $concern->assigned_to == $assignee->id ? 'selected' : '' }}>
                                            {{ $assignee->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="w-full bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                                    Update Assignment
                                </button>
                            </form>

                            <!-- Priority Update -->
                            <form action="{{ route('concerns.priority', $concern) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <label class="block text-sm font-medium text-gray-700 mb-1">Change Priority</label>
                                <select name="priority" class="w-full border-gray-300 rounded-md shadow-sm mb-2">
                                    @foreach($priorities as $key => $label)
                                        <option value="{{ $key }}" {{ $concern->priority == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="w-full bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700">
                                    Update Priority
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Details</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Ticket Number</dt>
                                    <dd class="text-sm font-mono font-medium">{{ $concern->ticket_number }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Reporter</dt>
                                    <dd class="text-sm font-medium">{{ $concern->reporter->name ?? 'Unknown' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Assignee</dt>
                                    <dd class="text-sm font-medium">{{ $concern->assignee->name ?? 'Unassigned' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Created</dt>
                                    <dd class="text-sm">{{ $concern->created_at->format('M d, Y H:i') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Last Updated</dt>
                                    <dd class="text-sm">{{ $concern->updated_at->format('M d, Y H:i') }}</dd>
                                </div>
                                @if($concern->resolved_at)
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500">Resolved At</dt>
                                        <dd class="text-sm">{{ $concern->resolved_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500">Resolved By</dt>
                                        <dd class="text-sm font-medium">{{ $concern->resolver->name ?? 'Unknown' }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Activity Log -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Activity Log</h3>
                            <div class="space-y-4 max-h-96 overflow-y-auto">
                                @forelse($concern->activities as $activity)
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-{{ $activity->action_color }}-100 flex items-center justify-center">
                                            @if($activity->action == 'created')
                                                <svg class="w-4 h-4 text-{{ $activity->action_color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            @elseif($activity->action == 'resolved')
                                                <svg class="w-4 h-4 text-{{ $activity->action_color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            @elseif($activity->action == 'commented')
                                                <svg class="w-4 h-4 text-{{ $activity->action_color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-{{ $activity->action_color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-900">
                                                <span class="font-medium">{{ $activity->user->name ?? 'System' }}</span>
                                                {{ $activity->description }}
                                            </p>
                                            <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center">No activity recorded</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
