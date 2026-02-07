<?php

namespace App\Events;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event: Leave Request Cancelled
 * 
 * Fired when a previously approved leave request is cancelled.
 * This triggers automated workflows:
 * 1. Restore leave balance
 * 2. Remove/update DTR entries for leave dates
 * 3. Update attendance records
 */
class LeaveCancelled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public LeaveRequest $leaveRequest;
    public User $cancelledBy;
    public string $reason;
    public bool $wasApproved;

    /**
     * Create a new event instance.
     *
     * @param LeaveRequest $leaveRequest The cancelled leave request
     * @param User $cancelledBy The user who cancelled
     * @param string $reason Reason for cancellation
     * @param bool $wasApproved Whether it was already approved when cancelled
     */
    public function __construct(
        LeaveRequest $leaveRequest, 
        User $cancelledBy,
        string $reason = '',
        bool $wasApproved = false
    ) {
        $this->leaveRequest = $leaveRequest;
        $this->cancelledBy = $cancelledBy;
        $this->reason = $reason;
        $this->wasApproved = $wasApproved;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('leave-automation'),
        ];
    }

    /**
     * Get the leave request details for logging
     */
    public function getLogContext(): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'user_id' => $this->leaveRequest->user_id,
            'user_name' => $this->leaveRequest->user->name ?? 'Unknown',
            'leave_type' => $this->leaveRequest->leaveType->name ?? 'Unknown',
            'start_date' => $this->leaveRequest->start_date->toDateString(),
            'end_date' => $this->leaveRequest->end_date->toDateString(),
            'total_days' => $this->leaveRequest->total_days,
            'cancelled_by_id' => $this->cancelledBy->id,
            'cancelled_by_name' => $this->cancelledBy->name,
            'reason' => $this->reason,
            'was_approved' => $this->wasApproved,
            'cancelled_at' => now()->toIso8601String(),
        ];
    }
}
