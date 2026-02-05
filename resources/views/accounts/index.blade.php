<x-app-layout>
    <div class="p-6 bg-[#e0f2fe] min-h-screen">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-700 mb-6 flex items-center gap-2">
                Administrator <span class="text-sm font-normal text-gray-400">User Roles</span>
            </h1>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-red-50 px-6 py-3 flex justify-between items-center border-b border-gray-200">
                    <h2 class="text-red-800 font-bold uppercase tracking-wider text-sm">User Roles</h2>
                    <a href="{{ route('accounts.create') }}" class="bg-red-600 text-white p-2 rounded-lg hover:bg-red-700 transition shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                        </svg>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Role Name</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Permissions Category</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Role Description</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider text-center">Level</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Site</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider text-center">Remarks</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($accounts as $account)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('accounts.edit', $account) }}" class="text-blue-400 font-medium hover:text-blue-600">
                                            {{ $account->name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 {{ 
                                            match($account->system_role) {
                                                'super_admin' => 'bg-purple-100 text-purple-700',
                                                'admin' => 'bg-red-100 text-red-700',
                                                'hr' => 'bg-green-100 text-green-700',
                                                'accounting' => 'bg-yellow-100 text-yellow-700',
                                                default => 'bg-gray-100 text-gray-700'
                                            }
                                        }} rounded-full text-[10px] font-bold uppercase tracking-wider">
                                            {{ $account->system_role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $account->description ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-center">
                                        <span class="px-2 py-1 bg-blue-50 text-blue-700 font-bold rounded text-xs">
                                            {{ $account->hierarchy_level }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                                            {{ $account->site->name ?? 'Global' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800 text-center font-bold">
                                        {{ $account->users_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-y-2">
                                        <div class="flex flex-col items-end gap-2">
                                            <!-- Delete Action -->
                                            <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline" id="delete-form-{{ $account->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="admin_password" id="admin_password_{{ $account->id }}">
                                                <button type="button" 
                                                    onclick="const password = prompt('Critical Action: This will delete the account. Enter ADMIN PASSWORD to confirm:'); if(password) { document.getElementById('admin_password_{{ $account->id }}').value = password; document.getElementById('delete-form-{{ $account->id }}').submit(); }"
                                                    class="p-1 px-2 bg-pink-100 text-pink-600 rounded shadow-sm hover:bg-pink-200 transition">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                            
                                            <!-- Edit Action -->
                                            <a href="{{ route('accounts.edit', $account) }}" class="p-1 px-2 bg-yellow-100 text-yellow-600 rounded shadow-sm hover:bg-yellow-200 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                                        No roles found.
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
