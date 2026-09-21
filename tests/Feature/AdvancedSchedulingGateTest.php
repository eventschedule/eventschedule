<?php

namespace Tests\Feature;

use App\Models\AppointmentType;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Advanced scheduling is a Pro feature: date overrides, buffers, minimum notice, the booking window
 * and the approval step.
 *
 * Gated on the STORED value in AppointmentTypeController::fill(), the same way
 * EventRepo::saveEvent() gates individual tickets and passes. A free schedule cannot turn one ON;
 * one that already has it keeps it and it keeps working. That clamp-not-wipe contract is the half
 * most likely to regress, because the obvious "just force it to the default" implementation passes
 * the refusal tests and silently breaks every lapsed customer.
 *
 * Deliberately NOT gated, and pinned below so a future tidy-up does not sweep them in: the slot
 * interval and multiple hour ranges per day.
 */
class AdvancedSchedulingGateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The gate short-circuits on selfhost, so every test here is a hosted test.
        config(['app.hosted' => true]);
    }

    private function windows(): array
    {
        return array_fill_keys(range(0, 6), [['start' => '09:00', 'end' => '17:00']]);
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

    private function freeRole(): Role
    {
        return $this->createRole($this->createOwner(), 'talent', [
            'timezone' => 'America/New_York',
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ]);
    }

    public function test_a_free_schedule_cannot_turn_advanced_settings_on(): void
    {
        $role = $this->freeRole();

        $this->actingAs($role->user)->post(route('appointments.store', ['subdomain' => $role->subdomain]),
            $this->payload([
                'buffer_before_minutes' => 15,
                'buffer_after_minutes' => 10,
                'min_notice_hours' => 48,
                'max_advance_days' => 180,
                'requires_approval' => 1,
                'date_overrides' => [now()->addWeek()->format('Y-m-d') => []],
            ]))->assertSessionHasNoErrors();

        $type = AppointmentType::where('role_id', $role->id)->firstOrFail();

        $this->assertSame(0, (int) $type->buffer_before_minutes);
        $this->assertSame(0, (int) $type->buffer_after_minutes);
        $this->assertSame(0, (int) $type->min_notice_hours);
        $this->assertSame(60, (int) $type->max_advance_days, 'falls back to the default window');
        $this->assertFalse((bool) $type->requires_approval);
        $this->assertEmpty($type->date_overrides ?? []);
    }

    public function test_a_pro_schedule_sets_them_freely(): void
    {
        // createRole() defaults to enterprise, which isPro() answers true for.
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);

        $this->actingAs($role->user)->post(route('appointments.store', ['subdomain' => $role->subdomain]),
            $this->payload([
                'buffer_before_minutes' => 15,
                'min_notice_hours' => 48,
                'max_advance_days' => 180,
                'requires_approval' => 1,
            ]))->assertSessionHasNoErrors();

        $type = AppointmentType::where('role_id', $role->id)->firstOrFail();

        $this->assertSame(15, (int) $type->buffer_before_minutes);
        $this->assertSame(48, (int) $type->min_notice_hours);
        $this->assertSame(180, (int) $type->max_advance_days);
        $this->assertTrue((bool) $type->requires_approval);
    }

    /**
     * The contract that matters. A schedule that configured these while Pro, then lapsed, must keep
     * them through an ordinary save of the form - otherwise a 48-hour notice period silently
     * becomes zero and same-day bookings start landing.
     */
    public function test_a_lapsed_schedule_keeps_its_advanced_settings_through_a_save(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'name' => 'Consultation',
            'buffer_before_minutes' => 15,
            'min_notice_hours' => 48,
            'max_advance_days' => 180,
            'requires_approval' => true,
        ]);

        // The plan lapses AFTER the settings were made.
        $role->forceFill([
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ])->save();
        $this->assertFalse($role->fresh()->isPro());

        // An ordinary edit of something else entirely.
        $this->actingAs($role->user)->put(route('appointments.update', [
            'subdomain' => $role->subdomain,
            'hash' => $type->hashedId(),
        ]), $this->payload([
            'name' => 'Consultation renamed',
            'buffer_before_minutes' => 15,
            'min_notice_hours' => 48,
            'max_advance_days' => 180,
            'requires_approval' => 1,
        ]))->assertSessionHasNoErrors();

        $type = $type->fresh();
        $this->assertSame('Consultation renamed', $type->name, 'the edit went through');
        $this->assertSame(15, (int) $type->buffer_before_minutes, 'clamped, not wiped');
        $this->assertSame(48, (int) $type->min_notice_hours);
        $this->assertSame(180, (int) $type->max_advance_days);
        $this->assertTrue((bool) $type->requires_approval);
    }

    /**
     * Turning a setting OFF never needs a plan. A free schedule that inherited a 48-hour notice must
     * be able to drop it, or the gate becomes a trap rather than an upsell.
     */
    public function test_a_free_schedule_may_always_turn_an_advanced_setting_off(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['min_notice_hours' => 48, 'requires_approval' => true]);

        $role->forceFill([
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ])->save();

        $this->actingAs($role->user)->put(route('appointments.update', [
            'subdomain' => $role->subdomain,
            'hash' => $type->hashedId(),
        ]), $this->payload(['min_notice_hours' => 0]))->assertSessionHasNoErrors();

        $type = $type->fresh();
        $this->assertSame(0, (int) $type->min_notice_hours, 'turning it off is always allowed');
        $this->assertFalse((bool) $type->requires_approval, 'an unposted toggle is off, and off is allowed');
    }

    /**
     * Reducing is not turning off, and must also be allowed.
     *
     * The first version of the gate let a value through only when it was EMPTY, so an owner
     * carrying a 48-hour notice could clear it but could not move it to 24: the form reported a
     * successful save and kept 48. A gate that refuses a reduction is a trap, not an upsell, and it
     * is invisible, which is why this is pinned separately from the turn-it-off test above.
     */
    public function test_a_free_schedule_may_reduce_an_advanced_setting_without_clearing_it(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'min_notice_hours' => 48,
            'buffer_before_minutes' => 30,
        ]);

        $role->forceFill([
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ])->save();

        $this->actingAs($role->user)->put(route('appointments.update', [
            'subdomain' => $role->subdomain,
            'hash' => $type->hashedId(),
        ]), $this->payload([
            'min_notice_hours' => 24,
            'buffer_before_minutes' => 10,
        ]))->assertSessionHasNoErrors();

        $type = $type->fresh();
        $this->assertSame(24, (int) $type->min_notice_hours, 'a reduction persists');
        $this->assertSame(10, (int) $type->buffer_before_minutes);
    }

    public function test_a_free_schedule_still_cannot_raise_one_it_has_reduced(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['min_notice_hours' => 24]);

        $role->forceFill([
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ])->save();

        $this->actingAs($role->user)->put(route('appointments.update', [
            'subdomain' => $role->subdomain,
            'hash' => $type->hashedId(),
        ]), $this->payload(['min_notice_hours' => 48]))->assertSessionHasNoErrors();

        $this->assertSame(24, (int) $type->fresh()->min_notice_hours, 'a raise is still refused');
    }

    /**
     * max_advance_days is the field the emptiness test broke completely, because its inert default
     * is 60 rather than 0: a non-empty submission was refused and an empty one fell back to 60, so
     * there was no input at all that set it to a chosen value on a free plan.
     *
     * The window it may reach is bounded at BOTH ends, between the default and what is stored.
     */
    public function test_a_free_schedule_may_shorten_its_booking_window_but_not_extend_it(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['max_advance_days' => 180]);

        $role->forceFill([
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ])->save();

        $update = fn (int $days) => $this->actingAs($role->user)->put(route('appointments.update', [
            'subdomain' => $role->subdomain,
            'hash' => $type->hashedId(),
        ]), $this->payload(['max_advance_days' => $days]))->assertSessionHasNoErrors();

        $update(90);
        $this->assertSame(90, (int) $type->fresh()->max_advance_days, 'shortening toward the default persists');

        $update(365);
        $this->assertSame(90, (int) $type->fresh()->max_advance_days, 'extending past what it had is refused');
    }

    /**
     * The case an interval clamp gets wrong, and the reason the rule is a DISTANCE test.
     *
     * max_advance_days is the only gated field whose inert default (60) is not at an extreme, so it
     * is the only one a submission can approach from either side. Clamping into [default, stored] =
     * [60, 180] turns a request for 30 into 60 - an owner shortening their booking window before
     * going on leave silently gets a longer one than they asked for, with a success message. 30 is
     * closer to the default than 180 is, so it is a reduction and must be allowed.
     */
    public function test_a_free_schedule_may_shorten_its_window_past_the_default(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['max_advance_days' => 180]);

        $role->forceFill([
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ])->save();

        $this->actingAs($role->user)->put(route('appointments.update', [
            'subdomain' => $role->subdomain,
            'hash' => $type->hashedId(),
        ]), $this->payload(['max_advance_days' => 30]))->assertSessionHasNoErrors();

        $this->assertSame(30, (int) $type->fresh()->max_advance_days);
    }

    public function test_a_new_free_type_is_pinned_to_the_default_booking_window(): void
    {
        $role = $this->freeRole();

        $this->actingAs($role->user)->post(route('appointments.store', ['subdomain' => $role->subdomain]),
            $this->payload(['max_advance_days' => 14]))->assertSessionHasNoErrors();

        $type = AppointmentType::where('role_id', $role->id)->firstOrFail();

        // 14 is closer to zero than the default, but the default is where a type with no history
        // sits: there is no stored value to move between, so the window does not move at all.
        $this->assertSame(60, (int) $type->max_advance_days);
    }

    /**
     * Clone is the path that launders the grandfather, because for advanced scheduling the STORED
     * VALUE is the grandfather - there is no stamp column to exclude from replicate().
     *
     * The full exploit: pause the lapsed-Pro type (appointmentTypeCount() counts active types only,
     * so the allowance frees up), Clone it, activate the copy, delete the original. Repeat for an
     * endless supply of fully configured types on a free plan.
     */
    public function test_cloning_on_a_free_plan_does_not_carry_the_advanced_settings_over(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'min_notice_hours' => 48,
            'buffer_before_minutes' => 30,
            'max_advance_days' => 180,
            'requires_approval' => true,
        ]);

        $role->forceFill([
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ])->save();

        // Pause it, which frees the allowance and lets the clone through planLimit().
        $type->forceFill(['is_active' => false])->save();

        $this->actingAs($role->user)->post(route('appointments.duplicate', [
            'subdomain' => $role->subdomain,
            'hash' => $type->hashedId(),
        ]))->assertSessionHasNoErrors();

        $copy = AppointmentType::where('role_id', $role->id)->where('id', '!=', $type->id)->firstOrFail();

        $this->assertSame(0, (int) $copy->min_notice_hours, 'the copy starts from the defaults');
        $this->assertSame(0, (int) $copy->buffer_before_minutes);
        $this->assertSame(60, (int) $copy->max_advance_days);
        $this->assertFalse((bool) $copy->requires_approval);

        // And the original is untouched - this clamps the copy, it does not wipe what was there.
        $this->assertSame(48, (int) $type->fresh()->min_notice_hours);
    }

    public function test_a_pro_schedule_keeps_its_settings_when_cloning(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['min_notice_hours' => 48, 'requires_approval' => true]);

        $this->actingAs($role->user)->post(route('appointments.duplicate', [
            'subdomain' => $role->subdomain,
            'hash' => $type->hashedId(),
        ]))->assertSessionHasNoErrors();

        $copy = AppointmentType::where('role_id', $role->id)->where('id', '!=', $type->id)->firstOrFail();

        $this->assertSame(48, (int) $copy->min_notice_hours, 'Pro may have these, so cloning keeps them');
        $this->assertTrue((bool) $copy->requires_approval);
    }

    /**
     * Activating is the third creation path. Without an allowance check there, pause -> create ->
     * re-activate leaves a free schedule holding two live types.
     */
    public function test_reactivating_a_second_type_is_refused_on_a_free_plan(): void
    {
        $role = $this->freeRole();

        $first = $this->createAppointmentType($role, ['name' => 'One']);
        $second = $this->createAppointmentType($role, ['name' => 'Two', 'is_active' => false]);

        $this->actingAs($role->user)->post(route('appointments.toggle', [
            'subdomain' => $role->subdomain,
            'hash' => $second->hashedId(),
        ]), ['is_active' => 1]);

        $this->assertFalse((bool) $second->fresh()->is_active, 'the second type stays paused');
        $this->assertTrue((bool) $first->fresh()->is_active, 'and the first is untouched');
    }

    /**
     * The ungated half. These two stay free on purpose and are pinned so a later tidy-up does not
     * quietly sweep them into the gate.
     */
    public function test_slot_interval_and_split_shifts_stay_free(): void
    {
        $role = $this->freeRole();

        $split = $this->windows();
        $split[1] = [['start' => '09:00', 'end' => '12:00'], ['start' => '14:00', 'end' => '17:00']];

        $this->actingAs($role->user)->post(route('appointments.store', ['subdomain' => $role->subdomain]),
            $this->payload([
                'slot_interval_minutes' => 15,
                'weekly_windows' => $split,
            ]))->assertSessionHasNoErrors();

        $type = AppointmentType::where('role_id', $role->id)->firstOrFail();

        $this->assertSame(15, (int) $type->slot_interval_minutes);
        $this->assertCount(2, $type->weekly_windows[1], 'two ranges on Monday survive on a free plan');
    }
}
