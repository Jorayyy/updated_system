<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'time_in',
        'first_break_out',
        'first_break_in',
        'lunch_break_out',
        'lunch_break_in',
        'second_break_out',
        'second_break_in',
        'time_out',
        'status',
        'current_step',
        'total_work_minutes',
        'total_break_minutes',
        'overtime_minutes',
        'undertime_minutes',
        'late_minutes',
        'night_diff_minutes',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'first_break_out' => 'datetime',
        'first_break_in' => 'datetime',
        'lunch_break_out' => 'datetime',
        'lunch_break_in' => 'datetime',
        'second_break_out' => 'datetime',
        'second_break_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    // Sequential steps in order
    const STEPS = [
        'time_in' => ['label' => 'IN', 'color' => 'blue', 'action' => 'Clock In'],
        'first_break_out' => ['label' => '1st BREAK OUT', 'color' => 'yellow', 'action' => '1st Break Out'],
        'first_break_in' => ['label' => '1st BREAK IN', 'color' => 'green', 'action' => '1st Break In'],
        'lunch_break_out' => ['label' => 'LUNCH BREAK OUT', 'color' => 'orange', 'action' => 'Lunch Out'],
        'lunch_break_in' => ['label' => 'LUNCH BREAK IN', 'color' => 'pink', 'action' => 'Lunch In'],
        'second_break_out' => ['label' => '2nd BREAK OUT', 'color' => 'cyan', 'action' => '2nd Break Out'],
        'second_break_in' => ['label' => '2nd BREAK IN', 'color' => 'lime', 'action' => '2nd Break In'],
        'time_out' => ['label' => 'OUT', 'color' => 'red', 'action' => 'Clock Out'],
    ];

    /**
     * Get the user that owns the attendance
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the approved overtime request for this attendance record
     * Based on user_id and date.
     */
    public function overtimeRequest()
    {
        return $this->hasOne(OvertimeRequest::class, 'user_id', 'user_id')
                    ->whereColumn('date', 'date')
                    ->where('status', 'approved');
    }

    /**
     * Get the breaks for this attendance (legacy support)
     */
    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    /**
     * Get the timekeeping transactions for this attendance
     */
    public function timekeepingTransactions(): HasMany
    {
        return $this->hasMany(TimekeepingTransaction::class);
    }

    /**
     * Check if employee is currently on break (any break)
     */
    public function isOnBreak(): bool
    {
        return in_array($this->current_step, ['first_break_out', 'lunch_break_out', 'second_break_out']);
    }

    /**
     * Get current step info
     */
    public function getCurrentStepInfo(): array
    {
        return self::STEPS[$this->current_step] ?? ['label' => 'Unknown', 'color' => 'gray', 'action' => 'Unknown'];
    }

    /**
     * Get next step to perform
     */
    public function getNextStep(): ?string
    {
        $steps = array_keys(self::STEPS);
        $currentIndex = array_search($this->current_step, $steps);
        
        if ($currentIndex === false || $currentIndex >= count($steps) - 1) {
            return null;
        }
        
        return $steps[$currentIndex + 1];
    }

    /**
     * Get next step info
     */
    public function getNextStepInfo(): ?array
    {
        $nextStep = $this->getNextStep();
        return $nextStep ? self::STEPS[$nextStep] : null;
    }

    /**
     * Check if employee has timed in
     */
    public function hasTimedIn(): bool
    {
        return !is_null($this->time_in);
    }

    /**
     * Check if employee has timed out
     */
    public function hasTimedOut(): bool
    {
        return !is_null($this->time_out);
    }

    /**
     * Check if day is completed
     */
    public function isCompleted(): bool
    {
        return $this->current_step === 'completed' || !is_null($this->time_out);
    }

    /**
     * Calculate total work minutes excluding breaks
     */
    public function calculateWorkMinutes(): int
    {
        if (!$this->time_in || !$this->time_out) {
            return 0;
        }

        $totalMinutes = $this->time_in->diffInMinutes($this->time_out);
        
        $service = app(\App\Services\AttendanceService::class);
        $schedule = $service->getScheduleForUser($this->user, $this->date);
        $totalScheduledBreak = $schedule['break_minutes'] ?? 90;

        // Actual total break captured (enforces minimums per break type)
        $capturedBreak = $this->calculateBreakMinutes();
        
        // Subtract the larger of the two to ensure overbreaks are captured
        // and minimum breaks are enforced even if punches are missing
        $breakToSubtract = max($capturedBreak, $totalScheduledBreak);

        return max(0, $totalMinutes - $breakToSubtract);
    }

    /**
     * Calculate total break minutes from sequential breaks
     * Enforces the scheduled break as a minimum if actual is shorter
     */
    public function calculateBreakMinutes(): int
    {
        $totalBreak = 0;
        
        $service = app(\App\Services\AttendanceService::class);
        $schedule = $service->getScheduleForUser($this->user, $this->date);

        // 1st Break
        if ($this->first_break_out && $this->first_break_in) {
            $actual = $this->first_break_out->diffInMinutes($this->first_break_in);
            $scheduled = $schedule['first_break_minutes'] ?? 15;
            $totalBreak += max($actual, $scheduled);
        }

        // Lunch Break
        if ($this->lunch_break_out && $this->lunch_break_in) {
            $actual = $this->lunch_break_out->diffInMinutes($this->lunch_break_in);
            $scheduled = $schedule['lunch_break_minutes'] ?? 60;
            $totalBreak += max($actual, $scheduled);
        }

        // 2nd Break
        if ($this->second_break_out && $this->second_break_in) {
            $actual = $this->second_break_out->diffInMinutes($this->second_break_in);
            $scheduled = $schedule['second_break_minutes'] ?? 15;
            $totalBreak += max($actual, $scheduled);
        }

        return $totalBreak;
    }

    /**
     * Calculate night differential minutes (10 PM to 6 AM)
     */
    public function calculateNightDiffMinutes(): int
    {
        if (!$this->time_in || !$this->time_out) {
            return 0;
        }

        $nightStart = 22; // 10 PM
        $nightEnd = 6;    // 6 AM
        
        $totalNightMinutes = 0;
        $current = $this->time_in->copy()->startOfMinute();
        $end = $this->time_out->copy()->startOfMinute();

        while ($current->lt($end)) {
            $hour = $current->hour;
            // Check if current hour is within 10PM - 6AM
            if ($hour >= $nightStart || $hour < $nightEnd) {
                // Check if this minute is part of a break
                if (!$this->isWithinBreak($current)) {
                    $totalNightMinutes++;
                }
            }
            $current->addMinute();
        }

        return $totalNightMinutes;
    }

    /**
     * Helper to check if a timestamp falls within any break period
     */
    protected function isWithinBreak(Carbon $time): bool
    {
        // 1st Break
        if ($this->first_break_out && $this->first_break_in) {
            if ($time->gte($this->first_break_out) && $time->lt($this->first_break_in)) {
                return true;
            }
        }

        // Lunch Break
        if ($this->lunch_break_out && $this->lunch_break_in) {
            if ($time->gte($this->lunch_break_out) && $time->lt($this->lunch_break_in)) {
                return true;
            }
        }

        // 2nd Break
        if ($this->second_break_out && $this->second_break_in) {
            if ($time->gte($this->second_break_out) && $time->lt($this->second_break_in)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get first break duration in minutes
     */
    public function getFirstBreakMinutes(): int
    {
        if ($this->first_break_out && $this->first_break_in) {
            return $this->first_break_out->diffInMinutes($this->first_break_in);
        }
        return 0;
    }

    /**
     * Get lunch break duration in minutes
     */
    public function getLunchBreakMinutes(): int
    {
        if ($this->lunch_break_out && $this->lunch_break_in) {
            return $this->lunch_break_out->diffInMinutes($this->lunch_break_in);
        }
        return 0;
    }

    /**
     * Get second break duration in minutes
     */
    public function getSecondBreakMinutes(): int
    {
        if ($this->second_break_out && $this->second_break_in) {
            return $this->second_break_out->diffInMinutes($this->second_break_in);
        }
        return 0;
    }

    /**
     * Calculate late minutes based on a given work start time
     */
    public function calculateLateMinutes(string $workStartTime, int $gracePeriod = 15): int
    {
        if (!$this->time_in) {
            return 0;
        }

        $workStart = $this->time_in->copy()->setTimeFromTimeString($workStartTime);

        // Night shift logic: If work starts e.g. at 21:00 and they clock in at 00:30,
        // it means their shift started "yesterday".
        if ($this->time_in->hour < 12 && Carbon::parse($workStartTime)->hour >= 12) {
            $workStart->subDay();
        }

        $lateThreshold = $workStart->copy()->addMinutes($gracePeriod);

        if ($this->time_in->gt($lateThreshold)) {
            return (int) $workStart->diffInMinutes($this->time_in);
        }

        return 0;
    }

    /**
     * Calculate overtime minutes
     */
    public function calculateOvertimeMinutes(int $standardWorkMinutes = 480): int
    {
        $actualWork = $this->calculateWorkMinutes();
        if ($actualWork > $standardWorkMinutes) {
            return $actualWork - $standardWorkMinutes;
        }
        return 0;
    }

    /**
     * Calculate undertime minutes
     */
    public function calculateUndertimeMinutes(int $standardWorkMinutes = 480): int
    {
        $actualWork = $this->calculateWorkMinutes();
        if ($actualWork < $standardWorkMinutes) {
            return $standardWorkMinutes - $actualWork;
        }
        return 0;
    }

    /**
     * Format total work time as hours and minutes
     */
    public function getFormattedWorkTimeAttribute(): string
    {
        $hours = floor($this->total_work_minutes / 60);
        $minutes = $this->total_work_minutes % 60;
        return sprintf('%d hrs %d mins', $hours, $minutes);
    }

    /**
     * Format total break time as hours and minutes
     */
    public function getFormattedBreakTimeAttribute(): string
    {
        $hours = floor($this->total_break_minutes / 60);
        $minutes = $this->total_break_minutes % 60;
        return sprintf('%d hrs %d mins', $hours, $minutes);
    }

    /**
     * Get all steps with their status and times
     */
    public function getStepsStatus(bool $canProceed = true): array
    {
        $steps = [];
        $stepOrder = array_keys(self::STEPS);
        $currentStepIndex = array_search($this->current_step, $stepOrder);
        
        foreach (self::STEPS as $step => $info) {
            $stepIndex = array_search($step, $stepOrder);
            $time = $this->{$step};
            
            $isCompleted = $time !== null;
            $isCurrent = $step === $this->current_step && !$this->isCompleted();
            $isNext = $canProceed && !$this->isCompleted() && $this->getNextStep() === $step;
            
            $steps[$step] = [
                'label' => $info['label'],
                'color' => $info['color'],
                'action' => $info['action'],
                'time' => $time ? $time->format('h:i A') : null,
                'is_completed' => $isCompleted,
                'is_current' => $isCurrent,
                'is_next' => $isNext,
            ];
        }
        
        return $steps;
    }

    /**
     * Scope for today's attendances
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope for a specific date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
