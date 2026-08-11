<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One scheduled payment within a plan. Installment 1 is taken at checkout; the rest are charged
 * off-session by app:charge-installments.
 */
class SaleInstallment extends Model
{
    /** Retry backoff in days after a genuine decline, indexed by attempt number. */
    public const RETRY_DAYS = [1, 3, 5];

    public const MAX_ATTEMPTS = 3;

    protected $fillable = [
        'sale_installment_plan_id',
        'sequence',
        'amount',
        'due_at',
        'status',
        'paid_at',
        'transaction_reference',
        'attempts',
        'next_attempt_at',
        'last_error',
        'reminder_sent_at',
        'failed_notice_sent_at',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'attempts' => 'integer',
        'due_at' => 'datetime',
        'next_attempt_at' => 'datetime',
        'paid_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'failed_notice_sent_at' => 'datetime',
    ];

    // `amount` is deliberately uncast - see the note in SaleInstallmentPlan.

    public function plan()
    {
        return $this->belongsTo(SaleInstallmentPlan::class, 'sale_installment_plan_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return ! in_array($this->status, ['paid', 'cancelled'], true)
            && $this->due_at
            && $this->due_at->isPast();
    }

    /**
     * Whether the cron may still retry this automatically. An SCA challenge is deliberately NOT
     * retryable: it fails identically every time, so the only way forward is the buyer opening
     * the payment link.
     */
    public function canRetry(): bool
    {
        return $this->status === 'scheduled' && $this->attempts < self::MAX_ATTEMPTS;
    }

    /**
     * Maps a Stripe decline code to a short phrase an organizer can act on. Never show
     * `last_error` raw: "Waiting for the buyer to confirm with their bank" and "Card declined"
     * call for completely different responses, and the raw string buries the difference.
     */
    public function humanErrorKey(): ?string
    {
        // Keyed on the RECORDED REASON, not on the status. `awaiting_customer` is reached both by
        // an SCA challenge and by the organizer revoking Stripe access, and reporting the latter
        // as "waiting for the buyer to confirm with their bank" sent organizers chasing a buyer
        // who had done nothing wrong while their own Stripe connection was the problem.
        if ($this->last_error === 'connect_revoked') {
            return 'messages.installment_error_connect_revoked';
        }

        if ($this->status === 'awaiting_reconciliation') {
            return 'messages.installment_error_reconcile';
        }

        if ($this->status === 'awaiting_customer') {
            return 'messages.installment_error_auth';
        }

        if (! $this->last_error) {
            return null;
        }

        return match (true) {
            str_contains($this->last_error, 'expired_card') => 'messages.installment_error_expired',
            str_contains($this->last_error, 'insufficient_funds') => 'messages.installment_error_funds',
            default => 'messages.installment_error_declined',
        };
    }
}
