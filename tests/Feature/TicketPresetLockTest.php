<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketPreset;
use App\Models\User;
use App\Services\TicketPresets;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Issue #130: a schedule's owner can have the rest of the team pick from ticket presets, and the
 * server holds them to it (EventRepo::saveEvent() through App\Services\TicketPresets).
 *
 * Every test of a bound member has a control beside it: the owner, a guest, an import with no one
 * signed in or another schedule's editor doing the same thing, unbound. A lock that bound everybody
 * would pass the member's tests on its own.
 */
class TicketPresetLockTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private User $member;

    private Role $venue;

    private TicketPreset $regular;

    private TicketPreset $special;

    private TicketPreset $free;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => false]);

        $this->owner = $this->createOwner();
        $this->venue = $this->createRole($this->owner, 'venue', ['country_code' => 'de', 'timezone' => 'Europe/Berlin']);
        $this->member = $this->createOwner();
        $this->venue->users()->attach($this->member->id, ['level' => 'admin']);

        $this->regular = TicketPreset::create(['role_id' => $this->venue->id, 'name' => 'Regular', 'kind' => 'tickets', 'is_default' => true, 'sort_order' => 0, 'settings' => [
            'currency_code' => 'EUR', 'payment_method' => 'cash', 'payment_instructions' => 'Pay at the door',
            'tickets' => [['type' => 'Entry', 'price' => 5, 'quantity' => null], ['type' => 'Reduced', 'price' => 3, 'quantity' => null]],
        ]]);
        $this->special = TicketPreset::create(['role_id' => $this->venue->id, 'name' => 'Special', 'kind' => 'tickets', 'is_default' => false, 'sort_order' => 1, 'settings' => [
            'currency_code' => 'EUR', 'payment_method' => 'cash',
            'tickets' => [['type' => null, 'price' => 20, 'quantity' => 150]],
        ]]);
        $this->free = TicketPreset::create(['role_id' => $this->venue->id, 'name' => 'Free event', 'kind' => 'rsvp', 'is_default' => false, 'sort_order' => 2, 'settings' => [
            'currency_code' => 'EUR', 'payment_method' => 'cash', 'rsvp_limit' => 80,
        ]]);

        TicketPresets::setTeamUsesPresets($this->venue, true);
    }

    private function store(User $user, array $data = [])
    {
        return $this->actingAs($user)->post(route('event.store', ['subdomain' => $this->venue->subdomain]), array_merge([
            'name' => 'Open Stage '.Str::random(4),
            'starts_at' => now()->addDays(10)->setTime(20, 0)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'schedule_type' => 'one_time',
        ], $data));
    }

    private function update(User $user, Event $event, array $data = [])
    {
        return $this->actingAs($user)->put(
            route('event.update', ['subdomain' => $this->venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
            array_merge(['name' => $event->name, 'starts_at' => $event->starts_at, 'duration' => 2], $data)
        );
    }

    /** What the full Tickets tab posts for a free, unlimited ticket: the bug the issue was about. */
    private function freeTicketPost(): array
    {
        return [
            'ticket_panel' => 'full',
            'tickets_enabled' => 1, 'rsvp_enabled' => 0,
            'ticket_currency_code' => 'EUR', 'payment_method' => 'cash',
            'tickets' => [['type' => 'Free for all', 'price' => '', 'quantity' => '']],
        ];
    }

    private function types(Event $event): array
    {
        return Ticket::where('event_id', $event->id)->where('is_deleted', false)->where('is_addon', false)
            ->orderByDesc('price')->get()->map(fn ($t) => [$t->type, (float) $t->price])->all();
    }

    private function latest(): Event
    {
        return Event::query()->latest('id')->firstOrFail();
    }

    public function test_the_fixture_binds_the_member_and_not_the_owner(): void
    {
        $this->assertTrue(TicketPresets::lockedFor($this->member, $this->venue));
        $this->assertFalse(TicketPresets::lockedFor($this->owner, $this->venue));
        $this->assertFalse(TicketPresets::lockedFor(null, $this->venue));
    }

    public function test_a_bound_members_new_event_takes_the_default_preset_whatever_it_posts(): void
    {
        $post = $this->freeTicketPost();
        unset($post['ticket_panel']);
        $this->store($this->member, $post)->assertRedirect()->assertSessionHasNoErrors();

        $event = $this->latest();
        $this->assertSame([['Entry', 5.0], ['Reduced', 3.0]], $this->types($event));
        $this->assertTrue((bool) $event->tickets_enabled);
        $this->assertSame('cash', $event->payment_method);
        $this->assertSame('Pay at the door', $event->payment_instructions);
        $this->assertSame($this->regular->id, (int) $event->ticket_preset_id);
    }

    public function test_the_owner_still_types_prices(): void
    {
        $post = $this->freeTicketPost();
        $this->store($this->owner, $post)->assertRedirect()->assertSessionHasNoErrors();

        $event = $this->latest();
        $this->assertSame([['Free for all', 0.0]], $this->types($event));
        $this->assertNull($event->ticket_preset_id);
    }

    public function test_a_bound_member_picks_another_preset(): void
    {
        $this->store($this->member, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->special->encodeId(), 'ticket_preset_apply' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();

        $event = $this->latest();
        $this->assertSame([[null, 20.0]], $this->types($event));
        $this->assertSame(150, (int) Ticket::where('event_id', $event->id)->where('is_deleted', false)->value('quantity'));
        $this->assertSame($this->special->id, (int) $event->ticket_preset_id);
    }

    public function test_a_free_registration_preset_empties_what_tickets_elsewhere_held(): void
    {
        $this->store($this->member, [
            'ticket_panel' => 'presets', 'ticket_preset_id' => $this->free->encodeId(), 'ticket_preset_apply' => 1,
            'registration_url' => 'https://tickets.example.org/x', 'ticket_price' => 12, 'coupon_code' => 'SNEAKY',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $event = $this->latest();
        $this->assertFalse((bool) $event->tickets_enabled);
        $this->assertTrue((bool) $event->rsvp_enabled);
        $this->assertSame(80, (int) $event->rsvp_limit);
        $this->assertNull($event->registration_url);
        $this->assertNull($event->ticket_price);
        $this->assertNull($event->coupon_code);
    }

    public function test_a_bound_members_new_event_is_on_the_owners_account(): void
    {
        $this->store($this->member, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->regular->encodeId(), 'ticket_preset_apply' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame($this->owner->id, (int) $this->latest()->user_id, 'payment gateways pay events.user_id: the owner set the prices');
    }

    public function test_an_unbound_members_new_event_stays_on_their_own_account(): void
    {
        TicketPresets::setTeamUsesPresets($this->venue, false);

        $this->store($this->member, $this->freeTicketPost())->assertRedirect()->assertSessionHasNoErrors();

        $event = $this->latest();
        $this->assertSame($this->member->id, (int) $event->user_id);
        $this->assertSame([['Free for all', 0.0]], $this->types($event));
    }

    public function test_another_schedules_preset_is_refused(): void
    {
        $other = $this->createRole($this->createOwner(), 'venue');
        $foreign = TicketPreset::create(['role_id' => $other->id, 'name' => 'Theirs', 'kind' => 'tickets', 'is_default' => true, 'settings' => ['tickets' => [['price' => 1]]]]);

        $this->store($this->member, ['ticket_panel' => 'presets', 'ticket_preset_id' => $foreign->encodeId(), 'ticket_preset_apply' => 1])
            ->assertSessionHasErrors('ticket_preset_id');

        $this->assertSame(0, Event::count());
    }

    public function test_the_full_tab_from_a_page_opened_before_the_switch_is_refused_not_dropped(): void
    {
        $this->store($this->member, $this->freeTicketPost())->assertSessionHasErrors('ticket_preset_id');

        $this->assertSame(0, Event::count());
    }

    private function eventFromRegular(array $attrs = []): Event
    {
        $event = $this->createEvent($this->venue, $attrs + [
            'creator_role_id' => $this->venue->id, 'user_id' => $this->owner->id,
            'tickets_enabled' => true, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash',
        ]);
        $this->createTicket($event, ['type' => 'Entry', 'price' => 5, 'quantity' => null]);
        $this->createTicket($event, ['type' => 'Reduced', 'price' => 3, 'quantity' => null]);
        \DB::table('tickets')->where('event_id', $event->id)->update(['quantity' => null]);
        \DB::table('events')->where('id', $event->id)->update(['ticket_preset_id' => $this->regular->id]);

        return Event::find($event->id);
    }

    public function test_a_bound_members_plain_save_keeps_the_stored_tickets(): void
    {
        $event = $this->eventFromRegular();

        $this->update($this->member, $event, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->regular->encodeId(), 'ticket_preset_apply' => 0, 'name' => 'Renamed'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame([['Entry', 5.0], ['Reduced', 3.0]], $this->types($event));
        $this->assertSame('Renamed', $event->fresh()->name);
    }

    public function test_a_bound_member_switches_an_events_preset_before_anyone_has_a_ticket(): void
    {
        $event = $this->eventFromRegular();

        $this->update($this->member, $event, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->special->encodeId(), 'ticket_preset_apply' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame([[null, 20.0]], $this->types($event));
        $this->assertSame($this->special->id, (int) $event->fresh()->ticket_preset_id);
    }

    public function test_switching_an_events_preset_drops_a_link_it_kept_from_tickets_elsewhere(): void
    {
        // A link saved under "Tickets elsewhere" stays saved while tickets are sold here (the guest
        // page falls back on it), so a preset that did not name the field would leave it behind.
        $event = $this->eventFromRegular(['registration_url' => 'https://tickets.example.org/old', 'coupon_code' => 'OLDCODE']);

        $this->update($this->member, $event, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->special->encodeId(), 'ticket_preset_apply' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertNull($event->fresh()->registration_url);
        $this->assertNull($event->fresh()->coupon_code);
    }

    public function test_a_changed_preset_is_taken_again_by_pressing_it(): void
    {
        $event = $this->eventFromRegular();
        $settings = $this->regular->settings;
        $settings['tickets'][0]['price'] = 6;
        $this->regular->update(['settings' => $settings]);
        $this->assertFalse($this->regular->fresh()->matches($event->fresh()), 'the event is now on older prices');

        $this->update($this->member, $event, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->regular->encodeId(), 'ticket_preset_apply' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame([['Entry', 6.0], ['Reduced', 3.0]], $this->types($event));
        $this->assertTrue($this->regular->fresh()->matches($event->fresh()->load('tickets')));
    }

    /** @return array<string, array{0: string}> */
    public static function liveStatuses(): array
    {
        return ['unpaid cash reservation' => ['unpaid'], 'paid' => ['paid'], 'refunded' => ['refunded']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('liveStatuses')]
    public function test_somebody_holding_a_ticket_freezes_the_preset(string $status): void
    {
        $event = $this->eventFromRegular();
        $this->createSale($event, $this->venue, ['status' => $status, 'payment_method' => 'cash'], Ticket::where('event_id', $event->id)->first());

        // A press that a sale made since turned into a no-op is said, not dropped without a word.
        $this->update($this->member, $event, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->special->encodeId(), 'ticket_preset_apply' => 1, 'name' => 'Renamed'])
            ->assertSessionHasErrors('ticket_preset_id');

        $this->assertSame([['Entry', 5.0], ['Reduced', 3.0]], $this->types($event));
        $this->assertSame($this->regular->id, (int) $event->fresh()->ticket_preset_id);
        $this->assertNotSame('Renamed', $event->fresh()->name, 'nothing of the refused save was written');
        // And the page comes back on the preset the event has, not lit on the one refused.
        $page = $this->actingAs($this->member)->get(route('event.edit', ['subdomain' => $this->venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))->assertOk()->getContent();
        $this->assertStringContainsString('presetLit: '.json_encode($this->regular->encodeId()), $page);
        $this->assertStringContainsString('presetApply: false', $page);

        // Without a press the member's save goes through and keeps the tickets.
        $this->update($this->member, $event, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->regular->encodeId(), 'ticket_preset_apply' => 0, 'name' => 'Renamed'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame([['Entry', 5.0], ['Reduced', 3.0]], $this->types($event));
    }

    public function test_an_owners_save_keeps_the_link_of_an_event_on_older_prices(): void
    {
        // Regular changed after the event was saved with it. The owner edits only the name; the
        // page posts the stored link back. The team must still see "older prices", not "Custom".
        $event = $this->eventFromRegular();
        $settings = $this->regular->settings;
        $settings['tickets'][0]['price'] = 6;
        $this->regular->update(['settings' => $settings]);
        $ids = Ticket::where('event_id', $event->id)->orderByDesc('price')->pluck('id')->all();

        $this->update($this->owner, $event, [
            'name' => 'Renamed', 'ticket_panel' => 'full', 'ticket_preset_id' => $this->regular->encodeId(),
            'tickets_enabled' => 1, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash',
            'tickets' => [['id' => $ids[0], 'type' => 'Entry', 'price' => 5], ['id' => $ids[1], 'type' => 'Reduced', 'price' => 3]],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame($this->regular->id, (int) $event->fresh()->ticket_preset_id);
    }

    public function test_a_bound_member_is_never_told_their_event_was_their_first(): void
    {
        $this->store($this->member, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->regular->encodeId(), 'ticket_preset_apply' => 1])
            ->assertRedirect()->assertSessionHasNoErrors()->assertSessionMissing('first_event_created');
    }

    public function test_a_preset_field_sent_as_a_list_is_refused_not_a_500(): void
    {
        $this->store($this->member, ['ticket_panel' => 'presets', 'ticket_preset_id' => ['x'], 'ticket_preset_apply' => 1])
            ->assertSessionHasErrors('ticket_preset_id');
        $this->store($this->owner, ['ticket_preset_name' => ['x'], 'save_ticket_preset' => 1])
            ->assertSessionHasErrors('ticket_preset_name');
    }

    public function test_a_bound_members_update_that_carries_prices_without_the_presets_tab_keeps_the_tickets(): void
    {
        $event = $this->eventFromRegular();

        // Through the API, which never sends ticket_panel.
        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), [
            'tickets_enabled' => true, 'ticket_currency_code' => 'EUR',
            'tickets' => [['type' => 'Free for all', 'price' => 0]],
        ], $this->apiKeyFor($this->member))->assertOk();
        $this->assertSame([['Entry', 5.0], ['Reduced', 3.0]], $this->types($event));

        // Through the form with the panel marker left out.
        $post = $this->freeTicketPost();
        unset($post['ticket_panel']);
        $this->update($this->member, $event, $post);
        $this->assertSame([['Entry', 5.0], ['Reduced', 3.0]], $this->types($event));
    }

    public function test_a_bound_member_editing_through_another_schedules_address_is_still_bound(): void
    {
        // The member also runs a performer schedule the venue's event is on. The schedule in the
        // address is that one, which has no presets: the venue's own still bind.
        $talent = $this->createRole($this->member, 'talent');
        $event = $this->eventFromRegular();
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $this->actingAs($this->member)->put(
            route('event.update', ['subdomain' => $talent->subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
            ['name' => $event->name, 'starts_at' => $event->starts_at, 'duration' => 2, 'ticket_panel' => 'presets', 'ticket_preset_id' => $this->special->encodeId(), 'ticket_preset_apply' => 1]
        )->assertSessionHasNoErrors();

        $this->assertSame([[null, 20.0]], $this->types($event), 'the venue\'s preset, applied through the venue\'s lock');
    }

    public function test_a_seating_plan_freezes_the_preset_too(): void
    {
        $plan = \App\Models\SeatingPlan::create(['role_id' => $this->venue->id, 'name' => 'Main House']);
        $event = $this->eventFromRegular();
        \DB::table('events')->where('id', $event->id)->update(['seating_plan_id' => $plan->id]);

        $this->update($this->member, Event::find($event->id), ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->special->encodeId(), 'ticket_preset_apply' => 1])
            ->assertSessionHasErrors('ticket_preset_id');
        $this->assertSame([['Entry', 5.0], ['Reduced', 3.0]], $this->types($event));
    }

    public function test_an_appointment_booking_is_not_bound(): void
    {
        $type = $this->createAppointmentType($this->venue);
        $booking = $this->eventFromRegular(['appointment_type_id' => $type->id]);

        $this->update($this->member, $booking, $this->freeTicketPost())->assertSessionDoesntHaveErrors('ticket_preset_id');
    }

    public function test_the_payee_a_preset_is_checked_against_is_the_owner(): void
    {
        // The member could take Invoice Ninja; the owner, whose account a bound member's new event
        // pays into, cannot. Checked against the member, it would go through.
        $this->member->forceFill(['invoiceninja_api_key' => 'member-key'])->save();
        $settings = $this->special->settings;
        $settings['payment_method'] = 'invoiceninja';
        $this->special->update(['settings' => $settings]);

        $this->store($this->member, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->special->encodeId(), 'ticket_preset_apply' => 1])
            ->assertSessionHasErrors('ticket_preset_id');
    }

    public function test_a_bound_admin_may_not_change_the_presets(): void
    {
        $this->assertFalse(TicketPresets::mayEdit($this->member, $this->venue));
        $this->assertTrue(TicketPresets::mayEdit($this->owner, $this->venue));
        TicketPresets::setTeamUsesPresets($this->venue, false);
        $this->assertTrue(TicketPresets::mayEdit($this->member, $this->venue), 'an editor may while the team is not bound');
    }

    public function test_a_preset_that_holds_only_passes_is_refused(): void
    {
        $passes = TicketPreset::create(['role_id' => $this->venue->id, 'name' => 'Season', 'kind' => 'tickets', 'is_default' => false, 'sort_order' => 3, 'settings' => [
            'currency_code' => 'EUR', 'payment_method' => 'cash', 'tickets' => [['type' => 'Season', 'price' => 50, 'is_pass' => true]],
        ]]);
        $event = $this->eventFromRegular();

        $this->update($this->member, $event, ['ticket_panel' => 'presets', 'ticket_preset_id' => $passes->encodeId(), 'ticket_preset_apply' => 1])
            ->assertSessionHasErrors('ticket_preset_id');
        $this->assertSame([['Entry', 5.0], ['Reduced', 3.0]], $this->types($event), 'not emptied');

        $tile = collect(TicketPresets::forForm($this->venue, $event, true))->firstWhere('id', $passes->encodeId());
        $this->assertFalse($tile['usable']);
    }

    public function test_a_bound_members_event_on_their_own_schedule_waits_as_a_request_on_the_venue(): void
    {
        $talent = $this->createRole($this->member, 'talent');
        $post = fn (array $data) => $this->actingAs($this->member)->post(route('event.store', ['subdomain' => $talent->subdomain]), array_merge([
            'name' => 'My night '.Str::random(4),
            'starts_at' => now()->addDays(10)->setTime(20, 0)->format('Y-m-d H:i:s'),
            'duration' => 2, 'schedule_type' => 'one_time',
            'venue_id' => UrlUtils::encodeId($this->venue->id), 'venue_submitted' => 1,
        ], $data));
        $accepted = fn () => \DB::table('event_role')->where('event_id', $this->latest()->id)->where('role_id', $this->venue->id)->value('is_accepted');

        $post($this->freeTicketPost())->assertSessionHasNoErrors();
        $this->assertNotEquals(1, $accepted(), 'their prices wait for the owner');

        // Nothing to pay for or sign up to: listed at once, as before.
        $post(['ticket_panel' => 'full', 'tickets_enabled' => 0, 'rsvp_enabled' => 0])->assertSessionHasNoErrors();
        $this->assertEquals(1, $accepted());

        // Nor with the switch off.
        TicketPresets::setTeamUsesPresets($this->venue, false);
        $post($this->freeTicketPost())->assertSessionHasNoErrors();
        $this->assertEquals(1, $accepted());
    }

    public function test_a_copy_whose_preset_changed_since_is_not_linked_to_it(): void
    {
        $event = $this->eventFromRegular(['payment_instructions' => 'Pay at the door']);
        $this->assertTrue($this->regular->matches($event), 'the fixture: the source is on Regular');
        $open = function () use ($event) {
            $this->actingAs($this->owner)->get(route('event.clone', ['subdomain' => $this->venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));

            return $this->actingAs($this->owner)->get(route('event.create', ['subdomain' => $this->venue->subdomain]))->assertOk()->viewData('event');
        };

        $this->assertSame($this->regular->id, (int) $open()->ticket_preset_id, 'the same tickets: still Regular');

        $settings = $this->regular->settings;
        $settings['tickets'][0]['price'] = 6;
        $this->regular->update(['settings' => $settings]);
        $this->assertNull($open()->ticket_preset_id, 'Regular is 6 now; the copy has 5');
    }

    public function test_an_abandoned_checkout_does_not_freeze_it(): void
    {
        $event = $this->eventFromRegular();
        $this->createSale($event, $this->venue, ['status' => 'expired', 'payment_method' => 'cash'], Ticket::where('event_id', $event->id)->first());

        $this->assertFalse(TicketPresets::frozen($event->fresh()));

        $this->update($this->member, $event, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->special->encodeId(), 'ticket_preset_apply' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame([[null, 20.0]], $this->types($event));
    }

    public function test_the_owner_on_an_event_with_tickets_still_edits_them(): void
    {
        $event = $this->eventFromRegular();
        $this->createSale($event, $this->venue, ['status' => 'paid'], Ticket::where('event_id', $event->id)->first());
        $ids = Ticket::where('event_id', $event->id)->orderByDesc('price')->pluck('id')->all();

        $this->update($this->owner, $event, [
            'ticket_panel' => 'full', 'ticket_preset_id' => '',
            'tickets_enabled' => 1, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash',
            'tickets' => [['id' => $ids[0], 'type' => 'Entry', 'price' => 7], ['id' => $ids[1], 'type' => 'Reduced', 'price' => 3]],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame([['Entry', 7.0], ['Reduced', 3.0]], $this->types($event));
        $this->assertNull($event->fresh()->ticket_preset_id, 'edited by hand, the event no longer stands on the preset');
    }

    public function test_a_guest_submitting_to_a_curator_is_not_bound(): void
    {
        $curatorOwner = $this->createOwner();
        $curator = $this->createRole($curatorOwner, 'curator');
        TicketPreset::create(['role_id' => $curator->id, 'name' => 'House', 'kind' => 'tickets', 'is_default' => true, 'settings' => ['tickets' => [['price' => 9]]]]);
        TicketPresets::setTeamUsesPresets($curator, true);

        $visitor = $this->createOwner();
        $this->assertFalse(TicketPresets::lockedFor($visitor, $curator), 'a visitor is not on the curator\'s team');
        $this->assertFalse(TicketPresets::lockedFor(null, $curator), 'an import with nobody signed in is not bound');
    }

    public function test_a_viewer_or_a_follower_is_not_bound_and_another_schedules_editor_is_not_either(): void
    {
        $viewer = $this->createOwner();
        $this->venue->users()->attach($viewer->id, ['level' => 'viewer']);
        $follower = $this->createOwner();
        $this->venue->users()->attach($follower->id, ['level' => 'follower']);

        $this->assertFalse(TicketPresets::lockedFor($viewer, $this->venue));
        $this->assertFalse(TicketPresets::lockedFor($follower, $this->venue));

        $talentOwner = $this->createOwner();
        $talent = $this->createRole($talentOwner, 'talent');
        $this->assertFalse(TicketPresets::lockedFor($this->member, $talent), 'bound by this schedule only');
    }

    public function test_switched_off_or_without_presets_nobody_is_bound(): void
    {
        TicketPresets::setTeamUsesPresets($this->venue, false);
        $this->assertFalse(TicketPresets::lockedFor($this->member, $this->venue));

        TicketPresets::setTeamUsesPresets($this->venue, true);
        TicketPreset::where('role_id', $this->venue->id)->delete();
        $this->assertFalse(TicketPresets::lockedFor($this->member, $this->venue));
    }

    public function test_a_preset_its_payee_cannot_take_is_refused(): void
    {
        $settings = $this->special->settings;
        $settings['payment_method'] = 'invoiceninja';
        $this->special->update(['settings' => $settings]);

        $this->store($this->member, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->special->encodeId(), 'ticket_preset_apply' => 1])
            ->assertSessionHasErrors('ticket_preset_id');

        $this->assertSame(0, Event::count());
    }

    private function apiKeyFor(User $user): array
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return ['X-API-Key' => $raw];
    }

    public function test_the_api_binds_the_member_too(): void
    {
        $headers = $this->apiKeyFor($this->member);

        $this->postJson('/api/events/'.$this->venue->subdomain, [
            'name' => 'Over the API',
            'starts_at' => now()->addDays(12)->setTime(20, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'ticket_currency_code' => 'EUR',
            'tickets' => [['type' => 'Free for all', 'price' => 0]],
        ], $headers)->assertCreated();

        $event = Event::where('name', 'Over the API')->firstOrFail();
        $this->assertSame([['Entry', 5.0], ['Reduced', 3.0]], $this->types($event));
    }

    public function test_an_api_update_that_names_no_preset_keeps_the_link_and_one_that_names_one_applies_it(): void
    {
        $event = $this->eventFromRegular();
        $headers = $this->apiKeyFor($this->owner);

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), ['name' => 'Renamed', 'ticket_preset_id' => null], $headers)->assertOk();
        $this->assertSame($this->regular->id, (int) $event->fresh()->ticket_preset_id, 'an empty preset id names nothing');

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), ['ticket_preset_id' => $this->special->encodeId()], $headers)->assertOk();
        $this->assertSame([[null, 20.0]], $this->types($event));
        $this->assertSame($this->special->encodeId(), $this->getJson('/api/events/'.UrlUtils::encodeId($event->id), $headers)->json('data.ticket_preset_id'));
    }

    public function test_the_presets_tab_saved_after_the_switch_went_off_keeps_the_tickets(): void
    {
        // The member's page was opened while the switch was on; the owner turned it off since.
        $event = $this->eventFromRegular();
        TicketPresets::setTeamUsesPresets($this->venue, false);

        $this->update($this->member, $event, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->regular->encodeId(), 'ticket_preset_apply' => 0, 'name' => 'Renamed'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame([['Entry', 5.0], ['Reduced', 3.0]], $this->types($event), 'a tab with no ticket fields is not a tab that removed them');
        $this->assertSame('Renamed', $event->fresh()->name);
    }

    public function test_the_presets_tab_after_the_switch_went_off_still_applies_what_was_pressed(): void
    {
        TicketPresets::setTeamUsesPresets($this->venue, false);

        $this->store($this->member, ['ticket_panel' => 'presets', 'ticket_preset_id' => $this->special->encodeId(), 'ticket_preset_apply' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();

        $event = $this->latest();
        $this->assertSame([[null, 20.0]], $this->types($event));
        $this->assertSame($this->member->id, (int) $event->user_id, 'no longer bound: on their own account, as any unbound member');
    }

    public function test_an_api_client_writing_back_what_it_read_does_not_reapply_the_preset(): void
    {
        $event = $this->eventFromRegular();
        $headers = $this->apiKeyFor($this->owner);
        $read = $this->getJson('/api/events/'.UrlUtils::encodeId($event->id), $headers)->assertOk()->json('data');
        $this->assertSame($this->regular->encodeId(), $read['ticket_preset_id']);

        // Sold: a rename sent with the whole read must not be refused as a preset switch.
        $this->createSale($event, $this->venue, ['status' => 'paid'], Ticket::where('event_id', $event->id)->first());
        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), array_merge($read, ['name' => 'Renamed']), $headers)->assertOk();
        $this->assertSame('Renamed', $event->fresh()->name);
        $this->assertSame([['Entry', 5.0], ['Reduced', 3.0]], $this->types($event));
    }

    public function test_an_api_client_asks_again_for_a_preset_that_changed(): void
    {
        $event = $this->eventFromRegular();
        $settings = $this->regular->settings;
        $settings['tickets'][0]['price'] = 6;
        $this->regular->update(['settings' => $settings]);
        $headers = $this->apiKeyFor($this->owner);

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), ['ticket_preset_id' => $this->regular->encodeId()], $headers)->assertOk();
        $this->assertSame([['Entry', 5.0], ['Reduced', 3.0]], $this->types($event), 'the preset it has, named again, applies nothing');

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), ['ticket_preset_id' => $this->regular->encodeId(), 'ticket_preset_apply' => true], $headers)->assertOk();
        $this->assertSame([['Entry', 6.0], ['Reduced', 3.0]], $this->types($event));
    }

    public function test_an_owners_tab_that_no_longer_matches_is_not_linked_to_the_preset(): void
    {
        // The chip was lit when the form was posted, but the tickets saved are not Regular's.
        $this->store($this->owner, [
            'ticket_panel' => 'full', 'ticket_preset_id' => $this->regular->encodeId(),
            'tickets_enabled' => 1, 'rsvp_enabled' => 0, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash', 'payment_instructions' => 'Pay at the door',
            'tickets' => [['type' => 'Entry', 'price' => 9], ['type' => 'Reduced', 'price' => 3]],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull($this->latest()->ticket_preset_id);

        $this->store($this->owner, [
            'ticket_panel' => 'full', 'ticket_preset_id' => $this->regular->encodeId(),
            'tickets_enabled' => 1, 'rsvp_enabled' => 0, 'ticket_currency_code' => 'EUR', 'payment_method' => 'cash', 'payment_instructions' => 'Pay at the door',
            'tickets' => [['type' => 'Entry', 'price' => 5], ['type' => 'Reduced', 'price' => 3]],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($this->regular->id, (int) $this->latest()->ticket_preset_id);
    }

    public function test_deleting_a_preset_clears_the_link_of_its_events(): void
    {
        $event = $this->eventFromRegular();

        TicketPresets::forget($this->regular);

        $this->assertNull($event->fresh()->ticket_preset_id);
    }
}
