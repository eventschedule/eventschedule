<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An account with two-factor is asked for its code on every door that signs a person in.
 *
 * The submit-your-event form signs a person in beside the event (EventController's inline sign-in).
 * It checked the password, the lockout and the block, and never asked whether the account has
 * two-factor: the password alone gave a full session. And on the challenge itself nothing counted
 * wrong codes against the ACCOUNT, only against the address, and signing in again renewed the
 * five-minute window, so somebody holding the password could go on guessing the six digits.
 *
 *   - the inline sign-in refuses an account with two-factor and sends it to the sign-in page;
 *   - five wrong codes close the challenge for that account for five minutes.
 *
 * A new door that signs a person in with a password asks hasTwoFactorEnabled() too.
 */
class TwoFactorDoorsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function withTwoFactor(User $user, string $password = 'password123'): User
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'two_factor_secret' => (new Google2FA)->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $user->fresh();
    }

    public function test_an_account_with_two_factor_is_not_signed_in_by_the_submit_form(): void
    {
        $curator = $this->createCurator($this->createOwner(), ['accept_requests' => true, 'require_account' => true, 'require_approval' => true, 'country_code' => 'us']);
        $user = $this->withTwoFactor($this->createOwner());

        $this->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), [
            'name' => 'Jazz Night', 'starts_at' => now()->addDays(10)->format('Y-m-d').' 19:15:00', 'duration' => 2,
            'ticket_currency_code' => 'USD', 'coupon_discount_type' => Event::DEFAULT_COUPON_DISCOUNT_TYPE, 'custom_field_values' => [],
            'account_mode' => 'login', 'account_email' => $user->email, 'account_password' => 'password123',
            'website' => '', 'venue_name' => 'The Blue Room', 'venue_city' => 'Springfield', 'venue_country_code' => 'us',
        ]);

        $this->assertGuest();
        $this->assertSame(0, Event::where('name', 'Jazz Night')->count());
    }

    public function test_wrong_two_factor_codes_are_counted_against_the_account(): void
    {
        $user = $this->withTwoFactor($this->createOwner());
        $session = ['login.id' => $user->id, 'login.expires' => now()->addMinutes(5)->timestamp];
        $right = (new Google2FA)->getCurrentOtp($user->two_factor_secret);
        $wrong = $right === '000000' ? '111111' : '000000';

        for ($i = 0; $i < 6; $i++) {
            $this->withSession($session)->post('/two-factor-challenge', ['code' => $wrong]);
        }
        $this->withSession($session)->post('/two-factor-challenge', ['code' => $right]);

        $this->assertGuest();
    }

    public function test_the_right_code_still_signs_in(): void
    {
        $user = $this->withTwoFactor($this->createOwner());
        $session = ['login.id' => $user->id, 'login.expires' => now()->addMinutes(5)->timestamp];

        $this->withSession($session)->post('/two-factor-challenge', ['code' => (new Google2FA)->getCurrentOtp($user->two_factor_secret)]);

        $this->assertAuthenticatedAs($user);
    }
}
