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
        Schema::create('timekeeping_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_type'); // time_in, time_out, break_start, break_end, etc.
            $table->timestamp('transaction_time');
            $table->string('ip_address', 45)->nullable();
            $table->string('device_info')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active'); // active, voided, adjusted
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->string('void_reason')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'transaction_time']);
            $table->index(['attendance_id', 'transaction_type']);
            $table->index('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timekeeping_transactions');
    }
};
