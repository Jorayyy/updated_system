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
        Schema::create('company_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('users')->onDelete('set null'); // Current holder
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->string('type')->comment('Laptop, Phone, Peripherals, ID, Uniform, etc.');
            $table->string('serial_number')->nullable();
            $table->text('description')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->enum('status', ['available', 'assigned', 'maintenance', 'lost', 'retired'])->default('available');
            $table->date('assigned_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_assets');
    }
};
