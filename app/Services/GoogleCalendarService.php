<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Repos\EventRepo;
use App\Services\Concerns\ConvertsLocationToVenue;
use App\Utils\EventTextGenerator;
use App\Utils\MarkdownUtils;
use App\Utils\SlugPatternUtils;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event as GoogleEvent;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    use ConvertsLocationToVenue;

    // Safety cap on incremental-sync pagination so a huge calendar can't spin unbounded during the
    // synchronous, save-triggered sync (mirrors MicrosoftCalendarService::MAX_DELTA_PAGES).
    const MAX_SYNC_PAGES = 50;

    protected $client;

    protected $calendarService;

    protected $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->client = new Client;
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->setScopes([
            Calendar::CALENDAR_EVENTS,
            Calendar::CALENDAR_READONLY,
            'openid',
            'email',
            'profile',
        ]);
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');
        $this->client->setPrompt('consent'); // This is the newer way to force consent
        $this->client->setIncludeGrantedScopes(true);

        $this->eventRepo = $eventRepo;
    }

    /**
     * Get the authorization URL for Google OAuth
     */
    public function getAuthUrl(): string
    {
        $this->client->setState($this->generateAndStoreState());

        return $this->client->createAuthUrl();
    }

    /**
     * Get the authorization URL for Google OAuth with forced re-authorization
     */
    public function getAuthUrlWithForce(): string
    {
        $this->client->setApprovalPrompt('force');
        $this->client->setPrompt('consent');
        $this->client->setState($this->generateAndStoreState());

        return $this->client->createAuthUrl();
    }

    /**
     * Generate a CSRF state token for the OAuth flow and store it in the session.
     */
    private function generateAndStoreState(): string
    {
        $state = bin2hex(random_bytes(32));
        session(['google_oauth_state' => $state]);

        return $state;
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken(string $code): array
    {
        return $this->client->fetchAccessTokenWithAuthCode($code);
    }

    /**
     * Set access token for API calls
     */
    public function setAccessToken(array $token): void
    {
        $this->client->setAccessToken($token);
        $this->calendarService = new Calendar($this->client);
    }

    /**
     * Refresh access token if needed
     */
    public function refreshTokenIfNeeded(User $user): bool
    {
        if (! $user->google_token || ! $user->google_refresh_token) {
            Log::warning('User missing Google tokens', [
                'user_id' => $user->id,
                'has_access_token' => ! is_null($user->google_token),
                'has_refresh_token' => ! is_null($user->google_refresh_token),
            ]);

            return false;
        }

        // Handle google_token_expires_at as string or Carbon instance
        $expiresAt = $user->google_token_expires_at;

        if ($expiresAt) {
            if (is_string($expiresAt)) {
                $expiresAt = \Carbon\Carbon::parse($expiresAt);
            }

            // Only refresh if token expires in the next 1 minute (reduced from 5 minutes)
            $minutesUntilExpiry = now()->diffInMinutes($expiresAt);

            if ($expiresAt->isFuture() && $minutesUntilExpiry > 1) {
                // Token is still valid for more than 1 minute, no need to refresh
                $this->setAccessToken([
                    'access_token' => $user->google_token,
                    'refresh_token' => $user->google_refresh_token,
                    'expires_in' => now()->diffInSeconds($expiresAt),
                ]);

                return true;
            }
        }

        $refreshToken = $user->google_refresh_token;
        if ($refreshToken) {
            try {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

                if (! isset($newToken['error'])) {
                    $user->update([
                        'google_token' => $newToken['access_token'],
                        'google_token_expires_at' => now()->addSeconds($newToken['expires_in']),
                    ]);

                    $this->setAccessToken($newToken);

                    return true;
                } else {
                    Log::error('Failed to refresh Google Calendar token', [
                        'user_id' => $user->id,
                        'error' => $newToken['error'],
                        'error_description' => $newToken['error_description'] ?? 'Unknown error',
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Exception during Google Calendar token refresh', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('No refresh token available for user', [
                'user_id' => $user->id,
            ]);
        }

        return false;
    }

    /**
     * Ensure user has valid Google Calendar access with automatic token refresh
     */
    public function ensureValidToken(User $user): bool
    {
        return $this->refreshTokenIfNeeded($user);
    }

    /**
     * Create a Google Calendar event from an Event model
     */
    public function createEvent(Event $event, Role $role, ?string $calendarId = null): ?GoogleEvent
    {
        try {
            if (! $this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            // Use provided calendar ID, or fall back to role's selected calendar
            if (! $calendarId) {
                $calendarId = $role->getGoogleCalendarId();
            }

            if (! $calendarId) {
                $calendarId = 'primary';
            }

            $googleEvent = new GoogleEvent;
            $googleEvent->setSummary($event->name);

            if ($role->calendar_description_template) {
                $event->loadMissing(['venue', 'tickets']);
                $googleEvent->setDescription(EventTextGenerator::parseTemplate($role->calendar_description_template, $event, $role, false, ['url_include_https' => false]));
            } else {
                $googleEvent->setDescription($event->description);
            }

            // Set start and end times
            $startDateTime = new EventDateTime;
            $startDateTime->setDateTime($event->getStartDateTime()->toRfc3339String());
            $startDateTime->setTimeZone($role->timezone ?? 'UTC');
            $googleEvent->setStart($startDateTime);

            $endDateTime = new EventDateTime;
            $endTime = $event->getStartDateTime()->copy()->addMinutes(Event::durationHoursToMinutes($event->duration ?? 2));
            $endDateTime->setDateTime($endTime->toRfc3339String());
            $endDateTime->setTimeZone($role->timezone ?? 'UTC');
            $googleEvent->setEnd($endDateTime);

            // Set location
            if ($event->venue && $event->venue->bestAddress()) {
                $googleEvent->setLocation($event->venue->bestAddress());
            }

            // Set visibility
            $googleEvent->setVisibility($event->is_private ? 'private' : 'public');

            // Create the event
            $createdEvent = $this->calendarService->events->insert($calendarId, $googleEvent);

            UsageTrackingService::track(UsageTrackingService::GCAL_CREATE, $role->id);

            return $createdEvent;

        } catch (\Throwable $e) {
            Log::error('Failed to create Google Calendar event', [
                'event_id' => $event->id,
                'role_id' => $role->id,
                'calendar_id' => $calendarId ?? null,
                'exception_class' => get_class($e),
                'http_code' => $e->getCode(),
                'google_errors' => $e instanceof \Google\Service\Exception ? $e->getErrors() : null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Update a Google Calendar event
     */
    public function updateEvent(Event $event, string $googleEventId, Role $role, ?string $calendarId = null): ?GoogleEvent
    {
        try {
            if (! $this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            // Use provided calendar ID, or fall back to role's selected calendar
            if (! $calendarId) {
                $calendarId = $role->getGoogleCalendarId();
            }

            if (! $calendarId) {
                $calendarId = 'primary';
            }

            $googleEvent = $this->calendarService->events->get($calendarId, $googleEventId);

            $googleEvent->setSummary($event->name);

            if ($role->calendar_description_template) {
                $event->loadMissing(['venue', 'tickets']);
                $googleEvent->setDescription(EventTextGenerator::parseTemplate($role->calendar_description_template, $event, $role, false, ['url_include_https' => false]));
            } elseif (! empty($event->description)) {
                $googleEvent->setDescription($event->description);
            }

            // Set start and end times
            $startDateTime = new EventDateTime;
            $startDateTime->setDateTime($event->getStartDateTime()->toRfc3339String());
            $startDateTime->setTimeZone($role->timezone ?? 'UTC');
            $googleEvent->setStart($startDateTime);

            $endDateTime = new EventDateTime;
            $endTime = $event->getStartDateTime()->copy()->addMinutes(Event::durationHoursToMinutes($event->duration ?? 2));
            $endDateTime->setDateTime($endTime->toRfc3339String());
            $endDateTime->setTimeZone($role->timezone ?? 'UTC');
            $googleEvent->setEnd($endDateTime);

            // Set location
            if ($event->venue && $event->venue->bestAddress()) {
                $googleEvent->setLocation($event->venue->bestAddress());
            }

            // Set visibility
            $googleEvent->setVisibility($event->is_private ? 'private' : 'public');

            // Update the event
            $updatedEvent = $this->calendarService->events->update($calendarId, $googleEventId, $googleEvent);

            UsageTrackingService::track(UsageTrackingService::GCAL_UPDATE, $role->id);

            return $updatedEvent;

        } catch (\Exception $e) {
            Log::error('Failed to update Google Calendar event', [
                'event_id' => $event->id,
                'google_event_id' => $googleEventId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Delete a Google Calendar event
     */
    public function deleteEvent(string $googleEventId, string $calendarId = 'primary', int $roleId = 0): bool
    {
        try {
            if (! $this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            $this->calendarService->events->delete($calendarId, $googleEventId);

            UsageTrackingService::track(UsageTrackingService::GCAL_DELETE, $roleId);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete Google Calendar event', [
                'google_event_id' => $googleEventId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get user's calendars
     */
    public function getCalendars(): array
    {
        try {
            if (! $this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            $calendarList = $this->calendarService->calendarList->listCalendarList();
            $calendars = [];

            foreach ($calendarList->getItems() as $calendar) {
                $calendars[] = [
                    'id' => $calendar->getId(),
                    'summary' => $calendar->getSummary(),
                    'primary' => $calendar->getPrimary(),
                ];
            }

            return $calendars;

        } catch (\Exception $e) {
            Log::error('Failed to get Google Calendars', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Sync all events for a user to Google Calendar for a specific role
     *
     * @param  bool  $force  When true, removes existing calendar_sync rows for this user and role so every event is created again on Google (push-only).
     */
    public function syncUserEvents(User $user, Role $role, bool $force = false): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'orphan_delete_errors' => 0,
        ];

        if (! $this->refreshTokenIfNeeded($user)) {
            $results['errors']++;

            return $results;
        }

        if ($force) {
            // Delete existing copies on the previously-selected calendar(s) so a calendar
            // switch leaves no orphans. Skip rows where the calendar wasn't recorded
            // (legacy NULLs) — without that id we'd no-op against the current pivot.
            $existingSyncs = \App\Models\CalendarSync::where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->whereNotNull('google_event_id')
                ->whereNotNull('google_calendar_id')
                ->get(['google_event_id', 'google_calendar_id']);

            foreach ($existingSyncs as $row) {
                try {
                    $this->deleteEvent($row->google_event_id, $row->google_calendar_id, $role->id);
                } catch (\Throwable $e) {
                    $results['orphan_delete_errors']++;
                    Log::warning('Failed to delete orphan from previous calendar', [
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                        'google_calendar_id' => $row->google_calendar_id,
                        'google_event_id' => $row->google_event_id,
                        'exception_class' => get_class($e),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            \App\Models\CalendarSync::where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->delete();
        }

        // Get all non-draft events for the specific role
        $events = Event::whereHas('roles', function ($query) use ($role) {
            $query->where('roles.id', $role->id);
        })->where('is_draft', false)->get();

        foreach ($events as $event) {
            try {
                $existingSync = \App\Models\CalendarSync::where('user_id', $user->id)
                    ->where('event_id', $event->id)
                    ->where('role_id', $role->id)
                    ->first();

                if ($existingSync?->google_event_id) {
                    // Skip events that already exist in Google Calendar
                    continue;
                } else {
                    // Create new event
                    $calendarId = $role->getGoogleCalendarId();
                    $googleEvent = $this->createEvent($event, $role, $calendarId);
                    if ($googleEvent) {
                        \App\Models\CalendarSync::updateOrCreate(
                            ['user_id' => $user->id, 'event_id' => $event->id, 'role_id' => $role->id],
                            ['google_event_id' => $googleEvent->getId(), 'google_calendar_id' => $calendarId]
                        );
                        $results['created']++;
                    } else {
                        $results['errors']++;
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Failed to sync event to Google Calendar', [
                    'event_id' => $event->id,
                    'exception_class' => get_class($e),
                    'error' => $e->getMessage(),
                ]);
                $results['errors']++;
            }
        }

        return $results;
    }

    /**
     * Sync all events for a user to Google Calendar across all their roles
     */
    public function syncAllUserEvents(User $user): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        if (! $this->refreshTokenIfNeeded($user)) {
            $results['errors']++;

            return $results;
        }

        // Get all roles for this user
        $roles = $user->roles;

        if ($roles->isEmpty()) {
            return $results;
        }

        // Sync events for each role that has sync enabled
        foreach ($roles as $role) {
            // Only sync roles that have sync enabled (sync_direction is 'to' or 'both')
            if ($role->syncsToGoogle()) {
                $roleResults = $this->syncUserEvents($user, $role);
                $results['created'] += $roleResults['created'];
                $results['updated'] += $roleResults['updated'];
                $results['errors'] += $roleResults['errors'];
            }
        }

        return $results;
    }

    /**
     * Map a Google Calendar event object into the associative array the inbound sync works with.
     * Includes 'status' ('confirmed' | 'tentative' | 'cancelled') so incremental syncs can detect
     * deletions, which arrive as 'cancelled' tombstones.
     */
    protected function mapGoogleEvent(GoogleEvent $event): array
    {
        return [
            'id' => $event->getId(),
            'status' => $event->getStatus(),
            'summary' => $event->getSummary(),
            'description' => $event->getDescription(),
            'start' => $event->getStart(),
            'end' => $event->getEnd(),
            'location' => $event->getLocation(),
            'htmlLink' => $event->getHtmlLink(),
            'created' => $event->getCreated(),
            'updated' => $event->getUpdated(),
        ];
    }

    /**
     * Get a specific event from Google Calendar
     */
    public function getEvent(string $calendarId, string $eventId): ?array
    {
        try {
            if (! $this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            $event = $this->calendarService->events->get($calendarId, $eventId);

            return [
                'id' => $event->getId(),
                'summary' => $event->getSummary(),
                'description' => $event->getDescription(),
                'start' => $event->getStart(),
                'end' => $event->getEnd(),
                'location' => $event->getLocation(),
                'htmlLink' => $event->getHtmlLink(),
                'created' => $event->getCreated(),
                'updated' => $event->getUpdated(),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get Google Calendar event', [
                'calendar_id' => $calendarId,
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sync events from Google Calendar to EventSchedule.
     *
     * Uses Google's incremental sync tokens: the stored nextSyncToken means each run fetches only
     * changes since the last one - including 'cancelled' tombstones for deleted events, which drive
     * inbound delete-sync. With no token yet, a bounded initial full sync establishes the first
     * token. Mirrors MicrosoftCalendarService::syncFromMicrosoftCalendar().
     */
    public function syncFromGoogleCalendar(User $user, Role $role, string $calendarId): array
    {
        $results = ['created' => 0, 'updated' => 0, 'deleted' => 0, 'errors' => 0];

        // Serialize inbound sync per role: the webhook and the 15-min poll (or two overlapping runs)
        // could otherwise import the same event twice and race on the sync-token advance.
        $lock = Cache::lock('google-role-sync-'.$role->id, 120);
        if (! $lock->get()) {
            return $results;
        }

        try {
            if (! $this->refreshTokenIfNeeded($user)) {
                $results['errors']++;

                return $results;
            }

            $syncToken = $role->google_sync_token ?: null;

            // Compute the initial-sync window ONCE - it must stay identical across paginated
            // requests (Google rejects a page request whose params differ from the first).
            $timeMin = now()->subDays(30)->format('c');
            $timeMax = now()->addDays(365)->format('c');

            $pageToken = null;
            $restarted = false;
            $pages = 0;
            $nextSyncToken = null;

            while (true) {
                if (++$pages > self::MAX_SYNC_PAGES) {
                    Log::warning('Google sync pagination cap hit', ['role_id' => $role->id]);
                    break;
                }

                // maxResults=2500 (API max) minimizes pages so a large calendar can still finish the
                // initial sync and establish an incremental token within MAX_SYNC_PAGES.
                $optParams = ['singleEvents' => true, 'maxResults' => 2500];
                if ($syncToken) {
                    // Incremental: showDeleted returns 'cancelled' tombstones. timeMin/timeMax/orderBy
                    // are not allowed alongside syncToken.
                    $optParams['syncToken'] = $syncToken;
                    $optParams['showDeleted'] = true;
                } else {
                    $optParams['timeMin'] = $timeMin;
                    $optParams['timeMax'] = $timeMax;
                    $optParams['orderBy'] = 'startTime';
                }
                if ($pageToken) {
                    $optParams['pageToken'] = $pageToken;
                }

                try {
                    $events = $this->calendarService->events->listEvents($calendarId, $optParams);
                } catch (\Google\Service\Exception $e) {
                    // A stored sync token can be rejected as expired (410 Gone) or invalid - e.g.
                    // after the selected calendar changed (400/410). Clear it and restart a full
                    // sync once; other errors (auth, rate limit, 5xx) abort and retry later.
                    if ($syncToken && ! $restarted && in_array($e->getCode(), [400, 410], true)) {
                        $restarted = true;
                        $this->clearGoogleSyncToken($role);
                        $syncToken = null;
                        $pageToken = null;
                        $pages = 0;

                        continue;
                    }

                    Log::error('Failed to list Google Calendar events', [
                        'role_id' => $role->id,
                        'calendar_id' => $calendarId,
                        'code' => $e->getCode(),
                        'error' => $e->getMessage(),
                    ]);
                    $results['errors']++;

                    // Abort WITHOUT persisting a token so the next run retries from the same point.
                    return $results;
                }

                foreach ($events->getItems() as $googleEvent) {
                    try {
                        $this->processInboundGoogleEvent($googleEvent, $role, $calendarId, $results);
                    } catch (\Throwable $e) {
                        Log::error('Failed to sync individual Google Calendar event', [
                            'google_event_id' => $googleEvent->getId(),
                            'role_id' => $role->id,
                            'error' => $e->getMessage(),
                        ]);
                        $results['errors']++;
                    }
                }

                $pageToken = $events->getNextPageToken();
                if (! $pageToken) {
                    // Google returns the next sync token only on the final page.
                    $nextSyncToken = $events->getNextSyncToken();
                    break;
                }
            }

            // Persist the fresh token only after a clean, fully-paginated run.
            if ($nextSyncToken) {
                $this->storeGoogleSyncToken($role, $nextSyncToken);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync from Google Calendar', [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'calendar_id' => $calendarId,
                'error' => $e->getMessage(),
            ]);
            $results['errors']++;
        } finally {
            $lock->release();
        }

        return $results;
    }

    /**
     * Reconcile one Google event from an inbound sync: create/update, or (for a cancelled tombstone)
     * apply the schedule's delete policy.
     */
    protected function processInboundGoogleEvent(GoogleEvent $googleEvent, Role $role, string $calendarId, array &$results): void
    {
        // Deleted events arrive as 'cancelled' tombstones on incremental (syncToken) responses.
        if ($googleEvent->getStatus() === 'cancelled') {
            $this->handleGoogleInboundDeletion($googleEvent->getId(), $role, $results);

            return;
        }

        $data = $this->mapGoogleEvent($googleEvent);

        // Match by google_event_id in calendar_syncs.
        $existingSync = \App\Models\CalendarSync::where('role_id', $role->id)
            ->where('google_event_id', $data['id'])
            ->first();
        $existingEvent = $existingSync ? Event::find($existingSync->event_id) : null;

        // Fallback: match by name + start time to prevent duplicates.
        if (! $existingEvent && isset($data['start'])) {
            $startTime = null;
            if ($data['start']->getDateTime()) {
                $startTime = \Carbon\Carbon::parse($data['start']->getDateTime())->utc();
            } elseif ($data['start']->getDate()) {
                $startTime = \Carbon\Carbon::parse($data['start']->getDate())->utc();
            }

            if ($startTime) {
                $existingEvent = Event::where('name', $data['summary'] ?: __('messages.untitled_event'))
                    ->where('starts_at', $startTime->format('Y-m-d H:i:s'))
                    ->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role->id);
                    })
                    ->first();
            }
        }

        if ($existingEvent) {
            $changed = $this->updateEventFromGoogle($existingEvent, $data, $role);

            // Backfill the sync mapping when the match came from the name+time fallback (no
            // CalendarSync row yet) so future syncs match reliably by google_event_id even if the
            // title or time later change.
            if (! $existingSync) {
                \App\Models\CalendarSync::firstOrCreate(
                    ['user_id' => $role->user_id, 'event_id' => $existingEvent->id, 'role_id' => $role->id],
                    ['google_event_id' => $data['id'], 'google_calendar_id' => $calendarId]
                );
            }

            if ($changed) {
                $results['updated']++;
                UsageTrackingService::track(UsageTrackingService::GCAL_SYNC, $role->id);
            }
        } else {
            $this->createEventFromGoogle($data, $role, $calendarId);
            $results['created']++;
            UsageTrackingService::track(UsageTrackingService::GCAL_SYNC, $role->id);
        }
    }

    /**
     * Apply a Google-side deletion (cancelled tombstone) to the locally-synced event, honoring the
     * schedule's calendar_delete_action, then drop the now-stale sync-mapping row.
     */
    protected function handleGoogleInboundDeletion(string $googleEventId, Role $role, array &$results): void
    {
        $sync = \App\Models\CalendarSync::where('role_id', $role->id)
            ->where('google_event_id', $googleEventId)
            ->first();

        if (! $sync) {
            return;
        }

        $event = Event::find($sync->event_id);

        if ($event) {
            $eventId = $event->id;
            $eventName = $event->name;

            $outcome = $event->applyInboundDeletion($role->calendarDeleteAction());

            if ($outcome === 'deleted') {
                $results['deleted']++;
                AuditService::log(AuditService::EVENT_DELETE, $role->user_id, 'Event', $eventId, null, null, $eventName);
            } elseif (in_array($outcome, ['cancelled', 'guarded_cancelled'], true)) {
                $results['deleted']++;
                AuditService::log(AuditService::EVENT_CANCEL, $role->user_id, 'Event', $eventId, null, null, $eventName);
            }
        }

        // Drop the stale mapping row - the remote event no longer exists. On a hard delete the FK
        // cascade already removed it, so this is a harmless no-op there.
        \App\Models\CalendarSync::where('role_id', $role->id)
            ->where('google_event_id', $googleEventId)
            ->delete();
    }

    /**
     * Persist Google's incremental sync cursor. Direct assignment + save() (not update()) because
     * google_sync_token is not mass-assignable; mirrors how microsoft_sync_token is stored.
     */
    protected function storeGoogleSyncToken(Role $role, string $token): void
    {
        $role->google_sync_token = $token;
        $role->save();
    }

    protected function clearGoogleSyncToken(Role $role): void
    {
        $role->google_sync_token = null;
        $role->save();
    }

    /**
     * Create an EventSchedule event from Google Calendar event
     */
    private function createEventFromGoogle(array $googleEvent, Role $role, string $calendarId): Event
    {
        $event = new Event;
        $event->user_id = $role->user_id;
        $event->creator_role_id = $role->id;
        $event->name = $googleEvent['summary'] ?: __('messages.untitled_event');
        $event->description = MarkdownUtils::convertHtmlToMarkdown($googleEvent['description'] ?? '');

        // Set start time
        if ($googleEvent['start']->getDateTime()) {
            $event->starts_at = \Carbon\Carbon::parse($googleEvent['start']->getDateTime())->utc();
        } elseif ($googleEvent['start']->getDate()) {
            $event->starts_at = \Carbon\Carbon::parse($googleEvent['start']->getDate())->utc();
        }

        // Set duration
        if ($googleEvent['end']->getDateTime() && $googleEvent['start']->getDateTime()) {
            $start = \Carbon\Carbon::parse($googleEvent['start']->getDateTime());
            $end = \Carbon\Carbon::parse($googleEvent['end']->getDateTime());
            $event->duration = $start->diffInHours($end);
        } elseif ($googleEvent['end']->getDate() && $googleEvent['start']->getDate()) {
            $start = \Carbon\Carbon::parse($googleEvent['start']->getDate());
            $end = \Carbon\Carbon::parse($googleEvent['end']->getDate());
            $days = $start->diffInDays($end);
            $event->duration = $days > 1 ? $days * 24 : 0;
        } else {
            $event->duration = 2; // Default 2 hours
        }

        // Generate slug AFTER starts_at is set (for date variables)
        $event->slug = SlugPatternUtils::generateSlug(
            $role->slug_pattern,
            $event->name,
            null,
            $event,
            $role
        );

        if ($defaultCategoryId = \App\Repos\EventRepo::resolveDefaultCategoryId($role)) {
            $event->category_id = $defaultCategoryId;
        }

        $event->save();

        // Attach to the role
        $event->roles()->attach($role->id, [
            'is_accepted' => true,
        ]);

        // Track the google_event_id in calendar_syncs
        \App\Models\CalendarSync::create([
            'user_id' => $role->user_id,
            'event_id' => $event->id,
            'role_id' => $role->id,
            'google_event_id' => $googleEvent['id'],
            'google_calendar_id' => $calendarId,
        ]);

        if ($googleEvent['location']) {
            $venue = $this->convertLocationToVenue($role, $googleEvent['location']);
            if ($venue && ! $event->roles()->where('type', 'venue')->exists()) {
                $event->roles()->attach($venue->id, [
                    'is_accepted' => $role->user->isMember($venue->subdomain),
                ]);
            }
        }

        return $event;
    }

    /**
     * Update an EventSchedule event from Google Calendar event
     */
    private function updateEventFromGoogle(Event $event, array $googleEvent, Role $role): bool
    {
        $event->name = $googleEvent['summary'] ?: __('messages.untitled_event');

        if (! empty($googleEvent['description'])) {
            $event->description = MarkdownUtils::convertHtmlToMarkdown($googleEvent['description']);
        }

        // Update start time. Format to a string (starts_at is not a date-cast attribute,
        // so an object value never equals the stored string and isDirty() would always be
        // true - defeating the no-op guard and inflating usage tracking on every sync).
        if ($googleEvent['start']->getDateTime()) {
            $event->starts_at = \Carbon\Carbon::parse($googleEvent['start']->getDateTime())->utc()->format('Y-m-d H:i:s');
        } elseif ($googleEvent['start']->getDate()) {
            $event->starts_at = \Carbon\Carbon::parse($googleEvent['start']->getDate())->utc()->format('Y-m-d H:i:s');
        }

        // Update duration
        if ($googleEvent['end']->getDateTime() && $googleEvent['start']->getDateTime()) {
            $start = \Carbon\Carbon::parse($googleEvent['start']->getDateTime());
            $end = \Carbon\Carbon::parse($googleEvent['end']->getDateTime());
            $event->duration = $start->diffInHours($end);
        } elseif ($googleEvent['end']->getDate() && $googleEvent['start']->getDate()) {
            $start = \Carbon\Carbon::parse($googleEvent['start']->getDate());
            $end = \Carbon\Carbon::parse($googleEvent['end']->getDate());
            $days = $start->diffInDays($end);
            $event->duration = $days > 1 ? $days * 24 : 0;
        }

        $venueAttached = false;
        if ($googleEvent['location']) {
            $venue = $this->convertLocationToVenue($role, $googleEvent['location']);
            if ($venue && ! $event->roles()->where('type', 'venue')->exists()) {
                $event->roles()->attach($venue->id, [
                    'is_accepted' => $role->user->isMember($venue->subdomain),
                ]);
                $venueAttached = true;
            }
        }

        // Attaching a venue is a real change but does not dirty the Event row. Save only
        // when the row itself changed, but report the venue attach so the caller still
        // counts it as an update and tracks usage.
        if (! $event->isDirty()) {
            return $venueAttached;
        }

        $event->save();

        return true;
    }

    /**
     * Create a webhook for calendar changes
     */
    public function createWebhook(string $calendarId, string $webhookUrl): array
    {
        try {
            if (! $this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            $webhook = new \Google\Service\Calendar\Channel;
            $webhook->setId($this->generateValidChannelId());
            $webhook->setType('web_hook');
            $webhook->setAddress($webhookUrl);
            $webhook->setToken(config('services.google.webhook_secret'));

            $result = $this->calendarService->events->watch($calendarId, $webhook);

            UsageTrackingService::track(UsageTrackingService::GCAL_WEBHOOK);

            return [
                'id' => $result->getId(),
                'resourceId' => $result->getResourceId(),
                'expiration' => $result->getExpiration(),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create Google Calendar webhook', [
                'calendar_id' => $calendarId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a webhook
     */
    public function deleteWebhook(string $webhookId, string $resourceId): bool
    {
        try {
            if (! $this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            $channel = new \Google\Service\Calendar\Channel;
            $channel->setId($webhookId);
            $channel->setResourceId($resourceId);

            $this->calendarService->channels->stop($channel);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete Google Calendar webhook', [
                'webhook_id' => $webhookId,
                'resource_id' => $resourceId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Generate a valid channel ID for Google Calendar webhooks
     * Channel ID must match pattern [A-Za-z0-9\\-_\\+/=]+
     */
    private function generateValidChannelId(): string
    {
        // Generate a unique ID using only allowed characters
        $prefix = 'webhook_';
        $timestamp = time();
        $random = bin2hex(random_bytes(8)); // 16 character hex string

        // Combine and ensure only valid characters
        $channelId = $prefix.$timestamp.'_'.$random;

        // Replace any potentially invalid characters (though our generation should be safe)
        $channelId = preg_replace('/[^A-Za-z0-9\\-_\\+\\/=]/', '', $channelId);

        return $channelId;
    }
}
