<?php

namespace App\Services;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventCancelled;
use App\Mail\EventChanged;
use App\Mail\EventInterestNotification;
use App\Models\Event;
use App\Models\EventInterest;
use App\Models\Role;

/**
 * Sends change / cancellation notifications to an event's registered attendees (paid sales, including
 * free RSVPs). Shared by the queued jobs and reusable by any future caller (e.g. the API). Email is the
 * channel that requires the schedule's own SMTP settings; push mirrors it additively.
 *
 * TWO AUDIENCES ON TWO DIFFERENT TRANSPORT GATES, and the split is deliberate.
 *
 * Buyers keep the gate they have always had: hasEmailSettings(), i.e. the schedule's own SMTP. A
 * buyer got their receipt from that address, and a platform-branded message about a purchase they
 * made elsewhere is the surprise that gate exists to prevent.
 *
 * The event-interest list does NOT sit behind it, and must not. Those people asked US, by name, on
 * that schedule's public page, and were told in so many words that they would hear if anything
 * changed - resources/lang/en/messages.php event_interest_help. Putting them behind
 * hasEmailSettings() would make that promise false for every schedule on the platform mailer, which
 * is most of them. They are bounded by Role::canSendAudienceMail() instead, the same gate
 * SendEventAnnouncements uses for exactly the same reason.
 */
class EventChangeNotifier
{
    public static function notifyChange(Event $event, ?Role $role, array $changes, ?string $note = null): int
    {
        if (! $role) {
            return 0;
        }

        $locale = $role->language_code ?: app()->getLocale();

        // Returned so the caller can stamp attendees_notified_at only when something really went
        // out. Both jobs used to stamp unconditionally, which is how an owner who notified nobody
        // still got the "recently notified" warning.
        $queued = self::notifyInterested($event, $role, EventInterestNotification::KIND_CHANGE);

        if (! $role->hasEmailSettings()) {
            return $queued;
        }

        self::eachRecipient($event, function ($sale) use ($event, $role, $changes, $note, $locale, &$queued) {
            $eventUrl = $event->getGuestUrl(false, $sale->event_date, true);
            $icalUrl = $event->getAppleCalendarUrl($sale->event_date);

            SendQueuedEmail::dispatch(
                new EventChanged($event, $role, $changes, $eventUrl, $note, $icalUrl, $sale->name),
                $sale->email,
                $role->id,
                $locale
            );

            // Push body is intentionally generic: never the join link or the organizer note.
            OneSignalService::pushToGuestEmail($sale->email, $locale, [
                'title_key' => 'messages.push_event_changed_title',
                'body_key' => 'messages.push_event_changed_body',
                'body_params' => ['event' => $event->name],
                'url' => $eventUrl,
                'options' => ['icon' => $role->profile_image_url],
            ], $role);

            $queued++;
        });

        return $queued;
    }

    public static function notifyCancellation(Event $event, ?Role $role, ?string $note = null): int
    {
        if (! $role) {
            return 0;
        }

        $locale = $role->language_code ?: app()->getLocale();

        // Returned so the caller can stamp attendees_notified_at only when something really went
        // out. Both jobs used to stamp unconditionally, which is how an owner who notified nobody
        // still got the "recently notified" warning.
        $queued = self::notifyInterested($event, $role, EventInterestNotification::KIND_CANCELLED);

        if (! $role->hasEmailSettings()) {
            return $queued;
        }

        self::eachRecipient($event, function ($sale) use ($event, $role, $note, $locale, &$queued) {
            $eventUrl = $event->getGuestUrl(false, $sale->event_date, true);

            SendQueuedEmail::dispatch(
                // A leg of a multi-event order: say the rest of the purchase stands, or the
                // buyer has no way to tell whether one cancellation voided everything.
                new EventCancelled($event, $role, $eventUrl, $note, $sale->name, (bool) $sale->order_id),
                $sale->email,
                $role->id,
                $locale
            );

            OneSignalService::pushToGuestEmail($sale->email, $locale, [
                'title_key' => 'messages.push_event_cancelled_title',
                'body_key' => 'messages.push_event_cancelled_body',
                'body_params' => ['event' => $event->name],
                'url' => $eventUrl,
                'options' => ['icon' => $role->profile_image_url],
            ], $role);

            $queued++;
        });

        return $queued;
    }

