<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My PC Station') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Current PC Status -->
        @if($currentPc)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Currently Using</h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse"></span>
                        Active
                    </span>
                </div>

                <div class="flex items-center gap-6">
                    <div class="p-6 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl text-white">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-2xl font-bold text-gray-900">{{ $currentPc->pc_number }}</h4>
                        @if($currentPc->name)
                            <p class="text-gray-600">{{ $currentPc->name }}</p>
                        @endif
                        @if($currentPc->location)
                            <p class="text-sm text-gray-500 mt-1">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $currentPc->location }}
                            </p>
                        @endif
                        <p class="text-sm text-gray-500 mt-2">
                            Using since: {{ $currentPc->assigned_at->format('M d, Y H:i') }}
                            ({{ $currentPc->assigned_at->diffForHumans() }})
                        </p>
                    </div>
                    <div>
                        <form action="{{ route('computers.release') }}" method="POST" onsubmit="return confirm('Are you sure you want to release this PC?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Release PC
                            </button>
                        </form>
                    </div>
                </div>

                @if($currentPc->specs)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Specifications</h5>
                        <p class="text-sm text-gray-600">{{ $currentPc->specs }}</p>
                    </div>
                @endif
            </div>
        @else
            <!-- No PC Assigned -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="text-center py-8">
                    <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No PC Assigned</h3>
                    <p class="text-gray-500 mb-4">You are not currently using any PC. Select one from the available list below.</p>
                </div>
            </div>
        @endif

        <!-- Available PCs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                Available PCs ({{ $availableComputers->total() }})
            </h3>

            @if($availableComputers->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($availableComputers as $pc)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-green-100 rounded-lg">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $pc->pc_number }}</h4>
                                        @if($pc->name)
                                            <p class="text-sm text-gray-500">{{ $pc->name }}</p>
                                        @endif
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Available
                                </span>
                            </div>

                            @if($pc->location)
                                <p class="text-sm text-gray-500 mt-2">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    {{ $pc->location }}
                                </p>
                            @endif

                            <form action="{{ route('computers.select') }}" method="POST" class="mt-4">
                                @csrf
                                <input type="hidden" name="computer_id" value="{{ $pc->id }}">
                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                                    Use This PC
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($availableComputers->hasPages())
                    <div class="mt-6">
                        {{ $availableComputers->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <p>No available PCs at the moment.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
