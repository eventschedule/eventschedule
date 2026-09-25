<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\Feature\Concerns\ForcesEnvironment;
use Tests\TestCase;

/**
 * An event URL whose slug is a word a route owns.
 *
 * A route registered ahead of the event routes, with a literal first segment and a parameter after
 * it, takes /{slug}/{id} for itself when the slug is that word: an event slugged "carpool" opened
 * its carpool board, one slugged "curate-event" a GET that curates it into the schedule in the
 * address, and a venue's event whose act is called "book" - the other schedule's subdomain is the
 * slug there - the booking form. Event::getGuestUrlData() writes such a slug as "{slug}-event",
 * which the id makes harmless: every guest route resolves the event by its id.
 */
class ShadowedEventSlugTest extends TestCase
{
    use CreatesScheduleData;
    use ForcesEnvironment;
    use RefreshDatabase;

    /**
     * The URLs an event carries its id in: its page, a dated occurrence, their galleries and their
     * .ics downloads. %s is the slug.
     *
     * @return array<int, string>
     */
    private function idUrlShapes(): array
    {
        $id = UrlUtils::encodeId(123);

        return [
            "/%s/{$id}",
            "/%s/{$id}/2026-10-04",
            "/%s/{$id}/photos",
            "/%s/{$id}/2026-10-04/photos",
            "/%s/{$id}/ical",
            "/%s/{$id}/2026-10-04/ical",
        ];
    }

    /**
     * Every word that sends one of an event's id URLs to another route, read from the route table
     * the way UrlUtils::reservedPathSlugs() reads it: each literal first segment, less the
     * {subdomain}/ prefix selfhost puts in front, is tried as the slug of every idUrlShapes() URL,
     * and kept when the route that answers is not the one an ordinary slug reaches.
     *
     * @return array<int, string>
     */
    private function wordsTheRoutesShadow(bool $hosted): array
    {
        $words = [];

        foreach (app('router')->getRoutes()->getRoutes() as $route) {
            $uri = $route->uri();

            if (str_starts_with($uri, '{subdomain}/')) {
                $uri = substr($uri, strlen('{subdomain}/'));
            }

            $segment = explode('/', $uri)[0];

            if ($segment !== '' && ! str_contains($segment, '{')) {
                $words[$segment] = true;
            }
        }

        $ordinary = array_map(fn (string $shape) => $this->routeFor($hosted, sprintf($shape, 'an-ordinary-event')), $this->idUrlShapes());
        $shadowed = [];

        foreach (array_keys($words) as $word) {
            foreach ($this->idUrlShapes() as $i => $shape) {
                if ($this->routeFor($hosted, sprintf($shape, $word)) !== $ordinary[$i]) {
                    $shadowed[] = (string) $word;

                    break;
                }
            }
        }

        sort($shadowed);

        return $shadowed;
    }

    /** The route that answers $path on a schedule's own host (hosted) or under its path (selfhost). */
    private function routeFor(bool $hosted, string $path): ?string
    {
        $base = parse_url(config('app.url'), PHP_URL_HOST);
        $url = $hosted ? "https://some-schedule.{$base}{$path}" : "https://{$base}/some-schedule{$path}";

        try {
            $route = app('router')->getRoutes()->match(Request::create($url));
        } catch (NotFoundHttpException|MethodNotAllowedHttpException) {
            return null;
        }

        return $route->getDomain().' '.$route->uri();
    }

    /** The list covers every word $words names, and the "-event" form of each reaches the event. */
    private function assertTheListCovers(bool $hosted, array $words): void
    {
        $mode = $hosted ? 'hosted' : 'selfhost';

        $this->assertContains('carpool', $words, "fixture: the {$mode} routes shadow the carpool board");
        $this->assertSame([], array_values(array_diff($words, Event::SHADOWED_SLUGS)),
            "Event::SHADOWED_SLUGS is missing a word a {$mode} route owns");

        foreach (Event::SHADOWED_SLUGS as $word) {
            foreach ($this->idUrlShapes() as $shape) {
                $this->assertSame(
                    $this->routeFor($hosted, sprintf($shape, 'an-ordinary-event')),
                    $this->routeFor($hosted, sprintf($shape, Event::guestUrlSlug($word))),
                    "{$mode}: ".sprintf($shape, Event::guestUrlSlug($word))
                );
            }
        }
    }

