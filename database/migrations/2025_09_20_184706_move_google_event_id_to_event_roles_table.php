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
        // Add google_event_id column to event_role table
        Schema::table('event_role', function (Blueprint $table) {
            $table->string('google_event_id')->nullable()->after('group_id');
        });

        // Remove google_event_id column from events table
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('google_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add google_event_id column back to events table
        Schema::table('events', function (Blueprint $table) {
            $table->string('google_event_id')->nullable();
        });

        // Remove google_event_id column from event_role table
        Schema::table('event_role', function (Blueprint $table) {
            $table->dropColumn('google_event_id');
        });
    }
};