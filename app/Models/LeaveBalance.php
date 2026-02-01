<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'year',
        'allocated_days',
        'used_days',
        'remaining_days',
    ];

    protected $casts = [
        'allocated_days' => 'decimal:1',
        'used_days' => 'decimal:1',
        'remaining_days' => 'decimal:1',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the leave type
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Update remaining days
     */
    public function updateRemainingDays(): void
    {
        $this->remaining_days = $this->allocated_days - $this->used_days;
        $this->save();
    }

    /**
     * Deduct days from balance
     */
    public function deductDays(float $days): bool
    {
        if ($this->remaining_days < $days) {
            return false;
        }

        $this->used_days += $days;
        $this->remaining_days = $this->allocated_days - $this->used_days;
        $this->save();

        return true;
    }

    /**
     * Restore days to balance
     */
    public function restoreDays(float $days): void
    {
        $this->used_days = max(0, $this->used_days - $days);
        $this->remaining_days = $this->allocated_days - $this->used_days;
        $this->save();
    }

    /**
     * Scope for current year
     */
    public function scopeCurrentYear($query)
    {
        return $query->where('year', date('Y'));
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
