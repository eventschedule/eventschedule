<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\SaleInstallmentPlan;
use App\Utils\MoneyUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InstallmentService
{
    /** Stripe refuses charges below roughly 50 smallest currency units. */
    public const STRIPE_MIN_UNITS = 50;

    public const MIN_COUNT = 2;

    public const MAX_COUNT = 12;

    /** Enforced floor on events.installment_final_days_before. */
    public const MIN_FINAL_DAYS_BEFORE = 7;

    /**
     * Split a total into N monthly payments.
     *
     * All arithmetic happens in the smallest currency unit, so the parts always re-sum to exactly
     * the total - a float division of 1000/3 does not. Any remainder lands on installment 1,
     * which is the one charged on the spot and therefore the only one the buyer sees and agrees
     * to before committing; hiding the odd cent in a payment three months out would mean the
     * schedule we showed them was wrong.
     *
     * @return array<int, array{sequence:int, amount:float, due_at:Carbon}>
     */
    public function buildSchedule(float $total, int $count, string $currency, ?Carbon $firstDueAt = null): array
    {
        $multiplier = MoneyUtils::getSmallestUnitMultiplier($currency);
        $totalUnits = (int) round($total * $multiplier);

        $base = intdiv($totalUnits, $count);
        $remainder = $totalUnits - ($base * $count);

        $start = ($firstDueAt ?? Carbon::now())->copy();
        $schedule = [];

        for ($i = 0; $i < $count; $i++) {
            $units = $base + ($i === 0 ? $remainder : 0);

            $schedule[] = [
                'sequence' => $i + 1,
                // Cast explicitly: for a zero-decimal currency the multiplier is 1, so the
                // division yields an int and the return type would otherwise change shape with
                // the currency. Downstream strict comparisons and JSON payloads both care.
                'amount' => (float) ($units / $multiplier),
                'due_at' => $this->addMonthsClamped($start, $i),
            ];
        }

        return $schedule;
    }

    /**
     * Carbon's addMonths() overflows: 31 January plus one month is 3 March, so a plan bought on
     * the 31st would skip February entirely and bunch two payments into March. addMonthsNoOverflow
     * clamps to the last day of the target month instead, which is what every card issuer does.
     */
    private function addMonthsClamped(Carbon $start, int $months): Carbon
    {
        return $start->copy()->addMonthsNoOverflow($months);
    }

    /**
     * The smallest per-installment amount Stripe will accept, in the currency's own units.
     * Mirrors the existing guard in TicketController::priceSaleLeg().
     */
    public function minimumChargeAmount(string $currency): float
    {
        return self::STRIPE_MIN_UNITS / MoneyUtils::getSmallestUnitMultiplier($currency);
    }

    /**
     * Whether an order can be split at all. Every caller re-checks this server-side; the guest
     * form's copy of the logic is a convenience, never the authority.
     *
     * $occurrenceDate is the date the buyer actually selected. It is NOT interchangeable with
     * $event->starts_at: for a recurring event starts_at is the recurrence anchor and sits in the
     * PAST, so a fit check against it would refuse every recurring event forever.
     */
    public function isEligible(Event $event, float $total, string $currency, ?string $occurrenceDate = null): bool
    {
        return $this->ineligibleReason($event, $total, $currency, $occurrenceDate) === null;
    }

    /**
     * Returns a translation key naming why the order cannot be split, or null when it can.
     * Split out from isEligible() so the guest form can say which condition failed rather than
     * silently dropping the option.
     */
    public function ineligibleReason(Event $event, float $total, string $currency, ?string $occurrenceDate = null): ?string
    {
        if (! $event->installments_enabled || ! $event->installment_count) {
            return 'messages.installments_not_offered';
        }

        // Stripe is the only rail that can charge a saved card without the buyer present.
        if ($event->payment_method !== 'stripe') {
            return 'messages.installments_requires_stripe';
        }

        $role = $event->creatorRole ?? $event->roles->first();
        if (! $role || ! $role->isPro()) {
            return 'messages.installments_not_offered';
        }

        if ($event->installment_min_order_amount && $total < (float) $event->installment_min_order_amount) {
            return 'messages.installments_below_minimum';
        }

        // Every part must clear Stripe's floor, not just the first. A gift card applied to a big
        // order can drag the payable remainder well below it.
        $count = (int) $event->installment_count;
        $schedule = $this->buildSchedule($total, $count, $currency);
        $minCharge = $this->minimumChargeAmount($currency);

        foreach ($schedule as $part) {
            if ($part['amount'] < $minCharge) {
                return 'messages.installments_below_minimum';
            }
        }

        if (! $this->scheduleFitsBeforeEvent($event, $schedule, $occurrenceDate)) {
            return 'messages.installments_does_not_fit';
        }

        return null;
    }

    /**
     * The last payment has to clear with enough runway to chase a decline before the doors open.
     * Without this an event sold close to its own date can have a final charge due after it has
     * already happened.
     */
    public function scheduleFitsBeforeEvent(Event $event, array $schedule, ?string $occurrenceDate = null): bool
    {
        $deadline = $this->collectionDeadline($event, $occurrenceDate);

        if (! $deadline) {
            return false;
        }

        $last = end($schedule);

        return $last && $last['due_at']->lessThanOrEqualTo($deadline);
    }

    /**
     * The moment by which every installment must have been collected: the occurrence start, less
     * the event's configured runway (floored at MIN_FINAL_DAYS_BEFORE).
     */
    public function collectionDeadline(Event $event, ?string $occurrenceDate = null): ?Carbon
    {
        $start = $this->occurrenceStart($event, $occurrenceDate);

        if (! $start) {
            return null;
        }

        $days = max(self::MIN_FINAL_DAYS_BEFORE, (int) $event->installment_final_days_before);

        return $start->copy()->subDays($days);
    }

    /**
     * Resolve the UTC start of the occurrence the buyer picked.
     *
     * Built explicitly rather than via Event::getStartDateTime(), which selects its timezone from
     * auth()->user() when there is one. That is fine on a request but wrong in the cron, where
     * there is no authenticated user and the answer would silently change depending on who
     * happened to trigger it. Mirrors the construction in TicketController::scanned().
     *
     * $occurrenceDate is a calendar date in the SCHEDULE's zone, which is how sales.event_date is
     * stored. Falling back to starts_at is only correct for a non-recurring event; for a
     * recurring one starts_at is the recurrence anchor and sits in the past, which is precisely
     * why the occurrence date has to be threaded through.
     */
    private function occurrenceStart(Event $event, ?string $occurrenceDate = null): ?Carbon
    {
        if (! $event->starts_at) {
            return null;
        }

        $tz = $event->creatorRole?->timezone ?? config('app.timezone');

        $anchor = strlen($event->starts_at) === 10
            ? Carbon::createFromFormat('Y-m-d', $event->starts_at, 'UTC')->startOfDay()
            : Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC');

        if (! $occurrenceDate) {
            return $anchor;
        }

        $timeOfDay = $anchor->copy()->setTimezone($tz)->format('H:i:s');

        return Carbon::createFromFormat('Y-m-d H:i:s', $occurrenceDate.' '.$timeOfDay, $tz)
            ->setTimezone('UTC');
    }

    /**
     * Create the plan and its rows for a sale that is about to go to Stripe.
     *
     * Called AFTER checkout's zero-total branch, never inside the pricing transaction: a gift
     * card can zero an order, in which case the sale is marked paid on the spot and a plan
     * created earlier would sit `active` forever with installments that are never charged.
     */
    public function createPlan(Sale $sale, Event $event, float $total, string $currency, ?string $mandateIp = null, bool $mandateAccepted = false): SaleInstallmentPlan
    {
        $count = (int) $event->installment_count;
        $schedule = $this->buildSchedule($total, $count, $currency);

        return DB::transaction(function () use ($sale, $event, $total, $currency, $count, $schedule, $mandateIp, $mandateAccepted) {
            $plan = SaleInstallmentPlan::create([
                'sale_id' => $sale->id,
                'currency' => $currency,
                'total_amount' => $total,
                'amount_paid' => 0,
                'installment_count' => $count,
                'status' => 'active',
                // Snapshot which connected account the card will live on. If the owner unlinks
                // and relinks Stripe this stops matching, which is how the cron detects that the
                // stored payment method is dead rather than throwing every hour.
                'stripe_account_id' => $event->user?->stripe_account_id,
                // Only stamped when the buyer actually ticked the box. A timestamp asserting
                // consent that was never given is worse than no record at all - it is the one
                // thing we would produce to defend a disputed charge.
                'mandate_accepted_at' => $mandateAccepted ? now() : null,
                'mandate_ip' => $mandateAccepted ? $mandateIp : null,
            ]);

            foreach ($schedule as $part) {
                SaleInstallment::create([
                    'sale_installment_plan_id' => $plan->id,
                    'sequence' => $part['sequence'],
                    'amount' => $part['amount'],
                    'due_at' => $part['due_at'],
                    // Installment 1 is charged at checkout, so it is already in flight.
                    'status' => $part['sequence'] === 1 ? 'processing' : 'scheduled',
                ]);
            }

            return $plan->load('installments');
        });
    }

    /**
     * Record a payment against a plan. The ONE settlement implementation.
     *
     * Shared by the Stripe webhook and by app:charge-installments, which settles in-process from
     * the PaymentIntent that `confirm: true` already returned. Before this was shared, the webhook
     * was the only thing in the codebase that could mark an installment paid - and both webhook
     * secrets are optional env vars, so on any install without one configured the cron charged
     * cards and the app recorded nothing, forever.
     *
     * The caller owns authentication (the webhook's account and currency guards); this owns the
     * money. Returns an outcome string so callers can log and notify appropriately:
     * `settled`, `already_settled`, `dead_plan`, `nothing_due`, `amount_mismatch`, `duplicate`.
     *
     * @param  array|null  $payoffIds  Installment ids pinned at session-creation time for a payoff.
     */
    public function settle(
        SaleInstallmentPlan $plan,
        ?SaleInstallment $installment,
        bool $isPayoff,
        float $paidAmount,
        ?string $reference,
        $paymentIntentObject = null,
        ?array $payoffIds = null,
    ): string {
        $outcome = 'settled';
        $saleBecamePaid = false;
        $paidLegs = [];

        // The PaymentMethod the plan's stored card fields already describe, read before the
        // capture below can overwrite it. See captureCardDisplay().
        $describedMethod = null;

        // Lock order is PLAN -> INSTALLMENT -> SALE. Every teardown path takes the Sale lock first
        // and then the plan (refundSale -> Sale::booted -> cancelPlan), so those two orders are an
        // ABBA inversion. DB::transaction defaults to a single attempt, so neither side retries a
        // deadlock. The Sale row is therefore taken with a non-blocking read first and only locked
        // when it actually needs writing, which removes the cycle in the common case.
        DB::transaction(function () use (
            $plan, $installment, $isPayoff, $paidAmount, $reference, $paymentIntentObject,
            $payoffIds, &$outcome, &$saleBecamePaid, &$paidLegs, &$describedMethod
        ) {
            $lockedPlan = SaleInstallmentPlan::lockForUpdate()->find($plan->id);

            if (! $lockedPlan || in_array($lockedPlan->status, ['cancelled', 'completed'], true)) {
                // The money is already at Stripe. Record it against the plan so it shows up for
                // reconciliation instead of vanishing into a log line - this is reachable any time
                // an organizer refunds or cancels while a Checkout Session is open.
                $this->recordUnmatchedPayment($lockedPlan ?? $plan, $paidAmount, $reference);
                $outcome = 'dead_plan';

                return;
            }

            // The card the rest of this plan will be charged against, stored FIRST, before any of
            // the early returns below can skip it.
            //
            // This is the whole feature's single point of failure: nothing else writes these two
            // columns from a purchase, and without them chargeDue() skips the plan and both
            // reminder sweeps filter it out - so the organizer collects installment 1 and nothing
            // else, and nobody is told. It has to be up here because
            // checkout.session.completed and payment_intent.succeeded both report the same
            // payment: whichever lands second finds the row already settled, and settling from
            // the one that carries no PaymentIntent used to mean the card was never recorded at
            // all. Deliberately not below with the rest of the write - the comment there claimed
            // this ordering while the code did the opposite.
            //
            // Ids only: this holds a row lock, so the PaymentMethod lookup that fills in brand and
            // last4 waits until after the commit.
            if ($paymentIntentObject) {
                $describedMethod = $lockedPlan->stripe_payment_method_id;

                $this->captureCardIds($lockedPlan, $paymentIntentObject);

                if ($lockedPlan->isDirty()) {
                    $lockedPlan->save();
                }
            }

            $targets = $this->settlementTargets($lockedPlan, $installment, $isPayoff, $payoffIds);

            if ($targets->isEmpty()) {
                $outcome = 'nothing_due';

                return;
            }

            // A row that is already paid means either a replayed webhook (same reference - fine,
            // do nothing) or a genuinely SECOND payment for the same installment (different
            // reference - the buyer has been charged twice and nothing recorded it). The old code
            // could not tell the two apart and returned silently for both.
            $alreadyPaid = $targets->filter(fn ($i) => $i->status === 'paid');

            if ($alreadyPaid->isNotEmpty()) {
                $sameReference = $alreadyPaid->every(fn ($i) => $i->transaction_reference === $reference);

                if ($sameReference) {
                    $outcome = 'already_settled';

                    return;
                }

                $this->recordUnmatchedPayment($lockedPlan, $paidAmount, $reference);
                $outcome = 'duplicate';

                return;
            }

            $expected = round((float) $targets->sum(fn ($i) => (float) $i->amount), 2);

            // UNDERPAYMENT is refused: the rows stay unsettled and are flagged for review.
            //
            // OVERPAYMENT is handled separately below rather than refused, because the payoff
            // amount is fixed when the session opens: if a scheduled charge settles while the
            // buyer is on Stripe's page, the outstanding set shrinks underneath them. Marking
            // those rows `failed` (the old behaviour) left a buyer who had paid credited nothing,
            // on a plan that could then never complete.
            if ($paidAmount + 0.01 < $expected) {
                foreach ($targets as $target) {
                    $target->status = 'failed';
                    $target->last_error = 'amount_mismatch';
                    $target->transaction_reference = $reference;
                    $target->save();
                }

                \Log::error('Installment payment short of the amount due - NOT credited', [
                    'plan_id' => $lockedPlan->id,
                    'expected_amount' => $expected,
                    'paid_amount' => $paidAmount,
                    'reference' => $reference,
                ]);

                $outcome = 'amount_mismatch';

                return;
            }

            foreach ($targets as $target) {
                $target->status = 'paid';
                $target->paid_at = now();
                $target->transaction_reference = $reference;
                $target->next_attempt_at = null;
                $target->last_error = null;
                $target->save();
            }

            // Credit exactly what was owed, never what happened to arrive. Any surplus is booked
            // as unmatched so it surfaces for reconciliation instead of silently inflating the
            // balance and completing a plan that was not actually paid off.
            $lockedPlan->amount_paid = round((float) $lockedPlan->amount_paid + $expected, 3);

            $surplus = round($paidAmount - $expected, 3);
            if ($surplus > 0.01) {
                $lockedPlan->unmatched_amount = round((float) $lockedPlan->unmatched_amount + $surplus, 3);

                \Log::warning('Installment payment exceeded the amount due - surplus recorded', [
                    'plan_id' => $lockedPlan->id,
                    'expected_amount' => $expected,
                    'paid_amount' => $paidAmount,
                    'surplus' => $surplus,
                    'reference' => $reference,
                ]);
            }

            $stillOwed = SaleInstallment::where('sale_installment_plan_id', $lockedPlan->id)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->exists();

            if (! $stillOwed) {
                $lockedPlan->status = 'completed';
            } elseif ($lockedPlan->status === 'delinquent') {
                $lockedPlan->status = 'active';
                $lockedPlan->delinquent_at = null;
            }

            $lockedPlan->save();

            $sale = Sale::find($lockedPlan->sale_id);

            if ($sale && $sale->status === 'unpaid') {
                $locked = Sale::lockForUpdate()->find($sale->id);

                if ($locked && $locked->status === 'unpaid') {
                    $locked->status = 'paid';
                    $locked->transaction_reference = $reference;
                    $locked->save();
                    $saleBecamePaid = true;
                    $paidLegs = $locked->orderLegs()->all();
                }
            }

            \App\Services\AuditService::log(
                \App\Services\AuditService::SALE_INSTALLMENT_PAID, null, 'Sale', $lockedPlan->sale_id,
                null, ['amount' => $paidAmount],
                'installment:event_id:'.($sale?->event_id ?? 0)
            );

            UsageTrackingService::track(UsageTrackingService::STRIPE_PAYMENT);
        });

        // Outside the transaction on purpose: this is the one part of the capture that talks to
        // Stripe, and the plan row was locked in there. A replayed webhook returns early inside it
        // without an API call; a dead plan is skipped outright, since nothing will ever charge it
        // again and the card would only be described to nobody.
        if ($paymentIntentObject && $outcome !== 'dead_plan') {
            $this->captureCardDisplay($plan->fresh(), $describedMethod);
        }

        if ($saleBecamePaid) {
            $this->onSaleBecamePaid($plan->fresh(), $paidLegs);
        }

        // Advertised in Webhook::EVENT_TYPES, the developer docs and the marketing copy that now
        // says "fourteen event types" - so they have to actually fire. Dispatched with the SALE,
        // because WebhookService resolves only Sale and Event and silently returns for anything
        // else; the installment detail rides along in $extraData.
        if ($sale = $plan->sale) {
            $event = in_array($outcome, ['settled', 'already_settled'], true)
                ? 'installment.paid'
                : 'installment.failed';

            if ($outcome !== 'already_settled') {
                WebhookService::dispatch($event, $sale, null, [
                    'installment' => [
                        'sequence' => $installment?->sequence,
                        'amount' => $paidAmount,
                        'outcome' => $outcome,
                        'reference' => $reference,
                        'plan' => $plan->fresh()->toSummaryData(),
                    ],
                ]);
            }
        }

        return $outcome;
    }

    /**
     * Which rows this payment settles.
     *
     * For a payoff the ids are pinned when the session is created, so a schedule that shifts while
     * the buyer is on Stripe's page cannot silently change what they are paying for.
     */
    private function settlementTargets(SaleInstallmentPlan $plan, ?SaleInstallment $installment, bool $isPayoff, ?array $payoffIds)
    {
        if (! $isPayoff) {
            return collect([SaleInstallment::lockForUpdate()->find($installment->id)])->filter();
        }

        $query = SaleInstallment::where('sale_installment_plan_id', $plan->id)
            ->whereNotIn('status', ['cancelled']);

        if ($payoffIds) {
            $query->whereIn('id', $payoffIds);
        } else {
            $query->whereNotIn('status', ['paid']);
        }

        return $query->orderBy('sequence')->lockForUpdate()->get();
    }

    /**
     * Money that arrived but belongs to nothing chargeable: a dead plan, or a second payment for a
     * row already settled by a different PaymentIntent.
     *
     * Nothing in this app refunds a Connect ticket sale, so the organizer has to act on it in
     * their own Stripe dashboard. Recording it on the plan gives them somewhere to see it; a log
     * line does not.
     */
    private function recordUnmatchedPayment(SaleInstallmentPlan $plan, float $amount, ?string $reference): void
    {
        \Log::error('Installment payment could not be matched - needs manual reconciliation', [
            'plan_id' => $plan->id,
            'amount' => $amount,
            'reference' => $reference,
            'plan_status' => $plan->status,
        ]);

        \App\Services\AuditService::log(
            \App\Services\AuditService::SALE_INSTALLMENT_FAILED, null, 'Sale', $plan->sale_id,
            null, ['unmatched_amount' => $amount, 'reference' => $reference],
            'installment:event_id:'.($plan->sale?->event_id ?? 0)
        );

        SaleInstallmentPlan::whereKey($plan->id)->update(['unmatched_amount' => DB::raw('COALESCE(unmatched_amount, 0) + '.(float) $amount)]);
    }

    /**
     * Everything the ordinary paid-sale path does, which the installment branch used to skip
     * because it `break`s before the webhook's own sale block: analytics revenue, the `sale.paid`
     * webhook (silently broken for existing Pro subscribers otherwise) and the buyer's emails.
     */
    private function onSaleBecamePaid(?SaleInstallmentPlan $plan, array $legs): void
    {
        $sale = $plan?->sale;

        if (! $sale) {
            return;
        }

        foreach ($legs as $leg) {
            if ($leg->status !== 'paid') {
                continue;
            }

            $legTotal = $leg->legTotalPayment();

            \App\Models\AnalyticsEventsDaily::incrementSale($leg->event_id, $legTotal);

            $legPromo = $leg->legTotalDiscount();
            if ($legPromo > 0) {
                \App\Models\AnalyticsEventsDaily::incrementPromoSale($leg->event_id, $legPromo);
            }

            // The ordinary paid path reports this from StripeController; the installment branch
            // breaks out before that block, so without this an event running a boost campaign
            // recorded no conversion at all for a payment-plan purchase and Meta optimised against
            // a sale it never saw. Same leg total the analytics credit above uses.
            app()->make(\App\Services\MetaAdsService::class)->sendSaleConversion($leg, $legTotal);

            WebhookService::dispatch('sale.paid', $leg);
            foreach ($leg->guestSales()->get() as $guestSale) {
                WebhookService::dispatch('sale.paid', $guestSale);
            }
        }

        (new EmailService)->sendSaleConfirmationEmails($sale);
    }

    /**
     * Store the Customer and PaymentMethod this plan will be charged against.
     *
     * The money-critical half of the capture, and deliberately network-free: it is called from
     * inside settle()'s transaction while the plan row is locked, and holding a row lock across a
     * Stripe round trip would serialise every settlement behind the slowest one. The cosmetic half
     * - brand, last4, expiry - is captureCardDisplay(), which runs after the commit.
     *
     * Mutates the model without saving; the caller owns the write.
     */
    private function captureCardIds(SaleInstallmentPlan $plan, $paymentIntent): void
    {
        if (isset($paymentIntent->customer) && is_string($paymentIntent->customer)) {
            $plan->stripe_customer_id = $paymentIntent->customer;
        }

        $method = $paymentIntent->payment_method ?? null;

        // Expanded rather than left as an id. Nothing here expands it today, but a Session or
        // PaymentIntent retrieved with `expand` arrives this way and would otherwise be dropped.
        if (is_object($method)) {
            $method = $method->id ?? null;
        }

        if (is_string($method) && $method !== '') {
            $plan->stripe_payment_method_id = $method;
        }
    }

    /**
     * Fill in the card's brand / last4 / expiry from the stored PaymentMethod.
     *
     * The card details live on the PaymentMethod, NOT on the PaymentIntent. This used to read
     * `$paymentIntent->charges->data[0]->payment_method_details->card`, but `charges` was removed
     * from PaymentIntent in the 2022-11-15 API version and stripe-php v16 does not declare it (it
     * carries `latest_charge`), while `payment_method_details` is a Charge field and never a
     * PaymentIntent one. So that chain was always null and these four columns were never written
     * on any rail - which silently disabled cardExpiresBeforeFinalPayment(), the one future
     * failure this feature can actually see coming, and left every email saying "your card"
     * rather than naming it.
     *
     * $describedMethod is the PaymentMethod the stored card fields already describe. Passing it is
     * what keeps a replayed webhook free of an API call while still re-reading the card when
     * setup_future_usage has repointed the plan at a different one.
     *
     * Best effort, and always after the money is settled: the charge has already succeeded, so a
     * failure here must never unwind it. Saves itself, because its caller's transaction has
     * committed by the time it runs.
     */
    public function captureCardDisplay(?SaleInstallmentPlan $plan, ?string $describedMethod = null): void
    {
        if (! $plan || ! $plan->stripe_payment_method_id) {
            return;
        }

        if ($plan->card_last4 && $describedMethod === $plan->stripe_payment_method_id) {
            return;
        }

        $card = $this->retrievePaymentMethod($plan, $plan->stripe_payment_method_id)?->card;

        if (! $card) {
            return;
        }

        $plan->forceFill([
            'card_brand' => $card->brand ?? $plan->card_brand,
            'card_last4' => $card->last4 ?? $plan->card_last4,
            'card_exp_month' => $card->exp_month ?? $plan->card_exp_month,
            'card_exp_year' => $card->exp_year ?? $plan->card_exp_year,
        ])->save();
    }

    /**
     * Record a card from a PaymentIntent and persist it, ids and display fields together.
     *
     * The entry point for callers OUTSIDE settle() - TicketController::success(), which is the
     * backstop that makes the capture work whatever a given install's webhook endpoint happens to
     * be subscribed to. Returns true when the stored card actually changed.
     */
    public function captureFrom(SaleInstallmentPlan $plan, $paymentIntent): bool
    {
        $describedMethod = $plan->stripe_payment_method_id;

        $this->captureCardIds($plan, $paymentIntent);

        // Scoped to the two columns this actually sets. A bare isDirty() would report "a card was
        // stored" because a caller happened to have dirtied something unrelated - and that answer
        // is what gates the buyer's success banner.
        $stored = $plan->isDirty(['stripe_customer_id', 'stripe_payment_method_id']);

        if ($stored) {
            $plan->save();
        }

        $this->captureCardDisplay($plan, $describedMethod);

        return $stored;
    }

    /**
     * Capture the card from a Checkout Session the buyer has just completed.
     *
     * The webhook-free backstop, called from TicketController::success() on the redirect. It is
     * what makes the capture work whatever a given install's Stripe endpoint happens to be
     * subscribed to - and the subscription is the whole problem: our own setup docs point the
     * Connect endpoint at payment_intent.succeeded and the platform endpoint at
     * checkout.session.completed, and only the first of those carries an intent to capture from.
     *
     * Does nothing when the plan already has a card, so a normal purchase costs no API call.
     */
    public function captureFromSession(?SaleInstallmentPlan $plan, $session): bool
    {
        if (! $plan || $plan->stripe_payment_method_id) {
            return false;
        }

        $intent = $session->payment_intent ?? null;

        if (is_object($intent)) {
            return $this->captureFrom($plan, $intent);
        }

        if (! is_string($intent) || $intent === '') {
            return false;
        }

        $intent = $this->retrievePaymentIntent($plan, $intent);

        return $intent ? $this->captureFrom($plan, $intent) : false;
    }

    /**
     * Which Stripe rail this plan settles on, as [client, requestOptions].
     *
     * The single source of truth, so the cron cannot drift from checkout the way it did: it used
     * to hardcode the Connect key and pass `stripe_account` unconditionally, which on a selfhost
     * install (no Connect key, null account) threw an AuthenticationException on every charge and
     * parked the plan as "connect_revoked" - collecting installment 1 and nothing else, while
     * telling the organizer their buyer had to confirm with their bank.
     */
    public function stripeContextFor(SaleInstallmentPlan $plan): array
    {
        $useConnect = config('app.hosted') && $plan->stripe_account_id;

        return [
            new \Stripe\StripeClient($useConnect
                ? config('services.stripe.key')
                : config('services.stripe_platform.secret')),
            $useConnect ? ['stripe_account' => $plan->stripe_account_id] : [],
        ];
    }

    /**
     * Charge one installment off-session. One Stripe call and nothing else.
     *
     * Deliberately its own method: it is the seam the tests replace, so the whole charge-then-
     * settle path can be exercised without a network. Before it existed the command built its own
     * client inline and the cron-to-settlement handoff - where every serious bug in this feature
     * lived - was untestable.
     */
    public function chargeOffSession(SaleInstallmentPlan $plan, SaleInstallment $installment, string $idempotencyKey): \Stripe\PaymentIntent
    {
        [$stripe, $options] = $this->stripeContextFor($plan);

        return $stripe->paymentIntents->create([
            'amount' => (int) round((float) $installment->amount * MoneyUtils::getSmallestUnitMultiplier($plan->currency)),
            'currency' => strtolower($plan->currency),
            'customer' => $plan->stripe_customer_id,
            'payment_method' => $plan->stripe_payment_method_id,
            'off_session' => true,
            'confirm' => true,
            'metadata' => [
                'installment_id' => \App\Utils\UrlUtils::encodeId($installment->id),
                'sale_id' => \App\Utils\UrlUtils::encodeId($plan->sale_id),
            ],
        ], $options + ['idempotency_key' => $idempotencyKey]);
    }

    /*
     * The Stripe reads this feature performs, each behind its own named method.
     *
     * Named rather than inlined for the same reason chargeOffSession() is: they are the seams the
     * tests replace, and the card handoff - where every serious bug in this feature has lived - is
     * otherwise unreachable without a network.
     *
     * They all return null instead of throwing. Every caller runs AFTER the money has already
     * moved, so a failed read must never unwind a settlement or a card swap.
     *
     * Returning null is not the same as being silent, though, and the two are deliberately split.
     * A read that only fills in display text is logged; a read the buyer is waiting on is also
     * reported, so it reaches Sentry. See $report on stripeRead().
     */

    /**
     * Read one PaymentIntent back off the plan's own rail.
     *
     * Exists because the two Stripe events that report the same purchase carry different payloads:
     * `payment_intent.succeeded` hands the webhook the whole intent, while
     * `checkout.session.completed` hands it a session whose `payment_intent` is a bare id. The card
     * capture needs the intent, and which of the two an install receives is a Stripe dashboard
     * setting we do not control - so the session rail fetches what it was not given rather than
     * leaving the plan with no card and the balance uncollectable.
     */
    public function retrievePaymentIntent(SaleInstallmentPlan $plan, string $id): ?\Stripe\PaymentIntent
    {
        return $this->stripeRead($plan, 'payment intent', $id,
            fn ($stripe, $options) => $stripe->paymentIntents->retrieve($id, [], $options));
    }

    /** The card behind a stored payment method - brand, last4 and expiry all live here. */
    public function retrievePaymentMethod(SaleInstallmentPlan $plan, string $id): ?\Stripe\PaymentMethod
    {
        return $this->stripeRead($plan, 'payment method', $id,
            fn ($stripe, $options) => $stripe->paymentMethods->retrieve($id, [], $options));
    }

    /**
     * The SetupIntent behind a completed `mode: setup` session, for a card swap.
     *
     * Reported, unlike its siblings. This one is only ever reached when a buyer has just replaced
     * their card because we asked them to, and a failure here means the swap did not happen - so
     * the cron keeps declining the old card and the ticket is eventually refused at the door. It
     * carried a report() before the shared implementation was extracted, and losing it would put
     * a silent failure back into the one feature whose entire defect was silence.
     */
    public function retrieveSetupIntent(SaleInstallmentPlan $plan, string $id): ?\Stripe\SetupIntent
    {
        return $this->stripeRead($plan, 'setup intent', $id,
            fn ($stripe, $options) => $stripe->setupIntents->retrieve($id, [], $options), report: true);
    }

    /** A Checkout Session the buyer was redirected back on. */
    public function retrieveCheckoutSession(SaleInstallmentPlan $plan, string $id): ?\Stripe\Checkout\Session
    {
        return $this->stripeRead($plan, 'checkout session', $id,
            fn ($stripe, $options) => $stripe->checkout->sessions->retrieve($id, [], $options));
    }

    /**
     * $report escalates a failure from a log line to Sentry. Off by default: these reads run on
     * webhook and cron paths that fire constantly, and a transient Stripe blip on one that only
     * fills in "Visa ending 4242" is not worth waking anyone. On by default would be the same
     * mistake in the other direction - the noise trains people to ignore it.
     */
    private function stripeRead(SaleInstallmentPlan $plan, string $what, string $id, \Closure $read, bool $report = false)
    {
        try {
            [$stripe, $options] = $this->stripeContextFor($plan);

            return $read($stripe, $options);
        } catch (\Exception $e) {
            if ($report) {
                report($e);
            }

            \Log::warning('Could not read a Stripe '.$what.' for an installment plan', [
                'plan_id' => $plan->id,
                'stripe_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Point a plan at a new card, from a SetupIntent the buyer just completed.
     *
     * Shared by the `checkout.session.completed` webhook and by InstallmentController::view(),
     * which applies it on the redirect back from Stripe. Both are needed: a `mode: 'setup'`
     * session emits ONLY `checkout.session.completed` and never `payment_intent.succeeded`, so on
     * an install whose endpoint follows our own Connect setup docs the webhook never arrives and
     * the swap was a silent no-op - the buyer saw a success banner while the cron went on
     * declining the dead card until their ticket was refused at the door.
     *
     * Idempotent, so whichever of the two gets there first wins and the other is free.
     * The CALLER owns authentication (the webhook's account guards, the controller's plan secret).
     */
    public function applyCardUpdate(SaleInstallmentPlan $plan, string $setupIntentId): bool
    {
        $setupIntent = $this->retrieveSetupIntent($plan, $setupIntentId);

        if (($setupIntent->status ?? null) !== 'succeeded' || ! $setupIntent->payment_method) {
            return false;
        }

        $card = $this->retrievePaymentMethod($plan, $setupIntent->payment_method)?->card;

        $plan->forceFill([
            'stripe_customer_id' => $setupIntent->customer ?: $plan->stripe_customer_id,
            'stripe_payment_method_id' => $setupIntent->payment_method,
            // Written even when the card lookup failed, so the display can never describe the card
            // this plan has just STOPPED using. A null last4 reads as "we do not know"; the old
            // card's last4 reads as a lie, and it is the number the buyer is told we will charge.
            'card_brand' => $card->brand ?? null,
            'card_last4' => $card->last4 ?? null,
            'card_exp_month' => $card->exp_month ?? null,
            'card_exp_year' => $card->exp_year ?? null,
        ])->save();

        // A new card is exactly the remedy for the state that parked these rows, so put them
        // back in the ladder rather than leaving the buyer stuck after doing what we asked.
        SaleInstallment::where('sale_installment_plan_id', $plan->id)
            ->whereIn('status', ['awaiting_customer', 'failed'])
            ->update(['status' => 'scheduled', 'attempts' => 0, 'next_attempt_at' => null, 'last_error' => null]);

        if ($plan->status === 'delinquent') {
            $plan->forceFill(['status' => 'active', 'delinquent_at' => null])->save();
        }

        return true;
    }

    /**
     * The idempotency key for one intended charge.
     *
     * Keyed on the installment and its DUE DATE, not on `attempts`. `attempts` does not increment
     * on success, so an attempts-based key was reused indefinitely by the hourly re-selection -
     * and Stripe drops keys after 24 hours, so the reuse silently became a second real charge on
     * the second day. The due date changes only when the schedule legitimately changes.
     */
    public function idempotencyKeyFor(SaleInstallment $installment): string
    {
        return 'installment_'.$installment->id.'_'.($installment->due_at?->timestamp ?? 0);
    }

    /**
     * Cancel every outstanding installment and the plan itself.
     *
     * This is the single most important method in the feature. Four separate paths end a sale -
     * a refund, an event cancellation, a schedule deletion and a backup restore - and any one of
     * them that does not reach here leaves the cron charging a real card monthly for an order
     * that no longer exists.
     *
     * Idempotent, and safe to call on a sale with no plan.
     */
    public function cancelPlan(?SaleInstallmentPlan $plan, string $reason = 'cancelled'): void
    {
        if (! $plan || in_array($plan->status, ['cancelled', 'completed'], true)) {
            return;
        }

        DB::transaction(function () use ($plan, $reason) {
            $locked = SaleInstallmentPlan::lockForUpdate()->find($plan->id);

            if (! $locked || in_array($locked->status, ['cancelled', 'completed'], true)) {
                return;
            }

            SaleInstallment::where('sale_installment_plan_id', $locked->id)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->update([
                    'status' => 'cancelled',
                    'next_attempt_at' => null,
                    'last_error' => $reason,
                    'updated_at' => now(),
                ]);

            $locked->status = 'cancelled';
            $locked->save();
        });
    }

    /**
     * Cancel every active plan attached to a set of sales. Used by the event-cancel and
     * schedule-delete paths, which never touch sales.status and so never reach Sale::booted().
     */
    public function cancelPlansForSales($saleIds, string $reason = 'cancelled'): int
    {
        $saleIds = collect($saleIds)->filter()->unique();

        if ($saleIds->isEmpty()) {
            return 0;
        }

        $plans = SaleInstallmentPlan::whereIn('sale_id', $saleIds)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->get();

        foreach ($plans as $plan) {
            $this->cancelPlan($plan, $reason);
        }

        return $plans->count();
    }
}
