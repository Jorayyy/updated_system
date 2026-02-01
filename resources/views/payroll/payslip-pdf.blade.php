<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payroll->user->name }} - {{ $payroll->payrollPeriod->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .company-address {
            color: #666;
            font-size: 10px;
        }
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 15px;
            letter-spacing: 2px;
        }
        .period-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 5px;
            border-radius: 5px;
        }
        .info-box h3 {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-label {
            color: #666;
            display: inline-block;
            width: 100px;
        }
        .info-value {
            font-weight: bold;
        }
        .earnings-deductions {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .earnings-column, .deductions-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 10px;
            margin-bottom: 10px;
        }
        .earnings-column .section-title {
            background-color: #d4edda;
            color: #155724;
        }
        .deductions-column .section-title {
            background-color: #f8d7da;
            color: #721c24;
        }
        .line-item {
            display: table;
            width: 100%;
            padding: 5px 10px;
            border-bottom: 1px dotted #ddd;
        }
        .line-item:last-child {
            border-bottom: none;
        }
        .line-item-label {
            display: table-cell;
            width: 70%;
        }
        .line-item-value {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-weight: 500;
        }
        .subtotal {
            background-color: #f0f0f0;
            font-weight: bold;
            margin-top: 10px;
            padding: 8px 10px;
        }
        .deductions-column .line-item-value {
            color: #dc3545;
        }
        .net-pay-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            text-align: center;
            padding: 25px;
            margin: 20px 0;
            border-radius: 10px;
        }
        .net-pay-label {
            font-size: 12px;
            text-transform: uppercase;
            opacity: 0.9;
        }
        .net-pay-amount {
            font-size: 32px;
            font-weight: bold;
            margin-top: 5px;
        }
        .remarks {
            background-color: #fff3cd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .remarks h4 {
            font-size: 10px;
            text-transform: uppercase;
            color: #856404;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .signatures {
            display: table;
            width: 100%;
            margin-top: 40px;
        }
        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 0 15px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">MEBS Call Center</div>
            <div class="company-address">Tacloban City, Leyte, Philippines</div>
            <div class="document-title">PAYSLIP</div>
            <div class="period-info">{{ $payroll->payrollPeriod->name }}</div>
        </div>

        <div class="info-section">
            <div class="info-column">
                <div class="info-box">
                    <h3>Employee Information</h3>
                    <div class="info-row">
                        <span class="info-label">Employee ID:</span>
                        <span class="info-value">{{ $payroll->user->employee_id }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value">{{ $payroll->user->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Department:</span>
                        <span class="info-value">{{ $payroll->user->department ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Position:</span>
                        <span class="info-value">{{ $payroll->user->position ?? '-' }}</span>
                    </div>
                </div>
            </div>
            <div class="info-column">
                <div class="info-box">
                    <h3>Pay Period Information</h3>
                    <div class="info-row">
                        <span class="info-label">Pay Period:</span>
                        <span class="info-value">{{ $payroll->payrollPeriod->start_date->format('M d') }} - {{ $payroll->payrollPeriod->end_date->format('M d, Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Pay Date:</span>
                        <span class="info-value">{{ $payroll->payrollPeriod->pay_date->format('M d, Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Days Worked:</span>
                        <span class="info-value">{{ $payroll->days_worked }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Hours Worked:</span>
                        <span class="info-value">{{ $payroll->hours_worked }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="earnings-deductions">
            <div class="earnings-column">
                <div class="section-title">Earnings</div>
                <div class="line-item">
                    <span class="line-item-label">Basic Pay</span>
                    <span class="line-item-value">₱{{ number_format($payroll->basic_pay, 2) }}</span>
                </div>
                @if($payroll->overtime_pay > 0)
                <div class="line-item">
                    <span class="line-item-label">Overtime Pay ({{ $payroll->overtime_hours }} hrs)</span>
                    <span class="line-item-value">₱{{ number_format($payroll->overtime_pay, 2) }}</span>
                </div>
                @endif
                @if($payroll->allowances > 0)
                <div class="line-item">
                    <span class="line-item-label">Allowances</span>
                    <span class="line-item-value">₱{{ number_format($payroll->allowances, 2) }}</span>
                </div>
                @endif
                @if($payroll->bonuses > 0)
                <div class="line-item">
                    <span class="line-item-label">Bonuses</span>
                    <span class="line-item-value">₱{{ number_format($payroll->bonuses, 2) }}</span>
                </div>
                @endif
                @if($payroll->holiday_pay > 0)
                <div class="line-item">
                    <span class="line-item-label">Holiday Pay</span>
                    <span class="line-item-value">₱{{ number_format($payroll->holiday_pay, 2) }}</span>
                </div>
                @endif
                @if($payroll->night_differential > 0)
                <div class="line-item">
                    <span class="line-item-label">Night Differential</span>
                    <span class="line-item-value">₱{{ number_format($payroll->night_differential, 2) }}</span>
                </div>
                @endif
                <div class="line-item subtotal">
                    <span class="line-item-label">GROSS PAY</span>
                    <span class="line-item-value">₱{{ number_format($payroll->gross_pay, 2) }}</span>
                </div>
            </div>
            <div class="deductions-column">
                <div class="section-title">Deductions</div>
                <div class="line-item">
                    <span class="line-item-label">SSS Contribution</span>
                    <span class="line-item-value">₱{{ number_format($payroll->sss_contribution, 2) }}</span>
                </div>
                <div class="line-item">
                    <span class="line-item-label">PhilHealth Contribution</span>
                    <span class="line-item-value">₱{{ number_format($payroll->philhealth_contribution, 2) }}</span>
                </div>
                <div class="line-item">
                    <span class="line-item-label">Pag-IBIG Contribution</span>
                    <span class="line-item-value">₱{{ number_format($payroll->pagibig_contribution, 2) }}</span>
                </div>
                <div class="line-item">
                    <span class="line-item-label">Withholding Tax</span>
                    <span class="line-item-value">₱{{ number_format($payroll->withholding_tax, 2) }}</span>
                </div>
                @if($payroll->late_deductions > 0)
                <div class="line-item">
                    <span class="line-item-label">Late Deductions</span>
                    <span class="line-item-value">₱{{ number_format($payroll->late_deductions, 2) }}</span>
                </div>
                @endif
                @if($payroll->absent_deductions > 0)
                <div class="line-item">
                    <span class="line-item-label">Absent Deductions</span>
                    <span class="line-item-value">₱{{ number_format($payroll->absent_deductions, 2) }}</span>
                </div>
                @endif
                @if($payroll->undertime_deductions > 0)
                <div class="line-item">
                    <span class="line-item-label">Undertime Deductions</span>
                    <span class="line-item-value">₱{{ number_format($payroll->undertime_deductions, 2) }}</span>
                </div>
                @endif
                @if($payroll->other_deductions > 0)
                <div class="line-item">
                    <span class="line-item-label">Other Deductions</span>
                    <span class="line-item-value">₱{{ number_format($payroll->other_deductions, 2) }}</span>
                </div>
                @endif
                <div class="line-item subtotal">
                    <span class="line-item-label">TOTAL DEDUCTIONS</span>
                    <span class="line-item-value">₱{{ number_format($payroll->total_deductions, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="net-pay-section">
            <div class="net-pay-label">Net Pay</div>
            <div class="net-pay-amount">₱{{ number_format($payroll->net_pay, 2) }}</div>
        </div>

        @if($payroll->remarks)
        <div class="remarks">
            <h4>Remarks</h4>
            <p>{{ $payroll->remarks }}</p>
        </div>
        @endif

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">Prepared By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Checked By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Received By</div>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated payslip. No signature required.</p>
            <p>Generated on {{ now()->format('F d, Y h:i A') }} | MEBS HIYAS Management System</p>
        </div>
    </div>
</body>
</html>
