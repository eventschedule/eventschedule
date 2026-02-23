<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEventsDaily;
use App\Models\Event;
use App\Models\Sale;
use App\Services\AuditService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApiSaleController extends Controller
{
    protected const MAX_PER_PAGE = 500;

    protected const DEFAULT_PER_PAGE = 100;

    public function index(Request $request)
    {
        try {
            $request->validate([
                'event_id' => 'nullable|string',
                'subdomain' => 'nullable|string',
                'status' => 'nullable|string|in:unpaid,paid,cancelled,refunded,expired',
                'email' => 'nullable|string|email',
                'event_date' => 'nullable|date_format:Y-m-d',
                'per_page' => 'nullable|integer|min:1|max:500',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $perPage = min(
            (int) $request->input('per_page', self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );

        $sales = Sale::with(['saleTickets.ticket', 'event'])
            ->where('is_deleted', false)
            ->whereHas('event.roles', function ($q) {
                $q->whereIn('roles.id', auth()->user()->roles()
                    ->wherePivotIn('level', ['owner', 'admin'])
                    ->pluck('roles.id'));
            })
            ->whereHas('event.roles', function ($q) {
                $q->wherePro();
            });

        if ($request->has('event_id')) {
            $sales->where('event_id', UrlUtils::decodeId($request->event_id));
        }

        if ($request->has('subdomain')) {
            $sales->where('subdomain', $request->subdomain);
        }

        if ($request->has('status')) {
            $sales->where('status', $request->status);
        }

        if ($request->has('email')) {
            $sales->where('email', $request->email);
        }

        if ($request->has('event_date')) {
            $sales->where('event_date', $request->event_date);
        }

        $sales = $sales->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $sales->map(function ($sale) {
                return $sale->toApiData();
            })->values(),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'from' => $sales->firstItem(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'to' => $sales->lastItem(),
                'total' => $sales->total(),
                'path' => $request->url(),
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function show(Request $request, $id)
    {
        $sale = Sale::with(['saleTickets.ticket', 'event'])->where('is_deleted', false)->find(UrlUtils::decodeId($id));

        if (! $sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        if (! auth()->user()->canEditEvent($sale->event)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $sale->event->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        return response()->json([
            'data' => $sale->toApiData(),
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function update(Request $request, $id)
    {
        $sale = Sale::with(['saleTickets.ticket', 'event'])->where('is_deleted', false)->find(UrlUtils::decodeId($id));

        if (! $sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        if (! auth()->user()->canEditEvent($sale->event)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $sale->event->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        try {
            $request->validate([
                'action' => 'required|string|in:mark_paid,refund,cancel',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $previousStatus = $sale->status;
        $actionPerformed = false;

        switch ($request->action) {
            case 'mark_paid':
                if ($sale->status === 'unpaid') {
                    $sale->status = 'paid';
                    $sale->transaction_reference = 'Manual payment (API)';
                    $sale->save();
                    $actionPerformed = true;

                    AnalyticsEventsDaily::incrementSale($sale->event_id, $sale->payment_amount);
                }
                break;

            case 'refund':
                if ($sale->status === 'paid') {
                    DB::transaction(function () use ($sale) {
                        $sale->status = 'refunded';
                        $sale->save();

                        AnalyticsEventsDaily::decrementSale($sale->event_id, $sale->payment_amount);
                    });
                    $actionPerformed = true;
                }
                break;

            case 'cancel':
                if (in_array($sale->status, ['unpaid', 'paid'])) {
                    $wasPaid = $sale->status === 'paid';
                    DB::transaction(function () use ($sale) {
                        $sale->status = 'cancelled';
                        $sale->save();
                    });
                    $actionPerformed = true;

                    if ($wasPaid) {
                        AnalyticsEventsDaily::decrementSale($sale->event_id, $sale->payment_amount);
                    }
                }
                break;
        }

        if (! $actionPerformed) {
            return response()->json([
                'error' => 'Action "'.$request->action.'" cannot be performed on a sale with status "'.$sale->status.'"',
            ], 422);
        }

        $auditAction = match ($request->action) {
            'refund' => AuditService::SALE_REFUND,
            default => AuditService::SALE_CHECKIN,
        };
        AuditService::log($auditAction, auth()->id(), 'Sale', $sale->id,
            ['status' => $previousStatus],
            ['status' => $sale->status],
            $request->action.':event_id:'.$sale->event_id
        );

        $sale->load(['saleTickets.ticket', 'event']);

        return response()->json([
            'data' => $sale->toApiData(),
            'meta' => [
                'message' => 'Sale updated successfully',
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function destroy(Request $request, $id)
    {
        $sale = Sale::with('event')->where('is_deleted', false)->find(UrlUtils::decodeId($id));

        if (! $sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        if (! auth()->user()->canEditEvent($sale->event)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $sale->event->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        $previousStatus = $sale->status;

        DB::transaction(function () use ($sale) {
            // If the sale was paid, cancel first to release ticket inventory
            // (triggers Sale::booted hook) and decrement analytics
            if ($sale->status === 'paid') {
                $sale->status = 'cancelled';
                $sale->save();

                AnalyticsEventsDaily::decrementSale($sale->event_id, $sale->payment_amount);
            }

            $sale->is_deleted = true;
            $sale->save();
        });

        AuditService::log(AuditService::SALE_CHECKIN, auth()->id(), 'Sale', $sale->id,
            ['status' => $previousStatus],
            ['status' => $sale->status, 'is_deleted' => true],
            'delete:event_id:'.$sale->event_id
        );

        return response()->json([
            'data' => [
                'message' => 'Sale deleted successfully',
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

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
        $event = Event::with(['roles', 'tickets', 'creatorRole'])->find($eventId);

        if (! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        // Verify user can edit the event (owner or owner/admin on associated schedule)
        if (! auth()->user()->canEditEvent($event)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if non-recurring event is in the past
        if (! $event->days_of_week && $event->starts_at && Carbon::parse($event->starts_at)->isPast()) {
            return response()->json(['error' => 'Cannot sell tickets for events in the past'], 422);
        }

        // Verify event has tickets enabled and is Pro
        if (! $event->canSellTickets()) {
            return response()->json(['error' => 'Event does not have tickets enabled or is not a Pro account'], 422);
        }

        // Require event_date for recurring events
        if ($event->days_of_week && ! $request->event_date) {
            return response()->json(['error' => 'The event_date parameter is required for recurring events'], 422);
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

        // Check if recurring event occurrence is in the past
        if ($event->days_of_week) {
            $startDateTime = $event->getStartDateTime($eventDate, true);
            if ($startDateTime->isPast()) {
                return response()->json(['error' => 'Cannot sell tickets for events in the past'], 422);
            }
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

                    if (! $ticket) {
                        throw new \RuntimeException('Ticket no longer available');
                    }

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
        $sale->load(['saleTickets.ticket', 'event']);

        return response()->json([
            'data' => $sale->toApiData(),
            'meta' => [
                'message' => 'Sale created successfully',
            ],
        ], 201, [], JSON_PRETTY_PRINT);
    }
}
