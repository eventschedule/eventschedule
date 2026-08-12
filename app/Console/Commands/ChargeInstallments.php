<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\InstallmentAuthenticationRequired;
use App\Mail\InstallmentFailed;
use App\Mail\InstallmentFinalNotice;
use App\Mail\InstallmentOnHold;
use App\Mail\InstallmentOrganizerDigest;
use App\Mail\InstallmentReminder;
use App\Models\Role;
use App\Models\SaleInstallment;
use App\Models\SaleInstallmentPlan;
use App\Services\AuditService;
use App\Services\InstallmentService;
use App\Services\WebhookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Collect scheduled installment payments and warn people before and after each attempt.
 *
 * This is the only place in the app that INITIATES a charge rather than recording one somebody
 * else made, which is why it carries protections nothing else here needs: a Stripe idempotency
 * key, a claim status, and a real retry backoff. Without them an overlapping run double-charges,
 * and an hourly command retries a declined card twenty-four times a day - a card-testing pattern
 * that gets the organizer's Stripe account rate-limited.
 */
class ChargeInstallments extends Command
{
    protected $signature = 'app:charge-installments {--max-seconds=120} {--dry-run}';

    protected $description = 'Charge due ticket installments and send payment reminders';

    /** Send the buyer a heads-up this many days before a charge. */
    private const REMIND_DAYS_AHEAD = 2;

    /** How long a `processing` row may sit before a later run reclaims it. */
    private const STRANDED_AFTER_HOURS = 6;

    /** How long a parked or failed installment may sit past due before the plan goes on hold. */
    private const STALLED_AFTER_DAYS = 7;

    /** Final pre-event sweep, regardless of where the retry ladder has got to. */
    private const PRE_EVENT_SWEEP_DAYS = 7;

    private float $deadline;

    public function handle(): int
    {
        // The hosted cron is an HTTP request sharing one 900s budget across every hourly command
        // (AppController::translateData). A synchronous loop of Stripe calls can eat all of it and
        // starve everything queued behind, so this command bounds itself the way app:translate
        // does and simply picks up where it left off next hour.
        $this->deadline = microtime(true) + (float) $this->option('max-seconds');

        $this->recoverStranded();
        $this->remindUpcoming();
        $this->chargeDue();
        $this->sweepBeforeEvents();
        $this->escalateStalled();

        return self::SUCCESS;
    }

    private function outOfTime(): bool
    {
        return microtime(true) >= $this->deadline;
    }

    /**
     * Put rows the previous run left mid-flight back in the ladder.
     *
     * `processing` is claimed immediately before the Stripe call, so a worker killed in that
     * window - a deploy, an OOM, the hosted cron's 900s budget expiring - left the row invisible
     * to every subsequent run: chargeDue() selects only `scheduled`, and nothing else in the app
     * ever read `processing` back. The money was simply never collected and nobody was told.
     *
     * Safe to retry because the idempotency key is derived from the installment and its due date,
     * so a genuinely in-flight charge is deduplicated by Stripe rather than taken twice. The
     * window is deliberately generous - far longer than any single charge - so this can never
     * race a healthy run.
     */
    private function recoverStranded(): void
    {
        $recovered = SaleInstallment::query()
            ->where('status', 'processing')
            ->where('updated_at', '<', now()->subHours(self::STRANDED_AFTER_HOURS))
            ->whereHas('plan', fn ($q) => $q->whereIn('status', ['active', 'delinquent']))
            ->update(['status' => 'scheduled']);

        if ($recovered) {
            Log::warning('Recovered stranded installment charges', ['count' => $recovered]);
            $this->info("Recovered {$recovered} stranded installment(s).");
        }
    }

