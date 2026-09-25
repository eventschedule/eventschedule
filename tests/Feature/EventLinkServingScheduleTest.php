<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventCancelled;
use App\Mail\EventChanged;
use App\Models\Event;
use App\Models\Role;
use App\Services\EventChangeNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A link that names no schedule goes to one that shows the event.
 *
 * Mail, notifications, cards and ad destinations ask for "the" event URL, and that picked the
 * performer before the venue without asking whether the performer had accepted the event. A
 * venue's event listing a claimed act that had not answered yet therefore linked the act's page,
 * which refuses the event until the act accepts: the buyers' "this event changed" mail, its
 * calendar file and every other such link were dead. The canonical URL already went around it
 * (Event::canonicalTarget()); the links now use the same serving schedule.
 */
class EventLinkServingScheduleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array{0: Role, 1: Role, 2: Event} a venue's event, and a claimed act that has not answered */
    private function venueEventWithPendingAct(): array
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Wine Bar']);
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Cellar Trio']);
        $event = $this->createEvent($venue, ['name' => 'Trio Night', 'creator_role_id' => $venue->id]);
        $event->roles()->attach($act->id, ['is_accepted' => null]);

        return [$venue, $act, $event->fresh()];
    }

    /**
     * Whether $url is on $role's pages: its first path segment, as the tests route by path. Not a
     * substring test - a venue's URL for the event carries the performer's subdomain as its slug.
     */
    private function onSchedule(Role $role, string $url): bool
    {
        return explode('/', trim((string) parse_url($url, PHP_URL_PATH), '/'))[0] === $role->subdomain;
    }

    public function test_a_link_that_names_no_schedule_goes_where_the_event_is_shown(): void
    {
        [$venue, $act, $event] = $this->venueEventWithPendingAct();

        // The control: the act's page refuses the event until the act accepts.
        $this->get($event->getGuestUrl($act->subdomain))->assertNotFound();

        foreach ([
            'the event page' => $event->getGuestUrl(),
            'the calendar file' => $event->getAppleCalendarUrl(),
            'the photo gallery' => $event->getPhotoGalleryUrl(),
        ] as $what => $url) {
            $this->assertTrue($this->onSchedule($venue, $url), "{$what} links {$url}, not the venue");
            $this->get($url)->assertOk();
        }

        // And it now agrees with the canonical, which already went around the act.
        $this->assertSame($event->getCanonicalUrl(), $event->getUndatedGuestUrl());
    }

    public function test_the_change_and_cancellation_mails_link_the_venue(): void
    {
        Queue::fake();

        [$venue, , $event] = $this->venueEventWithPendingAct();
        $venue->email_settings = [
            'host' => 'smtp.test.dev', 'port' => 587, 'encryption' => 'tls',
            'username' => 'mailer', 'password' => 'secret',
            'from_address' => 'events@harbour.dev', 'from_name' => 'Harbour Wine Bar',
        ];
        $venue->save();
        $this->createSale($event, $venue, ['email' => 'buyer@gmail.com', 'status' => 'paid']);

        $this->assertSame(1, EventChangeNotifier::notifyChange($event, $venue->fresh(), ['starts_at' => 'x']));
        $this->assertSame(1, EventChangeNotifier::notifyCancellation($event, $venue->fresh()));

        $read = fn (object $object, string $property) => (new \ReflectionProperty($object, $property))->getValue($object);
        $mail = Queue::pushed(SendQueuedEmail::class)->map(fn ($job) => $read($job, 'mailable'))->values();

        $changed = $mail->first(fn ($m) => $m instanceof EventChanged);
        $cancelled = $mail->first(fn ($m) => $m instanceof EventCancelled);
        $this->assertNotNull($changed);
        $this->assertNotNull($cancelled);

        foreach ([
            'the change mail\'s event link' => $read($changed, 'eventUrl'),
            'the change mail\'s calendar link' => $read($changed, 'icalUrl'),
            'the cancellation mail\'s event link' => $read($cancelled, 'eventUrl'),
        ] as $what => $url) {
            $this->assertTrue($this->onSchedule($venue, $url), "{$what} is {$url}, not on the venue");
            $this->get($url)->assertOk();
        }
    }

    public function test_once_the_act_accepts_it_gets_the_link(): void
    {
        [, $act, $event] = $this->venueEventWithPendingAct();
        $event->roles()->updateExistingPivot($act->id, ['is_accepted' => true]);
        $event = $event->fresh();

        $this->assertTrue($this->onSchedule($act, $event->getGuestUrl()), 'the performer is still preferred once it shows the event');
        $this->get($event->getGuestUrl())->assertOk();
    }

    public function test_a_schedule_the_caller_names_is_kept_and_so_is_the_pick_when_nobody_shows_it(): void
    {
        [$venue, $act, $event] = $this->venueEventWithPendingAct();

        // A caller that names a schedule gets that schedule, shown there or not.
        $this->assertTrue($this->onSchedule($act, $event->getGuestUrl($act->subdomain)));

        // With nobody showing the event yet (a member previewing it), the old pick stands.
        $event->roles()->updateExistingPivot($venue->id, ['is_accepted' => null]);
        $this->assertTrue($this->onSchedule($act, $event->fresh()->getGuestUrl()));
    }
}
