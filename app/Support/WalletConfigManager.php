<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;

class WalletConfigManager
{
    public static function applyApple(array $settings): void
    {
        $overrides = static::formatAppleSettings($settings);

        Config::set('wallet.apple', array_merge(config('wallet.apple', []), $overrides));
    }

    public static function applyGoogle(array $settings): void
    {
        $overrides = static::formatGoogleSettings($settings);

        Config::set('wallet.google', array_merge(config('wallet.google', []), $overrides));
    }

    public static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    protected static function formatAppleSettings(array $settings): array
    {
        $overrides = [];

        if (array_key_exists('enabled', $settings)) {
            $overrides['enabled'] = static::toBool($settings['enabled']);
        }

        if (array_key_exists('certificate_path', $settings)) {
            $overrides['certificate_path'] = static::resolveStoragePath($settings['certificate_path']);
        }

        if (array_key_exists('wwdr_certificate_path', $settings)) {
            $overrides['wwdr_certificate_path'] = static::resolveStoragePath($settings['wwdr_certificate_path']);
        }

        if (array_key_exists('certificate_password', $settings)) {
            $password = static::sanitizeString($settings['certificate_password']);

            if ($password !== null) {
                $overrides['certificate_password'] = $password;
            }
        }

        foreach ([
            'pass_type_identifier',
            'team_identifier',
            'organization_name',
            'background_color',
            'foreground_color',
            'label_color',
        ] as $key) {
            if (array_key_exists($key, $settings)) {
                $value = static::sanitizeString($settings[$key]);

                if ($value !== null) {
                    $overrides[$key] = $value;
                }
            }
        }

        return $overrides;
    }

    protected static function formatGoogleSettings(array $settings): array
    {
        $overrides = [];

        if (array_key_exists('enabled', $settings)) {
            $overrides['enabled'] = static::toBool($settings['enabled']);
        }

        foreach ([
            'issuer_id',
            'issuer_name',
            'class_suffix',
            'service_account_json',
        ] as $key) {
            if (array_key_exists($key, $settings)) {
                $value = static::sanitizeString($settings[$key]);

                if ($value !== null) {
                    $overrides[$key] = $value;
                }
            }
        }

        if (array_key_exists('service_account_json_path', $settings)) {
            $overrides['service_account_json_path'] = static::resolveStoragePath($settings['service_account_json_path']);
        }

        return $overrides;
    }

    protected static function resolveStoragePath(?string $relativePath): ?string
    {
        $relativePath = static::sanitizeString($relativePath);

        if ($relativePath === null) {
            return null;
        }

        return storage_path('app/' . ltrim($relativePath, '/'));
    }

    protected static function sanitizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
