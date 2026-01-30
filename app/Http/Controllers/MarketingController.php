<?php

namespace App\Http\Controllers;

class MarketingController extends Controller
{
    /**
     * Home page
     */
    public function index()
    {
        return view('marketing.index', [
            'personas' => $this->getPersonas(),
            'steps' => $this->getSteps(),
        ]);
    }

    /**
     * Features page
     */
    public function features()
    {
        return view('marketing.features', [
            'features' => $this->getFeatures(),
        ]);
    }

    /**
     * Pricing page
     */
    public function pricing()
    {
        return view('marketing.pricing', [
            'plans' => $this->getPlans(),
        ]);
    }

    /**
     * About page
     */
    public function about()
    {
        return view('marketing.about');
    }

    /**
     * FAQ page
     */
    public function faq()
    {
        return view('marketing.faq');
    }

    /**
     * Ticketing page
     */
    public function ticketing()
    {
        return view('marketing.ticketing', [
            'ticketFeatures' => $this->getTicketFeatures(),
        ]);
    }

    /**
     * AI features page
     */
    public function ai()
    {
        return view('marketing.ai');
    }

    /**
     * Calendar Sync page
     */
    public function calendarSync()
    {
        return view('marketing.calendar-sync');
    }

    /**
     * Google Calendar page
     */
    public function googleCalendar()
    {
        return view('marketing.google-calendar');
    }

    /**
     * CalDAV page
     */
    public function caldav()
    {
        return view('marketing.caldav');
    }

    /**
     * Stripe page
     */
    public function stripe()
    {
        return view('marketing.stripe');
    }

    /**
     * Invoice Ninja page
     */
    public function invoiceninja()
    {
        return view('marketing.invoiceninja');
    }

    /**
     * Analytics page
     */
    public function analytics()
    {
        return view('marketing.analytics');
    }

    /**
     * Custom Fields page
     */
    public function customFields()
    {
        return view('marketing.custom-fields');
    }

    /**
     * Team Scheduling page
     */
    public function teamScheduling()
    {
        return view('marketing.team-scheduling');
    }

    /**
     * Sub-schedules page
     */
    public function subSchedules()
    {
        return view('marketing.sub-schedules');
    }

    /**
     * Online Events page
     */
    public function onlineEvents()
    {
        return view('marketing.online-events');
    }

    /**
     * Open Source & API page
     */
    public function openSource()
    {
        return view('marketing.open-source');
    }

    /**
     * For Talent page
     */
    public function forTalent()
    {
        return view('marketing.for-talent');
    }

    /**
     * For Venues page
     */
    public function forVenues()
    {
        return view('marketing.for-venues');
    }

    /**
     * For Curators page
     */
    public function forCurators()
    {
        return view('marketing.for-curators');
    }

    /**
     * For Musicians page
     */
    public function forMusicians()
    {
        return view('marketing.for-musicians');
    }

    /**
     * For DJs page
     */
    public function forDJs()
    {
        return view('marketing.for-djs');
    }

    /**
     * For Comedians page
     */
    public function forComedians()
    {
        return view('marketing.for-comedians');
    }

    /**
     * For Circus & Acrobatics page
     */
    public function forCircusAcrobatics()
    {
        return view('marketing.for-circus-acrobatics');
    }

    /**
     * For Magicians page
     */
    public function forMagicians()
    {
        return view('marketing.for-magicians');
    }

    /**
     * For Spoken Word & Poetry page
     */
    public function forSpokenWord()
    {
        return view('marketing.for-spoken-word');
    }

    /**
     * For Bars & Pubs page
     */
    public function forBars()
    {
        return view('marketing.for-bars');
    }

    /**
     * For Nightclubs page
     */
    public function forNightclubs()
    {
        return view('marketing.for-nightclubs');
    }

    /**
     * For Music Venues page
     */
    public function forMusicVenues()
    {
        return view('marketing.for-music-venues');
    }

    /**
     * For Theaters page
     */
    public function forTheaters()
    {
        return view('marketing.for-theaters');
    }

