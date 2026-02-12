<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApiSaleController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'event_id' => 'required|string',
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'tickets' => 'required|array',
                'tickets.*' => 'required|integer|min:1',
                // Note: status parameter is intentionally not accepted from API input for security
                // Sales are always created as 'unpaid' and must go through proper payment flow
                'event_date' => 'nullable|date_format:Y-m-d',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $eventId = UrlUtils::decodeId($request->event_id);
        $event = Event::with(['roles', 'tickets'])->find($eventId);

        if (! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        // Verify user can edit the event (owner or owner/admin on associated schedule)
        if (! auth()->user()->canEditEvent($event)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify event has tickets enabled and is Pro
        if (! $event->canSellTickets()) {
            return response()->json(['error' => 'Event does not have tickets enabled or is not a Pro account'], 422);
        }

        // Check if trying to buy tickets for a past recurring event occurrence
        if ($event->days_of_week && $request->event_date) {
            $startDateTime = $event->getStartDateTime($request->event_date, true);
            if ($startDateTime->isPast()) {
                return response()->json(['error' => 'Cannot buy tickets for events in the past.'], 422);
            }
        }

        // Validate tickets - support both ticket ID and ticket type
        $ticketIds = [];
        foreach ($request->tickets as $ticketIdentifier => $quantity) {
            $ticket = null;

            // Try to decode as ticket ID first
            try {
                $decodedTicketId = UrlUtils::decodeId($ticketIdentifier);
                $ticket = $event->tickets()->where('id', $decodedTicketId)->where('is_deleted', false)->first();
            } catch (\Exception $e) {
                // Not a valid encoded ID, treat as ticket type
            }

            // If not found by ID, try to find by type
            if (! $ticket) {
                $ticket = $event->tickets()->where('type', $ticketIdentifier)->where('is_deleted', false)->first();

                if (! $ticket) {
                    return response()->json(['error' => 'Ticket not found: '.$ticketIdentifier.' (tried as ID and type)'], 422);
                }
            }

            $ticketIds[$ticket->id] = $quantity;
        }

        // Determine event_date
        $eventDate = $request->event_date;
        if (! $eventDate) {
            if (! $event->starts_at) {
                return response()->json(['error' => 'Event has no start date. Please provide event_date parameter.'], 422);
            }
            $eventDate = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d');
        }

        // Get subdomain from event
        $subdomain = null;
        if ($event->creatorRole) {
            $subdomain = $event->creatorRole->subdomain;
        } elseif ($event->venue) {
            $subdomain = $event->venue->subdomain;
        } elseif ($event->roles->count() > 0) {
            $subdomain = $event->roles->first()->subdomain;
        }

        if (! $subdomain) {
            return response()->json(['error' => 'Unable to determine subdomain for event'], 422);
        }

        // Use a transaction with row locking to prevent overselling
        try {
            $sale = DB::transaction(function () use ($event, $ticketIds, $eventDate, $request, $subdomain) {
                // Re-fetch tickets with a lock to prevent race conditions
                $lockedTickets = $event->tickets()->lockForUpdate()->get();

                // Check ticket availability
                foreach ($ticketIds as $ticketId => $quantity) {
                    $ticket = $lockedTickets->find($ticketId);

                    if ($ticket->quantity > 0) {
                        // Handle combined mode logic
                        if ($event->total_tickets_mode === 'combined' && $event->hasSameTicketQuantities()) {
                            $totalSold = $lockedTickets->sum(function ($t) use ($eventDate) {
                                $ticketSold = $t->sold ? (json_decode($t->sold, true) ?? []) : [];

                                return $ticketSold[$eventDate] ?? 0;
                            });
                            $totalQuantity = $event->getSameTicketQuantity();
                            $remainingTickets = $totalQuantity - $totalSold;

                            $totalRequested = array_sum($ticketIds);
                            if ($totalRequested > $remainingTickets) {
                                throw new \RuntimeException('Tickets not available. Remaining: '.$remainingTickets);
                            }
                        } else {
                            $sold = ($ticket->sold ? json_decode($ticket->sold, true) : null) ?? [];
                            $soldCount = $sold[$eventDate] ?? 0;
                            $remainingTickets = $ticket->quantity - $soldCount;

                            if ($quantity > $remainingTickets) {
                                throw new \RuntimeException('Tickets not available for ticket '.UrlUtils::encodeId($ticketId).'. Remaining: '.$remainingTickets);
                            }
                        }
                    }
                }

                // Create sale
                $sale = new Sale;
                $sale->event_id = $event->id;
                $sale->user_id = auth()->id();
                $sale->name = $request->name;
                $sale->email = $request->email;
                $sale->secret = strtolower(Str::random(32));
                $sale->event_date = $eventDate;
                $sale->subdomain = $subdomain;
                $sale->payment_method = $event->payment_method;
                $sale->status = 'unpaid';
                $sale->save();

                // Create sale tickets
                foreach ($ticketIds as $ticketId => $quantity) {
                    $sale->saleTickets()->create([
                        'sale_id' => $sale->id,
                        'ticket_id' => $ticketId,
                        'quantity' => $quantity,
                        'seats' => json_encode(array_fill(1, $quantity, null)),
                    ]);
                }

                // Calculate and set payment amount
                $total = $sale->calculateTotal();
                $sale->payment_amount = $total;
                $sale->save();

                // If total is 0, mark as paid (free tickets)
                if ($total == 0) {
                    $sale->status = 'paid';
                    $sale->save();
                }

                return $sale;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        // Reload sale with relationships for API response
        $sale->load('saleTickets.ticket');

        return response()->json([
            'data' => $sale->toApiData(),
            'meta' => [
                'message' => 'Sale created successfully',
            ],
        ], 201, [], JSON_PRETTY_PRINT);
    }
}
