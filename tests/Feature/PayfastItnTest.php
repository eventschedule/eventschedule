<?php

namespace Tests\Feature;

use App\Models\AnalyticsEventsDaily;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Payfast ITN callback (#113), which is the only thing that marks a Payfast sale paid.
 *
 * Payfast recommends four checks and Invoice Ninja's driver implements one. Each of the five here has
 * its own test, because any of them passing when it should not means an attacker can mark sales paid
 * without paying.
 */
class PayfastItnTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const PASSPHRASE = 'test-passphrase';

    private $owner;

    private $event;

    private $sale;

    /**
     * What Payfast's validate endpoint replies. A closure-backed fake rather than a URL stub because
     * Http::fake() MERGES stubs - a second fake for the same URL never wins, so a test that needs a
     * different answer has to change the source of the answer instead.
     */
    private string $validationBody = 'VALID';

    private int $validationStatus = 200;

    protected function setUp(): void
    {
        parent::setUp();

        // The source-IP check resolves these hosts. Pointing it at the test client is what lets the
        // check run for real rather than being stubbed out.
        config(['payments.payfast.itn_hosts' => ['127.0.0.1']]);

        // Payfast's server-side confirmation. Faked rather than skipped so the call is asserted.
        Http::fake(fn () => Http::response($this->validationBody, $this->validationStatus));

        $this->owner = $this->createOwner();
        $this->owner->forceFill([
            'payfast_merchant_id' => '10000100',
            'payfast_merchant_key' => '46f0cd694581a',
            'payfast_passphrase' => self::PASSPHRASE,
            'payfast_sandbox' => true,
        ])->save();

        $role = $this->createRole($this->owner);
        $this->event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'payfast',
            'ticket_currency_code' => 'ZAR',
        ]);
        $ticket = $this->createTicket($this->event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);

        // Built through the real checkout rather than hand-crafted, so the row under test is shaped
        // exactly as production shapes it - seat maps, pricing and all.
        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'event_date' => Carbon::parse($this->event->starts_at)->format('Y-m-d'),
            'name' => 'ZAR Buyer',
            'email' => 'zar-buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 2],
        ])->assertOk();

        $this->sale = Sale::where('email', 'zar-buyer@gmail.com')->firstOrFail();

        // 2 x R150. Asserted here so a pricing change shows up as a fixture failure rather than as a
        // confusing amount-mismatch further down.
        $this->assertSame(300.0, (float) $this->sale->payment_amount);
    }

    /**
     * Build a signed ITN body the way Payfast does: their field order, then the passphrase appended as
     * a urlencoded suffix.
     */
    private function itnBody(array $overrides = [], ?string $passphrase = self::PASSPHRASE): string
    {
        $payload = array_merge([
            'm_payment_id' => UrlUtils::encodeId($this->sale->id),
            'pf_payment_id' => '2579',
            'payment_status' => 'COMPLETE',
            'item_name' => 'General',
            'amount_gross' => '300.00',
            'amount_fee' => '-8.50',
            'amount_net' => '291.50',
            // Payfast really does send these empty, which is the case that breaks a signature
            // rebuilt from the parsed request rather than the raw body.
            'custom_str1' => '',
            'name_first' => '',
            'email_address' => '',
            'merchant_id' => '10000100',
        ], $overrides);

        $body = http_build_query($payload);

        return $body.'&signature='.md5($body.($passphrase ? '&passphrase='.urlencode($passphrase) : ''));
    }

    private function postItn(string $body)
    {
        return $this->call(
            'POST',
            route('payments.webhook', ['gateway' => 'payfast', 'sale_id' => UrlUtils::encodeId($this->sale->id)]),
            [], [], [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded', 'REMOTE_ADDR' => '127.0.0.1'],
            $body,
        );
    }

    public function test_a_valid_itn_settles_the_sale(): void
    {
        $this->postItn($this->itnBody())->assertNoContent();

        $this->sale->refresh();

        $this->assertSame('paid', $this->sale->status);
        // The gateway's own id, which is also how a duplicate delivery is recognised.
        $this->assertSame('2579', $this->sale->transaction_reference);

        // Revenue is booked once, against this event.
        $this->assertSame(
            300.0,
            (float) AnalyticsEventsDaily::where('event_id', $this->event->id)->sum('revenue'),
        );

        // Payfast was actually asked to confirm the payload.
        Http::assertSent(fn ($request) => str_contains($request->url(), '/eng/query/validate'));
    }

    public function test_a_tampered_amount_is_rejected_by_the_signature(): void
    {
        // Re-signing is not possible without the passphrase, so an attacker can only alter the body
        // and leave the original signature - which no longer matches.
        $body = $this->itnBody();
        $tampered = str_replace('amount_gross=300.00', 'amount_gross=1.00', $body);

        $this->postItn($tampered)->assertStatus(400);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_an_itn_signed_with_the_wrong_passphrase_is_rejected(): void
    {
        $this->postItn($this->itnBody(passphrase: 'not-the-passphrase'))->assertStatus(400);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_an_itn_from_another_merchant_is_rejected(): void
    {
        // Correctly signed with OUR passphrase but naming a different merchant account. Invoice
        // Ninja's driver does not check this.
        $this->postItn($this->itnBody(['merchant_id' => '99999999']))->assertStatus(400);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_an_itn_replayed_against_a_different_sale_is_rejected(): void
    {
        // A valid ITN for sale A, posted to sale B's endpoint. The m_payment_id cross-check is what
        // stops one payment from settling a second, unrelated sale.
        $this->postItn($this->itnBody(['m_payment_id' => UrlUtils::encodeId($this->sale->id + 1000)]))
            ->assertStatus(400);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_an_itn_from_an_unrecognised_source_still_settles(): void
    {
        // THE production shape, and the reason this check is advisory rather than a gate.
        //
        // $request->ip() is only the buyer-facing address when Laravel trusts the upstream proxy, and
        // config/trustedproxy.php trusts none unless IS_NEXUS is set. So on a selfhost install behind
        // Cloudflare or Docker this is the proxy's address and can never match the published Payfast
        // hosts. Rejecting on it took the buyer's money and issued no ticket, on every single payment,
        // with nothing but a log line to show for it - and it could not be reproduced on the hosted
        // install, which does set IS_NEXUS.
        config(['payments.payfast.itn_hosts' => ['203.0.113.7']]);

        $this->postItn($this->itnBody())->assertNoContent();

        $this->assertSame('paid', $this->sale->fresh()->status);
    }

    public function test_a_failure_to_resolve_any_host_does_not_block_settlement(): void
    {
        // Same reasoning for a DNS blip. The endpoint is not left open by this: confirmsPayment()
        // still has to get a VALID back from Payfast itself, which is strictly stronger than an
        // address allowlist and is what the tests below pin.
        config(['payments.payfast.itn_hosts' => []]);

        $this->postItn($this->itnBody())->assertNoContent();

        $this->assertSame('paid', $this->sale->fresh()->status);
    }

    public function test_a_forged_itn_from_an_unrecognised_source_is_still_refused(): void
    {
        // The other half of demoting the address check: with the source no longer a gate, an attacker
        // posting from anywhere must still be stopped by the checks that matter. Payfast declining to
        // confirm the payload is the decisive one.
        config(['payments.payfast.itn_hosts' => ['203.0.113.7']]);
        $this->validationBody = 'INVALID';

        $this->postItn($this->itnBody())->assertStatus(400);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_an_itn_payfast_will_not_confirm_is_rejected(): void
    {
        // The check an attacker cannot influence even with a leaked passphrase and a spoofed source.
        $this->validationBody = 'INVALID';

        $this->postItn($this->itnBody())->assertStatus(400);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_a_failed_confirmation_request_does_not_settle(): void
    {
        $this->validationBody = '';
        $this->validationStatus = 500;

        $this->postItn($this->itnBody())->assertStatus(400);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_a_short_payment_lands_in_amount_mismatch_rather_than_paid(): void
    {
        // Signed and genuine, but for less than the sale owes. Marking it paid would hand over the
        // tickets; ignoring it would lose the money silently. It goes to the owner instead.
        $this->postItn($this->itnBody(['amount_gross' => '250.00']))->assertNoContent();

        $this->assertSame('amount_mismatch', $this->sale->fresh()->status);
        $this->assertSame(
            0.0,
            (float) AnalyticsEventsDaily::where('event_id', $this->event->id)->sum('revenue'),
        );
    }

    public function test_a_duplicate_itn_settles_only_once(): void
    {
        // Payfast retries, so this is a normal day rather than an attack.
        $this->postItn($this->itnBody())->assertNoContent();
        $this->postItn($this->itnBody())->assertNoContent();

        $this->assertSame('paid', $this->sale->fresh()->status);

        // The number that would be wrong if idempotency failed: revenue booked twice.
        $this->assertSame(
            300.0,
            (float) AnalyticsEventsDaily::where('event_id', $this->event->id)->sum('revenue'),
        );
    }

    public function test_a_non_complete_status_settles_nothing(): void
    {
        foreach (['FAILED', 'PENDING', 'CANCELLED'] as $status) {
            $this->postItn($this->itnBody(['payment_status' => $status]))->assertNoContent();

            // Left unpaid for ReleaseTickets to expire on its own schedule. Anything else would take
            // the seats out of circulation on a payment that never happened.
            $this->assertSame('unpaid', $this->sale->fresh()->status, $status.' must not settle');
        }
    }

    public function test_a_payment_for_a_released_sale_is_escalated_not_just_logged(): void
    {
        // The money-with-no-ticket shape: an Instant EFT can go PENDING then COMPLETE, and the sale
        // can be expired by ReleaseTickets in between. Not reviving it is correct (the seats are
        // gone) - but the first version answered 204 and logged it at INFO under the word "settled",
        // which is how a buyer loses real money with no alert anywhere. It must be reported so
        // hosted installs see it in Sentry; the 204 stays because a retry cannot fix it.
        \Illuminate\Support\Facades\Exceptions::fake();

        $this->sale->status = 'expired';
        $this->sale->save();

        $this->postItn($this->itnBody())->assertNoContent();

        $this->assertSame('expired', $this->sale->fresh()->status);

        \Illuminate\Support\Facades\Exceptions::assertReported(
            fn (\RuntimeException $e) => str_contains($e->getMessage(), 'no longer be honoured')
        );
    }

    public function test_an_amount_mismatch_is_not_escalated_to_sentry(): void
    {
        // Mismatches are parked for review and AdminAlertService already counts them - reporting
        // every one would bury the genuinely stranded payments the exception exists to surface.
        \Illuminate\Support\Facades\Exceptions::fake();

        $this->postItn($this->itnBody(['amount_gross' => '250.00']))->assertNoContent();

        $this->assertSame('amount_mismatch', $this->sale->fresh()->status);

        \Illuminate\Support\Facades\Exceptions::assertNotReported(\RuntimeException::class);
    }

    public function test_a_released_sale_is_never_revived(): void
    {
        // Expiry already gave the seats back, and marking paid does not re-take them, so an ITN that
        // lands after the window must not oversell the event.
        $this->sale->status = 'expired';
        $this->sale->save();

        $this->postItn($this->itnBody())->assertNoContent();

        $this->assertSame('expired', $this->sale->fresh()->status);
    }

    // ------------------------------------------- installation-wide credentials (selfhost)

    /**
     * Point the installation at its own Payfast account, as a selfhost operator would in .env, and
     * clear this owner's own columns so the fallback is what settles.
     */
    private function installWideAccount(bool $clearOwner = true): void
    {
        config([
            'app.hosted' => false,
            'payments.payfast.merchant_id' => '20000200',
            'payments.payfast.merchant_key' => 'platform-merchant-key',
            'payments.payfast.passphrase' => 'platform-passphrase',
            'payments.payfast.sandbox' => true,
            'payments.payfast.payment_types' => null,
        ]);

        if ($clearOwner) {
            $this->owner->forceFill([
                'payfast_merchant_id' => null,
                'payfast_merchant_key' => null,
                'payfast_passphrase' => null,
            ])->save();

            // The Sale already holds a stale relation from setUp's checkout.
            $this->sale->refresh()->load('event.user');
        }
    }

    public function test_an_itn_on_the_install_wide_account_settles_the_sale(): void
    {
        $this->installWideAccount();

        $body = $this->itnBody(['merchant_id' => '20000200'], passphrase: 'platform-passphrase');

        $this->postItn($body)->assertNoContent();

        $this->assertSame('paid', $this->sale->fresh()->status);
    }

    public function test_the_install_wide_passphrase_does_not_settle_another_merchants_itn(): void
    {
        // The merchant id selects which passphrase is checked, so an ITN naming an account neither
        // this owner nor the installation holds is refused before any signature work happens.
        $this->installWideAccount();

        $body = $this->itnBody(['merchant_id' => '99999999'], passphrase: 'platform-passphrase');

        $this->postItn($body)->assertStatus(400);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_an_itn_on_the_install_wide_account_still_needs_the_right_passphrase(): void
    {
        $this->installWideAccount();

        $body = $this->itnBody(['merchant_id' => '20000200'], passphrase: 'not-the-passphrase');

        $this->postItn($body)->assertStatus(400);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_an_in_flight_payment_survives_the_owner_connecting_their_own_account(): void
    {
        // Instant EFT clears slowly, so an ITN can land hours after checkout. If the owner connects
        // their own Payfast account in that window, verifying against only the set a FRESH checkout
        // would use would reject a notification the installation's account really did sign: the buyer
        // charged, the seats held, and no ticket ever issued.
        //
        // setUp()'s sale is only a fixture - it was checked out before any of this, on the owner's own
        // account. What matters below is the state at ITN time: the installation supplies an account,
        // the owner supplies another, and the notification names the installation's.
        $this->installWideAccount();

        $body = $this->itnBody(['merchant_id' => '20000200'], passphrase: 'platform-passphrase');

        // ...and only now does the owner connect their own.
        $this->owner->forceFill([
            'payfast_merchant_id' => '10000100',
            'payfast_merchant_key' => '46f0cd694581a',
            'payfast_passphrase' => self::PASSPHRASE,
        ])->save();

        $this->sale->refresh()->load('event.user');

        $this->postItn($body)->assertNoContent();

        $this->assertSame('paid', $this->sale->fresh()->status);
    }

    public function test_an_owner_with_their_own_account_still_settles_on_it(): void
    {
        // The reverse of the above, and the case that must not regress: with both sets in play, the
        // owner's own account settles its own payments.
        $this->installWideAccount(clearOwner: false);

        $this->postItn($this->itnBody())->assertNoContent();

        $this->assertSame('paid', $this->sale->fresh()->status);
    }

    public function test_an_itn_is_refused_when_the_install_provides_nothing_and_the_owner_disconnects(): void
    {
        $this->owner->forceFill([
            'payfast_merchant_id' => null,
            'payfast_merchant_key' => null,
            'payfast_passphrase' => null,
        ])->save();

        $this->sale->refresh()->load('event.user');

        // The body, not just the status: every rejection here is a 400, and "not configured" is the
        // one that tells an operator the account went away rather than that somebody is forging
        // notifications. Getting the generic merchant-mismatch line instead would send them hunting
        // for an attacker that does not exist.
        $response = $this->postItn($this->itnBody());

        $response->assertStatus(400);
        $this->assertSame('not configured', $response->getContent());

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }

    public function test_two_accounts_sharing_a_merchant_id_both_get_their_signature_tried(): void
    {
        // An operator rotates PAYFAST_PASSPHRASE in .env while the owner still holds the old one in
        // their profile, so both candidate sets name the SAME merchant id with different passphrases.
        // Selecting by merchant id and stopping at the first match would check an in-flight ITN
        // against the wrong passphrase and reject it: buyer charged, ticket never issued.
        $this->installWideAccount(clearOwner: false);

        // Same merchant id as the owner's, different passphrase.
        config([
            'payments.payfast.merchant_id' => '10000100',
            'payments.payfast.passphrase' => 'rotated-passphrase',
        ]);

        $this->sale->refresh()->load('event.user');

        // Signed by the installation's rotated passphrase; the owner's set is tried first and fails.
        $body = $this->itnBody(['merchant_id' => '10000100'], passphrase: 'rotated-passphrase');

        $this->postItn($body)->assertNoContent();

        $this->assertSame('paid', $this->sale->fresh()->status);
    }

    public function test_a_shared_merchant_id_still_refuses_a_passphrase_neither_side_holds(): void
    {
        // The flip side: trying both candidates must not become "accept anything from this merchant".
        $this->installWideAccount(clearOwner: false);

        config([
            'payments.payfast.merchant_id' => '10000100',
            'payments.payfast.passphrase' => 'rotated-passphrase',
        ]);

        $this->sale->refresh()->load('event.user');

        $body = $this->itnBody(['merchant_id' => '10000100'], passphrase: 'neither-of-them');

        $this->postItn($body)->assertStatus(400);

        $this->assertSame('unpaid', $this->sale->fresh()->status);
    }
}
