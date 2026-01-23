<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Repos\EventRepo;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event as GoogleEvent;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
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
        return $this->client->createAuthUrl();
    }

    /**
     * Get the authorization URL for Google OAuth with forced re-authorization
     */
    public function getAuthUrlWithForce(): string
    {
        $this->client->setApprovalPrompt('force');
        $this->client->setPrompt('consent');

        return $this->client->createAuthUrl();
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
            $minutesUntilExpiry = $expiresAt->diffInMinutes(now());

            if ($minutesUntilExpiry > 1) {
                // Token is still valid for more than 1 minute, no need to refresh
                $this->setAccessToken([
                    'access_token' => $user->google_token,
                    'refresh_token' => $user->google_refresh_token,
                    'expires_in' => $expiresAt->diffInSeconds(now()),
                ]);

                return true;
            }
        }

        Log::info('Refreshing Google Calendar token', [
            'user_id' => $user->id,
            'expires_at' => $expiresAt,
            'minutes_until_expiry' => $expiresAt ? $expiresAt->diffInMinutes(now()) : 'unknown',
        ]);

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
    public function createEvent(Event $event, Role $role): ?GoogleEvent
    {
        try {
            if (! $this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            // Use the role's selected calendar or default to primary
            $calendarId = $role->getGoogleCalendarId();

            $user = $role->user;

            if (! $user->google_token) {
                throw new \Exception('User does not have Google Calendar connected');
            }

            if (! $calendarId) {
                $calendarId = 'primary';
            }

            $googleEvent = new GoogleEvent;
            $googleEvent->setSummary($event->name);
            $googleEvent->setDescription($event->description);

            // Set start and end times
            $startDateTime = new EventDateTime;
            $startDateTime->setDateTime($event->getStartDateTime()->toRfc3339String());
            $startDateTime->setTimeZone($role->timezone ?? 'UTC');
            $googleEvent->setStart($startDateTime);

            $endDateTime = new EventDateTime;
            $endTime = $event->getStartDateTime()->copy()->addHours($event->duration ?: 2);
            $endDateTime->setDateTime($endTime->toRfc3339String());
            $endDateTime->setTimeZone($role->timezone ?? 'UTC');
            $googleEvent->setEnd($endDateTime);

            // Set location
            if ($event->venue && $event->venue->bestAddress()) {
                $googleEvent->setLocation($event->venue->bestAddress());
            }

            // Set visibility
            $googleEvent->setVisibility('public');

            // Create the event
            $createdEvent = $this->calendarService->events->insert($calendarId, $googleEvent);

            return $createdEvent;

        } catch (\Exception $e) {
            Log::error('Failed to create Google Calendar event', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Update a Google Calendar event
     */
    public function updateEvent(Event $event, string $googleEventId, Role $role): ?GoogleEvent
    {
        try {
            if (! $this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            $calendarId = $role->getGoogleCalendarId();

            $user = $role->user;

            if (! $user->google_token) {
                throw new \Exception('User does not have Google Calendar connected');
            }

            // Use the role's selected calendar or default to primary
            if (! $calendarId) {
                $calendarId = 'primary';
            }

            $googleEvent = $this->calendarService->events->get($calendarId, $googleEventId);

            $googleEvent->setSummary($event->name);

            if (! empty($event->description)) {
                $googleEvent->setDescription($event->description);
            }

            // Set start and end times
            $startDateTime = new EventDateTime;
            $startDateTime->setDateTime($event->getStartDateTime()->toRfc3339String());
            $startDateTime->setTimeZone($role->timezone ?? 'UTC');
            $googleEvent->setStart($startDateTime);

            $endDateTime = new EventDateTime;
            $endTime = $event->getStartDateTime()->copy()->addHours($event->duration ?: 2);
            $endDateTime->setDateTime($endTime->toRfc3339String());
            $endDateTime->setTimeZone($role->timezone ?? 'UTC');
            $googleEvent->setEnd($endDateTime);

            // Set location
            if ($event->venue && $event->venue->bestAddress()) {
                $googleEvent->setLocation($event->venue->bestAddress());
            }

            // Update the event
            $updatedEvent = $this->calendarService->events->update($calendarId, $googleEventId, $googleEvent);

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
    public function deleteEvent(string $googleEventId, string $calendarId = 'primary'): bool
    {
        try {
            if (! $this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            $this->calendarService->events->delete($calendarId, $googleEventId);

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
     */
    public function syncUserEvents(User $user, Role $role): array
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

        // Get all events for the specific role
        $events = Event::whereHas('roles', function ($query) use ($role) {
            $query->where('roles.id', $role->id);
        })->get();

        foreach ($events as $event) {
            try {
                $googleEventId = $event->getGoogleEventIdForRole($role->id);

                if ($googleEventId) {
                    // Skip events that already exist in Google Calendar
                    // Updates should only happen when the specific event is changed in the app
                    // (handled by SyncEventToGoogleCalendar job with 'update' action)
                    continue;
                } else {
                    // Create new event
                    $googleEvent = $this->createEvent($event, $role);
                    if ($googleEvent) {
                        $event->setGoogleEventIdForRole($role->id, $googleEvent->getId());
                        $results['created']++;
                    } else {
                        $results['errors']++;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to sync event to Google Calendar', [
                    'event_id' => $event->id,
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
     * Get events from Google Calendar for a specific time range
     */
    public function getEvents(string $calendarId, ?\DateTime $timeMin = null, ?\DateTime $timeMax = null): array
    {
        try {
            if (! $this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            $optParams = [
                'orderBy' => 'startTime',
                'singleEvents' => true,
            ];

            if ($timeMin) {
                $optParams['timeMin'] = $timeMin->format('c');
            }

            if ($timeMax) {
                $optParams['timeMax'] = $timeMax->format('c');
            }

            $events = $this->calendarService->events->listEvents($calendarId, $optParams);

            $result = [];
            foreach ($events->getItems() as $event) {
                $result[] = [
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
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to get Google Calendar events', [
                'calendar_id' => $calendarId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
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
     * Sync events from Google Calendar to EventSchedule
     */
    public function syncFromGoogleCalendar(User $user, Role $role, string $calendarId): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        try {
            if (! $this->refreshTokenIfNeeded($user)) {
                $results['errors']++;

                return $results;
            }

            // Get events from the last 30 days to the next 365 days
            $timeMin = now()->subDays(30);
            $timeMax = now()->addDays(365);

            $googleEvents = $this->getEvents($calendarId, $timeMin, $timeMax);

            foreach ($googleEvents as $googleEvent) {
                try {
                    // Check if this event already exists by looking for the google_event_id in event_role table
                    $existingEvent = Event::whereHas('roles', function ($query) use ($googleEvent, $role) {
                        $query->where('role_id', $role->id)
                            ->where('google_event_id', $googleEvent['id']);
                    })->first();

                    // Also check for events with the same name and start time to prevent duplicates
                    if (! $existingEvent && isset($googleEvent['start'])) {
                        $startTime = null;
                        if ($googleEvent['start']->getDateTime()) {
                            $startTime = \Carbon\Carbon::parse($googleEvent['start']->getDateTime())->utc();
                        } elseif ($googleEvent['start']->getDate()) {
                            $startTime = \Carbon\Carbon::parse($googleEvent['start']->getDate())->utc();
                        }

                        if ($startTime) {
                            $existingEvent = Event::where('name', $googleEvent['summary'] ?: __('messages.untitled_event'))
                                ->where('starts_at', $startTime->format('Y-m-d H:i:s'))
                                ->whereHas('roles', function ($query) use ($role) {
                                    $query->where('role_id', $role->id);
                                })
                                ->first();
                        }
                    }

                    if ($existingEvent) {
                        // Skip updating existing events
                        continue;
                    } else {
                        // Create new event
                        $this->createEventFromGoogle($googleEvent, $role, $calendarId);
                        $results['created']++;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to sync individual Google Calendar event', [
                        'google_event_id' => $googleEvent['id'],
                        'role_id' => $role->id,
                        'error' => $e->getMessage(),
                    ]);
                    $results['errors']++;
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to sync from Google Calendar', [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'calendar_id' => $calendarId,
                'error' => $e->getMessage(),
            ]);
            $results['errors']++;
        }

        return $results;
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
        $event->description = $googleEvent['description'] ?: '';
        $event->slug = \Str::slug($event->name);

        if (! $event->slug) {
            $event->slug = strtolower(\Str::random(5));
        }

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
        } else {
            $event->duration = 2; // Default 2 hours
        }

        $event->save();

        // Attach to the role with google_event_id
        $event->roles()->attach($role->id, [
            'is_accepted' => true,
            'google_event_id' => $googleEvent['id'],
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
    private function updateEventFromGoogle(Event $event, array $googleEvent, Role $role): void
    {
        $event->name = $googleEvent['summary'] ?: __('messages.untitled_event');

        if (! empty($googleEvent['description'])) {
            $event->description = $googleEvent['description'];
        }

        // Update start time
        if ($googleEvent['start']->getDateTime()) {
            $event->starts_at = \Carbon\Carbon::parse($googleEvent['start']->getDateTime())->utc();
        } elseif ($googleEvent['start']->getDate()) {
            $event->starts_at = \Carbon\Carbon::parse($googleEvent['start']->getDate())->utc();
        }

        // Update duration
        if ($googleEvent['end']->getDateTime() && $googleEvent['start']->getDateTime()) {
            $start = \Carbon\Carbon::parse($googleEvent['start']->getDateTime());
            $end = \Carbon\Carbon::parse($googleEvent['end']->getDateTime());
            $event->duration = $start->diffInHours($end);
        }

        if ($googleEvent['location']) {
            $venue = $this->convertLocationToVenue($role, $googleEvent['location']);
            if ($venue && ! $event->roles()->where('type', 'venue')->exists()) {
                $event->roles()->attach($venue->id, [
                    'is_accepted' => $role->user->isMember($venue->subdomain),
                ]);
            }
        }

        $event->save();
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

    private function convertLocationToVenue($role, $location)
    {
        $location = trim($location);

        if (! $location) {
            return null;
        }

        // Get IDs of venues where the role's user is a follower
        $followedVenueIds = Role::where('type', 'venue')
            ->whereHas('members', function ($query) use ($role) {
                $query->where('user_id', $role->user_id)
                    ->where('level', 'follower');
            })
            ->where('is_deleted', false)
            ->pluck('id')
            ->toArray();

        $venue = Role::where('type', 'venue')
            ->where('address1', $location)
            ->whereIn('id', $followedVenueIds)
            ->where('is_deleted', false)
            ->first();

        if ($venue) {
            return $venue;
        }

        $venue = new Role;
        $venue->type = 'venue';
        $venue->address1 = $location;
        $venue->country_code = $role->country_code;
        $venue->save();

        $venue->members()->attach($role->user_id, ['level' => 'follower', 'created_at' => now()]);

        return $venue;
    }
}
