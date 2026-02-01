<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Leave Types') }}
            </h2>
            <a href="{{ route('leave-types.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Add Leave Type
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Code</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Max Days</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Paid</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Attachment</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($leaveTypes as $type)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $type->color ?? '#6b7280' }}"></div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $type->name }}</div>
                                                    @if($type->description)
                                                        <div class="text-xs text-gray-500">{{ Str::limit($type->description, 50) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-mono">
                                            {{ $type->code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                                            {{ $type->max_days }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($type->is_paid)
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Paid</span>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Unpaid</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($type->requires_attachment)
                                                <span class="text-green-600">Required</span>
                                            @else
                                                <span class="text-gray-400">Optional</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($type->is_active)
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <a href="{{ route('leave-types.edit', $type) }}" 
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            <form action="{{ route('leave-types.destroy', $type) }}" method="POST" class="inline"
                                                onsubmit="return confirm('Are you sure you want to delete this leave type?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No leave types found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
