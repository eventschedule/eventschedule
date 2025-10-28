<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Event;
use App\Models\SaleTicket;
use App\Utils\UrlUtils;
use Stripe\StripeClient;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use App\Utils\InvoiceNinja;
use App\Utils\NotificationUtils;
use App\Rules\NoFakeEmail;
use App\Services\Wallet\AppleWalletService;
use App\Services\Wallet\GoogleWalletService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TicketSaleNotification;

class TicketController extends Controller
{
    private const SALE_ACTIONS = ['mark_paid', 'refund', 'cancel', 'delete'];

    public function tickets()
    {
        $user = auth()->user();

        $sales = Sale::with('event', 'saleTickets')
            ->where('user_id', $user->id)
            ->where('is_deleted', false)
            ->where('event_date', '>=', now()->subDay()->startOfDay())
            ->whereHas('event', function($query) {
                $query->where('starts_at', '>=', now()->subDay()->startOfDay());
            })
            ->orderBy('event_date', 'ASC')
            ->get();

        return view('ticket.index', compact('sales'));
    }

    public function sales()
    {
        $user = auth()->user();
        $filter = strtolower(request()->filter);

        $columnFilters = [
            'customer' => trim((string) request()->input('filter_customer', '')),
            'event' => trim((string) request()->input('filter_event', '')),
            'total_min' => request()->input('filter_total_min'),
            'total_max' => request()->input('filter_total_max'),
            'transaction' => trim((string) request()->input('filter_transaction', '')),
            'status' => trim((string) request()->input('filter_status', '')),
            'usage' => trim((string) request()->input('filter_usage', '')),
        ];

        $query = Sale::with('event', 'saleTickets')
            ->where('is_deleted', false)
            ->whereHas('event', function($query) use ($user) {
                $query->where('user_id', $user->id);
            });

        if ($filter) {
            $query->where(function($q) use ($filter) {
                $q->where('status', 'LIKE', "%{$filter}%")
                  ->orWhere('transaction_reference', 'LIKE', "%{$filter}%")
                  ->orWhere('email', 'LIKE', "%{$filter}%")
                  ->orWhere('name', 'LIKE', "%{$filter}%")
                  ->orWhereHas('event', function($q) use ($filter) {
                      $q->where('name', 'LIKE', "%{$filter}%");
                  });
            });
        }

        if ($columnFilters['customer']) {
            $customerFilter = $columnFilters['customer'];
            $query->where(function($q) use ($customerFilter) {
                $q->where('name', 'LIKE', "%{$customerFilter}%")
                  ->orWhere('email', 'LIKE', "%{$customerFilter}%");
            });
        }

        if ($columnFilters['event']) {
            $eventFilter = $columnFilters['event'];
            $query->whereHas('event', function($q) use ($eventFilter) {
                $q->where('name', 'LIKE', "%{$eventFilter}%");
            });
        }

        if ($columnFilters['total_min'] !== null && $columnFilters['total_min'] !== '' && is_numeric($columnFilters['total_min'])) {
            $min = (float) $columnFilters['total_min'];
            $query->where('payment_amount', '>=', $min);
        }

        if ($columnFilters['total_max'] !== null && $columnFilters['total_max'] !== '' && is_numeric($columnFilters['total_max'])) {
            $max = (float) $columnFilters['total_max'];
            $query->where('payment_amount', '<=', $max);
        }

        if ($columnFilters['transaction']) {
            $transactionFilter = $columnFilters['transaction'];
            $query->where('transaction_reference', 'LIKE', "%{$transactionFilter}%");
        }

        if ($columnFilters['status'] && in_array($columnFilters['status'], ['unpaid', 'paid', 'cancelled', 'refunded', 'expired'])) {
            $query->where('status', $columnFilters['status']);
        }

        if ($columnFilters['usage'] && in_array($columnFilters['usage'], ['used', 'unused'], true)) {
            $usageQuery = clone $query;
            $matchingIds = $usageQuery->get()->filter(function (Sale $sale) use ($columnFilters) {
                return $sale->usage_status === $columnFilters['usage'];
            })->pluck('id');

            if ($matchingIds->isEmpty()) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereIn('id', $matchingIds);
            }
        }

        $count = $query->count();
        $sales = $query->orderBy('created_at', 'DESC')
                    ->paginate(50, ['*'], 'page');

