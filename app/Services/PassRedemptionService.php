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

        // Coverage resolves within the pass's own schedule (cross-tenant safe).
        $schedule = $sale->event?->creatorRole;
        $tz = $schedule?->timezone ?? config('app.timezone');

        $todayCarbon = $now->copy()->setTimezone($tz)->startOfDay();
        $today = $todayCarbon->format('Y-m-d');
        $nowUtc = $now->copy()->setTimezone('UTC');

        $usages = $passSaleTicket->pass_usages ?? [];

        $data->pass_name = $ticket->type;
        $data->pass_usage_type = $ticket->pass_usage_type;
        $data->pass_max_uses = $ticket->pass_max_uses ?: null;
        $data->pass_usage_count = count($usages);
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

        // Is the scanning event actually happening today?
        if (! $scanningEvent->starts_at || ! $scanningEvent->matchesDate($todayCarbon->copy())) {
            $next = $this->nextOccurrenceDate($scanningEvent, $today);
            $data->pass_status = 'no_event_today';
            $data->date = $next ? $scanningEvent->localStartsAt(true, $next) : null;
            $data->next_event = $data->date;

            return $data;
        }

        $data->date = $scanningEvent->localStartsAt(true, $today);

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

            // A committed visit is one pass_usages entry per event-day. It may already
            // exist because the holder reserved this occurrence in advance (kind
            // 'booking'); scanning in then upgrades that entry to a redemption rather
            // than consuming a second use. An entry already redeemed today is a
            // genuine repeat scan. Legacy entries (no kind) are treated as redemptions.
            $existingIndex = collect($usages)->search(fn ($u) => (int) ($u['event_id'] ?? 0) === (int) $scanningEvent->id
                && ($u['date'] ?? null) === $today);

            if ($existingIndex !== false && ($usages[$existingIndex]['kind'] ?? 'redemption') === 'redemption') {
                $data->pass_status = 'already_today';
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
                ];
            } else {
                // Redeem the seat booked in advance for today (no new use consumed).
                $usages[$existingIndex]['kind'] = 'redemption';
                $usages[$existingIndex]['at'] = $nowUtc->timestamp;
            }

            $locked->pass_usages = $usages;
            $locked->save();

            $data->pass_status = 'valid';
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
        $startsAt = strlen($event->starts_at) === 10
            ? Carbon::createFromFormat('Y-m-d', $event->starts_at, 'UTC')->startOfDay()
            : Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC');
        $timeOfDay = $startsAt->copy()->setTimezone($tz)->format('H:i:s');
        $startUtc = Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$timeOfDay, $tz)->setTimezone('UTC');
        $duration = $event->duration > 0 ? $event->duration : 2;
        $endUtc = $startUtc->copy()->addHours($duration);
        $earliest = $startUtc->copy()->subHours(24);

        return [$startUtc, $endUtc, $earliest];
    }

    /**
     * Next occurrence date (Y-m-d) on/after $fromDate for $event, scanning
     * forward up to a year. Null if none.
     */
    protected function nextOccurrenceDate(Event $event, string $fromDate): ?string
    {
        if (! $event->starts_at) {
            return null;
        }

        $date = Carbon::createFromFormat('Y-m-d', $fromDate)->startOfDay();
        for ($i = 0; $i < 366; $i++) {
            if ($event->matchesDate($date->copy())) {
                return $date->format('Y-m-d');
            }
            $date->addDay();
        }

        return null;
    }
}
