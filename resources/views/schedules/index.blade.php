<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-red-800 leading-tight uppercase tracking-tight">
                Mancao Electronic Connect Business Solutions OPC MAASIN ADMINS <span class="ml-2">📅</span>
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
                    <a href="{{ route('employees.create') }}" class="text-green-600 hover:text-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a href="{{ url()->previous() }}" class="text-red-600 hover:text-red-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-4 bg-blue-50/30 min-h-screen">
        <div class="max-w-[95%] mx-auto">
            <div class="bg-white shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                    <div class="flex items-center text-sm text-gray-600">
                        Show 
                        <select class="mx-2 border-gray-300 rounded text-sm py-1">
                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                        </select>
                        entries
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        Search: 
                        <form action="{{ route('schedules.index') }}" method="GET" class="ml-2">
                            <input type="text" name="search" value="{{ request('search') }}" class="border-gray-300 rounded text-sm py-1 px-2">
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs font-bold uppercase tracking-wider">
                                <th class="px-6 py-3 text-left border-r border-gray-200">Employee id <span class="ml-1 text-[10px] opacity-30">⇅</span></th>
                                <th class="px-6 py-3 text-left border-r border-gray-200">Employee name <span class="ml-1 text-[10px] opacity-30">⇅</span></th>
                                <th class="px-6 py-3 text-left border-r border-gray-200">Classification <span class="ml-1 text-[10px] opacity-30">⇅</span></th>
                                <th class="px-6 py-3 text-left border-r border-gray-200">Status <span class="ml-1 text-[10px] opacity-30">⇅</span></th>
                                <th class="px-6 py-3 text-left">Options</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors text-xs font-medium text-gray-700">
                                    <td class="px-6 py-3 border-r border-gray-100">{{ $user->employee_id }}</td>
                                    <td class="px-6 py-3 border-r border-gray-100 uppercase">{{ $user->name }}</td>
                                    <td class="px-6 py-3 border-r border-gray-100 uppercase">{{ $user->classification ?? 'STAFF' }}</td>
                                    <td class="px-6 py-3 border-r border-gray-100">{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                                    <td class="px-6 py-3 flex items-center space-x-2">
                                        {{-- Deactivate --}}
                                        <button class="text-red-600 hover:text-red-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path><line x1="12" y1="2" x2="12" y2="12"></line></svg>
                                        </button>
                                        {{-- Delete --}}
                                        <button class="text-purple-600 hover:text-purple-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        {{-- Edit (Yellow pencil icon) --}}
                                        <a href="{{ route('schedules.edit', $user->id) }}" class="text-yellow-400 hover:text-yellow-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        {{-- Calendar Icon (Light blue) --}}
                                        <a href="{{ route('dtr.show', $user->id) }}" class="text-cyan-400 hover:text-cyan-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 flex justify-between items-center text-xs text-gray-500 bg-white">
                    <div>Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries</div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
