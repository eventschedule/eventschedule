<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Validates and records redemption of a pass / subscription at a given event.
 *
 * A pass (`Ticket.is_pass`) may cover one recurring event (legacy season pass)
 * or multiple distinct events (a cross-event subscription). It is redeemed at
 * most once per event per day; the configurable limit governs how many distinct
 * event-days it allows. Outcomes are returned as a neutral `pass_status` the
 * scanner styles - only payment problems are returned as a red `error`.
 */
class PassRedemptionService
{
    /**
     * @param  Sale  $sale  The pass sale (resolved from the scanned QR secret).
     * @param  Event  $scanningEvent  The event the operator is physically scanning at (Y).
     * @param  Carbon  $now  Current time.
     */
    public function redeem(Sale $sale, Event $scanningEvent, Carbon $now): \stdClass
    {
        $sale->loadMissing(['saleTickets.ticket', 'event.creatorRole']);

        $passSaleTicket = $sale->saleTickets->first(fn ($st) => $st->ticket?->is_pass);
        $ticket = $passSaleTicket?->ticket;

        $data = new \stdClass;
        $data->is_pass = true;
        $data->attendee = $sale->name;
        $data->event = $scanningEvent->name;

        // Payment problems are the only hard errors (red); a valid pass must
        // never read as fraud.
        if ($sale->status === 'unpaid') {
            $data->error = __('messages.this_ticket_is_not_paid');

            return $data;
        } elseif ($sale->status === 'cancelled') {
            $data->error = __('messages.this_ticket_is_cancelled');

            return $data;
        } elseif ($sale->status === 'refunded') {
            $data->error = __('messages.this_ticket_is_refunded');

            return $data;
        }

        if (! $ticket || ! $passSaleTicket) {
            $data->error = __('messages.this_ticket_is_not_valid');

            return $data;
        }

        // Coverage resolves within the pass's own schedule (cross-tenant safe). creator_role_id is
        // nullable, so fall back to the first schedule listing the home event - Ticket::covers()
        // denies everything when handed no schedule.
        $schedule = $sale->event?->creatorRole ?? $sale->event?->roles->first();

        // Dates resolve at the venue the operator is standing in, which is the event being
        // scanned - not the pass's home schedule. `covers()` allows a cross-schedule pass (a
        // curator's pass over an imported venue event), and every other surface keys pass dates
        // by the event's own venue: PassBookingService, Event::passReservedSeats(), and the
        // check-in dashboard. Using the home schedule here would hide the holder's advance
        // booking and re-consume it as a walk-up. Identical for the default `this_event` scope.
        $tz = $scanningEvent->scheduleTimezone();

        $todayCarbon = $now->copy()->setTimezone($tz)->startOfDay();
        $today = $todayCarbon->format('Y-m-d');
        $nowUtc = $now->copy()->setTimezone('UTC');

        $usages = $passSaleTicket->pass_usages ?? [];

        $data->pass_name = $ticket->type;
        $data->pass_usage_type = $ticket->pass_usage_type;
        $data->pass_max_uses = $ticket->pass_max_uses ?: null;
        $data->pass_usage_count = count($usages);
        // How many people this pass admits per event (1 = holder only). Surfaced
        // so the scanner can show "Admitted 1 of 2" and prompt to scan the guest.
        $data->admits_per_event = $ticket->admitsPerEvent();
        if ($passSaleTicket->pass_expires_at) {
            $data->valid_until = $passSaleTicket->pass_expires_at->copy()->setTimezone($tz)->format('M j, Y');
        }

        // Does this pass cover the event being scanned?
        if (! $ticket->covers($scanningEvent, $schedule)) {
            $data->pass_status = 'not_covered';

            return $data;
        }

        // Has the pass expired? (both are Carbon instants - compare directly)
        if ($passSaleTicket->pass_expires_at
            && $now->greaterThan($passSaleTicket->pass_expires_at)) {
            $data->pass_status = 'expired';

            return $data;
        }

        // Is the scanning event actually happening today? Resolve the occurrence in the
        // schedule's timezone - $today already is, and matchesDate() would otherwise use the
        // scanning operator's, which flips the day for a late-evening one-time event.
        if (! $scanningEvent->starts_at || ! $scanningEvent->matchesDate($todayCarbon->copy(), $tz)) {
            $next = $this->nextOccurrenceDate($scanningEvent, $today, $tz);
            $data->pass_status = 'no_event_today';
            // Show the venue's local time - the operator is standing at the door, and their
            // own profile timezone is irrelevant to when the doors open.
            $data->date = $next ? $scanningEvent->localStartsAt(true, $next, false, $tz) : null;
            $data->next_event = $data->date;

            return $data;
        }

        $data->date = $scanningEvent->localStartsAt(true, $today, false, $tz);

        // Build today's occurrence window for the scanning event.
        [$startUtc, $endUtc, $earliest] = $this->occurrenceWindow($scanningEvent, $today, $tz);

        if ($nowUtc->lt($earliest)) {
            $data->pass_status = 'too_early';
            $data->check_in_opens = $startUtc->copy()->setTimezone($tz)->format('g:i A');

            return $data;
        }

        if ($nowUtc->gt($endUtc)) {
            $data->pass_status = 'event_over';

            return $data;
        }

        // The pass_usages read-modify-write must be atomic against concurrent
        // book()/cancel()/scan, which also mutate the array. Do it under a row lock
        // and re-read the latest array (mirrors PassBookingService::book/cancel).
        $data = DB::transaction(function () use ($passSaleTicket, $scanningEvent, $ticket, $today, $nowUtc, $tz, $data) {
            $locked = SaleTicket::lockForUpdate()->find($passSaleTicket->id);
            $usages = $locked->pass_usages ?? [];
            $admitsPerEvent = $ticket->admitsPerEvent();

            // A committed visit is one pass_usages entry per event-day. It may already
            // exist because the holder reserved this occurrence in advance (kind
            // 'booking'); scanning in then upgrades that entry to a redemption rather
            // than consuming a second use. An entry already redeemed today is a
            // genuine repeat scan. Legacy entries (no kind) are treated as redemptions.
            // A 'forfeited' entry is a dead late-cancellation whose seat may have been
            // resold - it never revives; showing up anyway is a brand-new walk-up
            // visit that consumes another use (subject to the limits below).
            $existingIndex = collect($usages)->search(fn ($u) => (int) ($u['event_id'] ?? 0) === (int) $scanningEvent->id
                && ($u['date'] ?? null) === $today
                && SaleTicket::usageKind($u) !== 'forfeited');

            if ($existingIndex !== false && ($usages[$existingIndex]['kind'] ?? 'redemption') === 'redemption') {
                // Already redeemed today. A multi-admit pass (e.g. holder + guest)
                // may still have admissions left, in which case this scan admits
                // the next person on the SAME visit - it does not consume another
                // use. Only when every admission is spent is it a repeat scan.
                $admitsUsed = max(1, (int) ($usages[$existingIndex]['admits'] ?? 1));

                // Gate the extra admit on the occurrence still having a free seat. A booking reserves
                // only ONE seat up front (computePassReservedSeats counts max(1, admits)), but admits
                // can climb to admitsPerEvent at the door - so an unchecked bump lets a walk-in guest
                // retroactively oversell a full room. null = unlimited house, so fail open there.
                $seatsRemaining = $scanningEvent->occurrenceSeatsRemaining($today);
                $roomForGuest = $seatsRemaining === null || $seatsRemaining > 0;

                if ($admitsUsed < $admitsPerEvent && $roomForGuest) {
                    $usages[$existingIndex]['admits'] = $admitsUsed + 1;
                    $locked->pass_usages = $usages;
                    $locked->save();

                    $data->pass_status = 'valid';
                    $data->admits_used = $admitsUsed + 1;
                    $data->pass_usage_count = count($usages);
                    if ($ticket->pass_usage_type === 'total' && $ticket->pass_max_uses) {
                        $data->pass_remaining = max(0, $ticket->pass_max_uses - count($usages));
                    }

                    return $data;
                }

                $data->pass_status = 'already_today';
                $data->admits_used = $admitsUsed;
                // Distinguish "all admissions spent" from "room is full", so the operator knows why an
                // extra guest was not admitted.
                $data->seats_full = $admitsUsed < $admitsPerEvent && ! $roomForGuest;
                $data->checked_in_at = Carbon::createFromTimestamp($usages[$existingIndex]['at'] ?? $nowUtc->timestamp, $tz)->format('g:i A');

                return $data;
            }

            if ($existingIndex === false) {
                // Brand new walk-up visit: enforce the visit limits, then record it.
                if ($ticket->pass_usage_type === 'total'
                    && $ticket->pass_max_uses
                    && count($usages) >= $ticket->pass_max_uses) {
                    $data->pass_status = 'limit_reached';

                    return $data;
                }

                if ($ticket->pass_usage_type === 'per_event'
                    && collect($usages)->contains(fn ($u) => (int) ($u['event_id'] ?? 0) === (int) $scanningEvent->id)) {
                    $data->pass_status = 'limit_reached';

                    return $data;
                }

                $usages[] = [
                    'event_id' => (int) $scanningEvent->id,
                    'date' => $today,
                    'at' => $nowUtc->timestamp,
                    'kind' => 'redemption',
                    'admits' => 1,
                ];
            } else {
                // Redeem the seat booked in advance for today (no new use consumed).
                $usages[$existingIndex]['kind'] = 'redemption';
                $usages[$existingIndex]['at'] = $nowUtc->timestamp;
                $usages[$existingIndex]['admits'] = max(1, (int) ($usages[$existingIndex]['admits'] ?? 1));
            }

            $locked->pass_usages = $usages;
            $locked->save();

            $data->pass_status = 'valid';
            $data->admits_used = 1;
            $data->pass_usage_count = count($usages);
            if ($ticket->pass_usage_type === 'total' && $ticket->pass_max_uses) {
                $data->pass_remaining = max(0, $ticket->pass_max_uses - count($usages));
            }

            return $data;
        });

        // Dispatch outside the transaction so the lock isn't held during delivery.
        if ($data->pass_status === 'valid') {
            WebhookService::dispatch('ticket.scanned', $sale, null, [
                'scanned_event_id' => UrlUtils::encodeId($scanningEvent->id),
                'scanned_event_date' => $today,
            ]);
        }

        return $data;
    }

