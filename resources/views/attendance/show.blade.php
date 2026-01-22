<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Attendance Details') }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('attendance.edit', $attendance) }}" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">
                    Edit
                </a>
                <a href="{{ route('attendance.manage') }}" class="text-gray-600 hover:text-gray-800">
                    &larr; Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Main Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-8">
                        <!-- Employee Info -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Employee Information</h3>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-gray-500">Employee ID:</span>
                                    <span class="font-medium ml-2">{{ $attendance->user->employee_id }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Name:</span>
                                    <span class="font-medium ml-2">{{ $attendance->user->name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Department:</span>
                                    <span class="font-medium ml-2">{{ $attendance->user->department ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Position:</span>
                                    <span class="font-medium ml-2">{{ $attendance->user->position ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Info -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Attendance Details</h3>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-gray-500">Date:</span>
                                    <span class="font-medium ml-2">{{ $attendance->date->format('l, F d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Status:</span>
                                    <span class="ml-2 px-2 py-1 text-xs rounded-full 
                                        @if($attendance->status == 'present') bg-green-100 text-green-800
                                        @elseif($attendance->status == 'late') bg-yellow-100 text-yellow-800
                                        @elseif($attendance->status == 'absent') bg-red-100 text-red-800
                                        @elseif($attendance->status == 'on_leave') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Time Details -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-sm text-gray-500">Time In</div>
                    <div class="text-2xl font-bold text-green-600">
                        {{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-sm text-gray-500">Time Out</div>
                    <div class="text-2xl font-bold text-red-600">
                        {{ $attendance->time_out ? $attendance->time_out->format('h:i A') : '-' }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-sm text-gray-500">Work Hours</div>
                    <div class="text-2xl font-bold text-indigo-600">
                        {{ number_format($attendance->total_work_minutes / 60, 1) }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-sm text-gray-500">Break Minutes</div>
                    <div class="text-2xl font-bold text-orange-600">
                        {{ $attendance->total_break_minutes }}
                    </div>
                </div>
            </div>

            <!-- Overtime/Undertime -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500">Overtime</div>
                            <div class="text-xl font-bold text-green-600">
                                {{ number_format($attendance->overtime_minutes / 60, 1) }} hours
                            </div>
                        </div>
                        <div class="text-4xl text-green-200">
                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500">Undertime</div>
                            <div class="text-xl font-bold text-red-600">
                                {{ number_format($attendance->undertime_minutes / 60, 1) }} hours
                            </div>
                        </div>
                        <div class="text-4xl text-red-200">
                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Breaks -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Break Records</h3>
                    @if($attendance->breaks->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Start</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">End</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Duration</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($attendance->breaks as $index => $break)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full 
                                                    @if($break->break_type == 'lunch') bg-orange-100 text-orange-800
                                                    @elseif($break->break_type == 'short') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($break->break_type) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                                {{ $break->break_start->format('h:i A') }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                                {{ $break->break_end ? $break->break_end->format('h:i A') : 'Ongoing' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-medium">
                                                {{ $break->duration_minutes ?? '-' }} min
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No breaks recorded</p>
                    @endif
                </div>
            </div>

            <!-- Remarks -->
            @if($attendance->remarks)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-2">Remarks</h3>
                        <p class="text-gray-700">{{ $attendance->remarks }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
