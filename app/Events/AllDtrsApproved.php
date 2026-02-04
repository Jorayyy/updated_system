<?php

namespace App\Events;

use App\Models\PayrollPeriod;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event: All DTRs Approved for Period
 * 
 * Fired when all DTRs in a payroll period are approved.
 * This triggers automatic payroll computation.
 */
class AllDtrsApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PayrollPeriod $period;

    public function __construct(PayrollPeriod $period)
    {
        $this->period = $period;
    }
}
