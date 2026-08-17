<?php

namespace App\Services\Payments;

use App\Models\Event;
use App\Models\Sale;
use App\Models\User;

/**
 * Everything a gateway needs to start a payment, so drivers never reach back into the controller.
 *
 * The sale rows are already committed by the time this is built - TicketController::checkout()
 * prices and saves every leg inside its own transaction first - so a driver may read totals freely
 * but must not assume it can still change what was bought.
 */
class CheckoutContext
{
    public function __construct(
        public readonly Sale $sale,
        public readonly Event $event,
        public readonly string $subdomain,
        public readonly bool $isEmbed = false,
    ) {}

    /**
     * What the buyer owes for this checkout.
     *
     * An order primary is charged for the WHOLE order in one go, so its total spans every leg;
     * anything else is charged for its own leg plus that leg's guest seats. This is the same rule
     * the Stripe webhook reconciles against, and a driver that charges the wrong one of the two
     * lands every sale in `amount_mismatch`.
     */
    public function total(): float
    {
        return (float) ($this->sale->isOrderPrimary()
            ? $this->sale->orderTotalPayment()
            : $this->sale->legTotalPayment());
    }

    /**
     * The schedule owner whose gateway credentials settle this sale, which is the account the
     * money reaches - not the buyer, and not the schedule.
     */
    public function owner(): ?User
    {
        return $this->event->user;
    }

    public function currency(): string
    {
        return $this->event->ticket_currency_code ?: 'USD';
    }
}
