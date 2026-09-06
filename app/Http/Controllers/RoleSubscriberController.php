<?php

namespace App\Http\Controllers;

use App\Jobs\SendQueuedEmail;
use App\Mail\SubscriptionConfirmation;
use App\Models\NewsletterUnsubscribe;
use App\Models\Role;
use App\Models\RoleSubscriber;
use App\Models\RoleUser;
use App\Models\User;
use App\Rules\NoFakeEmail;
use App\Services\AuditService;
use App\Utils\HoneypotUtils;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    /**
     * How many confirmation emails one schedule may generate per day, from all addresses.
     *
     * The per-email limit above keys on the literal address, so subaddressing walks straight past
     * it: victim+1@, victim+2@ ... each get their own bucket AND their own row (the unique index is
     * (role_id, email)), while every message lands in one inbox. Canonicalising addresses is not
     * the fix - plus-tags are meaningful at some providers and dots are only special at Gmail, so
     * folding them would silently merge distinct people.
     *
     * A ceiling on the SCHEDULE is the lever that actually bounds it: an attacker can still flood,
     * but only until the schedule's daily budget is spent, and the budget is per schedule so one
     * target cannot exhaust another's. Sized well above real use - a schedule taking 500 genuine
     * new subscribers in a day is doing extremely well - so this is a backstop, not a quota.
     */
    private const PER_ROLE_DAILY_LIMIT = 500;

    public function store(Request $request, $subdomain)
    {
        // Honeypot first, before validation, so a bot learns nothing from field-level errors.
        // 200 rather than an error status on the JSON path: the caller throws a generic
        // "Request failed" on !response.ok and only renders data.message on a 200.
        if (HoneypotUtils::isTripped($request)) {
            return $this->respond($request, $subdomain, __('messages.invalid_request'), false);
        }

        // Validated by hand rather than $request->validate(), because a ValidationException is
        // INVISIBLE on these surfaces and actively harmful on one of them. Guest layouts toast
        // session('error') / session('message') and render no per-field errors, so a rejected
        // address would redirect back showing nothing and the form would look dead - and on the
        // guest event page, event/show-guest.blade.php keys on $errors->any() to force-open the
        // RSVP / ticket-purchase modal, so a bad email address would pop the wrong dialog.
        //
        // Same rule as the honeypot bail below: match the bail to what the surface renders.

        // strip_tags BEFORE validating, not at write time: 'required' is satisfied by any non-empty
        // string, so "<b></b>" would pass the rule and then be stored as '' - a name the form
        // insists on and the database does not have. Trimming here also means max:255 measures what
        // is actually kept. Merging is safe on a field this endpoint always expects: an absent name
        // becomes '', which 'required' rejects exactly as a missing key would.
        //
        // is_string() is load-bearing, NOT defensive tidiness. input() hands back `name[]=x` as an
        // array untouched, this runs BEFORE the validator so the 'string' rule cannot protect it,
        // and `(string) []` raises "Array to string conversion" - which HandleExceptions promotes
        // to an ErrorException, i.e. a 500 on a public endpoint. Exactly the bug
        // test_an_array_email_is_rejected_rather_than_fatal exists for, and the same guard respond()
        // already uses on the address below. Reachable in the query string too (`?name[]=x`), since
        // input() unions query->all(). Anything non-string becomes '' and is rejected as missing.
        $submittedName = $request->input('name');
        $request->merge(['name' => is_string($submittedName) ? trim(strip_tags($submittedName)) : '']);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
        ], [
            // Spelled out because there is no resources/lang/*/validation.php in this repo, so
            // Laravel's own "The name field is required." would be English in every locale - and
            // now that the field is required, this is the one validation message a real visitor
            // actually hits. Only .required is worth a key: a 255-character name falls through to
            // the framework string exactly as every email failure already does.
            'name.required' => __('messages.subscribe_name_required'),
        ]);

        if ($validator->fails()) {
            // Email first, then name - the order the fields appear in on both surfaces, so someone
            // fixing them top-down hears about the first one they can see. Neither may fall through
            // to an empty string, hence the invalid_request backstop.
            $failedField = $validator->errors()->has('email') ? 'email' : 'name';

            return $this->respond($request, $subdomain, $validator->errors()->first('email')
                ?: ($validator->errors()->first('name') ?: __('messages.invalid_request')), false, $failedField);
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
        $roleKey = 'audience-join-role:'.$role->id;

        if (RateLimiter::tooManyAttempts($rateKey, self::PER_EMAIL_HOURLY_LIMIT)
            || RateLimiter::tooManyAttempts($roleKey, self::PER_ROLE_DAILY_LIMIT)) {
            return $this->respond($request, $subdomain, __('messages.subscription_check_your_email'), true);
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
                return $this->respond($request, $subdomain, __('messages.subscription_check_your_email'), true);
            }

            RateLimiter::hit($rateKey, 3600);
            RateLimiter::hit($roleKey, 86400);
            $this->sendConfirmation($role, $existing);

            return $this->respond($request, $subdomain, __('messages.subscription_check_your_email'), true);
        }

        try {
            $subscriber = RoleSubscriber::create([
                'role_id' => $role->id,
                'email' => $email,
                // Already stripped and trimmed above, and validated non-empty.
                'name' => $request->name,
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
                return $this->respond($request, $subdomain, __('messages.subscription_check_your_email'), true);
            }

            report($e);

            return $this->respond($request, $subdomain, __('messages.invalid_request'), false);
        }

        RateLimiter::hit($rateKey, 3600);
        RateLimiter::hit($roleKey, 86400);
        $this->sendConfirmation($role, $subscriber);

        return $this->respond($request, $subdomain, __('messages.subscription_check_your_email'), true);
    }

    /**
     * The confirm page: a GET that renders a button. The POST below is what mutates.
     *
     * Deliberately NOT a GET that confirms. A single-use token stops an OLD link being replayed,
     * which is what the previous design guarded and what
     * RoleSubscriberTest::test_a_replayed_confirm_link_is_expired pins - but it cannot stop a
     * corporate mail gateway (Defender Safe Links, Proofpoint, Barracuda) fetching the CURRENT
     * link the moment it lands. That fetch used to confirm the subscription AND delete the
     * recipient's newsletter_unsubscribes row with no human ever seeing the page.
     *
     * Anyone can put any address into the public form, and newsletter_unsubscribes is the SHARED
     * suppression list (NewsletterTrackingController writes it, NewsletterService reads it), so
     * that made an unauthenticated caller able to erase a stranger's newsletter opt-out by proxy -
     * against an address that had never used this feature at all. /sub/u/* is already a two-step
     * for the mirror-image reason; this now matches it.
     */
    public function showConfirm(Request $request, string $token)
    {
        [$subscriber, $role] = $this->resolveConfirmToken($token);

        if (! $subscriber) {
            return $this->linkExpired();
        }

        return view('subscriber.confirmed', [
            'role' => $role,
            'subscriber' => $subscriber,
            'done' => false,
        ]);
    }

    /**
     * Confirm, from the single-use confirm_token. POST only - see showConfirm() for why.
     *
     * Deliberately NOT keyed on the permanent unsubscribe token: a permanent confirm URL would let
     * merely receiving an old confirmation email resurrect a subscription somebody had cancelled.
     */
    public function confirm(Request $request, string $token)
    {
        [$subscriber, $role] = $this->resolveConfirmToken($token);

        if (! $subscriber) {
            return $this->linkExpired();
        }

        // Burn the token in the same write that confirms. Everything below is now unreachable by
        // a replay of this URL.
        $subscriber->forceFill([
            'confirmed_at' => $subscriber->confirmed_at ?: now(),
            'confirm_token' => null,
        ])->save();

        // Confirming is an unambiguous affirmative act with proof of mailbox possession, so it
        // lifts a previous opt-out for THIS schedule. The public form never does. Reachable only
        // from the POST above, so "possession" now means a person pressed a button rather than
        // anything that merely dereferenced a URL.
        NewsletterUnsubscribe::where('role_id', $role->id)
            ->where('email', $subscriber->email)
            ->delete();

        $user = $this->linkAccount($role, $subscriber);

        // Post/redirect/get. This method has just burned confirm_token, so rendering the view
        // straight from the POST meant the reward for pressing F5 on "you are on the list" was a
        // 410 - and, now that page carries a password form, losing that form for good.
        //
        // The state goes in the SESSION rather than the URL. The only durable token this subscriber
        // has is the UNSUBSCRIBE one, which ships in every List-Unsubscribe header and is
        // dereferenced by mail gateways, so it must never be a credential for setting a password.
        $request->session()->put('subscriber_confirmed', $this->claimState($role, $subscriber, $user));

        return redirect()->route('subscriber.confirmed');
    }

    /**
     * "You are on the list" - the GET half of confirm()'s redirect.
     *
     * Reads the session rather than a token, so a refresh keeps working for as long as the session
     * does. With nothing there (a bookmark, a new session, somebody typing the URL) there is no
     * schedule to name and no subscription to report, so send them to the front page instead of
     * rendering a content-free version of this.
     */
    public function confirmed(Request $request)
    {
        $state = $request->session()->get('subscriber_confirmed');

        if (! is_array($state) || empty($state['role_id'])) {
            return redirect('/');
        }

        $role = Role::find($state['role_id']);

        if (! $role || $role->is_deleted) {
            return redirect('/');
        }

        $this->applyLocale($state['locale'] ?? null, $role);

        return view('subscriber.confirmed', [
            'role' => $role,
            'done' => true,
            'claimToken' => $state['claim_token'] ?? null,
            'claimEmail' => $state['claim_email'] ?? null,
            'existingEmail' => $state['existing_email'] ?? null,
        ]);
    }

    /**
     * Give a confirmed subscriber an account, and make that account follow the schedule.
     *
     * Runs on CONFIRM, never on submit. NewsletterSegment::resolveFollowers() and
     * NewsletterService::resolveRecipientsUncached() resolve followers with no confirmation filter
     * of their own, so a pivot written when the form was posted would put any address a stranger
     * typed into the owner's next newsletter with no opt-in at all. Confirmation is the proof of
     * mailbox possession that earns the row.
     *
     * The subscriber row is not replaced by this. It stays the mail-permission record - it carries
     * confirmed_at, the locale, the source and the permanent RFC 8058 unsubscribe token, and
     * EventAnnouncement takes a RoleSubscriber. The account is an identity layered on top of it.
     *
     * Modelled on NewsletterController::storeSegmentUser(), which has always created exactly this
     * shape (passwordless stub + follower pivot) for an owner-imported address.
     */
    private function linkAccount(Role $role, RoleSubscriber $subscriber): ?User
    {
        // Every failure here is swallowed. The subscription is already committed a few lines above,
        // so a throw would 500 a page whose actual work succeeded - and the repo's rule is that a
        // user-facing catch never shows the exception.
        try {
            // Demo, unclaimed, or registration closed. The reasoning for each lives on the method
            // - it is deliberately not inlined here, because the same three conditions decide
            // whether the guest surfaces SAY an account is coming, and two copies of a rule that
            // has to agree with itself is one copy too many. The role_subscribers row is written
            // either way, so an unclaimed schedule's audience waits for whoever claims it.
            if (! $role->willCreateAccountOnConfirm()) {
                return null;
            }

            $user = User::where('email', $subscriber->email)->first();

            if (! $user) {
                try {
                    $user = User::create([
                        'email' => $subscriber->email,
                        'name' => $subscriber->name ?: '',
                        'is_subscribed' => true,
                        'language_code' => is_valid_language_code($subscriber->locale)
                            ? $subscriber->locale
                            : ($role->language_code ?: 'en'),
                        // Keeps them out of the organizer funnel: AdminController,
                        // GrowthExportService, SendOnboardingNudges and HomeController all scope
                        // that cohort to signup_intent null-or-organizer. email_verified_at stays
                        // null for the same reason - those counters also require a verified
                        // account, and auto-verifying every subscriber would inflate all of them.
                        'signup_intent' => 'subscriber',
                    ]);
                } catch (QueryException $e) {
                    // Lost a race with a signup on the same address.
                    if (($e->errorInfo[1] ?? null) != 1062) {
                        throw $e;
                    }

                    $user = User::where('email', $subscriber->email)->first();
                }
            }

            if (! $user) {
                return null;
            }

            // isConnected() is any role_user row at any level, which is the guard
            // RoleController::follow() uses - so an owner or admin who subscribes to their own
            // schedule does not get a second pivot, and does not get demoted to follower.
            if (! $user->isConnected($role->subdomain)) {
                try {
                    $role->followers()->attach($user->id, ['level' => 'follower', 'created_at' => now()]);
                } catch (QueryException $e) {
                    if (($e->errorInfo[1] ?? null) != 1062) {
                        throw $e;
                    }
                }
            }

            return $user;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * What /sub/done renders, and the one-shot credential POST /sub/account requires.
     *
     * The claim token is a standard password-reset token (60 minutes, config/auth.php) and it lives
     * ONLY here. claimAccount() reads it from the session and ignores whatever the form posts,
     * which is what stops that endpoint being a general stub-takeover: password reset links can be
     * minted for any stub by anyone, including a team-invite stub sitting at admin level, and
     * NewPasswordController deliberately refuses to verify an email or sign anybody in off one.
     */
    private function claimState(Role $role, RoleSubscriber $subscriber, ?User $user): array
    {
        $state = [
            'role_id' => $role->id,
            'locale' => $subscriber->locale,
            'claim_token' => null,
            'claim_email' => null,
            'existing_email' => null,
        ];

        if (! $user || ! public_registration_enabled()) {
            return $state;
        }

        // Somebody signed in as a different account is looking at a stub belonging to another
        // mailbox. Offering them the password form would be offering them that person's account.
        if (Auth::check() && Auth::id() !== $user->id) {
            return $state;
        }

        if (! $user->isStub()) {
            // A real account, already signed in as themselves, has nothing to do here. Signed out,
            // the useful thing to say is "sign in and you will see this on your Following list".
            $state['existing_email'] = Auth::check() ? null : $user->email;

            return $state;
        }

        $state['claim_token'] = Password::createToken($user);
        $state['claim_email'] = $user->email;

        return $state;
    }

    /**
     * Turn the stub created by linkAccount() into a real account.
     *
     * Legitimately skips the emailed verification code that RegisteredUserController requires on
     * hosted: the confirm click this page came from IS the proof of mailbox possession, and it is
     * the same proof a password reset link carries.
     */
    public function claimAccount(Request $request)
    {
        // Public form, so a honeypot - and x-auth-layout renders only per-field errors, so every
        // bail here has to be a ValidationException rather than a flash.
        if (HoneypotUtils::isTripped($request)) {
            throw ValidationException::withMessages(['password' => __('messages.invalid_request')]);
        }

        if (! public_registration_enabled()) {
            abort(404);
        }

        $state = $request->session()->get('subscriber_confirmed');
        $token = is_array($state) ? ($state['claim_token'] ?? null) : null;
        $email = is_array($state) ? ($state['claim_email'] ?? null) : null;

        // Never from the request. See claimState().
        //
        // Back to /sub/done rather than password.request: the session holds ONE claim slot, so
        // confirming a second schedule in the same browser clears the first page's token. Sending
        // somebody to "Forgot your password?" for an account that has never had one is a dead end;
        // /sub/done re-renders whatever state is current and still says, in
        // subscription_account_skip_note, that signing up with the address works any time.
        if (! $token || ! $email) {
            return redirect()->route('subscriber.confirmed');
        }

        $request->validate(['password' => ['required', 'string', 'min:8']]);

        $user = User::where('email', $email)->first();

        if (! $user || ! $user->isStub()) {
            $this->forgetClaim($request);

            return redirect()->route('password.request');
        }

        // Resolved once: deriving use_24_hour_time from a different expression than the one stored
        // meant a claim with JavaScript off saved America/New_York while detect_24_hour_time() saw
        // null and left the preference unset.
        $resolvedTimezone = $user->timezone ?: ($request->input('timezone') ?: 'America/New_York');
        $languageCode = is_valid_language_code($user->language_code) ? $user->language_code : 'en';

        $status = Password::reset(
            ['email' => $email, 'password' => $request->password, 'token' => $token],
            function (User $user) use ($request, $resolvedTimezone, $languageCode) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    // The confirm link proved the mailbox, so this is verified in the same sense a
                    // registration verification code makes it verified. It is also load-bearing:
                    // /following sits behind the `verified` middleware, so without it the redirect
                    // below lands on the verification wall.
                    'email_verified_at' => $user->email_verified_at ?: now(),
                    'remember_token' => Str::random(60),
                    'timezone' => $resolvedTimezone,
                    'use_24_hour_time' => detect_24_hour_time($resolvedTimezone, $languageCode),
                ])->save();

                AuditService::log(AuditService::AUTH_REGISTER, $user->id);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            // Spend the credential on anything but a rejected password. The token lasts 60 minutes
            // (config/auth.php), and leaving an expired one in the session re-rendered the same
            // form with the same dead token, so every retry failed identically for ever.
            if ($status !== Password::INVALID_PASSWORD) {
                $this->forgetClaim($request);
            }

            // back() lands on GET /sub/done, which re-renders from the session.
            throw ValidationException::withMessages(['password' => __($status)]);
        }

        $role = ! empty($state['role_id']) ? Role::find($state['role_id']) : null;
        $this->forgetClaim($request);

        Auth::login($user->fresh(), true);

        return redirect(app_url(route('following', [], false)))
            ->with('message', __('messages.subscription_account_created', [
                'schedule' => $role?->name ?: '',
            ]));
    }

    /** Spend the one-shot claim credential, keeping the rest of the page's state. */
    private function forgetClaim(Request $request): void
    {
        $state = $request->session()->get('subscriber_confirmed');

        if (is_array($state)) {
            $state['claim_token'] = null;
            $state['claim_email'] = null;
            $request->session()->put('subscriber_confirmed', $state);
        }
    }

    /**
     * Resolve a confirm token to its subscriber and schedule, or [null, null] when it is spent.
     *
     * Not firstOrFail(). The token is single-use and is nulled by confirm(), so the ROW CANNOT BE
     * FOUND on a replay - which means a bare 404 was the reward for pressing the button twice, or
     * for following a link a mail scanner had already spent. An expired-link page cannot
     * distinguish "already used" from "garbage", and does not need to: the copy covers both.
     *
     * @return array{0: ?RoleSubscriber, 1: ?Role}
     */
    private function resolveConfirmToken(string $token): array
    {
        $subscriber = RoleSubscriber::where('confirm_token', $token)->with('role')->first();

        if (! $subscriber) {
            return [null, null];
        }

        $role = $subscriber->role;

        if (! $role || $role->is_deleted) {
            abort(404);
        }

        $this->applyLocale($subscriber->locale, $role);

        return [$subscriber, $role];
    }

    public function showUnsubscribe(Request $request, string $token)
    {
        $subscriber = RoleSubscriber::where('token', $token)->with('role')->firstOrFail();

        $this->applyLocale($subscriber->locale, $subscriber->role);

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

        $this->applyLocale($subscriber->locale, $role);

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

        $subscriber = RoleSubscriber::where('role_id', $role->id)->where('id', $id)->first();

        if (! $subscriber) {
            return back()->with('message', __('messages.deleted_subscriber'));
        }

        $email = $subscriber->email;
        $confirmedAt = $subscriber->confirmed_at;
        $subscriber->delete();

        // Deleting the row stopped being the whole job the moment confirm() started creating an
        // account and attaching a follower pivot. resolveFollowers() and NewsletterService resolve
        // followers with no confirmation filter and no suppression of their own, and there is no
        // newsletter_unsubscribes row here because the person never unsubscribed - so leaving the
        // pivot meant the owner pressed Delete, was told the subscriber was removed, and carried on
        // mailing them, with the person reappearing in the Followers table on the next load.
        //
        // But ONLY the pivot this subscription created, which is what the two conditions below
        // establish. Without them a stranger could destroy a real follow: anybody can type a known
        // follower's address into the public panel, which writes an UNCONFIRMED row (confirm() and
        // therefore linkAccount() never run), and the owner tidying that row away would delete a
        // Follow the person made themselves - the mirror image of the hazard
        // Role::accountOnlyFollowers() is scoped against, and strictly worse because it destroys
        // rather than hides. The created_at test covers the other order too: somebody who pressed
        // Follow first and confirmed later owns their pivot, and it predates the confirmation.
        //
        // Scoped to level 'follower' explicitly rather than using followers()->detach(): that
        // relation constrains the RELATED query, not the pivot, so detach() would happily delete an
        // owner's own row if the owner had ever subscribed to their own schedule.
        $user = $confirmedAt ? User::where('email', $email)->first() : null;

        if ($user) {
            RoleUser::where('role_id', $role->id)
                ->where('user_id', $user->id)
                ->where('level', 'follower')
                ->where('created_at', '>=', $confirmedAt)
                ->delete();
        }

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
    private function applyLocale(?string $locale, ?Role $role): void
    {
        foreach ([$locale, $role?->language_code] as $candidate) {
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
            route('subscriber.show_confirm', ['token' => $subscriber->confirm_token]),
            route('subscriber.show_unsubscribe', ['token' => $subscriber->token]),
        );

        // roleId is passed so it goes out from the schedule's own SMTP where one is configured.
        //
        // The try/catch is not defensive padding. This used to read "Queued, never Mail::send(),
        // so a synchronous send cannot block the request on SMTP" - which is exactly backwards on
        // the install it named. Under QUEUE_CONNECTION=sync (the .env.example default, so most
        // selfhost installs) dispatch() IS the send: SyncQueue runs handle() inline and rethrows,
        // SendQueuedEmail has no catch of its own, and on selfhost RoleMailerService::sendForRole()
        // takes its non-role-mailer branch whose Mail::send() sits outside that method's try. So a
        // dead or misconfigured SMTP host rendered a 500 to an anonymous visitor on a public form -
        // with the transport exception on the page wherever APP_DEBUG is on - for a subscriber row
        // that had already been committed a few lines above.
        //
        // Swallowed to the same neutral response every other outcome returns, so a send failure
        // reveals nothing either. It is reported, so it is not silent.
        //
        // Residual, deliberately not chased here: under sync the SMTP round trip is still inside
        // the request, and store()'s already-confirmed branch is the one path that skips this call,
        // so response LATENCY still distinguishes "already a confirmed subscriber" from every other
        // state. Closing that means deferring the send past the response, which on sync would take
        // it out of the queue the tests fake and off the retry path queue-backed installs rely on.
        // The response body is identical in every case, which is the enumeration vector that
        // matters for an endpoint reachable on every schedule page.
        try {
            SendQueuedEmail::dispatch(
                $mailable,
                $subscriber->email,
                $role->id,
                $subscriber->locale ?: ($role->language_code ?: app()->getLocale()),
            );
        } catch (\Throwable $e) {
            report($e);
        }
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

    /*
     * Two things this deliberately does NOT do, both of which look like obvious improvements and
     * were briefly implemented here before being taken back out.
     *
     * It does not set users.is_subscribed = false. That flag is not a bigger version of the
     * suppression list: User::sendEmailVerificationNotification() refuses to send while it is
     * false, so it reaches a transactional path; AudienceResolver folds it in platform-wide while
     * confirm() only ever clears the PER-SCHEDULE newsletter_unsubscribes row - so somebody who
     * stops everything here and later deliberately subscribes to a DIFFERENT schedule would
     * confirm, be told they are on the list, and then receive nothing, for ever, with no signal;
     * and nothing anywhere in the app sets it back to true for an existing user, so there is no
     * way out of it. The button says "Stop emails from every schedule I follow", present tense,
     * and the loop above is exactly that.
     *
     * It does not delete a passwordless stub either, tempting though that is: a stub cannot sign
     * in, so ProfileController::destroy() is unreachable for them and this link is their only
     * control. But User has no SoftDeletes and the schema has 28 user_id foreign keys, 20 of them
     * cascading, so a delete here is a HARD delete triggered from an unauthenticated link in an
     * email, behind whatever guard list happened to be written that day. Erasure for account-less
     * subscribers is worth doing and needs its own pass: a complete FK audit plus a test that
     * fails when a new user_id foreign key appears. And the status quo is not "the address is
     * retained because of this feature" - a role_subscribers row carrying the same name and email
     * already survived an unsubscribe-all long before any account existed.
     */

    /**
     * One shape for both the async modal and the no-JS form.
     *
     * The plain-form half redirects back to the PANEL, not just to the page. It used to flash
     * session('message'), which layouts/app.blade.php renders as a three-second Toastify toast -
     * invisible with JavaScript off, which contradicts this form's whole reason for being a plain
     * POST, and out of context even with JavaScript, because back() lands at the TOP of a page
     * whose panel sits a couple of thousand pixels down and which still shows an empty, apparently
     * unsubmitted form. The flash now drives an inline state inside the panel and the fragment puts
     * the visitor in front of it.
     *
     * The fallback URL matters: with no referer, back() lands on "/", where no panel renders and
     * the state would be invisible. withFragment() strips any pre-existing fragment first, so it is
     * safe on an arbitrary referer.
     *
     * The keys carry the subdomain so a redirect can only ever light up the panel it belongs to.
     */
    private function respond(Request $request, string $subdomain, string $message, bool $success, ?string $errorField = null)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => $success, 'message' => $message]);
        }

        $back = back(302, [], custom_domain_url(route('role.view_guest', ['subdomain' => $subdomain])))
            ->withFragment('subscribe-panel');

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
            return $back
                ->with('subscribe_error', $message)
                ->with('subscribe_error_for', $subdomain)
                // Which input to mark invalid and focus. Callers that are not a field-level
                // rejection (honeypot, rate limit, mailer failure) pass nothing and keep the
                // original behaviour of pointing at the address.
                ->with('subscribe_error_field', $errorField ?: 'email')
                ->with('subscribe_email', is_string($request->input('email'))
                    ? $request->input('email')
                    : '')
                // The name comes back too, for the same reason the address does. It only started
                // mattering when the field became required: before, losing it on a rejected
                // address was an annoyance; now the browser blocks resubmission until it is
                // retyped, and every retry costs one of five attempts per minute. Already
                // normalised by store(), and is_string() guards the callers that bail before that
                // ran - the honeypot bails FIRST, so this can still see a raw `name[]=x`.
                ->with('subscribe_name', is_string($request->input('name'))
                    ? $request->input('name')
                    : '');
        }

        // NOT session('message'): that is the toast key, and toasting from the top of the viewport
        // while the panel below already says the same thing is double notification.
        // No subscribe_email on this branch: the success state deliberately does not name the
        // address (CLAUDE.md's guest-surface rule), so flashing it would only park it in the
        // session for nothing to read.
        return $back->with('subscribe_done', $subdomain);
    }
}
