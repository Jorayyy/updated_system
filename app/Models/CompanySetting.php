<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        try {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return match($setting->type) {
                'integer' => (int) $setting->value,
                'decimal' => (float) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        } catch (\Illuminate\Database\QueryException $e) {
            // Table might not exist yet during initial setup/migration
            return $default;
        }
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, $value, string $type = 'string', string $group = 'general'): void
    {
        $storeValue = match($type) {
            'json' => json_encode($value),
            'boolean' => $value ? 'true' : 'false',
            default => (string) $value,
        };

        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storeValue,
                'type' => $type,
                'group' => $group,
            ]
        );
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        return self::where('group', $group)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => self::getValue($setting->key)];
            })
            ->toArray();
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAllSettings(): array
    {
        return self::all()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => self::getValue($setting->key)];
            })
            ->toArray();
    }
}
