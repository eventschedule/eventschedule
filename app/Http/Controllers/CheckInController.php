<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Utils\UrlUtils;
use Carbon\Carbon;

class CheckInController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $events = Event::where('user_id', $user->id)
            ->whereHas('tickets')
            ->orderBy('starts_at', 'desc')
            ->get();

        $today = now()->format('Y-m-d');

        // Pre-select event with sales for today, else most recent
        $selectedEventId = null;
        foreach ($events as $event) {
            $hasTodaySales = Sale::where('event_id', $event->id)
                ->where('event_date', $today)
                ->where('status', 'paid')
                ->where('is_deleted', false)
                ->exists();

            if ($hasTodaySales) {
                $selectedEventId = $event->id;
                break;
            }
        }

        if (! $selectedEventId && $events->isNotEmpty()) {
            $selectedEventId = $events->first()->id;
        }

        $eventsData = $events->map(function ($event) {
            return [
                'id' => UrlUtils::encodeId($event->id),
                'name' => $event->name,
                'starts_at' => $event->starts_at ? Carbon::parse($event->starts_at)->format('D, M j, Y') : null,
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
        $event = Event::with(['tickets', 'roles'])->find(UrlUtils::decodeId($eventId));

        if (! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        if (! $user->canScanEvent($event)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $requestedDate = request()->query('date', now()->format('Y-m-d'));

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

        $activeTickets = $event->tickets->where('is_deleted', false);

        // Get sold counts per ticket
        $ticketSoldCounts = [];
        foreach ($activeTickets as $ticket) {
            $sold = $ticket->sold ? json_decode($ticket->sold, true) : [];
            $ticketSoldCounts[$ticket->id] = $sold[$requestedDate] ?? 0;
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
        $recentCheckins = [];

        foreach ($saleTickets as $saleTicket) {
            $seats = $saleTicket->seats ? json_decode($saleTicket->seats, true) : [];

            foreach ($seats as $seatNum => $timestamp) {
                if ($timestamp !== null) {
                    $ticketId = $saleTicket->ticket_id;
                    $checkedInCounts[$ticketId] = ($checkedInCounts[$ticketId] ?? 0) + 1;

                    $recentCheckins[] = [
                        'name' => $saleTicket->sale->name,
                        'ticket_type' => $saleTicket->ticket->type,
                        'timestamp' => (int) $timestamp,
                    ];
                }
            }
        }

        // Build ticket stats
        foreach ($activeTickets as $ticket) {
            $sold = $ticketSoldCounts[$ticket->id];
            $checkedIn = $checkedInCounts[$ticket->id] ?? 0;

            $tickets[] = [
                'type' => $ticket->type,
                'sold' => $sold,
                'checked_in' => $checkedIn,
            ];

            $totalSold += $sold;
            $totalCheckedIn += $checkedIn;
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
            'recent_checkins' => $recentCheckins,
        ]);
    }
}
