<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Things drawn on a level that are not seats: a stage, and free text labels.
 *
 * Until now a seat map had no way to say which end of the room the audience faces. Every other
 * ticketing product draws the stage, because without it a buyer looking at a grid of circles cannot
 * tell the front row from the back, and an organizer who angles the side blocks toward a focal
 * point is drawing a room whose focal point is invisible.
 *
 * A separate table rather than a fourth `seating_sections.kind`: a section carries a price band, a
 * ticket, seats and an accessibility flag, and every read site treats it as sellable inventory.
 * A stage is none of those things, and `kind` is validated as exactly seated|table|standing in
 * SeatingStructureService.
 *
 * Owned by a LEVEL, and through it by exactly one of a plan or a map, following the same XOR the
 * rest of this schema uses (asserted in App\Traits\SeatingOwnable, not by a CHECK constraint). A
 * stage belongs to a floor: the stalls and the balcony face the same one, but each level draws its
 * own, because a level is its own coordinate space.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seating_decorations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seating_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seating_plan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('event_seating_map_id')->nullable()->constrained()->cascadeOnDelete();

            // stage - a filled bar with centred text, the orientation marker.
            // text  - a bare label: BAR, ENTRANCE, EXIT, "no access during the first act".
            $table->string('kind', 10)->default('stage');
            $table->string('label', 100)->nullable();

            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->unsignedSmallInteger('width')->default(320);
            $table->unsignedSmallInteger('height')->default(40);
            $table->smallInteger('rotation')->default(0);
            $table->unsignedSmallInteger('position')->default(0);

            $table->timestamps();

            $table->index(['event_seating_map_id']);
            $table->index(['seating_plan_id']);
            $table->index(['seating_level_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seating_decorations');
    }
};
