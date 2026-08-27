<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The single-seat rule's settings, as TEMPLATE defaults.
 *
 * event_seating_maps has carried orphan_rule_enabled / _min_gap / _lift_pct since the feature
 * shipped. They are enforced on every guest selection and nothing has ever been able to change
 * them: no writer, no screen. The user guide documents that as a limitation - "The single-seat rule
 * has no setting. It is on for every allocated event."
 *
 * Settings belong on the plan as well as the occurrence for the same reason the layout does: a
 * venue decides once how it sells, and each date inherits that at materialize() while staying
 * free to differ. A comedy club selling barstools turns the rule off for the room, not for
 * thirty nights one at a time.
 *
 * Defaults match the occurrence columns exactly, so every existing plan behaves as it does today.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seating_plans', function (Blueprint $table) {
            $table->boolean('orphan_rule_enabled')->default(true);
            $table->unsignedTinyInteger('orphan_rule_min_gap')->default(1);
            $table->unsignedTinyInteger('orphan_rule_lift_pct')->default(90);
        });
    }

    public function down(): void
    {
        Schema::table('seating_plans', function (Blueprint $table) {
            $table->dropColumn(['orphan_rule_enabled', 'orphan_rule_min_gap', 'orphan_rule_lift_pct']);
        });
    }
};
