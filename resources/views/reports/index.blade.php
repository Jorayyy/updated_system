<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    Reports Central
                </h2>
                <p class="text-sm text-gray-500 mt-1">Generate and export comprehensive organizational data</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Attendance Report -->
                <a href="{{ route('reports.attendance') }}" class="group block h-full">
                    <div class="h-full bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1 group-hover:border-blue-200 relative overflow-hidden">
                        <div class="mx-auto w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors">Attendance</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed">Detailed logs of punch-ins, lates, and total hours worked.</p>
                        <div class="mt-6 flex items-center justify-center text-blue-600 font-semibold text-sm opacity-0 group-hover:opacity-100 transition-opacity">
                            Generate Report <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>

                <!-- Leave Report -->
                <a href="{{ route('reports.leaves') }}" class="group block h-full">
                    <div class="h-full bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1 group-hover:border-emerald-200 relative overflow-hidden">
                        <div class="mx-auto w-16 h-16 bg-emerald-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">Leaves</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed">Summary of leave requests, types, and approval statuses.</p>
                        <div class="mt-6 flex items-center justify-center text-emerald-600 font-semibold text-sm opacity-0 group-hover:opacity-100 transition-opacity">
                            Generate Report <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>

                <!-- Payroll Report -->
                <a href="{{ route('reports.payroll') }}" class="group block h-full">
                    <div class="h-full bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1 group-hover:border-amber-200 relative overflow-hidden">
                        <div class="mx-auto w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-amber-600 transition-colors">Payroll</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed">Financial breakdowns of earnings, taxes, and net pay.</p>
                        <div class="mt-6 flex items-center justify-center text-amber-600 font-semibold text-sm opacity-0 group-hover:opacity-100 transition-opacity">
                            Generate Report <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>

                <!-- Employee Report -->
                <a href="{{ route('reports.employees') }}" class="group block h-full">
                    <div class="h-full bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1 group-hover:border-purple-200 relative overflow-hidden">
                        <div class="mx-auto w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Employees</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed">Full staff roster including sites, accounts, and contact info.</p>
                        <div class="mt-6 flex items-center justify-center text-purple-600 font-semibold text-sm opacity-0 group-hover:opacity-100 transition-opacity">
                            Generate Report <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
