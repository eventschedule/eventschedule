<?php

namespace App\Http\Controllers;

use App\Jobs\SendQueuedEmail;
use App\Mail\SubscriptionConfirmation;
use App\Models\NewsletterUnsubscribe;
use App\Models\Role;
use App\Models\RoleSubscriber;
use App\Models\User;
use App\Rules\NoFakeEmail;
use App\Utils\HoneypotUtils;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Account-less audience capture for a schedule.
 *
 * Modelled on WaitlistController::join(), which is the repo's existing precedent for taking a bare
 * name and email from a signed-out guest. The differences are deliberate and all point the same
 * way: this endpoint is reachable on every schedule page rather than only on a sold-out event, so
 * it is a far more attractive target.
 */
class RoleSubscriberController extends Controller
{
    /**
     * How many distinct schedules one email address may be subscribed to per hour, platform-wide.
     *
     * The route throttle is keyed on IP and is shared with every other throttled guest route in the
     * group, so it does nothing to stop a distributed attempt to sign one victim up everywhere. The
     * confirmation email makes each subscription an outbound message, so this is the limit that
     * actually bounds the damage.
     */
    private const PER_EMAIL_HOURLY_LIMIT = 5;

    public function store(Request $request, $subdomain)
    {
        // Honeypot first, before validation, so a bot learns nothing from field-level errors.
        // 200 rather than an error status on the JSON path: the caller throws a generic
        // "Request failed" on !response.ok and only renders data.message on a 200.
        if (HoneypotUtils::isTripped($request)) {
            return $this->respond($request, __('messages.invalid_request'), false);
        }

        // Validated by hand rather than $request->validate(), because a ValidationException is
        // INVISIBLE on these surfaces and actively harmful on one of them. Guest layouts toast
        // session('error') / session('message') and render no per-field errors, so a rejected
        // address would redirect back showing nothing and the form would look dead - and on the
        // guest event page, event/show-guest.blade.php keys on $errors->any() to force-open the
        // RSVP / ticket-purchase modal, so a bad email address would pop the wrong dialog.
        //
        // Same rule as the honeypot bail below: match the bail to what the surface renders.
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
        ]);

        if ($validator->fails()) {
            return $this->respond($request, $validator->errors()->first('email')
                ?: __('messages.invalid_request'), false);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($role->is_deleted || is_demo_role($role)) {
            abort(404);
        }

        $email = strtolower(trim($request->email));

        // Deliberately the SAME response as success. A distinct "slow down" message would leak
        // that the address exists, and the point of the limit is to bound outbound mail, not to
        // tell the caller anything.
        $rateKey = 'audience-join:'.sha1($email);
        if (RateLimiter::tooManyAttempts($rateKey, self::PER_EMAIL_HOURLY_LIMIT)) {
            return $this->respond($request, __('messages.subscription_check_your_email'), true);
        }

        $existing = RoleSubscriber::where('role_id', $role->id)->where('email', $email)->first();

        if ($existing) {
            $suppressed = NewsletterUnsubscribe::where('role_id', $role->id)
                ->where('email', $email)
                ->exists();

            // Confirmed and not suppressed: nothing to do, and say nothing different about it.
            // Returning a distinct "already subscribed" string is what makes
            // WaitlistController::join() a membership oracle, and this endpoint is reachable on
            // every schedule page.
            //
            // Confirmed but SUPPRESSED is a different case and must not fall in here: somebody who
            // unsubscribed and later filled the form in again has to have a way back, and the form
            // itself deliberately never lifts a suppression. Re-sending the confirmation is that
            // way back - it costs one email to an address that already asked for it once.
            if ($existing->isConfirmed() && ! $suppressed) {
                return $this->respond($request, __('messages.subscription_check_your_email'), true);
            }

            RateLimiter::hit($rateKey, 3600);
            $this->sendConfirmation($role, $existing);

            return $this->respond($request, __('messages.subscription_check_your_email'), true);
        }

        try {
            $subscriber = RoleSubscriber::create([
                'role_id' => $role->id,
                'email' => $email,
                'name' => $request->filled('name') ? strip_tags($request->name) : null,
                'locale' => app()->getLocale(),
                'source' => $request->input('source') === 'modal' ? 'guest_modal' : 'guest_panel',
                'token' => RoleSubscriber::newToken(),
                'ip_address' => $request->ip(),
                // confirm_token is issued by sendConfirmation(), so every send gets a fresh one.
            ]);
        } catch (QueryException $e) {
            // Lost a race with a concurrent identical submit. Indistinguishable from success, and
            // it genuinely is one.
            if (($e->errorInfo[1] ?? null) == 1062) {
                return $this->respond($request, __('messages.subscription_check_your_email'), true);
            }

            report($e);

            return $this->respond($request, __('messages.invalid_request'), false);
        }

        RateLimiter::hit($rateKey, 3600);
        $this->sendConfirmation($role, $subscriber);

        return $this->respond($request, __('messages.subscription_check_your_email'), true);
    }

    /**
     * Confirm. Until this runs the row exists but App\Services\AudienceResolver will not mail it.
     */
    /**
     * Confirm, from the single-use confirm_token.
     *
     * Deliberately NOT keyed on the permanent unsubscribe token. This is a GET that mutates, and
     * mail gateways prefetch links, so a permanent confirm URL would let merely receiving an old
     * confirmation email resurrect a subscription somebody had since cancelled.
     */
    public function confirm(Request $request, string $token)
    {
        $subscriber = RoleSubscriber::where('confirm_token', $token)->with('role')->first();

        // Not firstOrFail(). The token is single-use and is nulled by the confirm below, so the
        // ROW CANNOT BE FOUND on a replay - which means a bare 404 was the reward for clicking
        // your own confirmation link twice, or for clicking it at all after a corporate mail
        // gateway (Safe Links, Proofpoint) prefetched and burned it. An expired-link page cannot
        // distinguish "already used" from "garbage", and does not need to: the copy covers both.
        if (! $subscriber) {
            return $this->linkExpired();
        }

        $role = $subscriber->role;

        if (! $role || $role->is_deleted) {
            abort(404);
        }

        $this->applyLocale($subscriber, $role);

        // Burn the token in the same write that confirms. Everything below is now unreachable by
        // a replay of this URL.
        $subscriber->forceFill([
            'confirmed_at' => $subscriber->confirmed_at ?: now(),
            'confirm_token' => null,
        ])->save();

        // Confirming is an unambiguous affirmative act with proof of mailbox possession, so it
        // lifts a previous opt-out for THIS schedule. The public form never does.
        NewsletterUnsubscribe::where('role_id', $role->id)
            ->where('email', $subscriber->email)
            ->delete();

        return view('subscriber.confirmed', [
            'role' => $role,
            'subscriber' => $subscriber,
        ]);
    }

    public function showUnsubscribe(Request $request, string $token)
    {
        $subscriber = RoleSubscriber::where('token', $token)->with('role')->firstOrFail();

        $this->applyLocale($subscriber, $subscriber->role);

        return view('subscriber.unsubscribe', [
            'role' => $subscriber->role,
            'subscriber' => $subscriber,
            'done' => false,
            'all' => false,
        ]);
    }

    /**
     * One-click unsubscribe (RFC 8058). CSRF-exempt in bootstrap/app.php, because a mail client's
     * one-click POST carries no session and no token.
     */
    public function unsubscribe(Request $request, string $token)
    {
        $subscriber = RoleSubscriber::where('token', $token)->with('role')->firstOrFail();
        $role = $subscriber->role;

        $this->applyLocale($subscriber, $role);

        // Any confirmation link still sitting in an inbox dies here, so it cannot be replayed -
        // by the person or by their mail scanner - to undo what they just asked for.
        $subscriber->forceFill(['confirm_token' => null])->save();

        $all = $request->boolean('all');

        if ($all) {
            $this->unsubscribeEverywhere($subscriber->email);
        } elseif ($role) {
            $this->suppress($role->id, $subscriber->email);
        }

        return view('subscriber.unsubscribe', [
            'role' => $role,
            'subscriber' => $subscriber,
            'done' => true,
            'all' => $all,
        ]);
    }

    /**
     * Owner-facing removal from the followers/audience tab.
     */
    public function remove(Request $request, $subdomain, $hash)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        // isEditor, NOT isMember: member() is ['owner','admin','viewer'], and a viewer is who you
        // hand the door to, not who prunes the mailing list. Reading the tab on isMember is right;
        // destroying rows is not. Matches AppointmentTypeController::destroy and friends.
        if (! auth()->user() || ! auth()->user()->isEditor($subdomain)) {
            abort(403);
        }

        // Ids visible to users are encoded, per the repo rule and every sibling route on this page.
        $id = \App\Utils\UrlUtils::decodeId($hash);

        RoleSubscriber::where('role_id', $role->id)->where('id', $id)->delete();

        return back()->with('message', __('messages.deleted_subscriber'));
    }

    /**
     * One locale rule for every page in this flow.
     *
     * sendConfirmation() dispatches the mail in $subscriber->locale, so resolving these pages off
     * the ROLE meant a visitor browsing in French got a French email and an English landing page.
     * The address was captured with a language attached; use it, and keep the schedule's own
     * language as the fallback it always was.
     */
    private function applyLocale(?RoleSubscriber $subscriber, ?Role $role): void
    {
        foreach ([$subscriber?->locale, $role?->language_code] as $candidate) {
            if ($candidate && is_valid_language_code($candidate)) {
                app()->setLocale($candidate);

                return;
            }
        }
    }

    /** A confirm link that has already been spent. Deliberately reveals nothing about the row. */
    private function linkExpired()
    {
        return response()->view('subscriber.link-expired', [], 410);
    }

    private function sendConfirmation(Role $role, RoleSubscriber $subscriber): void
    {
        // A fresh single-use token per send, so an earlier confirmation email stops working the
        // moment a newer one goes out.
        $subscriber->forceFill(['confirm_token' => RoleSubscriber::newToken()])->save();

        $mailable = new SubscriptionConfirmation(
            $role,
            $subscriber,
            route('subscriber.confirm', ['token' => $subscriber->confirm_token]),
            route('subscriber.show_unsubscribe', ['token' => $subscriber->token]),
        );

        // Queued, never Mail::send(). Selfhost ships QUEUE_CONNECTION=sync, and a synchronous send
        // here would turn the endpoint's response time into an oracle for whether the address was
        // already known, on top of blocking the request on SMTP.
        //
        // roleId is passed so it goes out from the schedule's own SMTP where one is configured.
        SendQueuedEmail::dispatch(
            $mailable,
            $subscriber->email,
            $role->id,
            $subscriber->locale ?: ($role->language_code ?: app()->getLocale()),
        );
    }

    /** The one suppression list, shared with the newsletter composer. */
    private function suppress(int $roleId, string $email): void
    {
        NewsletterUnsubscribe::firstOrCreate(
            ['role_id' => $roleId, 'email' => strtolower($email)],
            ['unsubscribed_at' => now()],
        );
    }

    /**
     * Stop everything for this address, across every schedule it reaches.
     *
     * Without this a fan following six venues needs six links, and what they will actually do is
     * press Report spam once - against a From address shared by every schedule on the platform.
     */
    private function unsubscribeEverywhere(string $email): void
    {
        $email = strtolower($email);

        RoleSubscriber::where('email', $email)
            ->pluck('role_id')
            ->each(fn ($roleId) => $this->suppress($roleId, $email));

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->roles()->wherePivot('level', 'follower')
                ->pluck('roles.id')
                ->each(fn ($roleId) => $this->suppress($roleId, $email));
        }
    }

    /** One shape for both the async modal and the no-JS form. */
    private function respond(Request $request, string $message, bool $success)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => $success, 'message' => $message]);
        }

        if (! $success) {
            // NOT session('error'), and not withInput().
            //
            // event/show-guest.blade.php force-opens the RSVP / ticket-purchase form when
            // `session('error') || $errors->any()` - which is why this controller validates by
            // hand instead of throwing ValidationException. But the error FLASH lands in the same
            // condition, so a mistyped address in the subscribe panel reopened the ticket form,
            // and hidePanelsBelow() then hid the panel the visitor was actually using. A key of
            // its own keeps the toast and leaves the page alone.
            //
            // The address comes back under its own key too: old('email') is shared with the
            // ticket and RSVP forms on that same page, so repopulating through withInput() would
            // cross-fill them.
            return back()
                ->with('subscribe_error', $message)
                ->with('subscribe_email', (string) $request->input('email'));
        }

        return back()->with('message', $message);
    }
}
