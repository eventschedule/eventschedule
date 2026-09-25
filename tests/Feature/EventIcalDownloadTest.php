<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An event's .ics download answers exactly when the schedule's own event page shows the event to
 * this visitor, and otherwise as an unknown event does.
 *
 * It used to ask only whether the event was attached to the schedule, in any state: a listing the
 * schedule had declined, or never answered, downloaded in full - title, description and address.
 * A locked event answered 403, which said it was there, and an unlisted one was refused even to
 * the people its page shows it to, so that page's own Apple Calendar link was dead. An
 * appointment booking, the one unlisted event nobody but its schedule may open, is members-only
 * in its own right (AppointmentPrivacyTest).
 */
class EventIcalDownloadTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function ics(Role $role, Event $event): string
    {
        return '/'.$role->subdomain.'/'.$event->slug.'/'.UrlUtils::encodeId($event->id).'/ical';
    }

    /** The body without the per-request CSP nonces and CSRF token. */
    private function body(TestResponse $response): string
    {
        return preg_replace(
            ['~nonce="[^"]*"~', '~(<meta name="csrf-token" content=")[^"]*~'],
            ['nonce=""', '$1'],
            (string) $response->getContent()
        );
    }

    /** @return array<string, Event> */
    private function events(Role $venue): array
    {
        $events = [
            'public' => $this->createEvent($venue, ['name' => 'Open Night']),
            'unlisted' => $this->createEvent($venue, ['name' => 'Link Only Night', 'is_private' => true]),
            'pending' => $this->createEvent($venue, ['name' => 'Pending Night']),
            'declined' => $this->createEvent($venue, ['name' => 'Declined Night']),
            'draft' => $this->createEvent($venue, ['name' => 'Draft Night', 'is_draft' => true]),
            'locked' => $this->createEvent($venue, ['name' => 'Locked Night', 'is_private' => true, 'event_password' => 'hunter2']),
            // A save keeps a password only on an unlisted event now; older rows are listed with one.
            'older locked' => $this->createEvent($venue, ['name' => 'Old Locked Night', 'event_password' => 'hunter2']),
        ];

        $events['pending']->roles()->updateExistingPivot($venue->id, ['is_accepted' => null]);
        $events['declined']->roles()->updateExistingPivot($venue->id, ['is_accepted' => false]);

        return $events;
    }

    public function test_a_visitor_downloads_only_what_the_event_page_shows_them(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Wine Bar']);
        $events = $this->events($venue);

        // The page itself, as the control: it shows the public event and no pending one.
        $this->get($this->guestEventUrl($venue, $events['public']))->assertOk();
        $this->get($this->guestEventUrl($venue, $events['pending']))->assertNotFound();

        $unknown = $this->get('/'.$venue->subdomain.'/no-such-event/'.UrlUtils::encodeId(999999).'/ical')->assertNotFound();

        foreach (['pending', 'declined', 'draft', 'locked', 'older locked'] as $what) {
            $response = $this->get($this->ics($venue, $events[$what]));

            $this->assertSame(404, $response->getStatusCode(), "a visitor downloads the {$what} event");
            $this->assertSame($this->body($unknown), $this->body($response), "the {$what} event answers unlike an unknown one");
        }

        $this->get($this->ics($venue, $events['public']))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->assertSee("SUMMARY:Open Night at Harbour Wine Bar\r\n", false);

        // Unlisted is no bar on the page - anybody holding the link may open it - so it is none
        // here, and the page's own Apple Calendar link downloads.
        $page = $this->get($this->guestEventUrl($venue, $events['unlisted']))->assertOk();
        $link = $events['unlisted']->getAppleCalendarUrl(null, $venue->subdomain);
        $page->assertSee('href="'.e($link).'"', false);
        $this->get($link)->assertOk()->assertSee('SUMMARY:Link Only Night', false);
    }

    public function test_a_password_event_downloads_once_past_the_password(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Harbour Wine Bar']);
        $events = $this->events($venue);

        // A session that entered the password, as the page's gate records it. The unlisted one
        // used to stay a 404 even then.
        foreach (['locked', 'older locked'] as $what) {
            $this->withSession(['event_password_'.$events[$what]->id => true])
                ->get($this->ics($venue, $events[$what]))
                ->assertOk()
                ->assertSee($events[$what]->name);

            $this->flushSession();
        }

        // Its own people reach everything the page shows them, the pending listing included.
        $this->actingAs($owner);

        foreach ($events as $what => $event) {
            $this->get($this->ics($venue, $event))->assertOk();
        }
    }

    /**
     * The page links the .ics on the schedule the visitor is looking at. A link that names no
     * schedule goes to the first one that shows the event (EventLinkServingScheduleTest), which
     * is the act once it accepts: a working download too, but not the page the visitor is on.
     */
    public function test_the_event_pages_apple_link_names_the_schedule_it_is_on(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Wine Bar']);
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Cellar Trio']);
        $event = $this->createEvent($venue, ['name' => 'Trio Night', 'creator_role_id' => $venue->id]);
        $event->roles()->attach($act->id, ['is_accepted' => true]);
        $event = $event->fresh();

        $link = $event->getAppleCalendarUrl(null, $venue->subdomain);
        $this->assertNotSame($event->getAppleCalendarUrl(), $link, 'fixture: the plain link names the act');

        $this->get($this->guestEventUrl($venue, $event))
            ->assertOk()
            ->assertSee('href="'.e($link).'"', false);

        $this->get($link)->assertOk()->assertSee('SUMMARY:Trio Night', false);
    }
}
