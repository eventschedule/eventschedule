<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEventsDaily;
use App\Models\Event;
use App\Models\Sale;
use App\Models\User;
use App\Utils\InvoiceNinja;
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
            return response()->json(['status' => 'error', 'message' => 'Sale not found'], 400);
        }

        // Use lockForUpdate to prevent race conditions from webhook retries
        \DB::transaction(function () use ($sale, $payload, $invoiceId) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status !== 'unpaid') {
                return;
            }

            $webhookAmount = $payload['paymentables'][0]['amount'];

            // Validate that the webhook amount matches the expected sale amount
            $expectedAmount = $sale->payment_amount;
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

                return;
            }

            $sale->payment_amount = $webhookAmount;
            $sale->status = 'paid';
            $sale->save();

            AnalyticsEventsDaily::incrementSale($sale->event_id, $webhookAmount);
            if ($sale->discount_amount > 0) {
                AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $sale->discount_amount);
            }
        });

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

        // Mark sale as paid with row locking
        \DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status !== 'unpaid') {
                return;
            }

            $sale->status = 'paid';
            $sale->save();

            AnalyticsEventsDaily::incrementSale($sale->event_id, $sale->payment_amount);
            if ($sale->discount_amount > 0) {
                AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $sale->discount_amount);
            }
        });

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

        // Mark sale as paid with row locking
        \DB::transaction(function () use ($sale, $event, $payload) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status !== 'unpaid') {
                return;
            }

            // Create SaleTickets from webhook line items (payment link mode)
            if ($sale->saleTickets()->count() === 0) {
                $lineItems = $payload['line_items'] ?? [];
                foreach ($lineItems as $lineItem) {
                    $productId = $lineItem['product_id'] ?? null;
                    $quantity = (int) ($lineItem['quantity'] ?? 0);
                    if (! $productId || $quantity <= 0) {
                        continue;
                    }

                    $ticket = $event->tickets()->where('invoiceninja_product_id', $productId)->first();
                    if (! $ticket) {
                        continue;
                    }

                    $sale->saleTickets()->create([
                        'ticket_id' => $ticket->id,
                        'quantity' => $quantity,
                        'seats' => json_encode(array_fill(1, $quantity, null)),
                    ]);
                }

                $sale->payment_amount = $sale->calculateTotal();

                // Check if a promo code discount was applied on the IN purchase page
                $invoiceDiscount = (float) ($payload['discount'] ?? 0);
                if ($invoiceDiscount > 0) {
                    $isAmountDiscount = $payload['is_amount_discount'] ?? true;
                    if ($isAmountDiscount) {
                        $discountAmount = $invoiceDiscount;
                    } else {
                        $discountAmount = round($sale->payment_amount * ($invoiceDiscount / 100), 2);
                    }

                    if ($discountAmount > 0) {
                        $promoCode = $event->promoCodes()->where('is_active', true)->first();
                        if ($promoCode) {
                            $sale->promo_code_id = $promoCode->id;
                            $sale->discount_amount = $discountAmount;
                            $sale->payment_amount = max(0, $sale->payment_amount - $discountAmount);
                            $promoCode->increment('times_used');
                        }
                    }
                }
            }

            $sale->status = 'paid';
            $sale->save();

            AnalyticsEventsDaily::incrementSale($sale->event_id, $sale->payment_amount);
            if ($sale->discount_amount > 0) {
                AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $sale->discount_amount);
            }
        });

        return response()->json(['status' => 'success']);
    }
}
