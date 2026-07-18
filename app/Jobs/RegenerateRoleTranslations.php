<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\EventPart;
use App\Models\Group;
use App\Models\Role;
use App\Utils\GeminiUtils;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

/**
 * Clears a schedule's cached translations when its translation TARGET language changes.
 *
 * Translations live in `_en` columns (the column name is historical - the value is whatever
 * the schedule's target language is). When the owner picks a new target, every stored `_en`
 * value is now in the wrong language, so we null them and reset `translation_attempts`; the
 * hourly `app:translate` cron then regenerates them in the new target. A target change also
 * invalidates any manual `_en` overrides, since those were written in the previous target.
 *
 * Sub-schedule (Group) names are the exception: they are translated inline (never by the cron),
 * so this job re-translates them directly here.
 *
 * Uses bulk query-builder updates and saveQuietly() so it never re-fires model events (which
 * would otherwise re-derive `_html` columns from the values we are nulling, or re-dispatch).
 */
class RegenerateRoleTranslations implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public $timeout = 120;

    protected Role $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("regen-translations-{$this->role->id}"))->dontRelease(),
        ];
    }

    public function handle(): void
    {
        try {
            $this->resetRole();
            $this->resetEventsAndParts();
            $this->retranslateGroups();
        } catch (\Throwable $e) {
            report($e);
            Log::error('RegenerateRoleTranslations failed', [
                'role_id' => $this->role->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Null the schedule's own cached translations (scalar `_en` columns and the `_en` sub-keys
     * inside JSON columns) and reset its attempt counter so the cron regenerates them.
     */
    protected function resetRole(): void
    {
        $role = Role::find($this->role->id);
        if (! $role) {
            return;
        }

        foreach ([
            'name_en', 'description_en', 'description_html_en', 'short_description_en',
            'address1_en', 'address2_en', 'city_en', 'state_en', 'request_terms_en',
            'banner_message_en', 'banner_message_html_en', 'sponsor_section_title_en',
        ] as $column) {
            $role->{$column} = null;
        }

        // JSON columns keep the translation alongside the source under `_en` sub-keys.
        $role->sponsor_logos = $this->stripJsonKeys($role->sponsor_logos, ['name_en'], true);
        $role->custom_labels = $this->stripJsonKeys($role->custom_labels, ['value_en']);
        $role->event_custom_fields = $this->stripJsonKeys($role->event_custom_fields, ['name_en', 'options_en']);
        $role->event_categories = $this->stripJsonKeys($role->event_categories, ['name_en']);

        $role->translation_attempts = 0;
        $role->saveQuietly();
    }

    /**
     * Remove the given keys from every entry of a list/map that is stored either as a JSON
     * string (sponsor_logos) or an already-cast array (custom_labels, event_custom_fields,
     * event_categories). Returns the same shape it received so the caller can assign it back.
     */
    protected function stripJsonKeys($value, array $keys, bool $isJsonString = false)
    {
        $decoded = $isJsonString ? (is_string($value) ? json_decode($value, true) : $value) : $value;

        if (! is_array($decoded)) {
            return $value;
        }

        foreach ($decoded as &$entry) {
            if (is_array($entry)) {
                foreach ($keys as $key) {
                    unset($entry[$key]);
                }
            }
        }
        unset($entry);

        return $isJsonString ? json_encode($decoded) : $decoded;
    }

    /**
     * Bulk-null the `_en` columns on this schedule's events and their agenda parts, and reset
     * their attempt counters. Query-builder updates skip model events, so no `_html` re-derive.
     */
    protected function resetEventsAndParts(): void
    {
        // A curator never drives an event's translation target - each event's `_en` is governed by
        // its own venue/first-talent (Event::getTranslationLanguageCode()), and curators display
        // aggregated events via the pivot `_translated` columns, not `_en`. So a curator changing
        // its own target must not null (and force re-translation of) every event it aggregates.
        if ($this->role->isCurator()) {
            return;
        }

        Event::whereHas('roles', fn ($q) => $q->where('roles.id', $this->role->id))
            ->update([
                'name_en' => null,
                'short_description_en' => null,
                'description_en' => null,
                'description_html_en' => null,
                'translation_attempts' => 0,
            ]);

        EventPart::whereHas('event.roles', fn ($q) => $q->where('roles.id', $this->role->id))
            ->update([
                'name_en' => null,
                'description_en' => null,
                'description_html_en' => null,
                'translation_attempts' => 0,
            ]);
    }

    /**
     * Sub-schedule names are translated inline (not by the cron), so regenerate them here.
     * When the target equals the authored language there is nothing to translate, so clear them.
     */
    protected function retranslateGroups(): void
    {
        $role = Role::find($this->role->id);
        if (! $role) {
            return;
        }

        $groups = $role->groups()->get();
        if ($groups->isEmpty()) {
            return;
        }

        $target = $role->translation_language_code ?: 'en';

        if ($role->language_code === $target) {
            foreach ($groups as $group) {
                $group->name_en = null;
                $group->saveQuietly();
            }

            return;
        }

        $names = $groups->pluck('name')->filter()->unique()->values()->all();
        if (empty($names)) {
            return;
        }

        $translations = GeminiUtils::translateGroupNames($names, $role->language_code, $target);

        // translateGroupNames returns [] on API failure/quota, indistinguishable from success.
        // Groups are never re-translated by the cron, so blanking name_en here would lose them
        // permanently. On a total failure, leave the existing (stale) values for the next run.
        if (empty($translations)) {
            Log::warning('RegenerateRoleTranslations: group translation returned no results, leaving existing names', [
                'role_id' => $this->role->id,
                'group_count' => $groups->count(),
            ]);

            return;
        }

        foreach ($groups as $group) {
            // Only overwrite groups we actually got a translation for; leave the rest untouched
            // (a partial failure must not blank the ones that were not returned).
            if ($group->name && isset($translations[$group->name])) {
                $group->name_en = $translations[$group->name];
                $group->saveQuietly();
            }
        }
    }
}
