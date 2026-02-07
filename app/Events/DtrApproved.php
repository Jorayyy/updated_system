<?php

namespace App\Events;

use App\Models\DailyTimeRecord;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event: DTR Approved
 * 
 * Fired when a Daily Time Record is approved.
 * This event triggers payroll computation for the employee.
 */
class DtrApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public DailyTimeRecord $dtr;
    public User $approver;
    public int $approvedBy;
    public string $approvalLevel;

    public function __construct(DailyTimeRecord $dtr, User $approver, string $approvalLevel = 'supervisor')
    {
        $this->dtr = $dtr;
        $this->approver = $approver;
        $this->approvedBy = $approver->id;
        $this->approvalLevel = $approvalLevel;
    }
}
