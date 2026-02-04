<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Automation Dashboard') }}
        </h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">HR Automation Dashboard</h1>
        <p class="text-gray-600 mt-2">Monitor and manage all automated HR processes</p>
    </div>

    <!-- Health Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Queue Status -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Queue Status</p>
                    <p class="text-2xl font-bold {{ $healthCheck['queue_status']['status'] === 'healthy' ? 'text-green-600' : ($healthCheck['queue_status']['status'] === 'warning' ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ ucfirst($healthCheck['queue_status']['status']) }}
                    </p>
                </div>
                <div class="p-3 rounded-full {{ $healthCheck['queue_status']['status'] === 'healthy' ? 'bg-green-100' : ($healthCheck['queue_status']['status'] === 'warning' ? 'bg-yellow-100' : 'bg-red-100') }}">
                    <svg class="w-6 h-6 {{ $healthCheck['queue_status']['status'] === 'healthy' ? 'text-green-600' : ($healthCheck['queue_status']['status'] === 'warning' ? 'text-yellow-600' : 'text-red-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ $healthCheck['queue_status']['message'] }}</p>
            @if(isset($healthCheck['queue_status']['pending']))
                <p class="text-xs text-gray-500">Pending: {{ $healthCheck['queue_status']['pending'] }} | Failed: {{ $healthCheck['queue_status']['failed'] ?? 0 }}</p>
            @endif
        </div>

        <!-- DTR Generation -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Last DTR Generated</p>
                    @if($healthCheck['last_dtr_generation'])
                        <p class="text-lg font-bold text-gray-900">{{ $healthCheck['last_dtr_generation']['for_date'] }}</p>
                    @else
                        <p class="text-lg font-bold text-gray-400">No DTRs yet</p>
                    @endif
                </div>
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            @if($healthCheck['last_dtr_generation'])
                <p class="text-xs text-gray-500 mt-2">For: {{ $healthCheck['last_dtr_generation']['user'] }}</p>
            @endif
        </div>

        <!-- Last Payroll Computation -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Last Payroll Computed</p>
                    @if($healthCheck['last_payroll_computation'])
                        <p class="text-lg font-bold text-green-600">₱{{ $healthCheck['last_payroll_computation']['amount'] }}</p>
                    @else
                        <p class="text-lg font-bold text-gray-400">No payrolls yet</p>
                    @endif
                </div>
                <div class="p-3 rounded-full bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            @if($healthCheck['last_payroll_computation'])
                <p class="text-xs text-gray-500 mt-2">{{ $healthCheck['last_payroll_computation']['date'] }}</p>
            @endif
        </div>

        <!-- Current Period -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Current Period</p>
                    @if($currentPeriod)
                        <p class="text-lg font-bold text-indigo-600">{{ $currentPeriod->name ?? 'Active' }}</p>
                    @else
                        <p class="text-lg font-bold text-gray-400">No active period</p>
                    @endif
                </div>
                <div class="p-3 rounded-full bg-indigo-100">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            @if($currentPeriod)
                <p class="text-xs text-gray-500 mt-2">{{ $currentPeriod->start_date->format('M d') }} - {{ $currentPeriod->end_date->format('M d, Y') }}</p>
            @endif
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- DTR Statistics -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                DTR Automation
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Pending Approval</span>
                    <span class="text-2xl font-bold text-yellow-600">{{ $dtrStats['pending_approval'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Approved Today</span>
                    <span class="text-2xl font-bold text-green-600">{{ $dtrStats['approved_today'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Corrections Pending</span>
                    <span class="text-2xl font-bold text-orange-600">{{ $dtrStats['corrections_pending'] }}</span>
                </div>
                <div class="flex justify-between items-center border-t pt-4">
                    <span class="text-gray-600">Total Generated</span>
                    <span class="text-xl font-semibold text-gray-900">{{ number_format($dtrStats['total_generated']) }}</span>
                </div>
            </div>
            <div class="mt-6">
                <a href="{{ route('dtr-approval.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    Go to DTR Approval
                </a>
            </div>
        </div>

        <!-- Leave Statistics -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Leave Automation
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Pending Approval</span>
                    <span class="text-2xl font-bold text-yellow-600">{{ $leaveStats['pending_approval'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Approved This Month</span>
                    <span class="text-2xl font-bold text-green-600">{{ $leaveStats['approved_this_month'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">With DTR Entries</span>
                    <span class="text-2xl font-bold text-blue-600">{{ $leaveStats['with_dtr_entries'] }}</span>
                </div>
                <div class="flex justify-between items-center border-t pt-4">
                    <span class="text-gray-600">Cancelled This Month</span>
                    <span class="text-xl font-semibold text-red-600">{{ $leaveStats['cancelled_this_month'] }}</span>
                </div>
            </div>
            <div class="mt-6">
                <a href="{{ route('leaves.manage') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    Manage Leaves
                </a>
            </div>
        </div>

        <!-- Payroll Statistics -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Payroll Automation
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Pending Computation</span>
                    <span class="text-2xl font-bold text-gray-600">{{ $payrollStats['pending_computation'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Awaiting Approval</span>
                    <span class="text-2xl font-bold text-yellow-600">{{ $payrollStats['computed_pending_approval'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Ready to Release</span>
                    <span class="text-2xl font-bold text-blue-600">{{ $payrollStats['approved_pending_release'] }}</span>
                </div>
                <div class="flex justify-between items-center border-t pt-4">
                    <span class="text-gray-600">Released This Month</span>
                    <span class="text-xl font-semibold text-green-600">{{ $payrollStats['released_this_month'] }}</span>
                </div>
            </div>
            <div class="mt-6">
                <a href="{{ route('payroll.computation.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    Payroll Computation
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Automation Activity</h3>
            @if(count($recentActivity) > 0)
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @foreach($recentActivity as $activity)
                        <div class="flex items-start gap-4 p-3 rounded-lg hover:bg-gray-50 transition">
                            <div class="p-2 rounded-full bg-{{ $activity['color'] }}-100 flex-shrink-0">
                                @if($activity['icon'] === 'check-circle')
                                    <svg class="w-5 h-5 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @elseif($activity['icon'] === 'calendar')
                                    <svg class="w-5 h-5 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                @elseif($activity['icon'] === 'currency-dollar')
                                    <svg class="w-5 h-5 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $activity['message'] }}</p>
                                <p class="text-xs text-gray-500">{{ $activity['timestamp']->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>No recent automation activity</p>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('dtr-approval.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition">
                    <div class="p-2 rounded-lg bg-blue-100">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Review DTRs</p>
                        <p class="text-xs text-gray-500">Approve pending DTR records</p>
                    </div>
                </a>

                <a href="{{ route('leaves.manage') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-green-500 hover:bg-green-50 transition">
                    <div class="p-2 rounded-lg bg-green-100">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Process Leaves</p>
                        <p class="text-xs text-gray-500">Approve leave requests</p>
                    </div>
                </a>

                <a href="{{ route('payroll.computation.dashboard') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-purple-500 hover:bg-purple-50 transition">
                    <div class="p-2 rounded-lg bg-purple-100">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Compute Payroll</p>
                        <p class="text-xs text-gray-500">Run payroll computation</p>
                    </div>
                </a>

                <a href="{{ route('payroll.periods') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition">
                    <div class="p-2 rounded-lg bg-indigo-100">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Manage Periods</p>
                        <p class="text-xs text-gray-500">Create payroll periods</p>
                    </div>
                </a>

                <a href="{{ route('leave-credits.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-teal-500 hover:bg-teal-50 transition">
                    <div class="p-2 rounded-lg bg-teal-100">
                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Leave Credits</p>
                        <p class="text-xs text-gray-500">Manage employee leave balances</p>
                    </div>
                </a>
            </div>

            <!-- Automation Flow Info -->
            <div class="mt-6 pt-6 border-t">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Automation Flow</h4>
                <div class="text-xs text-gray-600 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                        <span>Attendance → DTR Generated</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold">2</span>
                        <span>DTR Approved → Payroll Ready</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-bold">3</span>
                        <span>Payroll Computed → Payslip Generated</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-bold">4</span>
                        <span>Leave Approved → DTR Auto-Created</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payroll Periods -->
    @if($recentPeriods->count() > 0)
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Payroll Periods</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Range</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($recentPeriods as $period)
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $period->name ?? 'Period #'.$period->id }}</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-600">{{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $period->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($period->status === 'processing' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($period->status ?? 'pending') }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('payroll.computation.show', $period) }}" class="text-indigo-600 hover:text-indigo-900">View Details</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
</x-app-layout>
