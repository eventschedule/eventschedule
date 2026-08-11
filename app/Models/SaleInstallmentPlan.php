<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A buyer's agreement to pay one sale in monthly parts.
 *
 * The sale itself is `paid` from the first installment onward, so nothing downstream (the QR, the
 * check-in dashboard, the waitlist, the paid cascade) has to learn a new status. This row is the
 * only place that knows money is still owed.
 */
class SaleInstallmentPlan extends Model
{
    protected $fillable = [
        'sale_id',
        'currency',
        'total_amount',
        'amount_paid',
        'installment_count',
        'status',
        'stripe_account_id',
        'stripe_customer_id',
        'stripe_payment_method_id',
        'card_brand',
        'card_last4',
        'card_exp_month',
        'card_exp_year',
        'mandate_accepted_at',
        'mandate_ip',
        'delinquent_at',
        'secret',
        'unmatched_amount',
    ];

    /**
     * `secret` is a live payment link and the Stripe ids identify a reusable card on the owner's
     * connected account. WebhookService serializes Sale payloads with toApiData(true), and
     * SendWebhook::logDelivery() persists the whole body into webhook_deliveries.payload - a
     * UI-visible JSON column - so anything reachable from a serialized plan ends up stored in
     * plain text and returned from GET /api/sales. Hiding them here is the backstop; the API
     * summary is hand-built rather than dumping the model.
     */
    protected $hidden = [
        'secret',
        'stripe_customer_id',
        'stripe_payment_method_id',
    ];

    protected $casts = [
        'installment_count' => 'integer',
        'card_exp_month' => 'integer',
        'card_exp_year' => 'integer',
        'mandate_accepted_at' => 'datetime',
        'delinquent_at' => 'datetime',
    ];

    // No `decimal:` casts on the money columns. Eloquent returns a fixed-scale STRING for those
    // ("500.000" vs 500.0), which silently defeats every comparison against a freshly computed
    // float - the trap that stopped promotion refunds from ever reconciling. Sale::$casts leaves
    // payment_amount uncast for the same reason.

    protected static function booted()
    {
        static::creating(function ($plan) {
            if (! $plan->secret) {
                $plan->secret = Str::random(32);
            }
        });
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function installments()
    {
        return $this->hasMany(SaleInstallment::class)->orderBy('sequence');
    }

    public function amountRemaining(): float
    {
        return max(0, round((float) $this->total_amount - (float) $this->amount_paid, 3));
    }

    public function paidCount(): int
    {
        return $this->installments->where('status', 'paid')->count();
    }

    /**
     * The next installment the cron would act on. Excludes `paid` and `cancelled`, and
     * deliberately includes `awaiting_customer` and `failed` so the buyer's own "pay now" page
     * and the organizer's "charge now" both target the oldest thing actually owed.
     */
    public function nextDueInstallment(): ?SaleInstallment
    {
        return $this->installments
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sortBy('sequence')
            ->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Schema says `delinquent`; no user-facing surface may. Buyers see "on hold", organizers see
     * "overdue".
     */
    public function isDelinquent(): bool
    {
        return $this->status === 'delinquent';
    }

    /**
     * A card that expires before the last payment is a guaranteed future decline, and the only
     * one we can see coming.
     */
    public function cardExpiresBeforeFinalPayment(): bool
    {
        if (! $this->card_exp_year || ! $this->card_exp_month) {
            return false;
        }

        $final = $this->installments
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sortByDesc('sequence')
            ->first();

        if (! $final) {
            return false;
        }

        // Cards expire at the END of their stated month.
        $expiry = \Carbon\Carbon::createFromDate($this->card_exp_year, $this->card_exp_month, 1)->endOfMonth();

        return $expiry->lessThan($final->due_at);
    }

    /**
     * Hand-built so the model's secret and Stripe ids can never ride along. See $hidden.
     */
    public function toSummaryData(): array
    {
        $next = $this->nextDueInstallment();

        return [
            'status' => $this->status,
            'currency' => $this->currency,
            'total_amount' => (float) $this->total_amount,
            'amount_paid' => (float) $this->amount_paid,
            'amount_remaining' => $this->amountRemaining(),
            'installment_count' => (int) $this->installment_count,
            'installments_paid' => $this->paidCount(),
            'next_due_at' => $next?->due_at?->toDateString(),
            'next_amount' => $next ? (float) $next->amount : null,
        ];
    }
}
