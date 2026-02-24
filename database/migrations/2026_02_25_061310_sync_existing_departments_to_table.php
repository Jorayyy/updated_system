<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Department;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find all unique department names currently in the users table
        $userDepartments = User::whereNotNull('department')
            ->where('department', '!=', '')
            ->pluck('department')
            ->unique();

        foreach ($userDepartments as $name) {
            // FirstOrCreate to ensure we don't duplicate
            $dept = Department::firstOrCreate(['name' => $name]);
            
            // Link users to this department ID
            User::where('department', $name)->update(['department_id' => $dept->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data sync doesn't require schema reversal
    }
};
