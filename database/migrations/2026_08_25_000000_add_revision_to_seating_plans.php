<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A monotonic revision for a seating plan template, so the designer can refuse a save built on a
 * structure somebody else has since replaced.
 *
 * NOT updated_at. SeatingStructureService::save() writes only the child rows, so the plan row does
 * not move on its own - and a timestamp is second-resolution in MySQL, which is exactly why
 * event_seating_maps carries `version` rather than leaning on updated_at. A read and a save inside
 * the same second would compare equal and wave the overwrite straight through.
 *
 * event_seating_maps already has `version` and needs nothing here; this gives templates the
 * equivalent, bumped the same way (LAST_INSERT_ID(revision + 1)).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seating_plans', function (Blueprint $table) {
            $table->unsignedInteger('revision')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('seating_plans', function (Blueprint $table) {
            $table->dropColumn('revision');
        });
    }
};
