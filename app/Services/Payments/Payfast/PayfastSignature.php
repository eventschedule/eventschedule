<?php

namespace App\Services\Payments\Payfast;

/**
 * Payfast's MD5 signatures, in both directions.
 *
 * Two different constructions, and mixing them up is the classic Payfast integration bug:
 *
 *  - Outbound (sign): OUR field order, fixed by Payfast's docs, empties dropped, passphrase appended
 *    as an ordinary trailing field.
 *  - Inbound (verifyItn): THEIR field order, exactly as it arrived in the body, with the passphrase
 *    appended as a urlencoded suffix instead.
 */
class PayfastSignature
{
    /**
     * The order Payfast requires for a checkout signature. http_build_query() preserves insertion
     * order, so this array IS the specification - reordering it silently breaks every payment.
     */
    private const FIELD_ORDER = [
        'merchant_id', 'merchant_key', 'return_url', 'cancel_url', 'notify_url',
        'name_first', 'name_last', 'email_address', 'cell_number',
        'm_payment_id', 'amount', 'item_name', 'item_description',
        'custom_int1', 'custom_int2', 'custom_int3', 'custom_int4', 'custom_int5',
        'custom_str1', 'custom_str2', 'custom_str3', 'custom_str4', 'custom_str5',
        'email_confirmation', 'confirmation_address',
        'payment_method',
        'subscription_type', 'billing_date', 'recurring_amount', 'frequency', 'cycles',
    ];

    /**
     * Sign an outbound checkout payload.
     *
     * The passphrase is appended to the string being hashed and is NEVER part of the returned data.
     * Invoice Ninja's driver posts it as a hidden form field, which hands the merchant's shared secret
     * to the buyer's browser; anyone who has it can forge a valid ITN.
     *
     * @param  array<string, mixed>  $data  the fields being posted, signature excluded
     */
    public static function sign(array $data, ?string $passphrase): string
    {
        $fields = [];

        foreach (self::FIELD_ORDER as $key) {
            // empty() and not isset(): Payfast excludes blank values from the signature, and an
            // included-but-empty field produces a different hash than an omitted one.
            if (! empty($data[$key])) {
                $fields[$key] = $data[$key];
            }
        }

        if (! empty($passphrase)) {
            $fields['passphrase'] = $passphrase;
        }

        return md5(http_build_query($fields));
    }

    /**
     * Verify an inbound ITN signature.
     *
     * $rawBody, not $request->all(). Laravel's ConvertEmptyStringsToNull turns Payfast's empty ITN
     * fields into null and http_build_query() drops nulls, so rebuilding the string from the parsed
     * request produces a query missing keys Payfast included - and every real payment fails to
     * verify. Payfast's own field order has to be preserved too, which only the body has.
     */
    public static function verifyItn(string $rawBody, ?string $passphrase): bool
    {
        parse_str($rawBody, $data);

        $signature = $data['signature'] ?? '';

        if (! is_string($signature) || $signature === '') {
            return false;
        }

        unset($data['signature']);

        $query = http_build_query($data);

        // Appended as a suffix here rather than as a field, because the fields must keep the order
        // they arrived in and http_build_query() has already run over them.
        if (! empty($passphrase)) {
            $query .= '&passphrase='.urlencode($passphrase);
        }

        return hash_equals(md5($query), $signature);
    }

    /**
     * The ITN payload as Payfast sent it, parsed from the raw body for the same reason as above.
     *
     * @return array<string, mixed>
     */
    public static function parseItn(string $rawBody): array
    {
        parse_str($rawBody, $data);

        return $data;
    }
}
