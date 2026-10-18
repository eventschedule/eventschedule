<?php

namespace App\Services;

use App\Jobs\SyncEventToGoogleCalendar;
use App\Models\CalendarImportHold;
use App\Models\CalendarSyncSetting;
use App\Models\Event;
use App\Models\Role;
use App\Utils\CalendarPrivacy;
use App\Utils\GoogleImportUtils;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * How an event a standing calendar sync reads in (Google, Outlook, CalDAV) arrives on the
 * schedule, and when it is taken off the public page again.
 *
 * Until 2026-10 a sync put everything it read straight onto the public page, and one schedule
 * published somebody's whole work calendar that way. So:
 *
 *  - What the calendar itself says is not for publishing is not read in: an entry marked private,
 *    an invitation the calendar's owner declined, an out-of-office block. One already read in is
 *    hidden when the calendar starts saying so, which is the owner's own later word.
 *  - What is read in arrives as a draft, unless the owner said "publish as they arrive" for that
 *    calendar (publishes(), bound to the owner and the calendar it was said for).
 *  - Meeting details or an email thread (CalendarPrivacy) hold an entry back even then, until the
 *    owner publishes it; a decision covers the text it was made on.
 *  - The sync only ever puts back what it hid itself, and never touches an event people hold a
 *    ticket to.
 *
 * Every hold is a row in calendar_import_holds, which is what the schedule's notice, its review
 * page and the operator's page read.
 */
class CalendarArrival
{
    public const GOOGLE = Event::IMPORT_GOOGLE;

    public const MICROSOFT = Event::IMPORT_MICROSOFT;

    public const CALDAV = Event::IMPORT_CALDAV;

    public const PROVIDERS = [self::GOOGLE, self::MICROSOFT, self::CALDAV];

    /** What the calendar says about an entry. */
    public const SOURCE_PRIVATE = 'private';

    public const SOURCE_DECLINED = 'declined';

    public const SOURCE_CANCELLED = 'cancelled';

    public const SOURCE_NOT_AN_EVENT = 'not_an_event';

    /** An entry that arrived as a draft for the owner to look at. */
    public const HOLD_REVIEW = 'review';

    public const DECISION_PUBLISHED = 'published';

    public const DECISION_HIDDEN = 'hidden';

    /** The reason went away and the sync put the event back by itself. */
    public const DECISION_RELEASED = 'released';

    private static bool $tablesReady = false;

    /**
     * Whether the tables are in the database yet. The scheduler worker starts on new code while
     * the web container is still migrating; until then a sync behaves exactly as it always did.
     * Only "yes" is remembered (see Event::importColumnsReady()).
     */
    public static function tablesReady(): bool
    {
        return self::$tablesReady = self::$tablesReady
            || (Schema::hasTable('calendar_sync_settings') && Schema::hasTable('calendar_import_holds'));
    }

    // What the calendar says --------------------------------------------------------------

    /**
     * Google, from the inbound array (GoogleCalendarService::mapGoogleEvent()).
     *
     * @param  array<string, mixed>  $data
     */
    public static function googleReason(array $data): ?string
    {
        if (in_array($data['visibility'] ?? null, ['private', 'confidential'], true)) {
            return self::SOURCE_PRIVATE;
        }

        if (! empty($data['declined'])) {
            return self::SOURCE_DECLINED;
        }

        $type = $data['event_type'] ?? null;
        if (is_string($type) && $type !== '' && ! in_array($type, GoogleImportUtils::EVENT_TYPES, true)) {
            return self::SOURCE_NOT_AN_EVENT;
        }

        return null;
    }

    /**
     * Outlook, from a Graph event. A field the answer leaves out reads as nothing to say.
     *
     * @param  array<string, mixed>  $item
     */
    public static function graphReason(array $item): ?string
    {
        if (! empty($item['isCancelled'])) {
            return self::SOURCE_CANCELLED;
        }

        if (in_array(strtolower((string) ($item['sensitivity'] ?? '')), ['private', 'confidential', 'personal'], true)) {
            return self::SOURCE_PRIVATE;
        }

        if (strtolower((string) ($item['responseStatus']['response'] ?? '')) === 'declined') {
            return self::SOURCE_DECLINED;
        }

        if (in_array(strtolower((string) ($item['showAs'] ?? '')), ['oof', 'workingelsewhere'], true)) {
            return self::SOURCE_NOT_AN_EVENT;
        }

        return null;
    }

