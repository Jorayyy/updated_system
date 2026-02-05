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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // SSS, Pag-IBIG, Personal, etc.
            $table->decimal('total_amount', 12, 2);
            $table->decimal('remaining_balance', 12, 2);
            $table->decimal('monthly_amortization', 12, 2);
            $table->date('payment_start_date');
            $table->string('status')->default('pending'); // pending, approved, paid, rejected
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
