<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AdminAlertService;
use App\Services\AppUpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * System > App Update in the admin panel, the nav badge that advertises it, and the
 * app:make-admin escape hatch that gets a locked-out operator to it.
 *
 * All three exist because of issue #106: the self-updater lived only in Settings, the docs and
 * marketing site said it was "in your admin panel", and a selfhost user who lost the Settings
 * section had no second way in and no way to grant themselves admin.
 *
 * app.is_nexus, app.is_testing and app.hosted are pinned in every test. phpunit.xml pins
 * IS_NEXUS/APP_TESTING true and CI writes IS_HOSTED=true into .env, so nothing here may rely
 * on an ambient default.
 */
class AdminAppUpdateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function selfhost(array $overrides = []): void
    {
        config(array_merge([
            'app.is_nexus' => false,
            'app.is_testing' => false,
            'app.hosted' => false,
            'self-update.version_installed' => 'v1.0.100',
        ], $overrides));

        AdminAlertService::flush();
    }

    private function adminActing(User $admin)
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
    }

    public function test_an_admin_can_open_the_app_update_page(): void
    {
        $this->selfhost();
        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.101', 600);

        $response = $this->adminActing($this->createOwner(true))->get(route('admin.app_update'));

        $response->assertOk();
        $response->assertSee('v1.0.100', false);
        $response->assertSee('v1.0.101', false);
        $response->assertSee(route('admin.app_update.run'), false);
    }

    public function test_the_page_says_up_to_date_and_offers_no_update_button(): void
    {
        $this->selfhost();
        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.100', 600);

        $response = $this->adminActing($this->createOwner(true))->get(route('admin.app_update'));

        $response->assertOk();
        // false: the admin views use @lang, which echoes unescaped, so the apostrophe in
        // "You're up to date!" is not entity-encoded on the page.
        $response->assertSee(__('messages.up_to_date'), false);
        $response->assertDontSee(route('admin.app_update.run'), false);
    }

    public function test_the_page_404s_on_nexus(): void
    {
        $this->selfhost(['app.is_nexus' => true]);

        $this->adminActing($this->createOwner(true))->get(route('admin.app_update'))->assertNotFound();
    }

    public function test_a_non_admin_cannot_reach_the_page(): void
    {
        $this->selfhost();

        $this->actingAs($this->createOwner())->get(route('admin.app_update'))->assertRedirect();
    }

    public function test_the_nav_badge_appears_only_when_a_newer_version_is_cached(): void
    {
        $this->selfhost();

        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.101', 600);
        AdminAlertService::flush();
        $this->assertSame(1, AdminAlertService::badges()['tab']['app-update']['count'] ?? 0);

        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.100', 600);
        AdminAlertService::flush();
        $this->assertArrayNotHasKey('app-update', AdminAlertService::badges()['tab']);
    }

    public function test_the_nav_badge_never_calls_github(): void
    {
        // The badge composer runs on EVERY admin page render, and unauthenticated GitHub allows
        // 60 calls an hour for the whole install. A cold cache must read "no update", not block
        // the nav on a network round trip.
        $this->selfhost();
        Cache::forget(AppUpdateService::CACHE_KEY);
        Http::fake();

        AdminAlertService::flush();
        $badges = AdminAlertService::badges();

        Http::assertNothingSent();
        $this->assertArrayNotHasKey('app-update', $badges['tab']);
    }

    public function test_the_badge_is_silent_on_nexus(): void
    {
        $this->selfhost(['app.is_nexus' => true]);
        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.101', 600);
        AdminAlertService::flush();

        $this->assertArrayNotHasKey('app-update', AdminAlertService::badges()['tab']);
    }

    public function test_a_failed_version_check_is_not_an_available_update(): void
    {
        // The old code wrote the literal string 'Error: failed to check version' into the value
        // the view compared with !=, so every GitHub outage rendered an Update button.
        $this->selfhost();
        $service = app(AppUpdateService::class);

        Cache::put(AppUpdateService::CACHE_KEY, false, 600);
        $this->assertNull($service->cachedVersionAvailable());
        $this->assertFalse($service->isUpdateAvailable());

        Cache::put(AppUpdateService::CACHE_KEY, 'Error: failed to check version', 600);
        $this->assertNull($service->cachedVersionAvailable());
        $this->assertFalse($service->isUpdateAvailable());

        Cache::forget(AppUpdateService::CACHE_KEY);
        $this->assertFalse($service->isUpdateAvailable());
    }

    public function test_an_install_ahead_of_the_latest_release_is_not_offered_a_downgrade(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.124']);
        Cache::put(AppUpdateService::CACHE_KEY, 'v1.0.123', 600);

        $this->assertFalse(app(AppUpdateService::class)->isUpdateAvailable());
    }

    public function test_the_version_prefix_is_normalized_before_comparing(): void
    {
        $this->selfhost(['self-update.version_installed' => 'v1.0.100']);
        Cache::put(AppUpdateService::CACHE_KEY, '1.0.100', 600);

        $this->assertFalse(app(AppUpdateService::class)->isUpdateAvailable());
    }

    public function test_the_post_update_clear_never_wipes_the_application_cache(): void
    {
        // cache:clear (and optimize:clear, which contains it) would release the Cache::lock
        // mutex that serialises Stripe installment charges, drop 30-day sms_signup tokens and
        // in-flight verification codes, and hand a throttled attacker a fresh budget.
        $source = file_get_contents(app_path('Services/AppUpdateService.php'));

        $this->assertStringNotContainsString("'cache:clear'", $source);
        $this->assertStringNotContainsString("'optimize:clear'", $source);
        $this->assertStringContainsString("'config:clear'", $source);
        $this->assertStringContainsString("'clear-compiled'", $source);
    }

    public function test_the_updater_package_post_update_hook_is_left_empty(): void
    {
        // UpdaterServiceProvider registers config('self-update.artisan_commands.post_update')
        // entries by ['class'], so an entry shaped like a command signature resolves null and
        // fatals the first Artisan::call() of any request. Nothing in the package calls
        // postUpdateArtisanCommands() anyway.
        $this->assertSame([], config('self-update.artisan_commands.post_update'));
    }

    public function test_make_admin_promotes_a_user_who_is_not_id_1(): void
    {
        $first = $this->createOwner(true);
        $second = $this->createOwner();
        $this->assertFalse($second->isAdmin());

        $this->artisan('app:make-admin', ['email' => $second->email])->assertExitCode(0);

        $this->assertTrue($second->fresh()->isAdmin());
        $this->assertTrue($first->fresh()->isAdmin(), 'the existing admin is untouched');
    }

    public function test_make_admin_works_when_nobody_is_an_admin(): void
    {
        $user = $this->createOwner();
        User::query()->update(['is_admin' => false]);

        $this->artisan('app:make-admin', ['email' => $user->email])->assertExitCode(0);

        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_make_admin_bypasses_mass_assignment_without_weakening_it(): void
    {
        $user = $this->createOwner();

        $this->artisan('app:make-admin', ['email' => $user->email])->assertExitCode(0);

        $this->assertTrue($user->fresh()->is_admin);
        $this->assertContains('is_admin', (new User)->getGuarded(), 'is_admin must stay guarded');
    }

    public function test_make_admin_reports_an_unknown_email_without_promoting_anyone(): void
    {
        $this->createOwner();

        $this->artisan('app:make-admin', ['email' => 'nobody@example.test'])->assertExitCode(1);

        $this->assertSame(0, User::where('is_admin', true)->count());
    }

    public function test_make_admin_is_idempotent(): void
    {
        $user = $this->createOwner(true);

        $this->artisan('app:make-admin', ['email' => $user->email])->assertExitCode(0);

        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_make_admin_lists_the_current_admins(): void
    {
        $admin = $this->createOwner(true);

        $this->artisan('app:make-admin')->expectsOutputToContain($admin->email)->assertExitCode(0);
    }

    public function test_make_admin_runs_on_nexus(): void
    {
        // Deliberately not gated: shell access already outranks the flag, and gating would lock
        // out the operator of an install where nobody is an admin - the case it exists for.
        config(['app.is_nexus' => true]);
        $user = $this->createOwner();

        $this->artisan('app:make-admin', ['email' => $user->email])->assertExitCode(0);

        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_a_promoted_user_can_reach_the_admin_portal(): void
    {
        $this->selfhost();
        $user = $this->createOwner();
        $this->artisan('app:make-admin', ['email' => $user->email]);

        $this->adminActing($user->fresh())->get(route('admin.app_update'))->assertOk();
    }

    public function test_the_update_command_is_gated_on_nexus_not_hosted(): void
    {
        // It used to bail on config('app.hosted'), which left the self-hosted SaaS operator -
        // the one install type whose web route is admin-gated - with no command line either.
        $source = file_get_contents(app_path('Console/Commands/UpdateApp.php'));

        $this->assertStringContainsString("config('app.is_nexus')", $source);
        $this->assertStringNotContainsString("config('app.hosted')", $source);
        $this->assertStringNotContainsString('exit;', $source, 'exit; reports success to cron and kills phpunit');
    }

    public function test_the_update_command_refuses_on_nexus_with_a_failure_code(): void
    {
        config(['app.is_nexus' => true]);

        $this->artisan('app:update')->expectsOutputToContain('Not authorized')->assertExitCode(1);
    }

    public function test_check_version_no_ops_on_nexus(): void
    {
        config(['app.is_nexus' => true]);
        Http::fake();

        $this->artisan('app:check-version')->assertExitCode(0);

        Http::assertNothingSent();
    }
}
