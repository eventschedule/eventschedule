<?php

namespace Tests\Feature;

use App\Mail\InstallmentAuthenticationRequired;
use App\Mail\InstallmentFailed;
use App\Mail\InstallmentFinalNotice;
use App\Mail\InstallmentOnHold;
use App\Mail\InstallmentOrganizerDigest;
use App\Mail\InstallmentReminder;
use App\Models\Event;
use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\SaleInstallmentPlan;
use App\Services\InstallmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * app:charge-installments.
 *
 * The Stripe call itself is not exercised here (that needs the API); what is exercised is
 * everything around it that decides WHETHER to call, how often, and what the buyer is told -
 * which is where the damage would be. Emails are asserted through the queued-job bus, since
 * every send goes out via SendQueuedEmail.
 */
class ChargeInstallmentsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array{0: Event, 1: Sale, 2: SaleInstallmentPlan, 3: \App\Models\Role} */
    private function scaffold(array $eventAttrs = []): array
    {
        $owner = $this->createOwner();
        $owner->stripe_account_id = 'acct_merchant';
        $owner->save();

        $role = $this->createRole($owner, 'venue', ['email_settings' => [
            'host' => 'smtp.test', 'username' => 'u', 'password' => 'p',
            'port' => 587, 'from_address' => 'sched@gmail.com', 'from_name' => 'Sched',
        ]]);

        $event = $this->createEvent($role, array_merge([
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 4,
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ], $eventAttrs));

        $ticket = $this->createTicket($event, ['price' => 1000, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, [
            'status' => 'paid', 'payment_amount' => 1000, 'email' => 'buyer@gmail.com',
        ], $ticket);

        $plan = app(InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');
        // Pretend installment 1 settled and left a card on file.
        $plan->update([
            'amount_paid' => 250,
            'stripe_customer_id' => 'cus_x',
            'stripe_payment_method_id' => 'pm_x',
            'card_brand' => 'visa',
            'card_last4' => '4242',
        ]);
        $plan->installments->firstWhere('sequence', 1)->update(['status' => 'paid', 'paid_at' => now()]);

        return [$event, $sale, $plan->fresh('installments'), $role];
    }

    private function dispatchedMailables(): array
    {
        $out = [];
        foreach (Bus::dispatched(\App\Jobs\SendQueuedEmail::class) as $job) {
            $ref = new \ReflectionProperty($job, 'mailable');
            $ref->setAccessible(true);
            $out[] = get_class($ref->getValue($job));
        }

        return $out;
    }

    public function test_reminder_goes_to_the_buyer_and_a_digest_to_the_organizer(): void
    {
        Bus::fake();
        [, , $plan] = $this->scaffold();

        // Installment 2 falls inside the two-day reminder window.
        $second = $plan->installments->firstWhere('sequence', 2);
        $second->update(['due_at' => now()->addDay(), 'status' => 'scheduled']);

        $this->artisan('app:charge-installments')->assertSuccessful();

        $classes = $this->dispatchedMailables();
        $this->assertContains(InstallmentReminder::class, $classes);
        $this->assertContains(InstallmentOrganizerDigest::class, $classes);

        $this->assertNotNull($second->fresh()->reminder_sent_at);
    }

    /**
     * The reason the organizer digest exists: forty seats on four-part plans would otherwise be
     * forty emails in one morning. Many buyers, ONE digest.
     */
    public function test_many_buyers_produce_one_organizer_digest(): void
    {
        Bus::fake();
        [$event, , $plan, $role] = $this->scaffold();

        $plan->installments->firstWhere('sequence', 2)->update(['due_at' => now()->addDay()]);

        // Two more buyers on the same schedule, each also due tomorrow.
        for ($i = 0; $i < 2; $i++) {
            $ticket = $this->createTicket($event, ['price' => 1000, 'quantity' => 10]);
            $sale = $this->createSale($event, $role, [
                'status' => 'paid', 'payment_amount' => 1000, 'email' => "buyer{$i}@gmail.com",
            ], $ticket);
            $p = app(InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');
            // A stored card is what distinguishes a real buyer from an abandoned checkout - the
            // reminder pass now requires one, so these fixtures must have completed payment.
            $p->update(['stripe_customer_id' => 'cus_x', 'stripe_payment_method_id' => 'pm_x']);
            $p->installments->firstWhere('sequence', 1)->update(['status' => 'paid']);
            $p->installments->firstWhere('sequence', 2)->update(['due_at' => now()->addDay()]);
        }

        $this->artisan('app:charge-installments')->assertSuccessful();

        $classes = $this->dispatchedMailables();
        $this->assertSame(3, count(array_filter($classes, fn ($c) => $c === InstallmentReminder::class)));
        $this->assertSame(1, count(array_filter($classes, fn ($c) => $c === InstallmentOrganizerDigest::class)));
    }

    /**
     * An abandoned Stripe checkout leaves an `active` plan on a sale that was never paid. Dunning
     * that buyer - "your payment of X is due in 2 days" - is chasing someone who never bought
     * anything, and the pre-event sweep would follow it with a final notice.
     */
    public function test_an_abandoned_checkout_is_never_dunned(): void
    {
        Bus::fake();
        [, , $plan] = $this->scaffold();

        // No stored card is the signature of a checkout the buyer walked away from.
        $plan->update(['stripe_customer_id' => null, 'stripe_payment_method_id' => null]);
        $plan->installments->firstWhere('sequence', 2)->update(['due_at' => now()->addDay()]);

        $this->artisan('app:charge-installments')->assertSuccessful();

        $this->assertEmpty($this->dispatchedMailables());
    }

    public function test_reminder_is_not_sent_twice(): void
    {
        Bus::fake();
        [, , $plan] = $this->scaffold();
        $plan->installments->firstWhere('sequence', 2)->update(['due_at' => now()->addDay()]);

        $this->artisan('app:charge-installments')->assertSuccessful();
        $this->artisan('app:charge-installments')->assertSuccessful();
        $this->artisan('app:charge-installments')->assertSuccessful();

        $classes = $this->dispatchedMailables();
        $this->assertSame(1, count(array_filter($classes, fn ($c) => $c === InstallmentReminder::class)));
    }

    /**
     * The selector must respect next_attempt_at. Without it an hourly command retries a declined
     * card 24 times a day - a card-testing pattern.
     */
    public function test_a_backed_off_installment_is_not_retried_early(): void
    {
        Bus::fake();
        [, , $plan] = $this->scaffold();

        $second = $plan->installments->firstWhere('sequence', 2);
        $second->update([
            'due_at' => now()->subDay(),
            'status' => 'scheduled',
            'attempts' => 1,
            'next_attempt_at' => now()->addDays(3),
        ]);

        // Asserting on the status alone would prove nothing: --dry-run claims and then releases,
        // so the row reads `scheduled` with one attempt whether or not the selector matched it.
        // The only observable difference is whether the command considered it at all.
        $this->artisan('app:charge-installments --dry-run')
            ->doesntExpectOutputToContain('would charge installment')
            ->assertSuccessful();

        $this->assertSame('scheduled', $second->fresh()->status);
        $this->assertSame(1, $second->fresh()->attempts);
    }

    public function test_a_due_installment_past_its_backoff_is_claimed(): void
    {
        Bus::fake();
        [, , $plan] = $this->scaffold();

        $second = $plan->installments->firstWhere('sequence', 2);
        $second->update([
            'due_at' => now()->subDay(),
            'status' => 'scheduled',
            'attempts' => 1,
            'next_attempt_at' => now()->subHour(),
        ]);

        // --dry-run claims then releases, which is enough to prove the selector matched.
        $this->artisan('app:charge-installments --dry-run')
            ->expectsOutputToContain('would charge installment')
            ->assertSuccessful();
    }

    /**
     * No stored card means nothing to charge off-session. Must not throw, must not claim.
     */
    public function test_a_plan_without_a_stored_card_is_skipped(): void
    {
        Bus::fake();
        [, , $plan] = $this->scaffold();
        $plan->update(['stripe_customer_id' => null, 'stripe_payment_method_id' => null]);

        $second = $plan->installments->firstWhere('sequence', 2);
        $second->update(['due_at' => now()->subDay(), 'status' => 'scheduled']);

        $this->artisan('app:charge-installments')->assertSuccessful();

        $this->assertSame('scheduled', $second->fresh()->status);
    }

    /**
     * A cancelled plan is what every teardown path produces. The cron must never touch one.
     */
    public function test_a_cancelled_plan_is_never_charged_or_emailed(): void
    {
        Bus::fake();
        [, , $plan] = $this->scaffold();
        $plan->installments->firstWhere('sequence', 2)->update(['due_at' => now()->addDay()]);

        app(InstallmentService::class)->cancelPlan($plan, 'sale_refunded');

        $this->artisan('app:charge-installments')->assertSuccessful();

        $this->assertEmpty($this->dispatchedMailables());
    }

    /**
     * The backstop: a week out, anyone with a balance hears about it whatever their retry state.
     */
    public function test_pre_event_sweep_warns_buyer_and_organizer(): void
    {
        Bus::fake();
        [, , $plan] = $this->scaffold(['starts_at' => now()->addDays(5)->setTime(12, 0)->format('Y-m-d H:i:s')]);

        $this->artisan('app:charge-installments')->assertSuccessful();

        $classes = $this->dispatchedMailables();
        $this->assertContains(InstallmentFinalNotice::class, $classes);
        $this->assertContains(InstallmentOrganizerDigest::class, $classes);
    }

    public function test_pre_event_sweep_does_not_repeat(): void
    {
        Bus::fake();
        [, , $plan] = $this->scaffold(['starts_at' => now()->addDays(5)->setTime(12, 0)->format('Y-m-d H:i:s')]);

        $this->artisan('app:charge-installments')->assertSuccessful();
        $this->artisan('app:charge-installments')->assertSuccessful();

        $classes = $this->dispatchedMailables();
        $this->assertSame(1, count(array_filter($classes, fn ($c) => $c === InstallmentFinalNotice::class)));
    }

    public function test_a_fully_paid_plan_gets_no_pre_event_notice(): void
    {
        Bus::fake();
        [, , $plan] = $this->scaffold(['starts_at' => now()->addDays(5)->setTime(12, 0)->format('Y-m-d H:i:s')]);
        $plan->installments()->update(['status' => 'paid', 'paid_at' => now()]);

        $this->artisan('app:charge-installments')->assertSuccessful();

        $this->assertNotContains(InstallmentFinalNotice::class, $this->dispatchedMailables());
    }

    /**
     * The retry ladder and the SCA exemption, driven directly through the failure handler so the
     * behaviour is pinned without needing Stripe.
     */
    public function test_sca_parks_the_installment_without_burning_an_attempt(): void
    {
        Bus::fake();
        [, , $plan] = $this->scaffold();
        $second = $plan->installments->firstWhere('sequence', 2);

        $this->invokeCardFailure($second, $plan, 'authentication_required');

        $second->refresh();
        // Parked for the buyer to act on, NOT counted as a decline.
        $this->assertSame('awaiting_customer', $second->status);
        $this->assertSame(0, $second->attempts);
        $this->assertNull($second->next_attempt_at);
        $this->assertSame('active', $plan->fresh()->status);

        $this->assertContains(InstallmentAuthenticationRequired::class, $this->dispatchedMailables());
    }

    public function test_a_decline_backs_off_and_eventually_puts_the_plan_on_hold(): void
    {
        Bus::fake();
        [, , $plan] = $this->scaffold();
        $second = $plan->installments->firstWhere('sequence', 2);

        $this->invokeCardFailure($second, $plan, 'card_declined');
        $second->refresh();
        $this->assertSame('scheduled', $second->status);
        $this->assertSame(1, $second->attempts);
        $this->assertNotNull($second->next_attempt_at);
        $this->assertSame('active', $plan->fresh()->status);
        $this->assertContains(InstallmentFailed::class, $this->dispatchedMailables());

        $this->invokeCardFailure($second->fresh(), $plan, 'card_declined');
        $this->assertSame(2, $second->fresh()->attempts);
        $this->assertSame('active', $plan->fresh()->status);

        $this->invokeCardFailure($second->fresh(), $plan, 'card_declined');
        $this->assertSame(3, $second->fresh()->attempts);
        $this->assertSame('active', $plan->fresh()->status, 'The third attempt still has a retry left');

        // Fourth and final attempt - day nine. The ticket stops scanning.
        $this->invokeCardFailure($second->fresh(), $plan, 'card_declined');
        $second->refresh();
        $this->assertSame('failed', $second->status);
        $this->assertSame('delinquent', $plan->fresh()->status);
        $this->assertNotNull($plan->fresh()->delinquent_at);
        $this->assertContains(InstallmentOnHold::class, $this->dispatchedMailables());
    }

    private function invokeCardFailure(SaleInstallment $installment, SaleInstallmentPlan $plan, string $code): void
    {
        $command = app(\App\Console\Commands\ChargeInstallments::class);
        $method = new \ReflectionMethod($command, 'handleCardFailure');
        $method->setAccessible(true);

        $error = new \Stripe\ErrorObject;
        $error->updateAttributes(['code' => $code]);
        $exception = new \Stripe\Exception\CardException('declined');
        $exception->setError($error);

        $method->invoke($command, $installment, $plan->fresh('installments'), $exception);
    }
}
