<?php

namespace App\Http\Controllers;

use App\Models\SaleInstallment;
use App\Models\SaleInstallmentPlan;
use App\Services\InstallmentService;
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

    public function view(Request $request, string $planId, string $secret)
    {
        $plan = $this->resolvePlan($planId, $secret);

        $cardStored = $this->applyReturnedSession($request, $plan);

        if ($cardStored) {
            // applyCardUpdate() requeues parked rows with a mass update, so the collection loaded
            // by resolvePlan() is stale by now.
            $plan->load('installments');
        }

        return view('installment.pay', [
            'plan' => $plan,
            'sale' => $plan->sale,
            'event' => $plan->sale?->event,
            'role' => $plan->sale?->event?->creatorRole,
            'cardStored' => $cardStored,
        ]);
    }

    /**
     * Apply what the buyer just did on Stripe, read from the session they were redirected back on.
     *
     * The webhook is the other half of this and remains the backstop, but it cannot be the only
     * path. A `mode: 'setup'` session emits ONLY `checkout.session.completed`, and our own Connect
     * setup docs tell operators to subscribe to `payment_intent.succeeded` alone - so on a
     * doc-following install the card swap never reached the app at all, while this page cheerfully
     * told the buyer it had. The cron then went on declining the dead card until the plan went
     * delinquent and the ticket was refused at the door, which is the exact outcome "replace your
     * card" exists to prevent.
     *
     * Deliberately captures the CARD only and never settles money: settlement carries webhook
     * authentication this page has no equivalent for, and a redirect is something a buyer can
     * replay. Both halves are idempotent, so whichever arrives first wins.
     *
     * Returns true when a card was actually stored, which is what the success banner is gated on.
     */
    private function applyReturnedSession(Request $request, SaleInstallmentPlan $plan): bool
    {
        $sessionId = $request->query('session_id');

        if (! is_string($sessionId) || $sessionId === '') {
            return false;
        }

        $installments = app(InstallmentService::class);
        $session = $installments->retrieveCheckoutSession($plan, $sessionId);

        if (! $session) {
            return false;
        }

        // The session has to name THIS plan. The URL secret opens one plan; without this check it
        // would also let whoever holds it apply a session belonging to somebody else's.
        if (! $this->sessionBelongsToPlan($session, $plan)) {
            \Log::warning('Installment return session does not belong to this plan', [
                'plan_id' => $plan->id,
                'session' => $sessionId,
            ]);

            return false;
        }

        if (isset($session->metadata->installment_plan_card_update)) {
            return $session->setup_intent
                ? $installments->applyCardUpdate($plan, $session->setup_intent)
                : false;
        }

        // A manual payment or payoff. Its session also carries setup_future_usage, so the card it
        // leaves behind is the one future installments are charged to - and on an install
        // subscribed only to checkout.session.completed nothing else would ever record it.
        // Same call TicketController::success() makes, including its no-op when a card is already
        // stored, so a buyer reloading this page costs nothing.
        return $installments->captureFromSession($plan, $session);
    }

    /**
     * Whether a returned Checkout Session was created for this plan.
     *
     * Checked against the metadata key that actually identifies it rather than the sale_id every
     * session carries, so this stays correct if a sale ever grows a second plan.
     */
    private function sessionBelongsToPlan($session, SaleInstallmentPlan $plan): bool
    {
        foreach (['installment_plan_card_update', 'installment_plan_payoff'] as $key) {
            if (isset($session->metadata->{$key})) {
                return UrlUtils::decodeId($session->metadata->{$key}) == $plan->id;
            }
        }

        if (isset($session->metadata->installment_id)) {
            return SaleInstallment::whereKey(UrlUtils::decodeId($session->metadata->installment_id))
                ->where('sale_installment_plan_id', $plan->id)
                ->exists();
        }

        return false;
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

        // Handed back so view() can apply the outcome itself instead of trusting a webhook that
        // may not be subscribed - see applyReturnedSession(). Appended raw because Stripe
        // substitutes the placeholder, and safe to append with '&' because both URLs below already
        // open their query string: route() puts plan_id and secret in the PATH.
        $returnSession = '&session_id={CHECKOUT_SESSION_ID}';

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
                    'success_url' => route('installment.view', $returnData).'?updated=1'.$returnSession,
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
                'success_url' => route('installment.view', $returnData).'?paid=1'.$returnSession,
                'cancel_url' => route('installment.view', $returnData),
            ], $options);

            return redirect($session->url);
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', __('messages.error'));
        }
    }
}
