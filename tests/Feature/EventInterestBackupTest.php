<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\EventInterest;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The interest list has to survive a backup round trip.
 *
 * BackupService exports waitlists, role_subscribers and newsletter_unsubscribes; a new per-event
 * table that it does not know about is silently dropped, and an owner who exports and reimports
 * loses a list they have no way to rebuild - people gave that address once, on a page.
 */
class EventInterestBackupTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_a_round_trip_preserves_the_interest_list_and_its_send_claims(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $original = EventInterest::create([
            'event_id' => $event->id,
            'event_date' => $event->getStartDateTime(null, true, $event->scheduleTimezone())->format('Y-m-d'),
            'email' => 'fan@fans.test',
            'locale' => 'en',
            'source' => 'event_page',
            'confirmed_at' => now(),
            // Already told about the tickets. If a restore drops this, the next scheduler tick
            // announces months-old news to the whole list.
            'tickets_notified_at' => now()->subMonth(),
            'token' => EventInterest::newToken(),
        ]);

        $svc = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $exported = $data['schedules'][0]['events'][0]['interests'] ?? null;
        $this->assertIsArray($exported, 'the interest list must be exported');
        $this->assertCount(1, $exported);
        $this->assertSame('fan@fans.test', $exported[0]['email']);
        // A token is a capability. A restored copy must not hand out live unsubscribe links minted
        // for a different row, so it is not carried across.
        $this->assertArrayNotHasKey('token', $exported[0]);

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = EventInterest::where('id', '!=', $original->id)->get();
        $this->assertCount(1, $restored, 'the interest list must come back');
        $this->assertSame('fan@fans.test', $restored->first()->email);
        $this->assertNotNull($restored->first()->tickets_notified_at, 'a restore must not re-announce old news');
        $this->assertNotSame($original->token, $restored->first()->token, 'a restore must mint a fresh token');
    }
}
