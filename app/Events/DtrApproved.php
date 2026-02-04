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

    public function __construct(DailyTimeRecord $dtr, User $approver)
    {
        $this->dtr = $dtr;
        $this->approver = $approver;
    }
}