    /**
     * Both route tables, derived: the path routes the test environment registers (selfhost's, and
     * its admin routes under /{subdomain}/..., which come first), and the hosted tenant group.
     */
    public function test_the_list_is_exactly_the_words_the_route_tables_shadow(): void
    {
        $this->assertFalse(config('app.hosted') && ! config('app.is_testing'), 'fixture: the path-based routes');
        $selfhost = $this->wordsTheRoutesShadow(false);
        $this->assertTheListCovers(false, $selfhost);

        // The hosted tenant group is gated on `hosted && ! is_testing`, so only a rebuilt app has
        // it. The sequence is RouteLoadTest::test_hosted_gp_routes_load()'s.
        $this->app['db']->connection()->rollBack();
        $this->forceEnv('IS_HOSTED', 'true');
        $this->forceEnv('APP_TESTING', 'false');
        $this->refreshApplication();
        $this->app['db']->connection()->beginTransaction();

        try {
            $this->assertTrue(config('app.hosted') && ! config('app.is_testing'), 'fixture: the hosted tenant group');
            $hosted = $this->wordsTheRoutesShadow(true);
            $this->assertTheListCovers(true, $hosted);
        } finally {
            // See RouteLoadTest::test_hosted_gp_routes_load() for why this closes its own transaction.
            $connection = $this->app['db']->connection();

            if ($connection->getPdo() && $connection->getPdo()->inTransaction()) {
                $connection->rollBack();
            }

            $connection->disconnect();
        }

        // And nothing on the list that neither table shadows.
        $union = array_values(array_unique(array_merge($selfhost, $hosted)));
        $listed = Event::SHADOWED_SLUGS;
        sort($union);
        sort($listed);

        $this->assertSame($union, $listed);
    }

    /**
     * An event slugged each word opens its own page, dated occurrence, gallery and .ics - on the
     * path routes, where the admin words shadow too.
     */
    public function test_an_event_slugged_a_route_word_opens_its_own_pages(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue');
        // A Sunday afternoon in New York, the first of a weekly series; the occurrence a week later.
        $start = Carbon::now('UTC')->next(Carbon::SUNDAY)->setTime(18, 0);
        $occurrence = $start->copy()->addWeek()->format('Y-m-d');

        foreach (Event::SHADOWED_SLUGS as $word) {
            $event = $this->createRecurringEvent($venue, [
                'name' => 'Night of '.$word,
                'slug' => $word,
                'days_of_week' => '1000000',
                'starts_at' => $start->format('Y-m-d H:i:s'),
                'creator_role_id' => $venue->id,
                'fan_photos_enabled' => true,
            ]);

            $this->assertTrue($event->matchesDate($occurrence, $event->scheduleTimezone()), 'fixture: an occurrence');

            foreach ([false, $occurrence] as $date) {
                $this->get($event->getGuestUrl($venue->subdomain, $date))
                    ->assertOk()
                    ->assertViewIs('event.show-guest')
                    ->assertSee('Night of '.$word);

                $this->get($event->getPhotoGalleryUrl($venue->subdomain, $date))
                    ->assertOk()
                    ->assertViewIs('event.photo-gallery');

                $ical = $this->get($event->getAppleCalendarUrl($date ?: null, $venue->subdomain))->assertOk();
                $this->assertStringStartsWith('text/calendar', (string) $ical->headers->get('Content-Type'), $word);
            }
        }
    }

    /**
     * On a venue's host its event takes the act's subdomain as its slug, so an act called "book"
     * sent the venue's link to the booking form.
     */
    public function test_a_venues_event_with_an_act_called_book_opens_on_the_venue(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue');
        $act = $this->createRole($this->createOwner(), 'talent', ['subdomain' => 'book', 'name' => 'The Book Club Band']);

        $event = $this->createEvent($venue, ['name' => 'Songs From The Stacks', 'creator_role_id' => $venue->id]);
        $event->roles()->attach($act->id, ['is_accepted' => true]);
        $event = $event->fresh();

        $this->assertSame('book-event', $event->getGuestUrlData($venue->subdomain)['slug']);

        $this->get($event->getGuestUrl($venue->subdomain))
            ->assertOk()
            ->assertViewIs('event.show-guest')
            ->assertSee('Songs From The Stacks');
    }

