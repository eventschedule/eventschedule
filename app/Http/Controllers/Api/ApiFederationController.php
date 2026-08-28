<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Services\FederationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Intake endpoints on the nexus app (eventschedule.com) for events federated by
 * other installs. An instance registers once, an admin approves it once, and its
 * events publish automatically from then on.
 *
 * Unlike the translation intake, these are signed: federation publishes third-party
 * content and outbound links under an operator's name on this domain, so an
 * unauthenticated instance UUID would let anyone impersonate an approved instance.
 */
class ApiFederationController extends Controller
{
    /** The most listings a single instance may hold. Backstop above the route throttle. */
    public const MAX_EVENTS_PER_INSTANCE = 5000;

    /** Events accepted in one request. Senders chunk to this size. */
    public const MAX_ITEMS_PER_REQUEST = 200;

    /** Ids accepted in one reconcile chunk. Manifests are a full id list, not a delta. */
    public const MAX_MANIFEST_IDS = 2000;

    /**
     * How many unreviewed registrations may exist at once.
     *
     * Registration is unauthenticated by design - it is first contact - and only
     * throttled per IP, so without a ceiling the review queue can be flooded faster
     * than a human can clear it. Mirrors MAX_PENDING_PER_INSTANCE on the translation
     * intake, which guards the same shape of endpoint.
     */
    public const MAX_PENDING_INSTANCES = 500;

