<?php

namespace App\Services;

use App\Models\Event;
use App\Models\MicrosoftCalendarSync;
use App\Models\Role;
use App\Models\User;
use App\Repos\EventRepo;
use App\Services\Concerns\ConvertsLocationToVenue;
use App\Utils\EventTextGenerator;
use App\Utils\MarkdownUtils;
use App\Utils\SlugPatternUtils;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Outlook / Microsoft 365 calendar sync via the Microsoft Graph API.
 *
 * Mirrors GoogleCalendarService, but the transport is hand-rolled on Laravel's
 * Http facade (like CalDAVService) rather than an SDK client. Graph hosts are
 * fixed, so no SSRF guard is needed. Inbound sync uses Graph delta queries
 * (calendarView/delta) whose deltaLink is persisted in roles.microsoft_sync_token.
 */
class MicrosoftCalendarService
{
    use ConvertsLocationToVenue;

    const GRAPH_BASE = 'https://graph.microsoft.com/v1.0';

    const SCOPES = 'openid profile email offline_access https://graph.microsoft.com/Calendars.ReadWrite';

    const MAX_DELTA_PAGES = 50;

    const SUBSCRIPTION_HOURS = 60;

    protected $eventRepo;

    /**
     * The user whose token the current Graph requests are made with. Set by
     * ensureValidToken(); read by graph(). Mirrors the implicit per-instance
     * account state GoogleCalendarService keeps via setAccessToken().
     */
    protected ?User $currentUser = null;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    /**
     * A Graph HTTP client bound to the current user's access token.
     */
    protected function graph()
    {
        return Http::withToken($this->currentUser?->microsoft_token)
            ->acceptJson()
            ->timeout(30)
            // Never follow a redirect: the bearer token must not be forwarded off-host.
            ->withOptions(['allow_redirects' => false])
            ->baseUrl(self::GRAPH_BASE);
    }

    /**
     * Guard that a server-supplied absolute delta/pagination URL stays on the Graph host
     * before we GET it with the bearer attached (defense-in-depth against a token leak).
     */
    protected function isGraphUrl(string $url): bool
    {
        // Relative paths (resolved against baseUrl) are always in-host.
        if (! preg_match('#^https?://#i', $url)) {
            return true;
        }

        return strcasecmp((string) parse_url($url, PHP_URL_HOST), 'graph.microsoft.com') === 0;
    }

    protected function tenant(): string
    {
        return config('services.microsoft.tenant') ?: 'common';
    }

    protected function authorizeEndpoint(): string
    {
        return 'https://login.microsoftonline.com/'.$this->tenant().'/oauth2/v2.0/authorize';
    }

    protected function tokenEndpoint(): string
    {
        return 'https://login.microsoftonline.com/'.$this->tenant().'/oauth2/v2.0/token';
    }

    /**
     * Get the authorization URL for Microsoft OAuth
     */
    public function getAuthUrl(): string
    {
        return $this->buildAuthUrl('select_account');
    }

    /**
     * Get the authorization URL forcing a fresh consent (to re-obtain a refresh token)
     */
    public function getAuthUrlWithForce(): string
    {
        return $this->buildAuthUrl('consent');
    }

    protected function buildAuthUrl(string $prompt): string
    {
        $params = [
            'client_id' => config('services.microsoft.client_id'),
            'response_type' => 'code',
            'redirect_uri' => config('services.microsoft.redirect'),
            'response_mode' => 'query',
            'scope' => self::SCOPES,
            'state' => $this->generateAndStoreState(),
            'prompt' => $prompt,
        ];

        return $this->authorizeEndpoint().'?'.http_build_query($params);
    }

    /**
     * Generate a CSRF state token for the OAuth flow and store it in the session.
     */
    private function generateAndStoreState(): string
    {
        $state = bin2hex(random_bytes(32));
        session(['microsoft_oauth_state' => $state]);

        return $state;
    }

