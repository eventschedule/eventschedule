<?php

namespace Tests\Feature;

use App\Models\CalendarExposure;
use App\Models\CalendarSync;
use App\Models\Event;
use App\Models\Role;
use App\Services\CalendarExposureWatch;
use App\Utils\CalendarPrivacy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The sweep behind /admin/private-calendars (CalendarExposureWatch): it reads each event a
 * calendar brought in once per pass, a window of ids at a time, and keeps one row per event that
 * looks private, so the list and the alert never read thousands of descriptions.
 */
class CalendarExposureSweepTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const CURSOR = 'calendar_exposure.cursor.v1';

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Cache::forget(self::CURSOR);
        Cache::forget('meeting_link_venues.cursor.v1');
    }

    private function synced(Role $role, array $attrs): Event
    {
        return $this->createEvent($role, $attrs + ['import_source' => Event::IMPORT_GOOGLE, 'creator_role_id' => $role->id]);
    }

    public function test_an_imported_event_with_meeting_details_is_flagged_and_one_made_here_is_not(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $flagged = $this->synced($role, ['name' => 'Weekly', 'description' => "Meeting ID: 812 3456 7890\nPasscode: 123456"]);
        $ordinary = $this->synced($role, ['name' => 'Open rehearsal']);
        // Typed here by the owner: a public webinar shares its passcode on purpose.
        $handMade = $this->createEvent($role, ['name' => 'Webinar', 'description' => 'Passcode: 123456', 'creator_role_id' => $role->id]);
        $fromFeed = $this->createEvent($role, ['name' => 'Re: [EXTERNAL] Lunch', 'import_source' => Event::IMPORT_FEED, 'creator_role_id' => $role->id]);

        $done = CalendarExposureWatch::sweep();

        $this->assertSame(3, $done['read']);
        $this->assertEqualsCanonicalizing([$flagged->id, $fromFeed->id], CalendarExposure::pluck('event_id')->all());
        $this->assertSame([CalendarPrivacy::ACCESS_CODES], CalendarExposure::where('event_id', $flagged->id)->value('kinds'));
        $this->assertFalse(CalendarExposure::whereIn('event_id', [$ordinary->id, $handMade->id])->exists());
    }

    public function test_an_event_synced_before_its_source_was_recorded_is_found_by_its_mapping(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $old = $this->createEvent($role, ['name' => 'Re: Budget', 'creator_role_id' => $role->id]);
        $old->forceFill(['created_at' => '2026-09-01 10:00:00'])->saveQuietly();
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $old->id, 'role_id' => $role->id, 'google_event_id' => 'g-old', 'google_calendar_id' => 'primary']);

        // The same shape made after the column existed was made here and pushed out.
        $pushed = $this->createEvent($role, ['name' => 'Re: Budget review', 'creator_role_id' => $role->id]);
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $pushed->id, 'role_id' => $role->id, 'google_event_id' => 'g-pushed', 'google_calendar_id' => 'primary']);

        CalendarExposureWatch::sweep();

        $this->assertSame([$old->id], CalendarExposure::pluck('event_id')->all());
    }

    public function test_the_row_goes_when_the_event_is_fixed_and_matched_at_moves_only_on_a_change(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->synced($role, ['name' => 'Weekly', 'description' => 'Code: 583920174#']);

        CalendarExposureWatch::sweep();
        $first = CalendarExposure::where('event_id', $event->id)->value('matched_at');

        $this->travel(10)->minutes();
        CalendarExposureWatch::record($event->fresh());
        $this->assertEquals($first, CalendarExposure::where('event_id', $event->id)->value('matched_at'), 'read again, nothing new');

        $event->forceFill(['description' => "Code: 583920174#\nPasscode: 998877"])->save();
        CalendarExposureWatch::record($event->fresh());
        $this->assertNotEquals($first, CalendarExposure::where('event_id', $event->id)->value('matched_at'));

        $event->forceFill(['description' => 'See you there'])->save();
        CalendarExposureWatch::record($event->fresh());
        $this->assertFalse(CalendarExposure::where('event_id', $event->id)->exists());
    }

    public function test_a_hidden_event_keeps_its_row_and_a_deleted_one_loses_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $hidden = $this->synced($role, ['name' => 'Re: Lunch']);
        $deleted = $this->synced($role, ['name' => 'Re: Dinner']);
        CalendarExposureWatch::sweep();

        $hidden->setVisibilityState('draft');
        $hidden->save();
        $deleted->delete();

        Cache::forever(self::CURSOR, 0);
        CalendarExposureWatch::sweep();

        $this->assertSame([$hidden->id], CalendarExposure::pluck('event_id')->all());
        $this->assertSame(0, CalendarExposureWatch::count(), 'a draft is not on a page');
    }

    public function test_the_sweep_reads_a_window_at_a_time_and_starts_over_past_the_last_event(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->synced($role, ['name' => 'Re: Lunch']);

        Cache::forever(self::CURSOR, $event->id);
        $this->assertSame(0, CalendarExposureWatch::sweep()['read'], 'behind the cursor');
        $this->assertSame(0, (int) Cache::get(self::CURSOR), 'past the last id, back to the start');

        $this->assertSame(1, CalendarExposureWatch::sweep()['read']);

        // A window ends where it says it does.
        Cache::forever(self::CURSOR, $event->id - CalendarExposureWatch::WINDOW - 1);
        $this->assertSame(0, CalendarExposureWatch::sweep()['read']);
        $this->assertSame($event->id - 1, (int) Cache::get(self::CURSOR));
    }

    public function test_a_new_version_reads_what_is_flagged_again_first(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->synced($role, ['name' => 'Weekly']);

        // A row an older version of the patterns wrote, which these do not agree with.
        CalendarExposure::create(['event_id' => $event->id, 'role_id' => $role->id, 'kinds' => ['access_codes'], 'text_hash' => str_repeat('a', 40), 'matched_at' => now()]);
        Cache::forget(self::CURSOR);

        $done = CalendarExposureWatch::sweep();

        $this->assertSame(1, $done['removed']);
        $this->assertFalse(CalendarExposure::exists());
    }

    public function test_the_command_also_takes_down_venues_made_out_of_meeting_links(): void
    {
        $owner = $this->createOwner();
        $venue = new Role;
        $venue->type = 'venue';
        $venue->user_id = $owner->id;
        $venue->subdomain = 'stub-'.Str::lower(Str::random(10));
        $venue->name = 'https://us02web.zoom.us/j/1?pwd=abc123';
        $venue->address1 = $venue->name;
        $venue->save();

        $this->artisan('calendar:scan-exposures')->assertSuccessful();

        $this->assertTrue((bool) $venue->fresh()->is_deleted);
    }
}
