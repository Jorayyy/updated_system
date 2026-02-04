<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Leave Credits History') }} - {{ $employee->name }}
            </h2>
            <a href="{{ route('leave-credits.index') }}" 
                class="text-indigo-600 hover:text-indigo-900">
                &larr; Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Employee Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
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
                            <span class="text-sm text-gray-500">Position</span>
                            <p class="font-medium text-gray-900">{{ $employee->position ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Date Hired</span>
                            <p class="font-medium text-gray-900">
                                {{ $employee->date_hired ? $employee->date_hired->format('M d, Y') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Balances by Year -->
            @forelse($balances as $year => $yearBalances)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Year {{ $year }}
                            </h3>
                            <a href="{{ route('leave-credits.edit', ['employee' => $employee->id, 'year' => $year]) }}"
                                class="text-indigo-600 hover:text-indigo-900 text-sm">
                                Edit Credits
                            </a>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($yearBalances as $balance)
                                <div class="border rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-block w-3 h-3 rounded-full" 
                                            style="background-color: {{ $balance->leaveType->color ?? '#6366f1' }}"></span>
                                        <span class="font-medium text-gray-900">
                                            {{ $balance->leaveType->name }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-center">
                                        <div>
                                            <div class="text-2xl font-bold text-indigo-600">{{ $balance->allocated_days }}</div>
                                            <div class="text-xs text-gray-500">Allocated</div>
                                        </div>
                                        <div>
                                            <div class="text-2xl font-bold text-red-600">{{ $balance->used_days }}</div>
                                            <div class="text-xs text-gray-500">Used</div>
                                        </div>
                                        <div>
                                            <div class="text-2xl font-bold {{ $balance->remaining_days > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                                {{ $balance->remaining_days }}
                                            </div>
                                            <div class="text-xs text-gray-500">Remaining</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        No leave credit records found for this employee.
                    </div>
                </div>
            @endforelse

            <!-- Audit Log -->
            @if($auditLogs->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Credit Adjustment History
                        </h3>
                        <div class="space-y-3">
                            @foreach($auditLogs as $log)
                                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        @if(str_contains($log->action, 'add') || str_contains($log->description, '+'))
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </div>
                                        @elseif(str_contains($log->action, 'deduct') || str_contains($log->description, '-'))
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm text-gray-900">
                                            {{ $log->description }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $log->created_at->format('M d, Y h:i A') }}
                                            @if($log->user)
                                                • by {{ $log->user->name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
