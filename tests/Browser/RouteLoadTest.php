<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class RouteLoadTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    /**
     * Assert a page loads without a 500 Server Error.
     */
    protected function assertPageLoads(Browser $browser, string $url): void
    {
        $browser->visit($url);
        $browser->assertSourceMissing('<title>Server Error</title>');
    }

    /**
     * Test all WP (marketing) routes load without server errors.
     */
    public function test_wp_routes(): void
    {
        $this->browse(function (Browser $browser) {
            $urls = [
                // Core marketing pages
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

                // Docs - index
                '/docs',

                // Docs - user guide
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

                // Docs - selfhost
                '/docs/selfhost',
                '/docs/selfhost/installation',
                '/docs/selfhost/stripe',
                '/docs/selfhost/google-calendar',
                '/docs/selfhost/boost',
                '/docs/selfhost/admin',
                '/docs/selfhost/email',
                '/docs/selfhost/ai',

                // Docs - SaaS
                '/docs/saas',
                '/docs/saas/custom-domains',
                '/docs/saas/twilio',

                // Docs - developer
                '/docs/developer/api',
                '/docs/developer/webhooks',

                // Blog
                '/blog',

                // Auth pages
                '/login',
                '/sign_up',
                '/reset-password',

                // Public pages
                '/unsubscribe',
                '/robots.txt',
                '/sitemap.xml',
            ];

            foreach ($urls as $url) {
                $this->assertPageLoads($browser, $url);
            }
        });
    }

    /**
     * Test all AP (admin portal) routes load without server errors.
     */
    public function test_ap_routes(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser);
            $this->createTestTalent($browser);
            $this->createTestVenue($browser);

            // Global AP routes (no subdomain needed)
            $globalUrls = [
                '/dashboard',
                '/event',
                '/new/talent',
                '/new/venue',
                '/new/curator',
                '/settings',
                '/following',
                '/tickets',
                '/sales',
                '/analytics',
                '/newsletters',
                '/newsletters/create',
                '/newsletter-segments',
                '/newsletter-import',
                '/boost',
                '/boost/create',
                '/my-carpools',
                '/referrals',
                '/scan',
                '/checkin',
                '/waitlist',
            ];

            foreach ($globalUrls as $url) {
                $this->assertPageLoads($browser, $url);
            }

            // Schedule-specific AP routes (talent subdomain)
            $subdomain = 'talent';
            $scheduleUrls = [
                "/{$subdomain}/schedule",
                "/{$subdomain}/availability",
                "/{$subdomain}/requests",
                "/{$subdomain}/followers",
                "/{$subdomain}/team",
                "/{$subdomain}/plan",
                "/{$subdomain}/videos",
                "/{$subdomain}/edit",
                "/{$subdomain}/add-event",
                "/{$subdomain}/events-graphic",
                "/{$subdomain}/events-graphic/settings",
                "/{$subdomain}/import",
                "/{$subdomain}/import/ai",
                "/{$subdomain}/import/eventbrite",
                "/{$subdomain}/scan-agenda",
                "/{$subdomain}/team/add-member",
                "/{$subdomain}/followers/qr-code",
                "/{$subdomain}/match-videos",
            ];

            foreach ($scheduleUrls as $url) {
                $this->assertPageLoads($browser, $url);
            }
        });
    }

    /**
     * Test all GP (guest portal) routes load without server errors.
     */
    public function test_gp_routes(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser);
            $this->createTestTalent($browser);
            $this->logoutUser($browser, 'Talent');

            $subdomain = 'talent';
            $urls = [
                "/{$subdomain}",
                "/{$subdomain}/request",
                "/{$subdomain}/follow",
                "/{$subdomain}/guest-add",
                "/{$subdomain}/booking-request",
                "/{$subdomain}/feed/ical",
                "/{$subdomain}/feed/rss",
            ];

            foreach ($urls as $url) {
                $this->assertPageLoads($browser, $url);
            }
        });
    }
}
