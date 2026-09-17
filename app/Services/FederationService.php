<?php

namespace App\Services;

use App\Models\DismissedNextStep;
use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Sender half of federation: pushes this install's public events to the nexus app
 * (eventschedule.com), which lists them with a link back here.
 *
 * Modelled on TranslationOverrideService::shareToNexus() - chunked, capped, stamped
 * with a watermark, and abandoning the rest of a run on the first failure so it
 * retries next time rather than hammering a broken endpoint.
 */
class FederationService
{
    public const SIGNATURE_HEADER = 'X-Federation-Signature';

    /** Events per HTTP request. */
    public const PUSH_CHUNK_SIZE = 100;

    /**
     * Events per run. Bounds the first enable on an established install, where every
     * pre-existing event has a null watermark and would otherwise queue at once;
     * the backlog drains over subsequent hourly runs.
     */
    public const PUSH_MAX_PER_RUN = 500;

    /** Ids per reconcile request. A manifest is a full id list, not a delta. */
    public const MANIFEST_CHUNK_SIZE = 1000;

    /** How many future occurrences of a recurring event to resolve and send. */
    public const OCCURRENCES_AHEAD = 3;

    /** How far ahead to look when resolving occurrences, mirroring the RSS feed's window. */
    public const OCCURRENCE_WINDOW_DAYS = 120;

    /** How long the "is there anything to share yet?" answer is cached. */
    public const ADOPTION_PROMPT_CACHE_MINUTES = 10;

    /** Schedules one "List on the network" prompt offers at once. */
    public const LISTING_PROMPT_LIMIT = 20;

    /**
     * Is this install configured to federate at all? The system-level switch is the
     * operator's, and is deliberately separate from any individual schedule's.
     */
    public function isEnabled(): bool
    {
        return ! config('app.is_nexus') && (bool) Setting::get('federation_enabled');
    }

    /**
     * Should this user be nudged to turn federation on?
     *
     * Lives here rather than in a controller because two unrelated pages ask
     * (the dashboard, and the schedule page you land on after creating an event).
     *
     * Conditions are ordered cheapest-first so the last one - the only query - runs
     * only for an admin who has neither enabled nor dismissed.
     */
    public function shouldPromptAdoption(?object $user): bool
    {
        if (! $user || config('app.is_nexus') || is_demo_mode()) {
            return false;
        }

        // Enabling is admin-only, so anyone else would be shown a suggestion they have
        // no permission to carry out.
        if (! $user->isAdmin() || $user->federation_prompt_dismissed) {
            return false;
        }

        if (Setting::get('federation_enabled')) {
            return false;
        }

        // Never re-prompt an install that already tried federation. This key is only
        // written by instanceId() during register(), which only ever runs with the
        // switch on (turning it on, a contact email change, the hourly reconnect) - so
        // its presence means "connected at least once". Checking the toggle alone would
        // nag someone who deliberately turned it back off.
        if (Setting::get('federation_instance_id')) {
            return false;
        }

        // The "first event" trigger. Reuses the real selection query rather than
        // counting rows, so the prompt only appears when enabling would actually
        // publish something - and stays away on an install holding only demo data.
        //
        // Undecided schedules count here and nowhere else. Nobody can answer the
        // per-schedule question before the operator has joined a network - the toggle
        // is not even rendered until then - so the strict query is empty on exactly
        // the installs this prompt exists to reach, and the feature would be
        // undiscoverable.
        //
        // Cached because the schedule page is one of the most-loaded in the AP and this
        // query is not cheap; the answer flips once and then stays true.
        return Cache::remember(
            'federation:has_shareable_events',
            now()->addMinutes(self::ADOPTION_PROMPT_CACHE_MINUTES),
            fn () => $this->federatableQuery(true)->exists()
        );
    }

    /**
     * Self-issued instance identity, created on first use. This is an identifier,
     * not a credential - the secret below is what authenticates a request.
     */
    public function instanceId(): string
    {
        $id = Setting::get('federation_instance_id');

        if (! $id) {
            $id = (string) Str::uuid();
            Setting::set('federation_instance_id', $id);
        }

        return $id;
    }

    public function secret(): string
    {
        $secret = Setting::get('federation_secret');

        if (! $secret) {
            $secret = bin2hex(random_bytes(32));
            Setting::set('federation_secret', $secret);
        }

        return $secret;
    }

    public function status(): string
    {
        return (string) (Setting::get('federation_status') ?: 'not_connected');
    }

    /**
     * Introduce this install to the nexus. Safe to call repeatedly: a first call
     * creates a pending registration, later calls rotate the secret and are signed
     * with the current one.
     */
    public function register(): array
    {
        // Ahead of the payload, not left to send(): instanceId() and secret() below
        // both mint and persist on first read, so building the payload for an install
        // that never opted in would give it a federation identity it never asked for.
        if (! $this->isEnabled()) {
            return ['ok' => false, 'body' => []];
        }

        $payload = [
            'instance_id' => $this->instanceId(),
            'site_url' => rtrim((string) config('app.url'), '/'),
            'name' => config('app.name'),
            'contact_email' => Setting::get('federation_contact_email'),
            'secret' => $this->secret(),
            'app_version' => config('self-update.version_installed'),
        ];

        // The language of the admin switching sharing on, so the nexus writes to them in it.
        // Only from a signed-in request: the hourly reconnect runs from the scheduler or the
        // /translate_data cron request, where the locale is just the app default and would
        // overwrite the admin's. The nexus keeps what it has when the field is absent.
        if (auth()->check()) {
            $payload['locale'] = app()->getLocale();
        }

        $result = $this->send('/api/federation/register', $payload);

        // What the network now holds, so a later change - or one that failed to arrive - can be
        // told apart from the value this install merely has saved. See registrationIsStale().
        if ($result['ok']) {
            Setting::set('federation_registered_email', (string) ($payload['contact_email'] ?? ''));
        }

        return $result;
    }

