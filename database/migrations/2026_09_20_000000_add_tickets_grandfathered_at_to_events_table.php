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
        // Added on its own, before the backfill, so a timeout part-way through the stamping leaves
        // a usable (if unstamped) schema rather than a half-applied migration.
        Schema::table('events', function (Blueprint $table) {
            $table->timestamp('tickets_grandfathered_at')->nullable();
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
            // The filter set is lifted verbatim from Role::paidTicketsSoldSince(), which this
            // change deletes. A ticket counts only when the sale is paid and not deleted, its
            // payment method is neither rsvp nor import, and the ticket itself is not an add-on
            // and has a price above zero. Event::hasSettledMoney() is deliberately NOT used - it
            // filters on status alone, so an event with nothing but free RSVPs would stamp itself.
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
                ->where('tickets.price', '>', 0)
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
