<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-red-800 leading-tight uppercase tracking-tight">
                {{ App\Models\CompanySetting::getValue('company_name', 'Mancao Electronic Connect Business Solutions OPC') }} SITE ADMINS <span class="ml-2">📅</span>
            </h2>
            <div class="flex items-center space-x-4">
                {{-- Group Plotting Button --}}
                <a href="{{ route('schedules.group-create') }}" class="flex items-center space-x-2 bg-red-600 text-white px-3 py-1.5 rounded text-xs font-bold uppercase tracking-widest hover:bg-red-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Group Plotting</span>
                </a>

                <div class="flex items-center space-x-2 border-l pl-4 border-gray-200">
                    <a href="{{ route('employees.create', ['from' => 'schedules']) }}" class="text-green-600 hover:text-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-4 bg-gray-50 min-h-screen" x-data="{ activeTab: 'late', lateOption: 'occurences', absenceOption: 'occurences' }">
        <div class="max-w-[95%] mx-auto">
            <!-- Filters Section (Consistent with Employees page) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 transition-colors duration-200">
                <div class="p-6">
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Search by name, ID, or email..."
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                        </div>
                        
                        <select name="site_id" onchange="this.form.submit()" class="border-gray-300 rounded-md shadow-sm text-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">All Sites</option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                            @endforeach
                        </select>

                        <select name="account_id" onchange="this.form.submit()" class="border-gray-300 rounded-md shadow-sm text-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                            @endforeach
                        </select>

                        <select name="department_id" onchange="this.form.submit()" class="border-gray-300 rounded-md shadow-sm text-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors duration-200 text-sm font-bold uppercase">
                            Filter
                        </button>
                        <a href="{{ route('schedules.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200 text-sm font-medium">
                            Reset
                        </a>
                    </form>
                </div>
            </div>

            <!-- Main Employee Table -->
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden mb-8">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-lg font-bold text-gray-800 uppercase tracking-tight">Employee Schedules</h3>
                    <div class="text-sm text-gray-500 italic">Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries</div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Employee ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Account/Site</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Department</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $user->employee_id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($user->profile_photo)
                                                    <img class="h-10 w-10 rounded-full object-cover border border-gray-200" src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center border border-red-200">
                                                        <span class="text-red-700 font-bold text-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900 uppercase tracking-tight">{{ $user->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="font-bold text-gray-900">{{ $user->account->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500 uppercase">{{ $user->site->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 uppercase font-medium">
                                        {{ $user->assignedDepartment->name ?? $user->department ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full uppercase tracking-wider {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center space-x-3">
                                        {{-- Actions with tooltips --}}
                                        <a href="{{ route('schedules.edit', $user->id) }}" class="text-amber-500 hover:text-amber-700 transition-all duration-200 transform hover:scale-110" title="Edit Weekly Schedule">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('dtr.show', $user->id) }}" class="text-cyan-500 hover:text-cyan-700 transition-all duration-200 transform hover:scale-110" title="View Daily Time Record">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        <button class="text-rose-500 hover:text-rose-700 transition-all duration-200 transform hover:scale-110" title="Deactivate Employee">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path><line x1="12" y1="2" x2="12" y2="12"></line></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $users->onEachSide(1)->links() }}
                </div>
            </div>

            {{-- New Late and Absences Settings Section --}}
            <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Sidebar / Sidebar Menu -->
                <div class="md:col-span-1">
                    <div class="bg-emerald-600 text-white rounded-t-lg p-4 flex items-center space-x-2 font-bold shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="uppercase tracking-tight text-sm">Late and Absences Settings</span>
                    </div>
                    <div class="bg-white border-x border-b border-gray-200 rounded-b-lg shadow-sm">
                        <ul class="divide-y divide-gray-100 italic font-medium text-xs text-slate-600">
                            <li @click="activeTab = 'late'" class="p-4 flex items-center space-x-3 cursor-pointer transition-colors"
                                :class="activeTab === 'late' ? 'bg-emerald-50/50 text-emerald-700 font-bold' : 'hover:bg-gray-50'">
                                <a>Late Settings</a>
                            </li>
                            <li @click="activeTab = 'absences'" class="p-4 flex items-center space-x-3 cursor-pointer transition-colors"
                                :class="activeTab === 'absences' ? 'bg-emerald-50/50 text-emerald-700 font-bold' : 'hover:bg-gray-50'">
                                <a>Absences Settings</a>
                            </li>
                            <li @click="activeTab = 'basis'" class="p-4 flex items-center space-x-3 cursor-pointer transition-colors"
                                :class="activeTab === 'basis' ? 'bg-emerald-50/50 text-emerald-700 font-bold' : 'hover:bg-gray-50'">
                                <a>Late and Absencies Basis</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="md:col-span-3">
                    <!-- Late Settings Content -->
                    <div x-show="activeTab === 'late'" class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                        <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center">
                            <h3 class="flex items-center text-sm font-black text-rose-800 uppercase tracking-tight">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                Late Occurence Settings
                            </h3>
                        </div>
                        <div class="p-8 bg-white flex flex-col items-center space-y-6">
                            <div class="w-full max-w-lg flex items-center justify-center space-x-4">
                                <span class="text-xs font-bold text-gray-700 whitespace-nowrap">Select Site:</span>
                                <select class="w-full border-gray-300 rounded text-sm text-gray-600 py-1.5 focus:border-red-500 focus:ring-red-500">
                                    <option value="">Select Site</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full max-w-lg text-center font-bold">
                                <select x-model="lateOption" class="w-full border-gray-300 rounded text-sm text-gray-600 py-1.5 focus:border-red-500 focus:ring-red-500">
                                    <option value="occurences">Occurences</option>
                                    <option value="total">Total</option>
                                </select>
                            </div>

                            <div class="w-full mt-6">
                                <div class="text-center mb-6">
                                    <h4 class="text-rose-600 font-bold text-lg italic lowercase first-letter:uppercase" x-text="lateOption === 'occurences' ? 'Occurences Late and Absence Settings' : 'Total Late and Absence Settings'"></h4>
                                </div>

                                <div class="flex justify-between items-center mb-4 text-xs text-gray-600 px-4">
                                    <div class="flex items-center">
                                        Show 
                                        <select class="mx-2 border-gray-300 rounded text-xs py-1">
                                            <option>10</option>
                                            <option>25</option>
                                            <option>50</option>
                                        </select>
                                        entries
                                    </div>
                                    <div class="flex items-center">
                                        Search: 
                                        <input type="text" class="ml-2 border-gray-300 rounded text-xs py-1 px-2">
                                    </div>
                                </div>

                                <div class="overflow-x-auto border border-gray-100 rounded">
                                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                                        <thead class="bg-rose-50 text-gray-700 font-bold uppercase">
                                            <tr>
                                                <th class="px-4 py-3 text-left border-r border-gray-200">Classification <span class="ml-1 opacity-20 text-[10px]">⇅</span></th>
                                                <th class="px-4 py-3 text-left border-r border-gray-200">Probationary <span class="ml-1 opacity-20 text-[10px]">⇅</span></th>
                                                <th class="px-4 py-3 text-left border-r border-gray-200">REGULAR <span class="ml-1 opacity-20 text-[10px]">⇅</span></th>
                                                <th class="px-4 py-3 text-left">Contractual <span class="ml-1 opacity-20 text-[10px]">⇅</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-100 italic">
                                            @php 
                                                $classifications = ['EXECUTIVE', 'MANAGERIAL', 'RECRUITMENT', 'STAFF', 'SUPERVISORY'];
                                            @endphp
                                            @foreach($classifications as $classification)
                                            <tr class="hover:bg-gray-50 uppercase font-medium">
                                                <td class="px-4 py-3 border-r border-gray-100 font-medium not-italic">{{ $classification }}</td>
                                                <td class="px-4 py-3 border-r border-gray-100 text-gray-400">no setting</td>
                                                <td class="px-4 py-3 border-r border-gray-100 text-gray-400">no setting</td>
                                                <td class="px-4 py-3 text-gray-400">no setting</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4 flex justify-between items-center text-xs text-gray-500 px-4">
                                    <div>Showing 1 to 5 of 5 entries</div>
                                    <div class="flex space-x-0.5">
                                        <button class="px-3 py-1 border border-gray-200 rounded-l hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
                                        <button class="px-3 py-1 bg-blue-500 text-white rounded-none hover:bg-blue-600">1</button>
                                        <button class="px-3 py-1 border border-gray-200 rounded-r hover:bg-gray-50 disabled:opacity-50" disabled>Next</button>
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end px-4">
                                    <button class="bg-cyan-400 text-white px-6 py-2 rounded text-xs font-bold uppercase flex items-center shadow-sm hover:bg-cyan-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                        Update
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Absences Settings Content -->
                    <div x-show="activeTab === 'absences'" class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm" x-cloak>
                        <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center">
                            <h3 class="flex items-center text-sm font-black text-rose-800 uppercase tracking-tight">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                Absence Occurence Settings
                            </h3>
                        </div>
                        <div class="p-8 bg-white flex flex-col items-center space-y-6">
                            <div class="w-full max-w-lg flex items-center justify-center space-x-4">
                                <span class="text-xs font-bold text-gray-700 whitespace-nowrap">Select Site:</span>
                                <select class="w-full border-gray-300 rounded text-sm text-gray-600 py-1.5 focus:border-red-500 focus:ring-red-500">
                                    <option value="">Select Site</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-full max-w-lg text-center font-bold">
                                <select x-model="absenceOption" class="w-full border-gray-300 rounded text-sm text-gray-600 py-1.5 focus:border-red-500 focus:ring-red-500">
                                    <option value="occurences">Occurences</option>
                                    <option value="total">Total</option>
                                </select>
                            </div>

                            <div class="w-full mt-6">
                                <div class="text-center mb-6">
                                    <h4 class="text-rose-600 font-bold text-lg italic lowercase first-letter:uppercase" x-text="absenceOption === 'occurences' ? 'Occurences Late and Absence Settings' : 'Total Late and Absence Settings'"></h4>
                                </div>

                                <div class="flex justify-between items-center mb-4 text-xs text-gray-600 px-4">
                                    <div class="flex items-center">
                                        Show 
                                        <select class="mx-2 border-gray-300 rounded text-xs py-1">
                                            <option>10</option>
                                            <option>25</option>
                                            <option>50</option>
                                        </select>
                                        entries
                                    </div>
                                    <div class="flex items-center">
                                        Search: 
                                        <input type="text" class="ml-2 border-gray-300 rounded text-xs py-1 px-2">
                                    </div>
                                </div>

                                <div class="overflow-x-auto border border-gray-100 rounded">
                                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                                        <thead class="bg-rose-50 text-gray-700 font-bold uppercase">
                                            <tr>
                                                <th class="px-4 py-3 text-left border-r border-gray-200">Classification <span class="ml-1 opacity-20 text-[10px]">⇅</span></th>
                                                <th class="px-4 py-3 text-left border-r border-gray-200">Probationary <span class="ml-1 opacity-20 text-[10px]">⇅</span></th>
                                                <th class="px-4 py-3 text-left border-r border-gray-200">REGULAR <span class="ml-1 opacity-20 text-[10px]">⇅</span></th>
                                                <th class="px-4 py-3 text-left">Contractual <span class="ml-1 opacity-20 text-[10px]">⇅</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-100 italic">
                                            @foreach($classifications as $classification)
                                            <tr class="hover:bg-gray-50 uppercase font-medium">
                                                <td class="px-4 py-3 border-r border-gray-100 font-medium not-italic">{{ $classification }}</td>
                                                <td class="px-4 py-3 border-r border-gray-100 text-gray-400">no setting</td>
                                                <td class="px-4 py-3 border-r border-gray-100 text-gray-400">no setting</td>
                                                <td class="px-4 py-3 text-gray-400">no setting</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4 flex justify-between items-center text-xs text-gray-500 px-4">
                                    <div>Showing 1 to 5 of 5 entries</div>
                                    <div class="flex space-x-0.5">
                                        <button class="px-3 py-1 border border-gray-200 rounded-l hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
                                        <button class="px-3 py-1 bg-blue-500 text-white rounded-none hover:bg-blue-600">1</button>
                                        <button class="px-3 py-1 border border-gray-200 rounded-r hover:bg-gray-50 disabled:opacity-50" disabled>Next</button>
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end px-4">
                                    <button class="bg-cyan-400 text-white px-6 py-2 rounded text-xs font-bold uppercase flex items-center shadow-sm hover:bg-cyan-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                        Update
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basis Settings Content -->
                    <div x-show="activeTab === 'basis'" class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm" x-cloak>
                        <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center">
                            <h3 class="flex items-center text-sm font-black text-rose-800 uppercase tracking-tight">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                Late and Absences Basis Settings
                            </h3>
                        </div>
                        <div class="p-8 bg-white flex flex-col items-center space-y-8">
                            <div class="w-full max-w-lg flex items-center justify-center space-x-4">
                                <span class="text-xs font-bold text-gray-700 whitespace-nowrap">Select Site:</span>
                                <select class="w-full border-gray-300 rounded text-sm text-gray-600 py-1.5 focus:border-red-500 focus:ring-red-500">
                                    <option value="">Select Site</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-full max-w-5xl mt-8">
                                <div class="flex justify-between items-center mb-4 text-xs text-gray-600">
                                    <div class="flex items-center">
                                        Show 
                                        <select class="mx-2 border-gray-300 rounded text-xs py-1">
                                            <option>10</option>
                                            <option>25</option>
                                            <option>50</option>
                                        </select>
                                        entries
                                    </div>
                                    <div class="flex items-center">
                                        Search: 
                                        <input type="text" class="ml-2 border-gray-300 rounded text-xs py-1 px-2 border border-gray-300 shadow-sm focus:ring-1 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div class="overflow-x-auto border border-gray-100 rounded">
                                    <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                                        <thead class="bg-rose-50 text-gray-700 font-bold uppercase">
                                            <tr>
                                                <th class="px-4 py-3 border-r border-gray-200">Type <span class="ml-1 opacity-20 text-[10px]">⇅</span></th>
                                                <th class="px-4 py-3 border-r border-gray-200">Late <span class="ml-1 opacity-20 text-[10px]">⇅</span></th>
                                                <th class="px-4 py-3">Absence <span class="ml-1 opacity-20 text-[10px]">⇅</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-100">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 border-r border-gray-100 text-gray-700">Occurances</td>
                                                <td class="px-4 py-3 border-r border-gray-100">
                                                    <select class="w-1/2 border-gray-300 rounded text-xs py-1 focus:border-red-500 focus:ring-red-500">
                                                        <option>Per Month</option>
                                                        <option>Per Payroll Period</option>
                                                    </select>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <select class="w-1/2 border-gray-300 rounded text-xs py-1 focus:border-red-500 focus:ring-red-500">
                                                        <option>Per Payroll Period</option>
                                                        <option>Per Month</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr class="hover:bg-gray-50 border-t border-gray-100">
                                                <td class="px-4 py-3 border-r border-gray-100 text-gray-700">Total</td>
                                                <td class="px-4 py-3 border-r border-gray-100">
                                                    <select class="w-1/2 border-gray-300 rounded text-xs py-1 focus:border-red-500 focus:ring-red-500">
                                                        <option>Per Month</option>
                                                        <option>Per Payroll Period</option>
                                                    </select>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <select class="w-1/2 border-gray-300 rounded text-xs py-1 focus:border-red-500 focus:ring-red-500">
                                                        <option>Per Payroll Period</option>
                                                        <option>Per Month</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4 flex justify-between items-center text-xs text-gray-500">
                                    <div>Showing 1 to 2 of 2 entries</div>
                                    <div class="flex space-x-0.5">
                                        <button class="px-3 py-1 border border-gray-200 rounded hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
                                        <button class="px-3 py-1 bg-blue-500 text-white rounded-sm">1</button>
                                        <button class="px-3 py-1 border border-gray-200 rounded hover:bg-gray-50 disabled:opacity-50" disabled>Next</button>
                                    </div>
                                </div>

                                <div class="mt-8 flex justify-end">
                                    <button class="bg-emerald-600 text-white px-6 py-2 rounded text-xs font-bold uppercase hover:bg-emerald-700 transition-colors shadow-sm">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
