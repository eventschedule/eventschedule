<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\SaleRefund;
use App\Traits\HandlesSaleStatusActions;
use App\Utils\MoneyUtils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\IdempotencyException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\PermissionException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\UnknownApiErrorException;

/**
 * The one way money goes back out for a sale, and the counterpart to SaleSettlementService.
 *
 * Until this existed, refundSale() only flipped a status: the app said "Successfully refunded
 * ticket" and the organizer still had to go and refund by hand on their own dashboard.
 *
 * Three phases, and the split between them is not stylistic:
 *
 *  - CLAIM, in a small transaction with the sale locked. Inserting the ledger row IS the claim,
 *    which is what stops two people refunding the same sale twice. It deliberately does not touch
 *    Sale.status, because saving that fires Sale::booted's released branch - about ten side effects
 *    including InstallmentService::cancelPlan(), and that path takes SALE then PLAN while the
 *    installment service takes PLAN then INSTALLMENT then SALE. That inversion is documented in
 *    InstallmentService and DB::transaction does not retry a deadlock, so nothing new goes inside.
 *
 *  - CALL, after the commit, with no row lock held. Holding a lock across a gateway round trip is
 *    exactly what SaleSettlementService's own comment forbids.
 *
 *  - RECORD, in a second small transaction. The status only becomes `refunded` once the refunded
 *    total reaches what was actually charged, so a PARTIAL refund never enters Sale::booted's
 *    released branch. It must not: that branch returns every seat, restores the whole promo
 *    redemption and credits the entire gift card, all of which assume the sale is completely dead.
 *    Releasing a $100 order's six seats because $20 went back is not a refund, it is a giveaway.
 */
class SaleRefundService
{
    use HandlesSaleStatusActions;

    /**
     * Statuses a refund may be issued against.
     *
     * `amount_mismatch` is here for the site-admin path: the buyer's money is real and sitting at
     * the gateway even though settlement refused to book it, so it is exactly the case where a
     * refund is the right answer.
     */
    private const REFUNDABLE_STATUSES = ['paid', 'amount_mismatch'];

