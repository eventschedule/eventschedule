<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function testUserRegistration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/sign_up')
                    ->assertSee('Sign Up')
                    ->type('name', 'Test User')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->check('terms')
                    ->press('SIGN UP')
                    ->pause(2000)
                    ->assertPathIs('/verify-email')
                    ->assertSee('Verify Your Email Address');

            // Verify user was created
            $this->assertDatabaseHas('users', [
                'email' => 'test@example.com',
                'name' => 'Test User'
            ]);
        });
    }

    public function testUserLogin(): void
    {
        // Create a verified user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->assertSee('Log In')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->press('LOG IN')
                    ->assertPathIs('/events')
                    ->assertSee('Test User');
        });
    }

    public function testInvalidLogin(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'invalid@example.com')
                    ->type('password', 'wrongpassword')
                    ->press('LOG IN')
                    ->assertPathIs('/login')
                    ->assertSee('These credentials do not match our records');
        });
    }

    public function testUserLogout(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/events')
                    ->assertAuthenticated()
                    ->press($user->name)
                    ->clickLink('Log Out')
                    ->assertPathIs('/login')
                    ->assertGuest();
        });
    }

    public function testPasswordReset(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/reset-password')
                    ->assertSee('Reset Password')
                    ->type('email', 'test@example.com')
                    ->press('Email Password Reset Link')
                    ->assertSee('We have emailed your password reset link');
        });
    }

    public function testEmailVerificationRequired(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null, // Unverified user
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/events')
                    ->assertPathIs('/verify-email')
                    ->assertSee('Verify Your Email Address');
        });
    }

    public function testRegisterValidation(): void
    {
        $this->browse(function (Browser $browser) {
            // Test empty fields
            $browser->visit('/sign_up')
                    ->press('SIGN UP')
                    ->assertSee('The name field is required')
                    ->assertSee('The email field is required')
                    ->assertSee('The password field is required');

            // Test invalid email
            $browser->type('name', 'Test User')
                    ->type('email', 'invalid-email')
                    ->type('password', '123')
                    ->press('SIGN UP')
                    ->assertSee('The email field must be a valid email address')
                    ->assertSee('The password field must be at least 8 characters');
        });
    }

    public function testDuplicateEmailRegistration(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/sign_up')
                    ->type('name', 'New User')
                    ->type('email', 'existing@example.com')
                    ->type('password', 'password123')
                    ->check('terms')
                    ->press('SIGN UP')
                    ->assertSee('The email has already been taken');
        });
    }
} 