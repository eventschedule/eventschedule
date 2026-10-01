<?php

namespace App\Services;

use App\Models\Event;
use App\Models\GiftCard;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SupportConversation;
use App\Models\User;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * The Activity card on /admin/realtime: what happened in the last 24 hours that a founder cares
 * about (sign-ups, new schedules and events, orders, plan changes, support chats).
 *
 * It reads audit_logs, which the app keeps for security and operations whether or not anyone
 * accepted cookies, so it does not depend on the realtime beacon at all and ignores the page's
 * filters. Each rule below exists because the audit trail records more than the sentence claims:
 *
 *  - auth.register is also written for an account made by following a schedule or by a claim;
 *    only the organizer doors pass model_type = 'User'. A Google or Facebook sign-up is logged as
 *    a provider login with metadata 'new_account'.
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

    /** Event creations by one person on one schedule this close together read as one item. */
    private const BURST_SECONDS = 600;

    /**
     * @param  array<int, array{now: bool, ago: int}>  $onSite  user id => presence, for the "On the site" note
     * @return array{summary: ?string, items: list<array>, cursor: ?int}
     */
    public function build(bool $showAdmins, array $onSite = []): array
    {
        $since = now()->subDay();

        $rows = DB::table('audit_logs')
            ->whereIn('action', self::ACTIONS)
            ->where('created_at', '>=', $since)
            // Scheduler imports, in SQL so they never spend the 2,000-row budget. orWhereNull: a
            // bare `user_agent != 'Symfony'` would also drop every row whose user agent is NULL.
            ->where(fn ($query) => $query->where('action', '!=', AuditService::EVENT_CREATE)
                ->orWhereNull('user_agent')
                ->orWhere('user_agent', '!=', 'Symfony'))
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
        $items = $this->collapseEventBursts($items);

        return [
            'summary' => $this->summary($counts),
            'items' => $this->render($items->take(self::LIMIT), $onSite),
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

    private function summary(array $counts): ?string
    {
        $parts = [];
        foreach ($counts as $kind => $count) {
            if ($count > 0) {
                $parts[] = trans_choice('messages.realtime_count_'.$kind, $count, ['count' => number_format($count)]);
            }
        }

        return $parts ? implode(' · ', $parts) : null;
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
            ];
        })->values()->all();
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
