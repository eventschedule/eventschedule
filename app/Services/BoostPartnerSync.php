<?php

namespace App\Services;

use App\Exceptions\MetaApiException;
use App\Models\BoostCampaign;
use App\Models\BoostPartner;
use App\Models\BoostSpendDay;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Follow every partner boost at Meta: its review and running state, its totals and its spend
 * per day (in the ad account's timezone, which is how Meta reports it and how the monthly fee
 * is summed). Runs inside boost:sync. A partner's ad account is not subscribed to the
 * platform's webhook, so this is the only way a partner boost moves on.
 *
 * Meta's ad effective_status: ACTIVE is running; PENDING_REVIEW, IN_PROCESS and PREAPPROVED are
 * under review; DISAPPROVED is refused; WITH_ISSUES and PENDING_BILLING_INFO need the partner's
 * attention and change nothing here. There is no "completed" status at Meta: a boost is
 * completed when its end time has passed or its spend has reached its lifetime budget.
 */
class BoostPartnerSync
{
    /** Spend is final once a sync this long after the end changes nothing. */
    public const FINAL_AFTER_DAYS = 3;

    /** Access that stays lost this long stops the partner's live boosts. */
    public const ACCESS_GRACE_HOURS = 72;

    public function run(): void
    {
        $this->retryPendingPauses();

        $campaigns = BoostCampaign::viaPartner()
            ->whereNotNull('meta_campaign_id')
            ->where(function ($q) {
                $q->whereIn('status', BoostCampaign::PARTNER_LIVE_STATUSES)
                    // Paused at Meta for an edit and waiting on approval or payment: what it spent
                    // to the pause, which the Campaigns list and the spend floor of a budget edit
                    // read, is still coming in.
                    ->orWhereIn('status', ['pending_approval', 'awaiting_payment', 'ready'])
                    ->orWhere(function ($q) {
                        // Declined too: a boost that ran and was then declined (an edit sent back,
                        // its end passed while paused) has spend still to be read to its end.
                        $q->whereIn('status', ['completed', 'stopped', 'rejected', 'declined'])->whereNull('spend_final_at');
                    });
            })
            ->with(['ads', 'partner.user', 'user'])
            ->get();

        // Whether each partner's account answered this run: one campaign's error must not be
        // cleared by another's success, or the flag (and its mail, and the 72-hour clock) flaps.
        $failed = [];
        $answered = [];

        foreach ($campaigns as $campaign) {
            // A campaign that ran on an ad account the partner has since left says nothing about
            // the account it uses now: it neither raises nor clears the partner's flag.
            $current = ! $campaign->partner?->ad_account_id
                || (string) $campaign->meta_ad_account_id === (string) $campaign->partner->ad_account_id;

            try {
                $this->syncOne($campaign);
                if ($current) {
                    $answered[$campaign->boost_partner_id] = true;
                }
            } catch (MetaApiException $e) {
                if ($e->isPermission() && ! $current) {
                    $this->abandonOldAccount($campaign);
                } elseif ($e->isPermission()) {
                    $failed[$campaign->boost_partner_id] = $e;
                } else {
                    Log::warning('Boost partner sync failed', ['campaign_id' => $campaign->id, 'error' => $e->getMessage()]);
                }
            } catch (\Exception $e) {
                report($e);
            }
        }

        foreach ($failed as $partnerId => $e) {
            BoostPartnerNotifier::accessProblem(BoostPartner::find($partnerId), $e);
        }

        // Access is back only where every call for that partner worked: clear the flag and launch
        // whatever was approved and paid while it was down.
        foreach (array_keys($answered) as $partnerId) {
            if (isset($failed[$partnerId])) {
                continue;
            }
            $partner = BoostPartner::find($partnerId);
            if ($partner && $partner->needs_attention_at) {
                $partner->forceFill(['needs_attention_at' => null, 'connection_error' => null, 'connection_checked_at' => now()])->save();
                app(BoostPartnerService::class)->launchReady($partner->fresh());
            }
        }

        $this->stopWhereAccessStayedLost();
    }

