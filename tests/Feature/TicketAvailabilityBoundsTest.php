<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * How many of a ticket a buyer may put in one order comes from two independent bounds: the
 * row's own limit (its stock, capped by its own "Max Per Order") and the shared house every
 * row draws on. The guest form used to derive the second from the first ticket's copy of the
 * first, so a single ticket's per-order cap became the ceiling for every other type.
 *
 * These pin the two staying apart: Ticket::toData()'s quantity is per-row and nothing else,
 * and the house comes from Event::seatsRemainingForSale().
 */
class TicketAvailabilityBoundsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The date toData()/occurrenceSeatsRemaining() key their sold counts off. */
    private function saleDate(Event $event): string
    {
        return $event->saleEventDateFromStartsAt();
    }

    private function ticketingEvent(Role $role, string $mode = 'individual'): Event
    {
        return $this->createEvent($role, [
            'tickets_enabled' => true,
            'total_tickets_mode' => $mode,
        ]);
    }

    /**
     * The reported bug: standard and group tickets have no per-order limit, only the
     * couple's ticket does, yet the picker offered 2 for all three. Equal quantities so
     * combined mode is genuinely active on that pass - that is the branch that broke.
     */
    public function test_one_tickets_max_per_order_does_not_cap_the_other_types(): void
    {
        $cap = config('app.max_tickets_per_order');

        foreach (['individual', 'combined'] as $mode) {
            $owner = $this->createOwner();
            $role = $this->createRole($owner, 'talent');
            $event = $this->ticketingEvent($role, $mode);

            // Priced so the capped one sorts first: Event::tickets() is price DESC, and that
            // first row is what combined mode used to read the shared pool from.
            $couple = $this->createTicket($event, ['type' => "Couple's", 'price' => 90, 'quantity' => 100, 'max_per_order' => 2]);
            $standard = $this->createTicket($event, ['type' => 'Standard', 'price' => 50, 'quantity' => 100]);
            $group = $this->createTicket($event, ['type' => 'Group', 'price' => 40, 'quantity' => 100]);

            $event = $event->fresh();
            $date = $this->saleDate($event);
            foreach ([$couple, $standard, $group] as $ticket) {
                $ticket->setRelation('event', $event);
            }

            // Equal quantities throughout, so combined mode is genuinely active on that pass
            // rather than quietly falling back to the individual path.
            $this->assertTrue($event->hasSameTicketQuantities(), "{$mode}: fixture sanity");

            $this->assertSame(2, $couple->toData($date)['quantity'], "{$mode}: the couple's own cap still applies");
            $this->assertSame($cap, $standard->toData($date)['quantity'], "{$mode}: the standard ticket must not inherit it");
            $this->assertSame($cap, $group->toData($date)['quantity'], "{$mode}: the group ticket must not inherit it");
        }
    }

    /** A capped ticket reports its cap; the seats behind it stay on the event, not the row. */
    public function test_quantity_is_the_rows_own_limit_and_never_the_house(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->ticketingEvent($role);

        $capped = $this->createTicket($event, ['type' => 'Capped', 'price' => 90, 'quantity' => 50, 'max_per_order' => 2]);
        $open = $this->createTicket($event, ['type' => 'Open', 'price' => 50, 'quantity' => 50]);

        $event = $event->fresh();
        $date = $this->saleDate($event);
        $capped->setRelation('event', $event);
        $open->setRelation('event', $event);

        $this->assertSame(2, $capped->toData($date)['quantity'], 'the picker offers the per-order cap');
        $this->assertSame(100, $event->seatsRemainingForSale($date), 'while 100 seats are genuinely left');
        $this->assertSame(
            config('app.max_tickets_per_order'),
            $open->toData($date)['quantity'],
            'and the uncapped row is bounded only by the dropdown default'
        );
    }

    /**
     * The shared house still bounds a ticket that has plenty of its own stock. Combined mode
     * is where that bites: capacity is the one shared number, not the sum, so seats sold as A
     * come out of what B may still sell.
     */
    public function test_house_capacity_still_bounds_a_ticket_with_its_own_stock_left(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->ticketingEvent($role, 'combined');

        $a = $this->createTicket($event, ['type' => 'A', 'price' => 50, 'quantity' => 5]);
        $b = $this->createTicket($event, ['type' => 'B', 'price' => 40, 'quantity' => 5]);

        $event = $event->fresh();
        $date = $this->saleDate($event);
        $a->sold = json_encode([$date => 3]);
        $a->save();

        $event = $event->fresh();
        $a->setRelation('event', $event);
        $b->setRelation('event', $event);

        $this->assertSame(2, $event->seatsRemainingForSale($date), '5 shared seats, 3 sold');
        $this->assertSame(2, $a->toData($date)['quantity']);
        $this->assertSame(
            2,
            $b->toData($date)['quantity'],
            'the shared house binds B too, even though B sold none of its own 5'
        );
    }

    /**
     * Combined mode needs one shared pool, so an unlimited seat ticket has to disqualify it.
     * While it did not, nothing downstream agreed what the event meant: the guest picker had
     * no ceiling, while TicketController capped the whole order at the limited ticket's
     * quantity AND counted the unlimited ticket's sales against it - so once the unlimited
     * one had sold that many, the limited one could never be bought again.
     */
    public function test_an_unlimited_seat_ticket_disqualifies_combined_mode(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->ticketingEvent($role, 'combined');

        $unlimited = $this->createTicket($event, ['type' => 'Unlimited', 'price' => 90, 'quantity' => 0]);
        $limited = $this->createTicket($event, ['type' => 'Limited', 'price' => 50, 'quantity' => 40]);

        $event = $event->fresh();
        $date = $this->saleDate($event);

        $this->assertFalse($event->hasSameTicketQuantities());
        $this->assertNull($event->getSameTicketQuantity());
        $this->assertNull($event->seatsRemainingForSale($date), 'an unlimited type means no house');

        // 50 sold on the unlimited row used to consume the limited row's pool of 40 and lock
        // it out for good. What decides that is the combined branch's own guard condition,
        // TicketController::assertLegTicketsAvailable() - it must not be taken here.
        $unlimited->sold = json_encode([$date => 50]);
        $unlimited->save();

        $event = $event->fresh();
        $limited->setRelation('event', $event);

        $this->assertFalse(
            $event->total_tickets_mode === 'combined' && $event->hasSameTicketQuantities(),
            'the checkout guard must treat this event as individual'
        );
        $this->assertGreaterThan(0, $limited->toData($date)['quantity'], 'the limited ticket is still sellable');
    }

    /**
     * The guest form has to receive the row bound and the house separately, or its quantity
     * picker is back to deriving one from the other.
     */
    public function test_guest_ticket_form_seeds_the_row_bound_and_the_house_separately(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->ticketingEvent($role, 'combined');

        $this->createTicket($event, ['type' => "Couple's", 'price' => 90, 'quantity' => 8, 'max_per_order' => 2]);
        $this->createTicket($event, ['type' => 'Standard', 'price' => 50, 'quantity' => 8]);

        $html = $this->get($this->guestEventUrl($role, $event->fresh()))->assertOk()->getContent();

        $this->assertStringContainsString('"max_per_order":2', $html, "the couple's per-order cap");
        $this->assertStringContainsString('"quantity":2', $html, 'which is all that row may offer');
        $this->assertStringContainsString('sharedSeatsRemaining: 8', $html, 'the house comes from the server');

        // Real inventory is never published: the row bound is always clamped to the cap.
        $this->assertStringNotContainsString('stock_remaining', $html);
    }

    /**
     * The guest form reaches a one-time event with no date, and occurrenceSeatsRemaining()
     * reports "unlimited" for a null date. The form helper resolves the occurrence the way
     * the checkout guard does instead, so the two cannot disagree.
     */
    public function test_seats_remaining_for_sale_falls_back_to_the_event_start_date(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->ticketingEvent($role);

        $this->createTicket($event, ['type' => 'A', 'price' => 50, 'quantity' => 3]);
        $this->createTicket($event, ['type' => 'B', 'price' => 40, 'quantity' => 3]);

        $event = $event->fresh();

        $this->assertNull($event->occurrenceSeatsRemaining(null));
        $this->assertSame(6, $event->seatsRemainingForSale(null));
    }

    /**
     * A falsy cap reads through to "Sold Out" on every ticket of every event - the picker
     * offers nothing, isAllSoldOut flips, and the whole checkout is replaced by the waitlist
     * panel, with no exception and nothing logged. Two ways to get one, both guarded.
     */
    public function test_an_empty_env_value_cannot_produce_a_falsy_per_order_cap(): void
    {
        $original = getenv('MAX_TICKETS_PER_ORDER');

        try {
            // env() returns '' for a key that is present but empty, so a default ARGUMENT
            // never fires and (int) '' would be 0. Re-reading the config file is the only
            // way to exercise the expression itself.
            foreach (['', '0', 'abc', '100000'] as $raw) {
                putenv("MAX_TICKETS_PER_ORDER={$raw}");
                $_ENV['MAX_TICKETS_PER_ORDER'] = $raw;
                $_SERVER['MAX_TICKETS_PER_ORDER'] = $raw;

                $resolved = (require base_path('config/app.php'))['max_tickets_per_order'];

                $this->assertGreaterThanOrEqual(1, $resolved, "MAX_TICKETS_PER_ORDER={$raw} must not disable the picker");
                $this->assertLessThanOrEqual(100, $resolved, "MAX_TICKETS_PER_ORDER={$raw} must not flood the dropdown");
            }
        } finally {
            putenv('MAX_TICKETS_PER_ORDER'.($original === false ? '' : "={$original}"));
            unset($_ENV['MAX_TICKETS_PER_ORDER'], $_SERVER['MAX_TICKETS_PER_ORDER']);
        }
    }

    /** The other way: a config cache built before the key existed resolves it to null. */
    public function test_a_stale_config_cache_cannot_produce_a_falsy_per_order_cap(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->ticketingEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'Open', 'price' => 10, 'quantity' => 0]);
        $ticket->setRelation('event', $event->fresh());

        config(['app.max_tickets_per_order' => null]);

        $this->assertSame(20, $ticket->toData($this->saleDate($event))['quantity']);
    }
}
