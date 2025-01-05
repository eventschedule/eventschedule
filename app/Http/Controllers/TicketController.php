<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use Illuminate\Support\Str;
use App\Utils\UrlUtils;
use Stripe\StripeClient;

class TicketController extends Controller
{
    public function checkout(Request $request, $subdomain)
    {
        $sale = new Sale();
        $sale->fill($request->all());
        $sale->event_id = UrlUtils::decodeId($request->event_id);
        $sale->secret = Str::random(8);
        $sale->save();

        foreach($request->tickets as $ticket) {
            if ($ticket['quantity'] > 0) {
                $sale->tickets()->create([
                    'sale_id' => $sale->id,
                    'ticket_id' => UrlUtils::decodeId($ticket['id']),
                    'quantity' => $ticket['quantity'],
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
        foreach ($sale->tickets as $ticket) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $event->ticket_currency_code,
                    'product_data' => [
                        'name' => $ticket->ticket->type ? $ticket->ticket->type : __('messages.tickets'),
                        ...$ticket->ticket->description ? ['description' => $ticket->ticket->description] : [],
                    ],
                    'unit_amount' => $ticket->ticket->price * 100,
                ],
                'quantity' => $ticket->quantity,
            ];
        }

        $stripe = new StripeClient(config('services.stripe.key'));
        $session = $stripe->checkout->sessions->create(
            [
                'line_items' => $lineItems,                
                //'payment_intent_data' => ['application_fee_amount' => 123],
                'mode' => 'payment',
                'customer_email' => $sale->email,
                'metadata' => [
                    'customer_name' => $sale->name,
                ],
                'success_url' => route('checkout.success', ['sale_id' => $sale->id, 'subdomain' => $subdomain]),
                'cancel_url' => route('checkout.cancel', ['sale_id' => $sale->id, 'subdomain' => $subdomain]),
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
        dd($subdomain, $sale_id);
    }

    public function cancel($subdomain, $sale_id)
    {
        dd($subdomain, $sale_id);
    }
}