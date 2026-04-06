@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Accounting: Phase 2 - Review Calculation') }} - {{ $period->payrollGroup->name }}
        </h2>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Progress Stepper -->
        <div class="flex items-center justify-between mb-8 space-x-2 text-sm text-gray-500 font-medium font-bold uppercase tracking-widest">
            <div class="flex-1 flex items-center justify-center p-3 text-green-600 bg-green-50 border border-green-200 rounded-lg shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                Selection Phase
            </div>
            <div class="w-10 h-px bg-green-300"></div>
            <div class="flex-1 flex items-center justify-center p-3 text-indigo-600 bg-white border border-indigo-600 rounded-lg shadow-sm">
                 <span class="mr-2 px-2 py-1 bg-indigo-600 text-white rounded-full text-xs">2</span>
                 Computation & Verification
            </div>
             <div class="w-10 h-px bg-gray-300"></div>
            <div class="flex-1 flex items-center justify-center p-3 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                 <span class="mr-2 px-2 py-1 bg-gray-300 text-white rounded-full text-xs">3</span>
                 Draft Generation
            </div>
        </div>

        <form action="{{ route('payroll.processing.process', $period) }}" method="POST">
            @csrf
            @foreach($userIds as $id)
                <input type="hidden" name="user_ids[]" value="{{ $id }}">
            @endforeach
            
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg text-black">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2 text-indigo-600">Step 3: Verify Computed Amounts</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-100 text-gray-700 font-bold uppercase tracking-wider border-b">
                                <tr>
                                    <th class="px-4 py-3">Employee</th>
                                    <th class="px-4 py-3 text-right">Basic Pay</th>
                                    <th class="px-4 py-3 text-right text-blue-600">OT/Holiday</th>
                                    <th class="px-4 py-3 text-right text-red-600">Late/Undert.</th>
                                    <th class="px-4 py-3 text-right text-red-600">Abs/LWOP</th>
                                    <th class="px-4 py-3 text-right text-green-600">Allowance/Bonus</th>
                                    <th class="px-4 py-3 text-right font-bold bg-green-50">Gross Pay</th>
                                    <th class="px-4 py-3 text-right text-red-700 bg-red-50">Deductions</th>
                                    <th class="px-4 py-3 text-right font-black border-l-2 border-indigo-200 bg-indigo-50">Net Pay</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($previews as $preview)
                                    @php 
                                        $data = $preview['data']; 
                                        $uId = $preview['user']->id;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors" x-data="{ 
                                        basic: {{ $data['basic_pay'] }}, 
                                        ot: {{ $data['overtime_pay'] + $data['holiday_pay'] + $data['night_diff_pay'] + $data['rest_day_pay'] }},
                                        late: {{ $data['late_deduction'] + $data['undertime_deduction'] }},
                                        absent: {{ $data['absent_deduction'] + $data['leave_without_pay_deduction'] }},
                                        bonus: {{ $data['allowances'] + $data['bonus'] }},
                                        gov: {{ $data['total_deductions'] - ($data['late_deduction'] + $data['undertime_deduction'] + $data['absent_deduction'] + $data['leave_without_pay_deduction']) }}
                                    }">
                                        <td class="px-4 py-4 border-r">
                                            <div class="font-bold text-gray-900">{{ $preview['user']->name }}</div>
                                            <div class="text-[10px] text-gray-500">{{ $preview['user']->employee_id }}</div>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <input type="number" step="0.01" name="adjustments[{{ $uId }}][basic_pay]" 
                                                x-model.number="basic"
                                                class="w-24 text-right border-gray-300 rounded-md text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                        </td>
                                        <td class="px-4 py-4 text-right text-blue-600 font-medium">
                                            <input type="number" step="0.01" name="adjustments[{{ $uId }}][overtime_pay]" 
                                                x-model.number="ot"
                                                class="w-24 text-right border-gray-300 rounded-md text-xs focus:ring-blue-500 focus:border-blue-500 text-blue-600">
                                        </td>
                                        <td class="px-4 py-4 text-right text-red-500">
                                            <input type="number" step="0.01" name="adjustments[{{ $uId }}][late_undertime_deduction]" 
                                                x-model.number="late"
                                                class="w-24 text-right border-gray-300 rounded-md text-xs focus:ring-red-500 focus:border-red-500 text-red-600">
                                        </td>
                                        <td class="px-4 py-4 text-right text-red-500">
                                            <input type="number" step="0.01" name="adjustments[{{ $uId }}][absent_lwop_deduction]" 
                                                x-model.number="absent"
                                                class="w-24 text-right border-gray-300 rounded-md text-xs focus:ring-red-500 focus:border-red-500 text-red-600">
                                        </td>
                                        <td class="px-4 py-4 text-right text-green-600">
                                            <input type="number" step="0.01" name="adjustments[{{ $uId }}][allowance_bonus]" 
                                                x-model.number="bonus"
                                                class="w-24 text-right border-gray-300 rounded-md text-xs focus:ring-green-500 focus:border-green-500 text-green-600">
                                        </td>
                                        <td class="px-4 py-4 text-right font-bold bg-green-50">
                                            ₱<span x-text="(basic + ot + bonus).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                        </td>
                                        <td class="px-4 py-4 text-right text-red-700 bg-red-50">
                                            -₱<span x-text="(gov + late + absent).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                        </td>
                                        <td class="px-4 py-4 text-right font-black border-l-2 border-indigo-200 bg-indigo-50 text-indigo-900">
                                            ₱<span x-text="(basic + ot + bonus - (gov + late + absent)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 font-bold border-t-2">
                                <tr>
                                    <td class="px-4 py-3">TOTAL ({{ count($previews) }} Employees)</td>
                                    <td class="px-4 py-3 text-right">₱{{ number_format(collect($previews)->sum('data.basic_pay'), 2) }}</td>
                                    <td class="px-4 py-3 text-right text-blue-600">₱{{ number_format(collect($previews)->sum(fn($p) => $p['data']['overtime_pay'] + $p['data']['holiday_pay'] + $p['data']['night_diff_pay'] + $p['data']['rest_day_pay']), 2) }}</td>
                                    <td colspan="2" class="px-4 py-3 text-right text-red-500">
                                        -₱{{ number_format(collect($previews)->sum(fn($p) => $p['data']['late_deduction'] + $p['data']['undertime_deduction'] + $p['data']['absent_deduction'] + $p['data']['leave_without_pay_deduction']), 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right bg-green-100">₱{{ number_format(collect($previews)->sum('data.gross_pay'), 2) }}</td>
                                    <td class="px-4 py-3 text-right bg-red-100">-₱{{ number_format(collect($previews)->sum('data.total_deductions'), 2) }}</td>
                                    <td class="px-4 py-3 text-right bg-indigo-100 text-indigo-900">₱{{ number_format(collect($previews)->sum('data.net_pay'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="p-6 bg-gray-50 border-t flex items-center justify-between">
                    <button type="button" onclick="window.history.back()" class="text-sm text-indigo-600 font-bold hover:underline">
                        &larr; Back to Selection
                    </button>
                    
                    <button type="submit" class="inline-flex items-center px-8 py-4 bg-indigo-600 border border-transparent rounded-md font-black text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring ring-indigo-300 transition ease-in-out duration-150 shadow-lg">
                        Confirm & Generate Phase 3 &rarr;
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
