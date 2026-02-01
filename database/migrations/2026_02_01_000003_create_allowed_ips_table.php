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
        Schema::create('allowed_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique(); // Supports IPv6
            $table->string('label')->nullable(); // e.g., "Main Office", "Floor 2"
            $table->string('location')->nullable(); // Physical location
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('ip_address');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allowed_ips');
    }
};
