<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\TicketPreset;
use App\Models\User;
use App\Repos\EventRepo;
use App\Services\TicketPresets;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Issue #130 on the event form: the owner's preset chips above the Tickets tab's tiles, and the
 * tab a team member the owner's switch binds is given instead (event/partials/ticket-presets-*).
 */
class TicketPresetEventFormTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private User $member;

    private Role $venue;

    private TicketPreset $regular;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => false]);

        $this->owner = $this->createOwner();
        \DB::table('users')->where('id', $this->owner->id)->update(['name' => 'Sam {{ 7*7 }} Rivera']);
        $this->owner = $this->owner->fresh();
        $this->venue = $this->createRole($this->owner, 'venue', ['country_code' => 'de']);
        $this->member = $this->createOwner();
        $this->venue->users()->attach($this->member->id, ['level' => 'admin']);
        $this->regular = TicketPreset::create(['role_id' => $this->venue->id, 'name' => 'Regular <b>{{ 1+1 }}</b>', 'kind' => 'tickets', 'is_default' => true, 'settings' => [
            'currency_code' => 'EUR', 'payment_method' => 'cash',
            'tickets' => [['type' => 'Entry', 'price' => 5], ['type' => 'Reduced', 'price' => 3]],
        ]]);
    }

    private function createPage(User $user, array $session = []): string
    {
        return $this->actingAs($user)->withSession($session)
            ->get(route('event.create', ['subdomain' => $this->venue->subdomain]))->assertOk()->getContent();
    }

    private function editPage(User $user, Event $event): string
    {
        return $this->actingAs($user)
            ->get(route('event.edit', ['subdomain' => $this->venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))->assertOk()->getContent();
    }

    private function tab(string $html): string
    {
        $start = strpos($html, 'id="section-tickets"');
        $end = strpos($html, 'data-section="section-participants"', $start);

        return substr($html, $start, $end - $start);
    }

    public function test_the_owner_gets_the_chips_above_the_full_tab(): void
    {
        $tab = $this->tab($this->createPage($this->owner));

        $this->assertStringContainsString('id="ticket-preset-chips"', $tab);
        $this->assertStringContainsString('name="ticket_panel" value="full"', $tab);
        $this->assertStringContainsString('id="ticket_choice_tickets"', $tab);
        $this->assertStringContainsString('name="tickets_enabled"', $tab);
        $this->assertStringContainsString('id="save_ticket_preset"', $tab);
        $this->assertStringNotContainsString('save_default_tickets', $tab);
        $this->assertStringNotContainsString('<b>{{ 1+1 }}</b>', $tab, 'a preset\'s name is somebody\'s text: v-text, never markup');
    }

    public function test_a_bound_member_gets_only_the_presets(): void
    {
        TicketPresets::setTeamUsesPresets($this->venue, true);
        $html = $this->createPage($this->member);
        $tab = $this->tab($html);

        $this->assertStringContainsString('name="ticket_panel" value="presets"', $tab);
        $this->assertStringContainsString('id="ticket-preset-picked"', $tab);
        foreach (['name="tickets_enabled"', 'name="rsvp_enabled"', 'id="ticket_choice_tickets"', 'ticket-mode-radio', 'name="payment_method"', 'id="save_ticket_preset"', 'tickets[${index}][price]'] as $absent) {
            $this->assertStringNotContainsString($absent, $tab, $absent.' is not on a bound member\'s tab');
        }
        // The owner's name rides in a v-pre sentence; Vue would otherwise run what it holds.
        $this->assertMatchesRegularExpression('/<p[^>]*v-pre>[^<]*'.preg_quote(e('Sam {{ 7*7 }} Rivera'), '/').'/', $tab);
        // A bound member's page is not sent what a chip would fill the tab with.
        $presets = $this->presetsOn($html);
        $this->assertArrayNotHasKey('input', $presets[0]);
        $this->assertSame('Entry €5, Reduced €3', $presets[0]['tile']);
    }

    /** The presets array the page's Vue data was given. */
    private function presetsOn(string $html): array
    {
        $this->assertSame(1, preg_match('/presets: (\[.*?\]),\n\s+presetLit:/s', $html, $m));

        return json_decode($m[1], true);
    }

    public function test_a_bound_member_is_not_given_the_first_event_form(): void
    {
        TicketPresets::setTeamUsesPresets($this->venue, true);

        $this->assertStringNotContainsString(__('messages.next_step_add_first_event'), $this->createPage($this->member));
    }

    public function test_a_refused_save_comes_back_on_the_preset_that_was_pressed(): void
    {
        TicketPresets::setTeamUsesPresets($this->venue, true);
        $special = TicketPreset::create(['role_id' => $this->venue->id, 'name' => 'Special', 'kind' => 'tickets', 'settings' => ['tickets' => [['price' => 20]]]]);

        $html = $this->createPage($this->member, ['_old_input' => ['ticket_preset_id' => $special->encodeId(), 'ticket_preset_apply' => '1']]);

        $this->assertStringContainsString('presetLit: "'.$special->encodeId().'"', $html);
        $this->assertStringContainsString('presetApply: true', $html);
    }

    public function test_an_event_on_older_prices_and_an_event_with_tickets_say_so(): void
    {
        TicketPresets::setTeamUsesPresets($this->venue, true);
        $event = $this->createEvent($this->venue, ['creator_role_id' => $this->venue->id, 'tickets_enabled' => true, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash']);
        $entry = $this->createTicket($event, ['type' => 'Entry', 'price' => 4]);
        $this->createTicket($event, ['type' => 'Reduced', 'price' => 3]);
        \DB::table('tickets')->where('event_id', $event->id)->update(['quantity' => null]);
        \DB::table('events')->where('id', $event->id)->update(['ticket_preset_id' => $this->regular->id]);

        $html = $this->editPage($this->member, Event::find($event->id));
        $this->assertStringContainsString('presetStale: true', $html);
        $this->assertStringContainsString('presetFrozen: false', $html);

        $this->createSale($event, $this->venue, ['status' => 'unpaid', 'payment_method' => 'cash'], $entry, 2);
        $html = $this->editPage($this->member, Event::find($event->id));
        $this->assertStringContainsString('presetFrozen: true', $html);
        $this->assertStringContainsString(e(__('messages.ticket_preset_frozen_team', ['owner' => $this->owner->name])), $html);
    }

    public function test_the_owner_is_told_too_when_the_events_preset_changed_since(): void
    {
        $event = $this->createEvent($this->venue, ['creator_role_id' => $this->venue->id, 'tickets_enabled' => true, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash']);
        $this->createTicket($event, ['type' => 'Entry', 'price' => 4]);
        $this->createTicket($event, ['type' => 'Reduced', 'price' => 3]);
        \DB::table('tickets')->where('event_id', $event->id)->update(['quantity' => null]);
        \DB::table('events')->where('id', $event->id)->update(['ticket_preset_id' => $this->regular->id]);

        $html = $this->editPage($this->owner, Event::find($event->id));
        $this->assertStringContainsString('presetStale: true', $html);
        // The note and its button, under the owner's chips (drawn when presetStaleShown).
        $this->assertSame(1, preg_match('~<div v-if="presetStaleShown && presetChosen"[^>]*id="ticket-preset-stale"~', $html));
        $this->assertStringContainsString('@click="applyPreset(presetChosen)"', $html);
    }

    public function test_a_preset_is_applied_with_every_field_the_tab_posts(): void
    {
        $own = ['ticket_preset_id', 'ticket_preset_apply', 'ticket_panel', 'save_ticket_preset', 'ticket_preset_name', 'ticket_preset_default'];
        $missing = array_diff(array_diff(EventRepo::TICKET_PANEL_FIELDS, $own), array_keys($this->regular->toEventInput()));

        $this->assertSame([], array_values($missing), 'fill() keeps the stored value of any field left out, so a preset would leave it behind');
    }
}
