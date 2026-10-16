<?php

namespace App\Jobs;

use App\Models\BoostCampaign;
use App\Services\AuditService;
use App\Services\BoostPartnerNotifier;
use App\Services\BoostPartnerService;
use App\Services\MetaAdsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Send an approved and paid partner boost to the partner's own ad account, or, for one that was
 * already running, apply its newly approved version and resume it.
 *
 * The gates are checked under the campaign's lock before Meta is called and again after: an
 * edit or a Stop that lands while Meta is being called pauses what was just started. The Meta
 * campaign id is saved the moment Meta answers the first call, so a retry after a killed worker
 * carries on with that campaign instead of creating a second live one.
 */
class LaunchPartnerBoost implements ShouldQueue
{
    use Queueable;

    // Bounded by time and by real exceptions, not by attempts: WithoutOverlapping releases a
    // job that waits behind another launch of the same boost, and every release counts as an
    // attempt. With $tries a slow first launch made the waiting job "fail" and mark a healthy
    // launch rejected.
    public $maxExceptions = 3;

    public $backoff = [30, 120, 300];

    public $timeout = 300;

    public $deleteWhenMissingModels = true;

    // Not ShouldBeUnique: a re-approval that arrives while a launch is mid-call must not be
    // dropped. Two jobs are harmless: each works under the campaign's lock, the Meta campaign id
    // is saved at once, and the re-check after the calls pauses whatever is no longer wanted.

