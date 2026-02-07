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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('payroll_period_id')->constrained()->onDelete('cascade');
            
            // Work hours
            $table->integer('total_work_days')->default(0);
            $table->integer('total_work_minutes')->default(0);
            $table->integer('total_overtime_minutes')->default(0);
            $table->integer('total_undertime_minutes')->default(0);
            $table->integer('total_late_minutes')->default(0);
            $table->integer('total_absent_days')->default(0);
            
            // Earnings
            $table->decimal('basic_pay', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('holiday_pay', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2)->default(0);
            
            // Deductions
            $table->decimal('sss_contribution', 12, 2)->default(0);
            $table->decimal('philhealth_contribution', 12, 2)->default(0);
            $table->decimal('pagibig_contribution', 12, 2)->default(0);
            $table->decimal('withholding_tax', 12, 2)->default(0);
            $table->decimal('late_deductions', 12, 2)->default(0);
            $table->decimal('undertime_deductions', 12, 2)->default(0);
            $table->decimal('absent_deductions', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            
            // Net pay
            $table->decimal('net_pay', 12, 2)->default(0);
            
            $table->enum('status', ['draft', 'computed', 'approved', 'released'])->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'payroll_period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
