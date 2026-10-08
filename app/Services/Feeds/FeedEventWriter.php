<?php

namespace App\Services\Feeds;

use App\Jobs\NotifyEventChange;
use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\EventInterest;
use App\Models\Role;
use App\Models\User;
use App\Repos\EventRepo;
use App\Services\AuditService;
use App\Services\EventChangeNotifier;
use App\Utils\GeminiUtils;
use App\Utils\RemoteImage;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * What a feed writes: the event it makes from a row, and the changes it makes to that event
 * afterwards.
 *
 * MAKING an event goes through EventRepo::saveEvent(), as every other way of making one does,
 * as the schedule's owner: the slug, the pivots, the usage count, the calendars and the webhook
 * are all the save's.
 *
 * CHANGING one does not. A save reads everything a request does not carry as emptied (ticket
 * types, promo codes, the agenda, sponsors, the other schedules the event is on), and a feed
 * knows eight things about an event, not everything. So an update sets those fields on the
 * model and saves it, and nothing else about the event is touched.
 *
 * YOUR EDITS WIN, field by field. Beside each event the feed keeps what the source said when it
 * last wrote (`src`) and what the event then held (`row`, read back from the saved row, so that
 * anything the model tidies on the way in is compared as stored). A field follows the source
 * while the event still holds what the feed wrote. Once the owner changes it, it is theirs: the
 * source's later value is kept aside (`pending`), shown, and not written.
 *
 * A change that would MOVE an event people have signed up for (its start or its venue) is not
 * the feed's to make: it becomes a decision for the owner.
 */
class FeedEventWriter
{
    /** What a feed knows about an event. The flyer is the eighth, and has a pass of its own. */
    public const FIELDS = ['name', 'description', 'starts_at', 'duration', 'link', 'category_id', 'venue_id'];

    /** A change to one of these moves the event for the people coming to it. */
    public const GUARDED = ['starts_at', 'venue_id'];

    private const FLYER_SECONDS = 8;

    private const FLYER_MAX_BYTES = 8 * 1024 * 1024;

    public function __construct(private EventRepo $events) {}

    /**
     * What the source says about an event, as the values a feed would write.
     *
     * @return array{name: string, description: ?string, starts_at: string, duration: ?float, link: ?string, category_id: ?int, venue_id: ?int, flyer: ?string}
     */
    public function wanted(EventFeed $feed, Role $role, array $row, ?Role $venue): array
    {
        $duration = is_numeric($row['event_duration'] ?? null) && (float) $row['event_duration'] > 0
            ? round((float) $row['event_duration'], 3)
            : null;

        return [
            'name' => mb_substr(trim((string) ($row['event_name'] ?? '')), 0, 255) ?: __('messages.untitled_event'),
            'description' => self::text(mb_substr((string) ($row['event_details'] ?? ''), 0, 10000)),
            'starts_at' => FeedTime::startOf($row, $feed->source_timezone, $role->captureTimezone())->format('Y-m-d H:i:s'),
            'duration' => $duration,
            'link' => UrlUtils::safeHref($row['registration_url'] ?? null),
            'category_id' => $this->categoryFor($feed, $role, (string) ($row['category_name'] ?? '')),
            'venue_id' => $venue?->id,
            'flyer' => ($row['image_url'] ?? null) ?: null,
        ];
    }

    /**
     * Make the event. $batch is the first read's mark, which is what "Undo first read" removes
     * and what the digest to subscribers leaves out.
     */
    public function create(EventFeed $feed, Role $role, User $owner, EventFeedItem $item, array $wanted, ?string $batch): Event
    {
        $roleId = UrlUtils::encodeId($role->id);

        $request = new Request;
        $request->merge(array_filter([
            'name' => $wanted['name'],
            // saveEvent() reads the start as a wall-clock time in the zone it is handed.
            'starts_at' => Carbon::parse($wanted['starts_at'], 'UTC')->setTimezone($feed->source_timezone)->format('Y-m-d H:i:s'),
            'duration' => $wanted['duration'],
            'description' => $wanted['description'],
            'registration_url' => $wanted['link'],
            'category_id' => $wanted['category_id'],
            'venue_id' => $wanted['venue_id'] ? UrlUtils::encodeId($wanted['venue_id']) : null,
            'current_role_group_id' => $feed->group_id ? UrlUtils::encodeId($feed->group_id) : null,
        ], fn ($value) => $value !== null) + [
            'schedule_type' => 'one_time',
            'tickets_enabled' => false,
            'is_draft' => ! $feed->publishes(),
            'is_private' => false,
            'is_internal' => false,
            // The schedule's own place on its own event, as the API and the form give it.
            'members' => $role->isTalent() ? [$roleId => ['name' => $role->name]] : [],
            'curators' => $role->isCurator() ? [$roleId] : [],
        ]);
        $request->setUserResolver(fn () => $owner);

        // saveEvent() asks auth() as well as the request. The read runs with nobody signed in
        // (or, from the web rail, as whoever's request happened to trigger it).
        $before = Auth::user();
        Auth::setUser($owner);

        try {
            $event = $this->events->saveEvent(
                $role, $request, null, true, $feed->source_timezone,
                importSource: Event::IMPORT_FEED,
                importBatch: $batch,
            );
        } finally {
            $before ? Auth::setUser($before) : Auth::forgetUser();
        }

        // The picture is not there yet: what the source says about it is recorded when it is.
        $this->remember($item, $event, ['flyer' => null] + $wanted, null);
        $item->forceFill([
            'event_id' => $event->id,
            'state' => EventFeedItem::STATE_IMPORTED,
            'image_pending' => $wanted['flyer'] !== null,
            'image_source' => $wanted['flyer'],
        ])->save();

        return $event;
    }

