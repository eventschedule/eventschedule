<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The booking form options on Engagement > Requests (issue #124): which default fields a visitor
 * must fill in, and whether the form offers Online. Stored in roles.booking_form_config, written only
 * by RoleController::applyBookingFormConfig().
 */
class BookingFormSettingsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function payload(Role $role, array $overrides = []): array
    {
        return array_merge([
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'language_code' => 'en',
            'new_subdomain' => $role->subdomain,
        ], $overrides);
    }

    /**
     * What the section posts: a sentinel, a hidden 0 per row (overridden by the ticked box) and the
     * toggle's value.
     */
    private function section(array $ticked, bool $allowOnline = true, array $rows = Role::BOOKING_FORM_REQUIRABLE_FIELDS, bool $askPhone = false): array
    {
        $fields = [];
        foreach ($rows as $field) {
            $fields[$field] = in_array($field, $ticked, true) ? '1' : '0';
        }

        return [
            'booking_form_submitted' => '1',
            'booking_required_fields' => $fields,
            'booking_allow_online' => $allowOnline ? '1' : '0',
            'booking_ask_phone' => $askPhone ? '1' : '0',
        ];
    }

    private function save(Role $role, array $data)
    {
        return $this->actingAs($role->user)
            ->put(route('role.update', ['subdomain' => $role->subdomain]), $this->payload($role, $data))
            ->assertSessionHasNoErrors();
    }

    public function test_saving_stores_the_required_fields_and_the_online_switch(): void
    {
        $role = $this->createRole($this->createOwner(), 'curator');

        $this->save($role, $this->section(['event_name', 'description', 'location'], allowOnline: false));

        $role->refresh();
        $this->assertTrue($role->bookingFormRequires('event_name'));
        $this->assertFalse($role->bookingFormRequires('date_time'));
        $this->assertTrue($role->bookingFormRequires('description'));
        $this->assertTrue($role->bookingFormRequires('location'));
        $this->assertFalse($role->bookingFormAllowsOnline());
    }

    public function test_a_save_without_the_section_leaves_the_options_alone(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', [
            'booking_form_config' => ['required_fields' => ['description' => true], 'allow_online' => false],
        ]);

        // Stray rows without the sentinel, as from a page that does not render the section.
        $this->save($role, [
            'booking_required_fields' => ['description' => '0', 'event_name' => '1'],
            'booking_allow_online' => '1',
        ]);

        $role->refresh();
        $this->assertTrue($role->bookingFormRequires('description'));
        $this->assertFalse($role->bookingFormRequires('event_name'));
        $this->assertFalse($role->bookingFormAllowsOnline());
    }

    public function test_unticking_every_row_clears_every_requirement(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', [
            'booking_form_config' => ['required_fields' => array_fill_keys(Role::BOOKING_FORM_REQUIRABLE_FIELDS, true)],
        ]);

        $this->save($role, $this->section([]));

        $this->assertSame(
            array_fill_keys(Role::BOOKING_FORM_REQUIRABLE_FIELDS, false),
            $role->fresh()->bookingFormConfig()['required_fields']
        );
    }

    public function test_the_column_cannot_be_mass_assigned_and_unknown_keys_are_dropped(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');

        $this->save($role, ['booking_form_config' => ['required_fields' => ['event_name' => true]]]);
        $this->assertNull($role->fresh()->getRawOriginal('booking_form_config'));

        $this->save($role, array_replace_recursive($this->section(['description']), [
            'booking_required_fields' => ['ticket_price' => '1', 'is_admin' => '1'],
        ]));

        // Canonicalized: MySQL reorders the keys of a stored JSON object.
        $stored = json_decode($role->fresh()->getRawOriginal('booking_form_config'), true);
        $this->assertEqualsCanonicalizing(['required_fields', 'allow_online', 'ask_phone'], array_keys($stored));
        $this->assertEqualsCanonicalizing(Role::BOOKING_FORM_REQUIRABLE_FIELDS, array_keys($stored['required_fields']));
        $this->assertTrue($stored['required_fields']['description']);
    }

    public function test_location_can_never_be_required_on_a_venue(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue');

        // The venue page has no Location row, and a crafted one is ignored.
        $this->save($venue, array_replace_recursive(
            $this->section(['event_name'], rows: ['event_name', 'date_time', 'description']),
            ['booking_required_fields' => ['location' => '1']]
        ));

        $venue->refresh();
        $this->assertTrue($venue->bookingFormRequires('event_name'));
        $this->assertFalse($venue->bookingFormConfig()['required_fields']['location']);
        $this->assertFalse($venue->bookingFormRequires('location'));
    }

    public function test_a_row_the_page_did_not_render_keeps_its_stored_value(): void
    {
        $curator = $this->createRole($this->createOwner(), 'curator', [
            'booking_form_config' => ['required_fields' => ['location' => true]],
        ]);

        $this->save($curator, $this->section(['description'], rows: ['event_name', 'date_time', 'description']));

        $curator->refresh();
        $this->assertTrue($curator->bookingFormRequires('description'));
        $this->assertTrue($curator->bookingFormRequires('location'));
    }

    public function test_creating_a_schedule_saves_the_options(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner)->post(route('role.store'), array_merge([
            'type' => 'talent',
            'name' => 'Brand New Act',
            'email' => 'brand.new.act@gmail.com',
            'timezone' => 'America/New_York',
            'language_code' => 'en',
        ], $this->section(['date_time'], allowOnline: false)))->assertSessionHasNoErrors();

        $role = Role::where('name', 'Brand New Act')->firstOrFail();
        $this->assertTrue($role->bookingFormRequires('date_time'));
        $this->assertFalse($role->bookingFormAllowsOnline());
    }

    public function test_creating_a_schedule_without_the_section_keeps_the_defaults(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner)->post(route('role.store'), [
            'type' => 'venue',
            'name' => 'Plain Venue',
            'email' => 'plain.venue@gmail.com',
            'timezone' => 'America/New_York',
            'language_code' => 'en',
        ])->assertSessionHasNoErrors();

        $role = Role::where('name', 'Plain Venue')->firstOrFail();
        $this->assertNull($role->getRawOriginal('booking_form_config'));
        $this->assertTrue($role->bookingFormAllowsOnline());
        $this->assertFalse(in_array(true, $role->bookingFormRequiredFields(), true));
    }

    public function test_a_malformed_stored_value_falls_back_to_the_defaults(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');

        foreach (['"a string"', '[1, 2, 3]', '{"required_fields": "yes", "allow_online": "no"}'] as $raw) {
            DB::table('roles')->where('id', $role->id)->update(['booking_form_config' => $raw]);
            $fresh = $role->fresh();

            $this->assertSame(array_fill_keys(Role::BOOKING_FORM_REQUIRABLE_FIELDS, false), $fresh->bookingFormConfig()['required_fields'], $raw);
        }

        // A readable "no" is honoured, the rest of the value is not.
        $this->assertFalse($role->fresh()->bookingFormAllowsOnline());
    }

    public function test_a_talent_never_requires_an_account_on_its_booking_form(): void
    {
        $owner = $this->createOwner();

        $this->assertFalse($this->createRole($owner, 'talent', ['require_account' => true])->bookingFormRequiresAccount());
        $this->assertTrue($this->createRole($owner, 'venue', ['require_account' => true])->bookingFormRequiresAccount());
        $this->assertTrue($this->createRole($owner, 'curator', ['require_account' => true])->bookingFormRequiresAccount());
        $this->assertFalse($this->createRole($owner, 'curator', ['require_account' => false])->bookingFormRequiresAccount());
    }

    // -- The settings page -----------------------------------------------------------------------

    public static function scheduleTypes(): array
    {
        return [
            'talent' => ['talent', true],
            'venue' => ['venue', false],
            'curator' => ['curator', true],
        ];
    }

    #[DataProvider('scheduleTypes')]
    public function test_the_settings_page_renders_the_section(string $type, bool $hasLocationRow): void
    {
        $role = $this->createRole($this->createOwner(), $type);

        $html = $this->actingAs($role->user)
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="booking_form_section"', $html);
        $this->assertStringContainsString('data-always-booking="'.($type === 'talent' ? '1' : '0').'"', $html);
        $this->assertStringContainsString('name="booking_form_submitted" value="1"', $html);
        $this->assertStringContainsString('name="booking_allow_online"', $html);

        foreach (['event_name', 'date_time', 'description'] as $field) {
            $this->assertStringContainsString('name="booking_required_fields['.$field.']"', $html);
        }
        $this->assertSame($hasLocationRow, str_contains($html, 'name="booking_required_fields[location]"'));
    }

    public function test_the_settings_page_reflects_the_stored_options(): void
    {
        $role = $this->createRole($this->createOwner(), 'curator', [
            'booking_form_config' => ['required_fields' => ['description' => true], 'allow_online' => false],
        ]);

        $html = $this->actingAs($role->user)
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('~id="booking_required_description"[^>]*\bchecked\b~', $html);
        $this->assertDoesNotMatchRegularExpression('~id="booking_required_event_name"[^>]*\bchecked\b~', $html);
        $this->assertDoesNotMatchRegularExpression('~name="booking_allow_online"[^>]*value="1"[^>]*\bchecked\b~s', $html);
    }

    /**
     * A toggle rendered off still posts its hidden 0, and store() would save that over the default.
     */
    public function test_a_new_schedule_starts_with_online_on(): void
    {
        // The saved row, not the rendered toggle: /new/{type} is a first-run form now and carries
        // no booking controls at all.
        //
        // Both halves matter. applyBookingFormConfig() early-returns unless the request carries the
        // booking_form_submitted SENTINEL, so a form that omits the booking fields entirely leaves
        // the config untouched and normalizeBookingFormConfig() supplies allow_online => true. The
        // failure mode this guards is therefore a first-run form that starts posting the sentinel
        // without the online field beside it, which would write a hard false over that default -
        // so the absence of the sentinel is asserted too, not just the resulting value.
        $user = $this->createOwner();
        $this->actingAs($user);

        $html = $this->get(route('new', ['type' => 'venue']))->assertOk()->getContent();
        $this->assertStringNotContainsString('booking_form_submitted', $html,
            'the first-run form posts the booking sentinel, so it must carry the online field too');

        $role = $this->submitNewScheduleForm('venue', ['name' => 'Online Venue']);

        $this->assertTrue($role->bookingFormAllowsOnline());
    }

    // -- The phone field ------------------------------------------------------------------------

    public function test_a_new_schedule_does_not_ask_for_a_phone(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');

        $this->assertFalse($role->bookingFormAsksPhone());
        $this->assertFalse($role->bookingFormRequires('phone'));
    }

    public function test_the_ask_and_require_toggles_round_trip(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');

        $this->save($role, $this->section(['phone'], askPhone: true));

        $role = $role->fresh();
        $this->assertTrue($role->bookingFormAsksPhone());
        $this->assertTrue($role->bookingFormRequires('phone'));

        $this->save($role, $this->section([], askPhone: false));

        $role = $role->fresh();
        $this->assertFalse($role->bookingFormAsksPhone());
        $this->assertFalse($role->bookingFormRequires('phone'));
    }

    /**
     * The nested row is hidden, not removed, while the ask toggle is off, so a stored requirement
     * survives being switched off and on again. bookingFormRequires() keeps it inert meanwhile.
     */
    public function test_a_stored_phone_requirement_survives_the_ask_toggle(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');

        $this->save($role, $this->section(['phone'], askPhone: true));
        $this->save($role->fresh(), $this->section(['phone'], askPhone: false));

        $role = $role->fresh();
        $stored = json_decode($role->getRawOriginal('booking_form_config'), true);
        $this->assertTrue($stored['required_fields']['phone']);
        $this->assertFalse($role->bookingFormRequires('phone'));

        $this->save($role, $this->section(['phone'], askPhone: true));
        $this->assertTrue($role->fresh()->bookingFormRequires('phone'));
    }

    public function test_a_save_without_the_section_leaves_the_phone_options_alone(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');
        $this->save($role, $this->section(['phone'], askPhone: true));

        $this->save($role->fresh(), []);

        $role = $role->fresh();
        $this->assertTrue($role->bookingFormAsksPhone());
        $this->assertTrue($role->bookingFormRequires('phone'));
    }

    public function test_a_malformed_stored_value_does_not_ask_for_a_phone(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');
        DB::table('roles')->where('id', $role->id)->update(['booking_form_config' => '"nonsense"']);

        $this->assertFalse($role->fresh()->bookingFormAsksPhone());
    }

    public function test_the_settings_page_renders_the_phone_block(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');

        $this->actingAs($role->user)
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertSee('booking_ask_phone', false)
            ->assertSee('booking_required_fields[phone]', false)
            ->assertSee('booking-require-phone-row', false);
    }
}
