<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\Role;
use App\Services\BackupService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Filtering a schedule's calendar by an event custom field ("what happens in Room A").
 *
 * Two halves are pinned here. The "Show as filter" flag the owner sets per field, and WHICH
 * schedule's field definitions an event's values are read against. The second is the subtle one:
 * custom_field_values is written by whichever schedule's form saved it - not necessarily the
 * creator - and field keys collide across schedules (every schedule's first field is new_0), so
 * reading the values against the wrong definitions both hides a schedule's own values and can
 * present another schedule's private answer as public.
 */
class CustomFieldFilterTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    private const ROOM_FIELD = ['new_0' => ['name' => 'Room', 'type' => 'string', 'filter' => true, 'index' => 1]];

    private function putCustomFields(Role $role, array $fields)
    {
        return $this->actingAs($role->user)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $role->subdomain,
            'event_custom_fields_submitted' => '1',
            'event_custom_fields' => $fields,
        ]);
    }

    private function calendarRow(Role $role, Event $event): array
    {
        $payload = $this->getJson(route('role.calendar_events', [
            'subdomain' => $role->subdomain,
            'year' => $event->starts_at ? (int) substr($event->starts_at, 0, 4) : now()->year,
            'month' => $event->starts_at ? (int) substr($event->starts_at, 5, 2) : now()->month,
        ]))->assertOk()->json();

        foreach ($payload['events'] ?? [] as $row) {
            if (($row['name'] ?? null) === $event->name) {
                return $row;
            }
        }

        $this->fail("{$event->name} is not in the {$role->subdomain} calendar payload");
    }

    // -- The flag ----------------------------------------------------------------------------

    public function test_the_schedule_form_saves_the_filter_flag(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        $this->putCustomFields($role, [
            // The editor posts a hidden 0 followed by the checkbox's 1, so a checked box arrives as 1.
            'new_1' => ['name' => 'Room', 'type' => 'string', 'filter' => '1'],
            'new_2' => ['name' => 'Size', 'type' => 'dropdown', 'options' => 'S,M', 'filter' => '0'],
            'new_3' => ['name' => 'Accessible', 'type' => 'switch', 'filter' => '1'],
            // An API-style post with no flag gets the type's default.
            'new_4' => ['name' => 'Genre', 'type' => 'dropdown', 'options' => 'Jazz,Rock'],
            'new_5' => ['name' => 'Notes', 'type' => 'string'],
        ])->assertSessionHasNoErrors();

        $fields = $role->fresh()->getEventCustomFields();
        $this->assertTrue($fields['new_1']['filter'], 'a text field the owner opted in');
        $this->assertFalse($fields['new_2']['filter'], 'a dropdown the owner opted out');
        $this->assertFalse($fields['new_3']['filter'], 'a switch cannot filter, whatever was posted');
        $this->assertTrue($fields['new_4']['filter'], 'a dropdown with no flag keeps being a filter');
        $this->assertFalse($fields['new_5']['filter'], 'a text field with no flag stays off');
    }

    public function test_the_editor_renders_the_saved_choice_as_touched(): void
    {
        // data-touched stops the type-change default from overriding a choice the owner made.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['event_custom_fields' => [
            'saved' => ['name' => 'Saved', 'type' => 'dropdown', 'options' => 'A,B', 'filter' => false],
            'legacy' => ['name' => 'Legacy', 'type' => 'dropdown', 'options' => 'A,B'],
            // Every save stores the flag, so a stored value equal to the type's default is not a
            // choice: it must keep following the type, or no field would after its first save.
            'defaulted' => ['name' => 'Defaulted', 'type' => 'dropdown', 'options' => 'A,B', 'filter' => true],
        ]]);

        $html = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        // Saved as off: marked touched, and not checked.
        $this->assertMatchesRegularExpression('/id="event_field_filter_saved"\s+data-action="custom-field-filter-toggle"\s+data-touched="1"\s+value="1"\s+class=/', $html);
        // A field saved before the flag existed follows its type until the owner clicks it.
        $this->assertMatchesRegularExpression('/id="event_field_filter_legacy"\s+data-action="custom-field-filter-toggle"\s+value="1"\s+checked/', $html);
        $this->assertMatchesRegularExpression('/id="event_field_filter_defaulted"\s+data-action="custom-field-filter-toggle"\s+value="1"\s+checked/', $html);
    }

    // -- Whose definitions the values are keyed by -------------------------------------------

    public function test_saving_an_event_records_the_schedule_whose_fields_it_showed(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['event_custom_fields' => self::ROOM_FIELD]);

        $this->postCreateEvent($owner, $venue, [
            'custom_field_values' => ['new_0' => 'Room A'],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $event = $this->latestEvent();
        $this->assertSame(['new_0' => 'Room A'], $event->custom_field_values);
        $this->assertSame($venue->id, $event->custom_field_values_role_id);
    }

    public function test_a_venue_that_sets_the_room_on_a_talent_event_sees_it_and_the_talent_does_not(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent', ['event_custom_fields' => [
            // Same key as the venue's Room field, as every schedule's first field is.
            'new_0' => ['name' => 'Genre', 'type' => 'string', 'filter' => true, 'index' => 1],
        ]]);
        $venue = $this->createRole($this->createOwner(), 'venue', ['event_custom_fields' => self::ROOM_FIELD]);

        $event = $this->createEvent($talent, ['name' => 'Talent Show', 'creator_role_id' => $talent->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->putUpdateEvent($venue->user, $venue, $event, [
            'name' => 'Talent Show',
            'starts_at' => now()->addDays(7)->format('Y-m-d').' 20:00:00',
            'custom_field_values' => ['new_0' => 'Room A'],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame($venue->id, $event->custom_field_values_role_id);
        $this->assertSame(['new_0' => 'Room A'], $event->publicCustomFieldValuesFor($venue));
        $this->assertSame([], $event->publicCustomFieldValuesFor($talent), "the venue's room is not the talent's genre");

        $this->assertSame(['new_0' => 'Room A'], $this->calendarRow($venue, $event)['custom_field_values']);
        $this->assertSame([], $this->calendarRow($talent, $event)['custom_field_values']);
    }

    public function test_another_schedules_private_answer_does_not_pass_the_creators_public_check(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent', ['event_custom_fields' => [
            'new_0' => ['name' => 'Genre', 'type' => 'string', 'filter' => true],
        ]]);
        $curator = $this->createCurator($this->createOwner(), ['event_custom_fields' => [
            'new_0' => ['name' => 'Internal rating', 'type' => 'string', 'private' => true],
        ]]);

        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->custom_field_values = ['new_0' => 'Rejected twice'];
        $event->custom_field_values_role_id = $curator->id;
        $event->save();

        $this->assertSame([], $event->publicCustomFieldValuesFor($talent));
        $this->assertSame([], $event->publicCustomFieldValuesFor($curator), 'private to the curator too');
    }

    public function test_rows_written_before_the_column_read_against_the_creator(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['event_custom_fields' => self::ROOM_FIELD + [
            'new_1' => ['name' => 'Staff notes', 'type' => 'string', 'private' => true],
        ]]);
        $other = $this->createRole($this->createOwner(), 'venue', ['event_custom_fields' => self::ROOM_FIELD]);

        $event = $this->createEvent($venue, [
            'creator_role_id' => $venue->id,
            'custom_field_values' => ['new_0' => 'Room B', 'new_1' => 'Bring a ladder'],
        ]);
        $this->assertNull($event->custom_field_values_role_id);

        $this->assertSame(['new_0' => 'Room B'], $event->publicCustomFieldValuesFor($venue), 'private stripped');
        $this->assertSame([], $event->publicCustomFieldValuesFor($other));
        $this->assertSame([], $event->publicCustomFieldValuesFor(null));
    }

    public function test_a_schedule_that_is_not_pro_gets_no_custom_values(): void
    {
        $venue = $this->createFreeRole(null, 'venue', ['event_custom_fields' => self::ROOM_FIELD]);
        $this->assertFalse($venue->fresh()->isPro());

        $event = $this->createEvent($venue, [
            'creator_role_id' => $venue->id,
            'custom_field_values' => ['new_0' => 'Room A'],
        ]);

        $this->assertSame([], $event->publicCustomFieldValuesFor($venue->fresh()));
    }

    public function test_the_curator_request_flow_keys_the_answers_to_the_curator(): void
    {
        $curator = $this->createCurator($this->createOwner(), [
            'accept_requests' => true,
            'require_account' => true,
            'event_custom_fields' => [
                'new_0' => ['name' => 'Room', 'type' => 'string', 'filter' => true, 'show_on_request' => true],
            ],
        ]);

        $this->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), [
            'name' => 'Submitted With Account',
            'starts_at' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'account_mode' => 'register',
            'terms' => true,
            'account_name' => 'Sam Guest',
            'account_email' => 'sam-filter@eventschedule-test.org',
            'account_password' => 'password1234',
            'schedule_name' => 'Sam Band',
            'custom_field_values' => ['new_0' => 'Room C'],
        ])->assertOk();

        $event = Event::where('name', 'Submitted With Account')->firstOrFail();
        $this->assertNotSame($curator->id, $event->creator_role_id, 'the event belongs to the submitter');
        $this->assertSame($curator->id, $event->custom_field_values_role_id);
        $this->assertSame(['new_0' => 'Room C'], $event->publicCustomFieldValuesFor($curator));

        // The submitter's schedule starts with no fields, which would make the next assertion pass
        // however the values were read. Give it a public field on the same key first.
        $talent = $event->creatorRole;
        $talent->event_custom_fields = ['new_0' => ['name' => 'Genre', 'type' => 'string', 'filter' => true]];
        $talent->save();
        $this->assertSame([], $event->fresh()->publicCustomFieldValuesFor($talent->fresh()));
    }

    public function test_a_booking_request_keys_the_answers_to_the_venue(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', [
            'accept_requests' => true,
            'require_account' => false,
            'require_approval' => true,
            'event_request_form' => 'booking',
            'event_custom_fields' => [
                'new_0' => ['name' => 'Room', 'type' => 'string', 'show_on_request' => true],
            ],
        ]);

        $this->postJson(route('event.booking_request.store', ['subdomain' => $venue->subdomain]), [
            'event_name' => 'Room Booking',
            'date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '20:00',
            'contact_name' => 'Sam Guest',
            'contact_email' => 'sam@example.com',
            'custom_field_values' => ['new_0' => 'Room D'],
        ])->assertOk();

        $this->assertSame($venue->id, Event::firstOrFail()->custom_field_values_role_id);
    }

    public function test_unknown_ownership_is_never_read(): void
    {
        // A legacy row with neither a creator nor a keyed schedule: whose fields its values
        // answer is unknown, so no schedule's filters or search may read them.
        $venue = $this->createRole($this->createOwner(), 'venue', ['event_custom_fields' => self::ROOM_FIELD]);
        $event = $this->createEvent($venue, ['custom_field_values' => ['new_0' => 'Room A']]);
        $this->assertNull($event->creator_role_id);

        $this->assertSame([], $event->publicCustomFieldValuesFor($venue));
    }

    public function test_answers_keyed_by_a_deleted_schedule_stay_hidden(): void
    {
        // No foreign key on purpose: nulling the column on delete would fall back to the creator
        // and read the deleted schedule's answers against the creator's fields.
        $talent = $this->createRole($this->createOwner(), 'talent', ['event_custom_fields' => [
            'new_0' => ['name' => 'Genre', 'type' => 'string', 'filter' => true],
        ]]);
        $curator = $this->createCurator($this->createOwner(), ['event_custom_fields' => [
            'new_0' => ['name' => 'Internal rating', 'type' => 'string', 'private' => true],
        ]]);

        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->custom_field_values = ['new_0' => 'Rejected twice'];
        $event->custom_field_values_role_id = $curator->id;
        $event->save();

        $curator->delete();

        $event->refresh();
        $this->assertSame($curator->id, $event->custom_field_values_role_id, 'the id is kept, not nulled');
        $this->assertSame([], $event->publicCustomFieldValuesFor($talent->fresh()));
    }

    // -- Editing an event another schedule saved ---------------------------------------------

    /** A talent's event with a private answer on the same key as the venue's public field. */
    private function talentEventAtVenue(): array
    {
        $talent = $this->createRole($this->createOwner(), 'talent', ['event_custom_fields' => [
            'new_0' => ['name' => 'Genre', 'type' => 'string', 'filter' => true, 'index' => 1],
            'new_1' => ['name' => 'Fee', 'type' => 'string', 'private' => true, 'index' => 2],
        ]]);
        $venue = $this->createRole($this->createOwner(), 'venue', ['event_custom_fields' => self::ROOM_FIELD + [
            'new_1' => ['name' => 'Notes', 'type' => 'string', 'index' => 2],
        ]]);

        $event = $this->createEvent($talent, [
            'name' => 'Talent Show',
            'creator_role_id' => $talent->id,
            'custom_field_values' => ['new_0' => 'Jazz', 'new_1' => 'Fee 500'],
        ]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        return [$talent, $venue, $event];
    }

    public function test_the_edit_form_does_not_prefill_another_schedules_answers(): void
    {
        [, $venue, $event] = $this->talentEventAtVenue();

        $this->actingAs($venue->user)
            ->get(route('event.edit', ['subdomain' => $venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            // The inputs, not the page: the AP's Vue data carries the whole event row, and private
            // means hidden from guests, not from the admins of a schedule the event is on.
            ->assertDontSee('value="Fee 500"', false)
            ->assertDontSee('value="Jazz"', false);
    }

    public function test_saving_blank_fields_leaves_another_schedules_answers_alone(): void
    {
        [$talent, $venue, $event] = $this->talentEventAtVenue();

        $this->putUpdateEvent($venue->user, $venue, $event, [
            'name' => 'Talent Show',
            'starts_at' => now()->addDays(7)->format('Y-m-d').' 20:00:00',
            'custom_field_values' => ['new_0' => '', 'new_1' => ''],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame(['new_0' => 'Jazz', 'new_1' => 'Fee 500'], $event->custom_field_values);
        $this->assertNull($event->custom_field_values_role_id);
        $this->assertTrue($event->customFieldValuesBelongTo($talent));
    }

    public function test_a_venue_setting_its_room_does_not_publish_the_talents_private_answer(): void
    {
        [$talent, $venue, $event] = $this->talentEventAtVenue();

        // What the venue's form now posts: its own Room, and a blank Notes (never prefilled).
        $this->putUpdateEvent($venue->user, $venue, $event, [
            'name' => 'Talent Show',
            'starts_at' => now()->addDays(7)->format('Y-m-d').' 20:00:00',
            'custom_field_values' => ['new_0' => 'Room A', 'new_1' => ''],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame($venue->id, $event->custom_field_values_role_id);
        $this->assertSame(['new_0' => 'Room A'], $this->calendarRow($venue, $event)['custom_field_values']);
        $this->assertSame([], $event->publicCustomFieldValuesFor($talent));
    }

    public function test_a_clone_does_not_prefill_another_schedules_answers(): void
    {
        [, $venue, $event] = $this->talentEventAtVenue();

        $this->actingAs($venue->user)
            ->get(route('event.clone', ['subdomain' => $venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertRedirect();

        $this->actingAs($venue->user)
            ->get(route('event.create', ['subdomain' => $venue->subdomain]))
            ->assertOk()
            ->assertDontSee('value="Fee 500"', false)
            ->assertDontSee('value="Jazz"', false);
    }

    public function test_a_clone_of_the_schedules_own_event_keeps_its_answers(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['event_custom_fields' => self::ROOM_FIELD]);
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'custom_field_values' => ['new_0' => 'Room Z']]);

        $this->actingAs($owner)
            ->get(route('event.clone', ['subdomain' => $venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertRedirect();

        $this->actingAs($owner)
            ->get(route('event.create', ['subdomain' => $venue->subdomain]))
            ->assertOk()
            ->assertSee('value="Room Z"', false);
    }

    public function test_a_backup_leaves_another_schedules_answers_behind(): void
    {
        [$talent, $venue, $event] = $this->talentEventAtVenue();
        $own = $this->createEvent($venue, [
            'name' => 'Venue Night',
            'creator_role_id' => $venue->id,
            'custom_field_values' => ['new_0' => 'Room B'],
        ]);

        $job = BackupJob::create(['user_id' => $venue->user_id, 'type' => 'export', 'status' => 'processing']);
        $data = json_decode(json_encode(app(BackupService::class)->exportSchedules([$venue->fresh()], false, $job)['json']), true);

        $exported = collect($data['schedules'][0]['events'])->keyBy('name');
        $this->assertNull($exported['Talent Show']['custom_field_values'], "the talent's answers, keyed by the talent's fields");
        $this->assertNotNull($exported['Venue Night']['custom_field_values']);
    }

    // -- The schedule page -------------------------------------------------------------------

    /** A value the calendar partial hands to Vue as `name: @json(...)`, decoded. */
    private function vueData(string $html, string $name): mixed
    {
        $this->assertSame(1, preg_match('/^\s*'.$name.': (.*),$/m', $html, $m), "{$name} is not in the Vue data");

        return json_decode($m[1], true, flags: JSON_THROW_ON_ERROR);
    }

    private function roomSchedule(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', $attrs + ['event_custom_fields' => self::ROOM_FIELD + [
            'new_1' => ['name' => 'Size', 'type' => 'dropdown', 'options' => 'Small,Large', 'filter' => true, 'index' => 2],
            'new_2' => ['name' => 'Notes', 'type' => 'string', 'index' => 3],
            'new_3' => ['name' => 'Staff', 'type' => 'string', 'filter' => true, 'private' => true, 'index' => 4],
            'new_4' => ['name' => 'Legacy', 'type' => 'string', 'filter' => true],
        ]]);
    }

    public function test_the_schedule_page_offers_the_fields_that_filter(): void
    {
        $role = $this->roomSchedule();

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $fields = collect($this->vueData($html, 'filterCustomFields'))->keyBy('key');
        $this->assertSame(['new_0', 'new_1', 'new_4'], $fields->keys()->all(), 'opted in, public, text or options');
        $this->assertSame(1, $fields['new_0']['index']);
        $this->assertNull($fields['new_4']['index'], 'no stored index, so no URL param - never its position');

        // Notes is not a filter but is public, so the search box still matches it; Staff is private.
        $searchable = array_keys($this->vueData($html, 'searchableCustomFields'));
        $this->assertContains('new_2', $searchable);
        $this->assertNotContains('new_3', $searchable);
    }

    public function test_a_shared_link_opens_the_schedule_already_filtered(): void
    {
        $role = $this->roomSchedule();

        $html = $this->get('/'.$role->subdomain.'?custom_1='.rawurlencode('  Room   A ').'&custom_2=LARGE')->assertOk()->getContent();

        $this->assertSame(
            ['new_0' => 'room a', 'new_1' => 'large', 'new_4' => ''],
            $this->vueData($html, 'selectedCustomFields'),
            'compared without case or extra spaces, like every option the calendar offers'
        );
    }

    public function test_a_link_to_an_option_the_field_does_not_have_selects_nothing(): void
    {
        $role = $this->roomSchedule();

        $html = $this->get('/'.$role->subdomain.'?custom_2=Enormous&custom_3=anything')->assertOk()->getContent();

        $selected = $this->vueData($html, 'selectedCustomFields');
        $this->assertSame('', $selected['new_1'], 'not one of Small, Large');
        $this->assertArrayNotHasKey('new_2', $selected, 'custom_3 names a field that is not a filter');
    }

    public function test_a_schedule_that_is_not_pro_offers_no_custom_field_filters(): void
    {
        $role = $this->createFreeRole(null, 'venue', ['event_custom_fields' => self::ROOM_FIELD]);
        $this->assertFalse($role->fresh()->isPro(), 'fixture: a genuinely free schedule');

        $html = $this->get('/'.$role->subdomain.'?custom_1=Room+A')->assertOk()->getContent();

        $this->assertSame([], $this->vueData($html, 'filterCustomFields'));
        $this->assertSame([], $this->vueData($html, 'searchableCustomFields'));
        $this->assertSame([], $this->vueData($html, 'selectedCustomFields'));
    }

    // -- The event page ----------------------------------------------------------------------

    public function test_a_custom_field_filter_travels_from_an_event_page_to_the_next(): void
    {
        // The event page's other events are three rows drawn by the server (it used to hold a
        // second copy of the list app, whose links carried the filter). The rows and the way
        // back both keep the filter the visitor arrived with, so following one does not drop it.
        $venue = $this->createRole($this->createOwner(), 'venue', ['event_custom_fields' => self::ROOM_FIELD]);
        $soon = $this->createEvent($venue, ['name' => 'Soon', 'starts_at' => now()->addDay()->setTime(12, 0)->format('Y-m-d H:i:s')]);
        $later = $this->createEvent($venue, ['name' => 'Later', 'starts_at' => now()->addDays(70)->setTime(12, 0)->format('Y-m-d H:i:s')]);

        $url = $this->guestEventUrl($venue, $later);

        $plain = $this->get($url)->assertOk()->getContent();
        $this->assertStringContainsString('href="'.e($soon->fresh()->getGuestUrl($venue->subdomain)).'"', $plain, 'no filter, no query');
        $this->assertStringNotContainsString('viewFullScheduleFooter', $plain, 'the list app and its "earlier events" count are gone from this page');

        $filtered = $this->get($url.(str_contains($url, '?') ? '&' : '?').'custom_1=room+a')->assertOk()->getContent();
        $rows = substr($filtered, strpos($filtered, 'id="gp-upcoming-events"'), 2500);
        $this->assertStringContainsString('href="'.e($soon->fresh()->getGuestUrl($venue->subdomain).'?custom_1=room+a').'"', $rows, 'the row keeps the filter');
        // ...and the page's back link keeps it too.
        $this->assertSame(1, preg_match('/class="gk-link gk-dayhead-link" href="[^"]*custom_1=room\+a"/', $rows));
    }
}
