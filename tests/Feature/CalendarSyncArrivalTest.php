<?php

namespace Tests\Feature;

use App\Models\CalendarImportHold;
use App\Models\CalendarSync;
use App\Models\CalendarSyncSetting;
use App\Models\Event;
use App\Models\MicrosoftCalendarSync;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Repos\EventRepo;
use App\Services\CalDAVService;
use App\Services\CalendarArrival;
use App\Services\GoogleCalendarService;
use App\Services\MicrosoftCalendarService;
use App\Utils\CalendarPrivacy;
use Google\Service\Calendar\Event as GoogleEvent;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * How what a standing calendar sync reads in arrives on a schedule (CalendarArrival). A sync
 * once published somebody's whole work calendar: dial-in codes, meeting passwords, an email
 * thread about a child's doctor. Now an entry the calendar marks private, declined or not an
 * event is not read in; what is read in waits as a draft unless the owner said to publish that
 * calendar; and meeting details or an email thread wait for the owner even then.
 */
class CalendarSyncArrivalTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const ZOOM = 'https://us02web.zoom.us/j/81234567890?pwd=Xq7rTzLmPa2wKdE9vB.1';

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.google.backend' => '']);
    }

    /** @return array{0: User, 1: Role} */
    private function schedule(array $attrs = ['sync_direction' => 'from']): array
    {
        $owner = $this->createOwner();

        return [$owner, $this->createRole($owner, 'talent', $attrs)];
    }

    private function googleEntry(string $id, string $summary, array $set = []): GoogleEvent
    {
        $at = now()->addDays(5)->setTime(18, 0);
        $start = new EventDateTime;
        $start->setDateTime($at->toRfc3339String());
        $end = new EventDateTime;
        $end->setDateTime($at->copy()->addHour()->toRfc3339String());

        $entry = new GoogleEvent;
        $entry->setId($id);
        $entry->setStatus('confirmed');
        $entry->setSummary($summary);
        $entry->setDescription($set['description'] ?? '');
        $entry->setLocation($set['location'] ?? null);
        $entry->setStart($start);
        $entry->setEnd($end);
        if (isset($set['visibility'])) {
            $entry->setVisibility($set['visibility']);
        }
        if (isset($set['eventType'])) {
            $entry->setEventType($set['eventType']);
        }
        if (! empty($set['declined'])) {
            $me = new EventAttendee;
            $me->setSelf(true);
            $me->setResponseStatus('declined');
            $entry->setAttendees([$me]);
        }

        return $entry;
    }

    /** @return array<string, int> */
    private function pullGoogle(Role $role, GoogleEvent $entry, bool $firstPull = false): array
    {
        $service = app(GoogleCalendarService::class);
        (new \ReflectionProperty(GoogleCalendarService::class, 'inboundFirstPull'))->setValue($service, $firstPull);

        $results = ['created' => 0, 'updated' => 0, 'deleted' => 0, 'errors' => 0];
        $method = new \ReflectionMethod(GoogleCalendarService::class, 'processInboundGoogleEvent');
        $method->invokeArgs($service, [$entry, $role->fresh(), 'primary', &$results]);

        return $results;
    }

    private function synced(Role $role, string $googleId): ?Event
    {
        $eventId = CalendarSync::where('role_id', $role->id)->where('google_event_id', $googleId)->value('event_id');

        return $eventId ? Event::find($eventId) : null;
    }

    public function test_a_new_syncs_entries_arrive_as_drafts_waiting_for_the_owner(): void
    {
        [, $role] = $this->schedule();

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Open rehearsal'), true);

        $event = $this->synced($role, 'g-1');
        $this->assertTrue($event->is_draft);
        $this->assertNull($event->published_at);

        $hold = CalendarImportHold::where('event_id', $event->id)->first();
        $this->assertSame(CalendarArrival::HOLD_REVIEW, $hold->reason);
        $this->assertTrue($hold->first_pull);
        $this->assertNull($hold->was_visibility);
    }

    public function test_an_owner_who_said_publish_gets_their_entries_on_the_page(): void
    {
        [, $role] = $this->schedule();
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Open rehearsal'));

        $event = $this->synced($role, 'g-1');
        $this->assertFalse($event->is_draft);
        $this->assertNotNull($event->published_at);
        $this->assertFalse(CalendarImportHold::where('event_id', $event->id)->exists());
    }

    public function test_publishing_is_for_the_calendar_it_was_said_for(): void
    {
        [$owner, $role] = $this->schedule();
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);

        // The owner points the sync at another calendar, by whichever path.
        RoleUser::where('role_id', $role->id)->where('user_id', $owner->id)->update(['google_calendar_id' => 'work@group.calendar.google.com']);

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Standup'));

        $this->assertTrue($this->synced($role, 'g-1')->is_draft);
    }

    public function test_a_sync_that_was_publishing_before_this_keeps_publishing_the_calendar_it_reads(): void
    {
        [, $role] = $this->schedule();
        CalendarSyncSetting::create(['role_id' => $role->id, 'google_publishes' => true]);

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Open rehearsal'));

        $this->assertFalse($this->synced($role, 'g-1')->is_draft);
        $this->assertSame(CalendarArrival::binding($role->fresh(), CalendarArrival::GOOGLE), CalendarSyncSetting::where('role_id', $role->id)->value('google_publish_binding'));
    }

    public function test_meeting_details_wait_for_the_owner_even_on_a_sync_that_publishes(): void
    {
        [, $role] = $this->schedule();
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);

        $results = $this->pullGoogle($role, $this->googleEntry('g-1', 'Block association committee', ['location' => self::ZOOM]));

        $event = $this->synced($role, 'g-1');
        $this->assertTrue($event->is_draft);
        $hold = CalendarImportHold::where('event_id', $event->id)->first();
        $this->assertSame(CalendarPrivacy::HOLD_MEETING_DETAILS, $hold->reason);
        $this->assertSame('public', $hold->was_visibility, 'where it goes once the details are gone');

        // And the link did not become a public venue named after it.
        $this->assertFalse($event->roles()->where('roles.type', 'venue')->exists());
        $this->assertFalse(Role::where('type', 'venue')->where('name', 'like', '%zoom%')->exists());
        $this->assertSame(1, $results['held']);
        $this->assertSame(1, $results['places_left_out']);
    }

    public function test_an_email_thread_waits_for_the_owner(): void
    {
        [, $role] = $this->schedule();
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Re: [EXTERNAL] Appointment', ['description' => 'Received, thank you.']));

        $hold = CalendarImportHold::where('event_id', $this->synced($role, 'g-1')->id)->first();
        $this->assertSame(CalendarPrivacy::HOLD_EMAIL_THREAD, $hold->reason);
    }

    public function test_what_the_calendar_keeps_private_is_not_read_in(): void
    {
        [, $role] = $this->schedule();
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);

        $private = $this->pullGoogle($role, $this->googleEntry('g-private', 'Trade window', ['visibility' => 'private']));
        $declined = $this->pullGoogle($role, $this->googleEntry('g-declined', 'All hands', ['declined' => true]));
        $notAnEvent = $this->pullGoogle($role, $this->googleEntry('g-ooo', 'Out of office', ['eventType' => 'outOfOffice']));

        $this->assertNull($this->synced($role, 'g-private'));
        $this->assertNull($this->synced($role, 'g-declined'));
        $this->assertNull($this->synced($role, 'g-ooo'));
        $this->assertSame(0, Event::count());
        $this->assertSame(1, $private['private']);
        $this->assertSame(1, $declined['declined']);
        $this->assertSame(1, $notAnEvent['not_an_event']);
    }

    public function test_an_entry_marked_private_later_is_hidden_and_put_back_when_it_is_not(): void
    {
        [, $role] = $this->schedule();
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Open rehearsal'));
        $event = $this->synced($role, 'g-1');
        $this->assertFalse($event->is_draft);

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Open rehearsal', ['visibility' => 'private']));
        $this->assertTrue($event->fresh()->is_draft);
        $this->assertSame(CalendarArrival::SOURCE_PRIVATE, CalendarImportHold::where('event_id', $event->id)->value('reason'));

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Open rehearsal', ['visibility' => 'default']));
        $this->assertFalse($event->fresh()->is_draft);
        $this->assertFalse(CalendarImportHold::where('event_id', $event->id)->whereNull('decided_at')->exists(), 'nothing left waiting');
    }

    public function test_an_already_public_entry_that_now_carries_a_passcode_is_taken_off_the_page(): void
    {
        [, $role] = $this->schedule();
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Weekly call'));
        $event = $this->synced($role, 'g-1');

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Weekly call', ['description' => "Meeting ID: 812 3456 7890\nPasscode: 123456"]));

        $this->assertTrue($event->fresh()->is_draft);
        $hold = CalendarImportHold::where('event_id', $event->id)->first();
        $this->assertSame(CalendarPrivacy::HOLD_MEETING_DETAILS, $hold->reason);
        $this->assertSame('public', $hold->was_visibility);
    }

    public function test_an_owner_who_published_the_text_as_it_stands_is_not_overruled_until_it_changes(): void
    {
        [$owner, $role] = $this->schedule();
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);
        $codes = "Webinar\nMeeting ID: 812 3456 7890\nPasscode: 123456";
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Public webinar', ['description' => $codes]));
        $event = $this->synced($role, 'g-1');
        $this->assertTrue($event->is_draft);

        // Published as it is: a public webinar shares its passcode on purpose.
        $event->setVisibilityState('public');
        $event->save();
        CalendarArrival::decide($event, $owner->id, CalendarArrival::DECISION_PUBLISHED);

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Public webinar', ['description' => $codes]));
        $this->assertFalse($event->fresh()->is_draft);

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Public webinar', ['description' => $codes."\nPhone Conference ID: 541 485 493#"]));
        $this->assertTrue($event->fresh()->is_draft, 'new details are a new question');
    }

    public function test_an_unlisted_event_coming_back_marked_private_is_our_own_setting(): void
    {
        [, $role] = $this->schedule(['sync_direction' => 'both']);
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Members preview'));
        $event = $this->synced($role, 'g-1');
        $event->setVisibilityState('unlisted');
        $event->save();

        // Pushed out as private (GoogleCalendarService sets visibility from is_private), read back.
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Members preview', ['visibility' => 'private']));

        $this->assertSame('unlisted', $event->fresh()->visibilityState());
        $this->assertFalse(CalendarImportHold::where('event_id', $event->id)->exists());
    }

    public function test_an_event_made_here_and_pushed_out_is_never_judged_on_the_way_back(): void
    {
        [$owner, $role] = $this->schedule(['sync_direction' => 'both']);
        $event = $this->createEvent($role, ['name' => 'Public webinar', 'description' => 'Passcode: 123456', 'creator_role_id' => $role->id]);
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'google_event_id' => 'g-pushed', 'google_calendar_id' => 'primary']);

        $this->pullGoogle($role, $this->googleEntry('g-pushed', 'Public webinar', ['description' => 'Passcode: 123456', 'visibility' => 'private']));

        $this->assertFalse($event->fresh()->is_draft);
    }

    public function test_an_event_people_hold_tickets_to_is_never_hidden(): void
    {
        [, $role] = $this->schedule();
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Ticketed talk'));
        $event = $this->synced($role, 'g-1');
        $this->createSale($event, $role);

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Ticketed talk', ['visibility' => 'private']));

        $this->assertFalse($event->fresh()->is_draft);
    }

    public function test_outlook_skips_what_is_private_and_hides_a_cancelled_meeting_under_keep_it(): void
    {
        [, $role] = $this->schedule(['sync_direction' => null, 'microsoft_sync_direction' => 'from']);
        CalendarArrival::setPublishing($role, CalendarArrival::MICROSOFT, true);

        $service = app(MicrosoftCalendarService::class);
        $method = new \ReflectionMethod(MicrosoftCalendarService::class, 'processDeltaItem');
        $at = now()->addDays(5)->setTime(18, 0);
        $item = fn (string $id, array $extra = []) => $extra + [
            'id' => $id,
            'subject' => 'Board meeting',
            'body' => ['content' => ''],
            'start' => ['dateTime' => $at->format('Y-m-d\TH:i:s'), 'timeZone' => 'UTC'],
            'end' => ['dateTime' => $at->copy()->addHour()->format('Y-m-d\TH:i:s'), 'timeZone' => 'UTC'],
        ];

        $this->assertSame('skipped', $method->invoke($service, $item('m-private', ['sensitivity' => 'private']), $role->fresh(), null));
        $this->assertSame('skipped', $method->invoke($service, $item('m-declined', ['responseStatus' => ['response' => 'declined']]), $role->fresh(), null));
        $this->assertSame(0, Event::count());

        $this->assertSame('created', $method->invoke($service, $item('m-1'), $role->fresh(), null));
        $event = Event::find(MicrosoftCalendarSync::where('microsoft_event_id', 'm-1')->value('event_id'));
        $this->assertFalse($event->is_draft);

        $method->invoke($service, $item('m-1', ['isCancelled' => true, 'subject' => 'Canceled: Board meeting']), $role->fresh(), null);
        $this->assertTrue($event->fresh()->is_draft);
        $this->assertSame(CalendarArrival::SOURCE_CANCELLED, CalendarImportHold::where('event_id', $event->id)->value('reason'));
    }

    public function test_caldav_skips_an_entry_its_calendar_marks_private(): void
    {
        [, $role] = $this->schedule(['sync_direction' => null, 'caldav_sync_direction' => 'from']);
        $role->forceFill(['caldav_settings' => ['server_url' => 'https://dav.example.org', 'username' => 'me@example.org', 'password' => 'p', 'calendar_url' => 'https://dav.example.org/cal']])->save();
        CalendarArrival::setPublishing($role->fresh(), CalendarArrival::CALDAV, true);

        $at = now()->addDays(5)->setTime(18, 0);
        $entry = fn (string $uid, array $extra = []) => $extra + [
            'uid' => $uid, 'etag' => null, 'summary' => 'Entry '.$uid, 'description' => '', 'location' => null,
            'start' => $at->copy(), 'end' => $at->copy()->addHour(), 'duration' => 1, 'attendees' => [],
        ];

        $service = Mockery::mock(CalDAVService::class, [app(EventRepo::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('getEventsFromCalDAV')->andReturn([
            $entry('c-private', ['class' => 'PRIVATE']),
            $entry('c-declined', ['attendees' => ['ME@example.org' => 'DECLINED']]),
            $entry('c-1'),
        ]);

        $results = $service->syncFromCalDAV($role->fresh());

        $this->assertSame(1, $results['created']);
        $this->assertSame(1, $results['private']);
        $this->assertSame(1, $results['declined']);
        $this->assertSame(['Entry c-1'], Event::pluck('name')->all());
        $this->assertNotNull(CalendarSyncSetting::where('role_id', $role->id)->value('caldav_first_pull'));
    }

    public function test_a_change_of_owner_stops_reading_and_takes_the_consent_back(): void
    {
        [, $role] = $this->schedule(['sync_direction' => 'both', 'microsoft_sync_direction' => 'from']);
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);

        $stopped = CalendarArrival::stopReading($role, 'transfer');

        $this->assertSame([CalendarArrival::GOOGLE, CalendarArrival::MICROSOFT], $stopped);
        $fresh = $role->fresh();
        $this->assertSame('to', $fresh->sync_direction, 'pushing out is kept');
        $this->assertNull($fresh->microsoft_sync_direction);
        $this->assertFalse(CalendarArrival::publishes($fresh, CalendarArrival::GOOGLE));
        $this->assertSame('transfer', CalendarSyncSetting::where('role_id', $role->id)->value('google_stopped_reason'));

        $this->assertSame([CalendarArrival::GOOGLE, CalendarArrival::MICROSOFT], CalendarArrival::resumeReading($fresh, 'transfer'));
        $this->assertSame('both', $role->fresh()->sync_direction);
        $this->assertSame('from', $role->fresh()->microsoft_sync_direction);
    }

    public function test_a_syncs_draft_is_not_translated_and_a_draft_made_by_hand_still_is(): void
    {
        [, $role] = $this->schedule(['sync_direction' => 'from', 'language_code' => 'en', 'translation_language_code' => 'es']);
        $synced = $this->createEvent($role, ['name' => 'Weekly sync', 'is_draft' => true, 'import_source' => Event::IMPORT_GOOGLE, 'creator_role_id' => $role->id]);
        $handMade = $this->createEvent($role, ['name' => 'Open rehearsal', 'is_draft' => true]);

        $queued = \App\Services\TranslationQueue::events(null, $role->id)->pluck('id')->all();

        $this->assertNotContains($synced->id, $queued, 'somebody\'s own calendar until they publish it');
        $this->assertContains($handMade->id, $queued, 'the NULL in import_source must not drop it');
    }

    public function test_an_unlisted_event_keeps_its_password_while_hidden_and_comes_back_with_it(): void
    {
        [, $role] = $this->schedule(['sync_direction' => 'both']);
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Members preview'));
        $event = $this->synced($role, 'g-1');
        $event->setVisibilityState('unlisted');
        $event->event_password = 'secret';
        $event->save();

        // Its text gains a passcode: hidden. The calendar still marks it private (our own push).
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Members preview', ['visibility' => 'private', 'description' => 'Passcode: 123456']));
        $this->assertTrue($event->fresh()->is_draft);
        $this->assertSame('secret', $event->fresh()->event_password);

        // The passcode goes; "private" in the calendar is still our own setting, so it comes back.
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Members preview', ['visibility' => 'private', 'description' => 'Agenda to follow']));
        $this->assertSame('unlisted', $event->fresh()->visibilityState());
        $this->assertSame('secret', $event->fresh()->event_password, 'its link must not open for anybody');
    }

    public function test_publishing_an_event_the_owner_had_kept_hidden_is_not_undone_by_the_next_read(): void
    {
        [$owner, $role] = $this->schedule();
        $codes = "Webinar\nPasscode: 123456";
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Public webinar', ['description' => $codes]));
        $event = $this->synced($role, 'g-1');
        CalendarArrival::decide($event, $owner->id, CalendarArrival::DECISION_HIDDEN);

        app(\App\Services\EventLifecycleService::class)->publish($event->fresh(), $owner->id, $role->fresh());
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Public webinar', ['description' => $codes]));

        $this->assertFalse($event->fresh()->is_draft);
    }

    public function test_a_draft_that_later_shows_meeting_details_is_left_out_of_publish_all(): void
    {
        [, $role] = $this->schedule();
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Weekly call'));
        $event = $this->synced($role, 'g-1');
        $this->assertSame(CalendarArrival::HOLD_REVIEW, CalendarImportHold::where('event_id', $event->id)->value('reason'));

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Weekly call', ['description' => 'Meeting ID: 812 3456 7890']));

        $this->assertSame(CalendarPrivacy::HOLD_MEETING_DETAILS, CalendarImportHold::where('event_id', $event->id)->value('reason'));
    }

    public function test_an_entry_held_on_arrival_is_not_taken_off_the_page_and_stays_a_draft_once_publishing_is_off(): void
    {
        [, $role] = $this->schedule();
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Weekly call', ['description' => 'Passcode: 123456']));
        $event = $this->synced($role, 'g-1');

        // Never on the page, so never "taken off" it, and nobody is mailed.
        \Illuminate\Support\Facades\Bus::fake([\App\Jobs\SendQueuedEmail::class]);
        CalendarArrival::recordPull($role->fresh(), CalendarArrival::GOOGLE, ['created' => 1], false);
        \Illuminate\Support\Facades\Bus::assertNotDispatched(\App\Jobs\SendQueuedEmail::class);
        $this->assertSame(0, CalendarArrival::notice($role->fresh())['taken']);

        // The owner turns publishing off, then takes the passcode out: it stays a draft for a look.
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, false);
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Weekly call', ['description' => 'Agenda to follow']));
        $this->assertTrue($event->fresh()->is_draft);
        $this->assertSame(CalendarArrival::HOLD_REVIEW, CalendarImportHold::where('event_id', $event->id)->value('reason'));
    }

    public function test_an_event_put_back_by_the_sync_keeps_its_first_read_mark(): void
    {
        [, $role] = $this->schedule();
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);
        $this->pullGoogle($role, $this->googleEntry('g-1', 'Weekly call', ['description' => 'Passcode: 123456']), true);
        $event = $this->synced($role, 'g-1');

        $this->pullGoogle($role, $this->googleEntry('g-1', 'Weekly call', ['description' => 'Agenda to follow']));

        $this->assertFalse($event->fresh()->is_draft);
        $hold = CalendarImportHold::where('event_id', $event->id)->first();
        $this->assertTrue($hold->first_pull, 'a first read\'s backlog is not news to followers');
        $this->assertSame(CalendarArrival::DECISION_RELEASED, $hold->decision);
    }
}
