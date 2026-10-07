<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\MoneyUtils;
use App\Utils\RealtimeRows;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * What happened on a schedule owner's own schedules, beside the live traffic on the Realtime tab
 * of /analytics: the last 24 hours of sales, registrations, bookings, followers and requests (the
 * Activity rail), who has arrived at an event that is on (the door card), and which minutes of
 * the traffic chart a sale landed in.
 *
 * ScheduleRealtime answers "who is on my pages"; this answers "what did they do", from the owner's
 * own records. The two share nothing but the tab, and this class never reads realtime_hits.
 *
 * The rules, each of which a test in tests/Feature/ScheduleActivityTest.php holds:
 *
 *  - It names nobody. No query here selects a buyer's, follower's, submitter's or guest's name,
 *    email or phone number (`users` appears once, inside the NOT EXISTS that keeps a subscriber
 *    who already follows from being counted twice, and nothing is read from it): a row says what
 *    happened and to which event or schedule, and links to the list page (Sales, Followers,
 *    Requests) where the person is, for whoever may open it.
 *  - An appointment booking is an event NAMED AFTER ITS GUEST ("Consultation - Dana Whitlock"), so
 *    an event's title is printed only through eventLabel(), which answers with the appointment
 *    type for one. Bookings are read on their own, and every other query leaves them out
 *    (ownedEventIds()), so one waiting for approval is a booking and never an "event request".
 *  - Whose it is follows the schedules the tab covers and nothing else: an event counts when it
 *    is on one of them as its own, or was accepted onto one that is not a curator. That is the
 *    second half of Event::scopeManagedThrough() and deliberately not the first ("I created it"),
 *    which would keep a team member's sales on screen after the plan closed the schedule to them.
 *  - A purchase is one row, whatever it wrote: a checkout for five named guests writes five sales
 *    rows, and its first row speaks for it (`group_id IS NULL OR group_id = id`). A basket across
 *    several events is a row per event, because a row is titled by its event.
 *  - An import is not a sale: importing attendees writes paid rows stamped now. They are left
 *    out of the rows, the counts and the marks. A sale taken at the box office, and a cash order
 *    the organizer marked paid, are counted and listed, and are never a mark on the traffic
 *    chart: nobody on a page made them.
 *  - Times: these tables are written on the app's clock (Carbon::now()), realtime_hits on UTC. So
 *    nothing here is compared with RealtimeTracker::ts(), and a moment leaves as a Unix second
 *    (seconds()), which is what the traffic chart's minutes are counted from.
 *
 * Nothing is cached, for the reason ScheduleRealtime gives. The cost is held down by reading the
 * newest LIST rows of a kind and counting only when that list came back full, and by never
 * running a query per row (two grouped reads give every purchase its quantity).
 */
class ScheduleActivity
{
    public const WINDOW_HOURS = 24;

    /** Rows a list holds. Past this the rail says how many it is showing of how many. */
    public const LIST = 20;

    /** Purchases read for the chart's marks: half an hour of them. */
    public const MARK_CAP = 500;

    /** Events the door card lists, soonest first. */
    public const DOOR = 4;

    /**
     * Events looked at for the door card, of each kind: one-time events that are on, and series
     * that are still running. A scan is light (the event and its own schedule, nothing else), so
     * the cap is generous; past it the newest series are kept and the oldest are not seen.
     */
    private const DOOR_SCAN = 500;

    /** A series' occurrence is looked for this many days back at most (a week-long weekly event). */
    private const DOOR_LOOKBACK_DAYS = 7;

    /**
     * An event with no length is at the door for this long after it starts. One WITH a length is
     * there until it ends and no longer: a finished show left on the card for six hours pushed
     * the one in progress off it, since the card shows the earliest four.
     */
    private const DOOR_HOURS = 6;

    /** The kinds with a count button, in the order the buttons stand. */
    public const TILES = ['sale', 'registration', 'booking', 'follower', 'request'];

    /**
     * What a purchase came to. A party's first row carries its own id as group_id and the rows of
     * its guests carry the same, so the sum is over the group; a purchase of one has no group.
     */
    private const TOTAL = '(CASE WHEN sales.group_id IS NULL THEN sales.payment_amount ELSE (SELECT COALESCE(SUM(g.payment_amount), 0) FROM sales g WHERE g.group_id = sales.group_id AND g.is_deleted = 0) END)';

    private Carbon $now;

    private int $nowTs;

    /** @var array<int, Role> */
    private array $roles = [];

    /** @var list<int> */
    private array $roleIds;

    /** @var list<int> the covered schedules that are not curators: an accepted event is theirs too */
    private array $acceptingIds;

    /** @var array<int, object> */
    private array $events = [];

    /** @var array<int, string> */
    private array $types = [];

    /** @var array<int, int> event id to the covered schedule it is on */
    private array $eventRole = [];

