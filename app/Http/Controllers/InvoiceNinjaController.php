<?php

namespace App\Http\Controllers;

class InvoiceNinjaController extends Controller
{
    public function unlink()
    {
        $user = auth()->user();
        $user->invoiceninja_api_key = null;
        $user->invoiceninja_company_name = null;
        $user->save();

        return redirect()->back()->with('success', __('messages.invoiceninja_unlinked'));
    }

    public function webhook(Request $request)
    {
        $endpoint_secret = config('services.invoiceninja.webhook_secret');

        $request = $request->getContent();
        $event = json_decode($request);

        $sale = Sale::where('transaction_reference', $paymentIntent->id)->firstOrFail();
        $sale->status = 'paid';
        $sale->save();

        return response()->json(['status' => 'success']);
    }
}