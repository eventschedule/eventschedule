<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\OwnerDigest;
use App\Models\AnalyticsDaily;
use App\Models\Event;
use App\Models\Role;
use App\Services\DemoService;
use App\Utils\OwnerLocalTime;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The weekly owner digest: last week's numbers and the coming week's dates, one email per owner
 * covering every schedule of theirs that is active.
 *
 * Retention is the problem it answers: 12 of the 401 schedules created up to 2026-02 had an event
 * in the last 90 days. Every other owner email is triggered by a single event (a sale, a request),
 * so a schedule running quietly heard nothing from us at all.
 *
 * Bounded like the activation nudges, because it reaches people who have not asked for anything:
 *  - only schedules with a public event in the last 90 days or an occurrence still to come, so it
 *    is never a mailshot to the 542 dormant schedules;
 *  - a schedule with nothing to report (no views, no sign-ups, no sales, nothing coming up) is
 *    left out, and an owner with no schedule left gets no email that week;
 *  - one email per owner per ISO week, claimed with a unique index before sending;
 *  - the owner's own weekly_digest notification setting (default on) and users.is_subscribed.
 *
 * Scheduled HOURLY on both rails, and it sends to each owner only on Monday morning in their own
 * timezone (OwnerLocalTime): one UTC hour would be midnight somewhere. The claim is the owner's
 * LOCAL ISO week, so the hourly runs inside that window cannot send twice. No flag is a dry run;
 * --now skips the local-time gate for hand runs and tests.
 */
class SendOwnerDigests extends Command
{
    protected $signature = 'app:send-owner-digests
        {--apply : Send the emails. Without it, print what would be sent}
        {--user= : Only this owner (user id)}
        {--now : Ignore the Monday-morning local window. For tests and debugging only: a digest sent this way counts, so the next scheduled Monday within five days is skipped. The scheduler never passes it.}';

    protected $description = 'Send each active schedule owner a weekly summary of their schedules';

    /** How many upcoming dates one schedule's section lists. */
    private const UPCOMING_LIMIT = 5;

    /** How many schedules one email shows; the rest are "and N more on your dashboard". */
    private const SECTION_LIMIT = 8;

    /** Fewer views than this, and nothing else, is not news. */
    private const MIN_VIEWS = 5;

