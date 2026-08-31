<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Automatic new-event announcements to a schedule's confirmed audience.
 *
 * The subscribe panel, the follow modal and the checkout opt-in all promise the visitor an email
 * "when :schedule adds a new event", and the confirmation email promises a cadence of "at most one
 * every few days". Nothing sent it: role_subscribers was reachable only from the newsletter
 * composer, by an owner who remembered. These two columns are what App\Console\Commands\
 * SendEventAnnouncements needs to keep that promise without becoming a mailshot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Owner opt-out, default ON: the GUEST asked for these emails, not the owner, so the
            // safe default is the one that honours what the guest was told.
            $table->boolean('announce_new_events')->default(true);

            // Doubles as the cadence floor and as the first-run watermark.
            //
            // NULL means this schedule has never been considered. The command stamps it WITHOUT
            // sending on that first pass, which is what stops the very first run announcing every
            // event in the historical base - 542 dormant schedules would otherwise each mail their
            // whole audience at once, from the platform's shared sending reputation.
            $table->timestamp('last_announced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['announce_new_events', 'last_announced_at']);
        });
    }
};
