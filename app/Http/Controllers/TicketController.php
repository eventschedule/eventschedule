<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use Illuminate\Support\Str;
use App\Utils\UrlUtils;

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
        
        dd($sale);
    }
}
