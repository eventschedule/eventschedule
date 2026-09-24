<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\DemoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The invariant: every URL either sitemap submits answers 200, says "index", and names itself as
 * the canonical.
 *
 * The sitemap, the page's robots meta and the canonical each used to apply a rule of their own, and
 * a crawl of production found them contradicting each other at scale: of 1,351 submitted event URLs
 * 25 looped, 3 redirected to the login page, 1 was a 404 and 31 answered noindex, and 109 submitted
 * sub-schedule URLs named the schedule root as the page to index. This seeds every shape that
 * produced one of those, fetches both sitemaps, and then fetches every <loc> in them.
 */
class SitemapIndexabilityTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const CUSTOM_DOMAIN = 'https://invariant-direct.test';

    /** Noon UTC $days from today. */
    private function day(int $days): Carbon
    {
        return Carbon::now('UTC')->addDays($days)->setTime(12, 0);
    }

    private function event(Role $role, array $attrs = []): Event
    {
        return $this->createEvent($role, array_merge(['creator_role_id' => $role->id], $attrs));
    }

    private function locs(string $url): array
    {
        $xml = simplexml_load_string($this->get($url)->assertOk()->streamedContent());
        $this->assertNotFalse($xml, $url.' is not valid XML');

        $nodes = $xml->getName() === 'sitemapindex' ? $xml->sitemap : $xml->url;

        return collect(iterator_to_array($nodes, false))->map(fn ($node) => (string) $node->loc)->all();
    }

    /**
     * The request that fetches $loc here. Identical but for one case: the suite routes tenants by
     * path, so a custom domain's root - which production serves from the tenant group's "/" - is
     * only reachable at /{subdomain} on that host. Its canonical is still the bare domain.
     */
    private function fetchable(string $loc, Role $customDomainRole): string
    {
        return $loc === self::CUSTOM_DOMAIN ? $loc.'/'.$customDomainRole->subdomain : $loc;
    }

    public function test_every_submitted_url_is_a_200_indexable_self_canonical_page(): void
    {
        config(['app.hosted' => true]);

        // A verified schedule with a bit of everything.
        $home = $this->createRole($this->createOwner(), 'venue', ['name' => 'Home Hall']);
        $upcoming = $this->event($home, ['name' => 'Upcoming', 'starts_at' => $this->day(7)->format('Y-m-d H:i:s')]);
        $recent = $this->event($home, ['name' => 'Recent', 'starts_at' => $this->day(-10)->format('Y-m-d H:i:s')]);
        $aged = $this->event($home, ['name' => 'Aged Out', 'starts_at' => $this->day(-45)->format('Y-m-d H:i:s')]);

        // A series whose first date is excluded: its dated anchor URL used to redirect to itself.
        $sunday = Carbon::now('UTC')->startOfWeek(Carbon::SUNDAY)->addWeeks(2)->setTime(12, 0);
        $series = $this->createRecurringEvent($home, [
            'name' => 'Sunday Session',
            'days_of_week' => '1000000',
            'recurring_frequency' => 'weekly',
            'starts_at' => $sunday->format('Y-m-d H:i:s'),
            'recurring_exclude_dates' => [$sunday->format('Y-m-d')],
            'creator_role_id' => $home->id,
        ]);

        // A calendar sync's expansion of one series into one-off rows sharing a slug.
        $expanded = [];
        foreach ([-3, 4, 11] as $days) {
            $expanded[] = $this->event($home, ['name' => 'Synced', 'slug' => 'synced-standup', 'starts_at' => $this->day($days)->format('Y-m-d H:i:s')]);
        }

        // A sub-schedule, whose page canonicalizes to the schedule root.
        $group = $this->createGroup($home, ['slug' => 'late-shows']);
        $grouped = $this->event($home, ['name' => 'Grouped']);
        $grouped->roles()->updateExistingPivot($home->id, ['group_id' => $group->id]);

        // A venue's event with a performer who has not accepted it: the canonical is the venue's.
        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Pending Venue']);
        $performer = $this->createRole($this->createOwner(), 'talent', ['name' => 'Pending Act']);
        $pending = $this->event($venue, ['name' => 'Double Bill']);
        $pending->roles()->attach($performer->id, ['is_accepted' => null]);

        // A performer whose "verified" email is empty accepted a verified venue's event. The
        // canonical is the performer's page, which answers noindex, so the event is not listed.
        $stage = $this->createRole($this->createOwner(), 'venue', ['name' => 'Open Stage']);
        $ghost = $this->createRole($this->createOwner(), 'talent', ['name' => 'Ghost Act', 'email' => '']);
        $haunted = $this->event($stage, ['name' => 'Haunted Night']);
        $haunted->roles()->attach($ghost->id, ['is_accepted' => true]);

        // Hosts whose pages refuse indexing, each with an event.
        $unverified = $this->createRole($this->createOwner(), 'venue', ['email_verified_at' => null]);
        $emptyContact = $this->createRole($this->createOwner(), 'venue', ['email' => '']);
        $showcase = $this->createRole($this->createOwner(), 'venue', ['email' => DemoService::DEMO_EMAIL]);
        $demoOwned = $this->createRole(User::factory()->create(['email' => DemoService::DEMO_EMAIL, 'email_verified_at' => now()]), 'venue');
        $refused = [$unverified, $emptyContact, $showcase, $demoOwned];
        foreach ($refused as $role) {
            $this->event($role);
        }

        // Unlisted: out of the global sitemap, in its own.
        $unlisted = $this->createRole($this->createOwner(), 'venue', ['is_unlisted' => true]);
        $unlistedEvent = $this->event($unlisted, ['name' => 'By Invitation']);

        // Served directly on its own domain: only that domain's sitemap may carry its URLs.
        $direct = $this->createRole($this->createOwner(), 'talent', [
            'custom_domain' => self::CUSTOM_DOMAIN,
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);
        $directEvent = $this->event($direct, ['name' => 'On Our Own Domain']);

        // The URLs each sitemap must list, worked out before any request: route() builds on the
        // last request's host, and one of the requests below arrives on the custom domain.
        $mustBeGlobal = [$home->getCanonicalUrl(), $upcoming->getCanonicalUrl(), $recent->getCanonicalUrl(),
            $series->fresh()->getCanonicalUrl(), $expanded[1]->getCanonicalUrl(), $grouped->getCanonicalUrl(),
            $pending->fresh()->getCanonicalUrl(), $venue->getCanonicalUrl()];
        $mustBeOwn = [$unlistedEvent->getCanonicalUrl(), self::CUSTOM_DOMAIN, $directEvent->fresh()->getCanonicalUrl()];
        $subSchedule = $home->getCanonicalUrl().'/late-shows';
        $unlistedUrl = $unlisted->getCanonicalUrl();

        // ---- The sitemaps.

        $global = [];
        foreach ($this->locs('/sitemap.xml') as $child) {
            $path = parse_url($child, PHP_URL_PATH);

            // The marketing pages and the blog are the WP's, covered by SitemapCoverageTest.
            if (str_starts_with($path, '/sitemap-schedules-') || str_starts_with($path, '/sitemap-events-')) {
                $global = array_merge($global, $this->locs($path));
            }
        }

        $own = array_merge(
            $this->locs('/'.$home->subdomain.'/sitemap.xml'),
            $this->locs('/'.$unlisted->subdomain.'/sitemap.xml'),
            $this->locs('http://invariant-direct.test/'.$direct->subdomain.'/sitemap.xml'),
        );

        // What each must and must not contain, so the invariant below is checked over the shapes it
        // is meant to be.
        foreach ($mustBeGlobal as $url) {
            $this->assertContains($url, $global);
        }

        foreach ($mustBeOwn as $url) {
            $this->assertContains($url, $own);
        }

        // The schedule's own sitemap repeats what the global one lists for it, so once each.
        $everything = array_values(array_unique(array_merge($global, $own)));
        $slugs = implode("\n", $everything);

        $this->assertStringNotContainsString('/'.$aged->slug.'/', $slugs, 'aged out of the window');
        $this->assertSame(1, substr_count($slugs, '/synced-standup/'), 'the expanded instances collapse to one');
        $this->assertNotContains($subSchedule, $everything, 'sub-schedules are never listed');
        $this->assertStringNotContainsString('/'.$haunted->slug.'/', $slugs, 'canonical on a noindex host');
        $this->assertNotContains($unlistedUrl, $global);
        $this->assertStringNotContainsString('invariant-direct.test', implode("\n", $global));

        foreach ($refused as $role) {
            $this->assertStringNotContainsString('/'.$role->subdomain, implode("\n", $global), $role->subdomain);
            $this->get('/'.$role->subdomain.'/sitemap.xml')->assertNotFound();
        }

        // ---- The invariant.

        $this->assertNotEmpty($everything);

        foreach ($everything as $loc) {
            $response = $this->get($this->fetchable($loc, $direct));

            $this->assertSame(200, $response->getStatusCode(), $loc.' does not answer 200');

            $html = $response->getContent();
            preg_match('/<meta name="robots" content="([^"]*)"/', $html, $robots);
            preg_match('/<link rel="canonical" href="([^"]*)"/', $html, $canonical);

            $this->assertMatchesRegularExpression('/(^|,)\s*index\s*(,|$)/', $robots[1] ?? '', $loc.' is not indexable');
            $this->assertSame($loc, html_entity_decode($canonical[1] ?? ''), $loc.' is not its own canonical');
        }
    }

    /** The per-schedule sitemap of a host whose pages refuse indexing does not exist. */
    public function test_a_noindex_schedule_has_no_sitemap_of_its_own(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $this->event($role);
        $this->get('/'.$role->subdomain.'/sitemap.xml')->assertOk();

        DB::table('roles')->where('id', $role->id)->update(['email' => '']);

        $this->get('/'.$role->subdomain.'/sitemap.xml')->assertNotFound();
    }
}
