<?php

namespace App\Services\Payments;

use App\Models\User;

/**
 * The registry of payment gateways.
 *
 * config/payments.php is the single list, and its array order is the display order, so adding a
 * gateway is one class plus one line there. Nothing else in the app should contain a list of
 * gateway names - if you find yourself writing in_array($method, [...]) somewhere, the answer
 * belongs on PaymentGatewayDriver as a capability instead.
 */
class PaymentGatewayManager
{
    /** @var array<string, PaymentGatewayDriver>|null */
    private ?array $drivers = null;

    /**
     * @return array<string, PaymentGatewayDriver> keyed by key(), in config order
     */
    public function all(): array
    {
        if ($this->drivers !== null) {
            return $this->drivers;
        }

        $this->drivers = [];

        foreach ((array) config('payments.gateways', []) as $key => $class) {
            $driver = app($class);

            // The config key wins over the class's own key() so a typo shows up here rather than as
            // a gateway that silently never matches a sale.
            $this->drivers[$key] = $driver;
        }

        return $this->drivers;
    }

    /**
     * Resolve a stored payment_method to its driver, or null.
     *
     * Null-safe by contract, and callers MUST handle null. sales.payment_method carries 'rsvp' and
     * 'import' as provenance markers with no driver behind them, and surfaces like the admin sales
     * table iterate every sale they can see, so this returning null is a normal day rather than an
     * error. Legacy rows can also hold a method an installation has since removed from its config.
     */
    public function get(?string $key): ?PaymentGatewayDriver
    {
        if ($key === null || $key === '') {
            return null;
        }

        return $this->all()[$key] ?? null;
    }

    /**
     * Every registered key, including any that are not selectable for an event.
     *
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->all());
    }

    /**
     * The keys an owner may actually choose for an event, which is what validation rules want.
     *
     * @return list<string>
     */
    public function selectableKeys(): array
    {
        return array_keys(array_filter(
            $this->all(),
            fn (PaymentGatewayDriver $driver) => $driver->isSelectableForEvents(),
        ));
    }

    /**
     * Everything this owner may offer on an event, cash included.
     *
     * @return array<string, PaymentGatewayDriver>
     */
    public function configuredFor(?User $owner): array
    {
        if (! $owner) {
            return [];
        }

        return array_filter(
            $this->all(),
            fn (PaymentGatewayDriver $driver) => $driver->isSelectableForEvents()
                && $driver->isConfiguredFor($owner),
        );
    }

    /**
     * The gateways this owner has actually connected, excluding the ones that need no setup.
     *
     * Empty means they cannot take money online yet, which is what the event form keys the "connect
     * a gateway to get paid" nudge off. Deliberately NOT configuredFor(): cash is always available,
     * so counting it would mean the nudge never appeared and an owner on the free plan could publish
     * a paid event that can only be settled by hand without ever being told.
     *
     * @return array<string, PaymentGatewayDriver>
     */
    public function connectedFor(?User $owner): array
    {
        return array_filter(
            $this->configuredFor($owner),
            fn (PaymentGatewayDriver $driver) => $driver->needsCredentials(),
        );
    }

    /**
     * The gateways an owner may pick for one specific event: connected, able to settle the event's
     * currency, and able to take an amount of this size.
     *
     * $total is optional because the event form does not know what a buyer will spend; pass it on
     * the checkout path, where a gateway floor would otherwise surface as an opaque rejection on the
     * gateway's own page.
     *
     * @return array<string, PaymentGatewayDriver>
     */
    public function availableFor(?User $owner, ?string $currencyCode, ?float $total = null): array
    {
        $currency = $currencyCode ?: 'USD';

        return array_filter(
            $this->configuredFor($owner),
            function (PaymentGatewayDriver $driver) use ($currency, $total) {
                if (! $driver->supportsCurrency($currency)) {
                    return false;
                }

                if ($total === null) {
                    return true;
                }

                [$min, $max] = $driver->amountLimits($currency);

                // A zero total never reaches a gateway - checkout() settles free orders itself - so
                // a floor must not hide a gateway from an event that merely has a free ticket type.
                if ($total > 0 && $min !== null && $total < $min) {
                    return false;
                }

                return ! ($max !== null && $total > $max);
            },
        );
    }

    /**
     * Whether a stored method may be combined with other events in one cart. Unknown methods say no:
     * the cart has to settle every leg in one payment, and a rail we know nothing about cannot be
     * assumed to manage that.
     */
    public function supportsCart(?string $key): bool
    {
        return (bool) $this->get($key)?->supportsCart();
    }

    /**
     * Whether a buyer can return to an unpaid sale on a stored method and finish paying. Unknown
     * methods say no - offering a "complete payment" button that leads nowhere is worse than
     * offering nothing.
     */
    public function canResumePayment(?string $key): bool
    {
        return (bool) $this->get($key)?->canResumePayment();
    }

    /**
     * Whether paying sends the buyer to a third-party page, so an embedded widget must break frame.
     * Unknown methods say no: nothing to break out to.
     */
    public function redirectsOffsite(?string $key): bool
    {
        return (bool) $this->get($key)?->redirectsOffsite();
    }

    /**
     * Whether the owner's own written instructions are how this method gets paid.
     */
    public function usesPaymentInstructions(?string $key): bool
    {
        return (bool) $this->get($key)?->usesPaymentInstructions();
    }

    /**
     * Whether unpaid sales on a stored method should be auto-expired.
     *
     * Unknown methods default to true, matching what ReleaseTickets did before this existed: it
     * excluded cash by name and swept everything else, including rows whose method it had never
     * heard of.
     */
    public function expiresUnpaidSales(?string $key): bool
    {
        return $this->get($key)?->expiresUnpaidSales() ?? true;
    }

    /**
     * The methods whose unpaid sales must never be swept, for use with whereNotIn().
     *
     * An empty result is safe: Laravel compiles whereNotIn(col, []) to `1 = 1`, which excludes
     * nothing, and "exclude nothing" is the right reading of "no gateway opts out".
     *
     * @return list<string>
     */
    public function nonExpiringKeys(): array
    {
        return array_keys(array_filter(
            $this->all(),
            fn (PaymentGatewayDriver $driver) => ! $driver->expiresUnpaidSales(),
        ));
    }

    /**
     * The capability map handed to the event form's Vue mount, so the template can gate on what a
     * gateway does instead of naming it. Passed as JSON props and read with Vue's own interpolation,
     * never echoed server-side into a Vue-compiled template.
     *
     * @return array<string, array<string, bool>>
     */
    public function capabilityMap(): array
    {
        $map = [];

        foreach ($this->all() as $key => $driver) {
            $map[$key] = [
                'installments' => $driver->supportsInstallments(),
                'payment_instructions' => $driver->usesPaymentInstructions(),
                'cart' => $driver->supportsCart(),
                'resume' => $driver->canResumePayment(),
            ];
        }

        return $map;
    }
}