    /**
     * Send money back for a sale.
     *
     * @param  float|null  $amount  in major units. Null means "everything still refundable", and on
     *                              an `amount_mismatch` sale it means the whole charge, since what
     *                              arrived there is by definition not what we expected.
     * @param  int|null  $userId  who asked for it, for the ledger
     * @param  SaleInstallment|null  $leg  refund one installment rather than the sale's own charge
     */
    public function refund(
        Sale $sale,
        ?float $amount = null,
        ?int $userId = null,
        ?string $reason = null,
        ?SaleInstallment $leg = null,
        ?string $requestKey = null,
    ): SaleRefundResult {
        $driver = payment_gateways()->get($sale->payment_method);

        // Asked of the driver, never inferred from payment_method. A sale marked paid by hand has
        // the translated string __('messages.manual_payment') in transaction_reference, so
        // payment_method = 'stripe' is not evidence that Stripe holds anything.
        if (! $driver || ! $driver->supportsRefunds()) {
            return new SaleRefundResult(
                SaleRefundResult::UNSUPPORTED,
                null,
                __('messages.refund_status_only_note'),
            );
        }

        // Routed BEFORE the reference check, because for a plan the money is not on the sale.
        // sales.transaction_reference holds at most installment ONE's PaymentIntent, so refunding
        // the sale total against it asks Stripe for more than that charge ever took - and judging
        // refundability by it would answer "this rail cannot refund" for a plan whose legs each
        // carry a perfectly good reference, handing back every seat while keeping the money.
        if (! $leg && $sale->hasInstallmentPlan()) {
            return $this->refundInstallmentPlan($sale, $amount, $userId, $reason, $requestKey);
        }

        if ($driver->refundReferenceFor($sale, $leg) === null) {
            return new SaleRefundResult(
                SaleRefundResult::UNSUPPORTED,
                null,
                __('messages.refund_status_only_note'),
            );
        }

        [$claim, $fullCharge, $isReplay] = $this->claim(
            $sale, $amount, $userId, $reason, $leg, $driver->key(), $requestKey,
            $driver->omittedRefundAmountMeansRemainder(),
        );

        if (is_string($claim)) {
            return new SaleRefundResult($claim, null, $this->messageFor($claim));
        }

        // A request we have already claimed under this key. Answer from the existing row rather
        // than calling the gateway again - that second call is the whole bug this key exists to
        // stop, and a fresh key per row (which is what this used to mint) stopped nothing.
        if ($isReplay) {
            return $this->replay($claim);
        }

        // The buckets below are decided by ONE question: could the request have reached the gateway?
        //
        // Getting it wrong in the "definite failure" direction is how one refund becomes two. The
        // claim releases its amount only on `failed`, so an outcome we cannot actually rule out
        // must never land there: the owner would be told nothing moved, click again, and the buyer
        // would be paid twice.
        //
        // The driver gets first refusal (its own exception classes are the only ones it can
        // classify), and everything it declines to judge falls through to rethrowIntoLadder(), where
        // the Stripe-shaped arms live and where that hierarchy argument now belongs.
        try {
            $refundId = $driver->refund(
                $sale,
                $fullCharge ? null : (float) $claim->amount,
                $claim->idempotency_key,
                $leg,
                // The currency to scale by. A LEG has a true snapshot to offer - plans carry
                // sale_installment_plans.currency, fixed when the plan was created - so it wins.
                // Everything else falls back to the claim's own copy of the event's code.
                //
                // For an ordinary sale that copy is only as good as the event, because `sales` has
                // no currency column: the claim snapshots whatever the event says at claim time. The
                // real protection is upstream, where ApiEventController now refuses to change a
                // denominated event's currency at all - see Event::hasSettledMoney().
                $leg?->plan?->currency ?: $claim->currency_code,
            );
        } catch (\Throwable $e) {
            // Ask the driver first. The arms below name Stripe's exception classes literally, and a
            // driver on Laravel's Http client throws RequestException / ConnectionException, which
            // are plain \Exception subclasses - so without this every one of its failures, definite
            // refusals included, would reach the conservative \Throwable arm and be parked forever.
            //
            // A driver that returns null (all of them but PayPal) leaves the ladder exactly as it
            // was; \LogicException still wins outright, because "nothing left this machine" is a
            // stronger statement than anything a driver can classify.
            if (! $e instanceof \LogicException) {
                $verdict = $driver->classifyRefundFailure($e);

                if ($verdict === 'fail') {
                    return $this->fail($claim, $e, 'gateway_refused');
                }

                if ($verdict === 'park') {
                    return $this->park($claim, $e);
                }
            }

            return $this->rethrowIntoLadder($e, $claim);
        }

        return $this->record($sale, $claim, $refundId);
    }

    /**
     * The original Stripe-shaped ladder, unchanged in behaviour.
     *
     * Split out only so the driver hook above can run first without duplicating it.
     */
    private function rethrowIntoLadder(\Throwable $e, SaleRefund $claim): SaleRefundResult
    {
        try {
            throw $e;
        } catch (\LogicException $e) {
            // Nothing left this machine. StripeClient's constructor throws Stripe's
            // InvalidArgumentException when the key is unset or malformed, and the driver throws
            // \LogicException when the payment reference vanished between the pre-check and here.
            // Releasing the claim is correct, and the message must not send the owner off to a
            // dashboard we never called - on a misconfigured install this fires for every sale.
            return $this->fail($claim, $e, 'configuration', __('messages.refund_failed_configuration'));
        } catch (ApiConnectionException|UnknownApiErrorException $e) {
            // The request may well have arrived. ApiRequestor maps EVERY unrecognised status to
            // UnknownApiErrorException, which is where 500/502/503 land - a 5xx means Stripe
            // received the call and we do not know what it did with it. Park; never retry.
            return $this->park($claim, $e);
        } catch (AuthenticationException|CardException|IdempotencyException|InvalidRequestException|PermissionException|RateLimitException $e) {
            // The gateway answered and refused. Nothing moved, so the claim releases its amount.
            return $this->fail($claim, $e, $e->getError()?->code);
        } catch (\Throwable $e) {
            // Unknown shape, so unknown outcome, so stay conservative. Wrongly parking costs a
            // person one manual check; wrongly failing costs the buyer a second refund.
            return $this->park($claim, $e);
        }
    }

