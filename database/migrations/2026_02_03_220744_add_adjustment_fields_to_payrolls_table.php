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
            $table->boolean('is_manually_adjusted')->default(false)->after('net_pay');
            $table->text('adjustment_reason')->nullable()->after('is_manually_adjusted');
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->onDelete('set null')->after('adjustment_reason');
            $table->timestamp('adjusted_at')->nullable()->after('adjusted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['adjusted_by']);
            $table->dropColumn(['is_manually_adjusted', 'adjustment_reason', 'adjusted_by', 'adjusted_at']);
        });
    }
};
