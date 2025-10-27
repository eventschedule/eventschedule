<?php

namespace App\Support;

class UrlUtilsConfigManager
{
    /**
     * Apply URL utility configuration overrides to the runtime config array.
     */
    public static function apply(mixed $verifySsl): void
    {
        if ($verifySsl === null) {
            return;
        }

        config(['url_utils.verify_ssl' => static::toBoolean($verifySsl)]);
    }

    /**
     * Normalize a value into a boolean flag.
     */
    protected static function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }
}