    /**
     * Bring an event the feed made up to what the source says now.
     *
     * @return array{written: list<string>, kept: list<string>, held: list<string>} The fields
     *                                                                              written, the ones left because the owner changed them, and the ones held for
     *                                                                              the owner to decide.
     */
    public function update(EventFeed $feed, Role $role, EventFeedItem $item, Event $event, array $wanted): array
    {
        $src = $item->imported['src'] ?? [];
        $row = $item->imported['row'] ?? [];
        $current = $this->held($event);

        $written = $kept = $held = [];
        $pending = [];
        $guarded = null;

        foreach (self::FIELDS as $field) {
            if (self::same($wanted[$field], $src[$field] ?? null)) {
                continue;
            }

            // The owner changed it since the feed last wrote: theirs.
            if (! self::same($current[$field], $row[$field] ?? null)) {
                $kept[] = $field;
                $pending[$field] = $wanted[$field];

                continue;
            }

            if (in_array($field, self::GUARDED, true) && ($guarded ??= $this->hasPeople($event))) {
                $held[] = $field;

                continue;
            }

            $written[] = $field;
        }

        if ($written) {
            $this->apply($feed, $event, array_intersect_key($wanted, array_flip($written)));
            $material = (bool) array_intersect($written, ['starts_at', 'duration', 'venue_id']);

            if ($event->isDirty()) {
                $event->save();
            }
            if ($material) {
                // Subscribed calendars only take a change when the sequence moves.
                $event->forceFill(['ical_sequence' => (int) $event->ical_sequence + 1])->saveQuietly();
            }

            $event->unsetRelation('roles');

            if (! $event->is_draft) {
                $this->events->announceSave($event, $role, false);
            }

            AuditService::log(AuditService::EVENT_UPDATE, null, 'Event', $event->id, null, null, 'feed:'.$feed->id.' '.implode(',', $written));
        }

        // What was written is now what the source said and what the event holds. A field the
        // owner changed, or one held for them, keeps its old pair so that it is still seen as
        // theirs (or still to decide) on the next read.
        $now = $this->held($event);
        $imported = $item->imported ?? [];
        foreach ($written as $field) {
            $imported['src'][$field] = $wanted[$field];
            $imported['row'][$field] = $now[$field];
        }
        $imported['venue_id'] = $imported['row']['venue_id'] ?? null;

        $decide = array_intersect_key($wanted, array_flip($held));
        $decision = $decide ? ['kind' => 'moved'] + $decide : null;

        // The owner already said to leave this exact difference: it is not raised again.
        if ($decision && $item->decided_hash === self::hashOf($decision)) {
            $decision = null;
        }

        // `pending` also carries what the list said about the item (the importer's), which is
        // not this method's to drop.
        $aside = array_diff_key($item->pending ?? [], ['fields' => true, 'decide' => true])
            + array_filter(['fields' => $pending ?: null, 'decide' => $decision]);

        $item->forceFill([
            'imported' => $imported,
            'pending' => $aside ?: null,
            'state' => $decision ? EventFeedItem::STATE_DECIDE : ($item->state === EventFeedItem::STATE_DECIDE ? EventFeedItem::STATE_IMPORTED : $item->state),
        ])->save();

        return ['written' => $written, 'kept' => $kept, 'held' => $held];
    }