    /**
     * /curate-event/{hash} is a GET that attaches the event to the schedule in its address, so an
     * event URL that reached it wrote to the database: a link on a schedule that has since dropped
     * the event, revisited by a crawler, filed it there again as a request.
     */
    public function test_a_curate_event_slugged_url_attaches_nothing(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue');
        $curator = $this->createCurator($this->createOwner());
        $this->assertTrue((bool) $curator->fresh()->acceptEventRequests(), 'fixture: the curator takes requests');

        $event = $this->createEvent($venue, ['name' => 'Curated Night', 'slug' => 'curate-event', 'creator_role_id' => $venue->id]);

        $this->get($event->getGuestUrl($venue->subdomain))->assertOk()->assertSee('Curated Night');

        // The curator's link to it, once the curator has dropped it: a stranger, then its editor.
        $link = $event->getGuestUrl($curator->subdomain);
        $this->get($link)->assertNotFound();
        $this->actingAs(User::find($curator->user_id))->get($link)->assertNotFound();

        $this->assertSame([$venue->id], DB::table('event_role')->where('event_id', $event->id)->pluck('role_id')->all());
    }

    /**
     * The links built from the event's own slug rather than getGuestUrlData(): the carpool page's
     * way back to its event, and the agenda editor's "view" links.
     */
    public function test_links_built_from_the_raw_slug_reach_the_event_too(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['carpool_enabled' => true]);
        $rides = $this->createEvent($venue, ['name' => 'Ride Share Night', 'slug' => 'carpool', 'creator_role_id' => $venue->id]);
        $agenda = $this->createEvent($venue, ['name' => 'Agenda Night', 'slug' => 'edit-event', 'creator_role_id' => $venue->id]);

        $this->actingAs($owner);

        // The carpool page's back link, which on this slug led back to the carpool page itself.
        $board = $this->get(route('carpool.index', ['subdomain' => $venue->subdomain, 'event_hash' => UrlUtils::encodeId($rides->id)]))
            ->assertOk()
            ->getContent();
        $back = '~<a href="([^"]+)"[^>]*>\s*(?:←|→)\s*'.preg_quote(__('messages.carpool_back_to_event'), '~').'~u';
        $this->assertSame(1, preg_match($back, $board, $m), 'the carpool page links back to its event');
        $this->get(html_entity_decode($m[1]))->assertOk()->assertViewIs('event.show-guest')->assertSee('Ride Share Night');

        // The agenda scanner's event list, where "edit-event" led to the event's edit form.
        $scan = $this->get(route('event.scan_agenda', ['subdomain' => $venue->subdomain]))->assertOk();
        $listed = collect($scan->viewData('eventsData'))->keyBy('name');
        $this->assertSame(['Agenda Night', 'Ride Share Night'], $listed->keys()->sort()->values()->all());

        foreach ($listed as $name => $item) {
            $this->get($item['view_url'])->assertOk()->assertViewIs('event.show-guest')->assertSee($name);
        }

        // And the link the agenda save answers with.
        $saved = $this->postJson(route('event.save_parts', ['subdomain' => $venue->subdomain]), [
            'event_id' => UrlUtils::encodeId($agenda->id),
            'parts' => [['name' => 'Opening set']],
        ])->assertOk()->json();

        $this->get($saved['view_url'])->assertOk()->assertViewIs('event.show-guest')->assertSee('Agenda Night');
    }

    /** On selfhost the global /map-image/{id} route takes a whole schedule of that name. */
    public function test_a_schedule_cannot_be_named_map_image(): void
    {
        $this->assertContains('map-image', Role::RESERVED_SUBDOMAINS);
        $this->assertNotSame('map-image', Role::generateSubdomain('Map Image'));
        $this->assertNotSame('map-image', Role::cleanSubdomain('map-image'));
    }
}
