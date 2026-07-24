<?php

namespace Tests\Feature;

use App\Models\AppointmentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class AppointmentAdminTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function windows(array $mondayRanges = [['start' => '09:00', 'end' => '17:00']]): string
    {
        return json_encode(['0' => [], '1' => $mondayRanges, '2' => [], '3' => [], '4' => [], '5' => [], '6' => []]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Intro Call',
            'duration_minutes' => 30,
            'location_type' => 'in_person',
            'price' => 0,
            'weekly_windows' => $this->windows(),
            'is_active' => 1,
        ], $overrides);
    }

    public function test_tab_renders_for_owner(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']))
            ->assertOk()
            ->assertSee(__('messages.appointments_empty_title'));
    }

    public function test_store_creates_type(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);

        $this->actingAs($owner)->post(route('appointments.store', ['subdomain' => $role->subdomain]), $this->payload());

        $type = AppointmentType::where('role_id', $role->id)->firstOrFail();
        $this->assertSame('Intro Call', $type->name);
        $this->assertSame('intro-call', $type->slug);
        $this->assertSame(30, $type->duration_minutes);
        $this->assertCount(1, $type->weekly_windows['1']);
        $this->assertSame('09:00', $type->weekly_windows['1'][0]['start']);
        $this->assertSame('17:00', $type->weekly_windows['1'][0]['end']);
        $this->assertSame([], $type->weekly_windows['0']);
        $this->assertTrue($type->is_active);
    }

    public function test_update_edits_type(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['name' => 'Old']);

        $this->actingAs($owner)->put(route('appointments.update', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]),
            $this->payload(['name' => 'New Name', 'duration_minutes' => 45]));

        $type->refresh();
        $this->assertSame('New Name', $type->name);
        $this->assertSame(45, $type->duration_minutes);
    }

    public function test_toggle_and_soft_delete(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role);

        $this->actingAs($owner)->post(route('appointments.toggle', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]));
        $this->assertFalse($type->fresh()->is_active);

        $this->actingAs($owner)->delete(route('appointments.destroy', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]));
        $this->assertTrue($type->fresh()->is_deleted);
    }

    public function test_overlapping_ranges_are_rejected(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);

        $this->actingAs($owner)->post(route('appointments.store', ['subdomain' => $role->subdomain]),
            $this->payload(['weekly_windows' => $this->windows([['start' => '09:00', 'end' => '12:00'], ['start' => '11:00', 'end' => '13:00']])]))
            ->assertSessionHasErrors('weekly_windows');

        $this->assertSame(0, AppointmentType::where('role_id', $role->id)->count());
    }

    public function test_paid_type_requires_currency(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);

        $this->actingAs($owner)->post(route('appointments.store', ['subdomain' => $role->subdomain]),
            $this->payload(['price' => 50, 'currency_code' => '', 'payment_method' => 'cash']))
            ->assertSessionHasErrors('currency_code');
    }

    public function test_non_pro_hosted_is_gated(): void
    {
        config(['app.hosted' => true]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'timezone' => 'America/New_York',
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->actingAs($owner)->post(route('appointments.store', ['subdomain' => $role->subdomain]), $this->payload())
            ->assertRedirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']));

        $this->assertSame(0, AppointmentType::where('role_id', $role->id)->count());
    }
}
