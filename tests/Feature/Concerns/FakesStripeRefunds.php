<?php

namespace Tests\Feature\Concerns;

use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Services\Payments\Gateways\StripeGateway;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;

/**
 * Substitutes the Stripe rail so a refund can be exercised without reaching the network.
 *
 * Needed by ANY test that refunds a sale whose transaction_reference looks like a real
 * PaymentIntent, not only tests about refunds: phpunit.xml forces placeholder Stripe keys
 * (`sk_test_fake_platform`), so the gateway always reports itself configured and the SDK throws
 * "Invalid API Key provided" the moment SaleRefundService actually calls it. There is no Stripe
 * fake in the app itself - only MetaAdsServiceFake - so this is the seam.
 */
trait FakesStripeRefunds
{
    protected function fakeStripeRefunds(): object
    {
        $fake = new class extends StripeGateway
        {
            /** @var list<array{sale_id:int, amount:float|null, key:string}> */
            public array $calls = [];

            /** DB::transactionLevel() at the moment of each call, for the no-IO-in-a-transaction check. */
            public array $transactionLevels = [];

            public ?\Throwable $throw = null;

            public function refund(Sale $sale, ?float $amount, string $idempotencyKey, ?SaleInstallment $leg = null): string
            {
                $this->calls[] = ['sale_id' => $sale->id, 'amount' => $amount, 'key' => $idempotencyKey];
                $this->transactionLevels[] = DB::transactionLevel();

                if ($this->throw) {
                    throw $this->throw;
                }

                return 're_test_'.count($this->calls);
            }
        };

        $this->app->bind(StripeGateway::class, fn () => $fake);

        // PaymentGatewayManager is a singleton and memoizes its drivers on first use, so it has to
        // be rebuilt or it keeps handing out the real gateway it resolved before the bind.
        $this->app->forgetInstance(PaymentGatewayManager::class);

        return $fake;
    }
}
