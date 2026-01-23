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
        Schema::table('roles', function (Blueprint $table) {
            $table->text('caldav_settings')->nullable();           // Encrypted JSON (server_url, username, password, calendar_url)
            $table->enum('caldav_sync_direction', ['to', 'from', 'both'])->nullable();
            $table->string('caldav_sync_token')->nullable();       // For incremental sync (ctag)
            $table->timestamp('caldav_last_sync_at')->nullable();  // Last poll timestamp
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn([
                'caldav_settings',
                'caldav_sync_direction',
                'caldav_sync_token',
                'caldav_last_sync_at',
            ]);
        });
    }
};
