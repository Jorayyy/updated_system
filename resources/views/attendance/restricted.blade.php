<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance Restricted') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-500">
                <div class="p-8 text-center">
                    <div class="mb-6 flex justify-center">
                        <div class="bg-yellow-100 p-4 rounded-full">
                            <svg class="h-12 w-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 mb-2">System Account Restricted</h1>
                    <p class="text-gray-600 max-w-lg mx-auto mb-8">
                        You are currently logged in with a <strong>System/Administrator account ({{ ucfirst($user->role) }})</strong>.
                        Attendance tracking and DTR generation are only available for <strong>Employee</strong> accounts.
                    </p>

                    <div class="bg-blue-50 p-6 rounded-lg text-left max-w-2xl mx-auto border border-blue-100 mb-8">
                        <h3 class="font-bold text-blue-800 mb-2 flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Wait, I need to clock in!
                        </h3>
                        <p class="text-blue-700 text-sm">
                            If you need to record your attendance for payroll purposes, please use your dedicated <strong>Employee</strong> account.
                            System roles are strictly for administration and are not processed for payroll or DTR.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition shadow-sm">
                            Back to Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition shadow-md">
                                Logout & Switch Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
