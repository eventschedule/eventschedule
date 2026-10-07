<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What the event page says when its tickets cannot be bought right now.
 *
 * Event::canSellTickets() answers "is this event selling" and deliberately ignores stock, and with
 * "show unavailable tickets" on it ignores the per-ticket sales windows too. So an event could be
 * "selling" with nothing to sell, and the page offered Buy tickets all the same. Pressing it hid
 * the page and opened a form holding a name field and an email field: no ticket rows, no total,
 * and no Cancel, because the whole button row sat behind the same "not sold out" test. On a plan
 * with the waitlist, an event whose sales had not opened yet offered a waitlist that
 * WaitlistController then refused ("tickets are still available").
 *
 * Event::ticketSaleState() is now the one answer both buttons and the form read.
 */
class GuestTicketStatesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function eventOn(Role $role, array $attrs = []): Event
    {
        return $this->createEvent($role, $attrs + [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'creator_role_id' => $role->id,
        ]);
    }

    private function page(Event $event, Role $role, string $query = ''): string
    {
        return $this->get($event->fresh()->getGuestUrl($role->subdomain).$query)->assertOk()->getContent();
    }

    /** The markup from one gp- id up to the next one. */
    private function block(string $html, string $id): string
    {
        $start = strpos($html, 'id="'.$id.'"');
        $this->assertNotFalse($start, $id.' is on the page');
        $end = strpos($html, 'id="gp-', $start + strlen($id) + 5);

        return substr($html, $start, $end === false ? 4000 : $end - $start);
    }

    private function soldOutOnAFreeSchedule(): array
    {
        $role = $this->createFreeRole($this->createOwner());
        $event = $this->eventOn($role);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 1]);
        $date = now()->addDays(7)->format('Y-m-d');
        $this->createSale($event, $role, ['status' => 'paid', 'event_date' => $date], $ticket, 1);

        $event = $event->fresh();
        $this->assertTrue($event->canSellTickets($date), 'a surviving $0 row keeps the event "selling"');
        $this->assertTrue($event->allTicketsSoldOut($date));
        $this->assertFalse($event->canOfferWaitlist(), 'a free schedule has no ticket waitlist');

        return [$role, $event];
    }

    public function test_the_sale_state_names_what_is_true(): void
    {
        $role = $this->createRole($this->createOwner());
        $date = now()->addDays(7)->format('Y-m-d');

        $open = $this->eventOn($role);
        $this->createTicket($open, ['price' => 10, 'quantity' => 5]);
        $this->assertSame('open', $open->fresh()->ticketSaleState($date));

        $soon = $this->eventOn($role, ['show_unavailable_tickets' => true]);
        $this->createTicket($soon, ['price' => 10, 'sales_start_at' => now()->addDays(2)]);
        $this->assertSame('not_started', $soon->fresh()->ticketSaleState($date));

        $ended = $this->eventOn($role, ['show_unavailable_tickets' => true]);
        $this->createTicket($ended, ['price' => 10, 'sales_end_at' => now()->subDay()]);
        $this->assertSame('ended', $ended->fresh()->ticketSaleState($date));

        // One row over, one not open yet: nothing can be bought now, and something will be.
        $between = $this->eventOn($role, ['show_unavailable_tickets' => true]);
        $this->createTicket($between, ['type' => 'Early', 'price' => 10, 'sales_end_at' => now()->subDay()]);
        $this->createTicket($between, ['type' => 'Door', 'price' => 20, 'sales_start_at' => now()->addDays(2)]);
        $this->assertSame('not_started', $between->fresh()->ticketSaleState($date));

        // One row over, one on sale: the event is open.
        $mixed = $this->eventOn($role, ['show_unavailable_tickets' => true]);
        $this->createTicket($mixed, ['type' => 'Early', 'price' => 10, 'sales_end_at' => now()->subDay()]);
        $this->createTicket($mixed, ['type' => 'Regular', 'price' => 20]);
        $this->assertSame('open', $mixed->fresh()->ticketSaleState($date));

        $gone = $this->eventOn($role);
        $row = $this->createTicket($gone, ['price' => 10, 'quantity' => 1]);
        $this->createSale($gone, $role, ['status' => 'paid', 'event_date' => $date], $row, 1);
        $this->assertSame(['state' => 'sold_out', 'waitlist' => true, 'rows' => false], $gone->fresh()->ticketSale($date));

        // "rows": nothing to buy, but the owner shows unavailable tickets and there is one to show.
        $this->assertTrue($soon->fresh()->ticketSale($date)['rows']);
        $this->assertFalse($open->fresh()->ticketSale($date)['rows'], 'an open event needs no second way in');
    }

    public function test_sold_out_without_a_waitlist_says_so_instead_of_offering_to_sell(): void
    {
        [$role, $event] = $this->soldOutOnAFreeSchedule();

        $html = $this->page($event, $role);

        foreach (['gp-event-cta', 'gp-mobile-cta'] as $id) {
            $block = $this->block($html, $id);
            $this->assertStringContainsString(__('messages.sold_out'), $block, $id.' says the event is sold out');
            $this->assertStringNotContainsString('show-event-form', $block, $id.' does not open a form with nothing in it');
            $this->assertStringNotContainsString($role->customLabel('get_tickets'), $block);
        }
    }

    public function test_the_form_reached_by_a_link_says_sold_out_and_can_be_closed(): void
    {
        [$role, $event] = $this->soldOutOnAFreeSchedule();

        // An old link, a shared link, or a page left open: the form can still be asked for.
        $html = $this->page($event, $role, '?tickets=true');

        $start = strpos($html, 'id="tickets-unavailable"');
        $this->assertNotFalse($start, 'the form carries its own "nothing to buy" notice');
        $notice = substr($html, $start, 1500);

        $this->assertStringContainsString(__('messages.sold_out'), $notice);
        $this->assertStringContainsString('hideForm', $notice, 'and a way back to the page that does not depend on a ticket being available');
    }

    public function test_tickets_that_are_not_on_sale_yet_are_announced_and_never_offered_as_buy(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->eventOn($role, ['show_unavailable_tickets' => true]);
        $this->createTicket($event, ['price' => 10, 'sales_start_at' => now()->addDays(2)]);
        $date = now()->addDays(7)->format('Y-m-d');

        $this->assertTrue($event->fresh()->canSellTickets($date), '"show unavailable tickets" keeps the event selling');
        $this->assertTrue($event->fresh()->canOfferWaitlist());

        $html = $this->page($event, $role);

        foreach (['gp-event-cta', 'gp-mobile-cta'] as $id) {
            $block = $this->block($html, $id);
            $this->assertStringContainsString(__('messages.sales_not_started'), $block);
            $this->assertStringNotContainsString($role->customLabel('buy_tickets'), $block, $id.' does not say Buy');
            $this->assertStringNotContainsString(__('messages.join_waitlist'), $block, 'a waitlist is for sold out, not for "not yet"');

            // The owner asked for unavailable tickets to be shown: the form lists them greyed,
            // with their prices, so it stays one press away. Taking that away made the setting
            // change nothing a visitor could see.
            $this->assertSame(1, substr_count($block, 'data-sale-rows'), $id.' has a way to the ticket list');
            $this->assertStringContainsString('show-event-form', $block);
        }
    }

    public function test_tickets_whose_sales_ended_are_announced_and_never_offered_as_buy(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->eventOn($role, ['show_unavailable_tickets' => true]);
        $this->createTicket($event, ['price' => 10, 'sales_end_at' => now()->subDay()]);

        $html = $this->page($event, $role);

        foreach (['gp-event-cta', 'gp-mobile-cta'] as $id) {
            $block = $this->block($html, $id);
            $this->assertStringContainsString(__('messages.ticket_sales_ended'), $block);
            $this->assertStringNotContainsString($role->customLabel('buy_tickets'), $block);
            $this->assertSame(1, substr_count($block, 'data-sale-rows'));
        }
    }

    public function test_a_gap_between_two_tiers_says_coming_soon_and_the_form_knows_it_has_nothing(): void
    {
        // One tier over, the next not open, and the owner does NOT show unavailable tickets. The
        // event is still "selling" (neither every row ended nor every row to come), and the form
        // is sent no rows at all. It used to work "sold out" out from its rows and, having none,
        // conclude nothing: a name field, a total of zero and Checkout.
        $role = $this->createRole($this->createOwner());
        $event = $this->eventOn($role);
        $this->createTicket($event, ['type' => 'Early', 'price' => 10, 'sales_end_at' => now()->subDay()]);
        $this->createTicket($event, ['type' => 'Door', 'price' => 20, 'sales_start_at' => now()->addDays(2)]);
        $date = now()->addDays(7)->format('Y-m-d');

        $this->assertTrue($event->fresh()->canSellTickets($date));
        $this->assertSame(['state' => 'not_started', 'waitlist' => false, 'rows' => false], $event->fresh()->ticketSale($date));

        $html = $this->page($event, $role, '?tickets=true');

        foreach (['gp-event-cta', 'gp-mobile-cta'] as $id) {
            $block = $this->block($html, $id);
            $this->assertStringContainsString(__('messages.sales_not_started'), $block);
            $this->assertStringNotContainsString('show-event-form', $block, 'no rows to show, so nothing opens the form');
        }

        // The form is told by the server, and everything it hides behind "nothing to buy" hangs off that.
        $this->assertStringContainsString('saleState: "not_started"', $html);
        $this->assertStringContainsString("if (this.saleState !== 'open' || this.allSoldOut) return true;", $html);
        $this->assertStringContainsString('waitlistOpen: false', $html);
        $this->assertStringContainsString('data-sale-state="not_started"', substr($html, strpos($html, 'id="tickets-unavailable"'), 200));
    }

    public function test_the_only_ticket_on_sale_has_sold_and_another_opens_later(): void
    {
        // The house is not full (the later tier's seats are still there), so "is every ticket
        // sold" says no and the page used to say Buy tickets. Nothing can be bought.
        $role = $this->createRole($this->createOwner());
        $event = $this->eventOn($role);
        $early = $this->createTicket($event, ['type' => 'Early', 'price' => 10, 'quantity' => 1]);
        $this->createTicket($event, ['type' => 'General', 'price' => 20, 'quantity' => 100, 'sales_start_at' => now()->addDays(2)]);
        $date = now()->addDays(7)->format('Y-m-d');
        $this->createSale($event, $role, ['status' => 'paid', 'event_date' => $date], $early, 1);

        $event = $event->fresh();
        $this->assertFalse($event->allTicketsSoldOut($date), 'the house still has the later tier\'s seats');
        $this->assertSame('not_started', $event->ticketSaleState($date));
        $this->assertFalse($event->ticketSale($date)['waitlist'], 'the endpoint would answer "tickets are still available"');

        $html = $this->page($event, $role);
        foreach (['gp-event-cta', 'gp-mobile-cta'] as $id) {
            $block = $this->block($html, $id);
            $this->assertStringContainsString(__('messages.sales_not_started'), $block);
            $this->assertStringNotContainsString($role->customLabel('buy_tickets'), $block);
            $this->assertStringNotContainsString(__('messages.join_waitlist'), $block);
        }
    }

    public function test_a_free_schedule_whose_free_ticket_is_gone_does_not_count_the_tickets_it_may_not_sell(): void
    {
        // The paid row is not for sale on this plan, and the form leaves it out. Its seats used to
        // count as "not sold out", so the page offered a form holding one row with none left.
        $role = $this->createFreeRole($this->createOwner());
        $event = $this->eventOn($role);
        $free = $this->createTicket($event, ['type' => 'Free', 'price' => 0, 'quantity' => 1]);
        $this->createTicket($event, ['type' => 'Paid', 'price' => 25, 'quantity' => 100]);
        $date = now()->addDays(7)->format('Y-m-d');
        $this->createSale($event, $role, ['status' => 'paid', 'event_date' => $date], $free, 1);

        $event = $event->fresh();
        $this->assertTrue($event->canSellTickets($date));
        $this->assertSame(['state' => 'sold_out', 'waitlist' => false, 'rows' => false], $event->ticketSale($date));

        foreach (['gp-event-cta', 'gp-mobile-cta'] as $id) {
            $block = $this->block($this->page($event, $role), $id);
            $this->assertStringContainsString(__('messages.sold_out'), $block);
            $this->assertStringNotContainsString('show-event-form', $block);
        }
    }

    public function test_a_full_house_whose_sales_have_ended_offers_no_waitlist(): void
    {
        // WaitlistController would accept this join (the house is full), and nothing could ever
        // come of it: a seat that came back could not be sold.
        $role = $this->createRole($this->createOwner());
        $event = $this->eventOn($role, ['show_unavailable_tickets' => true]);
        $ticket = $this->createTicket($event, ['price' => 10, 'quantity' => 1, 'sales_end_at' => now()->subDay()]);
        $date = now()->addDays(7)->format('Y-m-d');
        $this->createSale($event, $role, ['status' => 'paid', 'event_date' => $date], $ticket, 1);

        $event = $event->fresh();
        $this->assertTrue($event->allTicketsSoldOut($date));
        $this->assertTrue($event->canOfferWaitlist());
        $this->assertSame('ended', $event->ticketSaleState($date));
        $this->assertFalse($event->ticketSale($date)['waitlist']);

        $html = $this->page($event, $role, '?tickets=true');
        $this->assertStringContainsString('waitlistOpen: false', $html);
        $this->assertStringNotContainsString(__('messages.join_waitlist'), $this->block($html, 'gp-event-cta'));
    }

    public function test_a_draft_gets_add_to_calendar_on_neither_a_laptop_nor_a_phone(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->eventOn($role, ['show_unavailable_tickets' => true, 'is_draft' => true]);
        $this->createTicket($event, ['price' => 10, 'sales_start_at' => now()->addDays(2)]);

        // A draft is seen by its own team.
        $html = $this->actingAs($owner)->get($event->fresh()->getGuestUrl($role->subdomain))->assertOk()->getContent();

        $this->assertStringContainsString(__('messages.sales_not_started'), $this->block($html, 'gp-event-cta'));
        $this->assertStringNotContainsString('calendar-popup-toggle', $this->block($html, 'gp-event-cta'), 'a laptop used to offer it while a phone did not');
        $this->assertStringNotContainsString('id="mobile-calendar-cta"', $html);
    }

    public function test_the_waitlist_in_the_form_is_the_servers_decision(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->eventOn($role, ['show_unavailable_tickets' => true]);
        $this->createTicket($event, ['price' => 10, 'sales_start_at' => now()->addDays(2)]);

        $html = $this->page($event, $role, '?tickets=true');

        $start = strpos($html, 'data-waitlist-block');
        $this->assertNotFalse($start, 'a plan with the waitlist renders its block');
        $tag = substr($html, strrpos(substr($html, 0, $start), '<'), 300);

        // Not the page's own "nothing can be bought", which is also true when no ticket is on
        // sale yet: read there, the form offered a waitlist the endpoint refused.
        $this->assertStringContainsString('v-if="waitlistOpen"', $tag);
        $this->assertStringContainsString('waitlistOpen: false', $html);
        $this->assertStringContainsString('saleState: "not_started"', $html);
    }

    public function test_sold_out_with_a_waitlist_still_offers_it(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->eventOn($role);
        $ticket = $this->createTicket($event, ['price' => 10, 'quantity' => 1]);
        $date = now()->addDays(7)->format('Y-m-d');
        $this->createSale($event, $role, ['status' => 'paid', 'event_date' => $date], $ticket, 1);

        $html = $this->page($event, $role);

        foreach (['gp-event-cta', 'gp-mobile-cta'] as $id) {
            $block = $this->block($html, $id);
            $this->assertStringContainsString(__('messages.join_waitlist'), $block);
            $this->assertStringContainsString('show-event-form', $block);
        }

        $form = $this->page($event, $role, '?tickets=true');
        $this->assertStringContainsString('saleState: "sold_out"', $form);
        $this->assertStringContainsString('waitlistOpen: true', $form);
    }

    public function test_an_event_with_tickets_on_sale_keeps_its_button(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->eventOn($role);
        $this->createTicket($event, ['price' => 10, 'quantity' => 5]);

        $html = $this->page($event, $role);

        foreach (['gp-event-cta', 'gp-mobile-cta'] as $id) {
            $block = $this->block($html, $id);
            $this->assertStringContainsString($role->customLabel('buy_tickets'), $block);
            $this->assertStringContainsString('show-event-form', $block);
        }
        $this->assertStringNotContainsString(__('messages.sold_out').'</', $this->block($html, 'gp-event-cta'));
    }

    public function test_a_full_sign_up_says_it_is_full_before_offering_the_waitlist(): void
    {
        $role = $this->createRole($this->createOwner());
        $date = now()->addDays(7)->format('Y-m-d');
        $event = $this->createEvent($role, [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'rsvp_enabled' => true,
            'rsvp_limit' => 1,
            'creator_role_id' => $role->id,
        ]);
        $event->updateRsvpSold($date, 1);

        $html = $this->page($event, $role, '?rsvp=true');

        $start = strpos($html, 'id="rsvp-form"');
        $this->assertNotFalse($start);
        $form = substr($html, $start, 2500);

        // The waitlist fields used to open under a button that said Join waitlist and nothing
        // else: no line said the event was full.
        $this->assertStringContainsString(__('messages.registration_full'), $form);
        $this->assertLessThan(strpos($form, 'id="waitlist_name"'), strpos($form, __('messages.registration_full')), 'said before the fields, not after');
    }

    public function test_the_embedded_sign_up_form_is_not_drawn_once_sign_up_has_closed(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'rsvp_enabled' => true,
            'is_cancelled' => true,
            'creator_role_id' => $role->id,
        ]);

        $this->assertFalse($event->fresh()->canAcceptRsvp(now()->addDays(7)->format('Y-m-d')), 'TicketController::rsvp() would refuse this');

        $html = $this->get($event->fresh()->getGuestUrl($role->subdomain).'?embed=true&rsvp=true')->assertOk()->getContent();

        $this->assertStringNotContainsString('id="rsvp-form"', $html, 'a form whose submission is refused');
        $this->assertStringContainsString(__('messages.registration_not_available_embed'), $html);
    }
}
