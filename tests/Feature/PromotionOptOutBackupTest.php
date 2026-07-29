<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Role;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * roles.promotions_opt_out has to survive a backup round trip.
 *
 * BackupService::ROLE_EXPORT_FIELDS is an explicit allowlist, so a new column is silently
 * dropped on export and comes back as its default on import. For this particular column the
 * default is "yes, carry other schedules' promotions" - so forgetting the allowlist entry
 * would quietly opt a schedule back into something it had deliberately turned off.
 */
class PromotionOptOutBackupTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_backup_round_trip_preserves_the_promotions_opt_out(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['promotions_opt_out' => true]);

        $svc = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $this->assertArrayHasKey('promotions_opt_out', $data['schedules'][0]['role']);
        $this->assertTrue((bool) $data['schedules'][0]['role']['promotions_opt_out']);

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();

        $this->assertTrue((bool) $restored->promotions_opt_out, 'A schedule that declined promotions must not silently start carrying them again after a restore.');
    }

    public function test_the_default_survives_too(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->assertFalse((bool) $role->promotions_opt_out);

        $svc = app(BackupService::class);
        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();

        $this->assertFalse((bool) $restored->promotions_opt_out);
    }
}
