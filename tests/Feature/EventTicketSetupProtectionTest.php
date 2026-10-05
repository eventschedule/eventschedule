<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\User;
use App\Repos\EventRepo;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An event's ticket setup belongs to whoever may see it (User::canViewEventData()).
 *
 * A curator that merely lists someone else's event can open its edit form, and is shown it
 * without the Tickets panel. Two things followed from that, both found by running a curator's
 * save rather than by reading it:
 *
 *   - The form posted no ticket fields, EventRepo::saveEvent() read their absence as "remove
 *     every ticket", and one plain save by the curator deleted the creator's ticket types and
 *     promo codes while leaving ticketing switched on.
 *   - The page's Vue data still carried every ticket type, promo code and add-on, plus the coupon
 *     and payment text, so the panel's gate hid nothing from anyone who opened the page source.
 *
 * The owner's tests are the controls: a guard that protected the setup by breaking it for
 * everybody would pass the curator's tests on its own.
 */
class EventTicketSetupProtectionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Values a page must not contain for someone refused the panel. */
    private const SECRETS = ['SECRETPROMO42', 'SECRETTIER', 'EXTCOUPON77', 'NOTESENTINEL', 'PAYSENTINEL'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    /** @return array{0: User, 1: Role, 2: Event, 3: \App\Models\Ticket, 4: User, 5: Role} */
    private function listedEvent(): array
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue, [
            'tickets_enabled' => true,
            'ticket_currency_code' => 'EUR',
            'ticket_notes' => 'NOTESENTINEL',
            'payment_instructions' => 'PAYSENTINEL',
            'coupon_code' => 'EXTCOUPON77',
            'terms_url' => 'https://example.org/terms',
            'expire_unpaid_tickets' => 3,
            'rsvp_limit' => 40,
            'installment_count' => 3,
            'installment_final_days_before' => 21,
            'ask_phone' => true,
            'sell_after_start' => true,
            'custom_fields' => ['f1' => ['name' => 'Dietary needs', 'type' => 'string', 'required' => false]],
        ]);
        $ticket = $this->createTicket($event, ['type' => 'SECRETTIER', 'price' => 25]);
        PromoCode::create(['event_id' => $event->id, 'code' => 'SECRETPROMO42', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);

        $curatorUser = $this->createOwner();
        $curator = $this->createRole($curatorUser, 'curator');
        // The curator lists the event but did not create it.
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        return [$owner, $venue, Event::find($event->id), $ticket, $curatorUser, $curator];
    }

    private function save(User $user, Role $role, Event $event, array $data = [])
    {
        return $this->actingAs($user)->put(
            route('event.update', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
            array_merge(['name' => 'Renamed', 'starts_at' => $event->starts_at, 'duration' => 2], $data)
        );
    }

    /** The ticket setup as stored: the event's own ticket columns, its ticket types and its promo codes. */
    private function ticketSetup(Event $event): array
    {
        $fresh = Event::find($event->id);
        $columns = array_values(array_intersect(
            array_merge(EventRepo::TICKET_PANEL_FIELDS, ['payment_instructions_html', 'ticket_notes_html']),
            Schema::getColumnListing('events')
        ));

        return [
            'columns' => collect($fresh->getAttributes())->only($columns)->sortKeys()->all(),
            'tickets' => $fresh->tickets()->where('is_deleted', false)->get(['type', 'price', 'quantity'])->toArray(),
            'promo_codes' => $fresh->promoCodes()->get(['code', 'value', 'is_active'])->toArray(),
        ];
    }

    private function editPage(User $user, Role $role, Event $event): string
    {
        return $this->actingAs($user)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();
    }

    public function test_the_fixture_is_the_case_under_test(): void
    {
        [$owner, , $event, , $curatorUser] = $this->listedEvent();

        $this->assertTrue($owner->canViewEventData($event));
        $this->assertFalse($curatorUser->canViewEventData($event), 'the curator may open the form but not see the ticket setup');
        $this->assertNotEmpty($this->ticketSetup($event)['tickets']);
        $this->assertNotEmpty($this->ticketSetup($event)['promo_codes']);
    }

    /** What the form the curator is given actually posts: nothing from the Tickets panel. */
    public function test_a_curator_saving_a_listed_event_leaves_its_ticket_setup_alone(): void
    {
        [, $venue, $event, , $curatorUser, $curator] = $this->listedEvent();
        $before = $this->ticketSetup($event);

        $this->save($curatorUser, $curator, $event, [
            'venue_submitted' => 1,
            'venue_id' => UrlUtils::encodeId($venue->id),
            'members_submitted' => 1,
            'curators_submitted' => 1,
            'curators' => [UrlUtils::encodeId($curator->id)],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Renamed', Event::find($event->id)->name, 'the save itself went through');
        $this->assertSame($before, $this->ticketSetup($event));
    }

    /** Dropping the fields matters as much as skipping the blocks: fill() takes whatever is posted. */
    public function test_a_curator_cannot_hand_post_the_ticket_setup(): void
    {
        [, , $event, $ticket, $curatorUser, $curator] = $this->listedEvent();
        $before = $this->ticketSetup($event);

        $this->save($curatorUser, $curator, $event, [
            'tickets_enabled' => 0,
            'rsvp_enabled' => 1,
            'rsvp_limit' => 1,
            'ticket_notes' => 'changed by the curator',
            'payment_instructions' => 'pay the curator',
            'registration_url' => 'https://elsewhere.example/buy',
            'ticket_currency_code' => 'USD',
            'coupon_code' => 'CURATOR',
            'terms_url' => 'https://elsewhere.example/terms',
            'expire_unpaid_tickets' => 0,
            'ask_phone' => 0,
            'sell_after_start' => 0,
            'custom_fields' => '{}',
            'tickets' => [['id' => $ticket->id, 'type' => 'CURATOR RENAMED', 'quantity' => 1, 'price' => 1]],
            'promo_codes' => [['code' => 'CURATORCODE', 'type' => 'percentage', 'value' => 100, 'is_active' => 1]],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Renamed', Event::find($event->id)->name, 'the save itself went through');
        $this->assertSame($before, $this->ticketSetup($event));
    }

    /** The same fields in the query string, where input() also reads them. */
    public function test_the_ticket_setup_cannot_be_reached_through_the_query_string_either(): void
    {
        [, , $event, , $curatorUser, $curator] = $this->listedEvent();
        $before = $this->ticketSetup($event);

        $url = route('event.update', ['subdomain' => $curator->subdomain, 'hash' => UrlUtils::encodeId($event->id)])
            .'?tickets_enabled=0&ticket_notes=from+the+query&coupon_code=QUERY';

        $this->actingAs($curatorUser)
            ->put($url, ['name' => 'Renamed', 'starts_at' => $event->starts_at, 'duration' => 2])
            ->assertRedirect();

        $this->assertSame($before, $this->ticketSetup($event));
    }

    /** Control: the owner's own save still changes all of it. */
    public function test_the_owner_still_changes_the_ticket_setup(): void
    {
        [$owner, $venue, $event, $ticket] = $this->listedEvent();

        $this->save($owner, $venue, $event, [
            'tickets_enabled' => 1,
            'ticket_notes' => 'owner notes',
            'ticket_currency_code' => 'USD',
            'terms_url' => 'https://example.org/new-terms',
            'tickets' => [['id' => $ticket->id, 'type' => 'Owner renamed', 'quantity' => 50, 'price' => 30]],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $after = $this->ticketSetup($event);

        $this->assertSame('owner notes', $after['columns']['ticket_notes']);
        $this->assertSame('USD', $after['columns']['ticket_currency_code']);
        $this->assertSame('https://example.org/new-terms', $after['columns']['terms_url']);
        $this->assertSame(['Owner renamed'], array_column($after['tickets'], 'type'));
    }

    /** Control: switching ticketing off still removes the ticket types, as it always has. */
    public function test_the_owner_still_removes_tickets_by_switching_ticketing_off(): void
    {
        [$owner, $venue, $event] = $this->listedEvent();

        $this->save($owner, $venue, $event, ['tickets_enabled' => 0])->assertRedirect();

        $this->assertSame([], $this->ticketSetup($event)['tickets']);
    }

    /**
     * TICKET_PANEL_FIELDS is a hand-kept list, so the panel is its source of truth: a field added
     * to the Tickets panel and not to the list could be hand-posted by someone refused the panel.
     */
    public function test_every_field_of_the_tickets_panel_is_on_the_protected_list(): void
    {
        [$owner, $venue, $event] = $this->listedEvent();

        $html = $this->editPage($owner, $venue, $event);

        $this->assertSame(1, preg_match('/id="section-tickets"(.*?)id="section-[a-z-]+"/s', $html, $panel), 'the Tickets panel was found');

        // Static names, and Vue-bound ones written as a template string: `tickets[${i}][type]`.
        preg_match_all('/(?<![:\w-])name="([a-z_]+)/', $panel[1], $static);
        preg_match_all('/(?::|v-bind:)name="`([a-z_]+)/', $panel[1], $bound);
        $names = array_values(array_unique(array_merge($static[1], $bound[1])));

        // Radios that only drive the panel's own display; the server never reads them.
        $displayOnly = ['promo_ticket_mode_'];

        $this->assertGreaterThan(25, count($names), 'the panel was parsed, not skipped');
        $this->assertSame([], array_values(array_diff($names, EventRepo::TICKET_PANEL_FIELDS, $displayOnly)));
    }

    public function test_someone_refused_the_tickets_panel_is_not_sent_the_ticket_setup(): void
    {
        [, , $event, , $curatorUser, $curator] = $this->listedEvent();

        $html = $this->editPage($curatorUser, $curator, $event);

        $this->assertStringNotContainsString('id="section-tickets"', $html, 'sanity check: this user is refused the panel');
        foreach (self::SECRETS as $secret) {
            $this->assertStringNotContainsString($secret, $html);
        }
        // The page still builds its ticket list, just an empty one, so the form's script runs.
        $this->assertMatchesRegularExpression('/tickets: \[\]\.map\(/', $html);
    }

    /** Control: the owner's page still carries all of it. */
    public function test_the_owner_is_still_sent_the_ticket_setup(): void
    {
        [$owner, $venue, $event] = $this->listedEvent();

        $html = $this->editPage($owner, $venue, $event);

        foreach (self::SECRETS as $secret) {
            $this->assertStringContainsString($secret, $html);
        }
    }
}
