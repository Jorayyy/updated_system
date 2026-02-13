<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Asset Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-bold">{{ $companyAsset->name }}</h3>
                            <p class="text-gray-500 font-mono text-sm">Serial: {{ $companyAsset->serial_number ?? 'N/A' }}</p>
                        </div>
                         <span class="px-3 py-1 rounded-full text-sm font-semibold 
                            {{ $companyAsset->status === 'available' ? 'bg-green-100 text-green-800' : 
                               ($companyAsset->status === 'assigned' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($companyAsset->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="text-sm uppercase text-gray-500 font-bold mb-2">Asset Information</h4>
                            <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Type:</span>
                                    <span class="font-medium">{{ $companyAsset->type }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Asset Code:</span>
                                    <span class="font-medium font-mono">{{ $companyAsset->asset_code ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Added On:</span>
                                    <span class="font-medium">{{ $companyAsset->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm uppercase text-gray-500 font-bold mb-2">Assignment</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                @if($companyAsset->assignedTo)
                                    <div class="flex items-center">
                                        <div class="ml-3">
                                            <p class="text-gray-900 font-medium">{{ $companyAsset->assignedTo->name }}</p>
                                            <p class="text-gray-500 text-sm">{{ $companyAsset->assignedTo->email }}</p>
                                            <p class="text-xs text-gray-400 mt-1">Assigned since: {{ $companyAsset->assigned_date ? \Carbon\Carbon::parse($companyAsset->assigned_date)->format('M d, Y') : '-' }}</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-gray-500 italic">Not currently assigned to anyone.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('company-assets.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                            Back to Assets
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
