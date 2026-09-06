<?php

namespace App\Services;

use App\Models\Newsletter;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\TicketWaitlist;
use Illuminate\Support\Facades\Cache;

/**
 * How much work is WAITING, as opposed to whether the runner is alive.
 *
 * /admin/queue could already answer the second question in detail and could not answer the first
 * at all, because the two have almost nothing to do with each other here: the `jobs` table is
 * drained every minute and is normally empty, while the commands that carry the real backlog -
 * app:translate above all - do their work inline inside the scheduled run and never enqueue a
 * job. So the page showed a healthy queue and an alive scheduler while thousands of rows sat
 * untranslated, with the only count of them on /admin/usage, a page about AI spend.
 *
 * Deliberately NOT rows in AdminAlertService. That class's docblock rules out exactly this: its
 * header total is a plain sum of counts and its rows are things one admin can personally clear,
 * so an 8,000-row backlog would swamp a to-do list whose other entries are single digits and pin
 * a permanent nav badge on a queue that drains itself. A backlog is a stat, and stats belong on
 * the page that explains them.
 *
 * Every entry names the scheduled task that drains it, so the panel can print that task's real
 * cadence from the live schedule instead of a hardcoded string that would drift the moment
 * routes/console.php changed.
 */
class WorkBacklog
{
    public const CACHE_KEY = 'admin.work_backlog';

    /**
     * How long a measurement stands.
     *
     * These counts are unindexed scans (there is no index on any `_en` column, on
     * last_translated_at, or on most of the status columns below) and /admin/queue is a page an
     * operator reloads while watching something drain, unlike /admin/usage. Five minutes costs
     * nothing real: the fastest task here runs every minute and the slowest every hour, and the
     * headline figure - translations - moves only every fifteen. The view prints the measurement
     * age rather than passing a stale number off as live.
     */
    public const CACHE_SECONDS = 300;

    /**
     * One entry per countable backlog, in the order the panel renders them.
     *
     * `available` keeps a backlog off the page entirely when this install never runs the task
     * that drains it - a permanent "0 waiting" row for a feature the operator has switched off is
     * noise that trains them to stop reading the panel. It mirrors the gate on the schedule entry
     * itself, so the two cannot disagree about whether the work happens here.
     *
     * Adding a seventh backlog is one entry. The only rule is that `count` must be a query whose
     * answer FALLS as the task runs; a fixed enumeration ("how many calendars are configured")
     * never drains and belongs somewhere else.
     *
     * @return array<string, array{label: string, task: string, available: callable, count: callable}>
     */
    private static function definitions(): array
    {
        return [
            // ProcessScheduledNewsletters::__invoke() - the same two clauses it selects on.
            'newsletters' => [
                'label' => __('messages.backlog_newsletters'),
                'task' => 'process-scheduled-newsletters',
                'available' => fn () => true,
                'count' => fn () => Newsletter::where('status', 'scheduled')
                    ->where('scheduled_at', '<=', now())
                    ->count(),
            ],

            // FederationService::push() takes these two arms in this order; the second (recurring
            // rows being refreshed) is not a backlog - it never drains - so only the unsynced arm
            // is counted. federated_skipped_at means the nexus already refused the row and nothing
            // about it has changed, so the next run will not retry it either.
            'federation' => [
                'label' => __('messages.backlog_federation'),
                'task' => 'federation-push',
                'available' => fn () => app(FederationService::class)->isEnabled(),
                'count' => fn () => app(FederationService::class)->federatableQuery()
                    ->whereNull('federated_at')
                    ->whereNull('federated_skipped_at')
                    ->count(),
            ],

            // Schedules with at least one public event published since their own announcement
            // watermark. Counted as SCHEDULES rather than events because that is the unit the
            // command sends in: one digest per schedule, however many events it gathered.
            'announcements' => [
                'label' => __('messages.backlog_announcements'),
                'task' => 'send-event-announcements',
                // Mirrors SendEventAnnouncements::schedulesToAnnounce(): on selfhost a log or
                // array mailer means mail was never configured, and the command returns early.
                'available' => fn () => config('app.hosted')
                    || ! in_array(config('mail.default'), ['log', 'array'], true),
                'count' => fn () => Role::query()
                    ->where('is_deleted', false)
                    ->where('announce_new_events', true)
                    ->whereNotNull('user_id')
                    ->whereHas('subscribers', fn ($q) => $q->whereNotNull('confirmed_at'))
                    ->whereExists(fn ($q) => $q->selectRaw(1)
                        ->from('events')
                        ->whereColumn('events.creator_role_id', 'roles.id')
                        ->where('events.is_draft', false)
                        ->where('events.is_private', false)
                        ->whereNull('events.event_password')
                        // COALESCE for the same reason the command uses it: an event drafted
                        // before the watermark and published after it is exactly the case this
                        // is meant to catch.
                        ->whereRaw('COALESCE(events.published_at, events.created_at) > roles.last_announced_at')
                        ->whereRaw('(events.starts_at >= NOW() OR (events.duration >= 24 AND DATE_ADD(events.starts_at, INTERVAL events.duration HOUR) >= NOW()))'))
                    ->count(),
            ],

            // ChargeInstallments::chargeDue(). next_attempt_at is part of "due": without it a
            // declined card reads as due on every run for ever and the figure never falls.
            'installments' => [
                'label' => __('messages.backlog_installments'),
                'task' => 'charge-installments',
                'available' => fn () => true,
                'count' => fn () => SaleInstallment::query()
                    ->where('status', 'scheduled')
                    ->where('due_at', '<=', now())
                    ->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()))
                    ->whereHas('plan', fn ($q) => $q->whereIn('status', ['active', 'delinquent']))
                    ->count(),
            ],

