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
            DB::statement("ALTER TABLE `roles` MODIFY `type` ENUM('venue', 'talent', 'vendor', 'curator', 'schedule') NOT NULL");
        }

        DB::table('roles')
            ->whereIn('type', ['talent', 'vendor'])
            ->update(['type' => 'schedule']);
        
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE `roles` MODIFY `type` ENUM('venue', 'curator', 'schedule') NOT NULL");
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
