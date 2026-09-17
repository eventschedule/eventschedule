<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Owner switches for the two guest-page email sign-up surfaces (Settings > Advanced).
 *
 * show_subscribe_panel: the "Stay up to date" panel on the schedule page and at the foot of each
 * event page (partials/subscribe-panel.blade.php). On by default, so nothing changes for anyone.
 *
 * show_event_interest: the "Tell me when tickets go on sale" card on event pages
 * (event/partials/interest-capture.blade.php) and the links that jump to it. OFF by default, and
 * that includes every existing schedule: the default applies to the rows already in the table,
 * which is the point - the card becomes something an owner opts into. Addresses already on an
 * interest list keep getting the emails they asked for; only new sign-ups stop.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Booleans cost a byte each, which `roles` can afford even this close to MySQL's
            // 65,535-byte row limit (see 2026_09_07_000000). No ->after(): column order is cosmetic.
            $table->boolean('show_subscribe_panel')->default(true);
            $table->boolean('show_event_interest')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['show_subscribe_panel', 'show_event_interest']);
        });
    }
};