    /**
     * Does the network hold an older contact address than this install has?
     *
     * The address only travels in register(). When that call fails - the network was down the
     * moment the operator saved a new address - nothing else would send it, and the network
     * could never tell them they were approved. FederateEvents re-registers while this holds.
     * An install that registered before this was recorded has no stored value, and re-registers
     * once; the network only mails a welcome when the address actually changed.
     */
    public function registrationIsStale(): bool
    {
        $current = strtolower(trim((string) Setting::get('federation_contact_email')));

        return $current !== ''
            && $current !== strtolower(trim((string) Setting::get('federation_registered_email')));
    }

    /**
     * Push everything not yet synced. Returns a summary; failures are recorded for
     * the operator rather than thrown, because this runs from a scheduled command.
     *
     * @param  int|null  $maxPerRun  Overrides PUSH_MAX_PER_RUN. Exists so the starvation
     *                               guarantee below can be tested without creating
     *                               hundreds of rows.
     */
    public function push(?int $maxPerRun = null): array
    {
        $maxPerRun ??= self::PUSH_MAX_PER_RUN;
        $pushed = 0;
        $failed = false;

        // Two budgets, not one. Recurring events are re-checked every run because their
        // dates move, so a single shared limit would let a few hundred of them fill the
        // window forever and starve newly created one-off events, which would then
        // never appear at all. Unsynced rows are the priority: a stale recurring date
        // is a smaller problem than an event that has never been published.
        $unsynced = $this->federatableQuery()
            ->with(['roles', 'creatorRole'])
            ->whereNull('federated_at')
            // Already refused once and unchanged since. Retrying it would burn the
            // per-run budget on something guaranteed to be refused again, starving the
            // events that would actually publish.
            ->whereNull('federated_skipped_at')
            ->orderBy('id')
            ->limit($maxPerRun)
            ->get();

        $recurring = $this->federatableQuery()
            ->with(['roles', 'creatorRole'])
            ->whereNotNull('federated_at')
            ->whereNotNull('days_of_week')
            ->orderBy('id')
            ->limit($maxPerRun)
            ->get();

        $events = $unsynced->concat($recurring);

        foreach ($events->chunk(self::PUSH_CHUNK_SIZE) as $chunk) {
            $items = [];
            $ids = [];
            $hashes = [];
            $skippedIds = [];
            $byExternalId = [];

            foreach ($chunk as $event) {
                $payload = $this->buildPayload($event);

                if ($payload === null) {
                    // Nothing worth listing (no image, no resolvable date). Record it as
                    // SKIPPED, not delivered: marking it delivered would make reconcile
                    // report it as missing upstream and requeue it every hour forever.
                    $skippedIds[] = $event->id;

                    continue;
                }

                // Unchanged recurring event: dates have not moved, so there is nothing
                // to say. This is what keeps steady-state traffic near zero.
                if ($event->federated_at && $event->days_of_week
                    && $payload['occurrences_hash'] === $event->federated_hash) {
                    continue;
                }

                $items[] = $payload;
                $ids[] = $event->id;
                $byExternalId[$payload['external_id']] = $event->id;

                // Only recurring events need their dates remembered; see stampSynced().
                if ($event->days_of_week) {
                    $hashes[$event->id] = $payload['occurrences_hash'];
                }
            }

            if (! $items) {
                $this->stampSkipped($skippedIds);

                continue;
            }

            $result = $this->send('/api/federation/events', ['items' => $items]);

            if (! $result['ok']) {
                $failed = true;
                break;
            }

            // The nexus names what it refused - an event whose backlink is off-host, or
            // whose title trips the junk filter, will never be accepted as-is. Those get
            // the skipped marker rather than the delivered one, so reconcile does not
            // treat their absence upstream as a gap to re-send.
            foreach (($result['body']['skipped_ids'] ?? []) as $externalId) {
                if (isset($byExternalId[$externalId])) {
                    $skippedIds[] = $byExternalId[$externalId];
                    $ids = array_values(array_diff($ids, [$byExternalId[$externalId]]));
                    unset($hashes[$byExternalId[$externalId]]);
                }
            }

            $this->stampSynced($ids, $hashes);
            $this->stampSkipped($skippedIds);
            $pushed += count($ids);
        }

        return ['pushed' => $pushed, 'failed' => $failed];
    }

