<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\GuestTheme;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The ticket page: what it says in each state a ticket can be in, whose colours it wears, and the
 * few things a holder can do from it.
 */
class GuestTicketPageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $venue;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        // A venue's own event: Event::role() (the performer) is null, which is the case the old
        // page had no schedule for.
        $this->venue = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'The Blue Room',
            'accent_color' => '#dc2626',
            'address1' => '12 Canal Street',
            'city' => 'Springfield',
        ]);
        $this->event = $this->createEvent($this->venue, [
            'name' => 'Late Jazz',
            'tickets_enabled' => true,
            'creator_role_id' => $this->venue->id,
        ]);
        $this->assertNull($this->event->role(), 'fixture: no performer');
    }

    private function sale(array $attrs = [], int $qty = 2): Sale
    {
        $ticket = $this->event->tickets()->first() ?? $this->createTicket($this->event, ['type' => 'General', 'price' => 20, 'quantity' => 50]);

        return $this->createSale($this->event, $this->venue, $attrs + ['name' => 'Sam Rivera', 'payment_amount' => 40], $ticket, $qty);
    }

    private function url(Sale $sale): string
    {
        return route('ticket.view', ['event_id' => UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]);
    }

    private function page(Sale $sale): string
    {
        return $this->get($this->url($sale))->assertOk()->getContent();
    }

    /** The ticket's markup alone: the script after it names the same attributes as selectors. */
    private function main(string $html): string
    {
        $start = strpos($html, '<main');

        return substr($html, $start, strpos($html, '</main>') - $start);
    }

    /** The redirect from a finished checkout, as redirectToPurchaseLanding() leaves the session. */
    private function arriving(Sale $sale): static
    {
        return $this->withSession([
            'cart_purchased' => [['subdomain' => $sale->subdomain, 'event_id' => UrlUtils::encodeId($sale->event_id), 'event_date' => (string) $sale->event_date]],
            '_flash' => ['new' => [], 'old' => ['cart_purchased']],
        ]);
    }

    public function test_a_good_ticket_is_its_code_and_the_code_can_be_enlarged(): void
    {
        $html = $this->main($this->page($this->sale()));

        $this->assertStringContainsString('data-sale-status="paid"', $html);
        $this->assertStringContainsString(__('messages.ticket_show_at_door'), $html);
        $this->assertStringContainsString(__('messages.ticket_admits', ['count' => 2]), $html);

        // The code itself and the button under it both open the door view.
        $this->assertSame(2, substr_count($html, 'data-door-open'));
        $door = substr($html, strpos($html, 'id="ticket-door"'), 1400);
        $this->assertStringContainsString('role="dialog"', $door);
        $this->assertStringContainsString('aria-modal="true"', $door);
        $this->assertStringContainsString('hidden', substr($door, 0, 200), 'closed until asked for');
        $this->assertStringContainsString('Sam Rivera', $door);
        $this->assertStringContainsString(__('messages.ticket_save_code'), $door);

        // It is not inside the ticket, which has a filter: a fixed child of a filtered element is
        // no longer fixed to the screen.
        $this->assertGreaterThan(strpos($html, '</article>'), strpos($html, 'id="ticket-door"'));

        $this->assertStringNotContainsString('gk-tk-stamp', $html);
        $this->assertStringNotContainsString('gk-tk-qr-void', $html);
    }

    public function test_it_wears_the_name_and_colour_of_the_schedule_that_sold_it(): void
    {
        $sale = $this->sale();
        $this->assertSame($this->venue->id, $sale->sellingRole()->id);

        $html = $this->page($sale);

        $theme = GuestTheme::fromAccent('#dc2626');
        $this->assertStringContainsString('--es-glow: '.$theme->glow.';', $html, 'the light is the schedule\'s red, not one violet for everybody');
        $this->assertStringContainsString('--es-glow-ink: '.$theme->glowInk.';', $html);
        $this->assertStringContainsString('| The Blue Room</title>', $html, 'and the tab names the schedule');
        $this->assertStringNotContainsString('violet', $this->main($html));
    }

    public function test_a_ticket_whose_schedule_is_gone_still_opens(): void
    {
        $sale = $this->sale();
        $sale->forceFill(['subdomain' => 'nolongerhere'])->save();

        // The event's own schedule is the fallback.
        $this->assertSame($this->venue->id, $sale->fresh()->sellingRole()->id);
        $this->assertStringContainsString('Late Jazz', $this->page($sale->fresh()));
    }

    public function test_arriving_from_checkout_says_you_are_going_once(): void
    {
        $sale = $this->sale();

        $html = $this->arriving($sale)->get($this->url($sale))->assertOk()->getContent();
        $this->assertStringContainsString('gk-ticket gk-ticket-fresh', $html);
        $this->assertMatchesRegularExpression('/<div class="gk-tk-hero gk-tk-noprint" data-ticket-state="going" data-hero\s*>/', $html, 'shown');
        $this->assertStringContainsString(__('messages.ticket_you_are_going'), $html);

        // A ticket link is opened again for weeks: it does not congratulate every time.
        $again = $this->page($sale);
        $this->assertStringNotContainsString('gk-ticket-fresh"', $again);
        $this->assertMatchesRegularExpression('/data-hero\s+hidden\s*>/', $again);
    }

    /** @return array<string, array{0: array, 1: bool, 2: string, 3: string}> sale attrs, event cancelled, state, stamp key */
    public static function deadTickets(): array
    {
        return [
            'not paid, to pay at the door' => [['status' => 'unpaid', 'payment_method' => 'cash'], false, 'unpaid', 'unpaid'],
            'cancelled' => [['status' => 'cancelled'], false, 'cancelled', 'void'],
            'refunded' => [['status' => 'refunded'], false, 'refunded', 'void'],
            'expired' => [['status' => 'expired'], false, 'expired', 'void'],
            'paid, but the event was cancelled' => [['status' => 'paid'], true, 'event-cancelled', 'cancelled'],
        ];
    }

    #[DataProvider('deadTickets')]
    public function test_a_ticket_that_will_not_be_accepted_says_so_and_is_stamped(array $attrs, bool $eventCancelled, string $state, string $stamp): void
    {
        if ($eventCancelled) {
            $this->event->forceFill(['is_cancelled' => true])->save();
        }

        $main = $this->main($this->page($this->sale($attrs)));

        $this->assertStringContainsString('data-ticket-state="'.$state.'"', $main, 'said before the code');
        $this->assertLessThan(strpos($main, 'gk-tk-qr'), strpos($main, 'data-ticket-state="'.$state.'"'));

        $this->assertStringContainsString('gk-tk-qr gk-tk-qr-void', $main);
        $this->assertMatchesRegularExpression('/<span class="gk-tk-stamp\s*">'.preg_quote(__('messages.'.$stamp), '/').'<\/span>/', $main);

        // Nothing offers to enlarge a code that will not scan, and there is no door view to open.
        $this->assertStringNotContainsString('data-door-open', $main);
        $this->assertStringNotContainsString('id="ticket-door"', $main);
        $this->assertStringNotContainsString(__('messages.ticket_you_are_going'), $main);
    }

    public function test_a_paid_ticket_for_a_cancelled_event_used_to_show_a_working_code(): void
    {
        $this->event->forceFill(['is_cancelled' => true])->save();

        $main = $this->main($this->page($this->sale()));

        $this->assertStringContainsString(__('messages.event_cancelled_heading'), $main);
        // And nothing invites anybody to it.
        $this->assertStringNotContainsString('data-invite', $main);
        $this->assertStringNotContainsString('data-calendar-toggle', $main);
    }

    public function test_an_unpaid_ticket_that_can_still_be_paid_offers_to_finish(): void
    {
        $sale = $this->sale(['status' => 'unpaid', 'payment_method' => 'stripe']);
        $main = $this->main($this->page($sale));

        $this->assertStringContainsString('data-ticket-state="unpaid"', $main);
        $this->assertStringContainsString(__('messages.complete_payment'), $main);
        $this->assertStringContainsString('href="'.e($sale->getEventUrl()).'" class="gk-tk-btn gk-tk-btn-fill', $main);

        // Cash is settled in person: nothing to go back and finish, and it says where to pay.
        $door = $this->sale(['status' => 'unpaid', 'payment_method' => 'cash', 'email' => 'door@example.com']);
        $main = $this->main($this->page($door));
        $this->assertStringNotContainsString(__('messages.complete_payment'), $main);
        $this->assertStringContainsString(__('messages.pay_at_the_door'), $main);
    }

    public function test_back_from_the_provider_ahead_of_its_confirmation_is_not_stamped_unpaid(): void
    {
        // The card checkout's return leaves the sale unpaid on purpose and lets the webhook settle
        // it, so this is the page most card buyers land on.
        $sale = $this->sale(['status' => 'unpaid', 'payment_method' => 'stripe']);

        $main = $this->main($this->arriving($sale)->get($this->url($sale))->assertOk()->getContent());

        $this->assertStringContainsString('data-confirming', $main);
        $this->assertStringContainsString(__('messages.ticket_confirming_payment'), $main);
        $this->assertStringNotContainsString('gk-tk-stamp', $main, 'no NOT PAID across the code of somebody who has just paid');
        $this->assertStringNotContainsString(__('messages.this_ticket_is_not_paid'), $main);
        $this->assertStringNotContainsString(__('messages.complete_payment'), $main, 'and no invitation to pay a second time');
        $this->assertStringNotContainsString('data-door-open', $main, 'but no working code either, until it is confirmed');

        // The same ticket a minute later, opened from the email: it says what is true.
        $later = $this->main($this->page($sale));
        $this->assertStringNotContainsString('data-confirming', $later);
        $this->assertStringContainsString(__('messages.complete_payment'), $later);

        // A method with nothing on its way never claims to be confirming.
        $cash = $this->sale(['status' => 'unpaid', 'payment_method' => 'cash', 'email' => 'door@example.com']);
        $this->assertStringNotContainsString('data-confirming', $this->main($this->arriving($cash)->get($this->url($cash))->getContent()));
    }

    public function test_invite_friends_gives_out_the_event_and_never_the_ticket(): void
    {
        $sale = $this->sale();
        $html = $this->page($sale);

        preg_match('/data-invite data-url="([^"]*)"/', $html, $m);
        $this->assertNotEmpty($m, 'the tile is there');
        $this->assertSame($sale->getEventUrl(), html_entity_decode($m[1]));
        $this->assertStringNotContainsString($sale->secret, $m[1], 'the ticket\'s own address is the ticket');
    }

    public function test_a_venue_gets_directions_and_an_online_event_gets_its_link(): void
    {
        $main = $this->main($this->page($this->sale()));
        $this->assertStringContainsString(__('messages.directions'), $main);
        $this->assertStringContainsString('google.com/maps/search/?api=1&query='.urlencode($this->venue->bestAddress()), $main);
        $this->assertStringContainsString('The Blue Room', $main);

        // Online, on a performer's schedule: no venue, so nowhere to be directed to.
        $talent = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Nightjars']);
        $online = $this->createEvent($talent, ['name' => 'Live Stream', 'tickets_enabled' => true, 'event_url' => 'https://stream.example.org/live', 'creator_role_id' => $talent->id]);
        $ticket = $this->createTicket($online, ['price' => 5, 'quantity' => 50]);
        $sale = $this->createSale($online, $talent, [], $ticket, 1);

        $main = $this->main($this->page($sale));
        $this->assertStringNotContainsString(__('messages.directions'), $main);
        $this->assertStringContainsString('href="https://stream.example.org/live"', $main);
        $this->assertStringContainsString('<dt>'.__('messages.online').'</dt>', $main);
    }

    public function test_what_the_organizer_wants_a_holder_to_know_is_directly_under_the_code(): void
    {
        $this->event->forceFill(['ticket_notes' => 'Doors at seven.', 'ticket_notes_html' => '<p>Doors at seven.</p>'])->save();

        $main = $this->main($this->page($this->sale()));

        $notes = strpos($main, 'Doors at seven.');
        $this->assertNotFalse($notes);
        $this->assertGreaterThan(strpos($main, 'class="gk-tk-qr"'), $notes);
        $this->assertLessThan(strpos($main, 'class="gk-ticket-b"'), $notes, 'above the tear, with the code, not at the foot of the page');
    }

    public function test_giving_up_a_free_place_is_asked_in_the_page(): void
    {
        $free = $this->sale(['payment_amount' => 0]);
        $main = $this->main($this->page($free));

        $form = substr($main, strpos($main, 'id="ticket-cancel"') - 60, 1200);
        $this->assertStringContainsString('hidden', $form, 'closed until Cancel is pressed');
        $this->assertStringNotContainsString('data-confirm', $form, 'not a browser box that says OK and Cancel about a cancellation');
        $this->assertStringContainsString(__('messages.ticket_keep'), $form);
        $this->assertStringContainsString(route('rsvp.cancel', ['sale_id' => UrlUtils::encodeId($free->id)]), $form);

        // A ticket that was paid for is the owner's to refund.
        $paid = $this->sale(['payment_amount' => 40, 'email' => 'paid@example.com']);
        $this->assertStringNotContainsString('id="ticket-cancel"', $this->page($paid));
    }

    public function test_on_paper_it_is_black_on_white_and_only_the_ticket(): void
    {
        $html = $this->page($this->sale());

        preg_match('/@media print \{(.*?)\n    \}\n<\/style>/s', $html, $m);
        $this->assertNotEmpty($m, 'the ticket has a print sheet');
        $this->assertStringContainsString('--tk-ink: #000000;', $m[1]);
        $this->assertStringContainsString('--tk-card: #ffffff;', $m[1]);
        $this->assertMatchesRegularExpression('/\.gk-tk-btn,[^}]*\.gk-door,[^}]*\{ display: none; \}/', $m[1], 'buttons and the door view are not printed');
        $this->assertStringContainsString('.gk-tkpage::before { display: none; }', $m[1], 'nor the light');
    }
}
