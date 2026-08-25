<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventDeclined;
use App\Models\Event;
use App\Services\AppointmentService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * events.user_id is the event's CREATOR, stamped once at creation - not a record of who asked the
 * declining schedule for a listing. These pin which of the two the decline notice actually follows.
 */
class EventDeclineNotificationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The queued mails, unwrapped past SendQueuedEmail's protected properties. */
    private function queuedMail(): Collection
    {
        return collect(Queue::pushedJobs()[SendQueuedEmail::class] ?? [])
            ->map(fn ($pushed) => (function () {
                return ['mailable' => $this->mailable, 'recipient' => $this->recipient];
            })->call($pushed['job']));
    }

    private function sentTo(string $mailableClass): Collection
    {
        return $this->queuedMail()
            ->filter(fn ($m) => $m['mailable'] instanceof $mailableClass)
            ->pluck('recipient')
            ->values();
    }

    /** An event created by $creator on their own schedule, listed on $curator. */
    private function listOnCurator($creator, $curator, array $pivot, array $eventAttrs = []): Event
    {
        $home = $this->createRole($creator, 'talent');
        $event = $this->createEvent($home, $eventAttrs);
        $event->roles()->attach($curator->id, $pivot);

        return $event->fresh();
    }

    private function decline($actor, $role, Event $event)
    {
        return $this->actingAs($actor)->post(route('event.decline', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
        ]));
    }

    public function test_declining_a_real_request_emails_the_submitter(): void
    {
        Queue::fake();

        $submitter = $this->createOwner();
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        $event = $this->listOnCurator($submitter, $curator, ['is_accepted' => null, 'is_auto_sourced' => false]);

        $this->decline($curatorOwner, $curator, $event);

        $this->assertSame([$submitter->email], $this->sentTo(EventDeclined::class)->all());
    }

    public function test_declining_an_auto_sourced_listing_does_not_email_the_creator(): void
    {
        Queue::fake();

        $creator = $this->createOwner();
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        // What CuratorSourceService::linkMissing() writes: already accepted, pulled in by the
        // curator rather than requested by anybody.
        $event = $this->listOnCurator($creator, $curator, ['is_accepted' => true, 'is_auto_sourced' => true]);

        $this->decline($curatorOwner, $curator, $event);

        $this->assertSame([], $this->sentTo(EventDeclined::class)->all());

        // The removal still has to happen - only the notification is wrong.
        $this->assertFalse((bool) $event->roles()->where('roles.id', $curator->id)->first()->pivot->is_accepted);
    }

    public function test_declining_a_guest_submission_does_not_email_the_stand_in_owner(): void
    {
        Queue::fake();

        $standIn = $this->createOwner();
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        $event = $this->listOnCurator(
            $standIn,
            $curator,
            ['is_accepted' => null, 'is_auto_sourced' => false],
            ['is_guest_submission' => true],
        );

        $this->decline($curatorOwner, $curator, $event);

        $this->assertSame([], $this->sentTo(EventDeclined::class)->all());
    }

    public function test_no_email_when_the_creator_is_on_the_declining_schedules_team(): void
    {
        Queue::fake();

        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        // The owner declining their own schedule's event sees the result in the UI.
        $event = $this->listOnCurator($curatorOwner, $curator, ['is_accepted' => null, 'is_auto_sourced' => false]);

        $this->decline($curatorOwner, $curator, $event)->assertRedirect();

        $this->assertSame([], $this->sentTo(EventDeclined::class)->all());

        // Anchor the absence: this rule predates the fix, so without proof the decline actually
        // landed, a 403 or a 404 would read exactly like working suppression.
        $this->assertFalse((bool) $event->roles()->where('roles.id', $curator->id)->first()->pivot->is_accepted);
    }

    /**
     * A booking's user_id is the schedule's own owner (AppointmentService), so declining it on that
     * schedule is already suppressed by the team check. Decline it from somewhere the owner is NOT a
     * member of and the generic "your event request" mail is what would go out - to a person who
     * booked nothing, about an appointment the guest already heard about via AppointmentDeclined.
     */
    public function test_an_appointment_booking_never_sends_the_generic_request_mail(): void
    {
        Queue::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]),
            'requires_approval' => true,
        ]);

        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = app(AppointmentService::class)->availableSlots($type, $from, 1);
        $slot = $slots['days'][array_key_first($slots['days'])][0]['utc'];

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York',
        ])->assertOk();

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();
        $this->assertSame($owner->id, $event->user_id, 'a booking carries the schedule owner, not the guest');

        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);
        $event->roles()->attach($curator->id, ['is_accepted' => null, 'is_auto_sourced' => false]);

        $this->decline($curatorOwner, $curator, $event->fresh());

        $this->assertSame([], $this->sentTo(EventDeclined::class)->all());
    }

    public function test_the_mail_renders_without_a_creator_role(): void
    {
        $creator = $this->createOwner();
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        $event = $this->listOnCurator($creator, $curator, ['is_accepted' => null, 'is_auto_sourced' => false]);
        $event->creator_role_id = null;
        $event->save();

        $mailable = new EventDeclined($event->fresh(), $curator);

        // Both of these used to dereference a null creatorRole and fatal inside SendQueuedEmail.
        $this->assertSame([], $mailable->headers()->text);
        $this->assertStringContainsString($event->name, $mailable->render());
    }

    // -- The write side: the flag has to actually get stamped ---------------------------------

    /** A signed-out visitor submitting to a schedule that does not require an account. */
    private function guestSubmit($role, string $name)
    {
        return $this->postJson(route('event.guest_import.store', ['subdomain' => $role->subdomain]), [
            'name' => $name,
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration' => 2,
        ]);
    }

    public function test_an_anonymous_submission_is_flagged_as_a_guest_submission(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'accept_requests' => true,
            'require_account' => false,
        ]);

        $this->guestSubmit($role, 'Anonymous Event')->assertOk();

        $event = Event::where('name', 'Anonymous Event')->firstOrFail();

        // user_id is the schedule owner standing in for a NOT NULL column, not a submitter.
        $this->assertSame($role->user_id, $event->user_id);
        $this->assertTrue($event->is_guest_submission);
    }

    public function test_a_signed_in_submission_is_not_flagged(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'accept_requests' => true,
            'require_account' => false,
        ]);

        $submitter = $this->createOwner();
        $this->actingAs($submitter);

        $this->guestSubmit($role, 'Signed In Event')->assertOk();

        $event = Event::where('name', 'Signed In Event')->firstOrFail();

        // Without this the anonymous test above could pass by flagging everything.
        $this->assertFalse($event->is_guest_submission);
    }

    /**
     * bookingRequest() hand-builds the Event instead of going through EventRepo::saveEvent(), so it
     * has to stamp the flag itself - and it then fans the row onto the schedule's default curators.
     */
    public function test_an_anonymous_booking_request_is_flagged_and_survives_a_curator_decline(): void
    {
        Queue::fake();

        $owner = $this->createOwner();
        // Talent schedules always use the booking form (Role::usesBookingForm).
        $role = $this->createRole($owner, 'talent', ['accept_requests' => true]);

        $this->post(route('event.booking_request.store', ['subdomain' => $role->subdomain]), [
            'event_name' => 'Booked By A Stranger',
            'date' => now()->addDays(4)->format('Y-m-d'),
            'start_time' => '19:00',
            'contact_name' => 'Stranger',
            'contact_email' => 'stranger@gmail.com',
        ]);

        $event = Event::where('name', 'Booked By A Stranger')->firstOrFail();
        $this->assertSame($owner->id, $event->user_id, 'the stand-in owner is stamped');
        $this->assertTrue($event->is_guest_submission);

        // The shape that leaked: fanned onto a curator, declined there, owner mailed about it.
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);
        $event->roles()->attach($curator->id, ['is_accepted' => null, 'is_auto_sourced' => false]);

        $this->decline($curatorOwner, $curator, $event->fresh())->assertRedirect();

        $this->assertSame([], $this->sentTo(EventDeclined::class)->all());
    }
}
