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
        Schema::table('leave_requests', function (Blueprint $table) {
            // HR approval
            $table->foreignId('hr_approved_by')->nullable()->after('approved_by')->constrained('users')->onDelete('set null');
            $table->timestamp('hr_approved_at')->nullable()->after('hr_approved_by');
            $table->enum('hr_status', ['pending', 'approved', 'rejected'])->default('pending')->after('hr_approved_at');
            
            // Admin approval
            $table->foreignId('admin_approved_by')->nullable()->after('hr_status')->constrained('users')->onDelete('set null');
            $table->timestamp('admin_approved_at')->nullable()->after('admin_approved_by');
            $table->enum('admin_status', ['pending', 'approved', 'rejected'])->default('pending')->after('admin_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['hr_approved_by']);
            $table->dropForeign(['admin_approved_by']);
            $table->dropColumn([
                'hr_approved_by',
                'hr_approved_at',
                'hr_status',
                'admin_approved_by',
                'admin_approved_at',
                'admin_status',
            ]);
        });
    }
};
