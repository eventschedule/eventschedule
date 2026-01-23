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
        Schema::table('event_role', function (Blueprint $table) {
            $table->string('caldav_event_uid')->nullable();  // CalDAV event UID
            $table->string('caldav_event_etag')->nullable(); // For conflict detection
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_role', function (Blueprint $table) {
            $table->dropColumn([
                'caldav_event_uid',
                'caldav_event_etag',
            ]);
        });
    }
};
