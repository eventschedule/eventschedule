<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_redirect_to_login_page(): void
    {
        config(['app.hosted' => false]);

        $response = $this->get('/sign_up');

        $response->assertStatus(302)->assertRedirect('/login');
    }

    public function test_registration_screen_can_be_rendered_in_hosted_app(): void
    {
        config(['app.hosted' => true]);

        $response = $this->get('/sign_up');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_in_hosted_app(): void
    {
        config(['app.hosted' => true]);

        $this->assertDatabaseCount('users', 0);

        $response = $this->post('/sign_up', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('home', absolute: false))
        ;

        $this->assertAuthenticated();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
        ]);
    }
}
