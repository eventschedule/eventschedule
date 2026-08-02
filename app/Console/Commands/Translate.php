<?php

namespace App\Console\Commands;

use App\Jobs\RegenerateRoleTranslations;
use App\Models\Event;
use App\Models\EventPart;
use App\Models\EventRole;
use App\Models\Role;
use App\Services\TranslationQueue;
use App\Services\UsageTrackingService;
use App\Utils\GeminiUtils;
use App\Utils\MarkdownUtils;
use App\Utils\UrlUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Fills the `_en` columns (which hold whatever each schedule's translation TARGET language is,
 * not necessarily English) by sending the authored text to the AI provider.
 *
 * Three properties matter and are easy to break:
 *
 * - Fair ordering. Rows are processed never-translated first, then longest-waiting, NOT by id.
 *   With an id ordering a budgeted run always spends its whole slice on the same low-id rows and
 *   a newer schedule is never reached, which is how schedules end up permanently untranslated.
 * - No work, no cost. A row can match the coarse SQL filter while having nothing left to
 *   translate (the JSON-column branches stay true forever once set). Those rows must not sleep
 *   and must not burn budget - they get their timestamp bumped so fair ordering parks them.
 * - `translation_attempts` counts CONSECUTIVE FAILURES, not attempts. A failed AI call is never
 *   persisted, so the column stays selectable and the row retries after a cooldown.
 */
