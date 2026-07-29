<?php

namespace App\Console\Commands;

use App\Models\AnalyticsPromotionsDaily;
use App\Models\BoostCampaign;
use App\Services\BoostBillingService;
use App\Services\PromotionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Housekeeping for on-network promotions.
 *
 * The Meta channel's equivalent (boost:sync) cannot be reused: routes/console.php gates it
 * behind MetaAdsService::isBoostConfigured(), so an operator running only the on-network
 * engine would never have it fire.
 */
class SyncPromotions extends Command
{
    protected $signature = 'promo:sync';

    protected $description = 'Reconcile on-network promotions: delivery, spend, completion and retention.';

    /**
     * How long a paused campaign may sit before it is settled and refunded regardless.
     *
     * Long enough that an advertiser who pauses to edit or wait out a quiet week keeps their
     * budget, short enough that money cannot be stranded indefinitely by a pause the system
     * itself applied.
     */
    private const PAUSED_SETTLE_DAYS = 30;

    public function handle(): int
    {
        // Settlement is deliberately NOT gated on the network being enabled. Campaigns are
        // prepaid, so an operator who switches the network off mid-flight would otherwise
        // strand advertisers' money: nothing would ever complete, and nothing would ever be
        // refunded. Only the delivery-optimisation step below depends on serving being live.
        $serving = PromotionService::isEnabled();

        $this->completeFinishedCampaigns();
        $this->pauseUnservableCampaigns();

        if ($serving) {
            // Meaningless without delivery: a campaign that cannot serve has no CTR to judge.
            $this->pauseUnderperformingCpc();
        }

        $this->reconcileSpend();
        $this->reportSpendDrift();
        $this->pruneOldStats();

        app(PromotionService::class)->forgetCandidates();

        $this->info($serving
            ? 'Promotion sync complete.'
            : 'Promotions network is disabled; settled outstanding campaigns only.');

        return self::SUCCESS;
    }

