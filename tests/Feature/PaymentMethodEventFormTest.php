<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Services\Payments\PaymentGatewayManager;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The event form's payment_method handling: server-side validation (the web path had none, which is
 * half of how a USD event kept charging through Payfast) and the stored-method option surviving in
 * the dropdown (the other half - a blank select posts nothing, so a stale value silently outlived
 * every save).
 */
class PaymentMethodEventFormTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    public function test_the_web_form_rejects_a_non_gateway_payment_method(): void
    {
        // 'rsvp' is a provenance marker on sales, not a gateway, and an arbitrary string would only
        // die later on the MySQL enum write. The API has had this rule from the start; the web path
        // did not.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'cash']);

        $this->putUpdateEvent($owner, $role, $event, ['payment_method' => 'rsvp'])
            ->assertSessionHasErrors('payment_method');

        $this->putUpdateEvent($owner, $role, $event, ['payment_method' => 'hax'])
            ->assertSessionHasErrors('payment_method');

        $this->assertSame('cash', $event->fresh()->payment_method);

        // A real registry key passes.
        $this->putUpdateEvent($owner, $role, $event, ['payment_method' => 'payfast'])
            ->assertSessionDoesntHaveErrors('payment_method');
    }

    public function test_a_disconnected_gateway_still_renders_its_stored_method(): void
    {
        // The case the previous commit named but did not cover: with the ONLY gateway disconnected,
        // connectedFor() is empty, so the whole select was hidden behind the "connect a gateway"
        // nudge - leaving the stored method invisible AND unchangeable from the form, which is the
        // exact dead end the fix was supposed to remove.
        $owner = $this->createOwner();
        $owner->forceFill([
            'payfast_merchant_id' => '10000100',
            'payfast_merchant_key' => '46f0cd694581a',
            'payfast_passphrase' => 'test-passphrase',
        ])->save();

        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'payfast',
            'ticket_currency_code' => 'ZAR',
        ]);

        // Owner clears their Payfast credentials afterwards.
        $owner->forceFill([
            'payfast_merchant_id' => null,
            'payfast_merchant_key' => null,
            'payfast_passphrase' => null,
        ])->save();

        $response = $this->actingAs($owner)->get(
            route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
        );

        $response->assertOk();
        $response->assertSee('name="payment_method"', escape: false);
        $response->assertSee('value="payfast"', escape: false);
        // And it is marked, so a broken configuration does not read as a healthy one.
        $response->assertSee(__('messages.payment_method_unavailable'));
    }

    public function test_a_stored_method_no_longer_offered_still_renders_as_an_option(): void
    {
        // The dropdown is filtered by the SAVED currency, so an event whose currency changed after
        // saving used to render a select with no matching option - which shows blank, posts nothing,
        // and silently preserves the stale value forever. The stored method must stay visible so the
        // owner can see it and change it; the checkout-time guards remain the authority.
        $owner = $this->createOwner();
        $owner->forceFill([
            'payfast_merchant_id' => '10000100',
            'payfast_merchant_key' => '46f0cd694581a',
            'payfast_passphrase' => 'test-passphrase',
        ])->save();

        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'payfast',
            'ticket_currency_code' => 'ZAR',
        ]);

        // The state G1 exploits: currency edited after the method was saved.
        $event->forceFill(['ticket_currency_code' => 'USD'])->save();

        $response = $this->actingAs($owner)->get(
            route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
        );

        $response->assertOk();
        // Payfast is not offerable for USD, but the stored selection still renders.
        $response->assertSee('value="payfast"', escape: false);
        // And the options carry v-pre: the label can contain owner-typed text (the merchant id) and
        // this select is inside the Vue mount, where an unguarded text node compiles as a template.
        $response->assertSee('<option v-pre value="payfast"', escape: false);
    }

    // ------------------------------------------------- DEFAULT_PAYMENT_METHOD

    /**
     * The install's own Payfast account, so the owner under test is "connected" without having
     * entered anything - the selfhost shape DEFAULT_PAYMENT_METHOD is meant for.
     */
    private function installWidePayfast(): void
    {
        config([
            'app.hosted' => false,
            'payments.payfast.merchant_id' => '20000200',
            'payments.payfast.merchant_key' => 'platform-merchant-key',
            'payments.payfast.passphrase' => 'platform-passphrase',
            'payments.payfast.sandbox' => true,
        ]);
    }

    /** Configure an API key on the user and return the raw key for the X-API-Key header. */
    private function apiKey(User $user): string
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return $raw;
    }

    /**
     * What the create form actually starts a new event on.
     *
     * Read out of the Vue mount's seed rather than a selected <option>: the select is bound with
     * v-model, so the server-rendered markup has no selected attribute to look at.
     */
    private function createFormPaymentMethod($owner, $role): ?string
    {
        $html = $this->actingAs($owner)
            ->get(route('event.create', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        preg_match('/"payment_method":"([a-z_]+)"/', $html, $m);

        return $m[1] ?? null;
    }

    public function test_a_new_event_starts_on_the_configured_default(): void
    {
        // A South African selfhost install cannot use Stripe at all, so every new event defaulting to
        // Cash means picking Payfast by hand forever. DEFAULT_PAYMENT_METHOD is how the operator says
        // it once.
        $this->installWidePayfast();
        config(['payments.default_method' => 'payfast']);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['country_code' => 'ZA']);

        $this->assertSame('payfast', $this->createFormPaymentMethod($owner, $role));
    }

    public function test_the_default_falls_back_to_cash_when_the_gateway_cannot_take_the_currency(): void
    {
        // Payfast settles in rand only. Starting a USD event on it would produce an event whose
        // checkout refuses every buyer, which is worse than the Cash default it replaced.
        $this->installWidePayfast();
        config(['payments.default_method' => 'payfast']);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['country_code' => 'US']);

        $this->assertSame('cash', $this->createFormPaymentMethod($owner, $role));
    }

    public function test_the_default_falls_back_to_cash_when_nothing_is_connected(): void
    {
        // Named in .env but with no credentials anywhere. Degrades to today's behaviour rather than
        // to an event nobody can buy from.
        config(['app.hosted' => false, 'payments.default_method' => 'payfast']);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['country_code' => 'ZA']);

        $this->assertSame('cash', $this->createFormPaymentMethod($owner, $role));
    }

    public function test_an_unset_default_still_starts_on_cash(): void
    {
        // Connected, and with exactly ONE way to take money online, which is the tempting case for
        // guessing: an install with only Payfast surely means Payfast. It does not. Picking a gateway
        // nobody asked for would change what every existing install's new events default to, so this
        // stays on Cash until an operator names a default.
        //
        // phpunit.xml forces STRIPE_PLATFORM_SECRET, so Stripe would otherwise be a second online
        // gateway here and hide exactly the behaviour this pins.
        $this->installWidePayfast();
        config(['services.stripe_platform.secret' => null]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['country_code' => 'ZA']);

        $this->assertSame(
            ['cash', 'payfast'],
            array_keys(app(PaymentGatewayManager::class)->availableFor($owner, 'ZAR')),
            'the fixture must leave Payfast as the only online option, or this pins nothing',
        );

        $this->assertSame('cash', $this->createFormPaymentMethod($owner, $role));
    }

    public function test_a_schedules_saved_ticket_defaults_beat_the_configured_default(): void
    {
        // The owner's own explicit choice wins over the operator's fallback.
        $this->installWidePayfast();
        config(['payments.default_method' => 'payfast']);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['country_code' => 'ZA']);
        $role->forceFill(['default_tickets' => json_encode([
            'currency_code' => 'ZAR',
            'payment_method' => 'cash',
        ])])->save();

        $this->assertSame('cash', $this->createFormPaymentMethod($owner, $role));
    }

    public function test_the_api_applies_the_configured_default_on_create(): void
    {
        $this->installWidePayfast();
        config(['payments.default_method' => 'payfast']);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['country_code' => 'ZA']);

        $this->postJson('/api/events/'.$role->subdomain, [
            'name' => 'Rand Show',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'ticket_currency_code' => 'ZAR',
        ], ['X-API-Key' => $this->apiKey($owner)])->assertSuccessful();

        $this->assertSame('payfast', Event::where('name', 'Rand Show')->firstOrFail()->payment_method);
    }

    public function test_the_api_leaves_an_explicit_payment_method_alone(): void
    {
        $this->installWidePayfast();
        config(['payments.default_method' => 'payfast']);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['country_code' => 'ZA']);

        $this->postJson('/api/events/'.$role->subdomain, [
            'name' => 'Cash Only Show',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'ticket_currency_code' => 'ZAR',
            'payment_method' => 'cash',
        ], ['X-API-Key' => $this->apiKey($owner)])->assertSuccessful();

        $this->assertSame('cash', Event::where('name', 'Cash Only Show')->firstOrFail()->payment_method);
    }

    // ------------------------------------------------- the currency-mismatch warning

    public function test_an_owner_whose_only_gateway_cannot_take_the_currency_is_warned(): void
    {
        // connectedFor() is currency-blind, so install-wide Payfast makes every owner "connected"
        // and silently removes the setup nudge - leaving a USD schedule looking healthy while its
        // dropdown holds nothing but Cash. The owner would publish a paid event that can only be
        // settled by hand and never be told.
        $this->installWidePayfast();
        config(['services.stripe_platform.secret' => null]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['country_code' => 'US']);

        $manager = app(PaymentGatewayManager::class);
        $this->assertNotEmpty($manager->connectedFor($owner), 'fixture: they must look connected');
        $this->assertSame(['cash'], array_keys($manager->availableFor($owner, 'USD')));

        $this->actingAs($owner)
            ->get(route('event.create', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertSee(__('messages.no_payment_method_for_currency', ['currency' => 'USD']))
            // NOT the Stripe nudge: on the installs this case is commonest on, Stripe is precisely
            // what is unavailable, so "Connect Stripe to get paid" would be wrong advice.
            ->assertDontSee(__('messages.connect_stripe_to_get_paid'));
    }

    public function test_the_warning_stays_away_when_the_gateway_can_take_the_currency(): void
    {
        $this->installWidePayfast();
        config(['services.stripe_platform.secret' => null]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['country_code' => 'ZA']);

        $this->actingAs($owner)
            ->get(route('event.create', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertDontSee(__('messages.no_payment_method_for_currency', ['currency' => 'ZAR']));
    }

    public function test_an_owner_with_nothing_connected_still_gets_the_setup_nudge(): void
    {
        // The original invariant, unchanged: the new branch must not swallow it.
        config(['app.hosted' => false, 'services.stripe_platform.secret' => null]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['country_code' => 'ZA']);

        $this->actingAs($owner)
            ->get(route('event.create', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertSee(__('messages.connect_stripe_to_get_paid'))
            ->assertDontSee(__('messages.no_payment_method_for_currency', ['currency' => 'ZAR']));
    }

    public function test_the_api_keeps_an_explicit_null_payment_method(): void
    {
        // nullable in the rule and nullable in the column, so an explicit null is the caller saying
        // "no online method", not the caller saying nothing. Only an absent key takes the default.
        $this->installWidePayfast();
        config(['payments.default_method' => 'payfast']);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['country_code' => 'ZA']);

        $this->postJson('/api/events/'.$role->subdomain, [
            'name' => 'Explicit Null Show',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'ticket_currency_code' => 'ZAR',
            'payment_method' => null,
        ], ['X-API-Key' => $this->apiKey($owner)])->assertSuccessful();

        $this->assertNotSame('payfast', Event::where('name', 'Explicit Null Show')->firstOrFail()->payment_method);
    }
}
