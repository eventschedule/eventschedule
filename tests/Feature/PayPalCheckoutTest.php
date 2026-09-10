<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Services\Payments\PaymentGatewayManager;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The redirect leg: the order we create, and the gating that keeps owners from selecting PayPal
 * where it cannot work.
 *
 * Every fake is closure-backed rather than a URL stub. Http::fake() MERGES stubs, so a second fake
 * for the same URL never wins and a per-test override would silently keep the setUp behaviour.
 */
class PayPalCheckoutTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const APPROVE_URL = 'https://www.sandbox.paypal.com/checkoutnow?token=ORDER123';

    /** @var list<array{url: string, body: array<string, mixed>}> */
    private array $sent = [];

    /** @var list<string> decoded "client_id:secret" pairs, in call order */
    private array $authHeaders = [];

    /**
     * What the create-order call answers. Overridden by a test that needs PayPal to refuse.
     *
     * A property rather than a second Http::fake() call, because Http::fake() MERGES: a later stub
     * for a URL the first one already answers never wins, so re-faking in the test body would
     * silently keep this behaviour and the test would pass for the wrong reason. (It did, once.)
     *
     * @var \Closure|null
     */
    private $orderResponse = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sent = [];
        $this->authHeaders = [];
        $this->orderResponse = null;
        $this->tokenResponse = null;

        Http::fake(function ($request) {
            $this->sent[] = ['url' => $request->url(), 'body' => (array) $request->data()];

            // Which credentials actually went out. Decoded here because it is the only signal that
            // distinguishes an owner's own account from the installation's when both are sandbox.
            foreach ((array) $request->header('Authorization') as $header) {
                if (str_starts_with((string) $header, 'Basic ')) {
                    $this->authHeaders[] = base64_decode(substr((string) $header, 6));
                }
            }

            if (str_contains($request->url(), '/v1/oauth2/token')) {
                return $this->tokenResponse
                    ? ($this->tokenResponse)($request)
                    : Http::response(['access_token' => 'fake-token', 'expires_in' => 32400]);
            }

            if (str_contains($request->url(), '/v2/checkout/orders')) {
                return $this->orderResponse
                    ? ($this->orderResponse)($request)
                    : Http::response([
                        'id' => 'ORDER123',
                        'status' => 'CREATED',
                        'links' => [['rel' => 'payer-action', 'href' => self::APPROVE_URL]],
                    ]);
            }

            if (str_contains($request->url(), '/v1/notifications/webhooks')) {
                return Http::response(['id' => 'WH-TEST-1']);
            }

            return Http::response([], 404);
        });
    }

    private function connectedOwner(array $attrs = [])
    {
        $owner = $this->createOwner();

        $owner->forceFill(array_merge([
            'paypal_client_id' => 'AaBbCc-client-id',
            'paypal_client_secret' => 'super-secret',
            'paypal_sandbox' => true,
        ], $attrs))->save();

        return $owner;
    }

    /**
     * The installation's own PayPal account, as a selfhost operator would set it in .env.
     *
     * config() rather than putenv(): config/payments.php reads env() once at boot and these tests
     * run long after that.
     */
    private function platformAccount(array $overrides = []): void
    {
        config(array_merge([
            'app.hosted' => false,
            'payments.paypal.client_id' => 'platform-client-id',
            'payments.paypal.client_secret' => 'platform-secret',
            'payments.paypal.sandbox' => true,
            'payments.paypal.webhook_id' => 'WH-PLATFORM',
        ], $overrides));
    }

    private function paypalEvent($role, array $attrs = [])
    {
        return $this->createEvent($role, array_merge([
            'tickets_enabled' => true,
            'payment_method' => 'paypal',
            'ticket_currency_code' => 'USD',
        ], $attrs));
    }

    private function checkout($role, $event, $ticket, int $qty = 1)
    {
        return $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'PayPal Buyer',
            'email' => 'paypal-buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => $qty],
        ]);
    }

    /** @return array<string, mixed> */
    private function orderBody(): array
    {
        foreach ($this->sent as $call) {
            if (str_contains($call['url'], '/v2/checkout/orders')) {
                return $call['body'];
            }
        }

        return [];
    }

    public function test_checkout_redirects_the_buyer_to_paypal(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 25, 'quantity' => 50]);

        $response = $this->checkout($role, $event, $ticket, 2);

        $sale = Sale::where('email', 'paypal-buyer@gmail.com')->firstOrFail();
        $body = $this->orderBody();

        $response->assertRedirect(self::APPROVE_URL);

        $this->assertSame('CAPTURE', $body['intent']);
        // ONE purchase unit, even though a cart could carry several legs: the whole order settles as
        // a single capture, which is all settle() and transaction_reference can hold.
        $this->assertCount(1, $body['purchase_units']);
        $this->assertSame('50.00', $body['purchase_units'][0]['amount']['value']);
        $this->assertSame('USD', $body['purchase_units'][0]['amount']['currency_code']);
        // custom_id is the only identifier of ours that rides onto the capture, so both callbacks
        // depend on it.
        $this->assertSame(UrlUtils::encodeId($sale->id), $body['purchase_units'][0]['custom_id']);

        // eChecks refused up front. A ticket is issued at once or not at all - and ReleaseTickets
        // expires a gift-card order at a hard 48 hours regardless of the event's own setting, so a
        // three-day funding source would hand back seats on money PayPal had taken.
        $this->assertSame(
            'IMMEDIATE_PAYMENT_REQUIRED',
            $body['payment_source']['paypal']['experience_context']['payment_method_preference']
        );

        // The secret rides the buyer-facing callbacks: the id alone is a Sqid and
        // PaymentGatewayController::resolve() refuses both without it.
        $this->assertStringContainsString($sale->secret, $body['payment_source']['paypal']['experience_context']['return_url']);
        $this->assertStringContainsString($sale->secret, $body['payment_source']['paypal']['experience_context']['cancel_url']);

        // Recorded but unpaid. Approving moves no money; only our capture call does.
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'unpaid',
            'payment_method' => 'paypal',
            'payment_amount' => 50,
            'paypal_order_id' => 'ORDER123',
        ]);
    }

    /**
     * The multi-event cart, which supportsCart() promises and the docs, the integrations register and
     * FEATURES.md all publish - and which nothing exercised until now. The single-event assertion
     * above cannot stand in for it: one event always yields one purchase unit.
     *
     * The shape being pinned is the whole reason PayPal can be carted where Payfast cannot. ONE
     * purchase unit at the ORDER total, so the order settles as a single capture - which is all
     * settle() takes, all transaction_reference holds, and all refundReferenceFor() can return.
     */
    public function test_a_two_event_cart_sends_one_purchase_unit_at_the_order_total(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);

        $eventA = $this->paypalEvent($role);
        $eventB = $this->paypalEvent($role);
        $ticketA = $this->createTicket($eventA, ['type' => 'A', 'price' => 25, 'quantity' => 50]);
        $ticketB = $this->createTicket($eventB, ['type' => 'B', 'price' => 30, 'quantity' => 50]);

        $leg = fn ($event, $ticket, $qty) => [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'tickets' => [UrlUtils::encodeId($ticket->id) => $qty],
        ];

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'name' => 'Cart Buyer',
            'email' => 'cart-buyer@gmail.com',
            'legs' => [$leg($eventA, $ticketA, 2), $leg($eventB, $ticketB, 1)],
        ])->assertRedirect(self::APPROVE_URL);

        $body = $this->orderBody();
        $units = $body['purchase_units'];

        $this->assertCount(1, $units, 'several units would mean several captures, which nothing downstream can hold');
        // 2 x 25 + 1 x 30. The ORDER total, not the anchoring leg's own 50.
        $this->assertSame('80.00', $units[0]['amount']['value']);

        $legs = Sale::where('email', 'cart-buyer@gmail.com')->get();
        $primary = $legs->firstWhere(fn ($sale) => $sale->isOrderPrimary());

        $this->assertCount(2, $legs);
        $this->assertNotNull($primary, 'the driver is handed the order primary, which is what makes total() the order total');
        $this->assertSame(UrlUtils::encodeId($primary->id), $units[0]['custom_id']);
    }

    /**
     * The secret must not reach PayPal in anything except the Basic-auth header, and must not reach
     * the buyer at all.
     *
     * The obvious version of this test - assertDontSee on the checkout response - pins nothing: the
     * response is a redirect to PayPal's own approve link, so the secret could not appear in it
     * however badly the driver behaved. Assert against what this code actually composes instead: the
     * order payload and the callback URLs inside it.
     */
    public function test_the_client_secret_never_reaches_the_browser_or_the_order_payload(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 10, 'quantity' => 5]);

        $this->checkout($role, $event, $ticket)->assertDontSee('super-secret', escape: false);

        $this->assertStringNotContainsString('super-secret', json_encode($this->orderBody()));

        // The token call is the ONE place it legitimately goes, and it goes as Basic auth rather
        // than in the body or the query string.
        $tokenCall = collect($this->sent)->first(fn ($c) => str_contains($c['url'], '/v1/oauth2/token'));
        $this->assertStringNotContainsString('super-secret', $tokenCall['url']);
        $this->assertStringNotContainsString('super-secret', json_encode($tokenCall['body']));
    }

    public function test_the_live_host_is_used_when_sandbox_is_off(): void
    {
        $owner = $this->connectedOwner(['paypal_sandbox' => false]);
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 10, 'quantity' => 5]);

        $this->checkout($role, $event, $ticket);

        $orderCall = collect($this->sent)->first(fn ($c) => str_contains($c['url'], '/v2/checkout/orders'));
        $hosts = array_map(fn ($call) => parse_url($call['url'], PHP_URL_HOST), $this->sent);

        // The ORDER specifically, not merely "some call went to the live host" - the token mint alone
        // would satisfy that even if order creation never happened.
        $this->assertNotNull($orderCall);
        $this->assertSame('api-m.paypal.com', parse_url($orderCall['url'], PHP_URL_HOST));
        $this->assertNotContains('api-m.sandbox.paypal.com', $hosts);
    }

    /**
     * The three currencies PayPal takes and this app cannot price safely.
     *
     * PayPal rejects any fractional amount in HUF, JPY and TWD. Our pricing path produces fractions
     * in all three - PromoCode::calculateDiscount() rounds against `Event->currency_code`, an
     * attribute that does not exist, so every currency is rounded to two decimals - and
     * SaleSettlementService's tolerance is a flat 0.01, so the rounded figure we are obliged to send
     * parks the sale in amount_mismatch. Money captured, ticket withheld.
     *
     * JPY was on the allowlist until review: the reasoning was that MoneyUtils already treats it as
     * zero-decimal, which is true and irrelevant, because nothing in the pricing path consults that
     * list. Pinned here so a future "but PayPal supports 25 currencies" tidy-up has to read why.
     */
    public function test_the_currencies_our_own_rounding_cannot_price_are_not_offered(): void
    {
        $driver = app(PaymentGatewayManager::class)->get('paypal');

        $this->assertTrue($driver->supportsCurrency('USD'));
        $this->assertFalse($driver->supportsCurrency('HUF'));
        $this->assertFalse($driver->supportsCurrency('TWD'));
        $this->assertFalse($driver->supportsCurrency('JPY'));
        // PayPal settles neither, so offering them would be a buyer-facing rejection.
        $this->assertFalse($driver->supportsCurrency('ZAR'));
        $this->assertFalse($driver->supportsCurrency('INR'));
    }

    /**
     * The arithmetic that made the exclusion necessary, pinned against the real pricing path rather
     * than asserted in a comment. A tenth off ¥1333 is what PayPal cannot be sent.
     */
    public function test_a_discounted_zero_decimal_price_really_does_go_fractional(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        // USD, because JPY can no longer reach checkout - the arithmetic is the currency-blind part.
        $event = $this->paypalEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 1333, 'quantity' => 5]);

        $promo = \App\Models\PromoCode::create([
            'event_id' => $event->id, 'code' => 'TENOFF', 'type' => 'percentage',
            'value' => 10, 'is_active' => true,
        ]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'PayPal Buyer', 'email' => 'paypal-buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
            'promo_code' => 'TENOFF',
        ]);

        $stored = (float) Sale::where('email', 'paypal-buyer@gmail.com')->firstOrFail()->payment_amount;

        // 1199.70, not 1200. In a zero-decimal currency PayPal could only be sent "1200", and
        // settlement's flat 0.01 tolerance would park the difference as an amount mismatch.
        $this->assertEqualsWithDelta(1199.70, $stored, 0.001);
        $this->assertNotSame(round($stored), $stored, 'the whole point: the stored amount is fractional');
        $this->assertSame($promo->id, Sale::where('email', 'paypal-buyer@gmail.com')->firstOrFail()->promo_code_id);
    }

    public function test_an_unsupported_currency_is_refused_and_gives_the_seats_back(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role, ['ticket_currency_code' => 'ZAR']);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        $this->checkout($role, $event, $ticket)->assertSessionHas('error');

        $sale = Sale::where('email', 'paypal-buyer@gmail.com')->firstOrFail();

        // Released, not merely left unpaid: SaleTicket::created already incremented `sold`, and
        // expire_unpaid_tickets defaults to 0 so nothing would ever reclaim it. The obvious retry
        // would burn another seat and a misconfigured event would sell itself out.
        $this->assertSame('expired', $sale->fresh()->status);
        $this->assertSame(0, $ticket->fresh()->soldCountFor(Carbon::parse($event->starts_at)->format('Y-m-d')));
    }

    public function test_a_refused_order_gives_the_seats_back(): void
    {
        $this->orderResponse = fn () => Http::response(
            ['name' => 'UNPROCESSABLE_ENTITY', 'details' => [['issue' => 'CURRENCY_NOT_SUPPORTED']]],
            422
        );

        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 25, 'quantity' => 50]);

        $this->checkout($role, $event, $ticket)->assertSessionHas('error');

        $this->assertSame('expired', Sale::where('email', 'paypal-buyer@gmail.com')->firstOrFail()->status);
        $this->assertSame(0, $ticket->fresh()->soldCountFor(Carbon::parse($event->starts_at)->format('Y-m-d')));
    }

    public function test_a_disconnected_owner_lands_the_buyer_and_gives_the_seats_back(): void
    {
        $owner = $this->connectedOwner(['paypal_client_id' => null, 'paypal_client_secret' => null]);
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 25, 'quantity' => 50]);

        $this->checkout($role, $event, $ticket);

        $this->assertSame('expired', Sale::where('email', 'paypal-buyer@gmail.com')->firstOrFail()->status);
        $this->assertSame(0, $ticket->fresh()->soldCountFor(Carbon::parse($event->starts_at)->format('Y-m-d')));
    }

    public function test_the_label_names_test_mode_and_is_bare_for_a_null_owner(): void
    {
        $driver = app(PaymentGatewayManager::class)->get('paypal');

        // A forgotten sandbox toggle sells tickets that look entirely normal and take no money, and
        // for PayPal that is a different API host AND different credentials - easier to leave on.
        $this->assertStringContainsString('PayPal', $driver->label($this->connectedOwner()));
        $this->assertNotSame('PayPal', $driver->label($this->connectedOwner()));
        $this->assertSame('PayPal', $driver->label($this->connectedOwner(['paypal_sandbox' => false])));
        // The settings tab strip asks for the bare product name.
        $this->assertSame('PayPal', $driver->label(null));
    }

    /**
     * Test mode has nowhere else to announce itself.
     *
     * Payfast warns on the interstitial it renders before redirecting. PayPal is a plain redirect, so
     * without this panel the only signal is a suffix in a dropdown - and a forgotten toggle sells
     * tickets that look entirely normal and take no money.
     */
    public function test_the_event_form_warns_when_paypal_is_in_test_mode(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role);

        $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->assertSee(__('messages.paypal_test_mode_warning'), escape: false);
    }

    public function test_the_event_form_does_not_warn_when_paypal_is_live(): void
    {
        $owner = $this->connectedOwner(['paypal_sandbox' => false]);
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role);

        $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->assertDontSee(__('messages.paypal_test_mode_warning'), escape: false);
    }

    // ---------------------------------------------------------------- connecting

    /**
     * The headline claim of the credentials work, and it had no test: a mistyped secret used to read
     * as "Connected" until a BUYER found out. PayPal is the only gateway here that can be asked
     * cheaply, so it is asked.
     */
    public function test_credentials_paypal_rejects_are_refused_and_not_stored(): void
    {
        $this->tokenResponse = fn () => Http::response(['error' => 'invalid_client'], 401);

        $owner = $this->createOwner();

        $this->actingAs($owner)->post(route('payments.connect', ['gateway' => 'paypal']), [
            'paypal_client_id' => 'AaBbCc-client-id',
            'paypal_client_secret' => 'wrong-secret',
        ])->assertSessionHasErrors('paypal_client_id');

        $this->assertNull($owner->fresh()->paypal_client_id);
        $this->assertNull($owner->fresh()->paypal_client_secret);
    }

    /**
     * The other half of the same split, and the one that matters more: refusing to store good
     * credentials because a third party happens to be down is the worse error. Same
     * definite-versus-unknown distinction SaleRefundService draws about refunds.
     */
    public function test_credentials_paypal_cannot_be_reached_about_are_stored_with_a_warning(): void
    {
        $this->tokenResponse = fn () => throw new \Illuminate\Http\Client\ConnectionException('network is down');

        $owner = $this->createOwner();

        $this->actingAs($owner)->post(route('payments.connect', ['gateway' => 'paypal']), [
            'paypal_client_id' => 'AaBbCc-client-id',
            'paypal_client_secret' => 'probably-fine',
        ])->assertSessionHasNoErrors()->assertSessionHas('warning');

        $this->assertSame('AaBbCc-client-id', $owner->fresh()->paypal_client_id);
    }

    /**
     * The listener is registered for the owner rather than asked for, which is why there is no
     * "Webhook ID" field on the form.
     */
    public function test_connecting_registers_a_webhook_and_stores_its_id(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner)->post(route('payments.connect', ['gateway' => 'paypal']), [
            'paypal_client_id' => 'AaBbCc-client-id',
            'paypal_client_secret' => 'super-secret',
        ])->assertSessionHasNoErrors();

        $this->assertSame('WH-TEST-1', $owner->fresh()->paypal_webhook_id);

        $registered = collect($this->sent)->first(fn ($c) => str_contains($c['url'], '/v1/notifications/webhooks'));
        $this->assertNotNull($registered);
        $this->assertStringContainsString('/payments/paypal/webhook', $registered['body']['url']);
    }

    /**
     * Repointing the credentials at a DIFFERENT PayPal app re-registers the listener.
     *
     * A webhook id belongs to the app that issued it, so it is worthless the moment the client id
     * changes. ensureWebhookRegistered() returns early when the field is non-empty, so without
     * clearing it first an owner who switched accounts kept the old id for ever: no listener was
     * ever created on the new account, so the two cases only the webhook can finish - a capture
     * PayPal held for review, and one whose response we lost - went quietly dark. Worse, the stale
     * id was still handed to verify-webhook-signature, which answers FAILURE for deliveries that
     * are perfectly genuine.
     */
    public function test_changing_the_client_id_registers_a_listener_on_the_new_account(): void
    {
        $owner = $this->connectedOwner(['paypal_webhook_id' => 'WH-OLD-ACCOUNT']);

        $this->actingAs($owner)->post(route('payments.connect', ['gateway' => 'paypal']), [
            'paypal_client_id' => 'A-DIFFERENT-client-id',
            'paypal_client_secret' => 'another-secret',
        ])->assertSessionHasNoErrors();

        $fresh = $owner->fresh();

        $this->assertSame('A-DIFFERENT-client-id', $fresh->paypal_client_id);
        $this->assertNotSame('WH-OLD-ACCOUNT', $fresh->paypal_webhook_id,
            'the old account\'s listener id cannot be kept against new credentials');
        $this->assertSame('WH-TEST-1', $fresh->paypal_webhook_id, 'a listener is registered on the new account');
    }

    /**
     * The other half: re-saving the SAME client id must not churn the listener.
     *
     * An owner correcting only their secret, or re-saving the form unchanged, should keep the
     * working registration rather than create a duplicate on every save.
     */
    public function test_resaving_the_same_client_id_keeps_the_existing_listener(): void
    {
        $owner = $this->connectedOwner(['paypal_webhook_id' => 'WH-KEEP-ME']);
        $before = count($this->sent);

        $this->actingAs($owner)->post(route('payments.connect', ['gateway' => 'paypal']), [
            'paypal_client_id' => $owner->paypal_client_id,
            'paypal_client_secret' => 'rotated-secret',
        ])->assertSessionHasNoErrors();

        $this->assertSame('WH-KEEP-ME', $owner->fresh()->paypal_webhook_id);

        $registrations = collect(array_slice($this->sent, $before))
            ->filter(fn ($c) => str_contains($c['url'], '/v1/notifications/webhooks'));
        $this->assertCount(0, $registrations, 'no second listener may be created');
    }

    public function test_disconnecting_removes_the_listener_and_clears_every_field(): void
    {
        $owner = $this->connectedOwner(['paypal_webhook_id' => 'WH-TEST-1']);

        $this->actingAs($owner)->post(route('payments.disconnect', ['gateway' => 'paypal']));

        $fresh = $owner->fresh();

        $this->assertNull($fresh->paypal_client_id);
        $this->assertNull($fresh->paypal_client_secret);
        // Not a declared credential field, so the base disconnect() does not clear it - the driver
        // has to, or a reconnect adopts a listener pointing at credentials that are gone.
        $this->assertNull($fresh->paypal_webhook_id);
    }

    public function test_the_install_account_lets_an_unconnected_owner_sell(): void
    {
        $this->platformAccount();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 25, 'quantity' => 50]);

        $this->checkout($role, $event, $ticket)->assertRedirect(self::APPROVE_URL);
    }

    public function test_the_owners_own_account_beats_the_install_account(): void
    {
        $this->platformAccount();

        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 25, 'quantity' => 50]);

        $this->checkout($role, $event, $ticket);

        // The CLIENT ID, not the host: both fixtures are sandbox, so a host assertion passes
        // whichever account was used and pins nothing. The id rides in the Basic-auth header, which
        // is the only place the two accounts actually differ.
        $auth = collect($this->authHeaders)->first();

        $this->assertNotNull($auth, 'the token call must have happened');
        $this->assertStringStartsWith('AaBbCc-client-id:', $auth, 'their money must reach their account, not the operator\'s');
        $this->assertStringNotContainsString('platform-client-id', $auth);
    }

    public function test_install_wide_credentials_are_ignored_when_hosted(): void
    {
        $this->platformAccount(['app.hosted' => true]);

        // Asserted registry-wide elsewhere too, because getting it wrong routes every hosted sale
        // into the operator's own account.
        $this->assertSame([], app(PaymentGatewayManager::class)->get('paypal')->platformCredentials());
    }

    public function test_a_half_filled_install_account_offers_nothing(): void
    {
        $this->platformAccount(['payments.paypal.client_secret' => '']);

        $this->assertSame([], app(PaymentGatewayManager::class)->get('paypal')->platformCredentials());
    }

    /**
     * An owner whose secret was encrypted under a rotated APP_KEY must not silently start settling
     * into the operator's account.
     */
    public function test_an_unreadable_stored_secret_never_falls_back_to_the_install_account(): void
    {
        $this->platformAccount();

        $owner = $this->connectedOwner();

        // Straight into the column, bypassing the EncryptedString cast, so it cannot decrypt.
        \DB::table('users')->where('id', $owner->id)->update(['paypal_client_secret' => 'not-ciphertext']);

        $this->assertNull(app(PaymentGatewayManager::class)->get('paypal')->credentialsFor($owner->fresh()));
    }
}
