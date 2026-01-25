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
     * Demo role subdomain (the actual schedule)
     */
    public const DEMO_ROLE_SUBDOMAIN = 'moestavern';

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
     * Get or create the demo role
     */
    public function getOrCreateDemoRole(User $user): Role
    {
        $role = Role::where('subdomain', self::DEMO_ROLE_SUBDOMAIN)->first();

        if (! $role) {
            $role = new Role;
            $role->user_id = $user->id;
            $role->subdomain = self::DEMO_ROLE_SUBDOMAIN;
            $role->type = 'venue';
            $role->name = "Moe's Tavern";
            $role->email = self::DEMO_EMAIL;
            $role->email_verified_at = now();
            $role->language_code = 'en';
            $role->timezone = 'America/New_York';
            $role->city = 'Springfield';
            $role->country_code = 'US';
            $role->background = 'gradient';
            $role->background_colors = '#D4A017, #8B4513';
            $role->accent_color = '#4E81FA';
            $role->header_image = 'Chill_Evening';
            $role->accept_requests = true;
            $role->plan_type = 'pro';
            $role->plan_expires = now()->addYear()->format('Y-m-d');
            $role->trial_ends_at = now()->addYear();
            $role->profile_image_url = 'demo_profile_band.jpg';
            $role->save();

            // Attach user to role as owner
            $role->users()->attach($user->id, ['level' => 'owner']);
        }

        return $role;
    }

    /**
     * Populate demo data for a role
     */
    public function populateDemoData(Role $role): void
    {
        $user = $role->user;

        // Download demo images if they don't exist
        $this->downloadDemoImages();

        // Create demo talent roles with themed gradients
        $this->createDemoTalents($user);

        // Create groups
        $groups = $this->createGroups($role);

        // Create events with tickets
        $this->createEvents($role, $user, $groups);

        // Create followed schedules for the demo user
        $this->createFollowedSchedules($user);

        // Create ticket purchases for the demo user
        $this->createUserTicketPurchases($user);

        // Create analytics data for the demo role
        $this->createAnalyticsData($role);
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
            $role->save();

            // Attach user to role as owner (so it's "claimed")
            $role->users()->attach($user->id, ['level' => 'owner']);
        }
    }

    /**
     * Reset demo data - deletes all demo data and repopulates
     */
    public function resetDemoData(Role $role): void
    {
        DB::transaction(function () use ($role) {
            $user = $role->user;

            // Delete sales and sale tickets for events owned by this role
            $eventIds = $role->events()->pluck('events.id');

            SaleTicket::whereIn('sale_id', function ($query) use ($eventIds) {
                $query->select('id')
                    ->from('sales')
                    ->whereIn('event_id', $eventIds);
            })->delete();

            Sale::whereIn('event_id', $eventIds)->delete();

            // Delete tickets for events
            Ticket::whereIn('event_id', $eventIds)->delete();

            // Detach events from role and delete events created by this role
            $role->events()->detach();
            Event::where('creator_role_id', $role->id)->delete();

            // Delete groups
            Group::where('role_id', $role->id)->delete();

            // Delete analytics data for the demo role
            AnalyticsDaily::where('role_id', $role->id)->delete();
            AnalyticsEventsDaily::whereIn('event_id', $eventIds)->delete();
            AnalyticsReferrersDaily::where('role_id', $role->id)->delete();

            // Delete demo user's ticket purchases (sales where user_id is demo user)
            if ($user) {
                $userSaleIds = Sale::where('user_id', $user->id)->pluck('id');
                SaleTicket::whereIn('sale_id', $userSaleIds)->delete();
                Sale::where('user_id', $user->id)->delete();
            }

            // Delete followed schedules (roles with subdomain starting with 'demo-')
            $followedRoles = Role::where('subdomain', 'like', 'demo-%')->get();
            foreach ($followedRoles as $followedRole) {
                // Delete events and related data for the followed role
                $followedEventIds = $followedRole->events()->pluck('events.id');

                SaleTicket::whereIn('sale_id', function ($query) use ($followedEventIds) {
                    $query->select('id')
                        ->from('sales')
                        ->whereIn('event_id', $followedEventIds);
                })->delete();

                Sale::whereIn('event_id', $followedEventIds)->delete();
                Ticket::whereIn('event_id', $followedEventIds)->delete();
                $followedRole->events()->detach();
                Event::where('creator_role_id', $followedRole->id)->delete();
                Group::where('role_id', $followedRole->id)->delete();

                // Detach users and delete the role
                $followedRole->users()->detach();
                $followedRole->delete();
            }

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
     */
    protected function createEvents(Role $role, User $user, array $groups): void
    {
        $events = $this->getEventTemplates();
        // Use fixed start date of January 1, 2026 for demo data
        $startDate = Carbon::create(2026, 1, 1, 0, 0, 0, $role->timezone);

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

            // Create event
            $event = new Event;
            $event->user_id = $user->id;
            $event->creator_role_id = $role->id;
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

            // Attach to role with group
            $groupName = $eventData['group'];
            $group = $groups[$groupName] ?? null;
            $role->events()->attach($event->id, [
                'is_accepted' => true,
                'group_id' => $group?->id,
            ]);

            // Attach talent role if specified
            if (! empty($eventData['talent'])) {
                $talentRole = Role::where('subdomain', $eventData['talent'])->first();
                if ($talentRole) {
                    $talentRole->events()->attach($event->id, ['is_accepted' => true]);
                }
            }

            // Create tickets
            $this->createTicketsForEvent($event, $eventData['tickets']);

            // Create sample sales for recurring events (they have past occurrences)
            if ($isRecurring) {
                $this->createSalesForEvent($event, $role);
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
     */
    protected function getEventTemplates(): array
    {
        return [
            // ===== SUNDAY EVENTS (3) =====
            [
                'name' => 'Jazz Night with Bleeding Gums Murphy',
                'description' => "A tribute to Springfield's greatest jazz musician. Lisa Simpson and friends perform classic Bleeding Gums Murphy hits.\n\n**Featuring:**\n- \"Sax on the Beach\"\n- \"Jazzman\"\n- And more smooth saxophone\n\nBring your tissues. This one's emotional.",
                'duration' => 3,
                'group' => 'Live Music',
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
                'name' => "Talent Show: Springfield's Got Talent",
                'description' => "Springfield's finest showcase their hidden talents!\n\n**Expected Performances:**\n- Mr. Burns: Juggling (with hounds)\n- Hans Moleman: \"Football in the Groin\" reenactment\n- Comic Book Guy: Worst. Performance. Ever.\n- Ralph Wiggum: TBD (probably glue-related)\n\nJudges: Mayor Quimby, Krusty, Lisa Simpson",
                'duration' => 3,
                'group' => 'Special Events',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 14,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-krusty',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 120],
                    ['type' => 'Judges Table', 'price' => 40, 'quantity' => 15, 'description' => 'Best seats + voting privileges'],
                ],
            ],
            [
                'name' => 'Le Grille BBQ Cookoff',
                'description' => "What the hell is that?! It's our weekly BBQ competition!\n\n**Featuring:**\n- Homer's patented \"moon waffles\"\n- Hibachi grilling (assembly instructions NOT included)\n- Prizes for best burnt offerings\n\n*\"Why must I fail at every attempt at masonry?!\"*\n\nBring your own tongs. Frustration included.",
                'duration' => 4,
                'group' => 'Special Events',
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

            // ===== MONDAY EVENTS (3) =====
            [
                'name' => 'Monorail Karaoke Night',
                'description' => "I've sold monorails to Brockway, Ogdenville, and North Haverbrook, and by gum, it put them on the map!\n\n**Tonight's Setlist:**\n- \"Monorail\" (mandatory opener)\n- \"We Put the Spring in Springfield\"\n- \"See My Vest\"\n- \"Who Needs the Kwik-E-Mart\"\n\nIs there a chance the track could bend? Not on your life, my Hindu friend!",
                'duration' => 3,
                'group' => 'Open Mic',
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
                'name' => "Mr. Burns' Retirement Planning",
                'description' => "Excellent... Learn the secrets of financial immortality from Springfield's oldest billionaire.\n\n**Topics Covered:**\n- How to live forever (almost)\n- Releasing the hounds on your competitors\n- \"Ah yes, I remember when a candy bar cost a nickel\"\n- Sun-blocking as an investment strategy\n\n*Smithers will be taking attendance.*",
                'duration' => 2,
                'group' => 'Special Events',
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
                'name' => 'Isotopes Game Watch Party',
                'description' => "Go Isotopes! Watch Springfield's beloved baseball team on the big screen!\n\n**Specials:**\n- $3 hot dogs (made from 100% Grade-F meat)\n- $2 Duffs during innings 1-3\n- Free nachos when Isotopes score\n\n*Warning: Team may relocate to Albuquerque at any moment.*\n\nRemember: Dancin' Homer appearances are NOT guaranteed.",
                'duration' => 3,
                'group' => 'Special Events',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0100000', // Monday
                'talent' => 'demo-rockers',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 5, 'quantity' => 150],
                ],
            ],

            // ===== TUESDAY EVENTS (3) =====
            [
                'name' => "Poetry Slam: Moe's Haiku Hour",
                'description' => "Words that move you... to tears. Competitive spoken word poetry featuring Springfield's most melancholic verses.\n\n**Hosted by Moe Szyslak**\n\nSample:\n*\"My life is empty\nNo one calls, the bar is dead\nPass the rat poison\"*\n\nTissues provided. Bring your sad poems.",
                'duration' => 2.5,
                'group' => 'Open Mic',
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
                'name' => 'Steamed Hams Cooking Class',
                'description' => "Well, Seymour, you are an odd fellow, but I must say... you steam a good ham.\n\n**Tonight's Menu:**\n- Steamed Hams (obviously grilled)\n- Aurora Borealis Flambé\n- Superintendent's Surprise\n\n**Note:** Kitchen fire extinguishers have been serviced.\n\n*May I see it?* No.",
                'duration' => 2,
                'group' => 'Special Events',
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
            [
                'name' => 'Max Power Networking Night',
                'description' => "Nobody snuggles with Max Power. You strap yourself in and FEEL THE Gs!\n\n**Network with Springfield's Elite:**\n- Learn how to change your name to something cool\n- Meet Trent Steel and other successful people\n- Power handshakes workshop\n\n*Names inspired by hair dryers welcome.*\n\nMax Power: He's the man whose name you'd love to touch!",
                'duration' => 2.5,
                'group' => 'Special Events',
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

            // ===== WEDNESDAY EVENTS (3) =====
            [
                'name' => 'Trivia: Springfield History',
                'description' => "Test your knowledge of our beloved town! Hosted by Professor Frink.\n\n**Sample Questions:**\n- Who really founded Springfield?\n- What's the tire fire's birthday?\n- How many times has Sideshow Bob tried to kill Bart?\n\nGlayvin! Prizes for top scorers!",
                'duration' => 2,
                'group' => 'Special Events',
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
                'name' => 'I Choo-Choo-Choose You Speed Dating',
                'description' => "Let's bee friends! Springfield's premier singles event for lonely hearts.\n\n**How it works:**\n- 5 minutes per date\n- Free conversation hearts\n- Ralph Wiggum NOT in attendance (restraining order)\n\n*\"You choo-choo-choose me?\"*\n\nNote: Please do not give valentines to anyone whose cat's breath smells like cat food.",
                'duration' => 3,
                'group' => 'Special Events',
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
                'name' => 'S-M-R-T Spelling Bee',
                'description' => "I am so smart! S-M-R-T! I mean S-M-A-R-T!\n\n**Compete in Springfield's dumbest smart competition:**\n- Words like \"be\" and \"cat\" accepted\n- Homer Simpson as guest judge\n- Prize: A feeling of adequacy\n\n*Sponsored by Springfield Elementary's No Child Left Behind Program*\n\nWinner gets to say \"I am so smart!\" on stage.",
                'duration' => 2,
                'group' => 'Comedy',
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

            // ===== THURSDAY EVENTS (3) =====
            [
                'name' => 'Open Mic Night - Springfield Edition',
                'description' => "Share your talent with Springfield! Our weekly open mic welcomes musicians, comedians, poets, and performers of all kinds.\n\n**Featured Acts:**\n- Homer's interpretive poetry\n- Milhouse's magic tricks\n- Ralph's show and tell\n\n**Sign-up starts at 6:30 PM**\n\nEverybody's welcome! Even Shelbyville residents.",
                'duration' => 3,
                'group' => 'Open Mic',
                'image' => 'demo_flyer_openmic.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0000100', // Thursday
                'talent' => 'demo-openmic',
                'tickets' => [
                    ['type' => 'Free Entry', 'price' => 0, 'quantity' => 100],
                ],
            ],
            [
                'name' => 'Karaoke Night',
                'description' => "Grab the mic and belt out your favorites! \"Baby on Board\" performances strongly encouraged.\n\n**Fan Favorites:**\n- \"See My Vest\" (Mr. Burns)\n- \"We Put The Spring in Springfield\"\n- \"Happy Birthday, Lisa\"\n\nThe Be Sharps reunions welcome!",
                'duration' => 3,
                'group' => 'Open Mic',
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
                'name' => 'Troy McClure Film Festival',
                'description' => "Hi, I'm Troy McClure! You may remember me from such film festivals as \"The Decaffeinated Man vs. The Case of the Missing Coffee\" and \"P is for Psycho.\"\n\n**Tonight's Features:**\n- \"The Contrabulous Fabtraption of Professor Horatio Hufnagel\"\n- \"Dial M for Murderousness\"\n- \"The President's Neck is Missing\"\n- \"Hydro, the Man With the Hydraulic Arms\"\n\n*Get confident, stupid!*",
                'duration' => 4,
                'group' => 'Special Events',
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

            // ===== FRIDAY EVENTS (4) =====
            [
                'name' => 'Duffapalooza',
                'description' => "The greatest beer festival this side of Shelbyville! Live music, unlimited Duff samples, and good times guaranteed.\n\n**What to expect:**\n- Live performances all night\n- Duff, Duff Lite, and Duff Dry on tap\n- Pork chops and donuts available\n\nD'oh-n't miss it!",
                'duration' => 4,
                'group' => 'Live Music',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '0000010', // Friday
                'talent' => 'demo-rockers',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 20, 'quantity' => 100],
                    ['type' => 'Duff VIP', 'price' => 50, 'quantity' => 20, 'description' => 'Reserved seating with unlimited Duff'],
                ],
            ],
            [
                'name' => 'Stand-Up with Krusty',
                'description' => "Hey hey! Krusty the Clown brings his legendary stand-up act to Moe's Tavern!\n\n**Lineup:**\n- Krusty the Clown (Headliner)\n- Mr. Teeny (Opening Act)\n- Sideshow Mel\n\nHey hey! This show is Krusty-approved!",
                'duration' => 2.5,
                'group' => 'Comedy',
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
                'description' => "Try the drink that put Moe's on the map! The legendary Flaming Moe - now with a secret ingredient that definitely isn't cough syrup.\n\n**Specials:**\n- $5 Flaming Moes all night\n- Fire extinguishers provided\n- Aerosmith NOT scheduled to appear\n\nDress code: Casual (fire-resistant clothing recommended)",
                'duration' => 5,
                'group' => 'Special Events',
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
                'name' => 'Battle of the Bands',
                'description' => "Springfield's biggest musical showdown!\n\n**Tonight's Lineup:**\n- School of Rock (starring Otto)\n- Spinal Tap Tribute Band\n- Sadgasm (featuring Homer)\n- The Party Posse\n\nMay the best band win! Voting by applause.",
                'duration' => 4,
                'group' => 'Live Music',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0000010', // Friday
                'talent' => 'demo-rockers',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 25, 'quantity' => 150],
                    ['type' => 'Backstage Pass', 'price' => 65, 'quantity' => 30, 'description' => 'Meet the bands + exclusive merch'],
                ],
            ],

            // ===== SATURDAY EVENTS (4) =====
            [
                'name' => 'DJ Sideshow Bob',
                'description' => "Get ready to dance! DJ Sideshow Bob spins the hottest electronic tracks all night long. State-of-the-art sound system included.\n\n**WARNING:** Rakes have been removed from the premises for your safety.\n\n21+ event. Valid Springfield ID required.",
                'duration' => 4,
                'group' => 'DJ Nights',
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
                'name' => 'The Stonecutters Secret Show',
                'description' => "Who controls the British crown? Who keeps the metric system down? WE DO! WE DO!\n\nExclusive members-only event. Sacred Parchment required for entry.\n\n**Number 908 (Homer) NOT invited.**\n\nRemember: We do! We do!",
                'duration' => 4,
                'group' => 'Special Events',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talent' => 'demo-stonecutters',
                'tickets' => [
                    ['type' => 'Stonecutter Member', 'price' => 25, 'quantity' => 150],
                    ['type' => 'Stone of Triumph Package', 'price' => 60, 'quantity' => 25, 'description' => 'Exclusive robes + sacred parchment'],
                ],
            ],
            [
                'name' => 'Comedy Roast: Principal Skinner',
                'description' => "SKINNER! Tonight we roast Springfield Elementary's finest principal. Hosted by Superintendent Chalmers.\n\n**Roasters include:**\n- Superintendent Chalmers\n- Groundskeeper Willie\n- Mrs. Krabappel (via video tribute)\n- Bart Simpson\n\nSteamed hams will NOT be served. It's an Albany expression.",
                'duration' => 2.5,
                'group' => 'Comedy',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talent' => 'demo-krusty',
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
                'image' => 'demo_flyer_party.jpg',
                'hour' => 21,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talent' => 'demo-djbob',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 150],
                ],
            ],

            // ===== MULTI-DAY RECURRING EVENTS =====
            [
                'name' => 'Happy Hour: $1 Squishees',
                'description' => "Who needs the Kwik-E-Mart? WE DO!\n\n**Daily Happy Hour Specials (4-6 PM):**\n- $1 Squishees (all 47 flavors!)\n- Brain freeze competition at 5 PM\n- Apu's secret recipe nachos\n\n*Thank you, come again!*\n\nNote: Squishee machine may occasionally achieve sentience.",
                'duration' => 2,
                'group' => 'Special Events',
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
                'name' => 'Bingo with Abe Simpson',
                'description' => "Back in my day, we had to walk 15 miles in the snow just to play bingo! And we liked it!\n\n**Hosted by Abraham \"Grampa\" Simpson**\n\nExpect long stories about:\n- The time he wore an onion on his belt (which was the style at the time)\n- Shelbyville and their speed holes\n- \"Dear Mr. President, there are too many states nowadays\"\n\n*\"Old Man Yells At Bingo Card\"*",
                'duration' => 2,
                'group' => 'Special Events',
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
                'name' => 'Lard Lad Donut Eating Contest',
                'description' => "Mmm... donuts. Homer Simpson's record: 47 donuts in one sitting.\n\n**Rules:**\n- No hands for the first round\n- Pink frosted sprinkled donuts only\n- \"I can't believe I ate the whole thing\" must be said afterward\n\n*\"Donuts. Is there anything they can't do?\"*\n\nWinner receives a golden Lard Lad statue.",
                'duration' => 2,
                'group' => 'Special Events',
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
                'name' => 'Legal Sea Foods with Lionel Hutz',
                'description' => "Works on contingency? No, money down!\n\n**Tonight's Seminar:**\n- How to read contracts (hint: don't)\n- \"That's why you're the judge and I'm the law-talking guy\"\n- Business card printing workshop\n- Smoking in court: pros and cons\n\n*\"Mr. Simpson, this is the most blatant case of fraudulent advertising since my suit against the film 'The Neverending Story.'\"*\n\nFree breadsticks (technically a loophole).",
                'duration' => 2,
                'group' => 'Comedy',
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

            // ===== ADDITIONAL DEEP-CUT EVENTS =====
            [
                'name' => 'Inanimate Carbon Rod Appreciation Night',
                'description' => "IN ROD WE TRUST!\n\nCelebrate the hero that saved the Corvair space shuttle!\n\n**Tonight's Program:**\n- Documentary screening: \"Rod: The Movie\"\n- Meet and greet with THE Rod\n- Time Magazine \"Inanimate Object of the Year\" ceremony\n\n*\"It's a door! Use it!\"*\n\nHomer Simpson NOT invited (still bitter).",
                'duration' => 2,
                'group' => 'Special Events',
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
                'name' => 'McBain Double Feature',
                'description' => "ICE TO MEET YOU!\n\n**Tonight's Films:**\n- \"McBain: Let's Get Silly\" (1991)\n- \"McBain IV: Fatal Discharge\"\n\n**Featuring:**\n- Commie-nazis getting what they deserve\n- One-liners that don't make sense in context\n- Senator Mendoza's comeuppance\n\n*\"Right now I'm thinking about holding another meeting... IN BED!\"*\n\nFree popcorn. It's showtime.",
                'duration' => 4,
                'group' => 'Special Events',
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
                'name' => 'Dental Plan Night',
                'description' => "Lisa needs braces! DENTAL PLAN! Lisa needs braces! DENTAL PLAN!\n\n**Union Meeting & Celebration:**\n- Free dental checkups\n- Carl Carlson's powerpoint presentation\n- Lenny's eye patch station\n- \"Where's my burrito?\" snack bar\n\n*Come for the dental plan. Stay for the braces.*\n\nSponsored by Local 643.",
                'duration' => 3,
                'group' => 'Special Events',
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
                'name' => 'Treehouse of Horror Marathon',
                'description' => "The following program contains scenes of extreme violence and adult content. Viewer discretion is advised.\n\n**Tonight's Episodes:**\n- \"The Shinning\" (No TV and no beer make Homer something something)\n- \"Time and Punishment\" (Don't touch anything!)\n- \"Nightmare Cafeteria\" (Grade F meat)\n- \"Clown Without Pity\" (That doll is evil!)\n\n*\"Quiet, you! Do you want to get sued?!\"*",
                'duration' => 5,
                'group' => 'Special Events',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 21,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talent' => 'demo-djbob',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 120],
                    ['type' => 'Krusty Doll Package', 'price' => 35, 'quantity' => 25, 'description' => 'Good/Evil switch included'],
                ],
            ],
            [
                'name' => 'Hans Moleman Film Festival',
                'description' => "\"Man Getting Hit by Football\" and other cinematic masterpieces.\n\n**Tonight's Films:**\n- \"Man Getting Hit by Football\" (Academy Award nominee)\n- \"Hans Moleman Productions Presents: Hans Moleman Productions\"\n- \"The Trials of Hans Moleman\"\n\n*\"I was saying Boo-urns!\"*\n\nNote: Hans Moleman is 31 years old. The man never waits.",
                'duration' => 2,
                'group' => 'Comedy',
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
                'name' => "Everything's Coming Up Milhouse",
                'description' => "A celebration of mediocrity! Finally, everything's coming up Milhouse!\n\n**Tonight's Festivities:**\n- Thrillho video game tournament\n- \"My mom says I'm cool\" affirmation station\n- Vaseline-based hair styling tips\n- Purple fruit drinks\n\n*\"My feet are soaked, but my cuffs are bone dry!\"*\n\nRemember: Nobody likes Milhouse! (But tonight we do)",
                'duration' => 3,
                'group' => 'Special Events',
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
                'name' => 'Pin Pals Bowling Tournament',
                'description' => "Pins pals! Pins pals! We're the Pin Pals!\n\n**Teams Welcome:**\n- Original Pin Pals (Homer, Apu, Moe, Otto)\n- The Holy Rollers\n- The Stereotypes\n- Channel 6 Wastelanders\n\n**Prizes:**\n- First place: Mr. Burns' actual bowling team trophy\n- Last place: You tried (participation ribbon)\n\n*Homer's bowling hand is ready.*",
                'duration' => 4,
                'group' => 'Special Events',
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
                'name' => 'Gabbo Night',
                'description' => "GABBO! GABBO! GABBO!\n\nAll the kids in Springfield are S.O.B.s! (Just kidding... or am I?)\n\n**Tonight's Show:**\n- Gabbo and Arthur Crandall perform\n- \"Look, Smithers! Garbo is coming!\"\n- Special appearance by that 2nd-rate ventriloquist, Krusty\n\n*\"I'm a bad wittle boy!\"*\n\nWarning: May contain phrases that sound like profanity.",
                'duration' => 2,
                'group' => 'Comedy',
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
                'name' => 'Tomacco Tasting Night',
                'description' => "It tastes like grandma! I want more!\n\n**Try Homer's controversial hybrid crop:**\n- Pure tomacco samples\n- Tomacco juice\n- Tomacco salsa (highly addictive)\n\n**Warning:** Product may be highly addictive. Animals will break down fences to obtain it.\n\n*Tobacco company executives NOT invited.*\n\nLaramie sponsorship pending.",
                'duration' => 2,
                'group' => 'Special Events',
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
                'name' => 'Canyonero Night',
                'description' => "CAN YOU NAME THE TRUCK WITH FOUR-WHEEL DRIVE? SMELLS LIKE A STEAK AND SEATS THIRTY-FIVE?\n\n**CANYONERO!**\n\n**Tonight's Events:**\n- SUV parade in the parking lot\n- \"Unexplained fires are a matter for the courts\" disclaimer signing\n- 12 yards long, 2 lanes wide display\n- Squirrel-crushing demonstration\n\n*She blinds everybody with her super high beams!*\n\nTop of the line in utility sports!",
                'duration' => 3,
                'group' => 'Special Events',
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
                'name' => 'Poochie Memorial Night',
                'description' => "I have to go now. My planet needs me.\n\n*Note: Poochie died on the way back to his home planet.*\n\n**Tonight's Tribute:**\n- Screening of Poochie's only episode\n- \"Poochitize me, Cap'n!\" drink specials\n- Rastafarian surfer costume contest\n- Roy appearance (maybe)\n\n*\"When are they gonna get to the fireworks factory?!\"*\n\nCute! But I want a dog, not Fonzie in a dog suit.",
                'duration' => 2,
                'group' => 'Special Events',
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
                'name' => 'Sneed\'s Night (Formerly Chuck\'s)',
                'description' => "Feed and Seed! (Formerly Chuck's)\n\n**What we offer:**\n- Quality feed\n- Premium seed\n- The finest agricultural supplies in Springfield\n\n*Ask about our previous owner's naming convention.*\n\nFamilies welcome. Subtle humor appreciated.",
                'duration' => 3,
                'group' => 'Special Events',
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
                'name' => 'Purple Monkey Dishwasher',
                'description' => "By the end of the meeting, the sentence \"we'll be negotiating our own contract\" became \"purple monkey dishwasher.\"\n\n**Tonight's Game:**\n- Championship telephone game tournament\n- Teams of 10\n- Prizes for most creative misinterpretations\n- Lenny and Carl as referees\n\n*\"We're sorry the teachers won't come back until you rehire Principal Skinner purple monkey dishwasher.\"*",
                'duration' => 2,
                'group' => 'Comedy',
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
                'name' => 'Do It For Her Night',
                'description' => "\"DON'T FORGET: YOU'RE HERE FOREVER\" covered by Maggie's photos.\n\n**A heartwarming evening:**\n- Photo collage workshop\n- Motivational sign-making\n- \"Do It For Her\" templates provided\n- Onions available for crying purposes\n\n*This is the most emotional room at the nuclear plant.*\n\nMaggie appearances not guaranteed but highly probable.",
                'duration' => 2,
                'group' => 'Special Events',
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
                'name' => "Dr. Nick's Medical Seminar",
                'description' => "Hi, everybody! HI, DR. NICK!\n\n**Tonight's Topics:**\n- The knee bone's connected to the... something\n- \"Call 1-600-DOCTORB. The B is for bargain!\"\n- Inflammable means flammable?!\n- How to identify which organ goes where\n\n*\"The coroner? I'm so sick of that guy!\"*\n\nDisclaimer: Dr. Nick graduated from Hollywood Upstairs Medical College.",
                'duration' => 2,
                'group' => 'Comedy',
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
            [
                'name' => 'Comic Book Guy\'s Worst. Event. Ever.',
                'description' => "Ooh, a sarcasm detector. That's a REAL useful invention.\n\n**Tonight's Activities:**\n- Worst costume contest (intentionally bad)\n- Trivia: Name characters who've died and returned\n- \"There is no emoticon for what I am feeling\" workshop\n- Tacobell dog memorial\n\n*\"I've wasted my life.\"*\n\nLoneliest guy in the world seeks attendees.",
                'duration' => 3,
                'group' => 'Comedy',
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

            // ===== ONE-TIME EVENTS =====
            [
                'name' => "New Year's Eve: Springfield Countdown",
                'description' => "Ring in the new year Springfield style! Live music, DJ after midnight, Duff toast, party favors, and the best view of the tire fire!\n\n**Package includes:**\n- Open Duff bar 9 PM - 2 AM\n- Krusty Burger appetizers\n- Duff toast at midnight\n- Party favors (Itchy & Scratchy themed)\n\nHappy New Year! Don't have a cow, man!",
                'duration' => 6,
                'group' => 'Special Events',
                'image' => 'demo_flyer_special.jpg',
                'days_of_week' => null, // One-time event
                'talent' => 'demo-djbob',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 75, 'quantity' => 200],
                    ['type' => 'VIP Package', 'price' => 150, 'quantity' => 50, 'description' => 'Premium open bar + Mr. Burns private lounge'],
                ],
            ],
        ];
    }

    /**
     * Create schedules that the demo user follows
     */
    protected function createFollowedSchedules(User $user): void
    {
        $schedules = [
            [
                'name' => 'The Leftorium',
                'subdomain' => 'demo-leftorium',
                'type' => 'venue',
                'city' => 'Springfield',
                'country_code' => 'US',
                'background_colors' => '#66bb6a, #2e7d32',
                'accent_color' => '#81c784',
                'events' => [
                    ['name' => 'Left-Handed Guitar Night', 'days_offset' => -7, 'price' => 25],
                    ['name' => 'Ned Flanders Gospel Hour', 'days_offset' => 5, 'price' => 15],
                    ['name' => 'Okily Dokily Open Mic', 'days_offset' => 12, 'price' => 10],
                ],
            ],
            [
                'name' => 'Krusty Burger Arena',
                'subdomain' => 'demo-krustyburger',
                'type' => 'venue',
                'city' => 'Springfield',
                'country_code' => 'US',
                'background_colors' => '#ff5722, #d32f2f',
                'accent_color' => '#ffeb3b',
                'events' => [
                    ['name' => 'Monster Truck Rally', 'days_offset' => -3, 'price' => 35],
                    ['name' => 'Wrestling: Bumblebee Man vs Dr. Nick', 'days_offset' => 8, 'price' => 40],
                ],
            ],
            [
                'name' => "The Android's Dungeon",
                'subdomain' => 'demo-androidsdungeon',
                'type' => 'venue',
                'city' => 'Springfield',
                'country_code' => 'US',
                'background_colors' => '#9c27b0, #4a148c',
                'accent_color' => '#ce93d8',
                'events' => [
                    ['name' => 'Radioactive Man Signing', 'days_offset' => -10, 'price' => 20],
                    ['name' => 'D&D Night: Dungeons & Donuts', 'days_offset' => 3, 'price' => 15],
                    ['name' => 'Worst. Cosplay Contest. Ever.', 'days_offset' => 15, 'price' => 12],
                ],
            ],
            [
                'name' => 'Springfield Bowl',
                'subdomain' => 'demo-springfieldbowl',
                'type' => 'venue',
                'city' => 'Springfield',
                'country_code' => 'US',
                'background_colors' => '#42a5f5, #1565c0',
                'accent_color' => '#64b5f6',
                'events' => [
                    ['name' => 'Springfield Philharmonic', 'days_offset' => -5, 'price' => 45],
                    ['name' => 'Spinal Tap Live!', 'days_offset' => 20, 'price' => 55],
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
                $event->description = 'Join us for an amazing event!';
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
     */
    protected function createUserTicketPurchases(User $user): void
    {
        // Get events from followed schedules (demo- prefixed roles)
        $followedRoles = Role::where('subdomain', 'like', 'demo-%')->get();

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
                AnalyticsDaily::create([
                    'role_id' => $role->id,
                    'date' => $date->toDateString(),
                    'desktop_views' => $desktopViews,
                    'mobile_views' => $mobileViews,
                    'tablet_views' => $tabletViews,
                    'unknown_views' => $unknownViews,
                ]);
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
                AnalyticsReferrersDaily::create([
                    'role_id' => $role->id,
                    'date' => $date->toDateString(),
                    'source' => $sourceData['source'],
                    'domain' => $sourceData['domain'],
                    'views' => $sourceViews,
                ]);
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

                AnalyticsEventsDaily::create([
                    'event_id' => $event->id,
                    'date' => $currentDate->toDateString(),
                    'desktop_views' => $desktopViews,
                    'mobile_views' => $mobileViews,
                    'tablet_views' => $tabletViews,
                    'unknown_views' => 0,
                    'sales_count' => $salesCount,
                    'revenue' => $revenue,
                ]);

                $currentDate->addDay();
            }
        }
    }
}
