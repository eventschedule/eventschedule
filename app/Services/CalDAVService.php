<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Repos\EventRepo;
use App\Utils\SlugPatternUtils;
use Carbon\Carbon;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

class CalDAVService
{
    protected $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    /**
     * Test connection to CalDAV server
     */
    public function testConnection(array $settings): array
    {
        try {
            $this->validateSettings($settings);

            $response = $this->makeRequest(
                'PROPFIND',
                $settings['server_url'],
                $settings,
                '<?xml version="1.0" encoding="utf-8"?>
                <d:propfind xmlns:d="DAV:">
                    <d:prop>
                        <d:current-user-principal/>
                    </d:prop>
                </d:propfind>',
                ['Depth' => '0']
            );

            if ($response->successful() || $response->status() === 207) {
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to connect: '.$response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('CalDAV connection test failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Discover calendars available on the server
     */
    public function discoverCalendars(array $settings): array
    {
        try {
            $this->validateSettings($settings);

            // First, find the principal URL
            $principalUrl = $this->findPrincipalUrl($settings);
            if (! $principalUrl) {
                // Try the server URL directly as calendar home
                $principalUrl = $settings['server_url'];
            }

            // Find the calendar home set
            $calendarHomeUrl = $this->findCalendarHomeSet($settings, $principalUrl);
            if (! $calendarHomeUrl) {
                $calendarHomeUrl = $principalUrl;
            }

            // List calendars in the calendar home
            $calendars = $this->listCalendars($settings, $calendarHomeUrl);

            return [
                'success' => true,
                'calendars' => $calendars,
            ];
        } catch (\Exception $e) {
            Log::error('CalDAV calendar discovery failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'calendars' => [],
            ];
        }
    }

    /**
     * Create an event on CalDAV server
     */
    public function createEvent(Event $event, Role $role): ?string
    {
        try {
            $settings = $role->getCalDAVSettings();
            if (empty($settings['calendar_url'])) {
                throw new \Exception('No calendar URL configured');
            }

            $uid = $this->generateUid();
            $vcalendar = $this->buildVEvent($event, $role, $uid);

            if (! $vcalendar) {
                Log::warning('CalDAV createEvent skipped: event cannot be converted to VEvent', [
                    'event_id' => $event->id,
                    'role_id' => $role->id,
                ]);

                return null;
            }

            $icsContent = $vcalendar->serialize();

            $eventUrl = rtrim($settings['calendar_url'], '/').'/'.$uid.'.ics';

            $response = $this->makeRequest(
                'PUT',
                $eventUrl,
                $settings,
                $icsContent,
                ['Content-Type' => 'text/calendar; charset=utf-8']
            );

            if ($response->successful() || $response->status() === 201) {
                Log::info('Event created in CalDAV', [
                    'event_id' => $event->id,
                    'uid' => $uid,
                ]);

                UsageTrackingService::track(UsageTrackingService::CALDAV_SYNC, $role->id);

                return $uid;
            }

            Log::error('Failed to create CalDAV event', [
                'event_id' => $event->id,
                'status' => $response->status(),
                'body' => $this->sanitizeResponseBody($response->body()),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('CalDAV createEvent failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Update an event on CalDAV server
     */
    public function updateEvent(Event $event, string $uid, Role $role): bool
    {
        try {
            $settings = $role->getCalDAVSettings();
            if (empty($settings['calendar_url'])) {
                throw new \Exception('No calendar URL configured');
            }

            $vcalendar = $this->buildVEvent($event, $role, $uid);

            if (! $vcalendar) {
                Log::warning('CalDAV updateEvent skipped: event cannot be converted to VEvent', [
                    'event_id' => $event->id,
                    'uid' => $uid,
                    'role_id' => $role->id,
                ]);

                return false;
            }

            $icsContent = $vcalendar->serialize();

            $eventUrl = rtrim($settings['calendar_url'], '/').'/'.$uid.'.ics';

            $response = $this->makeRequest(
                'PUT',
                $eventUrl,
                $settings,
                $icsContent,
                ['Content-Type' => 'text/calendar; charset=utf-8']
            );

            if ($response->successful() || $response->status() === 204) {
                Log::info('Event updated in CalDAV', [
                    'event_id' => $event->id,
                    'uid' => $uid,
                ]);

                UsageTrackingService::track(UsageTrackingService::CALDAV_SYNC, $role->id);

                return true;
            }

            Log::error('Failed to update CalDAV event', [
                'event_id' => $event->id,
                'uid' => $uid,
                'status' => $response->status(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('CalDAV updateEvent failed', [
                'event_id' => $event->id,
                'uid' => $uid,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete an event from CalDAV server
     */
    public function deleteEvent(string $uid, Role $role): bool
    {
        try {
            $settings = $role->getCalDAVSettings();
            if (empty($settings['calendar_url'])) {
                throw new \Exception('No calendar URL configured');
            }

            $eventUrl = rtrim($settings['calendar_url'], '/').'/'.$uid.'.ics';

            $response = $this->makeRequest(
                'DELETE',
                $eventUrl,
                $settings
            );

            if ($response->successful() || $response->status() === 204 || $response->status() === 404) {
                Log::info('Event deleted from CalDAV', [
                    'uid' => $uid,
                ]);

                return true;
            }

            Log::error('Failed to delete CalDAV event', [
                'uid' => $uid,
                'status' => $response->status(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('CalDAV deleteEvent failed', [
                'uid' => $uid,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Sync all events for a role to CalDAV
     */
    public function syncToCalDAV(Role $role): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        if (! $role->syncsToCalDAV()) {
            return $results;
        }

        // Use chunking to avoid memory issues with large event sets
        Event::whereHas('roles', function ($query) use ($role) {
            $query->where('roles.id', $role->id);
        })->with(['roles' => function ($query) use ($role) {
            $query->where('roles.id', $role->id);
        }])->chunk(100, function ($events) use ($role, &$results) {
            foreach ($events as $event) {
                try {
                    $uid = $event->getCalDAVEventUidForRole($role->id);

                    if ($uid) {
                        // Event already exists in CalDAV, skip (updates are handled separately)
                        continue;
                    }

                    // Create new event
                    $newUid = $this->createEvent($event, $role);
                    if ($newUid) {
                        $event->setCalDAVEventUidForRole($role->id, $newUid);
                        $results['created']++;
                    } else {
                        $results['errors']++;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to sync event to CalDAV', [
                        'event_id' => $event->id,
                        'error' => $e->getMessage(),
                    ]);
                    $results['errors']++;
                }
            }
        });

        return $results;
    }

    /**
     * Sync events from CalDAV to local database
     */
    public function syncFromCalDAV(Role $role): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        if (! $role->syncsFromCalDAV()) {
            return $results;
        }

        try {
            $settings = $role->getCalDAVSettings();
            if (empty($settings['calendar_url'])) {
                return $results;
            }

            // Get events from CalDAV
            $events = $this->getEventsFromCalDAV($settings);

            // Batch fetch existing UIDs to avoid N+1 queries
            $eventUids = collect($events)
                ->pluck('uid')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $existingUids = [];
            if (! empty($eventUids)) {
                $existingUids = DB::table('event_role')
                    ->where('role_id', $role->id)
                    ->whereIn('caldav_event_uid', $eventUids)
                    ->pluck('caldav_event_uid')
                    ->toArray();
            }
            $existingUidsSet = array_flip($existingUids);

            foreach ($events as $eventData) {
                try {
                    // Skip events without UID - they cannot be reliably tracked for updates/deduplication
                    if (empty($eventData['uid'])) {
                        Log::warning('Skipping CalDAV event without UID', [
                            'summary' => $eventData['summary'] ?? 'unknown',
                            'role_id' => $role->id,
                        ]);

                        continue;
                    }

                    // Check if event already exists by UID using pre-fetched set (O(1) lookup)
                    if (isset($existingUidsSet[$eventData['uid']])) {
                        // Skip existing events - updates from CalDAV not implemented to avoid conflicts
                        continue;
                    }

                    // Secondary check: prevent importing if an event with same name and time already exists
                    // This catches edge cases like manually created duplicates
                    if (isset($eventData['start'])) {
                        // Normalize to UTC for consistent comparison regardless of source timezone
                        $eventStartUtc = $eventData['start']->copy()->setTimezone('UTC')->format('Y-m-d H:i:s');

                        $existingByNameTime = Event::where('name', $eventData['summary'] ?: __('messages.untitled_event'))
                            ->where('starts_at', $eventStartUtc)
                            ->whereHas('roles', function ($query) use ($role) {
                                $query->where('role_id', $role->id);
                            })
                            ->first();

                        if ($existingByNameTime) {
                            Log::info('CalDAV event matches existing event by name/time, skipping', [
                                'uid' => $eventData['uid'],
                                'existing_event_id' => $existingByNameTime->id,
                                'role_id' => $role->id,
                            ]);

                            continue;
                        }
                    }

                    // Create new event
                    $this->createEventFromCalDAV($eventData, $role);
                    $results['created']++;
                    UsageTrackingService::track(UsageTrackingService::CALDAV_SYNC, $role->id);

                    // Add to existing set to prevent duplicates within the same sync batch
                    $existingUidsSet[$eventData['uid']] = true;
                } catch (\Exception $e) {
                    Log::error('Failed to sync individual CalDAV event', [
                        'uid' => $eventData['uid'] ?? 'unknown',
                        'role_id' => $role->id,
                        'error' => $e->getMessage(),
                    ]);
                    $results['errors']++;
                }
            }

            // Update last sync timestamp
            $role->caldav_last_sync_at = now();
            $role->save();
        } catch (\Exception $e) {
            Log::error('CalDAV sync from calendar failed', [
                'role_id' => $role->id,
                'error' => $e->getMessage(),
            ]);
            $results['errors']++;
        }

        return $results;
    }

    /**
     * Check if calendar has changes (using ctag/sync-token)
     */
    public function hasChanges(Role $role): bool
    {
        try {
            $settings = $role->getCalDAVSettings();
            if (empty($settings['calendar_url'])) {
                return false;
            }

            $response = $this->makeRequest(
                'PROPFIND',
                $settings['calendar_url'],
                $settings,
                '<?xml version="1.0" encoding="utf-8"?>
                <d:propfind xmlns:d="DAV:" xmlns:cs="http://calendarserver.org/ns/">
                    <d:prop>
                        <cs:getctag/>
                        <d:sync-token/>
                    </d:prop>
                </d:propfind>',
                ['Depth' => '0']
            );

            if (! $response->successful() && $response->status() !== 207) {
                return true; // Assume changes if we can't check
            }

            $body = $response->body();
            $currentToken = null;

            // Try to extract ctag (use lazy match to handle encoded entities)
            if (preg_match('/<cs:getctag[^>]*>(.*?)<\/cs:getctag>/is', $body, $matches)) {
                $currentToken = html_entity_decode(trim($matches[1]));
            }
            // Or sync-token
            if (! $currentToken && preg_match('/<d:sync-token[^>]*>(.*?)<\/d:sync-token>/is', $body, $matches)) {
                $currentToken = html_entity_decode(trim($matches[1]));
            }

            if (! $currentToken) {
                return true; // Can't determine, assume changes
            }

            if ($role->caldav_sync_token !== $currentToken) {
                $role->caldav_sync_token = $currentToken;
                $role->save();

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('CalDAV hasChanges check failed', [
                'role_id' => $role->id,
                'error' => $e->getMessage(),
            ]);

            return true; // Assume changes on error
        }
    }

    /**
     * Build a VCalendar object from an Event
     *
     * @return VCalendar|null Returns null if the event cannot be converted (e.g., missing starts_at)
     */
    public function buildVEvent(Event $event, Role $role, string $uid): ?VCalendar
    {
        // Skip events without a start date - they cannot be synced to CalDAV
        if (! $event->starts_at) {
            Log::warning('Cannot build VEvent: event has no start date', [
                'event_id' => $event->id,
                'role_id' => $role->id,
            ]);

            return null;
        }

        $vcalendar = new VCalendar([
            'VEVENT' => [
                'UID' => $uid,
                'SUMMARY' => $event->name,
                'DESCRIPTION' => $event->description ?: '',
                'DTSTART' => $event->getStartDateTime(),
                'DTEND' => $event->getStartDateTime()->copy()->addHours($event->duration ?: 2),
                'DTSTAMP' => new \DateTime,
                'CREATED' => $event->created_at ? new \DateTime($event->created_at->toDateTimeString()) : new \DateTime,
                'LAST-MODIFIED' => $event->updated_at ? new \DateTime($event->updated_at->toDateTimeString()) : new \DateTime,
            ],
        ]);

        // Mark private events
        if ($event->is_private) {
            $vcalendar->VEVENT->add('CLASS', 'PRIVATE');
        }

        // Add location if venue exists
        if ($event->venue && $event->venue->bestAddress()) {
            $vcalendar->VEVENT->add('LOCATION', $event->venue->bestAddress());
        }

        // Add URL if available
        $guestUrl = $event->getGuestUrl();
        if ($guestUrl) {
            $vcalendar->VEVENT->add('URL', $guestUrl);
        }

        return $vcalendar;
    }

    /**
     * Parse a VEvent into an array of event data
     */
    public function parseVEvent($vevent): array
    {
        $data = [
            'uid' => isset($vevent->UID) ? (string) $vevent->UID : null,
            'summary' => isset($vevent->SUMMARY) ? (string) $vevent->SUMMARY : null,
            'description' => isset($vevent->DESCRIPTION) ? (string) $vevent->DESCRIPTION : null,
            'location' => isset($vevent->LOCATION) ? (string) $vevent->LOCATION : null,
            'url' => isset($vevent->URL) ? (string) $vevent->URL : null,
            'start' => null,
            'end' => null,
            'duration' => 2, // Default
            'etag' => null, // Will be populated from CalDAV response
        ];

        // Parse start time
        if (isset($vevent->DTSTART)) {
            $data['start'] = Carbon::instance($vevent->DTSTART->getDateTime())->utc();
        }

        // Parse end time and calculate duration
        if (isset($vevent->DTEND)) {
            $data['end'] = Carbon::instance($vevent->DTEND->getDateTime())->utc();
            if ($data['start']) {
                // Use diffInMinutes/60 for decimal hours (diffInHours returns integers)
                $data['duration'] = $data['start']->diffInMinutes($data['end']) / 60;
            }
        } elseif (isset($vevent->DURATION)) {
            $duration = $vevent->DURATION->getDateInterval();
            // Include all time components for precise duration calculation
            $data['duration'] = ($duration->h + ($duration->d * 24) + ($duration->i / 60) + ($duration->s / 3600));
            if ($data['start']) {
                $data['end'] = $data['start']->copy()->add($duration);
            }
        }

        return $data;
    }

    /**
     * Get events from CalDAV server
     */
    protected function getEventsFromCalDAV(array $settings): array
    {
        $events = [];

        // Use REPORT with calendar-query to get events
        $timeMin = now()->subDays(30)->format('Ymd\THis\Z');
        $timeMax = now()->addDays(365)->format('Ymd\THis\Z');

        $response = $this->makeRequest(
            'REPORT',
            $settings['calendar_url'],
            $settings,
            '<?xml version="1.0" encoding="utf-8"?>
            <c:calendar-query xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
                <d:prop>
                    <d:getetag/>
                    <c:calendar-data/>
                </d:prop>
                <c:filter>
                    <c:comp-filter name="VCALENDAR">
                        <c:comp-filter name="VEVENT">
                            <c:time-range start="'.$timeMin.'" end="'.$timeMax.'"/>
                        </c:comp-filter>
                    </c:comp-filter>
                </c:filter>
            </c:calendar-query>',
            ['Depth' => '1', 'Content-Type' => 'application/xml']
        );

        if (! $response->successful() && $response->status() !== 207) {
            Log::error('CalDAV REPORT request failed', [
                'status' => $response->status(),
                'body' => $this->sanitizeResponseBody($response->body()),
            ]);

            return $events;
        }

        // Parse the response
        $body = $response->body();

        // Extract individual response elements (each contains getetag and calendar-data)
        preg_match_all('/<d:response>(.*?)<\/d:response>/s', $body, $responseMatches);

        foreach ($responseMatches[1] as $responseXml) {
            try {
                // Extract etag from this response element (handle quoted and encoded values)
                $etag = null;
                if (preg_match('/<d:getetag[^>]*>(.*?)<\/d:getetag>/s', $responseXml, $etagMatch)) {
                    $etag = html_entity_decode(trim($etagMatch[1]));
                    $etag = trim($etag, '"'); // Remove surrounding quotes if present
                }

                // Extract calendar-data from this response element
                if (! preg_match('/<c:calendar-data[^>]*>(.*?)<\/c:calendar-data>/s', $responseXml, $calMatch)) {
                    continue;
                }

                $icsData = html_entity_decode($calMatch[1]);
                $vcalendar = Reader::read($icsData);

                if (isset($vcalendar->VEVENT)) {
                    foreach ($vcalendar->VEVENT as $vevent) {
                        $eventData = $this->parseVEvent($vevent);
                        $eventData['etag'] = $etag;
                        $events[] = $eventData;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to parse CalDAV event', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $events;
    }

    /**
     * Create a local event from CalDAV data
     */
    protected function createEventFromCalDAV(array $eventData, Role $role): Event
    {
        return DB::transaction(function () use ($eventData, $role) {
            $event = new Event;
            $event->user_id = $role->user_id;
            $event->creator_role_id = $role->id;
            $event->name = $eventData['summary'] ?: __('messages.untitled_event');

            // Sanitize description from CalDAV to prevent XSS
            $description = $eventData['description'] ?: '';
            if ($description) {
                $config = HTMLPurifier_Config::createDefault();
                $purifier = new HTMLPurifier($config);
                $description = $purifier->purify($description);
            }
            $event->description = $description;

            // Set start time
            if ($eventData['start']) {
                $event->starts_at = $eventData['start']->format('Y-m-d H:i:s');
            }

            // Set duration
            $event->duration = $eventData['duration'] ?: 2;

            // Generate slug AFTER starts_at is set (for date variables)
            $event->slug = SlugPatternUtils::generateSlug(
                $role->slug_pattern,
                $event->name,
                null,
                $event,
                $role
            );

            $event->save();

            // Attach to role with CalDAV UID and etag for conflict detection
            $event->roles()->attach($role->id, [
                'is_accepted' => true,
                'caldav_event_uid' => $eventData['uid'],
                'caldav_event_etag' => $eventData['etag'] ?? null,
            ]);

            // Handle location if present
            if ($eventData['location']) {
                $venue = $this->convertLocationToVenue($role, $eventData['location']);
                if ($venue && ! $event->roles()->where('type', 'venue')->exists()) {
                    $event->roles()->attach($venue->id, [
                        'is_accepted' => $role->user?->isMember($venue->subdomain) ?? false,
                    ]);
                }
            }

            return $event;
        });
    }

    /**
     * Convert a location string to a venue
     */
    protected function convertLocationToVenue(Role $role, string $location): ?Role
    {
        // Guard: cannot create venue without a user
        if (! $role->user_id) {
            Log::warning('Cannot create venue: role has no user', ['role_id' => $role->id]);

            return null;
        }

        $location = trim($location);

        if (! $location) {
            return null;
        }

        // Truncate location if it exceeds database column limit
        if (strlen($location) > 255) {
            Log::warning('CalDAV location truncated', [
                'role_id' => $role->id,
                'original_length' => strlen($location),
            ]);
            $location = substr($location, 0, 255);
        }

        return DB::transaction(function () use ($role, $location) {
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

            // Create new venue with unique subdomain
            // generateSubdomain already handles uniqueness, but we retry if race condition occurs
            $subdomain = Role::generateSubdomain($location);
            $attempts = 0;
            while (Role::where('subdomain', $subdomain)->exists() && $attempts < 10) {
                $subdomain = Role::generateSubdomain($location.'-'.++$attempts);
            }

            $venue = new Role;
            $venue->type = 'venue';
            $venue->user_id = $role->user_id;
            $venue->subdomain = $subdomain;
            $venue->address1 = $location;
            $venue->country_code = $role->country_code;
            $venue->save();

            $venue->members()->attach($role->user_id, ['level' => 'follower', 'created_at' => now()]);

            return $venue;
        });
    }

    /**
     * Find the principal URL for the user
     */
    protected function findPrincipalUrl(array $settings): ?string
    {
        $response = $this->makeRequest(
            'PROPFIND',
            $settings['server_url'],
            $settings,
            '<?xml version="1.0" encoding="utf-8"?>
            <d:propfind xmlns:d="DAV:">
                <d:prop>
                    <d:current-user-principal/>
                </d:prop>
            </d:propfind>',
            ['Depth' => '0']
        );

        if (! $response->successful() && $response->status() !== 207) {
            return null;
        }

        $body = $response->body();

        if (preg_match('/<d:current-user-principal[^>]*>.*?<d:href[^>]*>(.*?)<\/d:href>/s', $body, $matches)) {
            return $this->resolveUrl($settings['server_url'], html_entity_decode(trim($matches[1])));
        }

        return null;
    }

    /**
     * Find the calendar home set URL
     */
    protected function findCalendarHomeSet(array $settings, string $principalUrl): ?string
    {
        $response = $this->makeRequest(
            'PROPFIND',
            $principalUrl,
            $settings,
            '<?xml version="1.0" encoding="utf-8"?>
            <d:propfind xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
                <d:prop>
                    <c:calendar-home-set/>
                </d:prop>
            </d:propfind>',
            ['Depth' => '0']
        );

        if (! $response->successful() && $response->status() !== 207) {
            return null;
        }

        $body = $response->body();

        if (preg_match('/<c:calendar-home-set[^>]*>.*?<d:href[^>]*>(.*?)<\/d:href>/s', $body, $matches)) {
            return $this->resolveUrl($settings['server_url'], html_entity_decode(trim($matches[1])));
        }

        return null;
    }

    /**
     * List calendars in the calendar home
     */
    protected function listCalendars(array $settings, string $calendarHomeUrl): array
    {
        $calendars = [];

        $response = $this->makeRequest(
            'PROPFIND',
            $calendarHomeUrl,
            $settings,
            '<?xml version="1.0" encoding="utf-8"?>
            <d:propfind xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav" xmlns:cs="http://calendarserver.org/ns/">
                <d:prop>
                    <d:displayname/>
                    <d:resourcetype/>
                    <cs:getctag/>
                </d:prop>
            </d:propfind>',
            ['Depth' => '1']
        );

        if (! $response->successful() && $response->status() !== 207) {
            return $calendars;
        }

        $body = $response->body();

        // Parse multi-status response
        preg_match_all('/<d:response>(.*?)<\/d:response>/s', $body, $responses);

        foreach ($responses[1] as $responseXml) {
            // Check if it's a calendar
            if (strpos($responseXml, '<c:calendar') === false && strpos($responseXml, 'calendar') === false) {
                continue;
            }

            // Skip the calendar home itself
            if (strpos($responseXml, '<d:resourcetype/>') !== false || strpos($responseXml, '<d:resourcetype></d:resourcetype>') !== false) {
                continue;
            }

            // Check for calendar resourcetype
            if (strpos($responseXml, '<c:calendar/>') === false && strpos($responseXml, '<c:calendar />') === false) {
                // May still be a calendar, check for displayname
                if (strpos($responseXml, '<d:displayname>') === false) {
                    continue;
                }
            }

            $href = '';
            $displayName = '';

            if (preg_match('/<d:href[^>]*>(.*?)<\/d:href>/s', $responseXml, $matches)) {
                $href = $this->resolveUrl($settings['server_url'], html_entity_decode(trim($matches[1])));
            }

            if (preg_match('/<d:displayname[^>]*>(.*?)<\/d:displayname>/s', $responseXml, $matches)) {
                $displayName = html_entity_decode(trim($matches[1]));
            }

            if ($href) {
                $calendars[] = [
                    'url' => $href,
                    'name' => $displayName ?: basename($href),
                ];
            }
        }

        return $calendars;
    }

    /**
     * Make a CalDAV HTTP request
     */
    protected function makeRequest(string $method, string $url, array $settings, ?string $body = null, array $headers = [])
    {
        $defaultHeaders = [
            'Content-Type' => 'application/xml; charset=utf-8',
        ];

        $allHeaders = array_merge($defaultHeaders, $headers);

        // Use longer timeout for REPORT requests as they may fetch large date ranges
        $timeout = strtoupper($method) === 'REPORT' ? 90 : 30;

        $request = Http::withBasicAuth($settings['username'], $settings['password'])
            ->withHeaders($allHeaders)
            ->timeout($timeout);

        switch (strtoupper($method)) {
            case 'PROPFIND':
            case 'REPORT':
                return $request->send($method, $url, ['body' => $body]);
            case 'PUT':
                return $request->withBody($body, $allHeaders['Content-Type'])->put($url);
            case 'DELETE':
                return $request->delete($url);
            default:
                return $request->get($url);
        }
    }

    /**
     * Validate CalDAV settings
     */
    protected function validateSettings(array $settings): void
    {
        if (empty($settings['server_url'])) {
            throw new \InvalidArgumentException('Server URL is required');
        }

        if (empty($settings['username'])) {
            throw new \InvalidArgumentException('Username is required');
        }

        if (empty($settings['password'])) {
            throw new \InvalidArgumentException('Password is required');
        }

        // Enforce HTTPS
        if (! str_starts_with(strtolower($settings['server_url']), 'https://')) {
            throw new \InvalidArgumentException('CalDAV server must use HTTPS');
        }
    }

    /**
     * Resolve a potentially relative URL against a base URL
     */
    protected function resolveUrl(string $baseUrl, string $relativeUrl): string
    {
        if (str_starts_with($relativeUrl, 'http://') || str_starts_with($relativeUrl, 'https://')) {
            return $relativeUrl;
        }

        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        $port = isset($parsed['port']) ? ':'.$parsed['port'] : '';

        if (str_starts_with($relativeUrl, '/')) {
            return $scheme.'://'.$host.$port.$relativeUrl;
        }

        $basePath = $parsed['path'] ?? '/';
        $basePath = rtrim(dirname($basePath), '/');

        return $scheme.'://'.$host.$port.$basePath.'/'.$relativeUrl;
    }

    /**
     * Generate a unique UID for CalDAV events
     */
    protected function generateUid(): string
    {
        return Str::uuid()->toString().'@eventschedule';
    }

    /**
     * Sanitize response body for logging to prevent credential leakage
     */
    protected function sanitizeResponseBody(string $body): string
    {
        // Truncate long responses
        $sanitized = Str::limit($body, 500);

        // Remove any potential credential patterns (Base64 auth, password fields, etc.)
        $sanitized = preg_replace('/Basic\s+[A-Za-z0-9+\/=]+/i', 'Basic [REDACTED]', $sanitized);
        $sanitized = preg_replace('/password["\s:=>]+[^"<>\s,}]+/i', 'password: [REDACTED]', $sanitized);
        $sanitized = preg_replace('/Authorization["\s:=>]+[^"<>\s,}]+/i', 'Authorization: [REDACTED]', $sanitized);

        return $sanitized;
    }
}