    /**
     * The owner said yes to a move the feed was holding (FeedActions::apply()). The held fields
     * are written as any update is, and this time the people who signed up can be told, in the
     * words a save by hand uses for a changed date or place.
     *
     * @return bool Whether there was anything to apply.
     */
    public function applyHeld(EventFeed $feed, Role $role, EventFeedItem $item, Event $event, bool $notify, ?string $note): bool
    {
        $values = array_intersect_key($item->pending['decide'] ?? [], array_flip(self::GUARDED));

        if (! $values) {
            return false;
        }

        $before = $this->forNotice($event);

        $this->apply($feed, $event, $values);
        if ($event->isDirty()) {
            $event->save();
        }
        $event->forceFill(['ical_sequence' => (int) $event->ical_sequence + 1])->saveQuietly();
        $event->unsetRelation('roles');

        if (! $event->is_draft) {
            $this->events->announceSave($event, $role, false);
        }

        $now = $this->held($event);
        $imported = $item->imported ?? [];
        foreach ($values as $field => $value) {
            $imported['src'][$field] = $value;
            $imported['row'][$field] = $now[$field];
        }
        $imported['venue_id'] = $imported['row']['venue_id'] ?? null;

        $item->forceFill([
            'imported' => $imported,
            'pending' => array_diff_key($item->pending ?? [], ['decide' => true]) ?: null,
            'state' => EventFeedItem::STATE_IMPORTED,
            'starts_at' => $event->starts_at,
        ])->save();

        $changes = EventRepo::detectMaterialChanges($before, $this->forNotice($event->fresh()));

        if ($notify && $changes && ! $event->is_draft && EventChangeNotifier::hasAnyoneToTell($event)) {
            NotifyEventChange::dispatch($event->id, $changes, $note ? Str::limit($note, 280, '') : null);
        }

        return true;
    }

    /** What a change notice compares, as a save takes it before and after. */
    private function forNotice(Event $event): array
    {
        return [
            'starts_at' => $event->starts_at,
            'duration' => $event->duration,
            'timezone' => $event->timezone,
            'days_of_week' => $event->days_of_week,
            'event_url' => $event->event_url,
            'venue_id' => $event->venue?->id,
            'venue_name' => $event->venue?->getDisplayName(),
        ];
    }

    /**
     * The flyer: fetched when the source's picture address changes, and only while the event's
     * flyer is still the one the feed put there. A picture that cannot be had leaves things as
     * they are, to be tried again.
     *
     * @return bool Whether there is nothing more to do about it.
     */
    public function flyer(EventFeedItem $item, Event $event, ?string $source): bool
    {
        $imported = $item->imported ?? [];
        $file = $event->getAttributes()['flyer_image_url'] ?? null;

        if (self::same($source, $imported['src']['flyer'] ?? null)) {
            return true;
        }

        // The owner put their own flyer on it, or took the feed's off.
        if (! self::same($file, $imported['row']['flyer'] ?? null)) {
            return true;
        }

        if ($source === null) {
            $this->events->removeFlyer($event);
        } else {
            $image = RemoteImage::read($source, self::FLYER_SECONDS, self::FLYER_MAX_BYTES);

            if (isset($image['reason'])) {
                return false;
            }

            $path = tempnam(sys_get_temp_dir(), 'feed');
            if ($path === false || file_put_contents($path, $image['contents']) === false) {
                return false;
            }

            try {
                $this->events->storeFlyer($event, new UploadedFile($path, 'flyer.'.$image['extension'], null, null, true));
            } finally {
                @unlink($path);
            }
        }

        $imported['src']['flyer'] = $source;
        $imported['row']['flyer'] = $event->getAttributes()['flyer_image_url'] ?? null;
        $item->forceFill(['imported' => $imported])->save();

        return true;
    }

    /**
     * Whether anybody has signed up for the event: bought or reserved, asked to be told when
     * tickets go on sale, or is being shown it by a boost that is running. For such an event
     * the feed neither moves, cancels nor removes anything on its own.
     */
    public function hasPeople(Event $event): bool
    {
        return $event->sales()->exists()
            || EventInterest::where('event_id', $event->id)->exists()
            || $event->boostCampaigns()->unsettled()->exists();
    }

