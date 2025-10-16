<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class MailConfigManager
{
    /**
     * Cache the hash of the last applied settings so we only rebuild the mailer
     * transport when something actually changes.
     */
    protected static ?string $appliedHash = null;

    public static function applyFromDatabase(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $mailSettings = Setting::forGroup('mail');

        if (empty($mailSettings)) {
            return;
        }

        static::apply($mailSettings);
    }

    public static function apply(array $settings, bool $force = false): void
    {
        $normalized = static::normalizeSettings($settings);

        $hash = md5(json_encode($normalized));

        if (! $force && $hash === static::$appliedHash) {
            return;
        }

        static::$appliedHash = $hash;

        $mailer = $normalized['mailer'] ?? null;

        if (array_key_exists('mailer', $normalized) && $normalized['mailer'] !== null) {
            config(['mail.default' => $normalized['mailer']]);
        }

        if (array_key_exists('smtp_url', $normalized)) {
            config(['mail.mailers.smtp.url' => $normalized['smtp_url']]);
        } elseif (($mailer ?? config('mail.default')) === 'smtp' && config('mail.mailers.smtp.url') !== null) {
            config(['mail.mailers.smtp.url' => null]);
        }

        foreach (['host', 'username', 'password', 'encryption'] as $key) {
            if (array_key_exists($key, $normalized) && $normalized[$key] !== null) {
                config(["mail.mailers.smtp.{$key}" => $normalized[$key]]);
            }
        }

        if (array_key_exists('port', $normalized) && $normalized['port'] !== null) {
            config(['mail.mailers.smtp.port' => $normalized['port']]);
        }

        foreach (['from_address' => 'address', 'from_name' => 'name'] as $source => $target) {
            if (array_key_exists($source, $normalized) && $normalized[$source] !== null) {
                config(["mail.from.{$target}" => $normalized[$source]]);
            }
        }

        if (array_key_exists('disable_delivery', $normalized) && $normalized['disable_delivery'] !== null) {
            config(['mail.disable_delivery' => $normalized['disable_delivery']]);
        }

        static::purgeResolvedMailer($mailer);
    }

    public static function purgeResolvedMailer(?string $mailer = null): void
    {
        if (app()->bound('mail.manager')) {
            app('mail.manager')->purge($mailer);
        }

        app()->forgetInstance('mailer');

        if (method_exists(app(), 'forgetResolvedInstance')) {
            app()->forgetResolvedInstance('mailer');
        }
    }

    protected static function normalizeSettings(array $settings): array
    {
        $normalized = [];

        if (Arr::exists($settings, 'mailer')) {
            $normalized['mailer'] = $settings['mailer'] ?: null;
        }

        if (Arr::exists($settings, 'smtp_url')) {
            $normalized['smtp_url'] = $settings['smtp_url'] ?: null;
        }

        if (Arr::exists($settings, 'host')) {
            $normalized['host'] = $settings['host'] ?: null;
        }

        if (Arr::exists($settings, 'port')) {
            $normalized['port'] = $settings['port'] !== null && $settings['port'] !== ''
                ? (int) $settings['port']
                : null;
        }

        if (Arr::exists($settings, 'username')) {
            $normalized['username'] = $settings['username'] ?: null;
        }

        if (Arr::exists($settings, 'password')) {
            $normalized['password'] = $settings['password'] ?: null;
        }

        if (Arr::exists($settings, 'encryption')) {
            $normalized['encryption'] = $settings['encryption'] ?: null;
        }

        if (Arr::exists($settings, 'from_address')) {
            $normalized['from_address'] = $settings['from_address'] ?: null;
        }

        if (Arr::exists($settings, 'from_name')) {
            $normalized['from_name'] = $settings['from_name'] ?: null;
        }

        if (Arr::exists($settings, 'disable_delivery')) {
            $normalized['disable_delivery'] = static::toBoolean($settings['disable_delivery']);
        }

        return $normalized;
    }

    protected static function toBoolean(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return null;
    }
}
