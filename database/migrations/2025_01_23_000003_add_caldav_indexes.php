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
            $table->index('caldav_sync_direction');
        });

        Schema::table('event_role', function (Blueprint $table) {
            $table->index('caldav_event_uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex(['caldav_sync_direction']);
        });

        Schema::table('event_role', function (Blueprint $table) {
            $table->dropIndex(['caldav_event_uid']);
        });
    }
};
