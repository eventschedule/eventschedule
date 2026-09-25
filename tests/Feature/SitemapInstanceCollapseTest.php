<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Cache\ArrayStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Exceptions;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * One sitemap URL per (creator, slug) for one-off events.
 *
 * Google Calendar sync imports a recurring series with singleEvents, so each occurrence lands as a
 * one-off row of its own sharing one slug - one schedule put 3,090 of the 4,441 event URLs in the
 * sitemap, 404 of them "nutesupara". Every instance keeps its page; the sitemap lists the one a
 * bare /{slug} resolves to: the next upcoming instance, else the latest.
 */
class SitemapInstanceCollapseTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Noon UTC $days from today. */
    private function day(int $days): string
    {
        return Carbon::now('UTC')->addDays($days)->setTime(12, 0)->format('Y-m-d H:i:s');
    }

    private function occurrence(Role $role, int $days, string $slug = 'nutesupara', array $attrs = []): Event
    {
        return $this->createEvent($role, array_merge([
            'name' => 'Nutesupara',
            'slug' => $slug,
            'starts_at' => $this->day($days),
            'creator_role_id' => $role->id,
        ], $attrs));
    }

    /** Every event URL both sitemaps list for $role, across all the global pages. */
    private function listed(Role $role): array
    {
        $global = [];
        $index = simplexml_load_string($this->get('/sitemap.xml')->assertOk()->streamedContent());

        foreach ($index->sitemap as $child) {
            $path = parse_url((string) $child->loc, PHP_URL_PATH);

            if (str_starts_with($path, '/sitemap-events-')) {
                $global = array_merge($global, $this->locs($path));
            }
        }

        $own = array_values(array_filter(
            $this->locs('/'.$role->subdomain.'/sitemap.xml'),
            fn ($loc) => $loc !== $role->getCanonicalUrl()
        ));

        return [$global, $own];
    }

    private function locs(string $path): array
    {
        $xml = simplexml_load_string($this->get($path)->assertOk()->streamedContent());

        return collect(iterator_to_array($xml->url, false))->map(fn ($node) => (string) $node->loc)->all();
    }

    public function test_the_next_upcoming_instance_is_the_one_listed(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $this->occurrence($role, -10);
        $next = $this->occurrence($role, 7);
        $this->occurrence($role, 14);

        [$global, $own] = $this->listed($role);
        $url = $this->guestEventUrl($role, $next);

        $this->assertSame([$url], $global);
        $this->assertSame([$url], $own);

        // The instance a bare /{slug} shows, which is what makes it the one to list.
        $html = $this->get(route('event.view_guest', ['subdomain' => $role->subdomain, 'slug' => 'nutesupara']))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('<link rel="canonical" href="'.$url.'">', $html);
    }

    public function test_with_nothing_upcoming_the_latest_instance_is_listed(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $this->occurrence($role, -25);
        $latest = $this->occurrence($role, -5);
        $this->occurrence($role, -15);

        [$global, $own] = $this->listed($role);

        $this->assertSame([$this->guestEventUrl($role, $latest)], $global);
        $this->assertSame([$this->guestEventUrl($role, $latest)], $own);
    }

    /**
     * Each sitemap page is a separate request, and the instances of one slug land on different
     * pages. An exact tie still has to resolve to one row on every page, not to whichever row a
     * page saw first - or both would be listed.
     */
    public function test_a_tie_resolves_to_one_instance_across_pages(): void
    {
        config(['app.sitemap_urls_per_file' => 1]);

        $role = $this->createRole($this->createOwner(), 'venue');
        $first = $this->occurrence($role, 7);
        $this->occurrence($role, 7);

        [$global] = $this->listed($role);

        $this->assertSame([$this->guestEventUrl($role, $first)], $global);
    }

    public function test_only_one_off_instances_of_one_creator_are_collapsed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $other = $this->createRole($owner, 'venue');

        // Another creator's event of the same slug is its own event.
        $mine = $this->occurrence($role, 7);
        $theirs = $this->occurrence($other, 8);

        // Two series sharing a slug are two series: each is a single row already.
        $seriesA = $this->occurrence($role, 3, 'open-mic', ['days_of_week' => '1111111', 'recurring_frequency' => 'weekly']);
        $seriesB = $this->occurrence($role, 4, 'open-mic', ['days_of_week' => '1111111', 'recurring_frequency' => 'weekly']);

        // No creator: nothing says the two are one event.
        $orphanA = $this->occurrence($role, 5, 'orphan', ['creator_role_id' => null]);
        $orphanB = $this->occurrence($role, 6, 'orphan', ['creator_role_id' => null]);

        [$global] = $this->listed($role);

        foreach ([[$role, $mine], [$other, $theirs], [$role, $seriesA], [$role, $seriesB], [$role, $orphanA], [$role, $orphanB]] as [$host, $event]) {
            $this->assertContains($this->guestEventUrl($host, $event), $global, $event->slug.' #'.$event->id);
        }

        $this->assertCount(6, $global);
    }

    /**
     * A venue's instances of one slug, with an act that accepted only $onlyOn of them - which makes
     * each of those canonical on the act, not on the venue.
     *
     * @param  array<int, int>  $days  one instance per entry, $days from today
     * @return array{0: Role, 1: array<int, Event>, 2: Role} the venue, its instances keyed by day, the act
     */
    private function actOnSomeInstances(array $days, array $onlyOn, bool $actUnlisted): array
    {
        $venue = $this->createRole($this->createOwner(), 'venue');
        $act = $this->createRole($this->createOwner(), 'talent', ['is_unlisted' => $actUnlisted]);

        $instances = [];

        foreach ($days as $day) {
            $instances[$day] = $this->occurrence($venue, $day);
        }

        foreach ($onlyOn as $day) {
            $instances[$day]->roles()->attach($act->id, ['is_accepted' => true]);
        }

        return [$venue, $instances, $act];
    }

    /**
     * The preferred instance is the one an act accepted, so it is canonical on the act, and the
     * venue's own sitemap lists only its own canonicals. With every sibling folded into that one,
     * the venue's sitemap used to list no instance at all. It lists the next one it can.
     */
    public function test_a_venue_still_lists_an_instance_when_its_act_accepted_only_the_preferred_one(): void
    {
        [$venue, $instances, $act] = $this->actOnSomeInstances([-10, 7, 14], onlyOn: [7], actUnlisted: false);

        [$global, $own] = $this->listed($venue);

        $this->assertSame([$this->guestEventUrl($venue, $instances[14])], $own);

        // The global sitemap still lists the preferred instance, where it is canonical.
        $this->assertSame([$instances[7]->fresh()->getCanonicalUrl()], $global);
        $this->assertStringStartsWith(url('/'.$act->subdomain.'/'), $global[0], 'fixture: canonical on the act');
    }

    /** The same fold, globally: an unlisted act keeps its canonicals out of our listing. */
    public function test_the_global_sitemap_lists_a_venue_instance_when_the_preferred_ones_act_is_unlisted(): void
    {
        [$venue, $instances] = $this->actOnSomeInstances([-10, 7, 14], onlyOn: [7], actUnlisted: true);

        [$global, $own] = $this->listed($venue);

        $this->assertSame([$this->guestEventUrl($venue, $instances[14])], $global);
        $this->assertSame([$this->guestEventUrl($venue, $instances[14])], $own);
    }

    /**
     * The instance listed instead is decided over every page's rows at once, so each page agrees:
     * worked out from one page's rows alone, the next two instances would each look listable on
     * their own page, and both would be.
     */
    public function test_the_instance_listed_instead_is_listed_exactly_once_across_pages(): void
    {
        config(['app.sitemap_urls_per_file' => 1]);

        [$venue, $instances] = $this->actOnSomeInstances([7, 14, 21], onlyOn: [7], actUnlisted: true);

        [$global] = $this->listed($venue);

        $this->assertSame([$this->guestEventUrl($venue, $instances[14])], $global);
    }

    /**
     * When no instance may be listed, none is: the preferred one stays the pick, and the listing
     * refuses it. A pin rather than a fix - the listing's own check refused these before and after.
     */
    public function test_no_instance_is_listed_when_none_of_them_can_be(): void
    {
        [$venue] = $this->actOnSomeInstances([7, 14], onlyOn: [7, 14], actUnlisted: true);

        [$global, $own] = $this->listed($venue);

        $this->assertSame([], $global);
        $this->assertSame([], $own);
    }

    /**
     * The global map is cached under its own key, so a crawl's pages agree and each one does not
     * work it out again - which is also why data changed between two requests needs a flush.
     */
    public function test_the_global_map_is_cached_under_its_own_key(): void
    {
        [$venue, $instances, $act] = $this->actOnSomeInstances([7, 14], onlyOn: [7], actUnlisted: true);

        [$global] = $this->listed($venue);
        $this->assertSame([$this->guestEventUrl($venue, $instances[14])], $global);
        $this->assertContains($instances[14]->id, Cache::get('sitemap:collapse:'.config('app.url')));

        // The act leaves: the preferred instance is the venue's own again, and listable. The cached
        // map still names the one it chose.
        $instances[7]->roles()->detach($act->id);

        [$global] = $this->listed($venue);
        $this->assertSame([$this->guestEventUrl($venue, $instances[14])], $global);

        Cache::flush();

        [$global] = $this->listed($venue);
        $this->assertSame([$this->guestEventUrl($venue, $instances[7])], $global);
    }

    /** A cache store that fails costs the cache, never the sitemap: the map is worked out directly. */
    public function test_a_failing_cache_store_still_collapses_the_global_sitemap(): void
    {
        Exceptions::fake();

        Cache::extend('collapse-map-down', fn ($app) => Cache::repository(new class extends ArrayStore
        {
            public function many(array $keys)
            {
                foreach ($keys as $key) {
                    if (str_starts_with($key, 'sitemap:collapse:')) {
                        throw new \RuntimeException('cache store down');
                    }
                }

                return parent::many($keys);
            }
        }));
        config([
            'cache.stores.collapse-map-down' => ['driver' => 'collapse-map-down'],
            'cache.default' => 'collapse-map-down',
        ]);

        [$venue, $instances] = $this->actOnSomeInstances([-10, 7, 14], onlyOn: [7], actUnlisted: true);

        [$global] = $this->listed($venue);

        $this->assertSame([$this->guestEventUrl($venue, $instances[14])], $global);
        Exceptions::assertReported(fn (\RuntimeException $e) => $e->getMessage() === 'cache store down');
    }
}
