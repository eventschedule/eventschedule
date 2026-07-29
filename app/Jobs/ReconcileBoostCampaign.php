<?php

namespace App\Jobs;

use App\Mail\BoostCompleted;
use App\Models\BoostCampaign;
use App\Models\Role;
use App\Services\BoostBillingService;
use App\Services\MetaAdsService;
use App\Services\MetaAdsServiceFake;
use App\Services\OneSignalService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReconcileBoostCampaign implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    protected BoostCampaign $campaign;

    public $tries = 3;

    public $backoff = [300, 3600, 14400];

    public $timeout = 300;

    public $deleteWhenMissingModels = true;

    public $uniqueFor = 86400;

    public function __construct(BoostCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function uniqueId(): string
    {
        return 'reconcile-boost-'.$this->campaign->id;
    }

    public function handle(): void
    {
        $campaign = $this->campaign;
        $campaign->refresh();
        $campaign->loadMissing(['event', 'user', 'ads']);

        if ($campaign->billing_status === 'refunded' || $campaign->billing_status === 'partially_refunded') {
            return;
        }

        if ($campaign->status !== 'completed') {
            return;
        }

        if ($campaign->isNetwork()) {
            // A network promotion has no external spend to fetch: it accrues into
            // spent_micros as impressions and clicks land. Mirroring it onto actual_spend
            // is what makes the shared refund and reporting paths below correct.
            (new BoostBillingService)->syncNetworkSpend($campaign);
            $campaign->update(['analytics_synced_at' => now()]);
        } else {
            // Fetch final spend data from Meta
            $metaService = $this->getMetaService();
            $insights = $metaService->fetchCampaignInsights($campaign);

            if ($insights) {
                $campaign->update([
                    'actual_spend' => (float) ($insights['spend'] ?? $campaign->actual_spend ?? 0),
                    'analytics_synced_at' => now(),
                ]);
            }
        }

        // Refund unspent budget
        $campaign->refresh();
        if (! in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
            if (! $campaign->stripe_payment_intent_id && $campaign->billing_status === 'charged') {
                // Delegated rather than reimplemented. The inline copy that used to live here
                // differed from the service in three ways that all mattered:
                //   - it locked only the Role, not the campaign, so two dispatches could both
                //     credit the wallet;
                //   - it ignored total_charged, so on selfhost (which records a zero charge
                //     while user_budget keeps the requested amount) it minted free credit;
                //   - `if ($unspentBudget > 0)` had no else, so a campaign that delivered its
                //     whole budget never moved off 'charged' - which kept it matching
                //     SyncPromotions::completeFinishedCampaigns()'s selector every 24 hours and
                //     re-sending the completion email forever.
                (new BoostBillingService)->refundCreditRemainder($campaign);
            } elseif (config('app.hosted') && ! config('app.is_testing')) {
                $billingService = new BoostBillingService;
                if (! $billingService->refundUnspent($campaign)) {
                    Log::warning('Boost reconciliation refund failed', [
                        'campaign_id' => $campaign->id,
                    ]);
                }
            }
        }

        $campaign->refresh();

        // Send completion email if still completed (not cancelled during reconciliation)
        if ($campaign->status === 'completed' && $campaign->user) {
            try {
                Mail::to($campaign->user->email)
                    ->send(new BoostCompleted($campaign));
            } catch (\Exception $e) {
                Log::warning('Failed to send boost completed email', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);
            }
            OneSignalService::pushToUser($campaign->user, [
                'title_key' => 'messages.push_boost_completed_title',
                'body_key' => 'messages.push_boost_completed_body',
                'url' => route('boost.show', ['hash' => $campaign->hashedId()]),
                'options' => [],
            ], null);
        }

        // Auto-increase trust limit for hosted mode
        if (config('app.hosted') && $campaign->role_id) {
            // Meta only. boost_max_budget is the per-campaign ceiling for META spend
            // (BoostController checks it via Role::getBoostMaxBudget), so counting network
            // completions here would let a few cheap on-network promotions permanently
            // ratchet up how much a schedule may spend on Facebook and Instagram.
            $completedCount = BoostCampaign::meta()
                ->where('role_id', $campaign->role_id)
                ->where('status', 'completed')
                ->count();

            $newLimit = Role::calculateBoostLimitForCompletedCount($completedCount);
            $role = Role::find($campaign->role_id);

            if ($role) {
                $currentLimit = $role->boost_max_budget !== null
                    ? (float) $role->boost_max_budget
                    : (float) config('services.meta.boost_default_limit', 10);

                // Only increase, never decrease (safe for admin overrides)
                if ($newLimit > $currentLimit) {
                    $role->update(['boost_max_budget' => $newLimit]);
                    Log::info('Boost spending limit auto-increased', [
                        'role_id' => $role->id,
                        'old_limit' => $currentLimit,
                        'new_limit' => $newLimit,
                        'completed_campaigns' => $completedCount,
                    ]);
                }
            }
        }

        Log::info('Boost campaign reconciled', [
            'campaign_id' => $campaign->id,
            'actual_spend' => $campaign->actual_spend,
            'user_budget' => $campaign->user_budget,
        ]);
    }

    protected function getMetaService(): MetaAdsService
    {
        if (! MetaAdsService::isBoostConfigured()) {
            return new MetaAdsServiceFake;
        }

        return new MetaAdsService;
    }
}
