<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Year-to-Date Summary {{ $year }} - {{ $user->name }}</title>
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
            padding: 25px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .company-address {
            color: #666;
            font-size: 9px;
        }
        .document-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 12px;
            letter-spacing: 2px;
        }
        .year-info {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .employee-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .employee-info h3 {
            font-size: 9px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 3px 0;
            width: 50%;
        }
        .info-label {
            color: #666;
            width: 100px;
            display: inline-block;
        }
        .info-value {
            font-weight: bold;
        }
        .summary-section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 12px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .earnings-title {
            background-color: #d4edda;
            color: #155724;
        }
        .deductions-title {
            background-color: #f8d7da;
            color: #721c24;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-col {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
            vertical-align: top;
        }
        .summary-item {
            display: table;
            width: 100%;
            padding: 6px 10px;
            border-bottom: 1px dotted #ddd;
        }
        .summary-item:last-child {
            border-bottom: none;
        }
        .summary-label {
            display: table-cell;
            width: 65%;
            color: #555;
        }
        .summary-value {
            display: table-cell;
            width: 35%;
            text-align: right;
            font-weight: 600;
        }
        .summary-total {
            background-color: #f0f0f0;
            font-weight: bold;
            margin-top: 5px;
        }
        .net-total {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            text-align: center;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .net-total-label {
            font-size: 10px;
            text-transform: uppercase;
            opacity: 0.9;
        }
        .net-total-amount {
            font-size: 26px;
            font-weight: bold;
            margin-top: 5px;
        }
        .payroll-history {
            margin-top: 20px;
        }
        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .history-table th {
            background-color: #f8f9fa;
            padding: 8px 6px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-size: 8px;
            text-transform: uppercase;
        }
        .history-table td {
            padding: 6px;
            border-bottom: 1px solid #eee;
        }
        .history-table tr:last-child td {
            border-bottom: none;
        }
        .history-table .amount {
            text-align: right;
            font-family: monospace;
        }
        .history-table tfoot {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .history-table tfoot td {
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">{{ $settings['company_name'] ?? 'MEBS Call Center' }}</div>
            <div class="company-address">{{ $settings['company_address'] ?? 'Tacloban City, Leyte, Philippines' }}</div>
            <div class="document-title">YEAR-TO-DATE COMPENSATION SUMMARY</div>
            <div class="year-info">Fiscal Year {{ $year }}</div>
        </div>

        <div class="employee-info">
            <h3>Employee Information</h3>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">Employee ID:</span>
                        <span class="info-value">{{ $user->employee_id }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">Department:</span>
                        <span class="info-value">{{ $user->department ?? '-' }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">Name:</span>
                        <span class="info-value">{{ $user->name }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">Position:</span>
                        <span class="info-value">{{ $user->position ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-col">
                <div class="section-title earnings-title">YTD Earnings</div>
                <div class="summary-item">
                    <span class="summary-label">Total Gross Pay</span>
                    <span class="summary-value">₱{{ number_format($summary['total_gross'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Overtime Pay</span>
                    <span class="summary-value">₱{{ number_format($summary['total_overtime'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Bonuses</span>
                    <span class="summary-value">₱{{ number_format($summary['total_bonuses'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Pay Periods</span>
                    <span class="summary-value">{{ $summary['total_periods'] }}</span>
                </div>
            </div>
            <div class="summary-col">
                <div class="section-title deductions-title">YTD Deductions</div>
                <div class="summary-item">
                    <span class="summary-label">SSS Contributions</span>
                    <span class="summary-value">₱{{ number_format($summary['total_sss'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">PhilHealth Contributions</span>
                    <span class="summary-value">₱{{ number_format($summary['total_philhealth'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Pag-IBIG Contributions</span>
                    <span class="summary-value">₱{{ number_format($summary['total_pagibig'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Withholding Tax</span>
                    <span class="summary-value">₱{{ number_format($summary['total_tax'], 2) }}</span>
                </div>
                <div class="summary-item summary-total">
                    <span class="summary-label">Total Deductions</span>
                    <span class="summary-value">₱{{ number_format($summary['total_deductions'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="net-total">
            <div class="net-total-label">Year-to-Date Net Pay</div>
            <div class="net-total-amount">₱{{ number_format($summary['total_net'], 2) }}</div>
        </div>

        <div class="payroll-history">
            <div class="section-title" style="background-color: #e2e3e5; color: #383d41;">Payroll History</div>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Pay Date</th>
                        <th class="amount">Gross Pay</th>
                        <th class="amount">Deductions</th>
                        <th class="amount">Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payrolls as $payroll)
                    <tr>
                        <td>{{ $payroll->payrollPeriod->start_date->format('M d') }} - {{ $payroll->payrollPeriod->end_date->format('M d') }}</td>
                        <td>{{ $payroll->payrollPeriod->pay_date->format('M d, Y') }}</td>
                        <td class="amount">₱{{ number_format($payroll->gross_pay, 2) }}</td>
                        <td class="amount">₱{{ number_format($payroll->total_deductions, 2) }}</td>
                        <td class="amount">₱{{ number_format($payroll->net_pay, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>YEAR TOTALS</strong></td>
                        <td class="amount">₱{{ number_format($summary['total_gross'], 2) }}</td>
                        <td class="amount">₱{{ number_format($summary['total_deductions'], 2) }}</td>
                        <td class="amount">₱{{ number_format($summary['total_net'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="footer">
            <p>This is a computer-generated document. No signature required.</p>
            <p>Generated on {{ now()->format('F d, Y h:i A') }} | MEBS HIYAS Management System</p>
            <p style="margin-top: 10px;">For discrepancies or inquiries, please contact the HR/Payroll department.</p>
        </div>
    </div>
</body>
</html>
