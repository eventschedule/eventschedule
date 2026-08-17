<?php

namespace App\Services\Payments;

/**
 * One field in a gateway's credentials form.
 *
 * Declaring the fields rather than hand-writing a blade per gateway is what keeps a new gateway down
 * to a single class: profile/partials/payments/credentials.blade.php renders any driver's list. Only
 * gateways whose connect flow is not a plain form need settingsView() instead - Stripe's OAuth
 * handshake and Invoice Ninja's validate-then-register-a-webhook are the two that do.
 *
 * $name is the users column the value lands in, so a field is also the persistence mapping.
 */
class CredentialField
{
    /**
     * @param  string  $label  translation key, not a literal
     * @param  string  $type  text|password|toggle|multiselect
     * @param  array<string, string>  $options  multiselect only, as value => already-translated label
     * @param  string|null  $help  translation key for the hint under the input
     * @param  bool  $secret  render as a password input and never echo the stored value back
     */
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly string $type = 'text',
        public readonly array $options = [],
        public readonly ?string $help = null,
        public readonly bool $required = false,
        public readonly bool $secret = false,
    ) {}

    public function isSecret(): bool
    {
        return $this->secret || $this->type === 'password';
    }
}
