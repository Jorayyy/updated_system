<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

/**
 * Attendance Settings Seeder
 * 
 * Seeds default attendance-related settings required for the automation system.
 * These settings control:
 * - Work schedule (time in, time out)
 * - Grace period for lateness
 * - Break durations
 * - Overtime calculation rules
 */
class AttendanceSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Work Schedule
            [
                'key' => 'standard_time_in',
                'value' => '08:00',
                'type' => 'string',
                'group' => 'attendance',
                'description' => 'Standard time-in (24-hour format)',
            ],
            [
                'key' => 'standard_time_out',
                'value' => '17:00',
                'type' => 'string',
                'group' => 'attendance',
                'description' => 'Standard time-out (24-hour format)',
            ],
            [
                'key' => 'standard_work_minutes',
                'value' => '480',
                'type' => 'integer',
                'group' => 'attendance',
                'description' => 'Required work minutes per day (480 = 8 hours)',
            ],

            // Grace Period
            [
                'key' => 'grace_period_minutes',
                'value' => '15',
                'type' => 'integer',
                'group' => 'attendance',
                'description' => 'Grace period in minutes before marked as late',
            ],
            [
                'key' => 'grace_period_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'attendance',
                'description' => 'Enable/disable grace period for lateness',
            ],

            // Break Settings
            [
                'key' => 'lunch_break_minutes',
                'value' => '60',
                'type' => 'integer',
                'group' => 'attendance',
                'description' => 'Lunch break duration in minutes',
            ],
            [
                'key' => 'break_minutes',
                'value' => '30',
                'type' => 'integer',
                'group' => 'attendance',
                'description' => 'Total short break duration in minutes (combined)',
            ],
            [
                'key' => 'auto_deduct_breaks',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'attendance',
                'description' => 'Automatically deduct breaks from work hours',
            ],

            // Overtime Settings
            [
                'key' => 'overtime_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'attendance',
                'description' => 'Enable overtime calculation',
            ],
            [
                'key' => 'overtime_threshold_minutes',
                'value' => '30',
                'type' => 'integer',
                'group' => 'attendance',
                'description' => 'Minimum overtime minutes to be counted',
            ],
            [
                'key' => 'overtime_multiplier',
                'value' => '1.25',
                'type' => 'decimal',
                'group' => 'attendance',
                'description' => 'Regular overtime rate multiplier (1.25 = 125%)',
            ],
            [
                'key' => 'overtime_requires_approval',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'attendance',
                'description' => 'Require overtime pre-approval',
            ],

            // Undertime Settings
            [
                'key' => 'undertime_threshold_minutes',
                'value' => '15',
                'type' => 'integer',
                'group' => 'attendance',
                'description' => 'Minimum undertime minutes before deduction',
            ],
            [
                'key' => 'undertime_deduction_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'attendance',
                'description' => 'Enable undertime deduction from salary',
            ],

            // Late Settings
            [
                'key' => 'late_deduction_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'attendance',
                'description' => 'Enable late deduction from salary',
            ],
            [
                'key' => 'late_deduction_per_minute',
                'value' => '0',
                'type' => 'decimal',
                'group' => 'attendance',
                'description' => 'Deduction per minute late (0 = use hourly rate)',
            ],

            // Auto-processing Settings
            [
                'key' => 'auto_timeout_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'attendance',
                'description' => 'Enable automatic time-out for forgotten clock-outs',
            ],
            [
                'key' => 'auto_timeout_time',
                'value' => '23:59',
                'type' => 'string',
                'group' => 'attendance',
                'description' => 'Time to auto-timeout (24-hour format)',
            ],
            [
                'key' => 'auto_generate_dtr',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'attendance',
                'description' => 'Automatically generate DTR at end of day',
            ],

            // IP Restriction (already exists, ensuring consistency)
            [
                'key' => 'attendance_ip_restriction',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'attendance',
                'description' => 'Restrict attendance recording to allowed IPs',
            ],

            // Work Week Settings
            [
                'key' => 'work_days',
                'value' => '["monday","tuesday","wednesday","thursday","friday"]',
                'type' => 'json',
                'group' => 'attendance',
                'description' => 'Regular work days (JSON array)',
            ],
            [
                'key' => 'half_day_minutes',
                'value' => '240',
                'type' => 'integer',
                'group' => 'attendance',
                'description' => 'Minutes threshold for half-day (240 = 4 hours)',
            ],
        ];

        foreach ($settings as $setting) {
            CompanySetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Attendance settings seeded successfully!');
    }
}
