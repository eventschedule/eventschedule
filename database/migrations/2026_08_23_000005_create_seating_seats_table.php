<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The seat itself - and, on a snapshot, its live state.
     *
     * NAMING: this is NOT sale_tickets.seats. That column is a JSON map of admission slot
     * number to check-in timestamp and has nothing to do with location. Seat ASSIGNMENT is
     * read through this table's sale_id / sale_ticket_id.
     *
     * Because a snapshot seat is a real row, its state lives on it and the primary key is
     * the double-booking guarantee. Two buyers racing the same seat serialise on
     * SELECT ... FOR UPDATE over these rows inside the existing checkout transaction.
     *
     * HOLD EXPIRY IS EVALUATED AT READ TIME. Every availability predicate treats
     * status='held' AND hold_expires_at < now() as available, so correctness never depends
     * on a sweeper running - selfhost ships QUEUE_CONNECTION=sync and a minute-granularity
     * scheduler. The sweep in ReleaseTickets is housekeeping only. Box office and house
     * holds carry a NULL hold_expires_at and therefore never lapse.
     */
    public function up(): void
    {
        Schema::create('seating_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seating_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seating_table_id')->nullable()->constrained()->cascadeOnDelete();
            // Denormalized from the section so the guest picker and the state poll - which
            // both run constantly - are one indexed lookup rather than a three-level join.
            $table->foreignId('seating_plan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('event_seating_map_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('row_label', 10)->nullable();
            // Sort key for the row. row_label alone sorts lexicographically, so in a house with
            // more than 26 rows "AA" lands before "B" - which misorders the designer and the
            // printed plan, and worse, gives the orphan-seat rule the wrong idea of which seats
            // are next to each other.
            $table->unsignedSmallInteger('row_position')->default(0);
            // Null means "any seat at this table" - the guest books a chair, not a number.
            $table->string('seat_label', 10)->nullable();
            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->string('kind', 20)->default('standard'); // standard|wheelchair|companion|restricted_view
            // Marks a physical gangway to the RIGHT of this seat. The orphan rule treats it
            // as a row boundary, so two seats either side of an aisle are not "adjacent"
            // and leaving one of them alone is not stranding it.
            $table->boolean('aisle_after')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            // Template seat this was copied from. Deliberately NOT a foreign key: the
            // template may be edited or deleted long after the snapshot was taken.
            $table->unsignedBigInteger('source_seat_id')->nullable();

            $table->string('status', 10)->default('available'); // available|held|sold
            $table->string('hold_kind', 20)->nullable(); // cart|house|production|accessibility|box_office
            $table->string('hold_note', 255)->nullable(); // INTERNAL ONLY - never rendered to a guest
            $table->string('hold_token', 64)->nullable();
            $table->timestamp('hold_expires_at')->nullable();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_ticket_id')->nullable()->constrained()->nullOnDelete();
            // Stamped with event_seating_maps.version on every state change; the poll asks
            // for state_version > cursor.
            $table->unsignedInteger('state_version')->default(0);

            $table->timestamps();

            // Map load (prefix), per-section availability counts, and status totals.
            $table->index(['event_seating_map_id', 'seating_section_id', 'status'], 'seating_seats_map_section_status_idx');
            // The diff poll.
            $table->index(['event_seating_map_id', 'state_version'], 'seating_seats_map_version_idx');
            // Row ordering for the orphan rule, the designer and the printed plan. row_label
            // lookups ("C14" at the box office) scan within the section, which is small.
            $table->index(['seating_section_id', 'row_position', 'position'], 'seating_seats_section_row_pos_idx');
            $table->index(['seating_plan_id']);
            $table->index(['sale_id']);
            $table->index(['hold_token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seating_seats');
    }
};
