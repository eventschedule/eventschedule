<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Newsletter;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Utils\ImageUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Everything /dashboard shows above the calendar, one method to a card.
 *
 * Who is looking decides the page (`state`):
 *
 *  - organizer  owns or administers a schedule: four tiles, their schedules, what is coming up and
 *               what just happened. `fresh` while there is nothing to count yet (no view, no
 *               follower, no sale): then the tiles are left out, because a row of four zeros is
 *               the wrong first thing to show someone who made their first event a minute ago.
 *  - viewer     only ever given a look at somebody's schedule: those schedules and what is coming
 *               up on them, and no number they were not given.
 *  - attendee   runs nothing, but holds tickets or follows schedules: their tickets and what the
 *               schedules they follow have on.
 *  - empty      none of that: the page invites them to make a schedule.
 *
 * Two scopes, deliberately not one. What a schedule shows publicly anyway (events, views,
 * followers) follows editor(), which is what this page has always used. Money and the live view
 * follow manageableRoles(), which is what Sales uses: on hosted a team member reaches somebody
 * else's schedule only while its plan includes a team. And money follows who OWNS an event
 * (Event::scopeManagedBy()): a curator that merely lists a venue's event does not see its sales.
 *
 * Every window is whole days: "30 days" is today and the 29 before it, against the 30 before
 * those. A day is a day in the app's timezone, which is how analytics_daily counts them.
 *
 * build() isolates each card, as AdminDashboard does: a card that throws is reported and left
 * out, and the page still opens.
 */
class HomeDashboard
{
    public const COMING = 5;

    public const ACTIVITY = 5;

    /**
     * Series looked at for their next date. Past this a series is taken at the query's word: it
     * is counted as upcoming and cannot be listed.
     */
    private const SERIES_CAP = 200;

    private Carbon $now;

    /** @var array<string, array> upcoming(), by the schedules and the audience asked about */
    private array $scans = [];

    public function __construct()
    {
        $this->now = now();
    }

    /**
     * @param  int  $period  7, 14 or 30
     * @param  ?array  $live  ScheduleRealtime::summary(), or null where there is no live view
     */
    public function build(User $user, int $period, ?array $live = null): array
    {
        $roles = app('userRoles');
        $editor = $roles->filter(fn ($role) => in_array($role->pivot->level, ['owner', 'admin'], true))->values();
        $viewing = $roles->filter(fn ($role) => $role->pivot->level === 'viewer')->values();

        if ($editor->isEmpty() && $viewing->isEmpty()) {
            $attendee = $this->card(fn () => $this->attendee($user, $roles->filter(fn ($role) => $role->pivot->level === 'follower')->values()));

            return $attendee ? ['state' => 'attendee', 'period' => $period] + $attendee : ['state' => 'empty', 'period' => $period];
        }

        // A schedule whose plan no longer includes a team: its own pages turn this person away
        // (RoleController::viewAdmin()), so a row for it is named with the reason and not linked.
        $blocked = $user->planBlockedRoles()->pluck('id')->all();

        if ($editor->isEmpty()) {
            $open = $viewing->reject(fn ($role) => in_array($role->id, $blocked, true))->values();

            return [
                'state' => 'viewer',
                'period' => $period,
                'schedules' => $viewing->map(fn (Role $role) => $this->scheduleIdentity($role) + ['blocked' => in_array($role->id, $blocked, true)])->all(),
                'coming' => $this->card(fn () => $this->coming($user, $open, $period, numbers: false)),
            ];
        }

        $editorIds = $editor->pluck('id');
        $views = $this->card(fn () => $this->views($editorIds, $period));
        $followers = $this->card(fn () => $this->followers($editorIds, $period));
        $revenue = $this->card(fn () => $this->revenue($user, $period));

        // Nothing to count yet. Read from what the three cards already found; the only extra
        // question is whether anything was ever viewed before this period.
        $fresh = ($views['total'] ?? 1) === 0 && ($followers['total'] ?? 1) === 0 && ($revenue['sales'] ?? 1) === 0
            && ! ($revenue['ever'] ?? true) && ! $this->card(fn () => ['ever' => $this->everViewed($editorIds)])['ever'];

        return [
            'state' => 'organizer',
            'period' => $period,
            'fresh' => $fresh,
            'views' => $views,
            'followers' => $followers,
            'revenue' => $revenue,
            'live' => $live,
            'upcoming' => $this->card(fn () => $this->upcomingCount($editorIds)),
            'schedules' => $editor->count() > 1 ? $this->card(fn () => $this->schedules($editor, $views, $followers, $live, $blocked)) : null,
            'coming' => $this->card(fn () => $this->coming($user, $editor, $period, numbers: true)),
            'activity' => $this->card(fn () => $this->activity($user, $editorIds)),
            'addTargets' => $this->addTargets($editor),
            'page' => $editor->count() === 1 ? $this->safe(fn () => $editor->first()->getGuestUrl()) : null,
            // With one schedule the Followers tile opens its followers; with several it points at
            // the list under the tiles, where each schedule's own are a click away.
            'followersUrl' => $editor->count() === 1 && ! in_array($editor->first()->id, $blocked, true)
                ? route('role.view_admin', ['subdomain' => $editor->first()->subdomain, 'tab' => 'followers'])
                : null,
        ];
    }

