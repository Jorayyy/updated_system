<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Payroll Period') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('payroll-periods.update', $period) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="start_date" :value="__('Start Date')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $period->start_date->format('Y-m-d'))" required />
                            </div>

                            <div>
                                <x-input-label for="end_date" :value="__('End Date')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', $period->end_date->format('Y-m-d'))" required />
                            </div>

                            <div>
                                <x-input-label for="pay_date" :value="__('Pay Date')" />
                                <x-text-input id="pay_date" class="block mt-1 w-full" type="date" name="pay_date" :value="old('pay_date', $period->pay_date->format('Y-m-d'))" required />
                            </div>

                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                                    <option value="draft" {{ $period->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="active" {{ $period->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="processing" {{ $period->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="completed" {{ $period->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="remarks" :value="__('Remarks')" />
                                <x-text-input id="remarks" class="block mt-1 w-full" type="text" name="remarks" :value="old('remarks', $period->remarks)" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t pt-6">
                            <a href="{{ route('payroll-groups.show', $period->payroll_group_id) }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <x-primary-button class="bg-indigo-600">
                                {{ __('Update Period') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
