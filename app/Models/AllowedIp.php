<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllowedIp extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'label',
        'location',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this IP entry
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only active IPs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if an IP address is allowed
     */
    public static function isAllowed(string $ipAddress): bool
    {
        // Check if IP restriction is enabled in settings
        $ipRestrictionEnabled = CompanySetting::getValue('attendance_ip_restriction', false);
        
        if (!$ipRestrictionEnabled) {
            return true; // IP restriction is disabled, allow all
        }

        // Check if there are any allowed IPs configured
        // If restriction is enabled but no IPs are registered, we block everything for security
        if (!self::active()->exists()) {
            return false;
        }
        
        return self::active()->where('ip_address', $ipAddress)->exists();
    }

    /**
     * Get all active IP addresses as array
     */
    public static function getAllowedIps(): array
    {
        return self::active()->pluck('ip_address')->toArray();
    }
}
