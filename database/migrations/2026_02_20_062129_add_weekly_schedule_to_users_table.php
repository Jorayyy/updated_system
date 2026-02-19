<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('monday_schedule')->nullable()->after('other_info');
            $table->string('tuesday_schedule')->nullable()->after('monday_schedule');
            $table->string('wednesday_schedule')->nullable()->after('tuesday_schedule');
            $table->string('thursday_schedule')->nullable()->after('wednesday_schedule');
            $table->string('friday_schedule')->nullable()->after('thursday_schedule');
            $table->string('saturday_schedule')->nullable()->after('friday_schedule');
            $table->string('sunday_schedule')->nullable()->after('saturday_schedule');
            $table->boolean('special_1_hour_only')->default(0)->after('sunday_schedule');
            $table->boolean('special_case_policy')->default(0)->after('special_1_hour_only');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'monday_schedule',
                'tuesday_schedule',
                'wednesday_schedule',
                'thursday_schedule',
                'friday_schedule',
                'saturday_schedule',
                'sunday_schedule',
                'special_1_hour_only',
                'special_case_policy',
            ]);
        });
    }
};
