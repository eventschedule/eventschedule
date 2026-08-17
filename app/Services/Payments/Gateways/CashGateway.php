<?php

namespace App\Services\Payments\Gateways;

use App\Models\User;
use App\Services\Payments\PaymentGatewayDriver;

/**
 * Pay the organizer directly - at the door, by transfer, however they say in their instructions.
 *
 * A pseudo-gateway: no credentials, no callback, no money moved by this app. It inherits the base
 * startCheckout(), which lands the buyer on their ticket with the sale left unpaid, because that is
 * exactly what cash means here.
 */
class CashGateway extends PaymentGatewayDriver
{
    public function key(): string
    {
        return 'cash';
    }

    public function label(?User $owner): string
    {
        return __('messages.cash');
    }

    /**
     * Always available: there is nothing to connect.
     */
    public function isConfiguredFor(?User $owner): bool
    {
        return true;
    }

    public function needsCredentials(): bool
    {
        return false;
    }

    /**
     * Cash events can be carted. The cart settles every leg in one go, and for cash "settling" is
     * just recording the sales, which works across any number of events.
     */
    public function supportsCart(): bool
    {
        return true;
    }

    /**
     * Nothing for the buyer to come back and pay online.
     */
    public function canResumePayment(): bool
    {
        return false;
    }

    /**
     * Nowhere to send the buyer, so an embedded widget has no reason to break out of its frame.
     */
    public function redirectsOffsite(): bool
    {
        return false;
    }

    public function usesPaymentInstructions(): bool
    {
        return true;
    }

    /**
     * A cash order is settled in person, sometimes well after any expiry window, so releasing it
     * would cancel a sale the organizer is still expecting to collect. This is the rule ReleaseTickets
     * used to spell out as `payment_method != 'cash'`.
     */
    public function expiresUnpaidSales(): bool
    {
        return false;
    }
}
