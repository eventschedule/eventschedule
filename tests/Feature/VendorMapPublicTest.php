<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Models\VendorMap;
use App\Models\VendorMapArea;
use App\Models\VendorMapLandmark;
use App\Models\VendorMapOccupant;
use App\Models\VendorMapSpot;
use App\Services\VendorMaps\VendorMapImages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The visitors' map (/{schedule}/map) and the band a market's guest page and its event page
 * carry: what a shopper sees, and that nothing private ever reaches them (a phone, an account,
 * a claim that is waiting), a name only is never linked, a vendor who is not coming is said to be
 * away with their next date, and a called-off market day says so and shows who comes next time.
 */
class VendorMapPublicTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $market;

    private Event $season;

    private VendorMap $map;

    /** @var array<string, VendorMapSpot> */
    private array $spots = [];

    private Role $avi;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        // Friday 19 June 2026, mid-morning: market day.
        $this->travelTo('2026-06-19 10:00:00');

        $this->owner = $this->createOwner();
        $this->market = $this->createRole($this->owner, 'venue', ['name' => 'Riverside Farmers Market', 'timezone' => 'UTC']);
        $this->season = $this->createRecurringEvent($this->market, [
            'name' => 'Friday market',
            'starts_at' => '2026-06-05 08:00:00',
            'duration' => 6,
            'days_of_week' => '0000010',
            'creator_role_id' => $this->market->id,
        ]);

        $this->map = VendorMap::create([
            'role_id' => $this->market->id,
            'enabled' => true,
            'kind' => 'market',
            'event_id' => $this->season->id,
            'width' => 1000,
            'height' => 700,
            'here_x' => 500,
            'here_y' => 650,
        ]);
        $hangar = VendorMapArea::create(['vendor_map_id' => $this->map->id, 'name' => 'Hangar', 'color' => '#16a34a']);
        $orchard = VendorMapArea::create(['vendor_map_id' => $this->map->id, 'name' => 'Orchard', 'color' => '#d97706', 'sort_order' => 1]);
        VendorMapLandmark::create(['vendor_map_id' => $this->map->id, 'kind' => 'entrance', 'x' => 470, 'y' => 640, 'w' => 60, 'h' => 40]);

        foreach (['1', '2', '3', '4', '5', '6'] as $i => $label) {
            $this->spots[$label] = VendorMapSpot::create([
                'vendor_map_id' => $this->map->id,
                'label' => $label,
                'area_id' => $i < 3 ? $hangar->id : $orchard->id,
                'x' => 100 + $i * 60, 'y' => 100, 'w' => 50, 'h' => 40,
            ]);
        }

        // Spot 2: a vendor with a page of their own, who gave a phone and is signed in.
        $vendorOwner = $this->createOwner();
        $this->avi = $this->createRole($vendorOwner, 'talent', ['name' => "Avi's Vegetables"]);
        VendorMapOccupant::create([
            'vendor_map_id' => $this->map->id, 'spot_id' => $this->spots['2']->id, 'event_date' => '*',
            'kind' => 'present', 'status' => 'approved', 'vendor_role_id' => $this->avi->id,
            'sells' => 'Eggs and greens', 'categories' => ['produce', 'eggs_dairy'],
            'phone' => '+972501234567', 'user_id' => $vendorOwner->id,
        ]);

        // Spot 1: a name only (read off a banner).
        VendorMapOccupant::create([
            'vendor_map_id' => $this->map->id, 'spot_id' => $this->spots['1']->id, 'event_date' => '*',
            'kind' => 'present', 'status' => 'approved', 'placeholder_name' => 'Corner Bakery', 'categories' => ['bread'],
        ]);

        // Spot 4: a season vendor who is not coming today.
        $honeyOwner = $this->createOwner();
        $honey = $this->createRole($honeyOwner, 'talent', ['name' => 'Hill Honey']);
        foreach (['*' => 'present', '2026-06-19' => 'absent'] as $date => $kind) {
            VendorMapOccupant::create([
                'vendor_map_id' => $this->map->id, 'spot_id' => $this->spots['4']->id, 'event_date' => $date,
                'kind' => $kind, 'status' => 'approved', 'vendor_role_id' => $honey->id, 'categories' => ['honey'],
            ]);
        }

        // Spot 5: somebody waiting for the organizer, with a phone.
        VendorMapOccupant::create([
            'vendor_map_id' => $this->map->id, 'spot_id' => $this->spots['5']->id, 'event_date' => '*',
            'kind' => 'present', 'status' => 'pending', 'placeholder_name' => 'Waiting Zebra Stand',
            'phone' => '+972509999999', 'categories' => ['other'],
        ]);
    }

    private function mapUrl(string $query = ''): string
    {
        return '/'.$this->market->subdomain.'/map'.$query;
    }

    /** The JSON the page hands its script. */
    private function boot(string $html): array
    {
        $this->assertMatchesRegularExpression('~<script type="application/json" id="vendor-map-public-data"[^>]*>(.*?)</script>~s', $html);
        preg_match('~<script type="application/json" id="vendor-map-public-data"[^>]*>(.*?)</script>~s', $html, $m);

        return json_decode($m[1], true);
    }

    private function spotRow(array $boot, string $label): array
    {
        return collect($boot['map']['spots'])->firstWhere('label', $label);
    }

    public function test_the_page_shows_the_vendors_and_the_areas(): void
    {
        $html = $this->get($this->mapUrl())->assertOk()->getContent();

        $this->assertStringContainsString(e("Avi's Vegetables"), $html);
        $this->assertStringContainsString('Corner Bakery', $html);
        $this->assertStringContainsString('Hangar', $html);
        $this->assertStringContainsString('Orchard', $html);
        // The map and the list are printed before any script, and the app starts on its own element.
        $this->assertStringContainsString('class="vms"', $html, 'the market is drawn as blocks of stalls before the app starts');
        $this->assertStringContainsString('id="vendor-map-public" data-vue-root', $html);
        $this->assertStringContainsString('rel="canonical" href="'.route('vendor_map.public', ['subdomain' => $this->market->subdomain]).'"', $html);

        $boot = $this->boot($html);
        $this->assertSame('open', $boot['status']);
        $this->assertSame('2026-06-19', $boot['map']['date']);
        $this->assertSame('present', $this->spotRow($boot, '2')['state']);
        $this->assertSame('free', $this->spotRow($boot, '3')['state']);
        $this->assertCount(2, $boot['map']['areas']);
    }

    public function test_the_list_comes_before_the_map_in_the_document(): void
    {
        $html = $this->get($this->mapUrl())->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'class="vm-mapwrap'), strpos($html, 'class="vm-side"'));
    }

    public function test_nothing_private_reaches_the_page(): void
    {
        $html = $this->get($this->mapUrl())->assertOk()->getContent();

        $this->assertStringNotContainsString('972501234567', $html);
        $this->assertStringNotContainsString('972509999999', $html);
        $this->assertStringNotContainsString('Waiting Zebra Stand', $html);
        $this->assertStringNotContainsString('"user_id"', $html);
        $this->assertStringNotContainsString('"phone"', $html);
        // A spot somebody is only waiting for is an empty spot to a shopper.
        $this->assertSame('free', $this->spotRow($this->boot($html), '5')['state']);
    }

    public function test_a_name_only_has_no_link_and_no_picture(): void
    {
        $html = $this->get($this->mapUrl())->assertOk()->getContent();
        $boot = $this->boot($html);

        $this->assertNull($this->spotRow($boot, '1')['vendor']['url']);
        $this->assertDoesNotMatchRegularExpression('~<a [^>]*class="vm-row-link"><bdi>Corner Bakery~', $html);
        $this->assertMatchesRegularExpression('~<a href="[^"]+" class="vm-row-link"><bdi>'.preg_quote(e("Avi's Vegetables"), '~').'~', $html);
        $this->assertNotEmpty($this->spotRow($boot, '2')['vendor']['url']);
    }

    public function test_a_vendor_who_is_not_coming_is_greyed_with_their_next_date(): void
    {
        $html = $this->get($this->mapUrl())->assertOk()->getContent();
        $boot = $this->boot($html);
        $honey = $this->spotRow($boot, '4');

        $this->assertSame('absent', $honey['state']);
        $this->assertSame('26 Jun', $honey['vendor']['next']);
        // Greyed on the map, and greyed in the list.
        $this->assertStringContainsString('vms-c is-absent', $html);
        $this->assertStringContainsString('vm-row is-absent', $html);
    }

    public function test_a_called_off_day_says_no_market_and_shows_who_comes_next(): void
    {
        $this->season->recurring_exclude_dates = ['2026-06-19'];
        $this->season->save();
        $this->map->forceFill(['cancelled_days' => ['2026-06-19' => 'weather']])->save();

        $html = $this->get($this->mapUrl())->assertOk()->getContent();
        $boot = $this->boot($html);

        $this->assertSame('closed', $boot['status']);
        $this->assertNotNull($boot['closed']);
        $this->assertStringContainsString('vm-closed', $html);
        // The list is next Friday's, with Hill Honey back.
        $this->assertSame('2026-06-26', $boot['map']['date']);
        $this->assertSame('present', $this->spotRow($boot, '4')['state']);
        $this->assertSame('present', $this->spotRow($boot, '2')['state']);
    }

    public function test_a_date_with_no_market_points_at_the_next_one(): void
    {
        $boot = $this->boot($this->get($this->mapUrl('?date=2026-06-17'))->assertOk()->getContent());

        $this->assertSame('closed', $boot['status']);
        $this->assertSame('2026-06-19', $boot['map']['date']);
    }

    public function test_s_opens_with_that_stall_picked(): void
    {
        $html = $this->get($this->mapUrl('?s=2'))->assertOk()->getContent();
        $boot = $this->boot($html);

        $this->assertSame($this->spots['2']->id, $boot['selected']);
        $this->assertStringContainsString('vm-card vm-card-slot', $html);

        $this->assertNull($this->boot($this->get($this->mapUrl('?s=99'))->getContent())['selected']);
    }

    public function test_here_shows_the_marker_only_when_asked(): void
    {
        $this->assertFalse($this->boot($this->get($this->mapUrl())->getContent())['showHere']);

        $html = $this->get($this->mapUrl('?here=1'))->assertOk()->getContent();
        $this->assertTrue($this->boot($html)['showHere']);
        $this->assertStringContainsString('class="vms-here"', $html);
        $this->assertStringNotContainsString('class="vms-here"', $this->get($this->mapUrl())->getContent());
    }

    public function test_the_market_picture_is_shown_to_shoppers_only_when_its_owner_chose_to(): void
    {
        Storage::fake(config('filesystems.default'));
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        $this->map->forceFill(['background_path' => VendorMapImages::storePrivate($png, 'png')])->save();
        $url = '/'.$this->market->subdomain.'/map/picture';

        // A tracing photo is private until its owner says otherwise.
        $this->get($url)->assertNotFound();
        $this->assertNull($this->boot($this->get($this->mapUrl())->getContent())['schematic']['picture']);

        $this->map->forceFill(['background_public' => true])->save();
        // Served from the public store (its CDN), never through PHP: the route only points there.
        $location = $this->get($url)->assertRedirect()->headers->get('Location');
        $this->assertStringContainsString('vmpic_', $location);
        $this->assertStringContainsString('vmpic_', $this->boot($this->get($this->mapUrl())->getContent())['schematic']['picture']);
        $this->assertStringNotContainsString($this->map->background_path, $location, 'the private path is not in the public name');
    }

    public function test_a_switched_off_map_is_not_found(): void
    {
        $this->map->forceFill(['enabled' => false])->save();

        $this->get($this->mapUrl())->assertNotFound();
    }

    public function test_an_event_slugged_map_keeps_its_short_link_where_there_is_no_market_map(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'A Concert Hall']);
        $this->createEvent($venue, ['name' => 'Map', 'slug' => 'map', 'creator_role_id' => $venue->id,
            'starts_at' => now()->addDays(3)->format('Y-m-d').' 20:00:00']);

        $this->get('/'.$venue->subdomain.'/map')->assertOk()->assertSee('A Concert Hall');
    }

    public function test_a_schedule_without_a_map_is_not_found(): void
    {
        $other = $this->createRole($this->createOwner(), 'venue', ['name' => 'No Map Hall']);

        $this->get('/'.$other->subdomain.'/map')->assertNotFound();
    }

    public function test_a_deleted_schedule_is_not_found(): void
    {
        $this->market->forceFill(['is_deleted' => true])->save();

        $this->get($this->mapUrl())->assertNotFound();
    }

    public function test_the_band_shows_on_the_guest_page(): void
    {
        $html = $this->get('/'.$this->market->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString('gk-vm-band', $html);
        $this->assertStringContainsString('href="'.route('vendor_map.public', ['subdomain' => $this->market->subdomain]).'"', $html);
        $this->assertStringNotContainsString('972501234567', $html);
        $this->assertStringNotContainsString('Waiting Zebra Stand', $html);
    }

    public function test_the_band_and_the_vendors_show_on_the_maps_event_only(): void
    {
        $html = $this->get($this->guestEventUrl($this->market, $this->season))->assertOk()->getContent();
        $this->assertStringContainsString('gp-vendor-map', $html);
        $this->assertStringContainsString('gk-vm-grid', $html);
        $this->assertStringContainsString(e("Avi's Vegetables"), $html);
        // Hill Honey is not here today, so not in today's list of who is there.
        $this->assertStringNotContainsString('Hill Honey', $html);
        $this->assertStringNotContainsString('Waiting Zebra Stand', $html);

        $other = $this->createEvent($this->market, ['name' => 'Harvest talk', 'creator_role_id' => $this->market->id]);
        $html = $this->get($this->guestEventUrl($this->market, $other))->assertOk()->getContent();
        $this->assertStringNotContainsString('gp-vendor-map', $html);
    }

    public function test_no_band_for_a_map_with_no_spots_or_switched_off(): void
    {
        $this->map->forceFill(['enabled' => false])->save();
        $this->assertStringNotContainsString('gk-vm-band', $this->get('/'.$this->market->subdomain)->getContent());

        $empty = $this->createRole($this->createOwner(), 'venue', ['name' => 'Empty Lot Market']);
        VendorMap::create(['role_id' => $empty->id, 'enabled' => true, 'kind' => 'market']);
        $this->assertStringNotContainsString('gk-vm-band', $this->get('/'.$empty->subdomain)->getContent());
    }
}
