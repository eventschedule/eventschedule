<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marks an event_role row written by CuratorSourceService rather than curated by hand.
     * Only these rows are removed when a source is dropped or an event stops qualifying.
     *
     * Deliberately no ->after(): several event_role columns live in migrations dated later
     * than this one, so anchoring to them breaks a fresh migrate.
     */
    public function up(): void
    {
        Schema::table('event_role', function (Blueprint $table) {
            $table->boolean('is_auto_sourced')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('event_role', function (Blueprint $table) {
            $table->dropColumn('is_auto_sourced');
        });
    }
};
