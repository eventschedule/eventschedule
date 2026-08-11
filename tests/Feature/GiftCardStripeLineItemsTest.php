<?php

namespace Tests\Feature;

use App\Http\Controllers\TicketController;
use App\Models\Event;
use App\Models\PromoCode;
use App\Models\SaleTicket;
use App\Models\Ticket;
use App\Utils\MoneyUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Unit tests for TicketController::buildStripeLineItems() - the money math that scales Stripe
 * line items for promo discounts + gift-card tender and reconciles the rounding. Guards the
 * "negative unit_amount → Stripe rejects → 500" crash for individual tickets + promo + gift card,
 * and the ~1c over/undercharge from per-unit rounding on a volume-discounted multi-quantity line.
 */
class GiftCardStripeLineItemsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->event = $this->createEvent($role, ['tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
    }

    private function ticket(float $price): Ticket
    {
        $t = $this->createTicket($this->event, ['price' => $price, 'quantity' => 100]);
        $t->setRelation('event', $this->event);

        return $t;
    }

    /** A ticket carrying a fixed per-line volume discount (min_quantity 2). */
    private function fixedVolumeTicket(float $price, float $fixedOff): Ticket
    {
        $t = $this->createTicket($this->event, [
            'price' => $price,
            'quantity' => 100,
            'volume_discount' => ['min_quantity' => 2, 'type' => 'fixed', 'value' => $fixedOff],
        ]);
        $t->setRelation('event', $this->event);

        return $t;
    }

    private function saleTicket(Ticket $ticket, int $qty): SaleTicket
    {
        $st = new SaleTicket(['ticket_id' => $ticket->id, 'quantity' => $qty]);
        $st->setRelation('ticket', $ticket);

        return $st;
    }

    /**
     * The single-leg case: one promo and one discount, both belonging to $this->event.
     *
     * @return array<int, array>
     */
    private function build($saleTickets, ?PromoCode $promo, float $discount, float $giftTotal, float $expectedTotal): array
    {
        return $this->buildLegs($saleTickets, [
            ['promo' => $promo, 'discount' => $discount, 'event_id' => $this->event->id],
        ], $giftTotal, $expectedTotal);
    }

    /**
     * The multi-event case: one Stripe session, one promo context per leg.
     *
     * @param  array<int, array{promo: ?PromoCode, discount: float, event_id: int}>  $promoLegs
     * @return array<int, array>
     */
    private function buildLegs($saleTickets, array $promoLegs, float $giftTotal, float $expectedTotal): array
    {
        $controller = app(TicketController::class);
        $method = new \ReflectionMethod($controller, 'buildStripeLineItems');
        $method->setAccessible(true);

        return $method->invoke($controller, collect($saleTickets), $this->event, $promoLegs, $giftTotal, $expectedTotal);
    }

    private function assertLineItemsValid(array $lineItems, float $expectedTotal): void
    {
        $mult = MoneyUtils::getSmallestUnitMultiplier('USD');
        $sum = 0;
        foreach ($lineItems as $li) {
            $unit = $li['price_data']['unit_amount'];
            $this->assertGreaterThanOrEqual(0, $unit, 'unit_amount must never be negative (Stripe rejects it)');
            $sum += $unit * $li['quantity'];
        }
        $this->assertSame((int) round($expectedTotal * $mult), $sum, 'line-item sum must equal the expected charge exactly');
    }

    public function test_gift_only_reconciles_and_stays_non_negative(): void
    {
        $t = $this->ticket(50);
        // $150 order, $100 gift card, nothing owed but the $50 remainder.
        $items = $this->build([$this->saleTicket($t, 3)], null, 0, 100, 50);
        $this->assertLineItemsValid($items, 50);
    }

    public function test_promo_only_reconciles(): void
    {
        $t = $this->ticket(50);
        $promo = $this->percentagePromo(30); // $45 off $150
        $items = $this->build([$this->saleTicket($t, 3)], $promo, 45, 0, 105);
        $this->assertLineItemsValid($items, 105);
    }

    public function test_promo_plus_gift_on_grouped_tickets_does_not_go_negative(): void
    {
        // The exact crash repro: 3×$50 = $150, promo 30% (group discount $45), gift card $100.
        // Group total payment = 150 - 45 - 100 = $5. Before the fix, passing the primary's
        // per-seat promo share ($15) instead of the group total drove a unit_amount to ~-$18.
        $t = $this->ticket(50);
        $promo = $this->percentagePromo(30);
        $items = $this->build([$this->saleTicket($t, 3)], $promo, 45, 100, 5);
        $this->assertLineItemsValid($items, 5);
    }

    public function test_multi_line_promo_plus_gift(): void
    {
        $a = $this->ticket(40);
        $b = $this->ticket(60);
        // 2×$40 + 1×$60 = $140, promo 25% ($35 off), gift $80. Owed = 140 - 35 - 80 = $25.
        $promo = $this->percentagePromo(25);
        $items = $this->build([$this->saleTicket($a, 2), $this->saleTicket($b, 1)], $promo, 35, 80, 25);
        $this->assertLineItemsValid($items, 25);
    }

    public function test_reconciliation_holds_under_near_full_coverage(): void
    {
        // Many small lines almost fully covered by a gift card down to the $0.50 min charge -
        // every unit_amount must stay >= 0 while still summing to the expected 50 cents.
        $tickets = [];
        for ($i = 0; $i < 8; $i++) {
            $tickets[] = $this->saleTicket($this->ticket(3), 1);
        }
        // $24 order, gift covers $23.50, $0.50 remains.
        $items = $this->build($tickets, null, 0, 23.5, 0.5);
        $this->assertLineItemsValid($items, 0.5);
    }

    public function test_high_quantity_line_heavily_gift_covered_reconciles_exactly(): void
    {
        // The regression the single-line clamp broke: ONE line of 14 units @ $10 = $140, gift card
        // covers all but the $0.50 min charge. Per-unit rounds 3.57c -> 4c, so the naive sum is
        // 14x4 = 56c but expected is 50c: a diff of -6 that a single unit (4c) cannot absorb.
        // The distribution must still land the sum on exactly 50c with no negative unit.
        $t = $this->ticket(10);
        $items = $this->build([$this->saleTicket($t, 14)], null, 0, 139.5, 0.5);
        $this->assertLineItemsValid($items, 0.5);
    }

    public function test_high_quantity_promo_plus_gift_reconciles_exactly(): void
    {
        // 20 units @ $12 = $240, promo 15% ($36 off), gift card down to a $0.50 remainder.
        $t = $this->ticket(12);
        $promo = $this->percentagePromo(15);
        $items = $this->build([$this->saleTicket($t, 20)], $promo, 36, 203.5, 0.5);
        $this->assertLineItemsValid($items, 0.5);
    }

    public function test_volume_discount_only_order_reconciles(): void
    {
        // Volume discount with NO promo and NO gift card - the pre-existing overcharge the widened
        // reconciliation guard now covers. 3×$10 = $30, fixed $1 volume discount → line $29, unit
        // $9.6667 rounds to 967c, so the naive sum is 3×967 = 2901c ($29.01) vs the $29.00 charged
        // in payment_amount. The reconciliation must pull the sum back to exactly 2900c.
        $t = $this->fixedVolumeTicket(10, 1);
        $items = $this->build([$this->saleTicket($t, 3)], null, 0, 0, 29);
        $this->assertLineItemsValid($items, 29);
    }

    public function test_volume_discount_only_spreads_diff_across_units(): void
    {
        // A volume-only diff larger than 1 cent, in the undercharge direction. 7×$25 = $175, fixed
        // $2 volume discount → line $173, unit $24.7143 rounds to 2471c, so the naive sum is
        // 7×2471 = 17297c vs the $173.00 owed (17300c): a +3 diff that must spread across 3 units.
        $t = $this->fixedVolumeTicket(25, 2);
        $items = $this->build([$this->saleTicket($t, 7)], null, 0, 0, 173);
        $this->assertLineItemsValid($items, 173);
    }

    /**
     * A cart is ONE Stripe session spanning several events, and each leg carries its own promo
     * code and its own discount_amount. Resolving the code from the anchor leg while summing the
     * discount across the order meant a code on any other leg was never applied: the session was
     * built at full price against a net expected total, the per-unit rounding reconciliation
     * could not close a gap that large, and the webhook parked the paid sale in amount_mismatch.
     */
    public function test_a_promo_on_a_non_anchor_leg_is_still_applied(): void
    {
        $other = $this->createEvent($this->event->roles->first(), [
            'tickets_enabled' => true,
            'ticket_currency_code' => 'USD',
        ]);

        // Leg A anchors the order (lowest event id) and carries no code; leg B has 10% off.
        $legA = $this->ticket(10);
        $legB = $this->createTicket($other, ['price' => 50, 'quantity' => 100]);
        $legB->setRelation('event', $other);

        $promo = $this->percentagePromo(10);
        $promo->event_id = $other->id;

        $items = $this->buildLegs(
            [$this->saleTicket($legA, 1), $this->saleTicket($legB, 1)],
            [
                ['promo' => null, 'discount' => 0.0, 'event_id' => $this->event->id],
                ['promo' => $promo, 'discount' => 5.0, 'event_id' => $other->id],
            ],
            0,
            55 // $10 + ($50 - $5)
        );

        $this->assertLineItemsValid($items, 55);
    }

    /**
     * A cart holds ONE ENTRY PER EVENT AND DATE, so two dates of a recurring event are two legs
     * of one order sharing a single ticket_id - and stripeCheckout aggregates the line items by
     * ticket_id. Resolving a ratio per leg and keying it by ticket therefore had the second leg
     * overwrite the first, spending only half the order's discount: the session was built $5 over
     * a $90 order, the per-unit reconciliation could not close it, and the paid sale landed in
     * amount_mismatch. The pre-rework code got this case right.
     */
    public function test_two_dates_of_one_event_spend_the_whole_orders_discount(): void
    {
        $ticket = $this->ticket(50);
        $promo = $this->percentagePromo(10);

        // One leg per date, each recording its own $5 discount against the same event.
        $items = $this->buildLegs(
            [$this->saleTicket($ticket, 2)],
            [
                ['promo' => $promo, 'discount' => 5.0, 'event_id' => $this->event->id],
                ['promo' => $promo, 'discount' => 5.0, 'event_id' => $this->event->id],
            ],
            0,
            90 // 2 x $50 less $10 of discount
        );

        $this->assertLineItemsValid($items, 90);
    }

    /** The mirror: one leg's discount must not be spent against another leg's tickets. */
    public function test_a_promo_is_not_applied_to_another_legs_tickets(): void
    {
        $other = $this->createEvent($this->event->roles->first(), [
            'tickets_enabled' => true,
            'ticket_currency_code' => 'USD',
        ]);

        $legA = $this->ticket(100);
        $legB = $this->createTicket($other, ['price' => 100, 'quantity' => 100]);
        $legB->setRelation('event', $other);

        $promo = $this->percentagePromo(50);

        // 50% off leg A only. Leg B must stay at its full $100.
        $items = $this->buildLegs(
            [$this->saleTicket($legA, 1), $this->saleTicket($legB, 1)],
            [
                ['promo' => $promo, 'discount' => 50.0, 'event_id' => $this->event->id],
                ['promo' => null, 'discount' => 0.0, 'event_id' => $other->id],
            ],
            0,
            150
        );

        $this->assertLineItemsValid($items, 150);

        $units = array_map(fn ($i) => $i['price_data']['unit_amount'], $items);
        sort($units);
        $this->assertSame([5000, 10000], $units, 'leg A halves, leg B keeps its full price');
    }

    /**
     * A leg whose recorded discount exceeds what its code is eligible for used to drive the ratio
     * negative, and a negative unit_amount is rejected by Stripe outright - a 500 with the sale
     * rows, seat holds and promo times_used already committed.
     */
    public function test_an_oversized_discount_never_produces_a_negative_unit_amount(): void
    {
        $t = $this->ticket(10);
        $promo = $this->percentagePromo(100);

        $items = $this->buildLegs(
            [$this->saleTicket($t, 1)],
            [['promo' => $promo, 'discount' => 60.0, 'event_id' => $this->event->id]],
            0,
            0
        );

        foreach ($items as $item) {
            $this->assertGreaterThanOrEqual(0, $item['price_data']['unit_amount']);
        }
    }

    private function percentagePromo(float $value): PromoCode
    {
        $promo = new PromoCode([
            'event_id' => $this->event->id,
            'code' => 'TEST'.$value,
            'type' => 'percentage',
            'value' => $value,
            'is_active' => true,
        ]);
        $promo->setRelation('event', $this->event);
        $promo->id = 999; // appliesToTicket only reads ticket_ids (null → all), id is unused here

        return $promo;
    }
}
