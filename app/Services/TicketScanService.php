<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\SeatingSeat;
use App\Utils\MoneyUtils;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * What the door scanner answers for an ordinary ticket, and the check-in it records. Passes and
 * subscriptions go to PassRedemptionService instead; the lookups and who may scan stay in
 * TicketController::scanned().
 *
 * The checks run in a fixed order, red ones first, and no override reaches them: a ticket that is
 * not valid, outside its own check-in window or not paid is turned away whatever the door says.
 * After them come the three stops an operator may wave through with "Admit anyway": a ticket for
 * another event than the one the scanner is set to, for another night of the same series, or
 * behind on an installment plan. Each names the override that waves it through, and is checked in
 * only once the scanner sends that override back.
 *
 * Every answer carries `verdict`. The two "another event / date" stops also carry `error`, because
 * a scanner tab opened before `verdict` existed reads nothing else and would otherwise paint a
 * ticket it did not check in green.
 */
class TicketScanService
{
    public const OVERRIDES = ['other_event', 'other_date', 'overdue'];

    /**
     * @param  Event  $event  The ticket's own event.
     * @param  ?Event  $doorEvent  The event the scanner is set to, already checked as one the
     *                             operator may scan; null when the scanner names none.
     * @param  string[]  $overrides  The stops the operator chose to wave through.
     */
    public function scan(Sale $sale, Event $event, ?Event $doorEvent, array $overrides, ?Carbon $now = null): array
    {
        $now = ($now ? $now->copy() : now())->setTimezone('UTC');

        if (! $event->starts_at || ! Event::isOccurrenceDate($sale->event_date)) {
            return $this->refuse(__('messages.this_ticket_is_not_valid'), 'not_valid');
        }

        $window = $event->checkInWindow($sale->event_date);
        $ticketDate = $this->dateLabel($event, $sale->event_date);

        if ($now->lt($window['opens'])) {
            return $this->refuse(__('messages.this_ticket_cannot_be_checked_in_yet'), 'too_early', $event, $ticketDate);
        }

        if ($now->gt($window['end'])) {
            return $this->refuse(__('messages.this_ticket_check_in_period_has_ended'), 'ended', $event, $ticketDate);
        }

        // Only a `paid` sale gets in. Sale::scanRefusalMessage() is the allowlist, shared with the
        // pass scanner, and says why every other status is turned away.
        if ($error = $sale->scanRefusalMessage()) {
            return $this->refuse($error, 'refused');
        }

        $sale->loadMissing('saleTickets.ticket');

        if ($stop = $this->stop($sale, $event, $doorEvent, $overrides, $ticketDate, $now)) {
            return $stop;
        }

        // Which stops the operator waved through, for the audit log: an override sent for a stop this
        // scan would not have met waved nothing through.
        $applied = array_values(array_filter(self::OVERRIDES,
            fn ($override) => in_array($override, $overrides, true) && $this->wouldStop($override, $sale, $event, $doorEvent, $now)));

        try {
            // Under a row lock, and read back from the locked rows: two phones scanning one ticket
            // at the same moment must not both be told to admit.
            [$lines, $before] = DB::transaction(function () use ($sale, $now) {
                $lines = SaleTicket::with('ticket')
                    ->where('sale_id', $sale->id)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
                $before = [];

                foreach ($lines as $line) {
                    $before[$line->id] = $line->seats;
                    $seats = $line->seats ? json_decode($line->seats, true) : null;

                    if (is_array($seats) && in_array(null, $seats, false)) {
                        foreach ($seats as $slot => $value) {
                            if (! $value) {
                                $seats[$slot] = $now->timestamp;
                            }
                        }
                        $line->seats = json_encode($seats);
                        $line->save();
                    }

                    // Arrival state on the MAP, so the box office console and the printed sheet can
                    // answer "is C14 here yet". Stamped per LINE rather than per slot: a scan admits
                    // the whole line, and sale_tickets.seats carries no location.
                    SeatingSeat::where('sale_ticket_id', $line->id)
                        ->whereNull('checked_in_at')
                        ->update(['checked_in_at' => $now]);
                }

                return [$lines, $before];
            });
        } catch (QueryException $e) {
            report($e);

            return $this->refuse(__('messages.an_error_occurred'), 'error');
        }

        $data = $this->details($sale, $event, $ticketDate, $lines, $now, $before);

        if ($data['slots'] > 0 && $data['slots_used'] === $data['slots']) {
            $data['verdict'] = 'already_checked_in';
        } else {
            $data['verdict'] = $data['slots_used'] > 0 ? 'partly_checked_in' : 'checked_in';
            $data['headcount_label'] = $data['admitted_now'] > 0
                ? trans_choice('messages.scan_admit_people', $data['admitted_now'])
                : __('messages.scan_checked_in');

            if ($applied) {
                $data['overrides_applied'] = $applied;
                AuditService::log(AuditService::SALE_CHECKIN, auth()->id(), 'Sale', $sale->id,
                    null,
                    ['overrides' => $applied],
                    'admit_anyway:event_id:'.$event->id
                );
            }
        }

        unset($data['slots'], $data['slots_used']);

        WebhookService::dispatch('ticket.scanned', $sale);

        return $data;
    }