    /**
     * Distinct count of people who asked to hear about this event, on the same occurrence rule the
     * sales side uses.
     *
     * Separate from recipientCount() and deliberately not folded into it: that number is labelled
     * "registered attendees" in the confirm dialog, and somebody who left an address on a public
     * page is not an attendee.
     */
    public static function interestedCount(Event $event): int
    {
        return self::interestQuery($event)->distinct()->count('email');
    }

    /**
     * Whether any BUYER can actually be reached, which needs the schedule's own SMTP.
     *
     * hasRecipients() asks whether buyers EXIST; this asks whether they can be MAILED. The
     * difference is why the confirm dialog used to promise "1 attendee notified" and then send
     * nothing: notifyChange() applies the SMTP gate to the sales half internally, so a schedule on
     * the platform mailer has buyers it can never write to.
     *
     * exists(), not a count: baseQuery() applies Sale::scopeExcludeTestEmails(), which is seven
     * non-sargable `NOT LIKE '%@domain'` predicates. EXISTS stops at the first row that passes
     * them; COUNT(DISTINCT email) evaluates all seven against every paid sale on the event, and
     * this runs synchronously on save.
     */
    public static function hasNotifiableBuyers(Event $event): bool
    {
        return optional($event->getRoleWithEmailSettings())->hasEmailSettings()
            && self::hasRecipients($event);
    }

    public static function notifiableBuyerCount(Event $event): int
    {
        return self::hasNotifiableBuyers($event) ? self::recipientCount($event) : 0;
    }

    /**
     * The schedule the INTEREST list belongs to, which is not the one the sales half speaks for.
     *
     * The jobs resolve their role with Event::getRoleWithEmailSettings(), which prefers a venue and
     * falls back to `$venue ?: $firstRole` - correct for a buyer, who got their receipt from
     * whichever schedule holds the SMTP. It is wrong for this list. Somebody who left an address on
     * a talent's event page should hear from the TALENT, not from the venue: otherwise the mail
     * carries the venue's sender name and Reply-To, links to the venue's storefront, is metered
     * against the venue, and - worst - is gated by the venue's verification status. An auto-created
     * ownerless venue fails canSendAudienceMail() CLOSED, which silently dropped the entire send.
     *
     * SendEventInterestMail uses creatorRole for the same four things, so this is also what stops
     * the two rails mailing the same person under two different identities.
     */
    protected static function interestRole(Event $event, ?Role $fallback = null): ?Role
    {
        return $event->creatorRole ?: $fallback;
    }

    /**
     * Interest recipients that would ACTUALLY be mailed, applying every gate the send applies.
     *
     * The count and the send share this so they cannot drift. The previous version counted rows and
     * modelled one of the four gates, so the jobs stamped attendees_notified_at and the flash
     * reported "N people notified" for sends that were refused - most reachably by the 50-recipient
     * ceiling in canSendAudienceMail(), which turns 51 interested people into zero mail.
     */
    public static function interestedNotifiableCount(Event $event, string $kind = EventInterestNotification::KIND_CHANGE): int
    {
        $role = self::interestRole($event);

        if (! $role || ! $role->subdomain || $role->is_deleted || is_demo_role($role)) {
            return 0;
        }

        // A cancellation still goes out to an event that has since been hidden - that is the whole
        // point of telling them - so only the other kinds check visibility.
        if ($kind !== EventInterestNotification::KIND_CANCELLED
            && $event->guestVisibilityFailure($role, false)) {
            return 0;
        }

        $recipients = self::interestQuery($event)->distinct()->count('email');

        if ($recipients === 0 || ! $role->canSendAudienceMail($recipients, $role->user)) {
            return 0;
        }

        return $recipients;
    }

    /**
     * Everyone this event can actually reach about a change: mailable buyers plus mailable
     * interest rows. What the dispatch gate and the saved-event flash both read.
     */
    public static function notifiableCount(Event $event): int
    {
        return self::notifiableBuyerCount($event) + self::interestedNotifiableCount($event);
    }