    /**
     * CalDAV, from CalDAVService::parseVEvent(). The owner's own reply is the attendee whose
     * address is the connection's username, which only says something when that is an address.
     *
     * @param  array<string, mixed>  $data
     */
    public static function calDavReason(array $data, ?string $username): ?string
    {
        if (strtoupper((string) ($data['status'] ?? '')) === 'CANCELLED') {
            return self::SOURCE_CANCELLED;
        }

        if (in_array(strtoupper((string) ($data['class'] ?? '')), ['PRIVATE', 'CONFIDENTIAL'], true)) {
            return self::SOURCE_PRIVATE;
        }

        $me = mb_strtolower(trim((string) $username));
        if ($me !== '' && str_contains($me, '@')) {
            foreach ($data['attendees'] ?? [] as $email => $partstat) {
                if (mb_strtolower((string) $email) === $me && strtoupper((string) $partstat) === 'DECLINED') {
                    return self::SOURCE_DECLINED;
                }
            }
        }

        return null;
    }

    // The owner's consent -------------------------------------------------------------------

    /**
     * What a "publish as they arrive" is for: this owner, this calendar. A change of either, by
     * whichever path makes it, falls back to drafts.
     */
    public static function binding(Role $role, string $provider): string
    {
        $calendar = match ($provider) {
            self::GOOGLE => (string) $role->getGoogleCalendarId(),
            self::MICROSOFT => $role->getMicrosoftCalendarId() ?: 'default',
            self::CALDAV => (function () use ($role) {
                $settings = $role->getCalDAVSettings() ?: [];

                return ($settings['calendar_url'] ?? '').'|'.($settings['username'] ?? '');
            })(),
            default => '',
        };

        return sha1($role->user_id.'|'.$provider.'|'.$calendar);
    }

    public static function settings(Role $role): CalendarSyncSetting
    {
        if (! self::tablesReady()) {
            return new CalendarSyncSetting(['role_id' => $role->id]);
        }

        return CalendarSyncSetting::where('role_id', $role->id)->first()
            ?? new CalendarSyncSetting(['role_id' => $role->id]);
    }

    /**
     * Whether what this sync reads in goes straight onto the public page. Before the tables
     * exist, yes, as it always did; after, only with the owner's word for this calendar.
     */
    public static function publishes(Role $role, string $provider): bool
    {
        if (! self::tablesReady()) {
            return true;
        }

        $row = CalendarSyncSetting::where('role_id', $role->id)->first();
        if (! $row || ! $row->{"{$provider}_publishes"}) {
            return false;
        }

        $binding = self::binding($role, $provider);
        $stored = $row->{"{$provider}_publish_binding"};

        // A sync that was publishing when this shipped carried its consent over with no calendar
        // named. It is the calendar it reads now that it was publishing.
        if ($stored === null) {
            $row->forceFill(["{$provider}_publish_binding" => $binding])->save();

            return true;
        }

        return hash_equals($stored, $binding);
    }

    /** Binds a carried-over consent to the calendar now being read. The syncs call it as they start. */
    public static function bindCarriedOver(Role $role, string $provider): void
    {
        if (self::tablesReady()) {
            self::publishes($role, $provider);
        }
    }

    /** The owner's own word: publish what this calendar sends as it arrives, or not. */
    public static function setPublishing(Role $role, string $provider, bool $on): void
    {
        if (! self::tablesReady()) {
            return;
        }

        self::writeSettings($role, [
            "{$provider}_publishes" => $on,
            "{$provider}_publish_binding" => $on ? self::binding($role, $provider) : null,
            "{$provider}_publish_confirmed_at" => $on ? now() : null,
        ]);
    }

