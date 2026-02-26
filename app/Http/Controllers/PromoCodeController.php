<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PromoCode;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function validate(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
            'code' => 'required|string|max:50',
            'tickets' => 'required|array',
        ]);

        $eventId = UrlUtils::decodeId($request->event_id);
        $event = Event::with('tickets')->find($eventId);

        if (! $event) {
            return response()->json([
                'valid' => false,
                'message' => __('messages.promo_code_invalid'),
            ]);
        }

        $promoCode = PromoCode::where('event_id', $eventId)
            ->whereRaw('LOWER(code) = ?', [strtolower($request->code)])
            ->first();

        if (! $promoCode || ! $promoCode->isValid()) {
            $message = __('messages.promo_code_invalid');
            if ($promoCode) {
                if ($promoCode->expires_at && $promoCode->expires_at->isPast()) {
                    $message = __('messages.promo_code_expired');
                } elseif ($promoCode->max_uses !== null && $promoCode->times_used >= $promoCode->max_uses) {
                    $message = __('messages.promo_code_limit_reached');
                }
            }

            return response()->json([
                'valid' => false,
                'message' => $message,
            ]);
        }

        // Build a temporary collection to calculate discount
        $saleTickets = collect();
        foreach ($request->tickets as $ticketId => $quantity) {
            $quantity = (int) $quantity;
            if ($quantity > 0) {
                $decodedId = UrlUtils::decodeId($ticketId);
                $ticket = $event->tickets->firstWhere('id', $decodedId);
                if ($ticket) {
                    $saleTickets->push((object) [
                        'ticket_id' => $decodedId,
                        'ticket' => $ticket,
                        'quantity' => $quantity,
                    ]);
                }
            }
        }

        if ($saleTickets->isEmpty()) {
            return response()->json([
                'valid' => false,
                'message' => __('messages.promo_code_invalid'),
            ]);
        }

        $discountAmount = $promoCode->calculateDiscount($saleTickets);

        if ($discountAmount <= 0) {
            return response()->json([
                'valid' => false,
                'message' => __('messages.promo_code_invalid'),
            ]);
        }

        $currencyCode = $event->ticket_currency_code;
        $discountDisplay = number_format($discountAmount, 2, '.', ',').' '.$currencyCode;

        return response()->json([
            'valid' => true,
            'discount_amount' => $discountAmount,
            'discount_display' => $discountDisplay,
            'message' => __('messages.promo_code_applied'),
        ]);
    }
}
