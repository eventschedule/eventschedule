<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\Payments\PaymentGatewayManager;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The one webhook endpoint every gateway calls back on.
 *
 * The {gateway} segment selects the driver, so a new gateway inherits a working callback URL instead
 * of needing its own route, its own controller and its own CSRF exemption - which is how the app
 * ended up with four bespoke webhook endpoints before this existed.
 *
 * Authentication is the driver's job, not this class's: every gateway proves itself differently (a
 * signed header, an HMAC over the raw body, a shared secret, a source-IP check), and there is nothing
 * useful to verify generically. What this does guarantee is that a driver is never handed a sale that
 * does not exist, or one belonging to a different gateway.
 */
class PaymentWebhookController extends Controller
{
    public function __construct(private PaymentGatewayManager $gateways) {}

    public function handle(Request $request, string $gateway, ?string $sale_id = null): Response
    {
        $driver = $this->gateways->get($gateway);

        if (! $driver) {
            abort(404);
        }

        $sale = null;

        if ($sale_id !== null) {
            $sale = Sale::find(UrlUtils::decodeId($sale_id));

            if (! $sale) {
                // 404 rather than 200: an unknown sale is not something a retry can fix, but it is
                // also not something to silently swallow, because it means a live payment has
                // nowhere to land and the provider's delivery log is the only record of it.
                abort(404);
            }

            // A callback addressed to a sale that was not bought on this rail is either a
            // misconfiguration or an attempt to have one gateway settle another's sale. Either way
            // the driver must not see it: its signature check would pass against its own credentials
            // while the sale belongs to somebody else's account.
            if ($sale->payment_method !== $gateway) {
                \Log::warning('Payment webhook gateway mismatch', [
                    'sale_id' => $sale->id,
                    'sale_payment_method' => $sale->payment_method,
                    'webhook_gateway' => $gateway,
                ]);

                abort(404);
            }
        }

        return $driver->handleWebhook($request, $sale);
    }
}
