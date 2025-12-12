<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\SaleTicket;
use App\Notifications\DeletedEventNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class EventDeletionNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_deletion_notifies_talent_roles()
    {
        Notification::fake();

        $user = User::factory()->create();
        $venue = Role::factory()->create(['type' => 'venue', 'user_id' => $user->id]);
        $talent = Role::factory()->create(['type' => 'talent']);
        $talentMember = User::factory()->create();
        $talent->members()->attach($talentMember->id);

        $event = Event::factory()->create([
            'user_id' => $user->id,
        ]);

        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $this->actingAs($user)
            ->delete(route('event.delete', [
                'subdomain' => $venue->subdomain,
                'hash' => \App\Utils\UrlUtils::encodeId($event->id)
            ]));

        Notification::assertSentTo($talentMember, DeletedEventNotification::class);
    }

    public function test_event_deletion_notifies_organizers()
    {
        Notification::fake();

        $user = User::factory()->create();
        $venue = Role::factory()->create(['type' => 'venue', 'user_id' => $user->id]);
        $venueMember = User::factory()->create();
        $venue->members()->attach($venueMember->id);

        $event = Event::factory()->create([
            'user_id' => $user->id,
        ]);

        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->actingAs($user)
            ->delete(route('event.delete', [
                'subdomain' => $venue->subdomain,
                'hash' => \App\Utils\UrlUtils::encodeId($event->id)
            ]));

        Notification::assertSentTo($venueMember, DeletedEventNotification::class);
    }

    public function test_event_deletion_notifies_ticket_purchasers()
    {
        Notification::fake();

        $user = User::factory()->create();
        $venue = Role::factory()->create(['type' => 'venue', 'user_id' => $user->id]);

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'ticket_currency_code' => 'USD',
        ]);

        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $ticket = Ticket::factory()->create(['event_id' => $event->id, 'price' => 1000]);

        $sale = Sale::factory()->create([
            'event_id' => $event->id,
            'email' => 'purchaser@example.com',
            'status' => 'paid',
        ]);

        SaleTicket::create([
            'sale_id' => $sale->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->delete(route('event.delete', [
                'subdomain' => $venue->subdomain,
                'hash' => \App\Utils\UrlUtils::encodeId($event->id)
            ]));

        Notification::assertSentTo(
            new \Illuminate\Notifications\AnonymousNotifiable,
            DeletedEventNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === ['purchaser@example.com'];
            }
        );
    }

    public function test_api_event_deletion_sends_notifications()
    {
        Notification::fake();

        $user = User::factory()->create();
        $venue = Role::factory()->create(['type' => 'venue', 'user_id' => $user->id]);
        $talent = Role::factory()->create(['type' => 'talent']);
        $talentMember = User::factory()->create();
        $talent->members()->attach($talentMember->id);

        $event = Event::factory()->create([
            'user_id' => $user->id,
        ]);

        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $token = $user->createToken('test', ['resources.manage'])->plainTextToken;

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/v2/events/' . \App\Utils\UrlUtils::encodeId($event->id));

        Notification::assertSentTo($talentMember, DeletedEventNotification::class);
    }

    public function test_role_deletion_sends_event_deletion_notifications()
    {
        Notification::fake();

        $user = User::factory()->create();
        $talent = Role::factory()->create(['type' => 'talent', 'user_id' => $user->id]);
        $venue = Role::factory()->create(['type' => 'venue']);
        $venueMember = User::factory()->create();
        $venue->members()->attach($venueMember->id);

        $event = Event::factory()->create([
            'user_id' => $user->id,
        ]);

        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        // Delete the talent role (which should delete orphaned events)
        $this->actingAs($user)
            ->delete(route('role.delete', ['subdomain' => $talent->subdomain]));

        Notification::assertSentTo($venueMember, DeletedEventNotification::class);
    }
}
