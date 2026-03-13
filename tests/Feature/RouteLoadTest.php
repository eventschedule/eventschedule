<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteLoadTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithSchedule(string $type = 'talent', string $subdomain = 'testtalent'): array
    {
        $user = User::factory()->create();

        $role = new Role;
        $role->subdomain = $subdomain;
        $role->user_id = $user->id;
        $role->type = $type;
        $role->name = 'Test ' . ucfirst($type);
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
        $role->subdomain = 'admin' . strtolower(\Str::random(8));
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

            $this->assertTrue(
                $response->status() < 500,
                "Route {$url} returned status {$response->status()}"
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
            '/self-hosting-terms-of-service',

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

            // Integration pages
            '/google-calendar',
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
            '/docs/selfhost/boost',
            '/docs/selfhost/admin',
            '/docs/selfhost/email',
            '/docs/selfhost/ai',
            '/docs/saas',
            '/docs/saas/custom-domains',
            '/docs/saas/twilio',
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
        ], $user, $session);
    }

    public function test_hosted_gp_routes_load(): void
    {
        // Roll back RefreshDatabase transaction to release locks before app refresh
        $this->app['db']->connection()->rollBack();

        // Switch to hosted mode and rebuild the app with subdomain routing
        putenv('IS_HOSTED=true');
        putenv('APP_TESTING=false');
        $this->refreshApplication();
        $this->app['db']->connection()->beginTransaction();

        [$user, $role] = $this->createUserWithSchedule('talent', 'testtalent');

        $baseUrl = 'http://testtalent.' . parse_url(config('app.url'), PHP_URL_HOST);

        $urls = [
            $baseUrl . '/',
            $baseUrl . '/request',
            $baseUrl . '/follow',
            $baseUrl . '/guest-add',
            $baseUrl . '/booking-request',
            $baseUrl . '/feed/ical',
            $baseUrl . '/feed/rss',
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);

            $this->assertTrue(
                $response->status() < 500,
                "Route {$url} returned status {$response->status()}"
            );
        }
    }

    protected function tearDown(): void
    {
        putenv('IS_HOSTED');
        putenv('APP_TESTING');

        parent::tearDown();
    }
}