    /**
     * Read one campaign's totals, daily spend and state. Figures are written straight to the row
     * (updated_at moves only when spend changed, so the API's updated_since is not every live
     * boost on every run); a change of STATUS is made under the campaign's lock and only from
     * the status it was read in, so a Stop or an edit that lands meanwhile is never overwritten.
     */
    public function syncOne(BoostCampaign $campaign): void
    {
        $meta = MetaAdsService::forCampaign($campaign);
        $before = round((float) $campaign->actual_spend, 2);
        $figures = [];

        $totals = $meta->campaignTotals($campaign);
        if ($totals) {
            $figures = [
                'actual_spend' => round((float) ($totals['spend'] ?? 0), 2),
                'impressions' => (int) ($totals['impressions'] ?? 0),
                'reach' => (int) ($totals['reach'] ?? 0),
                'clicks' => (int) ($totals['clicks'] ?? 0),
                'ctr' => (float) ($totals['ctr'] ?? 0),
                'cpc' => (float) ($totals['cpc'] ?? 0),
                'cpm' => (float) ($totals['cpm'] ?? 0),
                'analytics_synced_at' => now(),
            ];
            $campaign->forceFill($figures);
        }

        $this->syncSpendDays($campaign, $meta, 3);

        if (in_array($campaign->status, BoostCampaign::PARTNER_LIVE_STATUSES, true)) {
            $this->syncState($campaign, $meta, $figures);
        } elseif (in_array($campaign->status, ['completed', 'stopped', 'rejected', 'declined'], true)) {
            // Only a boost that has ended is ever final: one paused for an edit may run again.
            $endedAt = $campaign->stopped_at ?? $campaign->scheduled_end ?? $campaign->updated_at;
            $unchanged = round((float) $campaign->actual_spend, 2) === $before;
            if ($endedAt && $endedAt->lte(now()->subDays(self::FINAL_AFTER_DAYS)) && $unchanged) {
                $figures['spend_final_at'] = now();
            }
        }

        $figures['meta_synced_at'] = now();
        if (round((float) ($figures['actual_spend'] ?? $before), 2) !== $before || isset($figures['spend_final_at'])) {
            $figures['updated_at'] = now();
        }
        DB::table('boost_campaigns')->where('id', $campaign->id)->update($figures);
    }

    protected function syncState(BoostCampaign $campaign, MetaAdsService $meta, array &$figures): void
    {
        $ad = $campaign->ads->first();
        if (! $ad || ! $ad->meta_ad_id) {
            return;
        }

        // An edit waiting for approval or payment keeps the ad paused; nothing to read.
        if (! $campaign->isApprovedCurrent() || ! $campaign->isPaidCurrent()) {
            return;
        }

        $state = $meta->adState($ad->meta_ad_id);
        $status = strtoupper((string) ($state['status'] ?? ''));
        $ad->forceFill(['meta_status' => $status ?: null])->save();

        if ($status === 'DISAPPROVED') {
            $ad->forceFill(['meta_rejection_reason' => $state['rejection_reason']])->save();
            if ($this->transition($campaign, BoostCampaign::PARTNER_LIVE_STATUSES, [
                'status' => 'rejected',
                'meta_status' => 'DISAPPROVED',
                'meta_rejection_reason' => $state['rejection_reason'] ?: __('messages.boost_partner_rejected_default_reason'),
            ])) {
                BoostPartnerNotifier::rejected($campaign->fresh());
            }

            return;
        }

        // A problem on the partner's side of Meta (billing, an issue with the ad account): the
        // boost keeps its status, and the Campaigns list shows Meta's word for it from
        // meta_status. Access is fine, so this never counts toward stopping anything.
        if (in_array($status, ['WITH_ISSUES', 'PENDING_BILLING_INFO'], true)) {
            $figures['meta_status'] = $status;

            return;
        }

        $ended = ($campaign->scheduled_end && $campaign->scheduled_end->isPast())
            || ($campaign->user_budget > 0 && round((float) $campaign->actual_spend, 2) >= round((float) $campaign->user_budget, 2));

        if ($ended && $campaign->status === 'active') {
            $this->transition($campaign, ['active'], ['status' => 'completed']);

            return;
        }

        if ($status === 'ACTIVE' && $campaign->status === 'in_review'
            && $this->transition($campaign, ['in_review'], ['status' => 'active', 'meta_status' => 'ACTIVE'], (int) $campaign->version)) {
            BoostPartnerNotifier::live($campaign->fresh());
        }
    }

    /**
     * Change a campaign's status under its lock, only from one of $from (and, when given, only
     * while it is still the same version). Returns whether it changed.
     */
    protected function transition(BoostCampaign $campaign, array $from, array $fields, ?int $version = null): bool
    {
        return DB::transaction(function () use ($campaign, $from, $fields, $version) {
            $fresh = BoostCampaign::lockForUpdate()->find($campaign->id);
            if (! $fresh || ! in_array($fresh->status, $from, true) || ($version !== null && (int) $fresh->version !== $version)) {
                return false;
            }
            $fresh->forceFill($fields)->save();

            return true;
        });
    }

