<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('audit-logs.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Audit Log Details') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Timestamp</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->created_at->format('M d, Y H:i:s') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">User</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $auditLog->user?->name ?? 'System' }}
                                @if($auditLog->user)
                                    <span class="text-gray-500">({{ $auditLog->user->email }})</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Action</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->action }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Model</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ class_basename($auditLog->model_type) }}
                                @if($auditLog->model_id)
                                    <span class="text-gray-500">(ID: {{ $auditLog->model_id }})</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->ip_address ?? '-' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                            <dd class="mt-1 text-sm text-gray-900 break-all">{{ $auditLog->user_agent ?? '-' }}</dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->description ?? '-' }}</dd>
                        </div>

                        @if($auditLog->old_values)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Old Values</dt>
                                <dd class="mt-1 text-sm text-gray-900 bg-red-50 p-4 rounded overflow-x-auto">
                                    <pre class="text-xs">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                                </dd>
                            </div>
                        @endif

                        @if($auditLog->new_values)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">New Values</dt>
                                <dd class="mt-1 text-sm text-gray-900 bg-green-50 p-4 rounded overflow-x-auto">
                                    <pre class="text-xs">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
