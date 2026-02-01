<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notifications') }}
                @if($unreadCount > 0)
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        {{ $unreadCount }} unread
                    </span>
                @endif
            </h2>
            <div class="flex gap-2">
                @if($unreadCount > 0)
                    <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                            Mark All as Read
                        </button>
                    </form>
                @endif
                <form action="{{ route('notifications.delete-all-read') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                        Clear Read
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filter tabs -->
            <div class="mb-4 flex gap-2">
                <a href="{{ route('notifications.index') }}" 
                   class="px-4 py-2 rounded-md {{ !request('filter') ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    All
                </a>
                <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                   class="px-4 py-2 rounded-md {{ request('filter') === 'unread' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Unread
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="divide-y divide-gray-200">
                    @forelse($notifications as $notification)
                        <div class="p-4 {{ $notification->isRead() ? 'bg-white' : 'bg-blue-50' }} hover:bg-gray-50 transition">
                            <div class="flex items-start">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    @php
                                        $iconColors = [
                                            'blue' => 'text-blue-500 bg-blue-100',
                                            'green' => 'text-green-500 bg-green-100',
                                            'red' => 'text-red-500 bg-red-100',
                                            'yellow' => 'text-yellow-500 bg-yellow-100',
                                        ];
                                        $colorClass = $iconColors[$notification->icon_color] ?? $iconColors['blue'];
                                    @endphp
                                    <div class="w-10 h-10 rounded-full {{ $colorClass }} flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($notification->icon === 'calendar')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            @elseif($notification->icon === 'check-circle')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @elseif($notification->icon === 'x-circle')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @elseif($notification->icon === 'currency-dollar')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @elseif($notification->icon === 'clock')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            @endif
                                        </svg>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                                        <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ $notification->message }}</p>
                                    
                                    <div class="mt-2 flex items-center gap-3">
                                        @if($notification->action_url)
                                            <a href="{{ route('notifications.mark-as-read', $notification) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                                                View Details →
                                            </a>
                                        @endif
                                        
                                        @if(!$notification->isRead())
                                            <form action="{{ route('notifications.mark-as-read', $notification) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                                                    Mark as read
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-500 hover:text-red-700">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <p class="mt-2">No notifications yet.</p>
                        </div>
                    @endforelse
                </div>

                @if($notifications->hasPages())
                    <div class="p-4 border-t">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
