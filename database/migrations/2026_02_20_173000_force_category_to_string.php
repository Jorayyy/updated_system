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
        // Use the native schema builder to change the column type
        // This handles SQLite's lack of MODIFY COLUMN automatically
        Schema::table('concerns', function (Blueprint $table) {
            $table->string('category', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Handled by change()
    }
};
