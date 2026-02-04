<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Adjust Payroll: ') . $payroll->user->name }}
            </h2>
            <a href="javascript:void(0)" onclick="window.history.back()" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <form action="{{ route('payroll.computation.update', $payroll) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        {{-- Earnings Section --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-green-700 border-b pb-2">Earnings</h3>
                            
                            <div>
                                <x-input-label for="basic_pay" :value="__('Basic Pay')" />
                                <x-text-input id="basic_pay" name="basic_pay" type="number" step="0.01" class="mt-1 block w-full" :value="old('basic_pay', $payroll->basic_pay)" required />
                            </div>

                            <div>
                                <x-input-label for="overtime_pay" :value="__('Overtime Pay')" />
                                <x-text-input id="overtime_pay" name="overtime_pay" type="number" step="0.01" class="mt-1 block w-full" :value="old('overtime_pay', $payroll->overtime_pay)" required />
                            </div>

                            <div>
                                <x-input-label for="holiday_pay" :value="__('Holiday Pay')" />
                                <x-text-input id="holiday_pay" name="holiday_pay" type="number" step="0.01" class="mt-1 block w-full" :value="old('holiday_pay', $payroll->holiday_pay)" required />
                            </div>

                            <div>
                                <x-input-label for="night_diff_pay" :value="__('Night Differential Pay')" />
                                <x-text-input id="night_diff_pay" name="night_diff_pay" type="number" step="0.01" class="mt-1 block w-full" :value="old('night_diff_pay', $payroll->night_diff_pay)" required />
                            </div>

                            <div>
                                <x-input-label for="rest_day_pay" :value="__('Rest Day Pay')" />
                                <x-text-input id="rest_day_pay" name="rest_day_pay" type="number" step="0.01" class="mt-1 block w-full" :value="old('rest_day_pay', $payroll->rest_day_pay)" required />
                            </div>

                            <div>
                                <x-input-label for="bonus" :value="__('Bonus')" />
                                <x-text-input id="bonus" name="bonus" type="number" step="0.01" class="mt-1 block w-full" :value="old('bonus', $payroll->bonus)" required />
                            </div>

                            <div>
                                <x-input-label for="allowances" :value="__('Allowances')" />
                                <x-text-input id="allowances" name="allowances" type="number" step="0.01" class="mt-1 block w-full" :value="old('allowances', $payroll->allowances)" required />
                            </div>
                        </div>

                        {{-- Deductions Section --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-red-700 border-b pb-2">Deductions</h3>

                            <div>
                                <x-input-label for="sss_contribution" :value="__('SSS Contribution')" />
                                <x-text-input id="sss_contribution" name="sss_contribution" type="number" step="0.01" class="mt-1 block w-full" :value="old('sss_contribution', $payroll->sss_contribution)" required />
                            </div>

                            <div>
                                <x-input-label for="philhealth_contribution" :value="__('PhilHealth Contribution')" />
                                <x-text-input id="philhealth_contribution" name="philhealth_contribution" type="number" step="0.01" class="mt-1 block w-full" :value="old('philhealth_contribution', $payroll->philhealth_contribution)" required />
                            </div>

                            <div>
                                <x-input-label for="pagibig_contribution" :value="__('Pag-IBIG Contribution')" />
                                <x-text-input id="pagibig_contribution" name="pagibig_contribution" type="number" step="0.01" class="mt-1 block w-full" :value="old('pagibig_contribution', $payroll->pagibig_contribution)" required />
                            </div>

                            <div>
                                <x-input-label for="withholding_tax" :value="__('Withholding Tax')" />
                                <x-text-input id="withholding_tax" name="withholding_tax" type="number" step="0.01" class="mt-1 block w-full" :value="old('withholding_tax', $payroll->withholding_tax)" required />
                            </div>

                            <div>
                                <x-input-label for="loan_deductions" :value="__('Loan Deductions')" />
                                <x-text-input id="loan_deductions" name="loan_deductions" type="number" step="0.01" class="mt-1 block w-full" :value="old('loan_deductions', $payroll->loan_deductions)" required />
                            </div>

                            <div>
                                <x-input-label for="leave_without_pay_deductions" :value="__('LWOP Deductions')" />
                                <x-text-input id="leave_without_pay_deductions" name="leave_without_pay_deductions" type="number" step="0.01" class="mt-1 block w-full" :value="old('leave_without_pay_deductions', $payroll->leave_without_pay_deductions)" required />
                            </div>

                            <div>
                                <x-input-label for="other_deductions" :value="__('Other Deductions')" />
                                <x-text-input id="other_deductions" name="other_deductions" type="number" step="0.01" class="mt-1 block w-full" :value="old('other_deductions', $payroll->other_deductions)" required />
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <x-input-label for="adjustment_reason" :value="__('Reason for Adjustment')" />
                        <textarea id="adjustment_reason" name="adjustment_reason" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3" required placeholder="Explain why these changes are being made...">{{ old('adjustment_reason', $payroll->adjustment_reason) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('adjustment_reason')" />
                    </div>

                    <div class="flex items-center justify-end border-t pt-6">
                        <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                            {{ __('Cancel') }}
                        </x-secondary-button>
                        <x-primary-button>
                            {{ __('Save Adjustments') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
            
            <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Note:</strong> Gross Pay, Total Deductions, and Net Pay will be automatically recalculated based on your inputs. Attendance-based deductions (Late, Undertime, Absences) are preserved from the original computation.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
