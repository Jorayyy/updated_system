<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'type',
        'is_recurring',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Holiday types
     */
    const TYPE_REGULAR = 'regular';
    const TYPE_SPECIAL = 'special';
    const TYPE_SPECIAL_WORKING = 'special_working';

    /**
     * Get all holidays for a given date range
     */
    public static function forDateRange($startDate, $endDate)
    {
        return self::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }

    /**
     * Check if a specific date is a holiday
     */
    public static function isHoliday($date): bool
    {
        return self::whereDate('date', $date)->exists();
    }

    /**
     * Get holiday info for a specific date
     */
    public static function getHoliday($date)
    {
        return self::whereDate('date', $date)->first();
    }

    /**
     * Get holidays for current year
     */
    public static function forYear($year = null)
    {
        $year = $year ?? date('Y');
        return self::whereYear('date', $year)
            ->orderBy('date')
            ->get();
    }

    /**
     * Get holiday multiplier for payroll calculation
     */
    public function getPayMultiplier(): float
    {
        return match($this->type) {
            self::TYPE_REGULAR => 2.0,        // 200% for regular holiday
            self::TYPE_SPECIAL => 1.3,        // 130% for special holiday
            self::TYPE_SPECIAL_WORKING => 1.3,
            default => 1.0,
        };
    }

    /**
     * Scope for active/upcoming holidays
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', today())->orderBy('date');
    }

    /**
     * Get formatted type name
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            self::TYPE_REGULAR => 'Regular Holiday',
            self::TYPE_SPECIAL => 'Special Non-Working Holiday',
            self::TYPE_SPECIAL_WORKING => 'Special Working Holiday',
            default => 'Unknown',
        };
    }
}
