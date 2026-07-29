<?php

namespace App\Exceptions;

/**
 * Thrown when Invoice Ninja, or a proxy in front of it, rejects an API request.
 *
 * The message is safe to show to the account owner who configured the integration: it
 * carries the HTTP status, the curl error, or Invoice Ninja's own "message" field, never
 * the API token. reasonKey() names a messages.* string describing the likely cause so the
 * UI can show a plain language explanation above the technical detail.
 *
 * Extends \Exception so the existing catch (\Exception) blocks at every call site
 * (TicketController, GiftCardController, InvoiceNinjaController) keep working.
 */
class InvoiceNinjaException extends \Exception
{
    public function __construct(string $message, private string $reasonKey = 'invoiceninja_error_generic')
    {
        parent::__construct($message);
    }

    public function reasonKey(): string
    {
        return $this->reasonKey;
    }
}
