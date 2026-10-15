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

    /** What the faked PayPal reports for the capture: COMPLETED, or PENDING for a payment under review. */
    private string $captureStatus = 'COMPLETED';

    /** When true, the faked PayPal refuses to create an order (a timeout or a 5xx, as PayPalClient sees it). */
    private bool $orderFails = false;

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
                if ($this->orderFails) {
                    return Http::response(['name' => 'INTERNAL_SERVICE_ERROR'], 500);
                }

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

    private function connectPayfast(): void
    {
        $this->owner->forceFill([
            'payfast_merchant_id' => '10000100',
            'payfast_merchant_key' => '46f0cd694581a',
            'payfast_passphrase' => 'test-passphrase',
            'payfast_sandbox' => true,
        ])->save();
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
                'status' => $this->captureStatus,
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

        // Connected, each cause on its own: the currency, then the R5 floor.
        $this->connectPayfast();
        $this->assertFalse($this->paidType(['payment_method' => 'payfast', 'currency_code' => 'USD', 'price' => 150])->paymentMethodAvailable());
        $this->assertFalse($this->paidType(['payment_method' => 'payfast', 'currency_code' => 'ZAR', 'price' => 3])->paymentMethodAvailable());
        $this->assertTrue($this->paidType(['payment_method' => 'payfast', 'currency_code' => 'ZAR', 'price' => 150])->paymentMethodAvailable());

        // Stripe's floor, at booking time too: a type saved under it before the save check existed
        // is held back rather than failing at checkout with its slot held.
        config(['app.hosted' => false, 'services.stripe_platform.secret' => 'sk_test_appointments']);
        $this->assertFalse($this->paidType(['payment_method' => 'stripe', 'price' => 0.3])->paymentMethodAvailable());
        $this->assertTrue($this->paidType(['payment_method' => 'stripe', 'price' => 0.5])->paymentMethodAvailable());

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
        $params = $this->bookingParams($this->sale());

        // Paths, like the form's own bookUrl: the form posts and navigates from whichever host the
        // booking page was served on, a schedule's custom domain included.
        $this->assertSame(route('appointments.pay', $params, false), $response->json('pay_url'));
        $this->assertSame(route('appointments.manage', $params, false), $response->json('manage_url'));
        $this->assertStringStartsWith('/', $response->json('pay_url'));
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
        $this->connectPayfast();
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

    public function test_backing_out_at_the_gateway_keeps_the_hold(): void
    {
        $this->book($this->paidType());
        $sale = $this->sale();
        $manage = route('appointments.manage', $this->bookingParams($sale));

        $this->get(route('payments.cancel', [
            'gateway' => 'paypal',
            'sale_id' => UrlUtils::encodeId($sale->id),
            'secret' => $sale->secret,
        ]))->assertRedirect($manage.'?payment=cancelled');

        // Held, as a Stripe booking is: the guest can pay again or cancel, and the hold lapses on
        // its own clock.
        $this->assertSame('unpaid', $sale->fresh()->status);
        $this->assertFalse((bool) Event::find($sale->event_id)->is_cancelled);
        $this->assertFalse(\App\Services\Payments\PaymentGatewayDriver::hasLanded($sale), 'backing out is not a landing');

        $this->get($manage.'?payment=cancelled')->assertOk()
            ->assertSee(__('messages.payment_cancelled_not_charged'))
            ->assertSee(__('messages.appointments_awaiting_payment'))
            ->assertSee(route('appointments.pay', $this->bookingParams($sale)), false)
            ->assertSee(route('appointments.manage_cancel', $this->bookingParams($sale)), false)
            ->assertSee(trim(strstr(__('messages.appointments_held_until'), ':time', true)))
            // The hour PayPal's hold ends, in the zone the booking is shown in.
            ->assertSee($sale->created_at->copy()->addHour()->setTimezone('America/New_York')->format('g:i A'));

        // Presentation only, and only while there is something to pay: the same address after the
        // payment says nothing about a cancelled one.
        $sale->forceFill(['status' => 'paid'])->save();
        $this->get($manage.'?payment=cancelled')->assertOk()
            ->assertDontSee(__('messages.payment_cancelled_not_charged'));
    }

    public function test_backing_out_of_payfast_does_not_say_it_is_confirming(): void
    {
        // Payfast always reports awaitsConfirmation(), so a landing mark here would greet somebody
        // who has just backed out with "Confirming your payment".
        $this->connectPayfast();
        $this->book($this->paidType(['payment_method' => 'payfast', 'currency_code' => 'ZAR', 'price' => 150]));
        $sale = $this->sale();

        $this->get(route('payments.cancel', [
            'gateway' => 'payfast',
            'sale_id' => UrlUtils::encodeId($sale->id),
            'secret' => $sale->secret,
        ]))->assertRedirect(route('appointments.manage', $this->bookingParams($sale)).'?payment=cancelled');

        $this->get(route('appointments.manage', $this->bookingParams($sale)))->assertOk()
            ->assertDontSee(__('messages.ticket_confirming_payment'))
            ->assertSee(route('appointments.pay', $this->bookingParams($sale)), false);
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

    // ------------------------------------------------- per-gateway behaviour

    public function test_invoice_ninja_is_not_offered_for_appointments(): void
    {
        $this->owner->forceFill(['invoiceninja_api_key' => 'test-token', 'invoiceninja_company_name' => 'Acme'])->save();

        $this->assertNotContains('invoiceninja', app(\App\Services\Payments\PaymentGatewayManager::class)->appointmentKeys());
        $this->assertContains('paypal', app(\App\Services\Payments\PaymentGatewayManager::class)->appointmentKeys());

        $this->actingAs($this->owner)
            ->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'appointments', 'new' => 1]))
            ->assertOk()
            ->assertSee('value="paypal"', false)
            ->assertDontSee('value="invoiceninja"', false);

        $type = $this->paidType(['payment_method' => 'cash']);
        $this->actingAs($this->owner)->put(route('appointments.update', ['subdomain' => $this->role->subdomain, 'hash' => $type->hashedId()]), [
            'name' => 'Consultation', 'duration_minutes' => 30, 'location_type' => 'in_person',
            'price' => 50, 'currency_code' => 'USD', 'payment_method' => 'invoiceninja',
            'weekly_windows' => $type->weekly_windows, 'is_active' => 1,
        ])->assertSessionHasErrors('payment_method');

        // A type already stored on it (through the API, or before this rule) does not take bookings.
        $this->assertFalse($this->paidType(['payment_method' => 'invoiceninja'])->isBookable());
    }

    public function test_the_hold_comes_from_the_gateway(): void
    {
        $hours = fn (string $method) => $this->paidType(['payment_method' => $method])->expireHours();

        $this->assertSame(1, $hours('stripe'));
        $this->assertSame(24, $hours('payment_url'));
        $this->assertSame(1, $hours('paypal'));
        // An Instant EFT can clear hours later; a payment after the release would lose the booking.
        $this->assertSame(24, $hours('payfast'));
        $this->assertSame(0, $hours('cash'));

        $this->connectPayfast();
        $this->book($this->paidType(['payment_method' => 'payfast', 'currency_code' => 'ZAR', 'price' => 150]))->assertOk();
        $this->assertSame(24, (int) $this->sale()->event->expire_unpaid_tickets);
    }

    public function test_a_guest_back_from_paying_is_not_asked_to_pay_again(): void
    {
        $this->connectPayfast();
        $this->book($this->paidType(['payment_method' => 'payfast', 'currency_code' => 'ZAR', 'price' => 150]));
        $sale = $this->sale();
        $manage = route('appointments.manage', $this->bookingParams($sale));
        $pay = route('appointments.pay', $this->bookingParams($sale));

        // Payfast sends the buyer back before its ITN arrives.
        $this->get(route('payments.return', [
            'gateway' => 'payfast',
            'sale_id' => UrlUtils::encodeId($sale->id),
            'secret' => $sale->secret,
        ]))->assertRedirect($manage.'?new=1');

        // Inside the window the pay step refuses as well: no second Payfast form.
        $this->post($pay)->assertRedirect($manage);

        $this->get($manage)->assertOk()
            ->assertSee(__('messages.ticket_confirming_payment'))
            ->assertSee(route('ticket.status', $this->bookingParams($sale), false), false)
            ->assertDontSee($pay, false)
            ->assertDontSee(__('messages.appointments_complete_payment'));

        // Still nothing from Payfast once the window closes. An Instant EFT can clear hours later,
        // and a second payment would settle as already_paid with nobody told, so the guest who came
        // back from paying is still not asked to pay - nor offered Cancel, which would leave the
        // money with no booking.
        $this->travel(\App\Services\Payments\PaymentGatewayDriver::LANDED_SECONDS + 1)->seconds();

        $this->get($manage)->assertOk()
            ->assertSee(__('messages.ticket_confirming_payment'))
            ->assertSee(__('messages.appointments_payment_in_review', ['schedule' => $this->role->name]))
            ->assertDontSee($pay, false)
            ->assertDontSee(route('appointments.manage_cancel', $this->bookingParams($sale)), false);

        // The pay step refuses it too: no second Payfast form.
        $this->post($pay)->assertRedirect($manage);
    }

    public function test_a_later_checkout_in_the_same_browser_does_not_forget_the_landing(): void
    {
        // markLanded() tidies the session's notes as it writes one. Tidied at the 90-second
        // confirming window, the next checkout in the same browser - a ticket, another booking -
        // erased this booking's note, and its page offered Complete payment and Cancel again while
        // the Instant EFT was still clearing.
        $this->connectPayfast();
        $type = $this->paidType(['payment_method' => 'payfast', 'currency_code' => 'ZAR', 'price' => 150]);
        $this->book($type);
        $sale = $this->sale();
        $manage = route('appointments.manage', $this->bookingParams($sale));
        $pay = route('appointments.pay', $this->bookingParams($sale));
        $cancel = route('appointments.manage_cancel', $this->bookingParams($sale));
        $returnFor = fn (Sale $s) => route('payments.return', [
            'gateway' => 'payfast',
            'sale_id' => UrlUtils::encodeId($s->id),
            'secret' => $s->secret,
        ]);

        $this->get($returnFor($sale))->assertRedirect($manage.'?new=1');
        $this->travel(\App\Services\Payments\PaymentGatewayDriver::LANDED_SECONDS + 1)->seconds();

        // A second booking, paid from the same browser.
        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = app(AppointmentService::class)->availableSlots($type, $from, 1);
        $this->postJson(route('appointments.book.store', ['subdomain' => $this->role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Sam', 'email' => 'sam@gmail.com',
            'slot' => $slots['days'][array_key_first($slots['days'])][0]['utc'], 'guest_timezone' => 'America/New_York',
        ])->assertOk();
        $this->get($returnFor(Sale::where('email', 'sam@gmail.com')->firstOrFail()))->assertRedirect();

        $this->get($manage)->assertOk()
            ->assertSee(__('messages.appointments_payment_in_review', ['schedule' => $this->role->name]))
            ->assertDontSee($pay, false)
            ->assertDontSee($cancel, false);

        $this->post($pay)->assertRedirect($manage);
        $this->post($cancel)->assertRedirect($manage);
        $this->assertSame('unpaid', $sale->fresh()->status, 'the booking is still waiting on its payment');
    }

    public function test_a_guest_who_never_went_to_pay_still_gets_the_pay_button(): void
    {
        $this->connectPayfast();
        $this->book($this->paidType(['payment_method' => 'payfast', 'currency_code' => 'ZAR', 'price' => 150]));
        $sale = $this->sale();

        $this->get(route('appointments.manage', $this->bookingParams($sale)))->assertOk()
            ->assertDontSee(__('messages.ticket_confirming_payment'))
            ->assertSee(route('appointments.pay', $this->bookingParams($sale)), false);
    }

    // ------------------------------------------------------ review follow-ups

    public function test_an_approval_type_asks_for_payment_before_it_says_request_sent(): void
    {
        // fanOut() sends a priced booking to checkout before the approval step, so the payment is
        // what the guest has to act on first. Read the other way round, a guest who backed out saw
        // "Request sent" with no way to pay.
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
        \Illuminate\Support\Facades\Queue::fake();

        $this->book($this->paidType(['requires_approval' => true]));
        $sale = $this->sale();
        $manage = route('appointments.manage', $this->bookingParams($sale));
        $this->assertEmpty($this->queuedMailables(\App\Mail\AppointmentPending::class), 'no request mail before the payment');

        $this->get($manage)->assertOk()
            ->assertSee(__('messages.appointments_awaiting_payment'))
            ->assertSee(route('appointments.pay', $this->bookingParams($sale)), false)
            ->assertDontSee(__('messages.appointments_request_sent'));

        $this->post(route('appointments.pay', $this->bookingParams($sale)))->assertRedirect(self::APPROVE_URL);
        $this->get(route('payments.return', [
            'gateway' => 'paypal',
            'sale_id' => UrlUtils::encodeId($sale->id),
            'secret' => $sale->secret,
            'token' => 'ORDER123',
        ]));

        // Paid, and now waiting on the owner: "Request sent", and the request-received mail went
        // out with the payment (EmailService::sendSaleConfirmationEmails()).
        $this->assertSame('paid', $sale->fresh()->status);
        $this->assertNull($sale->fresh()->confirmed_at, 'not confirmed until the owner accepts');
        $this->get($manage)->assertOk()->assertSee(__('messages.appointments_request_sent'));
        $this->assertNotEmpty($this->queuedMailables(\App\Mail\AppointmentPending::class));
    }

    public function test_a_paypal_payment_under_review_is_not_offered_again_or_cancelled(): void
    {
        $this->captureStatus = 'PENDING';
        $this->book($this->paidType(['requires_approval' => true]));
        $sale = $this->sale();
        $params = $this->bookingParams($sale);
        $manage = route('appointments.manage', $params);

        $this->post(route('appointments.pay', $params))->assertRedirect(self::APPROVE_URL);
        $this->get(route('payments.return', [
            'gateway' => 'paypal',
            'sale_id' => UrlUtils::encodeId($sale->id),
            'secret' => $sale->secret,
            'token' => 'ORDER123',
        ]))->assertRedirect($manage.'?new=1')
            // The ticket's "your ticket will be issued" line has no place on a booking.
            ->assertSessionMissing('message');

        $this->assertSame('unpaid', $sale->fresh()->status);
        $this->assertNotNull($sale->fresh()->paypal_pending_at);

        // Past the confirming window, and on a type that also waits for approval: neither a second
        // payment nor a Cancel that would leave PayPal's money with no booking.
        $this->travel(\App\Services\Payments\PaymentGatewayDriver::LANDED_SECONDS + 1)->seconds();
        $this->get($manage)->assertOk()
            ->assertSee(__('messages.appointments_payment_in_review', ['schedule' => $this->role->name]))
            ->assertDontSee(__('messages.appointments_request_sent'))
            ->assertDontSee(route('appointments.pay', $params), false)
            ->assertDontSee(route('appointments.manage_cancel', $params), false);

        $orders = fn () => collect(Http::recorded())
            ->filter(fn ($pair) => $pair[0]->method() === 'POST' && str_ends_with($pair[0]->url(), '/v2/checkout/orders'))
            ->count();
        $before = $orders();
        $this->post(route('appointments.pay', $params))->assertRedirect($manage);
        $this->assertSame($before, $orders(), 'no second PayPal order');
    }

    public function test_the_pay_step_follows_the_method_the_booking_was_made_on(): void
    {
        $type = $this->paidType();
        $this->book($type);
        $sale = $this->sale();

        // The owner reprices the type and moves it to cash after this booking was made.
        $type->forceFill(['price' => 80, 'payment_method' => 'cash'])->save();

        $this->post(route('appointments.pay', $this->bookingParams($sale)))->assertRedirect(self::APPROVE_URL);

        $order = collect(Http::recorded())
            ->first(fn ($pair) => $pair[0]->method() === 'POST' && str_ends_with($pair[0]->url(), '/v2/checkout/orders'))[0]->data();
        $this->assertSame('50.00', $order['purchase_units'][0]['amount']['value'], 'what the booking was made for');
    }

    public function test_a_booking_landing_skips_the_ticket_funnel_and_cart(): void
    {
        $browser = [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120 Safari/537.36',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
            'HTTP_ACCEPT_LANGUAGE' => 'en-GB,en;q=0.9',
            'HTTP_CF_CONNECTING_IP' => '203.0.113.77',
            'REMOTE_ADDR' => '203.0.113.77',
        ];
        $done = fn () => (int) (\App\Models\MarketingDailyStat::where('date', now()->toDateString())->value('gp_checkouts_done') ?? 0);

        $this->book($this->paidType());
        $sale = $this->sale();
        $this->post(route('appointments.pay', $this->bookingParams($sale)));

        $this->call('GET', route('payments.return', [
            'gateway' => 'paypal',
            'sale_id' => UrlUtils::encodeId($sale->id),
            'secret' => $sale->secret,
            'token' => 'ORDER123',
        ]), [], [], [], $browser)->assertRedirect()->assertSessionMissing('cart_purchased');

        $this->assertSame('paid', $sale->fresh()->status);
        // A booking never counts checkout_start, so an end here would have no beginning.
        $this->assertSame(0, $done());

        // The same visitor and the same paid sale do count when asked directly, so it is the
        // booking branch that left it out, not the request.
        \App\Utils\GuestFunnel::countCheckoutDone(\Illuminate\Http\Request::create('/payments/paypal/return/1', 'GET', server: $browser), $sale->fresh());
        $this->assertSame(1, $done());
    }

    public function test_a_gateway_that_cannot_take_the_currency_is_refused_on_save(): void
    {
        $this->connectPayfast();
        $type = $this->paidType(['payment_method' => 'cash']);
        $url = route('appointments.update', ['subdomain' => $this->role->subdomain, 'hash' => $type->hashedId()]);
        $save = fn (string $method, string $currency) => $this->actingAs($this->owner)->put($url, [
            'name' => 'Consultation', 'duration_minutes' => 30, 'location_type' => 'in_person',
            'price' => 150, 'currency_code' => $currency, 'payment_method' => $method,
            'weekly_windows' => $type->weekly_windows, 'is_active' => 1,
        ]);

        $save('payfast', 'USD')->assertSessionHasErrors(['payment_method' => __('messages.payment_method_currency_unsupported', ['method' => 'Payfast', 'currency' => 'USD'])]);
        // INR is the default for a schedule in India, and PayPal's list does not have it.
        $save('paypal', 'INR')->assertSessionHasErrors('payment_method');
        $this->assertSame('cash', $type->fresh()->payment_method);

        $save('payfast', 'ZAR')->assertSessionHasNoErrors();
        $this->assertSame('payfast', $type->fresh()->payment_method);
    }

    public function test_an_unpaid_online_booking_is_not_reminded_accepted_or_moved(): void
    {
        config(['app.hosted' => false]);
        $this->book($this->paidType(['requires_approval' => true]));
        $sale = $this->sale();
        $event = Event::find($sale->event_id);

        // Accepting does not confirm a booking whose payment has not arrived.
        $this->actingAs($this->owner)->post(route('event.accept', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));
        $this->assertTrue((bool) $event->roles()->where('roles.id', $this->role->id)->first()->pivot->is_accepted);
        $this->assertNull($sale->fresh()->confirmed_at);

        // Moving it is refused: the hold expires on its creation clock.
        $this->postJson(route('appointments.reschedule.store', $this->bookingParams($sale)), [
            'slot' => Carbon::now('UTC')->addDays(2)->format('Y-m-d\TH:i:s\Z'),
        ])->assertStatus(422)->assertJsonPath('error', __('messages.appointments_reschedule_blocked_payment'));

        // No reminder while unpaid, and one once paid, in the reminder window.
        $event->forceFill(['starts_at' => now()->addHours(3)->format('Y-m-d H:i:s')])->saveQuietly();
        $this->artisan('app:send-appointment-reminders')->assertSuccessful();
        $this->assertNull($sale->fresh()->reminder_sent_at);

        $sale->refresh()->forceFill(['status' => 'paid'])->save();
        $this->artisan('app:send-appointment-reminders')->assertSuccessful();
        $this->assertNotNull($sale->fresh()->reminder_sent_at);
    }

    public function test_a_lapsed_hold_says_it_expired_rather_than_cancelled(): void
    {
        $this->book($this->paidType());
        $sale = $this->sale();

        $sale->forceFill(['status' => 'expired'])->save();

        $this->get(route('appointments.manage', $this->bookingParams($sale)))->assertOk()
            ->assertSee(__('messages.this_reservation_has_expired'))
            ->assertDontSee(__('messages.appointments_cancelled'));
    }

    public function test_a_gateway_that_cannot_start_keeps_the_booking(): void
    {
        // A guest who backed out kept the booking. A PayPal error on the next try must not cancel
        // it: the driver gives a TICKET's seats back, and a booking goes back to its page instead.
        $this->book($this->paidType());
        $sale = $this->sale();
        $manage = route('appointments.manage', $this->bookingParams($sale));

        $this->orderFails = true;
        $this->post(route('appointments.pay', $this->bookingParams($sale)))
            ->assertRedirect($manage)
            ->assertSessionHas('error', __('messages.paypal_checkout_unavailable'));

        $this->assertSame('unpaid', $sale->fresh()->status);
        $this->assertFalse((bool) Event::find($sale->event_id)->is_cancelled);

        // Once PayPal answers again, paying goes ahead.
        $this->orderFails = false;
        $this->post(route('appointments.pay', $this->bookingParams($sale)))->assertRedirect(self::APPROVE_URL);
    }

    public function test_a_gateway_the_owner_has_since_disconnected_is_not_started(): void
    {
        $this->book($this->paidType());
        $sale = $this->sale();

        $this->owner->forceFill(['paypal_client_id' => null, 'paypal_client_secret' => null])->save();

        $this->post(route('appointments.pay', $this->bookingParams($sale)))
            ->assertRedirect(route('appointments.manage', $this->bookingParams($sale)))
            ->assertSessionHas('error', __('messages.appointments_payment_unavailable', ['schedule' => $this->role->name]));
        $this->assertSame('unpaid', $sale->fresh()->status, 'the hold lapses on its own clock, not here');
    }

    public function test_money_that_did_not_match_is_not_asked_for_again(): void
    {
        $this->book($this->paidType());
        $sale = $this->sale();
        $params = $this->bookingParams($sale);
        $sale->forceFill(['status' => 'amount_mismatch'])->save();

        $this->get(route('appointments.manage', $params))->assertOk()
            ->assertSee(__('messages.appointments_payment_in_review', ['schedule' => $this->role->name]))
            ->assertDontSee(route('appointments.pay', $params), false)
            ->assertDontSee(route('appointments.manage_cancel', $params), false);
        $this->post(route('appointments.pay', $params))->assertRedirect(route('appointments.manage', $params));
        $this->assertSame('amount_mismatch', $sale->fresh()->status);

        // Nor cancelled from a stale tab or a hand-made post: the owner would hear nothing of the money.
        $this->post(route('appointments.manage_cancel', $params))->assertRedirect(route('appointments.manage', $params));
        $this->assertSame('amount_mismatch', $sale->fresh()->status);
        $this->assertFalse((bool) Event::find($sale->event_id)->is_cancelled);
    }

    public function test_nothing_is_taken_for_a_time_that_has_gone(): void
    {
        $this->book($this->paidType());
        $sale = $this->sale();
        $params = $this->bookingParams($sale);
        Event::find($sale->event_id)->forceFill(['starts_at' => now()->subHour()->format('Y-m-d H:i:s')])->saveQuietly();

        $this->get(route('appointments.manage', $params))->assertOk()
            ->assertSee(__('messages.appointments_passed'))
            ->assertDontSee(route('appointments.pay', $params), false);

        $before = collect(Http::recorded())->count();
        $this->post(route('appointments.pay', $params))->assertRedirect(route('appointments.manage', $params));
        $this->assertSame($before, collect(Http::recorded())->count(), 'no PayPal order for a past time');
    }

    public function test_a_price_under_the_gateways_floor_is_refused_on_save(): void
    {
        $this->connectPayfast();
        $type = $this->paidType(['payment_method' => 'cash']);
        $url = route('appointments.update', ['subdomain' => $this->role->subdomain, 'hash' => $type->hashedId()]);
        $save = fn ($price) => $this->actingAs($this->owner)->put($url, [
            'name' => 'Consultation', 'duration_minutes' => 30, 'location_type' => 'in_person',
            'price' => $price, 'currency_code' => 'ZAR', 'payment_method' => 'payfast',
            'weekly_windows' => $type->weekly_windows, 'is_active' => 1,
        ]);

        $save(3)->assertSessionHasErrors(['price' => __('messages.payment_method_amount_below_minimum', [
            'method' => 'Payfast', 'amount' => \App\Utils\MoneyUtils::format(5, 'ZAR'),
        ])]);
        $this->assertSame('cash', $type->fresh()->payment_method);

        $save(5)->assertSessionHasNoErrors();
        $this->assertSame('payfast', $type->fresh()->payment_method);
    }

    public function test_only_the_owner_is_pointed_at_the_payment_settings(): void
    {
        // The gateways are the owner's, and the link opens whoever is signed in's own settings: a
        // team admin who followed it would connect PayPal to their own account, for nothing.
        $this->paidType(['payment_method' => 'stripe']);
        $editor = route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'appointments', 'new' => 1]);
        $list = route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'appointments']);
        $link = route('profile.edit').'#section-payment-methods';

        $this->actingAs($this->owner)->get($editor)->assertOk()->assertSee($link, false);
        $this->actingAs($this->owner)->get($list)->assertOk()
            ->assertSee(__('messages.appointments_payment_not_set'))
            ->assertSee($link, false);

        $admin = \App\Models\User::factory()->create(['email_verified_at' => now()]);
        $this->role->users()->attach($admin->id, ['level' => 'admin']);
        $this->actingAs($admin)->get($editor)->assertOk()
            ->assertSee('name="payment_method"', false)
            ->assertDontSee($link, false);
        $this->actingAs($admin)->get($list)->assertOk()
            ->assertSee(__('messages.appointments_payment_not_set'))
            ->assertDontSee($link, false);
    }

    public function test_stripe_charges_what_the_booking_was_made_for(): void
    {
        // Stripe's appointment session is built here rather than by its driver, from the type's
        // price until now: a type repriced after the booking parked the paid sale as amount_mismatch.
        config(['app.hosted' => false, 'services.stripe_platform.secret' => 'sk_test_appointments']);
        $sessions = [];
        \Stripe\ApiRequestor::setHttpClient(new class($sessions) implements \Stripe\HttpClient\ClientInterface
        {
            public function __construct(private array &$sessions) {}

            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1')
            {
                $this->sessions[] = $params;

                return [json_encode(['id' => 'cs_test_127', 'object' => 'checkout.session', 'url' => 'https://checkout.stripe.com/c/pay/cs_test_127']), 200, []];
            }
        });

        try {
            $type = $this->paidType(['payment_method' => 'stripe']);
            $response = $this->book($type)->assertOk();
            $sale = $this->sale();

            $this->assertSame('https://checkout.stripe.com/c/pay/cs_test_127', $response->json('redirect_url'));
            $this->assertSame(route('appointments.manage', $this->bookingParams($sale), false), $response->json('manage_url'));
            $this->assertSame(5000, (int) $sessions[0]['line_items'][0]['price_data']['unit_amount']);
            $this->assertStringEndsWith('?payment=cancelled', $sessions[0]['cancel_url']);

            $type->forceFill(['price' => 80])->save();
            $this->post(route('appointments.pay', $this->bookingParams($sale)))->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_127');
            $this->assertSame(5000, (int) $sessions[1]['line_items'][0]['price_data']['unit_amount'], 'the booking, not the type as it is now');
            $this->assertSame('usd', strtolower($sessions[1]['line_items'][0]['price_data']['currency']));

            // An owner who has since unlinked Stripe: no session at all, never one on the platform's key.
            config(['services.stripe_platform.secret' => null]);
            $this->post(route('appointments.pay', $this->bookingParams($sale)))
                ->assertRedirect(route('appointments.manage', $this->bookingParams($sale)))
                ->assertSessionHas('error');
            $this->assertCount(2, $sessions);
        } finally {
            \Stripe\ApiRequestor::setHttpClient(null);
        }
    }

    public function test_a_stored_method_no_driver_knows_stays_listed_and_is_not_saved_as_cash(): void
    {
        // A backup from another install can carry a method this one has never had.
        $type = $this->paidType(['payment_method' => 'bitcoin']);
        $edit = route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'appointments', 'edit' => $type->hashedId()]);

        $this->actingAs($this->owner)->get($edit)->assertOk()
            ->assertSee('value="bitcoin" checked', false)
            ->assertSee('bitcoin - '.__('messages.payment_method_unavailable'));
        $this->assertFalse($type->fresh()->isBookable());

        $this->actingAs($this->owner)->put(route('appointments.update', ['subdomain' => $this->role->subdomain, 'hash' => $type->hashedId()]), [
            'name' => $type->name, 'duration_minutes' => 30, 'location_type' => 'in_person',
            'price' => 50, 'currency_code' => 'USD', 'payment_method' => 'bitcoin',
            'weekly_windows' => $type->weekly_windows, 'is_active' => 1,
        ])->assertSessionHasErrors('payment_method');
        $this->assertSame('bitcoin', $type->fresh()->payment_method);
    }

    /** Mailables queued via SendQueuedEmail whose class matches (empty when nothing queued). */
    private function queuedMailables(string $class): array
    {
        $found = [];
        foreach (\Illuminate\Support\Facades\Queue::pushed(\App\Jobs\SendQueuedEmail::class) as $job) {
            $ref = new \ReflectionProperty($job, 'mailable');
            $mailable = $ref->getValue($job);
            if ($mailable instanceof $class) {
                $found[] = $mailable;
            }
        }

        return $found;
    }
}
