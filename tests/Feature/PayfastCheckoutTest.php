<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Services\Payments\Payfast\PayfastSignature;
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

    public function test_every_posted_field_is_signed(): void
    {
        // The signature covers a fixed field list and skips empties, while the view renders whatever
        // it is handed. If those two sets ever diverge Payfast rejects the payment, so pin that the
        // posted set is exactly the signed set.
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->payfastEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        $html = $this->checkout($role, $event, $ticket)->getContent();

        preg_match_all('/name="([^"]+)" value="([^"]*)"/', $html, $matches, PREG_SET_ORDER);

        $posted = [];
        foreach ($matches as $match) {
            $posted[$match[1]] = html_entity_decode($match[2], ENT_QUOTES);
        }

        $signature = $posted['signature'] ?? null;
        unset($posted['signature']);

        $this->assertNotNull($signature);

        // No blank field may be posted: sign() would have skipped it while the form still sends it.
        foreach ($posted as $name => $value) {
            $this->assertNotSame('', $value, "posted field {$name} is blank and therefore unsigned");
        }

        $this->assertSame(
            PayfastSignature::sign($posted, 'test-passphrase'),
            $signature,
            'the signature must cover exactly the fields the form posts',
        );
    }

    public function test_an_individual_tickets_event_charges_the_group_total(): void
    {
        // Payfast does not disable individual tickets, so a Payfast sale can be a GROUP primary with
        // guest rows hanging off it. The redirect must charge the whole group, and the ITN then
        // reconciles against the same figure - if the two disagreed every such sale would land in
        // amount_mismatch.
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->payfastEvent($role, ['individual_tickets' => true]);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'ZAR Buyer',
            'email' => 'zar-buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 2],
            'guests' => [['name' => 'Second Attendee', 'email' => 'guest@gmail.com']],
        ])->assertOk();

        $primary = Sale::where('email', 'zar-buyer@gmail.com')->firstOrFail();

        // The amount posted to Payfast is the group total, not the primary seat's own share.
        $this->assertEqualsWithDelta(300.0, (float) $primary->groupTotalPayment(), 0.001);

        $html = $this->checkout($role, $event, $ticket)->getContent();
        $this->assertMatchesRegularExpression('/name="amount" value="\d+\.\d{2}"/', $html);
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

    public function test_an_owner_without_a_passphrase_is_not_offered_payfast(): void
    {
        // Payfast treats the passphrase as optional; we do not. Without one, verifyItn() appends
        // nothing and the ITN signature degrades to a plain MD5 of the payload that anyone can
        // reproduce - so the check meant to prove a notification came from Payfast proves nothing.
        // Such an account must never reach an event's payment dropdown.
        $owner = $this->createOwner();
        $owner->forceFill([
            'payfast_merchant_id' => '10000100',
            'payfast_merchant_key' => '46f0cd694581a',
            'payfast_passphrase' => null,
        ])->save();

        $manager = app(PaymentGatewayManager::class);

        $this->assertArrayNotHasKey('payfast', $manager->connectedFor($owner->fresh()));
        $this->assertArrayNotHasKey('payfast', $manager->availableFor($owner->fresh(), 'ZAR'));
    }

    public function test_a_first_connect_cannot_save_without_the_secrets(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner)
            ->post('/payments/payfast/connect', ['payfast_merchant_id' => '10000100'])
            ->assertSessionHasErrors(['payfast_merchant_key', 'payfast_passphrase']);

        $this->assertNull($owner->fresh()->payfast_merchant_id);
    }

    public function test_a_reconnect_may_leave_the_stored_secrets_untouched(): void
    {
        // The blank-means-unchanged convention: an owner correcting a merchant id must not have to
        // retype secrets the form never shows them.
        $owner = $this->connectedOwner();

        $this->actingAs($owner)
            ->post('/payments/payfast/connect', ['payfast_merchant_id' => '10000999'])
            ->assertSessionHasNoErrors();

        $owner->refresh();

        $this->assertSame('10000999', $owner->payfast_merchant_id);
        $this->assertSame('46f0cd694581a', $owner->payfast_merchant_key);
        $this->assertSame('test-passphrase', $owner->payfast_passphrase);
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

    public function test_a_payment_type_restriction_can_be_cleared(): void
    {
        // Unticking every box posts nothing for a checkbox group, and saveCredentials() skips a field
        // that is absent - so without the blank sentinel the owner could never undo a restriction, and
        // every checkout would keep pinning the type they had since removed. The help text promises
        // that unticking everything hands the choice back to Payfast.
        $owner = $this->connectedOwner(['payfast_payment_types' => 'cp']);
        $this->createRole($owner);

        // The form itself must carry the blank sentinel, or a browser with every box unticked posts
        // nothing at all and the controller never sees the field. Posting the sentinel by hand below
        // would test only the controller half and pass with the form broken.
        $this->actingAs($owner)->get(route('profile.edit'))
            ->assertSee('name="payfast_payment_types[]" value=""', escape: false);

        $this->actingAs($owner)
            ->post('/payments/payfast/connect', [
                'payfast_merchant_id' => '10000100',
                'payfast_payment_types' => [''],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('', (string) $owner->fresh()->payfast_payment_types);
    }

    public function test_a_buyer_whose_name_is_zero_still_gets_a_valid_signature(): void
    {
        // "0" is a legal name and passes `required`, but it is falsy. Skipping it with empty() would
        // drop name_first from our hash while the form still posted it, and Payfast would reject
        // every such payment as a signature mismatch.
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner);
        $event = $this->payfastEvent($role);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        $html = $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => '0',
            'email' => 'zar-buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
        ])->getContent();

        preg_match_all('/name="([^"]+)" value="([^"]*)"/', $html, $matches, PREG_SET_ORDER);

        $posted = [];
        foreach ($matches as $match) {
            $posted[$match[1]] = html_entity_decode($match[2], ENT_QUOTES);
        }

        $signature = $posted['signature'];
        unset($posted['signature']);

        $this->assertSame('0', $posted['name_first']);

        // Asserting sign($posted) === $signature would prove nothing: both sides call the same
        // function, so a field it wrongly skips is skipped identically in the expectation. Pin
        // instead that name_first genuinely PARTICIPATES - drop it and the signature must change.
        $withoutName = $posted;
        unset($withoutName['name_first']);

        $this->assertNotSame(
            PayfastSignature::sign($withoutName, 'test-passphrase'),
            $signature,
            'name_first is posted, so it must be covered by the signature',
        );
        $this->assertSame(PayfastSignature::sign($posted, 'test-passphrase'), $signature);
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
