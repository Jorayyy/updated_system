<x-app-layout>
    <div class="p-6 bg-[#f8fafc] min-h-screen">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-700 flex items-center gap-2">
                    Employment <span class="text-sm font-normal text-gray-400">Job Roles & Designations</span>
                </h1>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-indigo-50 px-6 py-3 flex justify-between items-center border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <h2 class="text-indigo-800 font-bold uppercase tracking-wider text-sm">Defined Designations</h2>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('accounts.create') }}" class="bg-indigo-600 text-white p-2 rounded-lg hover:bg-indigo-700 transition shadow-lg flex items-center gap-2 text-sm px-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Designation
                        </a>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Role Name</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">System Type</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Hierarchy Level</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider text-center">Assigned Users</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($accounts as $account)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">{{ $account->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $account->description ?? 'No description' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 {{ 
                                            match($account->system_role) {
                                                'super_admin' => 'bg-purple-100 text-purple-700 border border-purple-200',
                                                'admin' => 'bg-red-100 text-red-700 border border-red-200',
                                                'hr' => 'bg-green-100 text-green-700 border border-green-200',
                                                'accounting' => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
                                                default => 'bg-gray-100 text-gray-700 border border-gray-200'
                                            }
                                        }} rounded-full text-[10px] font-bold uppercase tracking-wider">
                                            {{ $account->system_role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-center">
                                        <span class="px-3 py-1 bg-indigo-50 text-indigo-700 font-bold rounded-full text-xs border border-indigo-100 italic">
                                            Lvl {{ $account->hierarchy_level }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800 text-center font-bold">
                                        {{ $account->users_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-3">
                                            @if(auth()->user()->isSuperAdmin())
                                                <a href="{{ route('accounts.edit', $account) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit Permissions">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </a>
                                                <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline" onsubmit="return confirm('Deleting a role may affect user access. Continue?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 italic text-xs">Read Only</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                                        No permission roles defined.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($accounts->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        {{ $accounts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
