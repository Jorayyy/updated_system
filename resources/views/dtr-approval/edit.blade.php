@extends('layouts.app')

@section('title', 'Modify DTR - ' . ($dailyTimeRecord->user->name ?? 'Employee'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb & Header -->
    <div class="mb-8">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm font-medium text-gray-500">
                <li class="inline-flex items-center">
                    <a href="{{ route('dtr-approval.index') }}" class="hover:text-indigo-600 transition-colors">DTR Approval</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('dtr-approval.show', $dailyTimeRecord) }}" class="ml-1 md:ml-2 hover:text-indigo-600 transition-colors">{{ $dailyTimeRecord->dtr_date->format('M d, Y') }}</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-gray-400 md:ml-2">Modify</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center">
            <svg class="w-8 h-8 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Modify Time Record
        </h1>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl overflow-hidden">
            <div class="bg-gray-900 px-8 py-6 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-1">Editing Record</p>
                    <h2 class="text-lg font-bold text-white tracking-tight">
                        {{ $dailyTimeRecord->user->name ?? 'Employee' }} — {{ $dailyTimeRecord->dtr_date->format('F d, Y') }}
                    </h2>
                </div>
                <div class="h-10 w-10 bg-indigo-500/20 rounded-xl flex items-center justify-center text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>

            <form action="{{ route('dtr-approval.update', $dailyTimeRecord) }}" method="POST" class="p-8">
                @csrf
                @method('PUT')

                <!-- Primary Time Inputs -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Time Entrance</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            </div>
                            <input type="time" name="time_in" class="w-full pl-12 pr-4 py-4 bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-gray-900 @error('time_in') border-red-500 bg-red-50 @enderror" value="{{ old('time_in', $dailyTimeRecord->time_in) }}">
                        </div>
                        @error('time_in') <p class="text-[10px] font-bold text-red-500 ml-1 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Time Exit</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-red-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            </div>
                            <input type="time" name="time_out" class="w-full pl-12 pr-4 py-4 bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all font-bold text-gray-900 @error('time_out') border-red-500 bg-red-50 @enderror" value="{{ old('time_out', $dailyTimeRecord->time_out) }}">
                        </div>
                        @error('time_out') <p class="text-[10px] font-bold text-red-500 ml-1 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Minutes Calculations -->
                <div class="bg-gray-50 rounded-2xl p-6 mb-10 border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-6 text-center">Calculated Exceptions (Minutes)</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">Late Minutes</label>
                            <input type="number" name="late_minutes" class="w-full px-4 py-3 bg-white border-gray-100 rounded-xl focus:ring-2 focus:ring-red-500/20 focus:border-red-500 font-black text-red-700" value="{{ old('late_minutes', $dailyTimeRecord->late_minutes) }}" min="0">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-amber-500 uppercase tracking-wider ml-1">Undertime</label>
                            <input type="number" name="undertime_minutes" class="w-full px-4 py-3 bg-white border-gray-100 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-black text-amber-700" value="{{ old('undertime_minutes', $dailyTimeRecord->undertime_minutes) }}" min="0">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-blue-500 uppercase tracking-wider ml-1">Overtime</label>
                            <input type="number" name="overtime_minutes" class="w-full px-4 py-3 bg-white border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-black text-blue-700" value="{{ old('overtime_minutes', $dailyTimeRecord->overtime_minutes) }}" min="0">
                        </div>
                    </div>
                </div>

                <!-- Type & Totals -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Service Hours (Calculated)</label>
                        <div class="relative">
                            <input type="number" name="total_hours_worked" step="0.01" class="w-full px-4 py-4 bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 font-black text-gray-900 pr-12" value="{{ old('total_hours_worked', $dailyTimeRecord->total_hours_worked) }}" min="0">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-black">Hr</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Classification of Day</label>
                        <select name="day_type" class="w-full px-4 py-4 bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 font-bold text-gray-900 appearance-none">
                            <option value="regular" {{ old('day_type', $dailyTimeRecord->day_type) === 'regular' ? 'selected' : '' }}>Regular Workday</option>
                            <option value="holiday" {{ old('day_type', $dailyTimeRecord->day_type) === 'holiday' ? 'selected' : '' }}>Public Holiday</option>
                            <option value="special_holiday" {{ old('day_type', $dailyTimeRecord->day_type) === 'special_holiday' ? 'selected' : '' }}>Special Non-Working</option>
                            <option value="rest_day" {{ old('day_type', $dailyTimeRecord->day_type) === 'rest_day' ? 'selected' : '' }}>Official Rest Day</option>
                        </select>
                    </div>
                </div>

                <!-- Remarks -->
                <div class="mb-10 space-y-2">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Administrative Remarks</label>
                    <textarea name="remarks" rows="4" class="w-full px-4 py-4 bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 font-medium text-gray-700" placeholder="Notes regarding this adjustment...">{{ old('remarks', $dailyTimeRecord->remarks) }}</textarea>
                </div>

                <!-- Footer Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-4 border-t border-gray-50">
                    <a href="{{ route('dtr-approval.show', $dailyTimeRecord) }}" class="w-full sm:w-auto px-8 py-3 rounded-2xl bg-white border border-gray-200 text-gray-500 font-bold hover:bg-gray-50 hover:text-gray-700 transition-all text-center uppercase tracking-widest text-xs">
                        Discard Changes
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-10 py-4 rounded-2xl bg-indigo-600 text-white font-black hover:bg-indigo-700 shadow-xl shadow-indigo-200 transition-all uppercase tracking-widest text-sm">
                        Apply Adjustments
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
