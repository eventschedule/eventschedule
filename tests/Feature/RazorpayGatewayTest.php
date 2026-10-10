<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\SaleRefund;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\SaleRefundService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * RazorpayGateway end to end - checkout, return, webhook, refund - against a faked
 * Razorpay API. Everything the buyer could tamper with is checked against Razorpay's own answer.
 */
class RazorpayGatewayTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const KEY_ID = 'rzp_test_AbCdEf123456';

    private const KEY_SECRET = 'key-secret-xyz';

    private const WEBHOOK_SECRET = 'webhook-secret-xyz';

    private const LINK_ID = 'plink_TestLink0000001';

    private const PAYMENT_ID = 'pay_TestPaymnt0001';

    private const SHORT_URL = 'https://rzp.io/rzp/abc123';

    private $owner;

    private $role;

    private $event;

    private $ticket;

    /** @var array<string, mixed>|null what GET /payment_links/{id} answers; null = 404 */
    private ?array $link = null;

    /** @var list<array<string, mixed>> */
    private array $existingRefunds = [];

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(function ($request) {
            $url = $request->url();

            if ($request->method() === 'POST' && str_ends_with($url, '/v1/payment_links')) {
                return Http::response(['id' => self::LINK_ID, 'short_url' => self::SHORT_URL, 'status' => 'created']);
            }

            if ($request->method() === 'GET' && str_contains($url, '/v1/payment_links/')) {
                return $this->link ? Http::response($this->link) : Http::response(['error' => []], 404);
            }

            if ($request->method() === 'GET' && str_contains($url, '/refunds')) {
                return Http::response(['items' => $this->existingRefunds]);
            }

            if ($request->method() === 'POST' && str_ends_with($url, '/refund')) {
                return Http::response(['id' => 'rfnd_TestRefund00001', 'status' => 'processed']);
            }

            return Http::response([], 404);
        });

        $this->owner = $this->createOwner();
        $this->owner->forceFill([
            'razorpay_key_id' => self::KEY_ID,
            'razorpay_key_secret' => self::KEY_SECRET,
            'razorpay_webhook_secret' => self::WEBHOOK_SECRET,
        ])->save();

        $this->role = $this->createRole($this->owner);
        $this->event = $this->createEvent($this->role, [
            'tickets_enabled' => true,
            'payment_method' => 'razorpay',
            'ticket_currency_code' => 'INR',
        ]);
        $this->ticket = $this->createTicket($this->event, ['type' => 'General', 'price' => 499.5, 'quantity' => 50]);
    }

    private function checkout(int $qty = 2)
    {
        return $this->post(route('event.checkout', ['subdomain' => $this->role->subdomain]), [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'event_date' => Carbon::parse($this->event->starts_at)->format('Y-m-d'),
            'name' => 'Razorpay Buyer',
            'email' => 'rzp-buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($this->ticket->id) => $qty],
        ]);
    }

    /** @return array<string, mixed> */
    private function linkRequestBody(): array
    {
        $pair = collect(Http::recorded())->first(fn ($p) => $p[0]->method() === 'POST' && str_ends_with($p[0]->url(), '/v1/payment_links'));

        return $pair ? (array) $pair[0]->data() : [];
    }

    private function sale(): Sale
    {
        return Sale::where('email', 'rzp-buyer@gmail.com')->firstOrFail();
    }

    /** A paid link as Razorpay's API would return it for $sale. */
    private function paidLink(Sale $sale, array $overrides = []): array
    {
        $encoded = UrlUtils::encodeId($sale->id);

        return array_merge([
            'id' => self::LINK_ID,
            'status' => 'paid',
            'currency' => 'INR',
            'amount' => 99900,
            'amount_paid' => 99900,
            'reference_id' => $encoded.'-abcd1234',
            'notes' => ['sale_id' => $encoded],
            'payments' => [['payment_id' => self::PAYMENT_ID, 'status' => 'captured', 'amount' => 99900]],
        ], $overrides);
    }

    private function returnFromRazorpay(Sale $sale, array $override = [], ?string $secret = null)
    {
        $params = array_merge([
            'razorpay_payment_id' => self::PAYMENT_ID,
            'razorpay_payment_link_id' => self::LINK_ID,
            'razorpay_payment_link_reference_id' => UrlUtils::encodeId($sale->id).'-abcd1234',
            'razorpay_payment_link_status' => 'paid',
        ], $override);

        $params['razorpay_signature'] ??= hash_hmac('sha256', implode('|', [
            $params['razorpay_payment_link_id'],
            $params['razorpay_payment_link_reference_id'],
            $params['razorpay_payment_link_status'],
            $params['razorpay_payment_id'],
        ]), $secret ?? self::KEY_SECRET);

        return $this->get(route('payments.return', array_merge([
            'gateway' => 'razorpay',
            'sale_id' => UrlUtils::encodeId($sale->id),
            'secret' => $sale->secret,
        ], $params)));
    }

    private function webhook(Sale $sale, ?string $signature = null, string $event = 'payment_link.paid')
    {
        $body = json_encode([
            'event' => $event,
            'payload' => [
                'payment_link' => ['entity' => $this->paidLink($sale)],
                'payment' => ['entity' => ['id' => self::PAYMENT_ID, 'amount' => 99900, 'currency' => 'INR', 'status' => 'captured']],
            ],
        ]);

        return $this->call('POST', route('payments.webhook', ['gateway' => 'razorpay']), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature ?? hash_hmac('sha256', $body, self::WEBHOOK_SECRET),
        ], $body);
    }

    // ---------------------------------------------------------------- registry

    public function test_offered_for_inr_events_only(): void
    {
        $gateways = app(PaymentGatewayManager::class);

        $this->assertArrayHasKey('razorpay', $gateways->availableFor($this->owner, 'INR'));
        $this->assertArrayNotHasKey('razorpay', $gateways->availableFor($this->owner, 'USD'));
        $this->assertArrayNotHasKey('razorpay', $gateways->availableFor($this->owner, 'INR', 0.5));
        $this->assertStringContainsString('Test mode', $gateways->get('razorpay')->label($this->owner));
    }

    /**
     * Fails when a later migration restates either enum without 'razorpay' - see the Razorpay enum
     * migration.
     */
    public function test_payment_method_enums_include_razorpay(): void
    {
        foreach (['events', 'sales'] as $table) {
            $type = DB::selectOne("SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'payment_method'", [$table])->t;
            $this->assertStringContainsString("'razorpay'", $type, "$table.payment_method lost 'razorpay'");
        }
    }

    public function test_installation_credentials_are_used_when_the_owner_has_none(): void
    {
        $this->owner->forceFill(['razorpay_key_id' => null, 'razorpay_key_secret' => null, 'razorpay_webhook_secret' => null])->save();
        config(['app.hosted' => false, 'payments.razorpay.key_id' => 'rzp_live_Platform0001', 'payments.razorpay.key_secret' => 'p-secret']);

        $driver = app(PaymentGatewayManager::class)->get('razorpay');

        $this->assertTrue($driver->isConfiguredFor($this->owner->fresh()));
        $this->assertSame('Razorpay', $driver->label($this->owner->fresh()));

        config(['app.hosted' => true]);
        $this->assertFalse($driver->isConfiguredFor($this->owner->fresh()));
    }

    // ---------------------------------------------------------------- checkout

    public function test_checkout_creates_a_payment_link_and_redirects_to_it(): void
    {
        $this->checkout(2)->assertRedirect(self::SHORT_URL);

        $sale = $this->sale();
        $body = $this->linkRequestBody();
        $encoded = UrlUtils::encodeId($sale->id);

        $this->assertSame(99900, $body['amount'], '2 x 499.50 in paise');
        $this->assertSame('INR', $body['currency']);
        $this->assertStringStartsWith($encoded.'-', $body['reference_id']);
        $this->assertSame(['sale_id' => $encoded], $body['notes']);
        $this->assertSame(['sms' => false, 'email' => false], $body['notify']);
        $this->assertStringContainsString($sale->secret, $body['callback_url']);
        $this->assertGreaterThanOrEqual(now()->addMinutes(15)->getTimestamp(), $body['expire_by']);
        $this->assertStringNotContainsString(self::KEY_SECRET, json_encode($body));
        $this->assertSame('unpaid', $sale->status);
        $this->assertSame('razorpay', $sale->payment_method);
    }

    public function test_a_failed_link_creation_releases_the_seats(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['*' => Http::response(['error' => ['description' => 'nope']], 400)]);

        $this->checkout(2)->assertRedirect();

        $this->assertSame('expired', $this->sale()->status);
    }

    // ------------------------------------------------------------------ return

    public function test_a_signed_return_settles_from_the_fetched_link(): void
    {
        $this->checkout(2);
        $sale = $this->sale();
        $this->link = $this->paidLink($sale);

        $this->returnFromRazorpay($sale)->assertRedirect();

        $sale->refresh();
        $this->assertSame('paid', $sale->status);
        $this->assertSame(self::PAYMENT_ID, $sale->transaction_reference);
    }

    public function test_a_bad_signature_does_not_settle_or_even_fetch(): void
    {
        $this->checkout(2);
        $sale = $this->sale();
        $this->link = $this->paidLink($sale);

        $this->returnFromRazorpay($sale, [], 'wrong-secret')->assertRedirect();

        $this->assertSame('unpaid', $sale->fresh()->status);
        $this->assertFalse(collect(Http::recorded())->contains(fn ($p) => str_contains($p[0]->url(), '/payment_links/')));
    }

    public function test_a_link_for_a_different_sale_does_not_settle(): void
    {
        $this->checkout(2);
        $sale = $this->sale();
        $this->link = $this->paidLink($sale, ['notes' => ['sale_id' => UrlUtils::encodeId($sale->id + 999)]]);

        $this->returnFromRazorpay($sale);

        $this->assertSame('unpaid', $sale->fresh()->status);
    }

    public function test_a_link_razorpay_says_is_unpaid_does_not_settle(): void
    {
        $this->checkout(2);
        $sale = $this->sale();
        $this->link = $this->paidLink($sale, ['status' => 'created', 'payments' => []]);

        $this->returnFromRazorpay($sale);

        $this->assertSame('unpaid', $sale->fresh()->status);
    }

    public function test_a_short_payment_is_parked_not_paid(): void
    {
        $this->checkout(2);
        $sale = $this->sale();
        $this->link = $this->paidLink($sale, ['amount_paid' => 100]);

        $this->returnFromRazorpay($sale);

        $this->assertSame('amount_mismatch', $sale->fresh()->status);
    }

    // ----------------------------------------------------------------- webhook

    public function test_a_signed_webhook_settles(): void
    {
        $this->checkout(2);
        $sale = $this->sale();
        $this->link = $this->paidLink($sale);

        $this->webhook($sale)->assertNoContent();

        $this->assertSame('paid', $sale->fresh()->status);
    }

    public function test_a_forged_webhook_is_refused(): void
    {
        $this->checkout(2);
        $sale = $this->sale();
        $this->link = $this->paidLink($sale);

        $this->webhook($sale, 'forged')->assertStatus(400);

        $this->assertSame('unpaid', $sale->fresh()->status);
    }

    public function test_a_webhook_is_ignored_when_no_webhook_secret_is_configured(): void
    {
        $this->owner->forceFill(['razorpay_webhook_secret' => null])->save();
        $this->checkout(2);
        $sale = $this->sale();
        $this->link = $this->paidLink($sale);

        $this->webhook($sale)->assertNoContent();

        $this->assertSame('unpaid', $sale->fresh()->status);
    }

    public function test_unrelated_webhook_events_are_acknowledged(): void
    {
        $this->checkout(2);

        $this->webhook($this->sale(), null, 'payment.captured')->assertNoContent();
        $this->assertSame('unpaid', $this->sale()->status);
    }

    // ------------------------------------------------------------------ refund

    private function paidSale(): Sale
    {
        return $this->createSale($this->event, $this->role, [
            'payment_method' => 'razorpay',
            'status' => 'paid',
            'payment_amount' => 999,
            'transaction_reference' => self::PAYMENT_ID,
        ]);
    }

    public function test_a_partial_refund_sends_paise_and_records_the_refund_id(): void
    {
        $sale = $this->paidSale();

        app(SaleRefundService::class)->refund($sale, 100.5, $this->owner->id, null, null, 'req-key-1');

        $call = collect(Http::recorded())->first(fn ($p) => $p[0]->method() === 'POST' && str_ends_with($p[0]->url(), '/refund'));
        $this->assertSame(10050, $call[0]->data()['amount']);
        $this->assertSame('rfnd_TestRefund00001', SaleRefund::where('sale_id', $sale->id)->firstOrFail()->gateway_refund_id);
    }

    public function test_a_retried_refund_reuses_the_one_already_made(): void
    {
        $sale = $this->paidSale();
        $driver = app(PaymentGatewayManager::class)->get('razorpay');
        $this->existingRefunds = [['id' => 'rfnd_AlreadyDone0001', 'notes' => ['idempotency_key' => 'key-1']]];

        $this->assertSame('rfnd_AlreadyDone0001', $driver->refund($sale, 10.0, 'key-1'));
        $this->assertFalse(collect(Http::recorded())->contains(fn ($p) => $p[0]->method() === 'POST' && str_ends_with($p[0]->url(), '/refund')));
    }

    public function test_a_manually_marked_sale_is_not_refundable_through_razorpay(): void
    {
        $sale = $this->paidSale();
        $sale->forceFill(['transaction_reference' => 'Manual payment'])->save();

        $this->assertNull(app(PaymentGatewayManager::class)->get('razorpay')->refundReferenceFor($sale->fresh()));
    }
}
