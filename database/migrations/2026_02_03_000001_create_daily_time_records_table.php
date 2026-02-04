<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daily Time Records (DTR) Table
 * 
 * Purpose: Aggregated daily attendance data for payroll processing
 * 
 * Design Decisions:
 * 1. DTR is separate from Attendance to allow approval workflow
 * 2. DTR aggregates attendance + breaks + leave into payroll-ready format
 * 3. Approval status gates payroll computation (only approved DTRs trigger payroll)
 * 4. Stores computed values to avoid recalculation and ensure audit trail
 * 5. Links to source attendance for traceability
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_time_records', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('payroll_period_id')->nullable()->constrained()->onDelete('set null');
            
            // Date Reference
            $table->date('date');
            
            // Time Entries (copied from attendance for immutability)
            $table->datetime('time_in')->nullable();
            $table->datetime('time_out')->nullable();
            
            // Computed Time Values (in minutes for precision)
            $table->integer('scheduled_minutes')->default(480); // 8 hours default
            $table->integer('actual_work_minutes')->default(0);
            $table->integer('total_break_minutes')->default(0);
            $table->integer('net_work_minutes')->default(0); // actual - breaks
            $table->integer('late_minutes')->default(0);
            $table->integer('undertime_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            
            // Day Classification
            $table->enum('day_type', [
                'regular',           // Normal work day
                'rest_day',          // Scheduled rest day
                'regular_holiday',   // Regular holiday (PH)
                'special_holiday',   // Special non-working holiday (PH)
                'double_holiday',    // When regular + special falls same day
            ])->default('regular');
            
            // Attendance Status
            $table->enum('attendance_status', [
                'present',           // Normal attendance
                'absent',            // No attendance, no leave
                'late',              // Arrived after grace period
                'half_day',          // Only morning or afternoon
                'on_leave',          // Approved leave
                'holiday',           // Non-working holiday
                'rest_day',          // Scheduled rest day
                'incomplete',        // Has time_in but no time_out
            ])->default('present');
            
            // Leave Reference (if on leave)
            $table->foreignId('leave_request_id')->nullable()->constrained()->onDelete('set null');
            
            // Approval Workflow
            $table->enum('status', [
                'draft',              // Auto-generated, awaiting review
                'pending',            // Submitted for approval
                'correction_pending', // Requesting correction
                'approved',           // Approved, ready for payroll
                'rejected',           // Rejected, needs correction
                'locked',             // Payroll processed, cannot change
            ])->default('draft');
            
            // Approval Tracking
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('approved_at')->nullable();
            $table->text('approval_remarks')->nullable();
            
            // Rejection Tracking
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Automation Tracking
            $table->boolean('is_auto_generated')->default(true);
            $table->boolean('is_manually_adjusted')->default(false);
            $table->text('adjustment_reason')->nullable();
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('adjusted_at')->nullable();
            
            // Payroll Processing Flag
            $table->boolean('is_payroll_processed')->default(false);
            $table->datetime('payroll_processed_at')->nullable();
            
            // General
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Indexes for Performance
            $table->unique(['user_id', 'date']); // One DTR per employee per day
            $table->index(['date', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['payroll_period_id', 'status']);
            $table->index('attendance_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_time_records');
    }
};
