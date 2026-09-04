<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Give every already-public event a published_at, so the column stops being a landmine.
 *
 * events.published_at has existed since 2024_07_13_184927_setup_database and nothing ever wrote
 * it, so every public row in production holds NULL. Event::boot()'s saving hook now stamps it on
 * the draft-to-public transition - but a legacy row has no stamp to compare against, so a public
 * event toggled to draft and back would satisfy that guard and be stamped TODAY, and
 * SendEventAnnouncements would mail it out as new to the schedule's whole audience.
 *
 * created_at is not a publication date we actually know. It is, however, precisely what
 * SendEventAnnouncements::newEventsFor() already reads for these rows - its predicate is
 * COALESCE(events.published_at, events.created_at) - so writing it here changes no behaviour
 * anywhere while removing the NULL the guard cannot reason about.
 *
 * Drafts are left alone: they have not been published, and the hook stamps them when they are.
 * Re-running is a no-op, because the whereNull no longer matches.
 *
 * NOTE FOR THE DEPLOY: neither is_draft nor published_at is indexed, so this scans `events` -
 * the same shape docs/NEXUS_RELEASE.md P2 flags for reset_untouched_coupon_discount_types. On a
 * large table, run it from the console before triggering the deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('events')
            ->where('is_draft', false)
            ->whereNull('published_at')
            ->whereNotNull('created_at')
            ->update(['published_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        // The values written here were created_at, which is still on the row. Nothing to restore,
        // and blanking the column again would re-arm the defect this migration exists to disarm.
    }
};