class Translate extends Command
{
    protected $signature = 'app:translate
        {--role_id= : Translate only a specific schedule, by ID}
        {--subdomain= : Translate only a specific schedule, by subdomain}
        {--event_id= : Translate only a specific event, by ID}
        {--event_slug= : Translate only a specific event, by slug}
        {--max-seconds= : Stop cleanly after this many seconds (0 = no limit)}
        {--reset : Discard the target\'s existing translations first, then re-translate (requires a target)}
        {--force : Ignore the stuck-attempt threshold and the retry cooldown}
        {--dry-run : Report what would be translated, in order, without calling the AI}
        {--debug : Enable debug mode with verbose logging}';

    protected $description = 'Translate schedule and event content into each schedule\'s target language';

    /**
     * Rows a single pass will look at per run. Far above what a budgeted run gets through; it is
     * a memory bound on the id query, not a throttle.
     */
    private const BATCH = 500;

    /** Role columns translated one string at a time, source column => target is "{$field}_en". */
    private const ROLE_TEXT_FIELDS = [
        'name', 'description', 'short_description', 'address1', 'address2',
        'city', 'state', 'request_terms', 'banner_message', 'sponsor_section_title',
    ];

    private bool $debug = false;

    private bool $dryRun = false;

    private bool $force = false;

    /** Absolute microtime the whole command must stop by, or null when unbudgeted. */
    private ?float $overallDeadline = null;

    /** Absolute microtime the current pass must stop by, or null when unbudgeted. */
    private ?float $passDeadline = null;

    public function handle()
    {
        $this->debug = (bool) $this->option('debug');
        $this->dryRun = (bool) $this->option('dry-run');
        $this->force = (bool) $this->option('force');

        // --dry-run makes no AI calls, so it has to keep working on an install with no key -
        // it is the only way to inspect what the cron would do.
        if (! $this->dryRun && ! config('services.google.gemini_key') && ! config('services.openai.api_key')) {
            $this->info('No AI API key found, skipping...');

            return self::SUCCESS;
        }

        $roleId = $this->resolveRoleId();
        if ($roleId === false) {
            return self::FAILURE;
        }

        $eventId = $this->resolveEventId();
        if ($eventId === false) {
            return self::FAILURE;
        }

        if ($this->option('reset') && ! $this->resetTranslations($roleId, $eventId)) {
            return self::FAILURE;
        }

        $this->startBudget(targeted: (bool) ($roleId || $eventId));

        if ($roleId) {
            $passes = [
                fn () => $this->translateRoles($roleId),
                // Without this the escape hatch cannot fix a schedule: its own events would be
                // left to the unfiltered platform-wide pass that may never reach them.
                fn () => $this->translateEvents(null, $roleId),
                fn () => $this->translateCuratorEvents(null, $roleId),
                fn () => $this->translateEventParts(null, $roleId),
            ];
        } elseif ($eventId) {
            $passes = [
                fn () => $this->translateEvents($eventId),
                fn () => $this->translateCuratorEvents($eventId),
                fn () => $this->translateEventParts($eventId),
            ];
        } else {
            $passes = [
                fn () => $this->translateRoles(),
                fn () => $this->translateEvents(),
                fn () => $this->translateCuratorEvents(),
                fn () => $this->translateEventParts(),
            ];
        }

        $remaining = count($passes);

        foreach ($passes as $pass) {
            $this->beginPass($remaining--);
            $this->runTranslateStep($pass);
        }

        return self::SUCCESS;
    }

    private function runTranslateStep(callable $step): void
    {
        try {
            $step();
        } catch (\Throwable $e) {
            $this->error("Translation step failed: {$e->getMessage()}");
            report($e);
        }
    }

    // ------------------------------------------------------------------
    // Reset
    // ------------------------------------------------------------------

    /**
     * Discard the target's cached translations so the passes below regenerate them.
     *
     * This exists because every selection query matches on `whereNull('name_en')`, while older code
     * wrote EMPTY STRINGS whenever a row's source language equalled its target. Those rows are
     * invisible to the command - and `--force` does not reach them either, since it bypasses the
     * attempt threshold rather than the NULL check. Without this the only recovery was a hand-rolled
     * tinker call.
     *
     * @return bool false when the run should abort
     */
    private function resetTranslations(?int $roleId, ?int $eventId): bool
    {
        // Deliberately target-only. A platform-wide reset would discard every translation on the
        // install and re-buy all of them from the AI provider.
        if (! $roleId && ! $eventId) {
            $this->error('--reset needs a target: pass --subdomain, --role_id, --event_id or --event_slug.');

            return false;
        }

        if ($this->dryRun) {
            $this->info('[dry-run] --reset would discard existing translations for the target first.');

            return true;
        }

        $what = $roleId ? "schedule #{$roleId} and its events" : "event #{$eventId}";

        if ($this->input->isInteractive() && ! $this->confirm("Discard existing translations for {$what} and regenerate them?", true)) {
            $this->info('Aborted.');

            return false;
        }

        if ($roleId) {
            $role = Role::find($roleId);

            if (! $role) {
                $this->error("Schedule not found: {$roleId}");

                return false;
            }

            // Same operation the app already performs when an owner changes the target language, so
            // reuse it rather than re-deriving which columns and which related rows to clear: it
            // handles the role's scalar and JSON `_en` values, its events and parts, the attempt
            // counters, and the sub-schedule names the cron never touches. Its curator/talent
            // scoping is the behaviour we want here too.
            RegenerateRoleTranslations::dispatchSync($role);

            $this->info("Reset translations for schedule #{$roleId}.");

            return true;
        }

        $event = Event::find($eventId);

        if (! $event) {
            $this->error("Event not found: {$eventId}");

            return false;
        }

        // Query-builder updates so no model events fire: Event::saving() would re-derive the `_html`
        // columns from the values being nulled.
        Event::where('id', $event->id)->update([
            'name_en' => null,
            'short_description_en' => null,
            'description_en' => null,
            'description_html_en' => null,
            'translation_attempts' => 0,
        ]);

        EventPart::where('event_id', $event->id)->update([
            'name_en' => null,
            'description_en' => null,
            'description_html_en' => null,
            'translation_attempts' => 0,
        ]);

        EventRole::where('event_id', $event->id)->update([
            'name_translated' => null,
            'short_description_translated' => null,
            'description_translated' => null,
            'description_html_translated' => null,
            'translation_attempts' => 0,
        ]);

        $this->info("Reset translations for event #{$eventId}.");

        return true;
    }

    // ------------------------------------------------------------------
    // Passes
    // ------------------------------------------------------------------

    public function translateRoles($roleId = null)
    {
        $this->info('Starting translation of schedules...');

        $query = TranslationQueue::roles($roleId);

        if ($roleId) {
            $this->info("Filtering for schedule ID: $roleId");
        }

        $this->applyRetryScope($query);

        $ids = $this->selectIds($query);

        if ($this->dryRun) {
            $this->reportSelection('roles', $ids);

            return;
        }

        $translated = 0;
        $upToDate = 0;

        foreach ($ids as $id) {
            if ($this->outOfTime('roles')) {
                break;
            }

            $role = Role::find($id);
            if (! $role || $this->isCooledDown($role)) {
                continue;
            }

            if (! $this->roleNeedsTranslation($role)) {
                $this->markChecked($role);
                $upToDate++;

                continue;
            }

            try {
                if ($this->translateRole($role)) {
                    $translated++;
                    $this->pause();
                }
            } catch (\Throwable $e) {
                $this->error("Failed to translate schedule ID: {$role->id} - {$e->getMessage()}");
                report($e);
            }
        }

        $this->info("Schedules: {$translated} translated, {$upToDate} already up to date, ".count($ids).' examined.');
    }

    public function translateEvents($eventId = null, $roleId = null)
    {
        $this->info('Starting translation of events...');

        $query = TranslationQueue::events($eventId, $roleId);

        if ($eventId) {
            $this->info("Filtering for event ID: $eventId");
        } elseif ($roleId) {
            $this->info("Filtering for schedule ID: $roleId");
        }

        $this->applyRetryScope($query);

        $ids = $this->selectIds($query);

        if ($this->dryRun) {
            $this->reportSelection('events', $ids);

            return;
        }

        $translated = 0;
        $skipped = 0;

        foreach ($ids as $id) {
            if ($this->outOfTime('events')) {
                break;
            }

            // Load the whole row: Event::saving() re-derives every `_html` column from the model,
            // so saving a partially selected Event would blank them.
            $event = Event::with(['roles', 'creatorRole'])->find($id);
            if (! $event || $this->isCooledDown($event)) {
                continue;
            }

            try {
                $toLang = $event->getTranslationLanguageCode();
                $fromLang = $event->getLanguageCode();

                if ($fromLang == $toLang) {
                    // Source already equals the target: mark it done with empty strings so the row
                    // drops out of the query instead of being re-examined every run.
                    $event->name_en = '';
                    $event->description_en = '';
                    $event->short_description_en = '';
                    $event->save();
                    $skipped++;

                    continue;
                }

                $glossary = [];
                if ($event->creatorRole && $event->creatorRole->name && $event->creatorRole->name_en) {
                    $glossary[$event->creatorRole->name] = $event->creatorRole->name_en;
                }

                $calls = 0;
                $successes = 0;

                foreach (['name', 'description', 'short_description'] as $field) {
                    if ($event->{$field} && ! $event->{"{$field}_en"}) {
                        $value = $this->translateText($event->{$field}, $fromLang, $toLang, $glossary, $calls, $successes);
                        if ($value !== null) {
                            $event->{"{$field}_en"} = $value;
                            $this->debugLine("Event {$event->id} {$field}: '{$event->{$field}}' -> '{$value}'");
                        }
                    }
                }

                if ($calls === 0) {
                    $this->markChecked($event);

                    continue;
                }

                $this->recordOutcome($event, $successes, $event->creator_role_id ?? 0);
                $event->save();
                $translated++;
                $this->pause();
            } catch (\Throwable $e) {
                $this->error("Failed to translate event ID: {$event->id} - {$e->getMessage()}");
                report($e);
            }
        }

        $this->info("Events: {$translated} translated, {$skipped} same-language, ".count($ids).' examined.');
    }

    public function translateCuratorEvents($eventId = null, $roleId = null)
    {
        $this->info('Starting translation of curator events...');

        $query = TranslationQueue::curatorEvents($eventId, $roleId);

        if ($eventId) {
            $this->info("Filtering for event ID: $eventId");
        }

        if ($roleId) {
            $this->info("Filtering for schedule ID: $roleId");
        }

        $this->applyRetryScope($query);

        $ids = $this->selectIds($query);

        if ($this->dryRun) {
            $this->reportSelection('curator-events', $ids);

            return;
        }

        $translated = 0;
        $skipped = 0;

        foreach ($ids as $id) {
            if ($this->outOfTime('curator events')) {
                break;
            }

            $eventRole = EventRole::with('role', 'event')->find($id);
            if (! $eventRole || ! $eventRole->event || ! $eventRole->role || $this->isCooledDown($eventRole)) {
                continue;
            }

            try {
                $fromLang = $eventRole->event->getLanguageCode();
                // A curator shows aggregated events in its OWN authored language, not in its
                // translation target - the target governs the curator's own copy, and the pivot
                // `_translated` columns are what the curator's page reads.
                $toLang = $eventRole->role->language_code;

                if ($fromLang == $toLang) {
                    $eventRole->name_translated = '';
                    $eventRole->description_translated = '';
                    $eventRole->short_description_translated = '';
                    $eventRole->save();
                    $skipped++;

                    continue;
                }

                $calls = 0;
                $successes = 0;

                foreach (['name', 'description', 'short_description'] as $field) {
                    $target = "{$field}_translated";
                    if ($eventRole->event->{$field} && ! $eventRole->{$target}) {
                        $value = $this->translateText($eventRole->event->{$field}, $fromLang, $toLang, [], $calls, $successes);
                        if ($value !== null) {
                            $eventRole->{$target} = $value;
                            if ($field === 'description') {
                                $eventRole->description_html_translated = MarkdownUtils::convertToHtml($value);
                            }
                            $this->debugLine("Curator event {$eventRole->id} {$field} -> '{$value}'");
                        }
                    }
                }

                if ($calls === 0) {
                    $this->markChecked($eventRole);

                    continue;
                }

                $this->recordOutcome($eventRole, $successes, $eventRole->role_id);
                $eventRole->save();
                $translated++;
                $this->pause();
            } catch (\Throwable $e) {
                $this->error("Failed to translate curator event ID: {$id} - {$e->getMessage()}");
                report($e);
            }
        }

        $this->info("Curator events: {$translated} translated, {$skipped} same-language, ".count($ids).' examined.');
    }

    public function translateEventParts($eventId = null, $roleId = null)
    {
        $this->info('Starting translation of event parts...');

        $query = TranslationQueue::eventParts($eventId, $roleId);

        if ($eventId) {
            $this->info("Filtering for event ID: $eventId");
        } elseif ($roleId) {
            $this->info("Filtering for schedule ID: $roleId");
        }

        $this->applyRetryScope($query);

        $ids = $this->selectIds($query);

        if ($this->dryRun) {
            $this->reportSelection('event-parts', $ids);

            return;
        }

        $translated = 0;
        $skipped = 0;

        foreach ($ids as $id) {
            if ($this->outOfTime('event parts')) {
                break;
            }

            $part = EventPart::with(['event.roles', 'event.creatorRole'])->find($id);
            if (! $part || ! $part->event || $this->isCooledDown($part)) {
                continue;
            }

            try {
                $fromLang = $part->event->getLanguageCode();
                $toLang = $part->event->getTranslationLanguageCode();

                if ($fromLang == $toLang) {
                    $part->name_en = '';
                    $part->description_en = '';
                    $part->save();
                    $skipped++;

                    continue;
                }

                $glossary = [];
                if ($part->event->creatorRole && $part->event->creatorRole->name && $part->event->creatorRole->name_en) {
                    $glossary[$part->event->creatorRole->name] = $part->event->creatorRole->name_en;
                }

                $calls = 0;
                $successes = 0;

                foreach (['name', 'description'] as $field) {
                    if ($part->{$field} && ! $part->{"{$field}_en"}) {
                        $value = $this->translateText($part->{$field}, $fromLang, $toLang, $glossary, $calls, $successes);
                        if ($value !== null) {
                            $part->{"{$field}_en"} = $value;
                            $this->debugLine("Event part {$part->id} {$field} -> '{$value}'");
                        }
                    }
                }

                if ($calls === 0) {
                    $this->markChecked($part);

                    continue;
                }

                $this->recordOutcome($part, $successes, $part->event->creator_role_id ?? 0);
                $part->save();
                $translated++;
                $this->pause();
            } catch (\Throwable $e) {
                $this->error("Failed to translate event part ID: {$id} - {$e->getMessage()}");
                report($e);
            }
        }

        $this->info("Event parts: {$translated} translated, {$skipped} same-language, ".count($ids).' examined.');
    }

    // ------------------------------------------------------------------
    // Role translation
    // ------------------------------------------------------------------

    /** Returns true when at least one AI call was made for this schedule. */
    private function translateRole(Role $role): bool
    {
        $fromLang = $role->language_code;
        $toLang = $role->translation_language_code;

        $this->debugLine("Processing schedule {$role->id} ({$role->subdomain}): {$fromLang} -> {$toLang}");

        $calls = 0;
        $successes = 0;

        // Translate the name first so every later field can reuse it as a glossary entry and stay
        // consistent with how the schedule is titled.
        if ($role->name && ! $role->name_en) {
            $value = $this->translateText($role->name, $fromLang, $toLang, [], $calls, $successes);
            if ($value !== null) {
                $role->name_en = $value;
            }
        }

        $glossary = [];
        if ($role->name && $role->name_en) {
            $glossary[$role->name] = $role->name_en;
        }

        foreach (self::ROLE_TEXT_FIELDS as $field) {
            if ($field === 'name') {
                continue;
            }

            if ($role->{$field} && ! $role->{"{$field}_en"}) {
                $value = $this->translateText($role->{$field}, $fromLang, $toLang, $glossary, $calls, $successes);
                if ($value !== null) {
                    $role->{"{$field}_en"} = $value;
                    $this->debugLine("Schedule {$role->id} {$field} -> '{$value}'");
                }
            }
        }

        $this->translateSponsorLogos($role, $fromLang, $toLang, $calls, $successes);
        $this->translateCustomFields($role, $fromLang, $toLang, $calls, $successes);
        $this->translateCustomLabels($role, $fromLang, $toLang, $calls, $successes);
        $this->translateEventCategories($role, $fromLang, $toLang, $calls, $successes);

        if ($calls === 0) {
            $this->markChecked($role);

            return false;
        }

        $this->recordOutcome($role, $successes, $role->id);
        $role->save();

        return true;
    }

    private function translateSponsorLogos(Role $role, string $from, string $to, int &$calls, int &$successes): void
    {
        $sponsors = $this->decodeSponsorLogos($role);
        $pending = $this->pendingEntries($sponsors, 'name', 'name_en');

        if (empty($pending)) {
            return;
        }

        $translations = $this->translateBatch($pending, $from, $to, $calls, $successes, 'sponsor names');

        foreach ($pending as $idx => $name) {
            if (isset($translations[$name])) {
                $sponsors[$idx]['name_en'] = $translations[$name];
            }
        }

        $role->sponsor_logos = json_encode($sponsors);
    }

    private function translateCustomFields(Role $role, string $from, string $to, int &$calls, int &$successes): void
    {
        $fields = $role->event_custom_fields;
        if (! is_array($fields)) {
            return;
        }

        $pending = $this->pendingEntries($fields, 'name', 'name_en');

        if (! empty($pending)) {
            $translations = $this->translateBatch($pending, $from, $to, $calls, $successes, 'custom field names');
            foreach ($pending as $key => $name) {
                if (isset($translations[$name])) {
                    $fields[$key]['name_en'] = $translations[$name];
                }
            }
        }

        $optionValues = [];
        $dropdownKeys = [];
        foreach ($fields as $key => $config) {
            if (! is_array($config)) {
                continue;
            }
            if (in_array($config['type'] ?? '', ['dropdown', 'multiselect'], true)
                && ! empty($config['options']) && empty($config['options_en'])) {
                foreach ($this->splitOptions($config['options']) as $option) {
                    $optionValues[$option] = $option;
                }
                $dropdownKeys[] = $key;
            }
        }

        if (! empty($optionValues)) {
            $calls++;
            $this->debugLine('Translating '.count($optionValues).' custom field option values');
            $translations = GeminiUtils::translateCustomFieldOptions(array_values($optionValues), $from, $to);

            if (! empty($translations)) {
                $successes++;
                foreach ($dropdownKeys as $key) {
                    $translated = [];
                    foreach ($this->splitOptions($fields[$key]['options']) as $option) {
                        $translated[] = $translations[$option] ?? $option;
                    }
                    $fields[$key]['options_en'] = implode(', ', $translated);
                }
            }
        }

        $role->event_custom_fields = $fields;
    }

    private function translateCustomLabels(Role $role, string $from, string $to, int &$calls, int &$successes): void
    {
        $labels = $role->custom_labels;
        $pending = $this->pendingEntries($labels, 'value', 'value_en');

        if (empty($pending)) {
            return;
        }

        $translations = $this->translateBatch($pending, $from, $to, $calls, $successes, 'custom labels');

        foreach ($pending as $key => $value) {
            if (isset($translations[$value])) {
                $labels[$key]['value_en'] = $translations[$value];
            }
        }

        $role->custom_labels = $labels;
    }

    private function translateEventCategories(Role $role, string $from, string $to, int &$calls, int &$successes): void
    {
        $categories = $role->event_categories;
        $pending = $this->pendingEntries($categories, 'name', 'name_en');

        if (empty($pending)) {
            return;
        }

        $translations = $this->translateBatch($pending, $from, $to, $calls, $successes, 'event categories');

        foreach ($pending as $idx => $name) {
            if (isset($translations[$name])) {
                $categories[$idx]['name_en'] = $translations[$name];
            }
        }

        $role->event_categories = $categories;
    }

    /**
     * @param  array<int|string, string>  $pending  keyed by entry index, valued by the source text
     * @return array<string, string> source text => translation, empty when the call failed
     */
    private function translateBatch(array $pending, string $from, string $to, int &$calls, int &$successes, string $label): array
    {
        $calls++;
        $this->debugLine('Translating '.count($pending).' '.$label);

        $translations = GeminiUtils::translateCustomFieldNames(array_values($pending), $from, $to);

        // Count a success only when the call actually resolved something we asked for. A non-empty
        // response is not enough: the model can return a map whose keys have been re-cased, trimmed
        // or renamed, in which case the callers write nothing back. Treating that as a success would
        // reset translation_attempts every run, so the row would be re-selected and re-billed
        // forever with the failure counter never rising to reach the cooldown.
        $resolved = 0;
        foreach ($pending as $value) {
            if (isset($translations[$value])) {
                $resolved++;
            }
        }

        if ($resolved > 0) {
            $successes++;
        }

        return $translations;
    }

    // ------------------------------------------------------------------
    // Shared helpers
    // ------------------------------------------------------------------

    /**
     * Translate one string, counting the call and whether it produced usable text.
     *
     * GeminiUtils::translate() returns null on quota, timeout and 503, which is indistinguishable
     * from a genuinely empty translation. Persisting that would leave the column non-selectable
     * (or wipe a good value), so a failure returns null and the caller leaves the column alone.
     */
    private function translateText(?string $text, string $from, string $to, array $glossary, int &$calls, int &$successes): ?string
    {
        if (! $text) {
            return null;
        }

        $calls++;
        $value = GeminiUtils::translate($text, $from, $to, $glossary);

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $successes++;

        return $value;
    }

    /**
     * Record the outcome of a row that made at least one AI call.
     *
     * `translation_attempts` counts CONSECUTIVE failures: any success resets it. Combined with the
     * cooldown in applyRetryScope()/isCooledDown() this means a quota window costs a schedule a
     * retry delay, not a permanent freeze.
     */
    private function recordOutcome($model, int $successes, $usageRoleId): void
    {
        $model->translation_attempts = $successes > 0 ? 0 : ((int) $model->translation_attempts) + 1;
        $model->last_translated_at = now();

        UsageTrackingService::track(UsageTrackingService::GEMINI_TRANSLATE, $usageRoleId);
    }

    /**
     * Park a row that matched the coarse filter but has nothing left to translate.
     *
     * Without the timestamp bump these rows sort first forever (fair ordering puts never-translated
     * rows first) and are re-examined ahead of real work on every run. saveQuietly() keeps it to a
     * single column write: no model events, so no `_html` re-derivation and no job dispatch.
     */
    private function markChecked($model): void
    {
        $model->last_translated_at = now();
        $model->saveQuietly();
    }

    private function roleNeedsTranslation(Role $role): bool
    {
        foreach (self::ROLE_TEXT_FIELDS as $field) {
            if ($role->{$field} && ! $role->{"{$field}_en"}) {
                return true;
            }
        }

        if ($this->pendingEntries($this->decodeSponsorLogos($role), 'name', 'name_en')) {
            return true;
        }

        if ($this->pendingEntries($role->event_custom_fields, 'name', 'name_en')) {
            return true;
        }

        if ($this->pendingEntries($role->custom_labels, 'value', 'value_en')) {
            return true;
        }

        if ($this->pendingEntries($role->event_categories, 'name', 'name_en')) {
            return true;
        }

        foreach ((array) $role->event_custom_fields as $config) {
            if (! is_array($config)) {
                continue;
            }
            if (in_array($config['type'] ?? '', ['dropdown', 'multiselect'], true)
                && ! empty($config['options']) && empty($config['options_en'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Entries of a JSON list/map that have a source value but no translation yet, keyed as in the
     * original so the caller can write the translation back in place.
     *
     * @return array<int|string, string>
     */
    private function pendingEntries($entries, string $sourceKey, string $targetKey): array
    {
        if (! is_array($entries)) {
            return [];
        }

        $pending = [];

        foreach ($entries as $key => $entry) {
            if (is_array($entry) && ! empty($entry[$sourceKey]) && empty($entry[$targetKey])) {
                $pending[$key] = $entry[$sourceKey];
            }
        }

        return $pending;
    }

    /** sponsor_logos is a string column holding JSON, unlike the cast-array JSON columns. */
    private function decodeSponsorLogos(Role $role): array
    {
        $decoded = is_string($role->sponsor_logos) ? json_decode($role->sponsor_logos, true) : $role->sponsor_logos;

        return is_array($decoded) ? $decoded : [];
    }

    private function splitOptions(string $options): array
    {
        return array_filter(array_map('trim', explode(',', $options)));
    }

    // ------------------------------------------------------------------
    // Selection, ordering and budget
    // ------------------------------------------------------------------

    /**
     * Select ids in processing order, then load each row individually in the caller.
     *
     * Not ->get(): that materialises every matching row (this pass used to load every event on the
     * platform). Not ->cursor(): the loops write to the table they would be streaming. Not
     * ->chunkById(): that forces an id ordering and would undo the fairness below.
     */
    private function selectIds($query): Collection
    {
        return $query
            // Never-translated rows first, then longest-waiting. This is what makes a budgeted run
            // cumulative - each run advances the frontier instead of restarting from the lowest id.
            ->orderByRaw('last_translated_at IS NULL DESC')
            ->orderBy('last_translated_at')
            ->orderBy('id')
            ->limit(self::BATCH)
            ->pluck('id');
    }

    private function reportSelection(string $label, Collection $ids): void
    {
        $this->info("[dry-run] {$label}: {$ids->count()} selected");

        foreach ($ids as $id) {
            $this->line("[dry-run] {$label} #{$id}");
        }
    }

    /** Exclude rows that have failed too often and are still inside their retry cooldown. */
    private function applyRetryScope($query): void
    {
        if ($this->force) {
            return;
        }

        $threshold = (int) config('usage.stuck_translation_attempts', 3);
        if ($threshold <= 0) {
            return;
        }

        $cutoff = $this->retryCutoff();

        $query->where(function ($q) use ($threshold, $cutoff) {
            $q->where('translation_attempts', '<', $threshold);

            if ($cutoff) {
                $q->orWhere('last_translated_at', '<', $cutoff);
            }
        });
    }

    private function isCooledDown($model): bool
    {
        if ($this->force) {
            return false;
        }

        $threshold = (int) config('usage.stuck_translation_attempts', 3);
        if ($threshold <= 0 || $model->translation_attempts < $threshold) {
            return false;
        }

        $cutoff = $this->retryCutoff();

        if ($cutoff === null) {
            return true;
        }

        return $model->last_translated_at && $model->last_translated_at->greaterThan($cutoff);
    }

    private function retryCutoff(): ?Carbon
    {
        $hours = (int) config('usage.translation_retry_after_hours', 24);

        return $hours > 0 ? now()->subHours($hours) : null;
    }

    private function startBudget(bool $targeted): void
    {
        $option = $this->option('max-seconds');

        // A hand-run single schedule should finish the job; only the cron needs a budget.
        $max = ($option === null || $option === '')
            ? ($targeted ? 0 : (int) config('usage.translation_max_seconds', 240))
            : (int) $option;

        $this->overallDeadline = $max > 0 ? microtime(true) + $max : null;

        if ($max > 0) {
            $this->info("Time budget: {$max}s");
        }
    }

    private function beginPass(int $passesRemaining): void
    {
        if ($this->overallDeadline === null) {
            $this->passDeadline = null;

            return;
        }

        // Split what is left evenly across the passes that have not run yet. Without this a
        // backlog in the first pass consumes the whole budget and the later passes never run,
        // which is how a schedule ends up with a translated name and untranslated events.
        $now = microtime(true);
        $remaining = max(0.0, $this->overallDeadline - $now);

        $this->passDeadline = $now + ($remaining / max(1, $passesRemaining));
    }

    private function outOfTime(string $pass): bool
    {
        if ($this->passDeadline === null || microtime(true) < $this->passDeadline) {
            return false;
        }

        $this->info("Time budget reached during {$pass}; stopping cleanly. The next run resumes with the longest-waiting rows.");

        return true;
    }

    private function pause(): void
    {
        if ($this->dryRun || config('app.is_testing')) {
            return;
        }

        sleep(rand(12, 18));
    }

    private function debugLine(string $message): void
    {
        if ($this->debug) {
            $this->info($message);
        }
    }

    // ------------------------------------------------------------------
    // Option resolution
    // ------------------------------------------------------------------

    /** @return int|null|false false signals an unresolvable option */
    private function resolveRoleId()
    {
        if ($subdomain = $this->option('subdomain')) {
            $role = Role::where('subdomain', $subdomain)->first();

            if (! $role) {
                $this->error("No schedule found with subdomain: {$subdomain}");

                return false;
            }

            $this->info("Resolved subdomain '{$subdomain}' to schedule ID: {$role->id}");

            return $role->id;
        }

        $given = $this->option('role_id');

        if (! $given) {
            return null;
        }

        $roleId = is_numeric($given) ? $given : UrlUtils::decodeId($given);

        // decodeId() returns null for anything it cannot decode. Falling through with 0 would run
        // every unscoped pass instead - a typo silently translating the whole install rather than
        // the one schedule the operator asked for.
        if (! $roleId) {
            $this->error("Could not resolve schedule ID: {$given}");

            return false;
        }

        if (! is_numeric($given)) {
            $this->info("Decoded schedule ID: {$roleId}");
        }

        return (int) $roleId;
    }

    /** @return int|null|false false signals an unresolvable option */
    private function resolveEventId()
    {
        $given = $this->option('event_id');
        $eventId = null;

        if ($given) {
            $eventId = is_numeric($given) ? $given : UrlUtils::decodeId($given);

            // Same reasoning as resolveRoleId(): an undecodable id must not fall through to a
            // platform-wide run.
            if (! $eventId) {
                $this->error("Could not resolve event ID: {$given}");

                return false;
            }

            if (! is_numeric($given)) {
                $this->info("Decoded event ID: {$eventId}");
            }
        }

        if (! $eventId && $slug = $this->option('event_slug')) {
            $event = Event::where('slug', $slug)->first();

            if (! $event) {
                $this->error("No event found with slug: {$slug}");

                return false;
            }

            $this->info("Resolved event slug '{$slug}' to event ID: {$event->id}");
            $eventId = $event->id;
        }

        return $eventId ? (int) $eventId : null;
    }
}
