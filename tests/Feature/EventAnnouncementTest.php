<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventAnnouncement;
use App\Models\NewsletterUnsubscribe;
use App\Models\Role;
use App\Models\RoleSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * app:send-event-announcements - the email the audience feature promises.
 *
 * Six surfaces tell a visitor they will hear about new events, including the double-opt-in
 * confirmation itself ("at most one email every few days, only when there is something new").
 * Before this command nothing sent it. These tests pin the three properties that stop it becoming
 * a mailshot, because every one of them is a way to email thousands of strangers by accident.
 */
class EventAnnouncementTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->role = $this->createRole($this->createOwner());
    }

    private function subscribe(string $email = 'fan@fans.test', bool $confirmed = true): RoleSubscriber
    {
        return RoleSubscriber::create([
            'role_id' => $this->role->id,
            'email' => $email,
            'name' => 'A Fan',
            'token' => RoleSubscriber::newToken(),
            'confirmed_at' => $confirmed ? now() : null,
        ]);
    }

    private function publish(array $attrs = [])
    {
        // creator_role_id is what the command filters on, and what getStartDateTime() needs to
        // resolve a schedule-local date rather than silently falling back to the app timezone.
        return $this->createEvent($this->role, $attrs + ['creator_role_id' => $this->role->id]);
    }

    /** Past the first-run watermark, so the schedule is eligible to announce. */
    private function baseline(?string $at = null): void
    {
        $this->role->forceFill(['last_announced_at' => $at ?: now()->subDays(30)])->save();
        $this->role->refresh();
    }

    private function runCommand(bool $apply = true): void
    {
        $this->artisan('app:send-event-announcements', $apply ? ['--apply' => true] : [])
            ->assertSuccessful();
    }

    private function announcements(): array
    {
        $found = [];
        foreach (Queue::pushed(SendQueuedEmail::class) as $job) {
            $p = new \ReflectionProperty($job, 'mailable');
            $p->setAccessible(true);
            $mailable = $p->getValue($job);

            if ($mailable instanceof EventAnnouncement) {
                $r = new \ReflectionProperty($job, 'recipient');
                $r->setAccessible(true);
                $found[] = ['to' => $r->getValue($job), 'mailable' => $mailable];
            }
        }

        return $found;
    }

    public function test_the_first_run_baselines_a_schedule_instead_of_mailing_its_back_catalogue(): void
    {
        // THE property that stops the very first deployment mailing every event this app has ever
        // stored. roles.last_announced_at starts NULL; a schedule seen for the first time is
        // stamped and sent nothing, because everything already on it predates the audience being
        // told they would hear about new events.
        $this->subscribe();
        $this->publish(['name' => 'An event from before the feature existed']);

        $this->assertNull($this->role->fresh()->last_announced_at);

        $this->runCommand();

        $this->assertEmpty($this->announcements(), 'the first pass must be a watermark, not a send');
        $this->assertNotNull($this->role->fresh()->last_announced_at);
    }

    public function test_an_event_published_after_the_baseline_is_announced(): void
    {
        $this->subscribe();
        $this->baseline();
        $this->publish(['name' => 'Opening Night']);

        $this->runCommand();

        $sent = $this->announcements();
        $this->assertCount(1, $sent);
        $this->assertSame('fan@fans.test', $sent[0]['to']);
        $this->assertSame(1, $sent[0]['mailable']->events->count());
    }

    public function test_several_events_become_one_digest_not_one_email_each(): void
    {
        // subscription_confirm_cadence promises "at most one email every few days". A schedule
        // that publishes a whole season in one sitting owes its audience one message.
        $this->subscribe();
        $this->baseline();
        $this->publish(['name' => 'Night One']);
        $this->publish(['name' => 'Night Two']);
        $this->publish(['name' => 'Night Three']);

        $this->runCommand();

        $sent = $this->announcements();
        $this->assertCount(1, $sent, 'one recipient must receive exactly one email');
        $this->assertSame(3, $sent[0]['mailable']->events->count());
    }

    public function test_the_cadence_floor_holds_a_second_announcement_back(): void
    {
        $this->subscribe();
        $this->baseline();
        $this->publish(['name' => 'Opening Night']);
        $this->runCommand();
        $this->assertCount(1, $this->announcements());

        // Publishing again immediately must not produce a second email: last_announced_at was
        // just stamped, and the floor is measured against it.
        $this->publish(['name' => 'A Late Addition']);
        $this->runCommand();

        $this->assertCount(1, $this->announcements(), 'the cadence floor was not honoured');
    }

    public function test_draft_and_unlisted_events_are_never_announced(): void
    {
        // Announcing either would broadcast an event the owner deliberately hid. is_draft also
        // covers the internal state, which Event::setVisibilityState() stores as a draft.
        $this->subscribe();
        $this->baseline();
        $this->publish(['name' => 'Still A Draft', 'is_draft' => true]);
        $this->publish(['name' => 'Unlisted', 'is_private' => true]);

        $this->runCommand();

        $this->assertEmpty($this->announcements());
    }

    public function test_a_past_event_is_not_announced(): void
    {
        // Back-filling last month's gigs is an ordinary thing to do and is not news.
        $this->subscribe();
        $this->baseline();
        $this->publish(['name' => 'Last Month', 'starts_at' => now()->subMonth()->format('Y-m-d H:i:s')]);

        $this->runCommand();

        $this->assertEmpty($this->announcements());
    }

    public function test_an_unconfirmed_subscriber_is_never_mailed(): void
    {
        // Confirmation is the proof of mailbox possession. Without this rule a stranger could
        // sign anybody up for a schedule's mail.
        $this->subscribe('unconfirmed@fans.test', confirmed: false);
        $this->baseline();
        $this->publish();

        $this->runCommand();

        $this->assertEmpty($this->announcements());
    }

    public function test_an_unsubscribed_address_is_never_mailed(): void
    {
        $this->subscribe('gone@fans.test');
        NewsletterUnsubscribe::create(['role_id' => $this->role->id, 'email' => 'gone@fans.test', 'unsubscribed_at' => now()]);
        $this->baseline();
        $this->publish();

        $this->runCommand();

        $this->assertEmpty($this->announcements());
    }

    public function test_an_owner_can_switch_announcements_off(): void
    {
        $this->subscribe();
        $this->baseline();
        $this->publish();
        $this->role->forceFill(['announce_new_events' => false])->save();

        $this->runCommand();

        $this->assertEmpty($this->announcements());
    }

    public function test_the_announcement_renders_and_carries_its_unsubscribe_machinery(): void
    {
        // The dispatch assertions above would all pass with a template that throws, so render it.
        // This also pins the two things that keep bulk mail out of a spam folder.
        $subscriber = $this->subscribe();
        $this->baseline();
        $event = $this->publish(['name' => 'Opening Night']);

        $unsubscribeUrl = route('subscriber.show_unsubscribe', ['token' => $subscriber->token]);
        $mailable = new EventAnnouncement($this->role, $subscriber, collect([$event]), $unsubscribeUrl);

        $rendered = $mailable->render();

        $this->assertStringContainsString('Opening Night', $rendered);
        $this->assertStringContainsString($unsubscribeUrl, $rendered);

        // messages.subscription_why_receiving shipped translated into all 13 languages and was
        // rendered nowhere. It is the line that turns "who is this?" into an unsubscribe rather
        // than a spam complaint.
        $this->assertStringContainsString(
            __('messages.subscription_why_receiving', ['schedule' => $this->role->name]),
            $rendered,
        );

        // RFC 8058. POST /sub/u/{token} has been CSRF-exempt for one-click since the feature
        // shipped, but nothing advertised it, so no mail client could offer the affordance.
        $headers = $mailable->headers();
        $this->assertStringContainsString($unsubscribeUrl, $headers->text['List-Unsubscribe']);
        $this->assertSame('List-Unsubscribe=One-Click', $headers->text['List-Unsubscribe-Post']);
    }

    public function test_the_subject_line_counts_the_events(): void
    {
        $subscriber = $this->subscribe();
        $event = $this->publish(['name' => 'Opening Night']);
        $second = $this->publish(['name' => 'Night Two']);

        $one = new EventAnnouncement($this->role, $subscriber, collect([$event]), 'https://x.test');
        $many = new EventAnnouncement($this->role, $subscriber, collect([$event, $second]), 'https://x.test');

        $this->assertStringContainsString($this->role->name, $one->envelope()->subject);
        $this->assertNotSame($one->envelope()->subject, $many->envelope()->subject);
        $this->assertStringContainsString('2', $many->envelope()->subject);
    }

    public function test_a_draft_published_after_the_watermark_is_announced(): void
    {
        // Hazard (d). newEventsFor() keyed on created_at, so the ordinary "write it up now,
        // publish on the day" workflow was invisible: the row was created before the watermark, so
        // it never qualified, however long after it went public. Event::boot() now stamps
        // published_at on the draft-to-public transition and the query reads
        // COALESCE(published_at, created_at).
        $this->subscribe();

        // Written as a draft a long time ago...
        $event = $this->publish(['is_draft' => true]);
        $event->forceFill(['created_at' => now()->subDays(60)])->saveQuietly();

        // ...the schedule was baselined after that...
        $this->baseline(now()->subDays(30)->toDateTimeString());

        // ...and it goes public today.
        $event->refresh();
        $event->is_draft = false;
        $event->save();

        $this->assertNotNull($event->fresh()->published_at, 'publishing must stamp published_at');

        $this->runCommand();

        $this->assertNotEmpty($this->announcements(), 'a draft published after the watermark must be announced');
    }

    public function test_publishing_twice_does_not_restamp_published_at(): void
    {
        // Unpublishing and republishing must not make an event new again, or it is announced twice.
        $event = $this->publish();
        $first = $event->fresh()->published_at;
        $this->assertNotNull($first);

        $event->is_draft = true;
        $event->save();
        $event->is_draft = false;
        $event->save();

        $this->assertEquals($first->toDateTimeString(), $event->fresh()->published_at->toDateTimeString());
    }

    /**
     * A schedule created through the UI must announce by default, like its column says.
     *
     * The Notifications tab is not gated on $role->exists, so its <x-toggle> renders on the CREATE
     * page too - against a `new Role` whose announce_new_events attribute does not exist. A toggle
     * reading null paints OFF while still submitting its companion hidden input at 0, and
     * RoleController::store() fills from $request->all(), so the migration's default(true) was
     * overwritten for every schedule made through the app. dueRoles() filters on this column, so
     * the effect was that no new schedule ever sent the email its subscribe panel promises guests.
     *
     * RoleController::create() already carries the identical guard for roles.event_layout, with a
     * comment describing this exact failure. This one was simply missed.
     */
    public function test_a_new_schedule_starts_with_announcements_on(): void
    {
        $owner = $this->createOwner('founder@venues.test');

        $response = $this->actingAs($owner)->get('/new/venue');

        $response->assertOk();

        // The mechanism is the rendered checkbox: it is what decides whether the browser submits
        // 1 or the hidden 0 beside it.
        $this->assertMatchesRegularExpression(
            '~name="announce_new_events"[^>]*value="1"[^>]*\bchecked\b~s',
            $response->getContent(),
            'the announce_new_events toggle must render ON for a new schedule, or the form posts '.
            'the hidden 0 and store() persists it over the column default'
        );

        // And the column default it mirrors really is on, so the two cannot drift apart.
        $this->assertTrue(
            (bool) $this->createRole($owner, 'venue')->fresh()->announce_new_events,
            'roles.announce_new_events must default to true at the database level'
        );
    }

    /**
     * The stamp is a TRANSITION, not a state - or every legacy event is announced as new.
     *
     * events.published_at has existed since 2024 and nothing ever wrote it, so every public row in
     * production holds NULL. A state test (`! is_draft && ! published_at`) therefore stamps the
     * first time ANY save touches such a row - and saving() runs before Eloquent's dirty check,
     * with the assignment itself dirtying the model, so it fires even on a save that would have
     * been a no-op. Event::updateRsvpSold() does exactly that on every RSVP, against precisely the
     * upcoming events newEventsFor() selects for.
     */
    public function test_an_unrelated_save_does_not_republish_a_long_public_event(): void
    {
        $this->subscribe();

        // A public event from months ago, in the state production is actually in: no published_at.
        $event = $this->publish();
        $event->forceFill([
            'created_at' => now()->subDays(90),
            'published_at' => null,
        ])->saveQuietly();

        $this->baseline(now()->subDays(30)->toDateTimeString());

        // Somebody RSVPs. updateRsvpSold() re-reads the row and save()s it.
        $event->refresh();
        $event->updateRsvpSold(substr((string) $event->starts_at, 0, 10), 1);

        $this->assertNull(
            $event->fresh()->published_at,
            'an RSVP is not a publication: stamping here makes a months-old event look new'
        );

        $this->runCommand();

        $this->assertEmpty(
            $this->announcements(),
            'an event that has been public for months must not be announced because somebody RSVPd'
        );
    }

    public function test_a_failed_dispatch_hands_the_window_back(): void
    {
        // Hazard (b). The watermark used to be stamped AFTER the dispatch loop with no try/catch,
        // so a throw part-way through left it untouched and the next run re-sent to everyone
        // already mailed - deterministically, not as a race. It is now claimed BEFORE sending and
        // handed back in the catch, so a failed run retries rather than double-sending.
        $this->subscribe();
        $this->publish();
        $this->baseline();
        $before = $this->role->fresh()->last_announced_at;

        // Time advances between the claim and the throw, deliberately, and it is hooked on the
        // claim's own UPDATE rather than on the queue: last_announced_at has second precision, so
        // a rollback naming now() instead of the timestamp actually claimed matches only while the
        // dispatch finishes inside the same second - which a test does and a real send does not.
        // Hooking the queue does not work here, because the failing call is not the one a mock
        // closure would run.
        \DB::listen(function ($query) {
            if (str_contains($query->sql, 'update `roles`') && str_contains($query->sql, 'last_announced_at')) {
                $this->travel(5)->seconds();
            }
        });

        Queue::shouldReceive('push')->andThrow(new \RuntimeException('mailer exploded'));
        Queue::shouldReceive('connection')->andReturnSelf();

        $this->runCommand();

        $this->assertEquals(
            $before->toDateTimeString(),
            $this->role->fresh()->last_announced_at->toDateTimeString(),
            'a failed dispatch must not advance the watermark'
        );
    }

    public function test_a_concurrent_run_cannot_claim_the_same_window(): void
    {
        // Hazard (c). routes/console.php and AppController::translateData() hold DIFFERENT mutexes,
        // so both rails can reach one schedule at once. claimWindow() is a conditional UPDATE
        // naming the value it read, so the second runner changes no row and skips. Simulated by
        // moving the watermark out from under the run, which is what the other rail doing its work
        // first looks like from here.
        $this->subscribe();
        $this->publish();
        $this->baseline();

        $role = $this->role;
        $moved = now()->subDay();

        // The other rail claims it between this run's read and its own claim.
        \Illuminate\Support\Facades\Event::listen('eloquent.retrieved: '.Role::class, function () use ($role, $moved) {
            \DB::table('roles')->where('id', $role->id)->update(['last_announced_at' => $moved]);
        });

        $this->runCommand();

        $this->assertSame([], $this->announcements(), 'the loser of a claim race must send nothing');
    }

    public function test_a_schedule_that_cannot_send_still_stamps(): void
    {
        // Hazard (e). A canSendAudienceMail() refusal used to `continue` without stamping, so the
        // same events were re-resolved, the same audience re-counted and the same refusal logged
        // on every run for ever - the defect this feature already fixed for scheduled newsletters.
        // app.is_testing OFF is load-bearing: canSendAudienceMail() short-circuits to true on it
        // before any of its real rules run, so without this the schedule sends and the test passes
        // for the wrong reason. AudienceMailGateTest's docblock says the same thing about itself.
        config(['app.hosted' => true, 'app.is_testing' => false]);

        // Unverified owner, audience over the unverified ceiling: the gate refuses.
        config(['usage.audience_mail_unverified_max_recipients' => 1]);
        $this->subscribe('a@fans.test');
        $this->subscribe('b@fans.test');
        $this->publish();
        $this->baseline();
        $before = $this->role->fresh()->last_announced_at;

        $this->runCommand();

        $this->assertSame([], $this->announcements(), 'a refused schedule must not send');
        $this->assertTrue(
            $this->role->fresh()->last_announced_at->gt($before),
            'a refusal must still advance the watermark, or it re-refuses for ever'
        );
    }

    public function test_an_unclaimed_schedule_is_not_announced_for(): void
    {
        // getGuestUrl() returns '' for an unclaimed schedule, which would render the email's
        // primary button with an empty href. It also has no owner who could have opted in.
        $this->subscribe();
        $this->publish();
        $this->baseline();

        \DB::table('roles')->where('id', $this->role->id)->update(['user_id' => null]);

        $this->runCommand();

        $this->assertSame([], $this->announcements());
    }

    /**
     * The command is on BOTH rails, and must stay on both.
     *
     * This assertion used to say the opposite: the command was hand-run while the seven hazards in
     * its class docblock were open. They are closed, so the risk has inverted - the repo rule is
     * that the two rails stay in sync, and a command registered on only one means an install using
     * the other rail silently never announces. CronRailSyncTest checks gate and cadence parity;
     * this checks the pair exists at all, so removing one is a deliberate edit rather than a
     * merge accident.
     *
     * Deliberately NOT gated on config('app.hosted') on either rail: the promise is made to a
     * guest, and on selfhost the subscribe panel is the only capture surface they ever see.
     */
    public function test_the_command_is_scheduled_on_both_rails(): void
    {
        foreach (['routes/console.php', 'app/Http/Controllers/AppController.php'] as $file) {
            $body = file_get_contents(base_path($file));

            $this->assertStringContainsString(
                "Artisan::call('app:send-event-announcements'", $body,
                "{$file} does not schedule the announcements; an install driven by that rail would "
                .'take subscriptions and never send anything'
            );

            $this->assertStringNotContainsString(
                "if (config('app.hosted')) {\n                    \\Artisan::call('app:send-event-announcements'", $body,
                "{$file} gates the announcements on hosted; selfhost is where the subscribe panel "
                .'is the only capture surface, so it must run there too'
            );
        }
    }

    /**
     * The watermark is operational state, not a setting, and must not be reachable from a form.
     *
     * RoleController::update() fills from $request->all() rather than validated(), and
     * RoleUpdateRequest has no rule for this key - so while it was in $fillable any editor could
     * POST a future timestamp and permanently silence their own announcements: dueRoles() reads
     * a negative diffInHours() as "sent recently" and skips forever, with nothing on screen to
     * explain it. Asserted through fill(), which is exactly the call the controller makes.
     */
    public function test_the_announcement_watermark_cannot_be_set_from_a_request(): void
    {
        $this->role->fill(['last_announced_at' => now()->addYears(5), 'announce_new_events' => false]);

        $this->assertNull($this->role->last_announced_at, 'the watermark must not be mass-assignable');
        $this->assertFalse($this->role->announce_new_events, 'the owner-facing toggle beside it must still fill');
    }

    public function test_a_dry_run_sends_nothing_and_stamps_nothing(): void
    {
        // The command is dry by default so a first deployment can be read before it mails anyone.
        $this->subscribe();
        $this->baseline();
        $this->publish();

        $this->runCommand(apply: false);

        $this->assertEmpty($this->announcements());
        $this->assertTrue($this->role->fresh()->last_announced_at->isBefore(now()->subDays(29)));
    }
}
