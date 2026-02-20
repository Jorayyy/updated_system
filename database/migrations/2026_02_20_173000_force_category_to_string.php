<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Force the category column to be a VARCHAR(255) using raw SQL
        // This bypasses any ENUM restriction issues on the server
        DB::statement("ALTER TABLE concerns MODIFY COLUMN category VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert specifically as string is more flexible
    }
};
