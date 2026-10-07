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
        $this->assertSame('sold_out', $gone->fresh()->ticketSaleState($date));
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

    public function test_tickets_that_are_not_on_sale_yet_are_announced_not_offered(): void
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
            $this->assertStringNotContainsString('show-event-form', $block);
            $this->assertStringNotContainsString(__('messages.join_waitlist'), $block, 'a waitlist is for sold out, not for "not yet"');
        }
    }

    public function test_tickets_whose_sales_ended_are_announced_not_offered(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->eventOn($role, ['show_unavailable_tickets' => true]);
        $this->createTicket($event, ['price' => 10, 'sales_end_at' => now()->subDay()]);

        $html = $this->page($event, $role);

        foreach (['gp-event-cta', 'gp-mobile-cta'] as $id) {
            $block = $this->block($html, $id);
            $this->assertStringContainsString(__('messages.ticket_sales_ended'), $block);
            $this->assertStringNotContainsString('show-event-form', $block);
        }
    }

    public function test_the_waitlist_in_the_form_follows_the_servers_sold_out_not_the_pages_guess(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->eventOn($role, ['show_unavailable_tickets' => true]);
        $this->createTicket($event, ['price' => 10, 'sales_start_at' => now()->addDays(2)]);

        $html = $this->page($event, $role, '?tickets=true');

        $start = strpos($html, 'data-waitlist-block');
        $this->assertNotFalse($start, 'a plan with the waitlist renders its block');
        $tag = substr($html, strrpos(substr($html, 0, $start), '<'), 300);

        // isAllSoldOut is also true when nothing is on sale yet; allSoldOut is the server's
        // answer to "is every ticket sold", which is the only thing WaitlistController accepts.
        $this->assertStringContainsString('v-if="allSoldOut"', $tag);
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
