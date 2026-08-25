<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Event;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * events.is_guest_submission has to survive a backup round trip.
 *
 * The column marks a user_id that is a stand-in for an anonymous submitter, so
 * EventController::decline() knows not to mail that person about a request they never made. It is
 * deliberately not fillable, and BackupService::exportEvent() iterates getFillable() - so without
 * an explicit export it is dropped and comes back false, which is the value that SENDS the mail.
 * That would quietly re-create the bug the column exists to prevent, for every restored schedule.
 */
class GuestSubmissionBackupTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function roundTrip($owner, $role): array
    {
        $svc = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        return $data;
    }

    public function test_backup_round_trip_preserves_the_guest_submission_flag(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $original = $this->createEvent($role, ['name' => 'Stranger Submitted', 'is_guest_submission' => true]);

        $data = $this->roundTrip($owner, $role);

        $this->assertArrayHasKey('is_guest_submission', $data['schedules'][0]['events'][0]);
        $this->assertTrue((bool) $data['schedules'][0]['events'][0]['is_guest_submission']);

        $restored = Event::where('name', 'Stranger Submitted')->where('id', '!=', $original->id)->latest('id')->firstOrFail();

        $this->assertTrue(
            (bool) $restored->is_guest_submission,
            'A restored guest submission must not start mailing the stand-in owner about a request they never made.'
        );
    }

    public function test_a_real_submission_stays_unflagged_through_a_round_trip(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $original = $this->createEvent($role, ['name' => 'Really Submitted']);

        $this->assertFalse((bool) $original->is_guest_submission);

        $this->roundTrip($owner, $role);

        $restored = Event::where('name', 'Really Submitted')->where('id', '!=', $original->id)->latest('id')->firstOrFail();

        // The other direction matters too: over-flagging would silence mail somebody should get.
        $this->assertFalse((bool) $restored->is_guest_submission);
    }
}