    /**
     * Whether the event carries anything of the owner's that would go with it: what guests
     * and the owner added to it, and any field the owner changed. Such an event is never
     * deleted by the feed.
     */
    public function hasOwnersWork(Event $event, EventFeedItem $item): bool
    {
        $row = $item->imported['row'] ?? [];
        $current = $this->held($event) + ['flyer' => $event->getAttributes()['flyer_image_url'] ?? null];

        foreach ([...self::FIELDS, 'flyer'] as $field) {
            if (! self::same($current[$field], $row[$field] ?? null)) {
                return true;
            }
        }

        foreach (['event_photos', 'event_videos', 'event_comments', 'event_polls', 'gallery_images', 'ticket_waitlists', 'event_feedbacks', 'promo_codes', 'event_parts', 'event_seating_maps', 'tickets'] as $table) {
            if (DB::table($table)->where('event_id', $event->id)->exists()) {
                return true;
            }
        }

        // On another schedule's page as well: somebody chose to list it.
        return DB::table('event_role')->where('event_id', $event->id)
            ->where('role_id', '!=', $event->creator_role_id)
            ->whereNotIn('role_id', array_filter([$row['venue_id'] ?? null]))
            ->exists();
    }

    /** Record what the source said and what the event holds, after the feed wrote. */
    private function remember(EventFeedItem $item, Event $event, array $wanted, ?string $flyerFile): void
    {
        $row = $this->held($event->fresh()) + ['flyer' => $flyerFile];

        $item->imported = ['src' => $wanted, 'row' => $row, 'venue_id' => $row['venue_id']];
    }

    /**
     * What the event holds now, in the terms a feed compares in.
     *
     * @return array{name: ?string, description: ?string, starts_at: ?string, duration: ?float, link: ?string, category_id: ?int, venue_id: ?int}
     */
    public function held(Event $event): array
    {
        return [
            'name' => $event->name,
            'description' => self::text($event->description),
            'starts_at' => $event->starts_at ?: null,
            'duration' => $event->duration ? round((float) $event->duration, 3) : null,
            'link' => $event->registration_url ?: null,
            'category_id' => $event->category_id ? (int) $event->category_id : null,
            'venue_id' => ($id = DB::table('event_role')
                ->join('roles', 'roles.id', '=', 'event_role.role_id')
                ->where('event_role.event_id', $event->id)
                ->where('roles.type', 'venue')
                ->orderBy('event_role.id')
                ->value('roles.id')) ? (int) $id : null,
        ];
    }

    private function apply(EventFeed $feed, Event $event, array $values): void
    {
        foreach ($values as $field => $value) {
            match ($field) {
                'name' => $event->name = $value,
                'description' => $event->description = $value,
                'duration' => $event->duration = $value,
                'link' => $event->registration_url = $value,
                'category_id' => $event->category_id = $value,
                'starts_at' => $event->forceFill(['starts_at' => $value, 'timezone' => $feed->source_timezone]),
                'venue_id' => $this->moveTo($event, $value),
            };
        }
    }

    /** The venue is a pivot row, not a column: the old one comes off and the new one goes on. */
    private function moveTo(Event $event, ?int $venueId): void
    {
        $venues = DB::table('event_role')
            ->join('roles', 'roles.id', '=', 'event_role.role_id')
            ->where('event_role.event_id', $event->id)
            ->where('roles.type', 'venue')
            ->pluck('roles.id');

        if ($venues->isNotEmpty()) {
            $event->roles()->detach($venues->all());
        }

        // Accepted: FeedVenueResolver only ever hands over a venue the schedule's own team runs,
        // an unclaimed one, or one it made.
        if ($venueId) {
            $event->roles()->attach($venueId, ['is_accepted' => true]);
        }
    }

    private function categoryFor(EventFeed $feed, Role $role, string $name): ?int
    {
        $wanted = GeminiUtils::normalizeForMatch($name);

        if ($wanted !== '') {
            foreach ($role->getEventCategories() as $entry) {
                if (GeminiUtils::normalizeForMatch((string) $entry['name']) === $wanted) {
                    return (int) $entry['id'];
                }
            }
        }

        return $feed->category_id ? (int) $feed->category_id : null;
    }

    /** Text as it is compared: one kind of line ending, no space around it, and nothing is null. */
    private static function text(?string $value): ?string
    {
        $value = trim(str_replace(["\r\n", "\r"], "\n", (string) $value));

        return $value === '' ? null : $value;
    }

    /** Two values of a field, as a feed tells them apart: 2 and 2.0 are one length, '' is nothing. */
    public static function same(mixed $a, mixed $b): bool
    {
        $plain = fn ($value) => match (true) {
            $value === null, $value === '' => null,
            is_int($value), is_float($value) => (string) round((float) $value, 3),
            is_string($value) && is_numeric($value) && ! str_contains($value, '-') && ! str_contains($value, ':') => (string) round((float) $value, 3),
            default => $value,
        };

        return $plain($a) === $plain($b);
    }

    public static function hashOf(array $decision): string
    {
        ksort($decision);

        return hash('sha256', json_encode($decision));
    }
}
