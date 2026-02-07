<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Payslip is Ready</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .payslip-summary {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .payslip-summary h3 {
            margin: 0 0 15px;
            color: #4f46e5;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .summary-row:last-child {
            border-bottom: none;
        }
        .summary-label {
            color: #64748b;
        }
        .summary-value {
            font-weight: 600;
            color: #1e293b;
        }
        .net-pay-box {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .net-pay-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
        }
        .net-pay-amount {
            font-size: 32px;
            font-weight: 700;
            margin-top: 5px;
        }
        .cta-button {
            display: inline-block;
            background-color: #4f46e5;
            color: white;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }
        .cta-button:hover {
            background-color: #4338ca;
        }
        .notice {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            font-size: 13px;
            color: #92400e;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
        .footer p {
            margin: 5px 0;
        }
        .company-name {
            font-weight: 600;
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>💰 Your Payslip is Ready!</h1>
            <p>{{ $period->start_date->format('F d') }} - {{ $period->end_date->format('F d, Y') }}</p>
        </div>

        <div class="content">
            <p class="greeting">Hi {{ $employee->first_name ?? $employee->name }},</p>

            <p>Great news! Your payslip for the period <strong>{{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}</strong> has been released and is now available for viewing.</p>

            <div class="payslip-summary">
                <h3>Pay Summary</h3>
                <div class="summary-row">
                    <span class="summary-label">Pay Period</span>
                    <span class="summary-value">{{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Pay Date</span>
                    <span class="summary-value">{{ $period->pay_date->format('F d, Y') }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Days Worked</span>
                    <span class="summary-value">{{ $payroll->days_worked ?? 0 }} days</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Gross Pay</span>
                    <span class="summary-value">₱{{ number_format($payroll->gross_pay ?? 0, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Total Deductions</span>
                    <span class="summary-value">₱{{ number_format($payroll->total_deductions ?? 0, 2) }}</span>
                </div>
            </div>

            <div class="net-pay-box">
                <div class="net-pay-label">Your Net Pay</div>
                <div class="net-pay-amount">₱{{ number_format($payroll->net_pay ?? 0, 2) }}</div>
            </div>

            <p style="text-align: center;">
                <a href="{{ url('/my-payslips') }}" class="cta-button">View Full Payslip</a>
            </p>

            <div class="notice">
                <strong>📎 Attachment:</strong> Your detailed payslip PDF is attached to this email. Please keep it for your records.
            </div>

            <p>If you have any questions or concerns about your payslip, please don't hesitate to contact the HR department.</p>

            <p>Thank you for your continued dedication and hard work!</p>

            <p>Best regards,<br>
            <strong>HR & Payroll Team</strong></p>
        </div>

        <div class="footer">
            <p class="company-name">MEBS Call Center</p>
            <p>Tacloban City, Leyte, Philippines</p>
            <p style="margin-top: 15px;">This is an automated email. Please do not reply directly to this message.</p>
            <p>© {{ date('Y') }} MEBS HIYAS Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
