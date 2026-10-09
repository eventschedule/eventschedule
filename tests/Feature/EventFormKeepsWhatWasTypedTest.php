<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPart;
use App\Models\EventPoll;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A save the server refuses gives the form back as it was left, not as it is stored.
 *
 * The event form seeded most of its tabs from the stored event alone. The dangerous one was
 * visibility: Draft chosen, the save refused over another field, and the page came back on Public -
 * so fixing that field and saving published the event. The "Also list on" ticks and the password
 * went the same way, and a refused link ending came back inside a closed, disabled editor, which
 * hid its message and did not send the value again.
 */
class EventFormKeepsWhatWasTypedTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['subdomain' => 'keeptalent']);
    }

    private function editUrl(Event $event): string
    {
        return route('event.edit', ['subdomain' => 'keeptalent', 'hash' => UrlUtils::encodeId($event->id)]);
    }

    /** Post a save that is refused for its empty name, and return the page it comes back to. */
    private function refused(Event $event, array $data): string
    {
        $this->actingAs($this->owner)->from($this->editUrl($event))
            ->put(route('event.update', ['subdomain' => 'keeptalent', 'hash' => UrlUtils::encodeId($event->id)]), array_merge([
                'name' => '', 'starts_at' => $event->starts_at, 'duration' => 2,
            ], $data))
            ->assertRedirect($this->editUrl($event))
            ->assertSessionHasErrors('name');

        return $this->actingAs($this->owner)->get($this->editUrl($event))->assertOk()->getContent();
    }

    public function test_draft_chosen_before_a_refused_save_is_still_draft_when_the_page_comes_back(): void
    {
        $event = $this->createEvent($this->role);
        $this->assertSame('public', $event->visibilityState());

        $html = $this->refused($event, ['is_draft' => 1, 'is_private' => 0, 'is_internal' => 0]);

        $this->assertStringContainsString('is_draft: true,', $html);
        $this->assertMatchesRegularExpression('/<input type="radio" name="visibility_ui" value="draft"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input type="radio" name="visibility_ui" value="public"[^>]*checked/', $html);
        // What is stored has not moved, and the badge and the bar's warning still read from it.
        $this->assertStringContainsString('savedVisibility: "public",', $html);
        $this->assertFalse((bool) Event::find($event->id)->is_draft, 'sanity check: the save really was refused');
    }

    public function test_public_chosen_for_a_draft_comes_back_as_public(): void
    {
        $event = $this->createEvent($this->role, ['is_draft' => true]);

        $html = $this->refused($event, ['is_draft' => 0, 'is_private' => 0, 'is_internal' => 0]);

        $this->assertStringContainsString('is_draft: false,', $html);
        $this->assertStringContainsString('savedVisibility: "draft",', $html);
    }

    public function test_unlisted_and_its_password_come_back(): void
    {
        $event = $this->createEvent($this->role);

        $html = $this->refused($event, ['is_draft' => 0, 'is_private' => 1, 'is_internal' => 0, 'event_password' => 'opensesame']);

        $this->assertStringContainsString('is_private: true,', $html);
        $this->assertStringContainsString('event_password: "opensesame",', $html);
    }

    public function test_the_also_list_on_ticks_come_back_as_they_were_left(): void
    {
        $kept = $this->createCurator($this->owner, ['name' => 'Kept Curator']);
        $dropped = $this->createCurator($this->owner, ['name' => 'Dropped Curator']);
        $added = $this->createCurator($this->owner, ['name' => 'Added Curator']);
        $event = $this->createEvent($this->role);
        $event->roles()->attach($kept->id, ['is_accepted' => true]);
        $event->roles()->attach($dropped->id, ['is_accepted' => true]);

        $html = $this->refused($event, ['curators_submitted' => 1, 'curators' => [UrlUtils::encodeId($kept->id), UrlUtils::encodeId($added->id)]]);

        $ticked = fn (Role $schedule) => preg_match('/id="curator_'.preg_quote($schedule->encodeId(), '/').'"[^>]*\schecked/s', $html) === 1;
        $this->assertTrue($ticked($kept));
        $this->assertTrue($ticked($added), 'a box ticked before the refusal is still ticked');
        $this->assertFalse($ticked($dropped), 'and one unticked is still unticked');
    }

    public function test_without_a_refused_save_the_ticks_are_what_is_stored(): void
    {
        $curator = $this->createCurator($this->owner);
        $other = $this->createCurator($this->owner);
        $event = $this->createEvent($this->role);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $html = $this->actingAs($this->owner)->get($this->editUrl($event))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="curator_'.preg_quote($curator->encodeId(), '/').'"[^>]*\schecked/s', $html);
        $this->assertDoesNotMatchRegularExpression('/id="curator_'.preg_quote($other->encodeId(), '/').'"[^>]*\schecked/s', $html);
    }

    public function test_a_refused_link_ending_comes_back_open_with_its_message_and_is_sent_again(): void
    {
        $event = $this->createEvent($this->role, ['slug' => 'first-night']);
        $taken = $this->createEvent($this->role, ['slug' => 'second-night', 'starts_at' => $event->starts_at]);

        $this->actingAs($this->owner)->from($this->editUrl($event))
            ->put(route('event.update', ['subdomain' => 'keeptalent', 'hash' => UrlUtils::encodeId($event->id)]), [
                'name' => $event->name, 'starts_at' => $event->starts_at, 'duration' => 2, 'slug' => 'second-night',
            ])
            ->assertSessionHasErrors('slug');

        $html = $this->actingAs($this->owner)->get($this->editUrl($event))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<div id="event-slug-edit" class="\s*mt-1">/', $html, 'the editor is open');
        $this->assertMatchesRegularExpression('/<div id="event-url-display" class="event-picked mt-1 hidden">/', $html);
        preg_match('/<input[^>]*id="event_slug"[^>]*>/', $html, $input);
        $this->assertStringContainsString('value="second-night"', $input[0]);
        $this->assertStringNotContainsString('disabled', $input[0], 'a disabled field would not be sent again');

        // And an ordinary load keeps it closed and out of the save.
        $plain = $this->actingAs($this->owner)->get($this->editUrl($taken))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<div id="event-slug-edit" class="hidden mt-1">/', $plain);
        preg_match('/<input[^>]*id="event_slug"[^>]*>/', $plain, $input);
        $this->assertStringContainsString('disabled', $input[0]);
    }

    /** The value of one key of the page's Vue data, decoded. */
    private function pageData(string $html, string $key): mixed
    {
        $this->assertSame(1, preg_match('/^\s*'.preg_quote($key, '/').': (.+?),?\s*$/m', $html, $m), "{$key} is in the page data");

        return json_decode(preg_replace('/\)?\.map\(.*$/', '', rtrim($m[1], ',')), true);
    }

    public function test_participants_come_back_as_they_were_being_edited(): void
    {
        $guest = $this->createRole($this->createOwner(), 'talent', ['name' => 'Stored Guest', 'email' => 'stored@example.org']);
        \Illuminate\Support\Facades\DB::table('roles')->where('id', $guest->id)->update(['user_id' => null, 'email_verified_at' => null]);
        $event = $this->createEvent($this->role);
        $event->roles()->attach($guest->id, ['is_accepted' => true]);
        $own = UrlUtils::encodeId($this->role->id);
        $theirs = UrlUtils::encodeId($guest->id);

        $html = $this->refused($event, ['members_submitted' => 1, 'members' => [
            $own => ['name' => $this->role->name, 'email' => ''],
            $theirs => ['name' => 'Renamed Guest', 'email' => 'renamed@example.org', 'phone' => '', 'youtube_url' => ''],
            'new_17' => ['name' => 'Brand New Act', 'email' => 'new@example.org', 'phone' => '', 'youtube_url' => ''],
        ]]);

        $members = collect($this->pageData($html, 'selectedMembers'))->keyBy('id');
        $this->assertSame('Renamed Guest', $members[$theirs]['name']);
        $this->assertSame('renamed@example.org', $members[$theirs]['email']);
        $this->assertSame('Brand New Act', $members['new_17']['name'], 'one added and not yet saved is still there');
        $this->assertCount(3, $members);
    }

    public function test_a_participant_removed_before_the_refusal_stays_removed(): void
    {
        $guest = $this->createRole($this->createOwner(), 'talent', ['name' => 'Stored Guest']);
        $event = $this->createEvent($this->role);
        $event->roles()->attach($guest->id, ['is_accepted' => true]);

        $html = $this->refused($event, ['members_submitted' => 1, 'members' => [UrlUtils::encodeId($this->role->id) => ['name' => $this->role->name, 'email' => '']]]);

        $this->assertSame([UrlUtils::encodeId($this->role->id)], array_column($this->pageData($html, 'selectedMembers'), 'id'));
    }

    public function test_agenda_parts_come_back_as_they_were_being_edited(): void
    {
        $event = $this->createEvent($this->role);
        $part = EventPart::create(['event_id' => $event->id, 'name' => 'Stored part', 'start_time' => '19:00', 'sort_order' => 0]);

        $html = $this->refused($event, ['agenda_show_times' => '1', 'event_parts' => [
            ['id' => $part->id, 'name' => 'Renamed part', 'start_time' => '19:30', 'end_time' => '', 'description' => ''],
            ['id' => '', 'name' => 'Typed and not saved', 'start_time' => '', 'end_time' => '', 'description' => 'Its description'],
        ]]);

        $parts = $this->pageData($html, 'eventParts');
        $this->assertSame(['Renamed part', 'Typed and not saved'], array_column($parts, 'name'));
        $this->assertSame('19:30', $parts[0]['start_time']);
        $this->assertSame('Its description', $parts[1]['description']);
        $this->assertStringContainsString('partUidCounter: 2,', $html);
    }

    public function test_polls_come_back_as_they_were_being_edited(): void
    {
        $event = $this->createEvent($this->role);
        $poll = EventPoll::create(['event_id' => $event->id, 'question' => 'Stored question?', 'options' => ['A', 'B'], 'is_active' => true, 'sort_order' => 0]);

        $html = $this->refused($event, ['polls' => [
            ['hash' => UrlUtils::encodeId($poll->id), 'question' => 'Edited question?', 'options' => json_encode(['A', 'B', 'C']), 'allow_user_options' => '1', 'require_option_approval' => '0'],
            ['hash' => '', 'question' => 'A new one?', 'options' => json_encode(['Yes', 'No']), 'allow_user_options' => '0', 'require_option_approval' => '0'],
        ]]);

        $polls = $this->pageData($html, 'polls');
        $this->assertSame(['Edited question?', 'A new one?'], array_column($polls, 'question'));
        $this->assertSame(['A', 'B', 'C'], $polls[0]['options']);
        $this->assertTrue($polls[0]['allow_user_options']);
        $this->assertSame(UrlUtils::encodeId($poll->id), $polls[0]['hash'], 'still the stored poll');
        $this->assertNull($polls[1]['hash']);
    }

    public function test_the_sponsors_choice_and_the_engagement_settings_come_back(): void
    {
        $event = $this->createEvent($this->role);

        $html = $this->refused($event, ['sponsor_mode' => 'none', 'fan_comments_enabled' => '0', 'fan_photos_enabled' => '', 'fan_videos_enabled' => '1', 'feedback_enabled' => '1']);

        $this->assertStringContainsString('sponsor_mode: "none",', $html);
        $this->assertSame(
            ['fan_comments_enabled' => '0', 'fan_photos_enabled' => '', 'fan_videos_enabled' => '1', 'feedback_enabled' => '1'],
            $this->pageData($html, 'engagementSettings')
        );
        $this->assertMatchesRegularExpression('/<input type="radio" class="sr-only peer" name="fan_comments_enabled" value="0"[^>]*checked/', $html);
    }

    /** The list a line of the page's data is seeded with, decoded. */
    private function seeded(string $html, string $pattern): array
    {
        $this->assertSame(1, preg_match($pattern, $html, $match), 'the seed was not found on the page: '.$pattern);

        return json_decode($match[1], true, 512, JSON_THROW_ON_ERROR);
    }

    private function ticketSeed(string $html): array
    {
        return $this->seeded($html, '/^\s*tickets: (\[.*\])\.map\(\(ticket, i\) => \(\{$/m');
    }

    /**
     * "Sell tickets" came back chosen and the ticket types did not: the page showed one blank row,
     * and the next save published one free, unlimited ticket in place of the three that were typed.
     */
    public function test_ticket_types_promo_codes_and_add_ons_typed_before_a_refused_save_come_back(): void
    {
        $event = $this->createEvent($this->role);
        $this->assertSame(0, $event->tickets()->count());

        $html = $this->refused($event, [
            'tickets_enabled' => 1, 'rsvp_enabled' => 0,
            'tickets' => [
                ['id' => '', 'type' => 'General', 'quantity' => '100', 'price' => '25', 'description' => 'Standing', 'is_pass' => '0', 'pass_allow_booking' => '0',
                    'custom_fields' => '{}', 'volume_discount' => '', 'pass_event_ids' => '[]', 'sales_start_at' => '2026-11-01 09:00', 'sales_end_at' => ''],
                ['id' => '', 'type' => 'VIP', 'quantity' => '10', 'price' => '80', 'is_pass' => '0', 'volume_discount' => '{"min_quantity":4,"percent":10}'],
            ],
            'promo_codes' => [['id' => '', 'code' => 'EARLY', 'type' => 'percentage', 'value' => '10', 'max_uses' => '', 'expires_at' => '', 'is_active' => '0', 'ticket_ids' => '[]']],
            'addons' => [['id' => '', 'type' => 'T-shirt', 'quantity' => '50', 'price' => '15', 'description' => 'Cotton', 'url' => '', 'remove_image' => '0']],
        ]);

        $this->assertMatchesRegularExpression('/ticketMode: "tickets"/', $html, 'the choice came back, as it did before');

        $tickets = $this->ticketSeed($html);
        $this->assertSame(['General', 'VIP'], array_column($tickets, 'type'), 'and so do the types that were typed');
        $this->assertEquals(25, $tickets[0]['price']);
        $this->assertSame('100', (string) $tickets[0]['quantity']);
        $this->assertSame('Standing', $tickets[0]['description']);
        $this->assertFalse($tickets[0]['is_pass'], 'a posted "0" is not a pass');
        $this->assertSame('2026-11-01 09:00', $tickets[0]['sales_start_at']);
        $this->assertSame(['min_quantity' => 4, 'percent' => 10], $tickets[1]['volume_discount']);

        $promos = $this->seeded($html, '/^\s*var pcs = (\[.*\])\.map\(pc => \(\{$/m');
        $this->assertSame(['EARLY'], array_column($promos, 'code'));
        $this->assertFalse($promos[0]['is_active'], 'switched off before the refusal, still off');

        $addons = $this->seeded($html, '/^\s*addons: (\[.*\])\.map\(\(addon, i\) => \(\{$/m');
        $this->assertSame(['T-shirt'], array_column($addons, 'type'));
        $this->assertSame('Cotton', $addons[0]['description']);
    }

    /** A stored ticket type comes back as it was being edited, still the same row. */
    public function test_a_stored_ticket_type_comes_back_with_what_was_typed_over_it(): void
    {
        $event = $this->createEvent($this->role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 20, 'quantity' => 50]);
        $gone = $this->createTicket($event, ['type' => 'Removed before the refusal', 'price' => 5]);

        $html = $this->refused($event, [
            'tickets_enabled' => 1, 'rsvp_enabled' => 0,
            'tickets' => [['id' => (string) $ticket->id, 'type' => 'General admission', 'quantity' => '60', 'price' => '22', 'is_pass' => '0']],
        ]);

        $tickets = $this->ticketSeed($html);
        $this->assertCount(1, $tickets, 'a type removed before the refusal stays removed');
        $this->assertSame($ticket->id, (int) $tickets[0]['id']);
        $this->assertSame('General admission', $tickets[0]['type']);
        $this->assertEquals(22, $tickets[0]['price']);
        $this->assertArrayHasKey('event_id', $tickets[0], 'what the row knew about its stored self is kept');
        $this->assertNotContains($gone->id, array_map('intval', array_column($tickets, 'id')));
    }

    public function test_without_a_refused_save_the_ticket_types_are_what_is_stored(): void
    {
        $event = $this->createEvent($this->role, ['tickets_enabled' => true]);
        $this->createTicket($event, ['type' => 'General', 'price' => 20]);

        $html = $this->actingAs($this->owner)->get($this->editUrl($event))->assertOk()->getContent();

        $this->assertSame(['General'], array_column($this->ticketSeed($html), 'type'));
        $this->assertStringContainsString('isDirty: false,', $html, 'and nothing is unsaved');
    }

    /**
     * A saved sponsor given a new logo is posted as an upload, and an upload does not survive a
     * refused save. The sponsor used to vanish from the list with it, and the next save deleted it
     * and its stored logo.
     */
    public function test_a_sponsor_being_given_a_new_logo_is_not_lost_by_a_refused_save(): void
    {
        $stored = [
            ['name' => 'Duff', 'logo' => 'sponsor_aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png', 'url' => 'https://duff.example.org', 'tier' => 'gold'],
            ['name' => 'Buzz Cola', 'logo' => 'sponsor_bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb.png', 'url' => null, 'tier' => ''],
        ];
        $event = $this->createEvent($this->role, ['sponsor_mode' => 'custom', 'sponsor_logos' => json_encode($stored)]);

        $html = $this->refused($event, [
            'sponsor_mode' => 'custom',
            // Duff is untouched; Buzz Cola was renamed and given a new logo; Krusty Burger is new.
            'existing_event_sponsors' => json_encode([$stored[0]]),
            'pending_event_sponsors' => json_encode([
                ['logo' => $stored[1]['logo'], 'name' => 'Buzz Cola Zero', 'url' => 'https://buzz.example.org', 'tier' => 'silver'],
                ['logo' => '', 'name' => 'Krusty Burger', 'url' => 'https://krusty.example.org', 'tier' => 'gold'],
            ]),
        ]);

        $sponsors = $this->seeded($html, '/^\s*eventSponsors: (\[.*\]),$/m');
        $this->assertSame(['Duff', 'Buzz Cola Zero'], array_column($sponsors, 'name'), 'the stored sponsor is still on the list, with what was typed');
        $this->assertSame($stored[1]['logo'], $sponsors[1]['logo'], 'under the logo it has, since the new one did not arrive');

        // The brand-new one cannot come back without its file: its form is open with what was typed.
        $this->assertStringContainsString('sponsorFormOpen: true,', $html);
        $this->assertSame(['name' => 'Krusty Burger', 'url' => 'https://krusty.example.org', 'tier' => 'gold'], $this->seeded($html, '/^\s*sponsorForm: (\{.*\}),$/m'));
        $this->assertStringContainsString('sponsorFormError: '.json_encode(__('messages.sponsor_needs_logo')).',', $html);
    }

    /** A page that came back from a refusal holds edits that are not saved, and says so. */
    public function test_a_refused_page_starts_as_unsaved(): void
    {
        $event = $this->createEvent($this->role);

        $html = $this->refused($event, ['is_draft' => 1]);

        $this->assertStringContainsString('isDirty: true,', $html, 'Cancel asks before leaving, and the browser warns');
    }

    /** An agenda that has times opens with them shown, whatever the schedule did last. */
    public function test_an_agenda_with_times_opens_showing_them(): void
    {
        $this->role->forceFill(['agenda_show_times' => false])->save();
        $event = $this->createEvent($this->role);
        EventPart::create(['event_id' => $event->id, 'name' => 'Doors', 'start_time' => '19:00', 'end_time' => '20:00', 'sort_order' => 0]);

        $html = $this->actingAs($this->owner)->get($this->editUrl($event))->assertOk()->getContent();
        $this->assertStringContainsString('agendaShowTimes: true,', $html, 'opening it set to remove its times was nobody\'s choice');

        // An agenda with no times follows the schedule's habit, as a new one does.
        $plain = $this->createEvent($this->role);
        EventPart::create(['event_id' => $plain->id, 'name' => 'Doors', 'sort_order' => 0]);
        $this->assertStringContainsString('agendaShowTimes: false,', $this->actingAs($this->owner)->get($this->editUrl($plain))->assertOk()->getContent());

        // And the form does not answer a question it no longer asks.
        $this->assertStringNotContainsString('name="agenda_show_description"', $html);
    }

    /** Add-ons go with the ticket types when tickets are switched off: the page knows how many. */
    public function test_the_page_knows_what_switching_tickets_off_would_remove(): void
    {
        $event = $this->createEvent($this->role, ['tickets_enabled' => true]);
        $this->createTicket($event, ['type' => 'General', 'price' => 20]);
        $this->createTicket($event, ['type' => 'T-shirt', 'price' => 15, 'is_addon' => true]);

        $html = $this->actingAs($this->owner)->get($this->editUrl($event))->assertOk()->getContent();

        $this->assertStringContainsString('savedAddonCount: 1,', $html);
        $this->assertStringContainsString(json_encode(__('messages.saving_removes_event_addons')), $html);
    }

    public function test_an_ordinary_load_shows_what_is_stored(): void
    {
        $event = $this->createEvent($this->role, ['fan_comments_enabled' => false]);
        EventPart::create(['event_id' => $event->id, 'name' => 'Stored part', 'sort_order' => 0]);

        $html = $this->actingAs($this->owner)->get($this->editUrl($event))->assertOk()->getContent();

        $this->assertSame(['Stored part'], array_column($this->pageData($html, 'eventParts'), 'name'));
        $this->assertSame('0', $this->pageData($html, 'engagementSettings')['fan_comments_enabled']);
        $this->assertSame([UrlUtils::encodeId($this->role->id)], array_column($this->pageData($html, 'selectedMembers'), 'id'));
    }

    /**
     * One field of the page's event data as the browser will hold it: what was typed over what is
     * stored, which is the order the page spreads them in.
     */
    private function eventField(string $html, string $key): mixed
    {
        $typed = $this->typedTicketFields($html);
        if (array_key_exists($key, $typed)) {
            return $typed[$key];
        }
        if (preg_match('/^\s*'.preg_quote($key, '/').': (.+?),?\s*$/m', $html, $m)) {
            return json_decode(rtrim($m[1], ','), true);
        }
        $this->assertSame(1, preg_match('/^\s*event: \{\s*\n\s*\.\.\.(\{.*\}),\s*$/m', $html, $m), 'the stored event is in the page data');

        return json_decode($m[1], true)[$key] ?? null;
    }

    /** What the page lays over the stored event: the Tickets tab's fields as they were typed. */
    private function typedTicketFields(string $html, bool $must = false): array
    {
        if (! preg_match('/^\s*\.\.\.(\{.*\}), \/\/ typed\s*$/m', $html, $m)) {
            $this->assertFalse($must, 'the typed fields are in the page data');

            return [];
        }

        return json_decode($m[1], true);
    }

    /**
     * The choice came back and what was typed under it did not. A limit typed, the save refused,
     * and the page came back with the limit blank: the next save made registration unlimited.
     */
    public function test_a_registration_limit_typed_before_a_refused_save_comes_back(): void
    {
        $event = $this->createEvent($this->role);

        $html = $this->refused($event, ['tickets_enabled' => 0, 'rsvp_enabled' => 1, 'rsvp_limit' => '50']);

        $this->assertSame(1, preg_match('/ticketMode: "rsvp"/', $html), 'the choice came back, as it did before');
        $this->assertSame(50, $this->eventField($html, 'rsvp_limit'), 'and so does the limit, as a number');
        $this->assertNull(Event::find($event->id)->rsvp_limit, 'sanity check: the save really was refused');
    }

    /** The other way round: a stored limit taken off must not come back filled in. */
    public function test_a_stored_limit_cleared_before_a_refused_save_comes_back_cleared(): void
    {
        $event = $this->createEvent($this->role, ['rsvp_enabled' => true, 'rsvp_limit' => 40]);

        $html = $this->refused($event, ['tickets_enabled' => 0, 'rsvp_enabled' => 1, 'rsvp_limit' => '']);

        $this->assertNull($this->eventField($html, 'rsvp_limit'));
        $this->assertSame(40, (int) Event::find($event->id)->rsvp_limit, 'sanity check: the save really was refused');
    }

    /** A link typed under "Tickets elsewhere" on an event that had none came back as "Not needed". */
    public function test_a_ticket_link_typed_before_a_refused_save_comes_back_chosen(): void
    {
        $event = $this->createEvent($this->role);

        $html = $this->refused($event, [
            'tickets_enabled' => 0, 'rsvp_enabled' => 0,
            'registration_url' => 'https://tickets.example.org/jazz', 'ticket_price' => '12.50',
            'coupon_code' => 'EARLY', 'coupon_discount_type' => 'percentage', 'coupon_discount' => '10',
        ]);

        $this->assertSame(1, preg_match('/ticketMode: "external"/', $html));
        $this->assertTrue($this->pageData($html, 'externalChosen'), '"Tickets elsewhere" is the choice on screen');
        $this->assertSame('https://tickets.example.org/jazz', $this->eventField($html, 'registration_url'));
        $this->assertSame(12.5, $this->eventField($html, 'ticket_price'));
        $this->assertSame('EARLY', $this->eventField($html, 'coupon_code'));
        $this->assertSame('percentage', $this->eventField($html, 'coupon_discount_type'));
        $this->assertSame(10, $this->eventField($html, 'coupon_discount'));
        // What the event is saved with has not moved: it is what a half-typed link falls back to.
        $this->assertSame('', $this->pageData($html, 'savedExternal')['registration_url']);
    }

    /** "Not needed" chosen over a stored link came back as "Tickets elsewhere" with the link. */
    public function test_not_needed_chosen_over_a_stored_link_comes_back_as_not_needed(): void
    {
        $event = $this->createEvent($this->role, ['registration_url' => 'https://tickets.example.org/jazz']);

        $html = $this->refused($event, [
            'tickets_enabled' => 0, 'rsvp_enabled' => 0,
            'registration_url' => '', 'ticket_price' => '', 'coupon_code' => '', 'coupon_discount_type' => 'fixed', 'coupon_discount' => '',
        ]);

        $this->assertFalse($this->pageData($html, 'externalChosen'));
        $this->assertNull($this->eventField($html, 'registration_url'));
        $this->assertSame('https://tickets.example.org/jazz', $this->pageData($html, 'savedExternal')['registration_url'], 'what is saved is still known');
        $this->assertSame('https://tickets.example.org/jazz', Event::find($event->id)->registration_url, 'sanity check: the save really was refused');
    }

    /**
     * The fields of a choice that is not the one on screen ride in hidden fields. One of those the
     * server refuses must not come back: nobody could see it to fix it, and every save after would
     * be refused the same way. It goes back to what the event is saved with.
     */
    public function test_a_refused_field_of_a_choice_that_is_not_on_screen_goes_back_to_what_is_saved(): void
    {
        $event = $this->createEvent($this->role, ['tickets_enabled' => true, 'coupon_code' => 'STORED']);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 20]);

        $this->actingAs($this->owner)->from($this->editUrl($event))
            ->put(route('event.update', ['subdomain' => 'keeptalent', 'hash' => UrlUtils::encodeId($event->id)]), [
                'name' => 'Still named', 'starts_at' => $event->starts_at, 'duration' => 2,
                'tickets_enabled' => 1, 'rsvp_enabled' => 0, 'coupon_code' => str_repeat('x', 300), 'registration_url' => 'https://typed.example.org',
                'tickets' => [['id' => (string) $ticket->id, 'type' => 'General', 'price' => '20', 'is_pass' => '0']],
            ])
            ->assertRedirect($this->editUrl($event))
            ->assertSessionHasErrors('coupon_code');
        $html = $this->actingAs($this->owner)->get($this->editUrl($event))->assertOk()->getContent();

        $this->assertSame('STORED', $this->eventField($html, 'coupon_code'), 'the refused one is not sent again');
        $this->assertSame('https://typed.example.org', $this->eventField($html, 'registration_url'), 'the rest came back as it was');
    }

    /** With "Tickets elsewhere" on screen the refused field is right there, with its message. */
    public function test_a_refused_field_of_the_choice_on_screen_comes_back_as_typed(): void
    {
        $event = $this->createEvent($this->role, ['coupon_code' => 'STORED', 'registration_url' => 'https://tickets.example.org/jazz']);

        $this->actingAs($this->owner)->from($this->editUrl($event))
            ->put(route('event.update', ['subdomain' => 'keeptalent', 'hash' => UrlUtils::encodeId($event->id)]), [
                'name' => 'Still named', 'starts_at' => $event->starts_at, 'duration' => 2,
                'tickets_enabled' => 0, 'rsvp_enabled' => 0, 'coupon_code' => str_repeat('x', 300), 'registration_url' => 'https://tickets.example.org/jazz',
            ])
            ->assertRedirect($this->editUrl($event))
            ->assertSessionHasErrors('coupon_code');
        $html = $this->actingAs($this->owner)->get($this->editUrl($event))->assertOk()->getContent();

        $this->assertSame(str_repeat('x', 300), $this->eventField($html, 'coupon_code'));
    }

    /** The Payment and Options rows: switches, the currency and the three texts. */
    public function test_the_payment_and_options_rows_come_back_as_they_were_left(): void
    {
        $event = $this->createEvent($this->role, ['tickets_enabled' => true, 'ask_phone' => false, 'sell_after_start' => true, 'ticket_currency_code' => 'USD']);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 20]);

        $html = $this->refused($event, [
            'tickets_enabled' => 1, 'rsvp_enabled' => 0,
            'tickets' => [['id' => (string) $ticket->id, 'type' => 'General', 'price' => '20', 'is_pass' => '0']],
            'ask_phone' => '1', 'require_phone' => '1', 'sell_after_start' => '0', 'show_unavailable_tickets' => '1',
            'ticket_currency_code' => 'EUR', 'total_tickets_mode' => 'combined',
            'payment_instructions' => 'Pay at the door', 'ticket_notes' => 'Bring this ticket', 'terms_url' => 'https://example.org/terms',
            'expire_unpaid_tickets' => '6',
        ]);

        $this->assertTrue($this->eventField($html, 'ask_phone'));
        $this->assertTrue($this->eventField($html, 'require_phone'));
        $this->assertFalse($this->eventField($html, 'sell_after_start'), 'a posted "0" is off, not "on because it is not empty"');
        $this->assertTrue($this->eventField($html, 'show_unavailable_tickets'));
        $this->assertSame('EUR', $this->eventField($html, 'ticket_currency_code'));
        $this->assertSame('combined', $this->eventField($html, 'total_tickets_mode'));
        $this->assertSame('Pay at the door', $this->eventField($html, 'payment_instructions'));
        $this->assertSame('Bring this ticket', $this->eventField($html, 'ticket_notes'));
        $this->assertSame('https://example.org/terms', $this->eventField($html, 'terms_url'));
        $this->assertSame(6, $this->eventField($html, 'expire_unpaid_tickets'));
        $this->assertTrue($this->pageData($html, 'showExpireUnpaid'), 'and its switch is on to show it');
    }

    /** Nothing typed, nothing laid over: an ordinary load is exactly what is stored. */
    public function test_an_ordinary_load_lays_nothing_over_the_stored_event(): void
    {
        $event = $this->createEvent($this->role, ['rsvp_enabled' => true, 'rsvp_limit' => 40, 'registration_url' => 'https://tickets.example.org/jazz']);

        $html = $this->actingAs($this->owner)->get($this->editUrl($event))->assertOk()->getContent();

        $this->assertSame([], $this->typedTicketFields($html, true));
        $this->assertSame(40, $this->eventField($html, 'rsvp_limit'));
        $this->assertTrue($this->pageData($html, 'externalChosen'));
    }

    /**
     * Ticket types are posted whether tickets are on or off; promo codes and add-ons only when on
     * (their fieldsets are disabled otherwise). A refused save made with tickets off took all three
     * lists from the post, so the codes and add-ons came back empty, and switching "Sell tickets"
     * back on and saving deleted every one that was stored.
     */
    public function test_promo_codes_and_add_ons_stay_as_stored_when_the_refused_save_had_tickets_off(): void
    {
        $event = $this->createEvent($this->role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 20]);
        $addon = $this->createTicket($event, ['type' => 'T-shirt', 'price' => 15, 'is_addon' => true]);
        $promo = \App\Models\PromoCode::create(['event_id' => $event->id, 'code' => 'EARLY', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);

        // "Not needed" pressed, then the save refused: the ticket row is still posted, the rest is not.
        $html = $this->refused($event, [
            'tickets_enabled' => 0, 'rsvp_enabled' => 0,
            'tickets' => [['id' => (string) $ticket->id, 'type' => 'General renamed', 'price' => '20', 'is_pass' => '0']],
        ]);

        $this->assertSame(['General renamed'], array_column($this->ticketSeed($html), 'type'), 'the ticket row was posted, and comes back as typed');
        $promos = $this->seeded($html, '/^\s*var pcs = (\[.*\])\.map\(pc => \(\{$/m');
        $this->assertSame([$promo->id], array_map('intval', array_column($promos, 'id')), 'the codes were not in the post, so they are what is stored');
        $addons = $this->seeded($html, '/^\s*addons: (\[.*\])\.map\(\(addon, i\) => \(\{$/m');
        $this->assertSame([$addon->id], array_map('intval', array_column($addons, 'id')), 'and so are the add-ons');

        // "Sell tickets" pressed again and saved, with what that page holds.
        $this->actingAs($this->owner)->from($this->editUrl($event))
            ->put(route('event.update', ['subdomain' => 'keeptalent', 'hash' => UrlUtils::encodeId($event->id)]), [
                'name' => 'Saved this time', 'starts_at' => $event->starts_at, 'duration' => 2,
                'tickets_enabled' => 1, 'rsvp_enabled' => 0,
                'tickets' => [['id' => (string) $ticket->id, 'type' => 'General renamed', 'price' => '20', 'is_pass' => '0']],
                'promo_codes' => array_map(fn ($p) => ['id' => (string) $p['id'], 'code' => $p['code'], 'type' => $p['type'], 'value' => (string) $p['value'], 'is_active' => '1', 'ticket_ids' => '[]'], $promos),
                'addons' => array_map(fn ($a) => ['id' => (string) $a['id'], 'type' => $a['type'], 'price' => (string) $a['price'], 'remove_image' => '0'], $addons),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Saved this time', Event::find($event->id)->name, 'sanity check: this one saved');
        $this->assertNotNull(\App\Models\PromoCode::find($promo->id), 'the stored promo code is still there');
        $this->assertFalse((bool) \App\Models\Ticket::find($addon->id)->is_deleted, 'and so is the add-on');
    }

    /** An add-on's new picture is sent as text, not as a file, so it can come back. */
    public function test_an_add_on_picture_chosen_before_a_refused_save_comes_back(): void
    {
        $event = $this->createEvent($this->role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 20]);
        $picture = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

        $html = $this->refused($event, [
            'tickets_enabled' => 1, 'rsvp_enabled' => 0,
            'tickets' => [['id' => (string) $ticket->id, 'type' => 'General', 'price' => '20', 'is_pass' => '0']],
            'addons' => [['id' => '', 'type' => 'T-shirt', 'price' => '15', 'remove_image' => '0'], ['id' => '', 'type' => 'Poster', 'price' => '5', 'remove_image' => '0']],
            'addon_image_data' => [1 => $picture],
        ]);

        $addons = $this->seeded($html, '/^\s*addons: (\[.*\])\.map\(\(addon, i\) => \(\{$/m');
        $this->assertSame(['T-shirt', 'Poster'], array_column($addons, 'type'));
        $this->assertEmpty($addons[0]['image_url'] ?? null);
        $this->assertSame($picture, $addons[1]['image_url'], 'the picture chosen for the second add-on is on the second add-on');
    }

    /** A picture taken off before the refusal is not shown again while its removal is still sent. */
    public function test_an_add_on_picture_removed_before_a_refused_save_stays_removed(): void
    {
        $event = $this->createEvent($this->role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 20]);
        $addon = $this->createTicket($event, ['type' => 'T-shirt', 'price' => 15, 'is_addon' => true]);
        \Illuminate\Support\Facades\DB::table('tickets')->where('id', $addon->id)->update(['image_url' => 'addon_stored.png']);

        $html = $this->refused($event, [
            'tickets_enabled' => 1, 'rsvp_enabled' => 0,
            'tickets' => [['id' => (string) $ticket->id, 'type' => 'General', 'price' => '20', 'is_pass' => '0']],
            'addons' => [['id' => (string) $addon->id, 'type' => 'T-shirt', 'price' => '15', 'remove_image' => '1']],
        ]);

        $addons = $this->seeded($html, '/^\s*addons: (\[.*\])\.map\(\(addon, i\) => \(\{$/m');
        $this->assertTrue($addons[0]['remove_image']);
        $this->assertEmpty($addons[0]['image_url'], 'the page does not show a picture it is about to remove');
    }

    /** The id the page's venue picker starts on, or null when it starts on none. */
    private function pickedVenue(string $html): ?string
    {
        $this->assertSame(1, preg_match('/^\s*selectedVenue: (.*),\s*$/m', $html, $seed), 'the venue seed was not found on the page');
        $venue = json_decode($seed[1], true);

        return is_array($venue) ? ($venue['id'] ?? null) : null;
    }

    /** An event of the schedule's at a venue its owner also runs. */
    private function eventAt(Role $venue): Event
    {
        $event = $this->createEvent($this->role);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        return $event;
    }

    /**
     * The venue picker was seeded from the stored event alone. Another venue chosen and a save
     * refused over another field came back on the old venue, with nothing on the page to say so,
     * and fixing that field and saving kept the event where it had been.
     */
    public function test_a_venue_chosen_before_a_refused_save_is_the_one_the_page_comes_back_on(): void
    {
        $venueA = $this->createVenueWithAddress($this->owner, ['name' => 'Venue A']);
        $venueB = $this->createVenueWithAddress($this->owner, ['name' => 'Venue B', 'address1' => '9 Other St']);
        $event = $this->eventAt($venueA);

        $html = $this->refused($event, ['venue_id' => UrlUtils::encodeId($venueB->id), 'venue_submitted' => 1]);

        $this->assertSame(UrlUtils::encodeId($venueB->id), $this->pickedVenue($html));
        // A change is still measured against what is stored, so the question about telling the
        // people who signed up is still asked when the save goes through.
        $this->assertStringContainsString('savedVenueId: "'.UrlUtils::encodeId($venueA->id).'",', $html);
        $this->assertStringContainsString('this.origVenueId = this.eventIsSaved ? this.savedVenueId', $html);
        $this->assertSame([$venueA->id], $event->roles()->where('roles.type', 'venue')->pluck('roles.id')->all(), 'sanity check: the save really was refused');
    }

    public function test_a_venue_chosen_before_a_refused_first_save_comes_back_chosen(): void
    {
        $venue = $this->createVenueWithAddress($this->owner, ['name' => 'Venue A']);
        $createUrl = route('event.create', ['subdomain' => 'keeptalent']);

        $this->actingAs($this->owner)->from($createUrl)
            ->post(route('event.store', ['subdomain' => 'keeptalent']), [
                'name' => '', 'starts_at' => '2026-08-15 20:00:00', 'duration' => 2,
                'venue_id' => UrlUtils::encodeId($venue->id), 'venue_submitted' => 1,
            ])
            ->assertRedirect($createUrl)
            ->assertSessionHasErrors('name');

        $html = $this->actingAs($this->owner)->get($createUrl)->assertOk()->getContent();

        $this->assertSame(UrlUtils::encodeId($venue->id), $this->pickedVenue($html));
    }

    public function test_a_venue_taken_off_before_a_refused_save_stays_off(): void
    {
        $venue = $this->createVenueWithAddress($this->owner, ['name' => 'Venue A']);
        $event = $this->eventAt($venue);

        $html = $this->refused($event, ['venue_submitted' => 1]);

        $this->assertNull($this->pickedVenue($html), 'the venue that was taken off does not come back as though it were chosen');
    }

    /** The id is whatever was posted, and a venue's data on the page is the whole schedule. */
    public function test_a_refused_save_does_not_look_up_a_venue_the_page_did_not_offer(): void
    {
        $venue = $this->createVenueWithAddress($this->owner, ['name' => 'Venue A']);
        $theirs = $this->createVenueWithAddress($this->createOwner(), ['name' => 'Somebody Elses Room', 'email' => 'private-room@gmail.com']);
        $event = $this->eventAt($venue);

        $html = $this->refused($event, ['venue_id' => UrlUtils::encodeId($theirs->id), 'venue_submitted' => 1]);

        $this->assertStringNotContainsString('private-room@gmail.com', $html);
        $this->assertNull($this->pickedVenue($html));
    }

    public function test_an_ordinary_load_starts_on_the_stored_venue(): void
    {
        $venue = $this->createVenueWithAddress($this->owner, ['name' => 'Venue A']);
        $event = $this->eventAt($venue);

        $html = $this->actingAs($this->owner)->get($this->editUrl($event))->assertOk()->getContent();

        $this->assertSame(UrlUtils::encodeId($venue->id), $this->pickedVenue($html));
        $this->assertStringContainsString('savedVenueId: "'.UrlUtils::encodeId($venue->id).'",', $html);
    }

    /**
     * Before the page's script runs a browser shows whatever is not hidden from it. The Save button
     * read "{{ galleryFinishingText }}Saving..." on every load: two of its three labels had no
     * v-cloak. Now one label shows, the one the script will choose.
     */
    public function test_the_save_button_shows_one_label_before_the_page_script_runs(): void
    {
        foreach ([[[], __('messages.save')], [['is_draft' => true], __('messages.save_draft')]] as [$attrs, $label]) {
            $event = $this->createEvent($this->role, $attrs);
            $html = $this->actingAs($this->owner)->get($this->editUrl($event))->assertOk()->getContent();

            $this->assertSame(1, preg_match('/<button[^>]*class="[^"]*\bevent-bar-save\b[^"]*"[^>]*>(.*?)<\/button>/s', $html, $button), 'the Save button is on the page');
            preg_match_all('/<span([^>]*)>(.*?)<\/span>/s', $button[1], $spans, PREG_SET_ORDER);
            $shown = array_values(array_filter($spans, fn ($span) => ! str_contains($span[1], 'v-cloak')));
            $this->assertCount(1, $shown, 'one label is not hidden until the script runs');
            $this->assertSame($label, trim($shown[0][2]), 'and it is the one the script will choose');
            $this->assertStringContainsString('v-if="false"', $shown[0][1], 'which the script takes away when it mounts');
        }
    }
}
