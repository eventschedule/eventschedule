<?php

namespace App\Services;

use App\Models\Event;
use App\Models\GiftCard;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SupportConversation;
use App\Models\User;
use App\Utils\MoneyUtils;
use App\Utils\SignupSource;
use App\Utils\UrlUtils;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;

/**
 * The Activity and Sign-ups cards on /admin/realtime: what happened in the last 24 hours that a
 * founder cares about (sign-ups, new schedules and events, orders, plan changes, support chats),
 * and how far each person who signed up has got since.
 *
 * It reads audit_logs, which the app keeps for security and operations whether or not anyone
 * accepted cookies, so it does not depend on the realtime beacon at all and ignores the page's
 * filters. Each rule below exists because the audit trail records more than the sentence claims:
 *
 *  - auth.register is also written for an account made by following a schedule or by a claim;
 *    only the sign-up form and the API pass model_type = 'User'. That form serves every intent
 *    (signup_intent_from_session(): follow, request, fan, team), so a "sign-up" is not always an
 *    organizer, and the Sign-ups card labels the ones that are not. A Google or Facebook sign-up is
 *    logged as a provider login with metadata 'new_account'.
 *  - event.create also fires for scheduler imports, which carry the console request's 'Symfony'
 *    user agent. Inbound calendar sync never audits.
 *  - sale.paid is also written for amount_mismatch, and gift_card.paid likewise, so the new status
 *    decides. Manually marked-paid sales are not audited, so the count says "orders", not revenue.
 *  - audit_logs.created_at is written with now() in APP_TIMEZONE, so this class compares in the
 *    app's zone, not UTC like the realtime table.
 */
class RealtimeActivity
{
    private const ACTIONS = [
        AuditService::AUTH_REGISTER,
        AuditService::API_REGISTER,
        AuditService::AUTH_GOOGLE_LOGIN,
        AuditService::AUTH_FACEBOOK_LOGIN,
        AuditService::SCHEDULE_CREATE,
        AuditService::EVENT_CREATE,
        AuditService::SALE_PAID,
        AuditService::SUBSCRIPTION_CREATE,
        AuditService::SUBSCRIPTION_CANCEL,
        AuditService::TICKET_TRIAL_START,
        AuditService::GIFT_CARD_PAID,
    ];

    private const LIMIT = 20;

    /** The kinds a count button can filter the feed to. */
    public const FEEDS = ['signup', 'schedule', 'events', 'order', 'upgrade'];

    /** The Sign-ups card lists this many people; its count, steps and hourly strip cover everyone. */
    private const SIGNUP_ROWS = 50;

    /** Event creations by one person on one schedule this close together read as one item. */
    private const BURST_SECONDS = 600;

