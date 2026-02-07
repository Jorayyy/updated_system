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
 * Event: Leave Request Approved
 * 
 * Fired when a leave request receives full approval (both HR and Admin).
 * This triggers automated workflows:
 * 1. Create DTR entries for leave dates
 * 2. Deduct leave balance
 * 3. Update attendance records
 * 4. Log for payroll processing
 */
class LeaveApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public LeaveRequest $leaveRequest;
    public User $approver;
    public string $approvalType;

    /**
     * Create a new event instance.
     *
     * @param LeaveRequest $leaveRequest The approved leave request
     * @param User $approver The user who gave final approval
     * @param string $approvalType Either 'hr', 'admin', or 'full' (both)
     */
    public function __construct(
        LeaveRequest $leaveRequest, 
        User $approver,
        string $approvalType = 'full'
    ) {
        $this->leaveRequest = $leaveRequest;
        $this->approver = $approver;
        $this->approvalType = $approvalType;
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
            'approver_id' => $this->approver->id,
            'approver_name' => $this->approver->name,
            'approval_type' => $this->approvalType,
            'approved_at' => now()->toIso8601String(),
        ];
    }
}
