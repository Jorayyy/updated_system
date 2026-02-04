<?php

namespace App\Listeners;

use App\Events\DtrApproved;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * DTR Approved Listener
 * 
 * Handles post-processing after a DTR is approved:
 * - Audit logging
 * - Trigger payroll recalculation check
 * - Notification to employee
 */
class DtrApprovedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    /**
     * Handle the event.
     */
    public function handle(DtrApproved $event): void
    {
        $dtr = $event->dtr;

        Log::channel('attendance')->info('DTR Approved Event Processed', [
            'dtr_id' => $dtr->id,
            'user_id' => $dtr->user_id,
            'dtr_date' => $dtr->dtr_date,
            'approved_by' => $event->approvedBy,
            'approval_level' => $event->approvalLevel,
        ]);

        // Create audit log
        $this->createAuditLog($dtr, $event);

        // Notify employee of approval
        $this->notifyEmployee($dtr, $event);

        // Check if all DTRs for payroll period are approved
        $this->checkPayrollPeriodCompletion($dtr);
    }

    /**
     * Create audit log entry
     */
    protected function createAuditLog(mixed $dtr, DtrApproved $event): void
    {
        try {
            AuditLog::create([
                'user_id' => $event->approvedBy,
                'action' => 'dtr_approved',
                'model_type' => 'DailyTimeRecord',
                'model_id' => $dtr->id,
                'old_values' => json_encode(['status' => 'pending']),
                'new_values' => json_encode([
                    'status' => 'approved',
                    'approved_by' => $event->approvedBy,
                    'approved_at' => now()->toISOString(),
                    'approval_level' => $event->approvalLevel,
                ]),
                'ip_address' => request()->ip() ?? 'system',
                'user_agent' => request()->userAgent() ?? 'System Process',
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create audit log for DTR approval', [
                'dtr_id' => $dtr->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify employee of DTR approval
     */
    protected function notifyEmployee(mixed $dtr, DtrApproved $event): void
    {
        try {
            \App\Models\Notification::create([
                'user_id' => $dtr->user_id,
                'type' => 'dtr_approved',
                'title' => 'DTR Approved',
                'message' => sprintf(
                    'Your Daily Time Record for %s has been approved.',
                    $dtr->dtr_date->format('M d, Y')
                ),
                'data' => json_encode([
                    'dtr_id' => $dtr->id,
                    'approved_by' => $event->approvedBy,
                ]),
                'read_at' => null,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create notification for DTR approval', [
                'dtr_id' => $dtr->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if all DTRs for the payroll period are approved
     */
    protected function checkPayrollPeriodCompletion(mixed $dtr): void
    {
        // If DTR is linked to a payroll period, check completion
        if (!$dtr->payroll_period_id) {
            return;
        }

        $payrollPeriod = $dtr->payrollPeriod;
        if (!$payrollPeriod) {
            return;
        }

        // Count pending DTRs for this employee in this period
        $pendingCount = \App\Models\DailyTimeRecord::where('user_id', $dtr->user_id)
            ->where('payroll_period_id', $dtr->payroll_period_id)
            ->whereIn('status', ['pending', 'draft'])
            ->count();

        if ($pendingCount === 0) {
            Log::channel('attendance')->info('All DTRs approved for employee in payroll period', [
                'user_id' => $dtr->user_id,
                'payroll_period_id' => $dtr->payroll_period_id,
            ]);

            // Employee is ready for payroll computation
            // The AllDtrsApproved event handles batch processing
        }
    }
}
