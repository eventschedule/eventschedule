<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Exceptions\EventCreationLimitException;
use App\Models\AnalyticsEventsDaily;
use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\AppointmentService;
use App\Services\AuditService;
use App\Services\WebhookService;
use App\Utils\MoneyUtils;
use App\Utils\TurnstileUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

/**
 * Guest-facing appointment booking: the public /book pages, the slots JSON API, the booking write
 * path, and the secret-link manage page. Owner-side type management lives in AppointmentTypeController.
 */
class AppointmentController extends Controller
{
    public function __construct(protected AppointmentService $appointments) {}

    /** GET /book - list bookable types (redirect when exactly one; 404 when none or gated). */
    public function showBook(Request $request, $subdomain)
    {
        $role = $this->resolveRole($subdomain);
        $this->assertNotGated($role);

        $types = $this->bookableTypes($role);
        if ($types->isEmpty()) {
            abort(404);
        }

        if ($types->count() === 1) {
            return redirect()->route('appointments.book_type', [
                'subdomain' => $role->subdomain,
                'typeSlug' => $types->first()->slug,
            ]);
        }

        return view('appointments.book', compact('role', 'types'));
    }

    /** GET /book/{typeSlug} - the slot picker, with the first available month rendered server-side. */
    public function showBookType(Request $request, $subdomain, $typeSlug)
    {
        $role = $this->resolveRole($subdomain);
        $type = $this->resolveBookableType($role, $typeSlug);

        $today = Carbon::now($type->timezone())->format('Y-m-d');
        $initial = $this->appointments->availableSlots($type, $today, 31);
        if (empty($initial['days']) && ! empty($initial['next_available_date'])) {
            $initial = $this->appointments->availableSlots($type, $initial['next_available_date'], 31);
        }

        return view('appointments.book-type', [
            'role' => $role,
            'type' => $type,
            'initialSlots' => $initial,
        ]);
    }

    /** GET /book/{typeSlug}/slots - JSON slot map for a month window. */
    public function slots(Request $request, $subdomain, $typeSlug)
    {
        $role = $this->resolveRole($subdomain);
        $type = $this->resolveBookableType($role, $typeSlug);

        $from = $request->input('from', Carbon::now($type->timezone())->format('Y-m-d'));
        $days = max(1, min(31, (int) $request->input('days', 31)));

        return response()->json($this->appointments->availableSlots($type, $from, $days));
    }

    /** POST /book/{typeSlug} - create the booking. Returns JSON (redirect_url | error | errors). */
    public function book(Request $request, $subdomain, $typeSlug)
    {
        // Honeypot: silently bounce back to the chooser.
        if ($request->filled('website')) {
            return response()->json(['redirect_url' => route('appointments.book', ['subdomain' => $subdomain])]);
        }

        $role = $this->resolveRole($subdomain);
        $type = $this->resolveBookableType($role, $typeSlug);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'slot' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/'],
            'guest_timezone' => 'nullable|string|max:64',
            'notes' => 'nullable|string|max:5000',
        ];
        if ($type->ask_phone) {
            $rules['phone'] = $type->require_phone ? 'required|string|max:50' : 'nullable|string|max:50';
        }
        $validated = $request->validate($rules);

        if (TurnstileUtils::isActiveForRequest()) {
            $request->validate(['cf-turnstile-response' => 'required']);
            if (! TurnstileUtils::verify($request->input('cf-turnstile-response'))) {
                return response()->json(['error' => __('messages.turnstile_verification_failed')], 422);
            }
        }

        // Daily creation cap (bookings create Events). The exception self-renders a 422 for JSON.
        if (! $role->canCreateEvent(auth()->user())) {
            throw new EventCreationLimitException;
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $request->input('phone'),
            'notes' => $request->input('notes'),
            'slot' => $validated['slot'],
            'guest_timezone' => $request->input('guest_timezone'),
            'custom_values' => $request->input('custom_values', []),
        ];

        try {
            $sale = $this->appointments->book($type, $role, $data, auth()->user());
        } catch (BusinessException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'slots' => $this->refreshDay($type, $validated['slot']),
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.error')], 500);
        }

        AuditService::log(AuditService::SALE_CHECKOUT, $sale->user_id, 'Sale', $sale->id, null, null, 'appointment:event_id:'.$sale->event_id);
        WebhookService::dispatch('sale.created', $sale);

