<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\Role;
use App\Models\RoleSource;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\BackupService;
use App\Services\CuratorSourceService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An appointment booking is never listed.
 *
 * A booking is named after its guest, with their notes as its description. The event pages hide
 * it for being a booking (Event::isMembersOnly()), but the lists, feeds, search and curator
 * sources hide an event only for being unlisted (is_private), and the API could set that false on
 * a booking as on any other event. Event's saving hook now keeps every booking unlisted, a restore
 * does the same, and curator sources leave bookings out besides, for a row already stored listed.
 */
class AppointmentBookingStaysUnlistedTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const GUEST = 'Jane Private';

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['name' => 'Harbour Coaching']);
    }

    /** Booked the way a guest books, on a type open every day. */
    private function book(): Event
    {
        $windows = array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '00:00', 'end' => '23:59']]);
        $type = $this->createAppointmentType($this->role, ['name' => 'Intro Chat', 'weekly_windows' => $windows]);

        $from = Carbon::now($this->role->timezone)->addDay()->format('Y-m-d');
        $slots = app(AppointmentService::class)->availableSlots($type, $from, 1);

        $sale = app(AppointmentService::class)->book($type, $this->role, [
            'name' => self::GUEST,
            'email' => 'jane@gmail.com',
            'slot' => $slots['days'][array_key_first($slots['days'])][0]['utc'],
            'guest_timezone' => 'America/New_York',
        ]);

        $booking = $sale->event->fresh();
        $this->assertSame('unlisted', $booking->visibilityState(), 'fixture: a booking is saved unlisted');

        return $booking;
    }

    private function apiKey(User $user): string
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return $raw;
    }

    /** @return array<string, array{0: array<string, bool>}> */
    public static function visibilityRequests(): array
    {
        return [
            'listed' => [['is_private' => false]],
            'a draft' => [['is_private' => false, 'is_draft' => true]],
            'internal' => [['is_private' => false, 'is_internal' => true]],
        ];
    }

    #[DataProvider('visibilityRequests')]
    public function test_the_api_cannot_take_a_booking_out_of_unlisted(array $visibility): void
    {
        $booking = $this->book();

        $this->putJson('/api/events/'.UrlUtils::encodeId($booking->id), $visibility, ['X-API-Key' => $this->apiKey($this->owner)])
            ->assertOk();

        $booking->refresh();
        $this->assertSame('unlisted', $booking->visibilityState());
        $this->assertTrue($booking->is_private);
        $this->assertFalse($booking->is_draft);
        $this->assertFalse($booking->is_internal);
    }

    public function test_an_ordinary_event_can_still_be_listed(): void
    {
        $event = $this->createEvent($this->role, ['name' => 'Open Evening', 'is_private' => true, 'creator_role_id' => $this->role->id]);
        $this->assertSame('unlisted', $event->visibilityState(), 'fixture');

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), ['is_private' => false], ['X-API-Key' => $this->apiKey($this->owner)])
            ->assertOk();

        $this->assertSame('public', $event->fresh()->visibilityState());
    }

    public function test_a_booking_stored_listed_is_never_sourced_into_a_curator(): void
    {
        $booking = $this->book();
        $public = $this->createEvent($this->role, ['name' => 'Open Evening']);

        // Stored listed around the hook, as the API could until now.
        Event::whereKey($booking->id)->update(['is_private' => false]);

        $curator = $this->createCurator($this->createOwner());
        RoleSource::create(['role_id' => $curator->id, 'source_role_id' => $this->role->id]);

        $sources = app(CuratorSourceService::class);
        $sources->reconcile($curator);

        $linked = fn () => DB::table('event_role')->where('role_id', $curator->id)->pluck('event_id')->all();
        $this->assertSame([$public->id], $linked(), 'the ordinary event is sourced and the booking is not');

        // One linked before this check existed comes off on the next pass.
        DB::table('event_role')->insert([
            'event_id' => $booking->id,
            'role_id' => $curator->id,
            'is_accepted' => true,
            'is_auto_sourced' => true,
        ]);
        $this->assertSame(1, $sources->reconcile($curator)['removed']);
        $this->assertSame([$public->id], $linked());
    }

    public function test_a_restored_booking_is_unlisted_even_when_its_backup_says_otherwise(): void
    {
        $booking = $this->book();
        Event::whereKey($booking->id)->update(['is_private' => false]);

        $service = app(BackupService::class);
        $exportJob = BackupJob::create(['user_id' => $this->owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $service->exportSchedules([$this->role->fresh()], false, $exportJob)['json'];

        $exported = collect($data['schedules'][0]['events'])->firstWhere('_appointment_type_ref_id', $booking->appointment_type_id);
        $this->assertFalse((bool) $exported['is_private'], 'fixture: the backup carries the booking listed');

        $importJob = BackupJob::create(['user_id' => $this->owner->id, 'type' => 'import', 'status' => 'processing']);
        $service->importSchedules($data, [0], $this->owner->id, $importJob);

        $restored = Event::whereNotNull('appointment_type_id')->where('id', '!=', $booking->id)->sole();
        $this->assertStringContainsString(self::GUEST, $restored->name);
        $this->assertSame('unlisted', $restored->visibilityState());
    }
}
