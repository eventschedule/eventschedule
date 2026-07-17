<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEventsDaily;
use App\Models\Event;
use App\Models\GiftCard;
use App\Models\Sale;
use App\Models\User;
use App\Services\AuditService;
use App\Services\EmailService;
use App\Services\WebhookService;
use App\Utils\InvoiceNinja;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;

class InvoiceNinjaController extends Controller
{
    public function unlink()
    {
        $user = auth()->user();

        $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);
        $company = $invoiceNinja->getCompany();

        foreach ($company['webhooks'] as $webhook) {
            if ($webhook['target_url'] == route('invoiceninja.webhook', ['secret' => $user->invoiceninja_webhook_secret])) {
                $invoiceNinja->deleteWebhook($webhook['id']);
            }
        }

        $user->invoiceninja_api_key = null;
        $user->invoiceninja_company_name = null;
        $user->invoiceninja_webhook_secret = null;
        $user->invoiceninja_mode = 'invoice';
        $user->save();

        return redirect()->back()->with('message', __('messages.invoiceninja_unlinked'));
    }

    public function webhook(Request $request, $secret = null)
    {
        $secretFromUrl = $secret !== null;

        // Prefer Authorization header over URL parameter for security
        // (URL secrets appear in logs and Referer headers)
        $headerSecret = $request->header('X-Webhook-Secret') ?? $request->header('Authorization');
        if ($headerSecret) {
            // Remove "Bearer " prefix if present
            $headerSecret = preg_replace('/^Bearer\s+/i', '', $headerSecret);
            $secret = $headerSecret;
            $secretFromUrl = false;
        }

        if (! $secret) {
            return response()->json(['status' => 'error', 'message' => 'Missing webhook secret'], 400);
        }

        if ($secretFromUrl) {
            \Log::warning('Invoice Ninja webhook authenticated via URL path parameter (deprecated). Use X-Webhook-Secret header instead.');
        }

        // Find user with matching webhook secret using constant-time comparison
        // to prevent timing attacks that could enumerate valid secrets
        $user = null;
        $users = User::whereNotNull('invoiceninja_webhook_secret')->get(['id', 'invoiceninja_webhook_secret']);
        foreach ($users as $candidate) {
            if (hash_equals($candidate->invoiceninja_webhook_secret, $secret)) {
                $user = User::find($candidate->id);
                break;
            }
        }

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Invalid webhook secret'], 400);
        }

        $payload = json_decode($request->getContent(), true);

        if (empty($payload['paymentables']) || count($payload['paymentables']) == 0) {
            return response()->json(['status' => 'error', 'message' => 'No paymentables found'], 400);
        }

        $invoiceId = $payload['paymentables'][0]['invoice_id'];

        if (! $invoiceId) {
            return response()->json(['status' => 'error', 'message' => 'No invoice_id found'], 400);
        }

        $sale = Sale::where('payment_method', 'invoiceninja')
            ->where('transaction_reference', $invoiceId)
            ->whereHas('event', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        if (! $sale) {
            // Not a ticket sale - the invoice may belong to a gift card purchase
            $giftCard = GiftCard::where('payment_method', 'invoiceninja')
                ->where('transaction_reference', $invoiceId)
                ->whereHas('role', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->first();

            if ($giftCard) {
                return $this->handleGiftCardPayment($giftCard, $payload, $invoiceId);
            }

            return response()->json(['status' => 'error', 'message' => 'Sale not found'], 400);
        }

        $didTransitionToPaid = false;

        // Use lockForUpdate to prevent race conditions from webhook retries
        \DB::transaction(function () use ($sale, $payload, $invoiceId, &$didTransitionToPaid) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status !== 'unpaid') {
                return;
            }

            $webhookAmount = $payload['paymentables'][0]['amount'];

            // For grouped purchases (individual tickets) the buyer pays the group total in one invoice.
            $expectedAmount = $sale->isPrimarySale() ? $sale->groupTotalPayment() : (float) $sale->payment_amount;
            $amountDifference = abs($webhookAmount - $expectedAmount);

            // Allow small tolerance for floating point differences (e.g., 0.01)
            if ($amountDifference > 0.01) {
                \Log::warning('Payment amount mismatch in Invoice Ninja webhook', [
                    'sale_id' => $sale->id,
                    'expected_amount' => $expectedAmount,
                    'webhook_amount' => $webhookAmount,
                    'difference' => $amountDifference,
                    'invoice_id' => $invoiceId,
                ]);

                $sale->status = 'amount_mismatch';
                $sale->transaction_reference = $invoiceId;
                $sale->save();

                AuditService::log(AuditService::SALE_PAID, $sale->user_id, 'Sale', $sale->id,
                    ['status' => 'unpaid'], ['status' => 'amount_mismatch'], 'invoiceninja_amount_mismatch:event_id:'.$sale->event_id);

                return;
            }

            // Preserve per-seat payment_amount on grouped primaries; only overwrite for ungrouped sales
            if (! $sale->isPrimarySale()) {
                $sale->payment_amount = $webhookAmount;
            }
            $sale->status = 'paid';
            $sale->save();
            $didTransitionToPaid = true;

            AuditService::log(AuditService::SALE_PAID, $sale->user_id, 'Sale', $sale->id,
                ['status' => 'unpaid'], ['status' => 'paid'], 'invoiceninja:event_id:'.$sale->event_id);

            AnalyticsEventsDaily::incrementSale($sale->event_id, $webhookAmount);
            $promoTotal = $sale->isPrimarySale() ? $sale->groupTotalDiscount() : (float) ($sale->discount_amount ?? 0);
            if ($promoTotal > 0) {
                AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $promoTotal);
            }

            WebhookService::dispatch('sale.paid', $sale);
            if ($sale->group_id && $sale->isPrimarySale()) {
                foreach (Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->get() as $gs) {
                    WebhookService::dispatch('sale.paid', $gs);
                }
            }
        });

        if ($didTransitionToPaid) {
            (new EmailService)->sendSaleConfirmationEmails($sale->refresh());
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Activate a gift card once its Invoice Ninja invoice is paid.
     * Locked, idempotent, and amount-verified (mirrors the sale path above).
     */
    private function handleGiftCardPayment(GiftCard $giftCard, array $payload, $invoiceId)
    {
        $didActivate = false;

        \DB::transaction(function () use ($giftCard, $payload, $invoiceId, &$didActivate) {
            $giftCard = GiftCard::lockForUpdate()->find($giftCard->id);
            if ($giftCard->status !== 'unpaid') {
                return;
            }

            $webhookAmount = $payload['paymentables'][0]['amount'];
            $amountDifference = abs($webhookAmount - (float) $giftCard->amount);

            if ($amountDifference > 0.01) {
                \Log::warning('Payment amount mismatch in Invoice Ninja gift card webhook', [
                    'gift_card_id' => $giftCard->id,
                    'expected_amount' => (float) $giftCard->amount,
                    'webhook_amount' => $webhookAmount,
                    'invoice_id' => $invoiceId,
                ]);

                $giftCard->status = 'amount_mismatch';
                $giftCard->save();

                AuditService::log(AuditService::GIFT_CARD_PAID, null, 'GiftCard', $giftCard->id,
                    ['status' => 'unpaid'], ['status' => 'amount_mismatch'], 'invoiceninja_amount_mismatch:role_id:'.$giftCard->role_id);

                return;
            }

            $giftCard->activate();
            $didActivate = true;

            AuditService::log(AuditService::GIFT_CARD_PAID, null, 'GiftCard', $giftCard->id,
                ['status' => 'unpaid'], ['status' => 'active'], 'invoiceninja:role_id:'.$giftCard->role_id);
        });

        if ($didActivate) {
            (new EmailService)->sendGiftCardEmails($giftCard->refresh());
        }

        return response()->json(['status' => 'success']);
    }

    public function purchaseWebhook(Request $request, $sale)
    {
        $sale = Sale::where('id', UrlUtils::decodeId($sale))
            ->where('payment_method', 'invoiceninja')
            ->where('transaction_reference', 'LIKE', 'sub:%')
            ->first();

        if (! $sale) {
            return response()->json(['status' => 'error', 'message' => 'Sale not found'], 400);
        }

        $user = $sale->event->user;

        // Validate webhook secret
        $headerSecret = $request->header('X-Webhook-Secret');
        if (! $headerSecret || ! $user->invoiceninja_webhook_secret || ! hash_equals($user->invoiceninja_webhook_secret, $headerSecret)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid webhook secret'], 400);
        }

        $didTransitionToPaid = false;

        // Mark sale as paid with row locking
        \DB::transaction(function () use ($sale, &$didTransitionToPaid) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status !== 'unpaid') {
                return;
            }

            $sale->status = 'paid';
            $sale->save();
            $didTransitionToPaid = true;

            AuditService::log(AuditService::SALE_PAID, $sale->user_id, 'Sale', $sale->id,
                ['status' => 'unpaid'], ['status' => 'paid'], 'invoiceninja_purchase:event_id:'.$sale->event_id);

            $analyticsAmount = $sale->isPrimarySale() ? $sale->groupTotalPayment() : (float) $sale->payment_amount;
            AnalyticsEventsDaily::incrementSale($sale->event_id, $analyticsAmount);
            $promoTotal = $sale->isPrimarySale() ? $sale->groupTotalDiscount() : (float) ($sale->discount_amount ?? 0);
            if ($promoTotal > 0) {
                AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $promoTotal);
            }

            WebhookService::dispatch('sale.paid', $sale);
            if ($sale->group_id && $sale->isPrimarySale()) {
                foreach (Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->get() as $gs) {
                    WebhookService::dispatch('sale.paid', $gs);
                }
            }
        });

        if ($didTransitionToPaid) {
            (new EmailService)->sendSaleConfirmationEmails($sale->refresh());
        }

        return response()->json(['status' => 'success']);
    }

    public function eventPurchaseWebhook(Request $request, $event)
    {
        $event = Event::find(UrlUtils::decodeId($event));

        if (! $event) {
            return response()->json(['status' => 'error', 'message' => 'Event not found'], 400);
        }

        $user = $event->user;

        // Validate webhook secret
        $headerSecret = $request->header('X-Webhook-Secret');
        if (! $headerSecret || ! $user->invoiceninja_webhook_secret || ! hash_equals($user->invoiceninja_webhook_secret, $headerSecret)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid webhook secret'], 400);
        }

        // Extract client email from webhook payload
        $payload = json_decode($request->getContent(), true);
        $clientEmail = null;
        if (isset($payload['client']['contacts']) && count($payload['client']['contacts']) > 0) {
            $clientEmail = $payload['client']['contacts'][0]['email'] ?? null;
        }

        if (! $clientEmail) {
            \Log::warning('Invoice Ninja event purchase webhook missing client email', [
                'event_id' => $event->id,
            ]);

            return response()->json(['status' => 'error', 'message' => 'Missing client email'], 400);
        }

        // Find the most recent unpaid sale for this event matching the client email
        $sale = Sale::where('event_id', $event->id)
            ->where('payment_method', 'invoiceninja')
            ->where('email', $clientEmail)
            ->where('status', 'unpaid')
            ->where('transaction_reference', 'LIKE', 'sub:%')
            ->orderBy('id', 'desc')
            ->first();

        if (! $sale) {
            return response()->json(['status' => 'error', 'message' => 'Sale not found'], 400);
        }

        $didTransitionToPaid = false;

        // Mark sale as paid with row locking
        \DB::transaction(function () use ($sale, $event, $payload, &$didTransitionToPaid) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status !== 'unpaid') {
                return;
            }

            // Create SaleTickets from webhook line items (payment link mode)
            if ($sale->saleTickets()->count() === 0) {
                $lineItems = $payload['line_items'] ?? [];

                // Lock ticket rows to prevent overselling (same pattern as TicketController::checkout)
                $lockedTickets = $event->tickets()->lockForUpdate()->get()
                    ->merge($event->addons()->lockForUpdate()->get());

                foreach ($lineItems as $lineItem) {
                    $productId = $lineItem['product_id'] ?? null;
                    $quantity = (int) ($lineItem['quantity'] ?? 0);
                    if (! $productId || $quantity <= 0) {
                        continue;
                    }

                    $ticket = $lockedTickets->where('invoiceninja_product_id', $productId)->first();
                    if (! $ticket) {
                        continue;
                    }

                    // Check availability - cap quantity to what's remaining (customer already paid)
                    if ($ticket->quantity > 0) {
                        if (! $ticket->is_addon && $event->total_tickets_mode === 'combined' && $event->hasSameTicketQuantities()) {
                            $totalSold = $lockedTickets->filter(fn ($t) => ! $t->is_addon && ! $t->is_pass)->sum(function ($t) use ($sale) {
                                $ticketSold = $t->sold ? (json_decode($t->sold, true) ?? []) : [];

                                return $ticketSold[$sale->event_date] ?? 0;
                            });
                            // Pass holders who booked this occurrence in advance occupy shared seats too.
                            $totalSold += $sale->event_date ? $event->passReservedSeats($sale->event_date) : 0;
                            $totalQuantity = $event->getSameTicketQuantity();
                            $remaining = $totalQuantity - $totalSold;
                        } else {
                            $sold = $ticket->sold ? json_decode($ticket->sold, true) : [];
                            $soldCount = $sold[$sale->event_date] ?? 0;
                            $remaining = $ticket->quantity - $soldCount;
                            // Honor pass advance-bookings against the shared per-occurrence
                            // house for regular seat tickets (combined+equal handled above).
                            if (! $ticket->is_addon && ! $ticket->is_pass && $sale->event_date) {
                                $event->setRelation('tickets', $lockedTickets->reject(fn ($t) => $t->is_addon)->values());
                                $houseRemaining = $event->occurrenceSeatsRemaining($sale->event_date);
                                if ($houseRemaining !== null) {
                                    $remaining = min($remaining, $houseRemaining);
                                }
                            }
                        }
                        if ($quantity > $remaining) {
                            \Log::warning('Invoice Ninja payment link oversell prevented', [
                                'event_id' => $event->id,
                                'ticket_id' => $ticket->id,
                                'requested' => $quantity,
                                'remaining' => $remaining,
                                'sale_id' => $sale->id,
                            ]);
                            $quantity = max(0, $remaining);
                            if ($quantity <= 0) {
                                continue;
                            }
                        }
                    }

                    $sale->saleTickets()->create([
                        'ticket_id' => $ticket->id,
                        'quantity' => $quantity,
                        'seats' => json_encode(array_fill(1, $quantity, null)),
                    ]);
                }

                $sale->payment_amount = $sale->calculateTotal();

                // Check if a discount was applied on the IN purchase page.
                // The payload doesn't include the promo code string, so we can't
                // reliably match to a specific PromoCode row - record the discount
                // amount for reporting but leave promo_code_id null.
                $invoiceDiscount = (float) ($payload['discount'] ?? 0);
                if ($invoiceDiscount > 0) {
                    $isAmountDiscount = $payload['is_amount_discount'] ?? true;
                    if ($isAmountDiscount) {
                        $discountAmount = $invoiceDiscount;
                    } else {
                        $decimals = MoneyUtils::decimalsFor($event->ticket_currency_code);
                        $discountAmount = round($sale->payment_amount * ($invoiceDiscount / 100), $decimals);
                    }

                    if ($discountAmount > 0) {
                        $sale->discount_amount = $discountAmount;
                        $sale->payment_amount = max(0, $sale->payment_amount - $discountAmount);
                    }
                }
            }

            // Reconcile the server-computed payment_amount against the invoice
            // total from the webhook payload. This defends against a tampered
            // payload (e.g. inflated discount or dropped line items) that would
            // otherwise mark the sale paid for less than we charged.
            $invoiceTotal = null;
            foreach (['amount', 'total', 'balance'] as $field) {
                if (isset($payload[$field]) && is_numeric($payload[$field])) {
                    $invoiceTotal = (float) $payload[$field];
                    break;
                }
            }

            if ($invoiceTotal !== null && abs($invoiceTotal - (float) $sale->payment_amount) > 0.01) {
                \Log::warning('Invoice Ninja event purchase webhook amount mismatch', [
                    'sale_id' => $sale->id,
                    'event_id' => $event->id,
                    'expected_amount' => $sale->payment_amount,
                    'invoice_total' => $invoiceTotal,
                ]);

                $sale->status = 'amount_mismatch';
                $sale->save();

                AuditService::log(AuditService::SALE_PAID, $sale->user_id, 'Sale', $sale->id,
                    ['status' => 'unpaid'], ['status' => 'amount_mismatch'], 'invoiceninja_event_purchase_amount_mismatch:event_id:'.$sale->event_id);

                return;
            }

            $sale->status = 'paid';
            $sale->save();
            $didTransitionToPaid = true;

            AuditService::log(AuditService::SALE_PAID, $sale->user_id, 'Sale', $sale->id,
                ['status' => 'unpaid'], ['status' => 'paid'], 'invoiceninja_event_purchase:event_id:'.$sale->event_id);

            AnalyticsEventsDaily::incrementSale($sale->event_id, $sale->payment_amount);
            if ($sale->discount_amount > 0) {
                AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $sale->discount_amount);
            }

            WebhookService::dispatch('sale.paid', $sale);
            if ($sale->group_id && $sale->isPrimarySale()) {
                foreach (Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->get() as $gs) {
                    WebhookService::dispatch('sale.paid', $gs);
                }
            }
        });

        if ($didTransitionToPaid) {
            (new EmailService)->sendSaleConfirmationEmails($sale->refresh());
        }

        return response()->json(['status' => 'success']);
    }
}
