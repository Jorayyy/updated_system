<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-red-800 leading-tight uppercase tracking-tight">
                {{ App\Models\CompanySetting::getValue('company_name', 'Mancao Electronic Connect Business Solutions OPC') }} SITE ADMINS <span class="ml-2">📅</span>
            </h2>
            <div class="flex items-center space-x-2">
                {{-- Shift Table Button --}}
                <a href="{{ route('shifts.index') }}" class="flex items-center space-x-2 bg-red-600 text-white px-3 py-1.5 rounded text-[10px] font-black uppercase tracking-widest hover:bg-red-700 transition-colors shadow-sm ring-1 ring-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Shift Table</span>
                </a>

                {{-- Group Plotting Button --}}
                <a href="{{ route('schedules.group-create') }}" class="flex items-center space-x-2 bg-red-600 text-white px-3 py-1.5 rounded text-[10px] font-black uppercase tracking-widest hover:bg-red-700 transition-colors shadow-sm ring-1 ring-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Group Plotting</span>
                </a>

                <div class="flex items-center space-x-2 border-l pl-2 border-gray-200 ml-2">
                    <a href="{{ route('employees.create', ['from' => 'schedules']) }}" class="text-green-600 hover:text-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-4 bg-gray-50 min-h-screen">
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
        </div>
    </div>
</x-app-layout>