    /**
     * @param  array<int, array{now: bool, ago: int, stuck: ?int, surface: string}>  $onSite  user id => presence, for the "On the site" note
     * @param  ?string  $feed  one of FEEDS, to list only that kind
     * @return array{stats: list<array>, feed: ?string, items: list<array>, signups: array, cursor: ?int}
     */
    public function build(bool $showAdmins, array $onSite = [], ?string $feed = null): array
    {
        $feed = in_array($feed, self::FEEDS, true) ? $feed : null;
        $since = now()->subDay();

        $rows = DB::table('audit_logs')
            ->whereIn('action', self::ACTIONS)
            ->where('created_at', '>=', $since)
            // Scheduler imports, in SQL so they never spend the 2,000-row budget. orWhereNull: a
            // bare `user_agent != 'Symfony'` would also drop every row whose user agent is NULL.
            ->where(fn ($query) => $query->where('action', '!=', AuditService::EVENT_CREATE)
                ->orWhereNull('user_agent')
                ->orWhere('user_agent', '!=', 'Symfony'))
            // An ordinary Google or Facebook login is the same action as a sign-up through one. Only
            // the sign-ups are wanted, and the logins would otherwise spend the row budget too.
            ->where(fn ($query) => $query->whereNotIn('action', [AuditService::AUTH_GOOGLE_LOGIN, AuditService::AUTH_FACEBOOK_LOGIN])
                ->orWhere('metadata', 'new_account'))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(2000)
            ->get(['id', 'user_id', 'action', 'model_type', 'model_id', 'new_values', 'user_agent', 'metadata', 'created_at']);

        $excludedUsers = User::where('email', DemoService::DEMO_EMAIL)->pluck('id');
        if (! $showAdmins) {
            $excludedUsers = $excludedUsers->merge(User::where('is_admin', true)->pluck('id'));
        }
        $excludedUsers = $excludedUsers->flip();

        $items = collect();

        foreach ($rows as $row) {
            if ($row->user_id && $excludedUsers->has($row->user_id)) {
                continue;
            }

            $type = $this->classify($row);
            if ($type === null) {
                continue;
            }

            // The no-user rule: a sale may have none (guest checkout), everything else must.
            if (! $row->user_id && ! in_array($type, ['order', 'gift_card'], true)) {
                continue;
            }

            $items->push(['type' => $type, 'row' => $row, 'at' => Carbon::parse($row->created_at)]);
        }

        $items = $items->merge($this->supportChats($since, $excludedUsers))
            ->sortByDesc(fn ($item) => $item['at']->getTimestamp())
            ->values();

        $counts = $this->counts($items);

        // Filtered before the cap, so a count button always lists everything it counted (an order
        // from this morning sits well outside the newest 20 items by the evening).
        $listed = $this->collapseEventBursts($feed ? $items->where('type', $feed)->values() : $items);

        return [
            'stats' => $this->stats($counts, $feed),
            'feed' => $feed,
            'items' => $this->render($listed->take(self::LIMIT), $onSite),
            'signups' => $this->signups($items->where('type', 'signup')->values(), $onSite, $excludedUsers),
            'cursor' => $items->isNotEmpty() ? $items->first()['at']->getTimestamp() : null,
        ];
    }

    private function classify(object $row): ?string
    {
        $newValues = json_decode((string) $row->new_values, true) ?: [];

        return match ($row->action) {
            AuditService::AUTH_REGISTER, AuditService::API_REGISTER => $row->model_type === 'User' ? 'signup' : null,
            AuditService::AUTH_GOOGLE_LOGIN, AuditService::AUTH_FACEBOOK_LOGIN => $row->metadata === 'new_account' ? 'signup' : null,
            AuditService::SCHEDULE_CREATE => 'schedule',
            AuditService::EVENT_CREATE => $row->user_agent === 'Symfony' ? null : 'events',
            AuditService::SALE_PAID => ($newValues['status'] ?? null) === 'paid' ? 'order' : null,
            AuditService::SUBSCRIPTION_CREATE => $row->model_type === 'Role' ? 'upgrade' : null,
            AuditService::SUBSCRIPTION_CANCEL => $row->model_type === 'Role' ? 'cancel' : null,
            AuditService::TICKET_TRIAL_START => 'trial',
            AuditService::GIFT_CARD_PAID => ($newValues['status'] ?? null) === 'active' ? 'gift_card' : null,
            default => null,
        };
    }

    private function supportChats(Carbon $since, Collection $excludedUsers): Collection
    {
        if (! Route::has('admin.support')) {
            return collect();
        }

        return SupportConversation::where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id', 'user_id', 'guest_name', 'created_at'])
            ->reject(fn ($conversation) => $conversation->user_id && $excludedUsers->has($conversation->user_id))
            ->map(fn ($conversation) => ['type' => 'support', 'row' => $conversation, 'at' => Carbon::parse($conversation->created_at)])
            ->values();
    }

    private function counts(Collection $items): array
    {
        $counts = $items->countBy('type')->all();

        return [
            'signups' => $counts['signup'] ?? 0,
            'schedules' => $counts['schedule'] ?? 0,
            'events' => $counts['events'] ?? 0,
            'orders' => $counts['order'] ?? 0,
            'upgrades' => $counts['upgrade'] ?? 0,
        ];
    }

