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
        Schema::create('concerns', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            // Concern details
            $table->string('title');
            $table->enum('category', [
                'technical',           // PC, software, hardware issues
                'network',             // Internet, connectivity issues
                'facilities',          // Office, chairs, AC, etc.
                'schedule',            // Shift changes, OT concerns
                'payroll',             // Salary, deductions
                'hr_related',          // Leave, benefits, policies
                'training',            // Training needs, skill gaps
                'performance',         // Performance feedback, coaching
                'safety',              // Safety hazards, health concerns
                'suggestion',          // Improvements, ideas
                'complaint',           // General complaints
                'other'                // Other concerns
            ]);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('description');
            $table->string('location')->nullable(); // Where the issue occurred
            $table->string('affected_pc')->nullable(); // PC number if technical
            
            // Status tracking
            $table->enum('status', [
                'open',
                'in_progress',
                'pending_info',
                'escalated',
                'resolved',
                'closed',
                'cancelled'
            ])->default('open');
            
            // Resolution
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            
            // SLA tracking
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('due_date')->nullable();
            
            // Additional info
            $table->boolean('is_confidential')->default(false);
            $table->string('attachment')->nullable();
            
            $table->timestamps();
        });

        // Concern comments/replies
        Schema::create('concern_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concern_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->boolean('is_internal')->default(false); // Internal notes not visible to reporter
            $table->timestamps();
        });

        // Concern activity log
        Schema::create('concern_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concern_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // e.g., 'status_changed', 'assigned', 'commented'
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concern_activities');
        Schema::dropIfExists('concern_comments');
        Schema::dropIfExists('concerns');
    }
};
