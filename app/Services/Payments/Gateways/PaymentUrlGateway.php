<?php

namespace App\Services\Payments\Gateways;

use App\Models\User;
use App\Services\Payments\PaymentGatewayDriver;

/**
 * A plain link out to whatever the owner already uses - Venmo, Cash App, PayPal.me, a bank page.
 *
 * Not a gateway in any real sense: this app sends the buyer away and never hears from the provider.
 * The buyer comes back through an HMAC-signed return URL, and that return is what marks the sale
 * paid, so the "payment" is on the buyer's word. That is the owner's deliberate trade for being able
 * to use a provider we do not integrate with.
 */
class PaymentUrlGateway extends PaymentGatewayDriver
{
    public function key(): string
    {
        return 'payment_url';
    }

    public function label(?User $owner): string
    {
        if ($owner?->payment_url) {
            return __('messages.payment_url').' - '.$owner->paymentUrlHost();
        }

        return __('messages.payment_url');
    }

    public function isConfiguredFor(?User $owner): bool
    {
        return (bool) $owner?->payment_url;
    }

    /**
     * Custom UI: connecting is not a credentials form here.
     */
    public function settingsView(): ?string
    {
        return 'profile.partials.payments.payment-url';
    }
}
