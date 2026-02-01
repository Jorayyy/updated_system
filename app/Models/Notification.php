<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Notification extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'icon',
        'icon_color',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    /**
     * Notification types
     */
    const TYPE_LEAVE_SUBMITTED = 'leave_submitted';
    const TYPE_LEAVE_APPROVED = 'leave_approved';
    const TYPE_LEAVE_REJECTED = 'leave_rejected';
    const TYPE_PAYROLL_RELEASED = 'payroll_released';
    const TYPE_ATTENDANCE_REMINDER = 'attendance_reminder';
    const TYPE_PC_ASSIGNED = 'pc_assigned';
    const TYPE_PC_RELEASED = 'pc_released';
    const TYPE_SYSTEM = 'system';

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark as unread
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Send notification to a user
     */
    public static function send(
        int $userId, 
        string $type, 
        string $title, 
        string $message, 
        ?string $actionUrl = null,
        ?string $icon = null,
        string $iconColor = 'blue'
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'icon' => $icon ?? self::getDefaultIcon($type),
            'icon_color' => $iconColor,
        ]);
    }

    /**
     * Get default icon for notification type
     */
    protected static function getDefaultIcon(string $type): string
    {
        return match($type) {
            self::TYPE_LEAVE_SUBMITTED => 'calendar',
            self::TYPE_LEAVE_APPROVED => 'check-circle',
            self::TYPE_LEAVE_REJECTED => 'x-circle',
            self::TYPE_PAYROLL_RELEASED => 'currency-dollar',
            self::TYPE_ATTENDANCE_REMINDER => 'clock',
            default => 'bell',
        };
    }

    /**
     * Send to multiple users
     */
    public static function sendToMany(
        array $userIds, 
        string $type, 
        string $title, 
        string $message, 
        ?string $actionUrl = null
    ): void {
        foreach ($userIds as $userId) {
            self::send($userId, $type, $title, $message, $actionUrl);
        }
    }

    /**
     * Send to all users with a specific role
     */
    public static function sendToRole(
        string $role, 
        string $type, 
        string $title, 
        string $message, 
        ?string $actionUrl = null
    ): void {
        $userIds = User::where('role', $role)->where('is_active', true)->pluck('id')->toArray();
        self::sendToMany($userIds, $type, $title, $message, $actionUrl);
    }
}
