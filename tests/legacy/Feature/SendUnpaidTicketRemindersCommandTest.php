<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketPaymentReminderNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class SendUnpaidTicketRemindersCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.backend' => null]);
    }

    public function test_it_sends_reminders_for_unpaid_sales(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-03-01 12:00:00'));

        $user = User::factory()->create();

        $creatorRole = Role::withoutEvents(function () use ($user) {
            return Role::create([
                'user_id' => $user->id,
                'subdomain' => 'reminder-creator',
                'type' => 'talent',
                'name' => 'Creator',
                'email' => 'creator@example.com',
                'timezone' => 'UTC',
                'language_code' => 'en',
            ]);
        });

        $venueRole = Role::withoutEvents(function () {
            return Role::create([
                'subdomain' => 'reminder-venue',
                'type' => 'venue',
                'name' => 'Reminder Venue',
                'email' => 'venue-reminder@example.com',
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
                'name' => 'Reminder Event',
                'slug' => 'reminder-event',
                'starts_at' => Carbon::parse('2024-03-05 19:00:00'),
                'tickets_enabled' => true,
                'ticket_currency_code' => 'USD',
                'payment_method' => 'stripe',
                'expire_unpaid_tickets' => 0,
                'remind_unpaid_tickets_every' => 6,
                'total_tickets_mode' => 'individual',
            ]);
        });

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'type' => 'General',
            'quantity' => 10,
            'price' => 25,
        ]);

        $sale = Sale::create([
            'event_id' => $event->id,
            'name' => 'Reminder Buyer',
            'email' => 'buyer@example.com',
            'secret' => Str::random(32),
            'event_date' => '2024-03-05',
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

        $sale->payment_amount = 50;
        $sale->created_at = Carbon::now()->subHours(6);
        $sale->save();

        Notification::fake();

        Artisan::call('app:remind-unpaid-tickets');

        $sale->refresh();

        Notification::assertSentOnDemand(
            TicketPaymentReminderNotification::class,
            function ($notification, $channels, $notifiable) use ($sale) {
                return in_array('mail', $channels, true)
                    && ($notifiable->routes['mail'] ?? null) === $sale->email;
            }
        );

        $this->assertNotNull($sale->last_reminder_sent_at);
        $this->assertTrue($sale->last_reminder_sent_at->equalTo(Carbon::now()));

        Notification::fake();

        Carbon::setTestNow(Carbon::parse('2024-03-01 16:00:00'));

        Artisan::call('app:remind-unpaid-tickets');

        Notification::assertNothingSent();

        Notification::fake();

        Carbon::setTestNow(Carbon::parse('2024-03-01 18:05:00'));

        Artisan::call('app:remind-unpaid-tickets');

        Notification::assertSentOnDemand(
            TicketPaymentReminderNotification::class,
            function ($notification, $channels, $notifiable) use ($sale) {
                return in_array('mail', $channels, true)
                    && ($notifiable->routes['mail'] ?? null) === $sale->email;
            }
        );

        $sale->refresh();

        $this->assertTrue($sale->last_reminder_sent_at->equalTo(Carbon::now()));

        Carbon::setTestNow(null);
    }

    public function test_reminder_email_includes_expiry_notice_when_event_expires_unpaid_sales(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-06-10 10:00:00'));

        $user = User::factory()->create();

        $creatorRole = Role::withoutEvents(function () use ($user) {
            return Role::create([
                'user_id' => $user->id,
                'subdomain' => 'expiry-reminder-creator',
                'type' => 'talent',
                'name' => 'Expiry Creator',
                'email' => 'expiry-creator@example.com',
                'timezone' => 'UTC',
                'language_code' => 'en',
            ]);
        });

        $venueRole = Role::withoutEvents(function () {
            return Role::create([
                'subdomain' => 'expiry-reminder-venue',
                'type' => 'venue',
                'name' => 'Expiry Venue',
                'email' => 'expiry-venue@example.com',
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
                'name' => 'Expiry Reminder Event',
                'slug' => 'expiry-reminder-event',
                'starts_at' => Carbon::parse('2024-06-15 19:00:00'),
                'tickets_enabled' => true,
                'ticket_currency_code' => 'USD',
                'payment_method' => 'cash',
                'expire_unpaid_tickets' => 24,
                'remind_unpaid_tickets_every' => 12,
                'total_tickets_mode' => 'individual',
            ]);
        });

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'type' => 'General Admission',
            'quantity' => 25,
            'price' => 75,
        ]);

        $sale = Sale::create([
            'event_id' => $event->id,
            'name' => 'Expiry Reminder Buyer',
            'email' => 'expiry-buyer@example.com',
            'secret' => Str::random(32),
            'event_date' => '2024-06-15',
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

        $sale->payment_amount = 225;
        $sale->created_at = Carbon::now()->subHours(12);
        $sale->save();

        $notification = new TicketPaymentReminderNotification($sale);

        $mailMessage = $notification->toMail((object) []);

        $body = $mailMessage->viewData['body'] ?? '';

        $this->assertStringContainsString('Payment must be completed within 24 hours', $body);

        Carbon::setTestNow(null);
    }

    public function test_it_does_not_send_reminders_when_disabled(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-04-10 09:00:00'));

        $user = User::factory()->create();

        $role = Role::withoutEvents(function () use ($user) {
            return Role::create([
                'user_id' => $user->id,
                'subdomain' => 'no-reminder-role',
                'type' => 'talent',
                'name' => 'No Reminder Role',
                'email' => 'no-reminder@example.com',
                'timezone' => 'UTC',
                'language_code' => 'en',
            ]);
        });

        $event = Event::withoutEvents(function () use ($user, $role) {
            return Event::create([
                'user_id' => $user->id,
                'creator_role_id' => $role->id,
                'role_id' => $role->id,
                'name' => 'No Reminder Event',
                'slug' => 'no-reminder-event',
                'starts_at' => Carbon::parse('2024-04-12 19:00:00'),
                'tickets_enabled' => true,
                'ticket_currency_code' => 'USD',
                'payment_method' => 'cash',
                'expire_unpaid_tickets' => 0,
                'remind_unpaid_tickets_every' => 0,
                'total_tickets_mode' => 'individual',
            ]);
        });

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'type' => 'Reserved',
            'quantity' => 5,
            'price' => 40,
        ]);

        $sale = Sale::create([
            'event_id' => $event->id,
            'name' => 'Waiting Buyer',
            'email' => 'waiting@example.com',
            'secret' => Str::random(32),
            'event_date' => '2024-04-12',
            'subdomain' => $role->subdomain,
        ]);

        $sale->saleTickets()->create([
            'ticket_id' => $ticket->id,
            'quantity' => 1,
        ]);

        $sale->created_at = Carbon::now()->subHours(12);
        $sale->save();

        Notification::fake();

        Artisan::call('app:remind-unpaid-tickets');

        Notification::assertNothingSent();

        $sale->refresh();

        $this->assertNull($sale->last_reminder_sent_at);

        Carbon::setTestNow(null);
    }
}
