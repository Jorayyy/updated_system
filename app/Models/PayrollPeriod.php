<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'payroll_group_id',
        'start_date',
        'end_date',
        'pay_date',
        'status',
        'period_type',
        'cover_month',
        'cover_year',
        'cut_off_label',
        'type', // covering bases if duplicate column exists
        'description',
        'remarks',
        'processed_by',
        'processed_at',
        'dtr_generated',
        'dtr_generated_at',
        'total_employees',
        'approved_dtr_count',
        'pending_dtr_count',
        'payroll_computed',
        'payroll_computed_at',
        'payslips_generated',
        'payslips_generated_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pay_date' => 'date',
        'processed_at' => 'datetime',
    ];

    public function payrollGroup(): BelongsTo
    {
        return $this->belongsTo(PayrollGroup::class);
    }

    /**
     * Get payrolls for this period
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get daily time records for this period
     */
    public function dailyTimeRecords(): HasMany
    {
        return $this->hasMany(DailyTimeRecord::class);
    }

    /**
     * Get the processor
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get period label
     */
    public function getPeriodLabelAttribute(): string
    {
        return $this->start_date->format('M d') . ' - ' . $this->end_date->format('M d, Y');
    }

    /**
     * Get name attribute (alias for period label)
     */
    public function getNameAttribute(): string
    {
        return 'Payroll Period: ' . $this->start_date->format('M d') . ' - ' . $this->end_date->format('M d, Y');
    }

    /**
     * Check if period is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if period is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Scope for draft periods
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for completed periods
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
