<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimekeepingTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'transaction_type',
        'transaction_time',
        'ip_address',
        'device_info',
        'notes',
        'status',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'transaction_time' => 'datetime',
        'voided_at' => 'datetime',
    ];

    /**
     * Transaction types with labels and categories
     */
    public const TRANSACTION_TYPES = [
        // Attendance
        'time_in' => ['label' => 'Time In', 'category' => 'attendance', 'icon' => 'login', 'color' => 'green'],
        'time_out' => ['label' => 'Time Out', 'category' => 'attendance', 'icon' => 'logout', 'color' => 'red'],
        
        // Breaks
        'break_start' => ['label' => 'Break Start', 'category' => 'break', 'icon' => 'pause', 'color' => 'yellow'],
        'break_end' => ['label' => 'Break End', 'category' => 'break', 'icon' => 'play', 'color' => 'green'],
        'lunch_start' => ['label' => 'Lunch Start', 'category' => 'break', 'icon' => 'pause', 'color' => 'orange'],
        'lunch_end' => ['label' => 'Lunch End', 'category' => 'break', 'icon' => 'play', 'color' => 'green'],
        
        // Aux Codes - Agent Activities
        'aux_meeting' => ['label' => 'Meeting', 'category' => 'aux', 'icon' => 'users', 'color' => 'blue'],
        'aux_training' => ['label' => 'Training', 'category' => 'aux', 'icon' => 'academic-cap', 'color' => 'purple'],
        'aux_coaching' => ['label' => 'Coaching/Feedback', 'category' => 'aux', 'icon' => 'chat', 'color' => 'indigo'],
        'aux_team_huddle' => ['label' => 'Team Huddle', 'category' => 'aux', 'icon' => 'user-group', 'color' => 'cyan'],
        'aux_one_on_one' => ['label' => '1-on-1 Session', 'category' => 'aux', 'icon' => 'user', 'color' => 'teal'],
        
        // Technical Issues
        'aux_system_issue' => ['label' => 'System Issue', 'category' => 'technical', 'icon' => 'exclamation', 'color' => 'red'],
        'aux_network_issue' => ['label' => 'Network Issue', 'category' => 'technical', 'icon' => 'wifi', 'color' => 'red'],
        'aux_pc_issue' => ['label' => 'PC/Hardware Issue', 'category' => 'technical', 'icon' => 'desktop', 'color' => 'red'],
        'aux_phone_issue' => ['label' => 'Phone/Softphone Issue', 'category' => 'technical', 'icon' => 'phone', 'color' => 'red'],
        
        // Personal
        'aux_personal' => ['label' => 'Personal Break', 'category' => 'personal', 'icon' => 'user', 'color' => 'gray'],
        'aux_restroom' => ['label' => 'Restroom', 'category' => 'personal', 'icon' => 'user', 'color' => 'gray'],
        'aux_wellness' => ['label' => 'Wellness Break', 'category' => 'personal', 'icon' => 'heart', 'color' => 'pink'],
        
        // Work Related
        'aux_after_call_work' => ['label' => 'After Call Work', 'category' => 'work', 'icon' => 'document-text', 'color' => 'blue'],
        'aux_documentation' => ['label' => 'Documentation', 'category' => 'work', 'icon' => 'document', 'color' => 'blue'],
        'aux_email' => ['label' => 'Email/Correspondence', 'category' => 'work', 'icon' => 'mail', 'color' => 'blue'],
        'aux_research' => ['label' => 'Research', 'category' => 'work', 'icon' => 'search', 'color' => 'blue'],
        'aux_escalation' => ['label' => 'Escalation Handling', 'category' => 'work', 'icon' => 'arrow-up', 'color' => 'orange'],
        'aux_quality_check' => ['label' => 'Quality Check', 'category' => 'work', 'icon' => 'badge-check', 'color' => 'green'],
        
        // Ready States
        'aux_available' => ['label' => 'Available/Ready', 'category' => 'ready', 'icon' => 'check-circle', 'color' => 'green'],
        'aux_wrap_up' => ['label' => 'Wrap Up', 'category' => 'ready', 'icon' => 'clock', 'color' => 'yellow'],
        
        // Admin/Manual
        'manual_adjustment' => ['label' => 'Manual Adjustment', 'category' => 'admin', 'icon' => 'pencil', 'color' => 'gray'],
        'overtime_start' => ['label' => 'Overtime Start', 'category' => 'overtime', 'icon' => 'clock', 'color' => 'purple'],
        'overtime_end' => ['label' => 'Overtime End', 'category' => 'overtime', 'icon' => 'clock', 'color' => 'purple'],
    ];

    /**
     * Transaction categories
     */
    public const CATEGORIES = [
        'attendance' => 'Attendance',
        'break' => 'Breaks',
        'aux' => 'Agent Activities',
        'technical' => 'Technical Issues',
        'personal' => 'Personal',
        'work' => 'Work Related',
        'ready' => 'Ready States',
        'overtime' => 'Overtime',
        'admin' => 'Administrative',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attendance record
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get who voided this transaction
     */
    public function voidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * Get transaction label
     */
    public function getLabelAttribute(): string
    {
        return self::TRANSACTION_TYPES[$this->transaction_type]['label'] ?? ucfirst(str_replace('_', ' ', $this->transaction_type));
    }

    /**
     * Get transaction category
     */
    public function getCategoryAttribute(): string
    {
        return self::TRANSACTION_TYPES[$this->transaction_type]['category'] ?? 'other';
    }

    /**
     * Get transaction color
     */
    public function getColorAttribute(): string
    {
        return self::TRANSACTION_TYPES[$this->transaction_type]['color'] ?? 'gray';
    }

    /**
     * Get color badge class
     */
    public function getColorBadgeAttribute(): string
    {
        $color = $this->color;
        return match($color) {
            'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
            'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
            'pink' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300',
            'cyan' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-300',
            'teal' => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    /**
     * Check if transaction is voided
     */
    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    /**
     * Void the transaction
     */
    public function void(string $reason, ?int $userId = null): void
    {
        $this->update([
            'status' => 'voided',
            'voided_by' => $userId ?? auth()->id(),
            'voided_at' => now(),
            'void_reason' => $reason,
        ]);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('transaction_time', today());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        $types = collect(self::TRANSACTION_TYPES)
            ->filter(fn($t) => $t['category'] === $category)
            ->keys()
            ->toArray();
        
        return $query->whereIn('transaction_type', $types);
    }

    /**
     * Get grouped transaction types for dropdown
     */
    public static function getGroupedTransactionTypes(): array
    {
        $grouped = [];
        foreach (self::TRANSACTION_TYPES as $key => $type) {
            $category = self::CATEGORIES[$type['category']] ?? 'Other';
            $grouped[$category][$key] = $type['label'];
        }
        return $grouped;
    }
}
