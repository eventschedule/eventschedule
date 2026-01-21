<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Normalize country codes to lowercase for consistent matching
     */
    public function up(): void
    {
        // Normalize country_code to lowercase in roles table
        DB::statement('UPDATE roles SET country_code = LOWER(country_code) WHERE country_code IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     * No reversal needed - lowercase is the preferred format
     */
    public function down(): void
    {
        // No reversal needed
    }
};