    /**
     * The count buttons. Upgrades are rare enough that a permanent zero would be noise, so that
     * button appears with the first one (or while the feed is filtered to it).
     */
    private function stats(array $counts, ?string $feed): array
    {
        $stats = [
            ['type' => 'signup', 'label' => __('messages.realtime_signups'), 'count' => $counts['signups']],
            ['type' => 'schedule', 'label' => __('messages.schedules'), 'count' => $counts['schedules']],
            ['type' => 'events', 'label' => __('messages.events'), 'count' => $counts['events']],
            ['type' => 'order', 'label' => __('messages.realtime_orders'), 'count' => $counts['orders']],
        ];

        if ($counts['upgrades'] > 0 || $feed === 'upgrade') {
            $stats[] = ['type' => 'upgrade', 'label' => __('messages.realtime_upgrades'), 'count' => $counts['upgrades'], 'wide' => true];
        }

        return $stats;
    }

    /**
     * The Sign-ups card: everyone who signed up in the window, newest first, with how far each has
     * got. Same audit rows as the feed's sign-up items (and the same keys, so one highlight covers
     * both), which is what keeps the count button and this list in step.
     *
     * The three steps follow GrowthExportService::signupRows(), so this card and Insights never
     * disagree about one person: a deleted schedule still counts as saved, and an add-on or a
     * deleted type is not a ticket. They are cumulative (a later step implies the earlier ones),
     * or "saved an event" could exceed "saved a schedule". An anonymous guest submission is not the
     * person's own event.
     *
     * @param  Collection<int, array>  $items  the classified sign-up items, newest first
     */
    private function signups(Collection $items, array $onSite, Collection $excludedUsers): array
    {
        $labels = [
            __('messages.funnel_stage_account'),
            __('messages.funnel_stage_saved_schedule'),
            __('messages.funnel_stage_saved_event'),
            __('messages.funnel_stage_saved_ticket'),
        ];
        $hours = array_fill(0, 24, 0);

        if ($items->isEmpty()) {
            return [
                'total' => 0, 'steps' => [], 'stage_titles' => array_slice($labels, 1), 'hours' => $hours,
                'rows' => [], 'marks' => [], 'more_text' => null, 'last_ago' => $this->lastSignupAgo($excludedUsers),
            ];
        }

        $nowTs = now()->getTimestamp();
        $userIds = $items->map(fn ($item) => $item['row']->user_id)->filter()->unique()->values();

        $users = User::with('referredBy:id,name')->whereIn('id', $userIds)
            // Every column SignupSource::forUser() reads. Leave one out and this page and the admin
            // dashboard name different sources for the same person, with no error.
            ->get(['id', 'name', 'email', 'signup_intent', 'utm_source', 'utm_medium', 'utm_campaign', 'referrer_url', 'landing_page', 'referred_by_user_id'])
            ->keyBy('id');

        $schedules = DB::table('roles')->whereIn('user_id', $userIds)->orderBy('id')
            ->get(['id', 'user_id', 'name', 'is_deleted'])->groupBy('user_id');
        $events = DB::table('events')->whereIn('user_id', $userIds)->where('is_guest_submission', false)
            ->selectRaw('user_id, COUNT(*) as total')->groupBy('user_id')->pluck('total', 'user_id');
        $tickets = DB::table('events')->join('tickets', 'tickets.event_id', '=', 'events.id')
            ->whereIn('events.user_id', $userIds)
            ->where('tickets.is_deleted', false)
            ->where('tickets.is_addon', false)
            ->distinct()->pluck('events.user_id')->flip();

        $stageOf = fn (?int $userId): int => match (true) {
            $userId === null || ! $users->has($userId) => 0,
            $tickets->has($userId) => 3,
            (int) ($events[$userId] ?? 0) > 0 => 2,
            $schedules->has($userId) => 1,
            default => 0,
        };

        $reached = [0, 0, 0, 0];
        $marks = [];

        foreach ($items as $item) {
            $ago = max(0, $nowTs - $item['at']->getTimestamp());
            $stage = $stageOf($item['row']->user_id);

            for ($step = 0; $step <= $stage; $step++) {
                $reached[$step]++;
            }

            $hours[23 - min(23, intdiv($ago, 3600))]++;

            // The per-minute chart marks the ones inside its own window.
            if ($ago < RealtimeDashboard::WINDOW_MINUTES * 60) {
                $name = $users->get($item['row']->user_id)?->name ?: __('messages.realtime_someone');
                $marks[] = ['ago' => $ago, 'text' => $this->signupText($item['row'], $name)];
            }
        }

        $rows = $items->take(self::SIGNUP_ROWS)->map(function ($item) use ($users, $schedules, $events, $stageOf, $labels, $onSite, $nowTs) {
            $row = $item['row'];
            $user = $users->get($row->user_id);
            $stage = $stageOf($row->user_id);
            $eventCount = $user ? (int) ($events[$user->id] ?? 0) : 0;
            $schedule = $user ? ($schedules->get($user->id) ?? collect())->firstWhere('is_deleted', 0) : null;
            $presence = $user ? ($onSite[$user->id] ?? null) : null;
            // "No event yet" is about people who have not created one.
            $stuck = $presence && $eventCount === 0 ? ($presence['stuck'] ?? null) : null;
            $intent = $user?->signup_intent;

            return [
                'key' => 'signup:'.$row->id,
                'name' => $user ? ($user->name ?: $user->email) : __('messages.realtime_someone'),
                'email' => $user?->email,
                'initials' => RealtimeDashboard::initials($user ? ($user->name ?: $user->email) : '?'),
                'ago' => max(0, $nowTs - $item['at']->getTimestamp()),
                'source' => $user ? $this->signupSource($user) : null,
                'schedule' => $schedule ? [
                    'name' => $schedule->name,
                    'url' => route('admin.schedules.edit', ['role' => UrlUtils::encodeId($schedule->id)]),
                ] : null,
                'stage' => $stage,
                'stage_label' => $labels[$stage],
                'status_text' => match (true) {
                    $eventCount > 0 => trans_choice('messages.realtime_count_events', $eventCount, ['count' => number_format($eventCount)]),
                    $stage >= 1 => __('messages.realtime_no_events_yet'),
                    default => __('messages.realtime_no_schedule_yet'),
                },
                // Someone who signed up to follow or to join a team was never going to create a
                // schedule: say what they came for instead of showing an empty meter.
                'intent_label' => $stage === 0 && $intent && $intent !== 'organizer'
                    ? (Lang::has('messages.signup_intent_'.$intent) ? __('messages.signup_intent_'.$intent) : $intent)
                    : null,
                'person_id' => $presence ? 'u:'.UrlUtils::encodeId($user->id) : null,
                'on_site' => $presence ? ($presence['now'] ? 'now' : 'recent') : null,
                'stuck_text' => $stuck ? trans_choice('messages.realtime_stuck', $stuck, ['count' => $stuck]) : null,
            ] + $this->where($presence);
        })->values()->all();

        $total = $items->count();

        return [
            'total' => $total,
            'steps' => collect(['account', 'schedule', 'event', 'ticket'])->map(fn ($key, $index) => [
                'key' => $key, 'label' => $labels[$index], 'count' => $reached[$index],
            ])->all(),
            'stage_titles' => array_slice($labels, 1),
            'hours' => $hours,
            'rows' => $rows,
            'marks' => $marks,
            'more_text' => $total > count($rows)
                ? __('messages.realtime_showing_newest', ['shown' => number_format(count($rows)), 'total' => number_format($total)])
                : null,
            'last_ago' => null,
        ];
    }

