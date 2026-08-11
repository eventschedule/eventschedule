<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\DigitalOceanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * DigitalOcean has no per-domain endpoint: changing an app's domains means PUTting the whole app
 * spec back, and every spec PUT creates a new App Platform deployment. Re-provision used to call
 * removeDomain() then addDomain(), so a single click redeployed the production app twice and the
 * second call ran while the container serving that very request was being replaced - which is how
 * the admin panel answered its own re-provision with a 503 from DigitalOcean's edge.
 *
 * It also ignored the boolean both calls returned, so a rejected domain was reported as success and
 * only reverted to "Setup failed" when the five-minute sync ran. That is what hid a plain DNS
 * misconfiguration from the person trying to diagnose it.
 *
 * These tests pin: one spec write per operation, no write when nothing would change, the failure
 * reason surfacing instead of a success flash, and the request payload staying exactly as it was.
 */
class CustomDomainProvisioningTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * The domain list the fake DigitalOcean app currently holds, mutated by PUTs so a test can
     * exercise a real read-modify-write cycle rather than a single canned response.
     */
    private array $domains = [];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.digitalocean.api_token' => 'test-token',
            'services.digitalocean.app_id' => 'app-123',
        ]);
    }

    /**
     * Stand in for the App Platform app. GET returns the current spec; PUT stores whatever spec is
     * sent, so state accumulates across calls the way the real API does.
     *
     * @param  array<int, array<string, string>>  $domains  the app's starting spec.domains
     * @param  int  $putStatus  status to answer spec updates with
     */
    private function fakeDigitalOceanApp(array $domains = [], int $putStatus = 200, array $putBody = []): void
    {
        $this->domains = $domains ?: [['domain' => 'eventschedule.com', 'type' => 'PRIMARY', 'zone' => '']];

        Http::fake([
            'api.digitalocean.com/v2/apps/*' => function ($request) use ($putStatus, $putBody) {
                if ($request->method() === 'PUT') {
                    if ($putStatus >= 400) {
                        return Http::response($putBody, $putStatus);
                    }

                    $this->domains = $request->data()['spec']['domains'] ?? [];

                    return Http::response(['app' => ['spec' => ['domains' => $this->domains]]], 200);
                }

                return Http::response(['app' => [
                    'spec' => ['name' => 'eventschedule', 'domains' => $this->domains],
                    'domains' => array_map(
                        fn ($domain) => ['spec' => $domain, 'phase' => 'ACTIVE'],
                        $this->domains,
                    ),
                ]], 200);
            },
        ]);
    }

    /**
     * How many times we asked DigitalOcean to rewrite the app spec. Every one of these is a
     * deployment, so this is the number that matters. assertSentCount would also count the GET.
     */
    private function specWrites(): int
    {
        return Http::recorded(fn ($request) => $request->method() === 'PUT')->count();
    }

    /**
     * The spec.domains array from the last PUT we sent.
     */
    private function lastWrittenDomains(): array
    {
        $writes = Http::recorded(fn ($request) => $request->method() === 'PUT');

        return $writes->last()[0]->data()['spec']['domains'] ?? [];
    }

    private function createDirectDomainRole(string $host = 'tenant-domain.test'): Role
    {
        return $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Custom Domain Venue',
            'custom_domain' => 'https://'.$host,
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'failed',
        ]);
    }

    /**
     * Re-provision as an admin. The admin middleware binds the session to a confirmed password.
     */
    private function reprovision(Role $role)
    {
        return $this->actingAs($this->createOwner(admin: true))
            ->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->post(route('admin.domains.reprovision', $role));
    }

    public function test_reprovisioning_a_missing_domain_takes_exactly_one_spec_write(): void
    {
        $this->fakeDigitalOceanApp();
        $role = $this->createDirectDomainRole();

        $this->reprovision($role)->assertRedirect();

        // The 503 pin: the old remove-then-add pair wrote the spec twice, redeploying the app
        // underneath the request that asked for it.
        $this->assertSame(1, $this->specWrites());

        $hosts = array_column($this->lastWrittenDomains(), 'domain');
        $this->assertContains('tenant-domain.test', $hosts);
        $this->assertContains('eventschedule.com', $hosts, 'The app\'s own domain must survive the rewrite.');

        $role->refresh();
        $this->assertSame('pending', $role->custom_domain_status);
        $this->assertNull($role->custom_domain_error);
    }

    public function test_reprovisioning_an_already_registered_domain_writes_nothing(): void
    {
        $this->fakeDigitalOceanApp([
            ['domain' => 'eventschedule.com', 'type' => 'PRIMARY', 'zone' => ''],
            ['domain' => 'tenant-domain.test', 'type' => 'PRIMARY', 'zone' => ''],
        ]);
        $role = $this->createDirectDomainRole();

        $response = $this->reprovision($role);

        // Nothing would change, so there is no reason to spend a deployment on it.
        $this->assertSame(0, $this->specWrites());
        $response->assertSessionHas('success', __('messages.reprovision_already_registered'));
    }

    public function test_a_rejected_domain_records_the_reason_instead_of_reporting_success(): void
    {
        $this->fakeDigitalOceanApp(
            putStatus: 422,
            putBody: ['id' => 'bad_request', 'message' => 'domain tenant-domain.test is not resolvable'],
        );
        $role = $this->createDirectDomainRole();

        $response = $this->reprovision($role);

        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');

        $role->refresh();
        $this->assertSame('failed', $role->custom_domain_status);
        $this->assertStringContainsString('not resolvable', (string) $role->custom_domain_error);
    }

    public function test_a_successful_add_clears_a_previously_recorded_error(): void
    {
        $this->fakeDigitalOceanApp();
        $role = $this->createDirectDomainRole();
        $role->forceFill(['custom_domain_error' => 'HTTP 422: something old'])->save();

        $this->reprovision($role);

        $role->refresh();
        $this->assertSame('pending', $role->custom_domain_status);
        $this->assertNull($role->custom_domain_error);
    }

    public function test_the_spec_payload_is_unchanged(): void
    {
        $this->fakeDigitalOceanApp();

        app(DigitalOceanService::class)->addDomain('payload-check.test');

        $written = collect($this->lastWrittenDomains())->firstWhere('domain', 'payload-check.test');

        // The DNS was the bug, not the payload. Adding a domain still sends exactly what it always
        // sent, so a future "cleanup" of these two fields has to be a deliberate decision.
        $this->assertSame(['domain' => 'payload-check.test', 'type' => 'PRIMARY', 'zone' => ''], $written);
    }

    public function test_a_domain_change_removes_and_adds_in_a_single_write(): void
    {
        $this->fakeDigitalOceanApp([
            ['domain' => 'eventschedule.com', 'type' => 'PRIMARY', 'zone' => ''],
            ['domain' => 'old-domain.test', 'type' => 'PRIMARY', 'zone' => ''],
        ]);

        $this->assertTrue(
            app(DigitalOceanService::class)->syncDomains(['new-domain.test'], ['old-domain.test'])
        );

        // The owner-facing save path used to spend two deployments on one domain change.
        $this->assertSame(1, $this->specWrites());

        $hosts = array_column($this->domains, 'domain');
        $this->assertContains('new-domain.test', $hosts);
        $this->assertNotContains('old-domain.test', $hosts);
    }

    public function test_sequential_provisions_do_not_clobber_each_other(): void
    {
        $this->fakeDigitalOceanApp();
        $service = app(DigitalOceanService::class);

        $service->addDomain('first-tenant.test');
        $service->addDomain('second-tenant.test');

        // Each add re-reads the spec, so the second must not drop the first one's domain.
        $hosts = array_column($this->domains, 'domain');
        $this->assertContains('first-tenant.test', $hosts);
        $this->assertContains('second-tenant.test', $hosts);
        $this->assertContains('eventschedule.com', $hosts);
    }

    public function test_re_adding_the_same_domain_does_not_duplicate_it(): void
    {
        $this->fakeDigitalOceanApp();
        $service = app(DigitalOceanService::class);

        $service->addDomain('idempotent.test');
        $service->addDomain('idempotent.test');

        $this->assertSame(1, $this->specWrites(), 'The second add had nothing to change.');
        $this->assertCount(1, array_filter(
            $this->domains,
            fn ($domain) => $domain['domain'] === 'idempotent.test',
        ));
    }

    public function test_removing_a_domain_that_is_not_registered_writes_nothing(): void
    {
        $this->fakeDigitalOceanApp();

        $this->assertTrue(app(DigitalOceanService::class)->removeDomain('never-added.test'));
        $this->assertSame(0, $this->specWrites());
    }

    public function test_a_failed_spec_read_is_reported_rather_than_swallowed(): void
    {
        Http::fake([
            'api.digitalocean.com/v2/apps/*' => Http::response(['message' => 'Unable to authenticate you'], 401),
        ]);

        $service = app(DigitalOceanService::class);

        $this->assertFalse($service->addDomain('unauthorized.test'));
        $this->assertStringContainsString('Unable to authenticate you', (string) $service->lastError());
        $this->assertSame(0, $this->specWrites());
    }

    /**
     * syncDomains() reports failure by RETURNING FALSE, never by throwing, so the try/catch the
     * remove path relied on was unreachable. Discarding the bool meant a failed removal nulled
     * every custom_domain* column and flashed success while the hostname stayed live in the DO
     * app spec - with nothing left in the database to retry from.
     */
    public function test_a_failed_removal_keeps_the_domain_on_the_role(): void
    {
        $this->fakeDigitalOceanApp([
            ['domain' => 'eventschedule.com', 'type' => 'PRIMARY', 'zone' => ''],
            ['domain' => 'tenant-domain.test', 'type' => 'PRIMARY', 'zone' => ''],
        ], putStatus: 422, putBody: ['message' => 'spec is invalid']);

        $role = $this->createDirectDomainRole();

        $this->actingAs($this->createOwner(admin: true))
            ->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->post(route('admin.domains.remove', $role))
            ->assertSessionHas('error');

        $role->refresh();

        $this->assertSame('https://tenant-domain.test', $role->custom_domain,
            'the record is the only way back to a hostname still live in the app spec');
        $this->assertSame('failed', $role->custom_domain_status);
    }

    /**
     * getApp() returns [] for any 2xx whose body lacks an `app` key - a proxy answering with
     * HTML, a 204, an envelope change. With an empty spec the PUT replaced the whole production
     * app spec with one containing nothing but `domains`: no services, no envs, no ingress.
     */
    public function test_a_2xx_without_an_app_spec_writes_nothing(): void
    {
        Http::fake([
            'api.digitalocean.com/v2/apps/*' => Http::response(['unexpected' => 'envelope'], 200),
        ]);

        $service = app(DigitalOceanService::class);

        $this->assertFalse($service->addDomain('tenant.test'));
        $this->assertSame(0, $this->specWrites(), 'an empty spec must never be written back');
    }

    /**
     * A tenant claiming the platform apex is refused at the form, but the ADD is a silent no-op
     * (the apex is already in the spec, so nothing changes and syncDomains returns true) - which
     * is exactly why nothing surfaced until the schedule was deleted and removeDomain() dropped
     * the operator's own hostname out of the app spec.
     */
    public function test_the_platform_hostname_is_left_in_the_spec_rather_than_removed(): void
    {
        config(['app.url' => 'https://myschedules.io']);
        $this->fakeDigitalOceanApp([['domain' => 'myschedules.io', 'type' => 'PRIMARY', 'zone' => '']]);

        $service = app(DigitalOceanService::class);

        // A no-op, not a failure: we never added it, so not removing it is the right outcome.
        // Returning false made a legacy row unremovable while the delete went ahead anyway.
        $this->assertTrue($service->removeDomain('myschedules.io'));
        $this->assertSame(0, $this->specWrites());
        $this->assertSame(
            ['myschedules.io'],
            array_column($this->domains, 'domain'),
            'the platform apex must still be in the spec'
        );
    }

    /** Adding it, though, must fail loudly - silence sent the owner to a certificate that never comes. */
    public function test_registering_the_platform_hostname_fails_rather_than_no_ops(): void
    {
        config(['app.url' => 'https://myschedules.io']);
        $this->fakeDigitalOceanApp([['domain' => 'myschedules.io', 'type' => 'PRIMARY', 'zone' => '']]);

        $service = app(DigitalOceanService::class);

        $this->assertFalse($service->addDomain('myschedules.io'));
        $this->assertStringContainsString('platform', (string) $service->lastError());
        $this->assertSame(0, $this->specWrites());
    }

    /**
     * And the rule the form applies refuses it up front, on ANY base domain rather than just
     * eventschedule.com - which is the whole gap for an operator running their own platform.
     */
    public function test_the_platform_hostname_is_reserved_on_any_install(): void
    {
        config(['app.url' => 'https://myschedules.io']);

        $this->assertTrue(Role::isReservedCustomDomainHost('myschedules.io'));
        $this->assertTrue(Role::isReservedCustomDomainHost('MySchedules.IO'), 'case must not matter');
        $this->assertTrue(Role::isReservedCustomDomainHost('tenant.myschedules.io'));

        // Still ours, whatever app.url says.
        $this->assertTrue(Role::isReservedCustomDomainHost('foo.eventschedule.com'));

        // A genuine tenant domain is untouched.
        $this->assertFalse(Role::isReservedCustomDomainHost('gigs.example.org'));
        $this->assertFalse(Role::isReservedCustomDomainHost('notmyschedules.io'));
        $this->assertFalse(Role::isReservedCustomDomainHost(null));
    }
}
