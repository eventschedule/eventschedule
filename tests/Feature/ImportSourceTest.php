<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\BackupService;
use App\Services\CalDAVService;
use App\Services\GoogleCalendarService;
use App\Services\MicrosoftCalendarService;
use App\Utils\ImportRun;
use App\Utils\UrlUtils;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * events.import_source says which import made an event, and events.import_batch which sitting on
 * the import page it came from. Both are stamped by the code that creates the event and by
 * nothing else: neither is fillable, so no form and no hand-built request can write them.
 *
 * The growth export counts on the first to say whether importing is what fills a schedule; "N
 * events added" and "Undo this import" count on the second.
 */
class ImportSourceTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'venue');
        $this->actingAs($this->owner);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Open Mic',
            'starts_at' => now()->addDays(10)->setTime(19, 0)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'schedule_type' => 'one_time',
        ], $overrides);
    }

    private function import(array $overrides = [])
    {
        return $this->postJson(
            route('event.import', ['subdomain' => $this->role->subdomain]),
            $this->payload($overrides)
        );
    }

    private function openImportPage(): string
    {
        $this->get(route('event.show_import_ai', ['subdomain' => $this->role->subdomain]))->assertOk();

        return session('import_run.'.$this->role->id)['batch'];
    }

    private function lastEvent(): Event
    {
        return Event::query()->latest('id')->firstOrFail();
    }

    /** Call a calendar service's non-public "create a local event" method. */
    private function create(object $service, string $method, array $args): Event
    {
        $reflection = new \ReflectionMethod($service, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($service, $args)->fresh();
    }

    public function test_an_event_typed_into_the_form_has_no_source(): void
    {
        $this->openImportPage();

        // Even with a run open, and even when the form posts the columns by name.
        $this->post(route('event.store', ['subdomain' => $this->role->subdomain]), $this->payload([
            'import_source' => 'ai',
            'import_batch' => 'aaaaaaaaaaaa',
        ]))->assertRedirect();

        $event = $this->lastEvent();
        $this->assertSame('Open Mic', $event->name);
        $this->assertNull($event->import_source);
        $this->assertNull($event->import_batch);
    }

    public function test_the_import_page_stamps_its_source_and_the_visits_batch(): void
    {
        $batch = $this->openImportPage();
        $this->assertSame(12, strlen($batch));

        $this->import(['name' => 'First'])->assertOk();
        $this->import(['name' => 'Second'])->assertOk();

        $events = Event::orderBy('id')->get();
        $this->assertSame(['ai', 'ai'], $events->pluck('import_source')->all());
        $this->assertSame([$batch, $batch], $events->pluck('import_batch')->all());
    }

    public function test_an_import_posted_without_visiting_the_page_has_a_source_and_a_run_of_its_own(): void
    {
        // A second tab, or a page the browser restored after its run was closed, saves with no
        // run open. It used to belong to none, which left it uncounted and beyond Undo.
        $this->import()->assertOk();

        $this->assertSame('ai', $this->lastEvent()->import_source);
        $this->assertMatchesRegularExpression('/^[a-z0-9]{12}$/', (string) $this->lastEvent()->import_batch);
    }

    public function test_what_the_request_says_about_either_column_is_ignored(): void
    {
        $batch = $this->openImportPage();

        $this->import(['import_source' => 'google', 'import_batch' => 'aaaaaaaaaaaa'])->assertOk();

        $event = $this->lastEvent();
        $this->assertSame('ai', $event->import_source);
        $this->assertSame($batch, $event->import_batch);

        // Nor can editing the event afterwards rewrite them.
        $this->put(
            route('event.update', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
            $this->payload(['name' => 'Renamed', 'import_source' => 'eventbrite', 'import_batch' => 'bbbbbbbbbbbb'])
        )->assertRedirect();

        $event->refresh();
        $this->assertSame('Renamed', $event->name);
        $this->assertSame('ai', $event->import_source);
        $this->assertSame($batch, $event->import_batch);

        $this->assertNotContains('import_source', (new Event)->getFillable());
        $this->assertNotContains('import_batch', (new Event)->getFillable());
    }

    public function test_a_reload_keeps_the_batch_and_a_visit_an_hour_later_starts_another(): void
    {
        $first = $this->openImportPage();
        $this->assertSame($first, $this->openImportPage());

        $this->travel(ImportRun::OPEN_MINUTES + 1)->minutes();
        $later = $this->openImportPage();

        $this->assertNotSame($first, $later);
    }

    public function test_one_schedules_batch_is_not_anothers(): void
    {
        $other = $this->createRole($this->owner, 'venue');
        $batch = $this->openImportPage();

        $this->postJson(route('event.import', ['subdomain' => $other->subdomain]), $this->payload())->assertOk();

        // The run was opened on the first schedule's page. This event is in one of the other
        // schedule's own, not in that.
        $this->assertNotNull($batch);
        $this->assertNotNull($this->lastEvent()->import_batch);
        $this->assertNotSame($batch, $this->lastEvent()->import_batch);
    }

    public function test_the_eventbrite_import_stamps_its_own_source(): void
    {
        Http::fake(['www.eventbriteapi.com/*' => Http::response(['description' => '<p>From Eventbrite</p>'])]);
        $this->get(route('event.show_import_eventbrite', ['subdomain' => $this->role->subdomain]))->assertOk();
        $batch = session('import_run.'.$this->role->id)['batch'];

        $this->postJson(route('event.eventbrite_import', ['subdomain' => $this->role->subdomain]), [
            'token' => 'private-token',
            'eventbrite_id' => '123456',
            'name' => 'Brought Over',
            'start_local' => now()->addDays(10)->setTime(19, 0)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'currency' => 'USD',
        ])->assertOk();

        $event = $this->lastEvent();
        $this->assertSame('Brought Over', $event->name);
        $this->assertSame('eventbrite', $event->import_source);
        $this->assertSame($batch, $event->import_batch);
    }

    public function test_a_calendar_pull_stamps_the_calendar_it_came_from(): void
    {
        $start = now()->addDays(10)->setTime(19, 0);

        $googleTime = fn ($time) => tap(new EventDateTime, fn ($when) => $when->setDateTime($time->toRfc3339String()));
        $google = $this->create(app(GoogleCalendarService::class), 'createEventFromGoogle', [[
            'id' => 'g-1',
            'summary' => 'From Google',
            'description' => '',
            'location' => null,
            'start' => $googleTime($start),
            'end' => $googleTime($start->copy()->addHours(2)),
        ], $this->role, 'primary']);

        $microsoft = $this->create(app(MicrosoftCalendarService::class), 'createEventFromMicrosoft', [[
            'id' => 'm-1',
            'subject' => 'From Outlook',
            'body' => ['content' => ''],
            'start' => ['dateTime' => $start->format('Y-m-d\TH:i:s'), 'timeZone' => 'UTC'],
            'end' => ['dateTime' => $start->copy()->addHours(2)->format('Y-m-d\TH:i:s'), 'timeZone' => 'UTC'],
        ], $this->role, 'cal-1']);

        $caldav = $this->create(app(CalDAVService::class), 'createEventFromCalDAV', [[
            'uid' => 'c-1',
            'etag' => null,
            'summary' => 'From CalDAV',
            'description' => '',
            'location' => null,
            'start' => $start->toDateTime(),
            'duration' => 2,
        ], $this->role]);

        $this->assertSame('google', $google->import_source);
        $this->assertSame('microsoft', $microsoft->import_source);
        $this->assertSame('caldav', $caldav->import_source);
        // A standing sync is not a sitting on the import page.
        $this->assertNull($google->import_batch);
    }

    public function test_whether_the_import_columns_exist_is_remembered_only_once_they_do(): void
    {
        // The scheduler worker asks during the minute of a deploy in which the columns are not
        // there yet, and is the same process for hours after. A "no" kept for its lifetime left
        // every event it synced unlabelled long after the migration had run.
        $remembered = new \ReflectionProperty(Event::class, 'importColumnsReady');
        $remembered->setValue(null, false);

        try {
            \Illuminate\Support\Facades\Schema::partialMock()->shouldReceive('hasColumn')
                ->with('events', 'import_source')->twice()->andReturn(false, true);

            $this->assertFalse(Event::importColumnsReady());
            $this->assertTrue(Event::importColumnsReady(), 'asked again, because the first answer was no');
            $this->assertTrue(Event::importColumnsReady(), 'and not asked a third time');
        } finally {
            $remembered->setValue(null, true);
        }
    }

    public function test_every_source_written_is_in_the_vocabulary_and_none_is_exported(): void
    {
        $this->assertSame(
            ['ai', 'ics', 'page', 'page_ai', 'eventbrite', 'google', 'microsoft', 'caldav', 'feed'],
            Event::IMPORT_SOURCES
        );

        // A source outside the vocabulary is dropped rather than stored, and takes its batch
        // with it: a batch only means something next to a source.
        $request = \Illuminate\Http\Request::create('/', 'POST', $this->payload());
        $request->setUserResolver(fn () => $this->owner);
        $event = app(\App\Repos\EventRepo::class)->saveEvent(
            $this->role, $request, null, true, null,
            importSource: 'made-up',
            importBatch: 'aaaaaaaaaaaa',
        );
        $this->assertNull($event->fresh()->import_source);
        $this->assertNull($event->fresh()->import_batch);

        $excluded = (new \ReflectionClassConstant(BackupService::class, 'EVENT_EXPORT_EXCLUDE'))->getValue();
        $this->assertContains('import_source', $excluded);
        $this->assertContains('import_batch', $excluded);
    }
}
