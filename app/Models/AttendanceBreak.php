<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AttendanceBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
        'break_type',
        'duration_minutes',
        'notes',
    ];

    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    /**
     * Get the attendance that owns this break
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Check if break is ongoing (not ended)
     */
    public function isOngoing(): bool
    {
        return is_null($this->break_end);
    }

    /**
     * Calculate duration in minutes
     */
    public function calculateDuration(): int
    {
        if (!$this->break_start || !$this->break_end) {
            return 0;
        }

        return $this->break_start->diffInMinutes($this->break_end);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->isOngoing()) {
            $minutes = $this->break_start->diffInMinutes(now());
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            return sprintf('%d hrs %d mins (ongoing)', $hours, $mins);
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        return sprintf('%d hrs %d mins', $hours, $minutes);
    }

    /**
     * Get break type label
     */
    public function getBreakTypeLabelAttribute(): string
    {
        return match($this->break_type) {
            'lunch' => 'Lunch Break',
            'short_break' => 'Short Break',
            'personal' => 'Personal Break',
            'other' => 'Other',
            default => ucfirst($this->break_type),
        };
    }

    /**
     * Scope for ongoing breaks
     */
    public function scopeOngoing($query)
    {
        return $query->whereNull('break_end');
    }

    /**
     * Scope for completed breaks
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('break_end');
    }
}
