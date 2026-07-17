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

    /** @return array<int, array> */
    private function build($saleTickets, ?PromoCode $promo, float $discount, float $giftTotal, float $expectedTotal): array
    {
        $controller = app(TicketController::class);
        $method = new \ReflectionMethod($controller, 'buildStripeLineItems');
        $method->setAccessible(true);

        return $method->invoke($controller, collect($saleTickets), $this->event, $promo, $discount, $giftTotal, $expectedTotal);
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
