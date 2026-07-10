<?php

namespace Tests\Feature\Characterization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Deep viewGuest() variants ahead of the P14 decomposition
 * (REFACTOR_PLAN.md): password-protected events, ticket embeds, and a
 * curator schedule listing other schedules' events.
 *
 * NOT covered here: the custom-domain host variant - the hosted domain-group
 * routes never load under the phpunit env (landmine L13), so that path is
 * verified by the P1 route snapshots + manual QA instead.
 */
class ViewGuestDeepCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_password_protected_event_gates_until_unlocked(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue'); // enterprise by default
        $event = $this->createEvent($role, [
            'name' => 'Secret Show',
            'is_private' => true,
            'event_password' => 'letmein',
        ]);

        // Guests hit the password gate: page loads but withholds the guarded
        // description content (the event name still renders on the gate page).
        $this->get($this->guestEventUrl($role, $event))->assertOk();

        // Wrong password -> redirect back to the event with a password_error
        // flash (NOT a validation-errors bag).
        $this->post('/'.$role->subdomain.'/event-password', [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'password' => 'wrong',
        ])->assertRedirect($event->getGuestUrl($role->subdomain))
            ->assertSessionHas('password_error', true);

        // Correct password unlocks (session-scoped) and the page renders.
        $this->post('/'.$role->subdomain.'/event-password', [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'password' => 'letmein',
        ])->assertRedirect();

        $this->get($this->guestEventUrl($role, $event))
            ->assertOk()
            ->assertSee('Secret Show');
    }

    public function test_private_event_without_password_is_hidden_from_guests(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'name' => 'Members Only',
            'is_private' => true,
        ]);

        // For guests the event resolves to null -> redirect to schedule home.
        $this->get($this->guestEventUrl($role, $event))
            ->assertRedirect($role->getGuestUrl());

        // Members still see it.
        $this->actingAs($owner)
            ->get($this->guestEventUrl($role, $event))
            ->assertOk()
            ->assertSee('Members Only');
    }

    public function test_ticket_embed_variant_renders_embed_view(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'name' => 'Embed Event']);
        $this->createTicket($event);

        $this->get($this->guestEventUrl($role, $event).'?embed=1&tickets=true')
            ->assertOk()
            ->assertSee('Embed Event');
    }

    public function test_curator_schedule_lists_accepted_events_from_other_schedules(): void
    {
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue');
        $event = $this->createEvent($venue, ['name' => 'Curated Concert']);

        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner, ['name' => 'City Guide']);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        // The curator's guest page lists the other schedule's event...
        $this->get('/'.$curator->subdomain.'?month='.now()->addDays(7)->month.'&year='.now()->addDays(7)->year)
            ->assertOk();
        $this->get('/'.$curator->subdomain.'/api/calendar-events?month='.now()->addDays(7)->month.'&year='.now()->addDays(7)->year)
            ->assertOk()
            ->assertSee('Curated Concert');

        // ...but an unaccepted event stays off it (is_accepted gate).
        $pending = $this->createEvent($venue, ['name' => 'Pending Request']);
        $pending->roles()->attach($curator->id, ['is_accepted' => null]);

        $this->get('/'.$curator->subdomain.'/api/calendar-events?month='.now()->addDays(7)->month.'&year='.now()->addDays(7)->year)
            ->assertOk()
            ->assertDontSee('Pending Request');
    }
}
