<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Concern extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'reported_by',
        'assigned_to',
        'title',
        'category',
        'priority',
        'description',
        'date_affected',
        'affected_punch',
        'location',
        'status',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
        'acknowledged_at',
        'first_response_at',
        'due_date',
        'is_confidential',
        'attachment',
    ];

    protected $casts = [
        'date_affected' => 'date',
        'resolved_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'first_response_at' => 'datetime',
        'due_date' => 'datetime',
        'is_confidential' => 'boolean',
    ];

    /**
     * Category labels
     */
    public const CATEGORIES = [
        'technical' => 'Technical Issue',
        'network' => 'Network/Connectivity',
        'facilities' => 'Facilities/Equipment',
        'timekeeping' => 'Timekeeping (TK) Complaint',
        'schedule' => 'Schedule/Shift',
        'payroll' => 'Payroll/Compensation',
        'hr_related' => 'HR Related',
        'training' => 'Training/Development',
        'performance' => 'Performance/Coaching',
        'safety' => 'Safety/Health',
        'suggestion' => 'Suggestion/Improvement',
        'complaint' => 'General Complaint',
        'other' => 'Other',
    ];

    /**
     * Priority levels
     */
    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ];

    /**
     * Status options
     */
    public const STATUSES = [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'pending_info' => 'Pending Info',
        'escalated' => 'Escalated',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Boot function to generate ticket number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($concern) {
            if (!$concern->ticket_number) {
                $concern->ticket_number = self::generateTicketNumber();
            }
        });
    }

    /**
     * Generate unique ticket number
     */
    public static function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $date = now()->format('ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }

    /**
     * Get the reporter
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the assignee
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the resolver
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get comments
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ConcernComment::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get activities
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ConcernActivity::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            'low' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'medium' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
            'critical' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'open' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'pending_info' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            'escalated' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
            'resolved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    /**
     * Get category badge class
     */
    public function getCategoryBadgeAttribute(): string
    {
        return match($this->category) {
            'technical' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
            'network' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-300',
            'facilities' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300',
            'schedule' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'payroll' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'hr_related' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300',
            'training' => 'bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-300',
            'performance' => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-300',
            'safety' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'suggestion' => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300',
            'complaint' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    /**
     * Check if concern is open
     */
    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress', 'pending_info', 'escalated']);
    }

    /**
     * Check if concern is closed
     */
    public function isClosed(): bool
    {
        return in_array($this->status, ['resolved', 'closed', 'cancelled']);
    }

    /**
     * Log activity
     */
    public function logActivity(string $action, string $description, ?array $oldValues = null, ?array $newValues = null): void
    {
        $this->activities()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    /**
     * Scopes
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'pending_info', 'escalated']);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', ['resolved', 'closed', 'cancelled']);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'critical']);
    }
}