    /**
     * For Dance Groups page
     */
    public function forDanceGroups()
    {
        return view('marketing.for-dance-groups');
    }

    /**
     * For Theater Performers page
     */
    public function forTheaterPerformers()
    {
        return view('marketing.for-theater-performers');
    }

    /**
     * For Food Trucks and Vendors page
     */
    public function forFoodTrucksAndVendors()
    {
        return view('marketing.for-food-trucks-and-vendors');
    }

    /**
     * For Comedy Clubs page
     */
    public function forComedyClubs()
    {
        return view('marketing.for-comedy-clubs');
    }

    /**
     * For Restaurants page
     */
    public function forRestaurants()
    {
        return view('marketing.for-restaurants');
    }

    /**
     * For Breweries & Wineries page
     */
    public function forBreweriesAndWineries()
    {
        return view('marketing.for-breweries-and-wineries');
    }

    /**
     * For Art Galleries & Studios page
     */
    public function forArtGalleries()
    {
        return view('marketing.for-art-galleries');
    }

    /**
     * For Community Centers page
     */
    public function forCommunityCenters()
    {
        return view('marketing.for-community-centers');
    }

    /**
     * For Fitness & Yoga page
     */
    public function forFitnessAndYoga()
    {
        return view('marketing.for-fitness-and-yoga');
    }

    /**
     * For Workshop Instructors page
     */
    public function forWorkshopInstructors()
    {
        return view('marketing.for-workshop-instructors');
    }

    /**
     * For Visual Artists page
     */
    public function forVisualArtists()
    {
        return view('marketing.for-visual-artists');
    }

    /**
     * For Farmers Markets page
     */
    public function forFarmersMarkets()
    {
        return view('marketing.for-farmers-markets');
    }

    /**
     * For Hotels & Resorts page
     */
    public function forHotelsAndResorts()
    {
        return view('marketing.for-hotels-and-resorts');
    }

    /**
     * For Libraries page
     */
    public function forLibraries()
    {
        return view('marketing.for-libraries');
    }

    /**
     * Use Cases page
     */
    public function useCases()
    {
        return view('marketing.use-cases');
    }

    /**
     * Compare page
     */
    public function compare()
    {
        return view('marketing.compare');
    }

    /**
     * Contact page
     */
    public function contact()
    {
        return view('marketing.contact');
    }

    /**
     * Integrations page
     */
    public function integrations()
    {
        return view('marketing.integrations');
    }

    /**
     * Privacy Policy page
     */
    public function privacy()
    {
        return view('marketing.privacy');
    }

    /**
     * Terms of Service page
     */
    public function terms()
    {
        return view('marketing.terms');
    }

    /**
     * Self-Hosting Terms of Service page
     */
    public function selfHostingTerms()
    {
        return view('marketing.self-hosting-terms');
    }

    /**
     * Self-Host page
     */
    public function selfHost()
    {
        return view('marketing.selfhost');
    }

    /**
     * SaaS white-label page
     */
    public function saas()
    {
        return view('marketing.saas');
    }

    /**
     * Documentation index page
     */
    public function docsIndex()
    {
        return view('marketing.docs.index');
    }

    // ==========================================
    // User Guide Pages (at /docs root level)
    // ==========================================

    /**
     * Getting Started documentation page
     */
    public function docsGettingStarted()
    {
        return view('marketing.docs.getting-started');
    }

    /**
     * Creating Schedules documentation page
     */
    public function docsCreatingSchedules()
    {
        return view('marketing.docs.creating-schedules');
    }

    /**
     * Schedule Basics documentation page
     */
    public function docsScheduleBasics()
    {
        $customFieldsData = [];

        if (auth()->check()) {
            $user = auth()->user();
            $roles = $user->member()->get();

            foreach ($roles as $role) {
                $fields = $role->getEventCustomFields();
                if (! empty($fields)) {
                    $customFieldsData[] = [
                        'role_name' => $role->name,
                        'fields' => $fields,
                    ];
                }
            }
        }

        return view('marketing.docs.schedule-basics', [
            'customFieldsData' => $customFieldsData,
        ]);
    }

