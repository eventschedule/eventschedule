<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\AuditService;
use App\Services\Payments\PaymentGatewayDriver;
use App\Services\Payments\PaymentGatewayManager;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

/**
 * The buyer-facing and owner-facing endpoints shared by every gateway: where a payer lands coming
 * back, and how an owner connects or disconnects credentials.
 *
 * Together with PaymentWebhookController this is the whole reason a new gateway needs no routes of
 * its own. connect/disconnect in particular are generic because a driver declares its fields
 * (PaymentGatewayDriver::credentialFields), so there is nothing gateway-shaped left in the handler.
 */
class PaymentGatewayController extends Controller
{
    public function __construct(private PaymentGatewayManager $gateways) {}

    /**
     * Buyer returning from a successful payment.
     *
     * Deliberately not where a sale is marked paid by default: a return URL is a browser redirect,
     * so it can be replayed, tampered with, or simply never reached when the buyer closes the tab.
     * The gateway's own callback is the authority. A driver that does learn something trustworthy
     * here overrides handleReturn().
     */
    public function handleReturn(Request $request, string $gateway, string $sale_id): Response
    {
        [$driver, $sale] = $this->resolve($gateway, $sale_id);

        return $driver->handleReturn($request, $sale);
    }

    /**
     * Buyer abandoned the payment.
     */
    public function handleCancel(Request $request, string $gateway, string $sale_id): Response
    {
        [$driver, $sale] = $this->resolve($gateway, $sale_id);

        return $driver->handleCancel($request, $sale);
    }

    /**
     * Save credentials for one gateway.
     *
     * No honeypot here on purpose: this form is authenticated, and the rule against honeypots on
     * signed-in forms exists because a password manager will happily fill the decoy field and lock
     * the owner out of their own settings.
     */
    public function connect(Request $request, string $gateway): Response
    {
        $driver = $this->gateways->get($gateway);

        if (! $driver || ! $driver->credentialFields()) {
            abort(404);
        }

        if (is_demo_mode()) {
            return $this->backToSettings(__('messages.demo_mode_restriction'), isError: true);
        }

        $validated = $request->validate($driver->credentialRules());

        try {
            $driver->saveCredentials($request->user(), $validated);
        } catch (\Illuminate\Database\QueryException $e) {
            // Never surface a database message to an owner; it says nothing they can act on and can
            // leak schema detail.
            report($e);

            return $this->backToSettings(__('messages.error'), isError: true);
        }

        AuditService::log(AuditService::ADMIN_UPDATE, $request->user()->id, 'User', $request->user()->id,
            null, null, 'payment_gateway_connect:'.$gateway);

        return $this->backToSettings(__('messages.settings_saved'));
    }

    public function disconnect(Request $request, string $gateway): Response
    {
        $driver = $this->gateways->get($gateway);

        if (! $driver || ! $driver->credentialFields()) {
            abort(404);
        }

        if (is_demo_mode()) {
            return $this->backToSettings(__('messages.demo_mode_restriction'), isError: true);
        }

        $driver->disconnect($request->user());

        AuditService::log(AuditService::ADMIN_UPDATE, $request->user()->id, 'User', $request->user()->id,
            null, null, 'payment_gateway_disconnect:'.$gateway);

        return $this->backToSettings(__('messages.settings_saved'));
    }

    /**
     * @return array{0: PaymentGatewayDriver, 1: Sale}
     */
    private function resolve(string $gateway, string $sale_id): array
    {
        $driver = $this->gateways->get($gateway);

        if (! $driver) {
            abort(404);
        }

        $sale = Sale::find(UrlUtils::decodeId($sale_id));

        // Same cross-gateway guard as the webhook: a driver must never be handed another rail's
        // sale, or its own secret would be used to validate somebody else's payment.
        if (! $sale || $sale->payment_method !== $gateway) {
            abort(404);
        }

        return [$driver, $sale];
    }

    private function backToSettings(string $message, bool $isError = false): Response
    {
        return Redirect::to(route('profile.edit').'#section-payment-methods')
            ->with($isError ? 'error' : 'message', $message);
    }
}
