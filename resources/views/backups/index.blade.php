<div x-data="{ isBackingUp: false }">
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('System Backups') }}
            </h2>
            <div class="flex gap-2">
                <form action="{{ route('backups.create') }}" method="POST" @submit="isBackingUp = true">
                    @csrf
                    <input type="hidden" name="type" value="db">
                    <x-secondary-button type="submit" onclick="return confirm('Create database backup?')">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                        </svg>
                        DB Backup
                    </x-secondary-button>
                </form>

                <form action="{{ route('backups.create') }}" method="POST" @submit="isBackingUp = true">
                    @csrf
                    <input type="hidden" name="type" value="full">
                    <x-primary-button type="submit" onclick="return confirm('This will create a full backup of the SOURCE CODE and DATABASE. This may take a minute. Continue?')">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                        </svg>
                        Full System Backup
                    </x-primary-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <!-- Windows-like Backup Animation Overlay -->
        <div x-show="isBackingUp" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm"
             x-cloak>
            <div class="bg-white rounded-lg shadow-2xl p-8 max-w-md w-full text-center">
                <!-- Windows-style Folder Animation -->
                <div class="relative w-32 h-32 mx-auto mb-6">
                    <!-- The Folder -->
                    <svg class="w-24 h-24 mx-auto text-blue-500 fill-current" viewBox="0 0 24 24">
                        <path d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z"/>
                    </svg>
                    <!-- Floating Paper Animation -->
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 animate-bounce">
                        <svg class="w-8 h-8 text-white fill-current bg-blue-600 rounded-sm shadow-md transition-all duration-1000 transform -translate-y-4 paper-animation" viewBox="0 0 24 24">
                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                        </svg>
                    </div>
                </div>
                
                <h3 class="text-xl font-bold text-gray-900 mb-2">Creating Backup...</h3>
                <p class="text-gray-600 mb-4 px-4 text-sm">Please wait while the system secures your data. This might take a while for full system backups.</p>
                
                <!-- Progress Bar Logic -->
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2 overflow-hidden">
                    <div class="bg-blue-600 h-2.5 rounded-full animate-progress-stripes" style="width: 100%"></div>
                </div>
                <span class="text-xs text-blue-600 font-medium">Securing Source Code & Database</span>
            </div>
        </div>

        <style>
            [x-cloak] { display: none !important; }
            
            @keyframes paper-in {
                0% { transform: translateY(-50px) translateX(-50%) rotate(0deg); opacity: 0; }
                50% { opacity: 1; }
                100% { transform: translateY(20px) translateX(-50%) rotate(15deg); opacity: 0; }
            }

            .paper-animation {
                animation: paper-in 1.5s infinite linear;
            }

            @keyframes progress-stripes {
                0% { background-position: 0 0; }
                100% { background-position: 40px 0; }
            }

            .animate-progress-stripes {
                background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
                background-size: 40px 40px;
                animation: progress-stripes 1s linear infinite;
            }
        </style>

        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Upload Backup Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <svg class="w-5 h-5 inline mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Restore from Backup File
                    </h3>
                    <form action="{{ route('backups.upload') }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-4" @submit="isBackingUp = true">
                        @csrf
                        <div class="flex-1">
                            <label for="backup_file" class="block text-sm font-medium text-gray-700 mb-1">Upload SQL Backup File</label>
                            <input type="file" name="backup_file" id="backup_file" accept=".sql" 
                                class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-indigo-50 file:text-indigo-700
                                   
                                    hover:file:bg-indigo-100
                                    cursor-pointer border border-gray-300 rounded-md">
                            <p class="mt-1 text-xs text-gray-500">Only .sql files are allowed. Max file size: 100MB</p>
                        </div>
                        <button type="submit" onclick="return confirm('⚠️ WARNING: This will replace your current database with the uploaded backup. This action cannot be undone! Are you sure you want to continue?')"
                            class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Upload & Restore
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-yellow-800">Important</h3>
                                <p class="text-sm text-yellow-700">Regular backups are recommended. Store backup files in a secure, off-site location.</p>
                            </div>
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filename</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($backups as $backup)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if(Str::endsWith($backup['filename'], '.zip'))
                                                <svg class="w-5 h-5 text-indigo-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                                </svg>
                                            @endif
                                            <span class="text-sm font-medium text-gray-900">{{ $backup['filename'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $backup['type'] === 'Full System' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $backup['type'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $backup['size_formatted'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $backup['last_modified']->format('M d, Y H:i') }}
                                        <span class="text-xs text-gray-400">({{ $backup['last_modified']->diffForHumans() }})</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('backups.download', $backup['filename']) }}" class="text-indigo-600 hover:text-indigo-900">
                                                Download
                                            </a>
                                            <form action="{{ route('backups.restore', $backup['filename']) }}" method="POST" class="inline" @submit="isBackingUp = true">
                                                @csrf
                                                <button type="submit" class="text-orange-600 hover:text-orange-900" 
                                                    onclick="return confirm('⚠️ WARNING: This will replace your current database with this backup. This action cannot be undone! Are you sure you want to continue?')">
                                                    Restore
                                                </button>
                                            </form>
                                            <form action="{{ route('backups.destroy', $backup['filename']) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this backup?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                        </svg>
                                        <p class="mt-2">No backups found.</p>
                                        <p class="text-sm">Create your first backup to get started.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</x-app-layout>
</div>
