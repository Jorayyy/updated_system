<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Leave Credits Management') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-sm text-gray-500">Year</div>
                    <div class="text-2xl font-bold text-indigo-600">{{ $year }}</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-sm text-gray-500">Total Active Employees</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $totalEmployees }}</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-sm text-gray-500">With Credits Allocated</div>
                    <div class="text-2xl font-bold text-green-600">{{ $employeesWithCredits }}</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-sm text-gray-500">Without Credits</div>
                    <div class="text-2xl font-bold {{ $employeesWithoutCredits > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        {{ $employeesWithoutCredits }}
                    </div>
                </div>
            </div>

            <!-- Actions and Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap gap-4 items-end justify-between">
                        <!-- Filters -->
                        <form method="GET" class="flex flex-wrap gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                                <select name="year" class="border-gray-300 rounded-md shadow-sm">
                                    @for($y = date('Y') + 1; $y >= date('Y') - 3; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                <select name="department" class="border-gray-300 rounded-md shadow-sm">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                    placeholder="Name, ID, or Email"
                                    class="border-gray-300 rounded-md shadow-sm">
                            </div>
                            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                                Filter
                            </button>
                            <a href="{{ route('leave-credits.index') }}" class="text-gray-600 hover:text-gray-800 py-2">
                                Clear
                            </a>
                        </form>

                        <!-- Bulk Actions -->
                        <div class="flex gap-2">
                            <button type="button" onclick="document.getElementById('bulkAllocateModal').classList.remove('hidden')"
                                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Bulk Allocate
                            </button>
                            <button type="button" onclick="document.getElementById('carryOverModal').classList.remove('hidden')"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Carry Over
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Types Legend -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <div class="flex flex-wrap gap-4 items-center">
                        <span class="text-sm font-medium text-gray-700">Leave Types:</span>
                        @foreach($leaveTypes as $type)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                style="background-color: {{ $type->color }}20; color: {{ $type->color }}">
                                {{ $type->code ?? substr($type->name, 0, 2) }}: {{ $type->name }} ({{ $type->max_days }} days)
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Employees Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Employee
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Department
                                    </th>
                                    @foreach($leaveTypes as $type)
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            title="{{ $type->name }}">
                                            {{ $type->code ?? substr($type->name, 0, 3) }}
                                        </th>
                                    @endforeach
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($employees as $employee)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $employee->employee_id }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $employee->department ?? '-' }}
                                        </td>
                                        @foreach($leaveTypes as $type)
                                            @php
                                                $balance = $employee->leaveBalances->where('leave_type_id', $type->id)->first();
                                            @endphp
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                @if($balance)
                                                    <div class="text-sm font-medium {{ $balance->remaining_days > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $balance->remaining_days }}
                                                    </div>
                                                    <div class="text-xs text-gray-400">
                                                        / {{ $balance->allocated_days }}
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex justify-center gap-2">
                                                <a href="{{ route('leave-credits.edit', ['employee' => $employee->id, 'year' => $year]) }}" 
                                                    class="text-indigo-600 hover:text-indigo-900" title="Edit Credits">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </a>
                                                <button type="button" 
                                                    onclick="openAdjustModal({{ $employee->id }}, '{{ $employee->name }}')"
                                                    class="text-yellow-600 hover:text-yellow-900" title="Quick Adjust">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                                    </svg>
                                                </button>
                                                <a href="{{ route('leave-credits.history', $employee->id) }}" 
                                                    class="text-gray-600 hover:text-gray-900" title="View History">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 3 + count($leaveTypes) }}" class="px-6 py-4 text-center text-gray-500">
                                            No employees found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $employees->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Allocate Modal -->
    <div id="bulkAllocateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Allocate Leave Credits</h3>
                <form action="{{ route('leave-credits.bulk-allocate') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                        <select name="year" class="w-full border-gray-300 rounded-md shadow-sm">
                            @for($y = date('Y') + 1; $y >= date('Y'); $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Allocation Type</label>
                        <select name="allocation_type" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="missing_only">Only employees without credits</option>
                            <option value="all">Reset ALL employees (use with caution)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Credits will be set to each leave type's maximum days.</p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('bulkAllocateModal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-500 hover:text-gray-700">Cancel</button>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            Allocate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Carry Over Modal -->
    <div id="carryOverModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Carry Over Leave Credits</h3>
                <form action="{{ route('leave-credits.carry-over') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type</label>
                        <select name="leave_type_id" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Year</label>
                        <select name="from_year" class="w-full border-gray-300 rounded-md shadow-sm">
                            @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Year</label>
                        <select name="to_year" class="w-full border-gray-300 rounded-md shadow-sm">
                            @for($y = date('Y') + 1; $y >= date('Y'); $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Days to Carry Over</label>
                        <input type="number" name="max_carryover" value="5" min="0" max="365" step="0.5"
                            class="w-full border-gray-300 rounded-md shadow-sm" required>
                        <p class="text-xs text-gray-500 mt-1">Maximum unused days each employee can carry over.</p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('carryOverModal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-500 hover:text-gray-700">Cancel</button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Carry Over
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Adjust Modal -->
    <div id="adjustModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Quick Adjust Credits - <span id="adjustEmployeeName"></span>
                </h3>
                <form id="adjustForm" method="POST">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type</label>
                        <select name="leave_type_id" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment</label>
                        <div class="flex gap-2">
                            <select name="adjustment_type" class="border-gray-300 rounded-md shadow-sm" required>
                                <option value="add">Add (+)</option>
                                <option value="deduct">Deduct (-)</option>
                            </select>
                            <input type="number" name="days" value="1" min="0.5" max="365" step="0.5"
                                class="flex-1 border-gray-300 rounded-md shadow-sm" required>
                            <span class="py-2 text-gray-600">days</span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                        <textarea name="reason" rows="2" class="w-full border-gray-300 rounded-md shadow-sm" 
                            required placeholder="Enter reason for adjustment..."></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('adjustModal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-500 hover:text-gray-700">Cancel</button>
                        <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">
                            Apply Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAdjustModal(employeeId, employeeName) {
            document.getElementById('adjustEmployeeName').textContent = employeeName;
            document.getElementById('adjustForm').action = '/leave-credits/' + employeeId + '/adjust';
            document.getElementById('adjustModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