    /**
     * The first "Admit anyway" stop this scan meets and the operator has not waved through, or null.
     */
    private function stop(Sale $sale, Event $event, ?Event $doorEvent, array $overrides, string $ticketDate, Carbon $now): ?array
    {
        foreach (self::OVERRIDES as $override) {
            if (in_array($override, $overrides, true) || ! $this->wouldStop($override, $sale, $event, $doorEvent, $now)) {
                continue;
            }

            $data = $this->details($sale, $event, $ticketDate, $sale->saleTickets, $now);
            unset($data['slots'], $data['slots_used']);
            $data['verdict'] = $override;
            $data['override'] = $override;
            $data['headcount_label'] = trans_choice('messages.scan_ticket_admits', $data['headcount']);

            if ($override === 'other_event') {
                $data['error'] = __('messages.scan_other_event');
            } elseif ($override === 'other_date') {
                $data['error'] = __('messages.scan_other_date');
                $data['door_date'] = $this->dateLabel($event, $event->doorOccurrenceDates($now)[0]);
            } else {
                // Not `error`: the scanner paints an error red and hides who is standing there. The
                // sale is still `paid` - the seat is taken, the money mostly collected - so this is
                // amber, with the name and the balance, and letting them in is the organizer's call.
                $plan = $sale->installmentPlan;
                $data['payment_status'] = 'overdue';
                $data['amount_due'] = MoneyUtils::format($plan->amountRemaining(), $plan->currency);
                $data['amount_paid'] = MoneyUtils::format($plan->amount_paid, $plan->currency);
                $data['amount_total'] = MoneyUtils::format($plan->total_amount, $plan->currency);
            }

            return $data;
        }

        return null;
    }

    private function wouldStop(string $override, Sale $sale, Event $event, ?Event $doorEvent, Carbon $now): bool
    {
        return match ($override) {
            'other_event' => $doorEvent !== null && (int) $doorEvent->id !== (int) $event->id,
            // Only where the series has another night whose door is open now: a weekly event's
            // other nights already fail the ticket's own window above.
            'other_date' => $doorEvent !== null
                && (int) $doorEvent->id === (int) $event->id
                && ($dates = $event->doorOccurrenceDates($now))
                && ! in_array($sale->event_date, $dates, true),
            'overdue' => $sale->isInstallmentDelinquent(),
            default => false,
        };
    }

    /**
     * Who is at the door and what they hold, from each line's slot map as it stood before this scan
     * (`$before`, by line id, when this scan stamped them). `seats` keeps its old shape (slot =>
     * timestamp or null); `headcount` counts the people on the order, which add-ons are not.
     *
     * @param  array<int, ?string>|null  $before
     */
    private function details(Sale $sale, Event $event, string $ticketDate, Collection $lines, Carbon $now, ?array $before = null): array
    {
        $tickets = [];
        $headcount = 0;
        $previously = 0;
        $slots = 0;
        $slotsUsed = 0;
        $earliest = null;

        foreach ($lines as $line) {
            $raw = $before !== null ? ($before[$line->id] ?? null) : $line->seats;
            $seats = $raw ? json_decode($raw, true) : [];
            $seats = is_array($seats) ? $seats : [];
            $isAddon = (bool) $line->ticket?->is_addon;

            $tickets[] = [
                'type' => $line->ticket?->type,
                // The check-in slot map (1..n => timestamp or null), not a seat NUMBER. On an
                // allocated event the door needs the actual seat, so it rides alongside in the
                // order the slots run.
                'seats' => $seats,
                'seat_labels' => $line->seatLabels(),
                'is_addon' => $isAddon,
            ];

            foreach ($seats as $timestamp) {
                $slots++;
                $headcount += $isAddon ? 0 : 1;

                if ($timestamp) {
                    $slotsUsed++;
                    $previously += $isAddon ? 0 : 1;
                    $earliest = $earliest === null ? (int) $timestamp : min($earliest, (int) $timestamp);
                }
            }
        }

        $data = [
            'attendee' => $sale->name,
            'event' => $event->name,
            'date' => $ticketDate,
            'tickets' => $tickets,
            'headcount' => $headcount,
            'admitted_now' => $headcount - $previously,
            'previously_admitted' => $previously,
            'slots' => $slots,
            'slots_used' => $slotsUsed,
        ];

        if ($earliest !== null) {
            $data['already_checked_in_at'] = $event->doorClockLabel(Carbon::createFromTimestamp($earliest), $now);
        }

        if ($previously > 0) {
            $data['already_label'] = trans_choice('messages.scan_people_already_in', $previously);
        }

        return $data;
    }

    /**
     * An occurrence's date and time at the venue, in the operator's language and on the 12 or 24
     * hour clock everything else uses. English keeps the short form the scanner always printed.
     */
    private function dateLabel(Event $event, string $date): string
    {
        $locale = app()->getLocale();

        return $event->localStartsAt(true, $date, false, $event->scheduleTimezone(), $locale === 'en' ? null : $locale, $event->use24HourTime());
    }

    private function refuse(string $message, string $reason, ?Event $event = null, ?string $ticketDate = null): array
    {
        $data = ['verdict' => 'error', 'error' => $message, 'reason' => $reason];

        // Too early or over: the door should see which night the ticket IS for.
        if ($event) {
            $data['event'] = $event->name;
            $data['date'] = $ticketDate;
        }

        return $data;
    }
}
