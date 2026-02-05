<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payroll Periods') }}
            </h2>
            <a href="{{ route('payroll.create-period') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Create Payroll Period
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Statistics -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Draft</div>
                    <div class="text-3xl font-bold text-gray-600">{{ $stats['draft'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Processing</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['processing'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Completed</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['completed'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Total Payroll (This Year)</div>
                    <div class="text-2xl font-bold text-indigo-600">₱{{ number_format($stats['total_amount'] ?? 0, 2) }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <select name="status" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <select name="year" class="border-gray-300 rounded-md shadow-sm">
                            @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Filter
                        </button>
                        <a href="{{ route('payroll.periods') }}" class="text-gray-600 hover:text-gray-800">Clear</a>
                    </form>
                </div>
            </div>

            <!-- Payroll Periods Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Range</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Employees</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross Pay</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net Pay</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($periods as $period)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $period->name }}</div>
                                            <div class="text-xs text-gray-500">{{ ucfirst($period->type) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            {{ $period->payrolls_count ?? $period->payrolls->count() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            ₱{{ number_format($period->payrolls->sum('gross_pay'), 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                                            ₱{{ number_format($period->payrolls->sum('net_pay'), 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($period->status == 'draft') bg-gray-100 text-gray-800
                                                @elseif($period->status == 'processing') bg-yellow-100 text-yellow-800
                                                @elseif($period->status == 'completed') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($period->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                <a href="{{ route('payroll.show-period', $period) }}" 
                                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium" title="View Period">View</a>
                                                
                                                @if(auth()->user()->isAccounting())
                                                    <a href="{{ route('payroll.computation.dashboard', ['period' => $period->id]) }}" 
                                                        class="text-blue-600 hover:text-blue-900 text-sm font-bold" title="Generate DTR & Payroll">
                                                        Generate
                                                    </a>
                                                @endif

                                                @if($period->status == 'draft')
                                                    <form action="{{ route('payroll.process-period', $period) }}" method="POST" class="inline"
                                                        onsubmit="return confirm('Are you sure you want to process this payroll period?')">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-900 text-sm font-medium">
                                                            Process
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($period->status == 'completed')
                                                    <a href="{{ route('payroll.report', $period) }}" 
                                                        class="text-purple-600 hover:text-purple-900 text-sm font-medium">Report</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No payroll periods found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $periods->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