    /**
     * First contact from an install, and secret rotation for one already known.
     *
     * A brand new instance_id may register unauthenticated - it lands as `pending`
     * and publishes nothing until an admin approves it. Re-registering an EXISTING
     * instance_id is a takeover vector, because the id travels in the clear on every
     * request and is an identifier rather than a credential, so it must be signed
     * with the secret already on record.
     */
    public function register(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        try {
            $validated = $this->validateSignedBody($request, [
                'instance_id' => ['required', 'uuid'],
                'site_url' => ['required', 'url', 'max:255'],
                'name' => ['nullable', 'string', 'max:191'],
                'contact_email' => ['nullable', 'email', 'max:191'],
                'secret' => ['required', 'string', 'min:32', 'max:255'],
                'app_version' => ['nullable', 'string', 'max:32'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }

        $host = strtolower((string) parse_url($validated['site_url'], PHP_URL_HOST));
        if (! $host) {
            return response()->json(['error' => 'Invalid site_url'], 422);
        }

        $instanceId = strtolower($validated['instance_id']);
        $instance = FederatedInstance::where('instance_id', $instanceId)->first();

        if ($instance) {
            // Rotation: prove possession of the current secret before replacing it.
            if (! $this->verifySignature($request, $instance)) {
                return response()->json(['error' => 'Signature verification failed'], 403);
            }

            // A host change is a different site, not a new address for the same one:
            // isAcceptable() authorises backlinks against whatever site_url currently
            // says, so silently accepting this would let an instance approved as
            // goodsite.example start publishing links to spam.example. The flagged_at
            // tripwire below cannot catch it - that compares a *push* against the
            // record, and by then the record itself has already been rewritten.
            $movedHost = $instance->host() !== null && $instance->host() !== $host;

            $instance->fill([
                'site_url' => $validated['site_url'],
                'name' => $validated['name'] ?? $instance->name,
                'contact_email' => $validated['contact_email'] ?? $instance->contact_email,
                'secret' => $validated['secret'],
                'app_version' => $validated['app_version'] ?? $instance->app_version,
            ]);
            $instance->last_seen_at = now();

            if ($movedHost) {
                $instance->flagged_at = now();

                // Back to the review queue, but only from approved: re-pending a
                // suspended instance would hand it an escape from moderation.
                if ($instance->status === FederatedInstance::STATUS_APPROVED) {
                    $instance->status = FederatedInstance::STATUS_PENDING;
                    $instance->approved_at = null;
                    $instance->approved_by = null;
                }
            }

            $instance->save();

            return response()->json(['status' => $instance->status, 'registered' => true]);
        }

        // Ceiling on unreviewed registrations. Existing instances are unaffected - the
        // rotation branch above returns before this - so a flood can only ever delay
        // new arrivals, never disrupt anyone already federating.
        if (FederatedInstance::pending()->count() >= self::MAX_PENDING_INSTANCES) {
            return response()->json(['error' => 'Registration queue is full, try again later'], 429);
        }

        $instance = FederatedInstance::create([
            'instance_id' => $instanceId,
            'site_url' => $validated['site_url'],
            'name' => $validated['name'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'secret' => $validated['secret'],
            'app_version' => $validated['app_version'] ?? null,
            'status' => FederatedInstance::STATUS_PENDING,
            'last_seen_at' => now(),
        ]);

        // Deliberately no email here: contact_email is attacker-supplied on an
        // unauthenticated endpoint, so mailing it on registration would turn this
        // into a spam relay. Notifications are tied to admin actions instead.

        return response()->json(['status' => $instance->status, 'registered' => true], 201);
    }

    /**
     * Receive a batch of listings. Stored whatever the instance's status - approval
     * then becomes a single switch that lights up everything already received.
     */
    public function store(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        $instance = $this->authenticateInstance($request);
        if ($instance instanceof \Illuminate\Http\JsonResponse) {
            return $instance;
        }

        // Only the envelope is all-or-nothing. Validating the items together would make
        // the batch only as good as its worst row: one malformed field 422s everything,
        // the sender stamps nothing, and it rebuilds the identical chunk next run - a
        // permanent silent stall. Per-item validation routes the bad row to skipped_ids
        // instead, which already means "refused, do not retry".
        try {
            $envelope = $this->validateSignedBody($request, [
                'items' => ['required', 'array', 'min:1', 'max:'.self::MAX_ITEMS_PER_REQUEST],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }

        $existingCount = FederatedEvent::where('federated_instance_id', $instance->id)->count();
        $accepted = 0;
        $skipped = 0;
        // Which ones, not just how many. The sender needs this to tell "delivered" from
        // "refused" - conflating them makes it retry a rejected event every hour forever.
        $skippedIds = [];

        foreach ($envelope['items'] as $item) {
            if (! is_array($item)) {
                // No external_id to name it by, so the sender cannot be told which row
                // this was. Counted, not reported.
                $skipped++;

                continue;
            }

            $validator = Validator::make($item, self::itemRules());

            if ($validator->fails()) {
                $skipped++;
                if (is_string($item['external_id'] ?? null) && $item['external_id'] !== '') {
                    $skippedIds[] = $item['external_id'];
                }

                continue;
            }

            $item = $validator->validated();

            if (! $this->isAcceptable($item, $instance)) {
                $skipped++;
                $skippedIds[] = $item['external_id'];

                continue;
            }

            $existing = FederatedEvent::where('federated_instance_id', $instance->id)
                ->where('external_id', $item['external_id'])
                ->first();

            if (! $existing && $existingCount >= self::MAX_EVENTS_PER_INSTANCE) {
                // Report rather than silently truncate, so an operator can see why
                // their catalogue stopped appearing.
                return response()->json([
                    'error' => 'Event limit reached',
                    'limit' => self::MAX_EVENTS_PER_INSTANCE,
                    'accepted' => $accepted,
                    'skipped' => $skipped,
                    'status' => $instance->status,
                ], 422);
            }

            $row = $existing ?: new FederatedEvent([
                'federated_instance_id' => $instance->id,
                'external_id' => $item['external_id'],
            ]);

            // A blocked listing stays blocked: fill() only touches UPSERT_FIELDS,
            // which deliberately excludes blocked_at.
            $row->fill($this->mapPayload($item, $instance));
            $row->save();

            if (! $existing) {
                $existingCount++;
            }
            $accepted++;
        }

        return response()->json([
            'accepted' => $accepted,
            'skipped' => $skipped,
            'skipped_ids' => $skippedIds,
            'status' => $instance->status,
        ]);
    }

    /**
     * Full-manifest reconcile. Events are hard-deleted at the source, so a sender
     * cannot enumerate what it previously sent and no tombstone list is
     * constructible; instead it sends everything it currently considers
     * federatable and this removes the rest.
     *
     * The response closes the loop the other way too: once the sender stamps its
     * watermark it never re-sends, so if rows are missing here (a suspension, a hit
     * cap, a restore) the two sides would deadlock. Reporting the gap lets the
     * sender clear those watermarks and re-push.
     */
    public function reconcile(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        $instance = $this->authenticateInstance($request);
        if ($instance instanceof \Illuminate\Http\JsonResponse) {
            return $instance;
        }

        try {
            $validated = $this->validateSignedBody($request, [
                'external_ids' => ['present', 'array', 'max:'.self::MAX_MANIFEST_IDS],
                'external_ids.*' => ['string', 'max:64'],
                // One token for the whole pass, repeated on every chunk. This is what
                // makes a multi-chunk manifest safe: deleting on "not in this request"
                // would wipe everything the other chunks carried.
                'run_token' => ['required', 'string', 'max:64'],
                'is_final' => ['nullable', 'boolean'],
                'known_ids' => ['nullable', 'array', 'max:'.self::MAX_MANIFEST_IDS],
                'known_ids.*' => ['string', 'max:64'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }

        $manifest = array_values(array_unique($validated['external_ids']));
        $runToken = $validated['run_token'];
        $removed = 0;

        // Mark: everything this chunk names belongs to the current pass.
        if ($manifest) {
            FederatedEvent::where('federated_instance_id', $instance->id)
                ->whereIn('external_id', $manifest)
                ->update(['manifest_token' => $runToken]);
        }

        // Sweep: anything still carrying an older token was not in ANY chunk of this
        // pass, so the source no longer considers it federatable.
        //
        // The flag comes from validateSignedBody(), so it can only have come from the
        // bytes the HMAC covers. Neither $request->boolean() nor a plain
        // $request->validate() would do: both read the merged input bag, so ?is_final=1
        // on the URL would turn a replayed mid-pass chunk into a full-catalogue sweep.
        if (filter_var($validated['is_final'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            // Blocked rows are tombstones: deleting them would let the source
            // resurrect a blocked listing simply by re-publishing it.
            // purge(), not delete(): a mass delete fires no model events, so the stored
            // images would outlive their rows on every hourly reconcile.
            $removed = FederatedEvent::purge(
                FederatedEvent::where('federated_instance_id', $instance->id)
                    ->whereNull('blocked_at')
                    ->where(function ($q) use ($runToken) {
                        $q->whereNull('manifest_token')->orWhere('manifest_token', '!=', $runToken);
                    })
            );
        }

        // Which of the ids the sender believes are synced do we actually not have?
        $checkIds = $validated['known_ids'] ?? $manifest;
        $present = FederatedEvent::where('federated_instance_id', $instance->id)
            ->whereIn('external_id', $checkIds)
            ->pluck('external_id')
            ->all();

        return response()->json([
            'removed' => $removed,
            'missing' => array_values(array_diff($checkIds, $present)),
            'status' => $instance->status,
        ]);
    }

    /**
     * Resolve and verify the calling instance, stamping last_seen_at.
     * Returns a JsonResponse on failure so callers can pass it straight back.
     */
    protected function authenticateInstance(Request $request)
    {
        // json(), not input(): identity and the site_url tripwire below must be decided
        // on the signed bytes, or a replayed body could be re-aimed from the URL.
        $instanceId = strtolower((string) $request->json('instance_id'));

        if (! Str::isUuid($instanceId)) {
            return response()->json(['error' => 'Unknown instance'], 403);
        }

        $instance = FederatedInstance::where('instance_id', $instanceId)->first();

        if (! $instance || ! $this->verifySignature($request, $instance)) {
            return response()->json(['error' => 'Signature verification failed'], 403);
        }

        $siteUrl = $request->json('site_url');
        if ($siteUrl && rtrim((string) $siteUrl, '/') !== rtrim((string) $instance->site_url, '/')) {
            // Two hosts sharing one identity, most likely a restored backup. Accept
            // the data but surface it: silently trusting the new host would let a
            // clone inherit an approved instance's standing.
            $instance->flagged_at = now();
        }

        $instance->last_seen_at = now();
        $instance->save();

        return $instance;
    }

    /**
     * HMAC over the exact raw body, matching the outbound webhook convention
     * (SendWebhook sends `sha256=<hex>` in X-Webhook-Signature).
     */
    protected function verifySignature(Request $request, FederatedInstance $instance): bool
    {
        $header = (string) $request->header(FederationService::SIGNATURE_HEADER);
        $secret = (string) $instance->secret;

        if ($header === '' || $secret === '') {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $header);
    }

    /**
     * Validate against the JSON body alone - the exact bytes the HMAC signs.
     *
     * $request->validate() runs over the merged input bag, which folds in the query
     * string. On a signed endpoint that is a hole: anything the sender omitted from the
     * body can be supplied on the URL, unsigned, and still arrive in $validated. That is
     * how ?is_final=1 could drive reconcile's full-catalogue sweep.
     *
     * @throws ValidationException
     */
    protected function validateSignedBody(Request $request, array $rules): array
    {
        return Validator::make($request->json()->all(), $rules)->validate();
    }

    /**
     * Validation for a single incoming listing.
     *
     * Extracted so each item can be judged on its own - see the note in store() about
     * why a shared `items.*` ruleset is the wrong shape here.
     */
    public static function itemRules(): array
    {
        return [
            'external_id' => ['required', 'string', 'max:64'],
            // Scheme allowlist, not a bare `url`: these land in an href on the public
            // browse page, and the default rule admits ftp/ws/view-source.
            'url' => ['required', 'url:http,https', 'max:1024'],
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'language' => ['nullable', 'string', 'max:10'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            // Must be a real IANA zone. The public card formats occurrences with
            // setTimezone(), which throws on anything else - so an unvalidated value
            // here would let one instance take the whole browse page down.
            'timezone' => ['nullable', 'timezone'],
            'occurrences' => ['nullable', 'array', 'max:50'],
            'occurrences.*' => ['date'],
            'occurrences_hash' => ['nullable', 'string', 'max:64'],
            'schedule_name' => ['nullable', 'string', 'max:255'],
            'schedule_url' => ['nullable', 'url:http,https', 'max:1024'],
            'image_url' => ['nullable', 'url:http,https', 'max:1024'],
            // The joining link is no longer stored, only whether there is one. The
            // event_url rule stays so an install on an older release is not rejected:
            // mapPayload() reads its presence and discards the value.
            'event_url' => ['nullable', 'url:http,https', 'max:1024'],
            'is_online' => ['nullable', 'boolean'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'country_code' => ['nullable', 'string', 'max:2'],
            'geo_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'geo_lon' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Quality and policy gates federated rows would otherwise bypass. Local events
     * pass through publicScheduleFilter() and excludeLikelyTest() before they reach
     * discovery, so without these the network would hold third-party content to a
     * lower bar than this site's own.
     */
    protected function isAcceptable(array $item, FederatedInstance $instance): bool
    {
        // The backlink must live on the instance's own host, or an approved instance
        // becomes an open backlink farm.
        if (! $instance->ownsUrl($item['url'])) {
            return false;
        }

        // Something to show and something to link to.
        $name = trim($this->stripControlCharacters($item['name']));
        if ($name === '') {
            return false;
        }

        if ($this->looksLikeTest($name)) {
            return false;
        }

        // The nexus operator's deliberate country policy applies to federated rows
        // too, now that in-person events carry their own country.
        $exclude = strtolower(trim((string) config('app.search_exclude_country', '')));
        if ($exclude !== '' && strtolower((string) ($item['country_code'] ?? '')) === $exclude) {
            return false;
        }

        // A listing with no date at all can never be ordered or expired.
        return ! empty($item['starts_at']) || ! empty($item['occurrences']);
    }

    /** Mirror of Event::scopeExcludeLikelyTest() applied to a single incoming name. */
    protected function looksLikeTest(string $name): bool
    {
        $lower = mb_strtolower($name);

        if (in_array($lower, Event::LIKELY_TEST_NAMES, true)) {
            return true;
        }

        foreach ([Event::LIKELY_TEST_NAME_REGEX, Event::REPEATED_CHAR_REGEX, Event::LIKELY_TEST_WEAK_REGEX] as $pattern) {
            if (preg_match('/'.str_replace('[[:space:]]', '\s', $pattern).'/iu', $lower)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Payload to column mapping, including the derived next_occurrence_at that
     * browse orders and paginates by (a JSON array cannot be indexed or sorted on).
     */
    protected function mapPayload(array $item, FederatedInstance $instance): array
    {
        $occurrences = array_values(array_filter($item['occurrences'] ?? []));
        sort($occurrences);

        $next = null;
        foreach ($occurrences as $occurrence) {
            if (strtotime($occurrence) >= strtotime('-1 day')) {
                $next = $occurrence;
                break;
            }
        }
        $next ??= $item['starts_at'] ?? ($occurrences ? end($occurrences) : null);

        return [
            'url' => $item['url'],
            'name' => $this->stripControlCharacters($item['name']),
            'short_description' => isset($item['short_description'])
                ? $this->stripControlCharacters($item['short_description'])
                : null,
            'language' => $item['language'] ?? null,
            'starts_at' => $item['starts_at'] ?? null,
            'ends_at' => $item['ends_at'] ?? null,
            'timezone' => $item['timezone'] ?? null,
            'occurrences' => $occurrences ?: null,
            'next_occurrence_at' => $next,
            'occurrences_hash' => $item['occurrences_hash'] ?? null,
            'schedule_name' => isset($item['schedule_name'])
                ? $this->stripControlCharacters($item['schedule_name'])
                : null,
            // Held to the same host rule as `url`. This one is rendered as a clickable
            // link on the review screen, so without the check an instance could aim an
            // admin anywhere it liked, which is the backlink-farm hole isAcceptable()
            // exists to close.
            //
            // Nulled rather than refused: a refusal is permanent (skipped_ids means "do
            // not retry"), so one bad schedule_url would keep an otherwise perfectly
            // good event off the network forever.
            'schedule_url' => $instance->ownsUrl($item['schedule_url'] ?? null)
                ? $item['schedule_url']
                : null,
            // Only the advertised URL is stored here. The local copy is fetched out of
            // band through UrlUtils::safeHttpGet(), so an intake request never makes
            // this server fetch an attacker-supplied URL on the request path.
            'image_url' => $item['image_url'] ?? null,
            // A flag, never the joining link itself. Senders on an older release still
            // send event_url; its presence means the same thing and is all this app
            // has ever used it for.
            'is_online' => ! empty($item['is_online']) || ! empty($item['event_url']),
            'venue_name' => isset($item['venue_name']) ? $this->stripControlCharacters($item['venue_name']) : null,
            'address' => isset($item['address']) ? $this->stripControlCharacters($item['address']) : null,
            'city' => isset($item['city']) ? $this->stripControlCharacters($item['city']) : null,
            'state' => isset($item['state']) ? $this->stripControlCharacters($item['state']) : null,
            'postal_code' => $item['postal_code'] ?? null,
            'country_code' => isset($item['country_code']) ? strtoupper($item['country_code']) : null,
            'geo_lat' => $item['geo_lat'] ?? null,
            'geo_lon' => $item['geo_lon'] ?? null,
        ];
    }

    /**
     * Remove control characters (keeping newlines and tabs) from remote input,
     * preserving the Unicode format characters RTL layout depends on. Returns ''
     * for invalid UTF-8, which the caller treats as an empty field.
     */
    protected function stripControlCharacters(string $value): string
    {
        return preg_replace('/[^\P{Cc}\n\t]/u', '', $value) ?? '';
    }
}
