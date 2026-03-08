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
     * Referral Program docs page
     */
    public function docsReferralProgram()
    {
        return view('marketing.docs.referral-program', $this->getDocNavigation('marketing.docs.referral_program'));
    }

    public function about()
    {
        return view('marketing.about', [
            'githubStars' => \App\Utils\GitHubUtils::getStars(),
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
     * Embed Tickets page
     */
    public function embedTickets()
    {
        return view('marketing.embed-tickets');
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
     * Feedback page
     */
    public function feedback()
    {
        return view('marketing.feedback');
    }

    /**
     * Availability page
     */
    public function availability()
    {
        return view('marketing.availability');
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
            'githubStars' => \App\Utils\GitHubUtils::getStars(),
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
     * Compare vs Sched page
     */
    public function compareSched()
    {
        return view('marketing.compare-single', $this->getComparisonData('sched'));
    }

    /**
     * Compare vs Whova page
     */
    public function compareWhova()
    {
        return view('marketing.compare-single', $this->getComparisonData('whova'));
    }

    /**
     * Compare vs Accelevents page
     */
    public function compareAccelevents()
    {
        return view('marketing.compare-single', $this->getComparisonData('accelevents'));
    }

    /**
     * Compare vs Tito page
     */
    public function compareTito()
    {
        return view('marketing.compare-single', $this->getComparisonData('tito'));
    }

    /**
     * Compare vs AddEvent page
     */
    public function compareAddEvent()
    {
        return view('marketing.compare-single', $this->getComparisonData('addevent'));
    }

    /**
     * Compare vs Pretix page
     */
    public function comparePretix()
    {
        return view('marketing.compare-single', $this->getComparisonData('pretix'));
    }

    /**
     * Compare vs Humanitix page
     */
    public function compareHumanitix()
    {
        return view('marketing.compare-single', $this->getComparisonData('humanitix'));
    }

    /**
     * Compare vs Eventzilla page
     */
    public function compareEventzilla()
    {
        return view('marketing.compare-single', $this->getComparisonData('eventzilla'));
    }

    /**
     * Replace hub page
     */
    public function replace()
    {
        return view('marketing.replace');
    }

    /**
     * Replace Google Forms page
     */
    public function replaceGoogleForms()
    {
        return view('marketing.replace-single', $this->getReplacementData('google-forms'));
    }

    /**
     * Replace Mailchimp page
     */
    public function replaceMailchimp()
    {
        return view('marketing.replace-single', $this->getReplacementData('mailchimp'));
    }

    /**
     * Replace Canva page
     */
    public function replaceCanva()
    {
        return view('marketing.replace-single', $this->getReplacementData('canva'));
    }

    /**
     * Replace Linktree page
     */
    public function replaceLinktree()
    {
        return view('marketing.replace-single', $this->getReplacementData('linktree'));
    }

    /**
     * Replace Google Sheets page
     */
    public function replaceGoogleSheets()
    {
        return view('marketing.replace-single', $this->getReplacementData('google-sheets'));
    }

    /**
     * Replace Calendly page
     */
    public function replaceCalendly()
    {
        return view('marketing.replace-single', $this->getReplacementData('calendly'));
    }

    /**
     * Replace SurveyMonkey page
     */
    public function replaceSurveymonkey()
    {
        return view('marketing.replace-single', $this->getReplacementData('surveymonkey'));
    }

    /**
     * Replace Doodle page
     */
    public function replaceDoodle()
    {
        return view('marketing.replace-single', $this->getReplacementData('doodle'));
    }

    /**
     * Replace QR code generators page
     */
    public function replaceQrCodeGenerators()
    {
        return view('marketing.replace-single', $this->getReplacementData('qr-code-generators'));
    }

    /**
     * Replace Squarespace page
     */
    public function replaceSquarespace()
    {
        return view('marketing.replace-single', $this->getReplacementData('squarespace'));
    }

    /**
     * Replace Notion page
     */
    public function replaceNotion()
    {
        return view('marketing.replace-single', $this->getReplacementData('notion'));
    }

    /**
     * Replace Trello page
     */
    public function replaceTrello()
    {
        return view('marketing.replace-single', $this->getReplacementData('trello'));
    }

    /**
     * Contact page
     */
    public function contact()
    {
        return view('marketing.contact', [
            'githubStars' => \App\Utils\GitHubUtils::getStars(),
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
            'githubStars' => \App\Utils\GitHubUtils::getStars(),
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
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'Yes', false],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Eventbrite auto-import', 'Yes (Pro)', 'N/A', true],
                        ['Google Calendar sync', 'Yes (Free)', 'No native 2-way sync', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Yes', false],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'Yes (paid)', false],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes', false],
                        ['Webhooks', 'Yes (Pro)', 'No', true],
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
                        'description' => 'Fully open source with no vendor lock-in. Selfhost on your own server instead of depending on Eventbrite\'s platform and fee structure.',
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
                        'title' => 'Eventbrite Auto-Import',
                        'description' => 'Connect your Eventbrite account and import all your events in one click. Event details, tickets, venues, and images are transferred automatically.',
                        'icon' => 'import',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                    ['name' => 'Tito', 'route' => 'marketing.compare_tito'],
                ],
                'auto_import' => [
                    'title' => 'Bring your Eventbrite events with you',
                    'description' => 'Connect your Eventbrite account and import all your events in one click. No manual data entry or copy-pasting needed.',
                    'bullets' => [
                        'Event details and descriptions',
                        'Ticket types and pricing',
                        'Venue information',
                        'Event images',
                    ],
                    'steps' => [
                        ['title' => 'Connect your account', 'description' => 'Link your Eventbrite account with one click.'],
                        ['title' => 'Select your events', 'description' => 'Browse your Eventbrite events and choose which to import.'],
                        ['title' => 'Import instantly', 'description' => 'All details, tickets, venues, and images transfer automatically.'],
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your schedule', 'description' => 'Sign up free and create your first schedule in under a minute.'],
                    ['title' => 'Add your events', 'description' => 'Import your events directly from Eventbrite, use AI import, or create events manually.'],
                    ['title' => 'Share and sell', 'description' => 'Share your schedule URL and start selling tickets.'],
                ],
                'faq' => [
                    ['question' => 'Can I import my existing Eventbrite events?', 'answer' => 'Yes. With the Pro plan, you can connect your Eventbrite account and import your events in bulk. Event details, ticket types, venues, and images are all transferred automatically.'],
                    ['question' => 'Is it easy to switch from Eventbrite to Event Schedule?', 'answer' => 'Yes. There is no need to migrate your Eventbrite history. Create a free schedule, add your upcoming events (or use AI import to paste and parse them), and share your new schedule URL with your audience.'],
                    ['question' => 'How does Event Schedule pricing compare to Eventbrite?', 'answer' => 'Eventbrite charges 3.7% + $1.79 per ticket on paid events. Event Schedule charges zero platform fees at every plan level. The Pro plan is a flat $5/mo regardless of how many tickets you sell.'],
                    ['question' => 'Does Event Schedule have an event marketplace like Eventbrite?', 'answer' => 'Event Schedule focuses on giving organizers their own branded schedule pages rather than a shared marketplace. You get a dedicated URL, custom domain support, and embeddable calendar widgets to drive discovery from your own channels.'],
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
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Stripe (2.9% + $0.30)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'No', true],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'No', true],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
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
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', '3 admins (free), 5 (Plus)', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (Plus)', true],
                        ['Webhooks', 'Yes (Pro)', 'No', true],
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
                        'description' => 'Paste event details in any format and our AI extracts all the details automatically. Perfect for quickly building your schedule when moving from another platform.',
                        'icon' => 'ai',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with no vendor lock-in. Selfhost on your own server instead of being locked into Luma\'s closed ecosystem.',
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
                        'description' => 'Generate shareable event graphics automatically. Promote events on social media without needing a designer.',
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
                'faq' => [
                    ['question' => 'Is it easy to switch from Luma to Event Schedule?', 'answer' => 'Yes. Create a free schedule and add your events manually or via AI import. Your existing audience can follow your new schedule and subscribe to updates via newsletter or calendar sync.'],
                    ['question' => 'How does Event Schedule pricing compare to Luma?', 'answer' => 'Luma charges $59/mo for its premium plan. Event Schedule Pro is $5/mo with zero platform fees. Even the free plan includes unlimited events, Google Calendar sync, and newsletters.'],
                    ['question' => 'Does Event Schedule support virtual events like Luma?', 'answer' => 'Yes. Event Schedule supports online events with video links, descriptions, and ticketing. While Luma has built-in video streaming, Event Schedule integrates with any video platform you already use.'],
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
                        ['Ticket waitlist', 'Yes (Pro)', 'No', true],
                        ['Check-in dashboard', 'Yes (Pro)', 'No', true],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'No', true],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'No', true],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
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
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes', false],
                        ['Webhooks', 'Yes (Pro)', 'No', true],
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
                        'description' => 'Paste event details in any format and our AI extracts all the details automatically. Skip manual data entry and populate your schedule in seconds.',
                        'icon' => 'ai',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with no vendor lock-in. Selfhost for complete data ownership, something no closed-source ticketing platform can offer.',
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
                        'description' => 'Generate shareable event graphics automatically. A built-in promotion tool that pure ticketing platforms lack.',
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
                    ['name' => 'Tito', 'route' => 'marketing.compare_tito'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Ticket Tailor to Event Schedule?', 'answer' => 'Yes. If you are familiar with Ticket Tailor, Event Schedule will feel natural. Create a free schedule, set up your ticket types, and connect Stripe. The ticketing workflow is straightforward and you can be selling tickets within minutes.'],
                    ['question' => 'How does Event Schedule pricing compare to Ticket Tailor?', 'answer' => 'Ticket Tailor charges per ticket sold. Event Schedule Pro is a flat $5/mo with zero platform fees, no matter how many tickets you sell. This makes costs predictable and lower for most organizers.'],
                    ['question' => 'What does Event Schedule offer that Ticket Tailor does not?', 'answer' => 'Event Schedule is open source and supports selfhosting, giving you full control over your data. It also includes AI event import, two-way Google Calendar sync, built-in newsletters, and fan engagement features that Ticket Tailor does not offer.'],
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
                        ['Ticket waitlist', 'Yes (Pro)', 'No', true],
                        ['Check-in dashboard', 'Yes (Pro)', 'No', true],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'No', true],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                        ['Free event RSVP', 'Yes (Free)', 'No', true],
                        ['Platform fees', '0%', 'N/A', true],
                    ],
                    'Event Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics generation', 'Yes (Pro)', 'No', true],
                        ['Rich descriptions (Markdown)', 'Yes (Free)', 'Plain text only', true],
                        ['Custom fields', 'Yes (Pro)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
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
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Webhooks', 'Yes (Pro)', 'No', true],
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
                        'icon' => 'globe',
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
                    ['name' => 'AddEvent', 'route' => 'marketing.compare_addevent'],
                ],
                'faq' => [
                    ['question' => 'Can I use Event Schedule alongside Google Calendar?', 'answer' => 'Yes. Event Schedule offers two-way Google Calendar sync on the free plan. Your events stay in sync between both platforms, so you can use Google Calendar as your personal view while Event Schedule powers your public schedule and ticketing.'],
                    ['question' => 'Is Event Schedule free like Google Calendar?', 'answer' => 'Event Schedule has a generous free plan that includes unlimited events, Google Calendar sync, newsletters, and team collaboration. The Pro plan at $5/mo adds ticketing, QR check-ins, and custom branding.'],
                    ['question' => 'Can Event Schedule handle ticketing that Google Calendar cannot?', 'answer' => 'Yes. Google Calendar has no ticketing features. Event Schedule Pro includes full ticketing with QR check-ins, a live dashboard, ticket waitlists, sale notifications, and sales CSV export. All with zero platform fees.'],
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
                        ['Ticket waitlist', 'Yes (Pro)', 'No', true],
                        ['Check-in dashboard', 'Yes (Pro)', 'No', true],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'No', true],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'No', true],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
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
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Co-organizers (paid)', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (limited)', true],
                        ['Webhooks', 'Yes (Pro)', 'No', true],
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
                'faq' => [
                    ['question' => 'Is it easy to switch from Meetup to Event Schedule?', 'answer' => 'Yes. Create a free schedule and add your recurring or one-time events. Share your schedule URL with your community. Event Schedule supports recurring events and newsletters to keep your group engaged.'],
                    ['question' => 'How does Event Schedule pricing compare to Meetup?', 'answer' => 'Meetup charges organizers a subscription starting at $14.99/mo for basic groups, with higher tiers for larger communities. Event Schedule has a free plan with unlimited events. The Pro plan is $5/mo with zero platform fees on ticket sales.'],
                    ['question' => 'Can Event Schedule handle community events and recurring meetups?', 'answer' => 'Yes. Event Schedule supports recurring events, fan engagement features like videos and comments, newsletters with A/B testing, and embeddable calendar widgets. All designed for building and maintaining community.'],
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
                        ['Ticket waitlist', 'Yes (Pro)', 'No', true],
                        ['Check-in dashboard', 'Yes (Pro)', 'No', true],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'No', true],
                        ['Recurring events', 'Yes (Free)', 'Limited', true],
                        ['Online events', 'Yes (Free)', 'Yes (livestream)', false],
                        ['Free event RSVP', 'Yes (Free)', 'No', true],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'No', true],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Push notifications', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Custom event pages', 'Yes (full control)', 'Template-based', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Widget only', true],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Limited', true],
                        ['Webhooks', 'Yes (Pro)', 'No', true],
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
                'faq' => [
                    ['question' => 'Is it easy to switch from DICE to Event Schedule?', 'answer' => 'Yes. Unlike DICE, Event Schedule works in any browser so your audience does not need to download an app. Create a free schedule, add your events, and share a direct link. Your fans can browse and buy tickets instantly.'],
                    ['question' => 'How does Event Schedule pricing compare to DICE?', 'answer' => 'DICE absorbs its fees into ticket prices, meaning fans pay more than your listed price. Event Schedule charges zero platform fees and connects directly to your Stripe account, so you control exactly what attendees pay.'],
                    ['question' => 'Does Event Schedule support music and nightlife events like DICE?', 'answer' => 'Yes. Event Schedule handles any event type including concerts, festivals, and nightlife. You get ticketing with QR check-ins, event graphics generation, and a shareable schedule page, without being locked into a single event genre.'],
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
                        ['Ticket waitlist', 'Yes (Pro)', 'No', true],
                        ['Check-in dashboard', 'Yes (Pro)', 'No', true],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'No', true],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Limited', true],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'No', true],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Basic email', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Modern event pages', 'Yes (responsive, customizable)', 'Dated design', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Limited widget', true],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Limited', true],
                        ['Webhooks', 'Yes (Pro)', 'No', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
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
                'faq' => [
                    ['question' => 'Is it easy to switch from Brown Paper Tickets to Event Schedule?', 'answer' => 'Yes. Many organizers have already moved away from Brown Paper Tickets due to reliability concerns. Create a free schedule, add your events (AI import speeds this up), and share your new URL. You can be up and running in minutes.'],
                    ['question' => 'How does Event Schedule pricing compare to Brown Paper Tickets?', 'answer' => 'Brown Paper Tickets charges $0.99 + 5% per ticket as a buyer-paid service fee. Event Schedule charges zero platform fees at any plan level. The Pro plan is a flat $5/mo for unlimited ticketing with direct Stripe payouts.'],
                    ['question' => 'Is Event Schedule actively maintained?', 'answer' => 'Yes. Event Schedule is built with a modern tech stack and receives regular updates. It is fully open source, so you can inspect the code, track development activity, and even selfhost it for complete control over your platform.'],
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
                        ['Ticket waitlist', 'Yes (Pro)', 'No', true],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'No', true],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
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
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Registration widget', true],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (enterprise)', false],
                        ['Webhooks', 'Yes (Pro)', 'No', true],
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
                    ['name' => 'Accelevents', 'route' => 'marketing.compare_accelevents'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Splash to Event Schedule?', 'answer' => 'Yes. Unlike Splash, there is no enterprise onboarding or sales process required. Just sign up, create a free schedule, add your events, and share your schedule URL. AI import can speed up event creation too.'],
                    ['question' => 'How does Event Schedule pricing compare to Splash?', 'answer' => 'Splash uses custom enterprise pricing that requires contacting sales, with contracts typically running thousands of dollars per year. Event Schedule Pro is a transparent $5/mo with zero platform fees and no annual commitment.'],
                    ['question' => 'Can Event Schedule handle corporate events like Splash?', 'answer' => 'Yes. Event Schedule supports custom domains, custom branding, team collaboration, and embeddable widgets. Enterprise features include custom CSS and API access for integration with your existing tools.'],
                ],
            ],
            'sched' => [
                'name' => 'Sched',
                'key' => 'sched',
                'slug' => 'sched-alternative',
                'tagline' => 'A more affordable, full-featured alternative to Sched with zero platform fees.',
                'description' => 'Compare Event Schedule with Sched. Get zero platform fees, calendar sync, and open source flexibility for $5/mo instead of $50+/mo.',
                'keywords' => 'sched alternative, sched.com alternative, conference schedule platform, event agenda alternative, sched competitor',
                'about' => 'Sched is a conference and event scheduling platform popular for managing multi-track agendas, speaker profiles, and personalized attendee schedules. It targets conferences, trade shows, and multi-day events.',
                'competitor_strengths' => [
                    'Multi-track conference agenda management with personalized attendee schedules',
                    'Comprehensive speaker and sponsor profile management',
                    'Session-level attendance tracking and feedback collection',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Small events only (under 50 attendees)', true],
                        ['Paid plan price', '$5/mo (7-day free trial)', 'From $50/mo', true],
                        ['Platform fees', '0%', 'Ticketing add-on required', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Stripe (separate)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes (paid add-on)', true],
                        ['QR check-ins', 'Yes (Pro)', 'Yes (Boost+)', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'No', true],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'No', true],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (small events)', true],
                        ['Event polls', 'Yes (Pro)', 'Feedback surveys only', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'Yes', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'Export/subscribe only', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Event email only', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'Yes', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes', false],
                        ['Webhooks', 'Yes (Pro)', 'No', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => '10x More Affordable',
                        'description' => '$5/mo vs $50+/mo starting price. Get more features for a fraction of the cost.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google Calendar and CalDAV sync included free. Sched only offers one-way iCal export.',
                        'icon' => 'calendar',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format and AI extracts everything automatically. Populate your conference agenda in seconds instead of entering sessions one by one.',
                        'icon' => 'ai',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Keep your audience engaged with built-in newsletters. Sched only has event email notifications.',
                        'icon' => 'mail',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Recurring Events',
                        'description' => 'Create automated recurring events on any schedule. Sched does not support recurring events.',
                        'icon' => 'calendar',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with selfhosting option. Selfhost for complete control, unlike Sched\'s closed-source hosted-only model.',
                        'icon' => 'code',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Whova', 'route' => 'marketing.compare_whova'],
                    ['name' => 'Accelevents', 'route' => 'marketing.compare_accelevents'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Sched to Event Schedule?', 'answer' => 'Yes. Create a free schedule and add your conference sessions or events. AI import lets you paste event details to populate your schedule quickly. No migration needed.'],
                    ['question' => 'How does Event Schedule pricing compare to Sched?', 'answer' => 'Sched charges per-event or annual pricing that scales with attendee count. Event Schedule Pro is a flat $5/mo with unlimited events and zero platform fees on ticket sales.'],
                    ['question' => 'Can Event Schedule handle conference agendas like Sched?', 'answer' => 'Yes. Event Schedule supports multi-day events, sub-schedules for organizing tracks or sessions, and embeddable calendar widgets. Attendees can browse your full conference agenda from a single schedule page.'],
                ],
            ],
            'whova' => [
                'name' => 'Whova',
                'key' => 'whova',
                'slug' => 'whova-alternative',
                'tagline' => 'A transparent, affordable alternative to Whova with zero platform fees.',
                'description' => 'Compare Event Schedule with Whova. Get transparent pricing, zero platform fees, and open source flexibility without custom quotes or sales calls.',
                'keywords' => 'whova alternative, whova alternative free, event app alternative, whova competitor, event management platform',
                'about' => 'Whova is an event management and networking platform popular for conferences and corporate events. It offers a feature-rich mobile app with attendee networking, live polls, and engagement tools, with custom quote-based pricing.',
                'competitor_strengths' => [
                    'Advanced attendee networking with AI-powered matchmaking and messaging',
                    'Feature-rich mobile app with high attendee engagement rates',
                    'Comprehensive virtual and hybrid event capabilities',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'No (quote-based)', true],
                        ['Paid plan price', '$5/mo (7-day free trial)', 'Custom quotes', true],
                        ['Platform fees', '0%', '3% + $0.99/ticket', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Stripe (2.9% + $0.30)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'No', true],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'No', true],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'Yes', false],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'Yes', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No (Zapier only)', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Announcements only', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No (not white-label)', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Agenda widget', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Not public', true],
                        ['Webhooks', 'Yes (Pro)', 'No (Zapier only)', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Transparent Pricing',
                        'description' => '$5/mo flat vs opaque custom quotes requiring sales calls. No surprises, no negotiations.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => '0% Platform Fees',
                        'description' => 'Whova charges 3% + $0.99 per paid ticket on top of their subscription. Event Schedule has zero platform fees.',
                        'icon' => 'percent',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google Calendar and CalDAV sync included free. Whova requires a Zapier add-on for basic calendar connections.',
                        'icon' => 'calendar',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'Custom CSS',
                        'description' => 'Full design control with custom CSS. Whova limits customization to their dashboard options.',
                        'icon' => 'code',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with selfhosting option. Selfhost for complete data ownership, free from Whova\'s opaque enterprise contracts.',
                        'icon' => 'code',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Event Graphics',
                        'description' => 'Auto-generate shareable event images for social media. Not available on Whova.',
                        'icon' => 'image',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Sched', 'route' => 'marketing.compare_sched'],
                    ['name' => 'Accelevents', 'route' => 'marketing.compare_accelevents'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Whova to Event Schedule?', 'answer' => 'Yes. Unlike Whova, there is no sales process or demo required. Just sign up, create a free schedule, and start adding events. AI import can parse pasted event details to speed things up.'],
                    ['question' => 'How does Event Schedule pricing compare to Whova?', 'answer' => 'Whova uses quote-based enterprise pricing that requires a sales call, with costs typically running thousands of dollars per event. Event Schedule Pro is a flat $5/mo with unlimited events and zero platform fees on ticket sales.'],
                    ['question' => 'Does Event Schedule offer attendee engagement features like Whova?', 'answer' => 'Yes. Event Schedule includes fan videos and comments, newsletters with A/B testing, event polls, and post-event feedback, all built into the platform without per-event pricing.'],
                ],
            ],
            'accelevents' => [
                'name' => 'Accelevents',
                'key' => 'accelevents',
                'slug' => 'accelevents-alternative',
                'tagline' => 'A simpler, more affordable alternative to Accelevents without enterprise pricing.',
                'description' => 'Compare Event Schedule with Accelevents. Get zero platform fees, instant setup, and open source flexibility for $5/mo instead of $7,500+/year.',
                'keywords' => 'accelevents alternative, accelevents alternative free, event management platform, accelevents competitor, affordable event platform',
                'about' => 'Accelevents is an enterprise event management platform offering in-person, virtual, and hybrid event solutions. It targets mid-to-large organizations with badge printing, CRM integrations, and white-label capabilities.',
                'competitor_strengths' => [
                    'Enterprise-grade badge printing and onsite check-in with kiosk support',
                    'Native CRM integrations with Salesforce, HubSpot, and Marketo',
                    'Comprehensive virtual and hybrid event hosting with built-in streaming',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Limited (high per-ticket fees)', true],
                        ['Paid plan price', '$5/mo (7-day free trial)', 'From $7,500/year', true],
                        ['Platform fees', '0%', '$1 + 1%/ticket (paid plans)', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', '2.9% + $0.30', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'Yes', false],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes (in-person only)', true],
                        ['Online events', 'Yes (Free)', 'Yes (paid add-on)', true],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'Yes', false],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'Yes (surveys)', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No (add-to-calendar only)', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Yes (Engage module)', false],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'White Label plan only', true],
                        ['Remove branding', 'Yes (Pro)', 'White Label plan only', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'Yes', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (unlimited admins)', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (Enterprise only)', true],
                        ['Webhooks', 'Yes (Pro)', 'Yes (Enterprise only)', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                        ['Setup time', 'Minutes', 'Weeks (implementation)', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => '$5/mo vs $7,500+/year',
                        'description' => '125x more affordable for core event management features. No enterprise contracts required.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Instant Setup',
                        'description' => 'Start in minutes vs weeks of enterprise onboarding. No implementation team needed.',
                        'icon' => 'calendar',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'No Sales Process',
                        'description' => 'Sign up and go. No demos, no contracts, no waiting for a sales rep to get back to you.',
                        'icon' => 'globe',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google Calendar and CalDAV sync included free. Accelevents only offers add-to-calendar links.',
                        'icon' => 'calendar',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details and AI extracts everything automatically. Not available on Accelevents.',
                        'icon' => 'ai',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Full transparency and selfhosting option. No vendor lock-in or enterprise contracts.',
                        'icon' => 'code',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Whova', 'route' => 'marketing.compare_whova'],
                    ['name' => 'Splash', 'route' => 'marketing.compare_splash'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Accelevents to Event Schedule?', 'answer' => 'Yes. Unlike Accelevents, there is no enterprise onboarding or implementation process. Sign up, create a free schedule, and start adding events immediately. AI import can parse pasted event details to speed things up.'],
                    ['question' => 'How does Event Schedule pricing compare to Accelevents?', 'answer' => 'Accelevents starts at $7,500/year with additional per-ticket fees ($1 + 1%) on paid plans. Event Schedule Pro is a flat $5/mo with unlimited events and zero platform fees.'],
                    ['question' => 'Can Event Schedule handle virtual and hybrid events like Accelevents?', 'answer' => 'Yes. Event Schedule supports online events with video links and integrates with any streaming platform. Combined with ticketing, QR check-ins, and newsletters, it covers both virtual and in-person needs.'],
                ],
            ],
            'tito' => [
                'name' => 'Tito',
                'key' => 'tito',
                'slug' => 'tito-alternative',
                'tagline' => 'A flat-rate alternative to Tito with calendar sync, newsletters, and zero per-ticket fees.',
                'description' => 'Compare Event Schedule with Tito. Get flat $5/mo pricing instead of 3% per ticket, plus calendar sync, newsletters, and selfhosting.',
                'keywords' => 'tito alternative, ti.to alternative, tito ticketing alternative, event ticketing platform, tito competitor',
                'about' => 'Tito is a simple, developer-friendly ticketing platform popular with tech conferences and community events. It charges 3% per paid ticket with no monthly subscription, and is known for its clean interface and well-documented API.',
                'competitor_strengths' => [
                    'Simple, no-frills interface focused purely on ticketing',
                    'Developer-friendly with well-documented REST API and webhooks',
                    'No monthly fee, with free branding removal included',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free for free events', false],
                        ['Paid plan price', '$5/mo (7-day free trial)', '3% per ticket (cap 25 EUR)', true],
                        ['Platform fees', '0%', '3% per ticket', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Stripe/PayPal', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes (iOS/Android/web)', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'No (webhooks only)', true],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'No', true],
                        ['Online events', 'Yes (Free)', 'Yes (via meeting links)', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Basic messaging only', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No (discontinued)', true],
                        ['Remove branding', 'Yes (Pro)', 'Yes (free)', false],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'Yes (widget mode)', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Checkout widget only', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (4 roles)', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes', false],
                        ['Webhooks', 'Yes (Pro)', 'Yes', false],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Flat-Rate Pricing',
                        'description' => '$5/mo flat vs 3% per ticket. Sell 200 x $25 tickets and save $145/mo.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google Calendar and CalDAV sync included free. Tito has no calendar integration.',
                        'icon' => 'calendar',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Keep your audience engaged with built-in newsletters. Tito only has basic transactional messaging.',
                        'icon' => 'mail',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'Custom Domains',
                        'description' => 'Use your own domain for your event pages. Tito discontinued custom domain support.',
                        'icon' => 'globe',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Event Graphics',
                        'description' => 'Auto-generate shareable images for social media. Not available on Tito.',
                        'icon' => 'image',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with selfhosting option. Inspect the code, contribute, or selfhost for complete data ownership.',
                        'icon' => 'code',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Pretix', 'route' => 'marketing.compare_pretix'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Tito to Event Schedule?', 'answer' => 'Yes. Create a free schedule and start adding events right away. AI import lets you paste event details to populate your listings quickly. No migration scripts required.'],
                    ['question' => 'How does Event Schedule pricing compare to Tito?', 'answer' => 'Tito charges per-ticket fees on each sale. Event Schedule charges zero platform fees at every plan level. The Pro plan is a flat $5/mo for unlimited ticketing and events.'],
                    ['question' => 'Does Event Schedule support developer-friendly features like Tito?', 'answer' => 'Yes. Event Schedule offers a REST API, webhooks, custom CSS, and is fully open source. You can selfhost it, fork it, or integrate it with your own tools and workflows.'],
                ],
            ],
            'addevent' => [
                'name' => 'AddEvent',
                'key' => 'addevent',
                'slug' => 'addevent-alternative',
                'tagline' => 'A complete event platform that goes beyond "Add to Calendar" buttons.',
                'description' => 'Compare Event Schedule with AddEvent. Get ticketing, public event pages, and a full event platform for $5/mo instead of just calendar buttons at $29/mo.',
                'keywords' => 'addevent alternative, add to calendar alternative, addevent competitor, event calendar platform, calendar button alternative',
                'about' => 'AddEvent is a calendar marketing tool focused on "Add to Calendar" buttons, subscription calendars, and RSVP collection. It helps drive event attendance through calendar engagement but does not offer ticketing or event management features.',
                'competitor_strengths' => [
                    'Industry-leading "Add to Calendar" buttons supporting all major calendar apps',
                    'Subscription calendars that auto-sync updates to followers\' personal calendars',
                    'Easy integration with websites, emails, and marketing platforms',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (100 clicks/mo, 20 RSVPs)', true],
                        ['Paid plan price', '$5/mo (7-day free trial)', 'From $29/mo', true],
                        ['Platform fees', '0%', 'N/A (no ticketing)', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'N/A (no ticketing)', true],
                    ],
                    'Events & Calendar' => [
                        ['Ticketing', 'Yes (Pro)', 'No', true],
                        ['QR check-ins', 'Yes (Pro)', 'No', true],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (limited)', true],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'No', true],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'No', true],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                        ['Add to calendar buttons', 'Via embeds', 'Yes (core feature)', false],
                        ['Subscription calendars', 'Via CalDAV/embeds', 'Yes (core feature)', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'Import only (one-way)', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'RSVP emails only', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Yes (via support)', false],
                        ['Remove branding', 'Yes (Pro)', 'Yes (paid plans)', false],
                        ['Custom fields', 'Yes (Pro)', 'Yes (RSVP forms)', false],
                        ['Custom CSS', 'Yes (Pro)', 'Yes (Professional+)', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes (paid plans)', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (Small Business+)', false],
                        ['Webhooks', 'Yes (Pro)', 'No (Zapier only)', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Full Ticketing',
                        'description' => 'Sell tickets with QR check-in and zero platform fees. AddEvent has no ticketing whatsoever.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Public Event Pages',
                        'description' => 'SEO-optimized event pages with rich descriptions. AddEvent only offers calendar/RSVP landing pages.',
                        'icon' => 'globe',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => '6x More Affordable',
                        'description' => '$5/mo vs $29/mo, with far more features included.',
                        'icon' => 'percent',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details and AI extracts everything automatically. Not available on AddEvent.',
                        'icon' => 'ai',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Bidirectional Google Calendar and CalDAV sync. AddEvent only imports one-way.',
                        'icon' => 'calendar',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with selfhosting. Selfhost for full ownership of your event data and platform.',
                        'icon' => 'code',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Google Calendar', 'route' => 'marketing.compare_google_calendar'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from AddEvent to Event Schedule?', 'answer' => 'Yes. Create a free schedule and add your events manually or via AI import. Event Schedule provides a full event management platform, not just a calendar widget.'],
                    ['question' => 'How does Event Schedule pricing compare to AddEvent?', 'answer' => 'AddEvent charges $29/mo for its premium plan. Event Schedule Pro is $5/mo with zero platform fees. Even the free plan includes unlimited events, Google Calendar sync, and newsletters.'],
                    ['question' => 'How is Event Schedule different from AddEvent?', 'answer' => 'AddEvent focuses on calendar add-to-calendar buttons and embeds. Event Schedule is a complete event management platform with ticketing, QR check-ins, newsletters, AI import, and public schedule pages.'],
                ],
            ],
            'pretix' => [
                'name' => 'Pretix',
                'key' => 'pretix',
                'slug' => 'pretix-alternative',
                'tagline' => 'A broader open source event platform with flat pricing and more built-in features.',
                'description' => 'Compare Event Schedule with Pretix. Get flat $5/mo pricing instead of per-ticket fees, plus calendar sync, newsletters, AI features, and fan engagement.',
                'keywords' => 'pretix alternative, pretix alternative free, open source ticketing alternative, pretix competitor, event platform open source',
                'about' => 'Pretix is an open source ticketing platform (AGPLv3) based in Germany, focused on ticket sales for conferences, festivals, and exhibitions. It offers a selfhosted Community Edition and a paid hosted service with per-ticket pricing.',
                'competitor_strengths' => [
                    'Open source (AGPLv3) with full selfhosted Community Edition',
                    'Seating plan editor with interactive graphical seat selection',
                    'Offline-capable check-in app (pretixSCAN) and point-of-sale system',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Selfhosted only (Community Edition)', true],
                        ['Hosted pricing', '$5/mo (7-day free trial)', '2.5%/ticket (cap 15 EUR)', true],
                        ['Platform fees', '0%', '2.5% per ticket', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Stripe/PayPal/Mollie', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes (pretixSCAN, offline-capable)', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes (automated)', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'Yes', false],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes (Event Series)', false],
                        ['Online events', 'Yes (Free)', 'Yes (Venueless integration)', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (free tickets)', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes (vouchers + discounts)', false],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'MailChimp integration', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Yes', false],
                        ['Remove branding', 'Yes (Pro)', 'Partial (subtle branding)', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'Yes (via plugin)', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Ticket widget only', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (unlimited teams)', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes', false],
                        ['Webhooks', 'Yes (Pro)', 'Yes', false],
                        ['Open source', 'Yes', 'Yes (AGPLv3)', false],
                        ['Selfhosting', 'Yes', 'Yes', false],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Flat $5/mo Pricing',
                        'description' => 'No per-ticket fees ever. Pretix charges 2.5% per ticket on their hosted plan.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google Calendar and CalDAV sync included free. Pretix has no calendar integration.',
                        'icon' => 'calendar',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Engage your audience directly. Pretix requires a separate MailChimp integration.',
                        'icon' => 'mail',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details and AI extracts everything automatically. Not available on Pretix.',
                        'icon' => 'ai',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Fan Engagement',
                        'description' => 'Let attendees share videos and comments on events. Pretix is purely a ticketing tool.',
                        'icon' => 'image',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Simpler Setup',
                        'description' => 'Simpler to selfhost than Pretix: just PHP and MySQL vs Docker, PostgreSQL, and Redis.',
                        'icon' => 'globe',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Tito', 'route' => 'marketing.compare_tito'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Pretix to Event Schedule?', 'answer' => 'Yes. Create a free schedule and start adding events immediately. No data migration or server setup required. AI import can parse pasted event details to speed up the process.'],
                    ['question' => 'How does Event Schedule pricing compare to Pretix?', 'answer' => 'Pretix Hosted charges per-ticket fees on each sale. Event Schedule Pro is a flat $5/mo with zero platform fees. Both platforms are open source, but Event Schedule is simpler to selfhost.'],
                    ['question' => 'Is Event Schedule easier to selfhost than Pretix?', 'answer' => 'Yes. Pretix selfhosting requires Docker, PostgreSQL, Redis, and significant server administration. Event Schedule runs on standard PHP hosting with MySQL, making it accessible to a much wider range of hosting environments.'],
                ],
            ],

            'humanitix' => [
                'name' => 'Humanitix',
                'key' => 'humanitix',
                'slug' => 'humanitix-alternative',
                'tagline' => 'Keep 100% of your ticket revenue with flat $5/mo pricing and zero platform fees.',
                'description' => 'Compare Event Schedule with Humanitix. Get flat $5/mo pricing instead of per-ticket fees, plus calendar sync, newsletters, AI features, and selfhosting.',
                'keywords' => 'humanitix alternative, humanitix alternative free, event ticketing platform, humanitix competitor, affordable event platform',
                'about' => 'Humanitix is a ticketing platform that donates profits to charity, primarily children\'s education. It charges per-ticket fees (5% + $1.29) with no subscription plans. It offers ticketing, QR check-in, promo codes, and embeddable widgets.',
                'competitor_strengths' => [
                    'Profits go to charity (children\'s education and humanitarian causes)',
                    'No subscription required with pay-as-you-go per-ticket pricing',
                    'Nonprofit discount pricing (3.9% + $1.29 per ticket)',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free for free events', false],
                        ['Paid plan price', '$5/mo (7-day free trial)', 'No subscription (per-ticket only)', true],
                        ['Platform fees', '0%', '5% + $1.29/ticket (3.9% + $1.29 nonprofits)', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Included in platform fee', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'Yes', false],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'No', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Widget only', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes', false],
                        ['Webhooks', 'Yes (Pro)', 'No', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Flat $5/mo Pricing',
                        'description' => 'No per-ticket fees ever. Humanitix charges 5% + $1.29 per ticket on every paid event.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google Calendar and CalDAV sync included free. Humanitix has no calendar integration.',
                        'icon' => 'calendar',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Engage your audience directly with built-in newsletters. Not available on Humanitix.',
                        'icon' => 'mail',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details and AI extracts everything automatically. Not available on Humanitix.',
                        'icon' => 'ai',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Open Source & Selfhosted',
                        'description' => 'Full source code access and selfhosting option. Humanitix is a closed-source hosted-only platform.',
                        'icon' => 'globe',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Fan Engagement',
                        'description' => 'Let attendees share videos and comments on events. Build community around your events, not just ticket sales.',
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
                    ['name' => 'Eventzilla', 'route' => 'marketing.compare_eventzilla'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Humanitix to Event Schedule?', 'answer' => 'Yes. Create a free schedule and start adding events right away. With zero platform fees, you keep more of your ticket revenue and can donate directly to causes you care about on your own terms.'],
                    ['question' => 'How does Event Schedule pricing compare to Humanitix?', 'answer' => 'Humanitix charges 5% + $1.29 per ticket (3.9% + $1.29 for nonprofits). Event Schedule charges zero platform fees at every plan level. The Pro plan is a flat $5/mo regardless of ticket volume.'],
                    ['question' => 'Does Humanitix donate to charity while Event Schedule does not?', 'answer' => 'Humanitix donates its profits to charity, which is funded by per-ticket fees on your sales. With Event Schedule, you keep 100% of your ticket revenue (minus Stripe processing) and can donate to causes you choose directly.'],
                ],
            ],

            'eventzilla' => [
                'name' => 'Eventzilla',
                'key' => 'eventzilla',
                'slug' => 'eventzilla-alternative',
                'tagline' => 'A flat-rate open source alternative to Eventzilla with zero per-ticket fees.',
                'description' => 'Compare Event Schedule with Eventzilla. Get flat $5/mo pricing instead of per-ticket fees, plus calendar sync, newsletters, AI features, and open source.',
                'keywords' => 'eventzilla alternative, eventzilla alternative free, event registration platform, eventzilla competitor, affordable event platform',
                'about' => 'Eventzilla is an event registration and ticketing platform offering per-ticket pricing across multiple tiers. Plans range from $1.50/registration (Basic) to $5,999/year (Unlimited). It includes features like badge printing, speaker management, live streaming, and CRM integrations.',
                'competitor_strengths' => [
                    'Badge printing with customizable templates and QR codes',
                    'Speaker and session management for conferences',
                    'Native integrations with Zapier, HubSpot, and Salesforce',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free for free events only', true],
                        ['Paid plan price', '$5/mo (7-day free trial)', '$1.50 to $2.90/ticket or $5,999/yr', true],
                        ['Platform fees', '0%', '1.9% to 2.9% (Pro/Plus plans)', true],
                        ['Payment processing', 'Stripe (2.9% + $0.30)', 'Stripe/PayPal (separate fees)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Pro)', 'Yes (kiosk mode)', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'Yes', false],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes (live streaming)', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'Yes (surveys)', false],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'Yes (surveys)', false],
                    ],
                    'Integrations' => [
                        ['Google Calendar sync', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'No (Mailchimp/HubSpot integration)', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Yes (Plus/Unlimited)', false],
                        ['Remove branding', 'Yes (Pro)', 'Yes (Plus/Unlimited)', false],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI event parsing', 'Yes (Enterprise)', 'No', true],
                        ['Event graphics gen', 'Yes (Pro)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Widget only', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Yes', false],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes', false],
                        ['Webhooks', 'Yes (Pro)', 'Yes (Zapier)', false],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Flat $5/mo Pricing',
                        'description' => 'No per-ticket fees ever. Eventzilla charges $1.50 to $2.90 per ticket plus percentage fees.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google Calendar and CalDAV sync included free. Eventzilla has no calendar integration.',
                        'icon' => 'calendar',
                        'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30',
                        'border' => 'border-violet-200 dark:border-violet-500/20',
                        'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
                        'icon_color' => 'text-violet-600 dark:text-violet-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Engage your audience directly with built-in newsletters. Eventzilla requires third-party integrations.',
                        'icon' => 'mail',
                        'gradient' => 'from-fuchsia-50 to-pink-50 dark:from-fuchsia-900/30 dark:to-pink-900/30',
                        'border' => 'border-fuchsia-200 dark:border-fuchsia-500/20',
                        'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
                        'icon_color' => 'text-fuchsia-600 dark:text-fuchsia-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details and AI extracts everything automatically. Not available on Eventzilla.',
                        'icon' => 'ai',
                        'gradient' => 'from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/30',
                        'border' => 'border-indigo-200 dark:border-indigo-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Open Source & Selfhosted',
                        'description' => 'Full source code access and selfhosting option. Eventzilla is a closed-source hosted-only platform.',
                        'icon' => 'globe',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Fan Engagement',
                        'description' => 'Let attendees share videos and comments on events. Eventzilla focuses on registration and ticketing.',
                        'icon' => 'image',
                        'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30',
                        'border' => 'border-rose-200 dark:border-rose-500/20',
                        'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
                        'icon_color' => 'text-rose-600 dark:text-rose-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Humanitix', 'route' => 'marketing.compare_humanitix'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Eventzilla to Event Schedule?', 'answer' => 'Yes. Create a free schedule and add your events manually or via AI import. Share your new schedule URL and start selling tickets immediately. No complex migration needed.'],
                    ['question' => 'How does Event Schedule pricing compare to Eventzilla?', 'answer' => 'Eventzilla charges $1.50 to $2.90 per ticket plus percentage fees depending on the plan. Event Schedule Pro is a flat $5/mo with zero platform fees, making costs predictable regardless of ticket volume.'],
                    ['question' => 'Can Event Schedule handle registration features like Eventzilla?', 'answer' => 'Yes. Event Schedule includes ticketing with custom fields, QR check-ins, a live dashboard, ticket waitlists, and sales CSV export. It also adds features Eventzilla lacks, like Google Calendar sync, newsletters, and AI event import.'],
                ],
            ],
        ];

        return $competitors[$competitor];
    }

    /**
     * Get replacement page data for a given tool
     */
    private function getReplacementData(string $tool): array
    {
        $tools = [
            'google-forms' => [
                'name' => 'Google Forms',
                'tagline' => 'Stop building event registration forms from scratch.',
                'description' => 'Replace Google Forms for event registration with Event Schedule. Get built-in ticketing, payments, public event pages, and attendee management.',
                'keywords' => 'Google Forms alternative for events, Google Forms replacement, event registration form, event signup form',
                'about' => 'Google Forms is a free form builder used by many event organizers to collect RSVPs and registrations. While it works for basic data collection, it was never designed for event management - leaving organizers to manually handle payments, confirmations, and attendee tracking outside the form.',
                'tool_strengths' => [
                    'Free and easy to set up for basic data collection',
                    'Familiar interface within the Google ecosystem',
                    'Flexible custom fields for any type of form',
                ],
                'pain_points' => [
                    'No built-in ticketing or payment processing',
                    'No public event pages or shareable calendar',
                    'Manual work to send confirmations and track attendees',
                    'No QR code check-in or event day tools',
                ],
                'es_solutions' => [
                    ['title' => 'Built-in Ticketing', 'description' => 'Sell tickets with Stripe payments, automatic confirmations, and zero platform fees. No manual invoicing needed.', 'icon' => 'ticket', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Public Event Pages', 'description' => 'Every event gets a shareable page with all the details. No more sending form links with separate info docs.', 'icon' => 'globe', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'QR Code Check-in', 'description' => 'Every ticket includes a QR code. Scan attendees in at the door with a live check-in dashboard.', 'icon' => 'qr', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'AI Event Import', 'description' => 'Paste event details in any format and AI extracts dates, times, locations, and descriptions automatically.', 'icon' => 'ai', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'Calendar Sync', 'description' => 'Two-way Google Calendar sync keeps your events updated everywhere, automatically.', 'icon' => 'calendar', 'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30', 'border' => 'border-rose-200 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                    ['title' => 'Newsletters', 'description' => 'Send event updates to your audience with built-in newsletters and A/B testing. No separate email tool needed.', 'icon' => 'mail', 'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30', 'border' => 'border-violet-200 dark:border-violet-500/20', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                ],
                'why_switch' => [
                    'intro' => 'Google Forms collects data, but Event Schedule manages your entire event workflow from registration to check-in day.',
                    'points' => [
                        'Built-in ticketing with Stripe payments and zero platform fees',
                        'Public event pages with all details, not just a form link',
                        'Automatic confirmation emails and QR code tickets',
                        'Two-way Google Calendar sync keeps everything in one place',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your schedule', 'description' => 'Sign up free and create your first schedule in under a minute.'],
                    ['title' => 'Add your events', 'description' => 'Paste your event details and AI imports them, or create events manually with custom fields.'],
                    ['title' => 'Share your event page', 'description' => 'Share the event link instead of a form link. Attendees register and pay in one step.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Google Forms to Event Schedule?', 'answer' => 'Yes. You can set up your first event in minutes. Paste your event details and AI will extract dates, times, and descriptions automatically. There is no data migration needed since Google Forms does not store event data in a structured way.'],
                    ['question' => 'Is Event Schedule really free?', 'answer' => 'Yes. The free plan includes unlimited events, Google Calendar sync, newsletters, and fan engagement features. Ticketing with zero platform fees is available on the Pro plan for $5/month. No credit card required to start.'],
                    ['question' => 'Can Event Schedule handle custom registration fields like Google Forms?', 'answer' => 'Yes. Pro plan includes custom fields on ticket forms so you can collect any information you need from attendees. Unlike Google Forms, the data is automatically connected to your attendee records and ticket sales.'],
                    ['question' => 'Is Event Schedule open source?', 'answer' => 'Yes. Event Schedule is fully open source and can be selfhosted on your own server for complete control over your data. The hosted version at eventschedule.com is also available if you prefer a managed solution.'],
                    ['question' => 'Can I collect RSVPs for free events without Google Forms?', 'answer' => 'Yes. Event Schedule supports free RSVPs with automatic confirmation emails. Attendees register through your event page and you get a real attendee list with check-in tools, not just form responses.'],
                ],
                'cross_links' => [
                    ['name' => 'SurveyMonkey', 'route' => 'marketing.replace_surveymonkey'],
                    ['name' => 'Google Sheets', 'route' => 'marketing.replace_google_sheets'],
                    ['name' => 'Mailchimp', 'route' => 'marketing.replace_mailchimp'],
                ],
                'related_features' => [
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Sell tickets with QR codes and zero platform fees.'],
                    ['name' => 'Custom Fields', 'route' => 'marketing.custom_fields', 'description' => 'Collect attendee information with custom form fields.'],
                    ['name' => 'AI Import', 'route' => 'marketing.ai', 'description' => 'Paste event details and let AI create your events.'],
                ],
            ],
            'mailchimp' => [
                'name' => 'Mailchimp',
                'tagline' => 'Your event emails and ticketing in one place.',
                'description' => 'Replace Mailchimp for event communications with Event Schedule. Built-in newsletters with A/B testing, attendee management, and ticketing.',
                'keywords' => 'Mailchimp alternative for events, Mailchimp replacement, event email marketing, event newsletter',
                'about' => 'Mailchimp is a popular email marketing platform that many event organizers use to promote events and communicate with attendees. However, using Mailchimp means managing a separate tool alongside your event platform, manually syncing attendee lists, and paying for email marketing on top of your event tools.',
                'tool_strengths' => [
                    'Powerful email automation and drip campaigns',
                    'Advanced audience segmentation and analytics',
                    'Large template library for marketing emails',
                ],
                'pain_points' => [
                    'Separate tool that does not connect to your event data',
                    'Manual import/export of attendee lists',
                    'No event pages, ticketing, or RSVP functionality',
                    'Costs add up alongside other event tools',
                ],
                'es_solutions' => [
                    ['title' => 'Built-in Newsletters', 'description' => 'Send event updates and promotions to subscribers directly from your schedule. A/B test subject lines to maximize opens.', 'icon' => 'mail', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Integrated Attendee Data', 'description' => 'Your ticket buyers and subscribers are already in the system. No manual list imports or CSV juggling.', 'icon' => 'chart', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Zero Platform Fees', 'description' => 'Sell tickets without platform fees. Only pay standard Stripe processing. Save money you would spend on Mailchimp plus ticketing.', 'icon' => 'dollar', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'Event Graphics', 'description' => 'Generate shareable event graphics and flyers automatically. Promote events without needing separate design tools.', 'icon' => 'image', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'Public Schedule Page', 'description' => 'A shareable page with all your events. Visitors can subscribe to updates and buy tickets in one place.', 'icon' => 'globe', 'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30', 'border' => 'border-rose-200 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                    ['title' => 'Open Source & Selfhosted', 'description' => 'Full source code access and selfhosting option. Own your event platform completely, no vendor lock-in.', 'icon' => 'code', 'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30', 'border' => 'border-violet-200 dark:border-violet-500/20', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                ],
                'why_switch' => [
                    'intro' => 'Mailchimp handles email, but Event Schedule combines newsletters, ticketing, and attendee management so you never juggle separate tools.',
                    'points' => [
                        'Newsletters with A/B testing built into your event platform',
                        'Subscriber lists that sync automatically with ticket buyers',
                        'Zero platform fees on ticket sales, saving the cost of Mailchimp plus a ticketing tool',
                        'Open source with selfhosting option for full data ownership',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your schedule', 'description' => 'Sign up free and set up your schedule with events, branding, and ticket options.'],
                    ['title' => 'Import your subscribers', 'description' => 'Your attendees subscribe directly through your schedule page. New ticket buyers are added automatically.'],
                    ['title' => 'Send your first newsletter', 'description' => 'Write and send event updates with the built-in newsletter builder. A/B test subject lines to maximize engagement.'],
                ],
                'faq' => [
                    ['question' => 'Can Event Schedule replace Mailchimp for event emails?', 'answer' => 'Yes. Event Schedule includes built-in newsletters with A/B testing, subscriber management, and automatic attendee data integration. You can send event announcements and updates directly from your schedule without a separate email tool.'],
                    ['question' => 'How much does Event Schedule cost compared to Mailchimp?', 'answer' => 'Event Schedule is free for newsletters, subscriber management, and unlimited events. The Pro plan at $5/month adds ticketing with zero platform fees. Mailchimp charges based on subscriber count and can cost $20 or more per month for similar email features alone.'],
                    ['question' => 'Does Event Schedule support email automation like Mailchimp?', 'answer' => 'Event Schedule focuses on event-specific communications: newsletters to subscribers, sale confirmations, and event updates. While it does not have Mailchimp-style drip campaigns, it covers the email needs of most event organizers without requiring a separate platform.'],
                    ['question' => 'Is Event Schedule open source?', 'answer' => 'Yes. Event Schedule is fully open source and can be selfhosted on your own server for complete control over your data and subscriber lists. The hosted version at eventschedule.com is also available if you prefer a managed solution.'],
                    ['question' => 'Can I send newsletters to people who bought tickets?', 'answer' => 'Yes. Ticket buyers are automatically part of your subscriber base. You can send newsletters to all subscribers or target specific segments without manually importing attendee lists.'],
                ],
                'cross_links' => [
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms'],
                    ['name' => 'Canva', 'route' => 'marketing.replace_canva'],
                    ['name' => 'Linktree', 'route' => 'marketing.replace_linktree'],
                ],
                'related_features' => [
                    ['name' => 'Newsletters', 'route' => 'marketing.newsletters', 'description' => 'Send event updates with A/B testing built in.'],
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Sell tickets with QR codes and zero platform fees.'],
                    ['name' => 'Event Graphics', 'route' => 'marketing.event_graphics', 'description' => 'Auto-generate shareable graphics for promotion.'],
                ],
            ],
            'canva' => [
                'name' => 'Canva',
                'tagline' => 'Event graphics that create themselves.',
                'description' => 'Replace Canva for event flyers and graphics with Event Schedule. AI-generated event graphics and shareable flyers are created automatically from your event details.',
                'keywords' => 'Canva alternative for events, Canva replacement, event flyer maker, event graphic generator',
                'about' => 'Canva is a popular graphic design platform that event organizers use to create flyers, social media posts, and promotional graphics. While Canva offers great design flexibility, creating event graphics manually takes time and requires updating multiple designs whenever event details change.',
                'tool_strengths' => [
                    'Extensive template library for many design needs',
                    'Intuitive drag-and-drop design interface',
                    'Broad use beyond events (presentations, social media, etc.)',
                ],
                'pain_points' => [
                    'Manual design work for every event graphic',
                    'Graphics become outdated when event details change',
                    'No connection to your actual event data or ticket sales',
                    'Time spent on design instead of event management',
                ],
                'es_solutions' => [
                    ['title' => 'Auto-generated Graphics', 'description' => 'Event Schedule generates shareable event graphics automatically from your event details. No design work required.', 'icon' => 'image', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'AI Flyer Generation', 'description' => 'Generate professional event flyers with AI. Unique designs created from your event details in seconds.', 'icon' => 'ai', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Always Up to Date', 'description' => 'Graphics update when your event details change. No need to re-create flyers for date or venue changes.', 'icon' => 'calendar', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'Integrated with Ticketing', 'description' => 'Your graphics link directly to event pages where people can buy tickets. No separate tools to connect.', 'icon' => 'ticket', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'Schedule Graphics', 'description' => 'Generate graphics for your entire schedule, not just individual events. Perfect for sharing on social media.', 'icon' => 'globe', 'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30', 'border' => 'border-rose-200 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                    ['title' => 'Zero Platform Fees', 'description' => 'Sell tickets without platform fees. Only pay standard Stripe processing. Save the cost of Canva Pro plus a separate ticketing tool.', 'icon' => 'dollar', 'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30', 'border' => 'border-violet-200 dark:border-violet-500/20', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                ],
                'why_switch' => [
                    'intro' => 'Canva makes beautiful designs, but Event Schedule generates event graphics automatically so you can focus on running your events.',
                    'points' => [
                        'AI-generated flyers and graphics created from your event details in seconds',
                        'Graphics always stay in sync when event details change',
                        'Event pages and ticket sales are connected to every graphic',
                        'One platform for graphics, ticketing, newsletters, and calendar sync',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your events', 'description' => 'Add event details including dates, venue, and description. AI can import them automatically.'],
                    ['title' => 'Generate graphics', 'description' => 'Event graphics and AI flyers are created automatically from your event details. No design work needed.'],
                    ['title' => 'Share and sell', 'description' => 'Share graphics on social media that link directly to your event pages where people can buy tickets.'],
                ],
                'faq' => [
                    ['question' => 'Can Event Schedule generate event graphics like Canva?', 'answer' => 'Yes. Event Schedule auto-generates shareable event graphics from your event details and also offers AI-powered flyer generation. Graphics update automatically when event details change, so you never have outdated flyers.'],
                    ['question' => 'Is Event Schedule free for event graphics?', 'answer' => 'Event graphics generation is included in the Pro plan at $5/month, which also includes ticketing, QR check-in, and more. The free plan includes unlimited events, public event pages, and Google Calendar sync.'],
                    ['question' => 'How do Event Schedule graphics compare to Canva designs?', 'answer' => 'Canva offers more design flexibility for general-purpose graphics. Event Schedule graphics are purpose-built for events, automatically pulling in dates, times, locations, and descriptions. They are designed for quick social media sharing and are always in sync with your event data.'],
                    ['question' => 'Is Event Schedule open source?', 'answer' => 'Yes. Event Schedule is fully open source and can be selfhosted on your own server. You get complete control over your platform, data, and branding without vendor lock-in.'],
                    ['question' => 'Can I still use Canva alongside Event Schedule?', 'answer' => 'Of course. Many organizers use Event Schedule for automatic event graphics and Canva for custom brand materials. Event Schedule handles the event-specific graphics so you spend less time in Canva for routine event promotion.'],
                ],
                'cross_links' => [
                    ['name' => 'QR Code Generators', 'route' => 'marketing.replace_qr_code_generators'],
                    ['name' => 'Linktree', 'route' => 'marketing.replace_linktree'],
                    ['name' => 'Squarespace', 'route' => 'marketing.replace_squarespace'],
                ],
                'related_features' => [
                    ['name' => 'Event Graphics', 'route' => 'marketing.event_graphics', 'description' => 'Auto-generate shareable graphics from event details.'],
                    ['name' => 'AI Features', 'route' => 'marketing.ai', 'description' => 'AI-powered flyer generation and event import.'],
                    ['name' => 'Newsletters', 'route' => 'marketing.newsletters', 'description' => 'Promote events with built-in email newsletters.'],
                ],
            ],
            'linktree' => [
                'name' => 'Linktree',
                'tagline' => 'A shareable page that actually sells tickets.',
                'description' => 'Replace Linktree for event promotion with Event Schedule. Get a branded schedule page with events, ticket sales, and subscriber signups.',
                'keywords' => 'Linktree alternative for events, Linktree replacement, event link in bio, event landing page',
                'about' => 'Linktree is a link-in-bio tool that event organizers use to share multiple event links from a single URL. While it solves the "one link" problem on social media, it is just a list of links with no event context, ticketing, or scheduling functionality.',
                'tool_strengths' => [
                    'Simple setup for sharing multiple links from one URL',
                    'Widely recognized link-in-bio format',
                    'Basic analytics on link clicks',
                ],
                'pain_points' => [
                    'Just a list of links with no event context or details',
                    'No built-in ticketing, RSVPs, or payments',
                    'Must update links manually as events change',
                    'No calendar view or schedule functionality',
                ],
                'es_solutions' => [
                    ['title' => 'Public Schedule Page', 'description' => 'Share one URL that shows all your upcoming events with full details, dates, and ticket links. Better than a link list.', 'icon' => 'globe', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Inline Ticket Sales', 'description' => 'Visitors buy tickets right from your schedule page. No redirects to separate ticketing platforms.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Auto-updating Events', 'description' => 'Your schedule page updates automatically when you add or change events. No manual link management.', 'icon' => 'calendar', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'Subscriber Signups', 'description' => 'Visitors can subscribe to your schedule for updates. Build your audience directly, no separate email tool needed.', 'icon' => 'mail', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'Embeddable Widget', 'description' => 'Embed your event calendar on any website. Show your schedule wherever your audience is.', 'icon' => 'code', 'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30', 'border' => 'border-rose-200 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                    ['title' => 'Zero Platform Fees', 'description' => 'Sell tickets without platform fees. Only pay standard Stripe processing. No hidden costs or premium upsells.', 'icon' => 'dollar', 'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30', 'border' => 'border-violet-200 dark:border-violet-500/20', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                ],
                'why_switch' => [
                    'intro' => 'Linktree gives you a list of links. Event Schedule gives you a full event hub with ticket sales, subscriber signups, and auto-updating event details.',
                    'points' => [
                        'A schedule page with event details, not just a list of URLs',
                        'Inline ticket purchasing without redirecting to another platform',
                        'Events update automatically so your link in bio is always current',
                        'Subscriber signups and newsletters to build your audience',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your schedule', 'description' => 'Sign up free and create your branded schedule page with your name, logo, and colors.'],
                    ['title' => 'Add your events', 'description' => 'Add events with full details, images, and ticket options. AI can import them automatically.'],
                    ['title' => 'Use as your link in bio', 'description' => 'Share your schedule URL as your link in bio. It updates automatically as you add events.'],
                ],
                'faq' => [
                    ['question' => 'How is Event Schedule better than Linktree for events?', 'answer' => 'Linktree is a list of links. Event Schedule gives you a full schedule page with event details, dates, ticket purchasing, and subscriber signups. Your audience sees a rich event experience, not just a link directory.'],
                    ['question' => 'Does Event Schedule have a free plan?', 'answer' => 'Yes. The free plan includes unlimited events, a public schedule page, Google Calendar sync, newsletters, and embeddable widgets. Ticketing with zero platform fees is available on the Pro plan for $5/month.'],
                    ['question' => 'Can I use Event Schedule as my link in bio?', 'answer' => 'Yes. Your schedule page URL works perfectly as a link in bio. It shows all your upcoming events with full details and ticket links, updating automatically as you add or change events. No manual link management needed.'],
                    ['question' => 'Is Event Schedule open source?', 'answer' => 'Yes. Event Schedule is fully open source and can be selfhosted on your own server. You get a branded event page with your own domain, complete data ownership, and no vendor lock-in.'],
                    ['question' => 'Can visitors subscribe to my events from the schedule page?', 'answer' => 'Yes. Your schedule page includes a subscribe button so visitors can sign up for email updates. You can then send newsletters about upcoming events directly from Event Schedule.'],
                ],
                'cross_links' => [
                    ['name' => 'Squarespace', 'route' => 'marketing.replace_squarespace'],
                    ['name' => 'Canva', 'route' => 'marketing.replace_canva'],
                    ['name' => 'Mailchimp', 'route' => 'marketing.replace_mailchimp'],
                ],
                'related_features' => [
                    ['name' => 'Embed Calendar', 'route' => 'marketing.embed_calendar', 'description' => 'Embed your event calendar on any website.'],
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Sell tickets with QR codes and zero platform fees.'],
                    ['name' => 'Newsletters', 'route' => 'marketing.newsletters', 'description' => 'Send event updates to your subscribers.'],
                ],
            ],
            'google-sheets' => [
                'name' => 'Google Sheets',
                'tagline' => 'Stop tracking event data in spreadsheets.',
                'description' => 'Replace Google Sheets for event tracking with Event Schedule. Get attendee management, ticket sales tracking, and event organization.',
                'keywords' => 'Google Sheets alternative for events, Google Sheets replacement, event tracking spreadsheet, Excel alternative for events',
                'about' => 'Google Sheets is a go-to tool for event organizers who need to track attendees, manage guest lists, and organize event details. But spreadsheets were not built for event management - they require manual data entry, have no attendee-facing features, and become unwieldy as events grow.',
                'tool_strengths' => [
                    'Flexible data structure for any type of tracking',
                    'Real-time collaboration with team members',
                    'Free and available to anyone with a Google account',
                ],
                'pain_points' => [
                    'Manual data entry for every attendee and ticket sale',
                    'No public-facing event pages or ticket purchasing',
                    'Formulas and formatting break as sheets grow',
                    'No automated confirmations, reminders, or check-in tools',
                ],
                'es_solutions' => [
                    ['title' => 'Automatic Tracking', 'description' => 'Ticket sales, attendee lists, and check-ins are tracked automatically. No manual data entry or formula maintenance.', 'icon' => 'chart', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'CSV Export', 'description' => 'Export your sales data to CSV anytime for custom analysis. Get the spreadsheet format when you need it.', 'icon' => 'code', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Live Dashboard', 'description' => 'Real-time check-in dashboard shows who has arrived, ticket counts, and revenue. Better than refreshing a spreadsheet.', 'icon' => 'dollar', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'Team Collaboration', 'description' => 'Invite team members to manage events together. Role-based access is built in, not a shared spreadsheet link.', 'icon' => 'globe', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'REST API', 'description' => 'Need custom integrations? Use the REST API to connect Event Schedule with your other tools programmatically.', 'icon' => 'code', 'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30', 'border' => 'border-rose-200 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                    ['title' => 'Newsletters', 'description' => 'Send event updates to your audience with built-in newsletters and A/B testing. No separate email tool needed.', 'icon' => 'mail', 'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30', 'border' => 'border-violet-200 dark:border-violet-500/20', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                ],
                'why_switch' => [
                    'intro' => 'Spreadsheets require manual data entry for every sale and attendee. Event Schedule tracks everything automatically with a live dashboard.',
                    'points' => [
                        'Automatic tracking of ticket sales, attendees, and check-ins',
                        'CSV export when you need spreadsheet-format data for analysis',
                        'REST API for custom integrations with your other tools',
                        'Live check-in dashboard instead of a static spreadsheet',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your events', 'description' => 'Set up events with ticket types and pricing. AI can import event details automatically.'],
                    ['title' => 'Sell tickets', 'description' => 'Attendees buy tickets online. Sales, attendee lists, and revenue are tracked automatically.'],
                    ['title' => 'Check in and export', 'description' => 'Use QR check-in on event day and export data to CSV anytime for custom reporting.'],
                ],
                'faq' => [
                    ['question' => 'Can Event Schedule replace Google Sheets for attendee tracking?', 'answer' => 'Yes. Event Schedule automatically tracks ticket sales, attendee lists, and check-ins. You get a live dashboard instead of a static spreadsheet, and you can export to CSV anytime for custom analysis.'],
                    ['question' => 'Is there a free plan for Event Schedule?', 'answer' => 'Yes. The free plan includes unlimited events, Google Calendar sync, newsletters, and fan engagement features. Ticketing with automatic tracking and zero platform fees is available on the Pro plan for $5/month.'],
                    ['question' => 'Can I still export data to a spreadsheet?', 'answer' => 'Yes. Pro plan includes CSV export of your sales data, so you can use spreadsheets for custom analysis when needed. The difference is that data collection and tracking happens automatically instead of through manual entry.'],
                    ['question' => 'Is Event Schedule open source?', 'answer' => 'Yes. Event Schedule is fully open source and can be selfhosted on your own server. You have complete control over your data, and the REST API lets you build custom integrations.'],
                    ['question' => 'Does Event Schedule have a REST API for custom integrations?', 'answer' => 'Yes. The Pro plan includes a REST API that lets you pull event data, attendee lists, and sales information programmatically. Connect Event Schedule with your CRM, accounting software, or any other tool.'],
                ],
                'cross_links' => [
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms'],
                    ['name' => 'SurveyMonkey', 'route' => 'marketing.replace_surveymonkey'],
                    ['name' => 'Doodle', 'route' => 'marketing.replace_doodle'],
                ],
                'related_features' => [
                    ['name' => 'Analytics', 'route' => 'marketing.analytics', 'description' => 'Track event performance and attendee data.'],
                    ['name' => 'Integrations', 'route' => 'marketing.integrations', 'description' => 'REST API and webhooks for custom workflows.'],
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Sell tickets with automatic sales tracking.'],
                ],
            ],
            'calendly' => [
                'name' => 'Calendly',
                'tagline' => 'Event scheduling built for audiences, not one-on-ones.',
                'description' => 'Replace Calendly for event scheduling with Event Schedule. Manage public events, sell tickets, and share a schedule page - not just appointment slots.',
                'keywords' => 'Calendly alternative for events, Calendly replacement, event scheduling tool, public event scheduling',
                'about' => 'Calendly is a scheduling tool designed for booking one-on-one meetings and appointments. Some event organizers use it to schedule events, but it lacks public event pages, ticketing, and the ability to share a calendar of events with an audience.',
                'tool_strengths' => [
                    'Excellent for one-on-one and small group scheduling',
                    'Calendar integration for availability management',
                    'Automated reminders and follow-ups for meetings',
                ],
                'pain_points' => [
                    'Designed for meetings, not public events or performances',
                    'No ticketing, payment processing, or attendee limits',
                    'No public event calendar or shareable schedule page',
                    'No event graphics, flyers, or promotional tools',
                ],
                'es_solutions' => [
                    ['title' => 'Public Event Pages', 'description' => 'Every event gets a shareable page with full details, images, and ticket purchasing. Built for audiences, not meeting invitees.', 'icon' => 'globe', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Ticketing & Payments', 'description' => 'Sell tickets with multiple ticket types, promo codes, and zero platform fees. Calendly cannot process event payments.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Google Calendar Sync', 'description' => 'Two-way Google Calendar sync keeps your events updated automatically. Similar calendar integration, designed for events.', 'icon' => 'calendar', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'Event Graphics', 'description' => 'Generate shareable event graphics and AI flyers automatically. Promote your events visually on social media.', 'icon' => 'image', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'Audience Building', 'description' => 'Subscribers, newsletters, and fan engagement tools help you build an audience. Calendly focuses on individual scheduling.', 'icon' => 'mail', 'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30', 'border' => 'border-rose-200 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                    ['title' => 'Open Source & Selfhosted', 'description' => 'Full source code access and selfhosting option. Own your event platform completely, no vendor lock-in.', 'icon' => 'code', 'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30', 'border' => 'border-violet-200 dark:border-violet-500/20', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                ],
                'why_switch' => [
                    'intro' => 'Calendly schedules meetings. Event Schedule manages public events with ticket sales, audience building, and promotional tools.',
                    'points' => [
                        'Public event pages with full details, images, and ticket purchasing',
                        'Ticketing with multiple types, promo codes, and zero platform fees',
                        'Two-way Google Calendar sync included free',
                        'Newsletters and subscriber management to grow your audience',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your schedule', 'description' => 'Sign up free and create your public schedule. Connect Google Calendar for two-way sync.'],
                    ['title' => 'Publish your events', 'description' => 'Add events with details, images, and ticket options. Share your schedule page with your audience.'],
                    ['title' => 'Sell tickets and grow', 'description' => 'Attendees buy tickets and subscribe for updates. Build your audience with every event.'],
                ],
                'faq' => [
                    ['question' => 'How is Event Schedule different from Calendly for events?', 'answer' => 'Calendly is built for one-on-one appointment scheduling. Event Schedule is built for public events with audiences. You get public event pages, ticket sales, a shareable schedule, newsletters, and event graphics that Calendly does not offer.'],
                    ['question' => 'Does Event Schedule have a free plan?', 'answer' => 'Yes. The free plan includes unlimited events, public event pages, Google Calendar sync, newsletters, and fan engagement features. Ticketing with zero platform fees is available on the Pro plan for $5/month.'],
                    ['question' => 'Does Event Schedule sync with Google Calendar like Calendly?', 'answer' => 'Yes. Event Schedule includes two-way Google Calendar sync on the free plan. Events you create sync to your Google Calendar, and Google Calendar events can sync back. CalDAV sync is also supported.'],
                    ['question' => 'Is Event Schedule open source?', 'answer' => 'Yes. Event Schedule is fully open source and can be selfhosted on your own server. Unlike Calendly, you have complete control over your platform and data with no vendor lock-in.'],
                    ['question' => 'Can I use Event Schedule for recurring events?', 'answer' => 'Yes. Event Schedule supports recurring events with flexible scheduling patterns. Each occurrence can have its own ticket types and attendee limits, which is something Calendly cannot handle for public events.'],
                ],
                'cross_links' => [
                    ['name' => 'Doodle', 'route' => 'marketing.replace_doodle'],
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms'],
                    ['name' => 'Linktree', 'route' => 'marketing.replace_linktree'],
                ],
                'related_features' => [
                    ['name' => 'Calendar Sync', 'route' => 'marketing.calendar_sync', 'description' => 'Two-way Google Calendar and CalDAV sync.'],
                    ['name' => 'Recurring Events', 'route' => 'marketing.recurring_events', 'description' => 'Flexible recurring event scheduling.'],
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Sell tickets with QR codes and zero platform fees.'],
                ],
            ],
            'surveymonkey' => [
                'name' => 'SurveyMonkey',
                'tagline' => 'Event registration without the survey overhead.',
                'description' => 'Replace SurveyMonkey for event registration with Event Schedule. Purpose-built event signup with ticketing, payments, and attendee management.',
                'keywords' => 'SurveyMonkey alternative for events, SurveyMonkey replacement, event registration, event signup',
                'about' => 'SurveyMonkey is a survey platform that some event organizers repurpose for event registration and feedback collection. While it offers form building capabilities, it was designed for surveys and research - not for managing events, selling tickets, or handling attendee logistics.',
                'tool_strengths' => [
                    'Advanced survey logic and branching questions',
                    'Built-in analytics and reporting for responses',
                    'Established platform with wide recognition',
                ],
                'pain_points' => [
                    'No event pages, calendars, or scheduling features',
                    'No payment processing or ticket management',
                    'Responses are survey data, not attendee records',
                    'Expensive for basic event registration needs',
                ],
                'es_solutions' => [
                    ['title' => 'Purpose-built Registration', 'description' => 'Event registration that understands events - with ticket types, attendee limits, waitlists, and automatic confirmations.', 'icon' => 'ticket', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Zero Platform Fees', 'description' => 'Sell tickets without platform fees. SurveyMonkey charges for premium features you need for basic event registration.', 'icon' => 'dollar', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Public Event Pages', 'description' => 'Shareable event pages with all details, images, and ticket options. Not a survey link that looks like a questionnaire.', 'icon' => 'globe', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'QR Code Check-in', 'description' => 'QR codes on every ticket for event day check-in. Live dashboard shows attendance in real time.', 'icon' => 'qr', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'Open Source', 'description' => 'Fully open source with a selfhosting option. Own your data and your platform, no vendor lock-in.', 'icon' => 'code', 'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30', 'border' => 'border-rose-200 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                    ['title' => 'Newsletters', 'description' => 'Send event updates to subscribers with built-in newsletters and A/B testing. No separate email marketing tool needed.', 'icon' => 'mail', 'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30', 'border' => 'border-violet-200 dark:border-violet-500/20', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                ],
                'why_switch' => [
                    'intro' => 'SurveyMonkey was built for surveys, not events. Event Schedule gives you purpose-built registration with ticketing, payments, and check-in tools.',
                    'points' => [
                        'Event registration with ticket types, limits, and waitlists',
                        'Zero platform fees at $5/month vs SurveyMonkey at $25+/month',
                        'Attendee records with check-in tools, not survey responses',
                        'Public event pages that look professional, not like questionnaires',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your events', 'description' => 'Set up events with ticket types, pricing, and custom fields for attendee information.'],
                    ['title' => 'Share your event page', 'description' => 'Share a professional event page instead of a survey link. Attendees register and pay in one step.'],
                    ['title' => 'Manage attendees', 'description' => 'Track registrations, check attendees in with QR codes, and send follow-up newsletters.'],
                ],
                'faq' => [
                    ['question' => 'Can Event Schedule handle event registration like SurveyMonkey?', 'answer' => 'Yes, and better. Event Schedule provides purpose-built event registration with ticket types, attendee limits, waitlists, and automatic confirmations. Unlike SurveyMonkey, registrations are connected to ticketing, payments, and check-in tools.'],
                    ['question' => 'How much does Event Schedule cost compared to SurveyMonkey?', 'answer' => 'Event Schedule is free for unlimited events, newsletters, and Google Calendar sync. The Pro plan at $5/month adds ticketing with zero platform fees. SurveyMonkey charges $25 or more per month for features like payment collection and custom branding.'],
                    ['question' => 'Does Event Schedule support post-event surveys?', 'answer' => 'Event Schedule includes post-event feedback collection on the Pro plan. While it does not have SurveyMonkey-style branching logic, it covers the feedback needs of most event organizers with integrated attendee data.'],
                    ['question' => 'Is Event Schedule open source?', 'answer' => 'Yes. Event Schedule is fully open source and can be selfhosted on your own server. You own your attendee data completely with no vendor lock-in or surprise price increases.'],
                    ['question' => 'Can I collect custom information from attendees?', 'answer' => 'Yes. Pro plan includes custom fields on ticket forms so you can collect any information you need during registration. The data is connected to attendee records and available for export.'],
                ],
                'cross_links' => [
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms'],
                    ['name' => 'Doodle', 'route' => 'marketing.replace_doodle'],
                    ['name' => 'Google Sheets', 'route' => 'marketing.replace_google_sheets'],
                ],
                'related_features' => [
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Sell tickets with QR codes and zero platform fees.'],
                    ['name' => 'Custom Fields', 'route' => 'marketing.custom_fields', 'description' => 'Collect attendee information with custom form fields.'],
                    ['name' => 'Polls', 'route' => 'marketing.polls', 'description' => 'Collect attendee feedback with built-in event polls.'],
                ],
            ],
            'doodle' => [
                'name' => 'Doodle',
                'tagline' => 'Go from polling dates to publishing events.',
                'description' => 'Replace Doodle for event scheduling with Event Schedule. Move beyond date polling to a full event platform with public pages, ticketing, and a shareable calendar.',
                'keywords' => 'Doodle alternative for events, Doodle replacement, event scheduling poll, event date scheduling',
                'about' => 'Doodle is a scheduling poll tool that helps groups find a common time to meet. Event organizers sometimes use it to pick event dates, but Doodle stops at the poll - it does not help you create, promote, or manage the actual event once a date is chosen.',
                'tool_strengths' => [
                    'Simple interface for group date polling',
                    'Quick setup with no account required for participants',
                    'Effective for finding mutually available times',
                ],
                'pain_points' => [
                    'Only helps pick dates, not manage or promote events',
                    'No event pages, ticketing, or attendee management',
                    'No calendar view or public schedule sharing',
                    'Requires switching to another tool after date selection',
                ],
                'es_solutions' => [
                    ['title' => 'Full Event Management', 'description' => 'Create events with dates, details, images, and ticket options. No need to switch tools after picking a date.', 'icon' => 'calendar', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Public Schedule Page', 'description' => 'Share a calendar of all your events with one URL. Attendees see upcoming events, not just a date poll.', 'icon' => 'globe', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Ticketing & RSVPs', 'description' => 'Sell tickets or collect free RSVPs with automatic confirmations. Doodle has no registration functionality.', 'icon' => 'ticket', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'Google Calendar Sync', 'description' => 'Two-way sync with Google Calendar. Your events appear on your calendar and your calendar events can sync back.', 'icon' => 'calendar', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'AI Event Import', 'description' => 'Paste event details in any format and AI parses them into structured events. Faster than manual event creation.', 'icon' => 'ai', 'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30', 'border' => 'border-rose-200 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                    ['title' => 'Event Graphics', 'description' => 'Generate shareable event graphics and AI flyers automatically. Promote events visually without separate design tools.', 'icon' => 'image', 'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30', 'border' => 'border-violet-200 dark:border-violet-500/20', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                ],
                'why_switch' => [
                    'intro' => 'Doodle stops at picking a date. Event Schedule takes you from event creation through promotion, ticketing, and check-in day.',
                    'points' => [
                        'Full event management from creation to check-in, not just date polling',
                        'Public schedule page with all your events shareable from one URL',
                        'Ticketing and RSVPs with automatic confirmations',
                        'Two-way Google Calendar sync keeps everything updated',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your events', 'description' => 'Add events with dates, details, and ticket options. Use AI to import event details automatically.'],
                    ['title' => 'Share your schedule', 'description' => 'Share one URL that shows all your upcoming events. Attendees see a full calendar, not a date poll.'],
                    ['title' => 'Sell and manage', 'description' => 'Sell tickets or collect RSVPs. Check attendees in with QR codes on event day.'],
                ],
                'faq' => [
                    ['question' => 'How is Event Schedule better than Doodle for events?', 'answer' => 'Doodle stops at picking a date. Event Schedule takes you from event creation through promotion and ticketing to check-in day. You get public event pages, ticket sales, Google Calendar sync, and newsletters in one platform.'],
                    ['question' => 'Is Event Schedule free to use?', 'answer' => 'Yes. The free plan includes unlimited events, public event pages, Google Calendar sync, newsletters, and fan engagement features. Ticketing with zero platform fees is available on the Pro plan for $5/month.'],
                    ['question' => 'Does Event Schedule have scheduling or polling features like Doodle?', 'answer' => 'Event Schedule includes event polls on the Pro plan for collecting attendee preferences. For date selection, you create events with set dates and share them through your public schedule page. It is designed for publishing events, not polling for availability.'],
                    ['question' => 'Is Event Schedule open source?', 'answer' => 'Yes. Event Schedule is fully open source and can be selfhosted on your own server. You get a complete event platform with no vendor lock-in or dependency on a third-party service.'],
                    ['question' => 'Can I collect RSVPs for free events?', 'answer' => 'Yes. Event Schedule supports free RSVPs with automatic confirmation emails. Attendees register through your event page and you get a real attendee list with check-in tools.'],
                ],
                'cross_links' => [
                    ['name' => 'Calendly', 'route' => 'marketing.replace_calendly'],
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms'],
                    ['name' => 'Google Sheets', 'route' => 'marketing.replace_google_sheets'],
                ],
                'related_features' => [
                    ['name' => 'Calendar Sync', 'route' => 'marketing.calendar_sync', 'description' => 'Two-way Google Calendar and CalDAV sync.'],
                    ['name' => 'Polls', 'route' => 'marketing.polls', 'description' => 'Collect attendee preferences with event polls.'],
                    ['name' => 'AI Import', 'route' => 'marketing.ai', 'description' => 'Paste event details and let AI create your events.'],
                ],
            ],
            'qr-code-generators' => [
                'name' => 'QR Code Generators',
                'short_name' => 'QR Code',
                'tagline' => 'QR codes that are built into every ticket.',
                'description' => 'Replace standalone QR code generators for events with Event Schedule. Every ticket includes a scannable QR code for check-in, with a live attendance dashboard.',
                'keywords' => 'QR code alternative for events, QR code generator replacement, event QR code, QR code check-in',
                'about' => 'QR code generators are standalone tools that event organizers use to create scannable codes for event check-in, links to event pages, or ticket verification. Using a separate QR tool means manually creating codes, linking them to attendee data, and building your own check-in process.',
                'tool_strengths' => [
                    'Simple generation of QR codes for any URL or data',
                    'Customizable QR code designs and colors',
                    'Can be used for many purposes beyond events',
                ],
                'pain_points' => [
                    'No connection to attendee data or ticket sales',
                    'Manual process to create and assign codes to attendees',
                    'No built-in scanning or check-in functionality',
                    'Requires building your own verification system',
                ],
                'es_solutions' => [
                    ['title' => 'Automatic QR Tickets', 'description' => 'Every ticket sold includes a unique QR code automatically. No manual QR generation or assignment needed.', 'icon' => 'qr', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Scan-to-Check-In', 'description' => 'Scan QR codes at the door to check attendees in. The system validates tickets and prevents duplicates instantly.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Live Dashboard', 'description' => 'Real-time check-in dashboard shows attendance numbers, ticket type breakdowns, and who has arrived.', 'icon' => 'chart', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'Zero Platform Fees', 'description' => 'All ticketing features including QR codes are included with zero platform fees. Only standard Stripe processing.', 'icon' => 'dollar', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'Complete Event Platform', 'description' => 'QR check-in is just one part. Get event pages, calendar sync, newsletters, and AI tools in one platform.', 'icon' => 'globe', 'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30', 'border' => 'border-rose-200 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                    ['title' => 'Open Source & Selfhosted', 'description' => 'Full source code access and selfhosting option. Own your event platform and data completely.', 'icon' => 'code', 'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30', 'border' => 'border-violet-200 dark:border-violet-500/20', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                ],
                'why_switch' => [
                    'intro' => 'Standalone QR generators create codes, but Event Schedule builds QR check-in into every ticket with automatic validation and a live dashboard.',
                    'points' => [
                        'Every ticket includes a unique QR code automatically',
                        'Built-in scanning that validates tickets and prevents duplicates',
                        'Live check-in dashboard with real-time attendance tracking',
                        'Complete event platform with ticketing, not just QR codes',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create tickets', 'description' => 'Set up ticket types for your event. Every ticket automatically includes a unique QR code.'],
                    ['title' => 'Sell online', 'description' => 'Attendees buy tickets and receive their QR codes via email. No manual code generation needed.'],
                    ['title' => 'Scan at the door', 'description' => 'Use any smartphone to scan QR codes. The live dashboard tracks check-ins in real time.'],
                ],
                'faq' => [
                    ['question' => 'Does Event Schedule replace standalone QR code generators?', 'answer' => 'Yes. Every ticket sold through Event Schedule includes a unique QR code automatically. No need for a separate QR generation tool. QR codes are linked to attendee data for instant validation at the door.'],
                    ['question' => 'How much does QR code ticketing cost?', 'answer' => 'QR code tickets are included in the Pro plan at $5/month with zero platform fees. You only pay standard Stripe processing fees (typically 2.9% + $0.30 per transaction). The free plan includes unlimited events without ticketing.'],
                    ['question' => 'How does the QR check-in system work?', 'answer' => 'Each ticket includes a unique QR code emailed to the buyer. At the event, use any smartphone to scan QR codes and check attendees in. The system validates tickets, prevents duplicate scans, and tracks attendance on a live dashboard in real time.'],
                    ['question' => 'Is Event Schedule open source?', 'answer' => 'Yes. Event Schedule is fully open source and can be selfhosted on your own server. You get complete control over your ticketing and check-in system with no vendor lock-in.'],
                    ['question' => 'Do I need special hardware to scan QR codes?', 'answer' => 'No. Any smartphone with a web browser can scan tickets. Open the check-in page on your phone, point it at the QR code, and the attendee is checked in instantly. No app download required.'],
                ],
                'cross_links' => [
                    ['name' => 'Canva', 'route' => 'marketing.replace_canva'],
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms'],
                    ['name' => 'Google Sheets', 'route' => 'marketing.replace_google_sheets'],
                ],
                'related_features' => [
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Sell tickets with QR codes and zero platform fees.'],
                    ['name' => 'Analytics', 'route' => 'marketing.analytics', 'description' => 'Track check-ins and attendance in real time.'],
                    ['name' => 'Event Graphics', 'route' => 'marketing.event_graphics', 'description' => 'Auto-generate shareable graphics for promotion.'],
                ],
            ],
            'squarespace' => [
                'name' => 'Squarespace',
                'tagline' => 'An event platform, not a website builder.',
                'description' => 'Replace Squarespace for event websites with Event Schedule. Get purpose-built event pages, integrated ticketing, and a shareable schedule without building a full website.',
                'keywords' => 'Squarespace alternative for events, Squarespace replacement, event website builder, event landing page',
                'about' => 'Squarespace is a general website builder that some event organizers use to create event pages and sell tickets through third-party integrations. While it produces beautiful websites, building event functionality on top of a website builder means extra complexity, plugins, and ongoing maintenance for features that should be built in.',
                'tool_strengths' => [
                    'Beautiful, professionally designed website templates',
                    'Flexible page builder for custom layouts',
                    'Built-in SEO tools and analytics',
                ],
                'pain_points' => [
                    'Requires building event pages from scratch or with plugins',
                    'Ticketing needs third-party integrations with extra fees',
                    'No calendar sync, newsletter, or attendee management built in',
                    'Monthly cost for a website when you just need event pages',
                ],
                'es_solutions' => [
                    ['title' => 'Ready-made Event Pages', 'description' => 'Professional event pages are generated automatically from your event details. No design or page building required.', 'icon' => 'globe', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Integrated Ticketing', 'description' => 'Ticket sales are built in with zero platform fees. No third-party ticketing plugin that takes a cut of each sale.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Embeddable Widget', 'description' => 'Already have a website? Embed your Event Schedule calendar on any site. Works with Squarespace, WordPress, or any platform.', 'icon' => 'code', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'Open Source', 'description' => 'Fully open source and selfhostable. No vendor lock-in. Squarespace owns your site if you cancel.', 'icon' => 'code', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'All-in-one Platform', 'description' => 'Ticketing, newsletters, calendar sync, AI tools, and event graphics in one platform. No plugins or integrations to manage.', 'icon' => 'ai', 'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30', 'border' => 'border-rose-200 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                    ['title' => 'AI Event Import', 'description' => 'Paste event details in any format and AI extracts dates, times, and descriptions automatically. No manual page building.', 'icon' => 'ai', 'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30', 'border' => 'border-violet-200 dark:border-violet-500/20', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                ],
                'why_switch' => [
                    'intro' => 'Squarespace builds websites. Event Schedule builds event experiences with ticketing, newsletters, and audience tools included.',
                    'points' => [
                        'Professional event pages generated automatically, no page building needed',
                        'Integrated ticketing with zero platform fees, no third-party plugins',
                        'Embeddable widget if you already have a Squarespace site',
                        'Open source with selfhosting option, no vendor lock-in',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your schedule', 'description' => 'Sign up free and create your branded schedule page. No website building required.'],
                    ['title' => 'Add events and tickets', 'description' => 'Create events with all details and ticket options. AI can import events automatically.'],
                    ['title' => 'Share or embed', 'description' => 'Share your schedule URL directly or embed the calendar widget on your existing website.'],
                ],
                'faq' => [
                    ['question' => 'Can Event Schedule replace my Squarespace events page?', 'answer' => 'Yes. Event Schedule provides ready-made event pages with all event details, ticket purchasing, and a shareable schedule. No website building, plugins, or custom page design required. You can also embed the calendar widget on your existing Squarespace site.'],
                    ['question' => 'How does Event Schedule pricing compare to Squarespace?', 'answer' => 'Event Schedule is free for unlimited events and a public schedule page. The Pro plan at $5/month adds ticketing with zero platform fees. Squarespace costs $16 or more per month for a basic website, plus additional fees for third-party ticketing integrations.'],
                    ['question' => 'Do I still need a website if I use Event Schedule?', 'answer' => 'Not necessarily. Your Event Schedule page works as a standalone event website with a custom URL, all your events, ticket sales, and subscriber signups. If you already have a website, you can embed the Event Schedule calendar widget on it.'],
                    ['question' => 'Is Event Schedule open source?', 'answer' => 'Yes. Event Schedule is fully open source and can be selfhosted on your own server. Unlike Squarespace, you own your platform and data completely. If you stop paying, your events and data are still yours.'],
                    ['question' => 'Can I embed Event Schedule on my Squarespace site?', 'answer' => 'Yes. The embeddable calendar widget works on any website including Squarespace. Add a code block to your Squarespace page and paste the embed snippet to show your event calendar.'],
                ],
                'cross_links' => [
                    ['name' => 'Linktree', 'route' => 'marketing.replace_linktree'],
                    ['name' => 'Canva', 'route' => 'marketing.replace_canva'],
                    ['name' => 'Mailchimp', 'route' => 'marketing.replace_mailchimp'],
                ],
                'related_features' => [
                    ['name' => 'Embed Calendar', 'route' => 'marketing.embed_calendar', 'description' => 'Embed your event calendar on any website.'],
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Sell tickets with QR codes and zero platform fees.'],
                    ['name' => 'White Label', 'route' => 'marketing.white_label', 'description' => 'Remove branding for a fully custom experience.'],
                ],
            ],
            'notion' => [
                'name' => 'Notion',
                'tagline' => 'Event management that faces your audience, not just your team.',
                'description' => 'Replace Notion for event planning with Event Schedule. Get public event pages, built-in ticketing, and Google Calendar sync instead of internal workspace databases.',
                'keywords' => 'Notion alternative for events, Notion replacement, Notion event planning, event management workspace',
                'about' => 'Notion is a workspace and productivity tool that some event organizers use to plan events with databases, calendars, and shared pages. While Notion is excellent for internal project management, it has no public-facing event pages, no ticketing or RSVP functionality, and a steep learning curve for setting up event workflows.',
                'tool_strengths' => [
                    'Flexible workspace with databases, docs, and wikis',
                    'Strong team collaboration and shared workspaces',
                    'Customizable templates for project management',
                ],
                'pain_points' => [
                    'No public event pages or attendee-facing features',
                    'No ticketing, RSVP, or payment processing',
                    'Internal calendar not connected to Google Calendar or public schedule',
                    'Steep learning curve to build event management workflows from scratch',
                ],
                'es_solutions' => [
                    ['title' => 'Public Event Pages', 'description' => 'Every event gets a shareable page with details, images, and ticket purchasing. Your audience sees polished event pages, not internal docs.', 'icon' => 'globe', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Built-in Ticketing', 'description' => 'Sell tickets with Stripe payments, automatic confirmations, and zero platform fees. No third-party integration needed.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Google Calendar Sync', 'description' => 'Two-way Google Calendar sync keeps events updated automatically. No manual copying between your workspace and calendar.', 'icon' => 'calendar', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'AI Event Import', 'description' => 'Paste event details in any format and AI extracts dates, times, and descriptions. Faster than building Notion database entries.', 'icon' => 'ai', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'Team Collaboration', 'description' => 'Invite team members to manage events together with role-based access. Built for event teams, not generic workspace collaboration.', 'icon' => 'globe', 'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30', 'border' => 'border-rose-200 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                    ['title' => 'Newsletters', 'description' => 'Send event updates to subscribers with built-in newsletters and A/B testing. No separate email tool needed.', 'icon' => 'mail', 'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30', 'border' => 'border-violet-200 dark:border-violet-500/20', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                ],
                'why_switch' => [
                    'intro' => 'Notion organizes your internal planning. Event Schedule faces your audience with public event pages, ticketing, and subscriber tools.',
                    'points' => [
                        'Public event pages your audience can see, not internal workspace docs',
                        'Built-in ticketing with payments and zero platform fees',
                        'Two-way Google Calendar sync instead of a disconnected internal calendar',
                        'AI event import that is faster than building Notion database entries',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your schedule', 'description' => 'Sign up free and set up your public schedule page. No databases or templates to configure.'],
                    ['title' => 'Import your events', 'description' => 'Paste event details and AI creates structured events automatically. Much faster than Notion database entries.'],
                    ['title' => 'Go public', 'description' => 'Share your schedule page with your audience. They can view events, buy tickets, and subscribe for updates.'],
                ],
                'faq' => [
                    ['question' => 'Can Event Schedule replace Notion for event planning?', 'answer' => 'Yes, for the event management side. Event Schedule handles everything Notion cannot: public event pages, ticket sales, attendee management, Google Calendar sync, and newsletters. You may still use Notion for internal project notes, but Event Schedule replaces it for audience-facing event work.'],
                    ['question' => 'Is Event Schedule free like Notion?', 'answer' => 'Yes. The free plan includes unlimited events, public event pages, Google Calendar sync, newsletters, and team collaboration. The Pro plan at $5/month adds ticketing with zero platform fees, event graphics, and more.'],
                    ['question' => 'Does Event Schedule support team collaboration like Notion?', 'answer' => 'Yes. You can invite team members to manage events together with role-based access on the Enterprise plan. Unlike Notion, collaboration is purpose-built for event management with tools like shared schedules, sub-schedules, and delegated event editing.'],
                    ['question' => 'Is Event Schedule open source?', 'answer' => 'Yes. Event Schedule is fully open source and can be selfhosted on your own server. You get complete control over your event data, unlike Notion where your data lives on their servers.'],
                    ['question' => 'Can I use Event Schedule alongside Notion?', 'answer' => 'Yes. Many organizers use Notion for internal project planning and Event Schedule for the audience-facing side: public event pages, ticket sales, newsletters, and attendee management. The REST API can also connect Event Schedule data with your Notion workflows.'],
                ],
                'cross_links' => [
                    ['name' => 'Trello', 'route' => 'marketing.replace_trello'],
                    ['name' => 'Google Sheets', 'route' => 'marketing.replace_google_sheets'],
                    ['name' => 'Calendly', 'route' => 'marketing.replace_calendly'],
                ],
                'related_features' => [
                    ['name' => 'Team Scheduling', 'route' => 'marketing.team_scheduling', 'description' => 'Collaborate with your team on event management.'],
                    ['name' => 'AI Import', 'route' => 'marketing.ai', 'description' => 'Paste event details and let AI create your events.'],
                    ['name' => 'Calendar Sync', 'route' => 'marketing.calendar_sync', 'description' => 'Two-way Google Calendar and CalDAV sync.'],
                ],
            ],
            'trello' => [
                'name' => 'Trello',
                'tagline' => 'From task boards to ticket sales.',
                'description' => 'Replace Trello for event management with Event Schedule. Get public event pages, built-in ticketing, payments, and a shareable schedule.',
                'keywords' => 'Trello alternative for events, Trello replacement, Trello event planning, event management board',
                'about' => 'Trello is a kanban-style project management tool that some event organizers use to track event planning tasks. While Trello is great for organizing workflows with boards and cards, it has no attendee-facing features, no registration or ticketing, and task boards do not map well to the event lifecycle of creating, promoting, and managing events.',
                'tool_strengths' => [
                    'Visual kanban boards for task management',
                    'Simple drag-and-drop interface for organizing workflows',
                    'Power-ups and integrations for extended functionality',
                ],
                'pain_points' => [
                    'No public event pages or attendee-facing features',
                    'No registration, ticketing, or payment processing',
                    'Task boards do not map to event lifecycle (create, promote, manage)',
                    'Purely internal project management with no audience interaction',
                ],
                'es_solutions' => [
                    ['title' => 'Public Event Pages', 'description' => 'Every event gets a shareable page with all details and ticket purchasing. Your audience sees event pages, not task cards.', 'icon' => 'globe', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Ticketing & Payments', 'description' => 'Sell tickets with Stripe payments, multiple ticket types, and zero platform fees. Trello has no way to handle registrations or payments.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Team Collaboration', 'description' => 'Invite team members with role-based access to manage events together. Purpose-built for event teams, not generic task management.', 'icon' => 'globe', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'Google Calendar Sync', 'description' => 'Two-way Google Calendar sync keeps events updated everywhere. No manual copying between boards and calendars.', 'icon' => 'calendar', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'AI Event Import', 'description' => 'Paste event details in any format and AI extracts structured event data automatically. Faster than creating Trello cards.', 'icon' => 'ai', 'gradient' => 'from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30', 'border' => 'border-rose-200 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_color' => 'text-rose-600 dark:text-rose-400'],
                    ['title' => 'Auto-generated Graphics', 'description' => 'Generate shareable event graphics and AI flyers automatically. Promote events visually without separate design tools.', 'icon' => 'image', 'gradient' => 'from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30', 'border' => 'border-violet-200 dark:border-violet-500/20', 'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20', 'icon_color' => 'text-violet-600 dark:text-violet-400'],
                ],
                'why_switch' => [
                    'intro' => 'Trello tracks internal tasks. Event Schedule handles the full event lifecycle your audience actually sees: event pages, ticket sales, and promotion.',
                    'points' => [
                        'Public event pages your audience can visit and buy tickets from',
                        'Built-in ticketing with Stripe payments and zero platform fees',
                        'Team collaboration purpose-built for event management workflows',
                        'AI event import and auto-generated graphics for fast promotion',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your events', 'description' => 'Add events with details, images, and ticket options. AI can import event details from any format.'],
                    ['title' => 'Invite your team', 'description' => 'Add team members with role-based access to manage events together.'],
                    ['title' => 'Publish and sell', 'description' => 'Share your public schedule page. Attendees see events, buy tickets, and subscribe for updates.'],
                ],
                'faq' => [
                    ['question' => 'Can Event Schedule replace Trello for event management?', 'answer' => 'Yes, for the event management side. Event Schedule handles what Trello cannot: public event pages, ticket sales, attendee management, and event promotion. You may still use Trello for internal task tracking, but Event Schedule replaces it for the full event lifecycle.'],
                    ['question' => 'Is Event Schedule free like Trello?', 'answer' => 'Yes. The free plan includes unlimited events, public event pages, Google Calendar sync, newsletters, and fan engagement features. The Pro plan at $5/month adds ticketing with zero platform fees, event graphics, and more.'],
                    ['question' => 'Does Event Schedule have task management like Trello?', 'answer' => 'Event Schedule is not a task management tool. It replaces Trello specifically for event management workflows: creating events, publishing them, selling tickets, and managing attendees. For internal planning tasks, you can continue using any project management tool you prefer.'],
                    ['question' => 'Is Event Schedule open source?', 'answer' => 'Yes. Event Schedule is fully open source and can be selfhosted on your own server. You get complete control over your event platform and data with no vendor lock-in.'],
                    ['question' => 'Can I use Event Schedule alongside Trello?', 'answer' => 'Yes. Use Trello for internal planning tasks and Event Schedule for the audience-facing event lifecycle: creating events, selling tickets, managing attendees, and sending newsletters. The two tools complement each other well.'],
                ],
                'cross_links' => [
                    ['name' => 'Notion', 'route' => 'marketing.replace_notion'],
                    ['name' => 'Doodle', 'route' => 'marketing.replace_doodle'],
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms'],
                ],
                'related_features' => [
                    ['name' => 'Team Scheduling', 'route' => 'marketing.team_scheduling', 'description' => 'Collaborate with your team on event management.'],
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Sell tickets with QR codes and zero platform fees.'],
                    ['name' => 'AI Import', 'route' => 'marketing.ai', 'description' => 'Paste event details and let AI create your events.'],
                ],
            ],
        ];

        return $tools[$tool];
    }

    /**
     * Get prev/next navigation data for a user guide doc page
     */
    protected function getDocNavigation(string $currentRoute): array
    {
        $pages = [
            ['route' => 'marketing.docs.getting_started', 'title' => 'Getting Started'],
            ['route' => 'marketing.docs.creating_schedules', 'title' => 'Creating Schedules'],
            ['route' => 'marketing.docs.managing_schedules', 'title' => 'Managing Schedules'],
            ['route' => 'marketing.docs.creating_events', 'title' => 'Creating Events'],
            ['route' => 'marketing.docs.ai_import', 'title' => 'AI Import'],
            ['route' => 'marketing.docs.scan_agenda', 'title' => 'Scan Agenda'],
            ['route' => 'marketing.docs.tickets', 'title' => 'Selling Tickets'],
            ['route' => 'marketing.docs.sharing', 'title' => 'Sharing Your Schedule'],
            ['route' => 'marketing.docs.event_graphics', 'title' => 'Event Graphics'],
            ['route' => 'marketing.docs.newsletters', 'title' => 'Newsletters'],
            ['route' => 'marketing.docs.boost', 'title' => 'Boost'],
            ['route' => 'marketing.docs.schedule_styling', 'title' => 'Schedule Styling'],
            ['route' => 'marketing.docs.analytics', 'title' => 'Analytics'],
            ['route' => 'marketing.docs.account_settings', 'title' => 'Account Settings'],
            ['route' => 'marketing.docs.referral_program', 'title' => 'Referral Program'],
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
            'Springfield' => [
                [
                    'subdomain' => 'simpsons',
                    'name' => 'Springfield Events',
                    'description' => 'Community events across Springfield venues',
                    'url' => 'https://simpsons.eventschedule.com/',
                    'profile_image_url' => 'images/demo/demo_profile_donuts.jpg',
                    'header_image_url' => 'images/demo/demo_header_town.jpg',
                ],
                [
                    'subdomain' => 'demo-moestavern',
                    'name' => "Moe's Tavern",
                    'description' => 'Live music, trivia, and open mic nights',
                    'url' => 'https://demo-moestavern.eventschedule.com/',
                    'profile_image_url' => 'images/demo/demo_profile_beer.jpg',
                    'header_image_url' => 'images/demo/demo_header_bar.jpg',
                ],
                [
                    'subdomain' => 'demo-amphitheater',
                    'name' => 'Springfield Amphitheater',
                    'description' => 'Outdoor concerts and performances',
                    'url' => 'https://demo-amphitheater.eventschedule.com/',
                    'profile_image_url' => 'images/demo/demo_profile_amphitheater.jpg',
                    'header_image_url' => 'images/demo/demo_header_concert.jpg',
                ],
                [
                    'subdomain' => 'demo-bowlarama',
                    'name' => "Barney's Bowl-A-Rama",
                    'description' => 'Bowling leagues, tournaments, and cosmic bowling nights',
                    'url' => 'https://demo-bowlarama.eventschedule.com/',
                    'profile_image_url' => 'images/demo/demo_profile_bowling.jpg',
                    'header_image_url' => 'images/demo/demo_header_bowling.jpg',
                ],
                [
                    'subdomain' => 'demo-aztectheater',
                    'name' => 'The Aztec Theater',
                    'description' => "Classic films and premieres at Springfield's art deco cinema",
                    'url' => 'https://demo-aztectheater.eventschedule.com/',
                    'profile_image_url' => 'images/demo/demo_profile_popcorn.jpg',
                    'header_image_url' => 'images/demo/demo_header_theater.jpg',
                ],
                [
                    'subdomain' => 'demo-lardlad',
                    'name' => 'Lard Lad Donuts',
                    'description' => 'Donut tastings, coffee events, and sweet celebrations',
                    'url' => 'https://demo-lardlad.eventschedule.com/',
                    'profile_image_url' => 'images/demo/demo_profile_donut_box.jpg',
                    'header_image_url' => 'images/demo/demo_header_donuts.jpg',
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
            'embed_calendar_feature' => route('marketing.embed_calendar'),
            'embed_tickets_feature' => route('marketing.embed_tickets'),
            'referral_program' => route('marketing.docs.referral_program'),
        ];

        return [
            // ===== USER GUIDE =====

            // Getting Started
            ['page' => 'Getting Started', 'section' => 'Create Your Account', 'description' => 'Register with email or Google login to get started.', 'url' => $r['getting_started'].'#create-account', 'category' => 'User Guide', 'keywords' => 'register sign up'],
            ['page' => 'Getting Started', 'section' => 'Create Your Schedule', 'description' => 'Set up your first schedule with a unique URL.', 'url' => $r['getting_started'].'#create-schedule', 'category' => 'User Guide', 'keywords' => 'new schedule setup'],
            ['page' => 'Getting Started', 'section' => 'Schedule Types', 'description' => 'Choose between Talent, Venue, and Curator schedule types.', 'url' => $r['getting_started'].'#schedule-types', 'category' => 'User Guide', 'keywords' => 'talent venue curator type'],
            ['page' => 'Getting Started', 'section' => 'Customize Your Schedule', 'description' => 'Set up profile, location, display settings, and sub-schedules.', 'url' => $r['getting_started'].'#customize', 'category' => 'User Guide', 'keywords' => 'customize personalize configure'],
            ['page' => 'Getting Started', 'section' => 'Frequently Asked Questions', 'description' => 'Common questions about schedules, URLs, and importing.', 'url' => $r['getting_started'].'#faq', 'category' => 'User Guide', 'keywords' => 'faq help questions'],
            ['page' => 'Getting Started', 'section' => 'Next Steps', 'description' => 'Links to related documentation for further setup.', 'url' => $r['getting_started'].'#next-steps', 'category' => 'User Guide', 'keywords' => ''],

            // Creating Schedules
            ['page' => 'Creating Schedules', 'section' => 'Schedule Types', 'description' => 'Choose the right schedule type for your use case.', 'url' => $r['creating_schedules'].'#schedule-types', 'category' => 'User Guide', 'keywords' => 'talent venue curator'],
            ['page' => 'Creating Schedules', 'section' => 'Details', 'description' => 'Set name, description, website, and URL slug.', 'url' => $r['creating_schedules'].'#details', 'category' => 'User Guide', 'keywords' => 'name description website slug domain custom'],
            ['page' => 'Creating Schedules', 'section' => 'Address', 'description' => 'Configure location settings for your schedule.', 'url' => $r['creating_schedules'].'#address', 'category' => 'User Guide', 'keywords' => 'location map'],
            ['page' => 'Creating Schedules', 'section' => 'Contact Information', 'description' => 'Add contact methods to your schedule.', 'url' => $r['creating_schedules'].'#contact-info', 'category' => 'User Guide', 'keywords' => 'email phone social contact'],
            ['page' => 'Creating Schedules', 'section' => 'Style', 'description' => 'Visual styling options for your schedule.', 'url' => $r['creating_schedules'].'#style', 'category' => 'User Guide', 'keywords' => 'design theme appearance'],
            ['page' => 'Creating Schedules', 'section' => 'Videos & Links', 'description' => 'Manage YouTube videos, social links, and payment links.', 'url' => $r['creating_schedules'].'#videos-links', 'category' => 'User Guide', 'keywords' => 'youtube social payment links videos'],
            ['page' => 'Creating Schedules', 'section' => 'Customize', 'description' => 'Customize your schedule with sub-schedules, custom fields, and sponsors.', 'url' => $r['creating_schedules'].'#customize', 'category' => 'User Guide', 'keywords' => 'customize personalize'],
            ['page' => 'Creating Schedules', 'section' => 'Sub-schedules', 'description' => 'Create categories to organize events within a schedule.', 'url' => $r['creating_schedules'].'#customize-subschedules', 'category' => 'User Guide', 'keywords' => 'groups categories organize'],
            ['page' => 'Creating Schedules', 'section' => 'Custom Fields', 'description' => 'Add custom fields to your events.', 'url' => $r['creating_schedules'].'#customize-custom-fields', 'category' => 'User Guide', 'keywords' => 'custom fields metadata'],
            ['page' => 'Creating Schedules', 'section' => 'Sponsors', 'description' => 'Showcase sponsor logos and links on your schedule page.', 'url' => $r['creating_schedules'].'#customize-sponsors', 'category' => 'User Guide', 'keywords' => 'sponsors logos branding partners'],
            ['page' => 'Creating Schedules', 'section' => 'Custom Labels', 'description' => 'Override default labels shown on your schedule page.', 'url' => $r['creating_schedules'].'#customize-custom-labels', 'category' => 'User Guide', 'keywords' => 'custom labels text rename override buttons'],
            ['page' => 'Creating Schedules', 'section' => 'Settings', 'description' => 'Configure general schedule settings.', 'url' => $r['creating_schedules'].'#settings', 'category' => 'User Guide', 'keywords' => 'configuration options preferences'],
            ['page' => 'Creating Schedules', 'section' => 'General Settings', 'description' => 'Timezone, language, and default settings.', 'url' => $r['creating_schedules'].'#settings-general', 'category' => 'User Guide', 'keywords' => 'timezone language defaults'],
            ['page' => 'Creating Schedules', 'section' => 'Custom Domain', 'description' => 'Set up a custom domain for your schedule using Direct (CNAME) or Redirect (Cloudflare) mode.', 'url' => $r['creating_schedules'].'#custom-domain', 'category' => 'User Guide', 'keywords' => 'custom domain cname dns branded url'],
            ['page' => 'Creating Schedules', 'section' => 'Notifications', 'description' => 'Configure email notification preferences for schedule activity.', 'url' => $r['creating_schedules'].'#settings-notifications', 'category' => 'User Guide', 'keywords' => 'notifications email alerts'],
            ['page' => 'Creating Schedules', 'section' => 'Advanced Settings', 'description' => 'Advanced schedule options and configuration.', 'url' => $r['creating_schedules'].'#settings-advanced', 'category' => 'User Guide', 'keywords' => 'advanced options'],
            ['page' => 'Creating Schedules', 'section' => 'Engagement', 'description' => 'Configure visitor interaction features for your schedule.', 'url' => $r['creating_schedules'].'#engagement', 'category' => 'User Guide', 'keywords' => 'engagement interaction community'],
            ['page' => 'Creating Schedules', 'section' => 'Requests', 'description' => 'Configure public event request submissions.', 'url' => $r['creating_schedules'].'#engagement-requests', 'category' => 'User Guide', 'keywords' => 'submissions public requests'],
            ['page' => 'Creating Schedules', 'section' => 'Fan Content', 'description' => 'Allow visitors to submit photos and videos to events.', 'url' => $r['creating_schedules'].'#engagement-fan-content', 'category' => 'User Guide', 'keywords' => 'fan content photos videos submissions'],
            ['page' => 'Creating Schedules', 'section' => 'Feedback', 'description' => 'Send feedback requests to attendees after events.', 'url' => $r['creating_schedules'].'#engagement-feedback', 'category' => 'User Guide', 'keywords' => 'feedback reviews ratings attendees'],
            ['page' => 'Creating Schedules', 'section' => 'Auto Import', 'description' => 'Automatically import events from external sources.', 'url' => $r['creating_schedules'].'#auto-import', 'category' => 'User Guide', 'keywords' => 'import automatic feed ical'],
            ['page' => 'Creating Schedules', 'section' => 'Integrations', 'description' => 'Connect with calendar and third-party services.', 'url' => $r['creating_schedules'].'#integrations', 'category' => 'User Guide', 'keywords' => 'connect sync third-party'],
            ['page' => 'Creating Schedules', 'section' => 'Email Settings', 'description' => 'Configure email delivery for your schedule.', 'url' => $r['creating_schedules'].'#integrations-email', 'category' => 'User Guide', 'keywords' => 'email smtp sender notifications'],
            ['page' => 'Creating Schedules', 'section' => 'Google Calendar', 'description' => 'Set up Google Calendar sync for your schedule.', 'url' => $r['creating_schedules'].'#integrations-google', 'category' => 'User Guide', 'keywords' => 'google calendar sync'],
            ['page' => 'Creating Schedules', 'section' => 'CalDAV', 'description' => 'Set up CalDAV protocol integration.', 'url' => $r['creating_schedules'].'#integrations-caldav', 'category' => 'User Guide', 'keywords' => 'caldav ical protocol'],
            ['page' => 'Creating Schedules', 'section' => 'Feeds', 'description' => 'iCal and RSS feed URLs for subscribing to your schedule.', 'url' => $r['creating_schedules'].'#integrations-feeds', 'category' => 'User Guide', 'keywords' => 'feeds ical rss subscribe calendar'],
            ['page' => 'Creating Schedules', 'section' => 'AI Details Generator', 'description' => 'Use AI to generate schedule descriptions (Enterprise).', 'url' => $r['creating_schedules'].'#ai-details-generator', 'category' => 'User Guide', 'keywords' => 'ai generate details description automatic'],

            // Schedule Styling
            ['page' => 'Schedule Styling', 'section' => 'Overview', 'description' => 'Introduction to schedule styling options.', 'url' => $r['schedule_styling'].'#overview', 'category' => 'User Guide', 'keywords' => 'design appearance theme'],
            ['page' => 'Schedule Styling', 'section' => 'Event Layout', 'description' => 'Choose between grid and list layout for events.', 'url' => $r['schedule_styling'].'#event-layout', 'category' => 'User Guide', 'keywords' => 'grid list layout display'],
            ['page' => 'Schedule Styling', 'section' => 'Profile Image', 'description' => 'Upload and set your profile or logo image.', 'url' => $r['schedule_styling'].'#profile-image', 'category' => 'User Guide', 'keywords' => 'logo avatar photo'],
            ['page' => 'Schedule Styling', 'section' => 'Header Images', 'description' => 'Set header banners with presets or custom uploads.', 'url' => $r['schedule_styling'].'#header-images', 'category' => 'User Guide', 'keywords' => 'banner cover header'],
            ['page' => 'Schedule Styling', 'section' => 'Background Options', 'description' => 'Choose solid color, gradient, or image backgrounds.', 'url' => $r['schedule_styling'].'#backgrounds', 'category' => 'User Guide', 'keywords' => 'background color gradient image'],
            ['page' => 'Schedule Styling', 'section' => 'Color Scheme', 'description' => 'Select accent colors for buttons and interactive elements.', 'url' => $r['schedule_styling'].'#color-scheme', 'category' => 'User Guide', 'keywords' => 'color accent theme palette'],
            ['page' => 'Schedule Styling', 'section' => 'Typography', 'description' => 'Choose custom fonts from Google Fonts.', 'url' => $r['schedule_styling'].'#typography', 'category' => 'User Guide', 'keywords' => 'font text typeface google fonts'],
            ['page' => 'Schedule Styling', 'section' => 'AI Style Generator', 'description' => 'Use AI to generate cohesive branding, images, colors, and fonts (Enterprise).', 'url' => $r['schedule_styling'].'#ai-style-generator', 'category' => 'User Guide', 'keywords' => 'ai generate style branding images automatic'],
            ['page' => 'Schedule Styling', 'section' => 'Remove Branding', 'description' => 'Remove the "Powered by Event Schedule" badge (Pro).', 'url' => $r['schedule_styling'].'#remove-branding', 'category' => 'User Guide', 'keywords' => 'branding badge powered by white label'],
            ['page' => 'Schedule Styling', 'section' => 'Custom CSS', 'description' => 'Add custom CSS for advanced styling (Pro).', 'url' => $r['schedule_styling'].'#custom-css', 'category' => 'User Guide', 'keywords' => 'css stylesheet custom code'],
            ['page' => 'Schedule Styling', 'section' => 'Live Preview', 'description' => 'Preview styling changes in real time.', 'url' => $r['schedule_styling'].'#live-preview', 'category' => 'User Guide', 'keywords' => 'preview real-time'],

            // Managing Schedules
            ['page' => 'Managing Schedules', 'section' => 'Overview', 'description' => 'Introduction to the schedule admin panel and tabs.', 'url' => $r['managing_schedules'].'#overview', 'category' => 'User Guide', 'keywords' => 'admin panel dashboard'],
            ['page' => 'Managing Schedules', 'section' => 'Schedule', 'description' => 'Main calendar view with event management.', 'url' => $r['managing_schedules'].'#schedule', 'category' => 'User Guide', 'keywords' => 'calendar events manage'],
            ['page' => 'Managing Schedules', 'section' => 'Actions', 'description' => 'Quick access dropdown for importing events, syncing calendars, generating graphics, embedding, and deleting.', 'url' => $r['managing_schedules'].'#actions', 'category' => 'User Guide', 'keywords' => 'actions dropdown import sync graphic embed delete'],
            ['page' => 'Managing Schedules', 'section' => 'Videos', 'description' => 'Assign YouTube videos for curator schedules.', 'url' => $r['managing_schedules'].'#videos', 'category' => 'User Guide', 'keywords' => 'youtube video curator'],
            ['page' => 'Managing Schedules', 'section' => 'Availability', 'description' => 'Set availability dates for talent schedules.', 'url' => $r['managing_schedules'].'#availability', 'category' => 'User Guide', 'keywords' => 'available dates talent booking'],
            ['page' => 'Managing Schedules', 'section' => 'Requests', 'description' => 'Manage public event request submissions.', 'url' => $r['managing_schedules'].'#requests', 'category' => 'User Guide', 'keywords' => 'submissions approve reject'],
            ['page' => 'Managing Schedules', 'section' => 'Followers', 'description' => 'Manage followers and follow links.', 'url' => $r['managing_schedules'].'#followers', 'category' => 'User Guide', 'keywords' => 'subscribers audience follow'],
            ['page' => 'Managing Schedules', 'section' => 'Team', 'description' => 'Manage team members and permissions.', 'url' => $r['managing_schedules'].'#team', 'category' => 'User Guide', 'keywords' => 'members permissions collaborate'],
            ['page' => 'Managing Schedules', 'section' => 'Plan', 'description' => 'View and manage your subscription plan.', 'url' => $r['managing_schedules'].'#plan', 'category' => 'User Guide', 'keywords' => 'subscription billing upgrade'],

            // Creating Events
            ['page' => 'Creating Events', 'section' => 'Manual Event Creation', 'description' => 'Create events manually with full control over details.', 'url' => $r['creating_events'].'#manual', 'category' => 'User Guide', 'keywords' => 'add new event create'],
            ['page' => 'Creating Events', 'section' => 'Event Details', 'description' => 'Set event name, dates, times, and description.', 'url' => $r['creating_events'].'#details', 'category' => 'User Guide', 'keywords' => 'name date time description'],
            ['page' => 'Creating Events', 'section' => 'Venue', 'description' => 'Add venue and location information to events.', 'url' => $r['creating_events'].'#venue', 'category' => 'User Guide', 'keywords' => 'location place address map'],
            ['page' => 'Creating Events', 'section' => 'Participants', 'description' => 'Add performers, speakers, or participants.', 'url' => $r['creating_events'].'#participants', 'category' => 'User Guide', 'keywords' => 'performers speakers artists lineup'],
            ['page' => 'Creating Events', 'section' => 'Recurring Events', 'description' => 'Set up events that repeat on a schedule.', 'url' => $r['creating_events'].'#recurring', 'category' => 'User Guide', 'keywords' => 'repeat weekly monthly recurring'],
            ['page' => 'Creating Events', 'section' => 'Agenda', 'description' => 'Create agenda items and event parts.', 'url' => $r['creating_events'].'#agenda', 'category' => 'User Guide', 'keywords' => 'parts itinerary lineup schedule'],
            ['page' => 'Creating Events', 'section' => 'Appearances on Other Schedules', 'description' => 'List your event on curator or talent schedules.', 'url' => $r['creating_events'].'#schedules', 'category' => 'User Guide', 'keywords' => 'cross-list curator appearances'],
            ['page' => 'Creating Events', 'section' => 'Google Calendar Integration', 'description' => 'Sync events with Google Calendar.', 'url' => $r['creating_events'].'#google-calendar', 'category' => 'User Guide', 'keywords' => 'google calendar sync'],
            ['page' => 'Creating Events', 'section' => 'WhatsApp Integration', 'description' => 'Add a WhatsApp link to your events.', 'url' => $r['creating_events'].'#whatsapp', 'category' => 'User Guide', 'keywords' => 'whatsapp chat message'],
            ['page' => 'Creating Events', 'section' => 'Ticket Configuration', 'description' => 'Set up tickets for an event.', 'url' => $r['creating_events'].'#tickets', 'category' => 'User Guide', 'keywords' => 'tickets pricing sell'],
            ['page' => 'Creating Events', 'section' => 'Privacy Settings', 'description' => 'Control event visibility and access.', 'url' => $r['creating_events'].'#privacy', 'category' => 'User Guide', 'keywords' => 'private public hidden visibility'],
            ['page' => 'Creating Events', 'section' => 'Custom Fields', 'description' => 'Add custom information fields to events.', 'url' => $r['creating_events'].'#custom-fields', 'category' => 'User Guide', 'keywords' => 'custom fields metadata'],
            ['page' => 'Creating Events', 'section' => 'Polls', 'description' => 'Create interactive polls for your events.', 'url' => $r['creating_events'].'#polls', 'category' => 'User Guide', 'keywords' => 'voting survey poll'],
            ['page' => 'Creating Events', 'section' => 'Feedback', 'description' => 'Control per-event feedback override settings.', 'url' => $r['creating_events'].'#feedback', 'category' => 'User Guide', 'keywords' => 'feedback rating review post-event override'],
            ['page' => 'Creating Events', 'section' => 'Fan Content', 'description' => 'Manage user-submitted videos and content.', 'url' => $r['creating_events'].'#fan-content', 'category' => 'User Guide', 'keywords' => 'user content videos submissions ugc'],
            ['page' => 'Creating Events', 'section' => 'AI Flyer Generation', 'description' => 'Generate event flyer images using AI (Enterprise).', 'url' => $r['creating_events'].'#ai-flyer', 'category' => 'User Guide', 'keywords' => 'ai flyer image generate poster design'],
            ['page' => 'Creating Events', 'section' => 'AI Details Generator', 'description' => 'Use AI to generate event descriptions and sub-schedule (Enterprise).', 'url' => $r['creating_events'].'#ai-details-generator', 'category' => 'User Guide', 'keywords' => 'ai generate details description automatic'],

            // AI Import
            ['page' => 'AI Import', 'section' => 'Overview', 'description' => 'Use AI to automatically import events.', 'url' => $r['ai_import'].'#ai-import', 'category' => 'User Guide', 'keywords' => 'ai artificial intelligence import parse'],
            ['page' => 'AI Import', 'section' => 'Importing from Text', 'description' => 'Paste event text and let AI extract the details.', 'url' => $r['ai_import'].'#text-import', 'category' => 'User Guide', 'keywords' => 'text paste extract parse'],
            ['page' => 'AI Import', 'section' => 'Importing from Images', 'description' => 'Upload flyers and extract event information with AI.', 'url' => $r['ai_import'].'#image-import', 'category' => 'User Guide', 'keywords' => 'image flyer photo scan ocr'],
            ['page' => 'AI Import', 'section' => 'Custom AI Prompts', 'description' => 'Create custom AI instructions for parsing events.', 'url' => $r['ai_import'].'#custom-prompts', 'category' => 'User Guide', 'keywords' => 'prompt instructions customize'],

            // Sharing
            ['page' => 'Sharing', 'section' => 'Your Schedule URL', 'description' => 'Share your unique schedule URL with others.', 'url' => $r['sharing'].'#schedule-url', 'category' => 'User Guide', 'keywords' => 'link url share'],
            ['page' => 'Sharing', 'section' => 'Embedding on Your Website', 'description' => 'Embed your schedule on external websites.', 'url' => $r['sharing'].'#embed', 'category' => 'User Guide', 'keywords' => 'embed iframe widget website'],
            ['page' => 'Sharing', 'section' => 'Social Media Sharing', 'description' => 'Share your schedule on social media platforms.', 'url' => $r['sharing'].'#social', 'category' => 'User Guide', 'keywords' => 'facebook twitter instagram social'],
            ['page' => 'Sharing', 'section' => 'Building Followers', 'description' => 'Grow your audience and follower base.', 'url' => $r['sharing'].'#followers', 'category' => 'User Guide', 'keywords' => 'followers subscribers grow audience'],
            ['page' => 'Sharing', 'section' => 'Calendar Subscriptions', 'description' => 'Provide iCal and RSS feed URLs for subscribers.', 'url' => $r['sharing'].'#calendar-feeds', 'category' => 'User Guide', 'keywords' => 'ical rss feed subscribe calendar'],
            ['page' => 'Sharing', 'section' => 'QR Codes', 'description' => 'Generate QR codes for your schedule.', 'url' => $r['sharing'].'#qr-code', 'category' => 'User Guide', 'keywords' => 'qr code scan print'],
            ['page' => 'Sharing', 'section' => 'Embed Troubleshooting', 'description' => 'Fix common embedding issues.', 'url' => $r['sharing'].'#troubleshooting', 'category' => 'User Guide', 'keywords' => 'troubleshoot fix embed problem'],
            ['page' => 'Sharing', 'section' => 'Embed Calendar (Feature Page)', 'description' => 'Embed your event calendar on any website with one line of code.', 'url' => $r['embed_calendar_feature'], 'category' => 'User Guide', 'keywords' => 'embed calendar iframe widget website'],

            // Newsletters
            ['page' => 'Newsletters', 'section' => 'Overview', 'description' => 'Send branded email newsletters to your audience.', 'url' => $r['newsletters'].'#overview', 'category' => 'User Guide', 'keywords' => 'email newsletter campaign'],
            ['page' => 'Newsletters', 'section' => 'Newsletter Builder', 'description' => 'Use the three-tab builder: Content, Style, Settings.', 'url' => $r['newsletters'].'#newsletter-builder', 'category' => 'User Guide', 'keywords' => 'builder editor compose'],
            ['page' => 'Newsletters', 'section' => 'Block Types', 'description' => 'Available content blocks: heading, text, events, button, image.', 'url' => $r['newsletters'].'#block-types', 'category' => 'User Guide', 'keywords' => 'blocks content heading text button image'],
            ['page' => 'Newsletters', 'section' => 'Templates', 'description' => 'Pre-designed newsletter templates.', 'url' => $r['newsletters'].'#templates', 'category' => 'User Guide', 'keywords' => 'template design preset'],
            ['page' => 'Newsletters', 'section' => 'Style Customization', 'description' => 'Customize colors, fonts, and button styles.', 'url' => $r['newsletters'].'#style-customization', 'category' => 'User Guide', 'keywords' => 'color font button style design'],
            ['page' => 'Newsletters', 'section' => 'Recipients & Segments', 'description' => 'Choose audiences: followers, ticket buyers, or custom lists.', 'url' => $r['newsletters'].'#recipients', 'category' => 'User Guide', 'keywords' => 'recipients segments audience list'],
            ['page' => 'Newsletters', 'section' => 'Managing Segments', 'description' => 'Create and manage reusable audience segments.', 'url' => $r['newsletters'].'#managing-segments', 'category' => 'User Guide', 'keywords' => 'segments create edit delete reusable'],
            ['page' => 'Newsletters', 'section' => 'Importing Emails', 'description' => 'Bulk import contacts via form entry, paste, or CSV upload.', 'url' => $r['newsletters'].'#importing-emails', 'category' => 'User Guide', 'keywords' => 'import csv paste emails bulk contacts'],
            ['page' => 'Newsletters', 'section' => 'Sending', 'description' => 'Send now, schedule for later, or send a test.', 'url' => $r['newsletters'].'#sending', 'category' => 'User Guide', 'keywords' => 'send schedule deliver'],
            ['page' => 'Newsletters', 'section' => 'A/B Testing', 'description' => 'Test different newsletter variants.', 'url' => $r['newsletters'].'#ab-testing', 'category' => 'User Guide', 'keywords' => 'ab test split experiment'],
            ['page' => 'Newsletters', 'section' => 'Analytics', 'description' => 'Track newsletter performance and engagement.', 'url' => $r['newsletters'].'#analytics', 'category' => 'User Guide', 'keywords' => 'opens clicks metrics performance'],
            ['page' => 'Newsletters', 'section' => 'Managing Newsletters', 'description' => 'Manage drafts, scheduled, and sent newsletters.', 'url' => $r['newsletters'].'#managing', 'category' => 'User Guide', 'keywords' => 'drafts manage list'],

            // Tickets
            ['page' => 'Selling Tickets', 'section' => 'General Ticketing', 'description' => 'Overview of ticket sales setup and features.', 'url' => $r['tickets'].'#general', 'category' => 'User Guide', 'keywords' => 'ticketing overview sell'],
            ['page' => 'Selling Tickets', 'section' => 'External Events', 'description' => 'Link to external registration pages and display event pricing.', 'url' => $r['tickets'].'#external', 'category' => 'User Guide', 'keywords' => 'external registration url price coupon link'],
            ['page' => 'Selling Tickets', 'section' => 'Registration (RSVP)', 'description' => 'Enable free event registration with optional capacity limits.', 'url' => $r['tickets'].'#registration', 'category' => 'User Guide', 'keywords' => 'registration rsvp free sign up attend'],
            ['page' => 'Selling Tickets', 'section' => 'Ticketing Setup', 'description' => 'Set up paid or multi-type ticketing for events.', 'url' => $r['tickets'].'#ticketing', 'category' => 'User Guide', 'keywords' => 'ticketing setup paid pro sell'],
            ['page' => 'Selling Tickets', 'section' => 'Ticket Types', 'description' => 'Configure different ticket type options.', 'url' => $r['tickets'].'#ticket-types', 'category' => 'User Guide', 'keywords' => 'general vip types tiers'],
            ['page' => 'Selling Tickets', 'section' => 'Free Tickets', 'description' => 'Offer free tickets by setting the price to zero.', 'url' => $r['tickets'].'#free-events', 'category' => 'User Guide', 'keywords' => 'free no cost zero price ticket'],
            ['page' => 'Selling Tickets', 'section' => 'Payment Processing', 'description' => 'Configure payment methods for ticket sales.', 'url' => $r['tickets'].'#payment', 'category' => 'User Guide', 'keywords' => 'payment stripe invoice ninja'],
            ['page' => 'Selling Tickets', 'section' => 'Invoice Ninja Modes', 'description' => 'Invoice mode vs. payment link mode for Invoice Ninja.', 'url' => $r['tickets'].'#invoiceninja-modes', 'category' => 'User Guide', 'keywords' => 'invoice ninja mode payment link'],
            ['page' => 'Selling Tickets', 'section' => 'Additional Options', 'description' => 'Extra checkout and ticket options.', 'url' => $r['tickets'].'#options', 'category' => 'User Guide', 'keywords' => 'options settings configuration'],
            ['page' => 'Selling Tickets', 'section' => 'Custom Checkout Fields', 'description' => 'Add custom fields to the checkout form.', 'url' => $r['tickets'].'#checkout-fields', 'category' => 'User Guide', 'keywords' => 'checkout form fields custom'],
            ['page' => 'Selling Tickets', 'section' => 'Promo Codes', 'description' => 'Create discount codes for ticket purchases.', 'url' => $r['tickets'].'#promo-codes', 'category' => 'User Guide', 'keywords' => 'promo discount coupon code'],
            ['page' => 'Selling Tickets', 'section' => 'Managing Sales', 'description' => 'View and manage ticket sale records.', 'url' => $r['tickets'].'#managing-sales', 'category' => 'User Guide', 'keywords' => 'sales orders manage view'],
            ['page' => 'Selling Tickets', 'section' => 'Filtering Sales', 'description' => 'Search and filter sales by buyer name, email, or event name.', 'url' => $r['tickets'].'#filtering-sales', 'category' => 'User Guide', 'keywords' => 'filter search sales buyer name email'],
            ['page' => 'Selling Tickets', 'section' => 'Sale Notifications', 'description' => 'Get notified when tickets are purchased.', 'url' => $r['tickets'].'#sale-notifications', 'category' => 'User Guide', 'keywords' => 'notification alert email'],
            ['page' => 'Selling Tickets', 'section' => 'Export Sales Data', 'description' => 'Export ticket sales as CSV.', 'url' => $r['tickets'].'#export', 'category' => 'User Guide', 'keywords' => 'export csv download data'],
            ['page' => 'Selling Tickets', 'section' => 'Check-In System', 'description' => 'QR code-based check-in for attendees.', 'url' => $r['tickets'].'#check-in', 'category' => 'User Guide', 'keywords' => 'check-in qr code scan attendee'],
            ['page' => 'Selling Tickets', 'section' => 'Check-In Dashboard', 'description' => 'Dashboard for managing event check-ins.', 'url' => $r['tickets'].'#checkin-dashboard', 'category' => 'User Guide', 'keywords' => 'dashboard check-in manage'],
            ['page' => 'Selling Tickets', 'section' => 'Waitlist', 'description' => 'Manage waitlists for sold-out events.', 'url' => $r['tickets'].'#waitlist', 'category' => 'User Guide', 'keywords' => 'waitlist waiting list sold out'],
            ['page' => 'Selling Tickets', 'section' => 'Post-Event Feedback', 'description' => 'Collect star ratings and comments from attendees after events.', 'url' => $r['tickets'].'#feedback', 'category' => 'User Guide', 'keywords' => 'feedback rating stars review comment post-event'],
            ['page' => 'Selling Tickets', 'section' => 'Financial Reporting', 'description' => 'Track revenue and financial metrics.', 'url' => $r['tickets'].'#financial', 'category' => 'User Guide', 'keywords' => 'revenue money financial report'],
            ['page' => 'Selling Tickets', 'section' => 'Embed Widget', 'description' => 'Embed a ticket purchase or RSVP form on any website.', 'url' => $r['tickets'].'#embed-widget', 'category' => 'User Guide', 'keywords' => 'embed widget iframe ticket rsvp website'],
            ['page' => 'Selling Tickets', 'section' => 'Embed Tickets (Feature Page)', 'description' => 'Embed a ticket purchase or RSVP form on any website with one line of code.', 'url' => $r['embed_tickets_feature'], 'category' => 'User Guide', 'keywords' => 'embed tickets iframe widget website sell rsvp purchase'],

            // Event Graphics
            ['page' => 'Event Graphics', 'section' => 'Overview', 'description' => 'Generate shareable images for social media.', 'url' => $r['event_graphics'].'#overview', 'category' => 'User Guide', 'keywords' => 'graphics images social media flyer'],
            ['page' => 'Event Graphics', 'section' => 'Text Template', 'description' => 'Customize text formatting for event graphics.', 'url' => $r['event_graphics'].'#text-template', 'category' => 'User Guide', 'keywords' => 'template text format'],
            ['page' => 'Event Graphics', 'section' => 'Quick Reference', 'description' => 'Essential template variables at a glance.', 'url' => $r['event_graphics'].'#quick-reference', 'category' => 'User Guide', 'keywords' => 'variables reference cheatsheet'],
            ['page' => 'Event Graphics', 'section' => 'All Template Variables', 'description' => 'Complete list of available template variables.', 'url' => $r['event_graphics'].'#variables', 'category' => 'User Guide', 'keywords' => 'variables placeholders tokens'],
            ['page' => 'Event Graphics', 'section' => 'AI Text Prompt', 'description' => 'Use AI to transform generated text (Enterprise).', 'url' => $r['event_graphics'].'#ai-prompt', 'category' => 'User Guide', 'keywords' => 'ai prompt transform text'],
            ['page' => 'Event Graphics', 'section' => 'Email Scheduling', 'description' => 'Schedule automatic graphic emails (Enterprise).', 'url' => $r['event_graphics'].'#email-scheduling', 'category' => 'User Guide', 'keywords' => 'email schedule automatic send'],

            // Analytics
            ['page' => 'Analytics', 'section' => 'Overview', 'description' => 'Introduction to the analytics dashboard.', 'url' => $r['analytics'].'#overview', 'category' => 'User Guide', 'keywords' => 'analytics dashboard stats'],
            ['page' => 'Analytics', 'section' => 'Filters', 'description' => 'Filter by schedule, date range, and period.', 'url' => $r['analytics'].'#filters', 'category' => 'User Guide', 'keywords' => 'filter date range period'],
            ['page' => 'Analytics', 'section' => 'Web Analytics', 'description' => 'Views, devices, browsers, traffic sources, and geographic data.', 'url' => $r['analytics'].'#web-analytics', 'category' => 'User Guide', 'keywords' => 'views devices browsers traffic sources geography'],
            ['page' => 'Analytics', 'section' => 'Revenue', 'description' => 'Revenue stats, conversion rates, promo codes, boost and newsletter funnels.', 'url' => $r['analytics'].'#revenue', 'category' => 'User Guide', 'keywords' => 'revenue conversion promo boost newsletter funnel'],
            ['page' => 'Analytics', 'section' => 'Check-ins', 'description' => 'Tickets sold, attendance rates, arrival times, and event breakdown.', 'url' => $r['analytics'].'#checkins', 'category' => 'User Guide', 'keywords' => 'checkins attendance arrival tickets sold no-shows'],
            ['page' => 'Analytics', 'section' => 'No Data State', 'description' => 'Why analytics data might be missing.', 'url' => $r['analytics'].'#no-data', 'category' => 'User Guide', 'keywords' => 'empty no data missing'],

            // Account Settings
            ['page' => 'Account Settings', 'section' => 'Profile Information', 'description' => 'Manage name, email, timezone, language, and profile image.', 'url' => $r['account_settings'].'#profile', 'category' => 'User Guide', 'keywords' => 'name email timezone language profile'],
            ['page' => 'Account Settings', 'section' => 'Payment Methods', 'description' => 'Configure payment methods for ticket sales.', 'url' => $r['account_settings'].'#payments', 'category' => 'User Guide', 'keywords' => 'payment method stripe invoice'],
            ['page' => 'Account Settings', 'section' => 'Stripe', 'description' => 'Connect Stripe for payment processing.', 'url' => $r['account_settings'].'#stripe', 'category' => 'User Guide', 'keywords' => 'stripe connect payment'],
            ['page' => 'Account Settings', 'section' => 'Invoice Ninja', 'description' => 'Set up Invoice Ninja as a payment gateway.', 'url' => $r['account_settings'].'#invoice-ninja', 'category' => 'User Guide', 'keywords' => 'invoice ninja payment'],
            ['page' => 'Account Settings', 'section' => 'Payment URL', 'description' => 'Configure a custom payment URL.', 'url' => $r['account_settings'].'#payment-url', 'category' => 'User Guide', 'keywords' => 'payment url link custom'],
            ['page' => 'Account Settings', 'section' => 'API Settings', 'description' => 'Manage API access and keys (Pro).', 'url' => $r['account_settings'].'#api', 'category' => 'User Guide', 'keywords' => 'api key token access'],
            ['page' => 'Account Settings', 'section' => 'Webhooks', 'description' => 'Configure webhook notifications (Pro).', 'url' => $r['account_settings'].'#webhooks', 'category' => 'User Guide', 'keywords' => 'webhooks notifications callback'],
            ['page' => 'Account Settings', 'section' => 'Google Settings', 'description' => 'Manage Google account and calendar sync.', 'url' => $r['account_settings'].'#google', 'category' => 'User Guide', 'keywords' => 'google account calendar oauth'],
            ['page' => 'Account Settings', 'section' => 'Backup & Restore', 'description' => 'Export and import schedule data as backup files.', 'url' => $r['account_settings'].'#backup', 'category' => 'User Guide', 'keywords' => 'backup restore export import data migration download upload'],
            ['page' => 'Account Settings', 'section' => 'App Update', 'description' => 'Check application update status (selfhosted).', 'url' => $r['account_settings'].'#app-update', 'category' => 'User Guide', 'keywords' => 'update version selfhosted'],
            ['page' => 'Account Settings', 'section' => 'Update Password', 'description' => 'Change your account password.', 'url' => $r['account_settings'].'#password', 'category' => 'User Guide', 'keywords' => 'password change security'],
            ['page' => 'Account Settings', 'section' => 'Two-Factor Authentication', 'description' => 'Enable 2FA for account security.', 'url' => $r['account_settings'].'#two-factor', 'category' => 'User Guide', 'keywords' => '2fa two-factor authentication security totp'],
            ['page' => 'Account Settings', 'section' => 'Delete Account', 'description' => 'Permanently delete your account.', 'url' => $r['account_settings'].'#delete-account', 'category' => 'User Guide', 'keywords' => 'delete remove account'],

            // Scan Agenda
            ['page' => 'Scan Agenda', 'section' => 'Overview', 'description' => 'Use AI to scan printed agendas and create event parts.', 'url' => $r['scan_agenda'].'#overview', 'category' => 'User Guide', 'keywords' => 'scan photo agenda ai camera'],
            ['page' => 'Scan Agenda', 'section' => 'Getting Started', 'description' => 'Access Scan Agenda from the admin panel.', 'url' => $r['scan_agenda'].'#getting-started', 'category' => 'User Guide', 'keywords' => 'start begin access'],
            ['page' => 'Scan Agenda', 'section' => 'How It Works', 'description' => 'Steps for scanning, parsing, reviewing, and saving.', 'url' => $r['scan_agenda'].'#how-it-works', 'category' => 'User Guide', 'keywords' => 'process steps workflow'],
            ['page' => 'Scan Agenda', 'section' => 'Custom AI Prompt', 'description' => 'Customize AI instructions for agenda parsing.', 'url' => $r['scan_agenda'].'#custom-prompt', 'category' => 'User Guide', 'keywords' => 'prompt customize instructions'],
            ['page' => 'Scan Agenda', 'section' => 'Tips', 'description' => 'Best practices for scanning agendas.', 'url' => $r['scan_agenda'].'#tips', 'category' => 'User Guide', 'keywords' => 'tips best practices lighting'],

            // Boost
            ['page' => 'Boost', 'section' => 'Overview', 'description' => 'Promote events with Facebook and Instagram ads.', 'url' => $r['boost'].'#overview', 'category' => 'User Guide', 'keywords' => 'boost promote advertise facebook instagram meta'],
            ['page' => 'Boost', 'section' => 'Quick Mode', 'description' => 'Create ad campaigns quickly with minimal setup.', 'url' => $r['boost'].'#quick-mode', 'category' => 'User Guide', 'keywords' => 'quick fast simple campaign'],
            ['page' => 'Boost', 'section' => 'Advanced Mode', 'description' => 'Full control over budget, targeting, and creative.', 'url' => $r['boost'].'#advanced-mode', 'category' => 'User Guide', 'keywords' => 'advanced targeting budget creative'],
            ['page' => 'Boost', 'section' => 'Smart Defaults', 'description' => 'Automatic configuration based on event type.', 'url' => $r['boost'].'#smart-defaults', 'category' => 'User Guide', 'keywords' => 'defaults automatic smart'],
            ['page' => 'Boost', 'section' => 'Managing Campaigns', 'description' => 'View campaign statuses and manage ads.', 'url' => $r['boost'].'#managing-campaigns', 'category' => 'User Guide', 'keywords' => 'campaigns manage status'],
            ['page' => 'Boost', 'section' => 'Spending Limits', 'description' => 'Budget limits based on campaign history.', 'url' => $r['boost'].'#spending-limits', 'category' => 'User Guide', 'keywords' => 'budget limits spending cap'],
            ['page' => 'Boost', 'section' => 'Analytics', 'description' => 'View campaign performance and metrics.', 'url' => $r['boost'].'#analytics', 'category' => 'User Guide', 'keywords' => 'analytics performance metrics reach impressions'],
            ['page' => 'Boost', 'section' => 'Billing & Refunds', 'description' => 'Pricing structure and refund policy.', 'url' => $r['boost'].'#billing', 'category' => 'User Guide', 'keywords' => 'billing pricing refund cost'],
            ['page' => 'Boost', 'section' => 'Tips', 'description' => 'Best practices for successful ad campaigns.', 'url' => $r['boost'].'#tips', 'category' => 'User Guide', 'keywords' => 'tips best practices advice'],

            // ===== SELFHOST =====

            // Installation
            ['page' => 'Installation', 'section' => 'Overview', 'description' => 'Manual installation guide for selfhosted deployments.', 'url' => $r['selfhost_installation'].'#overview', 'category' => 'Selfhost', 'keywords' => 'install setup deploy server'],
            ['page' => 'Installation', 'section' => 'Requirements', 'description' => 'Server requirements: PHP 8.1+, MySQL, and more.', 'url' => $r['selfhost_installation'].'#requirements', 'category' => 'Selfhost', 'keywords' => 'requirements php mysql server'],
            ['page' => 'Installation', 'section' => 'Set Up the Database', 'description' => 'Create MySQL database and user.', 'url' => $r['selfhost_installation'].'#database', 'category' => 'Selfhost', 'keywords' => 'database mysql create'],
            ['page' => 'Installation', 'section' => 'Download the Application', 'description' => 'Download and extract Event Schedule files.', 'url' => $r['selfhost_installation'].'#download', 'category' => 'Selfhost', 'keywords' => 'download extract files'],
            ['page' => 'Installation', 'section' => 'Set File Permissions', 'description' => 'Set proper directory permissions.', 'url' => $r['selfhost_installation'].'#permissions', 'category' => 'Selfhost', 'keywords' => 'permissions chmod directories'],
            ['page' => 'Installation', 'section' => 'Configure Environment', 'description' => 'Set up the .env configuration file.', 'url' => $r['selfhost_installation'].'#environment', 'category' => 'Selfhost', 'keywords' => 'env environment configuration'],
            ['page' => 'Installation', 'section' => 'Set Up the Cron Job', 'description' => 'Configure scheduled tasks for the application.', 'url' => $r['selfhost_installation'].'#cron', 'category' => 'Selfhost', 'keywords' => 'cron scheduler task'],
            ['page' => 'Installation', 'section' => 'Verification', 'description' => 'Test and verify the installation.', 'url' => $r['selfhost_installation'].'#verification', 'category' => 'Selfhost', 'keywords' => 'verify test check'],

            // Stripe (Selfhost)
            ['page' => 'Stripe Integration', 'section' => 'Overview', 'description' => 'Set up Stripe for payment processing.', 'url' => $r['selfhost_stripe'].'#overview', 'category' => 'Selfhost', 'keywords' => 'stripe payment setup'],
            ['page' => 'Stripe Integration', 'section' => 'Choose Your Setup', 'description' => 'Select the right deployment option.', 'url' => $r['selfhost_stripe'].'#choose-setup', 'category' => 'Selfhost', 'keywords' => 'setup option deployment'],
            ['page' => 'Stripe Integration', 'section' => 'For Selfhosted Users', 'description' => 'Stripe configuration for selfhosted deployments.', 'url' => $r['selfhost_stripe'].'#selfhosted-users', 'category' => 'Selfhost', 'keywords' => 'selfhosted stripe configuration'],
            ['page' => 'Stripe Integration', 'section' => 'For SaaS Operators', 'description' => 'Stripe Connect setup for SaaS platforms.', 'url' => $r['selfhost_stripe'].'#saas-operators', 'category' => 'Selfhost', 'keywords' => 'saas stripe connect'],
            ['page' => 'Stripe Integration', 'section' => 'Invoice Ninja Integration', 'description' => 'Configure Invoice Ninja as a payment gateway.', 'url' => $r['selfhost_stripe'].'#invoice-ninja', 'category' => 'Selfhost', 'keywords' => 'invoice ninja payment gateway'],
            ['page' => 'Stripe Integration', 'section' => 'Testing', 'description' => 'Test your payment setup.', 'url' => $r['selfhost_stripe'].'#testing', 'category' => 'Selfhost', 'keywords' => 'test payment debug'],
            ['page' => 'Stripe Integration', 'section' => 'Troubleshooting', 'description' => 'Fix common payment issues.', 'url' => $r['selfhost_stripe'].'#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix problem'],
            ['page' => 'Stripe Integration', 'section' => 'Security', 'description' => 'Payment security considerations.', 'url' => $r['selfhost_stripe'].'#security', 'category' => 'Selfhost', 'keywords' => 'security keys secrets'],

            // Google Calendar (Selfhost)
            ['page' => 'Google Calendar', 'section' => 'Prerequisites', 'description' => 'Google Cloud project requirements for calendar sync.', 'url' => $r['selfhost_google_calendar'].'#prerequisites', 'category' => 'Selfhost', 'keywords' => 'google cloud project prerequisites'],
            ['page' => 'Google Calendar', 'section' => 'Setup Instructions', 'description' => 'Step-by-step OAuth2 setup for Google Calendar.', 'url' => $r['selfhost_google_calendar'].'#setup', 'category' => 'Selfhost', 'keywords' => 'setup oauth2 credentials'],
            ['page' => 'Google Calendar', 'section' => 'Features', 'description' => 'Google Calendar sync capabilities.', 'url' => $r['selfhost_google_calendar'].'#features', 'category' => 'Selfhost', 'keywords' => 'features sync bidirectional'],
            ['page' => 'Google Calendar', 'section' => 'Usage', 'description' => 'How to use the Google Calendar integration.', 'url' => $r['selfhost_google_calendar'].'#usage', 'category' => 'Selfhost', 'keywords' => 'usage connect calendar'],
            ['page' => 'Google Calendar', 'section' => 'API Endpoints', 'description' => 'Google Calendar API endpoints reference.', 'url' => $r['selfhost_google_calendar'].'#api-endpoints', 'category' => 'Selfhost', 'keywords' => 'api endpoints routes'],
            ['page' => 'Google Calendar', 'section' => 'Troubleshooting', 'description' => 'Debug Google Calendar sync issues.', 'url' => $r['selfhost_google_calendar'].'#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix debug sync'],
            ['page' => 'Google Calendar', 'section' => 'Security', 'description' => 'Token storage and security considerations.', 'url' => $r['selfhost_google_calendar'].'#security', 'category' => 'Selfhost', 'keywords' => 'security tokens oauth'],

            // Email (Selfhost)
            ['page' => 'Email Setup', 'section' => 'Overview', 'description' => 'Configure email for your selfhosted instance.', 'url' => $r['selfhost_email'].'#overview', 'category' => 'Selfhost', 'keywords' => 'email mail setup'],
            ['page' => 'Email Setup', 'section' => 'SMTP Setup', 'description' => 'Configure SMTP for sending emails.', 'url' => $r['selfhost_email'].'#smtp', 'category' => 'Selfhost', 'keywords' => 'smtp mail server'],
            ['page' => 'Email Setup', 'section' => 'Other Mail Drivers', 'description' => 'Alternative mail drivers: Mailgun, SES, and more.', 'url' => $r['selfhost_email'].'#drivers', 'category' => 'Selfhost', 'keywords' => 'mailgun ses postmark driver'],
            ['page' => 'Email Setup', 'section' => 'Sender Configuration', 'description' => 'Configure the email sender address and name.', 'url' => $r['selfhost_email'].'#sender', 'category' => 'Selfhost', 'keywords' => 'sender from address name'],
            ['page' => 'Email Setup', 'section' => 'Testing', 'description' => 'Test your email configuration.', 'url' => $r['selfhost_email'].'#testing', 'category' => 'Selfhost', 'keywords' => 'test email verify'],
            ['page' => 'Email Setup', 'section' => 'Troubleshooting', 'description' => 'Fix email delivery issues.', 'url' => $r['selfhost_email'].'#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix delivery'],

            // AI (Selfhost)
            ['page' => 'AI Setup', 'section' => 'Overview', 'description' => 'Enable AI features with Google Gemini.', 'url' => $r['selfhost_ai'].'#overview', 'category' => 'Selfhost', 'keywords' => 'ai gemini setup'],
            ['page' => 'AI Setup', 'section' => 'AI Features', 'description' => 'Available AI features: import, scan, translation.', 'url' => $r['selfhost_ai'].'#features', 'category' => 'Selfhost', 'keywords' => 'features import scan translate'],
            ['page' => 'AI Setup', 'section' => 'Get an API Key', 'description' => 'Obtain a Google Gemini API key.', 'url' => $r['selfhost_ai'].'#api-key', 'category' => 'Selfhost', 'keywords' => 'api key gemini google'],
            ['page' => 'AI Setup', 'section' => 'Configuration', 'description' => 'Set the GEMINI_API_KEY environment variable.', 'url' => $r['selfhost_ai'].'#configuration', 'category' => 'Selfhost', 'keywords' => 'configuration env variable'],
            ['page' => 'AI Setup', 'section' => 'Troubleshooting', 'description' => 'Fix AI API issues.', 'url' => $r['selfhost_ai'].'#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix api error'],

            // Admin Panel (Selfhost)
            ['page' => 'Admin Panel', 'section' => 'Overview', 'description' => 'Admin panel organization and sections.', 'url' => $r['selfhost_admin'].'#overview', 'category' => 'Selfhost', 'keywords' => 'admin panel dashboard'],
            ['page' => 'Admin Panel', 'section' => 'Accessing /admin', 'description' => 'How to access the admin panel.', 'url' => $r['selfhost_admin'].'#accessing', 'category' => 'Selfhost', 'keywords' => 'access login admin url'],
            ['page' => 'Admin Panel', 'section' => 'Dashboard', 'description' => 'Key metrics and overview dashboard.', 'url' => $r['selfhost_admin'].'#dashboard', 'category' => 'Selfhost', 'keywords' => 'dashboard metrics overview'],
            ['page' => 'Admin Panel', 'section' => 'Users', 'description' => 'User management and administration.', 'url' => $r['selfhost_admin'].'#insights-users', 'category' => 'Selfhost', 'keywords' => 'users management accounts'],
            ['page' => 'Admin Panel', 'section' => 'Revenue', 'description' => 'Revenue analytics and tracking.', 'url' => $r['selfhost_admin'].'#insights-revenue', 'category' => 'Selfhost', 'keywords' => 'revenue income money analytics'],
            ['page' => 'Admin Panel', 'section' => 'Analytics', 'description' => 'Traffic and usage analytics.', 'url' => $r['selfhost_admin'].'#insights-analytics', 'category' => 'Selfhost', 'keywords' => 'analytics traffic views'],
            ['page' => 'Admin Panel', 'section' => 'Usage', 'description' => 'Feature usage tracking.', 'url' => $r['selfhost_admin'].'#insights-usage', 'category' => 'Selfhost', 'keywords' => 'usage features tracking'],
            ['page' => 'Admin Panel', 'section' => 'Boost Management', 'description' => 'Manage Boost ad campaigns.', 'url' => $r['selfhost_admin'].'#manage-boost', 'category' => 'Selfhost', 'keywords' => 'boost campaigns manage ads'],
            ['page' => 'Admin Panel', 'section' => 'Plans Management', 'description' => 'Manage plan tiers (SaaS only).', 'url' => $r['selfhost_admin'].'#manage-plans', 'category' => 'Selfhost', 'keywords' => 'plans tiers subscription manage'],
            ['page' => 'Admin Panel', 'section' => 'Domains Management', 'description' => 'Manage custom domains (SaaS only).', 'url' => $r['selfhost_admin'].'#manage-domains', 'category' => 'Selfhost', 'keywords' => 'domains custom manage'],
            ['page' => 'Admin Panel', 'section' => 'Newsletters Management', 'description' => 'Manage newsletters from the admin panel.', 'url' => $r['selfhost_admin'].'#manage-newsletters', 'category' => 'Selfhost', 'keywords' => 'newsletters manage admin'],
            ['page' => 'Admin Panel', 'section' => 'Blog Management', 'description' => 'Manage blog posts (SaaS only).', 'url' => $r['selfhost_admin'].'#manage-blog', 'category' => 'Selfhost', 'keywords' => 'blog posts manage content'],
            ['page' => 'Admin Panel', 'section' => 'Audit Log', 'description' => 'View platform activity audit log.', 'url' => $r['selfhost_admin'].'#system-audit-log', 'category' => 'Selfhost', 'keywords' => 'audit log activity tracking'],
            ['page' => 'Admin Panel', 'section' => 'Queue', 'description' => 'Monitor background job queue.', 'url' => $r['selfhost_admin'].'#system-queue', 'category' => 'Selfhost', 'keywords' => 'queue jobs background worker'],
            ['page' => 'Admin Panel', 'section' => 'Logs', 'description' => 'View application error logs.', 'url' => $r['selfhost_admin'].'#system-logs', 'category' => 'Selfhost', 'keywords' => 'logs errors debug'],

            // Boost Setup (Selfhost)
            ['page' => 'Boost Setup', 'section' => 'Overview', 'description' => 'Set up Meta/Facebook boost for selfhosted instances.', 'url' => $r['selfhost_boost'].'#overview', 'category' => 'Selfhost', 'keywords' => 'boost meta facebook setup'],
            ['page' => 'Boost Setup', 'section' => 'Create a Facebook App', 'description' => 'Create and configure a Facebook app.', 'url' => $r['selfhost_boost'].'#facebook-app', 'category' => 'Selfhost', 'keywords' => 'facebook app create meta'],
            ['page' => 'Boost Setup', 'section' => 'Meta Business & Ad Account', 'description' => 'Set up business and ad accounts.', 'url' => $r['selfhost_boost'].'#ad-account', 'category' => 'Selfhost', 'keywords' => 'business ad account meta'],
            ['page' => 'Boost Setup', 'section' => 'Facebook Page', 'description' => 'Configure a Facebook page for ads.', 'url' => $r['selfhost_boost'].'#facebook-page', 'category' => 'Selfhost', 'keywords' => 'facebook page configure'],
            ['page' => 'Boost Setup', 'section' => 'System User & Access Token', 'description' => 'Generate a system user access token.', 'url' => $r['selfhost_boost'].'#system-user', 'category' => 'Selfhost', 'keywords' => 'system user token access'],
            ['page' => 'Boost Setup', 'section' => 'Meta Pixel', 'description' => 'Set up Meta Pixel for conversion tracking.', 'url' => $r['selfhost_boost'].'#pixel', 'category' => 'Selfhost', 'keywords' => 'pixel tracking conversion meta'],
            ['page' => 'Boost Setup', 'section' => 'Webhooks', 'description' => 'Configure webhooks for Boost integration.', 'url' => $r['selfhost_boost'].'#webhooks', 'category' => 'Selfhost', 'keywords' => 'webhooks callback notifications'],
            ['page' => 'Boost Setup', 'section' => 'App Review', 'description' => 'Submit your app for Meta review.', 'url' => $r['selfhost_boost'].'#app-review', 'category' => 'Selfhost', 'keywords' => 'review approval meta facebook'],
            ['page' => 'Boost Setup', 'section' => 'Environment Variables', 'description' => 'Complete .env configuration for Boost.', 'url' => $r['selfhost_boost'].'#environment', 'category' => 'Selfhost', 'keywords' => 'env environment variables configuration'],
            ['page' => 'Boost Setup', 'section' => 'Scheduled Command', 'description' => 'Set up the Boost sync scheduler.', 'url' => $r['selfhost_boost'].'#scheduled-command', 'category' => 'Selfhost', 'keywords' => 'scheduler cron command sync'],

            // ===== SAAS =====

            // SaaS Setup
            ['page' => 'SaaS Setup', 'section' => 'Overview', 'description' => 'Deploy Event Schedule as a multi-tenant SaaS.', 'url' => $r['saas_setup'].'#overview', 'category' => 'SaaS', 'keywords' => 'saas multi-tenant deploy'],
            ['page' => 'SaaS Setup', 'section' => 'Prerequisites', 'description' => 'Requirements for SaaS deployment.', 'url' => $r['saas_setup'].'#prerequisites', 'category' => 'SaaS', 'keywords' => 'requirements prerequisites'],
            ['page' => 'SaaS Setup', 'section' => 'Environment Variables', 'description' => 'SaaS-specific .env configuration.', 'url' => $r['saas_setup'].'#environment', 'category' => 'SaaS', 'keywords' => 'env environment configuration'],
            ['page' => 'SaaS Setup', 'section' => 'DNS Setup', 'description' => 'Configure subdomain DNS for SaaS.', 'url' => $r['saas_setup'].'#dns', 'category' => 'SaaS', 'keywords' => 'dns subdomain wildcard'],
            ['page' => 'SaaS Setup', 'section' => 'Web Server', 'description' => 'Web server configuration for SaaS mode.', 'url' => $r['saas_setup'].'#webserver', 'category' => 'SaaS', 'keywords' => 'webserver nginx apache'],
            ['page' => 'SaaS Setup', 'section' => 'Stripe Integration', 'description' => 'Configure Stripe for SaaS subscriptions.', 'url' => $r['saas_setup'].'#stripe', 'category' => 'SaaS', 'keywords' => 'stripe payment subscription'],
            ['page' => 'SaaS Setup', 'section' => 'Example', 'description' => 'Complete SaaS setup example.', 'url' => $r['saas_setup'].'#example', 'category' => 'SaaS', 'keywords' => 'example tutorial walkthrough'],
            ['page' => 'SaaS Setup', 'section' => 'Verification', 'description' => 'Verify your SaaS setup is working.', 'url' => $r['saas_setup'].'#verification', 'category' => 'SaaS', 'keywords' => 'verify test check'],
            ['page' => 'SaaS Setup', 'section' => 'Demo Account', 'description' => 'Create a demo account for testing.', 'url' => $r['saas_setup'].'#demo', 'category' => 'SaaS', 'keywords' => 'demo test account sample'],
            ['page' => 'SaaS Setup', 'section' => 'Troubleshooting', 'description' => 'Fix common SaaS setup issues.', 'url' => $r['saas_setup'].'#troubleshooting', 'category' => 'SaaS', 'keywords' => 'troubleshoot fix problem'],
            ['page' => 'SaaS Setup', 'section' => 'Related Documentation', 'description' => 'Links to related SaaS documentation.', 'url' => $r['saas_setup'].'#related', 'category' => 'SaaS', 'keywords' => ''],
            ['page' => 'SaaS Setup', 'section' => 'Security', 'description' => 'Security considerations for SaaS deployments.', 'url' => $r['saas_setup'].'#security', 'category' => 'SaaS', 'keywords' => 'security ssl https'],

            // Custom Domains (SaaS)
            ['page' => 'Custom Domains', 'section' => 'Overview', 'description' => 'Allow users to use custom domains.', 'url' => $r['saas_custom_domains'].'#overview', 'category' => 'SaaS', 'keywords' => 'custom domain branded url'],
            ['page' => 'Custom Domains', 'section' => 'Prerequisites', 'description' => 'DigitalOcean requirements for custom domains.', 'url' => $r['saas_custom_domains'].'#prerequisites', 'category' => 'SaaS', 'keywords' => 'digitalocean requirements'],
            ['page' => 'Custom Domains', 'section' => 'Environment Setup', 'description' => 'Configure DigitalOcean API for domains.', 'url' => $r['saas_custom_domains'].'#environment', 'category' => 'SaaS', 'keywords' => 'env configuration digitalocean api'],
            ['page' => 'Custom Domains', 'section' => 'How It Works', 'description' => 'Redirect vs Direct domain modes.', 'url' => $r['saas_custom_domains'].'#how-it-works', 'category' => 'SaaS', 'keywords' => 'redirect direct mode proxy'],
            ['page' => 'Custom Domains', 'section' => 'DNS Setup for Customers', 'description' => 'Customer CNAME configuration instructions.', 'url' => $r['saas_custom_domains'].'#dns-setup', 'category' => 'SaaS', 'keywords' => 'dns cname configuration'],
            ['page' => 'Custom Domains', 'section' => 'Admin Management', 'description' => 'Domain management dashboard for admins.', 'url' => $r['saas_custom_domains'].'#admin-management', 'category' => 'SaaS', 'keywords' => 'admin manage dashboard'],
            ['page' => 'Custom Domains', 'section' => 'Troubleshooting', 'description' => 'Fix custom domain issues.', 'url' => $r['saas_custom_domains'].'#troubleshooting', 'category' => 'SaaS', 'keywords' => 'troubleshoot fix dns ssl'],

            // Twilio (SaaS)
            ['page' => 'Twilio Integration', 'section' => 'Overview', 'description' => 'Phone verification and WhatsApp for SaaS.', 'url' => $r['saas_twilio'].'#overview', 'category' => 'SaaS', 'keywords' => 'twilio phone sms whatsapp'],
            ['page' => 'Twilio Integration', 'section' => 'Create a Twilio Account', 'description' => 'Set up a Twilio account for messaging.', 'url' => $r['saas_twilio'].'#create-account', 'category' => 'SaaS', 'keywords' => 'twilio account setup register'],
            ['page' => 'Twilio Integration', 'section' => 'Environment Setup', 'description' => 'Configure Twilio environment variables.', 'url' => $r['saas_twilio'].'#environment', 'category' => 'SaaS', 'keywords' => 'env configuration twilio'],
            ['page' => 'Twilio Integration', 'section' => 'Phone Verification', 'description' => 'Implement phone number verification.', 'url' => $r['saas_twilio'].'#phone-verification', 'category' => 'SaaS', 'keywords' => 'phone verify number sms'],
            ['page' => 'Twilio Integration', 'section' => 'WhatsApp Setup', 'description' => 'Register and configure WhatsApp messaging.', 'url' => $r['saas_twilio'].'#whatsapp', 'category' => 'SaaS', 'keywords' => 'whatsapp messaging sender'],
            ['page' => 'Twilio Integration', 'section' => 'Testing', 'description' => 'Test SMS and WhatsApp functionality.', 'url' => $r['saas_twilio'].'#testing', 'category' => 'SaaS', 'keywords' => 'test sms whatsapp verify'],

            // ===== DEVELOPER =====

            // API Reference
            ['page' => 'API Reference', 'section' => 'Authentication', 'description' => 'API key authentication for REST endpoints.', 'url' => $r['developer_api'].'#authentication', 'category' => 'Developer', 'keywords' => 'api key auth token bearer'],
            ['page' => 'API Reference', 'section' => 'Rate Limits', 'description' => 'API rate limiting and quotas.', 'url' => $r['developer_api'].'#rate-limits', 'category' => 'Developer', 'keywords' => 'rate limit throttle quota'],
            ['page' => 'API Reference', 'section' => 'Response Format', 'description' => 'JSON response structure and conventions.', 'url' => $r['developer_api'].'#response-format', 'category' => 'Developer', 'keywords' => 'json response format structure'],
            ['page' => 'API Reference', 'section' => 'Pagination', 'description' => 'Paginate through list endpoints.', 'url' => $r['developer_api'].'#pagination', 'category' => 'Developer', 'keywords' => 'pagination page per_page cursor'],
            ['page' => 'API Reference', 'section' => 'Register', 'description' => 'User registration endpoint.', 'url' => $r['developer_api'].'#register', 'category' => 'Developer', 'keywords' => 'register user create account'],
            ['page' => 'API Reference', 'section' => 'Login', 'description' => 'User login endpoint.', 'url' => $r['developer_api'].'#login', 'category' => 'Developer', 'keywords' => 'login authenticate session'],
            ['page' => 'API Reference', 'section' => 'List Schedules', 'description' => 'GET /api/schedules - List all schedules.', 'url' => $r['developer_api'].'#list-schedules', 'category' => 'Developer', 'keywords' => 'list schedules get index'],
            ['page' => 'API Reference', 'section' => 'Show Schedule', 'description' => 'GET /api/schedules/{id} - Get schedule details.', 'url' => $r['developer_api'].'#show-schedule', 'category' => 'Developer', 'keywords' => 'show schedule get detail'],
            ['page' => 'API Reference', 'section' => 'Create Schedule', 'description' => 'POST /api/schedules - Create a new schedule.', 'url' => $r['developer_api'].'#create-schedule', 'category' => 'Developer', 'keywords' => 'create schedule post new'],
            ['page' => 'API Reference', 'section' => 'Update Schedule', 'description' => 'PUT /api/schedules/{id} - Update a schedule.', 'url' => $r['developer_api'].'#update-schedule', 'category' => 'Developer', 'keywords' => 'update schedule put modify'],
            ['page' => 'API Reference', 'section' => 'Delete Schedule', 'description' => 'DELETE /api/schedules/{id} - Delete a schedule.', 'url' => $r['developer_api'].'#delete-schedule', 'category' => 'Developer', 'keywords' => 'delete schedule remove destroy'],
            ['page' => 'API Reference', 'section' => 'List Sub-Schedules', 'description' => 'GET /api/schedules/groups - List sub-schedules.', 'url' => $r['developer_api'].'#list-groups', 'category' => 'Developer', 'keywords' => 'list groups sub-schedules get'],
            ['page' => 'API Reference', 'section' => 'Create Sub-Schedule', 'description' => 'POST /api/schedules/groups - Create a sub-schedule.', 'url' => $r['developer_api'].'#create-group', 'category' => 'Developer', 'keywords' => 'create group sub-schedule post'],
            ['page' => 'API Reference', 'section' => 'Update Sub-Schedule', 'description' => 'PUT /api/schedules/groups/{id} - Update a sub-schedule.', 'url' => $r['developer_api'].'#update-group', 'category' => 'Developer', 'keywords' => 'update group sub-schedule put'],
            ['page' => 'API Reference', 'section' => 'Delete Sub-Schedule', 'description' => 'DELETE /api/schedules/groups/{id} - Delete a sub-schedule.', 'url' => $r['developer_api'].'#delete-group', 'category' => 'Developer', 'keywords' => 'delete group sub-schedule remove'],
            ['page' => 'API Reference', 'section' => 'List Events', 'description' => 'GET /api/events - List all events.', 'url' => $r['developer_api'].'#list-events', 'category' => 'Developer', 'keywords' => 'list events get index'],
            ['page' => 'API Reference', 'section' => 'Show Event', 'description' => 'GET /api/events/{id} - Get event details.', 'url' => $r['developer_api'].'#show-event', 'category' => 'Developer', 'keywords' => 'show event get detail'],
            ['page' => 'API Reference', 'section' => 'Create Event', 'description' => 'POST /api/events - Create a new event.', 'url' => $r['developer_api'].'#create-event', 'category' => 'Developer', 'keywords' => 'create event post new'],
            ['page' => 'API Reference', 'section' => 'Update Event', 'description' => 'PUT /api/events/{id} - Update an event.', 'url' => $r['developer_api'].'#update-event', 'category' => 'Developer', 'keywords' => 'update event put modify'],
            ['page' => 'API Reference', 'section' => 'Delete Event', 'description' => 'DELETE /api/events/{id} - Delete an event.', 'url' => $r['developer_api'].'#delete-event', 'category' => 'Developer', 'keywords' => 'delete event remove destroy'],
            ['page' => 'API Reference', 'section' => 'Upload Flyer', 'description' => 'POST /api/events/flyer - Upload an event flyer image.', 'url' => $r['developer_api'].'#upload-flyer', 'category' => 'Developer', 'keywords' => 'upload flyer image poster'],
            ['page' => 'API Reference', 'section' => 'List Categories', 'description' => 'GET /api/categories - List event categories.', 'url' => $r['developer_api'].'#list-categories', 'category' => 'Developer', 'keywords' => 'list categories get'],
            ['page' => 'API Reference', 'section' => 'List Sales', 'description' => 'GET /api/sales - List ticket sales.', 'url' => $r['developer_api'].'#list-sales', 'category' => 'Developer', 'keywords' => 'list sales tickets get'],
            ['page' => 'API Reference', 'section' => 'Show Sale', 'description' => 'GET /api/sales/{id} - Get sale details.', 'url' => $r['developer_api'].'#show-sale', 'category' => 'Developer', 'keywords' => 'show sale get detail'],
            ['page' => 'API Reference', 'section' => 'Create Sale', 'description' => 'POST /api/sales - Create a new sale.', 'url' => $r['developer_api'].'#create-sale', 'category' => 'Developer', 'keywords' => 'create sale post new'],
            ['page' => 'API Reference', 'section' => 'Update Sale Status', 'description' => 'PUT /api/sales/{id} - Update a sale status.', 'url' => $r['developer_api'].'#update-sale', 'category' => 'Developer', 'keywords' => 'update sale put status'],
            ['page' => 'API Reference', 'section' => 'Delete Sale', 'description' => 'DELETE /api/sales/{id} - Delete a sale.', 'url' => $r['developer_api'].'#delete-sale', 'category' => 'Developer', 'keywords' => 'delete sale remove'],
            ['page' => 'API Reference', 'section' => 'Error Handling', 'description' => 'HTTP status codes and error responses.', 'url' => $r['developer_api'].'#error-handling', 'category' => 'Developer', 'keywords' => 'error status code http'],

            // Webhooks (Developer)
            ['page' => 'Webhooks', 'section' => 'Overview', 'description' => 'Real-time notifications for events via HTTP callbacks.', 'url' => $r['developer_webhooks'].'#overview', 'category' => 'Developer', 'keywords' => 'webhooks callback http notifications'],
            ['page' => 'Webhooks', 'section' => 'Setup', 'description' => 'Configure webhook endpoints and event types.', 'url' => $r['developer_webhooks'].'#setup', 'category' => 'Developer', 'keywords' => 'setup configure endpoint url'],
            ['page' => 'Webhooks', 'section' => 'Event Types', 'description' => 'Available webhook event types to subscribe to.', 'url' => $r['developer_webhooks'].'#event-types', 'category' => 'Developer', 'keywords' => 'event types subscribe trigger'],
            ['page' => 'Webhooks', 'section' => 'Payload Format', 'description' => 'Webhook payload JSON structure.', 'url' => $r['developer_webhooks'].'#payload', 'category' => 'Developer', 'keywords' => 'payload json data format'],
            ['page' => 'Webhooks', 'section' => 'Request Headers', 'description' => 'HTTP headers sent with webhook requests.', 'url' => $r['developer_webhooks'].'#headers', 'category' => 'Developer', 'keywords' => 'headers http request'],
            ['page' => 'Webhooks', 'section' => 'Signature Verification', 'description' => 'Verify webhooks with HMAC-SHA256 signatures.', 'url' => $r['developer_webhooks'].'#verification', 'category' => 'Developer', 'keywords' => 'signature hmac sha256 verify security'],
            ['page' => 'Webhooks', 'section' => 'Best Practices', 'description' => 'Best practices for reliable webhook handling.', 'url' => $r['developer_webhooks'].'#best-practices', 'category' => 'Developer', 'keywords' => 'best practices reliability idempotent'],
            ['page' => 'Webhooks', 'section' => 'Testing', 'description' => 'Test webhook endpoints and payloads.', 'url' => $r['developer_webhooks'].'#testing', 'category' => 'Developer', 'keywords' => 'test debug webhook'],

            // ===== REFERRAL PROGRAM =====
            ['page' => 'Referral Program', 'section' => 'Overview', 'description' => 'Earn free months by referring other event organizers.', 'url' => $r['referral_program'].'#overview', 'category' => 'User Guide', 'keywords' => 'referral refer earn free credit discount'],
            ['page' => 'Referral Program', 'section' => 'How It Works', 'description' => 'Share your link, they subscribe, earn your credit.', 'url' => $r['referral_program'].'#how-it-works', 'category' => 'User Guide', 'keywords' => 'referral steps share link subscribe'],
            ['page' => 'Referral Program', 'section' => 'Your Referral Link', 'description' => 'Find and share your unique referral link.', 'url' => $r['referral_program'].'#referral-link', 'category' => 'User Guide', 'keywords' => 'referral link copy share'],
            ['page' => 'Referral Program', 'section' => 'Rewards', 'description' => 'Pro ($5) and Enterprise ($15) referral credits.', 'url' => $r['referral_program'].'#rewards', 'category' => 'User Guide', 'keywords' => 'referral reward credit pro enterprise free month'],
            ['page' => 'Referral Program', 'section' => 'Applying Credits', 'description' => 'Apply qualified referral credits to your schedules.', 'url' => $r['referral_program'].'#applying-credits', 'category' => 'User Guide', 'keywords' => 'apply credit schedule referral'],
            ['page' => 'Referral Program', 'section' => 'Referral Statuses', 'description' => 'Pending, Subscribed, Qualified, Credited, and Expired statuses.', 'url' => $r['referral_program'].'#statuses', 'category' => 'User Guide', 'keywords' => 'referral status pending subscribed qualified credited expired'],
            ['page' => 'Referral Program', 'section' => 'Referral History', 'description' => 'View all your referrals and their current status.', 'url' => $r['referral_program'].'#history', 'category' => 'User Guide', 'keywords' => 'referral history table list'],
        ];
    }
}
