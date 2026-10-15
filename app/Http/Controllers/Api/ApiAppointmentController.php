<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BusinessException;
use App\Exceptions\SlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\AppointmentType;
use App\Models\Role;
use App\Models\Sale;
use App\Services\AppointmentBookingFlow;
use App\Services\AppointmentService;
use App\Services\AuditService;
use App\Services\EmailService;
use App\Services\WebhookService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * Appointments over the API: the types a schedule takes bookings for, their open times, and a
 * booking. Written for an agent that talks to the guest (a voice agent on a phone line, most
 * often), so every time comes with a sentence it can read out, and a taken time comes back with
 * the nearest ones still open.
 *
 * The key's account must be an owner or admin of the schedule, and the schedule Pro, as for every
 * other write here. A booking is the guest's, not the caller's: book() is handed no user, so the
 * caller never becomes the sale's buyer, and the sale is marked as made through the API so the
 * owner checks a name or address an agent may have misheard.
 *
 * What follows a booking (confirm, wait for approval, or wait for payment) is the booking form's
 * own step (AppointmentBookingFlow), except that a guest who pays online is emailed the manage
 * page at once, because an agent cannot hand them a payment page and a Stripe session expires.
 */
class ApiAppointmentController extends Controller
{
    /** Days of slots a request returns when it does not ask. */
    protected const DEFAULT_DAYS = 7;

    /** How long a booking's answer is kept for a repeat carrying the same Idempotency-Key. */
    protected const IDEMPOTENCY_HOURS = 24;

    public function __construct(
        protected AppointmentService $appointments,
        protected AppointmentBookingFlow $flow,
    ) {}

    /** GET /api/schedules/{subdomain}/appointment-types */
    public function types(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->where('is_deleted', false)->first();

        if (! $role) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        if ($refusal = $this->refusal($role)) {
            return $refusal;
        }

        return $this->inScheduleLocale($role, function () use ($role) {
            $data = $this->bookableTypes($role)
                ->map(fn (AppointmentType $type) => $this->typeData($type, $role))
                ->values();

            return response()->json([
                'data' => $data,
                'meta' => [
                    'schedule' => $role->subdomain,
                    'timezone' => $role->timezone ?: config('app.timezone'),
                ],
            ], 200, [], JSON_PRETTY_PRINT);
        });
    }

    /** GET /api/appointment-types/{id}/slots?from=&days= */
    public function slots(Request $request, $id)
    {
        try {
            $request->validate([
                'from' => 'nullable|date_format:Y-m-d',
                'days' => 'nullable|integer|min:1|max:31',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }

        [$type, $role, $refusal] = $this->resolveType($id);
        if ($refusal) {
            return $refusal;
        }

        return $this->inScheduleLocale($role, function () use ($request, $type, $role) {
            $tz = $type->timezone();
            $from = $request->input('from') ?: Carbon::now($tz)->format('Y-m-d');
            $days = (int) $request->input('days', self::DEFAULT_DAYS);

            $result = $this->appointments->availableSlots($type, $from, $days);

            return response()->json([
                'data' => $this->flatten($result, $role),
                'meta' => [
                    'appointment_type_id' => UrlUtils::encodeId($type->id),
                    'timezone' => $tz,
                    'from' => $from,
                    'days' => $days,
                    // Set only when this range has nothing open: the first time that is, so the
                    // agent can offer it rather than say there is nothing.
                    'next_available' => empty($result['days']) ? $this->firstSlot($type, $role, $result['next_available_date']) : null,
                ],
            ], 200, [], JSON_PRETTY_PRINT);
        });
    }

    /** POST /api/appointment-types/{id}/bookings */
    public function book(Request $request, $id)
    {
        // Who may call, first; whether the type still takes bookings comes after the
        // Idempotency-Key, so a repeat of a booking that went through is answered with it even if
        // the type has filled up or been switched off since.
        [$type, $role, $refusal] = $this->resolveType($id, requireBookable: false);
        if ($refusal) {
            return $refusal;
        }

        $create = function () use ($request, $type, $role) {
            [$bookable, $refusal] = $this->bookable($type, $role);

            return $refusal ?? $this->inScheduleLocale($role, fn () => $this->createBooking($request, $bookable, $role));
        };

        $idempotencyKey = $request->header('Idempotency-Key');
        if ($idempotencyKey !== null && ! preg_match('/^[A-Za-z0-9_.:-]{1,64}$/', $idempotencyKey)) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => ['Idempotency-Key' => ['Use up to 64 letters, digits, dots, colons, dashes or underscores.']],
            ], 422);
        }