    /**
     * Schedule Styling documentation page
     */
    public function docsScheduleStyling()
    {
        return view('marketing.docs.schedule-styling');
    }

    /**
     * Creating Events documentation page
     */
    public function docsCreatingEvents()
    {
        return view('marketing.docs.creating-events');
    }

    /**
     * Sharing documentation page
     */
    public function docsSharing()
    {
        return view('marketing.docs.sharing');
    }

    /**
     * Tickets documentation page
     */
    public function docsTickets()
    {
        return view('marketing.docs.tickets');
    }

    /**
     * Event Graphics documentation page
     */
    public function docsEventGraphics()
    {
        $customFieldsData = [];

        if (auth()->check()) {
            $user = auth()->user();
            // Get roles where user is owner or admin
            $roles = $user->member()->get();

            foreach ($roles as $role) {
                $fields = $role->getEventCustomFields();
                if (! empty($fields)) {
                    $customFieldsData[] = [
                        'role_name' => $role->name,
                        'fields' => $fields,
                    ];
                }
            }
        }

        return view('marketing.docs.event-graphics', [
            'customFieldsData' => $customFieldsData,
        ]);
    }

    // ==========================================
    // Selfhost Section
    // ==========================================

    /**
     * Selfhost section index page
     */
    public function docsSelfhostIndex()
    {
        return view('marketing.docs.selfhost.index');
    }

    /**
     * Installation documentation page
     */
    public function docsSelfhostInstallation()
    {
        return view('marketing.docs.selfhost.installation');
    }

    /**
     * SaaS documentation page
     */
    public function docsSelfhostSaas()
    {
        return view('marketing.docs.selfhost.saas');
    }

    /**
     * Stripe documentation page
     */
    public function docsSelfhostStripe()
    {
        return view('marketing.docs.selfhost.stripe');
    }

    /**
     * Google Calendar documentation page
     */
    public function docsSelfhostGoogleCalendar()
    {
        return view('marketing.docs.selfhost.google-calendar');
    }

    // ==========================================
    // Developer Section
    // ==========================================

    /**
     * API documentation page
     */
    public function docsDeveloperApi()
    {
        return view('marketing.docs.developer.api');
    }

    /**
     * Get user personas data
     */
    protected function getPersonas(): array
    {
        return [
            [
                'title' => 'Talent',
                'icon' => 'microphone',
                'description' => 'Musicians, DJs, performers, and artists who want to share their upcoming shows and build their audience.',
            ],
            [
                'title' => 'Venue',
                'icon' => 'building',
                'description' => 'Bars, clubs, theaters, and event spaces that host regular events and need to keep their calendar updated.',
            ],
            [
                'title' => 'Curator',
                'icon' => 'calendar',
                'description' => 'Event promoters, bloggers, and community organizers who aggregate and share events from multiple sources.',
            ],
            [
                'title' => 'Vendor',
                'icon' => 'store',
                'description' => 'Food trucks, market vendors, and mobile businesses that appear at different locations on different days.',
            ],
        ];
    }

    /**
     * Get 3-step process data
     */
    protected function getSteps(): array
    {
        return [
            [
                'number' => '1',
                'title' => 'Create Your Schedule',
                'description' => 'Sign up and add your events in seconds. Import from other calendars or add them manually.',
            ],
            [
                'number' => '2',
                'title' => 'Share Your Link',
                'description' => 'Get a custom URL for your schedule. Share it on social media, your website, or anywhere else.',
            ],
            [
                'number' => '3',
                'title' => 'Grow Your Audience',
                'description' => 'Fans can follow your schedule and get notified about new events. Build your community effortlessly.',
            ],
        ];
    }

