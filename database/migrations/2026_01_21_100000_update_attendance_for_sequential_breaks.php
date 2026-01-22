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
        // Add sequential break columns directly to attendance table
        Schema::table('attendances', function (Blueprint $table) {
            // 1st Break (Short break)
            $table->timestamp('first_break_out')->nullable()->after('time_in');
            $table->timestamp('first_break_in')->nullable()->after('first_break_out');
            
            // Lunch Break
            $table->timestamp('lunch_break_out')->nullable()->after('first_break_in');
            $table->timestamp('lunch_break_in')->nullable()->after('lunch_break_out');
            
            // 2nd Break (Short break)
            $table->timestamp('second_break_out')->nullable()->after('lunch_break_in');
            $table->timestamp('second_break_in')->nullable()->after('second_break_out');
            
            // Current step tracker
            $table->enum('current_step', [
                'time_in',
                'first_break_out',
                'first_break_in',
                'lunch_break_out',
                'lunch_break_in',
                'second_break_out',
                'second_break_in',
                'time_out',
                'completed'
            ])->default('time_in')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'first_break_out',
                'first_break_in',
                'lunch_break_out',
                'lunch_break_in',
                'second_break_out',
                'second_break_in',
                'current_step'
            ]);
        });
    }
};
