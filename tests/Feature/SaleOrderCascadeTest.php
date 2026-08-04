<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\TicketWaitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * sales.order_id - the rows a buyer paid for in one checkout that spanned several events.
 *
 * Separate from group_id, which groups one row per named guest within a single event. A leg can be
 * both, so the fixture here deliberately builds an order whose first leg also uses individual
 * tickets: leg A + its guest + leg B, all sharing order_id, with the guest also carrying group_id.
 */
class SaleOrderCascadeTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array{0: Sale, 1: Sale, 2: Sale, 3: \App\Models\Event, 4: \App\Models\Event} */
    private function createTwoLegOrder(string $status = 'unpaid'): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $eventA = $this->createEvent($role, ['tickets_enabled' => true]);
        $eventB = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticketA = $this->createTicket($eventA, ['price' => 30, 'quantity' => 50]);
        $ticketB = $this->createTicket($eventB, ['price' => 20, 'quantity' => 50]);

        $legA = $this->createSale($eventA, $role, [
            'name' => 'Buyer', 'email' => 'buyer@example.com',
            'payment_amount' => 30, 'payment_method' => 'stripe', 'status' => $status,
        ], $ticketA);

        $guestA = $this->createSale($eventA, $role, [
            'name' => 'Guest', 'email' => 'guest@example.com',
            'payment_amount' => 30, 'payment_method' => 'stripe', 'status' => $status,
        ], $ticketA);

        $legB = $this->createSale($eventB, $role, [
            'name' => 'Buyer', 'email' => 'buyer@example.com',
            'payment_amount' => 20, 'payment_method' => 'stripe', 'status' => $status,
        ], $ticketB);

        // Leg A anchors the order and its own guest group; every row carries order_id.
        $legA->group_id = $legA->id;
        $legA->order_id = $legA->id;
        $legA->saveQuietly();

        $guestA->group_id = $legA->id;
        $guestA->order_id = $legA->id;
        $guestA->saveQuietly();

        $legB->order_id = $legA->id;
        $legB->saveQuietly();

        return [$legA->fresh(), $guestA->fresh(), $legB->fresh(), $eventA, $eventB];
    }

    public function test_paying_the_order_primary_cascades_to_every_leg_and_guest(): void
    {
        [$legA, $guestA, $legB] = $this->createTwoLegOrder();

        $legA->status = 'paid';
        $legA->save();

        $this->assertSame('paid', $guestA->fresh()->status, 'guest row of leg A');
        $this->assertSame('paid', $legB->fresh()->status, 'leg B, a different event');
        $this->assertNotNull($legB->fresh()->paid_at, 'the raw cascade must stamp paid_at itself');
    }

    public function test_paying_the_order_clears_the_waitlist_on_every_legs_own_event(): void
    {
        [$legA, $guestA, $legB, $eventA, $eventB] = $this->createTwoLegOrder();

        // One waitlist entry per leg, on that leg's OWN event and date. The pre-order code matched
        // every sibling against the PRIMARY's event and date, so leg B's would never be cleared.
        foreach ([[$eventA, $guestA], [$eventB, $legB]] as [$event, $sale]) {
            TicketWaitlist::create([
                'event_id' => $event->id,
                'event_date' => $sale->event_date,
                'name' => $sale->name,
                'email' => $sale->email,
                'subdomain' => $sale->subdomain,
                'status' => 'waiting',
            ]);
        }

        $legA->status = 'paid';
        $legA->save();

        $this->assertSame('purchased', TicketWaitlist::where('event_id', $eventA->id)->first()->status);
        $this->assertSame('purchased', TicketWaitlist::where('event_id', $eventB->id)->first()->status,
            "leg B's waitlist entry must be matched against leg B's own event");
    }

    public function test_cancelling_the_order_primary_cascades_to_every_leg_and_guest(): void
    {
        [$legA, $guestA, $legB] = $this->createTwoLegOrder('paid');

        $legA->status = 'cancelled';
        $legA->save();

        $this->assertSame('cancelled', $guestA->fresh()->status);
        $this->assertSame('cancelled', $legB->fresh()->status);
    }

    public function test_cancelling_one_leg_leaves_the_rest_of_the_order_alone(): void
    {
        [$legA, $guestA, $legB] = $this->createTwoLegOrder('paid');

        // Leg B is in the order but is not its primary, so it drives no cascade.
        $legB->status = 'cancelled';
        $legB->save();

        $this->assertSame('cancelled', $legB->fresh()->status);
        $this->assertSame('paid', $legA->fresh()->status);
        $this->assertSame('paid', $guestA->fresh()->status);
    }

    public function test_an_order_expires_as_a_unit_when_the_shortest_leg_window_elapses(): void
    {
        [$legA, $guestA, $legB, , $eventB] = $this->createTwoLegOrder();

        // Only leg B's event opts into expiry. Judged leg by leg, B would expire and give its
        // seats back while A stays unpaid forever - an order that can never complete, and seats
        // resold while the buyer's payment session is still open.
        $eventB->expire_unpaid_tickets = 1;
        $eventB->save();

        Sale::whereIn('id', [$legA->id, $guestA->id, $legB->id])
            ->update(['created_at' => now()->subHours(3)]);

        $this->artisan('app:release-tickets')->assertExitCode(0);

        $this->assertSame('expired', $legB->fresh()->status, 'the leg whose window elapsed');
        $this->assertSame('expired', $legA->fresh()->status, 'the order primary must go with it');
        $this->assertSame('expired', $guestA->fresh()->status, 'and so must its guest row');
    }

    public function test_order_totals_span_legs_while_leg_totals_do_not(): void
    {
        [$legA] = $this->createTwoLegOrder();

        // Leg A + its guest = 60; plus leg B = 80.
        $this->assertSame(60.0, $legA->legTotalPayment(), 'legTotal stays within the event');
        $this->assertSame(80.0, $legA->orderTotalPayment(), 'orderTotal spans the whole checkout');
    }

    public function test_order_totals_fall_back_to_the_leg_when_there_is_no_order(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 25, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, ['payment_amount' => 25, 'status' => 'paid'], $ticket);

        // Every sale written before this feature has order_id null and must be unaffected.
        $this->assertSame(25.0, $sale->orderTotalPayment());
        $this->assertSame(25.0, $sale->legTotalPayment());
    }
}
