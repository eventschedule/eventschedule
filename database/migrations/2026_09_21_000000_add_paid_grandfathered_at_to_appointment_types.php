<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Charging for an appointment becomes a Pro feature, and this is what stops that being a
     * breaking change for anyone already doing it.
     *
     * A free appointment type is unaffected; a type with a PRICE on it now needs Pro, exactly as a
     * ticket with a price does (Event::canSellPaidTickets()). The marketing site
     * has been promising the opposite - "money changes hands through your own Stripe account on any
     * plan" - so every priced type that exists when this runs keeps working.
     *
     * DELIBERATELY WIDER THAN THE TICKET RULE. 2026_09_20_000000 stamps an event only on evidence of
     * a real paid sale, because a free schedule could have unboundedly many priced events. Here the
     * free plan allows exactly ONE appointment type, so the affected set is at most one row per
     * free schedule: small, knowable, and not worth stranding someone over. A schedule that
     * configured a paid consultation last week and has not been booked yet keeps it.
     *
     * Stamped once, never re-derived at request time. A live "is this type priced?" question would
     * be answered by the owner's own editor, so it would gate nothing; and a stored stamp cannot be
     * minted by restoring a crafted backup, because BackupService::exportAppointmentTypes() is an
     * explicit include-list that does not carry this column and importAppointmentType() assigns
     * every column by hand. The other way to mint one is Clone, which is why
     * AppointmentTypeController::duplicate() excludes it from replicate().
     */
    public function up(): void
    {
        Schema::table('appointment_types', function (Blueprint $table) {
            if (! Schema::hasColumn('appointment_types', 'paid_grandfathered_at')) {
                // No ->after(): column order is cosmetic and positional adds forfeit
                // ALGORITHM=INSTANT, per 2026_09_17_000002.
                $table->timestamp('paid_grandfathered_at')->nullable();
            }
        });

        $this->backfill();
    }

    /**
     * Public and re-runnable so a test can exercise it directly, like the ticket backfill.
     * Idempotent: an already-stamped row is skipped by the whereNull.
     */
    public function backfill(): void
    {
        $now = now();

        // Asks Role::isPro() in PHP rather than hand-rolling its SQL. That predicate ORs a Stripe
        // subscription, a generic trial, the enterprise check and the legacy plan_expires/plan_type
        // pair, and a migration that reimplemented it would drift from it silently. The affected set
        // is at most one priced type per free schedule, so there is nothing to optimise for.
        //
        // Both status filters, and both are load-bearing. An inactive type takes no bookings, so it
        // has no promise to keep and stamping it would hand the grandfather to anyone with a paused
        // draft lying around.
        //
        // is_deleted is filtered explicitly rather than leaned on: an earlier version of this
        // comment argued it was redundant because destroy() sets is_deleted and is_active together.
        // That is true of destroy() but NOT of BackupService::importAppointmentType(), which assigns
        // both independently from user-supplied backup JSON, so a restored row can arrive
        // is_deleted = true with is_active = true. The stamp would be inert either way - every read
        // path checks is_deleted - but a filter that costs nothing should not rest on an invariant
        // one import path does not hold.
        \App\Models\AppointmentType::query()
            ->with('role')
            ->where('is_active', true)
            ->where('is_deleted', false)
            ->where('price', '>', 0)
            ->whereNull('paid_grandfathered_at')
            ->chunkById(500, function ($types) use ($now) {
                $stamp = $types
                    // A Pro schedule needs no stamp, and giving it one would survive a later
                    // downgrade it never paid through.
                    ->filter(fn ($type) => $type->role && ! $type->role->isPro())
                    ->pluck('id')
                    ->all();

                if ($stamp) {
                    DB::table('appointment_types')
                        ->whereIn('id', $stamp)
                        ->update(['paid_grandfathered_at' => $now]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('appointment_types', function (Blueprint $table) {
            $table->dropColumn('paid_grandfathered_at');
        });
    }
};
