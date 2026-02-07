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
        Schema::table('daily_time_records', function (Blueprint $table) {
            // Correction Request Fields
            $table->boolean('correction_requested')->default(false)->after('rejection_reason');
            $table->json('correction_data')->nullable()->after('correction_requested');
            $table->text('correction_reason')->nullable()->after('correction_data');
            $table->datetime('correction_requested_at')->nullable()->after('correction_reason');
            
            // Correction Approval tracking
            $table->foreignId('correction_approved_by')->nullable()->after('correction_requested_at')->constrained('users')->onDelete('set null');
            $table->datetime('correction_approved_at')->nullable()->after('correction_approved_by');
            
            // Correction Rejection tracking
            $table->foreignId('correction_rejected_by')->nullable()->after('correction_approved_at')->constrained('users')->onDelete('set null');
            $table->datetime('correction_rejected_at')->nullable()->after('correction_rejected_by');
            $table->text('correction_rejection_reason')->nullable()->after('correction_rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->dropForeign(['correction_approved_by']);
            $table->dropForeign(['correction_rejected_by']);
            $table->dropColumn([
                'correction_requested',
                'correction_data',
                'correction_reason',
                'correction_requested_at',
                'correction_approved_by',
                'correction_approved_at',
                'correction_rejected_by',
                'correction_rejected_at',
                'correction_rejection_reason',
            ]);
        });
    }
};
