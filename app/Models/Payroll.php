<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    /**
     * Get all adjustment codes from database
     */
    public static function getAdjustmentCodes(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('payroll_adjustment_codes', 3600, function() {
            return \App\Models\PayrollAdjustmentType::all()->pluck('name', 'code')->toArray();
        });
    }

    protected $fillable = [
        'user_id',
        'payroll_period_id',
        'total_work_days',
        'total_work_minutes',
        'total_overtime_minutes',
        'total_undertime_minutes',
        'total_late_minutes',
        'total_absent_days',
        'basic_pay',
        'overtime_pay',
        'holiday_pay',
        'night_diff_pay',
        'rest_day_pay',
        'bonus',
        'allowances',
        'gross_pay',
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
        'withholding_tax',
        'late_deductions',
        'undertime_deductions',
        'absent_deductions',
        'loan_deductions',
        'other_deductions',
        'leave_without_pay_deductions',
        'total_deductions',
        'net_pay',
        'status',
        'is_posted',
        'posted_at',
        'computed_at',
        'approved_at',
        'released_at',
        'remarks',
        'is_manually_adjusted',
        'adjustment_reason',
        'adjusted_by',
        'adjusted_at',
    ];

    protected $casts = [
        'basic_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'holiday_pay' => 'decimal:2',
        'night_diff_pay' => 'decimal:2',
        'rest_day_pay' => 'decimal:2',
        'bonus' => 'decimal:2',
        'allowances' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'sss_contribution' => 'decimal:2',
        'philhealth_contribution' => 'decimal:2',
        'pagibig_contribution' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'late_deductions' => 'decimal:2',
        'undertime_deductions' => 'decimal:2',
        'absent_deductions' => 'decimal:2',
        'loan_deductions' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'leave_without_pay_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
        'computed_at' => 'datetime',
        'approved_at' => 'datetime',
        'released_at' => 'datetime',
        'is_manually_adjusted' => 'boolean',
        'adjusted_at' => 'datetime',
    ];

    /**
     * Get the employee
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the adjuster
     */
    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    /**
     * Get the payroll period
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * Get the daily time records for this payroll
     */
    public function dailyTimeRecords(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            DailyTimeRecord::class,
            PayrollPeriod::class,
            'id', // Foreign key on PayrollPeriod table
            'payroll_period_id', // Foreign key on DailyTimeRecord table
            'payroll_period_id', // Local key on Payroll table
            'id' // Local key on PayrollPeriod table
        )->where('daily_time_records.user_id', $this->user_id ?? 0);
    }

    /**
     * Get formatted total work hours
     */
    public function getFormattedWorkHoursAttribute(): string
    {
        $hours = floor($this->total_work_minutes / 60);
        $minutes = $this->total_work_minutes % 60;
        return sprintf('%d hrs %d mins', $hours, $minutes);
    }

    /**
     * Get formatted overtime hours
     */
    public function getFormattedOvertimeHoursAttribute(): string
    {
        $hours = floor($this->total_overtime_minutes / 60);
        $minutes = $this->total_overtime_minutes % 60;
        return sprintf('%d hrs %d mins', $hours, $minutes);
    }

    /**
     * Get days worked (alias for total_work_days)
     */
    public function getDaysWorkedAttribute(): int
    {
        return $this->total_work_days ?? 0;
    }

    /**
     * Get total work hours as numeric value
     */
    public function getHoursWorkedAttribute(): float
    {
        return round($this->total_work_minutes / 60, 2);
    }

    /**
     * Get overtime hours as numeric value
     */
    public function getOvertimeHoursAttribute(): float
    {
        return round($this->total_overtime_minutes / 60, 2);
    }

    /**
     * Alias for total_late_minutes
     */
    public function getLateMinutesAttribute(): int
    {
        return $this->total_late_minutes ?? 0;
    }

    /**
     * Alias for total_undertime_minutes
     */
    public function getUndertimeMinutesAttribute(): int
    {
        return $this->total_undertime_minutes ?? 0;
    }

    /**
     * Alias for total_absent_days
     */
    public function getAbsencesAttribute(): int
    {
        return $this->total_absent_days ?? 0;
    }

    /**
     * Alias for late_deductions (for view compatibility)
     */
    public function getLateDeductionAttribute(): float
    {
        return (float) ($this->late_deductions ?? 0);
    }

    /**
     * Alias for undertime_deductions (for view compatibility)
     */
    public function getUndertimeDeductionAttribute(): float
    {
        return (float) ($this->undertime_deductions ?? 0);
    }

    /**
     * Alias for absent_deductions (for view compatibility)
     */
    public function getAbsentDeductionAttribute(): float
    {
        return (float) ($this->absent_deductions ?? 0);
    }

    /**
     * Alias for loan_deductions (for view compatibility)
     */
    public function getLoanDeductionAttribute(): float
    {
        return (float) ($this->loan_deductions ?? 0);
    }

    /**
     * Alias for leave_without_pay_deductions (for view compatibility)
     */
    public function getLeaveWithoutPayDeductionAttribute(): float
    {
        return (float) ($this->leave_without_pay_deductions ?? 0);
    }

    /**
     * Alias for other_deductions (for view compatibility)
     */
    public function getOtherDeductionAttribute(): float
    {
        return (float) ($this->other_deductions ?? 0);
    }

    /**
     * Check if payroll is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if payroll is released
     */
    public function isReleased(): bool
    {
        return $this->status === 'released';
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for a specific period
     */
    public function scopeForPeriod($query, $periodId)
    {
        return $query->where('payroll_period_id', $periodId);
    }
}