    /**
     * Writes columns of the schedule's settings row, making the row first if there is none. Not
     * updateOrCreate(): the Google and Outlook reads of one schedule run under different locks,
     * and two of them making the row at once is a duplicate key, after the token was stored.
     *
     * @param  array<string, mixed>  $values
     */
    private static function writeSettings(Role $role, array $values): void
    {
        CalendarSyncSetting::query()->insertOrIgnore(['role_id' => $role->id, 'created_at' => now(), 'updated_at' => now()]);
        CalendarSyncSetting::where('role_id', $role->id)->firstOrFail()->forceFill($values)->save();
    }

    /** Takes the consent back, for one provider or all: a pause, a disconnect, a change of owner. */
    public static function resetConsent(Role $role, ?string $provider = null): void
    {
        if (! self::tablesReady()) {
            return;
        }

        $values = [];
        foreach ($provider ? [$provider] : self::PROVIDERS as $p) {
            $values["{$p}_publishes"] = false;
            $values["{$p}_publish_binding"] = null;
            $values["{$p}_publish_confirmed_at"] = null;
        }

        CalendarSyncSetting::where('role_id', $role->id)->update($values);
    }

    /** The same, on every schedule an account owns: the account disconnected the calendar. */
    public static function resetConsentForOwner(int $userId, string $provider): void
    {
        if (! self::tablesReady()) {
            return;
        }

        CalendarSyncSetting::whereIn('role_id', Role::where('user_id', $userId)->select('id'))->update([
            "{$provider}_publishes" => false,
            "{$provider}_publish_binding" => null,
            "{$provider}_publish_confirmed_at" => null,
        ]);
    }

    /**
     * Stops reading a calendar for the owner, keeping what it pushes out: "from" becomes off and
     * "both" becomes "to". $reason says why (operator, transfer), for the row that explains it,
     * and what it was is kept for Resume. Returns the providers that were reading.
     *
     * @return list<string>
     */
    public static function stopReading(Role $role, string $reason): array
    {
        $columns = [self::GOOGLE => 'sync_direction', self::MICROSOFT => 'microsoft_sync_direction', self::CALDAV => 'caldav_sync_direction'];
        $stopped = [];
        $directions = [];
        $settings = [];

        foreach ($columns as $provider => $column) {
            $direction = $role->{$column};
            if (! in_array($direction, ['from', 'both'], true)) {
                continue;
            }

            $directions[$column] = $direction === 'both' ? 'to' : null;
            $stopped[] = $provider;
            $settings["{$provider}_stopped_reason"] = $reason;
            $settings["{$provider}_stopped_at"] = now();
            $settings["{$provider}_stopped_direction"] = $direction;
            $settings["{$provider}_publishes"] = false;
            $settings["{$provider}_publish_binding"] = null;
            $settings["{$provider}_publish_confirmed_at"] = null;
        }

        if ($stopped) {
            $role->writeOperationalColumns($directions);
            if (self::tablesReady()) {
                self::writeSettings($role, $settings);
            }
        }

        return $stopped;
    }

    /**
     * Puts back what stopReading() turned off for $reason, when the owner has not chosen a
     * direction of their own since. New entries then arrive as drafts.
     *
     * @return list<string>
     */
    public static function resumeReading(Role $role, string $reason): array
    {
        if (! self::tablesReady()) {
            return [];
        }

        $row = CalendarSyncSetting::where('role_id', $role->id)->first();
        if (! $row) {
            return [];
        }

        $columns = [self::GOOGLE => 'sync_direction', self::MICROSOFT => 'microsoft_sync_direction', self::CALDAV => 'caldav_sync_direction'];
        $resumed = [];
        $directions = [];
        $settings = [];

        foreach ($columns as $provider => $column) {
            if ($row->{"{$provider}_stopped_reason"} !== $reason) {
                continue;
            }

            $was = $row->{"{$provider}_stopped_direction"};
            $now = $role->{$column};
            $expected = $was === 'both' ? 'to' : null;

            if ($now === $expected && in_array($was, ['from', 'both'], true)) {
                $directions[$column] = $was;
                $resumed[] = $provider;
            }

            $settings["{$provider}_stopped_reason"] = null;
            $settings["{$provider}_stopped_at"] = null;
            $settings["{$provider}_stopped_direction"] = null;
        }

        if ($directions) {
            $role->writeOperationalColumns($directions);
        }
        if ($settings) {
            $row->forceFill($settings)->save();
        }

        return $resumed;
    }