    /**
     * A plan that has stopped being collectable for any reason other than the retry ladder still
     * has to reach the organizer and the door.
     *
     * Only ONE of the ways collection can stop used to reach markDelinquent() - three consecutive
     * card declines. A plan parked for SCA, parked because the organizer revoked Stripe access,
     * flagged by an amount mismatch, or stranded mid-charge all left the ticket scanning green
     * indefinitely with the money uncollected, which is not what "revoked in arrears" is supposed
     * to mean.
     */
    private function escalateStalled(): void
    {
        $stalled = SaleInstallmentPlan::query()
            ->where('status', 'active')
            ->whereHas('installments', fn ($q) => $q
                ->whereIn('status', ['awaiting_customer', 'awaiting_reconciliation', 'failed'])
                ->where('due_at', '<', now()->subDays(self::STALLED_AFTER_DAYS)))
            ->with(['installments', 'sale.event.creatorRole'])
            ->get();

        foreach ($stalled as $plan) {
            $this->markDelinquent($plan);

            $role = $this->roleFor($plan);
            if ($role && $this->canEmail($role, $plan)) {
                SendQueuedEmail::dispatch(
                    new InstallmentOnHold($plan->fresh(), $plan->nextDueInstallment(), $role),
                    $plan->sale->email,
                    $role->id,
                    app()->getLocale()
                );
            }
        }

        if ($stalled->isNotEmpty()) {
            $this->info("Escalated {$stalled->count()} stalled plan(s).");
        }
    }

    /**
     * Buyer reminders, plus one aggregated digest per schedule.
     */
    private function remindUpcoming(): void
    {
        $windowEnd = now()->addDays(self::REMIND_DAYS_AHEAD);

        $installments = SaleInstallment::query()
            ->where('status', 'scheduled')
            ->whereNull('reminder_sent_at')
            ->where('due_at', '>', now())
            ->where('due_at', '<=', $windowEnd)
            // A stored card is the proof the buyer actually completed checkout. Without it the
            // plan is an abandoned Stripe session, and reminding those people about "your payment
            // of X is due in 2 days" is dunning someone who never bought anything.
            ->whereHas('plan', fn ($q) => $q->where('status', 'active')->whereNotNull('stripe_payment_method_id'))
            ->with(['plan.sale.event.creatorRole'])
            ->get();

        $digest = [];
        $sent = 0;

        foreach ($installments as $installment) {
            $plan = $installment->plan;
            $role = $this->roleFor($plan);

            if (! $role || ! $this->canEmail($role, $plan)) {
                continue;
            }

            // Claim before sending, under a row lock. Copied from SendAppointmentReminders, which
            // is the strongest idempotency idiom in the repo: stamp first so a concurrent run
            // cannot also send, and roll the stamp back if the dispatch throws.
            $claimed = false;
            DB::transaction(function () use ($installment, &$claimed) {
                $locked = SaleInstallment::whereKey($installment->id)->lockForUpdate()->first();
                if (! $locked || $locked->reminder_sent_at) {
                    return;
                }
                $locked->forceFill(['reminder_sent_at' => now()])->saveQuietly();
                $claimed = true;
            });

            if (! $claimed) {
                continue;
            }

            try {
                SendQueuedEmail::dispatch(
                    new InstallmentReminder($plan, $installment, $role),
                    $plan->sale->email,
                    $role->id,
                    app()->getLocale()
                );
                $sent++;

                $digest[$role->id.'|'.$plan->currency]['role'] = $role;
                $digest[$role->id.'|'.$plan->currency]['rows'][] = $this->digestRow($plan, $installment);
                $digest[$role->id.'|'.$plan->currency]['currency'] = $plan->currency;
                $digest[$role->id.'|'.$plan->currency]['total'] = ($digest[$role->id.'|'.$plan->currency]['total'] ?? 0) + (float) $installment->amount;
            } catch (\Throwable $e) {
                report($e);
                SaleInstallment::whereKey($installment->id)->update(['reminder_sent_at' => null]);
            }
        }

        foreach ($digest as $entry) {
            $this->sendDigest($entry['role'], $entry['rows'], 'due', $entry['currency'], $entry['total']);
        }

        $this->info("Installment reminders: {$sent} sent, ".count($digest).' organizer digests.');
    }