    /**
     * Get features data
     */
    protected function getFeatures(): array
    {
        return [
            [
                'title' => 'Event Scheduling',
                'icon' => 'calendar',
                'description' => 'Create and manage events with support for recurring schedules, multiple dates, and time zones.',
                'details' => [
                    'One-time and recurring events',
                    'Multiple date support',
                    'Automatic timezone handling',
                    'Import from Google Calendar',
                ],
            ],
            [
                'title' => 'Ticketing & Payments',
                'icon' => 'ticket',
                'description' => 'Sell tickets directly through your schedule with integrated payment processing.',
                'details' => [
                    'Multiple ticket types',
                    'QR code tickets',
                    'Stripe integration',
                    'Real-time sales tracking',
                ],
            ],
            [
                'title' => 'Mobile Optimized',
                'icon' => 'phone',
                'description' => 'Your schedule looks great on any device. Fans can browse and buy tickets on the go.',
                'details' => [
                    'Responsive design',
                    'Fast loading',
                    'Touch-friendly interface',
                    'Works offline',
                ],
            ],
            [
                'title' => 'Team Collaboration',
                'icon' => 'users',
                'description' => 'Invite team members to help manage your schedule. Control who can add and edit events.',
                'details' => [
                    'Multiple team members',
                    'Role-based permissions',
                    'Activity tracking',
                    'Email notifications',
                ],
            ],
            [
                'title' => 'Custom Branding',
                'icon' => 'palette',
                'description' => 'Make your schedule match your brand with custom colors, logos, and domains.',
                'details' => [
                    'Custom colors and fonts',
                    'Logo upload',
                    'Custom domain support',
                    'Remove branding (Pro)',
                ],
            ],
            [
                'title' => 'Analytics & Insights',
                'icon' => 'chart',
                'description' => 'Track views, followers, and ticket sales. Understand your audience better.',
                'details' => [
                    'View tracking',
                    'Follower growth',
                    'Sales reports',
                    'Export data',
                ],
            ],
        ];
    }

    /**
     * Get pricing plans data
     */
    protected function getPlans(): array
    {
        return [
            [
                'name' => 'Free',
                'price' => '$0',
                'period' => 'forever',
                'description' => 'Everything you need to get started',
                'features' => [
                    'Unlimited events',
                    'Custom subdomain',
                    'Mobile-friendly schedule',
                    'Basic analytics',
                    'Email support',
                    'Follower notifications',
                ],
                'cta' => 'Get Started',
                'highlighted' => false,
            ],
            [
                'name' => 'Pro',
                'price' => '$5',
                'period' => '/month',
                'description' => 'First year free, then $5/month',
                'features' => [
                    'Everything in Free, plus:',
                    'Custom domain',
                    'Remove Event Schedule branding',
                    'Priority support',
                    'Advanced analytics',
                    'Custom CSS styling',
                    'API access',
                ],
                'cta' => 'Start Free Trial',
                'highlighted' => true,
            ],
        ];
    }

    /**
     * Get ticketing features data
     */
    protected function getTicketFeatures(): array
    {
        return [
            [
                'title' => 'QR Code Tickets',
                'icon' => 'qrcode',
                'description' => 'Every ticket includes a unique QR code for quick and easy check-in at your event.',
            ],
            [
                'title' => 'Multiple Ticket Types',
                'icon' => 'layers',
                'description' => 'Create different ticket tiers: general admission, VIP, early bird, and more.',
            ],
            [
                'title' => 'Secure Payments',
                'icon' => 'shield',
                'description' => 'Accept credit cards securely with Stripe. Funds go directly to your account.',
            ],
            [
                'title' => 'Real-time Sales',
                'icon' => 'chart',
                'description' => 'Track ticket sales as they happen. See who bought tickets and manage attendees.',
            ],
            [
                'title' => 'Mobile Scanner',
                'icon' => 'scan',
                'description' => 'Use your phone to scan tickets at the door. No special hardware required.',
            ],
            [
                'title' => 'Automatic Emails',
                'icon' => 'mail',
                'description' => 'Buyers receive confirmation emails with their tickets automatically.',
            ],
        ];
    }
}
