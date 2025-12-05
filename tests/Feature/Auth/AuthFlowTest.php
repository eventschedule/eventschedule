<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_flow_creates_verified_user_and_authenticates(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => true]);

        $response = $this->post('/sign_up', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'language_code' => 'en',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);

        $login = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $login->assertRedirect();
        $this->assertAuthenticatedAs($user);

        $logout = $this->post('/logout');
        $logout->assertRedirect();
        $this->assertGuest();
    }

    public function test_password_reset_request_is_sent_for_verified_users(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }
}
