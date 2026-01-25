<?php

namespace App\Services;

use App\Models\AnalyticsDaily;
use App\Models\AnalyticsEventsDaily;
use App\Models\AnalyticsReferrersDaily;
use App\Models\Event;
use App\Models\Group;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoService
{
    /**
     * Demo user email
     */
    public const DEMO_EMAIL = 'contact@eventschedule.com';

    /**
     * Demo subdomain (auto-login trigger)
     */
    public const DEMO_SUBDOMAIN = 'demo';

    /**
     * Demo role subdomain (the actual schedule - curator that aggregates all venues)
     */
    public const DEMO_ROLE_SUBDOMAIN = 'demo-simpsons';

    /**
     * Demo social links (Event Schedule social media accounts)
     */
    public const DEMO_SOCIAL_LINKS = '[{"url":"https://www.facebook.com/appeventschedule"},{"url":"https://www.instagram.com/eventschedule/"},{"url":"https://youtube.com/@EventSchedule"},{"url":"https://x.com/ScheduleEvent"},{"url":"https://www.linkedin.com/company/eventschedule/"}]';

    /**
     * Check if the given user is the demo user
     */
    public static function isDemoUser(?User $user): bool
    {
        return $user && $user->email === self::DEMO_EMAIL;
    }

    /**
     * Get or create the demo user
     */
    public function getOrCreateDemoUser(): User
    {
        $user = User::where('email', self::DEMO_EMAIL)->first();

        if (! $user) {
            $user = User::create([
                'name' => 'Demo User',
                'email' => self::DEMO_EMAIL,
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
                'timezone' => 'America/New_York',
                'language_code' => 'en',
            ]);
        }

        return $user;
    }

    /**
     * Get or create the demo role (curator that aggregates all Springfield venues)
     */
    public function getOrCreateDemoRole(User $user): Role
    {
        $role = Role::where('subdomain', self::DEMO_ROLE_SUBDOMAIN)->first();

        if (! $role) {
            $role = new Role;
            $role->user_id = $user->id;
            $role->subdomain = self::DEMO_ROLE_SUBDOMAIN;
            $role->type = 'curator';
            $role->name = 'The Simpsons - Springfield Events';
            $role->email = self::DEMO_EMAIL;
            $role->email_verified_at = now();
            $role->language_code = 'en';
            $role->timezone = 'America/New_York';
            $role->city = 'Springfield';
            $role->country_code = 'US';
            $role->background = 'gradient';
            $role->background_colors = '#FFD90F, #0064B0'; // Classic Simpsons yellow/blue
            $role->accent_color = '#FFD90F';
            $role->accept_requests = true;
            $role->plan_type = 'pro';
            $role->plan_expires = now()->addYear()->format('Y-m-d');
            $role->trial_ends_at = now()->addYear();
            $role->social_links = self::DEMO_SOCIAL_LINKS;
            $role->save();

            // Attach user to role as owner
            $role->users()->attach($user->id, ['level' => 'owner']);
        }

        return $role;
    }

    /**
     * Populate demo data for a role (curator with multiple venues)
     */
    public function populateDemoData(Role $role): void
    {
        $user = $role->user;

        // Download demo images if they don't exist
        $this->downloadDemoImages();

        // Create demo talent roles with themed gradients
        $this->createDemoTalents($user);

        // Create demo venue schedules (Moe's Tavern, Bowl-A-Rama, etc.)
        $venues = $this->createDemoVenues($user);

        // Create curator groups (Bars & Nightlife, Entertainment, etc.)
        $curatorGroups = $this->createCuratorGroups($role);

        // Create venue groups for each venue
        $venueGroups = [];
        foreach ($venues as $subdomain => $venue) {
            $venueGroups[$subdomain] = $this->createGroups($venue);
        }

        // Create events with tickets (attached to venues AND curator)
        $this->createEvents($role, $user, $venues, $venueGroups, $curatorGroups);

        // Create followed schedules for the demo user (external schedules)
        $this->createFollowedSchedules($user);

        // Create ticket purchases for the demo user
        $this->createUserTicketPurchases($user);

        // Create analytics data for the demo role (curator)
        $this->createAnalyticsData($role);

        // Create analytics data for each venue
        foreach ($venues as $venue) {
            $this->createAnalyticsData($venue);
        }
    }

    /**
     * Download demo images from Unsplash if they don't exist
     */
    protected function downloadDemoImages(): void
    {
        $demoDir = public_path('images/demo');
        if (! is_dir($demoDir)) {
            mkdir($demoDir, 0755, true);
        }

        // Unsplash Source API URLs (no API key needed)
        $images = [
            // Jazz - Dark saxophone player in moody setting
            'demo_flyer_jazz.jpg' => 'https://images.unsplash.com/photo-1415201364774-f6f0bb35f28f?w=800&h=600&fit=crop',

            // DJ - Dark DJ booth with neon lighting
            'demo_flyer_dj.jpg' => 'https://images.unsplash.com/photo-1571266028243-e4733b0f0bb0?w=800&h=600&fit=crop',

            // Comedy - Stage with microphone under spotlight
            'demo_flyer_comedy.jpg' => 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?w=800&h=600&fit=crop',

            // Open Mic - Intimate acoustic stage setting
            'demo_flyer_openmic.jpg' => 'https://images.unsplash.com/photo-1598517834429-cf89c3c0f065?w=800&h=600&fit=crop',

            // Rock - Energetic rock band on stage
            'demo_flyer_rock.jpg' => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=800&h=600&fit=crop',

            // Party - Crowded bar celebration atmosphere
            'demo_flyer_party.jpg' => 'https://images.unsplash.com/photo-1575444758702-4a6b9222336e?w=800&h=600&fit=crop',

            // Special - Neon bar sign/atmosphere
            'demo_flyer_special.jpg' => 'https://images.unsplash.com/photo-1572116469696-31de0f17cc34?w=800&h=600&fit=crop',

            // Profile - Bartender at bar counter (square)
            'demo_profile_band.jpg' => 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=400&h=400&fit=crop',
        ];

        foreach ($images as $filename => $url) {
            $path = $demoDir.'/'.$filename;
            if (! file_exists($path)) {
                try {
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 10,
                            'user_agent' => 'EventSchedule/1.0',
                        ],
                    ]);
                    $imageContent = @file_get_contents($url, false, $context);
                    if ($imageContent !== false) {
                        file_put_contents($path, $imageContent);
                    }
                } catch (\Exception $e) {
                    // Silently skip if download fails
                    continue;
                }
            }
        }
    }

    /**
     * Create demo talent roles with themed gradients
     */
    protected function createDemoTalents(User $user): void
    {
        $talents = [
            [
                'name' => 'Lisa Simpson Jazz Quartet',
                'subdomain' => 'demo-lisajazz',
                'background_colors' => '#1a237e, #4a148c',
                'background_rotation' => 135,
            ],
            [
                'name' => 'Krusty Entertainment',
                'subdomain' => 'demo-krusty',
                'background_colors' => '#f44336, #ff9800',
                'background_rotation' => 45,
            ],
            [
                'name' => 'DJ Sideshow Bob',
                'subdomain' => 'demo-djbob',
                'background_colors' => '#7b1fa2, #00bcd4',
                'background_rotation' => 160,
            ],
            [
                'name' => 'Springfield Rockers',
                'subdomain' => 'demo-rockers',
                'background_colors' => '#212121, #616161',
                'background_rotation' => 180,
            ],
            [
                'name' => 'Open Mic Collective',
                'subdomain' => 'demo-openmic',
                'background_colors' => '#4e342e, #8d6e63',
                'background_rotation' => 120,
            ],
            [
                'name' => 'Troy McClure Productions',
                'subdomain' => 'demo-troymcclure',
                'background_colors' => '#b71c1c, #880e4f',
                'background_rotation' => 200,
            ],
            [
                'name' => 'Professor Frink Presents',
                'subdomain' => 'demo-frink',
                'background_colors' => '#0d47a1, #00695c',
                'background_rotation' => 90,
            ],
            [
                'name' => 'Stonecutters Guild',
                'subdomain' => 'demo-stonecutters',
                'background_colors' => '#5d4037, #ff8f00',
                'background_rotation' => 225,
            ],
        ];

        foreach ($talents as $talentData) {
            // Skip if already exists
            if (Role::where('subdomain', $talentData['subdomain'])->exists()) {
                continue;
            }

            $role = new Role;
            $role->user_id = $user->id;
            $role->subdomain = $talentData['subdomain'];
            $role->type = 'talent';
            $role->name = $talentData['name'];
            $role->email = self::DEMO_EMAIL;
            $role->email_verified_at = now();
            $role->language_code = 'en';
            $role->timezone = 'America/New_York';
            $role->city = 'Springfield';
            $role->country_code = 'US';
            $role->background = 'gradient';
            $role->background_colors = $talentData['background_colors'];
            $role->background_rotation = $talentData['background_rotation'];
            $role->plan_type = 'pro';
            $role->plan_expires = now()->addYear()->format('Y-m-d');
            $role->social_links = self::DEMO_SOCIAL_LINKS;
            $role->save();

            // Attach user to role as owner (so it's "claimed")
            $role->users()->attach($user->id, ['level' => 'owner']);
        }
    }

    /**
     * Create demo venue schedules
     */
    protected function createDemoVenues(User $user): array
    {
        $venues = [
            [
                'name' => "Moe's Tavern",
                'subdomain' => 'demo-moestavern',
                'background_colors' => '#D4A017, #8B4513', // Brown/gold
                'accent_color' => '#4E81FA',
            ],
            [
                'name' => "Barney's Bowl-A-Rama",
                'subdomain' => 'demo-bowlarama',
                'background_colors' => '#8B4513, #FF6B35', // Brown/orange
                'accent_color' => '#FF6B35',
            ],
            [
                'name' => 'The Aztec Theater',
                'subdomain' => 'demo-aztectheater',
                'background_colors' => '#DAA520, #800020', // Gold/maroon art deco
                'accent_color' => '#DAA520',
            ],
            [
                'name' => 'Springfield Amphitheater',
                'subdomain' => 'demo-amphitheater',
                'background_colors' => '#1E90FF, #228B22', // Blue/green outdoor
                'accent_color' => '#228B22',
            ],
            [
                'name' => 'Lard Lad Donuts',
                'subdomain' => 'demo-lardlad',
                'background_colors' => '#FF69B4, #8B4513', // Pink/brown
                'accent_color' => '#FF69B4',
            ],
            [
                'name' => 'Springfield Community Center',
                'subdomain' => 'demo-communitycenter',
                'background_colors' => '#228B22, #FFFFFF', // Civic green/white
                'accent_color' => '#228B22',
            ],
        ];

        $createdVenues = [];

        foreach ($venues as $venueData) {
            // Skip if already exists
            $existing = Role::where('subdomain', $venueData['subdomain'])->first();
            if ($existing) {
                $createdVenues[$venueData['subdomain']] = $existing;

                continue;
            }

            $role = new Role;
            $role->user_id = $user->id;
            $role->subdomain = $venueData['subdomain'];
            $role->type = 'venue';
            $role->name = $venueData['name'];
            $role->email = self::DEMO_EMAIL;
            $role->email_verified_at = now();
            $role->language_code = 'en';
            $role->timezone = 'America/New_York';
            $role->city = 'Springfield';
            $role->country_code = 'US';
            $role->background = 'gradient';
            $role->background_colors = $venueData['background_colors'];
            $role->accent_color = $venueData['accent_color'];
            $role->accept_requests = true;
            $role->plan_type = 'pro';
            $role->plan_expires = now()->addYear()->format('Y-m-d');
            $role->trial_ends_at = now()->addYear();
            $role->social_links = self::DEMO_SOCIAL_LINKS;
            $role->save();

            // Attach user to role as owner
            $role->users()->attach($user->id, ['level' => 'owner']);

            $createdVenues[$venueData['subdomain']] = $role;
        }

        return $createdVenues;
    }

    /**
     * Create curator groups (for the main curator schedule)
     */
    protected function createCuratorGroups(Role $role): array
    {
        $groupNames = [
            'Bars & Nightlife',
            'Entertainment',
            'Community',
            'Arts & Culture',
        ];

        $groups = [];
        foreach ($groupNames as $name) {
            $group = $role->groups()->create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
            $groups[$name] = $group;
        }

        return $groups;
    }

    /**
     * Reset demo data - deletes all demo data and repopulates
     * Handles curator + venues structure
     */
    public function resetDemoData(Role $role): void
    {
        DB::transaction(function () use ($role) {
            $user = $role->user;

            // Delete demo user's ticket purchases first (sales where user_id is demo user)
            if ($user) {
                $userSaleIds = Sale::where('user_id', $user->id)->pluck('id');
                SaleTicket::whereIn('sale_id', $userSaleIds)->delete();
                Sale::where('user_id', $user->id)->delete();
            }

            // Delete all demo-prefixed roles EXCEPT the curator (demo-simpsons)
            // This includes venues (demo-moestavern, demo-bowlarama, etc.), talents, and followed schedules
            $demoRoles = Role::where('subdomain', 'like', 'demo-%')
                ->where('subdomain', '!=', self::DEMO_ROLE_SUBDOMAIN)
                ->get();

            foreach ($demoRoles as $demoRole) {
                // Delete events and related data for this role
                $demoEventIds = $demoRole->events()->pluck('events.id');

                // Delete sales and sale tickets
                SaleTicket::whereIn('sale_id', function ($query) use ($demoEventIds) {
                    $query->select('id')
                        ->from('sales')
                        ->whereIn('event_id', $demoEventIds);
                })->delete();

                Sale::whereIn('event_id', $demoEventIds)->delete();
                Ticket::whereIn('event_id', $demoEventIds)->delete();

                // Delete analytics for events
                AnalyticsEventsDaily::whereIn('event_id', $demoEventIds)->delete();

                // Delete analytics for role
                AnalyticsDaily::where('role_id', $demoRole->id)->delete();
                AnalyticsReferrersDaily::where('role_id', $demoRole->id)->delete();

                // Detach events and delete events created by this role
                $demoRole->events()->detach();
                Event::where('creator_role_id', $demoRole->id)->delete();

                // Delete groups
                Group::where('role_id', $demoRole->id)->delete();

                // Detach users and delete the role
                $demoRole->users()->detach();
                $demoRole->delete();
            }

            // Now clean up the curator (the role passed in)
            $curatorEventIds = $role->events()->pluck('events.id');

            // Detach events from curator (events were already deleted above when venues were cleaned)
            $role->events()->detach();

            // Delete curator groups
            Group::where('role_id', $role->id)->delete();

            // Delete analytics for curator
            AnalyticsDaily::where('role_id', $role->id)->delete();
            AnalyticsEventsDaily::whereIn('event_id', $curatorEventIds)->delete();
            AnalyticsReferrersDaily::where('role_id', $role->id)->delete();

            // Repopulate
            $this->populateDemoData($role);
        });
    }

    /**
     * Create demo groups
     */
    protected function createGroups(Role $role): array
    {
        $groupNames = [
            'Live Music',
            'DJ Nights',
            'Comedy',
            'Open Mic',
            'Special Events',
        ];

        $groups = [];
        foreach ($groupNames as $name) {
            $group = $role->groups()->create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
            $groups[$name] = $group;
        }

        return $groups;
    }

    /**
     * Create demo events with tickets and sales
     * Events are attached to their venue AND to the curator
     */
    protected function createEvents(Role $curator, User $user, array $venues, array $venueGroups, array $curatorGroups): void
    {
        $events = $this->getEventTemplates();
        // Use fixed start date of January 1, 2026 for demo data
        $startDate = Carbon::create(2026, 1, 1, 0, 0, 0, $curator->timezone);

        foreach ($events as $index => $eventData) {
            $isRecurring = ! empty($eventData['days_of_week']);

            if ($isRecurring) {
                // For recurring events, find the first occurrence of the target day on or after Jan 1, 2026
                $targetDayOfWeek = strpos($eventData['days_of_week'], '1');
                $eventDate = $startDate->copy();

                // Find the first occurrence of this day of week on or after start date
                while ($eventDate->dayOfWeek !== $targetDayOfWeek) {
                    $eventDate->addDay();
                }

                // Set the time based on event template or default
                $hour = $eventData['hour'] ?? rand(18, 21);
                $minute = $eventData['minute'] ?? (rand(0, 1) * 30);
                $eventDate->setHour($hour)->setMinute($minute)->setSecond(0);
            } else {
                // One-time events (like New Year's Eve): set to Dec 31, 2026
                $eventDate = $startDate->copy()
                    ->setMonth(12)
                    ->setDay(31)
                    ->setHour(21)
                    ->setMinute(0)
                    ->setSecond(0);
            }

            // Get the venue for this event
            $venueSubdomain = $eventData['venue'] ?? 'demo-moestavern';
            $venue = $venues[$venueSubdomain] ?? reset($venues);

            // Create event (owned by the venue)
            $event = new Event;
            $event->user_id = $user->id;
            $event->creator_role_id = $venue->id;
            $event->name = $eventData['name'];
            $event->description = $eventData['description'];
            $event->starts_at = $eventDate->utc();
            $event->duration = $eventData['duration'];
            $event->slug = Str::slug($eventData['name']);
            $event->tickets_enabled = true;
            $event->ticket_currency_code = 'USD';
            $event->flyer_image_url = $eventData['image'] ?? null;

            // Set recurring fields for recurring events
            if ($isRecurring) {
                $event->days_of_week = $eventData['days_of_week'];
                $event->recurring_end_type = 'never';
            }

            $event->save();

            // Attach to venue with venue group
            $venueGroupName = $eventData['group'];
            $venueGroup = ($venueGroups[$venueSubdomain] ?? [])[$venueGroupName] ?? null;
            $venue->events()->attach($event->id, [
                'is_accepted' => true,
                'group_id' => $venueGroup?->id,
            ]);

            // Attach to curator with curator group
            $curatorGroupName = $eventData['curator_group'] ?? 'Entertainment';
            $curatorGroup = $curatorGroups[$curatorGroupName] ?? null;
            $curator->events()->attach($event->id, [
                'is_accepted' => true,
                'group_id' => $curatorGroup?->id,
            ]);

            // Attach talent role(s) if specified
            $talents = $eventData['talents'] ?? (isset($eventData['talent']) ? [$eventData['talent']] : []);
            foreach ($talents as $talentSubdomain) {
                $talentRole = Role::where('subdomain', $talentSubdomain)->first();
                if ($talentRole) {
                    $talentRole->events()->attach($event->id, ['is_accepted' => true]);
                }
            }

            // Create tickets
            $this->createTicketsForEvent($event, $eventData['tickets']);

            // Create sample sales for recurring events (they have past occurrences)
            if ($isRecurring) {
                $this->createSalesForEvent($event, $venue);
            }
        }
    }

    /**
     * Create tickets for an event
     */
    protected function createTicketsForEvent(Event $event, array $ticketTypes): void
    {
        foreach ($ticketTypes as $ticketData) {
            Ticket::create([
                'event_id' => $event->id,
                'type' => $ticketData['type'],
                'price' => $ticketData['price'],
                'quantity' => $ticketData['quantity'],
                'description' => $ticketData['description'] ?? null,
            ]);
        }
    }

    /**
     * Create sample sales for an event
     */
    protected function createSalesForEvent(Event $event, Role $role): void
    {
        $eventDate = Carbon::parse($event->starts_at)->format('Y-m-d');
        $tickets = $event->tickets;

        // Create 2-5 sample sales per event
        $numSales = rand(2, 5);
        $firstNames = ['Homer', 'Marge', 'Bart', 'Lisa', 'Maggie', 'Ned', 'Maude', 'Milhouse'];
        $lastNames = ['Simpson', 'Flanders', 'Van Houten', 'Bouvier', 'Burns', 'Carlson', 'Leonard', 'Szyslak'];

        for ($i = 0; $i < $numSales; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            $sale = Sale::create([
                'event_id' => $event->id,
                'name' => $firstName.' '.$lastName,
                'email' => strtolower($firstName).'.'.strtolower($lastName).'@example.com',
                'event_date' => $eventDate,
                'subdomain' => $role->subdomain,
                'status' => 'paid',
                'payment_method' => 'stripe',
                'transaction_reference' => __('messages.manual_payment'),
                'secret' => Str::random(32),
            ]);

            // Add 1-2 tickets to each sale
            $numTickets = rand(1, 2);
            $totalAmount = 0;

            foreach ($tickets->take($numTickets) as $ticket) {
                $quantity = rand(1, 2);
                SaleTicket::create([
                    'sale_id' => $sale->id,
                    'ticket_id' => $ticket->id,
                    'quantity' => $quantity,
                    'seats' => json_encode(array_fill(1, $quantity, null)),
                ]);

                // Update ticket sold count
                $ticket->updateSold($eventDate, $quantity);
                $totalAmount += $ticket->price * $quantity;
            }

            $sale->payment_amount = $totalAmount;
            $sale->save();
        }
    }

    /**
     * Get event templates for demo data
     * Each event includes 'venue' (subdomain) and 'curator_group' for proper linking
     */
    protected function getEventTemplates(): array
    {
        return [
            // ===========================
            // MOE'S TAVERN EVENTS
            // ===========================
            [
                'name' => 'Jazz Night with Bleeding Gums Murphy',
                'description' => "A tribute to Springfield's greatest jazz musician. Lisa Simpson and friends perform classic Bleeding Gums Murphy hits.\n\n**Featuring:**\n- \"Sax on the Beach\"\n- \"Jazzman\"\n- And more smooth saxophone\n\nBring your tissues. This one's emotional.",
                'duration' => 3,
                'group' => 'Live Music',
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_jazz.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-lisajazz',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 22, 'quantity' => 100],
                    ['type' => 'VIP Table', 'price' => 55, 'quantity' => 20, 'description' => 'Reserved seating + commemorative saxophone pin'],
                ],
            ],
            [
                'name' => 'Monorail Karaoke Night',
                'description' => "I've sold monorails to Brockway, Ogdenville, and North Haverbrook, and by gum, it put them on the map!\n\n**Tonight's Setlist:**\n- \"Monorail\" (mandatory opener)\n- \"We Put the Spring in Springfield\"\n- \"See My Vest\"\n- \"Who Needs the Kwik-E-Mart\"\n\nIs there a chance the track could bend? Not on your life, my Hindu friend!",
                'duration' => 3,
                'group' => 'Open Mic',
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_openmic.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0100000', // Monday
                'talent' => 'demo-openmic',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 12, 'quantity' => 100],
                    ['type' => 'Lyle Lanley VIP', 'price' => 30, 'quantity' => 20, 'description' => 'Reserved seating + monorail conductor hat'],
                ],
            ],
            [
                'name' => 'Isotopes Game Watch Party',
                'description' => "Go Isotopes! Watch Springfield's beloved baseball team on the big screen!\n\n**Specials:**\n- \$3 hot dogs (made from 100% Grade-F meat)\n- \$2 Duffs during innings 1-3\n- Free nachos when Isotopes score\n\n*Warning: Team may relocate to Albuquerque at any moment.*\n\nRemember: Dancin' Homer appearances are NOT guaranteed.",
                'duration' => 3,
                'group' => 'Special Events',
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0100000', // Monday
                'talent' => 'demo-rockers',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 5, 'quantity' => 150],
                ],
            ],
            [
                'name' => "Poetry Slam: Moe's Haiku Hour",
                'description' => "Words that move you... to tears. Competitive spoken word poetry featuring Springfield's most melancholic verses.\n\n**Hosted by Moe Szyslak**\n\nSample:\n*\"My life is empty\nNo one calls, the bar is dead\nPass the rat poison\"*\n\nTissues provided. Bring your sad poems.",
                'duration' => 2.5,
                'group' => 'Open Mic',
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_openmic.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0010000', // Tuesday
                'talent' => 'demo-openmic',
                'tickets' => [
                    ['type' => 'Audience', 'price' => 10, 'quantity' => 100],
                    ['type' => 'Poet Entry', 'price' => 5, 'quantity' => 20],
                ],
            ],
            [
                'name' => 'Trivia: Springfield History',
                'description' => "Test your knowledge of our beloved town! Hosted by Professor Frink.\n\n**Sample Questions:**\n- Who really founded Springfield?\n- What's the tire fire's birthday?\n- How many times has Sideshow Bob tried to kill Bart?\n\nGlayvin! Prizes for top scorers!",
                'duration' => 2,
                'group' => 'Special Events',
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0001000', // Wednesday
                'talent' => 'demo-frink',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 10, 'quantity' => 60],
                ],
            ],
            [
                'name' => 'Open Mic Night - Springfield Edition',
                'description' => "Share your talent with Springfield! Our weekly open mic welcomes musicians, comedians, poets, and performers of all kinds.\n\n**Featured Acts:**\n- Homer's interpretive poetry\n- Milhouse's magic tricks\n- Ralph's show and tell\n\n**Sign-up starts at 6:30 PM**\n\nEverybody's welcome! Even Shelbyville residents.",
                'duration' => 3,
                'group' => 'Open Mic',
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_openmic.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0000100', // Thursday
                'talents' => ['demo-openmic', 'demo-lisajazz'],
                'tickets' => [
                    ['type' => 'Free Entry', 'price' => 0, 'quantity' => 100],
                ],
            ],
            [
                'name' => 'Karaoke Night',
                'description' => "Grab the mic and belt out your favorites! \"Baby on Board\" performances strongly encouraged.\n\n**Fan Favorites:**\n- \"See My Vest\" (Mr. Burns)\n- \"We Put The Spring in Springfield\"\n- \"Happy Birthday, Lisa\"\n\nThe Be Sharps reunions welcome!",
                'duration' => 3,
                'group' => 'Open Mic',
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_openmic.jpg',
                'hour' => 21,
                'minute' => 0,
                'days_of_week' => '0000100', // Thursday
                'talent' => 'demo-openmic',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 8, 'quantity' => 75],
                ],
            ],
            [
                'name' => 'Stand-Up with Krusty',
                'description' => "Hey hey! Krusty the Clown brings his legendary stand-up act to Moe's Tavern!\n\n**Lineup:**\n- Krusty the Clown (Headliner)\n- Mr. Teeny (Opening Act)\n- Sideshow Mel\n\nHey hey! This show is Krusty-approved!",
                'duration' => 2.5,
                'group' => 'Comedy',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 21,
                'minute' => 0,
                'days_of_week' => '0000010', // Friday
                'talent' => 'demo-krusty',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 80],
                    ['type' => 'Front Row', 'price' => 35, 'quantity' => 15, 'description' => 'Best seats - warning: may get squirted with seltzer'],
                ],
            ],
            [
                'name' => 'Flaming Moe Night',
                'description' => "Try the drink that put Moe's on the map! The legendary Flaming Moe - now with a secret ingredient that definitely isn't cough syrup.\n\n**Specials:**\n- \$5 Flaming Moes all night\n- Fire extinguishers provided\n- Aerosmith NOT scheduled to appear\n\nDress code: Casual (fire-resistant clothing recommended)",
                'duration' => 5,
                'group' => 'Special Events',
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 17,
                'minute' => 0,
                'days_of_week' => '0000010', // Friday
                'talent' => 'demo-stonecutters',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 20, 'quantity' => 180],
                    ['type' => 'Flaming Moe Package', 'price' => 75, 'quantity' => 8, 'description' => '5 Flaming Moes + commemorative glass'],
                ],
            ],
            [
                'name' => 'DJ Sideshow Bob',
                'description' => "Get ready to dance! DJ Sideshow Bob spins the hottest electronic tracks all night long. State-of-the-art sound system included.\n\n**WARNING:** Rakes have been removed from the premises for your safety.\n\n21+ event. Valid Springfield ID required.",
                'duration' => 4,
                'group' => 'DJ Nights',
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_dj.jpg',
                'hour' => 22,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talent' => 'demo-djbob',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 200],
                    ['type' => 'VIP Access', 'price' => 45, 'quantity' => 30, 'description' => 'Skip the line + avoid rakes'],
                ],
            ],
            [
                'name' => 'Comedy Roast: Principal Skinner',
                'description' => "SKINNER! Tonight we roast Springfield Elementary's finest principal. Hosted by Superintendent Chalmers.\n\n**Roasters include:**\n- Superintendent Chalmers\n- Groundskeeper Willie\n- Mrs. Krabappel (via video tribute)\n- Bart Simpson\n\nSteamed hams will NOT be served. It's an Albany expression.",
                'duration' => 2.5,
                'group' => 'Comedy',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talents' => ['demo-krusty', 'demo-openmic'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 100],
                    ['type' => 'Aurora Borealis Package', 'price' => 40, 'quantity' => 20, 'description' => 'Front row + steamed ham (actually grilled)'],
                ],
            ],
            [
                'name' => '80s Night: Do The Bartman',
                'description' => "Flashback to the greatest decade! Dress in your best 80s/90s attire and dance to all the classics.\n\n**Featuring:**\n- \"Do The Bartman\" dance-off at 11 PM\n- Costume contest (Marge's hair encouraged)\n- Deep Cuts from the Springfield Files\n\nAy caramba!",
                'duration' => 4,
                'group' => 'DJ Nights',
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 21,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talents' => ['demo-djbob', 'demo-rockers'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 150],
                ],
            ],
            [
                'name' => 'Happy Hour: $1 Squishees',
                'description' => "Who needs the Kwik-E-Mart? WE DO!\n\n**Daily Happy Hour Specials (4-6 PM):**\n- \$1 Squishees (all 47 flavors!)\n- Brain freeze competition at 5 PM\n- Apu's secret recipe nachos\n\n*Thank you, come again!*\n\nNote: Squishee machine may occasionally achieve sentience.",
                'duration' => 2,
                'group' => 'Special Events',
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 16,
                'minute' => 0,
                'days_of_week' => '0000010', // Friday
                'talent' => 'demo-frink',
                'tickets' => [
                    ['type' => 'Free Entry', 'price' => 0, 'quantity' => 200],
                ],
            ],
            [
                'name' => 'Legal Sea Foods with Lionel Hutz',
                'description' => "Works on contingency? No, money down!\n\n**Tonight's Seminar:**\n- How to read contracts (hint: don't)\n- \"That's why you're the judge and I'm the law-talking guy\"\n- Business card printing workshop\n- Smoking in court: pros and cons\n\n*\"Mr. Simpson, this is the most blatant case of fraudulent advertising since my suit against the film 'The Neverending Story.'\"*\n\nFree breadsticks (technically a loophole).",
                'duration' => 2,
                'group' => 'Comedy',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 19,
                'minute' => 30,
                'days_of_week' => '0001000', // Wednesday
                'talent' => 'demo-krusty',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 60],
                    ['type' => 'I Can\'t Believe It\'s a Law Firm Package', 'price' => 35, 'quantity' => 15, 'description' => 'Includes fake business cards + orange drink'],
                ],
            ],
            [
                'name' => 'Dental Plan Night',
                'description' => "Lisa needs braces! DENTAL PLAN! Lisa needs braces! DENTAL PLAN!\n\n**Union Meeting & Celebration:**\n- Free dental checkups\n- Carl Carlson's powerpoint presentation\n- Lenny's eye patch station\n- \"Where's my burrito?\" snack bar\n\n*Come for the dental plan. Stay for the braces.*\n\nSponsored by Local 643.",
                'duration' => 3,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 18,
                'minute' => 30,
                'days_of_week' => '0000010', // Friday
                'talent' => 'demo-rockers',
                'tickets' => [
                    ['type' => 'Union Member', 'price' => 0, 'quantity' => 100],
                    ['type' => 'Management', 'price' => 50, 'quantity' => 10, 'description' => 'No dental plan for you'],
                ],
            ],
            [
                'name' => 'Canyonero Night',
                'description' => "CAN YOU NAME THE TRUCK WITH FOUR-WHEEL DRIVE? SMELLS LIKE A STEAK AND SEATS THIRTY-FIVE?\n\n**CANYONERO!**\n\n**Tonight's Events:**\n- SUV parade in the parking lot\n- \"Unexplained fires are a matter for the courts\" disclaimer signing\n- 12 yards long, 2 lanes wide display\n- Squirrel-crushing demonstration\n\n*She blinds everybody with her super high beams!*\n\nTop of the line in utility sports!",
                'duration' => 3,
                'group' => 'Special Events',
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talent' => 'demo-rockers',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 10, 'quantity' => 100],
                    ['type' => 'F Series Package', 'price' => 40, 'quantity' => 20, 'description' => 'Test drive + bumper sticker'],
                ],
            ],
            [
                'name' => 'Sneed\'s Night (Formerly Chuck\'s)',
                'description' => "Feed and Seed! (Formerly Chuck's)\n\n**What we offer:**\n- Quality feed\n- Premium seed\n- The finest agricultural supplies in Springfield\n\n*Ask about our previous owner's naming convention.*\n\nFamilies welcome. Subtle humor appreciated.",
                'duration' => 3,
                'group' => 'Special Events',
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talent' => 'demo-stonecutters',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 12, 'quantity' => 75],
                ],
            ],
            [
                'name' => "Dr. Nick's Medical Seminar",
                'description' => "Hi, everybody! HI, DR. NICK!\n\n**Tonight's Topics:**\n- The knee bone's connected to the... something\n- \"Call 1-600-DOCTORB. The B is for bargain!\"\n- Inflammable means flammable?!\n- How to identify which organ goes where\n\n*\"The coroner? I'm so sick of that guy!\"*\n\nDisclaimer: Dr. Nick graduated from Hollywood Upstairs Medical College.",
                'duration' => 2,
                'group' => 'Comedy',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0100000', // Monday
                'talent' => 'demo-krusty',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 5, 'quantity' => 100],
                    ['type' => 'Sun and Run Package', 'price' => 15, 'quantity' => 30, 'description' => 'Includes free bandage + dubious medical advice'],
                ],
            ],

            // ===========================
            // BARNEY'S BOWL-A-RAMA EVENTS
            // ===========================
            [
                'name' => 'Pin Pals Bowling Tournament',
                'description' => "Pins pals! Pins pals! We're the Pin Pals!\n\n**Teams Welcome:**\n- Original Pin Pals (Homer, Apu, Moe, Otto)\n- The Holy Rollers\n- The Stereotypes\n- Channel 6 Wastelanders\n\n**Prizes:**\n- First place: Mr. Burns' actual bowling team trophy\n- Last place: You tried (participation ribbon)\n\n*Homer's bowling hand is ready.*",
                'duration' => 4,
                'group' => 'Special Events',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-bowlarama',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '0000100', // Thursday
                'talent' => 'demo-rockers',
                'tickets' => [
                    ['type' => 'Spectator', 'price' => 5, 'quantity' => 100],
                    ['type' => 'Team Entry (4 people)', 'price' => 60, 'quantity' => 8, 'description' => 'Lane reservation + team shirts'],
                ],
            ],
            [
                'name' => '"Beer Baron" Prohibition League Night',
                'description' => "To alcohol! The cause of, and solution to, all of life's problems!\n\n**Tonight's Activities:**\n- Prohibition-era bowling league\n- Secret speakeasy in the back\n- Rex Banner NOT invited\n- \"I am the Beer Baron!\" shouting contest\n\n*\"Listen, rummy, I'm gonna say it plain and simple: Where'd you pinch the hooch?\"*\n\nBowling shoes and bathtub gin included.",
                'duration' => 3,
                'group' => 'Special Events',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-bowlarama',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0000010', // Friday
                'talent' => 'demo-stonecutters',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 80],
                    ['type' => 'Beer Baron Package', 'price' => 45, 'quantity' => 20, 'description' => 'All-you-can-bowl + commemorative mug'],
                ],
            ],
            [
                'name' => '"Two Dozen and One Greyhounds" Dog Show',
                'description' => "See my vest! See my vest! Made from real gorilla chest!\n\n**Event Schedule:**\n- Greyhound racing (no gambling... officially)\n- Santa's Little Helper agility course\n- \"Good dog\" vs \"Bad dog\" competitions\n- Best-dressed pet contest\n\n*No greyhounds will be harmed. Mr. Burns is NOT a judge.*\n\nWarning: Do not leave puppies unattended near wealthy industrialists.",
                'duration' => 3,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-bowlarama',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 14,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-openmic',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 12, 'quantity' => 100],
                    ['type' => 'Dog Entry', 'price' => 20, 'quantity' => 30, 'description' => 'Includes dog treats + bandana'],
                ],
            ],
            [
                'name' => "Homer's Perfect Game Challenge",
                'description' => "WOOHOO! Can you beat Homer's legendary 300 game?\n\n**Challenge Rules:**\n- Bowl your best game\n- Beat 300 (good luck)\n- Win a lifetime supply of donuts\n\n*\"I thought I told you to trim those sideburns!\"*\n\nMystery spots on the alley may or may not help your game.",
                'duration' => 4,
                'group' => 'Special Events',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-bowlarama',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talent' => 'demo-rockers',
                'tickets' => [
                    ['type' => 'Challenger Entry', 'price' => 15, 'quantity' => 40],
                    ['type' => 'Spectator', 'price' => 5, 'quantity' => 100],
                ],
            ],

            // ===========================
            // THE AZTEC THEATER EVENTS
            // ===========================
            [
                'name' => 'Troy McClure Film Festival',
                'description' => "Hi, I'm Troy McClure! You may remember me from such film festivals as \"The Decaffeinated Man vs. The Case of the Missing Coffee\" and \"P is for Psycho.\"\n\n**Tonight's Features:**\n- \"The Contrabulous Fabtraption of Professor Horatio Hufnagel\"\n- \"Dial M for Murderousness\"\n- \"The President's Neck is Missing\"\n- \"Hydro, the Man With the Hydraulic Arms\"\n\n*Get confident, stupid!*",
                'duration' => 4,
                'group' => 'Special Events',
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-aztectheater',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '0000100', // Thursday
                'talent' => 'demo-troymcclure',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 80],
                    ['type' => 'Stop the Planet of the Apes Package', 'price' => 40, 'quantity' => 15, 'description' => 'Includes popcorn + autographed headshot'],
                ],
            ],
            [
                'name' => 'McBain Double Feature',
                'description' => "ICE TO MEET YOU!\n\n**Tonight's Films:**\n- \"McBain: Let's Get Silly\" (1991)\n- \"McBain IV: Fatal Discharge\"\n\n**Featuring:**\n- Commie-nazis getting what they deserve\n- One-liners that don't make sense in context\n- Senator Mendoza's comeuppance\n\n*\"Right now I'm thinking about holding another meeting... IN BED!\"*\n\nFree popcorn. It's showtime.",
                'duration' => 4,
                'group' => 'Special Events',
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-aztectheater',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0000010', // Friday
                'talent' => 'demo-troymcclure',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 12, 'quantity' => 100],
                    ['type' => 'Mendoza Package', 'price' => 30, 'quantity' => 20, 'description' => 'Premium seating + McBain T-shirt'],
                ],
            ],
            [
                'name' => 'Hans Moleman Film Festival',
                'description' => "\"Man Getting Hit by Football\" and other cinematic masterpieces.\n\n**Tonight's Films:**\n- \"Man Getting Hit by Football\" (Academy Award nominee)\n- \"Hans Moleman Productions Presents: Hans Moleman Productions\"\n- \"The Trials of Hans Moleman\"\n\n*\"I was saying Boo-urns!\"*\n\nNote: Hans Moleman is 31 years old. The man never waits.",
                'duration' => 2,
                'group' => 'Comedy',
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-aztectheater',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 17,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-troymcclure',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 8, 'quantity' => 60],
                ],
            ],
            [
                'name' => 'Treehouse of Horror Marathon',
                'description' => "The following program contains scenes of extreme violence and adult content. Viewer discretion is advised.\n\n**Tonight's Episodes:**\n- \"The Shinning\" (No TV and no beer make Homer something something)\n- \"Time and Punishment\" (Don't touch anything!)\n- \"Nightmare Cafeteria\" (Grade F meat)\n- \"Clown Without Pity\" (That doll is evil!)\n\n*\"Quiet, you! Do you want to get sued?!\"*",
                'duration' => 5,
                'group' => 'Special Events',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-aztectheater',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 21,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talents' => ['demo-djbob', 'demo-krusty'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 120],
                    ['type' => 'Krusty Doll Package', 'price' => 35, 'quantity' => 25, 'description' => 'Good/Evil switch included'],
                ],
            ],
            [
                'name' => 'Gabbo Night',
                'description' => "GABBO! GABBO! GABBO!\n\nAll the kids in Springfield are S.O.B.s! (Just kidding... or am I?)\n\n**Tonight's Show:**\n- Gabbo and Arthur Crandall perform\n- \"Look, Smithers! Garbo is coming!\"\n- Special appearance by that 2nd-rate ventriloquist, Krusty\n\n*\"I'm a bad wittle boy!\"*\n\nWarning: May contain phrases that sound like profanity.",
                'duration' => 2,
                'group' => 'Comedy',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-aztectheater',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talent' => 'demo-krusty',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 20, 'quantity' => 100],
                    ['type' => 'Gabbo VIP Package', 'price' => 45, 'quantity' => 25, 'description' => 'Meet Gabbo + photo op (ventriloquist not included)'],
                ],
            ],
            [
                'name' => '"A Burns for All Seasons" Documentary',
                'description' => "Excellent... A cinematic journey through the life of Springfield's most beloved billionaire.\n\n**Documentary Highlights:**\n- Young Burns: The Early Years\n- The Sun-Blocking Machine Incident\n- \"Release the hounds\" supercut\n- Smithers loyalty montage\n\n*\"I'd trade it all for a little more.\"*\n\nFree Spruce Moose model for first 50 attendees.",
                'duration' => 3,
                'group' => 'Special Events',
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-aztectheater',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0010000', // Tuesday
                'talent' => 'demo-stonecutters',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 80],
                    ['type' => 'Burns Package', 'price' => 50, 'quantity' => 10, 'description' => 'Includes \"Excellent\" certificate + tiny violin'],
                ],
            ],
            [
                'name' => 'Radioactive Man Premier Night',
                'description' => "Up and atom! The premiere of Springfield's greatest superhero film!\n\n**Featuring:**\n- Radioactive Man (Rainier Wolfcastle)\n- Fallout Boy tryouts on stage\n- Goggles giveaway (\"My eyes! The goggles do nothing!\")\n- Meet the cast (those who survived production)\n\n*\"Jiminy jilikers!\"*\n\nFilmed on location in Springfield (until the town kicked them out).",
                'duration' => 4,
                'group' => 'Special Events',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-aztectheater',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0000010', // Friday
                'talent' => 'demo-troymcclure',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 120],
                    ['type' => 'Fallout Boy Package', 'price' => 40, 'quantity' => 30, 'description' => 'Includes goggles + radioactive glow stick'],
                ],
            ],
            [
                'name' => '"Who Shot Mr. Burns?" Mystery Screening',
                'description' => "Who shot Mr. Burns? WHO?!\n\n**Interactive Mystery Night:**\n- Watch both parts\n- Submit your guess during intermission\n- Winner gets... nothing (it was Maggie)\n- Clue-finding contest\n\n*\"I'm Mr. Burns, blah blah blah, do this, do that, blah blah blah.\"*\n\nSpoiler alert: It's always who you least suspect.",
                'duration' => 3,
                'group' => 'Special Events',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-aztectheater',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0001000', // Wednesday
                'talent' => 'demo-frink',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 12, 'quantity' => 100],
                    ['type' => 'Detective Package', 'price' => 25, 'quantity' => 30, 'description' => 'Includes magnifying glass + suspect list'],
                ],
            ],

            // ===========================
            // SPRINGFIELD AMPHITHEATER EVENTS
            // ===========================
            [
                'name' => 'Duffapalooza',
                'description' => "The greatest beer festival this side of Shelbyville! Live music, unlimited Duff samples, and good times guaranteed.\n\n**What to expect:**\n- Live performances all night\n- Duff, Duff Lite, and Duff Dry on tap\n- Pork chops and donuts available\n\nD'oh-n't miss it!",
                'duration' => 4,
                'group' => 'Live Music',
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-amphitheater',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '0000010', // Friday
                'talents' => ['demo-rockers', 'demo-djbob', 'demo-krusty'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 20, 'quantity' => 100],
                    ['type' => 'Duff VIP', 'price' => 50, 'quantity' => 20, 'description' => 'Reserved seating with unlimited Duff'],
                ],
            ],
            [
                'name' => 'Battle of the Bands',
                'description' => "Springfield's biggest musical showdown!\n\n**Tonight's Lineup:**\n- School of Rock (starring Otto)\n- Spinal Tap Tribute Band\n- Sadgasm (featuring Homer)\n- The Party Posse\n\nMay the best band win! Voting by applause.",
                'duration' => 4,
                'group' => 'Live Music',
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-amphitheater',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0000010', // Friday
                'talents' => ['demo-rockers', 'demo-lisajazz', 'demo-djbob'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 25, 'quantity' => 150],
                    ['type' => 'Backstage Pass', 'price' => 65, 'quantity' => 30, 'description' => 'Meet the bands + exclusive merch'],
                ],
            ],
            [
                'name' => 'Homerpalooza Festival',
                'description' => "Am I cool, man? YES!\n\n**Festival Lineup:**\n- Smashing Pumpkins\n- Cypress Hill\n- Sonic Youth\n- Peter Frampton\n\n**Plus:**\n- Cannonball to the gut show (safety not guaranteed)\n- \"Getting High\" alternative definition workshop\n\n*\"I used to rock and roll all night and party every day. Then it was every other day...\"*",
                'duration' => 6,
                'group' => 'Live Music',
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-amphitheater',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 14,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talents' => ['demo-rockers', 'demo-djbob'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 45, 'quantity' => 500],
                    ['type' => 'VIP Front Row', 'price' => 120, 'quantity' => 50, 'description' => 'Backstage access + meet the bands'],
                ],
            ],
            [
                'name' => 'Be Sharps Reunion Concert',
                'description' => "Baby on Board! The legendary Be Sharps reunite for one night only!\n\n**Setlist includes:**\n- \"Baby on Board\"\n- \"Goodbye My Coney Island Baby\"\n- Surprise rooftop finale\n\n*\"Barney, you're just like Yoko!\"*\n\nSpecial appearance by George Harrison (hologram pending).",
                'duration' => 3,
                'group' => 'Live Music',
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-amphitheater',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talents' => ['demo-rockers', 'demo-openmic'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 35, 'quantity' => 200],
                    ['type' => 'Barbershop Package', 'price' => 75, 'quantity' => 40, 'description' => 'Includes vintage album + bow tie'],
                ],
            ],
            [
                'name' => 'Springfield Symphony with Lisa',
                'description' => "A night of classical refinement in Springfield!\n\n**Program:**\n- Lisa Simpson: Saxophone Solo\n- Bleeding Gums Murphy Tribute\n- \"Jazzman\" Orchestral Arrangement\n- Beethoven's 5th (the 4th was too short)\n\n*\"I used to think it was just another dead white guy's music...\"*\n\nBlack tie optional. Sax on the beach definitely included.",
                'duration' => 3,
                'group' => 'Live Music',
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-amphitheater',
                'image' => 'demo_flyer_jazz.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-lisajazz',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 30, 'quantity' => 150],
                    ['type' => 'Orchestra Seating', 'price' => 60, 'quantity' => 50, 'description' => 'Premium seats + program book'],
                ],
            ],
            [
                'name' => "New Year's Eve: Springfield Countdown",
                'description' => "Ring in the new year Springfield style! Live music, DJ after midnight, Duff toast, party favors, and the best view of the tire fire!\n\n**Package includes:**\n- Open Duff bar 9 PM - 2 AM\n- Krusty Burger appetizers\n- Duff toast at midnight\n- Party favors (Itchy & Scratchy themed)\n\nHappy New Year! Don't have a cow, man!",
                'duration' => 6,
                'group' => 'Special Events',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-amphitheater',
                'image' => 'demo_flyer_special.jpg',
                'days_of_week' => null, // One-time event
                'talents' => ['demo-djbob', 'demo-rockers', 'demo-krusty'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 75, 'quantity' => 200],
                    ['type' => 'VIP Package', 'price' => 150, 'quantity' => 50, 'description' => 'Premium open bar + Mr. Burns private lounge'],
                ],
            ],

            // ===========================
            // LARD LAD DONUTS EVENTS
            // ===========================
            [
                'name' => 'Lard Lad Donut Eating Contest',
                'description' => "Mmm... donuts. Homer Simpson's record: 47 donuts in one sitting.\n\n**Rules:**\n- No hands for the first round\n- Pink frosted sprinkled donuts only\n- \"I can't believe I ate the whole thing\" must be said afterward\n\n*\"Donuts. Is there anything they can't do?\"*\n\nWinner receives a golden Lard Lad statue.",
                'duration' => 2,
                'group' => 'Special Events',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-lardlad',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 15,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talent' => 'demo-krusty',
                'tickets' => [
                    ['type' => 'Spectator', 'price' => 5, 'quantity' => 100],
                    ['type' => 'Competitor Entry', 'price' => 20, 'quantity' => 15, 'description' => 'Includes warm-up donut + antacids'],
                ],
            ],
            [
                'name' => 'Tomacco Tasting Night',
                'description' => "It tastes like grandma! I want more!\n\n**Try Homer's controversial hybrid crop:**\n- Pure tomacco samples\n- Tomacco juice\n- Tomacco salsa (highly addictive)\n\n**Warning:** Product may be highly addictive. Animals will break down fences to obtain it.\n\n*Tobacco company executives NOT invited.*\n\nLaramie sponsorship pending.",
                'duration' => 2,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-lardlad',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0000100', // Thursday
                'talent' => 'demo-frink',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 50],
                    ['type' => 'Farmer Package', 'price' => 30, 'quantity' => 15, 'description' => 'Take home tomacco seeds (results may vary)'],
                ],
            ],
            [
                'name' => 'Le Grille BBQ Cookoff',
                'description' => "What the hell is that?! It's our weekly BBQ competition!\n\n**Featuring:**\n- Homer's patented \"moon waffles\"\n- Hibachi grilling (assembly instructions NOT included)\n- Prizes for best burnt offerings\n\n*\"Why must I fail at every attempt at masonry?!\"*\n\nBring your own tongs. Frustration included.",
                'duration' => 4,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-lardlad',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 17,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-openmic',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 12, 'quantity' => 80],
                    ['type' => 'Competitor Entry', 'price' => 25, 'quantity' => 20, 'description' => 'Includes grill station + propane'],
                ],
            ],
            [
                'name' => 'Steamed Hams Cooking Class',
                'description' => "Well, Seymour, you are an odd fellow, but I must say... you steam a good ham.\n\n**Tonight's Menu:**\n- Steamed Hams (obviously grilled)\n- Aurora Borealis Flambé\n- Superintendent's Surprise\n\n**Note:** Kitchen fire extinguishers have been serviced.\n\n*May I see it?* No.",
                'duration' => 2,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-lardlad',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '0010000', // Tuesday
                'talent' => 'demo-frink',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 35, 'quantity' => 30],
                    ['type' => 'Albany Package', 'price' => 60, 'quantity' => 10, 'description' => 'Includes apron + \"It\'s a regional dialect\" certificate'],
                ],
            ],

            // ===========================
            // SPRINGFIELD COMMUNITY CENTER EVENTS
            // ===========================
            [
                'name' => "Talent Show: Springfield's Got Talent",
                'description' => "Springfield's finest showcase their hidden talents!\n\n**Expected Performances:**\n- Mr. Burns: Juggling (with hounds)\n- Hans Moleman: \"Football in the Groin\" reenactment\n- Comic Book Guy: Worst. Performance. Ever.\n- Ralph Wiggum: TBD (probably glue-related)\n\nJudges: Mayor Quimby, Krusty, Lisa Simpson",
                'duration' => 3,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 14,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talents' => ['demo-krusty', 'demo-openmic', 'demo-lisajazz'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 120],
                    ['type' => 'Judges Table', 'price' => 40, 'quantity' => 15, 'description' => 'Best seats + voting privileges'],
                ],
            ],
            [
                'name' => 'The Stonecutters Secret Show',
                'description' => "Who controls the British crown? Who keeps the metric system down? WE DO! WE DO!\n\nExclusive members-only event. Sacred Parchment required for entry.\n\n**Number 908 (Homer) NOT invited.**\n\nRemember: We do! We do!",
                'duration' => 4,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talents' => ['demo-stonecutters', 'demo-rockers'],
                'tickets' => [
                    ['type' => 'Stonecutter Member', 'price' => 25, 'quantity' => 150],
                    ['type' => 'Stone of Triumph Package', 'price' => 60, 'quantity' => 25, 'description' => 'Exclusive robes + sacred parchment'],
                ],
            ],
            [
                'name' => 'S-M-R-T Spelling Bee',
                'description' => "I am so smart! S-M-R-T! I mean S-M-A-R-T!\n\n**Compete in Springfield's dumbest smart competition:**\n- Words like \"be\" and \"cat\" accepted\n- Homer Simpson as guest judge\n- Prize: A feeling of adequacy\n\n*Sponsored by Springfield Elementary's No Child Left Behind Program*\n\nWinner gets to say \"I am so smart!\" on stage.",
                'duration' => 2,
                'group' => 'Comedy',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 18,
                'minute' => 30,
                'days_of_week' => '0001000', // Wednesday
                'talent' => 'demo-frink',
                'tickets' => [
                    ['type' => 'Spectator', 'price' => 8, 'quantity' => 80],
                    ['type' => 'Speller Entry', 'price' => 12, 'quantity' => 20],
                ],
            ],
            [
                'name' => 'Bingo with Abe Simpson',
                'description' => "Back in my day, we had to walk 15 miles in the snow just to play bingo! And we liked it!\n\n**Hosted by Abraham \"Grampa\" Simpson**\n\nExpect long stories about:\n- The time he wore an onion on his belt (which was the style at the time)\n- Shelbyville and their speed holes\n- \"Dear Mr. President, there are too many states nowadays\"\n\n*\"Old Man Yells At Bingo Card\"*",
                'duration' => 2,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 14,
                'minute' => 0,
                'days_of_week' => '0010000', // Tuesday
                'talent' => 'demo-openmic',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 8, 'quantity' => 50],
                    ['type' => 'Retirement Castle Package', 'price' => 15, 'quantity' => 20, 'description' => '3 bingo cards + pudding cup'],
                ],
            ],
            [
                'name' => 'Do It For Her Night',
                'description' => "\"DON'T FORGET: YOU'RE HERE FOREVER\" covered by Maggie's photos.\n\n**A heartwarming evening:**\n- Photo collage workshop\n- Motivational sign-making\n- \"Do It For Her\" templates provided\n- Onions available for crying purposes\n\n*This is the most emotional room at the nuclear plant.*\n\nMaggie appearances not guaranteed but highly probable.",
                'duration' => 2,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-openmic',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 10, 'quantity' => 60],
                    ['type' => 'Family Package', 'price' => 25, 'quantity' => 20, 'description' => 'Includes photo frame + tissue box'],
                ],
            ],
            [
                'name' => '"Monorail!" Town Meeting',
                'description' => "Monorail! Monorail! MONORAIL!\n\n**Agenda:**\n- Should Springfield get a monorail? (Spoiler: Yes)\n- Lyle Lanley presentation\n- \"But Main Street's still all cracked and broken!\" rebuttals\n- Singing encouraged\n\n*\"I've sold monorails to Brockway, Ogdenville, and North Haverbrook!\"*\n\nMarge's concerns will be politely ignored.",
                'duration' => 2,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0100000', // Monday
                'talent' => 'demo-openmic',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 0, 'quantity' => 200],
                ],
            ],
            [
                'name' => 'Radioactive Man Auditions',
                'description' => "Jiminy jilikers! Open auditions for the next Fallout Boy!\n\n**Requirements:**\n- Must be able to say \"Jiminy jilikers!\"\n- Radiation resistance preferred\n- No Milhouse (he didn't say it right)\n\n*\"Up and atom!\" \"Up and at them!\"*\n\nGoggles will be provided. They do nothing.",
                'duration' => 4,
                'group' => 'Special Events',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 10,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talent' => 'demo-troymcclure',
                'tickets' => [
                    ['type' => 'Audition Entry', 'price' => 10, 'quantity' => 100],
                    ['type' => 'Spectator', 'price' => 5, 'quantity' => 50],
                ],
            ],
            [
                'name' => 'Springfield A&M College Fair',
                'description' => "Clown College! Bovine University! Hollywood Upstairs Medical College!\n\n**Participating Schools:**\n- Springfield A&M (Go Cow!)\n- Bovine University\n- Clown College\n- Hollywood Upstairs Medical College\n- Springfield Heights Institute of Technology\n\n*\"I call it Billy and the Cloneasaurus!\"*",
                'duration' => 4,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 10,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-frink',
                'tickets' => [
                    ['type' => 'Free Entry', 'price' => 0, 'quantity' => 200],
                ],
            ],
            [
                'name' => "Mr. Burns' Retirement Planning",
                'description' => "Excellent... Learn the secrets of financial immortality from Springfield's oldest billionaire.\n\n**Topics Covered:**\n- How to live forever (almost)\n- Releasing the hounds on your competitors\n- \"Ah yes, I remember when a candy bar cost a nickel\"\n- Sun-blocking as an investment strategy\n\n*Smithers will be taking attendance.*",
                'duration' => 2,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 18,
                'minute' => 30,
                'days_of_week' => '0100000', // Monday
                'talent' => 'demo-stonecutters',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 50],
                    ['type' => 'Burns Package', 'price' => 100, 'quantity' => 5, 'description' => 'Private consultation + \"Excellent\" photo op'],
                ],
            ],
            [
                'name' => 'I Choo-Choo-Choose You Speed Dating',
                'description' => "Let's bee friends! Springfield's premier singles event for lonely hearts.\n\n**How it works:**\n- 5 minutes per date\n- Free conversation hearts\n- Ralph Wiggum NOT in attendance (restraining order)\n\n*\"You choo-choo-choose me?\"*\n\nNote: Please do not give valentines to anyone whose cat's breath smells like cat food.",
                'duration' => 3,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0001000', // Wednesday
                'talent' => 'demo-openmic',
                'tickets' => [
                    ['type' => 'Singles Entry', 'price' => 25, 'quantity' => 50],
                    ['type' => 'Milhouse Package', 'price' => 40, 'quantity' => 15, 'description' => 'Guaranteed second date (not really)'],
                ],
            ],
            [
                'name' => 'Max Power Networking Night',
                'description' => "Nobody snuggles with Max Power. You strap yourself in and FEEL THE Gs!\n\n**Network with Springfield's Elite:**\n- Learn how to change your name to something cool\n- Meet Trent Steel and other successful people\n- Power handshakes workshop\n\n*Names inspired by hair dryers welcome.*\n\nMax Power: He's the man whose name you'd love to touch!",
                'duration' => 2.5,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 19,
                'minute' => 30,
                'days_of_week' => '0010000', // Tuesday
                'talent' => 'demo-krusty',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 20, 'quantity' => 75],
                    ['type' => 'Power Package', 'price' => 45, 'quantity' => 25, 'description' => 'Premium name tag + business cards'],
                ],
            ],
            [
                'name' => "Everything's Coming Up Milhouse",
                'description' => "A celebration of mediocrity! Finally, everything's coming up Milhouse!\n\n**Tonight's Festivities:**\n- Thrillho video game tournament\n- \"My mom says I'm cool\" affirmation station\n- Vaseline-based hair styling tips\n- Purple fruit drinks\n\n*\"My feet are soaked, but my cuffs are bone dry!\"*\n\nRemember: Nobody likes Milhouse! (But tonight we do)",
                'duration' => 3,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-openmic',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 10, 'quantity' => 80],
                    ['type' => 'Thrillho Package', 'price' => 25, 'quantity' => 20, 'description' => 'Includes race car bed photo op'],
                ],
            ],
            [
                'name' => 'Inanimate Carbon Rod Appreciation Night',
                'description' => "IN ROD WE TRUST!\n\nCelebrate the hero that saved the Corvair space shuttle!\n\n**Tonight's Program:**\n- Documentary screening: \"Rod: The Movie\"\n- Meet and greet with THE Rod\n- Time Magazine \"Inanimate Object of the Year\" ceremony\n\n*\"It's a door! Use it!\"*\n\nHomer Simpson NOT invited (still bitter).",
                'duration' => 2,
                'group' => 'Special Events',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '0000100', // Thursday
                'talent' => 'demo-frink',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 10, 'quantity' => 75],
                    ['type' => 'Rod VIP Package', 'price' => 25, 'quantity' => 20, 'description' => 'Photo with the Rod + commemorative pin'],
                ],
            ],
            [
                'name' => 'Poochie Memorial Night',
                'description' => "I have to go now. My planet needs me.\n\n*Note: Poochie died on the way back to his home planet.*\n\n**Tonight's Tribute:**\n- Screening of Poochie's only episode\n- \"Poochitize me, Cap'n!\" drink specials\n- Rastafarian surfer costume contest\n- Roy appearance (maybe)\n\n*\"When are they gonna get to the fireworks factory?!\"*\n\nCute! But I want a dog, not Fonzie in a dog suit.",
                'duration' => 2,
                'group' => 'Special Events',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0001000', // Wednesday
                'talent' => 'demo-troymcclure',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 8, 'quantity' => 80],
                ],
            ],
            [
                'name' => 'Purple Monkey Dishwasher',
                'description' => "By the end of the meeting, the sentence \"we'll be negotiating our own contract\" became \"purple monkey dishwasher.\"\n\n**Tonight's Game:**\n- Championship telephone game tournament\n- Teams of 10\n- Prizes for most creative misinterpretations\n- Lenny and Carl as referees\n\n*\"We're sorry the teachers won't come back until you rehire Principal Skinner purple monkey dishwasher.\"*",
                'duration' => 2,
                'group' => 'Comedy',
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0010000', // Tuesday
                'talent' => 'demo-frink',
                'tickets' => [
                    ['type' => 'Player Entry', 'price' => 10, 'quantity' => 60],
                    ['type' => 'Team Registration (10 people)', 'price' => 80, 'quantity' => 6],
                ],
            ],
            [
                'name' => 'Comic Book Guy\'s Worst. Event. Ever.',
                'description' => "Ooh, a sarcasm detector. That's a REAL useful invention.\n\n**Tonight's Activities:**\n- Worst costume contest (intentionally bad)\n- Trivia: Name characters who've died and returned\n- \"There is no emoticon for what I am feeling\" workshop\n- Tacobell dog memorial\n\n*\"I've wasted my life.\"*\n\nLoneliest guy in the world seeks attendees.",
                'duration' => 3,
                'group' => 'Comedy',
                'curator_group' => 'Entertainment',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '0010000', // Tuesday
                'talent' => 'demo-troymcclure',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 12, 'quantity' => 80],
                    ['type' => 'Collector Package', 'price' => 35, 'quantity' => 20, 'description' => 'Includes limited edition trading card'],
                ],
            ],
        ];
    }

    /**
     * Create schedules that the demo user follows (external to the curator)
     * These demonstrate the "follow" feature with rivalry-themed events
     */
    protected function createFollowedSchedules(User $user): void
    {
        $schedules = [
            [
                'name' => 'Shelbyville Stadium',
                'subdomain' => 'demo-shelbyville',
                'type' => 'venue',
                'city' => 'Shelbyville',
                'country_code' => 'US',
                'background_colors' => '#8B0000, #FFD700', // Rival maroon/gold
                'accent_color' => '#FFD700',
                'events' => [
                    [
                        'name' => 'Shelbyville vs Springfield Football',
                        'description' => "The big game! Shelbyville takes on Springfield in the annual rivalry match.\n\n*\"Shake harder, boy!\"*\n\nNote: Lemon tree security has been increased.",
                        'days_offset' => 7,
                        'price' => 35,
                    ],
                    [
                        'name' => '"Their Lemon Tree is Ours!" Rally',
                        'description' => "Shelbyville's annual celebration of that time they stole Springfield's lemon tree.\n\n**Activities:**\n- Lemon-themed refreshments\n- \"Shake harder, boy!\" reenactments\n- Springfield mockery contest\n\n*We are the original Springfield!*",
                        'days_offset' => 14,
                        'price' => 10,
                    ],
                    [
                        'name' => 'Shelbyville Speedway Race',
                        'description' => "High-speed racing action at Shelbyville's premier speedway!\n\n*Shelbyville does everything Springfield does, but better.*\n\nCousin dating welcome (it's legal here).",
                        'days_offset' => 21,
                        'price' => 25,
                    ],
                ],
            ],
            [
                'name' => 'Capital City Arena',
                'subdomain' => 'demo-capitalcity',
                'type' => 'venue',
                'city' => 'Capital City',
                'country_code' => 'US',
                'background_colors' => '#4B0082, #C0C0C0', // Fancy city purple/silver
                'accent_color' => '#C0C0C0',
                'events' => [
                    [
                        'name' => 'Capital City Goofball Game',
                        'description' => "Watch the Capital City Goofballs take on their rivals!\n\n**Stadium Features:**\n- Actual good food (not Krusty Burger)\n- Clean restrooms\n- Parking that exists\n\n*The big city does it better!*",
                        'days_offset' => 5,
                        'price' => 45,
                    ],
                    [
                        'name' => 'Lurleen Lumpkin Concert',
                        'description' => "Bunk with Me Tonight! Country star Lurleen Lumpkin performs live!\n\n**Setlist includes:**\n- \"Bunk with Me Tonight\"\n- \"I'm Basting a Turkey with My Tears\"\n- \"Don't Look Up My Dress Unless You Mean It\"\n\n*Colonel Homer approved.*",
                        'days_offset' => 12,
                        'price' => 55,
                    ],
                    [
                        'name' => '"Capital City" (That Song) Dance Night',
                        'description' => "Capital City! Yeah! It's a helluva town!\n\n**Tonight's Entertainment:**\n- Live performance of the Capital City song\n- Dancing all night\n- Sophistication that Springfield lacks\n\n*\"It's a city that's got stuff!\"*",
                        'days_offset' => 19,
                        'price' => 30,
                    ],
                ],
            ],
        ];

        foreach ($schedules as $scheduleData) {
            // Create the role
            $role = new Role;
            $role->user_id = $user->id;
            $role->subdomain = $scheduleData['subdomain'];
            $role->type = $scheduleData['type'];
            $role->name = $scheduleData['name'];
            $role->email = self::DEMO_EMAIL;
            $role->email_verified_at = now();
            $role->language_code = 'en';
            $role->timezone = 'America/New_York';
            $role->city = $scheduleData['city'];
            $role->country_code = $scheduleData['country_code'];
            $role->background = 'gradient';
            $role->background_colors = $scheduleData['background_colors'];
            $role->accent_color = $scheduleData['accent_color'];
            $role->plan_type = 'pro';
            $role->plan_expires = now()->addYear()->format('Y-m-d');
            $role->save();

            // Attach demo user as follower
            $role->users()->attach($user->id, ['level' => 'follower']);

            // Create events for this schedule
            $now = Carbon::now($role->timezone);
            foreach ($scheduleData['events'] as $eventInfo) {
                $eventDate = $now->copy()->addDays($eventInfo['days_offset'])
                    ->setHour(rand(19, 21))
                    ->setMinute(0)
                    ->setSecond(0);

                $event = new Event;
                $event->user_id = $user->id;
                $event->creator_role_id = $role->id;
                $event->name = $eventInfo['name'];
                $event->description = $eventInfo['description'] ?? 'Join us for an amazing event!';
                $event->starts_at = $eventDate->utc();
                $event->duration = rand(2, 4);
                $event->slug = Str::slug($eventInfo['name']);
                $event->tickets_enabled = true;
                $event->ticket_currency_code = 'USD';
                $event->save();

                // Attach event to role
                $role->events()->attach($event->id, ['is_accepted' => true]);

                // Create ticket
                Ticket::create([
                    'event_id' => $event->id,
                    'type' => 'General Admission',
                    'price' => $eventInfo['price'],
                    'quantity' => 100,
                ]);
            }
        }
    }

    /**
     * Create ticket purchases for the demo user
     * Only purchases tickets from followed external schedules (not curator venues)
     */
    protected function createUserTicketPurchases(User $user): void
    {
        // Get events from followed schedules only (Shelbyville & Capital City)
        $followedSubdomains = ['demo-shelbyville', 'demo-capitalcity'];
        $followedRoles = Role::whereIn('subdomain', $followedSubdomains)->get();

        foreach ($followedRoles as $role) {
            $events = $role->events;

            foreach ($events as $event) {
                // 70% chance of having purchased tickets to each event
                if (rand(1, 100) > 70) {
                    continue;
                }

                $ticket = $event->tickets->first();
                if (! $ticket) {
                    continue;
                }

                $eventDate = Carbon::parse($event->starts_at)->format('Y-m-d');
                $quantity = rand(1, 2);
                $totalAmount = $ticket->price * $quantity;

                $sale = Sale::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'event_date' => $eventDate,
                    'subdomain' => $role->subdomain,
                    'status' => 'paid',
                    'payment_method' => 'stripe',
                    'payment_amount' => $totalAmount,
                    'transaction_reference' => __('messages.manual_payment'),
                    'secret' => Str::random(32),
                ]);

                SaleTicket::create([
                    'sale_id' => $sale->id,
                    'ticket_id' => $ticket->id,
                    'quantity' => $quantity,
                    'seats' => json_encode(array_fill(1, $quantity, null)),
                ]);
                // Note: ticket sold count is automatically updated via SaleTicket::booted()
            }
        }
    }

    /**
     * Create analytics data for the demo role
     */
    protected function createAnalyticsData(Role $role): void
    {
        $now = Carbon::now();

        // Generate 365 days of analytics data
        for ($daysAgo = 365; $daysAgo >= 0; $daysAgo--) {
            $date = $now->copy()->subDays($daysAgo);

            // Base views increase over time (growth trend)
            $growthFactor = 1 + (365 - $daysAgo) / 365; // 1.0 to 2.0

            // Weekend boost (Friday, Saturday, Sunday)
            $dayOfWeek = $date->dayOfWeek;
            $weekendMultiplier = in_array($dayOfWeek, [0, 5, 6]) ? 1.5 : 1.0;

            // Random daily variation
            $randomVariation = 0.7 + (rand(0, 60) / 100); // 0.7 to 1.3

            // Calculate base daily views (targeting ~50-70 views per day on average)
            $baseViews = 40 * $growthFactor * $weekendMultiplier * $randomVariation;

            // Device breakdown: ~50% mobile, ~35% desktop, ~15% tablet
            $mobileViews = (int) round($baseViews * 0.50 * (0.9 + rand(0, 20) / 100));
            $desktopViews = (int) round($baseViews * 0.35 * (0.9 + rand(0, 20) / 100));
            $tabletViews = (int) round($baseViews * 0.12 * (0.9 + rand(0, 20) / 100));
            $unknownViews = (int) round($baseViews * 0.03 * (0.9 + rand(0, 20) / 100));

            // Only create record if there are views
            if ($mobileViews + $desktopViews + $tabletViews + $unknownViews > 0) {
                AnalyticsDaily::updateOrCreate(
                    ['role_id' => $role->id, 'date' => $date->toDateString()],
                    [
                        'desktop_views' => $desktopViews,
                        'mobile_views' => $mobileViews,
                        'tablet_views' => $tabletViews,
                        'unknown_views' => $unknownViews,
                    ]
                );
            }

            // Create referrer data
            $this->createReferrerDataForDay($role, $date, $baseViews);
        }

        // Create event-specific analytics for past events
        $this->createEventAnalyticsData($role);
    }

    /**
     * Create referrer data for a specific day
     */
    protected function createReferrerDataForDay(Role $role, Carbon $date, float $baseViews): void
    {
        $sources = [
            ['source' => 'direct', 'domain' => '', 'weight' => 40],
            ['source' => 'social', 'domain' => 'instagram.com', 'weight' => 15],
            ['source' => 'social', 'domain' => 'facebook.com', 'weight' => 10],
            ['source' => 'search', 'domain' => 'google.com', 'weight' => 20],
            ['source' => 'other', 'domain' => 'linktr.ee', 'weight' => 8],
            ['source' => 'email', 'domain' => 'mail.google.com', 'weight' => 5],
            ['source' => 'social', 'domain' => 't.co', 'weight' => 2],
        ];

        foreach ($sources as $sourceData) {
            // Calculate views for this source based on weight
            $sourceViews = (int) round($baseViews * ($sourceData['weight'] / 100) * (0.8 + rand(0, 40) / 100));

            if ($sourceViews > 0) {
                AnalyticsReferrersDaily::updateOrCreate(
                    ['role_id' => $role->id, 'date' => $date->toDateString(), 'source' => $sourceData['source'], 'domain' => $sourceData['domain']],
                    ['views' => $sourceViews]
                );
            }
        }
    }

    /**
     * Create event-specific analytics data
     */
    protected function createEventAnalyticsData(Role $role): void
    {
        $now = Carbon::now();
        $events = $role->events;

        foreach ($events as $event) {
            $eventDate = Carbon::parse($event->starts_at);

            // Generate views for 30 days before the event (or from event creation if more recent)
            $startDate = $eventDate->copy()->subDays(30);
            if ($startDate->isFuture()) {
                $startDate = $now->copy()->subDays(7);
            }

            $endDate = min($eventDate->copy()->addDays(1), $now);

            // Skip if event is in the future
            if ($startDate->gt($now)) {
                continue;
            }

            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate) && $currentDate->lte($now)) {
                // Views increase as event approaches
                $daysUntilEvent = $currentDate->diffInDays($eventDate, false);
                $proximityBoost = max(1, 3 - abs($daysUntilEvent) / 10);

                $baseViews = rand(5, 20) * $proximityBoost;

                // Device breakdown
                $mobileViews = (int) round($baseViews * 0.55);
                $desktopViews = (int) round($baseViews * 0.35);
                $tabletViews = (int) round($baseViews * 0.10);

                // Sales data (only for past events and days before the event)
                $salesCount = 0;
                $revenue = 0;

                if ($daysUntilEvent > 0 && $daysUntilEvent <= 21) {
                    // Higher chance of sales in the 3 weeks before event
                    if (rand(1, 100) <= 60) {
                        $salesCount = rand(1, 4);
                        $ticket = $event->tickets->first();
                        if ($ticket) {
                            $revenue = $salesCount * $ticket->price * rand(1, 2);
                        }
                    }
                }

                AnalyticsEventsDaily::updateOrCreate(
                    ['event_id' => $event->id, 'date' => $currentDate->toDateString()],
                    [
                        'desktop_views' => $desktopViews,
                        'mobile_views' => $mobileViews,
                        'tablet_views' => $tabletViews,
                        'unknown_views' => 0,
                        'sales_count' => $salesCount,
                        'revenue' => $revenue,
                    ]
                );

                $currentDate->addDay();
            }
        }
    }
}
