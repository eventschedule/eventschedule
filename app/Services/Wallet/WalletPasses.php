<?php

namespace App\Services\Wallet;

use App\Models\Event;
use App\Models\Sale;

/**
 * What every wallet provider shares: which tickets may become a pass at all, and whether any
 * provider offers this one. Google (GoogleWalletService) and Apple (AppleWalletService) each add
 * only "am I configured" on top, so a badge of one can never appear for a ticket the other's route,
 * or the door, would treat differently.
 */
class WalletPasses
{
    /**
     * Whether this particular sale may be offered as a wallet pass, by any provider.
     *
     * One predicate for the buttons on the ticket page, the badges in the email, the badges on the
     * order page and both routes, so they cannot drift.
     *
     * is_cancelled is not redundant with the status check: a cancelled EVENT leaves its sales
     * `paid`, which is the trap ticket/order.blade.php already documents ("the buyer saw a live
     * ticket with a working code for an event that is not happening"). It matters more here than
     * on a web page, because a wallet pass then sits on the phone indefinitely.
     *
     * The delinquency arm is DELIBERATELY STRICTER THAN THE DOOR, and that is not an oversight to
     * be tidied up. TicketController::scanned() returns an amber 'overdue' with an "admit anyway"
     * override rather than refusing, because turning a paying guest away over one late instalment
     * is the organizer's call. A wallet pass is different in kind: it is a snapshot that is never
     * patched, so issuing one would put a permanently valid-looking ticket on the phone of someone
     * who may never pay. Refusing the pass leaves the on-page QR, and the override, untouched.
     *
     * appointment_type_id mirrors what TicketController::view() and EmailService already decide:
     * bookings have their own manage page and their own mailables, and never enter the QR ticket
     * flow. Without this a route would still mint a pass for one via a hand-built URL.
     */
    public static function eligible(?Sale $sale, ?Event $event): bool
    {
        if (! $sale || ! $event) {
            return false;
        }

        return $sale->status === 'paid'
            && ! $sale->is_deleted
            && ! $event->is_cancelled
            && ! $event->appointment_type_id
            && ! $sale->isInstallmentDelinquent();
    }

    /** Whether any configured provider offers this sale as a pass: the gate around a block of badges. */
    public static function anyOffered(?Sale $sale, ?Event $event): bool
    {
        return GoogleWalletService::canOffer($sale, $event) || AppleWalletService::canOffer($sale, $event);
    }
}
