<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Utils\UrlUtils;

class CheckInController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $events = Event::with('creatorRole')
            ->where('user_id', $user->id)
            ->whereNull('appointment_type_id') // appointment bookings are not check-in events
            ->where(fn ($q) => $q->whereHas('tickets')->orWhere('rsvp_enabled', true))
            ->orderBy('starts_at', 'desc')
            ->get();

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

        foreach ($saleTickets as $saleTicket) {
            $seats = $saleTicket->seats ? json_decode($saleTicket->seats, true) : [];
            if (! is_array($seats)) {
                $seats = [];
            }

            foreach ($seats as $seatNum => $timestamp) {
                if ($timestamp !== null) {
                    $ticketId = $saleTicket->ticket_id;
                    $checkedInCounts[$ticketId] = ($checkedInCounts[$ticketId] ?? 0) + 1;
                    $admittedCounts[$ticketId] = ($admittedCounts[$ticketId] ?? 0) + 1;

                    $recentCheckins[] = [
                        'name' => $saleTicket->sale->name,
                        'ticket_type' => $saleTicket->ticket->type,
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
