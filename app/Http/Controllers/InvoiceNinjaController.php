<?php

namespace App\Http\Controllers;

use App\Utils\InvoiceNinja;

class InvoiceNinjaController extends Controller
{
    public function unlink()
    {
        $user = auth()->user();

        $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key);
        $company = $invoiceNinja->getCompany();
        
        foreach ($company['webhooks'] as $webhook) {
            if ($webhook['target_url'] == route('invoiceninja.webhook', ['secret' => $user->invoiceninja_webhook_secret])) {
                $invoiceNinja->deleteWebhook($webhook['id']);
            }
        }

        $user->invoiceninja_api_key = null;
        $user->invoiceninja_company_name = null;
        $user->invoiceninja_webhook_secret = null;
        $user->save();

        return redirect()->back()->with('success', __('messages.invoiceninja_unlinked'));
    }

    public function webhook(Request $request, $secret)
    {
        $request = $request->getContent();
        $event = json_decode($request);

        // get invoice id
        // check webhook secret 

        $sale = Sale::where('transaction_reference', $paymentIntent->id)->firstOrFail();
        $sale->status = 'paid';
        $sale->save();

        return response()->json(['status' => 'success']);
    }
}