<?php

namespace App\Events;

use App\Models\DailyTimeRecord;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event: DTR Generated
 * 
 * Fired when a Daily Time Record is created or regenerated.
 * This event can trigger notifications or other downstream processes.
 */
class DtrGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public DailyTimeRecord $dtr;

    public function __construct(DailyTimeRecord $dtr)
    {
        $this->dtr = $dtr;
    }
}
