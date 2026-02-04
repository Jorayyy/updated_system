<?php

namespace App\Events;

use App\Models\Payroll;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when payroll is approved
 */
class PayrollApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payroll $payroll;
    public int $approvedBy;

    public function __construct(Payroll $payroll, int $approvedBy)
    {
        $this->payroll = $payroll;
        $this->approvedBy = $approvedBy;
    }
}
