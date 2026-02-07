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
     * Get the breaks for this attendance (legacy support)
     */
    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
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
        
        // If the user has a schedule, use its break duration
        $user = $this->user;
        $breakMinutes = 0;
        
        if ($user && $user->account && $user->account->activeSchedule) {
            $breakMinutes = $user->account->activeSchedule->break_duration_minutes;
        } else {
            // Fallback to manual breaks if no active schedule found
            $breakMinutes = $this->calculateBreakMinutes();
        }

        return max(0, $totalMinutes - $breakMinutes);
    }

    /**
     * Calculate total break minutes from sequential breaks
     */
    public function calculateBreakMinutes(): int
    {
        $totalBreak = 0;

        // 1st Break
        if ($this->first_break_out && $this->first_break_in) {
            $totalBreak += $this->first_break_out->diffInMinutes($this->first_break_in);
        }

        // Lunch Break
        if ($this->lunch_break_out && $this->lunch_break_in) {
            $totalBreak += $this->lunch_break_out->diffInMinutes($this->lunch_break_in);
        }

        // 2nd Break
        if ($this->second_break_out && $this->second_break_in) {
            $totalBreak += $this->second_break_out->diffInMinutes($this->second_break_in);
        }

        return $totalBreak;
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
