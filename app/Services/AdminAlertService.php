<?php

namespace App\Services;

use App\Models\BoostCampaign;
use App\Models\FederatedInstance;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SupportMessage;
use App\Models\TranslationOverride;
use App\Models\TranslationSuggestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Aggregates every queue in /admin that is waiting on a site admin into a single
 * sorted list of to-do rows, so the admin dashboard can show them in one panel and
 * the admin nav can badge the sections they live under.
 *
 * Rows are shaped like HomeController::getPendingActionItems() output (type, count,
 * title, subtitle, url, color) so both dashboards share the needs-attention
 * component, plus two keys only the nav reads: 'nav' (which dropdown) and 'tab'
 * (which item inside it).
 *
 * Each count deliberately reuses the query the destination page already runs, so a
 * badge and the page it links to can never disagree.
 */
class AdminAlertService
{
    /**
     * Ordered alert definitions. Position in this array is the sort priority:
     * breakage and held-up money first, then the review queues.
     *
     * Every row here must be something a site admin can actually act on. A count that
     * is really waiting on someone else - a schedule owner who never verified, a
     * referred user who never converted - is a metric, not a queue: it never drains,
     * it inflates the panel's header total (which is a plain sum of counts) and it
     * pins a permanent nav badge, which is exactly the signal SEVERITY exists to
     * protect. Those belong on their own admin page as a stat, not in here.
     */
    private const DEFINITIONS = [
        // First, because nothing else in this list is more broken. Every other row is a queue
        // someone has to work through; this one means the thing that drains those queues - and
        // sends every reminder, charges every installment and syncs every calendar - is not
        // running at all.
        'scheduler_stalled',
        // Above jobs_failed: a queue that has stopped draining is a worse condition than one job
        // that broke, and it is the failure the /admin/queue banner already warns about without
        // ever reaching the dashboard panel or the nav badge.
        'jobs_stalled',
        'jobs_failed',
        'domains_failed',
        'boosts_stuck',
        'boosts_failed',
        'sales_mismatch',
        'boosts_mismatch',
        'promos_pending',
        'federation_flagged',
        'federation',
        'translation_suggestions',
        'support_unread',
        'boosts_disapproved',
        'domains_pending',
        'translations_unshared',
        // Last: informational, and unlike every other row above it does not drain by the
        // operator clearing a queue, only by them choosing to update.
        'app_update_available',
    ];

    /**
     * How far back a boost failure or ad rejection still counts as actionable.
     * Nothing ever transitions a campaign out of 'failed', and Meta's DISAPPROVED
     * is never cleared, so an unbounded count would pin a permanent badge.
     * Public because AdminController::boost() windows its lists to match.
     */
    public const BOOST_ALERT_DAYS = 30;

    /**
     * How long a DUE job may sit before the queue counts as stalled. Matches the threshold the
     * /admin/queue banner renders, so the badge and the page it links to cannot disagree.
     */
    public const JOBS_STALLED_MINUTES = 60;

    /**
     * Nav badge colour precedence. A group's badge takes the highest severity it
     * contains, so a red badge keeps meaning "something is broken" even when
     * informational rows are counted alongside it.
     */
    private const SEVERITY = ['red' => 3, 'amber' => 2, 'blue' => 1];

    /**
     * Memoized for the request: the nav composer and the dashboard controller both
     * ask for this, and it is a dozen COUNT queries.
     */
    private static ?Collection $cache = null;

    /**
     * Both `roles` counts resolved in one pass. `custom_domain_status` is unindexed
     * and the nav composer runs on every admin page, so two separate COUNTs meant two
     * full scans of the tenant table per render.
     */
    private static ?object $roleCounts = null;

    private static ?bool $schedulerStalled = null;

    /**
     * Every pending admin action, highest priority first. Rows with a zero count are
     * omitted, so an empty collection means there is nothing to do.
     */
    public static function items(): Collection
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $isNexus = config('app.is_nexus');
        $isHosted = config('app.hosted');