    /**
     * Build [startUtc, endUtc, earliest] for an event's occurrence on $date
     * (schedule-TZ calendar date). Check-in opens 24h before start and closes
     * at end (start + duration, min 2h).
     *
     * @return array{0: Carbon, 1: Carbon, 2: Carbon}
     */
    protected function occurrenceWindow(Event $event, string $date, string $tz): array
    {
        $startUtc = $event->occurrenceStartUtc($date, $tz);
        $duration = $event->duration > 0 ? $event->duration : 2;
        $endUtc = $startUtc->copy()->addMinutes(Event::durationHoursToMinutes($duration));
        $earliest = $startUtc->copy()->subHours(24);

        return [$startUtc, $endUtc, $earliest];
    }

    /**
     * Next occurrence date (Y-m-d) on/after $fromDate for $event, scanning
     * forward up to a year. Null if none. Dates resolve in $timezone (the schedule's).
     */
    protected function nextOccurrenceDate(Event $event, string $fromDate, ?string $timezone = null): ?string
    {
        if (! $event->starts_at) {
            return null;
        }

        $date = Carbon::createFromFormat('Y-m-d', $fromDate)->startOfDay();
        for ($i = 0; $i < 366; $i++) {
            if ($event->matchesDate($date->copy(), $timezone)) {
                return $date->format('Y-m-d');
            }
            $date->addDay();
        }

        return null;
    }
}