    /**
     * Retire campaigns that have run their course, and hand them to the existing
     * reconciliation job so unspent budget is refunded through the shared path.
     */
    private function completeFinishedCampaigns(): void
    {
        // 'paused' as well as 'active': pauseUnservableCampaigns() and pauseUnderperformingCpc()
        // below both write 'paused', so selecting only 'active' meant this command's own
        // housekeeping moved campaigns into a state it would never settle - budget stranded with
        // no refund and no alert. The end conditions are unchanged, so a campaign the advertiser
        // paused deliberately still keeps its budget until its window actually closes.
        $finished = BoostCampaign::network()
            ->whereIn('status', ['active', 'paused'])
            ->where(function ($q) {
                $q->whereNotNull('exhausted_at')
                    ->orWhere(fn ($q2) => $q2->whereNotNull('scheduled_end')->where('scheduled_end', '<', now()))
                    // scheduled_end is optional, so without this an open-ended campaign whose
                    // event is over would stay 'active' forever - never serving (candidates()
                    // excludes past events) and so never exhausting, and never refunding.
                    // Mirrors the serving-side bound: a recurring event is never "over".
                    ->orWhereExists(function ($q2) {
                        $q2->select(DB::raw(1))
                            ->from('events as e')
                            ->whereColumn('e.id', 'boost_campaigns.event_id')
                            ->whereNull('e.days_of_week')
                            ->whereNotNull('e.starts_at')
                            ->whereRaw('DATE_ADD(e.starts_at, INTERVAL COALESCE(e.duration, 0) HOUR) < ?', [now()]);
                    })
                    // A backstop for the shape none of the above can reach: paused, on a
                    // RECURRING event (so never "over"), with no scheduled_end and no
                    // exhausted_at (it stopped serving before it could exhaust). That is exactly
                    // what pauseUnservableCampaigns() produces when a recurring event goes
                    // draft, and without this the budget sat paused forever - never served,
                    // never settled, never refunded, and in no admin alert.
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'paused')
                            ->where('updated_at', '<', now()->subDays(self::PAUSED_SETTLE_DAYS));
                    });
            })
            ->get();

        foreach ($finished as $campaign) {
            $campaign->update(['status' => 'completed']);
            $this->line("Completed promotion {$campaign->id}");
        }

        // Refund the unspent balance a day after completion, matching the Meta channel's
        // settling delay so late-arriving delivery is counted first.
        $toReconcile = BoostCampaign::network()
            ->where('status', 'completed')
            ->where('billing_status', 'charged')
            ->where('updated_at', '<=', now()->subDay())
            ->get();

        foreach ($toReconcile as $campaign) {
            \App\Jobs\ReconcileBoostCampaign::dispatch($campaign);
        }
    }

    /**
     * Pause a campaign whose event has stopped being publicly visible.
     *
     * The serving query already refuses to deliver these, but leaving them "active" is
     * misleading: the advertiser sees a live campaign that never spends and is told nothing.
     */
    private function pauseUnservableCampaigns(): void
    {
        $campaigns = BoostCampaign::network()
            ->where('status', 'active')
            ->with('event')
            ->get();

        foreach ($campaigns as $campaign) {
            $event = $campaign->event;

            $unservable = ! $event
                || $event->is_draft
                || $event->is_private
                || $event->is_cancelled;

            if ($unservable) {
                $campaign->update(['status' => 'paused']);
                Log::info('Paused promotion whose event is no longer public', ['campaign_id' => $campaign->id]);
                $this->line("Paused promotion {$campaign->id} (event not public)");
            }
        }
    }

    /**
     * Stop CPC creative that nobody clicks.
     *
     * A CPC campaign pays nothing for impressions, so weak creative would otherwise consume
     * host inventory indefinitely for free.
     */
    private function pauseUnderperformingCpc(): void
    {
        $floor = (float) config('ads.native_ctr_floor', 0.0002);
        $minImpressions = (int) config('ads.native_ctr_floor_min_impressions', 5000);

        $campaigns = BoostCampaign::network()
            ->where('status', 'active')
            ->where('pricing_model', 'cpc')
            ->get();

        foreach ($campaigns as $campaign) {
            $totals = AnalyticsPromotionsDaily::forCampaign($campaign->id)
                ->selectRaw('SUM(impressions) as impression_count, SUM(clicks) as click_count')
                ->first();

            $impressions = (int) ($totals->impression_count ?? 0);

            if ($impressions < $minImpressions) {
                continue;
            }

            $ctr = $impressions > 0 ? ((int) ($totals->click_count ?? 0)) / $impressions : 0;

            if ($ctr < $floor) {
                $campaign->update(['status' => 'paused']);
                $this->line("Paused promotion {$campaign->id} (CTR below floor)");
            }
        }
    }

    /**
     * Mirror delivery onto the cached counters the rest of the app already reads.
     *
     * Doing this here is what lets AnalyticsService::getBoostStats(), the admin revenue
     * figures and the campaign cards work across both channels with no changes.
     */
    private function reconcileSpend(): void
    {
        $campaigns = BoostCampaign::network()
            ->whereIn('status', ['active', 'paused', 'completed'])
            // Skip campaigns old enough that pruneOldStats() has already deleted their rollup
            // rows: recomputing from an empty SUM would reset a finished campaign's lifetime
            // counters to zero and then log a permanent false drift warning against it.
            ->where(function ($q) {
                $q->whereIn('status', ['active', 'paused'])
                    ->orWhere('updated_at', '>=', now()->subDays((int) config('ads.stats_retention_days', 400)));
            })
            ->get();

        $billing = new BoostBillingService;

        foreach ($campaigns as $campaign) {
            $totals = AnalyticsPromotionsDaily::forCampaign($campaign->id)
                ->selectRaw('SUM(impressions) as impression_count, SUM(clicks) as click_count')
                ->first();

            $impressions = (int) ($totals->impression_count ?? 0);
            $clicks = (int) ($totals->click_count ?? 0);

            $fresh = [
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 4) : 0,
                'cpc' => $clicks > 0 ? round($campaign->spentAmount() / $clicks, 2) : 0,
                'cpm' => $impressions > 0 ? round(($campaign->spentAmount() / $impressions) * 1000, 2) : 0,
            ];

            // Only write when a counter actually moved. This is not an optimization: writing
            // 'analytics_synced_at' => now() unconditionally touched updated_at on every
            // fifteen-minute run, and completeFinishedCampaigns() selects campaigns to refund
            // with `updated_at <= now()->subDay()`. That condition could therefore never become
            // true, and no network campaign ever had its unspent budget returned.
            //
            // Compared numerically against the RAW column values. Reading $campaign->ctr goes
            // through a `decimal:` cast, which returns a fixed-scale string - "1.0000" against
            // a fresh float of 1, "0.20" against 0.2 - so a string comparison reported every
            // campaign as changed on every run and reinstated the exact bug above. Casts are
            // a read-side presentation concern; the guard has to look at what is stored.
            $changed = array_filter(
                $fresh,
                fn ($value, $key) => (float) $campaign->getRawOriginal($key) !== (float) $value,
                ARRAY_FILTER_USE_BOTH
            );

            if ($changed !== []) {
                $campaign->update($fresh + ['analytics_synced_at' => now()]);
            }

            $billing->syncNetworkSpend($campaign);
        }
    }

    /**
     * Flag any campaign whose billed spend and counted delivery disagree.
     *
     * The debit and the rollup write are two statements, so a crash between them leaves a
     * charge with no impression. That is the safe direction to fail, but it must not go
     * unnoticed - a persistent gap means the advertiser is being over-billed.
     */
    private function reportSpendDrift(): void
    {
        $campaigns = BoostCampaign::network()
            ->whereIn('status', ['active', 'paused', 'completed'])
            // Skip campaigns old enough that pruneOldStats() has already deleted their rollup
            // rows: recomputing from an empty SUM would reset a finished campaign's lifetime
            // counters to zero and then log a permanent false drift warning against it.
            ->where(function ($q) {
                $q->whereIn('status', ['active', 'paused'])
                    ->orWhere('updated_at', '>=', now()->subDays((int) config('ads.stats_retention_days', 400)));
            })
            ->get();

        foreach ($campaigns as $campaign) {
            $totals = AnalyticsPromotionsDaily::forCampaign($campaign->id)
                ->selectRaw('SUM(impressions) as impression_count, SUM(clicks) as click_count')
                ->first();

            $expected = $campaign->pricing_model === 'cpm'
                ? ((int) ($totals->impression_count ?? 0)) * $campaign->impressionCostMicros()
                : ((int) ($totals->click_count ?? 0)) * $campaign->clickCostMicros();

            // One cent of tolerance for the ordinary in-flight window.
            if (abs($expected - (int) $campaign->spent_micros) > 10_000) {
                Log::warning('Promotion spend drift', [
                    'campaign_id' => $campaign->id,
                    'expected_micros' => $expected,
                    'recorded_micros' => (int) $campaign->spent_micros,
                ]);
            }
        }
    }

    /**
     * These rollups are keyed by campaign x host x day, so they grow faster than the other
     * analytics tables. Nothing else in the codebase prunes analytics, but nothing else is
     * this wide either.
     */
    private function pruneOldStats(): void
    {
        $cutoff = now()->subDays((int) config('ads.stats_retention_days', 400))->toDateString();

        $deleted = DB::table('analytics_promotions_daily')->where('date', '<', $cutoff)->delete();
        $deleted += DB::table('promotion_locations_daily')->where('date', '<', $cutoff)->delete();

        if ($deleted > 0) {
            $this->line("Pruned {$deleted} promotion stat rows older than {$cutoff}");
        }
    }
}