    /** Clears the "stopped" note once the owner chooses a direction themselves. */
    public static function clearStopped(Role $role, string $provider): void
    {
        if (! self::tablesReady()) {
            return;
        }

        CalendarSyncSetting::where('role_id', $role->id)->update([
            "{$provider}_stopped_reason" => null,
            "{$provider}_stopped_at" => null,
            "{$provider}_stopped_direction" => null,
        ]);
    }

    // What the schedule's own pages say ------------------------------------------------------

    /**
     * Whether a sync reads the account's main calendar: Google's 'primary' (or no calendar chosen,
     * which falls back to it), an id that is the account's own address rather than a calendar
     * made for something, or Outlook's default (no calendar chosen). Where the dentist and the
     * team meetings are.
     */
    public static function isMainCalendar(Role $role, string $provider): bool
    {
        return match ($provider) {
            self::GOOGLE => (function () use ($role) {
                $id = (string) $role->getGoogleCalendarId();

                return $id === 'primary'
                    || (str_contains($id, '@') && ! preg_match('~@(group|import|resource)\.calendar\.google\.com$~i', $id));
            })(),
            self::MICROSOFT => $role->getMicrosoftCalendarId() === null,
            default => false,
        };
    }

    /** The providers this schedule reads a calendar from now. */
    public static function readingProviders(Role $role): array
    {
        return array_values(array_filter([
            $role->syncsFromGoogle() ? self::GOOGLE : null,
            $role->syncsFromMicrosoft() ? self::MICROSOFT : null,
            $role->syncsFromCalDAV() ? self::CALDAV : null,
        ]));
    }

    /**
     * What the schedule's Schedule tab says about its calendar syncs, or null when there is nothing
     * to say: entries waiting as drafts (and how many of those are held for their text), entries
     * taken off the public page with the first few of them, the very first read's counts, and a
     * sync carried over from before this that publishes the account's main calendar and was never
     * confirmed by the owner (asked once).
     *
     * @return array<string, mixed>|null
     */
    public static function notice(Role $role): ?array
    {
        if (! self::tablesReady()) {
            return null;
        }

        $open = fn () => CalendarImportHold::where('calendar_import_holds.role_id', $role->id)
            ->whereNull('calendar_import_holds.decided_at')
            ->whereHas('event', fn ($q) => $q->where('creator_role_id', $role->id)->where('is_draft', true)->where('is_cancelled', false));

        $waiting = $open()->where('was_public', false)->count();
        $held = $waiting ? $open()->where('was_public', false)->whereIn('reason', [CalendarPrivacy::HOLD_MEETING_DETAILS, CalendarPrivacy::HOLD_EMAIL_THREAD])->count() : 0;
        $taken = $open()->where('was_public', true)->whereNull('held_by')->count();
        $takenEvents = $taken
            ? $open()->where('was_public', true)->whereNull('held_by')->with('event')->orderBy('id')->limit(3)->get()->pluck('event')->filter()->values()
            : collect();
        // Taken off by the operator (/admin/private-calendars), said apart: the sync does not put
        // those back by itself.
        $hiddenByAdmin = $open()->where('was_public', true)->whereNotNull('held_by')->count();

        $settings = self::settings($role);
        $provider = collect(self::readingProviders($role))->first()
            ?? CalendarImportHold::where('calendar_import_holds.role_id', $role->id)->join('events', 'events.id', '=', 'calendar_import_holds.event_id')->value('events.import_source')
            ?? self::GOOGLE;

        $confirm = collect(self::readingProviders($role))->first(fn ($p) => $settings->{"{$p}_publishes"}
            && $settings->{"{$p}_publish_confirmed_at"} === null
            && self::isMainCalendar($role, $p));

        if (! $waiting && ! $taken && ! $hiddenByAdmin && ! $confirm) {
            return null;
        }

        return [
            'provider' => $provider,
            'waiting' => $waiting,
            'held' => $held,
            'review' => $waiting - $held,
            'taken' => $taken,
            'taken_events' => $takenEvents,
            'hidden_by_admin' => $hiddenByAdmin,
            'first_pull' => $settings->{"{$provider}_first_pull"},
            'main' => self::isMainCalendar($role, $provider),
            'publishes' => self::publishes($role, $provider),
            'confirm' => $confirm,
        ];
    }

