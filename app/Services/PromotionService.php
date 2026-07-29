<?php

namespace App\Services;

use App\Models\AnalyticsPromotionsDaily;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\PageView;
use App\Models\PromotionLocationsDaily;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Chooses which paid promotion (if any) to show on a given free-tier page, and records the
 * impression.
 *
 * The selection has to run on every eligible guest page render, so it never queries per
 * request: one short-lived global snapshot of servable campaigns is cached, and the
 * per-request work is a loop over a handful of small arrays. Per-(host, country, category)
 * cache keys were rejected deliberately - the cache driver is `file` on selfhost, where that
 * would mean unbounded small-file creation with a near-zero hit rate.
 */
class PromotionService
{
    private const CACHE_KEY = 'promo:candidates';

    /** Hard ceiling on the snapshot, so a runaway campaign count cannot blow up memory. */
    private const MAX_CANDIDATES = 500;

    /**
     * Whether this install can run the promotions network at all.
     *
     * The hosted and nexus checks are not redundant with the operator toggle - they mirror
     * Role::showAds(), which is what decides whether any page can carry a promotion:
     *
     *   - off-hosted, actualPlanTier() returns 'enterprise', so there is no free tier and
     *     therefore no inventory;
     *   - on the nexus, showAds() returns false outright because eventschedule.com stays ad-free.
     *
     * Selling has to be gated on exactly the same conditions as serving. Without the nexus check
     * a Pro schedule there would see a healthy inventory estimate, prepay, go active, and then
     * never serve a single impression - and with no scheduled_end, never complete and never be
     * refunded either.
     */
    public static function isEnabled(): bool
    {
        return config('app.hosted')
            && ! config('app.is_nexus')
            && AdsService::nativeConfigured();
    }

    /**
     * Pick a promotion for this page, or null.
     *
     * Records the impression as a side effect when one is chosen, because the caller is a
     * Blade partial and counting must not happen in a template.
     *
     * @return array{campaign_id:int, headline:string, body:?string, image_url:?string,
     *               advertiser:string, click_url:string}|null
     */
    public function pick(Role $hostRole, ?Event $event, Request $request): ?array
    {
        if (! self::isEnabled() || $hostRole->promotions_opt_out) {
            return null;
        }

        $candidates = $this->candidates();

        if (empty($candidates)) {
            return null;
        }

        $ip = PageView::clientIp($request);

        // Without a resolvable address there is no key to cap spend on, and every impression
        // below is billed to a real advertiser. recordClick() bails here for the same reason.
        // Previously this fell through with an empty $seen, which meant NO cap at all.
        if (! $ip) {
            return null;
        }

        $ipHash = PageView::ipHash($ip);
        $visitorHash = PageView::visitorHash($ip, $request->userAgent());
        $country = app(GeoIpService::class)->lookup($ip);
        $seen = $visitorHash ? $this->seenToday($visitorHash) : [];
        $frequencyCap = (int) config('ads.native_frequency_cap', 3);

        $eligible = [];

        foreach ($candidates as $candidate) {
            // Never let a schedule advertise to itself, and never promote the very event
            // the visitor is already looking at.
            if ($candidate['role_id'] === $hostRole->id || $candidate['owner_id'] === $hostRole->user_id) {
                continue;
            }

            if ($event && $candidate['event_id'] === $event->id) {
                continue;
            }

            if (! $this->matchesTargeting($candidate, $hostRole, $country)) {
                continue;
            }

            if (($seen[$candidate['id']] ?? 0) >= $frequencyCap) {
                continue;
            }

            $weight = $this->weightFor($candidate);

            if ($weight > 0) {
                $eligible[] = ['candidate' => $candidate, 'weight' => $weight];
            }
        }

        if (empty($eligible)) {
            return null;
        }

        $chosen = $this->weightedPick($eligible);
        $campaign = BoostCampaign::find($chosen['id']);

        if (! $campaign || ! $campaign->isActive()) {
            return null;
        }

        // A member previewing their own page must never be billed or counted.
        if (AdsService::isPreview($request, $hostRole)) {
            return $this->creativeFor($chosen, $hostRole);
        }

        // Spend cap, keyed on the IP ALONE and checked before anything is billed.
        //
        // The frequency cap above is keyed on visitorHash, which mixes in the client-chosen
        // User-Agent. That is the right key for "do not show one browser the same ad four
        // times", but it is useless as a spend guard: rotating the User-Agent mints a fresh
        // bucket, and unlike /promo/{hash} these guest pages carry no route throttle. Since
        // every impression below is billed, the money needs a key the visitor cannot change.
        // Deliberately far above the per-browser frequency cap so that a shared address
        // (corporate NAT, a venue's own wifi) does not lose an advertiser real delivery.
        $ipCap = (int) config('ads.native_ip_impression_cap', 100);

        if ($ipCap > 0 && PageView::incrementDailyCounter("promo_imp_ip:{$campaign->id}:{$ipHash}") > $ipCap) {
            return null;
        }

        // Charge before counting. A failed count then means a charge with no impression
        // (caught by the spend-drift check), which is strictly better than the reverse:
        // an impression served and never paid for.
        if (! app(PromotionBillingService::class)->chargeImpression($campaign)) {
            $this->forgetCandidates();

            return null;
        }

        $isNewVisitor = $visitorHash
            ? PageView::incrementDailyCounter("promo_imp:{$visitorHash}:{$campaign->id}") === 1
            : false;

        AnalyticsPromotionsDaily::recordImpression($campaign->id, $hostRole->id, $isNewVisitor);
        PromotionLocationsDaily::record($campaign->id, $country, 'impression');

        if ($visitorHash) {
            $this->rememberSeen($visitorHash, $campaign->id);
        }

        return $this->creativeFor($chosen, $hostRole);
    }

