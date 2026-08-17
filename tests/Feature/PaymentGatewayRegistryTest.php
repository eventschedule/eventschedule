<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Payments\Gateways\CashGateway;
use App\Services\Payments\Gateways\StripeGateway;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Pins the registry contracts the rest of the app now leans on, in place of the payment-method
 * string checks that used to be scattered through blades, queries and validation rules.
 */
class PaymentGatewayRegistryTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function manager(): PaymentGatewayManager
    {
        return app(PaymentGatewayManager::class);
    }

    public function test_get_is_null_safe_for_non_gateway_methods(): void
    {
        $manager = $this->manager();

        // sales.payment_method also carries provenance markers with no driver behind them, and the
        // admin sales table asks about every row it renders. These returning null rather than
        // throwing is the contract that keeps that page up.
        $this->assertNull($manager->get('rsvp'));
        $this->assertNull($manager->get('import'));
        $this->assertNull($manager->get(null));
        $this->assertNull($manager->get(''));
        $this->assertNull($manager->get('a_gateway_this_install_removed'));
    }

    public function test_capability_lookups_tolerate_an_unknown_method(): void
    {
        $manager = $this->manager();

        // Each of these replaced an in_array() at a call site, so each has to answer safely for a
        // row whose method has no driver rather than blowing up the page it renders.
        $this->assertFalse($manager->supportsCart('rsvp'));
        $this->assertFalse($manager->canResumePayment('rsvp'));
        $this->assertFalse($manager->usesPaymentInstructions('rsvp'));
        $this->assertFalse($manager->redirectsOffsite('rsvp'));

        // The one that defaults the other way: ReleaseTickets swept anything it did not recognise
        // before this existed, and must keep doing so.
        $this->assertTrue($manager->expiresUnpaidSales('rsvp'));
        $this->assertTrue($manager->expiresUnpaidSales(null));
    }

    public function test_only_cash_opts_out_of_expiry(): void
    {
        // The rule ReleaseTickets used to spell out as `payment_method != 'cash'` in two queries.
        $this->assertSame(['cash'], $this->manager()->nonExpiringKeys());
        $this->assertFalse($this->manager()->expiresUnpaidSales('cash'));
        $this->assertTrue($this->manager()->expiresUnpaidSales('stripe'));
    }

    public function test_cart_eligibility_matches_the_previous_hardcoded_pair(): void
    {
        $manager = $this->manager();

        // Was in_array($method, ['stripe', 'cash']) in both TicketController and tickets.blade.php.
        $this->assertTrue($manager->supportsCart('stripe'));
        $this->assertTrue($manager->supportsCart('cash'));
        $this->assertFalse($manager->supportsCart('invoiceninja'));
        $this->assertFalse($manager->supportsCart('payment_url'));
    }

    public function test_resume_and_offsite_match_the_previous_hardcoded_triples(): void
    {
        $manager = $this->manager();

        foreach (['stripe', 'invoiceninja', 'payment_url'] as $key) {
            $this->assertTrue($manager->canResumePayment($key), $key.' should be resumable');
            $this->assertTrue($manager->redirectsOffsite($key), $key.' should redirect offsite');
        }

        $this->assertFalse($manager->canResumePayment('cash'));
        $this->assertFalse($manager->redirectsOffsite('cash'));
    }

    public function test_cash_is_selectable_but_does_not_count_as_connected(): void
    {
        $owner = $this->createOwner();

        // The distinction the event form depends on: cash is always on the menu, but an owner who
        // has connected nothing must still be shown the setup nudge rather than a cash-only
        // dropdown. Collapsing these two questions is what would silently hide the nudge.
        $this->assertArrayHasKey('cash', $this->manager()->configuredFor($owner));
        $this->assertSame([], array_keys($this->manager()->connectedFor($owner)));
    }

    public function test_connecting_a_gateway_makes_it_selectable(): void
    {
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $owner->payment_url = 'https://venmo.com/example';
        $owner->save();

        $connected = $this->manager()->connectedFor($owner);

        $this->assertSame(['payment_url'], array_keys($connected));
        $this->assertArrayHasKey('payment_url', $this->manager()->availableFor($owner, 'USD'));
    }

    public function test_selectable_keys_drive_validation_and_exclude_markers(): void
    {
        $keys = $this->manager()->selectableKeys();

        $this->assertSame(['cash', 'stripe', 'invoiceninja', 'payment_url', 'payfast'], $keys);
        $this->assertNotContains('rsvp', $keys);
        $this->assertNotContains('import', $keys);
    }

    public function test_config_order_is_the_display_order(): void
    {
        // The event dropdown renders in registry order, so the config array is the single place that
        // decides it. Cash first matches what the hand-written option list used to do.
        $this->assertSame(['cash', 'stripe', 'invoiceninja', 'payment_url', 'payfast'], $this->manager()->keys());
    }

    public function test_amount_limits_carry_the_stripe_minimum_charge(): void
    {
        // Stripe refuses charges under ~50 smallest units. The guest form needs the floor to decide
        // how much of a gift card it can apply without leaving an unchargeable remainder.
        [$min, $max] = (new StripeGateway)->amountLimits('USD');
        $this->assertSame(0.5, $min);
        $this->assertNull($max);

        // A zero-decimal currency has no sub-unit, so the floor is 50 of the unit itself.
        [$jpyMin] = (new StripeGateway)->amountLimits('JPY');
        $this->assertSame(50.0, (float) $jpyMin);

        // A gateway with no floor says so, rather than reporting zero as if it were a limit.
        $this->assertSame([null, null], (new CashGateway)->amountLimits('USD'));
    }

    public function test_a_gateway_that_cannot_take_the_currency_is_not_offered(): void
    {
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $owner->payment_url = 'https://venmo.com/example';
        $owner->save();

        // A stub gateway rather than Payfast, so this pins availableFor()'s use of supportsCurrency()
        // independently of any one driver's rules. PayfastCheckoutTest covers the real ZAR gate.
        $this->assertArrayHasKey('payment_url', $this->manager()->availableFor($owner, 'ZAR'));

        config(['payments.gateways' => [
            'currency_locked' => CurrencyLockedTestGateway::class,
        ]]);

        // Rebuild: the manager memoizes its drivers for the request.
        $manager = new PaymentGatewayManager;

        $this->assertArrayHasKey('currency_locked', $manager->availableFor($owner, 'ZAR'));
        $this->assertArrayNotHasKey('currency_locked', $manager->availableFor($owner, 'USD'));
    }

    public function test_an_amount_below_a_gateway_floor_hides_it_but_zero_does_not(): void
    {
        config(['app.hosted' => true]);
        config(['payments.gateways' => [
            'currency_locked' => CurrencyLockedTestGateway::class,
        ]]);

        $owner = $this->createOwner();
        $manager = new PaymentGatewayManager;

        $this->assertArrayNotHasKey('currency_locked', $manager->availableFor($owner, 'ZAR', 4.99));
        $this->assertArrayHasKey('currency_locked', $manager->availableFor($owner, 'ZAR', 5.00));

        // A free order never reaches a gateway - checkout() settles it directly - so a floor must
        // not hide the gateway from an event that merely has a free ticket type.
        $this->assertArrayHasKey('currency_locked', $manager->availableFor($owner, 'ZAR', 0.0));
    }
}

/**
 * A stand-in for a currency- and floor-restricted gateway, so the registry mechanics can be pinned
 * without waiting on a real driver to have those traits.
 */
class CurrencyLockedTestGateway extends \App\Services\Payments\PaymentGatewayDriver
{
    public function key(): string
    {
        return 'currency_locked';
    }

    public function label(?User $owner): string
    {
        return 'Currency Locked';
    }

    public function isConfiguredFor(?User $owner): bool
    {
        return true;
    }

    public function needsCredentials(): bool
    {
        return false;
    }

    public function supportsCurrency(string $currencyCode): bool
    {
        return $currencyCode === 'ZAR';
    }

    public function amountLimits(string $currencyCode): array
    {
        return [5.0, null];
    }
}
