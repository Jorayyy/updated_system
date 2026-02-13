<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Company Assets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">
                            @if(auth()->user()->hasRole('super_admin'))
                                All Company Assets
                            @else
                                My Assigned Assets
                            @endif
                        </h3>
                        
                        @if(auth()->user()->hasRole('super_admin'))
                        <a href="{{ route('company-assets.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add New Asset
                        </a>
                        @endif
                    </div>

                    @if(auth()->user()->hasRole('super_admin'))
                        <!-- Admin Table View -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asset Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial No.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($assets as $asset)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $asset->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $asset->type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-mono text-xs">{{ $asset->serial_number ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                            @if($asset->assignedTo)
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                                                        {{ substr($asset->assignedTo->name, 0, 1) }}
                                                    </div>
                                                    <span class="text-sm">{{ $asset->assignedTo->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic text-xs">Unassigned</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $asset->status === 'available' ? 'bg-green-100 text-green-800' : 
                                                   ($asset->status === 'assigned' ? 'bg-blue-100 text-blue-800' : 
                                                   ($asset->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                                {{ ucfirst($asset->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('company-assets.show', $asset) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                            <a href="{{ route('company-assets.edit', $asset) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                            <form action="{{ route('company-assets.destroy', $asset) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this asset?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No assets found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- Employee Grid View -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @forelse($assets as $asset)
                            <div class="border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition bg-gradient-to-br from-white to-gray-50">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $asset->status === 'available' ? 'bg-green-100 text-green-800' : 
                                           ($asset->status === 'assigned' ? 'bg-blue-100 text-blue-800' : 
                                           ($asset->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($asset->status) }}
                                    </span>
                                </div>
                                <h4 class="font-bold text-lg text-gray-900 mb-1">{{ $asset->name }}</h4>
                                <p class="text-sm text-gray-500 font-mono mb-4">{{ $asset->type }}</p>
                                <div class="space-y-2 pt-4 border-t border-gray-100 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Serial:</span>
                                        <span class="font-medium text-gray-700">{{ $asset->serial_number ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Assigned:</span>
                                        <span class="font-medium text-gray-700">{{ $asset->assigned_date ? \Carbon\Carbon::parse($asset->assigned_date)->format('M d, Y') : 'Unknown' }}</span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-span-full flex flex-col items-center justify-center py-12 text-gray-400">
                                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                <p class="text-lg font-medium">No assets assigned to you.</p>
                            </div>
                            @endforelse
                        </div>
                    @endif

                    <div class="mt-6">
                        {{ $assets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
