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

    /**
     * The command is hand-run for now, and must stay absent from BOTH scheduler rails.
     *
     * Asserted rather than commented, for the same reason ActivationNudgeTest asserts it: the repo
     * rule is that the two rails stay in sync, so the obvious "fix" for a missing registration is
     * to add one back - and adding one back here re-arms every defect listed in the class
     * docblock, including a watermark stamped after the dispatch loop and two rails holding
     * different mutexes with no claim between them. Neither can be undone once mail has left.
     */
    public function test_the_command_is_not_scheduled_on_either_rail(): void
    {
        foreach (['routes/console.php', 'app/Http/Controllers/AppController.php'] as $file) {
            $body = file_get_contents(base_path($file));

            $this->assertStringNotContainsString(
                "Artisan::call('app:send-event-announcements'", $body,
                "{$file} schedules the announcements; they are meant to be hand-run until the "
                .'blockers in the SendEventAnnouncements docblock are closed'
            );
        }
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
