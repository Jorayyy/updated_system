<?php

namespace App\Events;

use App\Models\Payroll;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when payroll is released to employee
 */
class PayrollReleased
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payroll $payroll;
    public int $releasedBy;

    public function __construct(Payroll $payroll, int $releasedBy)
    {
        $this->payroll = $payroll;
        $this->releasedBy = $releasedBy;
    }
}
