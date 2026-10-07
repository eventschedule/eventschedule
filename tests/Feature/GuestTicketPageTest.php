<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\Payments\PaymentGatewayDriver;
use App\Utils\GuestTheme;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
            PaymentGatewayDriver::LANDED_SESSION => [$sale->id => now()->getTimestamp()],
        ]);
    }

    public function test_a_good_ticket_is_its_code_and_the_code_can_be_enlarged(): void
    {
        $page = $this->page($this->sale());
        $html = $this->main($page);

        $this->assertStringContainsString('data-sale-status="paid"', $html);
        $this->assertStringContainsString(__('messages.ticket_show_at_door'), $html);
        $this->assertStringContainsString(__('messages.ticket_admits', ['count' => 2]), $html);

        // The code itself and the button under it both open the door view.
        $this->assertSame(2, substr_count($html, 'data-door-open'));
        $this->assertSame(1, preg_match('/<div class="gk-door" id="ticket-door" role="dialog" aria-modal="true"[^>]*\shidden>/', $page), 'a dialog, closed until asked for');
        $door = substr($page, strpos($page, 'id="ticket-door"'), 1400);
        $this->assertStringContainsString('Sam Rivera', $door);
        $this->assertStringContainsString(__('messages.ticket_save_code'), $door);

        // It is not inside the ticket, which has a filter: a fixed child of a filtered element is
        // no longer fixed to the screen. And not inside <main>, which is a stacking context of
        // its own: in there the cookie notice lay over the code, whatever z-index the door had.
        $this->assertStringNotContainsString('id="ticket-door"', $html);
        $this->assertGreaterThan(strpos($page, '</main>'), strpos($page, 'id="ticket-door"'));
        $this->assertSame(1, preg_match('/\.gk-tkpage \{[^}]*z-index: 0;/', $page), 'fixture: the page is still a stacking context');

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
            'not paid, and the event was cancelled' => [['status' => 'unpaid', 'payment_method' => 'stripe'], true, 'event-cancelled', 'cancelled'],
            'refunded, because the event was cancelled' => [['status' => 'refunded'], true, 'event-cancelled', 'cancelled'],
        ];
    }

    #[DataProvider('deadTickets')]
    public function test_a_ticket_that_will_not_be_accepted_says_so_and_is_stamped(array $attrs, bool $eventCancelled, string $state, string $stamp): void
    {
        if ($eventCancelled) {
            $this->event->forceFill(['is_cancelled' => true])->save();
        }

        $page = $this->page($this->sale($attrs));
        $main = $this->main($page);

        $this->assertStringContainsString('data-ticket-state="'.$state.'"', $main, 'said before the code');
        $this->assertLessThan(strpos($main, 'gk-tk-qr'), strpos($main, 'data-ticket-state="'.$state.'"'));

        $this->assertStringContainsString('gk-tk-qr gk-tk-qr-void', $main);
        $this->assertMatchesRegularExpression('/<span class="gk-tk-stamp\s*">'.preg_quote(__('messages.'.$stamp), '/').'<\/span>/', $main);

        // Nothing offers to enlarge a code that will not scan, and there is no door view to open.
        $this->assertStringNotContainsString('data-door-open', $main);
        $this->assertStringNotContainsString('id="ticket-door"', $page);
        $this->assertStringNotContainsString(__('messages.ticket_you_are_going'), $main);

        // One answer, top to bottom. A cancelled event with an unpaid sale used to say "this
        // ticket is not valid" above a code stamped UNPAID and "Payment required to enter".
        if ($eventCancelled) {
            $this->assertStringNotContainsString(__('messages.payment_required_to_enter'), $main);
            $this->assertStringNotContainsString(__('messages.complete_payment'), $main);
            $this->assertSame(($attrs['status'] ?? null) === 'refunded', str_contains($main, __('messages.this_ticket_is_refunded')), 'a refund is said, to the buyer who came looking for it');
        }
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

        $this->assertSame(1, preg_match('/data-confirming="(\d+)" data-status-url="([^"]+)"/', $main, $m), 'it says for how long, and where to ask');
        $this->assertGreaterThan(80, (int) $m[1]);
        $this->assertSame(route('ticket.status', ['event_id' => UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret], false), $m[2]);
        $this->assertStringContainsString(__('messages.ticket_confirming_payment'), $main);
        $this->assertStringNotContainsString('gk-tk-stamp', $main, 'no NOT PAID across the code of somebody who has just paid');
        $this->assertStringNotContainsString(__('messages.this_ticket_is_not_paid'), $main);
        $this->assertStringNotContainsString(__('messages.complete_payment'), $main, 'and no invitation to pay a second time');
        $this->assertStringNotContainsString('data-door-open', $main, 'but no working code either, until it is confirmed');

        // Loaded again, as an impatient buyer does: the same. It used to ride on a flash that
        // was gone by now, and said NOT PAID.
        $again = $this->main($this->page($sale));
        $this->assertStringContainsString('data-confirming="', $again);
        $this->assertStringNotContainsString('gk-tk-stamp', $again);

        // What the page asks while it waits: one word, not the page.
        $this->getJson($m[2])->assertOk()->assertExactJson(['status' => 'unpaid']);
        $this->getJson(route('ticket.status', ['event_id' => UrlUtils::encodeId($sale->event_id), 'secret' => 'not-the-secret']))->assertNotFound();
        $script = substr($this->page($sale), strpos($this->page($sale), '</main>'));
        $this->assertStringContainsString("fetch(url, { credentials: 'same-origin', cache: 'no-store'", $script);
        $this->assertStringNotContainsString('fetch(window.location.href', $script, 'it no longer asks for its own whole page');

        // Two minutes on, or opened from the email on another device: it says what is true.
        $this->travel(2)->minutes();
        $later = $this->main($this->page($sale));
        $this->assertStringNotContainsString('data-confirming', $later);
        $this->assertStringContainsString(__('messages.complete_payment'), $later);
        $this->travelBack();

        $elsewhere = $this->main($this->flushSession()->page($sale));
        $this->assertStringNotContainsString('data-confirming', $elsewhere);

        // A method with nothing on its way never claims to be confirming.
        $cash = $this->sale(['status' => 'unpaid', 'payment_method' => 'cash', 'email' => 'door@example.com']);
        $this->assertStringNotContainsString('data-confirming', $this->main($this->arriving($cash)->get($this->url($cash))->getContent()));
    }

    public function test_the_waiting_ticket_asks_on_a_budget_of_its_own(): void
    {
        // The limiter middleware steps aside under APP_TESTING, so the route is checked by its
        // parts: the name the route asks for is registered, and its key is the real visitor and
        // the ticket, not the edge address every visitor shares.
        $route = app('router')->getRoutes()->getByName('ticket.status');
        $this->assertContains('throttle:ticket_status', $route->gatherMiddleware());
        $this->assertNotContains('throttle:100,1', $route->gatherMiddleware(), 'not the counter the routes around it share');

        $limiter = RateLimiter::limiter('ticket_status');
        $this->assertNotNull($limiter);

        config(['app.hosted' => true]);
        $key = function (string $visitor, string $secret) use ($limiter, $route) {
            $request = Request::create('/ticket/status/abc/'.$secret, 'GET', server: ['REMOTE_ADDR' => '198.51.100.1', 'HTTP_CF_CONNECTING_IP' => $visitor]);
            $request->setRouteResolver(fn () => (clone $route)->bind($request));

            return $limiter($request)->key;
        };

        $this->assertNotSame($key('203.0.113.9', 'one'), $key('203.0.113.10', 'one'), 'two buyers behind one edge address');
        $this->assertNotSame($key('203.0.113.9', 'one'), $key('203.0.113.9', 'two'), 'two tickets of one buyer');
        $this->assertStringNotContainsString('203.0.113.9', $key('203.0.113.9', 'one'));
        $this->assertStringNotContainsString('one', substr($key('203.0.113.9', 'one'), strlen('ticket-status|')), 'and the ticket\'s secret is not kept in the key');
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
        // A row of the facts is a term and its descriptions and nothing else.
        $this->assertSame(1, preg_match('/<div class="gk-tk-fact">\s*<dt><span class="gk-tk-ico gk-tk-ico-b" aria-hidden="true">.*?<\/span>'.preg_quote(__('messages.online'), '/').'<\/dt>\s*<dd>/s', $main));
        $this->assertSame(0, preg_match('/<div class="gk-tk-fact">\s*<(?!dt)/', $main));
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
        $this->assertSame(1, preg_match('/<form id="ticket-cancel"[^>]*\shidden\s[^>]*>/', $form), 'closed until Cancel is pressed');
        $this->assertStringContainsString('role="group" aria-labelledby="ticket-cancel-question"', $form, 'the question is the group\'s name, so it is heard');
        $this->assertStringContainsString('<p id="ticket-cancel-question">'.__('messages.are_you_sure').'</p>', $form);
        $this->assertStringContainsString('<noscript><style', $main, 'and without scripts the question is simply there');
        $this->assertStringNotContainsString('data-confirm', $form, 'not a browser box that says OK and Cancel about a cancellation');
        $this->assertStringContainsString(__('messages.ticket_keep'), $form);
        $this->assertStringContainsString(route('rsvp.cancel', ['sale_id' => UrlUtils::encodeId($free->id)]), $form);

        // A ticket that was paid for is the owner's to refund.
        $paid = $this->sale(['payment_amount' => 40, 'email' => 'paid@example.com']);
        $this->assertStringNotContainsString('id="ticket-cancel"', $this->page($paid));
    }

    public function test_a_name_somebody_else_took_since_is_not_the_schedule_that_sold_it(): void
    {
        // A curator lists the venue's event and sells a ticket from its own page.
        $curator = $this->createCurator($this->createOwner(), ['name' => 'City Listings', 'accent_color' => '#16a34a']);
        $this->event->roles()->attach($curator->id, ['is_accepted' => true]);
        $sale = $this->sale(['subdomain' => $curator->subdomain]);
        $sale->forceFill(['subdomain' => $curator->subdomain])->save();

        $this->assertSame($curator->id, $sale->fresh()->sellingRole()->id, 'the schedule the buyer was on, while it still has the event');
        $this->assertStringContainsString('| City Listings</title>', $this->page($sale->fresh()));

        // The curator renames itself, and a stranger registers the name it left. The sale still
        // carries that name; the stranger has nothing to do with the event.
        $curator->forceFill(['subdomain' => 'citylistingsnew'])->save();
        $stranger = $this->createRole($this->createOwner(), 'venue', ['name' => 'Somebody Else']);
        $stranger->forceFill(['subdomain' => $sale->subdomain])->save();

        $this->assertSame($this->venue->id, $sale->fresh()->sellingRole()->id);
        $html = $this->page($sale->fresh());
        $this->assertStringNotContainsString('Somebody Else', $html);
        $this->assertStringContainsString('| The Blue Room</title>', $html);
    }

    public function test_the_contact_names_who_the_mail_goes_to_and_shows_the_address(): void
    {
        $main = $this->main($this->page($this->sale()));
        $email = $this->event->user->email;

        $this->assertStringContainsString('<a href="mailto:'.$email.'">'.__('messages.ticket_contact_organizer', ['name' => 'The Blue Room']).'</a>', $main);
        $this->assertStringContainsString('<span class="gk-tk-foot-addr"><bdi>'.$email.'</bdi></span>', $main, 'readable on paper and on a phone with no mail app');
    }

    public function test_inside_a_frame_a_link_to_the_event_opens_outside_it(): void
    {
        // The event page refuses to be framed without embed=true, so a plain link to it turned
        // the organizer's frame into the browser's refusal page.
        $sale = $this->sale(['status' => 'unpaid', 'payment_method' => 'stripe']);
        $framed = $this->main($this->get($this->url($sale).'?embed=true')->assertOk()->getContent());

        $this->assertSame(2, preg_match_all('/<a href="'.preg_quote(e($sale->getEventUrl()), '/').'" class="gk-tk-(?:back|btn gk-tk-btn-fill gk-tk-btn-block)" target="_blank" rel="noopener noreferrer">/', $framed));
        $this->assertStringNotContainsString('target="_blank"', substr($this->main($this->page($sale)), 0, 900), 'and on its own page it stays in the tab');
    }

    public function test_its_buttons_take_the_dark_colours_whatever_the_visitors_mode(): void
    {
        // A black accent: made for a white panel it is a black fill, which on the ticket's
        // near-black card was a label with no button around it.
        $this->venue->forceFill(['accent_color' => '#000000'])->save();
        $html = $this->page($this->sale());

        [$light, $dark] = GuestTheme::fromAccent('#000000')->tokens();
        $this->assertNotSame($light['--es-accent'], $dark['--es-accent'], 'fixture: the two modes differ for black');
        $this->assertSame(1, preg_match('/\n\s*\.gk-tkpage \{ --es-accent: '.preg_quote($dark['--es-accent'], '/').';[^}]*--es-accent-text: '.preg_quote($dark['--es-accent-text'], '/').';/', $html));
    }

    public function test_every_wording_the_ticket_pages_ask_for_exists(): void
    {
        // The pass cancellation's warning was asked for by a key that is in no language file, so
        // the browser box read "messages.pass_cancel_forfeit_confirm" and OK forfeited the visit.
        $english = require resource_path('lang/en/messages.php');
        $views = ['ticket/view', 'ticket/order', 'ticket/partials/confirming-script', 'installment/pay', 'partials/installment-plan-panel'];

        foreach ($views as $view) {
            preg_match_all("/__\('messages\.([a-z0-9_]+)'/", file_get_contents(resource_path('views/'.$view.'.blade.php')), $m);
            foreach (array_unique($m[1]) as $key) {
                $this->assertArrayHasKey($key, $english, $view.' asks for messages.'.$key);
            }
        }

        $source = file_get_contents(resource_path('views/ticket/view.blade.php'));
        $this->assertStringContainsString("__('messages.pass_forfeit_warning')", $source);
        // An inline display on something marked "not on paper" outranks the print sheet.
        $this->assertSame(0, preg_match('/class="[^"]*gk-tk-noprint[^"]*" style="[^"]*display:/', $source));
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
