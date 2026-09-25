<?php

namespace App\Services;

use App\Jobs\SendQueuedEmail;
use App\Mail\NotificationEmailConfirmation;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

/**
 * A schedule's shared notification address (issue #124): a team inbox that gets a copy of the
 * owner notifications, on top of each editor's own account email.
 *
 * It receives nothing until someone confirms it from the mailbox itself, and every copy carries a
 * link that removes it without signing in, so typing a stranger's address here cannot be
 * turned into a way to mail them.
 *
 * The send helpers are called by each notification's own send site AFTER that site's plan and
 * mail-transport checks, so the shared copy follows exactly the rules the editors' copies do.
 */
class NotificationEmailService
{
    public const VERIFY_TTL_DAYS = 7;

    /**
     * Confirmation emails go to an address the owner typed and carry text they chose (their name,
     * the schedule's), so each send is counted three ways: per schedule, per person asking, and
     * per address. The schedule bucket alone would let one account with many schedules (hosted
     * allows 50) mail a stranger over and over.
     *
     * @var array<string, array{0: int, 1: int}> bucket => [max attempts, decay seconds]
     */
    private const VERIFY_LIMITS = [
        'role' => [3, 3600],
        'user' => [10, 86400],
        'address' => [3, 86400],
    ];

    /**
     * The confirm link. Relative signature wrapped in app_url(): hosted serves the same route on
     * the app, tenant and custom-domain hosts, so an absolute signature would fail on all but one.
     */
    public static function verifyUrl(Role $role): string
    {
        return app_url(URL::temporarySignedRoute(
            'notification_email.show_confirm',
            now()->addDays(self::VERIFY_TTL_DAYS),
            ['role' => UrlUtils::encodeId($role->id), 'token' => $role->notificationEmailHash()],
            false
        ));
    }

    /**
     * The link that removes the address. Unsigned: it never expires (an old email must still let
     * the mailbox opt out), so a signature would add nothing the token does not already prove, and
     * a query string a mail client mangled must not be able to leave the address subscribed.
     */
    public static function unsubscribeUrl(Role $role): string
    {
        return app_url(route(
            'notification_email.show_unsubscribe',
            ['role' => UrlUtils::encodeId($role->id), 'token' => $role->notificationEmailHash()],
            false
        ));
    }

    /**
     * Whether this install can deliver mail at all. Hosted always can; a selfhost install on the
     * log or array mailer cannot, so there is no confirmation to send.
     */
    public static function canSend(): bool
    {
        if (config('app.hosted')) {
            return true;
        }

        return ! in_array(config('mail.default'), ['log', 'array'], true);
    }

    /**
     * Email the confirm link to the current address.
     *
     * @return string 'sent', 'rate_limited', 'no_mailer' or 'failed'
     */
    public function sendVerification(Role $role, ?User $requester = null): string
    {
        if (blank($role->notification_email) || $role->hasVerifiedNotificationEmail()) {
            return 'failed';
        }

        if (! self::canSend()) {
            return 'no_mailer';
        }

        $buckets = [
            'role' => 'notification-email-verify:'.$role->id,
            'address' => 'notification-email-verify-addr:'.sha1(mb_strtolower(trim($role->notification_email))),
        ];
        if ($requester) {
            $buckets['user'] = 'notification-email-verify-user:'.$requester->id;
        }

        foreach ($buckets as $bucket => $key) {
            if (RateLimiter::tooManyAttempts($key, self::VERIFY_LIMITS[$bucket][0])) {
                return 'rate_limited';
            }
        }

        foreach ($buckets as $bucket => $key) {
            RateLimiter::hit($key, self::VERIFY_LIMITS[$bucket][1]);
        }

        try {
            // Sent now on the platform mailer: someone is waiting on the settings page, and the
            // schedule's own SMTP may not be set up (or may be what is failing).
            Mail::to($role->notification_email)
                ->locale(self::locale($role))
                ->send(new NotificationEmailConfirmation($role, self::verifyUrl($role), $requester?->name));
        } catch (\Throwable $e) {
            report($e);

            return 'failed';
        }

        AuditService::log(AuditService::SCHEDULE_NOTIFICATION_EMAIL_SENT, $requester?->id, 'Role', $role->id);

        return 'sent';
    }

    /**
     * The flash a sendVerification() result becomes on the settings page, as [session key, text].
     *
     * @return array{0: string, 1: string}
     */
    public static function verificationFlash(string $result, Role $role): array
    {
        return match ($result) {
            'sent' => ['message', __('messages.notification_email_confirmation_sent', ['email' => $role->notification_email])],
            'rate_limited' => ['error', __('messages.notification_email_resend_wait')],
            'no_mailer' => ['error', __('messages.notification_email_no_mailer')],
            default => ['error', __('messages.notification_email_send_failed')],
        };
    }

    /**
     * Send a Notification (NewRequestsNotification, NewPollOptionsNotification) to the shared
     * address, on demand. Returns whether it was sent.
     *
     * @param  string|array<int, string>  $types
     * @param  iterable<User>  $notifiedEditors  the editors this same notice went to, for dedupe
     */
    public function sendNotification(Role $role, string|array $types, BaseNotification $notification, iterable $notifiedEditors = []): bool
    {
        $address = $role->getNotificationEmailWanting($types, $notifiedEditors);

        if (! $address) {
            return false;
        }

        try {
            Notification::route('mail', $address)->notify($notification->locale(self::locale($role)));
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return true;
    }

    /**
     * Queue a Mailable that uses SendsToNotificationEmail to the shared address, through the same
     * SendQueuedEmail path (and so the same schedule mailer) as the editors' copies. Returns
     * whether it was queued.
     *
     * @param  string|array<int, string>  $types
     * @param  iterable<User>  $notifiedEditors  the editors this same notice went to, for dedupe
     */
    public function sendMailable(Role $role, string|array $types, Mailable $mailable, iterable $notifiedEditors = []): bool
    {
        $address = $role->getNotificationEmailWanting($types, $notifiedEditors);

        if (! $address) {
            return false;
        }

        try {
            $mailable->toNotificationEmail(self::unsubscribeUrl($role));

            SendQueuedEmail::dispatch($mailable, $address, $role->id, self::locale($role));
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return true;
    }

    /**
     * A shared mailbox has no user and so no language of its own; the schedule's is the best guess.
     */
    public static function locale(Role $role): string
    {
        return is_valid_language_code($role->language_code) ? $role->language_code : config('app.locale');
    }
}
