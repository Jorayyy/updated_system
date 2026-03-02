<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('payroll-groups.index') }}" class="text-gray-500 hover:text-gray-700" title="Back to Groups">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payroll Group Details: ') . $payrollGroup->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-modal name="batch-add-employee" :show="false" focusable>
                <form method="post" action="{{ route('payroll-groups.add-employees', $payrollGroup) }}" class="p-6">
                    @csrf
                    <h2 class="text-lg font-bold text-gray-900 mb-4">
                        Manage Employees for {{ $payrollGroup->name }}
                    </h2>

                    <div class="mb-4">
                        <input type="text" id="employeeSearch" placeholder="Search employees..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="max-h-96 overflow-y-auto mb-6 border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-10">
                                        <input type="checkbox" id="selectAllEmployees" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Current Group</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="employeeTableBody">
                                @foreach($availableUsers as $user)
                                    @php
                                        $isAlreadyAssigned = $user->payroll_group_id && $user->payroll_group_id != $payrollGroup->id;
                                    @endphp
                                    <tr class="hover:bg-indigo-50 transition-colors {{ $isAlreadyAssigned ? 'opacity-50 cursor-not-allowed bg-gray-50' : 'cursor-pointer employee-row' }}" 
                                        data-name="{{ strtolower($user->full_name) }} {{ strtolower($availableUsersSelect[$user->id] ?? '') }}">
                                        <td class="px-4 py-2">
                                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" 
                                                class="employee-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                                                {{ $user->payroll_group_id == $payrollGroup->id ? 'checked' : '' }}
                                                {{ $isAlreadyAssigned ? 'disabled' : '' }}>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="text-sm font-bold text-gray-900">{{ $availableUsersSelect[$user->id] ?? $user->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                        </td>
                                        <td class="px-4 py-2">
                                            @if($user->payroll_group_id == $payrollGroup->id)
                                                <span class="px-2 py-0.5 text-[10px] rounded bg-green-100 text-green-700 font-bold uppercase border border-green-200">Current</span>
                                            @elseif($user->payroll_group_id)
                                                <span class="px-2 py-0.5 text-[10px] rounded bg-yellow-100 text-yellow-700 font-bold uppercase border border-yellow-200" title="Assigned to {{ $user->payrollGroup->name ?? 'another group' }}">{{ $user->payrollGroup->name ?? 'Assigned' }}</span>
                                            @else
                                                <span class="px-2 py-0.5 text-[10px] rounded bg-gray-100 text-gray-500 font-bold uppercase italic border border-gray-200">Unassigned</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t">
                        <x-secondary-button x-on:click="$dispatch('close')" type="button">
                            Cancel
                        </x-secondary-button>
                        <x-primary-button>
                            Update Assignments
                        </x-primary-button>
                    </div>
                </form>

                <script>
                    (function() {
                        const initModal = () => {
                            const searchInput = document.getElementById('employeeSearch');
                            const rows = document.querySelectorAll('.employee-row');
                            const selectAll = document.getElementById('selectAllEmployees');
                            
                            if (!searchInput || !rows.length) return;

                            searchInput.addEventListener('input', (e) => {
                                const term = e.target.value.toLowerCase();
                                rows.forEach(row => {
                                    const searchable = row.getAttribute('data-name');
                                    row.style.display = searchable.includes(term) ? '' : 'none';
                                });
                            });

                            if (selectAll) {
                                selectAll.addEventListener('change', (e) => {
                                    const checkboxes = document.querySelectorAll('.employee-checkbox:not(:disabled)');
                                    checkboxes.forEach(cb => {
                                        if (cb.closest('.employee-row').style.display !== 'none') {
                                            cb.checked = e.target.checked;
                                        }
                                    });
                                });
                            }

                            rows.forEach(row => {
                                row.addEventListener('click', (e) => {
                                    const cb = row.querySelector('.employee-checkbox');
                                    if (cb && !cb.disabled && e.target.tagName !== 'INPUT') {
                                        cb.checked = !cb.checked;
                                    }
                                });
                            });
                        };
                        
                        // Run on load and whenever modal might be re-rendered
                        document.addEventListener('DOMContentLoaded', initModal);
                        if (window.Alpine) {
                            document.addEventListener('alpine:init', initModal);
                        }
                    })();
                </script>
            </x-modal>
            
            <!-- Group Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Group Information</h3>
                            <div class="mt-2 text-sm text-gray-600">
                                <p><span class="font-semibold">Period Type:</span> {{ ucfirst($payrollGroup->period_type) }}</p>
                                <p><span class="font-semibold">Status:</span> {{ $payrollGroup->is_active ? 'Active' : 'Inactive' }}</p>
                                <p><span class="font-semibold">Total Employees:</span> {{ $payrollGroup->users->count() }}</p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('payroll-groups.edit', $payrollGroup) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Edit Details</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Employees Management -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Assigned Employees</h3>
                            <button x-data="" @click="$dispatch('open-modal', 'batch-add-employee')" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-bold text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Manage Employees
                            </button>
                        </div>
                        
                        <div class="flex flex-wrap gap-2 mb-6">
                            <a href="{{ route('schedules.group-create', ['payroll_group_id' => $payrollGroup->id]) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 transition ease-in-out duration-150">
                                📅 Plot Group Schedule
                            </a>
                        </div>

                        <!-- Employee List -->
                        <div class="overflow-y-auto max-h-96">
                            <ul class="divide-y divide-gray-200">
                                @php $any = false; @endphp
                                @foreach($groupedUsers as $role => $users)
                                    @php $any = true; @endphp
                                    <li class="py-2 px-3 bg-gray-50 text-xs text-gray-600 font-semibold">{{ ucfirst($role) }}</li>
                                    @foreach($users as $user)
                                        <li class="py-3 flex justify-between items-center">
                                            <div class="flex items-center">
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">{{ $user->full_name }} <span class="text-xs text-gray-400">@if($user->employee_id) • {{ $user->employee_id }} @endif</span></p>
                                                    <p class="text-xs text-gray-500">{{ $user->email }} <span class="ml-2 inline-block px-2 py-0.5 text-[10px] rounded bg-gray-100 text-gray-700">{{ ucfirst($user->role ?? 'unknown') }}</span></p>
                                                </div>
                                            </div>
                                            <form action="{{ route('payroll-groups.remove-employee', ['payrollGroup' => $payrollGroup, 'user' => $user]) }}" method="POST" onsubmit="return confirm('Remove this employee from the group?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Remove</button>
                                            </form>
                                        </li>
                                    @endforeach
                                @endforeach

                                @unless($any)
                                    <li class="py-4 text-center text-gray-500 text-sm">No employees assigned to this group.</li>
                                @endunless
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Recent Payroll Periods -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Recent Payroll Periods</h3>
                            <a href="{{ route('payroll.create-period', ['payroll_group_id' => $payrollGroup->id]) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                ➕ Generate Period
                            </a>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($payrollGroup->periods as $period)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $period->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                       ($period->status === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                                    {{ ucfirst($period->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-500">
                                                <a href="{{ route('payroll.show-period', $period) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-4 text-center text-gray-500 text-sm">No payroll periods generated for this group yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
