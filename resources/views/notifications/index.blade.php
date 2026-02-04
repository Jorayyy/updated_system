<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">Notifications</h2>
            <p class="text-sm text-gray-500 mt-1">Manage your notifications and alerts</p>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Total</p>
                            <p class="mt-1 text-xl font-bold text-gray-900">{{ auth()->user()->notifications()->count() }}</p>
                        </div>
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Unread</p>
                            <p class="mt-1 text-xl font-bold text-red-600">{{ auth()->user()->unreadNotifications->count() }}</p>
                        </div>
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 015.646 5.646 9 9 0 0120.354 15.354z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Read</p>
                            <p class="mt-1 text-xl font-bold text-green-600">{{ auth()->user()->notifications()->whereNotNull('read_at')->count() }}</p>
                        </div>
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Today</p>
                            <p class="mt-1 text-xl font-bold text-purple-600">{{ auth()->user()->notifications()->whereDate('created_at', today())->count() }}</p>
                        </div>
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications List Card -->
            <div class="bg-white rounded-lg shadow-sm">
                <!-- Header with Controls -->
                <div class="p-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <!-- Filter Tabs -->
                        <div class="flex gap-2">
                            <a href="{{ route('notifications.index') }}" 
                               class="px-3 py-1.5 text-sm font-semibold rounded-lg transition {{ request('filter') === null ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                All ({{ auth()->user()->notifications()->count() }})
                            </a>
                            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                               class="px-3 py-1.5 text-sm font-semibold rounded-lg transition {{ request('filter') === 'unread' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Unread ({{ auth()->user()->unreadNotifications->count() }})
                            </a>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2 w-full sm:w-auto">
                            <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                                    Mark All Read
                                </button>
                            </form>
                            <form action="{{ route('notifications.delete-all-read') }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete all read notifications?')" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">
                                    Clear Read
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="divide-y divide-gray-200">
                    @forelse(auth()->user()->notifications()->when(request('filter') === 'unread', fn($q) => $q->whereNull('read_at'))->paginate(20) as $notification)
                        <div class="p-4 hover:bg-gray-50 transition {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}">
                            <div class="flex items-start gap-4">
                                <!-- Icon -->
                                <div class="flex-shrink-0 pt-1">
                                    @if($notification->type === 'App\\Notifications\\LeaveRequestNotification')
                                        <div class="p-2 bg-blue-100 rounded-lg">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @elseif($notification->type === 'App\\Notifications\\ConcernNotification')
                                        <div class="p-2 bg-yellow-100 rounded-lg">
                                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0-12a9 9 0 110 18 9 9 0 010-18zm0 2a7 7 0 100 14 7 7 0 000-14z"/>
                                            </svg>
                                        </div>
                                    @elseif($notification->type === 'App\\Notifications\\AttendanceNotification')
                                        <div class="p-2 bg-green-100 rounded-lg">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="p-2 bg-gray-100 rounded-lg">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ $notification->title ?? 'Notification' }}
                                                @if(!$notification->read_at)
                                                    <span class="ml-2 inline-block w-2 h-2 bg-red-500 rounded-full"></span>
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-700 mt-1">
                                                {{ $notification->message ?? '' }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-2">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex-shrink-0 flex gap-2">
                                    @if(!$notification->read_at)
                                        <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm" title="Mark as read">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete this notification?')" class="text-red-600 hover:text-red-800 text-sm" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center">
                            <svg class="mx-auto h-10 w-10 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <p class="text-gray-500 text-sm">{{ request('filter') === 'unread' ? 'All caught up!' : 'No notifications found' }}</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if(auth()->user()->notifications()->count() > 0)
                    <div class="p-4 border-t border-gray-200 bg-gray-50">
                        {{ auth()->user()->notifications()->when(request('filter') === 'unread', fn($q) => $q->whereNull('read_at'))->paginate(20)->links() }}
                    </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
