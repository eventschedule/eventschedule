<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPart;
use App\Models\EventRole;
use App\Models\Role;
use App\Models\TranslationGlossaryEntry;
use App\Utils\GeminiUtils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * A schedule's translation glossary (Details > Translation glossary): names automatic translation
 * keeps exactly as written ("Drinx", "Drink & Draw"), and expressions with a fixed translation.
 *
 * Four jobs, and every reader goes through this class:
 *
 * - save(): the schedule form's list, cleaned and capped, and a description of what CHANGED.
 * - forRole() / forEvent() / forCurator(): the map GeminiUtils' prompts take (term => translation,
 *   null = keep as written). A keep-as-is term holds in any direction (a name is a name); a fixed
 *   translation only for the schedule's own pair of languages, since it is written in its target.
 * - exactMatch() / missingKept(): what app:translate checks in code, because a prompt is a request
 *   and not a guarantee (Translate::translateText()).
 * - requeue(): after a change, clears the translations that use a changed term and do not already
 *   read the way the glossary now says, so the cron translates them again. Nothing else.
 */
class TranslationGlossary
{
    public const MAX_ENTRIES = 100;

    public const MAX_LENGTH = 100;

    /** Per-run cache of entries by schedule id; null when off (see memoize()). */
    private static ?array $memo = null;

    /**
     * app:translate reads the same schedules' glossaries for row after row. Off by default, so a
     * long-lived worker never serves a list from before the owner's last save.
     */
    public static function memoize(): void
    {
        self::$memo = [];
    }

    public static function forget(): void
    {
        self::$memo = null;
    }

    /** @return array<int, array{term: string, translation: ?string}> */
    public static function entries(Role $role): array
    {
        if (! $role->id) {
            return [];
        }

        if (self::$memo !== null && array_key_exists($role->id, self::$memo)) {
            return self::$memo[$role->id];
        }

        $rows = $role->relationLoaded('translationGlossaryEntries')
            ? $role->translationGlossaryEntries
            : $role->translationGlossaryEntries()->get();

        $entries = $rows->map(fn (TranslationGlossaryEntry $entry) => [
            'term' => (string) $entry->term,
            'translation' => $entry->keepsAsIs() ? null : (string) $entry->translation,
        ])->values()->all();

        if (self::$memo !== null) {
            self::$memo[$role->id] = $entries;
        }

        return $entries;
    }

    /**
     * Load the entries of several schedules in one query, for a run that will ask about each.
     *
     * @param  iterable<Role|null>  $roles
     */
    private static function prime(iterable $roles): void
    {
        if (self::$memo === null) {
            return;
        }

        $missing = [];
        foreach ($roles as $role) {
            if ($role && $role->id && ! array_key_exists($role->id, self::$memo)) {
                $missing[$role->id] = true;
            }
        }

        if (! $missing) {
            return;
        }

        $rows = TranslationGlossaryEntry::whereIn('role_id', array_keys($missing))
            ->orderBy('position')->orderBy('id')->get()->groupBy('role_id');

        foreach (array_keys($missing) as $id) {
            self::$memo[$id] = ($rows[$id] ?? collect())->map(fn (TranslationGlossaryEntry $entry) => [
                'term' => (string) $entry->term,
                'translation' => $entry->keepsAsIs() ? null : (string) $entry->translation,
            ])->values()->all();
        }
    }

    /**
     * The glossary of one schedule for a translation from $from into $to (by default its own pair).
     *
     * @return array<string, ?string>
     */
    public static function forRole(Role $role, ?string $from = null, ?string $to = null): array
    {
        $ownFrom = $role->language_code ?: 'en';
        $ownTo = $role->translation_language_code ?: 'en';
        $pairApplies = ($from ?? $ownFrom) === $ownFrom && ($to ?? $ownTo) === $ownTo;

        $map = [];
        foreach (self::entries($role) as $entry) {
            if ($entry['translation'] === null) {
                $map[$entry['term']] = null;
            } elseif ($pairApplies) {
                $map[$entry['term']] = $entry['translation'];
            }
        }

        return $map;
    }

    /**
     * An event's glossary: its own schedule's name (as app:translate always passed it), then the
     * glossaries of the schedule that made it and of the venue and performers it is at. Not a
     * curator that lists it: a curator's glossary is for its own copy (forCurator()).
     *
     * @return array<string, ?string>
     */
    public static function forEvent(Event $event, string $from, string $to): array
    {
        $map = self::schedulesOf($event, $from, $to);

        $creator = $event->creatorRole;
        if ($creator && $creator->name && $creator->name_en && ! self::has($map, $creator->name)) {
            $map[$creator->name] = $creator->name_en;
        }

        return $map;
    }