    /**
     * Reserve the amount under the sale's lock, returning the ledger row or a failure constant.
     */
    private function claim(
        Sale $sale,
        ?float $amount,
        ?int $userId,
        ?string $reason,
        ?SaleInstallment $leg,
        string $gateway,
        ?string $requestKey = null,
        bool $omissionMeansRemainder = true,
    ): array {
        // Whether the CALLER asked for everything, as opposed to a figure we computed for them.
        $requestedFull = $amount === null;

        // Derived from the REQUEST when the caller supplied a key, so a resubmission of the same
        // request lands on the same row. A per-row UUID - which is what this was - cannot dedupe
        // anything, because a second HTTP request simply mints a second UUID.
        $key = $requestKey !== null
            ? 'sale_refund_'.$sale->id.'_'.$requestKey
            : 'sale_refund_'.$sale->id.'_'.Str::uuid();

        return DB::transaction(function () use ($sale, $amount, $requestedFull, $userId, $reason, $leg, $gateway, $key, $requestKey, $omissionMeansRemainder) {
            $locked = Sale::lockForUpdate()->find($sale->id);

            if (! $locked || ! in_array($locked->status, self::REFUNDABLE_STATUSES, true)) {
                return [SaleRefundResult::NOT_REFUNDABLE, false, false];
            }

            // Checked under the sale's lock, so two requests carrying the same key serialise here
            // and the second one sees the first one's row.
            if ($requestKey !== null && $existing = SaleRefund::where('idempotency_key', $key)->first()) {
                return [$existing, false, true];
            }

            // Has anything already gone back on this sale? Read HERE, before any row is created:
            // the create() at the end of this method is evaluated before the $fullCharge expression
            // beside it, so asking later would count the very claim this call is making.
            //
            // An exists() rather than refundedTotal() > 0 on purpose - no float comparison, and it
            // is the same question both readers below are actually asking.
            $hasPriorRefund = $locked->refunds()->whereIn('status', SaleRefund::CLAIMING_STATUSES)->exists();

            if ($locked->status === 'amount_mismatch') {
                // A leg is refused outright rather than metered.
                //
                // UNREACHABLE TODAY, and checked rather than assumed: a sale only reaches
                // `amount_mismatch` through SaleSettlementService::settle(), and an installment
                // plan never goes near it - InstallmentService::settle() takes a plan sale from
                // `unpaid` straight to `paid` itself. So there is no live path here.
                //
                // It is guarded anyway because the fall-through was silently wrong rather than
                // merely unhandled: $fullCharge is `... && ! $leg`, so a leg landing in this branch
                // would have sent the gateway $claim->amount, which the create() below sets to the
                // WHOLE SALE's chargedTotal() when $amount is null - one leg's capture asked to
                // refund every leg's money. Failing closed costs nothing while this cannot happen
                // and refuses loudly if a future change makes it possible.
                if ($leg) {
                    return [SaleRefundResult::INVALID_AMOUNT, false, false];
                }

                // No reliable expected total to meter against, so this is all-or-nothing and only
                // once. A second claim would ask the gateway for a second full refund.
                if ($hasPriorRefund) {
                    return [SaleRefundResult::INVALID_AMOUNT, false, false];
                }

                $amount = null;

                // $requestedFull moves WITH $amount, and forgetting it was a money bug rather than
                // a tidiness one. It is captured from the caller's argument at the top of this
                // method, so a caller who named a figure left it false - and it is what becomes
                // $fullCharge, the flag that decides whether the gateway is sent no amount at all.
                // With it false, refund() sends `(float) $claim->amount` instead, which the create()
                // below sets to chargedTotal() whenever $amount is null: the EXPECTED total. That is
                // exactly the number a mismatched sale is parked for not being, and the number
                // PayPalGateway::refund() documents as the one thing it must never receive here.
                //
                // Concretely, on a sale expecting 100 that captured 120: a request to refund 10 sent
                // PayPal 100, and record() then marked the sale fully refunded regardless of amount,
                // handing back every seat and the whole gift card. On one that captured 90 it asked
                // for more than the capture holds, and the owner was told a valid refund had failed.
                $requestedFull = true;
            } else {
                if ($leg) {
                    // A leg is its own charge, so it meters against itself rather than against the
                    // sale's balance - and it may be refunded exactly once.
                    $alreadyClaimed = SaleRefund::where('sale_installment_id', $leg->id)
                        ->whereIn('status', SaleRefund::CLAIMING_STATUSES)
                        ->exists();

                    if ($alreadyClaimed) {
                        return [SaleRefundResult::INVALID_AMOUNT, false, false];
                    }
                }

                $remaining = $leg
                    ? (float) $leg->amount
                    : $locked->refundableRemaining();

                $amount = $amount ?? $remaining;

                // Half a minor unit of the sale's own currency: 0.005 where cents exist, 0.5 for
                // JPY and the other zero-decimal currencies. A fixed 0.0001 was wrong in both
                // directions - the Sales page publishes the ceiling rounded to 2 decimals while
                // payment_amount is decimal(13,3), and an unrounded percentage discount routinely
                // puts a third decimal there. Refunding "everything" then either 422'd on the
                // prefilled figure or left a thousandth behind, which stops the sale ever reaching
                // `refunded`.
                $tolerance = self::toleranceFor($locked);

                // Cast both sides: payment_amount is decimal(13,3) and uncast, so it arrives as a
                // string, and comparing a string against a fresh float is the trap that stopped
                // promotion spend ever reconciling.
                if ((float) $amount <= 0 || round((float) $amount, 3) - $remaining > $tolerance) {
                    return [SaleRefundResult::INVALID_AMOUNT, false, false];
                }

                $amount = round((float) $amount, 3);

                // Asking for the whole remainder IS a full refund, however the caller spelled it.
                // Matters because $fullCharge is what sends no amount to the gateway, so it gives
                // back exactly what it holds rather than our rounded idea of it.
                if (abs($amount - $remaining) <= $tolerance) {
                    $amount = $remaining;
                    $requestedFull = true;
                }
            }

            return [SaleRefund::create([
                'sale_id' => $locked->id,
                'sale_installment_id' => $leg?->id,
                // Recorded even when null was sent to the gateway, so the ceiling query still sees
                // this claim. For an amount_mismatch that is the expected total, which is the best
                // estimate available until the gateway answers.
                'amount' => $amount ?? $locked->chargedTotal(),
                'currency_code' => $locked->event?->ticket_currency_code,
                'gateway' => $gateway,
                'status' => 'pending',
                // The row is the claim; the key just has to be unique and legible in the gateway's
                // own dashboard. Nothing ever reuses it, because nothing is ever retried.
                'idempotency_key' => $key,
                'user_id' => $userId,
                'reason' => $reason,
                // Send no amount at all when the whole sale is being refunded, so the gateway gives
                // back exactly what it holds. Our own figure is a sum of per-seat decimals that
                // settlement only reconciled to within a cent, so naming it can strand a cent on a
                // grouped order forever. An installment leg stays explicit: it is one charge among
                // several and should never be told to refund "the rest".
                //
                // ...but "no amount" does not mean the same thing to both gateways, which is what
                // $omissionMeansRemainder carries in from the driver.
                //
                // Stripe reads it as the UNREFUNDED balance. There, omitting after a partial is not
                // just safe but necessary: when the earlier claim was PARKED we do not know whether
                // its money moved, and naming our own remainder would under-refund the buyer if it
                // never did. That rail keeps the old behaviour unconditionally.
                //
                // PayPal reads it as the WHOLE capture and refuses one already partly refunded, so
                // there the same omission turned "give back the remaining 70" into a request for the
                // original 100 and a 422 - a sale that could never reach `refunded` through the UI.
                // On that rail the figure is named once anything has gone back; the cent-stranding
                // argument above still applies to the first, untouched refund, which still omits it.
            ]), $requestedFull && ! $leg && ($omissionMeansRemainder || ! $hasPriorRefund), false];
        });
    }

