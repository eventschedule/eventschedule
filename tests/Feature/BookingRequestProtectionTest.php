<?php

namespace Tests\Feature;

use App\Http\Controllers\EventController;
use App\Models\Event;
use App\Models\MarketingDailyStat;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewRequestsNotification;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What the public booking request form owes the people on both sides of it.
 *
 * Each test is something the form did that it should not have: handed a venue to whoever owned a
 * typed address, published a request nobody had answered, held the owner's own request for the
 * owner's approval, told a visitor "submitted" from a page nobody runs. BookingRequestFormTest
 * holds the page's markup and the owner's options; this holds what a request does once sent.
 *
 * The assertions use English literals rather than __(): a missing key makes __() return the key
 * name, the view renders that same key name, and an assertion written with __() passes on
 * completely unwired copy.
 */
class BookingRequestProtectionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Mail::fake();
    }

    private function schedule(string $type = 'talent', array $attrs = [], ?User $owner = null): Role
    {
        return $this->createRole($owner ?: $this->createOwner(), $type, $attrs + [
            'name' => 'The Nightjars',
            'accept_requests' => true,
            'require_account' => false,
            'require_approval' => true,
            'event_request_form' => 'booking',
            'timezone' => 'America/New_York',
        ]);
    }

    /** A schedule somebody invented while entering an event: it has a page and no owner. */
    private function nobodysSchedule(string $type = 'talent', string $name = 'Ghost Band'): Role
    {
        $role = new Role;
        $role->subdomain = 'ghost'.random_int(1000, 9999);
        $role->type = $type;
        $role->name = $name;
        $role->timezone = 'America/New_York';
        $role->save();

        return $role->fresh();
    }

    private function body(array $over = []): array
    {
        return $over + [
            'event_name' => 'Wedding Reception',
            'date' => now()->addDays(20)->format('Y-m-d'),
            'start_time' => '19:15',
            'description' => 'Two sets.',
            'venue_name' => 'The Blue Room',
            'venue_city' => 'Springfield',
            'venue_country_code' => 'us',
            'contact_name' => 'Sam Rivera',
            'contact_email' => 'sam.rivera'.random_int(1000, 9999).'@gmail.com',
            'website' => '',
        ];
    }

    private function send(Role $role, array $over = [])
    {
        return $this->postJson(route('event.booking_request.store', ['subdomain' => $role->subdomain]), $this->body($over));
    }

    private function pageHtml(Role $role): string
    {
        return $this->get(route('event.booking_request', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
    }

    private function linkTo(Event $event, Role $role)
    {
        return $event->roles()->where('roles.id', $role->id)->firstOrFail()->pivot->is_accepted;
    }

    private function decide(string $route, Role $role, ?Event $event = null): void
    {
        $params = ['subdomain' => $role->subdomain] + ($event ? ['hash' => UrlUtils::encodeId($event->id)] : []);
        $this->actingAs($role->user)->post(route($route, $params));
        auth()->logout();
    }

    // ---- whose venue it is -------------------------------------------------------------------

    /**
     * The contact email is typed by a stranger and proves nothing. The endpoint used to look up
     * the user who owned it and make them the owner of the venue the stranger had just typed:
     * verified, in their schedule list, and their default schedule if they had none.
     */
    public function test_a_venue_is_never_handed_to_whoever_owns_the_typed_address(): void
    {
        $someoneElse = User::factory()->create(['email' => 'someone.else@gmail.com', 'email_verified_at' => now()]);

        $this->send($this->schedule(), ['contact_email' => 'someone.else@gmail.com', 'venue_name' => 'Anything At All'])->assertOk();

        $venue = Role::where('name', 'Anything At All')->firstOrFail();
        $this->assertNull($venue->user_id);
        $this->assertNull($venue->email_verified_at);
        $someoneElse->refresh();
        $this->assertSame(0, $someoneElse->roles()->count());
        $this->assertNull($someoneElse->default_role_id);
    }

    /** The other half: someone who really is signed in does own the venue they typed. */
    public function test_a_signed_in_visitor_owns_the_venue_they_typed(): void
    {
        $visitor = $this->createOwner();

        $this->actingAs($visitor)->send($this->schedule(), ['venue_name' => 'My Own Room'])->assertOk();

        $this->assertSame($visitor->id, Role::where('name', 'My Own Room')->firstOrFail()->user_id);
    }

    // ---- a request is not public until it is answered ------------------------------------------

    /**
     * The typed venue was attached to the event as accepted whatever happened to the request, so
     * a booking nobody had answered was listed on the venue's public page at once, and stayed
     * there after it was declined.
     */
    public function test_a_request_nobody_has_answered_is_not_listed_on_its_venue(): void
    {
        $talent = $this->schedule();
        $this->send($talent, ['event_name' => 'Private Wedding Reception', 'venue_name' => 'The Secret Garden'])
            ->assertOk()->assertJsonPath('status', 'pending');
        $event = Event::where('name', 'Private Wedding Reception')->firstOrFail();
        $venue = Role::where('name', 'The Secret Garden')->firstOrFail();
        $venuePage = route('role.view_guest', ['subdomain' => $venue->subdomain]);

        $this->assertNull($this->linkTo($event, $venue));
        $this->get($venuePage)->assertOk()->assertDontSee('Private Wedding Reception');

        $this->decide('event.decline', $talent, $event);
        $this->assertNull($this->linkTo($event, $venue));
        $this->get($venuePage)->assertOk()->assertDontSee('Private Wedding Reception');
    }

    public function test_accepting_a_request_lists_it_on_the_venue_that_came_with_it(): void
    {
        $talent = $this->schedule();
        $this->send($talent, ['event_name' => 'Agreed Gig', 'venue_name' => 'The Open Door'])->assertOk();
        $event = Event::where('name', 'Agreed Gig')->firstOrFail();
        $venue = Role::where('name', 'The Open Door')->firstOrFail();

        $this->decide('event.accept', $talent, $event);

        $this->assertSame(1, (int) $this->linkTo($event, $venue));
        $this->get(route('role.view_guest', ['subdomain' => $venue->subdomain]))->assertOk()->assertSee('Agreed Gig');
    }

    /** "Accept all" is the other way a request is accepted, and it left the venue waiting for good. */
    public function test_accepting_all_lists_each_request_on_its_venue_too(): void
    {
        $talent = $this->schedule();
        $this->send($talent, ['event_name' => 'First Gig', 'venue_name' => 'Room One'])->assertOk();
        $this->send($talent, ['event_name' => 'Second Gig', 'venue_name' => 'Room Two'])->assertOk();

        $this->decide('event.accept_all', $talent);

        foreach (['First Gig' => 'Room One', 'Second Gig' => 'Room Two'] as $eventName => $venueName) {
            $this->assertSame(1, (int) $this->linkTo(Event::where('name', $eventName)->firstOrFail(), Role::where('name', $venueName)->firstOrFail()));
        }
    }

    /**
     * Accepting answers for the place that came with the request and for nothing else: a waiting
     * link to a schedule somebody runs is theirs to answer, and one to a placeholder that is not a
     * venue was not made by this form.
     */
    public function test_accepting_a_request_answers_for_no_other_schedule(): void
    {
        $talent = $this->schedule();
        $this->send($talent, ['event_name' => 'Agreed Gig', 'venue_name' => 'The Open Door'])->assertOk();
        $event = Event::where('name', 'Agreed Gig')->firstOrFail();
        $ownedVenue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Somebody Runs This']);
        $ownerlessTalent = $this->nobodysSchedule('talent', 'Opening Act');
        $event->roles()->attach($ownedVenue->id, ['is_accepted' => null]);
        $event->roles()->attach($ownerlessTalent->id, ['is_accepted' => null]);

        $this->decide('event.accept', $talent, $event);

        $this->assertNull($this->linkTo($event, $ownedVenue));
        $this->assertNull($this->linkTo($event, $ownerlessTalent));
    }

    // ---- who has to approve --------------------------------------------------------------------

    /** The endpoint read require_approval and nothing else, so the owner's own request waited for the owner. */
    public function test_the_owners_own_request_does_not_wait_for_the_owner(): void
    {
        $owner = $this->createOwner();
        $venue = $this->schedule('venue', ['name' => 'Springfield Hall'], $owner);

        $this->actingAs($owner)->send($venue)->assertOk()->assertJsonPath('status', 'live');

        $this->assertSame(1, (int) $this->linkTo(Event::latest('id')->firstOrFail(), $venue));
        Notification::assertNothingSent();
    }

    /** Nobody owns it, so nobody could ever approve: the request used to wait for ever, and the visitor was told "submitted". */
    public function test_a_schedule_nobody_owns_does_not_hold_a_request_for_approval(): void
    {
        $placeholder = $this->nobodysSchedule();

        // Still needs an account: there is no owner to file an anonymous request under.
        $this->send($placeholder)->assertStatus(422)->assertJsonValidationErrors(['create_account']);

        $this->send($placeholder, ['create_account' => '1', 'password' => 'long-enough-password', 'terms' => 'on'])
            ->assertOk()->assertJsonPath('status', 'live');
        $this->assertSame(1, (int) $this->linkTo(Event::latest('id')->firstOrFail(), $placeholder));
    }

    public function test_a_strangers_request_still_waits_and_the_owner_is_told(): void
    {
        $talent = $this->schedule();

        $this->send($talent)->assertOk()->assertJsonPath('status', 'pending')->assertJsonPath('emails_you', false);

        $this->assertNull($this->linkTo(Event::latest('id')->firstOrFail(), $talent));
        Notification::assertSentToTimes($talent->user, NewRequestsNotification::class, 1);
    }

    /**
     * The app emails an answer to an account and never to a typed address, so the page has to
     * know which kind of visitor it is talking to before it says "we email you".
     */
    public function test_the_answer_says_whether_the_app_will_email_the_decision(): void
    {
        $talent = $this->schedule();

        $this->send($talent)->assertOk()->assertJsonPath('emails_you', false)
            // Still there for a page that was loaded before the answer had a status.
            ->assertJsonPath('redirect_url', $talent->getGuestUrl());
        auth()->logout();

        $this->actingAs($this->createOwner())->send($talent)->assertOk()->assertJsonPath('emails_you', true);
    }

    // ---- an event that appears at once ---------------------------------------------------------

    /**
     * With nothing required and approval off, a request went straight onto the public schedule as
     * "Submit Event" with no date. What the owner left optional was for requests they read first.
     */
    public function test_an_event_that_will_appear_at_once_needs_a_name_and_a_time(): void
    {
        $open = $this->schedule('venue', ['name' => 'Open Hall', 'require_approval' => false]);
        $bare = ['contact_name' => 'Sam Rivera', 'contact_email' => 'sam.rivera@gmail.com', 'website' => ''];
        $url = route('event.booking_request.store', ['subdomain' => $open->subdomain]);

        $this->postJson($url, $bare)->assertStatus(422)->assertJsonValidationErrors(['event_name', 'date', 'start_time']);
        $this->assertSame(0, Event::count());

        // The page asks for the same, so the visitor is not surprised by it.
        $this->assertSame(1, preg_match('/^\s*required: (\{.*\}),$/m', $this->pageHtml($open), $found));
        $this->assertSame(['event_name' => true, 'date_time' => true], array_intersect_key(json_decode($found[1], true), ['event_name' => 1, 'date_time' => 1]));

        // A schedule that reads each request first still takes one with nothing in it.
        $reviewed = $this->schedule('venue', ['name' => 'Reviewed Hall']);
        $this->postJson(route('event.booking_request.store', ['subdomain' => $reviewed->subdomain]), $bare)->assertOk();
    }

    // ---- how long ------------------------------------------------------------------------------

    public function test_an_end_time_is_kept_as_the_length_of_the_event(): void
    {
        $talent = $this->schedule();

        $this->send($talent, ['start_time' => '19:15', 'end_time' => '23:00'])->assertOk();
        $this->assertEquals(3.75, Event::latest('id')->firstOrFail()->duration);

        // An end before the start is the next day.
        $this->send($talent, ['start_time' => '22:00', 'end_time' => '01:30'])->assertOk();
        $this->assertEquals(3.5, Event::latest('id')->firstOrFail()->duration);

        $this->send($talent)->assertOk();
        $this->assertNull(Event::latest('id')->firstOrFail()->duration);
    }

    public function test_an_end_time_needs_a_start_and_a_readable_one(): void
    {
        $talent = $this->schedule();

        $this->send($talent, ['date' => null, 'start_time' => null, 'end_time' => '23:00'])
            ->assertStatus(422)->assertJsonValidationErrors(['start_time']);
        $this->send($talent, ['end_time' => 'late'])->assertStatus(422)->assertJsonValidationErrors(['end_time']);
        $this->assertSame(0, Event::count());
    }

    // ---- what the page says --------------------------------------------------------------------

    public function test_it_says_what_pressing_send_leads_to(): void
    {
        $this->assertStringContainsString('Your request goes to The Nightjars, who accepts or declines it.', $this->pageHtml($this->schedule()));

        $venue = $this->pageHtml($this->schedule('venue', ['name' => 'Springfield Hall']));
        $this->assertStringContainsString('Springfield Hall reviews each event before it appears.', $venue);

        $open = $this->pageHtml($this->schedule('venue', ['name' => 'Open Hall', 'require_approval' => false]));
        $this->assertStringContainsString('Your event appears on Open Hall as soon as you submit.', $open);
        $this->assertStringNotContainsString('reviews each event before it appears', $open);
    }

    /** Nobody will read a request there or answer one, and the page used to be titled "Booking Request". */
    public function test_a_page_nobody_owns_is_not_called_a_booking_request(): void
    {
        $html = $this->pageHtml($this->nobodysSchedule());

        $this->assertStringContainsString('Nobody manages Ghost Band yet. Your event appears on it as soon as you submit, and no one will reply.', $html);
        $this->assertStringNotContainsString('who accepts or declines it', $html);
        $this->assertStringContainsString('<title>Submit Event', $html);
        // The account is not a box to tick there: it is the only way through, and the page says so first.
        $this->assertStringContainsString('An account is needed to add an event to a page nobody manages yet.', $html);
        $this->assertStringContainsString('mustHaveAccount: true,', $html);
        $this->assertStringContainsString('createAccount: true,', $html);
        $this->assertStringNotContainsString('id="create_account"', $html);
        // And nobody is promised a reply by a schedule that has nobody.
        $this->assertStringNotContainsString('so it can reply to your request', $html);
        $this->assertStringContainsString('Whoever claims this page later will see the contact details you enter here.', $html);
    }

    public function test_the_request_terms_are_read_before_the_form_and_outside_the_vue_mount(): void
    {
        $html = $this->pageHtml($this->schedule('venue', ['request_terms' => 'A 25% deposit confirms a booking.']));

        $terms = strpos($html, 'A 25% deposit confirms a booking.');
        $mount = strpos($html, '<div id="event-submit-app" data-vue-root>');
        $this->assertNotFalse($terms);
        $this->assertNotFalse($mount);
        $this->assertLessThan($mount, $terms, 'the terms sit above the form, where they are read first and never compiled as a template');
        // And only there: a second copy inside the mount would be the owner's text in a template.
        $this->assertStringNotContainsString('A 25% deposit confirms a booking.', substr($html, $mount));
    }

    /**
     * The page is a Vue mount, so anything the owner or the visitor wrote that is echoed inside it
     * is compiled as a template unless it carries v-pre or arrives as data.
     */
    public function test_nothing_an_owner_wrote_is_compiled_as_a_template(): void
    {
        $payload = '{{ 7 * 191 }}';
        $owner = $this->createOwner();
        $venue = $this->schedule('venue', [
            'name' => 'Hall '.$payload,
            'address1' => $payload.' Main St',
            'request_terms' => 'Terms '.$payload,
            'event_custom_fields' => ['q1' => ['name' => 'Question '.$payload, 'type' => 'dropdown', 'options' => 'A '.$payload.',B', 'show_on_request' => true]],
        ], $owner);
        $visitor = User::factory()->create(['name' => 'Sam '.$payload, 'email_verified_at' => now()]);

        $html = $this->actingAs($visitor)->get(route('event.booking_request', ['subdomain' => $venue->subdomain]))->assertOk()->getContent();

        $start = strpos($html, '<div id="event-submit-app" data-vue-root>');
        $end = strpos($html, '<script', $start);
        $mounted = substr($html, $start, $end - $start);
        $this->assertGreaterThan(2, substr_count($mounted, e($payload)), 'the fixture should reach the mount');

        // Every occurrence inside the mount sits in an element that opted out with v-pre.
        $offset = 0;
        while (($at = strpos($mounted, e($payload), $offset)) !== false) {
            $tagStart = strrpos(substr($mounted, 0, $at), '<');
            $tag = substr($mounted, $tagStart, strpos($mounted, '>', $tagStart) - $tagStart + 1);
            $this->assertStringContainsString('v-pre', $tag, 'unguarded owner or visitor text: '.$tag);
            $offset = $at + 1;
        }
    }

    public function test_the_two_request_pages_share_one_kit_and_one_stylesheet(): void
    {
        foreach (['event/booking-request.blade.php', 'event/guest-submit.blade.php'] as $view) {
            $source = file_get_contents(resource_path('views/'.$view));
            $this->assertStringContainsString("@include('partials.request-form-styles')", $source, $view);
            $this->assertStringContainsString("@include('partials.request-form-kit')", $source, $view);
        }
        // Shared means written once: neither page's own script reads a typed time.
        foreach (['event/partials/booking-request-script.blade.php', 'event/partials/guest-submit-script.blade.php'] as $script) {
            $source = file_get_contents(resource_path('views/'.$script));
            $this->assertStringContainsString('mixins: [window.RequestFormKit]', $source, $script);
            $this->assertStringNotContainsString('parseTime(text) {', $source, $script.' has its own time reader again');
        }
        $this->assertStringContainsString('parseTime(text) {', file_get_contents(resource_path('views/partials/request-form-kit.blade.php')));
    }

    // ---- an account, where it can be proved ----------------------------------------------------

    /**
     * On hosted an account made here proves its address with the emailed code, whose bot check is
     * registered for our own hosts. On a schedule's custom domain that check cannot run, so the
     * code could never be sent: the form there takes requests without offering an account.
     */
    public function test_the_account_option_is_not_offered_on_a_schedules_own_domain(): void
    {
        config(['app.hosted' => true]);
        $role = $this->schedule();
        $offered = function (bool $onCustomDomain) use ($role): bool {
            $request = Request::create(route('event.booking_request', ['subdomain' => $role->subdomain]));
            $request->headers->set('User-Agent', 'Mozilla/5.0');
            if ($onCustomDomain) {
                $request->attributes->set('custom_domain_host', 'tickets.example.com');
            }

            return app(EventController::class)->showBookingRequest($request, $role->subdomain)->getData()['offerAccount'];
        };

        $this->assertTrue($offered(false));
        $this->assertFalse($offered(true));
    }

    /** The emailed code is asked of this form's accounts where it is asked of everyone else's. */
    public function test_the_page_only_asks_for_a_code_where_the_server_will_check_one(): void
    {
        $role = $this->schedule();

        // Under APP_TESTING (this suite, and the browser tests) the server checks no code, and the page shows no code step.
        $this->assertStringContainsString('requiresCode: false,', $this->pageHtml($role));

        config(['app.is_testing' => false]);
        $html = $this->pageHtml($role);
        $this->assertStringContainsString('requiresCode: true,', $html);
        $this->assertStringContainsString("formKind: 'booking',", $html);
        $this->assertMatchesRegularExpression('/<div id="code-boxes"[^>]*dir="ltr"/', $html);
    }

    // ---- counted --------------------------------------------------------------------------------

    private function browser(string $ip = '203.0.113.9', bool $json = true): array
    {
        return [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120 Safari/537.36',
            'HTTP_ACCEPT' => $json ? 'application/json' : 'text/html,application/xhtml+xml',
            'HTTP_ACCEPT_LANGUAGE' => 'en-GB,en;q=0.9',
            'HTTP_CF_CONNECTING_IP' => $ip,
        ];
    }

    private function stat(string $column): int
    {
        return (int) (MarketingDailyStat::where('date', now()->toDateString())->value($column) ?? 0);
    }

    public function test_seeing_the_form_and_sending_a_request_each_count_a_visitor_once_a_day(): void
    {
        $talent = $this->schedule();
        $page = route('event.booking_request', ['subdomain' => $talent->subdomain]);
        $store = route('event.booking_request.store', ['subdomain' => $talent->subdomain]);

        $this->get($page, $this->browser(json: false))->assertOk();
        $this->get($page, $this->browser(json: false))->assertOk();
        $this->get($page, $this->browser('203.0.113.77', false))->assertOk();
        $this->assertSame(2, $this->stat('booking_request_views'));

        $this->postJson($store, $this->body(), $this->browser())->assertOk();
        $this->postJson($store, $this->body(), $this->browser())->assertOk();
        $this->assertSame(1, $this->stat('booking_request_submissions'));

        // A refused request is not a request sent.
        $this->postJson($store, $this->body(['contact_email' => 'not-an-address']), $this->browser('203.0.113.50'))->assertStatus(422);
        $this->assertSame(1, $this->stat('booking_request_submissions'));
        // And neither is the submit page's business.
        $this->assertSame(0, $this->stat('guest_submit_views'));
        $this->assertSame(0, $this->stat('guest_submit_submissions'));
    }

    /** This form asks for a code on the submit page's route. Its codes are not a stage of that page's funnel. */
    public function test_a_code_asked_for_here_is_not_counted_as_the_submit_pages(): void
    {
        config(['app.hosted' => true]);
        $url = route('event.guest_send_code', ['subdomain' => $this->schedule()->subdomain]);

        $this->postJson($url, ['email' => 'sam.rivera@gmail.com', 'website' => '', 'form' => 'booking'], $this->browser())->assertOk();
        $this->assertSame(0, $this->stat('guest_submit_code_requests'));

        // The submit page's own, from another visitor, still is.
        $this->postJson($url, ['email' => 'kim.lee@gmail.com', 'website' => ''], $this->browser('203.0.113.77'))->assertOk();
        $this->assertSame(1, $this->stat('guest_submit_code_requests'));
    }

    // ---- what the owner sees --------------------------------------------------------------------

    /** The card showed who asked and when, and not where: the owner had to open the request to learn that. */
    public function test_the_owners_request_card_says_where_and_until_when(): void
    {
        $talent = $this->schedule();
        $this->send($talent, ['start_time' => '19:15', 'end_time' => '23:00', 'is_online' => true, 'event_url' => 'https://meet.example.com/abc'])->assertOk();

        $card = $this->actingAs($talent->user)
            ->get(route('role.view_admin', ['subdomain' => $talent->subdomain, 'tab' => 'requests']))
            ->assertOk()->getContent();

        $this->assertSame(1, preg_match('/<div data-request-place[^>]*>(.*?)<\/div>/s', $card, $place));
        $this->assertStringContainsString('The Blue Room, Springfield', $place[1]);
        $this->assertStringContainsString('Online', $place[1]);
        $this->assertStringContainsString('7:15 PM - 11:00 PM', preg_replace('/\s+/', ' ', strip_tags($card)));

        // The end follows the clock the start is shown on: the viewer's own choice comes before the schedule's.
        $talent->user->forceFill(['use_24_hour_time' => true])->save();
        $card = $this->actingAs($talent->user->fresh())
            ->get(route('role.view_admin', ['subdomain' => $talent->subdomain, 'tab' => 'requests']))
            ->assertOk()->getContent();
        $this->assertStringContainsString('19:15 - 23:00', preg_replace('/\s+/', ' ', strip_tags($card)));
    }

    public function test_a_venues_own_address_is_not_repeated_back_to_it_on_the_card(): void
    {
        $venue = $this->schedule('venue', ['name' => 'Springfield Hall']);
        $this->send($venue)->assertOk();

        $this->actingAs($venue->user)
            ->get(route('role.view_admin', ['subdomain' => $venue->subdomain, 'tab' => 'requests']))
            ->assertOk()->assertDontSee('data-request-place', false);
    }
}