        $counts = [
            // Both cron rails write scheduler.last_run_at once a tick, even on the minutes when
            // nothing was due, so a missing or stale key means the scheduler itself stopped.
            //
            // Suppressed under APP_TESTING because the key is never written in a test run and an
            // always-on row would break every assertion that the panel is empty. The alert's own
            // coverage lives in tests/Feature/SchedulerHealthTest.php, which overrides the flag.
            //
            // Reads the shared cache store. On a multi-container install with CACHE_STORE=file
            // this would fire permanently - which is the correct signal that the deployment is
            // misconfigured, since every scheduler mutex is equally per-container there.
            'scheduler_stalled' => function () {
                if (config('app.is_testing')) {
                    return 0;
                }

                return self::schedulerIsStalled() ? 1 : 0;
            },

            // Jobs that are DUE and still waiting. available_at, not created_at: a delayed
            // dispatch (ReconcileBoostCampaign is +24h) is written with created_at = now, so
            // keying on created_at would pin a permanent badge on a perfectly healthy queue -
            // exactly the "never drains" shape this class's docblock forbids.
            //
            // Suppressed while the scheduler is stalled: nothing drains the queue then, so a
            // backlog is a symptom of the row above, and two red rows for one cause drains the
            // signal.
            'jobs_stalled' => function () {
                if (config('app.is_testing') || self::schedulerIsStalled()) {
                    return 0;
                }

                // exists(), not count(). Two reasons, and both matter. jobs is indexed on queue
                // only, so a COUNT over available_at is a full table scan - on every admin page
                // render, and worst exactly when there IS a backlog. And this class's own docblock
                // forbids a row whose count inflates the panel's header total: a backlog of 8,000
                // jobs would add 8,000 to a to-do list whose other rows are single digits. It is
                // one condition, so it counts as one.
                return DB::table('jobs')
                    ->where('available_at', '<=', now()->subMinutes(self::JOBS_STALLED_MINUTES)->timestamp)
                    ->exists() ? 1 : 0;
            },

            'jobs_failed' => fn () => DB::table('failed_jobs')->count(),

            'domains_failed' => fn () => $isHosted ? (int) self::roleCounts()->domains_failed : 0,

            // "pending_payment" is normal for a few minutes; past that the Stripe
            // callback never arrived and someone has to look. Mirrors AdminController::boost().
            'boosts_stuck' => fn () => BoostCampaign::where('status', 'pending_payment')
                ->where('created_at', '<', now()->subMinutes(30))
                ->count(),

            'boosts_failed' => fn () => BoostCampaign::where('status', 'failed')
                ->where('created_at', '>=', now()->subDays(self::BOOST_ALERT_DAYS))
                ->count(),

            'sales_mismatch' => fn () => Sale::where('status', 'amount_mismatch')->count(),

            'boosts_mismatch' => fn () => BoostCampaign::where('billing_status', 'amount_mismatch')->count(),

            // Approve-before-serve: a paid campaign sitting here is an advertiser who has
            // been charged and is waiting, so it is queued as actionable rather than
            // informational. Gated on the operator switch so an instance that never enabled
            // the network cannot accrue a queue it has no screen for.
            'promos_pending' => fn () => \App\Services\PromotionService::isEnabled()
                ? BoostCampaign::awaitingReview()->count()
                : 0,

            // Nexus-only: AdminFederationController and the suggestion endpoints abort
            // at runtime on the wrong install type, so counting there would badge a 404.
            //
            // An instance whose site_url stops matching is flagged. ApiFederationController
            // downgrades it to pending only when the change arrives on the register
            // endpoint; the sync path flags an approved instance and leaves it approved,
            // where the queue's default status=pending filter hides it. Scoped to
            // approved on purpose: a flagged *suspended* instance is already dealt with,
            // and surfacing it would undo "no escape from moderation".
            'federation_flagged' => fn () => $isNexus
                ? FederatedInstance::whereNotNull('flagged_at')
                    ->where('status', FederatedInstance::STATUS_APPROVED)
                    ->count()
                : 0,

            'federation' => fn () => $isNexus ? FederatedInstance::pending()->count() : 0,

            'translation_suggestions' => fn () => $isNexus ? TranslationSuggestion::pending()->count() : 0,

            'support_unread' => fn () => $isHosted
                ? SupportMessage::where('is_from_admin', false)->whereNull('read_at')->count()
                : 0,

            // Meta's verdict lands in boost_ads.meta_status; boost_ads.status is a
            // separate lowercase app enum that is never set to DISAPPROVED. Matching on
            // `status` here would be a permanently dead alert.
            'boosts_disapproved' => fn () => BoostCampaign::where('created_at', '>=', now()->subDays(self::BOOST_ALERT_DAYS))
                ->whereHas('ads', function ($query) {
                    $query->where('meta_status', 'DISAPPROVED');
                })->count(),

            'domains_pending' => fn () => $isHosted ? (int) self::roleCounts()->domains_pending : 0,

            // The mirror image of translation_suggestions: on a selfhosted install
            // these are local fixes that have not been offered back to the nexus yet.
            'translations_unshared' => fn () => $isNexus ? 0 : TranslationOverride::unshared()->count(),

            // A pure cache read. This composer runs on EVERY admin page render, and
            // getVersionAvailable() is an outbound call to GitHub - which allows 60 an hour
            // unauthenticated - so a cold cache must read "no update", not block the nav on a
            // network round trip. The daily app:check-version command is what keeps it warm.
            'app_update_available' => fn () => (! $isNexus && app(AppUpdateService::class)->isUpdateAvailable()) ? 1 : 0,
        ];

        $items = collect();

        foreach (self::DEFINITIONS as $type) {
            $count = (int) $counts[$type]();

            if ($count > 0 && $row = self::row($type, $count)) {
                $items->push($row);
            }
        }

