<?php

namespace App\Listeners;

use App\Events\PayrollReleased;
use App\Mail\PayslipReleased;
use App\Models\AuditLog;
use App\Models\CompanySetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Payroll Released Listener
 * 
 * Handles post-processing after payroll is released:
 * - Send payslip email with PDF attachment
 * - Audit logging
 * - Create in-app notification
 */
class PayrollReleasedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(PayrollReleased $event): void
    {
        $payroll = $event->payroll;
        $payroll->load(['user', 'payrollPeriod']);

        Log::channel('payroll')->info('PayrollReleased event processed', [
            'payroll_id' => $payroll->id,
            'user_id' => $payroll->user_id,
            'released_by' => $event->releasedBy,
        ]);

        // Create audit log
        AuditLog::create([
            'user_id' => $event->releasedBy,
            'action' => 'payroll_released',
            'model_type' => 'Payroll',
            'model_id' => $payroll->id,
            'old_values' => json_encode(['status' => 'approved']),
            'new_values' => json_encode([
                'status' => 'released',
                'released_at' => now()->toISOString(),
                'net_pay' => $payroll->net_pay,
            ]),
            'ip_address' => request()->ip() ?? 'system',
            'user_agent' => request()->userAgent() ?? 'Event Listener',
        ]);

        // Create in-app notification
        $this->createNotification($payroll);

        // Send payslip email (if enabled)
        $this->sendPayslipEmail($payroll);
    }

    /**
     * Create in-app notification for the employee
     */
    protected function createNotification($payroll): void
    {
        try {
            $periodName = $payroll->payrollPeriod->start_date->format('M d') . ' - ' . 
                          $payroll->payrollPeriod->end_date->format('M d, Y');

            \App\Models\Notification::create([
                'user_id' => $payroll->user_id,
                'type' => 'payslip_released',
                'title' => 'Payslip Released',
                'message' => "Your payslip for {$periodName} has been released. Net Pay: ₱" . number_format($payroll->net_pay, 2),
                'data' => json_encode([
                    'payroll_id' => $payroll->id,
                    'period_id' => $payroll->payroll_period_id,
                    'net_pay' => $payroll->net_pay,
                ]),
                'is_read' => false,
            ]);

            Log::channel('payroll')->info('Payslip notification created', [
                'payroll_id' => $payroll->id,
                'user_id' => $payroll->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create payslip notification', [
                'payroll_id' => $payroll->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send payslip email with PDF attachment
     */
    protected function sendPayslipEmail($payroll): void
    {
        // Check if email notifications are enabled
        $emailEnabled = CompanySetting::getValue('payslip_email_enabled', true);
        
        if (!$emailEnabled) {
            Log::channel('payroll')->info('Payslip email disabled by settings', [
                'payroll_id' => $payroll->id,
            ]);
            return;
        }

        // Check if user has email
        if (empty($payroll->user->email)) {
            Log::channel('payroll')->warning('Cannot send payslip email - user has no email', [
                'payroll_id' => $payroll->id,
                'user_id' => $payroll->user_id,
            ]);
            return;
        }

        try {
            $attachPdf = CompanySetting::getValue('payslip_email_attach_pdf', true);
            
            Mail::to($payroll->user->email)
                ->send(new PayslipReleased($payroll, $attachPdf));

            Log::channel('payroll')->info('Payslip email sent', [
                'payroll_id' => $payroll->id,
                'user_id' => $payroll->user_id,
                'email' => $payroll->user->email,
                'pdf_attached' => $attachPdf,
            ]);

            // Update payroll to mark email sent
            $payroll->update([
                'email_sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send payslip email', [
                'payroll_id' => $payroll->id,
                'user_id' => $payroll->user_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
