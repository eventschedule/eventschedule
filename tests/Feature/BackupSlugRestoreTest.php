<?php

namespace Tests\Feature;

use App\Models\AppointmentType;
use App\Models\BackupJob;
use App\Models\Role;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Restore used to generate identifiers with a bare Str::slug(), bypassing the two helpers
 * written to stop exactly that - Role::cleanSubdomain() and the appointment-type slug
 * generator. Str::slug returns "" for Hebrew and CJK, and `$data['x'] ?? Str::slug(...)`
 * does not even reach the fallback when the exported value is already "".
 */
class BackupSlugRestoreTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function roundTrip(Role $role, ?callable $mutate = null): Role
    {
        $backup = app(BackupService::class);
        $owner = $role->user;

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $backup->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        if ($mutate) {
            $data = $mutate($data);
        }

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $backup->importSchedules($data, [0], $owner->id, $importJob);

        return Role::where('user_id', $owner->id)
            ->where('id', '!=', $role->id)
            ->latest('id')
            ->firstOrFail();
    }

    public function test_a_restored_schedule_always_has_a_reachable_subdomain(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'מועדון הבלוז']);

        // What a backup of an affected schedule carries, and what a hand-written one omits.
        $restored = $this->roundTrip($role, function ($data) {
            $data['schedules'][0]['role']['subdomain'] = '';

            return $data;
        });

        $this->assertNotSame('', $restored->subdomain);
        $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $restored->subdomain);
    }

    public function test_two_restored_non_latin_appointment_types_get_distinct_booking_slugs(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'יועץ']);

        $consult = $this->createAppointmentType($role, ['name' => 'ייעוץ ראשוני', 'duration_minutes' => 30]);
        $followUp = $this->createAppointmentType($role, ['name' => 'פגישת המשך', 'duration_minutes' => 60]);

        // Simulate the pre-fix data: both stored with an empty slug.
        DB::table('appointment_types')->whereIn('id', [$consult->id, $followUp->id])->update(['slug' => '']);

        $restored = $this->roundTrip($role);
        $types = $restored->appointmentTypes()->orderBy('id')->get();

        $this->assertCount(2, $types);

        foreach ($types as $type) {
            $this->assertNotSame('', (string) $type->slug, "{$type->name} has no booking slug");
        }

        $this->assertNotSame($types[0]->slug, $types[1]->slug,
            'two types sharing a slug means firstWhere() books the guest onto the wrong one');

        // The column has no unique index, so prove resolution actually distinguishes them.
        foreach ($types as $type) {
            $resolved = $restored->appointmentTypes()->where('slug', $type->slug)->get();
            $this->assertCount(1, $resolved);
            $this->assertSame($type->duration_minutes, $resolved->first()->duration_minutes);
        }
    }

    public function test_the_shared_helper_keeps_latin_booking_slugs_unchanged(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->assertSame('strategy-call', AppointmentType::uniqueSlug($role, 'Strategy Call'));

        $this->createAppointmentType($role, ['name' => 'Strategy Call', 'slug' => 'strategy-call']);
        $this->assertSame('strategy-call-2', AppointmentType::uniqueSlug($role, 'Strategy Call'));
    }

    /**
     * Asserts properties, not the exact romanization - ICU's Any-Latin output can differ
     * between library versions, so pinning the literal string would fail on another machine.
     */
    public function test_a_non_latin_booking_slug_is_derived_from_the_name_not_generic(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $first = AppointmentType::uniqueSlug($role, 'ייעוץ ראשוני');
        $this->createAppointmentType($role, ['name' => 'ייעוץ ראשוני', 'slug' => $first]);
        $second = AppointmentType::uniqueSlug($role, 'פגישת המשך');

        foreach ([$first, $second] as $slug) {
            $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $slug);
            // Used to be 'appointment', 'appointment-2', ... for every Hebrew type.
            $this->assertStringStartsNotWith('appointment', $slug);
        }

        $this->assertNotSame($first, $second, 'different names must not romanize to the same slug');
    }
}
