<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Daily Time Record (DTR) Model
 * 
 * Represents a single day's attendance record for payroll processing.
 * DTR is generated from Attendance data and requires approval before
 * triggering payroll computation.
 */
class DailyTimeRecord extends Model
{
    use HasFactory;

    protected $table = 'daily_time_records';

    protected $fillable = [
        'user_id',
        'attendance_id',
        'payroll_period_id',
        'date',
        'time_in',
        'time_out',
        'scheduled_minutes',
        'actual_work_minutes',
        'total_break_minutes',
        'net_work_minutes',
        'late_minutes',
        'undertime_minutes',
        'overtime_minutes',
        'day_type',
        'attendance_status',
        'leave_request_id',
        'status',
        'approved_by',
        'approved_at',
        'approval_remarks',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'is_auto_generated',
        'is_manually_adjusted',
        'adjustment_reason',
        'adjusted_by',
        'adjusted_at',
        'is_payroll_processed',
        'payroll_processed_at',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'adjusted_at' => 'datetime',
        'payroll_processed_at' => 'datetime',
        'is_auto_generated' => 'boolean',
        'is_manually_adjusted' => 'boolean',
        'is_payroll_processed' => 'boolean',
    ];

    // Day Types
    const DAY_TYPE_REGULAR = 'regular';
    const DAY_TYPE_REST_DAY = 'rest_day';
    const DAY_TYPE_REGULAR_HOLIDAY = 'regular_holiday';
    const DAY_TYPE_SPECIAL_HOLIDAY = 'special_holiday';
    const DAY_TYPE_DOUBLE_HOLIDAY = 'double_holiday';

    // Attendance Statuses
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';
    const STATUS_HALF_DAY = 'half_day';
    const STATUS_ON_LEAVE = 'on_leave';
    const STATUS_HOLIDAY = 'holiday';
    const STATUS_REST_DAY = 'rest_day';
    const STATUS_INCOMPLETE = 'incomplete';

    // Approval Statuses
    const APPROVAL_DRAFT = 'draft';
    const APPROVAL_PENDING = 'pending';
    const APPROVAL_CORRECTION = 'correction_pending';
    const APPROVAL_APPROVED = 'approved';
    const APPROVAL_REJECTED = 'rejected';
    const APPROVAL_LOCKED = 'locked';

    /**
     * Get the employee
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the source attendance
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the payroll period
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * Get the leave request (if on leave)
     */
    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Alias for approver relationship
     */
    public function approvedByUser(): BelongsTo
    {
        return $this->approver();
    }

    /**
     * Get the rejector
     */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the adjuster
     */
    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    // ==================== STATUS CHECKS ====================

    public function isDraft(): bool
    {
        return $this->status === self::APPROVAL_DRAFT;
    }

    public function isPending(): bool
    {
        return $this->status === self::APPROVAL_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVAL_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::APPROVAL_REJECTED;
    }

    public function isLocked(): bool
    {
        return $this->status === self::APPROVAL_LOCKED;
    }

    public function canBeApproved(): bool
    {
        return in_array($this->status, [self::APPROVAL_DRAFT, self::APPROVAL_PENDING, self::APPROVAL_REJECTED]);
    }

    public function canBeEdited(): bool
    {
        return !in_array($this->status, [self::APPROVAL_LOCKED]);
    }

    // ==================== FORMATTED ATTRIBUTES ====================

    /**
     * Alias for date to support legacy dtr_date property
     */
    public function getDtrDateAttribute()
    {
        return $this->date;
    }

    /**
     * Get work hours as numeric value
     */
    public function getTotalHoursWorkedAttribute(): float
    {
        return $this->net_work_minutes / 60;
    }

    /**
     * Get formatted work hours
     */
    public function getFormattedWorkHoursAttribute(): string
    {
        $hours = floor($this->net_work_minutes / 60);
        $minutes = $this->net_work_minutes % 60;
        return sprintf('%dh %dm', $hours, $minutes);
    }

    /**
     * Get formatted late time
     */
    public function getFormattedLateAttribute(): string
    {
        if ($this->late_minutes <= 0) return '-';
        $hours = floor($this->late_minutes / 60);
        $minutes = $this->late_minutes % 60;
        return $hours > 0 ? sprintf('%dh %dm', $hours, $minutes) : sprintf('%dm', $minutes);
    }

    /**
     * Get formatted overtime
     */
    public function getFormattedOvertimeAttribute(): string
    {
        if ($this->overtime_minutes <= 0) return '-';
        $hours = floor($this->overtime_minutes / 60);
        $minutes = $this->overtime_minutes % 60;
        return sprintf('%dh %dm', $hours, $minutes);
    }

    /**
     * Get formatted undertime
     */
    public function getFormattedUndertimeAttribute(): string
    {
        if ($this->undertime_minutes <= 0) return '-';
        $hours = floor($this->undertime_minutes / 60);
        $minutes = $this->undertime_minutes % 60;
        return sprintf('%dh %dm', $hours, $minutes);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::APPROVAL_DRAFT => 'gray',
            self::APPROVAL_PENDING => 'yellow',
            self::APPROVAL_APPROVED => 'green',
            self::APPROVAL_REJECTED => 'red',
            self::APPROVAL_LOCKED => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get attendance status badge color
     */
    public function getAttendanceStatusColorAttribute(): string
    {
        return match($this->attendance_status) {
            self::STATUS_PRESENT => 'green',
            self::STATUS_ABSENT => 'red',
            self::STATUS_LATE => 'yellow',
            self::STATUS_HALF_DAY => 'orange',
            self::STATUS_ON_LEAVE => 'blue',
            self::STATUS_HOLIDAY => 'purple',
            self::STATUS_REST_DAY => 'gray',
            self::STATUS_INCOMPLETE => 'red',
            default => 'gray',
        };
    }

    /**
     * Get day type label
     */
    public function getDayTypeLabelAttribute(): string
    {
        return match($this->day_type) {
            self::DAY_TYPE_REGULAR => 'Regular',
            self::DAY_TYPE_REST_DAY => 'Rest Day',
            self::DAY_TYPE_REGULAR_HOLIDAY => 'Regular Holiday',
            self::DAY_TYPE_SPECIAL_HOLIDAY => 'Special Holiday',
            self::DAY_TYPE_DOUBLE_HOLIDAY => 'Double Holiday',
            default => 'Unknown',
        };
    }

    // ==================== SCOPES ====================

    public function scopeForPeriod($query, PayrollPeriod $period)
    {
        return $query->where('payroll_period_id', $period->id);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::APPROVAL_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::APPROVAL_DRAFT, self::APPROVAL_PENDING]);
    }

    public function scopeNotProcessed($query)
    {
        return $query->where('is_payroll_processed', false);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // ==================== ACTIONS ====================

    /**
     * Approve this DTR
     */
    public function approve(User $approver, ?string $remarks = null): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        $this->update([
            'status' => self::APPROVAL_APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_remarks' => $remarks,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        return true;
    }

    /**
     * Reject this DTR
     */
    public function reject(User $rejector, string $reason): bool
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->update([
            'status' => self::APPROVAL_REJECTED,
            'rejected_by' => $rejector->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Lock this DTR (after payroll processing)
     */
    public function lock(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        $this->update([
            'status' => self::APPROVAL_LOCKED,
            'is_payroll_processed' => true,
            'payroll_processed_at' => now(),
        ]);

        return true;
    }

    /**
     * Submit for approval
     */
    public function submitForApproval(): bool
    {
        if (!$this->isDraft()) {
            return false;
        }

        $this->update(['status' => self::APPROVAL_PENDING]);
        return true;
    }
}
