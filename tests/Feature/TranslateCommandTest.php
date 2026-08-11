<?php

namespace Tests\Feature;

use App\Models\EventPart;
use App\Models\EventRole;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Covers which rows `app:translate` picks up and in what order - the part that was broken when
 * schedules with a non-English translation target sat untranslated indefinitely.
 *
 * Everything here runs through --dry-run (or through a row that provably needs no AI call), so no
 * test in this file reaches the network. The Gemini call itself is not exercised.
 */
class TranslateCommandTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The command no-ops without a provider configured. Nothing here makes a call; the rows
        // under test either run under --dry-run or have nothing left to translate.
        config(['services.google.gemini_key' => 'test-key']);
    }

    private function dryRun(array $options = []): string
    {
        Artisan::call('app:translate', array_merge(['--dry-run' => true], $options));

        return Artisan::output();
    }

    /** @return int[] ids listed for a pass, in the order the command would process them */
    private function selectedIds(string $output, string $pass): array
    {
        preg_match_all('/\[dry-run\] '.preg_quote($pass, '/').' #(\d+)/', $output, $matches);

        return array_map('intval', $matches[1]);
    }

    public function test_never_translated_schedules_are_processed_before_longest_waiting_ones(): void
    {
        $user = $this->createOwner();

        // Created first (lowest id) but already attempted, so an id ordering would put it first.
        $attempted = $this->createRole($user, 'venue', [
            'name' => 'Attempted',
            'language_code' => 'it',
            'translation_language_code' => 'en',
            'last_translated_at' => now()->subHour(),
        ]);

        $waitingLonger = $this->createRole($user, 'venue', [
            'name' => 'Waiting Longer',
            'language_code' => 'it',
            'translation_language_code' => 'en',
            'last_translated_at' => now()->subDays(3),
        ]);

        $neverTried = $this->createRole($user, 'venue', [
            'name' => 'Never Tried',
            'language_code' => 'it',
            'translation_language_code' => 'en',
            'last_translated_at' => null,
        ]);

        $ids = $this->selectedIds($this->dryRun(), 'roles');

        // Never-attempted first, then longest-waiting. Under the old `orderBy('id')` this was
        // exactly reversed, which is how the newest schedules were never reached.
        $this->assertSame([$neverTried->id, $waitingLonger->id, $attempted->id], $ids);
    }

    public function test_targeting_a_schedule_includes_its_own_events(): void
    {
        $user = $this->createOwner();
        $venue = $this->createRole($user, 'venue', [
            'language_code' => 'en',
            'translation_language_code' => 'he',
        ]);
        $event = $this->createEvent($venue, ['name' => 'Shabbat Eikev']);

        $output = $this->dryRun(['--subdomain' => $venue->subdomain]);

        // The role-scoped run used to skip the events pass entirely, so there was no way to fix a
        // single schedule by hand.
        $this->assertSame([$event->id], $this->selectedIds($output, 'events'));
    }

    public function test_unknown_subdomain_fails_without_translating_anything(): void
    {
        $exitCode = Artisan::call('app:translate', ['--dry-run' => true, '--subdomain' => 'no-such-schedule']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('No schedule found with subdomain', Artisan::output());
    }

    public function test_schedule_needing_only_category_translation_is_selected(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', [
            'name' => 'Torah Learning Center',
            'name_en' => 'Already Translated',
            'language_code' => 'en',
            'translation_language_code' => 'he',
            'event_categories' => [
                ['id' => 1, 'name' => 'Education', 'name_en' => null],
            ],
        ]);

        // event_categories is translated by the command but was missing from its selection query,
        // so a schedule whose only untranslated content was its category names never got picked up.
        $this->assertSame([$role->id], $this->selectedIds($this->dryRun(), 'roles'));
    }

    public function test_schedule_with_nothing_left_to_translate_is_parked_without_an_ai_call(): void
    {
        $user = $this->createOwner();

        // Matches the selection query only through the coarse custom_labels prefilter, which stays
        // true forever once the column is set. Every translatable value is already filled in, so
        // reaching an AI call here would be the bug.
        $role = $this->createRole($user, 'venue', [
            'name' => 'Fully Translated',
            'name_en' => 'Fully Translated EN',
            'language_code' => 'it',
            'translation_language_code' => 'en',
            'custom_labels' => ['our_sponsors' => ['value' => 'Sponsor', 'value_en' => 'Sponsor EN']],
            'last_translated_at' => null,
        ]);

        $this->assertSame([$role->id], $this->selectedIds($this->dryRun(), 'roles'));

        Artisan::call('app:translate');

        $role->refresh();

        // Parked: the timestamp moves so fair ordering stops putting it ahead of real work, but
        // nothing was attempted and nothing was counted against it.
        $this->assertNotNull($role->last_translated_at);
        $this->assertSame(0, (int) $role->translation_attempts);
        $this->assertSame('Fully Translated EN', $role->name_en);
        $this->assertSame('Sponsor EN', $role->custom_labels['our_sponsors']['value_en']);
    }

    /**
     * A throwing AI call used to leave the row completely unstamped.
     *
     * GeminiUtils returns null for quota and 503 but RETHROWS a safety block, an INTERNAL, a
     * malformed body and any non-quota 4xx - all already-billed calls - and the per-item catch
     * only logged. That left translation_attempts at 0 and last_translated_at NULL, so
     * selectIds()'s "never translated first" ordering re-selected and re-bought the same row on
     * every run for ever, invisible to both the retry ceiling and the admin stuck-records panel.
     *
     * The catch now routes through recordFailure(); this pins its contract. (The four catch
     * blocks that call it are wired by inspection - the provider is raw curl, so a test cannot
     * drive a real throw without making a network call.)
     */
    public function test_a_thrown_translation_stamps_the_row_and_keeps_what_was_paid_for(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', [
            'name' => 'Mezcaleria',
            'language_code' => 'es',
            'translation_language_code' => 'en',
        ]);

        // What the loop holds in memory when a later field throws: one field already translated
        // and paid for, nothing persisted yet.
        $role->name_en = 'Mezcal Bar';

        $command = new \App\Console\Commands\Translate;
        $method = new \ReflectionMethod($command, 'recordFailure');
        $method->setAccessible(true);
        $method->invoke($command, $role, $role->id);

        $fresh = $role->fresh();

        $this->assertSame(1, (int) $fresh->translation_attempts, 'the failure has to count towards the retry ceiling');
        $this->assertNotNull($fresh->last_translated_at, 'an unstamped row sorts first on every subsequent run, for ever');
        $this->assertSame('Mezcal Bar', $fresh->name_en, 'a field that translated before the throw was already billed');
    }

    /** The consequence that costs money: a stamped failure stops being re-selected immediately. */
    public function test_a_stamped_failure_is_not_re_selected_on_the_next_run(): void
    {
        $user = $this->createOwner();

        // One short of the ceiling, so it is THIS failure being counted that parks the row.
        // Starting at the ceiling would make the test pass with or without the fix.
        $role = $this->createRole($user, 'venue', [
            'name' => 'Mezcaleria',
            'language_code' => 'es',
            'translation_language_code' => 'en',
            'translation_attempts' => (int) config('usage.stuck_translation_attempts') - 1,
        ]);

        $this->assertSame([$role->id], $this->selectedIds($this->dryRun(), 'roles'), 'precondition: it is selectable now');

        $command = new \App\Console\Commands\Translate;
        $method = new \ReflectionMethod($command, 'recordFailure');
        $method->setAccessible(true);
        $method->invoke($command, $role, $role->id);

        $this->assertSame([], $this->selectedIds($this->dryRun(), 'roles'));
    }

    /**
     * The two entry points must serialise on a lock that covers app:translate and NOTHING ELSE.
     *
     * translate_data_lock is held by AppController::translateData() around its whole
     * once-a-minute chain - newsletters, queue:work, every reminder - so using it here would mean
     * a 240s translation stalls all of that for minutes at a time, on exactly the installs that
     * run both entry points and therefore need the mutex in the first place.
     */
    public function test_the_two_translate_entry_points_share_a_translate_only_lock(): void
    {
        $console = file_get_contents(base_path('routes/console.php'));
        $controller = file_get_contents(app_path('Http/Controllers/AppController.php'));

        $this->assertStringContainsString("Cache::lock('app_translate_lock'", $console);
        $this->assertStringContainsString("Cache::lock('app_translate_lock'", $controller);

        // The scheduler must not reach for the broad one.
        $schedulerBlock = substr($console, strpos($console, "name('app-translate')") - 1200, 1200);
        $this->assertStringNotContainsString("Cache::lock('translate_data_lock'", $schedulerBlock,
            'the 15-minute translate task must not hold the lock that guards the per-minute chain');
    }

    public function test_repeatedly_failing_schedule_is_skipped_inside_its_cooldown(): void
    {
        $user = $this->createOwner();
        $this->createRole($user, 'venue', [
            'name' => 'Recently Failed',
            'language_code' => 'it',
            'translation_language_code' => 'en',
            'translation_attempts' => config('usage.stuck_translation_attempts'),
            'last_translated_at' => now()->subMinutes(5),
        ]);

        $this->assertSame([], $this->selectedIds($this->dryRun(), 'roles'));
    }

    public function test_repeatedly_failing_schedule_is_retried_once_past_the_cooldown(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', [
            'name' => 'Failed Long Ago',
            'language_code' => 'it',
            'translation_language_code' => 'en',
            'translation_attempts' => config('usage.stuck_translation_attempts'),
            'last_translated_at' => now()->subHours((int) config('usage.translation_retry_after_hours') + 1),
        ]);

        // Without the cooldown a schedule that hit the attempt threshold - which a quota window or
        // a run of API timeouts is enough to do - stayed frozen until someone edited it by hand.
        $this->assertSame([$role->id], $this->selectedIds($this->dryRun(), 'roles'));
    }

    public function test_force_ignores_the_cooldown(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', [
            'name' => 'Recently Failed',
            'language_code' => 'it',
            'translation_language_code' => 'en',
            'translation_attempts' => config('usage.stuck_translation_attempts'),
            'last_translated_at' => now()->subMinutes(5),
        ]);

        $this->assertSame([$role->id], $this->selectedIds($this->dryRun(['--force' => true]), 'roles'));
    }

    public function test_schedule_whose_target_matches_its_language_is_never_selected(): void
    {
        $user = $this->createOwner();
        $this->createRole($user, 'venue', [
            'name' => 'Monolingual',
            'language_code' => 'en',
            'translation_language_code' => 'en',
        ]);

        $this->assertSame([], $this->selectedIds($this->dryRun(), 'roles'));
    }

    public function test_events_pass_ignores_events_whose_schedules_want_no_translation(): void
    {
        $user = $this->createOwner();

        $monolingual = $this->createRole($user, 'venue', [
            'language_code' => 'en',
            'translation_language_code' => 'en',
        ]);
        $this->createEvent($monolingual, ['name' => 'Untranslatable']);

        $translating = $this->createRole($user, 'venue', [
            'language_code' => 'en',
            'translation_language_code' => 'he',
        ]);
        $wanted = $this->createEvent($translating, ['name' => 'Needs Hebrew']);

        // The pass used to load every event on the platform that had ever lacked a translation.
        $this->assertSame([$wanted->id], $this->selectedIds($this->dryRun(), 'events'));
    }

    public function test_scheduled_runs_are_budgeted_but_hand_run_schedules_are_not(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', [
            'language_code' => 'it',
            'translation_language_code' => 'en',
        ]);

        $this->assertStringContainsString('Time budget: 240s', $this->dryRun());

        // A hand-run single schedule should finish the job rather than stop at the cron's budget.
        $this->assertStringNotContainsString('Time budget', $this->dryRun(['--subdomain' => $role->subdomain]));
    }

    public function test_explicit_max_seconds_overrides_the_default(): void
    {
        $this->assertStringContainsString('Time budget: 30s', $this->dryRun(['--max-seconds' => 30]));
        $this->assertStringNotContainsString('Time budget', $this->dryRun(['--max-seconds' => 0]));
    }

    public function test_command_no_ops_without_an_ai_provider(): void
    {
        config(['services.google.gemini_key' => null, 'services.openai.api_key' => null]);

        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', [
            'name' => 'Waiting',
            'language_code' => 'it',
            'translation_language_code' => 'en',
        ]);

        Artisan::call('app:translate');

        $this->assertStringContainsString('No AI API key found', Artisan::output());
        $this->assertNull(Role::find($role->id)->last_translated_at);
    }

    public function test_rows_blanked_to_empty_strings_are_invisible_to_every_pass(): void
    {
        $user = $this->createOwner();
        $venue = $this->createRole($user, 'venue', [
            'language_code' => 'en',
            'translation_language_code' => 'he',
        ]);

        // What the old code wrote whenever an event's source language equalled its target. Not NULL,
        // so `whereNull('name_en')` cannot see it - this is why --reset has to exist.
        $this->createEvent($venue, ['name' => 'Shabbat Eikev', 'name_en' => '']);

        $this->assertSame([], $this->selectedIds($this->dryRun(['--subdomain' => $venue->subdomain]), 'events'));
    }

    public function test_reset_refuses_to_run_without_a_target(): void
    {
        $exitCode = Artisan::call('app:translate', ['--reset' => true]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('--reset needs a target', Artisan::output());
    }

    public function test_reset_dry_run_reports_without_destroying_anything(): void
    {
        $user = $this->createOwner();
        $venue = $this->createRole($user, 'venue', [
            'language_code' => 'en',
            'translation_language_code' => 'he',
            'name_en' => 'stale translation',
        ]);

        $output = $this->dryRun(['--reset' => true, '--subdomain' => $venue->subdomain]);

        $this->assertStringContainsString('[dry-run] --reset would discard', $output);
        $this->assertSame('stale translation', $venue->fresh()->name_en);
    }

    public function test_reset_clears_stale_translations_and_attempt_counters(): void
    {
        $user = $this->createOwner();

        // Monolingual on purpose: every pass short-circuits on source == target, so the whole run
        // is network-free and what remains is purely the reset's own effect.
        $venue = $this->createRole($user, 'venue', [
            'language_code' => 'en',
            'translation_language_code' => 'en',
            'name_en' => 'stale translation',
        ]);
        $event = $this->createEvent($venue, [
            'name' => 'Shabbat Eikev',
            'name_en' => '',
            'translation_attempts' => 3,
        ]);

        Artisan::call('app:translate', ['--reset' => true, '--subdomain' => $venue->subdomain]);

        $this->assertStringContainsString('Reset translations for schedule', Artisan::output());
        // The roles pass never touches a monolingual schedule, so a NULL here is the reset's doing.
        $this->assertNull($venue->fresh()->name_en);
        // The events pass re-blanks name_en on the same run (source == target), but it never touches
        // the counter - so this is what proves the event rows were reset too.
        $this->assertSame(0, (int) $event->fresh()->translation_attempts);
    }

    public function test_an_unresolvable_id_fails_instead_of_running_every_schedule(): void
    {
        $user = $this->createOwner();
        $this->createRole($user, 'venue', [
            'name' => 'Would Be Translated',
            'language_code' => 'it',
            'translation_language_code' => 'en',
        ]);

        foreach ([['--role_id' => 'garbage'], ['--event_id' => 'garbage']] as $options) {
            $exitCode = Artisan::call('app:translate', array_merge(['--dry-run' => true], $options));
            $output = Artisan::output();

            $this->assertSame(1, $exitCode);
            $this->assertStringContainsString('Could not resolve', $output);
            // The real damage of the old behaviour: a typo silently fell through to a full run.
            $this->assertStringNotContainsString('[dry-run] roles', $output);
        }
    }

    /** A venue that wants a translation, so its events reach the events pass at all. */
    private function translatingVenue(): Role
    {
        return $this->createRole($this->createOwner(), 'venue', [
            'language_code' => 'en',
            'translation_language_code' => 'he',
        ]);
    }

    public function test_recurring_events_are_kept_however_old_their_first_occurrence_is(): void
    {
        $venue = $this->translatingVenue();

        // A recurring series has no end date SQL can read and its starts_at is the FIRST occurrence,
        // never advanced - so a weekly class that began months ago still runs every week. Filtering
        // on starts_at alone would silently stop translating nearly every recurring event.
        $weekly = $this->createRecurringEvent($venue, [
            'name' => 'Gemara Masechet Brachot',
            'starts_at' => now()->subMonths(5)->format('Y-m-d H:i:s'),
        ]);

        $this->assertSame([$weekly->id], $this->selectedIds($this->dryRun(), 'events'));
    }

    public function test_events_that_are_over_are_not_selected(): void
    {
        $venue = $this->translatingVenue();
        $this->createEvent($venue, [
            'name' => 'Last Month Concert',
            'starts_at' => now()->subMonth()->format('Y-m-d H:i:s'),
            'duration' => 2,
        ]);

        $this->assertSame([], $this->selectedIds($this->dryRun(), 'events'));
    }

    public function test_an_event_earlier_today_is_still_selected(): void
    {
        $venue = $this->translatingVenue();

        // starts_at is a naive datetime and schedules span roughly +/-14h of timezone, so the cutoff
        // has a day of slack. An event whose start time passed an hour ago must not drop out.
        $earlier = $this->createEvent($venue, [
            'name' => 'This Morning',
            'starts_at' => now()->subHour()->format('Y-m-d H:i:s'),
        ]);

        $this->assertSame([$earlier->id], $this->selectedIds($this->dryRun(), 'events'));
    }

    public function test_a_multi_day_event_still_running_is_selected(): void
    {
        $venue = $this->translatingVenue();
        $festival = $this->createEvent($venue, [
            'name' => 'Week Long Festival',
            'starts_at' => now()->subDays(4)->format('Y-m-d H:i:s'),
            'duration' => 24 * 7,
        ]);

        $this->assertSame([$festival->id], $this->selectedIds($this->dryRun(), 'events'));
    }

    public function test_appointment_bookings_and_cancelled_events_are_not_selected(): void
    {
        $venue = $this->translatingVenue();
        $type = $this->createAppointmentType($venue);

        // A private booking row carrying a guest's name - never rendered in a second language.
        $this->createEvent($venue, [
            'name' => 'Consultation with Dana Levi',
            'appointment_type_id' => $type->id,
            'is_private' => true,
        ]);

        $this->createEvent($venue, [
            'name' => 'Cancelled Show',
            'is_cancelled' => true,
        ]);

        $this->assertSame([], $this->selectedIds($this->dryRun(), 'events'));
    }

    public function test_naming_an_event_explicitly_translates_it_even_when_past(): void
    {
        $venue = $this->translatingVenue();
        $past = $this->createEvent($venue, [
            'name' => 'Last Month Concert',
            'starts_at' => now()->subMonth()->format('Y-m-d H:i:s'),
        ]);

        // The operator escape hatch: naming a row by id overrides every gate.
        $this->assertSame([$past->id], $this->selectedIds($this->dryRun(['--event_id' => $past->id]), 'events'));
    }

    public function test_agenda_parts_and_curator_rows_follow_their_parent_event(): void
    {
        $venue = $this->translatingVenue();

        $past = $this->createEvent($venue, [
            'name' => 'Last Month Concert',
            'starts_at' => now()->subMonth()->format('Y-m-d H:i:s'),
        ]);
        $live = $this->createRecurringEvent($venue, [
            'name' => 'Weekly Class',
            'starts_at' => now()->subMonths(5)->format('Y-m-d H:i:s'),
        ]);

        // event_parts stores only clock times, and the curator pivot has no dates at all, so both
        // have to inherit liveness from the parent event.
        EventPart::create(['event_id' => $past->id, 'name' => 'Past Opening Act']);
        $livePart = EventPart::create(['event_id' => $live->id, 'name' => 'Live Opening Act']);

        $curator = $this->createCurator($this->createOwner(), ['language_code' => 'he']);
        $past->roles()->attach($curator->id, ['is_accepted' => true]);
        $live->roles()->attach($curator->id, ['is_accepted' => true]);

        $output = $this->dryRun();

        $this->assertSame([$livePart->id], $this->selectedIds($output, 'event-parts'));

        $curatorIds = $this->selectedIds($output, 'curator-events');
        $this->assertCount(1, $curatorIds);
        $this->assertSame($live->id, EventRole::find($curatorIds[0])->event_id);
    }

    public function test_dry_run_still_reports_without_an_ai_provider(): void
    {
        config(['services.google.gemini_key' => null, 'services.openai.api_key' => null]);

        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', [
            'name' => 'Waiting',
            'language_code' => 'it',
            'translation_language_code' => 'en',
        ]);

        // Inspecting the queue must not require a configured provider - it makes no calls.
        $this->assertSame([$role->id], $this->selectedIds($this->dryRun(), 'roles'));
    }
}
