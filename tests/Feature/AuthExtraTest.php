<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class AuthExtraTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_google_social_login_creates_user(): void
    {
        $socialUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialUser->shouldReceive('getId')->andReturn('google-oauth-123');
        $socialUser->shouldReceive('getEmail')->andReturn('newgoogler@gmail.com');
        $socialUser->shouldReceive('getName')->andReturn('Google Newcomer');
        $socialUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.png');
        $socialUser->user = ['locale' => 'en'];

        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('getting-started', absolute: false));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'newgoogler@gmail.com',
            'google_oauth_id' => 'google-oauth-123',
            'signup_intent' => 'organizer',
        ]);
    }

    public function test_google_login_authenticates_existing_oauth_user(): void
    {
        $existing = User::factory()->create([
            'email' => 'existing-google@gmail.com',
            'google_oauth_id' => 'google-existing-9',
        ]);

        $socialUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialUser->shouldReceive('getId')->andReturn('google-existing-9');
        $socialUser->shouldReceive('getEmail')->andReturn('existing-google@gmail.com');
        $socialUser->shouldReceive('getName')->andReturn('Existing');
        $socialUser->shouldReceive('getAvatar')->andReturn(null);
        $socialUser->user = ['locale' => 'en'];

        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($existing);
    }

    public function test_custom_labels_saved(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'new_subdomain' => $role->subdomain,
            'timezone' => $role->timezone,
            'custom_labels' => [
                ['value' => 'Performers', 'value_en' => 'Performers'],
                ['value' => 'Stages', 'value_en' => 'Stages'],
            ],
        ]);

        $labels = $role->fresh()->custom_labels;
        $this->assertNotEmpty($labels);
        $this->assertStringContainsString('Performers', json_encode($labels));
    }
}
