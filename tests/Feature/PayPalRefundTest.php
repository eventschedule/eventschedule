<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\SaleRefund;
use App\Services\SaleRefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * PayPal refunds, and the reason SaleRefundService grew a driver hook to support them.
 *
 * Its exception ladder names Stripe's classes literally. This driver throws Laravel's, which are
 * plain \Exception subclasses, so every failure - definite refusals included - used to reach the
 * conservative \Throwable arm and be PARKED. A parked claim keeps its amount in refundedTotal()
 * forever, so refundableRemaining() never recovers and the sale can never be refunded through the
 * app again, though nothing moved.
 */
class PayPalRefundTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private $owner;

    private $event;

    private $sale;

    /** @var \Closure|null */
    private $refundResponse = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->refundResponse = null;

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/v1/oauth2/token')) {
                return Http::response(['access_token' => 'fake-token', 'expires_in' => 32400]);
            }

            if (str_contains($request->url(), '/refund')) {
                return $this->refundResponse
                    ? ($this->refundResponse)($request)
                    : Http::response(['id' => 'REFUND00000000001', 'status' => 'COMPLETED']);
            }

            return Http::response([], 404);
        });

        $this->owner = $this->createOwner();
        $this->owner->forceFill([
            'paypal_client_id' => 'AaBbCc-client-id',
            'paypal_client_secret' => 'super-secret',
            'paypal_sandbox' => true,
        ])->save();

        $role = $this->createRole($this->owner);
        $this->event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'paypal',
            'ticket_currency_code' => 'USD',
        ]);

        $this->sale = $this->createSale($this->event, $role, [
            'payment_method' => 'paypal',
            'status' => 'paid',
            'payment_amount' => 50,
            'transaction_reference' => 'CAPTURE000000001A',
        ]);
    }

    private function refund(?float $amount = null): \App\Services\SaleRefundResult
    {
        return app(SaleRefundService::class)->refund(
            $this->sale->fresh(), $amount, $this->owner->id, null, null, 'req-key-1'
        );
    }

    public function test_a_full_refund_moves_the_money_and_records_the_refund_id(): void
    {
        $result = $this->refund();

        $this->assertSame('REFUND00000000001', SaleRefund::where('sale_id', $this->sale->id)->firstOrFail()->gateway_refund_id);
        $this->assertSame('refunded', $this->sale->fresh()->status);
        $this->assertTrue($result->moved());
    }

    public function test_a_full_refund_sends_no_amount(): void
    {
        $this->refund();

        $body = collect(Http::recorded())
            ->first(fn ($pair) => str_contains($pair[0]->url(), '/refund'))[0]->data();

        // Null means "everything PayPal still holds", which is NOT the sale's expected total: an
        // amount_mismatch sale is parked precisely because what arrived was not what we asked for.
        $this->assertArrayNotHasKey('amount', (array) $body);
    }

    public function test_a_partial_refund_sends_the_amount_and_leaves_the_sale_paid(): void
    {
        $this->refund(20.0);

        $body = (array) collect(Http::recorded())
            ->first(fn ($pair) => str_contains($pair[0]->url(), '/refund'))[0]->data();

        $this->assertSame('20.00', $body['amount']['value']);
        $this->assertSame('USD', $body['amount']['currency_code']);
        $this->assertSame('paid', $this->sale->fresh()->status);
    }

    /**
     * The whole point of the driver hook.
     */
    public function test_paypal_refusing_a_refund_fails_the_claim_and_releases_the_ceiling(): void
    {
        $this->refundResponse = fn () => Http::response(
            ['name' => 'UNPROCESSABLE_ENTITY', 'details' => [['issue' => 'REFUND_AMOUNT_EXCEEDED']]],
            422
        );

        $this->refund(20.0);

        $claim = SaleRefund::where('sale_id', $this->sale->id)->firstOrFail();

        // failed, NOT awaiting_reconciliation. A parked claim holds its amount forever, so the sale
        // could never be refunded through the app again even though nothing moved.
        $this->assertSame('failed', $claim->status);
        $this->assertNotContains($claim->status, SaleRefund::CLAIMING_STATUSES);
        $this->assertSame(50.0, (float) $this->sale->fresh()->refundableRemaining());
    }

    public function test_a_paypal_server_error_parks_rather_than_failing(): void
    {
        $this->refundResponse = fn () => Http::response(['name' => 'INTERNAL_SERVER_ERROR'], 500);

        $this->refund(20.0);

        // A 5xx means PayPal took the call and we do not know what it did with it. Failing would
        // tell the owner nothing moved; they would click again and the buyer would be paid twice.
        $this->assertContains(
            SaleRefund::where('sale_id', $this->sale->id)->firstOrFail()->status,
            SaleRefund::CLAIMING_STATUSES
        );
    }

    /**
     * The third arm of the classifier, and the one with the most at stake: no response at all.
     *
     * A timeout or a reset means the refund may well have been issued, so the claim must be parked
     * for a person rather than released. Releasing it would tell the owner nothing moved; they would
     * click again, and the buyer would be paid twice.
     */
    public function test_an_unreachable_paypal_parks_rather_than_failing(): void
    {
        $this->refundResponse = fn () => throw new \Illuminate\Http\Client\ConnectionException('timed out');

        $this->refund(20.0);

        $this->assertContains(
            SaleRefund::where('sale_id', $this->sale->id)->firstOrFail()->status,
            SaleRefund::CLAIMING_STATUSES
        );
    }

    public function test_a_sale_marked_paid_by_hand_offers_no_gateway_refund(): void
    {
        $this->sale->forceFill(['transaction_reference' => __('messages.manual_payment')])->saveQuietly();

        // PayPal ids carry no prefix to test, so shape is the only signal - and this is exactly the
        // sentinel DemoService writes. Anything not capture-shaped gets Mark as Refunded instead.
        $this->assertNull(
            payment_gateways()->get('paypal')->refundReferenceFor($this->sale->fresh())
        );
    }
}