    /**
     * Refund an installment plan by walking the charges it actually made.
     *
     * Full-amount only. A plan collects on its own schedule, so "refund half" has no single
     * meaning - half of what was collected, or half of what was owed? - and the answer would
     * differ per plan. Partial refunds stay available on ordinary sales, where the question has
     * one answer.
     *
     * Resumable, not all-or-nothing: a run that stops partway leaves ledger rows behind, and the
     * next attempt selects only the legs those rows do not already cover.
     *
     * Only PAID legs are refunded; the unpaid ones never took money, and cancelling them is
     * Sale::booted's job once the status flips.
     */
    private function refundInstallmentPlan(
        Sale $sale,
        ?float $amount,
        ?int $userId,
        ?string $reason,
        ?string $requestKey = null,
    ): SaleRefundResult {
        if ($amount !== null) {
            return new SaleRefundResult(
                SaleRefundResult::INVALID_AMOUNT,
                null,
                __('messages.refund_installments_full_only'),
            );
        }

        $plan = $sale->installmentPlan;
        $collected = $plan?->installments()->where('status', 'paid')->count() ?? 0;

        if ($collected === 0) {
            // Nothing was ever collected through the gateway, so there is nothing to send back and
            // the caller should fall back to the status-only path.
            return new SaleRefundResult(
                SaleRefundResult::UNSUPPORTED,
                null,
                __('messages.refund_status_only_note'),
            );
        }

        // Only the legs that have not been claimed yet. Selecting them this way, rather than
        // walking every paid leg and letting the per-leg guard reject the done ones, is what makes
        // a retry work: the guard returns INVALID_AMOUNT, which is not moved(), so the old loop
        // aborted on leg one and could never reach the legs that still held money.
        $legs = $plan->installments()
            ->where('status', 'paid')
            ->whereDoesntHave('refunds', fn ($q) => $q->whereIn('status', SaleRefund::CLAIMING_STATUSES))
            ->orderBy('sequence')
            ->get();

        if ($legs->isEmpty()) {
            // Every collected leg is already claimed, so the money is back and only the status is
            // outstanding - the state a previous run reached just before it was interrupted.
            $this->refundSale($sale);
            $sale->refresh();

            return new SaleRefundResult(SaleRefundResult::REFUNDED, null, $this->settledMessage($sale));
        }

        $moved = 0;
        $last = null;

        foreach ($legs as $installment) {
            $result = $this->refund(
                $sale,
                (float) $installment->amount,
                $userId,
                $reason,
                $installment,
                // Distinct per leg, or the second leg would replay the first leg's row.
                $requestKey !== null ? $requestKey.':leg'.$installment->id : null,
            );

            if (! $result->moved()) {
                // Never report UNSUPPORTED once money has gone back. The callers read UNSUPPORTED
                // as "this rail cannot refund at all" and answer it by flipping the status, which
                // would release every seat and credit the whole gift card on a sale we had just
                // partially refunded - and tell the owner nothing moved.
                if ($moved > 0 && $result->shouldFallBackToStatusOnly()) {
                    return new SaleRefundResult(
                        SaleRefundResult::FAILED,
                        $result->refund,
                        __('messages.refund_partially_completed'),
                    );
                }

                return $moved > 0
                    ? new SaleRefundResult($result->status, $result->refund, __('messages.refund_partially_completed'))
                    : $result;
            }

            $moved++;
            $last = $result;
        }

        // Every collected leg is back. The status flip is done here rather than left to the legs:
        // a plan that only ever collected 2 of 4 payments has refunded everything it took while
        // the sale total is still larger, so no individual leg can see that the job is finished.
        $this->refundSale($sale);
        $sale->refresh();

        return new SaleRefundResult(SaleRefundResult::REFUNDED, $last?->refund, $this->settledMessage($sale));
    }

