<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentService
{
    /**
     * Minutes a busy event with no/zero duration blocks. Matches FeedController::buildVevent's
     * `duration > 0 ? duration : 2` - conservative over-blocking beats double-booking.
     */
    public const DEFAULT_BUSY_MINUTES = 120;

    /**
     * Available slots for a type over [fromDate, fromDate+days), grouped by schedule-local date.
     *
     * Every slot start is a UTC instant; the client renders it in the visitor's timezone. Windows
     * are wall-clock in the schedule's timezone and anchored per-date so DST is handled correctly.
     *
     * @return array{schedule_timezone:string, days:array<string,array<int,array{utc:string,date:string,label:string}>>, next_available_date:?string}
     */
    public function availableSlots(AppointmentType $type, string $fromDate, int $days = 31, ?Carbon $now = null): array
    {
        $days = max(1, min(31, $days));
        $tz = $type->timezone();

        $now = ($now ? $now->copy() : Carbon::now())->setTimezone($tz);
        $earliest = $now->copy()->addHours((int) $type->min_notice_hours); // absolute min-notice instant
        $lastDay = $now->copy()->startOfDay()->addDays((int) $type->max_advance_days);

        $startDay = $this->parseDay($fromDate, $tz);
        if ($startDay->lt($now->copy()->startOfDay())) {
            $startDay = $now->copy()->startOfDay();
        }
        $endDay = $startDay->copy()->addDays($days - 1);
        if ($endDay->gt($lastDay)) {
            $endDay = $lastDay->copy();
        }

        $result = ['schedule_timezone' => $tz, 'days' => [], 'next_available_date' => null];

        if ($endDay->gte($startDay)) {
            $result['days'] = $this->computeDays($type, $startDay, $endDay, $earliest);
        }

        if (empty($result['days'])) {
            $result['next_available_date'] = $this->nextAvailableDate($type, $endDay->copy()->addDay(), $now, $earliest, $lastDay);
        }

        return $result;
    }

    /**
     * Whether a specific UTC slot instant is still bookable. Recomputes that date's slots and
     * requires exact membership - the caller re-checks this inside the booking lock.
     */
    public function isSlotAvailable(AppointmentType $type, string $utcIso, ?Carbon $now = null): bool
    {
        $s = $this->parseUtc($utcIso);
        if (! $s) {
            return false;
        }

        $tz = $type->timezone();
        $date = $s->copy()->setTimezone($tz)->format('Y-m-d');
        $slots = $this->availableSlots($type, $date, 1, $now);

        foreach ($slots['days'][$date] ?? [] as $slot) {
            if ($slot['utc'] === $s->format('Y-m-d\TH:i:s\Z')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Release a slot when its sale reaches a terminal status. Soft-cancels the backing event,
     * bumps the iCal sequence, and pushes the calendar delete + (Phase 6) guest mail after commit.
     * Re-entrant safe: no-ops when the event is missing, not an appointment, or already cancelled.
     */
    public function cancelFromSale(Sale $sale): void
    {
        $event = $sale->event;
        if (! $event || ! $event->appointment_type_id || $event->is_cancelled) {
            return;
        }

        $event->forceFill([
            'is_cancelled' => true,
            'cancelled_at' => now(),
            'ical_sequence' => ((int) $event->ical_sequence) + 1,
        ])->saveQuietly();

        DB::afterCommit(function () use ($event) {
            // Push the cancellation to any connected calendars (no-op if never synced).
            $event->dispatchCalendarSync('delete');
        });
    }

    /**
     * Create a booking (Event + inventory Ticket + Sale + SaleTicket) for a slot, in one
     * transaction, under a schedule-wide row lock. Re-checks slot availability inside the lock;
     * throws BusinessException('appointments_slot_taken'|'appointments_already_booked') on a race
     * or duplicate. The returned sale's status is set by payment type (free=paid, otherwise unpaid);
     * the caller fans out to confirm()/payment. Callers enforce the daily cap + honeypot first.
     */
    public function book(AppointmentType $type, Role $role, array $data, ?User $user = null): Sale
    {
        $slotUtc = $data['slot'];
        $tz = $type->timezone();
        $start = Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $slotUtc, 'UTC');

        return DB::transaction(function () use ($type, $role, $data, $user, $slotUtc, $tz, $start) {
            // Serialize every booking on this schedule: overlap conflicts span types, so no unique
            // index can express them (PassBookingService locks similarly).
            Role::whereKey($role->id)->lockForUpdate()->first();

            if (! $this->isSlotAvailable($type, $slotUtc)) {
                throw new BusinessException(__('messages.appointments_slot_taken'));
            }

            // Duplicate guard: same guest email already holds this exact slot on this schedule.
            $duplicate = Sale::query()
                ->where('subdomain', $role->subdomain)
                ->where('email', $data['email'])
                ->whereNotIn('status', ['cancelled', 'refunded', 'expired'])
                ->whereHas('event', function ($q) use ($start) {
                    $q->whereNotNull('appointment_type_id')
                        ->where('starts_at', $start->format('Y-m-d H:i:s'));
                })
                ->exists();
            if ($duplicate) {
                throw new BusinessException(__('messages.appointments_already_booked'));
            }

            $localDate = $start->copy()->setTimezone($tz)->format('Y-m-d');

            $event = new Event;
            $event->name = $type->name.' - '.$data['name'];
            $event->starts_at = $start->format('Y-m-d H:i:s');
            $event->duration = $type->duration_minutes / 60.0; // float HOURS; buffers not included
            $event->timezone = $tz;
            $event->is_private = true;      // hides from the public schedule, iCal feed, RSS
            $event->tickets_enabled = false; // keeps the normal event page/checkout inert if leaked
            $event->feedback_enabled = false; // suppress post-event surveys for bookings
            $event->creator_role_id = $role->id;
            $event->user_id = $role->user_id;
            $event->appointment_type_id = $type->id;
            // Random-suffixed slug: the page is unlisted-by-link and shows the guest name/notes.
            $event->slug = Str::slug($type->name).'-'.strtolower(Str::random(10));
            if (! empty($data['notes'])) {
                $event->description = __('messages.appointments_notes_from', ['name' => $data['name']]).': '.trim($data['notes']);
            }
            if ($type->location_type === 'online' && $type->location_url) {
                $event->event_url = $type->location_url;
            }
            if (! $type->isFree()) {
                $event->ticket_currency_code = $type->currency_code;
                $event->payment_method = $type->payment_method;
                $event->expire_unpaid_tickets = $type->expireHours();
            }
            $event->save();

            $event->roles()->attach($role->id, [
                'is_accepted' => $type->requires_approval ? null : true,
            ]);

            UsageTrackingService::track(UsageTrackingService::EVENT_CREATE, $role->id);

            $ticket = new Ticket;
            $ticket->event_id = $event->id;
            $ticket->type = $type->name;
            $ticket->quantity = 1;
            $ticket->price = $type->isFree() ? 0 : $type->price;
            $ticket->save();

            $sale = new Sale;
            $sale->event_id = $event->id;
            $sale->subdomain = $role->subdomain;
            $sale->user_id = $user?->id;
            $sale->name = $data['name'];
            $sale->email = $data['email'];
            $sale->phone = ! empty($data['phone']) ? strip_tags(trim($data['phone'])) : null;
            $sale->event_date = $localDate;
            $sale->secret = strtolower(Str::random(32));
            $sale->guest_timezone = $data['guest_timezone'] ?? null;
            $sale->payment_method = $type->isFree() ? 'cash' : $type->payment_method;
            $sale->payment_amount = $type->isFree() ? 0 : $type->price;
            // Free bookings are paid immediately; cash is unpaid-with-balance-due; stripe/payment_url
            // stay unpaid until payment clears.
            $sale->status = $type->isFree() ? 'paid' : 'unpaid';
            $this->applyCustomValues($sale, $type, $data['custom_values'] ?? []);
            $sale->save();

            // The SaleTicket::created hook increments ticket.sold, holding the seat.
            $saleTicket = new SaleTicket;
            $saleTicket->sale_id = $sale->id;
            $saleTicket->ticket_id = $ticket->id;
            $saleTicket->quantity = 1;
            $saleTicket->seats = json_encode([1 => null]);
            $saleTicket->save();

            return $sale;
        });
    }

    /**
     * Transition a booking to CONFIRMED once (guarded by sales.confirmed_at): push the calendar
     * create and send the guest confirmation, both after commit. No-ops while pending approval
     * (pivot is_accepted still null) or when the event is cancelled.
     */
    public function confirm(Sale $sale): void
    {
        $event = $sale->event;
        if (! $event || ! $event->appointment_type_id || $event->is_cancelled) {
            return;
        }
        if (! $this->isPivotAccepted($event)) {
            return; // awaiting owner approval
        }

        // Atomic claim: only the first caller to flip confirmed_at proceeds. Guards against a
        // concurrent double-confirm (owner double-clicks Accept, or the Stripe webhook races
        // accept()) which would otherwise create a duplicate/orphaned calendar event + double email.
        $claimed = Sale::whereKey($sale->id)->whereNull('confirmed_at')->update(['confirmed_at' => now()]);
        if (! $claimed) {
            return;
        }
        $sale->confirmed_at = now(); // keep the in-memory model consistent for the closure below

        DB::afterCommit(function () use ($event, $sale) {
            $event->dispatchCalendarSync('create');
            (new EmailService)->sendAppointmentConfirmationEmails($sale);
        });
    }

    /** Whether the creator schedule has accepted the booking (pivot is_accepted === true). */
    protected function isPivotAccepted(Event $event): bool
    {
        $pivot = $event->roles()
            ->where('roles.id', $event->creator_role_id)
            ->first()?->pivot;

        return $pivot && (int) $pivot->is_accepted === 1;
    }

    /** Map guest answers onto the sale's custom_value columns (same index convention as tickets). */
    protected function applyCustomValues(Sale $sale, AppointmentType $type, array $answers): void
    {
        $fields = $type->custom_fields ?? [];
        $fallbackIndex = 1;
        foreach ($fields as $fieldKey => $fieldConfig) {
            $index = $fieldConfig['index'] ?? $fallbackIndex;
            $fallbackIndex++;
            if ($index >= 1 && $index <= 10) {
                $value = $answers[$fieldKey] ?? null;
                if (is_array($value)) {
                    $value = implode(', ', array_map('trim', $value));
                }
                if ($value !== null) {
                    $value = trim(strip_tags($value));
                }
                $sale->{"custom_value{$index}"} = $value;
            }
        }
    }

    /**
     * Slot map for [$startDay, $endDay] (schedule-local days), keyed by date. Pure computation,
     * never recurses into next-available lookup.
     *
     * @return array<string,array<int,array{utc:string,date:string,label:string}>>
     */
    protected function computeDays(AppointmentType $type, Carbon $startDay, Carbon $endDay, Carbon $earliest): array
    {
        $tz = $type->timezone();
        $busy = $this->busyIntervals($type->role, $startDay, $endDay, $tz);

        $duration = (int) $type->duration_minutes;
        $step = max(1, $type->stepMinutes());
        $bufBefore = (int) $type->buffer_before_minutes;
        $bufAfter = (int) $type->buffer_after_minutes;

        $days = [];

        for ($day = $startDay->copy(); $day->lte($endDay); $day->addDay()) {
            $d = $day->format('Y-m-d');
            $slots = [];

            foreach ($this->windowsForDate($type, $day) as $window) {
                if (empty($window['start']) || empty($window['end'])) {
                    continue;
                }

                $wStart = Carbon::createFromFormat('Y-m-d H:i', $d.' '.$window['start'], $tz)->setTimezone('UTC');
                $wEnd = Carbon::createFromFormat('Y-m-d H:i', $d.' '.$window['end'], $tz)->setTimezone('UTC');
                if ($wEnd->lte($wStart)) {
                    continue; // inverted or DST-swallowed window
                }

                for ($s = $wStart->copy(); $s->copy()->addMinutes($duration)->lte($wEnd); $s->addMinutes($step)) {
                    if ($s->lt($earliest)) {
                        continue;
                    }

                    $candStart = $s->copy()->subMinutes($bufBefore);
                    $candEnd = $s->copy()->addMinutes($duration + $bufAfter);
                    if ($this->overlapsAny($busy, $candStart, $candEnd)) {
                        continue;
                    }

                    $slots[] = [
                        'utc' => $s->format('Y-m-d\TH:i:s\Z'),
                        'date' => $d,
                        'label' => $s->copy()->setTimezone($tz)->format('H:i'),
                    ];
                }
            }

            if (! empty($slots)) {
                $days[$d] = $slots;
            }
        }

        return $days;
    }

    /**
     * First schedule-local date (from $fromDay, capped at max_advance) that has at least one open
     * slot, or null. Scans forward in 31-day chunks so far-off availability stays bounded.
     */
    protected function nextAvailableDate(AppointmentType $type, Carbon $fromDay, Carbon $now, Carbon $earliest, Carbon $lastDay): ?string
    {
        $cursor = $fromDay->copy()->startOfDay();
        if ($cursor->lt($now->copy()->startOfDay())) {
            $cursor = $now->copy()->startOfDay();
        }

        $guard = 0;
        while ($cursor->lte($lastDay) && $guard < 15) {
            $guard++;
            $chunkEnd = $cursor->copy()->addDays(30);
            if ($chunkEnd->gt($lastDay)) {
                $chunkEnd = $lastDay->copy();
            }

            $days = $this->computeDays($type, $cursor->copy(), $chunkEnd, $earliest);
            if (! empty($days)) {
                return array_key_first($days);
            }

            $cursor->addDays(31);
        }

        return null;
    }

    /**
     * Busy intervals (as [startUtc, endUtc] pairs) held by the schedule's events across the window.
     * Busy = pivot is_accepted TRUE or NULL (pending holds time; declined does not) and not
     * cancelled. Inbound-synced external events are ordinary events, so they are covered here too.
     *
     * @return array<int,array{0:Carbon,1:Carbon}>
     */
    protected function busyIntervals(Role $role, Carbon $startDay, Carbon $endDay, string $tz): array
    {
        $padStart = $startDay->copy()->setTimezone('UTC')->subDay();
        $padEnd = $endDay->copy()->endOfDay()->setTimezone('UTC')->addDay();

        $base = Event::query()
            ->whereHas('roles', function ($q) use ($role) {
                $q->where('roles.id', $role->id)
                    ->where(function ($q2) {
                        $q2->whereNull('event_role.is_accepted')
                            ->orWhere('event_role.is_accepted', true);
                    });
            })
            ->where('is_cancelled', false);

        $intervals = [];

        // Concrete (non-recurring) events overlapping the padded UTC window.
        $concrete = (clone $base)
            ->whereNull('days_of_week')
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', $padEnd->format('Y-m-d H:i:s'))
            ->whereRaw('DATE_ADD(starts_at, INTERVAL GREATEST(COALESCE(duration, 0), 2) HOUR) >= ?', [$padStart->format('Y-m-d H:i:s')])
            ->with('appointmentType')
            ->get();

        foreach ($concrete as $e) {
            if (strlen((string) $e->starts_at) === 10) {
                continue; // date-only all-day placeholder does not block a time slot in v1
            }
            $s = Carbon::createFromFormat('Y-m-d H:i:s', $e->starts_at, 'UTC');
            $len = $e->duration > 0 ? $e->durationInMinutes() : self::DEFAULT_BUSY_MINUTES;
            [$b0, $b1] = $this->eventBuffers($e);
            $intervals[] = [$s->copy()->subMinutes($b0), $s->copy()->addMinutes($len + $b1)];
        }

        // Recurring events: expand per day across the window (matchesDate + occurrenceStartUtc).
        $recurring = (clone $base)->whereNotNull('days_of_week')->with('appointmentType')->get();
        if ($recurring->isNotEmpty()) {
            for ($day = $startDay->copy(); $day->lte($endDay); $day->addDay()) {
                $d = $day->format('Y-m-d');
                foreach ($recurring as $e) {
                    if (! $e->matchesDate($day, $tz)) {
                        continue;
                    }
                    $s = $e->occurrenceStartUtc($d, $tz);
                    $len = $e->duration > 0 ? $e->durationInMinutes() : self::DEFAULT_BUSY_MINUTES;
                    [$b0, $b1] = $this->eventBuffers($e);
                    $intervals[] = [$s->copy()->subMinutes($b0), $s->copy()->addMinutes($len + $b1)];
                }
            }
        }

        return $intervals;
    }

    /** Buffers to pad a busy event by: an appointment blocks by its own type's buffers, else none. */
    protected function eventBuffers(Event $e): array
    {
        if ($e->appointment_type_id && $e->appointmentType) {
            return [(int) $e->appointmentType->buffer_before_minutes, (int) $e->appointmentType->buffer_after_minutes];
        }

        return [0, 0];
    }

    /** Windows for a schedule-local date: a date override (present key) replaces the weekly windows. */
    protected function windowsForDate(AppointmentType $type, Carbon $day): array
    {
        $overrides = $type->date_overrides ?? [];
        $d = $day->format('Y-m-d');
        if (array_key_exists($d, $overrides)) {
            return is_array($overrides[$d]) ? $overrides[$d] : [];
        }

        $weekly = $type->weekly_windows ?? [];

        return $weekly[(string) $day->dayOfWeek] ?? [];
    }

    /** Half-open overlap so back-to-back intervals do not collide. */
    protected function overlapsAny(array $busy, Carbon $start, Carbon $end): bool
    {
        foreach ($busy as [$bStart, $bEnd]) {
            if ($start->lt($bEnd) && $bStart->lt($end)) {
                return true;
            }
        }

        return false;
    }

    protected function parseDay(string $date, string $tz): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $date, $tz)->startOfDay();
        } catch (\Throwable $e) {
            return Carbon::now($tz)->startOfDay();
        }
    }

    protected function parseUtc(string $utcIso): ?Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $utcIso, 'UTC');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
