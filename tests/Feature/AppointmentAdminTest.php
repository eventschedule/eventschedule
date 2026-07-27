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

    public function test_toggle_honours_an_explicit_value(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['is_active' => true]);
        $url = route('appointments.toggle', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]);

        // The list posts a switch value, so a repeat submit must not flip the type back on.
        $this->actingAs($owner)->post($url, ['is_active' => 0]);
        $this->assertFalse((bool) $type->fresh()->is_active);
        $this->actingAs($owner)->post($url, ['is_active' => 0]);
        $this->assertFalse((bool) $type->fresh()->is_active);

        $this->actingAs($owner)->post($url, ['is_active' => 1]);
        $this->assertTrue((bool) $type->fresh()->is_active);

        // An empty body still inverts, for any caller that does not send the field.
        $this->actingAs($owner)->post($url);
        $this->assertFalse((bool) $type->fresh()->is_active);
    }

    public function test_duplicate_creates_an_inactive_copy_and_opens_the_editor(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['name' => 'Intro Call', 'slug' => 'intro-call', 'duration_minutes' => 45]);

        $response = $this->actingAs($owner)->post(route('appointments.duplicate', [
            'subdomain' => $role->subdomain, 'hash' => $type->hashedId(),
        ]));

        $copy = AppointmentType::where('role_id', $role->id)->where('id', '!=', $type->id)->firstOrFail();
        $this->assertSame(45, $copy->duration_minutes);
        $this->assertFalse((bool) $copy->is_active);
        $this->assertNotSame($type->slug, $copy->slug);
        // Lands in the editor so the copy gets renamed before it can be booked.
        $response->assertRedirect(route('role.view_admin', [
            'subdomain' => $role->subdomain, 'tab' => 'appointments', 'edit' => $copy->hashedId(),
        ]));
    }

    public function test_a_type_with_no_hours_is_flagged(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $tab = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']);

        // isBookable() never checks the hours, so this type lights up the guest "Book a Time" button
        // and then dead-ends on "No available times". The list has to say so.
        $this->createAppointmentType($role, [
            'name' => 'Empty Week', 'slug' => 'empty-week',
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], []),
        ]);
        $this->actingAs($owner)->get($tab)->assertOk()->assertSee(__('messages.appointments_no_hours_warning'));
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

    public function test_editor_restores_submitted_values_after_validation_error(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $editorUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']).'?new=1';

        // Overlapping Tuesday ranges fail validation. Everything else the owner typed - including the
        // whole weekly-hours build - has to come back, not revert to the stored row or the defaults.
        $this->actingAs($owner)->from($editorUrl)->post(
            route('appointments.store', ['subdomain' => $role->subdomain]),
            $this->payload([
                'weekly_windows' => json_encode([
                    '0' => [], '1' => [],
                    '2' => [['start' => '08:00', 'end' => '12:00'], ['start' => '11:00', 'end' => '13:00']],
                    '3' => [], '4' => [], '5' => [], '6' => [],
                ]),
                'duration_minutes' => 90,
                'location_type' => 'phone',
                'payment_method' => 'stripe',
                'requires_approval' => 1,
                'is_active' => 0,
            ])
        )->assertSessionHasErrors('weekly_windows');

        $html = $this->actingAs($owner)->get($editorUrl)->assertOk()->getContent();

        // 08:00 is only ever pre-selected from restored input - the defaults and the range template
        // both use 09:00, so this asserts the posted windows survived rather than the fallback.
        $this->assertStringContainsString('value="08:00" selected', $html);
        $this->assertStringContainsString('value="13:00" selected', $html);
        $this->assertMatchesRegularExpression('/id="duration_minutes"[^>]*value="90"|value="90"[^>]*id="duration_minutes"/', $html);
        $this->assertStringContainsString('value="phone" selected', $html);
        $this->assertStringContainsString('value="stripe" checked', $html);

        // Toggles: requires_approval was on, is_active was explicitly off.
        $this->assertMatchesRegularExpression('/name="requires_approval" value="1"\s+checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="is_active" value="1"\s+checked/', $html);
    }

    /**
     * The slot engine and the controller have always honoured date_overrides; the editor simply never
     * posted the field. These assert the round trip the new UI relies on.
     */
    public function test_date_overrides_round_trip(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);

        $this->actingAs($owner)->post(route('appointments.store', ['subdomain' => $role->subdomain]), $this->payload([
            'date_overrides' => json_encode([
                '2026-12-25' => [],                                            // closed all day
                '2026-12-31' => [['start' => '09:00', 'end' => '12:00']],       // shortened day
            ]),
        ]));

        $type = AppointmentType::where('role_id', $role->id)->firstOrFail();
        $this->assertSame([], $type->date_overrides['2026-12-25']);
        $this->assertSame('09:00', $type->date_overrides['2026-12-31'][0]['start']);
        $this->assertSame('12:00', $type->date_overrides['2026-12-31'][0]['end']);

        // The editor renders them back so they can be edited or removed.
        $html = $this->actingAs($owner)->get(
            route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']).'?edit='.$type->hashedId()
        )->assertOk()->getContent();
        $this->assertStringContainsString('value="2026-12-25"', $html);
        $this->assertStringContainsString('value="2026-12-31"', $html);

        // Clearing the last row posts "{}" - which must store "no overrides", not leave the old ones.
        $this->actingAs($owner)->put(
            route('appointments.update', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]),
            $this->payload(['date_overrides' => '{}'])
        );
        $this->assertSame([], $type->fresh()->date_overrides);
    }

    public function test_a_blocked_date_removes_its_slots(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]),
        ]);

        $day = \Carbon\Carbon::now('America/New_York')->addDays(2)->format('Y-m-d');
        $service = app(\App\Services\AppointmentService::class);
        $this->assertNotEmpty($service->availableSlots($type, $day, 1)['days'][$day] ?? []);

        $this->actingAs($owner)->put(
            route('appointments.update', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]),
            $this->payload(['date_overrides' => json_encode([$day => []])])
        );

        $this->assertEmpty($service->availableSlots($type->fresh(), $day, 1)['days'][$day] ?? []);
    }

    public function test_bookings_view_separates_pending_from_upcoming(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $confirmedType = $this->createAppointmentType($role, [
            'name' => 'Confirmed Type', 'slug' => 'confirmed-type',
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '12:00']]),
        ]);
        $pendingType = $this->createAppointmentType($role, [
            'name' => 'Pending Type', 'slug' => 'pending-type', 'requires_approval' => true,
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '14:00', 'end' => '17:00']]),
        ]);

        foreach ([$confirmedType, $pendingType] as $type) {
            $from = \Carbon\Carbon::now('America/New_York')->addDay()->format('Y-m-d');
            $slots = app(\App\Services\AppointmentService::class)->availableSlots($type, $from, 1);
            $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
                'name' => 'Guest '.$type->slug,
                'email' => $type->slug.'@gmail.com',
                'slot' => $slots['days'][array_key_first($slots['days'])][0]['utc'],
                'guest_timezone' => 'America/New_York',
            ])->assertOk();
        }

        $tab = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']);

        // A booking awaiting approval belongs on the Pending filter only - counting it under Upcoming
        // too double-counts it against the pending badge and buries the decision it needs.
        $this->actingAs($owner)->get($tab.'?view=bookings&filter=upcoming')
            ->assertOk()
            ->assertSee('Confirmed Type')
            ->assertDontSee('Pending Type');

        $this->actingAs($owner)->get($tab.'?view=bookings&filter=pending')
            ->assertOk()
            ->assertSee('Pending Type')
            ->assertDontSee('Confirmed Type');
    }

    /**
     * The row actions used to re-derive their own eligibility and drifted from the endpoints in BOTH
     * directions. Each case below is a link that either could not work or was wrongly withheld.
     */
    public function test_row_actions_match_what_the_endpoints_actually_allow(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]),
        ]);

        $book = function (string $email) use ($role, $type) {
            $from = \Carbon\Carbon::now('America/New_York')->addDay()->format('Y-m-d');
            $slots = app(\App\Services\AppointmentService::class)->availableSlots($type, $from, 1);
            $slot = collect($slots['days'][array_key_first($slots['days'])])->pluck('utc')->first(
                fn ($utc) => app(\App\Services\AppointmentService::class)->isSlotAvailable($type, $utc)
            );
            $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
                'name' => 'Guest', 'email' => $email, 'slot' => $slot, 'guest_timezone' => 'America/New_York',
            ])->assertOk();

            return \App\Models\Sale::where('email', $email)->firstOrFail();
        };

        $ok = $book('ok@gmail.com');
        $unpaid = $book('unpaid@gmail.com');

        // An abandoned Stripe checkout: the endpoint refuses to move it until it is paid.
        \App\Models\Event::whereKey($unpaid->event_id)->update(['payment_method' => 'stripe']);
        \App\Models\Sale::whereKey($unpaid->id)->update(['status' => 'unpaid', 'payment_method' => 'stripe']);

        $rescheduleUrl = fn ($sale) => route('appointments.booking_reschedule', [
            'subdomain' => $role->subdomain, 'saleHash' => \App\Utils\UrlUtils::encodeId($sale->id),
        ]);
        $tab = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']);

        $html = $this->actingAs($owner)->get($tab.'?view=bookings&filter=upcoming')->assertOk()->getContent();

        // Movable booking: offered.
        $this->assertStringContainsString($rescheduleUrl($ok), $html);
        // Unpaid card hold: NOT offered, because the POST would bounce straight back with an error.
        $this->assertStringNotContainsString($rescheduleUrl($unpaid), $html);

        // Past bookings must not offer Cancel either - bookingCancel() rejects them.
        \App\Models\Event::whereKey($ok->event_id)->update([
            'starts_at' => now('UTC')->subDay()->format('Y-m-d H:i:s'),
        ]);
        $pastHtml = $this->actingAs($owner)->get($tab.'?view=bookings&filter=past')->assertOk()->getContent();
        $cancelUrl = route('appointments.booking_cancel', [
            'subdomain' => $role->subdomain, 'saleHash' => \App\Utils\UrlUtils::encodeId($ok->id),
        ]);
        $this->assertStringNotContainsString($cancelUrl, $pastHtml);
        $this->assertStringNotContainsString($rescheduleUrl($ok), $pastHtml);
    }

    /** A pending row can be given a different time - the endpoint allows it and the guest page offers it. */
    public function test_a_pending_row_offers_a_time_change(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'requires_approval' => true,
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]),
        ]);

        $from = \Carbon\Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = app(\App\Services\AppointmentService::class)->availableSlots($type, $from, 1);
        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Guest', 'email' => 'pending@gmail.com',
            'slot' => $slots['days'][array_key_first($slots['days'])][0]['utc'],
            'guest_timezone' => 'America/New_York',
        ])->assertOk();

        $sale = \App\Models\Sale::where('email', 'pending@gmail.com')->firstOrFail();
        $tab = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']);

        $this->actingAs($owner)->get($tab.'?view=bookings&filter=pending')
            ->assertOk()
            ->assertSee(route('appointments.booking_reschedule', [
                'subdomain' => $role->subdomain, 'saleHash' => \App\Utils\UrlUtils::encodeId($sale->id),
            ]), false)
            // Still leads with the decision.
            ->assertSee(__('messages.accept'))
            ->assertSee(__('messages.decline'));
    }

    public function test_bookings_can_be_searched_by_guest(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]),
        ]);

        $service = app(\App\Services\AppointmentService::class);
        $from = \Carbon\Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = collect($service->availableSlots($type, $from, 1)['days'][$from] ?? [])->pluck('utc')->values();

        foreach ([['Ada Lovelace', 'ada@gmail.com'], ['Grace Hopper', 'grace@gmail.com']] as $i => [$name, $email]) {
            $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
                'name' => $name, 'email' => $email, 'slot' => $slots[$i], 'guest_timezone' => 'America/New_York',
            ])->assertOk();
        }

        $tab = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']);
        $this->actingAs($owner)->get($tab.'?view=bookings&filter=upcoming&search=grace')
            ->assertOk()
            ->assertSee('Grace Hopper')
            ->assertDontSee('Ada Lovelace');
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
