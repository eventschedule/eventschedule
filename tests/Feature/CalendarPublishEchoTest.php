<?php

namespace Tests\Feature;

use App\Jobs\SyncEventToCalDAV;
use App\Jobs\SyncEventToGoogleCalendar;
use App\Jobs\SyncEventToMicrosoftCalendar;
use App\Models\CalendarSync;
use App\Models\Event;
use App\Models\MicrosoftCalendarSync;
use App\Models\Role;
use App\Models\User;
use App\Services\CalDAVService;
use App\Services\EventLifecycleService;
use App\Services\GoogleCalendarService;
use App\Services\MicrosoftCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An event a standing sync read in is a copy of an entry in the owner's own calendar. Publishing
 * the copy used to push a "create" and put a second entry beside the original; unpublishing or
 * deleting it pushed a "delete", which removed the original. Now that a sync's events arrive as
 * drafts and are published, hidden and tidied here, both would happen every day.
 */
class CalendarPublishEchoTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    /** @return array{0: User, 1: Role} */
    private function syncingSchedule(array $roleAttrs = []): array
    {
        $owner = $this->createOwner();
        $owner->forceFill([
            'google_token' => 'tok',
            'google_refresh_token' => 'ref',
            'microsoft_token' => 'mtok',
            'microsoft_refresh_token' => 'mref',
        ])->save();

        $role = $this->createRole($owner, 'venue', $roleAttrs + ['sync_direction' => 'both']);

        return [$owner, $role];
    }

    private function syncedDraft(Role $role, string $source): Event
    {
        return $this->createEvent($role, [
            'is_draft' => true,
            'import_source' => $source,
            'creator_role_id' => $role->id,
        ]);
    }

    private function googleMock(): \Mockery\MockInterface
    {
        $mock = Mockery::mock(GoogleCalendarService::class);
        $mock->shouldReceive('ensureValidToken')->andReturn(true);
        $mock->shouldReceive('setAccessToken');
        $this->app->instance(GoogleCalendarService::class, $mock);

        return $mock;
    }

    public function test_publishing_a_draft_the_google_sync_read_in_does_not_create_it_again(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->syncedDraft($role, Event::IMPORT_GOOGLE);
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'google_event_id' => 'g-original', 'google_calendar_id' => 'primary']);

        $google = $this->googleMock();
        $google->shouldNotReceive('createEvent');

        $outcome = app(EventLifecycleService::class)->publish($event->fresh(), $owner->id, $role->fresh());

        $this->assertSame(EventLifecycleService::PUBLISHED, $outcome);
        $this->assertSame('g-original', CalendarSync::where('event_id', $event->id)->value('google_event_id'));
    }

    public function test_an_event_with_no_entry_yet_is_still_created(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['is_draft' => true]);

        $created = new \Google\Service\Calendar\Event;
        $created->setId('g-new');
        $google = $this->googleMock();
        $google->shouldReceive('createEvent')->once()->andReturn($created);

        app(EventLifecycleService::class)->publish($event->fresh(), $owner->id, $role->fresh());

        $this->assertSame('g-new', CalendarSync::where('event_id', $event->id)->value('google_event_id'));
    }

    public function test_hiding_or_deleting_a_synced_draft_leaves_the_owners_google_entry(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->syncedDraft($role, Event::IMPORT_GOOGLE);
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'google_event_id' => 'g-original', 'google_calendar_id' => 'primary']);

        $google = $this->googleMock();
        $google->shouldNotReceive('deleteEvent');

        (new SyncEventToGoogleCalendar($event->fresh(), $role, 'delete'))->handle($google);
        $this->assertTrue(CalendarSync::where('event_id', $event->id)->exists());

        $event->fresh()->delete();
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_a_team_members_copy_of_a_synced_draft_is_still_removed(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $member = $this->createOwner();
        $member->forceFill(['google_token' => 'tok2', 'google_refresh_token' => 'ref2'])->save();
        $event = $this->syncedDraft($role, Event::IMPORT_GOOGLE);
        CalendarSync::create(['user_id' => $member->id, 'event_id' => $event->id, 'role_id' => $role->id, 'google_event_id' => 'g-member-copy', 'google_calendar_id' => 'member-cal']);

        $google = $this->googleMock();
        $google->shouldReceive('deleteEvent')->once()->with('g-member-copy', 'member-cal', $role->id)->andReturn(true);

        (new SyncEventToGoogleCalendar($event->fresh(), $role, 'delete', $member, 'member-cal'))->handle($google);

        $this->assertFalse(CalendarSync::where('event_id', $event->id)->where('user_id', $member->id)->exists());
    }

    public function test_an_event_made_here_is_still_deleted_from_the_calendar_it_was_pushed_to(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['is_draft' => true]);
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'google_event_id' => 'g-pushed', 'google_calendar_id' => 'primary']);

        $google = $this->googleMock();
        $google->shouldReceive('deleteEvent')->once()->andReturn(true);

        (new SyncEventToGoogleCalendar($event->fresh(), $role, 'delete'))->handle($google);
    }

    public function test_a_one_time_import_from_the_same_calendar_is_not_the_owners_original(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['is_draft' => true, 'import_source' => Event::IMPORT_GOOGLE, 'import_batch' => 'batch-1', 'creator_role_id' => $role->id]);

        $this->assertFalse($event->cameFromCalendarSync(Event::IMPORT_GOOGLE, $role));
        $this->assertTrue($this->syncedDraft($role, Event::IMPORT_GOOGLE)->cameFromCalendarSync(Event::IMPORT_GOOGLE, $role));
        $this->assertFalse($this->syncedDraft($role, Event::IMPORT_GOOGLE)->cameFromCalendarSync(Event::IMPORT_MICROSOFT));
    }

    private function syncedPublished(Role $role, string $source): Event
    {
        return $this->createEvent($role, [
            'is_draft' => false,
            'import_source' => $source,
            'creator_role_id' => $role->id,
        ]);
    }

    /**
     * Cancelling can be undone and deleting a calendar entry cannot, so cancelling a published
     * event the sync read in leaves the owner's entry; restoring it does not make a second one.
     */
    public function test_cancelling_a_published_synced_event_leaves_the_owners_google_entry(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->syncedPublished($role, Event::IMPORT_GOOGLE);
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'google_event_id' => 'g-original', 'google_calendar_id' => 'primary']);

        $google = $this->googleMock();
        $google->shouldNotReceive('deleteEvent');
        $google->shouldNotReceive('createEvent');

        $lifecycle = app(EventLifecycleService::class);
        $this->assertSame(EventLifecycleService::CANCELLED, $lifecycle->cancel($event->fresh(), $owner->id));
        $this->assertSame('g-original', CalendarSync::where('event_id', $event->id)->value('google_event_id'));

        $this->assertSame(EventLifecycleService::RESTORED, $lifecycle->restore($event->fresh(), $owner->id));
        $this->assertSame('g-original', CalendarSync::where('event_id', $event->id)->value('google_event_id'));
    }

    /** The control: an event made here and pushed out still comes off the calendar on a cancel. */
    public function test_cancelling_an_event_made_here_still_removes_its_entry(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'google_event_id' => 'g-pushed', 'google_calendar_id' => 'primary']);

        $google = $this->googleMock();
        $google->shouldReceive('deleteEvent')->once()->with('g-pushed', 'primary', $role->id)->andReturn(true);

        app(EventLifecycleService::class)->cancel($event->fresh(), $owner->id);

        $this->assertFalse(CalendarSync::where('event_id', $event->id)->exists());
    }

    /** Deleting the event here removes the original while the schedule pushes its changes there. */
    public function test_deleting_a_published_synced_event_removes_the_original_when_the_sync_is_both_ways(): void
    {
        [$owner, $role] = $this->syncingSchedule();
        $event = $this->syncedPublished($role, Event::IMPORT_GOOGLE);
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'google_event_id' => 'g-original', 'google_calendar_id' => 'primary']);

        $google = $this->googleMock();
        $google->shouldReceive('deleteEvent')->once()->with('g-original', 'primary', $role->id)->andReturn(true);

        $event->fresh()->delete();

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    /** A schedule that only reads from the calendar never deletes the owner's entry. */
    public function test_deleting_a_synced_event_on_a_read_only_sync_leaves_the_original(): void
    {
        [$owner, $role] = $this->syncingSchedule(['sync_direction' => 'from']);
        $event = $this->syncedPublished($role, Event::IMPORT_GOOGLE);
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'google_event_id' => 'g-original', 'google_calendar_id' => 'primary']);

        $google = $this->googleMock();
        $google->shouldNotReceive('deleteEvent');

        (new SyncEventToGoogleCalendar($event->fresh(), $role, 'delete'))->handle($google);
        $this->assertTrue(CalendarSync::where('event_id', $event->id)->exists());

        $event->fresh()->delete();
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_cancelling_keeps_the_original_on_outlook_and_caldav_too(): void
    {
        [$owner, $role] = $this->syncingSchedule(['sync_direction' => null, 'microsoft_sync_direction' => 'both']);
        $role->forceFill([
            'caldav_settings' => ['server_url' => 'https://calendar.example.org', 'username' => 'x', 'password' => 'y', 'calendar_url' => 'https://calendar.example.org/c'],
            'caldav_sync_direction' => 'both',
        ])->save();

        $outlookEvent = $this->syncedPublished($role, Event::IMPORT_MICROSOFT);
        MicrosoftCalendarSync::create(['user_id' => $owner->id, 'event_id' => $outlookEvent->id, 'role_id' => $role->id, 'microsoft_event_id' => 'm-original', 'microsoft_calendar_id' => null]);
        $caldavEvent = $this->syncedPublished($role, Event::IMPORT_CALDAV);
        $caldavEvent->roles()->updateExistingPivot($role->id, ['caldav_event_uid' => 'uid-original']);

        $microsoft = Mockery::mock(MicrosoftCalendarService::class);
        $microsoft->shouldReceive('ensureValidToken')->andReturn(true);
        $microsoft->shouldNotReceive('deleteEvent');
        $caldav = Mockery::mock(CalDAVService::class);
        $caldav->shouldNotReceive('deleteEvent');

        (new SyncEventToMicrosoftCalendar($outlookEvent->fresh(), $role->fresh(), 'cancel'))->handle($microsoft);
        (new SyncEventToCalDAV($caldavEvent->fresh(), $role->fresh(), 'cancel'))->handle($caldav);

        $this->assertSame('m-original', MicrosoftCalendarSync::where('event_id', $outlookEvent->id)->value('microsoft_event_id'));
        $this->assertSame('uid-original', $caldavEvent->fresh()->getCalDAVEventUidForRole($role->id));

        // A delete on a read-only direction leaves them as well.
        $role->forceFill(['microsoft_sync_direction' => 'from', 'caldav_sync_direction' => 'from'])->save();
        (new SyncEventToMicrosoftCalendar($outlookEvent->fresh(), $role->fresh(), 'delete'))->handle($microsoft);
        (new SyncEventToCalDAV($caldavEvent->fresh(), $role->fresh(), 'delete'))->handle($caldav);

        $this->assertSame('m-original', MicrosoftCalendarSync::where('event_id', $outlookEvent->id)->value('microsoft_event_id'));
        $this->assertSame('uid-original', $caldavEvent->fresh()->getCalDAVEventUidForRole($role->id));
    }

    public function test_the_same_holds_for_outlook(): void
    {
        [$owner, $role] = $this->syncingSchedule(['sync_direction' => null, 'microsoft_sync_direction' => 'both']);
        $event = $this->syncedDraft($role, Event::IMPORT_MICROSOFT);
        MicrosoftCalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'microsoft_event_id' => 'm-original', 'microsoft_calendar_id' => null]);

        $microsoft = Mockery::mock(MicrosoftCalendarService::class);
        $microsoft->shouldReceive('ensureValidToken')->andReturn(true);
        $microsoft->shouldNotReceive('createEvent');
        $microsoft->shouldNotReceive('deleteEvent');

        (new SyncEventToMicrosoftCalendar($event->fresh(), $role, 'create'))->handle($microsoft);
        (new SyncEventToMicrosoftCalendar($event->fresh(), $role, 'delete'))->handle($microsoft);

        $this->assertSame('m-original', MicrosoftCalendarSync::where('event_id', $event->id)->value('microsoft_event_id'));
    }

    public function test_the_same_holds_for_caldav(): void
    {
        [$owner, $role] = $this->syncingSchedule(['sync_direction' => null]);
        $role->forceFill([
            'caldav_settings' => ['server_url' => 'https://calendar.example.org', 'username' => 'x', 'password' => 'y', 'calendar_url' => 'https://calendar.example.org/c'],
            'caldav_sync_direction' => 'both',
        ])->save();
        $event = $this->syncedDraft($role, Event::IMPORT_CALDAV);
        $event->roles()->updateExistingPivot($role->id, ['caldav_event_uid' => 'uid-original']);

        $caldav = Mockery::mock(CalDAVService::class);
        $caldav->shouldNotReceive('createEvent');
        $caldav->shouldNotReceive('deleteEvent');

        (new SyncEventToCalDAV($event->fresh(), $role->fresh(), 'create'))->handle($caldav);
        (new SyncEventToCalDAV($event->fresh(), $role->fresh(), 'delete'))->handle($caldav);

        $this->assertSame('uid-original', $event->fresh()->getCalDAVEventUidForRole($role->id));
    }
}
