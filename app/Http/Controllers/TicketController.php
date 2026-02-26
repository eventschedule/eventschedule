<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketCheckoutRequest;
use App\Models\AnalyticsEventsDaily;
use App\Models\Event;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\AuditService;
use App\Services\EmailService;
use App\Utils\InvoiceNinja;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class TicketController extends Controller
{
    public function tickets()
    {
        $user = auth()->user();
        $past = request()->query('past') == 1;

        $query = Sale::with('event', 'saleTickets')
            ->where('user_id', $user->id)
            ->where('is_deleted', false);

        if ($past) {
            $query->where(function ($q) {
                $q->where('event_date', '<', now()->subDay()->startOfDay())
                    ->orWhereHas('event', function ($eq) {
                        $eq->where('starts_at', '<', now()->subDay()->startOfDay());
                    });
            });
            $sales = $query->orderBy('event_date', 'DESC')->get();
        } else {
            $query->where('event_date', '>=', now()->subDay()->startOfDay())
                ->whereHas('event', function ($eq) {
                    $eq->where('starts_at', '>=', now()->subDay()->startOfDay());
                });
            $sales = $query->orderBy('event_date', 'ASC')->get();

            $hasPastTickets = Sale::where('user_id', $user->id)
                ->where('is_deleted', false)
                ->where(function ($q) {
                    $q->where('event_date', '<', now()->subDay()->startOfDay())
                        ->orWhereHas('event', function ($eq) {
                            $eq->where('starts_at', '<', now()->subDay()->startOfDay());
                        });
                })
                ->exists();
        }

        return view('ticket.index', compact('sales', 'past') + ($past ? [] : compact('hasPastTickets')));
    }

    public function sales()
    {
        $user = auth()->user();
        $filter = strtolower(request()->filter);

        $query = Sale::with('event', 'saleTickets', 'promoCode')
            ->where('is_deleted', false)
            ->whereHas('event', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });

        if ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('status', 'LIKE', "%{$filter}%")
                    ->orWhere('transaction_reference', 'LIKE', "%{$filter}%")
                    ->orWhere('email', 'LIKE', "%{$filter}%")
                    ->orWhere('name', 'LIKE', "%{$filter}%")
                    ->orWhereHas('event', function ($q) use ($filter) {
                        $q->where('name', 'LIKE', "%{$filter}%");
                    });
            });
        }

        $count = $query->count();
        $sales = $query->orderBy('created_at', 'DESC')
            ->paginate(50, ['*'], 'page');

        if (request()->ajax()) {
            return view('ticket.sales_table', compact('sales'));
        } else {
            return view('ticket.sales', compact('sales', 'count'));
        }
    }

    public function checkout(TicketCheckoutRequest $request, $subdomain)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($request->event_id));

        $role = Role::subdomain($subdomain)->firstOrFail();
        if (! $event->roles()->wherePivot('role_id', $role->id)->exists()) {
            abort(403);
        }

        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        if ($event->is_private && ! $isMemberOrAdmin) {
            abort(404);
        }

        // Verify event can sell tickets (checks past dates, tickets_enabled, and Pro plan)
        if (! $event->canSellTickets($request->event_date)) {
            return back()->with('error', __('messages.tickets_not_available'));
        }

        if (! $user && $request->create_account && config('app.hosted')) {

            $utmParams = session('utm_params', []);

            // Fall back to cookie if session has no UTM data
            if (empty($utmParams) && $request->cookie('utm_params')) {
                $utmParams = json_decode($request->cookie('utm_params'), true) ?? [];
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'timezone' => $event->user->timezone,
                'language_code' => $event->user->language_code,
                'utm_source' => $utmParams['utm_source'] ?? null,
                'utm_medium' => $utmParams['utm_medium'] ?? null,
                'utm_campaign' => $utmParams['utm_campaign'] ?? null,
                'utm_content' => $utmParams['utm_content'] ?? null,
                'utm_term' => $utmParams['utm_term'] ?? null,
                'referrer_url' => session('utm_referrer_url') ?? $request->cookie('utm_referrer_url'),
                'landing_page' => session('utm_landing_page') ?? $request->cookie('utm_landing_page'),
            ]);

            session()->forget(['utm_params', 'utm_referrer_url', 'utm_landing_page']);

            $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        // Use database transaction with row locking to prevent race conditions
        // that could lead to overselling tickets
        try {
            $sale = DB::transaction(function () use ($request, $event, $user, $subdomain) {
                // Check ticket availability with row locking
                foreach ($request->tickets as $ticketId => $quantity) {
                    if ($quantity > 0) {
                        // Lock the ticket row to prevent concurrent modifications
                        $ticketModel = $event->tickets()->lockForUpdate()->find(UrlUtils::decodeId($ticketId));

                        if (! $ticketModel) {
                            throw new \Exception(__('messages.ticket_not_found'));
                        }

                        if ($ticketModel->quantity > 0) {
                            // Handle combined mode logic
                            if ($event->total_tickets_mode === 'combined' && $event->hasSameTicketQuantities()) {
                                // Lock all tickets for combined mode
                                $lockedTickets = $event->tickets()->lockForUpdate()->get();
                                $totalSold = $lockedTickets->sum(function ($ticket) use ($request) {
                                    $ticketSold = $ticket->sold ? json_decode($ticket->sold, true) : [];

                                    return $ticketSold[$request->event_date] ?? 0;
                                });
                                // In combined mode, the total quantity is the same as individual quantity
                                $totalQuantity = $event->getSameTicketQuantity();
                                $remainingTickets = $totalQuantity - $totalSold;

                                // Check if the total requested quantity exceeds remaining tickets
                                $totalRequested = array_sum($request->tickets);
                                if ($totalRequested > $remainingTickets) {
                                    throw new \Exception(__('messages.tickets_not_available'));
                                }
                            } else {
                                $sold = json_decode($ticketModel->sold, true);
                                $soldCount = $sold[$request->event_date] ?? 0;
                                $remainingTickets = $ticketModel->quantity - $soldCount;

                                if ($quantity > $remainingTickets) {
                                    throw new \Exception(__('messages.tickets_not_available'));
                                }
                            }
                        }
                    }
                }

                $sale = new Sale;
                $sale->name = $request->input('name');
                $sale->email = $request->input('email');
                $sale->event_date = $request->input('event_date');
                $sale->subdomain = $subdomain;
                $sale->event_id = $event->id;
                $sale->user_id = $user ? $user->id : null;
                $sale->secret = strtolower(Str::random(32));
                $sale->payment_method = $event->payment_method;

                // Capture UTM attribution
                $utmParams = $request->session()->get('utm_params', []);
                if (empty($utmParams) && $request->cookie('utm_params')) {
                    $utmParams = json_decode($request->cookie('utm_params'), true) ?? [];
                }
                $sale->utm_source = $utmParams['utm_source'] ?? null;
                $sale->utm_medium = $utmParams['utm_medium'] ?? null;
                $sale->utm_campaign = $utmParams['utm_campaign'] ?? null;
                if (($utmParams['utm_source'] ?? null) === 'boost' && ($utmParams['utm_campaign'] ?? null)) {
                    $sale->boost_campaign_id = UrlUtils::decodeId($utmParams['utm_campaign']);
                }
                if (($utmParams['utm_source'] ?? null) === 'newsletter' && ($utmParams['utm_campaign'] ?? null)) {
                    $sale->newsletter_id = UrlUtils::decodeId($utmParams['utm_campaign']);
                }

                if (! $sale->event_date) {
                    $sale->event_date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d');
                }

                // Store event-level custom field values using stable indices
                // Fallback to iteration order for backward compatibility with fields without index
                $eventCustomValues = $request->input('event_custom_values', []);
                $eventCustomFields = $event->custom_fields ?? [];
                $fallbackIndex = 1;
                foreach ($eventCustomFields as $fieldKey => $fieldConfig) {
                    $index = $fieldConfig['index'] ?? $fallbackIndex;
                    $fallbackIndex++;
                    if ($index >= 1 && $index <= 8) {
                        $value = $eventCustomValues[$fieldKey] ?? null;
                        // Sanitize custom field values to prevent stored XSS
                        if ($value !== null) {
                            $value = trim(strip_tags($value));
                        }
                        $sale->{"custom_value{$index}"} = $value;
                    }
                }

                $sale->save();

                // Store ticket-level custom field values
                $ticketCustomValues = $request->input('ticket_custom_values', []);

                foreach ($request->tickets as $ticketId => $quantity) {
                    if ($quantity > 0) {
                        $ticketModel = $event->tickets()->findOrFail(UrlUtils::decodeId($ticketId));
                        $ticketCustomFields = $ticketModel->custom_fields ?? [];

                        $saleTicketData = [
                            'sale_id' => $sale->id,
                            'ticket_id' => UrlUtils::decodeId($ticketId),
                            'quantity' => $quantity,
                            'seats' => json_encode(array_fill(1, $quantity, null)),
                        ];

                        // Store ticket-level custom field values using stable indices
                        // Fallback to iteration order for backward compatibility with fields without index
                        $ticketFallbackIndex = 1;
                        foreach ($ticketCustomFields as $fieldKey => $fieldConfig) {
                            $index = $fieldConfig['index'] ?? $ticketFallbackIndex;
                            $ticketFallbackIndex++;
                            if ($index >= 1 && $index <= 8) {
                                $value = $ticketCustomValues[$ticketId][$fieldKey] ?? null;
                                // Sanitize custom field values to prevent stored XSS
                                if ($value !== null) {
                                    $value = trim(strip_tags($value));
                                }
                                $saleTicketData["custom_value{$index}"] = $value;
                            }
                        }

                        $sale->saleTickets()->create($saleTicketData);
                    }
                }

                $subtotal = $sale->calculateTotal();
                $sale->payment_amount = $subtotal;

                // Apply promo code if provided
                if ($request->promo_code) {
                    $promoCode = PromoCode::where('event_id', $event->id)
                        ->whereRaw('LOWER(code) = ?', [strtolower($request->promo_code)])
                        ->lockForUpdate()
                        ->first();

                    if ($promoCode && $promoCode->isValid()) {
                        $discountAmount = $promoCode->calculateDiscount($sale->saleTickets);
                        if ($discountAmount > 0) {
                            $sale->promo_code_id = $promoCode->id;
                            $sale->discount_amount = $discountAmount;
                            $sale->payment_amount = max(0, $subtotal - $discountAmount);
                            $promoCode->increment('times_used');
                        }
                    }
                }

                $sale->save();

                return $sale;
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $total = $sale->payment_amount;

        // Send email when sale is created
        $this->sendTicketPurchaseEmail($sale, $event);

        AuditService::log(AuditService::SALE_CHECKOUT, $sale->user_id, 'Sale', $sale->id, null, null, 'event_id:'.$event->id);

        if ($total == 0) {
            $sale->status = 'paid';
            $sale->save();

            // Record free ticket sale in analytics (0 revenue)
            AnalyticsEventsDaily::incrementSale($event->id, 0);
            if ($sale->discount_amount > 0) {
                AnalyticsEventsDaily::incrementPromoSale($event->id, $sale->discount_amount);
            }

            return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
        } else {
            switch ($event->payment_method) {
                case 'stripe':
                    return $this->stripeCheckout($subdomain, $sale, $event);
                case 'invoiceninja':
                    return $this->invoiceninjaCheckout($subdomain, $sale, $event);
                case 'payment_url':
                    return $this->paymentUrlCheckout($subdomain, $sale, $event);
                default:
                    return $this->cashCheckout($subdomain, $sale, $event);
            }
        }
    }

    private function stripeCheckout($subdomain, $sale, $event)
    {
        $promoCode = $sale->promo_code_id ? $sale->promoCode : null;
        $discount = $sale->discount_amount ?? 0;

        // Calculate discount ratio for eligible tickets
        $eligibleSubtotal = 0;
        if ($promoCode && $discount > 0) {
            foreach ($sale->saleTickets as $saleTicket) {
                if ($promoCode->appliesToTicket($saleTicket->ticket_id)) {
                    $eligibleSubtotal += $saleTicket->ticket->price * $saleTicket->quantity;
                }
            }
        }
        $discountRatio = ($eligibleSubtotal > 0 && $discount > 0)
            ? ($eligibleSubtotal - $discount) / $eligibleSubtotal
            : 1;

        $lineItems = [];
        $totalCents = 0;
        $lastEligibleIndex = null;

        foreach ($sale->saleTickets as $index => $saleTicket) {
            $unitPrice = $saleTicket->ticket->price;
            if ($promoCode && $discount > 0 && $promoCode->appliesToTicket($saleTicket->ticket_id)) {
                $unitPrice = $unitPrice * $discountRatio;
                $lastEligibleIndex = count($lineItems);
            }
            $unitAmountCents = (int) round($unitPrice * 100);
            $totalCents += $unitAmountCents * $saleTicket->quantity;

            $lineItems[] = [
                'price_data' => [
                    'currency' => $event->ticket_currency_code,
                    'product_data' => [
                        'name' => $saleTicket->ticket->type ? $saleTicket->ticket->type : __('messages.tickets'),
                        ...$saleTicket->ticket->description ? ['description' => $saleTicket->ticket->description] : [],
                    ],
                    'unit_amount' => $unitAmountCents,
                ],
                'quantity' => $saleTicket->quantity,
            ];
        }

        // Fix rounding difference to match payment_amount exactly
        $expectedCents = (int) round($sale->payment_amount * 100);
        if ($totalCents !== $expectedCents && $lastEligibleIndex !== null) {
            $diff = $expectedCents - $totalCents;
            $lastItem = &$lineItems[$lastEligibleIndex];
            if ($lastItem['quantity'] > 1) {
                // Split: (quantity-1) at original price + 1 at adjusted price
                $originalUnit = $lastItem['price_data']['unit_amount'];
                $lastItem['quantity'] -= 1;
                $totalCents -= $originalUnit; // remove one unit from total
                $adjustedUnit = $originalUnit + $diff;
                $lineItems[] = [
                    'price_data' => [
                        'currency' => $lastItem['price_data']['currency'],
                        'product_data' => $lastItem['price_data']['product_data'],
                        'unit_amount' => $adjustedUnit,
                    ],
                    'quantity' => 1,
                ];
            } else {
                $lastItem['price_data']['unit_amount'] += $diff;
            }
            unset($lastItem);
        }

        $data = [
            'sale_id' => UrlUtils::encodeId($sale->id),
            'subdomain' => $subdomain,
            'date' => $sale->event_date,
        ];

        // Determine if using Stripe Connect (hosted mode) or direct payments (self-hosted)
        $useConnect = config('app.hosted') && $event->user->stripe_account_id;

        if ($useConnect) {
            // Hosted mode: Use Stripe Connect with event creator's account
            $stripe = new StripeClient(config('services.stripe.key'));

            $session = $stripe->checkout->sessions->create(
                [
                    'line_items' => $lineItems,
                    'mode' => 'payment',
                    'customer_email' => $sale->email,
                    'metadata' => [
                        'customer_name' => $sale->name,
                        'sale_id' => UrlUtils::encodeId($sale->id),
                    ],
                    'payment_intent_data' => [
                        'metadata' => [
                            'sale_id' => UrlUtils::encodeId($sale->id),
                        ],
                    ],
                    'success_url' => custom_domain_url(route('checkout.success', $data).'?session_id={CHECKOUT_SESSION_ID}'),
                    'cancel_url' => custom_domain_url(route('checkout.cancel', $data).'?secret='.$sale->secret),
                ],
                [
                    'stripe_account' => $event->user->stripe_account_id,
                ],
            );
        } else {
            // Self-hosted mode: Use direct Stripe payments with platform keys
            $stripe = new StripeClient(config('services.stripe_platform.secret'));

            $session = $stripe->checkout->sessions->create([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'customer_email' => $sale->email,
                'metadata' => [
                    'customer_name' => $sale->name,
                    'sale_id' => UrlUtils::encodeId($sale->id),
                ],
                'payment_intent_data' => [
                    'metadata' => [
                        'sale_id' => UrlUtils::encodeId($sale->id),
                    ],
                ],
                'success_url' => custom_domain_url(route('checkout.success', $data).'?session_id={CHECKOUT_SESSION_ID}&direct=1'),
                'cancel_url' => custom_domain_url(route('checkout.cancel', $data).'?secret='.$sale->secret),
            ]);
        }

        return redirect($session->url);
    }

    private function invoiceninjaCheckout($subdomain, $sale, $event)
    {
        $user = $event->user;

        if (str_starts_with($user->invoiceninja_mode, 'payment_link')) {
            return $this->invoiceninjaPaymentLinkCheckout($subdomain, $sale, $event);
        }

        return $this->invoiceninjaInvoiceCheckout($subdomain, $sale, $event);
    }

    private function invoiceninjaInvoiceCheckout($subdomain, $sale, $event)
    {
        try {
            $user = $event->user;
            $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);
            $company = null;

            $foundClient = false;
            $clientMachesEmail = false;
            $requirePassword = false;
            $sendEmail = false;

            $client = $invoiceNinja->findClient($sale->email, $event->ticket_currency_code);

            if ($client) {
                $foundClient = true;
                if (auth()->user() && auth()->user()->email_verified_at) {
                    foreach ($client['contacts'] as $contact) {
                        if ($contact['email'] == auth()->user()->email) {
                            $clientMachesEmail = true;
                        }
                    }
                }
                if (! $clientMachesEmail) {
                    $company = $invoiceNinja->getCompany();
                    $requirePassword = $company['settings']['enable_client_portal_password'];
                }
            } else {
                $client = $invoiceNinja->createClient($sale->name, $sale->email, $event->ticket_currency_code);
            }

            if ($foundClient && ! $clientMachesEmail && ! $requirePassword) {
                $sendEmail = true;
            }

            $lineItems = [];
            foreach ($sale->saleTickets as $saleTicket) {
                $lineItems[] = [
                    'product_key' => $saleTicket->ticket->type,
                    'notes' => $saleTicket->ticket->description ?? __('messages.tickets'),
                    'quantity' => $saleTicket->quantity,
                    'cost' => $saleTicket->ticket->price,
                ];
            }

            if ($sale->discount_amount > 0 && $sale->promoCode) {
                $lineItems[] = [
                    'product_key' => __('messages.discount'),
                    'notes' => $sale->promoCode->code,
                    'quantity' => 1,
                    'cost' => -$sale->discount_amount,
                ];
            }

            $qrCodeUrl = route('ticket.qr_code', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
            $invoice = $invoiceNinja->createInvoice($client['id'], $lineItems, $qrCodeUrl, $sendEmail);

            $sale->transaction_reference = $invoice['id'];
            $sale->payment_amount = $invoice['amount'];
            $sale->save();

            if ($sendEmail) {
                return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
            } else {
                return redirect($invoice['invitations'][0]['link']);
            }
        } catch (\Exception $e) {
            \Log::error('Invoice Ninja invoice checkout failed', [
                'sale_id' => $sale->id,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('messages.error'));
        }
    }

    private function invoiceninjaPaymentLinkCheckout($subdomain, $sale, $event)
    {
        try {
            $user = $event->user;
            $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);

            $promoCode = $sale->promo_code_id ? $sale->promoCode : null;
            $discount = $sale->discount_amount ?? 0;

            $eligibleSubtotal = 0;
            if ($promoCode && $discount > 0) {
                foreach ($sale->saleTickets as $saleTicket) {
                    if ($promoCode->appliesToTicket($saleTicket->ticket_id)) {
                        $eligibleSubtotal += $saleTicket->ticket->price * $saleTicket->quantity;
                    }
                }
            }
            $discountRatio = ($eligibleSubtotal > 0 && $discount > 0)
                ? ($eligibleSubtotal - $discount) / $eligibleSubtotal
                : 1;

            $productIds = [];
            foreach ($sale->saleTickets as $saleTicket) {
                $productKey = $saleTicket->ticket->type ?: __('messages.tickets');
                $price = $saleTicket->ticket->price;
                if ($promoCode && $discount > 0 && $promoCode->appliesToTicket($saleTicket->ticket_id)) {
                    $price = round($price * $discountRatio, 2);
                }
                $product = $invoiceNinja->createProduct(
                    $productKey,
                    $saleTicket->ticket->description ?? '',
                    $price
                );
                for ($i = 0; $i < $saleTicket->quantity; $i++) {
                    $productIds[] = $product['id'];
                }
            }

            $encodedSaleId = UrlUtils::encodeId($sale->id);
            $subscriptionName = 'ES-'.$encodedSaleId.'-'.time();

            $webhookConfig = [
                'post_purchase_url' => route('invoiceninja.purchase_webhook', ['sale' => $encodedSaleId]),
                'post_purchase_rest_method' => 'post',
                'post_purchase_headers' => ['X-Webhook-Secret' => $user->invoiceninja_webhook_secret],
            ];

            $steps = ($user->invoiceninja_mode === 'payment_link_v3') ? 'auth.login-or-register,cart' : 'cart';

            $subscription = $invoiceNinja->createSubscription(
                $subscriptionName,
                $productIds,
                $sale->payment_amount,
                $webhookConfig,
                $steps
            );

            $sale->transaction_reference = 'sub:'.$subscription['id'];
            $sale->save();

            $purchaseUrl = $subscription['purchase_page'];
            if ($user->invoiceninja_mode === 'payment_link_v2') {
                $purchaseUrl .= '/v2';
            } elseif ($user->invoiceninja_mode === 'payment_link_v3') {
                $purchaseUrl .= '/v3';
            }

            return redirect($purchaseUrl);
        } catch (\Exception $e) {
            \Log::warning('Invoice Ninja payment link checkout failed, falling back to invoice mode', [
                'sale_id' => $sale->id,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return $this->invoiceninjaInvoiceCheckout($subdomain, $sale, $event);
        }
    }

    private function paymentUrlCheckout($subdomain, $sale, $event)
    {
        $user = $event->user;

        return redirect($user->payment_url);
    }

    private function cashCheckout($subdomain, $sale, $event)
    {
        return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
    }

    public function success($subdomain, $sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $event = $sale->event;

        // Validate session_id parameter exists
        if (! request()->has('session_id')) {
            return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
        }

        $isDirect = request()->query('direct') === '1';

        try {
            if ($isDirect) {
                // Self-hosted mode: Use platform Stripe keys
                $stripe = new StripeClient(config('services.stripe_platform.secret'));
                $session = $stripe->checkout->sessions->retrieve(request()->session_id);
            } else {
                // Hosted mode: Use Stripe Connect
                $stripe = new StripeClient(config('services.stripe.key'));
                $session = $stripe->checkout->sessions->retrieve(request()->session_id, [], [
                    'stripe_account' => $sale->event->user->stripe_account_id,
                ]);
            }

            // Verify the session belongs to this sale to prevent cross-sale session reuse
            $sessionSaleId = $session->metadata->sale_id ?? null;
            if ($sessionSaleId !== UrlUtils::encodeId($sale->id)) {
                \Log::warning('Stripe session sale_id mismatch in success()', [
                    'url_sale_id' => $sale->id,
                    'session_sale_id' => $sessionSaleId,
                    'session_id' => request()->session_id,
                ]);

                return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
            }

            // Store the transaction reference so the webhook can find this sale,
            // but don't set status=paid or overwrite payment_amount here - the webhook
            // handles that with proper locking and amount validation
            if ($sale->status !== 'paid') {
                $sale->transaction_reference = $session->payment_intent;
                $sale->save();
            }
        } catch (\Exception $e) {
            // Log the error but don't fail - webhook will handle payment confirmation
            \Log::warning('Stripe session retrieval failed in success(): '.$e->getMessage());
        }

        return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
    }

    public function cancel($subdomain, $sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));

        // Verify the secret from the URL to prevent unauthorized cancellations
        $secret = request()->query('secret');
        if (! $secret || ! hash_equals($sale->secret, $secret)) {
            abort(403);
        }

        if ($sale->status !== 'unpaid') {
            return redirect($sale->event->getGuestUrl($subdomain, $sale->event_date).'&tickets=true');
        }

        DB::transaction(function () use ($sale) {
            $sale->status = 'cancelled';
            $sale->save();
        });

        $event = $sale->event;

        return redirect($event->getGuestUrl($subdomain, $sale->event_date).'&tickets=true');
    }

    public function paymentUrlSuccess($sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $event = $sale->event;
        $user = $event->user;

        // Verify the secret from the URL (using constant-time comparison to prevent timing attacks)
        // Accept either a per-sale HMAC token or the legacy global secret
        $secret = request()->query('secret');
        if (! $secret || ! $user->payment_secret || ! $this->verifyPaymentUrlSecret($secret, $sale, $user)) {
            abort(403, 'Invalid secret');
        }

        // Use lockForUpdate to prevent race conditions from concurrent requests
        DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status === 'paid') {
                return;
            }

            $sale->status = 'paid';
            $sale->transaction_reference = __('messages.manual_payment');
            $sale->save();

            AnalyticsEventsDaily::incrementSale($sale->event_id, $sale->payment_amount);
            if ($sale->discount_amount > 0) {
                AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $sale->discount_amount);
            }
        });

        return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
    }

    public function paymentUrlCancel($sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $event = $sale->event;
        $user = $event->user;

        // Verify the secret from the URL (using constant-time comparison to prevent timing attacks)
        // Accept either a per-sale HMAC token or the legacy global secret
        $secret = request()->query('secret');
        if (! $secret || ! $user->payment_secret || ! $this->verifyPaymentUrlSecret($secret, $sale, $user)) {
            abort(403, 'Invalid secret');
        }

        DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status !== 'unpaid') {
                return;
            }
            $sale->status = 'cancelled';
            $sale->save();
        });

        return redirect($event->getGuestUrl($sale->subdomain, $sale->event_date).'&tickets=true');
    }

    /**
     * Generate a per-sale HMAC token for payment URL callbacks.
     * This produces a unique token per sale without requiring schema changes.
     */
    public static function generatePaymentUrlHmac(Sale $sale, $paymentSecret): string
    {
        return hash_hmac('sha256', (string) $sale->id, $paymentSecret);
    }

    /**
     * Verify a payment URL secret: accepts either a per-sale HMAC token
     * or the legacy global payment_secret for backward compatibility.
     */
    private function verifyPaymentUrlSecret(string $secret, Sale $sale, $user): bool
    {
        // Check per-sale HMAC token first (preferred)
        $expectedHmac = self::generatePaymentUrlHmac($sale, $user->payment_secret);
        if (hash_equals($expectedHmac, $secret)) {
            return true;
        }

        // Fall back to legacy global secret for backward compatibility
        return hash_equals($user->payment_secret, $secret);
    }

    public function scan()
    {
        return view('ticket.scan');
    }

    public function scanned($eventId, $secret)
    {
        $user = auth()->user();
        $event = Event::find(UrlUtils::decodeId($eventId));

        if (! $event) {
            return response()->json(['error' => __('messages.this_ticket_is_not_valid')], 200);
        }

        $sale = Sale::where('event_id', $event->id)
            ->where('secret', $secret)
            ->first();

        if (! $sale) {
            return response()->json(['error' => __('messages.this_ticket_is_not_valid')], 200);
        }

        if (! $user->canScanEvent($event)) {
            return response()->json(['error' => __('messages.you_are_not_authorized_to_scan_this_ticket')], 200);
        }

        if (Carbon::parse($sale->event_date)->format('Y-m-d') !== now()->format('Y-m-d')) {
            return response()->json(['error' => __('messages.this_ticket_is_not_valid_for_today')], 200);
        }

        if ($sale->status == 'unpaid') {
            return response()->json(['error' => __('messages.this_ticket_is_not_paid')], 200);
        } elseif ($sale->status == 'cancelled') {
            return response()->json(['error' => __('messages.this_ticket_is_cancelled')], 200);
        } elseif ($sale->status == 'refunded') {
            return response()->json(['error' => __('messages.this_ticket_is_refunded')], 200);
        }

        $data = new \stdClass;
        $data->attendee = $sale->name;
        $data->event = $event->name;
        $data->date = $event->localStartsAt(true, $sale->event_date);
        $data->tickets = [];

        foreach ($sale->saleTickets as $saleTicket) {
            $data->tickets[] = [
                'type' => $saleTicket->ticket->type,
                'seats' => json_decode($saleTicket->seats, true),
            ];
        }

        foreach ($sale->saleTickets as $saleTicket) {
            $seats = $saleTicket->seats;
            if ($seats) {
                $seats = json_decode($seats, true);
                foreach ($seats as $key => $value) {
                    if (! $value) {
                        $seats[$key] = time();
                    }
                }
                $saleTicket->seats = json_encode($seats);
                $saleTicket->save();
            }
        }

        return response()->json($data);
    }

    public function qrCode($eventId, $secret)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->firstOrFail();

        $url = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $secret]);

        $qrCode = QrCode::create($url)
            ->setSize(200)
            ->setMargin(10);

        $writer = new PngWriter;
        $result = $writer->write($qrCode);

        header('Content-Type: '.$result->getMimeType());
        header('X-Content-Type-Options: nosniff');

        echo $result->getString();

        exit;
    }

    public function view($eventId, $secret)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::with('promoCode')->where('event_id', $event->id)->where('secret', $secret)->firstOrFail();
        $role = $event->role();

        return view('ticket.view', compact('event', 'sale', 'role'));
    }

    public function handleAction(Request $request, $sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $user = auth()->user();

        if (! $user->canEditEvent($sale->event)) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        $previousStatus = $sale->status;
        $actionPerformed = false;

        switch ($request->action) {
            case 'mark_paid':
                if ($sale->status === 'unpaid') {
                    // Clean up IN subscription for payment link mode sales
                    if ($sale->payment_method === 'invoiceninja' && str_starts_with($sale->transaction_reference ?? '', 'sub:')) {
                        try {
                            $user = $sale->event->user;
                            $subscriptionId = str_replace('sub:', '', $sale->transaction_reference);
                            $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);
                            $invoiceNinja->deleteSubscription($subscriptionId);
                        } catch (\Exception $e) {
                            \Log::warning('Failed to delete IN subscription on mark_paid', [
                                'sale_id' => $sale->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    DB::transaction(function () use ($sale) {
                        $sale->status = 'paid';
                        $sale->transaction_reference = __('messages.manual_payment');
                        $sale->save();
                    });
                    $actionPerformed = true;

                    AnalyticsEventsDaily::incrementSale($sale->event_id, $sale->payment_amount);
                    if ($sale->discount_amount > 0) {
                        AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $sale->discount_amount);
                    }
                }
                break;

            case 'refund':
                if ($sale->status === 'paid') {
                    DB::transaction(function () use ($sale) {
                        $sale->status = 'refunded';
                        $sale->save();

                        AnalyticsEventsDaily::decrementSale($sale->event_id, $sale->payment_amount);

                        if ($sale->discount_amount > 0) {
                            AnalyticsEventsDaily::decrementPromoSale($sale->event_id, $sale->discount_amount);
                        }
                    });
                    $actionPerformed = true;
                }
                break;

            case 'cancel':
                if (in_array($sale->status, ['unpaid', 'paid'])) {
                    $wasPaid = $sale->status === 'paid';

                    // Clean up IN subscription for payment link mode sales
                    if (! $wasPaid && $sale->payment_method === 'invoiceninja' && str_starts_with($sale->transaction_reference ?? '', 'sub:')) {
                        try {
                            $user = $sale->event->user;
                            $subscriptionId = str_replace('sub:', '', $sale->transaction_reference);
                            $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);
                            $invoiceNinja->deleteSubscription($subscriptionId);
                        } catch (\Exception $e) {
                            \Log::warning('Failed to delete IN subscription on cancel', [
                                'sale_id' => $sale->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    DB::transaction(function () use ($sale) {
                        $sale->status = 'cancelled';
                        $sale->save();
                    });
                    $actionPerformed = true;

                    if ($wasPaid) {
                        AnalyticsEventsDaily::decrementSale($sale->event_id, $sale->payment_amount);

                        if ($sale->discount_amount > 0) {
                            AnalyticsEventsDaily::decrementPromoSale($sale->event_id, $sale->discount_amount);
                        }
                    }
                }
                break;

            case 'delete':
                // Clean up IN subscription for unpaid payment link mode sales
                if ($sale->status === 'unpaid' && $sale->payment_method === 'invoiceninja' && str_starts_with($sale->transaction_reference ?? '', 'sub:')) {
                    try {
                        $user = $sale->event->user;
                        $subscriptionId = str_replace('sub:', '', $sale->transaction_reference);
                        $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);
                        $invoiceNinja->deleteSubscription($subscriptionId);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete IN subscription on delete', [
                            'sale_id' => $sale->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                DB::transaction(function () use ($sale) {
                    // If the sale was paid, cancel first to release ticket inventory
                    // (triggers Sale::booted hook) and decrement analytics
                    if ($sale->status === 'paid') {
                        $sale->status = 'cancelled';
                        $sale->save();

                        AnalyticsEventsDaily::decrementSale($sale->event_id, $sale->payment_amount);

                        if ($sale->discount_amount > 0) {
                            AnalyticsEventsDaily::decrementPromoSale($sale->event_id, $sale->discount_amount);
                        }
                    }

                    $sale->is_deleted = true;
                    $sale->save();
                });
                $actionPerformed = true;
                break;
        }

        if ($actionPerformed) {
            $auditAction = match ($request->action) {
                'refund' => AuditService::SALE_REFUND,
                default => AuditService::SALE_CHECKIN,
            };
            AuditService::log($auditAction, $user->id, 'Sale', $sale->id,
                ['status' => $previousStatus],
                ['status' => $sale->status],
                $request->action.':event_id:'.$sale->event_id
            );
        }

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('message', __('messages.action_completed'));
    }

    public function release()
    {
        $requestSecret = request()->get('secret');
        $serverSecret = config('app.cron_secret');

        if (! $serverSecret || ! $requestSecret || ! hash_equals($serverSecret, $requestSecret)) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        \Artisan::call('app:release-tickets');

        return response()->json(['success' => true]);
    }

    /**
     * Send ticket purchase email
     */
    private function sendTicketPurchaseEmail(Sale $sale, Event $event): void
    {
        try {
            // Load roles if not already loaded
            if (! $event->relationLoaded('roles')) {
                $event->load('roles');
            }

            // Get the venue role if available, otherwise get the first role
            $role = $event->venue ?: $event->roles->first();

            if (! $role) {
                \Log::warning('No schedule found for ticket email', [
                    'sale_id' => $sale->id,
                    'event_id' => $event->id,
                ]);

                return;
            }

            $emailService = new EmailService;
            $emailService->sendTicketEmail($sale, $role);
        } catch (\Exception $e) {
            // Log error but don't fail the sale creation
            \Log::error('Failed to send ticket purchase email: '.$e->getMessage(), [
                'sale_id' => $sale->id,
                'event_id' => $event->id,
            ]);
        }
    }

    /**
     * Resend ticket email
     */
    public function resendEmail($sale_id): JsonResponse
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $user = auth()->user();

        if (! $user->canEditEvent($sale->event)) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        try {
            $event = $sale->event;

            // Load roles if not already loaded
            if (! $event->relationLoaded('roles')) {
                $event->load('roles');
            }

            // Get the venue role if available, otherwise get the first role
            $role = $event->venue ?: $event->roles->first();

            if (! $role) {
                return response()->json(['error' => __('messages.error')], 400);
            }

            $emailService = new EmailService;

            $result = $emailService->sendTicketEmail($sale, $role, queue: false);

            if ($result === true) {
                return response()->json(['success' => true, 'message' => __('messages.email_sent_successfully')]);
            } elseif ($result === EmailService::ERROR_NOT_CONFIGURED) {
                return response()->json(['error' => __('messages.email_not_configured')], 422);
            } else {
                return response()->json(['error' => __('messages.failed_to_send_email')], 500);
            }
        } catch (\Exception $e) {
            // Log the full error server-side but return generic message to user
            \Log::error('Resend ticket email failed: '.$e->getMessage(), [
                'sale_id' => $sale->id ?? null,
            ]);

            return response()->json(['error' => __('messages.failed_to_send_email')], 500);
        }
    }
}
