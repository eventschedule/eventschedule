<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\GiftCard;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class GiftCardRedemptionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function giftCard(Role $role, array $attrs = []): GiftCard
    {
        $card = new GiftCard;
        $card->role_id = $role->id;
        $card->code = $attrs['code'] ?? GiftCard::generateCode();
        $card->secret = strtolower(Str::random(32));
        $card->amount = $attrs['amount'] ?? 50;
        $card->remaining_amount = $attrs['remaining_amount'] ?? ($attrs['amount'] ?? 50);
        $card->currency_code = $attrs['currency_code'] ?? 'USD';
        $card->status = $attrs['status'] ?? 'active';
        $card->payment_method = 'cash';
        $card->purchaser_name = 'Alice';
        $card->purchaser_email = 'alice@test.dev';
        $card->recipient_name = 'Bob';
        $card->recipient_email = 'bob@test.dev';
        $card->valid_days = $attrs['valid_days'] ?? null;
        $card->activated_at = $card->status === 'active' ? now() : null;
        $card->expires_at = $attrs['expires_at'] ?? null;
        $card->save();

        return $card->fresh();
    }

    private function ticketedEvent(Role $role, array $overrides = []): Event
    {
        return $this->createEvent($role, array_merge([
            'tickets_enabled' => true,
            'ticket_currency_code' => 'USD',
            'payment_method' => 'cash',
            'creator_role_id' => $role->id,
        ], $overrides));
    }

    private function checkout(Role $role, Event $event, array $data): void
    {
        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), array_merge([
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Redeemer',
            'email' => 'redeemer@test.dev',
        ], $data));
    }

    public function test_validate_endpoint_returns_balance(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);
        $this->createTicket($event, ['price' => 30]);
        $card = $this->giftCard($role, ['amount' => 50, 'remaining_amount' => 40]);

        $this->postJson(route('gift_card.validate', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'code' => $card->code,
        ])->assertOk()->assertJson(['valid' => true, 'balance' => 40, 'currency' => 'USD']);
    }

    public function test_validate_rejects_wrong_currency(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role, ['ticket_currency_code' => 'EUR']);
        $card = $this->giftCard($role, ['currency_code' => 'USD']);

        $this->postJson(route('gift_card.validate', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'code' => $card->code,
        ])->assertOk()->assertJson(['valid' => false]);
    }

    public function test_validate_rejects_unknown_code(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);

        $this->postJson(route('gift_card.validate', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'code' => 'NOTACODE1234',
        ])->assertOk()->assertJson(['valid' => false]);
    }

    public function test_validate_rejects_expired_and_depleted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);

        $expired = $this->giftCard($role, ['expires_at' => now()->subDay()]);
        $this->postJson(route('gift_card.validate', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'code' => $expired->code,
        ])->assertOk()->assertJson(['valid' => false]);

        $depleted = $this->giftCard($role, ['amount' => 50, 'remaining_amount' => 0]);
        $this->postJson(route('gift_card.validate', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'code' => $depleted->code,
        ])->assertOk()->assertJson(['valid' => false]);
    }

    public function test_partial_redemption_leaves_remainder_to_pay(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);
        $ticket = $this->createTicket($event, ['price' => 30, 'quantity' => 50]);
        $card = $this->giftCard($role, ['amount' => 20, 'remaining_amount' => 20]);

        $this->checkout($role, $event, [
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
            'gift_card_code' => $card->code,
        ]);

        $sale = Sale::where('email', 'redeemer@test.dev')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(20, (float) $sale->gift_card_amount);
        $this->assertEquals(10, (float) $sale->payment_amount); // 30 - 20 still owed
        $this->assertSame($card->id, $sale->gift_card_id);
        $this->assertEquals(0, (float) $card->fresh()->remaining_amount);
    }

    public function test_full_coverage_marks_sale_paid(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);
        $ticket = $this->createTicket($event, ['price' => 30, 'quantity' => 50]);
        $card = $this->giftCard($role, ['amount' => 50, 'remaining_amount' => 50]);

        $this->checkout($role, $event, [
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
            'gift_card_code' => $card->code,
        ]);

        $sale = Sale::where('email', 'redeemer@test.dev')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(0, (float) $sale->payment_amount);
        $this->assertSame('paid', $sale->status); // zero-total path
        $this->assertEquals(30, (float) $sale->gift_card_amount);
        $this->assertEquals(20, (float) $card->fresh()->remaining_amount);
    }

    public function test_invalid_code_aborts_checkout_with_no_sale(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);
        $ticket = $this->createTicket($event, ['price' => 30, 'quantity' => 50]);
        $this->giftCard($role, ['amount' => 50, 'remaining_amount' => 0]); // depleted

        $depletedCode = GiftCard::where('role_id', $role->id)->first()->code;

        $this->checkout($role, $event, [
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
            'gift_card_code' => $depletedCode,
        ]);

        // A gift card is a payment instrument: an unusable code aborts checkout entirely.
        $this->assertSame(0, Sale::where('email', 'redeemer@test.dev')->count());
    }

    public function test_sequential_redemption_capped_at_remaining_balance(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 50]);
        $card = $this->giftCard($role, ['amount' => 30, 'remaining_amount' => 30]);

        // First order: 20 applied, 10 left.
        $this->checkout($role, $event, [
            'name' => 'First', 'email' => 'first@test.dev',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
            'gift_card_code' => $card->code,
        ]);
        $this->assertEquals(10, (float) $card->fresh()->remaining_amount);

        // Second order: only 10 left, so 10 applied and 10 still owed.
        $this->checkout($role, $event, [
            'name' => 'Second', 'email' => 'second@test.dev',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
            'gift_card_code' => $card->code,
        ]);

        $second = Sale::where('email', 'second@test.dev')->first();
        $this->assertEquals(10, (float) $second->gift_card_amount);
        $this->assertEquals(10, (float) $second->payment_amount);
        $this->assertEquals(0, (float) $card->fresh()->remaining_amount);
    }

    public function test_cancelling_sale_restores_balance(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $card = $this->giftCard($role, ['amount' => 50, 'remaining_amount' => 30]); // 20 already used

        $sale = $this->createSale($event, $role, [
            'status' => 'paid',
            'payment_method' => 'cash',
            'payment_amount' => 0,
            'gift_card_id' => $card->id,
            'gift_card_amount' => 20,
        ], $ticket, 1);

        $sale->status = 'cancelled';
        $sale->save();

        $this->assertEquals(50, (float) $card->fresh()->remaining_amount);
    }

    public function test_no_restore_to_non_active_card(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $card = $this->giftCard($role, ['amount' => 50, 'remaining_amount' => 30, 'status' => 'refunded']);

        $sale = $this->createSale($event, $role, [
            'status' => 'paid',
            'payment_method' => 'cash',
            'payment_amount' => 0,
            'gift_card_id' => $card->id,
            'gift_card_amount' => 20,
        ], $ticket, 1);

        $sale->status = 'cancelled';
        $sale->save();

        // A refunded card must not be credited.
        $this->assertEquals(30, (float) $card->fresh()->remaining_amount);
    }

    public function test_restore_capped_at_face_value(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $card = $this->giftCard($role, ['amount' => 50, 'remaining_amount' => 50]); // already full

        $sale = $this->createSale($event, $role, [
            'status' => 'paid',
            'payment_method' => 'cash',
            'payment_amount' => 0,
            'gift_card_id' => $card->id,
            'gift_card_amount' => 20,
        ], $ticket, 1);

        $sale->status = 'refunded';
        $sale->save();

        $this->assertEquals(50, (float) $card->fresh()->remaining_amount);
    }

    public function test_curator_guard_blocks_cross_owner_redemption(): void
    {
        $ownerA = $this->createOwner();
        $roleA = $this->createRole($ownerA);
        $event = $this->ticketedEvent($roleA);
        $ticket = $this->createTicket($event, ['price' => 30, 'quantity' => 50]);

        // A different owner's curator schedule is co-listed on the event and sold a card.
        $ownerB = $this->createOwner();
        $roleB = $this->createRole($ownerB, 'curator');
        $event->roles()->attach($roleB->id, ['is_accepted' => true]);
        $card = $this->giftCard($roleB, ['amount' => 50, 'remaining_amount' => 50]);

        $this->checkout($roleA, $event, [
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
            'gift_card_code' => $card->code,
        ]);

        // Redemption would move money from the event owner to the card seller - blocked.
        $this->assertSame(0, Sale::where('email', 'redeemer@test.dev')->count());
        $this->assertEquals(50, (float) $card->fresh()->remaining_amount);
    }

    public function test_release_tickets_restores_balance_when_gift_lands_only_on_guest_rows(): void
    {
        // Individual-tickets order where the primary seat's net is 0 (free primary ticket) so the
        // gift card lands entirely on a guest row -> the primary carries gift_card_id = NULL.
        // ReleaseTickets must still find and expire the abandoned group (48h) and restore balance.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        // Non-cash so the 48h auto-release applies (cash orders are settled manually - see the
        // dedicated cash test below).
        $event = $this->ticketedEvent($role, ['individual_tickets' => true, 'payment_method' => 'payment_url']);
        $free = $this->createTicket($event, ['price' => 0, 'quantity' => 50]);
        $paid = $this->createTicket($event, ['price' => 30, 'quantity' => 50]);
        $card = $this->giftCard($role, ['amount' => 40, 'remaining_amount' => 40]);

        // Free primary seat + two $30 guests = $60. A $40 card covers the guests partially, so the
        // group still owes $20 (stays unpaid) while the primary's net is 0 -> primary gift_card_id null.
        $this->checkout($role, $event, [
            'name' => 'Primary', 'email' => 'primary@test.dev',
            'tickets' => [UrlUtils::encodeId($free->id) => 1, UrlUtils::encodeId($paid->id) => 2],
            'guests' => [
                ['name' => 'Primary', 'email' => 'primary@test.dev'],
                ['name' => 'Guest Two', 'email' => 'guest2@test.dev'],
                ['name' => 'Guest Three', 'email' => 'guest3@test.dev'],
            ],
            'gift_card_code' => $card->code,
        ]);

        $primary = Sale::where('email', 'primary@test.dev')->first();
        // Guests carry the gift share; the primary (free seat) has no gift_card_id.
        $this->assertNull($primary->gift_card_id);
        $this->assertSame('unpaid', $primary->status);
        $this->assertEquals(0, (float) $card->fresh()->remaining_amount);

        // Backdate the whole group past the 48h window and run the cleanup.
        Sale::where('id', $primary->id)->orWhere('group_id', $primary->id)
            ->update(['created_at' => now()->subHours(72)]);

        $this->artisan('app:release-tickets')->assertExitCode(0);

        $this->assertSame('expired', $primary->fresh()->status);
        $this->assertEquals(40, (float) $card->fresh()->remaining_amount, 'balance must be restored, not stranded');
    }

    public function test_release_tickets_restores_exact_face_value_when_primary_and_guest_both_carry_shares(): void
    {
        // Both the primary seat and a guest carry a gift share. Expiring the abandoned group must
        // restore each share exactly once, summing back to the exact face value. Also asserts the
        // second cron run is idempotent (no re-restore).
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role, ['individual_tickets' => true, 'payment_method' => 'payment_url']);
        $ticket = $this->createTicket($event, ['price' => 30, 'quantity' => 50]);
        $card = $this->giftCard($role, ['amount' => 40, 'remaining_amount' => 40]);

        // 3 x $30 = $90, $40 card: primary seat gets 30, guest1 gets 10, guest2 gets 0. Owed $50.
        $this->checkout($role, $event, [
            'name' => 'Primary', 'email' => 'primary@test.dev',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 3],
            'guests' => [
                ['name' => 'Primary', 'email' => 'primary@test.dev'],
                ['name' => 'Guest Two', 'email' => 'guest2@test.dev'],
                ['name' => 'Guest Three', 'email' => 'guest3@test.dev'],
            ],
            'gift_card_code' => $card->code,
        ]);

        $primary = Sale::where('email', 'primary@test.dev')->first();
        $this->assertGreaterThan(0, (float) $primary->gift_card_amount); // primary carries a share
        $this->assertEquals(0, (float) $card->fresh()->remaining_amount);

        Sale::where('id', $primary->id)->orWhere('group_id', $primary->id)
            ->update(['created_at' => now()->subHours(72)]);

        $this->artisan('app:release-tickets')->assertExitCode(0);
        $this->assertEquals(40, (float) $card->fresh()->remaining_amount, 'restore must sum to exact face value');

        // Idempotent: a second run finds nothing unpaid to expire and does not re-restore.
        $this->artisan('app:release-tickets')->assertExitCode(0);
        $this->assertEquals(40, (float) $card->fresh()->remaining_amount);
    }

    public function test_release_tickets_does_not_expire_cash_gift_redemptions(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role); // payment_method cash
        $ticket = $this->createTicket($event, ['price' => 30, 'quantity' => 50]);
        $card = $this->giftCard($role, ['amount' => 20, 'remaining_amount' => 20]);

        // Partial cash redemption: $20 off a $30 order, $10 owed in person -> stays unpaid.
        $this->checkout($role, $event, [
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
            'gift_card_code' => $card->code,
        ]);
        $sale = Sale::where('email', 'redeemer@test.dev')->first();
        $this->assertSame('unpaid', $sale->status);

        Sale::where('id', $sale->id)->update(['created_at' => now()->subHours(72)]);
        $this->artisan('app:release-tickets')->assertExitCode(0);

        // Cash sales are never auto-expired; the owner settles them manually.
        $this->assertSame('unpaid', $sale->fresh()->status);
        $this->assertEquals(0, (float) $card->fresh()->remaining_amount);
    }

    public function test_release_tickets_skips_a_sale_paid_after_the_query(): void
    {
        // Simulates the webhook-paid-before-cron race: a stale unpaid gift sale that a webhook
        // marked paid must NOT be flipped to expired (which would double-credit the card).
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role, ['payment_method' => 'stripe']);
        $ticket = $this->createTicket($event, ['price' => 30, 'quantity' => 50]);
        $card = $this->giftCard($role, ['amount' => 20, 'remaining_amount' => 20]);

        $this->checkout($role, $event, [
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
            'gift_card_code' => $card->code,
        ]);
        $sale = Sale::where('email', 'redeemer@test.dev')->first();
        // Webhook settled it, and it's older than 48h.
        Sale::where('id', $sale->id)->update(['status' => 'paid', 'created_at' => now()->subHours(72)]);

        $this->artisan('app:release-tickets')->assertExitCode(0);

        $this->assertSame('paid', $sale->fresh()->status);
        $this->assertEquals(0, (float) $card->fresh()->remaining_amount, 'a paid sale must not restore the balance');
    }

    public function test_admin_cancel_of_gift_paid_sale_restores_balance_exactly_once(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $card = $this->giftCard($role, ['amount' => 50, 'remaining_amount' => 30]); // 20 redeemed

        $sale = $this->createSale($event, $role, [
            'status' => 'paid', 'payment_method' => 'cash', 'payment_amount' => 0,
            'gift_card_id' => $card->id, 'gift_card_amount' => 20,
        ], $ticket, 1);

        $this->actingAs($owner)->withHeader('X-Requested-With', 'XMLHttpRequest')->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), [
            'action' => 'cancel',
        ])->assertOk();

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertEquals(50, (float) $card->fresh()->remaining_amount);

        // A second cancel on the now-terminal sale must be a no-op (the lock+recheck aborts it) -
        // no double restore.
        $this->actingAs($owner)->withHeader('X-Requested-With', 'XMLHttpRequest')->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), [
            'action' => 'cancel',
        ]);
        $this->assertEquals(50, (float) $card->fresh()->remaining_amount);
    }

    public function test_admin_refund_of_gift_paid_sale_restores_balance(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $card = $this->giftCard($role, ['amount' => 50, 'remaining_amount' => 25]);

        $sale = $this->createSale($event, $role, [
            'status' => 'paid', 'payment_method' => 'stripe', 'payment_amount' => 5,
            'gift_card_id' => $card->id, 'gift_card_amount' => 25,
        ], $ticket, 1);

        $this->actingAs($owner)->withHeader('X-Requested-With', 'XMLHttpRequest')->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), [
            'action' => 'refund',
        ])->assertOk();

        $this->assertSame('refunded', $sale->fresh()->status);
        $this->assertEquals(50, (float) $card->fresh()->remaining_amount);
    }

    public function test_admin_actions_still_work_on_a_regular_sale(): void
    {
        // Guards the handleAction lock refactor for the common (non-gift) path.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role);
        $ticket = $this->createTicket($event, ['price' => 10, 'quantity' => 50]);

        $unpaid = $this->createSale($event, $role, ['status' => 'unpaid', 'payment_method' => 'cash', 'payment_amount' => 10], $ticket, 1);
        $this->actingAs($owner)->withHeader('X-Requested-With', 'XMLHttpRequest')->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($unpaid->id)]), ['action' => 'mark_paid'])->assertOk();
        $this->assertSame('paid', $unpaid->fresh()->status);

        $this->actingAs($owner)->withHeader('X-Requested-With', 'XMLHttpRequest')->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($unpaid->id)]), ['action' => 'refund'])->assertOk();
        $this->assertSame('refunded', $unpaid->fresh()->status);

        $paid = $this->createSale($event, $role, ['status' => 'paid', 'payment_method' => 'cash', 'payment_amount' => 10], $ticket, 1);
        $this->actingAs($owner)->withHeader('X-Requested-With', 'XMLHttpRequest')->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($paid->id)]), ['action' => 'cancel'])->assertOk();
        $this->assertSame('cancelled', $paid->fresh()->status);

        $del = $this->createSale($event, $role, ['status' => 'paid', 'payment_method' => 'cash', 'payment_amount' => 10], $ticket, 1);
        $this->actingAs($owner)->withHeader('X-Requested-With', 'XMLHttpRequest')->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($del->id)]), ['action' => 'delete'])->assertOk();
        $this->assertTrue((bool) $del->fresh()->is_deleted);
    }

    public function test_grouped_primary_group_totals_equal_sum_of_per_seat_shares(): void
    {
        // Pins the round-1 stripeCheckout discount fix: for a grouped (individual-tickets) primary,
        // the value fed to the Stripe line-item builder must be the GROUP total (groupTotalDiscount /
        // groupTotalGiftCard), which must equal the sum of the per-seat shares across the group.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role, ['individual_tickets' => true]);
        $ticket = $this->createTicket($event, ['price' => 50, 'quantity' => 50]);
        $card = $this->giftCard($role, ['amount' => 60, 'remaining_amount' => 60]);

        // 3 seats x $50 = $150, $60 gift card. (No promo here - promo needs its own event setup.)
        $this->checkout($role, $event, [
            'name' => 'Primary', 'email' => 'primary@test.dev',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 3],
            'guests' => [
                ['name' => 'Primary', 'email' => 'primary@test.dev'],
                ['name' => 'Guest Two', 'email' => 'guest2@test.dev'],
                ['name' => 'Guest Three', 'email' => 'guest3@test.dev'],
            ],
            'gift_card_code' => $card->code,
        ]);

        $primary = Sale::where('email', 'primary@test.dev')->first();
        $sumOfShares = (float) Sale::where('id', $primary->id)->orWhere('group_id', $primary->id)->sum('gift_card_amount');

        $this->assertEquals(60, $sumOfShares, 'per-seat gift shares must sum to the applied amount');
        $this->assertEquals($sumOfShares, $primary->groupTotalGiftCard(), 'groupTotalGiftCard must equal the per-seat sum');
        // groupTotalPayment = 150 - 60 = 90 across the group.
        $this->assertEquals(90, $primary->groupTotalPayment());
    }

    public function test_individual_tickets_primary_zeroed_is_not_auto_paid(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->ticketedEvent($role, ['individual_tickets' => true]);
        $ticket = $this->createTicket($event, ['price' => 30, 'quantity' => 50]);
        // Card covers the primary seat fully but not the guest seat.
        $card = $this->giftCard($role, ['amount' => 40, 'remaining_amount' => 40]);

        $this->checkout($role, $event, [
            'name' => 'Primary', 'email' => 'primary@test.dev',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 2],
            'guests' => [
                ['name' => 'Primary', 'email' => 'primary@test.dev'],
                ['name' => 'Guest Two', 'email' => 'guest2@test.dev'],
            ],
            'gift_card_code' => $card->code,
        ]);

        $primary = Sale::where('email', 'primary@test.dev')->first();
        $guest = Sale::where('email', 'guest2@test.dev')->first();
        $this->assertNotNull($primary);
        $this->assertNotNull($guest);

        // The primary's own amount is 0, but the group still owes money - it must NOT be auto-paid
        // (regression guard for the group-total zero-check fix).
        $this->assertEquals(0, (float) $primary->payment_amount);
        $this->assertNotSame('paid', $primary->status);
        $this->assertNotSame('paid', $guest->status);
        // Greedy allocation: 30 to the primary seat, 10 to the guest; card fully drained.
        $this->assertEquals(30, (float) $primary->gift_card_amount);
        $this->assertEquals(10, (float) $guest->gift_card_amount);
        $this->assertEquals(0, (float) $card->fresh()->remaining_amount);
    }
}
