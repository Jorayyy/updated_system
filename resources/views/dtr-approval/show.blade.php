<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('DTR Approval Detail') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('dtr-approval.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium">
                    ← Back to List
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium shadow-sm transition">
                    PRINT DTR
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">
            
            {{-- HEADER INFO BLOCK --}}
            <div class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 divide-y divide-x divide-slate-800">
                    <!-- Payroll Period -->
                    <div class="p-4 flex flex-col justify-center">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Payroll Period</span>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-white leading-tight">
                                {{ optional($dailyTimeRecord->payrollPeriod)->start_date?->format('M d, Y') }} — {{ optional($dailyTimeRecord->payrollPeriod)->end_date?->format('M d, Y') }}
                            </span>
                            <span class="text-[9px] font-mono text-slate-500 mt-1">ID: {{ $dailyTimeRecord->payroll_period_id }}</span>
                        </div>
                    </div>

                    <!-- Employment Status -->
                    <div class="p-4 flex flex-col justify-center">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Employment</span>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                            <span class="text-sm font-bold text-white">{{ strtoupper($dailyTimeRecord->user->employment_status ?? 'Contractual') }}</span>
                        </div>
                    </div>

                    <!-- Employee ID -->
                    <div class="p-4 flex flex-col justify-center">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Employee ID</span>
                        <span class="text-sm font-bold text-sky-400 font-mono tracking-wider">{{ $dailyTimeRecord->user->employee_id }}</span>
                    </div>

                    <!-- Department -->
                    <div class="p-4 flex flex-col justify-center">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Department</span>
                        <span class="text-sm font-bold text-white truncate">{{ strtoupper($dailyTimeRecord->user->department->name ?? $dailyTimeRecord->user->department_rel->name ?? "N/A") }}</span>
                    </div>

                    <!-- Full Name -->
                    <div class="p-4 flex flex-col justify-center">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Name</span>
                        <span class="text-sm font-black text-white leading-tight">{{ strtoupper($dailyTimeRecord->user->last_name) }}, {{ strtoupper($dailyTimeRecord->user->first_name) }}</span>
                    </div>

                    <!-- Section -->
                    <div class="p-4 flex flex-col justify-center">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Section</span>
                        <span class="text-sm font-bold text-white truncate">{{ strtoupper($dailyTimeRecord->user->department->name ?? $dailyTimeRecord->user->department_rel->name ?? "N/A") }}</span>
                    </div>

                    <!-- Position -->
                    <div class="p-4 flex flex-col justify-center">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Position</span>
                        <span class="text-sm font-bold text-white truncate">{{ strtoupper($dailyTimeRecord->user->position ?? 'AGENT') }}</span>
                    </div>

                    <!-- Location -->
                    <div class="p-4 flex flex-col justify-center">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Location</span>
                        <div class="flex items-center gap-2">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="text-sm font-bold text-white truncate">{{ strtoupper($dailyTimeRecord->user->site->name ?? 'TACLOBAN OFFICE') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MAIN DTR GRID --}}
            <div class="bg-white shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-[9px] border-collapse border border-slate-800 font-sans">
                        <thead class="bg-[#002B49] text-white">
                            <tr>
                                <th rowspan="2" class="border border-slate-600 p-1">Date</th>
                                <th rowspan="2" class="border border-slate-600 p-1">Day</th>
                                <th colspan="2" class="border border-slate-600 p-1 bg-pink-500 text-white">Shift Time</th>
                                <th colspan="2" class="border border-slate-600 p-1 bg-green-500 text-white">Actual Time</th>
                                <th rowspan="2" class="border border-slate-600 p-1">Late</th>
                                <th rowspan="2" class="border border-slate-600 p-1">Over break</th>
                                <th rowspan="2" class="border border-slate-600 p-1">Undertime</th>
                                <th colspan="2" class="border border-slate-600 p-1 bg-cyan-800 text-white">Hours Worked</th>
                                <th colspan="6" class="border border-slate-600 p-1 bg-[#00426A]">Overtime</th>
                                <th colspan="5" class="border border-slate-600 p-1 bg-[#00558C]">Filed Forms</th>
                                <th rowspan="2" class="border border-slate-600 p-1">Status</th>
                            </tr>
                            <tr>
                                <th class="border border-slate-600 p-1 bg-pink-400">IN</th>
                                <th class="border border-slate-600 p-1 bg-pink-400">OUT</th>
                                <th class="border border-slate-600 p-1 bg-green-400">IN</th>
                                <th class="border border-slate-600 p-1 bg-green-400">OUT</th>
                                <th class="border border-slate-600 p-1">REG</th>
                                <th class="border border-slate-600 p-1">ND</th>
                                {{-- OT --}}
                                <th class="border border-slate-600 p-1">Reg</th>
                                <th class="border border-slate-600 p-1">Restday</th>
                                <th class="border border-slate-600 p-1">Holiday</th>
                                <th class="border border-slate-600 p-1">Special</th>
                                <th class="border border-slate-600 p-1">ND</th>
                                <th class="border border-slate-600 p-1">ATRO</th>
                                {{-- Filed Forms --}}
                                <th class="border border-slate-600 p-1">CS/Rest</th>
                                <th class="border border-slate-600 p-1">Leave</th>
                                <th class="border border-slate-600 p-1">OB</th>
                                <th class="border border-slate-600 p-1">TK</th>
                                <th class="border border-slate-600 p-1">UT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($periodRecords as $record)
                                @php
                                    $carbonDate = \Carbon\Carbon::parse($record->date);
                                    $isWeekend = $carbonDate->isWeekend();
                                    $isAbsent = $record->status_flag === 'absent';
                                    
                                    // Map Filed Forms for this date
                                    $dayLeave = $filedForms['leaves']->first(fn($f) => $carbonDate->between($f->start_date, $f->end_date));
                                    $dayOB = $filedForms['obs']->first(fn($f) => $f->date == $record->date);
                                    $dayOT = $filedForms['ots']->first(fn($f) => $f->date == $record->date);
                                    $dayCS = $filedForms['shifts']->first(fn($f) => $f->requested_date->format('Y-m-d') == $record->date);
                                @endphp
                                <tr class="{{ $isWeekend ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                                    <td class="border border-slate-300 p-1 text-center font-bold">{{ $carbonDate->format('m-d') }}</td>
                                    <td class="border border-slate-300 p-1 text-center">{{ $carbonDate->format('D') }}</td>
                                    
                                    {{-- Shift --}}
                                    <td class="border border-slate-300 p-1 text-center text-pink-700">{{ $record->shift_in ?? '--:--' }}</td>
                                    <td class="border border-slate-300 p-1 text-center text-pink-700">{{ $record->shift_out ?? '--:--' }}</td>
                                    
                                    {{-- Actual --}}
                                    @if($isAbsent)
                                        <td colspan="2" class="border border-slate-300 p-1 text-center text-red-500 font-bold bg-red-50">ABSENT</td>
                                    @else
                                        <td class="border border-slate-300 p-1 text-center text-green-700 bg-green-50 font-medium">{{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('H:i') : '--:--' }}</td>
                                        <td class="border border-slate-300 p-1 text-center text-green-700 bg-green-50 font-medium">{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('H:i') : '--:--' }}</td>
                                    @endif

                                    <td class="border border-slate-300 p-1 text-center {{ $record->late_minutes > 0 ? 'text-red-600 bg-red-50' : '' }} font-bold">{{ $record->late_minutes ?: '' }}</td>
                                    <td class="border border-slate-300 p-1 text-center">{{ $record->overbreak_minutes ?: '' }}</td>
                                    <td class="border border-slate-300 p-1 text-center {{ $record->undertime_minutes > 0 ? 'text-orange-600 bg-orange-50' : '' }}">{{ $record->undertime_minutes ?: '' }}</td>
                                    
                                    {{-- Work Hours --}}
                                    <td class="border border-slate-300 p-1 text-center {{ $isAbsent ? 'text-red-500 italic' : 'text-blue-800' }}">{{ $isAbsent ? 'absent' : number_format($record->regular_hours, 1) }}</td>
                                    <td class="border border-slate-300 p-1 text-center font-bold text-indigo-700">{{ $record->night_diff_minutes > 0 ? number_format($record->night_diff_minutes / 60, 1) : '' }}</td>
                                    
                                    {{-- OT Breakdown --}}
                                    <td class="border border-slate-300 p-1 text-center">{{ ($record->day_type == 'regular' && $record->overtime_minutes > 0) ? number_format($record->overtime_minutes / 60, 1) : '' }}</td>
                                    <td class="border border-slate-300 p-1 text-center font-bold">{{ ($record->day_type == 'rest_day') ? number_format($record->overtime_minutes / 60, 1) : '' }}</td>
                                    <td class="border border-slate-300 p-1 text-center">{{ ($record->day_type == 'holiday') ? number_format($record->overtime_minutes / 60, 1) : '' }}</td>
                                    <td class="border border-slate-300 p-1 text-center">{{ ($record->day_type == 'special_holiday') ? number_format($record->overtime_minutes / 60, 1) : '' }}</td>
                                    <td class="border border-slate-300 p-1 text-center"></td>
                                    <td class="border border-slate-300 p-1 text-center"></td>
                                    
                                    {{-- Forms --}}
                                    <td class="border border-slate-300 p-1 text-center">@if($dayCS)<span title="{{ $dayCS->remarks }}">✔</span>@endif</td>
                                    <td class="border border-slate-300 p-1 text-center font-bold text-indigo-600">@if($dayLeave)<span title="{{ $dayLeave->leaveType->name ?? 'Leave' }}">LEAVE</span>@endif</td>
                                    <td class="border border-slate-300 p-1 text-center">@if($dayOB)<span title="OB">✔</span>@endif</td>
                                    <td class="border border-slate-300 p-1 text-center"></td>
                                    <td class="border border-slate-300 p-1 text-center"></td>
                                    
                                    <td class="border border-slate-300 p-2 text-center">
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase {{ $record->status == 'approved' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $record->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- SUMMARY FOOTER --}}
            <div class="mt-4 grid grid-cols-2 gap-4">
                {{-- Totals Table --}}
                <div class="bg-white border-2 border-[#002B49] rounded overflow-hidden">
                    <table class="w-full text-[9px] uppercase font-bold text-[#002B49]">
                        <thead class="bg-[#002B49] text-white">
                            <tr>
                                <th class="p-1 border border-slate-800">Description</th>
                                <th class="p-1 border border-slate-800">Regular</th>
                                <th class="p-1 border border-slate-800">Restday</th>
                                <th class="p-1 border border-slate-800">Reg Holiday</th>
                                <th class="p-1 border border-slate-800">Spec Holiday</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="p-1 border border-slate-300 bg-gray-50">Regular</td>
                                <td class="p-1 border border-slate-300 text-center">{{ number_format($summary['regular_hours'], 2) }}</td>
                                <td class="p-1 border border-slate-300 text-center">0.00</td>
                                <td class="p-1 border border-slate-300 text-center">0.00</td>
                                <td class="p-1 border border-slate-300 text-center">0.00</td>
                            </tr>
                            <tr>
                                <td class="p-1 border border-slate-300 bg-gray-50">Overtime</td>
                                <td class="p-1 border border-slate-300 text-center">{{ number_format($summary['overtime_hours'], 2) }}</td>
                                <td class="p-1 border border-slate-300 text-center">0.00</td>
                                <td class="p-1 border border-slate-300 text-center">0.00</td>
                                <td class="p-1 border border-slate-300 text-center">0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Counts Table --}}
                <div class="bg-white border-2 border-[#002B49] rounded overflow-hidden">
                    <table class="w-full text-[9px] uppercase font-bold text-[#002B49]">
                        <thead class="bg-[#002B49] text-white">
                            <tr>
                                <th class="p-1 border border-slate-800">Summary Metric</th>
                                <th class="p-1 border border-slate-800">Total Minutes</th>
                                <th class="p-1 border border-slate-800">Occurrences</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="p-1 border border-slate-300 bg-red-50 text-red-700">Absences (Days)</td>
                                <td class="p-1 border border-slate-300 text-center">-</td>
                                <td class="p-1 border border-slate-300 text-center text-red-700">{{ $summary['absent_count'] }}</td>
                            </tr>
                            <tr>
                                <td class="p-1 border border-slate-300 bg-amber-50 text-amber-700">Lates/Tardiness</td>
                                <td class="p-1 border border-slate-300 text-center text-amber-700">{{ $summary['late_minutes'] }}</td>
                                <td class="p-1 border border-slate-300 text-center text-amber-700">{{ $summary['tardiness_count'] }}</td>
                            </tr>
                            <tr>
                                <td class="p-1 border border-slate-300 bg-orange-50 text-orange-700">Undertime</td>
                                <td class="p-1 border border-slate-300 text-center text-orange-700">{{ $summary['undertime_minutes'] }}</td>
                                <td class="p-1 border border-slate-300 text-center text-orange-700">{{ $summary['undertime_count'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ACTION OVERLAY (Only if viewing specific day DTR) --}}
            @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                <div class="mt-8 p-6 bg-white border-4 border-dashed border-indigo-200 rounded-2xl text-center">
                    <h4 class="font-black text-indigo-900 mb-4">You are currently viewing the FULL Period overview for this employee.</h4>
                    <div class="flex justify-center gap-4">
                        @if($dailyTimeRecord->status === 'pending')
                            <form action="{{ route('dtr-approval.approve', $dailyTimeRecord) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-xl font-bold uppercase shadow-lg shadow-green-200">
                                    Approve Current Selected Day ({{ $dailyTimeRecord->date }})
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('dtr-approval.edit', $dailyTimeRecord) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold uppercase shadow-lg shadow-indigo-200">
                            Edit Selected Day
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>



