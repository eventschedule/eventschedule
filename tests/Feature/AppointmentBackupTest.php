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

    /**
     * A restore must never be lossy, and on hosted it is the Pro data that is at risk.
     *
     * importRole() always creates a BRAND NEW schedule and ROLE_EXPORT_FIELDS carries no plan
     * columns, so roles.plan_type falls back to its 'free' default - meaning any isPro() gate inside
     * the importers fires on every hosted restore, including a paying customer reloading their own
     * backup. Passes came back as ordinary tickets, promo codes vanished entirely, and every
     * appointment type after the first was dropped, taking its bookings out of the Bookings tab with
     * it (importEvent() only remaps appointment_type_id when the type reached $idMap).
     *
     * The gates live on the read side instead: bookableAppointmentTypes() clamps what a guest may
     * book to the free allowance, AppointmentType::isBookable() refuses a PRICED type the restored
     * schedule may not charge for, PromoCode::isValid() refuses to apply a code,
     * PassBookingService::isBookable() refuses to book a pass. The rows restore intact and light up
     * again on upgrade.
     */
    public function test_a_hosted_restore_keeps_pro_data_the_new_schedule_is_not_yet_paying_for(): void
    {
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);

        $this->createAppointmentType($role, ['name' => 'Consult']);
        $this->createAppointmentType($role, ['name' => 'Follow-up']);

        $event = $this->createEvent($role);
        $this->createTicket($event, ['type' => 'Season Pass', 'price' => 100, 'is_pass' => true]);
        \App\Models\PromoCode::create([
            'event_id' => $event->id,
            'code' => 'EARLYBIRD',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $svc = app(BackupService::class);
        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $newRole = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();
        $this->assertFalse($newRole->isPro(), 'sanity check: the restored schedule really is free');

        $this->assertSame(
            2,
            $newRole->appointmentTypes()->where('is_deleted', false)->count(),
            'both appointment types restore; the allowance clamps what is bookable, not what exists'
        );
        $this->assertCount(
            1,
            $newRole->bookableAppointmentTypes(),
            'the free allowance still clamps the guest-facing list to one type'
        );

        $newEvent = Event::where('creator_role_id', $newRole->id)->whereNull('appointment_type_id')->firstOrFail();
        $this->assertTrue(
            (bool) $newEvent->tickets()->where('type', 'Season Pass')->firstOrFail()->is_pass,
            'a sold pass must not come back as an ordinary ticket'
        );
        $this->assertSame(
            'EARLYBIRD',
            $newEvent->promoCodes()->firstOrFail()->code,
            'promo codes must survive the round trip'
        );
    }

    /**
     * A restore must not hand out the right to charge.
     *
     * The grandfather stamp is what lets an existing priced type keep booking on a free plan, so a
     * backup that carried it would be a way to mint one: importRole() always lands on a brand new
     * free schedule. exportAppointmentTypes() is an explicit include-list that does not carry the
     * column, and importAppointmentType() assigns every column by hand - this pins both.
     */
    public function test_a_restore_does_not_carry_the_paid_grandfather_stamp(): void
    {
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);

        $paid = $this->createAppointmentType($role, ['name' => 'Consult', 'price' => 50, 'currency_code' => 'USD']);
        $paid->forceFill(['paid_grandfathered_at' => now()])->save();

        $svc = app(BackupService::class);
        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $newRole = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();
        $this->assertFalse($newRole->isPro(), 'sanity check: the restored schedule really is free');

        $restored = $newRole->appointmentTypes()->where('price', '>', 0)->firstOrFail();

        $this->assertNull($restored->paid_grandfathered_at, 'the stamp must not ride the backup');
        $this->assertFalse($restored->canTakePayment(), 'so the restored type may not charge');
        $this->assertFalse($restored->isBookable(), 'and it is not offered to guests');
    }
}
