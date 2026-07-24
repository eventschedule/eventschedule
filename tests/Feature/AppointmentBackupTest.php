<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\AppointmentService;
use App\Services\BackupService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class AppointmentBackupTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_backup_round_trip_preserves_appointments(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'name' => 'Consult',
            'requires_approval' => true,
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]),
        ]);

        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = app(AppointmentService::class)->availableSlots($type, $from, 1);
        $slot = $slots['days'][array_key_first($slots['days'])][0]['utc'];
        $sale = app(AppointmentService::class)->book($type, $role, [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'Europe/London',
        ]);

        $svc = app(BackupService::class);
        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $export = $svc->exportSchedules([$role->fresh()], false, $exportJob);
        $data = $export['json'];

        // Export captured the type and the pending pivot state.
        $sched = $data['schedules'][0];
        $this->assertCount(1, $sched['appointment_types']);
        $apptEvent = collect($sched['events'])->firstWhere('_appointment_type_ref_id', $type->id);
        $this->assertNotNull($apptEvent);
        $this->assertNull($apptEvent['_is_accepted_raw']); // pending, not coerced to accepted

        // Import as a fresh schedule.
        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $newRole = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();
        $newType = $newRole->appointmentTypes()->firstOrFail();
        $this->assertSame('Consult', $newType->name);

        $newEvent = Event::where('creator_role_id', $newRole->id)->whereNotNull('appointment_type_id')->firstOrFail();
        $this->assertSame($newType->id, $newEvent->appointment_type_id);
        $this->assertNull($newEvent->roles()->where('roles.id', $newRole->id)->first()->pivot->is_accepted);

        $newSale = Sale::where('event_id', $newEvent->id)->firstOrFail();
        $this->assertSame('Europe/London', $newSale->guest_timezone);
    }
}