            // ReleaseTickets' per-event expiry arm. The order-primary clause matters: a leg of a
            // multi-event order never expires on its own, so counting legs would overstate the
            // backlog by the size of the average order.
            'release_tickets' => [
                'label' => __('messages.backlog_release_tickets'),
                'task' => 'app-release-tickets',
                'available' => fn () => true,
                'count' => fn () => Sale::where('status', 'unpaid')
                    ->where(fn ($q) => $q->whereNull('group_id')->orWhereColumn('group_id', 'id'))
                    ->whereHas('event', fn ($q) => $q->where('events.expire_unpaid_tickets', '>', 0)
                        ->whereRaw('TIMESTAMPDIFF(HOUR, sales.created_at, NOW()) >= events.expire_unpaid_tickets'))
                    ->count(),
            ],

            // ExpireWaitlistNotifications::handle().
            'waitlist' => [
                'label' => __('messages.backlog_waitlist'),
                'task' => 'app-expire-waitlist',
                'available' => fn () => true,
                'count' => fn () => TicketWaitlist::where('status', 'notified')
                    ->where('expires_at', '<', now())
                    ->count(),
            ],
        ];
    }

    /**
     * Every backlog this install can have, measured. Cached as one payload.
     *
     * @return array{measured_at: int, translation: array<string, mixed>, entries: array<int, array<string, mixed>>}
     */
    public static function snapshot(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, fn () => [
            'measured_at' => now()->timestamp,
            'translation' => TranslationQueue::backlog(),
            'entries' => collect(self::definitions())
                ->filter(fn ($definition) => $definition['available']())
                ->map(fn ($definition, $key) => self::measure($definition, $key))
                ->filter()
                ->values()
                ->all(),
        ]);
    }

    /**
     * One backlog, counted, or null if it could not be.
     *
     * A backlog that throws costs its own row and nothing else. /admin/queue is the page an
     * operator opens when something is ALREADY broken - including an install that has pulled the
     * code and not run migrate, which is the exact case SchedulerHealth guards every read of
     * scheduled_task_runs against. Letting a missing column here take down the scheduler card
     * above would break the page precisely when it is the page that would explain the problem.
     */
    private static function measure(array $definition, string $key): ?array
    {
        try {
            return [
                'key' => $key,
                'label' => $definition['label'],
                'task' => $definition['task'],
                'count' => (int) $definition['count'](),
            ];
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Drop the memoized snapshot. For tests, and for an operator who has just drained something. */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * What the last unattended app:translate run got through, or null if none is on record.
     *
     * Null is the honest answer for an install whose cron has never run, one that has been quiet
     * for over a week, and one whose cache was just cleared. The panel says "not measured yet"
     * for all three rather than inventing a rate.
     *
     * @return object{at: \Illuminate\Support\Carbon, seconds: float, translated: int, failed: int, parked: int, budgetReached: bool, perHour: float}|null
     */
    public static function translationRate(): ?object
    {
        $summary = Cache::get(\App\Console\Commands\Translate::LAST_RUN_CACHE_KEY);

        if (! is_array($summary) || ! isset($summary['at'])) {
            return null;
        }

        $translated = (int) ($summary['translated'] ?? 0);

        // Runs per hour from the LIVE schedule rather than a constant, so changing the cadence in
        // routes/console.php changes this figure too. Falls back to the shipped 15 minutes if the
        // task cannot be found, which is the http-rail case (that rail runs it on the same
        // fifteen-minute tier).
        $task = SchedulerHealth::tasks()->firstWhere('name', 'app-translate');
        $interval = $task?->interval ?? 900;
        $perHour = $translated * (3600 / max(60, $interval));

        return (object) [
            // Whether the thing that produced this rate is still doing so. A rate is a record of
            // the past and stays true; an ETA is a claim about the future and is only as good as
            // the task behind it. 'not_yet_run' and 'unknown' are not health - the first has no
            // history and the second is what every task reads while the scheduler is stalled.
            'taskHealthy' => in_array($task?->state, ['ok', 'running'], true),
            'at' => \Illuminate\Support\Carbon::createFromTimestamp((int) $summary['at'])
                ->setTimezone(config('app.timezone')),
            'seconds' => (float) ($summary['seconds'] ?? 0),
            'translated' => $translated,
            'failed' => (int) ($summary['failed'] ?? 0),
            'parked' => (int) ($summary['parked'] ?? 0),
            'budgetReached' => (bool) ($summary['budget_reached'] ?? false),
            'perHour' => $perHour,
        ];
    }

    /**
     * Hours to clear `$pending` rows at the measured rate, or null when that cannot be said.
     *
     * Null rather than infinity when the last run translated nothing: that is the normal state of
     * a drained queue, and it is also what a wholly failing run looks like. Neither is an ETA.
     */
    public static function hoursToClear(int $pending, ?object $rate): ?float
    {
        // Also null when the draining task is not currently healthy. An estimate derived from a
        // rate that has stopped happening is the most misleading number this panel could print:
        // it reads as reassurance at the exact moment the operator needs alarm, and the scheduler
        // card directly above is already saying the task is failed or overdue.
        if ($pending <= 0 || $rate === null || $rate->perHour <= 0 || ! $rate->taskHealthy) {
            return null;
        }

        return $pending / $rate->perHour;
    }

    /**
     * Why there is no measured rate, when there is none.
     *
     * 'unshared_cache' is not a nicety. The summary is written by whichever container ran the
     * command and read by the one serving this page, so on CACHE_STORE=file with a separate
     * worker - the hosted default until it is set, per docs/DIGITALOCEAN_WORKER.md - the web
     * container reads nothing however healthy the cron is. Reporting that as "never measured"
     * would send an operator hunting a working cron, which is the same failure the scheduler
     * card's own cache-store row exists to prevent.
     */
    public static function rateUnavailableReason(): ?string
    {
        if (self::translationRate() !== null) {
            return null;
        }

        // An unshared store is a CANDIDATE cause, never a conclusion. Most installs are a single
        // container on the `file` driver, where the cron and this page share one cache and the
        // summary is perfectly readable - there simply has not been a run yet. Blaming the cache
        // there would send the operator to fix a setting that is doing them no harm.
        //
        // So the same standard SchedulerHealth::cacheIsHidingAHealthyScheduler() holds itself to:
        // say it only with positive evidence that the command IS running and this page cannot see
        // it. scheduled_task_runs is that evidence, because it is a table - a medium both
        // containers share by definition - so a completed app-translate row beside a missing cache
        // summary is the contradiction that can only be explained by a per-container cache.
        if (SchedulerHealth::cacheStoreIsShared()) {
            return 'not_measured';
        }

        $task = SchedulerHealth::tasks()->firstWhere('name', 'app-translate');

        return $task?->row?->last_finished_at !== null ? 'unshared_cache' : 'not_measured';
    }

    /** Confirmed translation work across all four passes. */
    public static function translationPending(array $translation): int
    {
        return (int) collect($translation)->sum('pending');
    }

    /** Rows the roles pass must still open to be sure, which cost no AI call. */
    public static function translationRecheck(array $translation): int
    {
        return (int) collect($translation)->sum('recheck');
    }
}
