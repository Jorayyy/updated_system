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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('category'); // Regular/Wholeday, etc.
            $table->time('time_in');
            $table->time('time_out');
            $table->integer('lunch_break_minutes')->default(60);
            $table->integer('first_break_minutes')->default(15);
            $table->integer('second_break_minutes')->default(15);
            $table->integer('registered_hours')->default(8);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