    /**
     * Where a new account came from: App\Utils\SignupSource, the one classifier the admin dashboard
     * uses too. Null when nothing was recorded, which is the usual case for a visitor who never
     * accepted cookies: calling that "Direct" would be a guess.
     *
     * @return ?array{primary: string, secondary: ?string, channel: string}
     */
    private function signupSource(User $user): ?array
    {
        return SignupSource::display($user, $user->referred_by_user_id ? $user->referredBy?->name : null);
    }

    /**
     * How long ago the newest sign-up before the window was, for the empty card.
     */
    private function lastSignupAgo(Collection $excludedUsers): ?int
    {
        $last = DB::table('audit_logs')
            ->where(fn ($query) => $query
                ->where(fn ($form) => $form->whereIn('action', [AuditService::AUTH_REGISTER, AuditService::API_REGISTER])->where('model_type', 'User'))
                ->orWhere(fn ($social) => $social->whereIn('action', [AuditService::AUTH_GOOGLE_LOGIN, AuditService::AUTH_FACEBOOK_LOGIN])->where('metadata', 'new_account')))
            ->when($excludedUsers->isNotEmpty(), fn ($query) => $query->whereNotIn('user_id', $excludedUsers->keys()))
            ->max('created_at');

        return $last ? max(0, now()->getTimestamp() - Carbon::parse($last)->getTimestamp()) : null;
    }

