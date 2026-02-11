<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('DTR Management') }}
        </h2>
    </x-sl    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 transition-colors duration-200">
                <div class="p-6">
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <div>
                            <select name="employee" class="border-gray-300 rounded-md shadow-sm">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('employee') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="department" class="border-gray-300 rounded-md shadow-sm">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="month" class="border-gray-300 rounded-md shadow-sm">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <select name="year" class="border-gray-300 rounded-md shadow-sm">
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 transition-colors duration-200">
                <div class="p-4">
                    <form action="{{ route('dtr.bulk-pdf') }}" method="POST" id="bulk-form">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="select-all" class="select-all-main rounded border-gray-300 text-indigo-600">
                                    <span class="ml-2 text-sm text-gray-600">Select All</span>
                                </label>
                                <span class="text-sm text-gray-500">
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">
                        DTR Summary - {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3"></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Present</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Late</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Absent</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Leave</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Work Hours</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">OT Hours</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($dtrData as $item)
                                    <tr class="transition-colors duration-200">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" name="employees[]" value="{{ $item['employee']->id }}" 
                                                form="bulk-form"
                                                class="employee-checkbox rounded border-gray-300 text-indigo-600">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['employee']->employee_id }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item['employee']->name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $item['employee']->department ?? '-' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <span class="text-green-600 font-medium">{{ $item['summary']['present_days'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <span class="text-yellow-600 font-medium">{{ $item['summary']['late_days'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <span class="text-red-600 font-medium">{{ $item['summary']['absent_days'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <span class="text-blue-600 font-medium">{{ $item['summary']['leave_days'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-medium text-gray-900">
                                            {{ $item['summary']['total_work_hours'] }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-medium text-purple-600">
                                            {{ $item['summary']['total_overtime_hours'] }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <a href="{{ route('dtr.show', ['user' => $item['employee']->id, 'month' => $month, 'year' => $year]) }}" 
                                                class="text-indigo-600 hover:text-indigo-900 mr-3 transition-colors duration-200">View</a>
                                            <a href="{{ route('dtr.employee-pdf', ['user' => $item['employee']->id, 'month' => $month, 'year' => $year]) }}" 
                                                class="text-green-600 hover:text-green-900 transition-colors duration-200">PDF</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="px-6 py-4 text-center text-gray-500">No records found</td>
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
            const selectAllMain = document.querySelectorAll('.select-all-main');
            const checkboxes = document.querySelectorAll('.employee-checkbox');
            const selectedCount = document.getElementById('selected-count');
            const bulkDownload = document.getElementById('bulk-download');

            function updateCount() {
                const checked = document.querySelectorAll('.employee-checkbox:checked').length;
                selectedCount.textContent = checked;
                bulkDownload.disabled = checked === 0;
            }

            selectAllMain.forEach(mainCb => {
                mainCb.addEventListener('change', function() {
                    const isChecked = this.checked;
                    checkboxes.forEach(cb => cb.checked = isChecked);
                    // Sync other select-all checkboxes
                    selectAllMain.forEach(otherMain => otherMain.checked = isChecked);
                    updateCount();
                });
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const totalCheckboxes = checkboxes.length;
                    const checkedCheckboxes = document.querySelectorAll('.employee-checkbox:checked').length;
                    
                    if (!this.checked) {
                        selectAllMain.forEach(main => main.checked = false);
                    } else if (checkedCheckboxes === totalCheckboxes) {
                        selectAllMain.forEach(main => main.checked = true);
tAll.checked = false;
                    } else if (document.querySelectorAll('.employee-checkbox:checked').length === checkboxes.length) {
                        selectAll.checked = true;
>>>>>>> 769e9a168b9c48f1c12934e85fb9898739209e6c
h(main => main.checked = false);
                    } else if (checkedCheckboxes === totalCheckboxes) {
                        selectAllMain.forEach(main => main.checked = true);
                    }
                    updateCount();
                });
            });

            updateCount();
        });
    </script>
    @endpush
</x-app-layout>
