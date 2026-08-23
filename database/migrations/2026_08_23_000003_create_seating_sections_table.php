<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A pricing band and colour block within a level (Stalls, Circle, Standing).
     *
     * kind drives everything downstream:
     *   seated   - has seating_seats rows, sold per seat
     *   table    - has seating_tables, each holding seats; sold per seat or per table
     *   standing - NO seats, just a capacity. Maps to an ordinary quantity Ticket and
     *              rides the existing (untouched) stock path, which is how one event can
     *              sell a row of allocated seats alongside a standing area.
     *
     * ticket_id is the price band and is only ever set on a SNAPSHOT section - tickets
     * belong to an event, templates do not. band is the template-side label used to
     * auto-match a section to a ticket type of the same name at attach time.
     *
     * The owner ids are denormalized from seating_levels so "every section for this
     * occurrence" is one indexed lookup instead of a join through levels.
     */
    public function up(): void
    {
        Schema::create('seating_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seating_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seating_plan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('event_seating_map_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('name', 100);
            $table->string('color', 9)->default('#4E81FA'); // #RRGGBB or #RRGGBBAA
            $table->string('kind', 10)->default('seated');  // seated|table|standing
            $table->unsignedSmallInteger('capacity')->nullable(); // standing only
            $table->string('band', 100)->nullable();

            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            // Optional whole-table price: charges one unit for the table rather than
            // N x the band price. Null falls back to N x band.
            $table->foreignId('table_ticket_id')->nullable()->constrained('tickets')->nullOnDelete();

            // Wheelchair spaces are not merely labelled - a section flagged here is only
            // sellable through an accessibility ticket band.
            $table->boolean('accessibility_only')->default(false);

            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->smallInteger('rotation')->default(0);
            $table->unsignedSmallInteger('position')->default(0);
            // Optional polygon outline [[x,y],...] for stadium tiers and in-the-round;
            // null renders as an axis-aligned bounding box around the seats.
            $table->json('shape')->nullable();

            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index(['event_seating_map_id', 'is_deleted']);
            $table->index(['seating_plan_id', 'is_deleted']);
            $table->index(['ticket_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seating_sections');
    }
};
