<?php

namespace Tests\Feature;

use App\Jobs\SendWebhook;
use App\Models\Webhook;
use App\Services\InstallmentService;
use App\Services\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Webhook dispatches that carry $extraData.
 *
 * These are the first tests in the repo to configure an actual subscriber, and that omission is
 * why a live bug survived: `WebhookService::dispatch()` bails at `$webhooks->isEmpty()` long before
 * it builds the payload, so every existing test returned early and the payload-building code was
 * never executed. `array_merge()` was being handed the stdClass from `Sale::toApiData()`, which is
 * a TypeError on PHP 8 - so any dispatch with $extraData threw, for real users only.
 *
 * On the installment path the throw landed *after* the payment had committed and was swallowed by
 * the cron's catch, which rewrote the settled row as `awaiting_reconciliation`: money taken, and
 * the buyer's ticket put on hold a week later.
 */
class WebhookExtraDataTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array{0: \App\Models\Sale, 1: \App\Models\Webhook, 2: \App\Models\Event} */
    private function subscribedSale(array $eventTypes): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, ['payment_method' => 'stripe']);
        $ticket = $this->createTicket($event, ['price' => 100, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 100], $ticket);

        $webhook = Webhook::create([
            'user_id' => $owner->id,
            'url' => 'https://example.test/hook',
            'secret' => 'shh',
            'event_types' => $eventTypes,
            'is_active' => true,
        ]);

        return [$sale, $webhook, $event];
    }

    /**
     * The regression itself. Without the cast this throws a TypeError rather than dispatching.
     */
    public function test_a_dispatch_carrying_extra_data_does_not_throw(): void
    {
        Bus::fake();
        [$sale] = $this->subscribedSale(['installment.paid']);

        WebhookService::dispatch('installment.paid', $sale, null, [
            'installment' => ['sequence' => 2, 'amount' => 250.0],
        ]);

        Bus::assertDispatched(SendWebhook::class);
    }

    /**
     * The extras have to actually arrive, not merely not-crash.
     */
    public function test_the_extra_data_reaches_the_payload(): void
    {
        Bus::fake();
        [$sale] = $this->subscribedSale(['installment.paid']);

        WebhookService::dispatch('installment.paid', $sale, null, [
            'installment' => ['sequence' => 2, 'amount' => 250.0, 'outcome' => 'settled'],
        ]);

        Bus::assertDispatched(SendWebhook::class, function ($job) {
            $ref = new \ReflectionProperty($job, 'payload');
            $ref->setAccessible(true);
            $payload = $ref->getValue($job);

            $data = (array) $payload['data'];

            // The sale's own fields survive the merge...
            $this->assertArrayHasKey('name', $data);
            // ...and the installment detail is present.
            $this->assertSame(2, $data['installment']['sequence']);
            $this->assertSame('settled', $data['installment']['outcome']);

            return true;
        });
    }

    /**
     * A settled installment must reach `paid` even with a subscriber configured. Before the fix the
     * dispatch threw after the money had moved, and the cron's catch rewrote the row.
     */
    public function test_a_subscriber_does_not_break_installment_settlement(): void
    {
        Bus::fake();

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

        Webhook::create([
            'user_id' => $owner->id,
            'url' => 'https://example.test/hook',
            'secret' => 'shh',
            'event_types' => ['installment.paid'],
            'is_active' => true,
        ]);

        $plan = app(InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');
        $second = $plan->installments->firstWhere('sequence', 2);

        $outcome = app(InstallmentService::class)->settle($plan, $second, false, 250.00, 'pi_ok');

        $this->assertSame('settled', $outcome);
        $this->assertSame('paid', $second->fresh()->status);
    }

    /**
     * `installment.failed` has to fire on a declined card, which is what its name promises. Before
     * this it only fired from settle(), when a payment *arrived* and could not be applied - so an
     * integrator subscribing to it to chase a failing card never heard about one.
     */
    public function test_a_declined_card_fires_installment_failed(): void
    {
        Bus::fake();

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

        Webhook::create([
            'user_id' => $owner->id,
            'url' => 'https://example.test/hook',
            'secret' => 'shh',
            'event_types' => ['installment.failed'],
            'is_active' => true,
        ]);

        $plan = app(InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');
        $second = $plan->installments->firstWhere('sequence', 2);

        $command = app(\App\Console\Commands\ChargeInstallments::class);
        $method = new \ReflectionMethod($command, 'handleCardFailure');
        $method->setAccessible(true);

        $error = new \Stripe\ErrorObject;
        $error->updateAttributes(['code' => 'card_declined']);
        $exception = new \Stripe\Exception\CardException('declined');
        $exception->setError($error);

        $method->invoke($command, $second, $plan->fresh('installments'), $exception);

        Bus::assertDispatched(SendWebhook::class, function ($job) {
            $ref = new \ReflectionProperty($job, 'payload');
            $ref->setAccessible(true);
            $payload = $ref->getValue($job);

            $this->assertSame('installment.failed', $payload['event']);

            $data = (array) $payload['data'];
            $this->assertSame('declined', $data['installment']['outcome']);
            $this->assertSame('card_declined', $data['installment']['error']);
            $this->assertSame(2, $data['installment']['sequence']);
            $this->assertFalse($data['installment']['is_final'], 'The first decline is not the last');
            $this->assertNotNull($data['installment']['next_attempt_at']);

            return true;
        });
    }

    /**
     * The same shape the pre-existing pass-booking dispatches use, so the fix is pinned for them
     * too rather than only for installments.
     */
    public function test_a_ticket_booked_dispatch_with_extras_survives(): void
    {
        Bus::fake();
        [$sale] = $this->subscribedSale(['ticket.booked']);

        WebhookService::dispatch('ticket.booked', $sale, null, [
            'booking' => ['event_id' => 1, 'date' => '2026-09-01'],
        ]);

        Bus::assertDispatched(SendWebhook::class);
    }
}
