<?php

namespace App\Events;

use App\Models\Payroll;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when payroll is computed for an employee
 */
class PayrollComputed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payroll $payroll;
    public string $computationSource;

    public function __construct(Payroll $payroll, string $source = 'manual')
    {
        $this->payroll = $payroll;
        $this->computationSource = $source;
    }
}