    /**
     * Charge everything due, off-session, on the event owner's connected account.
     */
    private function chargeDue(): void
    {
        $due = SaleInstallment::query()
            ->where('status', 'scheduled')
            ->where('due_at', '<=', now())
            // Without next_attempt_at this selector would re-attempt a declined card on every
            // hourly run, forever.
            ->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()))
            ->whereHas('plan', fn ($q) => $q->whereIn('status', ['active', 'delinquent']))
            ->with(['plan.sale.event.creatorRole'])
            ->orderBy('due_at')
            ->get();

        $charged = 0;
        $failed = 0;

        foreach ($due as $installment) {
            if ($this->outOfTime()) {
                $this->warn('Time budget reached; remaining installments will be picked up next run.');
                break;
            }

            $plan = $installment->plan;

            if (! $plan || ! $plan->stripe_customer_id || ! $plan->stripe_payment_method_id) {
                // No stored card yet (installment 1 has not settled) - nothing to charge
                // off-session. The buyer's own pay link still works.
                continue;
            }

            // Claim the row: scheduled -> processing, committed BEFORE talking to Stripe. Without
            // this the claim is only in memory and two overlapping runs both proceed.
            $claimed = false;
            DB::transaction(function () use ($installment, &$claimed) {
                $locked = SaleInstallment::whereKey($installment->id)->lockForUpdate()->first();
                if (! $locked || $locked->status !== 'scheduled') {
                    return;
                }
                $locked->forceFill(['status' => 'processing'])->saveQuietly();
                $claimed = true;
            });

            if (! $claimed) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("[dry-run] would charge installment {$installment->id}");
                SaleInstallment::whereKey($installment->id)->update(['status' => 'scheduled']);

                continue;
            }

            $this->attemptCharge($installment->fresh(), $plan, $charged, $failed);
        }

        $this->info("Installments charged: {$charged}, failed: {$failed}.");
    }

    private function attemptCharge(SaleInstallment $installment, SaleInstallmentPlan $plan, int &$charged, int &$failed): void
    {
        $installments = app(InstallmentService::class);

        try {
            $intent = $installments->chargeOffSession(
                $plan,
                $installment,
                $installments->idempotencyKeyFor($installment)
            );

            // Settle here, from the status `confirm: true` already returned, rather than releasing
            // the row and waiting for a webhook. The webhook stays the backstop and is idempotent,
            // but it can no longer be the ONLY thing that records a payment: both webhook secrets
            // are optional, and the row used to go straight back to `scheduled` with no
            // next_attempt_at, so an undelivered webhook meant the cron re-presented the same
            // charge every hour until Stripe forgot the idempotency key and took the money again.
            if (($intent->status ?? null) === 'succeeded') {
                $installments->settle(
                    $plan,
                    $installment->fresh(),
                    false,
                    (float) $installment->amount,
                    $intent->id ?? null,
                    $intent,
                );

                $charged++;

                return;
            }

            // Anything else (requires_action and friends) is the customer's move, not ours - so
            // the customer has to be asked to make it. The same outcome arriving as a CardException
            // emails them; arriving as an intent status used to park in silence, and the buyer's
            // first news of it was their ticket going on hold a week later.
            $this->releaseAndPark($installment, 'requires_action');

            $role = $this->roleFor($plan);
            if ($role && $this->canEmail($role, $plan)) {
                SendQueuedEmail::dispatch(
                    new InstallmentAuthenticationRequired($plan, $installment->fresh(), $role),
                    $plan->sale->email,
                    $role->id,
                    app()->getLocale()
                );
            }

            Log::info('Installment charge parked for customer action', [
                'installment_id' => $installment->id,
                'plan_id' => $plan->id,
                'intent_status' => $intent->status ?? null,
            ]);

            $failed++;
        } catch (\Stripe\Exception\CardException $e) {
            $this->handleCardFailure($installment, $plan, $e);
            $failed++;
        } catch (\Stripe\Exception\PermissionException|\Stripe\Exception\AuthenticationException $e) {
            // The owner revoked platform access from their own Stripe dashboard. Terminal, so tell
            // somebody: this used to park silently and the organizer's tab mislabelled it as the
            // buyer needing to confirm with their bank.
            $this->releaseAndPark($installment, 'connect_revoked');
            $this->notifyConnectRevoked($plan);
            Log::error('Installment charge failed: Stripe access revoked', [
                'plan_id' => $plan->id,
                'account' => $plan->stripe_account_id,
            ]);
            $failed++;
        } catch (\Throwable $e) {
            report($e);

            // The outcome is UNKNOWN - Stripe may well have taken the money and we lost the
            // response. Do not blind-retry: park it for a human, because a retry after the
            // idempotency key expires is exactly how a timeout becomes a double charge.
            SaleInstallment::whereKey($installment->id)->update([
                'status' => 'awaiting_reconciliation',
                'last_error' => substr($e->getMessage(), 0, 250),
                'next_attempt_at' => null,
            ]);
            $failed++;
        }
    }

    /**
     * A card error. SCA is separated out because it is not a decline and must not consume the
     * retry ladder.
     */
    private function handleCardFailure(SaleInstallment $installment, SaleInstallmentPlan $plan, \Stripe\Exception\CardException $e): void
    {
        $code = $e->getError()?->code;
        $role = $this->roleFor($plan);

        if ($code === 'authentication_required') {
            // The issuer wants the cardholder to authenticate. It will fail identically on every
            // retry, so burning three attempts and then revoking the ticket of a buyer who is
            // perfectly willing to pay is exactly the wrong outcome. Park it and put the buyer in
            // front of the payment instead. attempts is deliberately NOT incremented.
            $this->releaseAndPark($installment, 'authentication_required');

            if ($role && $this->canEmail($role, $plan)) {
                SendQueuedEmail::dispatch(
                    new InstallmentAuthenticationRequired($plan, $installment->fresh(), $role),
                    $plan->sale->email,
                    $role->id,
                    app()->getLocale()
                );
            }

            Log::info('Installment charge needs customer authentication (SCA)', [
                'installment_id' => $installment->id,
                'plan_id' => $plan->id,
            ]);

            return;
        }

        $attempts = $installment->attempts + 1;
        $isFinal = $attempts >= SaleInstallment::MAX_ATTEMPTS;

        $backoffDays = SaleInstallment::RETRY_DAYS[min($attempts, count(SaleInstallment::RETRY_DAYS)) - 1];

        SaleInstallment::whereKey($installment->id)->update([
            'status' => $isFinal ? 'failed' : 'scheduled',
            'attempts' => $attempts,
            'last_error' => substr((string) $code, 0, 250),
            'next_attempt_at' => $isFinal ? null : now()->addDays($backoffDays),
        ]);

        AuditService::log(
            AuditService::SALE_INSTALLMENT_FAILED, null, 'Sale', $plan->sale_id,
            null, ['code' => $code, 'attempt' => $attempts],
            'installment:event_id:'.($plan->sale?->event_id ?? 0)
        );

        if ($isFinal) {
            $this->markDelinquent($plan);
        }

        // The event name promises a charge that failed, so a decline has to be what fires it. The
        // only other dispatcher is InstallmentService::settle(), which fires it when a payment
        // *arrives* and cannot be applied - a real case, but not the one an integrator reads the
        // name as. Both now fire it and `outcome` is what tells them apart: 'declined' here,
        // 'dead_plan' / 'amount_mismatch' / 'duplicate' / 'nothing_due' there.
        if ($plan->sale) {
            WebhookService::dispatch('installment.failed', $plan->sale, null, [
                'installment' => [
                    'sequence' => $installment->sequence,
                    'amount' => (float) $installment->amount,
                    'outcome' => 'declined',
                    'error' => $code,
                    'attempt' => $attempts,
                    'is_final' => $isFinal,
                    'next_attempt_at' => $isFinal ? null : now()->addDays($backoffDays)->toIso8601String(),
                    'plan' => $plan->fresh()->toSummaryData(),
                ],
            ]);
        }

        if ($role && $this->canEmail($role, $plan)) {
            $mailable = $isFinal
                ? new InstallmentOnHold($plan->fresh(), $installment->fresh(), $role)
                : new InstallmentFailed($plan, $installment->fresh(), $role);

            SendQueuedEmail::dispatch($mailable, $plan->sale->email, $role->id, app()->getLocale());

            // Failures are immediate and individual for the organizer too - unlike the routine
            // reminder, this one needs acting on.
            $this->sendDigest($role, [$this->digestRow($plan, $installment)], 'overdue', $plan->currency, (float) $installment->amount);
        }
    }

    /**
     * Take the row out of the automatic ladder without counting it as a failed attempt.
     */
    private function releaseAndPark(SaleInstallment $installment, string $reason): void
    {
        SaleInstallment::whereKey($installment->id)->update([
            'status' => 'awaiting_customer',
            'last_error' => $reason,
            'next_attempt_at' => null,
        ]);
    }

    /**
     * The organizer's Stripe connection is gone, so nothing on this plan can be collected until
     * they reconnect. Theirs to fix, so they are the ones told - the buyer has done nothing wrong
     * and there is no action they could take.
     */
    private function notifyConnectRevoked(SaleInstallmentPlan $plan): void
    {
        $role = $this->roleFor($plan);

        if (! $role) {
            return;
        }

        $this->sendDigest($role, [$this->digestRow($plan, $plan->nextDueInstallment())], 'overdue', $plan->currency, $plan->amountRemaining());
    }

    private function markDelinquent(SaleInstallmentPlan $plan): void
    {
        DB::transaction(function () use ($plan) {
            $locked = SaleInstallmentPlan::lockForUpdate()->find($plan->id);
            if (! $locked || $locked->status !== 'active') {
                return;
            }
            $locked->status = 'delinquent';
            $locked->delinquent_at = now();
            $locked->save();
        });
    }

    /**
     * The backstop: everyone with an outstanding balance hears about it a week before the doors
     * open, whatever state their retry ladder is in.
     */
    private function sweepBeforeEvents(): void
    {
        $target = now()->addDays(self::PRE_EVENT_SWEEP_DAYS);

        $plans = SaleInstallmentPlan::query()
            ->whereIn('status', ['active', 'delinquent'])
            ->whereNotNull('stripe_payment_method_id')
            // Matched on the SALE's occurrence date, not events.starts_at. For a recurring event
            // starts_at is the recurrence anchor and sits in the past, so a window on it excluded
            // every recurring event from the one backstop that guarantees nobody reaches the door
            // without being told they owe money. InstallmentService::occurrenceStart() documents
            // the same trap.
            ->whereHas('sale', function ($q) use ($target) {
                $q->whereBetween('event_date', [now()->format('Y-m-d'), $target->format('Y-m-d')])
                    ->whereHas('event', fn ($eq) => $eq->where('is_cancelled', false));
            })
            ->with(['installments', 'sale.event.creatorRole'])
            ->get();

        $digest = [];
        $sent = 0;

        foreach ($plans as $plan) {
            $outstanding = $plan->installments->whereNotIn('status', ['paid', 'cancelled']);

            if ($outstanding->isEmpty()) {
                continue;
            }

            $role = $this->roleFor($plan);
            if (! $role || ! $this->canEmail($role, $plan)) {
                continue;
            }

            $next = $outstanding->sortBy('sequence')->first();

            // One notice per plan, latched on the installment so a daily run does not repeat it.
            $claimed = false;
            DB::transaction(function () use ($next, &$claimed) {
                $locked = SaleInstallment::whereKey($next->id)->lockForUpdate()->first();
                if (! $locked || $locked->failed_notice_sent_at) {
                    return;
                }
                $locked->forceFill(['failed_notice_sent_at' => now()])->saveQuietly();
                $claimed = true;
            });

            if (! $claimed) {
                continue;
            }

            try {
                SendQueuedEmail::dispatch(
                    new InstallmentFinalNotice($plan, $next, $role),
                    $plan->sale->email,
                    $role->id,
                    app()->getLocale()
                );
                $sent++;

                $digest[$role->id.'|'.$plan->currency]['role'] = $role;
                $digest[$role->id.'|'.$plan->currency]['rows'][] = $this->digestRow($plan, $next);
                $digest[$role->id.'|'.$plan->currency]['currency'] = $plan->currency;
                $digest[$role->id.'|'.$plan->currency]['total'] = ($digest[$role->id.'|'.$plan->currency]['total'] ?? 0) + $plan->amountRemaining();
            } catch (\Throwable $e) {
                report($e);
                SaleInstallment::whereKey($next->id)->update(['failed_notice_sent_at' => null]);
            }
        }

        foreach ($digest as $entry) {
            $this->sendDigest($entry['role'], $entry['rows'], 'overdue', $entry['currency'], $entry['total']);
        }

        $this->info("Pre-event installment notices: {$sent} sent.");
    }

    private function digestRow(SaleInstallmentPlan $plan, ?SaleInstallment $installment): array
    {
        return [
            'name' => $plan->sale?->name,
            'email' => $plan->sale?->email,
            'event' => $plan->sale?->event?->name,
            'amount' => (float) ($installment?->amount ?? 0),
            'currency' => $plan->currency,
            'due_at' => $installment?->due_at?->format('j M Y'),
            'progress' => $plan->paidCount().' / '.$plan->installment_count,
            'remaining' => $plan->amountRemaining(),
        ];
    }

    private function sendDigest(Role $role, array $rows, string $kind, ?string $currency, float $total): void
    {
        // Opt-in by default when the key was never set, matching new_request: an organizer who
        // has not thought about it still needs to know money did not arrive.
        $editors = $role->belongsToMany(\App\Models\User::class)
            ->withPivot('level', 'notification_settings')
            ->whereIn('level', ['owner', 'admin'])
            ->get()
            ->filter(function ($user) {
                $settings = json_decode($user->pivot->notification_settings ?? '{}', true);

                return ! array_key_exists('installment_due', $settings) || ! empty($settings['installment_due']);
            });

        foreach ($editors as $editor) {
            try {
                SendQueuedEmail::dispatch(
                    new InstallmentOrganizerDigest($role, $rows, $kind, $currency, $total),
                    $editor->email,
                    $role->id,
                    $editor->language_code ?? app()->getLocale()
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    private function roleFor(?SaleInstallmentPlan $plan): ?Role
    {
        $event = $plan?->sale?->event;

        return $event?->getRoleWithEmailSettings() ?: $event?->creatorRole;
    }

    /**
     * Transport gate. Deliberately the RELAXED one that sendTicketEmail() uses rather than the
     * strict hasEmailSettings() check: a notice that money is about to leave someone's card is
     * transactional, and silently not sending it because the schedule never configured SMTP is
     * worse than sending it from the platform mailer.
     */
    private function canEmail(Role $role, ?SaleInstallmentPlan $plan): bool
    {
        if (! $plan?->sale?->email) {
            return false;
        }

        if (is_demo_role($role)) {
            return false;
        }

        if (str_contains($plan->sale->email, '@example.') || str_ends_with($plan->sale->email, '@test.com')) {
            return false;
        }

        if (! config('app.hosted')) {
            return ! in_array(config('mail.default'), ['log', 'array'], true);
        }

        return true;
    }
}
