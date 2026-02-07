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
        'site_id',
        'account_id',
        'campaign_id',
        'start_date',
        'end_date',
        'pay_date',
        'status',
        'period_type',
        'remarks',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pay_date' => 'date',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the site associated with the period
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the account (Role) associated with the period
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the designation associated with the period
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Get the campaign (Work Group) associated with the period
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
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
