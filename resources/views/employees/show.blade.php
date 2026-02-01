<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $employee->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('employees.edit', $employee) }}" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">
                    Edit
                </a>
                <a href="{{ route('employees.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Employee Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Personal Information</h3>
                            <div class="space-y-2">
                                <div><span class="text-gray-500">Employee ID:</span> {{ $employee->employee_id }}</div>
                                <div><span class="text-gray-500">Email:</span> {{ $employee->email }}</div>
                                <div><span class="text-gray-500">Role:</span> <span class="px-2 py-1 text-xs rounded-full 
                                    @if($employee->role == 'admin') bg-purple-100 text-purple-800
                                    @elseif($employee->role == 'hr') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">{{ ucfirst($employee->role) }}</span></div>
                                <div><span class="text-gray-500">Status:</span> 
                                    <span class="px-2 py-1 text-xs rounded-full {{ $employee->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Employment Details</h3>
                            <div class="space-y-2">
                                <div><span class="text-gray-500">Department:</span> {{ $employee->department ?? '-' }}</div>
                                <div><span class="text-gray-500">Position:</span> {{ $employee->position ?? '-' }}</div>
                                <div><span class="text-gray-500">Date Hired:</span> {{ $employee->date_hired ? $employee->date_hired->format('M d, Y') : '-' }}</div>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Compensation</h3>
                            <div class="space-y-2">
                                <div><span class="text-gray-500">Monthly Salary:</span> ₱{{ number_format($employee->monthly_salary, 2) }}</div>
                                <div><span class="text-gray-500">Daily Rate:</span> ₱{{ number_format($employee->daily_rate, 2) }}</div>
                                <div><span class="text-gray-500">Hourly Rate:</span> ₱{{ number_format($employee->hourly_rate, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Recent Attendance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Recent Attendance</h3>
                        @if($employee->attendances->count() > 0)
                            <div class="space-y-2">
                                @foreach($employee->attendances as $attendance)
                                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                        <div>
                                            <div class="font-medium">{{ $attendance->date->format('M d, Y') }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }} - 
                                                {{ $attendance->time_out ? $attendance->time_out->format('h:i A') : '-' }}
                                            </div>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($attendance->status == 'present') bg-green-100 text-green-800
                                            @elseif($attendance->status == 'late') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No attendance records.</p>
                        @endif
                    </div>
                </div>

                <!-- Leave Balances -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Leave Balances ({{ date('Y') }})</h3>
                        @if($employee->leaveBalances->count() > 0)
                            <div class="space-y-2">
                                @foreach($employee->leaveBalances as $balance)
                                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                        <div>{{ $balance->leaveType->name }}</div>
                                        <div class="text-right">
                                            <span class="font-bold">{{ $balance->remaining_days }}</span> / {{ $balance->allocated_days }} days
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No leave balances configured.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
