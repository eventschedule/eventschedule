<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An owner's switch for the sponsors section of the guest pages (Engagement > Sponsors).
 *
 * Until now the section showed whenever roles.sponsor_logos held a sponsor, so the only way to
 * take it off a page for a while was to delete the sponsors and upload them again. Off hides the
 * section on the schedule page and on every event that shows the schedule's sponsors; the list
 * itself is untouched. On by default, so nothing changes for anyone.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // A byte, which `roles` can afford even this close to MySQL's 65,535-byte row limit
            // (see 2026_09_07_000000). No ->after(): column order is cosmetic.
            $table->boolean('show_sponsors')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('show_sponsors');
        });
    }
};
