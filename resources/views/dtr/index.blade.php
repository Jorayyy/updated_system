@php
    $startDate = \Carbon\Carbon::create($year, $month, 1);
    $endDate = $startDate->copy()->endOfMonth();
    $attendanceByDate = $attendances->keyBy(fn($a) => $a->date->format('Y-m-d'));
    $m = $summary['metrics'] ?? null;
@endphp

<x-app-layout>
    <div class="py-4 bg-slate-100 min-h-screen">
        <div class="max-w-[1500px] mx-auto sm:px-4 lg:px-6">
            <!-- Breadcrumb style title -->
            <div class="mb-4 bg-white p-3 border border-slate-200 rounded text-sm font-bold text-slate-600 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button onclick="window.history.back()" class="text-slate-400 hover:text-slate-600 transition-colors mr-1" title="Go Back">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </button>
                    <div>View DTR</div>
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('concerns.user-create', ['category' => 'timekeeping', 'title' => 'DTR Discrepancy - ' . $startDate->format('F Y')]) }}" 
                        class="text-[10px] bg-red-50 text-red-700 px-3 py-1 rounded border border-red-200 hover:bg-red-100 transition-colors">
                        Report TK Complaint
                    </a>
                </div>
            </div>

            <!-- Payroll Period Selection -->
            <div class="bg-white p-4 border border-slate-200 rounded mb-4 shadow-sm flex flex-col gap-4">
                <form method="GET" class="flex flex-wrap items-center gap-6">
                    <div class="flex items-center gap-3">
                        <label class="text-[11px] font-black text-slate-800 uppercase tracking-tighter">Payroll Period</label>
                        <select name="month" class="bg-white border text-[11px] p-2 rounded min-w-[200px] focus:ring-0 focus:border-slate-400">
                             @for($mo = 1; $mo <= 12; $mo++)
                                <option value="{{ $mo }}" {{ $month == $mo ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $mo, 1)) }}
                                </option>
                            @endfor
                        </select>
                        <select name="year" class="bg-white border text-[11px] p-2 rounded w-24 focus:ring-0 focus:border-slate-400">
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded text-[11px] font-bold">Refresh View</button>
                    </div>
                </form>
                <div>
                    <a href="{{ route('dtr.pdf', ['month' => $month, 'year' => $year]) }}" 
                       class="bg-rose-600 text-white px-8 py-2 rounded text-[11px] font-bold shadow-sm inline-block">Print</a>
                </div>
            </div>

            <!-- Header Section -->
            <div class="mb-6 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center bg-gradient-to-r from-indigo-700 to-indigo-600">
                    <div>
                        <h1 class="text-white text-xl font-bold tracking-tight uppercase">{{ $user->name }}</h1>
                        <p class="text-indigo-100 text-xs font-semibold uppercase tracking-wider">{{ $user->position ?? 'STAFF' }} • {{ $user->employee_id }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-indigo-100 text-[10px] uppercase font-black">Payroll Period</p>
                        <p class="text-white text-sm font-bold">{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-slate-100 p-4 bg-white">
                    <div class="px-4 py-2">
                        <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Department</span>
                        <span class="text-xs font-bold text-slate-700 uppercase">{{ $user->department ?? 'N/A' }}</span>
                    </div>
                    <div class="px-4 py-2">
                        <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Employment</span>
                        <span class="text-xs font-bold text-slate-700 uppercase">{{ $user->employment_type ?? 'Contractual' }}</span>
                    </div>
                    <div class="px-4 py-2">
                        <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Pay Type</span>
                        <span class="text-xs font-bold text-slate-700 uppercase">{{ $user->pay_type ?? 'Weekly' }}</span>
                    </div>
                    <div class="px-4 py-2 border-r-0">
                        <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Classification</span>
                        <span class="text-xs font-bold text-slate-700 uppercase">{{ $user->classification ?? 'STAFF' }}</span>
                    </div>
                </div>
            </div>

            <!-- Main DTR Table -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-4 py-3 text-left font-bold text-slate-700 border-r border-slate-100 w-24">DATE</th>
                                <th class="px-4 py-3 text-center font-bold text-slate-700 border-r border-slate-100">SCHEDULE</th>
                                <th class="px-4 py-3 text-center font-bold text-indigo-700 border-r border-slate-100 bg-indigo-50/30">TIME IN</th>
                                <th class="px-4 py-3 text-center font-bold text-indigo-700 border-r border-slate-100 bg-indigo-50/30">TIME OUT</th>
                                <th class="px-4 py-3 text-center font-bold text-emerald-700 border-r border-slate-100 bg-emerald-50/30">WORK HRS</th>
                                <th class="px-4 py-3 text-center font-bold text-rose-700 border-r border-slate-100 bg-rose-50/30">LATE/UT</th>
                                <th class="px-4 py-3 text-center font-bold text-slate-700">STATUS / REMARKS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @for($date = $startDate->copy(); $date <= $endDate; $date->addDay())
                                @php
                                    $attendance = $attendanceByDate->get($date->format('Y-m-d'));
                                    $shift = $user->getShiftForDate($date);
                                    $isRestDay = $shift['is_rest_day'];
                                    
                                    $status = '';
                                    if ($isRestDay) {
                                        $status = 'Rest Day';
                                    } elseif ($attendance && $attendance->status == 'on_leave') {
                                        $status = 'On Leave';
                                    } elseif (!$date->isFuture() && !$date->isToday()) {
                                        if (!$attendance || !$attendance->time_in) {
                                            $status = 'Absent';
                                        }
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors {{ $date->isToday() ? 'bg-amber-50/30' : '' }}">
                                    <td class="px-4 py-2.5 border-r border-slate-100 font-medium whitespace-nowrap">
                                        <span class="{{ $date->isWeekend() ? 'text-slate-400' : 'text-slate-700' }}">{{ $date->format('M d') }}</span>
                                        <span class="text-[10px] uppercase ml-1 {{ $date->isWeekend() ? 'text-slate-300' : 'text-slate-500' }}">{{ $date->format('D') }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 border-r border-slate-100 text-center text-slate-500 font-medium">
                                        {{ !$isRestDay ? $shift['in'] . ' - ' . $shift['out'] : '' }}
                                    </td>
                                    <td class="px-4 py-2.5 border-r border-slate-100 text-center font-bold text-slate-900 bg-indigo-50/10">
                                        {{ $attendance && $attendance->time_in ? $attendance->time_in->format('h:i A') : '--' }}
                                    </td>
                                    <td class="px-4 py-2.5 border-r border-slate-100 text-center font-bold text-slate-900 bg-indigo-50/10">
                                        {{ $attendance && $attendance->time_out ? $attendance->time_out->format('h:i A') : '--' }}
                                    </td>
                                    <td class="px-4 py-2.5 border-r border-slate-100 text-center font-black text-slate-800 bg-emerald-50/10">
                                        @if($attendance && $attendance->total_work_minutes > 0)
                                            {{ number_format($attendance->total_work_minutes/60, 2) }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 border-r border-slate-100 text-center font-medium bg-rose-50/10">
                                        @php
                                            $late = $attendance ? $attendance->late_minutes : 0;
                                            $ut = $attendance ? $attendance->undertime_minutes : 0;
                                            $totalLost = $late + $ut;
                                        @endphp
                                        @if($totalLost > 0)
                                            <span class="text-rose-600">{{ $totalLost }}m</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if($status == 'Absent')
                                            <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-700 text-[10px] font-bold uppercase tracking-wider">Absent</span>
                                        @elseif($status == 'On Leave')
                                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-wider">Leave</span>
                                        @elseif($status == 'Rest Day')
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Rest Day</span>
                                        @endif
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Section -->
            <div class="mb-12">
                <h2 class="text-sm font-black text-slate-800 uppercase tracking-tighter mb-4 flex items-center gap-2">
                    <span class="w-2 h-4 bg-indigo-600 rounded-full"></span>
                    Attendance Summary
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Work Hours Card -->
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Total Worked Hours</span>
                        <div class="flex items-end justify-between">
                            <span class="text-2xl font-black text-slate-900 leading-none">{{ number_format($m['regular']['worked'] ?? 0, 2) }}</span>
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">REGULAR</span>
                        </div>
                    </div>

                    <!-- OT Card -->
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Overtime Hours</span>
                        <div class="flex items-end justify-between">
                            <span class="text-2xl font-black text-slate-900 leading-none">{{ number_format(($m['regular']['ot'] ?? 0) + ($m['restday']['ot'] ?? 0), 2) }}</span>
                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">TOTAL OT</span>
                        </div>
                    </div>

                    <!-- Tardiness Card -->
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Late & Undertime</span>
                        <div class="flex items-end justify-between">
                            <span class="text-2xl font-black text-rose-600 leading-none">{{ number_format(($m['counts']['tardiness'] ?? 0) + ($m['counts']['undertime'] ?? 0), 0) }}m</span>
                            <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded">DEDUCTIONS</span>
                        </div>
                    </div>

                    <!-- Absenteeism Card -->
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Absences</span>
                        <div class="flex items-end justify-between">
                            <span class="text-2xl font-black text-slate-900 leading-none">{{ $m['counts']['absences'] ?? 0 }}</span>
                            <span class="text-[10px] font-bold text-slate-500 bg-slate-50 px-2 py-0.5 rounded">DAYS</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('dtr.pdf', ['month' => $month, 'year' => $year]) }}" 
                       class="bg-slate-900 hover:bg-black text-white px-8 py-3 rounded-lg shadow-lg text-xs font-bold uppercase flex items-center gap-3 transition-all hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Print Professional DTR
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
