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
            if (!Schema::hasColumn('users', 'perfect_attendance_bonus')) {
                $table->decimal('perfect_attendance_bonus', 10, 2)->default(0)->after('communication_allowance');
            }
            if (!Schema::hasColumn('users', 'site_incentive')) {
                $table->decimal('site_incentive', 10, 2)->default(0)->after('perfect_attendance_bonus');
            }
            if (!Schema::hasColumn('users', 'other_allowance')) {
                $table->decimal('other_allowance', 10, 2)->default(0)->after('site_incentive');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['perfect_attendance_bonus', 'site_incentive', 'other_allowance']);
        });
    }
};