    public function handle(): int
    {
        if (! config('app.hosted')) {
            $this->info('Skipping: not in hosted mode.');

            return self::SUCCESS;
        }

        $apply = (bool) $this->option('apply');
        $ignoreLocalTime = (bool) $this->option('now');
        // Seven calendar days including today: today and the six before it.
        $since = now()->subDays(6)->startOfDay();
        $batch = max(1, (int) config('usage.owner_digest_batch', 500));

        $owners = $this->candidateRoles()->groupBy('user_id');
        $sent = 0;

        foreach ($owners as $userId => $roles) {
            if ($sent >= $batch) {
                $this->info("Batch limit of {$batch} reached; the rest are due on the next run.");
                break;
            }

            $user = $roles->first()->user;
            $locale = $user->language_code ?: config('app.locale');

            // The owner's own Monday morning, and their own ISO week for the claim, so the hourly
            // runs across that window resolve to one week and one send.
            $localNow = OwnerLocalTime::now($user);
            $week = $localNow->format('o-\WW');

            if (! $ignoreLocalTime && (! $localNow->isMonday() || $localNow->hour < OwnerLocalTime::MORNING_FROM || $localNow->hour > OwnerLocalTime::MORNING_TO)) {
                continue;
            }

            if (DB::table('owner_digests')->where('user_id', $user->id)->where('week', $week)->exists()) {
                continue;
            }

            $sections = $roles
                ->filter(fn (Role $role) => $role->getEditorsWantingNotification('weekly_digest')->contains('id', $user->id))
                ->map(fn (Role $role) => $this->section($role, $since, $locale))
                ->filter()
                // The busiest first, so a long list leads with what matters: money, then people,
                // then attention.
                ->sortByDesc(fn ($section) => [$section['tickets'] + $section['rsvps'], $section['followers'] + $section['subscribers'], $section['views']])
                ->values()
                ->all();

            if (empty($sections)) {
                continue;
            }

            $more = max(0, count($sections) - self::SECTION_LIMIT);
            $sections = array_slice($sections, 0, self::SECTION_LIMIT);

            if (! $apply) {
                $this->line("would send {$user->email}: ".implode(', ', array_column($sections, 'name')).($more ? " (+{$more} more)" : ''));
                $sent++;

                continue;
            }

            // The claim, before the send. Stands if the dispatch throws: "only ever moves forward"
            // is what makes two scheduler rails safe.
            $claimed = DB::table('owner_digests')->insertOrIgnore([
                'user_id' => $user->id,
                'week' => $week,
                'schedules' => count($sections),
                'created_at' => now(),
            ]);

            if ($claimed === 0) {
                continue;
            }

            try {
                SendQueuedEmail::dispatch(
                    new OwnerDigest($user, $sections, $more),
                    $user->email,
                    // Platform mailer, never a schedule's own SMTP: this is our email about their
                    // account, it must not count against their allowance, and a schedule's SMTP
                    // failure window would drop it after the claim was written.
                    null,
                    $locale
                );
                $sent++;
            } catch (\Throwable $e) {
                Log::error('Failed to queue owner digest', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                $this->error("Failed for {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info(($apply ? 'Queued' : 'Would send')." {$sent} weekly digest(s).");

        return self::SUCCESS;
    }

    /**
     * Active schedules whose owner can be emailed. Whether the owner already had THIS week's
     * digest is checked per owner in handle(), because "this week" is the owner's local week.
     */
    private function candidateRoles(): Collection
    {
        $public = fn ($q) => $q->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_internal', false);

        return Role::query()
            ->where('is_deleted', false)
            ->whereNotNull('user_id')
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->whereHas('user', fn ($u) => $u->where('is_subscribed', true)
                ->where('email', '!=', DemoService::DEMO_EMAIL)
                ->whereNotNull('email_verified_at'))
            // Not emailed in the last five days, whatever week that was: a cheap pre-filter
            // that keeps the hourly runs from rebuilding sections for owners already done.
            ->whereNotExists(fn ($q) => $q->selectRaw('1')
                ->from('owner_digests')
                ->whereColumn('owner_digests.user_id', 'roles.user_id')
                ->where('owner_digests.created_at', '>=', now()->subDays(5)))
            ->where(fn ($q) => $q
                ->whereHas('events', fn ($e) => $public($e)->where('events.starts_at', '>=', now()->subDays(90)))
                ->orWhereHas('events', fn ($e) => $public($e)->hasUpcomingOccurrence()))
            ->when($this->option('user'), fn ($q, $userId) => $q->where('user_id', (int) $userId))
            ->with('user')
            ->orderBy('user_id')
            ->orderBy('name')
            ->get();
    }

    /** One schedule's part of the email, or null when there is nothing to tell. */
    private function section(Role $role, Carbon $since, string $locale): ?array
    {
        $views = (int) AnalyticsDaily::forRoles([$role->id])
            ->inDateRange($since, now())
            ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));

        // accountOnlyFollowers(): a confirmed email subscriber also gets a follower row, and is
        // counted once, below, as a subscriber.
        $followers = $role->accountOnlyFollowers()->wherePivot('created_at', '>=', $since)->count();
        $subscribers = $role->subscribers()->confirmed()->where('confirmed_at', '>=', $since)->count();

        // Sales on events this schedule created: the money is the creator's. One row per order -
        // individual-ticket group rows point at their primary through group_id.
        // Counted by ORDER: a multi-event checkout writes one primary row per event (sharing
        // order_id), and three events in one cart is one order, not three.
        $sales = fn () => DB::table('sales')
            ->join('events', 'events.id', '=', 'sales.event_id')
            ->where('events.creator_role_id', $role->id)
            ->where('sales.status', 'paid')
            ->where('sales.is_deleted', false)
            ->where(fn ($q) => $q->whereNull('sales.group_id')->orWhereColumn('sales.group_id', 'sales.id'))
            ->where('sales.created_at', '>=', $since);

        $orders = 'COUNT(DISTINCT COALESCE(sales.order_id, sales.id)) as n';
        $tickets = (int) $sales()->whereNotIn('sales.payment_method', ['rsvp', 'import'])->selectRaw($orders)->value('n');
        $rsvps = (int) $sales()->where('sales.payment_method', 'rsvp')->selectRaw($orders)->value('n');

        $upcoming = $this->upcoming($role, $locale);

        // A few stray views and nothing else is not worth an email; a digest that says "3 page
        // views" teaches people to ignore the next one.
        if ($views < self::MIN_VIEWS && $followers + $subscribers + $tickets + $rsvps === 0 && empty($upcoming)) {
            return null;
        }

        return [
            'name' => $role->name,
            'url' => app_url(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'], false)),
            'views' => $views,
            'followers' => $followers,
            'subscribers' => $subscribers,
            'tickets' => $tickets,
            'rsvps' => $rsvps,
            'upcoming' => $upcoming,
        ];
    }

    /**
     * The next seven days' dates on this schedule's page, recurring series expanded, in the
     * schedule's own timezone (Event::adminOccurrenceDates()).
     */
    private function upcoming(Role $role, string $locale): array
    {
        $events = $role->events()
            ->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_internal', false)
            ->where('events.is_cancelled', false)
            ->wherePivot('is_accepted', true)
            ->hasUpcomingOccurrence()
            // Ordered before the limit, or a venue with a season of dates synced ahead gets 50
            // arbitrary ones and can miss this week's. Series first (their starts_at is an anchor
            // in the past), then one-off dates soonest first.
            ->orderByRaw('events.days_of_week IS NULL')
            ->orderBy('events.starts_at')
            ->with('creatorRole')
            ->limit(50)
            ->get();

        $occurrences = [];

        foreach ($events as $event) {
            /** @var Event $event */
            $today = Carbon::parse($event->scheduleToday())->startOfDay();
            // Seven days including today.
            $last = $today->copy()->addDays(6);

            foreach ($event->adminOccurrenceDates(0, 7, 14) as $date) {
                $day = Carbon::parse($date);
                if ($day->betweenIncluded($today, $last)) {
                    // Date, then time of day: a series' starts_at is its first date, so the SQL
                    // order put every series ahead of a same-day one-off, and the cut below kept it.
                    $occurrences[] = ['sort' => $date.' '.$event->localTimeOfDay(), 'name' => $event->name, 'date' => $day->locale($locale)->translatedFormat('D j M')];
                }
            }
        }

        usort($occurrences, fn ($a, $b) => strcmp($a['sort'], $b['sort']));

        return array_map(
            fn ($o) => ['name' => $o['name'], 'date' => $o['date']],
            array_slice($occurrences, 0, self::UPCOMING_LIMIT)
        );
    }
}
