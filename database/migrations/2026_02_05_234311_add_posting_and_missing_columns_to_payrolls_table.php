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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->boolean('is_posted')->default(false)->after('status');
            $table->timestamp('posted_at')->nullable()->after('is_posted');
            $table->timestamp('released_at')->nullable()->after('approved_at');
            $table->timestamp('computed_at')->nullable()->after('payroll_period_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['is_posted', 'posted_at', 'released_at', 'computed_at']);
        });
    }
};
