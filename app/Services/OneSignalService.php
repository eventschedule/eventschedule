<?php

namespace App\Services;

use App\Jobs\SendQueuedPush;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends OneSignal web-push notifications that mirror the app's email
 * notifications. Push is an opt-in, off-by-default channel: when
 * ONESIGNAL_APP_ID / ONESIGNAL_REST_API_KEY are unset, every method here is a
 * no-op and no external calls are made.
 *
 * Recipient identity (OneSignal stores the browser subscriptions; we only
 * target them):
 *   - Logged-in users  -> external_id alias = UrlUtils::encodeId($user->id)
 *   - Account-less guests (ticket buyers / RSVP) -> es_email alias = sha256(lower(email))
 *
 * Structurally modeled on WebhookService (Pro-gate + DB::afterCommit + queued
 * send) and SmsService (isConfigured + direct Http call to a fixed host).
 */
class OneSignalService
{
    const ALIAS_EXTERNAL_ID = 'external_id';

    const ALIAS_EMAIL = 'es_email';

    const API_URL = 'https://api.onesignal.com/notifications';

    public static function isConfigured(): bool
    {
        return ! empty(config('services.onesignal.app_id'))
            && ! empty(config('services.onesignal.rest_api_key'));
    }

    /**
     * Whether push may be sent in this context. Schedule-scoped notifications
     * are Pro-gated via the owning Role (selfhost: isPro() is always true);
     * account-scoped notifications (no role) only require configuration.
     */
    public static function isEnabled(?Role $role = null): bool
    {
        if (! self::isConfigured()) {
            return false;
        }

        if ($role) {
            if (is_demo_role($role)) {
                return false;
            }

            return $role->isPro();
        }

        return true;
    }

    /**
     * The OneSignal external_id alias value for a user (stable, URL-safe).
     */
    public static function externalIdForUser(User|int $user): string
    {
        return UrlUtils::encodeId($user instanceof User ? $user->id : $user);
    }

    /**
     * The OneSignal es_email alias value for an account-less guest's email.
     * Must match the value set client-side via OneSignal.User.addAlias().
     */
    public static function emailAlias(string $email): string
    {
        return hash('sha256', strtolower(trim($email)));
    }

    /**
     * Queue a push to a logged-in user (targeted by external_id).
     *
     * @param  array  $payload  ['title_key','title_params'?,'body_key','body_params'?,'url'?,'options'?]
     */
    public static function pushToUser(User $user, array $payload, ?Role $role = null, bool $force = false): void
    {
        if (! self::isEnabled($role)) {
            return;
        }

        if (DemoService::isDemoUser($user)) {
            return;
        }

        // $force is used by the "send test push" action, which is an explicit
        // user request; normal notifications respect the device opt-in flag.
        if (! $force && ! self::userWantsPush($user)) {
            return;
        }

        self::queue(
            self::ALIAS_EXTERNAL_ID,
            [self::externalIdForUser($user)],
            $payload,
            $user->language_code ?: config('app.locale'),
            $role?->id,
        );
    }

    /**
     * Queue a push to an account-less guest (targeted by hashed email alias).
     */
    public static function pushToGuestEmail(string $email, string $locale, array $payload, ?Role $role = null): void
    {
        if (! self::isEnabled($role)) {
            return;
        }

        if (self::isTestEmail($email)) {
            return;
        }

        self::queue(
            self::ALIAS_EMAIL,
            [self::emailAlias($email)],
            $payload,
            $locale ?: config('app.locale'),
            $role?->id,
        );
    }

    /**
     * Dispatch the queued job, deferring until after the surrounding DB
     * transaction commits (mirrors WebhookService) so a rollback can't leave a
     * queued push behind.
     */
    protected static function queue(string $aliasField, array $aliasValues, array $payload, string $locale, ?int $roleId): void
    {
        $dispatch = function () use ($aliasField, $aliasValues, $payload, $locale, $roleId) {
            SendQueuedPush::dispatch($aliasField, $aliasValues, $payload, $locale, $roleId);
        };

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($dispatch);
        } else {
            $dispatch();
        }
    }

    /**
     * Perform the actual OneSignal REST call. Invoked from SendQueuedPush after
     * the recipient's locale has been applied and the title/body resolved.
     * Returns true on success. Never throws to the caller.
     */
    public static function send(string $aliasField, array $aliasValues, string $title, string $body, ?string $url, array $options = [], ?int $roleId = null): bool
    {
        if (! self::isConfigured() || empty($aliasValues)) {
            return false;
        }

        $payload = [
            'app_id' => config('services.onesignal.app_id'),
            'target_channel' => 'push',
            'include_aliases' => [$aliasField => array_values($aliasValues)],
            'headings' => ['en' => $title],
            'contents' => ['en' => $body],
        ];

        if (! empty($url)) {
            $payload['url'] = $url;
        }

        // Optional schedule/event branding for the notification.
        if (! empty($options['icon'])) {
            $payload['chrome_web_icon'] = $options['icon'];
        }
        if (! empty($options['image'])) {
            $payload['chrome_web_image'] = $options['image'];
            $payload['big_picture'] = $options['image'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key '.config('services.onesignal.rest_api_key'),
                'Content-Type' => 'application/json',
            ])->post(self::API_URL, $payload);

            if ($response->successful()) {
                UsageTrackingService::track(UsageTrackingService::PUSH_NOTIFICATION, $roleId ?? 0);

                return true;
            }

            // A 400 with "no subscribers"/all-recipients-invalid is normal when
            // the targeted person never opted into push; log quietly, don't report.
            Log::info('OneSignal push not delivered', [
                'status' => $response->status(),
                'body' => $response->body(),
                'role_id' => $roleId,
            ]);

            return false;
        } catch (\Throwable $e) {
            // Network/transport failure - report to Sentry, never surface to users.
            report($e);

            return false;
        }
    }

    /**
     * Whether the user has enabled push delivery on at least one device.
     * Stored in users.push_settings JSON (see migration).
     */
    protected static function userWantsPush(User $user): bool
    {
        $settings = $user->push_settings ?? [];

        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?: [];
        }

        return ! empty($settings['enabled']);
    }

    /**
     * Block reserved/test domains, mirroring EmailService::isTestEmail().
     */
    protected static function isTestEmail(string $email): bool
    {
        $email = strtolower($email);

        foreach (['@example.com', '@example.org', '@example.net', '@test.com', '@test.org', '@test.net', '@localhost'] as $domain) {
            if (str_contains($email, $domain)) {
                return true;
            }
        }

        return false;
    }
}
