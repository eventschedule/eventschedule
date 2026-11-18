<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\StripeClient;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A free trial starts only on a card whose security code Stripe matched (2026-11-18: five trials
 * in a day, three on one card whose code had failed, two on another, each on an account of its
 * own). A trial charges nothing until it ends, so the code is the only check the card gets.
 *
 * Stripe is faked at the container: Cashier resolves its client with app(StripeClient::class).
 * The fake answers the payment method lookup and nothing else, so a checkout that went on to
 * create the subscription would fail on the missing customer service, which is the point: the
 * refusal must come first.
 */
class TrialSecurityCodeTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.hosted' => true,
            'app.trial_days' => 7,
            'cashier.key' => 'pk_test_fixture',
            'cashier.secret' => 'sk_test_fixture',
            'services.stripe_platform.price_monthly' => 'price_pro_m',
            'services.stripe_platform.price_yearly' => 'price_pro_y',
        ]);
    }

    private function fakeCard(?string $cvcCheck): void
    {
        $paymentMethods = new class($cvcCheck)
        {
            public function __construct(public ?string $cvcCheck) {}

            public function retrieve($id, $params = null, $opts = null)
            {
                return \Stripe\PaymentMethod::constructFrom([
                    'id' => $id,
                    'type' => 'card',
                    'card' => ['brand' => 'mastercard', 'checks' => ['cvc_check' => $this->cvcCheck]],
                ]);
            }
        };

        $client = new class($paymentMethods)
        {
            public function __construct(public object $paymentMethods) {}
        };

        $this->app->bind(StripeClient::class, fn () => $client);
    }

    private function freeSchedule(User $owner): Role
    {
        $role = $this->createFreeRole($owner, 'venue');
        $role->forceFill(['plan_expires' => null])->save();

        return $role->fresh();
    }

    /** Mutation: drop the check, or accept anything but "pass". */
    public function test_a_trial_does_not_start_unless_the_security_code_matched(): void
    {
        foreach (['fail', 'unchecked', 'unavailable', null] as $check) {
            $owner = $this->createOwner();
            $role = $this->freeSchedule($owner);
            $this->assertNotNull($role->checkoutTrialEnd(), 'the fixture should be offered a trial');
            $this->fakeCard($check);

            $this->actingAs($owner)
                ->post('/'.$role->subdomain.'/subscribe', ['payment_method' => 'pm_test', 'plan' => 'monthly', 'tier' => 'pro'])
                ->assertRedirect()
                ->assertSessionHas('error', __('messages.subscription_card_code_unverified'));

            $this->assertSame(0, $role->subscriptions()->count(), 'cvc_check '.var_export($check, true).' started a subscription');
            $this->assertNull($owner->fresh()->trial_used_at, 'a refused card spent the trial');
            $this->assertSame('free', $role->fresh()->plan_type);
        }
    }

    /** A matched code goes on to create the subscription (here, the fake has no customers). */
    public function test_a_matched_security_code_goes_on_to_the_subscription(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeSchedule($owner);
        $this->fakeCard('pass');

        $this->actingAs($owner)
            ->post('/'.$role->subdomain.'/subscribe', ['payment_method' => 'pm_test', 'plan' => 'monthly', 'tier' => 'pro'])
            ->assertRedirect()
            ->assertSessionMissing('error', __('messages.subscription_card_code_unverified'));
    }
}
