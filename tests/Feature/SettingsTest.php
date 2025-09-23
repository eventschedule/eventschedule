<?php

namespace Tests\Feature;

use App\Mail\TemplatePreview;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Mail\Message;
use Mockery;
use RuntimeException;
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

    public function test_admin_can_send_test_mail_successfully(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);

        config([
            'mail.default' => 'log',
            'mail.mailers.smtp.host' => 'smtp.initial.test',
            'mail.mailers.smtp.port' => 1025,
            'mail.mailers.smtp.username' => 'initial-user',
            'mail.mailers.smtp.password' => 'initial-pass',
            'mail.mailers.smtp.encryption' => null,
            'mail.from.address' => 'initial@example.com',
            'mail.from.name' => 'Initial Name',
        ]);

        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function ($body, $callback) use ($admin) {
                $this->assertSame(__('messages.test_email_body'), $body);
                $this->assertIsCallable($callback);

                $message = Mockery::mock(Message::class);
                $message->shouldReceive('to')->once()->with($admin->email)->andReturnSelf();
                $message->shouldReceive('subject')->once()->with(__('messages.test_email_subject'))->andReturnSelf();

                $callback($message);

                return true;
            })
            ->andReturnNull();

        $mailer = $this->makeMailerStub([]);

        Mail::shouldReceive('mailer')->once()->andReturn($mailer);

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
            ->postJson('/settings/email/test', $payload);

        $response->assertOk();

        $response->assertJson(function (AssertableJson $json) use ($payload) {
            $json
                ->where('status', 'success')
                ->where('message', __('messages.test_email_sent'))
                ->where('logs', function (array $logs) use ($payload) {
                    return in_array('From address: ' . $payload['mail_from_address'], $logs, true)
                        && in_array('Mail driver did not report any delivery failures.', $logs, true);
                })
                ->missing('failures')
                ->etc();
        });

        $this->assertSame('log', config('mail.default'));
        $this->assertSame('smtp.initial.test', config('mail.mailers.smtp.host'));
        $this->assertSame(1025, config('mail.mailers.smtp.port'));
        $this->assertSame('initial-user', config('mail.mailers.smtp.username'));
        $this->assertSame('initial-pass', config('mail.mailers.smtp.password'));
        $this->assertNull(config('mail.mailers.smtp.encryption'));
        $this->assertSame('initial@example.com', config('mail.from.address'));
        $this->assertSame('Initial Name', config('mail.from.name'));
    }

    public function test_test_mail_rebuilds_mailer_with_updated_configuration(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.initial.test',
            'mail.mailers.smtp.port' => 1025,
            'mail.mailers.smtp.username' => 'initial-user',
            'mail.mailers.smtp.password' => 'initial-pass',
            'mail.mailers.smtp.encryption' => null,
            'mail.from.address' => 'initial@example.com',
            'mail.from.name' => 'Initial Name',
        ]);

        if (app()->bound('mail.manager')) {
            app('mail.manager')->purge('smtp');
        }

        app()->forgetInstance('mailer');

        if (method_exists(app(), 'forgetResolvedInstance')) {
            app()->forgetResolvedInstance('mailer');
        }

        $initialMailer = app('mail.manager')->mailer('smtp');
        $this->assertSame('smtp.initial.test', $this->getMailerHost($initialMailer));

        $resolvedInitialMailer = app('mailer');
        $this->assertSame($initialMailer, $resolvedInitialMailer);
        $this->assertSame('smtp.initial.test', $this->getMailerHost($resolvedInitialMailer));

        $originalFacade = Mail::getFacadeRoot();

        Mail::swap(new class($this, $admin)
        {
            public function __construct(private SettingsTest $testCase, private User $user)
            {
            }

            public function raw(string $body, callable $callback): void
            {
                $this->testCase->assertSame(__('messages.test_email_body'), $body);
                $this->testCase->assertIsCallable($callback);

                $managerMailer = app('mail.manager')->mailer('smtp');
                $this->testCase->assertSame('smtp.mailtrap.io', $this->testCase->getMailerHost($managerMailer));

                $resolvedMailer = app('mailer');
                $this->testCase->assertSame('smtp.mailtrap.io', $this->testCase->getMailerHost($resolvedMailer));

                $message = Mockery::mock(Message::class);
                $message->shouldReceive('to')->once()->with($this->user->email)->andReturnSelf();
                $message->shouldReceive('subject')->once()->with(__('messages.test_email_subject'))->andReturnSelf();

                $callback($message);
            }
        });

        try {
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
                ->postJson('/settings/email/test', $payload);

            $response->assertOk();

            $restoredManagerMailer = app('mail.manager')->mailer('smtp');
            $this->assertSame('smtp.initial.test', $this->getMailerHost($restoredManagerMailer));

            $restoredResolvedMailer = app('mailer');
            $this->assertSame('smtp.initial.test', $this->getMailerHost($restoredResolvedMailer));
        } finally {
            Mail::swap($originalFacade);

            if (app()->bound('mail.manager')) {
                app('mail.manager')->purge('smtp');
            }

            app()->forgetInstance('mailer');

            if (method_exists(app(), 'forgetResolvedInstance')) {
                app()->forgetResolvedInstance('mailer');
            }
        }
    }

    public function test_test_mail_reports_mail_driver_failures(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);

        config([
            'mail.default' => 'log',
            'mail.mailers.smtp.host' => 'smtp.initial.test',
            'mail.mailers.smtp.port' => 1025,
            'mail.mailers.smtp.username' => 'initial-user',
            'mail.mailers.smtp.password' => 'initial-pass',
            'mail.mailers.smtp.encryption' => null,
            'mail.from.address' => 'initial@example.com',
            'mail.from.name' => 'Initial Name',
        ]);

        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function ($body, $callback) use ($admin) {
                $this->assertSame(__('messages.test_email_body'), $body);
                $this->assertIsCallable($callback);

                $message = Mockery::mock(Message::class);
                $message->shouldReceive('to')->once()->with($admin->email)->andReturnSelf();
                $message->shouldReceive('subject')->once()->with(__('messages.test_email_subject'))->andReturnSelf();

                $callback($message);

                return true;
            })
            ->andReturnNull();

        $mailer = $this->makeMailerStub(['failed@example.com']);

        Mail::shouldReceive('mailer')->once()->andReturn($mailer);

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
            ->postJson('/settings/email/test', $payload);

        $response->assertStatus(500);

        $response->assertJson(function (AssertableJson $json) {
            $json
                ->where('status', 'error')
                ->where('message', __('messages.test_email_failed'))
                ->where('error', __('messages.test_email_failures'))
                ->where('failures', ['failed@example.com'])
                ->where('logs', function (array $logs) {
                    return in_array('Mail driver reported failures for the following recipients:', $logs, true)
                        && in_array(' - failed@example.com', $logs, true);
                })
                ->etc();
        });

        $this->assertSame('log', config('mail.default'));
        $this->assertSame('smtp.initial.test', config('mail.mailers.smtp.host'));
        $this->assertSame(1025, config('mail.mailers.smtp.port'));
        $this->assertSame('initial-user', config('mail.mailers.smtp.username'));
        $this->assertSame('initial-pass', config('mail.mailers.smtp.password'));
        $this->assertNull(config('mail.mailers.smtp.encryption'));
        $this->assertSame('initial@example.com', config('mail.from.address'));
        $this->assertSame('Initial Name', config('mail.from.name'));
    }

    public function test_test_mail_reports_transport_exceptions(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);

        config([
            'mail.default' => 'log',
            'mail.mailers.smtp.host' => 'smtp.initial.test',
            'mail.mailers.smtp.port' => 1025,
            'mail.mailers.smtp.username' => 'initial-user',
            'mail.mailers.smtp.password' => 'initial-pass',
            'mail.mailers.smtp.encryption' => null,
            'mail.from.address' => 'initial@example.com',
            'mail.from.name' => 'Initial Name',
        ]);

        Mail::shouldReceive('raw')
            ->once()
            ->withAnyArgs()
            ->andThrow(new RuntimeException('SMTP connection refused'));

        Mail::shouldReceive('mailer')->never();

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
            ->postJson('/settings/email/test', $payload);

        $response->assertStatus(500);

        $response->assertJson(function (AssertableJson $json) {
            $json
                ->where('status', 'error')
                ->where('message', __('messages.test_email_failed'))
                ->where('error', 'SMTP connection refused')
                ->where('logs', function (array $logs) {
                    $hasExceptionMessage = false;
                    $hasExceptionClass = false;

                    foreach ($logs as $line) {
                        if (str_contains($line, 'Encountered exception while sending the test email: SMTP connection refused')) {
                            $hasExceptionMessage = true;
                        }

                        if (str_contains($line, 'Exception class: ' . RuntimeException::class)) {
                            $hasExceptionClass = true;
                        }
                    }

                    return $hasExceptionMessage && $hasExceptionClass;
                })
                ->missing('failures')
                ->etc();
        });

        $this->assertSame('log', config('mail.default'));
        $this->assertSame('smtp.initial.test', config('mail.mailers.smtp.host'));
        $this->assertSame(1025, config('mail.mailers.smtp.port'));
        $this->assertSame('initial-user', config('mail.mailers.smtp.username'));
        $this->assertSame('initial-pass', config('mail.mailers.smtp.password'));
        $this->assertNull(config('mail.mailers.smtp.encryption'));
        $this->assertSame('initial@example.com', config('mail.from.address'));
        $this->assertSame('Initial Name', config('mail.from.name'));
    }

    public function test_test_mail_requires_authenticated_user_email(): void
    {
        $admin = User::factory()->create(['email' => null]);

        config([
            'mail.default' => 'log',
            'mail.mailers.smtp.host' => 'smtp.initial.test',
            'mail.mailers.smtp.port' => 1025,
            'mail.mailers.smtp.username' => 'initial-user',
            'mail.mailers.smtp.password' => 'initial-pass',
            'mail.mailers.smtp.encryption' => null,
            'mail.from.address' => 'initial@example.com',
            'mail.from.name' => 'Initial Name',
        ]);

        Mail::shouldReceive('raw')->never();
        Mail::shouldReceive('mailer')->never();

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
            ->postJson('/settings/email/test', $payload);

        $response->assertStatus(422);

        $response->assertJson(function (AssertableJson $json) {
            $json
                ->where('status', 'error')
                ->where('message', __('messages.test_email_failed'))
                ->where('error', __('messages.test_email_missing_user'))
                ->where('logs', ['No authenticated user email address was available for the test message.'])
                ->missing('failures')
                ->etc();
        });

        $this->assertSame('log', config('mail.default'));
        $this->assertSame('smtp.initial.test', config('mail.mailers.smtp.host'));
        $this->assertSame(1025, config('mail.mailers.smtp.port'));
        $this->assertSame('initial-user', config('mail.mailers.smtp.username'));
        $this->assertSame('initial-pass', config('mail.mailers.smtp.password'));
        $this->assertNull(config('mail.mailers.smtp.encryption'));
        $this->assertSame('initial@example.com', config('mail.from.address'));
        $this->assertSame('Initial Name', config('mail.from.name'));
    }

    public function test_mail_template_test_uses_configured_mail_settings(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User',
        ]);

        config([
            'mail.default' => 'log',
            'mail.mailers.smtp.host' => 'smtp.initial.test',
            'mail.mailers.smtp.port' => 1025,
            'mail.mailers.smtp.username' => 'initial-user',
            'mail.mailers.smtp.password' => 'initial-pass',
            'mail.mailers.smtp.encryption' => null,
            'mail.from.address' => 'initial@example.com',
            'mail.from.name' => 'Initial Name',
            'mail.disable_delivery' => true,
        ]);

        Setting::setGroup('mail', [
            'mailer' => 'smtp',
            'host' => 'smtp.mailtrap.io',
            'port' => '2525',
            'username' => 'mailer@example.com',
            'password' => 'secret-password',
            'encryption' => 'tls',
            'from_address' => 'no-reply@example.com',
            'from_name' => 'Event Schedule',
            'disable_delivery' => '0',
        ]);

        $pendingMail = Mockery::mock();
        $pendingMail->shouldReceive('send')
            ->once()
            ->withArgs(function ($mailable) {
                $this->assertInstanceOf(TemplatePreview::class, $mailable);
                $this->assertSame('smtp.mailtrap.io', config('mail.mailers.smtp.host'));
                $this->assertSame(2525, config('mail.mailers.smtp.port'));
                $this->assertSame('mailer@example.com', config('mail.mailers.smtp.username'));
                $this->assertSame('secret-password', config('mail.mailers.smtp.password'));
                $this->assertSame('tls', config('mail.mailers.smtp.encryption'));
                $this->assertSame('no-reply@example.com', config('mail.from.address'));
                $this->assertSame('Event Schedule', config('mail.from.name'));
                $this->assertSame('smtp', config('mail.default'));
                $this->assertFalse(config('mail.disable_delivery'));

                return true;
            })
            ->andReturnNull();

        Mail::shouldReceive('to')
            ->once()
            ->with($admin->email, $admin->name)
            ->andReturn($pendingMail);

        $mailer = $this->makeMailerStub([]);

        Mail::shouldReceive('mailer')->once()->andReturn($mailer);

        $response = $this
            ->actingAs($admin)
            ->postJson('/settings/email-templates/claim_role/test');

        $response->assertOk();

        $response->assertJson(function (AssertableJson $json) {
            $json
                ->where('status', 'success')
                ->where('message', __('messages.test_email_sent'))
                ->where('failures', [])
                ->etc();
        });

        $this->assertSame('log', config('mail.default'));
        $this->assertSame('smtp.initial.test', config('mail.mailers.smtp.host'));
        $this->assertSame(1025, config('mail.mailers.smtp.port'));
        $this->assertSame('initial-user', config('mail.mailers.smtp.username'));
        $this->assertSame('initial-pass', config('mail.mailers.smtp.password'));
        $this->assertNull(config('mail.mailers.smtp.encryption'));
        $this->assertSame('initial@example.com', config('mail.from.address'));
        $this->assertSame('Initial Name', config('mail.from.name'));
        $this->assertTrue(config('mail.disable_delivery'));
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

    protected function makeMailerStub(array $failures): object
    {
        return new class($failures)
        {
            public function __construct(private array $failures)
            {
            }

            public function failures(): array
            {
                return $this->failures;
            }
        };
    }

    protected function getMailerHost($mailer): ?string
    {
        if (! is_object($mailer) || ! method_exists($mailer, 'getSymfonyTransport')) {
            return null;
        }

        $transport = $mailer->getSymfonyTransport();

        if (! is_object($transport) || ! method_exists($transport, 'getHost')) {
            return null;
        }

        return $transport->getHost();
    }
}
