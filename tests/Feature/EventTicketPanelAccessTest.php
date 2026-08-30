<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Who may see the Tickets panel on the event editor.
 *
 * It used to be gated on `$event->user_id == $user->id`, which is creator identity rather than
 * permission: an Enterprise team admin saw no Tickets tab at all on their own schedule's
 * events. The replacement is canViewEventData(), NOT canEditEvent() - the latter has no curator
 * exception, so it would hand a curator's staff the ticket and payment setup for an event
 * another schedule created. Same predicate TicketController::sales() and BoxOfficeController
 * already use for buyer data.
 */
class EventTicketPanelAccessTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function editUrl($role, Event $event): string
    {
        return route('event.edit', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
        ]);
    }

    public function test_the_owner_sees_the_tickets_panel(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $this->actingAs($owner)->get($this->editUrl($role, $event))
            ->assertOk()
            ->assertSee('id="section-tickets"', false);
    }

    /**
     * The case the old gate broke. The owner created the event, so `user_id == $user->id` was
     * false for the admin and the whole panel disappeared for them.
     */
    public function test_a_team_admin_sees_the_tickets_panel_on_the_owners_event(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $admin = $this->createOwner();
        $role->users()->attach($admin->id, ['level' => 'admin']);

        $this->actingAs($admin)->get($this->editUrl($role, $event))
            ->assertOk()
            ->assertSee('id="section-tickets"', false);
    }

    /** A viewer runs the door; they do not configure prices. */
    public function test_a_viewer_does_not_see_the_tickets_panel(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $viewer = $this->createOwner();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);

        $response = $this->actingAs($viewer)->get($this->editUrl($role, $event));

        if ($response->getStatusCode() === 200) {
            // Not the bare id: the Help button's anchorMap lists every section unconditionally,
            // so a substring check passes for a page that never renders the panel.
            $response->assertDontSee('id="section-tickets"', false);
            $response->assertDontSee('data-section="section-tickets"', false);
        } else {
            $response->assertRedirect();
        }
    }

    /**
     * The reason canEditEvent() is the wrong predicate.
     *
     * A curator that merely LISTS someone else's event does not own the creator's ticket
     * prices, payment setup or the sales behind them. canEditEvent() has no curator exception
     * and would show all of it.
     */
    public function test_a_curator_listing_someone_elses_event_does_not_see_the_tickets_panel(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue);

        $curatorUser = $this->createOwner();
        $curator = $this->createRole($curatorUser, 'curator');
        // The curator lists the event but did not create it.
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $response = $this->actingAs($curatorUser)->get($this->editUrl($curator, $event->fresh()));

        if ($response->getStatusCode() === 200) {
            // Not the bare id: the Help button's anchorMap lists every section unconditionally,
            // so a substring check passes for a page that never renders the panel.
            $response->assertDontSee('id="section-tickets"', false);
            $response->assertDontSee('data-section="section-tickets"', false);
        } else {
            $response->assertRedirect();
        }
    }

    /** A curator DOES own what it created itself. */
    public function test_a_curator_sees_the_panel_on_its_own_event(): void
    {
        $curatorUser = $this->createOwner();
        $curator = $this->createRole($curatorUser, 'curator');
        $event = $this->createEvent($curator);

        $this->actingAs($curatorUser)->get($this->editUrl($curator, $event))
            ->assertOk()
            ->assertSee('id="section-tickets"', false);
    }

    /** Discoverability: Tickets is second in the nav, not ninth of eleven. */
    public function test_tickets_is_near_the_top_of_the_editor_nav(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $html = $this->actingAs($owner)->get($this->editUrl($role, $event))->getContent();

        preg_match_all('/class="section-nav-link" data-section="([a-z-]+)"/', $html, $m);

        $this->assertNotEmpty($m[1]);
        $this->assertSame('section-details', $m[1][0]);
        $this->assertSame('section-tickets', $m[1][1], 'Tickets must sit directly under Details');
    }
}
