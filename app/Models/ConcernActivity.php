<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConcernActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'concern_id',
        'user_id',
        'action',
        'description',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the concern
     */
    public function concern(): BelongsTo
    {
        return $this->belongsTo(Concern::class);
    }

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'created' => 'plus-circle',
            'status_changed' => 'refresh',
            'assigned' => 'user-plus',
            'unassigned' => 'user-minus',
            'commented' => 'chat-bubble-left',
            'priority_changed' => 'exclamation-triangle',
            'resolved' => 'check-circle',
            'closed' => 'lock-closed',
            'reopened' => 'lock-open',
            default => 'information-circle',
        };
    }

    /**
     * Get action color
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'blue',
            'status_changed' => 'yellow',
            'assigned' => 'purple',
            'unassigned' => 'gray',
            'commented' => 'indigo',
            'priority_changed' => 'orange',
            'resolved' => 'green',
            'closed' => 'gray',
            'reopened' => 'blue',
            default => 'gray',
        };
    }
}
