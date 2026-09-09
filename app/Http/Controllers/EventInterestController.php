<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventInterest;
use App\Models\Role;
use App\Rules\NoFakeEmail;
use App\Utils\HoneypotUtils;
use App\Utils\UrlUtils;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

/**
 * "Tell me when tickets go on sale, and if anything changes" on a single event.
 *
 * Modelled on RoleSubscriberController, which is the repo's precedent for taking an address from a
 * signed-out guest, and it inherits that controller's three layers of defence for the same reason:
 * this endpoint is reachable on every public event page, so an IP throttle alone does nothing about
 * a distributed attempt to sign one victim up everywhere.
 *
 * Two deliberate differences from that controller:
 *
 *   1. ONE field. It asks for an address and nothing else. A name is worth requiring on a standing
 *      subscription an owner will mail repeatedly; on "tell me about this one event" it is friction
 *      that buys nothing.
 *   2. SINGLE opt-in, stamped on create. docs/FEATURES.md already draws this line - double from the
 *      panel, single at checkout - and this is the checkout-shaped case: one affirmative act, about
 *      one named event, for a bounded set of messages, every one of which carries its own
 *      unsubscribe token. Requiring a round trip through an inbox to be told when tickets go on
 *      sale would cost most of the capture this feature exists for.
 */
class EventInterestController extends Controller
{
    /**
     * How many events one address may register interest in per hour, platform-wide.
     *
     * The route throttle is keyed on IP, so it cannot see one address being signed up from many
     * places. Every row here is a licence to send mail later, so this is the limit that bounds the
     * damage a third party's address can be put to.
     */
    private const PER_EMAIL_HOURLY_LIMIT = 5;

    /**
     * How many interest rows one EVENT may accumulate per day, from all addresses.
     *
     * The per-email limit keys on the literal address, so subaddressing walks straight past it -
     * victim+1@, victim+2@ each get their own bucket and their own row, while every message lands
     * in one inbox. Canonicalising is not the fix (plus-tags are meaningful at some providers, dots
     * only at Gmail), so a ceiling on the EVENT is the lever that actually bounds it. Sized well
     * above real use: the busiest event on the platform does not take 500 sign-ups in a day.
     */
    private const PER_EVENT_DAILY_LIMIT = 500;

