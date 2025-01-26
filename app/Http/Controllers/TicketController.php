<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Sale;
use App\Models\Event;
use App\Models\SaleTicket;
use App\Utils\UrlUtils;
use Stripe\StripeClient;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use App\Utils\InvoiceNinja;

class TicketController extends Controller
{
    public function tickets()
    {
        $user = auth()->user();

        $sales = Sale::with('event', 'saleTickets')
            ->where('user_id', $user->id)
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

        $sales = Sale::with('event', 'saleTickets')
            ->whereIn('status', ['paid', 'unpaid']) // TODO: remove this to show all sales
            ->whereHas('event', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('ticket.sales', compact('sales'));
    }

    public function checkout(Request $request, $subdomain)
    {
        $event = Event::find(UrlUtils::decodeId($request->event_id));
        $user = auth()->user();

        if (! $user && $request->create_account) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'timezone' => $event->user->timezone,
                'language_code' => $event->user->language_code,
            ]);
        }

        // Check ticket availability
        foreach($request->tickets as $ticket) {
            if ($ticket['quantity'] > 0) {
                $ticketModel = $event->tickets()->find(UrlUtils::decodeId($ticket['id']));
                
                if (! $ticketModel) {
                    return back()->with('error', __('messages.ticket_not_found'));
                }

                if ($ticketModel->quantity > 0) {
                    $sold = json_decode($ticketModel->sold, true);
                    $soldCount = $sold[$request->event_date] ?? 0;
                    $remainingTickets = $ticketModel->quantity - $soldCount;

                    if ($ticket['quantity'] > $remainingTickets) {
                        return back()->with('error', __('messages.tickets_not_available'));
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
        $sale->save();

        foreach($request->tickets as $ticket) {
            if ($ticket['quantity'] > 0) {
                $sale->saleTickets()->create([
                    'sale_id' => $sale->id,
                    'ticket_id' => UrlUtils::decodeId($ticket['id']),
                    'quantity' => $ticket['quantity'],
                    'seats' => json_encode(array_fill(1, $ticket['quantity'], null)),
                ]);
            }
        }

        $total = $sale->calculateTotal();

        $sale->payment_amount = $total;
        $sale->save();
        
        if ($total == 0) {
            $sale->status = 'paid';
            $sale->save();

            return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
        } else {
            switch ($event->payment_method) {
                case 'stripe':
                    return $this->stripeCheckout($subdomain, $sale, $event);
                case 'invoiceninja':
                    return $this->invoiceninjaCheckout($subdomain, $sale, $event);
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

    private function cashCheckout($subdomain, $sale, $event)
    {
        return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
    }

    public function success($subdomain, $sale_id)
    {
        $sale = Sale::find(UrlUtils::decodeId($sale_id));
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

        return redirect($event->getGuestUrl($subdomain, $sale->event_date));
    }

    public function cancel($subdomain, $sale_id)
    {
        $sale = Sale::find(UrlUtils::decodeId($sale_id));
        $sale->status = 'cancelled';
        $sale->save();

        $event = $sale->event;
        
        return redirect($event->getGuestUrl($subdomain, $sale->event_date) . '&tickets=true');
    }

    public function scan()
    {
        return view('ticket.scan');
    }

    public function scanned($eventId, $secret)
    {
        $user = auth()->user();
        $event = Event::find(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->firstOrFail();

        if (! $user->canEditEvent($event)) {
            return response()->json(['error' => 'You are not authorized to scan this ticket.'], 200);
        }
        
        if (Carbon::parse($sale->event_date)->format('Y-m-d') !== now()->format('Y-m-d')) {
            return response()->json(['error' => 'This ticket is not valid for today.'], 200);
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

    public function view($eventId, $secret)
    {
        $event = Event::find(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->firstOrFail();
        $role = $event->role();        

        return view('ticket.view', compact('event', 'sale', 'role'));
    }

}