    /**
     * @param  Collection<int, Role>  $roles  the schedules the tab covers, already authorized and
     *                                        already narrowed to the one picked
     * @param  string  $salt  per session: makes a row's key useless outside it
     * @param  bool  $several  whether a row should say which schedule it belongs to
     */
    public function __construct(private readonly User $viewer, Collection $roles, private readonly string $salt, private readonly bool $several)
    {
        foreach ($roles as $role) {
            $this->roles[(int) $role->id] = $role;
        }

        $this->roleIds = array_keys($this->roles);
        $this->acceptingIds = array_keys(array_filter($this->roles, fn (Role $role) => $role->type !== 'curator'));
        $this->now = Carbon::now();
        $this->nowTs = $this->now->getTimestamp();
    }

    /**
     * For the person asking, narrowed as the tab is. The caller has already checked that
     * $onlyRoleId is one of their schedules (RealtimeController::selectedSchedule()); one that is
     * not simply matches nothing here.
     */
    public static function forRequest(Request $request, User $user, ?int $onlyRoleId = null): self
    {
        $all = $user->manageableRoles();
        $roles = $onlyRoleId === null ? $all : $all->where('id', $onlyRoleId)->values();

        return new self($user, $roles, ScheduleRealtime::salt($request), $onlyRoleId === null && $all->count() > 1);
    }

    /**
     * The traffic poll's whole answer: ScheduleRealtime's payload with the marks and the checkout
     * figures laid on it. The tab's first render and every poll after it are both this.
     *
     * The traffic is the page; what is laid on it is a courtesy. So a failure here is reported and
     * leaves the traffic standing, where a failure in the traffic itself is not swallowed.
     */
    public static function trafficPayload(Request $request, User $user, ?int $onlyRoleId = null): array
    {
        $payload = ScheduleRealtime::forRequest($request, $user)->payload($onlyRoleId);

        try {
            return $payload + self::forRequest($request, $user, $onlyRoleId)->live((int) $payload['t']);
        } catch (Throwable $e) {
            report($e);

            $none = array_fill(0, RealtimeRows::MINUTES, 0);

            return $payload + ['marks' => ['sales' => $none, 'registrations' => $none, 'last' => null, 'at_last' => 0], 'checkouts' => ['started' => 0, 'paid' => 0]];
        }
    }

    /**
     * The rail's poll, and what the tab is first rendered with. `schedule` says which schedule
     * the answer is for, so a page can drop one that is no longer the one it is showing.
     */
    public static function railPayload(Request $request, User $user, ?int $onlyRoleId = null): array
    {
        $for = ['schedule' => $onlyRoleId ? UrlUtils::encodeId($onlyRoleId) : ''];

        try {
            return $for + self::forRequest($request, $user, $onlyRoleId)->rail();
        } catch (Throwable $e) {
            report($e);

            return $for + ['stats' => [], 'lists' => ['all' => []], 'total' => 0, 'door' => ['events' => [], 'more' => 0], 'failed' => true];
        }
    }

    /**
     * An event's title as this tab may print it. The one place a name is turned into a label.
     *
     * @param  ?object  $event  needs `name` and `appointment_type_id`
     * @param  array<int, string>  $types  appointment type names by id
     */
    public static function eventLabel(?object $event, array $types = []): string
    {
        if (! $event) {
            return __('messages.realtime_deleted_event');
        }

        if ($event->appointment_type_id) {
            $type = trim((string) ($types[(int) $event->appointment_type_id] ?? ''));

            return $type !== '' ? $type : __('messages.realtime_act_booking');
        }

        $name = trim((string) $event->name);

        return $name !== '' ? $name : __('messages.realtime_deleted_event');
    }

    /**
     * What rides on every traffic poll: the purchases of the last half hour as counts per minute
     * of the traffic chart, and the checkouts begun in it.
     *
     * @param  int  $nowTs  the second the traffic payload was counted at, so a mark lands on the
     *                      bar of the minute it happened in
     *                      `last` and `at_last` are how the page knows the rail is stale: the second of the newest
     *                      mark, and how many marks that second holds. The second alone is not enough: two people
     *                      paying in the same second is an ordinary Friday night, and the later of them would wait out
     *                      the rail's minute.
     * @return array{marks: array{sales: list<int>, registrations: list<int>, last: ?int, at_last: int}, checkouts: array{started: int, paid: int}}
     */
    public function live(int $nowTs): array
    {
        $sales = array_fill(0, RealtimeRows::MINUTES, 0);
        $registrations = $sales;
        $last = null;
        $atLast = 0;

        if ($this->roleIds) {
            $rows = $this->purchases($this->now->copy()->subMinutes(RealtimeRows::MINUTES))
                ->where(fn ($query) => $query->whereNull('sales.payment_method')->orWhere('sales.payment_method', '!=', 'box_office'))
                ->orderByDesc('sales.paid_at')
                ->limit(self::MARK_CAP)
                ->get(['sales.paid_at', 'sales.payment_method', DB::raw(self::TOTAL.' AS total')]);

            foreach ($rows as $row) {
                $registration = $row->payment_method === 'rsvp' || (float) $row->total <= 0;

                // A cash order becomes paid when the organizer marks it: their click, not a visit.
                if (! $registration && $row->payment_method === 'cash') {
                    continue;
                }

                $at = $this->seconds($row->paid_at);
                $index = RealtimeRows::minuteIndex($at, $nowTs);
                if ($index === null) {
                    continue;
                }

                $registration ? $registrations[$index]++ : $sales[$index]++;
                if ($last === null || $at > $last) {
                    $last = $at;
                    $atLast = 1;
                } elseif ($at === $last) {
                    $atLast++;
                }
            }
        }

        return [
            'marks' => ['sales' => $sales, 'registrations' => $registrations, 'last' => $last, 'at_last' => $atLast],
            'checkouts' => $this->checkouts(),
        ];
    }

