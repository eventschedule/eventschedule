<?php

namespace App\Http\Controllers;

use App\Utils\InvoiceNinja;
use App\Models\User;
use App\Models\Sale;
use Illuminate\Http\Request;

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
        $payload = json_decode($request->getContent(), true);    
        $invoiceId = $payload['paymentables'][0]['invoice_id'];
        
        if (! $invoiceId) {
            return response()->json(['status' => 'error', 'message' => 'No invoice_id found'], 400);
        }

        $sale = Sale::where('payment_method', 'invoiceninja')
            ->where('transaction_reference', $invoiceId)
            ->firstOrFail();

        if ($sale->event->user->invoiceninja_webhook_secret != $secret) {
            return response()->json(['status' => 'error', 'message' => 'Invalid webhook secret'], 400);
        }

        $sale->status = 'paid';
        $sale->save();

        return response()->json(['status' => 'success']);
    }
}