<?php

namespace App\Listeners;

use App\Events\LeaveCancelled;
use App\Services\LeaveAutomationService;
use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener: Leave Cancelled
 * 
 * Handles automated workflows when an approved leave is cancelled:
 * 1. Reverts DTR entries for leave dates
 * 2. Restores leave balance
 * 3. Sends notifications
 * 4. Logs the cancellation for auditing
 */
class LeaveCancelledListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds before the job should be retried after a failure.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The queue connection that the job should be sent to.
     *
     * @var string
     */
    public $connection = 'database';

    /**
     * The queue the job should be sent to.
     *
     * @var string
     */
    public $queue = 'leave-automation';

    protected LeaveAutomationService $leaveService;

    /**
     * Create the event listener.
     */
    public function __construct(LeaveAutomationService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    /**
     * Handle the event.
     */
    public function handle(LeaveCancelled $event): void
    {
        $leaveRequest = $event->leaveRequest;
        
        Log::channel('leave')->info('LeaveCancelledListener: Processing leave cancellation', [
            'leave_request_id' => $leaveRequest->id,
            'user_id' => $leaveRequest->user_id,
            'was_approved' => $event->wasApproved,
        ]);

        // Only process if the leave was previously approved
        if (!$event->wasApproved) {
            Log::channel('leave')->info('LeaveCancelledListener: Leave was not approved, skipping DTR revert', [
                'leave_request_id' => $leaveRequest->id,
            ]);
            return;
        }

        try {
            // Process the cancellation - reverts DTR entries and restores balance
            $results = $this->leaveService->processCancelledLeave($leaveRequest, $event->wasApproved);

            // Send notification to employee
            $this->notifyEmployee($leaveRequest, $results, $event->reason);

            // Notify HR about cancellation
            $this->notifyHr($leaveRequest, $results, $event);

            // Log successful processing
            AuditLog::log(
                'leave_cancellation_processed',
                \App\Models\LeaveRequest::class,
                $leaveRequest->id,
                [],
                [
                    'reverted_dates' => count($results['reverted_dates']),
                    'balance_restored' => $results['balance_restored'],
                    'errors' => $results['errors'],
                    'reason' => $event->reason,
                ],
                'Leave cancellation automation completed'
            );

            Log::channel('leave')->info('LeaveCancelledListener: Processing completed', [
                'leave_request_id' => $leaveRequest->id,
                'reverted' => count($results['reverted_dates']),
                'balance_restored' => $results['balance_restored'],
                'errors' => count($results['errors']),
            ]);

        } catch (\Exception $e) {
            Log::channel('leave')->error('LeaveCancelledListener: Processing failed', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Notify management about the failure
            $this->notifyProcessingFailure($leaveRequest, $e->getMessage());

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Notify employee about their leave cancellation processing
     */
    protected function notifyEmployee($leaveRequest, array $results, string $reason): void
    {
        $revertedCount = count($results['reverted_dates']);
        $leaveType = $leaveRequest->leaveType->name ?? 'Leave';
        $balanceMsg = $results['balance_restored'] 
            ? " Your leave balance has been restored." 
            : "";

        $reasonMsg = $reason ? " Reason: {$reason}" : "";

        Notification::send(
            $leaveRequest->user_id,
            'leave_cancelled_processed',
            'Leave Cancelled',
            "Your {$leaveType} request has been cancelled. " .
            "{$revertedCount} day(s) have been reverted in your DTR.{$balanceMsg}{$reasonMsg}",
            route('leaves.index'),
            'calendar-x',
            'yellow'
        );
    }

    /**
     * Notify HR about the cancellation
     */
    protected function notifyHr($leaveRequest, array $results, $event): void
    {
        $hrUsers = \App\Models\User::where('role', 'hr')
            ->where('is_active', true)
            ->get();

        $cancelledBy = $event->cancelledBy->name ?? 'Unknown';
        
        foreach ($hrUsers as $hr) {
            Notification::send(
                $hr->id,
                'leave_cancellation_notice',
                'Leave Cancellation Notice',
                "Leave for {$leaveRequest->user->name} was cancelled by {$cancelledBy}. " .
                "{$leaveRequest->total_days} days reverted.",
                route('leaves.manage'),
                'calendar-x',
                'orange'
            );
        }
    }

    /**
     * Notify management about processing failure
     */
    protected function notifyProcessingFailure($leaveRequest, string $errorMessage): void
    {
        $adminUsers = \App\Models\User::whereIn('role', ['admin', 'hr'])
            ->where('is_active', true)
            ->get();

        foreach ($adminUsers as $admin) {
            Notification::send(
                $admin->id,
                'leave_cancellation_failed',
                'Leave Cancellation Processing Failed',
                "Failed to process leave cancellation for {$leaveRequest->user->name}: {$errorMessage}. " .
                "Manual intervention may be required.",
                route('leaves.manage'),
                'x-circle',
                'red'
            );
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(LeaveCancelled $event, \Throwable $exception): void
    {
        Log::channel('leave')->error('LeaveCancelledListener: Job failed permanently', [
            'leave_request_id' => $event->leaveRequest->id,
            'error' => $exception->getMessage(),
        ]);

        // Record the failure
        AuditLog::log(
            'leave_cancellation_failed',
            \App\Models\LeaveRequest::class,
            $event->leaveRequest->id,
            [],
            ['error' => $exception->getMessage()],
            'Leave cancellation automation failed permanently'
        );
    }
}
