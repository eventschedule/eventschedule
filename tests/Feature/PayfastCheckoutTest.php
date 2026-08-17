<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Services\Payments\PaymentGatewayManager;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The redirect leg of the Payfast integration (#113): the signed form that posts the buyer to
 * Payfast, and the gating that keeps owners from selecting it where it cannot work.
 */
class PayfastCheckoutTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function connectedOwner(array $attrs = [])
    {
        $owner = $this->createOwner();

        $owner->forceFill(array_merge([
            'payfast_merchant_id' => '10000100',
            'payfast_merchant_key' => '46f0cd694581a',
            'payfast_passphrase' => 'test-passphrase',
            'payfast_sandbox' => true,
        ], $attrs))->save();

        return $owner;
    }

    private function payfastEvent($role, array $attrs = [])
    {
        return $this->createEvent($role, array_merge([
            'tickets_enabled' => true,
            'payment_method' => 'payfast',
            'ticket_currency_code' => 'ZAR',
        ], $attrs));
    }

    private function checkout($role, $event, $ticket, int $qty = 1)
    {
        return $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'ZAR Buyer',
            'email' => 'zar-buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => $qty],
        ]);
    }

    public function test_checkout_posts_a_signed_form_to_payfast(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->payfastEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        $response = $this->checkout($role, $event, $ticket, 2);

        $sale = Sale::where('email', 'zar-buyer@gmail.com')->firstOrFail();
        $encoded = UrlUtils::encodeId($sale->id);

        $response->assertOk();
        // Sandbox is on for this owner, so it must be the sandbox host and not the live one.
        $response->assertSee('https://sandbox.payfast.co.za/eng/process', escape: false);
        $response->assertSee('name="merchant_id" value="10000100"', escape: false);
        // Major units with two decimals, not cents.
        $response->assertSee('name="amount" value="300.00"', escape: false);
        // The encoded sale id, so the ITN can be cross-checked against the sale in its own URL.
        $response->assertSee('name="m_payment_id" value="'.$encoded.'"', escape: false);
        $response->assertSee(route('payments.webhook', ['gateway' => 'payfast', 'sale_id' => $encoded]), escape: false);

        // The sale is recorded but unpaid: only the ITN settles it.
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'unpaid',
            'payment_method' => 'payfast',
            'payment_amount' => 300,
        ]);
    }

    public function test_the_passphrase_never_reaches_the_browser(): void
    {
        // Invoice Ninja's driver posts the passphrase as a hidden field, handing the merchant's shared
        // secret to the buyer. Anyone holding it can forge a valid ITN and mark sales paid for free,
        // so this is the single most important assertion in this file.
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->payfastEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        $response = $this->checkout($role, $event, $ticket);

        $response->assertOk();
        $response->assertDontSee('test-passphrase');
        $response->assertDontSee('name="passphrase"', escape: false);
        // Nor the merchant key's secret sibling by accident - the key itself has to be posted, but
        // confirm the passphrase is not smuggled in under another name.
        $response->assertDontSee('passphrase', escape: false);
    }

    public function test_the_signature_matches_the_posted_fields(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->payfastEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        $html = $this->checkout($role, $event, $ticket)->getContent();

        // Rebuild the signature from what the page actually posts, so a field added to the form
        // without being signed (or vice versa) fails here rather than at Payfast.
        preg_match_all('/name="([^"]+)" value="([^"]*)"/', $html, $matches, PREG_SET_ORDER);

        $posted = [];
        foreach ($matches as $match) {
            $posted[$match[1]] = html_entity_decode($match[2], ENT_QUOTES);
        }

        $this->assertArrayHasKey('signature', $posted);

        $expected = \App\Services\Payments\Payfast\PayfastSignature::sign($posted, 'test-passphrase');

        $this->assertSame($expected, $posted['signature']);
    }

    public function test_a_live_owner_posts_to_the_live_host(): void
    {
        $owner = $this->connectedOwner(['payfast_sandbox' => false]);
        $role = $this->createRole($owner);
        $event = $this->payfastEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        $response = $this->checkout($role, $event, $ticket);

        $response->assertSee('https://www.payfast.co.za/eng/process', escape: false);
        $response->assertDontSee('sandbox.payfast.co.za');
    }

    public function test_over_long_fields_are_truncated_before_signing(): void
    {
        // Payfast rejects the whole redirect on an over-long item_name (100 chars), and newlines break
        // the signature string. Both have to be dealt with before signing, not after a bounce.
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->payfastEvent($role, [
            // Over Payfast's 100-char item_name limit, but within events.name's own 255.
            'name' => str_repeat('Very Long Event Name ', 10),
        ]);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        $html = $this->checkout($role, $event, $ticket)->getContent();

        preg_match('/name="item_name" value="([^"]*)"/', $html, $m);

        $this->assertNotEmpty($m[1] ?? '');
        $this->assertLessThanOrEqual(100, mb_strlen(html_entity_decode($m[1], ENT_QUOTES)));
    }

    public function test_payfast_is_only_offered_for_zar(): void
    {
        $owner = $this->connectedOwner();
        $manager = app(PaymentGatewayManager::class);

        $this->assertArrayHasKey('payfast', $manager->availableFor($owner, 'ZAR'));
        $this->assertArrayNotHasKey('payfast', $manager->availableFor($owner, 'USD'));
        $this->assertArrayNotHasKey('payfast', $manager->availableFor($owner, 'EUR'));
    }

    public function test_payfast_is_not_offered_below_its_floor(): void
    {
        // Payfast will not process under R5.00 and reports that as a failure on its own page, so the
        // gateway is withheld rather than letting a buyer discover it there.
        $owner = $this->connectedOwner();
        $manager = app(PaymentGatewayManager::class);

        $this->assertArrayNotHasKey('payfast', $manager->availableFor($owner, 'ZAR', 4.99));
        $this->assertArrayHasKey('payfast', $manager->availableFor($owner, 'ZAR', 5.00));
    }

    public function test_an_unconnected_owner_is_not_offered_payfast(): void
    {
        $owner = $this->createOwner();

        $this->assertArrayNotHasKey('payfast', app(PaymentGatewayManager::class)->connectedFor($owner));

        // Half-connected counts as not connected: a merchant id with no key cannot sign a checkout.
        $owner->forceFill(['payfast_merchant_id' => '10000100'])->save();

        $this->assertArrayNotHasKey('payfast', app(PaymentGatewayManager::class)->connectedFor($owner->fresh()));
    }

    public function test_payfast_cannot_be_carted(): void
    {
        // One m_payment_id per payment, so a single ITN cannot settle several legs.
        $this->assertFalse(app(PaymentGatewayManager::class)->supportsCart('payfast'));
    }

    public function test_a_disconnected_owner_lands_the_buyer_rather_than_posting_an_unsigned_form(): void
    {
        // Reachable when an owner disconnects Payfast while an event still names it.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->payfastEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        $response = $this->checkout($role, $event, $ticket);

        $sale = Sale::where('email', 'zar-buyer@gmail.com')->firstOrFail();

        $response->assertRedirect(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]));
        $this->assertSame('unpaid', $sale->status);
    }

    public function test_a_single_selected_payment_type_pins_the_checkout(): void
    {
        $owner = $this->connectedOwner(['payfast_payment_types' => 'cp']);
        $role = $this->createRole($owner);
        $event = $this->payfastEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        $this->checkout($role, $event, $ticket)
            ->assertSee('name="payment_method" value="cp"', escape: false);
    }

    public function test_several_selected_payment_types_leave_the_choice_to_payfast(): void
    {
        // payment_method takes one code, not a list, so ticking several means "show them all" -
        // which is what the owner meant by choosing more than one.
        $owner = $this->connectedOwner(['payfast_payment_types' => 'cp,ef,cc']);
        $role = $this->createRole($owner);
        $event = $this->payfastEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        $this->checkout($role, $event, $ticket)
            ->assertDontSee('name="payment_method"', escape: false);
    }
}
