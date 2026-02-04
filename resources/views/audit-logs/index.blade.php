<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Logs') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <x-input-label for="action" :value="__('Action')" />
                            <select id="action" name="action" class="mt-1 block border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Actions</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="model_type" :value="__('Model')" />
                            <select id="model_type" name="model_type" class="mt-1 block border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Models</option>
                                @foreach($modelTypes as $model)
                                    <option value="{{ $model['value'] }}" {{ request('model_type') == $model['value'] ? 'selected' : '' }}>{{ $model['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="start_date" :value="__('From Date')" />
                            <x-text-input type="date" id="start_date" name="start_date" :value="request('start_date')" class="mt-1 block" />
                        </div>
                        <div>
                            <x-input-label for="end_date" :value="__('To Date')" />
                            <x-text-input type="date" id="end_date" name="end_date" :value="request('end_date')" class="mt-1 block" />
                        </div>
                        <div>
                            <x-primary-button>Filter</x-primary-button>
                        </div>
                        @if(request()->hasAny(['action', 'model_type', 'start_date', 'end_date']))
                            <div>
                                <a href="{{ route('audit-logs.index') }}" class="text-gray-500 hover:text-gray-700">Clear Filters</a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($logs as $log)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->created_at->format('M d, Y H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $log->user?->name ?? 'System' }}</div>
                                        <div class="text-sm text-gray-500">{{ $log->user?->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $actionColors = [
                                                'create' => 'bg-green-100 text-green-800',
                                                'update' => 'bg-blue-100 text-blue-800',
                                                'delete' => 'bg-red-100 text-red-800',
                                                'login' => 'bg-purple-100 text-purple-800',
                                                'logout' => 'bg-gray-100 text-gray-800',
                                            ];
                                            $color = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                            {{ $log->action }}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">{{ class_basename($log->model_type) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate">{{ $log->description ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->ip_address }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('audit-logs.show', $log) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No audit logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
