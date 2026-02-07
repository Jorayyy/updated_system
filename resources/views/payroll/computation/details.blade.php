<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payroll Details') }}
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Payroll Info --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Employee & Period Info --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $payroll->user->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $payroll->user->employee_id }}</p>
                                    <p class="text-sm text-gray-500">{{ $payroll->user->position ?? 'Position N/A' }}</p>
                                </div>
                                <div class="flex flex-col items-end space-y-2">
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full 
                                        @if($payroll->status === 'released') bg-green-100 text-green-800
                                        @elseif($payroll->status === 'approved') bg-yellow-100 text-yellow-800
                                        @elseif($payroll->status === 'computed') bg-gray-100 text-gray-800
                                        @elseif($payroll->status === 'rejected') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($payroll->status) }}
                                    </span>
                                    @if($payroll->is_manually_adjusted)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            <i class="fas fa-edit mr-1"></i> Manually Adjusted
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            @if($payroll->is_manually_adjusted)
                                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                    <h4 class="text-sm font-semibold text-amber-800 mb-1">
                                        <i class="fas fa-info-circle mr-1"></i> Manual Adjustment Note
                                    </h4>
                                    <p class="text-sm text-amber-900 italic">"{{ $payroll->adjustment_reason }}"</p>
                                    <p class="text-xs text-amber-700 mt-2">
                                        Adjusted by {{ $payroll->adjustedBy->name ?? 'Admin' }} on {{ $payroll->adjusted_at ? $payroll->adjusted_at->format('M d, Y h:i A') : 'N/A' }}
                                    </p>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Period</p>
                                    <p class="font-medium">
                                        {{ $payroll->payrollPeriod->start_date->format('M d') }} - {{ $payroll->payrollPeriod->end_date->format('M d, Y') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Days Worked</p>
                                    <p class="font-medium">{{ $payroll->days_worked ?? 0 }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Total Hours</p>
                                    <p class="font-medium">{{ number_format($payroll->hours_worked ?? 0, 1) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Computed At</p>
                                    <p class="font-medium">{{ $payroll->computed_at ? $payroll->computed_at->format('M d, Y g:i A') : '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Earnings Breakdown --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 border-b border-gray-200 bg-green-50">
                            <h3 class="text-lg font-semibold text-green-700">Earnings</h3>
                        </div>
                        <div class="p-6">
                            <table class="w-full">
                                <tbody class="divide-y divide-gray-200">
                                    <tr>
                                        <td class="py-2 text-gray-600">Basic Pay</td>
                                        <td class="py-2 text-right font-medium">₱{{ number_format($payroll->basic_pay ?? 0, 2) }}</td>
                                    </tr>
                                    @if(($payroll->overtime_pay ?? 0) > 0)
                                        <tr>
                                            <td class="py-2 text-gray-600">Overtime Pay ({{ number_format($payroll->overtime_hours ?? 0, 1) }} hrs)</td>
                                            <td class="py-2 text-right font-medium text-blue-600">₱{{ number_format($payroll->overtime_pay, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if(($payroll->holiday_pay ?? 0) > 0)
                                        <tr>
                                            <td class="py-2 text-gray-600">Holiday Pay</td>
                                            <td class="py-2 text-right font-medium text-purple-600">₱{{ number_format($payroll->holiday_pay, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if(($payroll->night_diff_pay ?? 0) > 0)
                                        <tr>
                                            <td class="py-2 text-gray-600">Night Differential</td>
                                            <td class="py-2 text-right font-medium text-indigo-600">₱{{ number_format($payroll->night_diff_pay, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if(($payroll->rest_day_pay ?? 0) > 0)
                                        <tr>
                                            <td class="py-2 text-gray-600">Rest Day Pay</td>
                                            <td class="py-2 text-right font-medium text-orange-600">₱{{ number_format($payroll->rest_day_pay, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if(($payroll->allowances ?? 0) > 0)
                                        <tr>
                                            <td class="py-2 text-gray-600">Allowances</td>
                                            <td class="py-2 text-right font-medium">₱{{ number_format($payroll->allowances, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if(($payroll->bonus ?? 0) > 0)
                                        <tr>
                                            <td class="py-2 text-gray-600">Bonus</td>
                                            <td class="py-2 text-right font-medium text-green-600">₱{{ number_format($payroll->bonus, 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr class="border-t-2 border-gray-300">
                                        <td class="py-3 font-semibold text-gray-900">Gross Pay</td>
                                        <td class="py-3 text-right font-bold text-lg text-green-600">₱{{ number_format($payroll->gross_pay ?? 0, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Deductions Breakdown --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 border-b border-gray-200 bg-red-50">
                            <h3 class="text-lg font-semibold text-red-700">Deductions</h3>
                        </div>
                        <div class="p-6">
                            <table class="w-full">
                                <tbody class="divide-y divide-gray-200">
                                    {{-- Government Contributions --}}
                                    <tr>
                                        <td class="py-2 text-gray-600">SSS Contribution</td>
                                        <td class="py-2 text-right font-medium text-red-600">₱{{ number_format($payroll->sss_contribution ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-gray-600">PhilHealth Contribution</td>
                                        <td class="py-2 text-right font-medium text-red-600">₱{{ number_format($payroll->philhealth_contribution ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-gray-600">Pag-IBIG Contribution</td>
                                        <td class="py-2 text-right font-medium text-red-600">₱{{ number_format($payroll->pagibig_contribution ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-gray-600">Withholding Tax</td>
                                        <td class="py-2 text-right font-medium text-red-600">₱{{ number_format($payroll->withholding_tax ?? 0, 2) }}</td>
                                    </tr>
                                    
                                    {{-- Attendance Deductions --}}
                                    @if(($payroll->late_deduction ?? 0) > 0)
                                        <tr>
                                            <td class="py-2 text-gray-600">Late Deduction ({{ $payroll->late_minutes ?? 0 }} mins)</td>
                                            <td class="py-2 text-right font-medium text-red-600">₱{{ number_format($payroll->late_deduction, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if(($payroll->undertime_deduction ?? 0) > 0)
                                        <tr>
                                            <td class="py-2 text-gray-600">Undertime Deduction ({{ $payroll->undertime_minutes ?? 0 }} mins)</td>
                                            <td class="py-2 text-right font-medium text-red-600">₱{{ number_format($payroll->undertime_deduction, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if(($payroll->absent_deduction ?? 0) > 0)
                                        <tr>
                                            <td class="py-2 text-gray-600">Absent Deduction ({{ $payroll->absences ?? 0 }} days)</td>
                                            <td class="py-2 text-right font-medium text-red-600">₱{{ number_format($payroll->absent_deduction, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if(($payroll->loan_deductions ?? 0) > 0)
                                        <tr>
                                            <td class="py-2 text-gray-600">Loan Deduction</td>
                                            <td class="py-2 text-right font-medium text-red-600">₱{{ number_format($payroll->loan_deductions, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if(($payroll->leave_without_pay_deductions ?? 0) > 0)
                                        <tr>
                                            <td class="py-2 text-gray-600">LWOP Deduction</td>
                                            <td class="py-2 text-right font-medium text-red-600">₱{{ number_format($payroll->leave_without_pay_deductions, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if(($payroll->other_deductions ?? 0) > 0)
                                        <tr>
                                            <td class="py-2 text-gray-600">Other Deductions</td>
                                            <td class="py-2 text-right font-medium text-red-600">₱{{ number_format($payroll->other_deductions, 2) }}</td>
                                        </tr>
                                    @endif

                                    <tr class="border-t-2 border-gray-300">
                                        <td class="py-3 font-semibold text-gray-900">Total Deductions</td>
                                        <td class="py-3 text-right font-bold text-lg text-red-600">₱{{ number_format($payroll->total_deductions ?? 0, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- DTR Breakdown --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700">Daily Time Record Breakdown</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Time In</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Time Out</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Hours</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">OT</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Late</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($dtrBreakdown as $dtr)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 whitespace-nowrap text-sm">
                                                <div class="font-medium text-gray-900">{{ $dtr->date->format('M d') }}</div>
                                                <div class="text-gray-500">{{ $dtr->date->format('l') }}</div>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center text-sm text-gray-600">
                                                {{ $dtr->time_in ? $dtr->time_in->format('g:i A') : '-' }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center text-sm text-gray-600">
                                                {{ $dtr->time_out ? $dtr->time_out->format('g:i A') : '-' }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center text-sm font-medium">
                                                {{ number_format($dtr->regular_hours ?? 0, 1) }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center text-sm">
                                                @if(($dtr->overtime_hours ?? 0) > 0)
                                                    <span class="text-blue-600">{{ number_format($dtr->overtime_hours, 1) }}</span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center text-sm">
                                                @if(($dtr->late_minutes ?? 0) > 0)
                                                    <span class="text-red-600">{{ $dtr->late_minutes }}m</span>
                                                @else
                                                    <span class="text-green-600">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center">
                                                @php
                                                    $flag = $dtr->status_flag ?? 'present';
                                                    $flagColors = [
                                                        'present' => 'bg-green-100 text-green-800',
                                                        'absent' => 'bg-red-100 text-red-800',
                                                        'late' => 'bg-yellow-100 text-yellow-800',
                                                        'half_day' => 'bg-orange-100 text-orange-800',
                                                        'holiday' => 'bg-purple-100 text-purple-800',
                                                        'leave' => 'bg-blue-100 text-blue-800',
                                                    ];
                                                @endphp
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $flagColors[$flag] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $flag)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-4 text-center text-gray-500">
                                                No DTR records found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Net Pay Card --}}
                    <div class="bg-gradient-to-br from-green-500 to-green-600 overflow-hidden shadow-sm rounded-lg text-white">
                        <div class="p-6">
                            <p class="text-green-100 text-sm">Net Pay</p>
                            <p class="text-3xl font-bold mt-1">₱{{ number_format($payroll->net_pay ?? 0, 2) }}</p>
                            <div class="mt-4 pt-4 border-t border-green-400">
                                <div class="flex justify-between text-sm">
                                    <span class="text-green-100">Gross Pay</span>
                                    <span>₱{{ number_format($payroll->gross_pay ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm mt-1">
                                    <span class="text-green-100">Deductions</span>
                                    <span>-₱{{ number_format($payroll->total_deductions ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Card --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700">Actions</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            @if($payroll->status === 'computed')
                                <form action="{{ route('payroll.computation.approve', $payroll) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition">
                                        Approve Payroll
                                    </button>
                                </form>
                            @endif

                            @if($payroll->status === 'approved')
                                <form action="{{ route('payroll.computation.release', $payroll) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">
                                        Release Payroll
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('payroll.computation.edit', $payroll) }}" class="block w-full px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition text-center mb-2">
                                <i class="fas fa-edit mr-1"></i> Adjust Manually
                            </a>

                            @if(in_array($payroll->status, ['computed', 'approved']))
                                <form action="{{ route('payroll.computation.recompute', $payroll) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                                        Recompute
                                    </button>
                                </form>

                                <button type="button" onclick="document.getElementById('rejectModal').classList.remove('hidden')" 
                                        class="w-full px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                                    Reject
                                </button>
                            @endif

                            @if($payroll->status === 'released')
                                <a href="{{ route('payroll.payslip-pdf', $payroll) }}" 
                                   class="block w-full px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600 transition text-center">
                                    Download Payslip PDF
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Audit Trail --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700">Audit Trail</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3 text-sm">
                                @if($payroll->computed_at)
                                    <div class="flex items-start">
                                        <div class="w-2 h-2 mt-1.5 rounded-full bg-gray-400"></div>
                                        <div class="ml-3">
                                            <p class="text-gray-600">Computed</p>
                                            <p class="text-gray-400">{{ $payroll->computed_at->format('M d, Y g:i A') }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($payroll->approved_at)
                                    <div class="flex items-start">
                                        <div class="w-2 h-2 mt-1.5 rounded-full bg-yellow-400"></div>
                                        <div class="ml-3">
                                            <p class="text-gray-600">Approved by {{ $payroll->approver->name ?? 'System' }}</p>
                                            <p class="text-gray-400">{{ $payroll->approved_at->format('M d, Y g:i A') }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($payroll->released_at)
                                    <div class="flex items-start">
                                        <div class="w-2 h-2 mt-1.5 rounded-full bg-green-400"></div>
                                        <div class="ml-3">
                                            <p class="text-gray-600">Released</p>
                                            <p class="text-gray-400">{{ $payroll->released_at->format('M d, Y g:i A') }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($payroll->rejected_at)
                                    <div class="flex items-start">
                                        <div class="w-2 h-2 mt-1.5 rounded-full bg-red-400"></div>
                                        <div class="ml-3">
                                            <p class="text-gray-600">Rejected</p>
                                            <p class="text-gray-400">{{ $payroll->rejected_at->format('M d, Y g:i A') }}</p>
                                            @if($payroll->rejection_reason)
                                                <p class="text-red-600 mt-1">{{ $payroll->rejection_reason }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Payroll</h3>
                <form action="{{ route('payroll.computation.reject', $payroll) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
                        <textarea name="reason" id="reason" rows="4" required
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Enter reason for rejecting this payroll..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            Reject Payroll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
