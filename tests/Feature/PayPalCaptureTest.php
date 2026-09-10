<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The settlement leg: the capture on the buyer's return, and the account-level webhook that finishes
 * the two cases the return cannot.
 *
 * This is the security file. Everything the buyer could tamper with - the order token, the return
 * URL, the webhook body - is checked against PayPal's own answer before a cent is recognised.
 */
class PayPalCaptureTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private $owner;

    private $role;

    private $event;

    private $ticket;

    private $sale;

    /**
     * What the capture call answers. A property rather than a second Http::fake() call: Http::fake()
     * MERGES, so a later stub for a URL an earlier one already answers never wins.
     *
     * @var \Closure|null
     */
    private $captureResponse = null;

    /** @var \Closure|null */
    private $lookupResponse = null;

    /** @var \Closure|null */
    private $verifyResponse = null;

    private int $captureCalls = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->captureResponse = null;
        $this->lookupResponse = null;
        $this->verifyResponse = null;
        $this->captureCalls = 0;

        Http::fake(function ($request) {
            $url = $request->url();

            if (str_contains($url, '/v1/oauth2/token')) {
                return Http::response(['access_token' => 'fake-token', 'expires_in' => 32400]);
            }

            // GET /v2/payments/captures/{id} - the webhook's gate. Checked BEFORE the capture call
            // below, because that URL also contains the word "capture" and a looser match here
            // silently answered the webhook's lookup with an ORDER body.
            if (str_contains($url, '/v2/payments/captures/')) {
                return $this->lookupResponse
                    ? ($this->lookupResponse)($request)
                    : Http::response($this->capture());
            }

            // POST /v2/checkout/orders/{id}/capture - the call that moves the money.
            if (str_ends_with($url, '/capture')) {
                $this->captureCalls++;

                return $this->captureResponse
                    ? ($this->captureResponse)($request)
                    : Http::response($this->orderWithCapture());
            }

            // GET /v2/checkout/orders/{id} - reading the order back after ORDER_ALREADY_CAPTURED.
            if (str_contains($url, '/v2/checkout/orders/')) {
                return $this->lookupResponse
                    ? ($this->lookupResponse)($request)
                    : Http::response($this->orderWithCapture());
            }

            if (str_contains($url, '/v1/notifications/verify-webhook-signature')) {
                return $this->verifyResponse
                    ? ($this->verifyResponse)($request)
                    : Http::response(['verification_status' => 'SUCCESS']);
            }

            if (str_contains($url, '/v2/checkout/orders')) {
                return Http::response([
                    'id' => 'ORDER123',
                    'links' => [['rel' => 'payer-action', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=ORDER123']],
                ]);
            }

            return Http::response([], 404);
        });

        $this->owner = $this->createOwner();
        $this->owner->forceFill([
            'paypal_client_id' => 'AaBbCc-client-id',
            'paypal_client_secret' => 'super-secret',
            'paypal_sandbox' => true,
            'paypal_webhook_id' => 'WH-TEST-1',
        ])->save();

        $this->role = $this->createRole($this->owner);
        $this->event = $this->createEvent($this->role, [
            'tickets_enabled' => true,
            'payment_method' => 'paypal',
            'ticket_currency_code' => 'USD',
        ]);
        $this->ticket = $this->createTicket($this->event, ['type' => 'General', 'price' => 25, 'quantity' => 50]);

        // Built through the real checkout rather than hand-crafted, so the row under test is shaped
        // exactly as production shapes it.
        $this->post(route('event.checkout', ['subdomain' => $this->role->subdomain]), [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'event_date' => Carbon::parse($this->event->starts_at)->format('Y-m-d'),
            'name' => 'PayPal Buyer',
            'email' => 'paypal-buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($this->ticket->id) => 2],
        ])->assertRedirect();

        $this->sale = Sale::where('email', 'paypal-buyer@gmail.com')->firstOrFail();

        // 2 x $25. Asserted here so a pricing change shows up as a fixture failure rather than as a
        // confusing amount mismatch further down.
        $this->assertSame(50.0, (float) $this->sale->payment_amount);
    }

    /**
     * Seats sold for this occurrence.
     *
     * tickets.sold is a JSON map keyed by occurrence date, so casting the column to int reads 0
     * whatever it holds - an assertion written that way passes without pinning anything.
     */
    private function soldCount(): int
    {
        return $this->ticket->fresh()->soldCountFor(Carbon::parse($this->event->starts_at)->format('Y-m-d'));
    }

    /** @return array<string, mixed> */
    private function capture(array $overrides = []): array
    {
        return array_merge([
            'id' => 'CAPTURE000000001A',
            'status' => 'COMPLETED',
            'custom_id' => UrlUtils::encodeId($this->sale->id),
            'amount' => ['currency_code' => 'USD', 'value' => '50.00'],
        ], $overrides);
    }

    /** @return array<string, mixed> */
    private function orderWithCapture(array $overrides = []): array
    {
        return [
            'id' => 'ORDER123',
            'status' => 'COMPLETED',
            'purchase_units' => [['payments' => ['captures' => [$this->capture($overrides)]]]],
        ];
    }

    private function returnFromPayPal(array $query = [])
    {
        return $this->get(route('payments.return', array_merge([
            'gateway' => 'paypal',
            'sale_id' => UrlUtils::encodeId($this->sale->id),
            'secret' => $this->sale->secret,
            'token' => 'ORDER123',
        ], $query)));
    }

    /** @param  array<string, mixed>  $resource */
    private function webhook(array $resource = [], array $headers = [])
    {
        return $this->call(
            'POST',
            route('payments.webhook', ['gateway' => 'paypal']),
            [],
            [],
            [],
            $this->serverHeaders(array_merge([
                'paypal-cert-url' => 'https://api.sandbox.paypal.com/cert.pem',
                'paypal-transmission-id' => 'txn-1',
                'paypal-transmission-time' => now()->toIso8601String(),
                'paypal-transmission-sig' => 'sig',
                'paypal-auth-algo' => 'SHA256withRSA',
                'Content-Type' => 'application/json',
            ], $headers)),
            json_encode([
                'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
                'id' => 'WH-EVT-1',
                'resource' => array_merge($this->capture(), $resource),
            ])
        );
    }

    /** @return array<string, string> */
    private function serverHeaders(array $headers): array
    {
        $server = [];

        foreach ($headers as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return $server;
    }

    // ------------------------------------------------------------------ return

    public function test_a_capture_settles_the_sale_and_stores_the_capture_id(): void
    {
        $this->returnFromPayPal();

        $sale = $this->sale->fresh();

        $this->assertSame('paid', $sale->status);
        // The CAPTURE id, not the order id: it is the durable reference the webhook finds the sale
        // by, and the id a refund is issued against.
        $this->assertSame('CAPTURE000000001A', $sale->transaction_reference);
    }

    public function test_a_capture_naming_a_different_sale_is_refused(): void
    {
        $this->captureResponse = fn () => Http::response(
            $this->orderWithCapture(['custom_id' => UrlUtils::encodeId($this->sale->id + 999)])
        );

        $this->returnFromPayPal();

        // A tampered token naming another order inside the same merchant account must not settle
        // this one. Payfast gets this guard from m_payment_id.
        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_a_capture_in_a_different_currency_is_refused(): void
    {
        $this->captureResponse = fn () => Http::response($this->orderWithCapture([
            'amount' => ['currency_code' => 'JPY', 'value' => '50.00'],
        ]));

        $this->returnFromPayPal();

        // Otherwise 50 yen would reconcile against a 50 dollar sale on face value.
        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_a_short_capture_lands_in_amount_mismatch_rather_than_paid(): void
    {
        $this->captureResponse = fn () => Http::response($this->orderWithCapture([
            'amount' => ['currency_code' => 'USD', 'value' => '20.00'],
        ]));

        $this->returnFromPayPal();

        $this->assertSame('amount_mismatch', $this->sale->fresh()->status);
    }

    /**
     * The worst thing this integration could do, and the reason handleReturn() has a 422 branch.
     */
    public function test_an_already_captured_order_settles_and_keeps_the_seats(): void
    {
        $this->captureResponse = fn () => Http::response([
            'name' => 'UNPROCESSABLE_ENTITY',
            'details' => [['issue' => 'ORDER_ALREADY_CAPTURED']],
        ], 422);

        $this->lookupResponse = fn () => Http::response($this->orderWithCapture());

        $this->returnFromPayPal();

        $sale = $this->sale->fresh();

        // Settled from the capture that already existed - NOT released. Treating the 422 as a
        // failure would expire the sale, and Sale::booted would hand back the seats, the promo
        // redemption and the gift-card balance for money PayPal had actually taken.
        $this->assertSame('paid', $sale->status);
        $this->assertSame('CAPTURE000000001A', $sale->transaction_reference);
        $this->assertSame(2, $this->soldCount());
    }

    /**
     * A buyer refreshing the return URL must not produce a second capture ATTEMPT.
     *
     * The first version of this test asserted only that the sale ended `paid` and that seats had not
     * doubled - neither of which could fail, since seats are set at checkout and settle() is
     * idempotent. It passed with PayPal-Request-Id removed entirely. What actually matters is that
     * the second request short-circuits before the money call, which is what the status guard added
     * to handleReturn() does.
     */
    public function test_a_refreshed_return_does_not_attempt_a_second_capture(): void
    {
        $this->returnFromPayPal();

        $this->assertSame('paid', $this->sale->fresh()->status);
        $this->assertSame(1, $this->captureCalls);

        $this->returnFromPayPal();

        // Still one. The sale is no longer `unpaid`, so the second return never reaches PayPal -
        // belt and braces on top of PayPal's own idempotency, and the only half we control.
        $this->assertSame(1, $this->captureCalls, 'a refresh must not re-enter the money call');
        $this->assertSame('paid', $this->sale->fresh()->status);
        $this->assertSame(2, $this->soldCount());
    }

    /**
     * The return path must REFUSE to capture a sale whose seats have already gone back.
     *
     * Every other driver here is called after the money moved, so settle()'s released-sale guard is
     * early enough for them. Here the capture is the money, so the check has to come first - otherwise
     * we take the payment and only then discover the seats were resold. Reachable by approving,
     * closing the tab, letting ReleaseTickets expire the sale, then hitting Back.
     */
    public function test_a_released_sale_is_never_captured_in_the_first_place(): void
    {
        $this->sale->forceFill(['status' => 'expired'])->saveQuietly();

        $this->returnFromPayPal();

        $this->assertSame(0, $this->captureCalls, 'no money may move for a sale that cannot be honoured');
        $this->assertSame('expired', $this->sale->fresh()->status);
    }

    /**
     * The webhook is where that escalation still belongs: there the money genuinely HAS moved - PayPal
     * is telling us about a capture it completed - and the sale was released in between.
     */
    public function test_a_webhook_payment_for_a_released_sale_is_escalated_not_just_logged(): void
    {
        Exceptions::fake();

        $this->sale->forceFill(['status' => 'expired'])->saveQuietly();

        $this->webhook();

        // PayPal has the buyer's money and this install cannot honour it: a person must act, so it
        // has to reach Sentry rather than only a log file.
        Exceptions::assertReported(fn (\RuntimeException $e) => str_contains($e->getMessage(), 'can no longer be honoured'));
    }

    public function test_an_amount_mismatch_is_not_escalated_to_sentry(): void
    {
        Exceptions::fake();

        $this->captureResponse = fn () => Http::response($this->orderWithCapture([
            'amount' => ['currency_code' => 'USD', 'value' => '20.00'],
        ]));

        $this->returnFromPayPal();

        // Already counted by AdminAlertService; reporting it too would be noise.
        Exceptions::assertNothingReported();
    }

    /**
     * The settlement half of the cart claim. The checkout test proves ONE purchase unit at the order
     * total goes out; this proves one capture brings the whole order back.
     */
    public function test_one_capture_settles_every_leg_of_a_cart(): void
    {
        $eventB = $this->createEvent($this->role, [
            'tickets_enabled' => true, 'payment_method' => 'paypal', 'ticket_currency_code' => 'USD',
        ]);
        $ticketB = $this->createTicket($eventB, ['type' => 'B', 'price' => 30, 'quantity' => 50]);

        $leg = fn ($event, $ticket, $qty) => [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'tickets' => [UrlUtils::encodeId($ticket->id) => $qty],
        ];

        $this->post(route('event.checkout', ['subdomain' => $this->role->subdomain]), [
            'name' => 'Cart Buyer',
            'email' => 'cart-buyer@gmail.com',
            'legs' => [$leg($this->event, $this->ticket, 2), $leg($eventB, $ticketB, 1)],
        ])->assertRedirect();

        $legs = Sale::where('email', 'cart-buyer@gmail.com')->get();
        $primary = $legs->firstWhere(fn ($sale) => $sale->isOrderPrimary());
        $other = $legs->firstWhere(fn ($sale) => ! $sale->isOrderPrimary());

        $this->captureResponse = fn () => Http::response([
            'id' => 'ORDER123',
            'purchase_units' => [['payments' => ['captures' => [[
                'id' => 'CAPTURE000000009B',
                'status' => 'COMPLETED',
                'custom_id' => UrlUtils::encodeId($primary->id),
                'amount' => ['currency_code' => 'USD', 'value' => '80.00'],
            ]]]]],
        ]);

        $this->get(route('payments.return', [
            'gateway' => 'paypal',
            'sale_id' => UrlUtils::encodeId($primary->id),
            'secret' => $primary->secret,
            'token' => 'ORDER123',
        ]));

        $this->assertSame('paid', $primary->fresh()->status);
        $this->assertSame('paid', $other->fresh()->status, 'the cascade must carry the whole order');

        // Recorded rather than asserted as desirable: the paid cascade is a raw builder update of
        // status and paid_at only, so a non-primary leg keeps a NULL reference and its own Refund
        // control falls back to Mark as Refunded. Identical to Stripe today - a platform property,
        // not a PayPal one - but pinned here so a future change to either is a visible decision.
        $this->assertSame('CAPTURE000000009B', $primary->fresh()->transaction_reference);
        $this->assertNull($other->fresh()->transaction_reference);
    }

    // ----------------------------------------------------------------- pending

    public function test_a_pending_capture_leaves_the_sale_unpaid_and_is_marked_pending(): void
    {
        $this->captureResponse = fn () => Http::response($this->orderWithCapture(['status' => 'PENDING']));

        $this->returnFromPayPal();

        $sale = $this->sale->fresh();

        $this->assertSame('unpaid', $sale->status);
        $this->assertNotNull($sale->paypal_pending_at);
    }

    /**
     * The money bug this column exists to prevent.
     *
     * ticket/view.blade.php renders "this ticket is not paid" plus a Complete payment button for any
     * unpaid sale on a resumable rail - and that button starts a FRESH checkout against a NEW Sale.
     * A buyer whose payment PayPal is merely reviewing would be charged a second time.
     */
    public function test_a_pending_sale_does_not_invite_the_buyer_to_pay_again(): void
    {
        $this->captureResponse = fn () => Http::response($this->orderWithCapture(['status' => 'PENDING']));
        $this->returnFromPayPal();

        $view = $this->get(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'secret' => $this->sale->secret,
        ]));

        $view->assertOk();
        $view->assertDontSee(__('messages.complete_payment'), escape: false);
    }

    public function test_a_sale_awaiting_nothing_still_offers_to_complete_payment(): void
    {
        // The control: without it the assertion above would pass even if the button had been
        // removed for every PayPal sale, or for every sale full stop.
        $view = $this->get(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'secret' => $this->sale->secret,
        ]));

        $view->assertOk();
        $view->assertSee(__('messages.complete_payment'), escape: false);
    }

    /**
     * The half the pending flag was missing.
     *
     * PENDING suppresses the Complete payment button, which is right. But a capture that PayPal then
     * DECLINES is terminal - it will never clear - so the flag has to come back off, or the buyer is
     * left on a ticket page that says "not paid" and offers no route forward, permanently.
     */
    public function test_a_declined_capture_after_a_pending_one_gives_the_buyer_the_button_back(): void
    {
        $this->captureResponse = fn () => Http::response($this->orderWithCapture(['status' => 'PENDING']));
        $this->returnFromPayPal();
        $this->assertNotNull($this->sale->fresh()->paypal_pending_at, 'fixture: the sale must start out pending');

        $this->captureResponse = fn () => Http::response($this->orderWithCapture(['status' => 'DECLINED']));
        $this->returnFromPayPal();

        $sale = $this->sale->fresh();

        $this->assertSame('unpaid', $sale->status);
        $this->assertNull($sale->paypal_pending_at);

        $this->get(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'secret' => $this->sale->secret,
        ]))->assertOk()->assertSee(__('messages.complete_payment'), escape: false);
    }

    /**
     * The other half of what PENDING has to protect: the expiry sweeps.
     *
     * A PENDING capture means PayPal has ALREADY TAKEN the buyer's money and is reviewing it -
     * routine on a new merchant account. The sale correctly stays `unpaid` until the review clears,
     * which puts it squarely in the window app:release-tickets sweeps. Expiring it fires
     * Sale::booted's released branch (seats back to inventory, gift card credited, promo redemption
     * returned), and when PAYMENT.CAPTURE.COMPLETED finally lands settle() finds the sale expired
     * and can only log it. The buyer is out the money, holds no ticket, and the seat has been sold
     * to somebody else.
     *
     * Both sweeps are pinned. The per-event one has no payment_method filter at all, and the 48h
     * gift-card-hold one excludes only nonExpiringKeys() - which is CashGateway alone, so PayPal is
     * in scope there too.
     */
    public function test_a_capture_under_review_is_not_expired_by_the_release_sweep(): void
    {
        $this->event->forceFill(['expire_unpaid_tickets' => 1])->save();

        $this->captureResponse = fn () => Http::response($this->orderWithCapture(['status' => 'PENDING']));
        $this->returnFromPayPal();

        $sale = $this->sale->fresh();
        $this->assertSame('unpaid', $sale->status, 'fixture: a reviewed capture stays unpaid');
        $this->assertNotNull($sale->paypal_pending_at, 'fixture: the review flag must be set');

        // Push it well past the event's own expiry window.
        Sale::whereKey($sale->id)->update(['created_at' => now()->subDays(5)]);

        $this->artisan('app:release-tickets')->assertSuccessful();

        $this->assertSame(
            'unpaid',
            $this->sale->fresh()->status,
            'a capture PayPal is still holding must never be expired - the money has already left the buyer'
        );
    }

    public function test_settling_clears_the_pending_flag(): void
    {
        $this->captureResponse = fn () => Http::response($this->orderWithCapture(['status' => 'PENDING']));
        $this->returnFromPayPal();

        $this->captureResponse = null;
        $this->returnFromPayPal();

        $this->assertSame('paid', $this->sale->fresh()->status);
        $this->assertNull($this->sale->fresh()->paypal_pending_at);
    }

    /**
     * The cart's own version of the pay-twice bug, and the reason the review flag is written across
     * the order rather than onto the row the capture named.
     *
     * Every leg of an order gets its own ticket page with its own Complete payment button, and that
     * button starts a FRESH checkout against a NEW sale. Stamping the flag on the order primary alone
     * left leg B fully able to start a second payment while PayPal was still reviewing the first.
     */
    public function test_no_leg_of_a_cart_can_be_paid_again_while_the_order_is_under_review(): void
    {
        $eventB = $this->createEvent($this->role, [
            'tickets_enabled' => true, 'payment_method' => 'paypal', 'ticket_currency_code' => 'USD',
        ]);
        $ticketB = $this->createTicket($eventB, ['type' => 'B', 'price' => 30, 'quantity' => 50]);

        $leg = fn ($event, $ticket, $qty) => [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'tickets' => [UrlUtils::encodeId($ticket->id) => $qty],
        ];

        $this->post(route('event.checkout', ['subdomain' => $this->role->subdomain]), [
            'name' => 'Cart Buyer', 'email' => 'cart-buyer@gmail.com',
            'legs' => [$leg($this->event, $this->ticket, 2), $leg($eventB, $ticketB, 1)],
        ])->assertRedirect();

        $legs = Sale::where('email', 'cart-buyer@gmail.com')->get();
        $primary = $legs->firstWhere(fn ($sale) => $sale->isOrderPrimary());
        $other = $legs->firstWhere(fn ($sale) => ! $sale->isOrderPrimary());

        $this->captureResponse = fn () => Http::response([
            'id' => 'ORDER123',
            'purchase_units' => [['payments' => ['captures' => [[
                'id' => 'CAPTURE000000009B', 'status' => 'PENDING',
                'custom_id' => UrlUtils::encodeId($primary->id),
                'amount' => ['currency_code' => 'USD', 'value' => '80.00'],
            ]]]]],
        ]);

        $this->get(route('payments.return', [
            'gateway' => 'paypal',
            'sale_id' => UrlUtils::encodeId($primary->id),
            'secret' => $primary->secret,
            'token' => 'ORDER123',
        ]));

        $this->assertNotNull($other->fresh()->paypal_pending_at, 'the flag has to reach every leg');

        // Leg B's own ticket page, reached from the order page by its own secret.
        $this->get(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($eventB->id),
            'secret' => $other->secret,
        ]))->assertOk()->assertDontSee(__('messages.complete_payment'), escape: false);
    }

    // ----------------------------------------------------------------- webhook

    public function test_a_webhook_settles_a_sale_the_return_never_did(): void
    {
        $this->webhook()->assertNoContent();

        $this->assertSame('paid', $this->sale->fresh()->status);
    }

    public function test_a_webhook_for_a_sale_on_another_gateway_is_refused(): void
    {
        $this->sale->forceFill(['payment_method' => 'payfast'])->saveQuietly();

        // PaymentWebhookController makes this check only when a sale id is in the URL, and PayPal's
        // listener URL carries none - so the driver owns it. Without this line a PayPal webhook
        // could settle a Payfast sale. Acknowledged rather than refused: no retry can change whose
        // gateway a sale is on, and a non-2xx would earn three days of them.
        $this->webhook()->assertNoContent();

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_a_webhook_from_an_unrecognised_cert_host_is_refused(): void
    {
        // The cheapest gate, and it runs before any outbound call - the route is unauthenticated, so
        // a forgery must not be able to spend one of the owner's API calls.
        $this->webhook([], ['paypal-cert-url' => 'https://evil.example.com/cert.pem'])->assertStatus(400);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_a_capture_paypal_will_not_confirm_is_refused(): void
    {
        $this->lookupResponse = fn () => Http::response([], 404);

        // The real gate: nobody can make PayPal's own API report a capture that never happened.
        // 503 rather than 400 - if PayPal's API was merely unreachable, a retry is what we want.
        $this->webhook()->assertStatus(503);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_an_explicit_signature_failure_is_refused(): void
    {
        $this->verifyResponse = fn () => Http::response(['verification_status' => 'FAILURE']);

        $this->webhook()->assertStatus(503);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    /**
     * The owner's OTHER PayPal activity, which is the common case and used to be answered 400.
     *
     * A listener is registered against the whole PayPal app, so every capture on that account reaches
     * us - invoices, another storefront, a payment button on their own site. None carry a custom_id
     * we minted, and refusing them earns three days of retries each for news that can never become
     * actionable.
     */
    public function test_a_capture_this_app_never_created_is_acknowledged_not_refused(): void
    {
        $response = $this->call(
            'POST',
            route('payments.webhook', ['gateway' => 'paypal']),
            [], [], [],
            $this->serverHeaders([
                'paypal-cert-url' => 'https://api.sandbox.paypal.com/cert.pem',
                'Content-Type' => 'application/json',
            ]),
            json_encode([
                'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
                'id' => 'WH-EVT-OTHER',
                // A real capture on the owner's account, with no custom_id of ours.
                'resource' => ['id' => 'CAPTUREZZZZZZZZZZZ', 'status' => 'COMPLETED'],
            ])
        );

        $response->assertNoContent();
        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_an_unsubscribed_event_type_is_acknowledged_not_rejected(): void
    {
        $response = $this->call(
            'POST',
            route('payments.webhook', ['gateway' => 'paypal']),
            [],
            [],
            [],
            $this->serverHeaders(['Content-Type' => 'application/json']),
            json_encode(['event_type' => 'PAYMENT.CAPTURE.REFUNDED', 'resource' => []])
        );

        // PayPal retries a non-2xx for three days and disables a listener that keeps failing, so
        // news we do not act on is acknowledged rather than refused.
        $response->assertNoContent();
    }
}