    /**
     * Consecutive event creations (newest first) by the same person on the same schedule within
     * BURST_SECONDS of each other become one item carrying a count.
     */
    private function collapseEventBursts(Collection $items): Collection
    {
        $eventIds = $items->where('type', 'events')->map(fn ($item) => $item['row']->model_id)->filter()->all();
        $roleOf = $eventIds ? Event::whereIn('id', $eventIds)->pluck('creator_role_id', 'id') : collect();

        $collapsed = collect();

        foreach ($items as $item) {
            if ($item['type'] === 'events') {
                $item['role_id'] = $roleOf[$item['row']->model_id] ?? null;
                $item['count'] = 1;

                $last = $collapsed->last();
                if ($last && $last['type'] === 'events'
                    && $last['row']->user_id === $item['row']->user_id
                    && $last['role_id'] === $item['role_id']
                    && $last['oldest']->getTimestamp() - $item['at']->getTimestamp() <= self::BURST_SECONDS) {
                    $last['count']++;
                    $last['oldest'] = $item['at'];
                    $collapsed->put($collapsed->keys()->last(), $last);

                    continue;
                }

                $item['oldest'] = $item['at'];
            }

            $collapsed->push($item);
        }

        return $collapsed;
    }

    private function render(Collection $items, array $onSite): array
    {
        $rows = $items->pluck('row');
        $userIds = $rows->pluck('user_id')->filter()->unique();
        $users = User::whereIn('id', $userIds)->get(['id', 'name', 'email'])->keyBy('id');

        $roleIds = $items->map(fn ($item) => match ($item['type']) {
            'schedule', 'upgrade', 'cancel', 'trial' => $item['row']->model_id,
            'events' => $item['role_id'] ?? null,
            default => null,
        })->filter();

        $sales = Sale::with(['event:id,name,creator_role_id,ticket_currency_code', 'saleTickets.ticket'])
            ->whereIn('id', $items->where('type', 'order')->map(fn ($item) => $item['row']->model_id)->filter())
            ->get()->keyBy('id');
        $giftCards = GiftCard::whereIn('id', $items->where('type', 'gift_card')->map(fn ($item) => $item['row']->model_id)->filter())
            ->get(['id', 'role_id', 'amount', 'currency_code'])->keyBy('id');

        $roleIds = $roleIds
            ->merge($sales->map(fn ($sale) => $sale->event?->creator_role_id))
            ->merge($giftCards->pluck('role_id'))
            ->filter()->unique();
        $roles = Role::whereIn('id', $roleIds)->get(['id', 'name', 'user_id'])->keyBy('id');

        // A sign-up links to the person's first schedule, if they made one: there is no per-user admin page.
        $firstSchedules = Role::whereIn('user_id', $items->where('type', 'signup')->map(fn ($item) => $item['row']->user_id)->filter())
            ->where('is_deleted', false)
            ->orderBy('id')
            ->get(['id', 'user_id'])
            ->unique('user_id')
            ->keyBy('user_id');

        $nowTs = now()->getTimestamp();

        return $items->map(function ($item) use ($users, $roles, $sales, $giftCards, $firstSchedules, $onSite, $nowTs) {
            $row = $item['row'];
            $user = $row->user_id ? $users->get($row->user_id) : null;
            $name = $user?->name ?: __('messages.realtime_someone');
            $roleId = null;

            $text = match ($item['type']) {
                'signup' => $this->signupText($row, $name),
                'schedule' => __('messages.realtime_activity_created_schedule', ['name' => $name, 'schedule' => $this->roleName($roles, $roleId = $row->model_id)]),
                'events' => trans_choice('messages.realtime_activity_added_events', $item['count'], [
                    'name' => $name, 'count' => number_format($item['count']), 'schedule' => $this->roleName($roles, $roleId = $item['role_id']),
                ]),
                'order' => $this->orderText($sales->get($row->model_id), $roleId),
                'upgrade' => __('messages.realtime_activity_upgraded', [
                    'name' => $name, 'schedule' => $this->roleName($roles, $roleId = $row->model_id),
                    'plan' => ucfirst((string) (json_decode((string) $row->new_values, true)['plan_type'] ?? 'pro')),
                ]),
                'cancel' => __('messages.realtime_activity_canceled', ['name' => $name, 'schedule' => $this->roleName($roles, $roleId = $row->model_id)]),
                'trial' => __('messages.realtime_activity_trial', ['name' => $name, 'schedule' => $this->roleName($roles, $roleId = $row->model_id)]),
                'gift_card' => $this->giftCardText($giftCards->get($row->model_id), $roles, $roleId),
                'support' => $row->user_id
                    ? __('messages.realtime_activity_support_user', ['name' => $name])
                    : __('messages.realtime_activity_support'),
                default => '',
            };

            if ($item['type'] === 'signup' && $firstSchedules->has($row->user_id)) {
                $roleId = $firstSchedules->get($row->user_id)->id;
            }

            $url = match (true) {
                $item['type'] === 'support' => route('admin.support', ['c' => UrlUtils::encodeId($row->id)]),
                $roleId && $roles->has($roleId) || ($item['type'] === 'signup' && $roleId) => route('admin.schedules.edit', ['role' => UrlUtils::encodeId($roleId)]),
                default => null,
            };

            $ago = max(0, $nowTs - $item['at']->getTimestamp());
            $presence = $row->user_id ? ($onSite[$row->user_id] ?? null) : null;

            return [
                'key' => $item['type'].':'.$row->id,
                'type' => $item['type'],
                'ago' => $ago,
                'text' => $text,
                'url' => $url,
                'recent' => $ago <= 1800,
                'person_id' => $presence ? 'u:'.UrlUtils::encodeId($row->user_id) : null,
                'on_site' => $presence ? ($presence['now'] ? 'now' : 'recent') : null,
            ] + $this->where($presence);
        })->values()->all();
    }

