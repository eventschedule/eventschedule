<?php

namespace Tests\Feature;

use App\Jobs\PublishCalendarHolds;
use App\Jobs\SendQueuedEmail;
use App\Mail\CalendarTakenOffMail;
use App\Models\CalendarImportHold;
use App\Models\CalendarSyncSetting;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\CalendarArrival;
use App\Utils\CalendarPrivacy;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What a schedule's owner sees of the events a calendar sync is keeping off the public page
 * (CalendarArrival): the notice on the Schedule tab, the review page (publish, keep hidden,
 * publish all), the setup guide and dashboard, and the one mail about events taken off the page.
 */
class ScheduleCalendarReviewPageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['app.hosted' => false]);

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['sync_direction' => 'from']);
    }

    private function held(string $name, string $reason = CalendarArrival::HOLD_REVIEW, ?string $was = null, array $attrs = []): Event
    {
        $event = $this->createEvent($this->role, $attrs + [
            'name' => $name,
            'is_draft' => true,
            'import_source' => Event::IMPORT_GOOGLE,
            'creator_role_id' => $this->role->id,
        ]);
        CalendarImportHold::create(['event_id' => $event->id, 'role_id' => $this->role->id, 'reason' => $reason, 'was_visibility' => $was, 'was_public' => $was !== null]);

        return $event;
    }

    private function review(array $query = [])
    {
        return $this->actingAs($this->owner)->get(route('role.calendar_review', ['subdomain' => $this->role->subdomain] + $query));
    }

    public function test_the_schedule_tab_says_what_waits_and_links_to_the_review_page(): void
    {
        $this->held('Open rehearsal');
        $this->held('Weekly sync', CalendarPrivacy::HOLD_MEETING_DETAILS, null, ['description' => 'Passcode: 123456']);

        $this->actingAs($this->owner)
            ->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->assertSee('id="calendar-waiting"', false)
            ->assertSee(trans_choice('messages.calendar_waiting_title', 2, ['count' => 2, 'provider' => 'Google Calendar']))
            ->assertSee(trans_choice('messages.calendar_waiting_held', 1, ['count' => 1]))
            ->assertSee(route('role.calendar_review', ['subdomain' => $this->role->subdomain]), false);
    }

    public function test_repeated_meetings_are_one_row_and_a_held_one_shows_why(): void
    {
        foreach (range(1, 3) as $week) {
            $this->held('Weekly standup', CalendarArrival::HOLD_REVIEW, null, ['starts_at' => now()->addWeeks($week)->format('Y-m-d 15:00:00')]);
        }
        $this->held('Board call', CalendarPrivacy::HOLD_MEETING_DETAILS, null, ['description' => "Join\nMeeting ID: 812 3456 7890"]);

        $this->review()
            ->assertOk()
            ->assertSeeText(trans_choice('messages.calendar_review_dates', 3, ['count' => 3]))
            ->assertSee('<mark class="spam-word" dir="ltr">Meeting ID: 812 3456 7890</mark>', false)
            ->assertSee(__('messages.calendar_publish_details_confirm'), false);
    }

    public function test_publishing_one_records_the_owners_decision_and_keeping_one_hidden_moves_it(): void
    {
        $publish = $this->held('Open rehearsal');
        $hide = $this->held('Dentist');

        $this->actingAs($this->owner)
            ->post(route('role.calendar_review.decide', ['subdomain' => $this->role->subdomain]), ['publish_one' => UrlUtils::encodeId($publish->id)])
            ->assertRedirect();
        $this->assertFalse($publish->fresh()->is_draft);
        $this->assertSame(CalendarArrival::DECISION_PUBLISHED, CalendarImportHold::where('event_id', $publish->id)->value('decision'));

        $this->actingAs($this->owner)
            ->post(route('role.calendar_review.decide', ['subdomain' => $this->role->subdomain]), ['hide_one' => UrlUtils::encodeId($hide->id)]);
        $this->assertTrue($hide->fresh()->is_draft);
        $this->assertSame(CalendarArrival::DECISION_HIDDEN, CalendarImportHold::where('event_id', $hide->id)->value('decision'));

        $this->review(['show' => 'hidden'])->assertSeeText('Dentist');
        $this->review(['show' => 'waiting'])->assertDontSeeText('Dentist');
        // A link that names no list opens the first one with something in it.
        $this->review()->assertSeeText('Dentist');
    }

    public function test_publish_all_queues_the_drafts_that_wait_only_for_a_look(): void
    {
        $plain = $this->held('Open rehearsal');
        $details = $this->held('Weekly sync', CalendarPrivacy::HOLD_MEETING_DETAILS);

        Queue::fake();
        $this->actingAs($this->owner)
            ->post(route('role.calendar_review.publish_all', ['subdomain' => $this->role->subdomain]))
            ->assertRedirect();
        Queue::assertPushed(PublishCalendarHolds::class, fn ($job) => $job->eventIds === [$plain->id]);
        $this->review()->assertSeeText(trans_choice('messages.calendar_publishing_now', 1, ['count' => 1]));

        (new PublishCalendarHolds($this->role->id, $this->owner->id, [$plain->id, $details->id]))->handle(app(\App\Services\EventLifecycleService::class));

        $this->assertFalse($plain->fresh()->is_draft);
        $this->assertTrue($details->fresh()->is_draft, 'meeting details are published one at a time');
    }

    public function test_only_the_owner_and_admins_review(): void
    {
        $this->held('Open rehearsal');
        $viewer = $this->createOwner();
        $this->role->users()->attach($viewer->id, ['level' => 'viewer']);

        $this->actingAs($viewer)
            ->get(route('role.calendar_review', ['subdomain' => $this->role->subdomain]))
            ->assertRedirect(route('home'));
    }

    public function test_an_event_taken_off_the_page_is_listed_and_its_owner_is_mailed_once(): void
    {
        $this->held('Ops daily call', CalendarPrivacy::HOLD_MEETING_DETAILS, 'public');

        Bus::fake([SendQueuedEmail::class]);
        CalendarArrival::recordPull($this->role, CalendarArrival::GOOGLE, ['created' => 0], false);
        CalendarArrival::recordPull($this->role, CalendarArrival::GOOGLE, ['created' => 0], false);

        Bus::assertDispatchedTimes(SendQueuedEmail::class, 1);
        $this->assertNotNull(CalendarSyncSetting::where('role_id', $this->role->id)->value('taken_off_notified_at'));

        $this->review(['show' => 'taken'])->assertSeeText('Ops daily call');
        $this->actingAs($this->owner)
            ->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']))
            ->assertSee(trans_choice('messages.calendar_taken_off_title', 1, ['count' => 1]));
    }

    public function test_the_mail_names_the_schedule_and_never_the_codes(): void
    {
        $mail = new CalendarTakenOffMail($this->role, $this->owner, 2, ['Ops daily call', 'Board call'], 'Outlook');

        $this->assertStringContainsString('"'.$this->role->name.'"', $mail->envelope()->subject);
        $html = $mail->render();
        $this->assertStringContainsString('Ops daily call', $html);
        $this->assertStringContainsString(route('role.calendar_review', ['subdomain' => $this->role->subdomain, 'show' => 'taken']), html_entity_decode($html));
    }

    public function test_the_setup_guide_and_dashboard_never_offer_a_held_draft_to_publish(): void
    {
        $held = $this->held('Re: [EXTERNAL] Appointment', CalendarPrivacy::HOLD_EMAIL_THREAD);

        $draft = (new \ReflectionMethod(\App\Utils\SetupGuide::class, 'draft'))->invoke(null, $this->role->fresh());
        $this->assertArrayHasKey('review', $draft);
        $this->assertStringNotContainsString('EXTERNAL', $draft['name']);

        // The dashboard's "Publish your draft event" would open the held one; it waits on the
        // review page instead, which the dashboard names.
        $this->actingAs($this->owner)->get(route('home'))
            ->assertOk()
            ->assertDontSee(__('messages.next_step_publish_event'))
            ->assertSee(trans_choice('messages.pending_action_calendar_waiting', 1, ['count' => 1]));
    }

    public function test_publishing_what_is_ticked_leaves_out_meeting_details(): void
    {
        $plain = $this->held('Open rehearsal');
        $details = $this->held('Weekly sync', CalendarPrivacy::HOLD_MEETING_DETAILS, null, ['description' => 'Passcode: 123456']);

        $this->actingAs($this->owner)
            ->post(route('role.calendar_review.decide', ['subdomain' => $this->role->subdomain]), [
                'action' => 'publish',
                'events' => [UrlUtils::encodeId($plain->id), UrlUtils::encodeId($details->id)],
            ])
            ->assertSessionHas('success', fn ($said) => str_contains($said, __('messages.calendar_publish_selected_left_out')));

        $this->assertFalse($plain->fresh()->is_draft);
        $this->assertTrue($details->fresh()->is_draft, 'published one at a time, after its own question');

        // On its own, it is published.
        $this->actingAs($this->owner)
            ->post(route('role.calendar_review.decide', ['subdomain' => $this->role->subdomain]), ['publish_one' => UrlUtils::encodeId($details->id)]);
        $this->assertFalse($details->fresh()->is_draft);
    }
}
