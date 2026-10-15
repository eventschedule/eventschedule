<?php

namespace App\Services;

use App\Jobs\SendQueuedEmail;
use App\Mail\BoostStatementMail;
use App\Models\BoostCampaign;
use App\Models\BoostPartner;
use App\Models\BoostSpendDay;
use App\Models\BoostStatement;
use App\Models\BoostStatementLine;
use App\Utils\MoneyUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The platform's fee on a boost partner's ACTUAL Meta spend: one statement a month, charged to
 * the partner's card.
 *
 * Spend comes from boost_spend_days (Meta's daily figures in the ad account's timezone), so a
 * campaign that spans two months is billed in both. A month is closed, and billed, from the 3rd
 * of the next month in the ad account's timezone, which leaves Meta two days to settle its
 * numbers. A correction Meta makes later to a month already billed (the last two closed months
 * are pulled again each run) becomes an adjustment line on the next statement.
 *
 * Charging follows InstallmentService: the PaymentIntent is created once and its id stored; a
 * retry confirms that same intent with the current card. A fixed idempotency key for a new
 * intent would replay a stored decline for a day and could charge twice once it expired.
 */
class BoostStatementService
{
    public const CLOSE_AFTER_DAYS = 2;

    public const RETRY_DAYS = 7;

    /** Daily: create statements for closed months, then charge or retry what is unpaid. */
    public function run(): void
    {
        // Every partner with boosts, enabled or not: a partner switched off still owes the fee on
        // what its live campaigns went on spending.
        BoostPartner::query()->where(fn ($q) => $q->whereNotNull('enabled_at')->orWhereHas('campaigns'))
            ->each(function (BoostPartner $partner) {
                try {
                    $this->refreshRecentSpend($partner);
                    foreach ($this->closedUnbilledPeriods($partner) as [$period, $currency]) {
                        $this->build($partner, $period, $currency);
                    }
                    $partner->statements()->whereIn('status', ['pending', 'failed'])->each(fn ($s) => $this->charge($s));
                } catch (\Throwable $e) {
                    report($e);
                }
            });
    }

    /** Pull the last two closed months' daily spend again, so Meta's corrections are seen. */
    public function refreshRecentSpend(BoostPartner $partner): void
    {
        $tz = $partner->ad_account_timezone ?: 'UTC';
        $since = Carbon::now($tz)->startOfMonth()->subMonths(2);
        $sync = app(BoostPartnerSync::class);

        BoostCampaign::where('boost_partner_id', $partner->id)
            ->whereNotNull('meta_campaign_id')
            ->whereNotNull('launched_at')
            // Only what can still move: not final yet, or final within the last two months (a
            // month already billed can still be corrected). Older campaigns are left alone.
            ->where(fn ($q) => $q->whereNull('spend_final_at')->orWhere('spend_final_at', '>=', $since))
            ->where(fn ($q) => $q->whereNull('stopped_at')->orWhere('stopped_at', '>=', $since))
            ->each(function (BoostCampaign $campaign) use ($sync) {
                try {
                    $sync->syncSpendDays($campaign, MetaAdsService::forCampaign($campaign));
                } catch (\Exception $e) {
                    Log::info('Boost statement: could not refresh spend', ['campaign_id' => $campaign->id, 'error' => $e->getMessage()]);
                }
            });
    }

    /**
     * Closed months not yet billed, one entry per currency the partner spent in that month (a
     * partner that moved to an ad account in another currency is billed in each, never summed).
     *
     * @return array<int, array{0: string, 1: string}> [yyyy-mm, currency], oldest first
     */
    public function closedUnbilledPeriods(BoostPartner $partner, ?Carbon $now = null): array
    {
        $tz = $partner->ad_account_timezone ?: 'UTC';
        $now = ($now ?? Carbon::now())->copy()->setTimezone($tz);

        $ranges = BoostSpendDay::whereIn('boost_campaign_id', BoostCampaign::where('boost_partner_id', $partner->id)->select('id'))
            ->selectRaw('UPPER(currency) as currency, MIN(day) as first_day, MAX(day) as last_day')
            ->groupBy(DB::raw('UPPER(currency)'))
            ->get();

        $billed = $partner->statements()->get(['period', 'currency'])->map(fn ($s) => $s->period.'|'.strtoupper($s->currency))->all();
        $periods = [];

        // Each currency is billed from its own first day of spend until two months after its last
        // (the window in which Meta still corrects a month), never across the whole history.
        foreach ($ranges as $range) {
            $month = Carbon::parse($range->first_day, $tz)->startOfMonth();
            $lastMonth = Carbon::parse($range->last_day, $tz)->startOfMonth()->addMonths(2);
            while ($month->lte($lastMonth)) {
                $closesAt = $month->copy()->addMonth()->startOfMonth()->addDays(self::CLOSE_AFTER_DAYS);
                if ($now->lt($closesAt)) {
                    break;
                }
                $period = $month->format('Y-m');
                if (! in_array($period.'|'.$range->currency, $billed, true)) {
                    $periods[] = [$period, $range->currency];
                }
                $month->addMonth();
            }
        }

        usort($periods, fn ($a, $b) => strcmp($a[0], $b[0]) ?: strcmp($a[1], $b[1]));

        return $periods;
    }

