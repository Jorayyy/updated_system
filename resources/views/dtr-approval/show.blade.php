@extends('layouts.app')

@section('title', 'DTR Details - ' . ($dailyTimeRecord->user->name ?? 'User'))

@section('content')
<div class="container mx-auto px-4 py-8" x-data="{ 
    showRejectModal: false, 
    showRejectCorrectionModal: false,
    rejectReason: '',
    rejectCorrectionReason: '',
    submitRejection() {
        if (!this.rejectReason) {
            alert('Please provide a reason for rejection');
            return;
        }
        
        const data = { reason: this.rejectReason };
        this.performAjax('{{ route('dtr-approval.reject', $dailyTimeRecord) }}', data);
    },
    submitRejectCorrection() {
        if (!this.rejectCorrectionReason) {
            alert('Please provide a reason for rejecting the correction');
            return;
        }
        
        const data = { reason: this.rejectCorrectionReason };
        this.performAjax('{{ route('dtr-approval.reject-correction', $dailyTimeRecord) }}', data);
    },
    async performAjax(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Action failed');
            }
        } catch (e) {
            alert('An error occurred');
        }
    }
}">
    <!-- Header/Breadcrumb Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm font-medium text-gray-500">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dtr-approval.index') }}" class="hover:text-indigo-600 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                            DTR Approval
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <span class="ml-1 text-gray-400 md:ml-2">Details</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center">
                <svg class="w-8 h-8 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                DTR Details
            </h1>
        </div>

        <div class="flex flex-wrap gap-2">
            @if(in_array(auth()->user()->role, ['admin', 'hr']))
                @if(in_array($dailyTimeRecord->status, ['draft', 'pending']))
                    <button type="button" onclick="approveDtr()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all shadow-green-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Approve
                    </button>
                    <button type="button" @click="showRejectModal = true" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all shadow-red-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reject
                    </button>
                @endif
                @if($dailyTimeRecord->status === 'correction_pending')
                    <button type="button" onclick="approveCorrection()" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all shadow-indigo-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Approve Correction
                    </button>
                    <button type="button" @click="showRejectCorrectionModal = true" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all shadow-amber-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reject Correction
                    </button>
                @endif
                <a href="{{ route('dtr-approval.edit', $dailyTimeRecord) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-lg transition-colors border border-gray-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Modify
                </a>
            @endif
            <a href="javascript:window.history.back()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-bold rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Time Record Card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center tracking-tight">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Shift Details
                    </h2>
                    @php
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-700 border-gray-200',
                            'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                            'approved' => 'bg-green-100 text-green-700 border-green-200',
                            'rejected' => 'bg-red-100 text-red-700 border-red-200',
                            'correction_pending' => 'bg-blue-100 text-blue-700 border-blue-200',
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $statusColors[$dailyTimeRecord->status] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                        {{ str_replace('_', ' ', $dailyTimeRecord->status) }}
                    </span>
                </div>

                <div class="p-8">
                    <!-- Employee & Date Branding -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                        <div class="flex items-center">
                            <div class="h-14 w-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 mr-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Employee</p>
                                <h3 class="text-xl font-extrabold text-gray-900">{{ $dailyTimeRecord->user->name ?? 'N/A' }}</h3>
                                <p class="text-sm text-gray-500 font-medium">{{ $dailyTimeRecord->user->employee_id ?? 'No ID' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center md:justify-end">
                            <div class="text-right">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Work Date</p>
                                <h3 class="text-xl font-extrabold text-gray-900">{{ $dailyTimeRecord->dtr_date->format('l, F d, Y') }}</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $dailyTimeRecord->day_type === 'regular' ? 'bg-gray-100 text-gray-600' : 'bg-amber-100 text-amber-700' }} mt-1 capitalize">
                                    {{ str_replace('_', ' ', $dailyTimeRecord->day_type) }} Day
                                </span>
                            </div>
                            <div class="h-14 w-14 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 ml-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Core Time Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                        <div class="bg-green-50/50 rounded-2xl p-4 border border-green-100 text-center">
                            <svg class="w-6 h-6 mx-auto mb-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            <p class="text-[10px] font-bold text-green-600 uppercase tracking-wider mb-1">Time In</p>
                            <p class="text-lg font-black text-green-900 tracking-tight">{{ $dailyTimeRecord->time_in ?? '--:--' }}</p>
                        </div>
                        <div class="bg-red-50/50 rounded-2xl p-4 border border-red-100 text-center relative overflow-hidden">
                            @if($dailyTimeRecord->has_auto_timeout)
                                <div class="absolute top-0 right-0 px-2 py-0.5 bg-red-600 text-[8px] text-white font-black uppercase">Auto</div>
                            @endif
                            <svg class="w-6 h-6 mx-auto mb-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            <p class="text-[10px] font-bold text-red-600 uppercase tracking-wider mb-1">Time Out</p>
                            <p class="text-lg font-black text-red-900 tracking-tight">{{ $dailyTimeRecord->time_out ?? '--:--' }}</p>
                        </div>
                        <div class="bg-blue-50/50 rounded-2xl p-4 border border-blue-100 text-center">
                            <svg class="w-6 h-6 mx-auto mb-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-1">Break</p>
                            <p class="text-lg font-black text-blue-900 tracking-tight">{{ $dailyTimeRecord->total_break_minutes ?? 0 }}<span class="text-xs font-normal ml-0.5">m</span></p>
                        </div>
                        <div class="bg-indigo-50/50 rounded-2xl p-4 border border-indigo-100 text-center">
                            <svg class="w-6 h-6 mx-auto mb-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider mb-1">Work Hours</p>
                            <p class="text-lg font-black text-indigo-900 tracking-tight">{{ number_format($dailyTimeRecord->total_hours_worked, 2) }}<span class="text-xs font-normal ml-0.5">h</span></p>
                        </div>
                    </div>

                    <!-- Metrics Blocks -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-6 rounded-2xl border {{ $dailyTimeRecord->late_minutes > 0 ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-100' }} transition-colors">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Late Arrival</h4>
                            <div class="flex items-baseline">
                                <span class="text-3xl font-black {{ $dailyTimeRecord->late_minutes > 0 ? 'text-red-700' : 'text-gray-900' }}">{{ $dailyTimeRecord->late_minutes ?? 0 }}</span>
                                <span class="ml-1 text-sm font-bold text-gray-500 uppercase">Minutes</span>
                            </div>
                        </div>
                        <div class="p-6 rounded-2xl border {{ $dailyTimeRecord->undertime_minutes > 0 ? 'bg-amber-50 border-amber-100' : 'bg-gray-50 border-gray-100' }} transition-colors">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Undertime</h4>
                            <div class="flex items-baseline">
                                <span class="text-3xl font-black {{ $dailyTimeRecord->undertime_minutes > 0 ? 'text-amber-700' : 'text-gray-900' }}">{{ $dailyTimeRecord->undertime_minutes ?? 0 }}</span>
                                <span class="ml-1 text-sm font-bold text-gray-500 uppercase">Minutes</span>
                            </div>
                        </div>
                        <div class="p-6 rounded-2xl border {{ $dailyTimeRecord->overtime_minutes > 0 ? 'bg-blue-50 border-blue-100' : 'bg-gray-50 border-gray-100' }} transition-colors">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Overtime</h4>
                            <div class="flex items-baseline">
                                <span class="text-3xl font-black {{ $dailyTimeRecord->overtime_minutes > 0 ? 'text-blue-700' : 'text-gray-900' }}">{{ $dailyTimeRecord->overtime_minutes ?? 0 }}</span>
                                <span class="ml-1 text-sm font-bold text-gray-500 uppercase">Minutes</span>
                            </div>
                        </div>
                    </div>

                    @if($dailyTimeRecord->remarks)
                        <div class="mt-8 p-6 bg-yellow-50 rounded-2xl border border-yellow-100">
                            <h4 class="text-xs font-bold text-yellow-700 uppercase tracking-widest mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                                Employee Remarks
                            </h4>
                            <p class="text-gray-800 leading-relaxed font-medium italic">"{{ $dailyTimeRecord->remarks }}"</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Correction Request Banner -->
            @if($dailyTimeRecord->correction_requested && $dailyTimeRecord->correction_data)
                <div class="bg-white rounded-2xl border-2 border-indigo-200 shadow-md overflow-hidden animate-pulse-subtle">
                    <div class="bg-indigo-600 px-6 py-3 flex items-center justify-between">
                        <h2 class="text-sm font-black text-white uppercase tracking-[0.2em] flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Correction Requested
                        </h2>
                        <span class="bg-indigo-500 text-indigo-50 text-[10px] px-2 py-0.5 rounded font-black">{{ $dailyTimeRecord->correction_requested_at?->diffForHumans() }}</span>
                    </div>
                    <div class="p-6">
                        @php $correctionData = json_decode($dailyTimeRecord->correction_data, true); @endphp
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            @foreach($correctionData as $field => $value)
                                <div class="bg-indigo-50/50 p-3 rounded-xl border border-indigo-100">
                                    <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-1">{{ str_replace('_', ' ', $field) }}</p>
                                    <p class="text-sm font-extrabold text-indigo-900 uppercase">{{ $value }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Reason for correction</p>
                            <p class="text-gray-700 italic">"{{ $dailyTimeRecord->correction_reason }}"</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            <!-- Summary Info -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest">General Status</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 font-medium">Attendance</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $dailyTimeRecord->attendance_status === 'present' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' }}">
                            {{ $dailyTimeRecord->attendance_status ?? 'Unknown' }}
                        </span>
                    </div>
                    @if($dailyTimeRecord->payrollPeriod)
                        <div class="flex flex-col pt-3 border-t border-gray-50">
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Payroll Cycle</span>
                            <span class="text-sm text-gray-900 font-bold">
                                {{ $dailyTimeRecord->payrollPeriod->start_date->format('M d') }} - 
                                {{ $dailyTimeRecord->payrollPeriod->end_date->format('M d, Y') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approval Metadata -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest">Audit Trail</h3>
                </div>
                <div class="p-6">
                    @if($dailyTimeRecord->approved_at)
                        <div class="relative pl-6 border-l-2 border-green-500 pb-2">
                            <div class="absolute -left-[9px] top-0 h-4 w-4 rounded-full bg-green-500 border-2 border-white"></div>
                            <p class="text-xs font-bold text-green-600 uppercase tracking-wider mb-0.5">Approved</p>
                            <p class="text-sm text-gray-900 font-bold mb-1">{{ $dailyTimeRecord->approvedByUser->name ?? 'System' }}</p>
                            <p class="text-[10px] text-gray-400 font-medium tracking-tight">{{ $dailyTimeRecord->approved_at->format('M d, Y @ H:i') }}</p>
                        </div>
                    @elseif($dailyTimeRecord->rejected_at)
                        <div class="relative pl-6 border-l-2 border-red-500">
                            <div class="absolute -left-[9px] top-0 h-4 w-4 rounded-full bg-red-500 border-2 border-white"></div>
                            <p class="text-xs font-bold text-red-600 uppercase tracking-wider mb-0.5">Rejected</p>
                            <p class="text-sm text-gray-900 font-bold mb-1">{{ $dailyTimeRecord->rejectedByUser->name ?? 'Admin' }}</p>
                            <p class="text-[10px] text-gray-400 font-medium tracking-tight mb-3">{{ $dailyTimeRecord->rejected_at->format('M d, Y @ H:i') }}</p>
                            <div class="bg-red-50 p-2 rounded-lg border border-red-100">
                                <p class="text-[10px] font-bold text-red-700 uppercase mb-1">Reason</p>
                                <p class="text-xs text-red-800 italic">"{{ $dailyTimeRecord->rejection_reason }}"</p>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-6 text-gray-400">
                            <svg class="w-10 h-10 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-xs font-bold uppercase tracking-widest">Awaiting Review</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Record Info -->
            <div class="bg-gray-900 rounded-2xl p-6 text-white shadow-lg">
                <h3 class="text-xs font-black text-indigo-400 uppercase tracking-[0.2em] mb-4">Record Identity</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-400 font-bold uppercase">System ID</span>
                        <span class="font-mono text-gray-300">#{{ str_pad($dailyTimeRecord->id, 8, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-400 font-bold uppercase">Generated</span>
                        <span class="text-gray-300">{{ $dailyTimeRecord->created_at->format('m/d/y H:i') }}</span>
                    </div>
                    @if($dailyTimeRecord->attendance_id)
                        <div class="flex justify-between items-center text-xs pt-2 border-t border-gray-800 mt-2">
                            <span class="text-gray-400 font-bold uppercase">Source Logs</span>
                            <a href="{{ route('attendance.show', $dailyTimeRecord->attendance_id) }}" class="text-indigo-400 hover:text-indigo-300 font-bold underline transition-colors decoration-indigo-800 underline-offset-4">
                                Linked View
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->

<!-- Reject DTR Modal -->
<div x-cloak x-show="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showRejectModal = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-white px-8 pt-8 pb-6">
                <div class="sm:flex sm:items-start text-center sm:text-left">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10 text-red-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 sm:mt-0 sm:ml-4">
                        <h3 class="text-xl font-black text-gray-900 tracking-tight" id="modal-title uppercase">Reject Attendance Record</h3>
                        <p class="text-sm text-gray-500 mt-1">Please provide a valid reason for declining this DTR record. This will be visible to the employee.</p>
                    </div>
                </div>
                <div class="mt-8 space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-600 uppercase mb-2 tracking-[0.1em]">Internal Rejection Reason <span class="text-red-500 ml-1">REQUIRED</span></label>
                        <textarea x-model="rejectReason" rows="4" class="w-full rounded-2xl border-gray-200 focus:ring-red-500 focus:border-red-500 placeholder-gray-400 font-medium text-sm transition-all shadow-sm" required placeholder="Detailed reason for rejection..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showRejectModal = false; rejectReason = ''" class="px-6 py-3 rounded-2xl border border-gray-200 bg-white text-gray-700 font-bold hover:bg-gray-50 transition-colors uppercase tracking-widest text-xs">Cancel</button>
                        <button type="button" @click="submitRejection()" class="px-6 py-3 rounded-2xl bg-red-600 text-white font-black hover:bg-red-700 shadow-lg shadow-red-200 transition-all uppercase tracking-widest text-xs">Confirm Rejection</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Correction Modal -->
<div x-cloak x-show="showRejectCorrectionModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showRejectCorrectionModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showRejectCorrectionModal = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="showRejectCorrectionModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-white px-8 pt-8 pb-6">
                <div class="sm:flex sm:items-start text-center sm:text-left">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10 text-amber-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 sm:mt-0 sm:ml-4">
                        <h3 class="text-xl font-black text-gray-900 tracking-tight uppercase" id="modal-title">Deny Correction Request</h3>
                        <p class="text-sm text-gray-500 mt-1">Provide reasoning for why these specific changes are being rejected.</p>
                    </div>
                </div>
                <div class="mt-8 space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-600 uppercase mb-2 tracking-[0.1em]">Rejection Reason <span class="text-amber-600 ml-1">REQUIRED</span></label>
                        <textarea x-model="rejectCorrectionReason" rows="4" class="w-full rounded-2xl border-gray-200 focus:ring-amber-500 focus:border-amber-500 placeholder-gray-400 font-medium text-sm transition-all shadow-sm" required placeholder="Explain why the correction cannot be approved..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showRejectCorrectionModal = false; rejectCorrectionReason = ''" class="px-6 py-3 rounded-2xl border border-gray-200 bg-white text-gray-700 font-bold hover:bg-gray-50 transition-colors uppercase tracking-widest text-xs">Cancel</button>
                        <button type="button" @click="submitRejectCorrection()" class="px-6 py-3 rounded-2xl bg-amber-600 text-white font-black hover:bg-amber-700 shadow-lg shadow-amber-200 transition-all uppercase tracking-widest text-xs">Reject Correction</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<style>
    @keyframes pulse-subtle {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.005); }
    }
    .animate-pulse-subtle {
        animation: pulse-subtle 4s infinite ease-in-out;
    }
</style>
<script>
function approveDtr() {
    if (confirm('Approve this attendance record? This will finalize the record for payroll.')) {
        fetch('{{ route("dtr-approval.approve", $dailyTimeRecord) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

function approveCorrection() {
    if (confirm('Apply these corrections to the attendance record?')) {
        fetch('{{ route("dtr-approval.approve-correction", $dailyTimeRecord) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}
</script>
@endpush
