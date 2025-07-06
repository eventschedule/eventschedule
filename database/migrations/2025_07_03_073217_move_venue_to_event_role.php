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
        // First, backup the venue_id column
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('venue_id_bak')->nullable()->after('venue_id');
        });

        // Copy venue_id to venue_id_bak
        DB::statement('UPDATE events SET venue_id_bak = venue_id WHERE venue_id IS NOT NULL');

        // Add venue relationships to event_role table for existing events with venues
        $eventsWithVenues = DB::table('events')
            ->whereNotNull('venue_id')
            ->get();

        foreach ($eventsWithVenues as $event) {
            // Check if venue relationship already exists in event_role
            $existingVenueRole = DB::table('event_role')
                ->where('event_id', $event->id)
                ->where('role_id', $event->venue_id)
                ->first();

            if (!$existingVenueRole) {
                DB::table('event_role')->insert([
                    'event_id' => $event->id,
                    'role_id' => $event->venue_id,
                    'is_accepted' => $event->is_accepted,
                ]);
            }
        }

        // Remove the venue_id column from events table
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['venue_id']);
            $table->dropColumn('venue_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add venue_id column back to events table
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('venue_id')->nullable()->constrained('roles')->onDelete('cascade')->after('creator_role_id');
        });

        // Restore venue_id from venue_id_bak
        DB::statement('UPDATE events SET venue_id = venue_id_bak WHERE venue_id_bak IS NOT NULL');

        // Remove venue relationships from event_role table
        $venueRoles = DB::table('event_role')
            ->join('roles', 'event_role.role_id', '=', 'roles.id')
            ->where('roles.type', 'venue')
            ->select('event_role.event_id', 'event_role.role_id')
            ->get();

        foreach ($venueRoles as $venueRole) {
            DB::table('event_role')
                ->where('event_id', $venueRole->event_id)
                ->where('role_id', $venueRole->role_id)
                ->delete();
        }

        // Remove the backup column
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('venue_id_bak');
        });
    }
};
