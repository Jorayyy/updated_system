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
        Schema::create('employee_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('transaction_type'); // leave, schedule_change, overtime_auth, payroll_complaint, official_business, undertime, leave_cancellation, timekeeping_complaint, restday_change
            $table->string('status')->default('pending'); // pending, hr_approved, admin_approved, approved, rejected, cancelled
            $table->date('effective_date')->nullable();
            $table->date('effective_date_end')->nullable();
            $table->time('time_from')->nullable();
            $table->time('time_to')->nullable();
            $table->text('reason')->nullable();
            $table->text('details')->nullable(); // JSON for type-specific data
            $table->string('attachment')->nullable();
            
            // For leave-specific
            $table->foreignId('leave_type_id')->nullable()->constrained('leave_types')->nullOnDelete();
            $table->decimal('days_count', 5, 2)->nullable();
            
            // Approval workflow
            $table->foreignId('hr_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hr_approved_at')->nullable();
            $table->foreignId('admin_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('admin_approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'transaction_type']);
            $table->index(['status', 'transaction_type']);
            $table->index('effective_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_transactions');
    }
};
