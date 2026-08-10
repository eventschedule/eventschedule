<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\OnboardingNudge;
use App\Models\User;
use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The nudge emails real people who have not asked for anything, so the exclusions matter more
 * than the sends: an attendee told to "create your first schedule", or anyone emailed twice,
 * is worse than not sending at all.
 */
class OnboardingNudgeTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.hosted' => true]);
        Mail::fake();
    }

    /** A verified organizer-intent account with no schedule, signed up $hours ago. */
    private function stalled(int $hours, array $attrs = []): User
    {
        $user = User::factory()->create(array_merge([
            'email_verified_at' => now(),
            'signup_intent' => 'organizer',
            'is_subscribed' => true,
            'onboarding_nudge_stage' => 0,
        ], $attrs));

        $user->forceFill(['created_at' => now()->subHours($hours)])->save();

        return $user->fresh();
    }

    private function nudge(): void
    {
        $this->artisan('app:send-onboarding-nudges', ['--apply' => true])->assertExitCode(0);
    }

    public function test_it_sends_the_stage_matching_how_long_they_have_been_stalled(): void
    {
        $fresh = $this->stalled(2);      // past the 1h mark only
        $threeDays = $this->stalled(80); // past all three

        $this->nudge();

        $this->assertSame(1, $fresh->refresh()->onboarding_nudge_stage);
        // Not stage 1 - someone gone three days should not have to wait another three
        // days to receive the message that actually fits.
        $this->assertSame(3, $threeDays->refresh()->onboarding_nudge_stage);

        Mail::assertSent(OnboardingNudge::class, 2);
    }

    public function test_it_never_sends_the_same_stage_twice(): void
    {
        $user = $this->stalled(2);

        $this->nudge();
        $this->nudge();
        $this->nudge();

        Mail::assertSent(OnboardingNudge::class, 1);
        $this->assertSame(1, $user->refresh()->onboarding_nudge_stage);
    }

    public function test_it_advances_through_the_stages_as_time_passes(): void
    {
        $user = $this->stalled(2);

        $this->nudge();
        $this->assertSame(1, $user->refresh()->onboarding_nudge_stage);

        $this->travel(23)->hours();
        $this->nudge();
        $this->assertSame(2, $user->refresh()->onboarding_nudge_stage);

        $this->travel(48)->hours();
        $this->nudge();
        $this->assertSame(3, $user->refresh()->onboarding_nudge_stage);

        // Stage 3 is the last one, and it says so.
        $this->travel(30)->days();
        $this->nudge();
        $this->assertSame(3, $user->refresh()->onboarding_nudge_stage);
        Mail::assertSent(OnboardingNudge::class, 3);
    }

    public function test_it_leaves_activated_accounts_alone(): void
    {
        $user = $this->stalled(80);
        $this->createRole($user);

        $this->nudge();

        Mail::assertNothingSent();
        $this->assertSame(0, $user->refresh()->onboarding_nudge_stage);
    }

    public function test_attendees_are_never_told_to_create_a_schedule(): void
    {
        foreach (['follow', 'request', 'fan', 'claim'] as $intent) {
            $this->stalled(80, ['signup_intent' => $intent]);
        }

        $this->nudge();

        Mail::assertNothingSent();
    }

    public function test_unverified_unsubscribed_and_demo_accounts_are_skipped(): void
    {
        $this->stalled(80, ['email_verified_at' => null]);
        $this->stalled(80, ['is_subscribed' => false]);
        $this->stalled(80, ['email' => DemoService::DEMO_EMAIL]);

        $this->nudge();

        Mail::assertNothingSent();
    }

    public function test_an_account_younger_than_the_first_window_is_left_alone(): void
    {
        $this->stalled(0);

        $this->nudge();

        Mail::assertNothingSent();
    }

    public function test_nothing_is_sent_on_a_selfhosted_install(): void
    {
        config(['app.hosted' => false]);
        $this->stalled(80);

        $this->nudge();

        Mail::assertNothingSent();
    }

    public function test_dry_run_sends_nothing_and_records_nothing(): void
    {
        $user = $this->stalled(80);

        $this->artisan('app:send-onboarding-nudges')->assertExitCode(0);

        Mail::assertNothingSent();
        $this->assertSame(0, $user->refresh()->onboarding_nudge_stage);
    }

    /**
     * The one that matters most. `onboarding_nudge_stage` defaults to 0, so without an upper
     * bound on created_at the first --apply run matches every account ever created - and since
     * the stages run in descending order they would each get the STAGE 3 "last note" copy.
     */
    public function test_an_account_older_than_the_window_is_never_nudged(): void
    {
        $ancient = $this->stalled(24 * 400);

        $this->nudge();

        Mail::assertNothingSent();
        $this->assertSame(0, $ancient->refresh()->onboarding_nudge_stage);
    }

    /** The boundary, so the window cannot be quietly narrowed to nothing. */
    public function test_an_account_just_inside_the_window_is_still_nudged(): void
    {
        $recent = $this->stalled(24 * 13);

        $this->nudge();

        Mail::assertSent(OnboardingNudge::class, 1);
        $this->assertSame(3, $recent->refresh()->onboarding_nudge_stage);
    }

    /**
     * The dry run is what an operator reads to decide whether to enable this at all, so it has
     * to report the number of PEOPLE, not the number of stage queries they happen to match.
     */
    public function test_the_dry_run_counts_each_account_once(): void
    {
        $this->stalled(80); // matches the stage 3, 2 and 1 queries

        $this->artisan('app:send-onboarding-nudges')
            ->expectsOutputToContain('DRY RUN - 1 would be sent.')
            ->assertExitCode(0);
    }

    /** The 12 translations shipped with this command are worth nothing if it always sends English. */
    public function test_the_nudge_is_queued_in_the_recipients_language(): void
    {
        Queue::fake();

        $this->stalled(2, ['language_code' => 'fr']);

        $this->nudge();

        Queue::assertPushed(SendQueuedEmail::class, function ($job) {
            $locale = new \ReflectionProperty($job, 'locale');
            $locale->setAccessible(true);

            return $locale->getValue($job) === 'fr';
        });
    }

    /**
     * The stage is claimed before the send so two concurrent runners cannot both email the same
     * person - which means a failed send has to put it back, or that account's nudge is eaten.
     */
    public function test_a_failed_send_releases_the_claim(): void
    {
        $user = $this->stalled(2);

        Queue::shouldReceive('connection')->andThrow(new \RuntimeException('queue down'));

        $this->nudge();

        $this->assertSame(0, $user->refresh()->onboarding_nudge_stage);
    }

    /** The unsubscribe link has to actually work, or the mail is not sendable in good faith. */
    public function test_the_unsubscribe_link_verifies(): void
    {
        $user = $this->stalled(2);

        $html = (new OnboardingNudge($user, 1))->render();

        preg_match('~/user/unsubscribe\?[^"\']+~', $html, $m);
        $this->assertNotEmpty($m, 'the email must carry an unsubscribe link');

        $this->get(html_entity_decode($m[0]));

        $this->assertFalse((bool) $user->refresh()->is_subscribed);
    }
}
