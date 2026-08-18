<?php

namespace Tests\Feature;

use App\Http\Middleware\RedirectToAppSubdomain;
use App\Services\AppUpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Settings > App Update visibility, which is issue #106.
 *
 * Commit b9aaf31e added auth()->user()?->isAdmin() to the gate. On a plain selfhost
 * users.is_admin is only ever set on user id 1, so every other signed-in user lost the only
 * in-app way to update - and because that button is the only in-app way to update, the fix
 * could not reach them either. Nothing pinned the behaviour, in part because the /update route
 * used to be registered conditionally and route('app.update') threw while rendering the
 * section under phpunit.
 *
 * Every test pins app.is_nexus, app.is_testing AND app.hosted. None of the three can be left
 * to the environment: phpunit.xml pins IS_NEXUS/APP_TESTING true, and CI additionally writes
 * IS_HOSTED=true into .env (.github/workflows/test.yml), so the "default" differs between a
 * developer's machine and the build.
 */
class SelfUpdateVisibilityTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * Pin the install type. Also primes the version cache so ProfileController never reaches
     * out to api.github.com from a test.
     */
    private function selfhost(array $overrides = []): void
    {
        config(array_merge([
            'app.is_nexus' => false,
            'app.is_testing' => false,
            'app.hosted' => false,
            'self-update.version_installed' => 'v1.0.100',
        ], $overrides));

        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.101', 600);
    }

    /**
     * Hosted mode sends every non-app.* host to the app subdomain, which is orthogonal to the
     * App Update gate under test here and would turn every assertion into a 302.
     */
    private function actingAsOnHosted(\App\Models\User $user)
    {
        return $this->withoutMiddleware(RedirectToAppSubdomain::class)->actingAs($user);
    }

    public function test_a_non_admin_sees_the_app_update_section_on_a_plain_selfhost(): void
    {
        // The #106 case exactly: a signed-in selfhost user who is not user id 1.
        $this->selfhost();
        $user = $this->createOwner();
        $this->assertFalse($user->isAdmin());

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('id="section-app"', false);
        $response->assertSee(__('messages.app_update'));
        $response->assertViewHas('version_installed', 'v1.0.100');
    }

    public function test_an_admin_sees_the_app_update_section_on_a_plain_selfhost(): void
    {
        $this->selfhost();

        $response = $this->actingAs($this->createOwner(true))->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('id="section-app"', false);
    }

    public function test_an_admin_sees_the_app_update_section_on_a_hosted_mode_selfhost(): void
    {
        $this->selfhost(['app.hosted' => true]);

        $response = $this->actingAsOnHosted($this->createOwner(true))->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('id="section-app"', false);
    }

    public function test_a_tenant_does_not_see_the_app_update_section_on_a_hosted_mode_selfhost(): void
    {
        // A customer on someone else's multi-tenant install must not be able to update the
        // whole platform.
        $this->selfhost(['app.hosted' => true]);

        $response = $this->actingAsOnHosted($this->createOwner())->get(route('profile.edit'));

        $response->assertOk();
        $response->assertDontSee('id="section-app"', false);
    }

    public function test_nobody_sees_the_app_update_section_on_nexus(): void
    {
        $this->selfhost(['app.is_nexus' => true, 'app.hosted' => true]);

        $response = $this->actingAsOnHosted($this->createOwner(true))->get(route('profile.edit'));

        $response->assertOk();
        $response->assertDontSee('id="section-app"', false);
    }

    public function test_the_controller_skips_the_version_lookup_when_the_section_is_hidden(): void
    {
        // If the controller and the Blade guard ever disagree, the cheap direction is a wasted
        // GitHub call and the expensive one is an undefined $version_installed 500.
        $this->selfhost(['app.hosted' => true]);

        $response = $this->actingAsOnHosted($this->createOwner())->get(route('profile.edit'));

        $response->assertOk();
        $response->assertViewMissing('version_installed');
    }

    /**
     * @dataProvider selfUpdateMatrix
     */
    public function test_can_self_update_matrix(bool $isNexus, bool $isTesting, bool $hosted, bool $isAdmin, bool $expected): void
    {
        config([
            'app.is_nexus' => $isNexus,
            'app.is_testing' => $isTesting,
            'app.hosted' => $hosted,
        ]);

        $this->assertSame($expected, can_self_update($this->createOwner($isAdmin)));
    }

    public static function selfUpdateMatrix(): array
    {
        return [
            // Plain selfhost: everyone, admin or not. This is what #106 restored.
            'plain selfhost, non-admin' => [false, false, false, false, true],
            'plain selfhost, admin' => [false, false, false, true, true],
            // Hosted-mode selfhost (a self-hosted SaaS): operator only.
            'hosted selfhost, non-admin' => [false, false, true, false, false],
            'hosted selfhost, admin' => [false, false, true, true, true],
            // eventschedule.com deploys from git.
            'nexus, admin' => [true, false, true, true, false],
            'nexus, non-admin' => [true, false, false, false, false],
            // The guard that keeps the test suite from shelling out to GitHub.
            'testing, admin' => [false, true, false, true, false],
            'testing, non-admin' => [false, true, false, false, false],
        ];
    }

    public function test_can_self_update_handles_a_signed_out_visitor(): void
    {
        config(['app.is_nexus' => false, 'app.is_testing' => false, 'app.hosted' => true]);
        $this->assertFalse(can_self_update(null));

        config(['app.hosted' => false]);
        $this->assertTrue(can_self_update(null));
    }

    public function test_the_update_route_is_registered_on_every_install(): void
    {
        // Registered unconditionally on purpose: a route-cached install would otherwise freeze
        // whatever the old registration-time condition evaluated to, and the Blade partial
        // calls route('app.update') whenever the section renders.
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('app.update'));
    }

    public function test_the_update_route_404s_where_self_update_is_not_allowed(): void
    {
        // is_nexus and is_testing both make can_self_update() false; the controller is what
        // enforces it now that the route itself is always registered.
        config(['app.is_nexus' => true, 'app.is_testing' => false, 'app.hosted' => false]);

        // An admin with a confirmed session, so the request reaches the controller whether or
        // not the 'admin' middleware was attached at boot (which depends on IS_HOSTED in .env,
        // and CI sets it true).
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->createOwner(true))
            ->post(route('app.update'))
            ->assertNotFound();
    }
}
