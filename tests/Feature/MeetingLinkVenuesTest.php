<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CalendarSync;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use App\Services\CalendarArrival;
use App\Services\GoogleCalendarService;
use App\Services\MeetingLinkVenues;
use Google\Service\Calendar\Event as GoogleEvent;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Venue pages calendar imports made out of meeting links (MeetingLinkVenues). One schedule's
 * Zoom invitations became public venues named after the link, the meeting's password in the
 * name, the address and the subdomain. Those are taken down; a venue somebody runs, or one with
 * a real place beside a link, is left alone.
 */
class MeetingLinkVenuesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const ZOOM = 'https://us02web.zoom.us/j/81234567890?pwd=Xq7rTzLmPa2wKdE9vB.1';

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Cache::forget('meeting_link_venues.cursor.v1');
    }

    /** A placeholder exactly as ConvertsLocationToVenue made one. */
    private function stub(User $user, string $location): Role
    {
        $venue = new Role;
        $venue->type = 'venue';
        $venue->user_id = $user->id;
        $venue->subdomain = Role::generateSubdomain($location) ?: 'stub-'.Str::lower(Str::random(8));
        $venue->name = $location;
        $venue->address1 = $location;
        $venue->save();
        $venue->members()->attach($user->id, ['level' => 'follower', 'created_at' => now()]);

        return $venue->fresh();
    }

    public function test_a_venue_made_out_of_a_meeting_link_is_taken_down_and_the_link_goes_with_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['name' => 'Block association committee']);
        $venue = $this->stub($owner, self::ZOOM);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $this->assertTrue(MeetingLinkVenues::isMeetingStub($venue));

        $this->assertSame(1, MeetingLinkVenues::sweep());

        $gone = Role::find($venue->id);
        $this->assertTrue((bool) $gone->is_deleted);
        foreach (['name', 'address1', 'subdomain', 'subdomain_before_delete', 'geo_address'] as $column) {
            $this->assertStringNotContainsStringIgnoringCase('pwd', (string) $gone->{$column}, $column);
            $this->assertStringNotContainsStringIgnoringCase('zoom', (string) $gone->{$column}, $column);
        }
        $this->assertFalse($event->fresh()->roles()->where('roles.id', $venue->id)->exists());
        $this->assertNull($event->fresh()->venue, 'Event::venue() would otherwise still find it');

        $audit = AuditLog::where('action', AuditService::ADMIN_CALENDAR_PRIVACY_VENUE_REMOVED)->firstOrFail();
        $this->assertStringNotContainsStringIgnoringCase('zoom', json_encode($audit->getAttributes()));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function meetings(): array
    {
        return [
            'a dial-in with its code' => ['Microsoft Teams Meeting or Conference call #: (555)-010-0199 / Code: 583920174#'],
            'a provider on its own' => ['Microsoft Teams Meeting'],
            'a bare join link' => ['https://meet.google.com/abc-defg-hij'],
        ];
    }

    /**
     * @dataProvider meetings
     */
    public function test_every_kind_of_meeting_placeholder_is_one(string $location): void
    {
        $this->assertTrue(MeetingLinkVenues::isMeetingStub($this->stub($this->createOwner(), $location)));
    }

    public function test_a_venue_somebody_runs_or_a_real_place_is_left_alone(): void
    {
        $owner = $this->createOwner();

        $claimed = $this->createRole($owner, 'venue', ['address1' => self::ZOOM]);
        $bar = $this->stub($owner, 'Zoom Bar, 12 Main St');
        $room = $this->stub($owner, 'Microsoft Teams Meeting; ASO-HDQ Room 4');

        MeetingLinkVenues::sweep();

        $this->assertFalse((bool) $claimed->fresh()->is_deleted);
        $this->assertFalse((bool) $bar->fresh()->is_deleted);
        $this->assertFalse((bool) $room->fresh()->is_deleted);
    }

    public function test_the_next_read_takes_a_placeholder_off_an_event_that_is_already_public(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['sync_direction' => 'from']);
        CalendarArrival::setPublishing($role, CalendarArrival::GOOGLE, true);
        $event = $this->createEvent($role, ['name' => 'Weekly', 'import_source' => Event::IMPORT_GOOGLE, 'creator_role_id' => $role->id]);
        $venue = $this->stub($owner, self::ZOOM);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        CalendarSync::create(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id, 'google_event_id' => 'g-1', 'google_calendar_id' => 'primary']);

        $at = \Carbon\Carbon::parse($event->starts_at, 'UTC');
        $start = new EventDateTime;
        $start->setDateTime($at->toRfc3339String());
        $end = new EventDateTime;
        $end->setDateTime($at->copy()->addHour()->toRfc3339String());
        $entry = new GoogleEvent;
        $entry->setId('g-1');
        $entry->setStatus('confirmed');
        $entry->setSummary('Weekly');
        $entry->setLocation(self::ZOOM);
        $entry->setStart($start);
        $entry->setEnd($end);

        $results = ['created' => 0, 'updated' => 0, 'deleted' => 0, 'errors' => 0];
        (new \ReflectionMethod(GoogleCalendarService::class, 'processInboundGoogleEvent'))
            ->invokeArgs(app(GoogleCalendarService::class), [$entry, $role->fresh(), 'primary', &$results]);

        $this->assertFalse($event->fresh()->roles()->where('roles.id', $venue->id)->exists());
    }

    public function test_the_sweep_walks_a_window_at_a_time_and_starts_over_at_the_end(): void
    {
        $owner = $this->createOwner();
        $first = $this->stub($owner, self::ZOOM);

        Cache::forever('meeting_link_venues.cursor.v1', $first->id);
        $this->assertSame(0, MeetingLinkVenues::sweep(), 'behind the cursor');
        $this->assertSame(0, (int) Cache::get('meeting_link_venues.cursor.v1'), 'past the last venue, back to the start');

        $this->assertSame(1, MeetingLinkVenues::sweep());
    }
}
