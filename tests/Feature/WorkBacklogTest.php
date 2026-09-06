<?php

namespace Tests\Feature;

use App\Console\Commands\Translate;
use App\Models\Newsletter;
use App\Models\TicketWaitlist;
use App\Services\TranslationQueue;
use App\Services\WorkBacklog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The "Work Waiting" card on /admin/queue, and the backlog figures behind it.
 *
 * The bug this feature exists for is not a crash: it is that /admin/queue could report a live
 * scheduler and an empty jobs table while thousands of rows sat untranslated, because app:translate
 * does its work inline and enqueues nothing. So most of what is pinned here is that a number MOVES
 * when the underlying work appears and drains - a panel whose figures are decorative would pass a
 * mere "the page renders" test perfectly.
 */
class WorkBacklogTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkBacklog::flush();
    }

    /**
     * The whole reason `pending` and `recheck` are separate figures.
     *
     * TranslationQueue::roles() has to select on a coarse prefilter for the four JSON columns,
     * because the translation lives under an `_en` sub-key INSIDE the value and SQL cannot read
     * it. That branch stays true for ever once the column is set, so before this split any
     * schedule with custom labels held `pending` above zero permanently - on a panel whose entire
     * job is to say whether the queue is draining. A number that never reaches zero reads as a
     * broken cron, which is worse than no number at all.
     */
    public function test_a_fully_translated_schedule_is_not_counted_as_pending_work(): void
    {
        $user = $this->createOwner();

        // Every translatable value filled in. Matches the selection query only through the coarse
        // custom_labels prefilter - the same row TranslateCommandTest proves is parked without an
        // AI call.
        $this->createRole($user, 'venue', [
            'name' => 'Fully Translated',
            'name_en' => 'Fully Translated EN',
            'language_code' => 'it',
            'translation_language_code' => 'en',
            'custom_labels' => ['our_sponsors' => ['value' => 'Sponsor', 'value_en' => 'Sponsor EN']],
        ]);

        $backlog = TranslationQueue::backlog();

        $this->assertSame(0, $backlog['roles']['pending'], 'A schedule with nothing left to translate is not pending work.');
        $this->assertSame(1, $backlog['roles']['recheck'], 'It is still a row the roles pass must open to be sure.');
    }

    /** A schedule with genuinely untranslated text is confirmed work, not a re-check. */
    public function test_a_schedule_with_untranslated_text_is_counted_as_pending(): void
    {
        $user = $this->createOwner();

        $this->createRole($user, 'venue', [
            'name' => 'Mezcaleria',
            'name_en' => null,
            'language_code' => 'es',
            'translation_language_code' => 'en',
        ]);

        $backlog = TranslationQueue::backlog();

        $this->assertSame(1, $backlog['roles']['pending']);
        $this->assertSame(0, $backlog['roles']['recheck']);
    }

    /**
     * The selection query must keep handing the command every row it might need to open.
     *
     * The `pending` figure is narrower than the command's own selection ON PURPOSE, and the risk
     * of that split is someone "fixing" the drift by narrowing selection to match - which would
     * stop the cron ever translating a JSON column again. That failure is silent: nothing errors,
     * the panel reads zero, and the sponsor names simply stay in the source language for ever.
     */
    public function test_the_selection_query_still_offers_json_only_rows_to_the_command(): void
    {
        $user = $this->createOwner();

        $role = $this->createRole($user, 'venue', [
            'name' => 'Already Done',
            'name_en' => 'Already Done EN',
            'language_code' => 'it',
            'translation_language_code' => 'en',
            'event_categories' => [['id' => 1, 'name' => 'Education', 'name_en' => null]],
        ]);

        $this->assertSame([$role->id], TranslationQueue::roles()->pluck('id')->all());
        $this->assertSame([], TranslationQueue::roles(null, true)->pluck('id')->all());
    }

    /** Every field the command translates must be a field the backlog can see. */
    public function test_the_text_field_list_matches_the_command(): void
    {
        $commandFields = new \ReflectionClassConstant(Translate::class, 'ROLE_TEXT_FIELDS');

        $this->assertSame(
            $commandFields->getValue(),
            TranslationQueue::ROLE_TEXT_FIELDS,
            'A field the cron translates but the backlog cannot count is a field that silently never appears as work.'
        );
    }

    /** A due newsletter is waiting work; one scheduled for later is not. */
    public function test_only_newsletters_that_are_actually_due_are_counted(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue');

        Newsletter::create([
            'role_id' => $role->id, 'user_id' => $user->id, 'subject' => 'Due',
            'body' => 'x', 'status' => 'scheduled', 'scheduled_at' => now()->subMinute(),
        ]);
        Newsletter::create([
            'role_id' => $role->id, 'user_id' => $user->id, 'subject' => 'Later',
            'body' => 'x', 'status' => 'scheduled', 'scheduled_at' => now()->addDay(),
        ]);
        Newsletter::create([
            'role_id' => $role->id, 'user_id' => $user->id, 'subject' => 'Sent',
            'body' => 'x', 'status' => 'sent', 'scheduled_at' => now()->subDay(),
        ]);

        $this->assertSame(1, $this->entryCount('newsletters'));
    }

    /** An expired waitlist hold is waiting work; one still inside its window is not. */
    public function test_only_expired_waitlist_holds_are_counted(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue');
        $event = $this->createEvent($role);

        $date = \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d');

        // event_date and subdomain are both NOT NULL with no default, and nothing fills them for
        // a hand-built row - the same shape as sales.subdomain.
        $hold = fn (string $who, string $status, $expires) => TicketWaitlist::create([
            'event_id' => $event->id, 'event_date' => $date, 'subdomain' => $role->subdomain,
            'name' => $who, 'email' => strtolower($who).'@gmail.com',
            'status' => $status, 'expires_at' => $expires,
        ]);

        $hold('A', 'notified', now()->subHour());
        $hold('B', 'notified', now()->addHour());
        $hold('C', 'waiting', now()->subHour());

        $this->assertSame(1, $this->entryCount('waitlist'));
    }

    /**
     * A backlog for work this install never performs must not render at all.
     *
     * A permanent "0 waiting" row for a switched-off feature is noise, and noise on a panel like
     * this one trains the operator to stop reading it.
     */
    public function test_a_disabled_feature_contributes_no_row(): void
    {
        $keys = collect(WorkBacklog::snapshot()['entries'])->pluck('key');

        $this->assertFalse(
            $keys->contains('federation'),
            'Federation is off by default, so it has no backlog to report.'
        );
        $this->assertTrue($keys->contains('newsletters'));
    }

    /** The snapshot is served from cache, and the card can force a fresh measurement. */
    public function test_the_snapshot_is_cached_until_flushed(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue');

        $this->assertSame(0, $this->entryCount('newsletters'));

        Newsletter::create([
            'role_id' => $role->id, 'user_id' => $user->id, 'subject' => 'Due',
            'body' => 'x', 'status' => 'scheduled', 'scheduled_at' => now()->subMinute(),
        ]);

        $this->assertSame(0, $this->entryCount('newsletters'), 'The cached measurement stands.');

        WorkBacklog::flush();

        $this->assertSame(1, $this->entryCount('newsletters'), 'Measuring again picks up the new row.');
    }

    /**
     * No recorded run means no rate. Inventing one would be worse than the silence: the estimate
     * a config calculation produces is wrong in both directions and cannot say which, because a
     * parked row costs no pause at all while a failing row clears nothing.
     */
    public function test_the_rate_is_absent_until_a_run_records_one(): void
    {
        Cache::forget(Translate::LAST_RUN_CACHE_KEY);

        $this->assertNull(WorkBacklog::translationRate());
        $this->assertNull(WorkBacklog::hoursToClear(500, null));
    }

    /** With a recorded run AND a healthy task, the rate and the estimate come off it. */
    public function test_a_recorded_run_produces_a_measured_rate_and_estimate(): void
    {
        $this->markTranslateTaskHealthy();

        Cache::put(Translate::LAST_RUN_CACHE_KEY, [
            'at' => now()->timestamp,
            'seconds' => 238.4,
            'translated' => 16,
            'failed' => 1,
            'parked' => 40,
            'budget_reached' => true,
        ], now()->addDay());

        $rate = WorkBacklog::translationRate();

        $this->assertNotNull($rate);
        $this->assertSame(16, $rate->translated);
        $this->assertSame(40, $rate->parked);
        $this->assertTrue($rate->budgetReached);
        // app-translate runs every fifteen minutes, so sixteen rows a run is sixty-four an hour.
        $this->assertEqualsWithDelta(64.0, $rate->perHour, 0.001);
        $this->assertEqualsWithDelta(2.0, WorkBacklog::hoursToClear(128, $rate), 0.001);
    }

    /**
     * A drained queue and a wholly failing run both report zero translated. Neither is an ETA, so
     * neither may produce one - "0 hours to clear" on a backlog of 500 is a lie in both cases.
     */
    public function test_no_estimate_is_offered_when_the_last_run_cleared_nothing(): void
    {
        Cache::put(Translate::LAST_RUN_CACHE_KEY, [
            'at' => now()->timestamp, 'seconds' => 240.0,
            'translated' => 0, 'failed' => 4, 'parked' => 0, 'budget_reached' => false,
        ], now()->addDay());

        $rate = WorkBacklog::translationRate();

        $this->assertNotNull($rate);
        $this->assertNull(WorkBacklog::hoursToClear(500, $rate));
    }

    /** The card is on the page, and it reports the work rather than a placeholder. */
    public function test_the_card_renders_the_real_backlog(): void
    {
        $user = $this->createOwner(admin: true);

        $role = $this->createRole($user, 'venue');
        Newsletter::create([
            'role_id' => $role->id, 'user_id' => $user->id, 'subject' => 'Due',
            'body' => 'x', 'status' => 'scheduled', 'scheduled_at' => now()->subMinute(),
        ]);

        WorkBacklog::flush();

        $response = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($user)
            ->get('/admin/queue');

        $response->assertOk();
        $response->assertSee(__('messages.work_waiting'));
        $response->assertSee(__('messages.backlog_newsletters'));
        $response->assertSee('process-scheduled-newsletters');
        $response->assertSee(__('messages.translation_rate_unknown'));
    }

    /** The measure-now button drops the cached snapshot. */
    public function test_remeasure_flushes_the_snapshot(): void
    {
        $user = $this->createOwner(admin: true);

        WorkBacklog::snapshot();
        $this->assertNotNull(Cache::get(WorkBacklog::CACHE_KEY));

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($user)
            ->post('/admin/queue/remeasure')
            ->assertRedirect(route('admin.queue'));

        $this->assertNull(Cache::get(WorkBacklog::CACHE_KEY));
    }

    /**
     * A rate is a record of what happened; an ETA is a claim about what will. When the task has
     * stopped, the first stays true and the second becomes reassurance at exactly the moment the
     * scheduler card above is reporting a failure.
     */
    public function test_no_estimate_is_offered_while_the_translation_task_is_unhealthy(): void
    {
        Cache::put(Translate::LAST_RUN_CACHE_KEY, [
            'at' => now()->timestamp, 'seconds' => 238.4,
            'translated' => 16, 'failed' => 0, 'parked' => 0, 'budget_reached' => false,
        ], now()->addDay());

        // No scheduled_task_runs row at all, so app-translate is not reporting as healthy.
        $rate = WorkBacklog::translationRate();

        $this->assertNotNull($rate);
        $this->assertFalse($rate->taskHealthy);
        $this->assertEqualsWithDelta(64.0, $rate->perHour, 0.001, 'The measured rate still stands.');
        $this->assertNull(WorkBacklog::hoursToClear(128, $rate), 'The forecast does not.');
    }

    /**
     * A per-container cache store is a CANDIDATE explanation for a missing rate, not a conclusion.
     * Most installs are one container on the file driver, where the cron and this page share a
     * cache perfectly well and the real answer is simply that no run has happened yet. Saying
     * otherwise sends the operator to fix a setting that is doing them no harm.
     */
    public function test_an_unshared_cache_is_only_blamed_with_evidence_the_command_ran(): void
    {
        Cache::forget(Translate::LAST_RUN_CACHE_KEY);

        // The test suite runs on the array store, which is per-process and so never "shared".
        $this->assertFalse(\App\Services\SchedulerHealth::cacheStoreIsShared());
        $this->assertSame('not_measured', WorkBacklog::rateUnavailableReason());

        // A completed run recorded in the DATABASE - a medium both containers share - beside a
        // missing cache summary is the contradiction only a per-container cache explains.
        $this->markTranslateTaskHealthy();

        $this->assertSame('unshared_cache', WorkBacklog::rateUnavailableReason());
    }

    /** Rows parked in a failure cooldown are still waiting, but reported apart from the rest. */
    public function test_rows_in_a_retry_cooldown_are_counted_separately(): void
    {
        $user = $this->createOwner();

        $role = $this->createRole($user, 'venue', [
            'name' => 'Mezcaleria', 'name_en' => null,
            'language_code' => 'es', 'translation_language_code' => 'en',
        ]);

        // At the ceiling and freshly attempted, which is exactly what applyRetryScope() excludes.
        $role->forceFill([
            'translation_attempts' => (int) config('usage.stuck_translation_attempts', 3),
            'last_translated_at' => now(),
        ])->saveQuietly();

        $backlog = TranslationQueue::backlog();

        $this->assertSame(1, $backlog['roles']['pending'], 'It is still work waiting.');
        $this->assertSame(1, $backlog['roles']['cooling_off'], 'But not work the next run will attempt.');
    }

    /**
     * A backlog must never become a row in the admin alert list.
     *
     * That class's docblock rules it out and gives the reason: its header total is a plain sum of
     * counts and its rows are things one admin can personally clear, so a backlog of thousands
     * would swamp a to-do list whose other rows are single digits and pin a permanent nav badge on
     * a queue that drains itself. Pinned here because "surface the backlog in the alerts too" is
     * the obvious next request, and the harm it does is gradual rather than loud.
     */
    public function test_no_backlog_becomes_an_admin_alert(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue');

        Newsletter::create([
            'role_id' => $role->id, 'user_id' => $user->id, 'subject' => 'Due',
            'body' => 'x', 'status' => 'scheduled', 'scheduled_at' => now()->subMinute(),
        ]);
        $this->createRole($user, 'venue', [
            'name' => 'Mezcaleria', 'name_en' => null,
            'language_code' => 'es', 'translation_language_code' => 'en',
        ]);

        \App\Services\AdminAlertService::flush();

        $types = \App\Services\AdminAlertService::items()->pluck('type');

        foreach (['work_waiting', 'translation_backlog', 'newsletters_due', 'backlog'] as $forbidden) {
            $this->assertFalse($types->contains($forbidden), "A backlog must not appear in the alert list as '{$forbidden}'.");
        }
    }

    /** A completed app-translate run, recorded where SchedulerHealth reads task health from. */
    private function markTranslateTaskHealthy(): void
    {
        \App\Models\ScheduledTaskRun::create([
            'name' => 'app-translate',
            'last_started_at' => now()->subMinute(),
            'last_finished_at' => now(),
            'last_status' => \App\Models\ScheduledTaskRun::STATUS_SUCCEEDED,
        ]);

        // state() reports every task as 'unknown' while the scheduler itself looks stalled.
        Cache::put('scheduler.last_run_at', now()->timestamp, now()->addDay());
        Cache::put('scheduler.last_run_at.cron', now()->timestamp, now()->addDays(7));
    }

    private function entryCount(string $key): int
    {
        return (int) collect(WorkBacklog::snapshot()['entries'])->firstWhere('key', $key)['count'];
    }
}
