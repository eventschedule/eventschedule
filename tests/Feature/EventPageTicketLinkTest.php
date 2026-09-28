<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureEmailIsVerified;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The "view your ticket" link on a public event page carries the sale's secret, the only credential
 * its ticket, QR code and booking routes ask for. A signed-in visitor gets it for a sale they own by
 * user_id, or for a guest sale made with their email - but only once the account has proved that
 * email. Matching an unverified address handed a guest buyer's ticket to anyone who signed up, or
 * changed their profile email, to it.
 */
class EventPageTicketLinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const BUYER = 'guest-buyer@gmail.com';

    /** @return array{0: string, 1: string} the event page URL and the guest sale's secret */
    private function guestSale(): array
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'cash']);
        $sale = $this->createSale($event, $role, [
            'email' => self::BUYER,
            'status' => 'paid',
            'secret' => strtolower(Str::random(32)),
        ]);

        return [$this->guestEventUrl($role, $event), $sale->secret];
    }

    private function account(bool $verified): User
    {
        $user = User::factory()->create([
            'email' => self::BUYER,
            'email_verified_at' => $verified ? now() : null,
        ]);

        return $user->fresh();
    }

    public function test_a_verified_account_with_the_buyers_email_sees_its_ticket_link(): void
    {
        [$url, $secret] = $this->guestSale();

        $this->actingAs($this->account(verified: true))
            ->get($url)
            ->assertOk()
            ->assertSee($secret);
    }

    /**
     * An unverified account never reaches this page today - EnsureEmailIsVerified sends every
     * signed-in request to /verify-email first - so the controller's own check is taken with that
     * middleware off. It is the controller that must not depend on it: that redirect is a UX
     * choice, the secret link is an authorisation one.
     */
    public function test_an_unverified_account_with_the_buyers_email_does_not(): void
    {
        [$url, $secret] = $this->guestSale();

        $this->withoutMiddleware(EnsureEmailIsVerified::class)
            ->actingAs($this->account(verified: false))
            ->get($url)
            ->assertOk()
            ->assertDontSee($secret);
    }

    public function test_an_unverified_account_is_sent_to_verify_its_email_first(): void
    {
        [$url, $secret] = $this->guestSale();

        $response = $this->actingAs($this->account(verified: false))->get($url);

        $response->assertRedirect(app_url(route('verification.notice', [], false)));
        $this->assertStringNotContainsString($secret, (string) $response->getContent());
    }

    public function test_a_signed_out_visitor_does_not(): void
    {
        [$url, $secret] = $this->guestSale();

        $this->get($url)->assertOk()->assertDontSee($secret);
    }
}
