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
        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('department');
            $table->index('is_active');
            $table->index(['role', 'is_active']);
        });

        // Add indexes to attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('date');
            $table->index('status');
            $table->index(['user_id', 'date']);
            $table->index(['date', 'status']);
        });

        // Add indexes to leave_requests table
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // Add indexes to payrolls table
        Schema::table('payrolls', function (Blueprint $table) {
            $table->index('status');
            $table->index(['user_id', 'payroll_period_id']);
        });

        // Add indexes to payroll_periods table
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });

        // Add indexes to audit_logs table
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('action');
            $table->index('model_type');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });

        // Add indexes to company_settings table
        Schema::table('company_settings', function (Blueprint $table) {
            $table->index('group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['department']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['role', 'is_active']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'date']);
            $table->dropIndex(['date', 'status']);
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['start_date', 'end_date']);
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'payroll_period_id']);
        });

        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['start_date', 'end_date']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['action']);
            $table->dropIndex(['model_type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropIndex(['group']);
        });
    }
};
