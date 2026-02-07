<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payslips Table
 * 
 * Purpose: Store generated payslip PDFs and metadata
 * 
 * Design Decisions:
 * 1. Payslip is generated AFTER payroll is approved
 * 2. Stores PDF file path for retrieval
 * 3. Stores snapshot of payroll data for historical accuracy
 * 4. Tracks email delivery status
 * 5. Supports payslip versioning (re-generation)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->foreignId('payroll_period_id')->constrained()->onDelete('cascade');
            
            // Payslip Identification
            $table->string('payslip_number')->unique(); // e.g., PS-2026-02-001
            $table->integer('version')->default(1); // For re-generation tracking
            
            // File Storage
            $table->string('file_path')->nullable(); // Path to PDF file
            $table->string('file_name')->nullable(); // Original filename
            $table->integer('file_size')->nullable(); // In bytes
            $table->string('file_hash')->nullable(); // For integrity check
            
            // Snapshot of Payroll Data (for historical accuracy)
            // Even if payroll is modified, payslip retains original values
            $table->json('earnings_snapshot'); // Basic, OT, Holiday, Allowances
            $table->json('deductions_snapshot'); // SSS, PhilHealth, Pag-IBIG, Tax, etc.
            $table->json('attendance_snapshot'); // Work days, hours, late, etc.
            $table->decimal('gross_pay', 12, 2);
            $table->decimal('total_deductions', 12, 2);
            $table->decimal('net_pay', 12, 2);
            
            // Period Information Snapshot
            $table->date('period_start');
            $table->date('period_end');
            $table->date('pay_date');
            
            // Employee Information Snapshot (in case employee data changes)
            $table->json('employee_snapshot'); // Name, position, department, etc.
            
            // Status
            $table->enum('status', [
                'generating',    // PDF generation in progress
                'generated',     // PDF ready
                'failed',        // Generation failed
                'sent',          // Emailed to employee
                'viewed',        // Employee has viewed
                'downloaded',    // Employee has downloaded
            ])->default('generating');
            
            // Error Tracking
            $table->text('error_message')->nullable();
            
            // Generation Tracking
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('generated_at')->nullable();
            
            // Delivery Tracking
            $table->datetime('sent_at')->nullable();
            $table->string('sent_to_email')->nullable();
            $table->datetime('viewed_at')->nullable();
            $table->datetime('downloaded_at')->nullable();
            $table->integer('download_count')->default(0);
            
            // Security
            $table->string('access_token', 64)->nullable(); // For secure viewing link
            $table->datetime('token_expires_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'payroll_period_id']);
            $table->index('status');
            $table->index('payslip_number');
            $table->index('access_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
