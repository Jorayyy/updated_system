<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('IP Configuration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Add IP Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Allowed IP</h3>
                            
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm text-blue-700 dark:text-blue-300">Your Current IP:</span>
                                </div>
                                <p class="text-lg font-mono font-semibold text-blue-800 dark:text-blue-200 mt-1">{{ $currentIp }}</p>
                                @if(!$isCurrentIpAllowed)
                                    <form action="{{ route('settings.allowed-ips.add-current') }}" method="POST" class="mt-2">
                                        @csrf
                                        <button type="submit" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                            + Add this IP
                                        </button>
                                    </form>
                                @else
                                    <span class="text-sm text-green-600 dark:text-green-400">✓ Already allowed</span>
                                @endif
                            </div>
                            
                            <form action="{{ route('settings.allowed-ips.store') }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IP Address *</label>
                                        <input type="text" name="ip_address" required 
                                               placeholder="e.g., 192.168.1.100"
                                               pattern="^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$|^([a-fA-F0-9:]+)$"
                                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm">
                                        @error('ip_address')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label *</label>
                                        <input type="text" name="label" required 
                                               placeholder="e.g., Main Office Router"
                                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
                                        <input type="text" name="location" 
                                               placeholder="e.g., Building A, Floor 2"
                                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                        <textarea name="description" rows="2" 
                                                  placeholder="Optional description..."
                                                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm"></textarea>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="is_active" id="is_active" value="1" checked
                                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm">
                                        <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
                                    </div>
                                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition text-sm">
                                        Add IP Address
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="mt-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-yellow-700 dark:text-yellow-300">
                                <p class="font-medium">Important</p>
                                <p class="mt-1">Only attendance records from registered and active IP addresses will be accepted. Make sure to add all office IPs before enabling this restriction.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- IP List -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Allowed IP Addresses</h3>
                                <span class="px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    {{ $allowedIps->total() }} Total
                                </span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">IP Address</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Label / Location</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @forelse($allowedIps as $ip)
                                            <tr class="{{ !$ip->is_active ? 'opacity-50' : '' }}">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="font-mono text-sm text-gray-900 dark:text-white">{{ $ip->ip_address }}</span>
                                                    @if($ip->ip_address == $currentIp)
                                                        <span class="ml-2 px-2 py-0.5 text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 rounded">
                                                            Current
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $ip->label }}</div>
                                                    @if($ip->location)
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $ip->location }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                                    <form action="{{ route('settings.allowed-ips.toggle', $ip) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="px-3 py-1 text-xs rounded-full transition {{ $ip->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 hover:bg-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                                                            {{ $ip->is_active ? 'Active' : 'Inactive' }}
                                                        </button>
                                                    </form>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                                    <button type="button" onclick="openEditModal({{ json_encode($ip) }})" 
                                                            class="text-blue-600 hover:text-blue-800 text-sm mr-2">
                                                        Edit
                                                    </button>
                                                    <form action="{{ route('settings.allowed-ips.destroy', $ip) }}" method="POST" class="inline" 
                                                          onsubmit="return confirm('Are you sure you want to delete this IP address?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                    No IP addresses configured. Add your first IP to enable IP-based attendance restriction.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($allowedIps->hasPages())
                                <div class="mt-4">
                                    {{ $allowedIps->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Edit IP Address</h3>
            <form id="edit-form" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IP Address</label>
                        <input type="text" name="ip_address" id="edit-ip" required
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label</label>
                        <input type="text" name="label" id="edit-label" required
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
                        <input type="text" name="location" id="edit-location"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea name="description" id="edit-description" rows="2"
                                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeEditModal()" 
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openEditModal(ip) {
            document.getElementById('edit-form').action = `/settings/allowed-ips/${ip.id}`;
            document.getElementById('edit-ip').value = ip.ip_address;
            document.getElementById('edit-label').value = ip.label;
            document.getElementById('edit-location').value = ip.location || '';
            document.getElementById('edit-description').value = ip.description || '';
            document.getElementById('edit-modal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }

        document.getElementById('edit-modal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    </script>
    @endpush
</x-app-layout>
