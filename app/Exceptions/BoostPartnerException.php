<?php

namespace App\Exceptions;

/**
 * A refused step in a partner boost's lifecycle: the wrong state (409), an out-of-date version
 * (409), or a value that cannot be accepted (422). The message is a translated sentence meant
 * for the person who pressed the button, and `code` is a stable key for the API.
 */
class BoostPartnerException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 409,
        public readonly string $errorCode = 'invalid_state',
    ) {
        parent::__construct($message);
    }

    public static function stale(): self
    {
        return new self(__('messages.boost_partner_stale_version'), 409, 'stale_version');
    }

    /** A payment for a budget the Business has changed since the partner charged for it. */
    public static function staleBudget(): self
    {
        return new self(__('messages.boost_partner_stale_version'), 409, 'stale_budget');
    }

    public static function state(): self
    {
        return new self(__('messages.boost_partner_wrong_state'), 409, 'invalid_state');
    }

    public static function invalid(string $message): self
    {
        return new self($message, 422, 'validation_failed');
    }
}
