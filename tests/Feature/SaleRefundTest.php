<?php

namespace Tests\Feature;

use App\Jobs\SendWebhook;
use App\Models\PromoCode;
use App\Models\Sale;
use App\Models\SaleRefund;
use App\Models\Webhook;
use App\Services\SaleRefundResult;
use App\Services\SaleRefundService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\Feature\Concerns\FakesStripeRefunds;
use Tests\TestCase;

/**
 * Refunds that actually move money.
 *
 * Until SaleRefundService existed, refundSale() only flipped a status: the app said "Successfully
 * refunded ticket" and the buyer's money stayed where it was. The tests that matter here are the
 * ones guarding the two ways this can go expensively wrong - refunding MORE than was charged, and
 * letting a PARTIAL refund reach Sale::booted's released branch, which hands back every seat, the
 * whole promo redemption and the entire gift card.
 */
class SaleRefundTest extends TestCase
{
    use CreatesScheduleData;
    use FakesStripeRefunds;
    use RefreshDatabase;

    /** An ordinary single paid Stripe sale carrying a real-looking PaymentIntent. */
    private function paidStripeSale(float $amount = 100.0): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $ticket = $this->createTicket($event, ['price' => $amount, 'quantity' => 50]);

        $sale = $this->createSale($event, $role, [
            'payment_amount' => $amount,
            'payment_method' => 'stripe',
            'status' => 'paid',
            'transaction_reference' => 'pi_test_'.uniqid(),
        ], $ticket);

