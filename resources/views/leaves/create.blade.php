<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Leave Request') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Leave Balances Summary -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Available Leave Credits</h3>
                        <div class="flex flex-wrap gap-4">
                            @foreach($leaveBalances as $balance)
                                <div class="text-sm">
                                    <span class="font-medium">{{ $balance->leaveType->name }}:</span>
                                    <span class="text-indigo-600 font-bold">{{ $balance->balance }}</span> days
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <form method="POST" action="{{ route('leaves.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="space-y-6">
                            <!-- Leave Type -->
                            <div>
                                <x-input-label for="leave_type_id" :value="__('Leave Type')" />
                                <select name="leave_type_id" id="leave_type_id" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Leave Type</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}" 
                                            data-requires-attachment="{{ $type->requires_attachment ? 'true' : 'false' }}"
                                            {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }} 
                                            @if($type->is_paid) (Paid) @else (Unpaid) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('leave_type_id')" class="mt-2" />
                            </div>

                            <!-- Date Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="start_date" :value="__('Start Date')" />
                                    <x-text-input type="date" name="start_date" id="start_date" 
                                        class="mt-1 block w-full"
                                        :value="old('start_date')" 
                                        min="{{ date('Y-m-d') }}"
                                        required />
                                    <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="end_date" :value="__('End Date')" />
                                    <x-text-input type="date" name="end_date" id="end_date" 
                                        class="mt-1 block w-full"
                                        :value="old('end_date')" 
                                        min="{{ date('Y-m-d') }}"
                                        required />
                                    <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Calculated Days -->
                            <div class="p-3 bg-blue-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-blue-700">Total Days:</span>
                                    <span class="text-lg font-bold text-blue-800" id="total-days">0</span>
                                </div>
                            </div>

                            <!-- Half Day Option -->
                            <div class="flex items-center">
                                <input type="checkbox" name="is_half_day" id="is_half_day" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    {{ old('is_half_day') ? 'checked' : '' }}>
                                <label for="is_half_day" class="ml-2 text-sm text-gray-600">
                                    Half Day Leave (for single day only)
                                </label>
                            </div>

                            <!-- Half Day Period -->
                            <div id="half-day-period" class="hidden">
                                <x-input-label for="half_day_period" :value="__('Half Day Period')" />
                                <select name="half_day_period" id="half_day_period"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="AM" {{ old('half_day_period') == 'AM' ? 'selected' : '' }}>Morning (AM)</option>
                                    <option value="PM" {{ old('half_day_period') == 'PM' ? 'selected' : '' }}>Afternoon (PM)</option>
                                </select>
                            </div>

                            <!-- Reason -->
                            <div>
                                <x-input-label for="reason" :value="__('Reason')" />
                                <textarea name="reason" id="reason" rows="4" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Please provide a detailed reason for your leave request...">{{ old('reason') }}</textarea>
                                <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                            </div>

                            <!-- Attachment -->
                            <div id="attachment-section">
                                <x-input-label for="attachment" :value="__('Attachment')" />
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="attachment" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload a file</span>
                                                <input id="attachment" name="attachment" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PDF, PNG, JPG up to 10MB</p>
                                        <p id="attachment-required" class="text-xs text-red-500 hidden">* Attachment required for this leave type</p>
                                    </div>
                                </div>
                                <div id="file-name" class="mt-2 text-sm text-gray-600 hidden"></div>
                                <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('leaves.index') }}" class="text-gray-600 hover:text-gray-800">
                                    Cancel
                                </a>
                                <x-primary-button>
                                    {{ __('Submit Leave Request') }}
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
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const totalDays = document.getElementById('total-days');
            const isHalfDay = document.getElementById('is_half_day');
            const halfDayPeriod = document.getElementById('half-day-period');
            const leaveTypeSelect = document.getElementById('leave_type_id');
            const attachmentRequired = document.getElementById('attachment-required');
            const attachmentInput = document.getElementById('attachment');
            const fileName = document.getElementById('file-name');

            function calculateDays() {
                if (startDate.value && endDate.value) {
                    const start = new Date(startDate.value);
                    const end = new Date(endDate.value);
                    let days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    
                    if (days === 1 && isHalfDay.checked) {
                        days = 0.5;
                    }
                    
                    totalDays.textContent = days > 0 ? days : 0;
                }
            }

            function toggleHalfDay() {
                if (isHalfDay.checked) {
                    halfDayPeriod.classList.remove('hidden');
                    // Set end date same as start date for half day
                    if (startDate.value) {
                        endDate.value = startDate.value;
                    }
                } else {
                    halfDayPeriod.classList.add('hidden');
                }
                calculateDays();
            }

            function checkAttachmentRequired() {
                const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                if (selectedOption && selectedOption.dataset.requiresAttachment === 'true') {
                    attachmentRequired.classList.remove('hidden');
                    attachmentInput.required = true;
                } else {
                    attachmentRequired.classList.add('hidden');
                    attachmentInput.required = false;
                }
            }

            startDate.addEventListener('change', function() {
                endDate.min = this.value;
                if (endDate.value && endDate.value < this.value) {
                    endDate.value = this.value;
                }
                calculateDays();
            });

            endDate.addEventListener('change', calculateDays);
            isHalfDay.addEventListener('change', toggleHalfDay);
            leaveTypeSelect.addEventListener('change', checkAttachmentRequired);

            attachmentInput.addEventListener('change', function() {
                if (this.files[0]) {
                    fileName.textContent = 'Selected: ' + this.files[0].name;
                    fileName.classList.remove('hidden');
                } else {
                    fileName.classList.add('hidden');
                }
            });

            // Initial state
            calculateDays();
            toggleHalfDay();
            checkAttachmentRequired();
        });
    </script>
    @endpush
</x-app-layout>
