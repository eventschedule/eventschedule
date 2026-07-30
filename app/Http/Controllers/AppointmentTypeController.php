<?php

namespace App\Http\Controllers;

use App\Models\AppointmentType;
use App\Models\Role;
use App\Models\Sale;
use App\Traits\ReschedulesAppointments;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Owner-side CRUD for appointment types (the Appointments admin tab). Pro-gated on hosted.
 * Guest-facing booking lives in AppointmentController.
 */
class AppointmentTypeController extends Controller
{
    use ReschedulesAppointments;

    public function store(Request $request, $subdomain)
    {
        $role = $this->gate($request, $subdomain);
        $this->planLimit($role);
        $data = $this->validated($request);

        $type = new AppointmentType;
        $type->role_id = $role->id;
        $this->fill($type, $data);
        $type->slug = $this->uniqueSlug($role, $data['name']);
        $type->save();

        return $this->back($role, __('messages.appointments_type_saved'));
    }

    public function update(Request $request, $subdomain, $hash)
    {
        $role = $this->gate($request, $subdomain);
        $type = $this->resolveType($role, $hash);
        $data = $this->validated($request);

        $this->fill($type, $data);
        $type->save();

        return $this->back($role, __('messages.appointments_type_saved'));
    }

    public function destroy(Request $request, $subdomain, $hash)
    {
        $role = $this->gate($request, $subdomain);
        $type = $this->resolveType($role, $hash);

        $type->is_deleted = true;
        $type->is_active = false;
        $type->save();

        return $this->back($role, __('messages.appointments_type_deleted'));
    }

    public function toggle(Request $request, $subdomain, $hash)
    {
        $role = $this->gate($request, $subdomain);
        $type = $this->resolveType($role, $hash);

        // The list posts an explicit value from a toggle switch. Honouring it makes the action
        // idempotent, so a double submit or a re-post cannot flip the type back again. Falls back to
        // inverting for callers that post an empty body.
        $type->is_active = $request->has('is_active')
            ? $request->boolean('is_active')
            : ! $type->is_active;
        $type->save();

        return $this->back($role, __('messages.appointments_type_saved'));
    }

    /**
     * Copy a type so a near-identical one does not have to be rebuilt by hand. Created inactive and
     * opened in the editor, so the owner renames it before it can be booked - the same flow as
     * cloning a newsletter.
     */
    public function duplicate(Request $request, $subdomain, $hash)
    {
        $role = $this->gate($request, $subdomain);
        // Duplicating is the second creation path, so it takes the same allowance check.
        $this->planLimit($role);
        $type = $this->resolveType($role, $hash);

        $copy = $type->replicate();
        $copy->name = $type->name.' ('.__('messages.copy').')';
        $copy->slug = $this->uniqueSlug($role, $copy->name);
        $copy->is_active = false;
        $copy->save();

        return redirect(route('role.view_admin', [
            'subdomain' => $role->subdomain,
            'tab' => 'appointments',
            'edit' => $copy->hashedId(),
        ]))->with('message', __('messages.appointments_type_saved'));
    }

    /** GET .../bookings/{saleHash}/reschedule - the picker, in owner reschedule mode. */
    public function showBookingReschedule(Request $request, $subdomain, $saleHash)
    {
        $role = $this->gate($request, $subdomain);
        $sale = $this->resolveOwnedBooking($role, $saleHash);
        $event = $sale->event;
        $type = $event->appointmentType;

        if ($blocked = $this->rescheduleBlockedReason($event, $sale, $role)) {
            return $this->backToBookings($role, $blocked, true);
        }

        $service = app(\App\Services\AppointmentService::class);
        $today = \Carbon\Carbon::now($type->timezone())->format('Y-m-d');
        // ownerMode: min-notice and the booking window are guest-facing limits. An owner moving
        // tomorrow's appointment on a 48-hour-notice type would otherwise see an empty calendar.
        $initial = $service->availableSlots($type, $today, 31, null, true, $event->id, true);
        if (empty($initial['days']) && ! empty($initial['next_available_date'])) {
            $initial = $service->availableSlots($type, $initial['next_available_date'], 31, null, true, $event->id, true);
        }

        $params = ['subdomain' => $role->subdomain, 'saleHash' => $saleHash];

        return view('appointments.book-type', [
            'role' => $role,
            'type' => $type,
            'initialSlots' => $initial,
            'mode' => 'reschedule',
            'ownerMode' => true,
            'sale' => $sale,
            'event' => $event,
            'rescheduleUrl' => route('appointments.booking_reschedule.store', $params, false),
            'rescheduleSlotsUrl' => route('appointments.booking_reschedule_slots', $params, false),
            'backUrl' => route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments', 'view' => 'bookings'], false),
        ]);
    }