        if ($idempotencyKey === null) {
            return $create();
        }

        // A repeat of a booking that went through answers with that booking, whatever happened
        // since: an agent that lost the answer to a timeout must not book the guest twice.
        $cacheKey = 'api_appointment_booking:'.auth()->id().':'.hash('sha256', $idempotencyKey);
        $fingerprint = hash('sha256', json_encode([$type->id, $request->all()]));

        $lock = Cache::lock($cacheKey.':lock', 30);
        if (! $lock->get()) {
            return response()->json(['error' => 'idempotency_key_in_use', 'message' => 'A request with this Idempotency-Key is still being handled.'], 409);
        }

        try {
            $stored = Cache::get($cacheKey);
            if (is_array($stored)) {
                if (($stored['fingerprint'] ?? null) !== $fingerprint) {
                    return response()->json(['error' => 'idempotency_key_reused', 'message' => 'This Idempotency-Key was used for a different request.'], 422);
                }

                return response()->json($stored['body'], $stored['status'], ['Idempotent-Replayed' => 'true'], JSON_PRETTY_PRINT);
            }

            $response = $create();

            // Only a booking is kept: a refusal is answered again, so a retry after the slot was
            // freed, or after a fix to the request, is free to succeed.
            if ($response->getStatusCode() === 201) {
                Cache::put($cacheKey, [
                    'fingerprint' => $fingerprint,
                    'status' => 201,
                    'body' => $response->getData(true),
                ], now()->addHours(self::IDEMPOTENCY_HOURS));
            }

            return $response;
        } finally {
            $lock->release();
        }
    }

    protected function createBooking(Request $request, AppointmentType $type, Role $role): JsonResponse
    {
        $rules = [
            'slot' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/'],
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            // Not `timezone`, for the reason AppointmentController::book() gives: aliases a real
            // client reports fail it. Read through Sale::guestTimezone(), which falls back.
            'guest_timezone' => 'nullable|string|max:64',
            'notes' => 'nullable|string|max:5000',
            'answers' => 'nullable|array',
            'answers.*' => ['nullable', $this->answerRule()],
        ];
        if ($type->ask_phone) {
            $rules['phone'] = $type->require_phone ? 'required|string|max:50' : 'nullable|string|max:50';
        }

        // A question's own rule replaces the wildcard's for its key (that is how Laravel merges
        // them), so it carries the answer rule again.
        $questionRules = [];
        foreach ($this->questions($type) as $question) {
            $questionRules['answers.'.$question['key']] = [$question['required'] ? 'required' : 'nullable', $this->answerRule()];
        }

        try {
            $validated = $request->validate($rules + $questionRules);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }

        // Bookings create events, so the daily creation cap applies, as on the booking form.
        if (! $role->canCreateEvent(auth()->user())) {
            return response()->json(['error' => 'The daily limit for new bookings on this schedule has been reached'], 422);
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $type->ask_phone ? ($validated['phone'] ?? null) : null,
            'notes' => $validated['notes'] ?? null,
            'slot' => $validated['slot'],
            'guest_timezone' => $validated['guest_timezone'] ?? null,
            'custom_values' => is_array($validated['answers'] ?? null) ? $validated['answers'] : [],
            'source' => 'api',
        ];

        try {
            // No user: the API caller is the schedule's own agent, never the guest, and a sale
            // keyed to them would put the booking in their own tickets.
            $sale = $this->appointments->book($type, $role, $data, null);
        } catch (SlotUnavailableException $e) {
            // Taken by this same guest: the retry of a booking whose answer was lost. Said as such,
            // with the booking, rather than offering them other times.
            if ($existing = $this->existingBooking($role, $data['email'], $data['slot'])) {
                return response()->json([
                    'error' => 'already_booked',
                    'message' => __('messages.appointments_already_booked'),
                    'booking' => $this->bookingData($existing, $existing->event->appointmentType ?? $type, $role, $this->statusOf($existing), false),
                ], 409);
            }

            return response()->json([
                'error' => 'slot_taken',
                'message' => $e->getMessage(),
                'alternatives' => $this->alternatives($type, $role, $validated['slot']),
            ], 409);
        } catch (BusinessException $e) {
            if ($e->getCode() === AppointmentService::ALREADY_BOOKED) {
                // The guest already holds this exact time: say which booking that is, so an agent
                // that lost the first answer can read it back instead of booking again.
                $existing = $this->existingBooking($role, $data['email'], $data['slot']);

                return response()->json([
                    'error' => 'already_booked',
                    'message' => $e->getMessage(),
                    'booking' => $existing ? $this->bookingData($existing, $existing->event->appointmentType ?? $type, $role, $this->statusOf($existing), false) : null,
                ], 409);
            }

            return response()->json(['error' => $e->getMessage()], 422);
        } catch (QueryException $e) {
            report($e);

            return response()->json(['error' => 'The booking could not be saved. Please try again.'], 500);
        }

        AuditService::log(AuditService::SALE_CHECKOUT, auth()->id(), 'Sale', $sale->id, null, null, 'appointment_api:event_id:'.$sale->event_id);
        WebhookService::dispatch('sale.created', $sale);

        $mail = app(EmailService::class);
        // The same test the guest mails apply (a reserved test address is never mailed).
        $emailsSent = $mail->appointmentGuestMailGoesOut($sale);

        if ($this->flow->paysOnline($type)) {
            // The guest pays from the manage page, which this mail links to. Sent now, because
            // nothing else will reach them: the booking form would have opened a payment page.
            $mail->sendAppointmentPaymentDueEmail($sale);
            $status = 'awaiting_payment';
        } else {
            $this->flow->settle($type, $sale);
            $status = $type->requires_approval ? 'pending_approval' : 'confirmed';
        }

        $sale->refresh()->load('event.appointmentHost');

        return response()->json(['data' => $this->bookingData($sale, $type, $role, $status, $emailsSent)], 201, [], JSON_PRETTY_PRINT);
    }

    /**
     * An answer is text or a number, or a list of them (a question with several choices), each up
     * to 1000 characters. Anything deeper cannot be stored in an answer's column, and used to reach
     * it as an array and fail there.
     */
    protected function answerRule(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) {
            $plain = fn ($v) => (is_string($v) && mb_strlen($v) <= 1000) || is_int($v) || is_float($v);

            if ($value === null || $plain($value)) {
                return;
            }
            if (is_array($value) && array_is_list($value) && collect($value)->every($plain)) {
                return;
            }

            $fail('An answer must be text or a number, or a list of them, each up to 1000 characters.');
        };
    }

    /**
     * The key's account must run the schedule (owner or admin, with the plan letting a team
     * member in), and the schedule must be Pro, as for every write in this API.
     */
    protected function refusal(Role $role): ?JsonResponse
    {
        $user = auth()->user();

        $runs = $user->roles()
            ->where('roles.id', $role->id)
            ->wherePivotIn('level', ['owner', 'admin'])
            ->exists();

        if (! $runs || ! $user->planAllowsTeamAccess($role)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $role->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        return null;
    }

    /** @return array{0: ?AppointmentType, 1: ?Role, 2: ?JsonResponse} */
    protected function resolveType($id, bool $requireBookable = true): array
    {
        $type = AppointmentType::where('is_deleted', false)->find(UrlUtils::decodeId((string) $id));
        $role = $type ? Role::where('is_deleted', false)->find($type->role_id) : null;

        if (! $type || ! $role) {
            return [null, null, response()->json(['error' => 'Appointment type not found'], 404)];
        }

        if ($refusal = $this->refusal($role)) {
            return [null, null, $refusal];
        }

        if (! $requireBookable) {
            return [$type, $role, null];
        }

        [$bookable, $refusal] = $this->bookable($type, $role);

        return [$bookable, $refusal ? null : $role, $refusal];
    }

    /**
     * The type as the types endpoint lists it, or a refusal: a type it does not offer cannot be
     * booked or asked about.
     *
     * @return array{0: ?AppointmentType, 1: ?JsonResponse}
     */
    protected function bookable(AppointmentType $type, Role $role): array
    {
        $bookable = $this->bookableTypes($role)->firstWhere('id', $type->id);

        return $bookable
            ? [$bookable, null]
            : [null, response()->json(['error' => 'This appointment type is not taking bookings'], 422)];
    }

    /**
     * The schedule's bookable types (the guest /book page's list), less the ones that need mail
     * when the schedule cannot send appointment mail: a guest booked by phone pays, or hears that
     * the schedule accepted the request, only by email, and on hosted that needs the schedule's
     * own mail settings. So without mail, only a free type that confirms at once is offered.
     */
    protected function bookableTypes(Role $role)
    {
        $canMail = app(EmailService::class)->appointmentCanSend($role);

        return $role->bookableAppointmentTypes()
            ->filter(fn (AppointmentType $type) => $canMail || ($type->isFree() && ! $type->requires_approval))
            ->each(fn (AppointmentType $type) => $type->loadMissing('host'))
            ->values();
    }

    protected function typeData(AppointmentType $type, Role $role): array
    {
        return [
            'id' => UrlUtils::encodeId($type->id),
            'name' => $type->name,
            'description' => $type->description,
            'duration_minutes' => (int) $type->duration_minutes,
            'price' => $type->isFree() ? 0 : (float) $type->price,
            'currency' => $type->isFree() ? null : $type->currency_code,
            // free, cash (paid at the appointment, the booking confirms at once) or online (the
            // guest is emailed a link to pay, and the time is held until then).
            'payment' => $type->isFree() ? 'free' : ($this->flow->paysOnline($type) ? 'online' : 'cash'),
            'location_type' => $type->location_type,
            // Where the guest goes. A meeting link is sent with the confirmation, not here.
            'location' => match ($type->location_type) {
                'in_person' => $type->location_address ?: null,
                'phone' => $type->location_phone ?: null,
                default => null,
            },
            'host_name' => $type->hostName(),
            'timezone' => $type->timezone(),
            'requires_approval' => (bool) $type->requires_approval,
            'asks_phone' => (bool) $type->ask_phone,
            'requires_phone' => (bool) ($type->ask_phone && $type->require_phone),
            'questions' => $this->questions($type),
            'next_available' => $this->nextAvailable($type, $role),
            'booking_url' => route('appointments.book_type', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]),
        ];
    }

    /**
     * The type's own questions (custom_fields), keyed as the booking's `answers` are.
     *
     * @return array<int, array{key: string, label: string, type: string, required: bool, options: array<int, string>}>
     */
    protected function questions(AppointmentType $type): array
    {
        $questions = [];

        foreach ((array) ($type->custom_fields ?? []) as $key => $field) {
            if (! is_array($field) || trim((string) ($field['name'] ?? '')) === '') {
                continue;
            }

            $options = $field['options'] ?? [];
            if (is_string($options)) {
                $options = array_values(array_filter(array_map('trim', explode(',', $options)), 'strlen'));
            }

            $questions[] = [
                'key' => (string) $key,
                'label' => (string) $field['name'],
                'type' => (string) ($field['type'] ?? 'string'),
                'required' => ! empty($field['required']),
                'options' => array_values((array) $options),
            ];
        }

        return $questions;
    }

    /** The first open time from today, or null. */
    protected function nextAvailable(AppointmentType $type, Role $role): ?array
    {
        $today = Carbon::now($type->timezone())->format('Y-m-d');
        $result = $this->appointments->availableSlots($type, $today, 31);

        if (! empty($result['days'])) {
            return $this->flatten($result, $role)[0] ?? null;
        }

        return $this->firstSlot($type, $role, $result['next_available_date']);
    }

    protected function firstSlot(AppointmentType $type, Role $role, ?string $date): ?array
    {
        if (! $date) {
            return null;
        }

        return $this->flatten($this->appointments->availableSlots($type, $date, 1, null, false), $role)[0] ?? null;
    }

    /**
     * Slots as a flat list, each with its UTC instant and a sentence in the schedule's zone.
     *
     * @return array<int, array{starts_at: string, date: string, label: string}>
     */
    protected function flatten(array $result, Role $role): array
    {
        $tz = $result['schedule_timezone'] ?? ($role->timezone ?: config('app.timezone'));
        $slots = [];

        foreach ($result['days'] ?? [] as $date => $daySlots) {
            foreach ($daySlots as $slot) {
                $slots[] = [
                    'starts_at' => $slot['utc'],
                    'date' => $date,
                    'label' => $this->label(Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $slot['utc'], 'UTC'), $tz, $role),
                ];
            }
        }

        return $slots;
    }

    /** "Tuesday, October 20 at 2:30 PM EDT", in the schedule's zone, language and clock. */
    protected function label(Carbon $utc, string $tz, Role $role): string
    {
        $local = $utc->copy()->setTimezone($tz);

        return __('messages.appointments_slot_label', [
            'date' => $local->translatedFormat('l, F j'),
            'time' => $local->format($role->use_24_hour_time ? 'H:i' : 'g:i A').' '.$local->format('T'),
        ]);
    }

    /**
     * The three open times nearest the one that was taken, looked for on its day and the week
     * after it (and from today, when that week is empty).
     */
    protected function alternatives(AppointmentType $type, Role $role, string $slotUtc): array
    {
        try {
            $wanted = Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $slotUtc, 'UTC');
        } catch (\Throwable $e) {
            return [];
        }

        $tz = $type->timezone();
        $from = $wanted->copy()->setTimezone($tz)->subDay()->format('Y-m-d');
        $result = $this->appointments->availableSlots($type, $from, 8);

        if (empty($result['days']) && $result['next_available_date']) {
            $result = $this->appointments->availableSlots($type, $result['next_available_date'], 8, null, false);
        }

        return collect($this->flatten($result, $role))
            ->sortBy(fn ($slot) => abs(Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $slot['starts_at'], 'UTC')->diffInSeconds($wanted, false)))
            ->take(3)
            ->sortBy('starts_at')
            ->values()
            ->all();
    }

    protected function bookingData(Sale $sale, AppointmentType $type, Role $role, string $status, bool $emailsSent): array
    {
        $event = $sale->event;
        $tz = $type->timezone();
        $start = $event->getStartDateTime(); // UTC
        $hostName = $event->appointmentHostName();

        // The hold a payment has to beat (ReleaseTickets counts it from the sale's creation).
        $payBy = null;
        if ($status === 'awaiting_payment' && (int) $event->expire_unpaid_tickets > 0) {
            $payBy = $sale->created_at->copy()->setTimezone('UTC')->addHours((int) $event->expire_unpaid_tickets);
        }

        $when = $this->label($start->copy(), $tz, $role);
        $what = $hostName ? $type->name.' '.__('messages.appointments_with_host', ['name' => $hostName]) : $type->name;
        $email = $this->maskEmail($sale->email);

        $sentences = [match ($status) {
            'pending_approval' => __('messages.appointments_api_summary_pending', ['type' => $what, 'when' => $when, 'schedule' => $role->name]),
            'awaiting_payment' => __('messages.appointments_api_summary_awaiting_payment', ['type' => $what, 'when' => $when]),
            default => __('messages.appointments_api_summary_confirmed', ['type' => $what, 'when' => $when]),
        }];

        if ($emailsSent) {
            $sentences[] = match ($status) {
                'awaiting_payment' => __('messages.appointments_api_summary_payment_emailed', [
                    'email' => $email,
                    'time' => $payBy ? $this->label($payBy, $tz, $role) : $when,
                ]),
                'pending_approval' => __('messages.appointments_api_summary_request_emailed', ['email' => $email]),
                default => __('messages.appointments_api_summary_confirmation_emailed', ['email' => $email]),
            };
        }

        return [
            'id' => UrlUtils::encodeId($sale->id),
            'status' => $status,
            'appointment_type_id' => UrlUtils::encodeId($type->id),
            'appointment_type' => $type->name,
            'host_name' => $hostName,
            'starts_at' => $start->format('Y-m-d\TH:i:s\Z'),
            'ends_at' => $start->copy()->addMinutes((int) $type->duration_minutes)->format('Y-m-d\TH:i:s\Z'),
            'label' => $when,
            'timezone' => $tz,
            'name' => $sale->name,
            'email' => $sale->email,
            'phone' => $sale->phone,
            'price' => (float) $sale->payment_amount,
            'currency' => (float) $sale->payment_amount > 0 ? $event->ticket_currency_code : null,
            'pay_by' => $payBy?->format('Y-m-d\TH:i:s\Z'),
            'emails_sent' => $emailsSent,
            // Absolute, and where the guest pays, changes or cancels. Not a payment page: a
            // payment session expires, and the manage page starts a fresh one.
            'manage_url' => $this->flow->manageUrl($sale),
            'summary' => implode(' ', $sentences),
        ];
    }

    /** The live booking this guest holds at exactly this time on this schedule (book()'s guard). */
    protected function existingBooking(Role $role, string $email, string $slotUtc): ?Sale
    {
        try {
            $start = Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $slotUtc, 'UTC')->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }

        return Sale::query()
            ->where('subdomain', $role->subdomain)
            ->where('email', $email)
            ->where('is_deleted', false)
            ->whereNotIn('status', ['cancelled', 'refunded', 'expired'])
            ->whereHas('event', fn ($q) => $q->whereNotNull('appointment_type_id')->where('starts_at', $start))
            ->with('event.appointmentType', 'event.appointmentHost')
            ->latest('id')
            ->first();
    }

    /** A booking's status in this API's words. */
    protected function statusOf(Sale $sale): string
    {
        $event = $sale->event;

        if ($sale->status !== 'paid' && AppointmentType::paysOnline($event->payment_method)) {
            return 'awaiting_payment';
        }

        return $event->isAwaitingCreatorApproval() ? 'pending_approval' : 'confirmed';
    }

    /** "j…@gmail.com": enough for a caller to recognise their address read back to them. */
    protected function maskEmail(string $email): string
    {
        $at = strrpos($email, '@');
        if ($at === false || $at === 0) {
            return $email;
        }

        return mb_substr($email, 0, 1).'…'.substr($email, $at);
    }

    /** Run $work in the schedule's language, and put the locale back whatever happens. */
    protected function inScheduleLocale(Role $role, callable $work): mixed
    {
        $original = app()->getLocale();

        try {
            if (is_valid_language_code($role->language_code)) {
                app()->setLocale($role->language_code);
            }

            return $work();
        } finally {
            app()->setLocale($original);
        }
    }
}
