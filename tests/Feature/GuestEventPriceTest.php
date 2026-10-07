<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Utils\MoneyUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The event page says what a ticket costs, and that few are left, before anybody presses Buy.
 * It used to say nothing about price until the form was open.
 */
class GuestEventPriceTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = $this->createRole($this->createOwner());
    }

    private function event(array $attrs = []): Event
    {
        return $this->createEvent($this->role, $attrs + [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'ticket_currency_code' => 'USD',
            'creator_role_id' => $this->role->id,
        ]);
    }

    /** The price row's own markup, or null when the page has none. */
    private function priceRow(Event $event): ?string
    {
        $html = $this->get($event->fresh()->getGuestUrl($this->role->subdomain))->assertOk()->getContent();

        $this->assertLessThanOrEqual(1, substr_count($html, 'id="gp-event-price"'), 'a promised id is on the page once');
        $start = strpos($html, 'id="gp-event-price"');

        return $start === false ? null : substr($html, $start, strpos($html, 'id="gp-event-cta"') - $start);
    }

    public function test_one_price_is_that_price(): void
    {
        $event = $this->event();
        $this->createTicket($event, ['price' => 20, 'quantity' => 50]);

        $row = $this->priceRow($event);
        $this->assertNotNull($row, 'a ticketed event says what it costs');
        $this->assertStringContainsString(MoneyUtils::format(20, 'USD'), $row);
        $this->assertStringNotContainsString(__('messages.price_from', ['price' => '']), $row);
    }

    public function test_several_prices_are_from_the_lowest_in_the_events_own_currency(): void
    {
        $event = $this->event(['ticket_currency_code' => 'EUR']);
        $this->createTicket($event, ['type' => 'Balcony', 'price' => 12, 'quantity' => 50]);
        $this->createTicket($event, ['type' => 'Stalls', 'price' => 30, 'quantity' => 50]);

        $this->assertStringContainsString(__('messages.price_from', ['price' => MoneyUtils::format(12, 'EUR')]), $this->priceRow($event));
    }

    public function test_free_is_said_in_the_owners_words(): void
    {
        $this->role->forceFill(['custom_labels' => ['free_entry' => ['value' => 'No cover charge']]])->save();
        $event = $this->event();
        $this->createTicket($event, ['price' => 0, 'quantity' => 50]);

        $this->assertStringContainsString('No cover charge', $this->priceRow($event));
    }

    public function test_a_ticket_not_on_sale_yet_shows_its_price_only_if_the_owner_shows_it(): void
    {
        // The on-sale ticket decides the line while there is one.
        $mixed = $this->event(['show_unavailable_tickets' => true]);
        $this->createTicket($mixed, ['type' => 'Now', 'price' => 25, 'quantity' => 50]);
        $this->createTicket($mixed, ['type' => 'Later', 'price' => 5, 'quantity' => 50, 'sales_start_at' => now()->addDays(2)]);
        $row = $this->priceRow($mixed);
        $this->assertStringContainsString(MoneyUtils::format(25, 'USD'), $row);
        $this->assertStringNotContainsString(MoneyUtils::format(5, 'USD'), $row, 'a price nobody can pay yet is not the headline');

        // Nothing on sale, tickets shown: their price is public, so the page says it.
        $shown = $this->event(['show_unavailable_tickets' => true]);
        $this->createTicket($shown, ['price' => 15, 'quantity' => 50, 'sales_start_at' => now()->addDays(2)]);
        $this->assertStringContainsString(MoneyUtils::format(15, 'USD'), $this->priceRow($shown));
        $this->assertNull($shown->fresh()->ticketPriceSummary()['low'] ?: null);

        // Nothing on sale, tickets hidden: an owner who hides tickets has not published prices.
        $hidden = $this->event();
        $this->createTicket($hidden, ['type' => 'Early', 'price' => 15, 'quantity' => 50, 'sales_end_at' => now()->subDay()]);
        $this->createTicket($hidden, ['type' => 'Door', 'price' => 25, 'quantity' => 50, 'sales_start_at' => now()->addDays(2)]);
        $this->assertNull($hidden->fresh()->ticketPriceSummary());
        $this->assertNull($this->priceRow($hidden));
    }

    public function test_few_left_is_said_without_a_number_and_only_when_it_is_true(): void
    {
        $date = now()->addDays(7)->format('Y-m-d');

        $nearly = $this->event();
        $ticket = $this->createTicket($nearly, ['price' => 20, 'quantity' => 20]);
        $this->createSale($nearly, $this->role, ['status' => 'paid', 'event_date' => $date], $ticket, 18);
        $row = $this->priceRow($nearly);
        $this->assertStringContainsString(__('messages.few_left'), $row);
        $this->assertDoesNotMatchRegularExpression('/data-ticket-low[^<]*>[^<]*\d/', $row, 'never how many');

        $plenty = $this->event();
        $ticket = $this->createTicket($plenty, ['price' => 20, 'quantity' => 20]);
        $this->createSale($plenty, $this->role, ['status' => 'paid', 'event_date' => $date], $ticket, 5);
        $this->assertStringNotContainsString(__('messages.few_left'), $this->priceRow($plenty));

        // No ceiling, so nothing is running out.
        $open = $this->event();
        $this->createTicket($open, ['price' => 20, 'quantity' => 0]);
        $this->assertFalse($open->fresh()->ticketPriceSummary($date)['low']);

        // Sold out is said by the button, not as "few left".
        $gone = $this->event();
        $ticket = $this->createTicket($gone, ['price' => 20, 'quantity' => 2]);
        $this->createSale($gone, $this->role, ['status' => 'paid', 'event_date' => $date], $ticket, 2);
        $this->assertFalse($gone->fresh()->ticketPriceSummary($date)['low']);
    }

    public function test_a_sign_up_and_an_event_with_nothing_to_sell_keep_their_own_line_or_none(): void
    {
        $signup = $this->event(['tickets_enabled' => false, 'rsvp_enabled' => true]);
        $this->assertStringContainsString(__('messages.free_entry'), $this->priceRow($signup));

        $plain = $this->event(['tickets_enabled' => false]);
        $this->assertNull($this->priceRow($plain));
    }
}
