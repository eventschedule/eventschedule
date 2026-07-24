<?php

namespace App\Http\Controllers;

use App\Models\AppointmentType;
use App\Models\Role;
use App\Models\Sale;
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
    public function store(Request $request, $subdomain)
    {
        $role = $this->gate($request, $subdomain);
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

        $type->is_active = ! $type->is_active;
        $type->save();

        return $this->back($role, __('messages.appointments_type_saved'));
    }

    /** Owner cancels a booking from the Bookings sub-view. */
    public function bookingCancel(Request $request, $subdomain, $saleHash)
    {
        $role = $this->gate($request, $subdomain);
        $sale = Sale::findOrFail(UrlUtils::decodeId($saleHash));

        if ($sale->subdomain !== $role->subdomain || ! $sale->event?->appointment_type_id) {
            abort(404);
        }

        if (! in_array($sale->status, ['cancelled', 'refunded', 'expired'])) {
            $wasPaid = $sale->status === 'paid';
            $sale->status = 'cancelled'; // Sale::booted hook cancels the event + frees the slot
            $sale->save();
            if ($wasPaid) {
                \App\Models\AnalyticsEventsDaily::decrementSale($sale->event_id, (float) $sale->payment_amount, $sale->created_at->toDateString());
            }
            app(\App\Services\EmailService::class)->sendAppointmentGuestCancellation($sale);
            if ($wasPaid) {
                app(\App\Services\EmailService::class)->sendAppointmentOwnerCancellation($sale);
            }
        }

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']).'?view=bookings')
            ->with('message', __('messages.appointments_cancelled_message'));
    }

    /** Resolve the schedule, require an editor, and enforce the Pro gate on hosted. */
    protected function gate(Request $request, $subdomain): Role
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $request->user() || ! $request->user()->isEditor($subdomain)) {
            abort(403);
        }

        if (config('app.hosted') && ! $role->isPro()) {
            abort(redirect()->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan'])
                ->with('error', __('messages.upgrade_required')));
        }

        return $role;
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
        $type->slot_interval_minutes = ! empty($data['slot_interval_minutes']) ? (int) $data['slot_interval_minutes'] : null;
        $type->buffer_before_minutes = (int) ($data['buffer_before_minutes'] ?? 0);
        $type->buffer_after_minutes = (int) ($data['buffer_after_minutes'] ?? 0);
        $type->min_notice_hours = (int) ($data['min_notice_hours'] ?? 0);
        $type->max_advance_days = (int) ($data['max_advance_days'] ?? 60);
        $type->weekly_windows = $data['weekly_windows'];
        $type->date_overrides = $data['date_overrides'] ?? null;
        $type->location_type = $data['location_type'];
        $type->location_address = $data['location_address'] ?? null;
        $type->location_url = $data['location_url'] ?? null;
        $type->location_phone = $data['location_phone'] ?? null;
        $type->price = (float) ($data['price'] ?? 0);
        $type->currency_code = ((float) ($data['price'] ?? 0) > 0) ? strtoupper($data['currency_code']) : null;
        $type->payment_method = ((float) ($data['price'] ?? 0) > 0) ? ($data['payment_method'] ?? 'cash') : null;
        $type->requires_approval = request()->boolean('requires_approval');
        $type->ask_phone = request()->boolean('ask_phone');
        $type->require_phone = request()->boolean('require_phone');
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
