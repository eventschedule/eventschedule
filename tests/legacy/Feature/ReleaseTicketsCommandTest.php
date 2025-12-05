<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketTimeoutNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

class ReleaseTicketsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.backend' => null]);
    }

    public function test_it_expires_unpaid_sales_and_releases_ticket_inventory(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-01-01 10:00:00'));

        $user = User::factory()->create();

        $creatorRole = Role::withoutEvents(function () use ($user) {
            return Role::create([
                'user_id' => $user->id,
                'subdomain' => 'creator-role',
                'type' => 'talent',
                'name' => 'Creator',
                'email' => 'creator@example.com',
                'timezone' => 'UTC',
                'language_code' => 'en',
            ]);
        });

        $venueRole = Role::withoutEvents(function () {
            return Role::create([
                'subdomain' => 'venue-role',
                'type' => 'venue',
                'name' => 'Venue',
                'email' => 'venue@example.com',
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
                'name' => 'Sample Event',
                'slug' => 'sample-event',
                'starts_at' => Carbon::parse('2024-01-02 20:00:00'),
                'tickets_enabled' => true,
                'ticket_currency_code' => 'USD',
                'payment_method' => 'stripe',
                'expire_unpaid_tickets' => 2,
                'total_tickets_mode' => 'individual',
            ]);
        });

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'type' => 'General Admission',
            'quantity' => 50,
            'price' => 10,
        ]);

        $sale = Sale::create([
            'event_id' => $event->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'secret' => Str::random(32),
            'event_date' => '2024-01-02',
            'subdomain' => $creatorRole->subdomain,
        ]);

        $saleTicket = $sale->saleTickets()->create([
            'ticket_id' => $ticket->id,
            'quantity' => 3,
        ]);

        $saleTicket->entries()->createMany(collect(range(1, 3))->map(function ($seat) {
            return [
                'seat_number' => $seat,
                'secret' => Str::lower(Str::random(32)),
            ];
        })->all());

        $sale->payment_amount = 30;
        $sale->save();

        $ticket->refresh();
        $sold = json_decode($ticket->sold, true);
        $this->assertSame(3, $sold[$sale->event_date]);

        Carbon::setTestNow(Carbon::parse('2024-01-01 13:00:00'));

        Notification::fake();

        Artisan::call('app:release-tickets');

        Carbon::setTestNow(null);

        $sale->refresh();
        $ticket->refresh();

        $this->assertSame('expired', $sale->status);

        $updatedSold = json_decode($ticket->sold, true);
        $this->assertSame(0, $updatedSold[$sale->event_date]);

        Notification::assertSentOnDemand(
            TicketTimeoutNotification::class,
            function ($notification, $channels, $notifiable) use ($sale) {
                return in_array('mail', $channels, true)
                    && ($notifiable->routes['mail'] ?? null) === $sale->email;
            }
        );

        Notification::assertSentTo(
            $user,
            TicketTimeoutNotification::class,
            function (TicketTimeoutNotification $notification) use ($user, $event) {
                $mail = $notification->toMail($user);

                return str_contains($mail->subject, $event->name);
            }
        );
    }

    public function test_it_keeps_recent_unpaid_sales_active(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-02-10 09:00:00'));

        $user = User::factory()->create();

        $creatorRole = Role::withoutEvents(function () use ($user) {
            return Role::create([
                'user_id' => $user->id,
                'subdomain' => 'recent-creator',
                'type' => 'talent',
                'name' => 'Recent Creator',
                'email' => 'recent@example.com',
                'timezone' => 'UTC',
                'language_code' => 'en',
            ]);
        });

        $venueRole = Role::withoutEvents(function () {
            return Role::create([
                'subdomain' => 'recent-venue',
                'type' => 'venue',
                'name' => 'Recent Venue',
                'email' => 'recent-venue@example.com',
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
                'name' => 'Recent Event',
                'slug' => 'recent-event',
                'starts_at' => Carbon::parse('2024-02-11 20:00:00'),
                'tickets_enabled' => true,
                'ticket_currency_code' => 'USD',
                'payment_method' => 'stripe',
                'expire_unpaid_tickets' => 4,
                'total_tickets_mode' => 'individual',
            ]);
        });

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'type' => 'Balcony',
            'quantity' => 25,
            'price' => 15,
        ]);

        $sale = Sale::create([
            'event_id' => $event->id,
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'secret' => Str::random(32),
            'event_date' => '2024-02-11',
            'subdomain' => $creatorRole->subdomain,
        ]);

        $saleTicket = $sale->saleTickets()->create([
            'ticket_id' => $ticket->id,
            'quantity' => 2,
        ]);

        $saleTicket->entries()->createMany(collect(range(1, 2))->map(function ($seat) {
            return [
                'seat_number' => $seat,
                'secret' => Str::lower(Str::random(32)),
            ];
        })->all());

        Carbon::setTestNow(Carbon::parse('2024-02-10 12:00:00'));

        Artisan::call('app:release-tickets');

        Carbon::setTestNow(null);

        $sale->refresh();
        $ticket->refresh();

        $this->assertSame('unpaid', $sale->status);

        $sold = json_decode($ticket->sold, true);
        $this->assertSame(2, $sold[$sale->event_date]);
    }
}
