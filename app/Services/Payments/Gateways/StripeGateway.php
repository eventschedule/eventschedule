<?php

namespace App\Services\Payments\Gateways;

use App\Models\Sale;
use App\Models\User;
use App\Services\Payments\PaymentGatewayDriver;
use App\Utils\MoneyUtils;

/**
 * Stripe Checkout, on the schedule owner's behalf.
 *
 * Two rails behind one key, chosen by `config('app.hosted') && $owner->stripe_account_id`: hosted
 * installs use Stripe Connect against the platform key, selfhost installs use their own platform
 * keys directly. isConfiguredFor() is where that divergence lives, so nothing outside this class has
 * to know which rail an install is on.
 */
class StripeGateway extends PaymentGatewayDriver
{
    public function key(): string
    {
        return 'stripe';
    }

    public function label(?User $owner): string
    {
        // The connected account name only exists once onboarding finished. Before that the owner can
        // still select Stripe, so fall back to the bare product name rather than "Stripe - ".
        if ($owner?->stripe_completed_at && $owner->stripe_company_name) {
            return 'Stripe - '.$owner->stripe_company_name;
        }

        return 'Stripe';
    }

    public function isConfiguredFor(?User $owner): bool
    {
        return (bool) $owner?->canAcceptStripePayments();
    }

    /**
     * The only rail that can settle a whole multi-event order in one payment.
     */
    public function supportsCart(): bool
    {
        return true;
    }

    /**
     * The only rail that can charge a saved card off-session, which is what installments need.
     */
    public function supportsInstallments(): bool
    {
        return true;
    }

    /**
     * Stripe refuses a charge below roughly 50 smallest currency units. It matters well before the
     * gateway sees it: a gift card that covers all but a few cents of an order would leave an
     * unchargeable remainder, so the guest form has to know the floor to decide how much of the card
     * to apply.
     */
    public function amountLimits(string $currencyCode): array
    {
        return [50 / MoneyUtils::getSmallestUnitMultiplier($currencyCode), null];
    }

    public function referenceUrl(Sale $sale): ?string
    {
        if (! $sale->transaction_reference) {
            return null;
        }

        return 'https://dashboard.stripe.com/payments/'.$sale->transaction_reference;
    }
}
