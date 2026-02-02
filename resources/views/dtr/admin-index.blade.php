<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('DTR Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 transition-colors duration-200">
                <div class="p-6">
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <div>
                            <select name="employee" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('employee') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="department" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="month" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <select name="year" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 transition-colors duration-200">
                <div class="p-4">
                    <form action="{{ route('dtr.bulk-pdf') }}" method="POST" id="bulk-form">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="select-all" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-indigo-600">
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Select All</span>
                                </label>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    <span id="selected-count">0</span> selected
                                </span>
                            </div>
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50 transition-colors duration-200" id="bulk-download">
                                Download Selected DTR (PDF)
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Employees DTR Summary -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                        DTR Summary - {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        <input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-indigo-600" disabled>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Employee ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Department</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Present</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Late</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Absent</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Leave</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Work Hours</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">OT Hours</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($dtrData as $item)
                                    <tr class="transition-colors duration-200">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" name="employees[]" value="{{ $item['employee']->id }}" 
                                                form="bulk-form"
                                                class="employee-checkbox rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-indigo-600">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $item['employee']->employee_id }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $item['employee']->name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item['employee']->department ?? '-' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <span class="text-green-600 dark:text-green-400 font-medium">{{ $item['summary']['present_days'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <span class="text-yellow-600 dark:text-yellow-400 font-medium">{{ $item['summary']['late_days'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <span class="text-red-600 dark:text-red-400 font-medium">{{ $item['summary']['absent_days'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <span class="text-blue-600 dark:text-blue-400 font-medium">{{ $item['summary']['leave_days'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-medium text-gray-900 dark:text-white">
                                            {{ $item['summary']['total_work_hours'] }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-medium text-purple-600 dark:text-purple-400">
                                            {{ $item['summary']['total_overtime_hours'] }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <a href="{{ route('dtr.show', ['user' => $item['employee']->id, 'month' => $month, 'year' => $year]) }}" 
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3 transition-colors duration-200">View</a>
                                            <a href="{{ route('dtr.employee-pdf', ['user' => $item['employee']->id, 'month' => $month, 'year' => $year]) }}" 
                                                class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 transition-colors duration-200">PDF</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.employee-checkbox');
            const selectedCount = document.getElementById('selected-count');
            const bulkDownload = document.getElementById('bulk-download');

            function updateCount() {
                const checked = document.querySelectorAll('.employee-checkbox:checked').length;
                selectedCount.textContent = checked;
                bulkDownload.disabled = checked === 0;
            }

            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateCount();
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    if (!this.checked) {
                        selectAll.checked = false;
                    } else if (document.querySelectorAll('.employee-checkbox:checked').length === checkboxes.length) {
                        selectAll.checked = true;
                    }
                    updateCount();
                });
            });

            updateCount();
        });
    </script>
    @endpush
</x-app-layout>
