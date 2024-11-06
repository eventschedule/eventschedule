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


        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('accept_requests')->default(false);

            $table->dropColumn('accept_vendor_requests');
            $table->dropColumn('accept_talent_requests');            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
        $table->boolean('accept_talent_requests')->default(true);
        $table->boolean('accept_vendor_requests')->default(false);        

            $table->dropColumn('accept_requests');
        });
    }
};
