<?php

namespace App\Http\Controllers;

class TicketController extends Controller
{
    public function checkout(Request $request, $subdomain)
    {
        dd($request->all());
    }
}