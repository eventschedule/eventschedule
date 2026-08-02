<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Exceptions\SlotUnavailableException;
use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\Ticket;
use App\Models\User;
use App\Utils\AppointmentTimeUtils;
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
     * $withNextAvailable drives the "next available date" lookahead, which re-runs the whole slot
     * computation (and its busy-interval queries) over up to 15 further 31-day chunks. Callers that
     * only need membership - isSlotAvailable(), which runs inside the booking row lock - pass false.
     *
     * @return array{schedule_timezone:string, days:array<string,array<int,array{utc:string,date:string,label:string}>>, next_available_date:?string}
     */
    public function availableSlots(
        AppointmentType $type,
        string $fromDate,
        int $days = 31,
        ?Carbon $now = null,
        bool $withNextAvailable = true,
        ?int $excludeEventId = null,
        bool $ownerMode = false
    ): array {
        $days = max(1, min(31, $days));
        $tz = $type->timezone();

        $now = ($now ? $now->copy() : Carbon::now())->setTimezone($tz);
        // Owner mode drops the two GUEST-facing limits: min-notice and booking-window exist to stop
        // guests booking too close or too far out, and an owner moving tomorrow's appointment on a
        // 48-hour-notice type would otherwise be shown an empty calendar. Buffers, date overrides,
        // weekly windows and busy intervals all still apply, so double-booking stays impossible.
        //
        // It drops min-notice down to zero, NOT below: $earliest is the only past-slot floor
        // computeDays() has, so startOfDay() here offered this morning's elapsed slots. Moving a booking
        // into the past leaves it unmovable AND uncancellable, since both guards reject past bookings.
        $earliest = $ownerMode
            ? $now->copy()
            : $now->copy()->addHours((int) $type->min_notice_hours);
        // Owner mode lifts the guest booking window, but not to infinity: $lastDay also bounds the
        // next-available lookahead, which re-runs the whole slot computation over up to 15 further 31-day
        // chunks (two busy-interval queries each) and only runs when the visible range came back empty.
        // A year is far past any real appointment and keeps that walk to roughly what a guest's window
        // costs. The generous end of the guest range is honoured too, for a type that allows more.
        $lastDay = $ownerMode
            ? $now->copy()->startOfDay()->addDays(max(365, (int) $type->max_advance_days))
            : $now->copy()->startOfDay()->addDays((int) $type->max_advance_days);

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
            $result['days'] = $this->computeDays($type, $startDay, $endDay, $earliest, $excludeEventId);
        }

        if ($withNextAvailable && empty($result['days'])) {
            $result['next_available_date'] = $this->nextAvailableDate($type, $endDay->copy()->addDay(), $now, $earliest, $lastDay, $excludeEventId);
        }

        return $result;
    }

    /**
     * Whether a specific UTC slot instant is still bookable. Recomputes that date's slots and
     * requires exact membership - the caller re-checks this inside the booking lock.
     */
    public function isSlotAvailable(
        AppointmentType $type,
        string $utcIso,
        ?Carbon $now = null,
        ?int $excludeEventId = null,
        bool $ownerMode = false
    ): bool {
        $s = $this->parseUtc($utcIso);
        if (! $s) {
            return false;
        }

        $tz = $type->timezone();
        $date = $s->copy()->setTimezone($tz)->format('Y-m-d');
        // No lookahead: this runs inside book()'s schedule-wide row lock and only needs membership.
        $slots = $this->availableSlots($type, $date, 1, $now, false, $excludeEventId, $ownerMode);

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

        // A pending booking (pivot is_accepted null) would otherwise linger on the Requests tab
        // forever - the requests query filters only on the pivot. Close it out like a decline.
        $event->roles()->updateExistingPivot($event->creator_role_id, ['is_accepted' => false]);

        $expiredPaymentUrl = $sale->status === 'expired' && $sale->payment_method === 'payment_url';

        DB::afterCommit(function () use ($event, $sale, $expiredPaymentUrl) {
            // Push the cancellation to any connected calendars (no-op if never synced).
            $event->dispatchCalendarSync('delete');

            // payment_url holds expire after 24h with no payment callback - the guest may have
            // paid on the merchant page, so tell them the booking lapsed. Stripe abandonment
            // stays silent (consistent with ticket checkout).
            if ($expiredPaymentUrl) {
                (new EmailService)->sendAppointmentGuestCancellation($sale);
            }
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

    /** Minutes a booking must rest between moves. Bounds the per-move mail + calendar fan-out. */
    public const RESCHEDULE_COOLDOWN_MINUTES = 3;

    /**
     * Move an existing booking to a new slot, in place.
     *
     * This deliberately MOVES the Event and Sale rather than cancel-and-rebook: that keeps the sale id,
     * the secret link, the payment (`status`/`transaction_reference` untouched, no Stripe call needed),
     * the analytics row and the guest's calendar entry - and avoids double-counting `EVENT_CREATE` and
     * churning two Ticket rows.
     *
     * Returns the OLD `starts_at` as a UTC string so the caller can render "moved from X". It has to be
     * a scalar: `SerializesModels` re-fetches models when a queued mail is built, which would show the
     * new time on both sides (same reason AppointmentBookedNotification takes `$wasPaid` as a scalar).
     *
     * @param  string  $initiator  'guest' | 'owner' - drives the pending reset and who gets mailed.
     * @param  ?string  $fromSlotUtc  The slot the page was rendered with. Supplying it makes the write
     *                                idempotent; a replay with a stale value is rejected instead of
     *                                re-firing the calendar sync, the webhook and the mail.
     *
     * @throws BusinessException
     */
    public function reschedule(
        Sale $sale,
        string $slotUtc,
        string $initiator = 'guest',
        ?string $fromSlotUtc = null,
        ?string $guestTimezone = null,
        bool $ownerMode = false
    ): string {
        $start = $this->parseUtc($slotUtc);
        if (! $start) {
            throw new BusinessException(__('messages.appointments_slot_taken'));
        }

        $event = $sale->event;
        $type = $event?->appointmentType;
        $role = $event?->creatorRole;
        if (! $event || ! $type || ! $role) {
            throw new BusinessException(__('messages.appointments_slot_taken'));
        }

        $newStartsAt = $start->format('Y-m-d H:i:s');

        return DB::transaction(function () use ($sale, $event, $type, $role, $start, $newStartsAt, $initiator, $fromSlotUtc, $guestTimezone, $ownerMode) {
            // Same schedule-wide lock as book(), because overlap conflicts span types and no unique
            // index can express them. Then the Sale: a reschedule mutates it, and every transition that
            // races us locks the Sale (guest cancel, the Stripe webhook, ReleaseTickets, the reminder
            // claim). Role -> Sale is a safe order; nothing in the codebase takes Sale -> Role.
            Role::whereKey($role->id)->lockForUpdate()->first();
            $lockedSale = Sale::whereKey($sale->id)->lockForUpdate()->first();
            $lockedEvent = Event::whereKey($event->id)->first();

            if (! $lockedSale || ! $lockedEvent
                || $lockedSale->is_deleted
                || $lockedEvent->is_cancelled
                || in_array($lockedSale->status, ['cancelled', 'refunded', 'expired'], true)) {
                // The :schedule replacement is not optional - this string reads "Contact :schedule if you
                // need to change the time", and an unmatched placeholder renders verbatim to the guest.
                throw new BusinessException(__('messages.appointments_reschedule_unavailable', [
                    'schedule' => $role->name,
                ]));
            }

            $oldStartsAt = (string) $lockedEvent->starts_at;

            // Conflict detection: the caller tells us what the page was showing, so two people moving the
            // same booking to different slots resolve to a single winner rather than last-write-wins.
            //
            // NOT idempotency - the no-op return below already provided that, and this check running
            // first used to defeat it. A duplicate delivery (guest taps once on a flaky connection, the
            // request commits, the response is lost, they tap again) arrives with a from_slot that is now
            // stale but a target that already IS the live start. Rejecting that told the guest their
            // successful move had failed, and because the picker's recovery never refreshes
            // currentSlotUtc, every retry re-sent the same stale from_slot: a dead end until reload.
            if ($fromSlotUtc !== null) {
                $fromParsed = $this->parseUtc($fromSlotUtc);
                if (! $fromParsed || $fromParsed->format('Y-m-d H:i:s') !== $oldStartsAt) {
                    if ($oldStartsAt === $newStartsAt) {
                        return $oldStartsAt; // already where they asked for; report success
                    }

                    throw new SlotUnavailableException(__('messages.appointments_slot_taken'));
                }
            }

            // Nothing to do - and nothing to announce.
            if ($oldStartsAt === $newStartsAt) {
                return $oldStartsAt;
            }

            // Per-booking cooldown. The per-IP throttle cannot bound this: the capability is the secret,
            // not the address. Each accepted move costs an owner email plus INLINE Google/Microsoft/CalDAV
            // calls (dispatchSync), so a guest rotating IPs could otherwise walk a booking across the
            // calendar one notification at a time.
            //
            // Keyed on rescheduled_at, NOT updated_at. updated_at is set when the booking is created, so
            // it refused the most common move of all - a guest fixing a slot seconds after booking it -
            // with the actively false "this booking was just moved". It is also written by jobs that have
            // nothing to do with rescheduling (the Translate command has no is_private filter, and
            // inbound calendar sync calls save()), which turned a legitimate move into a random failure.
            if ($lockedEvent->rescheduled_at
                && $lockedEvent->rescheduled_at->diffInMinutes(now()) < self::RESCHEDULE_COOLDOWN_MINUTES) {
                throw new BusinessException(__('messages.appointments_reschedule_too_soon'));
            }

            if ($lockedEvent->getStartDateTime()->isPast()) {
                throw new BusinessException(__('messages.appointments_reschedule_unavailable', [
                    'schedule' => $role->name,
                ]));
            }

            // A floor on the NEW start. Belt-and-braces with the $earliest clamp in availableSlots():
            // A/B'd, and either one alone closes the hole, but a booking parked in the past can no
            // longer be moved OR cancelled by anyone, so an unrecoverable state is worth two guards.
            // Do not remove the clamp on the strength of this check, or vice versa.
            if ($start->isPast()) {
                throw new SlotUnavailableException(__('messages.appointments_slot_taken'));
            }

            // Re-check inside the lock, excluding this booking's own event so it does not block itself.
            // book()'s duplicate guard is deliberately NOT reused: it matches on subdomain + email + the
            // exact target instant without excluding the current sale, so it would always false-positive.
            if (! $this->isSlotAvailable($type, $start->format('Y-m-d\TH:i:s\Z'), null, $lockedEvent->id, $ownerMode)) {
                throw new SlotUnavailableException(__('messages.appointments_slot_taken'));
            }

            $wasAccepted = $this->isPivotAccepted($lockedEvent);
            // Branch on the LIVE pivot, not $type->requires_approval: the type is mutable, and an owner
            // who switched approval off leaves older bookings pivot-null. And only a GUEST move needs
            // re-approval - an owner would otherwise have to approve their own action.
            $backToPending = $initiator === 'guest' && $type->requires_approval && $wasAccepted;

            // duration is NOT optional. The slot grid is validated against the type's CURRENT duration
            // while busyIntervals() blocks using the event's stored one, so leaving a stale 30 minutes on
            // an event that now occupies a 60-minute slot lets a second guest book the uncovered half.
            // Plain save() (not saveQuietly): the Event::saving cascade re-keys ticket.sold and rewrites
            // sales.event_date off the new date, and saveQuietly would skip it.
            $lockedEvent->forceFill([
                'starts_at' => $newStartsAt,
                'duration' => $type->duration_minutes / 60.0,
                'ical_sequence' => ((int) $lockedEvent->ical_sequence) + 1,
                // Only this method ever writes it, which is the whole point: it stays null until a real
                // move happens, so a first move is never refused.
                'rescheduled_at' => now(),
            ])->save();

            // AFTER the event write, and as a targeted update rather than a read-modify-write.
            //
            // The Event::saving cascade rewrote sales.event_date through its OWN freshly-loaded Sale
            // instances, so every Sale object we are holding is now stale on that column. A plain
            // $sale->save() would NOT actually write the old value back (dirty tracking excludes an
            // untouched attribute - verified), but an update() or forceFill() that named event_date
            // would, and the in-memory value is wrong either way. Hence: targeted update here, and a
            // refresh below so callers building mail or guest URLs off this Sale see the new date.
            $saleChanges = ['reminder_sent_at' => null];
            if ($guestTimezone && AppointmentTimeUtils::resolveTimezone($guestTimezone)) {
                $saleChanges['guest_timezone'] = $guestTimezone;
            }
            if ($backToPending) {
                // confirm() is a one-shot latch on confirmed_at; without clearing it, re-approval would
                // silently send no confirmation and never sync the calendar.
                $saleChanges['confirmed_at'] = null;
            }
            Sale::whereKey($lockedSale->id)->update($saleChanges);

            if ($backToPending) {
                $lockedEvent->roles()->updateExistingPivot($role->id, ['is_accepted' => null]);
            }

            DB::afterCommit(function () use ($lockedEvent) {
                // dispatchSync runs these jobs INLINE and they re-throw. An afterCommit throw propagates
                // out of commit() after the row is already written, so the guest would see an error for
                // a move that happened and retry it.
                try {
                    $lockedEvent->dispatchCalendarSync('update');
                } catch (\Throwable $e) {
                    report($e);
                }
            });

            // The caller's instances are stale on event_date (rewritten by the cascade) and on
            // starts_at/duration/ical_sequence. Refresh both so mail and guest URLs built from them
            // afterwards describe the booking as it now is.
            $sale->refresh();
            $event->refresh();

            return $oldStartsAt;
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
            // 'update', not 'create'. The confirmed_at latch above used to make 'create' safe because it
            // could only ever fire once - but reschedule() clears confirmed_at when a guest move sends an
            // approval-required booking back to pending, so a second approval re-arms this. createEvent()
            // then makes a NEW remote event and overwrites the CalendarSync row, orphaning the entry it
            // replaced: unreachable by a later delete, and blocking that time on the owner's real calendar
            // forever. Every provider's updateEvent() falls back to createEvent() when no external id is
            // recorded (Google, Microsoft and CalDAV all do), so a first confirmation is unaffected.
            $event->dispatchCalendarSync('update');
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
    protected function computeDays(AppointmentType $type, Carbon $startDay, Carbon $endDay, Carbon $earliest, ?int $excludeEventId = null): array
    {
        $tz = $type->timezone();
        $busy = $this->busyIntervals($type->role, $startDay, $endDay, $tz, $excludeEventId);

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
    protected function nextAvailableDate(AppointmentType $type, Carbon $fromDay, Carbon $now, Carbon $earliest, Carbon $lastDay, ?int $excludeEventId = null): ?string
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

            $days = $this->computeDays($type, $cursor->copy(), $chunkEnd, $earliest, $excludeEventId);
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
    protected function busyIntervals(Role $role, Carbon $startDay, Carbon $endDay, string $tz, ?int $excludeEventId = null): array
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
            // A booking being rescheduled must not block its own move, nor hide the neighbouring slots
            // that overlap the time it currently holds. ALWAYS server-derived from the resolved sale:
            // this set includes inbound-synced private events, so a request-supplied id would turn the
            // slots endpoint into an oracle for the owner's personal calendar.
            ->when($excludeEventId, fn ($q) => $q->where('events.id', '!=', $excludeEventId))
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
