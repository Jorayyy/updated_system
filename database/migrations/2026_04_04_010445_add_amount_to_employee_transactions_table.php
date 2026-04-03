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
        Schema::table('employee_transactions', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable()->after('status');
            $table->string('type')->nullable()->after('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_transactions', function (Blueprint $table) {
            $table->dropColumn(['amount', 'type']);
        });
    }
};
