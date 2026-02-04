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
            $table->decimal('meal_allowance', 10, 2)->default(0)->after('monthly_salary');
            $table->decimal('transportation_allowance', 10, 2)->default(0)->after('meal_allowance');
            $table->decimal('communication_allowance', 10, 2)->default(0)->after('transportation_allowance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['meal_allowance', 'transportation_allowance', 'communication_allowance']);
        });
    }
};