    /**
     * Tell the nexus the complete set of events this install currently considers
     * federatable, so it can drop anything else.
     *
     * This exists because events are hard-deleted here (Event has no SoftDeletes),
     * which makes a tombstone list impossible to construct after the fact. It also
     * covers every other way an event can stop qualifying - unpublished, cancelled,
     * made private, schedule opted out - with one mechanism.
     *
     * The response reports ids the nexus does not hold; those get their watermark
     * cleared so the next run re-sends them. Without that the two sides can deadlock:
     * this side believes it is synced and the listing never appears.
     */
    public function reconcile(): array
    {
        $ids = $this->federatableQuery()->orderBy('id')->pluck('federated_at', 'id');

        $externalIds = [];
        $syncedExternalIds = [];
        foreach ($ids as $id => $federatedAt) {
            $external = UrlUtils::encodeId($id);
            $externalIds[$external] = $id;

            if ($federatedAt) {
                $syncedExternalIds[] = $external;
            }
        }

        $chunks = array_chunk(array_keys($externalIds), self::MANIFEST_CHUNK_SIZE);
        $chunks = $chunks ?: [[]];
        $missing = [];
        $removed = 0;

        // One token for the whole pass. The nexus stamps every id it is told about with
        // it and sweeps whatever still carries an older one, which is what makes a
        // manifest larger than a single request safe to act on.
        $runToken = (string) Str::uuid();

        foreach ($chunks as $index => $chunk) {
            $isFinal = $index === count($chunks) - 1;

            $result = $this->send('/api/federation/reconcile', [
                'external_ids' => $chunk,
                'run_token' => $runToken,
                'is_final' => $isFinal,
                // Only ask about events we believe are already delivered; the rest are
                // queued anyway.
                'known_ids' => array_values(array_intersect($syncedExternalIds, $chunk)),
            ]);

            if (! $result['ok']) {
                return ['ok' => false, 'removed' => 0, 'requeued' => 0];
            }

            $missing = array_merge($missing, $result['body']['missing'] ?? []);
            $removed += (int) ($result['body']['removed'] ?? 0);
        }

        $requeue = [];
        foreach ($missing as $external) {
            if (isset($externalIds[$external])) {
                $requeue[] = $externalIds[$external];
            }
        }

        if ($requeue) {
            Event::whereIn('id', $requeue)->update(['federated_at' => null]);
        }

        return ['ok' => true, 'removed' => $removed, 'requeued' => count($requeue)];
    }

    /**
     * Take every listing down, for when the operator switches the network off.
     *
     * Switching off stops push() and reconcile() from running at all, so without an
     * explicit goodbye the nexus is never told anything changed: it keeps publishing
     * what it already holds until each event's own date passes, which for a recurring
     * event carrying resolved occurrences is months. "Off" has to mean off on both
     * sides, and the docs promise exactly that.
     *
     * An empty final manifest is the whole message. The nexus stamps nothing with the
     * run token, so its is_final sweep deletes every row this instance owns. Blocked
     * rows are tombstones and survive by design - resurrecting one by re-publishing is
     * the thing the block exists to prevent.
     */
    public function withdraw(): array
    {
        // Never registered, so there is nothing on the other side to take down - and
        // nothing worth minting an identity for on the way out.
        if (! Setting::get('federation_instance_id')) {
            Setting::set('federation_withdraw_pending', null);

            return ['ok' => true, 'removed' => 0];
        }

        $result = $this->send('/api/federation/reconcile', [
            'external_ids' => [],
            'run_token' => (string) Str::uuid(),
            'is_final' => true,
            'known_ids' => [],
        ], force: true);

        // Nothing else will retry this: the hourly push returns early while the switch
        // is off, so a nexus that happened to be unreachable at the moment the operator
        // opted out would keep their events published indefinitely. Record the intent
        // instead, and let FederateEvents carry it out on a later run.
        Setting::set('federation_withdraw_pending', $result['ok'] ? null : '1');

        // The network no longer holds anything from this install, so nothing here is "sent" any
        // more. Left set, switching back on would show every withdrawn event as Sent, skip it on
        // the first push and only resend it a run later, via reconcile. toBase(): no
        // updated_at churn on every event of the install.
        if ($result['ok']) {
            Event::whereNotNull('federated_at')
                ->toBase()
                ->update(['federated_at' => null, 'federated_hash' => null]);
        }

        return ['ok' => $result['ok'], 'removed' => (int) ($result['body']['removed'] ?? 0)];
    }

