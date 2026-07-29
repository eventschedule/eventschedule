<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Role;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Stay22 columns have to survive a backup round trip.
 *
 * BackupService::ROLE_EXPORT_FIELDS is an explicit allowlist, so a new column is silently
 * dropped on export and comes back as its default on import. Here the consequence is sharper
 * than for promotions_opt_out: losing stay22_aid leaves the accommodation map switched on
 * while the commission silently falls back to the instance operator, which is exactly the
 * outcome the disclosure on the settings page exists to prevent.
 */
class Stay22BackupTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_backup_round_trip_preserves_the_toggle_and_the_affiliate_id(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'stay22_enabled' => true,
            'stay22_aid' => 'owner-aid',
        ]);

        $svc = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $this->assertArrayHasKey('stay22_enabled', $data['schedules'][0]['role']);
        $this->assertArrayHasKey('stay22_aid', $data['schedules'][0]['role']);
        $this->assertSame('owner-aid', $data['schedules'][0]['role']['stay22_aid']);

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();

        $this->assertTrue((bool) $restored->stay22_enabled);
        $this->assertSame(
            'owner-aid',
            $restored->stay22_aid,
            'Losing the affiliate ID on restore silently redirects the owner\'s commission to the operator.'
        );
    }

    public function test_the_defaults_survive_too(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->assertFalse((bool) $role->stay22_enabled);
        $this->assertNull($role->stay22_aid);

        $svc = app(BackupService::class);
        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();

        $this->assertFalse((bool) $restored->stay22_enabled);
        $this->assertNull($restored->stay22_aid);
    }
}
