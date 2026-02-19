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
                <div>View DTR</div>
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

            <!-- Header Info: High Contrast Reconstruction -->
            <div class="mb-6 overflow-hidden rounded-sm border-2 border-slate-800 shadow-md">
                <div class="bg-slate-900 p-2.5 flex justify-between items-center border-b border-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="bg-slate-700 px-2 py-0.5 rounded text-[10px] font-bold text-white border border-slate-600 uppercase">count</span>
                        <span class="bg-white text-slate-900 w-5 h-5 flex items-center justify-center rounded-full text-[10px] font-black shadow-inner">1</span>
                    </div>
                    <div class="flex gap-10 text-[10px] font-black uppercase tracking-tight pr-4">
                        <div class="flex gap-2 text-white">
                            <span class="text-slate-400">Salary Rate:</span>
                            <span class="font-bold">--</span>
                        </div>
                        <div class="flex gap-2 text-white">
                            <span class="text-slate-400 tracking-tighter">Date Employed:</span>
                            <span class="font-bold">{{ $user->date_hired ?? $user->created_at->format('Y-m-d') }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-slate-800 text-white divide-y divide-slate-700 text-[10px] font-bold uppercase">
                    <!-- Row 1 -->
                    <div class="grid grid-cols-4 divide-x divide-slate-700">
                        <div class="p-2.5 flex justify-between px-4">
                            <span class="text-slate-400">Payroll Period</span>
                            <span class="text-white font-black truncate">{{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</span>
                        </div>
                        <div class="p-2.5 opacity-0">...</div>
                        <div class="p-2.5 flex justify-between px-4">
                            <span class="text-slate-400">Employment</span>
                            <span class="text-white font-black">{{ $user->employment_type ?? 'Contractual' }}</span>
                        </div>
                        <div class="p-2.5 flex justify-between px-4">
                            <span class="text-slate-400">Classification</span>
                            <span class="text-white font-black">{{ $user->classification ?? 'STAFF' }}</span>
                        </div>
                    </div>
                    <!-- Row 2 -->
                    <div class="grid grid-cols-4 divide-x divide-slate-700">
                        <div class="p-2.5 flex justify-between px-4">
                            <span class="text-slate-400">Employee ID</span>
                            <span class="text-cyan-400 font-black select-all tracking-wider">{{ $user->employee_id }}</span>
                        </div>
                        <div class="p-2.5 flex justify-between px-4">
                            <span class="text-slate-400">Department</span>
                            <span class="text-white font-black uppercase">{{ $user->department ?? 'BPO OPERATIONS' }}</span>
                        </div>
                        <div class="p-2.5 opacity-0">...</div>
                        <div class="p-2.5 flex justify-between px-4">
                            <span class="text-slate-400">Pay Type</span>
                            <span class="text-white font-black uppercase text-amber-400">{{ $user->pay_type ?? 'Weekly' }}</span>
                        </div>
                    </div>
                    <!-- Row 3 -->
                    <div class="grid grid-cols-4 divide-x divide-slate-700">
                        <div class="p-2.5 flex justify-between px-4">
                            <span class="text-slate-400">Name</span>
                            <span class="text-white font-black uppercase whitespace-nowrap">{{ $user->name }}</span>
                        </div>
                        <div class="p-2.5 flex justify-between px-4">
                            <span class="text-slate-400 font-black">Section</span>
                            <span class="text-white font-black uppercase">{{ $user->section ?? 'TACLOBAN ADMIN' }}</span>
                        </div>
                        <div class="p-2.5 opacity-0">...</div>
                        <div class="p-2.5 flex justify-between px-4">
                            <span class="text-slate-400 font-black">Location</span>
                            <span class="text-white font-black uppercase text-[9px]">{{ $user->location ?? 'TACLOBAN MAIN OFFICE' }}</span>
                        </div>
                    </div>
                    <!-- Row 4 -->
                    <div class="grid grid-cols-4 divide-x divide-slate-700">
                        <div class="p-2.5 flex justify-between px-4 border-b border-slate-700">
                            <span class="text-slate-400">Position</span>
                            <span class="text-white font-black uppercase italic">{{ $user->position ?? 'CUSTOMER SUPPORT' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Big Main Table -->
            <div class="bg-white border border-slate-300 rounded shadow-md overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-[10px] border-collapse min-w-[1500px]">
                        <!-- Nested Headers for Complex Layout -->
                        <thead class="bg-slate-900 text-white sticky top-0 z-20">
                            <tr class="h-10">
                                <th colspan="2" class="border-r border-slate-700 py-2.5 text-center px-4">DATE INFORMATION</th>
                                <th colspan="2" class="border-r border-slate-700 bg-pink-600 text-white shadow-inner uppercase tracking-widest">Shift Schedule</th>
                                <th colspan="2" class="border-r border-slate-700 bg-emerald-700 text-white shadow-inner uppercase tracking-widest">Actual Attendance</th>
                                <th colspan="5" class="border-r border-slate-700 text-slate-300">CALCULATED HOURS</th>
                                <th colspan="2" class="border-r border-slate-700 text-slate-300">OVERTIME</th>
                                <th colspan="2" class="border-r border-slate-700 text-slate-300 border-b border-slate-700">HOLIDAY</th>
                                <th colspan="2" class="border-r border-slate-700 text-slate-300">RESTDAY</th>
                                <th rowspan="2" class="border-r border-slate-700 border-b border-slate-700">ND</th>
                                <th rowspan="2" class="border-r border-slate-700 border-b border-slate-700 bg-slate-800">ATRO</th>
                                <th colspan="6" class="bg-slate-800 border-b border-slate-700">REQUESTED / FILED FORMS</th>
                            </tr>
                            <tr class="bg-slate-700 text-[8px] uppercase font-black tracking-tight border-t border-slate-600 h-8">
                                <th class="border-r border-slate-600 py-1.5 w-12 text-slate-300">{{ $startDate->format('M') }}</th>
                                <th class="border-r border-slate-600 w-10 text-center text-slate-300">Day</th>
                                <th class="border-r border-slate-600 bg-pink-500 w-20 text-center text-white italic">IN</th>
                                <th class="border-r border-slate-600 bg-pink-500 w-20 text-center text-white italic">OUT</th>
                                <th class="border-r border-slate-600 bg-emerald-600 w-20 text-center text-white">IN</th>
                                <th class="border-r border-slate-600 bg-emerald-600 w-20 text-center text-white">OUT</th>
                                <th class="border-r border-slate-600 w-12 text-rose-300">Late</th>
                                <th class="border-r border-slate-600 w-12 text-[7px] leading-3 uppercase px-1 text-orange-300">Over break</th>
                                <th class="border-r border-slate-600 w-12 text-red-300">Undertime</th>
                                <th class="border-r border-slate-600 w-12 text-blue-300 font-bold">REGULAR</th>
                                <th class="border-r border-slate-600 w-12">ND</th>
                                <th class="border-r border-slate-600 w-10 text-xs">Reg</th>
                                <th class="border-r border-slate-600 w-10 text-xs">RD</th>
                                <th class="border-r border-slate-600 w-10 text-yellow-300">PH-Reg</th>
                                <th class="border-r border-slate-600 w-10">PH-RD</th>
                                <th class="border-r border-slate-600 w-10 text-indigo-300">SH-Reg</th>
                                <th class="border-r border-slate-600 w-10">SH-RD</th>
                                <th class="border-r border-slate-600 w-14 leading-3 uppercase px-1 text-teal-300">COS / ADJ</th>
                                <th class="border-r border-slate-600 w-10 text-blue-300">Leave</th>
                                <th class="border-r border-slate-600 w-12 leading-3">OB</th>
                                <th class="border-r border-slate-600 w-14 leading-3 uppercase px-1">TK Form</th>
                                <th class="border-r border-slate-600 w-12">UT Form</th>
                                <th class="w-10">OTH</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @for($date = $startDate->copy(); $date <= $endDate; $date->addDay())
                                @php
                                    $attendance = $attendanceByDate->get($date->format('Y-m-d'));
                                    $shift = $user->getShiftForDate($date);
                                    $isRestDay = $shift['is_rest_day'];
                                @endphp
                                <tr class="border-b border-slate-100 hover:bg-slate-50 h-8 font-medium text-slate-700">
                                    <td class="px-2 border-r border-slate-100 text-slate-500 text-center">{{ $date->format('m-d') }}</td>
                                    <td class="px-2 border-r border-slate-100 text-slate-500 text-center">{{ $date->format('D') }}</td>
                                    <td class="px-2 border-r border-slate-100 bg-pink-100/50 text-pink-900 font-black text-center italic">{{ !$isRestDay ? $shift['in'] : '' }}</td>
                                    <td class="px-2 border-r border-slate-100 bg-pink-100/50 text-pink-900 font-black text-center italic">{{ !$isRestDay ? $shift['out'] : '' }}</td>
                                    <td class="px-2 border-r border-slate-100 bg-emerald-100/50 text-emerald-900 font-black text-center">{{ $attendance && $attendance->time_in ? $attendance->time_in->format('H:i') : '--:--' }}</td>
                                    <td class="px-2 border-r border-slate-100 bg-emerald-100/50 text-emerald-900 font-black text-center">{{ $attendance && $attendance->time_out ? $attendance->time_out->format('H:i') : '--:--' }}</td>
                                    
                                    <td class="px-1 border-r border-slate-100 text-blue-800 font-bold text-center">
                                        {{ $attendance && $attendance->late_minutes > 0 ? number_format($attendance->late_minutes/60, 2) : '' }}
                                    </td>
                                    <td class="px-1 border-r border-slate-100 text-center italic font-bold text-amber-600">
                                        {{ $attendance && $attendance->total_break_minutes > 60 ? number_format(($attendance->total_break_minutes-60)/60, 2) : '' }}
                                    </td>
                                    <td class="px-1 border-r border-slate-100 text-red-600 font-bold text-center">
                                        {{ $attendance && $attendance->undertime_minutes > 0 ? number_format($attendance->undertime_minutes/60, 2) : '' }}
                                    </td>
                                    <td class="px-1 border-r border-slate-100 text-center">
                                        @if(!$isRestDay && (!$attendance || !$attendance->time_in) && !$date->isFuture() && !$date->isToday())
                                            <span class="text-rose-600 font-black italic">absent</span>
                                        @elseif($attendance && $attendance->total_work_minutes > 0)
                                            <span class="text-blue-700 font-black">{{ round($attendance->total_work_minutes/60) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-1 border-r border-slate-100 text-center"></td>
                                    <td class="px-1 border-r border-slate-100 text-center">
                                        @if(!$isRestDay && $attendance && $attendance->time_in) <span class="text-blue-900 font-black">✓</span> @endif
                                    </td>
                                    <td class="px-1 border-r border-slate-100 text-center">
                                        @if($isRestDay) <span class="text-blue-900 font-black">✓</span> @endif
                                    </td>
                                    
                                    <td class="px-1 border-r border-slate-100 text-center whitespace-nowrap overflow-hidden"></td>
                                    <td class="px-1 border-r border-slate-100 text-center whitespace-nowrap overflow-hidden"></td>
                                    <td class="px-1 border-r border-slate-100 text-center whitespace-nowrap overflow-hidden"></td>
                                    <td class="px-1 border-r border-slate-100 text-center whitespace-nowrap overflow-hidden"></td>
                                    <td class="px-1 border-r border-slate-100 text-center whitespace-nowrap overflow-hidden"></td>
                                    <td class="px-1 border-r border-slate-100 text-center whitespace-nowrap overflow-hidden"></td>
                                    <td class="px-1 border-r border-slate-100 text-center whitespace-nowrap overflow-hidden">
                                         @if($attendance && $attendance->status == 'on_leave') 
                                            <span class="text-[8px] bg-blue-100 text-blue-800 px-1 font-black">LEAVE</span>
                                        @endif
                                    </td>
                                    <td class="px-1 border-r border-slate-100 text-center whitespace-nowrap overflow-hidden"></td>
                                    <td class="px-1 border-r border-slate-100 text-center whitespace-nowrap overflow-hidden"></td>
                                    <td class="px-1 border-r border-slate-100 text-center whitespace-nowrap overflow-hidden"></td>
                                    <td class="px-1 text-center whitespace-nowrap overflow-hidden"></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Summary Table -->
            <div class="mt-8 mb-12">
                <div class="border border-slate-200 rounded overflow-hidden bg-white shadow-xl">
                    <table class="w-full text-[10.5px] border-collapse bg-white">
                        <thead>
                            <tr class="bg-slate-900 text-white divide-x divide-white/10 uppercase tracking-tighter">
                                <th class="p-3 text-left font-black w-64">Summary Metrics</th>
                                <th class="p-3 text-center font-black w-24">Regular</th>
                                <th class="p-3 text-center font-black w-24">Restday</th>
                                <th class="p-3 text-center font-black w-24">Holiday</th>
                                <th colspan="2" class="p-1 text-center font-black bg-white/5 border-b border-white/10">Type 1 / Type 2</th>
                                <th class="p-3 text-center font-black w-24">Spec-H</th>
                                <th class="p-3 text-center font-black w-24">Spec-R</th>
                                <th class="p-3 text-left font-black w-48 pl-6 border-l border-white/20">Occurrence Tracker</th>
                                <th class="p-3 text-right font-black w-24">Value</th>
                                <th class="p-3 text-right font-black w-24">Count</th>
                            </tr>
                            <tr class="bg-slate-800 text-[9px] text-white/70 divide-x divide-white/5 uppercase italic font-bold">
                                <th class="p-1"></th>
                                <th class="p-1"></th>
                                <th class="p-1"></th>
                                <th class="p-1"></th>
                                <th class="p-1 text-center bg-white/10">Type 1</th>
                                <th class="p-1 text-center bg-white/10">Type 2</th>
                                <th class="p-1"></th>
                                <th class="p-1"></th>
                                <th class="p-1 pl-6"></th>
                                <th class="p-1"></th>
                                <th class="p-1"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-800">
                            <tr class="h-10 hover:bg-slate-50 transition-colors">
                                <td class="p-2 border-r border-slate-100 font-bold uppercase pl-3">Total Worked Hours</td>
                                <td class="p-2 border-r border-slate-100 text-center font-black text-slate-900">{{ number_format($m['regular']['worked'] ?? 0, 2) }}</td>
                                <td class="p-2 border-r border-slate-100 text-center font-bold text-slate-500">{{ number_format($m['restday']['worked'] ?? 0, 2) }}</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-400">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center italic font-bold bg-slate-50/30">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center italic font-bold bg-slate-50/30">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-400">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-400">0.00</td>
                                <td class="p-2 border-r border-slate-100 uppercase text-slate-400 font-black italic pl-6">Absenteeism</td>
                                <td class="p-2 border-r border-slate-100 text-right text-rose-600 font-black">{{ $m['counts']['absences'] ?? 0 }}</td>
                                <td class="p-2 text-right text-rose-600 font-black">{{ $m['counts']['absences_occ'] ?? 0 }}</td>
                            </tr>
                            <tr class="h-10 hover:bg-slate-50 transition-colors">
                                <td class="p-2 border-r border-slate-100 font-bold uppercase pl-3">Night Differential</td>
                                <td class="p-2 border-r border-slate-100 text-center font-black text-indigo-700">{{ number_format($m['regular']['nd'] ?? 0, 2) }}</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-400">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-400">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center italic font-bold bg-slate-50/30">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center italic font-bold bg-slate-50/30">--</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-400">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-400">0.00</td>
                                <td class="p-2 border-r border-slate-100 uppercase text-slate-400 font-black italic pl-6">Undertime</td>
                                <td class="p-2 border-r border-slate-100 text-right font-black text-slate-900">{{ number_format($m['counts']['undertime'] ?? 0, 2) }}</td>
                                <td class="p-2 text-right font-black text-slate-900">{{ $m['counts']['undertime_occ'] ?? 0 }}</td>
                            </tr>
                            <tr class="h-10 hover:bg-slate-50 transition-colors">
                                <td class="p-2 border-r border-slate-100 font-bold uppercase pl-3">Overtime (Summary)</td>
                                <td class="p-2 border-r border-slate-100 text-center font-black text-emerald-700">{{ number_format($m['regular']['ot'] ?? 0, 2) }}</td>
                                <td class="p-2 border-r border-slate-100 text-center font-black text-emerald-700">{{ number_format($m['restday']['ot'] ?? 0, 2) }}</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-400">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center italic font-bold bg-slate-50/30">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center italic font-bold bg-slate-50/30">--</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-400">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-400">0.00</td>
                                <td class="p-2 border-r border-slate-100 uppercase text-slate-400 font-black italic pl-6">Tardiness</td>
                                <td class="p-2 border-r border-slate-100 text-right font-black text-blue-700">{{ number_format($m['counts']['tardiness'] ?? 0, 2) }}</td>
                                <td class="p-2 text-right font-black text-blue-700">{{ $m['counts']['tardiness_occ'] ?? 0 }}</td>
                            </tr>
                            <tr class="h-10 hover:bg-slate-50 transition-colors">
                                <td class="p-2 border-r border-slate-100 font-bold uppercase pl-3 text-slate-500 italic">Misc Deductions</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-300">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-300">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-300">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center italic font-bold bg-slate-50/30">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center italic font-bold bg-slate-50/30">--</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-300">0.00</td>
                                <td class="p-2 border-r border-slate-100 text-center text-slate-300">0.00</td>
                                <td class="p-2 border-r border-slate-100 uppercase text-slate-400 font-black italic pl-6">Overbreak</td>
                                <td class="p-2 border-r border-slate-100 text-right font-black text-rose-800">{{ number_format($m['counts']['overbreak'] ?? 0, 2) }}</td>
                                <td class="p-2 text-right font-black text-rose-800">{{ $m['counts']['overbreak_occ'] ?? 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 flex gap-4">
                    <button class="bg-slate-900 border border-white/10 hover:bg-black text-white px-8 py-4 rounded shadow-2xl text-[11px] font-black uppercase flex items-center gap-3 transition-all transform hover:-translate-y-1 active:translate-y-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Print Professional DTR
                    </button>
                    <button class="bg-indigo-900 border border-white/10 hover:bg-indigo-950 text-white px-8 py-4 rounded shadow-2xl text-[11px] font-black uppercase flex items-center gap-3 transition-all transform hover:-translate-y-1 active:translate-y-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Export Log Analysis
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
