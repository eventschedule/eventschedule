<?php

namespace Tests\Feature;

use App\Console\Commands\FederationMaintenance;
use App\Http\Controllers\MarketingController;
use App\Jobs\GenerateFederatedImageVariants;
use App\Models\Event;
use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Utils\BrowseWall;
use App\Utils\ImageUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Events other Event Schedule sites share, on /browse's own wall (2026-11): among the local
 * posters with their site named, at most BrowseWall::NETWORK_CAP of them, the next few after the
 * wall, and every one of them on the "From other sites" view (?show=network). Their pictures get
 * the light copies local flyers have.
 */
class BrowseNetworkWallTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    protected function tearDown(): void
    {
        Cache::flush();

        parent::tearDown();
    }

    private function makeInstance(array $attributes = []): FederatedInstance
    {
        return FederatedInstance::create(array_merge([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'name' => 'Operator',
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_APPROVED,
        ], $attributes));
    }

    /** A listing that will render, starting $hours from now unless its dates are given. */
    private function makeListing(FederatedInstance $instance, array $attributes = [], float $hours = 30): FederatedEvent
    {
        $start = now()->addMinutes((int) round($hours * 60));

        $listing = FederatedEvent::create(array_merge([
            'federated_instance_id' => $instance->id,
            'external_id' => Str::random(8),
            'url' => 'https://operator.test/venue/summer-show',
            'name' => 'Federated Summer Show',
            'language' => 'en',
            'country_code' => 'DE',
            'city' => 'Berlin',
            'timezone' => 'Europe/Berlin',
            'starts_at' => $start,
            'next_occurrence_at' => $start,
        ], $attributes));

        $listing->image_path = $attributes['image_path'] ?? 'federated_flyer.jpg';
        $listing->save();

        return $listing;
    }

    private function remoteTiles(array $tiles): array
    {
        return array_values(array_filter($tiles, fn ($tile) => ! empty($tile['remote'])));
    }

    /**
     * A listing on the mixed wall is a poster like the rest: its schedule's name links to the
     * schedule's own page, the site it comes from is named under it, the link says where it
     * goes, and a globe marks the picture. Mutation: drop the caption badge, or the label.
     */
    public function test_a_listing_stands_on_the_wall_with_its_site_named(): void
    {
        $listing = $this->makeListing($this->makeInstance(), [
            'schedule_name' => 'Example Club',
            'schedule_url' => 'https://operator.test/example',
        ]);

        $response = $this->get('/browse')->assertOk();

        $this->assertCount(1, $this->remoteTiles($response->viewData('wall')['tiles']));
        $response
            ->assertSee('Federated Summer Show')
            ->assertSee('aria-label="Federated Summer Show, on operator.test, opens in a new tab"', false)
            ->assertSee('<a href="https://operator.test/example" target="_blank" rel="noopener">Example Club</a>', false)
            ->assertSee('<bdi dir="ltr">operator.<wbr>test</bdi>', false)
            ->assertSee('class="bw-globe"', false)
            ->assertSee('data-federated-click="'.UrlUtils::encodeId($listing->id).'"', false)
            // And the switch to their view, with the count.
            ->assertSee('href="'.marketing_url('/browse?show=network').'"', false)
            ->assertSee('From other sites');
    }

    /**
     * One busy site cannot fill the wall: four of theirs at most, the next ones in the row after
     * it with "See all". Mutation: raise NETWORK_CAP in pickMixed's loop, or drop the teaser.
     */
    public function test_at_most_four_listings_stand_on_the_wall_and_the_next_follow_it(): void
    {
        $instance = $this->makeInstance();

        foreach (range(1, 7) as $i) {
            $this->makeListing($instance, ['name' => 'Night '.$i, 'schedule_url' => 'https://operator.test/s'.$i], 20 + $i);
        }

        $response = $this->get('/browse')->assertOk();

        $wall = $this->remoteTiles($response->viewData('wall')['tiles']);
        $this->assertSame(['Night 1', 'Night 2', 'Night 3', 'Night 4'], array_column($wall, 'name'));
        $this->assertSame(['Night 5', 'Night 6', 'Night 7'], array_column($response->viewData('teaser')['tiles'], 'name'));
        $this->assertSame(7, $response->viewData('networkTotal'));
        $response->assertSee('More from other sites')->assertSee('See all 7');
    }

    /** The wall counts a site's schedule once; their own view lists every event of it. */
    public function test_one_poster_per_schedule_on_the_wall_and_every_one_on_their_view(): void
    {
        $instance = $this->makeInstance();
        $this->makeListing($instance, ['name' => 'First Night', 'schedule_url' => 'https://operator.test/club'], 20);
        $this->makeListing($instance, ['name' => 'Second Night', 'schedule_url' => 'https://operator.test/club'], 40);

        $wall = $this->remoteTiles($this->get('/browse')->viewData('wall')['tiles']);
        $this->assertSame(['First Night'], array_column($wall, 'name'));

        $view = $this->remoteTiles($this->get('/browse?show=network')->viewData('wall')['tiles']);
        $this->assertSame(['First Night', 'Second Night'], array_column($view, 'name'));
    }

    /** Local events and listings share the 24 places and the order they are on in. */
    public function test_local_events_and_listings_are_ordered_together(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->createEvent($role, [
            'name' => 'Local Later',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
            'starts_at' => now()->addHours(50)->format('Y-m-d H:i:s'),
        ]);
        $this->makeListing($this->makeInstance(), ['name' => 'Remote Sooner'], 10);

        $names = array_column(array_filter($this->get('/browse')->viewData('wall')['tiles'], fn ($tile) => isset($tile['name'])), 'name');

        $this->assertSame(['Remote Sooner', 'Local Later'], array_values($names));
    }

    /**
     * Their view is a view: its own title, kept out of the index, this site's events and its
     * structured data left out, the admin's hidden lists too.
     */
    public function test_their_view_is_titled_and_kept_out_of_the_index(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->createEvent($role, ['name' => 'Local Showcase', 'flyer_image_url' => 'flyer.jpg', 'creator_role_id' => $role->id]);
        $this->makeListing($this->makeInstance());

        $this->get('/browse?show=network')
            ->assertOk()
            ->assertSee('<title>Events From Other Sites | Event Schedule</title>', false)
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('Federated Summer Show')
            ->assertDontSee('Local Showcase')
            ->assertDontSee('"@type":"ItemList"', false)
            ->assertSee('aria-current="page"', false);

        $this->get('/browse')
            ->assertOk()
            ->assertSee('Local Showcase')
            ->assertDontSee('noindex, follow', false);
    }

    /** Any of the view's own filters opens it, without show=network. */
    public function test_a_filter_opens_their_view(): void
    {
        $this->makeListing($this->makeInstance());

        foreach (['country=DE', 'language=en', 'federated_limit=48', 'instance='.UrlUtils::encodeId(1)] as $query) {
            $this->assertTrue($this->get('/browse?'.$query)->viewData('networkView'), $query);
        }

        $this->assertFalse($this->get('/browse?lang=en')->viewData('networkView'));
    }

    /**
     * 24 at a time, and "Show more" lands on the first poster it adds. Mutation: take the anchor
     * from the current page's first-added item.
     */
    public function test_show_more_lands_on_the_first_poster_it_adds(): void
    {
        $instance = $this->makeInstance();
        $listings = [];

        foreach (range(1, 30) as $i) {
            $listings[] = $this->makeListing($instance, ['name' => 'Gig '.$i], 20 + $i);
        }

        $first = $this->get('/browse?show=network')->assertOk();
        $this->assertCount(24, $this->remoteTiles($first->viewData('wall')['tiles']));
        $first->assertSee('federated_limit=48#l-'.UrlUtils::encodeId($listings[24]->id), false);

        $second = $this->get('/browse?show=network&federated_limit=48')->assertOk();
        $this->assertCount(30, $this->remoteTiles($second->viewData('wall')['tiles']));
        $second->assertSee('id="l-'.UrlUtils::encodeId($listings[24]->id).'"', false)
            ->assertDontSee(__('messages.federation_show_more'));
    }

    /** Past NETWORK_VIEW_CAP the view says how to narrow it rather than scroll on. */
    public function test_their_view_stops_at_its_cap(): void
    {
        $instance = $this->makeInstance();
        $start = now()->addDay();
        $rows = [];

        foreach (range(1, MarketingController::NETWORK_VIEW_CAP + 1) as $i) {
            $at = $start->copy()->addMinutes($i)->format('Y-m-d H:i:s');
            $rows[] = [
                'federated_instance_id' => $instance->id,
                'external_id' => 'bulk-'.$i,
                'url' => 'https://operator.test/e/'.$i,
                'name' => 'Bulk '.$i,
                'timezone' => 'UTC',
                'starts_at' => $at,
                'next_occurrence_at' => $at,
                'image_path' => 'federated_bulk.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        FederatedEvent::insert($rows);

        $response = $this->get('/browse?show=network&federated_limit=9999')->assertOk();

        $this->assertCount(MarketingController::NETWORK_VIEW_CAP, $this->remoteTiles($response->viewData('wall')['tiles']));
        $response->assertSee('Pick a country to narrow it.')
            ->assertDontSee(__('messages.federation_show_more'));
    }

    /** The view's filters keep the view and carry the site it was opened for. */
    public function test_one_sites_view_names_the_site_and_the_way_back(): void
    {
        $mine = $this->makeInstance();
        $other = $this->makeInstance(['instance_id' => (string) Str::uuid(), 'site_url' => 'https://other.test']);
        $this->makeListing($mine, ['name' => 'Mine Gig']);
        $this->makeListing($other, ['name' => 'Other Gig', 'url' => 'https://other.test/e/1']);

        $this->get('/browse?show=network&instance='.UrlUtils::encodeId($mine->id))
            ->assertOk()
            ->assertSee('Mine Gig')
            ->assertDontSee('Other Gig')
            ->assertSee('From <bdi dir="ltr">operator.test</bdi>', false)
            ->assertSee('1 event')
            ->assertSee('Show all sites')
            ->assertSee('<input type="hidden" name="show" value="network">', false)
            ->assertSee('<input type="hidden" name="instance" value="'.UrlUtils::encodeId($mine->id).'">', false);
    }

    /**
     * When a listing is on comes from its own dates, never from next_occurrence_at, which is
     * stamped at intake and kept a day after it has passed. Mutation: date from
     * next_occurrence_at, or drop the length from the "still on" test.
     */
    public function test_a_listing_is_dated_by_its_own_dates(): void
    {
        $now = Carbon::parse('2026-11-18 12:00:00', 'UTC');
        $instance = $this->makeInstance();

        // Stored at an occurrence that has passed; its next one is in three days.
        $series = $this->makeListing($instance, [
            'timezone' => 'UTC',
            'starts_at' => '2026-11-16 20:00:00',
            'ends_at' => '2026-11-16 22:00:00',
            'occurrences' => ['2026-11-16T20:00:00Z', '2026-11-21T20:00:00Z'],
            'next_occurrence_at' => '2026-11-17 20:00:00',
        ]);
        $next = BrowseWall::nextListing($series, $now);
        $this->assertSame('2026-11-21 20:00', $next['at']->format('Y-m-d H:i'));
        $this->assertSame('week', BrowseWall::remotePoster($series, $now)['band']);

        // Over two hours ago: listable() still keeps it for a day, the wall does not.
        $over = $this->makeListing($instance, [
            'timezone' => 'UTC',
            'starts_at' => '2026-11-18 08:00:00',
            'ends_at' => '2026-11-18 10:00:00',
            'next_occurrence_at' => '2026-11-18 08:00:00',
        ]);
        $this->assertNull(BrowseWall::nextListing($over, $now));
        $this->assertSame([], BrowseWall::pickMixed(collect(), collect([$over]), now: $now)->all());

        // On now: started an hour ago, two hours long.
        $live = $this->makeListing($instance, [
            'timezone' => 'UTC',
            'starts_at' => '2026-11-18 11:00:00',
            'ends_at' => '2026-11-18 13:00:00',
        ]);
        $poster = BrowseWall::remotePoster($live, $now);
        $this->assertTrue($poster['live']);
        $this->assertSame('today', $poster['band']);
    }

    /**
     * The choice is cached, but every request reads the listings again through listable(), so an
     * admin's Hide takes one off at once. Mutation: render the cached rows without re-reading.
     */
    public function test_a_hidden_listing_leaves_the_cached_wall_at_once(): void
    {
        config(['marketing.wall_cache_seconds' => 600]);
        $listing = $this->makeListing($this->makeInstance());

        $this->assertCount(1, $this->remoteTiles($this->get('/browse')->viewData('wall')['tiles']));

        $listing->blocked_at = now();
        $listing->save();

        $this->assertCount(0, $this->remoteTiles($this->get('/browse')->viewData('wall')['tiles']));
    }

    /** A copy cached in the shape before this one is read without an error. */
    public function test_a_cached_choice_without_the_network_keys_is_read_safely(): void
    {
        config(['marketing.wall_cache_seconds' => 600]);
        Cache::put(MarketingController::browseCacheKey(), ['ids' => [], 'more' => []], 600);

        $this->get('/browse')->assertOk();
    }

    /** The old address of their section, which welcome mail already sent names, goes to the view. */
    public function test_the_old_section_address_is_taken_to_their_view(): void
    {
        $this->get('/browse')
            ->assertOk()
            ->assertSee("location.hash === '#network'", false)
            ->assertSee('location.replace('.json_encode(marketing_url('/browse?show=network'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP).')', false);
    }

    /** Search shows each listing's blurb beside it, from the tile itself. */
    public function test_search_shows_each_listings_own_blurb(): void
    {
        $instance = $this->makeInstance();
        $this->makeListing($instance, ['name' => 'Harbour Jazz', 'short_description' => 'Saxophones by the water']);
        $this->makeListing($instance, ['name' => 'Harbour Poetry', 'short_description' => 'Verses at dusk'], 50);

        $cards = $this->get('/search?q=Harbour')->assertOk()->viewData('networkCards');

        $this->assertSame(
            ['Harbour Jazz' => 'Saxophones by the water', 'Harbour Poetry' => 'Verses at dusk'],
            collect($cards)->pluck('blurb', 'name')->sortKeys()->all()
        );
    }

    // ------------------------------------------------------------------- light copies

    /**
     * federation:maintain queues the copies of stored pictures that have none, a bounded number
     * per run, and only for listings that could be shown.
     */
    public function test_maintain_queues_light_copies_for_listings_that_can_be_shown(): void
    {
        Queue::fake();

        $approved = $this->makeInstance();
        $pending = $this->makeInstance(['instance_id' => (string) Str::uuid(), 'status' => FederatedInstance::STATUS_PENDING]);
        $wanted = $this->makeListing($approved, ['image_path' => 'federated_a.jpg']);
        $this->makeListing($pending, ['image_path' => 'federated_b.jpg']);
        $done = $this->makeListing($approved, ['image_path' => 'federated_c.jpg']);
        $done->image_variants = ['w480' => 'federated_c_w480.webp'];
        $done->save();
        $blocked = $this->makeListing($approved, ['image_path' => 'federated_d.jpg']);
        $blocked->blocked_at = now();
        $blocked->save();

        $this->assertSame(1, app(FederationMaintenance::class)->queueImageVariants());

        Queue::assertPushed(GenerateFederatedImageVariants::class, 1);
        Queue::assertPushed(fn (GenerateFederatedImageVariants $job) => $job->listingId === $wanted->id && $job->imagePath === 'federated_a.jpg');
    }

    /** The job makes the copies and records them with the picture's size, which keeps its shape. */
    public function test_the_job_records_the_copies_and_the_size(): void
    {
        Storage::fake();
        Queue::fake();

        $image = imagecreatetruecolor(1200, 800);
        imagefill($image, 0, 0, imagecolorallocate($image, 30, 90, 200));
        ob_start();
        imagepng($image);
        Storage::put(ImageUtils::storagePathFor('federated_wide.png'), ob_get_clean());

        $listing = $this->makeListing($this->makeInstance(), ['image_path' => 'federated_wide.png']);

        (new GenerateFederatedImageVariants($listing->id, 'federated_wide.png'))->handle();

        $listing->refresh();
        $this->assertSame('federated_wide_w480.webp', $listing->image_variants['w480'] ?? null);
        $this->assertSame([1200, 800], $listing->imageSourceDimensions());
        Storage::assertExists(ImageUtils::storagePathFor('federated_wide_w480.webp'));

        // The poster keeps the picture's shape and offers the copies.
        $poster = BrowseWall::remotePoster($listing, now());
        $this->assertEqualsWithDelta(1.5, $poster['ratio'], 0.01);
        $this->assertTrue($poster['exact']);
        $this->assertStringContainsString('federated_wide_w480.webp 480w', (string) $poster['srcset']);
    }

    /** A picture removed takes its copies with it, or they outlive it in storage. */
    public function test_deleting_a_stored_picture_deletes_its_copies(): void
    {
        Storage::fake();
        Storage::put(ImageUtils::storagePathFor('federated_gone.jpg'), 'bytes');
        Storage::put(ImageUtils::storagePathFor('federated_gone_w480.webp'), 'bytes');
        Storage::put(ImageUtils::storagePathFor('federated_gone_w960.webp'), 'bytes');

        FederatedEvent::deleteStoredImage('federated_gone.jpg');

        Storage::assertMissing(ImageUtils::storagePathFor('federated_gone.jpg'));
        Storage::assertMissing(ImageUtils::storagePathFor('federated_gone_w480.webp'));
        Storage::assertMissing(ImageUtils::storagePathFor('federated_gone_w960.webp'));
    }
}
