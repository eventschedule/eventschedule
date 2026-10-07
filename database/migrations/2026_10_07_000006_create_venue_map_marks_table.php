<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a schedule's owner has decided about one venue on THEIR venue map: off the map, or a
 * position set by hand (Engagement > Venue map > Venues).
 *
 * A mark belongs to the pair: role_id is the schedule whose map it is, venue_id the venue. The
 * same venue on another schedule's map is untouched, and nothing here changes the venue itself,
 * its address, or the looked-up position in place_lookups, which "Use the looked-up position"
 * goes back to by deleting the mark.
 *
 * hidden: the venue is not on this map (it stays in the owner's list, to be put back).
 * lat / lon: where the owner put the pin. Both set or both null. Their own data, never a
 * geocoder's, so nothing about a provider's terms applies to them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_map_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venue_id')->constrained('roles')->cascadeOnDelete();
            $table->boolean('hidden')->default(false);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lon', 10, 7)->nullable();
            $table->timestamps();

            $table->unique(['role_id', 'venue_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_map_marks');
    }
};
