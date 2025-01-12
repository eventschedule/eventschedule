<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Sale;
use App\Models\Event;
use App\Utils\UrlUtils;
use Stripe\StripeClient;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use App\Utils\InvoiceNinja;
class TicketController extends Controller
{
    public function checkout(Request $request, $subdomain)
    {
        $event = Event::find(UrlUtils::decodeId($request->event_id));

        $sale = new Sale();
        $sale->fill($request->all());
        $sale->event_id = $event->id;
        $sale->secret = Str::random(16);
        $sale->payment_method = $event->payment_method;
        $sale->save();

        foreach($request->tickets as $ticket) {
            if ($ticket['quantity'] > 0) {
                $sale->saleTickets()->create([
                    'sale_id' => $sale->id,
                    'ticket_id' => UrlUtils::decodeId($ticket['id']),
                    'seats' => json_encode(array_fill(1, $ticket['quantity'], null)),
                ]);
            }
        }

        switch ($event->payment_method) {
            case 'stripe':
                return $this->stripeCheckout($subdomain, $sale, $event);
            case 'invoiceninja':
                return $this->invoiceninjaCheckout($subdomain, $sale, $event);
            default:
                return $this->cashCheckout($subdomain, $sale, $event);
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
                'quantity' => $saleTicket->quantity(),
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
        $invoiceNinja = new InvoiceNinja($event->user->invoiceninja_api_key);
        $client = $invoiceNinja->createClient($sale->name, $sale->email, $event->ticket_currency_code);

        $lineItems = [];
        foreach ($sale->saleTickets as $saleTicket) {
            $lineItems[] = [
                'product_key' => $saleTicket->ticket->type,
                'notes' => $saleTicket->ticket->description,
                'quantity' => $saleTicket->quantity(),
                'cost' => $saleTicket->ticket->price,
            ];
        }

        $qrCodeUrl = route('ticket.qr_code', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
        $invoice = $invoiceNinja->createInvoice($client['id'], $lineItems, $qrCodeUrl);

        $sale->transaction_reference = $invoice['id'];
        $sale->save();

        return redirect($invoice['invitations'][0]['link']);
    }

    private function cashCheckout($subdomain, $sale, $event)
    {
        //
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

    public function qrCode($eventId, $secret)
    {
        $event = Event::find(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->first();

        if (! $sale) {
            return abort(404);
        }

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
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->first();

        return view('ticket.view', compact('event', 'sale'));
    }

}