        return self::$cache = $items;
    }

    /**
     * Badge data for the admin nav, keyed by dropdown and by item:
     * ['nav' => [dropdown => ['count' => int, 'color' => string]], 'tab' => [item => ...]].
     *
     * Each group carries the highest severity it contains. Without this a slow-moving
     * blue review queue would paint the nav the same red as a failed job and drain the
     * signal from both.
     */
    public static function badges(): array
    {
        $items = self::items();

        $group = fn (string $key) => $items->groupBy($key)->map(fn ($rows) => [
            'count' => $rows->sum('count'),
            'color' => $rows->sortByDesc(fn ($row) => self::SEVERITY[$row['color']] ?? 0)->first()['color'],
        ])->all();

        return [
            'nav' => $group('nav'),
            'tab' => $group('tab'),
        ];
    }

    /**
     * Drop the memoized counts. Only needed in tests, which seed rows after a page
     * render has already primed the cache.
     */
    public static function flush(): void
    {
        self::$cache = null;
        self::$roleCounts = null;
        self::$schedulerStalled = null;
    }

    /**
     * Both `roles` tallies in one scan, memoized for the request.
     *
     * The predicates mirror AdminController::domains() exactly - base
     * `whereNotNull('custom_domain')` plus the status filter, and deliberately NO
     * custom_domain_mode filter, because the list the alert links to has none either.
     * (Filtering on mode here would undercount: RoleController's provisioning catch
     * block sets custom_domain_status = 'failed' even while switching a role away
     * from direct mode.)
     */
    private static function roleCounts(): object
    {
        if (self::$roleCounts !== null) {
            return self::$roleCounts;
        }

        return self::$roleCounts = Role::selectRaw(
            "SUM(custom_domain IS NOT NULL AND custom_domain_status = 'failed') AS domains_failed,
             SUM(custom_domain IS NOT NULL AND custom_domain_status = 'pending') AS domains_pending"
        )->first();
    }

    /**
     * Shared by scheduler_stalled and jobs_stalled, memoized so the two rows read the same clock:
     * called twice, now() advances between them and the cascade could otherwise disagree with
     * itself within a single render.
     */
    private static function schedulerIsStalled(): bool
    {
        return self::$schedulerStalled ??= SchedulerHealth::isStalled();
    }

    /**
     * Build one to-do row, or null when the destination route is not registered on
     * this install. Titles are pluralized; subtitles name the destination section so
     * a row reads "3 instances awaiting approval / Federation".
     */
    private static function row(string $type, int $count): ?array
    {
        [$nav, $tab, $name, $params, $fragment, $color, $subtitle] = match ($type) {
            'scheduler_stalled' => ['system', 'queue', 'admin.queue', [], '', 'red', __('messages.queue')],
            'jobs_stalled' => ['system', 'queue', 'admin.queue', [], '', 'red', __('messages.queue')],
            'jobs_failed' => ['system', 'queue', 'admin.queue', [], '', 'red', __('messages.queue')],
            'domains_failed' => ['manage', 'domains', 'admin.domains', ['status' => 'failed'], '', 'red', __('messages.domains')],
            'boosts_stuck' => ['manage', 'boost', 'admin.boost', [], '#boost-alerts', 'red', 'Boost'],
            'boosts_failed' => ['manage', 'boost', 'admin.boost', [], '#boost-alerts', 'red', 'Boost'],
            'sales_mismatch' => ['insights', 'revenue', 'admin.revenue', [], '#amount-mismatch', 'red', __('messages.revenue')],
            'boosts_mismatch' => ['insights', 'revenue', 'admin.revenue', [], '#amount-mismatch', 'red', __('messages.revenue')],
            // Points at the existing Boost screen rather than adding a nav item, so there is
            // no new Route::has failure mode and the badge lands where the operator already
            // manages campaigns.
            'promos_pending' => ['manage', 'boost', 'admin.boost', [], '#promo-queue', 'amber', 'Boost'],
            'federation_flagged' => ['system', 'federation', 'admin.federation', ['status' => 'approved'], '', 'red', __('messages.federation')],
            'federation' => ['system', 'federation', 'admin.federation', [], '', 'amber', __('messages.federation')],
            'translation_suggestions' => ['system', 'translations', 'admin.translations.suggestions', [], '', 'blue', __('messages.translations')],
            'support_unread' => ['system', 'support', 'admin.support', [], '', 'blue', 'Support'],
            'boosts_disapproved' => ['manage', 'boost', 'admin.boost', [], '#boost-alerts', 'amber', 'Boost'],
            'domains_pending' => ['manage', 'domains', 'admin.domains', ['status' => 'pending'], '', 'amber', __('messages.domains')],
            'translations_unshared' => ['system', 'translations', 'admin.translations', [], '', 'blue', __('messages.translations')],
            // Blue, not red or amber: SEVERITY above reserves red for breakage, and an operator
            // who has decided not to update yet would otherwise carry a permanent alarm.
            'app_update_available' => ['system', 'app-update', 'admin.app_update', [], '', 'blue', __('messages.app_update')],
        };

        // Several admin routes are registered only on hosted installs. The counts are
        // gated to match, but never let a mismatch 500 the dashboard: no route, no row.
        if (! Route::has($name)) {
            return null;
        }

        return [
            'type' => $type,
            'count' => $count,
            'title' => trans_choice("messages.admin_alert_{$type}", $count, ['count' => $count]),
            'subtitle' => $subtitle,
            'url' => route($name, $params).$fragment,
            'color' => $color,
            'nav' => $nav,
            'tab' => $tab,
        ];
    }
}