    /**
     * Views of the schedules' pages: the period's total, the same span before it, and a bar a day.
     * Per schedule too, for the list under the tiles, from the same rows.
     */
    public function views(Collection $roleIds, int $period): array
    {
        $today = $this->now->copy()->startOfDay();
        $start = $today->copy()->subDays($period - 1);
        $previousStart = $start->copy()->subDays($period);

        $rows = DB::table('analytics_daily')
            ->whereIn('role_id', $roleIds)
            ->whereBetween('date', [$previousStart->toDateString(), $today->toDateString()])
            ->groupBy('role_id', 'date')
            ->get(['role_id', 'date', DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) AS views')]);

        $days = [];
        for ($i = 0; $i < $period; $i++) {
            $days[$start->copy()->addDays($i)->toDateString()] = 0;
        }

        $previous = 0;
        $bySchedule = [];
        foreach ($rows as $row) {
            $date = substr((string) $row->date, 0, 10);
            $count = (int) $row->views;

            if (array_key_exists($date, $days)) {
                $days[$date] += $count;
                $bySchedule[(int) $row->role_id] = ($bySchedule[(int) $row->role_id] ?? 0) + $count;
            } else {
                $previous += $count;
            }
        }

        $total = array_sum($days);

        return [
            'total' => $total,
            'previous' => $previous,
            // No earlier views means no percentage: "+100%" of nothing is not a trend.
            'change' => $previous > 0 ? round(($total - $previous) / $previous * 100, 1) : null,
            'days' => array_values($days),
            'by_schedule' => $bySchedule,
        ];
    }

    private function everViewed(Collection $roleIds): bool
    {
        return DB::table('analytics_daily')->whereIn('role_id', $roleIds)
            ->whereRaw('(desktop_views + mobile_views + tablet_views + unknown_views) > 0')
            ->exists();
    }

    /**
     * Followers, and the confirmed subscribers who have no follower record at all (an unclaimed
     * schedule, or a selfhost with registration closed, never makes one). Someone with both is one
     * person: the subscriber side leaves out anyone the follower side already counts.
     */
    public function followers(Collection $roleIds, int $period): array
    {
        $start = $this->now->copy()->startOfDay()->subDays($period - 1);

        $pivots = DB::table('role_user')->whereIn('role_id', $roleIds)->where('level', 'follower');
        $subscribers = DB::table('role_subscribers')
            ->whereIn('role_subscribers.role_id', $roleIds)
            ->whereNotNull('role_subscribers.confirmed_at')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('users')
                    ->join('role_user', 'role_user.user_id', '=', 'users.id')
                    ->whereColumn('users.email', 'role_subscribers.email')
                    ->whereColumn('role_user.role_id', 'role_subscribers.role_id')
                    ->where('role_user.level', 'follower');
            });