    /**
     * Exchange authorization code for an access token
     */
    public function getAccessToken(string $code): array
    {
        $response = Http::asForm()->post($this->tokenEndpoint(), [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.microsoft.client_id'),
            'client_secret' => config('services.microsoft.client_secret'),
            'code' => $code,
            'redirect_uri' => config('services.microsoft.redirect'),
            'scope' => self::SCOPES,
        ]);

        return $response->json() ?? [];
    }

    /**
     * Refresh the access token if needed, persisting Microsoft's rotated refresh token.
     *
     * Microsoft returns a NEW refresh_token on every refresh and may invalidate the
     * previous one, so we hold a per-user lock to avoid two workers rotating at once.
     */
    public function refreshTokenIfNeeded(User $user): bool
    {
        $this->currentUser = $user;

        if (! $user->microsoft_token || ! $user->microsoft_refresh_token) {
            Log::warning('User missing Microsoft tokens', [
                'user_id' => $user->id,
                'has_access_token' => ! is_null($user->microsoft_token),
                'has_refresh_token' => ! is_null($user->microsoft_refresh_token),
            ]);

            return false;
        }

        if ($this->tokenStillValid($user)) {
            return true;
        }

        // Refresh under a lock so concurrent jobs don't each rotate the refresh token.
        // The lock TTL (15s) must outlive the refresh HTTP timeout (8s below); otherwise the
        // lock could auto-expire mid-request and let a second worker rotate the token too.
        $lock = Cache::lock('microsoft-token-refresh-'.$user->id, 15);

        try {
            // Wait longer than the 8s refresh HTTP timeout so a waiter outlasts a legitimate
            // in-flight refresh (and still under the 15s lock TTL).
            $lock->block(10);
        } catch (LockTimeoutException $e) {
            // Another process refreshed while we waited - re-read and trust its result.
            $user->refresh();
            $this->currentUser = $user;

            return $this->tokenStillValid($user);
        }

        try {
            // Re-check inside the lock: another worker may have already rotated the token.
            $user->refresh();
            $this->currentUser = $user;
            if ($this->tokenStillValid($user)) {
                return true;
            }

            $response = Http::asForm()->timeout(8)->post($this->tokenEndpoint(), [
                'grant_type' => 'refresh_token',
                'client_id' => config('services.microsoft.client_id'),
                'client_secret' => config('services.microsoft.client_secret'),
                'refresh_token' => $user->microsoft_refresh_token,
                'scope' => self::SCOPES,
            ]);

            $newToken = $response->json() ?? [];

            if ($response->successful() && ! isset($newToken['error']) && ! empty($newToken['access_token'])) {
                $user->update([
                    'microsoft_token' => $newToken['access_token'],
                    'microsoft_refresh_token' => $newToken['refresh_token'] ?? $user->microsoft_refresh_token,
                    'microsoft_token_expires_at' => now()->addSeconds($newToken['expires_in'] ?? 3600),
                ]);
                $this->currentUser = $user;

                return true;
            }

            Log::error('Failed to refresh Microsoft Calendar token', [
                'user_id' => $user->id,
                'error' => $newToken['error'] ?? 'unknown',
                'error_description' => $newToken['error_description'] ?? 'Unknown error',
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Exception during Microsoft Calendar token refresh', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        } finally {
            $lock->release();
        }
    }

    /**
     * True when the stored access token is valid for more than 1 minute.
     */
    protected function tokenStillValid(User $user): bool
    {
        $expiresAt = $user->microsoft_token_expires_at;

        if (! $expiresAt) {
            return false;
        }

        if (is_string($expiresAt)) {
            $expiresAt = Carbon::parse($expiresAt);
        }

        return $expiresAt->isFuture() && now()->diffInMinutes($expiresAt) > 1;
    }

    /**
     * Ensure the user has a valid Microsoft access token (refreshing if needed).
     */
    public function ensureValidToken(User $user): bool
    {
        return $this->refreshTokenIfNeeded($user);
    }

    /**
     * Build the Graph event payload shared by create and update.
     *
     * Graph interprets start/end.dateTime as wall-clock in start/end.timeZone
     * (offset-less), unlike Google's UTC-with-offset - so we render the local
     * wall-clock in the schedule's timezone.
     */
    protected function buildEventPayload(Event $event, Role $role, bool $isUpdate = false): array
    {
        $tz = $role->timezone ?? 'UTC';
        $startLocal = $event->getStartDateTime()->copy()->setTimezone($tz);
        $minutes = Event::durationHoursToMinutes($event->duration ?? 2);

        $payload = [
            'subject' => $event->name,
            'sensitivity' => $event->is_private ? 'private' : 'normal',
        ];

        if ($minutes <= 0) {
            // All-day / zero-duration event: Graph rejects end == start and requires whole-day
            // midnight boundaries (inbound all-day events arrive here with duration 0).
            $startDay = $startLocal->copy()->startOfDay();
            $payload['isAllDay'] = true;
            $payload['start'] = ['dateTime' => $startDay->format('Y-m-d\T00:00:00'), 'timeZone' => $tz];
            $payload['end'] = ['dateTime' => $startDay->copy()->addDay()->format('Y-m-d\T00:00:00'), 'timeZone' => $tz];
        } else {
            $endLocal = $event->getStartDateTime()->copy()->addMinutes($minutes)->setTimezone($tz);
            $payload['start'] = ['dateTime' => $startLocal->format('Y-m-d\TH:i:s'), 'timeZone' => $tz];
            $payload['end'] = ['dateTime' => $endLocal->format('Y-m-d\TH:i:s'), 'timeZone' => $tz];
        }

        if ($role->calendar_description_template) {
            $event->loadMissing(['venue', 'tickets']);
            $payload['body'] = [
                'contentType' => 'text',
                'content' => EventTextGenerator::parseTemplate($role->calendar_description_template, $event, $role, false, ['url_include_https' => false]),
            ];
        } elseif (! $isUpdate || ! empty($event->description)) {
            // On update, only send the body when non-empty so we don't clobber the
            // remote description with a blank (mirrors GoogleCalendarService).
            $payload['body'] = ['contentType' => 'text', 'content' => $event->description ?? ''];
        }

        if ($event->venue && $event->venue->bestAddress()) {
            $payload['location'] = ['displayName' => $event->venue->bestAddress()];
        }

        return $payload;
    }

    /**
     * Create an Outlook event from an Event model. Returns the Graph event array or null.
     */
    public function createEvent(Event $event, Role $role, ?string $calendarId = null): ?array
    {
        try {
            if ($calendarId === null) {
                $calendarId = $role->getMicrosoftCalendarId();
            }

            $url = $calendarId ? "/me/calendars/{$calendarId}/events" : '/me/events';

            $payload = $this->buildEventPayload($event, $role);

            // Online events (no venue) become Teams meetings when the schedule opts in.
            $teams = $role->microsoft_create_teams_meetings && ! $event->venue;
            if ($teams) {
                $payload['isOnlineMeeting'] = true;
                $payload['onlineMeetingProvider'] = 'teamsForBusiness';
            }

            $response = $this->graph()->post($url, $payload);

            // Personal Microsoft accounts reject the Teams flags with a 400/403 - retry once
            // without them. Gate on those statuses specifically so a 401 (auth) or 429
            // (throttle, honoring Retry-After) does not trigger a wasted immediate re-POST.
            if ($teams && in_array($response->status(), [400, 403], true)) {
                unset($payload['isOnlineMeeting'], $payload['onlineMeetingProvider']);
                $teams = false;
                $response = $this->graph()->post($url, $payload);
            }

            if (! $response->successful()) {
                Log::error('Failed to create Microsoft Calendar event', [
                    'event_id' => $event->id,
                    'role_id' => $role->id,
                    'calendar_id' => $calendarId,
                    'status' => $response->status(),
                    'retry_after' => $response->header('Retry-After'),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $created = $response->json();

            // Write the Teams join URL back to event_url when the event has no link yet.
            // Reload a FULL model and saveQuietly() so partial-select *_html columns are
            // preserved and no outbound sync loop is triggered.
            if ($teams && ! empty($created['onlineMeeting']['joinUrl'])) {
                $fresh = Event::find($event->id);
                if ($fresh && empty($fresh->event_url)) {
                    $fresh->forceFill(['event_url' => $created['onlineMeeting']['joinUrl']])->saveQuietly();
                }
            } elseif ($teams) {
                Log::info('Microsoft Teams meeting created without a joinUrl (async provisioning)', [
                    'event_id' => $event->id,
                ]);
            }

            UsageTrackingService::track(UsageTrackingService::MSCAL_CREATE, $role->id);

            return $created;
        } catch (\Throwable $e) {
            Log::error('Exception creating Microsoft Calendar event', [
                'event_id' => $event->id,
                'role_id' => $role->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Update an Outlook event. Graph event ids are user-global, so we PATCH /me/events/{id}.
     */
    public function updateEvent(Event $event, string $microsoftEventId, Role $role, ?string $calendarId = null): ?array
    {
        try {
            $payload = $this->buildEventPayload($event, $role, true);

            $response = $this->graph()->patch("/me/events/{$microsoftEventId}", $payload);

            if (! $response->successful()) {
                Log::error('Failed to update Microsoft Calendar event', [
                    'event_id' => $event->id,
                    'microsoft_event_id' => $microsoftEventId,
                    'status' => $response->status(),
                    'retry_after' => $response->header('Retry-After'),
                ]);

                return null;
            }

            UsageTrackingService::track(UsageTrackingService::MSCAL_UPDATE, $role->id);

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Exception updating Microsoft Calendar event', [
                'event_id' => $event->id,
                'microsoft_event_id' => $microsoftEventId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Delete an Outlook event. A 404/410 (already gone) counts as success.
     */
    public function deleteEvent(string $microsoftEventId, ?string $calendarId = null, int $roleId = 0): bool
    {
        try {
            $response = $this->graph()->delete("/me/events/{$microsoftEventId}");

            if ($response->successful() || $response->status() === 404 || $response->status() === 410) {
                UsageTrackingService::track(UsageTrackingService::MSCAL_DELETE, $roleId);

                return true;
            }

            Log::error('Failed to delete Microsoft Calendar event', [
                'microsoft_event_id' => $microsoftEventId,
                'status' => $response->status(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Exception deleting Microsoft Calendar event', [
                'microsoft_event_id' => $microsoftEventId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * List the user's Outlook calendars.
     */
    public function getCalendars(User $user): array
    {
        try {
            if (! $this->ensureValidToken($user)) {
                return [];
            }

            $calendars = [];
            $url = '/me/calendars?'.http_build_query(['$select' => 'id,name,isDefaultCalendar,canEdit', '$top' => 100]);

            $pages = 0;
            while ($url && $pages++ < self::MAX_DELTA_PAGES) {
                $response = $this->graph()->get($url);

                if (! $response->successful()) {
                    Log::error('Failed to get Microsoft Calendars', ['status' => $response->status()]);
                    break;
                }

                $body = $response->json();
                foreach (($body['value'] ?? []) as $calendar) {
                    $calendars[] = [
                        'id' => $calendar['id'],
                        'summary' => $calendar['name'] ?? '',
                        'primary' => $calendar['isDefaultCalendar'] ?? false,
                    ];
                }

                $url = $body['@odata.nextLink'] ?? null;
            }

            return $calendars;
        } catch (\Throwable $e) {
            Log::error('Exception getting Microsoft Calendars', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Push all non-draft events for a role to Outlook.
     *
     * @param  bool  $force  When true, deletes existing sync rows/copies first so a calendar switch leaves no orphans.
     */
    public function syncUserEvents(User $user, Role $role, bool $force = false): array
    {
        $results = ['created' => 0, 'updated' => 0, 'errors' => 0, 'orphan_delete_errors' => 0];

        if (! $this->ensureValidToken($user)) {
            $results['errors']++;

            return $results;
        }

        if ($force) {
            $existingSyncs = MicrosoftCalendarSync::where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->whereNotNull('microsoft_event_id')
                ->get(['microsoft_event_id', 'microsoft_calendar_id']);

            foreach ($existingSyncs as $row) {
                try {
                    $this->deleteEvent($row->microsoft_event_id, $row->microsoft_calendar_id, $role->id);
                } catch (\Throwable $e) {
                    $results['orphan_delete_errors']++;
                    Log::warning('Failed to delete Microsoft orphan from previous calendar', [
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                        'microsoft_event_id' => $row->microsoft_event_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            MicrosoftCalendarSync::where('user_id', $user->id)->where('role_id', $role->id)->delete();
        }

        $events = Event::whereHas('roles', function ($query) use ($role) {
            $query->where('roles.id', $role->id);
        })->where('is_draft', false)->get();

        foreach ($events as $event) {
            try {
                $existingSync = MicrosoftCalendarSync::where('user_id', $user->id)
                    ->where('event_id', $event->id)
                    ->where('role_id', $role->id)
                    ->first();

                if ($existingSync?->microsoft_event_id) {
                    continue;
                }

                $calendarId = $role->getMicrosoftCalendarId();
                $msEvent = $this->createEvent($event, $role, $calendarId);
                if ($msEvent) {
                    MicrosoftCalendarSync::updateOrCreate(
                        ['user_id' => $user->id, 'event_id' => $event->id, 'role_id' => $role->id],
                        ['microsoft_event_id' => $msEvent['id'], 'microsoft_calendar_id' => $calendarId]
                    );
                    $results['created']++;
                } else {
                    $results['errors']++;
                }
            } catch (\Throwable $e) {
                Log::error('Failed to sync event to Microsoft Calendar', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
                $results['errors']++;
            }
        }

        return $results;
    }

    /**
     * Pull events from Outlook into Event Schedule using a Graph delta query.
     */
    public function syncFromMicrosoftCalendar(User $user, Role $role, ?string $calendarId): array
    {
        $results = ['created' => 0, 'updated' => 0, 'errors' => 0];

        // Serialize inbound sync per role: the webhook job and the 15-min poll (or two
        // overlapping runs) could otherwise both import the same remote event as two local
        // events (the sync-row lookup + name/time fallback are not constraint-backed).
        $lock = Cache::lock('microsoft-role-sync-'.$role->id, 120);
        if (! $lock->get()) {
            // Another inbound sync for this role is running; its deltaLink advance covers
            // these changes, so skipping is safe (not a lost update).
            return $results;
        }

        $restarted = false;

        try {
            if (! $this->ensureValidToken($user)) {
                $results['errors']++;

                return $results;
            }

            $nextUrl = $role->microsoft_sync_token ?: $this->initialDeltaUrl($calendarId);
            $deltaLink = null;
            $pages = 0;

            while ($nextUrl) {
                if (++$pages > self::MAX_DELTA_PAGES) {
                    Log::warning('Microsoft delta pagination cap hit', ['role_id' => $role->id]);
                    break;
                }

                // The nextLink/deltaLink comes from a Graph response or the stored sync token;
                // refuse to send the bearer anywhere but graph.microsoft.com.
                if (! $this->isGraphUrl($nextUrl)) {
                    Log::error('Microsoft delta URL is off-host, aborting', ['role_id' => $role->id]);
                    $results['errors']++;
                    break;
                }

                $response = $this->graph()->get($nextUrl);

                // Expired delta token - clear it and restart a full-window sync once.
                if ($response->status() === 410 || in_array($response->json('error.code'), ['resyncRequired', 'syncStateNotFound'], true)) {
                    if ($restarted) {
                        $results['errors']++;
                        break;
                    }
                    $restarted = true;
                    $role->microsoft_sync_token = null;
                    $role->save();
                    $nextUrl = $this->initialDeltaUrl($calendarId);

                    continue;
                }

                if (! $response->successful()) {
                    Log::error('Failed to fetch Microsoft delta', [
                        'role_id' => $role->id,
                        'status' => $response->status(),
                        'retry_after' => $response->header('Retry-After'),
                    ]);
                    $results['errors']++;
                    break;
                }

                $body = $response->json();

                foreach (($body['value'] ?? []) as $item) {
                    try {
                        $outcome = $this->processDeltaItem($item, $role, $calendarId);
                        if ($outcome === 'created') {
                            $results['created']++;
                            UsageTrackingService::track(UsageTrackingService::MSCAL_SYNC, $role->id);
                        } elseif ($outcome === 'updated') {
                            $results['updated']++;
                            UsageTrackingService::track(UsageTrackingService::MSCAL_SYNC, $role->id);
                        }
                    } catch (\Throwable $e) {
                        Log::error('Failed to sync individual Microsoft event', [
                            'microsoft_event_id' => $item['id'] ?? null,
                            'role_id' => $role->id,
                            'error' => $e->getMessage(),
                        ]);
                        $results['errors']++;
                    }
                }

                if (! empty($body['@odata.nextLink'])) {
                    $nextUrl = $body['@odata.nextLink'];
                } elseif (! empty($body['@odata.deltaLink'])) {
                    $deltaLink = $body['@odata.deltaLink'];
                    $nextUrl = null;
                } else {
                    $nextUrl = null;
                }
            }

            if ($deltaLink) {
                $role->microsoft_sync_token = $deltaLink;
                $role->microsoft_last_sync_at = now();
                $role->save();
            }
        } catch (\Throwable $e) {
            Log::error('Failed to sync from Microsoft Calendar', [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'error' => $e->getMessage(),
            ]);
            $results['errors']++;
        } finally {
            $lock->release();
        }

        return $results;
    }

    /**
     * Build the initial calendarView/delta URL for a full-window sync.
     */
    protected function initialDeltaUrl(?string $calendarId): string
    {
        $base = $calendarId
            ? "/me/calendars/{$calendarId}/calendarView/delta"
            : '/me/calendarView/delta';

        return $base.'?'.http_build_query([
            'startDateTime' => now()->subDays(30)->toIso8601String(),
            'endDateTime' => now()->addDays(365)->toIso8601String(),
        ]);
    }

    /**
     * Reconcile one delta item. Returns 'created', 'updated', or 'skipped'.
     */
    protected function processDeltaItem(array $item, Role $role, ?string $calendarId): string
    {
        // Removed events: drop the mapping only, never the local Event (Google does the
        // same - remote deletions are not propagated).
        if (array_key_exists('@removed', $item)) {
            if (! empty($item['id'])) {
                MicrosoftCalendarSync::where('role_id', $role->id)
                    ->where('microsoft_event_id', $item['id'])
                    ->delete();
            }

            return 'skipped';
        }

        $existingSync = MicrosoftCalendarSync::where('role_id', $role->id)
            ->where('microsoft_event_id', $item['id'])
            ->first();
        $existingEvent = $existingSync ? Event::find($existingSync->event_id) : null;

        // Fallback dedupe by name + start time to avoid importing an outbound copy twice.
        if (! $existingEvent) {
            $startTime = $this->parseGraphDateTime($item['start'] ?? null);
            if ($startTime) {
                $existingEvent = Event::where('name', ($item['subject'] ?? '') ?: __('messages.untitled_event'))
                    ->where('starts_at', $startTime->format('Y-m-d H:i:s'))
                    ->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role->id);
                    })
                    ->first();
            }
        }

        if ($existingEvent) {
            $changed = $this->updateEventFromMicrosoft($item, $existingEvent, $role);

            // Backfill the mapping when matched via the name+time fallback.
            if (! $existingSync) {
                MicrosoftCalendarSync::firstOrCreate(
                    ['user_id' => $role->user_id, 'event_id' => $existingEvent->id, 'role_id' => $role->id],
                    ['microsoft_event_id' => $item['id'], 'microsoft_calendar_id' => $calendarId]
                );
            }

            return $changed ? 'updated' : 'skipped';
        }

        $this->createEventFromMicrosoft($item, $role, $calendarId);

        return 'created';
    }

    /**
     * Parse a Graph dateTimeTimeZone object into a UTC Carbon instance.
     */
    protected function parseGraphDateTime(?array $dt): ?Carbon
    {
        if (! $dt || empty($dt['dateTime'])) {
            return null;
        }

        try {
            $tz = new \DateTimeZone($dt['timeZone'] ?? 'UTC');
        } catch (\Throwable $e) {
            // Graph may return Windows zone names (e.g. "Pacific Standard Time") that
            // Carbon can't parse - fall back to UTC (the default calendarView tz).
            $tz = new \DateTimeZone('UTC');
        }

        return Carbon::parse($dt['dateTime'], $tz)->utc();
    }

    /**
     * Create an Event Schedule event from a Graph event.
     */
    protected function createEventFromMicrosoft(array $item, Role $role, ?string $calendarId): Event
    {
        $event = new Event;
        $event->user_id = $role->user_id;
        $event->creator_role_id = $role->id;
        $event->name = ($item['subject'] ?? '') ?: __('messages.untitled_event');
        $event->description = MarkdownUtils::convertHtmlToMarkdown($item['body']['content'] ?? '');

        $start = $this->parseGraphDateTime($item['start'] ?? null);
        if ($start) {
            $event->starts_at = $start;
        }

        $end = $this->parseGraphDateTime($item['end'] ?? null);
        $isAllDay = $item['isAllDay'] ?? false;
        if ($start && $end && ! $isAllDay) {
            $event->duration = $start->diffInHours($end);
        } elseif ($start && $end && $isAllDay) {
            $days = $start->diffInDays($end);
            $event->duration = $days > 1 ? $days * 24 : 0;
        } else {
            $event->duration = 2;
        }

        // Generate slug AFTER starts_at is set (for date variables)
        $event->slug = SlugPatternUtils::generateSlug(
            $role->slug_pattern,
            $event->name,
            null,
            $event,
            $role
        );

        if ($defaultCategoryId = EventRepo::resolveDefaultCategoryId($role)) {
            $event->category_id = $defaultCategoryId;
        }

        $event->save();

        $event->roles()->attach($role->id, [
            'is_accepted' => true,
        ]);

        MicrosoftCalendarSync::create([
            'user_id' => $role->user_id,
            'event_id' => $event->id,
            'role_id' => $role->id,
            'microsoft_event_id' => $item['id'],
            'microsoft_calendar_id' => $calendarId,
        ]);

        $location = $item['location']['displayName'] ?? null;
        if ($location) {
            $venue = $this->convertLocationToVenue($role, $location);
            if ($venue && ! $event->roles()->where('type', 'venue')->exists()) {
                $event->roles()->attach($venue->id, [
                    'is_accepted' => $role->user->isMember($venue->subdomain),
                ]);
            }
        }

        return $event;
    }

    /**
     * Update an Event Schedule event from a Graph event. Returns true when a change was saved.
     */
    protected function updateEventFromMicrosoft(array $item, Event $event, Role $role): bool
    {
        $event->name = ($item['subject'] ?? '') ?: __('messages.untitled_event');

        if (! empty($item['body']['content'])) {
            // Graph re-serializes our plain-text body as HTML, so converting it back on every
            // poll can differ byte-for-byte from the stored markdown. Only assign when it
            // actually changed so unchanged events don't re-save (churn + usage inflation).
            $converted = MarkdownUtils::convertHtmlToMarkdown($item['body']['content']);
            if ($converted !== $event->description) {
                $event->description = $converted;
            }
        }

        // starts_at is not date-cast; assign the formatted string so an unchanged time
        // reads clean under isDirty() (mirrors GoogleCalendarService).
        $start = $this->parseGraphDateTime($item['start'] ?? null);
        if ($start) {
            $event->starts_at = $start->format('Y-m-d H:i:s');
        }

        $end = $this->parseGraphDateTime($item['end'] ?? null);
        $isAllDay = $item['isAllDay'] ?? false;
        if ($start && $end && ! $isAllDay) {
            $event->duration = $start->diffInHours($end);
        } elseif ($start && $end && $isAllDay) {
            $days = $start->diffInDays($end);
            $event->duration = $days > 1 ? $days * 24 : 0;
        }

        $venueAttached = false;
        $location = $item['location']['displayName'] ?? null;
        if ($location) {
            $venue = $this->convertLocationToVenue($role, $location);
            if ($venue && ! $event->roles()->where('type', 'venue')->exists()) {
                $event->roles()->attach($venue->id, [
                    'is_accepted' => $role->user->isMember($venue->subdomain),
                ]);
                $venueAttached = true;
            }
        }

        // Attaching a venue is a real change but does not dirty the Event row.
        if (! $event->isDirty()) {
            return $venueAttached;
        }

        $event->save();

        return true;
    }

    /**
     * Create a Graph change-notification subscription for the role's calendar.
     */
    public function createSubscription(User $user, ?string $calendarId, string $notificationUrl): array
    {
        $secret = config('services.microsoft.webhook_secret');
        if (empty($secret)) {
            // clientState is the only authenticity check on inbound notifications.
            throw new \RuntimeException('MICROSOFT_WEBHOOK_SECRET is not configured; Graph subscriptions require a clientState.');
        }

        if (! $this->ensureValidToken($user)) {
            throw new \RuntimeException('Unable to obtain a valid Microsoft token for subscription creation.');
        }

        $resource = $calendarId ? "/me/calendars/{$calendarId}/events" : '/me/events';

        $response = $this->graph()->post('/subscriptions', [
            'changeType' => 'created,updated,deleted',
            'notificationUrl' => $notificationUrl,
            'resource' => $resource,
            'expirationDateTime' => now()->addHours(self::SUBSCRIPTION_HOURS)->toIso8601String(),
            'clientState' => $secret,
        ]);

        if (! $response->successful()) {
            Log::error('Failed to create Microsoft Graph subscription', [
                'status' => $response->status(),
                'body' => $response->body(),
                'notification_url' => $notificationUrl,
            ]);
            throw new \RuntimeException('Failed to create Microsoft Graph subscription: '.$response->status());
        }

        UsageTrackingService::track(UsageTrackingService::MSCAL_WEBHOOK);

        $json = $response->json();

        return [
            'id' => $json['id'] ?? null,
            'expiration' => $json['expirationDateTime'] ?? null,
        ];
    }

    /**
     * Delete a Graph subscription. A 404 (already gone) counts as success.
     */
    public function deleteSubscription(User $user, string $subscriptionId): bool
    {
        try {
            if (! $this->ensureValidToken($user)) {
                return false;
            }

            $response = $this->graph()->delete("/subscriptions/{$subscriptionId}");

            return $response->successful() || $response->status() === 404;
        } catch (\Throwable $e) {
            Log::error('Failed to delete Microsoft Graph subscription', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Renew a Graph subscription's expiration.
     *
     * Returns null ONLY on 404 (subscription gone - caller should recreate). A transient
     * failure (5xx/429/network) throws so the caller retries later instead of recreating a
     * still-live subscription and orphaning it.
     */
    public function renewSubscription(User $user, string $subscriptionId): ?array
    {
        if (! $this->ensureValidToken($user)) {
            throw new \RuntimeException('Unable to obtain a valid Microsoft token to renew subscription.');
        }

        $response = $this->graph()->patch("/subscriptions/{$subscriptionId}", [
            'expirationDateTime' => now()->addHours(self::SUBSCRIPTION_HOURS)->toIso8601String(),
        ]);

        if ($response->status() === 404) {
            return null;
        }

        if (! $response->successful()) {
            Log::error('Failed to renew Microsoft Graph subscription', [
                'subscription_id' => $subscriptionId,
                'status' => $response->status(),
            ]);
            throw new \RuntimeException('Failed to renew Microsoft Graph subscription: '.$response->status());
        }

        $json = $response->json();

        return [
            'id' => $json['id'] ?? $subscriptionId,
            'expiration' => $json['expirationDateTime'] ?? null,
        ];
    }
}
