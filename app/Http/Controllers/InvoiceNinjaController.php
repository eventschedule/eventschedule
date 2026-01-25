<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use App\Utils\InvoiceNinja;
use Illuminate\Http\Request;

class InvoiceNinjaController extends Controller
{
    public function unlink()
    {
        $user = auth()->user();

        $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);
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

        return redirect()->back()->with('message', __('messages.invoiceninja_unlinked'));
    }

    public function webhook(Request $request, $secret = null)
    {
        // Prefer Authorization header over URL parameter for security
        // (URL secrets appear in logs and Referer headers)
        $headerSecret = $request->header('X-Webhook-Secret') ?? $request->header('Authorization');
        if ($headerSecret) {
            // Remove "Bearer " prefix if present
            $headerSecret = preg_replace('/^Bearer\s+/i', '', $headerSecret);
            $secret = $headerSecret;
        }

        if (! $secret) {
            return response()->json(['status' => 'error', 'message' => 'Missing webhook secret'], 400);
        }

        // Find user with matching webhook secret using constant-time comparison
        // to prevent timing attacks that could enumerate valid secrets
        $user = null;
        $users = User::whereNotNull('invoiceninja_webhook_secret')->get(['id', 'invoiceninja_webhook_secret']);
        foreach ($users as $candidate) {
            if (hash_equals($candidate->invoiceninja_webhook_secret, $secret)) {
                $user = User::find($candidate->id);
                break;
            }
        }

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Invalid webhook secret'], 400);
        }

        $payload = json_decode($request->getContent(), true);

        if (empty($payload['paymentables']) || count($payload['paymentables']) == 0) {
            return response()->json(['status' => 'error', 'message' => 'No paymentables found'], 400);
        }

        $invoiceId = $payload['paymentables'][0]['invoice_id'];

        if (! $invoiceId) {
            return response()->json(['status' => 'error', 'message' => 'No invoice_id found'], 400);
        }

        $sale = Sale::where('payment_method', 'invoiceninja')
            ->where('transaction_reference', $invoiceId)
            ->whereHas('event', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        if (! $sale) {
            return response()->json(['status' => 'error', 'message' => 'Sale not found'], 400);
        }

        // Idempotency check: only process if not already paid
        if ($sale->status !== 'paid') {
            $sale->payment_amount = $payload['paymentables'][0]['amount'];
            $sale->status = 'paid';
            $sale->save();
        }

        return response()->json(['status' => 'success']);
    }
}
