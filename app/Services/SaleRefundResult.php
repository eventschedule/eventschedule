<?php

namespace App\Services;

use App\Models\SaleRefund;

/**
 * What SaleRefundService did, in a shape a controller can act on without re-deriving it.
 *
 * A value object rather than a bare outcome string (which is what SaleSettlementService returns)
 * because a refund has two things a caller needs beyond the verdict: the ledger row, so the sale
 * page can show what happened, and a translated message, so the controller does not have to keep
 * its own map of every failure mode in sync with this service.
 */
class SaleRefundResult
{
    /** Money moved and the sale is now fully refunded. */
    public const REFUNDED = 'refunded';

    /** Money moved; the sale keeps its `paid` status because a balance remains. */
    public const PARTIALLY_REFUNDED = 'partially_refunded';

    /**
     * This rail cannot send money back, or this row has no usable gateway reference.
     *
     * Not an error: it is the answer for cash, RSVPs and any sale marked paid by hand. The caller
     * falls back to the status-only path and tells the owner the money was not moved.
     */
    public const UNSUPPORTED = 'unsupported';

    /** Wrong status, or the sale went away between the click and the lock. */
    public const NOT_REFUNDABLE = 'not_refundable';

    /** Zero, negative, or more than the sale has left to give back. */
    public const INVALID_AMOUNT = 'invalid_amount';

    /** The gateway processed the request and refused it. Nothing moved. */
    public const FAILED = 'failed';

    /**
     * A request carrying an idempotency key we have already claimed, whose first attempt has not
     * finished yet.
     *
     * Returned instead of claiming a second time, which is what a double-clicked Refund or a
     * retried fetch would otherwise do. The caller reports it as a conflict; it is not a failure
     * and the first attempt is still the one that decides the outcome.
     */
    public const IN_PROGRESS = 'in_progress';

    /**
     * The gateway may or may not have moved the money.
     *
     * Never retried automatically: idempotency keys expire, so a later retry with a fresh key is
     * how one refund becomes two. A person resolves it against the gateway's dashboard.
     */
    public const NEEDS_RECONCILIATION = 'needs_reconciliation';

    public function __construct(
        public readonly string $status,
        public readonly ?SaleRefund $refund = null,
        public readonly ?string $message = null,
    ) {}

    public function moved(): bool
    {
        return in_array($this->status, [self::REFUNDED, self::PARTIALLY_REFUNDED], true);
    }

    /** True when the caller should fall back to flipping the status without moving money. */
    public function shouldFallBackToStatusOnly(): bool
    {
        return $this->status === self::UNSUPPORTED;
    }
}
