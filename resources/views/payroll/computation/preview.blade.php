<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payroll Preview') }}
            </h2>
            <a href="{{ route('payroll.computation.dashboard') }}" class="text-indigo-600 hover:text-indigo-900">
                ← Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Period Info --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Period</p>
                            <p class="text-lg font-semibold">
                                {{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Type</p>
                            <p class="text-lg font-semibold">{{ ucfirst(str_replace('_', ' ', $period->period_type)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Pay Date</p>
                            <p class="text-lg font-semibold">{{ $period->pay_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Employees</p>
                            <p class="text-lg font-semibold">{{ count($previews) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total DTRs</p>
                    <p class="text-xl font-semibold">{{ $summary['total_dtrs'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Hours</p>
                    <p class="text-xl font-semibold">{{ number_format($summary['total_hours'] ?? 0, 1) }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Overtime Hours</p>
                    <p class="text-xl font-semibold text-blue-600">{{ number_format($summary['total_overtime'] ?? 0, 1) }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Late (mins)</p>
                    <p class="text-xl font-semibold text-red-600">{{ number_format($summary['total_late'] ?? 0) }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Est. Gross Pay</p>
                    <p class="text-xl font-semibold text-green-600">₱{{ number_format($summary['estimated_gross'] ?? 0, 2) }}</p>
                </div>
            </div>

            {{-- Employee Preview Table --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700">Employee Payroll Preview</h3>
                    <p class="text-sm text-gray-500">Review DTR metrics before computing payroll</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">DTRs</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">OT Hours</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Late (mins)</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">UT (mins)</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Absences</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Est. Gross</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($previews as $preview)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $preview['employee']->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $preview['employee']->employee_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        {{ $preview['dtr_count'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        {{ number_format($preview['total_hours'], 1) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        @if($preview['overtime_hours'] > 0)
                                            <span class="text-blue-600 font-medium">{{ number_format($preview['overtime_hours'], 1) }}</span>
                                        @else
                                            <span class="text-gray-400">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        @if($preview['late_minutes'] > 0)
                                            <span class="text-red-600">{{ $preview['late_minutes'] }}</span>
                                        @else
                                            <span class="text-green-600">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        @if($preview['undertime_minutes'] > 0)
                                            <span class="text-orange-600">{{ $preview['undertime_minutes'] }}</span>
                                        @else
                                            <span class="text-green-600">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        @if($preview['absences'] > 0)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ $preview['absences'] }}
                                            </span>
                                        @else
                                            <span class="text-green-600">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                        ₱{{ number_format($preview['estimated_gross'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        No employees with approved DTRs for this period
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($previews) > 0)
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">Totals</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-semibold text-gray-900">
                                        {{ collect($previews)->sum('dtr_count') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-semibold text-gray-900">
                                        {{ number_format(collect($previews)->sum('total_hours'), 1) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-semibold text-blue-600">
                                        {{ number_format(collect($previews)->sum('overtime_hours'), 1) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-semibold text-red-600">
                                        {{ collect($previews)->sum('late_minutes') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-semibold text-orange-600">
                                        {{ collect($previews)->sum('undertime_minutes') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-semibold text-red-600">
                                        {{ collect($previews)->sum('absences') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-green-600">
                                        ₱{{ number_format(collect($previews)->sum('estimated_gross'), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end space-x-4">
                <a href="{{ route('payroll.computation.dashboard') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition">
                    Cancel
                </a>
                
                {{-- Manual Mode Button --}}
                <form action="{{ route('payroll.computation.compute', $period) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="manual_mode" value="1">
                    <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition font-semibold" onclick="return confirm('This will generate payroll records with zero amounts so you can manually enter them. Are you sure?')">
                        Initialize Manual Entry
                    </button>
                </form>

                <form action="{{ route('payroll.computation.compute', $period) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition font-semibold" onclick="return confirm('Proceed with payroll computation?')">
                        Compute Payroll
                    </button>
                </form>
                <form action="{{ route('payroll.computation.compute', $period) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="use_queue" value="1">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition font-semibold" onclick="return confirm('Queue payroll computation in background?')">
                        Queue in Background
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
