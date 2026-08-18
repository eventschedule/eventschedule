<?php

namespace Tests\Feature;

use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
