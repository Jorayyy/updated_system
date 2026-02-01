<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('My Concerns & Tickets') }}
            </h2>
            <a href="{{ route('concerns.user-create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Submit New Concern
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Submitted</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['open'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Open</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['in_progress'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">In Progress</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['resolved'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Resolved</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Filters -->
                    <form method="GET" class="flex flex-wrap items-center gap-3 mb-4">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search ticket..."
                               class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        <select name="status" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">All Status</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        <button type="submit" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded text-sm hover:bg-gray-300 dark:hover:bg-gray-600">
                            Filter
                        </button>
                        <a href="{{ route('concerns.my') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 text-sm">Clear</a>
                    </form>

                    <!-- Concerns List -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ticket</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Title</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Category</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Priority</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Assigned To</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Submitted</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($concerns as $concern)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" onclick="window.location='{{ route('concerns.user-show', $concern) }}'">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="font-mono text-sm text-blue-600 dark:text-blue-400">{{ $concern->ticket_number }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($concern->title, 50) }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ \App\Models\Concern::CATEGORIES[$concern->category] ?? $concern->category }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @php
                                                $priorityColors = [
                                                    'low' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                    'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                                    'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                                                    'critical' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 text-xs rounded-full {{ $priorityColors[$concern->priority] ?? $priorityColors['medium'] }}">
                                                {{ \App\Models\Concern::PRIORITIES[$concern->priority] ?? $concern->priority }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @php
                                                $statusColors = [
                                                    'open' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                                    'in_progress' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                                    'on_hold' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                                                    'resolved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                    'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$concern->status] ?? $statusColors['open'] }}">
                                                {{ \App\Models\Concern::STATUSES[$concern->status] ?? $concern->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $concern->assignee->name ?? 'Unassigned' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $concern->created_at->format('M d, Y') }}
                                            <div class="text-xs">{{ $concern->created_at->format('h:i A') }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <p>You haven't submitted any concerns yet.</p>
                                                <a href="{{ route('concerns.user-create') }}" class="mt-2 text-blue-600 hover:underline">Submit your first concern</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($concerns->hasPages())
                        <div class="mt-4">
                            {{ $concerns->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
