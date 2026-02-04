<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('concerns.my') }}" class="text-gray-500 hover:text-gray-700 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ticket') }}: {{ $concern->ticket_number }}
            </h2>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Concern Details -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-semibold text-gray-900">
                                    {{ $concern->title }}
                                </h3>
                                <div class="flex space-x-2">
                                    @php
                                        $priorityColors = [
                                            'low' => 'bg-gray-100 text-gray-800',
                                            'medium' => 'bg-yellow-100 text-yellow-800',
                                            'high' => 'bg-orange-100 text-orange-800',
                                            'critical' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusColors = [
                                            'open' => 'bg-blue-100 text-blue-800',
                                            'in_progress' => 'bg-purple-100 text-purple-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'on_hold' => 'bg-orange-100 text-orange-800',
                                            'resolved' => 'bg-green-100 text-green-800',
                                            'closed' => 'bg-gray-100 text-gray-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs rounded-full {{ $priorityColors[$concern->priority] ?? '' }}">
                                        {{ $priorities[$concern->priority] ?? $concern->priority }}
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$concern->status] ?? '' }}">
                                        {{ $statuses[$concern->status] ?? $concern->status }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="prose max-w-none">
                                {!! nl2br(e($concern->description)) !!}
                            </div>

                            <div class="mt-6 pt-4 border-t border-gray-200 text-sm text-gray-500">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="font-medium">Category:</span>
                                        {{ $categories[$concern->category] ?? $concern->category }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Assigned To:</span>
                                        {{ $concern->assignee->name ?? 'Not yet assigned' }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Submitted:</span>
                                        {{ $concern->created_at->format('M d, Y h:i A') }}
                                    </div>
                                    @if($concern->resolved_at)
                                        <div>
                                            <span class="font-medium">Resolved:</span>
                                            {{ $concern->resolved_at->format('M d, Y h:i A') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">
                                Comments ({{ $concern->comments->count() }})
                            </h4>

                            @if($concern->comments->isEmpty())
                                <p class="text-gray-500 text-sm">No comments yet.</p>
                            @else
                                <div class="space-y-4">
                                    @foreach($concern->comments as $comment)
                                        <div class="flex space-x-3 {{ $comment->user_id == auth()->id() ? '' : 'bg-gray-50 -mx-3 px-3 py-2 rounded' }}">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-sm font-medium">
                                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                                    @if(in_array($comment->user->role, ['admin', 'hr']))
                                                        <span class="px-1.5 py-0.5 text-xs bg-blue-100 text-blue-800 rounded">Staff</span>
                                                    @endif
                                                    <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="mt-1 text-sm text-gray-700">{!! nl2br(e($comment->comment)) !!}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Add Comment Form -->
                            @if(!in_array($concern->status, ['closed', 'cancelled']))
                                <form action="{{ route('concerns.user-comment', $concern) }}" method="POST" class="mt-6">
                                    @csrf
                                    <div>
                                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                                            Add a Comment
                                        </label>
                                        <textarea name="comment" id="comment" rows="3" required
                                                  placeholder="Add additional information or reply to comments..."
                                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    </div>
                                    <div class="mt-3 flex justify-end">
                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition">
                                            Post Comment
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="mt-4 text-sm text-gray-500 italic">
                                    This ticket is {{ $concern->status }}. No more comments can be added.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Activity Log -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Activity Timeline</h4>
                            
                            @if($concern->activities->isEmpty())
                                <p class="text-gray-500 text-sm">No activity recorded.</p>
                            @else
                                <div class="flow-root">
                                    <ul class="-mb-8">
                                        @foreach($concern->activities as $activity)
                                            <li>
                                                <div class="relative pb-8">
                                                    @if(!$loop->last)
                                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                    @endif
                                                    <div class="relative flex space-x-3">
                                                        <div>
                                                            @php
                                                                $activityIcons = [
                                                                    'created' => 'bg-green-500',
                                                                    'status_changed' => 'bg-blue-500',
                                                                    'assigned' => 'bg-purple-500',
                                                                    'commented' => 'bg-gray-500',
                                                                    'priority_changed' => 'bg-yellow-500',
                                                                    'resolved' => 'bg-green-600',
                                                                ];
                                                            @endphp
                                                            <span class="h-8 w-8 rounded-full {{ $activityIcons[$activity->action] ?? 'bg-gray-400' }} flex items-center justify-center ring-8 ring-white">
                                                                <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                                </svg>
                                                            </span>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div class="text-sm text-gray-500">
                                                                <span class="font-medium text-gray-900">{{ $activity->user->name ?? 'System' }}</span>
                                                                {{ $activity->description }}
                                                            </div>
                                                            <div class="mt-1 text-xs text-gray-500">
                                                                {{ $activity->created_at->diffForHumans() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
