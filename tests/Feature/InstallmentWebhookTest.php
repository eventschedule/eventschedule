<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\SaleInstallmentPlan;
use App\Services\InstallmentService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Settlement of installment payments.
 *
 * The security cases mirror GiftCardTest's, because the same attack applies: the Connect webhook
 * secret only proves an event came from SOME connected account, so a hostile user could pay
 * themselves on their own account carrying a victim's installment_id and have the victim credited.
 */
class InstallmentWebhookTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const MERCHANT = 'acct_merchant';

    /** @return array{0: Role, 1: Event, 2: Sale, 3: SaleInstallmentPlan} */
    private function scaffold(array $planAttrs = []): array
    {
        $owner = $this->createOwner();
        $owner->stripe_account_id = self::MERCHANT;
        $owner->save();

        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 4,
            'ticket_currency_code' => 'USD',
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $ticket = $this->createTicket($event, ['price' => 1000, 'quantity' => 10]);

        // Starts unpaid: installment 1 is what makes it real.
        $sale = $this->createSale($event, $role, [
            'status' => 'unpaid',
            'payment_amount' => 1000,
            'email' => 'buyer@gmail.com',
        ], $ticket);

        $plan = app(InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');
        if ($planAttrs) {
            $plan->update($planAttrs);
        }

        return [$role, $event, $sale->fresh(), $plan->fresh('installments')];
    }

    private function settle(
        SaleInstallment $installment,
        int $rawAmount,
        string $currency = 'usd',
        string $reference = 'pi_test',
        bool $verifiedViaConnect = true,
        ?string $eventAccount = self::MERCHANT,
    ): void {
        $metadata = (object) ['installment_id' => UrlUtils::encodeId($installment->id)];

        $controller = app(\App\Http\Controllers\StripeController::class);
        $method = new \ReflectionMethod($controller, 'handleInstallmentPayment');
        $method->setAccessible(true);
        $method->invoke($controller, $metadata, $rawAmount, $currency, $reference, $verifiedViaConnect, $eventAccount, null);
    }

    private function settlePayoff(
        SaleInstallmentPlan $plan,
        int $rawAmount,
        bool $verifiedViaConnect = true,
        ?string $eventAccount = self::MERCHANT,
    ): void {
        $metadata = (object) ['installment_plan_payoff' => UrlUtils::encodeId($plan->id)];

        $controller = app(\App\Http\Controllers\StripeController::class);
        $method = new \ReflectionMethod($controller, 'handleInstallmentPayment');
        $method->setAccessible(true);
        $method->invoke($controller, $metadata, $rawAmount, 'usd', 'pi_payoff', $verifiedViaConnect, $eventAccount, null);
    }

    public function test_first_installment_marks_the_sale_paid_and_issues_the_ticket(): void
    {
        [, , $sale, $plan] = $this->scaffold();
        $first = $plan->installments->firstWhere('sequence', 1);

        $this->settle($first, 25000);

        $this->assertSame('paid', $first->fresh()->status);
        // This is the whole point of the design: the ticket is real after one payment.
        $this->assertSame('paid', $sale->fresh()->status);
        $this->assertSame('active', $plan->fresh()->status);
        $this->assertEqualsWithDelta(250.00, (float) $plan->fresh()->amount_paid, 0.001);
    }

    /**
     * The failure the whole branch-placement discussion exists to prevent: a 1-of-4 payment
     * reconciled against the full order total would flag the sale and kill a valid ticket.
     */
    public function test_a_partial_payment_never_flags_the_sale_as_amount_mismatch(): void
    {
        [, , $sale, $plan] = $this->scaffold();
        $first = $plan->installments->firstWhere('sequence', 1);

        $this->settle($first, 25000);

        $this->assertNotSame('amount_mismatch', $sale->fresh()->status);
        $this->assertSame('paid', $sale->fresh()->status);
    }

    public function test_replayed_webhook_does_not_double_credit(): void
    {
        [, , , $plan] = $this->scaffold();
        $first = $plan->installments->firstWhere('sequence', 1);

        $this->settle($first, 25000);
        $this->settle($first, 25000);
        $this->settle($first, 25000);

        $this->assertEqualsWithDelta(250.00, (float) $plan->fresh()->amount_paid, 0.001);
    }

    public function test_final_installment_completes_the_plan(): void
    {
        [, , , $plan] = $this->scaffold();

        foreach ($plan->installments->sortBy('sequence') as $installment) {
            $this->settle($installment, 25000, reference: 'pi_'.$installment->sequence);
        }

        $this->assertSame('completed', $plan->fresh()->status);
        $this->assertEqualsWithDelta(1000.00, (float) $plan->fresh()->amount_paid, 0.001);
    }

    /**
     * Paying up must restore the ticket at the door without anyone intervening.
     */
    public function test_paying_clears_delinquency(): void
    {
        [, , , $plan] = $this->scaffold(['status' => 'delinquent', 'delinquent_at' => now()]);
        $first = $plan->installments->firstWhere('sequence', 1);

        $this->settle($first, 25000);

        $this->assertSame('active', $plan->fresh()->status);
        $this->assertNull($plan->fresh()->delinquent_at);
    }

    /**
     * UNDERpayment is refused: the row stays unsettled and is flagged, never credited.
     */
    public function test_an_underpayment_is_refused_and_flagged(): void
    {
        [, , $sale, $plan] = $this->scaffold();
        $sale->update(['status' => 'paid']);
        $second = $plan->installments->firstWhere('sequence', 2);

        $this->settle($second, 10000, reference: 'pi_short');

        $this->assertSame('failed', $second->fresh()->status);
        $this->assertSame('amount_mismatch', $second->fresh()->last_error);

        // The ticket must stay valid: the sale is 1/4 paid and the buyer did nothing wrong.
        $this->assertSame('paid', $sale->fresh()->status);
        $this->assertEqualsWithDelta(0.0, (float) $plan->fresh()->amount_paid, 0.001);
    }

    /**
     * OVERpayment settles the row but credits only what was owed; the surplus is booked as
     * unmatched so it surfaces for reconciliation. Refusing it outright (the old behaviour) left a
     * buyer who had genuinely paid with nothing credited and a plan that could never complete -
     * reachable whenever a scheduled charge lands while a payoff session is open.
     */
    public function test_an_overpayment_settles_the_row_and_records_the_surplus(): void
    {
        [, , $sale, $plan] = $this->scaffold();
        $sale->update(['status' => 'paid']);
        $second = $plan->installments->firstWhere('sequence', 2);

        $this->settle($second, 99900, reference: 'pi_over');

        $this->assertSame('paid', $second->fresh()->status);
        // Credited the 250 owed, not the 999 that arrived.
        $this->assertEqualsWithDelta(250.0, (float) $plan->fresh()->amount_paid, 0.001);
        $this->assertEqualsWithDelta(749.0, (float) $plan->fresh()->unmatched_amount, 0.001);
    }

    /**
     * Pay the whole outstanding balance in one PaymentIntent - the one place where one charge
     * settles N installment rows.
     */
    public function test_payoff_settles_every_remaining_installment(): void
    {
        [, , , $plan] = $this->scaffold();
        $first = $plan->installments->firstWhere('sequence', 1);
        $this->settle($first, 25000);

        $this->settlePayoff($plan->fresh(), 75000);

        $plan = $plan->fresh('installments');
        $this->assertSame('completed', $plan->status);
        $this->assertSame(4, $plan->installments->where('status', 'paid')->count());
        $this->assertEqualsWithDelta(1000.00, (float) $plan->amount_paid, 0.001);
    }

    // ---- Security ----

    public function test_connected_account_mismatch_is_not_credited(): void
    {
        [, , $sale, $plan] = $this->scaffold();
        $first = $plan->installments->firstWhere('sequence', 1);

        // A Connect-verified event, but from somebody else's account.
        $this->settle($first, 25000, eventAccount: 'acct_attacker');

        $this->assertSame('processing', $first->fresh()->status);
        $this->assertEqualsWithDelta(0.0, (float) $plan->fresh()->amount_paid, 0.001);
        $this->assertSame('unpaid', $sale->fresh()->status);
    }

    public function test_platform_key_on_a_connect_plan_is_not_credited(): void
    {
        [, , , $plan] = $this->scaffold();
        $first = $plan->installments->firstWhere('sequence', 1);

        // The other direction: a Connect merchant's object confirmed by the platform secret.
        $this->settle($first, 25000, verifiedViaConnect: false, eventAccount: null);

        $this->assertEqualsWithDelta(0.0, (float) $plan->fresh()->amount_paid, 0.001);
    }

    public function test_currency_mismatch_is_not_credited(): void
    {
        [, , , $plan] = $this->scaffold();
        $first = $plan->installments->firstWhere('sequence', 1);

        $this->settle($first, 25000, currency: 'eur');

        $this->assertEqualsWithDelta(0.0, (float) $plan->fresh()->amount_paid, 0.001);
        $this->assertSame('processing', $first->fresh()->status);
    }

    /**
     * A cancelled plan is the state every teardown path produces. A late webhook arriving after a
     * refund must not resurrect it.
     */
    public function test_a_cancelled_plan_ignores_a_late_payment(): void
    {
        [, , , $plan] = $this->scaffold();
        $first = $plan->installments->firstWhere('sequence', 1);

        app(InstallmentService::class)->cancelPlan($plan, 'sale_refunded');

        $this->settle($first, 25000);

        $this->assertSame('cancelled', $plan->fresh()->status);
        $this->assertEqualsWithDelta(0.0, (float) $plan->fresh()->amount_paid, 0.001);
    }
}
