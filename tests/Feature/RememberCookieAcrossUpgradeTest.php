<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A remember-me cookie written before the move to Laravel 12 still signs its visitor in.
 *
 * The cookie is "id|token|password hash". Laravel 11 wrote the hash as it was, and its recaller
 * never compared it (AuthenticateSession did, and only for an account with a password); 12's
 * recaller checks it against the account's password with hash_equals(), which throws on null.
 * An account made by Google or Facebook has no password, so its cookie ended in
 * an empty part, and from the first request after its session expired every page answered 500,
 * the sign-in page included, until the cookie (400 days) was cleared. User::getAuthPassword()
 * answers '' for such an account.
 */
class RememberCookieAcrossUpgradeTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function rememberedBy(User $user, string $hash): void
    {
        $user->forceFill(['remember_token' => 'remembered-token'])->save();

        $this->withCookie(auth()->guard('web')->getRecallerName(), $user->id.'|remembered-token|'.$hash);
    }

    public function test_an_account_with_no_password_is_remembered_by_a_cookie_from_before_the_upgrade(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['password' => null])->save();
        $this->rememberedBy($user, '');

        // A new owner is sent on to the getting-started page; what matters is that it is not a 500.
        $this->assertLessThan(500, $this->get(route('home'))->status());

        $this->assertAuthenticatedAs($user);
    }

    public function test_an_account_with_a_password_is_remembered_by_a_cookie_from_before_the_upgrade(): void
    {
        $user = $this->createOwner();
        $this->rememberedBy($user, $user->fresh()->password);

        // A new owner is sent on to the getting-started page; what matters is that it is not a 500.
        $this->assertLessThan(500, $this->get(route('home'))->status());

        $this->assertAuthenticatedAs($user);
    }

    public function test_an_account_with_no_password_is_remembered_by_a_cookie_written_now(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['password' => null])->save();
        $this->rememberedBy($user, auth()->guard('web')->hashPasswordForCookie($user->getAuthPassword()));

        // A new owner is sent on to the getting-started page; what matters is that it is not a 500.
        $this->assertLessThan(500, $this->get(route('home'))->status());

        $this->assertAuthenticatedAs($user);
    }

    public function test_an_empty_password_never_matches_one_typed(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['password' => null])->save();

        $this->assertFalse(auth()->guard('web')->getProvider()->validateCredentials($user->fresh(), ['password' => '']));
    }
}
