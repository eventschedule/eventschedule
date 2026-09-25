<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * One canonical per recurring series, on a host that actually serves the event.
 *
 *  - Every dated occurrence /{slug}/{id}/{Y-m-d} canonicalizes to the undated series URL
 *    /{slug}/{id}, and the series URL is self-canonical. Each dated URL used to be its own
 *    canonical - 52 competing pages a year for a weekly event - and the undated URL pointed at
 *    "today's" next occurrence, a target that moved every day.
 *  - The dated URLs still render (sales, tickets and email link to them) and keep the occurrence
 *    in og:url, the share target. Everything else on the page names the series.
 *  - The canonical host has to have ACCEPTED the event. It used to be the claimed performer
 *    whatever their pivot said, and every guest lookup requires is_accepted on the host, so a
 *    performer still pending on a venue's event got a canonical - and a sitemap entry - that 404s.
 *  - The photo gallery is the exception: each night's gallery shows that night's photos, so a
 *    dated gallery is its own canonical, on the same home host, and the undated one is the series
 *    gallery.
 *
 * getGuestUrl() is not canonicalized: email, graphics and in-app links keep their dates, and a
 * link that names its schedule keeps that host. One that names no schedule goes to a schedule
 * that serves the event (EventLinkServingScheduleTest).
 */
class RecurringSeriesCanonicalTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** A Sunday at noon, $addWeeks from two weeks out, so the UTC and New York dates agree. */
    private function sunday(int $addWeeks = 0): Carbon
    {
        return Carbon::now()->startOfWeek(Carbon::SUNDAY)->addWeeks(2 + $addWeeks)->setTime(12, 0);
    }

    /** A Sundays-only weekly series on $role, created by it. */
    private function sundaySeries(Role $role, array $attrs = []): Event
    {
        return $this->createRecurringEvent($role, array_merge([
            'name' => 'Sunday Yin Yoga',
            'days_of_week' => '1000000',
            'recurring_frequency' => 'weekly',
            'starts_at' => $this->sunday()->format('Y-m-d H:i:s'),
            'creator_role_id' => $role->id,
        ], $attrs));
    }

    /** /{subdomain}/{slug}/{id}, with whatever slug the URL is expected to carry. */
    private function eventPath(Role $host, string $slug, Event $event, bool $absolute = true): string
    {
        return route('event.view_guest_with_id', [
            'subdomain' => $host->subdomain,
            'slug' => $slug,
            'id' => UrlUtils::encodeId($event->id),
        ], $absolute);
    }

    private function match(string $html, string $pattern): ?string
    {
        return preg_match($pattern, $html, $m) ? html_entity_decode($m[1]) : null;
    }

    private function canonical(string $html): ?string
    {
        return $this->match($html, '~<link rel="canonical" href="([^"]*)">~');
    }

    private function ogUrl(string $html): ?string
    {
        return $this->match($html, '~<meta property="og:url" content="([^"]*)">~');
    }

    private function hreflang(string $html, string $lang): ?string
    {
        return $this->match($html, '~<link rel="alternate" hreflang="'.$lang.'" href="([^"]*)">~');
    }

    /** The decoded JSON-LD node of $type, or null. */
    private function jsonLd(string $html, string $type): ?array
    {
        preg_match_all('~<script type="application/ld\+json"[^>]*>(.*?)</script>~s', $html, $m);

        foreach ($m[1] as $raw) {
            $node = json_decode($raw, true);

            if (($node['@type'] ?? null) === $type) {
                return $node;
            }
        }

        return null;
    }

    /** The URLs a sitemap document lists. */
    private function sitemapLocs(string $path): array
    {
        $xml = simplexml_load_string($this->get($path)->assertOk()->streamedContent());

        return collect(iterator_to_array($xml->url, false))->map(fn ($node) => (string) $node->loc)->all();
    }

    public function test_the_series_page_is_self_canonical(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        // Started three weeks ago, so the occurrence the page shows is not the series' first date.
        // Taking RSVPs, so the node has an offer whose url to check.
        $event = $this->sundaySeries($role, [
            'starts_at' => $this->sunday(-5)->format('Y-m-d H:i:s'),
            'rsvp_enabled' => true,
        ]);
        $series = $this->guestEventUrl($role, $event);
        $next = $event->nextOccurrenceFrom();

        $html = $this->get($series)->assertOk()->getContent();
        $node = $this->jsonLd($html, 'Event');

        $this->assertSame($series, $this->canonical($html), 'the undated URL used to canonicalize to its next occurrence');
        $this->assertSame($series, $this->ogUrl($html));
        $this->assertSame($series, $node['url']);
        $this->assertSame($series.'?rsvp=true', $node['offers']['url']);
        // The dates are still the occurrence the page shows: the next one.
        $this->assertStringStartsWith($next.'T', $node['startDate']);
    }

    public function test_a_dated_occurrence_canonicalizes_to_the_series_and_keeps_its_share_url(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->sundaySeries($role, ['rsvp_enabled' => true]);
        $series = $this->guestEventUrl($role, $event);
        $occurrence = $this->sunday(2)->format('Y-m-d');
        $dated = $this->guestEventUrl($role, $event, $occurrence);

        $html = $this->get($dated)->assertOk()->getContent();
        $node = $this->jsonLd($html, 'Event');
        $crumbs = $this->jsonLd($html, 'BreadcrumbList')['itemListElement'];

        $this->assertSame($series, $this->canonical($html));
        // og:url is the share target, so a share of this page opens this occurrence.
        $this->assertSame($dated, $this->ogUrl($html));
        $this->assertSame($series, $node['url']);
        $this->assertSame($series.'?rsvp=true', $node['offers']['url']);
        $this->assertSame($series, end($crumbs)['item']);
        // The occurrence this page is about. Noon UTC is the morning of the same day in New York.
        $this->assertStringStartsWith($occurrence.'T', $node['startDate']);
        $this->assertSame($event->getSchemaStartDate($occurrence), $node['startDate']);

        // The legacy ?date= form names the occurrence just as the path does.
        $html = $this->get($series.'?date='.$occurrence)->assertOk()->getContent();
        $this->assertSame($series, $this->canonical($html));
        $this->assertSame($dated, $this->ogUrl($html));
    }

    public function test_the_password_page_names_the_series(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->sundaySeries($role, ['event_password' => 'letmein']);

        $html = $this->get($this->guestEventUrl($role, $event, $this->sunday(2)->format('Y-m-d')))
            ->assertOk()
            ->assertSee(__('messages.event_password_required'))
            ->getContent();

        $this->assertSame($this->guestEventUrl($role, $event), $this->ogUrl($html));
    }

    public function test_hreflang_names_the_series_on_the_series_page_and_on_every_occurrence(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'language_code' => 'en',
            'translation_language_code' => 'es',
        ]);
        $event = $this->sundaySeries($role);
        $series = $this->guestEventUrl($role, $event);
        $dated = $this->guestEventUrl($role, $event, $this->sunday(2)->format('Y-m-d'));

        foreach ([$series, $dated] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertSame($series, $this->hreflang($html, 'en'), $url);
            $this->assertSame($series, $this->hreflang($html, 'x-default'), $url);
            $this->assertSame($series.'?lang=es', $this->hreflang($html, 'es'), $url);
        }

        // The alternate language keeps its suffix on the series canonical.
        $html = $this->get($dated.'?lang=es')->assertOk()->getContent();
        $this->assertSame($series.'?lang=es', $this->canonical($html));
    }

    public function test_the_series_is_canonical_on_a_direct_custom_domain_only(): void
    {
        $owner = $this->createOwner();
        $direct = $this->createRole($owner, 'talent', [
            'custom_domain' => 'https://series-direct.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);
        $event = $this->sundaySeries($direct);
        $occurrence = $this->sunday(2)->format('Y-m-d');
        $relativeDated = route('event.view_guest_full', [
            'subdomain' => $direct->subdomain,
            'slug' => $event->slug,
            'id' => UrlUtils::encodeId($event->id),
            'date' => $occurrence,
        ], false);

        // Loaded on the subdomain, as the custom-domain middleware never runs here.
        $html = $this->get($this->guestEventUrl($direct, $event, $occurrence))->assertOk()->getContent();

        $this->assertSame('https://series-direct.test'.$this->eventPath($direct, $event->slug, $event, false), $this->canonical($html));
        $this->assertSame('https://series-direct.test'.$relativeDated, $this->ogUrl($html));

        // Redirect mode 301s the custom domain to the subdomain, which therefore stays canonical.
        $redirect = $this->createRole($owner, 'talent', [
            'custom_domain' => 'https://series-redirect.test',
            'custom_domain_mode' => 'redirect',
        ]);
        $event = $this->sundaySeries($redirect);

        $html = $this->get($this->guestEventUrl($redirect, $event, $occurrence))->assertOk()->getContent();

        $this->assertSame($this->guestEventUrl($redirect, $event), $this->canonical($html));
    }

    /**
     * The production case: a venue lists a performer who has not accepted yet. getGuestUrlData()
     * picks the claimed performer, whose host 404s the event, so the canonical and the sitemap
     * both pointed there. It belongs on the venue until the performer accepts.
     */
    public function test_a_pending_performer_hands_the_canonical_to_the_venue_that_accepted(): void
    {
        foreach (['one-off' => false, 'series' => true] as $label => $recurring) {
            // A fresh pair each time: with its id unresolved, EventRepo::getEvent() falls back to
            // the next event BOTH schedules accepted, which a shared pair would make the last
            // iteration's event, and the performer's URL would answer 200 with the wrong page.
            $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'The Hall']);
            $talent = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Act']);
            $attrs = ['name' => 'Double Bill '.$label, 'creator_role_id' => $venue->id];
            $event = $recurring ? $this->sundaySeries($venue, $attrs) : $this->createEvent($venue, $attrs);
            $event->roles()->attach($talent->id, ['is_accepted' => null]);
            $event = $event->fresh();

            // What the canonical used to be: the performer's host, which does not serve the event.
            $performerUrl = $this->eventPath($talent, $venue->subdomain, $event);
            $this->get($performerUrl)->assertNotFound();

            // Now the venue's, with the slug the venue's own calendar links to.
            $venueUrl = $this->eventPath($venue, $talent->subdomain, $event);
            [$url, $home] = $event->canonicalTarget();
            $this->assertSame($venueUrl, $url, $label);
            $this->assertSame($venue->id, $home?->id, $label);

            // A link that names no schedule used to be the performer's as well; it now goes where
            // the canonical does (EventLinkServingScheduleTest).
            $this->assertSame($venueUrl, $event->getUndatedGuestUrl(), "$label: the plain link");

            $html = $this->get($venueUrl)->assertOk()->getContent();
            $this->assertSame($venueUrl, $this->canonical($html), "$label: the canonical is itself a 200");

            // The sitemap lists what the canonical names.
            $locs = $this->sitemapLocs('/sitemap-events-1.xml');
            $this->assertContains($venueUrl, $locs, $label);
            $this->assertNotContains($performerUrl, $locs, $label);

            // Once the performer accepts, their host serves the event and takes the canonical back.
            DB::table('event_role')->where('event_id', $event->id)->where('role_id', $talent->id)->update(['is_accepted' => true]);
            $event = $event->fresh();

            $this->assertSame($performerUrl, $event->getCanonicalUrl(), $label);
            $this->get($performerUrl)->assertOk();
        }
    }

    public function test_a_curator_event_with_acts_still_pending_is_canonical_on_the_curator(): void
    {
        $curator = $this->createCurator($this->createOwner());
        $venue = $this->createRole($this->createOwner(), 'venue');
        $talent = $this->createRole($this->createOwner(), 'talent');
        $event = $this->createEvent($curator, ['creator_role_id' => $curator->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => null]);
        $event->roles()->attach($talent->id, ['is_accepted' => null]);
        $event = $event->fresh();

        // A curator's event keeps its own slug: the venue/performer slug rule skips curators.
        $curatorUrl = $this->eventPath($curator, $event->slug, $event);

        $this->assertSame($curatorUrl, $event->getCanonicalUrl());
        $this->get($curatorUrl)->assertOk();
    }

    public function test_a_deleted_performer_is_never_the_canonical_host(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue');
        $talent = $this->createRole($this->createOwner(), 'talent');
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);
        Role::whereKey($talent->id)->update(['is_deleted' => true]);
        $event = $event->fresh();

        $venueUrl = $this->eventPath($venue, $talent->subdomain, $event);

        $this->assertSame($venueUrl, $event->getCanonicalUrl());
        $this->get($venueUrl)->assertOk();

        // The sitemap narrows the roles it loads, so it has to select is_deleted to agree: an
        // unselected column reads as null, and the deleted performer would win it back.
        $locs = $this->sitemapLocs('/sitemap-events-1.xml');
        $this->assertContains($venueUrl, $locs);
        $this->assertNotContains($this->eventPath($talent, $venue->subdomain, $event), $locs);
    }

    /** A member previewing an event nobody has accepted yet still gets a canonical. */
    public function test_an_event_no_schedule_serves_keeps_its_url_with_no_home(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        DB::table('event_role')->where('event_id', $event->id)->update(['is_accepted' => null]);
        $event = $event->fresh();

        [$url, $home] = $event->canonicalTarget();

        $this->assertSame($this->guestEventUrl($role, $event), $url);
        $this->assertNull($home);

        $html = $this->actingAs($owner)->get($url)->assertOk()->getContent();
        $this->assertSame($url, $this->canonical($html));
    }

    /**
     * Unlike the event page, each gallery is its own canonical. A dated gallery shows that night's
     * photos, which no other gallery does, and canonicalizing it to the undated one pointed it at
     * the NEXT occurrence's photos, a different night every week. The undated gallery is the series
     * gallery, and names no date.
     */
    public function test_each_gallery_is_its_own_canonical(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'language_code' => 'en',
            'translation_language_code' => 'es',
        ]);
        $event = $this->sundaySeries($role, ['fan_photos_enabled' => true]);
        $gallery = $this->guestEventUrl($role, $event).'/photos';
        $datedGallery = $this->guestEventUrl($role, $event, $this->sunday(2)->format('Y-m-d')).'/photos';

        foreach ([$gallery, $datedGallery] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertSame($url, $this->canonical($html), $url);
            $this->assertSame($url, $this->ogUrl($html), $url);
            $this->assertSame($url, $this->hreflang($html, 'en'), $url);
            $this->assertSame($url.'?lang=es', $this->hreflang($html, 'es'), $url);
        }
    }

    /**
     * A dated gallery's canonical keeps its date and moves to the home host, as the event page's
     * og:url does: a venue shows the event, but the performer who accepted it is its home.
     */
    public function test_a_dated_gallery_on_another_host_canonicalizes_to_the_home_hosts(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'The Hall']);
        $talent = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Act']);
        $event = $this->sundaySeries($venue, ['fan_photos_enabled' => true]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);
        $event = $event->fresh();
        $date = $this->sunday(2)->format('Y-m-d');

        // Each host names the other schedule as the slug, the rule getGuestUrlData() applies.
        $venueGallery = route('event.view_guest_full', [
            'subdomain' => $venue->subdomain,
            'slug' => $talent->subdomain,
            'id' => UrlUtils::encodeId($event->id),
            'date' => $date,
        ]).'/photos';
        $homeGallery = route('event.view_guest_full', [
            'subdomain' => $talent->subdomain,
            'slug' => $venue->subdomain,
            'id' => UrlUtils::encodeId($event->id),
            'date' => $date,
        ]).'/photos';

        $html = $this->get($venueGallery)->assertOk()->getContent();

        $this->assertSame($homeGallery, $this->canonical($html));
        $this->assertSame($homeGallery, $this->ogUrl($html));
        $this->assertSame($homeGallery, $this->canonical($this->get($homeGallery)->assertOk()->getContent()), 'the canonical is itself a 200, and self-canonical');
    }

    public function test_a_one_off_event_is_unchanged(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');
        $start = Carbon::now()->addDays(10)->setTime(12, 0);
        $event = $this->createEvent($role, ['starts_at' => $start->format('Y-m-d H:i:s'), 'creator_role_id' => $role->id]);
        $url = $this->guestEventUrl($role, $event);

        // What it has always been: the one URL a one-off event has, which getGuestUrl() builds too.
        $this->assertSame($event->getGuestUrl($role->subdomain), $url);
        $this->assertSame($url, $event->getCanonicalUrl());

        // Its own date in the path renders the same page, which names the one URL throughout.
        foreach ([$url, $this->guestEventUrl($role, $event, $start->format('Y-m-d'))] as $requested) {
            $html = $this->get($requested)->assertOk()->getContent();

            $this->assertSame($url, $this->canonical($html), $requested);
            $this->assertSame($url, $this->ogUrl($html), $requested);
            $this->assertSame($url, $this->jsonLd($html, 'Event')['url'], $requested);
        }
    }

    /**
     * getGuestUrl() still links a series at its first date, for the email, sales and graphics that
     * use it, and that date is the schedule's calendar day rather than the UTC one - an evening show
     * west of UTC falls on the following UTC day, a weekday the series does not run on. The sitemap
     * pinned this while it listed series at their first date (SitemapTest), which it no longer does.
     */
    public function test_get_guest_url_still_carries_the_schedule_local_first_date(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);

        // 02:00 UTC is the previous evening in New York.
        $startsAt = Carbon::now()->addDays(7)->setTime(2, 0);
        $event = $this->createRecurringEvent($role, [
            'starts_at' => $startsAt->format('Y-m-d H:i:s'),
            'creator_role_id' => $role->id,
        ]);

        $localDate = $startsAt->copy()->timezone('America/New_York')->format('Y-m-d');
        $this->assertNotSame($startsAt->format('Y-m-d'), $localDate, 'fixture no longer straddles midnight');

        $this->assertSame($this->guestEventUrl($role, $event, $localDate), $event->getGuestUrl($role->subdomain));
        // The canonical carries neither date.
        $this->assertSame($this->guestEventUrl($role, $event), $event->getCanonicalUrl());
    }
}