    /**
     * Which part of the site a person is in, for the note beside "On the site now". Only while they
     * are there: where someone was before they left says nothing about now.
     *
     * @return array{surface: ?string, surface_label: ?string}
     */
    private function where(?array $presence): array
    {
        $surface = $presence && $presence['now'] ? ($presence['surface'] ?? null) : null;

        return [
            'surface' => $surface,
            'surface_label' => $surface ? __('messages.realtime_surface_'.$surface) : null,
        ];
    }

    private function signupText(object $row, string $name): string
    {
        return match ($row->action) {
            AuditService::AUTH_GOOGLE_LOGIN => __('messages.realtime_activity_signed_up_with', ['name' => $name, 'provider' => 'Google']),
            AuditService::AUTH_FACEBOOK_LOGIN => __('messages.realtime_activity_signed_up_with', ['name' => $name, 'provider' => 'Facebook']),
            default => __('messages.realtime_activity_signed_up', ['name' => $name]),
        };
    }

    private function orderText(?Sale $sale, ?int &$roleId): string
    {
        if (! $sale) {
            return trans_choice('messages.realtime_activity_order_unknown', 1);
        }

        $roleId = $sale->event?->creator_role_id;
        $quantity = max(1, (int) $sale->quantity());
        $event = $sale->event?->name ?: __('messages.realtime_deleted_event');
        $amount = (float) $sale->payment_amount;

        if ($amount <= 0) {
            return trans_choice('messages.realtime_activity_order_free', $quantity, ['count' => $quantity, 'event' => $event]);
        }

        return trans_choice('messages.realtime_activity_order', $quantity, [
            'count' => $quantity,
            'event' => $event,
            'amount' => MoneyUtils::format($amount, $sale->event?->ticket_currency_code),
        ]);
    }

    private function giftCardText(?GiftCard $giftCard, Collection $roles, ?int &$roleId): string
    {
        $roleId = $giftCard?->role_id;

        return __('messages.realtime_activity_gift_card', [
            'schedule' => $this->roleName($roles, $roleId),
            'amount' => $giftCard ? MoneyUtils::format((float) $giftCard->amount, $giftCard->currency_code) : '',
        ]);
    }

    private function roleName(Collection $roles, ?int $roleId): string
    {
        return ($roleId ? $roles->get($roleId)?->name : null) ?: __('messages.realtime_deleted_schedule');
    }
}
