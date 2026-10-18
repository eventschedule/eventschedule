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

        CalendarSyncSetting::updateOrCreate(['role_id' => $role->id], [
            "{$provider}_publishes" => $on,
            "{$provider}_publish_binding" => $on ? self::binding($role, $provider) : null,
            "{$provider}_publish_confirmed_at" => $on ? now() : null,
        ]);
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
                CalendarSyncSetting::updateOrCreate(['role_id' => $role->id], $settings);
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

        self::writeHold($event, $role, $arrival['hold'], $arrival['would_be'], $firstPull, null);
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

        // An Unlisted event pushed out from here is marked private in the calendar: that is our
        // own setting coming back, not the owner's word about the entry.
        if ($sourceReason === self::SOURCE_PRIVATE && $event->is_private) {
            $sourceReason = null;
        }

        $hold = CalendarImportHold::where('event_id', $event->id)->first();
        $content = CalendarPrivacy::holdReason(CalendarPrivacy::findings($event->name, $event->description, null));
        $published = $hold && $hold->decision === self::DECISION_PUBLISHED
            && $hold->decided_hash === self::decisionHash($event);

        $reason = $sourceReason ?? ($content && ! $published ? $content : null);

        if ($reason !== null) {
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
        $event->setVisibilityState('draft');
        $event->save();

        self::writeHold($event, $role, $reason, $was, false, $heldBy);
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

        // Arrived held, on a sync whose entries wait for the owner anyway: with the details gone
        // it is an ordinary draft to look at.
        if ($hold->was_visibility === null) {
            if ($hold->reason !== self::HOLD_REVIEW && in_array($hold->reason, [CalendarPrivacy::HOLD_MEETING_DETAILS, CalendarPrivacy::HOLD_EMAIL_THREAD], true)) {
                $hold->forceFill(['reason' => self::HOLD_REVIEW])->save();
            }

            return false;
        }

        $event->setVisibilityState($hold->was_visibility);
        $event->save();
        $hold->delete();
        self::pushToMembers($event, 'create');

        return true;
    }

    /**
     * The owner published a held event, or chose to keep it hidden. Called by the publish paths
     * and the review page; a hold that is not open is left as it is.
     */
    public static function decide(Event $event, ?int $userId, string $decision): void
    {
        if (! self::tablesReady() || ! $event->exists) {
            return;
        }

        CalendarImportHold::where('event_id', $event->id)
            ->whereNull('decided_at')
            ->update([
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

        CalendarSyncSetting::updateOrCreate(['role_id' => $role->id], $values);
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

    private static function writeHold(Event $event, Role $role, string $reason, ?string $was, bool $firstPull, ?int $heldBy): void
    {
        $hold = CalendarImportHold::where('event_id', $event->id)->first();

        // A hold still waiting keeps its first reason and what the event was before it.
        if ($hold && $hold->isOpen()) {
            $hold->forceFill([
                'was_visibility' => $hold->was_visibility ?? $was,
                'held_by' => $hold->held_by ?? $heldBy,
            ])->save();

            return;
        }

        CalendarImportHold::updateOrCreate(['event_id' => $event->id], [
            'role_id' => $role->id,
            'reason' => $reason,
            'was_visibility' => $was,
            'first_pull' => $firstPull,
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
        foreach ($event->roles as $role) {
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