    /**
     * The rail and the door card. Asked for once a minute and whenever a mark arrives, never on
     * the traffic poll.
     */
    public function rail(): array
    {
        if (! $this->roleIds) {
            return ['stats' => [], 'lists' => ['all' => []], 'total' => 0, 'door' => ['events' => [], 'more' => 0]];
        }

        $since = $this->now->copy()->subHours(self::WINDOW_HOURS);

        $kinds = [
            'sale' => $this->purchaseKind($since, false),
            'registration' => $this->purchaseKind($since, true),
            'booking' => $this->bookings($since),
            'follower' => $this->followers($since),
            'request' => $this->requests($since),
            'other' => $this->others($since),
        ];

        $rows = collect();
        foreach ($kinds as $kind) {
            $rows = $rows->concat($kind['rows']);
        }
        $this->describe($rows);

        $all = $rows->sortByDesc(fn ($row) => [$row->at, $row->id])->values();
        $present = fn (Collection $list) => $list->take(self::LIST)->map(fn ($row) => $this->present($row))->values()->all();

        $lists = ['all' => $present($all)];
        $stats = [];
        foreach (self::TILES as $tile) {
            $count = (int) $kinds[$tile]['count'];
            // Bookings have a button only on a schedule that takes them.
            if ($tile !== 'booking' || $count > 0) {
                $stats[] = ['type' => $tile, 'count' => $count];
            }
            $lists[$tile] = $present($all->where('tile', $tile));
        }

        return [
            'stats' => $stats,
            'lists' => $lists,
            'total' => (int) array_sum(array_column($kinds, 'count')),
            'door' => $this->door(),
        ];
    }

    /**
     * Events on these schedules whose sales are theirs to read, and never an appointment booking.
     * A fresh builder per call: whereIn() consumes it.
     *
     * Two arms, each reached through an index that holds only what it needs:
     *
     *  - the schedule's OWN events, from `events.creator_role_id`. Mine whatever the pivot says,
     *    as long as the event is still on the schedule that made it;
     *  - somebody else's event that one of my schedules ACCEPTED, from that schedule's pivot rows.
     *    Never for a curator: a curator that lists a venue's event does not own its sales.
     *
     * It used to be one walk over every pivot row of every covered schedule. A curator lists tens
     * of thousands of events and owns few of them, and each row cost a lookup of its event before
     * it could be turned away, eight or more times a minute for every open tab. Now a curator
     * costs its own events and nothing else.
     *
     * The two are joined in a derived table and not as `IN (... UNION ...)` or two ORed
     * subqueries: neither of those can be a semi-join, and the sales query would fall back to
     * reading every paid sale on the install.
     */
    private function ownedEventIds(): Builder
    {
        $own = DB::table('events as owned')
            ->join('event_role', function ($join) {
                $join->on('event_role.event_id', '=', 'owned.id')
                    ->on('event_role.role_id', '=', 'owned.creator_role_id');
            })
            ->whereIn('owned.creator_role_id', $this->roleIds)
            ->whereNull('owned.appointment_type_id')
            ->select('owned.id as event_id');

        if ($this->acceptingIds) {
            $own->unionAll(DB::table('event_role')
                ->join('events as owned', 'owned.id', '=', 'event_role.event_id')
                ->whereIn('event_role.role_id', $this->acceptingIds)
                ->where('event_role.is_accepted', true)
                // Not the schedule's own events again: the first arm has them, and listing each
                // twice doubles what every query here materialises and misleads the optimizer
                // about how many events there are. NULL-safe, or an event with no creator
                // schedule (a legacy one) would drop out of both arms.
                ->whereRaw('NOT (event_role.role_id <=> owned.creator_role_id)')
                ->whereNull('owned.appointment_type_id')
                ->select('event_role.event_id'));
        }

        return DB::query()->fromSub($own, 'covered')->select('covered.event_id');
    }

    /** Paid purchases since $since, one row each, imports left out. */
    private function purchases(Carbon $since): Builder
    {
        return DB::table('sales')
            ->whereIn('sales.event_id', $this->ownedEventIds())
            ->where('sales.status', 'paid')
            ->where('sales.is_deleted', false)
            ->where('sales.paid_at', '>=', $since)
            ->where(fn ($query) => $query->whereNull('sales.payment_method')->orWhere('sales.payment_method', '!=', 'import'))
            ->where(fn ($query) => $query->whereNull('sales.group_id')->orWhereColumn('sales.group_id', 'sales.id'));
    }

