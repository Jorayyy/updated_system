@extends('layouts.app')

@section('title', 'Request DTR correction')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center">
                <svg class="w-8 h-8 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                DTR Correction Request
            </h1>
            <p class="text-sm text-gray-500 mt-1">Submit an official request to adjust your time records for HR review.</p>
        </div>
        <a href="{{ route('dtr-records.show', $dailyTimeRecord) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-bold rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Cancel
        </a>
    </div>

    <div class="max-w-4xl mx-auto space-y-8">
        <!-- Current Record Summary Card -->
        <div class="bg-gray-900 rounded-3xl p-8 text-white shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-1">Current Logged Record</p>
                        <h2 class="text-2xl font-black tracking-tight">{{ $dailyTimeRecord->dtr_date->format('l, F d, Y') }}</h2>
                    </div>
                    @php
                        $statusColors = [
                            'draft' => 'bg-gray-500/20 text-gray-300 border-gray-500/30',
                            'pending' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                            'approved' => 'bg-green-500/20 text-green-300 border-green-500/30',
                            'rejected' => 'bg-red-500/20 text-red-300 border-red-500/30',
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $statusColors[$dailyTimeRecord->status] ?? 'bg-gray-500/20 text-gray-300 border-gray-500/30' }}">
                        {{ $dailyTimeRecord->status }}
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Logged In</p>
                        <p class="text-lg font-black">{{ $dailyTimeRecord->time_in ?? '--:--' }}</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Logged Out</p>
                        <p class="text-lg font-black">{{ $dailyTimeRecord->time_out ?? '--:--' }}</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Hours</p>
                        <p class="text-lg font-black">{{ number_format($dailyTimeRecord->total_hours_worked, 2) }}</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Exceptions</p>
                        <p class="text-lg font-black">{{ $dailyTimeRecord->late_minutes + $dailyTimeRecord->undertime_minutes }}<span class="text-xs font-normal ml-1">m</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Correction Form Card -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-50 flex items-center bg-gray-50/50">
                <div class="h-10 w-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 tracking-tight uppercase tracking-widest">Correction Particulars</h3>
            </div>

            <form action="{{ route('dtr-records.request-correction', $dailyTimeRecord) }}" method="POST" class="p-8">
                @csrf

                <div class="mb-8 p-4 bg-blue-50 border border-blue-100 rounded-2xl flex items-start">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-blue-800 font-medium">
                        Only provide values for the fields that require adjustment. Fields left blank will remain as they are currently logged.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Proposed Time In</label>
                        <div class="relative group">
                            <input type="time" name="time_in" class="w-full px-4 py-4 bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-gray-900 @error('time_in') border-red-500 bg-red-50 @enderror" value="{{ old('time_in') }}">
                        </div>
                        <p class="text-[10px] text-gray-400 font-bold ml-1 italic italic">Current logged: {{ $dailyTimeRecord->time_in ?? 'Not set' }}</p>
                        @error('time_in') <p class="text-[10px] font-bold text-red-500 ml-1 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Proposed Time Out</label>
                        <div class="relative group">
                            <input type="time" name="time_out" class="w-full px-4 py-4 bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-gray-900 @error('time_out') border-red-500 bg-red-50 @enderror" value="{{ old('time_out') }}">
                        </div>
                        <p class="text-[10px] text-gray-400 font-bold ml-1 italic italic">Current logged: {{ $dailyTimeRecord->time_out ?? 'Not set' }}</p>
                        @error('time_out') <p class="text-[10px] font-bold text-red-500 ml-1 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-10 space-y-2">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Justification for Correction <span class="text-red-500">*</span></label>
                    <textarea name="reason" rows="5" class="w-full px-4 py-4 bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 font-medium text-gray-700 @error('reason') border-red-500 bg-red-50 @enderror" placeholder="Provide a detailed explanation for HR review (e.g. system malfunction, forgot to clock in, official business, etc.)" required>{{ old('reason') }}</textarea>
                    @error('reason') <p class="text-[10px] font-bold text-red-500 ml-1 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-10 p-4 bg-amber-50 border border-amber-100 rounded-2xl flex items-start">
                    <svg class="w-5 h-5 text-amber-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p class="text-xs text-amber-800 font-bold uppercase tracking-tight">
                        IMPORTANT: Correction requests are subject to approval by HR. You will be notified of the outcome via the notification center.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row justify-end items-center gap-4">
                    <button type="submit" class="w-full sm:w-auto px-12 py-4 rounded-2xl bg-indigo-600 text-white font-black hover:bg-indigo-700 shadow-xl shadow-indigo-200 transition-all uppercase tracking-widest text-sm flex items-center justify-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Transmit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
