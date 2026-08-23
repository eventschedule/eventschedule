<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A floor or tier with its own drawing canvas (Stalls, Circle, Balcony).
     *
     * INVARIANT: exactly one of seating_plan_id / event_seating_map_id is set. Template
     * rows carry the plan, snapshot rows carry the map. Keeping both shapes in ONE table
     * is what lets a single designer component and a single renderer serve the template
     * and the per-date map. Enforced in SeatingOwnable (model side) rather than a CHECK
     * constraint, matching the rest of this schema.
     */
    public function up(): void
    {
        Schema::create('seating_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seating_plan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('event_seating_map_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('name', 100);
            $table->unsignedSmallInteger('position')->default(0);
            // Canvas extent in abstract design units; the client scales to fit.
            $table->unsignedSmallInteger('width')->default(1200);
            $table->unsignedSmallInteger('height')->default(800);
            $table->timestamps();

            $table->index(['seating_plan_id', 'position']);
            $table->index(['event_seating_map_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seating_levels');
    }
};
