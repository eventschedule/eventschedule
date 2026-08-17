<?php

namespace App\Services\Payments\Gateways;

use App\Models\Sale;
use App\Models\User;
use App\Services\Payments\PaymentGatewayDriver;

/**
 * Invoice Ninja, in either of its two modes.
 *
 * `invoice` raises an invoice per checkout and sends the buyer to its payment page. `payment_link`
 * hands the whole quantity selection to an Invoice Ninja subscription page and learns what was bought
 * from the post-purchase webhook. Both are per event, which is why this rail cannot be carted.
 */
class InvoiceNinjaGateway extends PaymentGatewayDriver
{
    public function key(): string
    {
        return 'invoiceninja';
    }

    public function label(?User $owner): string
    {
        if ($owner?->invoiceninja_company_name) {
            return 'Invoice Ninja - '.$owner->invoiceninja_company_name;
        }

        return 'Invoice Ninja';
    }

    public function isConfiguredFor(?User $owner): bool
    {
        return (bool) $owner?->invoiceninja_api_key;
    }

    public function referenceUrl(Sale $sale): ?string
    {
        $reference = $sale->transaction_reference;

        if (! $reference) {
            return null;
        }

        // A payment-link sale stores 'sub:<subscription id>' rather than an invoice id, so there is
        // no invoice page to link to. Falls back to showing the raw reference.
        if (str_starts_with($reference, 'sub:')) {
            return null;
        }

        // Deliberately the hosted Invoice Ninja app rather than the owner's own
        // invoiceninja_api_url: this preserves the behaviour the sales table has always had. Owners
        // running their own instance get an unhelpful link here, which is worth revisiting, but not
        // as a silent side effect of this refactor.
        return 'https://app.invoicing.co/#/invoices/'.$reference.'/edit';
    }

    /**
     * Custom UI: connecting is not a credentials form here.
     */
    public function settingsView(): ?string
    {
        return 'profile.partials.payments.invoiceninja';
    }
}
