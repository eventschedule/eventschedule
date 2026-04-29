<?php

namespace App\Utils;

class PhoneUtils
{
    public static function normalize(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $phone = trim($phone);

        // Strip everything except digits
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (empty($digits)) {
            return null;
        }

        $normalized = '+' . $digits;

        // If it doesn't match E.164, reject the phone entirely
        if (! preg_match('/^\+[1-9]\d{1,14}$/', $normalized)) {
            return null;
        }

        return $normalized;
    }
}
