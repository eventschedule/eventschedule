<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // No ->after('is_draft'): is_draft is added by a later-dated migration, so on a fresh
            // migrate this file runs before it and that column doesn't exist yet.
            $table->boolean('is_internal')->default(false);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->string('default_event_visibility')->default('public');
        });

        // Backfill the new per-schedule default from the legacy draft_events_default boolean.
        // That column lives in a later-dated migration, so on a fresh migrate it isn't there yet
        // (and a fresh database has no data to backfill); hasColumn() makes this a safe no-op there.
        // On existing databases (where the later migration already ran) it carries the setting over.
        if (Schema::hasColumn('roles', 'draft_events_default')) {
            DB::table('roles')
                ->where('draft_events_default', true)
                ->update(['default_event_visibility' => 'draft']);
        }
    }

    public function down(): void
    {
        // Do not touch draft_events_default here; it is owned by its own migration.
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_internal');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('default_event_visibility');
        });
    }
};
