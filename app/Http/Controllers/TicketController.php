<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Stripe\StripeClient;

class TicketController extends Controller
{
    public function checkout(Request $request, $subdomain)
    {
        $date = $request->date;

        $sale = new Sale();
        $sale->fill($request->all());
        $sale->event_id = UrlUtils::decodeId($request->event_id);
        $sale->secret = Str::random(8);
        $sale->save();

        foreach($request->tickets as $ticket) {
            if ($ticket['quantity'] > 0) {
                $sale->saleTickets()->create([
                    'sale_id' => $sale->id,
                    'ticket_id' => UrlUtils::decodeId($ticket['id']),
                    'quantity' => $ticket['quantity'],
                    'date' => $date,
                ]);
            }
        }
        
        $event = $sale->event;

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
        //
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
}
