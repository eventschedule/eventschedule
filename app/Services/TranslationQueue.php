<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPart;
use App\Models\EventRole;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * The four selection queries behind `app:translate`.
 *
 * They live here rather than inside the command so the admin usage panel can report the real
 * backlog instead of a re-typed approximation of it. A drifting copy is how the panel ends up
 * saying nothing is pending while schedules sit untranslated.
 *
 * These select rows that MIGHT need work. The command still decides per row: for the JSON columns
 * SQL cannot see whether the `_en` sub-keys are filled, and for events the source == target check
 * needs Event::getTranslationLanguageCode()'s venue-first resolution.
 */
class TranslationQueue
{
    /**
     * Role columns whose translation lives in a sibling `_en` column, so SQL can see exactly
     * whether the work is done. Mirrors Translate::ROLE_TEXT_FIELDS; WorkBacklogTest pins
     * the two lists against each other, because a field present there and missing here is a
     * field the cron translates but the backlog never counts.
     */
    public const ROLE_TEXT_FIELDS = [
        'name', 'description', 'short_description', 'address1', 'address2',
        'city', 'state', 'request_terms', 'banner_message', 'sponsor_section_title',
    ];

    /**
     * Role columns holding JSON whose translations live under an `_en` sub-key INSIDE the value.
     * SQL cannot check those cheaply (sponsor_logos is a string column holding JSON; the rest are
     * cast arrays), so the only question these can answer is "might there be work in here" - and
     * the answer stays yes forever once the column is set.
     *
     * Value is the "column is not empty" test, since an empty sponsor_logos is the string '[]'
     * rather than NULL.
     */
    private const ROLE_JSON_COLUMNS = [
        'event_custom_fields' => null,
        'custom_labels' => null,
        'event_categories' => null,
        'sponsor_logos' => '[]',
    ];

    /**
     * The failure ceiling and retry cutoff app:translate parks a row on, read in ONE place.
     *
     * Both this class and the command need them: the command to exclude those rows from what it
     * selects, this class to count them. Two readings of the same config keys is how the panel
     * ends up promising a queue will drain in six hours while the command has quietly parked half
     * of it for a day.
     *
     * @return array{0: int, 1: ?\Illuminate\Support\Carbon} threshold (0 = no ceiling), cutoff
     */
    public static function retryLimits(): array
    {
        $threshold = (int) config('usage.stuck_translation_attempts', 3);
        $hours = (int) config('usage.translation_retry_after_hours', 24);

        return [max(0, $threshold), $hours > 0 ? now()->subHours($hours) : null];
    }

    /** `$field` has a source value and no translation beside it. */
    private static function missingTranslation($query, string $field): void
    {
        $query->whereNotNull($field)->where($field, '!=', '')->whereNull($field.'_en');
    }

    /**
     * Schedules with untranslated content, restricted to those whose authored language differs
     * from their target. Both language columns are NOT NULL DEFAULT 'en', so nothing is lost to a
     * NULL comparison here.
     *
     * $textOnly drops the JSON prefilter below. It is for COUNTING, never for selection: the
     * command has to be handed the rows it might need to look at, and only PHP can tell whether a
     * JSON column still has work in it.
     */
    public static function roles(?int $roleId = null, bool $textOnly = false): Builder
    {
        $query = Role::query()
            ->whereColumn('language_code', '!=', 'translation_language_code')
            ->where(function ($query) use ($textOnly) {
                // address2 is in Translate::ROLE_TEXT_FIELDS and Role::updating() clears
                // address2_en when it changes, but this chain once had no branch for it - so a
                // schedule whose only untranslated text was its second address line was never
                // selected, and translatedAddress2() stayed untranslated for good. Driving both
                // lists from one constant is what stops that recurring.
                foreach (self::ROLE_TEXT_FIELDS as $field) {
                    $query->orWhere(fn ($q) => self::missingTranslation($q, $field));
                }

                if ($textOnly) {
                    return;
                }

                // Coarse prefilter: the translation lives beside the source under an `_en`
                // sub-key, which SQL cannot check cheaply. These stay true once the column is
                // set, so the command re-checks in PHP and parks rows with nothing to do
                // (Translate::roleNeedsTranslation() / markChecked()).
                foreach (self::ROLE_JSON_COLUMNS as $column => $empty) {
                    $query->orWhere(function ($q) use ($column, $empty) {
                        $q->whereNotNull($column);

                        if ($empty !== null) {
                            $q->where($column, '!=', $empty);
                        }
                    });
                }
            });

        if ($roleId) {
            $query->where('id', $roleId);
        }

        return $query;
    }

