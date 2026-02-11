<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    // Auth actions
    const AUTH_LOGIN = 'auth.login';

    const AUTH_LOGIN_FAILED = 'auth.login_failed';

    const AUTH_LOGOUT = 'auth.logout';

    const AUTH_REGISTER = 'auth.register';

    const AUTH_PASSWORD_CHANGE = 'auth.password_change';

    const AUTH_PASSWORD_RESET = 'auth.password_reset';

    const AUTH_GOOGLE_LOGIN = 'auth.google_login';

    const AUTH_2FA_ENABLED = 'auth.2fa_enabled';

    const AUTH_2FA_DISABLED = 'auth.2fa_disabled';

    // Profile actions
    const PROFILE_UPDATE = 'profile.update';

    const PROFILE_DELETE = 'profile.delete';

    // API actions
    const API_KEY_GENERATED = 'api.key_generated';

    const API_KEY_DISABLED = 'api.key_disabled';

    const API_AUTH_FAILED = 'api.auth_failed';

    // Schedule actions
    const SCHEDULE_CREATE = 'schedule.create';

    const SCHEDULE_UPDATE = 'schedule.update';

    const SCHEDULE_DELETE = 'schedule.delete';

    const SCHEDULE_MEMBER_ADD = 'schedule.member_add';

    const SCHEDULE_MEMBER_REMOVE = 'schedule.member_remove';

    // Event actions
    const EVENT_CREATE = 'event.create';

    const EVENT_UPDATE = 'event.update';

    const EVENT_DELETE = 'event.delete';

    const EVENT_ACCEPT = 'event.accept';

    const EVENT_DECLINE = 'event.decline';

    // Sales actions
    const SALE_CHECKOUT = 'sale.checkout';

    const SALE_REFUND = 'sale.refund';

    const SALE_CHECKIN = 'sale.checkin';

    // Admin actions
    const ADMIN_PLAN_UPDATE = 'admin.plan_update';

    const ADMIN_QUEUE_ACTION = 'admin.queue_action';

    const ADMIN_PASSWORD_CONFIRMED = 'admin.password_confirmed';

    const ADMIN_PASSWORD_FAILED = 'admin.password_failed';

    const ADMIN_SESSION_CHANGED = 'admin.session_changed';

    // Payment actions
    const STRIPE_LINK = 'stripe.link';

    const STRIPE_UNLINK = 'stripe.unlink';

    /**
     * Sensitive fields that should be stripped from logged values.
     */
    private static array $sensitiveFields = [
        'password',
        'current_password',
        'password_confirmation',
        'token',
        'secret',
        'api_key',
        'google_token',
        'google_refresh_token',
        'facebook_token',
        'stripe_account_id',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Log an audit event. Silently fails so auditing never disrupts actual operations.
     */
    public static function log(
        string $action,
        ?int $userId = null,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $metadata = null,
    ): void {
        try {
            AuditLog::create([
                'user_id' => $userId,
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'old_values' => $oldValues ? self::filterSensitive($oldValues) : null,
                'new_values' => $newValues ? self::filterSensitive($newValues) : null,
                'ip_address' => request()->ip() ?? '0.0.0.0',
                'user_agent' => substr((string) request()->userAgent(), 0, 512),
                'metadata' => $metadata,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::warning('Audit logging failed: '.$e->getMessage());
        }
    }

    /**
     * Strip sensitive fields from values before logging.
     */
    private static function filterSensitive(array $values): array
    {
        foreach (self::$sensitiveFields as $field) {
            if (array_key_exists($field, $values)) {
                $values[$field] = '[REDACTED]';
            }
        }

        return $values;
    }
}