    /**
     * A curator's copy of an event it lists, in the curator's language: the curator's glossary
     * first, then the event's own schedules'.
     *
     * @return array<string, ?string>
     */
    public static function forCurator(Role $curator, Event $event, string $from, string $to): array
    {
        $map = self::forRole($curator, $from, $to);

        foreach (self::schedulesOf($event, $from, $to) as $term => $translation) {
            if (! self::has($map, (string) $term)) {
                $map[$term] = $translation;
            }
        }

        return $map;
    }

    /** @return array<string, ?string> */
    private static function schedulesOf(Event $event, string $from, string $to): array
    {
        $schedules = collect([$event->creatorRole])
            // Only a venue or performer that is ON the event: one that declined it, or has not
            // answered yet, has no say in how it is translated.
            ->merge($event->roles->reject(fn ($role) => $role->isCurator() || (int) ($role->pivot->is_accepted ?? 0) !== 1))
            ->filter()
            ->unique('id');

        self::prime($schedules);

        $map = [];
        foreach ($schedules as $schedule) {
            foreach (self::forRole($schedule, $from, $to) as $term => $translation) {
                if (! self::has($map, (string) $term)) {
                    $map[$term] = $translation;
                }
            }
        }

        return $map;
    }

    private static function has(array $map, string $term): bool
    {
        $key = self::foldKey($term);
        foreach (array_keys($map) as $other) {
            if (self::foldKey((string) $other) === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Two terms are one term when the table's collation (utf8mb4_unicode_ci) says so: without case,
     * without accents, "ß" as "ss". Compared the same way here, or a pair PHP calls different
     * ("Café", "Cafe") breaks the unique key on insert. ICU's root collation at primary strength
     * answers the same question; without intl, the lower-cased form is the fallback and the
     * insert below still survives a pair it missed.
     */
    public static function foldKey(string $term): string
    {
        static $collator = false;

        if ($collator === false) {
            $collator = class_exists(\Collator::class) ? new \Collator('root') : null;
            $collator?->setStrength(\Collator::PRIMARY);
        }

        $key = $collator ? $collator->getSortKey($term) : false;

        return is_string($key) ? $key : mb_strtolower($term);
    }

    /**
     * Whether $text uses $term as a word of its own, without regard to case: "Art" is not in
     * "party", nor "Bar" in "Barbecue". A term that begins or ends with a sign ("C++") is held to
     * the letters it does have.
     */
    public static function uses(string $text, string $term): bool
    {
        if ($term === '' || $text === '') {
            return false;
        }

        $found = preg_match('/(?<![\p{L}\p{N}])'.preg_quote($term, '/').'(?![\p{L}\p{N}])/iu', $text);

        // A PCRE failure (an invalid byte, a backtrack limit) answers false, not "absent".
        return $found === false ? mb_stripos($text, $term) !== false : $found === 1;
    }

    /**
     * Rows whose term is one an earlier row already has (foldKey()), by their position in $rows:
     * the schedule form refuses them with a message on the field rather than dropping one.
     *
     * @return array<int|string, string> row key => the earlier term it repeats
     */
    public static function duplicates(array $rows): array
    {
        $seen = [];
        $repeats = [];
        foreach ($rows as $index => $row) {
            $term = is_array($row) ? self::cleanText($row['term'] ?? null) : '';
            if ($term === '') {
                continue;
            }
            $key = self::foldKey($term);
            if (isset($seen[$key])) {
                $repeats[$index] = $seen[$key];
            } else {
                $seen[$key] = $term;
            }
        }

        return $repeats;
    }

    /**
     * Only the terms that appear in the text(s) being translated, as words, without case: a prompt
     * carrying a hundred unrelated names asks the model to think about all of them.
     *
     * @param  array<string, ?string>  $map
     * @param  string|array<int, string>  $texts
     * @return array<string, ?string>
     */
    public static function relevant(array $map, string|array $texts): array
    {
        $haystack = implode("\n", (array) $texts);

        return array_filter($map, fn ($translation, $term) => self::uses($haystack, (string) $term), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * A text that IS a glossary term (a sponsor named "Drinx", an event called "Drink & Draw") is
     * answered from the glossary without asking anybody.
     */
    public static function exactMatch(array $map, string $text): ?string
    {
        $trimmed = mb_strtolower(trim($text));
        if ($trimmed === '') {
            return null;
        }

        foreach ($map as $term => $translation) {
            if (mb_strtolower(trim((string) $term)) === $trimmed) {
                return $translation ?? trim($text);
            }
        }

        return null;
    }

    /**
     * Keep-as-is terms the source uses and the translation lost (as words, without case, the same
     * test needsRedo() makes, so the two never disagree about a translation). Only those:
     * a fixed translation may rightly be inflected (Portuguese gives a noun a gender and a plural),
     * so its absence proves nothing.
     *
     * @return array<int, string>
     */
    public static function missingKept(array $map, string $source, string $translation): array
    {
        $missing = [];
        foreach ($map as $term => $fixed) {
            $term = (string) $term;
            if ($fixed === null && $term !== '' && self::uses($source, $term) && ! self::uses($translation, $term)) {
                $missing[] = $term;
            }
        }

        return $missing;
    }

    // ------------------------------------------------------------------
    // Saving
    // ------------------------------------------------------------------

    /**
     * Replace a schedule's glossary with the rows the form posted, in their order. An empty term is
     * a row nobody filled in and is dropped; an empty translation means "keep as written"; a term
     * that repeats (without regard to case) keeps its first row; past MAX_ENTRIES rows are dropped.
     *
     * $seen is the terms the form SHOWED when it was opened (glossary_seen[]): a stored term it did
     * not show (added from another tab since) is kept, after the posted rows. Null replaces the
     * whole list (a restore).
     *
     * @param  array<int|string, mixed>  $rows  [['term' => ..., 'translation' => ...], ...]
     * @param  array<int, string>|null  $seen
     * @return array<int, array{term: string, before: array{translation: ?string}|null, after: array{translation: ?string}|null}>
     *                                                                                                                            what changed, for requeue()
     */
    public static function save(Role $role, array $rows, ?array $seen = null): array
    {
        $clean = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $term = self::cleanText($row['term'] ?? null);
            if ($term === '') {
                continue;
            }

            $key = self::foldKey($term);
            if (isset($clean[$key])) {
                continue;
            }

            $translation = self::cleanText($row['translation'] ?? null);
            $clean[$key] = ['term' => $term, 'translation' => $translation === '' ? null : $translation];

            if (count($clean) >= self::MAX_ENTRIES) {
                break;
            }
        }

        $stored = [];
        foreach (self::entries(Role::find($role->id) ?? $role) as $entry) {
            $stored[self::foldKey($entry['term'])] = $entry;
        }

        // Two tabs: what another one added since this page was opened is not this page's to remove.
        if ($seen !== null) {
            $shown = [];
            foreach ($seen as $term) {
                if (is_scalar($term) && ($term = self::cleanText($term)) !== '') {
                    $shown[self::foldKey($term)] = true;
                }
            }
            foreach ($stored as $key => $entry) {
                if (! isset($clean[$key]) && ! isset($shown[$key]) && count($clean) < self::MAX_ENTRIES) {
                    $clean[$key] = $entry;
                }
            }
        }

        $changes = [];
        foreach ($clean as $key => $entry) {
            $before = $stored[$key] ?? null;
            if ($before === null || $before['term'] !== $entry['term'] || $before['translation'] !== $entry['translation']) {
                $changes[] = [
                    'term' => $entry['term'],
                    'before' => $before ? ['translation' => $before['translation']] : null,
                    'after' => ['translation' => $entry['translation']],
                ];
            }
        }
        foreach ($stored as $key => $entry) {
            if (! isset($clean[$key])) {
                $changes[] = ['term' => $entry['term'], 'before' => ['translation' => $entry['translation']], 'after' => null];
            }
        }

        $orderChanged = array_keys($clean) !== array_keys($stored);

        if ($changes || $orderChanged) {
            DB::transaction(function () use ($role, $clean) {
                TranslationGlossaryEntry::where('role_id', $role->id)->delete();

                $now = now();
                $position = 0;
                $insert = [];
                foreach ($clean as $entry) {
                    $insert[] = [
                        'role_id' => $role->id,
                        'term' => $entry['term'],
                        'translation' => $entry['translation'],
                        'position' => $position++,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (! $insert) {
                    return;
                }

                try {
                    TranslationGlossaryEntry::insert($insert);
                } catch (QueryException $e) {
                    if (($e->errorInfo[1] ?? null) !== 1062) {
                        throw $e;
                    }
                    // A pair the collation calls one term and foldKey() did not: row by row, and
                    // the later of the two is dropped. MySQL undoes only the failed statement.
                    foreach ($insert as $row) {
                        try {
                            TranslationGlossaryEntry::insert($row);
                        } catch (QueryException $e) {
                            if (($e->errorInfo[1] ?? null) !== 1062) {
                                throw $e;
                            }
                        }
                    }
                }
            });
        }

        $role->unsetRelation('translationGlossaryEntries');
        if (self::$memo !== null) {
            unset(self::$memo[$role->id]);
        }

        return $changes;
    }

    public static function cleanText(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        // No invisible format characters (a zero-width space pasted with a name), one line, no runs
        // of spaces: a term is matched against the text as written.
        $value = (string) preg_replace('/\p{Cf}+/u', '', (string) $value);
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));

        return mb_substr($value, 0, self::MAX_LENGTH);
    }

    // ------------------------------------------------------------------
    // Re-translating what a change touches
    // ------------------------------------------------------------------

    /**
     * After the glossary of $role changed: clear the translations whose source uses a changed term
     * and that do not already read the way the glossary now says, so app:translate does them
     * again. Never a translation somebody corrected by hand (handEdited()), never an event the
     * cron will not reach again (a past one: clearing it would leave it untranslated for good),
     * and nothing that does not use a changed term.
     *
     * @param  array<int, array{term: string, before: ?array, after: ?array}>  $changes  from save()
     * @return array<string, int> how many rows were handed back, by kind
     */
    public static function requeue(Role $role, array $changes): array
    {
        $counts = ['schedule' => 0, 'events' => 0, 'parts' => 0, 'listings' => 0, 'sub_schedules' => 0];
        $changes = array_values(array_filter($changes, fn ($c) => is_array($c) && trim((string) ($c['term'] ?? '')) !== ''));

        if (! $changes) {
            return $counts;
        }

        $role = Role::find($role->id);
        if (! $role) {
            return $counts;
        }

        $terms = array_column($changes, 'term');

        // The schedule's own content, in its own pair.
        if ($role->offersTranslation()) {
            $counts['schedule'] = self::requeueSchedule($role, $changes);
            $counts['sub_schedules'] = self::retranslateGroups($role, $changes);
        }

        // Its events and their agenda parts. A curator's glossary governs only the events it made
        // and its own copies of the ones it lists.
        $events = Event::query()->where(function ($query) use ($role) {
            $query->where('creator_role_id', $role->id);
            if (! $role->isCurator()) {
                // Only an event it is on: a venue that declined one has no say in its translation.
                $query->orWhereHas('roles', fn ($r) => $r->where('roles.id', $role->id)->where('event_role.is_accepted', 1));
            }
        });
        TranslationQueue::liveEvents($events);

        $eventFields = ['name' => 'name_en', 'short_description' => 'short_description_en', 'description' => 'description_en'];
        // A fixed translation applies to an event only in the event's own pair of languages (its
        // venue's, else its first performer's), which may not be this schedule's.
        $ownPair = [$role->language_code ?: 'en', $role->translation_language_code ?: 'en'];
        $pairOf = fn (?Event $event) => $event && [$event->getLanguageCode(), $event->getTranslationLanguageCode()] === $ownPair;

        $counts['events'] = self::requeueRows(
            (clone $events)->where(fn ($q) => self::likeAny($q, array_keys($eventFields), $terms)),
            Event::class, $eventFields, $changes, $pairOf
        );

        $partFields = ['name' => 'name_en', 'description' => 'description_en'];
        $counts['parts'] = self::requeueRows(
            EventPart::query()->whereIn('event_id', (clone $events)->select('events.id'))
                ->where(fn ($q) => self::likeAny($q, array_keys($partFields), $terms)),
            EventPart::class, $partFields, $changes, fn (?Event $event) => $pairOf($event)
        );

        // A curator's copies of the events it lists, in its own authored language, so only its
        // keep-as-is terms apply there.
        if ($role->isCurator()) {
            $counts['listings'] = self::requeueListings($role, $changes, $terms);
        }

        if (array_sum($counts) > 0) {
            Log::info('Translation glossary: re-queued translations', ['role_id' => $role->id] + $counts);
        }

        return $counts;
    }

    private static function likeAny($query, array $columns, array $terms): void
    {
        foreach ($columns as $column) {
            foreach ($terms as $term) {
                $query->orWhere($column, 'like', '%'.addcslashes((string) $term, '\\%_').'%');
            }
        }
    }

    /**
     * Whether a translation of $source has to be done again after $changes.
     *
     * A change counts where it applied before or applies now: a keep-as-is term always, a fixed
     * translation only where $pairApplies. Where it applies now, the translation is redone unless it
     * already reads that way; where it applied only before (removed, or now a translation for
     * another pair), it is redone.
     */
    public static function needsRedo(?string $source, ?string $translation, array $changes, bool $pairApplies): bool
    {
        if ($source === null || $source === '' || $translation === null || $translation === '') {
            return false;
        }

        foreach ($changes as $change) {
            $term = (string) $change['term'];
            if (! self::uses($source, $term)) {
                continue;
            }

            $applies = fn ($side) => is_array($side) && ($side['translation'] === null || $pairApplies);

            if ($applies($change['after'] ?? null)) {
                $fixed = $change['after']['translation'];
                $reads = self::uses($translation, $fixed ?? $term);
                if (! $reads) {
                    return true;
                }
            } elseif ($applies($change['before'] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clear the fields of the matching rows of $query that need redoing, field by field, with a
     * query-builder update (no model events: Event::saving() would re-derive the `_html` columns
     * from the values being nulled).
     */
    private static function requeueRows(Builder $query, string $model, array $fields, array $changes, \Closure $pairOf): int
    {
        // Only a fixed translation depends on the pair; with none among the changes, nothing has
        // to be loaded to ask.
        $fixedChange = (bool) array_filter($changes, fn ($c) => ($c['before']['translation'] ?? null) !== null || ($c['after']['translation'] ?? null) !== null);

        $columns = ['id'];
        if ($model === EventPart::class) {
            $columns[] = 'event_id';
        }
        foreach ($fields as $source => $target) {
            $columns[] = $source;
            $columns[] = $target;
        }
        if ($overrides = self::overridesColumn($model)) {
            $columns[] = $overrides;
        }

        $byField = array_fill_keys(array_keys($fields), []);

        $query->select($columns)->chunkById(500, function ($rows) use ($fields, $changes, $pairOf, $fixedChange, $model, &$byField) {
            if ($fixedChange) {
                $rows->load($model === EventPart::class ? 'event.roles' : 'roles');
            }
            foreach ($rows as $row) {
                $pairApplies = $fixedChange && $pairOf($model === EventPart::class ? $row->event : $row);
                foreach ($fields as $source => $target) {
                    if (! self::handEdited($model, $row, $source) && self::needsRedo($row->{$source}, $row->{$target}, $changes, $pairApplies)) {
                        $byField[$source][] = $row->id;
                    }
                }
            }
        });

        $touched = [];
        foreach ($byField as $source => $ids) {
            foreach (array_chunk($ids, 500) as $chunk) {
                $values = [$fields[$source] => null, 'translation_attempts' => 0];
                if ($source === 'description') {
                    $values['description_html_en'] = null;
                }
                $model::whereIn('id', $chunk)->update($values);
                foreach ($chunk as $id) {
                    $touched[$id] = true;
                }
            }
        }

        return count($touched);
    }

    /**
     * Whether a translation was corrected by hand, which a glossary change must not overwrite: an
     * event's corrections are recorded per field (events.translation_overrides, the event form's
     * Translation row). A schedule's own `_en` fields and an agenda part's record no such thing.
     */
    private static function handEdited(string $model, $row, string $field): bool
    {
        return $model === Event::class && method_exists($row, 'translationIsManual') && $row->translationIsManual($field);
    }

    /** The column the event form records hand corrections in, once its migration has run. */
    private static function overridesColumn(string $model): ?string
    {
        static $has = null;

        if ($model !== Event::class) {
            return null;
        }

        $has ??= Schema::hasColumn('events', 'translation_overrides');

        return $has ? 'translation_overrides' : null;
    }

    private static function requeueSchedule(Role $role, array $changes): int
    {
        $values = [];

        foreach (TranslationQueue::ROLE_TEXT_FIELDS as $field) {
            if (self::needsRedo($role->{$field}, $role->{"{$field}_en"}, $changes, true)) {
                $values["{$field}_en"] = null;
                if ($field === 'description' || $field === 'banner_message') {
                    $values["{$field}_html_en"] = null;
                }
            }
        }

        // The lists that keep their translation beside each entry.
        $strip = function ($entries, string $sourceKey, array $targetKeys) use ($changes, &$any) {
            if (! is_array($entries)) {
                return $entries;
            }
            foreach ($entries as &$entry) {
                if (! is_array($entry)) {
                    continue;
                }
                foreach ($targetKeys as $targetKey) {
                    $source = $targetKey === 'options_en' ? ($entry['options'] ?? null) : ($entry[$sourceKey] ?? null);
                    if (isset($entry[$targetKey]) && self::needsRedo(is_string($source) ? $source : null, (string) $entry[$targetKey], $changes, true)) {
                        unset($entry[$targetKey]);
                        $any = true;
                    }
                }
            }
            unset($entry);

            return $entries;
        };

        $any = false;
        $sponsors = is_string($role->sponsor_logos) ? json_decode($role->sponsor_logos, true) : $role->sponsor_logos;
        $sponsors = $strip($sponsors, 'name', ['name_en']);
        $sponsorsChanged = $any;

        $any = false;
        $labels = $strip($role->custom_labels, 'value', ['value_en']);
        $labelsChanged = $any;

        $any = false;
        $fields = $strip($role->event_custom_fields, 'name', ['name_en', 'options_en']);
        $fieldsChanged = $any;

        $any = false;
        $categories = $strip($role->event_categories, 'name', ['name_en']);
        $categoriesChanged = $any;

        if ($sponsorsChanged) {
            $values['sponsor_logos'] = json_encode($sponsors);
        }
        if ($labelsChanged) {
            $values['custom_labels'] = json_encode($labels);
        }
        if ($fieldsChanged) {
            $values['event_custom_fields'] = json_encode($fields);
        }
        if ($categoriesChanged) {
            $values['event_categories'] = json_encode($categories);
        }

        if (! $values) {
            return 0;
        }

        // Not save(): that runs the schedule's whole saving hook and moves updated_at, which the
        // sitemap publishes (CLAUDE.md, writeOperationalColumns()). Straight to the table, since
        // the cast JSON columns are written here already encoded.
        $values['translation_attempts'] = 0;
        DB::table('roles')->where('id', $role->id)->update($values);

        return 1;
    }

    /**
     * Sub-schedule names are translated when they are saved, never by the cron, so a name that uses
     * a changed term is translated again here (as RegenerateRoleTranslations does).
     */
    private static function retranslateGroups(Role $role, array $changes): int
    {
        $groups = $role->groups()->get()->filter(
            fn ($group) => $group->name && self::needsRedo($group->name, $group->name_en ?: null, $changes, true)
        );

        if ($groups->isEmpty()) {
            return 0;
        }

        $names = $groups->pluck('name')->unique()->values()->all();
        $glossary = self::relevant(self::forRole($role), $names);
        $translations = GeminiUtils::translateGroupNames($names, $role->language_code, $role->translation_language_code ?: 'en', $glossary);

        $count = 0;
        foreach ($groups as $group) {
            $value = self::exactMatch($glossary, $group->name) ?? ($translations[$group->name] ?? null);
            if (is_string($value) && $value !== '') {
                $group->name_en = $value;
                $group->saveQuietly();
                $count++;
            }
        }

        return $count;
    }

    private static function requeueListings(Role $curator, array $changes, array $terms): int
    {
        $fields = ['name' => 'name_translated', 'short_description' => 'short_description_translated', 'description' => 'description_translated'];

        $byField = array_fill_keys(array_keys($fields), []);

        EventRole::query()
            ->where('event_role.role_id', $curator->id)
            ->whereHas('event', fn ($q) => TranslationQueue::liveEvents($q))
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->where(fn ($q) => self::likeAny($q, array_map(fn ($f) => 'events.'.$f, array_keys($fields)), $terms))
            ->select(['event_role.id', 'events.name', 'events.short_description', 'events.description', 'event_role.name_translated', 'event_role.short_description_translated', 'event_role.description_translated'])
            ->orderBy('event_role.id')
            ->chunk(500, function ($rows) use ($fields, $changes, &$byField) {
                foreach ($rows as $row) {
                    foreach ($fields as $source => $target) {
                        if (self::needsRedo($row->{$source}, $row->{$target}, $changes, false)) {
                            $byField[$source][] = $row->id;
                        }
                    }
                }
            });

        $touched = [];
        foreach ($byField as $source => $ids) {
            foreach (array_chunk($ids, 500) as $chunk) {
                $values = [$fields[$source] => null, 'translation_attempts' => 0];
                if ($source === 'description') {
                    $values['description_html_translated'] = null;
                }
                EventRole::whereIn('id', $chunk)->update($values);
                foreach ($chunk as $id) {
                    $touched[$id] = true;
                }
            }
        }

        return count($touched);
    }
}
