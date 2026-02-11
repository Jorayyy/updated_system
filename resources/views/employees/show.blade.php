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
                <a href="javascript:window.history.back()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Employee Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-8">
                        <div class="flex-shrink-0 flex flex-col items-center">
                            @if($employee->profile_photo)
                                <img class="h-48 w-48 rounded-2xl object-cover shadow-lg border-4 border-white" src="{{ asset('storage/' . $employee->profile_photo) }}" alt="{{ $employee->name }}">
                            @else
                                <div class="h-48 w-48 rounded-2xl bg-indigo-100 flex items-center justify-center border-4 border-white shadow-lg">
                                    <span class="text-indigo-700 font-bold text-5xl">{{ strtoupper(substr($employee->name, 0, 2)) }}</span>
                                </div>
                            @endif
                            <div class="mt-4 text-center">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $employee->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex-grow grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Personal Information</h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Employee ID</p>
                                        <p class="font-medium text-gray-900">{{ $employee->employee_id }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Email Address</p>
                                        <p class="font-medium text-gray-900">{{ $employee->email }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">System Role</p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($employee->role == 'super_admin') bg-red-100 text-red-800
                                            @elseif($employee->role == 'admin') bg-purple-100 text-purple-800
                                            @elseif($employee->role == 'hr') bg-blue-100 text-blue-800
                                            @elseif($employee->role == 'accounting') bg-indigo-100 text-indigo-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ str_replace('_', ' ', ucwords($employee->role, '_')) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Employment Details</h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Department</p>
                                        <p class="font-medium text-gray-900">{{ $employee->assignedDepartment?->name ?? ($employee->department ?? 'N/A') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Account</p>
                                        <p class="font-medium text-gray-900">{{ $employee->account?->name ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Site/Location</p>
                                        <p class="font-medium text-gray-900">{{ $employee->site?->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Compensation</h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Monthly Salary</p>
                                        <p class="font-medium text-gray-900">₱{{ number_format($employee->monthly_salary, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Daily Rate</p>
                                        <p class="font-medium text-gray-900">₱{{ number_format($employee->daily_rate, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Date Hired</p>
                                        <p class="font-medium text-gray-900">{{ $employee->date_hired ? $employee->date_hired->format('M d, Y') : 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Government IDs</h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">SSS Number</p>
                                        <p class="font-medium text-gray-900">{{ $employee->sss_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">PhilHealth Number</p>
                                        <p class="font-medium text-gray-900">{{ $employee->philhealth_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Pag-IBIG Number</p>
                                        <p class="font-medium text-gray-900">{{ $employee->pagibig_number ?? 'N/A' }}</p>
                                    </div>
                                </div>
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