    /**
     * "Google Calendar", "Outlook Calendar", "CalDAV Calendar", as the Integrations rows name them;
     * "your calendar" for anything else (a feed, a one-time import), which is not Google's.
     */
    public static function providerName(?string $provider): string
    {
        return match ($provider) {
            self::GOOGLE => 'Google Calendar',
            self::MICROSOFT => __('messages.microsoft_calendar'),
            self::CALDAV => __('messages.caldav_calendar'),
            default => __('messages.calendar_fallback_name'),
        };
    }

    // The one full read ----------------------------------------------------------------------

    /** At most this many full reads start in one run of a sync command... */
    public const REREADS_PER_RUN = 10;

    /** ...and none starts once the run has been going this long, so neither cron rail runs over. */
    public const REREAD_BUDGET_SECONDS = 120;

    /**
     * Inside a web request (the /translate_data cron rail) the three syncs run one after another in
     * the same request, which a server's request timeout can kill: there, one full read in the whole
     * request, and a read that saves its place sooner.
     */
    private const WEB_REREAD_SECONDS = 20;

    private static int $rereadsThisRequest = 0;

    /** Whether one more full read may start in this process: always on the console rail. */
    public static function claimReread(): bool
    {
        if (app()->runningInConsole()) {
            return true;
        }

        if (self::$rereadsThisRequest >= 1) {
            return false;
        }

        self::$rereadsThisRequest++;

        return true;
    }

    /** How long the commands go on starting full reads in one run. */
    public static function rereadBudgetSeconds(): int
    {
        return app()->runningInConsole() ? self::REREAD_BUDGET_SECONDS : self::WEB_REREAD_SECONDS;
    }

    /**
     * How long one full read may run before it saves its place (rereadCursor()) and stops, to go
     * on in the next run.
     */
    public static function rereadPageSeconds(): int
    {
        return app()->runningInConsole() ? self::REREAD_BUDGET_SECONDS : self::WEB_REREAD_SECONDS;
    }

    /**
     * Whether this sync still owes its calendar one full read. Incremental reads (Google's sync
     * token, Outlook's delta link, CalDAV's etags) only ever see what changed, so the filters
     * above would never reach an entry read in before them. A read that starts from nothing
     * counts as the full read too.
     */
    public static function needsReread(Role $role, string $provider): bool
    {
        if (! self::tablesReady()) {
            return false;
        }

        return CalendarSyncSetting::where('role_id', $role->id)->value("{$provider}_reread_at") === null;
    }

    /**
     * Where an unfinished full read resumes (a page cursor), or null to start from the top. Only
     * for the calendar and the owner it was saved for: a Graph link names the calendar it walks,
     * and resuming one after the owner chose another calendar read the old one and went on
     * following it.
     */
    public static function rereadCursor(Role $role, string $provider): ?string
    {
        if (! self::tablesReady()) {
            return null;
        }

        $saved = json_decode((string) CalendarSyncSetting::where('role_id', $role->id)->value("{$provider}_reread_cursor"), true);

        return is_array($saved) && ($saved['binding'] ?? null) === self::binding($role, $provider) && is_string($saved['value'] ?? null)
            ? $saved['value']
            : null;
    }