    /** GET .../bookings/{saleHash}/reschedule/slots - owner-mode slot JSON. */
    public function bookingRescheduleSlots(Request $request, $subdomain, $saleHash)
    {
        $role = $this->gate($request, $subdomain, json: true);
        $sale = $this->resolveOwnedBooking($role, $saleHash);
        $event = $sale->event;
        $type = $event->appointmentType;

        if ($blocked = $this->rescheduleBlockedReason($event, $sale, $role)) {
            return response()->json(['error' => $blocked], 422);
        }

        // See AppointmentController::rescheduleSlots - an array `from` is a TypeError, not a 422.
        $request->validate(['from' => 'nullable|date_format:Y-m-d', 'days' => 'nullable|integer']);

        $from = $request->input('from') ?: \Carbon\Carbon::now($type->timezone())->format('Y-m-d');
        $days = max(1, min(31, (int) $request->input('days', 31)));

        return response()->json(
            app(\App\Services\AppointmentService::class)
                ->availableSlots($type, $from, $days, null, true, $event->id, true)
        );
    }

    /** POST .../bookings/{saleHash}/reschedule - owner moves the booking. Always JSON. */
    public function bookingReschedule(Request $request, $subdomain, $saleHash)
    {
        $role = $this->gate($request, $subdomain, json: true);
        $sale = $this->resolveOwnedBooking($role, $saleHash);
        $event = $sale->event;

        if ($blocked = $this->rescheduleBlockedReason($event, $sale, $role, checkCooldown: false)) {
            return response()->json(['error' => $blocked], 422);
        }

        $validated = $request->validate([
            'slot' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/'],
            'from_slot' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/'],
            // Optional note to the guest, and the ability to move without emailing them at all -
            // the same choice the event editor already offers when a time changes.
            'note' => 'nullable|string|max:280',
            'notify' => 'nullable|boolean',
        ]);

        return $this->applyReschedule(
            $sale,
            $event,
            $role,
            $validated['slot'],
            'owner',
            $validated['from_slot'] ?? null,
            null,
            true,
            $request->boolean('notify', true),
            $validated['note'] ?? null
        );
    }

    /**
     * Resolve a booking the acting schedule actually owns.
     *
     * Scoped on creator_role_id, NOT sales.subdomain: that column is a booking-time snapshot and is
     * never rewritten when a schedule is renamed, so a freed subdomain claimed by another schedule
     * would expose the original owner's bookings here.
     */
    protected function resolveOwnedBooking(Role $role, string $saleHash): Sale
    {
        $sale = Sale::with('event.appointmentType')->findOrFail(UrlUtils::decodeId($saleHash));

        if ($sale->is_deleted
            || ! $sale->event?->appointment_type_id
            || (int) $sale->event->creator_role_id !== (int) $role->id) {
            abort(404);
        }

        return $sale;
    }

    protected function backToBookings(Role $role, string $message, bool $isError = false)
    {
        $url = route('role.view_admin', [
            'subdomain' => $role->subdomain, 'tab' => 'appointments', 'view' => 'bookings',
        ]);

        return redirect($url)->with($isError ? 'error' : 'message', $message);
    }

