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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('payroll_group_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->foreignId('payroll_group_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['payroll_group_id']);
            $table->dropColumn('payroll_group_id');
        });

        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropForeign(['payroll_group_id']);
            $table->dropColumn('payroll_group_id');
        });
    }
};
