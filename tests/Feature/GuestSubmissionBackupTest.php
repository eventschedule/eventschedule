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

    /**
     * The submitter's contact details ride along for the same reason, and by the same mechanism:
     * deliberately not fillable, so explicitly exported. A restore that dropped them would leave
     * every pending request unanswerable all over again, which is the whole of issue #124.
     */
    public function test_backup_round_trip_preserves_the_contact_details(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $original = $this->createEvent($role, ['name' => 'Needs An Answer']);
        $original->forceFill([
            'contact_name' => 'Sam Guest',
            'contact_email' => 'sam.guest@gmail.com',
            'contact_phone' => '+49 170 1234567',
        ])->save();

        $data = $this->roundTrip($owner, $role);

        $exported = $data['schedules'][0]['events'][0];
        $this->assertSame('Sam Guest', $exported['contact_name']);
        $this->assertSame('sam.guest@gmail.com', $exported['contact_email']);
        $this->assertSame('+49 170 1234567', $exported['contact_phone']);

        $restored = Event::where('name', 'Needs An Answer')->where('id', '!=', $original->id)->latest('id')->firstOrFail();

        $this->assertSame('Sam Guest', $restored->contact_name);
        $this->assertSame('sam.guest@gmail.com', $restored->contact_email);
        $this->assertSame('+49 170 1234567', $restored->contact_phone);
    }

    /**
     * importEvent() persists with saveQuietly(), so the model's clamping hook never runs and the
     * hand-rolled clamp loop only walks CLAMPED_COLUMNS. A hand-edited archive must not become a
     * MySQL 1406 that fails the whole restore.
     */
    public function test_an_over_long_contact_value_is_clamped_rather_than_failing_the_restore(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $original = $this->createEvent($role, ['name' => 'Very Long Contact']);

        $svc = app(BackupService::class);
        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $data['schedules'][0]['events'][0]['contact_name'] = str_repeat('a', 400);

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Event::where('name', 'Very Long Contact')->where('id', '!=', $original->id)->latest('id')->firstOrFail();

        $this->assertSame(255, strlen($restored->contact_name));
    }
}
