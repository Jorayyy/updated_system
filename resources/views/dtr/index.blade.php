@php
    $attendanceByDate = $attendances->keyBy(fn($a) => $a->date->format("Y-m-d"));
    $m = $summary["metrics"] ?? null;
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
                    <div>Attendance Report (DTR)</div>
                </div>
                <div class="flex gap-4">
                    <a href="{{ route("concerns.user-create", ["category" => "timekeeping", "title" => "DTR Discrepancy - " . $startDate->format("F d, Y")]) }}" 
                        class="text-[10px] bg-red-50 text-red-700 px-3 py-1 rounded border border-red-200 hover:bg-red-100 transition-colors uppercase font-bold">
                        Report TK Complaint
                    </a>
                </div>
            </div>

            <!-- Payroll Period Selection -->
            <div class="bg-white p-4 border border-slate-200 rounded mb-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <form method="GET" class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="text-[11px] font-black text-slate-800 uppercase tracking-tighter">View Period</label>
                        
                        @if(!empty($payrollPeriods) && count($payrollPeriods) > 0)
                            <select name="payroll_period_id" onchange="this.form.submit()" 
                                class="bg-white border text-[11px] p-2 rounded min-w-[280px] focus:ring-0 focus:border-slate-400 font-bold text-indigo-700">
                                <option value="">-- ALL RECENT PAYROLL PERIODS --</option>
                                @foreach($payrollPeriods as $period)
                                    <option value="{{ $period->id }}" 
                                        {{ $periodId == $period->id ? "selected" : "" }}>
                                        📅 &nbsp; {{ $period->start_date->format("M d") }} - {{ $period->end_date->format("M d, Y") }} 
                                        [{{ $period->payrollGroup->name ?? "Global" }}] - ({{ ucfirst($period->status) }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </form>
                <div class="flex gap-2">
                    <a href="{{ route("dtr.pdf", ["month" => $month, "year" => $year, "payroll_period_id" => $periodId]) }}" 
                       class="bg-rose-600 text-white px-8 py-2.5 rounded text-[11px] font-bold shadow-sm inline-block hover:bg-rose-700 transition-colors uppercase tracking-wider">
                       Print DTR Report
                    </a>
                </div>
            </div>

            <!-- Header Section -->
            @if($isProcessed)
                {{-- PROCESSED VIEW (Dark Theme) --}}
                <div class="mb-6 bg-slate-900 rounded-xl shadow-2xl overflow-hidden border border-slate-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 divide-y divide-x divide-slate-800">
                        <div class="p-4 flex flex-col justify-center">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Payroll Period</span>
                            <span class="text-sm font-bold text-white leading-tight">
                                {{ $startDate->format('M d, Y') }} — {{ $endDate->format('M d, Y') }}
                            </span>
                        </div>
                        <div class="p-4 flex flex-col justify-center">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Employment</span>
                            <span class="text-sm font-bold text-white">{{ strtoupper($user->employment_status ?? 'Contractual') }}</span>
                        </div>
                        <div class="p-4 flex flex-col justify-center">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Employee ID</span>
                            <span class="text-sm font-bold text-sky-400 font-mono tracking-wider">{{ $user->employee_id }}</span>
                        </div>
                        <div class="p-4 flex flex-col justify-center">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Department</span>
                            <span class="text-sm font-bold text-white truncate">{{ strtoupper($user->department->name ?? "ADMIN") }}</span>
                        </div>
                        <div class="p-4 flex flex-col justify-center">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Name</span>
                            <span class="text-sm font-black text-white leading-tight uppercase">{{ $user->name }}</span>
                        </div>
                        <div class="p-4 flex flex-col justify-center">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Section</span>
                            <span class="text-sm font-bold text-white truncate">{{ strtoupper($user->section ?? "TACLOBAN ADMIN") }}</span>
                        </div>
                        <div class="p-4 flex flex-col justify-center">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Position</span>
                            <span class="text-sm font-bold text-white truncate">{{ strtoupper($user->position ?? 'STAFF') }}</span>
                        </div>
                        <div class="p-4 flex flex-col justify-center">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Location</span>
                            <span class="text-sm font-bold text-white truncate">{{ strtoupper($user->site->name ?? 'TACLOBAN MAIN OFFICE') }}</span>
                        </div>
                    </div>
                </div>

                {{-- PROCESSED TABLE (Complex Theme) --}}
                <div class="bg-white shadow-xl border border-slate-800 overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-[10px] border-collapse border border-slate-800">
                            <thead class="bg-[#002B49] text-white">
                                <tr>
                                    <th rowspan="2" class="border border-slate-600 p-2">Date</th>
                                    <th rowspan="2" class="border border-slate-600 p-2">Day</th>
                                    <th colspan="2" class="border border-slate-600 p-2 bg-pink-500 text-white">Shift Time</th>
                                    <th colspan="2" class="border border-slate-600 p-2 bg-green-500 text-white">Actual Time</th>
                                    <th rowspan="2" class="border border-slate-600 p-2">Late</th>
                                    <th rowspan="2" class="border border-slate-600 p-2">Over break</th>
                                    <th rowspan="2" class="border border-slate-600 p-2">Undertime</th>
                                    <th colspan="2" class="border border-slate-600 p-2 bg-cyan-800 text-white font-bold">Hours Worked</th>
                                    <th colspan="6" class="border border-slate-600 p-2 bg-[#00426A]">Overtime</th>
                                    <th colspan="5" class="border border-slate-600 p-2 bg-[#00558C]">Filed Forms</th>
                                </tr>
                                <tr>
                                    <th class="border border-slate-600 p-1 bg-pink-400">IN</th>
                                    <th class="border border-slate-600 p-1 bg-pink-400">OUT</th>
                                    <th class="border border-slate-600 p-1 bg-green-400">IN</th>
                                    <th class="border border-slate-600 p-1 bg-green-400">OUT</th>
                                    <th class="border border-slate-600 p-1 bg-cyan-700 font-bold">REG</th>
                                    <th class="border border-slate-600 p-1 bg-cyan-700">ND</th>
                                    <th class="border border-slate-600 p-1">Reg</th>
                                    <th class="border border-slate-600 p-1">Restday</th>
                                    <th class="border border-slate-600 p-1">Holiday</th>
                                    <th class="border border-slate-600 p-1">Special</th>
                                    <th class="border border-slate-600 p-1">ND</th>
                                    <th class="border border-slate-600 p-1">ATRO</th>
                                    <th class="border border-slate-600 p-1">C-Sched</th>
                                    <th class="border border-slate-600 p-1">Leave</th>
                                    <th class="border border-slate-600 p-1">Official</th>
                                    <th class="border border-slate-600 p-1">TK</th>
                                    <th class="border border-slate-600 p-1">Undertime</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($processedRecords as $record)
                                    @php
                                        $d = \Carbon\Carbon::parse($record->date);
                                        $isRest = $record->attendance_status == 'rest_day';
                                        $isAbsent = $record->attendance_status == 'absent';
                                    @endphp
                                    <tr class="text-center font-bold {{ $d->isWeekend() ? 'bg-slate-50' : 'bg-white' }}">
                                        <td class="border border-slate-300 p-1">{{ $d->format('m-d') }}</td>
                                        <td class="border border-slate-300 p-1">{{ $d->format('D') }}</td>
                                        <td class="border border-slate-300 p-1 text-pink-700">{{ $record->shift_in ?? '--:--' }}</td>
                                        <td class="border border-slate-300 p-1 text-pink-700">{{ $record->shift_out ?? '--:--' }}</td>
                                        <td class="border border-slate-300 p-1 text-green-700 bg-green-50/20">
                                            {{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('H:i') : '--:--' }}
                                        </td>
                                        <td class="border border-slate-300 p-1 text-green-700 bg-green-50/20">
                                            {{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('H:i') : '--:--' }}
                                        </td>
                                        <td class="border border-slate-300 p-1 {{ $record->late_minutes > 0 ? 'text-red-600' : 'text-slate-400' }}">
                                            {{ $record->late_minutes > 0 ? number_format($record->late_minutes/60, 2) : '' }}
                                        </td>
                                        <td class="border border-slate-300 p-1"></td>
                                        <td class="border border-slate-300 p-1 {{ $record->undertime_minutes > 0 ? 'text-red-600' : 'text-slate-400' }}">
                                            {{ $record->undertime_minutes > 0 ? number_format($record->undertime_minutes/60, 2) : '' }}
                                        </td>
                                        <td class="border border-slate-300 p-1 bg-cyan-50/20 text-blue-800">
                                            @if($isAbsent) 
                                                <span class="text-red-600 text-[8px]">absent</span>
                                            @else
                                                {{ $record->actual_work_minutes > 0 ? number_format($record->actual_work_minutes/60, 2) : '' }}
                                            @endif
                                        </td>
                                        <td class="border border-slate-300 p-1"></td>
                                        <td class="border border-slate-300 p-1"></td>
                                        <td class="border border-slate-300 p-1 text-center">@if($isRest) ✔ @endif</td>
                                        <td class="border border-slate-300 p-1"></td>
                                        <td class="border border-slate-300 p-1"></td>
                                        <td class="border border-slate-300 p-1"></td>
                                        <td class="border border-slate-300 p-1"></td>
                                        <td class="border border-slate-300 p-1"></td>
                                        <td class="border border-slate-300 p-1"></td>
                                        <td class="border border-slate-300 p-1"></td>
                                        <td class="border border-slate-300 p-1"></td>
                                        <td class="border border-slate-300 p-1"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                {{-- UNPROCESSED VIEW --}}
                <div class="mb-6 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center bg-gradient-to-r from-indigo-800 to-indigo-600 border-b border-indigo-900/10">
                        <div>
                            <h1 class="text-white text-xl font-bold tracking-tight uppercase leading-none">{{ $user->name }}</h1>
                            <p class="text-indigo-200 text-[10px] font-semibold uppercase tracking-[0.2em] mt-2 flex items-center">
                                {{ $user->position ?? "STAFF" }} &nbsp; 
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2 mx-1 opacity-50 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                ID: {{ $user->employee_id }}
                            </p>
                        </div>
                        <div class="text-right bg-white/10 px-4 py-2 rounded-lg border border-white/10">
                            <p class="text-indigo-100 text-[9px] uppercase font-black opacity-80 tracking-widest">Selected View Period</p>
                            <p class="text-white text-sm font-bold mt-0.5">{{ $startDate->format("M d, Y") }} 
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline-block mx-1 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                {{ $endDate->format("M d, Y") }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Main DTR Table (Unprocessed Default) -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 text-red-600 rounded-full mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 mb-2">DTR is not yet processed</h3>
                        <p class="text-slate-500 text-sm max-w-md mx-auto">This payroll period has not been finalized yet. Please check again after the cut-off processing has been completed by HR/Finance.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
