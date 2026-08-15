<?php

namespace Tests\Feature;

use App\Models\SaleInstallment;
use App\Models\SaleInstallmentPlan;
use App\Services\InstallmentService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * How a plan comes to hold the card its remaining installments are charged against.
 *
 * This is the feature's single point of failure and it had no coverage at all: nothing else writes
 * stripe_customer_id / stripe_payment_method_id from a purchase, and every surface that would have
 * complained about their absence filters on them too - ChargeInstallments::chargeDue() skips the
 * plan, remindUpcoming() and sweepBeforeEvents() both require them - so an install that never
 * captured a card collected installment 1, stopped, and told nobody.
 *
 * The cases below pin the three ways that happened: the capture running after settle()'s early
 * returns, the checkout.session.completed rail carrying no PaymentIntent to capture from, and the
 * card swap depending on a webhook our own Connect setup docs tell operators not to subscribe to.
 */
class InstallmentCardCaptureTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const MERCHANT = 'acct_merchant';

    /** @return array{0: SaleInstallmentPlan, 1: SaleInstallment} */
    private function planAwaitingFirstPayment(): array
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
        $sale = $this->createSale($event, $role, [
            'status' => 'unpaid',
            'payment_amount' => 1000,
            'email' => 'buyer@gmail.com',
        ], $ticket);

        $plan = app(InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');

        return [$plan->fresh('installments'), $plan->installments->firstWhere('sequence', 1)];
    }

    /**
     * A PaymentIntent shaped exactly as Stripe sends one today.
     *
     * Note what is NOT here: `charges`, and `payment_method_details`. The capture used to read
     * `$intent->charges->data[0]->payment_method_details->card`, but `charges` was removed from
     * PaymentIntent in the 2022-11-15 API version and stripe-php v16 does not declare it - so that
     * chain silently resolved to null on every rail and the card columns were never written.
     */
    private function fakeIntent(string $id = 'pi_live'): \Stripe\PaymentIntent
    {
        return \Stripe\PaymentIntent::constructFrom([
            'id' => $id,
            'status' => 'succeeded',
            'customer' => 'cus_live',
            'payment_method' => 'pm_live',
            'latest_charge' => 'ch_live',
        ]);
    }

    private function fakeMethod(): \Stripe\PaymentMethod
    {
        return \Stripe\PaymentMethod::constructFrom([
            'id' => 'pm_live',
            'card' => ['brand' => 'visa', 'last4' => '4242', 'exp_month' => 12, 'exp_year' => 2030],
        ]);
    }

    private function fakeStripe(\Closure $expectation): void
    {
        $this->partialMock(InstallmentService::class, $expectation);
    }

    /** Drive the webhook handler the way StripeController's two branches do. */
    private function webhook(SaleInstallment $installment, ?\Stripe\PaymentIntent $intent, string $reference = 'pi_live'): void
    {
        $controller = app(\App\Http\Controllers\StripeController::class);
        $method = new \ReflectionMethod($controller, 'handleInstallmentPayment');
        $method->setAccessible(true);
        $method->invoke(
            $controller,
            (object) ['installment_id' => UrlUtils::encodeId($installment->id)],
            25000,
            'usd',
            $reference,
            true,
            self::MERCHANT,
            $intent,
        );
    }

    private function planUrl(SaleInstallmentPlan $plan, string $query = ''): string
    {
        return route('installment.view', [
            'plan_id' => UrlUtils::encodeId($plan->id),
            'secret' => $plan->secret,
        ]).$query;
    }

    /**
     * The race that cost the card: `checkout.session.completed` and `payment_intent.succeeded`
     * both report the same purchase, and only the second carries the intent.
     *
     * When the session event settled the row first, the payment-intent event arrived to find it
     * already paid and returned early - above the capture - so the card was never stored. The
     * comment in settle() claimed the capture ran before any early return; the code had it after.
     */
    public function test_the_card_is_stored_when_the_session_event_settles_first(): void
    {
        [$plan, $first] = $this->planAwaitingFirstPayment();

        $this->fakeStripe(function ($mock) {
            // The session rail cannot read the intent this time - a transient Stripe failure - so
            // the row settles carrying no card at all.
            $mock->shouldReceive('retrievePaymentIntent')->once()->andReturnNull();
            $mock->shouldReceive('retrievePaymentMethod')->andReturn($this->fakeMethod());
        });

        $this->webhook($first, null);

        $this->assertSame('paid', $first->fresh()->status, 'precondition: the session event settled the row');
        $this->assertNull($plan->fresh()->stripe_payment_method_id, 'precondition: it settled with no card');

        // Now payment_intent.succeeded arrives, carrying the intent, and finds the row settled.
        $this->webhook($first->fresh(), $this->fakeIntent());

        $plan->refresh();
        $this->assertSame('pm_live', $plan->stripe_payment_method_id);
        $this->assertSame('cus_live', $plan->stripe_customer_id);
    }

    /**
     * The selfhost rail, which our docs tell operators to point at `checkout.session.completed`
     * alone. That event carries `payment_intent` as a bare id, so the capture had nothing to read
     * and every payment plan on such an install collected exactly one installment.
     */
    public function test_the_session_rail_stores_the_card_with_no_payment_intent_event(): void
    {
        [$plan, $first] = $this->planAwaitingFirstPayment();

        $this->fakeStripe(function ($mock) {
            $mock->shouldReceive('retrievePaymentIntent')
                ->once()
                ->withArgs(fn ($p, $id) => $id === 'pi_from_session')
                ->andReturn($this->fakeIntent('pi_from_session'));
            $mock->shouldReceive('retrievePaymentMethod')->once()->andReturn($this->fakeMethod());
        });

        $this->webhook($first, null, 'pi_from_session');

        $plan->refresh();
        $this->assertSame('pm_live', $plan->stripe_payment_method_id, 'the session rail must fetch what it was not given');
        $this->assertSame('4242', $plan->card_last4);
    }

    /** A session id is not a payment intent id, and must not be handed to Stripe as one. */
    public function test_a_session_without_an_intent_is_not_looked_up(): void
    {
        [, $first] = $this->planAwaitingFirstPayment();

        $this->fakeStripe(function ($mock) {
            $mock->shouldNotReceive('retrievePaymentIntent');
            $mock->shouldReceive('retrievePaymentMethod')->andReturn($this->fakeMethod());
        });

        $this->webhook($first, null, 'cs_test_session');

        $this->assertSame('paid', $first->fresh()->status, 'the money is still credited');
    }

    /**
     * Brand / last4 / expiry come off the PaymentMethod. Reading them from the PaymentIntent found
     * nothing, which disabled cardExpiresBeforeFinalPayment() - the one future failure this
     * feature can see coming - and left every email saying "your card" rather than naming it.
     */
    public function test_the_card_display_comes_from_the_payment_method(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();

        $this->fakeStripe(function ($mock) {
            $mock->shouldReceive('retrievePaymentMethod')->once()->andReturn($this->fakeMethod());
        });

        app(InstallmentService::class)->captureFrom($plan, $this->fakeIntent());

        $plan->refresh();
        $this->assertSame('visa', $plan->card_brand);
        $this->assertSame('4242', $plan->card_last4);
        $this->assertSame(12, $plan->card_exp_month);
        $this->assertSame(2030, $plan->card_exp_year);
    }

    /**
     * "A card was stored" has to mean the card changed, not that the model was dirty. That answer
     * is what gates the buyer's success banner, so a bare isDirty() would let an unrelated pending
     * change confirm a swap that never happened.
     */
    public function test_an_unrelated_pending_change_is_not_a_stored_card(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();
        $plan->update([
            'stripe_customer_id' => 'cus_live',
            'stripe_payment_method_id' => 'pm_live',
            'card_last4' => '4242',
        ]);

        $this->fakeStripe(function ($mock) {
            $mock->shouldNotReceive('retrievePaymentMethod');
        });

        // Dirty, but not in a way that concerns the card.
        $plan->status = 'delinquent';

        $this->assertFalse(app(InstallmentService::class)->captureFrom($plan, $this->fakeIntent()));
    }

    /** Nothing will ever charge a cancelled plan, so its card is not worth an API call. */
    public function test_a_cancelled_plan_does_not_buy_a_card_lookup(): void
    {
        [$plan, $first] = $this->planAwaitingFirstPayment();
        $plan->update(['status' => 'cancelled', 'stripe_payment_method_id' => 'pm_live']);

        $this->fakeStripe(function ($mock) {
            $mock->shouldNotReceive('retrievePaymentMethod');
        });

        $this->webhook($first, $this->fakeIntent());

        // The money still lands somewhere an organizer can see it.
        $this->assertEqualsWithDelta(250.0, (float) $plan->fresh()->unmatched_amount, 0.001);
    }

    /** A replayed webhook must not buy the same lookup twice. */
    public function test_a_known_card_is_not_re_fetched(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();
        $plan->update(['stripe_payment_method_id' => 'pm_live', 'card_last4' => '4242']);

        $this->fakeStripe(function ($mock) {
            $mock->shouldNotReceive('retrievePaymentMethod');
        });

        app(InstallmentService::class)->captureFrom($plan, $this->fakeIntent());

        $this->assertSame('4242', $plan->fresh()->card_last4);
    }

    /**
     * The nexus failure. A `mode: setup` session emits ONLY `checkout.session.completed`, never
     * `payment_intent.succeeded` - and our Connect setup docs tell operators to subscribe to the
     * latter alone. So the swap never reached the app while this page told the buyer it had, and
     * the cron went on declining the card they had just replaced.
     */
    public function test_the_card_swap_applies_on_the_redirect_with_no_webhook(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();
        $plan->update([
            'stripe_customer_id' => 'cus_old',
            'stripe_payment_method_id' => 'pm_dead',
            'card_last4' => '0000',
            'status' => 'delinquent',
        ]);
        $plan->installments->firstWhere('sequence', 2)->update(['status' => 'failed', 'attempts' => 4]);

        $this->fakeStripe(function ($mock) use ($plan) {
            $mock->shouldReceive('retrieveCheckoutSession')->once()->andReturn(
                \Stripe\Checkout\Session::constructFrom([
                    'id' => 'cs_swap',
                    'setup_intent' => 'seti_new',
                    'metadata' => ['installment_plan_card_update' => UrlUtils::encodeId($plan->id)],
                ])
            );
            $mock->shouldReceive('retrieveSetupIntent')->once()->andReturn(
                \Stripe\SetupIntent::constructFrom([
                    'status' => 'succeeded',
                    'customer' => 'cus_live',
                    'payment_method' => 'pm_live',
                ])
            );
            $mock->shouldReceive('retrievePaymentMethod')->once()->andReturn($this->fakeMethod());
        });

        $this->get($this->planUrl($plan, '?updated=1&session_id=cs_swap'))->assertOk();

        $plan->refresh();
        $this->assertSame('pm_live', $plan->stripe_payment_method_id);
        $this->assertSame('4242', $plan->card_last4);
        $this->assertSame('active', $plan->status, 'a working card clears the hold');
        $this->assertSame(
            'scheduled',
            $plan->installments()->where('sequence', 2)->first()->status,
            'the parked row goes back in the ladder'
        );
    }

    /**
     * A card swap that fails has to reach Sentry, not just the log.
     *
     * This one is only reached because a buyer replaced their card after we asked them to, and a
     * failure means the swap did not happen - the cron carries on declining the old card until the
     * ticket is refused at the door. It carried a report() before the Stripe reads were extracted
     * behind a shared helper, and the extraction dropped it.
     */
    public function test_a_failed_card_swap_is_reported(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();

        \Illuminate\Support\Facades\Exceptions::fake();

        $this->partialMock(InstallmentService::class, function ($mock) {
            $mock->shouldReceive('stripeContextFor')->andThrow(new \RuntimeException('stripe is down'));
        });

        $this->assertFalse(app(InstallmentService::class)->applyCardUpdate($plan, 'seti_new'));

        \Illuminate\Support\Facades\Exceptions::assertReported(
            fn (\RuntimeException $e) => $e->getMessage() === 'stripe is down'
        );
    }

    /** ...while a routine card-display read that fails stays a log line. */
    public function test_a_failed_card_display_read_is_not_reported(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();
        $plan->update(['stripe_payment_method_id' => 'pm_live']);

        \Illuminate\Support\Facades\Exceptions::fake();

        $this->partialMock(InstallmentService::class, function ($mock) {
            $mock->shouldReceive('stripeContextFor')->andThrow(new \RuntimeException('stripe is down'));
        });

        app(InstallmentService::class)->captureCardDisplay($plan);

        \Illuminate\Support\Facades\Exceptions::assertNothingReported();
    }

    /** A session belonging to somebody else's plan must not be applied to this one. */
    public function test_a_session_for_another_plan_is_refused(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();
        [$other] = $this->planAwaitingFirstPayment();

        $this->fakeStripe(function ($mock) use ($other) {
            $mock->shouldReceive('retrieveCheckoutSession')->once()->andReturn(
                \Stripe\Checkout\Session::constructFrom([
                    'id' => 'cs_swap',
                    'setup_intent' => 'seti_new',
                    'metadata' => ['installment_plan_card_update' => UrlUtils::encodeId($other->id)],
                ])
            );
            $mock->shouldNotReceive('retrieveSetupIntent');
        });

        $this->get($this->planUrl($plan, '?updated=1&session_id=cs_swap'))->assertOk();

        $this->assertNull($plan->fresh()->stripe_payment_method_id);
    }

    /**
     * The banner used to be driven by the query string alone, so it confirmed a swap that had
     * never reached us. "Update card" also labels the panel button below, hence the count.
     */
    public function test_the_swap_banner_only_appears_when_a_card_was_stored(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();
        $plan->update(['stripe_payment_method_id' => 'pm_dead', 'card_last4' => '0000']);

        $this->fakeStripe(function ($mock) {
            // Stripe unreachable on the redirect. The webhook may still land later, but this page
            // cannot see that and must not claim otherwise.
            $mock->shouldReceive('retrieveCheckoutSession')->once()->andReturnNull();
        });

        $body = $this->get($this->planUrl($plan, '?updated=1&session_id=cs_swap'))
            ->assertOk()
            ->getContent();

        $this->assertSame(
            1,
            substr_count($body, __('messages.update_payment_card')),
            'only the panel button should carry this label - the confirmation banner must not appear'
        );
    }

    public function test_the_swap_banner_appears_when_the_card_was_stored(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();
        $plan->update(['stripe_payment_method_id' => 'pm_dead', 'card_last4' => '0000']);

        $this->fakeStripe(function ($mock) use ($plan) {
            $mock->shouldReceive('retrieveCheckoutSession')->once()->andReturn(
                \Stripe\Checkout\Session::constructFrom([
                    'id' => 'cs_swap',
                    'setup_intent' => 'seti_new',
                    'metadata' => ['installment_plan_card_update' => UrlUtils::encodeId($plan->id)],
                ])
            );
            $mock->shouldReceive('retrieveSetupIntent')->once()->andReturn(
                \Stripe\SetupIntent::constructFrom([
                    'status' => 'succeeded',
                    'customer' => 'cus_live',
                    'payment_method' => 'pm_live',
                ])
            );
            $mock->shouldReceive('retrievePaymentMethod')->once()->andReturn($this->fakeMethod());
        });

        $body = $this->get($this->planUrl($plan, '?updated=1&session_id=cs_swap'))
            ->assertOk()
            ->getContent();

        $this->assertSame(2, substr_count($body, __('messages.update_payment_card')));
    }

    /** An ordinary page load must not talk to Stripe. */
    public function test_viewing_the_page_without_a_session_touches_nothing(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();

        $this->fakeStripe(function ($mock) {
            $mock->shouldNotReceive('retrieveCheckoutSession');
        });

        $this->get($this->planUrl($plan))->assertOk();
    }

    /**
     * The webhook-free backstop TicketController::success() runs on the redirect, which is what
     * makes the capture independent of which Stripe events an install happens to subscribe to.
     */
    public function test_the_redirect_captures_the_card_from_the_session(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();

        $this->fakeStripe(function ($mock) {
            $mock->shouldReceive('retrievePaymentIntent')->once()->andReturn($this->fakeIntent());
            $mock->shouldReceive('retrievePaymentMethod')->once()->andReturn($this->fakeMethod());
        });

        $stored = app(InstallmentService::class)->captureFromSession(
            $plan,
            \Stripe\Checkout\Session::constructFrom(['id' => 'cs_buy', 'payment_intent' => 'pi_live'])
        );

        $this->assertTrue($stored);
        $this->assertSame('pm_live', $plan->fresh()->stripe_payment_method_id);
        $this->assertSame('4242', $plan->fresh()->card_last4);
    }

    /** An ordinary purchase whose webhook already landed must not buy a second lookup. */
    public function test_the_redirect_does_nothing_when_the_card_is_already_stored(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();
        $plan->update(['stripe_payment_method_id' => 'pm_live', 'card_last4' => '4242']);

        $this->fakeStripe(function ($mock) {
            $mock->shouldNotReceive('retrievePaymentIntent');
        });

        $this->assertFalse(app(InstallmentService::class)->captureFromSession(
            $plan,
            \Stripe\Checkout\Session::constructFrom(['id' => 'cs_buy', 'payment_intent' => 'pi_live'])
        ));
    }

    /** A sale with no payment plan is the overwhelmingly common case and must stay free. */
    public function test_the_redirect_is_free_for_a_sale_with_no_plan(): void
    {
        $this->fakeStripe(function ($mock) {
            $mock->shouldNotReceive('retrievePaymentIntent');
        });

        $this->assertFalse(app(InstallmentService::class)->captureFromSession(
            null,
            \Stripe\Checkout\Session::constructFrom(['id' => 'cs_buy', 'payment_intent' => 'pi_live'])
        ));
    }

    /**
     * The guard that would have caught all of the above. A due payment with no card on file is
     * where the money stops, and it used to be a bare `continue`.
     */
    public function test_a_due_installment_with_no_card_is_reported(): void
    {
        [$plan] = $this->planAwaitingFirstPayment();
        $plan->installments->firstWhere('sequence', 1)->update(['status' => 'paid', 'paid_at' => now()]);
        $plan->installments->firstWhere('sequence', 2)->update([
            'status' => 'scheduled',
            'due_at' => now()->subDay(),
        ]);

        $this->artisan('app:charge-installments')
            ->expectsOutputToContain('1 plan(s) have a payment due but no card on file.')
            ->assertSuccessful();
    }
}
