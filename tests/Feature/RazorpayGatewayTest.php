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
use Illuminate\Support\Facades\Exceptions;
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

    /** What GET /payments (the key check on save) answers; null = Razorpay cannot be reached. */
    private ?int $verifyStatus = 200;

    /** Whether POST /payment_links fails. */
    private bool $linkFails = false;

    /** Key ids whose GET /payments/{id}/refunds answers 400, as Razorpay does for another account's payment. */
    private array $refundsUnknownTo = [];

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(function ($request) {
            $url = $request->url();

            if ($request->method() === 'POST' && str_ends_with($url, '/v1/payment_links')) {
                return $this->linkFails
                    ? Http::response(['error' => ['description' => 'nope']], 500)
                    : Http::response(['id' => self::LINK_ID, 'short_url' => self::SHORT_URL, 'status' => 'created']);
            }

            if ($request->method() === 'GET' && preg_match('#/v1/payments(\?|$)#', $url)) {
                if ($this->verifyStatus === null) {
                    throw new \Illuminate\Http\Client\ConnectionException('network is down');
                }

                return Http::response(['items' => []], $this->verifyStatus);
            }

            if ($request->method() === 'GET' && str_contains($url, '/v1/payment_links/')) {
                return $this->link ? Http::response($this->link) : Http::response(['error' => []], 404);
            }

            if ($request->method() === 'GET' && str_contains($url, '/refunds')) {
                $keyId = explode(':', base64_decode(substr($request->header('Authorization')[0] ?? '', 6)))[0];

                return in_array($keyId, $this->refundsUnknownTo, true)
                    ? Http::response(['error' => ['description' => 'The id provided does not exist']], 400)
                    : Http::response(['items' => $this->existingRefunds]);
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

    // ------------------------------------------------------------- appointments

    /** A paid INR type on a schedule of the owner's, and a guest who has booked it. */
    private function bookPaidType(): Sale
    {
        $role = $this->createRole($this->owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]),
            'price' => 500, 'currency_code' => 'INR', 'payment_method' => 'razorpay',
        ]);

        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = app(\App\Services\AppointmentService::class)->availableSlots($type, $from, 1);
        $slot = $slots['days'][array_key_first($slots['days'])][0]['utc'];

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Meera', 'email' => 'meera@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York',
        ])->assertOk();

        return Sale::where('email', 'meera@gmail.com')->firstOrFail();
    }

    /** @return array<string, string> */
    private function bookingParams(Sale $sale): array
    {
        return ['event_id' => UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret];
    }

    private function linkCreations(): int
    {
        return collect(Http::recorded())->filter(fn ($p) => $p[0]->method() === 'POST' && str_ends_with($p[0]->url(), '/v1/payment_links'))->count();
    }

    public function test_a_booking_is_paid_through_a_link_and_lands_on_its_page(): void
    {
        $sale = $this->bookPaidType();
        $this->assertSame(1, (int) $sale->event->expire_unpaid_tickets, 'held for an hour');

        $this->post(route('appointments.pay', $this->bookingParams($sale)))->assertRedirect(self::SHORT_URL);
        $this->assertSame(50000, $this->linkRequestBody()['amount']);

        $this->link = $this->paidLink($sale, ['amount' => 50000, 'amount_paid' => 50000]);
        $this->returnFromRazorpay($sale)
            ->assertRedirect(route('appointments.manage', $this->bookingParams($sale)).'?new=1');

        $sale->refresh();
        $this->assertSame('paid', $sale->status);
        $this->assertSame(self::PAYMENT_ID, $sale->transaction_reference);
        $this->assertNotNull($sale->confirmed_at);
    }

    public function test_a_link_razorpay_cannot_create_keeps_the_booking(): void
    {
        $sale = $this->bookPaidType();
        $this->linkFails = true;

        $this->post(route('appointments.pay', $this->bookingParams($sale)))
            ->assertRedirect(route('appointments.manage', $this->bookingParams($sale)))
            ->assertSessionHas('error', __('messages.razorpay_link_failed'));

        // A passing failure: the guest keeps the time and can try again.
        $this->assertSame('unpaid', $sale->fresh()->status);
        $this->linkFails = false;
        $this->post(route('appointments.pay', $this->bookingParams($sale)))->assertRedirect(self::SHORT_URL);
    }

    public function test_a_link_made_late_in_the_hold_expires_before_it(): void
    {
        $sale = $this->bookPaidType();

        $this->travel(40)->minutes();
        $this->post(route('appointments.pay', $this->bookingParams($sale)))->assertRedirect(self::SHORT_URL);

        // Paid after the hold is released, the money would reach a booking that is gone.
        $this->assertLessThanOrEqual(
            $sale->created_at->copy()->addHour()->subMinute()->getTimestamp(),
            $this->linkRequestBody()['expire_by'],
        );
        $this->assertGreaterThanOrEqual(now()->addMinutes(15)->getTimestamp(), $this->linkRequestBody()['expire_by']);
    }

    public function test_too_little_of_the_hold_left_releases_the_booking(): void
    {
        $sale = $this->bookPaidType();
        $manage = route('appointments.manage', $this->bookingParams($sale));

        $this->travel(44)->minutes();
        $this->post(route('appointments.pay', $this->bookingParams($sale)))
            ->assertRedirect($manage)
            ->assertSessionHas('error', __('messages.razorpay_too_late_to_pay'));

        $this->assertSame(0, $this->linkCreations(), 'no link Razorpay would refuse, or that could outlive the hold');
        $this->assertSame('expired', $sale->fresh()->status);

        // Released, so the page offers a new time instead of a Complete payment that cannot work.
        $this->get($manage)->assertOk()
            ->assertSee(__('messages.this_reservation_has_expired'))
            ->assertDontSee(route('appointments.pay', $this->bookingParams($sale)), false);
    }

    public function test_an_expired_link_leaves_a_booking_on_its_own_clock(): void
    {
        $sale = $this->bookPaidType();
        $this->post(route('appointments.pay', $this->bookingParams($sale)));
        $this->link = $this->paidLink($sale, ['status' => 'expired', 'payments' => []]);

        $this->webhook($sale, null, 'payment_link.expired')->assertNoContent();

        $this->assertSame('unpaid', $sale->fresh()->status);
    }

    // ------------------------------------------------------------ settlement guards

    public function test_an_abandoned_ticket_checkout_gives_its_seats_back_when_the_link_expires(): void
    {
        $this->checkout(2);
        $sale = $this->sale();
        $this->link = $this->paidLink($sale, ['status' => 'expired', 'payments' => []]);

        $this->webhook($sale, null, 'payment_link.expired')->assertNoContent();

        $this->assertSame('expired', $sale->fresh()->status);
        $this->assertSame(0, $this->ticket->fresh()->soldCountFor(Carbon::parse($this->event->starts_at)->format('Y-m-d')));
    }

    public function test_an_expiry_notice_for_a_link_that_was_paid_settles_it(): void
    {
        $this->checkout(2);
        $sale = $this->sale();
        $this->link = $this->paidLink($sale);

        $this->webhook($sale, null, 'payment_link.expired')->assertNoContent();

        $this->assertSame('paid', $sale->fresh()->status);
    }

    public function test_a_refunded_sales_replayed_return_raises_no_alarm(): void
    {
        Exceptions::fake();
        $this->checkout(2);
        $sale = $this->sale();
        $this->link = $this->paidLink($sale);
        $this->returnFromRazorpay($sale);
        $this->assertSame('paid', $sale->fresh()->status);

        // Back to Razorpay's success page after a refund sends the browser to the return again.
        $sale->fresh()->forceFill(['status' => 'refunded'])->save();
        $this->returnFromRazorpay($sale)->assertRedirect();

        $this->assertSame('refunded', $sale->fresh()->status);
        Exceptions::assertNothingReported();
    }

    public function test_money_for_a_released_sale_is_reported_from_the_return(): void
    {
        Exceptions::fake();
        $this->checkout(2);
        $sale = $this->sale();
        $sale->forceFill(['status' => 'expired'])->save();
        $this->link = $this->paidLink($sale);

        $this->returnFromRazorpay($sale)->assertRedirect();

        $this->assertSame('expired', $sale->fresh()->status, 'never revived');
        Exceptions::assertReported(fn (\RuntimeException $e) => str_contains($e->getMessage(), 'can no longer be honoured'));
    }

    public function test_a_second_payment_for_a_paid_sale_is_reported(): void
    {
        Exceptions::fake();
        $this->checkout(2);
        $sale = $this->sale();
        $sale->forceFill(['status' => 'paid', 'transaction_reference' => 'pay_FirstPaymnt0001'])->save();
        $this->link = $this->paidLink($sale);

        $this->returnFromRazorpay($sale)->assertRedirect();

        $this->assertSame('pay_FirstPaymnt0001', $sale->fresh()->transaction_reference);
        Exceptions::assertReported(fn (\RuntimeException $e) => str_contains($e->getMessage(), 'already paid'));
    }

    public function test_a_refreshed_return_raises_no_alarm(): void
    {
        Exceptions::fake();
        $this->checkout(2);
        $sale = $this->sale();
        $this->link = $this->paidLink($sale);

        $this->returnFromRazorpay($sale);
        $this->returnFromRazorpay($sale);
        $this->webhook($sale)->assertNoContent();

        $this->assertSame('paid', $sale->fresh()->status);
        Exceptions::assertNothingReported();
    }

    // -------------------------------------------------------------- saving keys

    private function connect(array $input)
    {
        $owner = $this->createOwner();

        return [$owner, $this->actingAs($owner)->post(route('payments.connect', ['gateway' => 'razorpay']), $input)];
    }

    public function test_keys_razorpay_refuses_are_not_stored(): void
    {
        $this->verifyStatus = 401;

        [$owner, $response] = $this->connect(['razorpay_key_id' => 'rzp_live_Mistyped0001', 'razorpay_key_secret' => 'wrong']);

        $response->assertSessionHasErrors(['razorpay_key_id' => __('messages.razorpay_credentials_rejected')]);
        $this->assertNull($owner->fresh()->razorpay_key_id);
    }

    public function test_keys_that_could_not_be_checked_are_stored_and_the_owner_is_told(): void
    {
        $this->verifyStatus = null;

        [$owner, $response] = $this->connect(['razorpay_key_id' => 'rzp_live_Unchecked001', 'razorpay_key_secret' => 'probably-fine']);

        $response->assertSessionHasNoErrors()->assertSessionMissing('message');
        $this->assertSame('rzp_live_Unchecked001', $owner->fresh()->razorpay_key_id);

        // On the page, not only in the session: "Settings saved" used to win the layout's toast.
        $this->get(route('profile.edit'))->assertOk()
            ->assertSee(__('messages.razorpay_credentials_unverified'), false)
            ->assertDontSee(__('messages.settings_saved'), false);
    }

    public function test_good_keys_are_stored(): void
    {
        [$owner, $response] = $this->connect(['razorpay_key_id' => 'rzp_live_GoodKeys0001', 'razorpay_key_secret' => 'right']);

        $response->assertSessionHasNoErrors()->assertSessionHas('message', __('messages.settings_saved'));
        $this->assertSame('rzp_live_GoodKeys0001', $owner->fresh()->razorpay_key_id);
        $this->assertSame('right', $owner->fresh()->razorpay_key_secret);
    }

    public function test_switching_from_test_keys_to_live_ones_drops_the_test_webhook_secret(): void
    {
        $this->actingAs($this->owner)->post(route('payments.connect', ['gateway' => 'razorpay']), [
            'razorpay_key_id' => 'rzp_live_GoingLive0001',
            'razorpay_key_secret' => 'live-secret',
        ])->assertSessionHasNoErrors();

        // Razorpay's Test Mode webhook never hears of a live payment.
        $this->assertNull($this->owner->fresh()->razorpay_webhook_secret);

        // A new secret sent with the switch is kept.
        $this->actingAs($this->owner)->post(route('payments.connect', ['gateway' => 'razorpay']), [
            'razorpay_key_id' => 'rzp_test_BackToTest001',
            'razorpay_webhook_secret' => 'test-webhook-secret',
        ])->assertSessionHasNoErrors();
        $this->assertSame('test-webhook-secret', $this->owner->fresh()->razorpay_webhook_secret);
    }

    // ------------------------------------------------------------- refunds, forms

    public function test_a_refund_moves_on_to_the_installation_account_that_holds_the_payment(): void
    {
        config(['app.hosted' => false, 'payments.razorpay.key_id' => 'rzp_live_Platform0001', 'payments.razorpay.key_secret' => 'p-secret']);
        $this->refundsUnknownTo = [self::KEY_ID];
        $sale = $this->paidSale();

        $refundId = app(PaymentGatewayManager::class)->get('razorpay')->refund($sale, 10.0, 'key-2');

        $this->assertSame('rfnd_TestRefund00001', $refundId);
        $post = collect(Http::recorded())->first(fn ($p) => $p[0]->method() === 'POST' && str_ends_with($p[0]->url(), '/refund'));
        $this->assertSame('Basic '.base64_encode('rzp_live_Platform0001:p-secret'), $post[0]->header('Authorization')[0]);
    }

    public function test_the_event_form_warns_about_test_keys_only(): void
    {
        $edit = fn () => $this->actingAs($this->owner)
            ->get(route('event.edit', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($this->event->id)]))
            ->assertOk();

        $edit()->assertSee(__('messages.razorpay_test_mode_warning'), false);

        $this->owner->forceFill(['razorpay_key_id' => 'rzp_live_AbCdEf123456'])->save();
        $edit()->assertDontSee(__('messages.razorpay_test_mode_warning'), false);
    }
}
