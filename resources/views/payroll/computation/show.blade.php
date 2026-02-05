<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Computed Payrolls') }}
            </h2>
            <a href="javascript:void(0)" onclick="window.history.back()" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Period Info --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Period</p>
                                <p class="text-lg font-semibold">
                                    {{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <span class="px-2 py-1 text-sm font-semibold rounded-full 
                                    @if($period->status === 'completed') bg-green-100 text-green-800
                                    @elseif($period->status === 'processing') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($period->status) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Gross</p>
                                <p class="text-lg font-semibold text-green-600">₱{{ number_format($summary['total_gross'] ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Deductions</p>
                                <p class="text-lg font-semibold text-red-600">₱{{ number_format($summary['total_deductions'] ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Net Pay</p>
                                <p class="text-lg font-semibold text-blue-600">₱{{ number_format($summary['total_net'] ?? 0, 2) }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('payroll.computation.export', ['period' => $period, 'format' => 'csv']) }}" 
                               class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                                Export CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Counts --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 border-l-4 border-gray-400">
                    <p class="text-sm text-gray-500">Computed</p>
                    <p class="text-2xl font-semibold">{{ $statusCounts['computed'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 border-l-4 border-yellow-400">
                    <p class="text-sm text-gray-500">Approved</p>
                    <p class="text-2xl font-semibold">{{ $statusCounts['approved'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 border-l-4 border-green-400">
                    <p class="text-sm text-gray-500">Released</p>
                    <p class="text-2xl font-semibold">{{ $statusCounts['released'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 border-l-4 border-red-400">
                    <p class="text-sm text-gray-500">Rejected</p>
                    <p class="text-2xl font-semibold">{{ $statusCounts['rejected'] ?? 0 }}</p>
                </div>
            </div>

            {{-- Bulk Actions --}}
            @php
                $unpostedCount = \App\Models\Payroll::where('payroll_period_id', $period->id)
                    ->whereIn('status', ['approved', 'completed', 'released'])
                    ->where('is_posted', false)
                    ->count();
                $postedCount = \App\Models\Payroll::where('payroll_period_id', $period->id)
                    ->where('is_posted', true)
                    ->count();
            @endphp
            @if(($statusCounts['computed'] ?? 0) > 0 || ($statusCounts['approved'] ?? 0) > 0 || $unpostedCount > 0)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6 p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600 font-medium">Bulk Actions & Status:</p>
                        <div class="flex space-x-2">
                            @if(($statusCounts['computed'] ?? 0) > 0)
                                <form action="{{ route('payroll.computation.bulk-approve', $period) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs font-semibold" 
                                            onclick="return confirm('Approve all computed payrolls?')">
                                        Approve All ({{ $statusCounts['computed'] ?? 0 }})
                                    </button>
                                </form>
                            @endif
                            @if(($statusCounts['approved'] ?? 0) > 0)
                                <form action="{{ route('payroll.computation.bulk-release', $period) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs font-semibold" 
                                            onclick="return confirm('Release all approved payrolls?')">
                                        Release All ({{ $statusCounts['approved'] ?? 0 }})
                                    </button>
                                </form>
                            @endif
                            @if($unpostedCount > 0)
                                <form action="{{ route('payroll.computation.bulk-post', $period) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-xs font-semibold" 
                                            onclick="return confirm('Post all approved/completed payrolls for employee viewing?')">
                                        Post to Employees ({{ $unpostedCount }})
                                    </button>
                                </form>
                            @endif
                            <div class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-xs font-semibold border">
                                Posted: {{ $postedCount }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Payrolls Table --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Basic Pay</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Pay</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($payrolls as $payroll)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $payroll->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $payroll->user->employee_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        {{ $payroll->days_worked ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                        ₱{{ number_format($payroll->basic_pay ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        @if(($payroll->overtime_pay ?? 0) > 0)
                                            <span class="text-blue-600">₱{{ number_format($payroll->overtime_pay, 2) }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                        ₱{{ number_format($payroll->gross_pay ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600">
                                        ₱{{ number_format($payroll->total_deductions ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-green-600">
                                        ₱{{ number_format($payroll->net_pay ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                @if($payroll->status === 'released') bg-green-100 text-green-800
                                                @elseif($payroll->status === 'approved') bg-yellow-100 text-yellow-800
                                                @elseif($payroll->status === 'computed') bg-gray-100 text-gray-800
                                                @elseif($payroll->status === 'rejected') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($payroll->status) }}
                                            </span>
                                            @if($payroll->is_posted)
                                                <span class="mt-1 flex items-center text-[10px] text-indigo-600 font-bold uppercase">
                                                    <i class="fas fa-check-circle mr-1"></i> Posted
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('payroll.computation.details', $payroll) }}" class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if(!$payroll->is_posted && in_array($payroll->status, ['approved', 'completed', 'released']))
                                                <form action="{{ route('payroll.computation.post', $payroll) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900" title="Post to Employee">
                                                        <i class="fas fa-upload"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($payroll->status === 'computed')
                                                <form action="{{ route('payroll.computation.approve', $payroll) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @elseif($payroll->status === 'approved')
                                                <form action="{{ route('payroll.computation.release', $payroll) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900" title="Release">
                                                        <i class="fas fa-parachute-box"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if(in_array($payroll->status, ['computed', 'approved']) && !$payroll->is_posted)
                                                <form action="{{ route('payroll.computation.recompute', $payroll) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900" title="Recompute">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                        No payrolls computed for this period yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($payrolls->hasPages())
                    <div class="px-6 py-4 border-t">
                        {{ $payrolls->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
