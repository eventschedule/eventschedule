<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What an event page says to a signed-in visitor who already holds tickets for it.
 *
 * It said "You're registered!" with one link, whatever they held: not how many, to somebody
 * deciding whether to get one more, and one ticket page of the two that somebody who bought twice
 * has.
 */
class GuestEventOwnTicketsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    private Event $event;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = $this->createRole($this->createOwner());
        $this->event = $this->createEvent($this->role, ['tickets_enabled' => true, 'creator_role_id' => $this->role->id]);
        $this->buyer = User::factory()->create(['email' => 'sam@gmail.com', 'email_verified_at' => now()]);
    }

    private function buy(int $qty, array $attrs = []): Sale
    {
        $ticket = $this->event->tickets()->first() ?? $this->createTicket($this->event, ['price' => 10, 'quantity' => 50]);

        return $this->createSale($this->event, $this->role, $attrs + ['user_id' => $this->buyer->id, 'email' => 'sam@gmail.com'], $ticket, $qty);
    }

    /** The indicator's own markup: from its count to the end of its links. */
    private function indicator(?User $as = null): ?string
    {
        $html = ($as ? $this->actingAs($as) : $this)->get($this->event->fresh()->getGuestUrl($this->role->subdomain))->assertOk()->getContent();
        $start = strpos($html, 'data-user-tickets=');

        return $start === false ? null : substr($html, $start, strpos($html, '</div>', $start) - $start);
    }

    private function links(string $indicator): array
    {
        preg_match_all('~href="[^"]*/ticket/view/[^/"]+/([^"]+)"~', $indicator, $m);

        return $m[1];
    }

    public function test_it_says_how_many_and_links_the_ticket(): void
    {
        $sale = $this->buy(2);
        $indicator = $this->indicator($this->buyer);

        $this->assertStringContainsString('data-user-tickets="2"', $indicator);
        $this->assertStringContainsString(trans_choice('messages.you_have_tickets', 2, ['count' => 2]), $indicator);
        $this->assertSame('You have 2 tickets', trans_choice('messages.you_have_tickets', 2, ['count' => 2]));
        $this->assertSame([$sale->secret], $this->links($indicator));
        $this->assertStringNotContainsString(__('messages.you_are_registered'), $indicator);

        // Nobody else is told anything.
        $this->assertNull($this->indicator(User::factory()->create(['email_verified_at' => now()])));
        auth()->logout();
        $this->assertNull($this->indicator());
    }

    public function test_somebody_who_bought_twice_has_both_tickets_and_the_sum(): void
    {
        $first = $this->buy(2);
        $second = $this->buy(1);
        $indicator = $this->indicator($this->buyer);

        $this->assertStringContainsString('data-user-tickets="3"', $indicator);
        $this->assertSame([$second->secret, $first->secret], $this->links($indicator), 'each has its own page and its own code, newest first');
        $this->assertStringContainsString(__('messages.view_ticket').' 1', preg_replace('/\s+/', ' ', strip_tags($indicator)));
        $this->assertStringContainsString(__('messages.view_ticket').' 2', preg_replace('/\s+/', ' ', strip_tags($indicator)));

        // One that was refunded is not a ticket.
        Sale::whereKey($second->id)->update(['status' => 'refunded']);
        $indicator = $this->indicator($this->buyer);
        $this->assertStringContainsString('data-user-tickets="2"', $indicator);
        $this->assertSame([$first->secret], $this->links($indicator));
    }

    public function test_a_party_is_counted_once(): void
    {
        // The buyer's own sale leads a party of three; one guest's row carries the buyer's
        // address too (they typed it twice). Three places, not four.
        $lead = $this->buy(1);
        Sale::whereKey($lead->id)->update(['group_id' => $lead->id]);
        $this->buy(1, ['group_id' => $lead->id, 'name' => 'Guest One']);
        $other = $this->buy(1, ['group_id' => $lead->id, 'name' => 'Guest Two', 'email' => 'guest@gmail.com', 'user_id' => null]);
        $this->assertSame(3, $lead->fresh()->legTotalQuantity(), 'fixture');

        $this->assertStringContainsString('data-user-tickets="3"', $this->indicator($this->buyer));

        // And a guest of somebody else's party, signed in, has the one place that is theirs.
        $guest = User::factory()->create(['email' => 'guest@gmail.com', 'email_verified_at' => now()]);
        $indicator = $this->indicator($guest);
        $this->assertStringContainsString('data-user-tickets="1"', $indicator);
        $this->assertSame([$other->secret], $this->links($indicator));
    }

    public function test_a_sign_up_has_no_tickets_to_count(): void
    {
        $this->createSale($this->event, $this->role, ['user_id' => $this->buyer->id, 'email' => 'sam@gmail.com', 'payment_method' => 'rsvp']);
        $indicator = $this->indicator($this->buyer);

        $this->assertStringContainsString('data-user-tickets="0"', $indicator);
        $this->assertStringContainsString(e(__('messages.you_are_registered')), $indicator);
    }
}
