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
        Schema::table('shifts', function (Blueprint $table) {
            $table->unsignedBigInteger('payroll_group_id')->nullable()->after('id');
            $table->unsignedBigInteger('department_id')->nullable()->change();
            
            $table->foreign('payroll_group_id')->references('id')->on('payroll_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['payroll_group_id']);
            $table->dropColumn('payroll_group_id');
            // Reverting department_id to non-nullable in SQLite is tricky, 
            // but for down-migration it's often skipped or handled simplified.
        });
    }
};