    /** Owner cancels a booking from the Bookings sub-view. */
    public function bookingCancel(Request $request, $subdomain, $saleHash)
    {
        $role = $this->gate($request, $subdomain);
        // Same ownership scoping as the reschedule path - sales.subdomain drifts on rename.
        $sale = $this->resolveOwnedBooking($role, $saleHash);

        // A finished appointment cannot be cancelled (same guard as the guest manage page) -
        // the guest would otherwise get a cancellation email for something that already happened.
        if ($sale->event->getStartDateTime()->isPast()) {
            return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']).'?view=bookings')
                ->with('error', __('messages.appointments_cannot_cancel_past'));
        }

        if (! in_array($sale->status, ['cancelled', 'refunded', 'expired'])) {
            $wasPaid = $sale->status === 'paid';
            $wasPaidMoney = $wasPaid && (float) $sale->payment_amount > 0;
            $sale->status = 'cancelled'; // Sale::booted hook cancels the event + frees the slot
            $sale->save();
            if ($wasPaid) {
                \App\Models\AnalyticsEventsDaily::decrementSale($sale->event_id, (float) $sale->payment_amount, $sale->created_at->toDateString());
            }
            app(\App\Services\EmailService::class)->sendAppointmentGuestCancellation($sale);
            if ($wasPaidMoney) {
                // Refund reminder: only for real money, with the paid state captured pre-cancel.
                app(\App\Services\EmailService::class)->sendAppointmentOwnerCancellation($sale, true);
            }
        }

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']).'?view=bookings')
            ->with('message', __('messages.appointments_cancelled_message'));
    }