    public function build(BoostPartner $partner, string $period, ?string $currency = null): BoostStatement
    {
        return DB::transaction(function () use ($partner, $period, $currency) {
            $partner = BoostPartner::lockForUpdate()->find($partner->id);
            $currency = strtoupper($currency ?: ($partner->ad_account_currency ?: 'USD'));
            $existing = $partner->statements()->where('period', $period)->where('currency', $currency)->first();
            if ($existing) {
                return $existing;
            }

            $rate = (float) $partner->fee_rate;
            $statement = BoostStatement::create([
                'boost_partner_id' => $partner->id,
                'period' => $period,
                'currency' => $currency,
                'status' => 'pending',
            ]);

            $totalSpend = 0.0;
            $totalFee = 0.0;
            foreach ($this->spendByCampaign($partner, $period, $currency) as $campaignId => $spend) {
                $fee = round($spend * $rate, 2);
                BoostStatementLine::create([
                    'boost_statement_id' => $statement->id,
                    'boost_campaign_id' => $campaignId,
                    'period' => $period,
                    'spend' => $spend,
                    'fee' => $fee,
                ]);
                $totalSpend += $spend;
                $totalFee += $fee;
            }

            // Corrections to months already billed.
            $adjustments = 0.0;
            foreach ($partner->statements()->where('currency', $currency)->where('period', '<', $period)->orderByDesc('period')->limit(2)->pluck('period') as $earlier) {
                $billed = BoostStatementLine::whereIn('boost_statement_id', $partner->statements()->where('currency', $currency)->select('id'))
                    ->where('period', $earlier)
                    ->selectRaw('boost_campaign_id, SUM(spend) as spend')
                    ->groupBy('boost_campaign_id')
                    ->pluck('spend', 'boost_campaign_id');

                $now = $this->spendByCampaign($partner, $earlier, $currency);
                foreach (array_unique(array_merge(array_keys($now), $billed->keys()->all())) as $campaignId) {
                    $delta = round(($now[$campaignId] ?? 0) - (float) ($billed[$campaignId] ?? 0), 2);
                    if (abs($delta) < 0.01) {
                        continue;
                    }
                    $adjust = round($delta * $rate, 2);
                    BoostStatementLine::create([
                        'boost_statement_id' => $statement->id,
                        'boost_campaign_id' => $campaignId,
                        'period' => $earlier,
                        'spend' => $delta,
                        'fee' => 0,
                        'adjustment' => $adjust,
                        // No stored wording: the view says "Correction" for a line whose month is
                        // earlier than its statement's, in the reader's language.
                        'note' => null,
                    ]);
                    $adjustments += $adjust;
                }
            }

            // What was too small to charge, or a credit, moves on in its own currency: from the
            // latest earlier statement in this currency, when that one carried.
            $previous = $partner->statements()->where('currency', $currency)->where('period', '<', $period)
                ->where('id', '!=', $statement->id)->orderByDesc('period')->first();
            $carriedIn = $previous && $previous->status === 'carried' ? $previous->amountDue() : 0.0;
            $statement->fill([
                'total_spend' => round($totalSpend, 2),
                'total_fee' => round($totalFee, 2),
                'total_adjustments' => round($adjustments, 2),
                'carried_in' => $carriedIn,
            ]);

            $due = $statement->amountDue();
            $carried = $due <= 0 || $due < $this->minimumCharge($currency);
            if ($carried) {
                $statement->status = 'carried';
            }
            // Shown on the account; the statements themselves carry the amount from month to month.
            if ($currency === strtoupper($partner->ad_account_currency ?: 'USD')) {
                $partner->carry_forward = $carried ? $due : 0;
            }
            $statement->save();
            $partner->save();

            // A month with nothing in it is recorded, and not mailed.
            if ($statement->status === 'carried' && ($totalSpend != 0 || $adjustments != 0 || $carriedIn != 0)) {
                DB::afterCommit(fn () => $this->mail($statement));
            }

            return $statement;
        });
    }

