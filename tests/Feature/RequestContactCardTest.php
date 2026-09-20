<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Requests tab card for a booking-form request (issue #124).
 *
 * A booking-form request has no submitting schedule: EventController::bookingRequest() re-attributes
 * the row to the schedule's own owner, so the card used to headline the stub venue the guest typed
 * (a link to a page they invented) on a talent, or nothing at all on a venue, and never said who
 * asked. It also printed the VENUE's description under "Description", which for these rows is
 * structurally empty, so the guest's own message was invisible too.
 *
 * The header keys off contact_name rather than is_guest_submission so a signed-in submitter gets it
 * as well, and a request created before those columns existed falls back to the original markup.
 */
class RequestContactCardTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function requestsTab(Role $role)
    {
        return $this->actingAs($role->user)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'requests']))
            ->assertOk();
    }

    /**
     * Pending means is_accepted NULL on the pivot, and the pivot has to be set after the fact:
     * CreatesScheduleData::createEvent() reads `$attrs['is_accepted'] ?? true`, so an explicit null
     * is indistinguishable from an absent key and comes back accepted.
     */
    private function pendingRequest(Role $role, array $attrs = [])
    {
        $event = $this->createEvent($role, array_merge([
            'name' => 'Late Night Set',
            'is_guest_submission' => true,
        ], $attrs));

        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => null]);

        return $event->fresh();
    }

    public function test_the_card_shows_who_asked_and_how_to_reach_them(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');
        $this->pendingRequest($role, [
            'contact_name' => 'Sam Guest',
            'contact_email' => 'sam.guest@gmail.com',
            'contact_phone' => '+49 170 1234567',
            'description' => 'We would love to book you for our summer party.',
        ]);

        $this->requestsTab($role)
            ->assertSee('Sam Guest')
            ->assertSee('data-request-contact', false)
            ->assertSee('mailto:sam.guest@gmail.com', false)
            ->assertSee('tel:+49 170 1234567', false)
            ->assertSee('We would love to book you for our summer party.')
            // The event name stays, as the sub-line.
            ->assertSee('Late Night Set');
    }

    public function test_the_contact_panel_is_absent_when_only_a_name_was_captured(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');
        $this->pendingRequest($role, ['contact_name' => 'Sam Guest']);

        // Scoped to the panel: the admin shell carries mailto: links of its own (support, footer).
        $this->requestsTab($role)
            ->assertSee('Sam Guest')
            ->assertDontSee('data-request-contact', false);
    }

    /**
     * A request that predates the columns has all three null and must render exactly as before,
     * rather than a card with a blank headline.
     */
    public function test_a_request_from_before_the_columns_existed_keeps_the_old_header(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $venue = $this->createRole($owner, 'venue', ['name' => 'The Cellar']);

        $event = $this->pendingRequest($role, ['contact_name' => null, 'contact_email' => null]);
        $event->roles()->attach($venue->id, ['is_accepted' => true, 'created_at' => now()]);
        $event = $event->fresh();

        $this->requestsTab($role)->assertSee('The Cellar');
    }

    /**
     * The schedule owner's own surfaces may show these addresses; nothing a visitor can reach may.
     */
    public function test_the_contact_details_never_reach_the_guest_page(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');
        $this->createEvent($role, [
            'name' => 'Accepted Set',
            'is_accepted' => true,
            'contact_name' => 'Sam Guest',
            'contact_email' => 'sam.guest@gmail.com',
            'contact_phone' => '+49 170 1234567',
        ]);

        $this->get($role->getGuestUrl())
            ->assertOk()
            ->assertDontSee('sam.guest@gmail.com')
            ->assertDontSee('+49 170 1234567');
    }
}
