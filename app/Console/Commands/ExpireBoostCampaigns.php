<?php

namespace App\Console\Commands;

use App\Models\BoostCampaign;
use App\Services\BoostBillingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireBoostCampaigns extends Command
{
    protected $signature = 'boost:expire-pending';

    protected $description = 'Expire boost campaigns stuck in pending_payment status';

    public function handle()
    {
        $staleCampaigns = BoostCampaign::where('status', 'pending_payment')
            ->where('created_at', '<', now()->subMinutes(30))
            ->get();

        if ($staleCampaigns->isEmpty()) {
            return;
        }

        Log::info('Expiring '.$staleCampaigns->count().' stale pending_payment boost campaigns');

        $billingService = new BoostBillingService;

        foreach ($staleCampaigns as $campaign) {
            $campaign->update(['status' => 'expired']);

            if ($campaign->stripe_payment_intent_id) {
                $billingService->cancelPaymentIntent($campaign);
            }
        }
    }
}
