<?php

namespace Tests\Feature;

use App\Models\CalendarImportHold;
use App\Models\CalendarSync;
use App\Models\CalendarSyncSetting;
use App\Models\Event;
use App\Models\MicrosoftCalendarSync;
use App\Models\Role;
use App\Models\User;
use App\Repos\EventRepo;
use App\Services\CalendarArrival;
use App\Services\GoogleCalendarService;
use App\Services\MicrosoftCalendarService;
use App\Utils\CalendarPrivacy;
use Google\Service\Calendar\Event as GoogleEvent;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\Events;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The one full read every standing sync owes its calendar. An incremental read (Google's sync
 * token, Outlook's delta link, CalDAV's etags) only sees what changed, so an entry read in before
 * the privacy filters existed would stay on the public page until somebody edited it. The full
 * read runs from the sync commands only, a few a run, and is done once.
 */
class CalendarReReadTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    /** @return array{0: User, 1: Role, 2: Event} */
    private function publishingScheduleWithAPublicEntry(array $roleAttrs): array
    {
        $owner = $this->createOwner();
        $owner->forceFill(['google_token' => 'tok', 'google_refresh_token' => 'ref', 'microsoft_token' => 'mtok', 'microsoft_refresh_token' => 'mref'])->save();
        $role = $this->createRole($owner, 'talent', $roleAttrs);
        CalendarSyncSetting::create(['role_id' => $role->id, 'google_publishes' => true, 'microsoft_publishes' => true]);

        // Read in before this release: public, with its dial-in in the description.
        $event = $this->createEvent($role, [
            'name' => 'Ops daily call',
            'description' => 'Conference call: (555)-010-0199 / Code: 583920174#',
            'import_source' => $roleAttrs['microsoft_sync_direction'] ?? null ? Event::IMPORT_MICROSOFT : Event::IMPORT_GOOGLE,
            'creator_role_id' => $role->id,
        ]);

        return [$owner, $role, $event];
    }

    private function googleEntry(Event $event): GoogleEvent
    {
        $start = new EventDateTime;
        $start->setDateTime(\Carbon\Carbon::parse($event->starts_at, 'UTC')->toRfc3339String());
        $end = new EventDateTime;
        $end->setDateTime(\Carbon\Carbon::parse($event->starts_at, 'UTC')->addHour()->toRfc3339String());

        $entry = new GoogleEvent;
        $entry->setId('g-1');
        $entry->setStatus('confirmed');
        $entry->setSummary($event->name);
        $entry->setDescription($event->description);
        $entry->setStart($start);
        $entry->setEnd($end);

        return $entry;
    }

    /** A Google service whose calendar answers with $items, recording what it was asked. */
    private function google(array $items, array &$asked): GoogleCalendarService
    {
        $page = new Events;
        $page->setItems($items);
        $page->setNextSyncToken('fresh-token');

        $events = Mockery::mock();
        $events->shouldReceive('listEvents')->andReturnUsing(function ($calendarId, $params) use ($page, &$asked) {
            $asked[] = $params;

            return $page;
        });

        $service = Mockery::mock(GoogleCalendarService::class, [app(EventRepo::class)])->makePartial();
        $service->shouldReceive('refreshTokenIfNeeded')->andReturn(true);
        (new \ReflectionProperty(GoogleCalendarService::class, 'calendarService'))->setValue($service, (object) ['events' => $events]);

        return $service;
    }

    public function test_the_full_read_reads_the_whole_window_and_hides_what_now_counts_as_private(): void
    {
        [$owner, $role, $event] = $this->publishingScheduleWithAPublicEntry(['sync_direction' => 'from', 'google_sync_token' => 'old-token']);
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'google_event_id' => 'g-1', 'google_calendar_id' => 'primary']);
        $this->assertFalse($event->is_draft);

        $asked = [];
        $service = $this->google([$this->googleEntry($event)], $asked);
        $service->rereadAllowed = true;
        $service->syncFromGoogleCalendar($owner, $role->fresh(), 'primary');

        $this->assertArrayNotHasKey('syncToken', $asked[0], 'the stored token only sees what changed');
        $this->assertArrayHasKey('timeMin', $asked[0]);

        $this->assertTrue($event->fresh()->is_draft);
        $hold = CalendarImportHold::where('event_id', $event->id)->first();
        $this->assertSame(CalendarPrivacy::HOLD_MEETING_DETAILS, $hold->reason);
        $this->assertSame('public', $hold->was_visibility);

        $this->assertSame('fresh-token', $role->fresh()->google_sync_token);
        $this->assertNotNull(CalendarSyncSetting::where('role_id', $role->id)->value('google_reread_at'));
        $this->assertFalse(CalendarArrival::needsReread($role->fresh(), CalendarArrival::GOOGLE));
        $this->assertNull(CalendarSyncSetting::where('role_id', $role->id)->value('google_first_pull'), 'a full read of an old sync is not its first');
    }

    public function test_a_webhook_or_sync_now_reads_only_what_changed(): void
    {
        [$owner, $role] = $this->publishingScheduleWithAPublicEntry(['sync_direction' => 'from', 'google_sync_token' => 'old-token']);

        $asked = [];
        $this->google([], $asked)->syncFromGoogleCalendar($owner, $role->fresh(), 'primary');

        $this->assertSame('old-token', $asked[0]['syncToken']);
        $this->assertTrue(CalendarArrival::needsReread($role->fresh(), CalendarArrival::GOOGLE));
    }

    public function test_once_done_the_full_read_is_not_repeated(): void
    {
        [$owner, $role] = $this->publishingScheduleWithAPublicEntry(['sync_direction' => 'from', 'google_sync_token' => 'old-token']);
        CalendarArrival::finishReread($role, CalendarArrival::GOOGLE);

        $asked = [];
        $service = $this->google([], $asked);
        $service->rereadAllowed = true;
        $service->syncFromGoogleCalendar($owner, $role->fresh(), 'primary');

        $this->assertSame('old-token', $asked[0]['syncToken']);
    }

    public function test_outlook_starts_its_full_read_from_the_window_not_the_delta_link(): void
    {
        [$owner, $role, $event] = $this->publishingScheduleWithAPublicEntry(['sync_direction' => null, 'microsoft_sync_direction' => 'from', 'microsoft_sync_token' => 'https://graph.microsoft.com/v1.0/me/calendarView/delta?$deltatoken=old']);
        MicrosoftCalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'microsoft_event_id' => 'm-1']);

        $at = \Carbon\Carbon::parse($event->starts_at, 'UTC');
        Http::fake(function ($request) use ($event, $at) {
            $this->assertStringNotContainsString('deltatoken=old', $request->url());

            return Http::response([
                'value' => [[
                    'id' => 'm-1',
                    'subject' => $event->name,
                    'body' => ['content' => $event->description],
                    'start' => ['dateTime' => $at->format('Y-m-d\TH:i:s'), 'timeZone' => 'UTC'],
                    'end' => ['dateTime' => $at->copy()->addHour()->format('Y-m-d\TH:i:s'), 'timeZone' => 'UTC'],
                ]],
                '@odata.deltaLink' => 'https://graph.microsoft.com/v1.0/me/calendarView/delta?$deltatoken=fresh',
            ]);
        });

        $service = Mockery::mock(MicrosoftCalendarService::class, [app(EventRepo::class)])->makePartial();
        $service->shouldReceive('ensureValidToken')->andReturn(true);
        $service->rereadAllowed = true;
        $service->syncFromMicrosoftCalendar($owner, $role->fresh(), null);

        $this->assertTrue($event->fresh()->is_draft);
        $this->assertStringContainsString('deltatoken=fresh', $role->fresh()->microsoft_sync_token);
        $this->assertFalse(CalendarArrival::needsReread($role->fresh(), CalendarArrival::MICROSOFT));
    }

    public function test_the_command_gives_a_few_schedules_their_full_read_a_run(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['google_token' => 'tok', 'google_refresh_token' => 'ref'])->save();
        $roles = collect(range(1, CalendarArrival::REREADS_PER_RUN + 2))
            ->map(fn () => $this->createRole($owner, 'talent', ['sync_direction' => 'from', 'google_sync_token' => 'old-token']));

        $allowed = [];
        $service = Mockery::mock(GoogleCalendarService::class, [app(EventRepo::class)])->makePartial();
        $service->shouldReceive('ensureValidToken')->andReturn(true);
        $service->shouldReceive('syncFromGoogleCalendar')->andReturnUsing(function ($user, $role) use ($service, &$allowed) {
            $allowed[$role->id] = $service->rereadAllowed;

            return ['created' => 0, 'updated' => 0, 'deleted' => 0, 'errors' => 0];
        });
        $this->app->instance(GoogleCalendarService::class, $service);

        $this->artisan('google:sync')->assertSuccessful();

        $this->assertCount($roles->count(), $allowed);
        $this->assertSame(CalendarArrival::REREADS_PER_RUN, count(array_filter($allowed)));
        $this->assertFalse($service->rereadAllowed, 'not left on for a webhook in the same process');
    }

    public function test_the_full_read_does_not_rewrite_what_the_owner_edited_here(): void
    {
        [$owner, $role, $event] = $this->publishingScheduleWithAPublicEntry(['sync_direction' => 'from', 'google_sync_token' => 'old-token']);
        $event->forceFill(['description' => 'Doors at seven. Bring a friend.'])->save();
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'google_event_id' => 'g-1', 'google_calendar_id' => 'primary']);

        // The calendar still has the old text, dial-in code and all.
        $entry = $this->googleEntry($event);
        $entry->setDescription('Conference call: (555)-010-0199 / Code: 583920174#');

        $asked = [];
        $service = $this->google([$entry], $asked);
        $service->rereadAllowed = true;
        $service->syncFromGoogleCalendar($owner, $role->fresh(), 'primary');

        $this->assertSame('Doors at seven. Bring a friend.', $event->fresh()->description);
        $this->assertFalse($event->fresh()->is_draft, 'judged on the text people actually see');
    }

    public function test_a_saved_place_from_another_calendar_is_not_resumed(): void
    {
        [$owner, $role] = $this->publishingScheduleWithAPublicEntry(['sync_direction' => 'from', 'google_sync_token' => 'old-token']);
        CalendarArrival::saveRereadCursor($role, CalendarArrival::GOOGLE, json_encode(['page' => 'old-calendar-page', 'min' => now()->toIso8601String(), 'max' => now()->addYear()->toIso8601String()]));
        $this->assertNotNull(CalendarArrival::rereadCursor($role->fresh(), CalendarArrival::GOOGLE));

        // The owner points the sync at a calendar made for public events.
        \App\Models\RoleUser::where('role_id', $role->id)->where('user_id', $owner->id)->update(['google_calendar_id' => 'public@group.calendar.google.com']);

        $this->assertNull(CalendarArrival::rereadCursor($role->fresh(), CalendarArrival::GOOGLE));

        $asked = [];
        $service = $this->google([], $asked);
        $service->rereadAllowed = true;
        $service->syncFromGoogleCalendar($owner, $role->fresh(), 'public@group.calendar.google.com');
        $this->assertArrayNotHasKey('pageToken', $asked[0]);
    }
}
