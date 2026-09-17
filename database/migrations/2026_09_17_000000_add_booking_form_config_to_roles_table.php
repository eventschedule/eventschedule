<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Owner options for the guest booking request form (issue #124): which of its default fields a
 * visitor must fill in, and whether it offers the Online option at all.
 *
 * Read through Role::bookingFormConfig(), which fills in the defaults, so null - every existing
 * schedule - means nothing is required and Online is offered, exactly as the form behaved before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // json, like the other recent settings columns. It is stored off-page, so it costs a
            // pointer rather than row space - `roles` is close to MySQL's 65,535-byte row limit
            // (see 2026_09_07_000000). No ->after(): column order is cosmetic.
            $table->json('booking_form_config')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('booking_form_config');
        });
    }
};