    /**
     * Whether there is anyone at all to tell.
     *
     * The gate the two dispatch sites need. They used to ask hasRecipients(), which is sales-only,
     * so an event with an interest list and no sales never dispatched the job at all - while
     * event_interest_help promised "one if the date or venue changes". Asking about MAILABLE people
     * rather than existing ones also stops the opposite error: dispatching a job that sends nothing
     * and stamps attendees_notified_at on the way.
     *
     * Buyer half first: it is an exists() and the interest half is a count.
     */
    public static function hasAnyoneToTell(Event $event): bool
    {
        return self::hasNotifiableBuyers($event) || self::interestedNotifiableCount($event) > 0;
    }

    /** Distinct count of attendees that would be notified (drives the confirm dialog count). */
    public static function recipientCount(Event $event): int
    {
        return self::baseQuery($event)->distinct()->count('email');
    }

    public static function hasRecipients(Event $event): bool
    {
        return self::baseQuery($event)->exists();
    }

    /** Iterate paid attendees once per distinct (lowercased) email, scale-safe via chunking. */
    protected static function eachRecipient(Event $event, callable $callback): void
    {
        $seen = [];

        self::baseQuery($event)->orderBy('id')->chunkById(200, function ($sales) use (&$seen, $callback) {
            foreach ($sales as $sale) {
                $key = strtolower(trim((string) $sale->email));
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $callback($sale);
            }
        });
    }

    /**
     * The confirmed interest rows for this event's live occurrences.
     *
     * Shared by the count and the send so the confirm dialog cannot promise a number the send does
     * not deliver.
     */
    protected static function interestQuery(Event $event)
    {
        $isRecurring = (bool) $event->days_of_week;

        return EventInterest::query()
            ->confirmed()
            ->where('event_id', $event->id)
            // Same reasoning as baseQuery(): event_date is the VENUE's calendar date, so a past
            // occurrence of a recurring event owes nobody a change notice. scheduleToday() rather
            // than now()->toDateString(), which west of UTC has already rolled over in the evening.
            ->when($isRecurring, fn ($q) => $q->where(function ($w) use ($event) {
                $w->where('event_date', '')->orWhere('event_date', '>=', $event->scheduleToday());
            }));
    }

    protected static function baseQuery(Event $event)
    {
        $isRecurring = (bool) $event->days_of_week;

        // sales.event_date is the venue's calendar date. now()->toDateString() is the app
        // timezone's, so west of UTC it has already rolled over during the evening and would
        // exclude tonight's attendees from the change notice.
        return $event->sales()
            ->where('status', 'paid')
            ->excludeTestEmails()
            ->when($isRecurring, fn ($q) => $q->whereDate('event_date', '>=', $event->scheduleToday()));
    }

    /**
     * Tell the people who asked about this event, on its own transport gate.
     *
     * Not chunked through eachRecipient(): that iterates SALES. This list is usually small (it is
     * bounded per event by EventInterestController's per-event daily ceiling) and is read straight
     * off event_interests, deduped by the (event_id, event_date, email) unique index rather than in
     * PHP.
     *
     * No OneSignal mirror. Push is subscribed per browser by a guest who opted in there; somebody
     * who left an address on an event page has not done that, and pushToGuestEmail() would find
     * nothing to send to.
     */
    protected static function notifyInterested(Event $event, Role $fallbackRole, string $kind): int
    {
        // Its OWN role, not the one the sales half was handed - see interestRole().
        $role = self::interestRole($event, $fallbackRole);

        // Every gate lives in interestedNotifiableCount(), so the number the flash promises and the
        // number this sends cannot drift. A refusal there means nobody is mailed and nothing is
        // stamped.
        $recipients = $role ? self::interestedNotifiableCount($event, $kind) : 0;

        if ($recipients === 0) {
            return 0;
        }

        self::interestQuery($event)
            ->orderBy('id')
            ->chunkById(200, function ($interests) use ($event, $role, $kind) {
                foreach ($interests as $interest) {
                    SendQueuedEmail::dispatch(
                        new EventInterestNotification(
                            $role,
                            $event,
                            $interest,
                            $kind,
                            $event->getGuestUrl($role->subdomain, $interest->event_date ?: null, true),
                            route('event.interest.show_unsubscribe', ['token' => $interest->token]),
                        ),
                        $interest->email,
                        $role->id,
                        $interest->locale ?: ($role->language_code ?: config('app.locale')),
                    );
                }
            });

        return $recipients;
    }
}
