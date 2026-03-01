<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
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
        return view('marketing.about', [
            'githubStars' => $this->getGitHubStars(),
        ]);
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

    public function polls()
    {
        return view('marketing.polls');
    }

    /**
     * Boost page
     */
    public function boost()
    {
        return view('marketing.boost');
    }

    /**
     * Private Events page
     */
    public function privateEvents()
    {
        return view('marketing.private-events');
    }

    /**
     * Event Graphics page
     */
    public function eventGraphics()
    {
        return view('marketing.event-graphics');
    }

    /**
     * Embed Calendar page
     */
    public function embedCalendar()
    {
        return view('marketing.embed-calendar');
    }

    /**
     * White Label page
     */
    public function whiteLabel()
    {
        return view('marketing.white-label');
    }

    /**
     * Custom CSS page
     */
    public function customCss()
    {
        return view('marketing.custom-css');
    }

    /**
     * Custom Domain page
     */
    public function customDomain()
    {
        return view('marketing.custom-domain');
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
        return view('marketing.open-source', [
            'githubStars' => $this->getGitHubStars(),
        ]);
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
     * Compare vs Meetup page
     */
    public function compareMeetup()
    {
        return view('marketing.compare-single', $this->getComparisonData('meetup'));
    }

    /**
     * Compare vs Dice page
     */
    public function compareDice()
    {
        return view('marketing.compare-single', $this->getComparisonData('dice'));
    }

    /**
     * Compare vs Brown Paper Tickets page
     */
    public function compareBrownPaperTickets()
    {
        return view('marketing.compare-single', $this->getComparisonData('brown-paper-tickets'));
    }

    /**
     * Compare vs Splash page
     */
    public function compareSplash()
    {
        return view('marketing.compare-single', $this->getComparisonData('splash'));
    }

    /**
     * Contact page
     */
    public function contact()
    {
        return view('marketing.contact', [
            'githubStars' => $this->getGitHubStars(),
        ]);
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
        return view('marketing.selfhost', [
            'githubStars' => $this->getGitHubStars(),
        ]);
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
        return view('marketing.docs.index', [
            'searchIndex' => $this->getDocSearchIndex(),
        ]);
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
        $customFieldsData = [];

        if (auth()->check()) {
            $user = auth()->user();
            $roles = $user->member()->get();

            foreach ($roles as $role) {
                if ($role->isPro()) {
                    $fields = $role->getEventCustomFields();
                    if (! empty($fields)) {
                        $customFieldsData[] = [
                            'role_name' => $role->name,
                            'fields' => $fields,
                        ];
                    }
                }
            }
        }

        return view('marketing.docs.creating-schedules', array_merge([
            'customFieldsData' => $customFieldsData,
        ], $this->getDocNavigation('marketing.docs.creating_schedules')));
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
                if ($role->isPro()) {
                    $fields = $role->getEventCustomFields();
                    if (! empty($fields)) {
                        $customFieldsData[] = [
                            'role_name' => $role->name,
                            'fields' => $fields,
                        ];
                    }
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
     * Managing Schedules documentation page
     */
    public function docsManagingSchedules()
    {
        return view('marketing.docs.managing-schedules', $this->getDocNavigation('marketing.docs.managing_schedules'));
    }

    /**
     * Boost documentation page
     */
    public function docsBoost()
    {
        return view('marketing.docs.boost', $this->getDocNavigation('marketing.docs.boost'));
    }

    /**
     * AI Import documentation page
     */
    public function docsAiImport()
    {
        return view('marketing.docs.ai-import', $this->getDocNavigation('marketing.docs.ai_import'));
    }

    /**
     * Scan Agenda documentation page
     */
    public function docsScanAgenda()
    {
        return view('marketing.docs.scan-agenda', $this->getDocNavigation('marketing.docs.scan_agenda'));
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
     * SaaS setup documentation page
     */
    public function docsSaasSetup()
    {
        return view('marketing.docs.saas.setup');
    }

    /**
     * SaaS custom domains documentation page
     */
    public function docsSaasCustomDomains()
    {
        return view('marketing.docs.saas.custom-domains');
    }

    /**
     * SaaS Twilio integration documentation page
     */
    public function docsSaasTwilio()
    {
        return view('marketing.docs.saas.twilio');
    }

    /**
     * Boost documentation page
     */
    public function docsSelfhostBoost()
    {
        return view('marketing.docs.selfhost.boost');
    }

    /**
     * Admin panel documentation page
     */
    public function docsSelfhostAdmin()
    {
        return view('marketing.docs.selfhost.admin');
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

    /**
     * Email setup documentation page
     */
    public function docsSelfhostEmail()
    {
        return view('marketing.docs.selfhost.email');
    }

    /**
     * AI setup documentation page
     */
    public function docsSelfhostAi()
    {
        return view('marketing.docs.selfhost.ai');
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

    public function docsDeveloperWebhooks()
    {
        return view('marketing.docs.developer.webhooks');
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
                'description' => '7-day free trial, then $5/month',
                'features' => [
                    'Everything in Free, plus:',
                    'Custom domain',
                    'Remove Event Schedule branding',
                    'Priority support',
                    'Advanced analytics',
                    'Custom CSS styling',
                    'Event polls',
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
                        ['Paid plan price', '$5/mo (7-day free trial)', 'Free (fees on tickets)', true],
                        ['Platform fees', '0%', '3.7% + $1.79/ticket', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Built-in (included above)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No native 2-way sync', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Yes', false],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
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
                    ['name' => 'Meetup', 'route' => 'marketing.compare_meetup'],
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
                        ['Paid plan price', '$5/mo (7-day free trial)', '$59/mo', true],
                        ['Platform fees', '0%', '5% (free plan), 0% (Plus)', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Built-in', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'Yes', false],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Yes', false],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Yes (Plus)', true],
                        ['Remove branding', 'Yes (Pro)', 'Yes (Plus)', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', '3 admins (free), 5 (Plus)', true],
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
                        'description' => 'Event Schedule Pro costs $5/mo with a 7-day free trial. Luma Plus costs $59/mo for comparable features.',
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
                        ['Paid plan price', '$5/mo (7-day free trial)', 'From $0.28/ticket', true],
                        ['Platform fees', '0%', '$0.28 to $0.60/ticket', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Stripe/PayPal/Square', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'No', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Yes (paid)', true],
                        ['Remove branding', 'Yes (Pro)', 'Yes (paid)', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
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
                        ['Free event RSVP', 'Yes (Free)', 'No', true],
                        ['Platform fees', '0%', 'N/A', true],
                    ],
                    'Event Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics generation', 'Yes (Pro)', 'No', true],
                        ['Rich descriptions (Markdown)', 'Yes (Free)', 'Plain text only', true],
                        ['Custom fields', 'Yes (Pro)', 'No', true],
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
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                        ['Built-in analytics', 'Yes (Free)', 'No', true],
                    ],
                    'Platform' => [
                        ['Custom domain', 'Yes (Enterprise)', 'No', true],
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
            'meetup' => [
                'name' => 'Meetup',
                'key' => 'meetup',
                'slug' => 'meetup-alternative',
                'tagline' => 'A free, open source alternative to Meetup with zero fees and full customization.',
                'description' => 'Compare Event Schedule with Meetup. Get zero platform fees, custom domains, and open source flexibility without Meetup\'s subscription costs.',
                'keywords' => 'meetup alternative, meetup alternative free, free event platform, meetup competitor, community events platform',
                'about' => 'Meetup is a social platform for organizing and discovering local community events and groups. It connects people with shared interests through in-person and online gatherings.',
                'competitor_strengths' => [
                    'Large built-in community and event discovery marketplace',
                    'Strong network effects with millions of active users',
                    'Social features with member profiles, RSVPs, and group discussions',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (limited)', true],
                        ['Paid plan price', '$5/mo (7-day free trial)', 'From $14.99/mo (organizer)', true],
                        ['Platform fees', '0%', '0% (but subscription required)', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Built-in (for paid events)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes (paid events)', false],
                        ['QR check-ins', 'Yes (Pro)', 'No', true],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'Add to calendar only', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Group messaging only', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Custom fields', 'Yes (Pro)', 'No', true],
                        ['Custom themes/styling', 'Yes (Pro)', 'No', true],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Co-organizers (paid)', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (limited)', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'No Organizer Fee',
                        'description' => 'Meetup charges organizers $14.99 to $29.99/mo just to host a group. Event Schedule is free forever with optional $5/mo Pro upgrade - saving you up to $360/year.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Own Your Community',
                        'description' => 'On Meetup, your members belong to Meetup - not you. With Event Schedule, your audience is yours. Use your own domain, branding, and newsletter list.',
                        'icon' => 'globe',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'Custom Branding',
                        'description' => 'Every Meetup group looks the same. Event Schedule lets you customize themes, colors, backgrounds, and use your own domain for a unique brand identity.',
                        'icon' => 'image',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'Embeddable Calendar',
                        'description' => 'Embed your event calendar directly on your website. Meetup has no embed option, forcing visitors to leave your site.',
                        'icon' => 'calendar',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'QR Check-Ins',
                        'description' => 'Scan attendees in at the door with QR codes. Meetup has no built-in check-in system for tracking who actually showed up.',
                        'icon' => 'ticket',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Fan Engagement',
                        'description' => 'Let your community share videos and comments on events. Build engagement that goes beyond RSVPs - something Meetup cannot offer.',
                        'icon' => 'ai',
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
            'dice' => [
                'name' => 'DICE',
                'key' => 'dice',
                'slug' => 'dice-alternative',
                'tagline' => 'A transparent, fee-free alternative to DICE for event organizers.',
                'description' => 'Compare Event Schedule with DICE. Get zero platform fees, open source flexibility, and full control over your event pages.',
                'keywords' => 'dice alternative, dice fm alternative, dice ticketing alternative, dice competitor, event ticketing platform',
                'about' => 'DICE is a mobile-first ticketing platform focused on live music, nightlife, and entertainment events. It handles ticket distribution, fan engagement, and event discovery through its app.',
                'competitor_strengths' => [
                    'Strong focus on live music and nightlife events',
                    'Mobile-first app with curated event discovery',
                    'Anti-scalping technology with mobile-only tickets',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free for organizers', false],
                        ['Paid plan price', '$5/mo (7-day free trial)', 'Free (fees on buyers)', true],
                        ['Platform fees', '0%', 'Service fee on buyers', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Built-in', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes (app-based)', false],
                        ['Online events', 'Yes (Free)', 'Yes (livestream)', false],
                        ['Recurring events', 'Yes (Free)', 'Limited', true],
                        ['Free event RSVP', 'Yes (Free)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Push notifications', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Custom event pages', 'Yes (full control)', 'Template-based', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Widget only', true],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Limited', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Web-First Access',
                        'description' => 'DICE requires fans to download their app to buy tickets. Event Schedule works in any browser - no app install required, no friction for your audience.',
                        'icon' => 'globe',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Direct Payments',
                        'description' => 'With DICE, they control the money and pay you later. Event Schedule connects directly to your Stripe account - funds go straight to you.',
                        'icon' => 'dollar',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google Calendar and CalDAV sync keeps your schedule always up to date. DICE has no calendar integration whatsoever.',
                        'icon' => 'calendar',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'Fan Videos & Comments',
                        'description' => 'Let fans share videos and comments on your events, building a community around your shows. DICE offers no fan-generated content features.',
                        'icon' => 'image',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Custom Event Pages',
                        'description' => 'Design your event pages with custom themes, colors, backgrounds, and your own domain. DICE locks every event into the same app template.',
                        'icon' => 'code',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Newsletters',
                        'description' => 'Reach your audience directly with built-in newsletters. DICE limits you to push notifications that only work if fans have the app installed.',
                        'icon' => 'mail',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                ],
            ],
            'brown-paper-tickets' => [
                'name' => 'Brown Paper Tickets',
                'key' => 'brown-paper-tickets',
                'slug' => 'brown-paper-tickets-alternative',
                'tagline' => 'A modern, actively developed alternative to Brown Paper Tickets.',
                'description' => 'Compare Event Schedule with Brown Paper Tickets. Get zero platform fees, modern design, and reliable support.',
                'keywords' => 'brown paper tickets alternative, brown paper tickets replacement, bpt alternative, event ticketing platform, brown paper tickets competitor',
                'about' => 'Brown Paper Tickets is a ticketing platform that was known for its low fees and focus on independent events. The platform has faced service reliability issues in recent years, leading many organizers to seek alternatives.',
                'competitor_strengths' => [
                    'Historically low per-ticket fees for organizers',
                    'Long track record in the independent event space',
                    'Will-call and physical ticket options',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free to list', false],
                        ['Paid plan price', '$5/mo (7-day free trial)', 'Free (fees on tickets)', true],
                        ['Platform fees', '0%', '$0.99 + 5% per ticket (buyer-paid)', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Built-in', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Limited', true],
                        ['Online events', 'Yes (Free)', 'Limited', true],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Basic email', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Modern event pages', 'Yes (responsive, customizable)', 'Dated design', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Limited widget', true],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Limited', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                        ['Active development', 'Yes (regular updates)', 'Minimal updates', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Reliable & Active',
                        'description' => 'Brown Paper Tickets has faced major service outages and reliability issues. Event Schedule is actively developed with regular updates and dependable uptime.',
                        'icon' => 'globe',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Modern Design',
                        'description' => 'Brown Paper Tickets has a dated interface from another era. Event Schedule offers beautiful, mobile-responsive event pages with customizable themes and branding.',
                        'icon' => 'image',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'Fast Stripe Payouts',
                        'description' => 'BPT has been known for delayed payouts to organizers. Event Schedule connects directly to your Stripe account - funds go straight to you on Stripe\'s standard schedule.',
                        'icon' => 'dollar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google Calendar and CalDAV sync keeps everything in sync automatically. Brown Paper Tickets has no calendar integration at all.',
                        'icon' => 'calendar',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format and AI extracts dates, times, and descriptions automatically. Create events in seconds instead of filling out lengthy forms.',
                        'icon' => 'ai',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source so you can verify exactly how your data is handled. Selfhost for complete control, or trust our hosted platform with full transparency.',
                        'icon' => 'code',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                    ['name' => 'DICE', 'route' => 'marketing.compare_dice'],
                ],
            ],
            'splash' => [
                'name' => 'Splash',
                'key' => 'splash',
                'slug' => 'splash-alternative',
                'tagline' => 'A simpler, more affordable alternative to Splash for event marketing.',
                'description' => 'Compare Event Schedule with Splash. Get zero platform fees, open source flexibility, and powerful features without enterprise pricing.',
                'keywords' => 'splash alternative, splash event marketing alternative, splash competitor, event management platform, splash replacement',
                'about' => 'Splash is an enterprise event marketing platform focused on branded event experiences, registration, and attendee engagement. It targets mid-to-large organizations with custom event pages and marketing automation.',
                'competitor_strengths' => [
                    'Enterprise-grade event marketing and branding tools',
                    'Advanced attendee engagement and analytics',
                    'Custom event page designer with drag-and-drop builder',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'No (custom pricing)', true],
                        ['Paid plan price', '$5/mo (7-day free trial)', 'Enterprise pricing', true],
                        ['Platform fees', '0%', 'Included in subscription', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Built-in', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'Limited', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Yes (enterprise)', false],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Yes (enterprise)', false],
                        ['Remove branding', 'Yes (Pro)', 'Yes (enterprise)', false],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Embeddable calendar', 'Yes (Free)', 'Registration widget', true],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (enterprise)', false],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                        ['Setup time', 'Minutes', 'Weeks (implementation)', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'No Sales Process',
                        'description' => 'Splash requires contacting sales and negotiating an enterprise contract. Event Schedule lets you sign up and start creating events immediately - no calls, no demos, no waiting.',
                        'icon' => 'globe',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Instant Setup',
                        'description' => 'Splash implementations can take weeks of onboarding. Event Schedule is ready in minutes - create your schedule, add events, and share with your audience right away.',
                        'icon' => 'calendar',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'Transparent $5/mo',
                        'description' => 'Splash hides pricing behind "contact us" forms. Event Schedule is free forever, with Pro at a transparent $5/mo - no surprise invoices or annual commitments.',
                        'icon' => 'dollar',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format and AI extracts everything automatically. Skip the lengthy setup process that enterprise platforms require.',
                        'icon' => 'ai',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Fan Engagement',
                        'description' => 'Let attendees share videos and comments on your events, building community that goes beyond registration. Not available on Splash.',
                        'icon' => 'image',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with selfhosting option. Own your data completely - no enterprise contract needed to control your event platform.',
                        'icon' => 'code',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Meetup', 'route' => 'marketing.compare_meetup'],
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
            ['route' => 'marketing.docs.creating_schedules', 'title' => 'Creating Schedules'],
            ['route' => 'marketing.docs.schedule_styling', 'title' => 'Schedule Styling'],
            ['route' => 'marketing.docs.managing_schedules', 'title' => 'Managing Schedules'],
            ['route' => 'marketing.docs.creating_events', 'title' => 'Creating Events'],
            ['route' => 'marketing.docs.ai_import', 'title' => 'AI Import'],
            ['route' => 'marketing.docs.sharing', 'title' => 'Sharing Your Schedule'],
            ['route' => 'marketing.docs.newsletters', 'title' => 'Newsletters'],
            ['route' => 'marketing.docs.tickets', 'title' => 'Selling Tickets'],
            ['route' => 'marketing.docs.event_graphics', 'title' => 'Event Graphics'],
            ['route' => 'marketing.docs.analytics', 'title' => 'Analytics'],
            ['route' => 'marketing.docs.account_settings', 'title' => 'Account Settings'],
            ['route' => 'marketing.docs.scan_agenda', 'title' => 'Scan Agenda'],
            ['route' => 'marketing.docs.boost', 'title' => 'Boost'],
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

    /**
     * Search page
     */
    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));
        $schedules = collect();
        $events = collect();

        if (strlen($query) >= 2) {
            $escapedQuery = str_replace(['%', '_'], ['\\%', '\\_'], $query);

            $publicScheduleFilter = function ($q) {
                $q->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNotNull('email')
                            ->whereNotNull('email_verified_at');
                    })->orWhere(function ($q) {
                        $q->whereNotNull('phone')
                            ->whereNotNull('phone_verified_at');
                    });
                })
                    ->where('is_deleted', false)
                    ->where('is_unlisted', false)
                    ->whereNotNull('user_id');
            };

            $schedules = Role::where(function ($q) use ($escapedQuery) {
                $q->where('subdomain', 'like', $escapedQuery.'%')
                    ->orWhere('name', 'like', '%'.$escapedQuery.'%')
                    ->orWhere('city', 'like', '%'.$escapedQuery.'%')
                    ->orWhere('short_description', 'like', '%'.$escapedQuery.'%');
            })
                ->where($publicScheduleFilter)
                ->orderBy('name')
                ->limit(12)
                ->get();

            $events = Event::with(['roles'])
                ->where(function ($q) use ($escapedQuery) {
                    $q->where('name', 'like', '%'.$escapedQuery.'%')
                        ->orWhere('short_description', 'like', '%'.$escapedQuery.'%');
                })
                ->where(function ($q) {
                    $q->where('starts_at', '>=', Carbon::today())
                        ->orWhereNotNull('days_of_week');
                })
                ->where('is_private', false)
                ->whereHas('roles', $publicScheduleFilter)
                ->orderByRaw('starts_at IS NULL, starts_at ASC')
                ->limit(12)
                ->get();
        }

        return view('marketing.search', [
            'query' => $query,
            'searched' => strlen($query) >= 2,
            'schedules' => $schedules,
            'events' => $events,
        ]);
    }

    private function getGitHubStars(): ?int
    {
        $githubStars = cache()->get('github_stars');
        if ($githubStars === null) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'EventSchedule',
                ])->timeout(5)->get('https://api.github.com/repos/eventschedule/eventschedule');

                if ($response->successful()) {
                    $githubStars = $response->json('stargazers_count');
                    cache()->put('github_stars', $githubStars, 3600);
                }
            } catch (\Exception $e) {
                // Silently fail - star count won't show
            }
        }

        return $githubStars;
    }

    /**
     * Get the search index for the documentation pages.
     * Each entry has: page, section, description, url, category, keywords
     */
    protected function getDocSearchIndex(): array
    {
        $r = [
            'getting_started' => route('marketing.docs.getting_started'),
            'creating_schedules' => route('marketing.docs.creating_schedules'),
            'schedule_styling' => route('marketing.docs.schedule_styling'),
            'managing_schedules' => route('marketing.docs.managing_schedules'),
            'creating_events' => route('marketing.docs.creating_events'),
            'ai_import' => route('marketing.docs.ai_import'),
            'sharing' => route('marketing.docs.sharing'),
            'newsletters' => route('marketing.docs.newsletters'),
            'tickets' => route('marketing.docs.tickets'),
            'event_graphics' => route('marketing.docs.event_graphics'),
            'analytics' => route('marketing.docs.analytics'),
            'account_settings' => route('marketing.docs.account_settings'),
            'scan_agenda' => route('marketing.docs.scan_agenda'),
            'boost' => route('marketing.docs.boost'),
            'selfhost_installation' => route('marketing.docs.selfhost.installation'),
            'selfhost_stripe' => route('marketing.docs.selfhost.stripe'),
            'selfhost_google_calendar' => route('marketing.docs.selfhost.google_calendar'),
            'selfhost_email' => route('marketing.docs.selfhost.email'),
            'selfhost_ai' => route('marketing.docs.selfhost.ai'),
            'selfhost_admin' => route('marketing.docs.selfhost.admin'),
            'selfhost_boost' => route('marketing.docs.selfhost.boost'),
            'saas_setup' => route('marketing.docs.saas.setup'),
            'saas_custom_domains' => route('marketing.docs.saas.custom_domains'),
            'saas_twilio' => route('marketing.docs.saas.twilio'),
            'developer_api' => route('marketing.docs.developer.api'),
            'developer_webhooks' => route('marketing.docs.developer.webhooks'),
        ];

        return [
            // ===== USER GUIDE =====

            // Getting Started
            ['page' => 'Getting Started', 'section' => 'Create Your Account', 'description' => 'Register with email or Google login to get started.', 'url' => $r['getting_started'] . '#create-account', 'category' => 'User Guide', 'keywords' => 'register sign up'],
            ['page' => 'Getting Started', 'section' => 'Create Your Schedule', 'description' => 'Set up your first schedule with a unique URL.', 'url' => $r['getting_started'] . '#create-schedule', 'category' => 'User Guide', 'keywords' => 'new schedule setup'],
            ['page' => 'Getting Started', 'section' => 'Schedule Types', 'description' => 'Choose between Talent, Venue, and Curator schedule types.', 'url' => $r['getting_started'] . '#schedule-types', 'category' => 'User Guide', 'keywords' => 'talent venue curator type'],
            ['page' => 'Getting Started', 'section' => 'Customize Your Schedule', 'description' => 'Set up profile, location, display settings, and sub-schedules.', 'url' => $r['getting_started'] . '#customize', 'category' => 'User Guide', 'keywords' => 'customize personalize configure'],
            ['page' => 'Getting Started', 'section' => 'Frequently Asked Questions', 'description' => 'Common questions about schedules, URLs, and importing.', 'url' => $r['getting_started'] . '#faq', 'category' => 'User Guide', 'keywords' => 'faq help questions'],
            ['page' => 'Getting Started', 'section' => 'Next Steps', 'description' => 'Links to related documentation for further setup.', 'url' => $r['getting_started'] . '#next-steps', 'category' => 'User Guide', 'keywords' => ''],

            // Creating Schedules
            ['page' => 'Creating Schedules', 'section' => 'Schedule Types', 'description' => 'Choose the right schedule type for your use case.', 'url' => $r['creating_schedules'] . '#schedule-types', 'category' => 'User Guide', 'keywords' => 'talent venue curator'],
            ['page' => 'Creating Schedules', 'section' => 'Details', 'description' => 'Set name, description, website, and URL slug.', 'url' => $r['creating_schedules'] . '#details', 'category' => 'User Guide', 'keywords' => 'name description website slug'],
            ['page' => 'Creating Schedules', 'section' => 'Address', 'description' => 'Configure location settings for your schedule.', 'url' => $r['creating_schedules'] . '#address', 'category' => 'User Guide', 'keywords' => 'location map'],
            ['page' => 'Creating Schedules', 'section' => 'Contact Information', 'description' => 'Add contact methods to your schedule.', 'url' => $r['creating_schedules'] . '#contact-info', 'category' => 'User Guide', 'keywords' => 'email phone social contact'],
            ['page' => 'Creating Schedules', 'section' => 'Style', 'description' => 'Visual styling options for your schedule.', 'url' => $r['creating_schedules'] . '#style', 'category' => 'User Guide', 'keywords' => 'design theme appearance'],
            ['page' => 'Creating Schedules', 'section' => 'Sub-schedules', 'description' => 'Create categories to organize events within a schedule.', 'url' => $r['creating_schedules'] . '#subschedules', 'category' => 'User Guide', 'keywords' => 'groups categories organize'],
            ['page' => 'Creating Schedules', 'section' => 'Settings', 'description' => 'Configure general schedule settings.', 'url' => $r['creating_schedules'] . '#settings', 'category' => 'User Guide', 'keywords' => 'configuration options preferences'],
            ['page' => 'Creating Schedules', 'section' => 'General Settings', 'description' => 'Timezone, language, and default settings.', 'url' => $r['creating_schedules'] . '#settings-general', 'category' => 'User Guide', 'keywords' => 'timezone language defaults'],
            ['page' => 'Creating Schedules', 'section' => 'Custom Fields', 'description' => 'Add custom fields to your events.', 'url' => $r['creating_schedules'] . '#settings-custom-fields', 'category' => 'User Guide', 'keywords' => 'custom fields metadata'],
            ['page' => 'Creating Schedules', 'section' => 'Requests Settings', 'description' => 'Configure public event request submissions.', 'url' => $r['creating_schedules'] . '#settings-requests', 'category' => 'User Guide', 'keywords' => 'submissions public requests'],
            ['page' => 'Creating Schedules', 'section' => 'Advanced Settings', 'description' => 'Advanced schedule options and configuration.', 'url' => $r['creating_schedules'] . '#settings-advanced', 'category' => 'User Guide', 'keywords' => 'advanced options'],
            ['page' => 'Creating Schedules', 'section' => 'Auto Import', 'description' => 'Automatically import events from external sources.', 'url' => $r['creating_schedules'] . '#auto-import', 'category' => 'User Guide', 'keywords' => 'import automatic feed ical'],
            ['page' => 'Creating Schedules', 'section' => 'Integrations', 'description' => 'Connect with calendar and third-party services.', 'url' => $r['creating_schedules'] . '#integrations', 'category' => 'User Guide', 'keywords' => 'connect sync third-party'],
            ['page' => 'Creating Schedules', 'section' => 'Google Calendar', 'description' => 'Set up Google Calendar sync for your schedule.', 'url' => $r['creating_schedules'] . '#integrations-google', 'category' => 'User Guide', 'keywords' => 'google calendar sync'],
            ['page' => 'Creating Schedules', 'section' => 'CalDAV', 'description' => 'Set up CalDAV protocol integration.', 'url' => $r['creating_schedules'] . '#integrations-caldav', 'category' => 'User Guide', 'keywords' => 'caldav ical protocol'],
            ['page' => 'Creating Schedules', 'section' => 'Email Settings', 'description' => 'Configure email notifications for your schedule.', 'url' => $r['creating_schedules'] . '#email-settings', 'category' => 'User Guide', 'keywords' => 'email notifications sender'],

            // Schedule Styling
            ['page' => 'Schedule Styling', 'section' => 'Overview', 'description' => 'Introduction to schedule styling options.', 'url' => $r['schedule_styling'] . '#overview', 'category' => 'User Guide', 'keywords' => 'design appearance theme'],
            ['page' => 'Schedule Styling', 'section' => 'Event Layout', 'description' => 'Choose between grid and list layout for events.', 'url' => $r['schedule_styling'] . '#event-layout', 'category' => 'User Guide', 'keywords' => 'grid list layout display'],
            ['page' => 'Schedule Styling', 'section' => 'Profile Image', 'description' => 'Upload and set your profile or logo image.', 'url' => $r['schedule_styling'] . '#profile-image', 'category' => 'User Guide', 'keywords' => 'logo avatar photo'],
            ['page' => 'Schedule Styling', 'section' => 'Header Images', 'description' => 'Set header banners with presets or custom uploads.', 'url' => $r['schedule_styling'] . '#header-images', 'category' => 'User Guide', 'keywords' => 'banner cover header'],
            ['page' => 'Schedule Styling', 'section' => 'Background Options', 'description' => 'Choose solid color, gradient, or image backgrounds.', 'url' => $r['schedule_styling'] . '#backgrounds', 'category' => 'User Guide', 'keywords' => 'background color gradient image'],
            ['page' => 'Schedule Styling', 'section' => 'Color Scheme', 'description' => 'Select accent colors for buttons and interactive elements.', 'url' => $r['schedule_styling'] . '#color-scheme', 'category' => 'User Guide', 'keywords' => 'color accent theme palette'],
            ['page' => 'Schedule Styling', 'section' => 'Typography', 'description' => 'Choose custom fonts from Google Fonts.', 'url' => $r['schedule_styling'] . '#typography', 'category' => 'User Guide', 'keywords' => 'font text typeface google fonts'],
            ['page' => 'Schedule Styling', 'section' => 'Remove Branding', 'description' => 'Remove the "Powered by Event Schedule" badge (Pro).', 'url' => $r['schedule_styling'] . '#remove-branding', 'category' => 'User Guide', 'keywords' => 'branding badge powered by white label'],
            ['page' => 'Schedule Styling', 'section' => 'Custom CSS', 'description' => 'Add custom CSS for advanced styling (Pro).', 'url' => $r['schedule_styling'] . '#custom-css', 'category' => 'User Guide', 'keywords' => 'css stylesheet custom code'],
            ['page' => 'Schedule Styling', 'section' => 'Live Preview', 'description' => 'Preview styling changes in real time.', 'url' => $r['schedule_styling'] . '#live-preview', 'category' => 'User Guide', 'keywords' => 'preview real-time'],

            // Managing Schedules
            ['page' => 'Managing Schedules', 'section' => 'Overview', 'description' => 'Introduction to the schedule admin panel and tabs.', 'url' => $r['managing_schedules'] . '#overview', 'category' => 'User Guide', 'keywords' => 'admin panel dashboard'],
            ['page' => 'Managing Schedules', 'section' => 'Schedule', 'description' => 'Main calendar view with event management.', 'url' => $r['managing_schedules'] . '#schedule', 'category' => 'User Guide', 'keywords' => 'calendar events manage'],
            ['page' => 'Managing Schedules', 'section' => 'Videos', 'description' => 'Assign YouTube videos for curator schedules.', 'url' => $r['managing_schedules'] . '#videos', 'category' => 'User Guide', 'keywords' => 'youtube video curator'],
            ['page' => 'Managing Schedules', 'section' => 'Availability', 'description' => 'Set availability dates for talent schedules.', 'url' => $r['managing_schedules'] . '#availability', 'category' => 'User Guide', 'keywords' => 'available dates talent booking'],
            ['page' => 'Managing Schedules', 'section' => 'Requests', 'description' => 'Manage public event request submissions.', 'url' => $r['managing_schedules'] . '#requests', 'category' => 'User Guide', 'keywords' => 'submissions approve reject'],
            ['page' => 'Managing Schedules', 'section' => 'Profile', 'description' => 'Preview your schedule\'s public profile.', 'url' => $r['managing_schedules'] . '#profile', 'category' => 'User Guide', 'keywords' => 'public page preview'],
            ['page' => 'Managing Schedules', 'section' => 'Followers', 'description' => 'Manage followers and follow links.', 'url' => $r['managing_schedules'] . '#followers', 'category' => 'User Guide', 'keywords' => 'subscribers audience follow'],
            ['page' => 'Managing Schedules', 'section' => 'Team', 'description' => 'Manage team members and permissions.', 'url' => $r['managing_schedules'] . '#team', 'category' => 'User Guide', 'keywords' => 'members permissions collaborate'],
            ['page' => 'Managing Schedules', 'section' => 'Plan', 'description' => 'View and manage your subscription plan.', 'url' => $r['managing_schedules'] . '#plan', 'category' => 'User Guide', 'keywords' => 'subscription billing upgrade'],

            // Creating Events
            ['page' => 'Creating Events', 'section' => 'Manual Event Creation', 'description' => 'Create events manually with full control over details.', 'url' => $r['creating_events'] . '#manual', 'category' => 'User Guide', 'keywords' => 'add new event create'],
            ['page' => 'Creating Events', 'section' => 'Event Details', 'description' => 'Set event name, dates, times, and description.', 'url' => $r['creating_events'] . '#details', 'category' => 'User Guide', 'keywords' => 'name date time description'],
            ['page' => 'Creating Events', 'section' => 'Venue', 'description' => 'Add venue and location information to events.', 'url' => $r['creating_events'] . '#venue', 'category' => 'User Guide', 'keywords' => 'location place address map'],
            ['page' => 'Creating Events', 'section' => 'Participants', 'description' => 'Add performers, speakers, or participants.', 'url' => $r['creating_events'] . '#participants', 'category' => 'User Guide', 'keywords' => 'performers speakers artists lineup'],
            ['page' => 'Creating Events', 'section' => 'Recurring Events', 'description' => 'Set up events that repeat on a schedule.', 'url' => $r['creating_events'] . '#recurring', 'category' => 'User Guide', 'keywords' => 'repeat weekly monthly recurring'],
            ['page' => 'Creating Events', 'section' => 'Agenda', 'description' => 'Create agenda items and event parts.', 'url' => $r['creating_events'] . '#agenda', 'category' => 'User Guide', 'keywords' => 'parts itinerary lineup schedule'],
            ['page' => 'Creating Events', 'section' => 'Appearances on Other Schedules', 'description' => 'List your event on curator or talent schedules.', 'url' => $r['creating_events'] . '#schedules', 'category' => 'User Guide', 'keywords' => 'cross-list curator appearances'],
            ['page' => 'Creating Events', 'section' => 'Google Calendar Integration', 'description' => 'Sync events with Google Calendar.', 'url' => $r['creating_events'] . '#google-calendar', 'category' => 'User Guide', 'keywords' => 'google calendar sync'],
            ['page' => 'Creating Events', 'section' => 'WhatsApp Integration', 'description' => 'Add a WhatsApp link to your events.', 'url' => $r['creating_events'] . '#whatsapp', 'category' => 'User Guide', 'keywords' => 'whatsapp chat message'],
            ['page' => 'Creating Events', 'section' => 'Ticket Configuration', 'description' => 'Set up tickets for an event.', 'url' => $r['creating_events'] . '#tickets', 'category' => 'User Guide', 'keywords' => 'tickets pricing sell'],
            ['page' => 'Creating Events', 'section' => 'Privacy Settings', 'description' => 'Control event visibility and access.', 'url' => $r['creating_events'] . '#privacy', 'category' => 'User Guide', 'keywords' => 'private public hidden visibility'],
            ['page' => 'Creating Events', 'section' => 'Custom Fields', 'description' => 'Add custom information fields to events.', 'url' => $r['creating_events'] . '#custom-fields', 'category' => 'User Guide', 'keywords' => 'custom fields metadata'],
            ['page' => 'Creating Events', 'section' => 'Polls', 'description' => 'Create interactive polls for your events.', 'url' => $r['creating_events'] . '#polls', 'category' => 'User Guide', 'keywords' => 'voting survey poll'],
            ['page' => 'Creating Events', 'section' => 'Fan Content', 'description' => 'Manage user-submitted videos and content.', 'url' => $r['creating_events'] . '#fan-content', 'category' => 'User Guide', 'keywords' => 'user content videos submissions ugc'],

            // AI Import
            ['page' => 'AI Import', 'section' => 'Overview', 'description' => 'Use AI to automatically import events.', 'url' => $r['ai_import'] . '#ai-import', 'category' => 'User Guide', 'keywords' => 'ai artificial intelligence import parse'],
            ['page' => 'AI Import', 'section' => 'Importing from Text', 'description' => 'Paste event text and let AI extract the details.', 'url' => $r['ai_import'] . '#text-import', 'category' => 'User Guide', 'keywords' => 'text paste extract parse'],
            ['page' => 'AI Import', 'section' => 'Importing from Images', 'description' => 'Upload flyers and extract event information with AI.', 'url' => $r['ai_import'] . '#image-import', 'category' => 'User Guide', 'keywords' => 'image flyer photo scan ocr'],
            ['page' => 'AI Import', 'section' => 'Custom AI Prompts', 'description' => 'Create custom AI instructions for parsing events.', 'url' => $r['ai_import'] . '#custom-prompts', 'category' => 'User Guide', 'keywords' => 'prompt instructions customize'],

            // Sharing
            ['page' => 'Sharing', 'section' => 'Your Schedule URL', 'description' => 'Share your unique schedule URL with others.', 'url' => $r['sharing'] . '#schedule-url', 'category' => 'User Guide', 'keywords' => 'link url share'],
            ['page' => 'Sharing', 'section' => 'Embedding on Your Website', 'description' => 'Embed your schedule on external websites.', 'url' => $r['sharing'] . '#embed', 'category' => 'User Guide', 'keywords' => 'embed iframe widget website'],
            ['page' => 'Sharing', 'section' => 'Social Media Sharing', 'description' => 'Share your schedule on social media platforms.', 'url' => $r['sharing'] . '#social', 'category' => 'User Guide', 'keywords' => 'facebook twitter instagram social'],
            ['page' => 'Sharing', 'section' => 'Building Followers', 'description' => 'Grow your audience and follower base.', 'url' => $r['sharing'] . '#followers', 'category' => 'User Guide', 'keywords' => 'followers subscribers grow audience'],
            ['page' => 'Sharing', 'section' => 'Calendar Subscriptions', 'description' => 'Provide iCal and RSS feed URLs for subscribers.', 'url' => $r['sharing'] . '#calendar-feeds', 'category' => 'User Guide', 'keywords' => 'ical rss feed subscribe calendar'],
            ['page' => 'Sharing', 'section' => 'QR Codes', 'description' => 'Generate QR codes for your schedule.', 'url' => $r['sharing'] . '#qr-code', 'category' => 'User Guide', 'keywords' => 'qr code scan print'],
            ['page' => 'Sharing', 'section' => 'Embed Troubleshooting', 'description' => 'Fix common embedding issues.', 'url' => $r['sharing'] . '#troubleshooting', 'category' => 'User Guide', 'keywords' => 'troubleshoot fix embed problem'],

            // Newsletters
            ['page' => 'Newsletters', 'section' => 'Overview', 'description' => 'Send branded email newsletters to your audience.', 'url' => $r['newsletters'] . '#overview', 'category' => 'User Guide', 'keywords' => 'email newsletter campaign'],
            ['page' => 'Newsletters', 'section' => 'Newsletter Builder', 'description' => 'Use the three-tab builder: Content, Style, Settings.', 'url' => $r['newsletters'] . '#newsletter-builder', 'category' => 'User Guide', 'keywords' => 'builder editor compose'],
            ['page' => 'Newsletters', 'section' => 'Block Types', 'description' => 'Available content blocks: heading, text, events, button, image.', 'url' => $r['newsletters'] . '#block-types', 'category' => 'User Guide', 'keywords' => 'blocks content heading text button image'],
            ['page' => 'Newsletters', 'section' => 'Templates', 'description' => 'Pre-designed newsletter templates.', 'url' => $r['newsletters'] . '#templates', 'category' => 'User Guide', 'keywords' => 'template design preset'],
            ['page' => 'Newsletters', 'section' => 'Style Customization', 'description' => 'Customize colors, fonts, and button styles.', 'url' => $r['newsletters'] . '#style-customization', 'category' => 'User Guide', 'keywords' => 'color font button style design'],
            ['page' => 'Newsletters', 'section' => 'Recipients & Segments', 'description' => 'Choose audiences: followers, ticket buyers, or custom lists.', 'url' => $r['newsletters'] . '#recipients', 'category' => 'User Guide', 'keywords' => 'recipients segments audience list'],
            ['page' => 'Newsletters', 'section' => 'Sending', 'description' => 'Send now, schedule for later, or send a test.', 'url' => $r['newsletters'] . '#sending', 'category' => 'User Guide', 'keywords' => 'send schedule deliver'],
            ['page' => 'Newsletters', 'section' => 'A/B Testing', 'description' => 'Test different newsletter variants.', 'url' => $r['newsletters'] . '#ab-testing', 'category' => 'User Guide', 'keywords' => 'ab test split experiment'],
            ['page' => 'Newsletters', 'section' => 'Analytics', 'description' => 'Track newsletter performance and engagement.', 'url' => $r['newsletters'] . '#analytics', 'category' => 'User Guide', 'keywords' => 'opens clicks metrics performance'],
            ['page' => 'Newsletters', 'section' => 'Managing Newsletters', 'description' => 'Manage drafts, scheduled, and sent newsletters.', 'url' => $r['newsletters'] . '#managing', 'category' => 'User Guide', 'keywords' => 'drafts manage list'],

            // Tickets
            ['page' => 'Selling Tickets', 'section' => 'General Ticketing', 'description' => 'Overview of ticket sales setup and features.', 'url' => $r['tickets'] . '#general', 'category' => 'User Guide', 'keywords' => 'ticketing overview sell'],
            ['page' => 'Selling Tickets', 'section' => 'Creating Tickets', 'description' => 'Create ticket types for your events.', 'url' => $r['tickets'] . '#create-tickets', 'category' => 'User Guide', 'keywords' => 'create add ticket'],
            ['page' => 'Selling Tickets', 'section' => 'Ticket Types', 'description' => 'Configure different ticket type options.', 'url' => $r['tickets'] . '#ticket-types', 'category' => 'User Guide', 'keywords' => 'general vip types tiers'],
            ['page' => 'Selling Tickets', 'section' => 'Free Events', 'description' => 'Set up free events with no payment required.', 'url' => $r['tickets'] . '#free-events', 'category' => 'User Guide', 'keywords' => 'free no cost rsvp'],
            ['page' => 'Selling Tickets', 'section' => 'Payment Processing', 'description' => 'Configure payment methods for ticket sales.', 'url' => $r['tickets'] . '#payment', 'category' => 'User Guide', 'keywords' => 'payment stripe invoice ninja'],
            ['page' => 'Selling Tickets', 'section' => 'Invoice Ninja Modes', 'description' => 'Invoice mode vs. payment link mode for Invoice Ninja.', 'url' => $r['tickets'] . '#invoiceninja-modes', 'category' => 'User Guide', 'keywords' => 'invoice ninja mode payment link'],
            ['page' => 'Selling Tickets', 'section' => 'Additional Options', 'description' => 'Extra checkout and ticket options.', 'url' => $r['tickets'] . '#options', 'category' => 'User Guide', 'keywords' => 'options settings configuration'],
            ['page' => 'Selling Tickets', 'section' => 'Custom Checkout Fields', 'description' => 'Add custom fields to the checkout form.', 'url' => $r['tickets'] . '#checkout-fields', 'category' => 'User Guide', 'keywords' => 'checkout form fields custom'],
            ['page' => 'Selling Tickets', 'section' => 'Promo Codes', 'description' => 'Create discount codes for ticket purchases.', 'url' => $r['tickets'] . '#promo-codes', 'category' => 'User Guide', 'keywords' => 'promo discount coupon code'],
            ['page' => 'Selling Tickets', 'section' => 'Managing Sales', 'description' => 'View and manage ticket sale records.', 'url' => $r['tickets'] . '#managing-sales', 'category' => 'User Guide', 'keywords' => 'sales orders manage view'],
            ['page' => 'Selling Tickets', 'section' => 'Sale Notifications', 'description' => 'Get notified when tickets are purchased.', 'url' => $r['tickets'] . '#sale-notifications', 'category' => 'User Guide', 'keywords' => 'notification alert email'],
            ['page' => 'Selling Tickets', 'section' => 'Export Sales Data', 'description' => 'Export ticket sales as CSV.', 'url' => $r['tickets'] . '#export', 'category' => 'User Guide', 'keywords' => 'export csv download data'],
            ['page' => 'Selling Tickets', 'section' => 'Check-In System', 'description' => 'QR code-based check-in for attendees.', 'url' => $r['tickets'] . '#check-in', 'category' => 'User Guide', 'keywords' => 'check-in qr code scan attendee'],
            ['page' => 'Selling Tickets', 'section' => 'Check-In Dashboard', 'description' => 'Dashboard for managing event check-ins.', 'url' => $r['tickets'] . '#checkin-dashboard', 'category' => 'User Guide', 'keywords' => 'dashboard check-in manage'],
            ['page' => 'Selling Tickets', 'section' => 'Waitlist', 'description' => 'Manage waitlists for sold-out events.', 'url' => $r['tickets'] . '#waitlist', 'category' => 'User Guide', 'keywords' => 'waitlist waiting list sold out'],
            ['page' => 'Selling Tickets', 'section' => 'Financial Reporting', 'description' => 'Track revenue and financial metrics.', 'url' => $r['tickets'] . '#financial', 'category' => 'User Guide', 'keywords' => 'revenue money financial report'],

            // Event Graphics
            ['page' => 'Event Graphics', 'section' => 'Overview', 'description' => 'Generate shareable images for social media.', 'url' => $r['event_graphics'] . '#overview', 'category' => 'User Guide', 'keywords' => 'graphics images social media flyer'],
            ['page' => 'Event Graphics', 'section' => 'Text Template', 'description' => 'Customize text formatting for event graphics.', 'url' => $r['event_graphics'] . '#text-template', 'category' => 'User Guide', 'keywords' => 'template text format'],
            ['page' => 'Event Graphics', 'section' => 'Quick Reference', 'description' => 'Essential template variables at a glance.', 'url' => $r['event_graphics'] . '#quick-reference', 'category' => 'User Guide', 'keywords' => 'variables reference cheatsheet'],
            ['page' => 'Event Graphics', 'section' => 'All Template Variables', 'description' => 'Complete list of available template variables.', 'url' => $r['event_graphics'] . '#variables', 'category' => 'User Guide', 'keywords' => 'variables placeholders tokens'],
            ['page' => 'Event Graphics', 'section' => 'AI Text Prompt', 'description' => 'Use AI to transform generated text (Enterprise).', 'url' => $r['event_graphics'] . '#ai-prompt', 'category' => 'User Guide', 'keywords' => 'ai prompt transform text'],
            ['page' => 'Event Graphics', 'section' => 'Email Scheduling', 'description' => 'Schedule automatic graphic emails (Enterprise).', 'url' => $r['event_graphics'] . '#email-scheduling', 'category' => 'User Guide', 'keywords' => 'email schedule automatic send'],

            // Analytics
            ['page' => 'Analytics', 'section' => 'Overview', 'description' => 'Introduction to the analytics dashboard.', 'url' => $r['analytics'] . '#overview', 'category' => 'User Guide', 'keywords' => 'analytics dashboard stats'],
            ['page' => 'Analytics', 'section' => 'Filters', 'description' => 'Filter by schedule, date range, and period.', 'url' => $r['analytics'] . '#filters', 'category' => 'User Guide', 'keywords' => 'filter date range period'],
            ['page' => 'Analytics', 'section' => 'Stats Cards', 'description' => 'Summary metrics cards for views and revenue.', 'url' => $r['analytics'] . '#stats-cards', 'category' => 'User Guide', 'keywords' => 'metrics cards summary'],
            ['page' => 'Analytics', 'section' => 'Charts', 'description' => 'Interactive charts for views, devices, and traffic.', 'url' => $r['analytics'] . '#charts', 'category' => 'User Guide', 'keywords' => 'charts graphs devices traffic sources'],
            ['page' => 'Analytics', 'section' => 'No Data State', 'description' => 'Why analytics data might be missing.', 'url' => $r['analytics'] . '#no-data', 'category' => 'User Guide', 'keywords' => 'empty no data missing'],

            // Account Settings
            ['page' => 'Account Settings', 'section' => 'Profile Information', 'description' => 'Manage name, email, timezone, language, and profile image.', 'url' => $r['account_settings'] . '#profile', 'category' => 'User Guide', 'keywords' => 'name email timezone language profile'],
            ['page' => 'Account Settings', 'section' => 'Payment Methods', 'description' => 'Configure payment methods for ticket sales.', 'url' => $r['account_settings'] . '#payments', 'category' => 'User Guide', 'keywords' => 'payment method stripe invoice'],
            ['page' => 'Account Settings', 'section' => 'Stripe', 'description' => 'Connect Stripe for payment processing.', 'url' => $r['account_settings'] . '#stripe', 'category' => 'User Guide', 'keywords' => 'stripe connect payment'],
            ['page' => 'Account Settings', 'section' => 'Invoice Ninja', 'description' => 'Set up Invoice Ninja as a payment gateway.', 'url' => $r['account_settings'] . '#invoice-ninja', 'category' => 'User Guide', 'keywords' => 'invoice ninja payment'],
            ['page' => 'Account Settings', 'section' => 'Payment URL', 'description' => 'Configure a custom payment URL.', 'url' => $r['account_settings'] . '#payment-url', 'category' => 'User Guide', 'keywords' => 'payment url link custom'],
            ['page' => 'Account Settings', 'section' => 'API Settings', 'description' => 'Manage API access and keys (Pro).', 'url' => $r['account_settings'] . '#api', 'category' => 'User Guide', 'keywords' => 'api key token access'],
            ['page' => 'Account Settings', 'section' => 'Webhooks', 'description' => 'Configure webhook notifications (Pro).', 'url' => $r['account_settings'] . '#webhooks', 'category' => 'User Guide', 'keywords' => 'webhooks notifications callback'],
            ['page' => 'Account Settings', 'section' => 'Google Settings', 'description' => 'Manage Google account and calendar sync.', 'url' => $r['account_settings'] . '#google', 'category' => 'User Guide', 'keywords' => 'google account calendar oauth'],
            ['page' => 'Account Settings', 'section' => 'App Update', 'description' => 'Check application update status (selfhosted).', 'url' => $r['account_settings'] . '#app-update', 'category' => 'User Guide', 'keywords' => 'update version selfhosted'],
            ['page' => 'Account Settings', 'section' => 'Update Password', 'description' => 'Change your account password.', 'url' => $r['account_settings'] . '#password', 'category' => 'User Guide', 'keywords' => 'password change security'],
            ['page' => 'Account Settings', 'section' => 'Two-Factor Authentication', 'description' => 'Enable 2FA for account security.', 'url' => $r['account_settings'] . '#two-factor', 'category' => 'User Guide', 'keywords' => '2fa two-factor authentication security totp'],
            ['page' => 'Account Settings', 'section' => 'Delete Account', 'description' => 'Permanently delete your account.', 'url' => $r['account_settings'] . '#delete-account', 'category' => 'User Guide', 'keywords' => 'delete remove account'],

            // Scan Agenda
            ['page' => 'Scan Agenda', 'section' => 'Overview', 'description' => 'Use AI to scan printed agendas and create event parts.', 'url' => $r['scan_agenda'] . '#overview', 'category' => 'User Guide', 'keywords' => 'scan photo agenda ai camera'],
            ['page' => 'Scan Agenda', 'section' => 'Getting Started', 'description' => 'Access Scan Agenda from the admin panel.', 'url' => $r['scan_agenda'] . '#getting-started', 'category' => 'User Guide', 'keywords' => 'start begin access'],
            ['page' => 'Scan Agenda', 'section' => 'How It Works', 'description' => 'Steps for scanning, parsing, reviewing, and saving.', 'url' => $r['scan_agenda'] . '#how-it-works', 'category' => 'User Guide', 'keywords' => 'process steps workflow'],
            ['page' => 'Scan Agenda', 'section' => 'Custom AI Prompt', 'description' => 'Customize AI instructions for agenda parsing.', 'url' => $r['scan_agenda'] . '#custom-prompt', 'category' => 'User Guide', 'keywords' => 'prompt customize instructions'],
            ['page' => 'Scan Agenda', 'section' => 'Tips', 'description' => 'Best practices for scanning agendas.', 'url' => $r['scan_agenda'] . '#tips', 'category' => 'User Guide', 'keywords' => 'tips best practices lighting'],

            // Boost
            ['page' => 'Boost', 'section' => 'Overview', 'description' => 'Promote events with Facebook and Instagram ads.', 'url' => $r['boost'] . '#overview', 'category' => 'User Guide', 'keywords' => 'boost promote advertise facebook instagram meta'],
            ['page' => 'Boost', 'section' => 'Quick Mode', 'description' => 'Create ad campaigns quickly with minimal setup.', 'url' => $r['boost'] . '#quick-mode', 'category' => 'User Guide', 'keywords' => 'quick fast simple campaign'],
            ['page' => 'Boost', 'section' => 'Advanced Mode', 'description' => 'Full control over budget, targeting, and creative.', 'url' => $r['boost'] . '#advanced-mode', 'category' => 'User Guide', 'keywords' => 'advanced targeting budget creative'],
            ['page' => 'Boost', 'section' => 'Smart Defaults', 'description' => 'Automatic configuration based on event type.', 'url' => $r['boost'] . '#smart-defaults', 'category' => 'User Guide', 'keywords' => 'defaults automatic smart'],
            ['page' => 'Boost', 'section' => 'Managing Campaigns', 'description' => 'View campaign statuses and manage ads.', 'url' => $r['boost'] . '#managing-campaigns', 'category' => 'User Guide', 'keywords' => 'campaigns manage status'],
            ['page' => 'Boost', 'section' => 'Spending Limits', 'description' => 'Budget limits based on campaign history.', 'url' => $r['boost'] . '#spending-limits', 'category' => 'User Guide', 'keywords' => 'budget limits spending cap'],
            ['page' => 'Boost', 'section' => 'Analytics', 'description' => 'View campaign performance and metrics.', 'url' => $r['boost'] . '#analytics', 'category' => 'User Guide', 'keywords' => 'analytics performance metrics reach impressions'],
            ['page' => 'Boost', 'section' => 'Billing & Refunds', 'description' => 'Pricing structure and refund policy.', 'url' => $r['boost'] . '#billing', 'category' => 'User Guide', 'keywords' => 'billing pricing refund cost'],
            ['page' => 'Boost', 'section' => 'Tips', 'description' => 'Best practices for successful ad campaigns.', 'url' => $r['boost'] . '#tips', 'category' => 'User Guide', 'keywords' => 'tips best practices advice'],

            // ===== SELFHOST =====

            // Installation
            ['page' => 'Installation', 'section' => 'Overview', 'description' => 'Manual installation guide for selfhosted deployments.', 'url' => $r['selfhost_installation'] . '#overview', 'category' => 'Selfhost', 'keywords' => 'install setup deploy server'],
            ['page' => 'Installation', 'section' => 'Requirements', 'description' => 'Server requirements: PHP 8.1+, MySQL, and more.', 'url' => $r['selfhost_installation'] . '#requirements', 'category' => 'Selfhost', 'keywords' => 'requirements php mysql server'],
            ['page' => 'Installation', 'section' => 'Set Up the Database', 'description' => 'Create MySQL database and user.', 'url' => $r['selfhost_installation'] . '#database', 'category' => 'Selfhost', 'keywords' => 'database mysql create'],
            ['page' => 'Installation', 'section' => 'Download the Application', 'description' => 'Download and extract Event Schedule files.', 'url' => $r['selfhost_installation'] . '#download', 'category' => 'Selfhost', 'keywords' => 'download extract files'],
            ['page' => 'Installation', 'section' => 'Set File Permissions', 'description' => 'Set proper directory permissions.', 'url' => $r['selfhost_installation'] . '#permissions', 'category' => 'Selfhost', 'keywords' => 'permissions chmod directories'],
            ['page' => 'Installation', 'section' => 'Configure Environment', 'description' => 'Set up the .env configuration file.', 'url' => $r['selfhost_installation'] . '#environment', 'category' => 'Selfhost', 'keywords' => 'env environment configuration'],
            ['page' => 'Installation', 'section' => 'Set Up the Cron Job', 'description' => 'Configure scheduled tasks for the application.', 'url' => $r['selfhost_installation'] . '#cron', 'category' => 'Selfhost', 'keywords' => 'cron scheduler task'],
            ['page' => 'Installation', 'section' => 'Verification', 'description' => 'Test and verify the installation.', 'url' => $r['selfhost_installation'] . '#verification', 'category' => 'Selfhost', 'keywords' => 'verify test check'],

            // Stripe (Selfhost)
            ['page' => 'Stripe Integration', 'section' => 'Overview', 'description' => 'Set up Stripe for payment processing.', 'url' => $r['selfhost_stripe'] . '#overview', 'category' => 'Selfhost', 'keywords' => 'stripe payment setup'],
            ['page' => 'Stripe Integration', 'section' => 'Choose Your Setup', 'description' => 'Select the right deployment option.', 'url' => $r['selfhost_stripe'] . '#choose-setup', 'category' => 'Selfhost', 'keywords' => 'setup option deployment'],
            ['page' => 'Stripe Integration', 'section' => 'For Selfhosted Users', 'description' => 'Stripe configuration for selfhosted deployments.', 'url' => $r['selfhost_stripe'] . '#selfhosted-users', 'category' => 'Selfhost', 'keywords' => 'selfhosted stripe configuration'],
            ['page' => 'Stripe Integration', 'section' => 'For SaaS Operators', 'description' => 'Stripe Connect setup for SaaS platforms.', 'url' => $r['selfhost_stripe'] . '#saas-operators', 'category' => 'Selfhost', 'keywords' => 'saas stripe connect'],
            ['page' => 'Stripe Integration', 'section' => 'Invoice Ninja Integration', 'description' => 'Configure Invoice Ninja as a payment gateway.', 'url' => $r['selfhost_stripe'] . '#invoice-ninja', 'category' => 'Selfhost', 'keywords' => 'invoice ninja payment gateway'],
            ['page' => 'Stripe Integration', 'section' => 'Testing', 'description' => 'Test your payment setup.', 'url' => $r['selfhost_stripe'] . '#testing', 'category' => 'Selfhost', 'keywords' => 'test payment debug'],
            ['page' => 'Stripe Integration', 'section' => 'Troubleshooting', 'description' => 'Fix common payment issues.', 'url' => $r['selfhost_stripe'] . '#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix problem'],
            ['page' => 'Stripe Integration', 'section' => 'Security', 'description' => 'Payment security considerations.', 'url' => $r['selfhost_stripe'] . '#security', 'category' => 'Selfhost', 'keywords' => 'security keys secrets'],

            // Google Calendar (Selfhost)
            ['page' => 'Google Calendar', 'section' => 'Prerequisites', 'description' => 'Google Cloud project requirements for calendar sync.', 'url' => $r['selfhost_google_calendar'] . '#prerequisites', 'category' => 'Selfhost', 'keywords' => 'google cloud project prerequisites'],
            ['page' => 'Google Calendar', 'section' => 'Setup Instructions', 'description' => 'Step-by-step OAuth2 setup for Google Calendar.', 'url' => $r['selfhost_google_calendar'] . '#setup', 'category' => 'Selfhost', 'keywords' => 'setup oauth2 credentials'],
            ['page' => 'Google Calendar', 'section' => 'Features', 'description' => 'Google Calendar sync capabilities.', 'url' => $r['selfhost_google_calendar'] . '#features', 'category' => 'Selfhost', 'keywords' => 'features sync bidirectional'],
            ['page' => 'Google Calendar', 'section' => 'Usage', 'description' => 'How to use the Google Calendar integration.', 'url' => $r['selfhost_google_calendar'] . '#usage', 'category' => 'Selfhost', 'keywords' => 'usage connect calendar'],
            ['page' => 'Google Calendar', 'section' => 'API Endpoints', 'description' => 'Google Calendar API endpoints reference.', 'url' => $r['selfhost_google_calendar'] . '#api-endpoints', 'category' => 'Selfhost', 'keywords' => 'api endpoints routes'],
            ['page' => 'Google Calendar', 'section' => 'Troubleshooting', 'description' => 'Debug Google Calendar sync issues.', 'url' => $r['selfhost_google_calendar'] . '#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix debug sync'],
            ['page' => 'Google Calendar', 'section' => 'Security', 'description' => 'Token storage and security considerations.', 'url' => $r['selfhost_google_calendar'] . '#security', 'category' => 'Selfhost', 'keywords' => 'security tokens oauth'],

            // Email (Selfhost)
            ['page' => 'Email Setup', 'section' => 'Overview', 'description' => 'Configure email for your selfhosted instance.', 'url' => $r['selfhost_email'] . '#overview', 'category' => 'Selfhost', 'keywords' => 'email mail setup'],
            ['page' => 'Email Setup', 'section' => 'SMTP Setup', 'description' => 'Configure SMTP for sending emails.', 'url' => $r['selfhost_email'] . '#smtp', 'category' => 'Selfhost', 'keywords' => 'smtp mail server'],
            ['page' => 'Email Setup', 'section' => 'Other Mail Drivers', 'description' => 'Alternative mail drivers: Mailgun, SES, and more.', 'url' => $r['selfhost_email'] . '#drivers', 'category' => 'Selfhost', 'keywords' => 'mailgun ses postmark driver'],
            ['page' => 'Email Setup', 'section' => 'Sender Configuration', 'description' => 'Configure the email sender address and name.', 'url' => $r['selfhost_email'] . '#sender', 'category' => 'Selfhost', 'keywords' => 'sender from address name'],
            ['page' => 'Email Setup', 'section' => 'Testing', 'description' => 'Test your email configuration.', 'url' => $r['selfhost_email'] . '#testing', 'category' => 'Selfhost', 'keywords' => 'test email verify'],
            ['page' => 'Email Setup', 'section' => 'Troubleshooting', 'description' => 'Fix email delivery issues.', 'url' => $r['selfhost_email'] . '#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix delivery'],

            // AI (Selfhost)
            ['page' => 'AI Setup', 'section' => 'Overview', 'description' => 'Enable AI features with Google Gemini.', 'url' => $r['selfhost_ai'] . '#overview', 'category' => 'Selfhost', 'keywords' => 'ai gemini setup'],
            ['page' => 'AI Setup', 'section' => 'AI Features', 'description' => 'Available AI features: import, scan, translation.', 'url' => $r['selfhost_ai'] . '#features', 'category' => 'Selfhost', 'keywords' => 'features import scan translate'],
            ['page' => 'AI Setup', 'section' => 'Get an API Key', 'description' => 'Obtain a Google Gemini API key.', 'url' => $r['selfhost_ai'] . '#api-key', 'category' => 'Selfhost', 'keywords' => 'api key gemini google'],
            ['page' => 'AI Setup', 'section' => 'Configuration', 'description' => 'Set the GEMINI_API_KEY environment variable.', 'url' => $r['selfhost_ai'] . '#configuration', 'category' => 'Selfhost', 'keywords' => 'configuration env variable'],
            ['page' => 'AI Setup', 'section' => 'Troubleshooting', 'description' => 'Fix AI API issues.', 'url' => $r['selfhost_ai'] . '#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix api error'],

            // Admin Panel (Selfhost)
            ['page' => 'Admin Panel', 'section' => 'Overview', 'description' => 'Admin panel organization and sections.', 'url' => $r['selfhost_admin'] . '#overview', 'category' => 'Selfhost', 'keywords' => 'admin panel dashboard'],
            ['page' => 'Admin Panel', 'section' => 'Accessing /admin', 'description' => 'How to access the admin panel.', 'url' => $r['selfhost_admin'] . '#accessing', 'category' => 'Selfhost', 'keywords' => 'access login admin url'],
            ['page' => 'Admin Panel', 'section' => 'Dashboard', 'description' => 'Key metrics and overview dashboard.', 'url' => $r['selfhost_admin'] . '#dashboard', 'category' => 'Selfhost', 'keywords' => 'dashboard metrics overview'],
            ['page' => 'Admin Panel', 'section' => 'Users', 'description' => 'User management and administration.', 'url' => $r['selfhost_admin'] . '#insights-users', 'category' => 'Selfhost', 'keywords' => 'users management accounts'],
            ['page' => 'Admin Panel', 'section' => 'Revenue', 'description' => 'Revenue analytics and tracking.', 'url' => $r['selfhost_admin'] . '#insights-revenue', 'category' => 'Selfhost', 'keywords' => 'revenue income money analytics'],
            ['page' => 'Admin Panel', 'section' => 'Analytics', 'description' => 'Traffic and usage analytics.', 'url' => $r['selfhost_admin'] . '#insights-analytics', 'category' => 'Selfhost', 'keywords' => 'analytics traffic views'],
            ['page' => 'Admin Panel', 'section' => 'Usage', 'description' => 'Feature usage tracking.', 'url' => $r['selfhost_admin'] . '#insights-usage', 'category' => 'Selfhost', 'keywords' => 'usage features tracking'],
            ['page' => 'Admin Panel', 'section' => 'Boost Management', 'description' => 'Manage Boost ad campaigns.', 'url' => $r['selfhost_admin'] . '#manage-boost', 'category' => 'Selfhost', 'keywords' => 'boost campaigns manage ads'],
            ['page' => 'Admin Panel', 'section' => 'Plans Management', 'description' => 'Manage plan tiers (SaaS only).', 'url' => $r['selfhost_admin'] . '#manage-plans', 'category' => 'Selfhost', 'keywords' => 'plans tiers subscription manage'],
            ['page' => 'Admin Panel', 'section' => 'Domains Management', 'description' => 'Manage custom domains (SaaS only).', 'url' => $r['selfhost_admin'] . '#manage-domains', 'category' => 'Selfhost', 'keywords' => 'domains custom manage'],
            ['page' => 'Admin Panel', 'section' => 'Newsletters Management', 'description' => 'Manage newsletters from the admin panel.', 'url' => $r['selfhost_admin'] . '#manage-newsletters', 'category' => 'Selfhost', 'keywords' => 'newsletters manage admin'],
            ['page' => 'Admin Panel', 'section' => 'Blog Management', 'description' => 'Manage blog posts (SaaS only).', 'url' => $r['selfhost_admin'] . '#manage-blog', 'category' => 'Selfhost', 'keywords' => 'blog posts manage content'],
            ['page' => 'Admin Panel', 'section' => 'Audit Log', 'description' => 'View platform activity audit log.', 'url' => $r['selfhost_admin'] . '#system-audit-log', 'category' => 'Selfhost', 'keywords' => 'audit log activity tracking'],
            ['page' => 'Admin Panel', 'section' => 'Queue', 'description' => 'Monitor background job queue.', 'url' => $r['selfhost_admin'] . '#system-queue', 'category' => 'Selfhost', 'keywords' => 'queue jobs background worker'],
            ['page' => 'Admin Panel', 'section' => 'Logs', 'description' => 'View application error logs.', 'url' => $r['selfhost_admin'] . '#system-logs', 'category' => 'Selfhost', 'keywords' => 'logs errors debug'],

            // Boost Setup (Selfhost)
            ['page' => 'Boost Setup', 'section' => 'Overview', 'description' => 'Set up Meta/Facebook boost for selfhosted instances.', 'url' => $r['selfhost_boost'] . '#overview', 'category' => 'Selfhost', 'keywords' => 'boost meta facebook setup'],
            ['page' => 'Boost Setup', 'section' => 'Create a Facebook App', 'description' => 'Create and configure a Facebook app.', 'url' => $r['selfhost_boost'] . '#facebook-app', 'category' => 'Selfhost', 'keywords' => 'facebook app create meta'],
            ['page' => 'Boost Setup', 'section' => 'Meta Business & Ad Account', 'description' => 'Set up business and ad accounts.', 'url' => $r['selfhost_boost'] . '#ad-account', 'category' => 'Selfhost', 'keywords' => 'business ad account meta'],
            ['page' => 'Boost Setup', 'section' => 'Facebook Page', 'description' => 'Configure a Facebook page for ads.', 'url' => $r['selfhost_boost'] . '#facebook-page', 'category' => 'Selfhost', 'keywords' => 'facebook page configure'],
            ['page' => 'Boost Setup', 'section' => 'System User & Access Token', 'description' => 'Generate a system user access token.', 'url' => $r['selfhost_boost'] . '#system-user', 'category' => 'Selfhost', 'keywords' => 'system user token access'],
            ['page' => 'Boost Setup', 'section' => 'Meta Pixel', 'description' => 'Set up Meta Pixel for conversion tracking.', 'url' => $r['selfhost_boost'] . '#pixel', 'category' => 'Selfhost', 'keywords' => 'pixel tracking conversion meta'],
            ['page' => 'Boost Setup', 'section' => 'Webhooks', 'description' => 'Configure webhooks for Boost integration.', 'url' => $r['selfhost_boost'] . '#webhooks', 'category' => 'Selfhost', 'keywords' => 'webhooks callback notifications'],
            ['page' => 'Boost Setup', 'section' => 'App Review', 'description' => 'Submit your app for Meta review.', 'url' => $r['selfhost_boost'] . '#app-review', 'category' => 'Selfhost', 'keywords' => 'review approval meta facebook'],
            ['page' => 'Boost Setup', 'section' => 'Environment Variables', 'description' => 'Complete .env configuration for Boost.', 'url' => $r['selfhost_boost'] . '#environment', 'category' => 'Selfhost', 'keywords' => 'env environment variables configuration'],
            ['page' => 'Boost Setup', 'section' => 'Scheduled Command', 'description' => 'Set up the Boost sync scheduler.', 'url' => $r['selfhost_boost'] . '#scheduled-command', 'category' => 'Selfhost', 'keywords' => 'scheduler cron command sync'],

            // ===== SAAS =====

            // SaaS Setup
            ['page' => 'SaaS Setup', 'section' => 'Overview', 'description' => 'Deploy Event Schedule as a multi-tenant SaaS.', 'url' => $r['saas_setup'] . '#overview', 'category' => 'SaaS', 'keywords' => 'saas multi-tenant deploy'],
            ['page' => 'SaaS Setup', 'section' => 'Prerequisites', 'description' => 'Requirements for SaaS deployment.', 'url' => $r['saas_setup'] . '#prerequisites', 'category' => 'SaaS', 'keywords' => 'requirements prerequisites'],
            ['page' => 'SaaS Setup', 'section' => 'Environment Variables', 'description' => 'SaaS-specific .env configuration.', 'url' => $r['saas_setup'] . '#environment', 'category' => 'SaaS', 'keywords' => 'env environment configuration'],
            ['page' => 'SaaS Setup', 'section' => 'DNS Setup', 'description' => 'Configure subdomain DNS for SaaS.', 'url' => $r['saas_setup'] . '#dns', 'category' => 'SaaS', 'keywords' => 'dns subdomain wildcard'],
            ['page' => 'SaaS Setup', 'section' => 'Web Server', 'description' => 'Web server configuration for SaaS mode.', 'url' => $r['saas_setup'] . '#webserver', 'category' => 'SaaS', 'keywords' => 'webserver nginx apache'],
            ['page' => 'SaaS Setup', 'section' => 'Stripe Integration', 'description' => 'Configure Stripe for SaaS subscriptions.', 'url' => $r['saas_setup'] . '#stripe', 'category' => 'SaaS', 'keywords' => 'stripe payment subscription'],
            ['page' => 'SaaS Setup', 'section' => 'Example', 'description' => 'Complete SaaS setup example.', 'url' => $r['saas_setup'] . '#example', 'category' => 'SaaS', 'keywords' => 'example tutorial walkthrough'],
            ['page' => 'SaaS Setup', 'section' => 'Verification', 'description' => 'Verify your SaaS setup is working.', 'url' => $r['saas_setup'] . '#verification', 'category' => 'SaaS', 'keywords' => 'verify test check'],
            ['page' => 'SaaS Setup', 'section' => 'Demo Account', 'description' => 'Create a demo account for testing.', 'url' => $r['saas_setup'] . '#demo', 'category' => 'SaaS', 'keywords' => 'demo test account sample'],
            ['page' => 'SaaS Setup', 'section' => 'Troubleshooting', 'description' => 'Fix common SaaS setup issues.', 'url' => $r['saas_setup'] . '#troubleshooting', 'category' => 'SaaS', 'keywords' => 'troubleshoot fix problem'],
            ['page' => 'SaaS Setup', 'section' => 'Related Documentation', 'description' => 'Links to related SaaS documentation.', 'url' => $r['saas_setup'] . '#related', 'category' => 'SaaS', 'keywords' => ''],
            ['page' => 'SaaS Setup', 'section' => 'Security', 'description' => 'Security considerations for SaaS deployments.', 'url' => $r['saas_setup'] . '#security', 'category' => 'SaaS', 'keywords' => 'security ssl https'],

            // Custom Domains (SaaS)
            ['page' => 'Custom Domains', 'section' => 'Overview', 'description' => 'Allow users to use custom domains.', 'url' => $r['saas_custom_domains'] . '#overview', 'category' => 'SaaS', 'keywords' => 'custom domain branded url'],
            ['page' => 'Custom Domains', 'section' => 'Prerequisites', 'description' => 'DigitalOcean requirements for custom domains.', 'url' => $r['saas_custom_domains'] . '#prerequisites', 'category' => 'SaaS', 'keywords' => 'digitalocean requirements'],
            ['page' => 'Custom Domains', 'section' => 'Environment Setup', 'description' => 'Configure DigitalOcean API for domains.', 'url' => $r['saas_custom_domains'] . '#environment', 'category' => 'SaaS', 'keywords' => 'env configuration digitalocean api'],
            ['page' => 'Custom Domains', 'section' => 'How It Works', 'description' => 'Redirect vs Direct domain modes.', 'url' => $r['saas_custom_domains'] . '#how-it-works', 'category' => 'SaaS', 'keywords' => 'redirect direct mode proxy'],
            ['page' => 'Custom Domains', 'section' => 'DNS Setup for Customers', 'description' => 'Customer CNAME configuration instructions.', 'url' => $r['saas_custom_domains'] . '#dns-setup', 'category' => 'SaaS', 'keywords' => 'dns cname configuration'],
            ['page' => 'Custom Domains', 'section' => 'Admin Management', 'description' => 'Domain management dashboard for admins.', 'url' => $r['saas_custom_domains'] . '#admin-management', 'category' => 'SaaS', 'keywords' => 'admin manage dashboard'],
            ['page' => 'Custom Domains', 'section' => 'Troubleshooting', 'description' => 'Fix custom domain issues.', 'url' => $r['saas_custom_domains'] . '#troubleshooting', 'category' => 'SaaS', 'keywords' => 'troubleshoot fix dns ssl'],

            // Twilio (SaaS)
            ['page' => 'Twilio Integration', 'section' => 'Overview', 'description' => 'Phone verification and WhatsApp for SaaS.', 'url' => $r['saas_twilio'] . '#overview', 'category' => 'SaaS', 'keywords' => 'twilio phone sms whatsapp'],
            ['page' => 'Twilio Integration', 'section' => 'Create a Twilio Account', 'description' => 'Set up a Twilio account for messaging.', 'url' => $r['saas_twilio'] . '#create-account', 'category' => 'SaaS', 'keywords' => 'twilio account setup register'],
            ['page' => 'Twilio Integration', 'section' => 'Environment Setup', 'description' => 'Configure Twilio environment variables.', 'url' => $r['saas_twilio'] . '#environment', 'category' => 'SaaS', 'keywords' => 'env configuration twilio'],
            ['page' => 'Twilio Integration', 'section' => 'Phone Verification', 'description' => 'Implement phone number verification.', 'url' => $r['saas_twilio'] . '#phone-verification', 'category' => 'SaaS', 'keywords' => 'phone verify number sms'],
            ['page' => 'Twilio Integration', 'section' => 'WhatsApp Setup', 'description' => 'Register and configure WhatsApp messaging.', 'url' => $r['saas_twilio'] . '#whatsapp', 'category' => 'SaaS', 'keywords' => 'whatsapp messaging sender'],
            ['page' => 'Twilio Integration', 'section' => 'Testing', 'description' => 'Test SMS and WhatsApp functionality.', 'url' => $r['saas_twilio'] . '#testing', 'category' => 'SaaS', 'keywords' => 'test sms whatsapp verify'],

            // ===== DEVELOPER =====

            // API Reference
            ['page' => 'API Reference', 'section' => 'Authentication', 'description' => 'API key authentication for REST endpoints.', 'url' => $r['developer_api'] . '#authentication', 'category' => 'Developer', 'keywords' => 'api key auth token bearer'],
            ['page' => 'API Reference', 'section' => 'Rate Limits', 'description' => 'API rate limiting and quotas.', 'url' => $r['developer_api'] . '#rate-limits', 'category' => 'Developer', 'keywords' => 'rate limit throttle quota'],
            ['page' => 'API Reference', 'section' => 'Response Format', 'description' => 'JSON response structure and conventions.', 'url' => $r['developer_api'] . '#response-format', 'category' => 'Developer', 'keywords' => 'json response format structure'],
            ['page' => 'API Reference', 'section' => 'Pagination', 'description' => 'Paginate through list endpoints.', 'url' => $r['developer_api'] . '#pagination', 'category' => 'Developer', 'keywords' => 'pagination page per_page cursor'],
            ['page' => 'API Reference', 'section' => 'Register', 'description' => 'User registration endpoint.', 'url' => $r['developer_api'] . '#register', 'category' => 'Developer', 'keywords' => 'register user create account'],
            ['page' => 'API Reference', 'section' => 'Login', 'description' => 'User login endpoint.', 'url' => $r['developer_api'] . '#login', 'category' => 'Developer', 'keywords' => 'login authenticate session'],
            ['page' => 'API Reference', 'section' => 'List Schedules', 'description' => 'GET /api/schedules - List all schedules.', 'url' => $r['developer_api'] . '#list-schedules', 'category' => 'Developer', 'keywords' => 'list schedules get index'],
            ['page' => 'API Reference', 'section' => 'Show Schedule', 'description' => 'GET /api/schedules/{id} - Get schedule details.', 'url' => $r['developer_api'] . '#show-schedule', 'category' => 'Developer', 'keywords' => 'show schedule get detail'],
            ['page' => 'API Reference', 'section' => 'Create Schedule', 'description' => 'POST /api/schedules - Create a new schedule.', 'url' => $r['developer_api'] . '#create-schedule', 'category' => 'Developer', 'keywords' => 'create schedule post new'],
            ['page' => 'API Reference', 'section' => 'Update Schedule', 'description' => 'PUT /api/schedules/{id} - Update a schedule.', 'url' => $r['developer_api'] . '#update-schedule', 'category' => 'Developer', 'keywords' => 'update schedule put modify'],
            ['page' => 'API Reference', 'section' => 'Delete Schedule', 'description' => 'DELETE /api/schedules/{id} - Delete a schedule.', 'url' => $r['developer_api'] . '#delete-schedule', 'category' => 'Developer', 'keywords' => 'delete schedule remove destroy'],
            ['page' => 'API Reference', 'section' => 'List Sub-Schedules', 'description' => 'GET /api/schedules/groups - List sub-schedules.', 'url' => $r['developer_api'] . '#list-groups', 'category' => 'Developer', 'keywords' => 'list groups sub-schedules get'],
            ['page' => 'API Reference', 'section' => 'Create Sub-Schedule', 'description' => 'POST /api/schedules/groups - Create a sub-schedule.', 'url' => $r['developer_api'] . '#create-group', 'category' => 'Developer', 'keywords' => 'create group sub-schedule post'],
            ['page' => 'API Reference', 'section' => 'Update Sub-Schedule', 'description' => 'PUT /api/schedules/groups/{id} - Update a sub-schedule.', 'url' => $r['developer_api'] . '#update-group', 'category' => 'Developer', 'keywords' => 'update group sub-schedule put'],
            ['page' => 'API Reference', 'section' => 'Delete Sub-Schedule', 'description' => 'DELETE /api/schedules/groups/{id} - Delete a sub-schedule.', 'url' => $r['developer_api'] . '#delete-group', 'category' => 'Developer', 'keywords' => 'delete group sub-schedule remove'],
            ['page' => 'API Reference', 'section' => 'List Events', 'description' => 'GET /api/events - List all events.', 'url' => $r['developer_api'] . '#list-events', 'category' => 'Developer', 'keywords' => 'list events get index'],
            ['page' => 'API Reference', 'section' => 'Show Event', 'description' => 'GET /api/events/{id} - Get event details.', 'url' => $r['developer_api'] . '#show-event', 'category' => 'Developer', 'keywords' => 'show event get detail'],
            ['page' => 'API Reference', 'section' => 'Create Event', 'description' => 'POST /api/events - Create a new event.', 'url' => $r['developer_api'] . '#create-event', 'category' => 'Developer', 'keywords' => 'create event post new'],
            ['page' => 'API Reference', 'section' => 'Update Event', 'description' => 'PUT /api/events/{id} - Update an event.', 'url' => $r['developer_api'] . '#update-event', 'category' => 'Developer', 'keywords' => 'update event put modify'],
            ['page' => 'API Reference', 'section' => 'Delete Event', 'description' => 'DELETE /api/events/{id} - Delete an event.', 'url' => $r['developer_api'] . '#delete-event', 'category' => 'Developer', 'keywords' => 'delete event remove destroy'],
            ['page' => 'API Reference', 'section' => 'Upload Flyer', 'description' => 'POST /api/events/flyer - Upload an event flyer image.', 'url' => $r['developer_api'] . '#upload-flyer', 'category' => 'Developer', 'keywords' => 'upload flyer image poster'],
            ['page' => 'API Reference', 'section' => 'List Categories', 'description' => 'GET /api/categories - List event categories.', 'url' => $r['developer_api'] . '#list-categories', 'category' => 'Developer', 'keywords' => 'list categories get'],
            ['page' => 'API Reference', 'section' => 'List Sales', 'description' => 'GET /api/sales - List ticket sales.', 'url' => $r['developer_api'] . '#list-sales', 'category' => 'Developer', 'keywords' => 'list sales tickets get'],
            ['page' => 'API Reference', 'section' => 'Show Sale', 'description' => 'GET /api/sales/{id} - Get sale details.', 'url' => $r['developer_api'] . '#show-sale', 'category' => 'Developer', 'keywords' => 'show sale get detail'],
            ['page' => 'API Reference', 'section' => 'Create Sale', 'description' => 'POST /api/sales - Create a new sale.', 'url' => $r['developer_api'] . '#create-sale', 'category' => 'Developer', 'keywords' => 'create sale post new'],
            ['page' => 'API Reference', 'section' => 'Update Sale Status', 'description' => 'PUT /api/sales/{id} - Update a sale status.', 'url' => $r['developer_api'] . '#update-sale', 'category' => 'Developer', 'keywords' => 'update sale put status'],
            ['page' => 'API Reference', 'section' => 'Delete Sale', 'description' => 'DELETE /api/sales/{id} - Delete a sale.', 'url' => $r['developer_api'] . '#delete-sale', 'category' => 'Developer', 'keywords' => 'delete sale remove'],
            ['page' => 'API Reference', 'section' => 'Error Handling', 'description' => 'HTTP status codes and error responses.', 'url' => $r['developer_api'] . '#error-handling', 'category' => 'Developer', 'keywords' => 'error status code http'],

            // Webhooks (Developer)
            ['page' => 'Webhooks', 'section' => 'Overview', 'description' => 'Real-time notifications for events via HTTP callbacks.', 'url' => $r['developer_webhooks'] . '#overview', 'category' => 'Developer', 'keywords' => 'webhooks callback http notifications'],
            ['page' => 'Webhooks', 'section' => 'Setup', 'description' => 'Configure webhook endpoints and event types.', 'url' => $r['developer_webhooks'] . '#setup', 'category' => 'Developer', 'keywords' => 'setup configure endpoint url'],
            ['page' => 'Webhooks', 'section' => 'Event Types', 'description' => 'Available webhook event types to subscribe to.', 'url' => $r['developer_webhooks'] . '#event-types', 'category' => 'Developer', 'keywords' => 'event types subscribe trigger'],
            ['page' => 'Webhooks', 'section' => 'Payload Format', 'description' => 'Webhook payload JSON structure.', 'url' => $r['developer_webhooks'] . '#payload', 'category' => 'Developer', 'keywords' => 'payload json data format'],
            ['page' => 'Webhooks', 'section' => 'Request Headers', 'description' => 'HTTP headers sent with webhook requests.', 'url' => $r['developer_webhooks'] . '#headers', 'category' => 'Developer', 'keywords' => 'headers http request'],
            ['page' => 'Webhooks', 'section' => 'Signature Verification', 'description' => 'Verify webhooks with HMAC-SHA256 signatures.', 'url' => $r['developer_webhooks'] . '#verification', 'category' => 'Developer', 'keywords' => 'signature hmac sha256 verify security'],
            ['page' => 'Webhooks', 'section' => 'Best Practices', 'description' => 'Best practices for reliable webhook handling.', 'url' => $r['developer_webhooks'] . '#best-practices', 'category' => 'Developer', 'keywords' => 'best practices reliability idempotent'],
            ['page' => 'Webhooks', 'section' => 'Testing', 'description' => 'Test webhook endpoints and payloads.', 'url' => $r['developer_webhooks'] . '#testing', 'category' => 'Developer', 'keywords' => 'test debug webhook'],
        ];
    }
}
