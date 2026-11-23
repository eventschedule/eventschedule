<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\VendorMap;
use App\Models\VendorMapOccupant;
use App\Models\VendorMapSpot;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The vendor link of a vendor map (VendorSpotController): who may ask for which stall, the
 * vendor's home on the same link, and the "Not coming" link from the approval mail. The account
 * step is VendorSpotAccountTest.
 */
class VendorSpotClaimTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $market;

    private VendorMap $map;

    private Event $event;

    /** @var array<string, VendorMapSpot> by label */
    private array $spots = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => false]);
        Storage::fake(config('filesystems.default'));

        $this->market = $this->createRole($this->createOwner(), 'venue', ['name' => 'Riverside Farmers Market', 'language_code' => 'he', 'timezone' => 'Asia/Jerusalem', 'country_code' => 'IL']);
        // Every Friday, starting next Friday.
        $this->event = $this->createRecurringEvent($this->market, [
            'name' => 'Friday market',
            'days_of_week' => '0000010',
            'starts_at' => Carbon::now('UTC')->next(Carbon::FRIDAY)->setTime(6, 0)->format('Y-m-d H:i:s'),
            'creator_role_id' => $this->market->id,
        ]);

        $this->map = VendorMap::create(['role_id' => $this->market->id, 'kind' => 'market', 'event_id' => $this->event->id, 'width' => 1000, 'height' => 700]);
        $this->map->ensureClaimToken();

        foreach (['12', '13', '14', '15'] as $i => $label) {
            $this->spots[$label] = VendorMapSpot::create(['vendor_map_id' => $this->map->id, 'label' => $label, 'x' => 100 + $i * 90, 'y' => 100, 'w' => 70, 'h' => 50]);
        }

        // 14 has a name only (read from a banner): "Is this you?".
        $this->approved($this->spots['14'], ['placeholder_name' => "Avi's Vegetables", 'categories' => ['produce'], 'phone' => '+972500000001']);
    }

    private function approved(VendorMapSpot $spot, array $attrs = []): VendorMapOccupant
    {
        $row = new VendorMapOccupant(['vendor_map_id' => $this->map->id, 'spot_id' => $spot->id, 'event_date' => '*', 'kind' => 'present'] + $attrs);
        $row->status = 'approved';
        $row->decided_at = now();
        $row->save();

        return $row;
    }

    private function url(string $route = 'vendor_map.claim', ?string $token = null): string
    {
        return route($route, ['subdomain' => $this->market->subdomain, 'token' => $token ?? $this->map->fresh()->claim_token]);
    }

    private function claimBody(array $over = []): array
    {
        return $over + [
            'action' => 'claim',
            'spot' => $this->spots['12']->id,
            'name' => "Dana's Bread",
            'categories' => ['bread'],
            'sells' => 'Sourdough',
            'phone' => '050-000-0002',
            'every' => true,
        ];
    }

    private function vendorUser(int $pages = 1): array
    {
        $user = $this->createOwner();
        $out = [];
        for ($i = 0; $i < $pages; $i++) {
            $out[] = $this->createRole($user, 'talent', ['name' => 'Vendor page '.$i]);
        }

        return [$user, $out];
    }

    private function props(TestResponse $response): array
    {
        preg_match('#<script type="application/json" id="vendor-claim-props">(.*?)</script>#s', $response->getContent(), $m);
        $this->assertNotEmpty($m, 'the page carries its props');

        return json_decode($m[1], true);
    }

    public function test_an_unknown_or_rotated_token_is_a_friendly_page_that_writes_nothing(): void
    {
        $old = $this->map->claim_token;

        $this->get($this->url('vendor_map.claim', 'nope'.str_repeat('x', 28)))
            ->assertOk()
            ->assertSee(e(__('messages.vm_claim_closed_title')), false)
            ->assertSee('noindex', false);

        $this->map->rotateClaimToken();

        $this->get($this->url('vendor_map.claim', $old))->assertOk()->assertSee(e(__('messages.vm_claim_closed_title')), false);
        $this->postJson($this->url('vendor_map.claim.submit', $old), $this->claimBody())->assertStatus(410);
    }

    public function test_a_closed_link_says_so(): void
    {
        $this->map->forceFill(['claims_open' => false])->save();

        $this->get($this->url())->assertOk()->assertSee(e(__('messages.vm_claim_closed_title')), false);

        [$user] = $this->vendorUser();
        $this->actingAs($user)->postJson($this->url('vendor_map.claim.submit'), $this->claimBody())->assertStatus(410);
        $this->assertSame(0, VendorMapOccupant::where('status', 'pending')->count());
    }

    public function test_the_page_lists_the_stalls_and_never_a_phone_or_a_waiting_name(): void
    {
        [$other] = $this->vendorUser();
        $page = $other->talents()->first();
        $wait = new VendorMapOccupant(['vendor_map_id' => $this->map->id, 'spot_id' => $this->spots['13']->id, 'vendor_role_id' => $page->id, 'placeholder_name' => 'Secret Waiting Name', 'phone' => '+972509999999', 'user_id' => $other->id]);
        $wait->status = 'pending';
        $wait->save();

        $before = VendorMapOccupant::count();
        $response = $this->get($this->url())->assertOk()->assertSee('noindex', false);
        $this->assertSame($before, VendorMapOccupant::count(), 'opening the link writes nothing');

        $html = $response->getContent();
        $this->assertStringNotContainsString('+972500000001', $html);
        $this->assertStringNotContainsString('+972509999999', $html);
        $this->assertStringNotContainsString('Secret Waiting Name', $html);

        $props = $this->props($response);
        $byLabel = collect($props['spots'])->keyBy('label');
        $this->assertSame("Avi's Vegetables", $byLabel['14']['name']);
        $this->assertFalse($byLabel['14']['page']);
        $this->assertTrue($byLabel['13']['waiting']);
        $this->assertNull($byLabel['13']['name']);
        $this->assertNull($byLabel['12']['name']);
        $this->assertSame('he', app()->getLocale());
    }

    public function test_a_signed_in_vendor_with_two_pages_asks_for_a_stall_as_one_of_them(): void
    {
        [$user, $pages] = $this->vendorUser(2);

        $this->actingAs($user)
            ->postJson($this->url('vendor_map.claim.submit'), $this->claimBody(['page' => UrlUtils::encodeId($pages[1]->id)]))
            ->assertOk()
            ->assertJsonPath('waiting.spotId', $this->spots['12']->id);

        $row = VendorMapOccupant::where('status', 'pending')->sole();
        $this->assertSame($pages[1]->id, $row->vendor_role_id);
        $this->assertSame($user->id, $row->user_id);
        $this->assertSame('*', $row->event_date);
        $this->assertSame(['bread'], $row->categories);
        $this->assertSame('+972500000002', $row->phone);
        $this->assertSame(2, Role::where('type', 'talent')->count(), 'no new page was made');
    }

    public function test_a_page_the_vendor_only_follows_cannot_be_used(): void
    {
        [$user] = $this->vendorUser();
        $stranger = $this->createRole($this->createOwner(), 'talent', ['name' => 'Someone else']);
        $this->followRole($user, $stranger);

        $this->actingAs($user)
            ->postJson($this->url('vendor_map.claim.submit'), $this->claimBody(['page' => UrlUtils::encodeId($stranger->id)]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('page');

        $this->assertSame(0, VendorMapOccupant::where('status', 'pending')->count());
    }

    public function test_is_this_you_on_a_name_only_stall_waits_for_the_organizer(): void
    {
        [$user, $pages] = $this->vendorUser();

        $this->actingAs($user)
            ->postJson($this->url('vendor_map.claim.submit'), $this->claimBody(['spot' => $this->spots['14']->id, 'name' => "Avi's Vegetables", 'categories' => ['produce'], 'page' => UrlUtils::encodeId($pages[0]->id)]))
            ->assertOk();

        $this->assertSame(1, VendorMapOccupant::where('spot_id', $this->spots['14']->id)->where('status', 'pending')->count());
        // The name on the stall stays until the organizer says yes.
        $this->assertSame(1, VendorMapOccupant::where('spot_id', $this->spots['14']->id)->where('status', 'approved')->whereNull('vendor_role_id')->count());
    }

    public function test_a_stall_somebody_else_asked_for_is_refused(): void
    {
        [$first, $firstPages] = $this->vendorUser();
        $this->actingAs($first)->postJson($this->url('vendor_map.claim.submit'), $this->claimBody(['page' => UrlUtils::encodeId($firstPages[0]->id)]))->assertOk();

        [$second, $secondPages] = $this->vendorUser();
        $this->actingAs($second)
            ->postJson($this->url('vendor_map.claim.submit'), $this->claimBody(['page' => UrlUtils::encodeId($secondPages[0]->id)]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('spot');

        $this->assertSame(1, VendorMapOccupant::where('status', 'pending')->count());
    }

    public function test_a_stall_held_by_a_vendor_with_a_page_is_never_asked_for_again(): void
    {
        [$holder, $holderPages] = $this->vendorUser();
        $this->approved($this->spots['15'], ['vendor_role_id' => $holderPages[0]->id]);

        [$user, $pages] = $this->vendorUser();
        $this->actingAs($user)
            ->postJson($this->url('vendor_map.claim.submit'), $this->claimBody(['spot' => $this->spots['15']->id, 'page' => UrlUtils::encodeId($pages[0]->id)]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('spot');

        $props = $this->props($this->get($this->url()));
        $this->assertTrue(collect($props['spots'])->firstWhere('label', '15')['page']);
    }

    public function test_a_spot_of_another_map_or_a_retired_one_is_refused(): void
    {
        [$user, $pages] = $this->vendorUser();
        $otherMarket = $this->createRole($this->createOwner(), 'venue');
        $otherMap = VendorMap::create(['role_id' => $otherMarket->id, 'kind' => 'market']);
        $foreign = VendorMapSpot::create(['vendor_map_id' => $otherMap->id, 'label' => '1', 'x' => 1, 'y' => 1, 'w' => 10, 'h' => 10]);

        $this->actingAs($user)
            ->postJson($this->url('vendor_map.claim.submit'), $this->claimBody(['spot' => $foreign->id, 'page' => UrlUtils::encodeId($pages[0]->id)]))
            ->assertStatus(422)->assertJsonValidationErrors('spot');

        $this->spots['12']->forceFill(['retired_at' => now()])->save();
        $this->actingAs($user)
            ->postJson($this->url('vendor_map.claim.submit'), $this->claimBody(['page' => UrlUtils::encodeId($pages[0]->id)]))
            ->assertStatus(422)->assertJsonValidationErrors('spot');

        $this->assertSame(0, VendorMapOccupant::where('status', 'pending')->count());
    }

    public function test_chosen_dates_must_be_market_days(): void
    {
        [$user, $pages] = $this->vendorUser();
        $friday = $this->event->nextOccurrenceFrom();
        $thursday = Carbon::parse($friday)->subDay()->format('Y-m-d');

        $this->actingAs($user)
            ->postJson($this->url('vendor_map.claim.submit'), $this->claimBody(['every' => false, 'dates' => [$thursday], 'page' => UrlUtils::encodeId($pages[0]->id)]))
            ->assertStatus(422)->assertJsonValidationErrors('dates');

        $this->actingAs($user)
            ->postJson($this->url('vendor_map.claim.submit'), $this->claimBody(['every' => false, 'dates' => [$friday, $thursday], 'page' => UrlUtils::encodeId($pages[0]->id)]))
            ->assertOk();

        $this->assertSame([$friday], VendorMapOccupant::where('status', 'pending')->pluck('event_date')->all());
    }

    public function test_a_photo_is_kept_private_and_travels_with_the_request(): void
    {
        [$user, $pages] = $this->vendorUser();

        $ref = $this->actingAs($user)
            ->post($this->url('vendor_map.claim.photo'), ['photo' => UploadedFile::fake()->image('stall.jpg', 2400, 1800)], ['Accept' => 'application/json'])
            ->assertOk()
            ->json('ref');

        $this->postJson($this->url('vendor_map.claim.submit'), $this->claimBody(['photo' => $ref, 'page' => UrlUtils::encodeId($pages[0]->id)]))->assertOk();

        $row = VendorMapOccupant::where('status', 'pending')->sole();
        $this->assertStringStartsWith('vendor-maps/', $row->photo_path);
        $size = getimagesizefromstring(Storage::disk(config('filesystems.default'))->get($row->photo_path));
        $this->assertSame(1600, $size[0], 'shrunk to 1600px on the server too');

        $this->post($this->url('vendor_map.claim.photo'), ['photo' => UploadedFile::fake()->create('IMG_1.HEIC', 200, 'image/heic')], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors('photo');
    }

    public function test_the_vendor_home_marks_not_coming_and_takes_it_back(): void
    {
        [$user, $pages] = $this->vendorUser();
        $season = $this->approved($this->spots['12'], ['vendor_role_id' => $pages[0]->id]);
        $friday = $this->event->nextOccurrenceFrom();

        $props = $this->props($this->actingAs($user)->get($this->url())->assertOk());
        $this->assertSame($this->spots['12']->id, $props['home']['spotId']);
        $this->assertFalse($props['home']['dates'][0]['absent']);

        $this->postJson($this->url('vendor_map.claim.home'), ['action' => 'absent', 'date' => $friday])
            ->assertOk()
            ->assertJsonPath('home.dates.0.absent', true);

        $absent = VendorMapOccupant::where('kind', 'absent')->sole();
        $this->assertSame($friday, $absent->event_date);
        $this->assertSame('vendor', $absent->note);
        $this->assertSame($user->id, $absent->user_id);

        $this->postJson($this->url('vendor_map.claim.home'), ['action' => 'present', 'date' => $friday])
            ->assertOk()
            ->assertJsonPath('home.dates.0.absent', false);
        $this->assertSame(0, VendorMapOccupant::where('kind', 'absent')->count());

        // A day the market is not on is refused.
        $this->postJson($this->url('vendor_map.claim.home'), ['action' => 'absent', 'date' => Carbon::parse($friday)->subDay()->format('Y-m-d')])
            ->assertStatus(422);

        $this->postJson($this->url('vendor_map.claim.home'), ['action' => 'update', 'sells' => 'Rye and spelt', 'categories' => ['bread', 'nope']])->assertOk();
        $this->assertSame(['bread'], $season->fresh()->categories);
        $this->assertSame('Rye and spelt', $season->fresh()->sells);

        $this->postJson($this->url('vendor_map.claim.home'), ['action' => 'stop'])->assertOk()->assertJsonPath('stopped', true);
        $this->assertNull(VendorMapOccupant::find($season->id));
    }

    public function test_the_home_needs_a_stall_of_ones_own(): void
    {
        [$user] = $this->vendorUser();

        $this->actingAs($user)->postJson($this->url('vendor_map.claim.home'), ['action' => 'stop'])->assertStatus(410);
        $this->assertSame(1, VendorMapOccupant::count());
    }

    public function test_the_not_coming_link_checks_its_hash_and_its_get_writes_nothing(): void
    {
        [$user, $pages] = $this->vendorUser();
        $season = $this->approved($this->spots['12'], ['vendor_role_id' => $pages[0]->id]);
        $id = UrlUtils::encodeId($season->id);
        $friday = $this->event->nextOccurrenceFrom();

        $stale = route('vendor_map.away', ['subdomain' => $this->market->subdomain, 'occupant' => $id, 'hash' => str_repeat('0', 16)]);
        $this->get($stale)->assertOk()->assertSee(e(__('messages.vm_away_stale_title')), false);
        $this->post(route('vendor_map.away.submit', ['subdomain' => $this->market->subdomain, 'occupant' => $id, 'hash' => str_repeat('0', 16)]), ['dates' => [$friday]])
            ->assertOk()->assertSee(e(__('messages.vm_away_stale_title')), false);
        $this->assertSame(0, VendorMapOccupant::where('kind', 'absent')->count());

        $params = ['subdomain' => $this->market->subdomain, 'occupant' => $id, 'hash' => $season->stateHash()];
        $this->get(route('vendor_map.away', $params))->assertOk()->assertSee($friday, false)->assertSee('noindex', false);
        $this->assertSame(0, VendorMapOccupant::where('kind', 'absent')->count(), 'the GET only shows');

        // Another schedule's address cannot reach this row.
        $elsewhere = $this->createRole($this->createOwner(), 'venue');
        VendorMap::create(['role_id' => $elsewhere->id, 'kind' => 'market', 'event_id' => $this->event->id]);
        $this->get(route('vendor_map.away', ['subdomain' => $elsewhere->subdomain] + $params))->assertSee(e(__('messages.vm_away_stale_title')), false);

        $this->post(route('vendor_map.away.submit', $params), ['dates' => [$friday, '2001-01-01']])->assertRedirect(route('vendor_map.away', $params));
        $absent = VendorMapOccupant::where('kind', 'absent')->sole();
        $this->assertSame($friday, $absent->event_date);
        $this->assertSame('vendor', $absent->note);
        $this->assertSame($user->id, $absent->user_id, "the vendor page's owner");

        $this->post(route('vendor_map.away.submit', $params), ['come' => $friday])->assertRedirect();
        $this->assertSame(0, VendorMapOccupant::where('kind', 'absent')->count());

        // The honeypot.
        $this->post(route('vendor_map.away.submit', $params), ['dates' => [$friday], 'website' => 'http://spam.test'])->assertRedirect();
        $this->assertSame(0, VendorMapOccupant::where('kind', 'absent')->count());

        // Once the stall changes hands the old link stops working.
        $season->forceFill(['spot_id' => $this->spots['13']->id])->save();
        $this->get(route('vendor_map.away', $params))->assertSee(e(__('messages.vm_away_stale_title')), false);
    }

    public function test_nothing_on_the_link_writes_on_a_get(): void
    {
        [$user, $pages] = $this->vendorUser();
        $this->approved($this->spots['12'], ['vendor_role_id' => $pages[0]->id]);
        $before = VendorMapOccupant::count();
        $query = '?action=absent&date='.$this->event->nextOccurrenceFrom().'&spot='.$this->spots['13']->id.'&name=x&categories[]=bread';

        // The link itself answers a GET by showing; the other two addresses answer no GET of
        // theirs at all (whatever the catch-all routes make of them).
        foreach (['vendor_map.claim.submit', 'vendor_map.claim.photo', 'vendor_map.claim.home'] as $name) {
            $this->actingAs($user)->get($this->url($name).$query);
        }

        $this->assertSame($before, VendorMapOccupant::count());
        $this->assertSame(0, VendorMapOccupant::where('kind', 'absent')->count());
    }
}
