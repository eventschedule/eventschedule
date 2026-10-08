<?php

namespace Tests\Feature;

use App\Jobs\SendWebhook;
use App\Jobs\SyncEventToGoogleCalendar;
use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\EventPart;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\User;
use App\Models\Webhook;
use App\Services\Feeds\FeedEventWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What a feed writes. Making an event is a save like any other, as the schedule's owner.
 * Changing one is not a save at all: a save reads what a request does not carry as emptied, and
 * a feed knows eight things about an event. And once the owner changes one of those eight, it
 * is theirs.
 */
class FeedWriterTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const SOURCE = 'https://93.184.216.34';

    private User $owner;

    private Role $role;

    private EventFeed $feed;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Storage::fake(config('filesystems.default'));
        Http::preventStrayRequests();
        Http::fake([
            '93.184.216.34/poster.jpg' => Http::response($this->picture(200, 30, 30), 200),
            '93.184.216.34/new.jpg' => Http::response($this->picture(30, 200, 30), 200),
            '93.184.216.34/gone.jpg' => Http::response('', 404),
        ]);

        $this->owner = $this->createOwner();
        // The schedule is in New York and the feed's clock is Vienna's: the two must not be mixed.
        $this->role = $this->createRole($this->owner, 'talent', ['timezone' => 'America/New_York']);
        $this->feed = $this->feed();
    }

    private function picture(int $r, int $g, int $b): string
    {
        $image = imagecreatetruecolor(40, 30);
        imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));
        ob_start();
        imagejpeg($image);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    private function feed(array $attrs = []): EventFeed
    {
        $url = 'https://calendar.example.org/'.Str::random(8).'.ics';

        return EventFeed::create($attrs + [
            'role_id' => $this->role->id, 'name' => 'Town calendar', 'url' => $url, 'url_hash' => EventFeed::hashOf($url),
            'host' => 'calendar.example.org', 'kind' => EventFeed::KIND_CALENDAR, 'source_timezone' => 'Europe/Vienna',
            'publish_mode' => EventFeed::PUBLISH,
        ]);
    }

    private function item(string $id = 'post-1'): EventFeedItem
    {
        return EventFeedItem::create(['event_feed_id' => $this->feed->id, 'external_key' => EventFeedItem::keyFor($id), 'external_id' => $id]);
    }

    private function row(array $overrides = []): array
    {
        return $overrides + [
            'event_name' => 'Frühlingskonzert',
            'event_details' => "Kommt vorbei!\r\n\r\nEintritt frei.  ",
            'event_date_time' => '2027-05-09 19:30',
            'event_duration' => 2.5,
            'venue_name' => '',
            'event_address' => '',
            'registration_url' => 'https://tickets.example.org/konzert',
            'category_name' => '',
            'image_url' => null,
        ];
    }

    private function writer(): FeedEventWriter
    {
        return app(FeedEventWriter::class);
    }

    /** @return array{0: EventFeedItem, 1: Event} */
    private function made(array $row = [], ?Role $venue = null): array
    {
        $item = $this->item();
        $wanted = $this->writer()->wanted($this->feed, $this->role, $this->row($row), $venue);
        $event = $this->writer()->create($this->feed, $this->role, $this->owner, $item, $wanted, null);

        return [$item->fresh(), $event->fresh()];
    }

    private function update(EventFeedItem $item, Event $event, array $row, ?Role $venue = null): array
    {
        return $this->writer()->update($this->feed, $this->role, $item, $event, $this->writer()->wanted($this->feed, $this->role, $this->row($row), $venue));
    }

    public function test_an_event_is_made_as_the_owner_on_the_feeds_clock_and_marked_as_the_feeds(): void
    {
        $stage = $this->createGroup($this->role);
        $this->feed->update(['group_id' => $stage->id, 'category_id' => 3]);
        $venue = $this->createRole($this->owner, 'venue', ['name' => 'Stadtsaal']);
        $item = $this->item();
        $wanted = $this->writer()->wanted($this->feed, $this->role, $this->row(), $venue);

        $event = $this->writer()->create($this->feed, $this->role, $this->owner, $item, $wanted, 'abcdef012345')->fresh();

        $this->assertSame('Frühlingskonzert', $event->name);
        // 19:30 in Vienna in May is 17:30 UTC, whatever the schedule's own zone is.
        $this->assertSame('2027-05-09 17:30:00', $event->starts_at);
        $this->assertSame('Europe/Vienna', $event->timezone);
        $this->assertSame(2.5, $event->duration);
        $this->assertSame("Kommt vorbei!\n\nEintritt frei.", $event->description);
        $this->assertSame('https://tickets.example.org/konzert', $event->registration_url);
        $this->assertSame(3, (int) $event->category_id);
        $this->assertSame($this->owner->id, $event->user_id);
        $this->assertSame($this->role->id, $event->creator_role_id);
        $this->assertSame([Event::IMPORT_FEED, 'abcdef012345'], [$event->import_source, $event->import_batch]);
        $this->assertFalse((bool) $event->is_draft);
        $this->assertFalse((bool) $event->tickets_enabled);
        $this->assertSame($venue->id, $event->venue->id);
        $this->assertSame($stage->id, (int) DB::table('event_role')->where('event_id', $event->id)->where('role_id', $this->role->id)->value('group_id'));

        $item->refresh();
        $this->assertSame([$event->id, EventFeedItem::STATE_IMPORTED], [$item->event_id, $item->state]);
        $this->assertSame($venue->id, $item->imported['venue_id']);
        // (A JSON column gives its keys back in its own order.)
        $this->assertEquals($this->writer()->held($event) + ['flyer' => null], $item->imported['row']);

        // Nothing of the owner's hangs on a fresh one, and nobody has signed up for it.
        $this->assertFalse($this->writer()->hasOwnersWork($event, $item));
        $this->assertFalse($this->writer()->hasPeople($event));
        // The save ran as the owner and left nobody signed in.
        $this->assertNull(Auth::user());
    }

    public function test_a_feed_that_holds_new_events_makes_drafts_and_whoever_was_signed_in_still_is(): void
    {
        $this->feed->update(['publish_mode' => EventFeed::DRAFT]);
        $someone = User::factory()->create();
        $this->actingAs($someone);

        [, $event] = $this->made();

        $this->assertTrue((bool) $event->is_draft);
        $this->assertSame($this->owner->id, $event->user_id);
        $this->assertSame($someone->id, Auth::id());
    }

    /**
     * The whole reason an update is not a save: everything here is something a save would have
     * emptied, because a feed's request would not have carried it.
     */
    public function test_an_update_changes_what_the_source_changed_and_touches_nothing_else(): void
    {
        [$item, $event] = $this->made();
        $event->forceFill(['tickets_enabled' => true, 'short_description' => 'Written by hand'])->save();
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 15]);
        $promo = PromoCode::create(['event_id' => $event->id, 'code' => 'EARLY', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);
        $part = EventPart::create(['event_id' => $event->id, 'name' => 'Doors']);
        $curator = $this->createCurator($this->owner);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $result = $this->update($item, $event->fresh(), ['event_name' => 'Frühlingskonzert (ausverkauft)', 'event_duration' => 3]);

        $this->assertSame(['name', 'duration'], $result['written']);
        $event->refresh();
        $this->assertSame(['Frühlingskonzert (ausverkauft)', 3.0], [$event->name, $event->duration]);
        $this->assertSame('2027-05-09 17:30:00', $event->starts_at);
        $this->assertSame('Written by hand', $event->short_description);
        $this->assertTrue((bool) $event->tickets_enabled);
        $this->assertFalse((bool) $ticket->fresh()->is_deleted);
        $this->assertNotNull($promo->fresh());
        $this->assertNotNull($part->fresh());
        $this->assertTrue(DB::table('event_role')->where('event_id', $event->id)->where('role_id', $curator->id)->exists());

        // And a read that finds nothing changed writes nothing: a rewrite would buy every
        // translation again.
        $stamp = $event->updated_at;
        $again = $this->update($item->fresh(), $event, ['event_name' => 'Frühlingskonzert (ausverkauft)', 'event_duration' => 3]);
        $this->assertSame(['written' => [], 'kept' => [], 'held' => [], 'raised' => false], $again);
        $this->assertEquals($stamp, $event->fresh()->updated_at);
    }

    public function test_a_field_the_owner_changed_is_theirs_and_the_rest_still_follow(): void
    {
        [$item, $event] = $this->made();
        // The owner moves the start and rewrites the text.
        $event->forceFill(['starts_at' => '2027-05-09 18:00:00', 'description' => 'Our own words.'])->save();

        $result = $this->update($item, $event->fresh(), [
            'event_date_time' => '2027-05-09 20:00',
            'event_details' => 'New text at the source.',
            'event_name' => 'Renamed at the source',
        ]);

        $this->assertSame(['name'], $result['written']);
        $this->assertEqualsCanonicalizing(['starts_at', 'description'], $result['kept']);
        $event->refresh();
        $this->assertSame(['Renamed at the source', '2027-05-09 18:00:00', 'Our own words.'], [$event->name, $event->starts_at, $event->description]);

        // What the source says now is kept aside, to be shown and to be taken up on request.
        $item->refresh();
        $this->assertSame(['starts_at' => '2027-05-09 18:00:00', 'description' => 'New text at the source.'], $item->pending['fields']);
        $this->assertSame(EventFeedItem::STATE_IMPORTED, $item->state);

        // It stays theirs on the next read, and the next after that.
        $this->assertSame([], $this->update($item, $event, ['event_date_time' => '2027-05-09 21:00', 'event_details' => 'New text at the source.', 'event_name' => 'Renamed at the source'])['written']);
        $this->assertSame('2027-05-09 18:00:00', $event->fresh()->starts_at);

        // A field put back to exactly what the feed wrote follows the source again.
        $event->forceFill(['description' => "Kommt vorbei!\n\nEintritt frei."])->save();
        $this->assertSame(['description'], $this->update($item->fresh(), $event->fresh(), ['event_date_time' => '2027-05-09 21:00', 'event_details' => 'New text at the source.', 'event_name' => 'Renamed at the source'])['written']);
        $this->assertSame('New text at the source.', $event->fresh()->description);
    }

    /**
     * What the form and the model do to a value on its way in is not an edit: line endings,
     * space at the ends, an empty string for nothing, 2 for 2.0.
     */
    public function test_an_event_saved_without_being_changed_does_not_read_as_edited(): void
    {
        [$item, $event] = $this->made(['event_duration' => 2]);
        $event->forceFill(['description' => "Kommt vorbei!\r\n\r\nEintritt frei.\n", 'duration' => '2.000', 'category_id' => null])->save();
        $event->touch();

        $result = $this->update($item, $event->fresh(), [
            'event_name' => 'A', 'event_details' => 'B', 'event_date_time' => '2027-05-10 19:30', 'event_duration' => 4, 'registration_url' => 'https://tickets.example.org/c',
        ]);

        $this->assertSame(['name', 'description', 'starts_at', 'duration', 'link'], $result['written']);
        $this->assertSame([], $result['kept']);
    }

    public function test_a_published_events_change_reaches_the_calendars_and_the_webhook_and_a_drafts_does_not(): void
    {
        $this->owner->forceFill(['google_token' => 'tok', 'google_refresh_token' => 'ref'])->save();
        $this->role->forceFill(['sync_direction' => 'both'])->save();
        Webhook::create(['user_id' => $this->owner->id, 'url' => 'https://example.test/hook', 'secret' => 'shh', 'event_types' => ['event.updated'], 'is_active' => true]);
        Bus::fake();
        [$item, $event] = $this->made();
        $sequence = (int) $event->ical_sequence;

        Bus::fake();
        $this->update($item, $event, ['event_date_time' => '2027-05-09 20:00']);

        Bus::assertDispatchedSync(SyncEventToGoogleCalendar::class);
        Bus::assertDispatched(SendWebhook::class, fn (SendWebhook $job) => (fn () => $this->eventType)->call($job) === 'event.updated');
        // A moved start is what a subscribed calendar has to be told about.
        $this->assertSame($sequence + 1, (int) $event->fresh()->ical_sequence);

        // A new name is not: the sequence stays.
        $this->update($item->fresh(), $event->fresh(), ['event_date_time' => '2027-05-09 20:00', 'event_name' => 'Renamed']);
        $this->assertSame($sequence + 1, (int) $event->fresh()->ical_sequence);

        $event->forceFill(['is_draft' => true])->save();
        Bus::fake();
        $this->update($item->fresh(), $event->fresh(), ['event_date_time' => '2027-05-09 21:00', 'event_name' => 'Renamed']);
        Bus::assertNotDispatchedSync(SyncEventToGoogleCalendar::class);
        Bus::assertNotDispatched(SendWebhook::class);
    }

    public function test_the_venue_moves_with_the_source_until_the_owner_moves_it(): void
    {
        $first = $this->createRole($this->owner, 'venue', ['name' => 'Stadtsaal']);
        $second = $this->createRole($this->owner, 'venue', ['name' => 'Kunsthaus']);
        $third = $this->createRole($this->owner, 'venue', ['name' => 'Rathaus']);
        [$item, $event] = $this->made([], $first);

        $this->assertSame(['venue_id'], $this->update($item, $event, [], $second)['written']);
        $this->assertSame([$second->id], DB::table('event_role')->join('roles', 'roles.id', '=', 'event_role.role_id')->where('event_id', $event->id)->where('roles.type', 'venue')->pluck('roles.id')->all());
        $this->assertSame($second->id, $item->fresh()->imported['venue_id']);

        // The owner puts it somewhere else by hand: the source's next move is not followed.
        DB::table('event_role')->where('event_id', $event->id)->where('role_id', $second->id)->update(['role_id' => $third->id]);
        $this->assertSame(['venue_id'], $this->update($item->fresh(), $event->fresh(), [], $first)['kept']);
        $this->assertSame($third->id, $this->writer()->held($event->fresh())['venue_id']);

        // And a source that drops the venue altogether takes it off an event still following it.
        [$other, $otherEvent] = [$this->item('post-2'), null];
        $otherEvent = $this->writer()->create($this->feed, $this->role, $this->owner, $other, $this->writer()->wanted($this->feed, $this->role, $this->row(['event_name' => 'Second']), $first), null);
        $this->assertSame(['venue_id'], $this->update($other->fresh(), $otherEvent->fresh(), ['event_name' => 'Second'], null)['written']);
        $this->assertNull($this->writer()->held($otherEvent->fresh())['venue_id']);
    }

    /**
     * People are coming on the 9th. A feed does not move that under them: the change waits for
     * the owner, and what is not about when or where is still written.
     */
    public function test_a_move_of_an_event_people_signed_up_for_waits_for_the_owner(): void
    {
        [$item, $event] = $this->made();
        $event->forceFill(['tickets_enabled' => true])->save();
        $this->createSale($event, $this->role, ['status' => 'paid'], $this->createTicket($event));
        $this->assertTrue($this->writer()->hasPeople($event));

        $moved = ['event_date_time' => '2027-05-16 19:30', 'event_name' => 'Verschoben: Frühlingskonzert'];
        $result = $this->update($item, $event->fresh(), $moved);

        $this->assertSame(['name'], $result['written']);
        $this->assertSame(['starts_at'], $result['held']);
        $this->assertSame('2027-05-09 17:30:00', $event->fresh()->starts_at);
        $item->refresh();
        $this->assertSame(EventFeedItem::STATE_DECIDE, $item->state);
        $this->assertSame(['kind' => 'moved', 'starts_at' => '2027-05-16 17:30:00'], $item->pending['decide']);

        // The owner says to leave it. The same difference is not raised again.
        $item->forceFill(['decided_hash' => FeedEventWriter::hashOf($item->pending['decide']), 'state' => EventFeedItem::STATE_IMPORTED])->save();
        $this->update($item->fresh(), $event->fresh(), $moved);
        $this->assertSame(EventFeedItem::STATE_IMPORTED, $item->fresh()->state);
        $this->assertNull($item->fresh()->pending);

        // A different one is.
        $this->update($item->fresh(), $event->fresh(), ['event_date_time' => '2027-05-23 19:30'] + $moved);
        $this->assertSame(EventFeedItem::STATE_DECIDE, $item->fresh()->state);

        // And when the source goes back to the date people have, there is nothing left to decide.
        $this->update($item->fresh(), $event->fresh(), ['event_name' => 'Verschoben: Frühlingskonzert']);
        $this->assertSame(EventFeedItem::STATE_IMPORTED, $item->fresh()->state);
        $this->assertNull($item->fresh()->pending);
    }

    /** Bought or reserved, asked to be told, or being shown it by a boost that is running. */
    public function test_people_have_signed_up_in_more_ways_than_buying(): void
    {
        [, $event] = $this->made();
        $this->assertFalse($this->writer()->hasPeople($event));

        $interest = \App\Models\EventInterest::create([
            'event_id' => $event->id, 'email' => 'fan@fans.test', 'token' => Str::random(32), 'confirm_token' => Str::random(32),
            'event_date' => '2027-05-09',
        ]);
        $this->assertTrue($this->writer()->hasPeople($event));
        $interest->delete();

        \App\Models\BoostCampaign::create([
            'event_id' => $event->id, 'role_id' => $this->role->id, 'user_id' => $this->owner->id,
            'name' => 'Boost', 'status' => 'active', 'billing_status' => 'charged', 'currency_code' => 'USD',
            'user_budget' => 50, 'total_charged' => 55,
        ]);
        $this->assertTrue($this->writer()->hasPeople($event));

        // One that has been refunded is over.
        \App\Models\BoostCampaign::query()->update(['status' => 'cancelled', 'billing_status' => 'refunded']);
        $this->assertFalse($this->writer()->hasPeople($event));
    }

    /**
     * The owner corrects the feed's clock (Edit feed). A start the feed moves afterwards is read
     * on the new clock, and the event says which clock that is.
     */
    public function test_a_start_moved_after_the_feeds_clock_was_corrected_is_on_the_new_clock(): void
    {
        [$item, $event] = $this->made();
        $this->assertSame('Europe/Vienna', $event->timezone);

        $this->feed->update(['source_timezone' => 'Europe/London']);
        $this->update($item, $event, ['event_date_time' => '2027-05-10 19:30']);

        $event->refresh();
        // 19:30 in London in May is 18:30 UTC.
        $this->assertSame('2027-05-10 18:30:00', $event->starts_at);
        $this->assertSame('Europe/London', $event->timezone);
    }

    public function test_the_flyer_is_fetched_when_the_sources_picture_changes_and_never_over_the_owners(): void
    {
        [$item, $event] = $this->made(['image_url' => self::SOURCE.'/poster.jpg']);
        $this->assertNull($event->getAttributes()['flyer_image_url'] ?? null, 'the picture has a pass of its own');
        $this->assertTrue($item->image_pending);

        $this->assertTrue($this->writer()->flyer($item, $event, self::SOURCE.'/poster.jpg'));
        $first = $event->fresh()->getAttributes()['flyer_image_url'];
        Storage::assertExists('public/'.$first);

        // The same address again: nothing is fetched.
        Http::fake();
        $this->assertTrue($this->writer()->flyer($item->fresh(), $event->fresh(), self::SOURCE.'/poster.jpg'));
        Http::assertNothingSent();
        $this->assertSame($first, $event->fresh()->getAttributes()['flyer_image_url']);

        // A picture that cannot be had leaves the one there is, to be tried again.
        $this->assertFalse($this->writer()->flyer($item->fresh(), $event->fresh(), self::SOURCE.'/gone.jpg'));
        $this->assertSame($first, $event->fresh()->getAttributes()['flyer_image_url']);

        // The owner puts their own flyer on it: the source's next picture is not put over it.
        Storage::put('public/flyer_owner.jpg', $this->picture(1, 2, 3));
        $event->forceFill(['flyer_image_url' => 'flyer_owner.jpg'])->save();
        $this->assertTrue($this->writer()->hasOwnersWork($event->fresh(), $item->fresh()));
        $this->assertTrue($this->writer()->flyer($item->fresh(), $event->fresh(), self::SOURCE.'/new.jpg'));
        $this->assertSame('flyer_owner.jpg', $event->fresh()->getAttributes()['flyer_image_url']);
    }

    public function test_a_source_that_drops_its_picture_takes_the_feeds_flyer_off(): void
    {
        [$item, $event] = $this->made(['image_url' => self::SOURCE.'/poster.jpg']);
        $this->writer()->flyer($item, $event, self::SOURCE.'/poster.jpg');
        $file = $event->fresh()->getAttributes()['flyer_image_url'];
        $this->assertNotNull($file);

        $this->assertTrue($this->writer()->flyer($item->fresh(), $event->fresh(), null));

        $this->assertNull($event->fresh()->getAttributes()['flyer_image_url']);
        Storage::assertMissing('public/'.$file);
    }

    /** Each of these goes with the event when it is deleted, so a feed never deletes it. */
    public function test_what_the_owner_and_guests_added_to_an_event_makes_it_theirs(): void
    {
        $writer = $this->writer();

        foreach ([
            'a promo code' => fn (Event $event) => PromoCode::create(['event_id' => $event->id, 'code' => 'X', 'type' => 'percentage', 'value' => 10, 'is_active' => true]),
            'an agenda' => fn (Event $event) => EventPart::create(['event_id' => $event->id, 'name' => 'Doors']),
            'a ticket type' => fn (Event $event) => $this->createTicket($event),
            'another schedule listing it' => fn (Event $event) => $event->roles()->attach($this->createCurator($this->createOwner())->id, ['is_accepted' => true]),
            'an edited field' => fn (Event $event) => $event->forceFill(['name' => 'Renamed by hand'])->save(),
        ] as $what => $add) {
            $item = $this->item(Str::random(8));
            $event = $writer->create($this->feed, $this->role, $this->owner, $item, $writer->wanted($this->feed, $this->role, $this->row(), null), null);
            $this->assertFalse($writer->hasOwnersWork($event->fresh(), $item->fresh()), 'before '.$what);

            $add($event);

            $this->assertTrue($writer->hasOwnersWork($event->fresh(), $item->fresh()), $what);
        }
    }

    /**
     * Two-way calendar sync sends an update back for every event we push, and the inbound side
     * rewrites the event from the calendar's copy. For a feed's event that reads as the owner
     * editing every field, and the event would stop following its source after its first push.
     */
    public function test_a_calendars_echo_of_our_own_push_does_not_rewrite_a_feeds_event(): void
    {
        [$item, $event] = $this->made();
        $before = $event->only(['name', 'description', 'starts_at', 'duration']);
        $call = function (object $service, string $method, array $args) {
            $ref = new \ReflectionMethod($service, $method);
            $ref->setAccessible(true);

            return (bool) $ref->invokeArgs($service, $args);
        };

        $start = new \Google\Service\Calendar\EventDateTime;
        $start->setDateTime('2027-05-09T17:30:00Z');
        $end = new \Google\Service\Calendar\EventDateTime;
        $end->setDateTime('2027-05-09T19:30:00Z');

        $this->assertFalse($call(app(\App\Services\GoogleCalendarService::class), 'updateEventFromGoogle', [
            $event, ['summary' => 'Echoed', 'description' => '<p>Echoed</p>', 'start' => $start, 'end' => $end, 'location' => 'Somewhere'], $this->role,
        ]));
        $this->assertFalse($call(app(\App\Services\MicrosoftCalendarService::class), 'updateEventFromMicrosoft', [
            ['subject' => 'Echoed', 'body' => ['content' => 'Echoed'], 'start' => ['dateTime' => '2027-05-09T17:30:00', 'timeZone' => 'UTC'], 'end' => ['dateTime' => '2027-05-09T19:30:00', 'timeZone' => 'UTC'], 'isAllDay' => false],
            $event, $this->role,
        ]));

        $this->assertSame($before, $event->fresh()->only(['name', 'description', 'starts_at', 'duration']));
        // So the next read still finds every field following.
        $this->assertSame([], $this->update($item, $event->fresh(), ['event_name' => 'Renamed at the source'])['kept']);

        // An event made by hand is still rewritten, as it always was.
        $byHand = $this->createEvent($this->role, ['creator_role_id' => $this->role->id]);
        $this->assertTrue($call(app(\App\Services\GoogleCalendarService::class), 'updateEventFromGoogle', [
            $byHand, ['summary' => 'Echoed', 'description' => null, 'start' => $start, 'end' => $end, 'location' => null], $this->role,
        ]));
    }
}
