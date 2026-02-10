<?php

namespace App\Listeners;

use App\Events\AllDtrsApproved;
use App\Jobs\ComputePayrollJob;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\PayrollPeriod;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * All DTRs Approved Listener
 * 
 * Handles the event when all DTRs for a payroll period are approved:
 * - Triggers automatic payroll computation
 * - Notifies HR/Admin that payroll can be processed
 * - Updates payroll period status
 */
class AllDtrsApprovedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'payroll';

    /**
     * Handle the event.
     */
    public function handle(AllDtrsApproved $event): void
    {
        $payrollPeriod = $event->period;

        Log::channel('payroll')->info('All DTRs Approved Event Triggered', [
            'payroll_period_id' => $payrollPeriod->id,
            'period' => $payrollPeriod->start_date . ' to ' . $payrollPeriod->end_date,
        ]);

        // Create audit log
        $this->createAuditLog($payrollPeriod);

        // Update payroll period status
        $this->updatePayrollPeriodStatus($payrollPeriod);

        // Notify admins/HR
        $this->notifyAdmins($payrollPeriod);

        // Auto-trigger payroll computation if enabled
        if ($this->isAutoPayrollEnabled()) {
            $this->triggerPayrollComputation($payrollPeriod);
        }
    }

    /**
     * Create audit log entry
     */
    protected function createAuditLog(PayrollPeriod $payrollPeriod): void
    {
        try {
            AuditLog::create([
                'user_id' => null, // System action
                'action' => 'all_dtrs_approved',
                'model_type' => 'PayrollPeriod',
                'model_id' => $payrollPeriod->id,
                'old_values' => null,
                'new_values' => [
                    'status' => 'ready_for_payroll',
                    'all_dtrs_approved' => true,
                    'approved_at' => now()->toISOString(),
                ],
                'ip_address' => 'system',
                'user_agent' => 'Event Listener',
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create audit log for all DTRs approved', [
                'payroll_period_id' => $payrollPeriod->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update payroll period status
     */
    protected function updatePayrollPeriodStatus(PayrollPeriod $payrollPeriod): void
    {
        try {
            $payrollPeriod->update([
                'all_dtrs_approved' => true,
                'dtr_approved_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update payroll period status', [
                'payroll_period_id' => $payrollPeriod->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify admin users
     */
    protected function notifyAdmins(PayrollPeriod $payrollPeriod): void
    {
        try {
            // Get admin users
            $admins = \App\Models\User::where('role', 'admin')
                ->orWhere('role', 'hr')
                ->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'payroll_ready',
                    'title' => 'Payroll Ready for Processing',
                    'message' => sprintf(
                        'All DTRs for payroll period %s to %s have been approved. Payroll computation can now proceed.',
                        $payrollPeriod->start_date->format('M d, Y'),
                        $payrollPeriod->end_date->format('M d, Y')
                    ),
                    'data' => json_encode([
                        'payroll_period_id' => $payrollPeriod->id,
                    ]),
                    'read_at' => null,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to notify admins about payroll ready', [
                'payroll_period_id' => $payrollPeriod->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if auto-payroll computation is enabled
     */
    protected function isAutoPayrollEnabled(): bool
    {
        return \App\Models\CompanySetting::getValue('auto_compute_payroll', false);
    }

    /**
     * Trigger automatic payroll computation
     */
    protected function triggerPayrollComputation(PayrollPeriod $payrollPeriod): void
    {
        try {
            Log::channel('payroll')->info('Auto-triggering payroll computation', [
                'payroll_period_id' => $payrollPeriod->id,
                'period_start' => $payrollPeriod->start_date,
                'period_end' => $payrollPeriod->end_date,
            ]);

            // Dispatch payroll computation job to the queue
            ComputePayrollJob::dispatch($payrollPeriod)
                ->onQueue('payroll');

            Log::channel('payroll')->info('Payroll computation job dispatched', [
                'payroll_period_id' => $payrollPeriod->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to trigger payroll computation', [
                'payroll_period_id' => $payrollPeriod->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
