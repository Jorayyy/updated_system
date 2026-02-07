<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        // Hierarchical approval fields
        'hr_approved_by',
        'hr_approved_at',
        'hr_status',
        'admin_approved_by',
        'admin_approved_at',
        'admin_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'hr_approved_at' => 'datetime',
        'admin_approved_at' => 'datetime',
        'total_days' => 'decimal:1',
    ];

    /**
     * Get the user that requested the leave
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
     * Get the approver (legacy)
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the HR approver
     */
    public function hrApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_approved_by');
    }

    /**
     * Get the Admin approver
     */
    public function adminApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    /**
     * Get the DTR entries created from this leave request
     */
    public function dtrEntries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DailyTimeRecord::class, 'leave_request_id');
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if HR has approved
     */
    public function isHrApproved(): bool
    {
        return $this->hr_status === 'approved';
    }

    /**
     * Check if Admin has approved
     */
    public function isAdminApproved(): bool
    {
        return $this->admin_status === 'approved';
    }

    /**
     * Check if both HR and Admin have approved
     */
    public function isFullyApproved(): bool
    {
        return $this->hr_status === 'approved' && $this->admin_status === 'approved';
    }

    /**
     * Check if awaiting HR approval
     */
    public function needsHrApproval(): bool
    {
        return $this->status === 'pending' && $this->hr_status === 'pending';
    }

    /**
     * Check if awaiting Admin approval
     */
    public function needsAdminApproval(): bool
    {
        return $this->status === 'pending' && $this->admin_status === 'pending';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    /**
     * Get HR status badge class
     */
    public function getHrStatusBadgeAttribute(): string
    {
        return match($this->hr_status) {
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    /**
     * Get Admin status badge class
     */
    public function getAdminStatusBadgeAttribute(): string
    {
        return match($this->admin_status) {
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for pending HR approval
     */
    public function scopePendingHrApproval($query)
    {
        return $query->where('status', 'pending')->where('hr_status', 'pending');
    }

    /**
     * Scope for pending Admin approval
     */
    public function scopePendingAdminApproval($query)
    {
        return $query->where('status', 'pending')->where('admin_status', 'pending');
    }
}