    public static function saveRereadCursor(Role $role, string $provider, ?string $cursor): void
    {
        if (self::tablesReady()) {
            self::writeSettings($role, ["{$provider}_reread_cursor" => $cursor === null ? null : json_encode([
                'binding' => self::binding($role, $provider),
                'value' => $cursor,
            ])]);
        }
    }

    public static function finishReread(Role $role, string $provider): void
    {
        if (self::tablesReady()) {
            self::writeSettings($role, [
                "{$provider}_reread_at" => now(),
                "{$provider}_reread_cursor" => null,
            ]);
        }
    }

    // Arrival ------------------------------------------------------------------------------

    /**
     * How a new entry arrives: the visibility state to save it in, and the hold that keeps it
     * waiting for the owner (null when it goes straight on the page).
     *
     * would_be is where a held entry goes once its reason is gone: the page, for a sync that
     * publishes; nowhere (null) for one whose entries wait for the owner anyway.
     *
     * @param  array<string, mixed>  $findings  CalendarPrivacy::findings()
     * @return array{state: string, hold: ?string, would_be: ?string}
     */
    public static function arrival(Role $role, string $provider, array $findings): array
    {
        if (! self::tablesReady()) {
            return ['state' => 'public', 'hold' => null, 'would_be' => null];
        }

        $state = self::publishes($role, $provider) ? $role->defaultEventVisibility() : 'draft';

        if ($reason = CalendarPrivacy::holdReason($findings)) {
            return ['state' => 'draft', 'hold' => $reason, 'would_be' => $state === 'draft' ? null : $state];
        }

        return ['state' => $state, 'hold' => $state === 'draft' ? self::HOLD_REVIEW : null, 'would_be' => null];
    }

    /**
     * Writes the hold for an entry that arrived held.
     *
     * @param  array{state: string, hold: ?string, would_be: ?string}  $arrival
     */
    public static function holdOnArrival(Event $event, Role $role, array $arrival, bool $firstPull): void
    {
        if ($arrival['hold'] === null || ! self::tablesReady()) {
            return;
        }

        self::writeHold($event, $role, $arrival['hold'], $arrival['would_be'], $firstPull, null, false);
    }

    /**
     * After the sync updated an entry it read in: hide it when the calendar now says it is not
     * for publishing, or its text now carries meeting details or an email thread the owner has not
     * published as it stands; put it back when the reason has gone and the hide was the sync's own.
     * True when it changed what the page shows.
     */
    public static function reconsider(Event $event, Role $role, string $provider, ?string $sourceReason): bool
    {
        if (! self::tablesReady() || ! $event->cameFromCalendarSync($provider, $role) || $event->is_cancelled) {
            return false;
        }

        $hold = CalendarImportHold::where('event_id', $event->id)->first();

        // An Unlisted event pushed out from here is marked private in the calendar: that is our
        // own setting coming back, not the owner's word about the entry. So is one that was
        // Unlisted when it was hidden: it is a draft now, and still marked private there.
        if ($sourceReason === self::SOURCE_PRIVATE
            && ($event->is_private || ($hold && $hold->isOpen() && $hold->was_visibility === 'unlisted'))) {
            $sourceReason = null;
        }

        $content = CalendarPrivacy::holdReason(CalendarPrivacy::findings($event->name, $event->description, null));
        $published = $hold && $hold->decision === self::DECISION_PUBLISHED
            && $hold->decided_hash === self::decisionHash($event);

        $reason = $sourceReason ?? ($content && ! $published ? $content : null);

        if ($reason !== null) {
            // Already a draft: nothing to take off the page, but a draft that waited only for a
            // look now waits for more, and Publish all must leave it out.
            if ($event->is_draft) {
                if ($hold && $hold->isOpen() && $hold->reason === self::HOLD_REVIEW) {
                    $hold->forceFill(['reason' => $reason])->save();
                }

                return false;
            }

            return self::hide($event, $role, $reason, null);
        }

        return self::restore($event, $hold, null);
    }

