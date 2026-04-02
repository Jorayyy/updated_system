@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Manual DTR Entry</h1>
            <a href="{{ route('dtr-approval.index') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to DTR Center
            </a>
        </div>

        @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p class="font-bold">Please correct the following errors:</p>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-100">
            <form action="{{ route('dtr-approval.store') }}" method="POST" class="p-8">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Employee Selector -->
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Select Employee</label>
                        <select name="user_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Choose Employee --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('user_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }} ({{ $employee->emp_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payroll Period -->
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Payroll Period</label>
                        <select name="payroll_period_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Choose Period --</option>
                            @foreach($periods as $period)
                                <option value="{{ $period->id }}" {{ old('payroll_period_id') == $period->id ? 'selected' : '' }}>
                                    {{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date -->
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Log Date</label>
                        <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required 
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Status Selection -->
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status Flag</label>
                        <select name="attendance_status" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="present" {{ old('attendance_status') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ old('attendance_status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="on_leave" {{ old('attendance_status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                            <option value="late" {{ old('attendance_status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="half_day" {{ old('attendance_status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                        </select>
                    </div>

                    <!-- Time In -->
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Time In (24h)</label>
                        <input type="time" name="time_in" value="{{ old('time_in') }}"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500 italic">Leave blank if absent/on leave</p>
                    </div>

                    <!-- Time Out -->
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Time Out (24h)</label>
                        <input type="time" name="time_out" value="{{ old('time_out') }}"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Remarks -->
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Remarks / Reason</label>
                        <textarea name="remarks" rows="3" placeholder="e.g. Forgot to log, Field work, System override"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('remarks') }}</textarea>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end space-x-4">
                    <button type="reset" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-150">
                        Reset
                    </button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-md transition duration-150 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Save Manual Entry
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 text-sm text-blue-700">
            <p><strong>Note:</strong> Manually created DTRs are automatically marked as <strong>Approved</strong>. The system will recompute work hours and undertime based on the provided times and the employee's assigned schedule.</p>
        </div>
    </div>
</div>
@endsection