<?php

namespace Tests\Feature;

use App\Http\Controllers\SitemapController;
use App\Models\BlogPost;
use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * /sitemap.xml is a sitemap index whose children are streamed in chunks.
 *
 * The regression this guards: the previous single-file implementation hydrated every schedule and
 * every public event at once and rendered the whole document into one cached string, which grew
 * until it exhausted the PHP memory limit and returned a zero-byte 500 in production. Memory here
 * must stay flat, no path may fail to produce valid XML, and the index must never advertise a
 * child it cannot serve.
 */
class SitemapTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Fetch a streamed sitemap and return its body. */
    private function xml(string $path): string
    {
        return $this->get($path)->assertOk()->streamedContent();
    }

    private function parse(string $xml): \SimpleXMLElement
    {
        $parsed = simplexml_load_string($xml);

        $this->assertNotFalse($parsed, 'Sitemap is not valid XML: '.substr($xml, 0, 200));

        return $parsed;
    }

    /** The `<loc>` values of a sitemap document, as plain strings. */
    private function locs(string $xml): array
    {
        $parsed = $this->parse($xml);
        $nodes = $parsed->getName() === 'sitemapindex' ? $parsed->sitemap : $parsed->url;

        return collect(iterator_to_array($nodes, false))->map(fn ($n) => (string) $n->loc)->all();
    }

    /** The paths of the children the index currently advertises. */
    private function advertisedChildren(): array
    {
        return collect($this->locs($this->xml('/sitemap.xml')))
            ->map(fn ($loc) => parse_url($loc, PHP_URL_PATH))
            ->all();
    }

    public function test_index_is_a_valid_sitemapindex(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertHeader('Cache-Control', 'max-age=3600, public');

        $xml = $response->streamedContent();

        $this->assertSame('sitemapindex', $this->parse($xml)->getName());
        $this->assertStringContainsString('/sitemap-pages.xml', $xml);
    }

    /**
     * The direct regression guard for the 500: every URL the index advertises must return valid
     * XML, even with nothing in the database.
     */
    public function test_every_advertised_child_is_valid_on_an_empty_database(): void
    {
        $this->assertSame('sitemapindex', $this->parse($this->xml('/sitemap.xml'))->getName());

        $children = $this->advertisedChildren();
        $this->assertNotEmpty($children, 'the index must never be empty');

        foreach ($children as $path) {
            $this->assertSame('urlset', $this->parse($this->xml($path))->getName(), $path.' is not a urlset');
        }
    }

    public function test_every_advertised_child_is_valid_with_data(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->createGroup($role);
        $this->createEvent($role, ['creator_role_id' => $role->id]);

        foreach ($this->advertisedChildren() as $path) {
            $this->assertSame('urlset', $this->parse($this->xml($path))->getName(), $path.' is not a urlset');
        }
    }

    /**
     * Crawler traffic never needs a session, and Cloudflare will not cache a response that sets a
     * cookie - which would make the Cache-Control header pointless. Guards the
     * withoutMiddleware('web') on the sitemap routes.
     */
    public function test_sitemap_responses_do_not_set_cookies(): void
    {
        foreach (['/sitemap.xml', '/sitemap-pages.xml'] as $path) {
            $response = $this->get($path)->assertOk();

            $this->assertEmpty(
                $response->headers->getCookies(),
                $path.' set a cookie, which stops Cloudflare caching it'
            );
            $this->assertNull($response->headers->get('Set-Cookie'), $path.' set a cookie');
        }
    }

    /**
     * The .gz paths used to serve the same XML under a Content-Encoding: gzip transport header,
     * which is not a gzip file and which any proxy may re-negotiate away - so the same URL returned
     * gzip or bare XML depending on the caller. They are redirects now, and there must be exactly
     * one representation of each sitemap. Kept rather than deleted because Search Console and
     * anything else that already discovered a .gz URL has to land somewhere real.
     */
    public function test_gz_paths_redirect_to_the_canonical_sitemap(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->createEvent($role, ['creator_role_id' => $role->id]);

        $this->get('/sitemap.xml.gz')->assertRedirect('/sitemap.xml')->assertStatus(301);

        foreach ($this->advertisedChildren() as $path) {
            $this->get($path.'.gz')->assertRedirect($path)->assertStatus(301);
        }
    }

    /** Nothing may advertise or serve a .gz sitemap as its own document any more. */
    public function test_no_sitemap_response_is_content_encoded(): void
    {
        foreach (array_merge(['/sitemap.xml'], $this->advertisedChildren()) as $path) {
            $response = $this->get($path)->assertOk();

            $this->assertNull(
                $response->headers->get('Content-Encoding'),
                $path.' sets Content-Encoding, which proxies re-negotiate'
            );

            foreach ($this->locs($response->streamedContent()) as $loc) {
                $this->assertStringNotContainsString('.xml.gz', $loc, $path.' advertises a .gz URL');
            }
        }
    }

    /**
     * A body larger than the flush threshold is written out in several passes. Get that wrong and
     * the document is truncated or duplicated in production while every small fixture still passes,
     * so this deliberately produces a document big enough to flush several times.
     */
    public function test_a_large_body_survives_mid_stream_flushes(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->seedEvents($role, 900);

        $xml = $this->xml('/sitemap-events-1.xml');

        $this->assertGreaterThan(65536, strlen($xml), 'fixture is too small to trigger a flush');
        $this->assertSame('urlset', $this->parse($xml)->getName());
        $this->assertSame(900, substr_count($xml, '<loc>'));
    }

    /** Bulk-insert public events, bypassing the per-model save path for speed. */
    private function seedEvents(Role $role, int $count): void
    {
        $now = now();
        $events = [];

        for ($i = 0; $i < $count; $i++) {
            $events[] = [
                'user_id' => $role->user_id,
                'creator_role_id' => $role->id,
                'name' => 'Bulk Event '.$i,
                'slug' => 'bulk-event-'.$i,
                'starts_at' => $now->copy()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
                'duration' => 2,
                'is_draft' => false,
                'is_private' => false,
                'is_cancelled' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($events, 200) as $chunk) {
            DB::table('events')->insert($chunk);
        }

        $pivots = DB::table('events')
            ->where('creator_role_id', $role->id)
            ->pluck('id')
            ->map(fn ($id) => ['event_id' => $id, 'role_id' => $role->id, 'is_accepted' => true])
            ->all();

        foreach (array_chunk($pivots, 200) as $chunk) {
            DB::table('event_role')->insert($chunk);
        }
    }

    public function test_pages_child_holds_the_static_marketing_urls(): void
    {
        $xml = $this->xml('/sitemap-pages.xml');

        $this->assertSame('urlset', $this->parse($xml)->getName());
        $this->assertStringContainsString(url('/'), $xml);
        $this->assertStringContainsString(url('/pricing'), $xml);
        $this->assertStringContainsString(url('/docs/getting-started'), $xml);
    }

    /**
     * The static <lastmod> comes from view mtimes, and reproducible-build images rewrite those to a
     * fixed epoch - the hosted buildpack stamps 1980-01-01, which told crawlers the marketing pages
     * had not changed in 46 years. Any pre-2000 timestamp is a build artifact, never a real edit.
     */
    public function test_static_lastmods_are_not_build_artifacts(): void
    {
        $floor = Carbon::create(2000, 1, 1);
        $documents = ['/sitemap.xml' => 'sitemap', '/sitemap-pages.xml' => 'url'];

        foreach ($documents as $path => $node) {
            foreach ($this->parse($this->xml($path))->{$node} as $entry) {
                if (! isset($entry->lastmod)) {
                    continue;
                }

                $this->assertTrue(
                    Carbon::parse((string) $entry->lastmod)->gt($floor),
                    $path.' reports an implausible lastmod: '.$entry->lastmod
                );
            }
        }
    }

    /**
     * The fallback stands in for the deploy date, so it has to be identical for every container on
     * a release and move only when a new version ships.
     */
    public function test_static_lastmod_fallback_is_stable_per_release(): void
    {
        // Private: the fallback is an implementation detail of staticLastmod(), but it only fires
        // on a filesystem whose mtimes are normalized, which is not reproducible from a test.
        $method = new \ReflectionMethod(SitemapController::class, 'staticLastmodFallback');
        $lastmod = fn () => $method->invoke(new SitemapController);

        config(['self-update.version_installed' => 'v9.9.9']);
        $first = $lastmod();

        Carbon::setTestNow(now()->addDay());

        $this->assertSame($first, $lastmod(), 'the fallback moved without a deploy');

        config(['self-update.version_installed' => 'v9.9.10']);

        $this->assertNotSame($first, $lastmod(), 'the fallback did not move on a new release');
    }

    public function test_children_paginate_at_the_configured_cap(): void
    {
        config(['app.sitemap_urls_per_file' => 2]);

        $owner = $this->createOwner();
        $subdomains = [];
        for ($i = 0; $i < 5; $i++) {
            $subdomains[] = $this->createRole($owner, 'venue')->subdomain;
        }

        $index = $this->xml('/sitemap.xml');
        foreach ([1, 2, 3] as $page) {
            $this->assertStringContainsString('/sitemap-schedules-'.$page.'.xml', $index);
        }
        $this->assertStringNotContainsString('/sitemap-schedules-4.xml', $index);

        // Every schedule appears exactly once across the pages - no gaps, no duplicates.
        $seen = [];
        foreach ([1, 2, 3] as $page) {
            foreach ($this->locs($this->xml('/sitemap-schedules-'.$page.'.xml')) as $loc) {
                $seen[] = basename(parse_url($loc, PHP_URL_PATH));
            }
        }

        $this->assertSame(count($seen), count(array_unique($seen)), 'a schedule was listed more than once');
        $this->assertEqualsCanonicalizing($subdomains, $seen);

        $this->get('/sitemap-schedules-4.xml')->assertNotFound();
    }

    /** Events paginate descending, so page 1 holds the newest. */
    public function test_event_pages_have_no_gaps_or_duplicates(): void
    {
        config(['app.sitemap_urls_per_file' => 2]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $slugs = [];
        for ($i = 0; $i < 5; $i++) {
            $slugs[] = $this->createEvent($role, ['name' => 'Event '.$i, 'creator_role_id' => $role->id])->slug;
        }

        $seen = [];
        foreach ([1, 2, 3] as $page) {
            $xml = $this->xml('/sitemap-events-'.$page.'.xml');
            $this->assertLessThanOrEqual(2, substr_count($xml, '<loc>'), 'page '.$page.' exceeds the cap');

            foreach ($slugs as $slug) {
                if (str_contains($xml, '/'.$slug.'/')) {
                    $seen[] = $slug;
                }
            }
        }

        $this->assertSame(count($seen), count(array_unique($seen)), 'an event was listed more than once');
        $this->assertEqualsCanonicalizing($slugs, $seen);
    }

    /**
     * A schedules page emits one URL per schedule plus one per sub-schedule. Weighting by that is
     * what keeps the page under the cap.
     */
    public function test_sub_schedules_count_towards_the_page_cap(): void
    {
        config(['app.sitemap_urls_per_file' => 3]);

        $owner = $this->createOwner();
        for ($i = 0; $i < 3; $i++) {
            $role = $this->createRole($owner, 'venue');
            $this->createGroup($role);
            $this->createGroup($role);
        }

        foreach ($this->advertisedChildren() as $path) {
            if (! str_contains($path, 'schedules')) {
                continue;
            }

            $this->assertLessThanOrEqual(3, substr_count($this->xml($path), '<loc>'), $path.' exceeds the cap');
        }
    }

    public function test_sub_schedules_are_listed_under_their_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $group = $this->createGroup($role);

        $this->assertStringContainsString(
            $role->getCanonicalUrl().'/'.$group->slug,
            $this->xml('/sitemap-schedules-1.xml')
        );
    }

    /**
     * The page holding the newest ids is left unbounded, so a row created after the section list
     * was cached still lands in an existing page instead of falling outside every range.
     */
    public function test_rows_created_after_the_index_is_cached_still_appear(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->createEvent($role, ['name' => 'First', 'creator_role_id' => $role->id]);

        // Warms the cached section list, including the page id ranges.
        $this->xml('/sitemap.xml');

        $later = $this->createEvent($role, ['name' => 'Later', 'creator_role_id' => $role->id]);

        $this->assertStringContainsString($later->slug, $this->xml('/sitemap-events-1.xml'));
    }

    /** A child the index advertises must never 404, even after the rows behind it are deleted. */
    public function test_deleting_rows_does_not_make_an_advertised_child_404(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $children = $this->advertisedChildren();
        $this->assertContains('/sitemap-events-1.xml', $children);

        DB::table('event_role')->where('event_id', $event->id)->delete();
        Event::whereKey($event->id)->delete();

        foreach ($children as $path) {
            $this->assertSame('urlset', $this->parse($this->xml($path))->getName(), $path.' should still serve');
        }
    }

    /**
     * A schedule with a verified email but no owner is not claimed, so getGuestUrl() returns an
     * empty string. The old query did not check user_id, so the view rendered url('') and emitted
     * the site root as a duplicate <loc>, once per ownerless schedule.
     */
    public function test_unclaimed_schedules_are_excluded_and_the_root_is_not_duplicated(): void
    {
        $owner = $this->createOwner();
        $claimed = $this->createRole($owner, 'venue', ['name' => 'Claimed Venue']);
        $orphan = $this->createRole($owner, 'venue', ['name' => 'Orphan Venue']);
        DB::table('roles')->where('id', $orphan->id)->update(['user_id' => null]);

        $xml = $this->xml('/sitemap-schedules-1.xml');

        $this->assertStringContainsString($claimed->subdomain, $xml);
        $this->assertStringNotContainsString($orphan->subdomain, $xml);
        $this->assertSame(0, substr_count($xml, '<loc>'.url('/').'</loc>'));
    }

    /**
     * The date segment of a recurring event's URL is the schedule's calendar date, not the UTC
     * one. The old eager load omitted creatorRole.timezone, and loadMissing() will not refetch an
     * already-loaded relation, so it silently fell back to UTC and advertised the wrong day.
     */
    public function test_recurring_event_url_uses_the_creator_schedule_timezone(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);

        // 02:00 UTC is the previous evening in New York.
        $startsAt = Carbon::now()->addDays(7)->setTime(2, 0);
        $event = $this->createRecurringEvent($role, [
            'starts_at' => $startsAt->format('Y-m-d H:i:s'),
            'creator_role_id' => $role->id,
        ]);

        $localDate = $startsAt->copy()->timezone('America/New_York')->format('Y-m-d');
        $this->assertNotSame($startsAt->format('Y-m-d'), $localDate, 'fixture no longer straddles midnight');

        $xml = $this->xml('/sitemap-events-1.xml');

        $this->assertStringContainsString($event->slug, $xml);
        $this->assertStringContainsString('/'.$localDate.'<', $xml);
        $this->assertStringNotContainsString('/'.$startsAt->format('Y-m-d').'<', $xml);
    }

    /**
     * A schedule served directly on an active custom domain canonicalizes to that domain, and the
     * global sitemap is not allowed to carry a third-party host: Google reports every one of them
     * as "URL not allowed" and discards it (940 event URLs and 7 schedules, in production). They
     * belong in that host's own sitemap instead, which is where they are actually in scope.
     */
    public function test_custom_domain_urls_are_excluded_from_the_global_sitemap(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'custom_domain' => 'https://sitemap-direct.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);
        $this->createEvent($role, ['creator_role_id' => $role->id]);

        foreach (['/sitemap-schedules-1.xml', '/sitemap-events-1.xml'] as $path) {
            $xml = $this->xml($path);

            $this->assertStringNotContainsString('sitemap-direct.test', $xml, $path.' lists a custom domain');
            // Nor may it fall back to the subdomain: that page canonicalizes to the custom domain.
            $this->assertStringNotContainsString(url('/'.$role->subdomain).'<', $xml);
        }
    }

    /**
     * The scope rule Google enforces, tested at the predicate: a URL off this host is "URL not
     * allowed", and a host whose label exceeds the 63-octet DNS limit is "Invalid URL" (subdomains
     * predating the 50-character cap in Role::cleanSubdomain() - those hosts do not resolve at all).
     *
     * Not driven through a sitemap request because the suite is path-routed: a subdomain lands in
     * the path there, never in the host, so no fixture can produce an over-long DNS label.
     */
    public function test_only_in_scope_hosts_with_valid_labels_are_listable(): void
    {
        $method = new \ReflectionMethod(SitemapController::class, 'isListable');
        $controller = new SitemapController;

        (new \ReflectionMethod(SitemapController::class, 'scopeToBaseDomain'))->invoke($controller);

        $base = _base_domain();
        $long = str_repeat('a', 64);

        foreach (["https://{$base}/pricing", "https://tenant.{$base}", "https://blog.{$base}/post"] as $loc) {
            $this->assertTrue($method->invoke($controller, $loc), $loc.' should be listable');
        }

        foreach ([
            'https://emeklive.co.il/magal-30-7/Fz3n7C',  // a customer custom domain
            "https://{$long}.{$base}/event/abc",         // label over the DNS limit
            "https://{$base}.evil.test",                 // suffix match must not be a substring match
            'not a url',
        ] as $loc) {
            $this->assertFalse($method->invoke($controller, $loc), $loc.' should not be listable');
        }
    }

    /** A schedule's own sitemap carries its URLs and nobody else's. */
    public function test_schedule_sitemap_lists_only_that_schedules_urls(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $other = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $this->createEvent($other, ['creator_role_id' => $other->id]);

        $response = $this->get('/'.$role->subdomain.'/sitemap.xml')->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $this->assertEmpty($response->headers->getCookies(), 'the schedule sitemap set a cookie');

        $xml = $response->streamedContent();
        $locs = $this->locs($xml);

        $this->assertSame('urlset', $this->parse($xml)->getName());
        $this->assertContains($role->getCanonicalUrl(), $locs);
        $this->assertContains($event->getCanonicalUrl(), $locs);
        $this->assertNotContains($other->getCanonicalUrl(), $locs);
    }

    /**
     * Unknown, or canonical on a host other than the one being asked. The second case is a schedule
     * with an active custom domain asked at its subdomain: every URL it would emit belongs to the
     * custom domain, so an empty urlset would be the only honest body - and a 404 says it better.
     */
    public function test_schedule_sitemap_404s_when_it_has_nothing_to_serve(): void
    {
        $owner = $this->createOwner();
        $elsewhere = $this->createRole($owner, 'talent', [
            'custom_domain' => 'https://sitemap-elsewhere.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);

        $this->get('/no-such-schedule/sitemap.xml')->assertNotFound();
        $this->get('/'.$elsewhere->subdomain.'/sitemap.xml')->assertNotFound();
    }

    /**
     * robots.txt is how a sitemap is discovered, and <link rel="sitemap"> in the page head says the
     * same thing a second time. A custom domain has to point both at its own, because the global
     * sitemap is not allowed to carry a single one of that host's URLs. Asserted together so the
     * two can never drift - they share sitemap_url().
     */
    public function test_a_custom_domain_advertises_its_own_sitemap(): void
    {
        // Pinned: _base_domain() and the robots line both derive from app.url, which is empty in CI.
        config(['app.url' => 'https://eventschedule.test', 'app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'custom_domain' => 'https://robots-direct.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);

        $robots = $this->get('http://robots-direct.test/robots.txt')->assertOk()->getContent();

        $this->assertStringContainsString('Sitemap: https://robots-direct.test/sitemap.xml', $robots);
        $this->assertStringNotContainsString('eventschedule.test/sitemap.xml', $robots);

        $page = $this->get('http://robots-direct.test/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString(
            '<link rel="sitemap" type="application/xml" href="https://robots-direct.test/sitemap.xml">',
            $page
        );
        $this->assertStringNotContainsString('eventschedule.test/sitemap.xml', $page);
    }

    /**
     * Everywhere else keeps the global one. On a tenant subdomain that robots.txt line is the
     * cross-submission grant that keeps the subdomain's URLs legal inside the global sitemap - drop
     * it and Google rejects them the same way it rejects the custom-domain ones.
     */
    public function test_other_hosts_advertise_the_global_sitemap(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $expected = config('app.url').'/sitemap.xml';

        $this->assertStringContainsString(
            'Sitemap: '.$expected,
            $this->get('/robots.txt')->assertOk()->getContent()
        );

        $this->assertStringContainsString(
            '<link rel="sitemap" type="application/xml" href="'.$expected.'">',
            $this->get('/'.$role->subdomain)->assertOk()->getContent()
        );
    }

    /**
     * Walking every event in the database must not write one log line per unroutable event.
     *
     * eventQuery() now requires the accepted pivot's schedule to have an owner, so an event
     * whose only accepted schedule is an ownerless placeholder is excluded by the query rather
     * than skipped mid-walk - a better outcome, but it means the section only exists at all
     * when something routable is in it. Hence the routable sibling.
     */
    public function test_unroutable_events_are_skipped_without_logging_an_error(): void
    {
        $owner = $this->createOwner();
        $routableRole = $this->createRole($owner, 'talent');
        $routable = $this->createEvent($routableRole, ['name' => 'Routable', 'creator_role_id' => $routableRole->id]);

        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, ['creator_role_id' => null]);
        DB::table('roles')->where('id', $role->id)->update(['user_id' => null]);

        Log::spy();

        $xml = $this->xml('/sitemap-events-1.xml');

        $this->assertStringContainsString($routable->slug, $xml);
        $this->assertStringNotContainsString($event->slug, $xml);

        Log::shouldNotHaveReceived('error');
    }

    /** An event accepted only on a schedule nobody owns has no routable URL, so it stays out. */
    public function test_events_accepted_only_on_an_ownerless_schedule_are_excluded(): void
    {
        $owner = $this->createOwner();
        $routableRole = $this->createRole($owner, 'talent');
        $routable = $this->createEvent($routableRole, ['name' => 'Routable', 'creator_role_id' => $routableRole->id]);

        // The shape a guest submission leaves behind: pending on the claimed schedule it was
        // submitted to, accepted on the placeholder venue the importer auto-created.
        $placeholder = $this->createRole($owner, 'venue');
        DB::table('roles')->where('id', $placeholder->id)->update(['user_id' => null]);
        $orphan = $this->createEvent($routableRole, [
            'name' => 'Orphan',
            'creator_role_id' => $routableRole->id,
        ]);
        // createEvent() cannot express a null pivot - its `$attrs['is_accepted'] ?? true`
        // reads null as absent - so demote it afterwards.
        $orphan->roles()->updateExistingPivot($routableRole->id, ['is_accepted' => null]);
        $orphan->roles()->attach($placeholder->id, ['is_accepted' => true]);

        $xml = $this->xml('/sitemap-events-1.xml');

        $this->assertStringContainsString($routable->slug, $xml);
        $this->assertStringNotContainsString($orphan->slug, $xml);
    }

    public function test_hidden_events_are_excluded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $visible = $this->createEvent($role, ['name' => 'Visible', 'creator_role_id' => $role->id]);
        $hidden = [
            $this->createEvent($role, ['name' => 'Private', 'is_private' => true, 'creator_role_id' => $role->id]),
            $this->createEvent($role, ['name' => 'Draft', 'is_draft' => true, 'creator_role_id' => $role->id]),
            $this->createEvent($role, ['name' => 'Cancelled', 'is_cancelled' => true, 'creator_role_id' => $role->id]),
            $this->createEvent($role, ['name' => 'Locked', 'event_password' => 'secret', 'creator_role_id' => $role->id]),
            $this->createEvent($role, ['name' => 'Unaccepted', 'is_accepted' => false, 'creator_role_id' => $role->id]),
        ];

        $xml = $this->xml('/sitemap-events-1.xml');

        $this->assertStringContainsString($visible->slug, $xml);

        foreach ($hidden as $event) {
            $this->assertStringNotContainsString($event->slug, $xml, $event->name.' should not be listed');
        }
    }

    /**
     * A single XML-illegal control character makes the whole document non-well-formed, so Google
     * rejects the file rather than the offending row. htmlspecialchars does not strip them.
     */
    public function test_control_characters_in_a_slug_do_not_break_the_document(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $group = $this->createGroup($role);
        DB::table('groups')->where('id', $group->id)->update(['slug' => "bad\x08slug"]);

        $this->assertSame('urlset', $this->parse($this->xml('/sitemap-schedules-1.xml'))->getName());
    }

    public function test_blog_posts_are_listed_and_paginated(): void
    {
        config(['app.sitemap_urls_per_file' => 2]);

        $slugs = [];
        for ($i = 0; $i < 3; $i++) {
            $post = new BlogPost;
            $post->title = 'Post '.$i;
            $post->slug = 'post-'.$i.'-'.strtolower(Str::random(4));
            $post->content = 'Body';
            $post->is_published = true;
            $post->published_at = now()->subDays($i + 1);
            $post->save();

            $slugs[] = $post->slug;
        }

        $children = $this->advertisedChildren();
        $this->assertContains('/sitemap-blog-1.xml', $children);
        $this->assertContains('/sitemap-blog-2.xml', $children);

        $seen = [];
        foreach (['/sitemap-blog-1.xml', '/sitemap-blog-2.xml'] as $path) {
            $xml = $this->xml($path);
            $this->assertLessThanOrEqual(2, substr_count($xml, '<loc>'), $path.' exceeds the cap');

            foreach ($slugs as $slug) {
                if (str_contains($xml, $slug)) {
                    $seen[] = $slug;
                }
            }
        }

        $this->assertEqualsCanonicalizing($slugs, $seen);
    }

    public function test_unknown_sections_are_not_served(): void
    {
        // The route constraint keeps an unknown name out of the controller entirely, so this URL
        // falls through to whatever the app does with any other unknown path. All that matters
        // here is that it is never answered with a sitemap.
        $this->assertNotSame(200, $this->get('/sitemap-bogus.xml')->getStatusCode());

        // A well-formed name that is not in the index does reach the controller, and 404s there.
        $this->get('/sitemap-schedules-0.xml')->assertNotFound();
        $this->get('/sitemap-events-0.xml')->assertNotFound();
        $this->get('/sitemap-events-99.xml')->assertNotFound();
    }

    /** Query strings no longer fan out the cache or change the response. */
    public function test_legacy_query_parameters_are_ignored(): void
    {
        $plain = $this->xml('/sitemap.xml');

        $this->assertSame($plain, $this->xml('/sitemap.xml?events=1'));
        $this->assertSame($plain, $this->xml('/sitemap.xml?roles=1'));
    }

    /** Peak memory must not scale with row count. */
    public function test_streaming_memory_does_not_scale_with_row_count(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        for ($i = 0; $i < 5; $i++) {
            $this->createEvent($role, ['name' => 'Small '.$i, 'creator_role_id' => $role->id]);
        }

        gc_collect_cycles();
        $before = memory_get_usage();
        $this->xml('/sitemap-events-1.xml');
        $small = memory_get_usage() - $before;

        for ($i = 0; $i < 100; $i++) {
            $this->createEvent($role, ['name' => 'Large '.$i, 'creator_role_id' => $role->id]);
        }

        gc_collect_cycles();
        $before = memory_get_usage();
        $this->xml('/sitemap-events-1.xml');
        $large = memory_get_usage() - $before;

        $this->assertSame(105, Role::find($role->id)->events()->count());
        // 21x the rows must not mean 21x the retained memory. Generous bound: the point is that
        // the whole result set is never resident, not a precise byte budget.
        $this->assertLessThan(max(2 * 1024 * 1024, $small * 4 + 512 * 1024), $large);
    }
}
