<?php

namespace Tests\Feature;

use App\Mail\TicketPurchase;
use App\Models\Event;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Services\Payments\Gateways\StripeGateway;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A donation at checkout (Event::offersDonations(), TicketController::checkout()/priceSaleLeg()).
 *
 * The money must come out exactly as the buyer saw it: inside payment_amount (so every gateway
 * that charges the total, settlement and refunds need nothing of their own), never discounted by a
 * promo code, refused rather than dropped when it cannot be taken.
 */
class DonationCheckoutTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    private array $paypalOrders = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->paypalOrders = [];

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/v1/oauth2/token')) {
                return Http::response(['access_token' => 'fake-token', 'expires_in' => 32400]);
            }
            if (str_contains($request->url(), '/v2/checkout/orders')) {
                $this->paypalOrders[] = (array) $request->data();

                return Http::response(['id' => 'ORDER123', 'status' => 'CREATED', 'links' => [['rel' => 'payer-action', 'href' => 'https://paypal.test/approve']]]);
            }

            return Http::response([], 404);
        });
    }

    /** @return array{0: Role, 1: Event} */
    private function donationEvent(array $attrs = []): array
    {
        $owner = $this->createOwner();
        $owner->forceFill(['paypal_client_id' => 'client', 'paypal_client_secret' => 'secret', 'paypal_sandbox' => true])->save();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, array_merge([
            'tickets_enabled' => true,
            'payment_method' => 'paypal',
            'ticket_currency_code' => 'USD',
            'donations_enabled' => true,
            'creator_role_id' => $role->id,
        ], $attrs));

        return [$role, $event->fresh()];
    }

    private function checkout($role, $event, $ticket, int $qty = 1, array $extra = [])
    {
        return $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), array_merge([
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Dana Buyer',
            'email' => 'dana@guests.org',
            'tickets' => [UrlUtils::encodeId($ticket->id) => $qty],
        ], $extra));
    }

    private function paypalTotal(): ?string
    {
        return $this->paypalOrders[0]['purchase_units'][0]['amount']['value'] ?? null;
    }

    public function test_a_free_ticket_with_a_donation_is_pay_what_you_want(): void
    {
        [$role, $event] = $this->donationEvent();
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 100]);

        $this->checkout($role, $event, $ticket, 1, ['donation_amount' => '12'])->assertRedirect('https://paypal.test/approve');

        $sale = Sale::where('email', 'dana@guests.org')->firstOrFail();
        $this->assertSame(12.0, (float) $sale->payment_amount);
        $this->assertSame(12.0, (float) $sale->donation_amount);
        $this->assertSame('unpaid', $sale->status, 'taken through the gateway, not the free path');
        $this->assertSame('12.00', $this->paypalTotal());
    }

    public function test_a_donation_is_added_after_a_promo_code_and_never_discounted(): void
    {
        [$role, $event] = $this->donationEvent();
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);
        PromoCode::create(['event_id' => $event->id, 'code' => 'HALF', 'type' => 'percentage', 'value' => 50, 'is_active' => true]);

        $this->checkout($role, $event, $ticket, 2, ['donation_amount' => '7.555', 'promo_code' => 'HALF']);

        $sale = Sale::where('email', 'dana@guests.org')->firstOrFail();
        // 40 - 50% = 20, plus the donation rounded to cents.
        $this->assertSame(7.56, (float) $sale->donation_amount);
        $this->assertSame(27.56, round((float) $sale->payment_amount, 2));
        $this->assertSame('27.56', $this->paypalTotal());
    }

    public function test_no_donation_where_the_event_does_not_ask_for_one(): void
    {
        [$role, $event] = $this->donationEvent(['donations_enabled' => false]);
        $ticket = $this->createTicket($event, ['price' => 10, 'quantity' => 100]);

        $this->checkout($role, $event, $ticket, 1, ['donation_amount' => '5'])->assertSessionHas('error', __('messages.donation_not_available'));

        $this->assertSame(0, Sale::count());
    }

    /** Cash or a payment link would turn a free ticket with a donation into an unpaid order. */
    public function test_no_donation_through_a_gateway_that_does_not_charge_online(): void
    {
        [$role, $event] = $this->donationEvent(['payment_method' => 'cash']);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 100]);

        $this->assertFalse($event->offersDonations());
        $this->checkout($role, $event, $ticket, 1, ['donation_amount' => '5'])->assertSessionHas('error', __('messages.donation_not_available'));
        $this->assertSame(0, Sale::count());
    }

    public function test_a_free_plan_cannot_take_a_donation(): void
    {
        [$role, $event] = $this->donationEvent();
        config(['app.hosted' => true]);
        $role->forceFill(['plan_type' => 'free', 'plan_expires' => now()->subDay()->format('Y-m-d')])->save();

        $this->assertFalse($event->fresh()->offersDonations());

        // A grandfathered event may still sell its paid tickets; donations are Pro and stop.
        $event->forceFill(['tickets_grandfathered_at' => now()->subYear()])->save();
        $this->assertTrue($event->fresh()->canSellPaidTickets(), 'the grandfather stamp still sells tickets');
        $this->assertFalse($event->fresh()->offersDonations());
    }

    public function test_a_donation_outside_its_limits_is_refused(): void
    {
        [$role, $event] = $this->donationEvent();
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 100]);

        [$minimum, $maximum] = $event->donationLimits();
        $this->assertSame(10000.0, $maximum, 'two thousand times the first suggested dollar amount');

        $this->checkout($role, $event, $ticket, 1, ['donation_amount' => '20000'])->assertSessionHas('error');
        $this->assertSame(0, Sale::count());
    }

    public function test_a_cart_carries_no_donation(): void
    {
        [$role, $event] = $this->donationEvent();
        $ticket = $this->createTicket($event, ['price' => 10, 'quantity' => 100]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'name' => 'Dana Buyer',
            'email' => 'dana@guests.org',
            'legs' => [[
                'event_id' => UrlUtils::encodeId($event->id),
                'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
                'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
                'donation_amount' => '5',
            ]],
        ])->assertSessionHas('error', __('messages.donation_not_available'));

        $this->assertSame(0, Sale::count());
    }

    /** Stripe is sent the donation as its own line, and the lines add up to what is charged. */
    public function test_stripe_lists_the_donation_and_the_lines_add_up_with_a_gift_card(): void
    {
        [, $event] = $this->donationEvent(['payment_method' => 'stripe']);
        $ticket = $this->createTicket($event, ['price' => 10, 'quantity' => 100]);
        $ticket->setRelation('event', $event);
        $saleTicket = new SaleTicket(['ticket_id' => $ticket->id, 'quantity' => 3]);
        $saleTicket->setRelation('ticket', $ticket);

        $gateway = app(StripeGateway::class);
        $method = new \ReflectionMethod($gateway, 'buildStripeLineItems');
        $method->setAccessible(true);

        // 30 of tickets + 5 donation - 7 gift card = 28 charged.
        $lines = $method->invoke($gateway, collect([$saleTicket]), $event, [], 7.0, 28.0, 5.0);

        $names = array_map(fn ($line) => $line['price_data']['product_data']['name'], $lines);
        $this->assertContains(__('messages.donation'), $names);
        $total = array_sum(array_map(fn ($line) => $line['price_data']['unit_amount'] * $line['quantity'], $lines));
        $this->assertSame(2800, $total);
        foreach ($lines as $line) {
            $this->assertGreaterThanOrEqual(0, $line['price_data']['unit_amount']);
        }
    }

    public function test_the_owner_saves_the_ask_and_a_free_plan_cannot_switch_it_on(): void
    {
        [$role, $event] = $this->donationEvent(['donations_enabled' => false]);
        $owner = $role->users()->first();

        $this->putUpdateEvent($owner, $role, $event, [
            'tickets_enabled' => 1,
            'payment_method' => 'paypal',
            'donations_enabled' => 1,
            'donation_amounts' => ['3', '', '12.5'],
            'donation_custom' => 0,
            'donation_line' => '<b>Support</b> the choir',
        ])->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertTrue($event->donations_enabled);
        $this->assertSame([3.0, 12.5], array_map('floatval', $event->donation_amounts), 'an empty box is dropped');
        $this->assertFalse($event->donation_custom);
        $this->assertSame('Support the choir', $event->donation_line);

        // A save that does not carry the fields leaves them.
        $this->putUpdateEvent($owner, $role, $event, ['tickets_enabled' => 1, 'payment_method' => 'paypal']);
        $this->assertSame([3.0, 12.5], array_map('floatval', $event->fresh()->donation_amounts));

        $free = $this->createFreeRole();
        $freeOwner = $free->users()->first();
        $freeEvent = $this->createEvent($free, ['tickets_enabled' => true, 'creator_role_id' => $free->id]);
        $this->putUpdateEvent($freeOwner, $free, $freeEvent, ['tickets_enabled' => 1, 'donations_enabled' => 1]);
        $this->assertFalse((bool) $freeEvent->fresh()->donations_enabled);
    }

    public function test_the_ticket_form_and_the_receipt_show_the_donation(): void
    {
        [$role, $event] = $this->donationEvent(['donation_line' => 'Support the choir']);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 100]);

        $this->get($this->guestEventUrl($role, $event))->assertOk()
            ->assertSee('Support the choir')
            ->assertSee(__('messages.free_donations_welcome'))
            ->assertSee('name="donation_amount"', false);

        $sale = $this->createSale($event, $role, ['email' => 'dana@guests.org', 'payment_amount' => 15, 'donation_amount' => 15], $ticket, 1);
        $html = (new TicketPurchase($sale, $event, $role))->render();
        $this->assertStringContainsString(__('messages.donation_thanks', ['amount' => '$15']), $html);
        $this->assertSame(15.0, $sale->toApiData()->donation_amount);
    }

    /** The cap follows the currency and the owner's own amounts: none of them is ever refused. */
    public function test_the_limits_suit_every_currency_and_the_owners_amounts(): void
    {
        [, $event] = $this->donationEvent(['ticket_currency_code' => 'IDR', 'donation_amounts' => [50000, 100000, 2000000]]);

        [$minimum, $maximum] = $event->donationLimits(25000.0);
        $this->assertSame(100000000.0, $maximum, '2,000 x Rp50,000');
        $this->assertLessThanOrEqual(1.0, $minimum, 'beside a priced ticket, no gateway minimum: only the smallest unit');

        [, $usd] = $this->donationEvent(['donation_amounts' => [20000]]);
        $this->assertSame(200000.0, $usd->donationLimits()[1], 'ten times the owner\'s largest when that is more');

        foreach (MoneyUtils::currencyCodes() as $code) {
            $defaults = Event::defaultDonationAmounts($code);
            $this->assertCount(3, $defaults, $code);
            $this->assertTrue($defaults[0] > 0 && $defaults[0] < $defaults[1] && $defaults[1] < $defaults[2], $code.' suggests three rising amounts');
        }
        $this->assertNotSame(Event::defaultDonationAmounts('USD'), Event::defaultDonationAmounts('IDR'), 'IDR has amounts of its own');
    }

    public function test_only_the_suggested_amounts_when_the_owner_allows_no_other(): void
    {
        [$role, $event] = $this->donationEvent(['donation_custom' => false, 'donation_amounts' => [5, 10]]);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 100]);

        $this->checkout($role, $event, $ticket, 1, ['donation_amount' => '7'])->assertSessionHas('error', __('messages.donation_not_available'));
        $this->assertSame(0, Sale::count());

        $this->checkout($role, $event, $ticket, 1, ['donation_amount' => '10']);
        $this->assertSame(10.0, (float) Sale::firstOrFail()->donation_amount);
    }

    /** Pay what you want left unpaid must not hold a capped free event's seats for ever. */
    public function test_an_abandoned_donation_checkout_is_released_after_two_days(): void
    {
        [$role, $event] = $this->donationEvent(['expire_unpaid_tickets' => 0]);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 100]);
        $sale = $this->createSale($event, $role, ['email' => 'dana@guests.org', 'status' => 'unpaid', 'payment_method' => 'paypal', 'payment_amount' => 10, 'donation_amount' => 10], $ticket, 1);
        Sale::whereKey($sale->id)->update(['created_at' => now()->subHours(49)]);

        $this->artisan('app:release-tickets')->assertSuccessful();

        $this->assertSame('expired', $sale->fresh()->status);
    }

    public function test_an_owner_may_write_amounts_with_a_comma_and_a_refused_save_keeps_them(): void
    {
        [$role, $event] = $this->donationEvent();
        $owner = $role->users()->first();

        $this->putUpdateEvent($owner, $role, $event, ['tickets_enabled' => 1, 'payment_method' => 'paypal', 'donations_enabled' => 1, 'donation_amounts' => ['5,50', '', '']])
            ->assertSessionHasNoErrors();
        $this->assertSame([5.5], array_map('floatval', $event->fresh()->donation_amounts));

        // Refused (a line too long): the page comes back with what was typed, not with what is saved.
        $this->putUpdateEvent($owner, $role, $event, ['tickets_enabled' => 1, 'payment_method' => 'paypal', 'donations_enabled' => 1, 'donation_amounts' => ['7', '8', ''], 'donation_custom' => 0, 'donation_line' => str_repeat('x', 200)])
            ->assertSessionHasErrors('donation_line');
        $html = $this->actingAs($owner)->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))->assertOk()->getContent();
        $this->assertStringContainsString('"donation_amounts":["7","8",""]', $html);
        $this->assertStringContainsString('"donation_custom":false', $html);
    }

    /** The owner's "1,000" is a thousand, as a buyer's is, and one amount is never offered twice. */
    public function test_an_owners_amounts_are_read_as_a_buyers_are_and_kept_once(): void
    {
        [$role, $event] = $this->donationEvent();
        $owner = $role->users()->first();

        $this->putUpdateEvent($owner, $role, $event, ['tickets_enabled' => 1, 'payment_method' => 'paypal', 'donations_enabled' => 1, 'donation_amounts' => ['1,000', '10', '10.00']])
            ->assertSessionHasNoErrors();

        $this->assertSame([1000.0, 10.0], array_map('floatval', $event->fresh()->donation_amounts));
        $this->assertSame([1000.0, 10.0], $event->fresh()->donationAmounts());
    }

    /** Beside a priced ticket the page asks no gateway minimum, as the server asks none. */
    public function test_the_page_knows_the_minimum_beside_a_priced_ticket(): void
    {
        [$role, $event] = $this->donationEvent(['payment_method' => 'paypal']);
        $this->createTicket($event, ['price' => 20, 'quantity' => 100]);

        $html = $this->get($this->guestEventUrl($role, $event->fresh()))->assertOk()->getContent();

        $this->assertStringContainsString('donationMinWithTickets: '.json_encode($event->donationLimits(1.0)[0]), $html);
        $this->assertStringContainsString('return Math.max(this.donationMinWithTickets, this.donationLimits[0] - ticketsNet);', $html);
        $this->assertStringContainsString("v-if=\"donationChoice === 'other' && donationCustom\"", $html);
    }

    /**
     * Beside a priced ticket the gateway's minimum is waived, from list prices; a promo code that
     * takes the ticket to nothing leaves the donation as the whole charge, and Stripe refuses one
     * under its minimum. Refused here, before anything is held or sent to Stripe.
     */
    public function test_a_promo_code_cannot_leave_a_donation_under_the_gateway_minimum(): void
    {
        config(['services.stripe_platform.secret' => 'sk_test_donation_minimum']);
        [$role, $event] = $this->donationEvent(['payment_method' => 'stripe']);
        $event->user->forceFill(['stripe_account_id' => 'acct_donation_minimum', 'stripe_completed_at' => now()])->save();
        $event = $event->fresh();
        $this->assertTrue($event->offersDonations());
        $ticket = $this->createTicket($event, ['price' => 10, 'quantity' => 100]);
        PromoCode::create(['event_id' => $event->id, 'code' => 'FREE', 'type' => 'percentage', 'value' => 100, 'is_active' => true]);

        $this->checkout($role, $event, $ticket, 1, ['donation_amount' => '0.25', 'promo_code' => 'FREE'])
            ->assertSessionHas('error', __('messages.donation_out_of_range', ['min' => '$0.50', 'max' => '$10,000']));

        $this->assertSame(0, Sale::count());
        $this->assertSame(0, (int) PromoCode::where('code', 'FREE')->value('times_used'), 'the promo code is not spent');
    }
}