    /**
     * Every campaign that could serve right now, cached briefly and shared by all requests.
     *
     * The event-visibility join is load-bearing. An advertiser can set the promoted event to
     * draft, make it unlisted, cancel it or simply let its date pass while the campaign runs
     * on - none of which touch the campaign row. Without these predicates the promotion
     * would keep serving, and keep charging, while pointing at a page visitors cannot see.
     */
    public function candidates(): array
    {
        $ttl = (int) config('ads.candidate_cache_ttl', 300);

        return Cache::remember(self::CACHE_KEY, $ttl, function () {
            $rows = DB::table('boost_campaigns as c')
                ->join('events as e', 'e.id', '=', 'c.event_id')
                ->join('roles as r', 'r.id', '=', 'c.role_id')
                ->leftJoin('boost_ads as a', 'a.boost_campaign_id', '=', 'c.id')
                ->where('c.channel', 'network')
                ->where('c.status', 'active')
                ->where('c.moderation_status', 'approved')
                ->whereIn('c.billing_status', ['charged', 'free'])
                // exhausted_at as well as the arithmetic. debit() sets it when the campaign can
                // no longer afford another unit, which for a budget that is not a whole multiple
                // of the unit cost happens while spent_micros is still short of budget_micros.
                // Without this the campaign stayed in the pool, won rolls it could not pay for,
                // and each failure flushed the shared snapshot for the whole install.
                ->whereNull('c.exhausted_at')
                ->whereColumn('c.spent_micros', '<', 'c.budget_micros')
                ->where(fn ($q) => $q->whereNull('c.scheduled_start')->orWhere('c.scheduled_start', '<=', now()))
                ->where(fn ($q) => $q->whereNull('c.scheduled_end')->orWhere('c.scheduled_end', '>=', now()))
                // The promoted event must still be publicly visible. None of these touch the
                // campaign row, so without them a promotion keeps serving - and charging -
                // for an event the advertiser has since hidden, cancelled or let expire.
                ->where('e.is_draft', false)
                ->where('e.is_private', false)
                ->where(fn ($q) => $q->whereNull('e.is_cancelled')->orWhere('e.is_cancelled', false))
                // ...and must not be over. A recurring event has no single date and is
                // always current; a one-off is done once its end time has passed
                // (mirroring Event::scopeFullyPast).
                ->where(function ($q) {
                    $q->whereNotNull('e.days_of_week')
                        ->orWhere('e.starts_at', '>=', now())
                        ->orWhereRaw('DATE_ADD(e.starts_at, INTERVAL COALESCE(e.duration, 0) HOUR) >= ?', [now()]);
                })
                // is_accepted on the pivot is the universal visibility gate in this app.
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('event_role as er')
                        ->whereColumn('er.event_id', 'e.id')
                        ->whereColumn('er.role_id', 'c.role_id')
                        ->where('er.is_accepted', true);
                })
                ->groupBy('c.id')
                ->select(
                    'c.id', 'c.role_id', 'c.event_id', 'c.network_targeting', 'c.pricing_model',
                    'c.unit_rate_micros', 'c.budget_micros', 'c.spent_micros', 'c.scheduled_end',
                    'r.user_id as owner_id', 'r.name as advertiser',
                    DB::raw('MAX(a.headline) as headline'),
                    DB::raw('MAX(a.primary_text) as primary_text'),
                    DB::raw('MAX(a.image_url) as image_url'),
                    DB::raw('MAX(e.name) as event_name'),
                    DB::raw('MAX(e.flyer_image_url) as flyer_image_url'),
                )
                ->limit(self::MAX_CANDIDATES)
                ->get();

            if ($rows->count() >= self::MAX_CANDIDATES) {
                // Not silently truncating: an operator with this many live campaigns needs
                // to know the pool is being capped.
                Log::warning('Promotion candidate pool hit its cap; some campaigns are not being served.', [
                    'cap' => self::MAX_CANDIDATES,
                ]);
            }

            $deliveredToday = $this->deliveredToday($rows->pluck('id')->all());

            return $rows->map(fn ($row) => [
                'id' => (int) $row->id,
                'role_id' => (int) $row->role_id,
                'owner_id' => (int) $row->owner_id,
                'event_id' => (int) $row->event_id,
                'targeting' => $row->network_targeting ? json_decode($row->network_targeting, true) : [],
                'pricing_model' => $row->pricing_model,
                'unit_rate_micros' => (int) $row->unit_rate_micros,
                'budget_micros' => (int) $row->budget_micros,
                'spent_micros' => (int) $row->spent_micros,
                'scheduled_end' => $row->scheduled_end,
                'advertiser' => $row->advertiser,
                // Fall back to the event's own name and flyer when the advertiser did not
                // supply creative, so a campaign is never unservable for want of a headline.
                //
                // boost_ads.image_url is already absolute (PromotionController stores it via
                // the model accessor), but events.flyer_image_url is a bare filename in the
                // database - reading it out of a raw query bypasses the accessor and renders
                // a broken <img>. Run it back through the accessor rather than rebuilding the
                // URL here, which would be a second copy of the demo / Spaces / local logic.
                'headline' => $row->headline ?: $row->event_name,
                'body' => $row->primary_text,
                'image_url' => $row->image_url ?: self::flyerUrl($row->flyer_image_url),
                'delivered_today' => $deliveredToday[(int) $row->id] ?? 0,
            ])->all();
        });
    }

    /**
     * Resolve a raw events.flyer_image_url column value to a URL a browser can load.
     *
     * setRawAttributes() rather than a constructor or fill(): the column is not mass
     * assignable, and this needs the accessor to run on read without touching the database.
     */
    protected static function flyerUrl(?string $rawValue): string
    {
        if (! $rawValue) {
            return '';
        }

        $event = new Event;
        $event->setRawAttributes(['flyer_image_url' => $rawValue]);

        return (string) $event->flyer_image_url;
    }

    public function forgetCandidates(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Impressions delivered today per campaign, for pacing.
     *
     * @param  array<int>  $campaignIds
     * @return array<int,int>
     */
    protected function deliveredToday(array $campaignIds): array
    {
        if (empty($campaignIds)) {
            return [];
        }

        // Never alias an aggregate to a column name that exists on the grouped table -
        // MySQL binds the alias to the column and errors 1055.
        return AnalyticsPromotionsDaily::query()
            ->whereIn('boost_campaign_id', $campaignIds)
            ->where('date', now()->toDateString())
            ->groupBy('boost_campaign_id')
            ->pluck(DB::raw('SUM(impressions) as impression_count'), 'boost_campaign_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * Targeting is all-optional; an unset dimension matches everything.
     *
     * The two dimensions here are exactly the two the purchase form can set. Earlier drafts
     * also matched on host_countries, host_states and categories, but nothing ever wrote those
     * keys - they were branches that could not fire, and they made this read as though the
     * product supported five targeting axes. Add the control first, then the branch.
     *
     * When the visitor's country cannot be resolved - which is the norm on selfhost, where
     * the GeoIP database is not shipped - a country-targeted campaign is EXCLUDED. The
     * opposite default would deliver worldwide impressions to an advertiser who paid for
     * one country.
     */
    protected function matchesTargeting(array $candidate, Role $hostRole, ?string $country): bool
    {
        $targeting = $candidate['targeting'] ?? [];

        if (! empty($targeting['visitor_countries'])) {
            if (! $country || ! in_array(strtoupper($country), array_map('strtoupper', $targeting['visitor_countries']), true)) {
                return false;
            }
        }

        if (! empty($targeting['schedule_types'])) {
            if (! in_array($hostRole->type, $targeting['schedule_types'], true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Weight by remaining budget, softened when a campaign is ahead of its daily pace.
     *
     * Throttling to a tenth rather than zero matters: with a single live campaign, zeroing
     * would drop the slot through to AdSense for the rest of the day even though the
     * advertiser still has budget.
     */
    protected function weightFor(array $candidate): float
    {
        $remaining = max(0, $candidate['budget_micros'] - $candidate['spent_micros']);

        if ($remaining <= 0) {
            return 0;
        }

        $costPerImpression = $candidate['pricing_model'] === 'cpm'
            ? max(1, intdiv($candidate['unit_rate_micros'], 1000))
            : max(1, $candidate['unit_rate_micros']);

        $daysLeft = 1;
        if ($candidate['scheduled_end']) {
            $daysLeft = max(1, now()->diffInDays(\Illuminate\Support\Carbon::parse($candidate['scheduled_end']), false) ?: 1);
        }

        $paceTarget = ($remaining / $costPerImpression) / $daysLeft;

        return $candidate['delivered_today'] >= $paceTarget
            ? $remaining * 0.1
            : $remaining;
    }

    /**
     * @param  array<array{candidate: array, weight: float}>  $eligible
     */
    protected function weightedPick(array $eligible): array
    {
        $total = array_sum(array_column($eligible, 'weight'));

        if ($total <= 0) {
            return $eligible[0]['candidate'];
        }

        $roll = mt_rand(0, (int) round($total * 1000)) / 1000;
        $running = 0.0;

        foreach ($eligible as $entry) {
            $running += $entry['weight'];
            if ($roll <= $running) {
                return $entry['candidate'];
            }
        }

        return $eligible[array_key_last($eligible)]['candidate'];
    }

    /**
     * @return array<int,int> campaign id => impressions shown to this visitor today
     */
    protected function seenToday(string $visitorHash): array
    {
        return Cache::get("promo_seen:{$visitorHash}", []);
    }

    protected function rememberSeen(string $visitorHash, int $campaignId): void
    {
        $key = "promo_seen:{$visitorHash}";
        $seen = Cache::get($key, []);
        $seen[$campaignId] = ($seen[$campaignId] ?? 0) + 1;

        Cache::put($key, $seen, PageView::secondsUntilEndOfDay());
    }

    /**
     * Build the renderable creative, including the click-through URL.
     *
     * The click URL is generated per request rather than cached because it embeds the host
     * schedule, which is how placement attribution works.
     */
    protected function creativeFor(array $candidate, Role $hostRole): array
    {
        return [
            'campaign_id' => $candidate['id'],
            'headline' => $candidate['headline'],
            'body' => $candidate['body'],
            'image_url' => $candidate['image_url'],
            'advertiser' => $candidate['advertiser'],
            'click_url' => $this->clickUrl($candidate['id'], $hostRole),
        ];
    }

    /**
     * A short-lived token proving a visit actually came through /promo/{hash}.
     *
     * Attribution is otherwise first-touch, and the paid-placement override that jumps that
     * queue must not be forgeable: anyone could append utm_source=boost&utm_medium=network to
     * any link and have every subsequent sale in that browser credited to their campaign.
     *
     * A signed value rather than a session flag because the redirect can land on a schedule's
     * custom domain, where ResolveCustomDomain nulls session.domain and the session does not
     * follow. Hour granularity keeps it valid across a normal browse without being long-lived.
     */
    public static function clickToken(int $campaignId, ?string $hour = null): string
    {
        $hour ??= now()->format('Y-m-d-H');

        return substr(hash_hmac('sha256', 'promo-click|'.$campaignId.'|'.$hour, (string) config('app.key')), 0, 32);
    }

    /**
     * Accepts the current or previous hour so a click at :59 still attributes.
     */
    public static function verifyClickToken(?string $token, ?string $campaignHash): bool
    {
        if (! $token || ! $campaignHash) {
            return false;
        }

        $campaignId = UrlUtils::decodeId($campaignHash);

        if (! $campaignId) {
            return false;
        }

        foreach ([now()->format('Y-m-d-H'), now()->subHour()->format('Y-m-d-H')] as $hour) {
            if (hash_equals(self::clickToken($campaignId, $hour), $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Built by hand rather than through route(), which cannot be used here: both the hosted
     * and the selfhost route carry the name 'promo.click', and the selfhost one is registered
     * second, so route('promo.click') resolves to '{subdomain}/promo/{hash}' even on a hosted
     * install. Asking for it there throws on the missing subdomain parameter.
     *
     * PromotionClickTest::test_the_click_paths_match_the_registered_routes() pins these two
     * strings against routes/web.php so a path change fails loudly instead of 404ing silently.
     */
    public function clickUrl(int $campaignId, Role $hostRole): string
    {
        $hash = UrlUtils::encodeId($campaignId);

        return config('app.hosted')
            ? url("/promo/{$hash}")
            : url("/{$hostRole->subdomain}/promo/{$hash}");
    }
}
