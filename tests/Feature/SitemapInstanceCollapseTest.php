<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