    /**
     * Bank the successful call, then flip the status only if nothing is left to refund.
     *
     * Two transactions, not one. Marking the ledger row is the durable record that the money has
     * moved and must not be rolled back by anything the status flip does; and refundSale() opens
     * its own transaction to re-lock the sale, which is where the analytics legs are read.
     */
    private function record(Sale $sale, SaleRefund $claim, string $refundId): SaleRefundResult
    {
        DB::transaction(function () use ($claim, $refundId) {
            $claim->update([
                'status' => 'succeeded',
                'gateway_refund_id' => $refundId,
            ]);
        });

        $claim->refresh();
        $sale->refresh();

        $wasMismatch = $sale->status === 'amount_mismatch';

        // Recomputed from the ledger rather than trusting the claim: an earlier partial may have
        // landed between this refund being claimed and it succeeding. The epsilon is half a minor
        // unit of this sale's currency, matching the one the claim measured against.
        $fullyRefunded = $wasMismatch || $sale->refundableRemaining() <= self::toleranceFor($sale);

        // Revenue is deliberately NOT debited here, and the omission is worth stating: a partial
        // refund leaves analytics_events_daily carrying the full amount for this event until the
        // sale is refunded outright. Do not "fix" it with a decrement in this branch -
        // decrementSaleAnalytics() debits legTotalPayment(), the WHOLE leg, so the later full
        // refund would take the partial back a second time. Netting prior partials out of that
        // figure is the real change, and it belongs in HandlesSaleStatusActions.
        if (! $fullyRefunded) {
            return new SaleRefundResult(
                SaleRefundResult::PARTIALLY_REFUNDED,
                $claim,
                __('messages.refund_partial_success'),
            );
        }

        if ($wasMismatch) {
            // Never counted as paid, so there is no booked revenue to unwind - which is why this
            // does not go through refundSale(). Saving still fires Sale::booted's released branch,
            // and that is correct: SaleTicket took its stock on create, so the seats and inventory
            // do have to go back.
            DB::transaction(function () use ($sale) {
                $locked = Sale::lockForUpdate()->find($sale->id);

                if ($locked && $locked->status === 'amount_mismatch') {
                    $locked->status = 'refunded';
                    $locked->save();
                }
            });
        } else {
            $this->refundSale($sale);
        }

        $sale->refresh();

        return new SaleRefundResult(SaleRefundResult::REFUNDED, $claim, $this->settledMessage($sale));
    }

