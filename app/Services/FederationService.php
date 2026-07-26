<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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
        // written by instanceId() during register(), which only runs on the off->on
        // transition - so its presence means "connected at least once". Checking the
        // toggle alone would nag someone who deliberately turned it back off.
        if (Setting::get('federation_instance_id')) {
            return false;
        }

        // The "first event" trigger. Reuses the real selection query rather than
        // counting rows, so the prompt only appears when enabling would actually
        // publish something - and stays away on an install holding only demo data.
        //
        // Cached because the schedule page is one of the most-loaded in the AP and this
        // query is not cheap; the answer flips once and then stays true.
        return Cache::remember(
            'federation:has_shareable_events',
            now()->addMinutes(self::ADOPTION_PROMPT_CACHE_MINUTES),
            fn () => $this->federatableQuery()->exists()
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
        $payload = [
            'instance_id' => $this->instanceId(),
            'site_url' => rtrim((string) config('app.url'), '/'),
            'name' => config('app.name'),
            'contact_email' => Setting::get('federation_contact_email'),
            'secret' => $this->secret(),
            'app_version' => config('self-update.version_installed'),
        ];

        return $this->send('/api/federation/register', $payload);
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
     * Events this install is willing to share.
     *
     * Mirrors the nexus's own discovery query rather than inventing a second
     * definition of "public", so a federated listing is held to the same bar as a
     * local one: verified and listed schedule, accepted association, no demo or
     * throwaway-test content.
     */
    public function federatableQuery()
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
            ->whereHas('roles', function ($q) {
                $q->where('event_role.is_accepted', true)
                    ->where('roles.federation_enabled', true)
                    ->where('roles.is_deleted', false)
                    ->where('roles.is_unlisted', false)
                    ->whereNotNull('roles.user_id')
                    ->where(function ($r) {
                        $r->where(function ($x) {
                            $x->whereNotNull('roles.email')->whereNotNull('roles.email_verified_at');
                        })->orWhere(function ($x) {
                            $x->whereNotNull('roles.phone')->whereNotNull('roles.phone_verified_at');
                        });
                    });
            })
            ->whereDoesntHave('roles', function ($r) {
                $r->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                    ->orWhere('subdomain', 'like', 'demo-%');
            })
            ->excludeLikelyTest();
    }

    /**
     * Flatten an event into the wire format. Returns null when there is nothing
     * worth listing.
     */
    public function buildPayload(Event $event): ?array
    {
        $event->loadMissing('roles');

        $image = $event->getImageUrl();
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

        $venue = $event->venue;
        // Not role(), which only returns a talent schedule and would leave a
        // venue-only event with no attribution at all.
        $schedule = $event->getViewableRole();

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
            'event_url' => $event->event_url ?: null,
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
                // the UTC instant. Deliberately not getStartDateTime(), which falls back
                // to the authenticated user's timezone - there is no user in a scheduled
                // command, and an evening event would land on the wrong day.
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
     */
    protected function send(string $path, array $payload): array
    {
        $payload['instance_id'] ??= $this->instanceId();
        $payload['site_url'] ??= rtrim((string) config('app.url'), '/');

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

        $this->recordSuccess($json['status'] ?? null);

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

    protected function recordSuccess(?string $status): void
    {
        Setting::set('federation_last_error', null);
        Setting::set('federation_last_error_at', null);
        Setting::set('federation_last_synced_at', (string) now());

        if ($status) {
            Setting::set('federation_status', $status);
        }
    }

    /**
     * Events that would be shared on the next run, for the "here is exactly what
     * will be published" preview beside the operator's toggle.
     */
    public function previewEvents(int $limit = 25)
    {
        return $this->federatableQuery()->orderBy('starts_at')->limit($limit)->get();
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
}
