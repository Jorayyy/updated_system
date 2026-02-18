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
        Schema::table('attendances', function (Blueprint $table) {
            $table->integer('night_diff_minutes')->default(0)->after('overtime_minutes');
        });

        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->integer('night_diff_minutes')->default(0)->after('overtime_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('night_diff_minutes');
        });

        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->dropColumn('night_diff_minutes');
        });
    }
};
