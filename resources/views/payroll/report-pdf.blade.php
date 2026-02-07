<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Report - {{ $period->start_date->format('M d, Y') }} to {{ $period->end_date->format('M d, Y') }}</title>
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
            border-bottom: 2px solid #1f2937;
            padding-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
        }
        .company-address {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            color: #1f2937;
        }
        .period-info {
            font-size: 11px;
            margin-top: 5px;
            color: #666;
        }
        .summary-section {
            margin-bottom: 20px;
        }
        .summary-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-row {
            display: table-row;
        }
        .summary-cell {
            display: table-cell;
            width: 25%;
            padding: 8px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .summary-label {
            font-size: 9px;
            color: #6b7280;
        }
        .summary-value {
            font-size: 12px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 3px;
        }
        .table-section {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        th {
            background: #1f2937;
            color: white;
            padding: 8px 4px;
            text-align: right;
            font-weight: bold;
        }
        th:first-child {
            text-align: left;
        }
        td {
            padding: 6px 4px;
            border-bottom: 1px solid #e5e7eb;
            text-align: right;
        }
        td:first-child {
            text-align: left;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .employee-name {
            font-weight: bold;
        }
        .employee-id {
            font-size: 8px;
            color: #6b7280;
        }
        .total-row {
            background: #e5e7eb !important;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #1f2937;
            padding: 8px 4px;
        }
        .amount-positive {
            color: #059669;
        }
        .amount-negative {
            color: #dc2626;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 40px;
        }
        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 0 20px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }
        .signature-label {
            font-size: 10px;
            font-weight: bold;
        }
        .signature-title {
            font-size: 9px;
            color: #666;
        }
        .generated-at {
            text-align: right;
            font-size: 8px;
            color: #999;
            margin-top: 20px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">MEBS CALL CENTER</div>
            <div class="company-address">Tacloban City, Leyte, Philippines</div>
            <div class="report-title">PAYROLL REPORT</div>
            <div class="period-info">
                Period: {{ $period->start_date->format('F d, Y') }} - {{ $period->end_date->format('F d, Y') }}
                <br>
                Pay Date: {{ $period->pay_date->format('F d, Y') }}
            </div>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-title">PAYROLL SUMMARY</div>
            <table style="width: 100%; margin-bottom: 10px;">
                <tr>
                    <td style="width: 25%; padding: 8px; background: #f9fafb; border: 1px solid #e5e7eb;">
                        <div class="summary-label">Total Employees</div>
                        <div class="summary-value">{{ $summary['total_employees'] }}</div>
                    </td>
                    <td style="width: 25%; padding: 8px; background: #f9fafb; border: 1px solid #e5e7eb;">
                        <div class="summary-label">Total Gross Pay</div>
                        <div class="summary-value amount-positive">₱{{ number_format($summary['total_gross_pay'], 2) }}</div>
                    </td>
                    <td style="width: 25%; padding: 8px; background: #f9fafb; border: 1px solid #e5e7eb;">
                        <div class="summary-label">Total Deductions</div>
                        <div class="summary-value amount-negative">₱{{ number_format($summary['total_deductions'], 2) }}</div>
                    </td>
                    <td style="width: 25%; padding: 8px; background: #f9fafb; border: 1px solid #e5e7eb;">
                        <div class="summary-label">Total Net Pay</div>
                        <div class="summary-value">₱{{ number_format($summary['total_net_pay'], 2) }}</div>
                    </td>
                </tr>
            </table>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 25%; padding: 8px; background: #fff; border: 1px solid #e5e7eb;">
                        <div class="summary-label">SSS Contributions</div>
                        <div class="summary-value">₱{{ number_format($summary['total_sss'], 2) }}</div>
                    </td>
                    <td style="width: 25%; padding: 8px; background: #fff; border: 1px solid #e5e7eb;">
                        <div class="summary-label">PhilHealth</div>
                        <div class="summary-value">₱{{ number_format($summary['total_philhealth'], 2) }}</div>
                    </td>
                    <td style="width: 25%; padding: 8px; background: #fff; border: 1px solid #e5e7eb;">
                        <div class="summary-label">Pag-IBIG</div>
                        <div class="summary-value">₱{{ number_format($summary['total_pagibig'], 2) }}</div>
                    </td>
                    <td style="width: 25%; padding: 8px; background: #fff; border: 1px solid #e5e7eb;">
                        <div class="summary-label">Withholding Tax</div>
                        <div class="summary-value">₱{{ number_format($summary['total_tax'], 2) }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Payroll Details Table -->
        <div class="table-section">
            <div class="summary-title">PAYROLL DETAILS</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 18%;">Employee</th>
                        <th style="width: 9%;">Basic Pay</th>
                        <th style="width: 9%;">OT Pay</th>
                        <th style="width: 9%;">Gross</th>
                        <th style="width: 9%;">SSS</th>
                        <th style="width: 9%;">PhilHealth</th>
                        <th style="width: 9%;">Pag-IBIG</th>
                        <th style="width: 9%;">Tax</th>
                        <th style="width: 9%;">Deductions</th>
                        <th style="width: 10%;">Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payrolls as $payroll)
                        <tr>
                            <td>
                                <span class="employee-name">{{ $payroll->user->name }}</span>
                                <br>
                                <span class="employee-id">{{ $payroll->user->employee_id }}</span>
                            </td>
                            <td>₱{{ number_format($payroll->basic_pay, 2) }}</td>
                            <td>₱{{ number_format($payroll->overtime_pay, 2) }}</td>
                            <td class="amount-positive">₱{{ number_format($payroll->gross_pay, 2) }}</td>
                            <td>₱{{ number_format($payroll->sss_contribution, 2) }}</td>
                            <td>₱{{ number_format($payroll->philhealth_contribution, 2) }}</td>
                            <td>₱{{ number_format($payroll->pagibig_contribution, 2) }}</td>
                            <td>₱{{ number_format($payroll->withholding_tax, 2) }}</td>
                            <td class="amount-negative">₱{{ number_format($payroll->total_deductions, 2) }}</td>
                            <td><strong>₱{{ number_format($payroll->net_pay, 2) }}</strong></td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td><strong>TOTAL</strong></td>
                        <td>₱{{ number_format($payrolls->sum('basic_pay'), 2) }}</td>
                        <td>₱{{ number_format($payrolls->sum('overtime_pay'), 2) }}</td>
                        <td class="amount-positive">₱{{ number_format($summary['total_gross_pay'], 2) }}</td>
                        <td>₱{{ number_format($summary['total_sss'], 2) }}</td>
                        <td>₱{{ number_format($summary['total_philhealth'], 2) }}</td>
                        <td>₱{{ number_format($summary['total_pagibig'], 2) }}</td>
                        <td>₱{{ number_format($summary['total_tax'], 2) }}</td>
                        <td class="amount-negative">₱{{ number_format($summary['total_deductions'], 2) }}</td>
                        <td>₱{{ number_format($summary['total_net_pay'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Signature Section -->
        <div class="footer">
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line">
                        <div class="signature-label">Prepared By</div>
                        <div class="signature-title">Payroll Officer</div>
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <div class="signature-label">Checked By</div>
                        <div class="signature-title">HR Manager</div>
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <div class="signature-label">Approved By</div>
                        <div class="signature-title">General Manager</div>
                    </div>
                </div>
            </div>

            <div class="generated-at">
                Generated on {{ now()->format('F d, Y h:i A') }}
            </div>
        </div>
    </div>
</body>
</html>
