<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Utils\ListWording;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule's list in its owner's own words (App\Utils\ListWording): the Events graphic
 * caption's, or a wording of the list's own, chosen in the schedule form (Settings, Advanced,
 * "Event list wording").
 *
 * A customer posts the caption to WhatsApp every week and asked for the list to say the same.
 * The list keeps what makes it the list (its day headings and time line, the price and Sold out
 * chips, the picture); the wording gives each card its words. Nothing changes until the owner
 * chooses it, and the caption itself does not move by a byte.
 */
class ListWordingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const CUSTOM = "*{day_name}* {date_dmy}\n🔥 *{event_name}* 🔥\n🕗 {time} | 📍 {venue}\n🎟️ {price}₪ | Doors ~8pm to ~11pm @my_band\nTickets: {url}";

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        ListWording::flush();
    }

    private function talent(array $graphic = [], array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'talent', $attrs + ['name' => 'The Nightjars', 'graphic_settings' => $graphic ?: null]);
    }

    private function event(Role $role, array $attrs = [], ?Role $venue = null): Event
    {
        $event = $this->createEvent($role, $attrs + ['name' => 'Jazz Night', 'creator_role_id' => $role->id]);
        if ($venue) {
            $event->roles()->attach($venue->id, ['is_accepted' => true]);
        }

        return $event->fresh();
    }

    private function venue(): Role
    {
        return $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Room', 'city' => 'Springfield']);
    }

    /** The card as the live page gets it: the month's JSON, the builder that runs there. */
    private function row(Role $role, Event $event, string $query = ''): array
    {
        $when = now()->addDays(7);
        $events = $this->getJson('/'.$role->subdomain.'/api/calendar-events?year='.$when->year.'&month='.$when->month.$query)->assertOk()->json('events');
        $row = collect($events)->firstWhere('id', \App\Utils\UrlUtils::encodeId($event->id));
        $this->assertNotNull($row, 'the event is in the month');

        return $row;
    }

    private function text(array $parts): string
    {
        return implode('', array_column($parts, 'v'));
    }

    public function test_the_list_reads_as_it_did_until_the_owner_switches_it_on(): void
    {
        $role = $this->talent(['text_template' => self::CUSTOM]);
        $event = $this->event($role);

        $this->assertNull($this->row($role, $event)['wording'], 'a stored caption alone changes nothing');

        $role->graphic_settings = ['text_template' => self::CUSTOM, 'list_wording' => 'caption'];
        $role->save();
        ListWording::flush();

        $this->assertIsArray($this->row($role, $event)['wording']);
    }

    public function test_the_caption_every_schedule_starts_with_gives_the_card_it_already_has(): void
    {
        $role = $this->talent(['list_wording' => 'caption']);
        $event = $this->event($role, ['short_description' => 'An evening of standards'], $this->venue());

        $wording = $this->row($role, $event)['wording'];

        // The name, without the colon that introduced the lines under it in a message.
        $this->assertSame('Jazz Night', $this->text($wording['name']));
        $this->assertSame('name', $wording['name'][0]['k']);
        // Its short description, then where: the date line above the name and the link line are the list's.
        $this->assertSame(['An evening of standards', 'Blue Room | Springfield'], array_map(fn ($line) => $this->text($line['p']), $wording['body']));
        $this->assertFalse($wording['body'][0]['loc']);
        $this->assertTrue($wording['body'][1]['loc'], 'a line that only says where, which a wide card\'s venue tile says');
        $this->assertSame('ltr', $wording['dir']);
    }

    public function test_a_custom_caption_keeps_its_words_and_drops_what_the_list_says_itself(): void
    {
        $role = $this->talent(['text_template' => self::CUSTOM, 'list_wording' => 'caption']);
        $event = $this->event($role, [], $this->venue());

        $wording = $this->row($role, $event)['wording'];

        $this->assertSame("🔥\u{00A0}Jazz Night\u{00A0}🔥", $this->text($wording['name']), 'the decoration kept to the name, never alone on a line');
        $this->assertSame('gk-w-b', $wording['name'][1]['c']);
        $this->assertTrue($wording['name'][0]['d'], 'decoration a screen reader skips');
        $body = array_map(fn ($line) => $this->text($line['p']), $wording['body']);
        // The time is the card's own line, the price the chip's, the link the card itself: each
        // goes with its label, and nothing else of its line.
        $this->assertSame(["📍\u{00A0}Blue Room", 'Doors ~8pm to ~11pm @my_band'], $body);
        $this->assertSame('venue', $wording['body'][0]['p'][1]['k']);
        // WhatsApp's own rule: a ~ or _ inside a word is not formatting.
        $this->assertArrayNotHasKey('c', $wording['body'][1]['p'][0]);
    }

    public function test_an_empty_word_takes_its_label_on_either_side(): void
    {
        $role = $this->talent(['text_template' => "{event_name}\nAb {time} Uhr | 📍 {venue}\nDoors {time}, show {end_time}\n{event_name} at {time}", 'list_wording' => 'caption']);
        $event = $this->event($role, [], $this->venue());

        $body = array_map(fn ($line) => $this->text($line['p']), $this->row($role, $event)['wording']['body']);

        // "Ab ... Uhr" is the time's, before and after it; a line whose every word is the list's goes whole.
        $this->assertSame(["📍\u{00A0}Blue Room", 'Jazz Night'], $body);
    }

    public function test_formatting_comes_from_the_caption_never_from_an_events_own_text(): void
    {
        $role = $this->talent(['text_template' => "{event_name}\n{short_description}", 'list_wording' => 'caption']);
        $event = $this->event($role, ['name' => '*NSYNC _Tribute_ {{ 7*7 }}', 'short_description' => '~Half price~']);

        $wording = $this->row($role, $event)['wording'];

        $this->assertSame('*NSYNC _Tribute_ {{ 7*7 }}', $wording['name'][0]['v']);
        $this->assertArrayNotHasKey('c', $wording['name'][0]);
        $this->assertSame('~Half price~', $wording['body'][0]['p'][0]['v']);
    }

    public function test_a_custom_field_is_printed_only_where_the_event_page_prints_it(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Student House',
            'graphic_settings' => ['text_template' => "{event_name}\nRoom {custom_1}\nFee {custom_2}\nPhone {custom_3}", 'list_wording' => 'caption'],
            'event_custom_fields' => [
                'new_0' => ['name' => 'Room', 'type' => 'string', 'show_on_event' => true, 'index' => 1],
                'new_1' => ['name' => 'Fee paid', 'type' => 'string', 'private' => true, 'show_on_event' => true, 'index' => 2],
                'new_2' => ['name' => 'Phone', 'type' => 'string', 'index' => 3],
            ],
        ]);
        $event = $this->event($role, [
            'custom_field_values_role_id' => $role->id,
            'custom_field_values' => ['new_0' => 'Hall', 'new_1' => 'Yes', 'new_2' => '555-0100'],
        ]);

        $body = array_map(fn ($line) => $this->text($line['p']), $this->row($role, $event)['wording']['body']);

        $this->assertSame(['Room Hall'], $body, 'not a private field, nor one kept off the event page, and no label left behind');
    }

    public function test_the_standard_card_for_a_locked_event_another_language_or_a_caption_with_no_name(): void
    {
        $role = $this->talent(['list_wording' => 'caption'], ['language_code' => 'en', 'translation_language_code' => 'es']);
        $open = $this->event($role);
        $locked = $this->event($role, ['name' => 'Members Night', 'event_password' => 'secret']);

        $this->assertIsArray($this->row($role, $open)['wording']);
        $this->assertNull($this->row($role, $locked)['wording']);
        // The caption's own words cannot be translated: a visitor reading Spanish gets the usual card.
        $this->assertNull($this->row($role, $open, '&lang=es')['wording']);

        $role->graphic_settings = ['list_wording' => 'caption', 'text_template' => "*{day_name}* {time}\n{venue}"];
        $role->save();
        ListWording::flush();
        $this->assertNull($this->row($role, $open)['wording']);
    }

    public function test_the_venues_own_schedule_leaves_the_place_out(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Room', 'city' => 'Springfield', 'graphic_settings' => ['list_wording' => 'caption']]);
        $event = $this->event($role, ['short_description' => 'Standards']);

        $body = array_map(fn ($line) => $this->text($line['p']), $this->row($role, $event)['wording']['body']);

        $this->assertSame(['Standards'], $body);
    }

    public function test_the_event_pages_column_says_it_in_the_same_words(): void
    {
        $role = $this->talent(['text_template' => self::CUSTOM, 'list_wording' => 'caption']);
        $here = $this->event($role, ['name' => 'Tonight', 'starts_at' => now()->addDays(1)->setTime(12, 0)->format('Y-m-d H:i:s')]);
        $this->event($role, ['name' => 'Late Show'], $this->venue());

        $html = $this->get($this->guestEventUrl($role, $here))->assertOk()->getContent();
        $more = substr($html, strpos($html, 'id="gp-upcoming-events"'));
        $more = substr($more, 0, strpos($more, '</section>'));

        $this->assertStringContainsString('<span class="gk-row-title gk-row-name gk-w-title" dir="ltr"><span aria-hidden="true">🔥&nbsp;</span><bdi class="gk-w-b">Late Show</bdi>', str_replace("\u{00A0}", '&nbsp;', $more));
        $this->assertStringContainsString('Doors ~8pm to ~11pm @my_band', $more);
        $this->assertStringNotContainsString('Tickets:', $more);
    }

    public function test_a_wording_of_the_lists_own_is_used_instead_of_the_caption(): void
    {
        $role = $this->talent(['text_template' => self::CUSTOM, 'list_wording' => 'custom', 'list_template' => "{event_name} ✨\nAt {venue}"]);
        $event = $this->event($role, [], $this->venue());

        $wording = $this->row($role, $event)['wording'];

        $this->assertSame("Jazz Night\u{00A0}✨", $this->text($wording['name']));
        $this->assertSame(['At Blue Room'], array_map(fn ($line) => $this->text($line['p']), $wording['body']));

        // An empty one leaves the list as it was.
        $role->graphic_settings = ['text_template' => self::CUSTOM, 'list_wording' => 'custom', 'list_template' => '  '];
        $role->save();
        ListWording::flush();
        $this->assertNull($this->row($role, $event)['wording']);
    }

    public function test_the_schedule_form_offers_the_choice_and_keeps_it_apart_from_the_caption(): void
    {
        config(['app.hosted' => true]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'The Nightjars', 'graphic_settings' => ['text_template' => self::CUSTOM, 'header_text' => 'This week']]);
        $this->event($role, ['name' => 'Jazz Night', 'flyer_image_url' => 'flyer.png'], $this->venue());
        $save = fn (array $fields) => $this->actingAs($owner)
            ->from(route('role.edit', ['subdomain' => $role->subdomain]))
            ->put(route('role.update', ['subdomain' => $role->subdomain]), array_merge([
                'name' => $role->name, 'email' => $role->email, 'timezone' => $role->timezone, 'new_subdomain' => $role->subdomain,
            ], $fields));

        $edit = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString('<option value="standard" selected>', $edit, 'standard until the owner chooses');
        $this->assertStringContainsString('name="list_wording_submitted"', $edit);

        $save(['list_wording_submitted' => '1', 'list_wording' => 'custom', 'list_template' => "{event_name}\r\nAt {venue}"])->assertSessionHasNoErrors();
        $settings = $role->fresh()->graphic_settings;
        $this->assertSame('custom', $settings['list_wording']);
        $this->assertSame("{event_name}\nAt {venue}", $settings['list_template']);
        $this->assertSame(self::CUSTOM, $settings['text_template'], 'the caption is the Events graphic page\'s, untouched');
        $this->assertSame('This week', $settings['header_text']);

        // A save that did not carry the field changes nothing; the caption keeps the list's own wording.
        $save(['list_wording' => 'standard'])->assertSessionHasNoErrors();
        $this->assertSame('custom', $role->fresh()->graphic_settings['list_wording']);
        $this->actingAs($owner)->postJson(route('event.save_graphic_settings', ['subdomain' => $role->subdomain]), ['text_template' => 'X {event_name}'])->assertOk();
        $this->assertSame("{event_name}\nAt {venue}", $role->fresh()->graphic_settings['list_template']);
        $save(['list_wording_submitted' => '1', 'list_wording' => 'sideways'])->assertSessionHasErrors('list_wording');

        // The preview: the next events in the wording being typed, nothing saved.
        $preview = $this->actingAs($owner)->getJson(route('event.graphic_list_preview', ['subdomain' => $role->subdomain]).'?mode=custom&template='.urlencode("🔥 *{event_name}*\n📍 {venue}"))->assertOk()->json('cards');
        $this->assertSame('Jazz Night', $preview[0]['name']);
        $this->assertSame("🔥\u{00A0}Jazz Night", $this->text($preview[0]['wording']['name']));
        $this->assertSame("📍\u{00A0}Blue Room", $this->text($preview[0]['wording']['body'][0]['p']));
        $caption = $this->actingAs($owner)->getJson(route('event.graphic_list_preview', ['subdomain' => $role->subdomain]).'?mode=caption')->assertOk()->json('cards');
        $this->assertSame('X Jazz Night', $this->text($caption[0]['wording']['name']), 'the caption as stored, not what was typed for the list');

        // A team viewer does not get the preview.
        $viewer = $this->createOwner();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);
        $this->actingAs($viewer)->getJson(route('event.graphic_list_preview', ['subdomain' => $role->subdomain]).'?mode=caption')->assertForbidden();

        // Where the list follows the caption, the Events graphic page says so beside it.
        $save(['list_wording_submitted' => '1', 'list_wording' => 'caption', 'list_template' => ''])->assertSessionHasNoErrors();
        $page = $this->actingAs($owner)->get(route('event.generate_graphic', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString(e(__('messages.list_wording_follows')), $page);
        $this->assertStringContainsString('#style-content-animation', $page);
    }
}
