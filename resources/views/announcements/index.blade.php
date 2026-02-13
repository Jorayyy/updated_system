<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Announcements') }}
            </h2>
            @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin' || auth()->user()->role === 'hr')
            <a href="{{ route('announcements.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors duration-200">
                Post Announcement
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @forelse($announcements ?? [] as $announcement)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg {{ $announcement->is_pinned ? 'border-l-4 border-indigo-500' : '' }}">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2">
                                    @if($announcement->is_pinned)
                                        <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full font-bold">PINNED</span>
                                    @endif
                                    <h3 class="font-bold text-xl">{{ $announcement->title }}</h3>
                                </div>
                                <div class="text-sm text-gray-500 mt-1">
                                    Posted by {{ $announcement->author->name }} on {{ $announcement->created_at->format('M d, Y') }}
                                </div>
                            </div>
                            @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'hr')
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('announcements.pin', $announcement) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-gray-400 hover:text-indigo-600" title="{{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="{{ $announcement->is_pinned ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                            </svg>
                                        </button>
                                    </form>
                                    <!-- Add delete button here if needed -->
                                </div>
                            @endif
                        </div>
                        <div class="mt-4 prose max-w-none text-gray-700">
                            {!! nl2br(e($announcement->content)) !!}
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-500">
                    No active announcements.
                </div>
            @endforelse
            
            <div class="mt-4">
                @if(isset($announcements) && method_exists($announcements, 'links'))
                    {{ $announcements->links() }}
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
