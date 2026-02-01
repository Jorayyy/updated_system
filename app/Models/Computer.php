<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Computer extends Model
{
    use HasFactory;

    protected $fillable = [
        'pc_number',
        'name',
        'location',
        'specs',
        'status',
        'current_user_id',
        'assigned_at',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Status constants
     */
    const STATUS_AVAILABLE = 'available';
    const STATUS_IN_USE = 'in_use';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_RETIRED = 'retired';

    /**
     * Get the user currently using this computer
     */
    public function currentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    /**
     * Scope for available computers
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE)
                     ->where('is_active', true);
    }

    /**
     * Scope for in-use computers
     */
    public function scopeInUse($query)
    {
        return $query->where('status', self::STATUS_IN_USE);
    }

    /**
     * Scope for active computers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Assign computer to a user
     */
    public function assignTo(User $user): void
    {
        $this->update([
            'current_user_id' => $user->id,
            'status' => self::STATUS_IN_USE,
            'assigned_at' => now(),
        ]);

        // Log the assignment
        AuditLog::log(
            'computer_assigned',
            self::class,
            $this->id,
            null,
            ['user_id' => $user->id, 'pc_number' => $this->pc_number],
            "PC {$this->pc_number} assigned to {$user->name}"
        );
    }

    /**
     * Release computer from current user
     */
    public function release(): void
    {
        $previousUser = $this->currentUser;
        
        $this->update([
            'current_user_id' => null,
            'status' => self::STATUS_AVAILABLE,
            'assigned_at' => null,
        ]);

        // Log the release
        if ($previousUser) {
            AuditLog::log(
                'computer_released',
                self::class,
                $this->id,
                ['user_id' => $previousUser->id],
                null,
                "PC {$this->pc_number} released by {$previousUser->name}"
            );
        }
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_AVAILABLE => 'green',
            self::STATUS_IN_USE => 'blue',
            self::STATUS_MAINTENANCE => 'yellow',
            self::STATUS_RETIRED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_IN_USE => 'In Use',
            self::STATUS_MAINTENANCE => 'Maintenance',
            self::STATUS_RETIRED => 'Retired',
            default => 'Unknown',
        };
    }
}
