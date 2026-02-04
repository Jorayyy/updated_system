<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Leave Credits') }} - {{ $employee->name }}
            </h2>
            <a href="{{ route('leave-credits.index', ['year' => $year]) }}" 
                class="text-indigo-600 hover:text-indigo-900">
                &larr; Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Employee Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">Employee ID</span>
                            <p class="font-medium text-gray-900">{{ $employee->employee_id }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Name</span>
                            <p class="font-medium text-gray-900">{{ $employee->name }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Department</span>
                            <p class="font-medium text-gray-900">{{ $employee->department ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Year</span>
                            <p class="font-medium text-indigo-600 text-lg">{{ $year }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credits Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('leave-credits.update', $employee->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="year" value="{{ $year }}">

                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Leave Credits Allocation</h3>
                        
                        <div class="space-y-4">
                            @foreach($leaveTypes as $index => $type)
                                @php
                                    $balance = $balances->get($type->id);
                                @endphp
                                <div class="border rounded-lg p-4">
                                    <input type="hidden" name="credits[{{ $index }}][leave_type_id]" value="{{ $type->id }}">
                                    
                                    <div class="flex flex-wrap items-center gap-4">
                                        <div class="flex-1 min-w-[200px]">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-block w-4 h-4 rounded-full" style="background-color: {{ $type->color }}"></span>
                                                <span class="font-medium text-gray-900">{{ $type->name }}</span>
                                                @if($type->is_paid)
                                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded">Paid</span>
                                                @else
                                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">Unpaid</span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Default: {{ $type->max_days }} days/year
                                                @if($type->requires_attachment)
                                                    • Requires attachment
                                                @endif
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-6">
                                            <div class="text-center">
                                                <label class="block text-xs text-gray-500 mb-1">Allocated</label>
                                                <input type="number" name="credits[{{ $index }}][allocated_days]" 
                                                    value="{{ $balance ? $balance->allocated_days : $type->max_days }}"
                                                    min="0" max="365" step="0.5"
                                                    class="w-24 text-center border-gray-300 rounded-md shadow-sm">
                                            </div>
                                            
                                            @if($balance)
                                                <div class="text-center">
                                                    <label class="block text-xs text-gray-500 mb-1">Used</label>
                                                    <div class="w-24 py-2 text-center text-red-600 font-medium">
                                                        {{ $balance->used_days }}
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <label class="block text-xs text-gray-500 mb-1">Remaining</label>
                                                    <div class="w-24 py-2 text-center font-medium {{ $balance->remaining_days > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $balance->remaining_days }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center">
                                                    <label class="block text-xs text-gray-500 mb-1">Used</label>
                                                    <div class="w-24 py-2 text-center text-gray-400">0</div>
                                                </div>
                                                <div class="text-center">
                                                    <label class="block text-xs text-gray-500 mb-1">Remaining</label>
                                                    <div class="w-24 py-2 text-center text-gray-400">-</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($errors->any())
                            <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="px-6 py-4 bg-gray-50 flex justify-between items-center">
                        <a href="{{ route('leave-credits.history', $employee->id) }}" 
                            class="text-gray-600 hover:text-gray-800">
                            View History
                        </a>
                        <div class="flex gap-2">
                            <a href="{{ route('leave-credits.index', ['year' => $year]) }}" 
                                class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                Cancel
                            </a>
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
