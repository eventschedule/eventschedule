<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One attempt to send money back for a sale.
 *
 * A ledger rather than a `refunded_amount` column on `sales`, because it has to carry partial
 * refunds, per-installment legs (a plan is N charges with their own PaymentIntents), an
 * unknown-outcome state, and the idempotency key that makes a double-clicked Refund safe.
 *
 * Rows in every status except `failed` hold their amount against the sale's remaining refundable
 * balance - see SaleRefundService. A `pending` row is a CLAIM taken under the sale's lock before
 * the gateway is called, so two people refunding at once cannot both pass the ceiling check.
 */
class SaleRefund extends Model
{
    /**
     * Statuses that still hold their amount against the refundable balance.
     *
     * Everything except `failed`. A `pending` refund may yet succeed and an
     * `awaiting_reconciliation` one may already have moved the money, so neither releases its
     * claim; only a definite failure gives the amount back.
     */
    public const CLAIMING_STATUSES = ['pending', 'succeeded', 'awaiting_reconciliation'];

    protected $fillable = [
        'sale_id',
        'sale_installment_id',
        'amount',
        'currency_code',
        'gateway',
        'gateway_refund_id',
        'status',
        'idempotency_key',
        'user_id',
        'reason',
        'error_code',
        'last_error',
    ];

    /**
     * `amount` is deliberately NOT cast to `decimal:3`.
     *
     * A decimal cast makes Eloquent hand back a fixed-scale STRING, so comparing it against a
     * freshly computed float always reports a difference - the trap that stopped promotion spend
     * ever reconciling. `sales.payment_amount` is uncast for the same reason; cast both sides
     * numerically at the point of comparison instead.
     */
    protected $casts = [];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(SaleInstallment::class, 'sale_installment_id');
    }

    public function isSucceeded(): bool
    {
        return $this->status === 'succeeded';
    }

    /**
     * Needs a person to compare it against the gateway's own dashboard.
     *
     * Never resolved by re-issuing the call: the money may already have moved and the idempotency
     * key that would have made a retry safe expires after 24 hours.
     */
    public function needsReconciliation(): bool
    {
        return $this->status === 'awaiting_reconciliation';
    }
}
