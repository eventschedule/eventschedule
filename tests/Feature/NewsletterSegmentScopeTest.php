<?php

namespace Tests\Feature;

use App\Models\NewsletterSegment;
use App\Models\TicketWaitlist;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Newsletter segments used to resolve their audience through sales.subdomain /
 * ticket_waitlists.subdomain - a booking-time snapshot of the storefront the buyer checked out
 * through, which RoleController::update() never rewrites on rename.
 *
 * That had two halves. A renamed schedule silently lost its own audience, and - the part that
 * matters - whoever next claimed the freed subdomain inherited it and could mail those people.
 * RoleController::appointmentsTabData() documents the identical hazard and routes around it.
 *
 * They now resolve through the event_role pivot, which is stable across a rename and is what the
 * docs actually promise: "everyone who has bought a ticket ... for one of your events".
 */
class NewsletterSegmentScopeTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function segment($role, string $type, array $criteria = []): NewsletterSegment
    {
        return NewsletterSegment::create([
            'role_id' => $role->id,
            'name' => ucfirst($type),
            'type' => $type,
            'filter_criteria' => $criteria,
        ]);
    }

    private function emails(NewsletterSegment $segment): array
    {
        return $segment->resolveRecipients()->pluck('email')->all();
    }

    /**
     * `is_accepted = true` is the right rule for somebody ELSE's event and the wrong one for yours.
     *
     * The column is nullable, so `= true` drops NULL as well as false. On your own schedule that
     * silently deleted an audience the previous subdomain rule reached: AppointmentService attaches
     * a booking with creator_role_id = this schedule and is_accepted null while it awaits approval
     * (null again on reschedule), false once cancelled - and it sets sales.subdomain to this
     * schedule, so those buyers WERE in Ticket Buyers before. The cancelled ones would never have
     * come back.
     *
     * Event::scopeManagedThrough() carries the same own-schedule arm for the same reason.
     */
    public function test_your_own_schedules_events_are_mailable_whatever_the_pivot_says(): void
    {
        $role = $this->createRole($this->createOwner());

        // A booking still awaiting approval, and one that was cancelled - the two states
        // AppointmentService leaves behind on a schedule's OWN event.
        foreach ([['pending@gmail.com', null], ['cancelled@gmail.com', false]] as [$email, $accepted]) {
            $event = $this->createEvent($role, [
                'creator_role_id' => $role->id,
                'is_accepted' => $accepted,
            ]);
            $this->createSale($event, $role, [
                'name' => 'Booker', 'email' => $email, 'status' => 'paid',
            ], $this->createTicket($event, ['price' => 20]));
        }

        $emails = $this->emails($this->segment($role, 'ticket_buyers'));

        $this->assertContains('pending@gmail.com', $emails, 'a booking awaiting approval is still your customer');
        $this->assertContains('cancelled@gmail.com', $emails, 'a cancelled booking is still your customer');
    }

    /**
     * The other half of the same rule, asserted here so a fix to the one above cannot quietly
     * reopen it: a schedule that DECLINED somebody else's event still may not mail its buyers.
     */
    public function test_a_declined_event_from_another_schedule_stays_unmailable(): void
    {
        $mine = $this->createRole($this->createOwner());
        $theirs = $this->createRole($this->createOwner());

        $event = $this->createEvent($theirs, ['creator_role_id' => $theirs->id]);
        $this->createSale($event, $theirs, [
            'name' => 'Someone', 'email' => 'notmine@gmail.com', 'status' => 'paid',
        ], $this->createTicket($event, ['price' => 20]));

        // Cross-listed on my schedule, and I turned it down. A decline does not detach the row.
        $event->roles()->attach($mine->id, ['is_accepted' => false]);

        $this->assertNotContains('notmine@gmail.com', $this->emails($this->segment($mine, 'ticket_buyers')));
    }

    public function test_a_freed_subdomain_does_not_inherit_the_previous_schedules_buyers(): void
    {
        $original = $this->createRole($this->createOwner());
        $freed = $original->subdomain;

        $event = $this->createEvent($original, ['creator_role_id' => $original->id]);
        $ticket = $this->createTicket($event, ['price' => 20]);
        $this->createSale($event, $original, [
            'name' => 'Arthur Dent', 'email' => 'arthur@gmail.com', 'status' => 'paid',
        ], $ticket);

        // The schedule renames, freeing its old subdomain...
        $original->subdomain = 'renamed'.strtolower(\Illuminate\Support\Str::random(8));
        $original->save();

        // ...and somebody else claims it.
        $squatter = $this->createRole($this->createOwner());
        $squatter->subdomain = $freed;
        $squatter->save();

        // Positive control first: the rename must not have cost the original its own audience.
        $this->assertContains('arthur@gmail.com', $this->emails($this->segment($original->fresh(), 'ticket_buyers')),
            'a renamed schedule must keep its own ticket buyers');

        $this->assertNotContains('arthur@gmail.com', $this->emails($this->segment($squatter, 'ticket_buyers')),
            'claiming a freed subdomain must not inherit the previous schedule\'s buyers');
    }

    public function test_the_same_holds_for_the_waitlist_segment(): void
    {
        $original = $this->createRole($this->createOwner());
        $freed = $original->subdomain;

        $event = $this->createEvent($original, ['creator_role_id' => $original->id]);
        TicketWaitlist::create([
            'event_id' => $event->id,
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Ford Prefect',
            'email' => 'ford@gmail.com',
            'subdomain' => $freed,
            'status' => 'waiting',
        ]);

        $original->subdomain = 'renamed'.strtolower(\Illuminate\Support\Str::random(8));
        $original->save();

        $squatter = $this->createRole($this->createOwner());
        $squatter->subdomain = $freed;
        $squatter->save();

        $this->assertContains('ford@gmail.com', $this->emails($this->segment($original->fresh(), 'waitlist')));
        $this->assertNotContains('ford@gmail.com', $this->emails($this->segment($squatter, 'waitlist')));
    }

    public function test_a_schedule_that_declined_the_event_cannot_mail_its_buyers(): void
    {
        $venue = $this->createRole($this->createOwner());
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $ticket = $this->createTicket($event, ['price' => 20]);
        $this->createSale($event, $venue, [
            'name' => 'Trillian Astra', 'email' => 'trillian@gmail.com', 'status' => 'paid',
        ], $ticket);

        $declined = $this->createRole($this->createOwner(), 'venue', ['name' => 'Declined Venue']);
        $event->roles()->attach($declined->id, ['is_accepted' => false]);

        $accepted = $this->createRole($this->createOwner(), 'venue', ['name' => 'Accepted Venue']);
        $event->roles()->attach($accepted->id, ['is_accepted' => true]);

        $this->assertContains('trillian@gmail.com', $this->emails($this->segment($accepted, 'ticket_buyers')));
        $this->assertNotContains('trillian@gmail.com', $this->emails($this->segment($declined, 'ticket_buyers')),
            'a decline leaves the pivot row in place, so it has to be filtered out explicitly');
    }

    public function test_the_sub_schedule_segment_is_pinned_to_this_schedules_own_pivot_row(): void
    {
        $venue = $this->createRole($this->createOwner());
        $group = $this->createGroup($venue);

        // group_id lives on the event_role pivot, not on events - set it there.
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $event->roles()->updateExistingPivot($venue->id, ['group_id' => $group->id]);
        $ticket = $this->createTicket($event, ['price' => 20]);
        $this->createSale($event, $venue, [
            'name' => 'Zaphod Beeblebrox', 'email' => 'zaphod@gmail.com', 'status' => 'paid',
        ], $ticket);

        $this->assertContains('zaphod@gmail.com',
            $this->emails($this->segment($venue, 'group', ['group_id' => $group->id])));

        // Another schedule attached to the same event must not reach that sub-schedule's buyers by
        // quoting its group_id - the filter is pinned to the caller's own pivot row.
        $other = $this->createRole($this->createOwner(), 'venue', ['name' => 'Other Venue']);
        $event->roles()->attach($other->id, ['is_accepted' => true]);

        $this->assertNotContains('zaphod@gmail.com',
            $this->emails($this->segment($other, 'group', ['group_id' => $group->id])));
    }
}