    public function store(Request $request, $subdomain)
    {
        // Honeypot first, before validation, so a bot learns nothing from field-level errors. 200
        // rather than an error status: the caller throws a generic failure on !response.ok and only
        // renders data.message on a 200.
        if (HoneypotUtils::isTripped($request)) {
            return $this->respond($request, __('messages.invalid_request'), false);
        }

        // Validated by hand rather than $request->validate(). A ValidationException redirects back
        // with $errors populated, and event/show-guest.blade.php force-opens the RSVP / ticket
        // modal on `$errors->any()` - so a mistyped address here would pop the buy dialog. Same
        // reason RoleSubscriberController validates by hand, and the same reason respond() below
        // never flashes session('error').
        //
        // is_string() before any cast: input() hands back `email[]=x` as an array untouched, and
        // `(string) []` raises "Array to string conversion", which HandleExceptions promotes to a
        // 500 on a public endpoint.
        $submitted = $request->input('email');
        $request->merge(['email' => is_string($submitted) ? trim($submitted) : '']);

        $validator = Validator::make($request->all(), [
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'event_id' => ['required', 'string'],
            // Nullable: an event with no date at all has no occurrence to name, and the column
            // stores '' for that case so the unique index still bites.
            'event_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            return $this->respond($request, $validator->errors()->first('email')
                ?: __('messages.invalid_request'), false);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($role->is_deleted || is_demo_role($role)) {
            abort(404);
        }

        $eventId = UrlUtils::decodeId($request->event_id);
        $event = $eventId ? Event::find($eventId) : null;

        if (! $event) {
            abort(404);
        }

        // The canonical gate, shared with checkout and the seat-map endpoints. Guarding is_draft by
        // hand is what let an unlisted password-protected event leak once already; a guest is never
        // a member or admin on this endpoint, so pass false.
        if ($event->guestVisibilityFailure($role, false)) {
            abort(404);
        }

        // Nothing to be told about. A cancelled event still has an interest list - those people are
        // exactly who wants to hear - but there is no reason to take NEW sign-ups for one.
        if ($event->is_cancelled) {
            return $this->respond($request, __('messages.invalid_request'), false);
        }

        $eventDate = $this->resolveDate($event, $request->input('event_date'));

        if ($eventDate === false) {
            return $this->respond($request, __('messages.invalid_request'), false);
        }

        $email = strtolower(trim($request->email));

        // Deliberately the SAME response as success. A distinct "slow down" would leak that the
        // address is already on the list, and the point of the limit is to bound outbound mail
        // rather than to tell the caller anything.
        $rateKey = 'event-interest:'.sha1($email);
        $eventKey = 'event-interest-event:'.$event->id;

        if (RateLimiter::tooManyAttempts($rateKey, self::PER_EMAIL_HOURLY_LIMIT)
            || RateLimiter::tooManyAttempts($eventKey, self::PER_EVENT_DAILY_LIMIT)) {
            return $this->respond($request, __('messages.event_interest_confirmed'), true);
        }

        try {
            EventInterest::create([
                'event_id' => $event->id,
                'event_date' => $eventDate,
                'email' => $email,
                'locale' => app()->getLocale(),
                'source' => $request->input('source') === 'calendar' ? 'calendar' : 'event_page',
                // Single opt-in: see the class docblock.
                'confirmed_at' => now(),
                'token' => EventInterest::newToken(),
                'ip_address' => $request->ip(),
                // Pre-claimed when the event ALREADY sells. This is the watermark
                // SendEventAnnouncements needs a whole column on roles for, and here it falls out
                // of the row itself: "tickets are now on sale" is only news to somebody who asked
                // BEFORE they were, and telling everyone else about something already on the page
                // they just used is the mailshot this guard exists to prevent.
                'tickets_notified_at' => $event->canSellTickets($eventDate ?: null) ? now() : null,
            ]);
        } catch (QueryException $e) {
            // Lost a race with a concurrent identical submit, or the same person asking twice.
            // Indistinguishable from success, and it genuinely is one.
            if (($e->errorInfo[1] ?? null) == 1062) {
                return $this->respond($request, __('messages.event_interest_confirmed'), true);
            }

            report($e);

            return $this->respond($request, __('messages.invalid_request'), false);
        }

        RateLimiter::hit($rateKey, 3600);
        RateLimiter::hit($eventKey, 86400);

        return $this->respond($request, __('messages.event_interest_confirmed'), true);
    }

    /**
     * The occurrence this interest is about, or false if the submitted date is not one.
     *
     * WaitlistController::join() takes event_date on trust, which with a unique key on
     * (event_id, event_date, email) makes one address x 365 dates a table-flood primitive. That
     * endpoint is double-gated behind sold-out-and-Pro; this one is on every public event page, so
     * the date has to be checked against the event that owns it.
     *
     * @return string|false
     */
    private function resolveDate(Event $event, $submitted)
    {
        // A dateless event (a "Subscriptions" container, say) has no occurrence to name.
        if (! $event->starts_at) {
            return '';
        }

        if (! is_string($submitted) || $submitted === '') {
            // Non-recurring: the event's own day is the only answer, so accept an omitted date
            // rather than making every caller compute it.
            return $event->days_of_week
                ? false
                : $event->getStartDateTime(null, true, $event->scheduleTimezone())->format('Y-m-d');
        }

        return $event->matchesDate($submitted, $event->scheduleTimezone()) ? $submitted : false;
    }

    /**
     * The unsubscribe page: a GET that renders a button. The POST below is what mutates.
     *
     * Deliberately NOT a GET that acts. Corporate mail gateways (Safe Links, Proofpoint) fetch
     * every URL in an inbound message, so a mutating GET would unsubscribe people who never
     * clicked. Same split as RoleSubscriberController::showUnsubscribe() / unsubscribe().
     */
    public function showUnsubscribe(Request $request, string $token)
    {
        $interest = EventInterest::where('token', $token)->with('event')->firstOrFail();

        $this->applyLocale($interest->locale);

        return view('event.interest-unsubscribe', [
            'event' => $interest->event,
            'token' => $interest->token,
            'done' => false,
        ]);
    }

    /**
     * One-click unsubscribe (RFC 8058), which requires the POST to act with no confirmation step.
     * CSRF-exempt in bootstrap/app.php, because a mail client's one-click POST carries no session
     * and no token.
     *
     * Deletes rather than suppresses. RoleSubscriber keeps its row and adds to a shared suppression
     * list because that relationship is standing and an owner may import the address again. This
     * one is bounded by a single event, so there is nothing left to suppress once somebody opts out
     * - and a deletion is a real erasure rather than a retained address, which is the better answer
     * for a list nobody holds an account on.
     */
    public function unsubscribe(Request $request, string $token)
    {
        $interest = EventInterest::where('token', $token)->with('event')->firstOrFail();
        $event = $interest->event;

        $this->applyLocale($interest->locale);

        $interest->delete();

        return view('event.interest-unsubscribe', [
            'event' => $event,
            'token' => null,
            'done' => true,
        ]);
    }

    /**
     * The message was written in the recipient's locale, so the page it lands on should match.
     */
    private function applyLocale(?string $locale): void
    {
        // is_valid_language_code(), not in_array against config('app.supported_languages'): that
        // config is a code => name MAP, so a bare in_array tests the NAMES and never matches.
        if ($locale && is_valid_language_code($locale)) {
            app()->setLocale($locale);
        }
    }

    /**
     * Never session('error'): event/show-guest.blade.php force-opens the RSVP / ticket-purchase
     * form on `session('error') || $errors->any()`, so flashing one here would pop the buy dialog
     * at somebody who mistyped an address. RoleSubscriberController::respond() carries the same
     * guard and the same reasoning.
     */
    private function respond(Request $request, string $message, bool $success)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => $success, 'message' => $message]);
        }

        $back = back(302)->withFragment('event-interest');

        if ($success) {
            return $back->with('interest_message', $message);
        }

        // The address comes back under its own key, never through withInput(): old('email') is
        // shared with the ticket and RSVP forms on this same page, so repopulating that way would
        // cross-fill them.
        return $back
            ->with('interest_error', $message)
            ->with('interest_email', is_string($request->input('email')) ? $request->input('email') : '');
    }
}
