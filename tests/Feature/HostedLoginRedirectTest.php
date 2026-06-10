<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

class HostedLoginRedirectTest extends TestCase
{
    /**
     * The app_subdomain middleware and defaultHostedSessionDomain() read config
     * at call time, so runtime overrides exercise them without rebooting the
     * application (env-based overrides are unreliable here: Dotenv re-applies
     * .env values it loaded itself on every application refresh).
     */
    private function configure(array $overrides = []): void
    {
        config(array_merge([
            'app.hosted' => true,
            'app.is_testing' => false,
            'app.env' => 'production',
            'app.url' => 'https://eventschedule.test',
        ], $overrides));
    }

    public function test_hosted_login_on_bare_domain_redirects_to_app_subdomain(): void
    {
        $this->configure();

        $this->get('https://eventschedule.test/login')
            ->assertRedirect('https://app.eventschedule.test/login');

        // www host and query-string preservation (?pa= pending-action token)
        $this->get('https://www.eventschedule.test/login?pa=tok123')
            ->assertRedirect('https://app.eventschedule.test/login?pa=tok123');
    }

    public function test_hosted_login_on_app_subdomain_is_served(): void
    {
        $this->configure();

        $this->get('https://app.eventschedule.test/login')->assertOk();
    }

    public function test_selfhosted_guest_pages_are_served_without_redirect(): void
    {
        $this->configure(['app.hosted' => false]);

        // /reset-password renders a plain view with no database access
        // (/login calls User::exists() when selfhosted, making it DB-dependent).
        $this->get('/reset-password')->assertOk();
    }

    public function test_hosted_session_domain_defaults_to_base_domain(): void
    {
        $this->configure();
        config(['session.domain' => null]);

        AppServiceProvider::defaultHostedSessionDomain();

        $this->assertSame('.eventschedule.test', config('session.domain'));
    }

    public function test_explicit_session_domain_is_respected(): void
    {
        $this->configure();
        config(['session.domain' => '.custom.test']);

        AppServiceProvider::defaultHostedSessionDomain();

        $this->assertSame('.custom.test', config('session.domain'));
    }

    public function test_selfhosted_session_domain_is_not_defaulted(): void
    {
        $this->configure(['app.hosted' => false]);
        config(['session.domain' => null]);

        AppServiceProvider::defaultHostedSessionDomain();

        $this->assertNull(config('session.domain'));
    }

    public function test_session_domain_is_not_defaulted_for_undotted_or_ip_hosts(): void
    {
        $this->configure(['app.url' => 'http://localhost']);
        config(['session.domain' => null]);

        AppServiceProvider::defaultHostedSessionDomain();

        $this->assertNull(config('session.domain'));

        $this->configure(['app.url' => 'http://192.168.1.10']);

        AppServiceProvider::defaultHostedSessionDomain();

        $this->assertNull(config('session.domain'));
    }
}
