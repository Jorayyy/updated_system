<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Holidays') }} - {{ $year }}
            </h2>
            <div class="flex gap-2">
                <form action="{{ route('holidays.generate-recurring') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year + 1 }}">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        Generate {{ $year + 1 }} Holidays
                    </button>
                </form>
                <a href="{{ route('holidays.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    Add Holiday
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Year Navigation -->
            <div class="mb-4 flex gap-2">
                <a href="{{ route('holidays.index', ['year' => $year - 1]) }}" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
                    &larr; {{ $year - 1 }}
                </a>
                <a href="{{ route('holidays.index', ['year' => $year + 1]) }}" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
                    {{ $year + 1 }} &rarr;
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recurring</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($holidays as $holiday)
                                <tr class="{{ $holiday->date->isPast() ? 'bg-gray-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $holiday->date->format('M d, Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $holiday->date->format('l') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $holiday->name }}</div>
                                        @if($holiday->description)
                                            <div class="text-sm text-gray-500">{{ Str::limit($holiday->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $typeColors = [
                                                'regular' => 'bg-red-100 text-red-800',
                                                'special' => 'bg-yellow-100 text-yellow-800',
                                                'special_working' => 'bg-blue-100 text-blue-800',
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $typeColors[$holiday->type] ?? 'bg-gray-100' }}">
                                            {{ $holiday->type_name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($holiday->is_recurring)
                                            <span class="text-green-600">✓ Yes</span>
                                        @else
                                            <span class="text-gray-400">No</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('holidays.edit', $holiday) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form action="{{ route('holidays.destroy', $holiday) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this holiday?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        No holidays found for {{ $year }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $holidays->appends(['year' => $year])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
