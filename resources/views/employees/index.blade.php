<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Employees') }}
            </h2>
            <a href="{{ route('employees.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors duration-200">
                Add Employee
            </a>
        </div>
    </x-slot>

    <div class="py-4" x-data="{ 
        selectedEmployees: [],
        selectAll: false,
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedEmployees = Array.from(document.querySelectorAll('input[name=\'employees[]\']')).map(el => el.value);
            } else {
                this.selectedEmployees = [];
            }
        }
    }">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Bulk Actions -->
            <div x-show="selectedEmployees.length > 0" x-cloak
                class="bg-indigo-50 border-l-4 border-indigo-400 p-4 mb-6 flex flex-wrap items-center justify-between gap-4 sticky top-0 z-10 shadow-md rounded-r-lg">
                <div class="flex items-center">
                    <span class="text-indigo-700 font-medium" x-text="selectedEmployees.length + ' employees selected'"></span>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Bulk Site Assign -->
                    <form action="{{ route('employees.bulk-assign-site') }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <template x-for="id in selectedEmployees">
                            <input type="hidden" name="employee_ids[]" :value="id">
                        </template>
                        <select name="site_id" required class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Move to Site...</option>
                            @foreach($allSites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700">Assign Site</button>
                    </form>

                    <!-- Bulk Account Assign -->
                    <form action="{{ route('employees.bulk-assign-account') }}" method="POST" class="flex items-center gap-2 border-l pl-4 border-indigo-200">
                        @csrf
                        <template x-for="id in selectedEmployees">
                            <input type="hidden" name="employee_ids[]" :value="id">
                        </template>
                        <select name="account_id" required class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Move to Account...</option>
                            @foreach($allAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-blue-600 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-700">Assign Account</button>
                    </form>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 transition-colors duration-200">
                <div class="p-6">
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Search by name, email, ID..."
                            class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        
                        <select name="role" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">All Roles</option>
                            <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="hr" {{ request('role') == 'hr' ? 'selected' : '' }}>HR</option>
                            <option value="accounting" {{ request('role') == 'accounting' ? 'selected' : '' }}>Accounting</option>
                            <option value="employee" {{ request('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                        </select>

                        <select name="department" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>

                        <select name="account_id" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">All Accounts</option>
                            @foreach($allAccounts as $account)
                                <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                            @endforeach
                        </select>

                        <select name="site_id" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">All Sites</option>
                            @foreach($allSites as $site)
                                <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                            @endforeach
                        </select>

                        <select name="status" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>

                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors duration-200">
                            Filter
                        </button>
                        <a href="{{ route('employees.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                            Reset
                        </a>
                    </form>
                </div>
            </div>

            <!-- Employees Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-200">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left">
                                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account/Site</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($employees as $employee)
                                    <tr class="transition-colors duration-200" :class="selectedEmployees.includes('{{ $employee->id }}') ? 'bg-indigo-50' : ''">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <input type="checkbox" name="employees[]" value="{{ $employee->id }}" x-model="selectedEmployees"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $employee->employee_id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    @if($employee->profile_photo)
                                                        <img class="h-10 w-10 rounded-full object-cover border border-gray-200" src="{{ asset('storage/' . $employee->profile_photo) }}" alt="{{ $employee->name }}">
                                                    @else
                                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center border border-indigo-200">
                                                            <span class="text-indigo-700 font-medium text-sm">{{ strtoupper(substr($employee->name, 0, 2)) }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $employee->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="font-medium text-gray-900">{{ $employee->account?->name ?? 'N/A' }}</div>
                                            <div class="text-xs">{{ $employee->site?->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $employee->department ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($employee->role == 'admin') bg-purple-100 text-purple-800
                                                @elseif($employee->role == 'hr') bg-blue-100 text-blue-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($employee->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ $employee->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="{{ route('employees.show', $employee) }}" class="text-indigo-600 hover:text-indigo-900 mr-3 transition-colors duration-200">View</a>
                                            <a href="{{ route('employees.edit', $employee) }}" class="text-yellow-600 hover:text-yellow-900 mr-3 transition-colors duration-200">Edit</a>
                                            <form action="{{ route('employees.toggle-status', $employee) }}" method="POST" class="inline" id="toggle-form-{{ $employee->id }}">
                                                @csrf
                                                <input type="hidden" name="admin_password" id="admin_password_{{ $employee->id }}">
                                                @if($employee->is_active)
                                                    <button type="button" class="text-red-600 hover:text-red-900 transition-colors duration-200" 
                                                        onclick="const password = prompt('Critical Action: You are about to DEACTIVATE this employee. Enter ADMIN PASSWORD to confirm:'); if(password) { document.getElementById('admin_password_{{ $employee->id }}').value = password; document.getElementById('toggle-form-{{ $employee->id }}').submit(); }">Deactivate</button>
                                                @else
                                                    <button type="submit" class="text-green-600 hover:text-green-900 transition-colors duration-200">Activate</button>
                                                @endif
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            No employees found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
