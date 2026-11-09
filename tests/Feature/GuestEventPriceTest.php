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

    public function test_several_prices_are_the_range_in_the_events_own_currency(): void
    {
        $event = $this->event(['ticket_currency_code' => 'EUR']);
        $this->createTicket($event, ['type' => 'Balcony', 'price' => 12, 'quantity' => 50]);
        $this->createTicket($event, ['type' => 'Stalls', 'price' => 30, 'quantity' => 50]);

        $this->assertStringContainsString(e(__('messages.price_range', ['min' => MoneyUtils::format(12, 'EUR'), 'max' => MoneyUtils::format(30, 'EUR')])), $this->priceRow($event));
    }

    /**
     * An organizer sold admission at lei 250 and lei 280 beside a free voucher pass (three
     * visits and a discount at the restaurant), and the page said Free entry. A pass is priced
     * for several visits, so it speaks for the event only when passes are all it sells; an
     * add-on is never a way in at all.
     */
    public function test_a_free_pass_or_an_add_on_beside_admission_is_not_the_price(): void
    {
        $event = $this->event(['ticket_currency_code' => 'RON']);
        $this->createTicket($event, ['type' => 'Standard', 'price' => 280, 'quantity' => 50]);
        $this->createTicket($event, ['type' => 'Early Bird', 'price' => 250, 'quantity' => 50]);
        $this->createTicket($event, ['type' => 'Voucher', 'price' => 0, 'quantity' => 50, 'is_pass' => true, 'pass_max_uses' => 3]);
        $this->createTicket($event, ['type' => 'Parking', 'price' => 5, 'quantity' => 50, 'is_addon' => true]);

        $row = $this->priceRow($event);
        $this->assertStringContainsString(e(__('messages.price_range', ['min' => MoneyUtils::format(250, 'RON'), 'max' => MoneyUtils::format(280, 'RON')])), $row);
        $this->assertStringNotContainsString($this->role->customLabel('free_entry'), $row);

        // Passes are all it sells: then the pass is the price.
        $passes = $this->event();
        $this->createTicket($passes, ['type' => 'Ten visits', 'price' => 90, 'quantity' => 50, 'is_pass' => true, 'pass_max_uses' => 10]);
        $this->assertSame(90.0, $passes->fresh()->ticketPriceSummary()['min']);

        // Admission no longer on sale while the pass still is: the pass is what can be bought.
        $late = $this->event();
        $this->createTicket($late, ['type' => 'Early', 'price' => 40, 'quantity' => 50, 'sales_end_at' => now()->subDay()]);
        $this->createTicket($late, ['type' => 'Season', 'price' => 120, 'quantity' => 50, 'is_pass' => true, 'pass_max_uses' => 5]);
        $this->assertSame(120.0, $late->fresh()->ticketPriceSummary()['min']);
    }

    public function test_a_free_type_beside_paid_ones_is_free_to_the_top_price(): void
    {
        $event = $this->event();
        $this->createTicket($event, ['type' => 'Standing', 'price' => 0, 'quantity' => 50]);
        $this->createTicket($event, ['type' => 'Table', 'price' => 40, 'quantity' => 50]);
        $this->createTicket($event, ['type' => 'Booth', 'price' => 90, 'quantity' => 50]);

        $row = $this->priceRow($event);
        $this->assertStringContainsString(e(__('messages.price_free_to', ['max' => MoneyUtils::format(90, 'USD')])), $row);
        $this->assertStringNotContainsString($this->role->customLabel('free_entry'), $row);
    }

    /** Two left-to-right amounts in a right-to-left line: each is isolated, or the higher reads first. */
    public function test_a_range_isolates_its_amounts_in_a_right_to_left_language(): void
    {
        app()->setLocale('he');
        $text = Event::priceText('from', 12, 30, 'USD', true);
        $this->assertSame(__('messages.price_range', ['min' => "\u{2068}".MoneyUtils::format(12, 'USD')."\u{2069}", 'max' => "\u{2068}".MoneyUtils::format(30, 'USD')."\u{2069}"]), $text);

        app()->setLocale('en');
        $this->assertStringNotContainsString("\u{2068}", Event::priceText('from', 12, 30, 'USD', true));
        $this->assertSame(__('messages.price_from', ['price' => MoneyUtils::format(12, 'USD')]), Event::priceText('from', 12, 30, 'USD', false));
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

    public function test_from_is_a_price_somebody_can_still_pay(): void
    {
        $date = now()->addDays(7)->format('Y-m-d');

        // An early bird with no end date that has sold out is still "on sale" by its dates.
        $event = $this->event();
        $early = $this->createTicket($event, ['type' => 'Early bird', 'price' => 10, 'quantity' => 2]);
        $this->createTicket($event, ['type' => 'Regular', 'price' => 20, 'quantity' => 50]);
        $this->createSale($event, $this->role, ['status' => 'paid', 'event_date' => $date], $early, 2);

        $summary = $event->fresh()->ticketPriceSummary($date);
        $this->assertSame(20.0, $summary['min'], 'not "From $10", which nobody can buy');
        $this->assertFalse($summary['from'], 'and one price left is that price');
        $this->assertStringContainsString('$20', $this->priceRow($event));
        $this->assertStringNotContainsString('$10', $this->priceRow($event));

        // Everything gone: the page says Sold out, and the prices stay as they were.
        $gone = $this->event();
        $only = $this->createTicket($gone, ['price' => 15, 'quantity' => 1]);
        $this->createSale($gone, $this->role, ['status' => 'paid', 'event_date' => $date], $only, 1);
        $this->assertSame(15.0, $gone->fresh()->ticketPriceSummary($date)['min']);
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

        // A room of two with nothing sold yet is small, not running out.
        $small = $this->event();
        $this->createTicket($small, ['price' => 20, 'quantity' => 2]);
        $this->assertFalse($small->fresh()->ticketPriceSummary($date)['low']);

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
