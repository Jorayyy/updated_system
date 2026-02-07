<?php

namespace App\Listeners;

use App\Events\PayrollComputed;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Payroll Computed Listener
 * 
 * Handles post-processing after payroll is computed:
 * - Audit logging
 * - Validation checks
 * - Anomaly detection
 */
class PayrollComputedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(PayrollComputed $event): void
    {
        $payroll = $event->payroll;

        Log::channel('payroll')->info('PayrollComputed event processed', [
            'payroll_id' => $payroll->id,
            'user_id' => $payroll->user_id,
            'net_pay' => $payroll->net_pay,
            'source' => $event->computationSource,
        ]);

        // Check for anomalies
        $this->checkAnomalies($payroll);
    }

    protected function checkAnomalies($payroll): void
    {
        $anomalies = [];

        // Check for negative net pay
        if ($payroll->net_pay < 0) {
            $anomalies[] = 'Negative net pay: ' . $payroll->net_pay;
        }

        // Check for unusually high deductions
        if ($payroll->gross_pay > 0) {
            $deductionRatio = $payroll->total_deductions / $payroll->gross_pay;
            if ($deductionRatio > 0.5) {
                $anomalies[] = sprintf('High deduction ratio: %.2f%%', $deductionRatio * 100);
            }
        }

        // Check for zero gross pay
        if ($payroll->gross_pay <= 0) {
            $anomalies[] = 'Zero or negative gross pay';
        }

        if (!empty($anomalies)) {
            Log::channel('payroll')->warning('Payroll anomalies detected', [
                'payroll_id' => $payroll->id,
                'user_id' => $payroll->user_id,
                'anomalies' => $anomalies,
            ]);
        }
    }
}
