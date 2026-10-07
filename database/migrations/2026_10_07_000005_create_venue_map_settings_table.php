<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A schedule's venue map (Engagement > Venue map): the owner's two switches and whether the map
 * has had its first pass.
 *
 * A table of its own, and not three columns on `roles`, because `roles` is full: it sits at
 * MySQL's 65,535-byte row limit, and an ALTER adding one more tinyint fails with errno 1118
 * (2026_09_07_000000 met the same wall with a varchar; 2026_10_07_000003 took the last byte).
 * No row means the map is off, which is every schedule until its owner switches one on.
 *
 * enabled: the owner's switch.
 * starts_open: start with the map open on larger screens, for visitors who allowed cookies.
 * ready_at: set by the lookup runner once every venue's address has been asked once, so a map is
 * never published half-placed (App\Services\VenueMap::refreshReady()). Never set by a form.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_map_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(false);
            $table->boolean('starts_open')->default(false);
            $table->timestamp('ready_at')->nullable();
            $table->timestamps();

            // What the runner reads: maps switched on that are still waiting for their first pass.
            $table->index(['enabled', 'ready_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_map_settings');
    }
};
