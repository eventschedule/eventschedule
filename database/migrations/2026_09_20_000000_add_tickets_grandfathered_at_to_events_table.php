<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Paid ticket selling goes back to Pro/Enterprise, and this column is the one-time amnesty.
 *
 * An event that has ALREADY taken a paid sale keeps selling on a free plan. The privilege is
 * recorded here once, by this migration, and never re-derived at request time. That is deliberate:
 * BackupService::importSale() takes `status` from a user-supplied backup JSON and a restore always
 * lands on a NEW FREE schedule, so a live "does this event have a paid sale?" query would let
 * anyone unlock paid selling for ever by restoring a crafted backup.
 *
 * The column is NOT in Event::$fillable, which is what keeps it out of both halves of the backup
 * round-trip (exportEvent() and importEvent() each walk getFillable()). Do not add it.
 */
return new class extends Migration
{
    /** Events stamped per statement. Small enough that no single UPDATE holds events for long. */
    private const CHUNK = 1000;

    public function up(): void
    {
        // Guarded, and that is load-bearing rather than hygiene. Laravel writes the migrations row
        // only AFTER up() returns, and MySQL DDL auto-commits, so if the backfill below throws -
        // lock wait, PHP timeout, a killed connection on a large sales table - the column exists but
        // the migration is unlogged. Without this guard the retry dies on "Duplicate column name"
        // and the deploy is stuck until someone hand-edits the production migrations table.
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'tickets_grandfathered_at')) {
                $table->timestamp('tickets_grandfathered_at')->nullable();
            }
        });

        $this->backfill();
    }

    /**
     * Stamp every event that has already taken a genuine paid sale.
     *
     * Public and separate from up() so it can be exercised directly: this runs exactly once
     * against production and cannot be re-run, so the filter set below is worth a test.
     * See tests/Feature/TicketGrandfatherBackfillTest.php.
     */
    public function backfill(): void
    {
        $now = now();
        $lastId = 0;

        do {
            // The filter set started as a verbatim lift from Role::paidTicketsSoldSince(), which
            // this change deletes. A ticket counts only when the sale is paid and not deleted, its
            // payment method is neither rsvp nor import, and the ticket itself is not an add-on.
            // Event::hasSettledMoney() is deliberately NOT used - it filters on status alone, so an
            // event with nothing but free RSVPs would stamp itself.
            //
            // The price half is where the lift had to STOP being verbatim. sale_tickets records no
            // price, so `tickets.price > 0` alone asks what the ticket costs TODAY, and that is the
            // wrong question for a permanent, one-shot, irreversible entitlement: an event that
            // genuinely took $20 sales in March, whose owner has since edited that tier to 0 or
            // reused it for a free one, would never be stamped and would stop selling on deploy with
            // no way back (the column is not fillable, not exported, and written nowhere else).
            // That drift was harmless in paidTicketsSoldSince(), which was a monthly rate-limit
            // counter; the same predicate is not equally safe in both roles.
            //
            // So the OR: sales.payment_amount is what actually changed hands. It is the per-seat
            // figure on a group or order primary (Sale::chargedTotal()'s docblock explains why) and
            // it is net of gift card and discount, but both are fine for a `> 0` existence test.
            // The one case it still misses is a sale paid ENTIRELY by gift card on a ticket since
            // edited to 0, which records 0 here and no longer matches the price arm either. Left as
            // is: widening an amnesty only ever hands out a free pass, while narrowing it breaks a
            // paying customer irrecoverably, so the asymmetry says err wide.
            $eventIds = DB::table('sale_tickets')
                ->join('sales', 'sales.id', '=', 'sale_tickets.sale_id')
                ->join('tickets', 'tickets.id', '=', 'sale_tickets.ticket_id')
                ->join('events', 'events.id', '=', 'sales.event_id')
                // Appointment bookings create real Sale and SaleTicket rows against a priced
                // ticket, so without this filter booking a paid appointment - which is free on
                // every plan - would grandfather its container event into paid TICKET selling.
                // The allowance query this filter set comes from carried the same exclusion.
                ->whereNull('events.appointment_type_id')
                ->where('sales.status', 'paid')
                ->where('sales.is_deleted', false)
                ->whereNotIn('sales.payment_method', ['rsvp', 'import'])
                ->where('tickets.is_addon', false)
                ->where(fn ($q) => $q->where('tickets.price', '>', 0)
                    ->orWhere('sales.payment_amount', '>', 0))
                ->whereNull('events.tickets_grandfathered_at')
                ->whereNotNull('sales.event_id')
                ->where('sales.event_id', '>', $lastId)
                ->orderBy('sales.event_id')
                ->distinct()
                ->limit(self::CHUNK)
                ->pluck('sales.event_id')
                ->all();

            if (! $eventIds) {
                break;
            }

            DB::table('events')
                ->whereIn('id', $eventIds)
                ->update(['tickets_grandfathered_at' => $now]);

            $lastId = (int) end($eventIds);
        } while (count($eventIds) === self::CHUNK);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('tickets_grandfathered_at');
        });
    }
};
