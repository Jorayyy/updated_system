<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Payroll Period') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('payroll.store-period') }}">
                        @csrf

                        <div class="space-y-6">
                            <!-- Period Name -->
                            <div>
                                <x-input-label for="name" :value="__('Period Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" 
                                    :value="old('name')" required placeholder="e.g., January 1-15, 2024" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Period Type -->
                            <div>
                                <x-input-label for="type" :value="__('Payroll Type')" />
                                <select name="type" id="type" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="semi-monthly" {{ old('type') == 'semi-monthly' ? 'selected' : '' }}>Semi-Monthly (15th & 30th)</option>
                                    <option value="monthly" {{ old('type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="weekly" {{ old('type') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="bi-weekly" {{ old('type') == 'bi-weekly' ? 'selected' : '' }}>Bi-Weekly</option>
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <!-- Date Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="start_date" :value="__('Start Date')" />
                                    <x-text-input type="date" name="start_date" id="start_date" 
                                        class="mt-1 block w-full"
                                        :value="old('start_date')" required />
                                    <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="end_date" :value="__('End Date')" />
                                    <x-text-input type="date" name="end_date" id="end_date" 
                                        class="mt-1 block w-full"
                                        :value="old('end_date')" required />
                                    <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Pay Date -->
                            <div>
                                <x-input-label for="pay_date" :value="__('Pay Date')" />
                                <x-text-input type="date" name="pay_date" id="pay_date" 
                                    class="mt-1 block w-full"
                                    :value="old('pay_date')" required />
                                <p class="text-xs text-gray-500 mt-1">The date when employees will receive their salary</p>
                                <x-input-error :messages="$errors->get('pay_date')" class="mt-2" />
                            </div>

                            <!-- Description -->
                            <div>
                                <x-input-label for="description" :value="__('Notes/Description')" />
                                <textarea id="description" name="description" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Optional notes for this payroll period...">{{ old('description') }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <!-- Info Box -->
                            <div class="p-4 bg-blue-50 rounded-lg">
                                <h4 class="text-sm font-medium text-blue-800 mb-2">What happens next?</h4>
                                <ul class="text-sm text-blue-700 space-y-1">
                                    <li>• The payroll period will be created in "Draft" status</li>
                                    <li>• You can review and adjust individual payroll records</li>
                                    <li>• Click "Process" to calculate all employee payrolls</li>
                                    <li>• Once completed, payslips will be available for employees</li>
                                </ul>
                            </div>

                            <!-- Submit -->
                            <div class="flex items-center justify-end gap-4 pt-4 border-t">
                                <a href="{{ route('payroll.periods') }}" class="text-gray-600 hover:text-gray-800">
                                    Cancel
                                </a>
                                <x-primary-button>
                                    {{ __('Create Payroll Period') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const payDate = document.getElementById('pay_date');
            const nameInput = document.getElementById('name');

            function updateDates() {
                if (!startDate.value) return;
                
                const start = new Date(startDate.value);
                const type = typeSelect.value;
                let end, pay;

                switch(type) {
                    case 'semi-monthly':
                        if (start.getDate() <= 15) {
                            end = new Date(start.getFullYear(), start.getMonth(), 15);
                        } else {
                            end = new Date(start.getFullYear(), start.getMonth() + 1, 0); // Last day
                        }
                        pay = new Date(end);
                        pay.setDate(pay.getDate() + 5);
                        break;
                    case 'monthly':
                        end = new Date(start.getFullYear(), start.getMonth() + 1, 0);
                        pay = new Date(end);
                        pay.setDate(pay.getDate() + 5);
                        break;
                    case 'weekly':
                        end = new Date(start);
                        end.setDate(end.getDate() + 6);
                        pay = new Date(end);
                        pay.setDate(pay.getDate() + 3);
                        break;
                    case 'bi-weekly':
                        end = new Date(start);
                        end.setDate(end.getDate() + 13);
                        pay = new Date(end);
                        pay.setDate(pay.getDate() + 5);
                        break;
                }

                endDate.value = end.toISOString().split('T')[0];
                payDate.value = pay.toISOString().split('T')[0];

                // Auto-generate name
                const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                               'July', 'August', 'September', 'October', 'November', 'December'];
                nameInput.value = `${months[start.getMonth()]} ${start.getDate()}-${end.getDate()}, ${start.getFullYear()}`;
            }

            typeSelect.addEventListener('change', updateDates);
            startDate.addEventListener('change', updateDates);
        });
    </script>
    @endpush
</x-app-layout>
