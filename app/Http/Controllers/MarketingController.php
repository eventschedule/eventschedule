<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
     * Why Create Account page
     */
    public function whyCreateAccount(Request $request)
    {
        if ($request->has('lang') && is_valid_language_code($request->lang)) {
            app()->setLocale($request->lang);
        }

        return view('marketing.why-create-account');
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
     * Newsletters page
     */
    public function newsletters()
    {
        return view('marketing.newsletters');
    }

    /**
     * Recurring Events page
     */
    public function recurringEvents()
    {
        return view('marketing.recurring-events');
    }

    /**
     * Fan Videos page
     */
    public function fanVideos()
    {
        return view('marketing.fan-videos');
    }

    /**
     * Boost page
     */
    public function boost()
    {
        return view('marketing.boost');
    }

    /**
     * Embed Calendar page
     */
    public function embedCalendar()
    {
        return view('marketing.embed-calendar');
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
     * For Webinars page
     */
    public function forWebinars()
    {
        return view('marketing.for-webinars');
    }

    /**
     * For Live Concerts page
     */
    public function forLiveConcerts()
    {
        return view('marketing.for-live-concerts');
    }

    /**
     * For Online Classes page
     */
    public function forOnlineClasses()
    {
        return view('marketing.for-online-classes');
    }

    /**
     * For Virtual Conferences page
     */
    public function forVirtualConferences()
    {
        return view('marketing.for-virtual-conferences');
    }

    /**
     * For Live Q&A Sessions page
     */
    public function forLiveQaSessions()
    {
        return view('marketing.for-live-qa-sessions');
    }

    /**
     * For Watch Parties sub-audience page
     */
    public function forWatchParties()
    {
        return view('marketing.for-watch-parties');
    }

    /**
     * For AI Agents page
     */
    public function forAiAgents()
    {
        return view('marketing.for-ai-agents');
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
     * Compare vs Eventbrite page
     */
    public function compareEventbrite()
    {
        return view('marketing.compare-single', $this->getComparisonData('eventbrite'));
    }

    /**
     * Compare vs Luma page
     */
    public function compareLuma()
    {
        return view('marketing.compare-single', $this->getComparisonData('luma'));
    }

    /**
     * Compare vs Ticket Tailor page
     */
    public function compareTicketTailor()
    {
        return view('marketing.compare-single', $this->getComparisonData('ticket-tailor'));
    }

    /**
     * Compare vs Google Calendar page
     */
    public function compareGoogleCalendar()
    {
        return view('marketing.compare-single', $this->getComparisonData('google-calendar'));
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
        return view('marketing.docs.getting-started', $this->getDocNavigation('marketing.docs.getting_started'));
    }

    /**
     * Creating Schedules documentation page
     */
    public function docsCreatingSchedules()
    {
        return view('marketing.docs.creating-schedules', $this->getDocNavigation('marketing.docs.creating_schedules'));
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

        return view('marketing.docs.schedule-basics', array_merge([
            'customFieldsData' => $customFieldsData,
        ], $this->getDocNavigation('marketing.docs.schedule_basics')));
    }

    /**
     * Schedule Styling documentation page
     */
    public function docsScheduleStyling()
    {
        return view('marketing.docs.schedule-styling', $this->getDocNavigation('marketing.docs.schedule_styling'));
    }

    /**
     * Creating Events documentation page
     */
    public function docsCreatingEvents()
    {
        return view('marketing.docs.creating-events', $this->getDocNavigation('marketing.docs.creating_events'));
    }

    /**
     * Sharing documentation page
     */
    public function docsSharing()
    {
        return view('marketing.docs.sharing', $this->getDocNavigation('marketing.docs.sharing'));
    }

    /**
     * Tickets documentation page
     */
    public function docsTickets()
    {
        return view('marketing.docs.tickets', $this->getDocNavigation('marketing.docs.tickets'));
    }

    /**
     * Newsletters documentation page
     */
    public function docsNewsletters()
    {
        return view('marketing.docs.newsletters', $this->getDocNavigation('marketing.docs.newsletters'));
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

        return view('marketing.docs.event-graphics', array_merge([
            'customFieldsData' => $customFieldsData,
        ], $this->getDocNavigation('marketing.docs.event_graphics')));
    }

    /**
     * Analytics documentation page
     */
    public function docsAnalytics()
    {
        return view('marketing.docs.analytics', $this->getDocNavigation('marketing.docs.analytics'));
    }

    /**
     * Account Settings documentation page
     */
    public function docsAccountSettings()
    {
        return view('marketing.docs.account-settings', $this->getDocNavigation('marketing.docs.account_settings'));
    }

    /**
     * Availability Calendar documentation page
     */
    public function docsAvailability()
    {
        return view('marketing.docs.availability', $this->getDocNavigation('marketing.docs.availability'));
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
     * Get comparison data for individual competitor pages
     */
    private function getComparisonData(string $competitor): array
    {
        $competitors = [
            'eventbrite' => [
                'name' => 'Eventbrite',
                'key' => 'eventbrite',
                'slug' => 'eventbrite-alternative',
                'tagline' => 'A simpler, more affordable alternative to Eventbrite with zero platform fees.',
                'description' => 'Compare Event Schedule with Eventbrite. Zero platform fees, open source, and AI-powered event management.',
                'keywords' => 'eventbrite alternative, eventbrite alternative free, free event platform, no platform fees ticketing, eventbrite competitor',
                'about' => 'Eventbrite is one of the largest event ticketing platforms, widely used for conferences, festivals, and community events. It offers a comprehensive suite of tools for event promotion and ticket sales.',
                'competitor_strengths' => [
                    'Large marketplace with built-in event discovery and audience',
                    'Comprehensive event promotion and marketing tools',
                    'Established brand with wide consumer recognition',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free to publish events', true],
                        ['Paid plan price', '$5/mo (first year free)', 'Free (fees on tickets)', true],
                        ['Platform fees', '0%', '3.7% + $1.79/ticket', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Built-in (included above)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No native 2-way sync', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Yes', false],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Pro)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Custom fields', 'Yes (Free)', 'Yes', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Pro)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Team collaboration', 'Yes (Free)', 'Yes', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes', false],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => '0% Platform Fees',
                        'description' => 'Eventbrite charges 3.7% + $1.79 per ticket. With Event Schedule, you keep 100% of your revenue minus standard Stripe processing.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Custom Domain',
                        'description' => 'Use your own domain for your event pages. Eventbrite does not offer custom domain support at any price.',
                        'icon' => 'globe',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format and our AI extracts all the details automatically. Unique to Event Schedule.',
                        'icon' => 'ai',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with no vendor lock-in. Selfhost on your own server or use our hosted platform.',
                        'icon' => 'code',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google Calendar and CalDAV sync included free. Eventbrite lacks native two-way calendar sync.',
                        'icon' => 'calendar',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Event Graphics',
                        'description' => 'Generate shareable event graphics automatically. No design skills needed, a feature unique to Event Schedule.',
                        'icon' => 'image',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                    ['name' => 'Google Calendar', 'route' => 'marketing.compare_google_calendar'],
                ],
            ],
            'luma' => [
                'name' => 'Luma',
                'key' => 'luma',
                'slug' => 'luma-alternative',
                'tagline' => 'A powerful open source alternative to Luma at a fraction of the price.',
                'description' => 'Compare Event Schedule with Luma. Get custom domains, zero platform fees, and open source flexibility for $5/mo instead of $59/mo.',
                'keywords' => 'luma alternative, luma alternative open source, lu.ma alternative, event platform comparison, luma competitor',
                'about' => 'Luma (lu.ma) is a modern event platform popular with tech communities and creators. It offers sleek event pages, built-in video streaming, and community features.',
                'competitor_strengths' => [
                    'Polished, modern event page design with strong visual focus',
                    'Built-in video streaming for virtual events',
                    'Popular in tech and startup communities with strong network effects',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (forever)', false],
                        ['Paid plan price', '$5/mo (first year free)', '$59/mo', true],
                        ['Platform fees', '0%', '5% (free plan), 0% (Plus)', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Built-in', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'Yes', false],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Yes', false],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Pro)', 'Yes (Plus)', true],
                        ['Remove branding', 'Yes (Pro)', 'Yes (Plus)', true],
                        ['Custom fields', 'Yes (Free)', 'Yes', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Pro)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Team collaboration', 'Yes (Free)', '3 admins (free), 5 (Plus)', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (Plus)', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => '12x More Affordable',
                        'description' => 'Event Schedule Pro costs $5/mo with the first year free. Luma Plus costs $59/mo for comparable features.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => '0% Platform Fees',
                        'description' => 'Luma charges 5% on their free plan. Event Schedule never takes a cut of your ticket sales at any tier.',
                        'icon' => 'percent',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format and our AI extracts all the details automatically. Not available on Luma.',
                        'icon' => 'ai',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with no vendor lock-in. Selfhost on your own server for complete data ownership.',
                        'icon' => 'code',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'CalDAV Sync',
                        'description' => 'Sync with any CalDAV-compatible calendar app including Apple Calendar. Not available on Luma.',
                        'icon' => 'calendar',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Event Graphics',
                        'description' => 'Generate shareable event graphics automatically. No design skills needed, a feature unique to Event Schedule.',
                        'icon' => 'image',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                    ['name' => 'Google Calendar', 'route' => 'marketing.compare_google_calendar'],
                ],
            ],
            'ticket-tailor' => [
                'name' => 'Ticket Tailor',
                'key' => 'ticket-tailor',
                'slug' => 'ticket-tailor-alternative',
                'tagline' => 'A flat-rate alternative to Ticket Tailor with zero per-ticket fees.',
                'description' => 'Compare Event Schedule with Ticket Tailor. Get zero platform fees, open source flexibility, and AI features for a flat $5/mo.',
                'keywords' => 'ticket tailor alternative, ticket tailor alternative free, ticketing platform comparison, no per-ticket fees, ticket tailor competitor',
                'about' => 'Ticket Tailor is an independent ticketing platform focused on affordable event ticketing. It offers a straightforward per-ticket pricing model and supports various payment processors.',
                'competitor_strengths' => [
                    'Simple, transparent per-ticket pricing model',
                    'Supports multiple payment processors (Stripe, PayPal, Square)',
                    'Strong focus on ticketing with robust box office features',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free events only', true],
                        ['Paid plan price', '$5/mo (first year free)', 'From $0.28/ticket', true],
                        ['Platform fees', '0%', '$0.28 to $0.60/ticket', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Stripe/PayPal/Square', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'No', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Pro)', 'Yes (paid)', true],
                        ['Remove branding', 'Yes (Pro)', 'Yes (paid)', true],
                        ['Custom fields', 'Yes (Free)', 'Yes', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Pro)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Team collaboration', 'Yes (Free)', 'Yes', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes', false],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Flat-Rate Pricing',
                        'description' => 'Pay $5/mo flat instead of $0.28 to $0.60 per ticket. The more tickets you sell, the more you save.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google Calendar and CalDAV sync included free. Ticket Tailor does not offer calendar sync.',
                        'icon' => 'calendar',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format and our AI extracts all the details automatically. Not available on Ticket Tailor.',
                        'icon' => 'ai',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with no vendor lock-in. Selfhost on your own server for complete data ownership.',
                        'icon' => 'code',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Newsletters',
                        'description' => 'Built-in newsletter system to keep your audience engaged. Ticket Tailor does not offer newsletters.',
                        'icon' => 'mail',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Event Graphics',
                        'description' => 'Generate shareable event graphics automatically. No design skills needed, a feature unique to Event Schedule.',
                        'icon' => 'image',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Google Calendar', 'route' => 'marketing.compare_google_calendar'],
                ],
            ],
            'google-calendar' => [
                'name' => 'Google Calendar',
                'key' => 'google-calendar',
                'slug' => 'google-calendar-alternative',
                'tagline' => 'A purpose-built event platform vs a general scheduling tool.',
                'description' => 'Compare Event Schedule with Google Calendar. Get public event pages, ticketing, AI features, and more - everything Google Calendar wasn\'t designed for.',
                'keywords' => 'google calendar alternative, google calendar for events, event calendar platform, public event calendar, google calendar vs event platform',
                'about' => 'Google Calendar is a popular personal scheduling tool used by billions worldwide. It excels at managing appointments, meetings, and personal reminders with deep Google ecosystem integration. The good news? You don\'t have to choose - Event Schedule syncs with Google Calendar, so you can use both together.',
                'competitor_strengths' => [
                    'Free and ubiquitous with billions of existing users',
                    'Deep Google ecosystem integration (Gmail, Meet, Drive)',
                    'Excellent mobile apps with push notifications',
                ],
                'sections' => [
                    'Core Purpose' => [
                        ['Public event pages', 'Yes (Free)', 'No', true],
                        ['Event discovery & SEO', 'Yes (Free)', 'No', true],
                        ['Personal scheduling', 'No', 'Yes', false],
                        ['Meeting invites', 'No', 'Yes', false],
                    ],
                    'Ticketing & Payments' => [
                        ['Ticket sales', 'Yes (Pro)', 'No', true],
                        ['Payment processing', 'Stripe integration', 'No', true],
                        ['QR code check-in', 'Yes (Pro)', 'No', true],
                        ['Platform fees', '0%', 'N/A', true],
                    ],
                    'Event Features' => [
                        ['AI event parsing', 'Yes (Pro)', 'No', true],
                        ['Event graphics generation', 'Yes (Pro)', 'No', true],
                        ['Rich descriptions (Markdown)', 'Yes (Free)', 'Plain text only', true],
                        ['Custom fields', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                    ],
                    'Sharing & Promotion' => [
                        ['Shareable event pages', 'Yes (Free)', 'No', true],
                        ['Social sharing images', 'Yes (Pro)', 'No', true],
                        ['Newsletter integration', 'Yes (Free)', 'No', true],
                        ['Embeddable calendars', 'Yes (customizable)', 'Yes (limited styling)', true],
                    ],
                    'Organization' => [
                        ['Sub-schedules/categories', 'Yes (Free)', 'Multiple calendars', true],
                        ['Team collaboration', 'Yes (Free)', 'Yes', false],
                        ['Built-in analytics', 'Yes (Free)', 'No', true],
                    ],
                    'Platform' => [
                        ['Custom domain', 'Yes (Pro)', 'No', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                        ['Google Calendar sync', 'Yes (Free)', 'N/A', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Built for Public Events',
                        'description' => 'Google Calendar is for personal scheduling. Event Schedule is purpose-built for sharing events with the world - with SEO-optimized public pages.',
                        'icon' => 'globe',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Ticketing & Payments',
                        'description' => 'Sell tickets directly with Stripe integration and QR code check-in. Google Calendar has no payment or ticketing capabilities whatsoever.',
                        'icon' => 'dollar',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format and our AI extracts dates, times, and descriptions automatically. Google Calendar requires tedious manual entry.',
                        'icon' => 'ai',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Event Graphics',
                        'description' => 'Generate custom, shareable event graphics automatically. Perfect for social media promotion - something Google Calendar simply cannot do.',
                        'icon' => 'image',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                    [
                        'title' => 'Professional Embeds',
                        'description' => 'Embed fully customizable calendars on your website. Google Calendar embeds are rigid and difficult to style to match your brand.',
                        'icon' => 'code',
                        'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with selfhosting option. Own your data completely, unlike Google\'s proprietary ecosystem.',
                        'icon' => 'code',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                ],
            ],
        ];

        return $competitors[$competitor];
    }

    /**
     * Get prev/next navigation data for a user guide doc page
     */
    protected function getDocNavigation(string $currentRoute): array
    {
        $pages = [
            ['route' => 'marketing.docs.getting_started', 'title' => 'Getting Started'],
            ['route' => 'marketing.docs.schedule_basics', 'title' => 'Schedule Basics'],
            ['route' => 'marketing.docs.schedule_styling', 'title' => 'Schedule Styling'],
            ['route' => 'marketing.docs.creating_schedules', 'title' => 'Advanced Schedule Settings'],
            ['route' => 'marketing.docs.creating_events', 'title' => 'Creating Events'],
            ['route' => 'marketing.docs.sharing', 'title' => 'Sharing Your Schedule'],
            ['route' => 'marketing.docs.newsletters', 'title' => 'Newsletters'],
            ['route' => 'marketing.docs.tickets', 'title' => 'Selling Tickets'],
            ['route' => 'marketing.docs.event_graphics', 'title' => 'Event Graphics'],
            ['route' => 'marketing.docs.analytics', 'title' => 'Analytics'],
            ['route' => 'marketing.docs.account_settings', 'title' => 'Account Settings'],
            ['route' => 'marketing.docs.availability', 'title' => 'Availability Calendar'],
        ];

        $currentIndex = null;
        foreach ($pages as $index => $page) {
            if ($page['route'] === $currentRoute) {
                $currentIndex = $index;
                break;
            }
        }

        return [
            'prevDoc' => $currentIndex > 0 ? $pages[$currentIndex - 1] : null,
            'nextDoc' => $currentIndex < count($pages) - 1 ? $pages[$currentIndex + 1] : null,
        ];
    }

    /**
     * Demos page - showcase demo schedules grouped by category
     */
    public function demos()
    {
        $categories = $this->getDemoSchedulesByCategory();
        $allSchedules = collect($categories)->flatten(1);

        return view('marketing.demos', [
            'categories' => $categories,
            'scheduleCount' => $allSchedules->count(),
            'allSchedules' => $allSchedules->toArray(),
        ]);
    }

    /**
     * Get demo schedules organized by industry category
     * Hardcoded data - no database query needed
     * All URLs point to production eventschedule.com
     */
    protected function getDemoSchedulesByCategory(): array
    {
        return [
            'Fitness & Wellness' => [
                [
                    'subdomain' => 'meditationclasses',
                    'name' => 'Meditation Classes',
                    'description' => 'Daily guided sessions for mindfulness and calm',
                    'url' => 'https://meditationclasses.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_meditationclasses.png',
                    'header_image_url' => 'images/examples/header_meditationclasses.png',
                ],
                [
                    'subdomain' => 'weekendyogaretreat',
                    'name' => 'Weekend Yoga Retreat',
                    'description' => 'Multi-day weekend retreat with yoga classes',
                    'url' => 'https://weekendyogaretreat.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_weekendyogaretreat.jpg',
                    'header_image_url' => 'images/examples/header_weekendyogaretreat.jpeg',
                ],
                [
                    'subdomain' => 'hikingclub',
                    'name' => 'Hiking Club',
                    'description' => 'Weekly group hikes and outdoor adventures',
                    'url' => 'https://hikingclub.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_hikingclub.png',
                    'header_image_url' => 'images/examples/header_hikingclub.png',
                ],
            ],
            'Music & Entertainment' => [
                [
                    'subdomain' => 'battleofthebands',
                    'name' => 'Battle of the Bands',
                    'description' => 'Live competition showcasing local bands',
                    'url' => 'https://battleofthebands.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_battleofthebands.jpg',
                    'header_image_url' => 'images/examples/header_battleofthebands.jpg',
                ],
                [
                    'subdomain' => 'sufficientgroundscoffeemusic',
                    'name' => 'Sufficient Grounds',
                    'description' => 'Acoustic sets and open mic nights at a cafe',
                    'url' => 'https://sufficientgroundscoffeemusic.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_sufficientgroundscoffeemusic.jpg',
                    'header_image_url' => 'images/examples/header_sufficientgroundscoffeemusic.png',
                ],
                [
                    'subdomain' => 'villageidiot',
                    'name' => 'Village Idiot',
                    'description' => 'Weekly live music lineup at a neighborhood pub',
                    'url' => 'https://villageidiot.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_villageidiot.png',
                    'header_image_url' => 'images/examples/header_villageidiot.png',
                ],
            ],
            'Community & Recreation' => [
                [
                    'subdomain' => 'communityyouthgroup',
                    'name' => 'Community Youth Group',
                    'description' => 'Activities and meetups for young people',
                    'url' => 'https://communityyouthgroup.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_communityyouthgroup.png',
                    'header_image_url' => 'images/examples/header_communityyouthgroup.png',
                ],
                [
                    'subdomain' => 'karateclub',
                    'name' => 'Karate Club',
                    'description' => 'Martial arts classes for all skill levels',
                    'url' => 'https://karateclub.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_karateclub.jpg',
                    'header_image_url' => 'images/examples/header_karateclub.jpg',
                ],
                [
                    'subdomain' => 'countyfairgrounds',
                    'name' => 'County Fairgrounds',
                    'description' => 'Seasonal events, fairs, and community gatherings',
                    'url' => 'https://countyfairgrounds.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_countyfairgrounds.png',
                    'header_image_url' => 'images/examples/header_countyfairgrounds.jpg',
                ],
            ],
            'Creative & Workshops' => [
                [
                    'subdomain' => 'nateswoodworkingshop',
                    'name' => "Nate's Woodworking Shop",
                    'description' => 'Hands-on woodworking classes and projects',
                    'url' => 'https://nateswoodworkingshop.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_nateswoodworkingshop.png',
                    'header_image_url' => 'images/examples/header_nateswoodworkingshop.png',
                ],
                [
                    'subdomain' => 'painting',
                    'name' => 'Painting',
                    'description' => 'Painting sessions for beginners and artists',
                    'url' => 'https://painting.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_painting.jpg',
                    'header_image_url' => 'images/examples/header_painting.jpg',
                ],
                [
                    'subdomain' => 'pagesbooknookshop',
                    'name' => 'Pages Book Nook Shop',
                    'description' => 'Author readings, book clubs, and signings',
                    'url' => 'https://pagesbooknookshop.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_pagesbooknookshop.png',
                    'header_image_url' => 'images/examples/header_pagesbooknookshop.png',
                ],
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
