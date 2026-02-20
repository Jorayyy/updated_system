<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('transactions.index') }}" class="text-gray-500 hover:text-gray-700 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $typeInfo['name'] }}
            </h2>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('transactions.store', $type) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="space-y-6">
                            {{-- Leave Type (for leave applications) --}}
                            @if($type === 'leave')
                                <div>
                                    <label for="leave_type_id" class="block text-sm font-medium text-gray-700">
                                        Leave Type <span class="text-red-500">*</span>
                                    </label>
                                    <select name="leave_type_id" id="leave_type_id" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Select leave type</option>
                                        @foreach($leaveTypes as $leaveType)
                                            <option value="{{ $leaveType->id }}" {{ old('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                                {{ $leaveType->name }}
                                                @if(isset($leaveBalances[$leaveType->id]))
                                                    (Available: {{ $leaveBalances[$leaveType->id]->remaining_days }} days)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('leave_type_id')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif

                            {{-- Leave Cancellation - Select original leave --}}
                            @if($type === 'leave_cancellation')
                                <div>
                                    <label for="original_transaction_id" class="block text-sm font-medium text-gray-700">
                                        Select Leave to Cancel <span class="text-red-500">*</span>
                                    </label>
                                    <select name="original_transaction_id" id="original_transaction_id" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Select approved leave</option>
                                        @foreach($approvedLeaves as $leave)
                                            <option value="{{ $leave->id }}" {{ old('original_transaction_id') == $leave->id ? 'selected' : '' }}>
                                                {{ $leave->transaction_number }} - {{ $leave->leaveType->name ?? 'N/A' }} 
                                                ({{ $leave->effective_date->format('M d, Y') }} - {{ $leave->effective_date_end->format('M d, Y') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($approvedLeaves->isEmpty())
                                        <p class="mt-1 text-sm text-yellow-600">No approved future leaves found to cancel.</p>
                                    @endif
                                    @error('original_transaction_id')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif

                            {{-- Payroll Complaint specific fields --}}
                            @if($type === 'payroll_complaint')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="payroll_period" class="block text-sm font-medium text-gray-700">
                                            Payroll Period <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="payroll_period" id="payroll_period" required
                                               placeholder="e.g., Jan 1-15, 2026"
                                               value="{{ old('payroll_period') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        @error('payroll_period')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="complaint_type" class="block text-sm font-medium text-gray-700">
                                            Complaint Type <span class="text-red-500">*</span>
                                        </label>
                                        <select name="complaint_type" id="complaint_type" required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            <option value="">Select type</option>
                                            <option value="incorrect_salary" {{ old('complaint_type') === 'incorrect_salary' ? 'selected' : '' }}>Incorrect Salary</option>
                                            <option value="missing_overtime" {{ old('complaint_type') === 'missing_overtime' ? 'selected' : '' }}>Missing Overtime Pay</option>
                                            <option value="wrong_deduction" {{ old('complaint_type') === 'wrong_deduction' ? 'selected' : '' }}>Wrong Deduction</option>
                                            <option value="missing_allowance" {{ old('complaint_type') === 'missing_allowance' ? 'selected' : '' }}>Missing Allowance</option>
                                            <option value="tax_issue" {{ old('complaint_type') === 'tax_issue' ? 'selected' : '' }}>Tax Issue</option>
                                            <option value="other" {{ old('complaint_type') === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('complaint_type')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            {{-- Timekeeping Complaint specific fields --}}
                            @if($type === 'timekeeping_complaint')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="date_affected" class="block text-sm font-medium text-gray-700">
                                            Date Affected <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="date_affected" id="date_affected" required
                                               value="{{ old('date_affected') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        @error('date_affected')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="affected_punch" class="block text-sm font-medium text-gray-700">
                                            Affected Punch <span class="text-red-500">*</span>
                                        </label>
                                        <select name="affected_punch" id="affected_punch" required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            <option value="">Select Punch</option>
                                            @php
                                                $punches = ['IN', '1st BREAK OUT', '1st BREAK IN', 'LUNCH BREAK OUT', 'LUNCH BREAK IN', '2nd BREAK OUT', '2nd BREAK IN', 'OUT'];
                                            @endphp
                                            @foreach($punches as $punch)
                                                <option value="{{ $punch }}" {{ old('affected_punch') == $punch ? 'selected' : '' }}>{{ $punch }}</option>
                                            @endforeach
                                        </select>
                                        @error('affected_punch')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            {{-- Schedule Change / Rest Day Change specific fields --}}
                            @if($type === 'schedule_change' || $type === 'restday_change')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="current_schedule" class="block text-sm font-medium text-gray-700">
                                            Current {{ $type === 'restday_change' ? 'Rest Day' : 'Schedule' }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="current_schedule" id="current_schedule" required
                                               placeholder="{{ $type === 'restday_change' ? 'e.g., Saturday & Sunday' : 'e.g., 8:00 AM - 5:00 PM' }}"
                                               value="{{ old('current_schedule') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        @error('current_schedule')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="requested_schedule" class="block text-sm font-medium text-gray-700">
                                            Requested {{ $type === 'restday_change' ? 'Rest Day' : 'Schedule' }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="requested_schedule" id="requested_schedule" required
                                               placeholder="{{ $type === 'restday_change' ? 'e.g., Sunday & Monday' : 'e.g., 9:00 AM - 6:00 PM' }}"
                                               value="{{ old('requested_schedule') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        @error('requested_schedule')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            {{-- Official Business specific fields --}}
                            @if($type === 'official_business')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="destination" class="block text-sm font-medium text-gray-700">
                                            Destination <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="destination" id="destination" required
                                               placeholder="e.g., Client Office, BGC"
                                               value="{{ old('destination') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        @error('destination')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="purpose" class="block text-sm font-medium text-gray-700">
                                            Purpose <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="purpose" id="purpose" required
                                               placeholder="e.g., Client meeting, Training"
                                               value="{{ old('purpose') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        @error('purpose')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            {{-- Undertime specific fields --}}
                            @if($type === 'undertime')
                                <div>
                                    <label for="undertime_type" class="block text-sm font-medium text-gray-700">
                                        Undertime Type <span class="text-red-500">*</span>
                                    </label>
                                    <select name="undertime_type" id="undertime_type" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="early_out" {{ old('undertime_type', 'early_out') === 'early_out' ? 'selected' : '' }}>Early Out</option>
                                        <option value="late_in" {{ old('undertime_type') === 'late_in' ? 'selected' : '' }}>Late In</option>
                                        <option value="half_day" {{ old('undertime_type') === 'half_day' ? 'selected' : '' }}>Half Day</option>
                                    </select>
                                    @error('undertime_type')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif

                            {{-- Date Fields --}}
                            @if($typeInfo['requires_dates'] ?? false)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="effective_date" class="block text-sm font-medium text-gray-700">
                                            {{ $type === 'leave' ? 'Start Date' : 'Effective Date' }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="effective_date" id="effective_date" required
                                               value="{{ old('effective_date') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        @error('effective_date')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    @if($type !== 'leave_cancellation')
                                        <div>
                                            <label for="effective_date_end" class="block text-sm font-medium text-gray-700">
                                                {{ $type === 'leave' ? 'End Date' : 'End Date (if applicable)' }}
                                            </label>
                                            <input type="date" name="effective_date_end" id="effective_date_end"
                                                   value="{{ old('effective_date_end') }}"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            @error('effective_date_end')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Time Fields --}}
                            @if($typeInfo['requires_time'] ?? false)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="time_from" class="block text-sm font-medium text-gray-700">
                                            Time From
                                        </label>
                                        <input type="time" name="time_from" id="time_from"
                                               value="{{ old('time_from') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        @error('time_from')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="time_to" class="block text-sm font-medium text-gray-700">
                                            Time To
                                        </label>
                                        <input type="time" name="time_to" id="time_to"
                                               value="{{ old('time_to') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        @error('time_to')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            {{-- Attachment field (for all types) --}}
                            <div>
                                <label for="attachment" class="block text-sm font-medium text-gray-700">
                                    Attachment (e.g., Screenshot, Document)
                                </label>
                                <input type="file" name="attachment" id="attachment"
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="mt-1 text-xs text-gray-500">Max size: 5MB. Formats: JPG, PNG, PDF, DOCX.</p>
                                @error('attachment')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Reason (all types) --}}
                            <div>
                                <label for="reason" class="block text-sm font-medium text-gray-700">
                                    {{ $type === 'payroll_complaint' ? 'Details / Explanation' : 'Reason' }} <span class="text-red-500">*</span>
                                </label>
                                <textarea name="reason" id="reason" rows="4" required
                                          placeholder="Please provide detailed information..."
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Info Box --}}
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="text-sm text-blue-700">
                                        <p class="font-medium">Approval Process</p>
                                        <p class="mt-1">Your request will be reviewed by HR first, then by Admin for final approval.</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('transactions.index') }}" 
                                   class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                                    Cancel
                                </a>
                                <button type="submit" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                    Submit Request
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
