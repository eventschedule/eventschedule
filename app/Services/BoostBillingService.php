<?php

namespace App\Services;

use App\Models\BoostBillingRecord;
use App\Models\BoostCampaign;
use App\Models\Role;
use App\Utils\PlatformCurrency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BoostBillingService
{
    /**
     * Issue the right refund when a campaign is cancelled or its event/schedule is deleted.
     *
     * This exists because the choice between a full and a partial refund was written out by
     * hand at five call sites (BoostController::cancel, the Event and Role deleting hooks,
     * and the two API delete paths). All five read actual_spend, which network campaigns
     * never populate - their delivered spend lives in spent_micros - so all five would have
     * issued a FULL refund to an advertiser whose impressions had already been served.
     *
     * Normalizing here rather than patching each ternary means the sixth caller is correct
     * for free, which matters for a predicate that has already been copy-pasted five times.
     */
    public function refundOnCancellation(BoostCampaign $campaign): bool
    {
        $this->syncNetworkSpend($campaign);

        // Credit-funded campaigns settle locally - refundUnspent()/refundFull() both bail
        // without a Stripe intent, so routing them here is what stops a wallet purchase
        // being refunded in full after its impressions have already been delivered.
        if (! $campaign->stripe_payment_intent_id && $campaign->billing_status === 'charged') {
            return $this->refundCreditRemainder($campaign);
        }

        return $campaign->actual_spend && $campaign->actual_spend > 0
            ? $this->refundUnspent($campaign)
            : $this->refundFull($campaign);
    }

    /**
     * The card refund for a partially delivered campaign, in dollars.
     *
     * Extracted from refundUnspent() purely so it can be tested. That method news up a
     * \Stripe\StripeClient inline, so these two lines were unreachable without a live API key
     * and no test ever executed them - a regression that dropped the markup, or the * 100 cents
     * conversion below, would have refunded a real card by the wrong amount with a green suite.
     */
    public static function unspentRefundAmount(BoostCampaign $campaign): float
    {
        $delivered = (float) ($campaign->actual_spend ?? 0);
        $unspent = max(0, (float) $campaign->user_budget - $delivered);

        return round($unspent * (1 + (float) $campaign->markup_rate), 2);
    }

    /**
     * Stripe takes minor units. Rounded, not cast: (int) (0.29 * 100) is 28.
     */
    public static function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Return the UNDELIVERED portion of a credit-funded campaign to the schedule's wallet.
     *
     * The amount is the unspent budget grossed up by the markup, mirroring refundUnspent() -
     * for a network campaign markup_rate is 0, so it is simply what was never delivered.
     */
    public function refundCreditRemainder(BoostCampaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);

            if (! $campaign || in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
                return false;
            }

            $delivered = (float) ($campaign->actual_spend ?? 0);
            $unspent = max(0, (float) $campaign->user_budget - $delivered);
            $refundAmount = round($unspent * (1 + (float) $campaign->markup_rate), 2);

            // Never return more than was actually taken. On hosted this is a no-op - a credit
            // purchase writes total_charged = user_budget * (1 + markup_rate), which is always
            // >= the unspent portion of it. On SELFHOST, BoostController records
            // total_charged = 0 while user_budget still holds the requested amount, so without
            // this a create-then-cancel loop would mint boost_credit that nobody ever paid for.
            $refundAmount = min($refundAmount, round((float) ($campaign->total_charged ?? 0), 2));

            if ($refundAmount <= 0) {
                // Fully delivered: nothing to give back, but the campaign is settled.
                $campaign->update(['billing_status' => 'refunded']);

                return true;
            }

            $role = Role::lockForUpdate()->find($campaign->role_id);

            if (! $role) {
                // The schedule is gone (role_id is nullOnDelete), so there is no wallet to
                // credit - but the campaign must still be marked settled. Leaving it 'charged'
                // meant both completion queries kept selecting it, ReconcileBoostCampaign kept
                // touching updated_at, and the advertiser received the "campaign completed"
                // email every 24 hours forever.
                $campaign->update(['billing_status' => 'refunded']);

                Log::warning('Promotion refund had no schedule to credit', [
                    'campaign_id' => $campaign->id,
                    'amount' => $refundAmount,
                ]);

                return false;
            }

            $role->increment('boost_credit', $refundAmount);

            BoostBillingRecord::create([
                'boost_campaign_id' => $campaign->id,
                'type' => 'refund',
                'amount' => $refundAmount,
                'meta_spend' => $delivered,
                'markup_amount' => round($unspent * (float) $campaign->markup_rate, 2),
                'status' => 'completed',
                'notes' => 'Credit returned - unspent budget',
            ]);

            $campaign->update([
                'billing_status' => $delivered > 0 ? 'partially_refunded' : 'refunded',
            ]);

            return true;
        });
    }

    /**
     * Mirror a network campaign's delivered spend onto actual_spend.
     *
     * Meta campaigns get actual_spend from Meta's insights API; network campaigns accrue
     * into spent_micros as impressions and clicks land. Everything downstream of this - the
     * refund maths, the admin revenue figures, AnalyticsService::getBoostStats() - reads
     * actual_spend, so this is the single point where the two representations meet.
     */
    public function syncNetworkSpend(BoostCampaign $campaign): void
    {
        if (! $campaign->isNetwork()) {
            return;
        }

        $spent = $campaign->spentAmount();

        if ((float) ($campaign->actual_spend ?? 0) === $spent) {
            return;
        }

        $campaign->update(['actual_spend' => $spent]);
        $campaign->refresh();
    }

    /**
     * Confirm a payment was successful and update records
     */
    public function confirmPayment(BoostCampaign $campaign, string $paymentIntentId): bool
    {
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

            if ($paymentIntent->status === 'succeeded') {
                // Verify payment intent belongs to this campaign's event
                $intentEventId = $paymentIntent->metadata->event_id ?? null;
                if ($intentEventId && (int) $intentEventId !== $campaign->event_id) {
                    Log::error('Boost Billing: Payment intent event_id mismatch', [
                        'campaign_id' => $campaign->id,
                        'campaign_event_id' => $campaign->event_id,
                        'intent_event_id' => $intentEventId,
                        'payment_intent_id' => $paymentIntentId,
                    ]);

                    return false;
                }

                // Always save the payment intent ID so refunds can be issued if needed
                $campaign->update(['stripe_payment_intent_id' => $paymentIntentId]);

                // Verify the payment amount matches the expected cost
                $expectedCents = (int) round($campaign->getTotalCost() * 100);
                if ($paymentIntent->amount !== $expectedCents) {
                    Log::error('Boost Billing: Payment amount mismatch', [
                        'campaign_id' => $campaign->id,
                        'expected_cents' => $expectedCents,
                        'actual_cents' => $paymentIntent->amount,
                    ]);

                    // Record the mismatch for audit trail - do NOT mark as 'charged'
                    // so the refund path in cancelPaymentIntent() handles cleanup
                    DB::transaction(function () use ($campaign, $paymentIntentId, $paymentIntent, $expectedCents) {
                        $campaign->update([
                            'total_charged' => $paymentIntent->amount / 100,
                            'billing_status' => 'amount_mismatch',
                        ]);

                        BoostBillingRecord::create([
                            'boost_campaign_id' => $campaign->id,
                            'type' => 'charge',
                            'amount' => $paymentIntent->amount / 100,
                            'meta_spend' => 0,
                            'markup_amount' => 0,
                            'stripe_payment_intent_id' => $paymentIntentId,
                            'status' => 'failed',
                            'notes' => 'Amount mismatch - expected: '.($expectedCents / 100).', actual: '.($paymentIntent->amount / 100),
                        ]);
                    });

                    return false;
                }

                DB::transaction(function () use ($campaign, $paymentIntentId) {
                    $campaign->update([
                        'total_charged' => $campaign->getTotalCost(),
                        'billing_status' => 'charged',
                    ]);

                    // Create billing record for the charge.
                    //
                    // The two columns mean "money that left for an external ad platform" and
                    // "what the operator kept". For Meta that is budget/markup. A network
                    // promotion buys inventory on this instance, so nothing leaves and the whole
                    // charge is revenue - and markup_rate is forced to 0 for that channel, so
                    // getMarkupAmount() would report every card-funded network sale as zero
                    // revenue and the full budget as external spend. The credit branch in
                    // PromotionController already splits it this way; this is the card half.
                    $isNetwork = $campaign->isNetwork();

                    BoostBillingRecord::create([
                        'boost_campaign_id' => $campaign->id,
                        'type' => 'charge',
                        'amount' => $campaign->getTotalCost(),
                        'meta_spend' => $isNetwork ? 0 : $campaign->user_budget,
                        'markup_amount' => $isNetwork ? $campaign->getTotalCost() : $campaign->getMarkupAmount(),
                        'stripe_payment_intent_id' => $paymentIntentId,
                        'status' => 'completed',
                    ]);
                });

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Boost Billing: Failed to confirm payment', [
                'campaign_id' => $campaign->id,
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Issue a full refund (for rejected/failed campaigns before any spend)
     */
    public function refundFull(BoostCampaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);

            if (! $campaign->stripe_payment_intent_id) {
                return false;
            }

            if (in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
                return false;
            }

            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));

                $refund = $stripe->refunds->create([
                    'payment_intent' => $campaign->stripe_payment_intent_id,
                    'metadata' => [
                        'boost_campaign_id' => $campaign->id,
                        'reason' => 'full_refund',
                    ],
                ], [
                    'idempotency_key' => "boost_refund_full_{$campaign->id}",
                ]);

                $refundAmount = $campaign->total_charged ?? $campaign->getTotalCost();

                BoostBillingRecord::create([
                    'boost_campaign_id' => $campaign->id,
                    'type' => 'refund',
                    'amount' => $refundAmount,
                    'meta_spend' => 0,
                    'markup_amount' => 0,
                    'stripe_refund_id' => $refund->id,
                    'status' => 'completed',
                    'notes' => 'Full refund - campaign rejected or failed before spend',
                ]);

                $updateData = ['billing_status' => 'refunded'];
                if ($campaign->total_charged !== null) {
                    $updateData['total_charged'] = $refundAmount;
                }
                $campaign->update($updateData);

                return true;
            } catch (\Exception $e) {
                Log::error('Boost Billing: Failed to issue full refund', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);

                return false;
            }
        });
    }

    /**
     * Cancel a payment intent that was never confirmed/charged
     */
    public function cancelPaymentIntent(BoostCampaign $campaign): bool
    {
        if (! $campaign->stripe_payment_intent_id) {
            return false;
        }

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));
            $paymentIntent = $stripe->paymentIntents->retrieve($campaign->stripe_payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                return $this->refundFull($campaign);
            }

            if (in_array($paymentIntent->status, ['requires_payment_method', 'requires_confirmation', 'requires_action', 'processing'])) {
                $stripe->paymentIntents->cancel($campaign->stripe_payment_intent_id);
            }

            $campaign->update(['billing_status' => 'cancelled']);

            return true;
        } catch (\Exception $e) {
            Log::error('Boost Billing: Failed to cancel payment intent', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Refund unspent budget on campaign completion
     */
    public function refundUnspent(BoostCampaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);

            if (! $campaign->stripe_payment_intent_id || ! $campaign->total_charged) {
                return false;
            }

            if (in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
                return false;
            }

            $actualSpend = $campaign->actual_spend ?? 0;
            $unspentBudget = $campaign->user_budget - $actualSpend;

            if ($unspentBudget <= 0) {
                // Fully delivered: nothing to give back, but the campaign is SETTLED, and
                // saying so is what stops it being reconciled again. Both completion queries -
                // SyncPromotions::completeFinishedCampaigns() and
                // SyncBoostCampaigns::checkCompletedCampaigns() - select on
                // `billing_status = 'charged'` with a 24-hour age, and the reconcile job itself
                // touches updated_at, so leaving the status alone re-queued the campaign every
                // day and re-sent its completion email forever. refundCreditRemainder() already
                // handles this case the same way; this is the card-funded half.
                $campaign->update(['billing_status' => 'refunded']);

                return false;
            }

            $refundAmount = self::unspentRefundAmount($campaign);
            $refundAmountCents = self::toCents($refundAmount);

            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));

                $refund = $stripe->refunds->create([
                    'payment_intent' => $campaign->stripe_payment_intent_id,
                    'amount' => $refundAmountCents,
                    'metadata' => [
                        'boost_campaign_id' => $campaign->id,
                        'reason' => 'unspent_budget',
                        'actual_spend' => $actualSpend,
                    ],
                ], [
                    'idempotency_key' => "boost_refund_unspent_{$campaign->id}",
                ]);

                BoostBillingRecord::create([
                    'boost_campaign_id' => $campaign->id,
                    'type' => 'refund',
                    'amount' => $refundAmount,
                    'meta_spend' => $actualSpend,
                    'markup_amount' => round($unspentBudget * $campaign->markup_rate, 2),
                    'stripe_refund_id' => $refund->id,
                    'status' => 'completed',
                    'notes' => "Partial refund - unspent budget: {$campaign->getCurrencySymbol()}{$unspentBudget}",
                ]);

                $campaign->update([
                    'billing_status' => 'partially_refunded',
                ]);

                return true;
            } catch (\Exception $e) {
                Log::error('Boost Billing: Failed to refund unspent budget', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);

                return false;
            }
        });
    }

    /**
     * The currency to LABEL a markup-revenue aggregate with.
     *
     * markup_amount is written by BOTH rails: a Meta boost bills in META_DEFAULT_CURRENCY, a
     * network promotion in PROMOTIONS_CURRENCY. The admin tiles used to format with the Meta
     * config alone, so they named the wrong money on any install selling promotions - and on a
     * selfhost, where BoostController forces the Meta markup rate to 0, every non-zero markup is
     * network revenue and the tile still printed it in dollars.
     *
     * Reads the code off the campaigns themselves, the way AnalyticsService::getBoostStats()
     * already does, and takes the one carrying the most markup rather than whichever campaign is
     * newest - the newest would make a fixed historical total change its label every time a
     * campaign in the other currency was created.
     *
     * Falls back to the platform currency, not USD: with no campaigns at all the tile reads zero,
     * and zero should print in the currency the rest of the admin panel quotes.
     */
    public static function markupCurrency($startDate = null, $endDate = null): string
    {
        $query = BoostBillingRecord::query()
            ->join('boost_campaigns', 'boost_campaigns.id', '=', 'boost_billing_records.boost_campaign_id')
            ->where('boost_billing_records.type', 'charge')
            ->where('boost_billing_records.status', 'completed')
            ->whereNotNull('boost_campaigns.currency_code')
            ->where('boost_campaigns.currency_code', '!=', '');

        if ($startDate && $endDate) {
            $query->whereBetween('boost_billing_records.created_at', [$startDate, $endDate]);
        }

        // Group and order by the qualified column, never a select alias - MySQL binds a bare
        // alias back to the column and fails with 1055 under ONLY_FULL_GROUP_BY.
        $code = $query->groupBy('boost_campaigns.currency_code')
            ->orderByRaw('SUM(boost_billing_records.markup_amount) DESC')
            ->value('boost_campaigns.currency_code');

        return $code ?: PlatformCurrency::code();
    }
}