    /**
     * Narrow an Event query to the events still worth paying to translate.
     *
     * The three-branch shape is this codebase's settled idiom for "not over yet" - it appears
     * verbatim in FederationService::federatableQuery(), MarketingController::publicUpcomingEventsQuery(),
     * FeedController and PromotionService, the last of which states the rule outright: a recurring
     * event has no single date and is always current; a one-off is done once its end time passes.
     *
     * Do NOT substitute Event::scopeUpcomingOrOngoing(). That scope deliberately omits days_of_week
     * and every caller adds the branch itself. A recurring series has no end date SQL can read and
     * its starts_at is the FIRST occurrence, never advanced - so without that branch a weekly class
     * that began months ago reads as past forever, and translation would silently stop for nearly
     * every recurring event on the platform.
     */
    private static function restrictToLiveEvents(Builder $query): Builder
    {
        // Start of yesterday rather than now(): starts_at is a naive datetime and schedules span
        // roughly +/-14h of timezone, so a tighter bound drops events that locally have not happened.
        $cutoff = Carbon::today()->subDay();

        return $query
            // Appointment bookings carry a guest's name and are never rendered in a second language,
            // so translating them is pure spend. It also writes to rows the reschedule cooldown
            // watches - see the note in AppointmentService about this command touching them.
            ->whereNull('appointment_type_id')
            ->where('is_cancelled', false)
            ->where(function ($q) use ($cutoff) {
                $q->where('starts_at', '>=', $cutoff)
                    ->orWhereNotNull('days_of_week')
                    ->orWhere(function ($q2) use ($cutoff) {
                        // Multi-day events that started earlier but are still running. The
                        // duration >= 24 guard mirrors Event::getIsMultiDayAttribute().
                        $q2->where('duration', '>=', 24)
                            ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [$cutoff]);
                    });
            });
    }

    /**
     * Events missing a translation. Unscoped, this is gated on the schedules attached to the event
     * wanting a translation at all - without that gate the pass loads every event on the platform.
     * An event-scoped call skips every gate deliberately: it is the operator escape hatch, so naming
     * an event by id translates it even if it is long past.
     */
    public static function events(?int $eventId = null, ?int $roleId = null): Builder
    {
        $query = Event::query()
            ->where(function ($query) {
                $query->where(fn ($q) => $q->whereNotNull('name')->where('name', '!=', '')->whereNull('name_en'))
                    ->orWhere(fn ($q) => $q->whereNotNull('description')->where('description', '!=', '')->whereNull('description_en'))
                    ->orWhere(fn ($q) => $q->whereNotNull('short_description')->where('short_description', '!=', '')->whereNull('short_description_en'));
            });

        if ($eventId) {
            return $query->where('id', $eventId);
        }

        self::restrictToLiveEvents($query);

        if ($roleId) {
            return $query->whereHas('roles', fn ($q) => $q->where('roles.id', $roleId));
        }

        // A superset of what actually needs translating: Event::getTranslationLanguageCode()
        // resolves the venue first and only then the first talent, which SQL cannot express. The
        // source == target check in the command remains the correctness guard.
        return $query->whereHas('roles', fn ($q) => $q->whereColumn('roles.language_code', '!=', 'roles.translation_language_code'));
    }

    /**
     * Curator pivot rows missing a translation. A curator shows aggregated events in its own
     * authored language, so these are governed by the curator, not by the event's venue.
     */
    public static function curatorEvents(?int $eventId = null, ?int $roleId = null): Builder
    {
        $query = EventRole::query()
            ->whereHas('role', fn ($q) => $q->where('type', 'curator'))
            ->where(function ($query) {
                $query->where(fn ($q) => $q->whereNull('name_translated')
                    ->whereHas('event', fn ($e) => $e->whereNotNull('name')->where('name', '!=', '')))
                    ->orWhere(fn ($q) => $q->whereNull('description_translated')
                        ->whereHas('event', fn ($e) => $e->whereNotNull('description')->where('description', '!=', '')))
                    ->orWhere(fn ($q) => $q->whereNull('short_description_translated')
                        ->whereHas('event', fn ($e) => $e->whereNotNull('short_description')->where('short_description', '!=', '')));
            });

        if ($eventId) {
            return $query->where('event_id', $eventId);
        }

        // The pivot carries no dates, so the liveness test has to join to the parent event.
        $query->whereHas('event', fn ($q) => self::restrictToLiveEvents($q));

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        return $query;
    }

    /**
     * Agenda parts missing a translation, gated on the same roles the target is resolved from.
     * Gating on the event's creator role instead - which does not decide the target - both selects
     * parts with nothing to do and skips parts that need translating.
     */
    public static function eventParts(?int $eventId = null, ?int $roleId = null): Builder
    {
        $query = EventPart::query()
            ->where(function ($query) {
                $query->where(fn ($q) => $q->whereNotNull('name')->where('name', '!=', '')->whereNull('name_en'))
                    ->orWhere(fn ($q) => $q->whereNotNull('description')->where('description', '!=', '')->whereNull('description_en'));
            });

        if ($eventId) {
            return $query->where('event_id', $eventId);
        }

        // event_parts stores only clock times (start_time / end_time varchars), never dates, so the
        // liveness test has to join to the parent event.
        $query->whereHas('event', fn ($q) => self::restrictToLiveEvents($q));

        if ($roleId) {
            return $query->whereHas('event.roles', fn ($q) => $q->where('roles.id', $roleId));
        }

        return $query->whereHas('event.roles', fn ($q) => $q->whereColumn('roles.language_code', '!=', 'roles.translation_language_code'));
    }

    /**
     * What the cron still has to get through, per pass.
     *
     * The stuck-records panel only surfaces rows that failed repeatedly. A row sitting at zero
     * attempts that the cron simply never reaches is invisible there, which is exactly how a
     * schedule can stay untranslated for weeks with nothing reported.
     *
     * `pending` is CONFIRMED work: every row in it has a source value and an empty translation
     * column beside it, so it falls to zero when the queue drains. `recheck` is the rest of what
     * the roles pass must still open - rows selected only by the coarse JSON prefilter, which
     * cannot be resolved in SQL. Keeping them apart is the whole point: counted together, any
     * schedule with custom fields, labels, categories or sponsor logos held the total above zero
     * for ever, and a number that never falls reads as a broken cron rather than as a caveat.
     * A recheck costs no AI call and no pause - Translate::markChecked() parks the row - so the
     * two are not the same kind of work and must not share a figure.
     *
     * Deliberately NOT scoped by Translate::applyRetryScope(): a row inside its 24h failure
     * cooldown is still work waiting, it is just not work the NEXT run will attempt. The
     * stuck-records panel on /admin/usage is what reports that distinction.
     *
     * @return array<string, array{label: string, pending: int, recheck: int, never_attempted: int, cooling_off: int, oldest: ?string}>
     */
    public static function backlog(): array
    {
        $passes = [
            // Counted on the text-only form, so `pending` means the same thing here as it does
            // for the three passes below, which have no JSON columns and so no ambiguity.
            'roles' => [
                'label' => __('messages.schedules'),
                'query' => fn () => self::roles(null, true),
                'recheck' => fn () => self::roles(),
            ],
            'events' => ['label' => __('messages.events'), 'query' => fn () => self::events()],
            'curator_events' => ['label' => __('messages.curator').' / '.__('messages.events'), 'query' => fn () => self::curatorEvents()],
            'event_parts' => ['label' => __('messages.agenda'), 'query' => fn () => self::eventParts()],
        ];

        $backlog = [];

        foreach ($passes as $key => $pass) {
            // One aggregate per pass, not three. Each of these carries a whereHas EXISTS over a
            // table with no index on the `_en` columns, so every extra count is another full scan
            // on a page that is already heavy.
            // cooling_off rides along in the SAME aggregate rather than costing a fifth scan.
            // It is the exact negation of Translate::applyRetryScope(): a row that has failed its
            // way to the ceiling and has not yet waited out the cutoff. A never-attempted row has
            // translation_attempts = 0 and so cannot match, which is what keeps the NULL
            // last_translated_at out of the comparison.
            //
            // Reported, never subtracted. It is still work waiting - it comes back - but it is
            // provably not work the next run will attempt, and a drain rate applied to a figure
            // with a parked slice inside it produces an ETA that never arrives.
            [$threshold, $cutoff] = self::retryLimits();

            $cooling = $threshold <= 0
                ? '0'
                : ($cutoff === null
                    ? 'translation_attempts >= ?'
                    : '(translation_attempts >= ? AND last_translated_at IS NOT NULL AND last_translated_at >= ?)');

            $bindings = $threshold <= 0 ? [] : ($cutoff === null ? [$threshold] : [$threshold, $cutoff]);

            $row = $pass['query']()->selectRaw(
                'COUNT(*) as pending, SUM(last_translated_at IS NULL) as never_attempted,'
                ." SUM({$cooling}) as cooling_off, MIN(last_translated_at) as oldest",
                $bindings
            )->first();

            $pending = (int) $row->pending;

            // Only the roles pass has a coarse arm to subtract, and `roles` is the one table here
            // reached without a whereHas EXISTS - one row per schedule, no join - so the second
            // scan is the cheapest on the list rather than a doubling of the page's cost.
            $recheck = isset($pass['recheck'])
                ? max(0, $pass['recheck']()->count() - $pending)
                : 0;

            $backlog[$key] = [
                'label' => $pass['label'],
                'pending' => $pending,
                'recheck' => $recheck,
                'never_attempted' => (int) $row->never_attempted,
                'cooling_off' => (int) $row->cooling_off,
                'oldest' => $row->oldest,
            ];
        }

        return $backlog;
    }
}
