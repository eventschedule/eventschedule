<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Demo schedules exist to be looked at from /examples, not to rank.
 *
 * They are seeded fabricated venues and events, and Googlebot was spending about a quarter of its
 * crawl on them - countyfairgrounds, weekendyogaretreat, battleofthebands, karateclub and painting
 * alone took ~165k of 637k requests in 89 days, several of them out-crawling the tenant that earns
 * 44% of the whole property's clicks. Thousands of thin fabricated event pages are also a site-wide
 * quality signal the domain should not be sending.
 *
 * So they send noindex and are dropped from the sitemap. Both halves matter: advertising a URL in
 * the sitemap that then refuses indexing is a contradiction, not a fix.
 */
class DemoScheduleIndexingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** A schedule owned by the demo user - the predicate is_demo_role() actually keys on. */
    private function demoRole(array $attrs = [])
    {
        $demoUser = User::factory()->create([
            'email' => DemoService::DEMO_EMAIL,
            'email_verified_at' => now(),
        ]);

        return $this->createRole($demoUser, 'venue', array_merge(['name' => 'Demo Venue'], $attrs));
    }

    private function robotsOf(string $url): ?string
    {
        $content = $this->get($url)->assertOk()->getContent();

        return preg_match('/<meta name="robots" content="([^"]*)"/', $content, $m) ? $m[1] : null;
    }

    /** The `<loc>` values of a streamed sitemap child. */
    private function locs(string $path): array
    {
        $xml = simplexml_load_string($this->get($path)->assertOk()->streamedContent());
        $this->assertNotFalse($xml, 'Sitemap is not valid XML');

        return collect(iterator_to_array($xml->url, false))->map(fn ($n) => (string) $n->loc)->all();
    }

    public function test_demo_schedule_page_still_renders_but_is_noindex(): void
    {
        $role = $this->demoRole();

        // Still fully viewable - /examples links straight to it.
        $this->assertSame('noindex, nofollow', $this->robotsOf('/'.$role->subdomain));
    }

    public function test_demo_event_page_is_noindex(): void
    {
        $role = $this->demoRole();
        $event = $this->createEvent($role, ['name' => 'Fabricated Gig']);

        $this->assertSame('noindex, nofollow', $this->robotsOf($this->guestEventUrl($role, $event)));
    }

    public function test_the_simpsons_subdomain_is_noindex_too(): void
    {
        // is_demo_role() has a second arm: the historic demo subdomain, whose owner is a normal user.
        $role = $this->createRole($this->createOwner(), 'venue', [
            'subdomain' => DemoService::DEMO_ROLE_SUBDOMAIN,
        ]);

        $this->assertSame('noindex, nofollow', $this->robotsOf('/'.$role->subdomain));
    }

    public function test_a_normal_schedule_is_untouched(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Real Venue']);
        $event = $this->createEvent($role, ['name' => 'Real Gig']);

        $this->assertSame('index, follow', $this->robotsOf('/'.$role->subdomain));
        $this->assertSame('index, follow', $this->robotsOf($this->guestEventUrl($role, $event)));
    }

    public function test_demo_schedules_are_not_in_the_sitemap(): void
    {
        $demo = $this->demoRole();
        $real = $this->createRole($this->createOwner(), 'venue', ['name' => 'Real Venue']);

        $locs = $this->locs('/sitemap-schedules-1.xml');

        $this->assertTrue(
            collect($locs)->contains(fn ($l) => str_contains($l, $real->subdomain)),
            'a real schedule should still be advertised'
        );
        $this->assertFalse(
            collect($locs)->contains(fn ($l) => str_contains($l, $demo->subdomain)),
            'the demo schedule must not be advertised'
        );
    }

    public function test_demo_events_are_not_in_the_sitemap(): void
    {
        $demo = $this->demoRole();
        $demoEvent = $this->createEvent($demo, ['name' => 'Fabricated Gig']);

        $real = $this->createRole($this->createOwner(), 'venue', ['name' => 'Real Venue']);
        $realEvent = $this->createEvent($real, ['name' => 'Real Gig']);

        $locs = $this->locs('/sitemap-events-1.xml');

        $this->assertTrue(
            collect($locs)->contains(fn ($l) => str_contains($l, $realEvent->slug)),
            'a real event should still be advertised'
        );
        $this->assertFalse(
            collect($locs)->contains(fn ($l) => str_contains($l, $demoEvent->slug)),
            'the demo event must not be advertised'
        );
    }

    /**
     * A real schedule's event must survive a demo curator picking it up. The exclusion binds to the
     * pivot row, so the event qualifies through its non-demo acceptance - dropping demo events
     * outright would have taken this one with it.
     */
    public function test_a_real_event_a_demo_curator_picked_up_stays_in_the_sitemap(): void
    {
        $real = $this->createRole($this->createOwner(), 'venue', ['name' => 'Real Venue']);
        $event = $this->createEvent($real, ['name' => 'Shared Gig']);

        $demo = $this->demoRole();
        $event->roles()->attach($demo->id, ['is_accepted' => true]);

        $this->assertTrue(
            collect($this->locs('/sitemap-events-1.xml'))->contains(fn ($l) => str_contains($l, $event->slug)),
            'an event accepted by BOTH a real and a demo schedule must stay advertised'
        );
    }

    /**
     * The exclusion is expressed as a query, not is_demo_role() per row, because these queries
     * chunk over every role and event in the database and the helper reads $role->user. Pin that:
     * adding schedules must not add queries.
     */
    public function test_the_demo_check_never_loads_a_user_row_per_schedule(): void
    {
        foreach (range(1, 6) as $i) {
            $this->createRole($this->createOwner(), 'venue');
        }
        $this->demoRole();

        // Counting total queries is useless here - the document is cached, so a warm run makes
        // fewer queries than a cold one regardless. What matters is the SHAPE: is_demo_role() reads
        // $role->user, so a per-row implementation shows up as standalone selects against `users`.
        // Done as a subquery inside the roles SELECT, `users` never appears as its own statement.
        \DB::flushQueryLog();
        \DB::enableQueryLog();
        $this->get('/sitemap-schedules-1.xml')->assertOk()->streamedContent();
        $log = \DB::getQueryLog();
        \DB::disableQueryLog();

        // Match the FIRST `from` (non-greedy), or the correlated `from users` inside the intended
        // subquery counts as a hit and the assertion can never pass.
        $topLevelTable = function (string $q): ?string {
            return preg_match('/^select\b.*?\bfrom\s+[`"]?(\w+)[`"]?/is', trim($q), $m) ? $m[1] : null;
        };

        $standaloneUserSelects = collect($log)
            ->pluck('query')
            ->filter(fn ($q) => $topLevelTable($q) === 'users')
            ->values();

        $this->assertCount(
            0,
            $standaloneUserSelects,
            'the demo exclusion must be a subquery, not is_demo_role() per row: '
                .$standaloneUserSelects->implode(' | ')
        );
    }
}
