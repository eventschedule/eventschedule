<?php

namespace App\Services;

use App\Models\Ticket;
use App\Utils\MoneyUtils;

/**
 * Per-line volume (bulk) discount for ticket checkout. Promo codes apply after these amounts.
 */
class TicketVolumeDiscount
{
    /**
     * Normalize raw request/array into a valid rule or null.
     */
    public static function normalizeRule(?array $rule): ?array
    {
        if (! is_array($rule) || empty($rule)) {
            return null;
        }

        $minQty = isset($rule['min_quantity']) ? (int) $rule['min_quantity'] : 0;
        $type = $rule['type'] ?? '';
        $value = isset($rule['value']) ? (float) $rule['value'] : 0;

        if ($minQty < 2 || $value <= 0 || ! in_array($type, ['percentage', 'fixed'], true)) {
            return null;
        }

        if ($type === 'percentage' && $value > 100) {
            return null;
        }

        return [
            'min_quantity' => $minQty,
            'type' => $type,
            'value' => $value,
        ];
    }

    /**
     * Public fields for guest JSON (toData).
     */
    public static function toGuestPayload(?array $rule): ?array
    {
        $n = self::normalizeRule($rule);

        return $n;
    }

    public static function decimalsForTicket(Ticket $ticket): int
    {
        return MoneyUtils::decimalsFor($ticket->event?->ticket_currency_code);
    }

    public static function volumeDiscountAmount(?array $rule, float $unitPrice, int $quantity, int $decimals): float
    {
        $rule = self::normalizeRule($rule);
        if (! $rule || $quantity < 1 || $unitPrice <= 0) {
            return 0.0;
        }

        if ($quantity < $rule['min_quantity']) {
            return 0.0;
        }

        $gross = $unitPrice * $quantity;
        $value = max(0.0, (float) $rule['value']);

        if ($rule['type'] === 'percentage') {
            $value = min($value, 100.0);
            $discount = $gross * ($value / 100.0);
        } else {
            $discount = $value;
        }

        return round(min($discount, $gross), $decimals);
    }

    public static function lineSubtotalAfterVolume(?array $rule, float $unitPrice, int $quantity, int $decimals): float
    {
        $gross = $unitPrice * $quantity;
        $vol = self::volumeDiscountAmount($rule, $unitPrice, $quantity, $decimals);

        return round(max(0, $gross - $vol), $decimals);
    }

    /**
     * Volume savings for a cart keyed by encoded ticket id (guest checkout request shape).
     *
     * @param  array<string, int|string>  $encodedIdToQty
     */
    public static function totalVolumeDiscountForTicketQuantities(\App\Models\Event $event, array $encodedIdToQty): float
    {
        $sum = 0.0;
        foreach ($encodedIdToQty as $ticketId => $quantity) {
            $qty = (int) $quantity;
            if ($qty <= 0) {
                continue;
            }
            $decodedId = \App\Utils\UrlUtils::decodeId($ticketId);
            $ticket = $event->tickets()->find($decodedId);
            if (! $ticket || $ticket->is_addon) {
                continue;
            }
            $ticket->setRelation('event', $event);
            $sum += $ticket->volumeDiscountAmountForQuantity($qty);
        }

        return $sum;
    }

    /**
     * @param  iterable<\App\Models\SaleTicket|\stdClass>  $saleTickets  items with ticket_id, quantity, ticket
     */
    public static function totalVolumeDiscountForSaleTickets(iterable $saleTickets): float
    {
        $sum = 0.0;
        foreach ($saleTickets as $saleTicket) {
            if (! $saleTicket->ticket || $saleTicket->ticket->is_addon) {
                continue;
            }
            $sum += $saleTicket->ticket->volumeDiscountAmountForQuantity((int) $saleTicket->quantity);
        }

        return $sum;
    }
}
