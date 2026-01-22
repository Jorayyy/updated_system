<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

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
        'allowances',
        'gross_pay',
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
        'withholding_tax',
        'late_deductions',
        'undertime_deductions',
        'absent_deductions',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'status',
        'remarks',
    ];

    protected $casts = [
        'basic_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'holiday_pay' => 'decimal:2',
        'allowances' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'sss_contribution' => 'decimal:2',
        'philhealth_contribution' => 'decimal:2',
        'pagibig_contribution' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'late_deductions' => 'decimal:2',
        'undertime_deductions' => 'decimal:2',
        'absent_deductions' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    /**
     * Get the employee
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payroll period
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
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
