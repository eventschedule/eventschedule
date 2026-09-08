<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Utils\GitHubUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The star count must never cost a page render an outbound call, and must never disappear because
 * GitHub is having a bad day.
 *
 * getStars() used to fetch inline from nine call sites - two view composers and seven
 * MarketingController actions - so a slow GitHub delayed real admin and marketing page loads, and
 * a failed lookup was retried five minutes later, forever. The fetch now belongs to the daily
 * app:check-github-stars command alone.
 */
class GitHubStarsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * An authenticated admin-portal page on a SELFHOST install, which is the only install type
     * whose footer carries the star badge at all - layouts/app-admin.blade.php puts it in the
     * `@else` of `@if (config('app.hosted'))`, and hosted shows a support-email line instead.
     *
     * Only app.hosted is moved. The private selfhost() helpers in AdminAppUpdateTest and its
     * siblings also clear app.is_testing, and selfhost_needs_setup() (app/helpers.php) returns
     * false only while hosted OR is_testing is true - so clearing both re-arms EnsureSelfhostSetup
     * and the request redirects to the setup wizard instead of rendering the footer.
     *
     * The layouts.app-admin composer resolves app('userRoles'), so the user needs a role.
     */
    private function selfhostAdminPage()
    {
        config(['app.hosted' => false]);

        $owner = $this->createOwner();
        $this->createRole($owner);

        return $this->actingAs($owner)->get(route('sales'));
    }

    /** The whole point: the read path is not allowed to touch the network. */
    public function test_reading_the_count_makes_no_request(): void
    {
        Http::fake();

        $this->assertNull(GitHubUtils::getStars(), 'Nothing has been stored yet.');

        Setting::set(GitHubUtils::STARS_KEY, '1234');

        $this->assertSame(1234, GitHubUtils::getStars());

        Http::assertNothingSent();
    }

    public function test_a_refresh_stores_the_count(): void
    {
        Http::fake([
            'api.github.com/*' => Http::response(['stargazers_count' => 987], 200),
        ]);

        $this->assertSame(987, GitHubUtils::refresh());
        $this->assertSame('987', Setting::where('key', GitHubUtils::STARS_KEY)->value('value'));
        $this->assertSame(987, GitHubUtils::getStars());
    }

    /**
     * The requirement, in one test: GitHub being down is invisible to visitors.
     *
     * A failed refresh must store NOTHING, so the last known count survives indefinitely rather
     * than being replaced by a failure marker that blanks the badge.
     */
    public function test_a_failed_refresh_leaves_the_last_known_count_alone(): void
    {
        Setting::set(GitHubUtils::STARS_KEY, '4321');

        Http::fake([
            'api.github.com/*' => Http::response('nope', 500),
        ]);

        $this->assertNull(GitHubUtils::refresh(), 'A failed refresh reports nothing new.');
        $this->assertSame(4321, GitHubUtils::getStars(), 'The stored count must survive the outage.');
    }

    /** A connection that never answers must not propagate out of the command either. */
    public function test_an_unreachable_github_does_not_throw(): void
    {
        Setting::set(GitHubUtils::STARS_KEY, '55');

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('timed out');
        });

        $this->assertNull(GitHubUtils::refresh());
        $this->assertSame(55, GitHubUtils::getStars());
    }

    /**
     * The read must not go through Setting::get().
     *
     * That method serves the settings map from Cache::rememberForever('site_settings'), and
     * Setting::set() invalidates it with Cache::forget - which on the file driver runs in the
     * scheduler's container and cannot reach the web container. A rememberForever map would then
     * serve a pre-write copy until the container was recreated on the next deploy, which is exactly
     * the cross-container failure a database row was chosen to avoid.
     *
     * A poisoned map is what a stale sibling container holds. The count must come back anyway.
     */
    public function test_the_read_bypasses_the_cached_settings_map(): void
    {
        Setting::set(GitHubUtils::STARS_KEY, '777');

        Cache::forever('site_settings', []);

        $this->assertSame(777, GitHubUtils::getStars());
    }

    /**
     * The regression this pairing exists to prevent.
     *
     * The refresh runs on nexus only, so a selfhost install will never have a count - and gating
     * the whole anchor on it removed the "star us" link from the one footer that shows it. The link
     * must render regardless; only the number is conditional.
     */
    public function test_the_selfhost_footer_links_to_github_without_a_count(): void
    {
        $response = $this->selfhostAdminPage();

        $response->assertOk();
        $response->assertSee('https://github.com/eventschedule/eventschedule"', false);
        $response->assertSee(__('messages.star_on_github'));

        // The star glyph rides with the number, so its absence is what proves no count rendered.
        $response->assertDontSee('text-yellow-500', false);
    }

    public function test_the_selfhost_footer_shows_the_count_when_there_is_one(): void
    {
        Setting::set(GitHubUtils::STARS_KEY, '1234');

        $response = $this->selfhostAdminPage();

        $response->assertOk();
        $response->assertSee(number_format(1234));
        $response->assertSee('text-yellow-500', false);
    }

    /**
     * A failed refresh has to leave a trace somewhere.
     *
     * $this->error() does not: CallbackEvent::execute() never captures output, so the scheduler
     * rail discards it, and ScheduledTaskRecorder reads the non-throwing FAILURE as a success.
     * Without the log line a permanently broken refresh is a frozen number and nothing else.
     */
    public function test_a_failed_refresh_is_logged(): void
    {
        config(['app.is_nexus' => true]);
        Http::fake([
            'api.github.com/*' => Http::response('nope', 503),
        ]);

        Log::spy();

        $this->artisan('app:check-github-stars')->assertFailed();

        Log::shouldHaveReceived('warning')
            ->withArgs(fn ($message) => str_contains($message, 'app:check-github-stars'))
            ->once();
    }

    public function test_the_command_no_ops_off_nexus(): void
    {
        config(['app.is_nexus' => false]);
        Http::fake();

        $this->artisan('app:check-github-stars')->assertSuccessful();

        Http::assertNothingSent();
        $this->assertNull(GitHubUtils::getStars());
    }

    public function test_the_command_refreshes_on_nexus(): void
    {
        config(['app.is_nexus' => true]);
        Http::fake([
            'api.github.com/*' => Http::response(['stargazers_count' => 2468], 200),
        ]);

        $this->artisan('app:check-github-stars')->assertSuccessful();

        $this->assertSame(2468, GitHubUtils::getStars());
    }

    /**
     * A non-throwing FAILURE is what ScheduledTaskRecorder reads as a success, so an unreachable
     * GitHub does not paint /admin/queue red. Same contract as app:check-version.
     */
    public function test_the_command_reports_failure_without_throwing_when_github_is_down(): void
    {
        config(['app.is_nexus' => true]);
        Http::fake([
            'api.github.com/*' => Http::response('nope', 503),
        ]);

        $this->artisan('app:check-github-stars')->assertFailed();
    }
}
