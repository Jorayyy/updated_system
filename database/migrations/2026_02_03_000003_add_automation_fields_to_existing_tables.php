<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Automation Fields to Existing Tables
 * 
 * Purpose: Enhance existing tables to support event-driven automation
 * 
 * Design Decisions:
 * 1. Add status fields for workflow tracking
 * 2. Add processing timestamps for audit trail
 * 3. Add flags to prevent duplicate processing
 */
return new class extends Migration
{
    public function up(): void
    {
        // Enhance Attendance table
        Schema::table('attendances', function (Blueprint $table) {
            // DTR Generation Tracking
            if (!Schema::hasColumn('attendances', 'dtr_generated')) {
                $table->boolean('dtr_generated')->default(false)->after('remarks');
            }
            if (!Schema::hasColumn('attendances', 'dtr_generated_at')) {
                $table->datetime('dtr_generated_at')->nullable()->after('dtr_generated');
            }
            
            // Late Tracking (computed)
            if (!Schema::hasColumn('attendances', 'late_minutes')) {
                $table->integer('late_minutes')->default(0)->after('undertime_minutes');
            }
            
            // Grace Period Applied
            if (!Schema::hasColumn('attendances', 'grace_period_applied')) {
                $table->boolean('grace_period_applied')->default(false)->after('late_minutes');
            }
            
            // Source tracking for manual vs automated
            if (!Schema::hasColumn('attendances', 'source')) {
                $table->enum('source', ['biometric', 'web', 'mobile', 'manual', 'import'])->default('web')->after('grace_period_applied');
            }
        });

        // Enhance Payroll table
        Schema::table('payrolls', function (Blueprint $table) {
            // Approval workflow
            if (!Schema::hasColumn('payrolls', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('payrolls', 'approved_at')) {
                $table->datetime('approved_at')->nullable()->after('approved_by');
            }
            
            // Payslip generation tracking
            if (!Schema::hasColumn('payrolls', 'payslip_generated')) {
                $table->boolean('payslip_generated')->default(false)->after('approved_at');
            }
            if (!Schema::hasColumn('payrolls', 'payslip_generated_at')) {
                $table->datetime('payslip_generated_at')->nullable()->after('payslip_generated');
            }
            
            // Processing lock to prevent concurrent modifications
            if (!Schema::hasColumn('payrolls', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('payslip_generated_at');
            }
            if (!Schema::hasColumn('payrolls', 'locked_at')) {
                $table->datetime('locked_at')->nullable()->after('is_locked');
            }
            if (!Schema::hasColumn('payrolls', 'locked_by')) {
                $table->foreignId('locked_by')->nullable()->after('locked_at')->constrained('users')->onDelete('set null');
            }
        });

        // Enhance Payroll Periods table
        Schema::table('payroll_periods', function (Blueprint $table) {
            // DTR generation tracking
            if (!Schema::hasColumn('payroll_periods', 'dtr_generated')) {
                $table->boolean('dtr_generated')->default(false)->after('processed_at');
            }
            if (!Schema::hasColumn('payroll_periods', 'dtr_generated_at')) {
                $table->datetime('dtr_generated_at')->nullable()->after('dtr_generated');
            }
            
            // Approval counts for quick reference
            if (!Schema::hasColumn('payroll_periods', 'total_employees')) {
                $table->integer('total_employees')->default(0)->after('dtr_generated_at');
            }
            if (!Schema::hasColumn('payroll_periods', 'approved_dtr_count')) {
                $table->integer('approved_dtr_count')->default(0)->after('total_employees');
            }
            if (!Schema::hasColumn('payroll_periods', 'pending_dtr_count')) {
                $table->integer('pending_dtr_count')->default(0)->after('approved_dtr_count');
            }
            
            // Payroll computation tracking
            if (!Schema::hasColumn('payroll_periods', 'payroll_computed')) {
                $table->boolean('payroll_computed')->default(false)->after('pending_dtr_count');
            }
            if (!Schema::hasColumn('payroll_periods', 'payroll_computed_at')) {
                $table->datetime('payroll_computed_at')->nullable()->after('payroll_computed');
            }
            
            // Payslip generation tracking
            if (!Schema::hasColumn('payroll_periods', 'payslips_generated')) {
                $table->boolean('payslips_generated')->default(false)->after('payroll_computed_at');
            }
            if (!Schema::hasColumn('payroll_periods', 'payslips_generated_at')) {
                $table->datetime('payslips_generated_at')->nullable()->after('payslips_generated');
            }
        });

        // Enhance Leave Requests table
        Schema::table('leave_requests', function (Blueprint $table) {
            // Attendance marking tracking
            if (!Schema::hasColumn('leave_requests', 'attendance_marked')) {
                $table->boolean('attendance_marked')->default(false)->after('rejection_reason');
            }
            if (!Schema::hasColumn('leave_requests', 'attendance_marked_at')) {
                $table->datetime('attendance_marked_at')->nullable()->after('attendance_marked');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $columns = ['dtr_generated', 'dtr_generated_at', 'late_minutes', 'grace_period_applied', 'source'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $columns = ['approved_by', 'approved_at', 'payslip_generated', 'payslip_generated_at', 'is_locked', 'locked_at', 'locked_by'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('payrolls', $column)) {
                    if (in_array($column, ['approved_by', 'locked_by'])) {
                        $table->dropForeign([$column]);
                    }
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('payroll_periods', function (Blueprint $table) {
            $columns = ['dtr_generated', 'dtr_generated_at', 'total_employees', 'approved_dtr_count', 'pending_dtr_count', 'payroll_computed', 'payroll_computed_at', 'payslips_generated', 'payslips_generated_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('payroll_periods', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $columns = ['attendance_marked', 'attendance_marked_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('leave_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
