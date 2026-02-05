<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    Notifications
                </h2>
                <p class="text-sm text-gray-500 mt-1">Stay updated with your latest activities and alerts</p>
            </div>
            
            <div class="flex items-center gap-2">
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Mark all as read
                    </button>
                </form>
                
                <form action="{{ route('notifications.delete-all-read') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Clear all read notifications?')" class="inline-flex items-center px-4 py-2 bg-red-50 border border-red-100 rounded-xl font-semibold text-sm text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Clear Read
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats & Filters Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                <!-- Quick Stats -->
                <div class="lg:col-span-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Total Notifications -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-md transition-all duration-300">
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total</p>
                                <h3 class="text-3xl font-black text-gray-900 mb-1 group-hover:scale-105 transition-transform origin-left">{{ auth()->user()->notifications()->count() }}</h3>
                                <p class="text-[11px] text-gray-400">All notifications</p>
                            </div>
                            <div class="p-4 bg-indigo-50 rounded-xl text-indigo-600 group-hover:rotate-6 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </div>
                        </div>

                        <!-- Unread Notifications -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-md transition-all duration-300">
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Unread</p>
                                <h3 class="text-3xl font-black text-red-600 mb-1 group-hover:scale-105 transition-transform origin-left">{{ auth()->user()->unreadNotifications->count() }}</h3>
                                <p class="text-[11px] text-gray-400">Needs attention</p>
                                <div class="mt-2 w-full h-1 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="bg-red-500 h-full" style="width: {{ auth()->user()->notifications()->count() > 0 ? (auth()->user()->unreadNotifications->count() / auth()->user()->notifications()->count()) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="p-4 bg-red-50 rounded-xl text-red-600 group-hover:rotate-6 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                        </div>

                        <!-- Read Notifications -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-md transition-all duration-300">
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Read</p>
                                <h3 class="text-3xl font-black text-emerald-600 mb-1 group-hover:scale-105 transition-transform origin-left">{{ auth()->user()->notifications()->whereNotNull('read_at')->count() }}</h3>
                                <p class="text-[11px] text-gray-400">Processed alerts</p>
                            </div>
                            <div class="p-4 bg-emerald-50 rounded-xl text-emerald-600 group-hover:rotate-6 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>

                        <!-- Today's Notifications -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-md transition-all duration-300">
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Today</p>
                                <h3 class="text-3xl font-black text-indigo-600 mb-1 group-hover:scale-105 transition-transform origin-left">{{ auth()->user()->notifications()->whereDate('created_at', today())->count() }}</h3>
                                <p class="text-[11px] text-gray-400">Recent updates</p>
                            </div>
                            <div class="p-4 bg-indigo-50 rounded-xl text-indigo-600 group-hover:rotate-6 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-gray-50 p-1 rounded-2xl flex flex-col gap-1 border border-gray-200 h-fit">
                    <a href="{{ route('notifications.index') }}" 
                       class="flex items-center justify-between px-4 py-3 rounded-xl transition-all {{ request('filter') === null ? 'bg-white shadow-sm text-indigo-600 font-bold' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900' }}">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                            All Notifications
                        </span>
                        <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full text-gray-700">{{ auth()->user()->notifications()->count() }}</span>
                    </a>
                    <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                       class="flex items-center justify-between px-4 py-3 rounded-xl transition-all {{ request('filter') === 'unread' ? 'bg-white shadow-sm text-red-600 font-bold' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900' }}">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Unread Only
                        </span>
                        <span class="text-xs bg-red-100 px-2 py-0.5 rounded-full text-red-700 font-bold">{{ auth()->user()->unreadNotifications->count() }}</span>
                    </a>
                </div>
            </div>

            <!-- Notifications Feed -->
            <div class="space-y-4">
                @php
                    $notifications = auth()->user()->notifications()
                        ->when(request('filter') === 'unread', fn($q) => $q->whereNull('read_at'))
                        ->paginate(20);
                @endphp

                @forelse($notifications as $notification)
                    <div class="group bg-white rounded-2xl shadow-sm border {{ $notification->read_at ? 'border-gray-100' : 'border-indigo-100 bg-indigo-50/30' }} transition-all hover:shadow-md overflow-hidden">
                        <div class="p-5 flex items-start gap-4">
                            <!-- Category Icon -->
                            <div class="flex-shrink-0">
                                @php
                                    $iconColor = 'gray';
                                    $iconBg = 'gray';
                                    $svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                                    
                                    if(str_contains($notification->type, "Leave")) {
                                        $iconColor = "blue";
                                        $iconBg = "blue";
                                        $svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 01-2 2v12a2 2 0 002 2z"></path>';
                                    } elseif(str_contains($notification->type, "Concern")) {
                                        $iconColor = "amber";
                                        $iconBg = "amber";
                                        $svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                                    } elseif(str_contains($notification->type, "Attendance") || str_contains($notification->type, "Dtr")) {
                                        $iconColor = "emerald";
                                        $iconBg = "emerald";
                                        $svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                                    } elseif(str_contains($notification->type, "Payroll")) {
                                        $iconColor = "rose";
                                        $iconBg = "rose";
                                        $svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                                    }
                                @endphp

                                <div class="p-3 bg-{{ $iconBg }}-100 rounded-xl text-{{ $iconColor }}-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        {!! $svg !!}
                                    </svg>
                                </div>
                            </div>

                            <!-- Notification Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col md:flex-row md:items-center justify-between mb-1">
                                    <h4 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                        {{ $notification->data["title"] ?? ($notification->title ?? "Notification") }}
                                        @if(!$notification->read_at)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                New
                                            </span>
                                        @endif
                                    </h4>
                                    <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded-lg">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-gray-600 text-sm leading-relaxed">
                                    {{ $notification->data["message"] ?? ($notification->message ?? "") }}
                                </p>
                            </div>

                            <!-- Notification Actions (Alpine.js Dropdown) -->
                            <div class="relative flex-shrink-0" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </button>

                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 z-50 py-1 overflow-hidden"
                                     x-cloak>
                                    @if(!$notification->read_at)
                                        <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-emerald-600 hover:bg-emerald-50 font-bold flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Mark as read
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Remove this notification?')" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-bold flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Delete notification
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-3xl p-16 text-center border-2 border-dashed border-gray-200">
                        <div class="inline-flex items-center justify-center p-6 bg-gray-50 rounded-full mb-6">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-8 8-8-8"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-1">Your inbox is empty!</h3>
                        <p class="text-gray-500 max-w-sm mx-auto">
                            {{ request('filter') === 'unread' ? "You've read everything! Nicely done." : "When you receive updates about leaves, attendance or payroll, they'll appear here." }}
                        </p>
                    </div>
                @endforelse

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateX(10px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.2s ease-out forwards;
        }
    </style>
</x-app-layout>
