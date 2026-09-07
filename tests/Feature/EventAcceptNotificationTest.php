<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventAccepted;
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
 * The accept half of EventDeclineNotificationTest. accept() used to carry its own recipient rule -
 * "has an email and is not on our team" - while decline() had been narrowed to the three shapes
 * where events.user_id is not a requester at all. Both now share requestDecisionRecipient(), and
 * these pin that they agree.
 *
 * Also pins the delivery bug the sibling was fixed for and this one was not: a null creator_role_id
 * made EventAccepted throw while rendering, inside the queued job, so the approval mailed nobody.
 */
class EventAcceptNotificationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The queued mails, unwrapped past SendQueuedEmail's protected properties. */
    private function queuedMail(): Collection
    {
        return collect(Queue::pushedJobs()[SendQueuedEmail::class] ?? [])
            ->map(fn ($pushed) => (function () {
                return ['mailable' => $this->mailable, 'recipient' => $this->recipient, 'locale' => $this->locale];
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

    private function accept($actor, $role, Event $event)
    {
        return $this->actingAs($actor)->post(route('event.accept', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
        ]));
    }

    private function acceptAll($actor, $role)
    {
        return $this->actingAs($actor)->post(route('event.accept_all', [
            'subdomain' => $role->subdomain,
        ]));
    }

    private function pivotFor(Event $event, $role)
    {
        return $event->roles()->where('roles.id', $role->id)->first()->pivot;
    }

    public function test_accepting_a_real_request_emails_the_submitter(): void
    {
        Queue::fake();

        $submitter = $this->createOwner();
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        $event = $this->listOnCurator($submitter, $curator, ['is_accepted' => null, 'is_auto_sourced' => false]);

        $this->accept($curatorOwner, $curator, $event);

        $this->assertSame([$submitter->email], $this->sentTo(EventAccepted::class)->all());
        $this->assertTrue((bool) $this->pivotFor($event, $curator)->is_accepted);
    }

    /** The locale is the recipient's, not the accepting admin's - the mail is translated. */
    public function test_the_mail_is_queued_in_the_submitters_language(): void
    {
        Queue::fake();

        $submitter = $this->createOwner();
        $submitter->update(['language_code' => 'fr']);
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        $event = $this->listOnCurator($submitter, $curator, ['is_accepted' => null, 'is_auto_sourced' => false]);

        $this->accept($curatorOwner, $curator, $event);

        $this->assertSame(
            'fr',
            $this->queuedMail()->firstWhere(fn ($m) => $m['mailable'] instanceof EventAccepted)['locale']
        );
    }

    public function test_accepting_an_auto_sourced_listing_does_not_email_the_creator(): void
    {
        Queue::fake();

        $creator = $this->createOwner();
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        // What CuratorSourceService::linkMissing() writes: pulled in by the curator, not requested.
        // Left pending here so accept() has something to flip - the flag is what suppresses the mail.
        $event = $this->listOnCurator($creator, $curator, ['is_accepted' => null, 'is_auto_sourced' => true]);

        $this->accept($curatorOwner, $curator, $event);

        $this->assertSame([], $this->sentTo(EventAccepted::class)->all());

        // The acceptance still has to happen - only the notification is wrong.
        $this->assertTrue((bool) $this->pivotFor($event, $curator)->is_accepted);
    }

    public function test_accepting_a_guest_submission_does_not_email_the_stand_in_owner(): void
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

        $this->accept($curatorOwner, $curator, $event);

        $this->assertSame([], $this->sentTo(EventAccepted::class)->all());
        $this->assertTrue((bool) $this->pivotFor($event, $curator)->is_accepted);
    }

    public function test_no_email_when_the_creator_is_on_the_accepting_schedules_team(): void
    {
        Queue::fake();

        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        // The owner accepting their own schedule's event sees the result in the UI.
        $event = $this->listOnCurator($curatorOwner, $curator, ['is_accepted' => null, 'is_auto_sourced' => false]);

        $this->accept($curatorOwner, $curator, $event)->assertRedirect();

        $this->assertSame([], $this->sentTo(EventAccepted::class)->all());

        // Anchor the absence: a 403 or a 404 would read exactly like working suppression.
        $this->assertTrue((bool) $this->pivotFor($event, $curator)->is_accepted);
    }

    /**
     * A booking's user_id is the schedule's own owner (AppointmentService), so accepting it on that
     * schedule is already suppressed by the team check. Accept it from somewhere the owner is NOT a
     * member of and the generic "your event request" mail is what would go out - to a person who
     * booked nothing, about an appointment the guest already heard about via AppointmentConfirmed.
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

        $this->accept($curatorOwner, $curator, $event->fresh());

        $this->assertSame([], $this->sentTo(EventAccepted::class)->all());

        // Anchor the absence. accept() returns early for a booking whose slot is already past,
        // BEFORE the mail code, so if availableSlots() ever drifts (a DST edge, a changed window)
        // this would pass without the suppression being exercised at all.
        $this->assertTrue((bool) $this->pivotFor($event->fresh(), $curator)->is_accepted);
    }

    /**
     * The delivery bug. events.creator_role_id is nullable and known to be wrong on real rows
     * (CheckData says so), and headers() runs during send inside SendQueuedEmail - where a
     * promoted "property on null" warning becomes an ErrorException, burning the job's 3 tries.
     *
     * Neither faking style would catch this: Queue::fake() stops the job ever running, and
     * MailFake records the mailable without rendering it. So drive the mailable directly.
     */
    public function test_the_mail_renders_without_a_creator_role(): void
    {
        $creator = $this->createOwner();
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        $event = $this->listOnCurator($creator, $curator, ['is_accepted' => null, 'is_auto_sourced' => false]);
        $event->creator_role_id = null;
        $event->save();

        $mailable = new EventAccepted($event->fresh(), $curator);

        $this->assertSame([], $mailable->headers()->text);
        $this->assertStringContainsString($event->name, $mailable->render());

        // Mailer::render() renders ONLY the html view (renderView($view ?: $plain)), while a real
        // send renders both (Mailer::addContent()). Without this line the text twin's guard is
        // unpinned: delete it and every send throws again while this test stays green. Driven off
        // content() so the view name and the data cannot drift from the mailable.
        $content = $mailable->content();
        $this->assertStringContainsString($event->name, view($content->text, $content->with)->render());
    }

    /** Accept All is a second copy of the decision; it must apply the same recipient rule. */
    public function test_accept_all_follows_the_same_recipient_rule(): void
    {
        Queue::fake();

        $submitter = $this->createOwner();
        $standIn = $this->createOwner();
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        $requested = $this->listOnCurator($submitter, $curator, ['is_accepted' => null, 'is_auto_sourced' => false]);
        $guest = $this->listOnCurator(
            $standIn,
            $curator,
            ['is_accepted' => null, 'is_auto_sourced' => false],
            ['is_guest_submission' => true],
        );

        $this->acceptAll($curatorOwner, $curator);

        $this->assertSame([$submitter->email], $this->sentTo(EventAccepted::class)->all());

        // Both were still accepted - only the stand-in's notification is suppressed.
        $this->assertTrue((bool) $this->pivotFor($requested, $curator)->is_accepted);
        $this->assertTrue((bool) $this->pivotFor($guest, $curator)->is_accepted);
    }
}