        return $this->fanOut($request, $type, $role, $sale);
    }

    /** GET /appointment/view/{event_id}/{secret} - the guest manage/confirmation page. */
    public function manage(Request $request, $eventId, $secret)
    {
        [$event, $sale] = $this->resolveBookingBySecret($eventId, $secret);
        $role = $event->creatorRole;
        $type = $event->appointmentType;
        $state = $this->bookingState($event, $sale);

        return view('appointments.manage', compact('event', 'sale', 'role', 'type', 'state'));
    }

    /** Human-facing lifecycle state for the manage page. */
    protected function bookingState(Event $event, Sale $sale): string
    {
        if ($event->is_cancelled || in_array($sale->status, ['cancelled', 'refunded', 'expired'])) {
            return 'cancelled';
        }

        $pivot = $event->roles()->where('roles.id', $event->creator_role_id)->first()?->pivot;
        if ($pivot && is_null($pivot->is_accepted)) {
            return 'pending';
        }

        if ($sale->status !== 'paid' && in_array($event->payment_method, ['stripe', 'payment_url'], true)) {
            return 'awaiting_payment';
        }

        $startUtc = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC');
        if ($startUtc->isPast()) {
            return 'passed';
        }

        return 'confirmed';
    }

    /** POST /appointment/cancel/{event_id}/{secret} - guest cancels their booking. */
    public function cancelBooking(Request $request, $eventId, $secret)
    {
        [$event, $sale] = $this->resolveBookingBySecret($eventId, $secret);

        $manageParams = ['event_id' => $eventId, 'secret' => $secret];

        if (in_array($sale->status, ['cancelled', 'refunded', 'expired'])) {
            return redirect()->route('appointments.manage', $manageParams);
        }

        $startUtc = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC');
        if ($startUtc->isPast()) {
            return redirect()->route('appointments.manage', $manageParams)
                ->with('error', __('messages.appointments_cannot_cancel_past'));
        }

        $wasPaid = $sale->status === 'paid';

        DB::transaction(function () use ($sale) {
            $locked = Sale::whereKey($sale->id)->lockForUpdate()->first();
            if ($locked && ! in_array($locked->status, ['cancelled', 'refunded', 'expired'])) {
                // The Sale::booted hook soft-cancels the event and frees the slot.
                $locked->status = 'cancelled';
                $locked->save();
            }
        });

        if ($wasPaid) {
            AnalyticsEventsDaily::decrementSale($event->id, (float) $sale->payment_amount, $sale->created_at->toDateString());
        }

        // Guest cancelled - let the owner know.
        app(\App\Services\EmailService::class)->sendAppointmentOwnerCancellation($sale);

        return redirect()->route('appointments.manage', $manageParams)
            ->with('message', __('messages.appointments_cancelled_message'));
    }

    /**
     * Route a freshly-created booking to its next step. Free/cash confirm (or wait for approval)
     * and land on the manage page; paid types head to checkout (wired in Phase 4).
     */
    protected function fanOut(Request $request, AppointmentType $type, Role $role, Sale $sale)
    {
        if (! $type->isFree() && in_array($type->payment_method, ['stripe', 'payment_url'])) {
            return $this->initiatePayment($request, $type, $role, $sale);
        }

        // Free bookings realize a $0 sale immediately; cash increments on mark-paid.
        if ($type->isFree()) {
            AnalyticsEventsDaily::incrementSale($sale->event_id, 0);
        }

        if (! $type->requires_approval) {
            $this->appointments->confirm($sale);
        } else {
            // Pending: "request received" to the guest + a new-request notice to the owner.
            app(\App\Services\EmailService::class)->sendAppointmentPendingEmails($sale);
        }

        return response()->json(['redirect_url' => $this->manageUrl($sale).'?new=1']);
    }

    /** Paid checkout: hand the guest a Stripe session or the merchant payment URL (as JSON). */
    protected function initiatePayment(Request $request, AppointmentType $type, Role $role, Sale $sale)
    {
        if ($type->payment_method === 'stripe') {
            return response()->json(['redirect_url' => $this->stripeCheckoutUrl($sale, $type, $role)]);
        }

        // payment_url: the merchant's hosted page; payment_url.success/cancel confirm the sale.
        return response()->json(['redirect_url' => $role->user->payment_url]);
    }

    /** GET /appointment/checkout/success/{sale_id} - Stripe redirect target; the webhook marks paid. */
    public function checkoutSuccess(Request $request, $saleId)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($saleId));
        $event = $sale->event;
        if (! $event || ! $event->appointment_type_id) {
            abort(404);
        }

        $verified = false;
        if ($request->has('session_id')) {
            try {
                if ($request->query('direct') === '1') {
                    $stripe = new StripeClient(config('services.stripe_platform.secret'));
                    $session = $stripe->checkout->sessions->retrieve($request->session_id);
                } else {
                    $stripe = new StripeClient(config('services.stripe.key'));
                    $session = $stripe->checkout->sessions->retrieve($request->session_id, [], [
                        'stripe_account' => $event->user->stripe_account_id,
                    ]);
                }

                // The session must belong to this sale. Store the reference so the webhook can
                // reconcile; never mark paid here (the webhook does, with locking + amount validation).
                if (($session->metadata->sale_id ?? null) === UrlUtils::encodeId($sale->id)) {
                    $verified = true;
                    if ($sale->status !== 'paid') {
                        $sale->transaction_reference = $session->payment_intent;
                        $sale->save();
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Stripe session retrieval failed in appointment checkoutSuccess(): '.$e->getMessage());
            }
        }

        // Only hand back the secret-bearing manage URL after verifying the Stripe session belongs to
        // this sale - the encoded sale_id in the success_url is deliberately NOT secret. A direct or
        // unverified hit bounces to the schedule; the guest still gets the manage link by email.
        if ($verified) {
            return redirect($this->manageUrl($sale).'?new=1');
        }

        return redirect($event->creatorRole ? $event->creatorRole->getGuestUrl() : '/');
    }

    /** POST /appointment/pay/{event_id}/{secret} - retry payment for a still-unpaid booking. */
    public function pay(Request $request, $eventId, $secret)
    {
        [$event, $sale] = $this->resolveBookingBySecret($eventId, $secret);

        if ($sale->status === 'paid') {
            return redirect($this->manageUrl($sale));
        }
        if (in_array($sale->status, ['cancelled', 'refunded', 'expired'])) {
            return redirect($this->manageUrl($sale))->with('error', __('messages.appointments_booking_expired'));
        }

        $type = $event->appointmentType;
        $role = $event->creatorRole;

        if ($type && $type->payment_method === 'stripe') {
            return redirect($this->stripeCheckoutUrl($sale, $type, $role));
        }
        if ($type && $type->payment_method === 'payment_url' && $role->user->payment_url) {
            return redirect($role->user->payment_url);
        }

        return redirect($this->manageUrl($sale));
    }

    /** Create a Stripe Checkout session for the booking and return its URL (Connect or platform). */
    protected function stripeCheckoutUrl(Sale $sale, AppointmentType $type, Role $role): string
    {
        $unitAmountCents = (int) round($type->price * MoneyUtils::getSmallestUnitMultiplier($type->currency_code));
        $successParams = ['sale_id' => UrlUtils::encodeId($sale->id)];

        $sessionData = [
            'line_items' => [[
                'price_data' => [
                    'currency' => $type->currency_code,
                    'product_data' => ['name' => $type->name.' - '.$role->name],
                    'unit_amount' => $unitAmountCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $sale->email,
            'metadata' => ['sale_id' => UrlUtils::encodeId($sale->id)],
            'payment_intent_data' => ['metadata' => ['sale_id' => UrlUtils::encodeId($sale->id)]],
            'cancel_url' => $this->manageUrl($sale),
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

    protected function resolveRole($subdomain): Role
    {
        return Role::subdomain($subdomain)->firstOrFail();
    }

    protected function assertNotGated(Role $role): void
    {
        if (config('app.hosted') && ! $role->isPro()) {
            abort(404);
        }
    }

    /** Bookable, active, non-deleted types for the schedule (role relation pre-set to avoid N+1). */
    protected function bookableTypes(Role $role)
    {
        return $role->appointmentTypes()->active()->get()
            ->filter(function ($type) use ($role) {
                $type->setRelation('role', $role);

                return $type->isBookable();
            })
            ->values();
    }

    protected function resolveBookableType(Role $role, string $slug): AppointmentType
    {
        $this->assertNotGated($role);

        $type = $role->appointmentTypes()->active()->where('slug', $slug)->first();
        if (! $type) {
            abort(404);
        }
        $type->setRelation('role', $role);
        if (! $type->isBookable()) {
            abort(404);
        }

        return $type;
    }

    /** Decode the secret-link params to a booking (event + sale), 404 unless it is an appointment. */
    protected function resolveBookingBySecret($eventId, $secret): array
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        if (! $event->appointment_type_id) {
            abort(404);
        }
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->firstOrFail();

        return [$event, $sale];
    }

    protected function manageUrl(Sale $sale): string
    {
        return route('appointments.manage', [
            'event_id' => UrlUtils::encodeId($sale->event_id),
            'secret' => $sale->secret,
        ]);
    }

    /** Refreshed single-day slot map for the slot-taken recovery UI. */
    protected function refreshDay(AppointmentType $type, string $slotUtc): array
    {
        try {
            $date = Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $slotUtc, 'UTC')
                ->setTimezone($type->timezone())->format('Y-m-d');
        } catch (\Throwable $e) {
            $date = Carbon::now($type->timezone())->format('Y-m-d');
        }

        return $this->appointments->availableSlots($type, $date, 1);
    }
}
