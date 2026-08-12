<?php

namespace App\Http\Controllers;

use App\Models\SaleInstallmentPlan;
use App\Utils\HoneypotUtils;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Stripe\StripeClient;

/**
 * The buyer's own payment-plan page, and the three things they can do from it: pay the next
 * installment, clear the balance, or replace the card.
 *
 * Reached from every plan email and from the ticket page. Authentication is the plan's own
 * `secret`, which is deliberately NOT the sale's: a forwarded ticket link must not let somebody
 * pay - or see - another buyer's schedule.
 */
class InstallmentController extends Controller
{
    /**
     * Resolve a plan from its encoded id plus secret, in constant time.
     */
    private function resolvePlan(string $planId, string $secret): SaleInstallmentPlan
    {
        $plan = SaleInstallmentPlan::with(['installments', 'sale.event.creatorRole'])
            ->find(UrlUtils::decodeId($planId));

        abort_if(! $plan || ! hash_equals($plan->secret, $secret), 404);

        return $plan;
    }

    public function view(string $planId, string $secret)
    {
        $plan = $this->resolvePlan($planId, $secret);

        return view('installment.pay', [
            'plan' => $plan,
            'sale' => $plan->sale,
            'event' => $plan->sale?->event,
            'role' => $plan->sale?->event?->creatorRole,
        ]);
    }

    /**
     * Start a Stripe session for this plan.
     *
     * Which installment gets charged is decided HERE, from the plan, never from anything the
     * client sent. Accepting an installment id would turn the plan secret into an oracle for
     * arbitrary rows.
     */
    public function pay(Request $request, string $planId, string $secret)
    {
        if (HoneypotUtils::isTripped($request)) {
            return back()->with('error', __('messages.invalid_request'));
        }

        $plan = $this->resolvePlan($planId, $secret);

        if (in_array($plan->status, ['cancelled', 'completed'], true)) {
            return back()->with('error', __('messages.installments_not_offered'));
        }

        $mode = $request->input('mode', 'next');
        $event = $plan->sale?->event;
        $owner = $event?->user;

        if (! $event || ! $owner) {
            return back()->with('error', __('messages.error'));
        }

        // The plan SNAPSHOTS the connected account at creation (InstallmentService::createPlan)
        // while this page resolves it LIVE, and the two diverge permanently once an organizer
        // unlinks and reconnects: StripeController::unlink() nulls the account and connect() then
        // mints a brand-new acct_. Both halves of that divergence have to be handled here, before
        // any session is created, or the buyer pays and nothing credits them.
        $live = $owner->stripe_account_id;

        // Unlinked and not reconnected. Falling through would build the session on the PLATFORM
        // secret, so the money would land in our account rather than the organizer's - and
        // handleInstallmentPayment() would then refuse it as a platform key on a Connect plan,
        // leaving the buyer charged and uncredited. Refusing outright is the only safe answer.
        if (config('app.hosted') && ! $live) {
            return back()->with('error', __('messages.installments_requires_stripe'));
        }

        // Reconnected under a new account. The stored Customer and PaymentMethod live on the OLD
        // account, so they can be neither charged nor retrieved - and leaving the snapshot stale
        // makes the webhook's account guard reject this very payment. Re-snapshot and drop the
        // dead card; the session below carries setup_future_usage, so paying re-establishes one on
        // the account that will actually be charged.
        if ($live && $plan->stripe_account_id && $live !== $plan->stripe_account_id) {
            \Log::warning('Installment plan re-pointed at a new connected account', [
                'plan_id' => $plan->id,
                'old_account' => $plan->stripe_account_id,
                'new_account' => $live,
            ]);

            $plan->forceFill([
                'stripe_account_id' => $live,
                'stripe_customer_id' => null,
                'stripe_payment_method_id' => null,
                'card_brand' => null,
                'card_last4' => null,
                'card_exp_month' => null,
                'card_exp_year' => null,
            ])->save();
        }

        $useConnect = config('app.hosted') && $live;
        $stripe = new StripeClient($useConnect ? config('services.stripe.key') : config('services.stripe_platform.secret'));
        $options = $useConnect ? ['stripe_account' => $live] : [];

        $returnData = [
            'plan_id' => UrlUtils::encodeId($plan->id),
            'secret' => $plan->secret,
        ];

        try {
            if ($mode === 'update_card') {
                // mode: 'setup' stores a reusable payment method without taking money. Nothing in
                // the repo did this for a connected account before - the only other SetupIntent is
                // Cashier's, on the platform account, for the schedule's own subscription.
                $session = $stripe->checkout->sessions->create([
                    'mode' => 'setup',
                    'customer' => $plan->stripe_customer_id ?: null,
                    'customer_email' => $plan->stripe_customer_id ? null : $plan->sale->email,
                    'metadata' => [
                        'installment_plan_card_update' => UrlUtils::encodeId($plan->id),
                    ],
                    'success_url' => route('installment.view', $returnData).'?updated=1',
                    'cancel_url' => route('installment.view', $returnData),
                ], $options);

                return redirect($session->url);
            }

            $targets = $mode === 'payoff'
                ? $plan->installments->whereNotIn('status', ['paid', 'cancelled'])
                : collect([$plan->nextDueInstallment()])->filter();

            if ($targets->isEmpty()) {
                return back()->with('error', __('messages.installments_not_offered'));
            }

            // The cron has this row at Stripe right now. Letting the buyer open a second session
            // for it is how one installment gets paid twice, and nothing in this app can refund a
            // Connect sale afterwards.
            if ($targets->contains(fn ($i) => $i->status === 'processing')) {
                return back()->with('error', __('messages.installment_payment_in_progress'));
            }

            $amount = (float) $targets->sum(fn ($i) => (float) $i->amount);
            $multiplier = MoneyUtils::getSmallestUnitMultiplier($plan->currency);

            $metadata = $mode === 'payoff'
                // One PaymentIntent settling N rows - the single place the one-charge-per-
                // installment assumption breaks, so the webhook needs its own branch for it.
                //
                // The target ids are PINNED here, at the price the buyer is about to agree to. If
                // a scheduled charge settles while they are on Stripe's page the outstanding set
                // shrinks underneath them, and re-deriving it at settlement time meant the amount
                // no longer matched - which used to mark every remaining row `failed` on a plan
                // the buyer had just paid in full.
                ? [
                    'installment_plan_payoff' => UrlUtils::encodeId($plan->id),
                    'installment_ids' => $targets->map(fn ($i) => UrlUtils::encodeId($i->id))->implode(','),
                ]
                : ['installment_id' => UrlUtils::encodeId($targets->first()->id)];

            $metadata['sale_id'] = UrlUtils::encodeId($plan->sale_id);

            $session = $stripe->checkout->sessions->create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($plan->currency),
                        'product_data' => ['name' => $event->name.' - '.__('messages.payment_plan')],
                        'unit_amount' => (int) round($amount * $multiplier),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'customer' => $plan->stripe_customer_id ?: null,
                'customer_email' => $plan->stripe_customer_id ? null : $plan->sale->email,
                'metadata' => $metadata,
                'payment_intent_data' => [
                    // Re-save on every manual payment: whatever card they use here becomes the
                    // one future installments are charged to, which is what makes this double as
                    // the fix for a dead card.
                    'setup_future_usage' => 'off_session',
                    'metadata' => $metadata,
                ],
                'success_url' => route('installment.view', $returnData).'?paid=1',
                'cancel_url' => route('installment.view', $returnData),
            ], $options);

            return redirect($session->url);
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', __('messages.error'));
        }
    }
}