    /**
     * Sales (money changed hands) or registrations (a sign-up, or tickets that came to nothing).
     *
     * @return array{rows: Collection, count: int}
     */
    private function purchaseKind(Carbon $since, bool $registration): array
    {
        $filter = $registration
            ? "(COALESCE(sales.payment_method, '') = 'rsvp' OR ".self::TOTAL.' <= 0)'
            : "(COALESCE(sales.payment_method, '') <> 'rsvp' AND ".self::TOTAL.' > 0)';
        $query = fn () => $this->purchases($since)->whereRaw($filter);

        $rows = $query()->orderByDesc('sales.paid_at')->orderByDesc('sales.id')->limit(self::LIST)
            ->get(['sales.id', 'sales.event_id', 'sales.payment_method', 'sales.paid_at', DB::raw(self::TOTAL.' AS total')]);

        return [
            'rows' => $rows->map(fn ($row) => (object) [
                'kind' => $registration ? 'registration' : 'sale',
                'tile' => $registration ? 'registration' : 'sale',
                'id' => (int) $row->id,
                'event_id' => (int) $row->event_id,
                'role_id' => null,
                'at' => $this->seconds($row->paid_at),
                'total' => (float) $row->total,
                'rsvp' => $row->payment_method === 'rsvp',
            ]),
            'count' => $this->countOf($rows, $query),
        ];
    }

    /**
     * Appointment bookings made in the window, read from the booking's own event: it is created
     * with the booking, belongs to the schedule that was booked, and is not cancelled.
     *
     * @return array{rows: Collection, count: int}
     */
    private function bookings(Carbon $since): array
    {
        $query = fn () => DB::table('events')
            ->whereIn('events.creator_role_id', $this->roleIds)
            ->whereNotNull('events.appointment_type_id')
            ->where('events.created_at', '>=', $since)
            ->where('events.is_cancelled', false)
            ->whereExists(fn ($sale) => $sale->selectRaw('1')->from('sales')
                ->whereColumn('sales.event_id', 'events.id')
                ->where('sales.is_deleted', false)
                ->whereNotIn('sales.status', ['cancelled', 'refunded', 'expired']));

        $rows = $query()->orderByDesc('events.created_at')->orderByDesc('events.id')->limit(self::LIST)->get([
            'events.id', 'events.appointment_type_id', 'events.creator_role_id', 'events.starts_at',
            'events.created_at', 'events.ticket_currency_code',
            DB::raw("(SELECT COALESCE(SUM(s.payment_amount), 0) FROM sales s WHERE s.event_id = events.id AND s.is_deleted = 0 AND s.status = 'paid') AS total"),
        ]);

        return [
            'rows' => $rows->map(fn ($row) => (object) [
                'kind' => 'booking',
                'tile' => 'booking',
                'id' => (int) $row->id,
                'event_id' => null,
                'role_id' => (int) $row->creator_role_id,
                'at' => $this->seconds($row->created_at),
                'total' => (float) $row->total,
                'type_id' => (int) $row->appointment_type_id,
                'starts_at' => (string) $row->starts_at,
                'currency' => $row->ticket_currency_code,
            ]),
            'count' => $this->countOf($rows, $query),
        ];
    }

