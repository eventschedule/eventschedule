<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleTicketEntry;
use App\Models\Event;
use App\Models\EventRole;
use App\Utils\UrlUtils;
use Illuminate\Support\Str;
use Stripe\StripeClient;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TicketSaleNotification;
use App\Utils\NotificationUtils;

class ApiTicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get all events the user manages (owns or is a member of)
        $managedEventIds = Event::where('user_id', $user->id)
            ->pluck('id')
            ->toArray();
        
        // Also get events where user is a member of event roles
        $roleIds = $user->roles()->pluck('roles.id')->toArray();
        if (!empty($roleIds)) {
            $eventRoleIds = \App\Models\EventRole::whereIn('role_id', $roleIds)
                ->distinct()
                ->pluck('event_id')
                ->toArray();
            $managedEventIds = array_unique(array_merge($managedEventIds, $eventRoleIds));
        }
        
        // If user has no managed events, return empty result
        if (empty($managedEventIds)) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 50,
                    'total' => 0,
                ],
            ], 200, [], JSON_PRETTY_PRINT);
        }

        // Build query for sales from managed events
        $query = Sale::with(['event', 'saleTickets.ticket', 'saleTickets.entries'])
            ->whereIn('event_id', $managedEventIds)
            ->where('is_deleted', false);
        
        // Optional: filter by event_id
        if ($request->filled('event_id')) {
            $eventId = $request->integer('event_id');
            if (in_array($eventId, $managedEventIds)) {
                $query->where('event_id', $eventId);
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        
        // Optional: filter by query (name or email)
        if ($request->filled('query')) {
            $searchTerm = $request->string('query')->trim()->value();
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $sales = $query->orderByDesc('created_at')->paginate(50);

        return response()->json([
            'data' => $sales->map(function ($sale) {
                // Ensure saleTickets are loaded
                if (!$sale->relationLoaded('saleTickets')) {
                    $sale->load(['saleTickets.entries']);
                }
                
                return [
                    'id' => $sale->id,
                    'status' => $sale->status,
                    'name' => $sale->name,
                    'email' => $sale->email,
                    'event_id' => $sale->event_id,
                    'event' => $sale->event ? $sale->event->toApiData() : null,
                    'tickets' => $sale->saleTickets ? $sale->saleTickets->map(function ($st) {
                        return [
                            'id' => $st->id,
                            'ticket_id' => $st->ticket_id,
                            'quantity' => $st->quantity,
                            'usage_status' => $st->getUsageStatusAttribute(),
                        ];
                    })->toArray() : [],
                ];
            })->values(),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function update(Request $request, $sale_id)
    {
        $decoded = UrlUtils::decodeId($sale_id);
        $id = $decoded ?? $sale_id;

        $sale = Sale::findOrFail($id);
        $user = $request->user();
        
        // Check if user is authorized: either owns the sale or manages the event
        $isOwner = $sale->user_id === $user->id;
        $isEventManager = $user->canEditEvent($sale->event);
        
        if (!$isOwner && !$isEventManager) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'action' => ['nullable', 'string', 'in:mark_paid,mark_unpaid,refund,cancel,delete,mark_used,mark_unused'],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255'],
        ]);

        if (! empty($validated['name']) || ! empty($validated['email'])) {
            $sale->fill(array_filter([ 'name' => $validated['name'] ?? null, 'email' => $validated['email'] ?? null ]));
            $sale->save();
        }

        if (! empty($validated['action'])) {
            $action = $validated['action'];
            $previousStatus = $sale->status;
            $restockStatuses = ['refunded', 'cancelled', 'expired', 'deleted'];

            switch ($action) {
                case 'mark_paid':
                    Sale::where('id', $sale->id)->update(['status' => 'paid', 'transaction_reference' => 'Manual payment via API']);
                    $sale = Sale::findOrFail($id);
                    break;
                case 'mark_unpaid':
                    Sale::where('id', $sale->id)->update(['status' => 'unpaid']);
                    $sale = Sale::findOrFail($id);
                    break;
                case 'refund':
                    Sale::where('id', $sale->id)->update(['status' => 'refunded']);
                    $sale = Sale::findOrFail($id);
                    break;
                case 'cancel':
                    Sale::where('id', $sale->id)->update(['status' => 'cancelled']);
                    $sale = Sale::findOrFail($id);
                    break;
                case 'delete':
                    Sale::where('id', $sale->id)->update(['is_deleted' => true]);
                    $sale = Sale::findOrFail($id);
                    break;
                case 'mark_used':
                    $sale->load('saleTickets.entries');
                    $timestamp = now();
                    foreach ($sale->saleTickets as $saleTicket) {
                        foreach ($saleTicket->entries as $entry) {
                            if ($entry->scanned_at === null) {
                                $entry->scanned_at = $timestamp;
                                $entry->save();
                            }
                        }
                    }
                    break;
                case 'mark_unused':
                    $sale->load('saleTickets.entries');
                    foreach ($sale->saleTickets as $saleTicket) {
                        foreach ($saleTicket->entries as $entry) {
                            if ($entry->scanned_at !== null) {
                                $entry->scanned_at = null;
                                $entry->save();
                            }
                        }
                    }
                    break;
                default:
                    return response()->json(['error' => 'Unknown action'], 422);
            }

            // Return inventory to pool when moving into a restock status from a non-restock status.
            if (! in_array($previousStatus, $restockStatuses, true)
                && in_array($sale->status, $restockStatuses, true)) {
                $sale->loadMissing('saleTickets.ticket');
                foreach ($sale->saleTickets as $saleTicket) {
                    if ($saleTicket->ticket) {
                        $saleTicket->ticket->updateSold($sale->event_date, -$saleTicket->quantity);
                    }
                }
            }
        }

        // Reload fresh data with all relationships to return accurate state
        $sale = Sale::with(['event', 'saleTickets.ticket', 'saleTickets.entries'])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $sale->id,
                'status' => $sale->status,
                'name' => $sale->name,
                'email' => $sale->email,
                'event_id' => $sale->event_id,
                'event' => $sale->event ? $sale->event->toApiData() : null,
                'tickets' => $sale->saleTickets->map(function ($st) {
                    return [
                        'id' => $st->id,
                        'ticket_id' => $st->ticket_id,
                        'quantity' => $st->quantity,
                        'usage_status' => $st->usage_status ?? $st->getUsageStatusAttribute(),
                    ];
                })->all(),
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function scanByCode(Request $request)
    {
        $validated = $request->validate([
            'ticket_code' => ['required', 'string'],
            'sale_ticket_id' => ['nullable', 'integer'],
            'seat_number' => ['nullable', 'string', 'max:255'],
        ]);

        // Lookup sale by secret (ticket code)
        $sale = Sale::with(['event', 'saleTickets.ticket', 'saleTickets.entries'])
            ->where('secret', $validated['ticket_code'])
            ->where('is_deleted', false)
            ->first();

        if (!$sale) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        // Check if user manages this event
        $user = $request->user();
        $managedEventIds = Event::where('user_id', $user->id)->pluck('id')->toArray();
        $roleIds = $user->roles()->pluck('roles.id')->toArray();
        if (!empty($roleIds)) {
            $eventRoleIds = \App\Models\EventRole::whereIn('role_id', $roleIds)
                ->distinct()
                ->pluck('event_id')
                ->toArray();
            $managedEventIds = array_unique(array_merge($managedEventIds, $eventRoleIds));
        }

        if (!in_array($sale->event_id, $managedEventIds)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Determine which sale_ticket to scan
        if (isset($validated['sale_ticket_id'])) {
            $saleTicket = $sale->saleTickets->firstWhere('id', $validated['sale_ticket_id']);
            if (!$saleTicket) {
                return response()->json(['error' => 'Sale ticket not found'], 404);
            }
        } else {
            // Default to first sale ticket
            $saleTicket = $sale->saleTickets->first();
            if (!$saleTicket) {
                return response()->json(['error' => 'No tickets found in this sale'], 404);
            }
        }

        // Create entry and mark as scanned
        $entry = SaleTicketEntry::create([
            'sale_ticket_id' => $saleTicket->id,
            'secret' => Str::random(24),
            'seat_number' => $validated['seat_number'] ?? null,
            'scanned_at' => now(),
        ]);

        // Reload to get fresh usage status
        $sale->load(['saleTickets.entries']);

        return response()->json([
            'data' => [
                'sale_id' => $sale->id,
                'entry_id' => $entry->id,
                'scanned_at' => $entry->scanned_at->toIsoString(),
                'sale' => [
                    'id' => $sale->id,
                    'status' => $sale->status,
                    'name' => $sale->name,
                    'email' => $sale->email,
                    'event_id' => $sale->event_id,
                    'event' => $sale->event ? $sale->event->toApiData() : null,
                    'tickets' => $sale->saleTickets->map(function ($st) {
                        return [
                            'id' => $st->id,
                            'ticket_id' => $st->ticket_id,
                            'quantity' => $st->quantity,
                            'usage_status' => $st->usage_status,
                        ];
                    })->values()->all(),
                ],
            ],
        ], 201, [], JSON_PRETTY_PRINT);
    }

    public function scan(Request $request, $sale_id)
    {
        $decoded = UrlUtils::decodeId($sale_id);
        $id = $decoded ?? $sale_id;

        $sale = Sale::with('saleTickets')->findOrFail($id);

        if ($sale->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'sale_ticket_id' => ['required', 'integer', 'exists:sale_tickets,id'],
            'seat_number' => ['nullable', 'string', 'max:255'],
        ]);

        $saleTicketId = $validated['sale_ticket_id'];

        $entry = SaleTicketEntry::create([
            'sale_ticket_id' => $saleTicketId,
            'secret' => Str::random(24),
            'seat_number' => $validated['seat_number'] ?? null,
            'scanned_at' => now(),
        ]);

        return response()->json(['data' => ['entry_id' => $entry->id, 'scanned_at' => $entry->scanned_at->toIsoString()]], 201, [], JSON_PRETTY_PRINT);
    }

    public function checkout(Request $request, $sale_id)
    {
        $decoded = UrlUtils::decodeId($sale_id);
        $id = $decoded ?? $sale_id;

        $sale = Sale::with(['saleTickets.ticket', 'event.user'])->findOrFail($id);

        if ($sale->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $event = $sale->event;

        $lineItems = [];
        foreach ($sale->saleTickets as $saleTicket) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $event->ticket_currency_code,
                    'product_data' => [
                        'name' => $saleTicket->ticket->type ?: 'Tickets',
                    ],
                    'unit_amount' => $saleTicket->ticket->price * 100,
                ],
                'quantity' => $saleTicket->quantity,
            ];
        }

        $stripe = new StripeClient(config('services.stripe.key'));

        $data = [
            'sale_id' => UrlUtils::encodeId($sale->id),
            'subdomain' => $sale->subdomain ?? null,
            'date' => $sale->event_date,
        ];

        $options = [
            'line_items' => $lineItems,
            'mode' => 'payment',
            'customer_email' => $sale->email,
            'metadata' => [
                'customer_name' => $sale->name,
            ],
            'success_url' => route('checkout.success', $data) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', $data),
        ];

        $accountOptions = [];
        if ($event && $event->user && $event->user->stripe_account_id) {
            $accountOptions['stripe_account'] = $event->user->stripe_account_id;
        }

        $session = $stripe->checkout->sessions->create($options, $accountOptions);

        return response()->json(['data' => ['url' => $session->url, 'id' => $session->id]], 201, [], JSON_PRETTY_PRINT);
    }

    public function createSale(Request $request, $subdomain)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'event_date' => ['required', 'date_format:Y-m-d'],
            'tickets' => ['required', 'array'],
            'tickets.*' => ['integer', 'min:0'],
        ]);

        $event = Event::where('subdomain', $subdomain)->firstOrFail();

        // If the event is password protected, require password match
        if ($event->hasPassword()) {
            $password = $request->input('password');
            if (! $password || ! $event->verifyPassword($password)) {
                return response()->json(['error' => 'Invalid event password'], 403);
            }
        }

        // Basic ticket availability check (mirrors web logic minimally)
        foreach ($validated['tickets'] as $ticketHash => $quantity) {
            if ($quantity <= 0) continue;
            $ticketId = UrlUtils::decodeId($ticketHash);
            $ticketModel = Ticket::findOrFail($ticketId);
            // If a ticket has limited quantity, ensure enough remain
            if ($ticketModel->quantity > 0) {
                $sold = $ticketModel->sold ? json_decode($ticketModel->sold, true) : [];
                $soldCount = $sold[$validated['event_date']] ?? 0;
                $remaining = $ticketModel->quantity - $soldCount;
                if ($quantity > $remaining) {
                    return response()->json(['error' => 'Tickets not available'], 422);
                }
            }
        }

        $sale = new Sale();
        $sale->name = $validated['name'];
        $sale->email = $validated['email'];
        $sale->event_id = $event->id;
        $sale->user_id = auth()->id() ?: null;
        $sale->secret = strtolower(Str::random(32));
        $sale->payment_method = $event->payment_method;
        $sale->event_date = $validated['event_date'];
        $sale->save();

        foreach ($validated['tickets'] as $ticketHash => $quantity) {
            if ($quantity <= 0) continue;
            $ticketId = UrlUtils::decodeId($ticketHash);
            $saleTicket = $sale->saleTickets()->create([
                'sale_id' => $sale->id,
                'ticket_id' => $ticketId,
                'quantity' => $quantity,
            ]);

            $entries = [];
            for ($seat = 1; $seat <= $quantity; $seat++) {
                $entries[] = ['seat_number' => $seat, 'secret' => strtolower(Str::random(32))];
            }
            $saleTicket->entries()->createMany($entries);
        }

        $sale->payment_amount = $sale->calculateTotal();
        $sale->save();

        // Dispatch notifications (similar to web flow)
        $sale->loadMissing(['saleTickets.ticket', 'event.roles.members', 'event.venue.members', 'event.creatorRole.members', 'event.user']);
        $event = $sale->event;
        $contextRole = $event->venue ?: $event->creatorRole;

        if ($sale->email) {
            Notification::route('mail', $sale->email)
                ->notify(new TicketSaleNotification($sale, 'purchaser', $contextRole));
        }

        $notifiedUserIds = collect();
        $organizerRoles = collect([$event->creatorRole, $event->venue])->filter();

        NotificationUtils::uniqueRoleMembersWithContext($organizerRoles)->each(function (array $recipient) use (&$notifiedUserIds, $sale) {
            $recipient['user']->notify(new TicketSaleNotification($sale, 'organizer', $recipient['role']));
            $notifiedUserIds->push($recipient['user']->id);
        });

        if ($event->user && $event->user->email && $event->user->is_subscribed !== false && ! $notifiedUserIds->contains($event->user->id)) {
            Notification::send($event->user, new TicketSaleNotification($sale, 'organizer', $contextRole));
        }

        return response()->json(['data' => ['id' => UrlUtils::encodeId($sale->id), 'status' => $sale->status, 'payment_method' => $sale->payment_method]], 201, [], JSON_PRETTY_PRINT);
    }

    /**
     * Reassign ticket to a new holder.
     */
    public function reassign(Request $request, $sale_id)
    {
        $decoded = UrlUtils::decodeId($sale_id);
        $id = $decoded ?? $sale_id;

        $sale = Sale::findOrFail($id);

        if ($sale->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'new_holder_name' => 'required|string|max:255',
            'new_holder_email' => 'required|email|max:255',
        ]);

        $sale->update([
            'name' => $validated['new_holder_name'],
            'email' => $validated['new_holder_email'],
        ]);

        return response()->json([
            'message' => 'Ticket reassigned successfully',
            'data' => [
                'id' => $sale->id,
                'new_holder_name' => $sale->name,
                'new_holder_email' => $sale->email,
            ],
        ]);
    }

    /**
     * Add internal note to ticket.
     */
    public function addNote(Request $request, $sale_id)
    {
        $decoded = UrlUtils::decodeId($sale_id);
        $id = $decoded ?? $sale_id;

        $sale = Sale::findOrFail($id);

        if ($sale->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        // Get existing notes (assuming a 'notes' column or we'll use 'metadata' style field)
        // For simplicity, we'll store as JSON array
        $notes = $sale->notes ? json_decode($sale->notes, true) : [];
        
        $notes[] = [
            'note' => $validated['note'],
            'created_at' => now()->toIso8601String(),
            'created_by' => $request->user()->id,
        ];

        $sale->notes = json_encode($notes);
        $sale->save();

        return response()->json([
            'message' => 'Note added successfully',
            'data' => [
                'note' => $validated['note'],
                'created_at' => now()->toIso8601String(),
            ],
        ], 201);
    }
}
