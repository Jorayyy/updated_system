<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR - {{ $user->name }} - {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
        }
        .period {
            font-size: 12px;
            color: #666;
        }
        .employee-info {
            margin-bottom: 20px;
        }
        .employee-info table {
            width: 100%;
        }
        .employee-info td {
            padding: 3px 10px 3px 0;
        }
        .employee-info .label {
            font-weight: bold;
            width: 100px;
        }
        .dtr-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .dtr-table th,
        .dtr-table td {
            border: 1px solid #333;
            padding: 5px;
            text-align: center;
            font-size: 9px;
        }
        .dtr-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .dtr-table .day-col {
            width: 25px;
        }
        .dtr-table .time-col {
            width: 60px;
        }
        .dtr-table .weekend {
            background-color: #f5f5f5;
        }
        .dtr-table .status-present {
            color: green;
        }
        .dtr-table .status-late {
            color: orange;
        }
        .dtr-table .status-absent {
            color: red;
        }
        .dtr-table .status-leave {
            color: blue;
        }
        .summary-table {
            width: 50%;
            margin: 0 auto 30px auto;
            border-collapse: collapse;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #333;
            padding: 6px 10px;
            font-size: 10px;
        }
        .summary-table th {
            background-color: #f0f0f0;
            text-align: left;
            width: 50%;
        }
        .summary-table td {
            text-align: center;
            font-weight: bold;
        }
        .signatures {
            margin-top: 40px;
        }
        .signatures table {
            width: 100%;
        }
        .signatures td {
            width: 33%;
            text-align: center;
            padding-top: 50px;
        }
        .signatures .line {
            border-top: 1px solid #333;
            display: inline-block;
            width: 150px;
            margin-bottom: 5px;
        }
        .signatures .sig-label {
            font-size: 9px;
            color: #666;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">MEBS Call Center</div>
            <div>{{ $companyAddress ?? 'Tacloban City, Leyte, Philippines' }}</div>
            <div class="document-title">DAILY TIME RECORD</div>
            <div class="period">For the month of {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</div>
        </div>

        <div class="employee-info">
            <table>
                <tr>
                    <td class="label">Employee ID:</td>
                    <td>{{ $user->employee_id }}</td>
                    <td class="label">Department:</td>
                    <td>{{ $user->department ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Name:</td>
                    <td>{{ $user->name }}</td>
                    <td class="label">Position:</td>
                    <td>{{ $user->position ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <table class="dtr-table">
            <thead>
                <tr>
                    <th class="day-col">Day</th>
                    <th>Date</th>
                    <th class="time-col">AM IN</th>
                    <th class="time-col">AM OUT</th>
                    <th class="time-col">PM IN</th>
                    <th class="time-col">PM OUT</th>
                    <th>Hours</th>
                    <th>OT/UT</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $startDate = \Carbon\Carbon::create($year, $month, 1);
                    $endDate = $startDate->copy()->endOfMonth();
                    $attendanceByDate = $attendances->keyBy(fn($a) => $a->date->format('Y-m-d'));
                @endphp
                @for($date = $startDate->copy(); $date <= $endDate; $date->addDay())
                    @php
                        $attendance = $attendanceByDate->get($date->format('Y-m-d'));
                        $isWeekend = $date->isWeekend();
                    @endphp
                    <tr class="{{ $isWeekend ? 'weekend' : '' }}">
                        <td>{{ $date->format('d') }}</td>
                        <td>{{ $date->format('D') }}</td>
                        @if($attendance)
                            <td>{{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>{{ $attendance->time_out ? $attendance->time_out->format('h:i A') : '-' }}</td>
                            <td>{{ number_format($attendance->total_work_minutes / 60, 1) }}</td>
                            <td>
                                @if($attendance->overtime_minutes > 0)
                                    +{{ number_format($attendance->overtime_minutes / 60, 1) }}
                                @elseif($attendance->undertime_minutes > 0)
                                    -{{ number_format($attendance->undertime_minutes / 60, 1) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="status-{{ $attendance->status }}">
                                {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                            </td>
                        @else
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>
                                @if($isWeekend)
                                    <span style="color: #999;">Rest Day</span>
                                @elseif($date->isFuture())
                                    -
                                @else
                                    <span class="status-absent">Absent</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endfor
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <th>Present Days</th>
                <td>{{ $summary['present_days'] }}</td>
            </tr>
            <tr>
                <th>Late Days</th>
                <td>{{ $summary['late_days'] }}</td>
            </tr>
            <tr>
                <th>Absent Days</th>
                <td>{{ $summary['absent_days'] }}</td>
            </tr>
            <tr>
                <th>Leave Days</th>
                <td>{{ $summary['leave_days'] }}</td>
            </tr>
            <tr>
                <th>Total Work Hours</th>
                <td>{{ $summary['total_work_hours'] }}</td>
            </tr>
            <tr>
                <th>Total Overtime Hours</th>
                <td>{{ $summary['total_overtime_hours'] }}</td>
            </tr>
        </table>

        <div class="signatures">
            <table>
                <tr>
                    <td>
                        <div class="line"></div>
                        <div class="sig-label">Employee's Signature</div>
                    </td>
                    <td>
                        <div class="line"></div>
                        <div class="sig-label">Verified By</div>
                    </td>
                    <td>
                        <div class="line"></div>
                        <div class="sig-label">Approved By</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Generated on {{ now()->format('F d, Y h:i A') }} | MEBS HR Management System
        </div>
    </div>
</body>
</html>
