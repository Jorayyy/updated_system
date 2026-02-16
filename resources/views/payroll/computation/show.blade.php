<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Payroll Management') }}
                </h2>
                <div class="text-sm text-gray-500 mt-1 space-x-2">
                    <span>Period: <span class="font-medium text-gray-700">{{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}</span></span>
                    <span class="text-gray-300">|</span>
                    <span>Group: <span class="font-medium text-gray-700">{{ $period->payrollGroup->name ?? 'Global' }}</span></span>
                    <span class="text-gray-300">|</span>
                    <span>Status: <span class="uppercase font-bold {{ $period->status === 'completed' ? 'text-green-600' : 'text-blue-600' }}">{{ $period->status }}</span></span>
                </div>
            </div>
            
            <div class="flex space-x-2">
                <a href="{{ route('payroll.computation.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium">
                    ← Back to Dashboard
                </a>
                
                @if($payrolls->isNotEmpty())
                    <a href="{{ route('payroll.computation.export', ['period' => $period, 'format' => 'excel']) }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium shadow-sm transition">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Summary Cards (Only show if initialized) --}}
            @if($payrolls->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Employees</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $payrolls->total() }}</dd>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Gross Pay</dt>
                        <dd class="mt-1 text-3xl font-semibold text-indigo-600">₱{{ number_format($summary['total_gross'] ?? 0, 2) }}</dd>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Deductions</dt>
                        <dd class="mt-1 text-3xl font-semibold text-red-600">₱{{ number_format($summary['total_deductions'] ?? 0, 2) }}</dd>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Net Pay</dt>
                        <dd class="mt-1 text-3xl font-semibold text-green-600">₱{{ number_format($summary['total_net'] ?? 0, 2) }}</dd>
                    </div>
                </div>
            @endif

            {{-- Main Content Area --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                
                @if($payrolls->isEmpty())
                    {{-- EMPTY STATE: Initialization Options --}}
                    <div class="p-12 text-center">
                        <div class="mx-auto h-24 w-24 text-gray-300 mb-6">
                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Payroll Not Yet Initialized</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">This payroll period is currently empty. Initialize it to start manual entry.</p>
                        
                        <div class="max-w-md mx-auto">
                            
                            {{-- Option 1: Manual Mode (Only) --}}
                            <div class="border-2 border-indigo-200 rounded-xl p-8 hover:border-indigo-400 hover:shadow-lg transition cursor-pointer group bg-indigo-50 relative overflow-hidden">
                                <h4 class="font-bold text-xl text-indigo-900 mb-4 text-center">Start Manual Payroll Entry</h4>
                                <p class="text-sm text-indigo-700 mb-8 text-center">This will create empty payroll records for all employees. You can then manually type in the salary, deductions, and net pay for each person.</p>
                                
                                <form action="{{ route('payroll.computation.compute', $period) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="manual_mode" value="1">
                                    <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-lg transition transform hover:scale-105" onclick="return confirm('Start Manual Payroll?\n\nThis will create blank records for you to fill in.')">
                                        Initialize Manual Entry Now
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                
                @else
                    {{-- DATA STATE: Table & Bulk Actions --}}
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center space-x-2">
                             <h3 class="text-lg font-bold text-gray-800">Payroll Records</h3>
                             <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">{{ $payrolls->total() }}</span>
                        </div>
                        
                        <div class="flex space-x-2">
                             @if(($statusCounts['computed'] ?? 0) > 0)
                                <form action="{{ route('payroll.computation.bulk-approve', $period) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150" onclick="return confirm('Approve all computed payrolls?')">
                                        Approve All
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('payroll.computation.compute', $period) }}" method="POST">
                                @csrf
                                <input type="hidden" name="manual_mode" value="1">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-md font-semibold text-xs text-blue-700 uppercase tracking-widest shadow-sm hover:text-blue-500 hover:bg-blue-50 focus:outline-none focus:border-blue-300 focus:ring ring-blue-300 active:bg-blue-50 active:text-blue-800 transition ease-in-out duration-150" onclick="return confirm('WARNING: This will RESET all amounts.\n\nAre you sure you want to re-initialize?')">
                                    Re-Initialize
                                </button>
                            </form>

                            @if(auth()->user()->hasRole('super_admin'))
                                <form action="{{ route('payroll.computation.bulk-delete', $period) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 transition" onclick="return confirm('EXTREME WARNING: This will PERMANENTLY DELETE all payroll records for this period.\n\nThis action cannot be undone. Proceed?')">
                                        Delete All
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Work Days</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Pay</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($payrolls as $payroll)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-xs ring-2 ring-white">
                                                    {{ substr($payroll->user->name, 0, 2) }}
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-bold text-gray-900">{{ $payroll->user->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $payroll->user->employee_id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                            <span class="font-medium">{{ $payroll->total_work_days }}</span> days
                                            @if($payroll->total_overtime_minutes > 0)
                                                <div class="text-xs text-blue-600">+{{ number_format($payroll->total_overtime_minutes/60, 1) }}h OT</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <div class="font-medium text-gray-900">₱{{ number_format($payroll->gross_pay, 2) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600">
                                            - ₱{{ number_format($payroll->total_deductions, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full bg-green-50 text-green-700">
                                                ₱{{ number_format($payroll->net_pay, 2) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($payroll->status == 'approved') bg-green-100 text-green-800 
                                                @elseif($payroll->status == 'computed') bg-yellow-100 text-yellow-800 
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($payroll->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-3">
                                                <a href="{{ route('payroll.computation.edit', $payroll) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    Edit
                                                </a>
                                                @if(auth()->user()->hasRole('super_admin'))
                                                    <form action="{{ route('payroll.computation.destroy', $payroll) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold" onclick="return confirm('Delete this payroll record?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($payrolls->hasPages())
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                            {{ $payrolls->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
