<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
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

    public function __construct()
    {
        $this->client = new Client();
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
        if (!$user->google_token || !$user->google_refresh_token) {
            Log::warning('User missing Google tokens', [
                'user_id' => $user->id,
                'has_access_token' => !is_null($user->google_token),
                'has_refresh_token' => !is_null($user->google_refresh_token),
            ]);
            return false;
        }

        // Handle google_token_expires_at as string or Carbon instance
        $expiresAt = $user->google_token_expires_at;
        $expiresInSeconds = 3600; // Default to 1 hour
        
        if ($expiresAt) {
            if (is_string($expiresAt)) {
                $expiresAt = \Carbon\Carbon::parse($expiresAt);
            }
            $expiresInSeconds = $expiresAt->diffInSeconds(now());
        }
        
        $token = [
            'access_token' => $user->google_token,
            'refresh_token' => $user->google_refresh_token,
            'expires_in' => $expiresInSeconds,
        ];

        $this->client->setAccessToken($token);

        // Check if token is expired or will expire in the next 5 minutes
        $isExpired = $this->client->isAccessTokenExpired();
        $willExpireSoon = false;
        
        if ($expiresAt) {
            $willExpireSoon = $expiresAt->diffInMinutes(now()) < 5;
        }
        
        if ($isExpired || $willExpireSoon) {
            Log::info('Refreshing Google Calendar token', [
                'user_id' => $user->id,
                'expires_at' => $expiresAt,
                'reason' => $isExpired ? 'expired' : 'expiring_soon',
            ]);

            $refreshToken = $user->google_refresh_token;
            if ($refreshToken) {
                try {
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                    
                    if (!isset($newToken['error'])) {
                        $user->update([
                            'google_token' => $newToken['access_token'],
                            'google_token_expires_at' => now()->addSeconds($newToken['expires_in']),
                        ]);
                        
                        $this->setAccessToken($newToken);
                        
                        Log::info('Google Calendar token refreshed successfully', [
                            'user_id' => $user->id,
                            'new_expires_at' => $user->fresh()->google_token_expires_at,
                        ]);
                        
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

        $this->setAccessToken($token);
        return true;
    }

    /**
     * Ensure user has valid Google Calendar access with automatic token refresh
     */
    public function ensureValidToken(User $user): bool
    {
        // First attempt to refresh if needed
        if ($this->refreshTokenIfNeeded($user)) {
            return true;
        }

        // If refresh failed, try one more time after a short delay
        Log::info('Retrying Google Calendar token refresh', [
            'user_id' => $user->id,
        ]);

        sleep(1); // Brief delay before retry
        return $this->refreshTokenIfNeeded($user);
    }

    /**
     * Create a Google Calendar event from an Event model
     */
    public function createEvent(Event $event, string $calendarId = null): ?GoogleEvent
    {
        try {
            if (!$this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            // Use the role's selected calendar or default to primary
            if (!$calendarId) {
                $calendarId = $event->creatorRole ? $event->creatorRole->getGoogleCalendarId() : 'primary';
            }

            $googleEvent = new GoogleEvent();
            $googleEvent->setSummary($event->name);
            
            // Set description
            $description = $event->description_html ? strip_tags($event->description_html) : $event->description;
            if ($event->venue) {
                $description .= "\n\nVenue: " . $event->venue->getDisplayName();
                if ($event->venue->bestAddress()) {
                    $description .= "\nAddress: " . $event->venue->bestAddress();
                }
            }
            if ($event->event_url) {
                $description .= "\n\nEvent URL: " . $event->event_url;
            }
            $googleEvent->setDescription($description);

            // Set start and end times
            $startDateTime = new EventDateTime();
            $startDateTime->setDateTime($event->getStartDateTime()->toRfc3339String());
            $startDateTime->setTimeZone($event->creatorRole->timezone ?? 'UTC');
            $googleEvent->setStart($startDateTime);

            $endDateTime = new EventDateTime();
            $endTime = $event->getStartDateTime()->copy()->addHours($event->duration ?: 2);
            $endDateTime->setDateTime($endTime->toRfc3339String());
            $endDateTime->setTimeZone($event->creatorRole->timezone ?? 'UTC');
            $googleEvent->setEnd($endDateTime);

            // Set location
            if ($event->venue && $event->venue->bestAddress()) {
                $googleEvent->setLocation($event->venue->bestAddress());
            }

            // Set visibility
            $googleEvent->setVisibility('public');

            // Create the event
            $createdEvent = $this->calendarService->events->insert($calendarId, $googleEvent);
            
            Log::info('Google Calendar event created', [
                'event_id' => $event->id,
                'google_event_id' => $createdEvent->getId(),
            ]);

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
    public function updateEvent(Event $event, string $googleEventId, string $calendarId = null): ?GoogleEvent
    {
        try {
            if (!$this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            // Use the role's selected calendar or default to primary
            if (!$calendarId) {
                $calendarId = $event->creatorRole ? $event->creatorRole->getGoogleCalendarId() : 'primary';
            }

            $googleEvent = $this->calendarService->events->get($calendarId, $googleEventId);
            
            $googleEvent->setSummary($event->name);
            
            // Set description
            $description = $event->description_html ? strip_tags($event->description_html) : $event->description;
            if ($event->venue) {
                $description .= "\n\nVenue: " . $event->venue->getDisplayName();
                if ($event->venue->bestAddress()) {
                    $description .= "\nAddress: " . $event->venue->bestAddress();
                }
            }
            if ($event->event_url) {
                $description .= "\n\nEvent URL: " . $event->event_url;
            }
            $googleEvent->setDescription($description);

            // Set start and end times
            $startDateTime = new EventDateTime();
            $startDateTime->setDateTime($event->getStartDateTime()->toRfc3339String());
            $startDateTime->setTimeZone($event->creatorRole->timezone ?? 'UTC');
            $googleEvent->setStart($startDateTime);

            $endDateTime = new EventDateTime();
            $endTime = $event->getStartDateTime()->copy()->addHours($event->duration ?: 2);
            $endDateTime->setDateTime($endTime->toRfc3339String());
            $endDateTime->setTimeZone($event->creatorRole->timezone ?? 'UTC');
            $googleEvent->setEnd($endDateTime);

            // Set location
            if ($event->venue && $event->venue->bestAddress()) {
                $googleEvent->setLocation($event->venue->bestAddress());
            }

            // Update the event
            $updatedEvent = $this->calendarService->events->update($calendarId, $googleEventId, $googleEvent);
            
            Log::info('Google Calendar event updated', [
                'event_id' => $event->id,
                'google_event_id' => $googleEventId,
            ]);

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
            if (!$this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            $this->calendarService->events->delete($calendarId, $googleEventId);
            
            Log::info('Google Calendar event deleted', [
                'google_event_id' => $googleEventId,
            ]);

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
            if (!$this->calendarService) {
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

        if (!$this->refreshTokenIfNeeded($user)) {
            $results['errors']++;
            return $results;
        }

        // Get all events for the specific role
        $events = Event::whereHas('roles', function ($query) use ($role) {
            $query->where('id', $role->id);
        })->get();

        foreach ($events as $event) {
            try {
                if ($event->google_event_id) {
                    // Update existing event
                    $this->updateEvent($event, $event->google_event_id);
                    $results['updated']++;
                } else {
                    // Create new event
                    $googleEvent = $this->createEvent($event);
                    if ($googleEvent) {
                        $event->update(['google_event_id' => $googleEvent->getId()]);
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
     * Get events from Google Calendar for a specific time range
     */
    public function getEvents(string $calendarId, \DateTime $timeMin = null, \DateTime $timeMax = null): array
    {
        try {
            if (!$this->calendarService) {
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
            if (!$this->calendarService) {
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
            if (!$this->refreshTokenIfNeeded($user)) {
                $results['errors']++;
                return $results;
            }

            // Get events from the last 30 days to the next 365 days
            $timeMin = now()->subDays(30);
            $timeMax = now()->addDays(365);
            
            $googleEvents = $this->getEvents($calendarId, $timeMin, $timeMax);

            foreach ($googleEvents as $googleEvent) {
                try {
                    // Check if this event already exists
                    $existingEvent = Event::where('google_event_id', $googleEvent['id'])
                                        ->whereHas('creatorRole', function($query) use ($calendarId) {
                                            $query->where('google_calendar_id', $calendarId);
                                        })
                                        ->first();

                    // Also check for events with the same name and start time to prevent duplicates
                    if (!$existingEvent && isset($googleEvent['start'])) {
                        $startTime = null;
                        if ($googleEvent['start']->getDateTime()) {
                            $startTime = \Carbon\Carbon::parse($googleEvent['start']->getDateTime())->utc();
                        } elseif ($googleEvent['start']->getDate()) {
                            $startTime = \Carbon\Carbon::parse($googleEvent['start']->getDate())->utc();
                        }

                        if ($startTime) {
                            $existingEvent = Event::where('name', $googleEvent['summary'] ?: 'Untitled Event')
                                                ->where('starts_at', $startTime->format('Y-m-d H:i:s'))
                                                ->whereHas('creatorRole', function($query) use ($role) {
                                                    $query->where('id', $role->id);
                                                })
                                                ->first();
                        }
                    }

                    if ($existingEvent) {
                        // Update existing event
                        $this->updateEventFromGoogle($existingEvent, $googleEvent, $role);
                        $results['updated']++;
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
        $event = new Event();
        $event->user_id = $role->user_id;
        $event->creator_role_id = $role->id;
        $event->google_event_id = $googleEvent['id'];
        $event->name = $googleEvent['summary'] ?: 'Untitled Event';
        $event->description = $googleEvent['description'] ?: '';
        $event->slug = \Str::slug($event->name);

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

        // Attach to the role
        $event->roles()->attach($role->id, [
            'is_accepted' => true,
        ]);

        Log::info('Event created from Google Calendar', [
            'event_id' => $event->id,
            'google_event_id' => $googleEvent['id'],
            'role_id' => $role->id,
        ]);

        return $event;
    }

    /**
     * Update an EventSchedule event from Google Calendar event
     */
    private function updateEventFromGoogle(Event $event, array $googleEvent, Role $role): void
    {
        $event->name = $googleEvent['summary'] ?: 'Untitled Event';
        $event->description = $googleEvent['description'] ?: '';

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

        $event->save();

        Log::info('Event updated from Google Calendar', [
            'event_id' => $event->id,
            'google_event_id' => $googleEvent['id'],
        ]);
    }

    /**
     * Create a webhook for calendar changes
     */
    public function createWebhook(string $calendarId, string $webhookUrl): array
    {
        try {
            if (!$this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            $webhook = new \Google\Service\Calendar\Channel();
            $webhook->setId(uniqid('webhook_', true));
            $webhook->setType('web_hook');
            $webhook->setAddress($webhookUrl);
            $webhook->setToken(env('GOOGLE_WEBHOOK_SECRET', 'default_secret'));

            $result = $this->calendarService->events->watch($calendarId, $webhook);
            
            Log::info('Google Calendar webhook created', [
                'calendar_id' => $calendarId,
                'webhook_id' => $result->getId(),
                'resource_id' => $result->getResourceId(),
            ]);

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
    public function deleteWebhook(string $webhookId): bool
    {
        try {
            if (!$this->calendarService) {
                throw new \Exception('Calendar service not initialized');
            }

            $channel = new \Google\Service\Calendar\Channel();
            $channel->setId($webhookId);
            
            $this->calendarService->channels->stop($channel);
            
            Log::info('Google Calendar webhook deleted', [
                'webhook_id' => $webhookId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete Google Calendar webhook', [
                'webhook_id' => $webhookId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
