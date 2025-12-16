<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleTicketEntry;
use App\Utils\UrlUtils;
use Illuminate\Support\Str;
use Stripe\StripeClient;
use App\Models\Event;
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

        $sales = Sale::with(['event', 'saleTickets.ticket'])
            ->where('user_id', $user->id)
            ->where('is_deleted', false)
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json([
            'data' => $sales->map(function ($sale) {
                return [
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

        if ($sale->user_id !== $request->user()->id) {
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

            switch ($action) {
                case 'mark_paid':
                    if ($sale->status === 'unpaid') {
                        $sale->update(['status' => 'paid', 'transaction_reference' => 'Manual payment via API']);
                    }
                    break;
                case 'mark_unpaid':
                    if (in_array($sale->status, ['paid', 'cancelled'])) {
                        $sale->update(['status' => 'unpaid']);
                    }
                    break;
                case 'refund':
                    if ($sale->status === 'paid') {
                        $sale->update(['status' => 'refunded']);
                    }
                    break;
                case 'cancel':
                    if (in_array($sale->status, ['unpaid', 'paid'])) {
                        $sale->update(['status' => 'cancelled']);
                    }
                    break;
                case 'delete':
                    $sale->update(['is_deleted' => true]);
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
        }

        return response()->json(['data' => ['id' => $sale->id, 'status' => $sale->status]], 200, [], JSON_PRETTY_PRINT);
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
