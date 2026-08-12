<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\InstallmentAuthenticationRequired;
use App\Models\SaleInstallment;
use App\Models\SaleInstallmentPlan;
use App\Services\InstallmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The cron-to-settlement handoff.
 *
 * This is the seam every serious bug in this feature lived in, and it was the one thing the
 * original suite never crossed: ChargeInstallmentsTest ran the command 11 times and reached the
 * settlement handler 0 times, InstallmentWebhookTest called the handler directly and never ran the
 * command, and neither touched a Stripe client. Both halves passed; the join was untested.
 *
 * `chargeOffSession()` exists as a one-call seam precisely so these can be written.
 */
class InstallmentChargeSettlementTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array{0: SaleInstallmentPlan, 1: SaleInstallment} */
    private function planDueForCharge(array $planAttrs = []): array
    {
        $owner = $this->createOwner();
        $owner->stripe_account_id = 'acct_merchant';
        $owner->save();

        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 4,
            'starts_at' => now()->addMonths(8)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $ticket = $this->createTicket($event, ['price' => 1000, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, [
            'status' => 'paid', 'payment_amount' => 1000, 'email' => 'buyer@gmail.com',
        ], $ticket);

        $plan = app(InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');
        $plan->update(array_merge([
            'amount_paid' => 250,
            'stripe_customer_id' => 'cus_x',
            'stripe_payment_method_id' => 'pm_x',
        ], $planAttrs));
        $plan->installments->firstWhere('sequence', 1)->update(['status' => 'paid', 'paid_at' => now()]);

        $second = $plan->installments->firstWhere('sequence', 2);
        $second->update(['due_at' => now()->subDay(), 'status' => 'scheduled']);

        return [$plan->fresh('installments'), $second->fresh()];
    }

    /**
     * A real PaymentIntent shaped like the one `confirm: true` returns.
     *
     * Constructed properly rather than as a stdClass so it satisfies chargeOffSession()'s declared
     * return type - the fake stays faithful to the contract the production code relies on.
     */
    private function fakeIntent(string $id = 'pi_ok', string $status = 'succeeded'): \Stripe\PaymentIntent
    {
        return \Stripe\PaymentIntent::constructFrom([
            'id' => $id,
            'status' => $status,
            'customer' => 'cus_x',
            'payment_method' => 'pm_x',
        ]);
    }

    private function fakeStripe(\Closure $expectation): void
    {
        $this->partialMock(InstallmentService::class, $expectation);
    }

    /**
     * The core guarantee: the command alone settles the payment. No webhook.
     *
     * Both Stripe webhook secrets are optional env vars, so an install can legitimately have none
     * configured - and until this fix the webhook was the only thing in the codebase that could
     * mark an installment paid.
     */
    public function test_a_successful_charge_is_settled_by_the_command_alone(): void
    {
        [$plan, $second] = $this->planDueForCharge();

        $this->fakeStripe(function ($mock) {
            $mock->shouldReceive('chargeOffSession')->once()->andReturn($this->fakeIntent());
        });

        $this->artisan('app:charge-installments')->assertSuccessful();

        $this->assertSame('paid', $second->fresh()->status, 'The command must settle its own charge');
        $this->assertSame('pi_ok', $second->fresh()->transaction_reference);
        $this->assertEqualsWithDelta(500.0, (float) $plan->fresh()->amount_paid, 0.001);
    }

    /**
     * The duplicate-charge path: a settled row must not be re-presented to Stripe on the next run.
     */
    public function test_a_settled_installment_is_not_charged_again(): void
    {
        [, $second] = $this->planDueForCharge();

        $this->fakeStripe(function ($mock) {
            // ONCE across both runs. Before the fix the row went back to `scheduled` with a null
            // next_attempt_at, so every hourly run re-presented the same charge - deduped by
            // Stripe's idempotency key only until it expired, then charged for real.
            $mock->shouldReceive('chargeOffSession')->once()->andReturn($this->fakeIntent());
        });

        $this->artisan('app:charge-installments')->assertSuccessful();
        $this->artisan('app:charge-installments')->assertSuccessful();

        $this->assertSame('paid', $second->fresh()->status);
    }

    /**
     * A lost response is not a licence to retry. Stripe may have taken the money.
     */
    public function test_an_unknown_outcome_is_parked_for_reconciliation_not_retried(): void
    {
        [, $second] = $this->planDueForCharge();

        $this->fakeStripe(function ($mock) {
            $mock->shouldReceive('chargeOffSession')->once()
                ->andThrow(new \Stripe\Exception\ApiConnectionException('timeout'));
        });

        $this->artisan('app:charge-installments')->assertSuccessful();

        $second->refresh();
        $this->assertSame('awaiting_reconciliation', $second->status);
        $this->assertNull($second->next_attempt_at, 'An unknown outcome must not be auto-retried');
    }

    /**
     * A `requires_action` intent is the buyer's move, so the buyer has to be told to make it. The
     * identical outcome arriving as a CardException already emailed them; arriving as an intent
     * status parked in silence, and the buyer's first news of it was their ticket going on hold a
     * week later for a payment nobody had asked them to approve.
     */
    public function test_an_intent_needing_customer_action_emails_the_buyer(): void
    {
        Bus::fake();
        [, $second] = $this->planDueForCharge();

        $this->fakeStripe(function ($mock) {
            $mock->shouldReceive('chargeOffSession')->once()
                ->andReturn($this->fakeIntent('pi_sca', 'requires_action'));
        });

        $this->artisan('app:charge-installments')->assertSuccessful();

        $second->refresh();
        $this->assertSame('awaiting_customer', $second->status);
        $this->assertSame('requires_action', $second->last_error);
        // Not a decline: the ladder must not be consumed by something the card never refused.
        $this->assertSame(0, $second->attempts);

        $mailables = [];
        foreach (Bus::dispatched(SendQueuedEmail::class) as $job) {
            $ref = new \ReflectionProperty($job, 'mailable');
            $ref->setAccessible(true);
            $mailables[] = get_class($ref->getValue($job));
        }

        $this->assertContains(InstallmentAuthenticationRequired::class, $mailables);
    }

    /**
     * The idempotency key must be stable for one intended charge, and must not depend on
     * `attempts` - which does not increment on success.
     */
    public function test_the_idempotency_key_is_stable_across_attempts(): void
    {
        [, $second] = $this->planDueForCharge();
        $service = app(InstallmentService::class);

        $first = $service->idempotencyKeyFor($second);
        $second->update(['attempts' => 2]);

        $this->assertSame($first, $service->idempotencyKeyFor($second->fresh()));
    }

    /**
     * Selfhost has no Connect key and no connected account. The cron used to hardcode the Connect
     * key and pass `stripe_account` regardless, so every charge after the first threw and parked.
     */
    public function test_selfhost_uses_the_platform_key_and_no_connected_account(): void
    {
        config(['app.hosted' => false]);
        [$plan] = $this->planDueForCharge(['stripe_account_id' => null]);

        [$client, $options] = app(InstallmentService::class)->stripeContextFor($plan->fresh());

        $this->assertSame([], $options, 'Selfhost must not send stripe_account');

        // The key, not just the options. `assertNotNull($client)` used to stand in for this, which
        // could not fail - `new` does not return null - so the two rails could have been swapped
        // and this test, named for that exact regression, would still have passed.
        $this->assertSame(config('services.stripe_platform.secret'), $client->getApiKey(),
            'Selfhost has no Connect key, so it must charge on the platform secret');
    }

    public function test_hosted_connect_uses_the_connected_account(): void
    {
        config(['app.hosted' => true]);
        [$plan] = $this->planDueForCharge();

        [$client, $options] = app(InstallmentService::class)->stripeContextFor($plan->fresh());

        $this->assertSame(['stripe_account' => 'acct_merchant'], $options);
        $this->assertSame(config('services.stripe.key'), $client->getApiKey(),
            'A Connect plan must charge on the Connect key, not the platform secret');
    }

    /**
     * The card-capture race: whichever Stripe event settles first must store the card, or the plan
     * becomes permanently unchargeable and the organizer collects one instalment of four.
     */
    public function test_card_details_are_captured_on_settlement(): void
    {
        [$plan, $second] = $this->planDueForCharge([
            'stripe_customer_id' => null,
            'stripe_payment_method_id' => null,
        ]);

        // No stored card yet, so the cron skips it - settle directly, as the webhook would.
        app(InstallmentService::class)->settle(
            $plan, $second, false, 250.00, 'pi_first', $this->fakeIntent('pi_first')
        );

        $plan->refresh();
        $this->assertSame('cus_x', $plan->stripe_customer_id);
        $this->assertSame('pm_x', $plan->stripe_payment_method_id);
    }

    /**
     * A replay is a no-op; a genuinely different PaymentIntent for an already-paid row is money
     * the buyer has paid twice, and must be recorded rather than silently dropped.
     */
    public function test_a_replay_is_ignored_but_a_second_payment_is_recorded(): void
    {
        [$plan, $second] = $this->planDueForCharge();
        $service = app(InstallmentService::class);

        $this->assertSame('settled', $service->settle($plan, $second, false, 250.00, 'pi_one'));
        $this->assertSame('already_settled', $service->settle($plan->fresh(), $second->fresh(), false, 250.00, 'pi_one'));

        $this->assertSame('duplicate', $service->settle($plan->fresh(), $second->fresh(), false, 250.00, 'pi_TWO'));
        $this->assertEqualsWithDelta(250.0, (float) $plan->fresh()->unmatched_amount, 0.001);
    }

    /**
     * A payment landing after the plan died is still the buyer's money.
     */
    public function test_a_payment_on_a_dead_plan_is_recorded_for_reconciliation(): void
    {
        [$plan, $second] = $this->planDueForCharge();
        app(InstallmentService::class)->cancelPlan($plan, 'sale_refunded');

        $outcome = app(InstallmentService::class)->settle($plan->fresh(), $second->fresh(), false, 250.00, 'pi_late');

        $this->assertSame('dead_plan', $outcome);
        $this->assertEqualsWithDelta(250.0, (float) $plan->fresh()->unmatched_amount, 0.001);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
