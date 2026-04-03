@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Accounting: Manual Review & Generation - ') }} {{ $period->payrollGroup->name }}
        </h2>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Progress Stepper -->
        <div class="flex items-center justify-between mb-8 space-x-2 text-sm text-gray-500 font-medium font-bold uppercase tracking-widest">
            <div class="flex-1 flex items-center justify-center p-3 text-indigo-600 bg-white border border-indigo-600 rounded-lg shadow-sm">
                <span class="mr-2 px-2 py-1 bg-indigo-600 text-white rounded-full text-xs">1</span>
                Selection Phase
            </div>
            <div class="w-10 h-px bg-gray-300"></div>
            <div class="flex-1 flex items-center justify-center p-3 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                 <span class="mr-2 px-2 py-1 bg-gray-300 text-white rounded-full text-xs">2</span>
                 Computation & Verification
            </div>
             <div class="w-10 h-px bg-gray-300"></div>
            <div class="flex-1 flex items-center justify-center p-3 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                 <span class="mr-2 px-2 py-1 bg-gray-300 text-white rounded-full text-xs">3</span>
                 Draft Generation
            </div>
        </div>

        <form action="{{ route('payroll.processing.review', $period) }}" method="POST">
            @csrf
            
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg text-black">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4 border-b pb-2">
                        <h3 class="text-lg font-semibold">Step 2: Select Employees to Sync Into Payslips</h3>
                        <div class="space-x-2">
                            <button type="button" onclick="selectAll(true)" class="text-xs font-bold text-indigo-600 hover:text-indigo-900 border border-indigo-200 rounded px-2 py-1">Select All</button>
                            <button type="button" onclick="selectAll(false)" class="text-xs font-bold text-red-600 hover:text-red-900 border border-red-200 rounded px-2 py-1">Clear All</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-700 font-bold uppercase tracking-wider border-b">
                                <tr>
                                    <th class="px-6 py-3 w-4">
                                        <input type="checkbox" id="check-all" onchange="selectAll(this.checked)" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    </th>
                                    <th class="px-6 py-3">Employee Name</th>
                                    <th class="px-6 py-3">Last Processed Salary</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-right">Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($employees as $employee)
                                    @php $existing = $existingPayrolls->get($employee->id); @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <input type="checkbox" name="user_ids[]" value="{{ $employee->id }}" 
                                                   class="employee-check w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                                   {{ $existing ? '' : 'checked' }}>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900">{{ $employee->name }}</div>
                                            <div class="text-xs text-gray-500">Emp Code: {{ $employee->employee_id ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($employee->monthly_salary > 0 || $employee->daily_rate > 0 || $employee->hourly_rate > 0)
                                                <div class="text-green-600 font-medium">
                                                    @if($employee->monthly_salary > 0)
                                                        ₱{{ number_format($employee->monthly_salary, 2) }} (Monthly)
                                                    @elseif($employee->daily_rate > 0)
                                                        ₱{{ number_format($employee->daily_rate, 2) }} (Daily)
                                                    @else
                                                        ₱{{ number_format($employee->hourly_rate, 2) }} (Hourly)
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-red-500 font-bold italic">NO SALARY SET</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($existing)
                                                <span class="px-2 py-1 rounded text-xs font-bold uppercase bg-green-100 text-green-800">Has Payslip</span>
                                                <div class="text-[10px] text-gray-400 mt-1 italic">Click to regenerate</div>
                                            @else
                                                <span class="px-2 py-1 rounded text-xs font-bold uppercase bg-gray-100 text-gray-800 italic">Not Generated</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="text-xs font-medium text-gray-500 italic">
                                                Net: ₱{{ $existing ? number_format($existing->net_pay, 2) : '0.00' }}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">
                                            No employees found for this group.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-6 bg-gray-50 border-t flex items-center justify-between">
                    <a href="{{ route('payroll.processing.index') }}" class="text-sm text-gray-600 hover:underline">Cancel & Back</a>
                    
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-bold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Continue to Phase 2: Review Calculation
                        <svg class="ml-2 -mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function selectAll(checked) {
        document.querySelectorAll('.employee-check').forEach(el => {
            el.checked = checked;
        });
        document.getElementById('check-all').checked = checked;
    }
</script>
@endsection