<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Schedule owners can put their custom fields on the public event request forms (issue #109), with
 * an optional validation pattern on text fields. There are three request forms and each reaches the
 * events table by a different route, so each is covered here.
 */
class RequestCustomFieldsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const FIELDS = [
        'gear' => [
            'name' => 'What gear do you need?',
            'type' => 'multiselect',
            'options' => 'projector,dj controller,tablesoccer',
            'show_on_request' => true,
        ],
        'ref' => [
            'name' => 'Booking reference',
            'type' => 'string',
            'regex' => '[A-Z]{3}-[0-9]{4}',
            'regex_hint' => 'Three letters, a dash, four digits',
            'show_on_request' => true,
        ],
        'internal' => [
            'name' => 'Internal note',
            'type' => 'string',
            'show_on_request' => false,
        ],
    ];

    private function bookingVenue(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', array_merge([
            'accept_requests' => true,
            'require_account' => false,
            'require_approval' => true,
            'event_request_form' => 'booking',
            'event_custom_fields' => self::FIELDS,
        ], $attrs));
    }

    private function bookingPayload(array $extra = []): array
    {
        return array_merge([
            'event_name' => 'Gear Test Show',
            'date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '20:00',
            'contact_name' => 'Sam Guest',
            'contact_email' => 'sam@example.com',
        ], $extra);
    }

    // -- Booking form ------------------------------------------------------------------------

    public function test_the_booking_form_shows_request_fields_and_hides_opted_out_ones(): void
    {
        $role = $this->bookingVenue();

        $this->get(route('event.booking_request', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertSee('What gear do you need?')
            ->assertSee('tablesoccer')
            ->assertSee('Three letters, a dash, four digits')
            ->assertDontSee('Internal note');
    }

    public function test_a_booking_request_stores_multiselect_answers(): void
    {
        $role = $this->bookingVenue();

        $this->postJson(
            route('event.booking_request.store', ['subdomain' => $role->subdomain]),
            $this->bookingPayload(['custom_field_values' => ['gear' => ['projector', 'tablesoccer']]])
        )->assertOk();

        $event = Event::where('name', 'Gear Test Show')->firstOrFail();
        $this->assertSame(['gear' => 'projector, tablesoccer'], $event->custom_field_values);
    }

    public function test_a_booking_request_rejects_an_option_that_is_not_on_the_list(): void
    {
        $role = $this->bookingVenue();

        $this->postJson(
            route('event.booking_request.store', ['subdomain' => $role->subdomain]),
            $this->bookingPayload(['custom_field_values' => ['gear' => ['smoke machine']]])
        )->assertStatus(422)->assertJsonValidationErrors('custom_field_values.gear.0');

        $this->assertSame(0, Event::count());
    }

    public function test_a_booking_request_enforces_the_validation_pattern(): void
    {
        $role = $this->bookingVenue();
        $route = route('event.booking_request.store', ['subdomain' => $role->subdomain]);

        $this->postJson($route, $this->bookingPayload(['custom_field_values' => ['ref' => 'nope']]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('custom_field_values.ref');
        $this->assertSame(0, Event::count());

        $this->postJson($route, $this->bookingPayload(['custom_field_values' => ['ref' => 'ABC-1234']]))
            ->assertOk();
        $this->assertSame('ABC-1234', Event::firstOrFail()->custom_field_values['ref']);
    }

    public function test_the_validation_error_names_the_field_rather_than_the_key(): void
    {
        $role = $this->bookingVenue();

        $this->postJson(
            route('event.booking_request.store', ['subdomain' => $role->subdomain]),
            $this->bookingPayload(['custom_field_values' => ['ref' => 'nope']])
        )->assertStatus(422)
            ->assertJsonFragment(['The Booking reference field format is invalid.']);
    }

    public function test_a_required_request_field_is_enforced_server_side(): void
    {
        $fields = self::FIELDS;
        $fields['gear']['required'] = true;
        $role = $this->bookingVenue(['event_custom_fields' => $fields]);

        $this->postJson(
            route('event.booking_request.store', ['subdomain' => $role->subdomain]),
            $this->bookingPayload()
        )->assertStatus(422)->assertJsonValidationErrors('custom_field_values.gear');
    }

    public function test_a_field_kept_off_the_request_form_cannot_be_set_by_posting_it(): void
    {
        $role = $this->bookingVenue();

        $this->postJson(
            route('event.booking_request.store', ['subdomain' => $role->subdomain]),
            $this->bookingPayload(['custom_field_values' => ['internal' => 'injected']])
        )->assertOk();

        $this->assertNull(Event::firstOrFail()->custom_field_values);
    }

    // -- AI import form (no account required) ------------------------------------------------

    public function test_the_ai_import_form_accepts_a_comma_joined_multiselect_string(): void
    {
        // Regression guard: import.blade.php posts a multiselect as one comma-joined string, while
        // the validation rule expects an array. Without normalizing, every such submission 422s.
        $role = $this->createRole($this->createOwner(), 'venue', [
            'accept_requests' => true,
            'require_account' => false,
            'event_custom_fields' => self::FIELDS,
        ]);

        $this->postJson(route('event.guest_import.store', ['subdomain' => $role->subdomain]), [
            'name' => 'Imported Event',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'custom_field_values' => ['gear' => 'projector, tablesoccer'],
        ])->assertOk();

        $event = Event::where('name', 'Imported Event')->firstOrFail();
        $this->assertSame('projector, tablesoccer', $event->custom_field_values['gear']);
    }

    public function test_an_ai_guessed_dropdown_value_is_cleared_instead_of_rejected(): void
    {
        // The guest sees that select rendered blank, so failing them on it would be unfixable.
        $role = $this->createRole($this->createOwner(), 'venue', [
            'accept_requests' => true,
            'require_account' => false,
            'event_custom_fields' => [
                'size' => ['name' => 'Size', 'type' => 'dropdown', 'options' => 'S,M,L'],
            ],
        ]);

        $this->postJson(route('event.guest_import.store', ['subdomain' => $role->subdomain]), [
            'name' => 'Parsed Event',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'custom_field_values' => ['size' => 'XL'],
        ])->assertOk();

        $this->assertNull(Event::where('name', 'Parsed Event')->firstOrFail()->custom_field_values);
    }

    public function test_the_ai_import_form_enforces_the_validation_pattern(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'accept_requests' => true,
            'require_account' => false,
            'event_custom_fields' => self::FIELDS,
        ]);

        $this->postJson(route('event.guest_import.store', ['subdomain' => $role->subdomain]), [
            'name' => 'Bad Reference',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'custom_field_values' => ['ref' => 'nope'],
        ])->assertStatus(422)->assertJsonValidationErrors('custom_field_values.ref');
    }

    // -- Structured guest submit (require_account) -------------------------------------------

    public function test_the_structured_submission_stores_the_curators_field_values(): void
    {
        // The event saves onto the submitter's own talent schedule, so the curator's answers have
        // to be reapplied afterwards or EventRepo whitelists them against the wrong definitions.
        $curator = $this->createCurator($this->createOwner(), [
            'accept_requests' => true,
            'require_account' => true,
            'event_custom_fields' => self::FIELDS,
        ]);

        $this->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), [
            'name' => 'Submitted With Account',
            'starts_at' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'account_mode' => 'register',
            'account_name' => 'Sam Guest',
            // NoFakeEmail blocks @example.com.
            'account_email' => 'sam-submit@eventschedule-test.org',
            'account_password' => 'password1234',
            'schedule_name' => 'Sam Band',
            'custom_field_values' => ['gear' => ['dj controller']],
        ])->assertOk();

        $event = Event::where('name', 'Submitted With Account')->firstOrFail();
        $this->assertSame(['gear' => 'dj controller'], $event->custom_field_values);
        $this->assertTrue($curator->events()->where('events.id', $event->id)->exists());
    }

    // -- Admin surfaces ----------------------------------------------------------------------

    public function test_the_requests_tab_shows_the_answers(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'accept_requests' => true,
            'require_account' => false,
            'require_approval' => true,
            'event_request_form' => 'booking',
            'event_custom_fields' => self::FIELDS,
            'email_verified_at' => now(),
        ]);

        $this->postJson(
            route('event.booking_request.store', ['subdomain' => $role->subdomain]),
            $this->bookingPayload(['custom_field_values' => ['gear' => ['projector', 'tablesoccer']]])
        )->assertOk();

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'requests']))
            ->assertOk()
            ->assertSee('What gear do you need?')
            ->assertSee('projector')
            ->assertSee('tablesoccer');
    }

    public function test_the_requests_empty_state_links_to_the_public_request_page(): void
    {
        // This branch also renders when the schedule email is unverified, and used to reference a
        // route name that does not exist.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['accept_requests' => true, 'email_verified_at' => null]);
        $pending = $this->createEvent($role, ['name' => 'Pending Request']);
        $pending->roles()->updateExistingPivot($role->id, ['is_accepted' => null]);

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'requests']))
            ->assertOk()
            ->assertSee(route('role.request', ['subdomain' => $role->subdomain]));
    }

    // -- Settings round trip -----------------------------------------------------------------

    public function test_the_settings_round_trip_persists_the_new_properties(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $role->subdomain,
            'event_custom_fields_submitted' => '1',
            'event_custom_fields' => [
                'new_1' => ['name' => 'Reference', 'type' => 'string', 'regex' => '[A-Z]{3}', 'regex_hint' => 'Three capitals', 'show_on_request' => '1'],
                'new_2' => ['name' => 'Hidden', 'type' => 'string', 'show_on_request' => '0'],
            ],
        ])->assertSessionHasNoErrors();

        $fields = $role->fresh()->getEventCustomFields();
        $this->assertTrue($fields['new_1']['show_on_request']);
        $this->assertSame('[A-Z]{3}', $fields['new_1']['regex']);
        $this->assertSame('Three capitals', $fields['new_1']['regex_hint']);
        $this->assertFalse($fields['new_2']['show_on_request']);
    }

    public function test_a_pattern_that_does_not_compile_is_rejected(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $role->subdomain,
            'event_custom_fields_submitted' => '1',
            'event_custom_fields' => [
                'new_1' => ['name' => 'Broken', 'type' => 'string', 'regex' => '[unterminated'],
            ],
        ])->assertSessionHasErrors('event_custom_fields.new_1.regex');

        $this->assertEmpty($role->fresh()->getEventCustomFields());
    }

    public function test_a_pattern_is_dropped_when_the_type_no_longer_accepts_one(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $role->subdomain,
            'event_custom_fields_submitted' => '1',
            'event_custom_fields' => [
                'new_1' => ['name' => 'Size', 'type' => 'dropdown', 'options' => 'S,M,L', 'regex' => '[A-Z]{3}', 'regex_hint' => 'stale'],
            ],
        ])->assertSessionHasNoErrors();

        $field = $role->fresh()->getEventCustomFields()['new_1'];
        $this->assertSame('', $field['regex']);
        $this->assertSame('', $field['regex_hint']);
    }

    // -- Guest visibility injection ----------------------------------------------------------

    public function test_a_guest_cannot_inject_event_visibility_over_json(): void
    {
        // The strip at guestImport() used $request->request->remove(), which does not touch the JSON
        // bag that $request->all() actually reads on these endpoints.
        $role = $this->createRole($this->createOwner(), 'venue', [
            'accept_requests' => true,
            'require_account' => false,
        ]);

        $this->postJson(route('event.guest_import.store', ['subdomain' => $role->subdomain]), [
            'name' => 'Sneaky Hidden Event',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'is_private' => true,
            'is_draft' => true,
            'is_internal' => true,
        ])->assertOk();

        $event = Event::where('name', 'Sneaky Hidden Event')->firstOrFail();
        $this->assertFalse((bool) $event->is_private);
        $this->assertFalse((bool) $event->is_internal);
    }
}
