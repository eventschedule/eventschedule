<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Utils\CustomFieldDisplay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Custom fields on the public event page (issue #126: "so people can see which floor the event is
 * happening on").
 *
 * Until 2026-10 no page printed a custom field's answer. What is printed now is decided by ONE
 * rule, CustomFieldDisplay::forEventPage(): the field is ticked "On event page" by the schedule
 * whose form wrote the answer, is not private, and that schedule is Pro. Nothing a schedule
 * already held is published by the feature arriving: an absent flag is off.
 */
class CustomFieldEventPageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array<string, array<string, mixed>> */
    private function fields(array $overrides = []): array
    {
        $fields = [
            'new_0' => ['name' => 'Room', 'type' => 'multiselect', 'options' => 'Foyer,Garden,Hall', 'show_on_event' => true, 'index' => 1],
            'new_1' => ['name' => 'Fee paid', 'type' => 'string', 'private' => true, 'show_on_event' => true, 'index' => 2],
            'new_2' => ['name' => 'Contact phone', 'type' => 'string', 'index' => 3],
            'new_3' => ['name' => 'Step-free', 'type' => 'switch', 'show_on_event' => true, 'index' => 4],
            'new_4' => ['name' => 'Sign up by', 'type' => 'date', 'show_on_event' => true, 'index' => 5],
        ];

        foreach ($overrides as $key => $override) {
            $fields[$key] = $override + $fields[$key];
        }

        return $fields;
    }

    private function venue(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', $attrs + ['name' => 'Student House', 'event_custom_fields' => $this->fields()]);
    }

    private function event(Role $role, array $values, array $attrs = []): Event
    {
        return $this->createEvent($role, $attrs + [
            'name' => 'Opening Night',
            'creator_role_id' => $role->id,
            'custom_field_values_role_id' => $role->id,
            'custom_field_values' => $values,
        ]);
    }

    /** The facts row of an event page, or '' when the page has none. */
    private function factsRow(Role $role, Event $event): string
    {
        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();
        $start = strpos($html, 'id="gp-event-fields"');

        return $start === false ? '' : substr($html, $start, strpos($html, 'id="gp-event-', $start + 10) - $start);
    }

    public function test_a_ticked_field_is_on_the_event_page(): void
    {
        $venue = $this->venue();
        $event = $this->event($venue, ['new_0' => 'Foyer, Garden', 'new_1' => 'Fee 500', 'new_2' => '555 0100', 'new_3' => '1', 'new_4' => '2026-10-12']);

        $row = $this->factsRow($venue, $event);

        $this->assertStringContainsString('Room', $row);
        $this->assertStringContainsString('Foyer, Garden', $row);
        $this->assertStringContainsString('Step-free', $row);
        $this->assertStringContainsString('Oct 12, 2026', $row, 'a date is printed as a date, not as it is stored');
        $this->assertStringNotContainsString('2026-10-12', $row);
    }

    /** The whole point of asking: a request form's answers are somebody else's words. */
    public function test_a_field_nobody_ticked_is_not_printed_anywhere_on_the_page(): void
    {
        $venue = $this->venue();
        $event = $this->event($venue, ['new_0' => 'Foyer', 'new_2' => '555 0100']);

        $html = $this->get($this->guestEventUrl($venue, $event))->assertOk()->getContent();

        $this->assertStringNotContainsString('Contact phone', $html);
        $this->assertStringNotContainsString('555 0100', $html);
    }

    public function test_a_schedule_that_has_ticked_nothing_gets_the_page_it_had(): void
    {
        $fields = array_map(fn ($field) => array_diff_key($field, ['show_on_event' => 1]), $this->fields());
        $venue = $this->venue(['event_custom_fields' => $fields]);
        $event = $this->event($venue, ['new_0' => 'Foyer', 'new_3' => '1']);

        $html = $this->get($this->guestEventUrl($venue, $event))->assertOk()->getContent();

        $this->assertStringNotContainsString('gp-event-fields', $html, 'an absent flag is off: nothing is published by the feature arriving');
        $this->assertSame([], CustomFieldDisplay::forEventPage($event));
    }

    public function test_a_private_field_is_never_printed_even_when_ticked(): void
    {
        $venue = $this->venue();
        $event = $this->event($venue, ['new_0' => 'Hall', 'new_1' => 'Fee 500']);

        $html = $this->get($this->guestEventUrl($venue, $event))->assertOk()->getContent();

        $this->assertStringContainsString('Hall', $this->factsRow($venue, $event));
        $this->assertStringNotContainsString('Fee 500', $html);
        $this->assertStringNotContainsString('Fee paid', $html);
    }

    /**
     * The form posts 0 for a switch nobody touched, so every event saved since the field was
     * added says "no". On a public page that would be a claim nobody made.
     */
    public function test_a_switch_is_printed_only_when_it_is_on(): void
    {
        $venue = $this->venue();
        $off = $this->event($venue, ['new_0' => 'Hall', 'new_3' => '0']);
        $on = $this->event($venue, ['new_0' => 'Hall', 'new_3' => '1'], ['name' => 'Second Night']);

        $this->assertStringNotContainsString('Step-free', $this->factsRow($venue, $off));
        $this->assertStringContainsString('Step-free', $this->factsRow($venue, $on));
    }

    /**
     * Which of its two forms the row takes is the schedule's doing: with several fields ticked,
     * an event that answered one of them keeps the list, or the room would change size and place
     * from one of a schedule's events to the next.
     */
    public function test_a_schedules_events_all_lay_the_row_out_the_same_way(): void
    {
        $several = $this->venue();
        $one = $this->venue(['event_custom_fields' => ['new_0' => ['name' => 'Room', 'type' => 'string', 'show_on_event' => true, 'index' => 1]]]);

        $this->assertStringContainsString('gk-facts-pairs', $this->factsRow($several, $this->event($several, ['new_0' => 'Hall'])));

        $row = $this->factsRow($one, $this->event($one, ['new_0' => 'Hall']));
        $this->assertStringNotContainsString('gk-facts-pairs', $row);
        $this->assertStringContainsString('class="gk-fact"', $row);

        // A paragraph over its own label reads upside down: a long answer is a list of one.
        $this->assertStringContainsString('gk-facts-pairs', $this->factsRow($one, $this->event($one, ['new_0' => str_repeat('A long answer. ', 6)], ['name' => 'Second Night'])));
    }

    /** "Step-free access", not "Yes" over a caption. */
    public function test_a_lone_switch_leads_with_its_name(): void
    {
        $venue = $this->venue(['event_custom_fields' => ['new_0' => ['name' => 'Step-free access', 'type' => 'switch', 'show_on_event' => true, 'index' => 1]]]);

        $row = $this->factsRow($venue, $this->event($venue, ['new_0' => '1']));

        $this->assertStringContainsString('gk-fact-flag', $row);
        $this->assertMatchesRegularExpression('/<dt class="text-lg[^"]*"[^>]*><bdi>Step-free access<\/bdi><\/dt>/', $row);
    }

    /** "Oct 12, 2026" in large type under the event's own date row reads as a second date for the event. */
    public function test_a_lone_date_leads_with_its_name(): void
    {
        $venue = $this->venue(['event_custom_fields' => ['new_0' => ['name' => 'Sign up by', 'type' => 'date', 'show_on_event' => true, 'index' => 1]]]);

        $row = $this->factsRow($venue, $this->event($venue, ['new_0' => '2026-10-12']));

        $this->assertMatchesRegularExpression('/<dt class="text-lg[^"]*"[^>]*><bdi>Sign up by<\/bdi><\/dt>/', $row);
        $this->assertStringContainsString('Oct 12, 2026', $row);
    }

    /**
     * A field's second name is in ITS schedule's second language, which need not be the page's.
     * An English venue translated into Hebrew, on the page of a Hebrew curator translated into
     * English: the curator's own (Hebrew) view wants the venue's Hebrew name for the field.
     */
    public function test_a_label_is_in_the_language_the_page_is_read_in_whoever_owns_the_field(): void
    {
        $venue = $this->venue([
            'language_code' => 'en', 'translation_language_code' => 'he',
            'event_custom_fields' => ['new_0' => ['name' => 'Room', 'name_en' => 'חדר', 'type' => 'dropdown', 'options' => 'Garden,Hall', 'options_en' => 'גינה,אולם', 'show_on_event' => true, 'index' => 1]],
        ]);
        $curator = $this->createRole($this->createOwner(), 'curator', ['name' => 'לוח העיר', 'language_code' => 'he', 'translation_language_code' => 'en']);
        $event = $this->event($venue, ['new_0' => 'Garden']);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $event = $event->fresh();

        $own = CustomFieldDisplay::forEventPage($event, $venue)[0];
        $this->assertSame(['Room', 'Garden'], [$own['label'], $own['value']], "the venue's own page, in its own language");

        $theirs = CustomFieldDisplay::forEventPage($event, $curator)[0];
        $this->assertSame(['חדר', 'גינה'], [$theirs['label'], $theirs['value']], "the curator's Hebrew page");
    }

    /** Every save of the schedule used to drop the options' translations until the next translation run. */
    public function test_saving_the_schedule_keeps_the_translations_of_options_that_did_not_change(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Student House', 'event_custom_fields' => ['new_0' => ['name' => 'Room', 'type' => 'dropdown', 'options' => 'Garden,Hall', 'options_en' => 'Jardin,Salle', 'index' => 1]]]);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $venue->subdomain]), [
            'name' => $venue->name, 'new_subdomain' => $venue->subdomain, 'email' => $venue->email, 'timezone' => $venue->timezone, 'language_code' => 'en',
            'event_custom_fields_submitted' => '1',
            'event_custom_fields' => ['new_0' => ['name' => 'Room', 'type' => 'dropdown', 'options' => 'Garden,Hall', 'show_on_request' => '1', 'index' => '1']],
        ])->assertSessionHasNoErrors();

        $this->assertSame('Jardin,Salle', $venue->fresh()->event_custom_fields['new_0']['options_en'] ?? null);
    }

    public function test_a_deleted_schedules_fields_are_on_no_page(): void
    {
        $venue = $this->venue();
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Quartet']);
        $event = $this->event($venue, ['new_0' => 'Garden']);
        $event->roles()->attach($act->id, ['is_accepted' => true]);
        DB::table('roles')->where('id', $venue->id)->update(['is_deleted' => true]);

        $this->assertSame([], CustomFieldDisplay::forEventPage($event->fresh(), $act));
    }

    public function test_a_date_is_in_the_order_of_the_language_the_page_is_read_in(): void
    {
        $venue = $this->venue(['language_code' => 'de']);
        $event = $this->event($venue, ['new_0' => 'Hall', 'new_4' => '2026-10-12']);

        $this->assertStringContainsString('12. Okt 2026', $this->factsRow($venue, $event));
    }

    /**
     * The other end of it: a visitor's answer to a ticked field is public once the schedule
     * accepts their event, and the form they type it into says so.
     */
    public function test_the_request_forms_say_which_answers_will_be_on_the_event_page(): void
    {
        $fields = [
            'new_0' => ['name' => 'Room', 'type' => 'string', 'show_on_request' => true, 'show_on_event' => true, 'index' => 1],
            'new_1' => ['name' => 'Phone', 'type' => 'string', 'show_on_request' => true, 'index' => 2],
            'new_2' => ['name' => 'Fee', 'type' => 'string', 'show_on_request' => true, 'private' => true, 'show_on_event' => true, 'index' => 3],
        ];
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['accept_requests' => true, 'require_account' => true, 'require_approval' => true, 'event_custom_fields' => $fields]);
        $open = $this->createCurator($owner, ['accept_requests' => true, 'require_account' => false, 'require_approval' => true, 'event_custom_fields' => $fields]);
        $talent = $this->createRole($owner, 'talent', ['accept_requests' => true, 'event_custom_fields' => $fields]);

        // The two forms a visitor types into: each question is data handed to the page's script.
        foreach ([route('event.guest_submit', ['subdomain' => $curator->subdomain]), route('event.booking_request', ['subdomain' => $talent->subdomain])] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('data-answer-on-event', $html, $url);
            $this->assertMatchesRegularExpression('/"key":"new_0"[^}]*"on_event":true/', $html, $url);
            $this->assertMatchesRegularExpression('/"key":"new_1"[^}]*"on_event":false/', $html, $url);
            $this->assertMatchesRegularExpression('/"key":"new_2"[^}]*"on_event":false/', $html, 'a private answer is never on the page, whatever is ticked');
        }

        // The import page draws its questions on the server: one hint, under the one ticked public field.
        $html = $this->get(route('event.guest_import', ['subdomain' => $open->subdomain]))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'data-answer-on-event'));
    }

    public function test_an_event_with_no_answer_to_a_ticked_field_has_no_row(): void
    {
        $venue = $this->venue();
        $event = $this->event($venue, ['new_2' => '555 0100', 'new_3' => '0']);

        $this->assertSame('', $this->factsRow($venue, $event));
    }

    /** The room is the same fact on the act's page of the event as on the venue's. */
    public function test_the_answer_is_on_another_schedules_page_of_the_same_event(): void
    {
        $venue = $this->venue();
        $event = $this->event($venue, ['new_0' => 'Garden']);
        $act = $this->createRole($this->createOwner(), 'talent', [
            'name' => 'The Quartet',
            // Its own first field is new_0 too, and private: the venue's room must not be read
            // against it, in either direction.
            'event_custom_fields' => ['new_0' => ['name' => 'Fee', 'type' => 'string', 'private' => true, 'index' => 1]],
        ]);
        $event->roles()->attach($act->id, ['is_accepted' => true]);

        $row = $this->factsRow($act, $event->fresh());

        $this->assertStringContainsString('Room', $row);
        $this->assertStringContainsString('Garden', $row);
        $this->assertStringNotContainsString('Fee', $row);
    }

    public function test_answers_keyed_by_a_schedule_that_ticked_nothing_stay_off_every_page(): void
    {
        $venue = $this->venue();
        $act = $this->createRole($this->createOwner(), 'talent', [
            'name' => 'The Quartet',
            'event_custom_fields' => ['new_0' => ['name' => 'Fee', 'type' => 'string', 'index' => 1]],
        ]);
        // The act's form wrote this answer; the venue's new_0 is ticked, the act's is not.
        $event = $this->createEvent($act, ['name' => 'Opening Night', 'creator_role_id' => $act->id, 'custom_field_values_role_id' => $act->id, 'custom_field_values' => ['new_0' => 'Fee 500']]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $html = $this->get($this->guestEventUrl($venue, $event->fresh()))->assertOk()->getContent();

        $this->assertStringNotContainsString('Fee 500', $html, "the act's fee was printed as the venue's room");
        $this->assertStringNotContainsString('gp-event-fields', $html);
    }

    /**
     * A request's answers are keyed to the schedule that was ASKED, from the moment of asking.
     * Until it accepts, they are answers to its questions and nothing more: an act that sends a
     * curator a request must not have the curator's "Room" on its own public page at once, and
     * must not keep it after the curator says no.
     */
    public function test_a_schedule_that_has_not_accepted_the_event_puts_nothing_on_its_pages(): void
    {
        $curator = $this->createRole($this->createOwner(), 'curator', ['name' => 'City Listings', 'event_custom_fields' => $this->fields()]);
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Quartet']);
        $event = $this->createEvent($act, ['name' => 'Opening Night', 'creator_role_id' => $act->id, 'custom_field_values_role_id' => $curator->id, 'custom_field_values' => ['new_0' => 'Garden']]);
        $event->roles()->attach($curator->id, ['is_accepted' => null]);

        $this->assertSame('', $this->factsRow($act, $event->fresh()), 'waiting');

        $event->roles()->updateExistingPivot($curator->id, ['is_accepted' => false]);
        $this->assertSame('', $this->factsRow($act, $event->fresh()), 'declined');

        $event->roles()->updateExistingPivot($curator->id, ['is_accepted' => true]);
        $this->assertStringContainsString('Garden', $this->factsRow($act, $event->fresh()), 'accepted');
    }

    /**
     * The editor numbers a new field from the highest key on the page, so removing the last
     * field and adding another used to hand the new one the old one's key, and every answer
     * events still held under it: a removed private phone number, printed as the new "Room".
     */
    public function test_a_new_field_does_not_inherit_a_removed_fields_answers(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Student House', 'event_custom_fields' => ['new_0' => ['name' => 'Contact phone', 'type' => 'string', 'private' => true, 'index' => 1]]]);
        $event = $this->event($venue, ['new_0' => '555 0100']);
        $save = fn (array $fields) => $this->actingAs($owner)->put(route('role.update', ['subdomain' => $venue->subdomain]), [
            'name' => $venue->name, 'new_subdomain' => $venue->subdomain, 'email' => $venue->email, 'timezone' => $venue->timezone, 'language_code' => 'en',
            'event_custom_fields_submitted' => '1', 'event_custom_fields' => $fields,
        ])->assertSessionHasNoErrors();

        // The owner removes the field, saves, and adds "Room": the page offers it new_0 again.
        $save([]);
        $save(['new_0' => ['name' => 'Room', 'type' => 'string', 'show_on_request' => '1', 'show_on_event' => '1']]);

        $fields = $venue->fresh()->event_custom_fields;
        $this->assertCount(1, $fields);
        $this->assertNotSame('new_0', array_key_first($fields), 'the new field took the key of the one that was removed');
        auth()->logout();
        $html = $this->get($this->guestEventUrl($venue, $event))->assertOk()->getContent();
        $this->assertStringNotContainsString('555 0100', $html);

        // A field that is already saved keeps its key, and so its answers.
        $key = array_key_first($fields);
        $save([$key => ['name' => 'Room', 'type' => 'string', 'show_on_request' => '1', 'show_on_event' => '1'], 'new_7' => ['name' => 'Floor', 'type' => 'string']]);
        $this->assertSame([$key, 'new_7'], array_keys($venue->fresh()->event_custom_fields), 'a key no event answers is kept as it was posted');
    }

    public function test_a_password_protected_event_keeps_its_answers_behind_the_password(): void
    {
        $venue = $this->venue();
        $event = $this->event($venue, ['new_0' => 'Garden'], ['event_password' => 'open sesame']);

        $html = $this->get($this->guestEventUrl($venue, $event))->getContent();

        $this->assertStringNotContainsString('gp-event-fields', $html);
        $this->assertStringNotContainsString('Garden', $html);
    }

    public function test_a_schedule_below_pro_prints_nothing(): void
    {
        $venue = $this->createFreeRole(null, 'venue', ['name' => 'Student House', 'event_custom_fields' => $this->fields()]);
        $this->assertFalse($venue->fresh()->isPro());
        $event = $this->event($venue, ['new_0' => 'Foyer']);

        $this->assertSame([], CustomFieldDisplay::forEventPage($event));
        $this->assertStringNotContainsString('gp-event-fields', $this->get($this->guestEventUrl($venue, $event))->assertOk()->getContent());
    }

    public function test_an_answer_is_printed_as_text(): void
    {
        $venue = $this->venue(['event_custom_fields' => $this->fields(['new_2' => ['show_on_event' => true]])]);
        $event = $this->event($venue, ['new_2' => '<script>alert(1)</script> {{ 7 * 7 }}']);

        $row = $this->factsRow($venue, $event);

        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $row);
        $this->assertStringNotContainsString('<script>alert(1)', $row);
    }

    public function test_the_schedule_form_saves_the_choice_and_shows_it_ticked(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Student House', 'event_custom_fields' => ['new_0' => ['name' => 'Room', 'type' => 'string', 'index' => 1]]]);
        $this->assertFalse(Role::isEventCustomFieldOnEventPage($venue->event_custom_fields['new_0']));

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $venue->subdomain]), [
            'name' => $venue->name,
            'new_subdomain' => $venue->subdomain,
            'email' => $venue->email,
            'timezone' => $venue->timezone,
            'language_code' => 'en',
            'event_custom_fields_submitted' => '1',
            'event_custom_fields' => ['new_0' => ['name' => 'Room', 'type' => 'string', 'show_on_request' => '1', 'show_on_event' => '1', 'index' => '1']],
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Role::isEventCustomFieldOnEventPage($venue->fresh()->event_custom_fields['new_0']));

        $html = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $venue->subdomain]))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/name="event_custom_fields\[new_0\]\[show_on_event\]"[^>]*\bchecked\b/s', $html);
    }
}
