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
            $role->city = 'New York';
            $role->country_code = 'US';
            $role->background = 'gradient';
            $role->background_colors = '#667eea, #764ba2';
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
            'demo_flyer_jazz.jpg' => 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=800&h=600&fit=crop',
            'demo_flyer_dj.jpg' => 'https://images.unsplash.com/photo-1574391884720-bbc3740c59d1?w=800&h=600&fit=crop',
            'demo_flyer_comedy.jpg' => 'https://images.unsplash.com/photo-1585699324551-f6c309eedeca?w=800&h=600&fit=crop',
            'demo_flyer_openmic.jpg' => 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?w=800&h=600&fit=crop',
            'demo_flyer_rock.jpg' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop',
            'demo_flyer_party.jpg' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800&h=600&fit=crop',
            'demo_flyer_special.jpg' => 'https://images.unsplash.com/photo-1496024840928-4c417adf211d?w=800&h=600&fit=crop',
            'demo_profile_band.jpg' => 'https://images.unsplash.com/photo-1415201364774-f6f0bb35f28f?w=400&h=400&fit=crop',
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
        $now = Carbon::now($role->timezone);

        foreach ($events as $index => $eventData) {
            $isRecurring = ! empty($eventData['days_of_week']);

            if ($isRecurring) {
                // For recurring events, find the most recent occurrence of the target day
                // This ensures events appear in the past and recur into the future
                $targetDayOfWeek = strpos($eventData['days_of_week'], '1');
                $eventDate = $now->copy()
                    ->previous($targetDayOfWeek) // Get last occurrence of this day
                    ->setHour(rand(18, 21))
                    ->setMinute(rand(0, 1) * 30)
                    ->setSecond(0);
            } else {
                // One-time events (like New Year's Eve): set to next Dec 31
                $eventDate = $now->copy()
                    ->setMonth(12)
                    ->setDay(31)
                    ->setHour(21)
                    ->setMinute(0)
                    ->setSecond(0);

                // If Dec 31 has passed this year, use next year
                if ($eventDate->isPast()) {
                    $eventDate->addYear();
                }
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
            [
                'name' => 'Duffapalooza',
                'description' => "The greatest beer festival this side of Shelbyville! Live music, unlimited Duff samples, and good times guaranteed.\n\n**What to expect:**\n- Live performances all night\n- Duff, Duff Lite, and Duff Dry on tap\n- Pork chops and donuts available\n\nD'oh-n't miss it!",
                'duration' => 4,
                'group' => 'Live Music',
                'image' => 'demo_flyer_rock.jpg',
                'days_of_week' => '0000010', // Friday
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 20, 'quantity' => 100],
                    ['type' => 'Duff VIP', 'price' => 50, 'quantity' => 20, 'description' => 'Reserved seating with unlimited Duff'],
                ],
            ],
            [
                'name' => 'DJ Sideshow Bob',
                'description' => "Get ready to dance! DJ Sideshow Bob spins the hottest electronic tracks all night long. State-of-the-art sound system included.\n\n**WARNING:** Rakes have been removed from the premises for your safety.\n\n21+ event. Valid Springfield ID required.",
                'duration' => 4,
                'group' => 'DJ Nights',
                'image' => 'demo_flyer_dj.jpg',
                'days_of_week' => '0000001', // Saturday
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 200],
                    ['type' => 'VIP Access', 'price' => 45, 'quantity' => 30, 'description' => 'Skip the line + avoid rakes'],
                ],
            ],
            [
                'name' => 'Stand-Up with Krusty',
                'description' => "Hey hey! Krusty the Clown brings his legendary stand-up act to Moe's Tavern!\n\n**Lineup:**\n- Krusty the Clown (Headliner)\n- Mr. Teeny (Opening Act)\n- Sideshow Mel\n\nHey hey! This show is Krusty-approved!",
                'duration' => 2.5,
                'group' => 'Comedy',
                'image' => 'demo_flyer_comedy.jpg',
                'days_of_week' => '0000010', // Friday
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 80],
                    ['type' => 'Front Row', 'price' => 35, 'quantity' => 15, 'description' => 'Best seats - warning: may get squirted with seltzer'],
                ],
            ],
            [
                'name' => 'Open Mic Night - Springfield Edition',
                'description' => "Share your talent with Springfield! Our weekly open mic welcomes musicians, comedians, poets, and performers of all kinds.\n\n**Featured Acts:**\n- Homer's interpretive poetry\n- Milhouse's magic tricks\n- Ralph's show and tell\n\n**Sign-up starts at 6:30 PM**\n\nEverybody's welcome! Even Shelbyville residents.",
                'duration' => 3,
                'group' => 'Open Mic',
                'image' => 'demo_flyer_openmic.jpg',
                'days_of_week' => '0000100', // Thursday
                'tickets' => [
                    ['type' => 'Free Entry', 'price' => 0, 'quantity' => 100],
                ],
            ],
            [
                'name' => 'The Stonecutters Secret Show',
                'description' => "Who controls the British crown? Who keeps the metric system down? WE DO! WE DO!\n\nExclusive members-only event. Sacred Parchment required for entry.\n\n**Number 908 (Homer) NOT invited.**\n\nRemember: We do! We do!",
                'duration' => 4,
                'group' => 'Special Events',
                'image' => 'demo_flyer_special.jpg',
                'days_of_week' => '0000001', // Saturday
                'tickets' => [
                    ['type' => 'Stonecutter Member', 'price' => 25, 'quantity' => 150],
                    ['type' => 'Stone of Triumph Package', 'price' => 60, 'quantity' => 25, 'description' => 'Exclusive robes + sacred parchment'],
                ],
            ],
            [
                'name' => 'Flaming Moe Night',
                'description' => "Try the drink that put Moe's on the map! The legendary Flaming Moe - now with a secret ingredient that definitely isn't cough syrup.\n\n**Specials:**\n- $5 Flaming Moes all night\n- Fire extinguishers provided\n- Aerosmith NOT scheduled to appear\n\nDress code: Casual (fire-resistant clothing recommended)",
                'duration' => 5,
                'group' => 'Special Events',
                'image' => 'demo_flyer_party.jpg',
                'days_of_week' => '0000010', // Friday
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 20, 'quantity' => 180],
                    ['type' => 'Flaming Moe Package', 'price' => 75, 'quantity' => 8, 'description' => '5 Flaming Moes + commemorative glass'],
                ],
            ],
            [
                'name' => 'Trivia: Springfield History',
                'description' => "Test your knowledge of our beloved town! Hosted by Professor Frink.\n\n**Sample Questions:**\n- Who really founded Springfield?\n- What's the tire fire's birthday?\n- How many times has Sideshow Bob tried to kill Bart?\n\nGlayvin! Prizes for top scorers!",
                'duration' => 2,
                'group' => 'Special Events',
                'image' => 'demo_flyer_special.jpg',
                'days_of_week' => '0001000', // Wednesday
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 10, 'quantity' => 60],
                ],
            ],
            [
                'name' => 'Karaoke Night',
                'description' => "Grab the mic and belt out your favorites! \"Baby on Board\" performances strongly encouraged.\n\n**Fan Favorites:**\n- \"See My Vest\" (Mr. Burns)\n- \"We Put The Spring in Springfield\"\n- \"Happy Birthday, Lisa\"\n\nThe Be Sharps reunions welcome!",
                'duration' => 3,
                'group' => 'Open Mic',
                'image' => 'demo_flyer_openmic.jpg',
                'days_of_week' => '0000100', // Thursday
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 8, 'quantity' => 75],
                ],
            ],
            [
                'name' => 'Jazz Night with Bleeding Gums Murphy',
                'description' => "A tribute to Springfield's greatest jazz musician. Lisa Simpson and friends perform classic Bleeding Gums Murphy hits.\n\n**Featuring:**\n- \"Sax on the Beach\"\n- \"Jazzman\"\n- And more smooth saxophone\n\nBring your tissues. This one's emotional.",
                'duration' => 3,
                'group' => 'Live Music',
                'image' => 'demo_flyer_jazz.jpg',
                'days_of_week' => '1000000', // Sunday
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 22, 'quantity' => 100],
                    ['type' => 'VIP Table', 'price' => 55, 'quantity' => 20, 'description' => 'Reserved seating + commemorative saxophone pin'],
                ],
            ],
            [
                'name' => 'Comedy Roast: Principal Skinner',
                'description' => "SKINNER! Tonight we roast Springfield Elementary's finest principal. Hosted by Superintendent Chalmers.\n\n**Roasters include:**\n- Superintendent Chalmers\n- Groundskeeper Willie\n- Mrs. Krabappel (via video tribute)\n- Bart Simpson\n\nSteamed hams will NOT be served. It's an Albany expression.",
                'duration' => 2.5,
                'group' => 'Comedy',
                'image' => 'demo_flyer_comedy.jpg',
                'days_of_week' => '0000001', // Saturday
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 100],
                    ['type' => 'Aurora Borealis Package', 'price' => 40, 'quantity' => 20, 'description' => 'Front row + steamed ham (actually grilled)'],
                ],
            ],
            [
                'name' => 'Battle of the Bands',
                'description' => "Springfield's biggest musical showdown!\n\n**Tonight's Lineup:**\n- School of Rock (starring Otto)\n- Spinal Tap Tribute Band\n- Sadgasm (featuring Homer)\n- The Party Posse\n\nMay the best band win! Voting by applause.",
                'duration' => 4,
                'group' => 'Live Music',
                'image' => 'demo_flyer_rock.jpg',
                'days_of_week' => '0000010', // Friday
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 25, 'quantity' => 150],
                    ['type' => 'Backstage Pass', 'price' => 65, 'quantity' => 30, 'description' => 'Meet the bands + exclusive merch'],
                ],
            ],
            [
                'name' => '80s Night: Do The Bartman',
                'description' => "Flashback to the greatest decade! Dress in your best 80s/90s attire and dance to all the classics.\n\n**Featuring:**\n- \"Do The Bartman\" dance-off at 11 PM\n- Costume contest (Marge's hair encouraged)\n- Deep Cuts from the Springfield Files\n\nAy caramba!",
                'duration' => 4,
                'group' => 'DJ Nights',
                'image' => 'demo_flyer_party.jpg',
                'days_of_week' => '0000001', // Saturday
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 150],
                ],
            ],
            [
                'name' => "Poetry Slam: Moe's Haiku Hour",
                'description' => "Words that move you... to tears. Competitive spoken word poetry featuring Springfield's most melancholic verses.\n\n**Hosted by Moe Szyslak**\n\nSample:\n*\"My life is empty\nNo one calls, the bar is dead\nPass the rat poison\"*\n\nTissues provided. Bring your sad poems.",
                'duration' => 2.5,
                'group' => 'Open Mic',
                'image' => 'demo_flyer_openmic.jpg',
                'days_of_week' => '0010000', // Tuesday
                'tickets' => [
                    ['type' => 'Audience', 'price' => 10, 'quantity' => 100],
                    ['type' => 'Poet Entry', 'price' => 5, 'quantity' => 20],
                ],
            ],
            [
                'name' => "New Year's Eve: Springfield Countdown",
                'description' => "Ring in the new year Springfield style! Live music, DJ after midnight, Duff toast, party favors, and the best view of the tire fire!\n\n**Package includes:**\n- Open Duff bar 9 PM - 2 AM\n- Krusty Burger appetizers\n- Duff toast at midnight\n- Party favors (Itchy & Scratchy themed)\n\nHappy New Year! Don't have a cow, man!",
                'duration' => 6,
                'group' => 'Special Events',
                'image' => 'demo_flyer_special.jpg',
                'days_of_week' => null, // One-time event
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 75, 'quantity' => 200],
                    ['type' => 'VIP Package', 'price' => 150, 'quantity' => 50, 'description' => 'Premium open bar + Mr. Burns private lounge'],
                ],
            ],
            [
                'name' => "Talent Show: Springfield's Got Talent",
                'description' => "Springfield's finest showcase their hidden talents!\n\n**Expected Performances:**\n- Mr. Burns: Juggling (with hounds)\n- Hans Moleman: \"Football in the Groin\" reenactment\n- Comic Book Guy: Worst. Performance. Ever.\n- Ralph Wiggum: TBD (probably glue-related)\n\nJudges: Mayor Quimby, Krusty, Lisa Simpson",
                'duration' => 3,
                'group' => 'Special Events',
                'image' => 'demo_flyer_special.jpg',
                'days_of_week' => '1000000', // Sunday
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 120],
                    ['type' => 'Judges Table', 'price' => 40, 'quantity' => 15, 'description' => 'Best seats + voting privileges'],
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
                'background_colors' => '#2e7d32, #1b5e20',
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
                'background_colors' => '#d32f2f, #b71c1c',
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
                'background_colors' => '#4a148c, #7b1fa2',
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
                'background_colors' => '#1565c0, #0d47a1',
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
            $role->email = 'demo-'.Str::random(8).'@example.com';
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
