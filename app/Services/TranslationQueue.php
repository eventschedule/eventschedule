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
     * Schedules with untranslated content, restricted to those whose authored language differs
     * from their target. Both language columns are NOT NULL DEFAULT 'en', so nothing is lost to a
     * NULL comparison here.
     */
    public static function roles(?int $roleId = null): Builder
    {
        $query = Role::query()
            ->whereColumn('language_code', '!=', 'translation_language_code')
            ->where(function ($query) {
                $query->where(fn ($q) => $q->whereNotNull('name')->where('name', '!=', '')->whereNull('name_en'))
                    ->orWhere(fn ($q) => $q->whereNotNull('description')->where('description', '!=', '')->whereNull('description_en'))
                    ->orWhere(fn ($q) => $q->whereNotNull('short_description')->where('short_description', '!=', '')->whereNull('short_description_en'))
                    ->orWhere(fn ($q) => $q->whereNotNull('address1')->where('address1', '!=', '')->whereNull('address1_en'))
                    ->orWhere(fn ($q) => $q->whereNotNull('city')->where('city', '!=', '')->whereNull('city_en'))
                    ->orWhere(fn ($q) => $q->whereNotNull('state')->where('state', '!=', '')->whereNull('state_en'))
                    ->orWhere(fn ($q) => $q->whereNotNull('request_terms')->where('request_terms', '!=', '')->whereNull('request_terms_en'))
                    ->orWhere(fn ($q) => $q->whereNotNull('banner_message')->where('banner_message', '!=', '')->whereNull('banner_message_en'))
                    ->orWhere(fn ($q) => $q->whereNotNull('sponsor_section_title')->where('sponsor_section_title', '!=', '')->whereNull('sponsor_section_title_en'))
                    // Coarse prefilter: the translation lives beside the source under an `_en`
                    // sub-key, which SQL cannot check cheaply (sponsor_logos is a string column
                    // holding JSON; the rest are cast arrays). These stay true once the column is
                    // set, so the command re-checks in PHP and parks rows with nothing to do.
                    ->orWhere(fn ($q) => $q->whereNotNull('event_custom_fields'))
                    ->orWhere(fn ($q) => $q->whereNotNull('custom_labels'))
                    ->orWhere(fn ($q) => $q->whereNotNull('event_categories'))
                    ->orWhere(fn ($q) => $q->whereNotNull('sponsor_logos')->where('sponsor_logos', '!=', '[]'));
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
     * @return array<string, array{label: string, pending: int, never_attempted: int, oldest: ?\Illuminate\Support\Carbon}>
     */
    public static function backlog(): array
    {
        $passes = [
            'roles' => ['label' => __('messages.schedules'), 'query' => fn () => self::roles()],
            'events' => ['label' => __('messages.events'), 'query' => fn () => self::events()],
            'curator_events' => ['label' => __('messages.curator').' / '.__('messages.events'), 'query' => fn () => self::curatorEvents()],
            'event_parts' => ['label' => __('messages.agenda'), 'query' => fn () => self::eventParts()],
        ];

        $backlog = [];

        foreach ($passes as $key => $pass) {
            // One aggregate per pass, not three. Each of these carries a whereHas EXISTS over a
            // table with no index on the `_en` columns, so every extra count is another full scan
            // on a page that is already heavy.
            $row = $pass['query']()->selectRaw(
                'COUNT(*) as pending, SUM(last_translated_at IS NULL) as never_attempted, MIN(last_translated_at) as oldest'
            )->first();

            $backlog[$key] = [
                'label' => $pass['label'],
                'pending' => (int) $row->pending,
                'never_attempted' => (int) $row->never_attempted,
                'oldest' => $row->oldest,
            ];
        }

        return $backlog;
    }
}
