<?php

namespace Tests\Feature;

use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Sale;
use App\Services\AppointmentService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Paid appointment types on a gateway other than Stripe or the payment URL, which go through the
 * gateway registry: offered in the editor once connected, checked for currency and price, started
 * by the driver from the pay step, and landed back on the booking's manage page.
 *
 * PayPal stands in for a gateway that redirects, Payfast for one that answers with its own page.
 * PayPal's API is faked with one closure, for the reason PayPalCheckoutTest gives.
 */
class AppointmentGatewayPaymentTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const APPROVE_URL = 'https://www.sandbox.paypal.com/checkoutnow?token=ORDER123';

    private $owner;

    private $role;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(function ($request) {
            $url = $request->url();

            if (str_contains($url, '/v1/oauth2/token')) {
                return Http::response(['access_token' => 'fake-token', 'expires_in' => 32400]);
            }

            if (str_ends_with($url, '/capture') || str_contains($url, '/v2/checkout/orders/')) {
                return Http::response($this->capturedOrder());
            }

            if (str_contains($url, '/v2/checkout/orders')) {
                return Http::response([
                    'id' => 'ORDER123',
                    'status' => 'CREATED',
                    'links' => [['rel' => 'payer-action', 'href' => self::APPROVE_URL]],
                ]);
            }

            return Http::response([], 404);
        });

        $this->owner = $this->createOwner();
        $this->owner->forceFill([
            'paypal_client_id' => 'AaBbCc-client-id',
            'paypal_client_secret' => 'super-secret',
            'paypal_sandbox' => true,
        ])->save();

        $this->role = $this->createRole($this->owner, 'talent', ['timezone' => 'America/New_York']);
    }

    private function paidType(array $attrs = []): AppointmentType
    {
        return $this->createAppointmentType($this->role, array_merge([
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]),
            'price' => 50, 'currency_code' => 'USD', 'payment_method' => 'paypal',
        ], $attrs));
    }

    private function book(AppointmentType $type)
    {
        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = app(AppointmentService::class)->availableSlots($type, $from, 1);
        $slot = $slots['days'][array_key_first($slots['days'])][0]['utc'];

        return $this->postJson(route('appointments.book.store', ['subdomain' => $this->role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York',
        ]);
    }

    private function sale(): Sale
    {
        return Sale::where('email', 'jane@gmail.com')->firstOrFail();
    }

    /** @return array<string, string> */
    private function bookingParams(Sale $sale): array
    {
        return ['event_id' => UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret];
    }

    /** @return array<string, mixed> */
    private function capturedOrder(): array
    {
        $sale = Sale::where('email', 'jane@gmail.com')->first();

        return [
            'id' => 'ORDER123',
            'status' => 'COMPLETED',
            'purchase_units' => [['payments' => ['captures' => [[
                'id' => 'CAPTURE000000001A',
                'status' => 'COMPLETED',
                'custom_id' => $sale ? UrlUtils::encodeId($sale->id) : '',
                'amount' => ['currency_code' => 'USD', 'value' => '50.00'],
            ]]]]],
        ];
    }

    // ---------------------------------------------------------------- setup

    public function test_a_connected_gateway_is_offered_in_the_editor(): void
    {
        $this->actingAs($this->owner)
            ->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'appointments', 'new' => 1]))
            ->assertOk()
            ->assertSee('value="paypal"', false)
            // Not connected, so not offered.
            ->assertDontSee('value="payfast"', false);
    }

    public function test_a_registered_gateway_can_be_saved_and_an_unknown_one_cannot(): void
    {
        $type = $this->paidType(['payment_method' => 'cash']);
        $payload = fn (string $method) => [
            'name' => 'Consultation', 'duration_minutes' => 30, 'location_type' => 'in_person',
            'price' => 50, 'currency_code' => 'USD', 'payment_method' => $method,
            'weekly_windows' => $type->weekly_windows, 'is_active' => 1,
        ];
        $url = route('appointments.update', ['subdomain' => $this->role->subdomain, 'hash' => $type->hashedId()]);

        $this->actingAs($this->owner)->put($url, $payload('paypal'))->assertSessionHasNoErrors();
        $this->assertSame('paypal', $type->fresh()->payment_method);

        $this->actingAs($this->owner)->put($url, $payload('bitcoin'))->assertSessionHasErrors('payment_method');
        $this->assertSame('paypal', $type->fresh()->payment_method);
    }

    public function test_availability_asks_the_gateway(): void
    {
        $type = $this->paidType();
        $this->assertTrue($type->paymentMethodAvailable());
        $this->assertTrue($type->isBookable());
        $this->assertSame(1, $type->expireHours());

        // Payfast settles ZAR only, and this owner has not connected it anyway.
        $this->assertFalse($this->paidType(['payment_method' => 'payfast'])->paymentMethodAvailable());

        $this->owner->forceFill(['paypal_client_id' => null, 'paypal_client_secret' => null])->save();
        $this->assertFalse($type->fresh()->paymentMethodAvailable());
        $this->assertFalse($type->fresh()->isBookable());
    }

    public function test_pays_online_covers_every_gateway_but_cash(): void
    {
        foreach (['stripe', 'payment_url', 'paypal', 'payfast', 'invoiceninja'] as $method) {
            $this->assertTrue(AppointmentType::paysOnline($method), $method);
        }

        foreach (['cash', null, 'rsvp', 'not-a-gateway'] as $method) {
            $this->assertFalse(AppointmentType::paysOnline($method), (string) $method);
        }
    }

    // -------------------------------------------------------------- booking

    public function test_booking_hands_the_guest_to_the_pay_step(): void
    {
        $this->book($this->paidType())->assertOk();

        $sale = $this->sale();

        $this->assertSame('unpaid', $sale->status);
        $this->assertSame('paypal', $sale->payment_method);
        $this->assertNull($sale->confirmed_at, 'not confirmed until paid');
        $this->assertSame(1, (int) $sale->event->expire_unpaid_tickets);
    }

    public function test_the_booking_response_names_the_pay_step(): void
    {
        $response = $this->book($this->paidType())->assertOk();

        $this->assertSame(route('appointments.pay', $this->bookingParams($this->sale())), $response->json('pay_url'));
        $this->assertNull($response->json('redirect_url'));
    }

    public function test_the_pay_step_redirects_to_a_redirecting_gateway(): void
    {
        $this->book($this->paidType());
        $sale = $this->sale();

        $this->post(route('appointments.pay', $this->bookingParams($sale)))->assertRedirect(self::APPROVE_URL);

        $order = collect(Http::recorded())
            ->first(fn ($pair) => $pair[0]->method() === 'POST' && str_ends_with($pair[0]->url(), '/v2/checkout/orders'))[0]->data();
        $this->assertSame('50.00', $order['purchase_units'][0]['amount']['value']);
        $this->assertSame(UrlUtils::encodeId($sale->id), $order['purchase_units'][0]['custom_id']);
    }

    public function test_the_pay_step_renders_a_gateway_that_answers_with_a_page(): void
    {
        $this->owner->forceFill([
            'payfast_merchant_id' => '10000100',
            'payfast_merchant_key' => '46f0cd694581a',
            'payfast_passphrase' => 'test-passphrase',
            'payfast_sandbox' => true,
        ])->save();
        $type = $this->paidType(['payment_method' => 'payfast', 'currency_code' => 'ZAR', 'price' => 150]);

        $this->book($type)->assertOk();
        $sale = $this->sale();

        $this->post(route('appointments.pay', $this->bookingParams($sale)))
            ->assertOk()
            ->assertSee('https://sandbox.payfast.co.za/eng/process', escape: false)
            ->assertSee('name="amount" value="150.00"', escape: false)
            ->assertSee('name="m_payment_id" value="'.UrlUtils::encodeId($sale->id).'"', escape: false);
    }

    public function test_the_gateway_return_settles_confirms_and_lands_on_the_manage_page(): void
    {
        $this->book($this->paidType());
        $sale = $this->sale();
        $this->post(route('appointments.pay', $this->bookingParams($sale)));

        $this->get(route('payments.return', [
            'gateway' => 'paypal',
            'sale_id' => UrlUtils::encodeId($sale->id),
            'secret' => $sale->secret,
            'token' => 'ORDER123',
        ]))->assertRedirect(route('appointments.manage', $this->bookingParams($sale)).'?new=1');

        $sale->refresh();
        $this->assertSame('paid', $sale->status);
        $this->assertSame('CAPTURE000000001A', $sale->transaction_reference);
        $this->assertNotNull($sale->confirmed_at);
    }

    public function test_cancelling_at_the_gateway_releases_the_slot_and_returns_to_the_manage_page(): void
    {
        $this->book($this->paidType());
        $sale = $this->sale();

        $this->get(route('payments.cancel', [
            'gateway' => 'paypal',
            'sale_id' => UrlUtils::encodeId($sale->id),
            'secret' => $sale->secret,
        ]))->assertRedirect(route('appointments.manage', $this->bookingParams($sale)));

        $this->assertSame('expired', $sale->fresh()->status);
        $this->assertTrue((bool) Event::find($sale->event_id)->is_cancelled);
    }

    public function test_an_unpaid_online_booking_shows_awaiting_payment(): void
    {
        $this->book($this->paidType());
        $sale = $this->sale();

        $this->get(route('appointments.manage', $this->bookingParams($sale)))
            ->assertOk()
            ->assertSee(__('messages.appointments_awaiting_payment'))
            ->assertSee(route('appointments.pay', $this->bookingParams($sale)), false);
    }
}