    /**
     * Events this install is willing to share.
     *
     * Mirrors the nexus's own discovery query rather than inventing a second
     * definition of "public", so a federated listing is held to the same bar as a
     * local one: verified and listed schedule, accepted association, no demo or
     * throwaway-test content.
     *
     * @param  bool  $includeUndecided  Count schedules that have not answered the
     *                                  question yet as willing. Strictly for the
     *                                  adoption prompt, which asks "would enabling
     *                                  this publish anything?" - never for the
     *                                  preview or a push, which must show and send
     *                                  only what was actually opted in.
     */
    public function federatableQuery(bool $includeUndecided = false)
    {
        return Event::query()
            ->where(function ($q) {
                $q->where('starts_at', '>=', Carbon::today())
                    ->orWhereNotNull('days_of_week')
                    ->orWhere(function ($q2) {
                        $q2->where('duration', '>=', 24)
                            ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [Carbon::today()]);
                    });
            })
            ->where('is_private', false)
            ->where('is_draft', false)
            ->where('is_cancelled', false)
            ->whereNull('event_password')
            // Three states, and the difference matters twice: see the veto below. Only
            // an explicit yes qualifies a schedule to publish. The role-column half
            // lives on the Role model so previewSchedules() can apply the identical
            // rule to a standalone query instead of a second copy of it.
            ->whereHas('roles', function ($q) use ($includeUndecided) {
                $q->where('event_role.is_accepted', true)
                    ->federationEligible($includeUndecided);
            })
            // The whereHas above is an ANY-match, so on a co-listed event one willing
            // schedule would drag every other participant onto the network with it -
            // including their name, and the venue's full address. An opt-out is an
            // explicit act, so any participant that made it vetoes the whole listing.
            //
            // Matching `false` and not "anything other than true" is the whole reason
            // the column is nullable: schedules created after the operator joined
            // start as null, and there are a lot of them (every unclaimed placeholder
            // a curator creates, every venue calendar sync invents). Treating those as
            // opt-outs would let one of them veto an event its real, opted-in schedule
            // published perfectly well.
            ->whereDoesntHave('roles', function ($r) {
                $r->where('roles.federation_enabled', false);
            })
            ->whereDoesntHave('roles', function ($r) {
                $r->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                    ->orWhere('subdomain', 'like', 'demo-%');
            })
            ->excludeLikelyTest();
    }

    /**
     * The image as a URL the nexus can actually fetch, or null.
     *
     * getImageUrl() resolves through accessors whose final branch returns the raw
     * stored value - a bare filename - on any disk that is not local/public, or
     * do_spaces while hosted. The nexus validates image_url as a URL, and a batch is
     * only as good as its worst item, so sending a filename from (say) an S3-backed
     * selfhost would stall that install's sync permanently. Resolve it against the
     * configured disk instead, and skip the event rather than send something
     * unfetchable.
     */
    protected function absoluteImageUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        try {
            $url = (string) Storage::url($value);
        } catch (\Throwable $e) {
            // A disk with no public URL concept. Nothing sensible to advertise.
            report($e);

            return null;
        }

        // A disk that answers with a path rather than a URL: make it absolute against
        // this install, which is the host the nexus checks the backlink against anyway.
        if (! Str::startsWith($url, ['http://', 'https://'])) {
            $url = $url === '' ? '' : url($url);
        }

        return Str::startsWith($url, ['http://', 'https://']) ? $url : null;
    }

    /**
     * The event's schedules that individually satisfy the federation bar.
     *
     * In-memory mirror of federatableQuery()'s whereHas(). That query only asks whether
     * *some* role qualifies; this says *which*, which is what attribution needs. Sorted
     * by id so a co-listed event credits the same schedule on every run rather than
     * whatever order the pivot happened to return.
     *
     * Keep in sync with federatableQuery().
     */
    public function federatingRoles(Event $event)
    {
        return $event->roles
            ->filter(fn ($role) => $role->pivot->is_accepted
                && $role->federation_enabled
                && ! $role->is_deleted
                && ! $role->is_unlisted
                && $role->isClaimed())
            ->sortBy('id')
            ->values();
    }

    /**
     * Flatten an event into the wire format. Returns null when there is nothing
     * worth listing.
     */
    public function buildPayload(Event $event): ?array
    {
        $event->loadMissing('roles');

        $image = $this->absoluteImageUrl($event->getImageUrl());
        if (! $image) {
            // The nexus only lists cards that show a real picture, matching what it
            // does with its own events, so sending this would be wasted.
            return null;
        }

        $occurrences = $this->resolveOccurrences($event);
        if (! $occurrences && ! $event->starts_at) {
            return null;
        }

        // Deliberately NOT getCanonicalUrl(): for a schedule on a direct custom domain
        // that returns the customer's own domain, which the nexus refuses because it
        // only accepts backlinks on the host the instance registered - so those
        // schedules would silently never federate. Force this install's own host, which
        // always passes; the origin page's own canonical tag still points search
        // engines at the custom domain.
        $url = $event->getGuestUrl(false, null, false);
        if (! $url) {
            return null;
        }

        // Attribute to a schedule that actually qualifies to federate, not to
        // getViewableRole() - that returns the first *claimed* role and knows nothing
        // about federation, so on a multi-role event it could credit (and link to) an
        // unlisted or unverified schedule that the eligibility query never approved.
        // $event->roles has no ORDER BY either, so which one it picked was not even
        // stable between runs.
        $federating = $this->federatingRoles($event);

        // Still not role(), which only returns a talent schedule and would leave a
        // venue-only event with no attribution at all.
        $schedule = $federating->first() ?: $event->getViewableRole();

        // The venue is NOT taken from that filtered set. Its consent is already settled -
        // federatableQuery() drops the whole event if any participant opted out - and
        // requiring it to qualify in full would demand isClaimed(), which most venues are
        // not, silently stripping the location off the majority of listings.
        $venue = $event->venue;

        return [
            'external_id' => UrlUtils::encodeId($event->id),
            'url' => $url,
            'name' => (string) $event->name,
            'short_description' => $event->short_description,
            'language' => $event->getLanguageCode(),
            'starts_at' => $occurrences[0] ?? null,
            'ends_at' => $this->endsAt($event, $occurrences[0] ?? null),
            'timezone' => $event->scheduleTimezone(),
            'occurrences' => $occurrences,
            'occurrences_hash' => $this->hashOccurrences($occurrences),
            'schedule_name' => optional($schedule)->name,
            'schedule_url' => optional($schedule)->getGuestUrl(),
            'image_url' => $image,
            // Whether there is an online component, never the joining link itself.
            // event_url routinely holds a tokenised Zoom or Teams URL (see the
            // migration that widened events.event_url to 500), and the network has no
            // use for it: it decides a label and nothing more.
            'is_online' => (bool) $event->event_url,
            'venue_name' => $venue ? $venue->name : null,
            'address' => $venue ? $venue->bestAddress() : null,
            'city' => $venue ? $venue->city : null,
            'state' => $venue ? $venue->state : null,
            'postal_code' => $venue ? $venue->postal_code : null,
            'country_code' => $venue ? $venue->country_code : null,
            'geo_lat' => $venue ? $venue->geo_lat : null,
            'geo_lon' => $venue ? $venue->geo_lon : null,
        ];
    }

    /**
     * End time, derived rather than stored. `duration` is a float in hours, so
     * addHours() would truncate and a 90-minute event would end after 60.
     */
    protected function endsAt(Event $event, ?string $startsAt): ?string
    {
        if (! $startsAt || ! $event->duration) {
            return null;
        }

        return Carbon::parse($startsAt)
            ->addMinutes($event->durationInMinutes())
            ->toIso8601String();
    }

    /**
     * Concrete future dates for a recurring event, resolved HERE with the existing
     * Event::matchesDate().
     *
     * The recurrence rules are deliberately not sent to the nexus: matchesDate()
     * spans six frequencies, an interval, an end condition, a day-of-week bitmask
     * and include/exclude overrides, and carries a hard-won timezone fix. Re-deriving
     * that against a second table would reintroduce exactly those bugs on the
     * public marketing site.
     */
    public function resolveOccurrences(Event $event): array
    {
        if (! $event->days_of_week) {
            return $event->starts_at
                ? [Carbon::parse($event->starts_at)->toIso8601String()]
                : [];
        }

        $timezone = $event->scheduleTimezone();
        $cursor = Carbon::now($timezone)->startOfDay();
        $end = $cursor->copy()->addDays(self::OCCURRENCE_WINDOW_DAYS);
        $found = [];

        while ($cursor->lte($end) && count($found) < self::OCCURRENCES_AHEAD) {
            if ($event->matchesDate($cursor, $timezone)) {
                // occurrenceStartUtc() takes a schedule-LOCAL calendar date and returns
                // the UTC instant. Deliberately not getStartDateTime(), which would stamp
                // the anchor's time-of-day onto the occurrence date.
                $found[] = $event->occurrenceStartUtc($cursor->format('Y-m-d'), $timezone)->toIso8601String();
            }
            $cursor->addDay();
        }

        return $found;
    }

    protected function hashOccurrences(array $occurrences): string
    {
        return hash('sha256', implode('|', $occurrences));
    }

    /**
     * Mark events the network will not take.
     *
     * Deliberately separate from federated_at. Both keep an event out of the next push,
     * but only federated_at means "the nexus has this" - and reconcile asks the nexus
     * about everything it believes was delivered. Marking a refused event as delivered
     * makes the nexus report it missing, which clears the watermark, which re-sends it
     * next hour, forever. Clearing federated_at here keeps that invariant true.
     */
    protected function stampSkipped(array $ids): void
    {
        if (! $ids) {
            return;
        }

        Event::whereIn('id', $ids)->update([
            'federated_skipped_at' => now(),
            'federated_at' => null,
        ]);
    }

    /**
     * Mark a delivered batch as synced.
     *
     * The occurrence hash lives on the event row rather than in a shared settings blob:
     * reading it is free, it cannot accumulate entries for deleted events, and it does
     * not invalidate the whole settings cache on every chunk.
     *
     * Only RECURRING events get a hash. It is read in exactly one place - the
     * "have this event's dates moved?" check in push() - which is guarded on
     * days_of_week, so storing one for a one-off event is work nothing ever reads.
     * That matters at the size it matters most: every event has a distinct occurrence
     * set and therefore a distinct hash, so hashing everything would turn a
     * 500-event first-enable into 500 UPDATE statements instead of one.
     */
    protected function stampSynced(array $ids, array $hashes): void
    {
        $now = now();

        // Everything in the batch gets its watermark in a single statement.
        $withoutHash = array_values(array_diff($ids, array_keys($hashes)));
        if ($withoutHash) {
            Event::whereIn('id', $withoutHash)->update(['federated_at' => $now]);
        }

        // Recurring events additionally record the dates they were sent with. These are
        // a minority of rows and their hashes are genuinely distinct, so one statement
        // each is the honest cost.
        foreach ($hashes as $id => $hash) {
            Event::whereKey($id)->update([
                'federated_at' => $now,
                'federated_hash' => $hash,
            ]);
        }
    }

    /**
     * Signed POST to the nexus.
     *
     * Deliberately NOT routed through the SSRF guard: nexus_url is operator-controlled
     * .env configuration in the same trust class as DB_HOST, and guarding it would
     * break local and staging nexus targets, which resolve to private addresses.
     * Inbound, attacker-supplied URLs are a different matter and are guarded.
     *
     * @param  bool  $force  Send even though the operator's switch is off. Only
     *                       withdraw() does this, and only to take listings down.
     */
    protected function send(string $path, array $payload, bool $force = false): array
    {
        // The opt-in belongs here, not only in the callers. Every caller today checks
        // first, but this is the single point where install data leaves the building,
        // so the guarantee should not depend on the next one remembering to.
        // Deliberately before instanceId(), which would otherwise mint and persist an
        // identity for an install that never opted in.
        if (! $force && ! $this->isEnabled()) {
            return ['ok' => false, 'body' => []];
        }

        $payload['instance_id'] ??= $this->instanceId();
        $payload['site_url'] ??= rtrim((string) config('app.url'), '/');
        // On every call, not only at registration: the network picks the instructions in its
        // welcome email by version, and an install updates long after it registered.
        $payload['app_version'] ??= config('self-update.version_installed');

        $url = rtrim((string) config('app.nexus_url'), '/').$path;
        $body = json_encode($payload);
        $signature = 'sha256='.hash_hmac('sha256', $body, $this->secret());

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    self::SIGNATURE_HEADER => $signature,
                    'Content-Type' => 'application/json',
                ])
                ->withBody($body, 'application/json')
                ->post($url);
        } catch (\Throwable $e) {
            report($e);
            $this->recordFailure('unreachable');

            return ['ok' => false, 'body' => []];
        }

        $json = $response->json() ?: [];

        if (! $response->successful()) {
            $this->recordFailure($this->classifyFailure($response->status(), $json));

            return ['ok' => false, 'body' => $json, 'status' => $response->status()];
        }

        $this->recordSuccess($json['status'] ?? null, $json['listings_url'] ?? null);

        return ['ok' => true, 'body' => $json];
    }

    /**
     * Map a failure onto a small set of states the operator can act on. The raw
     * response is never surfaced: it would leak whatever the remote end or the HTTP
     * client produced into the admin UI.
     */
    protected function classifyFailure(int $status, array $body): string
    {
        if ($status === 403) {
            return 'rejected';
        }

        if ($status === 422 && ($body['error'] ?? null) === 'Event limit reached') {
            return 'over_limit';
        }

        // Individual listings are refused via skipped_ids, so a 422 now means the
        // request envelope itself was rejected - worth saying so rather than folding it
        // into the generic error, because the operator's next step is different.
        if ($status === 422) {
            return 'validation';
        }

        if ($status === 404) {
            return 'not_available';
        }

        return 'error';
    }

    protected function recordFailure(string $reason): void
    {
        Setting::set('federation_last_error', $reason);
        Setting::set('federation_last_error_at', (string) now());
    }

    protected function recordSuccess(?string $status, $listingsUrl = null): void
    {
        Setting::set('federation_last_error', null);
        Setting::set('federation_last_error_at', null);
        Setting::set('federation_last_synced_at', (string) now());

        if ($status) {
            Setting::set('federation_status', $status);
        }

        // Where this install's listings can be seen. Only the nexus can build it (the id in it is
        // encoded with the nexus's key), and it is rendered as a link on the settings page, so
        // it is only kept when it really points at the network this install is configured for.
        // Every response carries it, so only a change is written - each write clears the whole
        // settings cache.
        if (is_string($listingsUrl) && $listingsUrl !== Setting::get('federation_listings_url')
            && $this->isNexusUrl($listingsUrl)) {
            Setting::set('federation_listings_url', $listingsUrl);
        }
    }

    protected function isNexusUrl(string $url): bool
    {
        $nexus = parse_url((string) config('app.nexus_url')) ?: [];
        $parts = parse_url($url) ?: [];

        return in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && ! empty($parts['host'])
            && strtolower($parts['host']) === strtolower($nexus['host'] ?? '');
    }

    /**
     * Events that would be shared on the next run, for the "here is exactly what
     * will be published" preview beside the operator's toggle.
     */
    public function previewEvents(int $limit = 25)
    {
        // roles for previewState(): an event's image can come from its talent or venue.
        return $this->federatableQuery()->with('roles')->orderBy('starts_at')->limit($limit)->get();
    }

    /**
     * Where one previewed event stands, so the preview says why a listed event is not on the
     * network yet instead of implying everything on it will be.
     *
     *   sent        - delivered to the network
     *   needs_image - buildPayload() will refuse it: nothing to show on a listing card
     *   skipped     - sent before and refused, and not changed since
     *   next_sync   - goes out on the next hourly run
     */
    public function previewState(Event $event): string
    {
        if ($event->federated_at) {
            return 'sent';
        }

        if (! $this->absoluteImageUrl($event->getImageUrl())) {
            return 'needs_image';
        }

        return $event->federated_skipped_at ? 'skipped' : 'next_sync';
    }

    /**
     * How much of what the preview lists has been delivered, in one pass over the query the
     * page already runs: ['total' => ..., 'sent' => ...].
     */
    public function previewTotals(): array
    {
        $row = $this->federatableQuery()
            ->toBase()
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(events.federated_at IS NOT NULL), 0) as sent')
            ->first();

        return ['total' => (int) ($row->total ?? 0), 'sent' => (int) ($row->sent ?? 0)];
    }

    /**
     * The schedules that would appear on at least one shared listing, for the "what
     * will be shared" preview beside the operator's toggle.
     *
     * A listing carries the schedule's name AND the address of its public page, and
     * both are shown to whoever reviews the install, so an operator deciding whether
     * to join should see the schedules and not only the event titles.
     *
     * Derived from federatableQuery(), NOT from roles.federation_enabled. A standalone
     * Role query cannot reproduce the veto: a schedule can be opted in and still
     * publish nothing, because any co-listed participant opting out drops the whole
     * event. Listing it as "will be shared" would be a straight lie in exactly the
     * place an operator is trusting this screen.
     *
     * The role-side federationEligible() is load-bearing, not belt-and-braces. The
     * whereHas only proves this role is attached to a federatable event, and
     * federatableQuery()'s own role filter is an ANY-match, so without it an
     * unverified or unlisted co-participant on that same event would be listed as
     * being published when it is not.
     */
    public function previewSchedules(int $limit = 25)
    {
        return $this->federatableSchedulesQuery()->orderBy('roles.name')->limit($limit)->get();
    }

    /**
     * How many there are in total, for the "and N more" line.
     *
     * Separate from previewSchedules() rather than folded into it because it repeats
     * federatableQuery() - two EXISTS subqueries plus three REGEXP predicates over the
     * whole events table - and the settings page already runs that shape twice. Call
     * it only when the capped list came back full.
     */
    public function previewScheduleCount(): int
    {
        return $this->federatableSchedulesQuery()->count();
    }

    protected function federatableSchedulesQuery()
    {
        return Role::federationEligible()
            ->whereHas('events', function ($q) {
                $q->whereIn('events.id', $this->federatableQuery()->select('events.id'))
                    ->where('event_role.is_accepted', true);
            });
    }

    /**
     * Schedules held back because they are not verified. On a self-hosted SaaS this
     * is the usual reason a customer's events never appear, and it looks like a bug
     * unless it is stated.
     */
    public function unverifiedScheduleCount(): int
    {
        return Role::where('is_deleted', false)
            ->where('federation_enabled', true)
            ->whereNotNull('user_id')
            ->whereNull('email_verified_at')
            ->whereNull('phone_verified_at')
            ->count();
    }

    /**
     * Schedules that have not answered the per-schedule question yet.
     *
     * The other reason a preview looks emptier than the operator expects, and unlike
     * the unverified count it is not a problem to fix - it is just how a schedule
     * created after the install joined the network starts out. Stated because an
     * empty preview with no explanation reads as a broken feature.
     *
     * $exceptUserId leaves out one owner's schedules: the settings card lists the
     * operator's own with a checkbox each, so the footnote counts everyone else's.
     * Unlisted and demo schedules are not counted - listing them would publish nothing.
     */
    public function undecidedScheduleCount(?int $exceptUserId = null): int
    {
        return $this->undecidedFilters(Role::query())
            ->when($exceptUserId, fn ($q) => $q->where('roles.user_id', '!=', $exceptUserId))
            ->count();
    }

    /**
     * Schedules this user OWNS that nobody has answered the network question for.
     *
     * The set every multi-schedule surface offers (the settings checklist, the dashboard
     * prompt). Owned rather than editable: those surfaces list several schedules at once, and a
     * team admin should not be carrying a customer's schedule onto the network in a batch. One
     * schedule at a time, from its own page, anyone who can edit it may - see
     * editableUndecidedSchedules().
     */
    public function ownedUndecidedSchedules(User $user): Collection
    {
        return $this->undecidedFilters(Role::query()->where('roles.user_id', $user->id))
            ->orderBy('roles.name')
            ->get();
    }

    /**
     * Undecided schedules this user may list: the ones they can edit, which is who can change
     * the select on the schedule's settings page. roles() already drops deleted schedules.
     */
    public function editableUndecidedSchedules(User $user): Collection
    {
        return $this->undecidedFilters($user->editor())
            ->orderBy('roles.name')
            ->get();
    }

    /**
     * Undecided, and something a listing could actually go out for: an owner (placeholders
     * never list anything), not unlisted (listing it would publish nothing), not a demo.
     */
    protected function undecidedFilters($query)
    {
        return $query
            ->where('roles.is_deleted', false)
            ->whereNotNull('roles.user_id')
            ->whereNull('roles.federation_enabled')
            ->where('roles.is_unlisted', false)
            ->where('roles.subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('roles.subdomain', 'not like', 'demo-%');
    }

    /**
     * For each schedule, how many of its events would be shared once it is listed.
     *
     * Undecided co-participants count as willing, like the adoption prompt: that is the
     * question being asked. Not cached, so a prompt can appear the moment the first qualifying
     * event is saved - which is when the schedule page it shows on is opened.
     *
     * @return array<int, int> role id => event count, only for schedules with at least one
     */
    public function shareableCounts(array $roleIds): array
    {
        $roleIds = array_values(array_unique(array_map('intval', $roleIds)));

        if ($roleIds === []) {
            return [];
        }

        // Only schedules that would publish on their own once listed. federatableQuery()'s role
        // clause is an ANY-match, so without this an ineligible schedule - an unverified one -
        // would be credited with events that qualify through a co-participant, and offered a
        // listing that publishes nothing. The same trap federatableSchedulesQuery() guards.
        $eligible = Role::whereIn('roles.id', $roleIds)
            ->federationEligible(true)
            ->pluck('roles.id')
            ->all();

        if ($eligible === []) {
            return [];
        }

        $pivots = fn () => DB::table('event_role')
            ->whereIn('event_role.role_id', $eligible)
            ->where('event_role.is_accepted', true);

        // Narrowed to the candidates' own events first (event_role is indexed role first), so
        // the expensive federation predicates only ever run on those rows.
        $qualifying = $this->federatableQuery(true)
            ->whereIn('events.id', $pivots()->select('event_role.event_id'))
            ->where(fn ($q) => $this->whereHasImage($q))
            ->select('events.id');

        return $pivots()
            ->whereIn('event_role.event_id', $qualifying)
            ->groupBy('event_role.role_id')
            ->selectRaw('event_role.role_id as role_id, COUNT(DISTINCT event_role.event_id) as shareable')
            ->pluck('shareable', 'role_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    /**
     * An SQL stand-in for the image rule buildPayload() enforces through Event::getImageUrl():
     * a flyer, or a talent or venue schedule on the event with a profile image. Close rather than
     * exact - getImageUrl() only looks at the first talent, and absoluteImageUrl() can still
     * refuse a stored value - which is fine for deciding whether to ask.
     */
    protected function whereHasImage($query)
    {
        return $query
            ->where(fn ($flyer) => $flyer->whereNotNull('events.flyer_image_url')
                ->where('events.flyer_image_url', '!=', ''))
            ->orWhereHas('roles', fn ($role) => $role->whereIn('roles.type', ['talent', 'venue'])
                ->whereNotNull('roles.profile_image_url')
                ->where('roles.profile_image_url', '!=', ''));
    }

    /**
     * Can anyone list a schedule right now? Not before the operator has joined a network, not
     * on a demo, and not while the network has this install suspended - asking owners to list
     * onto a network that is hiding everything would be a promise nobody can keep.
     */
    public function listingAvailable(): bool
    {
        return $this->isEnabled() && ! is_demo_mode() && $this->status() !== 'suspended';
    }

    /**
     * The schedules a "List on the network" prompt should offer this user.
     *
     * Without $only: their own undecided schedules (the dashboard). With it: that one schedule,
     * if they can edit it (its own page). Either way minus the ones this user turned down, and
     * only those with at least one event that would be shared - a prompt on an empty schedule
     * gets dismissed before it could matter, and a dismissal is permanent.
     *
     * @return Collection<int, Role> each with a `shareable_count` attribute set
     */
    public function listingPromptSchedules(?User $user, ?Role $only = null): Collection
    {
        if (! $user || ! $this->listingAvailable()) {
            return collect();
        }

        // The schedule page asks about one schedule, which is usually already answered - settle
        // that from the loaded row before any query.
        if ($only && ($only->federation_enabled !== null || $only->is_unlisted || $only->is_deleted)) {
            return collect();
        }

        $candidates = $only
            ? $this->undecidedFilters($user->editor()->where('roles.id', $only->id))->get()
            : $this->ownedUndecidedSchedules($user);

        if ($candidates->isEmpty()) {
            return $candidates;
        }

        $dismissed = DismissedNextStep::where('user_id', $user->id)
            ->where('step_type', DismissedNextStep::FEDERATION_LISTING)
            ->whereIn('role_id', $candidates->pluck('id'))
            ->pluck('role_id')
            ->flip();

        $candidates = $candidates->reject(fn ($role) => isset($dismissed[$role->id]))->values();

        $counts = $this->shareableCounts($candidates->pluck('id')->all());

        return $candidates
            ->filter(fn ($role) => ($counts[$role->id] ?? 0) > 0)
            // A batch at a time: every name is shown with a ticked box, and a list too long to
            // read is not one to approve in one click. The next batch appears once these are
            // answered.
            ->take(self::LISTING_PROMPT_LIMIT)
            ->each(fn ($role) => $role->setAttribute('shareable_count', $counts[$role->id]))
            ->values();
    }

    /**
     * List these schedules on the network, on this user's say-so.
     *
     * Only schedules still undecided and editable by the user; anything else in $roleIds is
     * ignored. The whereNull keeps an explicit "Not listed" - a veto on co-listed events - from
     * being overridden by a stale form.
     *
     * A query update: nothing in Role's saving hooks applies (the install switch is checked
     * here, which is all the federation guard there does), and toBase() leaves updated_at alone,
     * which is the schedule page's sitemap lastmod - its page has not changed. No re-queue is
     * needed: federation_enabled is not in Role::FEDERATION_FIELDS, exactly as for the select.
     *
     * @return Collection<int, Role> the schedules that were listed
     */
    public function listSchedulesFor(User $user, array $roleIds): Collection
    {
        if (! $this->isEnabled()) {
            return collect();
        }

        $roleIds = array_map('intval', $roleIds);

        $roles = $this->editableUndecidedSchedules($user)
            ->filter(fn ($role) => in_array((int) $role->id, $roleIds, true))
            ->values();

        if ($roles->isEmpty()) {
            return $roles;
        }

        $listedIds = [];

        foreach ($roles as $role) {
            $updated = Role::whereKey($role->id)
                ->whereNull('federation_enabled')
                ->toBase()
                ->update(['federation_enabled' => true]);

            if ($updated !== 1) {
                continue;
            }

            $listedIds[] = $role->id;

            AuditService::log(
                AuditService::SCHEDULE_UPDATE,
                $user->id,
                'Role',
                $role->id,
                ['federation_enabled' => null],
                ['federation_enabled' => true],
                'Listed on the Event Schedule network',
            );
        }

        return $roles->filter(fn ($role) => in_array($role->id, $listedIds, true))
            ->each(function ($role) {
                $role->federation_enabled = true;
                $role->syncOriginalAttribute('federation_enabled');
            })
            ->values();
    }

    /**
     * Which of these schedules will still publish nothing once listed, because they are not
     * verified - the scopeFederationEligible() rule, not a column check of its own.
     */
    public function heldBack(Collection $roles): Collection
    {
        if ($roles->isEmpty()) {
            return $roles;
        }

        $eligible = Role::whereIn('id', $roles->pluck('id'))
            ->federationEligible(true)
            ->pluck('id')
            ->flip();

        return $roles->reject(fn ($role) => isset($eligible[$role->id]))->values();
    }
}
