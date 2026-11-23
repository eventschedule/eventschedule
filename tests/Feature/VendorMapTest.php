<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Models\VendorMap;
use App\Models\VendorMapDraft;
use App\Models\VendorMapOccupant;
use App\Models\VendorMapSpot;
use App\Services\VendorMaps\VendorMapAi;
use App\Services\VendorMaps\VendorMapClaims;
use App\Services\VendorMaps\VendorMapOccupancy;
use App\Services\VendorMaps\VendorMapPresenter;
use App\Services\VendorMaps\VendorMapStructure;
use App\Utils\GeminiUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The vendor map's core: the designer's saves, who is on which spot on a date, and the
 * organizer's answers. All names are made up.
 */
class VendorMapTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $market;

    private Event $event;

    private VendorMap $map;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-23 09:00:00', 'America/New_York'));

        $this->owner = $this->createOwner();
        $this->market = $this->createRole($this->owner, 'venue', ['name' => 'Riverside Farmers Market']);
        // Every Friday, 8 to 2, from this week on.
        $this->event = $this->createEvent($this->market, [
            'name' => 'Friday market',
            'creator_role_id' => $this->market->id,
            'starts_at' => Carbon::parse('2026-11-27 08:00', 'America/New_York')->utc()->format('Y-m-d H:i:s'),
            'duration' => 6,
            'days_of_week' => '0000010',
            'recurring_frequency' => 'weekly',
        ]);
        $this->map = VendorMap::create(['role_id' => $this->market->id, 'kind' => 'market', 'event_id' => $this->event->id]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        GeminiUtils::fakeResponses(null);
        parent::tearDown();
    }

    // ---- the designer ----

    public function test_a_save_keeps_spot_ids_and_bumps_the_revision(): void
    {
        $first = VendorMapStructure::save($this->map, $this->payload(['1', '2', '3']));
        $ids = VendorMapSpot::where('vendor_map_id', $this->map->id)->orderBy('id')->pluck('id')->all();
        $this->assertCount(3, $ids);
        $this->assertSame(1, $first['revision']);

        $structure = VendorMapStructure::toArray($this->map->fresh());
        $structure['spots'][0]['label'] = '1A';
        VendorMapStructure::save($this->map->fresh(), $structure);

        $this->assertSame($ids, VendorMapSpot::where('vendor_map_id', $this->map->id)->orderBy('id')->pluck('id')->all(), 'spot ids survive a save');
        $this->assertSame('1A', VendorMapSpot::find($ids[0])->label);
        $this->assertSame(2, $this->map->fresh()->revision);
    }

    public function test_a_save_from_an_old_revision_is_refused(): void
    {
        VendorMapStructure::save($this->map, $this->payload(['1']));

        $this->expectException(ValidationException::class);
        VendorMapStructure::save($this->map->fresh(), $this->payload(['1', '2'], revision: 0));
    }

    public function test_a_spot_with_a_season_vendor_cannot_be_removed_and_one_with_history_is_retired(): void
    {
        VendorMapStructure::save($this->map, $this->payload(['1', '2']));
        [$one, $two] = $this->spots();
        $vendor = $this->createRole($this->createOwner(), 'talent', ['name' => "Avi's Vegetables"]);
        $this->approvedSeason($one, $vendor);

        try {
            VendorMapStructure::save($this->map->fresh(), $this->withoutSpot($one));
            $this->fail('removing a season vendor\'s spot must be refused');
        } catch (ValidationException) {
            $this->assertNotNull(VendorMapSpot::find($one->id)?->id);
        }

        // A one-day vendor once stood on spot 2: removing it keeps the row, retired.
        $this->dated($two, '2026-11-27', 'Guest Baker');
        VendorMapOccupant::where('spot_id', $two->id)->update(['placeholder_name' => null, 'vendor_role_id' => $vendor->id, 'event_date' => '2026-11-20']);
        VendorMapStructure::save($this->map->fresh(), $this->withoutSpot($two));

        $this->assertNotNull(VendorMapSpot::find($two->id)->retired_at);
        $this->assertSame(1, $this->map->fresh()->spots()->count());
    }

    public function test_names_typed_in_the_designer_become_names_on_spots_and_clear(): void
    {
        $payload = $this->payload(['1', '2']);
        $payload['spots'][0]['name'] = 'Hilltop Honey';
        $payload['spots'][0]['category'] = 'honey';
        VendorMapStructure::save($this->map, $payload);

        [$one] = $this->spots();
        $row = VendorMapOccupant::where('spot_id', $one->id)->firstOrFail();
        $this->assertSame('Hilltop Honey', $row->placeholder_name);
        $this->assertSame(['honey'], $row->categories);
        $this->assertSame('approved', $row->status);

        $structure = VendorMapStructure::toArray($this->map->fresh());
        $structure['spots'][0]['name'] = '';
        VendorMapStructure::save($this->map->fresh(), $structure);
        $this->assertSame(0, VendorMapOccupant::where('spot_id', $one->id)->count());
    }

    // ---- who is where ----

    public function test_a_season_vendor_shows_every_friday_and_not_on_other_days(): void
    {
        VendorMapStructure::save($this->map, $this->payload(['1']));
        [$one] = $this->spots();
        $vendor = $this->createRole($this->createOwner(), 'talent', ['name' => "Avi's Vegetables"]);
        $this->approvedSeason($one, $vendor);

        $friday = VendorMapOccupancy::on($this->map->fresh(), '2026-11-27');
        $this->assertSame('present', $friday[$one->id]['state']);

        $thursday = VendorMapOccupancy::on($this->map->fresh(), '2026-11-26');
        $this->assertSame('free', $thursday[$one->id]['state'], 'the market is not on a Thursday');
    }

    public function test_not_coming_greys_the_spot_for_that_day_only_and_can_be_undone(): void
    {
        VendorMapStructure::save($this->map, $this->payload(['1']));
        [$one] = $this->spots();
        $vendor = $this->createRole($this->createOwner(), 'talent', ['name' => "Avi's Vegetables"]);
        $season = $this->approvedSeason($one, $vendor);

        VendorMapClaims::markAbsent($this->map, $season, ['2026-11-27']);

        $this->assertSame('absent', VendorMapOccupancy::on($this->map->fresh(), '2026-11-27')[$one->id]['state']);
        $this->assertSame('present', VendorMapOccupancy::on($this->map->fresh(), '2026-12-04')[$one->id]['state']);
        $this->assertSame('2026-12-04', VendorMapOccupancy::nextPresentDate($this->map->fresh(), $season, '2026-11-27'));

        VendorMapClaims::clearAbsent($this->map, $season, '2026-11-27');
        $this->assertSame('present', VendorMapOccupancy::on($this->map->fresh(), '2026-11-27')[$one->id]['state']);
    }

    public function test_a_one_day_move_shows_the_vendor_exactly_once_that_day(): void
    {
        VendorMapStructure::save($this->map, $this->payload(['1', '2']));
        [$one, $two] = $this->spots();
        $avi = $this->createRole($this->createOwner(), 'talent', ['name' => "Avi's Vegetables"]);
        $noa = $this->createRole($this->createOwner(), 'talent', ['name' => 'Noa Bakes']);
        $aviSeason = $this->approvedSeason($one, $avi);
        $this->approvedSeason($two, $noa);

        // Onto a spot that has its own season vendor: the two swap, for that Friday only.
        VendorMapClaims::move($this->map, $aviSeason, $two, '2026-11-27');

        $day = VendorMapOccupancy::on($this->map->fresh(), '2026-11-27');
        $presentVendors = collect($day)->where('state', 'present')->map(fn ($d) => $d['occupant']->vendor_role_id)->values()->all();
        sort($presentVendors);
        $expected = [$avi->id, $noa->id];
        sort($expected);
        $this->assertSame($expected, $presentVendors, 'each vendor is present once');
        $this->assertSame($avi->id, $day[$two->id]['occupant']->vendor_role_id);

        $next = VendorMapOccupancy::on($this->map->fresh(), '2026-12-04');
        $this->assertSame($avi->id, $next[$one->id]['occupant']->vendor_role_id, 'the next Friday is as before');
    }

    // ---- the organizer's answers ----

    public function test_approving_an_is_this_you_claim_replaces_the_name_on_the_spot(): void
    {
        $payload = $this->payload(['1']);
        $payload['spots'][0]['name'] = "Avi's Vegetables";
        $payload['spots'][0]['category'] = 'produce';
        VendorMapStructure::save($this->map, $payload);
        [$one] = $this->spots();

        [$user, $vendor] = $this->vendor("Avi's Vegetables");
        $claim = VendorMapClaims::request($this->map, $one, $vendor, $user, ['name' => "Avi's Vegetables", 'categories' => [], 'dates' => null]);
        VendorMapClaims::approve($claim);

        $rows = VendorMapOccupant::where('spot_id', $one->id)->where('status', 'approved')->get();
        $this->assertCount(1, $rows);
        $this->assertSame($vendor->id, $rows[0]->vendor_role_id);
        $this->assertSame(['produce'], $rows[0]->categories, 'the AI\'s category is kept');

        // Undo puts the name back on the stall; it was only stood aside.
        $this->assertTrue(VendorMapClaims::undo($claim->fresh()));
        $back = VendorMapOccupant::where('spot_id', $one->id)->where('status', 'approved')->sole();
        $this->assertNull($back->vendor_role_id);
        $this->assertSame("Avi's Vegetables", $back->placeholder_name);
        $this->assertSame('pending', $claim->fresh()->status);
    }

    public function test_two_vendors_cannot_both_be_approved_on_one_spot(): void
    {
        VendorMapStructure::save($this->map, $this->payload(['1']));
        [$one] = $this->spots();
        [$u1, $v1] = $this->vendor('Hilltop Honey');
        [$u2, $v2] = $this->vendor('Valley Flowers');

        $a = VendorMapClaims::request($this->map, $one, $v1, $u1, ['name' => 'Hilltop Honey', 'categories' => []]);
        $b = VendorMapClaims::request($this->map, $one, $v2, $u2, ['name' => 'Valley Flowers', 'categories' => []]);

        $this->assertCount(0, VendorMapPresenter::conflictFreeClaims($this->map->fresh()), 'two wanting one spot is a conflict');

        VendorMapClaims::approve($a);
        $this->expectException(ValidationException::class);
        VendorMapClaims::approve($b);
    }

    public function test_undo_works_only_before_the_vendor_is_told(): void
    {
        VendorMapStructure::save($this->map, $this->payload(['1']));
        [$one] = $this->spots();
        [$u, $v] = $this->vendor('Hilltop Honey');
        $claim = VendorMapClaims::request($this->map, $one, $v, $u, ['name' => 'Hilltop Honey', 'categories' => []]);
        VendorMapClaims::approve($claim);

        $this->assertTrue(VendorMapClaims::undo($claim->fresh()));
        $this->assertSame('pending', $claim->fresh()->status);

        VendorMapClaims::approve($claim->fresh());
        $claim->fresh()->forceFill(['vendor_notified_at' => now()])->save();
        $this->assertFalse(VendorMapClaims::undo($claim->fresh()));
    }

    public function test_a_phone_is_kept_in_international_form(): void
    {
        $this->assertSame('+972521234567', VendorMapClaims::normalisePhone('052-123-4567', 'IL'));
        $this->assertSame('+15551234567', VendorMapClaims::normalisePhone('+1 555 123 4567', 'IL'));
        $this->assertNull(VendorMapClaims::normalisePhone('12', 'IL'));
    }

    // ---- what the public is given ----

    public function test_the_public_payload_carries_no_phone_no_user_and_no_waiting_claim(): void
    {
        VendorMapStructure::save($this->map, $this->payload(['1', '2']));
        [$one, $two] = $this->spots();
        [$u, $v] = $this->vendor('Hilltop Honey');
        $approved = VendorMapClaims::request($this->map, $one, $v, $u, ['name' => 'Hilltop Honey', 'categories' => ['honey'], 'phone' => '052-123-4567']);
        VendorMapClaims::approve($approved);
        [$u2, $v2] = $this->vendor('Secret Waiting Name');
        VendorMapClaims::request($this->map, $two, $v2, $u2, ['name' => 'Secret Waiting Name', 'categories' => [], 'phone' => '054-999-8888']);

        $json = json_encode(VendorMapPresenter::publicPayload($this->market, $this->map->fresh(), '2026-11-27'));

        $this->assertStringContainsString('Hilltop Honey', $json);
        $this->assertStringNotContainsString('Secret Waiting Name', $json);
        $this->assertStringNotContainsString('972521234567', $json);
        $this->assertStringNotContainsString('999', $json);
        $this->assertStringNotContainsString('"user_id"', $json);
    }

    // ---- the owner's endpoints ----

    public function test_the_market_path_makes_the_weekly_event_and_the_map(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Harbor Market', 'timezone' => 'Asia/Jerusalem']);

        $this->actingAs($owner)->postJson(route('vendor_map.setup', ['subdomain' => $venue->subdomain]), [
            'kind' => 'market', 'name' => 'Friday Harbor Market', 'days' => [5], 'starts' => '08:00', 'ends' => '14:00',
        ])->assertOk();

        $map = VendorMap::where('role_id', $venue->id)->firstOrFail();
        $event = $map->event;
        $this->assertSame('0000010', $event->days_of_week);
        $this->assertSame($venue->id, $event->creator_role_id);
        $this->assertSame('08:00', Carbon::parse($event->starts_at, 'UTC')->setTimezone('Asia/Jerusalem')->format('H:i'));
        $this->assertTrue($venue->ownEvents()->where('events.id', $event->id)->exists());
    }

    public function test_a_map_cannot_serve_an_event_the_schedule_only_lists(): void
    {
        $other = $this->createRole($this->createOwner(), 'venue', ['name' => 'Elsewhere Hall']);
        $theirs = $this->createEvent($other, ['name' => 'Their night', 'creator_role_id' => $other->id]);
        $theirs->roles()->attach($this->market->id, ['is_accepted' => true]);

        $this->actingAs($this->owner)->postJson(route('vendor_map.settings', ['subdomain' => $this->market->subdomain]), [
            'event_id' => UrlUtils::encodeId($theirs->id),
        ])->assertStatus(422);
    }

    public function test_the_tab_renders_for_an_editor_and_not_for_a_stranger(): void
    {
        VendorMapStructure::save($this->map, $this->payload(['1', '2']));

        $this->actingAs($this->owner)->get(route('role.view_admin', ['subdomain' => $this->market->subdomain, 'tab' => 'vendor-map']))
            ->assertOk()->assertSee('id="vendor-map-admin"', false);

        $this->actingAs($this->createOwner())->getJson(route('vendor_map.day', ['subdomain' => $this->market->subdomain]))->assertForbidden();
        $this->actingAs($this->createOwner())->get(route('vendor_map.design', ['subdomain' => $this->market->subdomain]))->assertForbidden();
    }

    public function test_a_vendor_who_joined_by_the_link_and_is_not_coming_is_absent_on_the_map(): void
    {
        VendorMapStructure::save($this->map, $this->payload(['1']));
        $spot = $this->map->spots()->first();
        [, $page] = $this->vendor("Avi's Vegetables");
        $row = $this->approvedSeason($spot, $page);
        // The link keeps the name the vendor typed on the season row; the absent row does not.
        $row->forceFill(['placeholder_name' => "Avi's Vegetables"])->save();

        VendorMapClaims::markAbsent($this->map, $row, ['2026-11-27']);

        $this->assertSame('absent', VendorMapOccupancy::on($this->map, '2026-11-27')[$spot->id]['state']);
        $this->assertSame('present', VendorMapOccupancy::on($this->map, '2026-12-04')[$spot->id]['state']);
    }

    public function test_two_vendors_swap_spots_for_the_rest_of_the_season_and_keep_their_days_off(): void
    {
        VendorMapStructure::save($this->map, $this->payload(['1', '2']));
        [$a, $b] = $this->map->spots()->orderBy('label')->get()->all();
        [, $pageA] = $this->vendor('First Vendor');
        [, $pageB] = $this->vendor('Second Vendor');
        $rowA = $this->approvedSeason($a, $pageA);
        $rowB = $this->approvedSeason($b, $pageB);
        VendorMapClaims::markAbsent($this->map, $rowA, ['2026-12-04']);

        VendorMapClaims::move($this->map, $rowA, $b, null);

        $this->assertSame($b->id, $rowA->fresh()->spot_id);
        $this->assertSame($a->id, $rowB->fresh()->spot_id);
        $this->assertSame('approved', $rowB->fresh()->status);
        $day = VendorMapOccupancy::on($this->map, '2026-12-04');
        $this->assertSame('absent', $day[$b->id]['state'], 'the day off moved with the vendor');
        $this->assertSame('present', $day[$a->id]['state']);
    }

    public function test_calling_off_one_day_of_an_expo_leaves_the_other_days(): void
    {
        $this->event->forceFill(['days_of_week' => null, 'recurring_frequency' => null, 'duration' => 54])->save();
        $map = $this->map->fresh();
        $this->assertTrue(VendorMapOccupancy::isOpenOn($map, '2026-11-28'));

        $this->actingAs($this->owner)->postJson(route('vendor_map.cancel_day', ['subdomain' => $this->market->subdomain]), [
            'date' => '2026-11-28', 'reason' => 'weather',
        ])->assertOk();

        $this->assertFalse((bool) $this->event->fresh()->is_cancelled, 'the expo goes on');
        $map = $this->map->fresh();
        $this->assertFalse(VendorMapOccupancy::isOpenOn($map, '2026-11-28'));
        $this->assertTrue(VendorMapOccupancy::isOpenOn($map, '2026-11-27'));
        $this->assertTrue(VendorMapOccupancy::isOpenOn($map, '2026-11-29'));
    }

    public function test_cancelling_a_day_takes_it_off_the_season_and_keeps_the_reason(): void
    {
        VendorMapStructure::save($this->map, $this->payload(['1']));

        $this->actingAs($this->owner)->postJson(route('vendor_map.cancel_day', ['subdomain' => $this->market->subdomain]), [
            'date' => '2026-11-27', 'reason' => 'weather',
        ])->assertOk()->assertJsonPath('open', false)->assertJsonPath('cancelled', 'weather');

        $this->assertFalse(VendorMapOccupancy::isOpenOn($this->map->fresh(), '2026-11-27'));
        $this->assertTrue(VendorMapOccupancy::isOpenOn($this->map->fresh(), '2026-12-04'));
    }

    public function test_photos_go_in_privately_and_the_ai_draft_is_normalized(): void
    {
        Storage::fake(config('filesystems.default'));
        config(['services.google.gemini_key' => 'test-key', 'services.ai.text_provider' => 'gemini']);

        GeminiUtils::fakeResponses(function ($prompt, $image, $purpose, $options) {
            $this->assertCount(2, $options['images'], 'both photos are sent in one request');

            return [[
                'spots' => [
                    ['label' => '1', 'x' => 10, 'y' => 10, 'w' => 60, 'h' => 40, 'name_seen' => 'Hilltop Honey', 'category' => 'honey', 'confidence' => 0.9],
                    ['label' => '1', 'x' => 5000, 'y' => -20, 'w' => 60, 'h' => 40, 'name_seen' => '', 'category' => 'not-a-category'],
                ],
                'areas' => [['name' => 'Hangar', 'x' => 0, 'y' => 0, 'w' => 300, 'h' => 200]],
                'landmarks' => [['kind' => 'entrance', 'x' => 0, 'y' => 650]],
            ]];
        });

        $jpeg = UploadedFile::fake()->image('a.jpg', 800, 600);
        $png = UploadedFile::fake()->image('b.png', 800, 600);

        $upload = $this->actingAs($this->owner)->post(route('vendor_map.photos', ['subdomain' => $this->market->subdomain]), [
            'photos' => [$jpeg, $png],
        ], ['Accept' => 'application/json'])->assertOk()->json();

        $draft = VendorMapDraft::findOrFail(UrlUtils::decodeId($upload['draft']));
        foreach ($draft->images as $path) {
            $this->assertStringStartsWith('vendor-maps/', $path);
            Storage::disk(config('filesystems.default'))->assertExists($path);
        }

        $result = $this->actingAs($this->owner)->postJson(route('vendor_map.draft.parse', ['subdomain' => $this->market->subdomain, 'draft' => $upload['draft']]))
            ->assertOk()->json('result');

        $this->assertCount(2, $result['spots']);
        $this->assertNotSame($result['spots'][0]['label'], $result['spots'][1]['label'], 'labels are made unique');
        $this->assertLessThanOrEqual(1000, $result['spots'][1]['x'] + $result['spots'][1]['w']);
        $this->assertNull($result['spots'][1]['category']);
        $this->assertSame(1, $result['names_read']);

        // A stranger cannot see the photos.
        $this->actingAs($this->createOwner())->get(route('vendor_map.private_image', [
            'subdomain' => $this->market->subdomain, 'kind' => 'draft', 'id' => $upload['draft'], 'index' => 0,
        ]))->assertForbidden();
    }

    public function test_a_large_place_sends_its_photos_in_batches_to_one_draft(): void
    {
        Storage::fake(config('filesystems.default'));
        config(['services.google.gemini_key' => 'test-key', 'services.ai.text_provider' => 'gemini']);
        $route = route('vendor_map.photos', ['subdomain' => $this->market->subdomain]);
        $batch = fn () => array_map(fn ($i) => UploadedFile::fake()->image("p{$i}.jpg", 400, 300), range(1, 4));

        $first = $this->actingAs($this->owner)->post($route, ['photos' => $batch()], ['Accept' => 'application/json'])->assertOk()->json('draft');
        for ($k = 0; $k < 4; $k++) {
            $this->assertSame($first, $this->actingAs($this->owner)->post($route, ['photos' => $batch(), 'draft' => $first], ['Accept' => 'application/json'])->assertOk()->json('draft'));
        }
        $this->assertCount(VendorMapAi::MAX_AREA_PHOTOS, VendorMapDraft::findOrFail(UrlUtils::decodeId($first))->images);

        // One more is over the limit, and what it sent is not kept.
        $this->actingAs($this->owner)->post($route, ['photos' => [UploadedFile::fake()->image('x.jpg', 400, 300)], 'draft' => $first], ['Accept' => 'application/json'])
            ->assertUnprocessable();
        $this->assertCount(VendorMapAi::MAX_AREA_PHOTOS, VendorMapDraft::findOrFail(UrlUtils::decodeId($first))->images);

        // Another schedule's draft cannot be added to.
        $other = $this->createOwner();
        $this->actingAs($other)->post($route, ['photos' => $batch(), 'draft' => $first], ['Accept' => 'application/json'])->assertForbidden();
    }

    public function test_a_market_photographed_area_by_area_is_read_area_by_area_and_put_side_by_side(): void
    {
        Storage::fake(config('filesystems.default'));
        config(['services.google.gemini_key' => 'test-key', 'services.ai.text_provider' => 'gemini']);
        $calls = [];
        GeminiUtils::fakeResponses(function ($prompt, $image, $purpose, $options) use (&$calls) {
            $calls[] = ['images' => count($options['images']), 'prompt' => $prompt];

            // Each area numbered from 1 by the AI, as it would be.
            return [[
                'spots' => [
                    ['label' => '1', 'x' => 100, 'y' => 100, 'w' => 60, 'h' => 40],
                    ['label' => '2', 'x' => 170, 'y' => 100, 'w' => 60, 'h' => 40],
                ],
                'landmarks' => [['kind' => 'entrance', 'x' => 100, 'y' => 200]],
            ]];
        });
        $route = route('vendor_map.photos', ['subdomain' => $this->market->subdomain]);
        $photo = fn ($n) => UploadedFile::fake()->image("p{$n}.jpg", 400, 300);

        $draft = $this->actingAs($this->owner)->post($route, ['photos' => [$photo(1), $photo(2)], 'area' => 0, 'area_name' => 'The hall'], ['Accept' => 'application/json'])
            ->assertOk()->json('draft');
        $this->actingAs($this->owner)->post($route, ['photos' => [$photo(3)], 'area' => 1, 'area_name' => 'The orchard', 'draft' => $draft], ['Accept' => 'application/json'])
            ->assertOk();

        $parse = route('vendor_map.draft.parse', ['subdomain' => $this->market->subdomain, 'draft' => $draft]);
        $this->actingAs($this->owner)->postJson($parse)->assertOk()
            ->assertJson(['pending' => true, 'done' => 1, 'total' => 2, 'next' => 'The orchard']);
        $result = $this->actingAs($this->owner)->postJson($parse)->assertOk()->json('result');

        $this->assertSame([2, 1], array_column($calls, 'images'), 'each area is read with its own photos only');
        $this->assertStringContainsString('The hall', $calls[0]['prompt']);
        $this->assertSame(['The hall', 'The orchard'], array_column($result['areas'], 'name'));
        $this->assertSame(['1', '2', '3', '4'], array_column($result['spots'], 'label'), 'numbers continue across areas');
        $this->assertSame(['The hall', 'The hall', 'The orchard', 'The orchard'], array_column($result['spots'], 'area'));
        $this->assertSame(2, $result['blocks']);
        $this->assertGreaterThan(max(array_map(fn ($s) => $s['x'] + $s['w'], array_slice($result['spots'], 0, 2))), min(array_column(array_slice($result['spots'], 2), 'x')), 'the second area is a block beside the first');
        $this->assertCount(2, $result['landmarks']);
        $this->assertSame('ready', VendorMapDraft::findOrFail(UrlUtils::decodeId($draft))->status);
    }

    public function test_the_ai_is_not_offered_without_gemini(): void
    {
        config(['services.google.gemini_key' => '', 'services.ai.text_provider' => 'gemini']);
        $this->assertFalse(VendorMapAi::available($this->map));

        config(['services.google.gemini_key' => 'test-key', 'services.ai.text_provider' => 'openai']);
        $this->assertFalse(VendorMapAi::available($this->map));
    }

    // ---- helpers ----

    private function payload(array $labels, ?int $revision = null): array
    {
        $spots = [];
        foreach ($labels as $i => $label) {
            $spots[] = ['label' => $label, 'x' => 20 + $i * 70, 'y' => 40, 'w' => 60, 'h' => 40];
        }

        return ['revision' => $revision ?? $this->map->fresh()->revision, 'areas' => [], 'spots' => $spots, 'landmarks' => []];
    }

    private function withoutSpot(VendorMapSpot $spot): array
    {
        $s = VendorMapStructure::toArray($this->map->fresh());
        $s['spots'] = array_values(array_filter($s['spots'], fn ($x) => $x['id'] !== $spot->id));
        foreach ($s['spots'] as &$x) {
            unset($x['name']);
        }

        return $s;
    }

    /** @return VendorMapSpot[] */
    private function spots(): array
    {
        return VendorMapSpot::where('vendor_map_id', $this->map->id)->whereNull('retired_at')->orderBy('id')->get()->all();
    }

    private function approvedSeason(VendorMapSpot $spot, Role $vendor): VendorMapOccupant
    {
        $row = new VendorMapOccupant(['vendor_map_id' => $this->map->id, 'spot_id' => $spot->id, 'event_date' => '*', 'kind' => 'present', 'vendor_role_id' => $vendor->id]);
        $row->status = 'approved';
        $row->decided_at = now();
        $row->save();

        return $row;
    }

    private function dated(VendorMapSpot $spot, string $date, string $name): VendorMapOccupant
    {
        $row = new VendorMapOccupant(['vendor_map_id' => $this->map->id, 'spot_id' => $spot->id, 'event_date' => $date, 'kind' => 'present', 'placeholder_name' => $name]);
        $row->status = 'approved';
        $row->save();

        return $row;
    }

    /** @return array{0: User, 1: Role} */
    private function vendor(string $name): array
    {
        $user = $this->createOwner();

        return [$user, $this->createRole($user, 'talent', ['name' => $name])];
    }
}
