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
        $other = $this->createOwner();

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

    public function test_a_refreshed_return_settles_only_once(): void
    {
        $this->returnFromPayPal();
        $this->returnFromPayPal();

        $sale = $this->sale->fresh();

        $this->assertSame('paid', $sale->status);
        // Two capture attempts is fine - PayPal deduplicates on PayPal-Request-Id and answers 422 -
        // but the seats must not double.
        $this->assertSame(2, $this->soldCount());
    }

    public function test_a_payment_for_a_released_sale_is_escalated_not_just_logged(): void
    {
        Exceptions::fake();

        $this->sale->forceFill(['status' => 'expired'])->saveQuietly();

        $this->returnFromPayPal();

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
        // could settle a Payfast sale.
        $this->webhook()->assertStatus(400);

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
        $this->webhook()->assertStatus(400);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_an_explicit_signature_failure_is_refused(): void
    {
        $this->verifyResponse = fn () => Http::response(['verification_status' => 'FAILURE']);

        $this->webhook()->assertStatus(400);

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
