<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The first time anyone opens the Tickets panel.
 *
 * 438 schedules publish an event and 144 have ever created a ticket type. What the panel
 * actually offered on a first visit was two unlabelled number boxes - the name field was hidden
 * until a SECOND row existed - behind a radio that defaults to sending buyers somewhere else.
 */
class EventTicketFirstRunTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function editHtml($user, $role, Event $event): string
    {
        return $this->actingAs($user)->get(route('event.edit', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
        ]))->assertOk()->getContent();
    }

    private function createHtml($user, $role): string
    {
        return $this->actingAs($user)->get(route('event.create', [
            'subdomain' => $role->subdomain,
        ]))->assertOk()->getContent();
    }

    /** The panel says what the row is, instead of opening on two bare number inputs. */
    public function test_the_first_ticket_row_is_introduced(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $html = $this->editHtml($owner, $role, $event);

        $this->assertStringContainsString(__('messages.your_first_ticket_type'), $html);
        $this->assertStringContainsString(__('messages.your_first_ticket_type_help'), $html);
    }

    /**
     * The name field renders for the FIRST ticket, not only once a second exists.
     *
     * It is the one field that tells a buyer what they are choosing, and it was `v-if
     * ="tickets.length > 1"`, i.e. invisible on every first ticket anyone ever made.
     */
    public function test_the_ticket_name_field_is_visible_on_the_first_row(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $html = $this->editHtml($owner, $role, $event);

        $this->assertStringContainsString('tickets[${index}][type]', $html);
        $this->assertStringContainsString(__('messages.ticket_type_placeholder'), $html);

        // The markup is ALWAYS server-rendered - Vue decides visibility at runtime - so
        // asserting the field exists proves nothing. Assert the wrapper's directive instead:
        // find the <div ...> that opens the block containing the type input.
        $at = strpos($html, 'tickets[${index}][type]');
        $this->assertNotFalse($at);
        $before = substr($html, 0, $at);
        $openedAt = strrpos($before, '<div');
        $this->assertNotFalse($openedAt);
        $openTag = substr($html, $openedAt, strpos($html, '>', $openedAt) - $openedAt + 1);

        $this->assertStringNotContainsString(
            'v-if', $openTag,
            'the name field must render on the first row, not only once a second one exists'
        );
    }

    /** Still only REQUIRED with more than one row, where an unnamed ticket is ambiguous. */
    public function test_the_name_is_required_only_once_there_are_several_rows(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $html = $this->editHtml($owner, $role, $event);

        $this->assertStringContainsString('event.tickets_enabled && tickets.length > 1', $html);
    }

    /** A brand-new schedule is unchanged: nothing turns selling on for someone who never has. */
    public function test_a_new_schedule_still_starts_on_external(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $html = $this->createHtml($owner, $role);

        $this->assertStringContainsString('ticketMode: "external"', $html);
    }

    /**
     * A schedule that sold last time starts on tickets this time.
     *
     * The radio defaulted to "external" on every event, so a venue selling every week had to
     * flip it every week, and nothing on a first visit suggested selling here was possible.
     */
    public function test_a_schedule_that_sells_starts_the_next_event_on_tickets(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['tickets_enabled' => true]);

        $html = $this->createHtml($owner, $role);

        $this->assertStringContainsString('ticketMode: "tickets"', $html);
    }

    /** And the same for registration, so the carry-over is the mode and not just ticketing. */
    public function test_a_schedule_that_takes_registrations_carries_that_over(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['rsvp_enabled' => true]);

        $html = $this->createHtml($owner, $role);

        $this->assertStringContainsString('ticketMode: "rsvp"', $html);
    }

    /** It reads the SCHEDULE's own history, not any event the user happens to own elsewhere. */
    public function test_another_schedules_history_does_not_leak_in(): void
    {
        $owner = $this->createOwner();
        $seller = $this->createRole($owner, 'venue');
        $this->createEvent($seller, ['tickets_enabled' => true]);

        $other = $this->createRole($owner, 'talent');

        $html = $this->createHtml($owner, $other);

        $this->assertStringContainsString('ticketMode: "external"', $html);
    }
}