        if (request()->ajax()) {
            return view('ticket.sales_table', compact('sales'));
        } else {
            return view('ticket.sales', compact('sales', 'count'));
        }
    }

    public function checkout(Request $request, $subdomain)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($request->event_id));
        $user = auth()->user();

        // Validate basic required fields for all checkout requests
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'tickets' => ['required', 'array'],
        ]);

        if (! $user && $request->create_account && config('app.hosted')) {

            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class, new NoFakeEmail],
                'password' => ['required', 'string', 'min:8'],
            ]);    

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'timezone' => $event->user->timezone,
                'language_code' => $event->user->language_code,
            ]);

            $role = Role::subdomain($subdomain)->firstOrFail();
            $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        // Check ticket availability
        foreach($request->tickets as $ticketId => $quantity) {
            if ($quantity > 0) {
                $ticketModel = $event->tickets()->findOrFail(UrlUtils::decodeId($ticketId));
                
                if (! $ticketModel) {
                    return back()->with('error', __('messages.ticket_not_found'));
                }

                if ($ticketModel->quantity > 0) {
                    // Handle combined mode logic
                    if ($event->total_tickets_mode === 'combined' && $event->hasSameTicketQuantities()) {
                        $totalSold = $event->tickets->sum(function($ticket) use ($request) {
                            $ticketSold = $ticket->sold ? json_decode($ticket->sold, true) : [];
                            return $ticketSold[$request->event_date] ?? 0;
                        });
                        // In combined mode, the total quantity is the same as individual quantity
                        $totalQuantity = $event->getSameTicketQuantity();
                        $remainingTickets = $totalQuantity - $totalSold;
                        
                        // Check if the total requested quantity exceeds remaining tickets
                        $totalRequested = array_sum($request->tickets);
                        if ($totalRequested > $remainingTickets) {
                            return back()->with('error', __('messages.tickets_not_available'));
                        }
                    } else {
                        $sold = json_decode($ticketModel->sold, true);
                        $soldCount = $sold[$request->event_date] ?? 0;
                        $remainingTickets = $ticketModel->quantity - $soldCount;

                        if ($quantity > $remainingTickets) {
                            return back()->with('error', __('messages.tickets_not_available'));
                        }
                    }
                }
            }
        }

        $sale = new Sale();
        $sale->fill($request->all());
        $sale->event_id = $event->id;
        $sale->user_id = $user ? $user->id : null;
        $sale->secret = strtolower(Str::random(32));
        $sale->payment_method = $event->payment_method;

        if (! $sale->event_date) {
            $sale->event_date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d');
        }

        $sale->save();

        Log::info('Ticket checkout created sale', [
            'sale_id' => $sale->id,
            'event_id' => $event->id,
            'event_name' => $event->name,
            'user_id' => optional($user)->id,
            'purchaser_email' => $sale->email,
            'total_tickets' => collect($request->tickets)->sum(),
            'status' => $sale->status,
            'payment_method' => $sale->payment_method,
            'event_date' => $sale->event_date,
        ]);

        foreach($request->tickets as $ticketId => $quantity) {
            if ($quantity > 0) {
                $sale->saleTickets()->create([
                    'sale_id' => $sale->id,
                    'ticket_id' => UrlUtils::decodeId($ticketId),
                    'quantity' => $quantity,
                    'seats' => json_encode(array_fill(1, $quantity, null)),
                ]);
            }
        }

        $total = $sale->calculateTotal();

        $sale->payment_amount = $total;
        $sale->save();

        Log::info('Ticket checkout completed sale totals', [
            'sale_id' => $sale->id,
            'payment_amount' => $sale->payment_amount,
            'status' => $sale->status,
        ]);

        $this->sendTicketSaleNotifications($sale);

        if ($total == 0) {
            $sale->status = 'paid';
            $sale->save();

            Log::info('Ticket checkout auto-marked sale as paid (zero total)', [
                'sale_id' => $sale->id,
                'event_id' => $event->id,
                'payment_amount' => $sale->payment_amount,
            ]);

            return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
        } else {
            switch ($event->payment_method) {
                case 'stripe':
                    return $this->stripeCheckout($subdomain, $sale, $event);
                case 'invoiceninja':
                    return $this->invoiceninjaCheckout($subdomain, $sale, $event);
                case 'payment_url':
                    return $this->paymentUrlCheckout($subdomain, $sale, $event);
                default:
                    return $this->cashCheckout($subdomain, $sale, $event);
            }
        }
    }

    private function stripeCheckout($subdomain, $sale, $event)
    {
        $lineItems = [];
        foreach ($sale->saleTickets as $saleTicket) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $event->ticket_currency_code,
                    'product_data' => [
                        'name' => $saleTicket->ticket->type ? $saleTicket->ticket->type : __('messages.tickets'),
                        ...$saleTicket->ticket->description ? ['description' => $saleTicket->ticket->description] : [],
                    ],
                    'unit_amount' => $saleTicket->ticket->price * 100,
                ],
                'quantity' => $saleTicket->quantity,
            ];
        }

        $stripe = new StripeClient(config('services.stripe.key'));
        $data = [
            'sale_id' => UrlUtils::encodeId($sale->id), 
            'subdomain' => $subdomain, 
            'date' => $sale->event_date,
        ];
        
        $session = $stripe->checkout->sessions->create(
            [
                'line_items' => $lineItems,                
                //'payment_intent_data' => ['application_fee_amount' => 123],
                'mode' => 'payment',
                'customer_email' => $sale->email,
                'metadata' => [
                    'customer_name' => $sale->name,
                ],
                'success_url' => route('checkout.success', $data) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.cancel', $data),
            ],
            [
                'stripe_account' => $event->user->stripe_account_id,
            ],
        );

        return redirect($session->url);
    }

    private function invoiceninjaCheckout($subdomain, $sale, $event)
    {
        $user = $event->user;
        $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);
        $company = null;

        $foundClient = false;
        $clientMachesEmail = false;
        $requirePassword = false;
        $sendEmail = false;

        $client = $invoiceNinja->findClient($sale->email, $event->ticket_currency_code);

        if ($client) {
            $foundClient = true;
            if (auth()->user() && auth()->user()->email_verified_at) {
                foreach ($client['contacts'] as $contact) {
                    if ($contact['email'] == auth()->user()->email) {
                        $clientMachesEmail = true;
                    }
                }
            }
            if (! $clientMachesEmail) {
                $company = $invoiceNinja->getCompany();
                $requirePassword = $company['settings']['enable_client_portal_password'];
            }
        } else {
            $client = $invoiceNinja->createClient($sale->name, $sale->email, $event->ticket_currency_code);
        }

        if ($foundClient && ! $clientMachesEmail && ! $requirePassword) {
            $sendEmail = true;
        }

        $lineItems = [];
        foreach ($sale->saleTickets as $saleTicket) {
            $lineItems[] = [
                'product_key' => $saleTicket->ticket->type,
                'notes' => $saleTicket->ticket->description ?? __('messages.tickets'),
                'quantity' => $saleTicket->quantity,
                'cost' => $saleTicket->ticket->price,
            ];
        }

        $qrCodeUrl = route('ticket.qr_code', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
        $invoice = $invoiceNinja->createInvoice($client['id'], $lineItems, $qrCodeUrl, $sendEmail);

        $sale->transaction_reference = $invoice['id'];
        $sale->payment_amount = $invoice['amount'];
        $sale->save();
        
        if ($sendEmail) {
            return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);    
        } else {
            return redirect($invoice['invitations'][0]['link']);
        }
    }

    private function paymentUrlCheckout($subdomain, $sale, $event)
    {
        $user = $event->user;
        
        return redirect($user->payment_url);
    }

    private function cashCheckout($subdomain, $sale, $event)
    {
        return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
    }

    public function success($subdomain, $sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $event = $sale->event;

        $stripe = new StripeClient(config('services.stripe.key'));
        $session = $stripe->checkout->sessions->retrieve(request()->session_id, [], [
            'stripe_account' => $sale->event->user->stripe_account_id,
        ]);


        if ($session->payment_status === 'paid') {            
            $sale->status = 'paid';
        }

        $sale->transaction_reference = $session->payment_intent;
        $sale->save();

        return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
    }

    public function cancel($subdomain, $sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $sale->status = 'cancelled';
        $sale->save();

        $event = $sale->event;
        
        $redirectUrl = UrlUtils::appendQueryParameters(
            $event->getGuestUrl($subdomain),
            ['tickets' => 'true']
        );

        return redirect($redirectUrl);
    }

    public function paymentUrlSuccess($sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $event = $sale->event;
        $user = $event->user;

        // Verify the secret from the URL
        $secret = request()->query('secret');
        if ($secret !== $user->payment_secret) {
            abort(403, 'Invalid secret');
        }

        // Mark the sale as paid
        $sale->status = 'paid';
        $sale->transaction_reference = __('messages.manual_payment');
        $sale->save();

        return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
    }

    public function paymentUrlCancel($sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $sale->status = 'cancelled';
        $sale->save();

        $event = $sale->event;
        
        $redirectUrl = UrlUtils::appendQueryParameters(
            $event->getGuestUrl($sale->subdomain),
            ['tickets' => 'true']
        );

        return redirect($redirectUrl);
    }

    public function scan()
    {
        return view('ticket.scan');
    }

    public function scanned($eventId, $secret)
    {
        $user = auth()->user();
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));

        if (! $event) {
            return response()->json(['error' => __('messages.this_ticket_is_not_valid')], 200);
        }

        $sale = Sale::where('event_id', $event->id)
                    ->where('secret', $secret)
                    ->first();

        if (! $sale) {
            return response()->json(['error' => __('messages.this_ticket_is_not_valid')], 200);
        }

        if (! $user->canEditEvent($event)) {
            return response()->json(['error' => __('messages.you_are_not_authorized_to_scan_this_ticket')], 200);
        }
        
        if (Carbon::parse($sale->event_date)->format('Y-m-d') !== now()->format('Y-m-d')) {
            return response()->json(['error' => __('messages.this_ticket_is_not_valid_for_today')], 200);
        }
        
        if ($sale->status == 'unpaid') {
            return response()->json(['error' => __('messages.this_ticket_is_not_paid')], 200);
        } else if ($sale->status == 'cancelled') {
            return response()->json(['error' => __('messages.this_ticket_is_cancelled')], 200);
        } else if ($sale->status == 'refunded') {
            return response()->json(['error' => __('messages.this_ticket_is_refunded')], 200);
        }

        $data = new \stdClass();
        $data->attendee = $sale->name;
        $data->event = $event->name;
        $data->date = $event->localStartsAt(true, $sale->event_date);
        $data->tickets = [];

        foreach ($sale->saleTickets as $saleTicket) {
            $data->tickets[] = [
                'type' => $saleTicket->ticket->type,
                'seats' => json_decode($saleTicket->seats, true),
            ];
        }

        foreach ($sale->saleTickets as $saleTicket) {
            $seats = $saleTicket->seats;
            if ($seats) {
                $seats = json_decode($seats, true);
                foreach ($seats as $key => $value) {
                    if (! $value) {
                        $seats[$key] = time();
                    }
                }
                $saleTicket->seats = json_encode($seats);
                $saleTicket->save();
            }
        }
        
        return response()->json($data);
    }

    public function qrCode($eventId, $secret)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->firstOrFail();

        $url = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $secret]);

        $qrCode = QrCode::create($url)            
            ->setSize(200)
            ->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        header('Content-Type: ' . $result->getMimeType());
            
        echo $result->getString();

        exit;
    }

    public function view($eventId, $secret, AppleWalletService $appleWalletService, GoogleWalletService $googleWalletService)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->firstOrFail();
        $role = $event->role();

        $appleWalletUrl = $appleWalletService->isAvailableForSale($sale)
            ? route('ticket.wallet.apple', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret])
            : null;
        $googleWalletUrl = $googleWalletService->isAvailableForSale($sale)
            ? route('ticket.wallet.google', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret])
            : null;

        return view('ticket.view', compact('event', 'sale', 'role', 'appleWalletUrl', 'googleWalletUrl'));
    }

    public function appleWallet($eventId, $secret, AppleWalletService $appleWalletService)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->firstOrFail();

        if (! $appleWalletService->isAvailableForSale($sale)) {
            abort(404);
        }

        try {
            $pass = $appleWalletService->generateTicketPass($sale);
        } catch (\Throwable $exception) {
            report($exception);

            abort(500, __('messages.unable_to_generate_wallet_pass'));
        }

        $filename = Str::slug($event->name . '-' . $sale->id) . '.pkpass';

        $passLength = strlen($pass);

        return response($pass, 200, [
            'Content-Type' => 'application/vnd.apple.pkpass',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Content-Length' => (string) $passLength,
        ]);
    }

    public function googleWallet($eventId, $secret, GoogleWalletService $googleWalletService)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->firstOrFail();

        if (! $googleWalletService->isAvailableForSale($sale)) {
            abort(404);
        }

        try {
            $link = $googleWalletService->createSaveLink($sale);
        } catch (\Throwable $exception) {
            report($exception);

            abort(500, __('messages.unable_to_generate_wallet_pass'));
        }

        return redirect()->away($link);
    }

    public function handleAction(Request $request, $sale_id)
    {
        $request->validate([
            'action' => ['required', Rule::in(self::SALE_ACTIONS)],
        ]);

        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $user = auth()->user();

        if ($user->id != $sale->event->user_id) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        $this->processSaleAction($sale, $request->action, $user);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('messages.action_completed'));
    }

    public function handleBulkAction(Request $request)
    {
        $validated = $request->validate([
            'sale_ids' => ['required', 'array'],
            'sale_ids.*' => ['string'],
            'action' => ['required', Rule::in(self::SALE_ACTIONS)],
        ]);

        $user = auth()->user();

        $decodedIds = collect($validated['sale_ids'])
            ->map(function ($encodedId) {
                try {
                    return UrlUtils::decodeId($encodedId);
                } catch (\Throwable $exception) {
                    return null;
                }
            })
            ->filter()
            ->values();

        if ($decodedIds->isEmpty()) {
            return response()->json(['error' => __('messages.no_sales_selected')], 422);
        }

        $sales = Sale::with('event')
            ->whereIn('id', $decodedIds)
            ->whereHas('event', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        if ($sales->isEmpty()) {
            return response()->json(['error' => __('messages.no_sales_available_for_action')], 403);
        }

        $sales->each(function (Sale $sale) use ($validated, $user) {
            $this->processSaleAction($sale, $validated['action'], $user);
        });

        return response()->json([
            'success' => true,
            'processed' => $sales->count(),
        ]);
    }

    private function processSaleAction(Sale $sale, string $action, User $user): void
    {
        switch ($action) {
            case 'mark_paid':
                if ($sale->status === 'unpaid') {
                    $previousStatus = $sale->status;
                    $sale->status = 'paid';
                    $sale->transaction_reference = __('messages.manual_payment');
                    $sale->save();

                    Log::info('Sale manually marked as paid', [
                        'sale_id' => $sale->id,
                        'event_id' => $sale->event_id,
                        'previous_status' => $previousStatus,
                        'actor_id' => $user->id,
                    ]);
                } else {
                    Log::info('Sale mark_paid request ignored', [
                        'sale_id' => $sale->id,
                        'event_id' => $sale->event_id,
                        'current_status' => $sale->status,
                        'actor_id' => $user->id,
                    ]);
                }
                break;

            case 'refund':
                if ($sale->status === 'paid') {
                    $sale->status = 'refunded';
                    $sale->save();
                }
                break;

            case 'cancel':
                if (in_array($sale->status, ['unpaid', 'paid'])) {
                    $sale->status = 'cancelled';
                    $sale->save();
                }
                break;

            case 'delete':
                if (! $sale->is_deleted) {
                    if (! in_array($sale->status, ['cancelled', 'refunded', 'expired'])) {
                        $sale->loadMissing('saleTickets.ticket');

                        foreach ($sale->saleTickets as $saleTicket) {
                            if ($saleTicket->ticket) {
                                $saleTicket->ticket->updateSold($sale->event_date, -$saleTicket->quantity);
                            }
                        }
                    }

                    $sale->is_deleted = true;
                    $sale->save();
                }
                break;
        }
    }

    private function sendTicketSaleNotifications(Sale $sale): void
    {
        $sale->loadMissing(['saleTickets.ticket', 'event.roles.members', 'event.venue.members', 'event.creatorRole.members', 'event.user']);

        $event = $sale->event;
        $contextRole = $event->venue ?: $event->creatorRole;

        Log::info('Dispatching ticket sale notifications', [
            'sale_id' => $sale->id,
            'event_id' => $event->id,
            'purchaser_email' => $sale->email,
            'status' => $sale->status,
        ]);

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
    }

    public function release()
    {
        $requestSecret = request()->get('secret');
        $serverSecret = config('app.cron_secret');
        
        if (!$serverSecret || !$requestSecret || !hash_equals($serverSecret, $requestSecret)) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        \Artisan::call('app:release-tickets');

        return response()->json(['success' => true]);
    }
}
