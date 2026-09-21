<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The one-time amnesty behind events.tickets_grandfathered_at.
 *
 * This backfill runs exactly once, on deploy, against a production database nobody can reach from
 * a dev machine, and it cannot be re-run - an event it fails to stamp silently stops selling, and
 * one it stamps wrongly sells for ever on a free plan. So the predicate gets its own test.
 *
 * It calls the migration's backfill() directly rather than re-implementing the query: a test that
 * restates the filter set would pass whatever the migration actually does.
 */
class TicketGrandfatherBackfillTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function runBackfill(): void
    {
        // Globbed, not a hardcoded path: a rename makes `require` a FATAL that kills the whole
        // PHPUnit process rather than failing one test, and CLAUDE.md's "use today's date" rule
        // makes a rename likely if this sits unshipped. It also fails the build if the migration
        // was never committed, which is the mistake this file exists to catch.
        $file = collect(File::glob(database_path('migrations/*_add_tickets_grandfathered_at_to_events_table.php')))->first();

        $this->assertNotNull($file, 'the grandfather migration is missing from database/migrations');

        $migration = require $file;

        // RefreshDatabase has already run up(), so the column exists; only the stamping is re-run.
        Event::query()->update(['tickets_grandfathered_at' => null]);
        $migration->backfill();
    }

    private function stamped(Event $event): bool
    {
        return $event->fresh()->tickets_grandfathered_at !== null;
    }

    public function test_an_event_with_a_genuine_paid_sale_is_stamped(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);
        $this->createSale($event, $role, ['status' => 'paid'], $ticket, 1);

        $this->runBackfill();

        $this->assertTrue($this->stamped($event));
        $this->assertTrue($event->fresh()->canSellTickets(), 'and it therefore keeps selling');
    }

    /**
     * The case the price filter alone gets wrong.
     *
     * sale_tickets records no price, so `tickets.price > 0` asks what the tier costs TODAY. An
     * owner who sold at $20 and has since edited that tier down to 0 - or reused it as a free one -
     * would never be stamped, and would stop selling on deploy with no recovery path: the column is
     * not fillable, not exported, and written nowhere but this migration. sales.payment_amount is
     * the evidence that money actually moved.
     */
    public function test_a_tier_whose_price_was_edited_to_zero_after_the_sale_is_still_stamped(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);
        $sale = $this->createSale($event, $role, ['status' => 'paid'], $ticket, 1);

        // Money changed hands at $20 ...
        $sale->forceFill(['payment_amount' => 20])->save();
        // ... and the owner later made the tier free.
        $ticket->forceFill(['price' => 0])->save();

        $this->runBackfill();

        $this->assertTrue($this->stamped($event));
        $this->assertTrue($event->fresh()->canSellTickets(), 'and it therefore keeps selling');
    }

    public function test_it_stamps_a_pro_schedules_events_too(): void
    {
        // Deliberate: the amnesty is not scoped to schedules that are free TODAY, so a Pro
        // schedule that lapses next month keeps selling the events it has already sold.
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);
        $this->createSale($event, $role, ['status' => 'paid'], $ticket, 1);

        $this->runBackfill();

        $this->assertTrue($this->stamped($event));
    }

    /**
     * Everything that is NOT a genuine paid sale. Each of these would hand a free schedule
     * permanent paid selling if the filter set were loosened, which is why they are one test:
     * a reviewer should see the whole excluded set at once.
     */
    public function test_nothing_but_a_genuine_paid_sale_stamps_an_event(): void
    {
        $role = $this->createFreeRole();

        $cases = [];

        // A free RSVP. payment_method 'rsvp' is excluded.
        $rsvp = $this->createEvent($role, ['tickets_enabled' => true, 'rsvp_enabled' => true]);
        $rsvpTicket = $this->createTicket($rsvp, ['price' => 20, 'quantity' => 100]);
        $this->createSale($rsvp, $role, ['status' => 'paid', 'payment_method' => 'rsvp'], $rsvpTicket, 1);
        $cases['a free RSVP'] = $rsvp;

        // A bulk import. payment_method 'import' is excluded.
        $import = $this->createEvent($role, ['tickets_enabled' => true]);
        $importTicket = $this->createTicket($import, ['price' => 20, 'quantity' => 100]);
        $this->createSale($import, $role, ['status' => 'paid', 'payment_method' => 'import'], $importTicket, 1);
        $cases['a bulk import'] = $import;

        // A zero-price row, which was never worth money.
        $zero = $this->createEvent($role, ['tickets_enabled' => true]);
        $zeroTicket = $this->createTicket($zero, ['price' => 0, 'quantity' => 100]);
        $this->createSale($zero, $role, ['status' => 'paid'], $zeroTicket, 1);
        $cases['a zero-price ticket'] = $zero;

        // An add-on, which follows Event::canSellAddons() rather than this gate.
        $addon = $this->createEvent($role, ['tickets_enabled' => true]);
        $addonTicket = $this->createTicket($addon, ['price' => 20, 'quantity' => 100, 'is_addon' => true]);
        $this->createSale($addon, $role, ['status' => 'paid'], $addonTicket, 1);
        $cases['an add-on'] = $addon;

        // An unpaid sale: money was never taken.
        $unpaid = $this->createEvent($role, ['tickets_enabled' => true]);
        $unpaidTicket = $this->createTicket($unpaid, ['price' => 20, 'quantity' => 100]);
        $this->createSale($unpaid, $role, ['status' => 'unpaid'], $unpaidTicket, 1);
        $cases['an unpaid sale'] = $unpaid;

        // A deleted sale.
        $deleted = $this->createEvent($role, ['tickets_enabled' => true]);
        $deletedTicket = $this->createTicket($deleted, ['price' => 20, 'quantity' => 100]);
        $this->createSale($deleted, $role, ['status' => 'paid', 'is_deleted' => true], $deletedTicket, 1);
        $cases['a deleted sale'] = $deleted;

        // No sale at all.
        $none = $this->createEvent($role, ['tickets_enabled' => true]);
        $this->createTicket($none, ['price' => 20, 'quantity' => 100]);
        $cases['no sale at all'] = $none;

        // A PAID APPOINTMENT BOOKING. Taking money for an appointment is free on every plan, and
        // it creates real Sale and SaleTicket rows against a priced ticket - so without the
        // appointment filter in the backfill it would grandfather its container event into paid
        // ticket selling, which is a different feature the schedule never paid for.
        $typeId = \Illuminate\Support\Facades\DB::table('appointment_types')->insertGetId([
            'role_id' => $role->id,
            'name' => 'Consultation',
            'slug' => 'consultation',
            'duration_minutes' => 30,
            'weekly_windows' => json_encode(['1' => [['start' => '09:00', 'end' => '17:00']]]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $booking = $this->createEvent($role, ['tickets_enabled' => true]);
        $booking->forceFill(['appointment_type_id' => $typeId])->saveQuietly();
        $bookingTicket = $this->createTicket($booking, ['price' => 40, 'quantity' => 100]);
        $this->createSale($booking, $role, ['status' => 'paid'], $bookingTicket, 1);
        $cases['a paid appointment booking'] = $booking;

        $this->runBackfill();

        foreach ($cases as $why => $event) {
            $this->assertFalse($this->stamped($event), "{$why} must not grandfather an event");
            $this->assertFalse(
                $event->fresh()->canSellPaidTickets(),
                "{$why} must not leave the event able to sell paid tickets"
            );
        }
    }
}
