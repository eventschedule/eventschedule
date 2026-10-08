<?php

namespace App\Rules;

use App\Services\Blocklist;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Refuses an email address the operator's list refuses (/admin/blocked): the address itself, its
 * domain, and where an account is being MADE also the network address the request comes from.
 *
 * On the email field of every form that makes an account a person can sign in to, and with
 * `address: false` on the form that changes an account's address, where who is asking is
 * already known. Not on checkout or a newsletter form: the list is about accounts.
 *
 * The sentence says which of the two it was. Whoever is refused finds that out by trying
 * anyway, and somebody who shares an office's address with a spammer should not be told their
 * email is the trouble.
 */
class NotBlocklisted implements ValidationRule
{
    public function __construct(private bool $address = true) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Something that is not text is another rule's to refuse.
        $entry = Blocklist::refusal(is_string($value) ? $value : null, $this->address ? request() : null);

        if ($entry) {
            $fail(self::sentence($entry->type));
        }
    }

    public static function sentence(string $type): string
    {
        return __($type === Blocklist::IP ? 'messages.signup_refused_address' : 'messages.signup_refused_email');
    }
}