    /**
     * Takes an event off the public page and holds it, with what it was so it can be put back.
     * $heldBy is the operator for an operator's hide. Never an event people hold a ticket to.
     */
    public static function hide(Event $event, Role $role, string $reason, ?int $heldBy): bool
    {
        if (! self::tablesReady() || $event->is_draft || $event->is_cancelled || TicketPresets::liveSaleCount($event) > 0) {
            return false;
        }

        $was = $event->visibilityState();
        $password = $event->event_password;
        $event->setVisibilityState('draft');
        // An Unlisted event keeps its password while it is hidden: put back without it, its link
        // would open for anybody.
        if ($was === 'unlisted') {
            $event->event_password = $password;
        }
        $event->save();

        self::writeHold($event, $role, $reason, $was, false, $heldBy, true);
        self::pushToMembers($event, 'delete');

        return true;
    }

    /**
     * Puts back what was hidden, when the hold is still undecided and is the one $heldBy wrote
     * (null: the sync's). A hold that arrived as a draft has nothing to put back.
     */
    public static function restore(Event $event, ?CalendarImportHold $hold, ?int $heldBy): bool
    {
        if (! $hold || ! $hold->isOpen() || ! $event->is_draft) {
            return false;
        }

        if (($heldBy === null) !== ($hold->held_by === null)) {
            return false;
        }

        // Arrived held: with the reason gone it goes where it would have gone, but only while the
        // sync still publishes that calendar. Otherwise (and on a sync whose entries wait anyway)
        // it is an ordinary draft to look at.
        if (! $hold->was_public) {
            $role = Role::find($hold->role_id);
            $stillPublishes = $hold->was_visibility !== null && $role && self::publishes($role, (string) $event->import_source);

            if (! $stillPublishes) {
                if ($hold->reason !== self::HOLD_REVIEW) {
                    $hold->forceFill(['reason' => self::HOLD_REVIEW])->save();
                }

                return false;
            }
        }

        $event->setVisibilityState($hold->was_visibility);
        $event->save();
        // The hold stays, settled, so a first read's backlog that comes back is still not news to
        // followers (SendEventAnnouncements reads first_pull off it).
        $hold->forceFill(['decision' => self::DECISION_RELEASED, 'decided_at' => now(), 'decided_by' => null, 'decided_hash' => null])->save();
        self::pushToMembers($event, 'create');

        return true;
    }

    /**
     * The owner published a held event, or chose to keep it hidden. Called by the publish paths
     * and the review page.
     */
    public static function decide(Event $event, ?int $userId, string $decision): void
    {
        if (! self::tablesReady() || ! $event->exists) {
            return;
        }

        $query = CalendarImportHold::where('event_id', $event->id);

        // Publishing is the owner's last word whatever was said before (an event kept hidden and
        // published later from its own form); keeping hidden answers only a hold still waiting.
        if ($decision !== self::DECISION_PUBLISHED) {
            $query->whereNull('decided_at');
        }

        $query->update([
            'decision' => $decision,
            'decided_at' => now(),
            'decided_by' => $userId,
            'decided_hash' => self::decisionHash($event),
            'updated_at' => now(),
        ]);
    }

    /**
     * Records what a read did, for the schedule's notice and the row's status line. The very
     * first read is kept as well: it is the one an owner is told about.
     *
     * @param  array<string, int>  $counts
     */
    public static function recordPull(Role $role, string $provider, array $counts, bool $firstPull): void
    {
        if (! self::tablesReady()) {
            return;
        }

        $summary = ['at' => now()->toIso8601String()];
        foreach (['created', 'held', 'private', 'declined', 'cancelled', 'not_an_event', 'places_left_out'] as $key) {
            $summary[$key] = (int) ($counts[$key] ?? 0);
        }

        $values = ["{$provider}_last_pull" => $summary];
        if ($firstPull) {
            $values["{$provider}_first_pull"] = $summary;
        }

        self::writeSettings($role, $values);

        self::tellOwnerOfTakenOff($role, $provider);
    }