    /**
     * Spend per day since launch, in the ad account's timezone. A day Meta corrects later is
     * overwritten here, and the statement picks the difference up as an adjustment.
     */
    public function syncSpendDays(BoostCampaign $campaign, MetaAdsService $meta, ?int $lastDays = null): void
    {
        if (! $campaign->launched_at) {
            return;
        }

        $tz = $campaign->account_timezone ?: 'UTC';
        $since = $campaign->launched_at->copy()->setTimezone($tz)->subDay();
        // The 15-minute sync reads the last few days only; the daily statement run reads the whole
        // life again (refreshRecentSpend()), which is where an older correction is picked up.
        if ($lastDays !== null) {
            $since = $since->max(Carbon::now($tz)->subDays($lastDays));
        }
        $until = Carbon::now($tz);
        if ($campaign->stopped_at) {
            $until = $campaign->stopped_at->copy()->setTimezone($tz)->addDays(2)->min($until);
        }

        // A boost stopped well before the last few days has nothing in them: the short read would
        // ask Meta for a range that ends before it starts, which it refuses, and the totals read
        // with it would never be written. The daily full read covers it.
        if ($since->gt($until)) {
            return;
        }

        $days = $meta->dailySpend($campaign, $since, $until);
        foreach ($days as $day => $spend) {
            BoostSpendDay::updateOrCreate(
                ['boost_campaign_id' => $campaign->id, 'day' => $day],
                ['spend' => $spend, 'currency' => $campaign->currency_code ?: 'USD'],
            );
        }
    }

    /**
     * A Stop whose pause did not reach Meta is retried until it does, for a week. After that the
     * account is taken to be out of reach for good (the partner left it): the pending pause is
     * dropped so it no longer holds the partner's settings, and the log says so.
     */
    protected function retryPendingPauses(): void
    {
        $service = app(BoostPartnerService::class);

        BoostCampaign::viaPartner()
            ->whereNotNull('pause_pending_at')
            ->whereNotNull('meta_campaign_id')
            ->with('partner')
            ->each(function (BoostCampaign $campaign) use ($service) {
                if ($service->pauseAtMeta($campaign)) {
                    return;
                }
                if ($campaign->pause_pending_at->lt(now()->subDays(7))) {
                    Log::warning('Boost partner: gave up pausing a campaign at Meta after a week', ['campaign_id' => $campaign->id]);
                    $campaign->forceFill(['pause_pending_at' => null])->save();
                }
            });
    }

    /**
     * An ad account the partner no longer uses stopped answering: its campaigns can be read no
     * more, so their spend is final as it stands, and one still marked live is stopped here (its
     * ad, if any, is the partner's to stop in that account).
     */
    protected function abandonOldAccount(BoostCampaign $campaign): void
    {
        $fields = ['spend_final_at' => now(), 'pause_pending_at' => null];

        DB::transaction(function () use ($campaign, $fields) {
            $fresh = BoostCampaign::lockForUpdate()->find($campaign->id);
            if (! $fresh) {
                return;
            }
            if (in_array($fresh->status, BoostCampaign::PARTNER_LIVE_STATUSES, true)) {
                $fields += ['status' => 'stopped', 'stopped_at' => now(), 'stop_code' => BoostPartnerService::STOP_ACCESS_WITHDRAWN];
            }
            $fresh->forceFill($fields)->save();
        });
    }

    /**
     * One lost answer must not end paid campaigns, so a partner first "needs attention"; only
     * when that has lasted ACCESS_GRACE_HOURS are its live boosts stopped. Ads in the partner's
     * own account may keep running at Meta after our access is gone: the setup instructions
     * say so.
     */
    protected function stopWhereAccessStayedLost(): void
    {
        $partners = BoostPartner::whereNotNull('needs_attention_at')
            ->where('needs_attention_at', '<=', now()->subHours(self::ACCESS_GRACE_HOURS))
            ->get();

        $service = app(BoostPartnerService::class);
        foreach ($partners as $partner) {
            $partner->campaigns()->whereIn('status', BoostCampaign::PARTNER_LIVE_STATUSES)->each(function (BoostCampaign $campaign) use ($service) {
                try {
                    $service->stop($campaign, null, BoostPartnerService::STOP_ACCESS_WITHDRAWN);
                } catch (\Exception $e) {
                    report($e);
                }
            });
        }
    }
}
