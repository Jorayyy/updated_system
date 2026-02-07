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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('night_diff_pay', 12, 2)->default(0)->after('holiday_pay');
            $table->decimal('rest_day_pay', 12, 2)->default(0)->after('night_diff_pay');
            $table->decimal('bonus', 12, 2)->default(0)->after('rest_day_pay');
            $table->decimal('loan_deductions', 12, 2)->default(0)->after('absent_deductions');
            $table->decimal('leave_without_pay_deductions', 12, 2)->default(0)->after('loan_deductions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['night_diff_pay', 'rest_day_pay', 'bonus', 'loan_deductions', 'leave_without_pay_deductions']);
        });
    }
};
