<?php

namespace Tests\Feature;

use App\Models\DismissedNextStep;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\SetupGuide;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The setup guide (App\Utils\SetupGuide): the thing that replaced the three-circle step band.
 *
 * Three properties carry the rest, and each has gone wrong on paper before it was built:
 *
 *   WHO HAS ONE. Only somebody whose first own schedule was saved through the wizard. An
 *   earlier design derived it from "owns a young schedule", which reached accounts minted by the
 *   guest-submit flow, people matched to an auto-created venue, claims, the API and restores.
 *
 *   WHAT COUNTS AS DONE. A draft is not live. An appointment booking is an events row named for
 *   the guest who booked it, and would both tick a step and print that guest's name in the
 *   guide. Registration has no ticket row at all. One weekly event fills a page on its own.
 *
 *   WHAT IT MAY TOUCH. It hides the dashboard's Next steps rows it is asking for itself - and
 *   "Dismiss all" must not then permanently dismiss a row nobody was shown, because a dismissal
 *   also silences that step's nudge mail.
 */
class SetupGuideTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    /** Sign up, then save a first schedule the way a person does: through /new/{type}. */
    private function startGuide(string $type = 'venue', array $overrides = []): array
    {
        $user = $this->createOwner();
        $this->actingAs($user);
        $role = $this->submitNewScheduleForm($type, $overrides);

        return [$user, $role];
    }

    /** What the database holds, whatever the model in hand thinks. */
    private function stored(User $user): ?array
    {
        $raw = DB::table('users')->where('id', $user->id)->value('setup_guide');

        return $raw === null ? null : json_decode($raw, true);
    }

    private function state(User $user): ?array
    {
        SetupGuide::flush();

        return SetupGuide::state($user);
    }

    private function publishedEvent(Role $role, array $attrs = []): Event
    {
        return $this->createEvent($role, $attrs + ['creator_role_id' => $role->id]);
    }

    /** The payload the page hands the component, or null when the page carries no guide. */
    private function guideOn(string $html): ?array
    {
        if (! preg_match('/<script type="application\/json" data-setup-guide-json>(.*?)<\/script>/s', $html, $match)) {
            return null;
        }

        return json_decode($match[1], true);
    }

    private function surfaceOf(string $html): ?string
    {
        return preg_match('/data-setup-guide="([a-z]+)"/', $html, $match) ? $match[1] : null;
    }

    private function post_(array $payload)
    {
        return $this->postJson(route('home.setup_guide'), $payload);
    }

    // ---- Who has one ---------------------------------------------------------------------

    public function test_the_wizards_first_save_starts_a_guide_for_that_schedule(): void
    {
        [$user, $role] = $this->startGuide();

        $guide = $this->stored($user);

        $this->assertSame($role->id, $guide['role_id']);
        $this->assertNotEmpty($guide['started_at']);
        $this->assertSame(['role_id', 'started_at'], array_keys($guide), 'a fresh guide carries nothing else');
    }

    public function test_a_second_schedule_does_not_move_the_guide(): void
    {
        [$user, $first] = $this->startGuide();

        $this->submitNewScheduleForm('talent', ['name' => 'Second']);

        $this->assertSame($first->id, $this->stored($user)['role_id']);
    }

    public function test_a_schedule_made_any_other_way_starts_nothing(): void
    {
        $user = $this->createOwner();
        $this->createRole($user);

        $this->assertNull($this->stored($user));

        // The dashboard's card is there for them, as the LIST of suggestions ("add your first
        // event"), and carries no guide.
        $html = $this->actingAs($user)->get(route('home'))->assertOk()->getContent();

        $this->assertSame('steps', $this->surfaceOf($html));
        $this->assertArrayNotHasKey('role_id', $this->guideOn($html));
    }

    /** That visitor came to send an event to somebody else's schedule, and keeps the step band. */
    public function test_the_guest_submit_flow_starts_nothing(): void
    {
        $host = $this->createRole($this->createOwner(), 'venue');
        $user = $this->createOwner();

        $this->actingAs($user)->withSession(['pending_request' => $host->subdomain]);
        $this->submitNewScheduleForm('talent');

        $this->assertNull($this->stored($user));
    }

    public function test_deleting_the_schedule_and_starting_again_gets_a_fresh_guide(): void
    {
        [$user, $first] = $this->startGuide();
        SetupGuide::stamp($user, 'dismissed_at');
        SetupGuide::stamp($user, 'shared_at');
        SetupGuide::skip($user, 'tickets');

        DB::table('role_user')->where('role_id', $first->id)->delete();
        DB::table('roles')->where('id', $first->id)->delete();

        $second = $this->submitNewScheduleForm('venue', ['name' => 'Again']);
        $guide = $this->stored($user);

        $this->assertSame($second->id, $guide['role_id']);
        $this->assertArrayNotHasKey('dismissed_at', $guide, 'the old answers do not follow a new schedule');
        $this->assertArrayNotHasKey('shared_at', $guide);
        $this->assertArrayNotHasKey('skipped', $guide);
    }

    public function test_someone_who_finished_a_guide_is_not_started_on_another(): void
    {
        [$user, $first] = $this->startGuide();
        SetupGuide::stamp($user, 'completed_at');

        DB::table('role_user')->where('role_id', $first->id)->delete();
        DB::table('roles')->where('id', $first->id)->delete();

        $this->submitNewScheduleForm('venue', ['name' => 'Again']);

        $this->assertSame($first->id, $this->stored($user)['role_id']);
        $this->assertNotEmpty($this->stored($user)['completed_at']);
    }

    public function test_it_ends_after_thirty_days(): void
    {
        [$user] = $this->startGuide();

        $this->travel(SetupGuide::LIFETIME_DAYS - 1)->days();
        $this->assertNotNull($this->state($user->fresh()));

        $this->travel(2)->days();
        $this->assertNull($this->state($user->fresh()));
    }

    public function test_it_goes_quiet_after_seventy_two_hours(): void
    {
        [$user] = $this->startGuide();

        $this->assertFalse($this->state($user->fresh())['quiet']);

        $this->travel(SetupGuide::QUIET_HOURS + 1)->hours();

        $this->assertTrue($this->state($user->fresh())['quiet']);
    }

    // ---- What counts as done ---------------------------------------------------------------

    public function test_a_new_schedule_is_one_step_from_live(): void
    {
        [$user] = $this->startGuide();

        $state = $this->state($user);

        $this->assertFalse($state['live']);
        $this->assertFalse($state['celebrate']);
        $this->assertSame('event', $state['current']);
        $this->assertSame(1, $state['remaining']);
        $this->assertNull($state['draft']);
    }

    public function test_a_draft_is_not_live_and_is_offered_for_publishing(): void
    {
        [$user, $role] = $this->startGuide();
        $draft = $this->publishedEvent($role, ['name' => 'Friday Jazz Night', 'is_draft' => true]);

        $state = $this->state($user);

        $this->assertFalse($state['live']);
        $this->assertSame('Friday Jazz Night', $state['draft']['name']);
        $this->assertSame(
            route('event.publish', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($draft->id)]),
            $state['draft']['publish']
        );
    }

    public function test_an_internal_event_and_an_appointment_booking_are_not_events_on_the_page(): void
    {
        [$user, $role] = $this->startGuide();
        $type = $this->createAppointmentType($role);

        $this->publishedEvent($role, ['name' => 'Staff only', 'is_internal' => true]);
        $this->publishedEvent($role, ['name' => 'Unlisted', 'is_private' => true]);

        // Event's saving hook makes a booking unlisted, which would hide it here anyway. A quiet
        // save does not run that hook (BackupService::importEvent() repeats it by hand for this
        // reason), so the booking is also written the way one of those leaves it: listed. The
        // guide's own "not a booking" test is what has to keep that guest's name out.
        $booking = $this->publishedEvent($role, ['name' => '30 Minute Meeting - Dana Guest', 'appointment_type_id' => $type->id]);
        DB::table('events')->where('id', $booking->id)->update(['is_private' => false]);

        $state = $this->state($user);

        $this->assertFalse($state['live'], 'none of those is on the public page');
        $this->assertSame([], $state['events'], 'and a guest who booked is never named in the guide');
    }

    public function test_a_published_event_makes_it_live_and_opens_the_second_stretch(): void
    {
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role, ['name' => 'Friday Jazz Night']);

        $state = $this->state($user);

        $this->assertTrue($state['live']);
        $this->assertTrue($state['celebrate']);
        $this->assertSame('events', $state['current']);
        $this->assertSame(3, $state['remaining']);
        $this->assertSame(['event', 'events', 'share', 'tickets'], array_keys($state['steps']));
        $this->assertSame('Friday Jazz Night', $state['events'][0]['name']);
    }

    public function test_three_events_or_one_that_repeats_make_a_page_worth_sharing(): void
    {
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role);
        $this->publishedEvent($role);

        $this->assertFalse($this->state($user)['steps']['events']['done'], 'two is not three');

        $this->publishedEvent($role);

        $this->assertTrue($this->state($user)['steps']['events']['done']);
        $this->assertSame('share', $this->state($user)['current']);

        [$other, $weekly] = $this->startGuide('talent');
        $this->publishedEvent($weekly, ['days_of_week' => '0100000', 'recurring_frequency' => 'weekly']);

        $state = $this->state($other);

        $this->assertTrue($state['steps']['events']['done'], 'one weekly event fills the page on its own');
        $this->assertTrue($state['events'][0]['repeats']);
    }

    public function test_a_ticket_type_or_registration_answers_the_tickets_step(): void
    {
        [$user, $role] = $this->startGuide();
        $event = $this->publishedEvent($role);

        $this->assertFalse($this->state($user)['steps']['tickets']['done']);

        $event->rsvp_enabled = true;
        $event->save();

        $this->assertTrue($this->state($user)['steps']['tickets']['done'], 'registration has no ticket row at all');

        $event->rsvp_enabled = false;
        $event->save();
        $this->createTicket($event);

        $this->assertTrue($this->state($user)['steps']['tickets']['done']);
    }

    /** It lists other people's events, owns none of them and can price none. */
    public function test_a_curator_that_owns_no_events_is_not_asked_about_tickets(): void
    {
        [$user, $curator] = $this->startGuide('curator');
        $venue = $this->createRole($this->createOwner(), 'venue');

        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $state = $this->state($user);

        $this->assertTrue($state['live'], 'a listed event is on its page');
        $this->assertSame(['event', 'events', 'share'], array_keys($state['steps']));
        $this->assertNull($state['ticket_event']);
    }

    /** An import undone can empty a schedule again. No second celebration when it refills. */
    public function test_losing_every_event_goes_back_to_the_first_stretch_quietly(): void
    {
        [$user, $role] = $this->startGuide();
        $event = $this->publishedEvent($role);

        $this->post_(['action' => 'celebrated'])->assertOk();
        $this->assertNotEmpty($this->stored($user)['celebrated_at']);

        $event->roles()->detach();
        $event->delete();

        $state = $this->state($user->fresh());

        $this->assertFalse($state['live']);
        $this->assertSame('event', $state['current']);

        $this->publishedEvent($role);

        $this->assertFalse($this->state($user->fresh())['celebrate']);
    }

    /**
     * 23:30 UTC on the 10th is the 11th in Tokyo and still the 10th in Los Angeles. An event is on
     * a given day because of where it happens, not because of who is looking at the dashboard.
     */
    public function test_a_tile_shows_the_date_in_the_schedules_timezone_not_the_viewers(): void
    {
        [$user, $role] = $this->startGuide();

        DB::table('roles')->where('id', $role->id)->update(['timezone' => 'Asia/Tokyo']);
        DB::table('users')->where('id', $user->id)->update(['timezone' => 'America/Los_Angeles']);

        $day = Carbon::now('UTC')->addDays(40)->setTime(23, 30);
        $this->publishedEvent($role->fresh(), ['starts_at' => $day->format('Y-m-d H:i:s')]);

        $tile = $this->state($user->fresh())['events'][0];

        $this->assertSame($day->copy()->setTimezone('Asia/Tokyo')->format('j'), $tile['day']);
        $this->assertNotSame($day->copy()->setTimezone('America/Los_Angeles')->format('j'), $tile['day']);
    }

    // ---- Where it shows --------------------------------------------------------------------

    public function test_each_page_gets_the_shape_that_belongs_on_it(): void
    {
        [$user, $role] = $this->startGuide();
        $slug = ['subdomain' => $role->subdomain];

        $form = $this->get(route('event.create', $slug))->assertOk()->getContent();

        $this->assertSame('dock', $this->surfaceOf($form), 'the first-event form: docked beside the fields');
        $this->assertStringNotContainsString('class="step-indicator', $form, 'and no band');
        $this->assertStringContainsString(__('messages.setup_guide_then_live', ['name' => $role->name]), $form);

        $this->assertSame('section', $this->surfaceOf($this->get(route('home'))->getContent()));
        $this->assertSame('pill', $this->surfaceOf($this->get(route('role.view_admin', $slug + ['tab' => 'schedule']))->getContent()));
        $this->assertSame('pill', $this->surfaceOf($this->get(route('role.view_admin', $slug + ['tab' => 'followers']))->getContent()));
        $this->assertSame('ring', $this->surfaceOf($this->get(route('role.edit', $slug))->getContent()));

        $this->publishedEvent($role);
        $this->post_(['action' => 'celebrated']);

        $this->assertSame('ring', $this->surfaceOf($this->get(route('event.create', $slug))->getContent()), 'once live, the event form gets the ring');
    }

    public function test_it_keeps_to_its_own_schedule_and_off_pages_that_are_not_one(): void
    {
        [$user, $role] = $this->startGuide();
        $other = $this->submitNewScheduleForm('talent', ['name' => 'Second']);

        $this->assertNull($this->surfaceOf(
            $this->get(route('role.view_admin', ['subdomain' => $other->subdomain, 'tab' => 'schedule']))->assertOk()->getContent()
        ), 'another schedule of the same owner');
        $this->assertNull($this->surfaceOf($this->get(route('sales'))->assertOk()->getContent()), 'a page that belongs to no schedule');
        $this->assertNull($this->surfaceOf(
            $this->get(route('event.show_import_ai', ['subdomain' => $role->subdomain]))->assertOk()->getContent()
        ), 'the import page, whose own forward button sits where the pill would');
        $this->assertNull($this->guideOn(
            $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]))->assertOk()->getContent()
        ), 'the guest portal shares the shell and must never carry it');
    }

    public function test_the_sidebar_line_is_on_every_admin_page_and_brings_a_hidden_guide_back(): void
    {
        [$user, $role] = $this->startGuide();
        $line = route('home').'#setup-guide';

        $this->get(route('sales'))->assertSee($line, false);

        $this->post_(['action' => 'dismiss'])->assertOk();

        $page = $this->get(route('sales'))->assertOk();
        $page->assertDontSee($line, false);
        $page->assertSee('name="action" value="restore"', false);
        $this->assertNull($this->surfaceOf($this->get(route('home'))->getContent()), 'hidden means hidden');

        // The plain form the sidebar posts: back to the dashboard, where the guide lives.
        $this->post(route('home.setup_guide'), ['action' => 'restore'])->assertRedirect($line);
        $this->assertSame('section', $this->surfaceOf($this->get(route('home'))->getContent()));

        // The line leads to the guide, so it goes when the guide would: the schedule handed on.
        DB::table('role_user')->where('role_id', $role->id)->where('user_id', $user->id)->update(['level' => 'admin']);
        $this->get(route('sales'))->assertOk()->assertDontSee($line, false);
    }

    /**
     * The sidebar asks about the guide on every admin page, and nearly all of them can show no
     * shape. Those pages answer from the stored column and one ownership check; counting the
     * schedule's events, tickets and plan is for the pages that draw it.
     */
    public function test_a_page_that_can_carry_no_shape_does_not_resolve_the_guide(): void
    {
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role);

        $resolved = 0;
        DB::listen(function ($query) use (&$resolved) {
            // SetupGuide::page()'s count, which nothing else in the app asks for.
            if (str_contains($query->sql, 'as repeating')) {
                $resolved++;
            }
        });

        $this->get(route('sales'))->assertOk()->assertSee(route('home').'#setup-guide', false);
        $this->assertSame(0, $resolved, 'the line is there, and the guide was never resolved for it');

        $this->get(route('home'))->assertOk();
        $this->assertSame(1, $resolved, 'the dashboard draws it, and asks once');
    }

    /** The guide's name and an event's name are their owners' text, and one of them is hostile. */
    public function test_a_hostile_name_reaches_the_page_only_inside_the_json(): void
    {
        config(['app.hosted' => true]);
        [$user, $role] = $this->startGuide('venue', ['name' => 'Blue {{ 7*7 }} </script><b>Room']);
        $this->publishedEvent($role, ['name' => '</script><i>Jazz</i>']);
        // A second schedule, so the card's still version prints a suggestion row with a name.
        $this->createRole($user, 'talent', ['name' => 'Evil {{ 8*8 }} <u>Twin</u>']);

        $html = $this->get(route('home'))->assertOk()->getContent();

        preg_match('/<section id="setup-guide".*?<\/section>/s', $html, $host);

        $this->assertNotEmpty($host);
        $this->assertStringNotContainsString('7*7', $host[0], 'the guide\'s own name is only in the payload');
        // The other schedule's name IS in the still version now, as text: escaped, and inside a
        // host that carries v-pre, so nothing that mounts around it can compile the mustache.
        $this->assertStringContainsString('Evil {{ 8*8 }} &lt;u&gt;Twin&lt;/u&gt;', $host[0]);
        $this->assertStringNotContainsString('<u>Twin</u>', $html);
        $this->assertMatchesRegularExpression('/<section id="setup-guide"[^>]*\sv-pre[\s>]/', $host[0]);
        $this->assertStringNotContainsString('</script><b>', $html, 'and the payload cannot close its own script block');
        $this->assertStringNotContainsString('</script><i>', $html);

        $guide = $this->guideOn($html);

        $this->assertSame('Blue {{ 7*7 }} </script><b>Room', $guide['name']);
        $this->assertSame('</script><i>Jazz</i>', $guide['events'][0]['name']);
    }

    /** The ring is the count. Words say how close; nothing prints "2 of 3". */
    public function test_no_string_of_the_guide_prints_a_fraction(): void
    {
        foreach (array_keys(config('app.supported_languages')) as $language) {
            $strings = array_filter(
                require resource_path("lang/{$language}/messages.php"),
                fn ($key) => str_starts_with($key, 'setup_guide_'),
                ARRAY_FILTER_USE_KEY
            );

            $this->assertGreaterThan(60, count($strings), $language);

            foreach ($strings as $key => $text) {
                $this->assertDoesNotMatchRegularExpression('/\d/', $text, "{$language}: {$key}");
            }
        }
    }

    // ---- The endpoint ----------------------------------------------------------------------

    public function test_the_endpoint_does_nothing_for_someone_with_no_guide(): void
    {
        $user = $this->createOwner();
        $this->createRole($user);

        $this->actingAs($user);
        $this->post_(['action' => 'dismiss'])->assertOk();
        $this->post_(['action' => 'share'])->assertOk();

        $this->assertNull($this->stored($user), 'a request cannot start a guide that was never started');
    }

    public function test_it_records_each_answer_once_without_touching_updated_at(): void
    {
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role);

        DB::table('users')->where('id', $user->id)->update(['updated_at' => '2026-01-01 00:00:00']);

        $this->post_(['action' => 'share'])->assertOk();
        $first = $this->stored($user)['shared_at'];

        $this->travel(5)->minutes();
        $this->post_(['action' => 'share'])->assertOk();
        $this->post_(['action' => 'embed'])->assertOk();
        $this->post_(['action' => 'skip', 'step' => 'tickets'])->assertOk();

        $guide = $this->stored($user);

        $this->assertSame($first, $guide['shared_at'], 'a second copy keeps the first time');
        $this->assertNotEmpty($guide['embedded_at']);
        $this->assertSame(['tickets'], $guide['skipped']);

        $this->post_(['action' => 'unskip', 'step' => 'tickets'])->assertOk();

        $this->assertSame([], $this->stored($user)['skipped']);
        // The admin active-users metric reads this column; a setup widget must not move it.
        $this->assertSame('2026-01-01 00:00:00', DB::table('users')->where('id', $user->id)->value('updated_at'));
    }

    /**
     * "No tickets needed" is an answer, not a change of subject: the row keeps the wording it was
     * asked in, and Undo reopens the same question. It used to come back as the sign-up wording,
     * because whether the schedule can sell was only worked out while the step was open.
     */
    public function test_turning_the_tickets_step_down_does_not_change_what_it_was_asking(): void
    {
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role);

        $this->assertTrue($this->state($user)['can_sell']);

        $this->post_(['action' => 'skip', 'step' => 'tickets'])->assertOk();

        $state = $this->state($user);
        $this->assertTrue($state['steps']['tickets']['skipped']);
        $this->assertTrue($state['can_sell']);
    }

    public function test_it_takes_only_the_actions_and_steps_it_knows(): void
    {
        [$user] = $this->startGuide();

        $this->post_(['action' => 'explode'])->assertStatus(422);
        $this->post_(['action' => 'skip'])->assertStatus(422);
        $this->post_(['action' => 'skip', 'step' => 'event'])->assertStatus(422);
        $this->post_(['action' => 'skip', 'step' => '<script>'])->assertStatus(422);

        $this->assertArrayNotHasKey('skipped', $this->stored($user));
    }

    public function test_the_column_cannot_be_mass_assigned(): void
    {
        $user = $this->createOwner();
        $user->fill(['setup_guide' => ['role_id' => 1, 'started_at' => now()->toIso8601String()]]);

        $this->assertNull($user->setup_guide);
    }

    public function test_the_celebration_cannot_be_spent_before_the_schedule_is_live(): void
    {
        [$user, $role] = $this->startGuide();

        $this->post_(['action' => 'celebrated'])->assertOk();
        $this->assertArrayNotHasKey('celebrated_at', $this->stored($user));

        $this->publishedEvent($role);
        $this->post_(['action' => 'celebrated'])->assertOk();

        $this->assertNotEmpty($this->stored($user)['celebrated_at']);
    }

    public function test_a_finished_guide_stays_for_the_session_and_then_is_gone(): void
    {
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role);

        $this->post_(['action' => 'complete'])->assertOk();
        $this->assertArrayNotHasKey('completed_at', $this->stored($user), 'not finished yet');

        foreach (SetupGuide::SKIPPABLE as $step) {
            $this->post_(['action' => 'skip', 'step' => $step])->assertOk();
        }

        $this->post_(['action' => 'complete'])->assertOk();
        $this->assertNotEmpty($this->stored($user)['completed_at']);

        $page = $this->get(route('home'))->getContent();
        $guide = $this->guideOn($page);
        $this->assertTrue($guide['retired'], 'still there, to offer the next event');

        // A finished guide is a third the height of the working checklist. Holding the height
        // reserved for that left a blank band under its links.
        preg_match('/<section id="setup-guide"[^>]*>/', $page, $host);
        $this->assertStringContainsString('--sg-h: 0px; --sg-h-lg: 244px; --sg-h-xl: 244px', $host[0]);

        $this->flushSession();
        $this->actingAs($user->fresh());

        $this->assertNull($this->surfaceOf($this->get(route('home'))->getContent()), 'gone in the next session');
    }

    /**
     * The answer that finishes the guide records the finish, in the same request. The page sent
     * "complete" as a second request beside it; judged before the answer was stored, it recorded
     * nothing, and the next page played the finish (confetti and all) a second time.
     */
    public function test_the_answer_that_finishes_the_guide_records_the_finish(): void
    {
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role);

        $this->post_(['action' => 'skip', 'step' => 'events'])->assertOk();
        $this->post_(['action' => 'skip', 'step' => 'tickets'])->assertOk();
        $this->assertArrayNotHasKey('completed_at', $this->stored($user), 'one step is still open');

        $this->post_(['action' => 'share'])->assertOk();

        $this->assertNotEmpty($this->stored($user)['completed_at']);
        $this->assertTrue(
            $this->guideOn($this->get(route('home'))->getContent())['retired'],
            'so the next page shows it finished rather than finishing it again'
        );
    }

    /**
     * "For the session" is not an afternoon: SESSION_LIFETIME is a day of inactivity, so
     * somebody who opens the app every morning never leaves the session they finished in. A
     * finished guide can be dismissed, and goes by itself the same day.
     */
    public function test_a_finished_guide_can_be_dismissed_and_does_not_outstay_the_day(): void
    {
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role);

        foreach (SetupGuide::SKIPPABLE as $step) {
            $this->post_(['action' => 'skip', 'step' => $step])->assertOk();
        }

        $this->assertSame('section', $this->surfaceOf($this->get(route('home'))->getContent()));
        $this->get(route('sales'))->assertOk()->assertDontSee(route('home').'#setup-guide', false);

        $this->travel(SetupGuide::FINISHED_HOURS + 1)->hours();
        $this->assertNull($this->surfaceOf($this->get(route('home'))->getContent()), 'the same session, the next morning');

        $this->travelBack();
        $this->assertSame('section', $this->surfaceOf($this->get(route('home'))->getContent()));

        $this->post_(['action' => 'dismiss'])->assertOk();

        $this->assertNull($this->surfaceOf($this->get(route('home'))->getContent()));
        // dismissed_at is "hid a guide they had not finished": the growth export reports it so.
        $this->assertArrayNotHasKey('dismissed_at', $this->stored($user));
    }

    // ---- Going live ------------------------------------------------------------------------

    public function test_arriving_from_the_wizard_the_guide_speaks_instead_of_the_toast(): void
    {
        [$user, $role] = $this->startGuide();

        // The flash is still there for anything asserting on the redirect...
        $this->assertSame(__('messages.created_schedule'), session('message'));

        // ...and the page it lands on does not print it a second time.
        $form = $this->get(route('event.create', ['subdomain' => $role->subdomain]))->assertOk();

        $form->assertDontSee(__('messages.created_schedule'));
        $this->assertTrue($this->guideOn($form->getContent())['arrived']);
    }

    public function test_saving_an_event_lands_on_the_guides_panel(): void
    {
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role, ['name' => 'Friday Jazz Night']);

        $page = $this->withSession(['setup_guide_saved' => true])
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->getContent();

        $guide = $this->guideOn($page);

        $this->assertSame('panel', $this->surfaceOf($page));
        $this->assertTrue($guide['celebrate']);
        $this->assertTrue($guide['saved']);
    }

    /**
     * "Is on your page" is said of an event that is on the page. A draft saved on a live
     * schedule used to get the panel too, which then named the last PUBLIC event as the one just
     * added and took the toast that said the draft was saved.
     */
    public function test_a_draft_saved_on_a_live_schedule_is_not_answered_as_on_your_page(): void
    {
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role, ['name' => 'Already Public']);
        $this->post_(['action' => 'celebrated'])->assertOk();

        $when = now()->addDays(10)->format('Y-m-d').' 20:00:00';

        $draft = $this->postCreateEvent($user, $role, ['name' => 'Still A Draft', 'starts_at' => $when, 'is_draft' => 1]);
        $draft->assertRedirect();

        $this->assertNull(session('setup_guide_saved'));
        $this->assertSame('pill', $this->surfaceOf($this->get($draft->headers->get('Location'))->getContent()));

        $public = $this->postCreateEvent($user, $role, ['name' => 'Second Public', 'starts_at' => $when]);
        $public->assertRedirect();

        $page = $this->get($public->headers->get('Location'))->getContent();

        $this->assertSame('panel', $this->surfaceOf($page));
        $this->assertSame('Second Public', $this->guideOn($page)['landed']);
    }

    /**
     * The panel takes the toast's place only for the redirect that brought its event. It also
     * shows on any first visit to the Schedule tab after going live, and a toast arriving with
     * that visit is somebody else's news.
     */
    public function test_the_panel_only_speaks_for_the_toast_that_came_with_its_event(): void
    {
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role);
        $tab = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']);

        $page = $this->withSession(['message' => 'Unrelated toast 4711'])->get($tab)->assertOk();

        $this->assertSame('panel', $this->surfaceOf($page->getContent()), 'live and not yet celebrated');
        $page->assertSee('Unrelated toast 4711');

        $page = $this->withSession(['message' => 'Event toast 4712', 'setup_guide_saved' => true])->get($tab)->assertOk();

        $this->assertSame('panel', $this->surfaceOf($page->getContent()));
        $page->assertDontSee('Event toast 4712');
    }

    /**
     * The panel answers every event that lands while the page still wants events or has not been
     * shared - including the save that completes "add more events", whose button is "Copy link".
     * It names the event that landed, which need not be one of the three the strip shows.
     */
    public function test_the_panel_follows_each_event_until_the_page_is_shared(): void
    {
        [$user, $role] = $this->startGuide();
        $tab = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']);

        foreach (['One', 'Two', 'Three'] as $index => $name) {
            $this->publishedEvent($role, ['name' => $name, 'starts_at' => now()->addDays($index + 2)->format('Y-m-d H:i:s')]);
        }
        $this->post_(['action' => 'celebrated']);

        $this->assertSame('pill', $this->surfaceOf($this->get($tab)->getContent()), 'no event landed: the pill, not a panel');

        // A fourth, months away: on the page, not among the next three.
        $this->publishedEvent($role, ['name' => 'Far Off', 'starts_at' => now()->addDays(200)->format('Y-m-d H:i:s')]);

        $page = $this->withSession(['setup_guide_saved' => true])->get($tab)->getContent();
        $guide = $this->guideOn($page);

        $this->assertSame('panel', $this->surfaceOf($page));
        $this->assertSame('share', $guide['current']);
        $this->assertSame('Far Off', $guide['landed']);
        $this->assertNotContains('Far Off', array_column($guide['events'], 'name'));
        $this->assertSame(1, $guide['more']);

        $this->post_(['action' => 'share']);
        $this->publishedEvent($role, ['name' => 'Fifth']);

        $this->assertSame(
            'pill',
            $this->surfaceOf($this->withSession(['setup_guide_saved' => true])->get($tab)->getContent()),
            'shared: the page goes back to its own toast'
        );
    }

    /** Reached from the guest page, the guide and the draft panel: all three end here. */
    public function test_publishing_the_draft_that_takes_it_live_lands_on_the_schedule_tab(): void
    {
        [$user, $role] = $this->startGuide();
        $draft = $this->publishedEvent($role, ['is_draft' => true]);

        $response = $this->from(route('home'))
            ->post(route('event.publish', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($draft->id)]));

        $response->assertRedirect();
        $this->assertStringContainsString('/'.$role->subdomain.'/schedule', $response->headers->get('Location'));
        $this->assertSame('panel', $this->surfaceOf($this->get($response->headers->get('Location'))->getContent()));
    }

    public function test_publishing_without_a_guide_goes_back_where_it_came_from(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $draft = $this->publishedEvent($role, ['is_draft' => true]);

        $this->actingAs($user)->from(route('home'))
            ->post(route('event.publish', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($draft->id)]))
            ->assertRedirect(route('home'));
    }

    /** One panel: the import's own gains the strip, and the guide's does not also render. */
    public function test_an_import_is_answered_in_the_import_panel(): void
    {
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role);

        $page = $this->withSession(['events_imported' => ['count' => 1, 'names' => ['Test Event']]])
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->getContent();

        $this->assertSame('strip', $this->surfaceOf($page));
        $this->assertSame(1, substr_count($page, 'data-setup-guide='));
        $this->assertStringContainsString('data-setup-share="link"', $page);
    }

    // ---- One voice with the dashboard ------------------------------------------------------

    private function nextStepTypes(User $user): array
    {
        return array_column($this->actingAs($user)->get(route('home'))->assertOk()->viewData('nextStepItems')->all(), 'type');
    }

    public function test_the_dashboard_does_not_ask_what_the_guide_is_asking(): void
    {
        config(['app.hosted' => true]);
        [$user, $role] = $this->startGuide();

        $this->assertSame([], $this->nextStepTypes($user), 'before live: no "add your first event" row');

        $this->publishedEvent($role);

        $this->assertSame([], $this->nextStepTypes($user), 'after live: no tickets row');
    }

    /**
     * "No tickets needed" is an answer. It used to silence nothing outside the guide, and in one
     * card that read as the app not listening: the row asked again under the answer, and again
     * under "is set up". It is read from the guide's stored answer, so it holds after the guide
     * has ended, and nothing is copied into the dismissals table.
     */
    public function test_no_tickets_needed_is_an_answer_that_outlives_the_guide(): void
    {
        config(['app.hosted' => true]);
        [$user, $role] = $this->startGuide();
        // Far enough out to still be upcoming once the guide's 30 days are over.
        $this->publishedEvent($role, ['starts_at' => now()->addDays(90)->format('Y-m-d H:i:s')]);

        $this->post_(['action' => 'skip', 'step' => 'tickets'])->assertOk();
        $this->travel(SetupGuide::LIFETIME_DAYS + 1)->days();

        $this->assertNull($this->state($user), 'the guide is over');
        $this->assertSame([], $this->nextStepTypes($user), 'and its answer still stands');
        $this->assertSame(0, DismissedNextStep::count(), 'in the guide\'s column, not in the dismissals table');

        // The guide's Undo is the way back, and it is the same stored answer being read.
        $this->post_(['action' => 'unskip', 'step' => 'tickets'])->assertOk();

        $this->assertSame(['next_step_tickets'], $this->nextStepTypes($user));
    }

    /**
     * Hiding the guide used to release the rows it was holding back, so the thing just dismissed
     * was replaced on the spot by a panel asking the same. Hidden, it holds everything about its
     * schedule - and writes no dismissal, so no reminder email is silenced by hiding a widget,
     * and the schedule is suggested to like any other once the guide's time is up.
     */
    public function test_a_hidden_guide_holds_everything_about_its_schedule_and_dismisses_nothing(): void
    {
        config(['app.hosted' => true]);
        [$user, $role] = $this->startGuide();
        $other = $this->createRole($user, 'talent');

        $schedules = fn () => array_column(
            $this->actingAs($user)->get(route('home'))->assertOk()->viewData('nextStepItems')->all(),
            'dismiss_schedule'
        );

        $this->assertSame([UrlUtils::encodeId($other->id)], $schedules(), 'showing: only the other schedule asks');

        $this->post_(['action' => 'dismiss'])->assertOk();

        $this->assertSame([UrlUtils::encodeId($other->id)], $schedules(), 'hidden: its own row does not take its place');
        $this->assertSame(0, DismissedNextStep::count());

        $this->travel(SetupGuide::LIFETIME_DAYS + 1)->days();

        $this->assertEqualsCanonicalizing(
            [UrlUtils::encodeId($role->id), UrlUtils::encodeId($other->id)],
            $schedules(),
            'when its time is up the schedule is suggested to like any other'
        );
    }

    /**
     * The guide ticks its tickets step for an event that takes sign-ups; the suggestion does not
     * count sign-ups on a schedule that can sell. Held only while the step was OPEN, "add a
     * ticket type" came back as the guide's own line under the tick.
     */
    public function test_a_showing_guide_holds_its_tickets_row_whatever_its_step_says(): void
    {
        config(['app.hosted' => true]);
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role, ['rsvp_enabled' => true]);

        $this->assertTrue($this->state($user)['steps']['tickets']['done'], 'registration ticks the step');
        $this->assertSame([], $this->nextStepTypes($user));

        // The row itself exists: with the guide over, an ordinary schedule that can sell is
        // still asked for a ticket type.
        DB::table('users')->where('id', $user->id)->update(['setup_guide' => null]);

        $this->assertSame(['next_step_tickets'], $this->nextStepTypes($user->fresh()));
    }

    /** One card, one voice: "Add your next date" is the guide's "Add more events" said twice. */
    public function test_add_your_next_date_is_held_while_the_guide_asks_for_more_events(): void
    {
        config(['app.hosted' => true]);
        [$user, $role] = $this->startGuide();
        // On the page, and past: live for the guide, "no upcoming date" for the suggestions.
        $this->publishedEvent($role, ['starts_at' => now()->subDays(2)->format('Y-m-d H:i:s')]);

        $this->assertSame('events', $this->state($user)['current']);
        $this->assertSame([], $this->nextStepTypes($user));

        $this->post_(['action' => 'skip', 'step' => 'events'])->assertOk();

        $this->assertSame(['next_step_next_event'], $this->nextStepTypes($user), 'no longer the guide\'s question');
    }

    /**
     * With a guide the suggestions are IN its card: the one about its own schedule as a line of
     * the guide, the others as a band. No second panel is printed beside it.
     */
    public function test_the_card_carries_the_suggestions_split_by_schedule(): void
    {
        config(['app.hosted' => true]);
        [$user, $role] = $this->startGuide();
        $this->publishedEvent($role, ['starts_at' => now()->subDays(2)->format('Y-m-d H:i:s')]);
        $this->post_(['action' => 'skip', 'step' => 'events'])->assertOk();
        $other = $this->createRole($user, 'talent', ['name' => 'Sunday Sessions']);

        $html = $this->get(route('home'))->assertOk()->getContent();
        $guide = $this->guideOn($html);

        $this->assertSame('section', $this->surfaceOf($html));
        $this->assertSame(1, substr_count($html, 'data-setup-guide='), 'one card');
        $this->assertSame(['next_step_next_event'], array_column($guide['suggestions']['own'], 'type'));
        $this->assertSame(UrlUtils::encodeId($role->id), $guide['suggestions']['own'][0]['schedule']);
        $this->assertSame(['Sunday Sessions'], array_column($guide['suggestions']['others'], 'name'));
        $this->assertSame('S', $guide['suggestions']['others'][0]['initial']);

        // The still version's panel holds the OTHER schedule only: what its "Dismiss all" writes.
        $this->assertSame(1, substr_count($html, 'action="'.route('home.next_steps_dismiss').'"'));

        $this->post(route('home.next_steps_dismiss_all'))->assertRedirect();

        $this->assertSame(
            [$other->id],
            DismissedNextStep::where('user_id', $user->id)->pluck('role_id')->all(),
            'the still form leaves the showing guide\'s schedule alone'
        );
    }

    /**
     * "Turn off suggestions": the guide in every shape, the sidebar line, the wizard's ring and
     * every suggestion, now and later. And back.
     */
    public function test_turning_suggestions_off_removes_everything_and_on_brings_it_back(): void
    {
        config(['app.hosted' => true]);
        [$user, $role] = $this->startGuide();
        $this->createRole($user, 'talent');
        $line = route('home').'#setup-guide';
        $tab = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'followers']);

        $this->assertSame('section', $this->surfaceOf($this->get(route('home'))->getContent()));
        $this->assertSame('pill', $this->surfaceOf($this->get($tab)->getContent()));

        $this->postJson(route('home.suggestions'), ['on' => false])->assertOk();

        $this->assertNotNull(DB::table('users')->where('id', $user->id)->value('suggestions_off_at'));

        $home = $this->get(route('home'))->assertOk();

        $this->assertNull($this->surfaceOf($home->getContent()));
        $this->assertSame([], $home->viewData('nextStepItems')->all());
        $home->assertDontSee($line, false);
        $this->assertNull($this->surfaceOf($this->get($tab)->getContent()));

        // A schedule created while it is off is not suggested to either.
        $this->createRole($user, 'talent');
        $this->assertSame([], $this->get(route('home'))->viewData('nextStepItems')->all());

        $this->postJson(route('home.suggestions'), ['on' => true])->assertOk();

        $home = $this->get(route('home'))->assertOk();

        $this->assertSame('section', $this->surfaceOf($home->getContent()));
        $this->assertCount(2, $home->viewData('nextStepItems'));

        // The ring above the first schedule form is the guide's too. Somebody with no schedule
        // yet sees it, unless they turned suggestions off first.
        $newcomer = $this->createOwner();
        $form = route('new', ['type' => 'talent']);

        $this->actingAs($newcomer)->get($form)->assertOk()->assertSee(__('messages.setup_guide_two_steps'));

        SetupGuide::suggest($newcomer, false);

        $this->actingAs($newcomer->fresh())->get($form)->assertOk()->assertDontSee(__('messages.setup_guide_two_steps'));
    }

    /**
     * Nothing can stamp "celebrated" while every shape is gone. Without the guard, turning
     * suggestions back on weeks later threw confetti for a page that had been live all along -
     * and the toggle in Account settings is the usual way back, so it is tested through that.
     */
    public function test_turning_suggestions_back_on_does_not_celebrate_late(): void
    {
        [$user, $role] = $this->startGuide();
        $profile = ['name' => $user->name, 'email' => $user->email, 'timezone' => 'America/New_York', 'language_code' => 'en'];

        $this->patch(route('profile.update'), $profile + ['show_suggestions' => '0']);
        $this->assertFalse($user->fresh()->wantsSuggestions());

        // A save from a form without the field leaves the switch alone.
        $this->patch(route('profile.update'), $profile);
        $this->assertFalse($user->fresh()->wantsSuggestions());

        $this->publishedEvent($role);
        $this->actingAs($user->fresh());
        $this->patch(route('profile.update'), $profile + ['show_suggestions' => '1']);

        $this->assertTrue($user->fresh()->wantsSuggestions());
        $this->assertNotEmpty($this->stored($user)['celebrated_at']);

        $this->actingAs($user->fresh());
        $this->assertFalse($this->state($user->fresh())['celebrate']);
    }

    /**
     * A dismissal is permanent and silences that step's nudge mail. "Dismiss all" recomputes its
     * rows on the server, so without the same filter it would dismiss a row the guide was hiding:
     * an answer to a question the person was never shown.
     */
    public function test_dismiss_all_writes_nothing_for_a_row_the_guide_was_hiding(): void
    {
        config(['app.hosted' => true]);
        [$user, $role] = $this->startGuide();
        $other = $this->createRole($user, 'talent');

        $this->assertSame(['next_step_first_event'], $this->nextStepTypes($user), 'only the other schedule asks');

        $this->post(route('home.next_steps_dismiss_all'))->assertRedirect();

        $this->assertSame(
            [$other->id],
            DismissedNextStep::where('user_id', $user->id)->pluck('role_id')->all()
        );
    }
}
