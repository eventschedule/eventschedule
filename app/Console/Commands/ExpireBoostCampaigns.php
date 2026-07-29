<?php

namespace App\Console\Commands;

use App\Models\BoostCampaign;
use App\Services\BoostBillingService;
use Illuminate\Console\Command;

class ExpireBoostCampaigns extends Command
{
    protected $signature = 'boost:expire-pending';

    protected $description = 'Expire boost campaigns stuck in pending_payment status';

    public function handle()
    {
        // Deliberately channel-blind. SyncBoostCampaigns::recoverStalePendingPayments() selects
        // the identical set at the identical threshold and RECOVERS it when the intent actually
        // succeeded - but that lives inside boost:sync, which is gated on Meta being configured.
        // So on a network-only install a buyer whose card was charged and whose store() request
        // then died is expired here rather than recovered: cancelPaymentIntent() refunds them in
        // full, so no money is lost, but they have to buy again.
        //
        // Scoping this to ::meta() would be worse - nothing else cleans up network
        // pending_payment rows on such an install, so they would sit forever. Teaching the
        // network path its own recovery means duplicating the Stripe retrieve-and-confirm logic
        // above; worth doing if this turns out to happen in practice.
        $staleCampaigns = BoostCampaign::where('status', 'pending_payment')
            ->where('created_at', '<', now()->subMinutes(30))
            ->get();

        if ($staleCampaigns->isEmpty()) {
            return;
        }

        $billingService = new BoostBillingService;

        foreach ($staleCampaigns as $campaign) {
            $campaign->update(['status' => 'expired']);

            if ($campaign->stripe_payment_intent_id) {
                $billingService->cancelPaymentIntent($campaign);
            }
        }
    }
}
