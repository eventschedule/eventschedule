<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Models\UsageDaily;
use App\Models\User;
use App\Repos\EventRepo;
use App\Services\PersonalDataExportService;
use App\Services\UsageTrackingService;
use App\Utils\UrlUtils;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What feeds are kept in, before anything reads one: the two tables and what the rest of the app
 * has to know about them. An address that is a credential stays one at rest and in every export;
 * a feed follows its schedule through a merge; an item outlives its event without forgetting what
 * it was; and a caller that creates many events can ask how many the day still has room for.
 */
class FeedDataTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const ADDRESS = 'https://calendar.example.org/ical/someone%40example.org/private-0123456789abcdef/basic.ics';

    private function feed(Role $role, array $attrs = []): EventFeed
    {
        $url = $attrs['url'] ?? self::ADDRESS;

        return EventFeed::create($attrs + [
            'role_id' => $role->id,
            'name' => 'Town calendar',
            'url' => $url,
            'url_hash' => EventFeed::hashOf($url),
            'host' => (string) parse_url($url, PHP_URL_HOST),
            'kind' => EventFeed::KIND_CALENDAR,
            'source_timezone' => 'Europe/Vienna',
        ]);
    }

    private function item(EventFeed $feed, string $externalId, array $attrs = []): EventFeedItem
    {
        return EventFeedItem::create($attrs + [
            'event_feed_id' => $feed->id,
            'external_key' => EventFeedItem::keyFor($externalId),
            'external_id' => $externalId,
        ]);
    }

    public function test_the_address_is_a_secret_at_rest_and_in_every_form_of_the_model(): void
    {
        $role = $this->createRole($this->createOwner());
        $feed = $this->feed($role, ['etag' => 'W/"abc"']);
        $item = $this->item($feed, 'post-1', ['detail_url' => self::ADDRESS.'?post=1', 'image_source' => self::ADDRESS.'/poster.jpg']);

        $stored = DB::table('event_feeds')->where('id', $feed->id)->first();
        $this->assertStringNotContainsString('private-0123456789abcdef', $stored->url);
        $this->assertSame(self::ADDRESS, $feed->fresh()->url);
        $this->assertSame('calendar.example.org', $stored->host);

        $storedItem = DB::table('event_feed_items')->where('id', $item->id)->first();
        $this->assertStringNotContainsString('private-0123456789abcdef', $storedItem->detail_url.$storedItem->image_source);
        $this->assertSame(self::ADDRESS.'?post=1', $item->fresh()->detail_url);

        // A log line, a JSON reply or a dump of the model is not where the address goes.
        $this->assertStringNotContainsString('private-0123456789abcdef', json_encode([$feed->fresh(), $item->fresh()]));
        $this->assertTrue(EventFeed::tablesReady());
    }

    public function test_one_address_is_one_feed_per_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->feed($role);

        // Another schedule may read the same address.
        $this->feed($this->createRole($owner));
        $this->feed($role, ['url' => 'https://other.example.org/feed.ics']);

        $this->expectException(QueryException::class);
        $this->feed($role);
    }

    public function test_an_item_is_found_by_its_sources_id_exactly(): void
    {
        $feed = $this->feed($this->createRole($this->createOwner()));
        $long = str_repeat('https://example.org/a-very-long-id/', 40);

        $this->item($feed, 'aB3');
        $this->item($feed, 'Ab3');
        $this->item($feed, $long);

        $this->assertSame(3, $feed->items()->count());
        $this->assertSame($long, $feed->items()->where('external_key', EventFeedItem::keyFor($long))->value('external_id'));
        $this->assertSame(64, strlen(EventFeedItem::keyFor($long)));

        $this->expectException(QueryException::class);
        $this->item($feed, 'aB3');
    }

    /**
     * An item whose event is gone keeps its state. That is how the next read tells an event the
     * owner deleted on purpose (dismissed) from one that vanished some other way (still
     * `imported`, with no event), which it brings back.
     */
    public function test_what_goes_with_what(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $adder = User::factory()->create();
        $group = $this->createGroup($role);
        $feed = $this->feed($role, ['added_by' => $adder->id, 'group_id' => $group->id]);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $item = $this->item($feed, 'post-1', ['event_id' => $event->id, 'state' => EventFeedItem::STATE_IMPORTED]);

        $event->delete();
        $this->assertNull($item->fresh()->event_id);
        $this->assertSame(EventFeedItem::STATE_IMPORTED, $item->fresh()->state);

        DB::table('users')->where('id', $adder->id)->delete();
        DB::table('groups')->where('id', $group->id)->delete();
        $this->assertNull($feed->fresh()->added_by);
        $this->assertNull($feed->fresh()->group_id);

        DB::table('roles')->where('id', $role->id)->delete();
        $this->assertSame(0, EventFeed::count());
        $this->assertSame(0, EventFeedItem::count());
    }

    /** A merged-away schedule's feeds go on reading into the schedule it became. */
    public function test_a_merge_takes_the_feeds_along_and_drops_one_the_target_already_reads(): void
    {
        $owner = $this->createOwner();
        $survivor = $this->createRole($owner, 'venue', ['name' => 'Ozen Bar', 'city' => 'Tel Aviv', 'country_code' => 'il']);

        $duplicate = new Role;
        $duplicate->subdomain = 'stub'.strtolower(Str::random(10));
        $duplicate->type = 'venue';
        $duplicate->name = 'Ozen Bar';
        $duplicate->city = 'Tel Aviv';
        $duplicate->country_code = 'il';
        $duplicate->save();
        $this->followRole($owner, $duplicate);

        $kept = $this->feed($survivor);
        $same = $this->feed($duplicate, ['group_id' => $this->createGroup($duplicate)->id]);
        $this->item($same, 'post-1');
        $moved = $this->feed($duplicate, ['url' => 'https://other.example.org/feed.ics', 'group_id' => $this->createGroup($duplicate)->id]);
        $movedItem = $this->item($moved, 'post-2');

        $this->actingAs($owner)->post(route('following.merge_venues_group'), [
            'target_id' => UrlUtils::encodeId($survivor->id),
            'source_ids' => [UrlUtils::encodeId($duplicate->id)],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame($survivor->id, $moved->fresh()->role_id);
        $this->assertNull($moved->fresh()->group_id, 'a sub-schedule of the merged-away schedule is not the target\'s');
        $this->assertNotNull($movedItem->fresh());
        $this->assertNull($same->fresh());
        $this->assertSame(0, EventFeedItem::where('event_feed_id', $same->id)->count());
        $this->assertSame($survivor->id, $kept->fresh()->role_id);
        $this->assertSame(2, $survivor->feeds()->count());
    }

    public function test_a_persons_export_names_the_feeds_they_added_and_not_their_addresses(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->feed($role, ['added_by' => $owner->id]);
        $this->feed($this->createRole($this->createOwner()), ['name' => 'Somebody else\'s']);

        $export = app(PersonalDataExportService::class)->build($owner);

        $this->assertSame(['Town calendar'], array_column($export['feeds_added'], 'name'));
        $this->assertSame('calendar.example.org', $export['feeds_added'][0]['host']);
        $this->assertStringNotContainsString('private-0123456789abcdef', json_encode($export));
        $this->assertArrayNotHasKey('url', $export['feeds_added'][0]);
    }

    public function test_an_event_can_be_stamped_as_a_feeds_with_the_batch_of_its_first_read(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $request = Request::create('/', 'POST', [
            'name' => 'From a feed',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'members' => [UrlUtils::encodeId($role->id) => ['name' => $role->name]],
        ]);
        $request->setUserResolver(fn () => $owner);
        $this->actingAs($owner);

        $event = app(EventRepo::class)->saveEvent($role, $request, null, true, null, importSource: Event::IMPORT_FEED, importBatch: 'abcdef012345');

        $this->assertSame('feed', $event->fresh()->import_source);
        $this->assertSame('abcdef012345', $event->fresh()->import_batch);
    }

    /**
     * What is left of the day, for a caller that makes many events at once and has to stop while
     * there is still room for the owner to add one by hand.
     */
    public function test_a_schedule_says_how_many_more_events_the_day_has_room_for(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $other = $this->createRole($owner);
        $used = function (Role $role, int $times) {
            for ($i = 0; $i < $times; $i++) {
                UsageDaily::record(UsageTrackingService::EVENT_CREATE, $role->id);
            }
        };
        $limits = fn (int $schedule, int $user) => config([
            'usage.event_create_daily_limit_trial' => $schedule,
            'usage.event_create_daily_limit_pro' => $schedule,
            'usage.event_create_daily_limit_enterprise' => $schedule,
            'usage.event_create_user_daily_limit_trial' => $user,
            'usage.event_create_user_daily_limit_pro' => $user,
            'usage.event_create_user_daily_limit_enterprise' => $user,
        ]);

        // Selfhost has no cap of its own.
        config(['app.hosted' => false]);
        $limits(10, 30);
        $this->assertNull($role->eventCreateAllowance($owner));
        $this->assertTrue($role->canCreateEvent($owner));

        config(['app.hosted' => true]);
        $this->assertSame(10, $role->eventCreateAllowance($owner));

        $used($role, 4);
        $this->assertSame(6, $role->eventCreateAllowance($owner));
        // Nobody acting: the schedule's own cap is all there is.
        $this->assertSame(6, $role->eventCreateAllowance());

        // The account's cap across its schedules is the smaller one now.
        $limits(10, 7);
        $used($other, 2);
        $this->assertSame(1, $role->eventCreateAllowance($owner));
        $this->assertSame(6, $role->eventCreateAllowance());

        $used($other, 5);
        $this->assertSame(0, $role->eventCreateAllowance($owner), 'never below zero');
        $this->assertFalse($role->canCreateEvent($owner));
        $this->assertTrue($role->canCreateEvent());
    }
}
