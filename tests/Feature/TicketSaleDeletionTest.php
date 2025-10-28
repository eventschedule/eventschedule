<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\User;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TicketSaleDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.backend' => null]);
    }

    public function test_deleting_sale_releases_ticket_inventory(): void
    {
        $user = User::factory()->create();

        $creatorRole = Role::withoutEvents(function () use ($user) {
            return Role::create([
                'user_id' => $user->id,
                'subdomain' => 'delete-sale-creator',
                'type' => 'talent',
                'name' => 'Delete Sale Creator',
                'email' => 'creator-delete@example.com',
                'timezone' => 'UTC',
                'language_code' => 'en',
            ]);
        });

        $venueRole = Role::withoutEvents(function () {
            return Role::create([
                'subdomain' => 'delete-sale-venue',
                'type' => 'venue',
                'name' => 'Delete Sale Venue',
                'email' => 'venue-delete@example.com',
                'timezone' => 'UTC',
                'language_code' => 'en',
            ]);
        });

        $event = Event::withoutEvents(function () use ($user, $creatorRole, $venueRole) {
            return Event::create([
                'user_id' => $user->id,
                'creator_role_id' => $creatorRole->id,
                'role_id' => $creatorRole->id,
                'venue_id' => $venueRole->id,
                'name' => 'Delete Sale Event',
                'slug' => 'delete-sale-event',
                'starts_at' => Carbon::parse('2024-06-02 20:00:00'),
                'tickets_enabled' => true,
                'ticket_currency_code' => 'USD',
                'payment_method' => 'stripe',
                'expire_unpaid_tickets' => 0,
                'total_tickets_mode' => 'individual',
            ]);
        });

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'type' => 'General Admission',
            'quantity' => 25,
            'price' => 10,
        ]);

        $eventDate = '2024-06-01';

        $sale = Sale::create([
            'event_id' => $event->id,
            'name' => 'Sale Delete Tester',
            'email' => 'tester@example.com',
            'secret' => Str::random(32),
            'event_date' => $eventDate,
            'subdomain' => $creatorRole->subdomain,
        ]);

        $sale->saleTickets()->create([
            'ticket_id' => $ticket->id,
            'quantity' => 4,
            'seats' => json_encode(array_fill(0, 4, null)),
        ]);

        $ticket->refresh();
        $sold = json_decode($ticket->sold, true);
        $this->assertSame(4, $sold[$eventDate]);

        $response = $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), [
                'action' => 'delete',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $sale->refresh();
        $ticket->refresh();

        $this->assertTrue($sale->is_deleted);

        $updatedSold = json_decode($ticket->sold, true);
        $this->assertSame(0, $updatedSold[$eventDate]);
    }

    public function test_deleting_cancelled_sale_does_not_double_release_inventory(): void
    {
        $user = User::factory()->create();

        $creatorRole = Role::withoutEvents(function () use ($user) {
            return Role::create([
                'user_id' => $user->id,
                'subdomain' => 'cancelled-sale-creator',
                'type' => 'talent',
                'name' => 'Cancelled Sale Creator',
                'email' => 'creator-cancelled@example.com',
                'timezone' => 'UTC',
                'language_code' => 'en',
            ]);
        });

        $venueRole = Role::withoutEvents(function () {
            return Role::create([
                'subdomain' => 'cancelled-sale-venue',
                'type' => 'venue',
                'name' => 'Cancelled Sale Venue',
                'email' => 'venue-cancelled@example.com',
                'timezone' => 'UTC',
                'language_code' => 'en',
            ]);
        });

        $event = Event::withoutEvents(function () use ($user, $creatorRole, $venueRole) {
            return Event::create([
                'user_id' => $user->id,
                'creator_role_id' => $creatorRole->id,
                'role_id' => $creatorRole->id,
                'venue_id' => $venueRole->id,
                'name' => 'Cancelled Sale Event',
                'slug' => 'cancelled-sale-event',
                'starts_at' => Carbon::parse('2024-07-02 20:00:00'),
                'tickets_enabled' => true,
                'ticket_currency_code' => 'USD',
                'payment_method' => 'stripe',
                'expire_unpaid_tickets' => 0,
                'total_tickets_mode' => 'individual',
            ]);
        });

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'type' => 'VIP',
            'quantity' => 10,
            'price' => 20,
        ]);

        $eventDate = '2024-07-01';

        $sale = Sale::create([
            'event_id' => $event->id,
            'name' => 'Cancelled Sale Tester',
            'email' => 'cancelled@example.com',
            'secret' => Str::random(32),
            'event_date' => $eventDate,
            'subdomain' => $creatorRole->subdomain,
        ]);

        $sale->saleTickets()->create([
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'seats' => json_encode(array_fill(0, 2, null)),
        ]);

        $sale->status = 'cancelled';
        $sale->save();

        $ticket->refresh();
        $sold = json_decode($ticket->sold, true);
        $this->assertSame(0, $sold[$eventDate]);

        $response = $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), [
                'action' => 'delete',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $sale->refresh();
        $ticket->refresh();

        $this->assertTrue($sale->is_deleted);

        $updatedSold = json_decode($ticket->sold, true);
        $this->assertSame(0, $updatedSold[$eventDate]);
    }
}