    /** @return array<int, float> campaign id => spend in that month */
    public function spendByCampaign(BoostPartner $partner, string $period, ?string $currency = null): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $period.'-01')->startOfDay();

        return BoostSpendDay::whereIn('boost_campaign_id', BoostCampaign::where('boost_partner_id', $partner->id)->select('id'))
            ->when($currency, fn ($q) => $q->where('currency', $currency))
            ->whereBetween('day', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()])
            ->selectRaw('boost_campaign_id, SUM(spend) as spend')
            ->groupBy('boost_campaign_id')
            ->pluck('spend', 'boost_campaign_id')
            ->map(fn ($v) => round((float) $v, 2))
            ->filter(fn ($v) => $v != 0)
            ->all();
    }

    /** This month so far, for the Statements page: spend and the fee it would carry. */
    public function monthSoFar(BoostPartner $partner): array
    {
        $tz = $partner->ad_account_timezone ?: 'UTC';
        $period = Carbon::now($tz)->format('Y-m');
        $spend = array_sum($this->spendByCampaign($partner, $period, $partner->ad_account_currency ?: 'USD'));

        return [
            'period' => $period,
            'currency' => $partner->ad_account_currency ?: 'USD',
            'spend' => round($spend, 2),
            'fee' => round($spend * (float) $partner->fee_rate, 2),
        ];
    }

    /** Where the bank sends the partner to confirm a payment, after an on-session charge. */
    public ?string $redirectUrl = null;

    /**
     * Charge a statement. Returns paid | action_required | failed | skipped.
     *
     * Off-session (the daily run) a card that needs its holder to authenticate is never retried:
     * the statement waits for "Pay now". On-session (Pay now) the bank's confirmation page is
     * returned in $redirectUrl.
     */
    public function charge(BoostStatement $statement, bool $onSession = false): string
    {
        $this->redirectUrl = null;

        if ($statement->isSettled()) {
            return $statement->status === 'paid' ? 'paid' : 'skipped';
        }

        $partner = $statement->partner;
        if (! $partner || ! config('services.stripe_platform.secret')) {
            return 'skipped';
        }

        // No card: say so once, and count it as unpaid where the operator looks.
        if (! $partner->hasCard()) {
            if ($statement->status === 'pending') {
                $statement->forceFill(['attempts' => $statement->attempts + 1, 'last_attempt_at' => now()])->save();

                return $this->markFailed($statement, 'no_card');
            }

            return 'skipped';
        }

        // A declined card is retried for RETRY_DAYS; a statement set back to "pending" by a new
        // card gets its try whatever its age.
        if (! $onSession && ($statement->status === 'action_required'
            || ($statement->status === 'failed' && $statement->created_at->lt(now()->subDays(self::RETRY_DAYS)))
            || ($statement->last_attempt_at && $statement->last_attempt_at->gt(now()->subHours(20))))) {
            return 'skipped';
        }

        $statement->forceFill(['attempts' => $statement->attempts + 1, 'last_attempt_at' => now()])->save();

        try {
            $intent = $this->confirmIntent($statement, $partner, $onSession);
        } catch (\Stripe\Exception\CardException $e) {
            // The intent Stripe made for the declined attempt: kept, and confirmed again on the
            // next try rather than replaced (a new one could charge twice).
            $failedIntent = $e->getError()?->payment_intent;
            if ($failedIntent && ! $statement->stripe_payment_intent_id) {
                $statement->forceFill(['stripe_payment_intent_id' => $failedIntent->id])->save();
            }

            $code = $e->getError()?->decline_code ?: ($e->getStripeCode() ?: 'card_declined');
            if ($code === 'authentication_required' || ($failedIntent->status ?? null) === 'requires_action') {
                return $this->markActionRequired($statement);
            }

            return $this->markFailed($statement, $code);
        } catch (\Exception $e) {
            report($e);

            return $this->markFailed($statement, 'error');
        }

        if ($intent->status === 'succeeded') {
            $this->markPaid($statement, $intent->id);

            return 'paid';
        }

        if ($intent->status === 'processing') {
            return 'skipped';
        }

        if (in_array($intent->status, ['requires_action', 'requires_confirmation'], true)) {
            $this->redirectUrl = $intent->next_action->redirect_to_url->url ?? null;

            return $this->markActionRequired($statement, ! $onSession);
        }

        return $this->markFailed($statement, $intent->last_payment_error->code ?? $intent->status);
    }

    /** After the bank's page: read the intent again and record the payment if it went through. */
    public function settleReturned(BoostStatement $statement): string
    {
        if ($statement->status === 'paid') {
            return 'paid';
        }

        if (! $statement->stripe_payment_intent_id || ! config('services.stripe_platform.secret')) {
            return 'failed';
        }

        try {
            $intent = (new \Stripe\StripeClient(config('services.stripe_platform.secret')))->paymentIntents->retrieve($statement->stripe_payment_intent_id);
        } catch (\Exception $e) {
            report($e);

            return 'failed';
        }

        if ($intent->status === 'succeeded') {
            $this->markPaid($statement, $intent->id);

            return 'paid';
        }

        return 'failed';
    }

    public function markPaid(BoostStatement $statement, string $intentId): void
    {
        // Once: the return from the bank and Stripe's webhook can both arrive.
        $changed = BoostStatement::whereKey($statement->id)->where('status', '!=', 'paid')->update([
            'status' => 'paid',
            'stripe_payment_intent_id' => $intentId,
            'charged_amount' => $statement->amountDue(),
            'charged_at' => now(),
            'failure_code' => null,
        ]);

        if ($changed) {
            $this->mail($statement->fresh());
        }
    }

    protected function markActionRequired(BoostStatement $statement, bool $mail = true): string
    {
        $first = $statement->status !== 'action_required';
        // Never over a payment recorded meanwhile (the webhook, or a second Pay now).
        $changed = BoostStatement::whereKey($statement->id)->where('status', '!=', 'paid')
            ->update(['status' => 'action_required', 'failure_code' => 'authentication_required']);
        if ($changed && $first && $mail) {
            $this->mail($statement->fresh());
        }

        return $changed ? 'action_required' : 'paid';
    }

    protected function markFailed(BoostStatement $statement, string $code): string
    {
        $changed = BoostStatement::whereKey($statement->id)->where('status', '!=', 'paid')
            ->update(['status' => 'failed', 'failure_code' => substr($code, 0, 64)]);
        if ($changed && $statement->attempts === 1) {
            $this->mail($statement->fresh());
        }

        return $changed ? 'failed' : 'paid';
    }

    /**
     * The intent is created once and confirmed again on a retry with the card on file now. Every
     * call carries a key of its own attempt: a fixed key would answer a new card with the old
     * request's result, or refuse it as a mismatch.
     */
    protected function confirmIntent(BoostStatement $statement, BoostPartner $partner, bool $onSession): \Stripe\PaymentIntent
    {
        $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));
        // A confirm carries a key of its own attempt (a new card must not get the old answer);
        // the create carries one stable key, so a create that went through while our call timed
        // out is answered with that same intent, never made twice.
        $key = 'boost-statement-'.$statement->id.'-confirm-'.$statement->attempts;
        $returnUrl = route('boost.partner.statement_return', ['period' => $statement->period, 'currency' => $statement->currency]);

        if ($statement->stripe_payment_intent_id) {
            $intent = $stripe->paymentIntents->retrieve($statement->stripe_payment_intent_id);
            if (in_array($intent->status, ['succeeded', 'processing'], true)) {
                return $intent;
            }

            return $stripe->paymentIntents->confirm($intent->id, $onSession
                ? ['payment_method' => $partner->stripe_payment_method_id, 'return_url' => $returnUrl]
                : ['payment_method' => $partner->stripe_payment_method_id, 'off_session' => true],
                ['idempotency_key' => $key]);
        }

        $intent = $stripe->paymentIntents->create(array_filter([
            'amount' => (int) round($statement->amountDue() * MoneyUtils::getSmallestUnitMultiplier($statement->currency)),
            'currency' => strtolower($statement->currency),
            'customer' => $partner->stripe_customer_id,
            'payment_method' => $partner->stripe_payment_method_id,
            'off_session' => $onSession ? null : true,
            'return_url' => $onSession ? $returnUrl : null,
            'confirm' => true,
            'description' => 'Boost fee '.$statement->period,
            'metadata' => ['boost_statement_id' => (string) $statement->id],
        ], fn ($v) => $v !== null), ['idempotency_key' => 'boost-statement-'.$statement->id.'-create']);

        $statement->forceFill(['stripe_payment_intent_id' => $intent->id])->save();

        return $intent;
    }

    public function minimumCharge(string $currency): float
    {
        return app(InstallmentService::class)->minimumChargeAmount($currency);
    }

    protected function mail(BoostStatement $statement): void
    {
        $user = $statement->partner?->user;
        if (! $user?->email) {
            return;
        }

        SendQueuedEmail::dispatch(new BoostStatementMail($statement->fresh()), $user->email, null, $user->language_code ?: null);
        $statement->forceFill(['mailed_at' => now()])->save();
    }
}
