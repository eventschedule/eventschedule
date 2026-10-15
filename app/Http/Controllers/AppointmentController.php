<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Exceptions\EventCreationLimitException;
use App\Models\AnalyticsEventsDaily;
use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\AppointmentBookingFlow;
use App\Services\AppointmentService;
use App\Services\AuditService;
use App\Services\Payments\CheckoutContext;
use App\Services\Payments\PaymentGatewayDriver;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\WebhookService;
use App\Traits\ReschedulesAppointments;
use App\Utils\HoneypotUtils;
use App\Utils\IcsUtils;
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
    use ReschedulesAppointments;

    public function __construct(protected AppointmentService $appointments) {}

    /** GET /book - list bookable types (redirect when exactly one; 404 when none). */
    public function showBook(Request $request, $subdomain)
    {
        $role = $this->resolveRole($subdomain);

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

        // The chooser names each type's host, and groups the cards by host when there are two.
        $types->loadMissing('host');

        return view('appointments.book', compact('role', 'types'));
    }

    /** GET /book/{typeSlug} - the slot picker, with the first available month rendered server-side. */
    public function showBookType(Request $request, $subdomain, $typeSlug)
    {
        $role = $this->resolveRole($subdomain);

        // A real, published slug that is simply not taking bookings renders an explanatory page
        // rather than a 404. Deliberately says nothing about plans: the guest is the schedule
        // owner's customer, not ours.
        if (! $this->bookableTypes($role)->firstWhere('slug', $typeSlug)) {
            if ($unavailable = $this->resolveUnavailableType($role, $typeSlug)) {
                return response()->view('appointments.book-type', [
                    'role' => $role,
                    'type' => $unavailable,
                    'initialSlots' => ['days' => []],
                    'unavailable' => true,
                ], 404);
            }
        }

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

        // Validated, because availableSlots() takes `string $fromDate`: ?from[]=x makes input('from')
        // an array, which is an uncaught TypeError -> 500 rather than a 422. Garbage STRINGS were already
        // safe (parseDay catches Throwable and falls back to today).
        $request->validate(['from' => 'nullable|date_format:Y-m-d', 'days' => 'nullable|integer']);

        $from = $request->input('from') ?: Carbon::now($type->timezone())->format('Y-m-d');
        $days = max(1, min(31, (int) $request->input('days', 31)));

        return response()->json($this->appointments->availableSlots($type, $from, $days));
    }

    /** POST /book/{typeSlug} - create the booking. Returns JSON (redirect_url | error | errors). */
    public function book(Request $request, $subdomain, $typeSlug)
    {
        // Honeypot. An error rather than the old silent bounce to the chooser, which
        // discarded a false positive's name, email and chosen slot without saying why.
        if (HoneypotUtils::isTripped($request)) {
            return response()->json(['error' => __('messages.invalid_request')], 422);
        }

        $role = $this->resolveRole($subdomain);
        $type = $this->resolveBookableType($role, $typeSlug);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'slot' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/'],
            // Deliberately NOT `timezone`: that rule tests DateTimeZone::ALL, which omits
            // backward-compat aliases - Asia/Calcutta, Europe/Kiev, Asia/Saigon and US/Eastern are all
            // valid zones that fail it. The picker posts whatever Intl.DateTimeFormat() reports, so the
            // strict rule rejected real browsers and the booking could not be completed at all.
            // Validation happens on READ instead: every consumer goes through Sale::guestTimezone() ->
            // AppointmentTimeUtils::resolveTimezone(), which membership-tests and falls back to the
            // schedule's zone, so nothing can hand a bad value to setTimezone().
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

        return $this->flow()->webResponse($type, $role, $sale);
    }

    /** GET /appointment/view/{event_id}/{secret} - the guest manage/confirmation page. */
    public function manage(Request $request, $eventId, $secret)
    {
        [$event, $sale] = $this->resolveBookingBySecret($eventId, $secret);
        $role = $event->creatorRole;
        $type = $event->appointmentType;
        $state = $this->bookingState($event, $sale);
        // Same helper the reschedule endpoints use, so the button can never offer something the POST
        // would refuse.
        $rescheduleBlocked = $this->rescheduleBlockedReason($event, $sale, $role);

        return view('appointments.manage', compact('event', 'sale', 'role', 'type', 'state', 'rescheduleBlocked'));
    }

    /**
     * GET /appointment/ical/{event_id}/{secret} - the .ics for a booking.
     *
     * Bookings are created with is_private = true, so the normal event iCal download aborts 404 for
     * the guest who made the booking. This serves the same invite the confirmation mail attaches,
     * behind the same secret link as the manage page.
     */
    public function ical(Request $request, $eventId, $secret)
    {
        [$event, $sale] = $this->resolveBookingBySecret($eventId, $secret);

        return response(IcsUtils::buildInvite($event, $event->creatorRole, $sale), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="appointment.ics"',
        ]);
    }

    /** GET /appointment/reschedule/{event_id}/{secret} - the picker, in reschedule mode. */
    public function showReschedule(Request $request, $eventId, $secret)
    {
        [$event, $sale] = $this->resolveBookingBySecret($eventId, $secret);
        $role = $event->creatorRole;
        $type = $event->appointmentType;

        if ($blocked = $this->rescheduleBlockedReason($event, $sale, $role)) {
            return redirect()->route('appointments.manage', [
                'event_id' => $eventId, 'secret' => $secret,
            ])->with('error', $blocked);
        }

        $today = Carbon::now($type->timezone())->format('Y-m-d');
        $initial = $this->appointments->availableSlots($type, $today, 31, null, true, $event->id);
        if (empty($initial['days']) && ! empty($initial['next_available_date'])) {
            $initial = $this->appointments->availableSlots($type, $initial['next_available_date'], 31, null, true, $event->id);
        }

        return view('appointments.book-type', [
            'role' => $role,
            'type' => $type,
            'initialSlots' => $initial,
            'mode' => 'reschedule',
            'sale' => $sale,
            'event' => $event,
            'rescheduleUrl' => route('appointments.reschedule.store', ['event_id' => $eventId, 'secret' => $secret], false),
            'rescheduleSlotsUrl' => route('appointments.reschedule_slots', ['event_id' => $eventId, 'secret' => $secret], false),
            'backUrl' => route('appointments.manage', ['event_id' => $eventId, 'secret' => $secret], false),
        ]);
    }

    /** GET /appointment/reschedule/{event_id}/{secret}/slots - slot JSON excluding this booking. */
    public function rescheduleSlots(Request $request, $eventId, $secret)
    {
        [$event, $sale] = $this->resolveBookingBySecret($eventId, $secret);
        $type = $event->appointmentType;

        if ($blocked = $this->rescheduleBlockedReason($event, $sale, $event->creatorRole)) {
            return response()->json(['error' => $blocked], 422);
        }

        // Validated, because availableSlots() takes `string $fromDate`: ?from[]=x makes input('from')
        // an array, which is an uncaught TypeError -> 500, and this URL carries the booking secret in
        // its path, so that 500 writes the secret into the log. Garbage STRINGS were already safe
        // (parseDay catches Throwable and falls back to today).
        $request->validate(['from' => 'nullable|date_format:Y-m-d', 'days' => 'nullable|integer']);

        $from = $request->input('from') ?: Carbon::now($type->timezone())->format('Y-m-d');
        $days = max(1, min(31, (int) $request->input('days', 31)));

        // excludeEventId is derived from the resolved sale, NEVER from the request: busyIntervals()
        // includes inbound-synced private events, so a caller-supplied id would read back the start
        // times and durations of the owner's personal calendar.
        return response()->json(
            $this->appointments->availableSlots($type, $from, $days, null, true, $event->id)
        );
    }

    /** POST /appointment/reschedule/{event_id}/{secret} - move the booking. Always returns JSON. */
    public function reschedule(Request $request, $eventId, $secret)
    {
        [$event, $sale] = $this->resolveBookingBySecret($eventId, $secret);
        $role = $event->creatorRole;

        if ($blocked = $this->rescheduleBlockedReason($event, $sale, $role, checkCooldown: false)) {
            return response()->json(['error' => $blocked], 422);
        }

        $validated = $request->validate([
            'slot' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/'],
            'from_slot' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/'],
            // Same reasoning as book(): `timezone` rejects real browser-reported aliases. The value is
            // re-validated on read by AppointmentTimeUtils::resolveTimezone().
            'guest_timezone' => 'nullable|string|max:64',
        ]);

        return $this->applyReschedule(
            $sale,
            $event,
            $role,
            $validated['slot'],
            'guest',
            $validated['from_slot'] ?? null,
            $validated['guest_timezone'] ?? null
        );
    }

    /** Human-facing lifecycle state for the manage page. */
    protected function bookingState(Event $event, Sale $sale): string
    {
        if ($event->is_cancelled || in_array($sale->status, ['cancelled', 'refunded', 'expired'])) {
            return 'cancelled';
        }

        // Payment before approval. The booking flow sends a priced booking to checkout before the approval
        // step, so an approval type paid online is unpaid AND pending at once, and the payment is
        // what the guest has to act on. Checked the other way round, a guest who backed out of the
        // payment page read "Request sent" with no way to pay, and one whose payment was under
        // review was offered Cancel, which would leave the money with no booking.
        if ($sale->status !== 'paid' && AppointmentType::paysOnline($event->payment_method)) {
            $gateways = app(PaymentGatewayManager::class);

            // Money arrived and did not match (SaleSettlementService parks the sale as
            // amount_mismatch for a person to look at). Asking for it again would charge again, and
            // Cancel would read the sale as unpaid and tell the owner nothing about the money.
            if ($sale->status !== 'unpaid') {
                return 'in_review';
            }

            if ($gateways->awaitsConfirmation($sale->payment_method, $sale)) {
                // Back from the provider before its confirmation reached us (Stripe's webhook,
                // Payfast's ITN). The guest has just paid, so offering "Complete payment" would
                // start a second payment. Same rule and same window as the ticket page (ticket/view).
                if (PaymentGatewayDriver::landedSecondsLeft($sale) > 0) {
                    return 'confirming';
                }

                // Still nothing once the window has closed. An Instant EFT can clear hours later,
                // and a second Payfast payment settles as already_paid with nobody told; PayPal says
                // outright that it is reviewing one. Either way the guest is not asked to pay again.
                if (! $gateways->canResumePayment($sale->payment_method, $sale)
                    || PaymentGatewayDriver::hasLanded($sale)) {
                    return 'in_review';
                }
            }

            // Nothing is taken for a time that has gone. A day's hold (Payfast, a payment link) can
            // outlast an appointment booked for the next morning.
            if ($event->getStartDateTime()->isPast()) {
                return 'passed';
            }

            return 'awaiting_payment';
        }

        $pivot = $event->roles()->where('roles.id', $event->creator_role_id)->first()?->pivot;
        if ($pivot && is_null($pivot->is_accepted)) {
            return 'pending';
        }

        // getStartDateTime() returns UTC here and tolerates a date-only starts_at; a bare
        // createFromFormat('Y-m-d H:i:s') would throw on one and 500 the page.
        if ($event->getStartDateTime()->isPast()) {
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

        if ($event->getStartDateTime()->isPast()) {
            return redirect()->route('appointments.manage', $manageParams)
                ->with('error', __('messages.appointments_cannot_cancel_past'));
        }

        // Not while a payment is on its way or waiting on a person (the page offers no Cancel then,
        // and a stale tab or a hand-made post must not either): the sale reads unpaid, so the owner
        // would be told nothing about money that has been taken or is about to be.
        if (in_array($this->bookingState($event, $sale), ['confirming', 'in_review'], true)) {
            return redirect()->route('appointments.manage', $manageParams);
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

        // Guest cancelled - let the owner know (paid state captured pre-cancel for the refund note).
        app(\App\Services\EmailService::class)->sendAppointmentOwnerCancellation($sale, $wasPaid && (float) $sale->payment_amount > 0);

        return redirect()->route('appointments.manage', $manageParams)
            ->with('message', __('messages.appointments_cancelled_message'));
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
            // Opens the manage page's "Confirming your payment" window until the webhook lands.
            PaymentGatewayDriver::markLanded($sale);

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

        // Only a booking that is waiting for its guest to pay. One whose payment is on its way
        // (confirming, or under review) must not start a second one, and a cash booking has
        // nothing to pay online. The manage page offers the button on the same answer.
        if ($this->bookingState($event, $sale) !== 'awaiting_payment') {
            return redirect($this->manageUrl($sale));
        }

        $role = $event->creatorRole;

        // Keyed on the SALE's method, not the type's: that is what the booking was made on, what the
        // generic return and webhook routes check, and it cannot drift if the owner edits the type
        // later.
        if ($sale->payment_method === 'stripe') {
            // The owner may have unlinked Stripe since the booking was made. Asked here, of the same
            // account stripeCheckoutUrl() charges, because it falls back to the platform's own key
            // when that account is not connected, which on hosted would take the guest's money into
            // Event Schedule's.
            if (! $role?->user?->canAcceptStripePayments()) {
                return redirect($this->manageUrl($sale))->with('error', __('messages.appointments_payment_unavailable', ['schedule' => $role?->name ?? '']));
            }

            return redirect($this->flow()->stripeCheckoutUrl($sale, $event, $role));
        }
        if ($sale->payment_method === 'payment_url') {
            return redirect($role->user->payment_url ?: $this->manageUrl($sale));
        }

        // Any other gateway goes through its driver, whose return lands the guest back on the manage
        // page (PaymentGatewayDriver::redirectToPurchaseLanding()). Asked first whether it can still
        // take this booking (connected, this currency, this amount: the test the booking page applied),
        // because a driver that cannot start gives a ticket's seats back, and a booking kept by a guest
        // who backed out must not be cancelled because the owner disconnected the gateway since.
        $gateways = app(PaymentGatewayManager::class);
        $available = $gateways->availableFor($event->user, $event->ticket_currency_code, (float) $sale->payment_amount);

        if (! isset($available[$sale->payment_method])) {
            return redirect($this->manageUrl($sale))->with('error', __('messages.appointments_payment_unavailable', ['schedule' => $role?->name ?? '']));
        }

        return $available[$sale->payment_method]
            ->startCheckout(new CheckoutContext($sale, $event, $sale->subdomain ?: $role->subdomain));
    }

    /**
     * Every guest booking route's schedule. A deleted one, or an unpublished one to anybody outside
     * it, answers as an unknown subdomain does: the booking pages used to show either, by name.
     */
    protected function resolveRole($subdomain): Role
    {
        return Role::findForGuestOrFail($subdomain);
    }

    /**
     * Bookable types for the schedule.
     *
     * Booking itself is on every plan. Two things drop out here: a PRICED type on a schedule that
     * may not charge (AppointmentType::canTakePayment()) or whose payment method has gone, and
     * anything past the free plan's one-type allowance (Role::appointmentTypeLimit()). A schedule
     * that lapsed from Pro keeps every type it created; they simply stop appearing until it
     * upgrades.
     */
    protected function bookableTypes(Role $role)
    {
        return $role->bookableAppointmentTypes();
    }

    protected function resolveBookableType(Role $role, string $slug): AppointmentType
    {
        $type = $this->bookableTypes($role)->firstWhere('slug', $slug);

        if (! $type) {
            abort(404);
        }

        return $type;
    }

    /**
     * A type that exists on this schedule but is not currently bookable, or null.
     *
     * Told apart from "no such slug" on purpose. These URLs are published: they sit in calendar
     * invites and confirmation emails, and a plan lapse should not turn them into a 404 that reads
     * as a broken link. Mirrors Event::blockedByPlanOnly(), which exists for exactly this reason on
     * the ticket side.
     *
     * is_active is filtered even though the page this feeds is already a 404, because that page
     * renders the type's NAME and DESCRIPTION to explain itself. An inactive type was never
     * published, so it has no circulating URL to keep alive and nothing to explain - and Clone
     * creates its copy inactive, which would otherwise put a draft's name on a public page for
     * anyone holding the slug.
     */
    protected function resolveUnavailableType(Role $role, string $slug): ?AppointmentType
    {
        return $role->appointmentTypes()
            ->where('slug', $slug)
            ->where('is_deleted', false)
            ->where('is_active', true)
            ->first();
    }

    /** Decode the secret-link params to a booking (event + sale), 404 unless it is an appointment. */
    protected function resolveBookingBySecret($eventId, $secret): array
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        if (! $event->appointment_type_id) {
            abort(404);
        }
        // is_deleted was never filtered here. A deleted sale is invisible in the owner's Bookings list
        // (that query filters it) but would still resolve and be reschedulable, leaving ticket.sold
        // keyed to a date nothing points at any more.
        $sale = Sale::where('event_id', $event->id)
            ->where('secret', $secret)
            ->where('is_deleted', false)
            ->firstOrFail();

        return [$event, $sale];
    }

    protected function manageUrl(Sale $sale): string
    {
        return $this->flow()->manageUrl($sale);
    }

    /** What follows book(), shared with the API (AppointmentBookingFlow). */
    protected function flow(): AppointmentBookingFlow
    {
        return app(AppointmentBookingFlow::class);
    }

    /**
     * Refreshed single-day slot map for the slot-taken recovery UI, for the BOOKING path.
     *
     * No exclusion or owner-mode arguments: there is no event yet when booking, and the reschedule paths
     * use ReschedulesAppointments::refreshDayForReschedule(), which needs both. Those two parameters were
     * added here as well and no caller ever passed them.
     */
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
