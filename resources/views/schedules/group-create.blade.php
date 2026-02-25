<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-red-800 text-white px-6 py-5 -mx-4 sm:-mx-6 lg:-mx-8 -mt-4 shadow-lg border-b-4 border-red-900">
            <h2 class="font-bold text-xl uppercase tracking-widest flex items-center">
                SITE ADMINS - GROUP PLOTTING
            </h2>
            <a href="{{ route('schedules.index') }}" class="hover:scale-110 transition-transform bg-white/10 p-1.5 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-blue-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100">
                <div class="p-8">
                    <form method="POST" action="{{ route('schedules.group-store') }}">
                        @csrf
                        
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                            {{-- Step 1: Select Employees --}}
                            <div class="space-y-4">
                                <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest border-b pb-2">1. Select Employees</h3>
                                
                                <div class="bg-slate-50 p-4 rounded-md space-y-3">
                                    <div class="text-xs font-bold text-slate-500 uppercase">Filters & Search:</div>
                                    <div class="space-y-2">
                                        {{-- Search Input --}}
                                        <div class="relative">
                                            <input type="text" id="employeeSearch" 
                                                   class="w-full text-xs rounded border-gray-200 pl-8 pr-4 py-2 focus:ring-red-500 shadow-sm" 
                                                   placeholder="Search name or ID...">
                                            <svg class="w-4 h-4 absolute left-2.5 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </div>

                                        <select name="site_id" id="siteFilter" class="w-full text-xs rounded border-gray-200">
                                            <option value="">Filter by Site...</option>
                                            @foreach($sites as $site)
                                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                                            @endforeach
                                        </select>

                                        <select name="department_id" id="departmentFilter" class="w-full text-xs rounded border-gray-200">
                                            <option value="">Filter by Department...</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="max-h-[500px] overflow-y-auto border border-gray-100 rounded bg-gray-50/30">
                                    <div id="loadingIndicator" class="hidden p-8 text-center">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600 mx-auto"></div>
                                        <p class="text-[10px] text-slate-400 mt-2 font-bold uppercase">Fetching Employees...</p>
                                    </div>
                                    <table class="min-w-full divide-y divide-gray-100" id="employeeTable">
                                        <thead class="bg-gray-100 sticky top-0">
                                            <tr>
                                                <th class="px-3 py-2 text-left">
                                                    <input type="checkbox" id="select-all" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                </th>
                                                <th class="px-3 py-2 text-left text-[10px] font-black uppercase text-gray-500">Employee</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50 bg-white" id="employeeListBody">
                                            <tr><td colspan="2" class="p-8 text-center text-[10px] text-gray-400 italic">Please use the filters or search bar above to find employees.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Step 2: Set Weekly Schedule --}}
                            <div class="lg:col-span-2 space-y-6">
                                <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest border-b pb-2">2. Set Weekly Schedule</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                        <div>
                                            <div class="flex justify-between items-center mb-1">
                                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest">{{ $day }}</label>
                                                <button type="button" onclick="toggleManualInput('{{ $day }}')" class="text-[9px] font-bold text-blue-600 hover:text-blue-800 uppercase tracking-tighter">
                                                    Manual Entry
                                                </button>
                                            </div>
                                            <div id="{{ $day }}_select_container">
                                                <select name="{{ $day }}_schedule" id="{{ $day }}_select" class="w-full border-gray-200 rounded text-sm focus:ring-red-500 focus:border-red-500 shift-selector">
                                                    <option value="Rest day">Rest day</option>
                                                    @foreach($schedules->groupBy(fn($s) => $s->department->name ?? 'GENERAL') as $deptName => $deptShifts)
                                                        <optgroup label="{{ $deptName }}" data-dept-name="{{ $deptName }}">
                                                            @foreach($deptShifts as $shift)
                                                                @php
                                                                    $shiftTime = \Carbon\Carbon::parse($shift->time_in)->format('H:i') . ' to ' . \Carbon\Carbon::parse($shift->time_out)->format('H:i');
                                                                @endphp
                                                                <option value="{{ $shiftTime }}">{{ $shiftTime }}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div id="{{ $day }}_manual_container" class="hidden flex gap-2">
                                                <div class="flex-1">
                                                    <input type="time" id="{{ $day }}_start" class="w-full text-xs border-gray-200 rounded p-1 mb-1" placeholder="Start">
                                                    <input type="time" id="{{ $day }}_end" class="w-full text-xs border-gray-200 rounded p-1" placeholder="End">
                                                </div>
                                                <button type="button" onclick="applyManualTime('{{ $day }}')" class="bg-blue-50 text-blue-700 px-2 rounded text-[10px] font-bold uppercase border border-blue-200">Set</button>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Special Options Section --}}
                                    <div class="md:col-start-2 pt-4 border-t border-slate-100 lg:border-t-0 lg:pt-0">
                                        <div class="space-y-6">
                                            <div>
                                                <div class="flex items-center mb-2">
                                                    <label class="font-black text-[11px] text-gray-800 uppercase tracking-widest mr-2">Special 1 Hour Only</label>
                                                    <input type="hidden" name="special_1_hour_only" value="0">
                                                    <input type="checkbox" name="special_1_hour_only" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="flex items-center mb-2">
                                                    <label class="font-black text-[11px] text-gray-800 uppercase tracking-widest mr-2">Special Case / Present Policy</label>
                                                    <input type="hidden" name="special_case_policy" value="0">
                                                    <input type="checkbox" name="special_case_policy" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-12">
                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-4 rounded shadow-lg transition-all transform hover:-translate-y-0.5 active:translate-y-0 uppercase tracking-widest text-xs flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                        </svg>
                                        APPLY SCHEDULE TO ALL SELECTED EMPLOYEES
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const employeeListBody = document.getElementById('employeeListBody');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const employeeTable = document.getElementById('employeeTable');
        const selectAllCb = document.getElementById('select-all');

        function fetchEmployees() {
            const search = document.getElementById('employeeSearch').value;
            const siteId = document.getElementById('siteFilter').value;
            const departmentFilter = document.getElementById('departmentFilter');
            const departmentId = departmentFilter.value;
            const selectedDeptName = departmentFilter.options[departmentFilter.selectedIndex].text;

            // Filter the shifts dropdowns based on the selected department
            document.querySelectorAll('.shift-selector').forEach(select => {
                const optgroups = select.querySelectorAll('optgroup');
                let foundMatch = false;
                
                optgroups.forEach(og => {
                    if (!departmentId || og.getAttribute('data-dept-name') === selectedDeptName) {
                        og.classList.remove('hidden');
                        // For Browsers that don't support hidden on optgroup, we might need a different approach
                        // but Tailwind/Modern browsers handle it.
                        og.style.display = '';
                        foundMatch = true;
                    } else {
                        og.classList.add('hidden');
                        og.style.display = 'none';
                    }
                });
            });

            // Only fetch if at least one filter has a value (search or selects)
            if (!search && !siteId && !departmentId) {
                employeeListBody.innerHTML = '<tr><td colspan="2" class="p-8 text-center text-[10px] text-gray-400 italic uppercase">Please use the filters or search bar above to find employees.</td></tr>';
                return;
            }

            loadingIndicator.classList.remove('hidden');
            employeeTable.classList.add('opacity-50');

            const params = new URLSearchParams({
                search: search,
                site_id: siteId,
                department_id: departmentId
            });

            fetch(`{{ route('schedules.group-create') }}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                loadingIndicator.classList.add('hidden');
                employeeTable.classList.remove('opacity-50');
                
                if (data.length === 0) {
                    employeeListBody.innerHTML = '<tr><td colspan="2" class="p-8 text-center text-[10px] text-gray-400 italic uppercase">No employees found matching your criteria.</td></tr>';
                    return;
                }

                let html = '';
                data.forEach(user => {
                    html += `
                        <tr class="hover:bg-red-50/30">
                            <td class="px-3 py-2">
                                <input type="checkbox" name="user_ids[]" value="${user.id}" class="employee-cb rounded border-gray-300 text-red-600 focus:ring-red-500">
                            </td>
                            <td class="px-3 py-2">
                                <div class="text-[11px] font-bold text-gray-800 uppercase">${user.name}</div>
                                <div class="text-[10px] text-gray-500">${user.employee_id} | ${user.site_name} | ${user.department_name}</div>
                            </td>
                        </tr>
                    `;
                });
                employeeListBody.innerHTML = html;
                selectAllCb.checked = false; // Reset select all
            })
            .catch(error => {
                console.error('Error fetching employees:', error);
                loadingIndicator.classList.add('hidden');
                employeeTable.classList.remove('opacity-50');
            });
        }

        // Attach listeners
        document.getElementById('siteFilter').addEventListener('change', fetchEmployees);
        document.getElementById('departmentFilter').addEventListener('change', fetchEmployees);
        
        // Debounce search
        let searchTimeout;
        document.getElementById('employeeSearch').addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(fetchEmployees, 300);
        });

        function toggleManualInput(day) {
            const selectContainer = document.getElementById(`${day}_select_container`);
            const manualContainer = document.getElementById(`${day}_manual_container`);
            
            if (manualContainer.classList.contains('hidden')) {
                manualContainer.classList.remove('hidden');
                selectContainer.classList.add('hidden');
            } else {
                manualContainer.classList.add('hidden');
                selectContainer.classList.remove('hidden');
            }
        }

        function applyManualTime(day) {
            const start = document.getElementById(`${day}_start`).value;
            const end = document.getElementById(`${day}_end`).value;
            const select = document.getElementById(`${day}_select`);
            
            if (!start || !end) {
                alert('Please provide both start and end times');
                return;
            }

            const formattedTime = `${start} to ${end}`;
            
            // Add custom option to select and select it
            let exists = false;
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value === formattedTime) {
                    select.selectedIndex = i;
                    exists = true;
                    break;
                }
            }
            
            if (!exists) {
                const option = new Option(formattedTime, formattedTime);
                select.add(option);
                select.value = formattedTime;
            }

            // Switch back to select view
            toggleManualInput(day);
        }

        document.getElementById('select-all').addEventListener('change', function() {
            document.querySelectorAll('.employee-cb').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    </script>
    @endpush
</x-app-layout>