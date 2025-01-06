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
                ]);
            }
        }
        
        $event = $sale->event;

        switch ($event->payment_method) {
            case 'stripe':
                return $this->stripeCheckout($subdomain, $sale, $event, $date);
            case 'invoiceninja':
                return $this->invoiceninjaCheckout($subdomain, $sale, $event, $date);
            default:
                return $this->cashCheckout($subdomain, $sale, $event, $date);
        }
    }

    private function stripeCheckout($subdomain, $sale, $event, $date)
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
            'sale_id' => $sale->id, 
            'subdomain' => $subdomain, 
            'date' => $date
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
                'success_url' => route('checkout.success', $data),
                'cancel_url' => route('checkout.cancel', $data),
            ],
            [
                'stripe_account' => $event->user->stripe_account_id,
            ],
        );

        return redirect($session->url);
    }

    private function invoiceninjaCheckout($subdomain, $sale, $event, $date)
    {
        //
    }

    private function cashCheckout($subdomain, $sale, $event, $date)
    {
        //
    }

    public function success($subdomain, $sale_id)
    {
        dd($subdomain, $sale_id);
    }

    public function cancel($subdomain, $sale_id)
    {
        $sale = Sale::find($sale_id);
        $sale->status = 'cancelled';
        $sale->save();

        return redirect()->route('event.show', ['subdomain' => $subdomain, 'event_id' => $sale->event_id]);
    }
}