    /**
     * Once per schedule: a read took events that were on the public page off it. The owner is
     * mailed what happened (nothing deleted, where to look), at their account's address and in
     * their language, by the platform and never through the schedule's own mail server.
     */
    public static function tellOwnerOfTakenOff(Role $role, string $provider): void
    {
        // Every read ends here: once told, nothing to count.
        if (CalendarSyncSetting::where('role_id', $role->id)->whereNotNull('taken_off_notified_at')->exists()) {
            return;
        }

        $taken = CalendarImportHold::where('calendar_import_holds.role_id', $role->id)
            ->whereNull('decided_at')
            ->where('was_public', true)
            ->whereNull('held_by')
            ->whereHas('event', fn ($q) => $q->where('creator_role_id', $role->id)->where('is_draft', true));

        $count = (clone $taken)->count();
        $owner = $role->user;
        if ($count === 0 || ! $owner || ! $owner->email || $owner->isBlocked()) {
            return;
        }

        // Claimed atomically: two reads finishing together must not mail twice.
        $claimed = CalendarSyncSetting::where('role_id', $role->id)->whereNull('taken_off_notified_at')->update(['taken_off_notified_at' => now()]);
        if (! $claimed) {
            return;
        }

        $names = (clone $taken)->with('event')->orderBy('id')->limit(5)->get()->pluck('event.name')->filter()->values()->all();

        try {
            \App\Jobs\SendQueuedEmail::dispatch(
                new \App\Mail\CalendarTakenOffMail($role, $owner, $count, $names, self::providerName($provider)),
                $owner->email,
                null,
                $owner->language_code ?: null,
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Counts a skipped or held entry in a read's results, by reason. */
    public static function count(array &$results, ?string $reason): void
    {
        if ($reason === null) {
            return;
        }

        $key = in_array($reason, [self::SOURCE_PRIVATE, self::SOURCE_DECLINED, self::SOURCE_CANCELLED, self::SOURCE_NOT_AN_EVENT], true)
            ? $reason
            : 'held';

        $results[$key] = ($results[$key] ?? 0) + 1;
    }

    /** The text an owner's decision was made on. The location is not in it: see reconsider(). */
    private static function decisionHash(Event $event): string
    {
        return CalendarPrivacy::textHash($event->name, $event->description, null);
    }

    private static function writeHold(Event $event, Role $role, string $reason, ?string $was, bool $firstPull, ?int $heldBy, bool $wasPublic): void
    {
        $hold = CalendarImportHold::where('event_id', $event->id)->first();

        // A hold still waiting keeps its first reason and what the event was before it.
        if ($hold && $hold->isOpen()) {
            $hold->forceFill([
                'was_visibility' => $hold->was_visibility ?? $was,
                'was_public' => $hold->was_public || $wasPublic,
                'held_by' => $hold->held_by ?? $heldBy,
            ])->save();

            return;
        }

        CalendarImportHold::updateOrCreate(['event_id' => $event->id], [
            'role_id' => $role->id,
            'reason' => $reason,
            'was_visibility' => $was,
            'was_public' => $wasPublic,
            // A first read's event stays one when it is held again later.
            'first_pull' => $firstPull || (bool) $hold?->first_pull,
            'held_by' => $heldBy,
            'decision' => null,
            'decided_at' => null,
            'decided_by' => null,
            'decided_hash' => null,
        ]);
    }

    /**
     * Team members who keep the schedule in a calendar of their own have a copy of a public
     * event, which goes when it is hidden and comes back with it. The owner's own entry is the
     * original and is left alone (the push jobs' isOwnersOriginal()).
     */
    private static function pushToMembers(Event $event, string $action): void
    {
        // Read fresh: a caller may have loaded the event with its venues alone.
        foreach ($event->roles()->get() as $role) {
            foreach ($role->getMembersWithCalendarSync() as $member) {
                if (! $member->google_token) {
                    continue;
                }

                try {
                    SyncEventToGoogleCalendar::dispatchSync($event, $role, $action, $member, $member->pivot->google_calendar_id);
                } catch (\Throwable $e) {
                    Log::warning('Calendar arrival: a team member\'s calendar was not updated', [
                        'event_id' => $event->id,
                        'action' => $action,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