        $bySchedule = [];
        foreach ((clone $pivots)->groupBy('role_id')->get(['role_id', DB::raw('COUNT(*) AS total')]) as $row) {
            $bySchedule[(int) $row->role_id] = (int) $row->total;
        }
        foreach ((clone $subscribers)->groupBy('role_subscribers.role_id')->get(['role_subscribers.role_id', DB::raw('COUNT(*) AS total')]) as $row) {
            $bySchedule[(int) $row->role_id] = ($bySchedule[(int) $row->role_id] ?? 0) + (int) $row->total;
        }

        $days = [];
        for ($i = 0; $i < $period; $i++) {
            $days[$start->copy()->addDays($i)->toDateString()] = 0;
        }

        $recent = (clone $pivots)->where('created_at', '>=', $start)->pluck('created_at')
            ->concat((clone $subscribers)->where('role_subscribers.confirmed_at', '>=', $start)->pluck('role_subscribers.confirmed_at'));

        foreach ($recent as $at) {
            $date = substr((string) $at, 0, 10);
            if (array_key_exists($date, $days)) {
                $days[$date]++;
            }
        }

        return [
            'total' => array_sum($bySchedule),
            'new' => array_sum($days),
            'days' => array_values($days),
            'by_schedule' => $bySchedule,
        ];
    }

    /**
     * Money taken in the period, by currency, and the purchases behind it.
     *
     * Only for events whose sales are this person's to see (Event::scopeManagedBy()), only paid
     * sales, and never a deleted one. A purchase, not a row: one checkout writes a row per named
     * guest and per event in a cart, so rows are collapsed to their order.
     */
    public function revenue(User $user, int $period): array
    {
        $start = $this->now->copy()->startOfDay()->subDays($period - 1);

        $days = [];
        for ($i = 0; $i < $period; $i++) {
            $days[$start->copy()->addDays($i)->toDateString()] = 0;
        }

        // As a subquery: an organizer with thousands of events would otherwise bind every id
        // into each of the queries below.
        $paid = DB::table('sales')
            ->join('events', 'sales.event_id', '=', 'events.id')
            ->whereIn('sales.event_id', Event::query()->managedBy($user)->select('events.id'))
            ->where('sales.status', 'paid')
            ->where('sales.is_deleted', false);
        $purchase = 'COALESCE(sales.order_id, sales.group_id, sales.id)';

        $currencies = (clone $paid)->where('sales.created_at', '>=', $start)
            ->groupBy('events.ticket_currency_code')
            ->get(['events.ticket_currency_code AS currency_code', DB::raw('COALESCE(SUM(sales.payment_amount), 0) AS amount')])
            ->map(fn ($row) => ['currency_code' => $row->currency_code ?: platform_currency(), 'amount' => (float) $row->amount])
            ->filter(fn ($row) => $row['amount'] > 0)
            ->sortByDesc('amount')->values()->all();

        $perDay = (clone $paid)->where('sales.created_at', '>=', $start)
            ->groupBy(DB::raw('DATE(sales.created_at)'))
            ->get([DB::raw('DATE(sales.created_at) AS day'), DB::raw("COUNT(DISTINCT {$purchase}) AS purchases")]);

        foreach ($perDay as $row) {
            $date = substr((string) $row->day, 0, 10);
            if (array_key_exists($date, $days)) {
                $days[$date] = (int) $row->purchases;
            }
        }

        $sales = (int) (clone $paid)->where('sales.created_at', '>=', $start)->distinct()->count(DB::raw($purchase));

        return [
            'currencies' => $currencies,
            'sales' => $sales,
            'days' => array_values($days),
            'ever' => $sales > 0 || (clone $paid)->exists(),
        ];
    }

    /**
     * How many events are still to come, a series counted once.
     */
    public function upcomingCount(Collection $roleIds): array
    {
        return ['count' => $this->upcoming($roleIds)['count']];
    }

    /**
     * @param  bool  $publicOnly  what a stranger may see on the schedule's own page: for someone
     *                            who follows a schedule and has no part in running it
     */
    private function upcomingQuery(Collection $roleIds, bool $publicOnly = false)
    {
        $query = Event::whereIn('events.id', function ($query) use ($roleIds) {
            $query->select('event_id')->from('event_role')->whereIn('role_id', $roleIds)->where('is_accepted', true);
        })->hasUpcomingOccurrence();

        if ($publicOnly) {
            // A draft (Internal is one too), an unlisted or password-protected event, a cancelled
            // one, and an appointment booking, which is named after the guest who made it.
            $query->where('events.is_draft', false)
                ->where('events.is_private', false)
                ->where('events.is_cancelled', false)
                ->whereNull('events.appointment_type_id')
                ->notPasswordProtected();
        }

        return $query;
    }

    /**
     * What is to come on these schedules, looked at once and shared by the tile, the card and the
     * rows of "Your schedules", so the three cannot disagree.
     *
     * The query alone is an upper bound for a series: it keeps one that ended and has an extra
     * date on file, and one whose last occurrence it can only estimate. So every series is asked
     * for its real next date, and one that has none is neither listed nor counted. That is what
     * used to put "Upcoming 1" beside "No upcoming events".
     *
     * @return array{count: int, one_off_count: int, one_offs: Collection, series: Collection}
     */
    private function upcoming(Collection $roleIds, bool $publicOnly = false): array
    {
        $key = $roleIds->sort()->implode(',').'|'.(int) $publicOnly;

        return $this->scans[$key] ??= (function () use ($roleIds, $publicOnly) {
            $with = ['roles', 'creatorRole', 'tickets'];
            $oneOffs = $this->upcomingQuery($roleIds, $publicOnly)->whereNull('events.days_of_week');
            $running = $this->upcomingQuery($roleIds, $publicOnly)->whereNotNull('events.days_of_week');

            $scanned = (clone $running)->orderByDesc('events.id')->limit(self::SERIES_CAP)->with($with)->get();
            $series = collect();
            foreach ($scanned as $event) {
                $date = $this->safe(fn () => $event->nextOccurrenceFrom());
                if ($date && ($start = $this->safe(fn () => $event->getStartDateTime($date, true)))) {
                    $series->push(['event' => $event, 'date' => $date, 'start' => $start]);
                }
            }

            $oneOffCount = (clone $oneOffs)->count();
            $unscanned = $scanned->count() < self::SERIES_CAP ? 0 : max(0, (clone $running)->count() - self::SERIES_CAP);

            return [
                'count' => $oneOffCount + $series->count() + $unscanned,
                'one_off_count' => $oneOffCount,
                'one_offs' => (clone $oneOffs)->orderBy('events.starts_at')->limit(self::COMING)->with($with)->get(),
                'series' => $series,
            ];
        })();
    }

    /**
     * The next events across these schedules, soonest first.
     *
     * A series is listed once, at its next date. That is the difference from the list this
     * replaces, which filtered on `days_of_week IS NULL` and so showed a venue that runs only
     * weekly nights an empty panel and "0 upcoming events".
     *
     * @param  bool  $numbers  sold and views beside each row; false for someone who may only view
     * @param  bool  $publicOnly  only what the schedules show a stranger (see upcomingQuery())
     */
    public function coming(User $user, Collection $roles, int $period, bool $numbers, bool $publicOnly = false): array
    {
        $roleIds = $roles->pluck('id');
        if ($roleIds->isEmpty()) {
            return ['rows' => [], 'total' => 0];
        }

        $upcoming = $this->upcoming($roleIds, $publicOnly);

        $candidates = collect($upcoming['series']->all());
        foreach ($upcoming['one_offs'] as $event) {
            if ($start = $this->safe(fn () => $event->getStartDateTime(null, true))) {
                $candidates->push(['event' => $event, 'date' => null, 'start' => $start]);
            }
        }

        $picked = $candidates->sortBy(fn ($row) => $row['start']->getTimestamp())->take(self::COMING)->values();
        $eventViews = $numbers ? $this->eventViews($picked->pluck('event.id'), $period) : [];
        $roleById = $roles->keyBy('id');

        $rows = $picked->map(function (array $row) use ($user, $roleById, $eventViews, $numbers, $roles) {
            /** @var Event $event */
            $event = $row['event'];
            $start = $row['start'];

            // Today and tomorrow in the event's own schedule's timezone, the zone its date is
            // rendered in: an event must not be "today" on one line and Saturday on the next.
            $zone = $event->scheduleTimezone();
            $day = $start->format('Y-m-d');
            $today = Carbon::now($zone)->format('Y-m-d');
            $when = $day === $today ? 'today' : ($day === Carbon::now($zone)->addDay()->format('Y-m-d') ? 'tomorrow' : null);

            $mine = $event->roles->first(fn ($role) => $roleById->has($role->id) && $role->id === $event->creator_role_id)
                ?? $event->roles->first(fn ($role) => $roleById->has($role->id));

            // What was sold is the event's owner's to read: a curator that only lists a venue's
            // event sees the event and not its sales (User::canViewEventData()).
            $tickets = $numbers && $user->canViewEventData($event) ? $this->ticketLine($event, $row['date'] ?: $day) : null;

            // The edit form is under a schedule this person runs. canEditEvent() alone also
            // answers yes for whoever created the event, and for a follower or a viewer the
            // form under that schedule's address turns them away.
            $level = $mine ? ($roleById->get($mine->id)?->pivot?->level) : null;
            $editable = in_array($level, ['owner', 'admin'], true) && $user->canEditEvent($event);

            return [
                'name' => (string) $event->name,
                'image' => $this->safe(fn () => $event->getImageUrl(ImageUtils::VARIANT_WIDTH)) ?: null,
                'url' => $editable
                    ? route('event.edit', ['subdomain' => $mine->subdomain, 'hash' => UrlUtils::encodeId($event->id)])
                    : $this->safe(fn () => $event->getGuestUrl($mine?->subdomain ?: false, $row['date'] ?: false)),
                'when' => $when,
                'date' => $start->translatedFormat('D, M j'),
                'time' => $start->translatedFormat($this->timeFormat($mine)),
                'series' => $row['date'] !== null,
                'frequency' => $row['date'] !== null ? (string) $event->recurring_frequency : null,
                'visibility' => $event->visibilityState(),
                'schedule' => $roles->count() > 1 ? $mine?->name : null,
                // Whose event it is, always: what the list of followed schedules prints.
                'host' => $mine?->name,
                'tickets' => $tickets,
                'views' => $numbers ? ($eventViews[$event->id] ?? 0) : null,
                // Only where /checkin would list the event (CheckInController::index()): it is
                // a paid plan's page, and a booking is not a check-in event.
                'check_in' => $numbers && $when === 'today' && $tickets !== null && ! $event->isAppointment() && $event->isPro()
                    ? route('checkin.index')
                    : null,
            ];
        })->all();

        return ['rows' => $rows, 'total' => $upcoming['count']];
    }

    /**
     * What an event's tickets or sign-ups say for one date: how many seats are taken and, where
     * there is a limit, out of how many. Null where the event takes neither.
     *
     * The seats are the event's own arithmetic (Event::occurrenceSeatsRemaining()), not a sum
     * over ticket types: three types that share one house of 100 are 100 seats, not 300; a pass
     * is sold once for a whole series, so its pool belongs to no date, while the seats its
     * holders booked on this date are taken; and a seated house has no single number at all.
     *
     * @return array{sold: int, capacity: ?int, paid: bool}|null
     */
    private function ticketLine(Event $event, string $date): ?array
    {
        // Sign-ups first, as the event itself reads them (Event::isFree()).
        if ($event->rsvp_enabled) {
            return [
                'sold' => (int) $event->rsvpSoldCount($date),
                'capacity' => (int) $event->rsvp_limit > 0 ? (int) $event->rsvp_limit : null,
                'paid' => false,
            ];
        }

        if (! $event->tickets_enabled) {
            return null;
        }

        // tickets() already leaves add-ons out; seatTickets() leaves passes out too.
        $seats = $event->seatTickets();
        if ($seats->isEmpty()) {
            return null;
        }

        $seated = $event->hasAllocatedSeating();
        $sold = (int) $seats->sum(fn ($ticket) => $ticket->soldCountFor($date));
        if (! $seated) {
            $sold += (int) $this->safe(fn () => $event->passReservedSeats($date));
        }
        $limited = ! $seated && $seats->every(fn ($ticket) => (int) $ticket->quantity > 0);

        return [
            'sold' => $sold,
            'capacity' => $limited ? (int) $event->getTotalTicketQuantity() : null,
            'paid' => $seats->contains(fn ($ticket) => (float) $ticket->price > 0),
        ];
    }

    /** @return array<int, int> */
    private function eventViews(Collection $eventIds, int $period): array
    {
        if ($eventIds->isEmpty()) {
            return [];
        }

        $start = $this->now->copy()->startOfDay()->subDays($period - 1);

        return DB::table('analytics_events_daily')
            ->whereIn('event_id', $eventIds)
            ->where('date', '>=', $start->toDateString())
            ->groupBy('event_id')
            ->pluck(DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) AS views'), 'event_id')
            ->map(fn ($views) => (int) $views)->all();
    }

    /**
     * Sales, new followers and newsletters, newest first.
     */
    public function activity(User $user, Collection $editorIds): array
    {
        $rows = collect();
        $names = app('userRoles')->pluck('name', 'id');
        $several = $editorIds->count() > 1;

        // Sales follow who owns the event, and a deleted sale is not a sale. One row to a
        // purchase, as on the Sales page: a checkout for five named guests writes five rows, and
        // five rows of one family would be the whole card.
        $sales = Sale::query()
            ->whereIn('event_id', Event::query()->managedBy($user)->select('events.id'))
            ->where('status', 'paid')
            ->where('is_deleted', false)
            ->where(fn ($query) => $query->whereNull('group_id')->orWhereColumn('group_id', 'id'))
            ->latest()->limit(self::ACTIVITY * 2)
            ->with(['event.roles', 'saleTickets.ticket'])
            ->get();

        foreach ($sales as $sale) {
            $schedule = $several ? $sale->event?->roles->first(fn ($role) => $editorIds->contains($role->id))?->name : null;
            $rows->push([
                'type' => 'sale',
                'title' => (string) ($sale->event?->name ?: __('messages.realtime_deleted_event')),
                'who' => trim((string) $sale->name) ?: null,
                // The whole purchase, and tickets only: an add-on (parking) is not a ticket.
                'quantity' => max(1, (int) $sale->groupTotalQuantity()),
                'amount' => ($amount = (float) $sale->groupTotalPayment()) > 0 ? $amount : null,
                'currency_code' => $sale->event?->ticket_currency_code ?: platform_currency(),
                'schedule' => $schedule,
                'at' => $sale->created_at,
            ]);
        }

        $follows = DB::table('role_user')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->whereIn('role_user.role_id', $editorIds)
            ->where('role_user.level', 'follower')
            ->orderByDesc('role_user.created_at')
            ->limit(self::ACTIVITY * 2)
            ->get(['users.name', 'users.email', 'role_user.role_id', 'role_user.created_at']);

        foreach ($follows as $follow) {
            if (! $follow->created_at) {
                continue;
            }
            $name = trim((string) $follow->name);
            $rows->push([
                'type' => 'follower',
                'title' => $name !== '' ? $name : (string) $follow->email,
                'who' => $name !== '' ? (string) $follow->email : null,
                'schedule' => $several ? ($names[$follow->role_id] ?? null) : null,
                'at' => Carbon::parse($follow->created_at),
            ]);
        }

        $newsletters = Newsletter::whereIn('role_id', $editorIds)
            ->where('status', 'sent')->whereNotNull('sent_at')
            ->orderByDesc('sent_at')->limit(self::ACTIVITY)
            ->get(['id', 'role_id', 'subject', 'sent_at', 'sent_count']);

        foreach ($newsletters as $newsletter) {
            $rows->push([
                'type' => 'newsletter',
                'title' => (string) $newsletter->subject,
                'sent' => (int) $newsletter->sent_count,
                'schedule' => $several ? ($names[$newsletter->role_id] ?? null) : null,
                'at' => $newsletter->sent_at,
            ]);
        }

        return ['rows' => $rows->sortByDesc(fn ($row) => $row['at']->getTimestamp())->take(self::ACTIVITY)->values()->all()];
    }

    /**
     * One row per schedule under the tiles: the same measures, split.
     */
    private function schedules(Collection $editor, ?array $views, ?array $followers, ?array $live, array $blocked = []): array
    {
        $ids = $editor->pluck('id');

        // One-offs counted in SQL, and each series the scan found a next date for, under every
        // one of these schedules that lists it: the same events the tile counts.
        $upcoming = DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->whereIn('event_role.role_id', $ids)
            ->where('event_role.is_accepted', true)
            ->whereNull('events.days_of_week')
            ->where(fn ($query) => Event::constrainToOccurrencesSince($query, Carbon::now('UTC')))
            ->groupBy('event_role.role_id')
            ->pluck(DB::raw('COUNT(*) AS total'), 'event_role.role_id')
            ->map(fn ($total) => (int) $total)->all();

        foreach ($this->upcoming($ids)['series'] as $row) {
            foreach ($row['event']->roles as $role) {
                if ($ids->contains($role->id) && $role->pivot->is_accepted) {
                    $upcoming[$role->id] = ($upcoming[$role->id] ?? 0) + 1;
                }
            }
        }

        // Busiest first (views, then what is coming up), by name among equals: with many
        // schedules the list is cut short on the page, and the ones in use should be the ones
        // that show.
        return $editor->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values()->map(fn (Role $role) => $this->scheduleIdentity($role) + [
            'upcoming' => (int) ($upcoming[$role->id] ?? 0),
            'views' => (int) ($views['by_schedule'][$role->id] ?? 0),
            'followers' => (int) ($followers['by_schedule'][$role->id] ?? 0),
            'now' => $live === null ? null : (int) ($live['by_schedule'][$role->id] ?? 0),
            'blocked' => in_array($role->id, $blocked, true),
        ])->sortBy([['views', 'desc'], ['upcoming', 'desc']])->values()->all();
    }

    private function scheduleIdentity(Role $role): array
    {
        return [
            'id' => UrlUtils::encodeId($role->id),
            'name' => (string) $role->name,
            'type' => (string) $role->type,
            'image' => $this->safe(fn () => $role->getProfileImageUrl(ImageUtils::VARIANT_WIDTH)) ?: null,
            'url' => route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']),
        ];
    }

    /**
     * Where "Add event" can go: schedules this person edits that are able to take an event yet,
     * by the form's own rule (EventController::create()), so the button never leads to a refusal.
     */
    private function addTargets(Collection $editor): array
    {
        return $editor->filter(fn (Role $role) => $role->isClaimed())
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values()
            ->map(fn (Role $role) => $this->scheduleIdentity($role) + [
                'add' => route('event.create', ['subdomain' => $role->subdomain]),
            ])->all();
    }

    /**
     * Someone who runs nothing: the tickets they hold for what is not over yet, and what the
     * schedules they follow have on. Null when they have none of it, and then the page has
     * nothing of theirs to show. Someone whose tickets are all in the past still gets this page
     * (with its way to all of them), not an invitation to create a schedule.
     */
    private function attendee(User $user, Collection $following): ?array
    {
        $nowUtc = Carbon::now('UTC');

        // The window /tickets keeps (TicketController::tickets()): a date not yet past, or an
        // event of a day or more that is still running. The second half is what keeps a ticket
        // on the page on day two of a festival.
        $tickets = Sale::query()
            ->where('user_id', $user->id)
            ->where('is_deleted', false)
            ->whereIn('status', ['paid', 'unpaid'])
            ->where(fn ($query) => $query->whereNull('event_date')
                ->orWhere('event_date', '>=', $nowUtc->copy()->subDay()->toDateString())
                ->orWhereHas('event', fn ($event) => $event->where('duration', '>=', 24)
                    ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [$nowUtc->format('Y-m-d H:i:s')])))
            ->with(['event.roles', 'event.creatorRole', 'saleTickets.ticket'])
            ->orderBy('event_date')
            ->limit(20)
            ->get()
            ->filter(fn ($sale) => $sale->event !== null)
            // One row to a purchase. Somebody who bought for a group holds its first row, and a
            // named guest holds their own; either way a person has one row per purchase here.
            ->unique(fn ($sale) => $sale->group_id ?: $sale->id)
            ->map(function ($sale) {
                $date = $sale->event_date ?: null;

                return [
                    'sale' => $sale,
                    'start' => $this->safe(fn () => $sale->event->getStartDateTime($date, true)),
                    'end' => $this->safe(fn () => $sale->event->getEndDateTime($date, true)),
                ];
            })
            // Until it is over, and for six hours from its start whatever its length: a length
            // is often a guess (one left blank counts as two hours), and a ticket must not
            // leave the page while its holder may still be inside.
            ->filter(fn ($row) => $row['start'] !== null && max(
                $row['start']->getTimestamp() + 6 * 3600,
                $row['end']?->getTimestamp() ?? 0,
            ) >= $this->now->getTimestamp())
            ->sortBy(fn ($row) => $row['start']->getTimestamp())
            ->take(self::COMING)->values()
            ->map(function ($row) {
                $sale = $row['sale'];
                $venue = $sale->event->roles->firstWhere('type', 'venue');

                return [
                    'name' => (string) $sale->event->name,
                    'image' => $this->safe(fn () => $sale->event->getImageUrl(ImageUtils::VARIANT_WIDTH)) ?: null,
                    'url' => route('ticket.view', ['event_id' => UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]),
                    'start' => $row['start'],
                    'place' => $venue?->name ?: $sale->event->creatorRole?->name,
                    // Tickets, not add-ons; the whole group's where this is the row that bought it.
                    'quantity' => max(1, (int) ($sale->group_id && (int) $sale->group_id === (int) $sale->id
                        ? $sale->groupTotalQuantity()
                        : $sale->quantity())),
                ];
            });

        // Only what those schedules show a stranger: following a schedule gives no part in it.
        $follows = $following->isEmpty()
            ? collect()
            : collect($this->coming($user, $following, 30, numbers: false, publicOnly: true)['rows']);

        // Any ticket at all, a past one included: its holder is an attendee, with a way to it.
        $holdsTickets = $tickets->isNotEmpty() || $user->tickets()->where('is_deleted', false)->exists();

        if (! $holdsTickets && $following->isEmpty()) {
            return null;
        }

        return [
            'holdsTickets' => $holdsTickets,
            'tickets' => $tickets->map(fn ($row) => $this->dated($row))->all(),
            'follows' => $follows->all(),
            'following' => $following->count(),
        ];
    }

    /** A ticket row's date, in the words the event list uses. */
    private function dated(array $row): array
    {
        /** @var Carbon $start */
        $start = $row['start'];
        $zone = $start->getTimezone();
        $day = $start->format('Y-m-d');
        $today = Carbon::now($zone)->format('Y-m-d');

        return [
            'when' => $day === $today ? 'today' : ($day === Carbon::now($zone)->addDay()->format('Y-m-d') ? 'tomorrow' : null),
            'date' => $start->translatedFormat('D, M j'),
            'time' => $start->translatedFormat($this->timeFormat(null)),
        ] + array_diff_key($row, ['start' => true]);
    }

    private function timeFormat(?Role $role): string
    {
        return get_use_24_hour_time($role) ? 'H:i' : 'g:i A';
    }

    private function safe(callable $read): mixed
    {
        try {
            return $read();
        } catch (Throwable) {
            return null;
        }
    }

    private function card(callable $build): ?array
    {
        try {
            return $build();
        } catch (Throwable $e) {
            // A test must see the failure, not a page with a card missing.
            if (config('app.is_testing')) {
                throw $e;
            }

            report($e);

            return null;
        }
    }
}
