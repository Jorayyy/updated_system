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
            $table->foreignId('department_id')->nullable()->after('department')->constrained('departments')->nullOnDelete();
        });

        // Migrate Data
        $existingDepartments = DB::table('users')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department');

        foreach ($existingDepartments as $deptName) {
            $deptName = trim($deptName);
            if (empty($deptName)) continue;

            // Check if exists (idempotency)
            $dept = DB::table('departments')->where('name', $deptName)->first();
            
            if (!$dept) {
                $id = DB::table('departments')->insertGetId([
                    'name' => $deptName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $id = $dept->id;
            }

            // Update users
            DB::table('users')
                ->where('department', $deptName)
                ->update(['department_id' => $id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
