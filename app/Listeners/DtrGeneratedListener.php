<?php

namespace App\Listeners;

use App\Events\DtrGenerated;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * DTR Generated Listener
 * 
 * Handles post-processing after a DTR is generated:
 * - Audit logging
 * - Notification dispatch (if needed)
 * - Validation checks
 */
class DtrGeneratedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    /**
     * Handle the event.
     */
    public function handle(DtrGenerated $event): void
    {
        $dtr = $event->dtr;

        Log::channel('attendance')->info('DTR Generated Event Processed', [
            'dtr_id' => $dtr->id,
            'user_id' => $dtr->user_id,
            'dtr_date' => $dtr->dtr_date,
            'status' => $dtr->status,
            'processing_type' => $event->processingType,
            'triggered_by' => $event->triggeredBy,
        ]);

        // Create audit log for DTR generation
        $this->createAuditLog($dtr, $event);

        // Check for anomalies that might need attention
        $this->checkForAnomalies($dtr);

        // Notify employee if there are issues with their DTR
        $this->notifyIfNeeded($dtr, $event);
    }

    /**
     * Create audit log entry
     */
    protected function createAuditLog(mixed $dtr, DtrGenerated $event): void
    {
        try {
            AuditLog::create([
                'user_id' => $event->triggeredBy ?? $dtr->user_id,
                'action' => 'dtr_generated',
                'model_type' => 'DailyTimeRecord',
                'model_id' => $dtr->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'dtr_date' => $dtr->dtr_date,
                    'status' => $dtr->status,
                    'late_minutes' => $dtr->late_minutes,
                    'undertime_minutes' => $dtr->undertime_minutes,
                    'overtime_minutes' => $dtr->overtime_minutes,
                    'total_hours_worked' => $dtr->total_hours_worked,
                ]),
                'ip_address' => request()->ip() ?? 'scheduler',
                'user_agent' => request()->userAgent() ?? 'Laravel Scheduler',
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create audit log for DTR generation', [
                'dtr_id' => $dtr->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check for anomalies in the DTR
     */
    protected function checkForAnomalies(mixed $dtr): void
    {
        $anomalies = [];

        // Check for excessive late minutes
        if ($dtr->late_minutes > 120) {
            $anomalies[] = 'Excessive lateness: ' . $dtr->late_minutes . ' minutes';
        }

        // Check for incomplete attendance
        if ($dtr->attendance_status === 'incomplete') {
            $anomalies[] = 'Incomplete attendance record';
        }

        // Check for unusually high overtime
        if ($dtr->overtime_minutes > 240) {
            $anomalies[] = 'High overtime: ' . $dtr->overtime_minutes . ' minutes';
        }

        // Check for auto-timeout
        if ($dtr->has_auto_timeout) {
            $anomalies[] = 'Auto-timeout applied (missed clock-out)';
        }

        if (!empty($anomalies)) {
            Log::channel('attendance')->warning('DTR Anomalies Detected', [
                'dtr_id' => $dtr->id,
                'user_id' => $dtr->user_id,
                'dtr_date' => $dtr->dtr_date,
                'anomalies' => $anomalies,
            ]);
        }
    }

    /**
     * Notify employee if there are issues
     */
    protected function notifyIfNeeded(mixed $dtr, DtrGenerated $event): void
    {
        // If auto-timeout was applied, consider notifying the employee
        if ($dtr->has_auto_timeout) {
            // Create notification for employee
            try {
                \App\Models\Notification::create([
                    'user_id' => $dtr->user_id,
                    'type' => 'dtr_auto_timeout',
                    'title' => 'Missed Clock-Out Detected',
                    'message' => sprintf(
                        'Your attendance for %s was automatically timed out. Please review your DTR and update if needed.',
                        $dtr->dtr_date->format('M d, Y')
                    ),
                    'data' => json_encode(['dtr_id' => $dtr->id]),
                    'read_at' => null,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create notification for auto-timeout', [
                    'dtr_id' => $dtr->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Determine whether the listener should be queued.
     */
    public function shouldQueue(DtrGenerated $event): bool
    {
        return true;
    }
}
