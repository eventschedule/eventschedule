<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One occurrence's own seat map - the snapshot taken from a seating_plans template.
     *
     * Keyed by (event_id, event_date) because a recurring occurrence is identified by a
     * Y-m-d STRING everywhere else in this app (sales.event_date, ticket_waitlists), not
     * by a date column. Resolve a missing date through Event::saleEventDateFromStartsAt()
     * exactly as checkout does, or the map and the oversell guard will key differently and
     * a one-time event will silently get two maps.
     *
     * Materialization is lazy - first designer open, first guest view or first box-office
     * view - so a recurring event with 200 future dates costs nothing until a date is
     * actually touched.
     */
    public function up(): void
    {
        Schema::create('event_seating_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('event_date');
            // Provenance only. The map is self-contained once materialized, so a deleted
            // template must not take the sold seats with it.
            $table->foreignId('seating_plan_id')->nullable()->constrained()->nullOnDelete();

            $table->boolean('orphan_rule_enabled')->default(true);
            // Largest run of stranded seats to reject. 1 = never leave a single seat alone.
            $table->unsignedTinyInteger('orphan_rule_min_gap')->default(1);
            // Percent sold past which the rule stops applying to ONLINE selections, so it
            // cannot block the last few seats. Box office is always exempt.
            $table->unsignedTinyInteger('orphan_rule_lift_pct')->default(90);

            // Monotonic cursor for the state poll. Bumped on every seat state change and
            // stamped onto the changed seats, so a poll is an exact indexed range scan
            // rather than a timestamp comparison at MySQL's one-second resolution.
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('materialized_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_seating_maps');
    }
};
