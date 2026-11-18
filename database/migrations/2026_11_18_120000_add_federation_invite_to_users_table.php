<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The invitation to share events on the Event Schedule network comes back now and then: at most
 * three times, thirty days apart (FederationService::inviteDue()). Each admin keeps their own clock.
 *
 *   federation_invite_at     when they were last asked (the dashboard's modal, or the schedule
 *                            page's banner answered with Not now)
 *   federation_invite_count  how many times
 *   federation_live_seen_at  when they saw the one-time "you're on the network" card
 *
 * The old banner's Dismiss meant never. It now counts as one Not now: an admin who said no once,
 * before there was anything to show on the network, is asked again in thirty days, and twice more
 * at most. "Don't ask again" (federation_prompt_dismissed) is what ends it now.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Guarded: MySQL does not roll DDL back, so a run that failed after this line must be able
        // to run again, or every later migrate on the install fails with "Duplicate column".
        if (! Schema::hasColumn('users', 'federation_invite_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('federation_invite_at')->nullable();
                $table->unsignedTinyInteger('federation_invite_count')->default(0);
                $table->timestamp('federation_live_seen_at')->nullable();
            });
        }

        $this->carryOverDismissals();

        // Cosmetic (the worst case is a late "you're live" card), so it never stops the migration.
        try {
            $this->markAlreadyLive();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** An old Dismiss is one Not now (FederationInviteTest calls this directly). */
    public function carryOverDismissals(): void
    {
        DB::table('users')
            ->where('federation_prompt_dismissed', true)
            ->update([
                'federation_invite_count' => 1,
                'federation_invite_at' => now(),
                'federation_prompt_dismissed' => false,
            ]);
    }

    /**
     * An install already listed on the network is past its first approved sync: the "you're on
     * the network" card is for the day that happens, not for this update. A count of zero records
     * the moment and shows nothing (FederationService::noteLive(), takeLiveCard()).
     */
    public function markAlreadyLive(): void
    {
        if (! config('app.is_nexus')
            && Setting::get('federation_status') === 'approved'
            && ! Setting::get('federation_live_at')
            && DB::table('events')->whereNotNull('federated_at')->exists()) {
            Setting::set('federation_live_at', (string) now());
            Setting::set('federation_live_count', '0');
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['federation_invite_at', 'federation_invite_count', 'federation_live_seen_at']);
        });
    }
};
