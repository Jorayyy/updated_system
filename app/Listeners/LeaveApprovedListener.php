<?php

namespace App\Listeners;

use App\Events\LeaveApproved;
use App\Services\LeaveAutomationService;
use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener: Leave Approved
 * 
 * Handles automated workflows when a leave request is fully approved:
 * 1. Creates DTR entries for leave dates
 * 2. Sends notifications
 * 3. Updates employee calendar
 * 4. Logs the approval for auditing
 */
class LeaveApprovedListener implements ShouldQueue
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
    public function handle(LeaveApproved $event): void
    {
        $leaveRequest = $event->leaveRequest;
        
        Log::channel('leave')->info('LeaveApprovedListener: Processing leave approval', [
            'leave_request_id' => $leaveRequest->id,
            'user_id' => $leaveRequest->user_id,
            'approval_type' => $event->approvalType,
        ]);

        try {
            // Process the approved leave - creates DTR entries
            $results = $this->leaveService->processApprovedLeave($leaveRequest);

            // Send notification to employee
            $this->notifyEmployee($leaveRequest, $results);

            // Notify HR/Admin about successful processing
            $this->notifyManagement($leaveRequest, $results);

            // Log successful processing
            AuditLog::log(
                'leave_approval_processed',
                \App\Models\LeaveRequest::class,
                $leaveRequest->id,
                [],
                [
                    'processed_dates' => count($results['processed_dates']),
                    'skipped_dates' => count($results['skipped_dates']),
                    'errors' => $results['errors'],
                ],
                'Leave approval automation completed'
            );

            Log::channel('leave')->info('LeaveApprovedListener: Processing completed', [
                'leave_request_id' => $leaveRequest->id,
                'processed' => count($results['processed_dates']),
                'skipped' => count($results['skipped_dates']),
                'errors' => count($results['errors']),
            ]);

        } catch (\Exception $e) {
            Log::channel('leave')->error('LeaveApprovedListener: Processing failed', [
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
     * Notify employee about their leave processing
     */
    protected function notifyEmployee($leaveRequest, array $results): void
    {
        $processedCount = count($results['processed_dates']);
        $leaveType = $leaveRequest->leaveType->name ?? 'Leave';

        Notification::send(
            $leaveRequest->user_id,
            'leave_processed',
            'Leave Approved & Processed',
            "Your {$leaveType} request has been approved and processed. " .
            "{$processedCount} day(s) have been recorded in your DTR. " .
            "Period: {$leaveRequest->start_date->format('M d')} - {$leaveRequest->end_date->format('M d, Y')}",
            route('my-dtr-records.index'),
            'calendar-check',
            'green'
        );
    }

    /**
     * Notify management about successful processing
     */
    protected function notifyManagement($leaveRequest, array $results): void
    {
        // Only notify if there were errors or issues
        if (!empty($results['errors'])) {
            $hrUsers = \App\Models\User::where('role', 'hr')
                ->where('is_active', true)
                ->get();

            foreach ($hrUsers as $hr) {
                Notification::send(
                    $hr->id,
                    'leave_processing_warning',
                    'Leave Processing Warning',
                    "Leave for {$leaveRequest->user->name} was processed with issues: " .
                    implode(', ', array_column($results['errors'], 'error')),
                    route('leaves.manage'),
                    'exclamation-triangle',
                    'yellow'
                );
            }
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
                'leave_processing_failed',
                'Leave Processing Failed',
                "Failed to process leave for {$leaveRequest->user->name}: {$errorMessage}. " .
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
    public function failed(LeaveApproved $event, \Throwable $exception): void
    {
        Log::channel('leave')->error('LeaveApprovedListener: Job failed permanently', [
            'leave_request_id' => $event->leaveRequest->id,
            'error' => $exception->getMessage(),
        ]);

        // Record the failure
        AuditLog::log(
            'leave_processing_failed',
            \App\Models\LeaveRequest::class,
            $event->leaveRequest->id,
            [],
            ['error' => $exception->getMessage()],
            'Leave approval automation failed permanently'
        );
    }
}
