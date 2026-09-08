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
        $this->orderResponse = null;

        Http::fake(function ($request) {
            $this->sent[] = ['url' => $request->url(), 'body' => (array) $request->data()];

            if (str_contains($request->url(), '/v1/oauth2/token')) {
                return Http::response(['access_token' => 'fake-token', 'expires_in' => 32400]);
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

    public function test_the_client_secret_never_reaches_the_browser(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 10, 'quantity' => 5]);

        $this->checkout($role, $event, $ticket)->assertDontSee('super-secret', escape: false);
    }

    public function test_the_live_host_is_used_when_sandbox_is_off(): void
    {
        $owner = $this->connectedOwner(['paypal_sandbox' => false]);
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 10, 'quantity' => 5]);

        $this->checkout($role, $event, $ticket);

        $hosts = array_map(fn ($call) => parse_url($call['url'], PHP_URL_HOST), $this->sent);

        $this->assertContains('api-m.paypal.com', $hosts);
        $this->assertNotContains('api-m.sandbox.paypal.com', $hosts);
    }

    /**
     * The two currencies PayPal takes and this app disagrees with it about.
     *
     * MoneyUtils holds STRIPE's zero-decimal list - JPY but not HUF or TWD - so the app stores
     * fractions PayPal will not accept. They are excluded from the allowlist for that reason, and
     * this pins the exclusion so a future "PayPal supports 25 currencies" tidy-up cannot quietly
     * reintroduce a class of amount_mismatch.
     */
    public function test_the_currencies_our_own_rounding_disagrees_about_are_not_offered(): void
    {
        $driver = app(PaymentGatewayManager::class)->get('paypal');

        $this->assertTrue($driver->supportsCurrency('USD'));
        $this->assertTrue($driver->supportsCurrency('JPY'), 'JPY is zero-decimal on both sides, so it is safe');
        $this->assertFalse($driver->supportsCurrency('HUF'));
        $this->assertFalse($driver->supportsCurrency('TWD'));
        // PayPal settles neither, so offering them would be a buyer-facing rejection.
        $this->assertFalse($driver->supportsCurrency('ZAR'));
        $this->assertFalse($driver->supportsCurrency('INR'));
    }

    public function test_a_jpy_order_carries_no_decimals(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->paypalEvent($role, ['ticket_currency_code' => 'JPY']);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 1500, 'quantity' => 5]);

        $this->checkout($role, $event, $ticket);

        $this->assertSame('1500', $this->orderBody()['purchase_units'][0]['amount']['value']);
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

        $tokenCall = collect($this->sent)->first(fn ($c) => str_contains($c['url'], '/v1/oauth2/token'));

        // Their money must reach their account, not the operator's.
        $this->assertNotNull($tokenCall);
        $this->assertSame('api-m.sandbox.paypal.com', parse_url($tokenCall['url'], PHP_URL_HOST));
        $this->assertTrue(
            app(PaymentGatewayManager::class)->get('paypal')->hasOwnCredentials($owner)
        );
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
