<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A reusable seating plan TEMPLATE owned by a schedule (e.g. "Main House").
     *
     * A template is never sold from directly. When an occurrence first needs a seat
     * map it is COPIED into occurrence-owned rows (see event_seating_maps), which is
     * what makes "edit this date only" possible and stops a template edit from
     * disturbing an event that has already sold seats.
     */
    public function up(): void
    {
        Schema::create('seating_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            // Rows are never hard-deleted: a materialized map keeps seating_plan_id as
            // provenance, and reports read the name back through it.
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index(['role_id', 'is_deleted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seating_plans');
    }
};