    public function __construct(public BoostCampaign $campaign) {}

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(30);
    }

    /**
     * One launch per campaign at a time: a second job waits its turn instead of being dropped,
     * so it never re-enters a creation the first is in the middle of.
     */
    public function middleware(): array
    {
        return [(new \Illuminate\Queue\Middleware\WithoutOverlapping('boost-launch-'.$this->campaign->id))
            ->releaseAfter(30)->expireAfter(600)];
    }

    public function handle(): void
    {
        $prepared = DB::transaction(function () {
            $campaign = BoostCampaign::lockForUpdate()->find($this->campaign->id);
            if (! $campaign) {
                return null;
            }

            if (! $this->gatesHold($campaign)) {
                // Approved and paid, but the partner's account cannot be used right now: back to
                // "ready", which launchReady() picks up once the account is usable again. Never
                // left in_review with nothing at Meta.
                if ($campaign->status === 'in_review'
                    && $campaign->isApprovedCurrent() && $campaign->isPaidCurrent()) {
                    $campaign->forceFill(['status' => 'ready'])->save();
                }

                return ['stale' => $campaign];
            }

            // A campaign not yet at Meta takes its account from the partner's CONFIRMED
            // connection now, never from what the partner had saved when the request was made: an
            // unconfirmed ID (a typo, another client's account) must never receive the ad.
            if (! $campaign->meta_campaign_id) {
                $partner = $campaign->partner;
                $currency = $partner->ad_account_currency ?: $campaign->currency_code;
                if ($campaign->currency_code && $currency && strtoupper($currency) !== strtoupper($campaign->currency_code)) {
                    $campaign->forceFill([
                        'status' => 'pending_approval',
                        'approved_version' => null,
                        'payment_confirmed_at' => null,
                        'paid_budget' => null,
                        'decline_reason' => 'currency_changed',
                    ])->save();

                    return null;
                }

                $campaign->forceFill([
                    'meta_ad_account_id' => $partner->ad_account_id,
                    'meta_page_id' => $partner->page_id,
                    'meta_page_name' => $partner->page_name,
                    'account_timezone' => $partner->ad_account_timezone,
                    'currency_code' => $currency,
                ]);
            }

            $campaign->pause_pending_at = null;
            // Running again (a resent rejected or declined boost): its spend is not final until it
            // ends this time, and the sync's last pass after that must not be skipped.
            $campaign->spend_final_at = null;

            $version = $campaign->approvedVersion();
            if (! $version) {
                return null;
            }

            $end = $version->end_date ?? $campaign->scheduled_end;
            if ($end && $end->isPast()) {
                $campaign->forceFill([
                    'status' => 'declined',
                    'declined_at' => now(),
                    'decline_reason' => BoostPartnerService::DECLINE_END_PASSED,
                ])->save();

                return null;
            }

            if (! $campaign->meta_campaign_id && $campaign->scheduled_start && $campaign->scheduled_start->isPast()) {
                $campaign->scheduled_start = now();
            }

            // The ad Meta is sent is always the approved version, never whatever the BoostAd
            // row held before. A picture's hash belongs to one ad account, so a new picture or
            // another account uploads it again; Meta's old verdict is cleared with it.
            $ad = $campaign->ads()->first();
            if ($ad) {
                $fields = app(BoostPartnerService::class)->adFieldsFrom($version);
                $resetHash = $ad->image_url !== $fields['image_url'];
                $ad->fill($fields + ['meta_status' => null, 'meta_rejection_reason' => null]);
                if ($resetHash) {
                    $ad->image_hash = null;
                }
                $ad->save();
            }

            $campaign->save();

            return ['campaign' => $campaign, 'version' => $version];
        });

        if (! $prepared) {
            return;
        }

        if (isset($prepared['stale'])) {
            // A request that is no longer wanted but already reached Meta (a retry after a Stop
            // or an edit landed between a killed worker's first call and its save): hold it.
            $stale = $prepared['stale'];
            if ($stale->meta_campaign_id && ! in_array($stale->status, ['active'], true)) {
                app(BoostPartnerService::class)->pauseAtMeta($stale);
            }

            return;
        }

        /** @var BoostCampaign $campaign */
        $campaign = $prepared['campaign'];
        $version = $prepared['version'];
        // Resuming whenever the ad already exists at Meta, launched_at or not: a first launch
        // that created the ad and then stopped short (a lost token, a failed ACTIVE) must swap in
        // the newly approved version, never switch the old one back on.
        $resuming = (bool) $campaign->meta_campaign_id
            && ($campaign->launched_at || $campaign->ads()->whereNotNull('meta_ad_id')->exists());

        try {
            $meta = MetaAdsService::forCampaign($campaign);
            if ($resuming) {
                $ad = $campaign->ads()->first();
                if ($ad && $ad->meta_ad_id) {
                    $meta->updateCreative($ad);
                }
                if ($campaign->meta_adset_id) {
                    $meta->updateAdSet($campaign->meta_adset_id, (float) $version->budget, $version->end_date, $version->targeting, $campaign->currency_code);
                    $meta->setStatus($campaign->meta_adset_id, 'ACTIVE');
                }
                $meta->setStatus($campaign->meta_campaign_id, 'ACTIVE');
            } else {
                $meta->createPartnerCampaign($campaign, $version, function (string $metaCampaignId) use ($campaign) {
                    BoostCampaign::whereKey($campaign->id)->update(['meta_campaign_id' => $metaCampaignId]);
                    $campaign->meta_campaign_id = $metaCampaignId;
                });
            }
        } catch (\App\Exceptions\MetaApiException $e) {
            if ($e->isPermission()) {
                // Our access to the partner's account is gone: that is the partner's to fix, not
                // a verdict on the ad. The request waits as "ready" and launches again once the
                // account answers (BoostPartnerSync calls launchReady()).
                BoostPartnerNotifier::accessProblem($campaign->partner, $e);
                BoostCampaign::whereKey($campaign->id)->where('status', 'in_review')
                    ->update(['status' => 'ready']);

                return;
            }

            throw $e;
        }

        $stillWanted = DB::transaction(function () use ($campaign, $version) {
            $fresh = BoostCampaign::lockForUpdate()->find($campaign->id);
            if (! $fresh) {
                return false;
            }

            $fresh->forceFill([
                'meta_status' => 'PENDING_REVIEW',
                'meta_synced_at' => now(),
                'launched_at' => $fresh->launched_at ?? now(),
            ]);

            $wanted = $fresh->status === 'in_review'
                && (int) $fresh->approved_version === (int) $version->version
                && $fresh->isApprovedCurrent()
                && $fresh->isPaidCurrent();

            $fresh->save();

            return $wanted;
        });

        if (! $stillWanted) {
            // Something changed while Meta was being called: hold what was just started.
            try {
                MetaAdsService::forCampaign($campaign)->setStatus($campaign->fresh()->meta_campaign_id, 'PAUSED');
            } catch (\Exception $e) {
                BoostCampaign::whereKey($campaign->id)->update(['pause_pending_at' => now()]);
                Log::warning('Boost partner: could not pause a launch that was overtaken', ['campaign_id' => $campaign->id, 'error' => $e->getMessage()]);
            }

            return;
        }

        AuditService::log(AuditService::BOOST_PARTNER_LAUNCH, null, 'BoostCampaign', $campaign->id, null, ['version' => $version->version]);
    }

    public function failed(?\Throwable $exception): void
    {
        $campaign = BoostCampaign::find($this->campaign->id);
        if (! $campaign || $campaign->status !== 'in_review') {
            return;
        }

        if ($exception) {
            report($exception);
        }

        // A generic reason: Meta's own message is for the log, never for the Business.
        $campaign->forceFill([
            'status' => 'rejected',
            'meta_status' => 'ERROR',
            'meta_rejection_reason' => __('messages.boost_partner_launch_failed_reason'),
        ])->save();

        if ($campaign->meta_campaign_id) {
            app(BoostPartnerService::class)->pauseAtMeta($campaign);
        }

        BoostPartnerNotifier::rejected($campaign->fresh());
    }

    protected function gatesHold(BoostCampaign $campaign): bool
    {
        return $campaign->isPartner()
            && $campaign->status === 'in_review'
            && $campaign->isApprovedCurrent()
            && $campaign->isPaidCurrent()
            && $campaign->partner
            && $campaign->partner->isUsable()
            // The schedule must still be the partner's: one that changed hands is not its to run.
            && (! $campaign->event || \App\Models\BoostPartner::forEvent($campaign->event)?->id === $campaign->boost_partner_id);
    }
}
