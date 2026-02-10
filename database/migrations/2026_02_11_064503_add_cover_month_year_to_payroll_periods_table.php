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
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->string('cover_month')->nullable()->after('end_date');
            $table->year('cover_year')->nullable()->after('cover_month');
            $table->string('cut_off_label')->nullable()->after('cover_year'); // e.g. "1st cut off"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropColumn(['cover_month', 'cover_year', 'cut_off_label']);
        });
    }
};