    /**
     * New followers, and people who confirmed a newsletter sign-up without an account that
     * follows: the same two populations the dashboard's Followers tile adds up.
     *
     * @return array{rows: Collection, count: int}
     */
    private function followers(Carbon $since): array
    {
        $pivots = fn () => DB::table('role_user')
            ->whereIn('role_user.role_id', $this->roleIds)
            ->where('role_user.level', 'follower')
            ->where('role_user.created_at', '>=', $since);

        $subscribers = fn () => DB::table('role_subscribers')
            ->whereIn('role_subscribers.role_id', $this->roleIds)
            ->where('role_subscribers.confirmed_at', '>=', $since)
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('users')
                    ->join('role_user', 'role_user.user_id', '=', 'users.id')
                    ->whereColumn('users.email', 'role_subscribers.email')
                    ->whereColumn('role_user.role_id', 'role_subscribers.role_id')
                    ->where('role_user.level', 'follower');
            });

        $followed = $pivots()->orderByDesc('role_user.created_at')->orderByDesc('role_user.id')->limit(self::LIST)
            ->get(['role_user.id', 'role_user.role_id', 'role_user.created_at AS happened_at']);
        $subscribed = $subscribers()->orderByDesc('role_subscribers.confirmed_at')->orderByDesc('role_subscribers.id')->limit(self::LIST)
            ->get(['role_subscribers.id', 'role_subscribers.role_id', 'role_subscribers.confirmed_at AS happened_at']);

        $row = fn (string $kind) => fn ($row) => (object) [
            'kind' => $kind,
            'tile' => 'follower',
            'id' => (int) $row->id,
            'event_id' => null,
            'role_id' => (int) $row->role_id,
            'at' => $this->seconds($row->happened_at),
        ];

        return [
            'rows' => $followed->map($row('follower'))->concat($subscribed->map($row('subscriber'))),
            'count' => $this->countOf($followed, $pivots) + $this->countOf($subscribed, $subscribers),
        ];
    }

    /**
     * Events sent to one of these schedules in the window and still waiting for an answer.
     *
     * The pivot has no timestamps, so a request is placed in time by when its EVENT was made.
     * Two things follow, both by design: one that was answered leaves (nothing records when), and
     * an event made last week and offered to this schedule today is not listed here at all. The
     * Requests tab is the whole list; this is the day's news from it.
     *
     * @return array{rows: Collection, count: int}
     */
    private function requests(Carbon $since): array
    {
        $query = fn () => DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->whereIn('event_role.role_id', $this->roleIds)
            ->whereNull('event_role.is_accepted')
            ->whereNull('events.appointment_type_id')
            ->where('events.created_at', '>=', $since);

        $rows = $query()->orderByDesc('events.created_at')->orderByDesc('events.id')->limit(self::LIST)
            ->get(['event_role.id', 'event_role.event_id', 'event_role.role_id', 'events.created_at AS happened_at']);

        return [
            'rows' => $rows->map(fn ($row) => (object) [
                'kind' => 'request',
                'tile' => 'request',
                'id' => (int) $row->id,
                'event_id' => (int) $row->event_id,
                'role_id' => (int) $row->role_id,
                'at' => $this->seconds($row->happened_at),
            ]),
            'count' => $this->countOf($rows, $query),
        ];
    }

    /**
     * The kinds without a button, in one read: waitlist joins, people who asked to hear about
     * tickets (once they confirmed), and comments, photos and videos from the audience.
     *
     * @return array{rows: Collection, count: int}
     */
    private function others(Carbon $since): array
    {
        $part = function (string $table, string $kind, string $column, string $flag, bool $authored = false) use ($since) {
            $query = DB::table($table)
                ->whereIn($table.'.event_id', $this->ownedEventIds())
                ->where($table.'.'.$column, '>=', $since)
                ->selectRaw("'{$kind}' AS kind, {$table}.id AS id, {$table}.event_id AS event_id, {$table}.{$column} AS happened_at, {$flag} AS flag");

            // What the person looking wrote themselves is not news to them.
            if ($authored) {
                $query->where(fn ($own) => $own->whereNull($table.'.user_id')->orWhere($table.'.user_id', '!=', $this->viewer->id));
            }

            return $query;
        };

        $union = fn () => $part('ticket_waitlists', 'waitlist', 'created_at', 'NULL')
            ->unionAll($part('event_interests', 'interest', 'confirmed_at', 'NULL'))
            ->unionAll($part('event_comments', 'comment', 'created_at', 'event_comments.is_approved', true))
            ->unionAll($part('event_photos', 'photo', 'created_at', 'event_photos.is_approved', true))
            ->unionAll($part('event_videos', 'video', 'created_at', 'event_videos.is_approved', true));

        $rows = $union()->orderByDesc('happened_at')->limit(self::LIST)->get();

        return [
            'rows' => $rows->map(fn ($row) => (object) [
                'kind' => (string) $row->kind,
                'tile' => null,
                'id' => (int) $row->id,
                'event_id' => (int) $row->event_id,
                'role_id' => null,
                'at' => $this->seconds($row->happened_at),
                'waiting' => $row->flag !== null && ! (bool) $row->flag,
            ]),
            'count' => $rows->count() < self::LIST ? $rows->count() : (int) DB::query()->fromSub($union(), 'happened')->count(),
        ];
    }

    /** A list that came back short is its own count; a full one is counted. */
    private function countOf(Collection $rows, callable $query): int
    {
        return $rows->count() < self::LIST ? $rows->count() : (int) $query()->count();
    }

    /**
     * Ticket checkouts begun in the last half hour that cost something, and how many of them are
     * paid by now. From the audit log, which is indexed by action and time; `sales` has no index
     * on created_at, and a schedule's unpaid orders are not otherwise findable by when they began.
     *
     * A sign-up and a booking write the same audit action with another prefix, and are not
     * checkouts: there is nothing to abandon. Nor is a cash order one: it is complete when it is
     * placed, and would read as "started, not paid" until the organizer marked it at the door.
     *
     * @return array{started: int, paid: int}
     */
    private function checkouts(): array
    {
        if (! $this->roleIds) {
            return ['started' => 0, 'paid' => 0];
        }

        $row = DB::table('audit_logs')
            ->join('sales', 'sales.id', '=', 'audit_logs.model_id')
            ->where('audit_logs.action', AuditService::SALE_CHECKOUT)
            ->where('audit_logs.created_at', '>=', $this->now->copy()->subMinutes(RealtimeRows::MINUTES))
            ->where('audit_logs.model_type', 'Sale')
            ->where('audit_logs.metadata', 'like', 'event_id:%')
            ->whereIn('sales.event_id', $this->ownedEventIds())
            ->where('sales.is_deleted', false)
            // A method, and not cash: an order with none falls back to the cash gateway too.
            ->whereNotNull('sales.payment_method')
            ->where('sales.payment_method', '!=', 'cash')
            ->whereRaw(self::TOTAL.' > 0')
            ->selectRaw("COUNT(*) AS started, COALESCE(SUM(CASE WHEN sales.status = 'paid' THEN 1 ELSE 0 END), 0) AS paid")
            ->first();

        return ['started' => (int) ($row->started ?? 0), 'paid' => (int) ($row->paid ?? 0)];
    }

    /**
     * Everything the rows about to be printed need, in a handful of reads however many rows
     * there are: event titles, appointment types, which schedule an event is on, and what each
     * purchase held.
     */
    private function describe(Collection $rows): void
    {
        $eventIds = $rows->pluck('event_id')->filter()->unique()->values()->all();
        if ($eventIds) {
            $this->events = DB::table('events')->whereIn('id', $eventIds)
                ->get(['id', 'name', 'appointment_type_id', 'creator_role_id', 'ticket_currency_code'])
                ->keyBy('id')->all();

            // Which of my schedules a row is filed under: the one the event is COVERED through.
            // Its own first (an event of mine that I also list elsewhere belongs under the
            // schedule that made it), else one that accepted it and is not a curator. Never a
            // curator of mine that merely lists it, or a schedule still asked: the row would
            // carry that schedule's name, and its link would open under it.
            $own = [];
            foreach (DB::table('event_role')->whereIn('event_id', $eventIds)->whereIn('role_id', $this->roleIds)->get(['event_id', 'role_id', 'is_accepted']) as $pivot) {
                $eventId = (int) $pivot->event_id;
                $roleId = (int) $pivot->role_id;

                if ((int) ($this->events[$eventId]->creator_role_id ?? 0) === $roleId) {
                    $this->eventRole[$eventId] = $roleId;
                    $own[$eventId] = true;
                } elseif (! isset($own[$eventId]) && ! isset($this->eventRole[$eventId])
                    && $pivot->is_accepted && in_array($roleId, $this->acceptingIds, true)) {
                    $this->eventRole[$eventId] = $roleId;
                }
            }
        }

        $typeIds = $rows->pluck('type_id')->filter()->unique()->values()->all();
        if ($typeIds) {
            $this->types = DB::table('appointment_types')->whereIn('id', $typeIds)->pluck('name', 'id')->all();
        }

        $purchases = $rows->whereIn('kind', ['sale', 'registration']);
        $ids = $purchases->pluck('id')->all();
        if (! $ids) {
            return;
        }

        $inPurchases = fn ($query) => $query->whereIn('sales.id', $ids)->orWhereIn('sales.group_id', $ids);

        // Tickets, not add-ons: parking is not a ticket.
        $tickets = DB::table('sale_tickets')
            ->join('sales', 'sales.id', '=', 'sale_tickets.sale_id')
            ->join('tickets', 'tickets.id', '=', 'sale_tickets.ticket_id')
            ->where('tickets.is_addon', false)
            ->where('sales.is_deleted', false)
            // A guest of the party who cancelled since is no longer part of what was bought.
            ->where('sales.status', 'paid')
            ->where($inPurchases)
            ->groupBy(DB::raw('COALESCE(sales.group_id, sales.id)'))
            ->selectRaw('COALESCE(sales.group_id, sales.id) AS purchase, SUM(sale_tickets.quantity) AS quantity')
            ->pluck('quantity', 'purchase');

        // A sign-up holds no ticket rows: its party is the rows of its group.
        $parties = DB::table('sales')
            ->where('sales.is_deleted', false)
            ->where('sales.status', 'paid')
            ->where($inPurchases)
            ->groupBy(DB::raw('COALESCE(sales.group_id, sales.id)'))
            ->selectRaw('COALESCE(sales.group_id, sales.id) AS purchase, COUNT(*) AS guests')
            ->pluck('guests', 'purchase');

        foreach ($purchases as $row) {
            $row->unit = $row->rsvp ? 'guests' : 'tickets';
            $row->quantity = max(1, (int) ($row->rsvp ? ($parties[$row->id] ?? 1) : ($tickets[$row->id] ?? 1)));
        }
    }

    /**
     * One row of the rail. Line one is the thing (an event, or what happened to a schedule); line
     * two is what it is, made on the page from labels so that no sentence needs a plural.
     */
    private function present(object $row): array
    {
        $event = $row->event_id ? ($this->events[$row->event_id] ?? null) : null;
        $roleId = $row->role_id ?: ($row->event_id ? ($this->eventRole[$row->event_id] ?? null) : null);
        $role = $roleId ? ($this->roles[$roleId] ?? null) : null;

        $out = [
            // A hash, as every key on this tab is: a sale's id is not printed for a browser.
            'key' => $this->handle($row->kind.':'.$row->id),
            'kind' => $row->kind,
            'title' => null,
            'amount' => null,
            'unit' => null,
            'quantity' => null,
            'note' => null,
            'when' => null,
            'schedule' => $this->several ? ($role?->name ?: null) : null,
            'ago' => max(0, $this->nowTs - $row->at),
            'url' => null,
        ];

        switch ($row->kind) {
            case 'sale':
            case 'registration':
                $out['title'] = self::eventLabel($event);
                $out['amount'] = $row->total > 0 ? MoneyUtils::format($row->total, $event?->ticket_currency_code ?: platform_currency()) : null;
                $out['unit'] = $row->unit ?? 'tickets';
                $out['quantity'] = $row->quantity ?? 1;
                $out['url'] = route('sales');
                break;

            case 'booking':
                $out['title'] = self::eventLabel((object) ['name' => '', 'appointment_type_id' => $row->type_id], $this->types);
                $out['amount'] = $row->total > 0 ? MoneyUtils::format($row->total, $row->currency ?: platform_currency()) : null;
                $out['when'] = $this->moment($row->starts_at, $role);
                $out['url'] = $role ? route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) : null;
                break;

            case 'follower':
            case 'subscriber':
                // The schedule is what was followed, so it is always said, one schedule or ten.
                $out['schedule'] = $role?->name ?: null;
                $out['url'] = $role ? route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'followers']) : null;
                break;

            case 'request':
                $out['title'] = self::eventLabel($event);
                $out['note'] = 'waiting';
                $out['url'] = $role ? route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'requests']) : null;
                break;

            case 'waitlist':
                $out['title'] = self::eventLabel($event);
                $out['url'] = route('waitlist.index');
                break;

            default:
                // Interest, and a comment, photo or video: the event's own form is where each is
                // read and approved.
                $out['title'] = self::eventLabel($event);
                $out['note'] = ($row->waiting ?? false) ? 'approval' : null;
                $out['url'] = $event && $role && $row->kind !== 'interest'
                    ? route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]).'?engagement=fan_content#section-engagement'
                    : null;
        }

        return $out;
    }

    /**
     * Events that are on: from the start of their day until they end, under the date their
     * tickets were sold for.
     *
     * Not "today" on the schedule's calendar, which is what /checkin opens on: on the second day
     * of a festival, and after midnight at a late show, today is not the date anything was sold
     * under, and sold and checked in would both read zero. A one-time event is on from its own
     * date until it ends; a recurring one is looked up for today and for yesterday, whose late
     * show may still be running.
     *
     * One-time events and series are looked for apart: in a single list cut by start, every
     * one-time event of the week would stand ahead of a series, whose start is its first night,
     * and the house band of ten years would be the first to go. Each kind has its own cap
     * (DOOR_SCAN); a schedule with more running series than that loses the oldest of them.
     *
     * @return array{events: list<array>, more: int}
     */
    private function door(): array
    {
        $utc = Carbon::now('UTC');
        $format = 'Y-m-d H:i:s';

        $ticketed = fn () => Event::query()
            ->whereIn('events.id', $this->ownedEventIds())
            ->where(fn ($query) => $query->where('events.tickets_enabled', true)->orWhere('events.rsvp_enabled', true))
            ->where('events.is_cancelled', false)
            ->whereNotNull('events.starts_at')
            // Only what deciding "is it on" needs. What a card needs (plans, tickets) is loaded
            // for the handful that get one, not for everything looked at.
            ->with(['creatorRole']);

        try {
            // One-time events that have begun their day somewhere on earth and have not ended.
            // The end is worked out in SQL from the event's own length (minutes, since half an
            // hour is a length), so a ten-day festival is still here on day nine; the lower bound
            // on the start is only for the index, and a year is the longest an event may be.
            $oneOffs = $ticketed()
                ->whereNull('events.days_of_week')
                ->whereBetween('events.starts_at', [$utc->copy()->subDays(367)->format($format), $utc->copy()->addHours(26)->format($format)])
                ->whereRaw(
                    'DATE_ADD(events.starts_at, INTERVAL (CASE WHEN events.duration > 0 THEN ROUND(events.duration * 60) ELSE ? END) MINUTE) >= ?',
                    [self::DOOR_HOURS * 60, $utc->format($format)]
                )
                ->orderBy('events.starts_at')
                ->limit(self::DOOR_SCAN)
                ->get();

            // Series that are still running, or whose last night was within two days (it may be
            // the show that is on). One that ended last year is not looked at.
            $series = Event::constrainToOccurrencesSince($ticketed()->whereNotNull('events.days_of_week'), $utc->copy()->subDays(2))
                ->orderByDesc('events.id')
                ->limit(self::DOOR_SCAN)
                ->get();
        } catch (Throwable $e) {
            report($e);

            return ['events' => [], 'more' => 0];
        }

        $on = [];
        foreach ($oneOffs->concat($series) as $event) {
            try {
                foreach ($this->datesOn($event) as $date => $start) {
                    $on[] = ['event' => $event, 'date' => $date, 'start' => $start];
                }
            } catch (Throwable $e) {
                // One event with a date nothing can parse must not take the card with it.
                report($e);
            }
        }

        // Earliest first: what is in progress, then what is still to come today.
        usort($on, fn ($a, $b) => $a['start']->getTimestamp() <=> $b['start']->getTimestamp());

        // The plans and ticket types of the first few, in one read each. Past these (cards that
        // could not be built) an event loads its own.
        (new EloquentCollection(collect(array_slice($on, 0, self::DOOR * 2))->pluck('event')->unique('id')->values()->all()))
            ->loadMissing(['roles.subscriptions', 'tickets']);

        // Four CARDS, not the first four entries: an event with nothing to count (its only
        // ticket type is a pass) or one that cannot be counted has no card, and must not take a
        // place from one that has. "More" is what is left after the last entry looked at.
        $events = [];
        $used = 0;
        foreach ($on as $index => $entry) {
            if (count($events) >= self::DOOR) {
                break;
            }
            $used = $index + 1;

            try {
                $entry['event']->loadMissing(['roles.subscriptions', 'tickets']);
                if ($card = $this->doorCard($entry['event'], $entry['date'], $entry['start'])) {
                    $events[] = $card;
                }
            } catch (Throwable $e) {
                // One event that cannot be counted loses its own card and nobody else's.
                report($e);
            }
        }

        return ['events' => $events, 'more' => max(0, count($on) - $used)];
    }

    /**
     * The dates of $event that are on now, each with its start on the schedule's clock.
     *
     * @return array<string, Carbon>
     */
    private function datesOn(Event $event): array
    {
        $zone = $event->scheduleTimezone();
        $today = Carbon::now($zone)->format('Y-m-d');
        $out = [];

        $stillOn = function (?string $date) use ($event): ?Carbon {
            $start = $event->getStartDateTime($date, true);
            // With a length, until it ends. With none there is no end to go by (the two hours
            // getEndDateTime() assumes are for drawing a calendar, not for closing a door).
            $until = (float) $event->duration > 0
                ? $event->getEndDateTime($date, true)
                : $start->copy()->addHours(self::DOOR_HOURS);

            return $this->nowTs < $until->getTimestamp() ? $start : null;
        };

        if (! $event->days_of_week) {
            $date = $event->saleEventDateFromStartsAt();
            if ($date && $date <= $today && ($start = $stillOn(null))) {
                $out[$date] = $start;
            }

            return $out;
        }

        // Today's occurrence, and yesterday's, whose late show may still be running. For a
        // series whose occurrences run longer than a day (a weekly Friday-to-Sunday market),
        // as far back as one of them can reach: on its third day the one that is on began the
        // day before yesterday.
        $back = (float) $event->duration > 24
            ? min(self::DOOR_LOOKBACK_DAYS, (int) ceil((float) $event->duration / 24))
            : 1;

        for ($days = 0; $days <= $back; $days++) {
            $date = Carbon::now($zone)->subDays($days)->format('Y-m-d');
            if ($event->matchesDate($date) && ($start = $stillOn($date))) {
                $out[$date] = $start;
            }
        }

        return $out;
    }

    private function doorCard(Event $event, string $date, Carbon $start): ?array
    {
        // Sales are the event's owner's to read, as everywhere (User::canViewEventData()); the
        // query already said so, and this is the same answer asked of the event itself.
        if (! $this->viewer->canViewEventData($event)) {
            return null;
        }

        $line = EventSeats::line($event, $date);
        if ($line === null) {
            return null;
        }

        // The schedule the event is covered through, as in describe(): its own, else one of mine
        // that accepted it and is not a curator.
        $role = collect($event->roles)->first(fn ($role) => isset($this->roles[$role->id]) && $role->id === $event->creator_role_id)
            ?? collect($event->roles)->first(fn ($role) => in_array((int) $role->id, $this->acceptingIds, true) && $role->pivot?->is_accepted);

        // Scanning records nothing for a sign-up, and check-in is a paid plan's page: either way
        // there is no arrival count to show, and the card says how many are coming instead.
        $scans = ! $event->rsvp_enabled && $event->isPro();
        $arrived = $scans ? EventSeats::checkedIn($event, $date, $this->nowTs) : null;
        $today = Carbon::now($event->scheduleTimezone())->format('Y-m-d');

        return [
            'key' => $this->handle('door:'.$event->id.':'.$date),
            'name' => self::eventLabel($event),
            'time' => $start->translatedFormat(get_use_24_hour_time($role) ? 'H:i' : 'g:i A'),
            // Said only when it is not today's: day two of a festival, a show past midnight.
            'day' => $date !== $today ? $start->translatedFormat('D, M j') : null,
            'schedule' => $this->several ? ($role?->name ?: null) : null,
            'sold' => $line['sold'],
            'capacity' => $line['capacity'],
            'signups' => (bool) $event->rsvp_enabled,
            'checked_in' => $arrived ? $arrived['count'] : null,
            'recent' => $arrived ? $arrived['recent'] : null,
            'floor' => $arrived ? $arrived['truncated'] : false,
            'url' => $scans ? route('checkin.index', ['event' => UrlUtils::encodeId($event->id)]) : route('sales'),
        ];
    }

    /** A stored UTC event time on the booked schedule's clock: "Thu, Oct 8, 3:00 PM". */
    private function moment(string $startsAt, ?Role $role): ?string
    {
        if ($startsAt === '') {
            return null;
        }

        try {
            $at = Carbon::parse($startsAt, 'UTC')->setTimezone($role?->timezone ?: config('app.timezone'));

            return $at->translatedFormat('D, M j').', '.$at->translatedFormat(get_use_24_hour_time($role) ? 'H:i' : 'g:i A');
        } catch (Throwable) {
            return null;
        }
    }

    /** A timestamp column written on the app's clock, as a Unix second. */
    private function seconds(mixed $value): int
    {
        return Carbon::parse((string) $value, config('app.timezone'))->getTimestamp();
    }

    private function handle(string $subject): string
    {
        return substr(hash_hmac('sha256', 'rt-activity|'.$this->viewer->id.'|'.$this->salt.'|'.$subject, (string) config('app.key')), 0, 12);
    }
}
