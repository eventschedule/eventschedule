<?php

namespace App\Http\Controllers;

use App\Http\Requests\GiftCardPurchaseRequest;
use App\Models\Event;
use App\Models\GiftCard;
use App\Models\Role;
use App\Services\AuditService;
use App\Services\EmailService;
use App\Utils\InvoiceNinja;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class GiftCardController extends Controller
{
    public function showPurchase($subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $this->canSellGiftCards($role)) {
            abort(404);
        }

        return view('gift-card.purchase', [
            'role' => $role,
        ]);
    }

    public function purchase(GiftCardPurchaseRequest $request, $subdomain)
    {
        // Honeypot field - bots fill it, humans never see it
        if ($request->filled('website')) {
            return redirect()->back();
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $this->canSellGiftCards($role)) {
            abort(404);
        }

        // The amount must exactly match one of the owner's configured presets
        $presets = array_map('floatval', $role->gift_card_amounts ?? []);
        $amount = (float) $request->amount;
        if (! in_array($amount, $presets)) {
            return back()->withInput()->with('error', __('messages.gift_card_invalid_amount'));
        }

        $sendToSelf = $request->boolean('send_to_self');
        $purchaserName = $this->cleanText($request->purchaser_name);
        $purchaserEmail = strtolower(trim($request->purchaser_email));

        try {
            $giftCard = new GiftCard;
            $giftCard->role_id = $role->id;
            $giftCard->code = GiftCard::generateCode();
            $giftCard->secret = strtolower(Str::random(32));
            $giftCard->amount = $amount;
            $giftCard->remaining_amount = $amount;
            $giftCard->currency_code = strtoupper($role->gift_card_currency_code ?: 'USD');
            $giftCard->status = 'unpaid';
            $giftCard->payment_method = $role->gift_card_payment_method ?: 'cash';
            $giftCard->valid_days = $role->gift_card_valid_days;
            $giftCard->purchaser_name = $purchaserName;
            $giftCard->purchaser_email = $purchaserEmail;
            $giftCard->recipient_name = $sendToSelf ? $purchaserName : $this->cleanText($request->recipient_name);
            $giftCard->recipient_email = $sendToSelf ? $purchaserEmail : strtolower(trim($request->recipient_email));
            $giftCard->message = $request->filled('message') ? $this->cleanText($request->message) : null;
            $giftCard->save();

            AuditService::log(AuditService::GIFT_CARD_CREATED, auth()->user()?->id, 'GiftCard', $giftCard->id,
                null, null, 'role_id:'.$role->id);

            switch ($giftCard->payment_method) {
                case 'stripe':
                    return $this->stripeCheckout($role, $giftCard);
                case 'invoiceninja':
                    return $this->invoiceninjaCheckout($role, $giftCard);
                case 'payment_url':
                    return redirect($role->user->payment_url);
                default:
                    // Cash: the card stays unpaid until the owner marks it paid
                    return redirect($giftCard->getViewUrl());
            }
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return back()->withInput()->with('error', __('messages.error'));
        } catch (\App\Exceptions\BusinessException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', __('messages.error'));
        }
    }

    private function stripeCheckout(Role $role, GiftCard $giftCard)
    {
        $data = [
            'gift_card_id' => UrlUtils::encodeId($giftCard->id),
            'subdomain' => $role->subdomain,
        ];

        $unitAmountCents = (int) round($giftCard->amount * MoneyUtils::getSmallestUnitMultiplier($giftCard->currency_code));

        $sessionData = [
            'line_items' => [[
                'price_data' => [
                    'currency' => $giftCard->currency_code,
                    'product_data' => [
                        'name' => __('messages.gift_card').' - '.$role->name,
                    ],
                    'unit_amount' => $unitAmountCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $giftCard->purchaser_email,
            'metadata' => [
                'gift_card_id' => UrlUtils::encodeId($giftCard->id),
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'gift_card_id' => UrlUtils::encodeId($giftCard->id),
                ],
            ],
        ];

        // Determine if using Stripe Connect (hosted mode) or direct payments (selfhosted)
        $useConnect = config('app.hosted') && $role->user->stripe_account_id;

        if ($useConnect) {
            $stripe = new StripeClient(config('services.stripe.key'));

            $sessionData['success_url'] = custom_domain_url(route('gift_card.success', $data).'?session_id={CHECKOUT_SESSION_ID}');
            $sessionData['cancel_url'] = custom_domain_url(route('gift_card.cancel', $data).'?secret='.$giftCard->secret);

            $session = $stripe->checkout->sessions->create($sessionData, [
                'stripe_account' => $role->user->stripe_account_id,
            ]);
        } else {
            $stripe = new StripeClient(config('services.stripe_platform.secret'));

            $sessionData['success_url'] = custom_domain_url(route('gift_card.success', $data).'?session_id={CHECKOUT_SESSION_ID}&direct=1');
            $sessionData['cancel_url'] = custom_domain_url(route('gift_card.cancel', $data).'?secret='.$giftCard->secret);

            $session = $stripe->checkout->sessions->create($sessionData);
        }

        return redirect($session->url);
    }

    private function invoiceninjaCheckout(Role $role, GiftCard $giftCard)
    {
        try {
            $user = $role->user;
            $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);

            $client = $invoiceNinja->findClient($giftCard->purchaser_email, $giftCard->currency_code);

            if (! $client) {
                $client = $invoiceNinja->createClient($giftCard->purchaser_name, $giftCard->purchaser_email, $giftCard->currency_code);
            }

            $lineItems = [[
                'product_key' => __('messages.gift_card'),
                'notes' => __('messages.gift_card').' - '.$role->name,
                'quantity' => 1,
                'cost' => (float) $giftCard->amount,
            ]];

            $invoice = $invoiceNinja->createInvoice($client['id'], $lineItems, null, false,
                __('messages.gift_card').' - '.$role->name);

            $giftCard->transaction_reference = $invoice['id'];
            $giftCard->save();

            return redirect($invoice['invitations'][0]['link']);
        } catch (\Exception $e) {
            report($e);
            \Log::error('Invoice Ninja gift card checkout failed', [
                'gift_card_id' => $giftCard->id,
                'role_id' => $role->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', __('messages.error'));
        }
    }

    public function success($subdomain, $gift_card_id)
    {
        $giftCard = GiftCard::findOrFail(UrlUtils::decodeId($gift_card_id));

        if (! request()->has('session_id')) {
            abort(404);
        }

        $isDirect = request()->query('direct') === '1';

        try {
            if ($isDirect) {
                $stripe = new StripeClient(config('services.stripe_platform.secret'));
                $session = $stripe->checkout->sessions->retrieve(request()->session_id);
            } else {
                $stripe = new StripeClient(config('services.stripe.key'));
                $session = $stripe->checkout->sessions->retrieve(request()->session_id, [], [
                    'stripe_account' => $giftCard->role->user->stripe_account_id,
                ]);
            }

            // Verify the session belongs to this gift card to prevent cross-session reuse
            $sessionGiftCardId = $session->metadata->gift_card_id ?? null;
            if ($sessionGiftCardId !== UrlUtils::encodeId($giftCard->id)) {
                \Log::warning('Stripe session gift_card_id mismatch in gift card success()', [
                    'url_gift_card_id' => $giftCard->id,
                    'session_gift_card_id' => $sessionGiftCardId,
                    'session_id' => request()->session_id,
                ]);

                return redirect($giftCard->getViewUrl());
            }

            // Store the transaction reference so the webhook can find this card,
            // but leave activation to the webhook (locked + amount-verified)
            if ($giftCard->status === 'unpaid') {
                $giftCard->transaction_reference = $session->payment_intent;
                $giftCard->save();
            }
        } catch (\Exception $e) {
            \Log::warning('Stripe session retrieval failed in gift card success(): '.$e->getMessage());
        }

        return redirect($giftCard->getViewUrl());
    }

    public function cancel($subdomain, $gift_card_id)
    {
        $giftCard = GiftCard::findOrFail(UrlUtils::decodeId($gift_card_id));

        $secret = request()->query('secret');
        if (! $secret || ! hash_equals($giftCard->secret, $secret)) {
            abort(403);
        }

        $cancelled = DB::transaction(function () use ($giftCard) {
            $giftCard = GiftCard::lockForUpdate()->find($giftCard->id);
            if ($giftCard->status !== 'unpaid') {
                return false;
            }
            $giftCard->status = 'cancelled';
            $giftCard->save();

            return true;
        });

        if ($cancelled) {
            AuditService::log(AuditService::GIFT_CARD_CANCELLED, auth()->user()?->id, 'GiftCard', $giftCard->id,
                ['status' => 'unpaid'], ['status' => 'cancelled'], 'guest_abandon:role_id:'.$giftCard->role_id);
        }

        return redirect(route('gift_card.purchase', ['subdomain' => $subdomain]))
            ->with('error', __('messages.gift_card_payment_cancelled'));
    }

    public function paymentUrlSuccess($subdomain, $gift_card_id)
    {
        $giftCard = GiftCard::findOrFail(UrlUtils::decodeId($gift_card_id));
        $user = $giftCard->role->user;

        $secret = request()->query('secret');
        if (! $secret || ! $user->payment_secret || ! $this->verifyGiftCardPaymentUrlSecret($secret, $giftCard, $user)) {
            abort(403, 'Invalid secret');
        }

        $didActivate = false;

        DB::transaction(function () use ($giftCard, &$didActivate) {
            $giftCard = GiftCard::lockForUpdate()->find($giftCard->id);
            if ($giftCard->status !== 'unpaid') {
                return;
            }

            $giftCard->activate(__('messages.manual_payment'));
            $didActivate = true;

            AuditService::log(AuditService::GIFT_CARD_PAID, null, 'GiftCard', $giftCard->id,
                ['status' => 'unpaid'], ['status' => 'active'], 'payment_url:role_id:'.$giftCard->role_id);
        });

        if ($didActivate) {
            (new EmailService)->sendGiftCardEmails($giftCard->refresh());
        }

        return redirect($giftCard->getViewUrl());
    }

    public function paymentUrlCancel($subdomain, $gift_card_id)
    {
        $giftCard = GiftCard::findOrFail(UrlUtils::decodeId($gift_card_id));
        $user = $giftCard->role->user;

        $secret = request()->query('secret');
        if (! $secret || ! $user->payment_secret || ! $this->verifyGiftCardPaymentUrlSecret($secret, $giftCard, $user)) {
            abort(403, 'Invalid secret');
        }

        $cancelled = DB::transaction(function () use ($giftCard) {
            $giftCard = GiftCard::lockForUpdate()->find($giftCard->id);
            if ($giftCard->status !== 'unpaid') {
                return false;
            }
            $giftCard->status = 'cancelled';
            $giftCard->save();

            return true;
        });

        if ($cancelled) {
            AuditService::log(AuditService::GIFT_CARD_CANCELLED, null, 'GiftCard', $giftCard->id,
                ['status' => 'unpaid'], ['status' => 'cancelled'], 'payment_url_abandon:role_id:'.$giftCard->role_id);
        }

        return redirect(route('gift_card.purchase', ['subdomain' => $subdomain]));
    }

    public function view($gift_card_id, $secret)
    {
        $giftCard = GiftCard::with('role')->findOrFail(UrlUtils::decodeId($gift_card_id));

        if (! $secret || ! hash_equals($giftCard->secret, $secret)) {
            abort(403);
        }

        return view('gift-card.view', [
            'giftCard' => $giftCard,
            'role' => $giftCard->role,
        ]);
    }

    /**
     * AJAX pre-validation of a gift card code at ticket checkout.
     * Mirrors PromoCodeController::validate.
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
            'code' => 'required|string|max:20',
        ]);

        $eventId = UrlUtils::decodeId($request->event_id);
        $event = Event::with('roles')->where('is_draft', false)->find($eventId);

        if (! $event) {
            return response()->json([
                'valid' => false,
                'message' => __('messages.gift_card_invalid'),
            ]);
        }

        $giftCard = GiftCard::with('role')
            ->whereIn('role_id', $event->roles->pluck('id'))
            ->where('code', GiftCard::normalizeCode($request->code))
            ->first();

        // Gift cards are sold through the schedule owner's payment account but redeemed
        // against the event owner's payout, so both must be the same user (curator guard).
        if (! $giftCard || $event->user_id !== $giftCard->role->user_id) {
            return response()->json([
                'valid' => false,
                'message' => __('messages.gift_card_invalid'),
            ]);
        }

        if ($giftCard->status !== 'active' || $giftCard->isExpired()) {
            $message = __('messages.gift_card_invalid');
            if ($giftCard->status === 'active' && $giftCard->isExpired()) {
                $message = __('messages.gift_card_expired', ['date' => $giftCard->expires_at->format('M j, Y')]);
            }

            return response()->json([
                'valid' => false,
                'message' => $message,
            ]);
        }

        if ($giftCard->isDepleted()) {
            return response()->json([
                'valid' => false,
                'message' => __('messages.gift_card_depleted'),
            ]);
        }

        if (strcasecmp($giftCard->currency_code, $event->ticket_currency_code) !== 0) {
            return response()->json([
                'valid' => false,
                'message' => __('messages.gift_card_wrong_currency', [
                    'card_currency' => $giftCard->currency_code,
                    'event_currency' => $event->ticket_currency_code,
                ]),
            ]);
        }

        return response()->json([
            'valid' => true,
            'balance' => (float) $giftCard->remaining_amount,
            'currency' => $giftCard->currency_code,
            'message' => __('messages.gift_card_applied'),
        ]);
    }

    /**
     * Owner actions on a gift card from the Sales page tab.
     */
    public function handleAction(Request $request, $gift_card_id)
    {
        $giftCard = GiftCard::with('role')->findOrFail(UrlUtils::decodeId($gift_card_id));
        $user = auth()->user();

        if ($giftCard->role->user_id !== $user->id) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        $previousStatus = $giftCard->status;
        $actionPerformed = false;
        $didActivate = false;

        switch ($request->action) {
            case 'mark_paid':
                DB::transaction(function () use ($giftCard, &$actionPerformed, &$didActivate) {
                    $giftCard = GiftCard::lockForUpdate()->find($giftCard->id);
                    // amount_mismatch is resolvable here after the owner verifies the charge
                    if (! in_array($giftCard->status, ['unpaid', 'amount_mismatch'])) {
                        return;
                    }
                    $giftCard->activate($giftCard->transaction_reference ?: __('messages.manual_payment'));
                    $actionPerformed = true;
                    $didActivate = true;
                });
                break;

            case 'cancel':
                DB::transaction(function () use ($giftCard, &$actionPerformed) {
                    $giftCard = GiftCard::lockForUpdate()->find($giftCard->id);
                    if (! in_array($giftCard->status, ['unpaid', 'active', 'amount_mismatch'])) {
                        return;
                    }
                    $giftCard->status = 'cancelled';
                    $giftCard->save();
                    $actionPerformed = true;
                });
                break;

            case 'refund':
                DB::transaction(function () use ($giftCard, &$actionPerformed) {
                    $giftCard = GiftCard::lockForUpdate()->find($giftCard->id);
                    if ($giftCard->status !== 'active') {
                        return;
                    }
                    $giftCard->status = 'refunded';
                    $giftCard->save();
                    $actionPerformed = true;
                });
                break;

            default:
                return response()->json(['error' => __('messages.error')], 400);
        }

        if (! $actionPerformed) {
            return response()->json(['error' => __('messages.error')], 400);
        }

        $giftCard->refresh();

        $auditAction = match ($request->action) {
            'mark_paid' => AuditService::GIFT_CARD_PAID,
            'cancel' => AuditService::GIFT_CARD_CANCELLED,
            'refund' => AuditService::GIFT_CARD_REFUNDED,
        };
        AuditService::log($auditAction, $user->id, 'GiftCard', $giftCard->id,
            ['status' => $previousStatus], ['status' => $giftCard->status], 'sales_tab:role_id:'.$giftCard->role_id);

        if ($didActivate) {
            (new EmailService)->sendGiftCardEmails($giftCard);
        }

        return response()->json(['success' => true, 'status' => $giftCard->displayStatus()]);
    }

    public function resendEmail(Request $request, $gift_card_id)
    {
        $giftCard = GiftCard::with('role')->findOrFail(UrlUtils::decodeId($gift_card_id));
        $user = auth()->user();

        if ($giftCard->role->user_id !== $user->id) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        if ($giftCard->status !== 'active') {
            return response()->json(['error' => __('messages.error')], 400);
        }

        (new EmailService)->sendGiftCardEmails($giftCard, recipientOnly: true);

        return response()->json(['success' => true]);
    }

    private function canSellGiftCards(Role $role): bool
    {
        return $role->canSellGiftCards();
    }

    /**
     * Domain-separated HMAC for gift card payment URL callbacks, so a sale
     * token for id N can never activate gift card N.
     */
    public static function generateGiftCardPaymentUrlHmac(GiftCard $giftCard, $paymentSecret): string
    {
        return hash_hmac('sha256', 'gift-card:'.$giftCard->id, $paymentSecret);
    }

    private function verifyGiftCardPaymentUrlSecret(string $secret, GiftCard $giftCard, $user): bool
    {
        $expectedHmac = self::generateGiftCardPaymentUrlHmac($giftCard, $user->payment_secret);
        if (hash_equals($expectedHmac, $secret)) {
            return true;
        }

        // Legacy global secret fallback, matching the sale payment_url flow
        return hash_equals($user->payment_secret, $secret);
    }

    /**
     * Strip tags and control characters from user-entered text - these values
     * land in email subjects and admin tables.
     */
    private function cleanText(?string $value): string
    {
        $value = strip_tags(trim((string) $value));

        return trim(preg_replace('/[\x00-\x1F\x7F]/u', ' ', $value));
    }
}
