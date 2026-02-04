<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event: Attendance Processed
 * 
 * Fired when attendance is finalized (time_out recorded).
 * Can be used for real-time notifications or logging.
 */
class AttendanceProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Attendance $attendance;

    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }
}
