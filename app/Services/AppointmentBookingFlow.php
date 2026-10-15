<?php

namespace App\Services;

use App\Models\AnalyticsEventsDaily;
use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Illuminate\Http\JsonResponse;
use Stripe\StripeClient;

/**
 * What happens to a booking once AppointmentService::book() has written it, for the booking form
 * (AppointmentController) and the API (Api\ApiAppointmentController) alike. The two used to be one
 * controller's private methods; the API needs the same confirm-or-wait step and must not grow a
 * second copy that drifts.
 *
 * The one place they part: the form sends a guest who pays online straight to a payment page,
 * while the API cannot (the caller is an agent, and a Stripe session expires), so it mails the
 * guest the manage page, where they pay.
 */
class AppointmentBookingFlow
{
    public function __construct(protected AppointmentService $appointments) {}

    /** Whether the booking waits for an online payment before anything else happens. */
    public function paysOnline(AppointmentType $type): bool
    {
        return ! $type->isFree() && AppointmentType::paysOnline($type->payment_method);
    }

    /**
     * The booking form's answer, as JSON (the form posts over fetch). Free and cash confirm, or
     * wait for approval, and land on the manage page; a priced type paid online heads to checkout.
     */
    public function webResponse(AppointmentType $type, Role $role, Sale $sale): JsonResponse
    {
        if ($this->paysOnline($type)) {
            return $this->initiatePayment($type, $role, $sale);
        }

        $this->settle($type, $sale);

        return response()->json([
            'redirect_url' => $this->manageUrl($sale).'?new=1',
            'manage_url' => $this->managePath($sale),
        ]);
    }

    /**
     * A booking that is not waiting for an online payment: a free one realises a $0 sale at once
     * (cash is counted when it is marked paid), then it is confirmed, or the guest is told it was
     * received and the owner that it waits for them.
     */
    public function settle(AppointmentType $type, Sale $sale): void
    {
        if ($type->isFree()) {
            AnalyticsEventsDaily::incrementSale($sale->event_id, 0);
        }

        if (! $type->requires_approval) {
            $this->appointments->confirm($sale);
        } else {
            app(EmailService::class)->sendAppointmentPendingEmails($sale);
        }
    }

    /**
     * Paid checkout, answered as JSON because the booking form posts over fetch: a Stripe session,
     * the merchant payment URL, or - for any other gateway - the pay step, which the form submits as
     * an ordinary navigation so the driver can answer with a redirect or a page of its own (Payfast
     * hands the buyer on with an auto-submitting form, which a fetch cannot follow).
     *
     * Every answer also carries the booking's own page. The guest is about to leave for a payment
     * page, and a browser that brings the booking form back from its cache (the Back button) shows
     * it as it was left: the button still busy, and the slot already held by this booking. The form
     * sends them to manage_url instead (appointments/book-type).
     */
    public function initiatePayment(AppointmentType $type, Role $role, Sale $sale): JsonResponse
    {
        $managePath = $this->managePath($sale);

        if ($type->payment_method === 'stripe') {
            return response()->json([
                'redirect_url' => $this->stripeCheckoutUrl($sale, $sale->event, $role),
                'manage_url' => $managePath,
            ]);
        }

        if ($type->payment_method === 'payment_url') {
            // The guest leaves for the merchant's hosted page and there is no callback, so email
            // them the manage link + "complete your payment" note NOW - it is their only artifact
            // until the owner marks the sale paid (which then sends the confirmation).
            app(EmailService::class)->sendAppointmentPaymentDueEmail($sale);

            return response()->json(['redirect_url' => $role->user->payment_url, 'manage_url' => $managePath]);
        }

        // Path-relative, like the form's own bookUrl: the form posts it from whichever host the
        // booking page was served on, a schedule's custom domain included.
        return response()->json([
            'pay_url' => route('appointments.pay', $this->manageParams($sale), false),
            'manage_url' => $managePath,
        ]);
    }

    /**
     * Create a Stripe Checkout session for the booking and return its URL (Connect or platform).
     *
     * Charges what the booking was made for (the sale's amount, in the currency stamped on its
     * event), never the type's current price: the webhook settles against payment_amount, so a type
     * repriced after the booking would park the sale as amount_mismatch, and one made free would ask
     * Stripe for a zero-amount session.
     */
    public function stripeCheckoutUrl(Sale $sale, Event $event, Role $role): string
    {
        $currency = $event->ticket_currency_code ?: ($event->appointmentType?->currency_code ?? 'USD');
        $unitAmountCents = (int) round((float) $sale->payment_amount * MoneyUtils::getSmallestUnitMultiplier($currency));
        $successParams = ['sale_id' => UrlUtils::encodeId($sale->id)];

        $sessionData = [
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => ['name' => ($event->appointmentType?->name ?? $event->name).' - '.$role->name],
                    'unit_amount' => $unitAmountCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $sale->email,
            'metadata' => ['sale_id' => UrlUtils::encodeId($sale->id)],
            'payment_intent_data' => ['metadata' => ['sale_id' => UrlUtils::encodeId($sale->id)]],
            // The manage page says the payment was cancelled and nothing was charged, and keeps the
            // hold (appointments/manage).
            'cancel_url' => $this->manageUrl($sale).'?payment=cancelled',
        ];

        // Stripe Connect (hosted, owner has a connected account) vs the platform key (selfhosted).
        if (config('app.hosted') && $role->user->stripe_account_id) {
            $stripe = new StripeClient(config('services.stripe.key'));
            $sessionData['success_url'] = custom_domain_url(route('appointments.checkout_success', $successParams).'?session_id={CHECKOUT_SESSION_ID}');
            $session = $stripe->checkout->sessions->create($sessionData, ['stripe_account' => $role->user->stripe_account_id]);
        } else {
            $stripe = new StripeClient(config('services.stripe_platform.secret'));
            $sessionData['success_url'] = custom_domain_url(route('appointments.checkout_success', $successParams).'?session_id={CHECKOUT_SESSION_ID}&direct=1');
            $session = $stripe->checkout->sessions->create($sessionData);
        }

        return $session->url;
    }

    /** The guest's manage page, absolute. */
    public function manageUrl(Sale $sale): string
    {
        return route('appointments.manage', $this->manageParams($sale));
    }

    /** The manage page as a path, for the booking form to navigate to from whatever host it is on. */
    public function managePath(Sale $sale): string
    {
        return route('appointments.manage', $this->manageParams($sale), false);
    }

    /** @return array{event_id: string, secret: string} */
    public function manageParams(Sale $sale): array
    {
        return ['event_id' => UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret];
    }
}
