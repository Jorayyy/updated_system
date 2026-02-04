<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Settings') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Company Settings Card -->
                <a href="{{ route('settings.company') }}" class="block">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition border border-transparent hover:border-indigo-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Company Information</h3>
                                    <p class="text-sm text-gray-500">Company details, contact & registration</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Call Center Settings Card -->
                <a href="{{ route('settings.call-center') }}" class="block">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition border border-transparent hover:border-purple-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Call Center Operations</h3>
                                    <p class="text-sm text-gray-500">Shifts, schedules & agent settings</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Payroll Settings Card -->
                <a href="{{ route('settings.payroll') }}" class="block">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition border border-transparent hover:border-green-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Payroll Settings</h3>
                                    <p class="text-sm text-gray-500">Rates, deductions & tax settings</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Attendance Settings Card -->
                <a href="{{ route('settings.attendance') }}" class="block">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition border border-transparent hover:border-yellow-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Attendance Settings</h3>
                                    <p class="text-sm text-gray-500">Work hours, breaks & time tracking</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Leave Settings Card -->
                <a href="{{ route('settings.leave') }}" class="block">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition border border-transparent hover:border-blue-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Leave Management</h3>
                                    <p class="text-sm text-gray-500">Credits, policies & approvals</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Notification Settings Card -->
                <a href="{{ route('settings.notifications') }}" class="block">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition border border-transparent hover:border-red-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
                                    <p class="text-sm text-gray-500">Alerts, emails & reminders</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- System Settings Card -->
                <a href="{{ route('settings.system') }}" class="block">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition border border-transparent hover:border-gray-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-gray-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">System Settings</h3>
                                    <p class="text-sm text-gray-500">Timezone, formats & maintenance</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- IP Configuration Card -->
                <a href="{{ route('settings.allowed-ips') }}" class="block">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition border border-transparent hover:border-teal-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-teal-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">IP Configuration</h3>
                                    <p class="text-sm text-gray-500">Allowed IPs for attendance</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
