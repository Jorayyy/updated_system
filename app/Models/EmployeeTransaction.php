<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'user_id',
        'transaction_type',
        'status',
        'effective_date',
        'effective_date_end',
        'time_from',
        'time_to',
        'reason',
        'details',
        'attachment',
        'leave_type_id',
        'days_count',
        'hr_approved_by',
        'hr_approved_at',
        'admin_approved_by',
        'admin_approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'effective_date_end' => 'date',
        'hr_approved_at' => 'datetime',
        'admin_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'details' => 'array',
        'days_count' => 'decimal:2',
    ];

    // Transaction Types
    const TYPES = [
        'leave' => [
            'name' => 'Apply Leave',
            'icon' => 'calendar',
            'color' => 'blue',
            'requires_dates' => true,
            'requires_leave_type' => true,
        ],
        'schedule_change' => [
            'name' => 'Request of Change of Schedule',
            'icon' => 'clock',
            'color' => 'purple',
            'requires_dates' => true,
            'requires_time' => true,
        ],
        'overtime_auth' => [
            'name' => 'Authorization to Render Overtime',
            'icon' => 'plus-circle',
            'color' => 'orange',
            'requires_dates' => true,
            'requires_time' => true,
        ],
        'payroll_complaint' => [
            'name' => 'Payroll Complaints Form',
            'icon' => 'currency-dollar',
            'color' => 'green',
            'requires_dates' => false,
        ],
        'official_business' => [
            'name' => 'Official Business Form',
            'icon' => 'briefcase',
            'color' => 'indigo',
            'requires_dates' => true,
            'requires_time' => true,
        ],
        'undertime' => [
            'name' => 'Under Time Form',
            'icon' => 'arrow-down',
            'color' => 'yellow',
            'requires_dates' => true,
            'requires_time' => true,
        ],
        'leave_cancellation' => [
            'name' => 'Cancellation of Leave',
            'icon' => 'x-circle',
            'color' => 'red',
            'requires_dates' => true,
        ],
        'timekeeping_complaint' => [
            'name' => 'Timekeeping (TK) Complaint',
            'icon' => 'exclamation',
            'color' => 'rose',
            'requires_dates' => true,
        ],
        'restday_change' => [
            'name' => 'Request of Change of Rest Day',
            'icon' => 'refresh',
            'color' => 'teal',
            'requires_dates' => true,
        ],
    ];

    // Statuses
    const STATUSES = [
        'pending' => 'Pending',
        'hr_approved' => 'HR Approved',
        'admin_approved' => 'Admin Approved',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Generate transaction number
     */
    public static function generateTransactionNumber(string $type): string
    {
        $prefix = match($type) {
            'leave' => 'LV',
            'schedule_change' => 'SC',
            'overtime_auth' => 'OT',
            'payroll_complaint' => 'PC',
            'official_business' => 'OB',
            'undertime' => 'UT',
            'leave_cancellation' => 'LC',
            'timekeeping_complaint' => 'TC',
            'restday_change' => 'RC',
            default => 'TR',
        };
        
        $year = date('Y');
        $month = date('m');
        
        $lastTransaction = self::where('transaction_number', 'like', "{$prefix}-{$year}{$month}%")
            ->orderBy('transaction_number', 'desc')
            ->first();
        
        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "{$prefix}-{$year}{$month}-{$newNumber}";
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function hrApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_approved_by');
    }

    public function adminApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Accessors
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->transaction_type]['name'] ?? $this->transaction_type;
    }

    public function getTypeColorAttribute(): string
    {
        return self::TYPES[$this->transaction_type]['color'] ?? 'gray';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'hr_approved' => 'blue',
            'admin_approved' => 'indigo',
            'approved' => 'green',
            'rejected' => 'red',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    // Status checks
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'hr_approved']);
    }

    public function needsHrApproval(): bool
    {
        return $this->status === 'pending';
    }

    public function needsAdminApproval(): bool
    {
        return $this->status === 'hr_approved';
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNeedsApproval($query)
    {
        return $query->whereIn('status', ['pending', 'hr_approved']);
    }
}
