<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\OnboardingNudge;
use App\Models\User;
use App\Services\DemoService;
use Carbon\Carbon;
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

    /**
     * --now skips the local-morning window for stages 2 and 3, which every test below except the
     * ones about that window would otherwise depend on the hour the suite happens to run at.
     */
    private function nudge(bool $now = true): void
    {
        $args = ['--apply' => true];
        if ($now) {
            $args['--now'] = true;
        }

        $this->artisan('app:send-onboarding-nudges', $args)->assertExitCode(0);
    }

    private function dryRun()
    {
        return $this->artisan('app:send-onboarding-nudges', ['--now' => true]);
    }

    public function test_it_sends_the_stage_matching_how_long_they_have_been_stalled(): void
    {
        $fresh = $this->stalled(2);      // past the 1h mark only
        $aWeek = $this->stalled(170);    // past all three

        $this->nudge();

        $this->assertSame(1, $fresh->refresh()->onboarding_nudge_stage);
        // Not stage 1 - someone gone a week should not have to wait another week to
        // receive the message that actually fits.
        $this->assertSame(3, $aWeek->refresh()->onboarding_nudge_stage);

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

        $this->travel(47)->hours();
        $this->nudge();
        $this->assertSame(2, $user->refresh()->onboarding_nudge_stage);

        $this->travel(120)->hours();
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

        $this->dryRun()->assertExitCode(0);

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
        $this->stalled(170); // matches the stage 3, 2 and 1 queries

        $this->dryRun()
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
     * The per-run cap must be a cap on PEOPLE, not on each stage query.
     *
     * Applied per stage, the accounts that missed the stage-3 cut fell straight into the stage-2
     * query in the same run, and the next lot into stage 1 - so those cohorts received all three
     * nudges, ending with "this is the last email we will send", within three hours of each
     * other. It also made the dry run report a third of what --apply would send.
     */
    public function test_one_run_nudges_each_account_at_most_once(): void
    {
        // A run budget of one, and three accounts all past the 168h mark so every one of them
        // matches every stage query. Applied per stage, accounts 2 and 3 would fall through to
        // the stage 2 and stage 1 queries in this same run.
        config(['usage.onboarding_nudge_batch' => 1]);

        foreach (range(1, 3) as $i) {
            $this->stalled(170, ['email' => "stalled{$i}@gmail.com"]);
        }

        $this->nudge();

        Mail::assertSent(OnboardingNudge::class, 1);

        // Every account that was reached got the stage that fits, and nothing got two.
        Mail::assertSent(OnboardingNudge::class, function ($mail) {
            $stage = new \ReflectionProperty($mail, 'stage');
            $stage->setAccessible(true);

            return $stage->getValue($mail) === 3;
        });

        $stages = User::whereIn('email', ['stalled1@gmail.com', 'stalled2@gmail.com', 'stalled3@gmail.com'])
            ->pluck('onboarding_nudge_stage')
            ->all();

        foreach ($stages as $stage) {
            $this->assertContains($stage, [0, 3],
                'an account is either untouched this run or has had exactly the stage that fits');
        }
    }

    /** The dry run has to report the number --apply would actually send. */
    public function test_the_dry_run_matches_what_apply_would_send(): void
    {
        config(['usage.onboarding_nudge_batch' => 2]);

        foreach (range(1, 3) as $i) {
            $this->stalled(170, ['email' => "stalled{$i}@gmail.com"]);
        }

        // Both numbers are the run budget, not a multiple of it.
        $this->dryRun()
            ->expectsOutputToContain('DRY RUN - 2 would be sent.')
            ->assertExitCode(0);

        $this->nudge();

        Mail::assertSent(OnboardingNudge::class, 2);
    }

    /**
     * A failed send must NOT put the stage back. The column is the only record that a stage was
     * delivered, so rewinding it lets a concurrent runner's successful send be re-sent.
     */
    public function test_a_failed_send_does_not_rewind_a_recorded_stage(): void
    {
        $user = $this->stalled(2);

        Queue::shouldReceive('connection')->andThrow(new \RuntimeException('queue down'));

        $this->nudge();

        $this->assertSame(1, $user->refresh()->onboarding_nudge_stage,
            'the claim stands: only ever moves forward');
    }

    /** The unsubscribe link has to actually work, or the mail is not sendable in good faith. */
    public function test_the_unsubscribe_link_verifies(): void
    {
        $user = $this->stalled(2);

        $html = (new OnboardingNudge($user, 1))->render();

        preg_match('~/user/unsubscribe\?[^"\']+~', $html, $m);
        $this->assertNotEmpty($m, 'the email must carry an unsubscribe link');

        $url = html_entity_decode($m[0]);

        // The GET only confirms - a mail scanner prefetching it must not opt anybody out.
        $this->get($url)->assertOk();
        $this->assertTrue((bool) $user->refresh()->is_subscribed);

        $this->post($url)->assertOk();
        $this->assertFalse((bool) $user->refresh()->is_subscribed);
    }

    //
    // Pacing: spread across the first week, stages 2 and 3 in the recipient's morning.
    //

    /** The boundaries, so the spacing cannot quietly drift back to three emails in three days. */
    public function test_the_stages_are_spread_across_the_first_week(): void
    {
        $this->freezeTime();

        $notYetTwo = $this->stalled(47, ['onboarding_nudge_stage' => 1]);
        $two = $this->stalled(48, ['onboarding_nudge_stage' => 1]);
        $notYetThree = $this->stalled(167, ['onboarding_nudge_stage' => 2]);
        $three = $this->stalled(168, ['onboarding_nudge_stage' => 2]);

        $this->nudge();

        $this->assertSame(1, $notYetTwo->refresh()->onboarding_nudge_stage);
        $this->assertSame(2, $two->refresh()->onboarding_nudge_stage);
        $this->assertSame(2, $notYetThree->refresh()->onboarding_nudge_stage);
        $this->assertSame(3, $three->refresh()->onboarding_nudge_stage);
    }

    /**
     * Outside the morning a due stage 2 or 3 waits - and the account must not be handed a LOWER
     * stage instead. Its stage is below theirs, so the stage 1 query matches it too: left unmarked,
     * an account that never got stage 1 was sent stage 1 at night and "last note" the next morning.
     */
    public function test_a_later_stage_waits_for_the_morning_without_falling_back_to_stage_1(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 03:00:00', 'UTC'));
        $user = $this->stalled(170, ['timezone' => 'UTC']);

        $this->nudge(now: false);

        Mail::assertNothingSent();
        $this->assertSame(0, $user->refresh()->onboarding_nudge_stage);

        $this->travelTo(Carbon::parse('2026-10-07 09:30:00', 'UTC'));
        $this->nudge(now: false);

        $this->assertSame(3, $user->refresh()->onboarding_nudge_stage);
        Mail::assertSent(OnboardingNudge::class, 1);
    }

    /** The morning is the recipient's, not the server's. */
    public function test_the_morning_is_in_the_recipients_timezone(): void
    {
        // 03:00 UTC is 11:00 in Singapore.
        $this->travelTo(Carbon::parse('2026-10-07 03:00:00', 'UTC'));
        $user = $this->stalled(50, ['timezone' => 'Asia/Singapore', 'onboarding_nudge_stage' => 1]);

        $this->nudge(now: false);

        $this->assertSame(2, $user->refresh()->onboarding_nudge_stage);
    }

    /** Stage 1 follows the signup session itself, whatever the hour. */
    public function test_stage_1_is_not_held_for_the_morning(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 03:00:00', 'UTC'));
        $user = $this->stalled(2, ['timezone' => 'UTC']);

        $this->nudge(now: false);

        $this->assertSame(1, $user->refresh()->onboarding_nudge_stage);
    }

    /** The window is what makes an hourly schedule reach each account at a sensible time. */
    public function test_the_command_is_scheduled_on_both_rails_without_now(): void
    {
        foreach (['routes/console.php', 'app/Http/Controllers/AppController.php'] as $file) {
            $body = file_get_contents(base_path($file));

            $this->assertStringContainsString("app:send-onboarding-nudges', ['--apply' => true]", $body, $file);
            $this->assertDoesNotMatchRegularExpression("/app:send-onboarding-nudges'[^;]*--now/", $body, $file);
        }
    }

    /** SendActivationNudges reads it to leave a few quiet days after an onboarding email. */
    public function test_the_claim_records_when_it_was_sent(): void
    {
        $this->freezeTime();
        $user = $this->stalled(2);

        $this->nudge();

        $this->assertEquals(now()->startOfSecond(), $user->refresh()->onboarding_nudge_sent_at);
    }

    //
    // What the mail says and where its button goes.
    //

    private function mail(User $user, int $stage): OnboardingNudge
    {
        return new OnboardingNudge($user->fresh(), $stage);
    }

    public function test_the_button_resumes_at_the_type_they_picked(): void
    {
        $picked = $this->stalled(2, ['pending_schedule_type' => 'venue']);
        $html = $this->mail($picked, 2)->render();
        $this->assertStringContainsString('/getting-started?type=venue', $html);

        $unpicked = $this->stalled(2);
        $html = $this->mail($unpicked, 2)->render();
        $this->assertStringContainsString('/getting-started', $html);
        $this->assertStringNotContainsString('/getting-started?type=', $html);
    }

    /** Stage 1 swaps "choose whether you perform, run a venue or curate" for the type they chose. */
    public function test_stage_1_talks_about_the_type_they_picked(): void
    {
        $choose = __('messages.onboarding_nudge_body_1');

        $html = $this->mail($this->stalled(2, ['pending_schedule_type' => 'venue']), 1)->render();
        $this->assertStringContainsString(e(__('messages.onboarding_nudge_type_venue')), $html);
        $this->assertStringNotContainsString(e($choose), $html);

        $html = $this->mail($this->stalled(2), 1)->render();
        $this->assertStringContainsString(e($choose), $html);
    }

    public function test_a_claimed_name_is_named_in_the_first_subject(): void
    {
        $user = $this->stalled(2, ['pending_schedule_name' => 'blue-room']);

        $this->assertSame('Finish setting up Blue Room', $this->mail($user, 1)->envelope()->subject);
        // Only the first: the later stages are about the account, not a page they are building.
        $this->assertSame(__('messages.onboarding_nudge_subject_2'), $this->mail($user, 2)->envelope()->subject);
    }

    /** No "Hola there," in a translated mail, and no email address used as a name. */
    public function test_a_missing_or_address_name_greets_without_one(): void
    {
        foreach (['', 'sam@example.com'] as $name) {
            $html = $this->mail($this->stalled(2, ['name' => $name]), 1)->render();

            $this->assertStringContainsString('Hello,', $html, var_export($name, true));
            $this->assertStringNotContainsString('Hello there', $html);
            $this->assertStringNotContainsString('Hello sam@', $html);
        }

        $html = $this->mail($this->stalled(2, ['name' => 'Sam Lee']), 1)->render();
        $this->assertStringContainsString('Hello Sam,', $html);
    }

    /** The founder's note, the reply invitation and our inbox, on eventschedule.com. */
    public function test_the_personal_parts_appear_on_the_nexus(): void
    {
        config(['app.is_nexus' => true, 'app.support_email' => 'contact@eventschedule.com']);
        $user = $this->stalled(2);

        $first = $this->mail($user, 1);
        $html = $first->render();
        $this->assertStringContainsString('/examples', $html);
        $this->assertStringContainsString('Hillel', $html);
        $this->assertSame('contact@eventschedule.com', $first->envelope()->replyTo[0]->address);

        $this->assertStringContainsString(e(__('messages.onboarding_nudge_reply_2')), $this->mail($user, 2)->render());

        // The last one asks what stopped them, and no longer points at examples.
        $html = $this->mail($user, 3)->render();
        $this->assertStringContainsString(e(__('messages.onboarding_nudge_reply_3')), $html);
        $this->assertStringNotContainsString('/examples', $html);
    }

    /** The plain-text part carries the same additions as the HTML one. */
    public function test_the_text_version_carries_them_too(): void
    {
        config(['app.is_nexus' => true]);
        $mail = $this->mail($this->stalled(2, ['pending_schedule_type' => 'talent']), 2);

        $text = view('emails.onboarding_nudge_text', $mail->content()->with)->render();

        $this->assertStringContainsString('/getting-started?type=talent', $text);
        $this->assertStringContainsString('/examples', $text);
        $this->assertStringContainsString(__('messages.onboarding_nudge_reply_2'), $text);
        $this->assertStringContainsString('Hillel', $text);
    }

    /**
     * An operator platform (IS_HOSTED=true, IS_NEXUS=false) runs this command too. It has no
     * /examples route, and our founder's name and inbox are not theirs to send. phpunit.xml forces
     * is_nexus on, so nothing else in the suite ever renders this branch.
     */
    public function test_an_operator_platform_gets_none_of_the_personal_parts(): void
    {
        config(['app.is_nexus' => false]);
        $user = $this->stalled(2);

        foreach ([1, 2, 3] as $stage) {
            $mail = $this->mail($user, $stage);
            $html = $mail->render();

            $this->assertStringNotContainsString('/examples', $html, "stage {$stage}");
            $this->assertStringNotContainsString('Hillel', $html, "stage {$stage}");
            $this->assertStringNotContainsString(e(__('messages.onboarding_nudge_reply_2')), $html);
            $this->assertStringNotContainsString(e(__('messages.onboarding_nudge_reply_3')), $html);
            $this->assertSame([], $mail->envelope()->replyTo, "stage {$stage}");
        }
    }

    /**
     * Every language actually DEFINES every key, and none of them is the English string.
     * Asserted against the files, because __() falls back to English for a missing key.
     */
    public function test_every_language_defines_its_own_copy(): void
    {
        $en = require resource_path('lang/en/messages.php');
        $keys = array_values(array_filter(array_keys($en), fn ($k) => str_starts_with($k, 'onboarding_nudge_')));

        $this->assertContains('onboarding_nudge_signoff', $keys, 'sanity: the new keys are in the list');

        foreach (array_keys(config('app.supported_languages')) as $lang) {
            $lines = require resource_path("lang/{$lang}/messages.php");

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $lines, "{$lang} is missing {$key}");

                if ($lang !== 'en') {
                    $this->assertNotSame($en[$key], $lines[$key], "{$lang}/{$key} is the English string copied over");
                }

                foreach (['schedule', 'app'] as $placeholder) {
                    if (str_contains($en[$key], ':'.$placeholder)) {
                        $this->assertStringContainsString(':'.$placeholder, $lines[$key], "{$lang}/{$key} lost :{$placeholder}");
                    }
                }
            }
        }
    }

    public function test_rtl_locales_mark_the_body_direction(): void
    {
        $user = $this->stalled(2, ['pending_schedule_name' => 'blue-room']);

        foreach (array_keys(config('app.supported_languages')) as $lang) {
            app()->setLocale($lang);
            $rendered = $this->mail($user, 1)->render();

            $this->assertSame(in_array($lang, ['ar', 'he']), str_contains($rendered, 'dir="rtl"'), "{$lang} direction");
            $this->assertStringContainsString('Blue Room', $rendered, "{$lang} lost the name");
        }
    }
}
