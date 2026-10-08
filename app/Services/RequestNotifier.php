<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Notifications\NewRequestsNotification;
use App\Utils\RequestSummary;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Tells a schedule's owners and admins what is waiting for their answer, spelling out each
 * request once.
 *
 * Both rails send through here: the request forms, at the moment a request arrives
 * (EventController::tellOwnersOfPendingRequest(), at most once per quarter of an hour), and
 * app:notify-request-changes at noon, for whatever arrived inside that quarter of an hour or by
 * another road (one schedule adding its event to another's). They were two copies of one loop and
 * had drifted: the noon copy wrote in the app's language, linked with a bare route() and stored
 * its count with save().
 *
 * Which requests a mail spells out is decided by a stamp on the request's own row
 * (event_role.request_notified_at), not by the count the mail used to be: a count cannot say
 * WHICH requests are new, and it missed one whenever the owner answered another in between.
 * A request is stamped when a mail that NAMES it has left, and only then, so every request is
 * in one mail and no other, and searching a mailbox for it finds it. A mail spells out the
 * first few in full (LISTED, fewer when they are long) and names every other new request in one
 * line each (NAMED): "Save all" on the import page sends a programme of thirty events as thirty
 * requests, and five a mail was five a day. What is past both stays unstamped for the next mail.
 */
class RequestNotifier
{
    /** Requests one mail spells out in full, at most. Fewer when they are long (BUDGET). */
    public const LISTED = 5;

    /** Further requests one mail names in a line each, at most: the event, who asked, the day. */
    public const NAMED = 30;

    /**
     * Bytes of request one mail carries, spelled out and named together. Gmail cuts a message
     * off at about 102 KB and the button and the unsubscribe link go with what it cuts; the
     * layout around the requests is some 12 KB, and the line the other owner mails are held to
     * is 80 KB (EmailOwnerRenderTest).
     */
    private const BUDGET = 56000;

    /** Roughly what a named line of ordinary length costs: kept back for each one there will be. */
    private const LINE = 450;

    /**
     * Rows one run reads. A schedule with more than this unannounced is being flooded, and the
     * rest are the next run's; unbounded, the ids would one day be more than a query can name.
     */
    private const BATCH = 1000;

    /** Whether $role has a request no mail has spelled out yet. */
    public function hasUnannounced(Role $role): bool
    {
        return $this->unannounced($role)->exists();
    }

    /**
     * Send the mail (and the push) for what is waiting on $role and has not been told. Returns
     * whether anybody was told.
     *
     * The caller decides WHETHER to (the schedule takes requests and reviews them, the quarter
     * of an hour has passed). Sending does not throw: a mail server that is down must not undo
     * a request that is already saved, and one address that bounces, or one push that fails,
     * must not cost the others their mail.
     */
    public function announce(Role $role): bool
    {
        // One announcement of a schedule at a time. The noon run and a request arriving at noon
        // (or the two cron rails) would otherwise both read the same unstamped rows and both
        // mail them. A second caller waits a few seconds for the first to finish, then tells of
        // whatever the first did not (a request that arrived while it was sending). Ten minutes,
        // because the mail is sent inside the lock and a mail server can be slow to refuse.
        try {
            return (bool) Cache::lock('request-announce:'.$role->id, 600)->block(15, fn () => $this->send($role));
        } catch (LockTimeoutException $e) {
            // Still sending. What this caller came about is unstamped, and is the next run's.
            return false;
        }
    }

    private function send(Role $role): bool
    {
        // Who would read it, before anything is read for them: a schedule with the notice off
        // and no shared address is asked this at every noon and every request, for good.
        $editors = $role->getEditorsWantingNotification('new_request');
        if ($editors->isEmpty() && ! $role->getNotificationEmailWanting('new_request', $editors)) {
            return false;
        }

        // Read once, before anything is sent: a request that arrives while the mail is on its
        // way was not in it, and must not be stamped as though it had been.
        $pivotIds = $this->unannounced($role)->orderBy('id')->limit(self::BATCH)->pluck('id');
        if ($pivotIds->isEmpty()) {
            return false;
        }

        $pendingCount = Event::whereHas('roles', function ($query) use ($role) {
            $query->where('event_role.role_id', $role->id)
                ->whereNull('event_role.is_accepted');
        })->count();

        // An appointment waiting to be confirmed is counted and never spelled out here: its own
        // mail said who booked what the moment they did (AppointmentBookedNotification).
        $listable = $role->events()
            ->wherePivotIn('id', $pivotIds->all())
            ->whereNull('events.appointment_type_id');
        $newCount = (clone $listable)->count();
        $candidates = $listable->with(['roles', 'creatorRole'])->orderBy('event_role.id')->limit(self::LISTED + self::NAMED)->get();
        // The oldest are spelled out; every other one is named, in the same mail.
        [$events, $named] = $this->thatFit($candidates, $role);

        // What this mail accounts for: the requests it spells out or names, and the bookings it
        // counts. A request past both stays unstamped, and the next mail has it.
        $told = $events->concat($named)->map(fn (Event $event) => $event->pivot->id)
            ->merge(
                DB::table('event_role')
                    ->join('events', 'events.id', '=', 'event_role.event_id')
                    ->whereIn('event_role.id', $pivotIds->all())
                    ->whereNotNull('events.appointment_type_id')
                    ->pluck('event_role.id')
            );

        $url = app_url(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'requests'], false));
        $sent = 0;

        foreach ($editors as $editor) {
            // Each in their own language, not the visitor's whose request this may be running in.
            $locale = is_valid_language_code($editor->language_code)
                ? $editor->language_code
                : NotificationEmailService::locale($role);

            try {
                $editor->notify((new NewRequestsNotification($role, $pendingCount, $events, $newCount, $named))->locale($locale));
                $sent++;
            } catch (\Throwable $e) {
                report($e);

                continue;
            }

            // Its own try: a push that cannot be queued after the mail has left would otherwise
            // end the loop with nothing stamped, and the same request would be mailed to this
            // editor again by every run after it.
            try {
                OneSignalService::pushToUser($editor, [
                    'title_key' => 'messages.push_new_request_title',
                    'body_key' => 'messages.push_new_request_body',
                    'url' => $url,
                    'options' => ['icon' => $role->profile_image_url],
                ], $role);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if (app(NotificationEmailService::class)->sendNotification($role, 'new_request', new NewRequestsNotification($role, $pendingCount, $events, $newCount, $named), $editors)) {
            $sent++;
        }

        // Stamped only once a mail that names them has left. With every send failed they stay
        // unannounced, and the next run says what this one could not.
        if ($sent > 0) {
            foreach ($told->chunk(500) as $ids) {
                DB::table('event_role')->whereIn('id', $ids->all())->update(['request_notified_at' => now()]);
            }

            // What the mail counted. Operational state: save() would run the schedule's whole
            // saving hook and move updated_at, which the sitemap publishes as the page's lastmod.
            if ((int) $role->last_notified_request_count !== $pendingCount) {
                $role->writeOperationalColumns(['last_notified_request_count' => $pendingCount]);
            }
        }

        return $sent > 0;
    }

    /**
     * What one mail has room for, oldest first: the requests it spells out (the first always,
     * then up to LISTED while they stay inside BUDGET) and, after them, the ones it names in a
     * line each (up to NAMED, inside the same BUDGET). Room for the lines is kept back before
     * the requests are weighed, or five long requests would leave the thirty behind them nothing.
     *
     * Several are each told more briefly than one would be, which is what
     * RequestSummary::describe()'s $brief is; weighed here in whatever language this runs in,
     * since a label or a date in another one changes a request's size by a few bytes at most.
     *
     * Decided HERE, once, rather than by each reader's copy of the mail: what a mail named is
     * what gets stamped, and every reader's copy has to have named the same requests.
     *
     * @return array{0: Collection<int, Event>, 1: Collection<int, Event>} spelled out, named
     */
    private function thatFit(Collection $candidates, Role $role): array
    {
        $first = $candidates->take(self::LISTED);
        $brief = $first->count() > 1;
        $room = self::BUDGET - min($candidates->count() - $first->count(), self::NAMED) * self::LINE;
        $full = collect();
        $named = collect();
        $weight = 0;

        foreach ($first as $event) {
            $cost = $this->weigh($event, $role, $brief);

            if ($full->isNotEmpty() && $weight + $cost > $room) {
                break;
            }

            $full->push($event);
            $weight += $cost;
        }

        foreach ($candidates->slice($full->count())->take(self::NAMED) as $event) {
            $cost = $this->weighLine($event, $role);

            if ($weight + $cost > self::BUDGET) {
                break;
            }

            $named->push($event);
            $weight += $cost;
        }

        return [$full, $named];
    }

    private function weigh(Event $event, Role $role, bool $brief): int
    {
        try {
            return RequestSummary::weight(RequestSummary::describe($event, $role, $brief));
        } catch (\Throwable $e) {
            // NewRequestsNotification falls back to the request's name for one it cannot describe.
            return RequestSummary::weight(RequestSummary::bare($event));
        }
    }

    private function weighLine(Event $event, Role $role): int
    {
        try {
            return RequestSummary::lineWeight(RequestSummary::line($event, $role));
        } catch (\Throwable $e) {
            return RequestSummary::lineWeight(['title' => (string) $event->name, 'from' => null, 'day' => null]);
        }
    }

    private function unannounced(Role $role)
    {
        return DB::table('event_role')
            ->where('role_id', $role->id)
            ->whereNull('is_accepted')
            ->whereNull('request_notified_at');
    }
}
