<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false, 'app.hosted' => false]);
        Cache::flush();
    }

    public function test_settings_page_is_displayed_for_admin(): void
    {
        $admin = User::factory()->create();

        $response = $this
            ->actingAs($admin)
            ->get('/settings');

        $response->assertOk();
        $response->assertSee('Email Settings');
        $response->assertSee('General Settings');
    }

    public function test_non_admin_cannot_access_settings_page(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/settings');

        $response->assertForbidden();
    }

    public function test_admin_can_update_mail_settings(): void
    {
        $admin = User::factory()->create();

        $payload = [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.mailtrap.io',
            'mail_port' => 2525,
            'mail_username' => 'mailer@example.com',
            'mail_password' => 'secret-password',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'no-reply@example.com',
            'mail_from_name' => 'Event Schedule',
        ];

        $response = $this
            ->actingAs($admin)
            ->patch('/settings/email', $payload);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/email');

        $this->assertDatabaseHas('settings', [
            'group' => 'mail',
            'key' => 'host',
            'value' => 'smtp.mailtrap.io',
        ]);

        $mailSettings = Setting::forGroup('mail');

        $this->assertSame('smtp', $mailSettings['mailer']);
        $this->assertSame('2525', $mailSettings['port']);
        $this->assertSame('secret-password', $mailSettings['password']);

        $this->assertSame('smtp.mailtrap.io', config('mail.mailers.smtp.host'));
        $this->assertSame(2525, config('mail.mailers.smtp.port'));
        $this->assertSame('mailer@example.com', config('mail.mailers.smtp.username'));
        $this->assertSame('secret-password', config('mail.mailers.smtp.password'));
        $this->assertSame('tls', config('mail.mailers.smtp.encryption'));
        $this->assertSame('no-reply@example.com', config('mail.from.address'));
        $this->assertSame('Event Schedule', config('mail.from.name'));
    }

    public function test_admin_can_update_general_settings(): void
    {
        $admin = User::factory()->create();

        $payload = [
            'public_url' => 'https://example.org',
        ];

        $response = $this
            ->actingAs($admin)
            ->patch('/settings/general', $payload);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/general');

        $this->assertDatabaseHas('settings', [
            'group' => 'general',
            'key' => 'public_url',
            'value' => 'https://example.org',
        ]);

        $this->assertSame('https://example.org', config('app.url'));
    }
}
