<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class RouteLoadTest extends TestCase
{
    use RefreshDatabase;

    /** Env values as they stood before forceEnv() overrode them, keyed by variable name. */
    private array $originalEnv = [];

    private function createUserWithSchedule(string $type = 'talent', string $subdomain = 'testtalent'): array
    {
        $user = User::factory()->create();

        $role = new Role;
        $role->subdomain = $subdomain;
        $role->user_id = $user->id;
        $role->type = $type;
        $role->name = 'Test '.ucfirst($type);
        $role->email = 'test@example.com';
        $role->save();

        $role->users()->attach($user->id, ['level' => 'owner']);

        return [$user, $role];
    }

    private function createAdminUser(): array
    {
        $user = User::factory()->create();
        $user->is_admin = true;
        $user->save();

        $role = new Role;
        $role->subdomain = 'admin'.strtolower(\Str::random(8));
        $role->user_id = $user->id;
        $role->type = 'venue';
        $role->name = 'Admin Schedule';
        $role->email = 'admin@example.com';
        $role->save();

        $role->users()->attach($user->id, ['level' => 'owner']);

        return [$user, $role];
    }

    private function assertRoutesLoad(array $urls, ?User $user = null, array $session = []): void
    {
        foreach ($urls as $url) {
            $request = $user ? $this->actingAs($user) : $this;

            if ($session) {
                $request = $request->withSession($session);
            }

            $response = $request->get($url);

            // getStatusCode(), not status(): TestResponse forwards unknown methods to the base
            // response, and a streamed response (the sitemap) is a plain Symfony StreamedResponse
            // without Laravel's status() helper.
            $status = $response->getStatusCode();

            // Name the exception, or a 500 that only happens on CI (where APP_DEBUG is false, so
            // nothing else in the build output identifies it) costs a round trip to diagnose.
            // Illuminate\Routing\Pipeline attaches it via withException(); read it only for a 500,
            // because the streamed sitemap response has no such property at all.
            $exception = $status >= 500 ? ($response->baseResponse->exception ?? null) : null;

            $this->assertTrue(
                $status < 500,
                "Route {$url} returned status {$status}"
                .($exception ? ' ('.get_class($exception).': '.$exception->getMessage().')' : '')
            );
        }
    }

    public function test_public_routes_load(): void
    {
        $this->assertRoutesLoad([
            '/login',
            '/sign_up',
            '/reset-password',
            '/sitemap.xml',
            '/sitemap.xml.gz',
            '/sitemap-pages.xml',
            '/unsubscribe',
            '/robots.txt',
            '/blog',
        ]);
    }

    public function test_marketing_routes_load(): void
    {
        $this->assertRoutesLoad([
            '/',
            '/features',
            '/pricing',
            '/about',
            '/examples',
            '/faq',
            '/why-create-account',
            '/open-source',
            '/use-cases',
            '/selfhost',
            '/saas',
            '/contact',
            '/privacy',
            '/terms-of-service',
            // 404s until an operator writes one, which assertRoutesLoad tolerates;
            // it is here to catch a 500 inside LegalController before that point.
            '/cookie-policy',
            '/self-hosting-terms-of-service',
            '/search',
            '/accessibility',

            // Feature pages
            '/features/ticketing',
            '/features/ai',
            '/features/calendar-sync',
            '/features/analytics',
            '/features/integrations',
            '/features/custom-fields',
            '/features/team-scheduling',
            '/features/sub-schedules',
            '/features/online-events',
            '/features/newsletters',
            '/features/recurring-events',
            '/features/embed-calendar',
            '/features/embed-tickets',
            '/features/fan-videos',
            '/features/polls',
            '/features/boost',
            '/features/private-events',
            '/features/event-graphics',
            '/features/white-label',
            '/features/custom-css',
            '/features/custom-domain',
            '/features/custom-labels',
            '/features/feedback',
            '/features/availability',
            '/features/gift-cards',
            '/features/carpool',

            // Integration pages
            '/google-calendar',
            '/outlook-calendar',
            '/caldav',
            '/stripe',
            '/invoiceninja',

            // Audience pages
            '/for-talent',
            '/for-venues',
            '/for-curators',
            '/for-musicians',
            '/for-djs',
            '/for-comedians',
            '/for-circus-acrobatics',
            '/for-magicians',
            '/for-spoken-word',
            '/for-bars',
            '/for-nightclubs',
            '/for-music-venues',
            '/for-theaters',
            '/for-dance-groups',
            '/for-theater-performers',
            '/for-food-trucks-and-vendors',
            '/for-comedy-clubs',
            '/for-restaurants',
            '/for-breweries-and-wineries',
            '/for-art-galleries',
            '/for-community-centers',
            '/for-fitness-and-yoga',
            '/for-workshop-instructors',
            '/for-visual-artists',
            '/for-farmers-markets',
            '/for-hotels-and-resorts',
            '/for-libraries',
            '/for-webinars',
            '/for-live-concerts',
            '/for-online-classes',
            '/for-virtual-conferences',
            '/for-live-qa-sessions',
            '/for-watch-parties',
            '/for-ai-agents',

            // Comparison pages
            '/compare',
            '/eventbrite-alternative',
            '/luma-alternative',
            '/ticket-tailor-alternative',
            '/google-calendar-alternative',
            '/meetup-alternative',
            '/dice-alternative',
            '/brown-paper-tickets-alternative',
            '/splash-alternative',
            '/sched-alternative',
            '/whova-alternative',
            '/accelevents-alternative',
            '/tito-alternative',
            '/addevent-alternative',
            '/pretix-alternative',
            '/humanitix-alternative',
            '/eventzilla-alternative',

            // Replacement pages
            '/replace',
            '/google-forms-replacement',
            '/mailchimp-replacement',
            '/canva-replacement',
            '/linktree-replacement',
            '/google-sheets-replacement',
            '/calendly-replacement',
            '/surveymonkey-replacement',
            '/doodle-replacement',
            '/qr-code-generator-replacement',
            '/squarespace-replacement',
            '/notion-replacement',
            '/trello-replacement',

            // Docs
            '/docs',
            '/docs/getting-started',
            '/docs/creating-schedules',
            '/docs/schedule-styling',
            '/docs/managing-schedules',
            '/docs/creating-events',
            '/docs/sharing',
            '/docs/tickets',
            '/docs/subscriptions',
            '/docs/gift-cards',
            '/docs/appointments',
            '/docs/event-graphics',
            '/docs/newsletters',
            '/docs/analytics',
            '/docs/account-settings',
            '/docs/boost',
            '/docs/ai-import',
            '/docs/scan-agenda',
            '/docs/referral-program',
            '/docs/selfhost',
            '/docs/selfhost/installation',
            '/docs/selfhost/stripe',
            '/docs/selfhost/google-calendar',
            '/docs/selfhost/microsoft-calendar',
            '/docs/selfhost/boost',
            '/docs/selfhost/admin',
            '/docs/selfhost/email',
            '/docs/selfhost/ai',
            '/docs/selfhost/accessibility',
            '/docs/saas',
            '/docs/saas/custom-domains',
            '/docs/saas/twilio',
            '/docs/saas/federation',
            '/docs/saas/monetization',
            '/docs/selfhost/federation',
            '/docs/developer/api',
            '/docs/developer/webhooks',
        ]);
    }

    public function test_ap_global_routes_load(): void
    {
        [$user] = $this->createUserWithSchedule();

        $this->assertRoutesLoad([
            '/dashboard',
            '/event',
            '/new/talent',
            '/new/venue',
            '/new/curator',
            '/settings',
            '/following',
            '/following/merge-venues',
            '/tickets',
            '/my-carpools',
            '/sales',
            '/analytics',
            '/newsletters',
            '/newsletters/create',
            '/newsletter-segments',
            '/newsletter-import',
            '/boost',
            '/boost/create',
            '/referrals',
            '/scan',
            '/checkin',
            '/waitlist',
        ], $user);
    }

    public function test_ap_schedule_routes_load(): void
    {
        [$user, $role] = $this->createUserWithSchedule('talent', 'testtalent');

        $this->assertRoutesLoad([
            '/testtalent/schedule',
            '/testtalent/availability',
            '/testtalent/requests',
            '/testtalent/followers',
            '/testtalent/team',
            '/testtalent/plan',
            '/testtalent/videos',
            '/testtalent/edit',
            '/testtalent/add-event',
            '/testtalent/events-graphic',
            '/testtalent/events-graphic/settings',
            '/testtalent/import',
            '/testtalent/import/ai',
            '/testtalent/import/eventbrite',
            '/testtalent/scan-agenda',
            '/testtalent/team/add-member',
            '/testtalent/followers/qr-code',
            '/testtalent/match-videos',
        ], $user);
    }

    public function test_selfhosted_gp_routes_load(): void
    {
        [$user, $role] = $this->createUserWithSchedule('talent', 'testtalent');

        $this->assertRoutesLoad([
            '/testtalent',
            '/testtalent/request',
            '/testtalent/follow',
            '/testtalent/guest-add',
            '/testtalent/booking-request',
            '/testtalent/feed/ical',
            '/testtalent/feed/rss',
        ]);
    }

    public function test_admin_routes_load(): void
    {
        [$user, $role] = $this->createAdminUser();

        $session = ['admin_password_confirmed_at' => now()->timestamp];

        $this->assertRoutesLoad([
            '/admin/dashboard',
            '/admin/users',
            '/admin/revenue',
            '/admin/analytics',
            '/admin/usage',
            '/admin/audit-log',
            '/admin/boost',
            '/admin/queue',
            '/admin/logs',
            '/admin/newsletters',
            '/admin/newsletters/create',
            '/admin/newsletter-segments',
            '/admin/legal',
        ], $user, $session);
    }

    public function test_hosted_gp_routes_load(): void
    {
        // Roll back RefreshDatabase transaction to release locks before app refresh
        $this->app['db']->connection()->rollBack();

        // Switch to hosted mode and rebuild the app with subdomain routing.
        //
        // forceEnv(), not putenv(): Laravel's Env repository reads $_SERVER and $_ENV BEFORE
        // getenv(), and phpunit.xml's <env name="APP_TESTING" value="true"/> lands in $_ENV, so
        // putenv('APP_TESTING=false') is outranked and config('app.is_testing') stays true.
        // routes/web.php gates the hosted subdomain group on `hosted && ! is_testing`, so the
        // group was never registered: the domain-less '/' that routes/web.php:1013 registers for
        // testing answered instead, and this test asserted against the marketing homepage while
        // claiming to cover the guest portal.
        $this->forceEnv('IS_HOSTED', 'true');
        $this->forceEnv('APP_TESTING', 'false');
        $this->refreshApplication();
        $this->app['db']->connection()->beginTransaction();

        try {
            [$user, $role] = $this->createUserWithSchedule('talent', 'testtalent');

            $baseUrl = 'http://testtalent.'.parse_url(config('app.url'), PHP_URL_HOST);

            $urls = [
                $baseUrl.'/',
                $baseUrl.'/request',
                $baseUrl.'/follow',
                $baseUrl.'/guest-add',
                $baseUrl.'/booking-request',
                $baseUrl.'/feed/ical',
                $baseUrl.'/feed/rss',
            ];

            // The whole point of the refresh: without it routes/web.php registers the OTHER half
            // and every URL below lands on a domain-less route, which passes for the wrong reason.
            $this->assertTrue(config('app.hosted'));
            $this->assertFalse(
                config('app.is_testing'),
                'The hosted subdomain group is gated on ! is_testing, so the domain-less routes '
                .'would answer instead and every assertion below would pass for the wrong reason.'
            );
            $this->assertSame(
                'role.view_guest',
                app('router')->getRoutes()->match(Request::create($baseUrl.'/'))->getName()
            );

            $this->assertRoutesLoad($urls);
        } finally {
            // refreshApplication() swapped the database manager, so the transaction opened above is
            // invisible to RefreshDatabase's teardown callback, which still holds the manager
            // captured before the refresh. Close it here. Left open, the connection sits idle in a
            // transaction holding metadata locks, and the next test class's migrate:fresh then
            // blocks on DROP TABLE for lock_wait_timeout - a year by default in MySQL - so the
            // suite hangs instead of failing.
            $connection = $this->app['db']->connection();

            if ($connection->getPdo() && $connection->getPdo()->inTransaction()) {
                $connection->rollBack();
            }

            $connection->disconnect();
        }
    }

    /**
     * Pin an env value ahead of every layer Laravel's Env repository reads.
     *
     * The repository queries $_SERVER, then $_ENV, then getenv(), so a putenv() alone loses to
     * anything phpunit.xml or the .env loader already wrote - which is why the whole $_SERVER
     * mirror in tests/bootstrap.php exists. Original values are captured for tearDown(), because
     * these decide which half of routes/web.php the NEXT test class registers.
     */
    private function forceEnv(string $key, string $value): void
    {
        if (! array_key_exists($key, $this->originalEnv)) {
            $this->originalEnv[$key] = [
                'server' => $_SERVER[$key] ?? null,
                'env' => $_ENV[$key] ?? null,
                'getenv' => getenv($key),
            ];
        }

        $_SERVER[$key] = $value;
        $_ENV[$key] = $value;
        putenv($key.'='.$value);
    }

    protected function tearDown(): void
    {
        foreach ($this->originalEnv as $key => $original) {
            if ($original['server'] === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $original['server'];
            }

            if ($original['env'] === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $original['env'];
            }

            if ($original['getenv'] === false) {
                putenv($key);
            } else {
                putenv($key.'='.$original['getenv']);
            }
        }

        $this->originalEnv = [];

        parent::tearDown();
    }
}
