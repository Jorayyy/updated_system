<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Payslip Model
 * 
 * Represents a generated payslip document with PDF storage
 * and delivery tracking capabilities.
 */
class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payroll_id',
        'payroll_period_id',
        'payslip_number',
        'version',
        'file_path',
        'file_name',
        'file_size',
        'file_hash',
        'earnings_snapshot',
        'deductions_snapshot',
        'attendance_snapshot',
        'gross_pay',
        'total_deductions',
        'net_pay',
        'period_start',
        'period_end',
        'pay_date',
        'employee_snapshot',
        'status',
        'error_message',
        'generated_by',
        'generated_at',
        'sent_at',
        'sent_to_email',
        'viewed_at',
        'downloaded_at',
        'download_count',
        'access_token',
        'token_expires_at',
    ];

    protected $casts = [
        'earnings_snapshot' => 'array',
        'deductions_snapshot' => 'array',
        'attendance_snapshot' => 'array',
        'employee_snapshot' => 'array',
        'gross_pay' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'pay_date' => 'date',
        'generated_at' => 'datetime',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'downloaded_at' => 'datetime',
        'token_expires_at' => 'datetime',
    ];

    // Statuses
    const STATUS_GENERATING = 'generating';
    const STATUS_GENERATED = 'generated';
    const STATUS_FAILED = 'failed';
    const STATUS_SENT = 'sent';
    const STATUS_VIEWED = 'viewed';
    const STATUS_DOWNLOADED = 'downloaded';

    /**
     * Get the employee
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payroll
     */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    /**
     * Get the payroll period
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * Get the generator
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // ==================== STATUS CHECKS ====================

    public function isGenerating(): bool
    {
        return $this->status === self::STATUS_GENERATING;
    }

    public function isGenerated(): bool
    {
        return in_array($this->status, [
            self::STATUS_GENERATED,
            self::STATUS_SENT,
            self::STATUS_VIEWED,
            self::STATUS_DOWNLOADED,
        ]);
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isSent(): bool
    {
        return in_array($this->status, [
            self::STATUS_SENT,
            self::STATUS_VIEWED,
            self::STATUS_DOWNLOADED,
        ]);
    }

    public function hasBeenViewed(): bool
    {
        return $this->viewed_at !== null;
    }

    public function hasBeenDownloaded(): bool
    {
        return $this->downloaded_at !== null;
    }

    // ==================== TOKEN MANAGEMENT ====================

    /**
     * Generate a new access token
     */
    public function generateAccessToken(int $expiryHours = 168): string // 7 days default
    {
        $token = Str::random(64);
        
        $this->update([
            'access_token' => $token,
            'token_expires_at' => now()->addHours($expiryHours),
        ]);

        return $token;
    }

    /**
     * Validate access token
     */
    public function validateToken(string $token): bool
    {
        if ($this->access_token !== $token) {
            return false;
        }

        if ($this->token_expires_at && $this->token_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Get secure viewing URL
     */
    public function getSecureUrl(): string
    {
        if (!$this->access_token) {
            $this->generateAccessToken();
        }

        return route('payslips.view', [
            'payslip' => $this->id,
            'token' => $this->access_token,
        ]);
    }

    // ==================== TRACKING ====================

    /**
     * Mark as viewed
     */
    public function markAsViewed(): void
    {
        if (!$this->viewed_at) {
            $this->update([
                'viewed_at' => now(),
                'status' => self::STATUS_VIEWED,
            ]);
        }
    }

    /**
     * Mark as downloaded
     */
    public function markAsDownloaded(): void
    {
        $this->increment('download_count');
        
        if (!$this->downloaded_at) {
            $this->update([
                'downloaded_at' => now(),
                'status' => self::STATUS_DOWNLOADED,
            ]);
        }
    }

    /**
     * Mark as sent
     */
    public function markAsSent(string $email): void
    {
        $this->update([
            'sent_at' => now(),
            'sent_to_email' => $email,
            'status' => self::STATUS_SENT,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    // ==================== FORMATTED ATTRIBUTES ====================

    /**
     * Get period label
     */
    public function getPeriodLabelAttribute(): string
    {
        return $this->period_start->format('M d') . ' - ' . $this->period_end->format('M d, Y');
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) return '-';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_GENERATING => 'yellow',
            self::STATUS_GENERATED => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_SENT => 'blue',
            self::STATUS_VIEWED => 'indigo',
            self::STATUS_DOWNLOADED => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_GENERATING => 'Generating',
            self::STATUS_GENERATED => 'Ready',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_SENT => 'Sent',
            self::STATUS_VIEWED => 'Viewed',
            self::STATUS_DOWNLOADED => 'Downloaded',
            default => 'Unknown',
        };
    }

    // ==================== SCOPES ====================

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPeriod($query, $periodId)
    {
        return $query->where('payroll_period_id', $periodId);
    }

    public function scopeGenerated($query)
    {
        return $query->whereIn('status', [
            self::STATUS_GENERATED,
            self::STATUS_SENT,
            self::STATUS_VIEWED,
            self::STATUS_DOWNLOADED,
        ]);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    // ==================== PAYSLIP NUMBER GENERATION ====================

    /**
     * Generate unique payslip number
     */
    public static function generatePayslipNumber(PayrollPeriod $period): string
    {
        $year = $period->end_date->format('Y');
        $month = $period->end_date->format('m');
        
        $lastPayslip = static::where('payslip_number', 'like', "PS-{$year}-{$month}-%")
            ->orderBy('payslip_number', 'desc')
            ->first();

        if ($lastPayslip) {
            $lastNumber = (int) Str::afterLast($lastPayslip->payslip_number, '-');
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('PS-%s-%s-%04d', $year, $month, $newNumber);
    }

    // ==================== SNAPSHOT HELPERS ====================

    /**
     * Get earning from snapshot
     */
    public function getEarning(string $key, $default = 0)
    {
        return $this->earnings_snapshot[$key] ?? $default;
    }

    /**
     * Get deduction from snapshot
     */
    public function getDeduction(string $key, $default = 0)
    {
        return $this->deductions_snapshot[$key] ?? $default;
    }

    /**
     * Get attendance data from snapshot
     */
    public function getAttendanceData(string $key, $default = 0)
    {
        return $this->attendance_snapshot[$key] ?? $default;
    }

    /**
     * Get employee data from snapshot
     */
    public function getEmployeeData(string $key, $default = null)
    {
        return $this->employee_snapshot[$key] ?? $default;
    }
}
