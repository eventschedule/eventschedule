<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\SeatingSeat;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Pro only. Until the free plan could sell, this needed no gate: a free schedule produced no
        // paid sales, so the dashboard had nothing to show. Now that it can sell, the gate has to be
        // explicit or check-in becomes a free feature by accident.
        $events = Event::with(['creatorRole', 'roles'])
            ->managedBy($user)
            ->whereNull('appointment_type_id') // appointment bookings are not check-in events
            ->where(fn ($q) => $q->whereHas('tickets')->orWhere('rsvp_enabled', true))
            ->orderBy('starts_at', 'desc')
            ->get()
            ->filter(fn (Event $event) => $event->isPro())
            ->values();

        // Pre-select event with sales for today, else most recent. sales.event_date holds the
        // venue's calendar date, so compare against that rather than the app timezone's date;
        // listed events may sit in different timezones, hence the exact per-venue date set.
        $salesDatesByEvent = Sale::whereIn('event_id', $events->pluck('id'))
            ->whereIn('event_date', Event::scheduleTodayDates($events))
            ->where('status', 'paid')
            ->where('is_deleted', false)
            ->get(['event_id', 'event_date'])
            ->groupBy('event_id')
            ->map(fn ($rows) => $rows->pluck('event_date')->all());

        $selectedEventId = $events->first(
            fn (Event $e) => in_array($e->scheduleToday(), $salesDatesByEvent[$e->id] ?? [], true)
        )?->id ?? $events->first()?->id;

        $eventsData = $events->map(function ($event) {
            return [
                'id' => UrlUtils::encodeId($event->id),
                'name' => $event->name,
                'starts_at' => $event->starts_at ? $event->getShortDateRangeDisplay('D, M j, Y') : null,
                'image_url' => $event->getImageUrl(),
            ];
        });

        return view('ticket.checkin', [
            'events' => $eventsData,
            'selectedEventId' => $selectedEventId ? UrlUtils::encodeId($selectedEventId) : null,
        ]);
    }

    /**
     * Find an attendee at the door.
     *
     * The check-in screen had no search of ANY kind - not by name, not by seat, not by order - only
     * a rear-view feed of the last ten arrivals. So "is C14 here yet", and "this person says they
     * booked but the scanner will not read their phone", both had no answer on this screen at all.
     *
     * Read-only on purpose: this tells staff what they are looking at. Admitting somebody still
     * goes through the scan, which is the one path that writes.
     */
    public function search(Request $request, $eventId)
    {
        $user = auth()->user();
        $event = Event::with(['tickets', 'creatorRole'])->find(UrlUtils::decodeId($eventId));

        if (! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        if (! $user || ! $user->canViewEventData($event)) {
            abort(403);
        }

        // Explicit, like index() and stats(). Attendee lookup is part of check-in, and check-in is
        // Pro - without this line it becomes a free feature by accident.
        if (! $event->isPro()) {
            abort(403);
        }

        $query = trim((string) $request->input('q'));

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        // Through Carbon like stats() does. input('date') is whatever was sent: an array or a
        // malformed string does not throw - it binds and matches nothing - so the door searched a
        // night that does not exist and got a confident empty list back. Falling back to tonight is
        // the same answer stats() gives, and an empty result here reads as "not on the list".
        $date = $event->scheduleToday();

        try {
            $raw = $request->input('date');
            $date = is_string($raw) && $raw !== ''
                ? Carbon::createFromFormat('Y-m-d', $raw)->format('Y-m-d')
                : $date;
        } catch (\Exception $e) {
            // keep today
        }

        // The backslash first, or escaping the wildcards re-breaks a term that contains one.
        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $query).'%';

        // Seat first: "C14" is what somebody at the door is holding. Row and seat are matched
        // separately so "C14", "C 14" and "row C seat 14" all land on the same seat.
        $seatMatch = preg_match('/^\s*([a-z]+)\s*-?\s*(\d+)\s*$/i', $query, $m)
            || preg_match('/row\s+([a-z0-9]+)\s+seat\s+(\d+)/i', $query, $m);

        $seats = SeatingSeat::with(['sale', 'saleTicket.ticket', 'section'])
            ->whereHas('eventSeatingMap', fn ($q) => $q->where('event_id', $event->id)->where('event_date', $date))
            ->where('status', 'sold')
            ->where(function ($q) use ($seatMatch, $m, $like) {
                if ($seatMatch) {
                    $q->where(fn ($sq) => $sq->where('row_label', $m[1])->where('seat_label', $m[2]));
                }

                $q->orWhereHas('sale', fn ($sq) => $sq->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->orderBy('row_position')->orderBy('position')
            ->limit(30)
            ->get();

        return response()->json([
            'results' => $seats->map(fn (SeatingSeat $seat) => [
                'seat' => $seat->fullLabel(),
                'name' => $seat->sale?->name,
                'ticket_type' => $seat->saleTicket?->ticket?->type,
                'status' => $seat->sale?->status,
                'arrived' => $seat->checked_in_at !== null,
                'arrived_at' => $seat->checked_in_at?->getTimestamp(),
            ])->values(),
        ]);
    }

    public function stats($eventId)
    {
        $user = auth()->user();
        $event = Event::with(['tickets', 'roles', 'creatorRole'])->find(UrlUtils::decodeId($eventId));

        if (! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        if (! $user->canViewEventData($event)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Pro only, same as index(). See the note there on why this is now explicit.
        if (! $event->isPro()) {
            return response()->json(['error' => __('messages.upgrade_required')], 403);
        }

        // Default to the venue's calendar date, which is what sales.event_date and the
        // pass_usages entries are keyed by. Using the app timezone's date instead reports
        // zero check-ins and zero reserved seats all evening for any venue west of UTC.
        $today = $event->scheduleToday();
        $requestedDate = request()->query('date', $today);
        try {
            $requestedDate = \Carbon\Carbon::createFromFormat('Y-m-d', $requestedDate)->format('Y-m-d');
        } catch (\Exception $e) {
            $requestedDate = $today;
        }

        // Get available dates for this event
        $availableDates = Sale::where('event_id', $event->id)
            ->where('status', 'paid')
            ->where('is_deleted', false)
            ->select('event_date')
            ->distinct()
            ->orderBy('event_date', 'desc')
            ->pluck('event_date')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->values();

        // Get sold counts from Ticket.sold JSON
        $tickets = [];
        $totalSold = 0;
        $totalCheckedIn = 0;
        $totalAdmitted = 0;

        $activeTickets = $event->tickets->where('is_deleted', false);

        // Get sold counts per ticket
        $ticketSoldCounts = [];
        foreach ($activeTickets as $ticket) {
            $sold = $ticket->sold ? json_decode($ticket->sold, true) : [];
            // Passes track inventory under a single 'pass' key, not per date.
            $ticketSoldCounts[$ticket->id] = $sold[$ticket->soldKey($requestedDate)] ?? 0;
        }

        // Get check-in counts and recent activity from SaleTickets
        $saleTickets = SaleTicket::with('sale:id,name', 'ticket:id,type')
            ->whereHas('sale', function ($q) use ($event, $requestedDate) {
                $q->where('event_id', $event->id)
                    ->where('event_date', $requestedDate)
                    ->where('status', 'paid')
                    ->where('is_deleted', false);
            })
            ->get();

        $checkedInCounts = [];
        $admittedCounts = [];
        $recentCheckins = [];

        // On an allocated event the door wants the seat, not the slot ordinal. One batched read
        // rather than seatLabels() per line: this endpoint is polled.
        $seatLabels = [];
        if ($event->hasAllocatedSeating()) {
            $seatLabels = \App\Models\SeatingSeat::with(['section', 'seatingTable'])
                ->whereIn('sale_ticket_id', $saleTickets->pluck('id'))
                ->orderBy('row_position')->orderBy('position')
                ->get()
                ->groupBy('sale_ticket_id')
                ->map(fn ($group) => $group->map(fn ($seat) => $seat->fullLabel())->values()->all())
                ->all();
        }

        foreach ($saleTickets as $saleTicket) {
            $seats = $saleTicket->seats ? json_decode($saleTicket->seats, true) : [];
            if (! is_array($seats)) {
                $seats = [];
            }
            $labels = $seatLabels[$saleTicket->id] ?? [];
            $slot = -1;

            foreach ($seats as $seatNum => $timestamp) {
                $slot++;
                if ($timestamp !== null) {
                    $ticketId = $saleTicket->ticket_id;
                    $checkedInCounts[$ticketId] = ($checkedInCounts[$ticketId] ?? 0) + 1;
                    $admittedCounts[$ticketId] = ($admittedCounts[$ticketId] ?? 0) + 1;

                    $recentCheckins[] = [
                        'name' => $saleTicket->sale->name,
                        'ticket_type' => $saleTicket->ticket->type,
                        'seat_label' => $labels[$slot] ?? null,
                        'timestamp' => (int) $timestamp,
                    ];
                }
            }

            // Pass / subscription redemptions recorded at this event on this date.
            // Only redemptions count as checked in; an advance booking is a
            // reservation (the holder has not arrived yet) and is surfaced
            // separately via the reserved-seat count below.
            // (Cross-event subscriptions sold on another event surface on the
            // Subscriptions tab; here we count passes whose home event is this one.)
            foreach (($saleTicket->pass_usages ?? []) as $usage) {
                if ((int) ($usage['event_id'] ?? 0) === (int) $event->id
                    && ($usage['date'] ?? null) === $requestedDate
                    && ($usage['kind'] ?? 'redemption') === 'redemption') {
                    $ticketId = $saleTicket->ticket_id;
                    // One pass = one check-in (keeps checked-in / sold <= 100%),
                    // but a single visit may admit the holder plus guests - track
                    // that headcount separately so door staff see true attendance.
                    $admits = max(1, (int) ($usage['admits'] ?? 1));
                    $checkedInCounts[$ticketId] = ($checkedInCounts[$ticketId] ?? 0) + 1;
                    $admittedCounts[$ticketId] = ($admittedCounts[$ticketId] ?? 0) + $admits;

                    $recentCheckins[] = [
                        'name' => $saleTicket->sale->name.($admits > 1 ? ' (+'.($admits - 1).')' : ''),
                        'ticket_type' => $saleTicket->ticket->type,
                        'timestamp' => (int) ($usage['at'] ?? 0),
                    ];
                }
            }
        }

        // Build ticket stats
        foreach ($activeTickets as $ticket) {
            $sold = $ticketSoldCounts[$ticket->id];
            $checkedIn = $checkedInCounts[$ticket->id] ?? 0;
            $admitted = $admittedCounts[$ticket->id] ?? $checkedIn;

            $tickets[] = [
                'type' => $ticket->type,
                'sold' => $sold,
                'checked_in' => $checkedIn,
                'admitted' => $admitted,
            ];

            $totalSold += $sold;
            $totalCheckedIn += $checkedIn;
            $totalAdmitted += $admitted;
        }

        // Sort recent check-ins by timestamp desc, take top 10
        usort($recentCheckins, fn ($a, $b) => $b['timestamp'] - $a['timestamp']);
        $recentCheckins = array_slice($recentCheckins, 0, 10);

        return response()->json([
            'event_name' => $event->name,
            'date' => $requestedDate,
            'available_dates' => $availableDates,
            'tickets' => $tickets,
            'total_sold' => $totalSold,
            'total_checked_in' => $totalCheckedIn,
            // Headcount including pass guests (>= checked_in). Surfaced as a
            // secondary line; the checked_in / sold ratio stays the primary stat.
            'total_admitted' => $totalAdmitted,
            // Total pass seats reserved for this occurrence (advance bookings plus
            // any already redeemed), so door staff see expected pass attendance.
            'pass_reserved' => $event->passReservedSeats($requestedDate),
            'recent_checkins' => $recentCheckins,
        ]);
    }
}
