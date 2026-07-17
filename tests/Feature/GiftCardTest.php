<?php

namespace Tests\Feature;

use App\Mail\GiftCardReceipt;
use App\Mail\GiftCardRecipient;
use App\Models\GiftCard;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class GiftCardTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function enableGiftCards(Role $role, array $overrides = []): Role
    {
        foreach (array_merge([
            'gift_cards_enabled' => true,
            'gift_card_amounts' => [25, 50, 100],
            'gift_card_currency_code' => 'USD',
            'gift_card_valid_days' => 365,
            'gift_card_payment_method' => 'cash',
        ], $overrides) as $key => $value) {
            $role->{$key} = $value;
        }
        // The test suite runs with app.hosted=true, where gift cards require a
        // configured email channel (the recipient email is the delivery mechanism).
        $role->email_settings = [
            'host' => 'smtp.test.dev',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'mailer',
            'password' => 'secret',
            'from_address' => 'events@schedule.dev',
            'from_name' => 'Test Schedule',
        ];
        $role->save();

        return $role->fresh();
    }

    private function activeGiftCard(Role $role, array $attrs = []): GiftCard
    {
        $card = new GiftCard;
        $card->role_id = $role->id;
        $card->code = $attrs['code'] ?? GiftCard::generateCode();
        $card->secret = $attrs['secret'] ?? strtolower(Str::random(32));
        $card->amount = $attrs['amount'] ?? 50;
        $card->remaining_amount = $attrs['remaining_amount'] ?? ($attrs['amount'] ?? 50);
        $card->currency_code = $attrs['currency_code'] ?? 'USD';
        $card->status = $attrs['status'] ?? 'active';
        $card->payment_method = $attrs['payment_method'] ?? 'cash';
        $card->purchaser_name = $attrs['purchaser_name'] ?? 'Alice Buyer';
        $card->purchaser_email = $attrs['purchaser_email'] ?? 'alice@test.dev';
        $card->recipient_name = $attrs['recipient_name'] ?? 'Bob Recipient';
        $card->recipient_email = $attrs['recipient_email'] ?? 'bob@test.dev';
        $card->valid_days = $attrs['valid_days'] ?? null;
        $card->activated_at = $attrs['activated_at'] ?? ($card->status === 'active' ? now() : null);
        $card->expires_at = $attrs['expires_at'] ?? null;
        $card->save();

        return $card->fresh();
    }

    public function test_settings_saved_and_amounts_normalized(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'timezone' => $role->timezone,
            'email' => $role->email,
            'new_subdomain' => $role->subdomain,
            'gift_cards_enabled' => '1',
            'gift_card_amounts' => ['100', '25', '50', '25'], // unsorted + duplicate
            'gift_card_currency_code' => 'USD',
            'gift_card_valid_days' => '180',
            'gift_card_payment_method' => 'cash',
        ])->assertRedirect();

        $role->refresh();
        $this->assertTrue((bool) $role->gift_cards_enabled);
        // Normalized: unique, positive, sorted ascending
        $this->assertSame([25.0, 50.0, 100.0], array_map('floatval', $role->gift_card_amounts));
        $this->assertSame(180, (int) $role->gift_card_valid_days);
        $this->assertSame('cash', $role->gift_card_payment_method);
    }

    public function test_settings_pro_gated_when_hosted(): void
    {
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        // Free plan on hosted means isPro() is false, so the merge-guard must keep gift cards off.
        $role = $this->createRole($owner, 'venue', ['plan_type' => 'free', 'plan_expires' => null]);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'timezone' => $role->timezone,
            'email' => $role->email,
            'new_subdomain' => $role->subdomain,
            'gift_cards_enabled' => '1',
            'gift_card_amounts' => ['50'],
            'gift_card_currency_code' => 'USD',
            'gift_card_payment_method' => 'cash',
        ])->assertRedirect();

        $role->refresh();
        $this->assertFalse((bool) $role->gift_cards_enabled);
        $this->assertEmpty($role->gift_card_amounts);
    }

    public function test_purchase_page_404_when_disabled(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->get(route('gift_card.purchase', ['subdomain' => $role->subdomain]))
            ->assertNotFound();
    }

    public function test_purchase_page_renders_when_enabled(): void
    {
        $owner = $this->createOwner();
        $role = $this->enableGiftCards($this->createRole($owner));

        $this->get(route('gift_card.purchase', ['subdomain' => $role->subdomain]))
            ->assertOk();
    }

    public function test_cash_purchase_creates_unpaid_card(): void
    {
        $owner = $this->createOwner();
        $role = $this->enableGiftCards($this->createRole($owner));

        $this->post(route('gift_card.purchase.store', ['subdomain' => $role->subdomain]), [
            'amount' => 50,
            'purchaser_name' => 'Alice Buyer',
            'purchaser_email' => 'alice@test.dev',
            'recipient_name' => 'Bob Recipient',
            'recipient_email' => 'bob@test.dev',
            'message' => 'Happy birthday!',
        ]);

        $card = GiftCard::where('role_id', $role->id)->first();
        $this->assertNotNull($card);
        $this->assertSame('unpaid', $card->status);
        $this->assertSame(12, strlen($card->code));
        $this->assertSame(32, strlen($card->secret));
        $this->assertEquals(50, (float) $card->amount);
        $this->assertEquals(50, (float) $card->remaining_amount);
        $this->assertSame('USD', $card->currency_code);
        $this->assertSame(365, (int) $card->valid_days);
        $this->assertNull($card->expires_at); // not stamped until activation
        $this->assertSame('bob@test.dev', $card->recipient_email);
    }

    public function test_purchase_rejects_non_preset_amount(): void
    {
        $owner = $this->createOwner();
        $role = $this->enableGiftCards($this->createRole($owner));

        $this->post(route('gift_card.purchase.store', ['subdomain' => $role->subdomain]), [
            'amount' => 37, // not one of 25/50/100
            'purchaser_name' => 'Alice',
            'purchaser_email' => 'alice@test.dev',
            'recipient_name' => 'Bob',
            'recipient_email' => 'bob@test.dev',
        ])->assertSessionHas('error');

        $this->assertSame(0, GiftCard::where('role_id', $role->id)->count());
    }

    public function test_send_to_self_copies_buyer_into_recipient(): void
    {
        $owner = $this->createOwner();
        $role = $this->enableGiftCards($this->createRole($owner));

        $this->post(route('gift_card.purchase.store', ['subdomain' => $role->subdomain]), [
            'amount' => 25,
            'purchaser_name' => 'Alice Buyer',
            'purchaser_email' => 'alice@test.dev',
            'send_to_self' => '1',
        ]);

        $card = GiftCard::where('role_id', $role->id)->first();
        $this->assertNotNull($card);
        $this->assertSame('Alice Buyer', $card->recipient_name);
        $this->assertSame('alice@test.dev', $card->recipient_email);
    }

    public function test_mark_paid_activates_and_emails_recipient(): void
    {
        Mail::fake();
        config(['mail.default' => 'smtp']); // pass the transport guard (selfhost non-log)

        $owner = $this->createOwner();
        $role = $this->enableGiftCards($this->createRole($owner));
        $card = $this->activeGiftCard($role, [
            'status' => 'unpaid',
            'valid_days' => 365,
            'activated_at' => null,
            'recipient_email' => 'bob@test.dev',
            'purchaser_email' => 'alice@test.dev',
        ]);

        $this->actingAs($owner)->post(route('gift_card.action', ['gift_card_id' => UrlUtils::encodeId($card->id)]), [
            'action' => 'mark_paid',
        ])->assertOk();

        $card->refresh();
        $this->assertSame('active', $card->status);
        $this->assertNotNull($card->activated_at);
        $this->assertNotNull($card->expires_at); // stamped at activation
        $this->assertEqualsWithDelta(now()->addDays(365)->timestamp, $card->expires_at->timestamp, 120);

        Mail::assertSent(GiftCardRecipient::class, fn ($mail) => $mail->hasTo('bob@test.dev'));
        Mail::assertSent(GiftCardReceipt::class, fn ($mail) => $mail->hasTo('alice@test.dev'));
    }

    public function test_mark_paid_never_expires_when_valid_days_null(): void
    {
        Mail::fake();
        config(['mail.default' => 'smtp']);

        $owner = $this->createOwner();
        $role = $this->enableGiftCards($this->createRole($owner), ['gift_card_valid_days' => null]);
        $card = $this->activeGiftCard($role, ['status' => 'unpaid', 'valid_days' => null, 'activated_at' => null]);

        $this->actingAs($owner)->post(route('gift_card.action', ['gift_card_id' => UrlUtils::encodeId($card->id)]), [
            'action' => 'mark_paid',
        ])->assertOk();

        $card->refresh();
        $this->assertSame('active', $card->status);
        $this->assertNull($card->expires_at);
    }

    public function test_action_requires_ownership(): void
    {
        $owner = $this->createOwner();
        $role = $this->enableGiftCards($this->createRole($owner));
        $card = $this->activeGiftCard($role, ['status' => 'unpaid']);

        $stranger = $this->createOwner();

        $this->actingAs($stranger)->post(route('gift_card.action', ['gift_card_id' => UrlUtils::encodeId($card->id)]), [
            'action' => 'mark_paid',
        ])->assertStatus(403);

        $this->assertSame('unpaid', $card->fresh()->status);
    }

    public function test_view_page_requires_secret(): void
    {
        $owner = $this->createOwner();
        $role = $this->enableGiftCards($this->createRole($owner));
        $card = $this->activeGiftCard($role);

        $this->get(route('gift_card.view', ['gift_card_id' => UrlUtils::encodeId($card->id), 'secret' => $card->secret]))
            ->assertOk();

        $this->get(route('gift_card.view', ['gift_card_id' => UrlUtils::encodeId($card->id), 'secret' => 'wrong'.Str::random(28)]))
            ->assertStatus(403);
    }

    public function test_stripe_webhook_activates_card(): void
    {
        $owner = $this->createOwner();
        $role = $this->enableGiftCards($this->createRole($owner), ['gift_card_payment_method' => 'stripe']);
        $card = $this->activeGiftCard($role, ['status' => 'unpaid', 'amount' => 50, 'remaining_amount' => 50, 'valid_days' => 365, 'activated_at' => null]);

        $this->invokeStripeGiftCardHandler($card, 5000, 'usd', 'pi_test', false, null);

        $card->refresh();
        $this->assertSame('active', $card->status);
        $this->assertSame('pi_test', $card->transaction_reference);
    }

    public function test_stripe_webhook_flags_amount_mismatch(): void
    {
        $owner = $this->createOwner();
        $role = $this->enableGiftCards($this->createRole($owner), ['gift_card_payment_method' => 'stripe']);
        $card = $this->activeGiftCard($role, ['status' => 'unpaid', 'amount' => 50, 'remaining_amount' => 50]);

        $this->invokeStripeGiftCardHandler($card, 9900, 'usd', 'pi_bad', false, null);

        $this->assertSame('amount_mismatch', $card->fresh()->status);
    }

    public function test_stripe_webhook_flags_currency_mismatch(): void
    {
        $owner = $this->createOwner();
        $role = $this->enableGiftCards($this->createRole($owner), ['gift_card_payment_method' => 'stripe']);
        $card = $this->activeGiftCard($role, ['status' => 'unpaid', 'amount' => 50, 'remaining_amount' => 50, 'currency_code' => 'USD']);

        $this->invokeStripeGiftCardHandler($card, 5000, 'eur', 'pi_eur', false, null);

        $this->assertSame('amount_mismatch', $card->fresh()->status);
    }

    public function test_stripe_connect_account_mismatch_does_not_activate(): void
    {
        $owner = $this->createOwner();
        $owner->stripe_account_id = 'acct_victim';
        $owner->save();
        $role = $this->enableGiftCards($this->createRole($owner), ['gift_card_payment_method' => 'stripe']);
        $card = $this->activeGiftCard($role, ['status' => 'unpaid', 'amount' => 50, 'remaining_amount' => 50]);

        // A Connect-verified event whose top-level account is NOT the card's merchant must be rejected.
        $this->invokeStripeGiftCardHandler($card, 5000, 'usd', 'pi_attacker', true, 'acct_attacker');
        $this->assertSame('unpaid', $card->fresh()->status);

        // The genuine connected account activates it.
        $this->invokeStripeGiftCardHandler($card, 5000, 'usd', 'pi_ok', true, 'acct_victim');
        $this->assertSame('active', $card->fresh()->status);
    }

    private function invokeStripeGiftCardHandler(GiftCard $card, int $rawAmount, string $currency, string $reference, bool $verifiedViaConnect, ?string $eventAccount): void
    {
        $controller = app(\App\Http\Controllers\StripeController::class);
        $method = new \ReflectionMethod($controller, 'handleGiftCardPayment');
        $method->setAccessible(true);
        $method->invoke($controller, $card->fresh(), $rawAmount, $currency, $reference, $verifiedViaConnect, $eventAccount);
    }
}