    /**
     * Resolve the schedule and require an editor.
     *
     * Appointments are available on every plan; the free plan is limited to one appointment type
     * (enforced by planLimit() on the two creation paths), not locked out of the feature.
     */
    protected function gate(Request $request, $subdomain, bool $json = false): Role
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $request->user() || ! $request->user()->isEditor($subdomain)) {
            abort(403);
        }

        return $role;
    }

    /**
     * Refuse a new appointment type once the schedule's plan allowance is used up.
     *
     * $json is for endpoints the picker calls with fetch(). A plan refusal that aborts with a
     * REDIRECT is thrown verbatim no matter what the client asked for, so fetch() followed it, got
     * HTML, failed to parse, and reported "session expired" instead of the real reason. Page
     * endpoints keep the redirect, which is correct for a normal navigation.
     */
    protected function planLimit(Role $role, bool $json = false): void
    {
        if ($role->canCreateAppointmentType()) {
            return;
        }

        $message = __('messages.appointment_type_limit_reached', ['limit' => $role->appointmentTypeLimit()]);

        abort($json
            ? response()->json(['error' => $message], 403)
            : redirect()->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments'])
                ->with('error', $message));
    }

    protected function resolveType(Role $role, string $hash): AppointmentType
    {
        $type = AppointmentType::where('role_id', $role->id)
            ->where('is_deleted', false)
            ->findOrFail(UrlUtils::decodeId($hash));

        return $type;
    }

    protected function validated(Request $request): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'duration_minutes' => 'required|integer|min:5|max:1440',
            'slot_interval_minutes' => 'nullable|integer|min:5|max:1440',
            'buffer_before_minutes' => 'nullable|integer|min:0|max:1440',
            'buffer_after_minutes' => 'nullable|integer|min:0|max:1440',
            'min_notice_hours' => 'nullable|integer|min:0|max:8760',
            'max_advance_days' => 'nullable|integer|min:1|max:730',
            'location_type' => 'required|in:in_person,online,phone',
            'location_address' => 'nullable|string|max:500',
            'location_url' => 'nullable|url:http,https|max:500',
            'location_phone' => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'currency_code' => 'nullable|string|size:3',
            'payment_method' => 'nullable|in:stripe,payment_url,cash',
            'weekly_windows' => 'required',
            'date_overrides' => 'nullable',
        ];

        $validated = $request->validate($rules);

        // A paid type needs a currency (the Stripe webhook derives the unit multiplier from it).
        if ((float) ($validated['price'] ?? 0) > 0 && empty($validated['currency_code'])) {
            throw ValidationException::withMessages(['currency_code' => __('messages.appointments_currency_required')]);
        }

        $validated['weekly_windows'] = $this->parseWindows($request->input('weekly_windows'), 'weekly_windows');
        $validated['date_overrides'] = $request->filled('date_overrides')
            ? $this->parseOverrides($request->input('date_overrides'))
            : null;

        return $validated;
    }

    /** Decode + validate a weekly-windows structure: keys "0".."6" -> array of {start,end} ranges. */
    protected function parseWindows($input, string $field): array
    {
        $windows = is_string($input) ? json_decode($input, true) : $input;
        if (! is_array($windows)) {
            throw ValidationException::withMessages([$field => __('messages.error')]);
        }

        $clean = [];
        foreach (['0', '1', '2', '3', '4', '5', '6'] as $day) {
            $ranges = $windows[$day] ?? [];
            $clean[$day] = $this->validateRanges(is_array($ranges) ? $ranges : [], $field);
        }

        return $clean;
    }

    protected function parseOverrides($input): array
    {
        $overrides = is_string($input) ? json_decode($input, true) : $input;
        if (! is_array($overrides)) {
            return [];
        }

        $clean = [];
        foreach ($overrides as $date => $ranges) {
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
                continue;
            }
            // An empty array is a valid "closed" override.
            $clean[$date] = $this->validateRanges(is_array($ranges) ? $ranges : [], 'date_overrides');
        }

        return $clean;
    }

    /** At most 4 non-overlapping HH:MM ranges with start < end, sorted. */
    protected function validateRanges(array $ranges, string $field): array
    {
        if (count($ranges) > 4) {
            throw ValidationException::withMessages([$field => __('messages.appointments_too_many_ranges')]);
        }

        $clean = [];
        foreach ($ranges as $range) {
            $start = $range['start'] ?? null;
            $end = $range['end'] ?? null;
            if (! $this->isHm($start) || ! $this->isHm($end)) {
                throw ValidationException::withMessages([$field => __('messages.error')]);
            }
            if ($start >= $end) {
                throw ValidationException::withMessages([$field => __('messages.appointments_invalid_range')]);
            }
            $clean[] = ['start' => $start, 'end' => $end];
        }

        usort($clean, fn ($a, $b) => strcmp($a['start'], $b['start']));

        // Reject overlaps.
        for ($i = 1; $i < count($clean); $i++) {
            if ($clean[$i]['start'] < $clean[$i - 1]['end']) {
                throw ValidationException::withMessages([$field => __('messages.appointments_overlapping_ranges')]);
            }
        }

        return $clean;
    }

    protected function isHm($value): bool
    {
        return is_string($value) && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) === 1;
    }

    protected function fill(AppointmentType $type, array $data): void
    {
        $type->name = $data['name'];
        $type->description = $data['description'] ?? null;
        $type->duration_minutes = (int) $data['duration_minutes'];
        // Only overwrite when the request actually sends it, so a value set by a backup restore or the API
        // is not wiped by a form that omits the field. (The editor does render both this and
        // date_overrides now - an earlier comment here claimed it did not.)
        if (request()->has('slot_interval_minutes')) {
            $type->slot_interval_minutes = ! empty($data['slot_interval_minutes']) ? (int) $data['slot_interval_minutes'] : null;
        }
        $type->buffer_before_minutes = (int) ($data['buffer_before_minutes'] ?? 0);
        $type->buffer_after_minutes = (int) ($data['buffer_after_minutes'] ?? 0);
        $type->min_notice_hours = (int) ($data['min_notice_hours'] ?? 0);
        $type->max_advance_days = (int) ($data['max_advance_days'] ?? 60);
        $type->weekly_windows = $data['weekly_windows'];
        // Same has-guard as slot_interval_minutes, for the same reason.
        if (request()->has('date_overrides')) {
            $type->date_overrides = $data['date_overrides'] ?? null;
        }
        $type->location_type = $data['location_type'];
        $type->location_address = $data['location_address'] ?? null;
        $type->location_url = $data['location_url'] ?? null;
        $type->location_phone = $data['location_phone'] ?? null;
        $type->price = (float) ($data['price'] ?? 0);
        $type->currency_code = ((float) ($data['price'] ?? 0) > 0) ? strtoupper($data['currency_code']) : null;
        $type->payment_method = ((float) ($data['price'] ?? 0) > 0) ? ($data['payment_method'] ?? 'cash') : null;
        $type->requires_approval = request()->boolean('requires_approval');
        $type->ask_phone = request()->boolean('ask_phone');
        // Requiring a field that is never asked for is meaningless - the booking validation only
        // reads require_phone inside `if ($type->ask_phone)`. Normalise rather than storing the
        // contradiction.
        $type->require_phone = $type->ask_phone && request()->boolean('require_phone');
        $type->is_active = request()->boolean('is_active');
    }

    protected function uniqueSlug(Role $role, string $name): string
    {
        $base = Str::slug($name) ?: 'appointment';
        $slug = $base;
        $i = 2;
        while (AppointmentType::where('role_id', $role->id)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    protected function back(Role $role, string $message)
    {
        return redirect()->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments'])
            ->with('message', $message);
    }
}
