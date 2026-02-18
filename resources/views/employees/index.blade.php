<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Employees') }}
            </h2>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 transition-colors duration-200 flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Bulk Import
                </button>
                <a href="{{ route('employees.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors duration-200">
                    Add Employee
                </a>
            </div>
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
            <!-- Bulk Actions & Selection -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 transition-all duration-200 border-l-4 border-indigo-400">
                <div class="p-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" 
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 transition-all cursor-pointer">
                            <span class="ml-2 text-sm text-gray-700 font-bold uppercase tracking-wider group-hover:text-indigo-600 transition-colors">Select All</span>
                        </label>
                        <div x-show="selectedEmployees.length > 0" x-cloak class="flex items-center gap-2 px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-bold ring-1 ring-indigo-100">
                            <span x-text="selectedEmployees.length"></span>
                            <span>SELECTED</span>
                        </div>
                    </div>

                    <div x-show="selectedEmployees.length > 0" x-cloak 
                        class="flex flex-wrap items-center gap-4"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0">
                        
                        <!-- Bulk Site Assign -->
                        <form action="{{ route('employees.bulk-assign-site') }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <template x-for="id in selectedEmployees">
                                <input type="hidden" name="employee_ids[]" :value="id">
                            </template>
                            <select name="site_id" required class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 py-1.5 focus:shadow-sm">
                                <option value="">Move to Site...</option>
                                @foreach($allSites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 rounded text-sm font-medium hover:bg-indigo-700 transition shadow-sm">Assign Site</button>
                        </form>

                        <!-- Bulk Account Assign -->
                        <form action="{{ route('employees.bulk-assign-account') }}" method="POST" class="flex items-center gap-2 border-l pl-4 border-gray-200">
                            @csrf
                            <template x-for="id in selectedEmployees">
                                <input type="hidden" name="employee_ids[]" :value="id">
                            </template>
                            <select name="account_id" required class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 py-1.5 focus:shadow-sm">
                                <option value="">Move to Account...</option>
                                @foreach($allAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-1.5 rounded text-sm font-medium hover:bg-blue-700 transition shadow-sm">Assign Account</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 transition-colors duration-200">
                <div class="p-6">
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('import_errors'))
                        <div class="mb-4 p-4 bg-orange-50 border-l-4 border-orange-400 text-orange-700">
                            <p class="font-bold mb-2">Import Errors:</p>
                            <ul class="list-disc list-inside text-sm">
                                @foreach (session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

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
                                <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
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
                                    <th class="px-4 py-3"></th>
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
                                            {{ $employee->assignedDepartment?->name ?? ($employee->department ?? '-') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full font-bold uppercase tracking-wider
                                                @if($employee->role == 'super_admin') bg-indigo-100 text-indigo-700
                                                @elseif($employee->role == 'admin') bg-red-100 text-red-700
                                                @elseif($employee->role == 'hr') bg-green-100 text-green-700
                                                @elseif($employee->role == 'accounting') bg-yellow-100 text-yellow-700
                                                @else bg-gray-100 text-gray-700 @endif">
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
                                            
                                            <!-- Toggle Status -->
                                            <form action="{{ route('employees.toggle-status', $employee) }}" method="POST" class="inline" id="toggle-form-{{ $employee->id }}">
                                                @csrf
                                                <input type="hidden" name="admin_password" id="admin_password_toggle_{{ $employee->id }}">
                                                @if($employee->is_active)
                                                    <button type="button" class="text-red-600 hover:text-red-900 transition-colors duration-200 mr-3" 
                                                        onclick="const password = prompt('Critical Action: You are about to DEACTIVATE this employee. Enter ADMIN PASSWORD to confirm:'); if(password) { document.getElementById('admin_password_toggle_{{ $employee->id }}').value = password; document.getElementById('toggle-form-{{ $employee->id }}').submit(); }">Deactivate</button>
                                                @else
                                                    <button type="submit" class="text-green-600 hover:text-green-900 transition-colors duration-200 mr-3">Activate</button>
                                                @endif
                                            </form>

                                            <!-- Permanent Delete -->
                                            <form action="{{ route('employees.force-delete', $employee) }}" method="POST" class="inline" id="delete-form-{{ $employee->id }}">
                                                @csrf
                                                <input type="hidden" name="admin_password" id="admin_password_delete_{{ $employee->id }}">
                                                <button type="button" class="text-gray-400 hover:text-red-700 transition-all duration-200 group" 
                                                    title="PERMANENTLY REMOVE FROM SYSTEM"
                                                    onclick="if(confirm('DANGER: This will permanently delete ALL records (DTRs, Payroll, Leves) for {{ $employee->name }}. This action CANNOT BE UNDONE. Proceed anyway?')) { const password = prompt('Enter ADMIN PASSWORD to confirm permanent deletion:'); if(password) { document.getElementById('admin_password_delete_{{ $employee->id }}').value = password; document.getElementById('delete-form-{{ $employee->id }}').submit(); } }">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    <span class="text-xs font-bold hidden group-hover:inline ml-1">DELETE</span>
                                                </button>
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

                    <!-- Import Modal -->
                    <div id="importModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('importModal').classList.add('hidden')"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                x-data="{ importing: false, downloaded: false }">
                                <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data" @submit="importing = true">
                                    @csrf
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Bulk Employee Import</h3>
                                                <div class="mt-4 space-y-4">
                                                    <p class="text-sm text-gray-500">
                                                        Upload a CSV file to import multiple employees at once. Please use our template to ensure correct formatting.
                                                    </p>
                                                    <a href="{{ route('employees.import.template') }}" 
                                                        @click="downloaded = true; setTimeout(() => downloaded = false, 3000)"
                                                        class="inline-flex items-center text-sm font-medium transition-colors"
                                                        :class="downloaded ? 'text-green-600' : 'text-indigo-600 hover:text-indigo-500'">
                                                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!downloaded">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                        </svg>
                                                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="downloaded" x-cloak>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        <span x-text="downloaded ? 'Template Downloaded!' : 'Download Template (.csv)'"></span>
                                                    </a>
                                                    <div class="mt-4">
                                                        <label class="block text-sm font-medium text-gray-700">Select File</label>
                                                        <input type="file" name="csv_file" accept=".csv" required 
                                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit" 
                                            :disabled="importing"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" x-show="importing" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span x-text="importing ? 'Processing...' : 'Start Import'"></span>
                                        </button>
                                        <button type="button" 
                                            @click="document.getElementById('importModal').classList.add('hidden')"
                                            :disabled="importing"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