        return [$sale->fresh(), $event, $ticket, $owner];
    }

    /**
     * A four-payment plan on a $1,000 sale with the first two legs collected.
     *
     * @return array{0: Sale, 1: \App\Models\SaleInstallmentPlan, 2: \App\Models\Ticket, 3: \App\Models\User}
     */
    private function paidInstallmentSale(): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'ticket_currency_code' => 'USD',
            'installments_enabled' => true,
            'installment_count' => 4,
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $ticket = $this->createTicket($event, ['price' => 1000, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, [
            'status' => 'paid',
            'payment_amount' => 1000,
            'payment_method' => 'stripe',
            'transaction_reference' => 'pi_first',
        ], $ticket);

        $plan = app(\App\Services\InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');

        foreach ([1, 2] as $sequence) {
            $plan->installments->firstWhere('sequence', $sequence)->update([
                'status' => 'paid',
                'paid_at' => now(),
                'transaction_reference' => 'pi_leg_'.$sequence,
            ]);
        }
        $plan->update(['amount_paid' => 500.00]);

        return [$sale->fresh(), $plan->fresh('installments'), $ticket, $owner];
    }

    public function test_a_full_refund_calls_the_gateway_and_flips_the_status(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale] = $this->paidStripeSale(100.0);

        $result = app(SaleRefundService::class)->refund($sale);

        $this->assertSame(SaleRefundResult::REFUNDED, $result->status);
        $this->assertCount(1, $fake->calls);
        // No amount is sent for a whole-sale refund, so the gateway returns exactly what it holds.
        // Naming our own figure - a sum of per-seat decimals settlement reconciled only to within a
        // cent - can leave a cent stranded on a grouped order forever.
        $this->assertNull($fake->calls[0]['amount']);
        $this->assertSame('refunded', $sale->fresh()->status);

        $refund = SaleRefund::where('sale_id', $sale->id)->firstOrFail();
        $this->assertSame('succeeded', $refund->status);
        $this->assertSame('re_test_1', $refund->gateway_refund_id);
    }

    /**
     * The one that protects the money.
     *
     * Sale::booted's released branch assumes the sale is completely dead. If a partial refund
     * reached it, refunding $20 of a $100 order would return every seat and restore the whole promo
     * redemption - a giveaway, not a refund.
     */
    public function test_a_partial_refund_leaves_the_sale_paid_and_releases_nothing(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale, $event, $ticket] = $this->paidStripeSale(100.0);

        $promo = PromoCode::create([
            'event_id' => $event->id,
            'code' => 'SAVE10',
            'type' => 'fixed',
            'value' => 10,
            'times_used' => 3,
            'is_active' => true,
        ]);
        $sale->promo_code_id = $promo->id;
        $sale->saveQuietly();

        $soldBefore = $ticket->fresh()->sold;

        $result = app(SaleRefundService::class)->refund($sale->fresh(), 20.0);

        $this->assertSame(SaleRefundResult::PARTIALLY_REFUNDED, $result->status);
        $this->assertEqualsWithDelta(20.0, $fake->calls[0]['amount'], 0.001);

        $this->assertSame('paid', $sale->fresh()->status, 'A partial refund must not change the status.');
        $this->assertSame($soldBefore, $ticket->fresh()->sold, 'A partial refund must not return ticket stock.');
        $this->assertSame(3, $promo->fresh()->times_used, 'A partial refund must not give back the promo redemption.');
    }

    /**
     * The ceiling is what the gateway took, not sales.payment_amount.
     *
     * On a group primary payment_amount is deliberately the PER-SEAT figure, so capping on it would
     * limit a two-seat order to one seat's price and strand the rest of the buyer's money.
     */
    public function test_the_ceiling_is_the_group_total_not_the_per_seat_amount(): void
    {
        $fake = $this->fakeStripeRefunds();
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $ticket = $this->createTicket($event, ['price' => 30.0, 'quantity' => 50]);

        $primary = $this->createSale($event, $role, [
            'payment_amount' => 30.0,
            'payment_method' => 'stripe',
            'status' => 'paid',
            'transaction_reference' => 'pi_test_group',
        ], $ticket);
        $primary->group_id = $primary->id;
        $primary->saveQuietly();

        $guest = $this->createSale($event, $role, [
            'email' => 'guest@example.com',
            'payment_amount' => 30.0,
            'payment_method' => 'stripe',
            'status' => 'paid',
        ], $ticket);
        $guest->group_id = $primary->id;
        $guest->saveQuietly();

        $primary = $primary->fresh();
        $this->assertEqualsWithDelta(60.0, $primary->chargedTotal(), 0.001);

        // 45 is more than the primary's own payment_amount (30) and less than the group total (60).
        $result = app(SaleRefundService::class)->refund($primary, 45.0);

        $this->assertSame(SaleRefundResult::PARTIALLY_REFUNDED, $result->status);
        $this->assertEqualsWithDelta(45.0, $fake->calls[0]['amount'], 0.001);
    }

    /**
     * payment_method = 'stripe' is not evidence that Stripe holds anything.
     *
     * Marking a sale paid by hand writes the TRANSLATED string manual_payment into
     * transaction_reference, so a naive check would hand that to the API as a payment id.
     */
    public function test_a_hand_marked_sale_falls_back_to_status_only(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale] = $this->paidStripeSale(50.0);

        $sale->transaction_reference = __('messages.manual_payment');
        $sale->saveQuietly();

        $result = app(SaleRefundService::class)->refund($sale->fresh());

        $this->assertSame(SaleRefundResult::UNSUPPORTED, $result->status);
        $this->assertTrue($result->shouldFallBackToStatusOnly());
        $this->assertCount(0, $fake->calls, 'Nothing should have been sent to the gateway.');
        $this->assertSame('paid', $sale->fresh()->status, 'The service alone must not flip the status.');
    }

    public function test_a_refund_cannot_exceed_what_is_left(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale] = $this->paidStripeSale(100.0);

        app(SaleRefundService::class)->refund($sale->fresh(), 60.0);

        $result = app(SaleRefundService::class)->refund($sale->fresh(), 50.0);

        $this->assertSame(SaleRefundResult::INVALID_AMOUNT, $result->status);
        $this->assertCount(1, $fake->calls, 'The over-refund must not reach the gateway.');
    }

    /**
     * A claim that has not resolved yet still holds its amount.
     *
     * Two people clicking Refund at once serialize on the sale's lock, but if the ceiling only
     * counted `succeeded` rows both would see nothing refunded and both would pass.
     */
    public function test_a_pending_claim_holds_its_amount_against_the_ceiling(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale] = $this->paidStripeSale(100.0);

        SaleRefund::create([
            'sale_id' => $sale->id,
            'amount' => 80.0,
            'gateway' => 'stripe',
            'status' => 'pending',
            'idempotency_key' => 'sale_refund_pending_'.$sale->id,
        ]);

        $result = app(SaleRefundService::class)->refund($sale->fresh(), 50.0);

        $this->assertSame(SaleRefundResult::INVALID_AMOUNT, $result->status);
        $this->assertCount(0, $fake->calls);
    }

    public function test_a_definite_gateway_refusal_leaves_the_sale_paid(): void
    {
        $fake = $this->fakeStripeRefunds();
        $fake->throw = new \Stripe\Exception\InvalidRequestException('charge already refunded');

        [$sale] = $this->paidStripeSale(100.0);

        $result = app(SaleRefundService::class)->refund($sale->fresh());

        $this->assertSame(SaleRefundResult::FAILED, $result->status);
        $this->assertSame('paid', $sale->fresh()->status);
        $this->assertSame('failed', SaleRefund::where('sale_id', $sale->id)->value('status'));

        // A definite failure releases its claim, so the money is refundable again.
        $this->assertEqualsWithDelta(100.0, $sale->fresh()->refundableRemaining(), 0.001);
    }

    /**
     * An unknown outcome parks instead of retrying: the money may already have moved, and Stripe's
     * idempotency keys expire, so a later retry with a fresh key is how one refund becomes two.
     */
    public function test_an_indeterminate_outcome_parks_for_reconciliation(): void
    {
        $fake = $this->fakeStripeRefunds();
        $fake->throw = new \Stripe\Exception\ApiConnectionException('timeout');

        [$sale] = $this->paidStripeSale(100.0);

        $result = app(SaleRefundService::class)->refund($sale->fresh());

        $this->assertSame(SaleRefundResult::NEEDS_RECONCILIATION, $result->status);
        $this->assertSame('paid', $sale->fresh()->status);
        $this->assertSame('awaiting_reconciliation', SaleRefund::where('sale_id', $sale->id)->value('status'));

        // Still claimed: an unknown outcome must not free the amount up for a second attempt.
        $this->assertEqualsWithDelta(0.0, $sale->fresh()->refundableRemaining(), 0.001);
    }

    /**
     * An installment sale is N charges, and sales.transaction_reference holds only installment
     * ONE's PaymentIntent. Refunding the sale total against that id asks Stripe for more than that
     * charge ever took, so the plan has to be walked leg by leg.
     */
    public function test_an_installment_plan_is_refunded_leg_by_leg(): void
    {
        $fake = $this->fakeStripeRefunds();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'ticket_currency_code' => 'USD',
            'installments_enabled' => true,
            'installment_count' => 4,
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $ticket = $this->createTicket($event, ['price' => 1000, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, [
            'status' => 'paid',
            'payment_amount' => 1000,
            'payment_method' => 'stripe',
            'transaction_reference' => 'pi_first',
        ], $ticket);

        $plan = app(\App\Services\InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');

        // Two of the four collected, the way the webhook would leave them.
        foreach ([1, 2] as $sequence) {
            $plan->installments->firstWhere('sequence', $sequence)->update([
                'status' => 'paid',
                'paid_at' => now(),
                'transaction_reference' => 'pi_leg_'.$sequence,
            ]);
        }
        $plan->update(['amount_paid' => 500.00]);

        $result = app(SaleRefundService::class)->refund($sale->fresh());

        $this->assertSame(SaleRefundResult::REFUNDED, $result->status);

        // One call per COLLECTED leg, not one for the sale total.
        $this->assertCount(2, $fake->calls);
        $this->assertEqualsWithDelta(250.0, $fake->calls[0]['amount'], 0.001);
        $this->assertEqualsWithDelta(250.0, $fake->calls[1]['amount'], 0.001);

        // The status flips even though only half the sale total was ever collected, so
        // Sale::booted cancels the two payments still to come.
        $this->assertSame('refunded', $sale->fresh()->status);
        $this->assertSame(2, SaleRefund::where('sale_id', $sale->id)->where('status', 'succeeded')->count());
    }

    public function test_a_payment_plan_refuses_a_partial_amount(): void
    {
        $fake = $this->fakeStripeRefunds();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'ticket_currency_code' => 'USD',
            'installments_enabled' => true,
            'installment_count' => 4,
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $ticket = $this->createTicket($event, ['price' => 1000, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, [
            'status' => 'paid',
            'payment_amount' => 1000,
            'payment_method' => 'stripe',
            'transaction_reference' => 'pi_first',
        ], $ticket);

        app(\App\Services\InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');

        $result = app(SaleRefundService::class)->refund($sale->fresh(), 100.0);

        $this->assertSame(SaleRefundResult::INVALID_AMOUNT, $result->status);
        $this->assertCount(0, $fake->calls);
        $this->assertSame('paid', $sale->fresh()->status);
    }

    /**
     * The controller wiring, through the route the Sales page actually posts to.
     *
     * Worth its own test because the interesting decisions are not in the service: whether a
     * partial refund is kept away from the sale.refunded webhook and Sale::booted, and whether a
     * refused refund still reports success to the owner.
     */
    public function test_the_sales_route_refunds_in_full_and_in_part(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale, , , $owner] = $this->paidStripeSale(100.0);

        Queue::fake();
        Webhook::create([
            'user_id' => $owner->id,
            'url' => 'https://example.test/hook',
            'secret' => 'shh',
            'event_types' => ['sale.refunded'],
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), [
                'action' => 'refund',
                'refund_amount' => 40,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEqualsWithDelta(40.0, $fake->calls[0]['amount'], 0.001);
        $this->assertSame('paid', $sale->fresh()->status, 'A partial refund must leave the sale paid.');

        // The sale is still paid, so nothing may announce it as refunded. Asserted rather than
        // taken on trust: the status assertion above passes even if the controller treats a
        // partial as a completed action, and the webhook is the part an integrator would act on.
        Queue::assertNotPushed(SendWebhook::class);

        // The remainder, which should now flip it and fire for real.
        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), ['action' => 'refund'])
            ->assertOk();

        $this->assertSame('refunded', $sale->fresh()->status);
        $this->assertCount(2, $fake->calls);
        Queue::assertPushed(SendWebhook::class);
    }

    public function test_the_sales_route_reports_a_refused_refund_rather_than_success(): void
    {
        $fake = $this->fakeStripeRefunds();
        $fake->throw = new \Stripe\Exception\InvalidRequestException('charge already refunded');

        [$sale, , , $owner] = $this->paidStripeSale(100.0);

        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), ['action' => 'refund'])
            ->assertStatus(422);

        $this->assertSame('paid', $sale->fresh()->status);
    }

    /**
     * The Sales page has to actually offer the control.
     *
     * Both render sites shipped wrapped in `@if(false && ...)` and stayed dead long enough for the
     * user guide to document the absence, which nothing caught because every other test drove the
     * action through the route directly.
     */
    public function test_the_sales_page_offers_a_real_refund_for_a_stripe_sale(): void
    {
        $this->fakeStripeRefunds();
        [$sale, , , $owner] = $this->paidStripeSale(100.0);

        $response = $this->actingAs($owner)->get(route('sales'));

        $response->assertOk();
        $response->assertSee('data-sale-action="refund"', false);
        // Three decimals, matching the stored balance. The button used to publish two, which
        // silently truncated a remainder like 85.002 and left the sale unable to reach `refunded`.
        $response->assertSee('data-refund-remaining="100.000"', false);
        // Asserting the ABSENCE of the status-only label, not the presence of the refund one:
        // the dialog's own heading uses messages.refund_ticket and renders on every Sales page,
        // so assertSee() on it passes even with the button disabled and pins nothing.
        $response->assertDontSee(__('messages.mark_as_refunded'));

        // The mobile card is the other half of this table and reaches the dialog by a different
        // route: an Alpine @click whose arguments are Js::from() values concatenated into a
        // double-quoted attribute. Nothing had ever rendered it - the button lived behind
        // `@if(false && ...)` until refunds shipped - and a malformed concatenation there is a
        // silent JS error at click time, not a failing page.
        $response->assertSee(sprintf(
            "openRefundDialog('%s', '100.000', '%s', 2)",
            UrlUtils::encodeId($sale->id),
            \App\Utils\MoneyUtils::format(100.0, 'USD'),
        ), false);
    }

    /**
     * A rail that cannot move money must say so. The old label promised a refund and delivered a
     * status change, which is the whole reason the button was hidden rather than fixed.
     */
    public function test_the_sales_page_offers_mark_as_refunded_for_a_cash_sale(): void
    {
        $this->fakeStripeRefunds();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 10]);
        $this->createSale($event, $role, [
            'payment_amount' => 20,
            'payment_method' => 'cash',
            'status' => 'paid',
        ], $ticket);

        $response = $this->actingAs($owner)->get(route('sales'));

        $response->assertOk();
        $response->assertSee('data-sale-action="refund"', false);
        $response->assertSee(__('messages.mark_as_refunded'));
        // The `="` matters: the dialog's own JS names this attribute in a selector, so a bare
        // substring check matches the script block on every page and can never fail.
        $response->assertDontSee('data-refund-remaining="', false);
    }

    /**
     * A 5xx is NOT a refusal.
     *
     * ApiRequestor maps every unrecognised status to UnknownApiErrorException, which extends
     * ApiErrorException - so bucketing that whole class as "the gateway refused" marked the claim
     * `failed`, handed its amount back to the ceiling, and told the owner nothing moved. Stripe may
     * well have created the refund, and the owner's second click is how the buyer gets paid twice.
     */
    public function test_a_gateway_5xx_parks_instead_of_releasing_the_claim(): void
    {
        $fake = $this->fakeStripeRefunds();
        $fake->throw = new \Stripe\Exception\UnknownApiErrorException('server error');

        [$sale] = $this->paidStripeSale(100.0);

        $result = app(SaleRefundService::class)->refund($sale->fresh());

        $this->assertSame(SaleRefundResult::NEEDS_RECONCILIATION, $result->status);
        $this->assertSame('awaiting_reconciliation', SaleRefund::where('sale_id', $sale->id)->value('status'));
        $this->assertEqualsWithDelta(0.0, $sale->fresh()->refundableRemaining(), 0.001,
            'A 5xx must keep holding its amount, or a retry refunds the same money again.');
    }

    /**
     * A local misconfiguration never reached the gateway, so it must release the claim - and must
     * not send the owner to a dashboard we did not call.
     */
    public function test_a_configuration_error_fails_without_parking(): void
    {
        $fake = $this->fakeStripeRefunds();
        $fake->throw = new \Stripe\Exception\InvalidArgumentException('no API key provided');

        [$sale] = $this->paidStripeSale(100.0);

        $result = app(SaleRefundService::class)->refund($sale->fresh());

        $this->assertSame(SaleRefundResult::FAILED, $result->status);
        $this->assertSame('configuration', SaleRefund::where('sale_id', $sale->id)->value('error_code'));
        $this->assertSame(__('messages.refund_failed_configuration'), $result->message);
        $this->assertEqualsWithDelta(100.0, $sale->fresh()->refundableRemaining(), 0.001,
            'Nothing was sent, so the money must still be refundable.');
    }

    /**
     * The same request submitted twice is one refund.
     *
     * The key used to be a fresh UUID per ledger row, which cannot dedupe anything: a second HTTP
     * request simply minted a second UUID and Stripe processed a second refund.
     */
    public function test_a_repeated_request_key_does_not_refund_twice(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale, , , $owner] = $this->paidStripeSale(100.0);

        foreach ([1, 2] as $attempt) {
            $this->actingAs($owner)
                ->withHeader('X-Requested-With', 'XMLHttpRequest')
                ->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), [
                    'action' => 'refund',
                    'refund_amount' => 50,
                    'idempotency_key' => 'the-same-key',
                ]);
        }

        $this->assertCount(1, $fake->calls, 'The second submission must not reach the gateway.');
        $this->assertSame(1, SaleRefund::where('sale_id', $sale->id)->count());
        $this->assertEqualsWithDelta(50.0, $sale->fresh()->refundableRemaining(), 0.001);
    }

    /**
     * Refunding "everything" has to mean everything, even when the balance has a third decimal.
     *
     * payment_amount is decimal(13,3) and an unrounded percentage discount routinely puts a
     * thousandth there, while the Sales page can only offer the owner a figure at the currency's
     * own precision. Naming that rounded figure left a fraction outstanding, so the sale never
     * reached `refunded`: seats never released, no webhook, and the button then offered 0.00.
     */
    public function test_a_two_decimal_request_still_clears_a_three_decimal_balance(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale] = $this->paidStripeSale(85.002);

        $result = app(SaleRefundService::class)->refund($sale->fresh(), 85.00);

        $this->assertSame(SaleRefundResult::REFUNDED, $result->status);
        $this->assertNull($fake->calls[0]['amount'],
            'Close enough to the whole balance means send no amount, so the gateway returns what it holds.');
        $this->assertSame('refunded', $sale->fresh()->status);
    }

    /**
     * A plan refund that stopped partway must resume, not restart and abort.
     *
     * The walk used to return the first non-moved result, and on a retry leg one hit the
     * already-claimed guard and answered INVALID_AMOUNT - so it gave up on leg one and the legs
     * that still held money became unrefundable through any path.
     */
    public function test_an_interrupted_plan_refund_resumes_on_the_remaining_legs(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale, $plan] = $this->paidInstallmentSale();

        // Leg 1 already went back on an earlier, interrupted run.
        SaleRefund::create([
            'sale_id' => $sale->id,
            'sale_installment_id' => $plan->installments->firstWhere('sequence', 1)->id,
            'amount' => 250.0,
            'gateway' => 'stripe',
            'status' => 'succeeded',
            'idempotency_key' => 'earlier_run_leg1',
        ]);

        $result = app(SaleRefundService::class)->refund($sale->fresh());

        $this->assertSame(SaleRefundResult::REFUNDED, $result->status);
        $this->assertCount(1, $fake->calls, 'Only the leg that had not been claimed should be sent.');
        $this->assertSame('refunded', $sale->fresh()->status);
    }

    /**
     * Once any leg has moved, the callers must never be told "this rail cannot refund".
     *
     * They answer UNSUPPORTED by flipping the status, which releases every seat and credits the
     * whole gift card - on a sale we had just partially paid out on.
     */
    public function test_a_leg_without_a_reference_does_not_become_a_status_only_refund(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale, $plan, $ticket, $owner] = $this->paidInstallmentSale();

        // Leg 2 was reconciled by hand and carries no PaymentIntent.
        $plan->installments->firstWhere('sequence', 2)->update(['transaction_reference' => null]);

        $soldBefore = $ticket->fresh()->sold;

        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), ['action' => 'refund'])
            ->assertStatus(422);

        $this->assertCount(1, $fake->calls, 'Leg 1 goes back; leg 2 cannot.');
        $this->assertSame('paid', $sale->fresh()->status, 'The sale must not be marked fully refunded.');
        $this->assertSame($soldBefore, $ticket->fresh()->sold, 'No stock may be returned.');
    }

    /**
     * An unconfirmed claim must not be shown to the owner as money returned, but must still hold
     * its amount so the same refund cannot be issued twice.
     */
    public function test_a_parked_refund_is_not_counted_as_returned_but_still_blocks_a_retry(): void
    {
        $fake = $this->fakeStripeRefunds();
        $fake->throw = new \Stripe\Exception\ApiConnectionException('timeout');

        [$sale] = $this->paidStripeSale(100.0);

        app(SaleRefundService::class)->refund($sale->fresh(), 60.0);

        $sale = $sale->fresh();
        $this->assertEqualsWithDelta(0.0, $sale->refundedConfirmedTotal(), 0.001,
            'Nothing is confirmed, so nothing may be displayed as refunded.');
        $this->assertEqualsWithDelta(60.0, $sale->refundedTotal(), 0.001,
            'The ceiling must still hold it.');
        $this->assertTrue($sale->hasUnconfirmedRefund());

        $second = app(SaleRefundService::class)->refund($sale, 60.0);
        $this->assertSame(SaleRefundResult::INVALID_AMOUNT, $second->status);
    }

    /**
     * An unconfirmed refund is money that may already have left. It has to reach a person.
     */
    public function test_an_unconfirmed_refund_raises_an_admin_alert(): void
    {
        [$sale] = $this->paidStripeSale(100.0);

        SaleRefund::create([
            'sale_id' => $sale->id,
            'amount' => 100.0,
            'gateway' => 'stripe',
            'status' => 'awaiting_reconciliation',
            'idempotency_key' => 'parked_key',
        ])->forceFill(['created_at' => now()->subHour()])->save();

        \App\Services\AdminAlertService::flush();

        $row = \App\Services\AdminAlertService::items()->firstWhere('type', 'refunds_unconfirmed');

        $this->assertNotNull($row, 'A parked refund must appear in the admin to-do list.');
        $this->assertSame(1, $row['count']);
    }

    /**
     * A sale can reach `refunded` while a claim is still unconfirmed, and the owner has to be told.
     *
     * refundableRemaining() counts pending and awaiting_reconciliation rows against the ceiling -
     * correct, or the same money could be claimed twice - so a parked $30 plus a confirmed $70
     * satisfies record()'s fullyRefunded test and flips the status. Money that may never have left
     * is then being counted as returned, and the warning used to be rendered only under the `paid`
     * badge, so it disappeared at exactly that moment.
     */
    public function test_a_refunded_sale_with_a_parked_claim_still_warns_the_owner(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale, , , $owner] = $this->paidStripeSale(100.0);

        // An earlier attempt whose outcome the gateway never reported.
        SaleRefund::create([
            'sale_id' => $sale->id,
            'amount' => 30.0,
            'gateway' => 'stripe',
            'status' => 'awaiting_reconciliation',
            'idempotency_key' => 'parked_thirty',
        ]);

        $result = app(SaleRefundService::class)->refund($sale->fresh());

        // The status flip is right - the owner asked for the rest back and the seats must go.
        $this->assertSame(SaleRefundResult::REFUNDED, $result->status);
        $this->assertSame('refunded', $sale->fresh()->status);
        // No amount is sent, and here that is load-bearing rather than incidental: the parked $30
        // may or may not have moved, so only Stripe knows what is left. Naming our own $70 would
        // under-refund the buyer whenever the parked call never landed.
        $this->assertCount(1, $fake->calls);
        $this->assertNull($fake->calls[0]['amount']);

        // ...but "Successfully refunded ticket" is not true while $30 is unaccounted for.
        $this->assertSame(__('messages.refund_needs_reconciliation'), $result->message);

        $response = $this->actingAs($owner)->get(route('sales'));
        $response->assertOk();

        // Counted, not assertSee'd. The table renders every sale TWICE - the desktop row and the
        // mobile card - and each carries its own copy of this block, so a bare assertSee passes
        // with either one still nested under the `paid` badge and pins only the other.
        $this->assertSame(
            2,
            substr_count($response->getContent(), __('messages.refund_awaiting_confirmation')),
            'Both the desktop row and the mobile card must warn on a refunded sale.',
        );
    }

    /**
     * The same thing on the plan rail, which reaches it without the owner doing anything unusual.
     *
     * refundInstallmentPlan() skips legs that already carry a claiming row, so the leg that parked
     * on attempt one is simply absent from attempt two - the walk then "completes" and used to
     * report a clean full refund for a leg it never confirmed.
     */
    public function test_a_plan_with_a_parked_leg_does_not_report_a_clean_refund(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale, $plan] = $this->paidInstallmentSale();

        SaleRefund::create([
            'sale_id' => $sale->id,
            'sale_installment_id' => $plan->installments->firstWhere('sequence', 1)->id,
            'amount' => 250.0,
            'gateway' => 'stripe',
            'status' => 'awaiting_reconciliation',
            'idempotency_key' => 'parked_leg_one',
        ]);

        $result = app(SaleRefundService::class)->refund($sale->fresh());

        $this->assertSame(SaleRefundResult::REFUNDED, $result->status,
            'The status must still flip, or the caller skips the audit entry and the webhook.');
        $this->assertCount(1, $fake->calls, 'Only leg 2 is outstanding.');
        $this->assertSame(__('messages.refund_needs_reconciliation'), $result->message);
    }

    /**
     * The resume path with nothing left to send: every collected leg is claimed, none confirmed.
     *
     * This branch calls no gateway at all, so reporting success here told the owner the money was
     * back on the strength of two timeouts.
     */
    public function test_a_plan_whose_every_leg_parked_reports_no_clean_refund(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale, $plan] = $this->paidInstallmentSale();

        foreach ([1, 2] as $sequence) {
            SaleRefund::create([
                'sale_id' => $sale->id,
                'sale_installment_id' => $plan->installments->firstWhere('sequence', $sequence)->id,
                'amount' => 250.0,
                'gateway' => 'stripe',
                'status' => 'awaiting_reconciliation',
                'idempotency_key' => 'parked_leg_'.$sequence,
            ]);
        }

        $result = app(SaleRefundService::class)->refund($sale->fresh());

        $this->assertSame(SaleRefundResult::REFUNDED, $result->status);
        $this->assertCount(0, $fake->calls, 'Nothing is left to send.');
        $this->assertSame(__('messages.refund_needs_reconciliation'), $result->message);
        $this->assertSame('refunded', $sale->fresh()->status);
    }

    /**
     * A free registration took no money, so it gets Cancel, not a refund control.
     *
     * 'rsvp' is a provenance marker with no driver behind it, so payment_gateways()->get() returns
     * null and the row would otherwise fall through to the status-only "Mark as Refunded".
     */
    public function test_the_sales_page_offers_no_refund_control_for_an_rsvp(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 10]);
        $this->createSale($event, $role, [
            'payment_amount' => 0,
            'payment_method' => 'rsvp',
            'status' => 'paid',
        ], $ticket);

        $response = $this->actingAs($owner)->get(route('sales'));

        $response->assertOk();
        $response->assertDontSee('data-sale-action="refund"', false);
        $response->assertSee('data-sale-action="cancel"', false);
    }

    /**
     * The alert has to lead somewhere. It is the only surface in the app that knows a parked claim
     * exists, and nothing will ever retry one - a person settles it against the dashboard, using
     * the reference this panel prints.
     */
    public function test_the_admin_revenue_page_lists_an_unconfirmed_refund(): void
    {
        [$sale] = $this->paidStripeSale(100.0);
        $admin = $this->createOwner(true);

        SaleRefund::create([
            'sale_id' => $sale->id,
            'amount' => 100.0,
            'currency_code' => 'USD',
            'gateway' => 'stripe',
            'status' => 'awaiting_reconciliation',
            'idempotency_key' => 'sale_refund_needs_a_person',
            'last_error' => 'Connection timed out',
        ])->forceFill(['created_at' => now()->subHour()])->save();

        $response = $this->actingAs($admin)
            ->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->get(route('admin.revenue'));

        $response->assertOk();
        $response->assertSee('id="unconfirmed-refunds"', false);
        $response->assertSee('sale_refund_needs_a_person');
        $response->assertSee('Connection timed out');

        // The badge and the panel must count the same rows, or a red row links to an empty page.
        \App\Services\AdminAlertService::flush();
        $this->assertSame(
            1,
            \App\Services\AdminAlertService::items()->firstWhere('type', 'refunds_unconfirmed')['count'] ?? 0,
        );
    }

    /**
     * The gateway must be called with no transaction open.
     *
     * Holding the sale's row lock across a network round trip is what SaleSettlementService's own
     * comment forbids, and refundSale()'s transaction already carries the documented
     * SALE -> PLAN lock inversion against InstallmentService.
     */
    public function test_no_gateway_call_happens_inside_a_transaction(): void
    {
        $fake = $this->fakeStripeRefunds();
        [$sale] = $this->paidStripeSale(100.0);

        // RefreshDatabase wraps every test in its own transaction, so the floor is 1, not 0.
        $baseline = DB::transactionLevel();

        app(SaleRefundService::class)->refund($sale->fresh());

        $this->assertSame([$baseline], $fake->transactionLevels);
    }
}
