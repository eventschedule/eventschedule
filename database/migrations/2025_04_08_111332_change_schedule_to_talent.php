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
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE `roles` MODIFY `type` ENUM('venue', 'talent', 'curator', 'schedule') NOT NULL");
        }

        DB::table('roles')
            ->where('type', 'schedule')
            ->update(['type' => 'talent']);
        
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE `roles` MODIFY `type` ENUM('venue', 'curator', 'talent') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
