<?php

namespace App\Services;

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
    public const DEMO_ROLE_SUBDOMAIN = 'thenightowls';

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
            $role->type = 'talent';
            $role->name = 'The Night Owls';
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
        $pastEventCount = 5;

        foreach ($events as $index => $eventData) {
            // Split events: ~5 past (last 14 days), rest future (next 30 days)
            if ($index < $pastEventCount) {
                // Past events: random 1-14 days ago
                $daysAgo = rand(1, 14);
                $eventDate = $now->copy()->subDays($daysAgo)
                    ->setHour(rand(18, 21))
                    ->setMinute(rand(0, 1) * 30)
                    ->setSecond(0);
            } else {
                // Future events: base spacing + random offset
                $futureIndex = $index - $pastEventCount;
                $daysAhead = max(1, ($futureIndex * 3) + rand(-1, 2));
                $eventDate = $now->copy()->addDays($daysAhead)
                    ->setHour(rand(18, 22))
                    ->setMinute(rand(0, 1) * 30)
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

            // Create sample sales for past events
            if ($index < $pastEventCount) {
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
        $firstNames = ['John', 'Jane', 'Mike', 'Sarah', 'David', 'Emily', 'Chris', 'Anna'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis'];

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
                'name' => 'Jazz Night with The Blue Notes',
                'description' => "Join us for an unforgettable evening of smooth jazz! The Blue Notes bring their signature blend of classic and contemporary jazz that will transport you to another era.\n\n**What to expect:**\n- Live jazz performance\n- Full bar service\n- Appetizers available",
                'duration' => 3,
                'group' => 'Live Music',
                'image' => 'demo_flyer_jazz.jpg',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 20, 'quantity' => 100],
                    ['type' => 'VIP Table', 'price' => 50, 'quantity' => 20, 'description' => 'Reserved seating with bottle service'],
                ],
            ],
            [
                'name' => 'DJ MaxBeat - Electronic Dance Night',
                'description' => "Get ready to dance! DJ MaxBeat spins the hottest electronic tracks all night long. State-of-the-art sound system and incredible light show included.\n\n21+ event. Valid ID required.",
                'duration' => 4,
                'group' => 'DJ Nights',
                'image' => 'demo_flyer_dj.jpg',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 200],
                    ['type' => 'VIP Access', 'price' => 45, 'quantity' => 30, 'description' => 'Skip the line + VIP lounge access'],
                ],
            ],
            [
                'name' => 'Comedy Showcase',
                'description' => "Laugh until it hurts! Our monthly comedy showcase features 5 hilarious comedians from the local scene and beyond.\n\n**Lineup:**\n- Mike Thompson (Headliner)\n- Sarah Chen\n- David Martinez\n- And more!",
                'duration' => 2.5,
                'group' => 'Comedy',
                'image' => 'demo_flyer_comedy.jpg',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 80],
                    ['type' => 'Front Row', 'price' => 35, 'quantity' => 15, 'description' => 'Best seats in the house - be prepared to be part of the show!'],
                ],
            ],
            [
                'name' => 'Open Mic Night',
                'description' => "Share your talent with the world! Our weekly open mic welcomes musicians, comedians, poets, and performers of all kinds.\n\n**Sign-up starts at 6:30 PM**\n\nFirst-timers welcome!",
                'duration' => 3,
                'group' => 'Open Mic',
                'image' => 'demo_flyer_openmic.jpg',
                'tickets' => [
                    ['type' => 'Free Entry', 'price' => 0, 'quantity' => 100],
                ],
            ],
            [
                'name' => 'Rock The House - Live Band Night',
                'description' => "Three incredible rock bands take the stage for one epic night!\n\n**Performing:**\n- The Electric Storm\n- Midnight Ravens\n- Steel Thunder\n\nDoors open at 7 PM. Earplugs recommended!",
                'duration' => 4,
                'group' => 'Live Music',
                'image' => 'demo_flyer_rock.jpg',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 25, 'quantity' => 150],
                    ['type' => 'VIP Backstage', 'price' => 60, 'quantity' => 25, 'description' => 'Meet the bands + exclusive merchandise'],
                ],
            ],
            [
                'name' => 'Tropical House Party',
                'description' => "Escape to paradise! Our resident DJs spin tropical house and summer vibes all night. Specialty cocktails and island-inspired decor.\n\nDress code: Beach chic",
                'duration' => 5,
                'group' => 'DJ Nights',
                'image' => 'demo_flyer_party.jpg',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 20, 'quantity' => 180],
                    ['type' => 'Cabana Package', 'price' => 150, 'quantity' => 8, 'description' => 'Private cabana for up to 6 guests + bottle service'],
                ],
            ],
            [
                'name' => 'Improv Comedy Night',
                'description' => "Anything can happen! Our improv troupe takes your suggestions and turns them into hilarious scenes. Interactive, unpredictable, and absolutely hilarious.\n\nFamily-friendly show at 7 PM, adult show at 9 PM.",
                'duration' => 2,
                'group' => 'Comedy',
                'image' => 'demo_flyer_comedy.jpg',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 60],
                ],
            ],
            [
                'name' => 'Acoustic Sessions',
                'description' => "An intimate evening of acoustic performances in our cozy lounge setting. Perfect for date night or a relaxing evening with friends.\n\nFeaturing local singer-songwriters sharing their original music.",
                'duration' => 2.5,
                'group' => 'Live Music',
                'image' => 'demo_flyer_jazz.jpg',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 12, 'quantity' => 50],
                ],
            ],
            [
                'name' => 'Poetry Slam',
                'description' => "Words that move you! Competitive spoken word poetry with cash prizes for the top performers.\n\n**Prizes:**\n- 1st Place: $200\n- 2nd Place: $100\n- 3rd Place: $50\n\nAudience voting determines the winners!",
                'duration' => 3,
                'group' => 'Open Mic',
                'image' => 'demo_flyer_openmic.jpg',
                'tickets' => [
                    ['type' => 'Audience', 'price' => 10, 'quantity' => 100],
                    ['type' => 'Performer Entry', 'price' => 5, 'quantity' => 20],
                ],
            ],
            [
                'name' => 'New Year\'s Eve Spectacular',
                'description' => "Ring in the new year with style! Live band, DJ after midnight, champagne toast, party favors, and the best view of the fireworks in town.\n\n**Package includes:**\n- Open bar 9 PM - 2 AM\n- Gourmet appetizers\n- Champagne toast at midnight\n- Party favors",
                'duration' => 6,
                'group' => 'Special Events',
                'image' => 'demo_flyer_special.jpg',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 75, 'quantity' => 200],
                    ['type' => 'VIP Package', 'price' => 150, 'quantity' => 50, 'description' => 'Premium open bar + private lounge + gift bag'],
                ],
            ],
            [
                'name' => 'Blues & BBQ',
                'description' => "Soul food and soulful music! Live blues band performs while you enjoy our famous BBQ spread.\n\n**Menu highlights:**\n- Smoked brisket\n- Pulled pork\n- Mac & cheese\n- Cornbread\n\nFood included with admission!",
                'duration' => 4,
                'group' => 'Live Music',
                'image' => 'demo_flyer_jazz.jpg',
                'tickets' => [
                    ['type' => 'General Admission (includes meal)', 'price' => 45, 'quantity' => 100],
                ],
            ],
            [
                'name' => '80s Retro Dance Party',
                'description' => "Flashback to the greatest decade! Dress in your best 80s attire and dance to all the classics.\n\n**Costume contest at 11 PM!**\n\nPrizes for best dressed. Hair spray and neon encouraged!",
                'duration' => 4,
                'group' => 'DJ Nights',
                'image' => 'demo_flyer_party.jpg',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 150],
                ],
            ],
            [
                'name' => 'Stand-Up Spotlight',
                'description' => "One comedian, one hour of non-stop laughs! This month featuring national touring headliner Amy Roberts as seen on Netflix.\n\nMeet & greet after the show for VIP ticket holders.",
                'duration' => 1.5,
                'group' => 'Comedy',
                'image' => 'demo_flyer_comedy.jpg',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 30, 'quantity' => 120],
                    ['type' => 'VIP Meet & Greet', 'price' => 55, 'quantity' => 25, 'description' => 'Photo op + signed merchandise'],
                ],
            ],
            [
                'name' => 'Songwriter Circle',
                'description' => "Four acclaimed songwriters share the stage, trading songs and stories in an intimate \"in the round\" setting.\n\nA rare opportunity to hear the stories behind the songs.",
                'duration' => 2.5,
                'group' => 'Live Music',
                'image' => 'demo_flyer_rock.jpg',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 22, 'quantity' => 75],
                ],
            ],
            [
                'name' => 'Latin Night Fiesta',
                'description' => "Salsa, bachata, merengue and more! Live Latin band followed by DJ playing the hottest reggaeton tracks.\n\n**Free salsa lesson at 8 PM!**\n\nNo partner needed.",
                'duration' => 5,
                'group' => 'Special Events',
                'image' => 'demo_flyer_special.jpg',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 20, 'quantity' => 175],
                    ['type' => 'VIP Booth', 'price' => 100, 'quantity' => 10, 'description' => 'Reserved booth for 4 + bottle service'],
                ],
            ],
        ];
    }
}