    /**
     * Half a minor unit of the sale's currency, as a major-unit float.
     *
     * Everything money-shaped here is compared with this rather than an absolute epsilon, because
     * "close enough to be the same amount" means something different in USD and in JPY.
     */
    private static function toleranceFor(Sale $sale): float
    {
        $currency = $sale->event?->ticket_currency_code ?: 'USD';

        return 0.5 / MoneyUtils::getSmallestUnitMultiplier($currency);
    }

    /**
     * Answer a repeated request from the row its first attempt already created.
     *
     * Never calls the gateway. A `pending` row means the first attempt is still in flight, which is
     * a conflict rather than a failure - the caller must not be told the refund failed, or they
     * will try again and the money will go back twice.
     */
    private function replay(SaleRefund $claim): SaleRefundResult
    {
        return match ($claim->status) {
            // Answers what the first attempt answered, message included: a replayed partial
            // reported "Successfully refunded ticket" where the original said "Partial refund sent".
            'succeeded' => $claim->sale?->status === 'refunded'
                ? new SaleRefundResult(SaleRefundResult::REFUNDED, $claim, $this->settledMessage($claim->sale))
                : new SaleRefundResult(SaleRefundResult::PARTIALLY_REFUNDED, $claim, __('messages.refund_partial_success')),
            'failed' => new SaleRefundResult(SaleRefundResult::FAILED, $claim, __('messages.refund_failed')),
            'awaiting_reconciliation' => new SaleRefundResult(
                SaleRefundResult::NEEDS_RECONCILIATION,
                $claim,
                __('messages.refund_needs_reconciliation'),
            ),
            default => new SaleRefundResult(
                SaleRefundResult::IN_PROGRESS,
                $claim,
                __('messages.refund_in_progress'),
            ),
        };
    }

    private function fail(SaleRefund $claim, \Throwable $e, ?string $code = null, ?string $message = null): SaleRefundResult
    {
        report($e);

        $claim->update([
            'status' => 'failed',
            'error_code' => $code,
            'last_error' => Str::limit($e->getMessage(), 240, ''),
        ]);

        return new SaleRefundResult(
            SaleRefundResult::FAILED,
            $claim->refresh(),
            $message ?? __('messages.refund_failed'),
        );
    }

    private function park(SaleRefund $claim, \Throwable $e): SaleRefundResult
    {
        report($e);

        $claim->update([
            'status' => 'awaiting_reconciliation',
            'last_error' => Str::limit($e->getMessage(), 240, ''),
        ]);

        return new SaleRefundResult(
            SaleRefundResult::NEEDS_RECONCILIATION,
            $claim->refresh(),
            __('messages.refund_needs_reconciliation'),
        );
    }

    /**
     * "Successfully refunded" is only true once every claim on this sale has been confirmed.
     *
     * A `pending` or `awaiting_reconciliation` row holds its amount against refundableRemaining(),
     * so a sale can reach `refunded` while money we never watched leave is being counted as
     * returned. The status flip is still right - the owner asked for a full refund and the seats
     * have to go back - but telling them it succeeded is not, because nothing here will ever retry
     * that claim and only they can settle it against the gateway.
     *
     * Reachable two ways: a parked partial followed by a refund of the remainder, and a plan whose
     * leg parked, since refundInstallmentPlan() then skips that leg on the next attempt and the
     * walk completes without it.
     */
    private function settledMessage(?Sale $sale): string
    {
        return $sale?->hasUnconfirmedRefund()
            ? __('messages.refund_needs_reconciliation')
            : __('messages.refund_success');
    }

    private function messageFor(string $status): string
    {
        return match ($status) {
            SaleRefundResult::INVALID_AMOUNT => __('messages.refund_amount_invalid'),
            default => __('messages.refund_not_refundable'),
        };
    }
}
