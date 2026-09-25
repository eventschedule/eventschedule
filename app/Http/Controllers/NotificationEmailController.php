<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\AuditService;
use App\Services\NotificationEmailService;
use App\Utils\HoneypotUtils;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * A schedule's shared notification address (issue #124): the confirm and remove links sent to the
 * mailbox, and the resend button on the settings page.
 *
 * The mailbox links need no login, because the people reading a shared inbox may have no account.
 * Their token is an HMAC of the schedule and its current address (Role::notificationEmailHash()),
 * so a link sent to a previous address can do nothing to the current one. The confirm link is also
 * signed, which is what gives it an expiry; the unsubscribe link never expires, so it rests on the
 * token alone.
 *
 * Each is a GET that renders a button and a POST that acts. Corporate mail gateways (Safe Links,
 * Proofpoint) fetch every URL in an inbound message, so a GET that acted would confirm an address
 * nobody at it agreed to, or remove one nobody asked to remove. Same split as /int/u and /sub/u.
 */
class NotificationEmailController extends Controller
{
    public function resend(Request $request, string $subdomain, NotificationEmailService $service)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        // settings_tab, not only the section hash: the page otherwise reopens whichever Settings
        // tab was last used, and the button that brought them here is on Notifications.
        $redirect = redirect(route('role.edit', ['subdomain' => $role->subdomain, 'settings_tab' => 'notifications']).'#section-settings');

        if (blank($role->notification_email) || $role->hasVerifiedNotificationEmail()) {
            return $redirect;
        }

        [$key, $message] = NotificationEmailService::verificationFlash($service->sendVerification($role, auth()->user()), $role);

        return $redirect->with($key, $message);
    }

    public function showConfirm(Request $request, string $role, string $token)
    {
        $schedule = $this->resolveSigned($request, $role, $token);

        if (! $schedule) {
            return view('notification-email.confirm', ['state' => 'invalid', 'role' => null]);
        }

        return view('notification-email.confirm', [
            'state' => $schedule->hasVerifiedNotificationEmail() ? 'done' : 'confirm',
            'role' => $schedule,
        ]);
    }

    public function confirm(Request $request, string $role, string $token)
    {
        if (HoneypotUtils::isTripped($request)) {
            throw ValidationException::withMessages([
                'email' => __('messages.invalid_request'),
            ]);
        }

        $schedule = $this->resolveSigned($request, $role, $token);

        if (! $schedule) {
            return view('notification-email.confirm', ['state' => 'invalid', 'role' => null]);
        }

        if (! $schedule->hasVerifiedNotificationEmail()) {
            $schedule->notification_email_verified_at = now();
            $schedule->save();

            AuditService::log(AuditService::SCHEDULE_NOTIFICATION_EMAIL_CONFIRMED, null, 'Role', $schedule->id);
        }

        return view('notification-email.confirm', ['state' => 'done', 'role' => $schedule]);
    }

    /**
     * A link that matches nothing renders the "done" page with no schedule name: the id in the URL
     * is only a decoded integer until the token proves the link, and naming whatever schedule it
     * happens to point at would let anyone read every schedule's name by counting.
     */
    public function showUnsubscribe(Request $request, string $role, string $token)
    {
        $schedule = $this->resolveToken($role, $token);

        return view('notification-email.unsubscribe', [
            'done' => ! $schedule,
            'role' => $schedule,
        ]);
    }

    /**
     * One-click unsubscribe (RFC 8058): CSRF-exempt in bootstrap/app.php, because a mail client's
     * one-click POST carries no session and no token.
     *
     * A stale link (the address was already removed or changed) answers 200 with the same "no
     * longer receiving" page rather than an error: a failed unsubscribe is what turns into a spam
     * complaint. That page is only ever true, because the token is the whole check: a link whose
     * token matches always removes the address, however its query string was mangled on the way.
     */
    public function unsubscribe(Request $request, string $role, string $token)
    {
        $schedule = $this->resolveToken($role, $token);

        if ($schedule) {
            $schedule->clearNotificationEmail();
            $schedule->save();

            AuditService::log(AuditService::SCHEDULE_NOTIFICATION_EMAIL_REMOVED, null, 'Role', $schedule->id);
        }

        return view('notification-email.unsubscribe', [
            'done' => true,
            'role' => $schedule,
        ]);
    }

    /**
     * The confirm link's schedule: the token must match AND the signature must be intact and
     * unexpired. Relative, because the link was signed without its host
     * (NotificationEmailService::verifyUrl()).
     */
    private function resolveSigned(Request $request, string $encodedId, string $token): ?Role
    {
        if (! $request->hasValidSignature(false)) {
            return null;
        }

        return $this->resolveToken($encodedId, $token);
    }

    /**
     * The schedule whose CURRENT shared address this token was made for, or null. Only once the
     * token matches does it switch to the schedule's language, which is the one the email was
     * written in.
     */
    private function resolveToken(string $encodedId, string $token): ?Role
    {
        $id = UrlUtils::decodeId($encodedId);
        $role = $id ? Role::find($id) : null;
        $expected = $role?->notificationEmailHash();

        if ($expected === null || ! hash_equals($expected, $token)) {
            return null;
        }

        app()->setLocale(NotificationEmailService::locale($role));

        return $role;
    }
}
