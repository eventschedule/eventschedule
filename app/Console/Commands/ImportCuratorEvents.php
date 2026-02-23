<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\GeminiUtils;
use App\Utils\ImageUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportCuratorEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-curator-events {--role_id= : Import events for a specific curator role by ID} {--debug : Enable debug mode with verbose logging} {--test : Test mode - only process first event from each URL} {--urls=* : URLs to import from (for testing)} {--cities=* : Cities to import from (for testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically import events for curator roles based on their import configuration';

    public function handle()
    {
        if (! config('services.google.gemini_key')) {
            $this->error('No Gemini API key found. Please add GEMINI_API_KEY to your .env file.');

            return 1;
        }

        $roleId = $this->option('role_id');
        $debug = $this->option('debug');
        $test = $this->option('test');
        $providedUrls = $this->option('urls');
        $providedCities = $this->option('cities');

        if ($debug) {
            $this->info('Debug mode enabled - verbose logging will be shown');
        }

        if ($test) {
            $this->warn('Test mode enabled - processing one new event from each URL');
        }

        // Decode ID if it's an encoded string
        if ($roleId && ! is_numeric($roleId)) {
            $roleId = UrlUtils::decodeId($roleId);
        }

        // Get curator roles with import configuration
        $query = Role::whereNotNull('import_config');

        if ($roleId) {
            $query->where('id', $roleId);
        }

        $curatorRoles = $query->get();

        if ($curatorRoles->isEmpty()) {
            $this->error('No curator roles with import configuration found.');

            return 0;
        }

        $this->info("Found {$curatorRoles->count()} curator role(s) with import configuration.");

        $totalEvents = 0;
        $totalErrors = 0;

        foreach ($curatorRoles as $curator) {
            // Use provided URLs/cities for testing, otherwise use role's config
            if ($test && (! empty($providedUrls) || ! empty($providedCities))) {
                $config = [
                    'urls' => array_map('strtolower', $providedUrls),
                    'cities' => array_map('strtolower', $providedCities),
                ];
            } else {
                $config = $curator->import_config;
            }

            if (empty($config['urls']) && empty($config['cities'])) {
                $this->warn("No URLs or cities configured for curator {$curator->name}");

                continue;
            }

            $eventsProcessed = 0;
            $errorsForCurator = 0;

            // Process URLs
            if (! empty($config['urls'])) {
                $this->info('Processing '.count($config['urls'])." URL(s) for curator {$curator->name}");

                foreach ($config['urls'] as $url) {
                    try {
                        $urlEvents = $this->processUrl($curator, $url, $debug, $test);
                        $eventsProcessed += $urlEvents;
                        $this->info("Processed {$urlEvents} events from URL: {$url}");
                    } catch (\Exception $e) {
                        $errorsForCurator++;
                        $totalErrors++;
                        $this->error("Error processing URL {$url}: ".$e->getMessage());
                        if ($debug) {
                            Log::error("Import error for curator {$curator->id}, URL {$url}: ".$e->getMessage());
                        }
                    }
                }
            }

            $totalEvents += $eventsProcessed;
        }

        $this->info("\nImport completed: {$totalEvents} events processed, {$totalErrors} errors");

        return 0;
    }

    /**
     * Check if scraping is allowed according to robots.txt
     */
    private function checkRobotsTxt(string $url, bool $debug = false): bool
    {
        try {
            // Parse the URL to get the base domain
            $parsedUrl = parse_url($url);
            if (! $parsedUrl || ! isset($parsedUrl['scheme']) || ! isset($parsedUrl['host'])) {
                if ($debug) {
                    $this->warn("Could not parse URL: {$url}");
                }

                return true; // Default to allowing if we can't parse the URL
            }

            $robotsUrl = $parsedUrl['scheme'].'://'.$parsedUrl['host'].'/robots.txt';

            if ($debug) {
                $this->line("Checking robots.txt at: {$robotsUrl}");
            }

            // Fetch robots.txt
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Event Schedule Bot/1.0 (+https://www.eventschedule.com)'])
                ->get($robotsUrl);

            if (! $response->successful()) {
                if ($debug) {
                    $this->warn("Could not fetch robots.txt (HTTP {$response->status()}), assuming scraping is allowed");
                }

                return true; // Default to allowing if robots.txt is not accessible
            }

            $robotsContent = $response->body();

            if ($debug) {
                $this->line('Robots.txt content length: '.strlen($robotsContent).' bytes');
            }

            // Parse robots.txt content
            $lines = explode("\n", $robotsContent);
            $userAgent = '*'; // We'll check for all user agents
            $disallowRules = [];
            $allowRules = [];
            $currentUserAgent = null;

            foreach ($lines as $line) {
                $line = trim($line);

                // Skip comments and empty lines
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }

                // Parse User-agent line
                if (preg_match('/^User-agent:\s*(.+)$/i', $line, $matches)) {
                    $currentUserAgent = trim($matches[1]);

                    continue;
                }

                // Parse Disallow line
                if (preg_match('/^Disallow:\s*(.+)$/i', $line, $matches)) {
                    $path = trim($matches[1]);
                    if ($currentUserAgent === '*' || $currentUserAgent === $userAgent) {
                        $disallowRules[] = $path;
                    }

                    continue;
                }

                // Parse Allow line
                if (preg_match('/^Allow:\s*(.+)$/i', $line, $matches)) {
                    $path = trim($matches[1]);
                    if ($currentUserAgent === '*' || $currentUserAgent === $userAgent) {
                        $allowRules[] = $path;
                    }

                    continue;
                }
            }

            // Check if the URL path is disallowed
            $urlPath = $parsedUrl['path'] ?? '/';

            // Check disallow rules
            foreach ($disallowRules as $disallowPath) {
                if ($this->pathMatches($urlPath, $disallowPath)) {
                    $this->warn("Scraping blocked for {$url} by robots.txt");

                    return false;
                }
            }

            // Check allow rules (allow rules override disallow rules)
            foreach ($allowRules as $allowPath) {
                if ($this->pathMatches($urlPath, $allowPath)) {
                    if ($debug) {
                        $this->line("URL path '{$urlPath}' matches allow rule '{$allowPath}'");
                    }

                    return true;
                }
            }

            if ($debug) {
                $this->line("No robots.txt restrictions found for URL: {$url}");
            }

            return true;

        } catch (\Exception $e) {
            if ($debug) {
                $this->warn('Error checking robots.txt: '.$e->getMessage());
            }

            return true; // Default to allowing if there's an error
        }
    }

    /**
     * Check if a URL path matches a robots.txt rule
     */
    private function pathMatches(string $urlPath, string $rulePath): bool
    {
        // Handle wildcard patterns
        if ($rulePath === '*') {
            return true;
        }

        // Handle exact path match
        if ($rulePath === $urlPath) {
            return true;
        }

        // Handle prefix matching (most common case)
        if (strpos($rulePath, '*') === false) {
            return strpos($urlPath, $rulePath) === 0;
        }

        // Handle wildcard patterns (basic implementation)
        $pattern = str_replace(['*', '?'], ['.*', '.'], $rulePath);
        $pattern = '#^'.$pattern.'#';

        return preg_match($pattern, $urlPath);
    }

    /**
     * Process a URL to find and import events
     */
    private function processUrl(Role $curator, string $url, bool $debug = false, bool $test = false): int
    {
        if ($debug) {
            $this->line("Fetching content from: {$url}");
        }

        // Check robots.txt first
        if (! $this->checkRobotsTxt($url, $debug)) {
            if ($debug) {
                $this->warn("Scraping not allowed for URL: {$url}");
            }

            return 0;
        }

        // Validate URL against SSRF before fetching
        if (! UrlUtils::isUrlSafe($url)) {
            if ($debug) {
                $this->warn("URL blocked by SSRF check: {$url}");
            }

            return 0;
        }

        // Fetch the webpage content
        $response = Http::timeout(30)
            ->withHeaders(['User-Agent' => 'Event Schedule Bot/1.0 (+https://www.eventschedule.com)'])
            ->get($url);

        if (! $response->successful()) {
            throw new \Exception("Failed to fetch URL: HTTP {$response->status()}");
        }

        $html = $response->body();

        if ($debug) {
            $this->line('Content length: '.strlen($html).' bytes');
        }

        // Extract event links from the HTML
        $eventLinks = $this->extractEventLinks($html, $url, $test);

        if ($debug) {
            $this->line('Found '.count($eventLinks).' potential event links');
        }

        $eventsProcessed = 0;

        foreach ($eventLinks as $eventUrl) {
            // Check if the event URL has already been parsed
            $parsedEventUrl = DB::table('parsed_event_urls')
                ->where('url', $eventUrl)
                ->where('role_id', $curator->id)
                ->first();

            if ($parsedEventUrl) {
                if ($test) {
                    // In test mode, continue to the next URL to find an unprocessed one
                    continue;
                } else {
                    continue;
                }
            }

            try {
                $eventCreated = $this->processEventUrl($curator, $eventUrl, $debug);

                if ($eventCreated) {
                    $eventsProcessed++;

                    // In test mode, stop after processing one new event
                    if ($test) {
                        break;
                    }
                }

                DB::table('parsed_event_urls')
                    ->insert(['url' => $eventUrl, 'role_id' => $curator->id]);
            } catch (\Exception $e) {
                if ($debug) {
                    $this->error("Error processing event URL {$eventUrl}: ".$e->getMessage());
                }
                Log::error("Error processing event URL {$eventUrl}: ".$e->getMessage());
            }
        }

        return $eventsProcessed;
    }

    /**
     * Extract event links from HTML content
     */
    private function extractEventLinks(string $html, string $baseUrl, bool $test = false): array
    {
        $links = [];

        // Use DOMDocument to parse HTML
        $dom = new \DOMDocument;
        @$dom->loadHTML($html, LIBXML_NOERROR);

        $xpath = new \DOMXPath($dom);

        // Look for common event link patterns
        $linkSelectors = [
            "//a[contains(@href, 'event')]",
            "//a[contains(@href, 'events')]",
            "//a[contains(@class, 'event')]",
            "//a[contains(@class, 'events')]",
            "//a[contains(text(), 'event')]",
            "//a[contains(text(), 'Event')]",
            "//a[contains(text(), 'EVENT')]",
        ];

        foreach ($linkSelectors as $selector) {
            $nodes = $xpath->query($selector);

            foreach ($nodes as $node) {
                $href = $node->getAttribute('href');
                if ($href) {
                    // Convert relative URLs to absolute
                    $absoluteUrl = $this->makeAbsoluteUrl($href, $baseUrl);

                    if ($absoluteUrl && ! in_array($absoluteUrl, $links)) {
                        $links[] = $absoluteUrl;
                    }
                }
            }
        }

        return array_slice($links, 0, 50);
    }

    /**
     * Convert relative URL to absolute URL
     */
    private function makeAbsoluteUrl(string $href, string $baseUrl): ?string
    {
        $href = explode('?', $href);
        $href = $href[0];

        // Skip if already absolute
        if (filter_var($href, FILTER_VALIDATE_URL)) {
            return $href;
        }

        // Skip if it's a fragment or javascript
        if (strpos($href, '#') === 0 || strpos($href, 'javascript:') === 0) {
            return null;
        }

        // Parse base URL
        $baseParts = parse_url($baseUrl);
        if (! $baseParts) {
            return null;
        }

        // Handle relative URLs
        if (strpos($href, '/') === 0) {
            // Absolute path
            return $baseParts['scheme'].'://'.$baseParts['host'].$href;
        } else {
            // Relative path
            $path = isset($baseParts['path']) ? dirname($baseParts['path']) : '/';

            return $baseParts['scheme'].'://'.$baseParts['host'].$path.'/'.$href;
        }
    }

    /**
     * Process a single event URL
     */
    private function processEventUrl(Role $curator, string $eventUrl, bool $debug = false): bool
    {
        if ($debug) {
            $this->line("Processing event URL: {$eventUrl}");
        }

        // Check if event already exists for this curator
        $existingEvent = Event::whereHas('roles', function ($query) use ($curator) {
            $query->where('role_id', $curator->id);
        })->where('registration_url', $eventUrl)->first();

        if ($existingEvent) {
            if ($debug) {
                $this->line("Event already exists for curator: {$eventUrl}");
            }

            return false;
        }

        // Fetch event page content
        $response = Http::timeout(30)
            ->withHeaders(['User-Agent' => 'Event Schedule Bot/1.0 (+https://www.eventschedule.com)'])
            ->get($eventUrl);

        if (! $response->successful()) {
            throw new \Exception("Failed to fetch event URL: HTTP {$response->status()}");
        }

        $html = $response->body();

        // Check for OpenGraph meta tags
        $dom = new \DOMDocument;
        @$dom->loadHTML($html, LIBXML_NOERROR);
        $xpath = new \DOMXPath($dom);

        // Extract OG title and description if available
        $ogTitle = $xpath->evaluate('string(//meta[@property="og:title"]/@content)');
        $ogDescription = $xpath->evaluate('string(//meta[@property="og:description"]/@content)');
        $ogImage = $xpath->evaluate('string(//meta[@property="og:image"]/@content)');

        // Download image data once if available
        $imageData = null;
        $imageFormat = null;
        $imageUrl = null;

        if ($ogImage && UrlUtils::isUrlSafe($ogImage)) {
            try {
                $imageData = file_get_contents($ogImage);
                $imageUrl = $ogImage;
                $imageFormat = ImageUtils::detectImageFormat($imageData, $ogImage);
                if ($debug) {
                    $this->line('Downloaded image data: '.strlen($imageData).' bytes, format: '.$imageFormat);
                }
            } catch (\Exception $e) {
                if ($debug) {
                    $this->warn('Failed to download image: '.$e->getMessage());
                }
            }
        }

        /*
        if ($ogTitle && $ogDescription) {
            $eventDetails = $ogTitle . ' ' . $ogDescription;
        } else {
            $eventDetails = $this->extractTextContent($html);
        }*/

        $eventDetails = $ogTitle.' '.$ogDescription.' '.$this->extractTextContent($html);

        // Extract event details using Gemini
        $eventDetails = $this->extractEventDetails($curator, $eventDetails, $eventUrl, $imageData, $imageFormat, $imageUrl, $debug);

        if ($debug) {
            $this->line('Event details: '.json_encode($eventDetails));
        }

        if (! $eventDetails) {
            if ($debug) {
                $this->line("Could not extract event details from: {$eventUrl}");
            }

            return false;
        }

        $config = $curator->import_config;

        // Process cities (placeholder for future implementation)
        if (! empty($config['cities'])) {
            $eventCity = isset($eventDetails['event_city']) ? strtolower($eventDetails['event_city']) : (isset($eventDetails['event_city_en']) ? strtolower($eventDetails['event_city_en']) : null);
            if (! in_array($eventCity, $config['cities'])) {
                if ($debug) {
                    $this->warn("Event city ({$eventCity}) does not match configured cities (".implode(', ', $config['cities']).')');
                }

                return false;
            }
        }

        // Create the event
        $event = $this->createEvent($curator, $eventDetails, $eventUrl, $imageData, $imageFormat, $imageUrl, $debug);

        if ($event) {
            if ($debug) {
                $this->line("Created event: {$event->name}");
            }

            return true;
        }

        return false;
    }

    /**
     * Extract event details from HTML using Gemini
     */
    private function extractEventDetails(Role $curator, string $textContent, string $eventUrl, ?string $imageData = null, ?string $imageFormat = null, ?string $imageUrl = null, bool $debug = false): ?array
    {
        if ($debug) {
            $this->line('Text content: '.$textContent);
        }

        // Create UploadedFile object from image data if available
        $file = null;
        if ($imageData && $imageUrl) {
            $file = ImageUtils::createUploadedFileFromImageData($imageData, $imageUrl);

            if ($debug) {
                $this->line('Created UploadedFile from image data: '.strlen($imageData).' bytes, format: '.$imageFormat);
            }
        }

        // Use Gemini to parse event details
        $parsedEvents = GeminiUtils::parseEvent($curator, $textContent, $file);

        if (empty($parsedEvents)) {
            return null;
        }

        // Use the first parsed event
        $eventData = $parsedEvents[0];

        // Check the event is valid
        if (empty($eventData['event_name']) || empty($eventData['event_date_time'])) {
            return null;
        }

        // Add the registration URL
        $eventData['registration_url'] = $eventUrl;

        return $eventData;
    }

    /**
     * Extract text content from HTML
     */
    private function extractTextContent(string $html): string
    {
        // Remove script and style tags
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);

        // Convert HTML to text
        $text = strip_tags($html);

        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        return $text;
    }

    /**
     * Create an event from extracted details
     */
    private function createEvent(Role $curator, array $eventData, string $eventUrl, ?string $imageData = null, ?string $imageFormat = null, ?string $imageUrl = null, bool $debug = false): ?Event
    {
        try {
            // Parse event date and time
            $eventDateTime = $eventData['event_date_time'] ?? null;

            if (! $eventDateTime) {
                $this->info('No event date time found');

                return null;
            }

            $startsAt = Carbon::parse($eventDateTime);

            // Don't create events in the past
            if ($startsAt->isPast()) {
                $this->info("Event is in the past: {$eventDateTime}");

                return null;
            }

            if ($debug) {
                $this->info("\nEvent Name: ".$eventData['event_name']);
                $this->info('Event Name (en): '.$eventData['event_name_en']);
                $this->info("\nEvent Details: ".$eventData['event_details']);
            }

            // Create a mock request object with the event data
            $request = new \Illuminate\Http\Request;
            $request->merge([
                'venue_name' => $eventData['venue_name'] ?? null,
                'venue_name_en' => $eventData['venue_name_en'] ?? null,
                'venue_address1' => $eventData['event_address'] ?? null,
                'venue_address1_en' => $eventData['event_address_en'] ?? null,
                'venue_city' => $eventData['event_city'] ?? null,
                'venue_city_en' => $eventData['event_city_en'] ?? null,
                'venue_state' => $eventData['event_state'] ?? null,
                'venue_state_en' => $eventData['event_state_en'] ?? null,
                'venue_postal_code' => $eventData['event_postal_code'] ?? null,
                'venue_country_code' => $eventData['event_country_code'] ?? null,
                'venue_id' => $eventData['venue_id'] ?? null,
                'venue_website' => $eventData['venue_website'] ?? null,
                'venue_language_code' => $curator->language_code,
                'members' => $this->buildMembersData($eventData),
                'name' => $eventData['event_name'] ?? 'Imported Event',
                'name_en' => $eventData['event_name_en'] ?? null,
                'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                'description' => $eventData['event_details'] ?? '',
                'social_image' => $eventData['social_image'] ?? null,
                'registration_url' => $eventUrl,
                'curators' => [UrlUtils::encodeId($curator->id)],
                'tickets_enabled' => false,
                'category_id' => $eventData['category_id'] ?? null,
            ]);

            if (isset($eventData['event_duration']) && $eventData['event_duration'] > 0) {
                $request->merge([
                    'duration' => $eventData['event_duration'],
                ]);
            }

            $request->setUserResolver(function () use ($curator) {
                return User::find($curator->user_id);
            });

            // Use the EventRepo to save the event
            $eventRepo = app(\App\Repos\EventRepo::class);
            $event = $eventRepo->saveEvent($curator, $request);

            if ($imageData && $imageUrl) {
                // Use the already-downloaded image data
                $filename = ImageUtils::saveImageData($imageData, $imageUrl);

                $event->flyer_image_url = $filename;
                $event->save();
            }

            if ($event) {
                $this->info('Event created: '.$event->name);
            } else {
                $this->info('Event not created');
            }

            return $event;
        } catch (\Exception $e) {
            if ($debug) {
                $this->info('Error creating event: '.$e->getMessage());
            }

            Log::error('Error creating event: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Build members data array from event data
     */
    private function buildMembersData(array $eventData): array
    {
        $members = [];

        // Handle performers array if present
        if (! empty($eventData['performers'])) {
            \Log::info('buildMembersData: performers found', [
                'count' => count($eventData['performers']),
                'performers' => $eventData['performers'],
            ]);

            foreach ($eventData['performers'] as $index => $performer) {
                $memberId = ! empty($performer['talent_id']) ? $performer['talent_id'] : "new_talent_{$index}";

                \Log::info('buildMembersData: processing performer', [
                    'index' => $index,
                    'performer_name' => $performer['name'] ?? null,
                    'talent_id_in_data' => $performer['talent_id'] ?? null,
                    'talent_id_empty' => empty($performer['talent_id']),
                    'resulting_member_id' => $memberId,
                ]);

                $members[$memberId] = [
                    'name' => $performer['name'] ?? '',
                    'name_en' => $performer['name_en'] ?? '',
                    'email' => $performer['email'] ?? '',
                    'website' => $performer['website'] ?? '',
                    'youtube_url' => $performer['youtube_url'] ?? '',
                ];
            }
        } else {
            \Log::info('buildMembersData: no performers in eventData');
        }

        \Log::info('buildMembersData: final members', [
            'member_keys' => array_keys($members),
        ]);

        return $members;
    }
}
