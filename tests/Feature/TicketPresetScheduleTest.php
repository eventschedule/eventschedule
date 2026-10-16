<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\TicketPreset;
use App\Models\User;
use App\Services\TicketPresets;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Issue #130: Edit schedule > Settings > Ticket presets (RoleController::update() through
 * App\Services\TicketPresets::saveFromSchedule()), and "Save these tickets as a preset" on the
 * event form, which took over from "Save as default" (roles.default_tickets).
 */
class TicketPresetScheduleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => false]);
    }

    private function save(User $user, Role $role, array $fields = []): TestResponse
    {
        return $this->actingAs($user)
            ->from(route('role.edit', ['subdomain' => $role->subdomain]))
            ->put(route('role.update', ['subdomain' => $role->subdomain]), array_merge([
                'name' => $role->name,
                'email' => $role->email,
                'timezone' => $role->timezone,
                'new_subdomain' => $role->subdomain,
            ], $fields));
    }

    private function row(array $row = []): array
    {
        return $row + [
            'id' => '', 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => 0,
            'currency_code' => 'EUR', 'payment_method' => 'cash', 'payment_instructions' => 'Pay at the door',
            'tickets' => [['type' => 'Entry', 'price' => '5', 'quantity' => ''], ['type' => 'Reduced', 'price' => '3', 'quantity' => '']],
        ];
    }

    public function test_the_owner_adds_presets_and_the_first_is_the_default(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $this->save($owner, $venue, [
            'ticket_presets_submitted' => 1,
            'ticket_presets' => [
                $this->row(),
                $this->row(['name' => 'Special', 'tickets' => [['type' => '', 'price' => '20', 'quantity' => '150']]]),
                $this->row(['name' => 'Free event', 'kind' => 'rsvp', 'rsvp_limit' => '80', 'tickets' => null]),
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $presets = TicketPresets::for($venue);
        $this->assertSame(['Regular', 'Special', 'Free event'], $presets->pluck('name')->all());
        $this->assertSame([true, false, false], $presets->pluck('is_default')->all());
        $this->assertSame('Entry €5, Reduced €3', $presets[0]->typesLine());
        $this->assertSame(150, $presets[1]->ticketRows()[0]['quantity']);
        $this->assertSame(80, $presets[2]->rsvpLimit());
    }

    public function test_every_ticket_type_in_a_preset_has_a_price(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $this->save($owner, $venue, [
            'ticket_presets_submitted' => 1,
            'ticket_presets' => [$this->row(['tickets' => [['type' => 'Entry', 'price' => '', 'quantity' => '']]])],
        ])->assertSessionHasErrors('ticket_presets.0.tickets.0.price');

        $this->assertSame(0, TicketPreset::count(), 'a blank price never saves as a free ticket');
    }

    public function test_editing_keeps_what_the_editor_does_not_show_and_removing_drops_the_rest(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $kept = TicketPreset::create(['role_id' => $venue->id, 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => true, 'settings' => [
            'currency_code' => 'EUR', 'payment_method' => 'cash', 'ticket_notes' => 'Bring your ID',
            'addons' => [['type' => 'Drink', 'price' => 2]],
            'tickets' => [['type' => 'Entry', 'price' => 5, 'description' => 'Standing']],
        ]]);
        $gone = TicketPreset::create(['role_id' => $venue->id, 'name' => 'Old', 'kind' => 'none', 'settings' => []]);

        $this->save($owner, $venue, [
            'ticket_presets_submitted' => 1,
            'ticket_presets_seen' => [$kept->encodeId(), $gone->encodeId()],
            'ticket_presets' => [$this->row(['id' => $kept->encodeId(), 'is_default' => 1, 'tickets' => [['src' => '0', 'type' => 'Entry', 'price' => '6', 'quantity' => '']]])],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $fresh = $kept->fresh();
        $this->assertSame(6.0, $fresh->ticketRows()[0]['price']);
        $this->assertSame('Standing', $fresh->ticketRows()[0]['description']);
        $this->assertSame('Bring your ID', $fresh->settings['ticket_notes']);
        $this->assertSame('Drink', $fresh->settings['addons'][0]['type']);
        $this->assertNull($gone->fresh());
    }

    public function test_a_type_keeps_its_own_hidden_fields_when_one_above_it_is_removed(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $preset = TicketPreset::create(['role_id' => $venue->id, 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => true, 'settings' => [
            'currency_code' => 'EUR', 'payment_method' => 'cash', 'payment_instructions' => 'Pay at the door',
            'tickets' => [
                ['type' => 'Entry', 'price' => 5, 'description' => 'Standing', 'volume_discount' => ['min' => 4, 'percent' => 10]],
                ['type' => 'Reduced', 'price' => 3, 'description' => 'Students'],
                ['type' => 'Season', 'price' => 80, 'is_pass' => true, 'pass_usage_type' => 'total', 'pass_max_uses' => 10],
            ],
        ]]);

        // Entry removed in the editor; Reduced was the second stored type. A row whose way of signing
        // up has no instructions does not send them, which must not erase them either.
        $this->save($owner, $venue, [
            'ticket_presets_submitted' => 1,
            'ticket_presets' => [['id' => $preset->encodeId(), 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => 1, 'currency_code' => 'EUR', 'payment_method' => 'cash',
                'tickets' => [['src' => '1', 'type' => 'Reduced', 'price' => '3', 'quantity' => '']]]],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $types = $preset->fresh()->settings['tickets'];
        $this->assertSame('Students', $types[0]['description'], 'not Entry\'s description, which sat in the first place');
        $this->assertArrayNotHasKey('volume_discount', $types[0]);
        $this->assertTrue($types[1]['is_pass'], 'a pass the editor does not show is kept');
        $this->assertSame('Pay at the door', $preset->fresh()->settings['payment_instructions']);
    }

    /** The island's props as the schedule form prints them. */
    private function editorProps(User $user, Role $role): array
    {
        $html = $this->actingAs($user)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
        $this->assertSame(1, preg_match('~<script[^>]*id="ticket-preset-items-json"[^>]*>(.*?)</script>~s', $html, $found));

        return json_decode($found[1], true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_a_refused_save_still_removes_what_was_removed_on_the_page(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $a = TicketPreset::create(['role_id' => $venue->id, 'name' => 'A', 'kind' => 'tickets', 'is_default' => true, 'sort_order' => 0, 'settings' => ['tickets' => [['type' => 'Entry', 'price' => 5]]]]);
        $b = TicketPreset::create(['role_id' => $venue->id, 'name' => 'B', 'kind' => 'tickets', 'is_default' => false, 'sort_order' => 1, 'settings' => ['tickets' => [['type' => 'Entry', 'price' => 9]]]]);
        $opened = ['ticket_presets_seen' => [$a->encodeId(), $b->encodeId()], 'ticket_presets_default_seen' => $a->encodeId()];

        // A removed, B made the default, and a new C with no price yet: refused for C.
        $this->save($owner, $venue, ['ticket_presets_submitted' => 1, 'ticket_presets' => [
            $this->row(['id' => $b->encodeId(), 'name' => 'B', 'is_default' => 1, 'tickets' => [['src' => '0', 'type' => 'Entry', 'price' => '9']]]),
            $this->row(['name' => 'C', 'tickets' => [['type' => 'Entry', 'price' => '']]]),
        ]] + $opened)->assertSessionHasErrors();
        $this->assertNotNull($a->fresh());

        // The page comes back knowing what it showed when it was first opened, not the redrawn rows.
        $props = $this->editorProps($owner, $venue);
        $this->assertSame([$a->encodeId(), $b->encodeId()], $props['seen']);
        $this->assertSame($a->encodeId(), $props['defaultSeenId']);

        // C's price typed, and saved as the page would post it.
        $this->save($owner, $venue, ['ticket_presets_submitted' => 1, 'ticket_presets_seen' => $props['seen'], 'ticket_presets_default_seen' => $props['defaultSeenId'], 'ticket_presets' => [
            $this->row(['id' => $b->encodeId(), 'name' => 'B', 'is_default' => 1, 'tickets' => [['src' => '0', 'type' => 'Entry', 'price' => '9']]]),
            $this->row(['name' => 'C', 'tickets' => [['type' => 'Entry', 'price' => '12']]]),
        ]])->assertSessionHasNoErrors();

        $this->assertNull($a->fresh(), 'removed on the page, so removed by the save');
        $this->assertTrue($b->fresh()->is_default, 'and the default is the one the page chose');
    }

    public function test_a_page_that_showed_no_presets_removes_none(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        // Saved from an event in another tab while this Settings page, opened with no presets, was open.
        $saved = TicketPreset::create(['role_id' => $venue->id, 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => true, 'settings' => ['tickets' => [['price' => 5]]]]);

        $this->save($owner, $venue, ['ticket_presets_submitted' => 1, 'ticket_presets_default_seen' => ''])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertNotNull($saved->fresh());
        $this->assertTrue($saved->fresh()->is_default);
    }

    public function test_a_preset_of_passes_only_does_not_stop_the_schedule_form(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $pass = ['type' => 'Season', 'price' => 50, 'is_pass' => true, 'pass_scope' => 'this_event'];
        $preset = TicketPreset::create(['role_id' => $venue->id, 'name' => 'Season', 'kind' => 'tickets', 'is_default' => true, 'settings' => ['currency_code' => 'EUR', 'payment_method' => 'cash', 'tickets' => [$pass]]]);

        $row = collect($this->editorProps($owner, $venue)['presets'])->firstWhere('id', $preset->encodeId());
        $this->assertSame([], $row['tickets'], 'nothing for the editor to show');
        $this->assertSame(1, $row['passes']);

        // The island posts no ticket rows for it: the save goes through and the pass is kept.
        $posted = $this->row(['id' => $preset->encodeId(), 'name' => 'Season', 'is_default' => 1]);
        unset($posted['tickets']);
        $this->save($owner, $venue, ['ticket_presets_submitted' => 1, 'ticket_presets_seen' => [$preset->encodeId()], 'ticket_presets_default_seen' => $preset->encodeId(), 'ticket_presets' => [$posted]])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame([$pass], $preset->fresh()->settings['tickets']);
    }

    public function test_a_settings_page_opened_earlier_does_not_remove_a_preset_saved_since(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $regular = TicketPreset::create(['role_id' => $venue->id, 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => true, 'settings' => ['tickets' => [['type' => 'Entry', 'price' => 5]]]]);
        // Saved from an event in another tab after this Settings page was opened, and made the default.
        $special = TicketPreset::create(['role_id' => $venue->id, 'name' => 'Special', 'kind' => 'tickets', 'is_default' => false, 'settings' => ['tickets' => [['price' => 20]]]]);
        TicketPresets::makeDefault($venue, $special);

        $this->save($owner, $venue, [
            'ticket_presets_submitted' => 1,
            'ticket_presets_seen' => [$regular->encodeId()],
            'ticket_presets_default_seen' => $regular->encodeId(),
            'ticket_presets' => [$this->row(['id' => $regular->encodeId(), 'is_default' => 1, 'tickets' => [['src' => '0', 'type' => 'Entry', 'price' => '5']]])],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertNotNull($special->fresh(), 'never on that page, so not removed by it');
        $this->assertTrue($special->fresh()->is_default, 'the default moved since stays where it was moved');
    }

    public function test_a_save_that_did_not_show_the_list_leaves_it_alone(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        TicketPreset::create(['role_id' => $venue->id, 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => true, 'settings' => []]);

        $this->save($owner, $venue)->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(1, TicketPreset::count());
    }

    public function test_the_switch_is_the_owners_and_needs_a_way_of_signing_up_for_every_preset(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $migrated = TicketPreset::create(['role_id' => $venue->id, 'name' => null, 'kind' => null, 'is_default' => true, 'settings' => ['tickets' => [['price' => 5]]]]);

        $this->save($owner, $venue, [
            'ticket_presets_submitted' => 1, 'ticket_presets_team' => 1,
            'ticket_presets' => [['id' => $migrated->encodeId(), 'name' => '', 'is_default' => 1, 'currency_code' => 'EUR', 'payment_method' => 'cash', 'tickets' => [['price' => '5']]]],
        ])->assertSessionHasErrors('ticket_presets.0.kind');
        $this->assertFalse(TicketPresets::teamUsesPresets($venue->fresh()));

        $this->save($owner, $venue, [
            'ticket_presets_submitted' => 1, 'ticket_presets_team' => 1,
            'ticket_presets' => [['id' => $migrated->encodeId(), 'name' => '', 'kind' => 'tickets', 'is_default' => 1, 'currency_code' => 'EUR', 'payment_method' => 'cash', 'tickets' => [['price' => '5']]]],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertTrue(TicketPresets::teamUsesPresets($venue->fresh()));
    }

    public function test_an_admin_cannot_set_the_switch_and_cannot_change_presets_while_it_is_on(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $admin = $this->createOwner();
        $venue->users()->attach($admin->id, ['level' => 'admin']);
        $preset = TicketPreset::create(['role_id' => $venue->id, 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => true, 'settings' => ['tickets' => [['type' => 'Entry', 'price' => 5]]]]);

        // Off: an admin edits presets, as they could save the default from an event before, but
        // the switch is not theirs.
        $this->save($admin, $venue, [
            'ticket_presets_submitted' => 1, 'ticket_presets_team' => 1,
            'ticket_presets' => [$this->row(['id' => $preset->encodeId(), 'is_default' => 1, 'tickets' => [['type' => 'Entry', 'price' => '4']]])],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(4.0, $preset->fresh()->ticketRows()[0]['price']);
        $this->assertFalse(TicketPresets::teamUsesPresets($venue->fresh()));

        // On: the presets are the owner's alone, or the admin would set the prices the switch holds.
        TicketPresets::setTeamUsesPresets($venue, true);
        $this->save($admin, $venue, [
            'ticket_presets_submitted' => 1, 'ticket_presets_team' => 0,
            'ticket_presets' => [$this->row(['id' => $preset->encodeId(), 'is_default' => 1, 'tickets' => [['type' => 'Entry', 'price' => '0']]])],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(4.0, $preset->fresh()->ticketRows()[0]['price']);
        $this->assertTrue(TicketPresets::teamUsesPresets($venue->fresh()));
    }

    public function test_the_switch_with_nothing_to_pick_from_is_refused(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $this->save($owner, $venue, ['ticket_presets_submitted' => 1, 'ticket_presets_team' => 1])
            ->assertSessionHasErrors('ticket_presets_team');

        $this->assertFalse(TicketPresets::teamUsesPresets($venue->fresh()));
    }

    public function test_removing_the_last_preset_turns_the_switch_off(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $last = TicketPreset::create(['role_id' => $venue->id, 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => true, 'settings' => []]);
        TicketPresets::setTeamUsesPresets($venue, true);

        $this->save($owner, $venue, ['ticket_presets_submitted' => 1, 'ticket_presets_seen' => [$last->encodeId()], 'ticket_presets_team' => 0])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(0, TicketPreset::count());
        $this->assertFalse(TicketPresets::teamUsesPresets($venue->fresh()));
    }

    public function test_the_row_is_on_the_settings_tab_and_read_only_for_a_bound_admin(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $admin = $this->createOwner();
        $venue->users()->attach($admin->id, ['level' => 'admin']);
        TicketPreset::create(['role_id' => $venue->id, 'name' => 'Regular <b>x</b>', 'kind' => 'tickets', 'is_default' => true, 'settings' => ['tickets' => [['price' => 5]]]]);

        $ownerPage = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $venue->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString('id="settings-tab-tickets"', $ownerPage);
        $this->assertStringContainsString('"canEdit":true', $ownerPage, 'the island draws the list and its marker for the owner');
        $this->assertStringContainsString('data-action="add-ticket-preset"', $ownerPage);
        $this->assertStringContainsString('id="ticket_presets_team"', $ownerPage);
        $this->assertStringNotContainsString('Regular <b>x</b>', $ownerPage, 'a preset name reaches the page only as JSON, never as markup');

        TicketPresets::setTeamUsesPresets($venue, true);
        $adminPage = $this->actingAs($admin)->get(route('role.edit', ['subdomain' => $venue->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString('id="settings-tab-tickets"', $adminPage);
        $this->assertStringContainsString('"canEdit":false', $adminPage, 'read only, so no marker: a save from it changes no preset');
        $this->assertStringNotContainsString('id="ticket_presets_team"', $adminPage);
        $this->assertStringContainsString(e(__('messages.ticket_presets_locked_note', ['owner' => $owner->name])), $adminPage);
    }

    public function test_a_new_event_starts_on_the_default_preset_and_its_way_of_signing_up(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['country_code' => 'de']);
        // The last event was free registration; the default preset sells tickets, and says so.
        $this->createEvent($venue, ['rsvp_enabled' => true, 'tickets_enabled' => false]);
        TicketPreset::create(['role_id' => $venue->id, 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => true, 'settings' => [
            'currency_code' => 'EUR', 'payment_method' => 'cash', 'tickets' => [['type' => 'Entry', 'price' => 5]],
        ]]);

        $html = $this->actingAs($owner)->get(route('event.create', ['subdomain' => $venue->subdomain]))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/ticketMode: "tickets"/', $html);
        $this->assertStringContainsString('"type":"Entry"', $html);
        $this->assertStringContainsString('id="ticket-preset-chips"', $html);
    }

    public function test_the_preset_copied_from_the_old_default_keeps_the_last_events_mode(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $this->createEvent($venue, ['rsvp_enabled' => true, 'tickets_enabled' => false]);
        TicketPreset::create(['role_id' => $venue->id, 'name' => null, 'kind' => null, 'is_default' => true, 'settings' => ['currency_code' => 'EUR', 'tickets' => [['type' => 'Entry', 'price' => 5]]]]);

        $html = $this->actingAs($owner)->get(route('event.create', ['subdomain' => $venue->subdomain]))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/ticketMode: "rsvp"/', $html);
    }

    public function test_save_as_a_preset_from_an_event_adds_one_and_the_same_name_replaces_it(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'tickets_enabled' => true, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash']);
        $ticket = $this->createTicket($event, ['type' => 'Entry', 'price' => 5]);
        $put = fn (array $data) => $this->actingAs($owner)->put(
            route('event.update', ['subdomain' => $venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
            array_merge(['name' => $event->name, 'starts_at' => $event->starts_at, 'duration' => 2, 'ticket_panel' => 'full',
                'tickets_enabled' => 1, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash',
                'tickets' => [['id' => $ticket->id, 'type' => 'Entry', 'price' => 5]]], $data)
        );

        $put(['save_ticket_preset' => 1, 'ticket_preset_name' => 'Regular'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(['Regular'], TicketPresets::for($venue)->pluck('name')->all());
        $this->assertTrue((bool) TicketPresets::for($venue)->first()->is_default, 'the first preset is the default');

        $put(['save_ticket_preset' => 1, 'ticket_preset_name' => 'regular', 'tickets' => [['id' => $ticket->id, 'type' => 'Entry', 'price' => 7]]])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1, TicketPreset::count(), 'the same name, in any case, replaces it');
        $this->assertSame(7.0, TicketPreset::first()->ticketRows()[0]['price']);

        $put(['save_ticket_preset' => 1, 'ticket_preset_name' => 'Late night', 'ticket_preset_default' => 1])->assertRedirect();
        $this->assertSame(['Regular', 'Late night'], TicketPresets::for($venue)->pluck('name')->all());
        $this->assertSame('Late night', TicketPresets::defaultFor($venue)->name);
    }

    public function test_a_preset_past_what_the_settings_editor_takes_is_refused_before_the_event_saves(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'tickets_enabled' => true, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash']);
        $ticket = $this->createTicket($event, ['type' => 'Entry', 'price' => 5]);
        foreach (range(1, TicketPresets::MAX_PRESETS) as $i) {
            TicketPreset::create(['role_id' => $venue->id, 'name' => 'Night '.$i, 'kind' => 'tickets', 'is_default' => $i === 1, 'sort_order' => $i, 'settings' => ['tickets' => [['price' => $i]]]]);
        }
        $put = fn (array $data) => $this->actingAs($owner)->put(
            route('event.update', ['subdomain' => $venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
            array_merge(['name' => 'Renamed', 'starts_at' => $event->starts_at, 'duration' => 2, 'ticket_panel' => 'full',
                'tickets_enabled' => 1, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash',
                'tickets' => [['id' => $ticket->id, 'type' => 'Entry', 'price' => 5]]], $data)
        );

        // A 31st would refuse every later save of the schedule form, on any tab.
        $put(['save_ticket_preset' => 1, 'ticket_preset_name' => 'One more'])->assertSessionHasErrors('ticket_preset_name');
        $this->assertSame(TicketPresets::MAX_PRESETS, TicketPreset::count());
        $this->assertNotSame('Renamed', $event->fresh()->name, 'refused before anything was saved');

        // One of the thirty, by name, is updated as before.
        $put(['save_ticket_preset' => 1, 'ticket_preset_name' => 'night 3'])->assertSessionHasNoErrors();

        // More types than the editor takes.
        $rows = array_map(fn ($i) => ['type' => 'Row '.$i, 'price' => $i], range(1, TicketPresets::MAX_TYPES + 1));
        $put(['save_ticket_preset' => 1, 'ticket_preset_name' => 'night 3', 'tickets' => $rows])->assertSessionHasErrors('ticket_preset_name');
    }

    public function test_a_venue_editing_a_performers_event_is_not_sent_the_performers_presets(): void
    {
        $talentOwner = $this->createOwner();
        $talent = $this->createRole($talentOwner, 'talent');
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue');
        TicketPreset::create(['role_id' => $talent->id, 'name' => 'Backstage Night', 'kind' => 'tickets', 'is_default' => true, 'settings' => [
            'currency_code' => 'EUR', 'payment_method' => 'cash', 'tickets' => [['type' => 'Entry', 'price' => 5]],
            'promo_codes' => [['code' => 'CREWONLY50', 'type' => 'percentage', 'value' => 50, 'is_active' => true]],
        ]]);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id, 'user_id' => $talentOwner->id, 'tickets_enabled' => true, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash']);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $this->createTicket($event, ['type' => 'Entry', 'price' => 5]);

        $html = $this->actingAs($venueOwner)->get(route('event.edit', ['subdomain' => $venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))->assertOk()->getContent();
        $this->assertStringNotContainsString('CREWONLY50', $html);
        $this->assertStringNotContainsString('Backstage Night', $html);

        // The performer's own people are.
        $html = $this->actingAs($talentOwner)->get(route('event.edit', ['subdomain' => $talent->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))->assertOk()->getContent();
        $this->assertStringContainsString('Backstage Night', $html);
    }

    public function test_after_a_downgrade_the_owner_can_turn_the_switch_off_and_not_on(): void
    {
        config(['app.hosted' => true]);
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['plan_type' => 'pro']);
        $this->assertFalse(TicketPresets::teamSwitchOffered($venue), 'the fixture: no team on this plan');
        $preset = TicketPreset::create(['role_id' => $venue->id, 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => true, 'settings' => ['tickets' => [['price' => 5]]]]);
        TicketPresets::setTeamUsesPresets($venue, true);

        $props = $this->editorProps($owner, $venue);
        $this->assertTrue($props['switchOffered'], 'still on, so still the owner\'s to turn off');

        $base = ['ticket_presets_submitted' => 1, 'ticket_presets_seen' => [$preset->encodeId()], 'ticket_presets_default_seen' => $preset->encodeId(),
            'ticket_presets' => [$this->row(['id' => $preset->encodeId(), 'is_default' => 1, 'tickets' => [['src' => '0', 'type' => '', 'price' => '5']]])]];

        $this->save($owner, $venue, $base + ['ticket_presets_team' => 1])->assertSessionHasNoErrors();
        $this->assertTrue(TicketPresets::teamUsesPresets($venue->fresh()), 'left on is left alone');

        $this->save($owner, $venue, $base)->assertSessionHasNoErrors();
        $this->assertFalse(TicketPresets::teamUsesPresets($venue->fresh()));

        $this->save($owner, $venue, $base + ['ticket_presets_team' => 1])->assertSessionHasNoErrors();
        $this->assertFalse(TicketPresets::teamUsesPresets($venue->fresh()), 'never on without a plan that offers it');
        $this->assertFalse($this->editorProps($owner, $venue)['switchOffered']);
    }

    public function test_saving_through_the_venue_does_not_write_the_events_own_schedules_presets(): void
    {
        // The venue an event is at may edit it (User::canEditEvent()), and is not bound by the
        // performer's presets; it is not one of the performer's people either, so it cannot change
        // them by saving the event as a preset.
        $talentOwner = $this->createOwner();
        $talent = $this->createRole($talentOwner, 'talent');
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue');
        TicketPreset::create(['role_id' => $talent->id, 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => true, 'settings' => ['currency_code' => 'EUR', 'payment_method' => 'cash', 'tickets' => [['type' => 'Entry', 'price' => 5]]]]);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id, 'user_id' => $talentOwner->id, 'tickets_enabled' => true, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash']);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $ticket = $this->createTicket($event, ['type' => 'Entry', 'price' => 5]);
        $this->assertTrue($venueOwner->canEditEvent($event->fresh()), 'the fixture: the venue edits the event');

        $this->actingAs($venueOwner)->put(route('event.update', ['subdomain' => $venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]), [
            'name' => $event->name, 'starts_at' => $event->starts_at, 'duration' => 2, 'ticket_panel' => 'full',
            'tickets_enabled' => 1, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash',
            'tickets' => [['id' => $ticket->id, 'type' => 'Entry', 'price' => 1]],
            'save_ticket_preset' => 1, 'ticket_preset_name' => 'Regular',
        ])->assertRedirect();

        $this->assertSame(5.0, TicketPreset::where('role_id', $talent->id)->first()->ticketRows()[0]['price']);
        $this->assertSame(0, TicketPreset::where('role_id', $venue->id)->count());
    }

    public function test_a_bound_admin_cannot_save_a_preset_from_an_event(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $admin = $this->createOwner();
        $venue->users()->attach($admin->id, ['level' => 'admin']);
        TicketPreset::create(['role_id' => $venue->id, 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => true, 'settings' => ['currency_code' => 'EUR', 'payment_method' => 'cash', 'tickets' => [['type' => 'Entry', 'price' => 5]]]]);
        TicketPresets::setTeamUsesPresets($venue, true);

        $this->actingAs($admin)->post(route('event.store', ['subdomain' => $venue->subdomain]), [
            'name' => 'Theirs', 'starts_at' => now()->addDays(5)->setTime(20, 0)->format('Y-m-d H:i:s'), 'duration' => 2,
            'ticket_panel' => 'presets', 'save_ticket_preset' => 1, 'ticket_preset_name' => 'Regular',
        ])->assertRedirect();

        $this->assertSame(5.0, TicketPreset::first()->ticketRows()[0]['price']);
        $this->assertSame(1, TicketPreset::count());
        $this->assertSame(1, Event::where('name', 'Theirs')->count());
    }
}
