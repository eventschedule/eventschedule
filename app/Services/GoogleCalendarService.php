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
use Google\Auth\Cache\MemoryCacheItemPool;
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

    /** What the standing sync needs: read the calendars, and write events to one of them. */
    public const FULL_SCOPES = [
        Calendar::CALENDAR_EVENTS,
        Calendar::CALENDAR_READONLY,
        'openid',
        'email',
        'profile',
    ];

    /**
     * What bringing events in needs: read, and nothing else. The permission screen then says
     * "View events on all your calendars" to someone who only wants to copy theirs, instead of
     * "View and edit".
     */
    public const READ_SCOPES = [
        Calendar::CALENDAR_READONLY,
        'openid',
        'email',
        'profile',
    ];

    public function __construct(EventRepo $eventRepo)
    {
        $this->client = new Client;
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->setScopes(self::FULL_SCOPES);
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');
        $this->client->setPrompt('consent'); // This is the newer way to force consent
        $this->client->setIncludeGrantedScopes(true);

        $this->eventRepo = $eventRepo;
    }

    /**
     * Get the authorization URL for Google OAuth
     */
    public function getAuthUrl(bool $readOnly = false, bool $chooseAccount = false, ?string $account = null): string
    {
        // include_granted_scopes is on, so asking for less never takes away what an account
        // already granted: a schedule that sends events to Google keeps doing so.
        $this->client->setScopes($readOnly ? self::READ_SCOPES : self::FULL_SCOPES);
        // Set both ways every time: the client keeps what it was last told, and one instance
        // can outlive a request.
        $this->client->setPrompt($chooseAccount ? 'select_account consent' : 'consent');
        // With more than one account signed in, Google asks which. Naming the one already
        // connected keeps a second permission from landing on another account, which would
        // replace the token every synced schedule of this person runs on. Empty leaves it out.
        $this->client->setLoginHint($chooseAccount ? '' : (string) $account);
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
     * Set access token for API calls.
     *
     * One service is walked across every syncing owner by google:sync and
     * google:refresh-webhooks, so what is set here has to stay that one owner's:
     *
     *  - `created` is when the token's `expires_in` was counted from. Google's client reads a
     *    token without it as expired already, and then renews it by itself on every request.
     *  - Such a renewal goes through the client's cache, which is keyed by client id and scopes
     *    and not by person. Left shared, the first owner's renewed token was served to every
     *    owner after them whose own stored token was still good (one whose token had run out
     *    is renewed by refreshTokenIfNeeded() and was read as themselves). A token that is
     *    good now can still run out part way through a long sync, so each token gets a cache
     *    of its own.
     *
     * tests/Feature/GoogleTokenUseTest.php fails with either half removed.
     */
    public function setAccessToken(array $token): void
    {
        $token['created'] ??= time();

        $this->client->setCache(new MemoryCacheItemPool);
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
                } elseif ($newToken['error'] === 'invalid_grant') {
                    // The grant is gone for good: the user removed access in their Google account
                    // (or it lapsed). Retrying every fifteen minutes would change nothing, and what
                    // we stored to sync with it has to go - the privacy policy and Google's Limited
                    // Use policy both say so.
                    Log::info('Google Calendar access was revoked; forgetting the stored authorization', [
                        'user_id' => $user->id,
                    ]);
                    $this->forgetAuthorization($user, false);
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
     * Forget everything stored to sync this user's Google Calendar: the tokens, each owned
     * schedule's webhook and sync cursor, the per-event sync records and the per-schedule calendar
     * choice.
     *
     * $tellGoogle on a manual disconnect, where the token still works: the webhooks are stopped and
     * the grant itself is revoked, so Google stops holding a token for us too. Off when a refresh
     * has just come back invalid_grant, which is how a user withdrawing access from their Google
     * account reaches us, and there is nothing left to tell Google with.
     *
     * google_id is the Google SIGN-IN link, not calendar data, so it is the caller's to clear.
     */
    public function forgetAuthorization(User $user, bool $tellGoogle): void
    {
        if ($tellGoogle && $user->google_token && $this->ensureValidToken($user)) {
            foreach ($user->owner()->whereNotNull('google_webhook_id')->get() as $role) {
                if ($role->google_webhook_resource_id) {
                    try {
                        $this->deleteWebhook($role->google_webhook_id, $role->google_webhook_resource_id);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to stop a Google Calendar webhook while disconnecting', [
                            'user_id' => $user->id,
                            'role_id' => $role->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $this->revoke($user);
        }

        // Query-builder updates: these are system-managed columns, and google_sync_token is not
        // fillable.
        Role::where('user_id', $user->id)->whereNotNull('google_webhook_id')->update([
            'google_webhook_id' => null,
            'google_webhook_resource_id' => null,
            'google_webhook_expires_at' => null,
        ]);
        Role::where('user_id', $user->id)->update(['sync_direction' => null, 'google_sync_token' => null]);

        \App\Models\CalendarSync::where('user_id', $user->id)->delete();

        \Illuminate\Support\Facades\DB::table('role_user')
            ->where('user_id', $user->id)
            ->whereNotNull('google_calendar_id')
            ->update(['google_calendar_id' => null]);

        $forgotten = [
            'google_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
        ];
        // A deploy serves this code before its migration has run, and a revoked grant has to be
        // forgotten then too: a write that names a column not there yet fails as a whole.
        if (User::googleScopesColumnReady()) {
            $forgotten['google_token_scopes'] = null;
        }
        $user->forceFill($forgotten)->save();
    }

    /**
     * Ask Google to revoke this user's grant, so the token stops working at Google's end too and
     * not just ours. Best effort: an already revoked or expired token is the outcome we wanted.
     */
    public function revoke(User $user): void
    {
        $token = $user->google_refresh_token ?: $user->google_token;

        if (! $token) {
            return;
        }

        try {
            $this->client->revokeToken($token);
        } catch (\Throwable $e) {
            Log::warning('Failed to revoke the Google grant', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
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
     * The calendars an import can read, as Google lists them. Unlike getCalendars() this throws:
     * "could not load your calendars" and "you have no calendars" are different things to say.
     *
     * @return list<array{id: string, name: string, color: ?string, primary: bool, access: string}>
     */
    public function listImportCalendars(): array
    {
        if (! $this->calendarService) {
            throw new \RuntimeException('Calendar service not initialized');
        }

        $calendars = [];
        $pageToken = null;

        do {
            $list = $this->calendarService->calendarList->listCalendarList(array_filter([
                'minAccessRole' => 'reader',
                'pageToken' => $pageToken,
            ]));

            foreach ($list->getItems() as $calendar) {
                $calendars[] = [
                    'id' => (string) $calendar->getId(),
                    'name' => (string) ($calendar->getSummaryOverride() ?: $calendar->getSummary()),
                    'color' => $calendar->getBackgroundColor(),
                    'primary' => (bool) $calendar->getPrimary(),
                    'access' => (string) $calendar->getAccessRole(),
                ];
            }

            $pageToken = $list->getNextPageToken();
        } while ($pageToken);

        return $calendars;
    }

    /** As many entries as Google will put on one page of a calendar's events. */
    private const IMPORT_PAGE_SIZE = 2500;

    /** Pages read for one import. Google may send a page short, or empty, with more to follow. */
    private const IMPORT_MAX_PAGES = 6;

    /**
     * A calendar's entries between two moments, as plain arrays (GoogleImportUtils reads them).
     * Repeating entries come as their rule, not as one row per date: the import turns a rule
     * into one repeating event.
     *
     * Entries arrive in no order (Google sorts only when a rule is expanded into its dates), so
     * a calendar that is cut short is cut anywhere: `truncated` says so, and the page says it
     * above the list (a read that is cut short and has no events in it is answered as a
     * failure, by the controller).
     *
     * @return array{events: list<array>, timezone: ?string, name: ?string, truncated: bool}
     */
    public function listUpcomingEvents(string $calendarId, \DateTimeInterface $from, \DateTimeInterface $to, int $limit = self::IMPORT_PAGE_SIZE): array
    {
        if (! $this->calendarService) {
            throw new \RuntimeException('Calendar service not initialized');
        }

        $events = [];
        $timezone = null;
        $name = null;
        $pageToken = null;
        $pages = 0;

        do {
            $options = [
                'timeMin' => $from->format(\DateTimeInterface::RFC3339),
                'timeMax' => $to->format(\DateTimeInterface::RFC3339),
                'singleEvents' => false,
                'showDeleted' => false,
                'maxResults' => max(1, min($limit, self::IMPORT_PAGE_SIZE)),
                // Past one guest Google sends only the calendar's own reply to an invitation,
                // which is all that is read here (importEntry()), and not the guest list.
                'maxAttendees' => 1,
            ];
            if ($pageToken) {
                $options['pageToken'] = $pageToken;
            }

            $page = $this->calendarService->events->listEvents($calendarId, $options);
            $timezone = $page->getTimeZone() ?: $timezone;
            $name = $page->getSummary() ?: $name;

            foreach ($page->getItems() as $event) {
                $events[] = self::importEntry($event);
            }

            $pageToken = $page->getNextPageToken();
            $pages++;
        } while ($pageToken && count($events) < $limit && $pages < self::IMPORT_MAX_PAGES);

        return ['events' => $events, 'timezone' => $timezone, 'name' => $name, 'truncated' => (bool) $pageToken];
    }

    /**
     * One of Google's entries as the plain array the import reads. Public so a test can hand it
     * the library's own model, built from an answer in Google's shape.
     */
    public static function importEntry(GoogleEvent $event): array
    {
        // An invitation the calendar's owner said no to is still on the calendar. `self` marks
        // the reply that is theirs.
        $declined = false;
        foreach ($event->getAttendees() ?: [] as $attendee) {
            if ($attendee->getSelf() && $attendee->getResponseStatus() === 'declined') {
                $declined = true;
            }
        }

        return [
            'id' => $event->getId(),
            'status' => $event->getStatus(),
            'summary' => $event->getSummary(),
            'description' => $event->getDescription(),
            'location' => $event->getLocation(),
            'visibility' => $event->getVisibility(),
            'eventType' => $event->getEventType(),
            'start' => self::importMoment($event->getStart()),
            'end' => self::importMoment($event->getEnd()),
            'recurrence' => $event->getRecurrence() ?: [],
            'recurringEventId' => $event->getRecurringEventId(),
            'originalStartTime' => self::importMoment($event->getOriginalStartTime()),
            'declined' => $declined,
        ];
    }

    /** @return array{date: ?string, dateTime: ?string, timeZone: ?string}|null */
    private static function importMoment($moment): ?array
    {
        if (! $moment) {
            return null;
        }

        return ['date' => $moment->getDate(), 'dateTime' => $moment->getDateTime(), 'timeZone' => $moment->getTimeZone()];
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
                    // NOTE: do NOT set orderBy here - Google omits nextSyncToken from a list response
                    // that uses orderBy, which would leave the initial sync unable to establish an
                    // incremental token. Without the token the sync never enters the showDeleted
                    // branch, so cancelled tombstones (inbound delete-sync) would never be fetched.
                    $optParams['timeMin'] = $timeMin;
                    $optParams['timeMax'] = $timeMax;
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

            $outcome = $event->applyInboundDeletion($role->calendarDeleteAction(), $role);

            if ($outcome === 'deleted') {
                $results['deleted']++;
                AuditService::log(AuditService::EVENT_DELETE, $role->user_id, 'Event', $eventId, null, null, $eventName);
            } elseif (in_array($outcome, ['cancelled', 'guarded_cancelled', 'detached'], true)) {
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
     * Persist Google's incremental sync cursor. A targeted write, not save(): this runs once per
     * synced schedule on every poll and every webhook, and a save() runs the schedule's whole
     * saving hook and moves updated_at for the sake of one column. Mirrors how
     * microsoft_sync_token is stored.
     */
    protected function storeGoogleSyncToken(Role $role, string $token): void
    {
        $role->writeOperationalColumns(['google_sync_token' => $token]);
    }

    protected function clearGoogleSyncToken(Role $role): void
    {
        $role->writeOperationalColumns(['google_sync_token' => null]);
    }

    /**
     * Create an EventSchedule event from Google Calendar event
     */
    private function createEventFromGoogle(array $googleEvent, Role $role, string $calendarId): Event
    {
        $event = new Event;
        $event->user_id = $role->user_id;
        $event->creator_role_id = $role->id;
        if (Event::importColumnsReady()) {
            $event->import_source = Event::IMPORT_GOOGLE;
        }
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

        $this->attachLocationVenue($event, $role, $googleEvent['location']);

        return $event;
    }

    /**
     * Update an EventSchedule event from Google Calendar event
     */
    private function updateEventFromGoogle(Event $event, array $googleEvent, Role $role): bool
    {
        // Appointment bookings are owned by this app, never by the calendar. Our own outbound
        // dispatchCalendarSync('update') can come straight back in here, and this method would then
        // rewrite name, description, starts_at and duration from the remote copy - so a schedule with
        // calendar_description_template set would get the rendered template written back over the
        // guest's notes, and a rescheduled booking could be dragged back to its old time.
        if ($event->appointment_type_id) {
            return false;
        }

        // An event a feed made is owned by its feed. This method would rewrite its name,
        // description, whole-hour duration and venue from the calendar's copy of what we pushed,
        // and the feed tells a field the owner edited from one it still writes by comparing with
        // what it last wrote: after one echo every field would read as edited, and the event
        // would stop following its source for good. Event::isFromFeed().
        if ($event->isFromFeed()) {
            return false;
        }

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

        $venueAttached = $this->attachLocationVenue($event, $role, $googleEvent['location']);

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
