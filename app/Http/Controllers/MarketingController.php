<?php

namespace App\Http\Controllers;

use App\Http\Middleware\TrackMarketingVisit;
use App\Models\DiscoveryHiddenSchedule;
use App\Models\Event;
use App\Models\FederatedEvent;
use App\Models\FederationClicksDaily;
use App\Models\PageView;
use App\Models\Role;
use App\Services\AuditService;
use App\Services\Wallet\AppleWalletService;
use App\Services\Wallet\GoogleWalletService;
use App\Utils\AdminReauthUtils;
use App\Utils\BrowseWall;
use App\Utils\DiscoveryUtils;
use App\Utils\DocsUtils;
use App\Utils\ExampleSchedules;
use App\Utils\GitHubUtils;
use App\Utils\HeroExperiment;
use App\Utils\HomeQuotes;
use App\Utils\MoneyUtils;
use App\Utils\PlatformPricing;
use App\Utils\TicketFees;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MarketingController extends Controller
{
    /**
     * A plan's monthly price, for the comparison and replacement copy built in this file.
     *
     * The views get $proMonthly/$entMonthly from AppServiceProvider's composer, but these pages
     * assemble their tables, FAQs and JSON-LD in PHP arrays here, where that composer has not run
     * yet - which is how ~94 quotes of the old $5/mo survived a price change and shipped beside
     * an offers block already emitting the new one. Same source, read directly.
     *
     * Returns a float: the amounts are admin-settable to two decimal places, and the (int) cast
     * this used to carry would quote 14.50 as 14. plan_price() formats either shape.
     */
    private function planPrice(bool $enterprise = false): float
    {
        return PlatformPricing::amount($enterprise ? 'enterprise' : 'pro', 'monthly');
    }

    /**
     * The length of the free trial, in days, as the installation sets it (TRIAL_DAYS), for the
     * same tables and FAQs. They used to type a week, whatever it was set to.
     */
    private function trialDays(): int
    {
        return (int) config('app.trial_days', 7);
    }

    /**
     * One month of ticket sales priced on each platform, for a comparison page's "fees, worked
     * out" table. The totals are worked out here from each platform's published rates rather than
     * typed, so a changed rate or plan price cannot leave a sum behind. Every ticket is its own
     * order, which is what makes a processor's fixed fee count once per ticket.
     *
     * A row: name, charges (the sentence shown), platform_percent, platform_fixed (per ticket),
     * processing_percent, processing_fixed (per order), monthly (subscription), ours.
     */
    private function feeExample(int $tickets, float $price, array $rows): array
    {
        $sales = $tickets * $price;

        return array_map(function (array $row) use ($tickets, $price, $sales) {
            $total = $tickets * ($price * ($row['platform_percent'] ?? 0) + ($row['platform_fixed'] ?? 0))
                + $sales * ($row['processing_percent'] ?? 0)
                + $tickets * ($row['processing_fixed'] ?? 0)
                + ($row['monthly'] ?? 0);
            $total = round($total, 2);

            return [
                'name' => $row['name'],
                'charges' => $row['charges'],
                // Our row includes the plan price, which is in the installation's currency.
                'total' => ! empty($row['ours']) ? plan_price($total) : '$'.number_format($total, fmod($total, 1) ? 2 : 0),
                'ours' => ! empty($row['ours']),
            ];
        }, $rows);
    }

    /**
     * A feeExample() row for a platform whose published rates TicketFees::rates() records, so a
     * comparison page's worked month and /ticket-fee-calculator cannot quote different rates.
     * A platform that runs the payment itself ('stripe' => false) carries its own processing
     * fee; one that settles into the organizer's own account pays Stripe's on top.
     */
    private function ratesFeeRow(string $key, ?string $name = null): array
    {
        $rates = TicketFees::rates();
        $rate = $rates[$key];
        $stripe = $rates['stripe'];
        $ownProcessing = ($rate['stripe'] ?? true) === false;

        return [
            'name' => $name ?? $rate['name'],
            'charges' => $rate['label'].($ownProcessing ? '' : ', and Stripe\'s own fee'),
            'platform_percent' => $rate['percent'] ?? 0,
            'platform_fixed' => $rate['fixed'] ?? 0,
            'processing_percent' => $ownProcessing ? $rate['processing'] : $stripe['percent'],
            'processing_fixed' => $ownProcessing ? 0 : $stripe['fixed'],
        ];
    }

    /**
     * Our own row of a worked month: no platform fee, Stripe's fee, and the Pro subscription.
     */
    private function ourFeeRow(): array
    {
        $stripe = TicketFees::rates()['stripe'];

        return [
            'name' => 'Event Schedule Pro',
            'charges' => '0% platform fee, Stripe\'s own fee, and Pro at '.plan_price($this->planPrice()).'/mo',
            'processing_percent' => $stripe['percent'],
            'processing_fixed' => $stripe['fixed'],
            'monthly' => $this->planPrice(),
            'ours' => true,
        ];
    }

    /**
     * What one ticket costs its seller in fees on a row, platform and processing together.
     */
    private function feePerTicket(array $row, float $price): float
    {
        return $price * (($row['platform_percent'] ?? 0) + ($row['processing_percent'] ?? 0))
            + ($row['platform_fixed'] ?? 0) + ($row['processing_fixed'] ?? 0);
    }

    /**
     * How many tickets at $price a month take before Pro costs less than the platform's
     * per-ticket fees: past this many, the row with the subscription is the cheaper one.
     */
    private function feeBreakEven(array $theirs, float $price): int
    {
        $ours = $this->ourFeeRow();
        $saved = $this->feePerTicket($theirs, $price) - $this->feePerTicket($ours, $price);

        return $saved > 0 ? (int) floor($this->planPrice() / $saved) : 0;
    }

    /**
     * Home page
     */
    public function index()
    {
        return view('marketing.index', [
            'personas' => $this->getPersonas(),
            'steps' => $this->getSteps(),
            'discoverEvents' => $this->discoverWallEvents(),
            'hero' => HeroExperiment::forPage(),
            // The operator's quotes, nexus only: the homepage elsewhere is a selfhost's own.
            'homeQuotes' => config('app.is_nexus') ? HomeQuotes::forPage() : [],
        ]);
    }

    /**
     * The events behind the homepage poster wall, the mobile strip and the discover rail.
     *
     * Same visibility rules as /browse: only events whose card shows a real image (own flyer, or
     * a talent/venue schedule's profile photo), so the wall is guaranteed visually rich posters.
     *
     * ONE event for each schedule, its soonest, and no more (DiscoveryUtils::onePerSchedule()),
     * as on /browse (BrowseWall). The wall, the phone strip, the rail and the structured list all
     * read this one collection, so the rule holds for all four. The wall above the fold is 25
     * pictures with no names under them, and a schedule's second poster is very often its
     * profile photo again. Until 2026-10 a schedule's other events followed once every schedule
     * had had its turn, so the list was never short: with sixteen schedules for the 25 places,
     * one of them was on the wall five times. A short list is this page's honest state now. The
     * wall fills the places left with its demo flyers, and the rail drops its pinned scroll
     * below four.
     *
     * Cached for `marketing.wall_cache_seconds`: this is five correlated subqueries plus an
     * excludeLikelyTest() regex pass, run on the single most-hit page on the site, and its answer
     * only moves when somebody publishes an event. Keyed on the app URL so a white-label nexus
     * install cannot be served another install's wall, and busted by forgetWallCache() from
     * Event's saved/deleted hooks - the admin Hide/Unhide button is rendered ON this page, so a
     * stale wall would contradict its own flash message and flip the event back on a re-click.
     *
     * A TTL of 0 (the test default) skips the cache entirely.
     */
    private function discoverWallEvents()
    {
        $limit = 25;
        $perSchedule = 1;

        // Chosen INSIDE the closure so the cached collection is already one per schedule, and
        // so the cache holds at most $limit models rather than the whole candidate pool.
        $build = fn () => DiscoveryUtils::onePerSchedule(
            DiscoveryUtils::candidates(
                $this->publicUpcomingEventsQuery()
                    ->where('is_hidden_from_discovery', false)
                    ->where(function ($sub) {
                        $sub->where(function ($f) {
                            $f->whereNotNull('flyer_image_url')
                                ->where('flyer_image_url', '!=', '');
                        })
                            ->orWhereHas('roles', function ($r) {
                                $r->whereIn('roles.type', ['talent', 'venue'])
                                    ->whereNotNull('roles.profile_image_url')
                                    ->where('roles.profile_image_url', '!=', '');
                            });
                    })
                    ->orderByRaw('CASE WHEN starts_at >= ? THEN 0 ELSE 1 END, starts_at IS NULL, starts_at ASC, events.id ASC', [Carbon::today()]),
                $limit,
                $perSchedule
            ),
            $limit
        );

        $ttl = (int) config('marketing.wall_cache_seconds');

        if ($ttl <= 0) {
            return $build();
        }

        return Cache::remember(self::wallCacheKey(), $ttl, $build);
    }

    /**
     * The cache key discoverWallEvents() reads and writes.
     *
     * Per app URL: a white-label nexus install shares this process's cache store on the hosted
     * deploy and must never be served another install's wall.
     */
    public static function wallCacheKey(): string
    {
        return 'marketing.wall.'.md5((string) config('app.url'));
    }

    /** The cache key browseWall() reads and writes: which events /browse's wall is chosen from. */
    public static function browseCacheKey(): string
    {
        return self::wallCacheKey().'.browse';
    }

    /**
     * Drop the cached homepage wall, and what /browse's wall is chosen from.
     *
     * Called from Event's saved/deleted hooks (see Event::WALL_CACHE_FIELDS) and from the admin
     * discovery toggle. Two Cache::forgets, so it is cheap enough to run on every qualifying
     * save; the next hit on each page pays for its rebuild.
     */
    public static function forgetWallCache(): void
    {
        Cache::forget(self::wallCacheKey());
        Cache::forget(self::browseCacheKey());
    }

    /**
     * Features page
     */
    public function features()
    {
        return view('marketing.features');
    }

    /**
     * Pricing page
     */
    public function pricing()
    {
        return view('marketing.pricing');
    }

    /**
     * Referral Program docs page
     */
    public function docsReferralProgram()
    {
        return view('marketing.docs.referral-program');
    }

    public function about()
    {
        return view('marketing.about', [
            'githubStars' => GitHubUtils::getStars(),
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
     * Outlook / Microsoft Calendar page
     */
    public function outlookCalendar()
    {
        return view('marketing.outlook-calendar');
    }

    /**
     * Gift Cards page
     */
    public function giftCards()
    {
        return view('marketing.gift-cards');
    }

    /**
     * Allocated Seating page
     */
    public function allocatedSeating()
    {
        return view('marketing.allocated-seating');
    }

    /**
     * Passes & Subscriptions page
     */
    public function passes()
    {
        return view('marketing.passes');
    }

    /**
     * Check-in Dashboard page
     */
    public function checkIn()
    {
        return view('marketing.check-in');
    }

    /**
     * PayPal page
     */
    public function paypal()
    {
        return view('marketing.paypal');
    }

    /**
     * Promo Codes & Add-ons page
     */
    public function promoCodes()
    {
        return view('marketing.promo-codes');
    }

    /**
     * Ticket Waitlist page
     */
    public function waitlist()
    {
        return view('marketing.waitlist');
    }

    /**
     * Free Event Registration and RSVP page
     */
    public function registration()
    {
        return view('marketing.registration');
    }

    /**
     * Booking Request Form page
     */
    public function bookingRequests()
    {
        return view('marketing.booking-requests');
    }

    /**
     * Event Lineup page: the lineup, pages for the acts listed, the logo wall and sponsors
     */
    public function lineup()
    {
        return view('marketing.lineup');
    }

    /**
     * Community Event Calendar page
     */
    public function communityEventCalendar()
    {
        return view('marketing.community-event-calendar');
    }

    /**
     * Event Landing Page page
     */
    public function eventLandingPage()
    {
        return view('marketing.event-landing-page');
    }

    /**
     * WordPress Event Calendar page
     */
    public function wordpressEventCalendar()
    {
        return view('marketing.wordpress-event-calendar');
    }

    /**
     * Installment Payments page
     */
    public function installments()
    {
        return view('marketing.installments');
    }

    /**
     * Move from Eventbrite page
     */
    public function switchFromEventbrite()
    {
        return view('marketing.switch-from-eventbrite');
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
     * Stackapella page: Event Schedule beside Stackapella, for voice teachers and choirs
     */
    public function stackapella()
    {
        return view('marketing.stackapella');
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
     * Appointment booking page
     */
    public function appointments()
    {
        return view('marketing.appointments');
    }

    /**
     * Carpool page
     */
    public function carpool()
    {
        return view('marketing.carpool');
    }

    /**
     * Custom Fields page
     */
    public function customFields()
    {
        return view('marketing.custom-fields');
    }

    /**
     * Custom Labels page
     */
    public function customLabels()
    {
        return view('marketing.custom-labels');
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
            'githubStars' => GitHubUtils::getStars(),
        ]);
    }

    /**
     * For Talent page
     */
    public function forTalent()
    {
        return view('marketing.for-talent', [
            // Real upcoming shows from talent schedules, used as the page's proof
            // rail. Same visibility rules as /browse and the homepage: only events
            // whose card resolves to a real image, so no placeholder ever ships.
            // The section hides itself entirely when this comes back empty - it is
            // never padded with demo flyers, which would read as fake proof. Spread
            // across schedules for the same reason: a rail of one schedule's dates
            // proves less than a rail of several. Note the quota is keyed on the
            // schedule the card credits, which for an act playing a claimed venue can
            // be the VENUE, so this spreads rooms as readily as acts. The view wants 8
            // or 4 exactly, so the spread has to reorder rather than drop or the rail
            // can fall through a threshold.
            'talentEvents' => DiscoveryUtils::spread(
                DiscoveryUtils::candidates(
                    $this->publicUpcomingEventsQuery()
                        ->where('is_hidden_from_discovery', false)
                        ->whereHas('roles', function ($r) {
                            $r->where('roles.type', 'talent');
                        })
                        ->where(function ($sub) {
                            $sub->where(function ($f) {
                                $f->whereNotNull('flyer_image_url')
                                    ->where('flyer_image_url', '!=', '');
                            })
                                ->orWhereHas('roles', function ($r) {
                                    $r->where('roles.type', 'talent')
                                        ->whereNotNull('roles.profile_image_url')
                                        ->where('roles.profile_image_url', '!=', '');
                                });
                        })
                        ->orderByRaw('CASE WHEN starts_at >= ? THEN 0 ELSE 1 END, starts_at IS NULL, starts_at ASC, events.id ASC', [Carbon::today()]),
                    8
                ),
                8
            ),
        ]);
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
     * For Churches page
     */
    public function forChurches()
    {
        return view('marketing.for-churches');
    }

    /**
     * For Schools page
     */
    public function forSchools()
    {
        return view('marketing.for-schools');
    }

    /**
     * For Nonprofits page
     */
    public function forNonprofits()
    {
        return view('marketing.for-nonprofits');
    }

    /**
     * For Festivals page
     */
    public function forFestivals()
    {
        return view('marketing.for-festivals');
    }

    /**
     * For Sports Leagues page
     */
    public function forSportsLeagues()
    {
        return view('marketing.for-sports-leagues');
    }

    /**
     * For Museums page
     */
    public function forMuseums()
    {
        return view('marketing.for-museums');
    }

    /**
     * For Meetup Groups page
     */
    public function forMeetupGroups()
    {
        return view('marketing.for-meetup-groups');
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
        return view('marketing.compare', [
            'sections' => $this->getHubComparisonData(),
            'headToHead' => $this->getHubHeadToHead(),
            'rates' => TicketFees::rates(),
            // The open-source card renders the live star badge, which is a
            // no-op without this and leaves the card visibly empty.
            'githubStars' => GitHubUtils::getStars(),
        ]);
    }

    /**
     * Ticket Fee Calculator page
     */
    public function ticketFeeCalculator()
    {
        return view('marketing.ticket-fee-calculator');
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
     * Compare vs Timely page
     */
    public function compareTimely()
    {
        return view('marketing.compare-single', $this->getComparisonData('timely'));
    }

    /**
     * Compare vs The Events Calendar page
     */
    public function compareTheEventsCalendar()
    {
        return view('marketing.compare-single', $this->getComparisonData('the-events-calendar'));
    }

    /**
     * Compare vs Bandsintown page
     */
    public function compareBandsintown()
    {
        return view('marketing.compare-single', $this->getComparisonData('bandsintown'));
    }

    /**
     * Compare vs Posh page
     */
    public function comparePosh()
    {
        return view('marketing.compare-single', $this->getComparisonData('posh'));
    }

    /**
     * Compare vs Partiful page
     */
    public function comparePartiful()
    {
        return view('marketing.compare-single', $this->getComparisonData('partiful'));
    }

    /**
     * Compare vs Facebook Events page
     */
    public function compareFacebookEvents()
    {
        return view('marketing.compare-single', $this->getComparisonData('facebook-events'));
    }

    /**
     * Compare vs Zeffy page
     */
    public function compareZeffy()
    {
        return view('marketing.compare-single', $this->getComparisonData('zeffy'));
    }

    /**
     * Compare vs Tockify page
     */
    public function compareTockify()
    {
        return view('marketing.compare-single', $this->getComparisonData('tockify'));
    }

    /**
     * Compare vs Hi.Events page
     */
    public function compareHiEvents()
    {
        return view('marketing.compare-single', $this->getComparisonData('hi-events'));
    }

    /**
     * Compare vs Mobilizon page
     */
    public function compareMobilizon()
    {
        return view('marketing.compare-single', $this->getComparisonData('mobilizon'));
    }

    /**
     * Compare vs TicketLeap page
     */
    public function compareTicketLeap()
    {
        return view('marketing.compare-single', $this->getComparisonData('ticketleap'));
    }

    /**
     * Compare vs Songkick page
     */
    public function compareSongkick()
    {
        return view('marketing.compare-single', $this->getComparisonData('songkick'));
    }

    /**
     * Compare vs AllEvents page
     */
    public function compareAllEvents()
    {
        return view('marketing.compare-single', $this->getComparisonData('allevents'));
    }

    /**
     * Compare vs Universe page
     */
    public function compareUniverse()
    {
        return view('marketing.compare-single', $this->getComparisonData('universe'));
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
            'githubStars' => GitHubUtils::getStars(),
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
     * Public accessibility declaration (marketing site).
     */
    public function accessibility()
    {
        return view('marketing.accessibility');
    }

    /**
     * Selfhost documentation: accessibility obligations for operators.
     */
    public function docsSelfhostAccessibility()
    {
        return view('marketing.docs.selfhost.accessibility');
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
            'githubStars' => GitHubUtils::getStars(),
        ]);
    }

    /**
     * SaaS white-label page
     */
    public function saas()
    {
        return view('marketing.saas', [
            'githubStars' => GitHubUtils::getStars(),
        ]);
    }

    /**
     * Documentation index page
     */
    public function docsIndex()
    {
        // No searchIndex here on purpose: the widget fetches the cached
        // /docs/search-index.json endpoint instead of having 364 rows inlined
        // into the page. Passing it built those rows and made ~35 route() calls
        // on every request, uncached, only to throw the array away.
        return view('marketing.docs.index');
    }

    /**
     * The docs search index, served as JSON.
     *
     * 364 entries, ~97 KB raw. Inlining that into all 41 doc pages would add
     * ~18 KB gzipped to every one of them, so the search widget fetches this
     * on first focus instead and the browser caches it across the section.
     *
     * The payload is host-independent (rows carry the same URLs the index
     * always used), so it is safe to cache globally.
     */
    public function docsSearchIndex()
    {
        $index = Cache::remember(
            'docs.search-index.'.app()->getLocale(),
            now()->addDay(),
            function () {
                // Attach the manifest's icon key to each row so the results can
                // render <use href="#docs-icon-..."> against the page sprite.
                // The /docs search widget used to carry its own hand-written
                // 26-entry copy of the icon paths in JavaScript.
                // Keyed on the short nav_title as well as the title, lowercased:
                // search rows label the page the way a reader would say it
                // ("Sharing"), which is exactly what nav_title is for. Matching
                // on title alone silently fell back to the generic book icon.
                $icons = [];
                foreach (DocsUtils::pages() as $page) {
                    $icons[mb_strtolower($page['title'])] = $page['icon'];

                    if (! empty($page['nav_title'])) {
                        $icons[mb_strtolower($page['nav_title'])] = $page['icon'];
                    }
                }

                return array_map(function ($row) use ($icons) {
                    $row['icon'] = $icons[mb_strtolower($row['page'])] ?? 'book';

                    return $row;
                }, $this->getDocSearchIndex());
            }
        );

        return response()
            ->json($index)
            ->header('Cache-Control', 'public, max-age=3600');
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
        ]));
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
     * Subscriptions & passes documentation page
     */
    public function docsSubscriptions()
    {
        return view('marketing.docs.subscriptions');
    }

    /**
     * Gift cards documentation page
     */
    public function docsGiftCards()
    {
        return view('marketing.docs.gift-cards');
    }

    public function docsAllocatedSeating()
    {
        return view('marketing.docs.allocated-seating');
    }

    public function docsAppointments()
    {
        return view('marketing.docs.appointments');
    }

    /**
     * Newsletters documentation page
     */
    public function docsNewsletters()
    {
        return view('marketing.docs.newsletters');
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
        ]));
    }

    /**
     * Analytics documentation page
     */
    public function docsAnalytics()
    {
        return view('marketing.docs.analytics');
    }

    /**
     * Account Settings documentation page
     */
    public function docsAccountSettings()
    {
        return view('marketing.docs.account-settings');
    }

    /**
     * Managing Schedules documentation page
     */
    public function docsManagingSchedules()
    {
        return view('marketing.docs.managing-schedules');
    }

    /**
     * Boost documentation page
     */
    public function docsBoost()
    {
        return view('marketing.docs.boost');
    }

    /**
     * AI Import documentation page
     */
    public function docsAiImport()
    {
        return view('marketing.docs.ai-import');
    }

    /**
     * Scan Agenda documentation page
     */
    public function docsScanAgenda()
    {
        return view('marketing.docs.scan-agenda');
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

    public function docsSelfhostDocker()
    {
        return view('marketing.docs.selfhost.docker');
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
     * Facebook Login setup documentation
     */
    public function docsSaasFacebookLogin()
    {
        return view('marketing.docs.saas.facebook-login');
    }

    /**
     * Federation documentation.
     *
     * Mirrored across both docs trees because federation is available to any
     * non-nexus install, single-tenant selfhost and selfhosted SaaS alike. Both
     * pages share one body partial so they cannot drift apart.
     */
    public function docsSaasFederation()
    {
        return view('marketing.docs.saas.federation');
    }

    /**
     * Monetization documentation page (SaaS operators).
     */
    public function docsSaasMonetization()
    {
        return view('marketing.docs.saas.monetization');
    }

    public function docsSelfhostFederation()
    {
        return view('marketing.docs.selfhost.federation');
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
     * Google Wallet documentation page
     */
    public function docsSelfhostGoogleWallet()
    {
        return view('marketing.docs.selfhost.google-wallet');
    }

    /**
     * Apple Wallet documentation page
     */
    public function docsSelfhostAppleWallet()
    {
        return view('marketing.docs.selfhost.apple-wallet');
    }

    /**
     * Outlook / Microsoft Calendar documentation page
     */
    public function docsSelfhostMicrosoftCalendar()
    {
        return view('marketing.docs.selfhost.microsoft-calendar');
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
     * Get comparison data for the hub /compare page
     */
    private function getHubComparisonData(): array
    {
        return [
            'Pricing & Fees' => [
                ['Free plan', 'Yes (forever)', 'Free to publish events', 'Yes (forever)', 'Free events only', 'Yes (forever)'],
                ['Paid plan price', 'From '.plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'Free (fees on tickets)', '$59/mo', 'No subscription ($0.30-$0.85/ticket)', 'Free'],
                ['Platform fees', '0%', '3.7% + $1.79/ticket', '5% (free plan), 0% (Plus)', '$0.30-$0.85/ticket', 'N/A (no ticketing)'],
                ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay', 'Built-in (2.9% per order, on top)', 'Stripe (2.9% + $0.30)', 'Stripe/PayPal/Square', 'No'],
            ],
            'Events & Ticketing' => [
                ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', 'Yes', 'Yes', 'No'],
                ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', 'Yes', 'Yes', 'No'],
                ['QR check-ins', 'Yes (Free)', 'Yes', 'Yes', 'Yes', 'No'],
                ['Ticket waitlist', 'Yes (Pro)', 'Yes', 'Yes', 'Yes (email sign-ups)', 'No'],
                ['Check-in dashboard', 'Yes (Pro)', 'Yes', 'No', 'Yes', 'No'],
                ['Sale notifications', 'Yes (Pro)', 'Yes', 'Yes', 'Yes', 'No'],
                ['Sales data export', 'Yes (Pro)', 'Yes', 'Yes', 'Yes', 'No'],
                ['Online events', 'Yes (Free)', 'Yes', 'Yes', 'Yes', 'No'],
                ['Free event RSVP', 'Yes (Free)', 'Yes', 'Yes', 'Yes', 'No'],
                ['Recurring events', 'Yes (Free)', 'Yes', 'Via cloning', 'Yes', 'Yes'],
                ['Event polls', 'Yes (Pro)', 'No', 'No', 'No', 'No'],
                ['Promo/discount codes', 'Yes (Pro)', 'Yes', 'Yes', 'Yes', 'No'],
                ['Post-event feedback', 'Yes (Pro)', 'Yes', 'Yes', 'No', 'No'],
                ['Embed ticket widget', 'Yes (Pro)', 'Yes', 'No', 'Yes', 'No'],
                ['Individual tickets', 'Yes (Pro)', 'Yes', 'No', 'No', 'No'],
                // Competitor cells checked against each one's own help pages, 2026-09-10, and the whole table again on 2026-11-11.
                ['Refunds, full or partial', 'Yes (Pro)', 'Yes', 'Yes', 'Yes', 'No'],
                ['Reserved seating', 'Yes (Enterprise)', 'Yes', 'No', 'Yes', 'No'],
                ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes', 'Yes', 'Scheduled broadcasts', 'N/A'],
                ['Donations / pay what you want', 'Yes (Pro)', 'Yes', 'Yes', 'Yes', 'No'],
                ['Promoter links with commission', 'Yes (Pro)', 'Tracking only', 'Tracking only', 'Tracking only', 'No'],
            ],
            'Integrations' => [
                ['Calendar sync, both ways', 'Yes (Free)', 'No native 2-way sync', 'One-way iCal feed', 'No', 'N/A'],
                ['CalDAV sync', 'Yes (Free)', 'No', 'No', 'No', 'No'],
                ['Newsletters', 'Yes (Free)', 'Yes', 'Yes', 'No', 'No'],
                ['Embed calendar on website', 'Yes (Free)', 'Yes', 'Yes', 'Yes', 'Yes (limited styling)'],
            ],
            'Customization' => [
                ['Custom domains', 'Yes (Enterprise)', 'No', 'No (custom luma.com URL)', 'Yes ($8/mo add-on)', 'No'],
                ['Remove branding', 'Yes (Pro)', 'No', 'Yes (Plus)', 'Yes (paid)', 'No'],
                ['Custom fields', 'Yes (Pro)', 'Yes', 'Yes', 'Yes', 'No'],
                ['Built-in analytics', 'Yes (Free)', 'Yes', 'Yes', 'Yes', 'No'],
            ],
            'Unique Features' => [
                ['AI event parsing, agenda scanning & flyer generation', 'Yes (parsing on every plan, scanning & flyers Enterprise)', 'No', 'No', 'No', 'No'],
                ['Event graphics gen', 'Yes (Free)', 'No', 'No', 'No', 'No'],
                ['Sub-schedules', 'Yes (Free)', 'No', 'No', 'No', 'Multiple calendars'],
                ['Fan videos & comments', 'Yes (Free)', 'No', 'No', 'No', 'No'],
                ['Event boost (ads)', 'Yes (Pro)', 'Yes (paid)', 'No', 'No', 'No'],
                ['Team collaboration', '1 member (Free), multiple (Enterprise)', 'Yes', '3 admins (free), 5 (Plus)', 'Yes', 'Yes'],
                ['Private/password-protected events', 'Yes (Enterprise)', 'Yes (password)', 'Unlisted only', 'Yes (access code)', 'No'],
                ['Availability management', 'Yes (Enterprise)', 'No', 'No', 'No', 'No'],
                ['WhatsApp event creation', 'Yes (Enterprise)', 'No', 'No', 'No', 'No'],
                ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', 'No', 'No', 'No'],
                ['Carpool matching', 'Yes (Pro)', 'No', 'No', 'No', 'No'],
                ['Sponsor/partner logos', 'Yes (Pro)', 'No', 'No', 'No', 'No'],
            ],
            'Platform' => [
                ['REST API', 'Yes (Pro)', 'Yes', 'Yes (Plus)', 'Yes', 'Yes'],
                ['Webhooks', 'Yes (Pro)', 'Yes', 'Yes (Plus)', 'Yes', 'No'],
                ['Open source', 'Yes', 'No', 'No', 'No', 'No'],
                ['Selfhosting', 'Yes', 'No', 'No', 'No', 'No'],
            ],
        ];
    }

    /**
     * The order competitors appear in the /compare picker: roughly how often
     * people arrive already using them. The first eight are the ones shown
     * before the "show all" expander on small screens.
     */
    private const HUB_PICKER_ORDER = [
        'eventbrite', 'luma', 'ticket-tailor', 'google-calendar',
        'meetup', 'dice', 'humanitix', 'tito',
        'sched', 'whova', 'splash', 'accelevents',
        'brown-paper-tickets', 'addevent', 'pretix', 'eventzilla',
        'facebook-events', 'partiful', 'posh', 'bandsintown',
        'the-events-calendar', 'timely', 'tockify', 'zeffy',
        'hi-events', 'mobilizon', 'ticketleap', 'songkick',
        'allevents', 'universe',
    ];

    /**
     * Feature rows the /compare head-to-head prefers, highest signal first.
     * Nearly every competitor carries most of these labels, so in practice
     * almost all of them fill the six slots from this list alone.
     */
    private const HUB_HEAD_TO_HEAD_ROWS = [
        'Platform fees',
        'Paid plan price',
        'Free plan',
        'Ticketing',
        'Calendar sync, both ways',
        'Open source',
        'Selfhosting',
    ];

    /**
     * Compact head-to-head summaries for the /compare picker, one per
     * competitor, derived from the same getComparisonData() rows that drive
     * the detail pages so the hub and the detail page can never disagree.
     *
     * Deriving rather than restating also means no competitor fact is ever
     * authored twice. getComparisonData() is pinned byte-for-byte by
     * MarketingDataCharacterizationTest, so it is read here and never touched.
     *
     * Labels vary between competitors (Google Calendar has no ticketing rows
     * at all), so the preferred list above is applied first and any competitor
     * left short is topped up from its own rows, in their authored order.
     */
    private function getHubHeadToHead(): array
    {
        $out = [];

        foreach (self::HUB_PICKER_ORDER as $slug) {
            $data = $this->getComparisonData($slug);

            // Flatten the competitor's sections to label => row. Later
            // sections win on the rare duplicate label, which is harmless
            // because the values agree.
            $byLabel = [];
            foreach ($data['sections'] as $rows) {
                foreach ($rows as $row) {
                    $byLabel[$row[0]] = $row;
                }
            }

            $picked = [];
            foreach (self::HUB_HEAD_TO_HEAD_ROWS as $label) {
                if (count($picked) >= 6) {
                    break;
                }
                if (isset($byLabel[$label])) {
                    $picked[$label] = $byLabel[$label];
                }
            }
            foreach ($byLabel as $label => $row) {
                if (count($picked) >= 6) {
                    break;
                }
                $picked[$label] = $row;
            }

            $out[$slug] = [
                'name' => $data['name'],
                'slug' => $data['slug'] ?? $slug,
                'tagline' => $data['tagline'] ?? '',
                'route' => 'marketing.compare_'.str_replace('-', '_', $slug),
                'rows' => array_values(array_map(fn ($row) => [
                    'feature' => $row[0],
                    'ours' => $row[1],
                    'theirs' => $row[2],
                    'esWins' => (bool) ($row[3] ?? false),
                ], $picked)),
            ];
        }

        return $out;
    }

    /**
     * ", and buyers can also add it to Apple Wallet or Google Wallet", naming only the wallets this
     * install has configured (both are off until the operator sets them up), or nothing at all.
     */
    private function walletClause(): string
    {
        $wallets = array_keys(array_filter([
            'Apple Wallet' => AppleWalletService::isConfigured(),
            'Google Wallet' => GoogleWalletService::isConfigured(),
        ]));

        return $wallets ? ', and buyers can also add it to '.implode(' or ', $wallets) : '';
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
                'meta_title' => 'Eventbrite Alternative With 0% Platform Fees | Event Schedule',
                'tagline' => 'A simpler, more affordable alternative to Eventbrite with zero platform fees.',
                'description' => 'Compare Event Schedule with Eventbrite: zero platform fees instead of 3.7% + $1.79 a ticket, payouts to your own Stripe or PayPal account, and an importer.',
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
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'Free (fees on tickets); Eventbrite Pro email marketing from $15/mo', true],
                        ['Platform fees', '0%', '3.7% + $1.79/ticket', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Built-in (2.9% per order, on top)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'Yes', false],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'Yes (star ratings, public paid events in US, Canada and UK)', false],
                        ['Individual tickets', 'Yes (Pro)', 'Yes', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (48 hours before)', false],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'Tracking links, no commission', true],
                    ],
                    'Integrations' => [
                        ['Eventbrite auto-import', 'Yes (Pro)', 'N/A', true],
                        ['Calendar sync, both ways', 'Yes (Free)', 'No native 2-way sync', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Yes (250 a day free, more on Eventbrite Pro)', false],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'AI writes the description, summary and cover image', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'Yes (paid)', false],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Yes (password)', false],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'No', true],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
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
                        'title' => '0% Platform Fees',
                        'description' => 'Eventbrite charges 3.7% + $1.79 per ticket, plus 2.9% payment processing per order. With Event Schedule, you keep your revenue minus Stripe or PayPal processing.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Custom Domain',
                        'description' => 'Use your own domain for your event pages, on Enterprise. Eventbrite does not offer custom domain support at any price.',
                        'icon' => 'globe',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format, or upload a flyer, and the parser fills in the event for you to check. Fifty a day on the free and Pro plans.',
                        'icon' => 'ai',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with no vendor lock-in. Selfhost on your own server instead of depending on Eventbrite\'s platform and fee structure.',
                        'icon' => 'code',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google, Outlook and CalDAV sync included free. Eventbrite lacks native two-way calendar sync.',
                        'icon' => 'calendar',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Eventbrite Auto-Import',
                        'description' => 'Connect your Eventbrite account and import all your events in one click. Event details, tickets, venues, and images are transferred automatically. On Pro.',
                        'icon' => 'import',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
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
                'why_choose' => [
                    'summary' => 'Event Schedule sells, scans and refunds tickets without taking a platform fee on any of them, and adds what Eventbrite does not have: your own domain, open source code and two-way calendar sync.',
                    'points' => [
                        'Zero platform fees vs Eventbrite\'s 3.7% + $1.79 per ticket',
                        'The Eventbrite importer (Pro) brings your events across with their ticket types, venues and images',
                        'Your own domain (Enterprise) and no Event Schedule branding (Pro), which Eventbrite does not offer at any price',
                        'AI event parsing on every plan, and two-way sync with Google, Outlook and CalDAV',
                        'Stripe or PayPal checkout into your own account, one cart across several events, and refunds from the Sales page',
                        'Reserved seating with a seat map on Enterprise, and an interest list that emails people when tickets go on sale',
                    ],
                ],
                // Eventbrite is the one competitor with a real importer, so its
                // second step is the connector rather than the paste-and-parse
                // route the default steps describe.
                'switch_steps' => [
                    ['title' => 'Create your schedule', 'description' => 'Free, no card, and it has its own address the moment it exists.'],
                    ['title' => 'Connect Eventbrite', 'description' => 'Authorise the connection once and your events come across with their ticket types, venues and images. On Pro.'],
                    ['title' => 'Bring your list across', 'description' => 'Export your Eventbrite attendees and paste them in or upload a CSV, up to ten thousand rows, on any plan.'],
                    ['title' => 'Connect Stripe or PayPal and sell', 'description' => 'Payouts land in your own Stripe or PayPal account, with zero platform fees instead of 3.7% + $1.79 a ticket.'],
                ],
                // Eventbrite's rates from TicketFees::rates() (eventbrite.com/organizer/pricing, checked
                // 2026-09-24), the same reader /ticket-fee-calculator uses, so the two cannot disagree.
                'fee_example' => [
                    'tag' => 'Eventbrite fees, worked out',
                    'title' => 'What 100 tickets at $20 cost you on Eventbrite',
                    'lead' => 'Eventbrite charges a 3.7% + $1.79 service fee on each paid ticket and a separate 2.9% payment processing fee on each order, with no cap on either. Buyers pay both on top by default. The table shows them absorbed, which is what it costs to keep the price you advertise the price people pay.',
                    'total_label' => 'Fees on $2,000',
                    'caption' => 'Fees on 100 tickets at $20, each bought in its own order, on Event Schedule Pro, Eventbrite and Luma Free.',
                    'rows' => $this->feeExample(100, 20, [$this->ourFeeRow(), $this->ratesFeeRow('eventbrite'), ['name' => 'Luma Free', 'charges' => '5% platform fee, and Stripe\'s own fee', 'platform_percent' => 0.05, 'processing_percent' => 0.029, 'processing_fixed' => 0.30]]),
                    'notes' => [
                        'On a $20 ticket Eventbrite\'s two fees come to $'.number_format($this->feePerTicket($this->ratesFeeRow('eventbrite'), 20), 2).', against $'.number_format($this->feePerTicket($this->ourFeeRow(), 20), 2).' in Stripe fees on Event Schedule, so Pro costs less once a month passes '.$this->feeBreakEven($this->ratesFeeRow('eventbrite'), 20).' paid tickets.',
                        'A free event carries no Eventbrite fee, and free registration is unlimited on Event Schedule\'s free plan, so a month of free events costs nothing on either.',
                        'These are Eventbrite\'s published US rates as checked in September 2026. Each ticket is counted as its own order, the dearest case for any fee charged per payment.',
                    ],
                ],
                'limits' => [
                    ['tag' => 'Eventbrite\'s buyers stay there', 'title' => 'Nobody browses in for you', 'text' => 'Eventbrite\'s marketplace sends its own buyers to an event, and that audience does not come across with your listings. The people who turn up here are the ones you bring: your schedule\'s followers, the subscribers who get its digest of new events, and visitors to the calendar you embed on your own site.'],
                    ['tag' => 'Bring a payment account', 'title' => 'Eventbrite processes, we do not', 'text' => 'Eventbrite takes the card payment itself, so selling there needs no account of your own. Here the money is taken by a Stripe, PayPal, Payfast or Razorpay account in your name, connected once in Settings, and that processor\'s fee and payout timing apply.'],
                    ['tag' => 'Pro for priced tickets', 'title' => 'A monthly plan, sales or not', 'text' => 'Publishing on Eventbrite costs nothing until a ticket sells, because its fee comes out of each sale. Here a ticket with a price needs the Pro plan, which costs '.plan_price($this->planPrice()).'/mo even in a month with no sales, while free registration stays unlimited on the free plan.'],
                ],
                'faq' => [
                    ['question' => 'Can I import my existing Eventbrite events?', 'answer' => 'Yes. With the Pro plan, you can connect your Eventbrite account and import your events in bulk. Event details, ticket types, venues, and images are all transferred automatically.'],
                    ['question' => 'Is it easy to switch from Eventbrite to Event Schedule?', 'answer' => 'Yes. There is no need to migrate your Eventbrite history. Create a free schedule, add your upcoming events (or use AI import to paste and parse them), and share your new schedule URL with your audience.'],
                    ['question' => 'How does Event Schedule pricing compare to Eventbrite?', 'answer' => 'Eventbrite\'s US pricing is a 3.7% + $1.79 service fee per paid ticket, plus a 2.9% payment processing fee per order. Event Schedule charges zero platform fees at every plan level, so you pay only Stripe\'s or PayPal\'s own processing. The Pro plan is a flat '.plan_price($this->planPrice()).'/mo regardless of how many tickets you sell.'],
                    ['question' => 'Does Event Schedule have an event marketplace like Eventbrite?', 'answer' => 'Event Schedule focuses on giving organizers their own branded schedule pages rather than a shared marketplace. You get a dedicated URL (or your own domain on Enterprise), embeddable calendar widgets, a live calendar feed people can subscribe to, and an optional interest list on each event that emails people when tickets go on sale.'],
                    ['question' => 'Can I refund a ticket buyer?', 'answer' => 'Yes, from the Sales page. Refunds come with paid ticketing on Pro, because that is where the money is taken in the first place. A Stripe, PayPal or Razorpay sale can be refunded in full or in part, and the money goes back through the provider before the sale changes status. A partial refund keeps the tickets valid and shows how much has gone back so far; a full refund returns the tickets to sale. A sale paid another way, such as cash, is marked as refunded instead, because no money moves through us.'],
                    ['question' => 'Does Event Schedule do reserved seating?', 'answer' => 'Yes, on the Enterprise plan. A venue schedule draws its room once, with sections, rows, tables, standing areas and wheelchair spaces, and buyers pick their seats from the map at checkout. A box office view handles holds, phone bookings and seat moves. On Free and Pro, ticket types have a name, a price and a quantity, without assigned seats.'],
                ],
            ],
            'luma' => [
                'name' => 'Luma',
                'key' => 'luma',
                'slug' => 'luma-alternative',
                'meta_title' => 'Luma Alternative: Luma Pricing and Fees vs 0% | Event Schedule',
                'tagline' => 'An open source alternative to Luma with zero platform fees, on the free plan too.',
                'description' => 'Luma\'s free plan takes 5% of paid tickets on top of Stripe, and Luma Plus is $59/mo billed annually. Event Schedule takes 0% on every plan, with Pro at '.plan_price($this->planPrice()).'/mo.',
                'keywords' => 'luma alternative, luma pricing, luma fees, luma plus pricing, is luma free, luma vs eventbrite, lu.ma alternative',
                'about' => 'Luma (luma.com) is a modern event platform popular with tech communities and creators. It offers sleek event pages, Zoom and Google Meet integration for online events, and community features.',
                'competitor_strengths' => [
                    'Polished, modern event page design with strong visual focus',
                    'Zoom and Google Meet integration that creates the meeting and tracks attendance',
                    'Popular in tech and startup communities with strong network effects',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (forever)', false],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', '$59/mo', true],
                        ['Platform fees', '0%', '5% (free plan), 0% (Plus)', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Stripe (2.9% + $0.30)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'No', true],
                        ['Sale notifications', 'Yes (Pro)', 'Yes (registration alerts by email and push)', false],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Clone with a repeat pattern (up to 20 copies), no linked series', true],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes (coupons)', false],
                        ['Post-event feedback', 'Yes (Pro)', 'Yes (Free)', false],
                        ['Individual tickets', 'Yes (Pro)', 'No', true],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (email, SMS and push)', false],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes (Free)', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'Referral tracking, no commission', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'No (one-way iCal feed)', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Yes', false],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No (custom luma.com URL on Plus)', true],
                        ['Remove branding', 'Yes (Pro)', 'Yes (Plus)', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'No', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Unlisted only, no password', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', '3 admins (free), 5 (Plus)', true],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'No', true],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (Plus)', true],
                        ['Webhooks', 'Yes (Pro)', 'Yes (Plus)', false],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => plan_price($this->planPrice()).'/mo vs $59/mo',
                        'description' => 'Event Schedule Pro costs '.plan_price($this->planPrice()).'/mo with a '.$this->trialDays().'-day free trial. Luma Plus costs $59/mo, billed annually, for comparable features.',
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
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format and our AI extracts all the details automatically. Perfect for quickly building your schedule when moving from another platform.',
                        'icon' => 'ai',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with no vendor lock-in. Selfhost on your own server instead of being locked into Luma\'s closed ecosystem.',
                        'icon' => 'code',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
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
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                    ['name' => 'Google Calendar', 'route' => 'marketing.compare_google_calendar'],
                ],
                'why_choose' => [
                    'summary' => 'Event Schedule delivers comparable features to Luma at a fraction of the cost, with open source transparency and zero platform fees.',
                    'points' => [
                        plan_price($this->planPrice()).'/month vs Luma\'s $59/month for premium features',
                        'Zero platform fees vs Luma\'s 5% on the free plan',
                        'Fully open source with selfhosting option for complete data ownership',
                        'Two-way Google, Outlook and CalDAV sync included free on every plan',
                        'A live calendar feed your community subscribes to once, and an interest list that emails people when tickets go on sale',
                        'Stripe or PayPal checkout, with refunds from the Sales page, on Pro',
                    ],
                ],
                // Search brings this page people asking what Luma costs (its fees, Luma Plus, whether
                // it is free, how it compares with Eventbrite), so the page answers that with a worked
                // month at Luma's published rates. Rates as published in October 2026.
                'fee_example' => [
                    'tag' => 'Luma fees, worked out',
                    'title' => 'What 100 tickets at $20 cost you on Luma',
                    'lead' => 'Luma\'s free plan takes a 5% platform fee on paid tickets, and Stripe\'s processing fee, typically 2.9% + 30 cents, comes on top. Luma Plus drops the platform fee for $59 a month, billed annually. Here is one month of $2,000 in ticket sales on each, at the rates each company publishes.',
                    'total_label' => 'Fees on $2,000',
                    'caption' => 'Fees on 100 tickets at $20, each bought in its own order, on Event Schedule Pro, Luma Plus, Luma Free and Eventbrite.',
                    'rows' => $this->feeExample(100, 20, [
                        ['name' => 'Event Schedule Pro', 'charges' => '0% platform fee, Stripe\'s own fee, and Pro at '.plan_price($this->planPrice()).'/mo', 'processing_percent' => 0.029, 'processing_fixed' => 0.30, 'monthly' => $this->planPrice(), 'ours' => true],
                        ['name' => 'Luma Plus', 'charges' => '0% platform fee, Stripe\'s own fee, and $59/mo billed annually', 'processing_percent' => 0.029, 'processing_fixed' => 0.30, 'monthly' => 59],
                        ['name' => 'Luma Free', 'charges' => '5% platform fee on top of Stripe\'s own fee', 'platform_percent' => 0.05, 'processing_percent' => 0.029, 'processing_fixed' => 0.30],
                        ['name' => 'Eventbrite', 'charges' => '3.7% + $1.79 a ticket, and 2.9% of each order for processing', 'platform_percent' => 0.037, 'platform_fixed' => 1.79, 'processing_percent' => 0.029],
                    ]),
                    'notes' => [
                        'Stripe\'s fee is on every row because every row pays it: it is the card processor\'s charge, not the platform\'s. Each ticket is counted as its own order, which is what makes the 30 cents count a hundred times.',
                        'Between Luma\'s own two plans, the 5% costs less than Luma Plus until a month\'s ticket sales pass $1,180. Event Schedule Pro costs less than Luma\'s 5% once a month\'s sales pass '.plan_price($this->planPrice() / 0.05).', and free registration costs nothing on either.',
                        'Luma Plus is billed annually, so its $59 is a twelfth of a $708 bill paid up front. These are the published US rates as of October 2026; your own numbers move with the ticket price, the number of orders and the country.',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create your schedule', 'description' => 'Free, no card, with its own address the moment it exists. Your Luma pages can stay up while you move.', 'doc' => 'getting-started#create-schedule'],
                    ['title' => 'Bring your Luma events across', 'description' => 'Paste the link to a Luma event or calendar page into AI import, and it fills in the name, date, time, venue and description for you to check before anything is saved.', 'doc' => 'ai-import'],
                    ['title' => 'Bring your guests across', 'description' => 'Export your guest list from Luma as a CSV and upload it to your newsletter list, up to ten thousand rows, on any plan.', 'doc' => 'newsletters#importing-emails'],
                    ['title' => 'Connect Stripe or PayPal and sell', 'description' => 'Paid tickets settle into your own account with no platform fee on top, where Luma\'s free plan takes 5%. Selling a ticket with a price is on Pro.', 'doc' => 'tickets#payment'],
                ],
                'limits' => [
                    ['tag' => 'No Zoom integration', 'title' => 'Bring your own video room', 'text' => 'Luma creates the Zoom or Google Meet meeting for a virtual event and records who attended, and Event Schedule does not. An online event here carries the link to the meeting or stream you run somewhere else, and a schedule synced with Outlook can add a Teams meeting link to it.'],
                    ['tag' => 'No tech-scene network', 'title' => 'Luma\'s crowd stays on Luma', 'text' => 'Part of Luma\'s pull is the tech and startup community already using it, which a new schedule here does not inherit. People find you through the follow link and its QR code, the live calendar feed, and the "Notify me" card on each event.'],
                    ['tag' => 'Co-hosts are Enterprise', 'title' => 'Three free admins there, one here', 'text' => 'Luma lets three admins run a calendar on its free plan and five on Plus. On Event Schedule\'s Free and Pro plans one person runs a schedule, and admins or viewers beside the owner come with Enterprise, up to five on the hosted service.'],
                ],
                'faq' => [
                    ['question' => 'Is Luma free?', 'answer' => 'Luma\'s free plan has no monthly charge, but on paid tickets it takes a 5% platform fee, and Stripe\'s processing fee (typically 2.9% + 30 cents) is charged on top. Luma Plus removes the platform fee for $59 a month, billed annually. Event Schedule takes no platform fee on any plan: free registration is unlimited on the free plan, and selling a ticket with a price on it is on Pro at '.plan_price($this->planPrice()).'/mo.'],
                    ['question' => 'What does Luma Plus cost, and what does it add?', 'answer' => 'Luma Plus is $59 a month, billed annually, which is $708 a year. It removes the 5% platform fee, raises calendar admins from three to five, and raises invites and newsletters from 500 a week to 5,000. Stripe\'s processing fee still applies. Event Schedule Pro is '.plan_price($this->planPrice()).'/mo with a '.$this->trialDays().'-day free trial and no platform fee.'],
                    ['question' => 'Luma or Eventbrite: which charges less per ticket?', 'answer' => 'On a $20 ticket, Luma\'s free plan takes $1.00 (5%) and Eventbrite takes $2.53 (3.7% + $1.79), before payment processing on either. Luma Plus takes nothing per ticket for $59 a month. Event Schedule takes nothing per ticket on any plan; the only deduction is your payment processor\'s own.'],
                    ['question' => 'Is it easy to switch from Luma to Event Schedule?', 'answer' => 'Yes. Create a free schedule and add your events manually or via AI import. Your audience can sign up by email for an occasional digest of the new events you create, or subscribe to the schedule\'s live calendar feed, which needs no email address.'],
                    ['question' => 'How does Event Schedule pricing compare to Luma?', 'answer' => 'Luma Plus is $59/mo billed annually, and Luma\'s free plan takes a 5% platform fee on paid events. Event Schedule Pro is '.plan_price($this->planPrice()).'/mo with zero platform fees. Even the free plan includes unlimited events, two-way calendar sync, unlimited free registration and newsletters, with no platform fee on anything you sell.'],
                    ['question' => 'Does Event Schedule support virtual events like Luma?', 'answer' => 'Yes. Event Schedule supports online events with video links, descriptions, and ticketing. Luma creates Zoom and Google Meet meetings for you, while Event Schedule takes the link from any video platform you already use, and the Outlook sync can create a Teams meeting.'],
                    ['question' => 'Can people be told when tickets go on sale?', 'answer' => 'Yes, free on every plan. Switch on the "Notify me" card and, on an event page, visitors leave an email address, with no account, and get one email when tickets go on sale, a reminder shortly before the event, and a notice if you cancel it; a change notice reaches them too when you choose to send one. Each date of a recurring event keeps its own list, and every email has a one-click unsubscribe. The event editor shows you how many people are waiting.'],
                ],
            ],
            'ticket-tailor' => [
                'name' => 'Ticket Tailor',
                'key' => 'ticket-tailor',
                'slug' => 'ticket-tailor-alternative',
                'meta_title' => 'Ticket Tailor Alternative, No Per-Ticket Fees | Event Schedule',
                'tagline' => 'A flat-rate alternative to Ticket Tailor with zero per-ticket fees.',
                'description' => 'Compare Event Schedule with Ticket Tailor: no per-ticket fee, Stripe or PayPal checkout, refunds and a multi-event cart.',
                'keywords' => 'ticket tailor alternative, ticket tailor alternative free, ticketing platform comparison, no per-ticket fees, ticket tailor competitor',
                'about' => 'Ticket Tailor is an independent ticketing platform focused on affordable event ticketing. It offers a straightforward per-ticket pricing model and supports various payment processors.',
                'competitor_strengths' => [
                    'Simple, transparent per-ticket pricing model',
                    'Supports multiple payment processors (Stripe, PayPal, Square)',
                    'Strong focus on ticketing, with box office features',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free events only', true],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'No subscription ($0.30 to $0.85 a ticket)', true],
                        ['Platform fees', '0%', '$0.30 to $0.85/ticket', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Stripe/PayPal/Square', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes (email sign-ups)', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'Yes', false],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                        ['Individual tickets', 'Yes (Pro)', 'No', true],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Scheduled broadcasts you set up (SMS paid)', true],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'Tracking links, commission paid by hand', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'No', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Yes ($8/mo add-on)', false],
                        ['Remove branding', 'Yes (Pro)', 'Yes (paid)', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'No built-in AI (an MCP connector for outside AI tools)', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Yes (access code)', false],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'No', true],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
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
                        'description' => 'Pay '.plan_price($this->planPrice()).'/mo flat instead of $0.30 to $0.85 per ticket. The more tickets you sell, the more you save.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google, Outlook and CalDAV sync included free. Ticket Tailor does not offer calendar sync.',
                        'icon' => 'calendar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format and our AI extracts all the details automatically. Skip manual data entry and populate your schedule in seconds.',
                        'icon' => 'ai',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Fully open source with no vendor lock-in. Selfhost for complete data ownership, something no closed-source ticketing platform can offer.',
                        'icon' => 'code',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
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
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Tito', 'route' => 'marketing.compare_tito'],
                ],
                'why_choose' => [
                    'summary' => 'Event Schedule offers flat-rate pricing instead of per-ticket fees, plus a full suite of features that go beyond pure ticketing.',
                    'points' => [
                        'Flat '.plan_price($this->planPrice()).'/month vs Ticket Tailor\'s per-ticket fees that scale with volume',
                        'Built-in newsletters and two-way Google, Outlook and CalDAV sync, included free',
                        'AI event parsing on every plan, and AI flyer generation on Enterprise',
                        'Fully open source with selfhosting option for complete data control',
                        'Stripe or PayPal checkout, one cart across several events, and refunds from the Sales page, on Pro',
                        'Gift cards, passes and installment payments on Pro, and allocated seating on Enterprise',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Pick the schedule type', 'description' => 'A Venue schedule if you sell for one room, a Talent schedule if the act travels. The type decides which sections the form offers, and making one is free with no card.', 'doc' => 'getting-started#schedule-types'],
                    ['title' => 'Copy your upcoming events', 'description' => 'Copy the details of each upcoming event from your Ticket Tailor box office and paste them into the import box, or upload the flyer, then check what it read before anything is saved.', 'doc' => 'ai-import#text-import'],
                    ['title' => 'Set your ticket types up once', 'description' => 'Rebuild your usual ticket types, prices and sales dates and keep them as a ticket preset, so each new event starts from it. Presets are on every plan.', 'doc' => 'tickets#ticket-presets'],
                    ['title' => 'Reconnect Stripe or PayPal', 'description' => 'Sold through Stripe or PayPal on Ticket Tailor? Connect that same account here. Square is not one of our gateways, so a Square seller opens a Stripe or PayPal account first.', 'doc' => 'account-settings#payments'],
                ],
                'limits' => [
                    ['tag' => 'No Square', 'title' => 'Square is not offered', 'text' => 'Ticket Tailor lets a seller take payments through Square, besides Stripe and PayPal, and that is one gateway missing here. A seller here picks from Stripe, PayPal, Razorpay, Payfast, Invoice Ninja, a payment link of their own, or cash at the door.'],
                    ['tag' => 'Box office is Enterprise', 'title' => 'Holds and phone bookings sit with seating', 'text' => 'Holding seats back, taking a booking over the phone and moving a buyer are done in the box office console that comes with allocated seating on Enterprise. On Pro a buyer can choose to pay cash at the door, where you mark the order paid and scan it.'],
                    ['tag' => 'Own domain is Enterprise', 'title' => 'A custom domain takes the top plan', 'text' => 'Ticket Tailor sells a custom domain as a paid option, and here it is part of Enterprise. Pro already removes the Event Schedule branding, and every schedule has its own address on our domain from the day it is made.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Ticket Tailor to Event Schedule?', 'answer' => 'Yes. If you are familiar with Ticket Tailor, Event Schedule will feel natural. Create a free schedule, set up your ticket types, and connect Stripe or PayPal. The ticketing workflow is straightforward and you can be selling tickets within minutes.'],
                    ['question' => 'How does Event Schedule pricing compare to Ticket Tailor?', 'answer' => 'Ticket Tailor charges per ticket sold. Event Schedule Pro is a flat '.plan_price($this->planPrice()).'/mo with zero platform fees, no matter how many tickets you sell. This makes costs predictable and lower for most organizers.'],
                    ['question' => 'What does Event Schedule offer that Ticket Tailor does not?', 'answer' => 'Event Schedule is open source and supports selfhosting, giving you full control over your data. It also includes AI event import, two-way Google Calendar sync, built-in newsletters, and fan engagement features that Ticket Tailor does not offer.'],
                    ['question' => 'Does Event Schedule have gift cards, passes, payment plans and seating?', 'answer' => 'Yes, each on the plan named. Gift cards, passes (festival, season, visit and membership) and installment payments, which split a ticket into monthly charges on Stripe, are on Pro. Allocated seating, where buyers pick seats from a map of the room, is on Enterprise for venue schedules. Refunds, full or partial, through Stripe, PayPal or Razorpay, come with paid ticketing on Pro.'],
                ],
            ],
            'google-calendar' => [
                'name' => 'Google Calendar',
                'key' => 'google-calendar',
                'slug' => 'google-calendar-alternative',
                'meta_title' => 'Google Calendar Alternative for Public Events | Event Schedule',
                'tagline' => 'A purpose-built event platform vs a general scheduling tool.',
                'description' => 'Compare Event Schedule with Google Calendar: public event pages, ticket sales, a live feed your audience can subscribe to, and two-way sync with Google.',
                'keywords' => 'google calendar alternative, google calendar for events, event calendar platform, public event calendar, google calendar vs event platform',
                'about' => 'Google Calendar is a popular personal scheduling tool used by hundreds of millions of people. It excels at managing appointments, meetings, and personal reminders with deep Google ecosystem integration. The good news? You don\'t have to choose - Event Schedule syncs with Google Calendar, so you can use both together.',
                'competitor_strengths' => [
                    'Free and ubiquitous, with a huge existing user base',
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
                    // Row labels and tiers are deliberately the SAME strings the other
                    // fifteen comparison tables use. This page had its own older
                    // vocabulary - "Ticket sales", "QR code check-in",
                    // "Sub-schedules/categories" - and with it two tiers that stopped
                    // being true when the free plan started selling.
                    'Ticketing & Payments' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'No', true],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'No', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Paid appointment bookings through your Stripe (premium plans)', true],
                        ['QR check-ins', 'Yes (Free)', 'No', true],
                        ['Ticket waitlist', 'Yes (Pro)', 'No', true],
                        ['Check-in dashboard', 'Yes (Pro)', 'No', true],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'No', true],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                        ['Free event RSVP', 'Yes (Free)', 'Invite link (up to 200 guests)', true],
                        ['Platform fees', '0%', '0% (Stripe fees on paid bookings)', false],
                    ],
                    'Event Features' => [
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'Gemini creates events from a prompt', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Rich descriptions (Markdown)', 'Yes (Free)', 'Basic rich text, no Markdown', true],
                        ['Custom fields', 'Yes (Pro)', 'Booking-form questions (appointments)', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Private visibility, no password', false],
                        ['Availability management', 'Yes (Enterprise)', 'Personal working hours and booking pages', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
                    ],
                    'Sharing & Promotion' => [
                        ['Shareable event pages', 'Yes (Free)', 'Basic public event link', true],
                        ['Link preview images', 'Yes (Free)', 'No', true],
                        ['Newsletter integration', 'Yes (Free)', 'No', true],
                        ['Embeddable calendars', 'Yes (customizable)', 'Yes (limited styling)', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'No', true],
                    ],
                    'Organization' => [
                        ['Sub-schedules', 'Yes (Free)', 'Multiple calendars', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                        ['Built-in analytics', 'Yes (Free)', 'No', true],
                    ],
                    'Platform' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Webhooks', 'Yes (Pro)', 'Change notifications through the API', false],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                        ['Calendar sync, both ways', 'Yes (Free)', 'N/A', true],
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
                        'description' => 'Sell tickets through your own Stripe or PayPal account, with QR check-in at the door and refunds from the Sales page. Google Calendar has no payment or ticketing capabilities whatsoever.',
                        'icon' => 'dollar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format and our AI extracts dates, times, and descriptions automatically. Google Calendar does not read a flyer or a pasted listing into a public event page.',
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
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
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
                'why_choose' => [
                    'summary' => 'Event Schedule is purpose-built for sharing events publicly with ticketing, newsletters, and custom branding - things Google Calendar was never designed for.',
                    'points' => [
                        'Ticketing through your own Stripe or PayPal account on Pro, with QR check-in at the door and refunds from the Sales page',
                        'Two-way sync with Google, Outlook and CalDAV keeps both platforms in sync automatically',
                        'Public schedule pages with embeddable widgets, and your own domain on Enterprise',
                        'Built-in newsletters, event graphics, and AI-powered event import',
                        'A live calendar feed people subscribe to once, and an interest list that emails them when tickets go on sale',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Create a schedule for the public side', 'description' => 'Google Calendar keeps your meetings. The schedule is the page the public sees, at an address of its own, and it is free.', 'doc' => 'getting-started#create-schedule'],
                    ['title' => 'Give public events their own calendar', 'description' => 'Make a separate calendar in Google for what you want published. The form warns you if you pick your main calendar, because that one usually holds meetings and reminders.', 'doc' => 'creating-schedules#integrations-google'],
                    ['title' => 'Choose the direction and the deletes', 'description' => 'Bidirectional Sync carries an edit made on either side to the other. You also decide whether an event deleted in Google is kept, marked cancelled or deleted here.', 'doc' => 'creating-schedules#delete-sync'],
                    ['title' => 'Hand out the feed, not invites', 'description' => 'Share the schedule\'s live calendar feed. People subscribe once in their own calendar app, and a date you move reaches them without anyone sending an invitation.', 'doc' => 'sharing#calendar-feeds'],
                ],
                'limits' => [
                    ['tag' => 'Not a personal calendar', 'title' => 'Meetings stay in Google', 'text' => 'Event Schedule does not send meeting invitations to a guest list or keep your own reminders. Keep Google Calendar for that, and let the two-way sync carry the public events between the two.'],
                    ['tag' => 'No Meet rooms', 'title' => 'Video links come from elsewhere', 'text' => 'Google Calendar is tied into Meet, Gmail and Drive, and Event Schedule hosts no video. For an online event you paste the address of the meeting, wherever it is held, and the Outlook sync can create a Teams meeting for one.'],
                    ['tag' => 'Shared editing is Enterprise', 'title' => 'One editor on Free and Pro', 'text' => 'Google lets several people work on one calendar at no cost. Here more than one team member on a schedule is an Enterprise feature, at most five on the hosted platform, and each member can then point the schedule at a Google calendar of their own.'],
                ],
                'faq' => [
                    ['question' => 'Can I use Event Schedule alongside Google Calendar?', 'answer' => 'Yes. Event Schedule offers two-way Google Calendar sync on the free plan. Your events stay in sync between both platforms, so you can use Google Calendar as your personal view while Event Schedule powers your public schedule and ticketing.'],
                    ['question' => 'Is Event Schedule free like Google Calendar?', 'answer' => 'Event Schedule has a free plan that includes unlimited events, two-way Google Calendar sync, newsletters and a live calendar feed your audience can subscribe to. Free registration is unlimited and every ticket scans at the door. Selling paid tickets is on the Pro plan at '.plan_price($this->planPrice()).'/mo, which also adds the live check-in dashboard and branding removal.'],
                    ['question' => 'Can Event Schedule handle ticketing that Google Calendar cannot?', 'answer' => 'Yes. Google Calendar has no ticketing features. Event Schedule sells tickets through your own Stripe or PayPal account on the Pro plan, each one scanned at the door, with refunds from the Sales page. Free registration is unlimited on every plan. Pro also adds the live check-in dashboard, ticket waitlists, sale notifications and sales CSV export, all with zero platform fees.'],
                    ['question' => 'Can people subscribe to all my events in their own calendar?', 'answer' => 'Yes, free. Every schedule has a live calendar feed, offered in the sign-up panel on your schedule and event pages and in the Add to Calendar menu. Their calendar app re-reads the feed, so an added or moved date shows up without anyone sending a new invite, which a one-off download cannot do. It costs them no email address.'],
                ],
            ],
            'meetup' => [
                'name' => 'Meetup',
                'key' => 'meetup',
                'slug' => 'meetup-alternative',
                'meta_title' => 'Meetup Alternative for Community Events | Event Schedule',
                'tagline' => 'A free, open source alternative to Meetup with zero fees and full customization.',
                'description' => 'Compare Event Schedule with Meetup: free community event pages, email sign-ups, a live calendar feed and ticketing with zero platform fees.',
                'keywords' => 'meetup alternative, meetup alternative free, free event platform, meetup competitor, community events platform',
                'about' => 'Meetup is a social platform for organizing and discovering local community events and groups. It connects people with shared interests through in-person and online gatherings.',
                'competitor_strengths' => [
                    'Large built-in community and event discovery marketplace',
                    'Strong network effects with millions of active users',
                    'Social features with member profiles, RSVPs, and group discussions',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Starter (first-time organizers: 2 events a month, 10 attendees each)', true],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'From $29.99/mo or $174.99/yr (organizer)', true],
                        ['Platform fees', '0%', 'Service fee on each paid ticket, paid by the attendee', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Built-in (for paid events)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes (paid events)', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes (in the Meetup app)', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Free events only', true],
                        ['Check-in dashboard', 'Yes (Pro)', 'Attendee list with check-in status', false],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'Yes (star ratings and reviews)', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (email and push)', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'Subscribe feed, one way', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Group messaging only', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Custom fields', 'Yes (Pro)', 'RSVP questions', true],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                    ],
                    'Unique Features' => [
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'No', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'Comments and photos, no video', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Co-organizers (paid)', true],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'No', true],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'GraphQL, Meetup Pro only', true],
                        ['Webhooks', 'Yes (Pro)', 'No', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'No Organizer Fee',
                        'description' => 'Past its Starter plan of two small events a month, Meetup charges organizers from $29.99/mo (or $174.99/yr) to run a group. Event Schedule is free forever, with an optional '.plan_price($this->planPrice()).'/mo Pro upgrade.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Own Your Community',
                        'description' => 'On Meetup, your members belong to Meetup - not you. With Event Schedule, your audience is yours: your own branding and newsletter list, and your own domain on Enterprise.',
                        'icon' => 'globe',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Custom Branding',
                        'description' => 'Every Meetup group looks the same. Event Schedule lets you customize themes, colors, backgrounds, and use your own domain for a unique brand identity.',
                        'icon' => 'image',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Embeddable Calendar',
                        'description' => 'Embed your event calendar directly on your website. Meetup has no embed option, forcing visitors to leave your site.',
                        'icon' => 'calendar',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Check-In From Any Phone',
                        'description' => 'Scan attendees in at the door from any phone browser, with nothing to install. Meetup checks members in through its own app.',
                        'icon' => 'ticket',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Fan Engagement',
                        'description' => 'Let your community share videos and comments on events. Build engagement that goes beyond RSVPs, with videos as well as photos.',
                        'icon' => 'image',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Google Calendar', 'route' => 'marketing.compare_google_calendar'],
                ],
                'why_choose' => [
                    'summary' => 'Against Meetup it comes down to who holds the members and what hosting a group costs. Meetup charges the organizer a subscription; here running the group is free, and the people who sign up are on your own list.',
                    'points' => [
                        'Running the group costs nothing on the free plan, against Meetup\'s organizer subscription from $29.99/mo once a group outgrows Starter',
                        'Members who sign up by email are on your list, and the confirmed ones get a digest of each batch of new events automatically',
                        'A monthly meetup is one recurring event, on the 2nd Tuesday or any other pattern, on every plan',
                        'Free registration with an optional limit per date, and a QR scan at the door from any phone browser',
                        'The group\'s calendar embeds on your own website, so visitors never have to leave it to see what is next',
                        'Paid meetups on Pro, settled into a payment account in your name, with promo codes and a waitlist for sold-out tickets',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Choose a type for the group', 'description' => 'A group that meets in different places usually fits a Curator schedule, and one with a regular room a Venue schedule. The type cannot be changed once it is saved.', 'doc' => 'getting-started#schedule-types'],
                    ['title' => 'Set up the regular night once', 'description' => 'A meetup on the 2nd Tuesday of each month is one recurring event, with single dates skipped or added when the room is taken.', 'doc' => 'creating-events#recurring'],
                    ['title' => 'Swap RSVPs for free registration', 'description' => 'Guests register with a name and an email, capped per date if the room has a limit. It is unlimited on the free plan and needs no payment account.', 'doc' => 'tickets#registration'],
                    ['title' => 'Invite members from inside Meetup', 'description' => 'Send the group a message with your schedule\'s sign-up link or QR code, so members join your list themselves and nothing has to be exported.', 'doc' => 'sharing#qr-code'],
                ],
                'limits' => [
                    ['tag' => 'No member network', 'title' => 'Nobody finds the group by browsing', 'text' => 'Meetup\'s members find new groups through its own discovery, and a new schedule here starts without that pull. Growth comes from the sign-up link and QR code you hand out, the calendar you embed, and your public events on the Event Schedule browse pages.'],
                    ['tag' => 'No discussions or profiles', 'title' => 'Comments, not a forum', 'text' => 'There are no member profiles and no group discussion boards. People can comment on an event and send photos and videos to it, each one approved by you before it shows.'],
                    ['tag' => 'Newsletters have an allowance', 'title' => 'Group messages are counted', 'text' => 'A newsletter you write here counts every recipient, up to ten a month on the free plan, a hundred on Pro and a thousand on Enterprise, with no ceiling when it goes out through your own email server, while the automatic new-event digest is not counted at all.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Meetup to Event Schedule?', 'answer' => 'Yes. Create a free schedule and add your recurring or one-time events, then share its URL with your community. Members can sign up by email for a digest of the new events you create or subscribe to your live calendar feed, and newsletters keep the group engaged between meetups.'],
                    ['question' => 'How does Event Schedule pricing compare to Meetup?', 'answer' => 'Meetup has a free Starter plan for first-time organizers (one group, two events a month, ten attendees each), then charges a subscription from $29.99/mo or $174.99/yr, and attendees pay a service fee on paid tickets. Event Schedule has a free plan with unlimited events. The Pro plan is '.plan_price($this->planPrice()).'/mo with zero platform fees on ticket sales.'],
                    ['question' => 'Can Event Schedule handle community events and recurring meetups?', 'answer' => 'Yes. Event Schedule supports recurring events, fan engagement features like videos and comments, newsletters with A/B testing, and embeddable calendar widgets. All designed for building and maintaining community.'],
                    ['question' => 'How will my members hear about new events?', 'answer' => 'People who sign up by email and confirm get an occasional digest of the new public events you create, at most one every 72 hours per schedule. Anyone can subscribe to your live calendar feed, which needs no email address and updates itself when a date moves. On a single event, the interest list emails people when tickets go on sale, reminds them before it starts and tells them if it is cancelled. Newsletters you write reach whoever you choose, within your plan\'s monthly recipient allowance.'],
                    ['question' => 'Can I bring my Meetup members with me?', 'answer' => 'If you hold their addresses with their consent, a CSV of up to 10,000 rows goes into your newsletter list on any plan. Otherwise post your schedule\'s sign-up link in a group message: people who confirm get the automatic digest of new events, and anyone can subscribe to the live calendar feed without giving an email address.'],
                    ['question' => 'Is there a fee on a paid meetup?', 'answer' => 'Event Schedule takes no platform fee on a paid event. A priced ticket is a Pro feature ('.plan_price($this->planPrice()).'/mo), and the money lands in the Stripe or PayPal account you connect, whose processing fee applies. A meetup that only takes free registrations costs nothing.'],
                ],
            ],
            'dice' => [
                'name' => 'DICE',
                'key' => 'dice',
                'slug' => 'dice-alternative',
                'meta_title' => 'DICE Alternative With Zero Platform Fees | Event Schedule',
                'tagline' => 'A transparent, fee-free alternative to DICE for event organizers.',
                'description' => 'Compare Event Schedule with DICE: tickets that open in any browser, payouts to your own Stripe or PayPal account, and zero platform fees.',
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
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'Free (fees on buyers)', true],
                        ['Platform fees', '0%', 'Service fee on buyers', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Built-in', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes (app-based)', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes (face-value resale waitlist)', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'No', true],
                        ['Sale notifications', 'Yes (Pro)', 'Scheduled sales reports', true],
                        ['Sales data export', 'Yes (Pro)', 'Yes (reports and a read-only API)', false],
                        ['Recurring events', 'Yes (Free)', 'Limited', true],
                        ['Online events', 'Yes (Free)', 'Yes (livestream)', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (free tickets and guest lists)', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                        ['Individual tickets', 'Yes (Pro)', 'No', true],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'On-sale alerts, no documented pre-show reminder', true],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Charity venues only', true],
                        ['Promoter links with commission', 'Yes (Pro)', 'Tracking links, no commission', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'No newsletter tool (DICE emails its own users)', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No', true],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Custom event pages', 'Yes (full control)', 'Template-based', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Widget only', true],
                    ],
                    'Unique Features' => [
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'No', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'No', true],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
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
                        'description' => 'DICE fans can buy on the web, but they need its app to open the ticket and get in. Event Schedule tickets open in any browser - no app install required, no friction for your audience.',
                        'icon' => 'globe',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Direct Payments',
                        'description' => 'With DICE, they control the money and pay you later. Event Schedule connects directly to your own Stripe or PayPal account - funds go straight to you, and refunds go back the same way.',
                        'icon' => 'dollar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google, Outlook and CalDAV sync keeps your schedule always up to date. DICE has no calendar integration whatsoever.',
                        'icon' => 'calendar',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Fan Videos & Comments',
                        'description' => 'Let fans share videos and comments on your events, building a community around your shows. DICE offers no fan-generated content features.',
                        'icon' => 'image',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
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
                        'description' => 'Reach your audience directly with built-in newsletters. DICE emails and notifies its own users, and gives you no newsletter tool of your own.',
                        'icon' => 'mail',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                ],
                'why_choose' => [
                    'summary' => 'For a promoter or venue weighing DICE, two things decide it: who holds the ticket money, and whether fans have to install an app. Here tickets open in a browser and payments land in your own account.',
                    'points' => [
                        'Fans buy and open their tickets in any browser, with nothing to install',
                        'Ticket money settles into your own Stripe or PayPal account on Pro',
                        'No service fee from us on top of your price, so the figure you set is the one fans see',
                        'Free registration for guest-list nights and free-entry shows, with no app needed to get in',
                        'Promo codes and a waitlist for sold-out shows on Pro, and a reminder email to every ticket holder before the night, on every plan',
                        'A weekly club night set up once, on chosen days, as one recurring event on every plan',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Set up the room or the act', 'description' => 'A club or venue takes a Venue schedule, with its address and a map; a DJ or band takes a Talent schedule that lists shows wherever they play.', 'doc' => 'getting-started#schedule-types'],
                    ['title' => 'Bring the line-up across', 'description' => 'Upload a show\'s poster or paste its listing, and the parser reads the act, date, time and venue for you to check before saving.', 'doc' => 'ai-import#image-import'],
                    ['title' => 'Price the tiers', 'description' => 'Give each ticket type its own sales dates, so an early-bird tier closes by itself on the day you choose. Taking payment for them is on Pro.', 'doc' => 'tickets#ticket-types'],
                    ['title' => 'Catch fans before the on-sale', 'description' => 'Switch on the Notify me card, and fans can leave an address on an announced show and get an email when its tickets go on sale. Collecting the addresses is free on every plan; the paid tickets themselves are Pro.', 'doc' => 'creating-schedules#engagement-mailing-list'],
                ],
                'limits' => [
                    ['tag' => 'Tickets can be forwarded', 'title' => 'No mobile-only lock', 'text' => 'DICE ties each ticket to the fan\'s phone to stop scalping, and Event Schedule has no such lock: a ticket is a QR code in an email and on a page, and it can be passed on. What stops a copy getting in twice is the door scanner, which admits each ticket once and warns on a second scan.'],
                    ['tag' => 'No discovery app', 'title' => 'Fans will not find you in a feed', 'text' => 'DICE puts shows in front of fans inside its own app, and there is no app audience of that kind here. You reach fans through your schedule page, its followers, the new-event digest and newsletters, which arrive by email rather than as push notifications.'],
                    ['tag' => 'No livestreaming', 'title' => 'Streams are hosted elsewhere', 'text' => 'DICE can stream a show itself. Here you stream on the service of your choice and the event page links to it, because Event Schedule hosts no video.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from DICE to Event Schedule?', 'answer' => 'Yes. Unlike DICE, Event Schedule works in any browser so your audience does not need to download an app. Create a free schedule, add your events, and share a direct link. Your fans can browse and buy tickets instantly.'],
                    ['question' => 'How does Event Schedule pricing compare to DICE?', 'answer' => 'DICE builds a booking fee into the advertised ticket price and shares it with the organiser, at a rate it does not publish. Event Schedule charges zero platform fees and connects directly to your own Stripe or PayPal account, so you control exactly what attendees pay.'],
                    ['question' => 'Does Event Schedule support music and nightlife events like DICE?', 'answer' => 'Yes. Event Schedule handles any event type including concerts, festivals, and nightlife. You get ticketing with QR check-ins, event graphics generation, and a shareable schedule page, without being locked into a single event genre.'],
                    ['question' => 'Do my fans need an app to get their tickets?', 'answer' => 'No. The ticket opens in any browser, from the confirmation email or the ticket page, each with its QR code'.$this->walletClause().'. The free scanner reads that same code at the door.'],
                    ['question' => 'Can Event Schedule stop ticket touts?', 'answer' => 'Not the way DICE does, because there is no mobile-only ticket. Each ticket is a QR code that admits once, and a second scan warns the door. A per-order limit on each ticket type caps how many one buyer can take in a single order.'],
                    ['question' => 'Can one show have a guest list and paid tickets?', 'answer' => 'Yes. A ticket type priced at zero can sit beside paid ones, so a guest-list tier and a paid tier share one event, and the $0 tier works on every plan. The paid tier needs Pro. A night with no paid entry at all can use free registration instead.'],
                ],
            ],
            'brown-paper-tickets' => [
                'name' => 'Brown Paper Tickets',
                'key' => 'brown-paper-tickets',
                'slug' => 'brown-paper-tickets-alternative',
                'meta_title' => 'Brown Paper Tickets Alternative, 0% Fees | Event Schedule',
                'tagline' => 'A modern, actively developed alternative to Brown Paper Tickets.',
                'description' => 'Brown Paper Tickets has closed to new events. Compare Event Schedule: zero platform fees, Stripe or PayPal payouts, and unlimited free registration.',
                'keywords' => 'brown paper tickets alternative, brown paper tickets replacement, bpt alternative, event ticketing platform, brown paper tickets competitor',
                'about' => 'Brown Paper Tickets was a ticketing platform known for its low fees and focus on independent events. It was acquired by Events.com, no longer takes new events and sends organizers to Events.com, so many are looking for a new home.',
                'competitor_strengths' => [
                    'Historically low per-ticket fees for organizers',
                    'Long track record in the independent event space',
                    'Will-call and physical ticket options',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free to list', false],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'Free (fees on tickets)', true],
                        ['Platform fees', '0%', '$1.49 + 6% per ticket (buyer-paid)', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Built-in', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Limited', true],
                        ['Ticket waitlist', 'Yes (Pro)', 'No', true],
                        ['Check-in dashboard', 'Yes (Pro)', 'No', true],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'Yes (spreadsheet reports)', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Limited', true],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Passwords and member discount codes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                        ['Individual tickets', 'Yes (Pro)', 'No', true],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'No (updates sent by hand)', true],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'No', true],
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
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'No', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Password-protected prices only', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'No', true],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
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
                        'description' => 'Brown Paper Tickets no longer takes new events, and its past event data was not moved to Events.com. Event Schedule is actively developed with regular updates and dependable uptime.',
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
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Direct Payouts',
                        'description' => 'BPT has been known for delayed payouts to organizers. Event Schedule connects directly to your own Stripe or PayPal account - funds go straight to you on the provider\'s own schedule.',
                        'icon' => 'dollar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google, Outlook and CalDAV sync keeps everything in sync automatically. Brown Paper Tickets has no calendar integration at all.',
                        'icon' => 'calendar',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
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
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                    ['name' => 'DICE', 'route' => 'marketing.compare_dice'],
                ],
                'why_choose' => [
                    'summary' => 'With Brown Paper Tickets closed to new events, the real question is where to settle next. Event Schedule is actively developed, adds no platform fee, and its code is open source, so the software cannot be taken away from you a second time.',
                    'points' => [
                        'No $1.49 + 6% service fee on each ticket: Event Schedule adds no platform fee to what buyers pay',
                        'Payouts on your processor\'s own timetable, straight into the Stripe or PayPal account you connect',
                        'Promo codes, a ticket waitlist and a reminder email to every ticket holder before the event, which Brown Paper Tickets never sent for you',
                        'A door scanner that runs on a phone on every plan, and a live check-in count on Pro',
                        'Cash at the door still works on Pro: set an event to cash, and you mark each order paid when the buyer pays at the door',
                        'Selfhosting under an open source license, which resolves to Enterprise, if you never want to be moved on again',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Bring your events across with AI import', 'description' => 'Paste the text of an event, from an old listing, an email or a flyer, into AI import, which fills in the event for you to check.', 'doc' => 'ai-import#text-import'],
                    ['title' => 'Recreate the ticket types', 'description' => 'A name, price and quantity for each type, with dates sales open and close, so an early-bird price ends on its own.', 'doc' => 'tickets#ticket-types'],
                    ['title' => 'Connect a payment account', 'description' => 'Brown Paper Tickets processed the cards itself. Here Stripe or PayPal does, into an account in your name, and an event can be set to cash at the door instead.', 'doc' => 'account-settings#payments'],
                    ['title' => 'Scan at the door', 'description' => 'The scanner runs in a phone\'s browser on every plan, and each ticket admits once.', 'doc' => 'tickets#check-in'],
                ],
                'limits' => [
                    ['tag' => 'No printed tickets', 'title' => 'Will-call becomes a QR code', 'text' => 'Brown Paper Tickets offered will-call and physical tickets, and Event Schedule does not print or post tickets. Every ticket is a QR code in the buyer\'s email and on a ticket page, which scans from a phone screen or a printout.'],
                    ['tag' => 'No history import', 'title' => 'Past orders stay behind', 'text' => 'There is no Brown Paper Tickets importer, so past events, orders and buyer records do not come across. Upcoming events can be copied from their pages with AI import while those pages still load.'],
                    ['tag' => 'Pro for selling', 'title' => 'The organizer pays, not the buyer', 'text' => 'Brown Paper Tickets put its fee on the buyer, so listing cost the organizer nothing. Selling priced tickets here is Pro at '.plan_price($this->planPrice()).'/mo, a flat charge that does not grow with the number sold, and free registration stays free.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Brown Paper Tickets to Event Schedule?', 'answer' => 'Yes. Many organizers are moving away from Brown Paper Tickets now that the platform no longer takes new events. Create a free schedule, add your events (AI import speeds this up), and share your new URL. You can be up and running in minutes.'],
                    ['question' => 'How does Event Schedule pricing compare to Brown Paper Tickets?', 'answer' => 'Brown Paper Tickets charged $1.49 + 6% per ticket in the US as a buyer-paid service fee. Event Schedule charges zero platform fees at any plan level. Free registration is unlimited, and selling paid tickets is on Pro. The Pro plan is a flat '.plan_price($this->planPrice()).'/mo for unlimited ticketing, with payouts straight into your own Stripe or PayPal account.'],
                    ['question' => 'Is Event Schedule actively maintained?', 'answer' => 'Yes. Brown Paper Tickets no longer takes new events. Event Schedule is built with a modern tech stack and receives regular updates. It is fully open source, so you can inspect the code, track development activity, and even selfhost it for complete control over your platform.'],
                    ['question' => 'Can a buyer get tickets to several shows in one order?', 'answer' => 'Yes. Tickets for several of your events go into one cart and are paid once, through Stripe or PayPal, with one order page listing every ticket. The cart itself is on every plan, and each paid ticket in it needs Pro.'],
                    ['question' => 'What happens to tickets I already sold on Brown Paper Tickets?', 'answer' => 'They stay with Brown Paper Tickets, or Events.com where it now sends organizers, because there is no importer for them. If those buyers are coming to a show you now run here and you have their details, the attendee import on Pro adds them from a CSV or by hand, up to 5,000 rows at a time.'],
                    ['question' => 'Can I keep my events if Event Schedule ever changes course?', 'answer' => 'The code is open source under the Attribution Assurance License, so the hosted service is not the only way to run it. You can download a backup of your schedule at any time, and the same software can be installed on your own server, where every feature is included.'],
                ],
            ],
            'splash' => [
                'name' => 'Splash',
                'key' => 'splash',
                'slug' => 'splash-alternative',
                'meta_title' => 'Splash Alternative Without Enterprise Pricing | Event Schedule',
                'tagline' => 'A simpler, more affordable alternative to Splash for event marketing.',
                'description' => 'Compare Event Schedule with Splash: branded event pages, registration and ticketing with zero platform fees, and published pricing with no sales call.',
                'keywords' => 'splash alternative, splash event marketing alternative, splash competitor, event management platform, splash replacement',
                'about' => 'Splash, part of Cvent since 2024, is an enterprise event marketing platform focused on branded event experiences, registration, and attendee engagement. It targets mid-to-large organizations with custom event pages and marketing automation.',
                'competitor_strengths' => [
                    'Enterprise-grade event marketing and branding tools',
                    'Advanced attendee engagement and analytics',
                    'Custom event page designer with drag-and-drop builder',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'No (custom pricing)', true],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'Enterprise pricing', true],
                        ['Platform fees', '0%', 'Included in subscription', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Built-in', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes (RSVP events, paid plans)', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'Yes (RSVP notifications, Slack)', false],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'Survey pages you send by hand', true],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (you build and schedule them)', false],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes (donation tickets)', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'Tracking links, no commission', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'Limited', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Yes (enterprise)', false],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Yes (enterprise)', false],
                        ['Remove branding', 'Yes (Pro)', 'Yes (enterprise)', false],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'Yes (paid plans)', false],
                        ['Embeddable calendar', 'Yes (Free)', 'Registration widget', true],
                    ],
                    'Unique Features' => [
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'No', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Event boost (ads)', 'Yes (Pro)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'No', true],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (enterprise)', false],
                        ['Webhooks', 'Yes (Pro)', 'Yes (Simple Postback)', false],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                        ['Setup time', 'Minutes', 'Self-serve, or sales-led onboarding', true],
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
                        'description' => 'Splash is sold through its sales team, with onboarding for larger rollouts. Event Schedule is ready in minutes - create your schedule, add events, and share with your audience right away.',
                        'icon' => 'calendar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Transparent '.plan_price($this->planPrice()).'/mo',
                        'description' => 'Splash hides pricing behind "contact us" forms. Event Schedule is free forever, with Pro at a transparent '.plan_price($this->planPrice()).'/mo - no surprise invoices or annual commitments.',
                        'icon' => 'dollar',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format and AI extracts everything automatically. Skip the lengthy setup process that enterprise platforms require.',
                        'icon' => 'ai',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
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
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Accelevents', 'route' => 'marketing.compare_accelevents'],
                ],
                'why_choose' => [
                    'summary' => 'Splash is sold through a sales team on enterprise terms. Event Schedule publishes its prices, starts free, and gives a team branded registration pages without a procurement process.',
                    'points' => [
                        'Published prices: a free plan, and Pro at '.plan_price($this->planPrice()).'/mo after a '.$this->trialDays().'-day trial, with no annual contract',
                        'Unlimited free registration on every plan, with questions asked once per order on Pro',
                        'Custom CSS on Pro for whatever the theme settings do not cover, at a price that is published',
                        'A ticket waitlist, promo codes and post-event feedback emails on Pro',
                        'The REST API and webhooks on Pro, where Splash keeps its API for enterprise',
                        'The open source code can be selfhosted, so attendee data can stay on your company\'s own servers',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Sign up, no demo', 'description' => 'Make an account and a schedule in a few minutes, free and without a card. Nobody from sales has to be involved.', 'doc' => 'getting-started#create-account'],
                    ['title' => 'Match the company brand', 'description' => 'Set the schedule\'s colors, fonts, header and background to your own, and add custom CSS on Pro for anything left over.', 'doc' => 'schedule-styling#color-scheme'],
                    ['title' => 'Build the registration form', 'description' => 'Choose free registration, then add up to ten questions under Custom Fields (Per Order) in its Registration form row, such as company or dietary needs. Those questions are on Pro.', 'doc' => 'tickets#checkout-fields'],
                    ['title' => 'Put sign-up on your own site', 'description' => 'Embed the registration form for free, or the ticket form on Pro, so people sign up without leaving the company website.', 'doc' => 'tickets#embed-widget'],
                ],
                'limits' => [
                    ['tag' => 'No drag-and-drop builder', 'title' => 'Pages are styled, not built', 'text' => 'Splash designs an event page in a drag-and-drop builder. Here the layout of a page is set, and you style it with colors, fonts, header images and backgrounds, plus custom CSS on Pro.'],
                    ['tag' => 'No marketing automation', 'title' => 'Email you write, not journeys', 'text' => 'Splash runs marketing automation around an event; Event Schedule has no automated email journeys and no contact database. What it has is newsletters with A/B testing and repeats, the digest that goes out when you publish new events, and the Notify me list, all sent to your own followers and subscribers.'],
                    ['tag' => 'Analytics per schedule', 'title' => 'Counts, not attendee profiles', 'text' => 'Splash tracks how each attendee engages. The built-in analytics here count page views, followers and sales for a schedule, and the Realtime view shows visits without naming anyone.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Splash to Event Schedule?', 'answer' => 'Yes. Unlike Splash, there is no enterprise onboarding or sales process required. Just sign up, create a free schedule, add your events, and share your schedule URL. AI import can speed up event creation too.'],
                    ['question' => 'How does Event Schedule pricing compare to Splash?', 'answer' => 'Splash uses custom enterprise pricing that requires contacting sales, on annual contracts. Event Schedule Pro is a transparent '.plan_price($this->planPrice()).'/mo with zero platform fees and no annual commitment.'],
                    ['question' => 'Can Event Schedule handle corporate events like Splash?', 'answer' => 'Yes. Event Schedule supports custom branding, embeddable widgets, custom CSS, and a REST API with webhooks on Pro for integration with your existing tools. Custom domains and multiple team members are on Enterprise.'],
                    ['question' => 'Can my team work on the same schedule?', 'answer' => 'Yes, on Enterprise, capped at five members on the hosted platform. An admin runs the schedule day to day and sees its ticket sales, waitlist and check-in dashboard; a viewer is read-only and sees no sales, but can scan tickets at the door. The free and Pro plans are one person per schedule.'],
                    ['question' => 'Can registrations go straight into our own systems?', 'answer' => 'Yes, through webhooks on Pro, which POST each new order (a free registration included), each published event change and each check-in to an address you choose. The REST API on the same plan reads and writes events and sales. There is no ready-made connector to a named CRM, so the receiving end is yours to build.'],
                    ['question' => 'Where is attendee data kept?', 'answer' => 'On the hosted platform it sits in Event Schedule\'s own database, and you can download a backup of a schedule at any time. A company that needs it on its own servers can selfhost the open source code instead, which includes every Enterprise feature.'],
                ],
            ],
            'sched' => [
                'name' => 'Sched',
                'key' => 'sched',
                'slug' => 'sched-alternative',
                'meta_title' => 'Sched Alternative for Conference Agendas | Event Schedule',
                'tagline' => 'A more affordable, full-featured alternative to Sched with zero platform fees.',
                'description' => 'Compare Event Schedule with Sched. Get zero platform fees, calendar sync, and open source flexibility for '.plan_price($this->planPrice()).'/mo instead of $50+/mo.',
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
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'From $50/mo, billed annually', true],
                        ['Platform fees', '0%', '1.9% + $0.99/ticket (0.99% on Ultra)', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Stripe (separate)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes (paid plans, fee per ticket)', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes (Boost+)', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Session waitlists only (Boost plan and up)', true],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'No', true],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'No', true],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (small events)', true],
                        ['Event polls', 'Yes (Pro)', 'Feedback surveys only', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'Yes', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (Launch plan and up)', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'Export/subscribe only', true],
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
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'AI writing tools (titles, descriptions, email)', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Yes (password wall, invite-only)', false],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'Yes (Boost plan and up)', false],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
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
                        'title' => 'Pro at '.plan_price($this->planPrice()).'/mo',
                        'description' => plan_price($this->planPrice()).'/mo vs $50+/mo starting price. Get more features for a fraction of the cost.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync included free. Sched only offers one-way iCal export.',
                        'icon' => 'calendar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details in any format and AI extracts everything automatically. Populate your conference agenda in seconds instead of entering sessions one by one.',
                        'icon' => 'ai',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Keep your audience engaged with built-in newsletters. Sched only has event email notifications.',
                        'icon' => 'mail',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
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
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Whova', 'route' => 'marketing.compare_whova'],
                    ['name' => 'Accelevents', 'route' => 'marketing.compare_accelevents'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                ],
                'why_choose' => [
                    'summary' => 'Sched is built around one conference agenda at a time. Event Schedule is a standing calendar a conference can live on, with tracks as sub-schedules, sessions as agenda parts, and ticketing that takes no platform fee.',
                    'points' => [
                        'No attendee ceiling on the free plan, where Sched\'s free tier is for events under 50 people',
                        'Tracks or rooms as sub-schedules, each with its own color and address for filtering the program',
                        'Sessions as the parts of an event\'s agenda, open to comments, free on every plan',
                        'Ticketing built in on Pro rather than sold as an add-on, with no platform fee',
                        'Recurring events, which Sched does not support, for a meetup series that runs between conferences',
                        'A waitlist for a sold-out ticket and sale notifications, both on Pro, where Sched waitlists sessions only',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Read in the Sched program', 'description' => 'Paste your Sched event\'s iCal export link into the import box. A calendar feed is read directly, so it does not count against the daily AI allowance.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'Turn tracks into sub-schedules', 'description' => 'Make one sub-schedule per track, stage or room. Each is given a color, and the calendar can be filtered down to any one of them.', 'doc' => 'creating-schedules#customize-subschedules'],
                    ['title' => 'Name the speakers', 'description' => 'List each speaker on the sessions they give, under Participants. A speaker with no page yet is given one, and claims it later by signing in with the email you entered.', 'doc' => 'creating-events#participants'],
                    ['title' => 'Sell one ticket for the conference', 'description' => 'A festival pass on Pro admits once to each session it covers. For a free conference, make it one multi-day event with free registration.', 'doc' => 'subscriptions#types'],
                ],
                'limits' => [
                    ['tag' => 'No personal agendas', 'title' => 'Attendees do not build their own schedule', 'text' => 'Sched lets each attendee pick sessions into a personal agenda, and there is no equivalent here. Attendees filter the calendar by sub-schedule and add sessions to their own calendar, or subscribe to the whole feed.'],
                    ['tag' => 'No speaker management', 'title' => 'Speakers are named, not managed', 'text' => 'There are no speaker or exhibitor profiles to collect and approve, which is a large part of what Sched does. A speaker is added to a session as a participant with a page they can claim, and sponsors show as tiered logos on Pro.'],
                    ['tag' => 'Agenda scanning is Enterprise', 'title' => 'Typed agendas on Free and Pro', 'text' => 'Reading a printed program into an event\'s agenda with AI is an Enterprise feature. On Free and Pro the parts are typed in by hand, or each session becomes its own event, which AI import can fill from pasted text.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Sched to Event Schedule?', 'answer' => 'Yes. Create a free schedule and add your conference sessions or events. AI import lets you paste event details to populate your schedule quickly. No migration needed.'],
                    ['question' => 'How does Event Schedule pricing compare to Sched?', 'answer' => 'Sched charges per-event or annual pricing that scales with attendee count. Event Schedule Pro is a flat '.plan_price($this->planPrice()).'/mo with unlimited events and zero platform fees on ticket sales.'],
                    ['question' => 'Can Event Schedule handle conference agendas like Sched?', 'answer' => 'Yes. Event Schedule supports multi-day events, sub-schedules for organizing tracks or sessions, and embeddable calendar widgets. Attendees can browse your full conference agenda from a single schedule page.'],
                    ['question' => 'Can attendees buy one pass for several sessions?', 'answer' => 'Yes, on Pro. A pass admits to several events: a festival pass covers each event once, a visit pass gives a set number of visits, a membership is unlimited until it expires, and a season pass covers a recurring event, with every use tracked on the Subscriptions tab. On every plan, buyers can also put tickets for several sessions in one cart and pay once.'],
                    ['question' => 'Can attendees comment on or rate a session?', 'answer' => 'Comments are open on events and on each part of an event\'s agenda on every plan, and each one waits for your approval before it shows. Post-event feedback, on Pro, emails attendees for a star rating and a comment after the event ends; on the hosted platform it needs your schedule\'s own email settings.'],
                    ['question' => 'Is the free plan enough for a small event?', 'answer' => 'For a free event, yes, at any size: registration has no attendee cap on the free plan, and every ticket scans at the door. Sched\'s free tier is for events under 50 attendees. Charging for tickets is what needs Pro.'],
                ],
            ],
            'whova' => [
                'name' => 'Whova',
                'key' => 'whova',
                'slug' => 'whova-alternative',
                'meta_title' => 'Whova Alternative With Transparent Pricing | Event Schedule',
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
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'Custom quotes', true],
                        ['Platform fees', '0%', '3% + $0.99 per paid ticket, plus a quoted fee', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Stripe (2.9% + $0.30)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes', false],
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
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'Referral contest, no commission', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'No (Zapier only)', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Email campaigns per event, and announcements', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No', true],
                        ['Remove branding', 'Yes (Pro)', 'No (not white-label)', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'AI writing assistant only', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Agenda widget', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'Yes (Sponsor Center, banners)', false],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
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
                        'description' => plan_price($this->planPrice()).'/mo flat vs opaque custom quotes requiring sales calls. No surprises, no negotiations.',
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
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google, Outlook and CalDAV sync included free. Whova requires a Zapier add-on for basic calendar connections.',
                        'icon' => 'calendar',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Custom CSS',
                        'description' => 'Full design control with custom CSS. Whova limits customization to their dashboard options.',
                        'icon' => 'code',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
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
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Sched', 'route' => 'marketing.compare_sched'],
                    ['name' => 'Accelevents', 'route' => 'marketing.compare_accelevents'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                ],
                'why_choose' => [
                    'summary' => 'Whova is built around a conference app, and its price is a quote. When what your events need most is a public list of sessions, registration and a mailing list, Event Schedule covers it at a price listed on the page.',
                    'points' => [
                        'Free to start and Pro at '.plan_price($this->planPrice()).'/mo, with no sales call before you see a number',
                        'No 3% + $0.99 on each paid ticket: on Pro only Stripe\'s or PayPal\'s own fee comes off',
                        'Tracks as sub-schedules of one conference schedule, which visitors can filter by, on Free',
                        'An agenda on each event page with a start and end time per part, added by hand on every plan',
                        'A calendar of every session to embed on the conference website, where Whova offers an agenda widget',
                        'Newsletters with A/B testing to a list that outlives any one event, plus a digest that mails subscribers each batch of new sessions',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Give each track a sub-schedule', 'description' => 'Make one schedule for the conference and a sub-schedule for each track or hall, so a visitor can narrow the program to the talks they came for. Neither costs anything.', 'doc' => 'creating-schedules#customize-subschedules'],
                    ['title' => 'Paste the agenda in', 'description' => 'Copy the session list from Whova or your website into AI import, or add a photo of the printed agenda, and the sessions come back as a list to check.', 'doc' => 'ai-import#text-import'],
                    ['title' => 'Name your speakers', 'description' => 'List each session\'s speakers on its Participants tab. A speaker who has no account yet is given a speaker page, theirs to claim whenever they sign in.', 'doc' => 'creating-events#participants'],
                    ['title' => 'Swap the agenda widget for the embed', 'description' => 'Replace the Whova widget on your site with the schedule\'s embed code, free, and every change you make shows up there by itself.', 'doc' => 'sharing#embed'],
                ],
                'limits' => [
                    ['tag' => 'No attendee app', 'title' => 'Guests use the web', 'text' => 'There is no native mobile app for attendees to download. Guests use the schedule and event pages in a phone browser, with nothing to install, and open their ticket\'s QR code from the confirmation email.'],
                    ['tag' => 'No matchmaking', 'title' => 'Attendees do not message each other', 'text' => 'Event Schedule has no attendee directory, matchmaking or one-to-one messaging, which is where Whova is strongest. What guests can do is comment on an event and post photos and videos to it, on every plan, once you approve them.'],
                    ['tag' => 'No virtual venue', 'title' => 'Streams happen elsewhere', 'text' => 'There is no built-in stage for virtual or hybrid sessions. A session held online shows the join link from your own video service, and with Outlook connected each one can be created as a Teams meeting.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Whova to Event Schedule?', 'answer' => 'Yes. Unlike Whova, there is no sales process or demo required. Just sign up, create a free schedule, and start adding events. AI import can parse pasted event details to speed things up.'],
                    ['question' => 'How does Event Schedule pricing compare to Whova?', 'answer' => 'Whova uses quote-based enterprise pricing that requires a sales call, quoted per event. Event Schedule Pro is a flat '.plan_price($this->planPrice()).'/mo with unlimited events and zero platform fees on ticket sales.'],
                    ['question' => 'Does Event Schedule offer attendee engagement features like Whova?', 'answer' => 'Yes. Fan videos and comments and newsletters with A/B testing are on every plan, and event polls and post-event feedback are on Pro, all without per-event pricing.'],
                    ['question' => 'Can I refund an attendee?', 'answer' => 'Yes, on Pro, alongside the paid ticketing that took the money. From the Sales page, a Stripe, PayPal or Razorpay sale can be refunded in full or in part and the money goes back through the provider; a partial refund keeps the ticket valid. A sale taken another way, such as cash or a payment link, is marked as refunded instead.'],
                    ['question' => 'Can I bring my Whova attendee list across?', 'answer' => 'Whova can export your sales data, and the email addresses in it can go into a schedule\'s newsletter list from a spreadsheet of up to 10,000 rows, on any plan. Holding them costs nothing; what is metered is each email sent, with a monthly allowance that grows with the plan and lifts entirely when the schedule uses its own mail server.'],
                    ['question' => 'Does a conference of several days fit on one event?', 'answer' => 'Yes. Switch on Multi-day event and the event runs across its dates, with the Agenda tab listing each part in order on the event page. When sessions need their own tickets or their own pages, make each one an event instead and gather them under sub-schedules.'],
                ],
            ],
            'accelevents' => [
                'name' => 'Accelevents',
                'key' => 'accelevents',
                'slug' => 'accelevents-alternative',
                'meta_title' => 'Accelevents Alternative With Instant Setup | Event Schedule',
                'tagline' => 'A simpler, more affordable alternative to Accelevents without enterprise pricing.',
                'description' => 'Compare Event Schedule with Accelevents: a free plan, Pro at '.plan_price($this->planPrice()).'/mo against paid plans from $7,500, setup in minutes, and open source code you can selfhost.',
                'keywords' => 'accelevents alternative, accelevents alternative free, event management platform, accelevents competitor, affordable event platform',
                'about' => 'Accelevents is an enterprise event management platform offering in-person, virtual, and hybrid event solutions. It targets mid-to-large organizations with badge printing, CRM integrations, and white-label capabilities.',
                'competitor_strengths' => [
                    'Enterprise-grade badge printing and onsite check-in with kiosk support',
                    'Native CRM integrations with Salesforce, HubSpot, and Marketo',
                    'Comprehensive virtual and hybrid event hosting with built-in streaming',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Limited free plan ($1.50 + 3.5% a ticket)', true],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'From $7,500 (Professional)', true],
                        ['Platform fees', '0%', 'No transaction fees (paid plans)', false],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Stripe/Square/Payflow', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes', false],
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
                        ['Individual tickets', 'Yes (Pro)', 'Yes', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (you schedule up to 5)', false],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'Tracking links, no commission', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'No (add-to-calendar only)', true],
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
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'No', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Yes', false],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (unlimited admins)', false],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'Yes', false],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (Enterprise only)', true],
                        ['Webhooks', 'Yes (Pro)', 'Yes (Enterprise only)', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                        ['Setup time', 'Minutes', 'Demo and account team on paid plans', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => plan_price($this->planPrice()).'/mo vs $7,500+',
                        'description' => 'Accelevents plans start from $7,500. Event Schedule starts free and Pro is billed by the month, with no enterprise contract to sign.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Instant Setup',
                        'description' => 'Start in minutes, with no demo or implementation team in front of you.',
                        'icon' => 'calendar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'No Sales Process',
                        'description' => 'Sign up and go. Accelevents sells its larger plans through a demo; here every plan is a published price.',
                        'icon' => 'globe',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google, Outlook and CalDAV sync included free. Accelevents only offers add-to-calendar links.',
                        'icon' => 'calendar',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
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
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Whova', 'route' => 'marketing.compare_whova'],
                    ['name' => 'Splash', 'route' => 'marketing.compare_splash'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                ],
                'why_choose' => [
                    'summary' => 'Accelevents is priced for mid-to-large organizations, from $7,500 for its Professional plan, with badge printing and CRM connectors to match. A team that needs a calendar, registration and ticket sales running this week can have them on Event Schedule without signing a contract.',
                    'points' => [
                        'Free registration and QR check-in from day one, and Pro at '.plan_price($this->planPrice()).'/mo once a ticket needs a price',
                        'Developer access on Pro, the REST API and webhooks both, where Accelevents keeps them for Enterprise',
                        'Online events on the free plan, rather than a paid add-on',
                        'Repeating series for online events as well as in-person ones',
                        'Payment lands in an account you hold with Stripe, PayPal, Payfast or Razorpay, and a refund is issued from the Sales page on Pro',
                        'Open source code to selfhost when policy says event data stays in-house, with every Enterprise feature included',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Publish without an implementation', 'description' => 'A schedule is live the moment you create it, with no onboarding project in front of it. Build the first event while the old platform is still running.', 'doc' => 'getting-started#create-schedule'],
                    ['title' => 'Copy each event over by link', 'description' => 'Give import from a link the address of an Accelevents event. The parser reads that page and hands back an event to review, using one request of the daily allowance.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'Export attendees, upload subscribers', 'description' => 'Take the attendee export from Accelevents and load its addresses into the newsletter list, ten thousand at most per file, on Free as well.', 'doc' => 'newsletters#importing-emails'],
                    ['title' => 'Point your CRM at webhooks', 'description' => 'Where Accelevents synced Salesforce or HubSpot for you, send sale and check-in webhooks to your own endpoint or an automation tool that writes to the CRM, on Pro.', 'doc' => 'developer/webhooks#setup'],
                ],
                'limits' => [
                    ['tag' => 'No badge printing', 'title' => 'Nothing to print at the door', 'text' => 'There are no badge templates, kiosks or onsite printing stations. Check-in is a phone camera reading the QR code on each ticket, on every plan, with a live check-in count on Pro.'],
                    ['tag' => 'No native CRM sync', 'title' => 'Salesforce and HubSpot need your own glue', 'text' => 'Event Schedule does not connect to Salesforce, HubSpot or Marketo out of the box. The REST API and webhooks on Pro expose sales, events and check-ins to whoever builds the link.'],
                    ['tag' => 'Teams of five', 'title' => 'Not an unlimited admin roster', 'text' => 'Accelevents allows unlimited admins, while team members here are an Enterprise feature capped at five per schedule on the hosted service. A selfhosted install has no such cap.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Accelevents to Event Schedule?', 'answer' => 'Yes. There is no demo or implementation process in front of any plan. Sign up, create a free schedule, and start adding events immediately. AI import can parse pasted event details to speed things up.'],
                    ['question' => 'How does Event Schedule pricing compare to Accelevents?', 'answer' => 'Accelevents\' Professional plan starts from $7,500 and Business from $13,500, with no transaction fees on those plans. Event Schedule has a free plan with unlimited events and free registration; paid ticket sales come with Pro, a flat '.plan_price($this->planPrice()).'/mo with unlimited events and zero platform fees.'],
                    ['question' => 'Can Event Schedule handle virtual and hybrid events like Accelevents?', 'answer' => 'Yes. Event Schedule supports online events with video links and integrates with any streaming platform. Combined with ticketing, QR check-ins, and newsletters, it covers both virtual and in-person needs.'],
                    ['question' => 'Does Event Schedule do reserved seating and payment plans?', 'answer' => 'Yes. Allocated seating is on Enterprise: a venue schedule draws its room once and buyers pick seats from the map, with a box office view for holds and phone bookings. Installment payments are on Pro and run on Stripe: the buyer pays the first part at checkout, the ticket is valid at once, and the rest is charged monthly to the saved card.'],
                    ['question' => 'Can staff have different levels of access?', 'answer' => 'Yes, on Enterprise. Admins handle the daily running, sales, waitlist and check-in dashboard included, while viewers can only look, see no sales and can still scan tickets at the door. Levels are the owner\'s to change, and the hosted service allows five members per schedule.'],
                    ['question' => 'What does a ticketed event cost to run, all in?', 'answer' => 'On Pro, '.plan_price($this->planPrice()).' a month plus your payment processor\'s own fee on each sale, such as Stripe\'s 2.9% + $0.30 per US card charge. There is no platform percentage and no implementation fee, and the '.$this->trialDays().'-day free trial lets you sell before the first charge.'],
                ],
            ],
            'tito' => [
                'name' => 'Tito',
                'key' => 'tito',
                'slug' => 'tito-alternative',
                'meta_title' => 'Tito Alternative With No Per-Ticket Fee | Event Schedule',
                'tagline' => 'A flat-rate alternative to Tito with calendar sync, newsletters, and zero per-ticket fees.',
                'description' => 'Compare Event Schedule with Tito. Get flat '.plan_price($this->planPrice()).'/mo pricing instead of 3% per ticket, plus calendar sync, newsletters, and selfhosting.',
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
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', '3% per ticket (cap 25 EUR)', true],
                        ['Platform fees', '0%', '3% per ticket', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Stripe/PayPal', true],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes (iOS/Android/web)', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes', false],
                        ['Check-in dashboard', 'Yes (Pro)', 'Yes', false],
                        ['Sale notifications', 'Yes (Pro)', 'Yes', false],
                        ['Sales data export', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Series of separate events, no repeat pattern', true],
                        ['Online events', 'Yes (Free)', 'Yes (via meeting links)', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                        ['Individual tickets', 'Yes (Pro)', 'Yes', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'No (messages you schedule)', true],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'UTM tracking, no commission', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Basic messaging only', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Yes', false],
                        ['Remove branding', 'Yes (Pro)', 'Yes (free)', false],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'Yes (widget mode)', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'No', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Checkout widget only', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Yes (unlisted, secret or invitation only)', false],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (4 roles)', false],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'No', true],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
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
                        'description' => plan_price($this->planPrice()).'/mo flat vs 3% per ticket. Sell 200 tickets at $30 and Tito\'s 3% comes to $180 that month.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google, Outlook and CalDAV sync included free. Tito has no calendar integration.',
                        'icon' => 'calendar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Keep your audience engaged with built-in newsletters. Tito only has basic transactional messaging.',
                        'icon' => 'mail',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Automatic Reminders',
                        'description' => 'Every ticket holder gets a reminder email before the event, with no message to write: on every plan, and on by default on Pro and Enterprise. Tito sends reminders only when you schedule them.',
                        'icon' => 'mail',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
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
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Pretix', 'route' => 'marketing.compare_pretix'],
                ],
                'why_choose' => [
                    'summary' => 'Tito fits a tech conference that sells its tickets once a year and wants nothing else. A schedule that runs events all year, with a calendar and a mailing list beside the checkout, is where a flat monthly plan starts to beat 3% a ticket.',
                    'points' => [
                        'Pro at '.plan_price($this->planPrice()).'/mo costs less than Tito\'s 3% once a month\'s ticket sales pass '.plan_price($this->planPrice() / 0.03),
                        'Recurring events with dates added or skipped, on Free, where a Tito series is a set of separate events',
                        'Payfast for South African rand and Razorpay for Indian rupees, beside Stripe and PayPal',
                        'Individual tickets on Pro, so each attendee in a group order gets a QR code of their own',
                        'A calendar of all your events to embed, where Tito embeds a checkout widget',
                        'Code you can read and selfhost, next to an API and webhooks on Pro for the scripts Tito users already write',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Keep Tito selling for now', 'description' => 'Add the event with the Tickets elsewhere tile and its button links to your Tito checkout, on every plan, so nothing already on sale has to move.', 'doc' => 'tickets#external'],
                    ['title' => 'Copy the event page across', 'description' => 'Hand the address of your Tito event to import from a link, and confirm the name, dates, venue and description it picked up.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'Bring registrations to the door', 'description' => 'Export registrations from Tito and import them as attendees on Pro, as many as 5,000 in one file. With Send Email on, each paid one gets a ticket your scanner reads.', 'doc' => 'tickets#importing-attendees'],
                    ['title' => 'Move your scripts to the API', 'description' => 'Create an API key, then point whatever listened to Tito\'s webhooks at ours, which fire on a sale, an event change or a check-in, on Pro.', 'doc' => 'developer/api#authentication'],
                ],
                'limits' => [
                    ['tag' => 'A monthly plan', 'title' => 'Quiet months still cost Pro', 'text' => 'Tito charges nothing in a month with no paid sales, whereas a priced ticket on Event Schedule rides on a '.plan_price($this->planPrice()).'/mo Pro plan whether or not anything sells. Below '.plan_price($this->planPrice() / 0.03).' of ticket sales a month, Tito\'s 3% is the smaller bill.'],
                    ['tag' => 'Branding is Pro', 'title' => 'Tito drops its name for free', 'text' => 'Tito removes its own branding at no charge, but a free schedule here carries Event Schedule branding. Pro takes it off every page, and custom CSS on Pro changes the rest of the look.'],
                    ['tag' => 'No scanning app', 'title' => 'The door runs in a browser', 'text' => 'Tito has check-in apps for iOS and Android, and Event Schedule has no app to install. Its scanner is a page in the phone\'s browser, on every plan, and it needs a connection to record each scan.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Tito to Event Schedule?', 'answer' => 'Yes. Create a free schedule and start adding events right away. AI import lets you paste event details to populate your listings quickly. No migration scripts required.'],
                    ['question' => 'How does Event Schedule pricing compare to Tito?', 'answer' => 'Tito charges per-ticket fees on each sale. Event Schedule charges zero platform fees at every plan level. The Pro plan is a flat '.plan_price($this->planPrice()).'/mo for unlimited ticketing and events.'],
                    ['question' => 'Does Event Schedule support developer-friendly features like Tito?', 'answer' => 'Yes. Event Schedule offers a REST API, webhooks, custom CSS, and is fully open source. You can selfhost it, fork it, or integrate it with your own tools and workflows.'],
                    ['question' => 'Can I take PayPal as well as Stripe?', 'answer' => 'Yes, on Pro, which is the plan that lets a ticket carry a price at all. Connect your own PayPal account in Settings > Payment Methods, next to Stripe if you like, and choose the method per event; the credentials are checked with PayPal before they are saved. Refunds, full or partial, go back through PayPal or Stripe from the Sales page. Installment plans run on Stripe only.'],
                    ['question' => 'Does Event Schedule cap its fee per ticket the way Tito does?', 'answer' => 'There is no per-ticket fee to cap. Tito\'s 3% stops at 25 EUR on an expensive ticket, while Event Schedule takes nothing from any ticket, so a 500 EUR workshop and a 10 EUR meetup both carry 0%. Your payment processor\'s own fee still applies.'],
                    ['question' => 'Can one event carry several ticket types and a timetable of talks?', 'answer' => 'Yes. One event holds several ticket types, each with its own quantity and sales dates, and the priced ones sell on Pro along with promo codes and add-ons. The Agenda tab lists each talk with its time on the event page, free on every plan.'],
                ],
            ],
            'addevent' => [
                'name' => 'AddEvent',
                'key' => 'addevent',
                'slug' => 'addevent-alternative',
                'meta_title' => 'AddEvent Alternative With Ticketing Built In | Event Schedule',
                'tagline' => 'A complete event platform that goes beyond "Add to Calendar" buttons.',
                'description' => 'Compare Event Schedule with AddEvent: ticketing, public event pages and a live calendar feed, free, with Pro at '.plan_price($this->planPrice()).'/mo against calendar buttons at $29/mo.',
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
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'From $36/mo (monthly billing)', true],
                        ['Platform fees', '0%', 'N/A (no ticketing)', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'N/A (no ticketing)', true],
                    ],
                    'Events & Calendar' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'No', true],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'N/A (no ticketing)', true],
                        ['QR check-ins', 'Yes (Free)', 'No', true],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (limited)', true],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes (join links, Zoom and webinar integrations)', false],
                        ['Event polls', 'Yes (Pro)', 'No', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'No', true],
                        ['Post-event feedback', 'Yes (Pro)', 'No', true],
                        ['Add to calendar buttons', 'Yes (Free)', 'Yes (core feature)', false],
                        ['Subscription calendars', 'Yes (Free)', 'Yes (core feature)', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (every plan)', false],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'Import only (one-way)', true],
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
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'No', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'No', true],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
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
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => plan_price($this->planPrice()).'/mo vs $36/mo',
                        'description' => 'AddEvent Small Business is $36/mo on monthly billing, less when paid yearly, and has no ticketing. Event Schedule takes free registrations on the free plan, and paid tickets are on Pro.',
                        'icon' => 'percent',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details and AI extracts everything automatically. Not available on AddEvent.',
                        'icon' => 'ai',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Bidirectional Google, Outlook and CalDAV sync. AddEvent only imports one-way.',
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
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Google Calendar', 'route' => 'marketing.compare_google_calendar'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                ],
                'why_choose' => [
                    'summary' => 'AddEvent puts a calendar button on pages that live somewhere else. Event Schedule is the page itself: the listing, the sign-up form and the checkout, with a calendar subscription built in.',
                    'points' => [
                        'Free registration with no cap on sign-ups, where AddEvent\'s free plan stops at 20 RSVPs',
                        'Priced tickets on Pro and QR scanning at the door on every plan, neither of which AddEvent offers',
                        'Google, Outlook and CalDAV kept in step in both directions, not imported one way',
                        'A live feed followers subscribe to once, and a one-date calendar file on each event page as well',
                        'Newsletters to everyone who follows the schedule, rather than RSVP emails alone',
                        'Webhooks on Pro that post straight to your own address, without routing through Zapier',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Read your events in from a link', 'description' => 'Run import from a link on the page or calendar address where your events are listed. An .ics or webcal address is copied directly, a year ahead, and costs nothing from the AI allowance.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'Switch on registration', 'description' => 'Where AddEvent collected RSVPs, press Free registration on the event\'s Tickets tab. Leave the limit blank for unlimited sign-ups.', 'doc' => 'tickets#registration'],
                    ['title' => 'Give subscribers the new feed', 'description' => 'Each schedule has a public iCal feed. Share it where your AddEvent subscription link used to go, and calendars that add it re-read it on their own.', 'doc' => 'sharing#calendar-feeds'],
                    ['title' => 'Embed the calendar on your site', 'description' => 'Put the schedule\'s embed code where the AddEvent widget sat. It loads live, so an event you add or move appears on your site with nothing to update.', 'doc' => 'sharing#embed'],
                ],
                'limits' => [
                    ['tag' => 'No buttons for other sites', 'title' => 'Add to Calendar lives on our pages', 'text' => 'Event Schedule does not make standalone calendar buttons to paste into your own pages, emails or ads. Add to Calendar sits on each of its event pages, and the embeddable calendar and live feed cover your website and your subscribers.'],
                    ['tag' => 'No marketing connectors', 'title' => 'Webhooks, not integrations', 'text' => 'There are no ready-made connectors for email marketing or CRM platforms, and no Zapier app. On Pro, a REST API and webhooks let you wire one up yourself.'],
                    ['tag' => 'Domains are Enterprise', 'title' => 'Your own address is the top plan', 'text' => 'Serving a schedule from your own domain is part of Enterprise, where AddEvent sets one up through its support team. On Free and Pro every schedule has an address of its own on a subdomain.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from AddEvent to Event Schedule?', 'answer' => 'Yes. Create a free schedule and add your events manually or via AI import. Event Schedule provides a full event management platform, not just a calendar widget.'],
                    ['question' => 'How does Event Schedule pricing compare to AddEvent?', 'answer' => 'AddEvent\'s Small Business plan is $36/mo on monthly billing, and less when paid yearly. Event Schedule Pro is '.plan_price($this->planPrice()).'/mo with zero platform fees. Even the free plan includes unlimited events, two-way calendar sync, unlimited free registration and newsletters.'],
                    ['question' => 'How is Event Schedule different from AddEvent?', 'answer' => 'AddEvent focuses on calendar add-to-calendar buttons and embeds. Event Schedule is a complete event management platform with ticketing, QR check-ins, newsletters, AI import, and public schedule pages.'],
                    ['question' => 'Can people subscribe to my calendar, like an AddEvent subscription calendar?', 'answer' => 'Yes, free. Every schedule has a live calendar feed that visitors subscribe to from the sign-up panel or an event\'s Add to Calendar menu, and every event page offers Add to Calendar for a single date. Their calendar re-reads the feed, so a moved date updates itself. AddEvent remains the specialist for calendar buttons placed on other sites and in email campaigns.'],
                    ['question' => 'Can events I keep in Google Calendar stay there?', 'answer' => 'Yes. Connect the calendar under Integrations and pick a sync direction: from Google, to Google, or both ways. Events that arrive from Google can wait as drafts until you publish them, and Outlook offers the same choices, free on every plan.'],
                    ['question' => 'Can visitors join my mailing list from my own site?', 'answer' => 'Yes, free. The embed dialog offers a sign-up form beside the calendar, so visitors to your site can join the schedule\'s mailing list without leaving it. Once they confirm, an automatic digest tells them about new events.'],
                ],
            ],
            'pretix' => [
                'name' => 'Pretix',
                'key' => 'pretix',
                'slug' => 'pretix-alternative',
                'meta_title' => 'Pretix Alternative: Open Source, Flat Pricing | Event Schedule',
                'tagline' => 'A broader open source event platform with flat pricing and more built-in features.',
                'description' => 'Compare Event Schedule with Pretix. Get flat '.plan_price($this->planPrice()).'/mo pricing instead of per-ticket fees, plus calendar sync, newsletters, AI features, and fan engagement.',
                'keywords' => 'pretix alternative, pretix alternative free, open source ticketing alternative, pretix competitor, event platform open source',
                'about' => 'Pretix is an open source ticketing platform (AGPLv3) based in Germany, focused on ticket sales for conferences, festivals, and exhibitions. It offers a selfhosted Community Edition and a paid hosted service with per-ticket pricing.',
                'competitor_strengths' => [
                    'Open source (AGPLv3) with full selfhosted Community Edition',
                    'Seating plan editor with interactive graphical seat selection',
                    'Offline-capable check-in app (pretixSCAN) and point-of-sale system',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Hosted: 500 free tickets a year, then 0.50 EUR each; or selfhost', true],
                        ['Hosted pricing', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', '2.5%/ticket (cap 15 EUR)', true],
                        ['Platform fees', '0%', '2.5% per ticket', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Stripe/PayPal/Mollie', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes (pretixSCAN, offline-capable)', false],
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
                        ['Individual tickets', 'Yes (Pro)', 'Yes', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (email rules you set up)', false],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'Tracking links, no commission', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'No', true],
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
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'No', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Widget with a month, week or list of events', false],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'No', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (unlimited teams)', false],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'No', true],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
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
                        'title' => 'Flat '.plan_price($this->planPrice()).'/mo Pricing',
                        'description' => 'No per-ticket fees ever. Pretix charges 2.5% per ticket on their hosted plan.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google, Outlook and CalDAV sync included free. Pretix has no calendar integration.',
                        'icon' => 'calendar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Engage your audience directly. Pretix requires a separate MailChimp integration.',
                        'icon' => 'mail',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details and AI extracts everything automatically. Not available on Pretix.',
                        'icon' => 'ai',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
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
                        'description' => 'Simpler to selfhost than Pretix: just PHP and MySQL, where Pretix wants PostgreSQL and Redis in production.',
                        'icon' => 'globe',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Tito', 'route' => 'marketing.compare_tito'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                ],
                'why_choose' => [
                    'summary' => 'Pretix publishes its code too, and either one can be run on hardware you control, which leaves scope and upkeep to decide it. Pretix sells tickets; Event Schedule adds the public calendar and the mailing list around the shop, on a PHP and MySQL stack.',
                    'points' => [
                        'No 2.5% per ticket on the hosted service: Pro is '.plan_price($this->planPrice()).'/mo with 0% on every sale',
                        'A selfhosted install that needs a PHP host and a MySQL database, and updates itself in one click from inside the app',
                        'A browsable public calendar with search and filters, and an embed of it, on every plan',
                        'Newsletters sent from the app itself, and subscribers told about new dates without you writing to them',
                        'Event polls and post-event feedback on Pro, neither of which Pretix has',
                        'AI import that turns a pasted listing, a link or a flyer into events, 50 a day on Free and unlimited when selfhosted',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Pick where it runs', 'description' => 'Open a free account on eventschedule.com, or put the release on a PHP and MySQL server of your own, where every Enterprise feature comes included.', 'doc' => 'selfhost/installation#requirements'],
                    ['title' => 'Import the shop\'s events', 'description' => 'Give import from a link your Pretix shop address and look over what it found. A single read can return as many as 100 events.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'Turn an event series into one recurring event', 'description' => 'A Pretix event series becomes one recurring event here: pick the pattern, from daily to yearly or the second Tuesday of each month, then add or drop single dates.', 'doc' => 'creating-events#recurring'],
                    ['title' => 'Rebuild products and vouchers', 'description' => 'Each product with a quota becomes a ticket type with a quantity, and vouchers become promo codes with usage limits and expiry dates, on Pro.', 'doc' => 'tickets#promo-codes'],
                ],
                'limits' => [
                    ['tag' => 'Online-only scanner', 'title' => 'No offline check-in app', 'text' => 'pretixSCAN keeps working without a connection, and the Event Schedule scanner does not: it is a browser page that records each scan as it happens. It runs on any phone with a camera, free on every plan, but the door needs Wi-Fi or mobile data.'],
                    ['tag' => 'No point of sale', 'title' => 'No till for the counter', 'text' => 'There is no point-of-sale system for selling at a desk or bar. Door sales go through the same online checkout as any other, or are added afterwards with the attendee import on Pro.'],
                    ['tag' => 'No Mollie', 'title' => 'Stripe, PayPal, Payfast or Razorpay', 'text' => 'Mollie is one of the payment providers Pretix supports, and Event Schedule has no Mollie connection. Organizers who rely on it can use Stripe, PayPal or an Invoice Ninja payment link instead.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Pretix to Event Schedule?', 'answer' => 'Yes. Create a free schedule and start adding events immediately. No data migration or server setup required. AI import can parse pasted event details to speed up the process.'],
                    ['question' => 'How does Event Schedule pricing compare to Pretix?', 'answer' => 'Pretix Hosted charges per-ticket fees on each sale. Event Schedule Pro is a flat '.plan_price($this->planPrice()).'/mo with zero platform fees. Both platforms are open source, but Event Schedule is simpler to selfhost.'],
                    ['question' => 'Is Event Schedule easier to selfhost than Pretix?', 'answer' => 'Yes. A production Pretix install wants PostgreSQL and Redis, with Docker as the documented route, and more server administration. Event Schedule runs on standard PHP hosting with MySQL, making it accessible to a much wider range of hosting environments.'],
                    ['question' => 'Does Event Schedule have seating plans?', 'answer' => 'Yes, on Enterprise, and a selfhosted install resolves to Enterprise, so seating is included when you run it yourself. A venue schedule draws its room once, with sections, rows, tables, standing areas and wheelchair spaces, and buyers pick their seats from the map. On the hosted Free and Pro plans, ticket types have a name, a price and a quantity, without assigned seats.'],
                    ['question' => 'How do the two open source licences differ?', 'answer' => 'Pretix is published under the AGPLv3. Event Schedule\'s licence is the Attribution Assurance License, which is why a selfhosted install carries a small Event Schedule credit on its pages. Under both you can read the code, change it and run it yourself.'],
                    ['question' => 'Can a festival sell one pass for every day?', 'answer' => 'Yes, on Pro. A festival pass admits its holder once to each event it covers, a season pass covers the dates of a recurring event, and every use is tracked per pass. Without a pass, a buyer can still add several days to one cart and check out once.'],
                ],
            ],

            'humanitix' => [
                'name' => 'Humanitix',
                'key' => 'humanitix',
                'slug' => 'humanitix-alternative',
                'meta_title' => 'Humanitix Alternative With No Booking Fees | Event Schedule',
                'tagline' => 'Flat '.plan_price($this->planPrice()).'/mo pricing and zero platform fees, so only your payment processor\'s fee comes off a paid ticket.',
                'description' => 'Compare Event Schedule with Humanitix. Get flat '.plan_price($this->planPrice()).'/mo pricing instead of per-ticket fees, plus calendar sync, newsletters, AI features, and selfhosting.',
                'keywords' => 'humanitix alternative, humanitix alternative free, event ticketing platform, humanitix competitor, affordable event platform',
                'about' => 'Humanitix is a ticketing platform that donates profits to charity, primarily children\'s education. It charges a per-ticket booking fee (5% + $1.29 a ticket in the US) with no subscription plans. It offers ticketing, QR check-in, promo codes, and embeddable widgets.',
                'competitor_strengths' => [
                    'Profits go to charity (children\'s education and humanitarian causes)',
                    'No subscription required with pay-as-you-go per-ticket pricing',
                    'Reduced booking fees for nonprofits',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free for free events', false],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'No subscription (per-ticket only)', true],
                        ['Platform fees', '0%', 'Booking fee on paid tickets (5% + $1.29 in the US)', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Included in platform fee', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes', false],
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
                        ['Individual tickets', 'Yes (Pro)', 'Yes', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'No (emails you schedule)', true],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'Affiliate codes, no commission', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'No', true],
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
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'No', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Widget only', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Private (link only), no password', true],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'No', true],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
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
                        'title' => 'Flat '.plan_price($this->planPrice()).'/mo Pricing',
                        'description' => 'No per-ticket fees ever. Humanitix charges a booking fee on every paid ticket (5% + $1.29 a ticket in the US).',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google, Outlook and CalDAV sync included free. Humanitix has no calendar integration.',
                        'icon' => 'calendar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Engage your audience directly with built-in newsletters. Not available on Humanitix.',
                        'icon' => 'mail',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details and AI extracts everything automatically. Not available on Humanitix.',
                        'icon' => 'ai',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
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
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                    ['name' => 'Eventzilla', 'route' => 'marketing.compare_eventzilla'],
                ],
                'why_choose' => [
                    'summary' => 'Humanitix pays for its giving out of the fee each buyer is charged. Here nothing is added to the ticket at all, so what would have gone in fees stays with you, and where it goes is your decision.',
                    'points' => [
                        'No booking fee on a paid ticket, where Humanitix adds one (5% + $1.29 a ticket in the US)',
                        'Newsletters from your own schedule, which Humanitix does not have, and a digest of new events mailed to subscribers',
                        'Branding removal and custom CSS on Pro, where Humanitix offers neither',
                        'Your own domain on Enterprise, which Humanitix does not support',
                        'Two-way calendar sync with Google, Outlook or CalDAV, free',
                        'Event polls and post-event ratings on Pro, so you hear from the people who came',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Leave live events where they are', 'description' => 'An event already selling on Humanitix can sit on your schedule with the Tickets elsewhere tile, its button linking to the Humanitix page, until its sales close.', 'doc' => 'tickets#external'],
                    ['title' => 'Import the next ones by link', 'description' => 'For events not yet on sale, give each Humanitix address to import from a link and fix whatever the parser misread.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'Set up your own payment account', 'description' => 'Humanitix processed payments inside its fee. Here buyers pay into a Stripe or PayPal account in your name, connected once under Settings > Payment Methods.', 'doc' => 'tickets#payment-setup'],
                    ['title' => 'Bring past attendees onto your list', 'description' => 'Export attendee data from Humanitix and upload the addresses as newsletter subscribers, one file holding as many as ten thousand.', 'doc' => 'newsletters#importing-emails'],
                ],
                'limits' => [
                    ['tag' => 'No built-in giving', 'title' => 'Fees do not go to charity', 'text' => 'Event Schedule does not pass a share of its revenue to a cause, the way Humanitix gives away its profits. With no booking fee on your tickets, any donation is yours to make, to the cause you pick.'],
                    ['tag' => 'Paid tickets need Pro', 'title' => 'A subscription instead of pay-as-you-go', 'text' => 'Humanitix asks for no subscription, while charging for a ticket here takes Pro at '.plan_price($this->planPrice()).'/mo. Free registration stays free and unlimited on every plan.'],
                    ['tag' => 'Processing is separate', 'title' => 'You hold the payment account', 'text' => 'Humanitix folds payment processing into its fee, but here Stripe or PayPal charge their own fee to the account you connect. You need that account before the first paid sale, and the money settles straight into it.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Humanitix to Event Schedule?', 'answer' => 'Yes. Create a free schedule and start adding events right away. With zero platform fees, you keep more of your ticket revenue and can donate directly to causes you care about on your own terms, or ask your own buyers for a donation at checkout on Pro.'],
                    ['question' => 'How does Event Schedule pricing compare to Humanitix?', 'answer' => 'Humanitix charges a per-ticket booking fee on every paid event (5% + $1.29 a ticket in the US). Event Schedule charges zero platform fees at every plan level. The Pro plan is a flat '.plan_price($this->planPrice()).'/mo regardless of ticket volume.'],
                    ['question' => 'Does Humanitix donate to charity while Event Schedule does not?', 'answer' => 'Humanitix donates its profits to charity, which is funded by per-ticket fees on your sales. With Event Schedule, you keep 100% of your ticket revenue (minus Stripe or PayPal processing) and can donate to causes you choose directly. On Pro you can also ask buyers for a donation at checkout, and a free ticket with donations on becomes pay what you want.'],
                    ['question' => 'Can buyers pay in installments?', 'answer' => 'Yes, on Pro, with Stripe. You switch it on per event; the buyer pays the first part at checkout, the ticket is valid at once, and the rest is charged monthly to the saved card. A plan that falls behind shows a hold at the door instead of admitting the ticket, and a refund is issued payment by payment, in full.'],
                    ['question' => 'Can I offer a lower price for groups?', 'answer' => 'Yes, and it costs nothing extra. A ticket type can carry volume discounts, so a buyer taking several tickets in one order pays less for each, and every type can open and close on its own sales dates. Promo codes, with their own usage limits and expiry, are on Pro.'],
                    ['question' => 'Will my attendees still get a ticket with a QR code?', 'answer' => 'Yes. Every ticket and every free registration comes with a confirmation email carrying a QR code, and the door scanner reads it on any plan. A guest can cancel a free registration from the ticket page linked in that email, which frees the place for someone else.'],
                ],
            ],

            'eventzilla' => [
                'name' => 'Eventzilla',
                'key' => 'eventzilla',
                'slug' => 'eventzilla-alternative',
                'meta_title' => 'Eventzilla Alternative, No Per-Ticket Fees | Event Schedule',
                'tagline' => 'A flat-rate open source alternative to Eventzilla with zero per-ticket fees.',
                'description' => 'Compare Event Schedule with Eventzilla. Get flat '.plan_price($this->planPrice()).'/mo pricing instead of per-ticket fees, plus calendar sync, newsletters, AI features, and open source.',
                'keywords' => 'eventzilla alternative, eventzilla alternative free, event registration platform, eventzilla competitor, affordable event platform',
                'about' => 'Eventzilla is an event registration and ticketing platform offering per-ticket pricing across multiple tiers. Plans run from $1.50 a registration (Basic) to 2.9% + $1.50 (Plus), with custom annual pricing on Scale. It includes features like badge printing, speaker management, live streaming, and CRM integrations.',
                'competitor_strengths' => [
                    'Badge printing with customizable templates and QR codes',
                    'Speaker and session management for conferences',
                    'Native integrations with Zapier, HubSpot, and Salesforce',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free for free events only', true],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', '$1.50/ticket + up to 2.9%', true],
                        ['Platform fees', '0%', '1.9% to 2.9% (Pro/Plus plans)', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Stripe/PayPal (separate fees)', true],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Unlimited ticket sales', 'Yes (Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes (kiosk mode)', false],
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
                        ['Individual tickets', 'Yes (Pro)', 'Yes', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (2 days before; 7 days on Pro and up)', false],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes (donation tickets)', false],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'No', true],
                        ['CalDAV sync', 'Yes (Free)', 'No', true],
                        ['Newsletters', 'Yes (Free)', 'Yes (email invitations and campaigns)', false],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Yes (Plus and Scale)', false],
                        ['Remove branding', 'Yes (Pro)', 'Yes (Plus and Scale)', false],
                        ['Custom fields', 'Yes (Pro)', 'Yes', false],
                        ['Custom CSS', 'Yes (Pro)', 'No', true],
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['AI suite (event parsing, flyer gen, style gen)', 'Parsing on Free, flyer and style on Enterprise', 'No', true],
                        ['Event graphics gen', 'Yes (Free)', 'No', true],
                        ['Sub-schedules', 'Yes (Free)', 'No', true],
                        ['Fan videos & comments', 'Yes (Free)', 'No', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Widget only', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Yes', false],
                        ['Availability management', 'Yes (Enterprise)', 'No', true],
                        ['WhatsApp event creation', 'Yes (Enterprise)', 'No', true],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes', false],
                        ['Carpool matching', 'Yes (Pro)', 'No', true],
                        ['Sponsor/partner logos', 'Yes (Pro)', 'Yes', false],
                        ['Scheduled graphic emails', 'Yes (Enterprise)', 'No', true],
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
                        'title' => 'Flat '.plan_price($this->planPrice()).'/mo Pricing',
                        'description' => 'No per-ticket fees ever. Eventzilla charges $1.50/ticket plus up to 2.9% in platform fees.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Calendar Sync',
                        'description' => 'Two-way Google, Outlook and CalDAV sync included free. Eventzilla has no calendar integration.',
                        'icon' => 'calendar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Newsletters Beyond One Event',
                        'description' => 'Write newsletters to a subscriber list that outlives any one event, with A/B testing, on every plan.',
                        'icon' => 'mail',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details and AI extracts everything automatically. Not available on Eventzilla.',
                        'icon' => 'ai',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
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
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Humanitix', 'route' => 'marketing.compare_humanitix'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                ],
                'why_choose' => [
                    'summary' => 'Eventzilla bills by the registration and adds a percentage on its Pro and Plus plans, with badge printing and CRM links aimed at larger conferences. For organizers who mostly need sign-ups, a calendar and a checkout, a flat plan with no cut is the simpler bill.',
                    'points' => [
                        'No $1.50 a ticket and no percentage on top: a paid ticket on Pro carries only your processor\'s fee',
                        'A Participants tab for speakers and an Agenda tab for timed sessions on every event, both free',
                        'A newsletter to your whole subscriber list, with A/B testing, on the free plan',
                        'Custom CSS on Pro, which Eventzilla does not offer',
                        'Fan photos, videos and comments on each event, free, shown once you approve them',
                        'Google, Outlook and CalDAV sync in both directions on the free plan',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Read in your Eventzilla pages', 'description' => 'Paste the address of an Eventzilla listing into import from a link. A page with several events on it comes back as a list to tick through.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'Move speakers and sessions', 'description' => 'Add each speaker on the Participants tab and each session as a line on the Agenda tab, with its own start and end time.', 'doc' => 'creating-events#participants'],
                    ['title' => 'Recreate the registration questions', 'description' => 'A question attached to a ticket type works on every plan. Questions for the whole order, or for each attendee, are part of custom fields on Pro.', 'doc' => 'tickets#checkout-fields'],
                    ['title' => 'Import people already registered', 'description' => 'Registrants for upcoming dates can be imported as attendees from a CSV on Pro, matched to your ticket types by name, without charging anyone again.', 'doc' => 'tickets#importing-attendees'],
                ],
                'limits' => [
                    ['tag' => 'No badges', 'title' => 'No badge templates to print', 'text' => 'Eventzilla prints name badges from templates with QR codes on them, and Event Schedule has no badge design or printing. Each guest\'s QR code arrives on the ticket in their confirmation email instead.'],
                    ['tag' => 'Password events are Enterprise', 'title' => 'A password needs the top plan', 'text' => 'Eventzilla can protect an event with a password; here Unlisted events with an optional password, and Internal events for members only, are on Enterprise. Free and Pro offer Public and Draft.'],
                    ['tag' => 'No live streaming', 'title' => 'Bring your own stream', 'text' => 'Eventzilla can stream an online event itself, and Event Schedule does not host video. An online event here shows the meeting or stream link from whatever service you already use.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Eventzilla to Event Schedule?', 'answer' => 'Yes. Create a free schedule and add your events manually or via AI import. Share your new schedule URL and start selling tickets immediately. No complex migration needed.'],
                    ['question' => 'How does Event Schedule pricing compare to Eventzilla?', 'answer' => 'Eventzilla charges $1.50/ticket plus up to 2.9% in platform fees depending on the plan. Event Schedule Pro is a flat '.plan_price($this->planPrice()).'/mo with zero platform fees, making costs predictable regardless of ticket volume.'],
                    ['question' => 'Can Event Schedule handle registration features like Eventzilla?', 'answer' => 'Yes. Free registration and QR check-in are on every plan, and paid tickets are on Pro. Custom fields, the live check-in dashboard, ticket waitlists and sales CSV export are on Pro. It also adds features Eventzilla lacks, like two-way Google Calendar sync, newsletters and AI event import.'],
                    ['question' => 'Can someone buy tickets for several events in one order?', 'answer' => 'Yes, on every plan. A buyer adds tickets for several of your events to one cart and pays once, through Stripe or PayPal, and gets one order page with every ticket on it. Each event in the cart is checked on its own, so a priced ticket needs that schedule to be on Pro.'],
                    ['question' => 'What does a year cost next to Eventzilla?', 'answer' => 'Eventzilla charges per registration, from $1.50 on Basic to 2.9% + $1.50 on Plus, and quotes its Scale plan by the year. Here the Pro plan runs '.plan_price($this->planPrice()).' a month and the Enterprise plan '.plan_price($this->planPrice(true)).' a month, and neither takes a share of ticket sales. A selfhosted install has no subscription at all.'],
                    ['question' => 'Does each person in a group booking get their own ticket?', 'answer' => 'Yes, with Individual tickets on Pro: every attendee in the order gives their own details and receives their own confirmation and QR code. For free registration the same per-guest option is free on every plan.'],
                ],
            ],

            // Timely cells checked 2026-09-23 against time.ly/pricing, time.ly/event-management-software-features,
            // help.time.ly (ticket fees, import feeds, private calendars). Two-way sync was not found and is not claimed.
            'timely' => [
                'name' => 'Timely',
                'key' => 'timely',
                'slug' => 'timely-alternative',
                'meta_title' => 'Timely Alternative With a Free Plan | Event Schedule',
                'tagline' => 'An event calendar with ticketing built in, a free plan, and zero platform fees.',
                'description' => 'Compare Event Schedule with Timely: a free plan and published prices where Timely quotes on request, zero platform fees on tickets, and calendar sync both ways.',
                'keywords' => 'timely alternative, time.ly alternative, timely event calendar alternative, event calendar software, timely competitor',
                'about' => 'Timely (time.ly) is hosted event calendar and event management software used by nonprofits, schools, universities, tourism boards and media companies. Its plans are billed annually, and registration and ticketing are sold as an add-on.',
                'competitor_strengths' => [
                    'Many calendar views, including stream, tile, posterboard, map and year',
                    'A registration and ticketing add-on with time-slot bookings, badge printing and QR check-in',
                    'Built for institutions, with WCAG 2.1 AA, SSO on Enterprise and community event submissions',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'No', true],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'Not published (request pricing or book a demo)', true],
                        ['Platform fees', '0%', '2.5% + $0.90 per paid ticket (add-on)', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Timely Pay/Stripe/Square/PayPal/Authorize.net', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes (paid add-on)', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes (add-on)', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes (add-on)', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes ($0.90 per registration)', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes (add-on)', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (with the ticketing add-on)', false],
                        ['Donations / pay what you want', 'Yes (Pro)', 'No', true],
                        ['Promoter links with commission', 'Yes (Pro)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'Imports feeds (two-way sync not listed)', true],
                        ['Newsletters', 'Yes (Free)', 'Mailchimp integration (Growth plan and up)', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Yes (Professional and up)', false],
                        ['Remove branding', 'Yes (Pro)', 'Yes (Enterprise)', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes (add-on, up to 8)', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes (Professional and up)', true],
                    ],
                    'Unique Features' => [
                        ['Embeddable calendar', 'Yes (Free)', 'Yes', false],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Yes (logged-in users only)', false],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (3 on Growth, 4 on Professional, custom on Enterprise)', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (Enterprise)', true],
                        ['Webhooks', 'Yes (Pro)', 'Yes (Enterprise)', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'A Free Plan',
                        'description' => 'Event Schedule has a free plan with unlimited events and the embeddable calendar. Every Timely plan is paid and billed annually, at a price Timely quotes on request.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => '0% Platform Fees',
                        'description' => 'Timely\'s ticketing add-on charges 2.5% + $0.90 per paid ticket and $0.90 per free registration. Event Schedule takes no platform fee on any plan.',
                        'icon' => 'percent',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free. Timely imports calendar feeds on an hourly schedule and does not list two-way sync.',
                        'icon' => 'calendar',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Email the people who follow your schedule from inside the app. Timely connects to Mailchimp from its Growth plan up.',
                        'icon' => 'mail',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'API and Webhooks on Pro',
                        'description' => 'The REST API and webhooks come with Pro at '.plan_price($this->planPrice()).'/mo. On Timely both are Enterprise features.',
                        'icon' => 'code',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Read the code and selfhost it on your own server. Timely is a hosted service only.',
                        'icon' => 'globe',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'The Events Calendar', 'route' => 'marketing.compare_the_events_calendar'],
                    ['name' => 'Tockify', 'route' => 'marketing.compare_tockify'],
                    ['name' => 'AddEvent', 'route' => 'marketing.compare_addevent'],
                ],
                'why_choose' => [
                    'summary' => 'Timely is calendar software for institutions, billed by the year, with registration and ticketing sold as an add-on. Event Schedule starts on a free plan, and the embed, calendar sync and unlimited free registration are part of it rather than extras.',
                    'points' => [
                        'A free plan, where every Timely plan is paid and quoted on request',
                        'Free registrations cost nothing per person, against $0.90 each on Timely\'s ticketing add-on',
                        'Sub-schedules for departments or programs, free, and an embed that can show just one of them',
                        'A request form for community submissions on every plan, with your approval before a submitted event goes live',
                        'Edits made in Google Calendar, Outlook or a CalDAV calendar come back to the schedule, and edits made here go out',
                        'Pro billed by the month at '.plan_price($this->planPrice()).' after a '.$this->trialDays().'-day trial, with the REST API and webhooks that Timely keeps for Enterprise',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Open a schedule for the calendar', 'description' => 'One schedule for each public calendar, free. Departments, venues or programs become sub-schedules, and each one gets a page of its own.', 'doc' => 'creating-schedules#customize-subschedules'],
                    ['title' => 'Read the feeds Timely was reading', 'description' => 'If your Timely calendar pulls from a Google or Outlook calendar or an .ics feed, paste that same address into Import from a link. A feed is read without AI and uses none of the daily allowance.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'Swap the embed code', 'description' => 'Replace the Timely embed on your website with the iframe from Embed Schedule, set to the month calendar or the list. Embedding is free on every plan.', 'doc' => 'sharing#embed'],
                    ['title' => 'Move ticketing off the add-on', 'description' => 'Free registration needs no plan and has no charge per person. Priced tickets are on Pro and settle into your own Stripe or PayPal account.', 'doc' => 'tickets#registration'],
                ],
                'limits' => [
                    ['tag' => 'Two calendar layouts', 'title' => 'Month or list, not posterboard', 'text' => 'Timely can show a calendar as a stream, tiles, a posterboard, a map or a whole year. A schedule here has a month calendar and a list, and a page can embed both, each in its own frame.'],
                    ['tag' => 'No single sign-on', 'title' => 'Staff keep their own logins', 'text' => 'Timely\'s Enterprise plan signs staff in through SSO, and Event Schedule has no SAML or SSO connection. Each team member signs in to an account of their own, with two-factor sign-in available.'],
                    ['tag' => 'No badge printing', 'title' => 'QR tickets, not name badges', 'text' => 'Timely\'s ticketing add-on prints attendee badges, which Event Schedule does not do. Every registration comes with a QR ticket that the door scanner checks on any plan, and the live check-in count is on Pro.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Timely to Event Schedule?', 'answer' => 'Yes. Create a free schedule, then paste event details or upload a flyer and the AI parser fills in the event for you to check. Visitors can follow the schedule, sign up by email, or subscribe to its live calendar feed.'],
                    ['question' => 'How does Event Schedule pricing compare to Timely?', 'answer' => 'Timely publishes no plan prices (Growth and Professional are quoted on request, Enterprise after a demo) and has no free plan, and its ticketing add-on charges 2.5% + $0.90 per paid ticket. Event Schedule has a free plan, and Pro is '.plan_price($this->planPrice()).'/mo with zero platform fees on ticket sales.'],
                    ['question' => 'Can I embed the calendar on my website like Timely?', 'answer' => 'Yes, on every plan. The embeddable calendar shows your upcoming events on any website, and each event links to its own page, where people can register or buy tickets.'],
                    ['question' => 'Are free registrations charged per person?', 'answer' => 'No. Free registration and RSVP are unlimited on every plan, with no per-registration fee. You can set a capacity, and each guest gets a ticket with a QR code that is scanned at the door.'],
                    ['question' => 'My Timely calendar is fed from Google Calendar. Can Event Schedule use it?', 'answer' => 'Yes. Connect that Google calendar, or an Outlook one, under Integrations and it syncs both ways on the free plan, so a change made in either place reaches the other. A CalDAV calendar works the same way.'],
                    ['question' => 'Can a university or nonprofit split one calendar by department?', 'answer' => 'Yes, with sub-schedules, free. Each sub-schedule has its own page and can be embedded on its own, so a department\'s website shows only its events while the main calendar shows everything. A curator schedule can also gather the events that other schedules publish.'],
                ],
            ],

            // The Events Calendar cells checked 2026-09-23 against liquidweb.com/software/the-events-calendar (tier prices),
            // wordpress.org/plugins/the-events-calendar and /event-tickets (GPL, free features, 2% Tickets Commerce fee)
            // and docs.nexcess.com (waitlist, embed). Webhooks, team roles and custom CSS were not verified and are left out.
            'the-events-calendar' => [
                'name' => 'The Events Calendar',
                'key' => 'the-events-calendar',
                'slug' => 'the-events-calendar-alternative',
                'meta_title' => 'The Events Calendar Alternative, No Plugins | Event Schedule',
                'tagline' => 'Event pages, ticketing and calendar sync without installing or updating a WordPress plugin.',
                'description' => 'Compare Event Schedule with The Events Calendar for WordPress: hosted or selfhosted, two-way calendar sync, and Pro at '.plan_price($this->planPrice()).'/mo instead of from $399 a year.',
                'keywords' => 'the events calendar alternative, wordpress event calendar alternative, events calendar pro alternative, wordpress events plugin alternative, event tickets plus alternative',
                'about' => 'The Events Calendar is a WordPress plugin, now sold by Liquid Web, that adds an event calendar to your own WordPress site. The core plugin is free and GPL-licensed on wordpress.org, and paid tiers add recurring events, check-in, seating maps and email campaigns.',
                'competitor_strengths' => [
                    'A free, GPL-licensed core plugin with several calendar views and a WordPress REST API',
                    'Runs inside your own WordPress site and theme',
                    'A wide range of add-ons, from event imports to seating maps and email campaigns',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (core plugin)', false],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'From $399/yr (Pro)', true],
                        ['Platform fees', '0%', '2% on the free Event Tickets plugin, 0% on Pro and Elite', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Stripe/PayPal/Square, WooCommerce on Pro', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes (Event Tickets plugin)', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes (Pro tier)', true],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes (Event Tickets Plus)', false],
                        ['Recurring events', 'Yes (Free)', 'Yes (paid tiers)', true],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes (coupons, paid tiers)', false],
                        ['Reserved seating', 'Yes (Enterprise)', 'Yes (Elite tier)', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Elite tier only ($599/yr)', true],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Through WooCommerce or GiveWP add-ons', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'Calendar export and one-way imports', true],
                        ['Newsletters', 'Yes (Free)', 'Yes (Elite tier)', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Runs on your own WordPress domain', false],
                        ['Custom fields', 'Yes (Pro)', 'Yes (Pro tier)', false],
                        ['Built-in analytics', 'Yes (Free)', 'Attendee reports', false],
                    ],
                    'Unique Features' => [
                        ['Hosted service', 'Yes (Free)', 'No (you run the WordPress site)', true],
                        ['Embeddable calendar', 'Yes (Free)', 'Yes (month view embed)', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes (free core plugin)', false],
                        ['Open source', 'Yes', 'Yes (GPLv2 or later)', false],
                        ['Selfhosting', 'Yes', 'Yes (on WordPress)', false],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'No WordPress Required',
                        'description' => 'Event Schedule is a hosted service with a free plan, so there is no WordPress site, plugin stack or update cycle to keep running. Self-hosting stays available if you want it.',
                        'icon' => 'globe',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => plan_price($this->planPrice()).'/mo vs $399/yr',
                        'description' => 'Event Schedule Pro is '.plan_price($this->planPrice()).'/mo with a '.$this->trialDays().'-day free trial. The Events Calendar paid tiers are $399 and $599 a year, renewed annually.',
                        'icon' => 'dollar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Zero Platform Fees',
                        'description' => 'Event Schedule takes no platform fee on any plan, and paid tickets are on Pro. The free Event Tickets plugin takes a 2% application fee on each sale.',
                        'icon' => 'percent',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free. The Events Calendar exports to calendar apps and imports feeds in one direction.',
                        'icon' => 'calendar',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Email the people who follow your schedule on every plan. On The Events Calendar, email campaigns are part of the Elite tier.',
                        'icon' => 'mail',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details or upload a flyer and the parser fills in the name, date, time and venue for you to check, on every plan.',
                        'icon' => 'ai',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Timely', 'route' => 'marketing.compare_timely'],
                    ['name' => 'Tockify', 'route' => 'marketing.compare_tockify'],
                    ['name' => 'Google Calendar', 'route' => 'marketing.compare_google_calendar'],
                ],
                'why_choose' => [
                    'summary' => 'Against a WordPress plugin, the question is who keeps the event system running. Event Schedule is hosted for you, includes recurring events and door check-in on its free plan, where the plugin keeps them for paid tiers, and still lets you run your own copy.',
                    'points' => [
                        'Recurring series on the free plan, where the plugin keeps them for its paid tiers',
                        'QR tickets scanned at the door on Free, where the plugin\'s check-in starts at its Pro tier',
                        'Newsletters to your followers on every plan, with no move up to Elite',
                        'Nothing added to your WordPress install: no plugin to update and no tables in its database, just one Custom HTML block',
                        'Pro at '.plan_price($this->planPrice()).' a month, where the plugin\'s paid tiers start at $399 and renew each year',
                        'Open source on both sides, but here as its own PHP and MySQL app, and a selfhosted copy resolves to Enterprise',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Start a schedule beside the site', 'description' => 'Free and hosted, so nothing changes on your WordPress site until you swap the calendar. The plugin can keep running while you check the copy.', 'doc' => 'getting-started#create-schedule'],
                    ['title' => 'Read the plugin\'s calendar export', 'description' => 'Paste the iCal export address of your events calendar into Import from a link. A calendar feed is read directly, for the next twelve months, with no AI involved.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'Replace the calendar page with one block', 'description' => 'Copy the iframe from Embed Schedule and paste it into a Custom HTML block on your Events page. In the classic editor, paste it on the Text tab.', 'doc' => 'sharing#embed'],
                    ['title' => 'Move Event Tickets to the Tickets tab', 'description' => 'Free registrations carry on at no cost. Priced tickets are Pro, through your own Stripe or PayPal account and without the 2% application fee.', 'doc' => 'tickets#payment'],
                ],
                'limits' => [
                    ['tag' => 'Not inside your theme', 'title' => 'Events live on the schedule\'s pages', 'text' => 'The plugin runs inside your WordPress site and wears your theme. Here the calendar arrives in an iframe styled by the schedule\'s own colors and typeface, and each event page sits at the schedule\'s address rather than on your WordPress domain.'],
                    ['tag' => 'No Square or WooCommerce', 'title' => 'A different set of payment options', 'text' => 'The plugin can take payment through Square, or through WooCommerce on its Pro tier, and Event Schedule supports neither. Payment goes to your own Stripe, PayPal, Payfast or Razorpay account instead, and Invoice Ninja can bill a sale as an invoice.'],
                    ['tag' => 'Selfhost is its own app', 'title' => 'Not an upload to WordPress hosting', 'text' => 'A selfhosted Event Schedule is a Laravel application that needs PHP 8.2 or newer, MySQL or MariaDB and a cron entry, not a plugin you add to an existing WordPress host. If that is more server than you want, the hosted service has nothing to install.'],
                ],
                'faq' => [
                    ['question' => 'Do I need a WordPress site to use Event Schedule?', 'answer' => 'No. Your schedule gets its own page and an embeddable calendar, so it works with WordPress or any other website builder. On a WordPress site, the calendar goes on a page with one iframe tag.'],
                    ['question' => 'How does Event Schedule pricing compare to The Events Calendar?', 'answer' => 'The core plugin is free, and the paid tiers are $399 and $599 a year. Event Schedule has a free plan, and Pro is '.plan_price($this->planPrice()).'/mo with zero platform fees on ticket sales.'],
                    ['question' => 'Is Event Schedule open source like The Events Calendar?', 'answer' => 'Yes, both are. The Events Calendar is GPL-licensed and runs inside WordPress. Event Schedule is a standalone app you can selfhost on PHP and MySQL, and a selfhosted install resolves to Enterprise.'],
                    ['question' => 'Can I move my events off WordPress?', 'answer' => 'Yes. Paste your event listings or upload flyers and the AI parser fills in the details for you to check, fifty a day on the free and Pro plans. Then embed the new calendar on your WordPress page, or link to your schedule instead.'],
                    ['question' => 'Can the embedded calendar match my WordPress site?', 'answer' => 'Mostly. The embed can follow each visitor\'s light or dark setting or be pinned to either, and you choose the month calendar or the list for it. The schedule\'s own colors and typeface style what is inside the frame, and custom CSS for the schedule\'s pages is on Pro.'],
                    ['question' => 'Can visitors submit their own events to the calendar?', 'answer' => 'Yes, on every plan. A venue or curator schedule takes requests through a form, and you decide whether a submitted event needs your approval, or an account, before it appears. Requests wait for you on the Requests tab.'],
                ],
            ],

            // Bandsintown cells checked 2026-09-23 against artist.bandsintown.com, bandsintown.pro and help.artists.bandsintown.com
            // (ticket links, waitlist, email builder pricing, insights, API, team roles). Calendar sync, custom domains and
            // private events were not verified and are left out.
            'bandsintown' => [
                'name' => 'Bandsintown',
                'key' => 'bandsintown',
                'slug' => 'bandsintown-alternative',
                'meta_title' => 'Bandsintown Alternative With Your Own Tickets | Event Schedule',
                'tagline' => 'Your own tour-date page that takes registrations and sells tickets, with calendar sync and zero platform fees.',
                'description' => 'Compare Event Schedule with Bandsintown for Artists: your own schedule page, free registration with QR tickets, paid tickets on Pro, and calendar sync.',
                'keywords' => 'bandsintown alternative, bandsintown for artists alternative, tour dates widget, concert listing for musicians, bandsintown competitor',
                'about' => 'Bandsintown for Artists is a free platform where musicians and their teams publish tour dates, reach fans who follow them, and send those dates on to streaming and search services. It links out to ticket sellers rather than selling tickets itself.',
                'competitor_strengths' => [
                    'A large built-in audience of fans who follow artists and get alerts about new dates',
                    'Publish once and the dates reach Spotify, Google, Apple Music, Shazam and Amazon Music',
                    'Free for artists, with a tour-date widget for your website and 80+ ticketing integrations',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (free for artists)', false],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'Free (paid email and SMS extras)', false],
                        ['Platform fees', '0%', 'N/A (links to outside ticket sellers)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'No (links to outside ticket sellers)', true],
                        ['Ticket waitlist', 'Yes (Pro)', 'RSVP waitlist, fans not notified automatically', true],
                        ['Online events', 'Yes (Free)', 'Yes (livestreams)', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (to RSVPs)', false],
                    ],
                    'Integrations' => [
                        ['Newsletters', 'Yes (Free)', 'Yes (10,000 free emails/mo, then per email)', false],
                    ],
                    'Customization' => [
                        ['Built-in analytics', 'Yes (Free)', 'Yes (from 100 followers)', false],
                    ],
                    'Unique Features' => [
                        ['Embeddable calendar', 'Yes (Free)', 'Yes (events widget)', false],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (admin, editor and viewer roles)', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Read-only, for a single artist', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Tickets on Your Own Page',
                        'description' => 'Bandsintown links fans out to other ticket sellers. Event Schedule takes free registrations with QR tickets on every plan, and paid tickets on Pro with zero platform fees.',
                        'icon' => 'ticket',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Your Page, Your Audience',
                        'description' => 'Your schedule is your own page, with your own followers and email sign-ups, and it keeps working whichever service your fans use.',
                        'icon' => 'globe',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free, so the dates in your own calendar and on your page never disagree.',
                        'icon' => 'calendar',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Venues and Artists Together',
                        'description' => 'Venue and talent schedules link to each other, so a show you play appears on the venue\'s schedule as well as your own.',
                        'icon' => 'building',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Event Graphics',
                        'description' => 'Turn your upcoming dates into a shareable graphic for social media in one click, free.',
                        'icon' => 'image',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Read the code and selfhost it on your own server. Bandsintown is a hosted service only.',
                        'icon' => 'code',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Facebook Events', 'route' => 'marketing.compare_facebook_events'],
                    ['name' => 'DICE', 'route' => 'marketing.compare_dice'],
                    ['name' => 'Posh', 'route' => 'marketing.compare_posh'],
                ],
                'why_choose' => [
                    'summary' => 'Bandsintown is where fans hear about a date, not where they buy a ticket for it. Event Schedule gives the act a page that registers or sells for each show, and keeps those dates in step with the calendar you plan the tour in.',
                    'points' => [
                        'A booking form on a Talent schedule, free, so promoters and venues can ask you for a date',
                        'Sell a show yourself on Pro, or point any date at an outside seller with Tickets elsewhere on every plan',
                        'Fans can add videos and photos from the night to its event page, shown once you approve them',
                        'An email digest to everyone who signed up, sent automatically when you publish new dates',
                        'A graphic of your upcoming dates for social posts, generated from the schedule, free',
                        'A live calendar feed fans subscribe to once, which their calendar app re-reads when a date moves',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Make a Talent schedule', 'description' => 'Pick Talent when you create the schedule, free. It is the act\'s own page, and the type that takes booking requests.', 'doc' => 'getting-started#schedule-types'],
                    ['title' => 'Paste the tour', 'description' => 'Copy your list of dates, or a link to a page that lists them, into AI import and look over every show before saving. Name the venue on each date and the show appears on that venue\'s schedule too.', 'doc' => 'ai-import'],
                    ['title' => 'Keep or replace the ticket links', 'description' => 'A date sold elsewhere keeps its outside link under Tickets elsewhere, on any plan. Selling a date yourself is Pro, with the buyers paying your own Stripe or PayPal account.', 'doc' => 'tickets#external'],
                    ['title' => 'Swap the widget on your site', 'description' => 'Replace the tour-date widget with the schedule\'s embed, pinned to the list for a dates page. Each date links to its own event page.', 'doc' => 'sharing#embed'],
                ],
                'limits' => [
                    ['tag' => 'No fan alert network', 'title' => 'Fans follow you one by one', 'text' => 'Bandsintown already has fans who follow artists and get alerts about new dates. A schedule here reaches only the people who followed it or signed up, so the follow link belongs on every post and on the merch table.'],
                    ['tag' => 'No streaming-app listings', 'title' => 'Dates are not sent to Spotify', 'text' => 'Event Schedule does not pass your shows on to Spotify, Apple Music, Shazam or Amazon Music the way Bandsintown does. What it publishes is your own page, a calendar feed and an embed, all kept current from one place.'],
                    ['tag' => 'A smaller email allowance', 'title' => '10,000 free emails there, fewer here', 'text' => 'On the hosted service a newsletter counts each recipient against 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, where Bandsintown gives artists 10,000 a month free. Connecting the schedule to your own mail server lifts that cap, and the automatic new-date digest never counts against it.'],
                ],
                'faq' => [
                    ['question' => 'Can I use Event Schedule alongside Bandsintown?', 'answer' => 'Yes. Many artists keep a Bandsintown listing for its fan alerts and use an Event Schedule page as the home for their dates, with their own tickets, followers and calendar feed. Paste your dates in and the AI parser fills in each show for you to check.'],
                    ['question' => 'How does Event Schedule pricing compare to Bandsintown?', 'answer' => 'Bandsintown is free for artists, with paid extras for email beyond 10,000 a month, promoted emails and SMS. Event Schedule is free for listing dates, registrations and newsletters, and Pro is '.plan_price($this->planPrice()).'/mo for paid tickets with zero platform fees.'],
                    ['question' => 'Can I sell tickets to my own shows?', 'answer' => 'Yes, on Pro, through your own Stripe or PayPal account, with zero platform fees. Each buyer gets a ticket with a QR code, scanned at the door on any plan.'],
                    ['question' => 'Can I put my tour dates on my website?', 'answer' => 'Yes. The embeddable calendar shows your upcoming dates on any website, free, and each date links to its own event page.'],
                    ['question' => 'Can promoters ask to book me?', 'answer' => 'Yes. A Talent schedule has a booking form on the free plan. A promoter sends the details through it, the request waits on your Requests tab, and you can require your approval before anything appears on your schedule.'],
                    ['question' => 'Do fans need an account to hear about new dates?', 'answer' => 'No. A fan types an email address into the sign-up panel, confirms it, and gets a digest whenever you publish new dates. The live calendar feed needs no email address at all.'],
                ],
            ],

            // Posh cells checked 2026-09-23 against support.posh.vip (ticket tiers and service fee, waitlist, promo codes,
            // SMS, analytics, team roles, Stripe Connect) and posh.vip/university (webhooks). Posh publishes no pricing page and
            // its blog quotes conflicting fee figures, so no percentage is stated here.
            'posh' => [
                'name' => 'Posh',
                'key' => 'posh',
                'slug' => 'posh-alternative',
                'meta_title' => 'Posh Alternative With 0% Platform Fees | Event Schedule',
                'tagline' => 'Tickets for parties and nights out, paid into your own account, with zero platform fees.',
                'description' => 'Compare Event Schedule with Posh: zero platform fees instead of a service fee on every ticket, payouts through your own Stripe or PayPal, and calendar sync.',
                'keywords' => 'posh alternative, posh.vip alternative, nightlife ticketing, party ticketing platform, posh competitor',
                'about' => 'Posh (posh.vip) is a social platform for discovering and creating in-person events, from club nights and run clubs to fashion shows and festivals. Organizers sell tickets through Stripe Connect, and each paid ticket carries a Posh service fee, added on top of the price the buyer sees.',
                'competitor_strengths' => [
                    'SMS blasts to past attendees, with unlimited event-level blasts until shortly after the event',
                    'Promoter commissions tracked with unique links',
                    'Discovery in the Posh app and on its Explore page that brings organizers new buyers',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'No subscription (service fee per ticket)', false],
                        ['Platform fees', '0%', '10% + $0.99 per paid ticket, paid by the buyer', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Stripe Connect', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (RSVP and $0 tickets)', false],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'Yes', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'No (abandoned-cart reminders only)', true],
                        ['Donations / pay what you want', 'Yes (Pro)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Newsletters', 'Yes (Free)', 'SMS blasts, email through Mailchimp', false],
                    ],
                    'Customization' => [
                        ['Built-in analytics', 'Yes (Free)', 'Yes', false],
                    ],
                    'Unique Features' => [
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Password or hidden tickets', false],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (admin, host and door roles)', false],
                    ],
                    'Platform' => [
                        ['Webhooks', 'Yes (Pro)', 'Yes (orders, refunds and transfers)', false],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => '0% Platform Fees',
                        'description' => 'Posh adds a service fee to every paid ticket, charged to the buyer on top of your price. Event Schedule takes no platform fee on any plan.',
                        'icon' => 'percent',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Flat '.plan_price($this->planPrice()).'/mo for Pro',
                        'description' => 'Paid tickets are on Pro at '.plan_price($this->planPrice()).'/mo with a '.$this->trialDays().'-day free trial, whether you sell ten tickets a month or ten thousand.',
                        'icon' => 'dollar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Your Own Payment Account',
                        'description' => 'Money goes straight into your own Stripe or PayPal account, and refunds go back through it from the Sales page.',
                        'icon' => 'ticket',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Recurring Nights',
                        'description' => 'Set a weekly or monthly residency once and skip the nights you are closed, on every plan.',
                        'icon' => 'calendar',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Email the people who follow your schedule from inside the app, alongside the new-event digest they get automatically.',
                        'icon' => 'mail',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Read the code and selfhost it on your own server. Posh is a hosted service only.',
                        'icon' => 'code',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Partiful', 'route' => 'marketing.compare_partiful'],
                    ['name' => 'DICE', 'route' => 'marketing.compare_dice'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                ],
                'why_choose' => [
                    'summary' => 'Posh takes a service fee on each ticket and finds buyers through its explore feed. Event Schedule charges one flat plan for paid tickets, takes no cut of them, and treats a weekly night as one event with many dates.',
                    'points' => [
                        'A residency set up once as a weekly or monthly series, with closed nights skipped, on Free',
                        'Guest lists and free entry as RSVPs with no cap and no fee per head',
                        'One cart for tickets to several of your nights, paid in a single checkout',
                        'A waitlist that emails people when a sold-out ticket frees up, and promo codes, on Pro',
                        'A graphic of the coming nights for your story, generated from the schedule for free',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Set up the brand\'s schedule', 'description' => 'A Venue schedule if you run the room, a Curator schedule if you throw parties in other people\'s, free either way.', 'doc' => 'getting-started#schedule-types'],
                    ['title' => 'Make the regular night a series', 'description' => 'Create the night once with its weekly or monthly pattern and leave out the dates you are closed. Every date in the series sells and checks in on its own.', 'doc' => 'creating-events#recurring'],
                    ['title' => 'Hand the door a scanner', 'description' => 'Whoever runs the schedule opens the scanner and checks QR tickets at the door, on any plan. Separate logins for door staff need team members, which is Enterprise.', 'doc' => 'tickets#check-in'],
                    ['title' => 'Price the tickets on Pro', 'description' => 'Connect Stripe or PayPal, then add ticket tiers with a quantity each. Nothing is added to the buyer\'s total or kept back from yours as a platform fee.', 'doc' => 'tickets#payment'],
                ],
                'limits' => [
                    ['tag' => 'No SMS', 'title' => 'Email reaches the crowd, not texts', 'text' => 'Posh can text past attendees, and Event Schedule sends no SMS to guests at all. Your reach here is email: newsletters, the automatic digest of new nights, and the "Notify me" list on each event.'],
                    ['tag' => 'Door staff are Enterprise', 'title' => 'One login on Free and Pro', 'text' => 'Posh gives door staff a login of their own. Here a viewer, who scans tickets without seeing sales, is a team member, and team members come with Enterprise, at most five on the hosted service.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Posh to Event Schedule?', 'answer' => 'Yes. Create a free schedule, paste your upcoming nights or upload a flyer and the AI parser fills in each event, then connect Stripe or PayPal and move to Pro to sell paid tickets.'],
                    ['question' => 'How does Event Schedule pricing compare to Posh?', 'answer' => 'Posh has no subscription and adds a service fee to every paid ticket. Event Schedule Pro is a flat '.plan_price($this->planPrice()).'/mo with zero platform fees, so what you pay does not grow with the number of tickets you sell.'],
                    ['question' => 'Can I run guest lists and free entry?', 'answer' => 'Yes. Free registration and RSVP are unlimited on every plan, each guest gets a QR code, and the door scans it on any plan. The live check-in dashboard is on Pro.'],
                    ['question' => 'Can I set up a weekly club night once?', 'answer' => 'Yes. A recurring event repeats weekly, every few weeks or monthly, on every plan, and you can skip the dates you are closed. Each date keeps its own tickets and guest list.'],
                    ['question' => 'Can a night be free before midnight and paid after?', 'answer' => 'Yes. Each ticket type has its own sales start and end, on every plan, so a free early tier can stop selling at a set time while a priced tier carries on. A ticket priced at zero sells on any plan, and the priced tiers need Pro.'],
                    ['question' => 'Can I see how a night is filling up?', 'answer' => 'Yes. With sale notifications switched on, Pro tells you about every sale by email, once the schedule has its own email settings. On the night, the check-in dashboard on Pro shows a running count, broken down by ticket type.'],
                ],
            ],

            // Partiful cells checked 2026-09-23 against partiful.com and help.partiful.com (pricing, ticket fees, ticketing
            // countries, QR check-in, waitlist, promo codes, recurring events, calendar sync, text blasts, cohosts, privacy).
            // Partiful publishes no fee percentage, so none is stated here.
            'partiful' => [
                'name' => 'Partiful',
                'key' => 'partiful',
                'slug' => 'partiful-alternative',
                'meta_title' => 'Partiful Alternative With Recurring Events | Event Schedule',
                'tagline' => 'Invites and RSVPs, plus a public schedule page, recurring events and ticketing with zero platform fees.',
                'description' => 'Compare Event Schedule with Partiful: free RSVPs plus recurring events, a public schedule page for every event you host, and zero platform fees.',
                'keywords' => 'partiful alternative, partiful competitor, party invite app alternative, rsvp app, event invitation platform',
                'about' => 'Partiful is a free app for party invites and RSVPs, used mostly by people hosting social events, with organization profiles for groups. Hosts in the US and UK can sell tickets, with fees that depend on event size and ticket price.',
                'competitor_strengths' => [
                    'Designed event pages with themes, fonts, effects and music',
                    'Text blasts and automatic reminders to guests',
                    'Free to use, and guests can RSVP from an invite sent on any platform',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (core app is free)', false],
                        ['Platform fees', '0%', 'Ticket fees vary by event size and price', true],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes (US and UK hosts)', true],
                        ['QR check-ins', 'Yes (Free)', 'Yes', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'No (clone the event instead)', true],
                        ['Free event RSVP', 'Yes (Free)', 'Yes', false],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (SMS and push)', false],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Chip In, paid outside Partiful', false],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'Subscription feed, one way', true],
                        ['Newsletters', 'Yes (Free)', 'Text blasts (up to 10 per event)', false],
                    ],
                    'Customization' => [
                        ['Custom fields', 'Yes (Pro)', 'Yes (RSVP questions)', false],
                    ],
                    'Unique Features' => [
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Yes (private, password)', false],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (cohosts)', false],
                    ],
                    'Platform' => [
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Recurring Events',
                        'description' => 'Set a weekly meetup or monthly party once, on every plan. Partiful has no recurring events and suggests cloning each one.',
                        'icon' => 'calendar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'One Page for Everything',
                        'description' => 'Your schedule lists every event you host on one public page, and people who sign up by email hear about the next one automatically.',
                        'icon' => 'globe',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => '0% Platform Fees',
                        'description' => 'Event Schedule takes no platform fee on any plan. Paid tickets are on Pro, paid into your own Stripe or PayPal account.',
                        'icon' => 'percent',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free, so your own calendar and your public page stay in step.',
                        'icon' => 'calendar',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Embed on Your Website',
                        'description' => 'Put the calendar on any website, free, and each event links to its own page for RSVPs or tickets.',
                        'icon' => 'layout',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Read the code and selfhost it on your own server. Partiful is a hosted app only.',
                        'icon' => 'code',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Posh', 'route' => 'marketing.compare_posh'],
                    ['name' => 'Luma', 'route' => 'marketing.compare_luma'],
                    ['name' => 'Facebook Events', 'route' => 'marketing.compare_facebook_events'],
                ],
                'why_choose' => [
                    'summary' => 'Partiful is built around one invite at a time. Event Schedule is built around the host: one public page for everything you run, dates that repeat, and people who follow you hear about the next event without being invited again.',
                    'points' => [
                        'A weekly or monthly event set up once, on Free, instead of a clone for every date',
                        'People who sign up by email get a digest when you publish new events, with no invite to send',
                        'Paid tickets on Pro wherever your Stripe, PayPal, Payfast or Razorpay account can take payments, not only in the US and UK',
                        'A calendar embed for a club or collective website, free',
                        'Events created in your Google or Outlook calendar can come into the schedule, and the other way round, free',
                        'Fan photos and videos on each event page once you approve them, 25 photos per schedule on Free',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Turn the organization profile into a schedule', 'description' => 'Your group\'s Partiful profile becomes a schedule: one free public page that lists every event you host.', 'doc' => 'getting-started#create-schedule'],
                    ['title' => 'Read the subscription feed', 'description' => 'Partiful\'s calendar subscription link goes into the import box as it is. Being a calendar feed, it is read without the AI and costs nothing from the daily allowance.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'Fold the clones into a series', 'description' => 'Delete the copies you cloned week by week and set one event to repeat instead. A date you are skipping can be left out on its own.', 'doc' => 'creating-events#recurring'],
                    ['title' => 'Ask guests to follow', 'description' => 'Share the schedule link or print its QR code. Guests who sign up by email hear about new events, with nothing for you to send.', 'doc' => 'sharing#qr-code'],
                ],
                'limits' => [
                    ['tag' => 'No per-event themes', 'title' => 'One look for the whole schedule', 'text' => 'Partiful lets each invite pick its own theme, font, effects and music. Here the look is set once for the schedule, with its colors, background, header and typeface, and every event page follows it.'],
                    ['tag' => 'No text blasts', 'title' => 'Guests hear from you by email', 'text' => 'Partiful texts guests and sends them reminders, and Event Schedule sends guests no text messages. A registration arrives as an email with its QR ticket, and every registered guest and ticket holder gets an email reminder before the event once reminders are on (the default on paid plans).'],
                    ['tag' => 'Private events are Enterprise', 'title' => 'Password events need the top plan', 'text' => 'A Partiful invite can be private or sit behind a password. Here Public and Draft are on every plan, while an unlisted event with an optional password, or an internal one for members only, is Enterprise.'],
                ],
                'faq' => [
                    ['question' => 'Is Event Schedule free like Partiful?', 'answer' => 'Yes. Unlimited events, free RSVPs with QR tickets, newsletters and calendar sync are on the free plan. Pro at '.plan_price($this->planPrice()).'/mo adds paid tickets with zero platform fees, promo codes and the check-in dashboard.'],
                    ['question' => 'Can guests RSVP without an account?', 'answer' => 'Yes. A guest registers with a name and an email address and gets a ticket with a QR code. They can follow your schedule to hear about your next event.'],
                    ['question' => 'Can I host the same event every week?', 'answer' => 'Yes. Recurring events repeat weekly, every few weeks or monthly on every plan, and each date keeps its own guest list and capacity.'],
                    ['question' => 'Can I keep an event private?', 'answer' => 'Yes, on Enterprise: an event can be unlisted or protected with a password, so only people you share it with can register.'],
                    ['question' => 'Can I host with friends?', 'answer' => 'On Enterprise, yes: a schedule can have up to five team members on the hosted service, as admins who run events or viewers who see the calendar and scan tickets. On Free and Pro a schedule is run by one person.'],
                    ['question' => 'Can I sell tickets outside the US or UK?', 'answer' => 'Yes. Free RSVPs work anywhere, on any plan. Priced tickets are on Pro and go through your own Stripe, PayPal, Payfast or Razorpay account, so they work wherever that account takes payments; Payfast settles in South African rand and Razorpay in Indian rupees.'],
                ],
            ],

            // Facebook Events cells checked 2026-09-23 against facebook.com/help (creating, privacy, repeat and online events,
            // co-hosts, add to calendar, event metrics) and developers.facebook.com (Graph API event reference, Page plugin).
            // Meta's paid online events pages no longer load, so paid events are not mentioned.
            'facebook-events' => [
                'name' => 'Facebook Events',
                'key' => 'facebook-events',
                'slug' => 'facebook-events-alternative',
                'meta_title' => 'Facebook Events Alternative You Own | Event Schedule',
                'tagline' => 'An events page you own, that people can see without an account, with ticketing and calendar sync.',
                'description' => 'Compare Event Schedule with Facebook Events: a public schedule page anyone can open without an account, free registration, and two-way calendar sync.',
                'keywords' => 'facebook events alternative, facebook event page alternative, community events calendar, events without facebook, facebook events competitor',
                'about' => 'Facebook Events is the free events feature inside Facebook, used by people, Pages and groups to create events and invite others to them. Guests respond Interested or Going, and a ticket link can send them to another site to buy.',
                'competitor_strengths' => [
                    'A huge built-in audience, with invites to friends, groups and past guests',
                    'Free, with privacy levels from public to private and group-only',
                    'Repeating events and online events through Facebook Live or an external link',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (free)', false],
                        ['Platform fees', '0%', 'N/A (no ticketing)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'No (external ticket link)', true],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (Interested or Going)', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Facebook notifications, not email', false],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'Per-event export (.ics)', true],
                    ],
                    'Customization' => [
                        ['Built-in analytics', 'Yes (Free)', 'Yes (Page event metrics)', false],
                    ],
                    'Unique Features' => [
                        ['Embeddable calendar', 'Yes (Free)', 'Page plugin events tab', false],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Yes (private events)', false],
                        ['Team collaboration', 'Yes (Enterprise)', 'Co-hosts (friends or Pages)', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Read-only, Marketing Partners only', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'No Account to View',
                        'description' => 'Your schedule and every event page open for anyone with the link, with no sign-in wall in front of the details.',
                        'icon' => 'globe',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Tickets on the Page',
                        'description' => 'Facebook events send buyers to another site. Event Schedule takes free registrations with QR tickets on every plan, and paid tickets on Pro with zero platform fees.',
                        'icon' => 'ticket',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Reach People by Email',
                        'description' => 'People who sign up by email get a digest of your new events and your newsletters, instead of depending on what a feed decides to show them.',
                        'icon' => 'mail',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free, and anyone can subscribe to your schedule\'s live calendar feed.',
                        'icon' => 'calendar',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Embed on Your Website',
                        'description' => 'Put the calendar on your own website, free, with each event linking to its own page.',
                        'icon' => 'layout',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Read the code and selfhost it on your own server, so your event listings stay yours.',
                        'icon' => 'code',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Meetup', 'route' => 'marketing.compare_meetup'],
                    ['name' => 'Partiful', 'route' => 'marketing.compare_partiful'],
                    ['name' => 'Mobilizon', 'route' => 'marketing.compare_mobilizon'],
                ],
                'why_choose' => [
                    'summary' => 'Facebook Events works inside Facebook, for the people who are on it. An Event Schedule page works on the open web: nothing to sign in to before reading it, a register button on the page itself, and an email list that belongs to you.',
                    'points' => [
                        'Guests register on the event page itself and each gets a QR code, where Facebook stops at Interested or Going',
                        'Your followers\' names and email addresses on your own Followers tab, so the list stays yours',
                        'A feed of the whole schedule that a subscriber\'s calendar re-reads, rather than one .ics file per event',
                        'Curator schedules for a community calendar that collects the events venues and acts publish, free',
                        'Sub-schedules that split a Page\'s events by series or category, each with its own address',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Make a schedule for the Page', 'description' => 'A Page that runs events becomes a schedule: Venue for a place, Talent for an act, Curator for a group that lists other people\'s events. All three are free.', 'doc' => 'getting-started#schedule-types'],
                    ['title' => 'Screenshot the events, not the link', 'description' => 'Facebook links cannot be read by other sites, so add a screenshot of each event, or paste its text, into AI import and check what comes back.', 'doc' => 'ai-import#image-import'],
                    ['title' => 'Replace Interested with Register', 'description' => 'Turn on free registration so each guest gets a QR ticket, with a capacity if the room has one. Priced tickets go on Pro.', 'doc' => 'tickets#registration'],
                    ['title' => 'Post links that lead home', 'description' => 'Keep posting on Facebook, but post the event page link, so the people who follow the schedule from it join a list you can email.', 'doc' => 'sharing#followers'],
                ],
                'limits' => [
                    ['tag' => 'No friends network', 'title' => 'Nobody is invited for you', 'text' => 'On Facebook an event can go out to friends, groups and past guests in a few taps. Event Schedule has no social network behind it, so people arrive through the links, QR codes and emails you put out.'],
                    ['tag' => 'Facebook links are refused', 'title' => 'Bringing events over is by hand', 'text' => 'Facebook does not let other sites read its pages, so the importer turns those links down and cannot pull a Page\'s events in. A screenshot or the pasted text of each event is read instead, within the daily allowance of 50 on Free and Pro.'],
                    ['tag' => 'Group-only is Enterprise', 'title' => 'Private events cost more here', 'text' => 'Facebook can make an event private or keep it inside a group at no charge. Here an event only the schedule\'s members can see, or one hidden from the page and reached by link, is part of Enterprise.'],
                ],
                'faq' => [
                    ['question' => 'Can people see my events without a Facebook account?', 'answer' => 'Yes. Your schedule and each event page are public web pages, so anyone with the link can read the details, register and add the event to their calendar, with no account.'],
                    ['question' => 'Is Event Schedule free like Facebook Events?', 'answer' => 'Yes. Unlimited events, free registration with QR tickets, newsletters, the embeddable calendar and calendar sync are on the free plan. Pro at '.plan_price($this->planPrice()).'/mo adds paid tickets with zero platform fees.'],
                    ['question' => 'Can I still share my events on Facebook?', 'answer' => 'Yes. Share each event page link on Facebook as you would any other link, and the event graphic generator makes an image for the post, free. Boost can also run a paid Facebook and Instagram ad for an event, on Pro.'],
                    ['question' => 'Can I bring my Facebook events across?', 'answer' => 'Yes. Paste the event details or upload the flyer and the AI parser fills in the name, date, time and venue for you to check, fifty a day on the free and Pro plans.'],
                    ['question' => 'Will the people who said Going on Facebook come across?', 'answer' => 'There is no import of a Facebook guest list. Post the new event page on the Facebook event and ask guests to register there; each one gets a QR ticket, and every registration shows on your Sales page.'],
                    ['question' => 'Can I run a community calendar of other people\'s events?', 'answer' => 'Yes, with a curator schedule, free. Add the venue and talent schedules you want as sources and every event they publish is listed on yours automatically. Visitors can also submit events through a request form, held for your approval if you require it.'],
                ],
            ],

            // Zeffy cells checked 2026-09-23 against zeffy.com/pricing and support.zeffy.com (eligibility and countries,
            // ticketing, event form configuration, emails, campaign URLs, API and webhooks, users). Waitlist, online events,
            // calendar sync and private events were not verified and are left out.
            'zeffy' => [
                'name' => 'Zeffy',
                'key' => 'zeffy',
                'slug' => 'zeffy-alternative',
                'meta_title' => 'Zeffy Alternative for Any Organizer | Event Schedule',
                'tagline' => 'Open source event ticketing with zero platform fees, open to any organizer, not only registered nonprofits.',
                'description' => 'Compare Event Schedule with Zeffy: event ticketing with zero platform fees for any organizer, not only registered nonprofits, plus calendar sync and self-hosting.',
                'keywords' => 'zeffy alternative, zeffy competitor, free event ticketing, nonprofit event ticketing, ticketing for community groups',
                'about' => 'Zeffy is a free fundraising and ticketing platform for nonprofits and registered charities in the US, Canada, the UK, Ireland, Australia and Germany. It covers card fees itself and is funded by optional tips from donors and buyers.',
                'competitor_strengths' => [
                    'No fees for the nonprofit: Zeffy covers card and transaction fees',
                    'A full fundraising suite: donations, raffles, auctions, memberships and an online store',
                    'Unlimited users, with permissions per campaign',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (100% free, tip-funded)', false],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'None (optional tips at checkout)', false],
                        ['Platform fees', '0%', '0% (optional tips at checkout)', false],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Stripe (card fees covered by Zeffy)', false],
                        ['Open to any organizer', 'Yes', 'No (nonprofits and charities only)', true],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Multi-date events', true],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Reserved seating', 'Yes (Enterprise)', 'Seating chart image, a ticket type per row', true],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (7 days before)', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'No', true],
                    ],
                    'Integrations' => [
                        ['Newsletters', 'Yes (Free)', 'Yes (5,000 per email, 30,000 with your own domain)', false],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'No (email sender domain only)', true],
                        ['Custom fields', 'Yes (Pro)', 'Yes (custom questions)', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes (reports)', false],
                    ],
                    'Unique Features' => [
                        ['Fundraising & donations', 'Donations at checkout and pay what you want (Pro); no raffles or auctions', 'Yes (donations, raffles, auctions)', false],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (unlimited users)', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Read access (payments, contacts, campaigns)', false],
                        ['Webhooks', 'Yes (Pro)', 'Yes (payments and contacts)', false],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Open to Everyone',
                        'description' => 'Zeffy is for registered nonprofits and charities in six countries. Event Schedule works for any organizer, anywhere: a community group, a venue, a band or a business.',
                        'icon' => 'globe',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => '0% Platform Fees',
                        'description' => 'Event Schedule takes no platform fee on any plan, and there is no tip prompt at checkout. Paid tickets are on Pro at '.plan_price($this->planPrice()).'/mo.',
                        'icon' => 'percent',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free, and anyone can subscribe to your schedule\'s live calendar feed.',
                        'icon' => 'calendar',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'A Public Schedule Page',
                        'description' => 'Every event you run sits on one public schedule page, with email subscribers who hear about new events and an embeddable calendar for your website.',
                        'icon' => 'layout',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Recurring Events',
                        'description' => 'Weekly, monthly or every few weeks, with dates you can skip, on every plan, and each date keeps its own tickets.',
                        'icon' => 'calendar',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Read the code and selfhost it on your own server. Zeffy is a hosted service only.',
                        'icon' => 'code',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Humanitix', 'route' => 'marketing.compare_humanitix'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Hi.Events', 'route' => 'marketing.compare_hi_events'],
                ],
                'why_choose' => [
                    'summary' => 'What decides it is who may sign up and what the page is for. Event Schedule takes any organizer in any country, puts every event on one public schedule with followers and calendar sync, and asks the buyer for nothing beyond the ticket price.',
                    'points' => [
                        'No charity status to prove: clubs, venues, bands and businesses sign up the same way',
                        'A checkout without a tip request, so the buyer pays the ticket price and nothing more',
                        'Payfast for rand and Razorpay for rupees beside Stripe and PayPal, on Pro',
                        'Free registration with QR tickets for a gala or a volunteer evening, with no plan needed',
                        'A season pass or a membership that buyers redeem across your events, on Pro',
                        'Two-way calendar sync and a subscribable feed, so volunteers see the dates in their own calendar',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Sign the organization up', 'description' => 'Free, and no charity number is asked for. Its public page lists every event you run, the fundraiser and the volunteer night alike.', 'doc' => 'getting-started#create-schedule'],
                    ['title' => 'Bring each campaign\'s event over', 'description' => 'Paste the text of a Zeffy event page, or its link, into AI import and check the name, date, venue and description before saving.', 'doc' => 'ai-import'],
                    ['title' => 'Keep donations where they are', 'description' => 'Event Schedule takes a donation only at an event\'s checkout (on Pro), so keep your Zeffy donation page for giving that is not tied to an event, and add its address to the schedule\'s social links, where it shows as a link on your page.', 'doc' => 'creating-schedules#social-links'],
                    ['title' => 'Decide on Pro for priced tickets', 'description' => 'Free events need no plan. A priced ticket is Pro, paid into your own Stripe or PayPal account at its standard card rate, the fee Zeffy was covering.', 'doc' => 'tickets#payment'],
                ],
                'limits' => [
                    ['tag' => 'No fundraising tools', 'title' => 'Tickets, not donations', 'text' => 'Zeffy runs donation forms, raffles, auctions and an online store. Event Schedule takes a donation only beside a ticket at checkout, and sells tickets, passes, add-ons and gift cards, on Pro.'],
                    ['tag' => 'Card fees are yours', 'title' => 'Nobody covers Stripe for you', 'text' => 'Zeffy pays the card fees on a nonprofit\'s sales and is funded by tips. Here the processor\'s own fee comes out of each sale at your account\'s rate, and selling a priced ticket needs Pro.'],
                    ['tag' => 'Five seats, on Enterprise', 'title' => 'Not unlimited volunteers', 'text' => 'Zeffy gives a nonprofit unlimited users, with permissions per campaign. A schedule here is run by one person on Free and Pro, and team members are Enterprise, capped at five on the hosted service.'],
                ],
                'faq' => [
                    ['question' => 'Can a group that is not a registered nonprofit use Event Schedule?', 'answer' => 'Yes. Any organizer can use Event Schedule: community groups, clubs, venues, artists and businesses, in any country. Zeffy is limited to nonprofits and charities in six countries.'],
                    ['question' => 'How does Event Schedule pricing compare to Zeffy?', 'answer' => 'Zeffy is free for nonprofits and asks buyers for an optional tip at checkout. Event Schedule has a free plan with free registration, and Pro at '.plan_price($this->planPrice()).'/mo adds paid tickets with zero platform fees and no tip prompt.'],
                    ['question' => 'Does Event Schedule take donations like Zeffy?', 'answer' => 'At an event\'s checkout, yes: on Pro a buyer can add a donation to their tickets, and a free ticket with donations on is pay what you want. There are no standalone donation forms, raffles or auctions, so if fundraising is the heart of what you do, Zeffy covers more of it.'],
                    ['question' => 'Who pays the card fees?', 'answer' => 'Paid tickets go through your own Stripe or PayPal account at its standard rates. Event Schedule adds no platform fee on top, and refunds go back through the same account from the Sales page.'],
                    ['question' => 'Can volunteers check people in at the door?', 'answer' => 'On Enterprise, yes: a viewer on the schedule\'s team scans tickets without seeing sales, with up to five members on the hosted service. On Free and Pro, scanning is done from the owner\'s account.'],
                    ['question' => 'Can I sell memberships?', 'answer' => 'Yes, as a pass, on Pro. A pass can stand for a membership, a season or a number of visits, buyers redeem it across your events, and each use is tracked on the Subscriptions tab.'],
                ],
            ],

            // Tockify cells checked 2026-09-23 against tockify.com/i/site/trialPlanPicker, /i/docs/faq/subscription,
            // /i/docs/howto/googlesync, /i/docs/howto/whitelabel and /i/docs/api. Prices are the US figures; Tockify
            // bills in USD, GBP or EUR by country. Newsletters, QR check-in and webhooks were not verified and are left out.
            'tockify' => [
                'name' => 'Tockify',
                'key' => 'tockify',
                'slug' => 'tockify-alternative',
                'meta_title' => 'Tockify Alternative With Ticketing Built In | Event Schedule',
                'tagline' => 'An embeddable event calendar that also takes registrations and sells tickets, with a free plan.',
                'description' => 'Compare Event Schedule with Tockify: an embeddable calendar with free registration and QR check-in, two-way Google sync, and Pro at '.plan_price($this->planPrice()).'/mo.',
                'keywords' => 'tockify alternative, tockify competitor, embeddable event calendar, website event calendar, free event calendar widget',
                'about' => 'Tockify is a hosted, embeddable website calendar used by museums, theatres, schools, churches and gyms. It has a free plan and paid plans charged per calendar, and it sends visitors to another site for tickets.',
                'competitor_strengths' => [
                    'Good-looking, customizable calendar views: agenda, pinboard, month and mini',
                    'Simple per-calendar pricing, with a free plan',
                    'RSVP forms and events submitted by the public',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (agenda view, 2,000 views/mo)', false],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', '$8/mo per calendar (Premium)', false],
                        ['Platform fees', '0%', 'N/A (no ticketing)', false],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'No', true],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'No (links out to your ticket page)', true],
                        ['Recurring events', 'Yes (Free)', 'Yes (Premium)', true],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (Premium)', true],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Opt-in on RSVP forms (Premium)', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'Google import only (read-only)', true],
                    ],
                    'Customization' => [
                        ['Custom domains', 'Yes (Enterprise)', 'Yes (White Label, for event links)', false],
                        ['Remove branding', 'Yes (Pro)', 'Yes (White Label, $40/mo)', true],
                    ],
                    'Unique Features' => [
                        ['Embeddable calendar', 'Yes (Free)', 'Yes', false],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Calendar passcode (experimental)', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'No (embedding API only)', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Tickets on the Page',
                        'description' => 'Tockify sends visitors to another site for tickets. Event Schedule takes free registrations with QR tickets on every plan, and paid tickets on Pro with zero platform fees.',
                        'icon' => 'ticket',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Event Graphics',
                        'description' => 'Turn your upcoming events into a shareable graphic for social media in one click, free.',
                        'icon' => 'image',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free. Tockify reads from Google Calendar with read-only access.',
                        'icon' => 'calendar',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Email the people who follow your schedule from inside the app, on every plan.',
                        'icon' => 'mail',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Remove Branding on Pro',
                        'description' => 'Pro removes the Event Schedule branding. Tockify removes its branding on the White Label plan, at $40/mo.',
                        'icon' => 'shield',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Read the code and selfhost it on your own server. Tockify is a hosted service only.',
                        'icon' => 'globe',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Timely', 'route' => 'marketing.compare_timely'],
                    ['name' => 'The Events Calendar', 'route' => 'marketing.compare_the_events_calendar'],
                    ['name' => 'AddEvent', 'route' => 'marketing.compare_addevent'],
                ],
                'why_choose' => [
                    'summary' => 'Tockify draws a good-looking calendar and then sends people to another site to pay. Event Schedule keeps the registration, the ticket and the door scan on the page each calendar entry opens.',
                    'points' => [
                        'Free registration with a QR ticket for each guest on the free plan, where Tockify keeps RSVP forms for Premium',
                        'Repeating events on Free: daily, weekly on chosen days, monthly by date or weekday, with dates left out',
                        'Sub-schedules split one schedule into programmes, each with its own page and its own embed, at no extra cost',
                        'Events the public sends in arrive as requests you accept or turn down, on every plan',
                        'Visitors can subscribe to the schedule\'s live calendar feed, so a moved date updates in their own calendar',
                        'Priced tickets on Pro at '.plan_price($this->planPrice()).'/mo, charged through your own Stripe or PayPal account with no platform fee',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'One schedule, or one with sub-schedules', 'description' => 'Each Tockify calendar can become a schedule of its own, or a sub-schedule when several share one audience, such as a museum\'s talks and family days. Both are free.', 'doc' => 'creating-schedules#customize-subschedules'],
                    ['title' => 'Connect the Google Calendar it reads', 'description' => 'If Tockify reads your events from Google Calendar, connect that calendar here. The sync runs both ways on the free plan, so you keep entering events where you do now.', 'doc' => 'creating-schedules#integrations-google'],
                    ['title' => 'Replace the ticket links', 'description' => 'Where an event linked out, choose Free registration on its Tickets tab, or keep the link with Tickets elsewhere, which every plan has. A ticket with a price is Pro.', 'doc' => 'tickets#registration'],
                    ['title' => 'Swap the embed code', 'description' => 'From the Actions menu choose Embed Schedule, pick the month calendar or the list, and paste the iframe where the Tockify embed sat.', 'doc' => 'sharing#embed-code'],
                ],
                'limits' => [
                    ['tag' => 'Two layouts', 'title' => 'A month grid and a list', 'text' => 'Tockify offers agenda, pinboard, month and mini views of one calendar. Event Schedule draws two, the month calendar and the list, and an embed can pin either one or follow the schedule\'s own default.'],
                    ['tag' => 'Custom domain on Enterprise', 'title' => 'Your own domain is the top plan', 'text' => 'Tockify puts event links on your domain with its White Label plan. Here a custom domain is Enterprise, and a schedule on Free or Pro lives at its eventschedule.com address while the embed shows it on your own site.'],
                    ['tag' => 'Passwords on Enterprise', 'title' => 'Private events cost more', 'text' => 'An event that is members-only, or reachable by link behind a password, is Enterprise. Free and Pro have Drafts, which stay off the public calendar until you publish them.'],
                ],
                'faq' => [
                    ['question' => 'Is it easy to switch from Tockify to Event Schedule?', 'answer' => 'Yes. Create a free schedule, connect Google Calendar or paste your event details for the AI parser, then swap the embed code on your website for the Event Schedule calendar.'],
                    ['question' => 'How does Event Schedule pricing compare to Tockify?', 'answer' => 'Tockify\'s free plan is an agenda view with Tockify branding, Premium is $8/mo per calendar and White Label is $40/mo. Event Schedule\'s free plan includes the embeddable calendar, and Pro is '.plan_price($this->planPrice()).'/mo, which also removes our branding.'],
                    ['question' => 'Can people register for events on my calendar?', 'answer' => 'Yes. Free registration and RSVP are unlimited on every plan, each guest gets a ticket with a QR code, and paid tickets are on Pro with zero platform fees, paid into your own Stripe or PayPal account.'],
                    ['question' => 'Can I keep my events in Google Calendar?', 'answer' => 'Yes. Google Calendar sync runs both ways on every plan, so an event added in either place shows up in the other. Outlook and CalDAV calendars sync the same way.'],
                    ['question' => 'Can one account run several calendars?', 'answer' => 'Yes. Make a schedule for each, or keep one schedule and divide it into sub-schedules, which are free and each get their own page. To embed one sub-schedule on its own, add ?schedule= and its slug to the embed address. Tockify prices each calendar separately.'],
                    ['question' => 'Can visitors suggest events for my calendar?', 'answer' => 'Yes, on every plan. A visitor sends an event through your schedule\'s request form by pasting its details or uploading a flyer. With Require Approval on, it waits on the Requests tab until you accept it, and you can ask submitters to sign in first.'],
                ],
            ],

            // Hi.Events cells checked 2026-09-23 against hi.events/pricing, hi.events, hi.events/open-source-event-ticketing,
            // hi.events/docs (ticket widget, webhooks) and github.com/HiEventsDev/Hi.Events (license, stack, features).
            // Calendar sync, custom domains on Cloud and custom CSS were not verified and are left out.
            'hi-events' => [
                'name' => 'Hi.Events',
                'key' => 'hi-events',
                'slug' => 'hi-events-alternative',
                'meta_title' => 'Hi.Events Alternative, No Per-Ticket Fees | Event Schedule',
                'tagline' => 'Open source ticketing with a flat price instead of a per-ticket fee, plus calendar sync and newsletters.',
                'description' => 'Compare Event Schedule with Hi.Events: open source ticketing with flat '.plan_price($this->planPrice()).'/mo pricing instead of 1.25% + $0.60 a ticket, calendar sync, and simple self-hosting.',
                'keywords' => 'hi.events alternative, hi events alternative, open source ticketing alternative, open source eventbrite alternative, selfhosted ticketing',
                'about' => 'Hi.Events is an open source event management and ticketing platform (AGPL-3.0 with additional terms) for conferences, nightlife, concerts and workshops. It offers a hosted Cloud service with a per-ticket fee and a selfhosted version, with paid licences to remove its branding.',
                'competitor_strengths' => [
                    'Open source and selfhostable, with an all-in-one Docker image',
                    'No monthly fee on the hosted Cloud service',
                    'A broad ticketing feature set: waitlists, browser check-in lists, product add-ons, affiliate tracking, reserved seating, an API and webhooks',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'No monthly fee on Cloud (per-ticket fee)', false],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'No subscription (1.25% + $0.60 per ticket)', true],
                        ['Platform fees', '0%', '1.25% + $0.60 per ticket (Cloud)', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Stripe, plus offline payments', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes (check-in lists opened from a link)', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (free tickets)', false],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'Tracking links, no commission', true],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'No (messages you schedule)', true],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes (donation tickets)', false],
                    ],
                    'Customization' => [
                        ['Remove branding', 'Yes (Pro)', 'Cloud subscription, or a selfhost licence from 249 EUR/yr', true],
                        ['Custom fields', 'Yes (ticket questions free, event fields Pro)', 'Yes (questions per ticket type)', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes (sales reports)', false],
                    ],
                    'Unique Features' => [
                        ['Embeddable calendar', 'Yes (Free)', 'Ticket widget', false],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (admin and organizer roles)', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Yes', false],
                        ['Webhooks', 'Yes (Pro)', 'Yes', false],
                        ['Open source', 'Yes', 'Yes (AGPL-3.0 with additional terms)', false],
                        ['Selfhosting', 'Yes', 'Yes (Docker, PostgreSQL, Redis)', false],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Flat '.plan_price($this->planPrice()).'/mo Pricing',
                        'description' => 'Hi.Events Cloud charges 1.25% + $0.60 on every ticket. Event Schedule Pro is a flat '.plan_price($this->planPrice()).'/mo with zero platform fees, whatever you sell.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free, and anyone can subscribe to your schedule\'s live calendar feed.',
                        'icon' => 'calendar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Email the people who follow your schedule, not only the people who bought a ticket, from inside the app.',
                        'icon' => 'mail',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Simpler to Selfhost',
                        'description' => 'Event Schedule runs on standard PHP hosting with MySQL. Hi.Events self-hosting uses Docker with PostgreSQL and Redis.',
                        'icon' => 'globe',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'A Public Schedule Page',
                        'description' => 'Every event sits on one public schedule page with followers, sub-schedules and an embeddable calendar, not only a checkout.',
                        'icon' => 'layout',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details or upload a flyer and the parser fills in the name, date, time and venue for you to check, on every plan.',
                        'icon' => 'ai',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Pretix', 'route' => 'marketing.compare_pretix'],
                    ['name' => 'Mobilizon', 'route' => 'marketing.compare_mobilizon'],
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                ],
                // Hi.Events Cloud's rate from TicketFees::rates() (hi.events/pricing, checked 2026-09-24).
                'fee_example' => [
                    'tag' => 'Hi.Events fees, worked out',
                    'title' => 'What 100 tickets at $20 cost you on Hi.Events Cloud',
                    'lead' => 'Hi.Events Cloud has no subscription. It charges 1.25% + $0.60 on each paid ticket, and Stripe\'s processing fee is charged on top. Event Schedule charges nothing per ticket and asks for Pro instead. Here is one month of $2,000 in ticket sales on each.',
                    'total_label' => 'Fees on $2,000',
                    'caption' => 'Fees on 100 tickets at $20, each bought in its own order, on Event Schedule Pro, Hi.Events Cloud and Eventbrite.',
                    'rows' => $this->feeExample(100, 20, [$this->ourFeeRow(), $this->ratesFeeRow('hi-events', 'Hi.Events Cloud'), $this->ratesFeeRow('eventbrite')]),
                    'notes' => [
                        'The Hi.Events fee on a $20 ticket is $'.number_format(20 * 0.0125 + 0.60, 2).', so Event Schedule Pro costs less once a month passes '.$this->feeBreakEven($this->ratesFeeRow('hi-events'), 20).' paid tickets. Below that, a quiet month is cheaper on Hi.Events.',
                        'Both are open source, so either can run on your own server instead. The rows above compare the two hosted services.',
                        'The rate is the one Hi.Events published in September 2026. Stripe\'s 30 cents is counted on every ticket because each ticket is its own order here.',
                    ],
                ],
                'why_choose' => [
                    'summary' => 'Both projects publish their code, so open source does not separate them. What does is how you pay, a cut of each ticket on Hi.Events Cloud against one flat month here, and what surrounds the checkout: a public schedule with followers, calendar sync and newsletters.',
                    'points' => [
                        'Pro at '.plan_price($this->planPrice()).'/mo in place of 1.25% + $0.60 on every ticket, so the platform\'s share does not grow with a sold-out month',
                        'Questions on a ticket type are free, as they are on Hi.Events; add-ons and promo codes come with Pro',
                        'A month or list calendar of every event for your own website, free, beside the ticket widget on Pro',
                        'People who follow or subscribe to the schedule hear about each new event before they have bought anything',
                        'A selfhosted install runs on PHP and MySQL, or in Docker, and includes every Enterprise feature',
                        'Start on the hosted service and move to your own server later with a backup of your schedules and their events',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Decide where it runs', 'description' => 'The hosted service needs nothing installed. To selfhost, ordinary PHP hosting with MySQL is enough, with no PostgreSQL or Redis to run beside it.', 'doc' => 'selfhost/installation#requirements'],
                    ['title' => 'Read each event page', 'description' => 'Paste the link to a Hi.Events event page into Import from a link. The page is read and the event comes back filled in for you to check.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'Rebuild the ticket types', 'description' => 'Per-ticket questions become questions on a ticket type, free. Product add-ons become add-ons, and discount codes become promo codes, both on Pro.', 'doc' => 'tickets#add-ons'],
                    ['title' => 'Connect Stripe and open the door', 'description' => 'Connect your own Stripe or PayPal account and sell with no per-ticket fee. At the event, tickets are scanned in any phone\'s browser, with no check-in app to install.', 'doc' => 'tickets#payment'],
                ],
                'limits' => [
                    ['tag' => 'A monthly price', 'title' => 'A quiet month still costs Pro', 'text' => 'Hi.Events Cloud charges nothing until a ticket sells, while a priced ticket here needs Pro at '.plan_price($this->planPrice()).'/mo whatever you sell. An organizer who sells only a few paid tickets a year may pay less per ticket there; registering for a free event is free with both.'],
                    ['tag' => 'Credit stays on selfhost', 'title' => 'No licence takes our credit off', 'text' => 'Hi.Events sells a licence, from 249 EUR a year, that removes its link from a selfhosted install, and a subscription that removes it on its Cloud. Our license requires an attribution credit on every selfhosted install and nothing removes it there, though on the hosted service Pro takes the Event Schedule branding off.'],
                ],
                'faq' => [
                    ['question' => 'Are both platforms open source?', 'answer' => 'Yes. Hi.Events is AGPL-3.0 with additional terms that require a visible "Powered by Hi.Events" link unless you buy a licence. Event Schedule is open source under the Attribution Assurance License, and a selfhosted install resolves to Enterprise. It carries an attribution credit too: the license requires it, and no plan removes it on a selfhosted install.'],
                    ['question' => 'How does Event Schedule pricing compare to Hi.Events Cloud?', 'answer' => 'Hi.Events Cloud has no monthly fee and charges 1.25% + $0.60 per ticket, paid by buyers by default. Event Schedule Pro is a flat '.plan_price($this->planPrice()).'/mo with zero platform fees, so the saving grows with every ticket you sell.'],
                    ['question' => 'Which is easier to selfhost?', 'answer' => 'Event Schedule runs on standard PHP hosting with MySQL. Hi.Events ships an all-in-one Docker image and uses PostgreSQL and Redis, which suits a server you already run Docker on.'],
                    ['question' => 'Does Event Schedule have a check-in app?', 'answer' => 'Scanning works in the browser on any phone, free on every plan, with no app to install. The live check-in dashboard, which shows the room filling up, is on Pro.'],
                    ['question' => 'Does Event Schedule have add-ons and ticket questions?', 'answer' => 'Yes. Questions attached to a ticket type are asked once per order at checkout and are free on every plan. Add-ons, sold beside a ticket with their own stock and a per-order maximum, are on Pro, as are event custom fields and answers collected for each guest.'],
                    ['question' => 'Can buyers pay offline?', 'answer' => 'Yes, on Pro. An event can take cash or a payment link instead of a card checkout, and you mark the sale paid when the money comes in. To be paid online, the choices are Stripe, PayPal, Payfast, Razorpay and Invoice Ninja.'],
                ],
            ],

            // Mobilizon cells checked 2026-09-23 against mobilizon.org/about, docs.mobilizon.org (creating events, RSS and ICS
            // feeds, ActivityPub, embedding, group roles) and framagit.org/kaihuri/mobilizon (AGPLv3). The event docs show no
            // ticket or payment options, so ticketing is described, not marked No. Recurring events and webhooks are left out.
            'mobilizon' => [
                'name' => 'Mobilizon',
                'key' => 'mobilizon',
                'slug' => 'mobilizon-alternative',
                'meta_title' => 'Mobilizon Alternative With Ticketing | Event Schedule',
                'tagline' => 'Open source community events with ticketing, recurring events and two-way calendar sync built in.',
                'description' => 'Compare Event Schedule with Mobilizon: open source and selfhostable too, with free registration and QR tickets, recurring events, and two-way calendar sync.',
                'keywords' => 'mobilizon alternative, mobilizon competitor, open source meetup alternative, open source event platform, selfhosted community events',
                'about' => 'Mobilizon is free, open source software (AGPLv3) for publishing local events and running groups, federated with the fediverse over ActivityPub. Framasoft launched it in 2019, and since 2024 the French nonprofit Kaihuri maintains it. Anyone can join an existing instance or run their own.',
                'competitor_strengths' => [
                    'Federated over ActivityPub, so events can be followed from Mastodon and other fediverse apps',
                    'Privacy-first, with multiple identities and anonymous participation',
                    'Free software you can selfhost, with a GraphQL API',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (free software, run by instances)', false],
                        ['Platform fees', '0%', 'N/A (no ticketing)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Participation and place limits only', true],
                        ['Online events', 'Yes (Free)', 'Live stream link on the event', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (with optional approval)', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Opt-in by each participant', true],
                    ],
                    'Integrations' => [
                        ['Calendar sync, both ways', 'Yes (Free)', 'ICS and RSS feeds, one way', true],
                    ],
                    'Unique Features' => [
                        ['Embeddable calendar', 'Yes (Free)', 'Via ICS feed or third-party plugins', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Yes (link-only events)', false],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (group roles)', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'GraphQL API', false],
                        ['Open source', 'Yes', 'Yes (AGPLv3)', false],
                        ['Selfhosting', 'Yes', 'Yes (Elixir, PostgreSQL)', false],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Tickets and Check-In',
                        'description' => 'Free registration with a QR ticket for every guest, scanned at the door on every plan, and paid tickets on Pro with zero platform fees.',
                        'icon' => 'ticket',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free, as well as a live calendar feed anyone can subscribe to.',
                        'icon' => 'calendar',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Embed in One Tag',
                        'description' => 'Put the calendar on any website with one iframe tag, free, with each event linking to its own page.',
                        'icon' => 'layout',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Hosted or Selfhosted',
                        'description' => 'Start on the hosted service with nothing to install, or selfhost on standard PHP hosting with MySQL. A selfhosted install resolves to Enterprise.',
                        'icon' => 'globe',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Built-in Newsletters',
                        'description' => 'Email the people who follow your schedule from inside the app, alongside the automatic digest of your new events.',
                        'icon' => 'mail',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'AI Event Parsing',
                        'description' => 'Paste event details or upload a flyer and the parser fills in the name, date, time and venue for you to check, on every plan.',
                        'icon' => 'ai',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Meetup', 'route' => 'marketing.compare_meetup'],
                    ['name' => 'Facebook Events', 'route' => 'marketing.compare_facebook_events'],
                    ['name' => 'Hi.Events', 'route' => 'marketing.compare_hi_events'],
                ],
                'why_choose' => [
                    'summary' => 'Mobilizon and Event Schedule are both open source and both run on your own server if you want. The difference shows once people turn up: here an event takes registrations with QR tickets, can sell tickets on Pro, and keeps your own calendars in step.',
                    'points' => [
                        'A registration limit per date, a QR ticket in each guest\'s inbox and a scanner for the door, all on the free plan',
                        'Events that repeat daily, weekly on chosen days, every few weeks or monthly by date or weekday, with exceptions',
                        'Google, Outlook and CalDAV calendars kept in step both ways, where Mobilizon publishes ICS and RSS feeds one way',
                        'A hosted service with a free plan, for a group that would rather not look after an instance',
                        'Paid tickets on Pro, or on any selfhosted install, into your own Stripe or PayPal account',
                        'Curator schedules that collect other organizers\' events onto one listing, the way a local events guide would',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Read your Mobilizon feed', 'description' => 'Paste the ICS feed Mobilizon publishes for your events into Import from a link. A calendar feed is read as written, with no AI, for the next 12 months.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'Keep it in sync while you move', 'description' => 'Press Keep this link in sync and the feed is read about once an hour, so events still posted on Mobilizon arrive too. Feeds are Enterprise on the hosted service and included when you selfhost.', 'doc' => 'managing-schedules#feeds'],
                    ['title' => 'Turn place limits into registration', 'description' => 'Where an event had a place limit, choose Free registration and set a Registration Limit, which counts each date of a series on its own.', 'doc' => 'tickets#registration'],
                    ['title' => 'Give people the new address', 'description' => 'Share the schedule page or embed it on your site. People can leave an email for new events or subscribe to its calendar feed, which needs no address at all.', 'doc' => 'sharing#calendar-feeds'],
                ],
                'limits' => [
                    ['tag' => 'No ActivityPub', 'title' => 'Not on the fediverse', 'text' => 'Mobilizon events can be followed from Mastodon and other fediverse apps, and Event Schedule does not speak ActivityPub. A selfhosted install can instead share the public events of chosen schedules with the eventschedule.com listings, each one linking back to the original.'],
                    ['tag' => 'Name and email', 'title' => 'No anonymous sign-ups', 'text' => 'Registering for an event takes a name and an email address, and one person has one identity here, not several. The lightest way in is an event\'s "Notify me" card, which asks for an email address and nothing else.'],
                    ['tag' => 'Teams on Enterprise', 'title' => 'Team members cost extra on hosted', 'text' => 'A Mobilizon group has member roles on any instance. On the hosted service, adding team members to a schedule is Enterprise and capped at five, while a selfhosted install has it built in.'],
                ],
                'faq' => [
                    ['question' => 'Are both platforms open source?', 'answer' => 'Yes. Mobilizon is AGPLv3 and written in Elixir with PostgreSQL. Event Schedule is open source under the Attribution Assurance License and runs on PHP with MySQL, and a selfhosted install resolves to Enterprise.'],
                    ['question' => 'Does Event Schedule federate like Mobilizon?', 'answer' => 'Not over ActivityPub. A selfhosted Event Schedule install can share its public events with the eventschedule.com listings, one schedule at a time and only when the owner opts in, and every listing links back to the original event.'],
                    ['question' => 'Can I sell tickets on Event Schedule?', 'answer' => 'Yes, on Pro at '.plan_price($this->planPrice()).'/mo, through your own Stripe or PayPal account with zero platform fees. Free registration with QR tickets is on every plan.'],
                    ['question' => 'Do I have to run a server?', 'answer' => 'No. The hosted service has a free plan with nothing to install. Self-hosting is there if you want your events on your own server.'],
                    ['question' => 'Can I bring my Mobilizon events across?', 'answer' => 'Yes. Mobilizon publishes an ICS feed, and Import from a link reads a calendar feed directly, listing the next 12 months of events for you to tick. To keep importing as new ones appear, turn the link into a feed, which is Enterprise on eventschedule.com and included when you selfhost.'],
                    ['question' => 'Can people take part without giving their name?', 'answer' => 'Not when they register: that asks for a name and an email address, and the QR ticket goes to that address. An event\'s interest list takes an email address only, and the live calendar feed takes nothing. There is no equivalent of Mobilizon\'s anonymous participation.'],
                ],
            ],

            // TicketLeap cells checked 2026-09-24 against ticketleap.com/info/pricing, /info/features, /solutions/reserved-seating
            // and support.ticketleap.com (fees, payouts, recurring events, users, scan app, email campaigns, hidden events,
            // Buy Tickets button). Waitlist, online events, API, webhooks, custom domains and calendar sync were not found and are left out.
            'ticketleap' => [
                'name' => 'TicketLeap',
                'key' => 'ticketleap',
                'slug' => 'ticketleap-alternative',
                'meta_title' => 'TicketLeap Alternative, No Per-Ticket Fees | Event Schedule',
                'tagline' => 'Sell tickets from your own page with zero platform fees, payouts into your own account, and calendar sync.',
                'description' => 'Compare Event Schedule with TicketLeap: zero platform fees instead of $1 + 2% a ticket plus 3% per order, paid to your own Stripe or PayPal, and calendar sync.',
                'keywords' => 'ticketleap alternative, ticketleap competitor, ticketleap fees, haunted attraction ticketing, reserved seating ticketing',
                'about' => 'TicketLeap is a self-serve ticketing platform founded in 2003 and now part of Leap Event Technology. It is free to use, sells in US, Canadian and Australian currencies, and charges a per-ticket fee plus a transaction fee that organizers can pass on to buyers.',
                'competitor_strengths' => [
                    'A free drag-and-drop seating chart builder, with tables and held seats',
                    'Scanning and box office apps, with card readers for sales at the door',
                    'No subscription or contract, and phone support on weekdays',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free to use (fees on paid tickets)', false],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'No subscription (fees per ticket)', false],
                        ['Platform fees', '0%', '$1 + 2% per ticket, plus 3% per order', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Built in (Stripe), paid out after the event', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes (TicketLeap Scan app)', false],
                        ['Recurring events', 'Yes (Free)', 'Yes (schedule builder)', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (no fees on free events)', false],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Reserved seating', 'Yes (Enterprise)', 'Yes (seating chart builder, free)', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (24 hours before)', false],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes (name your own price)', false],
                    ],
                    'Integrations' => [
                        ['Newsletters', 'Yes (Free)', 'Email campaigns to ticket buyers', false],
                    ],
                    'Customization' => [
                        ['Custom fields', 'Yes (ticket questions free, event fields Pro)', 'Yes (checkout questions and waivers)', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes (reports, GA4 and Meta Pixel)', false],
                    ],
                    'Unique Features' => [
                        ['Embeddable calendar', 'Yes (Free)', 'Buy Tickets button only', true],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Hidden events (link only)', false],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (six user roles)', false],
                    ],
                    'Platform' => [
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => '0% Platform Fees',
                        'description' => 'TicketLeap charges $1 + 2% per paid ticket plus a 3% transaction fee per order. Event Schedule takes no platform fee on any plan.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Paid Into Your Own Account',
                        'description' => 'On Pro, ticket money goes straight into your own Stripe or PayPal account instead of arriving days after the event.',
                        'icon' => 'ticket',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free, and anyone can subscribe to your schedule\'s live calendar feed.',
                        'icon' => 'calendar',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Embed on Your Website',
                        'description' => 'Put the whole calendar on any website, free, with each event linking to its own page.',
                        'icon' => 'layout',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'API and Webhooks on Pro',
                        'description' => 'A REST API and webhooks for sales, events and check-ins come with Pro.',
                        'icon' => 'link',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Read the code and selfhost it on your own server. TicketLeap is a hosted service only.',
                        'icon' => 'code',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                    ['name' => 'Brown Paper Tickets', 'route' => 'marketing.compare_brown_paper_tickets'],
                ],
                // TicketLeap's rates from TicketFees::rates() (ticketleap.com/info/pricing, checked 2026-09-24).
                // At $20 neither the $0.49 flat fee (tickets of $5 or less) nor the $20 cap applies.
                'fee_example' => [
                    'tag' => 'TicketLeap fees, worked out',
                    'title' => 'What 100 tickets at $20 cost you on TicketLeap',
                    'lead' => 'TicketLeap has no subscription. It takes $1 + 2% of the price on each ticket and a 3% online transaction fee on each order, and it runs the payment itself, so no Stripe fee is added. Here is a month of $2,000 in ticket sales on each, at the published rates.',
                    'total_label' => 'Fees on $2,000',
                    'caption' => 'Fees on 100 tickets at $20, each bought in its own order, on Event Schedule Pro, TicketLeap and Eventbrite.',
                    'rows' => $this->feeExample(100, 20, [$this->ourFeeRow(), $this->ratesFeeRow('ticketleap'), $this->ratesFeeRow('eventbrite')]),
                    'notes' => [
                        'Counting the card fee on both sides, a $20 ticket costs $'.number_format($this->feePerTicket($this->ratesFeeRow('ticketleap'), 20), 2).' on TicketLeap and $'.number_format($this->feePerTicket($this->ourFeeRow(), 20), 2).' on Event Schedule, so Pro pays for itself once a month passes '.$this->feeBreakEven($this->ratesFeeRow('ticketleap'), 20).' tickets.',
                        'On a ticket of $'.number_format(TicketFees::rates()['ticketleap']['low_price']).' or less TicketLeap charges a flat $'.number_format(TicketFees::rates()['ticketleap']['low_fixed'], 2).' instead, and its per-ticket fee never passes $20, so its share is lighter on cheap tickets and capped on dear ones. The 3% per order has no cap.',
                        'These are TicketLeap\'s US rates as published in September 2026.',
                    ],
                ],
                'why_choose' => [
                    'summary' => 'Against TicketLeap the question is who holds the money, and for how long. TicketLeap takes its fees out and pays you days after each event; Event Schedule takes no fee and never holds the money at all.',
                    'points' => [
                        'No $1 + 2% on each ticket and no 3% on each order, on any plan',
                        'Each order is charged through your own Stripe or PayPal account the moment it is placed',
                        'Prices in any of '.count(MoneyUtils::currencies()).' currencies, from the euro and the pound to the rupee and the rand, not only US, Canadian and Australian dollars',
                        'A full calendar of your dates for your own website, free, where TicketLeap gives you a Buy Tickets button',
                        'Free events take registrations with QR tickets, scanned in a phone browser, on every plan',
                        'A REST API and webhooks on Pro, and Google, Outlook and CalDAV calendars synced both ways for free',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Let current sales run out', 'description' => 'Leave the events already on sale at TicketLeap until they are over, and put the next ones here, so nobody who has bought is asked to buy again.', 'doc' => 'getting-started#create-schedule'],
                    ['title' => 'Paste in the coming dates', 'description' => 'Copy the text of a TicketLeap event page, or your own list of dates, into AI import. A run of shows can be one recurring event, with each date counted on its own.', 'doc' => 'ai-import#text-import'],
                    ['title' => 'Recreate ticket types and codes', 'description' => 'Ticket types, prices and sales start and end dates go on the event\'s Tickets tab, and promo codes are on Pro. A house with numbered seats needs Enterprise for a seating plan.', 'doc' => 'tickets#ticket-types'],
                    ['title' => 'Connect your own payment account', 'description' => 'Upgrade to Pro and connect Stripe or PayPal. Orders settle into that account as they are placed, rather than by direct deposit after the event date.', 'doc' => 'tickets#payment'],
                ],
                'limits' => [
                    ['tag' => 'Seating is Enterprise', 'title' => 'The free chart builder is theirs', 'text' => 'TicketLeap includes its seating chart builder, with tables and held seats, at no charge. Here allocated seating is on Enterprise, and Free and Pro sell general admission ticket types.'],
                    ['tag' => 'No card readers', 'title' => 'Nothing to swipe a card on', 'text' => 'TicketLeap sells at the door with box office apps and card readers. Event Schedule has no card hardware: the door scans tickets in a phone browser, and a walk-up buyer pays on their own phone through the event page.'],
                    ['tag' => 'No waivers', 'title' => 'Terms are a link, not a signature', 'text' => 'TicketLeap can put a waiver into its checkout. A ticket here can carry a link to your own terms, printed on every ticket, but buyers are not asked to accept it when they pay.'],
                ],
                'faq' => [
                    ['question' => 'How does Event Schedule pricing compare to TicketLeap?', 'answer' => 'TicketLeap has no subscription and charges $1 + 2% on each paid ticket, plus a 3% transaction fee per order, passed on to buyers or absorbed by you. Event Schedule Pro is a flat '.plan_price($this->planPrice()).'/mo with zero platform fees, so you pay only Stripe\'s or PayPal\'s own processing.'],
                    ['question' => 'When does the money from ticket sales reach me?', 'answer' => 'On Pro, paid tickets are charged through your own Stripe or PayPal account, so the money goes there as each order is placed and follows their normal payout schedule. TicketLeap pays organizers by direct deposit 4 to 6 business days after each event date, or weekly once approved for its FastPay advances.'],
                    ['question' => 'Can I price tickets in my own currency?', 'answer' => 'Yes, on Pro. Payments go through your own Stripe or PayPal account, so you can price tickets in most of the currencies they support. TicketLeap sells in US, Canadian and Australian dollars.'],
                    ['question' => 'Does Event Schedule have reserved seating like TicketLeap?', 'answer' => 'Yes, on Enterprise. A venue schedule draws its room once, with sections, rows, tables, standing areas and wheelchair spaces, and buyers pick their seats from the map at checkout. TicketLeap includes its seating chart builder at no extra cost.'],
                    ['question' => 'Can I put my events on my own website?', 'answer' => 'Yes. The embeddable calendar shows your upcoming events on any website, free, and each event links to its own page. On Pro, the ticket widget puts checkout on your site too. TicketLeap offers a Buy Tickets button that links to its own event page.'],
                    ['question' => 'Is it easy to switch from TicketLeap?', 'answer' => 'Yes. Create a free schedule, paste your event details or upload a flyer and the AI parser fills in each event for you to check. Then connect Stripe or PayPal and move to Pro to sell paid tickets.'],
                    ['question' => 'Can door staff scan tickets without seeing sales?', 'answer' => 'Yes. A team member at the viewer level can scan tickets and sees no sales, while an admin runs the schedule day to day. Adding team members is on Enterprise, up to five on eventschedule.com. TicketLeap has six user roles to choose from.'],
                    ['question' => 'What about events already on sale at TicketLeap?', 'answer' => 'Leave them there until they are over. Meanwhile an event here can point to its TicketLeap page with Tickets elsewhere, which is on every plan, so your schedule shows every date in one place.'],
                ],
            ],

            // Songkick cells checked 2026-09-24 against songkick.com (/info/about, /info/privacy, /developer), tourbox.songkick.com,
            // campaigns.songkick.com and support.songkick.com (Tourbox, ticket links, widget, venues and promoters, insights,
            // campaigns, live streams). Songkick sells no tickets. Calendar sync and team roles were not verified and are left out.
            'songkick' => [
                'name' => 'Songkick',
                'key' => 'songkick',
                'slug' => 'songkick-alternative',
                'meta_title' => 'Songkick Alternative With Your Own Tickets | Event Schedule',
                'tagline' => 'A schedule page that takes registrations and sells its own tickets, for artists and venues alike.',
                'description' => 'Compare Event Schedule with Songkick: your own schedule page with free RSVPs and QR tickets, paid tickets on Pro, two-way calendar sync, and venue tools too.',
                'keywords' => 'songkick alternative, songkick tourbox alternative, tour dates widget, concert listings for artists, songkick competitor',
                'about' => 'Songkick is a concert discovery app and website that lists tour dates gathered from hundreds of sources and alerts fans when artists they track announce shows. Artist teams manage their dates with the Tourbox tool, and tickets are sold by outside vendors. Suno acquired Songkick from Warner Music Group in a deal announced in November 2025.',
                'competitor_strengths' => [
                    'A large fan audience that gets alerts when the artists they track announce dates',
                    'The free Tourbox tool, with a tour-date widget and partners such as Deezer, Bandcamp and SoundCloud',
                    'Paid email campaigns to an artist\'s Songkick fans, and fan insights for bigger artists',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (Tourbox for artists)', false],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'Free (paid email campaigns)', false],
                        ['Platform fees', '0%', 'N/A (links to outside ticket sellers)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'No (links to outside ticket sellers)', true],
                        ['Online events', 'Yes (Free)', 'Yes (live stream listings)', false],
                        ['Free event RSVP', 'Yes (Free)', 'No (fans mark Interested or Going)', true],
                    ],
                    'Integrations' => [
                        ['Newsletters', 'Yes (Free)', 'Automatic fan alerts, paid campaigns', false],
                    ],
                    'Customization' => [
                        ['Built-in analytics', 'Yes (Free)', 'Fan insights from 3,000 trackers', true],
                    ],
                    'Unique Features' => [
                        ['Embeddable calendar', 'Yes (Free)', 'Yes (Tourbox widget)', false],
                        ['Venue and promoter tools', 'Yes (venue and curator schedules)', 'No (Tourbox is for artist teams)', true],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Paid licence, partners only', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'Tickets on Your Own Page',
                        'description' => 'Songkick links fans out to other ticket sellers. Event Schedule takes free registrations with QR tickets on every plan, and paid tickets on Pro with zero platform fees.',
                        'icon' => 'ticket',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'For Venues Too',
                        'description' => 'Venue, talent and curator schedules link to each other, so a show a venue lists can appear on the performer\'s schedule as well.',
                        'icon' => 'globe',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Your Own Audience',
                        'description' => 'People who sign up on your schedule get an email when you add dates, and you can write to them yourself from inside the app.',
                        'icon' => 'mail',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free, so the dates in your calendar and on your page never disagree.',
                        'icon' => 'calendar',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Event Graphics',
                        'description' => 'Turn your upcoming dates into a shareable graphic for social media in one click, free.',
                        'icon' => 'image',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Read the code and selfhost it on your own server. Songkick is a hosted service only.',
                        'icon' => 'code',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Bandsintown', 'route' => 'marketing.compare_bandsintown'],
                    ['name' => 'DICE', 'route' => 'marketing.compare_dice'],
                    ['name' => 'Facebook Events', 'route' => 'marketing.compare_facebook_events'],
                ],
                'why_choose' => [
                    'summary' => 'Songkick is where fans learn that a date exists. Event Schedule is where the date lives: one page of your own that takes the guest list, sells the ticket on Pro, and is the same page wherever a fan found the link.',
                    'points' => [
                        'The ticket link in Tourbox can point to your own event page, so a fan who finds you there buys from you',
                        'Free registration with a QR ticket, for guest-list nights and free shows',
                        'A booking form on every talent schedule, which venues and promoters use to ask you for a date, free',
                        'Fans can post their videos and photos from a show to its page once you approve them',
                        'A digest of your new dates goes to email subscribers by itself and is not counted against your newsletter allowance',
                        'Paid tickets on Pro at '.plan_price($this->planPrice()).'/mo, with no platform fee on top of Stripe or PayPal',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Start a talent schedule', 'description' => 'Pick Talent when you create it. That type comes with the booking form, and every show on it can name the venue it is at.', 'doc' => 'getting-started#schedule-types'],
                    ['title' => 'Copy your dates over', 'description' => 'Paste your tour dates as text, or upload the tour poster, into AI import, and check each show before it is saved.', 'doc' => 'ai-import#image-import'],
                    ['title' => 'Name the venues', 'description' => 'A venue that is not on Event Schedule yet gets a schedule created for it, which the venue can claim by signing in with the address on it.', 'doc' => 'creating-events#claim'],
                    ['title' => 'Point Tourbox at your pages', 'description' => 'Add each event page as the ticket link in Tourbox, and put the embeddable calendar on your own website beside the Tourbox widget or in its place.', 'doc' => 'sharing#embed-code'],
                ],
                'limits' => [
                    ['tag' => 'No alerts network', 'title' => 'Nobody is tracking you here yet', 'text' => 'Songkick alerts fans about any artist they track, across its whole app. Here only the people who signed up on your schedule hear about a new date, so the list is the one you build.'],
                    ['tag' => 'No streaming partners', 'title' => 'Your dates stay on your pages', 'text' => 'Tourbox sends dates on to partners such as Deezer, Bandcamp and SoundCloud, and Event Schedule has no such partners. Your dates reach fans through your schedule page, the embed on your website and the calendar feed they subscribe to.'],
                    ['tag' => 'Your page only', 'title' => 'No data on fans elsewhere', 'text' => 'Songkick\'s fan insights draw on people who track an artist inside its app. The analytics here count the views of your own schedule and event pages and where those visitors came from, free, and know nothing about anyone who has not been there.'],
                ],
                'faq' => [
                    ['question' => 'Does Songkick sell tickets?', 'answer' => 'No. Songkick lists dates from hundreds of sources and links fans to outside ticket sellers, so orders and refunds are handled by whoever sold the ticket. Event Schedule takes free registrations with QR tickets on every plan, and paid tickets on Pro with zero platform fees.'],
                    ['question' => 'Can I use Event Schedule alongside Songkick?', 'answer' => 'Yes. Keep your Songkick listing for its fan alerts and add your Event Schedule event page as the ticket link in Tourbox, so fans who find a date there land on your own page. Paste your dates in and the AI parser fills in each show for you to check.'],
                    ['question' => 'Can venues and promoters use Event Schedule?', 'answer' => 'Yes. Venues, talent and curators each get their own schedule, and a show a venue lists can appear on the performer\'s schedule too. Songkick\'s Tourbox is only for artist teams, and venues and promoters cannot add ticket links themselves.'],
                    ['question' => 'How does Event Schedule pricing compare to Songkick?', 'answer' => 'Songkick is free for artists, with paid email campaigns to their fans. Event Schedule is free for listing dates, registrations and newsletters, and Pro is '.plan_price($this->planPrice()).'/mo for paid tickets with zero platform fees.'],
                    ['question' => 'Can I put my tour dates on my website?', 'answer' => 'Yes. The embeddable calendar shows your upcoming dates on any website, free, and each date links to its own event page. Fans can also subscribe to your schedule\'s live calendar feed, so a moved date updates in their own calendar.'],
                    ['question' => 'Will my fans be told when I add a date?', 'answer' => 'The ones who signed up on your schedule will. People who leave their email address get a digest of the new dates you publish, at most one every 72 hours, and fans who subscribe to the calendar feed see new dates in their own calendar. Nobody who has never visited your page is alerted.'],
                    ['question' => 'Can a promoter or festival list several artists?', 'answer' => 'Yes. A curator schedule lists events from other schedules, and can take talent and venue schedules as sources so every date they publish appears on it automatically. It is free on every plan, which suits a promoter running a season or a festival line-up.'],
                ],
            ],

            // AllEvents cells checked 2026-09-24 against allevents.in/pages/pricing (and its plans_prices feed), /pages/event-ticketing,
            // /pages/event-marketing, /pages/events-api and support.allevents.in (commission, payouts, recurring events, seating,
            // private events, plugins, ticket embeds, webhooks, admins, QR scanner, discount codes, email credits). Waitlist,
            // calendar sync and custom domains were not found and are left out.
            'allevents' => [
                'name' => 'AllEvents',
                'key' => 'allevents',
                'slug' => 'allevents-alternative',
                'meta_title' => 'AllEvents Alternative With No Booking Fees | Event Schedule',
                'tagline' => 'Sell tickets from your own schedule page with no booking fee, unlimited events and two-way calendar sync.',
                'description' => 'Compare Event Schedule with AllEvents: no per-ticket booking fee, unlimited events on the free plan, two-way calendar sync, and the API and webhooks on Pro.',
                'keywords' => 'allevents alternative, allevents.in alternative, allevents competitor, event listing platform alternative, sell event tickets online',
                'about' => 'AllEvents (allevents.in) is an event discovery platform founded in 2011 that lists events in over 40,000 cities, many of them gathered from other websites. Organizers can publish events and sell tickets for a booking fee charged to buyers, and buy paid promotion.',
                'competitor_strengths' => [
                    'A discovery marketplace it says reaches 20 million event seekers a month',
                    'Paid promotion run by its own marketing team, plus email invite credits',
                    'Ticket money paid straight into your own Stripe or PayPal account, outside India',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Yes (5 active upcoming events)', false],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'USD 12, 60 or 200/mo', false],
                        ['Platform fees', '0%', 'USD 1 per paid ticket (buyers pay by default)', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Your Stripe or PayPal (India: AllEvents)', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes (Event Manager app)', false],
                        ['Recurring events', 'Yes (Free)', 'Yes', false],
                        ['Online events', 'Yes (Free)', 'Yes', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (free tickets)', false],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Reserved seating', 'Yes (Enterprise)', 'Seat map built by their team on request', false],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (Essential plan and up)', true],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes (donation tickets)', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'Yes (Essential plan and up)', false],
                    ],
                    'Integrations' => [
                        ['Newsletters', 'Yes (Free)', 'Email invite credits, buyer broadcasts', false],
                    ],
                    'Customization' => [
                        ['Custom fields', 'Yes (ticket questions free, event fields Pro)', 'Yes (custom registration form)', false],
                    ],
                    'Unique Features' => [
                        ['Embeddable calendar', 'Yes (Free)', 'Yes (event list and ticket embeds)', false],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Private (link-only) listings', false],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (event admins)', false],
                        ['Event boost (ads)', 'Yes (Pro)', 'Paid promotion packages', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'Events data API by request only', true],
                        ['Webhooks', 'Yes (Pro)', 'Essential plan (USD 12/mo) and up', true],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => 'No Booking Fee',
                        'description' => 'AllEvents adds USD 1 to each paid ticket sold online, and 10% + INR 10 to one priced in rupees. Event Schedule takes no platform fee on any plan, and Pro is a flat '.plan_price($this->planPrice()).'/mo.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Unlimited Events, Free',
                        'description' => 'The free plan has no cap on events. The AllEvents free plan allows 5 upcoming events at a time.',
                        'icon' => 'ticket',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free, and anyone can subscribe to your schedule\'s live calendar feed.',
                        'icon' => 'calendar',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'API and Webhooks on Pro',
                        'description' => 'Both come with Pro. On AllEvents, webhooks start on the Essential plan at USD 12 a month.',
                        'icon' => 'link',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'Your Own Page',
                        'description' => 'Your schedule lists only your events, with your followers, instead of sitting in a city feed beside everyone else\'s.',
                        'icon' => 'layout',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Read the code and selfhost it on your own server. AllEvents is a hosted service only.',
                        'icon' => 'code',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'Meetup', 'route' => 'marketing.compare_meetup'],
                    ['name' => 'Facebook Events', 'route' => 'marketing.compare_facebook_events'],
                ],
                // AllEvents' booking fee from TicketFees::rates() (allevents.in pricing feed, checked 2026-09-24).
                'fee_example' => [
                    'tag' => 'AllEvents fees, worked out',
                    'title' => 'What 100 tickets at $20 cost you on AllEvents',
                    'lead' => 'AllEvents adds a $1 booking fee to each paid ticket sold online, charged to the buyer unless you absorb it. Outside India the ticket money settles into your own Stripe or PayPal account, whose fee comes on top. The table absorbs the booking fee, which is what keeping your advertised price costs.',
                    'total_label' => 'Fees on $2,000',
                    'caption' => 'Fees on 100 tickets at $20, each bought in its own order, on Event Schedule Pro, AllEvents and Eventbrite.',
                    'rows' => $this->feeExample(100, 20, [$this->ourFeeRow(), $this->ratesFeeRow('allevents'), $this->ratesFeeRow('eventbrite')]),
                    'notes' => [
                        'Both settle through your own Stripe account, so the card fee is the same on each, and the difference is $1 a ticket against Pro\'s '.plan_price($this->planPrice()).' a month: Pro costs less once a month passes '.$this->feeBreakEven($this->ratesFeeRow('allevents'), 20).' paid tickets.',
                        'Passed to the buyer instead, the booking fee costs you nothing and costs every buyer $1, so a $20 ticket checks out at $21.',
                        'Events priced in Indian rupees pay 10% + INR 10 a ticket instead, collected by AllEvents itself. The table uses the US dollar rate published in September 2026.',
                    ],
                ],
                'why_choose' => [
                    'summary' => 'AllEvents is a city feed you post into, with a cap on upcoming events and a dollar on each paid ticket. Event Schedule is a page that holds only your events, as many as you like, with nothing added to the price a buyer sees.',
                    'points' => [
                        'No USD 1 booking fee for buyers to pay or for you to absorb',
                        'As many upcoming events as you want on the free plan, where AllEvents lists 5 at a time',
                        'Rupee prices through Razorpay on Pro, settled into your own Razorpay account rather than collected by AllEvents',
                        'A curator schedule that gathers other organizers\' events, for a city guide that is yours',
                        'Registration and door scanning in a phone browser on every plan, with no Event Manager app',
                        'A Facebook and Instagram ad for an event, set up from inside the app, on Pro',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Pull your listings across', 'description' => 'Paste the link to an AllEvents event page into Import from a link, check what it fills in, and save. A page the AI has to read counts once against the daily import allowance.', 'doc' => 'ai-import#link-import'],
                    ['title' => 'List other organizers too', 'description' => 'If you posted other people\'s events to AllEvents, make a curator schedule and add the ones already on Event Schedule as sources, so their dates arrive as they publish them.', 'doc' => 'creating-schedules#event-sources'],
                    ['title' => 'Take payment without the booking fee', 'description' => 'Connect Stripe or PayPal, or Razorpay for events priced in rupees, and tickets carry no booking fee. Paid tickets are on Pro.', 'doc' => 'tickets#razorpay'],
                    ['title' => 'Build an audience of your own', 'description' => 'Show the follower QR code at your events. Whoever scans it can sign up to hear about your next ones from you, not from a city feed.', 'doc' => 'sharing#qr-code'],
                ],
                'limits' => [
                    ['tag' => 'No city feed', 'title' => 'Strangers will not stumble on you', 'text' => 'AllEvents says it reaches 20 million event seekers a month, in listings across more than 40,000 cities. The Event Schedule browse page shows one upcoming event per schedule, a far smaller window, so most of your audience will arrive through links you share.'],
                    ['tag' => 'No promotion team', 'title' => 'Nobody runs a campaign for you', 'text' => 'AllEvents sells promotion run by its own marketing team. The nearest thing here is boosting an event on Pro, a Facebook and Instagram ad that you set up and pay for yourself.'],
                    ['tag' => 'Newsletter allowance', 'title' => 'Sends are counted per recipient', 'text' => 'On eventschedule.com a newsletter counts each recipient against a monthly allowance: 10 on Free, 100 on Pro and 1,000 on Enterprise. The limit lifts once the schedule sends through its own email settings, and the automatic digest of new events never counts against it.'],
                ],
                'faq' => [
                    ['question' => 'How does Event Schedule pricing compare to AllEvents?', 'answer' => 'AllEvents adds a booking fee of USD 1 to each paid ticket sold online (10% + INR 10 on events priced in rupees), charged to buyers unless you absorb it, and its paid plans at USD 12, 60 and 200 a month raise how many upcoming events you can list at once. Event Schedule has no booking fee, and Pro at '.plan_price($this->planPrice()).'/mo adds paid tickets with zero platform fees.'],
                    ['question' => 'Do I need a paid plan to list more events?', 'answer' => 'No. The free plan has no limit on events, and free registration with QR tickets is unlimited too. The AllEvents free plan allows 5 upcoming events at a time, and its paid plans raise that to 20, 100 or more.'],
                    ['question' => 'When do I get paid for ticket sales?', 'answer' => 'On Pro, paid tickets are charged through your own Stripe or PayPal account, so the money goes there as each order is placed. AllEvents also pays into your own PayPal or Stripe account, except for events priced in Indian rupees, which it pays out a few business days after the event.'],
                    ['question' => 'Can Event Schedule promote my event like AllEvents?', 'answer' => 'Event Schedule is built around your own schedule page rather than a shared marketplace. People who sign up on your schedule get an email when you add events, you can send newsletters, and on Pro you can boost an event with a Facebook and Instagram ad. If reaching strangers in your city matters most, AllEvents does more of that.'],
                    ['question' => 'Can I sell tickets on my own website?', 'answer' => 'Yes, on Pro. The ticket widget puts checkout on your own site, and the embeddable calendar, which is free, lists every upcoming event. AllEvents offers event list plugins and a ticket embed as well.'],
                    ['question' => 'Are the API and webhooks included?', 'answer' => 'Yes, both are on Pro at '.plan_price($this->planPrice()).'/mo, with a REST API for events, schedules and sales. AllEvents includes webhooks from its Essential plan at USD 12 a month, and its API is a data product for event listings rather than for managing your own events.'],
                    ['question' => 'Can people who do not know me still find my events?', 'answer' => 'Some can. Public events can appear on the Event Schedule browse page, which shows one upcoming event from each schedule, and every public event page can be listed by search engines. That is a much smaller audience than AllEvents\' city listings.'],
                    ['question' => 'Can I sell tickets for an event priced in rupees?', 'answer' => 'Yes, on Pro, through Razorpay, which settles Indian rupees into your own Razorpay account with no platform fee from us. AllEvents charges buyers INR 10 a ticket on rupee events and takes 5% to 10% of sales from the organizer, and pays out a few business days after the event.'],
                ],
            ],

            // Universe cells checked 2026-09-24 against universe.com/pricing (plan list and its embedded fee table), universe.com/features
            // and support.universe.com (fees, payment processing, payouts, waitlist, discount codes, BoxOffice, embeddable widgets,
            // webhooks, OAuth API, Mailchimp, message attendees, private events, product updates). Still onboarding organizers.
            // Custom domains and calendar sync were not found and are left out.
            'universe' => [
                'name' => 'Universe',
                'key' => 'universe',
                'slug' => 'universe-alternative',
                'meta_title' => 'Universe Alternative With 0% Platform Fees | Event Schedule',
                'tagline' => 'Ticketing with zero platform fees, payouts into your own account, two-way calendar sync, and reserved seating on Enterprise.',
                'description' => 'Compare Event Schedule with Universe: 0% platform fees instead of 2% + $0.79 a ticket plus 3% processing, calendar sync, and reserved seating on Enterprise.',
                'keywords' => 'universe alternative, universe.com alternative, universe ticketing alternative, ticketmaster universe alternative, timed entry ticketing',
                'about' => 'Universe is a self-serve ticketing platform owned by Ticketmaster since 2015, used by attractions, clubs and event organizers. Publishing is free, paid tickets carry a per-ticket service fee plus a processing fee, and custom Pro plans add selling on Ticketmaster.',
                'competitor_strengths' => [
                    'Timed-entry tools for attractions, with repeating timeslots that each have their own prices and capacity',
                    'Selling on Ticketmaster, on custom Pro plans',
                    'The BoxOffice app for scanning and card sales at the door, plus a GraphQL API and webhooks',
                ],
                'sections' => [
                    'Pricing & Fees' => [
                        ['Free plan', 'Yes (forever)', 'Free to publish (fees on paid tickets)', false],
                        ['Paid plan price', plan_price($this->planPrice()).'/mo ('.$this->trialDays().'-day free trial)', 'No subscription (Pro plans custom)', false],
                        ['Platform fees', '0%', '2% + $0.79 per ticket (US Starter)', true],
                        ['Payment processing', 'Stripe/PayPal/Payfast/Razorpay (processor rates apply)', 'Universe Payments (3%) or your own Stripe', false],
                    ],
                    'Events & Ticketing' => [
                        ['Ticketing', 'Yes (free RSVP, paid on Pro)', 'Yes', false],
                        ['QR check-ins', 'Yes (Free)', 'Yes (BoxOffice app)', false],
                        ['Ticket waitlist', 'Yes (Pro)', 'Yes (automatic)', false],
                        ['Recurring events', 'Yes (Free)', 'Yes (repeating timeslots)', false],
                        ['Online events', 'Yes (Free)', 'Yes (virtual events)', false],
                        ['Free event RSVP', 'Yes (Free)', 'Yes (no fees on free events)', false],
                        ['Promo/discount codes', 'Yes (Pro)', 'Yes', false],
                        ['Reserved seating', 'Yes (Enterprise)', 'No (retired in 2019)', true],
                        ['Reminder emails before the event', 'Yes (every plan; on by default on paid plans)', 'Yes (24 hours before)', false],
                        ['Donations / pay what you want', 'Yes (Pro)', 'Yes', false],
                        ['Promoter links with commission', 'Yes (Pro)', 'Tracking links, no commission', true],
                    ],
                    'Integrations' => [
                        ['Newsletters', 'Yes (Free)', 'Message attendees, Mailchimp sync', false],
                    ],
                    'Customization' => [
                        ['Custom fields', 'Yes (ticket questions free, event fields Pro)', 'Yes (checkout questions)', false],
                        ['Built-in analytics', 'Yes (Free)', 'Yes (dashboards and reports)', false],
                    ],
                    'Unique Features' => [
                        ['Embeddable calendar', 'Yes (Free)', 'Yes (ticket, listings and calendar widgets)', false],
                        ['Private/password-protected events', 'Yes (Enterprise)', 'Unlisted events, password-protected ticket types', false],
                        ['Team collaboration', 'Yes (Enterprise)', 'Yes (Standard tier)', false],
                    ],
                    'Platform' => [
                        ['REST API', 'Yes (Pro)', 'GraphQL API', false],
                        ['Webhooks', 'Yes (Pro)', 'Yes (ticket purchase and update)', false],
                        ['Open source', 'Yes', 'No', true],
                        ['Selfhosting', 'Yes', 'No', true],
                    ],
                ],
                'key_advantages' => [
                    [
                        'title' => '0% Platform Fees',
                        'description' => 'Universe charges US organizers 2% + $0.79 per ticket on Starter plus 3% processing. Event Schedule takes no platform fee on any plan.',
                        'icon' => 'dollar',
                        'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30',
                        'border' => 'border-emerald-200 dark:border-emerald-500/20',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                    [
                        'title' => 'Paid Into Your Own Account',
                        'description' => 'On Pro, ticket money goes straight into your own Stripe or PayPal account, not into a balance you withdraw after the event.',
                        'icon' => 'ticket',
                        'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30',
                        'border' => 'border-sky-200 dark:border-sky-500/20',
                        'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
                        'icon_color' => 'text-sky-600 dark:text-sky-400',
                    ],
                    [
                        'title' => 'Reserved Seating',
                        'description' => 'Venue schedules draw a seat map once and buyers pick their seats, on Enterprise. Universe retired seating plans in 2019.',
                        'icon' => 'layout',
                        'gradient' => 'from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30',
                        'border' => 'border-teal-200 dark:border-teal-500/20',
                        'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
                        'icon_color' => 'text-teal-600 dark:text-teal-400',
                    ],
                    [
                        'title' => 'Two-Way Calendar Sync',
                        'description' => 'Google, Outlook and CalDAV sync both ways, free, and anyone can subscribe to your schedule\'s live calendar feed.',
                        'icon' => 'calendar',
                        'gradient' => 'from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30',
                        'border' => 'border-blue-200 dark:border-blue-500/20',
                        'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
                        'icon_color' => 'text-blue-600 dark:text-blue-400',
                    ],
                    [
                        'title' => 'A Public Schedule Page',
                        'description' => 'Every event sits on one public schedule page with followers, sub-schedules and an embeddable calendar.',
                        'icon' => 'globe',
                        'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30',
                        'border' => 'border-amber-200 dark:border-amber-500/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                    ],
                    [
                        'title' => 'Open Source',
                        'description' => 'Read the code and selfhost it on your own server. Universe is a hosted service only.',
                        'icon' => 'code',
                        'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30',
                        'border' => 'border-cyan-200 dark:border-cyan-500/20',
                        'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
                        'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                    ],
                ],
                'cross_links' => [
                    ['name' => 'Eventbrite', 'route' => 'marketing.compare_eventbrite'],
                    ['name' => 'DICE', 'route' => 'marketing.compare_dice'],
                    ['name' => 'Ticket Tailor', 'route' => 'marketing.compare_ticket_tailor'],
                ],
                // Universe's US Starter rates from TicketFees::rates() (support.universe.com, checked 2026-09-24).
                // At $20 the $19.95 cap on the service fee does not apply.
                'fee_example' => [
                    'tag' => 'Universe fees, worked out',
                    'title' => 'What 100 tickets at $20 cost you on Universe',
                    'lead' => 'On its US Starter tier Universe charges a 2% + $0.79 service fee on each ticket, capped at $'.number_format(TicketFees::rates()['universe']['cap'], 2).', and a 3% processing fee through Universe Payments. Here is one month of $2,000 in ticket sales on each, with every fee absorbed by the organizer.',
                    'total_label' => 'Fees on $2,000',
                    'caption' => 'Fees on 100 tickets at $20, each bought in its own order, on Event Schedule Pro, Universe Starter and Eventbrite.',
                    'rows' => $this->feeExample(100, 20, [$this->ourFeeRow(), $this->ratesFeeRow('universe', 'Universe Starter'), $this->ratesFeeRow('eventbrite')]),
                    'notes' => [
                        'A $20 ticket costs $'.number_format($this->feePerTicket($this->ratesFeeRow('universe'), 20), 2).' in fees on Universe and $'.number_format($this->feePerTicket($this->ourFeeRow(), 20), 2).' on Event Schedule, so Pro costs less once a month passes '.$this->feeBreakEven($this->ratesFeeRow('universe'), 20).' tickets.',
                        'Connect your own Stripe account to Universe and the service fee stays, with Stripe\'s rate in place of the 3%. The table uses Universe Payments.',
                        'These are the US Starter rates in Universe\'s help center as of September 2026.',
                    ],
                ],
                'why_choose' => [
                    'summary' => 'On Universe Payments a paid ticket carries a service fee and a processing fee, and the money waits in a Universe balance until 48 hours after the event ends. Event Schedule adds no fee of its own and charges each order through your own account as it comes in.',
                    'points' => [
                        'No 2% + $0.79 service fee on each ticket, and no 3% processing fee on top of it',
                        'Seat maps for venue schedules on Enterprise, something Universe stopped offering in 2019',
                        'Each date of a recurring event keeps its own tickets and capacity, on the free plan',
                        'Followers and email subscribers who hear about your next event, not only the people who bought for the last one',
                        'Free registration and door scanning on every plan, with no BoxOffice app to install',
                        'Open source, with a selfhosted install that includes every Enterprise feature',
                    ],
                ],
                'switch_steps' => [
                    ['title' => 'Bring the events in', 'description' => 'Paste each Universe listing, as a link or as its text, into AI import, which fills in the event for you to check before it is saved.', 'doc' => 'ai-import'],
                    ['title' => 'Turn timeslots into dates', 'description' => 'An event that repeated on Universe becomes one recurring event, daily or weekly on chosen days, with each date\'s tickets and capacity counted on their own.', 'doc' => 'creating-events#recurring'],
                    ['title' => 'Recreate questions and codes', 'description' => 'Checkout questions become questions on a ticket type, free. Discount codes become promo codes, and the waitlist for sold-out tickets switches on, both on Pro.', 'doc' => 'tickets#checkout-fields'],
                    ['title' => 'Connect your own Stripe', 'description' => 'If you connected your own Stripe account to Universe, connect it here as well, or use PayPal. Selling a priced ticket is on Pro, and no service fee is added.', 'doc' => 'tickets#stripe'],
                ],
                'limits' => [
                    ['tag' => 'No timed entry', 'title' => 'Dates, not half-hour slots', 'text' => 'Universe\'s timed-entry tools give an attraction many entry times a day, each with its own price and capacity. A recurring event here repeats by date at one start time, so it suits a weekly class or a nightly show better than an attraction that admits a group every half hour.'],
                    ['tag' => 'No Ticketmaster', 'title' => 'Tickets sell where you put them', 'text' => 'Universe\'s custom Pro plans can list your tickets on Ticketmaster. Event Schedule has no distribution partner: tickets sell on your schedule and event pages, and on Pro in the ticket widget on your own website.'],
                    ['tag' => 'No Mailchimp sync', 'title' => 'The mailing list lives here', 'text' => 'Universe can sync attendees to Mailchimp, and Event Schedule does not connect to Mailchimp at all. Buyers who tick the box at checkout join your email subscribers, and newsletters to them are written and sent from inside the app.'],
                ],
                'faq' => [
                    ['question' => 'How does Event Schedule pricing compare to Universe?', 'answer' => 'Universe charges US organizers 2% + $0.79 per paid ticket on its Starter plan, plus a 3% processing fee through Universe Payments, passed on to buyers or absorbed by you. Event Schedule Pro is a flat '.plan_price($this->planPrice()).'/mo with zero platform fees, so you pay only Stripe\'s or PayPal\'s own processing.'],
                    ['question' => 'When do I get paid?', 'answer' => 'On Pro, paid tickets are charged through your own Stripe or PayPal account, so the money goes there as each order is placed. With Universe Payments, funds reach your Universe balance 48 hours after the event ends, and connecting your own Stripe account is its option for money before the event.'],
                    ['question' => 'Does Event Schedule have reserved seating?', 'answer' => 'Yes, on Enterprise. A venue schedule draws its room once, with sections, rows, tables, standing areas and wheelchair spaces, and buyers pick their seats from the map at checkout. Universe stopped offering seating plans for new listings in 2019.'],
                    ['question' => 'Can I run a long series of dates?', 'answer' => 'Yes. Recurring events repeat daily, weekly on chosen days, every few weeks or monthly, with dates you can skip, and each date keeps its own tickets and capacity. For attractions with many entry times a day, Universe\'s timed-entry events go further.'],
                    ['question' => 'Can I keep my events in Google Calendar?', 'answer' => 'Yes. Google Calendar sync runs both ways on every plan, and Outlook and CalDAV calendars sync the same way. Anyone can also subscribe to your schedule\'s live calendar feed.'],
                    ['question' => 'Is it easy to switch from Universe?', 'answer' => 'Yes. Create a free schedule, paste your event details or upload a flyer and the AI parser fills in each event for you to check, then connect Stripe or PayPal and move to Pro to sell paid tickets.'],
                    ['question' => 'Does Event Schedule have a door app like BoxOffice?', 'answer' => 'There is nothing to install: tickets are scanned in the browser on any phone, free on every plan, and each ticket admits once. Pro adds a live count of arrivals, broken down by ticket type. Taking a card payment in person is not something Event Schedule does.'],
                    ['question' => 'Can I keep some tickets for invited guests only?', 'answer' => 'Not with a password on one ticket type, as Universe allows. On Enterprise a whole event can be Unlisted, reachable only by its link and optionally behind a password, and on Pro a promo code gives the people you choose a lower price.'],
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
                'tagline' => 'A form collects answers. An event needs places counted, a ticket sent and a list that works at the door.',
                'meta_title' => 'Replace Google Forms for Event Registration | Event Schedule',
                'description' => 'How events really run on Google Forms, where the form stops, and what changes when a sign-up counts places, sends a QR ticket and scans at the door.',
                'keywords' => 'Google Forms alternative for events, Google Forms replacement, event registration form, event signup form',
                'audience_hint' => 'For organizers whose sign-up is a Google Form, a linked Sheet and a mail merge the night before.',
                'about' => 'Google Forms is the form builder in Google Workspace: questions on a page, answers in a Responses tab and, if you link one, a Google Sheet. For events it is the default sign-up tool because everyone has filled one in and a form for a workshop or a potluck is ready in ten minutes. It collects answers. What happens to a place, a payment or a door list after Submit is yours to arrange.',
                'pricing_note' => 'Google Forms is free, but payments and tickets have to come from other tools. Event Schedule does free registration, confirmation emails and door check-in on its free plan; paid tickets, at zero platform fees, are on Pro at '.plan_price($this->planPrice()).'/month.',
                'competitor_price' => 'Free (but $50+/mo with add-ons)',
                'es_price' => 'From '.plan_price(0).'/mo (Pro '.plan_price($this->planPrice()).'/mo)',
                'comparison_rows' => [
                    ['feature' => 'Sign-up form', 'competitor' => true, 'es' => 'Register button on the event page'],
                    ['feature' => 'Stops at capacity', 'competitor' => 'Response limit, counts submissions', 'es' => 'Limit per date, counts places'],
                    ['feature' => 'Waitlist when full', 'competitor' => false, 'es' => 'Free on registration, Pro on tickets'],
                    ['feature' => 'Confirmation to the guest', 'competitor' => 'Copy of their answers', 'es' => 'Email with a QR code'],
                    ['feature' => 'Reminder before the event', 'competitor' => false, 'es' => 'On every plan; on by default on paid plans'],
                    ['feature' => 'Guest cancels and frees the place', 'competitor' => false, 'es' => true],
                    ['feature' => 'Taking payment', 'competitor' => 'A link pasted into the form', 'es' => 'On Pro, no platform fee'],
                    ['feature' => 'Check-in at the door', 'competitor' => false, 'es' => 'QR scan from a phone browser'],
                    ['feature' => 'Your own questions', 'competitor' => true, 'es' => 'On Pro'],
                    ['feature' => 'Answers in a spreadsheet', 'competitor' => 'Live linked Sheet', 'es' => 'CSV export on Pro'],
                ],
                'tool_strengths' => [
                    'Many question types, from short answer to file upload, and sections that show or hide depending on an answer',
                    'Responses land in a linked Google Sheet as they arrive, ready to sort, filter and share with a co-organizer',
                    'Guests have filled one in before, and need no Google account unless you limit the form to one response or ask for a file',
                    'A form can close itself on a date or after a set number of responses',
                ],
                'keep_when' => [
                    'what you are collecting is answers, not places: a volunteer skills survey, a menu vote, a call for speakers',
                    'the form needs many questions that branch, a file upload or a quiz score',
                    'the event is inside a school or company on Google Workspace, the form is limited to your own domain, and nobody needs a ticket',
                ],
                'pain_points' => [
                    'The response limit counts submissions. One answer that says "me plus three" uses one of your 40 responses and four of your 40 chairs.',
                    'Someone emails on Thursday to say they cannot come. The form stays closed at its limit, and the person who found it full on Tuesday never hears that a place opened.',
                    'The receipt Google sends is a copy of the answers, and Google warns that it may not arrive. Guests write to ask whether they are in.',
                    'Payment lives in another tab. You paste a payment link into the description, then match names in the payment app against rows in the Sheet.',
                    'At the door the list is a printed Sheet. Finding Katherine, who registered as Kate, holds up the line, and nothing stops the same row being ticked twice.',
                    'The form link is not the event. The date, the address, parking and the poster live in a message somewhere else, and the form looks the same with five places left as with fifty.',
                ],
                'gaps_lead' => 'Forms does what it says: it asks and it stores. These are the moments in an event\'s life when asking and storing is not enough.',
                'gaps_close' => 'For a sign-up where being a row in a Sheet is the whole point, none of this matters, and Forms is the right amount of tool.',
                'today' => [
                    'title' => 'An event on Google Forms, start to finish',
                    'intro' => 'This is the usual routine for a free workshop with 40 places and an optional paid lunch. Each step works. The effort is in the joins between them.',
                    'steps' => [
                        ['title' => 'Build the form', 'description' => 'Name, email, number of guests, dietary needs. You turn on Collect email addresses and choose Responder input so people without a Google account can sign up, then write a confirmation message that says "You are registered" before you know whether there will be room.'],
                        ['title' => 'Link the Sheet', 'description' => 'On the Responses tab you press Link to Sheets. Every submission becomes a row, and that Sheet is now your attendee list, your waitlist and your door list at once.'],
                        ['title' => 'Cap the places', 'description' => 'You set a response limit of 40 so the form closes itself. It counts submissions, not people, so you either drop the guests question or add up the column yourself and close the form early.'],
                        ['title' => 'Confirm and remind', 'description' => 'Send responders a copy of their response gives each person their answers back, if it clears their spam filter. The real confirmation, with the address and the time, is a mail merge from the Sheet. So is the reminder two days before.'],
                        ['title' => 'Take the money', 'description' => 'Forms has no payment question. The lunch fee is a payment link in the form description, and afterwards you compare the payment app\'s list with the Sheet and fill in a Paid column.'],
                        ['title' => 'Run the door', 'description' => 'You sort the Sheet by name, print it and tick people off. Cancellations that came by email are crossed out by hand, and you decide on the spot whether a walk-in can have the place.'],
                    ],
                    'outro' => 'That is four tools and three manual joins: form to Sheet, Sheet to mail merge, Sheet to payment list. It holds for one event of 40. It gets heavy at the second date, the second ticket price or the first full house.',
                ],
                'native_lead' => 'The same sign-up, where the form, the count, the confirmation and the door list are one record instead of four.',
                'es_solutions' => [
                    ['title' => 'A Register button that counts places', 'description' => 'Switch an event to Free registration and its page gets a Register button that asks for a name and an email, and a phone number if you want one. Set a Registration Limit and the page shows how many places are left. On a repeating event the limit applies to each date on its own. Free on every plan, with no ceiling on sign-ups.', 'icon' => 'clipboard', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'A waitlist and a way to back out', 'description' => 'A full date shows Join Waitlist. When a guest cancels from the link in their confirmation, the place returns to the count and the next person in line is emailed, with 24 hours to take it. Free on registration events; on priced tickets the waitlist is on Pro.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'A confirmation that is a ticket', 'description' => 'Each sign-up gets an email with a QR code and a ticket page of their own, carrying any notes you wrote: directions, parking, what to bring. A reminder follows before the event once reminders are on (the default on paid plans, one switch on Free), a day ahead unless you pick another time. There is no mail merge to run.', 'icon' => 'mail', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'Payment in the same step', 'description' => 'On Pro, put a price on a ticket type and guests pay as they book, through your own Stripe or PayPal account, a payment link or cash at the door, among others. Event Schedule adds no platform fee; the processor still charges its own. An order paid online through Stripe or PayPal is marked paid by the payment itself, so there are no two lists to match.', 'icon' => 'dollar', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'A door list that scans', 'description' => 'Open the Sales page on a phone, press Scan Ticket and point the camera at the code. Each registration admits once and a second scan warns you. Scanning is free and runs in the phone\'s browser, so it needs a connection. The live check-in dashboard with running counts is on Pro.', 'icon' => 'qr', 'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30', 'border' => 'border-cyan-200 dark:border-cyan-500/20', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                    ['title' => 'One link that is the whole event', 'description' => 'The event page carries the date, the map, the description, the image and the Register button together, with an Add to Calendar menu. It sits on your schedule at your own address beside your other events, so the next sign-up does not start from a blank form.', 'icon' => 'globe', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                ],
                'why_switch' => [
                    'intro' => 'You keep the ten-minute setup. What changes is that a sign-up becomes a place with a holder, a date and a QR code, so the count, the emails and the door all work from the same record.',
                    'points' => [
                        'The sign-up itself: name, email and an optional phone number on every plan, your own questions on Pro',
                        'The cap, counted in places per date and reopened when someone cancels',
                        'The confirmation and the reminder, sent without a mail merge',
                        'The payment step, on Pro, paid into your own account',
                        'The door list, as a scan on a phone instead of a printout',
                    ],
                ],
                'switch_lead' => 'Leave past forms where they are. Move the next event, and point the old link at it.',
                'switch_steps' => [
                    ['title' => 'Rebuild the next event, not the archive', 'description' => 'Create a schedule, add the event and choose Free registration on its Tickets tab. Set the Registration Limit to the number your form\'s response limit stood at. If the form asked extra questions, add them as custom fields on Pro, or keep to name, email and phone on the free plan.'],
                    ['title' => 'Bring across anyone already signed up', 'description' => 'If the form already has responses, the simplest route is to ask those people to register again from the new link. The attendee import, on Pro, takes a CSV of up to 5,000 rows and can email each person a confirmation, but it files people under a ticket type, so it needs the event set to Sell tickets with a free ticket type, not Free registration.'],
                    ['title' => 'Close the form with a forwarding note', 'description' => 'Turn off Accepting responses and put the event page address in the message people see on a closed form, so an old link in a chat still leads somewhere. Then share the event link wherever the form link used to go.'],
                ],
                'faq' => [
                    ['question' => 'Can Google Forms limit the number of registrations?', 'answer' => 'It can limit the number of responses: a form can close itself on a date or after a set number of submissions. What it cannot do is count places: a response for a party of four is one response, a cancellation does not reopen the form, and there is no waitlist. Event Schedule counts one place per registration and per date (with Individual tickets on, each named guest in a party counts), gives a cancelled place back and offers it to the next person waiting.'],
                    ['question' => 'Can I take payment through a Google Form?', 'answer' => 'Not inside the form: Forms has no payment question. Organizers paste a payment link into the form or its confirmation message and reconcile the two lists afterwards. In Event Schedule a priced ticket is paid during booking through your own Stripe, PayPal or other connected account, which needs Pro at '.plan_price($this->planPrice()).' a month. Event Schedule charges no platform fee, and the processor\'s own fee still applies.'],
                    ['question' => 'What does it cost to move event sign-ups off Google Forms?', 'answer' => 'Nothing, if the event is free to attend. Registration, the capacity limit, the waitlist on a full registration date, confirmation and reminder emails and door scanning are all on the free plan at '.plan_price(0).', with no limit on sign-ups. Pro at '.plan_price($this->planPrice()).' a month is for priced tickets, your own questions on the form, the sales export and the live check-in dashboard.'],
                    ['question' => 'Can I ask the same questions I ask in my form?', 'answer' => 'Name, email and an optional or required phone number are built in on every plan. Your own questions, as text, a dropdown or a yes-or-no, are custom fields and need Pro. There is no file upload question and no branching between sections, so a long application form is still better as a Google Form.'],
                    ['question' => 'Can I keep using Google Forms alongside Event Schedule?', 'answer' => 'The two sit together well. You can take the sign-up on the event page and still send a Google Form for the things that are not a booking: a volunteer rota, a speaker questionnaire, a catering order. Put the form link in the event description or the registration notes and it reaches everyone who signs up.'],
                    ['question' => 'What does Google Forms still do better?', 'answer' => 'Long and conditional forms. It has more question types, sections that depend on an answer, file uploads, quizzes, and a linked Sheet that updates live and can be shared with anyone. Event Schedule is not a form builder or a survey tool: it asks a few questions at sign-up and puts its effort into the count, the ticket and the door.'],
                    ['question' => 'How do I move people who already filled in my form?', 'answer' => 'Download the responses as a CSV from the Responses tab. On Pro you can import that list as attendees of an event that uses ticket types (a free ticket type is enough), up to 5,000 rows at a time. An event on Free registration has no ticket type to import into, so there people register again from the new link. Past events do not need moving: their Sheets stay in your Drive, and you start with the next date.'],
                ],
                'cross_links' => [
                    ['name' => 'SurveyMonkey', 'route' => 'marketing.replace_surveymonkey', 'description' => 'For the survey after the event, and sign-ups built as surveys.'],
                    ['name' => 'Google Sheets', 'route' => 'marketing.replace_google_sheets', 'description' => 'The Sheet your form feeds is doing an attendee list\'s job.'],
                    ['name' => 'Mailchimp', 'route' => 'marketing.replace_mailchimp', 'description' => 'Confirmations and reminders without a second contact list.'],
                ],
                'related_features' => [
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Priced ticket types paid into your own account, on Pro.'],
                    ['name' => 'Custom Fields', 'route' => 'marketing.custom_fields', 'description' => 'Your own questions on the sign-up form, on Pro.'],
                    ['name' => 'Check-in', 'route' => 'marketing.check_in', 'description' => 'Scan each QR code at the door from a phone browser, on every plan.'],
                ],
            ],
            'mailchimp' => [
                'name' => 'Mailchimp',
                'tagline' => 'New events email your subscribers by themselves and ticket buyers are already a list, within a monthly allowance worth reading before you move.',
                'meta_title' => 'Replace Mailchimp for Event Newsletters | Event Schedule',
                'description' => 'Where Mailchimp stops for event email, what moves to Event Schedule, and the monthly allowance that decides whether your list should stay.',
                'keywords' => 'Mailchimp alternative for events, Mailchimp replacement, event email marketing, event newsletter',
                'audience_hint' => 'For venues, promoters and performers who keep a Mailchimp audience mainly to tell people what is on.',
                'about' => 'Mailchimp is an email marketing service built around an audience of contacts, with tags, segments, signup forms, a campaign builder and automation flows. Event people use it for the monthly what\'s-on email, the on-sale announcement and the last-tickets push, while the events themselves live somewhere else: a ticketing site, a website calendar or a spreadsheet. It reaches ticketing tools through integrations, such as the one Eventbrite offers.',
                'pricing_note' => 'Mailchimp starts at $13/month for 500 contacts and goes up from there. Event Schedule does not cap subscribers on any plan; what the plan sets is the newsletter allowance, counted in recipients: 10 a month free, 100 on Pro at '.plan_price($this->planPrice()).'/month, 1,000 on Enterprise. The automatic digest of new events is outside it. Free registration is unlimited, and selling paid tickets is on Pro.',
                'competitor_price' => 'From $13/mo',
                'es_price' => 'From '.plan_price(0).'/mo (Pro '.plan_price($this->planPrice()).'/mo)',
                'comparison_rows' => [
                    ['feature' => 'Newsletter builder with templates', 'competitor' => true, 'es' => 'Free, five designs'],
                    ['feature' => 'What the plan measures', 'competitor' => 'Contacts in the audience', 'es' => 'Recipients emailed per month'],
                    ['feature' => 'Events listed in an email', 'competitor' => 'Copied into each campaign', 'es' => 'Read from the schedule at send time'],
                    ['feature' => 'Email when new events are published', 'competitor' => 'With an RSS automation', 'es' => 'Automatic digest, free'],
                    ['feature' => 'Ticket buyers as a list', 'competitor' => 'Through an integration or import', 'es' => 'Built-in segment'],
                    ['feature' => 'Sign-up form with double opt-in', 'competitor' => true, 'es' => true],
                    ['feature' => 'A/B testing', 'competitor' => true, 'es' => 'Subject or opening content'],
                    ['feature' => 'Automation flows and drip series', 'competitor' => true, 'es' => 'Not available'],
                    ['feature' => 'SMS marketing', 'competitor' => true, 'es' => 'Not available'],
                    ['feature' => 'Event pages, registration and tickets', 'competitor' => false, 'es' => 'Free RSVP, priced tickets on Pro'],
                ],
                'tool_strengths' => [
                    'Automation flows that send a series of emails after a signup, a tag or a date',
                    'Tags and segments over one audience, with whatever fields you add to it',
                    'A drag-and-drop builder and template library for mail that has nothing to do with events',
                    'Lists in the thousands, written to several times a month, on its own sending infrastructure',
                ],
                'keep_when' => [
                    'your list is over a thousand people, you write to all of them more than once a month, and you do not want to run your own mail server',
                    'you depend on automation flows: a welcome series, a win-back sequence, anything started by a tag or a date',
                    'most of what you send is not about events: a shop, a blog, a fundraising appeal, a membership',
                ],
                'pain_points' => [
                    'The date moves on the ticketing page on Tuesday. The campaign scheduled for Thursday still says Friday, because the email holds a copy of the event and not the event.',
                    'Every what\'s-on email opens with the same half hour: each event\'s title, date, price, image and ticket link copied into a content block from wherever it is really kept.',
                    'To write only to the people coming on Saturday, you export the order list, import it, tag it and build a segment, on the day you have the least time.',
                    'A buyer who never joined the list and a subscriber who never bought sit in two systems, and unless an integration carries them across you cannot tell which is which.',
                    'People who unsubscribed keep counting toward the contact limit until you archive them, so the audience on the bill is larger than the one you can write to.',
                    'The email can link to an RSVP page but cannot be one: the capacity, the guest list and the door are another tool again.',
                ],
                'gaps_lead' => 'Mailchimp sends mail about anything, so it knows nothing about your events in particular. These are the moments where that shows.',
                'gaps_close' => 'None of this is a defect in Mailchimp: it is an email service, and to it an event is text in a campaign like any other text.',
                'today' => [
                    'title' => 'A what\'s-on email in Mailchimp, start to finish',
                    'intro' => 'The monthly routine of a venue or promoter whose audience is in Mailchimp and whose events are somewhere else.',
                    'steps' => [
                        ['title' => 'Collect the addresses', 'description' => 'A Mailchimp signup form, embedded, hosted or as a pop-up, sits on your website and feeds one audience. Double opt-in is a setting you switch on for that audience. The names from the clipboard at the door are typed in or imported afterwards.'],
                        ['title' => 'Bring the buyers across', 'description' => 'Ticket buyers are in the ticketing tool. Either its integration copies over the ones who agreed to marketing at checkout, or you export the orders and use Import contacts: upload the file, match the columns, add a tag for the event.'],
                        ['title' => 'Build the campaign', 'description' => 'Create an email, pick the template, then add a block per event: image, title, date, a button to the ticket page. Each one is copied by hand from the ticketing site or the website calendar.'],
                        ['title' => 'Choose who gets it', 'description' => 'Send to the whole audience, or build a segment from tags: last season\'s buyers, the jazz crowd, the people who came to the festival.'],
                        ['title' => 'Test, schedule, send', 'description' => 'Send yourself a test, fix the button that still points at last month\'s show, then schedule it. Where your plan has A/B testing, two subject lines go to part of the audience first.'],
                        ['title' => 'After it goes', 'description' => 'Opens and clicks arrive in the report. If an event then moves or sells out, the sent email stays as it was, and the correction is another campaign.'],
                    ],
                    'outro' => 'It works, and a great many rooms have been filled this way. The cost is that every event exists twice, once where it is sold and once as text in an email, and you are what keeps the two in step.',
                ],
                'native_lead' => 'The same email, written in the place where the events, the buyers and the sign-up form already are.',
                'es_solutions' => [
                    ['title' => 'New events announce themselves', 'description' => 'Publish public events and your confirmed subscribers get one digest listing them, at most one every 72 hours. It is free on every plan and does not come out of the newsletter allowance. You can turn it off per schedule.', 'icon' => 'mail', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'An Events block that fills itself', 'description' => 'The Events block lists your events with day, time, price and link, worked out when the newsletter is sent and not when you wrote it. Set a newsletter to repeat on the first of each month, with the Events block set to the rest of that month, and it carries the month\'s programme without being rebuilt.', 'icon' => 'calendar', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Buyers are already a segment', 'description' => 'Ticket buyers is a built-in segment, for every event or for one. Waitlist, sub-schedule, followers and email subscribers are segments too, so writing to Saturday\'s crowd involves no export.', 'icon' => 'ticket', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'The allowance, stated plainly', 'description' => 'A newsletter counts every recipient: 10 a month on Free, 100 on Pro, 1,000 on Enterprise, and a send that would go over is refused whole. Connect your own mail server on any plan and the cap comes off, because the mail then leaves through your provider.', 'icon' => 'dollar', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'Sign-up where the events are', 'description' => 'A sign-up panel on your schedule and event pages, the same form embedded on your own website, a tick box at checkout and a QR code for the door. The panel and the embedded form confirm by email before anyone is added.', 'icon' => 'globe', 'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30', 'border' => 'border-cyan-200 dark:border-cyan-500/20', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                    ['title' => 'A/B tests and who opened', 'description' => 'Try two subject lines, or two openings, on 5 to 50 percent of the list; after the wait you set, the better one goes to everyone else. Each newsletter then shows opens, clicks, failures and unsubscribes. All of it is on the free plan.', 'icon' => 'chart', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                ],
                'why_switch' => [
                    'intro' => 'Move the event mail if your list fits the allowance, or if you will send through your own mail server. If neither is true, keep Mailchimp for the campaigns and let the schedule do the listing, the sign-ups and the tickets.',
                    'points' => [
                        'Your subscribed contacts, imported up to 10,000 addresses at a time into a segment',
                        'The monthly what\'s-on email, as a repeating newsletter with an Events block',
                        'The "new dates announced" campaign, as the automatic digest',
                        'Buyer follow-ups, through the Ticket buyers segment',
                        'The signup form on your website, as the embedded sign-up form',
                    ],
                ],
                'switch_lead' => 'Count your list against the allowance first, then move the audience, then the form.',
                'switch_steps' => [
                    ['title' => 'Export the audience from Mailchimp', 'description' => 'In Audience, choose Export audience, then Export CSV. The download holds separate files for subscribed, unsubscribed and cleaned contacts. Bring across the subscribed file only, so nobody who opted out hears from you again.'],
                    ['title' => 'Import it and swap the form', 'description' => 'On the Import Emails tab of Newsletters, upload the CSV, map the email and name columns and save it as a segment. Then take the Mailchimp form off your website and paste in the code from Embed Signup Form on your Followers tab.'],
                    ['title' => 'Send the next what\'s-on email from here', 'description' => 'Build it with an Events block and send yourself a test. Review and send shows how many people it reaches and what is left of the month\'s allowance before anything goes. Say in that email that event news now comes from this address.'],
                ],
                'faq' => [
                    ['question' => 'Is Event Schedule a Mailchimp alternative for event emails?', 'answer' => 'For telling a small or mid-sized list what is on, it does the job with less copying: events are pulled into the email, buyers are a ready segment, and new events reach subscribers as an automatic digest. It is not a general email marketing tool. There are no drip sequences, no tags and no SMS, and the monthly allowance is small unless you connect your own mail server.'],
                    ['question' => 'How many emails can I send a month, and what does it cost?', 'answer' => 'The allowance counts recipients, not newsletters: 10 a month on the free plan at '.plan_price(0).', 100 on Pro at '.plan_price($this->planPrice()).' a month and 1,000 on Enterprise at '.plan_price($this->planPrice(true)).' a month. One newsletter to 300 people uses 300, and a send that would pass the limit is refused whole, never delivered in part. The automatic digest of new events is outside the count. A schedule that sends through its own mail server has no cap on any plan, and neither does a selfhosted install.'],
                    ['question' => 'My list has 5,000 people. Should I switch?', 'answer' => 'Not for the newsletters, unless you connect your own mail server. The largest hosted allowance is 1,000 newsletter emails a month, each recipient counting as one, so one newsletter to 5,000 people cannot go out on it. With your own SMTP provider entered in the schedule\'s Email Settings the cap is lifted, and deliverability becomes yours to look after. If that does not appeal, keep Mailchimp for campaigns and use the schedule for event pages, sign-ups and tickets.'],
                    ['question' => 'Can I keep using Mailchimp alongside Event Schedule?', 'answer' => 'You can, and for a large list it is the sensible arrangement. There is no built-in Mailchimp sync. Every schedule publishes an RSS feed of its upcoming events, and Mailchimp\'s RSS automation sends from a feed on the schedule you set. On Pro, the Sales page exports orders as a CSV that you can import into your Mailchimp audience.'],
                    ['question' => 'What does Mailchimp still do better?', 'answer' => 'Automation, list structure and design freedom. Mailchimp has automation flows, tags and custom fields, SMS, and a far larger template library. The newsletter builder here has five designs, a short list of email-safe fonts and no free-form HTML, and a newsletter goes out when you send or schedule it, weekly or monthly repeats included.'],
                    ['question' => 'How do I move my Mailchimp subscribers across?', 'answer' => 'Export the audience from Mailchimp as a CSV and upload the subscribed file on the Import Emails tab, up to 10,000 addresses at a time on any plan. They land in a segment you can send newsletters to; no account is created for anyone and nobody is added to your followers. Imported addresses do not receive the automatic digest, which goes only to people who signed up on your pages or ticked the box at checkout.'],
                    ['question' => 'Is signing up double opt-in, as it can be in Mailchimp?', 'answer' => 'On the sign-up panel and the embedded form it always is: a visitor gives a name and an email address and joins only after opening the confirmation link. The tick box at checkout is single opt-in, because the ticket is already being sent to that address. Someone still awaiting confirmation is never emailed.'],
                ],
                'cross_links' => [
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms', 'description' => 'Registration with a capacity limit, without a form and a response sheet.'],
                    ['name' => 'Canva', 'route' => 'marketing.replace_canva', 'description' => 'The weekly line-up image, made from the flyers you already have.'],
                    ['name' => 'Linktree', 'route' => 'marketing.replace_linktree', 'description' => 'A link in bio that lists your dates by itself.'],
                ],
                'related_features' => [
                    ['name' => 'Newsletters', 'route' => 'marketing.newsletters', 'description' => 'The builder, the allowance and the automatic digest in detail.'],
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Free registration, and priced tickets on Pro with no platform fee.'],
                    ['name' => 'Event Graphics', 'route' => 'marketing.event_graphics', 'description' => 'One image and caption of your upcoming events, for the social post.'],
                ],
            ],
            'canva' => [
                'name' => 'Canva',
                'tagline' => 'Design the poster once in Canva, then let the line-up image, the sizes and the caption be generated from your events.',
                'meta_title' => 'Replace Canva for Event Flyers and Graphics | Event Schedule',
                'description' => 'How event graphics get made in Canva, which of them Event Schedule generates from your flyers for free, and why the poster itself stays in Canva.',
                'keywords' => 'Canva alternative for events, Canva replacement, event flyer maker, event graphic generator',
                'audience_hint' => 'For venues, promoters and bands who rebuild the same what\'s-on graphic in Canva every week.',
                'about' => 'Canva is a browser-based design tool with templates for posters, flyers and social posts. Event people use it to make a poster for each show, resize it for the feed and the story, and build a weekly or monthly line-up image by typing the dates into a saved design. It also exports print-ready files, holds brand assets and can schedule posts to social accounts.',
                'pricing_note' => 'Canva Pro costs $15/month for premium design features. Event Schedule generates event graphics automatically on every plan, the free one included; Pro at '.plan_price($this->planPrice()).'/month adds paid ticket sales and raises the newsletter allowance, and AI flyers are on Enterprise.',
                'competitor_price' => 'From $15/mo (Pro)',
                'es_price' => 'From '.plan_price(0).'/mo (Pro '.plan_price($this->planPrice()).'/mo)',
                'comparison_rows' => [
                    ['feature' => 'Design a poster from a template or a blank page', 'competitor' => true, 'es' => 'Not a design tool'],
                    ['feature' => 'A flyer drawn by AI from the event details', 'competitor' => 'AI design tools', 'es' => 'On Enterprise'],
                    ['feature' => 'One image of all upcoming events', 'competitor' => 'Built by hand', 'es' => 'Free, from your flyers'],
                    ['feature' => 'Date on the image follows the event', 'competitor' => false, 'es' => 'Optional date strip'],
                    ['feature' => 'Caption text for the post', 'competitor' => 'Written per post', 'es' => 'Filled from the events'],
                    ['feature' => 'Feed, story and link-preview sizes', 'competitor' => 'Resize tool', 'es' => 'Four fixed shapes'],
                    ['feature' => 'QR code to the event page', 'competitor' => 'QR code app', 'es' => 'On each flyer in the image'],
                    ['feature' => 'Print-ready PDF with bleed and crop marks', 'competitor' => true, 'es' => 'PNG only'],
                    ['feature' => 'Schedule posts to social accounts', 'competitor' => 'Content Planner', 'es' => 'Not available'],
                    ['feature' => 'Ticket sales and check-in', 'competitor' => false, 'es' => 'Free RSVP, priced tickets on Pro'],
                ],
                'tool_strengths' => [
                    'A template for nearly every format, from a gig poster to a story, that someone who is not a designer can finish',
                    'Full control of the layout: the type, the photo, the colour and where each thing sits',
                    'Print output, including PDF Print with crop marks and bleed',
                    'Brand Kits that keep your logos, colours and fonts in one place',
                ],
                'keep_when' => [
                    'each show needs its own designed poster: nothing here designs one, short of the AI flyer on Enterprise',
                    'you print posters, table cards or banners, since the image made here is a PNG for screens',
                    'the graphics are your brand work, with a look you control down to the letter spacing',
                ],
                'pain_points' => [
                    'The headliner moves from Friday to Saturday. The poster, the square post, the story and the weekly line-up each hold the old date as typed text, and each is a separate file to open, correct, download and post again.',
                    'Every Monday the line-up image is rebuilt from the calendar: seven names and seven times typed into last week\'s design, and the show you left out gets noticed on Wednesday.',
                    'A static QR code on the poster opens the link you pasted the day you made it. When the ticket page changes, the posters already on the wall keep sending people to the old one.',
                    'The caption is written apart from the picture, so sooner or later the post says 8pm under an image that says 7.30.',
                    'The finished image cannot say Sold Out, take a booking or count who is coming. It is a picture of the details, and the details live somewhere else.',
                ],
                'gaps_lead' => 'Canva is where the poster gets made, and it does that well. The gaps open after the poster exists, when the same details have to be repeated across sizes, weeks and changes.',
                'gaps_close' => 'Canva is a design tool doing design work; keeping a file in step with an event was never its job.',
                'today' => [
                    'title' => 'A show\'s graphics in Canva, start to finish',
                    'intro' => 'One gig at a venue that posts a poster for each night and a line-up for each week.',
                    'steps' => [
                        ['title' => 'Start from a template', 'description' => 'Search the templates for a concert poster or event flyer, pick one, swap in the act\'s photo and type the name, date, door time, venue and price over the placeholder text.'],
                        ['title' => 'Make it yours', 'description' => 'Apply the Brand Kit if your plan has one, or set the venue\'s colours and fonts by hand and drop in the logo from your uploads.'],
                        ['title' => 'Resize for each place', 'description' => 'The poster becomes a square post, a story and a print size. Canva\'s Resize makes the copies, and from then on each copy is a design of its own to check and keep up to date.'],
                        ['title' => 'Add the way in', 'description' => 'Paste the ticket link into a QR code from Canva\'s QR code app and place it in a corner. The same link goes into the caption, which you write in the social app.'],
                        ['title' => 'Download and post', 'description' => 'Share, then Download: PNG for social, PDF Print for the printer. Upload each file to each account yourself, or schedule it in Content Planner on the plans that include it, one platform per design.'],
                        ['title' => 'Then the weekly line-up', 'description' => 'Open last week\'s line-up design, delete seven rows of text and type the next seven from the calendar. When a date changes midweek, every file above is opened again.'],
                    ],
                    'outro' => 'The first step is design, and Canva is the right place for it. The other five are the same facts retyped, and every retyping is a chance for the image and the event to disagree.',
                ],
                'native_lead' => 'Keep the designing where it is. The repeating can come from the events themselves.',
                'es_solutions' => [
                    ['title' => 'The line-up image, generated', 'description' => 'Events graphic lays the flyers of your upcoming events into one PNG, up to 20 of them, as a grid, as rows or as a list with names and times. It is free on every plan and is drawn fresh from the schedule each time you open it.', 'icon' => 'image', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Dates drawn from the event', 'description' => 'In the Grid and Rows layouts, switch on the date strip and each flyer carries its date from the event record, in your own wording if you set one; the List layout writes each date beside the name. Move the event, generate again, and the picture is right without opening a design.', 'icon' => 'calendar', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Four shapes, nothing to re-flow', 'description' => 'Square, Portrait, Story and Wide are fixed sizes the image is fitted into and padded with your schedule\'s background, so no flyer is cropped. Natural lets the image grow with the number of events.', 'icon' => 'layout', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'A caption that matches the picture', 'description' => 'Beside the image is a caption built from a template you control: day, time, name, venue and link by default, with price and other details as variables you can add, once per event. It is read from the same events as the image, so the two cannot give different times.', 'icon' => 'clipboard', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'A QR code that leads to a live page', 'description' => 'Every flyer in the image carries a QR code to that event\'s own page, where the time and place are whatever they are today. Registration there is free on every plan; a ticket with a price is on Pro.', 'icon' => 'qr', 'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30', 'border' => 'border-cyan-200 dark:border-cyan-500/20', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                    ['title' => 'A flyer when you have none', 'description' => 'On Enterprise, the AI Generator on the event form draws a flyer from the event\'s details and a style you describe. On Free and Pro you upload the one you made in Canva, a JPG or PNG under 2.5 MB.', 'icon' => 'ai', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                ],
                'why_switch' => [
                    'intro' => 'Keep designing posters in Canva. Move the graphics that are only the same details arranged again: the line-up, its sizes, its caption and its link.',
                    'points' => [
                        'The weekly or monthly line-up image, composed from the flyers on your events',
                        'The caption under it, written from the event data',
                        'The feed, story and link-preview versions of that line-up',
                        'The QR code, which now opens a page that stays current',
                        'Your finished posters themselves, uploaded once to each event',
                    ],
                ],
                'switch_lead' => 'Nothing leaves Canva except the posters you have already made.',
                'switch_steps' => [
                    ['title' => 'Download your current posters from Canva', 'description' => 'For each upcoming show, download the poster as a PNG or JPG. Leave the designs where they are: you will open Canva again for the next poster.'],
                    ['title' => 'Turn each poster into an event', 'description' => 'On the import page, drop in a poster and its name, date, venue and price are read into an event you check before adding. Make sure each event has its flyer attached, because only events with a flyer are drawn in the line-up image.'],
                    ['title' => 'Retire the line-up design', 'description' => 'Open Events graphic from the schedule\'s Actions menu, pick a layout and a shape, then download the image and copy the caption. Next week it is the same: open, download, copy. The saved line-up design in Canva can be archived.'],
                ],
                'faq' => [
                    ['question' => 'Can Event Schedule replace Canva for event flyers?', 'answer' => 'Partly, and it depends which graphic you mean. It does not design a poster: there is no canvas, no templates and no text tools. What it replaces is everything made by arranging existing details again, which for most venues is the line-up image, its social sizes and the caption. The one exception is the AI flyer on Enterprise.'],
                    ['question' => 'Does it make a flyer for a single event?', 'answer' => 'On Enterprise it can: the AI Generator on the event form draws one from the event\'s details, following a style you describe. On Free and Pro it does not. You upload a flyer you already have, and the event page, the link preview and the line-up image all use that file.'],
                    ['question' => 'Is the events graphic free, and what do the paid plans add?', 'answer' => 'Generating, downloading and copying the image and its caption costs '.plan_price(0).' on every plan, and so does uploading a flyer to an event. Enterprise at '.plan_price($this->planPrice(true)).' a month adds the AI flyer, an AI rewrite of the caption and the image emailed on a schedule to addresses you list. Pro at '.plan_price($this->planPrice()).' a month is the ticket-selling plan and adds nothing here. On eventschedule.com every image carries a small eventschedule.com credit in one corner, on every plan.'],
                    ['question' => 'Can I keep using Canva alongside Event Schedule?', 'answer' => 'That is the expected arrangement. Canva makes the poster, the poster is uploaded to the event, and from there the line-up image, the caption and the event page take over. Nothing here opens or edits a Canva design.'],
                    ['question' => 'What does Canva still do better?', 'answer' => 'Design, print and posting. Canva gives you templates, free placement of every element and a print-ready PDF, and its Content Planner can schedule a post to a social account. Here the layouts are three fixed arrangements, the text on the image is set in built-in fonts, the output is a PNG, and nothing is posted for you: you download the image and paste the caption.'],
                    ['question' => 'What happens to the image when an event changes?', 'answer' => 'The next image you generate is right, because nothing is stored as a finished picture. A file you downloaded and posted yesterday is still yesterday\'s file. The event page behind the QR code and the link shows the current time and place straight away.'],
                    ['question' => 'How do I bring my existing Canva designs across?', 'answer' => 'Download each one from Canva as a PNG or JPG and upload it as the event\'s flyer, which takes a file under 2.5 MB. The import page can also read a poster image and fill in the event from it. Your Canva fonts and Brand Kit do not carry over, except as they appear inside the flyer images themselves.'],
                ],
                'cross_links' => [
                    ['name' => 'QR Code Generators', 'route' => 'marketing.replace_qr_code_generators', 'description' => 'A code on every ticket, scanned at the door in a phone browser.'],
                    ['name' => 'Linktree', 'route' => 'marketing.replace_linktree', 'description' => 'Somewhere for the QR code on the poster to lead.'],
                    ['name' => 'Squarespace', 'route' => 'marketing.replace_squarespace', 'description' => 'A calendar to embed in the site you already have.'],
                ],
                'related_features' => [
                    ['name' => 'Event Graphics', 'route' => 'marketing.event_graphics', 'description' => 'Layouts, shapes, the date strip and the caption template in full.'],
                    ['name' => 'AI Features', 'route' => 'marketing.ai', 'description' => 'Reading a poster into an event on every plan, and drawing one on Enterprise.'],
                    ['name' => 'Newsletters', 'route' => 'marketing.newsletters', 'description' => 'An Events block that shows each event with its flyer.'],
                ],
            ],
            'linktree' => [
                'name' => 'Linktree',
                'tagline' => 'Put your calendar behind the link in your bio, so the next show is listed without a link being added or taken down.',
                'meta_title' => 'Replace Linktree for Your Event Link in Bio | Event Schedule',
                'description' => 'What a Linktree does for events, where it leaves you adding and removing links by hand, and when a schedule page is the better link in bio.',
                'keywords' => 'Linktree alternative for events, Linktree replacement, event link in bio, event landing page',
                'audience_hint' => 'For DJs, bands, venues and promoters whose bio link mostly exists to say where the next date is.',
                'about' => 'Linktree is a link-in-bio page: one address that opens a stack of titled buttons, each leading somewhere else. Performers and venues use it to hold a ticket link per show next to their music, merch and socials, and a touring act can add a Tour and Events link that shows upcoming dates from a Seated or Bandsintown profile. It also collects sign-ups, counts clicks and sells downloads and courses.',
                'pricing_note' => 'Linktree Pro costs $9/month for analytics and customization on what is still a list of links. On Event Schedule the schedule page, subscriber sign-ups, newsletters, link click counts and unlimited free registration are all on the free plan; selling paid tickets is on Pro at '.plan_price($this->planPrice()).'/month.',
                'competitor_price' => 'From $9/mo (Pro)',
                'es_price' => 'From '.plan_price(0).'/mo (Pro '.plan_price($this->planPrice()).'/mo)',
                'comparison_rows' => [
                    ['feature' => 'What the bio link opens', 'competitor' => 'A stack of links', 'es' => 'Your events, by date'],
                    ['feature' => 'Upcoming dates on the page', 'competitor' => 'From Seated or Bandsintown', 'es' => 'From the schedule itself'],
                    ['feature' => 'A page for each event', 'competitor' => 'Opens another site', 'es' => true],
                    ['feature' => 'Month calendar and add to calendar', 'competitor' => false, 'es' => true],
                    ['feature' => 'Where the ticket is bought', 'competitor' => 'On the ticketing site', 'es' => 'On your page, on Pro'],
                    ['feature' => 'Email sign-ups', 'competitor' => true, 'es' => 'Confirmed by email'],
                    ['feature' => 'Click counts per link', 'competitor' => true, 'es' => 'Free, with a short address'],
                    ['feature' => 'Titled buttons for any link', 'competitor' => true, 'es' => 'Icons only'],
                    ['feature' => 'Timed links that appear and disappear', 'competitor' => true, 'es' => 'Not available'],
                    ['feature' => 'Selling downloads and courses', 'competitor' => true, 'es' => 'Not available'],
                ],
                'tool_strengths' => [
                    'One page for every kind of link: a release, a merch store, a video, a booking page',
                    'Titled buttons you reorder by dragging, and can set to go live and disappear on a date',
                    'A Tour and Events link that previews upcoming shows from a Seated or Bandsintown profile',
                    'Downloads and courses sold from the page, and click counts for every link',
                ],
                'keep_when' => [
                    'most of your links are not dates: releases, merch, a shop, a podcast, affiliate links',
                    'you tour, your dates already live on Bandsintown or Seated, and the Tour and Events link shows them well enough',
                    'you sell downloads or courses from the page itself',
                ],
                'pain_points' => [
                    'Saturday\'s show is over, and on Tuesday its ticket link is still the first thing in your bio, because taking it down was a job for Sunday morning.',
                    'A link title holds a name and a date. The door time, the address, the support act and the price are on the far side of a tap, on a site with someone else\'s header.',
                    'Six dates next month means six links to add, title, put in order and later remove, each pasted from the ticketing site.',
                    'A visitor arriving from your bio wants to see what is on this weekend. A stack of links is ordered by what you dragged to the top, not by date.',
                    'The click count says forty people tapped the ticket link. Whether any of them bought is in another dashboard.',
                ],
                'gaps_lead' => 'A Linktree points at things. That is its strength for a release or a shop and its limit for a calendar, because an event is more than a destination.',
                'gaps_close' => 'For links, Linktree is the right shape; these gaps only appear once the thing behind the link has a date.',
                'today' => [
                    'title' => 'A run of shows on Linktree, start to finish',
                    'intro' => 'A performer or small venue with a handful of dates coming up and one link in the Instagram bio.',
                    'steps' => [
                        ['title' => 'One link per show', 'description' => 'Add a link, title it with the date and the room, paste the ticket page address and drag it to the top. Repeat for the next date, and the one after.'],
                        ['title' => 'Time it, or remember it', 'description' => 'Where your plan has Schedule, set when the link goes live and when it disappears. Otherwise the link comes down when you remember to take it down.'],
                        ['title' => 'If you tour, connect a tour page', 'description' => 'A Tour and Events link takes your Seated or Bandsintown artist address and shows a preview of your next shows. Fans go through to that tour page to buy.'],
                        ['title' => 'Everything else is one tap away', 'description' => 'The button says what and when. The line-up, the door time, the map and the price are on the ticketing site, which may be a different site for each promoter you play for.'],
                        ['title' => 'Collect the addresses', 'description' => 'A contact form on the Linktree gathers emails into its Audience page. From there you export a CSV, or sync the list to an email tool when there is something to announce.'],
                        ['title' => 'Read the clicks', 'description' => 'Analytics show views and the clicks on each link, and where the traffic came from. What happened after the click is the ticketing site\'s to report.'],
                    ],
                    'outro' => 'A Linktree is a good front door. For anyone with more than one date it is also a list tended by hand, where each show is added, titled, ordered and removed, and what a fan wants to know sits one tap further on.',
                ],
                'native_lead' => 'Put the calendar itself behind the bio link, and the list tends itself.',
                'es_solutions' => [
                    ['title' => 'The bio link is the calendar', 'description' => 'Your schedule address opens your events in date order, as a list or a month, with search and filters. Every event also has its own page and link, for the posts about that one night.', 'icon' => 'globe', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'One entry per event, not one link', 'description' => 'Add an event once. Change its time later and the page, the calendar feed and the embed on your website follow. A weekly residency is a single recurring event, with no link to renew each week.', 'icon' => 'calendar', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'The page answers the tap', 'description' => 'Time, place, description and flyer are on the event page. Free registration with a capacity limit is on every plan. A ticket with a price is on Pro, paid into your own Stripe or PayPal account with no platform fee; the processor\'s own fee still applies.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Your other links, as short addresses', 'description' => 'Instagram, Spotify, a merch store or any other site go on the schedule as links, shown as icons beside your name. Each gets a short address on your own URL, such as /instagram, and its clicks are counted, free.', 'icon' => 'link', 'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30', 'border' => 'border-cyan-200 dark:border-cyan-500/20', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                    ['title' => 'Fans who hear about the next date', 'description' => 'Visitors leave a name and an email on the page and confirm it. When you publish new events they get one digest, at most every 72 hours, without you writing anything. Newsletters you write yourself count recipients: 10 a month on Free, 100 on Pro, 1,000 on Enterprise.', 'icon' => 'mail', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'Your look, and what Free carries', 'description' => 'Profile image, header, background, accent colour and font are free. A free page shows Event Schedule branding, which Pro removes. Your own domain is on Enterprise.', 'icon' => 'image', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                ],
                'why_switch' => [
                    'intro' => 'Switch the bio link if dates are the main thing you promote. If they are one thing among many, keep Linktree and make your schedule its first button.',
                    'points' => [
                        'Your show links, as events with pages of their own',
                        'Instagram, Spotify and the rest, as icons with short addresses and click counts',
                        'Your YouTube videos, in a panel on the page',
                        'Your sign-ups, imported up to 10,000 addresses at a time',
                        'The QR code on your posters, as one that opens the schedule with the sign-up form ready',
                    ],
                ],
                'switch_lead' => 'Start with the dates, then the links, then the bio.',
                'switch_steps' => [
                    ['title' => 'Turn each show link into an event', 'description' => 'For every date on your Linktree, paste the ticket page address, the announcement text or the flyer on the import page, and check the event it reads back before adding it. Choose Tickets elsewhere to keep the link to the site that already sells the show, or take registration on your own page.'],
                    ['title' => 'Bring the other links and the sign-ups', 'description' => 'In the schedule\'s Page content, use Add Link for each profile and site. Export your sign-ups from Linktree\'s Audience page as a CSV and upload it on the Import Emails tab.'],
                    ['title' => 'Change the address in your bio', 'description' => 'Replace the Linktree address with your schedule address on Instagram, TikTok and anywhere else it appears. If you are keeping Linktree for its other links, add the schedule as the top button and delete the per-show links.'],
                ],
                'faq' => [
                    ['question' => 'Can I use Event Schedule as my link in bio?', 'answer' => 'The schedule address is an ordinary web link, so it goes in any bio field that takes one. It opens your upcoming events with their dates, each leading to its own page. What it does not give you is a stack of titled buttons: links to other places appear as icons beside your name.'],
                    ['question' => 'What happens to my other links, like Instagram or my merch store?', 'answer' => 'Add them to the schedule and each one is shown as an icon and given a short address on your own URL: /instagram for a platform the app recognises, or the site\'s brand name for anything else. Clicks on those addresses are counted in Analytics on every plan, with your own clicks left out. There is no title, thumbnail or timer on a link, so a page that is mostly links reads better on Linktree.'],
                    ['question' => 'Can I keep Linktree and use both?', 'answer' => 'If dates are only part of what you link to, that is the better arrangement. Make the schedule the first button on your Linktree, titled something like All dates and tickets, and stop adding a link per show. Linktree keeps the releases, the merch and the rest; the schedule keeps the calendar.'],
                    ['question' => 'What does Linktree still do better?', 'answer' => 'Anything that is a link and not a date. It gives every link a titled button, lets you time links to appear and disappear, sells downloads and courses, and for touring acts shows dates from a Seated or Bandsintown profile. A schedule page is built around events and treats other links as a row of icons.'],
                    ['question' => 'Is it free, and what does a free page look like?', 'answer' => 'The schedule page, unlimited events, free registration, email sign-ups, short links and analytics cost '.plan_price(0).'. A free page shows Event Schedule branding. Pro at '.plan_price($this->planPrice()).' a month removes the branding and adds tickets with a price; your own domain is on Enterprise at '.plan_price($this->planPrice(true)).' a month.'],
                    ['question' => 'How do I move my existing dates and sign-ups across?', 'answer' => 'Dates come in through the import page, which reads a link, pasted text or a flyer image into events you review first; it is free within a daily allowance. Sign-ups come out of Linktree as a CSV and go in on the Import Emails tab, up to 10,000 addresses at a time. Imported addresses can be sent newsletters, but only people who sign up on your own page get the automatic digest of new events.'],
                    ['question' => 'Can visitors buy tickets without leaving my page?', 'answer' => 'On Pro they can: the event page takes the order and the money goes to your own Stripe, PayPal or other connected account, with no platform fee on top of the processor\'s. On the free plan the page takes free registrations with a capacity limit, or shows a button to the ticketing site you already use.'],
                ],
                'cross_links' => [
                    ['name' => 'Squarespace', 'route' => 'marketing.replace_squarespace', 'description' => 'When the bio link was standing in for a website.'],
                    ['name' => 'Canva', 'route' => 'marketing.replace_canva', 'description' => 'The line-up post that sends people to the link.'],
                    ['name' => 'Mailchimp', 'route' => 'marketing.replace_mailchimp', 'description' => 'Where the sign-ups from your bio page go next.'],
                ],
                'related_features' => [
                    ['name' => 'Analytics', 'route' => 'marketing.analytics', 'description' => 'Page views, where visitors came from and clicks on each of your links.'],
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Registration and tickets on the page the bio link opens.'],
                    ['name' => 'Newsletters', 'route' => 'marketing.newsletters', 'description' => 'The digest of new events, and what the monthly allowance covers.'],
                ],
            ],
            'google-sheets' => [
                'name' => 'Google Sheets',
                'tagline' => 'The guest list stops being a sheet you keep up by hand: each registration, payment and door scan writes its own row.',
                'meta_title' => 'Replace Google Sheets for Event Guest Lists | Event Schedule',
                'description' => 'How an event really runs from a Google Sheets guest list, where the sheet lets you down at the door, and what writes the list for you.',
                'keywords' => 'Google Sheets alternative for events, Google Sheets replacement, event tracking spreadsheet, Excel alternative for events',
                'audience_hint' => 'For organizers whose sign-ups land in a response sheet and whose door list is a printout of it.',
                'about' => 'Google Sheets is the spreadsheet in Google Workspace, and for many small events it is the whole back office: one tab of dates and venues, and one tab of names that a Google Form fills a row at a time. It is shared with a link, edited by several people at once, and bent to any shape with a formula. What it holds is a record of the event, so the page people sign up on, the money and the door all live somewhere else.',
                'pricing_note' => 'Google Sheets is free, but tracking events by hand costs time. Event Schedule records sales, attendees and refunds as they happen. Free registration is unlimited, and selling paid tickets is on the Pro plan at '.plan_price($this->planPrice()).'/month, which also adds the sales export.',
                'competitor_price' => 'Free (manual effort)',
                'es_price' => 'From '.plan_price(0).'/mo (Pro '.plan_price($this->planPrice()).'/mo)',
                'comparison_rows' => [
                    ['feature' => 'A page the public signs up on', 'competitor' => 'Needs a form', 'es' => true],
                    ['feature' => 'Guest list that fills itself', 'competitor' => 'From a linked form', 'es' => 'From registration and checkout'],
                    ['feature' => 'Free registration capped at a number', 'competitor' => false, 'es' => 'Yes, with a waitlist'],
                    ['feature' => 'Taking payment', 'competitor' => false, 'es' => 'On Pro, zero platform fees'],
                    ['feature' => 'Confirmation with a QR code', 'competitor' => 'Mail merge, no code', 'es' => true],
                    ['feature' => 'Reminder before the event', 'competitor' => 'By hand', 'es' => 'Sent for you'],
                    ['feature' => 'Marking arrivals at the door', 'competitor' => 'Checkbox column', 'es' => 'QR scan in a phone browser'],
                    ['feature' => 'Several people in the list at once', 'competitor' => true, 'es' => 'On Enterprise, up to 5'],
                    ['feature' => 'Your own columns and formulas', 'competitor' => true, 'es' => 'Custom fields and CSV export on Pro'],
                    ['feature' => 'Public calendar of every event', 'competitor' => 'Publish to the web', 'es' => true],
                ],
                'tool_strengths' => [
                    'Any column you can think of, added in a second, with no field to configure first',
                    'Several people in one list at the same time, each with a filter view of their own that leaves everyone else\'s screen alone',
                    'Formulas for the questions nobody planned for: a COUNTIF of vegetarian meals, a sum of what each table owes',
                    'Version history and a per-cell edit history, so a deleted guest can be traced and put back',
                ],
                'keep_when' => [
                    'the list is private and has no public side: a wedding seating chart, a budget, a run of show',
                    'guests are invited by name and nobody signs up, so a registration page has nothing to do',
                    'the job is working the numbers afterwards. On Pro, export the sales from Event Schedule and do that part in a sheet',
                ],
                'pain_points' => [
                    'A helper at the second door filters the list to L through Z, and the first door\'s list changes with it, because an ordinary filter belongs to the whole sheet and not to the person who set it.',
                    'The sheet counts rows and cannot refuse one. Whatever feeds it keeps adding names after the room is full, and you learn about it when you scroll down.',
                    'Someone pays by bank transfer and the Paid box gets ticked later, from memory. On the night three people say they paid and the column says they did not.',
                    'The same person signs up twice under two spellings, and the head count you gave the caterer is high by however many of those there are.',
                    'A guest cancels by email. You delete the row, but the list at the door was printed yesterday and their chair is still set.',
                    'Protecting the header row prevents accidents, not copying. Google\'s help says a protected range should not be used as a security measure, and everyone the sheet is shared with can copy every email address in it.',
                ],
                'gaps_lead' => 'A sheet is a very good place to keep a list and a poor place to make one. These are the moments the difference shows.',
                'gaps_close' => 'In each of these the spreadsheet is doing what it was made for, which is storing what you type. An event also needs something to act on it.',
                'today' => [
                    'title' => 'An event on Google Sheets, start to finish',
                    'intro' => 'Most events that live in a spreadsheet go through the same six moves. If this reads like your last one, the rest of the page is written for you.',
                    'steps' => [
                        ['title' => 'Two tabs', 'description' => 'One tab lists the events: date, venue, capacity, status. The other is the guest list, with columns for name, email, places, Paid and Arrived, the last two as checkboxes.'],
                        ['title' => 'A form fills the list', 'description' => 'Nobody outside the team can type into the sheet, so sign-up goes through a Google Form with a spreadsheet chosen as the destination for responses. Each answer becomes a row, and the columns you add beside them are yours to fill.'],
                        ['title' => 'Places counted by formula', 'description' => 'A COUNTA or a SUM in the top row shows how many places are taken. When it reaches the limit you close the form yourself: the formula can report the number and cannot act on it.'],
                        ['title' => 'Payment matched by hand', 'description' => 'For a paid event a payment link sits in the form or in the confirmation text. The money arrives in another account, and someone ticks Paid row by row against the statement.'],
                        ['title' => 'Confirmations by mail merge', 'description' => 'Gmail can run a mail merge from the first tab of a sheet, with the column headers as merge tags, on the Workspace editions that include it. Otherwise it is an add-on or a BCC. The reminder two days before is the same job again, sent to whoever is on the list by then.'],
                        ['title' => 'The door', 'description' => 'The list is sorted by surname and printed, or opened on a phone with the Arrived column. Late sign-ups are written at the bottom in pen, and the ticks are typed back into the sheet the next morning.'],
                    ],
                    'outro' => 'It works, and a great many events run this way. But every join in it is a person: closing the form, matching the payments, sending the merge, reprinting the list. Miss one and the sheet goes on looking correct.',
                ],
                'native_lead' => 'The steps are the same ones. A registration now writes the row, sends the confirmation and makes the door entry by itself.',
                'es_solutions' => [
                    ['title' => 'A page people sign up on', 'description' => 'Each event has a public page with a Register or Buy button, and your schedule shows all of them on one calendar. Free registration is unlimited on every plan, so the form and the published list of dates become a single address.', 'icon' => 'globe', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'A limit that closes sign-up', 'description' => 'Give free registration a capacity and it stops at that number without anyone watching a formula. Later arrivals can join a waitlist, which is free for registration events and on Pro for priced tickets.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Paid, according to the payment', 'description' => 'On Pro, a ticket with a price is bought at checkout through your own Stripe, PayPal, Payfast, Razorpay or Invoice Ninja account, or recorded against a payment link or cash. A Stripe or PayPal payment marks its own sale as paid. Event Schedule takes no platform fee; the processor still charges its own.', 'icon' => 'dollar', 'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30', 'border' => 'border-cyan-200 dark:border-cyan-500/20', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                    ['title' => 'Confirmation and reminder, sent', 'description' => 'A guest who registers gets a confirmation carrying a QR code. Everyone holding a ticket or a registration also gets a reminder email before the event once reminders are on (the default on paid plans, one switch on Free), a day ahead unless you choose 3 hours, 2 days or 3 days. No merge tags involved.', 'icon' => 'mail', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'The door list is the scanner', 'description' => 'Open the scan page in a phone browser and read the QR on each ticket or registration, free on every plan. A ticket admits once and a second scan warns. The page needs a connection and there is no app to install. The live check-in dashboard, with arrivals by ticket type, is on Pro.', 'icon' => 'qr', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'A sheet when you want one', 'description' => 'On Pro, export sales to CSV: buyer, event and date, ticket types and quantities, amount, payment status, check-in status and time, and one column for each custom field. Open it in Google Sheets and the formulas run on rows that nobody typed.', 'icon' => 'chart', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                ],
                'why_switch' => [
                    'intro' => 'You keep the habit of looking at one list. What changes is who writes it: the guest, the payment and the scanner, where it used to be you.',
                    'points' => [
                        'The events tab: paste its rows into the import box, and each event comes back for you to review before it is added',
                        'An open guest list: imported from CSV on Pro, up to 5,000 rows at a time, and each guest imported as paid can be emailed a confirmation',
                        'The Paid column: set by the payment on Pro, and refundable in full or in part from the Sales page for Stripe, PayPal and Razorpay sales',
                        'The Arrived column: written by the scan at the door',
                        'Your extra columns, such as dietary needs: questions on the registration form, on Pro',
                    ],
                ],
                'switch_lead' => 'Leave past events in the spreadsheet. Move the events tab now, and move a guest list only if that event is already filling.',
                'switch_steps' => [
                    ['title' => 'Paste the events tab', 'description' => 'Copy the rows of upcoming events out of the sheet and paste them into the import box on a free schedule. Several events come back as a list you tick and add, and nothing is public until you add it. Enter a weekly or monthly event once, as a recurring event.'],
                    ['title' => 'Bring the open list, or let it run out', 'description' => 'For an event people have already signed up to, download that tab as a CSV and import it on Pro, matching each column to a field. Email is the only required one. For anything further out, close the form and put the event page link where the form link was.'],
                    ['title' => 'Retire the sheet to the archive', 'description' => 'Rename the spreadsheet with the year and stop editing it. Tell whoever worked in it that the list now lives on the Sales page. One thing to plan for: a schedule has a single login until Enterprise, which carries up to five team members.'],
                ],
                'faq' => [
                    ['question' => 'Can I keep using Google Sheets alongside Event Schedule?', 'answer' => 'You can, and for analysis you probably should. On Pro the Sales page exports to CSV, which Sheets opens, and the REST API and webhooks let your own script or automation tool add a row for each sale. The part that stops is typing the list in.'],
                    ['question' => 'What does Google Sheets still do better?', 'answer' => 'Anything that is not a registration. A seating chart, a budget, a volunteer rota and a run of show all want a free-form grid with formulas, and Event Schedule has neither; it does not handle volunteer shifts at all. Sheets also lets a whole committee into one list by sharing a link, where a second team member here needs Enterprise.'],
                    ['question' => 'How do I move the guest list I already have in a spreadsheet?', 'answer' => 'Download the tab as a CSV and import it from the Sales page, on Pro, onto an event that uses ticket types (a free ticket type is enough; Free registration has none to import into). You match each column to a field, only email is required, and one import takes up to 5,000 rows. Duplicate emails inside the file are skipped, and you choose whether guests imported as paid are emailed a confirmation.'],
                    ['question' => 'Is Event Schedule free the way Google Sheets is?', 'answer' => 'The Free plan, at '.plan_price(0).', covers unlimited events, public pages, free registration with a capacity limit and scanning at the door. A ticket with a price, the CSV export, the attendee import and your own questions on the form are Pro, at '.plan_price($this->planPrice()).' a month. More than one team member is Enterprise, at '.plan_price($this->planPrice(true)).' a month. No plan takes a platform fee on sales; the payment processor charges its own.'],
                    ['question' => 'Can several people work the door from the same list?', 'answer' => 'On Enterprise, up to five team members can sign in, and a viewer can scan tickets without seeing any sales. A ticket admits once, so one shown at a second entrance comes up as already used. On Free and Pro the schedule has a single login.'],
                    ['question' => 'Can I add my own columns, like dietary needs or T-shirt size?', 'answer' => 'On Pro, custom fields put your own questions on the registration or checkout form, and each one is a column in the CSV export. There is no formula column, though: counts and sums beyond the built-in totals are done in the export.'],
                    ['question' => 'Does the scanner work without a signal, like a printed list does?', 'answer' => 'No. The scanner is a page in the phone\'s browser and needs a connection to check each code. For a venue with no signal, print the guest list from the CSV export on Pro and tick names in pen. That is the one place the paper list still wins.'],
                ],
                'cross_links' => [
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms', 'description' => 'The form that feeds the sheet, and what a registration page does differently.'],
                    ['name' => 'SurveyMonkey', 'route' => 'marketing.replace_surveymonkey', 'description' => 'When the sign-up is a survey and the answers are exported to a sheet.'],
                    ['name' => 'Doodle', 'route' => 'marketing.replace_doodle', 'description' => 'When the first tab of the sheet was a poll to pick the date.'],
                ],
                'related_features' => [
                    ['name' => 'Analytics', 'route' => 'marketing.analytics', 'description' => 'Page views, followers and sales per schedule, without a formula.'],
                    ['name' => 'Integrations', 'route' => 'marketing.integrations', 'description' => 'The REST API and webhooks on Pro, for sending sales to a sheet of your own.'],
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Priced tickets on Pro, with every sale recorded as it is paid.'],
                ],
            ],
            'calendly' => [
                'name' => 'Calendly',
                'tagline' => 'Keep bookable hours for one-to-one time, and give classes, workshops and tours a dated page with places and tickets.',
                'meta_title' => 'Replace Calendly for Bookings and Events | Event Schedule',
                'description' => 'Where Calendly stops for classes and public events, how organizers work around it, and what bookings, registration and tickets on one schedule change.',
                'keywords' => 'Calendly alternative for events, Calendly replacement, event scheduling tool, public event scheduling',
                'audience_hint' => 'For teachers, coaches, studios and guides who take one-to-one bookings and also fill classes, workshops or tours.',
                'about' => 'Calendly is a scheduling tool: you publish your availability, an invitee picks a slot, and the meeting lands on both calendars. Event people stretch it with the Group event type, which lets many invitees book the same slot up to an invitee limit, to fill classes, tours and webinars. It handles the booking well and leaves the public listing, the tickets and the door to other tools.',
                'pricing_note' => 'Calendly charges $10/month per user for its Standard plan. Event Schedule is free for unlimited events, public pages and one appointment type. Free registration is unlimited, and more types, putting a price on a ticket or a booking, and the advanced scheduling rules are on Pro at '.plan_price($this->planPrice()).'/month, with no per-user fees.',
                'competitor_price' => 'From $10/mo per user',
                'es_price' => 'From '.plan_price(0).'/mo (Pro '.plan_price($this->planPrice()).'/mo)',
                'comparison_rows' => [
                    ['feature' => 'One-to-one booking from your weekly hours', 'competitor' => true, 'es' => 'Free, one type'],
                    ['feature' => 'Several booking types', 'competitor' => true, 'es' => 'On Pro'],
                    ['feature' => 'Many people booking the same session', 'competitor' => 'Group event type', 'es' => 'Registration, capped per date'],
                    ['feature' => 'A public calendar and a page for each dated event', 'competitor' => false, 'es' => true],
                    ['feature' => 'Several places in one order', 'competitor' => 'One payment per booking', 'es' => 'On Pro'],
                    ['feature' => 'Refunds issued inside the tool', 'competitor' => 'In Stripe or PayPal', 'es' => 'On Pro, from Sales'],
                    ['feature' => 'A QR code scanned at the door', 'competitor' => false, 'es' => 'Free, on events'],
                    ['feature' => 'Reminders by text message', 'competitor' => 'Workflows', 'es' => 'Email only'],
                    ['feature' => 'A new video link for each booking', 'competitor' => 'With Zoom', 'es' => 'Your own link'],
                    ['feature' => 'Newsletter to guests and followers', 'competitor' => false, 'es' => 'Free, 10 emails a month'],
                ],
                'tool_strengths' => [
                    'Finding a time without the email back-and-forth: people see only the slots your availability allows and pick one',
                    'Team scheduling, with round robin and collective event types that share bookings across several hosts',
                    'Video meetings: with Zoom as the location, each booking is given its own newly made link',
                    'Workflows that send reminders and follow-ups around a booking, by email or by text message',
                ],
                'keep_when' => [
                    'your work is meetings: sales calls, interviews and client sessions, shared between several colleagues',
                    'you need a fresh Zoom link created for every booking, or reminders sent by text message',
                    'nothing you run is public: no timetable to publish, no tickets, nobody to count at a door',
                ],
                'pain_points' => [
                    'Your Saturday workshop exists only as a slot inside a booking flow. There is no page with the flyer, the venue and the date that someone can find, share, or see listed beside your other dates.',
                    'A parent wants three places in the pottery class. Payment is taken once per booking, so that is three bookings, or one booking and a payment request for the other two.',
                    'Someone cancels the day before and asks for their money back. You leave Calendly and make the refund in Stripe or PayPal, because refunds are not issued from Calendly.',
                    'A one-off evening does not fit weekly availability. You add date-specific hours for that single date, and they apply to the whole event type, so the rest of the week has to be closed around it.',
                    'At the door you read names from the Invitees list on your phone. The guest holds a calendar entry, not a ticket, and nothing records who has already walked in.',
                    'Next term opens and there is nothing in Calendly to announce it with. The people who came are invitees on past meetings, and the mailing happens in another tool.',
                ],
                'gaps_lead' => 'Calendly answers one question well: when are you both free. A class, a tour or a show asks different ones: what is on, how many places are left, who paid for how many, and who came.',
                'gaps_close' => 'That is not a flaw in Calendly, which was designed around a meeting between people who are already in touch, not around an audience buying places.',
                'today' => [
                    'title' => 'A workshop on Calendly, start to finish',
                    'intro' => 'This is how a teacher or a guide usually fills a class with a tool that was built to book meetings.',
                    'steps' => [
                        ['title' => 'Create a Group event type', 'description' => 'One-on-One is the usual starting point, so you choose Group and set the invitee limit to the size of the room. Turn on Display remaining spots and each time slot shows how many places are left.'],
                        ['title' => 'Bend availability into a timetable', 'description' => 'The event type offers every slot your availability allows. For a class on Tuesdays at 18:00 you narrow the weekly hours to that one hour; for a single workshop you add date-specific hours for that day and close everything else.'],
                        ['title' => 'Attach payment', 'description' => 'Connect your own Stripe or PayPal account and require payment to book. Each invitee pays when they book their spot, one payment per booking, and the money goes straight to your account.'],
                        ['title' => 'Share the booking link', 'description' => 'The link goes on your site, in your bio and on a flyer made somewhere else. Calendly\'s landing page lists your event types, not next month\'s dates, so there is no calendar of sessions to point people to.'],
                        ['title' => 'Let the confirmations run', 'description' => 'Each invitee gets a confirmation email with a calendar file, and a Workflow can send a reminder the day before. If the class is online with Zoom as the location, the invitation carries the link.'],
                        ['title' => 'Run the day from the Invitees list', 'description' => 'You open the scheduled meeting, read its Invitees section and tick people off on paper. Anyone who asks for their money back is refunded from Stripe or PayPal.'],
                    ],
                    'outro' => 'It works, and for a weekly class of twelve it is fine. What you have built is a meeting with many guests: no page for the event, no order holding several places, no ticket, and no record of who walked in.',
                ],
                'native_lead' => 'One schedule holds both halves: hours that people book, and dated events that people register for or buy.',
                'es_solutions' => [
                    ['title' => 'Bookable hours, one type free', 'description' => 'Set weekly hours, a duration and how often a start time is offered, and guests book from a public page in their own timezone. One appointment type is free. More types, a price on a booking, buffers, minimum notice, date overrides and an approval step are on Pro.', 'icon' => 'calendar', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'A dated page for every class', 'description' => 'A class or a workshop is an event with a page of its own: flyer, description, venue with a map, date and time. A weekly class is one recurring event, and every date shows on your public schedule. Free on every plan.', 'icon' => 'globe', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Places counted per date', 'description' => 'Free registration takes sign-ups up to a limit for each date and opens a waitlist when it fills, on every plan. Ticket types with a price are on Pro, a buyer can take several in one order, and no platform fee is added; your payment processor still charges its own.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'A ticket the door can scan', 'description' => 'Every registration and ticket for an event carries a QR code. A phone browser scans it at the door on any plan, admits the order once and warns on a second scan. A booked appointment is different: it has no QR code, only a confirmation, a calendar invite and a private link.', 'icon' => 'qr', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'Reminders, and a way to announce next term', 'description' => 'A confirmed booking gets a reminder email about a day ahead, once the schedule has its own email settings, and ticket holders get one before their event. Everyone who booked or bought can be sent a newsletter: the monthly allowance is 10 recipients on Free, 100 on Pro and 1,000 on Enterprise. All of it is email. There are no text messages.', 'icon' => 'mail', 'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30', 'border' => 'border-cyan-200 dark:border-cyan-500/20', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                    ['title' => 'Paid bookings, moved and refunded in one place', 'description' => 'On Pro a booking can be paid by Stripe, PayPal, Payfast, Razorpay, a payment link or cash. A guest moves a booking from their private link and the payment carries over. A Stripe, PayPal or Razorpay payment is refunded from the Sales page, in full or in part.', 'icon' => 'dollar', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                ],
                'why_switch' => [
                    'intro' => 'Your hours and your booking link have an equivalent here. What does not come with you is the meeting machinery: bookings shared across a team, text reminders and a new video link per booking stay Calendly\'s territory.',
                    'points' => [
                        'One booking type with its weekly hours, free; your other types on Pro',
                        'Classes run as Group event types, re-created as recurring events with a limit on places per date',
                        'Paid sessions, taken through your own Stripe or PayPal account on Pro',
                        'Your Google or Outlook calendar, synced both ways, so what is already in it blocks slots',
                        'The link in your bio and email signature, pointed at the new booking page',
                    ],
                ],
                'switch_lead' => 'Move the classes first, since they gain the most, and the one-to-one hours when the diary allows.',
                'switch_steps' => [
                    ['title' => 'Rebuild the hours and set up email', 'description' => 'Create a schedule, set its timezone, and add an appointment type with the same duration and weekly hours as your Calendly event type. On eventschedule.com, add the schedule\'s email settings before you share it: booking confirmations and reminders are not sent until you do.'],
                    ['title' => 'Turn each class into an event', 'description' => 'Make the class a recurring event on its real dates and switch on registration, with the old invitee limit as the cap, or add ticket types with a price on Pro. Bookings already made in Calendly stay there; honour them and take new ones on the new page.'],
                    ['title' => 'Swap the links and wind down', 'description' => 'Replace the Calendly link on your site, in your bio and in your signature with your booking page and your schedule. Leave the old event types on until the last booked date has passed, then turn them off.'],
                ],
                'faq' => [
                    ['question' => 'Can Event Schedule replace Calendly for one-to-one bookings?', 'answer' => 'For one person with weekly hours, largely. One appointment type is free: guests pick a slot in their own timezone, get a confirmation with a calendar invite and a reminder once the schedule has its own email settings, and can move or cancel from a private link. It does not rotate bookings across a team, send text messages or create a new video link for each booking. On Enterprise a type can name one team member as its host.'],
                    ['question' => 'How do I run a class with limited places?', 'answer' => 'As an event, not as a bookable slot. Create the class on its dates, weekly if it repeats, and turn on registration with a limit per date. People sign up on the event page, receive a QR code, and can join a waitlist when the date is full. That part is free; charging for a place is on Pro.'],
                    ['question' => 'What does it cost compared with paying per seat?', 'answer' => 'One appointment type, unlimited events, free registration and door scanning are on the Free plan at '.plan_price(0).'. More appointment types, a price on a booking or a ticket, and the scheduling rules (buffers, minimum notice, booking window, date overrides, approval) are on Pro at '.plan_price($this->planPrice()).' a month. Enterprise at '.plan_price($this->planPrice(true)).' a month adds up to five team members and a host per appointment type. No platform fee is taken on any plan; the payment processor charges its own.'],
                    ['question' => 'Can I keep Calendly and use Event Schedule beside it?', 'answer' => 'Plenty of people should. Keep Calendly for meetings and team scheduling, and put the public classes, workshops and shows on a schedule. The two do not need to know about each other: one arranges meetings, the other holds what the public can attend.'],
                    ['question' => 'What does Calendly still do better?', 'answer' => 'Meetings between busy people. Round robin and collective scheduling across a team, text message reminders through Workflows, and a fresh Zoom link for every booking are things Event Schedule does not do. Its appointments are email only, with a meeting link you enter yourself.'],
                    ['question' => 'Can I move my existing Calendly bookings across?', 'answer' => 'No, there is no import for bookings, and moving them would mean asking each person to book again. Let the ones already made run their course in Calendly and send new people to the new page. Classes are quicker to move, because the event is created once and people sign up afresh.'],
                    ['question' => 'What happens when a guest needs to move or cancel a paid booking?', 'answer' => 'The guest moves it from the private link in their confirmation and the payment stays attached, so nobody pays twice. To give money back, refund from the Sales page: a Stripe, PayPal or Razorpay payment is returned through the provider, in full or in part. Refund before you cancel, because cancelling on its own moves no money.'],
                ],
                'cross_links' => [
                    ['name' => 'Doodle', 'route' => 'marketing.replace_doodle', 'description' => 'From a date poll to an event people can sign up for.'],
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms', 'description' => 'Sign-up forms that know how many places are left.'],
                    ['name' => 'Linktree', 'route' => 'marketing.replace_linktree', 'description' => 'One link that lists every date and takes the booking.'],
                ],
                'related_features' => [
                    ['name' => 'Calendar Sync', 'route' => 'marketing.calendar_sync', 'description' => 'Two-way sync with Google Calendar, Outlook and CalDAV.'],
                    ['name' => 'Appointments', 'route' => 'marketing.appointments', 'description' => 'One bookable appointment type free; more types and paid bookings on Pro.'],
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Ticket types with a price on Pro, with no platform fee.'],
                ],
            ],
            'surveymonkey' => [
                'name' => 'SurveyMonkey',
                'tagline' => 'Ask how it went without uploading a list, and take sign-ups on a page that knows the date.',
                'meta_title' => 'Replace SurveyMonkey for Event Feedback | Event Schedule',
                'description' => 'What running event sign-ups and feedback surveys on SurveyMonkey involves, where the exports take over, and which parts an event page does by itself.',
                'keywords' => 'SurveyMonkey alternative for events, SurveyMonkey replacement, event registration, event signup',
                'audience_hint' => 'For organizers who build a registration survey before each event and a satisfaction survey after it.',
                'about' => 'SurveyMonkey is a survey platform: you design a questionnaire, send it through a collector such as a web link or an email invitation, and read the charts and exports in Analyze. Event people use it at both ends of an event. Beforehand it is a registration form, with event templates and a Stripe or PayPal payment option on paid plans; afterwards it is the feedback survey. The event in between happens where SurveyMonkey cannot see it.',
                'pricing_note' => 'SurveyMonkey charges $25+/month for features like payment collection and custom branding. Event Schedule registration is free and unlimited; paid tickets, at zero platform fees, are on Pro at '.plan_price($this->planPrice()).'/month, which also adds feedback, polls and custom fields.',
                'competitor_price' => 'From $25+/mo',
                'es_price' => 'From '.plan_price(0).'/mo (Pro '.plan_price($this->planPrice()).'/mo)',
                'comparison_rows' => [
                    ['feature' => 'Post-event feedback', 'competitor' => 'Any survey you design', 'es' => 'Rating and comment, on Pro'],
                    ['feature' => 'Who is asked', 'competitor' => 'A contact list you upload', 'es' => 'Everyone who booked that date'],
                    ['feature' => 'When it is sent', 'competitor' => 'When you send it', 'es' => 'After the event, on a delay you set'],
                    ['feature' => 'Skip logic and NPS questions', 'competitor' => true, 'es' => 'No, it is not a survey tool'],
                    ['feature' => 'Registration form', 'competitor' => 'A survey from an event template', 'es' => 'A Register button on the event'],
                    ['feature' => 'Capacity', 'competitor' => 'Response limit per collector', 'es' => 'Places per date, with a waitlist'],
                    ['feature' => 'Taking payment', 'competitor' => 'Stripe or PayPal, on paid plans', 'es' => 'On Pro, no platform fee'],
                    ['feature' => 'QR ticket and door scan', 'competitor' => false, 'es' => 'Scan free, live dashboard on Pro'],
                    ['feature' => 'Quick question on the event page', 'competitor' => false, 'es' => 'Event polls, on Pro'],
                    ['feature' => 'Getting results out', 'competitor' => 'CSV, XLS, PDF, PPT, SPSS', 'es' => 'CSV, API and webhook, on Pro'],
                ],
                'tool_strengths' => [
                    'Question skip logic and page logic that route each respondent by what they answered',
                    'A Net Promoter Score question that sorts answers into Detractors, Passives and Promoters for you',
                    'Email invitation collectors that record one response per address and can remind the people who have not answered',
                    'Exports to CSV, XLS, PDF, PPT and SPSS for a report or a board pack',
                ],
                'keep_when' => [
                    'you need to learn something that takes fifteen questions: a member survey, a sponsor report, research for next season',
                    'the answers must be anonymous, which its Anonymous Responses setting handles collector by collector',
                    'you are surveying people who never booked: a mailing list, the neighbours, your staff',
                ],
                'pain_points' => [
                    'After the event you export the attendee list from wherever it lives, tidy it, upload it as contacts and write the invitation. By the time it goes out, the evening is four days old.',
                    'A web link dropped in the chat can be answered by people who were not there, and for a weekly event nothing ties an answer to the Tuesday it is about unless you add a question for it.',
                    'A registration survey has a response limit, not a room. It closes at the number you set, a few late responses can still slip in, and a cancellation does not reopen it.',
                    'Registrants get a thank-you page or a copy of their answers. On the night there is nothing for them to show and nothing to scan, so the door works from an export.',
                    'Paid registration puts the money in Stripe or PayPal and the answers in Analyze. A refund is made in the payment account, not in the survey.',
                    'Every event starts with a new survey and a new link, and last month\'s registrants stay in last month\'s results.',
                ],
                'gaps_lead' => 'A survey tool sees the questionnaire. It does not see the booking, the date or the door, and that is where the exporting comes from.',
                'gaps_close' => 'As a way to ask well-built questions and analyse the answers, SurveyMonkey goes far past anything on this page, and nothing here says otherwise.',
                'today' => [
                    'title' => 'One event, two surveys and the gap between',
                    'intro' => 'An evening course or a conference day run on SurveyMonkey has a survey in front of it and a survey behind it. This is the full loop.',
                    'steps' => [
                        ['title' => 'Build the registration survey', 'description' => 'You start from an event registration template, add questions for session choice and dietary needs, and use skip logic so only the people who pick the dinner see the menu question.'],
                        ['title' => 'Set up the collector', 'description' => 'You create a web link collector and give it a cutoff date and a response limit to match the room. On a paid plan you can add a payment step that sends the fee to your own Stripe or PayPal account.'],
                        ['title' => 'Turn responses into a guest list', 'description' => 'Registrations arrive as responses in Analyze. You export them to a spreadsheet, send the joining details from your own email and print the sheet for the door.'],
                        ['title' => 'Run the event from the export', 'description' => 'Whatever changes after the export is yours to track: the person who cancels by email, the colleague added by phone, the walk-in. The survey knows none of it.'],
                        ['title' => 'Send the feedback survey', 'description' => 'The next morning you copy the last feedback survey, change the event name, upload the attendee addresses to an email invitation collector and send. One automated reminder can follow for those who have not answered.'],
                        ['title' => 'Read the results', 'description' => 'Analyze gives you a chart per question and an export. Linking a low score to what that person booked, or comparing this month with last, means matching addresses across two surveys and a spreadsheet.'],
                    ],
                    'outro' => 'The surveys are the quick part. The slow part is carrying the list of people from the first survey to the door, and from the door to the second survey, by export, every time.',
                ],
                'native_lead' => 'Here the booking is the record: registration creates it, the door scans it, and the feedback request is addressed to it.',
                'es_solutions' => [
                    ['title' => 'Feedback sent to the people who booked', 'description' => 'On Pro, once an event date has ended, everyone who held a booking for it is emailed a private link: a rating from one to five, which is required, and a comment of up to 2,000 characters, which is not. You choose the delay, from 1 to 48 hours, and there is no list to upload. On eventschedule.com the request goes out through your schedule\'s own email settings.', 'icon' => 'chart', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'One answer per booking, tied to its date', 'description' => 'The link belongs to one booking, so nobody outside the event can answer and nobody answers twice. On a weekly event each date collects its own answers. Read them on the Feedback tab under an average and a response rate, export them as CSV, or receive each one by webhook or API. All on Pro.', 'icon' => 'mail', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Registration without building a survey', 'description' => 'Switch an event to Free registration and its page takes sign-ups with a name and an email, a Registration Limit per date and a waitlist when it fills. That is unlimited on the free plan. Your own questions, such as session choice or dietary needs, are custom fields on Pro.', 'icon' => 'ticket', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'A code to scan, not a row to find', 'description' => 'Each registration or ticket is confirmed by email with a QR code. At the door a team member scans it from a phone browser, free on every plan, and a second scan of the same code warns them. The live check-in dashboard is on Pro.', 'icon' => 'qr', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'A poll for the one quick question', 'description' => 'On Pro an event can carry up to five polls of two to ten choices each. Signed-in guests mark one choice and see the count once they have voted. The poll sits on the event page, so there is no second link to send. It is a vote, not a survey: one question and a short list of choices.', 'icon' => 'globe', 'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30', 'border' => 'border-cyan-200 dark:border-cyan-500/20', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                    ['title' => 'Payment and refunds on one page', 'description' => 'On Pro, priced tickets are paid during booking into your own Stripe, PayPal or other connected account, with no platform fee from Event Schedule on top of the processor\'s own. A Stripe, PayPal or Razorpay sale is refunded from the Sales page, in full or in part.', 'icon' => 'dollar', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                ],
                'why_switch' => [
                    'intro' => 'Move the two jobs that depend on knowing who booked: taking the sign-up and asking how it went. Leave the research surveys where they are.',
                    'points' => [
                        'Sign-ups, with a place count per date and a waitlist',
                        'The guest list, as bookings with QR codes instead of an export',
                        'The post-event request, sent to that date\'s bookings with no list to upload, on Pro',
                        'An average rating and a response rate without building a report, on Pro',
                        'Payment for priced places, on Pro',
                    ],
                ],
                'switch_lead' => 'Start with the next event\'s sign-up, then let its feedback request send itself.',
                'switch_steps' => [
                    ['title' => 'Export what you want to keep', 'description' => 'In Analyze, export past results as CSV or XLS, and as PDF if you report to a board. Event Schedule does not import survey responses, so those files are your archive.'],
                    ['title' => 'Put the next event on a page', 'description' => 'Create a schedule, add the event and choose Free registration or tickets. Recreate the registration questions you really use as custom fields on Pro. Then close the old collector and share the event link in its place.'],
                    ['title' => 'Switch on feedback before the night', 'description' => 'On Pro, save your schedule\'s email settings, turn on Feedback under the schedule\'s Engagement settings, pick the delay and send yourself the test email. Tell your regulars the question is now a rating and an optional comment.'],
                ],
                'faq' => [
                    ['question' => 'Can Event Schedule replace my post-event SurveyMonkey survey?', 'answer' => 'It replaces the short one. On Pro, each person who booked is emailed after the event for a one-to-five rating and an optional comment, and the answer is tied to that event date. It does not replace a questionnaire: there are no extra feedback questions, no skip logic and no NPS calculation. If your post-event survey is twelve questions for a funder, keep sending it from SurveyMonkey.'],
                    ['question' => 'Who receives the feedback request, and when?', 'answer' => 'Everyone who holds a paid or free booking for that date, once per booking, so a person who booked six seats is asked once. It goes out after the date has ended plus a delay you choose from 1, 2, 6, 12, 24 or 48 hours, worked out in your schedule\'s timezone. Bookings refunded in full, passes and followers are not asked.'],
                    ['question' => 'Is the feedback anonymous?', 'answer' => 'No. Each answer is tied to a booking, so you see the attendee\'s name and email beside the rating. If you choose to publish reviews on the event page, only first names are shown, and attendees are told so on the form. For anonymous answers, SurveyMonkey\'s Anonymous Responses option is the right tool.'],
                    ['question' => 'Can I take event registrations here instead of a registration survey?', 'answer' => 'For most events, and for free ones at no cost. A registration takes a name, an email and optionally a phone number, counts against a limit per date, joins a waitlist when the date is full and is confirmed with a QR code. Extra questions are custom fields on Pro. What you give up is skip logic.'],
                    ['question' => 'What does SurveyMonkey still do better, and can I use both?', 'answer' => 'Survey design and analysis: skip logic, a wide range of question types, an NPS question that scores itself, anonymous collectors and exports to PDF, PPT and SPSS are beyond what Event Schedule attempts. The two work side by side. Take the booking and the quick rating in Event Schedule, and send a SurveyMonkey link once or twice a year for the long questions.'],
                    ['question' => 'How much does Event Schedule cost for this?', 'answer' => 'Free registration, its waitlist, confirmation emails and door scanning are on the free plan at '.plan_price(0).'. Feedback, polls, custom fields and priced tickets are on Pro at '.plan_price($this->planPrice()).' a month, and nothing is charged per response. Whether that replaces a SurveyMonkey plan depends on whether you still need one for research surveys.'],
                    ['question' => 'Can I import my old survey results or registrant lists?', 'answer' => 'Survey results stay as exports; there is nothing here to import them into. A registrant list is different: on Pro, the attendee import takes a CSV of up to 5,000 rows and adds each person to an event that uses ticket types, a free ticket type included. It does not import into a Free registration event. Past events can stay in SurveyMonkey as a record while the next one starts on its own page.'],
                ],
                'cross_links' => [
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms', 'description' => 'The same sign-up problem, with a linked Sheet instead of Analyze.'],
                    ['name' => 'Doodle', 'route' => 'marketing.replace_doodle', 'description' => 'For the poll that picks the date before any of this starts.'],
                    ['name' => 'Google Sheets', 'route' => 'marketing.replace_google_sheets', 'description' => 'Where the exported registrant list usually ends up.'],
                ],
                'related_features' => [
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Priced tickets and refunds from one Sales page, on Pro.'],
                    ['name' => 'Custom Fields', 'route' => 'marketing.custom_fields', 'description' => 'The registration questions you used to ask in a survey, on Pro.'],
                    ['name' => 'Feedback', 'route' => 'marketing.feedback', 'description' => 'A rating and a comment from each booking after the event, on Pro.'],
                ],
            ],
            'doodle' => [
                'name' => 'Doodle',
                'tagline' => 'Doodle finds the date. This is for everything that comes after the date is booked.',
                'meta_title' => 'Replace Doodle for Event Scheduling | Event Schedule',
                'description' => 'What happens after a Doodle poll picks the date: the announcement, the headcount, the reminders and the door, and which of those an event page handles.',
                'keywords' => 'Doodle alternative for events, Doodle replacement, event scheduling poll, event date scheduling',
                'audience_hint' => 'For clubs, bands, classes and committees that poll for a date and then run the event from a chat thread.',
                'about' => 'Doodle is a scheduling tool best known for the group poll: you propose times, share one link, people mark the ones that suit them without creating an account, and you book the winner. It also has a Booking Page and 1:1s for appointments, and Sign-up Sheets for sessions with a seat limit. Organizers use the poll to settle a date for a rehearsal, a board meeting or a reunion, and the sign-up sheet as a light registration list.',
                'pricing_note' => 'Doodle charges $7+/month per user for its Pro plan. Event Schedule is free for unlimited events with a public schedule. Free registration is unlimited, and selling paid tickets is on Pro at '.plan_price($this->planPrice()).'/month, and nothing here is priced per user.',
                'competitor_price' => 'From $7+/mo per user',
                'es_price' => 'From '.plan_price(0).'/mo (Pro '.plan_price($this->planPrice()).'/mo)',
                'comparison_rows' => [
                    ['feature' => 'Poll invitees for a date', 'competitor' => true, 'es' => 'No availability poll'],
                    ['feature' => 'Event page with details, map and image', 'competitor' => 'A poll or sheet link', 'es' => true],
                    ['feature' => 'Sign-ups with a place limit', 'competitor' => 'Sign-up Sheets, per session', 'es' => 'Free registration, per date'],
                    ['feature' => 'Paid tickets for an event', 'competitor' => 'Payments on Booking Page only', 'es' => 'On Pro, no platform fee'],
                    ['feature' => 'QR code and door scan', 'competitor' => false, 'es' => 'Free scan from a phone browser'],
                    ['feature' => 'Recurring events', 'competitor' => 'Not created directly', 'es' => 'Weekly, monthly and more'],
                    ['feature' => 'Your own calendar', 'competitor' => 'Adds the booked time', 'es' => 'Two-way Google, Outlook, CalDAV'],
                    ['feature' => 'Getting the date into other calendars', 'competitor' => 'Invite to those who responded', 'es' => 'Add to Calendar and a live feed'],
                    ['feature' => 'Reminder before the event', 'competitor' => 'For sign-up sheet participants', 'es' => 'For everyone registered'],
                    ['feature' => 'Booking a time slot with you', 'competitor' => 'Booking Page and 1:1', 'es' => 'One appointment type free, more on Pro'],
                ],
                'tool_strengths' => [
                    'One link to share, and participants vote without creating an account',
                    'A booked time goes into your connected calendar, and Doodle uses that calendar to keep you from being double-booked',
                    'Calendar invites go to everyone who responded as soon as you book the time',
                    'Sign-up Sheets cover several sessions with a seat limit on each',
                ],
                'keep_when' => [
                    'the date is still open and depends on eight particular people being free',
                    'you are arranging meetings and not events: interviews, a committee, a call across time zones',
                    'a sign-up sheet with a few sessions and a seat count is all the registration you need',
                ],
                'pain_points' => [
                    'The poll closes with a winner and the organizing moves to a chat thread: where, what to bring, who is in. People who never voted are not told the final time.',
                    'A poll asks the people you invited whether they can make it. It gives the public nothing to find, share or sign up on.',
                    'A weekly class or a monthly meetup needs no vote after the first one, and Doodle does not create recurring meetings directly, so the repeat lives in a calendar invite.',
                    'A sign-up sheet gives you names against sessions. On the night that is a list to print, with nothing for a guest to show and nothing to scan.',
                    'If the evening costs money, the poll has no way to take it. Payment goes by bank transfer or cash and is tracked in the same chat.',
                    'Next month the same people get a new link, and anyone who has joined since has to be found and added.',
                ],
                'gaps_lead' => 'Doodle answers one question, when, for a known set of people. An event raises the next five.',
                'gaps_close' => 'Picking a time among busy people is a real problem and Doodle handles it well. It was never meant to be the event\'s home.',
                'today' => [
                    'title' => 'An event on Doodle, from poll to the day',
                    'intro' => 'Take a choir\'s summer concert or a club\'s annual dinner. The date is the first decision, and Doodle is where it gets made.',
                    'steps' => [
                        ['title' => 'Propose the dates', 'description' => 'You create a group poll, add four or five candidate evenings and copy the link. Sharing that link is how people are invited, so it goes to the email list and into the chat.'],
                        ['title' => 'Chase the votes', 'description' => 'Answers come in over a week. You nudge the ones who have not voted, or on a paid plan let automatic reminders do it, and watch for the evening most people can make.'],
                        ['title' => 'Book the winner', 'description' => 'You pick the final time. Doodle adds it to your connected calendar and sends invites to the people who responded. Anyone who did not vote hears nothing, so you write to them yourself.'],
                        ['title' => 'Announce it somewhere else', 'description' => 'The poll was for the members. The audience still needs somewhere to read the date, the venue and the programme, so you make a social post, a page on the website or an email.'],
                        ['title' => 'Collect who is coming', 'description' => 'For numbers you open a second thing: a Doodle sign-up sheet with a seat limit, a form, or a count of replies to an email. Money, if there is any, is collected separately.'],
                        ['title' => 'Hold the event', 'description' => 'On the day you work from a printed list of names. Afterwards the poll, the sheet and the thread are finished, and next year starts with a new link.'],
                    ],
                    'outro' => 'The poll took one link and a few days. Everything after it, the announcement, the headcount, the money and the door, was put together by hand from other tools.',
                ],
                'native_lead' => 'Once the date exists, it goes on a page that takes the sign-ups, sends the reminders and feeds everyone\'s calendar.',
                'es_solutions' => [
                    ['title' => 'A page for the date you picked', 'description' => 'Add the event to your schedule and it has its own address with the time, the venue map, the description and an image. Your schedule page lists every upcoming date, so members and the public look in one place. Events are unlimited on the free plan.', 'icon' => 'globe', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'RSVPs with a headcount', 'description' => 'Free registration puts a Register button on the event page, with a limit per date and a waitlist when it fills. Each person gets a confirmation with a QR code and, once you switch reminders on, a reminder before the event. That is all on the free plan. A ticket with a price needs Pro and is paid into your own payment account, with no platform fee.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Dates that repeat without a new poll', 'description' => 'A rehearsal every Tuesday or a meetup on the second Thursday is one recurring event: daily, weekly on chosen days, every few weeks, monthly by date or by weekday, or yearly, with single dates added or skipped. Each date takes its own registrations. Free on every plan.', 'icon' => 'calendar', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'Into every calendar, voters or not', 'description' => 'Each event page has an Add to Calendar menu, and your schedule has a live calendar feed. Someone who subscribes once sees each new date appear and a moved date update itself, without giving an email address. Your own Google, Outlook or CalDAV calendar syncs both ways.', 'icon' => 'calendar', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'A list that hears about the next one', 'description' => 'Visitors can follow your schedule or leave an email address. Confirmed subscribers get an automatic digest when you publish new events, so next month does not begin with collecting addresses. Newsletters you write to that list draw on a monthly allowance of emails, each recipient counting as one: 10 on Free, 100 on Pro, 1,000 on Enterprise.', 'icon' => 'mail', 'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30', 'border' => 'border-cyan-200 dark:border-cyan-500/20', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                    ['title' => 'Bookable slots, if that is what you polled for', 'description' => 'If your Doodle was a Booking Page for lessons or consultations, appointment booking covers it: you set weekly hours and guests pick an open time on a public page. The free plan includes one appointment type, which cannot carry a price, and on eventschedule.com its emails need the schedule\'s own email settings. More types, paid bookings and rules such as buffers need Pro.', 'icon' => 'clipboard', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                ],
                'why_switch' => [
                    'intro' => 'Nothing here picks a date among busy people. What moves is the part of the job that starts once the date is known, and the events that never needed a poll.',
                    'points' => [
                        'The announcement: one event page instead of a message in three places',
                        'The headcount: registrations with a limit per date instead of a sign-up sheet',
                        'The calendar entry: an Add to Calendar menu and a feed that reach people who never voted',
                        'The repeat dates: one recurring event instead of a link per month',
                        'The door: a QR code scanned from a phone browser',
                    ],
                ],
                'switch_lead' => 'Keep your Doodle account for the next undecided date. Move what is already decided.',
                'switch_steps' => [
                    ['title' => 'Copy the booked dates across', 'description' => 'Open your finished polls and sign-up sheets and note the final dates, then add each one as an event on a new schedule. If Doodle booked them into your Google Calendar, the import page can read that calendar and offer its coming events as a list to add.'],
                    ['title' => 'Turn the sign-up sheet into registration', 'description' => 'For each event that needs numbers, choose Free registration and set the Registration Limit to the seat count the sheet had. People already on the sheet register again from the new link.'],
                    ['title' => 'Send one last message to the poll\'s audience', 'description' => 'Post the schedule address where the poll link used to go, and ask people to subscribe to the calendar feed or leave their email on the page. From then on a new date reaches them without a new link.'],
                ],
                'faq' => [
                    ['question' => 'Can Event Schedule run a date poll like Doodle?', 'answer' => 'No, it has no availability poll where invitees mark the times that suit them. The nearest thing is an event poll, on Pro: a question with two to ten choices on an existing event\'s page, which signed-in guests answer once. That suits asking regulars which of two dates they prefer for the next session, not finding a free evening among ten busy people.'],
                    ['question' => 'So when would I use it, and can I keep Doodle alongside?', 'answer' => 'Use it when the date is already set, or was never in question. A public concert, a weekly class or a monthly meetup is announced, not voted on, and needs a page, sign-ups and reminders more than a poll. Keeping Doodle for the one decision and Event Schedule for everything after it is a sensible split.'],
                    ['question' => 'Does it replace Doodle\'s Sign-up Sheets and Booking Page?', 'answer' => 'Sign-up sheets for events, in most cases: free registration gives each date a limit, a waitlist, a QR confirmation and a reminder on the free plan. A sheet of thirty short slots on one evening maps less neatly, since each session would be its own event. For bookable time slots there is appointment booking: one appointment type on the free plan, more types, paid bookings and scheduling rules on Pro, with its emails sent through your schedule\'s own email settings on eventschedule.com.'],
                    ['question' => 'Can my team mark when they are unavailable?', 'answer' => 'Only as whole days away. On Enterprise, each member of a talent schedule\'s team, viewers included, can mark the whole days they are not free, and everyone on the team sees those days on the schedule\'s calendar. It is a private note with no times and no voting, so it tells a band who is away in August and does not find the evening everyone is free.'],
                    ['question' => 'What does Doodle still do better?', 'answer' => 'Finding a time. The group poll, voting without an account, scheduling that checks your calendar and invites to everyone who responded are its core, and Event Schedule does not attempt them. Keep Doodle for meetings and for any date that depends on particular people.'],
                    ['question' => 'What does Event Schedule cost for a club or a class?', 'answer' => 'A schedule, unlimited events, free registration, reminders, calendar sync and the calendar feed are on the free plan at '.plan_price(0).'. Pro at '.plan_price($this->planPrice()).' a month adds priced tickets, event polls and more appointment types. Enterprise at '.plan_price($this->planPrice(true)).' a month adds up to five team members and availability marking.'],
                    ['question' => 'How do I move my existing Doodle polls?', 'answer' => 'You move their results, not the polls. Add each booked date as an event, or import the dates from the Google Calendar they were booked into, and set each sign-up sheet\'s seat count as a Registration Limit. Old polls can stay in Doodle as a record.'],
                ],
                'cross_links' => [
                    ['name' => 'Calendly', 'route' => 'marketing.replace_calendly', 'description' => 'For time slots people book with you, one at a time.'],
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms', 'description' => 'If your headcount after the poll is a form.'],
                    ['name' => 'Google Sheets', 'route' => 'marketing.replace_google_sheets', 'description' => 'If the list of who is coming lives in a spreadsheet.'],
                ],
                'related_features' => [
                    ['name' => 'Calendar Sync', 'route' => 'marketing.calendar_sync', 'description' => 'Two-way sync with Google, Outlook and CalDAV calendars.'],
                    ['name' => 'Polls', 'route' => 'marketing.polls', 'description' => 'A question with a short list of choices on an event page, on Pro.'],
                    ['name' => 'Recurring Events', 'route' => 'marketing.recurring_events', 'description' => 'One event for every Tuesday, with dates you can skip or add.'],
                ],
            ],
            'qr-code-generators' => [
                'name' => 'QR Code Generators',
                'short_name' => 'QR Code',
                'plural' => true,
                'stay_name' => 'a plain QR generator',
                'tagline' => 'A generated QR code opens a web address for anyone who scans it. A ticket code belongs to one order and is checked at the door.',
                'meta_title' => 'Replace QR Code Generators for Check-in | Event Schedule',
                'description' => 'What a QR code generator can and cannot do for an event poster or a ticket, how a door is run with one, and what a code per order changes.',
                'keywords' => 'QR code alternative for events, QR code generator replacement, event QR code, QR code check-in',
                'audience_hint' => 'For organizers who put a QR code on the poster and are wondering whether the same trick can work as a ticket.',
                'about' => 'A QR code generator turns a piece of text, usually a web address, into a square image you can print. A static code holds the address in its own pattern, so it cannot be edited once made and keeps working for as long as the address does. A dynamic code holds a short redirect address run by the generator, which lets you change the destination later and count scans. Organizers put them on posters and flyers, and some try to use them as tickets.',
                'pricing_note' => 'Many QR generators charge $5 to $15/month for dynamic QR codes. Event Schedule puts a QR code on every ticket and free registration, and scanning them at the door is free on every plan, including tickets you sold before. Pro at '.plan_price($this->planPrice()).'/month adds the live check-in dashboard.',
                'competitor_price' => '$5 to $15/mo',
                'es_price' => 'From '.plan_price(0).'/mo (Pro '.plan_price($this->planPrice()).'/mo)',
                'comparison_rows' => [
                    ['feature' => 'A code on a poster that opens a page', 'competitor' => true, 'es' => 'Schedule code, free'],
                    ['feature' => 'A different code for each order', 'competitor' => 'If you supply each value', 'es' => 'Issued with the order'],
                    ['feature' => 'Sends the code to the guest', 'competitor' => false, 'es' => 'In the confirmation email'],
                    ['feature' => 'Knows a code was already used', 'competitor' => false, 'es' => true],
                    ['feature' => 'Checks the order is paid and for this date', 'competitor' => false, 'es' => true],
                    ['feature' => 'Refuses a refunded or cancelled order', 'competitor' => false, 'es' => true],
                    ['feature' => 'What door staff scan with', 'competitor' => 'The phone camera', 'es' => 'A page in the phone browser'],
                    ['feature' => 'A count of scans', 'competitor' => 'Dynamic codes', 'es' => 'Live by ticket type, on Pro'],
                    ['feature' => 'Change the destination after printing', 'competitor' => 'Dynamic codes', 'es' => 'Edit the page behind it'],
                    ['feature' => 'Colours and a logo in the code', 'competitor' => 'On many generators', 'es' => 'Plain image only'],
                ],
                'tool_strengths' => [
                    'A static code needs nothing behind it: it has no expiry date and works for as long as the address it holds is online',
                    'It is general purpose, so the same tool covers a menu, a sign-up form and a poster that opens your event page',
                    'A dynamic code can be pointed somewhere new after the poster is printed, and reports how often it was scanned',
                    'Many generators let you set colours and place a logo in the code for print',
                ],
                'keep_when' => [
                    'the code only has to open a page: a poster, a table card, the last slide of a talk',
                    'you want a styled code with your logo and colours, which Event Schedule does not make',
                    'the event is open to all, and nobody is counted or turned away at the door',
                ],
                'pain_points' => [
                    'You make one code and print it on every ticket. At the door it scans, and so does the copy a guest forwarded to four friends, because the code only holds an address.',
                    'To make tickets unique you generate a code per guest, name the files and attach each to the right email. At forty guests that is an evening, and one swapped file sends a stranger someone else\'s ticket.',
                    'The volunteer on the door scans a code with the phone camera and a web page opens. Nothing says whether this person paid, and nothing is written down, so the second scan looks exactly like the first.',
                    'Two doors, two volunteers, one printed list. Both tick off the same forwarded ticket a minute apart, and neither can know.',
                    'A guest was refunded on Tuesday. Their code still scans on Saturday, because a printed pattern cannot be told about a refund.',
                    'The dynamic code on five hundred posters runs through the generator\'s redirect. If that redirect stops working, the posters lead nowhere.',
                ],
                'gaps_lead' => 'A QR code is a way of writing an address so that a camera can read it. A ticket needs something a picture cannot hold: a record of whether it has been used.',
                'gaps_close' => 'A generator does what it says, and for a poster that is all you need. Being a ticket was never its job.',
                'today' => [
                    'title' => 'A door run on generated QR codes',
                    'intro' => 'There are two versions of this job. The code on the poster works. The code as a ticket is where the evenings go.',
                    'steps' => [
                        ['title' => 'Make the poster code', 'description' => 'Paste the address of your event page or sign-up form into a generator and download the image. A static code is fixed from that moment, so a typo in the address means a new code and a reprint.'],
                        ['title' => 'Take sign-ups somewhere else', 'description' => 'The code only opens a page. Names and payments are collected by a form, a payment link or a ticketing site, and the generator never learns who signed up.'],
                        ['title' => 'Decide what the ticket code holds', 'description' => 'Either every guest gets the same code, which proves nothing, or you invent a value per guest, such as an order number, and make a code for each. Some generators take a spreadsheet and produce them in bulk.'],
                        ['title' => 'Send each guest their code', 'description' => 'The right image goes into the right email, by mail merge or by hand. When a guest loses theirs, you find the file and send it again.'],
                        ['title' => 'Scan at the door with the camera', 'description' => 'A phone camera reads the code and shows its value or opens its link. A person then finds that value on the list and marks it used, because the camera checks nothing.'],
                        ['title' => 'Reconcile the next morning', 'description' => 'Attendance is whatever was ticked on the sheet. Duplicates, refunds and people who paid at the door are sorted out from memory.'],
                    ],
                    'outro' => 'The generator finished its part in step one. Everything after that is a verification system built by hand from a spreadsheet, a mail merge and a volunteer\'s eyesight.',
                ],
                'native_lead' => 'Here the code is issued with the order, and the record behind it decides who gets in.',
                'es_solutions' => [
                    ['title' => 'A code issued with every order', 'description' => 'When someone registers or buys, the confirmation email and their ticket page carry a QR code that belongs to that order alone. Free registrations get one too, on every plan. Nobody generates, names or attaches a file.', 'icon' => 'qr', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'A scanner that checks, not only reads', 'description' => 'Open Scan Ticket on a phone and point the camera. The answer comes back green, amber or red with the name on the order; the first scan admits, and a second says already checked in and when. Free on every plan. It is a page in the browser, not an app, and each scan needs a connection.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Refunds and wrong nights are caught', 'description' => 'The scanner asks the server whether the order is paid, for this event and for tonight. A cancelled or refunded order is refused, and a ticket for another date stops in amber so that a person decides.', 'icon' => 'dollar', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'A running count, on Pro', 'description' => 'The check-in dashboard shows how many people are in, broken down by ticket type, and finds a guest by name or email. That dashboard is the Pro part of check-in. Scanning itself has no plan attached.', 'icon' => 'chart', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'The poster code is covered too', 'description' => 'Each schedule has a QR code to download on every plan, which opens the schedule with its sign-up form ready, and the events graphic prints a code on each flyer that leads to that event\'s page. They are plain codes. For one with your logo in it, a generator is still the right tool.', 'icon' => 'globe', 'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30', 'border' => 'border-cyan-200 dark:border-cyan-500/20', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                    ['title' => 'Guest lists and comps get codes', 'description' => 'On Pro, import a guest list from a CSV file or type it in, up to 5,000 rows at a time, and each person can be emailed a confirmation with a code of their own. Tickets with a price are also on Pro, with no platform fee; the payment processor charges its own.', 'icon' => 'mail', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                ],
                'why_switch' => [
                    'intro' => 'Nothing you made in a generator has to be thrown away. The poster code keeps pointing where it points. What changes is that the ticket stops being a picture you made and becomes a record the door can ask.',
                    'points' => [
                        'The poster code: keep it if the address behind it still leads somewhere useful, or make a new one for the event page',
                        'Your guest list, typed in or imported from CSV on Pro, with a code for each row',
                        'The door: the same phones, opening a scanner page in place of the camera app',
                        'The tally: checked in or not is recorded per order at the moment of the scan',
                        'Free sign-ups, which get a code and a place on the list on every plan',
                    ],
                ],
                'switch_lead' => 'Start with the next event that has a door.',
                'switch_steps' => [
                    ['title' => 'Put the event on a schedule', 'description' => 'Create the event and turn on free registration with a limit, or ticket types with a price on Pro. From then on, every sign-up is emailed a code of its own.'],
                    ['title' => 'Repoint or reprint the poster code', 'description' => 'A dynamic code can be edited in your generator to open the new event page. A static one cannot, so keep the old page online with a link across, or make a new code from the event address for the next print run.'],
                    ['title' => 'Rehearse the door once', 'description' => 'Register yourself, sign in on the door phone, open Sales and tap Scan Ticket where the door will be. Check there is signal or wifi, and tell guests that the code in their confirmation email is the ticket.'],
                ],
                'faq' => [
                    ['question' => 'Can I use a free QR code generator to make event tickets?', 'answer' => 'You can make the codes, but not the check. A generator hands you an image; knowing that a code belongs to a paid order and has not been used takes a record that every scan is compared with. Without one, a screenshot of a single ticket admits as many people as receive it.'],
                    ['question' => 'What is the difference between a QR code on a poster and a QR code on a ticket?', 'answer' => 'A poster code is the same for everyone and opens a page. A ticket code belongs to one order and is looked up when scanned: is it paid, is it for this event and date, has it been used. The first is a link, and the second is a key to a record.'],
                    ['question' => 'Do I still need a QR code generator if I use Event Schedule?', 'answer' => 'Not for tickets, since a code is issued with each registration or sale. For print you may still want one. Event Schedule gives each schedule a plain QR image to download, and a generator is the better tool for a code in your colours with a logo, or one that points at something other than your schedule.'],
                    ['question' => 'What does scanning tickets cost?', 'answer' => 'Scanning tickets and free registrations at the door is on the Free plan, at '.plan_price(0).'. Putting a price on a ticket, the live check-in dashboard and importing a guest list are on Pro at '.plan_price($this->planPrice()).' a month. No platform fee is taken on ticket sales on any plan; the payment processor still charges its own fee.'],
                    ['question' => 'Do door staff need an app or a scanner gun?', 'answer' => 'Neither: the scanner is a page in the phone browser that uses the camera. It needs a connection for every scan, because each one is checked against a single shared record, and there is no offline mode. Door staff with a sign-in of their own are team members, which is Enterprise, up to five people.'],
                    ['question' => 'What happens when someone shows a screenshot of another person\'s ticket?', 'answer' => 'The first scan of that code admits the order. The next scan, from any phone at any door, shows already checked in with the time of the first one, and the person on the door decides what to do. A screenshot is fine for the real guest, and it opens with no signal.'],
                    ['question' => 'I already sent out codes from a generator. Can I move those guests across?', 'answer' => 'The old codes cannot be brought in, because they are images with no order behind them. The guests can: on Pro, import the list from CSV onto an event with ticket types, with Send Email on, and each person imported as paid receives a confirmation with a code that scans. Tell them the new email replaces the old one.'],
                ],
                'cross_links' => [
                    ['name' => 'Canva', 'route' => 'marketing.replace_canva', 'description' => 'The flyer the code gets printed on.'],
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms', 'description' => 'The sign-up form the poster code usually opens.'],
                    ['name' => 'Google Sheets', 'route' => 'marketing.replace_google_sheets', 'description' => 'The list that gets ticked at the door.'],
                ],
                'related_features' => [
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'A QR code on every ticket, with no platform fee on sales.'],
                    ['name' => 'Check-in', 'route' => 'marketing.check_in', 'description' => 'How to staff and run the door; scanning is free, the live dashboard is Pro.'],
                    ['name' => 'Event Graphics', 'route' => 'marketing.event_graphics', 'description' => 'A shareable image of your upcoming events, on every plan.'],
                ],
            ],
            'squarespace' => [
                'name' => 'Squarespace',
                'tagline' => 'Keep the website. Move the events off hand-built calendar entries and onto a schedule that repeats, takes sign-ups and sells tickets.',
                'meta_title' => 'Replace Squarespace for Event Pages | Event Schedule',
                'description' => 'How events are run on a Squarespace calendar page, where the copying and ticket workarounds begin, and how a schedule embeds in the same site.',
                'keywords' => 'Squarespace alternative for events, Squarespace replacement, event website builder, event landing page',
                'audience_hint' => 'For venues, studios and bands whose site is on Squarespace and whose events are entered one by one on a calendar page.',
                'about' => 'Squarespace is a website builder. Its calendar page, called an events page on version 7.0 sites, shows events as a list or a month, and each event has a title, a start and end time, a location with a map pin, an excerpt and a featured image. Organizers use it as the What\'s On page of their site and add registration with a form, a service product or an outside ticketing service.',
                'pricing_note' => 'Squarespace costs $16+/month for a basic site, plus extra for third-party ticketing. Event Schedule is free for event pages. Free registration is unlimited, and selling paid tickets is on Pro at '.plan_price($this->planPrice()).'/month, with zero platform fees.',
                'competitor_price' => 'From $16+/mo',
                'es_price' => 'From '.plan_price(0).'/mo (Pro '.plan_price($this->planPrice()).'/mo)',
                'comparison_rows' => [
                    ['feature' => 'A page per event with date, place and map', 'competitor' => true, 'es' => true],
                    ['feature' => 'Repeating events', 'competitor' => 'Duplicate each date', 'es' => 'One series'],
                    ['feature' => 'Free sign-up counted against a limit', 'competitor' => 'A form you add', 'es' => 'Free, limit per date'],
                    ['feature' => 'Tickets with a price', 'competitor' => 'Service product, Tock or Eventbrite', 'es' => 'On Pro'],
                    ['feature' => 'A ticket scanned at the door', 'competitor' => 'Only via an outside service', 'es' => 'Free scanning'],
                    ['feature' => 'Add to calendar links', 'competitor' => 'Google and iCal', 'es' => true],
                    ['feature' => 'Sits inside your Squarespace site', 'competitor' => 'Native', 'es' => 'Embed in a Code block'],
                    ['feature' => 'Block-by-block page design', 'competitor' => true, 'es' => 'Colours and fonts; CSS on Pro'],
                    ['feature' => 'The rest of the site: pages, blog, shop', 'competitor' => true, 'es' => 'Not a website builder'],
                    ['feature' => 'Events included in a site export', 'competitor' => false, 'es' => 'Calendar feed'],
                ],
                'tool_strengths' => [
                    'The whole site in one place: home page, about, blog, shop and events share one design and one navigation',
                    'Layout freedom: an event description is built from blocks, so it can hold a gallery, a video, a form or a product',
                    'Calendar and summary blocks that show upcoming events on the home page or any other page',
                    'Built-in commerce, so a workshop can be sold as a service product with its stock as the capacity',
                ],
                'keep_when' => [
                    'you host a handful of one-off events a year, and a title, a date and a paragraph is all each one needs',
                    'events are a small corner of a site that mainly sells products, shows a portfolio or runs a blog',
                    'you want every event page laid out by hand, differently each time',
                ],
                'pain_points' => [
                    'Your Thursday open mic runs every week. Calendar pages have no repeating events, so each Thursday is a duplicate you make and re-date by hand, and a new start time is an edit to every copy.',
                    'A workshop is sold as a service product with stock set to the number of places. That is a product to create beside every event, with a matching title and the date typed into its name.',
                    'The buyer of that product has a shop order, not a ticket. At the door you work down the orders list on a laptop, with nothing to scan.',
                    'For a ticketed show you send people to Eventbrite or Tock and list the event on the calendar page as well. Each event now lives in two systems, and a changed date has to be changed in both.',
                    'A free talk needs a headcount, so a form goes into the event description. The sign-ups arrive as form submissions, apart from the event, and someone has to keep count.',
                    'The day you want to move, the events are the part that stays behind: Squarespace\'s site export does not include calendar pages.',
                ],
                'gaps_lead' => 'A calendar page is a list of dated entries. It shows an event well and stops there: repeating it, counting places, issuing a ticket and checking it at the door fall to other blocks and other services.',
                'gaps_close' => 'Squarespace says as much in its own help pages, which send bookings to Acuity Scheduling and tickets to Tock or Eventbrite. It is a website builder doing what a website builder does.',
                'today' => [
                    'title' => 'Events on a Squarespace site, start to finish',
                    'intro' => 'Most Squarespace sites that list events are run the same way, by whoever also looks after everything else on the site.',
                    'steps' => [
                        ['title' => 'Add a calendar page', 'description' => 'It becomes the What\'s On page. You choose the list or the month view; on a version 7.0 site the same thing is called an events page.'],
                        ['title' => 'Enter each event', 'description' => 'Title, start and end time (an end time is required), the address, which drops a pin on a map, an excerpt and a featured image. The featured image is the thumbnail only, so the poster goes in a second time as an image block in the description.'],
                        ['title' => 'Duplicate for every repeat', 'description' => 'There is no repeating event. For a weekly class you open the event\'s menu, choose Duplicate and change the date, once for each week you want listed.'],
                        ['title' => 'Bolt on a way to sign up', 'description' => 'For a paid workshop, Squarespace\'s own guide is a service product per session, stock set to the number of places, and a product block in the event description. For ticketed events it points to Tock or Eventbrite, with a link from the event.'],
                        ['title' => 'Turn on the calendar links', 'description' => 'Show Export Links adds Google Calendar and iCal links to each event. Times are shown in the site\'s time zone.'],
                        ['title' => 'Run the night from a list', 'description' => 'Who is coming is a list of shop orders, form submissions or the other service\'s attendee report. Afterwards the event drops into past events, and the product behind it stays in the store until you take it down.'],
                    ],
                    'outro' => 'The page looks right, because Squarespace is good at pages. The event itself is spread over a calendar entry, a product or an outside ticket page, and whichever list you check names against.',
                ],
                'native_lead' => 'The site stays where it is. The events move to a schedule that sits inside it.',
                'es_solutions' => [
                    ['title' => 'An event page with nothing to assemble', 'description' => 'Enter the event once and its page has the flyer, the date and time, the venue with a map, add to calendar buttons and a register or buy button. Free on every plan. On the Free plan the public pages carry Event Schedule branding, which Pro removes.', 'icon' => 'globe', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'A weekly night is one event', 'description' => 'A recurring event repeats daily, weekly on chosen days, every few weeks, monthly or yearly, with single dates added or skipped. You edit the series, not fifty-two copies. Free on every plan.', 'icon' => 'calendar', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'Sign-ups and tickets on the event itself', 'description' => 'Free registration counts places against a limit for each date and is unlimited on every plan. Ticket types with a price are on Pro, paid into your own Stripe, PayPal or other connected account with no platform fee; the processor still charges its own. There is no product to keep in step with the event.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'The calendar goes in a Code block', 'description' => 'Copy the Iframe Code from the Embed dialog and paste it into a Code block on your Squarespace page. On the Free plan the snippet carries a small Powered by Event Schedule line under the frame. It shows your live schedule, so a date you move is already right on the site. The calendar embed is free; the widget that sells tickets inside your page is on Pro. Which Squarespace plans allow an iframe in a Code block is Squarespace\'s decision, so check yours.', 'icon' => 'code', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'A ticket that scans', 'description' => 'Each registration and each ticket is emailed with a QR code of its own. A phone browser scans it at the door on any plan, and an order admits once. The live check-in dashboard is on Pro.', 'icon' => 'qr', 'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30', 'border' => 'border-cyan-200 dark:border-cyan-500/20', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                    ['title' => 'Your look, within limits', 'description' => 'Colours, fonts, backgrounds and a header image are free. Custom CSS and removing Event Schedule branding are on Pro, and your own domain, such as events.yourdomain.com, is on Enterprise. It is not a page builder: there are no free-form layouts, and no blog or shop.', 'icon' => 'image', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                ],
                'why_switch' => [
                    'intro' => 'You are not replacing the website. You are replacing the calendar page and the workarounds hung off it, and leaving the home page, the blog and the shop exactly where they are.',
                    'points' => [
                        'Upcoming events, entered once each: paste the text or a link and the details are read for you, within a daily limit',
                        'Repeating events, which collapse from many duplicates into one series',
                        'Workshop places, which become registration or tickets on the event in place of a service product',
                        'The What\'s On page, which keeps its address and its spot in the navigation and now holds the embed',
                        'Email sign-ups, which can stay in Squarespace or be collected on the schedule as well',
                    ],
                ],
                'switch_lead' => 'Nothing imports from a calendar page, so move forward from the next date and leave the archive where it is.',
                'switch_steps' => [
                    ['title' => 'Re-enter what is coming up', 'description' => 'Squarespace\'s export leaves calendar pages out, so copy each upcoming event across by hand, or paste its page link into the import and check what was read. Make each repeating event once, as a series.'],
                    ['title' => 'Swap the calendar page for the embed', 'description' => 'On your What\'s On page, put a Code block holding the iframe tag from the Embed dialog where the event list was. Keep the page address and the navigation label, so links and search results still land there. The old calendar page can move to Not linked if you want to keep the archive.'],
                    ['title' => 'Retire the stand-ins', 'description' => 'Once their dates have passed, unpublish the service products that stood in for workshop places and remove the sign-up forms. Tell your list that sign-up now happens on the event page and that the confirmation email is the ticket.'],
                ],
                'faq' => [
                    ['question' => 'Can I keep my Squarespace site and move only the events?', 'answer' => 'That works. The site stays on Squarespace, and the calendar goes into a Code block as an iframe that shows your live schedule. A click on an event opens its page in a new tab, where people register or buy.'],
                    ['question' => 'Does Squarespace have recurring events?', 'answer' => 'Not on calendar pages: its help centre says repeating events are not supported and suggests duplicating an event instead. On Event Schedule a recurring event is one entry that repeats daily, weekly, monthly or yearly, with single dates added or skipped, on every plan.'],
                    ['question' => 'How do people sell event tickets on Squarespace today?', 'answer' => 'By one of two routes. For workshops, Squarespace\'s guide is a service product for each session, with stock as the capacity and a product block in the event description. For ticketed events it suggests Tock or Eventbrite, with the event promoted on a calendar page. Either way the ticket and the event are separate things to keep in step.'],
                    ['question' => 'Is Event Schedule a website builder?', 'answer' => 'No. It gives you a schedule page, a page for each event and a calendar you can embed, and that is the extent of it: no free-form pages, no blog, no shop. If you need a full site, keep Squarespace for that and use the schedule for the events part.'],
                    ['question' => 'What does it cost on top of a Squarespace plan?', 'answer' => 'If you keep the site it is an addition, not a swap. Event pages, the calendar embed, recurring events, free registration and door scanning are on the Free plan at '.plan_price(0).'. Tickets with a price, the ticket widget and pages without our branding are on Pro at '.plan_price($this->planPrice()).' a month, and a custom domain is on Enterprise at '.plan_price($this->planPrice(true)).' a month. No platform fee is taken on sales; your payment processor charges its own.'],
                    ['question' => 'What does Squarespace still do better?', 'answer' => 'Design, and everything that is not an event. You lay out each page block by block, and the events sit in the same navigation and style as the rest of the site. An embedded calendar is a frame whose height you set yourself, and it arrives in the schedule\'s own colours and fonts, which you match to the site by hand.'],
                    ['question' => 'Can my existing Squarespace events be imported?', 'answer' => 'There is no importer for a calendar page, and Squarespace\'s own site export does not include calendar pages. Upcoming events are entered again: paste an event\'s text or its link and the details are read for you, within a daily limit, then check them. Moving only the coming dates, and leaving past events on the old page, is the short route.'],
                ],
                'cross_links' => [
                    ['name' => 'Linktree', 'route' => 'marketing.replace_linktree', 'description' => 'The link in your bio, listing every date.'],
                    ['name' => 'Canva', 'route' => 'marketing.replace_canva', 'description' => 'The image you post for each week of events.'],
                    ['name' => 'Mailchimp', 'route' => 'marketing.replace_mailchimp', 'description' => 'The email that tells your list what is on.'],
                ],
                'related_features' => [
                    ['name' => 'Embed Calendar', 'route' => 'marketing.embed_calendar', 'description' => 'One iframe tag that puts your live calendar on a Squarespace page.'],
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Ticket types with a price on Pro, with no platform fee.'],
                    ['name' => 'PayPal', 'route' => 'marketing.paypal', 'description' => 'Take ticket payments through your own PayPal account.'],
                ],
            ],
            'notion' => [
                'name' => 'Notion',
                'tagline' => 'A published Notion calendar shows people your events. Registering, paying and saving the date all happen somewhere else.',
                'meta_title' => 'Replace Notion for Event Planning | Event Schedule',
                'description' => 'What a published Notion events database can and cannot do for visitors, and how to move the public calendar while the planning stays put.',
                'keywords' => 'Notion alternative for events, Notion replacement, Notion event planning, event management workspace',
                'audience_hint' => 'For collectives, studios and communities whose public events page is a Notion database published to the web.',
                'about' => 'Notion is a workspace of pages and databases. Event people use it in two ways: privately, as the planning wiki, and publicly, as an events database with a calendar view that is published to the web and linked from a bio. This page is about the second use. The first is something Notion does well and Event Schedule does not do at all.',
                'pricing_note' => 'Notion Plus costs $10/month per user. Event Schedule is free for unlimited events and public pages. Free registration is unlimited on every plan, and selling paid tickets is on Pro at '.plan_price($this->planPrice()).'/month. Nothing is charged per user either way: a team of up to five is the Enterprise plan, not five seats.',
                'competitor_price' => 'From $10/mo per user',
                'es_price' => 'From '.plan_price(0).'/mo (Pro '.plan_price($this->planPrice()).'/mo)',
                'comparison_rows' => [
                    ['feature' => 'Public page listing your events', 'competitor' => 'Published database', 'es' => true],
                    ['feature' => 'Calendar and list views for visitors', 'competitor' => true, 'es' => true],
                    ['feature' => 'Sign-up that closes at a set number', 'competitor' => 'Form, closed by hand', 'es' => 'Free registration'],
                    ['feature' => 'Selling tickets', 'competitor' => false, 'es' => 'On Pro, zero platform fees'],
                    ['feature' => 'Visitors save an event to their calendar', 'competitor' => false, 'es' => 'Per event, or a live feed'],
                    ['feature' => 'Sync with Google, Outlook and CalDAV', 'competitor' => 'Dates stay in Notion Calendar', 'es' => 'Two-way'],
                    ['feature' => 'Repeating events', 'competitor' => 'Repeating template', 'es' => 'One event with a rule'],
                    ['feature' => 'Email when new events are published', 'competitor' => false, 'es' => 'Automatic digest'],
                    ['feature' => 'Docs, tasks and wikis around the event', 'competitor' => true, 'es' => 'Not a planning tool'],
                    ['feature' => 'Several editors', 'competitor' => true, 'es' => 'On Enterprise, up to 5'],
                ],
                'tool_strengths' => [
                    'One database shown as a table, a board, a timeline, a calendar, a list or a gallery, each view with filters of its own',
                    'Every event is a full page, so the running order, the rider and the budget can sit inside the event they belong to',
                    'Database templates that lay out a new event in one click, and can repeat daily, weekly, monthly or yearly',
                    'Publishing is a switch: Share, then Publish, and the page with its subpages is on the web and updates as you edit',
                ],
                'keep_when' => [
                    'the page is for your team or your members, and nobody has to register or pay',
                    'the events are one corner of a larger published site, such as a handbook or a community wiki, and belong inside it',
                    'what you need is the planning side: tasks, owners, documents and deadlines around the date',
                ],
                'pain_points' => [
                    'Someone opens your published calendar on a phone, finds Thursday\'s workshop and looks for the button. The event is a document, and the way to sign up is whichever link you remembered to paste into it.',
                    'A Notion form takes answers from anyone with the link, but it is either open or closed. Holding a class to twelve means watching the Responses view and setting access to No access yourself.',
                    'The dates in your database show up in Notion Calendar, for you. Notion\'s help says a database can be viewed there and not in Google or iCloud calendar, so a visitor who wants your event in theirs types it in.',
                    'You move the venue. The published page changes the moment you edit it, and nobody who read it last week is told.',
                    'Payment is a link to another service, kept in a URL property or the page body. Who has paid is known to that service and not to the row.',
                    'A weekly class built on a repeating template is a new row every week, so the series is as many separate pages as there have been weeks, each with a link of its own.',
                ],
                'gaps_lead' => 'Publishing a database makes it visible. It does not make it an events page, and the gap opens at the moment a visitor wants to do something.',
                'gaps_close' => 'Notion publishes documents and databases faithfully, which is all it says it does. Registration, tickets and calendars for the public are a different product.',
                'today' => [
                    'title' => 'A public events page on Notion, start to finish',
                    'intro' => 'This is the usual build, give or take a property. Anyone who has made one will recognise the order.',
                    'steps' => [
                        ['title' => 'The Events database', 'description' => 'A database with a Date property, a Select for the type or the venue, a Status, a URL property for the sign-up link and a cover image. Each row opens as a page, and the description is written there.'],
                        ['title' => 'Views for the public', 'description' => 'A Calendar view for the month, a List or Gallery view filtered to upcoming dates, sometimes a Board by type. A filter on Status keeps anything not yet confirmed out of sight, which is how drafts are handled.'],
                        ['title' => 'Publish', 'description' => 'Share, then Publish. The page and all of its subpages go live, visitors can switch between the views and open each event, and the Discoverable on the web toggle decides whether search engines may list it.'],
                        ['title' => 'Sign-ups through a form', 'description' => 'A form connected to a Registrations database, open to anyone on the web with the link, with a question that says which event. Answers land in its Responses view. Registration closes when you set the form to No access.'],
                        ['title' => 'Money and tickets elsewhere', 'description' => 'For a paid event the URL property points at a payment link or a ticketing site. Who paid is checked over there and, on a careful day, copied back into the row.'],
                        ['title' => 'Telling people', 'description' => 'The page link goes in the bio. The published page sends nothing, so each new event is announced by hand in whatever channel you use, and a changed date is announced a second time.'],
                    ],
                    'outro' => 'The result looks good. It is also a brochure: whatever a visitor does after reading it happens in another tool, and the join between the two is you.',
                ],
                'native_lead' => 'The same public calendar, with the buttons the visitor was looking for.',
                'es_solutions' => [
                    ['title' => 'A calendar made for visitors', 'description' => 'Your schedule is a public calendar at its own address, with a month view, a list, and a panel to search and filter. Each event has a page that sets out its date, venue, image and description. Free, with unlimited events.', 'icon' => 'globe', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Register on the event page', 'description' => 'Free registration sits on the event itself, with an optional capacity that closes sign-up at your number and a waitlist behind it. Unlimited on every plan. Adding your own questions to the form is on Pro.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'Into their calendar, and yours', 'description' => 'A visitor adds one event to their calendar or subscribes to the schedule\'s live feed, which follows a date when it moves. On your side, two-way sync with Google Calendar, Outlook and CalDAV is free and is set up by the schedule\'s owner.', 'icon' => 'calendar', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'Read in, not retyped', 'description' => 'Paste the rows of your Events database, or a screenshot of its calendar view, into the import box. The events come back as a list with name, date, venue and description filled in, and you tick the ones to add. On Free and Pro a schedule gets 50 of these reads a day.', 'icon' => 'ai', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'New events announce themselves', 'description' => 'Visitors follow the schedule or leave an email address, and confirmed subscribers get an automatic digest when you publish new events. For anything else there is a newsletter builder, whose monthly allowance counts recipients: 10 on Free, 100 on Pro, 1,000 on Enterprise.', 'icon' => 'mail', 'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30', 'border' => 'border-cyan-200 dark:border-cyan-500/20', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                    ['title' => 'Sub-schedules for filtered views', 'description' => 'Split one schedule into strands such as Workshops, Socials and Talks. A sub-schedule has a color, an address of its own and a filter button on the calendar. It sorts events and never hides them: a draft is hidden by the event\'s own visibility.', 'icon' => 'layout', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                ],
                'why_switch' => [
                    'intro' => 'Leave the wiki where it is. The part that moves is the page the public sees, and it moves without being rebuilt row by row.',
                    'points' => [
                        'The Events database: pasted in as text or a screenshot, and reviewed before anything is added',
                        'The filtered views: sub-schedules, categories and the visitor\'s own filter panel',
                        'The sign-up form: free registration with a capacity, on the event page',
                        'The repeating template: one recurring event with a rule such as the 2nd Tuesday, with single dates added or skipped',
                        'The link in your bio: one schedule address, or your own domain on Enterprise',
                    ],
                ],
                'switch_lead' => 'The database can stay in Notion as your planning record. Only the published half changes address.',
                'switch_steps' => [
                    ['title' => 'Take the upcoming rows out', 'description' => 'Filter a table view to upcoming events and export it as Markdown and CSV from the page menu. Paste the contents of the CSV into the import box on a free schedule, up to 10,000 characters at a time. Events come back as a list to tick, and nothing is public until you add it.'],
                    ['title' => 'Turn views into sub-schedules', 'description' => 'Make a sub-schedule or a category for each Select value you used to filter by. Switch on registration for the events that had a form and set the capacity. For a paid event, add a priced ticket on Pro in place of the payment link.'],
                    ['title' => 'Point the old page at the new one', 'description' => 'Replace the published database with a line of text and a link to your schedule, or unpublish it, and change the link in your bio. Tell your audience they can now follow the schedule or subscribe to its calendar, which the old page had no way to offer.'],
                ],
                'faq' => [
                    ['question' => 'Can I keep planning in Notion and publish with Event Schedule?', 'answer' => 'That split works well. Notion keeps the tasks, documents and budgets, and Event Schedule carries the public calendar, registration and tickets. On Pro the REST API and webhooks let a script or an automation tool write a sale or a new event back to a Notion database, though there is no ready-made Notion connector.'],
                    ['question' => 'What does Notion still do better?', 'answer' => 'The whole planning side. Event Schedule has no documents, no task lists and no relations between databases, and custom fields on events are a Pro feature. A Notion page can also be laid out however you like, where an event page here keeps one structure and takes your accent color, font and images.'],
                    ['question' => 'Can visitors add my events to Google Calendar from a published Notion page?', 'answer' => 'Not from the published page. Notion\'s help says a database can be viewed in Notion Calendar and not in Google or iCloud calendar. On Event Schedule an event page has an Add to Calendar menu, and the schedule has a live feed that a calendar app can subscribe to.'],
                    ['question' => 'How do I move my events out of a Notion database?', 'answer' => 'Export the view as CSV and paste its contents into the import box, or add a screenshot of the view. One read can return several events for review, and nothing is added until you tick it. The longer notes inside each page stay in Notion, which is usually where they belong.'],
                    ['question' => 'Do I pay for each team member?', 'answer' => 'No plan is priced by the seat. Free is '.plan_price(0).' with one login per schedule, Pro is '.plan_price($this->planPrice()).' a month and adds priced tickets, and Enterprise is '.plan_price($this->planPrice(true)).' a month and carries up to five team members as admins or viewers. If more than five people edit your Notion database today, that ceiling is the thing to weigh.'],
                    ['question' => 'Can I hide draft events the way a filtered view does?', 'answer' => 'Every plan has two states, Public and Draft, and a draft is seen only by the schedule\'s own people. Unlisted events, reached by a direct link with an optional password, and Internal events for the team are on Enterprise. A sub-schedule is not a way to hide anything.'],
                    ['question' => 'Can I put the calendar on my own website?', 'answer' => 'The snippet from the Embed dialog places the live calendar on a site that accepts an iframe, free on every plan, and it updates when your events change. The ticket purchase form can be embedded as well, on Pro.'],
                ],
                'cross_links' => [
                    ['name' => 'Trello', 'route' => 'marketing.replace_trello', 'description' => 'When each event is a card on a board, not a row in a database.'],
                    ['name' => 'Google Sheets', 'route' => 'marketing.replace_google_sheets', 'description' => 'When the events list and the guest list are tabs in a spreadsheet.'],
                    ['name' => 'Calendly', 'route' => 'marketing.replace_calendly', 'description' => 'When the thing to publish is bookable time slots, not dated events.'],
                ],
                'related_features' => [
                    ['name' => 'Team Scheduling', 'route' => 'marketing.team_scheduling', 'description' => 'Admins and viewers on one schedule, up to five on Enterprise.'],
                    ['name' => 'AI Import', 'route' => 'marketing.ai', 'description' => 'How pasted text, a link or an image becomes events you review.'],
                    ['name' => 'Calendar Sync', 'route' => 'marketing.calendar_sync', 'description' => 'Two-way sync with Google Calendar, Outlook and CalDAV, on every plan.'],
                ],
            ],
            'trello' => [
                'name' => 'Trello',
                'tagline' => 'A card can plan an event. It cannot publish it, take a registration for it or sell a ticket to it.',
                'meta_title' => 'Replace Trello for Event Management | Event Schedule',
                'description' => 'How events are run as cards on a Trello board, where the board stops at the public, and what to move off it without giving up the planning.',
                'keywords' => 'Trello alternative for events, Trello replacement, Trello event planning, event management board',
                'audience_hint' => 'For venue bookers, promoters and committees who track each event as a card moving from Ideas to Confirmed to Done.',
                'about' => 'Trello is a kanban board: lists from left to right, and cards that move between them. Event teams tend to keep one card per event, with a due date for the night and a checklist of everything to do before it, or one board for a large event with a card per task. It is a planning tool and a good one. Event Schedule is not a planning tool: it has no tasks, no checklists and no board.',
                'pricing_note' => 'Trello Standard costs $5 per user a month billed annually ($6 monthly). Event Schedule is free for unlimited events and public pages. Free registration is unlimited, and selling paid tickets is on Pro at '.plan_price($this->planPrice()).'/month, and nothing here is charged per user; a team of five is the Enterprise plan rather than five subscriptions.',
                'competitor_price' => 'From $5/mo per user',
                'es_price' => 'From '.plan_price(0).'/mo (Pro '.plan_price($this->planPrice()).'/mo)',
                'comparison_rows' => [
                    ['feature' => 'Tasks and checklists before the event', 'competitor' => true, 'es' => 'Not a task tool'],
                    ['feature' => 'Public page for each event', 'competitor' => 'Public board, view only', 'es' => true],
                    ['feature' => 'Open to search engines', 'competitor' => false, 'es' => true],
                    ['feature' => 'Registration with a capacity limit', 'competitor' => false, 'es' => 'Free'],
                    ['feature' => 'Ticket sales', 'competitor' => false, 'es' => 'On Pro, zero platform fees'],
                    ['feature' => 'Events on a calendar', 'competitor' => 'Cards with due dates', 'es' => 'Public calendar'],
                    ['feature' => 'Google, Outlook and CalDAV', 'competitor' => 'iCalendar feed of due dates', 'es' => 'Two-way sync'],
                    ['feature' => 'Email to your audience', 'competitor' => false, 'es' => 'Digest and newsletters'],
                    ['feature' => 'Guest list and door check-in', 'competitor' => false, 'es' => 'QR scan in a phone browser'],
                    ['feature' => 'Team members', 'competitor' => true, 'es' => 'On Enterprise, up to 5'],
                ],
                'tool_strengths' => [
                    'The whole season on one screen: a list per stage, a card per event, and dragging the card is the status update',
                    'A checklist inside each card for the jobs before the night, and template cards so every new event starts from the same one',
                    'Automation and Power-Ups that move cards and connect other tools without anyone writing code',
                    'A Calendar Power-Up that sets cards with due dates on a week or a month, with an iCalendar feed for your own calendar app',
                ],
                'keep_when' => [
                    'the work is the preparation: booking the room, chasing the rider, ordering the banner',
                    'the event is internal, and nobody outside the team has to find it, sign up or pay',
                    'a large event wants a board to itself with a card per task. Keep that board, and publish the event from a schedule',
                ],
                'pain_points' => [
                    'The card reaches Confirmed and nothing happens outside the board. Someone still writes the event up for the website, the socials and the ticket page, copying the date off the card each time.',
                    'You make the board public so people can see what is on. They get lists to scroll through, and Trello\'s help says public boards are not indexed by search engines, so nobody arrives by searching.',
                    'The due date stands in for the show. Start date and duration are only available within Trello, so in an outside calendar the night arrives as a single moment with no end.',
                    'The band emails a later start. You change the card. The website, the ticket page and the poster still say eight.',
                    'A card has no place for a stranger to register, so sign-up is a link in the description to some other form, and the head count comes back to the board only if someone types it into a comment.',
                    'After the night the card is dragged to Done and archived. It was the only record of the event, and it never held a list of who came.',
                ],
                'gaps_lead' => 'A board tracks work your team does. An event also has an audience, and the audience never sees the board.',
                'gaps_close' => 'Trello makes no claim to be a box office or a listings page. It is a board for work, and the public half of an event sits outside it.',
                'today' => [
                    'title' => 'An event on a Trello board, from card to doors',
                    'intro' => 'One card per event is the common setup for a venue or a series. This is the life of that card.',
                    'steps' => [
                        ['title' => 'A card in Ideas', 'description' => 'Someone adds a card with the act or a working title. It gathers comments, a few links and a rough month.'],
                        ['title' => 'A date and a checklist', 'description' => 'Once the night is agreed the card gets a due date and a checklist from the template card: contract, deposit, artwork, listing, tickets, sound check. The date is set by hand, since a template card does not carry dates over. The card moves to Confirmed.'],
                        ['title' => 'The calendar', 'description' => 'With the Calendar Power-Up enabled, dated cards sit on a month grid, and its iCalendar feed shows those due dates in the team\'s own calendar apps. It is the nearest the board comes to a program of events, and only the team sees it.'],
                        ['title' => 'Publishing by copy and paste', 'description' => 'Three of the checklist items are really publishing: write the listing, open the tickets, post the graphic. Each is done in a different tool, with the title, date and price retyped from the card.'],
                        ['title' => 'Changes', 'description' => 'When something moves, the card is edited first and the checklist grows: update the website, update the ticket page, tell the buyers. Whether those happen depends on who opens the card.'],
                        ['title' => 'Doors, then Done', 'description' => 'On the night the guest list comes from wherever the tickets were sold. Next morning the card is dragged to Done.'],
                    ],
                    'outro' => 'The board knew everything about the event and could tell nobody outside the team. Each public fact was copied out of a card by hand, and each change had to be copied out again.',
                ],
                'native_lead' => 'What the Confirmed list was waiting for: the event published once, in the place the public and the calendars read it from.',
                'es_solutions' => [
                    ['title' => 'Confirmed means published', 'description' => 'Add the event to your schedule and it has a public page and a square on your calendar. Hold it as a Draft until the contract is signed, then set it to Public. Events are unlimited on the free plan.', 'icon' => 'globe', 'gradient' => 'from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border' => 'border-emerald-200 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_color' => 'text-emerald-600 dark:text-emerald-400'],
                    ['title' => 'Registration and tickets on the event', 'description' => 'Free registration with a capacity limit is on every plan. Priced tickets are on Pro, paid through your own Stripe, PayPal, Payfast, Razorpay or Invoice Ninja account, a payment link or cash. Event Schedule adds no platform fee, and the processor\'s own fee still applies.', 'icon' => 'ticket', 'gradient' => 'from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-500/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20', 'icon_color' => 'text-blue-600 dark:text-blue-400'],
                    ['title' => 'One edit, every copy', 'description' => 'Change the start time on the event and the page, the calendar embedded on your website, the feed people subscribed to and a connected Google, Outlook or CalDAV calendar carry the new time. Two-way sync is free, and the schedule\'s owner sets it up.', 'icon' => 'calendar', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border' => 'border-amber-200 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_color' => 'text-amber-600 dark:text-amber-400'],
                    ['title' => 'A queue for incoming events', 'description' => 'Turn on Accept requests and bands, promoters or neighbors send an event through a public form. Each waits on the Requests tab until you accept or decline it: an Ideas list where the person asking does the typing. Free, and your own questions on the form are on Pro.', 'icon' => 'clipboard', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                    ['title' => 'A guest list that exists', 'description' => 'Each registration and sale is on the Sales page, and each ticket carries a QR code read at the door from a phone browser, free on every plan. The live check-in dashboard is on Pro. The morning after, you know who came.', 'icon' => 'qr', 'gradient' => 'from-cyan-50 to-sky-50 dark:from-cyan-900/30 dark:to-sky-900/30', 'border' => 'border-cyan-200 dark:border-cyan-500/20', 'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'icon_color' => 'text-cyan-600 dark:text-cyan-400'],
                    ['title' => 'A team, at one plan', 'description' => 'Free and Pro have a single login per schedule. Enterprise carries up to five team members: an admin adds events, answers requests and sees sales, and a viewer browses the calendar and scans tickets. Nothing is priced by the seat.', 'icon' => 'globe', 'gradient' => 'from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border' => 'border-sky-200 dark:border-sky-500/20', 'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20', 'icon_color' => 'text-sky-600 dark:text-sky-400'],
                ],
                'why_switch' => [
                    'intro' => 'Nothing about the board has to change. What goes away is the copying between the board and the outside world.',
                    'points' => [
                        'Dated cards: read in as events from pasted text, a screenshot of the board or a calendar feed, and reviewed before any is added',
                        'The "write the listing" item: the event page is the listing',
                        'The "open the tickets" item: registration or tickets on that same page',
                        'The "post the graphic" item: a shareable image of upcoming events, generated from the schedule on every plan',
                        'The update chores after a change: one edit, carried to the page, the embed, the feed and the synced calendar',
                    ],
                ],
                'switch_lead' => 'Start with the Confirmed list. An idea can stay a card until it is real.',
                'switch_steps' => [
                    ['title' => 'Read the Confirmed list in', 'description' => 'If the board uses the Calendar Power-Up, copy its iCalendar feed address and paste it on the import page of a free schedule: a calendar link is read directly, and upcoming entries come back as a list to tick. Otherwise paste the card titles and dates as text, or add a screenshot. Check every time, because a due date has no end.'],
                    ['title' => 'Shorten the template checklist', 'description' => 'Swap the listing, ticket and graphic items on your template card for one: publish on the schedule. When the event page exists, put its link on the card, so the board points at the public version and stops being the source of it.'],
                    ['title' => 'Change the public links', 'description' => 'If a public board was how people saw what is on, put your schedule address in its place and embed the calendar on your website. Tell your audience they can follow the schedule or subscribe to its calendar feed, which a board never gave them.'],
                ],
                'faq' => [
                    ['question' => 'Can I keep using Trello alongside Event Schedule?', 'answer' => 'Running both is the sensible setup. Trello holds the tasks, and Event Schedule holds the published event, the registrations and the tickets. On Pro, webhooks fire on sales, events and check-ins and the REST API can create events, so an automation tool or a short script can tie a card to its event. There is no ready-made Trello Power-Up.'],
                    ['question' => 'What does Trello still do better?', 'answer' => 'Planning. Event Schedule has no boards, no task cards, no checklists and nobody to assign a job to. It does not manage volunteers, speakers or exhibitors either. When the hard part of your event is the preparation, that work stays on the board.'],
                    ['question' => 'Does Event Schedule have a kanban or pipeline view of events?', 'answer' => 'No. Events appear on a calendar and in lists, and every plan has two states for them, Draft and Public. The nearest thing to a pipeline is the Requests tab, where submitted events wait until you accept or decline each one.'],
                    ['question' => 'How do I get my events off the board and onto a schedule?', 'answer' => 'Paste them. The import box reads pasted text, an image such as a screenshot of the board, or a link to a calendar feed, and returns the events as a list for you to review. One link read brings back up to 100 events, and an import can be undone for 24 hours.'],
                    ['question' => 'What does it cost compared with paying per member?', 'answer' => 'Plans are flat. Free is '.plan_price(0).', Pro is '.plan_price($this->planPrice()).' a month for priced tickets and the API, and Enterprise is '.plan_price($this->planPrice(true)).' a month for up to five team members and your own domain. The catch for a large team is that five is the ceiling. Ticket sales carry no platform fee, only the payment processor\'s.'],
                    ['question' => 'Can my whole committee see the events in their own calendars?', 'answer' => 'Anyone can subscribe to the schedule\'s public calendar feed without an account, so a committee of any size can follow what is published. Team members on Enterprise can also send the schedule\'s events to a Google calendar of their own. It is editing that is limited to the team.'],
                    ['question' => 'Can performers or partners send me events the way they would add a card?', 'answer' => 'With Accept requests on, anyone can submit an event from your schedule page without being invited to a board. By default each submission waits for your approval, and schedules you name as approved skip the queue. This is free on every plan; adding your own questions to the form needs Pro.'],
                ],
                'cross_links' => [
                    ['name' => 'Notion', 'route' => 'marketing.replace_notion', 'description' => 'When the events live in a published database instead of on a board.'],
                    ['name' => 'Doodle', 'route' => 'marketing.replace_doodle', 'description' => 'When the card started life as a poll to find the date.'],
                    ['name' => 'Google Forms', 'route' => 'marketing.replace_google_forms', 'description' => 'The sign-up form that the card\'s description links to.'],
                ],
                'related_features' => [
                    ['name' => 'Team Scheduling', 'route' => 'marketing.team_scheduling', 'description' => 'What an admin and a viewer can each do, on Enterprise.'],
                    ['name' => 'Ticketing', 'route' => 'marketing.ticketing', 'description' => 'Priced tickets on Pro, with a QR code scanned at the door.'],
                    ['name' => 'AI Import', 'route' => 'marketing.ai', 'description' => 'Turning pasted card titles, a screenshot or a feed link into events.'],
                ],
            ],
        ];

        return array_merge($tools[$tool], [
            'githubStars' => GitHubUtils::getStars(),
        ]);
    }

    /**
     * Examples page: the demo schedules, on two walls of their own pictures
     */
    public function demos()
    {
        $categories = ExampleSchedules::byCategory();
        $allSchedules = collect($categories)->flatten(1);
        // What app:generate-example-shots wrote: the schedules' pictures at the size the wall
        // uses, and the photographs of their pages. Empty on an install that has not run it.
        $pictures = ExampleSchedules::shots();

        return view('marketing.demos', [
            'categories' => $categories,
            'scheduleCount' => $allSchedules->count(),
            'allSchedules' => $allSchedules->toArray(),
            'walls' => ExampleSchedules::walls($categories, $pictures['art']),
            'art' => $pictures['art'],
            'shots' => $pictures['shots'],
            'xray' => $pictures['xray'],
            'shotsDate' => $pictures['taken'],
        ]);
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

            $excludeCountry = strtolower(trim((string) config('app.search_exclude_country', '')));

            $schedules = Role::where(function ($q) use ($escapedQuery) {
                $q->where('subdomain', 'like', $escapedQuery.'%')
                    ->orWhere('name', 'like', '%'.$escapedQuery.'%')
                    ->orWhere('city', 'like', '%'.$escapedQuery.'%')
                    ->orWhere('short_description', 'like', '%'.$escapedQuery.'%');
            })
                ->where($this->publicScheduleFilter())
                // Explicit, because demoContent() below is null-safe by necessity and so no longer
                // filters these rows out as a side effect of NULL propagation, the way the
                // `subdomain != ...` pair it replaced did. A schedule with no subdomain has no
                // guest URL, so a result card for it would link nowhere.
                ->whereNotNull('subdomain')
                ->whereNot(fn ($q) => $q->demoContent())
                // A schedule a platform admin hid from discovery is not suggested here either.
                ->whereNot(fn ($q) => $q->hiddenFromDiscovery())
                ->when($excludeCountry !== '', function ($q) use ($excludeCountry) {
                    // strict: only schedules with a known country other than the excluded one
                    $q->whereNotNull('country_code')
                        ->where('country_code', '!=', '')
                        ->whereRaw('LOWER(country_code) != ?', [$excludeCountry]);
                })
                ->orderBy('name')
                ->limit(12)
                ->get();

            // Spread like the other discovery surfaces. It can never hide a match: the pool is
            // drawn from the SAME LIKE, so a lookup that only one schedule answers still returns
            // every one of its events, just with the near-duplicates moved down the list.
            $events = DiscoveryUtils::spread(
                DiscoveryUtils::candidates(
                    $this->publicUpcomingEventsQuery()
                        ->where(function ($q) use ($escapedQuery) {
                            $q->where('name', 'like', '%'.$escapedQuery.'%')
                                ->orWhere('short_description', 'like', '%'.$escapedQuery.'%');
                        })
                        ->where('is_hidden_from_discovery', false)
                        // Same order as the homepage wall: dated events from today first, soonest first, then the
                        // recurring series whose starts_at is the date the series BEGAN (already past), then
                        // undated rows. A plain starts_at sort put a weekly night that started last year ahead
                        // of next week's one-offs, contradicting the /search page's own "soonest first".
                        // The id tiebreak makes the spread's pick deterministic when events share a start.
                        ->orderByRaw('CASE WHEN starts_at >= ? THEN 0 ELSE 1 END, starts_at IS NULL, starts_at ASC, events.id ASC', [Carbon::today()]),
                    12
                ),
                12
            );
        }

        return view('marketing.search', [
            'query' => $query,
            'searched' => strlen($query) >= 2,
            'schedules' => $schedules,
            'events' => $events,
        ]);
    }

    /**
     * Browse page - highlights upcoming public events across the platform.
     */
    public function browse(Request $request)
    {
        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        $now = Carbon::now('UTC');

        // One poster for each schedule: the event it has on next (BrowseWall). $more names the
        // schedules that have something else coming up as well.
        [$events, $more] = $this->browseWall($now);

        // Admins also see hidden events so they can restore them. Deliberately NOT spread and
        // NOT widened: this is a moderation list, where demoting an event past the 50th row
        // would put it out of reach of the person whose job is to find it.
        $hiddenEvents = $isAdmin
            ? $this->publicUpcomingEventsQuery()
                ->where('is_hidden_from_discovery', true)
                ->orderByRaw('starts_at IS NULL, starts_at ASC')
                ->limit(50)
                ->get()
            : collect();

        // And the schedules hidden whole ("Hide schedule"), newest first, each with its Unhide.
        $hiddenSchedules = $isAdmin
            ? DiscoveryHiddenSchedule::with('role')->latest()->latest('id')->limit(100)->get()->filter(fn ($row) => $row->role)
            : collect();

        // Federated listings render as their own labelled section rather than being
        // interleaved: every discovery query here ends in a single Eloquent builder
        // with its own ordering and limit, so mixing a second corpus in would need a
        // UNION or a merge-and-re-sort in PHP.
        // Normalise to a scalar BEFORE the query. filled() is true for an array too, so
        // ?country[]=US used to reach strtoupper(array) - a TypeError, i.e. a 500 on this
        // public page. Casting instead of rejecting would only trade that for an
        // "Array to string conversion" warning and a filter on the string "ARRAY".
        $federatedCountry = is_scalar($request->input('country'))
            ? strtoupper(trim((string) $request->input('country')))
            : '';
        $federatedLanguage = is_scalar($request->input('lang'))
            ? trim((string) $request->input('lang'))
            : '';

        // Everything one instance publishes here, linked from the federation review
        // screen so an admin can see the blast radius of an approval.
        $federatedInstance = is_scalar($request->input('instance'))
            ? trim((string) $request->input('instance'))
            : '';
        $federatedInstanceId = (int) ($federatedInstance !== '' ? UrlUtils::decodeId($federatedInstance) : 0);

        $federatedQuery = FederatedEvent::listable()
            ->when($federatedCountry !== '', fn ($q) => $q->where('country_code', $federatedCountry))
            ->when($federatedLanguage !== '', fn ($q) => $q->where('language', $federatedLanguage))
            // Keyed on the raw string, NEVER on the decoded id. decodeId() returns null
            // for an unreadable hash, so when($federatedInstanceId) would fall through
            // and quietly serve the whole corpus as though no filter had been asked
            // for. A null would not save it either: where($column, null) is rewritten
            // by the builder into IS NULL, which matches nothing by accident rather
            // than on purpose. Casting to 0 makes the never-match deliberate.
            ->when($federatedInstance !== '', fn ($q) => $q->where('federated_instance_id', $federatedInstanceId))
            ->orderBy('next_occurrence_at');

        $federatedLimit = min(max((int) $request->input('federated_limit', 12), 12), 96);
        $federatedTotal = (clone $federatedQuery)->count();
        $federatedEvents = $federatedQuery->limit($federatedLimit)->get();

        return view('marketing.browse', [
            'events' => $events,
            'wall' => BrowseWall::build($events, $more, $now),
            'network' => BrowseWall::remote($federatedEvents, $now),
            'hidden' => BrowseWall::build($hiddenEvents, [], $now, plain: true),
            'browseAdmin' => $isAdmin,
            'hiddenEvents' => $hiddenEvents,
            'hiddenSchedules' => $hiddenSchedules,
            'federatedEvents' => $federatedEvents,
            'federatedTotal' => $federatedTotal,
            'federatedLimit' => $federatedLimit,
            'federatedCountries' => $this->federatedFilterValues('country_code'),
            'federatedLanguages' => $this->federatedFilterValues('language'),
            'federatedCountry' => $federatedCountry,
            'federatedLanguage' => $federatedLanguage,
            'federatedInstance' => $federatedInstance,
        ]);
    }

    /**
     * The events on /browse's wall, soonest first, and the schedules among them that have more.
     *
     * Two reads make the pool. Dated events that have not ended, a few per owning schedule
     * (DiscoveryUtils::candidates()), in the order they are on: what is running now first, then
     * by start. And every series, because when a series is next on cannot be asked of SQL - the
     * rhythm, the skipped dates and the end of the run are Event::nextOccurrenceFrom()'s.
     * BrowseWall::pick() then keeps one event for each credited schedule.
     *
     * "Not ended" is in the SQL, before the pool is cut, on purpose. Chosen afterwards, a studio
     * with three classes already over today had no row left in its three places, and was off the
     * wall with one on tonight. It is start plus length, and SIX HOURS for an event with no
     * length recorded, which is BrowseWall::NO_LENGTH_HOURS: the two must agree, or the pool
     * holds rows the pick then throws away.
     *
     * Only events whose poster shows a picture (own flyer, or a talent/venue schedule's profile
     * photo). The FAQ on the page describes these rules, so keep the two in step.
     *
     * The ids are cached with the homepage wall's (marketing.wall_cache_seconds, 0 in tests) and
     * dropped with it. Each request reloads those rows through the same visibility rules and
     * picks again, so an event that has ended or been unpublished since is gone at once and
     * only a NEW event waits out the cache.
     *
     * @return array{0: Collection, 1: array<string, bool>}
     */
    private function browseWall(Carbon $now): array
    {
        $listed = fn () => $this->publicEventsQuery()
            ->where('is_hidden_from_discovery', false)
            ->where(function ($sub) {
                $sub->where(function ($f) {
                    $f->whereNotNull('flyer_image_url')
                        ->where('flyer_image_url', '!=', '');
                })
                    ->orWhereHas('roles', function ($r) {
                        $r->whereIn('roles.type', ['talent', 'venue'])
                            ->whereNotNull('roles.profile_image_url')
                            ->where('roles.profile_image_url', '!=', '');
                    });
            });

        $build = function () use ($listed, $now) {
            $at = $now->format('Y-m-d H:i:s');

            $dated = DiscoveryUtils::candidates(
                $listed()
                    ->whereNull('days_of_week')
                    ->whereNotNull('starts_at')
                    ->whereRaw(
                        'DATE_ADD(starts_at, INTERVAL ROUND(IF(duration IS NULL OR duration <= 0, ?, duration) * 60) MINUTE) >= ?',
                        [BrowseWall::NO_LENGTH_HOURS, $at]
                    )
                    ->orderByRaw('GREATEST(starts_at, ?) ASC, events.id ASC', [$at]),
                BrowseWall::LIMIT,
                1
            );

            $series = $listed()
                ->whereNotNull('days_of_week')
                ->orderByDesc('events.id')
                ->limit(BrowseWall::SERIES)
                ->get();

            $pool = $dated->concat($series);
            $picked = BrowseWall::pick($pool, BrowseWall::LIMIT, $now);

            return [
                'ids' => $picked->pluck('id')->all(),
                'more' => BrowseWall::schedulesWithMore($pool, $picked, $now),
            ];
        };

        $ttl = (int) config('marketing.wall_cache_seconds');
        $chosen = $ttl > 0 ? Cache::remember(self::browseCacheKey(), $ttl, $build) : $build();

        if (! $chosen['ids']) {
            return [(new Event)->newCollection(), []];
        }

        return [
            BrowseWall::pick($listed()->whereIn('events.id', $chosen['ids'])->get(), BrowseWall::LIMIT, $now),
            $chosen['more'],
        ];
    }

    /**
     * Distinct filter values actually present in the federated corpus, so the
     * dropdowns never offer a choice that returns nothing.
     */
    private function federatedFilterValues(string $column): array
    {
        return FederatedEvent::listable()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }

    /**
     * Record a click sent out to a federated instance.
     *
     * Fired by a beacon rather than by routing the link through a redirect: the
     * card's href must stay a direct, followable link to the origin, because that
     * backlink IS what federation offers operators. A tracking redirect would point
     * the link at this domain and quietly destroy it.
     */
    public function federatedClick(Request $request, string $hash)
    {
        abort_unless(config('app.is_nexus'), 404);

        // Resolved through listable() so counts cannot be inflated by posting hashes for
        // listings that are blocked, expired, or from an unapproved instance.
        $event = FederatedEvent::listable()
            ->whereKey(UrlUtils::decodeId($hash))
            ->first();

        if ($event) {
            FederationClicksDaily::incrementClick($event->federated_instance_id);
        }

        return response()->noContent();
    }

    /**
     * Beacon target for the onboarding funnel's marketing page-view counters.
     *
     * Anonymous marketing HTML is cached at the edge (CacheableMarketingResponse), so a
     * cached page never reaches TrackMarketingVisit and the /admin/users funnel would
     * collapse to whatever the CDN happened to miss. layouts/marketing.blade.php therefore
     * sends the route name here once per page view, and the middleware stands down on any
     * response that shipped the beacon, so there is still exactly one writer per view.
     *
     * CSRF is excluded on the route: navigator.sendBeacon() cannot carry a token, and this
     * changes no state a token would protect - it moves a counter, behind the same
     * bot/suspicious-request filters and the same per-IP+UA daily dedup as before, plus a
     * rate limit. The route name is validated against the router rather than trusted.
     */
    public function recordVisit(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        $routeName = $request->input('route');
        $routeName = is_string($routeName) ? $routeName : null;

        if (! TrackMarketingVisit::isCountableRouteName($routeName)) {
            return response()->noContent(422);
        }

        TrackMarketingVisit::record($request, $routeName, expectDocumentAccept: false);

        return response()->noContent();
    }

    /**
     * Beacon target for the homepage headline test. Filtered exactly like the visit beacon
     * above (signed-in users, bots, requests that do not look like a browser), so the
     * per-variant visitor count and the site-wide one agree on who counts.
     *
     * Deduped per IP+UA per day, per event and variant, on the same daily-salted hash as the
     * site-wide visitor counter. The browser already sends each event once per session, but
     * its click flag is per TAB, and the endpoint is unauthenticated: without this, one
     * visitor with several tabs counted as several clicks, and anyone could POST clicks for
     * one variant and steer the traffic while it is still following clicks.
     */
    public function recordHeroEvent(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        $variant = $request->input('variant');
        $event = $request->input('event');

        if (! HeroExperiment::isVariant($variant) || ! is_string($event) || ! isset(HeroExperiment::EVENTS[$event])) {
            return response()->noContent(422);
        }

        if (! auth()->check()
            && ! PageView::isBot($request->userAgent())
            && ! PageView::isSuspiciousRequest($request, false)
            && PageView::isFirstDailyVisit('mkt_hero_'.$event.'_'.$variant, $request->header('CF-Connecting-IP') ?? $request->ip(), $request->userAgent())) {
            HeroExperiment::recordEvent($variant, $event);
        }

        return response()->noContent();
    }

    /**
     * Admin-only: toggle whether an event is hidden from the platform discovery
     * surfaces (homepage Discover, /browse, /search). Does not unpublish the event.
     */
    /**
     * Block or unblock a federated listing from the browse page.
     *
     * Lives here rather than on AdminFederationController because /browse is served
     * from the base domain, while the admin routes sit behind the app-subdomain group:
     * posting there gets 302'd by RedirectToAppSubdomain, which browsers replay as GET
     * (405). Same shape as toggleEventDiscovery below, which solves this for local events.
     *
     * Being outside the admin route group means EnsureUserIsAdmin never runs, and isAdmin()
     * alone knows nothing about the password re-confirmation window - so this action, a
     * verbatim duplicate of the in-gate AdminFederationController::blockEvent(), would
     * otherwise be the one way to moderate the platform without a current confirmation.
     * Ask AdminReauthUtils directly instead. The session reaches here because
     * AppServiceProvider::defaultHostedSessionDomain() scopes the cookie to the base domain.
     */
    public function toggleFederatedBlock(string $hash)
    {
        abort_unless(config('app.is_nexus'), 404);
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        if (! AdminReauthUtils::isCurrent(request()->session())) {
            return back()->with('error', __('messages.admin_confirm_password'));
        }

        $listing = FederatedEvent::findOrFail(UrlUtils::decodeIdOrFail($hash));
        $listing->isBlocked() ? $listing->unblock() : $listing->block();

        AuditService::log(
            AuditService::ADMIN_FEDERATION_BLOCK_EVENT,
            auth()->id(),
            'FederatedEvent',
            $listing->id,
            null,
            ['blocked' => $listing->isBlocked()],
            $listing->isBlocked() ? 'Blocked federated listing' : 'Unblocked federated listing',
        );

        return back()->with('message', __('messages.saved'));
    }

    /**
     * Hide or unhide a local event from the browse wall.
     *
     * Same gate reasoning as toggleFederatedBlock above: served from the base domain, outside the
     * admin route group, so the re-confirmation window has to be checked by hand.
     */
    public function toggleEventDiscovery(Request $request, string $hash)
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        if (! AdminReauthUtils::isCurrent($request->session())) {
            return $this->discoveryRefused($request);
        }

        $event = Event::findOrFail(UrlUtils::decodeIdOrFail($hash));
        // The pills and Undo say which state they want, so a double press cannot flip it back; a
        // post that does not say still flips, as this action always has.
        $event->is_hidden_from_discovery = $request->has('hide')
            ? $request->boolean('hide')
            : ! $event->is_hidden_from_discovery;
        $event->save();

        // The save() above already busts it through Event's saved hook. Repeated here because
        // this button is rendered ON the cached page: the redirect below re-renders the wall
        // immediately, and a stale one would show the badge and the flash contradicting each
        // other and flip the event back the next time it is clicked.
        self::forgetWallCache();

        AuditService::log(
            'event.toggle_discovery',
            auth()->id(),
            Event::class,
            $event->id,
            null,
            ['is_hidden_from_discovery' => $event->is_hidden_from_discovery],
        );

        $said = $event->is_hidden_from_discovery
            ? 'Event hidden from discovery'
            : 'Event restored to discovery';

        return $this->backToDiscovery($request, 'message', $said, [
            'undo' => [
                'action' => route('marketing.discovery.toggle', $event->hashedId()),
                'hide' => ! $event->is_hidden_from_discovery,
            ],
        ]);
    }

    /**
     * Hide or unhide every event FROM a schedule (Event::constrainFromSchedule()) on the homepage,
     * /browse, /search, /for-talent and the global sitemap, and the schedule from /search's
     * schedule results. Its own pages are not touched.
     *
     * The same gate as toggleEventDiscovery() and for the same reason. Not a flip: the form says
     * hide=1 or hide=0, so a double press, or Undo pressed twice, lands where it was meant to.
     * The row lives in its own table, whose write fires no model hook, so the wall's cache is
     * dropped here by hand.
     */
    public function toggleScheduleDiscovery(Request $request, string $hash)
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        if (! AdminReauthUtils::isCurrent($request->session())) {
            return $this->discoveryRefused($request);
        }

        $role = Role::findOrFail(UrlUtils::decodeIdOrFail($hash));
        $hide = $request->boolean('hide');

        if ($hide) {
            DiscoveryHiddenSchedule::firstOrCreate(['role_id' => $role->id]);
        } else {
            DiscoveryHiddenSchedule::where('role_id', $role->id)->delete();
        }

        self::forgetWallCache();

        AuditService::log(
            AuditService::ADMIN_DISCOVERY_HIDE_SCHEDULE,
            auth()->id(),
            'Role',
            $role->id,
            null,
            ['hidden' => $hide],
            $hide ? 'Hid a schedule from discovery' : 'Restored a schedule to discovery',
        );

        // What the press moved: its upcoming events that discovery would otherwise list. Counted
        // with the hidden schedules left in, so the number is the same before and after the write.
        $count = Event::constrainFromSchedule($this->publicUpcomingEventsQuery(hiddenSchedulesToo: true), $role->id)
            ->where('is_hidden_from_discovery', false)
            ->count();
        $events = $count === 1 ? '1 upcoming event' : $count.' upcoming events';

        $said = match (true) {
            $hide && $count > 0 => $events.' from '.$role->name.($count === 1 ? ' is' : ' are').' now hidden from the homepage, Browse and search.',
            $hide => $role->name.' is now hidden from the homepage, Browse and search. It has no upcoming events listed.',
            $count > 0 => $role->name.' is back on the homepage, Browse and search, with '.$events.'.',
            default => $role->name.' is back on the homepage, Browse and search.',
        };

        return $this->backToDiscovery($request, 'message', $said, [
            'undo' => [
                'action' => route('marketing.discovery.schedule', UrlUtils::encodeId($role->id)),
                'hide' => ! $hide,
            ],
            // The lasting way back: /search leaves a hidden schedule out even for an admin.
            'list' => $hide ? route('marketing.browse').'#hidden-schedules' : null,
        ]);
    }

    /** The session key a homepage discovery note waits under for discoveryControls(). */
    private const HOME_NOTICE = 'discovery_notice_home';

    /**
     * A platform admin's pills for the homepage rail's cards, and the note left by the last one
     * pressed there, as HTML for the page to put in place.
     *
     * The homepage is served from the edge's one anonymous copy to everyone, signed-in visitors
     * included (docs/CACHING.md), so anything drawn for an admin on the server never reaches them.
     * The page asks here after it loads, when the browser holds es_signed_in (MarkSignedIn). Named
     * outside marketing.*, so CacheableMarketingResponse can never mark it public, and said
     * private besides. Each card's state is read now, not from the copy: the edge can show a card
     * for up to ten minutes after it was hidden, and its pill then offers the way back.
     */
    public function discoveryControls(Request $request)
    {
        $private = ['Cache-Control' => 'private, no-store'];

        // An ordinary signed-in visitor asks too (the cookie does not say who is an admin). Their
        // flash is kept for the page it was meant for.
        $request->session()->reflash();

        if (! $request->user()?->isAdmin()) {
            return response()->noContent(204, $private);
        }

        $ids = collect(explode(',', (string) $request->query('events')))
            ->take(30)
            ->map(fn ($hash) => UrlUtils::decodeId(trim($hash)))
            ->filter()
            ->all();

        $events = Event::with(['roles', 'creatorRole'])->whereIn('id', $ids)->get();
        $cardRoles = $events->mapWithKeys(fn (Event $event) => [$event->id => $event->getViewableRole()]);
        $hiddenSchedules = DiscoveryHiddenSchedule::whereIn('role_id', $cardRoles->filter()->pluck('id'))
            ->pluck('role_id')
            ->flip();

        $cards = [];

        foreach ($events as $event) {
            $cardRole = $cardRoles[$event->id];
            // Offered only where hiding the schedule takes this card away: never a curator's listing.
            $schedule = $cardRole && $event->isFromSchedule($cardRole) ? $cardRole : null;
            $scheduleHidden = $schedule && $hiddenSchedules->has($schedule->id);

            $cards[$event->hashedId()] = [
                'html' => view('marketing.partials.home-discovery-pills', [
                    'event' => $event,
                    'schedule' => $schedule,
                    'scheduleHidden' => $scheduleHidden,
                ])->render(),
                'dim' => $event->is_hidden_from_discovery || $scheduleHidden,
            ];
        }

        $notice = $request->session()->pull(self::HOME_NOTICE);

        return response()->json([
            'cards' => (object) $cards,
            'notice' => is_array($notice) ? view('marketing.partials.home-discovery-notice', ['notice' => $notice])->render() : null,
        ], 200, $private);
    }

    /**
     * Where a discovery pill returns the admin to: a fixed page for each anchor the forms send,
     * never the Referer. back() reads the Referer and then the session's last page, and the hosted
     * session is shared with the app's subdomains, so without one it could land on the admin
     * area. A post that names no anchor goes back() as it always has.
     */
    private function discoveryTarget(Request $request): ?string
    {
        return match ($request->input('anchor')) {
            'discover' => route('marketing.index').'#discover',
            'events' => route('marketing.browse').'#events',
            'hidden-events' => route('marketing.browse').'#hidden-events',
            'hidden-schedules' => route('marketing.browse').'#hidden-schedules',
            default => null,
        };
    }

    /**
     * Back to where the pill was pressed, with the flash /browse has always printed ($key) and the
     * notice the homepage and /browse's sections print: $said, plus Undo or a link, and the anchor
     * it was pressed at ('at'), so the section the page lands on is the one that says it.
     */
    private function backToDiscovery(Request $request, string $key, string $said, array $notice): RedirectResponse
    {
        $target = $this->discoveryTarget($request);

        // The homepage is the edge's anonymous copy, so nothing reads a flash there: the page asks
        // discoveryControls() for the note after it loads. Kept until that request takes it, not
        // flashed, because the page's own beacons reach the session first and would age a flash.
        if ($request->input('anchor') === 'discover') {
            $request->session()->put(self::HOME_NOTICE, $notice + ['text' => $said]);

            return redirect()->to($target);
        }

        return ($target ? redirect()->to($target) : back())
            ->with($key, $said)
            ->with('discovery_notice', $notice + ['text' => $said, 'at' => $target ? $request->input('anchor') : null]);
    }

    /**
     * A lapsed password confirmation. The homepage printed nothing at all for this, and /browse
     * said "to access the admin area" with no way to do it: the notice links to the confirm page,
     * which returns through url.intended to the place the pill was pressed.
     */
    private function discoveryRefused(Request $request): RedirectResponse
    {
        $target = $this->discoveryTarget($request);

        if ($target) {
            $request->session()->put('url.intended', $target);
        }

        return $this->backToDiscovery($request, 'error', __('messages.admin_confirm_password'), [
            'text' => 'Confirm your password to hide or restore events, then press the button again.',
            'bad' => true,
            'confirm' => route('admin.password.confirm.show'),
        ]);
    }

    /**
     * Closure constraining a Role query to publicly listed, claimed, verified schedules.
     */
    private function publicScheduleFilter(): \Closure
    {
        return function ($q) {
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
    }

    /**
     * Base query for upcoming, public, non-demo events shown on the homepage Discover, /search
     * and /for-talent. Callers add hidden-state filtering, any text search, ordering and limits.
     *
     * "Upcoming" here is from the start of today (UTC), every series, and anything of a day or
     * longer that is still running. /browse asks a finer question (not ended, to the minute) and
     * builds on publicEventsQuery() itself.
     */
    private function publicUpcomingEventsQuery(bool $hiddenSchedulesToo = false): Builder
    {
        return $this->publicEventsQuery($hiddenSchedulesToo)
            ->where(function ($q) {
                $q->where('starts_at', '>=', Carbon::today())
                    ->orWhereNotNull('days_of_week')
                    ->orWhere(function ($q2) {
                        $q2->where('duration', '>=', 24)
                            ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [Carbon::today()]);
                    });
            });
    }

    /**
     * Which events the platform's discovery surfaces may show at all, whenever they are on:
     * public, accepted by a listed schedule, not demo content, not a likely test and not from a
     * schedule a platform admin has hidden. Every caller adds its own rule about time.
     *
     * $hiddenSchedulesToo keeps a hidden schedule's events, for the one caller that has to count
     * them: the notice that says how many a "Hide schedule" took out.
     */
    private function publicEventsQuery(bool $hiddenSchedulesToo = false): Builder
    {
        $excludeCountry = strtolower(trim((string) config('app.search_exclude_country', '')));
        $publicScheduleFilter = $this->publicScheduleFilter();

        // creatorRole: every card renders the date in its own schedule's timezone.
        //
        // roles is ordered explicitly because the relation has no ordering of its own, and three
        // things read its FIRST element: getViewableRole() (the name and city on the card),
        // getGuestUrlData() via role()/venue (the card's href and the url in the ItemList JSON-LD)
        // and DiscoveryUtils::scheduleKey() (the schedule an event is counted against, on every surface).
        //
        // event_role.id, NOT roles.id. It is attachment order, which is what the unordered query
        // was already returning in practice - the join drives off the event_id index, whose leaves
        // are (event_id, PK) - so pinning it keeps serving the URLs production already serves.
        // roles.id would pin a DIFFERENT order: the schedule that registered earliest would take
        // the credit on every event it touches, so a venue from 2024 would out-rank every act it
        // books. withPivot('id') already selects the column, so this costs no extra read, and it
        // avoids the filesort that ordering by roles.id forces over the joined result.
        //
        // Scoped to discovery rather than added to Event::roles(), which guest pages, graphics and
        // emails all read. The sitemap deliberately keeps the natural order for the same reason.
        return Event::with(['roles' => fn ($q) => $q->orderBy('event_role.id'), 'creatorRole'])
            ->where('is_private', false)
            ->where('is_draft', false)
            ->where('is_cancelled', false)
            // A save keeps a password only on an unlisted event now, but older rows carry one while
            // listed, and every card here shows the event's name and flyer to anyone. /search
            // matches on its short description too.
            ->notPasswordProtected()
            // Only surface events a public schedule has actually accepted (is_accepted = true).
            // Pending (null) or rejected (false) curator associations stay out of discovery,
            // matching the guest portal, which never shows un-accepted events for a schedule.
            ->whereHas('roles', function ($q) use ($publicScheduleFilter) {
                $publicScheduleFilter($q);
                $q->where('event_role.is_accepted', true);
            })
            // Keep demo content out of the public showcase. See Role::scopeDemoContent() for why
            // that is a wider question than the subdomain shapes this used to test.
            //
            // Note this drops an event if ANY attached schedule is demo, where
            // SitemapController::eventQuery() instead binds its demo check to the same pivot row as
            // is_accepted, so a real schedule's event survives a demo curator picking it up. The
            // stricter rule here is what actually removes the reported "Wine & Jazz Evening", whose
            // only schedules are demo ones - but widening the predicate widens that exposure, so
            // the asymmetry is deliberate rather than overlooked.
            ->whereDoesntHave('roles', fn ($r) => $r->demoContent())
            // Keep throwaway "test" events (and events under "test"-named schedules) out too.
            ->excludeLikelyTest()
            // And every event FROM a schedule a platform admin hid with "Hide schedule": the ones
            // it owns and the ones it is on as a talent or venue, never a curator's listings
            // (Event::constrainFromSchedule() says why).
            ->unless($hiddenSchedulesToo, fn ($q) => Event::constrainNotOnHiddenSchedule($q))
            ->when($excludeCountry !== '', function ($q) use ($excludeCountry) {
                // strict: require at least one schedule with a known country, and exclude events
                // linked to any schedule in the configured country
                $q->whereHas('roles', function ($r) {
                    $r->whereNotNull('country_code')->where('country_code', '!=', '');
                })
                    ->whereDoesntHave('roles', function ($r) use ($excludeCountry) {
                        $r->whereRaw('LOWER(country_code) = ?', [$excludeCountry]);
                    });
            });
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
            'subscriptions' => route('marketing.docs.subscriptions'),
            'gift_cards' => route('marketing.docs.gift_cards'),
            'allocated_seating' => route('marketing.docs.allocated_seating'),
            'appointments' => route('marketing.docs.appointments'),
            'event_graphics' => route('marketing.docs.event_graphics'),
            'analytics' => route('marketing.docs.analytics'),
            'account_settings' => route('marketing.docs.account_settings'),
            'scan_agenda' => route('marketing.docs.scan_agenda'),
            'boost' => route('marketing.docs.boost'),
            'selfhost_index' => route('marketing.docs.selfhost'),
            'selfhost_installation' => route('marketing.docs.selfhost.installation'),
            'selfhost_docker' => route('marketing.docs.selfhost.docker'),
            'selfhost_stripe' => route('marketing.docs.selfhost.stripe'),
            'selfhost_google_calendar' => route('marketing.docs.selfhost.google_calendar'),
            'selfhost_google_wallet' => route('marketing.docs.selfhost.google_wallet'),
            'selfhost_apple_wallet' => route('marketing.docs.selfhost.apple_wallet'),
            'selfhost_microsoft_calendar' => route('marketing.docs.selfhost.microsoft_calendar'),
            'selfhost_email' => route('marketing.docs.selfhost.email'),
            'selfhost_ai' => route('marketing.docs.selfhost.ai'),
            'selfhost_admin' => route('marketing.docs.selfhost.admin'),
            'selfhost_boost' => route('marketing.docs.selfhost.boost'),
            'selfhost_accessibility' => route('marketing.docs.selfhost.accessibility'),
            'saas_setup' => route('marketing.docs.saas.setup'),
            'saas_custom_domains' => route('marketing.docs.saas.custom_domains'),
            'saas_twilio' => route('marketing.docs.saas.twilio'),
            'saas_facebook_login' => route('marketing.docs.saas.facebook_login'),
            'saas_federation' => route('marketing.docs.saas.federation'),
            'saas_monetization' => route('marketing.docs.saas.monetization'),
            'selfhost_federation' => route('marketing.docs.selfhost.federation'),
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
            ['page' => 'Getting Started', 'section' => 'Customize Your Schedule', 'description' => 'What each section of the schedule form holds.', 'url' => $r['getting_started'].'#customize', 'category' => 'User Guide', 'keywords' => 'customize personalize configure'],
            ['page' => 'Getting Started', 'section' => 'Your Dashboard', 'description' => 'The page you land on after signing in: views, followers, revenue and live visitors, what is coming up, recent sales and followers, and the calendar.', 'url' => $r['getting_started'].'#dashboard', 'category' => 'User Guide', 'keywords' => 'dashboard home overview tiles views followers revenue realtime coming up recent activity customize add event'],
            ['page' => 'Getting Started', 'section' => 'Frequently Asked Questions', 'description' => 'Common questions about schedules, URLs, importing, getting paid, how people hear about new events, and claiming a page someone created for you.', 'url' => $r['getting_started'].'#faq', 'category' => 'User Guide', 'keywords' => 'faq help questions claim my venue page someone made a page for me get paid payment methods stripe paypal refund hear about new events subscribers'],
            ['page' => 'Getting Started', 'section' => 'Next Steps', 'description' => 'Links to related documentation for further setup.', 'url' => $r['getting_started'].'#next-steps', 'category' => 'User Guide', 'keywords' => ''],
            ['page' => 'Getting Started', 'section' => 'The Setup Guide', 'description' => 'The card that walks a new organizer from a first schedule to a live, shared page.', 'url' => $r['getting_started'].'#setup-guide', 'category' => 'User Guide', 'keywords' => 'setup guide steps first event share hide'],

            // Creating Schedules, in the order of the schedule form
            ['page' => 'Creating Schedules', 'section' => 'The Schedule Form', 'description' => 'Where the schedule form is, its sections and rows, and how the one Save works.', 'url' => $r['creating_schedules'].'#schedule-form', 'category' => 'User Guide', 'keywords' => 'edit schedule form sections rows save'],
            ['page' => 'Creating Schedules', 'section' => 'Saving', 'description' => 'The save bar: unsaved sections, what saving removes, and what a refused save asks you to check.', 'url' => $r['creating_schedules'].'#saving', 'category' => 'User Guide', 'keywords' => 'save bar unsaved saving removes check cancel discard'],
            ['page' => 'Creating Schedules', 'section' => 'Schedule Types', 'description' => 'Choose the right schedule type for your use case.', 'url' => $r['creating_schedules'].'#schedule-types', 'category' => 'User Guide', 'keywords' => 'talent venue curator'],
            ['page' => 'Creating Schedules', 'section' => 'Details', 'description' => 'Name, short description and description, language and time, and contact info.', 'url' => $r['creating_schedules'].'#details', 'category' => 'User Guide', 'keywords' => 'name description short description ai generator'],
            ['page' => 'Creating Schedules', 'section' => 'AI Details Generator', 'description' => 'Use AI to generate schedule descriptions (Enterprise).', 'url' => $r['creating_schedules'].'#ai-details-generator', 'category' => 'User Guide', 'keywords' => 'ai generate details description automatic'],
            ['page' => 'Creating Schedules', 'section' => 'First Day of Week', 'description' => 'Choose which day your schedule\'s calendar week starts on, in the Language and time row beside the timezone.', 'url' => $r['creating_schedules'].'#details-localization', 'category' => 'User Guide', 'keywords' => 'first day of week monday sunday calendar week start language and time timezone 24-hour advanced'],
            ['page' => 'Creating Schedules', 'section' => 'Translation glossary', 'description' => 'Names automatic translation keeps exactly as written, and words with a fixed translation. Changing the list translates again what uses a changed term.', 'url' => $r['creating_schedules'].'#details-glossary', 'category' => 'User Guide', 'keywords' => 'translation glossary keep name untranslated do not translate fixed translation terms brand program organization weglot second language'],
            ['page' => 'Creating Schedules', 'section' => 'Contact Info', 'description' => 'Add contact methods to your schedule.', 'url' => $r['creating_schedules'].'#contact-info', 'category' => 'User Guide', 'keywords' => 'email phone website contact city country'],
            ['page' => 'Creating Schedules', 'section' => 'Announcement Bar', 'description' => 'Show an announcement banner at the top of your schedule\'s guest pages, and on its event pages too.', 'url' => $r['creating_schedules'].'#announcement-bar', 'category' => 'User Guide', 'keywords' => 'banner announcement message notice top of page'],
            ['page' => 'Creating Schedules', 'section' => 'Event Sources', 'description' => 'Follow talent and venue schedules so all of their events appear on a curator automatically.', 'url' => $r['creating_schedules'].'#event-sources', 'category' => 'User Guide', 'keywords' => 'curator sources follow aggregate talent venue automatic city guide festival'],
            ['page' => 'Creating Schedules', 'section' => 'Address', 'description' => 'Configure location settings for your schedule.', 'url' => $r['creating_schedules'].'#address', 'category' => 'User Guide', 'keywords' => 'location map'],
            ['page' => 'Creating Schedules', 'section' => 'Merging Duplicates', 'description' => 'Merge a duplicate venue schedule into another, or bulk-merge look-alike venues from Following or a curator schedule.', 'url' => $r['creating_schedules'].'#merge', 'category' => 'User Guide', 'keywords' => 'merge duplicate venue venues combine consolidate merge into not duplicates unclaimed following listed twice'],
            ['page' => 'Creating Schedules', 'section' => 'Style', 'description' => 'Visual styling options for your schedule.', 'url' => $r['creating_schedules'].'#style', 'category' => 'User Guide', 'keywords' => 'design theme appearance'],
            ['page' => 'Creating Schedules', 'section' => 'Page Content', 'description' => 'What your schedule page shows besides its events: social links, videos, a gallery, sponsors, a venue map and nearby accommodation.', 'url' => $r['creating_schedules'].'#page-content', 'category' => 'User Guide', 'keywords' => 'page content public page social links videos gallery sponsors venue map accommodation videos & links'],
            ['page' => 'Creating Schedules', 'section' => 'Social Links', 'description' => 'Add your social links to your schedule page, each with a short link on your own schedule URL that counts its clicks.', 'url' => $r['creating_schedules'].'#social-links', 'category' => 'User Guide', 'keywords' => 'social links instagram facebook spotify short link vanity url custom slug forwarding redirect videos & links videos and links'],
            ['page' => 'Creating Schedules', 'section' => 'Videos', 'description' => 'Show YouTube videos on your schedule page, and hide the strip of your performers\' videos.', 'url' => $r['creating_schedules'].'#videos', 'category' => 'User Guide', 'keywords' => 'youtube videos video panel hide performers videos hide videos videos & links'],
            ['page' => 'Creating Schedules', 'section' => 'Gallery', 'description' => 'Show a gallery of photos of your venue, past events or team on your schedule page.', 'url' => $r['creating_schedules'].'#gallery', 'category' => 'User Guide', 'keywords' => 'gallery photos images pictures slideshow lightbox album upload'],
            ['page' => 'Creating Schedules', 'section' => 'Sponsors', 'description' => 'Showcase sponsor logos and links on your schedule page.', 'url' => $r['creating_schedules'].'#sponsors', 'category' => 'User Guide', 'keywords' => 'sponsors logos branding partners'],
            ['page' => 'Creating Schedules', 'section' => 'Venue map', 'description' => 'Show your venues as pins on a map on your schedule page, each with its logo.', 'url' => $r['creating_schedules'].'#venue-map', 'category' => 'User Guide', 'keywords' => 'venue map pins locations openstreetmap streets directions nearby approximate position address not found show map'],
            ['page' => 'Creating Schedules', 'section' => 'Accommodation', 'description' => 'Show nearby hotels and rentals on event pages, and earn affiliate commission.', 'url' => $r['creating_schedules'].'#accommodation', 'category' => 'User Guide', 'keywords' => 'accommodation hotels lodging stay22 affiliate commission where to stay travel booking rentals'],
            ['page' => 'Creating Schedules', 'section' => 'Customize', 'description' => 'Sub-schedules, custom fields, categories and custom labels.', 'url' => $r['creating_schedules'].'#customize', 'category' => 'User Guide', 'keywords' => 'customize personalize'],
            ['page' => 'Creating Schedules', 'section' => 'Sub-schedules', 'description' => 'Create categories to organize events within a schedule.', 'url' => $r['creating_schedules'].'#customize-subschedules', 'category' => 'User Guide', 'keywords' => 'groups categories organize'],
            ['page' => 'Creating Schedules', 'section' => 'Custom Fields', 'description' => 'Add custom fields to your events.', 'url' => $r['creating_schedules'].'#customize-custom-fields', 'category' => 'User Guide', 'keywords' => 'custom fields metadata request form validation pattern regex hint multiselect checklist show as filter room floor on event page public search shareable link'],
            ['page' => 'Creating Schedules', 'section' => 'Categories', 'description' => 'Customise the event categories shown on your schedule.', 'url' => $r['creating_schedules'].'#customize-categories', 'category' => 'User Guide', 'keywords' => 'categories custom rename add'],
            ['page' => 'Creating Schedules', 'section' => 'Custom Labels', 'description' => 'Override default labels shown on your schedule page.', 'url' => $r['creating_schedules'].'#customize-custom-labels', 'category' => 'User Guide', 'keywords' => 'custom labels text rename override buttons'],
            ['page' => 'Creating Schedules', 'section' => 'Engagement', 'description' => 'Configure visitor interaction features for your schedule.', 'url' => $r['creating_schedules'].'#engagement', 'category' => 'User Guide', 'keywords' => 'engagement interaction community'],
            ['page' => 'Creating Schedules', 'section' => 'Requests', 'description' => 'Configure public event request submissions.', 'url' => $r['creating_schedules'].'#engagement-requests', 'category' => 'User Guide', 'keywords' => 'submissions public requests custom questions extra fields validation pattern booking form required fields mandatory online in-person request terms guest without account phone number contact details who submitted reply to requester'],
            ['page' => 'Creating Schedules', 'section' => 'Mailing List', 'description' => 'For your visitors: the Stay up to date sign-up panel and the "Notify me" card on your pages, where they leave an email address, and whether subscribers are emailed about new events. Not your own email preferences.', 'url' => $r['creating_schedules'].'#engagement-mailing-list', 'category' => 'User Guide', 'keywords' => 'mailing list sign-up panel signup panel stay up to date subscribe subscribers followers notify me card tell me when tickets go on sale interest list visitors email capture show sign-up panel turn off advanced'],
            ['page' => 'Creating Schedules', 'section' => 'Fan Content', 'description' => 'Allow visitors to submit photos and videos to events.', 'url' => $r['creating_schedules'].'#engagement-fan-content', 'category' => 'User Guide', 'keywords' => 'fan content photos videos submissions'],
            ['page' => 'Creating Schedules', 'section' => 'Feedback', 'description' => 'Send feedback requests to attendees after events, and optionally publish the results.', 'url' => $r['creating_schedules'].'#engagement-feedback', 'category' => 'User Guide', 'keywords' => 'feedback reviews ratings attendees show feedback publicly public reviews delay'],
            ['page' => 'Creating Schedules', 'section' => 'Carpool', 'description' => 'Enable carpool matching for event attendees.', 'url' => $r['creating_schedules'].'#engagement-carpool', 'category' => 'User Guide', 'keywords' => 'carpool ride sharing matching driver attendee'],
            ['page' => 'Creating Schedules', 'section' => 'Tickets', 'description' => 'The ticket presets new events start from, and gift cards.', 'url' => $r['creating_schedules'].'#tickets', 'category' => 'User Guide', 'keywords' => 'tickets ticket presets gift cards default tickets gift cards tab'],
            ['page' => 'Creating Schedules', 'section' => 'Ticket Presets', 'description' => 'Named ticket setups new events start from, and the owner\'s switch that has the team pick from them instead of typing prices.', 'url' => $r['creating_schedules'].'#ticket-presets', 'category' => 'User Guide', 'keywords' => 'ticket presets default tickets price team members staff pick lock cash regular special free event'],
            ['page' => 'Creating Schedules', 'section' => 'Gift Cards', 'description' => 'Sell gift cards that buyers send by email, from the Gift cards row of the schedule\'s Tickets tab.', 'url' => $r['creating_schedules'].'#gift-cards', 'category' => 'User Guide', 'keywords' => 'gift cards section gift cards tab tickets'],
            ['page' => 'Creating Schedules', 'section' => 'Reminders', 'description' => 'The email ticket holders and registered guests get before an event, when it is on by default and when it goes out.', 'url' => $r['creating_schedules'].'#ticket-reminders', 'category' => 'User Guide', 'keywords' => 'reminder reminders email before event tomorrow ticket holders attendees no-shows notification default on off free plan'],
            ['page' => 'Creating Schedules', 'section' => 'Settings', 'description' => 'Configure general schedule settings.', 'url' => $r['creating_schedules'].'#settings', 'category' => 'User Guide', 'keywords' => 'configuration options preferences'],
            ['page' => 'Creating Schedules', 'section' => 'Schedule URL', 'description' => 'Your schedule\'s address, and a custom domain for it.', 'url' => $r['creating_schedules'].'#settings-general', 'category' => 'User Guide', 'keywords' => 'schedule url address subdomain path custom domain'],
            ['page' => 'Creating Schedules', 'section' => 'Custom Domain', 'description' => 'Set up a custom domain for your schedule using Direct (CNAME) or Redirect (Cloudflare) mode.', 'url' => $r['creating_schedules'].'#custom-domain', 'category' => 'User Guide', 'keywords' => 'custom domain cname dns branded url'],
            ['page' => 'Creating Schedules', 'section' => 'New Events', 'description' => 'The visibility, URL pattern, category and curator schedules every new event starts with, and the fields of the AI import form.', 'url' => $r['creating_schedules'].'#settings-new-events', 'category' => 'User Guide', 'keywords' => 'new events defaults default visibility public draft internal unlisted event url pattern slug default category default curator schedules import form fields ai import update all events advanced'],
            ['page' => 'Creating Schedules', 'section' => 'URL Pattern Variables', 'description' => 'The variables an event URL pattern can use.', 'url' => $r['creating_schedules'].'#url-pattern-variables', 'category' => 'User Guide', 'keywords' => 'url pattern variables slug event link'],
            ['page' => 'Creating Schedules', 'section' => 'Notifications', 'description' => 'Choose your own email notifications for a schedule, and whether its email subscribers get a digest of new events.', 'url' => $r['creating_schedules'].'#settings-notifications', 'category' => 'User Guide', 'keywords' => 'notifications email alerts new event requests ticket sale feedback poll installment payments digest announce new events subscribers shared mailbox'],
            ['page' => 'Creating Schedules', 'section' => 'Shared Notification Address', 'description' => 'Send a copy of a schedule\'s notifications, such as new event requests, to a shared mailbox your team works from.', 'url' => $r['creating_schedules'].'#notification-email', 'category' => 'User Guide', 'keywords' => 'shared mailbox team inbox notification address copy send notifications to another email confirm stop unsubscribe'],
            ['page' => 'Creating Schedules', 'section' => 'Push Notifications', 'description' => 'Get schedule notifications in your browser and on your phone, in addition to email.', 'url' => $r['creating_schedules'].'#notifications-push', 'category' => 'User Guide', 'keywords' => 'push notifications browser mobile device test onesignal enable push ios home screen'],
            ['page' => 'Creating Schedules', 'section' => 'Advanced Settings', 'description' => 'The accessibility widget, declining other schedules\' promotions, and listing the schedule on the network.', 'url' => $r['creating_schedules'].'#settings-advanced', 'category' => 'User Guide', 'keywords' => 'advanced options accessibility widget font size contrast motion promotions opt out network federation'],
            ['page' => 'Creating Schedules', 'section' => 'Do Not Show Other Schedules\' Promotions', 'description' => 'Decline to carry other schedules\' paid promotions, and any ads, on your public pages.', 'url' => $r['creating_schedules'].'#settings-advanced', 'category' => 'User Guide', 'keywords' => 'ads adsense advertising promotions opt out opt-out decline hide ads no ads free plan'],
            ['page' => 'Creating Schedules', 'section' => 'List This Schedule on the Network', 'description' => 'List a schedule on the Event Schedule network to share its public events with the eventschedule.com listings, or keep it off.', 'url' => $r['creating_schedules'].'#settings-advanced', 'category' => 'User Guide', 'keywords' => 'federation network list schedule share events eventschedule.com listings opt in opt out discovery list on the network prompt dashboard listed badge'],
            ['page' => 'Creating Schedules', 'section' => 'Integrations', 'description' => 'Connect with calendar and third-party services.', 'url' => $r['creating_schedules'].'#integrations', 'category' => 'User Guide', 'keywords' => 'connect sync third-party'],
            ['page' => 'Creating Schedules', 'section' => 'Email Settings', 'description' => 'Configure email delivery for your schedule.', 'url' => $r['creating_schedules'].'#integrations-email', 'category' => 'User Guide', 'keywords' => 'email smtp sender notifications permission denied authentication failed test email error unverified sender app password troubleshooting'],
            ['page' => 'Creating Schedules', 'section' => 'Calendar Sync', 'description' => 'Sync direction and what the Google, Outlook and CalDAV rows share.', 'url' => $r['creating_schedules'].'#calendar-sync', 'category' => 'User Guide', 'keywords' => 'calendar sync direction google outlook caldav'],
            ['page' => 'Creating Schedules', 'section' => 'Publish New Events Automatically', 'description' => 'Events pulled in from a calendar arrive as drafts unless you turn on publishing them as they arrive, for the calendar you chose.', 'url' => $r['creating_schedules'].'#publish-on-arrival', 'category' => 'User Guide', 'keywords' => 'publish automatically drafts arrive calendar sync google outlook caldav main calendar'],
            ['page' => 'Creating Schedules', 'section' => 'What Stays Off Your Page', 'description' => 'Private, declined and cancelled entries, and ones with meeting passcodes or an email thread, stay off your public page; meeting links never become venues.', 'url' => $r['creating_schedules'].'#calendar-privacy', 'category' => 'User Guide', 'keywords' => 'private calendar passcode zoom teams dial-in meeting link email thread declined hidden'],
            ['page' => 'Creating Schedules', 'section' => 'Events From Your Calendar', 'description' => 'The list of events a calendar sync is keeping off your page: publish them, keep them hidden, or publish all.', 'url' => $r['creating_schedules'].'#calendar-review', 'category' => 'User Guide', 'keywords' => 'review drafts waiting taken off publish all keep hidden calendar sync'],
            ['page' => 'Creating Schedules', 'section' => 'Calendar Delete Sync', 'description' => 'Choose what happens when you delete an imported event in your external calendar: keep it, mark it cancelled, or delete it.', 'url' => $r['creating_schedules'].'#delete-sync', 'category' => 'User Guide', 'keywords' => 'delete cancel on delete calendar delete sync tombstone keep it mark cancelled removed google outlook inbound'],
            ['page' => 'Creating Schedules', 'section' => 'Google Calendar', 'description' => 'Set up Google Calendar sync for your schedule.', 'url' => $r['creating_schedules'].'#integrations-google', 'category' => 'User Guide', 'keywords' => 'google calendar sync'],
            ['page' => 'Creating Schedules', 'section' => 'Outlook Calendar', 'description' => 'Set up Outlook / Microsoft 365 calendar sync for your schedule.', 'url' => $r['creating_schedules'].'#integrations-microsoft', 'category' => 'User Guide', 'keywords' => 'outlook microsoft 365 office calendar sync teams'],
            ['page' => 'Creating Schedules', 'section' => 'CalDAV Calendar', 'description' => 'Set up CalDAV protocol integration.', 'url' => $r['creating_schedules'].'#integrations-caldav', 'category' => 'User Guide', 'keywords' => 'caldav ical protocol'],
            ['page' => 'Creating Schedules', 'section' => 'Auto Import', 'description' => 'On a selfhosted install, read events from a list of web pages once a day, optionally keeping only certain cities.', 'url' => $r['creating_schedules'].'#auto-import', 'category' => 'User Guide', 'keywords' => 'import automatic daily urls web pages city filter selfhost auto import tab integrations'],
            ['page' => 'Creating Schedules', 'section' => 'Feeds from Other Sites', 'description' => 'Keep a schedule up to date from a calendar address, an RSS feed or a page that lists events.', 'url' => $r['creating_schedules'].'#integrations-feeds', 'category' => 'User Guide', 'keywords' => 'feed rss atom ics ical webcal sync import subscribe'],
            ['page' => 'Creating Schedules', 'section' => 'Calendar Text and Feeds', 'description' => 'Calendar description template and iCal/RSS feed URLs.', 'url' => $r['creating_schedules'].'#integrations-advanced', 'category' => 'User Guide', 'keywords' => 'advanced feeds ical rss subscribe calendar description template variables price discount before after coupon calendar text'],
            ['page' => 'Creating Schedules', 'section' => 'Template Variables', 'description' => 'Every variable a calendar description template can use.', 'url' => $r['creating_schedules'].'#available-variables', 'category' => 'User Guide', 'keywords' => 'template variables calendar description'],
            ['page' => 'Creating Schedules', 'section' => 'Custom domain main address', 'description' => 'Choose whether your domain or your subdomain is the main address search engines, mail and shared links use.', 'url' => $r['creating_schedules'].'#custom-domain-main-address', 'category' => 'User Guide', 'keywords' => 'custom domain main address subdomain canonical primary direct mode sitemap emails shared links qr also at'],
            ['page' => 'Creating Schedules', 'section' => 'When calendar reading stops', 'description' => 'What happens when an admin pauses a calendar or the schedule changes owner, and how reading resumes.', 'url' => $r['creating_schedules'].'#reading-stopped', 'category' => 'User Guide', 'keywords' => 'calendar sync reading stopped paused private calendar owner transfer no sync to google calendar to outlook calendar resume publish automatically'],

            // Schedule Styling
            ['page' => 'Schedule Styling', 'section' => 'Overview', 'description' => 'Introduction to schedule styling options.', 'url' => $r['schedule_styling'].'#overview', 'category' => 'User Guide', 'keywords' => 'design appearance theme'],
            ['page' => 'Schedule Styling', 'section' => 'Default Layout', 'description' => 'Choose between the month calendar and the list as your schedule\'s default layout.', 'url' => $r['schedule_styling'].'#event-layout', 'category' => 'User Guide', 'keywords' => 'calendar grid list layout display default'],
            ['page' => 'Schedule Styling', 'section' => 'Hide Past Events', 'description' => 'Keep past events off your public schedule, from the Events row of the Style section.', 'url' => $r['schedule_styling'].'#hide-past-events', 'category' => 'User Guide', 'keywords' => 'hide past events upcoming only old events public schedule events row advanced'],
            ['page' => 'Schedule Styling', 'section' => 'Show Next Event', 'description' => 'Show or hide the card above the month calendar that names your next event.', 'url' => $r['schedule_styling'].'#next-event', 'category' => 'User Guide', 'keywords' => 'next event card upcoming featured today tomorrow above calendar hide remove events row'],
            ['page' => 'Schedule Styling', 'section' => 'Header Style', 'description' => 'Choose the Banner or Compact header style for your public schedule page.', 'url' => $r['schedule_styling'].'#header-style', 'category' => 'User Guide', 'keywords' => 'header style banner compact slim full-width bar'],
            ['page' => 'Schedule Styling', 'section' => 'Profile Image', 'description' => 'Upload and set your profile or logo image, which also becomes your favicon on Pro.', 'url' => $r['schedule_styling'].'#profile-image', 'category' => 'User Guide', 'keywords' => 'logo avatar photo favicon browser tab icon apple touch home screen'],
            ['page' => 'Schedule Styling', 'section' => 'Header Image', 'description' => 'Pick a header picture from a wall of presets, upload your own, or show a venue logo wall.', 'url' => $r['schedule_styling'].'#header-images', 'category' => 'User Guide', 'keywords' => 'banner cover header logo wall venue logos pictures thumbnails upload accent gradient'],
            ['page' => 'Schedule Styling', 'section' => 'Background', 'description' => 'Choose Aurora, a gradient by its swatch, a solid color, or an image background that fills the window.', 'url' => $r['schedule_styling'].'#backgrounds', 'category' => 'User Guide', 'keywords' => 'background color gradient image swatch rotation hex upload sharp blurry resolution'],
            ['page' => 'Schedule Styling', 'section' => 'Aurora', 'description' => 'Soft drifting lights drawn from your colors, sharp at any size, following light and dark mode.', 'url' => $r['schedule_styling'].'#aurora', 'category' => 'User Guide', 'keywords' => 'aurora background palette shuffle motion drift match header accent animated'],
            ['page' => 'Schedule Styling', 'section' => 'Accent Color', 'description' => 'Set the accent color for buttons and highlights, with a picker, a hex code or a preset.', 'url' => $r['schedule_styling'].'#color-scheme', 'category' => 'User Guide', 'keywords' => 'color accent theme palette scheme hex'],
            ['page' => 'Schedule Styling', 'section' => 'Font Family', 'description' => 'Choose a Google Font from a list that shows your schedule\'s name in each one.', 'url' => $r['schedule_styling'].'#typography', 'category' => 'User Guide', 'keywords' => 'font text typeface google fonts typography search'],
            ['page' => 'Schedule Styling', 'section' => 'Event Animation', 'description' => 'Animate event cards as visitors scroll your schedule.', 'url' => $r['schedule_styling'].'#list-animation', 'category' => 'User Guide', 'keywords' => 'animation motion scroll reveal rise focus slide deal shine curtain effect'],
            ['page' => 'Schedule Styling', 'section' => 'Event List Wording', 'description' => 'Word each event on your schedule\'s list like your Events graphic caption, or with a template of the list\'s own.', 'url' => $r['schedule_styling'].'#list-wording', 'category' => 'User Guide', 'keywords' => 'event list wording template caption whatsapp same text card title variables event_name custom wording list layout format standard events row advanced'],
            ['page' => 'Schedule Styling', 'section' => 'AI Style Generator', 'description' => 'Use AI to generate cohesive branding, images, colors, and fonts (Enterprise).', 'url' => $r['schedule_styling'].'#ai-style-generator', 'category' => 'User Guide', 'keywords' => 'ai generate style branding images automatic'],
            ['page' => 'Schedule Styling', 'section' => 'Remove Branding', 'description' => 'Remove the "Powered by Event Schedule" badge (Pro), and what a selfhosted install keeps.', 'url' => $r['schedule_styling'].'#remove-branding', 'category' => 'User Guide', 'keywords' => 'branding badge powered by white label selfhost attribution credit licence'],
            ['page' => 'Schedule Styling', 'section' => 'Custom CSS', 'description' => 'Add custom CSS for advanced styling (Pro).', 'url' => $r['schedule_styling'].'#custom-css', 'category' => 'User Guide', 'keywords' => 'css stylesheet custom code'],
            ['page' => 'Schedule Styling', 'section' => 'Hiding Sections', 'description' => 'Hide any panel on your schedule or event pages with one line of CSS (Pro).', 'url' => $r['schedule_styling'].'#hiding-sections', 'category' => 'User Guide', 'keywords' => 'hide remove section panel css display none notify me performer list'],
            ['page' => 'Schedule Styling', 'section' => 'Live Preview', 'description' => 'See your header, events, background and colors as you change them, in light and dark.', 'url' => $r['schedule_styling'].'#live-preview', 'category' => 'User Guide', 'keywords' => 'preview real-time light dark view schedule not saved'],

            // Managing Schedules
            ['page' => 'Managing Schedules', 'section' => 'Overview', 'description' => 'Introduction to the schedule admin panel and tabs.', 'url' => $r['managing_schedules'].'#overview', 'category' => 'User Guide', 'keywords' => 'admin panel dashboard'],
            ['page' => 'Managing Schedules', 'section' => 'Schedule', 'description' => 'Main calendar view with event management.', 'url' => $r['managing_schedules'].'#schedule', 'category' => 'User Guide', 'keywords' => 'calendar events manage filters search chips copy link'],
            ['page' => 'Managing Schedules', 'section' => 'Actions Menu', 'description' => 'The Actions menu beside the schedule\'s name: import events, scan an agenda, sync calendars, graphics, embedding, the audit log and deleting.', 'url' => $r['managing_schedules'].'#actions', 'category' => 'User Guide', 'keywords' => 'actions dropdown import sync graphic embed delete'],
            ['page' => 'Managing Schedules', 'section' => 'Templates', 'description' => 'Save an event as a reusable template (preset) and create new events from it in seconds (Pro).', 'url' => $r['managing_schedules'].'#templates', 'category' => 'User Guide', 'keywords' => 'template preset reusable recurring duplicate clone save as template'],
            ['page' => 'Managing Schedules', 'section' => 'Videos', 'description' => 'Assign YouTube videos for curator schedules.', 'url' => $r['managing_schedules'].'#videos', 'category' => 'User Guide', 'keywords' => 'youtube video curator'],
            ['page' => 'Managing Schedules', 'section' => 'Availability', 'description' => 'Set availability dates for talent schedules.', 'url' => $r['managing_schedules'].'#availability', 'category' => 'User Guide', 'keywords' => 'available dates talent booking'],
            ['page' => 'Managing Schedules', 'section' => 'Appointments', 'description' => 'The Appointments tab: set up bookable types, see your bookings, and share your booking page. One type is free; Pro adds more, paid bookings and scheduling rules.', 'url' => $r['managing_schedules'].'#appointments', 'category' => 'User Guide', 'keywords' => 'appointments tab bookings booking page tab free calendly'],
            ['page' => 'Managing Schedules', 'section' => 'Requests', 'description' => 'Manage public event request submissions and appointment bookings that need approval.', 'url' => $r['managing_schedules'].'#requests', 'category' => 'User Guide', 'keywords' => 'submissions approve reject appointment booking approval pending'],
            ['page' => 'Managing Schedules', 'section' => 'Feeds', 'description' => 'Keep a schedule up to date from a calendar address, an RSS feed or a page that lists events.', 'url' => $r['managing_schedules'].'#feeds', 'category' => 'User Guide', 'keywords' => 'feed rss atom ics ical webcal sync import subscribe standing automatic'],
            ['page' => 'Managing Schedules', 'section' => 'Adding a Feed', 'description' => 'Check an address, choose how new events arrive and what happens when one leaves the feed.', 'url' => $r['managing_schedules'].'#feeds-add', 'category' => 'User Guide', 'keywords' => 'add feed address check publish drafts'],
            ['page' => 'Managing Schedules', 'section' => 'Reviewing Drafts', 'description' => 'Publish, skip or edit the events a feed holds as drafts.', 'url' => $r['managing_schedules'].'#feeds-review', 'category' => 'User Guide', 'keywords' => 'review drafts publish skip publish all'],
            ['page' => 'Managing Schedules', 'section' => 'Feed Updates and Your Edits', 'description' => 'What a feed copies from its source, and why a field you changed is kept.', 'url' => $r['managing_schedules'].'#feeds-updates', 'category' => 'User Guide', 'keywords' => 'feed update edits overwrite keep changed'],
            ['page' => 'Managing Schedules', 'section' => 'When an Event Leaves a Feed', 'description' => 'Leave, cancel or remove an event that is no longer listed, and the decisions held for you.', 'url' => $r['managing_schedules'].'#feeds-gone', 'category' => 'User Guide', 'keywords' => 'feed removed cancelled gone decision registered'],
            ['page' => 'Managing Schedules', 'section' => 'When a Feed Cannot Be Read', 'description' => 'Retries, the emails a team gets, and when a feed is paused.', 'url' => $r['managing_schedules'].'#feeds-trouble', 'category' => 'User Guide', 'keywords' => 'feed failing paused resume read now error'],
            ['page' => 'Managing Schedules', 'section' => 'Followers', 'description' => 'Manage your audience, follow links and the Account badge.', 'url' => $r['managing_schedules'].'#followers', 'category' => 'User Guide', 'keywords' => 'subscribers audience follow account badge remove'],
            ['page' => 'Managing Schedules', 'section' => 'Team', 'description' => 'Invite team members as admins or viewers: who sees sales and refunds, who runs check-in, and who can scan tickets at the door.', 'url' => $r['managing_schedules'].'#team', 'category' => 'User Guide', 'keywords' => 'members permissions collaborate admin viewer access level staff door scanner sales refunds'],
            ['page' => 'Managing Schedules', 'section' => 'Transferring Ownership', 'description' => 'Hand a schedule and everything on it to another account, with the new owner accepting by email.', 'url' => $r['managing_schedules'].'#transfer-ownership', 'category' => 'User Guide', 'keywords' => 'transfer ownership hand over change owner give away new owner sell venue'],
            ['page' => 'Managing Schedules', 'section' => 'Promoters', 'description' => 'Give each person who sells for you their own link, and see the clicks, tickets, revenue and commission each one brought in.', 'url' => $r['managing_schedules'].'#promoters', 'category' => 'User Guide', 'keywords' => 'promoter promoters affiliate referral tracking link via street team commission ambassador sales attribution'],
            ['page' => 'Managing Schedules', 'section' => 'Plan', 'description' => 'View and manage your subscription plan.', 'url' => $r['managing_schedules'].'#plan', 'category' => 'User Guide', 'keywords' => 'subscription billing upgrade'],
            ['page' => 'Managing Schedules', 'section' => 'Ads on Free Schedules', 'description' => 'Why some sites show ads on free schedules, where they never appear, and how to switch them off.', 'url' => $r['managing_schedules'].'#plan-ads', 'category' => 'User Guide', 'keywords' => 'ads adsense advertising free plan remove ads ad-free upgrade pro'],
            ['page' => 'Managing Schedules', 'section' => 'Audit Log', 'description' => 'Track who changed what on your schedule, with filters and per-entry details.', 'url' => $r['managing_schedules'].'#audit-log', 'category' => 'User Guide', 'keywords' => 'audit log activity history changes tracking who'],
            ['page' => 'Managing Schedules', 'section' => 'Seating Plans', 'description' => 'Reusable seating plans on a Venue schedule: new plan, designer, duplicate, delete (Enterprise).', 'url' => $r['managing_schedules'].'#seating', 'category' => 'User Guide', 'keywords' => 'seating plans tab venue'],
            ['page' => 'Managing Schedules', 'section' => 'Filter Events', 'description' => 'Search and filter the admin calendar, filter chips, and Copy Link for a filtered public address.', 'url' => $r['managing_schedules'].'#schedule-filters', 'category' => 'User Guide', 'keywords' => 'filter events search chips copy link'],
            ['page' => 'Managing Schedules', 'section' => 'Curator Notices', 'description' => 'Duplicate venues and events in another timezone on a Curator schedule.', 'url' => $r['managing_schedules'].'#schedule-notices', 'category' => 'User Guide', 'keywords' => 'curator duplicate venues timezone notice'],
            ['page' => 'Managing Schedules', 'section' => 'When someone leaves the team', 'description' => 'What happens to events, appointment hosts, feeds and calendar sync when a member leaves, is removed or becomes a viewer.', 'url' => $r['managing_schedules'].'#team-leaving', 'category' => 'User Guide', 'keywords' => 'team member leave remove viewer events owner appointment host feeds paused calendar sync'],
            ['page' => 'Managing Schedules', 'section' => 'Other notices above the calendar', 'description' => 'The import band, calendar-sync notices and the network prompt on the Schedule tab.', 'url' => $r['managing_schedules'].'#schedule-other-notices', 'category' => 'User Guide', 'keywords' => 'notice import band calendar sync drafts network prompt schedule tab'],

            // Creating Events
            ['page' => 'Creating Events', 'section' => 'Manual Event Creation', 'description' => 'Create events manually with full control over details.', 'url' => $r['creating_events'].'#manual', 'category' => 'User Guide', 'keywords' => 'add new event create'],
            ['page' => 'Creating Events', 'section' => 'Notifying Attendees of Changes', 'description' => 'Email ticket buyers and the interest list when the date, venue or online link of an event changes, or when you cancel it.', 'url' => $r['creating_events'].'#notify-attendees', 'category' => 'User Guide', 'keywords' => 'notify attendees change date time venue moved rescheduled cancel event cancellation email notice buyers interest list'],
            ['page' => 'Creating Events', 'section' => 'Correcting the Translation', 'description' => 'Correct the automatic translation of an event\'s name and descriptions by hand, and keep your wording when the original changes.', 'url' => $r['creating_events'].'#translation', 'category' => 'User Guide', 'keywords' => 'translation translate second language edit correct fix wording portuguese manual override keep mine reset automatic'],
            ['page' => 'Creating Events', 'section' => 'The Event Tab', 'description' => 'Set the event name, date and time, location, flyer and description on the first tab of the event form.', 'url' => $r['creating_events'].'#details', 'category' => 'User Guide', 'keywords' => 'name date time description'],
            ['page' => 'Creating Events', 'section' => 'Event Visibility', 'description' => 'Choose who can see an event: Public, Draft, Internal, or Unlisted. Hidden events stay off your public schedule, the calendar feed, the subscriber digest, graphics and newsletters.', 'url' => $r['creating_events'].'#draft', 'category' => 'User Guide', 'keywords' => 'visibility draft internal unlisted public private unpublished hidden publish digest feed'],
            ['page' => 'Creating Events', 'section' => 'Location', 'description' => 'Pick a saved venue, enter a new one, find a venue by email or phone, or mark the event as online.', 'url' => $r['creating_events'].'#venue', 'category' => 'User Guide', 'keywords' => 'location place address map'],
            ['page' => 'Creating Events', 'section' => 'Participants', 'description' => 'Add performers, speakers, or participants. Naming someone who is not on Event Schedule yet creates a page for them.', 'url' => $r['creating_events'].'#participants', 'category' => 'User Guide', 'keywords' => 'performers speakers artists lineup members bill'],
            ['page' => 'Creating Events', 'section' => 'Pages Created for Others', 'description' => 'Naming a performer or venue who is not on Event Schedule creates a page for them, which they can claim. Your event page shows the whole lineup and links to it.', 'url' => $r['creating_events'].'#claim', 'category' => 'User Guide', 'keywords' => 'claim unclaimed claim this page is this you not me invitation page created for me remove my page profile someone made a page lineup approval'],
            ['page' => 'Creating Events', 'section' => 'Recurring Events', 'description' => 'Set up events that repeat on a schedule.', 'url' => $r['creating_events'].'#recurring', 'category' => 'User Guide', 'keywords' => 'repeat weekly monthly recurring'],
            ['page' => 'Creating Events', 'section' => 'Agenda', 'description' => 'Create agenda items and event parts.', 'url' => $r['creating_events'].'#agenda', 'category' => 'User Guide', 'keywords' => 'parts itinerary lineup schedule'],
            ['page' => 'Creating Events', 'section' => 'Listing', 'description' => 'Set an event\'s visibility, link, category, the other schedules it appears on and the calendars it is copied to.', 'url' => $r['creating_events'].'#listing', 'category' => 'User Guide', 'keywords' => 'visibility slug url category draft public also list on calendar sync'],
            ['page' => 'Creating Events', 'section' => 'Also List On', 'description' => 'Show the event on your other schedules and on schedules you follow that take requests.', 'url' => $r['creating_events'].'#schedules', 'category' => 'User Guide', 'keywords' => 'cross-list curator appearances'],
            ['page' => 'Creating Events', 'section' => 'Calendar Sync', 'description' => 'At the end of the Listing tab: see whether one event is on your Google or Outlook calendar, sync it now or remove it.', 'url' => $r['creating_events'].'#google-calendar', 'category' => 'User Guide', 'keywords' => 'google calendar sync outlook tab listing'],
            ['page' => 'Creating Events', 'section' => 'WhatsApp Integration', 'description' => 'Create an event by sending a WhatsApp message or a photo of a flyer (Enterprise).', 'url' => $r['creating_events'].'#whatsapp', 'category' => 'User Guide', 'keywords' => 'whatsapp chat message flyer photo phone twilio'],
            ['page' => 'Creating Events', 'section' => 'Ticket Configuration', 'description' => 'Free registration, Sell tickets or Tickets elsewhere, and what each opens.', 'url' => $r['creating_events'].'#tickets', 'category' => 'User Guide', 'keywords' => 'tickets pricing sell payment method stripe paypal registration'],
            ['page' => 'Creating Events', 'section' => 'Custom Fields', 'description' => 'Add custom information fields to events.', 'url' => $r['creating_events'].'#custom-fields', 'category' => 'User Guide', 'keywords' => 'custom fields metadata'],
            ['page' => 'Creating Events', 'section' => 'Internal & Unlisted Events', 'description' => 'Enterprise visibility options: keep events members-only (Internal) or reachable by direct link with an optional password (Unlisted).', 'url' => $r['creating_events'].'#privacy', 'category' => 'User Guide', 'keywords' => 'internal unlisted private password direct link visibility enterprise'],
            ['page' => 'Creating Events', 'section' => 'Page Content', 'description' => 'The Page content tab of an event: its Gallery and Sponsors rows.', 'url' => $r['creating_events'].'#page-content', 'category' => 'User Guide', 'keywords' => 'page content tab gallery sponsors photos logos'],
            ['page' => 'Creating Events', 'section' => 'Sponsors', 'description' => 'Choose which sponsors one event shows, in the Sponsors row of the Page content tab.', 'url' => $r['creating_events'].'#sponsors', 'category' => 'User Guide', 'keywords' => 'sponsors logos branding partners per-event override tab'],
            ['page' => 'Creating Events', 'section' => 'Gallery', 'description' => 'Add a photo gallery to an event in the Gallery row of the Page content tab: upload, arrange, caption and credit photos, or add approved fan photos.', 'url' => $r['creating_events'].'#gallery', 'category' => 'User Guide', 'keywords' => 'gallery photos images pictures multiple photos flyer album lightbox captions credit upload tab'],
            ['page' => 'Creating Events', 'section' => 'Engagement', 'description' => 'The Engagement tab of the event form: a row each for polls, fan content, feedback and carpool, saying what is in force and whether it follows the schedule.', 'url' => $r['creating_events'].'#engagement', 'category' => 'User Guide', 'keywords' => 'engagement tab rows same as schedule override polls fan content feedback carpool'],
            ['page' => 'Creating Events', 'section' => 'Polls', 'description' => 'Add multiple-choice polls to an event for signed-in guests to vote on (Pro).', 'url' => $r['creating_events'].'#polls', 'category' => 'User Guide', 'keywords' => 'voting survey poll vote results suggested options'],
            ['page' => 'Creating Events', 'section' => 'Feedback', 'description' => 'Control per-event feedback override settings.', 'url' => $r['creating_events'].'#feedback', 'category' => 'User Guide', 'keywords' => 'feedback rating review post-event override'],
            ['page' => 'Creating Events', 'section' => 'Fan Content', 'description' => 'Per-event comment, photo and video settings, and approving what guests send in.', 'url' => $r['creating_events'].'#fan-content', 'category' => 'User Guide', 'keywords' => 'user content videos submissions ugc'],
            ['page' => 'Creating Events', 'section' => 'AI Flyer Generation', 'description' => 'Generate event flyer images using AI (Enterprise).', 'url' => $r['creating_events'].'#ai-flyer', 'category' => 'User Guide', 'keywords' => 'ai flyer image generate poster design'],
            ['page' => 'Creating Events', 'section' => 'AI Details Generator', 'description' => 'Use AI to fill in an event category, flyer image and descriptions from its name (Enterprise).', 'url' => $r['creating_events'].'#ai-details-generator', 'category' => 'User Guide', 'keywords' => 'ai generate details description automatic category flyer short description'],
            ['page' => 'Creating Events', 'section' => 'Tabs of the Event Form', 'description' => 'The seven tabs of the event form, what each covers and when it appears.', 'url' => $r['creating_events'].'#tabs', 'category' => 'User Guide', 'keywords' => 'tabs event tickets participants agenda page content gallery sponsors listing calendar sync engagement'],
            ['page' => 'Creating Events', 'section' => 'Saving an Event', 'description' => 'What the save bar says, what saving removes, and what a refused save keeps.', 'url' => $r['creating_events'].'#saving', 'category' => 'User Guide', 'keywords' => 'save bar publish save draft unsaved changes cancel'],
            ['page' => 'Creating Events', 'section' => 'Importing an Agenda with AI', 'description' => 'Read an agenda from text or a photo into event parts from the Agenda tab\'s Import row.', 'url' => $r['creating_events'].'#agenda-import', 'category' => 'User Guide', 'keywords' => 'agenda import ai photo text parts'],
            ['page' => 'Creating Events', 'section' => 'Default curator schedules', 'description' => 'List every new event on chosen Curator schedules automatically, clones included.', 'url' => $r['creating_events'].'#default-curators', 'category' => 'User Guide', 'keywords' => 'default curators also list on automatic curator clone duplicate'],

            // AI Import
            ['page' => 'AI Import', 'section' => 'The Import Page', 'description' => 'Import events from a link, pasted text or a flyer image.', 'url' => $r['ai_import'].'#ai-import', 'category' => 'User Guide', 'keywords' => 'ai artificial intelligence import parse bring existing events'],
            ['page' => 'AI Import', 'section' => 'Importing from a Link', 'description' => 'Paste the address of an events page or a calendar and its events are read for you.', 'url' => $r['ai_import'].'#link-import', 'category' => 'User Guide', 'keywords' => 'link url website web page calendar feed ics ical webcal google calendar outlook meetup facebook instagram one-time copy time zone repeating recurring'],
            ['page' => 'AI Import', 'section' => 'Importing from Google Calendar', 'description' => 'Connect Google Calendar with read-only access, choose a calendar, and pick which of its events to add.', 'url' => $r['ai_import'].'#google-import', 'category' => 'User Guide', 'keywords' => 'google calendar gcal connect read-only private calendar choose events copy import main calendar different account'],
            ['page' => 'AI Import', 'section' => 'Importing from Text', 'description' => 'Paste event text and let AI extract the details.', 'url' => $r['ai_import'].'#text-import', 'category' => 'User Guide', 'keywords' => 'text paste extract parse'],
            ['page' => 'AI Import', 'section' => 'New Pages and Requests', 'description' => 'What saving an import does for the performers and venue it names: new claimable pages, or requests to schedules they run.', 'url' => $r['ai_import'].'#new-pages', 'category' => 'User Guide', 'keywords' => 'new page claim claimable unclaimed performer talent venue request approval approved list noindex'],
            ['page' => 'AI Import', 'section' => 'Importing from Images', 'description' => 'Upload flyers and extract event information with AI.', 'url' => $r['ai_import'].'#image-import', 'category' => 'User Guide', 'keywords' => 'image flyer photo scan ocr'],
            ['page' => 'AI Import', 'section' => 'Choosing What to Add', 'description' => 'Tick the events to add from the list a read returns, then add them in one go.', 'url' => $r['ai_import'].'#choosing-events', 'category' => 'User Guide', 'keywords' => 'list select all tick checkbox add events bulk several many progress duplicate already have'],
            ['page' => 'AI Import', 'section' => 'Undoing an Import', 'description' => 'Remove the events an import added, and what the panel after an import offers.', 'url' => $r['ai_import'].'#undo-import', 'category' => 'User Guide', 'keywords' => 'undo remove delete imported events mistake wrong time zone events added panel embed website'],
            ['page' => 'AI Import', 'section' => 'Custom AI Prompts', 'description' => 'Steer the parser with custom field prompts, your categories, the schedule\'s language and the import form fields.', 'url' => $r['ai_import'].'#custom-prompts', 'category' => 'User Guide', 'keywords' => 'prompt instructions customize'],
            ['page' => 'AI Import', 'section' => 'Importing from Eventbrite', 'description' => 'Copy events, ticket types and venues from an Eventbrite account with a private token (Pro).', 'url' => $r['ai_import'].'#eventbrite-import', 'category' => 'User Guide', 'keywords' => 'eventbrite import token migrate'],
            ['page' => 'AI Import', 'section' => 'The Event Card', 'description' => 'What a parsed event holds before you save it: its fields, venue matching and the duplicate warning.', 'url' => $r['ai_import'].'#event-card', 'category' => 'User Guide', 'keywords' => 'parsed event card venue matching duplicate'],
            ['page' => 'AI Import', 'section' => 'Daily Limits', 'description' => 'How many imports a schedule can read in a day on each plan.', 'url' => $r['ai_import'].'#daily-limits', 'category' => 'User Guide', 'keywords' => 'daily limit allowance cap imports'],

            // Sharing
            ['page' => 'Sharing', 'section' => 'Your Schedule URL', 'description' => 'Find your schedule\'s public address under its name in the admin panel, copy it and share it.', 'url' => $r['sharing'].'#schedule-url', 'category' => 'User Guide', 'keywords' => 'link url share'],
            ['page' => 'Sharing', 'section' => 'Embedding on Your Website', 'description' => 'Embed your schedule on external websites.', 'url' => $r['sharing'].'#embed', 'category' => 'User Guide', 'keywords' => 'embed iframe widget website'],
            ['page' => 'Sharing', 'section' => 'Embed URL Parameters', 'description' => 'Force a calendar or list layout, language, theme, category, sub-schedule or custom field value per embed.', 'url' => $r['sharing'].'#embed-parameters', 'category' => 'User Guide', 'keywords' => 'layout grid list calendar url parameter query string embed dark light theme lang category month year form subscribe custom field custom_n room filter link qr code'],
            ['page' => 'Sharing', 'section' => 'Embedding a Signup Form', 'description' => 'Put the email sign-up form on your own website so visitors can subscribe to your new events.', 'url' => $r['sharing'].'#embed-subscribe-form', 'category' => 'User Guide', 'keywords' => 'embed signup sign up subscribe form newsletter email mailing list website iframe widget subscribers'],
            ['page' => 'Sharing', 'section' => 'Social Media Sharing', 'description' => 'Share your schedule on social media platforms.', 'url' => $r['sharing'].'#social', 'category' => 'User Guide', 'keywords' => 'facebook twitter instagram social'],
            ['page' => 'Sharing', 'section' => 'Building Followers', 'description' => 'Grow your audience: the Follow button, the email sign-up panel, and the account a confirmed sign-up sets up.', 'url' => $r['sharing'].'#followers', 'category' => 'User Guide', 'keywords' => 'followers subscribers grow audience sign up keep me posted stay up to date email subscribe account'],
            ['page' => 'Sharing', 'section' => 'Calendar Subscriptions', 'description' => 'Let guests subscribe to a live iCal feed of your upcoming events from the Add to Calendar menu or the sign-up panel, or copy the iCal and RSS URLs.', 'url' => $r['sharing'].'#calendar-feeds', 'category' => 'User Guide', 'keywords' => 'ical rss feed subscribe calendar live feed subscribe to all events add to calendar'],
            ['page' => 'Sharing', 'section' => 'QR Codes', 'description' => 'Print a QR code that opens your schedule with the sign-up form ready.', 'url' => $r['sharing'].'#qr-code', 'category' => 'User Guide', 'keywords' => 'qr code scan print poster flyer sign up'],
            ['page' => 'Sharing', 'section' => 'Embed Troubleshooting', 'description' => 'Fix common embedding issues.', 'url' => $r['sharing'].'#troubleshooting', 'category' => 'User Guide', 'keywords' => 'troubleshoot fix embed problem'],
            ['page' => 'Sharing', 'section' => 'Embed Calendar (Feature Page)', 'description' => 'Embed your event calendar on any website with one line of code.', 'url' => $r['embed_calendar_feature'], 'category' => 'User Guide', 'keywords' => 'embed calendar iframe widget website'],
            ['page' => 'Sharing', 'section' => 'Managing Followers', 'description' => 'The Followers tab: its three figures, the subscriber and follower lists, Embed Signup Form and QR Code.', 'url' => $r['sharing'].'#managing-followers', 'category' => 'User Guide', 'keywords' => 'followers tab subscribers list qr code embed signup form'],
            ['page' => 'Sharing', 'section' => 'Subscribers and Followers', 'description' => 'Following and subscribing are two lists: who is on each and what each one receives.', 'url' => $r['sharing'].'#followers-two-lists', 'category' => 'User Guide', 'keywords' => 'follow subscribe difference email list'],

            // Newsletters
            ['page' => 'Newsletters', 'section' => 'Overview', 'description' => 'Send branded email newsletters to your audience.', 'url' => $r['newsletters'].'#overview', 'category' => 'User Guide', 'keywords' => 'email newsletter campaign'],
            ['page' => 'Newsletters', 'section' => 'Newsletter Builder', 'description' => 'The three-tab builder (Content, Style, Settings), the live preview that follows the open block, and the bar that saves, tests and sends.', 'url' => $r['newsletters'].'#newsletter-builder', 'category' => 'User Guide', 'keywords' => 'builder editor compose preview text preheader inbox line desktop mobile phone preview save unsaved review and send undo move block'],
            ['page' => 'Newsletters', 'section' => 'Block Types', 'description' => 'Available content blocks: heading, text, events, button, image, video, quote, sponsors, poll, divider, spacer, social links, profile image and header banner.', 'url' => $r['newsletters'].'#block-types', 'category' => 'User Guide', 'keywords' => 'blocks content heading text button image video quote sponsors poll divider spacer social profile image header banner events to list date range next 7 days next 30 days this month next month'],
            ['page' => 'Newsletters', 'section' => 'Templates', 'description' => 'The five designs, and the Templates tab for newsletters you save to start from.', 'url' => $r['newsletters'].'#templates', 'category' => 'User Guide', 'keywords' => 'template design preset modern classic minimal bold compact save as template'],
            ['page' => 'Newsletters', 'section' => 'Style Customization', 'description' => 'Customize colors, fonts, and button styles.', 'url' => $r['newsletters'].'#style-customization', 'category' => 'User Guide', 'keywords' => 'color font button style design system font dark mode'],
            ['page' => 'Newsletters', 'section' => 'Email Subscribers', 'description' => 'Collect email addresses from visitors, give them an account when they confirm, and send an automatic digest when you publish new events.', 'url' => $r['newsletters'].'#email-subscribers', 'category' => 'User Guide', 'keywords' => 'subscribers email subscribe sign up audience mailing list new event announcement notify automatic digest opt in confirm'],
            ['page' => 'Newsletters', 'section' => 'Recipients & Segments', 'description' => 'Choose audiences: followers, email subscribers, ticket buyers, waitlists, sub-schedules or manual lists. An event\'s interest list is not a segment.', 'url' => $r['newsletters'].'#recipients', 'category' => 'User Guide', 'keywords' => 'recipients segments audience list subscribers followers ticket buyers waitlist interest list who receives default everyone'],
            ['page' => 'Newsletters', 'section' => 'Managing Segments', 'description' => 'The Segments tab: create, rename and delete audience segments, and change the event or sub-schedule one is narrowed to.', 'url' => $r['newsletters'].'#managing-segments', 'category' => 'User Guide', 'keywords' => 'segments create edit delete reusable event filter sub-schedule'],
            ['page' => 'Newsletters', 'section' => 'Importing Emails', 'description' => 'Bulk import contacts via form entry, paste, or CSV upload.', 'url' => $r['newsletters'].'#importing-emails', 'category' => 'User Guide', 'keywords' => 'import csv paste emails bulk contacts'],
            ['page' => 'Newsletters', 'section' => 'Sending', 'description' => 'Review and send: who it goes to, how many, and what is left of the allowance, then send now or schedule it; send a test to yourself first. On eventschedule.com a send to more than 50 recipients needs email settings or a verified phone.', 'url' => $r['newsletters'].'#sending', 'category' => 'User Guide', 'keywords' => 'send schedule deliver review test verification verify phone smtp email settings 50 recipients refused timezone'],
            ['page' => 'Newsletters', 'section' => 'A/B Testing', 'description' => 'Send two versions of a newsletter to a sample, and the better one to everyone else after the wait.', 'url' => $r['newsletters'].'#ab-testing', 'category' => 'User Guide', 'keywords' => 'ab test split experiment sample winner variant'],
            ['page' => 'Newsletters', 'section' => 'Analytics', 'description' => 'A sent newsletter\'s statistics: sent, opens, clicks, failed, unsubscribed, top links and recipients, and the email itself.', 'url' => $r['newsletters'].'#analytics', 'category' => 'User Guide', 'keywords' => 'opens clicks metrics performance unsubscribed view email progress'],
            ['page' => 'Newsletters', 'section' => 'Managing Newsletters', 'description' => 'The Newsletters tab: statuses, sorting, cloning, deleting and unsubscribes.', 'url' => $r['newsletters'].'#managing', 'category' => 'User Guide', 'keywords' => 'drafts manage list'],
            ['page' => 'Newsletters', 'section' => 'Monthly Allowance', 'description' => '10, 100 or 1,000 newsletter emails a month by plan, counted per recipient.', 'url' => $r['newsletters'].'#allowance', 'category' => 'User Guide', 'keywords' => 'allowance limit emails per month recipients'],
            ['page' => 'Newsletters', 'section' => 'Saving Your Own Templates', 'description' => 'Save a newsletter\'s blocks and style as a template, and use, edit or delete it on the Templates tab.', 'url' => $r['newsletters'].'#saved-templates', 'category' => 'User Guide', 'keywords' => 'save as template templates tab reuse'],
            ['page' => 'Newsletters', 'section' => 'Repeating Newsletters', 'description' => 'Send a newsletter every week or every month on its own, with the Events block listing that week or month, and {month} in the subject naming it.', 'url' => $r['newsletters'].'#repeating', 'category' => 'User Guide', 'keywords' => 'repeat repeating recurring automatic automatically monthly weekly every month first day of the month this month next month date range month placeholder stop repeating skipped'],
            ['page' => 'Newsletters', 'section' => 'Verification Above 50 Recipients', 'description' => 'Sending to more than 50 people needs a verified phone number or your own email settings.', 'url' => $r['newsletters'].'#verification', 'category' => 'User Guide', 'keywords' => 'verify phone 50 recipients email settings'],

            // Tickets
            ['page' => 'Selling Tickets', 'section' => 'General Ticketing', 'description' => 'Overview of ticket sales setup and features.', 'url' => $r['tickets'].'#general', 'category' => 'User Guide', 'keywords' => 'ticketing overview sell'],
            ['page' => 'Selling Tickets', 'section' => 'Ticket Presets', 'description' => 'Start an event\'s tickets from a named preset, save a setup as one, and have the team pick from them.', 'url' => $r['tickets'].'#ticket-presets', 'category' => 'User Guide', 'keywords' => 'preset presets template default ticket setup regular special save as default team staff pick price lock'],
            ['page' => 'Selling Tickets', 'section' => 'Tickets Elsewhere', 'description' => 'Link to external registration pages and display event pricing, coupon code and discount.', 'url' => $r['tickets'].'#external', 'category' => 'User Guide', 'keywords' => 'external registration url price coupon discount link'],
            ['page' => 'Selling Tickets', 'section' => 'Free Registration', 'description' => 'Enable free event registration with optional capacity limits.', 'url' => $r['tickets'].'#registration', 'category' => 'User Guide', 'keywords' => 'registration rsvp free sign up attend'],
            ['page' => 'Selling Tickets', 'section' => 'Sell Tickets', 'description' => 'Set up paid or multi-type ticketing for events.', 'url' => $r['tickets'].'#ticketing', 'category' => 'User Guide', 'keywords' => 'ticketing setup paid pro sell'],
            ['page' => 'Selling Tickets', 'section' => 'Start with a free trial', 'description' => 'Save and start free trial: Pro starts with a free trial, once per account, and a plan covers one schedule.', 'url' => $r['tickets'].'#selling-trial', 'category' => 'User Guide', 'keywords' => 'trial free trial sell paid tickets plan pro upgrade checkout charged card once per account second schedule per schedule'],
            ['page' => 'Selling Tickets', 'section' => 'Ticket Types', 'description' => 'Configure different ticket type options.', 'url' => $r['tickets'].'#ticket-types', 'category' => 'User Guide', 'keywords' => 'general vip types tiers'],
            ['page' => 'Selling Tickets', 'section' => 'Buying Several Events at Once', 'description' => 'Let a visitor collect tickets to several events and pay for them in one checkout.', 'url' => $r['tickets'].'#cart', 'category' => 'User Guide', 'keywords' => 'cart basket multiple events one checkout add to cart order several'],
            ['page' => 'Selling Tickets', 'section' => 'Volume Discount', 'description' => 'Discount a ticket type once a buyer takes a minimum quantity, as a percentage or a fixed amount.', 'url' => $r['tickets'].'#volume-discount', 'category' => 'User Guide', 'keywords' => 'volume discount bulk group minimum quantity percentage fixed amount stacks promo code'],
            ['page' => 'Selling Tickets', 'section' => 'Max Per Order', 'description' => 'Cap how many of one ticket type a single buyer can take in one order.', 'url' => $r['tickets'].'#max-per-order', 'category' => 'User Guide', 'keywords' => 'max per order limit cap quantity per buyer add limit'],
            ['page' => 'Selling Tickets', 'section' => 'Free Tickets', 'description' => 'Offer free tickets by setting the price to zero.', 'url' => $r['tickets'].'#free-events', 'category' => 'User Guide', 'keywords' => 'free no cost zero price ticket'],
            ['page' => 'Selling Tickets', 'section' => 'Payment Processing', 'description' => 'Configure payment methods for ticket sales.', 'url' => $r['tickets'].'#payment', 'category' => 'User Guide', 'keywords' => 'payment stripe paypal invoice ninja payfast razorpay gateway'],
            ['page' => 'Selling Tickets', 'section' => 'PayPal', 'description' => 'Connect PayPal to sell tickets.', 'url' => $r['tickets'].'#paypal', 'category' => 'User Guide', 'keywords' => 'paypal client id secret sandbox test mode developer dashboard apps and credentials orders capture currencies supported pending review echeck refund partial refund multi-event cart installments gift cards'],
            ['page' => 'Selling Tickets', 'section' => 'When a PayPal checkout is refused', 'description' => 'Why PayPal may not appear, and what a buyer sees when a checkout cannot start.', 'url' => $r['tickets'].'#paypal-refused', 'category' => 'User Guide', 'keywords' => 'paypal refused declined unsupported currency no longer available amount mismatch seats released reservation expired'],
            ['page' => 'Selling Tickets', 'section' => 'Razorpay', 'description' => 'Connect Razorpay to sell tickets in Indian rupees by UPI, card, netbanking or wallet.', 'url' => $r['tickets'].'#razorpay', 'category' => 'User Guide', 'keywords' => 'razorpay india inr indian rupees upi netbanking wallet card key id key secret rzp_live rzp_test test mode payment link refund partial refund multi-event cart installments gift cards provided by this installation'],
            ['page' => 'Selling Tickets', 'section' => 'Why the Razorpay webhook matters', 'description' => 'Add the Razorpay webhook so a buyer who pays and never comes back still gets a ticket, and an unfinished checkout gives its seats back.', 'url' => $r['tickets'].'#razorpay-webhook', 'category' => 'User Guide', 'keywords' => 'razorpay webhook secret payment_link.paid payment_link.expired upi app closed tab seats held expire unpaid tickets mark paid no webhook test mode live mode'],
            ['page' => 'Selling Tickets', 'section' => 'When a Razorpay checkout is refused', 'description' => 'Why Razorpay may not appear, and what a buyer sees when a checkout cannot start.', 'url' => $r['tickets'].'#razorpay-refused', 'category' => 'User Guide', 'keywords' => 'razorpay refused minimum one rupee inr currency no longer available try again amount mismatch seats released'],
            ['page' => 'Selling Tickets', 'section' => 'Payfast', 'description' => 'Connect Payfast to sell tickets in South African rand.', 'url' => $r['tickets'].'#payfast', 'category' => 'User Guide', 'keywords' => 'payfast south africa zar rand instant eft capitec pay merchant id merchant key passphrase sandbox test mode itn instant transaction notification notify url payfast refund provided by this installation'],
            ['page' => 'Selling Tickets', 'section' => 'Invoice Ninja Modes', 'description' => 'Invoice mode vs. payment link mode for Invoice Ninja.', 'url' => $r['tickets'].'#invoiceninja-modes', 'category' => 'User Guide', 'keywords' => 'invoice ninja mode payment link'],
            ['page' => 'Selling Tickets', 'section' => 'Checkout', 'description' => 'The Checkout row (Registration form on free registration): phone number, custom fields, individual tickets, the terms link and ticket notes.', 'url' => $r['tickets'].'#checkout', 'category' => 'User Guide', 'keywords' => 'checkout registration form phone individual tickets notes terms options settings'],
            ['page' => 'Selling Tickets', 'section' => 'Sales Rules', 'description' => 'The Sales rules row: selling after the event starts, showing sold-out types and expiring unpaid orders. Sales start and end dates are set on each ticket type.', 'url' => $r['tickets'].'#sales-rules', 'category' => 'User Guide', 'keywords' => 'sales rules sales dates start end expire unpaid sell after start show unavailable options settings'],
            ['page' => 'Selling Tickets', 'section' => 'Custom Checkout Fields', 'description' => 'Add custom fields to the checkout form.', 'url' => $r['tickets'].'#checkout-fields', 'category' => 'User Guide', 'keywords' => 'checkout form fields custom'],
            ['page' => 'Selling Tickets', 'section' => 'Installment Payments', 'description' => 'Let buyers pay for an expensive ticket over monthly installments.', 'url' => $r['tickets'].'#installments', 'category' => 'User Guide', 'keywords' => 'installment installments monthly payment plan instalment split pay later spread cost deposit'],
            ['page' => 'Selling Tickets', 'section' => 'Promo Codes', 'description' => 'Create discount codes for ticket purchases.', 'url' => $r['tickets'].'#promo-codes', 'category' => 'User Guide', 'keywords' => 'promo discount coupon code'],
            ['page' => 'Selling Tickets', 'section' => 'Add-ons', 'description' => 'Offer optional items customers can add to their ticket purchase, such as parking, merchandise, or meal packages.', 'url' => $r['tickets'].'#add-ons', 'category' => 'User Guide', 'keywords' => 'add-ons optional extras merchandise parking meals'],
            ['page' => 'Allocated Seating', 'section' => 'What allocated seating is', 'description' => 'Sell the seats themselves rather than a quantity: draw your room once and reuse it across every date.', 'url' => $r['allocated_seating'].'#overview', 'category' => 'User Guide', 'keywords' => 'allocated reserved seating seats seat map assigned numbered auditorium theatre stadium'],
            ['page' => 'Allocated Seating', 'section' => 'Build the plan', 'description' => 'Levels, sections, rows, tables, standing areas, seat kinds, gangways and wheelchair spaces.', 'url' => $r['allocated_seating'].'#build', 'category' => 'User Guide', 'keywords' => 'seating plan designer rows sections levels tables wheelchair companion accessible aisle gangway layout curve'],
            ['page' => 'Allocated Seating', 'section' => 'How rows and seats are numbered', 'description' => 'Lettered or numbered rows, letters to skip, and seats running left to right, right to left or odds and evens from the middle.', 'url' => $r['allocated_seating'].'#build-numbering', 'category' => 'User Guide', 'keywords' => 'row labels seat numbering odds evens lettered numbered rename label preview'],
            ['page' => 'Allocated Seating', 'section' => 'The stage, and labels on the map', 'description' => 'Add a stage marker so buyers know which way they face, plus text labels for bars and entrances.', 'url' => $r['allocated_seating'].'#build-decorations', 'category' => 'User Guide', 'keywords' => 'stage marker decoration text label bar entrance orient facing rotate'],
            ['page' => 'Allocated Seating', 'section' => 'Put it on sale', 'description' => 'Attach a plan to an event and price each band; the plan sets the ticket quantity.', 'url' => $r['allocated_seating'].'#sell', 'category' => 'User Guide', 'keywords' => 'price band ticket type quantity attach seating plan event'],
            ['page' => 'Allocated Seating', 'section' => 'Change a single date', 'description' => 'Edit one performance without touching the rest of the run, and revert it again.', 'url' => $r['allocated_seating'].'#one-date', 'category' => 'User Guide', 'keywords' => 'modify this date only revert per date occurrence recurring'],
            ['page' => 'Allocated Seating', 'section' => 'What the buyer sees', 'description' => 'Best available or choose your own seats, the hold countdown, map and list views.', 'url' => $r['allocated_seating'].'#buying', 'category' => 'User Guide', 'keywords' => 'seat picker choose your own best available hold countdown map list'],
            ['page' => 'Allocated Seating', 'section' => 'Rules that refuse a selection', 'description' => 'The single-seat rule, accessible seating and whole-table bookings.', 'url' => $r['allocated_seating'].'#rules', 'category' => 'User Guide', 'keywords' => 'single seat orphan rule wheelchair companion whole table refused'],
            ['page' => 'Allocated Seating', 'section' => 'Run the door', 'description' => 'The box office console: hold seats back, book by phone, move or release seats, and refund the share of a released seat from Sales.', 'url' => $r['allocated_seating'].'#box-office', 'category' => 'User Guide', 'keywords' => 'box office house seats block reserve exchange release phone booking comp lookup select whole row arrived refund partial'],
            ['page' => 'Allocated Seating', 'section' => 'Seating plan report', 'description' => 'Printable seat by seat report of who is sitting where, or a CSV.', 'url' => $r['allocated_seating'].'#report', 'category' => 'User Guide', 'keywords' => 'seating report print front of house csv manifest'],
            ['page' => 'Allocated Seating', 'section' => 'Every option and limit', 'description' => 'Reference table of every seating option, choice and size limit.', 'url' => $r['allocated_seating'].'#reference', 'category' => 'User Guide', 'keywords' => 'reference options limits seat kinds table booking row labels curve caps'],
            ['page' => 'Allocated Seating', 'section' => 'The Seating plans tab', 'description' => 'A venue schedule\'s plan cards: open the designer, duplicate a plan or delete it.', 'url' => $r['allocated_seating'].'#plans-tab', 'category' => 'User Guide', 'keywords' => 'seating plans tab new plan duplicate delete open designer'],
            ['page' => 'Allocated Seating', 'section' => 'When a date stops following the plan', 'description' => 'Each date takes its own copy of the plan the first time it is opened, and later edits to the plan do not reach it.', 'url' => $r['allocated_seating'].'#date-copy', 'category' => 'User Guide', 'keywords' => 'snapshot copy date plan changes not applied'],
            ['page' => 'Selling Tickets', 'section' => 'Managing Sales', 'description' => 'View and manage ticket sale records.', 'url' => $r['tickets'].'#managing-sales', 'category' => 'User Guide', 'keywords' => 'sales orders manage view'],
            ['page' => 'Selling Tickets', 'section' => 'Refunds', 'description' => 'Refund a Stripe, PayPal or Razorpay sale in full or in part from the Sales page, or mark a sale paid another way as refunded.', 'url' => $r['tickets'].'#refunds', 'category' => 'User Guide', 'keywords' => 'refund partial refund money back return stripe paypal mark as refunded refunded so far available to refund installment plan'],
            ['page' => 'Selling Tickets', 'section' => 'Filtering Sales', 'description' => 'Search and filter sales by buyer name, email, or event name.', 'url' => $r['tickets'].'#filtering-sales', 'category' => 'User Guide', 'keywords' => 'filter search sales buyer name email'],
            ['page' => 'Selling Tickets', 'section' => 'Sale Notifications', 'description' => 'Get notified when tickets are purchased.', 'url' => $r['tickets'].'#sale-notifications', 'category' => 'User Guide', 'keywords' => 'notification alert email'],
            ['page' => 'Selling Tickets', 'section' => 'Export Sales Data', 'description' => 'Export ticket sales as CSV.', 'url' => $r['tickets'].'#export', 'category' => 'User Guide', 'keywords' => 'export csv download data'],
            ['page' => 'Selling Tickets', 'section' => 'Check-In System', 'description' => 'QR code-based check-in for attendees.', 'url' => $r['tickets'].'#check-in', 'category' => 'User Guide', 'keywords' => 'check-in qr code scan attendee camera door'],
            ['page' => 'Selling Tickets', 'section' => 'Check-In Dashboard', 'description' => 'Dashboard for managing event check-ins.', 'url' => $r['tickets'].'#checkin-dashboard', 'category' => 'User Guide', 'keywords' => 'dashboard check-in manage'],
            ['page' => 'Selling Tickets', 'section' => 'Donations and Pay What You Want', 'description' => 'Let buyers add a donation at checkout, or pay what they want for a free ticket.', 'url' => $r['tickets'].'#donations', 'category' => 'User Guide', 'keywords' => 'donation donations pay what you want pwyw tip suggested amount fundraising sliding scale'],
            ['page' => 'Selling Tickets', 'section' => 'Wallet Passes', 'description' => 'Let buyers save their ticket to Google Wallet or Apple Wallet and scan it at the door.', 'url' => $r['tickets'].'#wallet-passes', 'category' => 'User Guide', 'keywords' => 'google wallet pass phone add to wallet apple passbook qr'],
            ['page' => 'Selling Tickets', 'section' => 'Waitlist', 'description' => 'Manage waitlists for sold-out events.', 'url' => $r['tickets'].'#waitlist', 'category' => 'User Guide', 'keywords' => 'waitlist waiting list sold out'],
            ['page' => 'Selling Tickets', 'section' => 'Interest List', 'description' => 'Switch on the "Notify me" card so visitors can leave an email to hear when an event\'s tickets go on sale, if it is cancelled, and shortly before it starts.', 'url' => $r['tickets'].'#interest-list', 'category' => 'User Guide', 'keywords' => 'interest notify me tell me when tickets go on sale tell me if anything changes remind reminder cancelled change notice email capture waiting turn on enable show notify me card'],
            ['page' => 'Selling Tickets', 'section' => 'Post-Event Feedback', 'description' => 'Collect star ratings and comments from attendees after events.', 'url' => $r['tickets'].'#feedback', 'category' => 'User Guide', 'keywords' => 'feedback rating stars review comment post-event'],
            ['page' => 'Selling Tickets', 'section' => 'Financial Information', 'description' => 'Refunds, taxes, processing fees, cancelled events and payouts.', 'url' => $r['tickets'].'#financial', 'category' => 'User Guide', 'keywords' => 'revenue money financial report'],
            ['page' => 'Selling Tickets', 'section' => 'Embed Widget', 'description' => 'Embed a ticket purchase or RSVP form on any website.', 'url' => $r['tickets'].'#embed-widget', 'category' => 'User Guide', 'keywords' => 'embed widget iframe ticket rsvp website'],
            ['page' => 'Selling Tickets', 'section' => 'Embed Tickets (Feature Page)', 'description' => 'Embed a ticket purchase or RSVP form on any website with one line of code.', 'url' => $r['embed_tickets_feature'], 'category' => 'User Guide', 'keywords' => 'embed tickets iframe widget website sell rsvp purchase'],
            ['page' => 'Selling Tickets', 'section' => 'The Tickets Tab', 'description' => 'The three tiles, Not needed, and what a save removes.', 'url' => $r['tickets'].'#tickets-tab', 'category' => 'User Guide', 'keywords' => 'tickets tab tiles free registration sell tickets tickets elsewhere not needed'],
            ['page' => 'Selling Tickets', 'section' => 'What Each Plan Includes', 'description' => 'Which ticketing features are on Free and which need Pro.', 'url' => $r['tickets'].'#plan-table', 'category' => 'User Guide', 'keywords' => 'plan free pro ticketing features'],
            ['page' => 'Selling Tickets', 'section' => 'The Payment Row', 'description' => 'An event\'s payment method, currency and instructions, and the warnings when a method is not connected.', 'url' => $r['tickets'].'#payment-row', 'category' => 'User Guide', 'keywords' => 'payment row method currency instructions'],
            ['page' => 'Selling Tickets', 'section' => 'Connecting Stripe', 'description' => 'Connect Stripe in Settings so an event can take card payments.', 'url' => $r['tickets'].'#stripe', 'category' => 'User Guide', 'keywords' => 'stripe connect card payments'],
            ['page' => 'Selling Tickets', 'section' => 'The Sales List', 'description' => 'The columns of the Sales list and what opening a row shows.', 'url' => $r['tickets'].'#sales-list', 'category' => 'User Guide', 'keywords' => 'sales list columns customer event total status date'],
            ['page' => 'Selling Tickets', 'section' => 'Sale Actions', 'description' => 'The three-dot menu on a sale: mark paid, refund, cancel, resend and more, and when each is offered.', 'url' => $r['tickets'].'#sale-actions', 'category' => 'User Guide', 'keywords' => 'sale actions mark paid refund cancel resend'],
            ['page' => 'Selling Tickets', 'section' => 'What the Scanner Shows', 'description' => 'Each result the ticket scanner can give at the door, and what it means.', 'url' => $r['tickets'].'#scan-results', 'category' => 'User Guide', 'keywords' => 'scanner scan result valid already checked in another event another date admit anyway overdue'],
            ['page' => 'Selling Tickets', 'section' => 'Currency Lock', 'description' => 'Why an event\'s currency cannot change once it has sales.', 'url' => $r['tickets'].'#currency-lock', 'category' => 'User Guide', 'keywords' => 'currency lock change sales'],
            ['page' => 'Selling Tickets', 'section' => 'Viewing Feedback', 'description' => 'The Feedback tab of Sales: the figures, the queue and the responses.', 'url' => $r['tickets'].'#feedback-tab', 'category' => 'User Guide', 'keywords' => 'feedback tab responses ratings export'],
            ['page' => 'Selling Tickets', 'section' => 'Tracking Installments', 'description' => 'Follow each installment plan and its payments on the Installments tab of Sales.', 'url' => $r['tickets'].'#installments-tracking', 'category' => 'User Guide', 'keywords' => 'installments tab plans payments due'],

            // Subscriptions & Passes
            ['page' => 'Subscriptions & Passes', 'section' => 'Subscriptions & Passes', 'description' => 'Sell one pass a guest pays for once and reuses across many events.', 'url' => $r['subscriptions'].'#overview', 'category' => 'User Guide', 'keywords' => 'subscription pass multi-use membership punch card visit festival season recurring reuse'],
            ['page' => 'Subscriptions & Passes', 'section' => 'How It Works', 'description' => 'The buy, scan, and track lifecycle of a pass.', 'url' => $r['subscriptions'].'#how-it-works', 'category' => 'User Guide', 'keywords' => 'how it works lifecycle buy scan track visits'],
            ['page' => 'Subscriptions & Passes', 'section' => 'Subscription Types', 'description' => 'Visit pass, membership, festival pass, and season pass.', 'url' => $r['subscriptions'].'#types', 'category' => 'User Guide', 'keywords' => 'visit pass membership festival season punch card unlimited type'],
            ['page' => 'Subscriptions & Passes', 'section' => 'Covered Events', 'description' => 'Choose which events a pass works at: all events, a sub-schedule, or specific events.', 'url' => $r['subscriptions'].'#coverage', 'category' => 'User Guide', 'keywords' => 'coverage covered all events sub-schedule specific cross-event'],
            ['page' => 'Subscriptions & Passes', 'section' => 'Advance Booking', 'description' => 'Let pass holders reserve a seat for specific dates in advance, sharing one pool with regular ticket sales so you never oversell.', 'url' => $r['subscriptions'].'#advance-booking', 'category' => 'User Guide', 'keywords' => 'advance booking reserve seat ahead capacity overbooking shared pool book dates per-occurrence cap'],
            ['page' => 'Subscriptions & Passes', 'section' => 'Cancellation Policy', 'description' => 'Set a cancellation deadline for booked dates and decide whether a late cancellation forfeits the visit or is blocked, so no-shows do not cost you seats.', 'url' => $r['subscriptions'].'#cancellation-policy', 'category' => 'User Guide', 'keywords' => 'cancellation policy deadline cutoff late cancel forfeit block no-show credit visit back seat released waiting list'],
            ['page' => 'Subscriptions & Passes', 'section' => 'Admissions Per Event', 'description' => 'Let one pass admit more than one person per event - for example the holder plus a guest - without using extra visits.', 'url' => $r['subscriptions'].'#admissions-per-event', 'category' => 'User Guide', 'keywords' => 'admissions per event admit guest bring a guest plus one party size scan twice couple family membership holder companion'],
            ['page' => 'Subscriptions & Passes', 'section' => 'Redeeming Passes', 'description' => 'Scan a pass at the door and read the result.', 'url' => $r['subscriptions'].'#redeeming', 'category' => 'User Guide', 'keywords' => 'redeem scan check-in door event selector visit already expired not covered'],
            ['page' => 'Subscriptions & Passes', 'section' => 'Tracking Subscriptions', 'description' => 'See who used a pass, how many visits, and at which events.', 'url' => $r['subscriptions'].'#monitoring', 'category' => 'User Guide', 'keywords' => 'track usage visits subscriptions tab subscribers report monitor'],
            ['page' => 'Subscriptions & Passes', 'section' => 'Passes and Individual tickets', 'description' => 'An event with a pass type cannot use Individual tickets.', 'url' => $r['subscriptions'].'#pass-individual-tickets', 'category' => 'User Guide', 'keywords' => 'pass individual tickets disabled switch off'],

            // Gift Cards
            ['page' => 'Appointments', 'section' => 'Overview', 'description' => 'Let guests book time with you on a public page: appointment types, weekly hours, and a shareable booking link.', 'url' => $r['appointments'].'#overview', 'category' => 'User Guide', 'keywords' => 'appointments booking calendly book a time one on one slots meeting'],
            ['page' => 'Appointments', 'section' => 'Appointment Types', 'description' => 'Create appointment types with a name, any duration and a start-time interval; one is free, more and pricing are Pro. Toggle them active or inactive.', 'url' => $r['appointments'].'#appointment-types', 'category' => 'User Guide', 'keywords' => 'appointment type duration create toggle active inactive intro call session start times interval slot'],
            ['page' => 'Appointments', 'section' => 'Hosts', 'description' => 'Book a type with one person on your team (Enterprise): their bookings and days away block its times, they get the booking emails, and guests see who they are meeting.', 'url' => $r['appointments'].'#hosts', 'category' => 'User Guide', 'keywords' => 'host team member staff admin with sam hours days away calendar per person round robin left the team choose a host enterprise'],
            ['page' => 'Appointments', 'section' => 'When a Host Leaves the Team', 'description' => 'A departing host\'s types are switched off and the owner emailed; their bookings stay and can be moved or cancelled.', 'url' => $r['appointments'].'#hosts-leaving', 'category' => 'User Guide', 'keywords' => 'host left team removed viewer off choose a host bookings move cancel'],
            ['page' => 'Appointments', 'section' => 'Weekly Hours', 'description' => 'Set the hours you take bookings for each day of the week, in your schedule timezone.', 'url' => $r['appointments'].'#weekly-hours', 'category' => 'User Guide', 'keywords' => 'weekly hours availability days ranges timezone open'],
            ['page' => 'Appointments', 'section' => 'Date Overrides', 'description' => 'Close a single date or give it different hours, for holidays and one-off changes to your weekly hours.', 'url' => $r['appointments'].'#date-overrides', 'category' => 'User Guide', 'keywords' => 'date override block date holiday day off closed unavailable special hours one off vacation'],
            ['page' => 'Appointments', 'section' => 'Scheduling Rules', 'description' => 'Add buffers between appointments, require minimum notice, and limit how far ahead guests can book.', 'url' => $r['appointments'].'#buffers-and-notice', 'category' => 'User Guide', 'keywords' => 'buffer minimum notice booking window lead time padding'],
            ['page' => 'Appointments', 'section' => 'Where You Meet', 'description' => 'Set each appointment type as in person, online, or by phone, with the address, meeting link, or number.', 'url' => $r['appointments'].'#location', 'category' => 'User Guide', 'keywords' => 'location in person online phone address meeting link video call zoom number where'],
            ['page' => 'Appointments', 'section' => 'What You Ask Guests', 'description' => 'Collect a name and email, optionally ask for or require a phone number, and let guests leave notes.', 'url' => $r['appointments'].'#guest-details', 'category' => 'User Guide', 'keywords' => 'guest details name email phone number required notes questions'],
            ['page' => 'Appointments', 'section' => 'Payments', 'description' => 'Take payment for appointments on Pro with Stripe, PayPal, Payfast, Razorpay, a payment link, or cash; online payments hold the time until paid, and a booking is refunded from the Sales page.', 'url' => $r['appointments'].'#payments', 'category' => 'User Guide', 'keywords' => 'paid appointment stripe paypal payfast razorpay payment link cash price charge currency payment pending expired unpaid hold refund'],
            ['page' => 'Appointments', 'section' => 'Refunds', 'description' => 'Refund a booking from the Sales page: a full Stripe, PayPal or Razorpay refund cancels it and frees the time, a partial one keeps it, and Payfast, payment link or cash bookings are marked refunded.', 'url' => $r['appointments'].'#refunds', 'category' => 'User Guide', 'keywords' => 'refund appointment booking stripe paypal razorpay partial refund mark as refunded money back cancel paid booking refund before cancelling'],
            ['page' => 'Appointments', 'section' => 'Approval', 'description' => 'Require approval so new bookings arrive as requests you accept or decline.', 'url' => $r['appointments'].'#approval', 'category' => 'User Guide', 'keywords' => 'approval request accept decline confirm pending'],
            ['page' => 'Appointments', 'section' => 'Managing Bookings', 'description' => 'View upcoming, pending, past, and cancelled bookings; approve or decline pending ones and cancel a booking when needed.', 'url' => $r['appointments'].'#bookings', 'category' => 'User Guide', 'keywords' => 'bookings manage cancel upcoming pending past list approve decline accept phone notes amount'],
            ['page' => 'Appointments', 'section' => 'Rescheduling', 'description' => 'Move a booking to another time without cancelling it: the guest can do it from their private link, you can do it from the Bookings row, and the payment and calendar invite carry over.', 'url' => $r['appointments'].'#rescheduling', 'category' => 'User Guide', 'keywords' => 'reschedule rescheduling move change time moved change booking time new time short notice notify guest note requires approval again'],
            ['page' => 'Appointments', 'section' => 'The Booking Page', 'description' => 'Share your /book link or the Book a Time button; guests pick an open time in their own timezone and get a confirmation, a calendar invite, and a reminder.', 'url' => $r['appointments'].'#guest-booking', 'category' => 'User Guide', 'keywords' => 'guest booking page book link share url book a time button timezone confirmation calendar invite reminder manage link cancel'],
            ['page' => 'Appointments', 'section' => 'Good to Know', 'description' => 'Email settings are required for confirmations, reminders skip unconfirmed bookings, and bookings stay off your public schedule.', 'url' => $r['appointments'].'#good-to-know', 'category' => 'User Guide', 'keywords' => 'email settings required reminder private booking not on schedule deactivate availability difference pro google wallet pass qr ticket buyers newsletter'],
            ['page' => 'Appointments', 'section' => 'The Types List', 'description' => 'What each appointment type shows on the Appointments tab, its Actions menu and the "Your booking page" row.', 'url' => $r['appointments'].'#types-list', 'category' => 'User Guide', 'keywords' => 'appointment types list preview copy link clone delete active'],
            ['page' => 'Appointments', 'section' => 'What a Free Schedule Can Save', 'description' => 'Date overrides, scheduling rules, approval and paid bookings need Pro; what happens when a Free schedule fills them in.', 'url' => $r['appointments'].'#advanced-on-free', 'category' => 'User Guide', 'keywords' => 'free plan pro date overrides buffers approval paid'],
            ['page' => 'Gift Cards', 'section' => 'Overview', 'description' => 'Sell gift cards that customers buy for someone else and redeem toward tickets for any event on your schedule.', 'url' => $r['gift_cards'].'#overview', 'category' => 'User Guide', 'keywords' => 'gift card voucher present buy for someone denomination balance redeem credit'],
            ['page' => 'Gift Cards', 'section' => 'Enabling Gift Cards', 'description' => 'Turn on gift cards, set the denominations, currency, validity period, and how buyers pay.', 'url' => $r['gift_cards'].'#setup', 'category' => 'User Guide', 'keywords' => 'enable setup denominations amounts currency validity expiry payment method pro'],
            ['page' => 'Gift Cards', 'section' => 'Buying a Gift Card', 'description' => 'Buyers pick an amount, enter the recipient details and a message, and pay; the recipient is emailed the code.', 'url' => $r['gift_cards'].'#buying', 'category' => 'User Guide', 'keywords' => 'buy purchase recipient message send to self email delivery receipt'],
            ['page' => 'Gift Cards', 'section' => 'Redeeming at Checkout', 'description' => 'Customers enter the gift card code at ticket checkout to deduct the balance; any remainder stays on the card.', 'url' => $r['gift_cards'].'#redeeming', 'category' => 'User Guide', 'keywords' => 'redeem checkout code balance partial remainder deduct apply pay difference'],
            ['page' => 'Gift Cards', 'section' => 'Managing Gift Cards', 'description' => 'Track sold gift cards, balances, and redemptions on the Sales page, and mark, cancel, or refund them.', 'url' => $r['gift_cards'].'#managing', 'category' => 'User Guide', 'keywords' => 'manage track sales tab balance outstanding mark paid cancel refund resend'],
            ['page' => 'Gift Cards', 'section' => 'Why a Code Can Be Refused', 'description' => 'The reasons checkout turns a gift card code down.', 'url' => $r['gift_cards'].'#code-refused', 'category' => 'User Guide', 'keywords' => 'gift card code refused invalid expired currency'],
            ['page' => 'Gift Cards', 'section' => 'Abandoned Checkout', 'description' => 'The balance goes back to the card when an unpaid order expires.', 'url' => $r['gift_cards'].'#abandoned-checkout', 'category' => 'User Guide', 'keywords' => 'abandoned checkout balance returned unpaid'],

            // Event Graphics
            ['page' => 'Event Graphics', 'section' => 'Overview', 'description' => 'Generate a shareable image and ready-to-paste text from your upcoming events, free on every plan.', 'url' => $r['event_graphics'].'#overview', 'category' => 'User Guide', 'keywords' => 'graphics images social media flyer free plan credit watermark download copy'],
            ['page' => 'Event Graphics', 'section' => 'Layout and Shape', 'description' => 'Grid, Rows or List, flyers per row, and a fixed social shape: Square, Portrait, Story or Wide, or leave it on Natural.', 'url' => $r['event_graphics'].'#overview', 'category' => 'User Guide', 'keywords' => 'layout grid rows list flyers per row shape image size square portrait story wide landscape natural auto 1080 1200 630 social format instagram facebook dimensions aspect ratio'],
            ['page' => 'Event Graphics', 'section' => 'Which Events', 'description' => 'Which upcoming events are used, how many, a limit per talent or venue, and leaving recurring events out.', 'url' => $r['event_graphics'].'#events', 'category' => 'User Guide', 'keywords' => 'events number of events limit per talent venue per schedule max cap variety diversity same band repeat dominate spread lineup unlimited recurring exclude flyer required'],
            ['page' => 'Event Graphics', 'section' => 'Dates and Numbers', 'description' => 'A date strip on or above each flyer, your own text in place of the date, and a numbered badge per flyer.', 'url' => $r['event_graphics'].'#dates-and-numbers', 'category' => 'User Guide', 'keywords' => 'date strip overlay above flyer show text number badge numbered flyers'],
            ['page' => 'Event Graphics', 'section' => 'Header & Footer Text', 'description' => 'Add a header image, your profile image, a headline above and a sign-off below the events.', 'url' => $r['event_graphics'].'#header-footer-text', 'category' => 'User Guide', 'keywords' => 'header footer text headline tagline branding signoff header image profile image logo'],
            ['page' => 'Event Graphics', 'section' => 'Text Template', 'description' => 'How each event is written in the caption that goes with the graphic.', 'url' => $r['event_graphics'].'#text-template', 'category' => 'User Guide', 'keywords' => 'template text format caption wording whatsapp bold'],
            ['page' => 'Event Graphics', 'section' => 'Quick Reference', 'description' => 'Essential template variables at a glance.', 'url' => $r['event_graphics'].'#quick-reference', 'category' => 'User Guide', 'keywords' => 'variables reference cheatsheet price discount before after coupon'],
            ['page' => 'Event Graphics', 'section' => 'All Template Variables', 'description' => 'Complete list of available template variables.', 'url' => $r['event_graphics'].'#variables', 'category' => 'User Guide', 'keywords' => 'variables placeholders tokens price discount before after coupon'],
            ['page' => 'Event Graphics', 'section' => 'AI Text Prompt', 'description' => 'Tell AI how to rewrite the caption (Enterprise).', 'url' => $r['event_graphics'].'#ai-prompt', 'category' => 'User Guide', 'keywords' => 'ai prompt transform text rewrite caption instruction'],
            ['page' => 'Event Graphics', 'section' => 'Email Scheduling', 'description' => 'Have the graphic and its caption emailed daily, weekly or monthly (Enterprise).', 'url' => $r['event_graphics'].'#email-scheduling', 'category' => 'User Guide', 'keywords' => 'email schedule scheduled automatic send it now last sent'],
            ['page' => 'Event Graphics', 'section' => 'Caption Options', 'description' => 'The switches that decide what the caption beside the graphic includes.', 'url' => $r['event_graphics'].'#text-options', 'category' => 'User Guide', 'keywords' => 'caption text options url link short https event id english every upcoming event'],
            ['page' => 'Event Graphics', 'section' => 'Graphic shapes and sizes', 'description' => 'The shapes a graphic can take and where each is used.', 'url' => $r['event_graphics'].'#shape', 'category' => 'User Guide', 'keywords' => 'shape size square story portrait landscape instagram'],

            // Analytics
            ['page' => 'Analytics', 'section' => 'Overview', 'description' => 'Introduction to the analytics dashboard.', 'url' => $r['analytics'].'#overview', 'category' => 'User Guide', 'keywords' => 'analytics dashboard stats'],
            ['page' => 'Analytics', 'section' => 'Filters', 'description' => 'Filter by schedule, date range, and period.', 'url' => $r['analytics'].'#filters', 'category' => 'User Guide', 'keywords' => 'filter date range period'],
            ['page' => 'Analytics', 'section' => 'Web Analytics', 'description' => 'Views, devices, top events, broken links, traffic sources, visitor locations, and clicks on your social and short links.', 'url' => $r['analytics'].'#web-analytics', 'category' => 'User Guide', 'keywords' => 'views devices traffic sources geography country visitor locations social clicks short links link clicks broken links 404 not found missing event views'],
            ['page' => 'Analytics', 'section' => 'Revenue', 'description' => 'Revenue stats, conversion rates, promo codes, boost and newsletter funnels; revenue is not net of partial refunds.', 'url' => $r['analytics'].'#revenue', 'category' => 'User Guide', 'keywords' => 'revenue conversion promo boost newsletter funnel refund partial refund gross appointments'],
            ['page' => 'Analytics', 'section' => 'Check-ins', 'description' => 'Tickets sold, attendance rates, arrival times, and event breakdown.', 'url' => $r['analytics'].'#checkins', 'category' => 'User Guide', 'keywords' => 'checkins attendance arrival tickets sold no-shows'],
            ['page' => 'Analytics', 'section' => 'Realtime', 'description' => 'The Realtime tab of Analytics: live traffic to your own schedule pages, page views as they happen, visitors who accepted cookies as rows with no name, the last 24 hours of sales, registrations, followers and requests with no names, and how many have checked in at an event that is on.', 'url' => $r['analytics'].'#realtime', 'category' => 'User Guide', 'keywords' => 'realtime live visitors now right now traffic page views online who is on my page activity last 24 hours sales registrations followers requests at the door arrivals checked in checkouts'],
            ['page' => 'Analytics', 'section' => 'No Data State', 'description' => 'Why analytics data might be missing.', 'url' => $r['analytics'].'#no-data', 'category' => 'User Guide', 'keywords' => 'empty no data missing'],
            ['page' => 'Analytics', 'section' => 'What Counts as a View', 'description' => 'Bots, your own team, embeds and the per-visitor daily caps: what is left out of the count.', 'url' => $r['analytics'].'#web-counting', 'category' => 'User Guide', 'keywords' => 'view counted bots team embed cap'],
            ['page' => 'Analytics', 'section' => 'Realtime: Live Traffic', 'description' => 'Page views and visitors on your pages now, top pages, sources, countries and devices.', 'url' => $r['analytics'].'#realtime-traffic', 'category' => 'User Guide', 'keywords' => 'realtime live traffic visitors now page views'],
            ['page' => 'Analytics', 'section' => 'Realtime: At the Door Today', 'description' => 'Arrivals at an event that is on: checked in out of sold, and the last 30 minutes.', 'url' => $r['analytics'].'#realtime-door', 'category' => 'User Guide', 'keywords' => 'door check-in arrivals today event'],
            ['page' => 'Analytics', 'section' => 'Realtime: Activity', 'description' => 'The last 24 hours of sales, registrations, bookings, followers and requests, with no names.', 'url' => $r['analytics'].'#realtime-activity', 'category' => 'User Guide', 'keywords' => 'activity last 24 hours sales registrations followers requests'],
            ['page' => 'Analytics', 'section' => 'Realtime: Who Is Counted', 'description' => 'Who is left out, who is a page view only, and who is listed without a name.', 'url' => $r['analytics'].'#realtime-privacy', 'category' => 'User Guide', 'keywords' => 'privacy cookies consent counted listed'],

            // Account Settings
            ['page' => 'Account Settings', 'section' => 'Profile Information', 'description' => 'Manage name, email, phone number, default schedule and profile image.', 'url' => $r['account_settings'].'#profile', 'category' => 'User Guide', 'keywords' => 'name email phone profile image avatar default schedule'],
            ['page' => 'Account Settings', 'section' => 'Preferences', 'description' => 'Email updates, suggestions, and being asked before you follow a schedule.', 'url' => $r['account_settings'].'#preferences', 'category' => 'User Guide', 'keywords' => 'preferences email updates unsubscribe suggestions setup guide follow consent'],
            ['page' => 'Account Settings', 'section' => 'Localization', 'description' => 'Set your timezone, language and 12 or 24-hour clock.', 'url' => $r['account_settings'].'#localization', 'category' => 'User Guide', 'keywords' => 'timezone language 24 hour time clock localization portuguese brazil portugal european'],
            ['page' => 'Account Settings', 'section' => 'Accessibility', 'description' => 'Bring back the accessibility widget after hiding it.', 'url' => $r['account_settings'].'#accessibility', 'category' => 'User Guide', 'keywords' => 'accessibility widget show hidden'],
            ['page' => 'Account Settings', 'section' => 'Appearance', 'description' => 'Choose light or dark mode and pick from three light and three dark colour palettes.', 'url' => $r['account_settings'].'#appearance', 'category' => 'User Guide', 'keywords' => 'appearance theme dark mode light palette colours colors sand mist paper espresso midnight carbon'],
            ['page' => 'Account Settings', 'section' => 'Payment Methods', 'description' => 'Configure payment methods for ticket sales.', 'url' => $r['account_settings'].'#payments', 'category' => 'User Guide', 'keywords' => 'payment method gateway stripe paypal invoice ninja payfast razorpay payment link cash refund refunds installments'],
            ['page' => 'Account Settings', 'section' => 'Stripe', 'description' => 'Connect Stripe for payment processing.', 'url' => $r['account_settings'].'#stripe', 'category' => 'User Guide', 'keywords' => 'stripe connect payment'],
            ['page' => 'Account Settings', 'section' => 'Invoice Ninja', 'description' => 'Set up Invoice Ninja as a payment gateway.', 'url' => $r['account_settings'].'#invoice-ninja', 'category' => 'User Guide', 'keywords' => 'invoice ninja payment'],
            ['page' => 'Account Settings', 'section' => 'Payment Link', 'description' => 'Send buyers to your own payment page, such as a bank transfer page.', 'url' => $r['account_settings'].'#payment-url', 'category' => 'User Guide', 'keywords' => 'payment link url custom bank transfer external mark paid'],
            ['page' => 'Account Settings', 'section' => 'PayPal', 'description' => 'Connect PayPal to take payments.', 'url' => $r['account_settings'].'#paypal', 'category' => 'User Guide', 'keywords' => 'paypal client id secret test mode sandbox developer dashboard checked when you save provided by this installation administrator account'],
            ['page' => 'Account Settings', 'section' => 'Payfast', 'description' => 'Connect Payfast to take South African payments.', 'url' => $r['account_settings'].'#payfast', 'category' => 'User Guide', 'keywords' => 'payfast south africa zar rand merchant id merchant key passphrase test mode sandbox instant eft capitec provided by this installation administrator account'],
            ['page' => 'Account Settings', 'section' => 'When you are paid', 'description' => 'Ticket money goes straight to your own payment account, which pays it out to your bank on its own schedule.', 'url' => $r['account_settings'].'#payouts', 'category' => 'User Guide', 'keywords' => 'payout payouts paid when bank transfer stripe paypal razorpay payfast settlement first payout 7 to 14 days vendors balance'],
            ['page' => 'Account Settings', 'section' => 'Razorpay', 'description' => 'Connect Razorpay to take payments in Indian rupees.', 'url' => $r['account_settings'].'#razorpay', 'category' => 'User Guide', 'keywords' => 'razorpay india inr indian rupees upi key id key secret webhook secret webhook url test mode checked when you save no webhook provided by this installation administrator account'],
            ['page' => 'Account Settings', 'section' => 'API Settings', 'description' => 'Manage API access and keys (Pro).', 'url' => $r['account_settings'].'#api', 'category' => 'User Guide', 'keywords' => 'api key token access'],
            ['page' => 'Account Settings', 'section' => 'Webhooks', 'description' => 'Configure webhook notifications (Pro).', 'url' => $r['account_settings'].'#webhooks', 'category' => 'User Guide', 'keywords' => 'webhooks notifications callback'],
            ['page' => 'Account Settings', 'section' => 'Google Settings', 'description' => 'Manage Google account and calendar sync.', 'url' => $r['account_settings'].'#google', 'category' => 'User Guide', 'keywords' => 'google account calendar oauth'],
            ['page' => 'Account Settings', 'section' => 'Outlook Calendar', 'description' => 'Connect your Outlook / Microsoft 365 account for calendar sync.', 'url' => $r['account_settings'].'#microsoft', 'category' => 'User Guide', 'keywords' => 'outlook microsoft 365 office account calendar oauth'],
            ['page' => 'Account Settings', 'section' => 'Facebook Settings', 'description' => 'Connect your Facebook account to log in with Facebook.', 'url' => $r['account_settings'].'#facebook', 'category' => 'User Guide', 'keywords' => 'facebook login sign in connect disconnect social account'],
            ['page' => 'Account Settings', 'section' => 'Boosts', 'description' => 'For a boost partner: connect your Facebook Page and ad account, say how businesses pay you, and add the fee card.', 'url' => $r['account_settings'].'#boosts', 'category' => 'User Guide', 'keywords' => 'boost partner page ad account share business id confirm fee card'],
            ['page' => 'Account Settings', 'section' => 'Backup & Restore', 'description' => 'Export and import schedule data as backup files.', 'url' => $r['account_settings'].'#backup', 'category' => 'User Guide', 'keywords' => 'backup restore export import data migration download upload interest list waitlist subscribers personal data'],
            ['page' => 'Account Settings', 'section' => 'App Update', 'description' => 'Check application update status (selfhosted).', 'url' => $r['account_settings'].'#app-update', 'category' => 'User Guide', 'keywords' => 'update version selfhosted'],
            ['page' => 'Account Settings', 'section' => 'Exporting a Backup', 'description' => 'Export schedules as a .zip file, with or without their images.', 'url' => $r['account_settings'].'#backup-export', 'category' => 'User Guide', 'keywords' => 'backup export download zip schedules'],
            ['page' => 'Account Settings', 'section' => 'Importing a Backup', 'description' => 'Restore schedules from a backup file as new schedules.', 'url' => $r['account_settings'].'#backup-import', 'category' => 'User Guide', 'keywords' => 'backup import restore upload zip'],
            ['page' => 'Account Settings', 'section' => 'Update Password', 'description' => 'Change your account password.', 'url' => $r['account_settings'].'#password', 'category' => 'User Guide', 'keywords' => 'password change security'],
            ['page' => 'Account Settings', 'section' => 'Two-Factor Authentication', 'description' => 'Enable 2FA for account security.', 'url' => $r['account_settings'].'#two-factor', 'category' => 'User Guide', 'keywords' => '2fa two-factor authentication security totp'],
            ['page' => 'Account Settings', 'section' => 'Your Data', 'description' => 'Download a copy of the personal data held about your account and email address.', 'url' => $r['account_settings'].'#your-data', 'category' => 'User Guide', 'keywords' => 'download my data export gdpr personal data access portability copy'],
            ['page' => 'Account Settings', 'section' => 'Delete Account', 'description' => 'Permanently delete your account.', 'url' => $r['account_settings'].'#delete-account', 'category' => 'User Guide', 'keywords' => 'delete remove account'],

            // Scan Agenda
            ['page' => 'Scan Agenda', 'section' => 'Overview', 'description' => 'Use AI to scan printed agendas and create event parts.', 'url' => $r['scan_agenda'].'#overview', 'category' => 'User Guide', 'keywords' => 'scan photo agenda ai camera'],
            ['page' => 'Scan Agenda', 'section' => 'Getting Started', 'description' => 'Access Scan Agenda from the admin panel.', 'url' => $r['scan_agenda'].'#getting-started', 'category' => 'User Guide', 'keywords' => 'start begin access'],
            ['page' => 'Scan Agenda', 'section' => 'How It Works', 'description' => 'Steps for scanning, parsing, reviewing, and saving.', 'url' => $r['scan_agenda'].'#how-it-works', 'category' => 'User Guide', 'keywords' => 'process steps workflow'],
            ['page' => 'Scan Agenda', 'section' => 'Custom AI Prompt', 'description' => 'Customize AI instructions for agenda parsing.', 'url' => $r['scan_agenda'].'#custom-prompt', 'category' => 'User Guide', 'keywords' => 'prompt customize instructions'],
            ['page' => 'Scan Agenda', 'section' => 'Tips', 'description' => 'Best practices for scanning agendas.', 'url' => $r['scan_agenda'].'#tips', 'category' => 'User Guide', 'keywords' => 'tips best practices lighting'],

            // Boost
            ['page' => 'Boost', 'section' => 'Overview', 'description' => 'Promote events with Facebook and Instagram ads.', 'url' => $r['boost'].'#overview', 'category' => 'User Guide', 'keywords' => 'boost promote advertise facebook instagram meta'],
            ['page' => 'Boost', 'section' => 'On-Network Promotions', 'description' => 'Buy promoted placement on other schedules on this site, priced per view or per click.', 'url' => $r['boost'].'#on-network', 'category' => 'User Guide', 'keywords' => 'promotion promoted network cpm cpc budget targeting countries review refund'],
            ['page' => 'Boost', 'section' => 'Hosting Other Schedules\' Promotions', 'description' => 'Decline to carry other schedules\' promotions, and any ads, on your own public pages.', 'url' => $r['boost'].'#on-network-hosting', 'category' => 'User Guide', 'keywords' => 'host promotions opt out opt-out decline ads adsense free plan my pages'],
            ['page' => 'Boost', 'section' => 'Quick Mode', 'description' => 'Create ad campaigns quickly with minimal setup.', 'url' => $r['boost'].'#quick-mode', 'category' => 'User Guide', 'keywords' => 'quick fast simple campaign'],
            ['page' => 'Boost', 'section' => 'Advanced Mode', 'description' => 'Full control over budget, targeting, and creative.', 'url' => $r['boost'].'#advanced-mode', 'category' => 'User Guide', 'keywords' => 'advanced targeting budget creative'],
            ['page' => 'Boost', 'section' => 'Smart Defaults', 'description' => 'Automatic configuration based on event type.', 'url' => $r['boost'].'#smart-defaults', 'category' => 'User Guide', 'keywords' => 'defaults automatic smart'],
            ['page' => 'Boost', 'section' => 'Managing Campaigns', 'description' => 'View campaign statuses and manage ads.', 'url' => $r['boost'].'#managing-campaigns', 'category' => 'User Guide', 'keywords' => 'campaigns manage status'],
            ['page' => 'Boost', 'section' => 'Spending Limits', 'description' => 'Budget limits based on campaign history.', 'url' => $r['boost'].'#spending-limits', 'category' => 'User Guide', 'keywords' => 'budget limits spending cap'],
            ['page' => 'Boost', 'section' => 'Boosting Under Your Own Page', 'description' => 'Run every boost on your schedules under your own Facebook Page and ad account, approving each ad first.', 'url' => $r['boost'].'#partners', 'category' => 'User Guide', 'keywords' => 'partner network reseller own page ad account approve request share business'],
            ['page' => 'Boost', 'section' => 'Every Boost Is a Request', 'description' => 'Approve or send back each ad, record the payment, and stop a boost.', 'url' => $r['boost'].'#partners-requests', 'category' => 'User Guide', 'keywords' => 'approve decline payment received stop withdraw request version'],
            ['page' => 'Boost', 'section' => 'Campaigns and Statements', 'description' => 'Spend, payments and the monthly fee on actual ad spend.', 'url' => $r['boost'].'#partners-campaigns', 'category' => 'User Guide', 'keywords' => 'statement fee spend export reconcile invoice monthly'],
            ['page' => 'Boost', 'section' => 'Analytics', 'description' => 'View campaign performance and metrics.', 'url' => $r['boost'].'#analytics', 'category' => 'User Guide', 'keywords' => 'analytics performance metrics reach impressions'],
            ['page' => 'Boost', 'section' => 'Billing & Refunds', 'description' => 'Pricing structure and refund policy.', 'url' => $r['boost'].'#billing', 'category' => 'User Guide', 'keywords' => 'billing pricing refund cost'],
            ['page' => 'Boost', 'section' => 'Tips', 'description' => 'Best practices for successful ad campaigns.', 'url' => $r['boost'].'#tips', 'category' => 'User Guide', 'keywords' => 'tips best practices advice'],
            ['page' => 'Boost', 'section' => 'The Boost Event Button on an Event', 'description' => 'Where the Boost Event button is on the event form, and what each of its states means.', 'url' => $r['boost'].'#boost-from-event', 'category' => 'User Guide', 'keywords' => 'boost event button event form actions'],

            // ===== SELFHOST =====

            // Overview (the group's landing page)
            ['page' => 'Selfhost', 'section' => 'Selfhost Guides', 'description' => 'Where to start when deploying Event Schedule on your own server, and which integration guides to add.', 'url' => $r['selfhost_index'].'#guides', 'category' => 'Selfhost', 'keywords' => 'selfhost self hosted on premise own server single tenant guides index overview'],

            // Installation
            ['page' => 'Docker', 'section' => 'Overview', 'description' => 'Run Event Schedule with Docker Compose: the app, Nginx, MariaDB and the scheduler as four containers.', 'url' => $r['selfhost_docker'].'#overview', 'category' => 'Selfhost', 'keywords' => 'docker compose container install selfhost'],
            ['page' => 'Docker', 'section' => 'Fix the Application Key', 'description' => 'Keep APP_KEY in .env so a rebuilt image does not sign everyone out.', 'url' => $r['selfhost_docker'].'#app-key', 'category' => 'Selfhost', 'keywords' => 'docker app_key key generate rebuild'],
            ['page' => 'Docker', 'section' => 'Serve It Over HTTPS', 'description' => 'APP_ENV, APP_URL, SESSION_SECURE_COOKIE and TRUSTED_PROXIES behind Caddy, SWAG, Nginx Proxy Manager or Traefik.', 'url' => $r['selfhost_docker'].'#https', 'category' => 'Selfhost', 'keywords' => 'docker https reverse proxy caddy swag traefik nginx proxy manager trusted_proxies'],
            ['page' => 'Docker', 'section' => 'Backups', 'description' => 'Dump the MariaDB database and archive the storage volume.', 'url' => $r['selfhost_docker'].'#backups', 'category' => 'Selfhost', 'keywords' => 'docker backup volume mariadb dump restore'],
            ['page' => 'Docker', 'section' => 'Updating', 'description' => 'Rebuild the image without the cache, or pin a release with APP_REF.', 'url' => $r['selfhost_docker'].'#updating', 'category' => 'Selfhost', 'keywords' => 'docker update upgrade rebuild no-cache app_ref release'],
            ['page' => 'Installation', 'section' => 'Overview', 'description' => 'Manual installation guide for selfhosted deployments.', 'url' => $r['selfhost_installation'].'#overview', 'category' => 'Selfhost', 'keywords' => 'install setup deploy server'],
            ['page' => 'Installation', 'section' => 'Requirements', 'description' => 'Server requirements: PHP 8.2+, MySQL 5.7+ or MariaDB 10.3+, the PHP extensions, HTTPS and a cron entry.', 'url' => $r['selfhost_installation'].'#requirements', 'category' => 'Selfhost', 'keywords' => 'requirements php 8.2 mysql mariadb extensions gd server'],
            ['page' => 'Installation', 'section' => 'Set Up the Database', 'description' => 'Create MySQL database and user.', 'url' => $r['selfhost_installation'].'#database', 'category' => 'Selfhost', 'keywords' => 'database mysql create'],
            ['page' => 'Installation', 'section' => 'Download the Application', 'description' => 'Download and extract Event Schedule files.', 'url' => $r['selfhost_installation'].'#download', 'category' => 'Selfhost', 'keywords' => 'download extract files'],
            ['page' => 'Installation', 'section' => 'Set File Permissions', 'description' => 'Set proper directory permissions.', 'url' => $r['selfhost_installation'].'#permissions', 'category' => 'Selfhost', 'keywords' => 'permissions chmod directories'],
            ['page' => 'Installation', 'section' => 'Run the Setup Wizard', 'description' => 'Copy .env, hand it to the web-server user, and run the browser setup wizard.', 'url' => $r['selfhost_installation'].'#environment', 'category' => 'Selfhost', 'keywords' => 'env environment configuration'],
            ['page' => 'Installation', 'section' => 'User Accounts and Registration', 'description' => 'Selfhost is single user by default; enable ALLOW_REGISTRATION to let others sign up.', 'url' => $r['selfhost_installation'].'#user-accounts', 'category' => 'Selfhost', 'keywords' => 'registration sign up register accounts users ALLOW_REGISTRATION single user attendees multi user public registration'],
            ['page' => 'Installation', 'section' => 'Running Behind a Reverse Proxy', 'description' => 'Set TRUSTED_PROXIES when running behind Nginx, Apache, or Cloudflare.', 'url' => $r['selfhost_installation'].'#reverse-proxy', 'category' => 'Selfhost', 'keywords' => 'proxy reverse proxy nginx apache cloudflare hestiacp https forwarded proto redirect loop TRUSTED_PROXIES'],
            ['page' => 'Installation', 'section' => 'Set Up the Cron Job', 'description' => 'Configure scheduled tasks for the application.', 'url' => $r['selfhost_installation'].'#cron', 'category' => 'Selfhost', 'keywords' => 'cron scheduler task'],
            ['page' => 'Installation', 'section' => 'Verification', 'description' => 'Test and verify the installation.', 'url' => $r['selfhost_installation'].'#verification', 'category' => 'Selfhost', 'keywords' => 'verify test check'],
            ['page' => 'Installation', 'section' => 'Troubleshooting', 'description' => 'Fix setup problems: permission denied on laravel.log, 500 errors, getting back to the setup wizard, and QR codes that will not scan.', 'url' => $r['selfhost_installation'].'#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix permission denied laravel.log 500 setup wizard locked out app_url storage qr code will not scan invalid ticket document root public directory'],
            ['page' => 'Installation', 'section' => 'Push Notifications', 'description' => 'Enable optional OneSignal web push notifications that mirror email notifications.', 'url' => $r['selfhost_installation'].'#push-notifications', 'category' => 'Selfhost', 'keywords' => 'push notifications onesignal web push browser mobile alerts ONESIGNAL_APP_ID'],
            ['page' => 'Installation', 'section' => 'Spam Protection', 'description' => 'Add a Cloudflare Turnstile challenge to checkout, gift card purchases and fan content submissions.', 'url' => $r['selfhost_installation'].'#spam-protection', 'category' => 'Selfhost', 'keywords' => 'spam bots turnstile cloudflare captcha challenge TURNSTILE_SITE_KEY TURNSTILE_SECRET_KEY checkout gift cards fan content'],

            // Stripe (Selfhost)
            ['page' => 'Stripe Integration', 'section' => 'Overview', 'description' => 'Set up Stripe for payment processing.', 'url' => $r['selfhost_stripe'].'#overview', 'category' => 'Selfhost', 'keywords' => 'stripe payment setup'],
            ['page' => 'Stripe Integration', 'section' => 'Choose Your Setup', 'description' => 'Select the right deployment option.', 'url' => $r['selfhost_stripe'].'#choose-setup', 'category' => 'Selfhost', 'keywords' => 'setup option deployment'],
            ['page' => 'Stripe Integration', 'section' => 'For Selfhosted Users', 'description' => 'Stripe configuration for selfhosted deployments.', 'url' => $r['selfhost_stripe'].'#selfhosted-users', 'category' => 'Selfhost', 'keywords' => 'selfhosted stripe configuration'],
            ['page' => 'Stripe Integration', 'section' => 'For SaaS Operators', 'description' => 'Stripe Connect setup for SaaS platforms.', 'url' => $r['selfhost_stripe'].'#saas-operators', 'category' => 'Selfhost', 'keywords' => 'saas stripe connect'],
            ['page' => 'Stripe Integration', 'section' => 'Plan pricing', 'description' => 'Set what you advertise Pro and Enterprise at, from the admin panel or the STRIPE_PRICE_*_AMOUNT variables.', 'url' => $r['selfhost_stripe'].'#cashier', 'category' => 'Selfhost', 'keywords' => 'plan pricing custom pricing price amount pro enterprise monthly yearly STRIPE_PRICE_MONTHLY_AMOUNT selfhost change price'],
            ['page' => 'Stripe Integration', 'section' => 'Plan currency', 'description' => 'PLATFORM_CURRENCY decides the symbol beside your plan prices, and the currency a new event starts in when its schedule has no country; the Stripe price decides what is charged.', 'url' => $r['selfhost_stripe'].'#cashier', 'category' => 'Selfhost', 'keywords' => 'PLATFORM_CURRENCY plan price currency symbol dollar euro pound rand default event currency no country'],
            ['page' => 'Stripe Integration', 'section' => 'Invoice Ninja Integration', 'description' => 'Configure Invoice Ninja as a payment gateway.', 'url' => $r['selfhost_stripe'].'#invoice-ninja', 'category' => 'Selfhost', 'keywords' => 'invoice ninja payment gateway'],
            ['page' => 'Stripe Integration', 'section' => 'Testing', 'description' => 'Test your payment setup.', 'url' => $r['selfhost_stripe'].'#testing', 'category' => 'Selfhost', 'keywords' => 'test payment debug'],
            ['page' => 'Stripe Integration', 'section' => 'Troubleshooting', 'description' => 'Fix common payment issues.', 'url' => $r['selfhost_stripe'].'#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix problem'],
            ['page' => 'Stripe Integration', 'section' => 'Security', 'description' => 'Payment security considerations.', 'url' => $r['selfhost_stripe'].'#security', 'category' => 'Selfhost', 'keywords' => 'security keys secrets'],
            ['page' => 'Stripe Integration', 'section' => 'PayPal', 'description' => 'PayPal on a selfhosted install: one account for everyone via .env, or one per user.', 'url' => $r['selfhost_stripe'].'#paypal', 'category' => 'Selfhost', 'keywords' => 'paypal selfhost client id secret webhook localhost capture no public host needed'],
            ['page' => 'Stripe Integration', 'section' => 'PayPal for the whole install', 'description' => 'Set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET so every schedule can sell without connecting an account.', 'url' => $r['selfhost_stripe'].'#paypal', 'category' => 'Selfhost', 'keywords' => 'PAYPAL_CLIENT_ID PAYPAL_CLIENT_SECRET PAYPAL_SANDBOX PAYPAL_WEBHOOK_ID env platform wide install wide paypal every schedule default account stripe not available'],
            ['page' => 'Stripe Integration', 'section' => 'Payfast', 'description' => 'Payfast on a selfhosted install: one account for everyone via .env, or one per user.', 'url' => $r['selfhost_stripe'].'#payfast', 'category' => 'Selfhost', 'keywords' => 'payfast selfhost south africa zar itn notification publicly reachable localhost'],
            ['page' => 'Stripe Integration', 'section' => 'Payfast for the whole install', 'description' => 'Set PAYFAST_MERCHANT_ID, PAYFAST_MERCHANT_KEY and PAYFAST_PASSPHRASE so every schedule can sell without connecting an account.', 'url' => $r['selfhost_stripe'].'#payfast', 'category' => 'Selfhost', 'keywords' => 'PAYFAST_MERCHANT_ID PAYFAST_MERCHANT_KEY PAYFAST_PASSPHRASE PAYFAST_SANDBOX PAYFAST_PAYMENT_TYPES env platform wide install wide payfast every schedule default account south africa stripe not available'],
            ['page' => 'Stripe Integration', 'section' => 'Razorpay', 'description' => 'Razorpay on a selfhosted install: one account for everyone via .env, or one per user.', 'url' => $r['selfhost_stripe'].'#razorpay', 'category' => 'Selfhost', 'keywords' => 'razorpay selfhost india inr indian rupees upi localhost no public host needed payment link'],
            ['page' => 'Stripe Integration', 'section' => 'Razorpay for the whole install', 'description' => 'Set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET so every schedule can sell in Indian rupees without connecting an account.', 'url' => $r['selfhost_stripe'].'#razorpay', 'category' => 'Selfhost', 'keywords' => 'RAZORPAY_KEY_ID RAZORPAY_KEY_SECRET RAZORPAY_WEBHOOK_SECRET env platform wide install wide razorpay every schedule default account india'],
            ['page' => 'Stripe Integration', 'section' => 'Razorpay webhook', 'description' => 'Add the Razorpay webhook for an install-wide account, or late payments stay unpaid and unfinished checkouts keep their seats.', 'url' => $r['selfhost_stripe'].'#razorpay-webhook', 'category' => 'Selfhost', 'keywords' => 'razorpay webhook secret payment_link.paid payment_link.expired install-wide'],
            ['page' => 'Stripe Integration', 'section' => 'Default payment method', 'description' => 'DEFAULT_PAYMENT_METHOD sets what new events start on instead of Cash.', 'url' => $r['selfhost_stripe'].'#default-payment-method', 'category' => 'Selfhost', 'keywords' => 'DEFAULT_PAYMENT_METHOD default payment method new event cash payfast paypal razorpay stripe env'],
            ['page' => 'Stripe Integration', 'section' => 'Payfast ITN and proxies', 'description' => 'Why the unrecognised source address warning is harmless behind Cloudflare or Docker.', 'url' => $r['selfhost_stripe'].'#payfast-itn-log', 'category' => 'Selfhost', 'keywords' => 'payfast itn unrecognised source address trusted proxies cloudflare docker reverse proxy warning'],
            ['page' => 'Stripe Integration', 'section' => 'Refunds', 'description' => 'Refund a Stripe, PayPal or Razorpay sale from the Sales page, in full or in part; other methods only record it, and dashboard refunds are not reported back.', 'url' => $r['selfhost_stripe'].'#refunds', 'category' => 'Selfhost', 'keywords' => 'refund refunds partial refund full refund mark as refunded money back stripe dashboard paypal dashboard razorpay dashboard sales page installment plan'],
            ['page' => 'Stripe Integration', 'section' => 'Stripe Connect', 'description' => 'Let each customer connect their own Stripe account, and what the Stripe row in Settings shows.', 'url' => $r['selfhost_stripe'].'#stripe-connect', 'category' => 'Selfhost', 'keywords' => 'stripe connect onboarding connected setup not finished'],
            ['page' => 'Stripe Integration', 'section' => 'PayPal webhook', 'description' => 'Add the listener for an install-wide PayPal account yourself, or late payments stay unpaid.', 'url' => $r['selfhost_stripe'].'#paypal-webhook', 'category' => 'Selfhost', 'keywords' => 'paypal webhook listener install-wide'],
            ['page' => 'Stripe Integration', 'section' => 'Payfast currency', 'description' => 'Payfast settles in rand only, and what that means for an event priced in another currency.', 'url' => $r['selfhost_stripe'].'#payfast-currency', 'category' => 'Selfhost', 'keywords' => 'payfast zar rand currency'],

            // Google Calendar (Selfhost)
            ['page' => 'Google Wallet', 'section' => 'Prerequisites', 'description' => 'What you need before enabling Google Wallet passes.', 'url' => $r['selfhost_google_wallet'].'#prerequisites', 'category' => 'Selfhost', 'keywords' => 'google wallet issuer account prerequisites pass'],
            ['page' => 'Google Wallet', 'section' => 'Setup Instructions', 'description' => 'Issuer account, service account and env keys for Google Wallet passes.', 'url' => $r['selfhost_google_wallet'].'#setup', 'category' => 'Selfhost', 'keywords' => 'google wallet setup issuer service account GOOGLE_WALLET_ISSUER_ID GOOGLE_WALLET_SERVICE_ACCOUNT'],
            ['page' => 'Google Wallet', 'section' => 'Verifying it works', 'description' => 'Confirm the Add to Google Wallet button and demo mode.', 'url' => $r['selfhost_google_wallet'].'#verify', 'category' => 'Selfhost', 'keywords' => 'google wallet verify demo mode test account'],
            ['page' => 'Google Wallet', 'section' => 'What is sent to Google', 'description' => 'The attendee data a saved wallet pass carries.', 'url' => $r['selfhost_google_wallet'].'#privacy', 'category' => 'Selfhost', 'keywords' => 'google wallet privacy data sent attendee secret'],
            ['page' => 'Google Wallet', 'section' => 'How it works', 'description' => 'Pass classes, snapshots and when a pass expires.', 'url' => $r['selfhost_google_wallet'].'#behaviour', 'category' => 'Selfhost', 'keywords' => 'google wallet pass class snapshot expiry archive'],
            ['page' => 'Google Wallet', 'section' => 'Troubleshooting', 'description' => 'Why the Add to Google Wallet button is missing or failing.', 'url' => $r['selfhost_google_wallet'].'#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'google wallet troubleshooting button missing error log'],
            ['page' => 'Apple Wallet', 'section' => 'Prerequisites', 'description' => 'What you need before enabling Apple Wallet passes.', 'url' => $r['selfhost_apple_wallet'].'#prerequisites', 'category' => 'Selfhost', 'keywords' => 'apple wallet pkpass prerequisites developer program'],
            ['page' => 'Apple Wallet', 'section' => 'Setup Instructions', 'description' => 'Pass Type ID, certificate and env keys for Apple Wallet passes.', 'url' => $r['selfhost_apple_wallet'].'#setup', 'category' => 'Selfhost', 'keywords' => 'apple wallet pass type id certificate p12 pem team id env'],
            ['page' => 'Apple Wallet', 'section' => 'What is sent to Apple', 'description' => 'Nothing: the pass is a signed file the buyer\'s device opens.', 'url' => $r['selfhost_apple_wallet'].'#privacy', 'category' => 'Selfhost', 'keywords' => 'apple wallet privacy data'],
            ['page' => 'Apple Wallet', 'section' => 'Troubleshooting', 'description' => 'Why the Add to Apple Wallet button is missing, or the pass will not add.', 'url' => $r['selfhost_apple_wallet'].'#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'apple wallet button missing pass cannot be added badge'],
            ['page' => 'Google Wallet', 'section' => 'Overview', 'description' => 'Where the Add to Google Wallet badge appears, and what switches it on.', 'url' => $r['selfhost_google_wallet'].'#overview', 'category' => 'Selfhost', 'keywords' => 'overview badge add to google wallet'],
            ['page' => 'Google Calendar', 'section' => 'Prerequisites', 'description' => 'Google Cloud project requirements for calendar sync.', 'url' => $r['selfhost_google_calendar'].'#prerequisites', 'category' => 'Selfhost', 'keywords' => 'google cloud project prerequisites'],
            ['page' => 'Google Calendar', 'section' => 'Setup Instructions', 'description' => 'Step-by-step OAuth2 setup for Google Calendar.', 'url' => $r['selfhost_google_calendar'].'#setup', 'category' => 'Selfhost', 'keywords' => 'setup oauth2 credentials'],
            ['page' => 'Google Calendar', 'section' => 'Features', 'description' => 'Google Calendar sync capabilities.', 'url' => $r['selfhost_google_calendar'].'#features', 'category' => 'Selfhost', 'keywords' => 'features sync bidirectional'],
            ['page' => 'Google Calendar', 'section' => 'Usage', 'description' => 'How to use the Google Calendar integration.', 'url' => $r['selfhost_google_calendar'].'#usage', 'category' => 'Selfhost', 'keywords' => 'usage connect calendar'],
            ['page' => 'Google Calendar', 'section' => 'API Endpoints', 'description' => 'Google Calendar API endpoints reference.', 'url' => $r['selfhost_google_calendar'].'#api-endpoints', 'category' => 'Selfhost', 'keywords' => 'api endpoints routes'],
            ['page' => 'Google Calendar', 'section' => 'Troubleshooting', 'description' => 'Debug Google Calendar sync issues.', 'url' => $r['selfhost_google_calendar'].'#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix debug sync'],
            ['page' => 'Google Calendar', 'section' => 'Security', 'description' => 'Token storage and security considerations.', 'url' => $r['selfhost_google_calendar'].'#security', 'category' => 'Selfhost', 'keywords' => 'security tokens oauth'],
            ['page' => 'Google Calendar', 'section' => 'Overview', 'description' => 'What the Google OAuth client is used for, and where sync lives in the app.', 'url' => $r['selfhost_google_calendar'].'#overview', 'category' => 'Selfhost', 'keywords' => 'overview oauth client sign in sync import'],
            ['page' => 'Google Calendar', 'section' => 'Read-Only Connections', 'description' => 'A connection made from the import page can read but not write; press Allow at Google to change it.', 'url' => $r['selfhost_google_calendar'].'#read-only', 'category' => 'Selfhost', 'keywords' => 'read only readonly scope allow at google'],
            ['page' => 'Google Calendar', 'section' => 'One-Time Import', 'description' => 'The read-only Google Calendar source on the import page.', 'url' => $r['selfhost_google_calendar'].'#one-time-import', 'category' => 'Selfhost', 'keywords' => 'import google calendar one time'],
            ['page' => 'Google Calendar', 'section' => 'Deletions in Google', 'description' => 'Keep, mark cancelled or delete when an event is deleted in the connected calendar.', 'url' => $r['selfhost_google_calendar'].'#delete-sync', 'category' => 'Selfhost', 'keywords' => 'delete deletion cancelled keep'],
            ['page' => 'Google Calendar', 'section' => 'Member Calendars', 'description' => 'Sync to My Calendar for team members.', 'url' => $r['selfhost_google_calendar'].'#member-sync', 'category' => 'Selfhost', 'keywords' => 'team member my calendar sync'],
            ['page' => 'Google Calendar', 'section' => 'Calendar Sync on an Event', 'description' => 'The Calendar sync block at the end of an event\'s Listing tab: Synced, Not synced, Sync Now and Remove.', 'url' => $r['selfhost_google_calendar'].'#calendar-sync-tab', 'category' => 'Selfhost', 'keywords' => 'event calendar sync tab sync now remove'],

            // Outlook / Microsoft Calendar (Selfhost)
            ['page' => 'Outlook Calendar', 'section' => 'Prerequisites', 'description' => 'Azure app registration requirements for Outlook calendar sync.', 'url' => $r['selfhost_microsoft_calendar'].'#prerequisites', 'category' => 'Selfhost', 'keywords' => 'microsoft azure app registration prerequisites outlook 365'],
            ['page' => 'Outlook Calendar', 'section' => 'Setup Instructions', 'description' => 'Step-by-step Microsoft Graph OAuth setup for Outlook Calendar.', 'url' => $r['selfhost_microsoft_calendar'].'#setup', 'category' => 'Selfhost', 'keywords' => 'setup oauth graph credentials microsoft outlook'],
            ['page' => 'Outlook Calendar', 'section' => 'Features', 'description' => 'Outlook Calendar sync capabilities including Teams meetings.', 'url' => $r['selfhost_microsoft_calendar'].'#features', 'category' => 'Selfhost', 'keywords' => 'features sync bidirectional teams meetings outlook'],
            ['page' => 'Outlook Calendar', 'section' => 'Usage', 'description' => 'How to use the Outlook Calendar integration.', 'url' => $r['selfhost_microsoft_calendar'].'#usage', 'category' => 'Selfhost', 'keywords' => 'usage connect calendar outlook microsoft'],
            ['page' => 'Outlook Calendar', 'section' => 'API Endpoints', 'description' => 'Outlook Calendar API endpoints reference.', 'url' => $r['selfhost_microsoft_calendar'].'#api-endpoints', 'category' => 'Selfhost', 'keywords' => 'api endpoints routes microsoft outlook webhook'],
            ['page' => 'Outlook Calendar', 'section' => 'Troubleshooting', 'description' => 'Debug Outlook Calendar sync issues.', 'url' => $r['selfhost_microsoft_calendar'].'#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix debug sync outlook microsoft'],
            ['page' => 'Outlook Calendar', 'section' => 'Security', 'description' => 'Token storage and security considerations for Outlook sync.', 'url' => $r['selfhost_microsoft_calendar'].'#security', 'category' => 'Selfhost', 'keywords' => 'security tokens oauth microsoft outlook'],
            ['page' => 'Outlook Calendar', 'section' => 'Overview', 'description' => 'What is set up, and where Outlook sync lives in the app.', 'url' => $r['selfhost_microsoft_calendar'].'#overview', 'category' => 'Selfhost', 'keywords' => 'overview microsoft outlook sync'],
            ['page' => 'Outlook Calendar', 'section' => 'Teams Meeting Links', 'description' => 'Create Teams meetings for online events.', 'url' => $r['selfhost_microsoft_calendar'].'#teams', 'category' => 'Selfhost', 'keywords' => 'teams meeting link online'],
            ['page' => 'Outlook Calendar', 'section' => 'Deletions in Outlook', 'description' => 'What a deletion in Outlook does here.', 'url' => $r['selfhost_microsoft_calendar'].'#delete-sync', 'category' => 'Selfhost', 'keywords' => 'delete deletion cancelled keep'],
            ['page' => 'Outlook Calendar', 'section' => 'Calendar Sync on an Event', 'description' => 'The Calendar sync block at the end of an event\'s Listing tab: Sync Now and Remove.', 'url' => $r['selfhost_microsoft_calendar'].'#calendar-sync-tab', 'category' => 'Selfhost', 'keywords' => 'event calendar sync tab sync now remove'],

            // Email (Selfhost)
            ['page' => 'Email Setup', 'section' => 'Overview', 'description' => 'Configure email for your selfhosted instance.', 'url' => $r['selfhost_email'].'#overview', 'category' => 'Selfhost', 'keywords' => 'email mail setup'],
            ['page' => 'Email Setup', 'section' => 'What Is Sent Automatically', 'description' => 'Ticket reminders, the new-event digest, interest-list emails, change notices and sign-up confirmations, and what each needs on a selfhosted install.', 'url' => $r['selfhost_email'].'#what-is-sent', 'category' => 'Selfhost', 'keywords' => 'digest announcements interest list tell me when tickets go on sale reminder cancellation change notice buyers sign-up confirmation subscriber account ALLOW_REGISTRATION claim invitation AUDIENCE_ANNOUNCEMENT_MIN_HOURS EVENT_INTEREST_REMINDER_HOURS'],
            ['page' => 'Email Setup', 'section' => 'SMTP Setup', 'description' => 'Configure SMTP for sending emails.', 'url' => $r['selfhost_email'].'#smtp', 'category' => 'Selfhost', 'keywords' => 'smtp mail server'],
            ['page' => 'Email Setup', 'section' => 'Other Mail Drivers', 'description' => 'SES, sendmail, Postmark, Resend, failover and round robin. Mailgun goes through SMTP.', 'url' => $r['selfhost_email'].'#drivers', 'category' => 'Selfhost', 'keywords' => 'mailgun ses postmark driver'],
            ['page' => 'Email Setup', 'section' => 'Sender Configuration', 'description' => 'Configure the email sender address and name.', 'url' => $r['selfhost_email'].'#sender', 'category' => 'Selfhost', 'keywords' => 'sender from address name'],
            ['page' => 'Email Setup', 'section' => 'Testing', 'description' => 'Test your email configuration.', 'url' => $r['selfhost_email'].'#testing', 'category' => 'Selfhost', 'keywords' => 'test email verify'],
            ['page' => 'Email Setup', 'section' => 'Troubleshooting', 'description' => 'Fix email delivery issues.', 'url' => $r['selfhost_email'].'#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix delivery'],
            ['page' => 'Email Setup', 'section' => 'No Mail Transport', 'description' => 'What is skipped and what is only written to the log while MAIL_MAILER is log or array.', 'url' => $r['selfhost_email'].'#no-transport', 'category' => 'Selfhost', 'keywords' => 'mail_mailer log array skipped not sent'],
            ['page' => 'Email Setup', 'section' => 'Differences from the Hosted Service', 'description' => 'No per-schedule sender, no newsletter allowance, and no platform nudges or digests on a selfhosted install.', 'url' => $r['selfhost_email'].'#hosted-differences', 'category' => 'Selfhost', 'keywords' => 'hosted selfhost difference onboarding digest nudges'],
            ['page' => 'Email Setup', 'section' => 'Packages and updates', 'description' => 'A package added with composer require is removed by the next update; use the provider\'s SMTP.', 'url' => $r['selfhost_email'].'#composer-packages', 'category' => 'Selfhost', 'keywords' => 'postmark resend composer vendor update package removed smtp'],

            // AI (Selfhost)
            ['page' => 'AI Setup', 'section' => 'Overview', 'description' => 'Enable AI features with Google Gemini and OpenAI.', 'url' => $r['selfhost_ai'].'#overview', 'category' => 'Selfhost', 'keywords' => 'ai gemini openai setup'],
            ['page' => 'AI Setup', 'section' => 'AI Features', 'description' => 'Every AI feature a key turns on: import, a daily auto import from event URLs, agenda scanning, translation and image generation.', 'url' => $r['selfhost_ai'].'#features', 'category' => 'Selfhost', 'keywords' => 'features import scan translate images auto import daily urls cities filter curator unclaimed'],
            ['page' => 'AI Setup', 'section' => 'Get a Gemini API Key', 'description' => 'Obtain a Google Gemini API key for text features.', 'url' => $r['selfhost_ai'].'#gemini-api-key', 'category' => 'Selfhost', 'keywords' => 'api key gemini google'],
            ['page' => 'AI Setup', 'section' => 'Get an OpenAI API Key', 'description' => 'Obtain an OpenAI API key for image generation.', 'url' => $r['selfhost_ai'].'#openai-api-key', 'category' => 'Selfhost', 'keywords' => 'api key openai dall-e image'],
            ['page' => 'AI Setup', 'section' => 'Configuration', 'description' => 'Set the GEMINI_API_KEY and OPENAI_API_KEY environment variables.', 'url' => $r['selfhost_ai'].'#configuration', 'category' => 'Selfhost', 'keywords' => 'configuration env variable'],
            ['page' => 'AI Setup', 'section' => 'Troubleshooting', 'description' => 'Fix AI API issues.', 'url' => $r['selfhost_ai'].'#troubleshooting', 'category' => 'Selfhost', 'keywords' => 'troubleshoot fix api error'],
            ['page' => 'AI Setup', 'section' => 'With No Key', 'description' => 'Which screens change, and what guests see, on an install with no AI key.', 'url' => $r['selfhost_ai'].'#no-key', 'category' => 'Selfhost', 'keywords' => 'no api key setup required import guest'],

            // Web accessibility (selfhost operators)
            ['page' => 'Web Accessibility', 'section' => 'Overview', 'description' => 'Legal context for selfhost operators and configuration keys for accessibility.', 'url' => $r['selfhost_accessibility'].'#overview', 'category' => 'Selfhost', 'keywords' => 'accessibility wcag declaration israel eu legal selfhost'],
            ['page' => 'Web Accessibility', 'section' => 'Your declaration', 'description' => 'Publish your own accessibility statement on your domain when you serve regulated markets.', 'url' => $r['selfhost_accessibility'].'#your-declaration', 'category' => 'Selfhost', 'keywords' => 'declaration statement coordinator'],
            ['page' => 'Web Accessibility', 'section' => 'Configuration', 'description' => 'Environment variables for the contact email, SLA days, WCAG label, review date and measured pages.', 'url' => $r['selfhost_accessibility'].'#configuration', 'category' => 'Selfhost', 'keywords' => 'env accessibility contact email ACCESSIBILITY_LAST_REVIEWED ACCESSIBILITY_PUBLIC_PAGES_MEASURED ACCESSIBILITY_PUBLIC_MEASUREMENT_DATE measurement'],

            ['page' => 'Installation', 'section' => 'Custom translations', 'description' => 'Rename Talent to Artist or any other UI term. Overrides live in storage/app/lang/ and survive upgrades.', 'url' => $r['selfhost_installation'].'#translations', 'category' => 'Selfhost', 'keywords' => 'translation language rename customize override messages talent artist curator event planner locale'],
            ['page' => 'SaaS Setup', 'section' => 'Scheduler and queue', 'description' => 'Drive timed tasks with a cron entry, a schedule:work process, or the /translate_data HTTP endpoint. Includes the stalled-scheduler alert.', 'url' => $r['saas_setup'].'#scheduler', 'category' => 'SaaS', 'keywords' => 'cron scheduler schedule:work schedule:run queue worker queue:work translate_data APP_CRON_SECRET heartbeat stalled timed tasks background jobs'],
            ['page' => 'SaaS Setup', 'section' => 'Backup storage', 'description' => 'Point schedule exports at object storage when the app runs on more than one server. Must be a private bucket, never the images bucket.', 'url' => $r['saas_setup'].'#backup-storage', 'category' => 'SaaS', 'keywords' => 'backup export storage bucket spaces s3 private BACKUP_SPACES_BUCKET download archive'],
            ['page' => 'Facebook Login', 'section' => 'Overview', 'description' => 'Add Continue with Facebook sign-in and a Facebook Settings section for your customers. Off by default.', 'url' => $r['saas_facebook_login'].'#overview', 'category' => 'SaaS', 'keywords' => 'facebook login sign in sign up oauth meta social login continue with facebook events import export sync'],
            ['page' => 'Facebook Login', 'section' => 'Reusing the Boost app', 'description' => 'When the Boost Meta app can also be used for login, and why a separate Consumer app is recommended.', 'url' => $r['saas_facebook_login'].'#reuse-boost-app', 'category' => 'SaaS', 'keywords' => 'facebook boost meta app reuse business app consumer facebook login for business META_APP_ID META_APP_SECRET'],
            ['page' => 'Facebook Login', 'section' => 'Create the app', 'description' => 'Create a Consumer Meta app with the Facebook Login use case.', 'url' => $r['saas_facebook_login'].'#create-app', 'category' => 'SaaS', 'keywords' => 'facebook meta app developers create app consumer business'],
            ['page' => 'Facebook Login', 'section' => 'Basic settings', 'description' => 'App domains, privacy policy, terms, data deletion URL, icon and category.', 'url' => $r['saas_facebook_login'].'#basic-settings', 'category' => 'SaaS', 'keywords' => 'facebook meta app domains privacy policy terms data deletion app secret app id'],
            ['page' => 'Facebook Login', 'section' => 'Facebook Login settings', 'description' => 'Register the three OAuth redirect URIs on the app subdomain.', 'url' => $r['saas_facebook_login'].'#login-settings', 'category' => 'SaaS', 'keywords' => 'facebook redirect uri callback oauth valid oauth redirect uris strict mode https'],
            ['page' => 'Facebook Login', 'section' => 'Permissions', 'description' => 'Add the email and public_profile permissions. No App Review needed.', 'url' => $r['saas_facebook_login'].'#permissions', 'category' => 'SaaS', 'keywords' => 'facebook permissions email public_profile advanced access app review'],
            ['page' => 'Facebook Login', 'section' => 'Configure Event Schedule', 'description' => 'Set FACEBOOK_CLIENT_ID and FACEBOOK_CLIENT_SECRET in .env.', 'url' => $r['saas_facebook_login'].'#configure', 'category' => 'SaaS', 'keywords' => 'facebook env FACEBOOK_CLIENT_ID FACEBOOK_CLIENT_SECRET FACEBOOK_REDIRECT_URI config'],
            ['page' => 'Facebook Login', 'section' => 'Test, then go Live', 'description' => 'Test with app roles and test users, then switch the app to Live.', 'url' => $r['saas_facebook_login'].'#go-live', 'category' => 'SaaS', 'keywords' => 'facebook test users app roles development live mode app not active'],
            ['page' => 'Facebook Login', 'section' => 'How accounts are matched', 'description' => 'How a Facebook sign-in links to an existing account or creates a new one.', 'url' => $r['saas_facebook_login'].'#account-matching', 'category' => 'SaaS', 'keywords' => 'facebook account linking matching existing email placeholder rotate app secret'],
            ['page' => 'Facebook Login', 'section' => 'Turning it off', 'description' => 'Unset either value to hide Facebook login. Stored links are kept.', 'url' => $r['saas_facebook_login'].'#disable', 'category' => 'SaaS', 'keywords' => 'facebook disable turn off remove login reset password'],
            ['page' => 'SaaS Setup', 'section' => 'Custom translations', 'description' => 'Override UI strings in storage/app/lang/ across every tenant. Survives upgrades.', 'url' => $r['saas_setup'].'#translations', 'category' => 'SaaS', 'keywords' => 'translation language rename customize override messages talent artist curator event planner locale'],
            ['page' => 'Installation', 'section' => 'Custom dashboard links', 'description' => 'Add custom links to the admin sidebar via CUSTOM_LINK_* environment variables.', 'url' => $r['selfhost_installation'].'#custom-links', 'category' => 'Selfhost', 'keywords' => 'custom links sidebar dashboard navigation menu env environment'],
            ['page' => 'Installation', 'section' => 'Updating', 'description' => 'Update from the App Update page in Settings or the admin panel, or with php artisan app:update.', 'url' => $r['selfhost_installation'].'#updating', 'category' => 'Selfhost', 'keywords' => 'update upgrade app:update new version php version newer php migrate error page unfinished'],
            ['page' => 'Installation', 'section' => 'Cron URL', 'description' => 'Drive the scheduler over HTTP with APP_CRON_SECRET when the host has no crontab.', 'url' => $r['selfhost_installation'].'#cron-url', 'category' => 'Selfhost', 'keywords' => 'cron url translate_data app_cron_secret no crontab'],
            ['page' => 'Installation', 'section' => 'Google Maps and YouTube keys', 'description' => 'BACKEND_GOOGLE_KEY and MAPS_API_KEY: venue coordinates, map images, the Google Map on event pages, YouTube search.', 'url' => $r['selfhost_installation'].'#google-keys', 'category' => 'Selfhost', 'keywords' => 'google maps geocoding youtube BACKEND_GOOGLE_KEY MAPS_API_KEY api key'],
            ['page' => 'Installation', 'section' => 'PHP settings that ship with the app', 'description' => 'Upload size, request time and PHP2Shell stopgaps set by public/.user.ini.', 'url' => $r['selfhost_installation'].'#php-settings', 'category' => 'Selfhost', 'keywords' => 'upload_max_filesize max_execution_time user.ini php2shell php settings'],
            ['page' => 'Installation', 'section' => 'Page Expired on sign up', 'description' => 'Sign Up returns 419 on plain HTTP because SESSION_SECURE_COOKIE is true.', 'url' => $r['selfhost_installation'].'#troubleshooting-page-expired', 'category' => 'Selfhost', 'keywords' => '419 page expired http session secure cookie SESSION_SECURE_COOKIE'],
            ['page' => 'SaaS Setup', 'section' => 'Custom dashboard links', 'description' => 'Add platform-wide custom links to the admin sidebar via CUSTOM_LINK_* environment variables.', 'url' => $r['saas_setup'].'#custom-links', 'category' => 'SaaS', 'keywords' => 'custom links sidebar dashboard navigation menu env environment'],

            // Admin Panel (Selfhost)
            ['page' => 'Admin Panel', 'section' => 'Overview', 'description' => 'Admin panel organization and sections.', 'url' => $r['selfhost_admin'].'#overview', 'category' => 'Selfhost', 'keywords' => 'admin panel dashboard'],
            ['page' => 'Admin Panel', 'section' => 'Accessing /admin', 'description' => 'How to access the admin panel.', 'url' => $r['selfhost_admin'].'#accessing', 'category' => 'Selfhost', 'keywords' => 'access login admin url session timeout re-auth reauth password confirmation ADMIN_REAUTH_TIMEOUT ADMIN_REAUTH_MAX_LIFETIME SESSION_LIFETIME'],
            ['page' => 'Admin Panel', 'section' => 'Venue map', 'description' => 'Enable the map of venues with MAP_GEOCODER_URL and MAP_TILE_URL, and what each service is sent.', 'url' => $r['selfhost_admin'].'#venue-map', 'category' => 'Selfhost', 'keywords' => 'venue map openstreetmap nominatim tiles geocoder address search MAP_GEOCODER_URL MAP_TILE_URL attribution'],
            ['page' => 'Admin Panel', 'section' => 'Dashboard', 'description' => 'Who is on the site right now with recurring revenue beside it, then the newest schedules and events, sign-ups, active users, sources, upcoming events and federation.', 'url' => $r['selfhost_admin'].'#dashboard', 'category' => 'Selfhost', 'keywords' => 'dashboard metrics overview live realtime sign-ups signups organizers active users mrr arr trials revenue upcoming events online in person federation traffic sources'],
            ['page' => 'Admin Panel', 'section' => 'Needs Attention', 'description' => 'Everything waiting on an admin, collected into one list on the dashboard with matching nav badges.', 'url' => $r['selfhost_admin'].'#needs-attention', 'category' => 'Selfhost', 'keywords' => 'needs attention alerts badges scheduler stalled not running jobs not draining failed jobs queue unrecognized subscriptions refunds awaiting confirmation unconfirmed pending domains stuck boosts promotions review federation flagged support unread update available'],
            ['page' => 'Admin Panel', 'section' => 'Accommodation Affiliate', 'description' => 'Enable the nearby-lodging map with STAY22_ENABLED and set the fallback affiliate ID.', 'url' => $r['selfhost_admin'].'#settings-accommodation', 'category' => 'Selfhost', 'keywords' => 'accommodation stay22 affiliate commission hotels lodging STAY22_ENABLED STAY22_AID config cache'],
            ['page' => 'Admin Panel', 'section' => 'Live view', 'description' => 'The live part of the dashboard: visitors, pages, sources, countries and the last 24 hours of sign-ups and orders. Visitors are identified only after they accept cookies.', 'url' => $r['selfhost_admin'].'#realtime', 'category' => 'Selfhost', 'keywords' => 'realtime live visitors online now active users traffic who is on the site consent sign-ups signups new users registrations activity orders'],
            ['page' => 'Admin Panel', 'section' => 'What schedule owners see', 'description' => 'The second Realtime switch: gives everyone who manages a schedule live traffic to their own pages, with visitors as rows with no name, and their own last 24 hours of sales, registrations, followers and requests. Says whose consent counts.', 'url' => $r['selfhost_admin'].'#realtime-owner-view', 'category' => 'Selfhost', 'keywords' => 'realtime schedule owners organizers own view second switch live traffic unnamed rows consent cookie banner listed activity sales registrations followers requests door arrivals'],
            ['page' => 'Admin Panel', 'section' => 'Users', 'description' => 'Who signed up, how far they got and where they came from.', 'url' => $r['selfhost_admin'].'#insights-users', 'category' => 'Selfhost', 'keywords' => 'users management accounts'],
            ['page' => 'Admin Panel', 'section' => 'Revenue', 'description' => 'Ticket revenue across every schedule, MRR by plan, sales with an amount mismatch, and refunds awaiting confirmation.', 'url' => $r['selfhost_admin'].'#insights-revenue', 'category' => 'Selfhost', 'keywords' => 'revenue income money analytics mrr arr recurring subscriptions trial at risk outside stripe amount mismatch approve refund refunds awaiting confirmation unconfirmed stripe paypal refund rate partial'],
            ['page' => 'Admin Panel', 'section' => 'Analytics', 'description' => 'Traffic and usage analytics.', 'url' => $r['selfhost_admin'].'#insights-analytics', 'category' => 'Selfhost', 'keywords' => 'analytics traffic views'],
            ['page' => 'Admin Panel', 'section' => 'Usage', 'description' => 'Calls to email, AI, calendar and payment providers against daily limits, and the translation backlog.', 'url' => $r['selfhost_admin'].'#insights-usage', 'category' => 'Selfhost', 'keywords' => 'usage features tracking'],
            ['page' => 'Admin Panel', 'section' => 'Boost Management', 'description' => 'Manage Boost ad campaigns.', 'url' => $r['selfhost_admin'].'#manage-boost', 'category' => 'Selfhost', 'keywords' => 'boost campaigns manage ads'],
            ['page' => 'Admin Panel', 'section' => 'Schedules', 'description' => 'Manage any schedule on any install: edit its details, verify it, release a squatted subdomain, mark it deleted and restore it. Plans are assigned on hosted installs only.', 'url' => $r['selfhost_admin'].'#manage-plans', 'category' => 'Selfhost', 'keywords' => 'plans tiers subscription manage schedules delete deleted restore rename subdomain release free up squatted taken unclaimed ownerless junk takedown undo this is not me claimed owner filter curator approved list'],
            ['page' => 'Admin Panel', 'section' => 'Blocked', 'description' => 'Block an account that abuses the service: it is signed out, its schedules go offline, and its email address, domain, network address or country can be refused at sign-up, for as long as you choose.', 'url' => $r['selfhost_admin'].'#manage-blocked', 'category' => 'Selfhost', 'keywords' => 'block blocked ban banned spam spammer spammers abuse suspend account unblock blocklist blacklist refuse sign-up signup email domain ip address network range country geoblock temporary expires'],
            ['page' => 'Admin Panel', 'section' => 'Private Calendars', 'description' => 'Schedules that look as though a calendar import is publishing a private calendar: hide what is public, pause the sync, tell the owner, mark reviewed.', 'url' => $r['selfhost_admin'].'#manage-private-calendars', 'category' => 'Selfhost', 'keywords' => 'private calendar passcode zoom teams dial-in email thread exposed leak hide pause owner review'],
            ['page' => 'Admin Panel', 'section' => 'Accounts that may be spam', 'description' => 'Accounts with a watched word such as paypal or bitcoin in a schedule they own or an event they made are listed for review: block them or mark them not spam.', 'url' => $r['selfhost_admin'].'#blocked-spam', 'category' => 'Selfhost', 'keywords' => 'spam suspect suspicious may be spam watch words watched words paypal bitcoin crypto flag flagged not spam review'],
            ['page' => 'Admin Panel', 'section' => 'Feeds', 'description' => 'Every feed on the install: which schedule reads what, whether it is being read, and Read now or Resume for one that is not.', 'url' => $r['selfhost_admin'].'#manage-feeds', 'category' => 'Selfhost', 'keywords' => 'feeds admin failing paused read now resume ics rss import'],
            ['page' => 'Admin Panel', 'section' => 'Domains Management', 'description' => 'Manage custom domains (SaaS only).', 'url' => $r['selfhost_admin'].'#manage-domains', 'category' => 'Selfhost', 'keywords' => 'domains custom manage'],
            ['page' => 'Admin Panel', 'section' => 'Newsletters Management', 'description' => 'Manage newsletters from the admin panel.', 'url' => $r['selfhost_admin'].'#manage-newsletters', 'category' => 'Selfhost', 'keywords' => 'newsletters manage admin'],
            ['page' => 'Admin Panel', 'section' => 'Audit Log', 'description' => 'View platform activity audit log.', 'url' => $r['selfhost_admin'].'#system-audit-log', 'category' => 'Selfhost', 'keywords' => 'audit log activity tracking'],
            ['page' => 'Admin Panel', 'section' => 'Queue', 'description' => 'Monitor the scheduler and the background job queue: heartbeat, rails, per-task health and failed jobs.', 'url' => $r['selfhost_admin'].'#system-queue', 'category' => 'Selfhost', 'keywords' => 'queue jobs background worker scheduler schedule tasks cron heartbeat stalled overdue rails'],
            ['page' => 'Admin Panel', 'section' => 'Logs', 'description' => 'View application error logs.', 'url' => $r['selfhost_admin'].'#system-logs', 'category' => 'Selfhost', 'keywords' => 'logs errors debug'],
            ['page' => 'Admin Panel', 'section' => 'Settings', 'description' => 'Add custom header and footer code (Google Tag Manager, analytics) injected into all public guest pages.', 'url' => $r['selfhost_admin'].'#system-settings', 'category' => 'Selfhost', 'keywords' => 'settings header footer code google tag manager gtm analytics tracking script head body'],
            ['page' => 'Admin Panel', 'section' => 'Plan pricing', 'description' => 'Set what your platform advertises Pro and Enterprise at, without editing .env, and move every page at once.', 'url' => $r['selfhost_admin'].'#settings-plan-pricing', 'category' => 'Selfhost', 'keywords' => 'plan pricing custom pricing price amount pro enterprise monthly yearly advertise marketing prices upgrade prompts STRIPE_PRICE_MONTHLY_AMOUNT selfhost'],
            ['page' => 'Admin Panel', 'section' => 'Platform Currency', 'description' => 'Set the currency your platform shows its own prices in, and the fallback currency for a new event.', 'url' => $r['selfhost_admin'].'#settings-currency', 'category' => 'Selfhost', 'keywords' => 'platform currency symbol dollar euro pound rand yen PLATFORM_CURRENCY plan price marketing prices default currency white label'],
            ['page' => 'Admin Panel', 'section' => 'Event Schedule network', 'description' => 'Share your public events with eventschedule.com, pick which of your schedules to list, and see what has been sent.', 'url' => $r['selfhost_admin'].'#settings-network', 'category' => 'Selfhost', 'keywords' => 'federation network share events eventschedule.com listings contact email list these schedules connection status pending approved suspended preview sent needs an image'],
            ['page' => 'Admin Panel', 'section' => 'Cookie consent banner', 'description' => 'When the cookie banner appears, what the UTM attribution cookies do, and why the built-in analytics need no consent.', 'url' => $r['selfhost_admin'].'#cookie-consent', 'category' => 'Selfhost', 'keywords' => 'cookie consent banner gdpr privacy COOKIE_CONSENT_BANNER utm_params utm_referrer_url utm_landing_page attribution tracking analytics anonymous aggregate hashed ip'],
            ['page' => 'Admin Panel', 'section' => 'App Update', 'description' => 'See the installed version next to the latest release, and apply an update in one click.', 'url' => $r['selfhost_admin'].'#system-app-update', 'category' => 'Selfhost', 'keywords' => 'update upgrade version release github selfhost one click migrations app:update update app missing notification badge'],
            ['page' => 'Admin Panel', 'section' => 'Translations', 'description' => 'Review and customize the app\'s text in any language, and share improvements with the community.', 'url' => $r['selfhost_admin'].'#system-translations', 'category' => 'Selfhost', 'keywords' => 'translations translate language customize text terminology rename wording localization locale strings'],
            ['page' => 'Admin Panel', 'section' => 'Sharing translations', 'description' => 'Share your translation improvements with the EventSchedule community for review.', 'url' => $r['selfhost_admin'].'#translations-sharing', 'category' => 'Selfhost', 'keywords' => 'share translations community contribute suggestions auto-share'],
            ['page' => 'Admin Panel', 'section' => 'Legal Pages', 'description' => 'Replace the built-in privacy policy and terms of service with your own, and add a cookie policy.', 'url' => $r['selfhost_admin'].'#system-legal-pages', 'category' => 'Selfhost', 'keywords' => 'legal privacy policy terms of service cookie policy gdpr paia popia ccpa compliance jurisdiction consent custom documents markdown'],
            ['page' => 'Admin Panel', 'section' => 'Growth', 'description' => 'Hosted only: activation, free plan pressure, MRR, nudges, churn and acquisition.', 'url' => $r['selfhost_admin'].'#insights-growth', 'category' => 'Selfhost', 'keywords' => 'growth activation mrr churn acquisition'],
            ['page' => 'Admin Panel', 'section' => 'Referrals', 'description' => 'Hosted only: every referral and its status.', 'url' => $r['selfhost_admin'].'#manage-referrals', 'category' => 'Selfhost', 'keywords' => 'referrals admin status credit'],
            ['page' => 'Admin Panel', 'section' => 'Blog', 'description' => 'eventschedule.com only: the blog\'s posts.', 'url' => $r['selfhost_admin'].'#manage-blog', 'category' => 'Selfhost', 'keywords' => 'blog posts admin'],
            ['page' => 'Admin Panel', 'section' => 'Realtime visitors setting', 'description' => 'Switch live page views on or off, and whether schedule owners see traffic to their own pages.', 'url' => $r['selfhost_admin'].'#settings-realtime', 'category' => 'Selfhost', 'keywords' => 'realtime setting live page views owners'],
            ['page' => 'Admin Panel', 'section' => 'Work Waiting', 'description' => 'Rows the scheduled tasks still have to get through, with the translation rate.', 'url' => $r['selfhost_admin'].'#queue-work-waiting', 'category' => 'Selfhost', 'keywords' => 'work waiting backlog translation queue'],
            ['page' => 'Admin Panel', 'section' => 'Subscriptions billing for deleted schedules', 'description' => 'Subscriptions Stripe is still charging for a schedule that was deleted, with Cancel in Stripe.', 'url' => $r['selfhost_admin'].'#revenue-orphaned-subscriptions', 'category' => 'Selfhost', 'keywords' => 'orphaned subscription deleted schedule cancel in stripe'],
            ['page' => 'Admin Panel', 'section' => 'Date range', 'description' => 'The period select on the admin pages and what it covers.', 'url' => $r['selfhost_admin'].'#date-range', 'category' => 'Selfhost', 'keywords' => 'date range period last 30 days'],
            ['page' => 'Admin Panel', 'section' => 'Federation', 'description' => 'eventschedule.com only: reviewing the installs that share events with the network.', 'url' => $r['selfhost_admin'].'#system-federation', 'category' => 'Selfhost', 'keywords' => 'federation moderation approve suspend'],
            ['page' => 'Admin Panel', 'section' => 'Support', 'description' => 'Hosted only: the support chat inbox.', 'url' => $r['selfhost_admin'].'#system-support', 'category' => 'Selfhost', 'keywords' => 'support chat conversations'],
            ['page' => 'Admin Panel', 'section' => 'Boost partners', 'description' => 'The Boost partners card: partners who run boosts on their own Page and ad account.', 'url' => $r['selfhost_admin'].'#boost-partners', 'category' => 'Selfhost', 'keywords' => 'boost partners partner meta page ad account fee statements'],

            // Boost Setup (Selfhost)
            ['page' => 'Boost Setup', 'section' => 'Overview', 'description' => 'Set up Meta/Facebook boost for selfhosted instances.', 'url' => $r['selfhost_boost'].'#overview', 'category' => 'Selfhost', 'keywords' => 'boost meta facebook setup'],
            ['page' => 'Boost Setup', 'section' => 'Create a Facebook App', 'description' => 'Create and configure a Facebook app.', 'url' => $r['selfhost_boost'].'#facebook-app', 'category' => 'Selfhost', 'keywords' => 'facebook app create meta'],
            ['page' => 'Boost Setup', 'section' => 'Meta Business & Ad Account', 'description' => 'Set up business and ad accounts.', 'url' => $r['selfhost_boost'].'#ad-account', 'category' => 'Selfhost', 'keywords' => 'business ad account meta'],
            ['page' => 'Boost Setup', 'section' => 'Facebook Page', 'description' => 'Configure a Facebook page for ads.', 'url' => $r['selfhost_boost'].'#facebook-page', 'category' => 'Selfhost', 'keywords' => 'facebook page configure'],
            ['page' => 'Boost Setup', 'section' => 'System User & Access Token', 'description' => 'Generate a system user access token.', 'url' => $r['selfhost_boost'].'#system-user', 'category' => 'Selfhost', 'keywords' => 'system user token access'],
            ['page' => 'Boost Setup', 'section' => 'Meta Pixel', 'description' => 'Set up Meta Pixel for conversion tracking.', 'url' => $r['selfhost_boost'].'#pixel', 'category' => 'Selfhost', 'keywords' => 'pixel tracking conversion meta cookie consent'],
            ['page' => 'Boost Setup', 'section' => 'Webhooks', 'description' => 'Configure webhooks for Boost integration.', 'url' => $r['selfhost_boost'].'#webhooks', 'category' => 'Selfhost', 'keywords' => 'webhooks callback notifications'],
            ['page' => 'Boost Setup', 'section' => 'App Review', 'description' => 'Submit your app for Meta review.', 'url' => $r['selfhost_boost'].'#app-review', 'category' => 'Selfhost', 'keywords' => 'review approval meta facebook'],
            ['page' => 'Boost Setup', 'section' => 'Environment Variables', 'description' => 'Complete .env configuration for Boost.', 'url' => $r['selfhost_boost'].'#environment', 'category' => 'Selfhost', 'keywords' => 'env environment variables configuration'],
            ['page' => 'Boost Setup', 'section' => 'Scheduled Command', 'description' => 'Set up the Boost sync scheduler.', 'url' => $r['selfhost_boost'].'#scheduled-command', 'category' => 'Selfhost', 'keywords' => 'scheduler cron command sync'],
            ['page' => 'Boost Setup', 'section' => 'The Admin Boost Page', 'description' => 'What the admin panel\'s Boost page shows on a selfhosted install, and which cards are hosted only.', 'url' => $r['selfhost_boost'].'#admin-boost-page', 'category' => 'Selfhost', 'keywords' => 'admin boost page campaigns credit spending limit'],
            ['page' => 'Boost Setup', 'section' => 'Boost partners (hosted only)', 'description' => 'The extra variables and how a partner connects and is confirmed.', 'url' => $r['selfhost_boost'].'#boost-partners', 'category' => 'Selfhost', 'keywords' => 'boost partners BOOST_PARTNERS_ENABLED META_BUSINESS_ID META_SYSTEM_USER_ID META_LOGIN_CONFIG_ID hosted'],

            // ===== SAAS =====

            // SaaS Setup
            ['page' => 'SaaS Setup', 'section' => 'Overview', 'description' => 'Deploy Event Schedule as a multi-tenant SaaS.', 'url' => $r['saas_setup'].'#overview', 'category' => 'SaaS', 'keywords' => 'saas multi-tenant deploy'],
            ['page' => 'SaaS Setup', 'section' => 'Prerequisites', 'description' => 'Requirements for SaaS deployment.', 'url' => $r['saas_setup'].'#prerequisites', 'category' => 'SaaS', 'keywords' => 'requirements prerequisites'],
            ['page' => 'SaaS Setup', 'section' => 'Environment Variables', 'description' => 'SaaS-specific .env configuration, your own branding, and the one licence credit you cannot repoint.', 'url' => $r['saas_setup'].'#environment', 'category' => 'SaaS', 'keywords' => 'env environment configuration branding white label logo app name APP_MARKETING_URL attribution credit licence'],
            ['page' => 'SaaS Setup', 'section' => 'Push Notifications', 'description' => 'Enable optional OneSignal web push notifications platform-wide.', 'url' => $r['saas_setup'].'#push-notifications', 'category' => 'SaaS', 'keywords' => 'push notifications onesignal web push browser mobile alerts ONESIGNAL_APP_ID'],
            ['page' => 'SaaS Setup', 'section' => 'Google Wallet Passes', 'description' => 'Give every ticket buyer on your platform an Add to Google Wallet button with one issuer account.', 'url' => $r['saas_setup'].'#google-wallet', 'category' => 'SaaS', 'keywords' => 'google wallet pass passes ticket mobile wallet issuer GOOGLE_WALLET_ISSUER_ID GOOGLE_WALLET_SERVICE_ACCOUNT GOOGLE_WALLET_ID_PREFIX'],
            ['page' => 'SaaS Setup', 'section' => 'Running Behind a Reverse Proxy', 'description' => 'Set TRUSTED_PROXIES when running behind Nginx, Apache, or Cloudflare.', 'url' => $r['saas_setup'].'#reverse-proxy', 'category' => 'SaaS', 'keywords' => 'proxy reverse proxy nginx apache cloudflare hestiacp https forwarded proto redirect loop TRUSTED_PROXIES'],
            ['page' => 'SaaS Setup', 'section' => 'DNS Setup', 'description' => 'Configure subdomain DNS for SaaS.', 'url' => $r['saas_setup'].'#dns', 'category' => 'SaaS', 'keywords' => 'dns subdomain wildcard'],
            ['page' => 'SaaS Setup', 'section' => 'Web Server', 'description' => 'Web server configuration for SaaS mode.', 'url' => $r['saas_setup'].'#webserver', 'category' => 'SaaS', 'keywords' => 'webserver nginx apache'],
            ['page' => 'SaaS Setup', 'section' => 'Stripe Integration', 'description' => 'Configure Stripe for SaaS subscriptions.', 'url' => $r['saas_setup'].'#stripe', 'category' => 'SaaS', 'keywords' => 'stripe payment subscription'],
            ['page' => 'SaaS Setup', 'section' => 'Plan pricing', 'description' => 'Set the Pro and Enterprise amounts your platform shows; the Stripe Price decides the charge.', 'url' => $r['saas_setup'].'#stripe-variables', 'category' => 'SaaS', 'keywords' => 'plan pricing custom pricing price amount pro enterprise monthly yearly white label STRIPE_PRICE_MONTHLY_AMOUNT'],
            ['page' => 'SaaS Setup', 'section' => 'Plan currency', 'description' => 'Set PLATFORM_CURRENCY so your plan prices show in your own currency rather than dollars.', 'url' => $r['saas_setup'].'#stripe-variables', 'category' => 'SaaS', 'keywords' => 'PLATFORM_CURRENCY plan price currency symbol dollar euro pound rand white label branding'],
            ['page' => 'SaaS Setup', 'section' => 'Example', 'description' => 'Complete SaaS setup example.', 'url' => $r['saas_setup'].'#example', 'category' => 'SaaS', 'keywords' => 'example tutorial walkthrough'],
            ['page' => 'SaaS Setup', 'section' => 'Verification', 'description' => 'Verify your SaaS setup is working.', 'url' => $r['saas_setup'].'#verification', 'category' => 'SaaS', 'keywords' => 'verify test check'],
            ['page' => 'SaaS Setup', 'section' => 'Demo Mode', 'description' => 'The demo host and its sample data, created and reset hourly by the scheduler on a hosted install.', 'url' => $r['saas_setup'].'#demo', 'category' => 'SaaS', 'keywords' => 'demo test account sample'],
            ['page' => 'SaaS Setup', 'section' => 'Troubleshooting', 'description' => 'Fix common SaaS setup issues.', 'url' => $r['saas_setup'].'#troubleshooting', 'category' => 'SaaS', 'keywords' => 'troubleshoot fix problem'],
            ['page' => 'SaaS Setup', 'section' => 'Related Documentation', 'description' => 'Links to related SaaS documentation.', 'url' => $r['saas_setup'].'#related', 'category' => 'SaaS', 'keywords' => ''],
            ['page' => 'SaaS Setup', 'section' => 'Security', 'description' => 'Security considerations for SaaS deployments.', 'url' => $r['saas_setup'].'#security', 'category' => 'SaaS', 'keywords' => 'security ssl https'],
            ['page' => 'SaaS Setup', 'section' => 'Support Chat', 'description' => 'Built-in real-time chat for user support.', 'url' => $r['saas_setup'].'#support-chat', 'category' => 'SaaS', 'keywords' => 'support chat help message admin conversation availability online offline visitor'],
            ['page' => 'SaaS Setup', 'section' => 'Legal Pages', 'description' => 'Publish your own privacy policy, terms and cookie policy under System, Legal Pages.', 'url' => $r['saas_setup'].'#legal-pages', 'category' => 'SaaS', 'keywords' => 'legal privacy policy terms cookies'],
            ['page' => 'SaaS Setup', 'section' => 'Trials', 'description' => 'TRIAL_DAYS sets the length of the one free trial, once per account.', 'url' => $r['saas_setup'].'#trials', 'category' => 'SaaS', 'keywords' => 'trial trial_days once per account selling trial retired'],
            ['page' => 'SaaS Setup', 'section' => 'Branding', 'description' => 'Your logos and name, and the licence credit.', 'url' => $r['saas_setup'].'#branding', 'category' => 'SaaS', 'keywords' => 'logo app_logo branding white label'],
            ['page' => 'SaaS Setup', 'section' => 'Webhook Endpoint', 'description' => 'The Stripe webhook endpoint for subscriptions and the events it needs.', 'url' => $r['saas_setup'].'#stripe-webhook', 'category' => 'SaaS', 'keywords' => 'stripe webhook endpoint subscription'],
            ['page' => 'SaaS Setup', 'section' => 'How Subscriptions Work', 'description' => 'From Upgrade on the Plan tab to the webhook that grants the plan.', 'url' => $r['saas_setup'].'#stripe-flow', 'category' => 'SaaS', 'keywords' => 'subscription flow upgrade plan tab'],
            ['page' => 'SaaS Setup', 'section' => 'HTTP cron endpoint', 'description' => 'Drive the scheduler by requesting a URL once a minute.', 'url' => $r['saas_setup'].'#http-cron', 'category' => 'SaaS', 'keywords' => 'http cron translate_data app_cron_secret'],
            ['page' => 'SaaS Setup', 'section' => 'Knowing that it stopped', 'description' => 'The Scheduler card on the Queue page, SCHEDULER_RAIL and SCHEDULER_EXPECTED_RAIL.', 'url' => $r['saas_setup'].'#scheduler-health', 'category' => 'SaaS', 'keywords' => 'scheduler stalled health rail'],
            ['page' => 'SaaS Setup', 'section' => 'The minimum to go live', 'description' => 'The steps a platform needs before it opens.', 'url' => $r['saas_setup'].'#go-live-checklist', 'category' => 'SaaS', 'keywords' => 'go live checklist launch minimum steps'],

            // Custom Domains (SaaS)
            ['page' => 'Custom Domains', 'section' => 'Overview', 'description' => 'Allow users to use custom domains.', 'url' => $r['saas_custom_domains'].'#overview', 'category' => 'SaaS', 'keywords' => 'custom domain branded url'],
            ['page' => 'Custom Domains', 'section' => 'Prerequisites', 'description' => 'DigitalOcean requirements for custom domains.', 'url' => $r['saas_custom_domains'].'#prerequisites', 'category' => 'SaaS', 'keywords' => 'digitalocean requirements'],
            ['page' => 'Custom Domains', 'section' => 'Environment Setup', 'description' => 'Configure DigitalOcean API for domains.', 'url' => $r['saas_custom_domains'].'#environment', 'category' => 'SaaS', 'keywords' => 'env configuration digitalocean api'],
            ['page' => 'Custom Domains', 'section' => 'How It Works', 'description' => 'Redirect vs Direct domain modes.', 'url' => $r['saas_custom_domains'].'#how-it-works', 'category' => 'SaaS', 'keywords' => 'redirect direct mode proxy'],
            ['page' => 'Custom Domains', 'section' => 'DNS Setup for Customers', 'description' => 'Customer CNAME configuration instructions.', 'url' => $r['saas_custom_domains'].'#dns-setup', 'category' => 'SaaS', 'keywords' => 'dns cname configuration'],
            ['page' => 'Custom Domains', 'section' => 'Admin Management', 'description' => 'Domain management dashboard for admins.', 'url' => $r['saas_custom_domains'].'#admin-management', 'category' => 'SaaS', 'keywords' => 'admin manage dashboard'],
            ['page' => 'Custom Domains', 'section' => 'Troubleshooting', 'description' => 'Fix custom domain issues.', 'url' => $r['saas_custom_domains'].'#troubleshooting', 'category' => 'SaaS', 'keywords' => 'troubleshoot fix dns ssl'],
            ['page' => 'Custom Domains', 'section' => 'What changes on a direct custom domain', 'description' => 'What works differently on a schedule served from its own domain.', 'url' => $r['saas_custom_domains'].'#direct-differences', 'category' => 'SaaS', 'keywords' => 'direct custom domain differences signed in'],
            ['page' => 'Custom Domains', 'section' => 'Main address', 'description' => 'Keep the subdomain as a schedule\'s main address while the custom domain still serves every page.', 'url' => $r['saas_custom_domains'].'#main-address', 'category' => 'SaaS', 'keywords' => 'main address subdomain canonical custom domain direct sitemap emails links'],

            // Twilio (SaaS)
            ['page' => 'Twilio Integration', 'section' => 'Overview', 'description' => 'Phone verification and WhatsApp for SaaS.', 'url' => $r['saas_twilio'].'#overview', 'category' => 'SaaS', 'keywords' => 'twilio phone sms whatsapp'],
            ['page' => 'Twilio Integration', 'section' => 'Create a Twilio Account', 'description' => 'Set up a Twilio account for messaging.', 'url' => $r['saas_twilio'].'#create-account', 'category' => 'SaaS', 'keywords' => 'twilio account setup register'],
            ['page' => 'Twilio Integration', 'section' => 'Environment Setup', 'description' => 'Configure Twilio environment variables.', 'url' => $r['saas_twilio'].'#environment', 'category' => 'SaaS', 'keywords' => 'env configuration twilio'],
            ['page' => 'Twilio Integration', 'section' => 'Phone Verification', 'description' => 'Implement phone number verification.', 'url' => $r['saas_twilio'].'#phone-verification', 'category' => 'SaaS', 'keywords' => 'phone verify number sms'],
            ['page' => 'Twilio Integration', 'section' => 'WhatsApp Setup', 'description' => 'Register and configure WhatsApp messaging.', 'url' => $r['saas_twilio'].'#whatsapp', 'category' => 'SaaS', 'keywords' => 'whatsapp messaging sender'],
            ['page' => 'Twilio Integration', 'section' => 'Testing', 'description' => 'Test SMS and WhatsApp functionality.', 'url' => $r['saas_twilio'].'#testing', 'category' => 'SaaS', 'keywords' => 'test sms whatsapp verify'],
            ['page' => 'Twilio Integration', 'section' => 'SMS Invitations', 'description' => 'Team invitations and claim links sent by text message.', 'url' => $r['saas_twilio'].'#sms-invitations', 'category' => 'SaaS', 'keywords' => 'sms invitations text message invite team claim link phone'],
            ['page' => 'Federation', 'section' => 'Overview', 'description' => 'Share your public events with the eventschedule.com listings.', 'url' => $r['saas_federation'].'#overview', 'category' => 'SaaS', 'keywords' => 'federation network listings discovery traffic backlink'],
            ['page' => 'Monetization', 'section' => 'Overview', 'description' => 'Show ads on free schedules and sell promotional placement to paid ones. The licence credit is not an upsell.', 'url' => $r['saas_monetization'].'#overview', 'category' => 'SaaS', 'keywords' => 'monetization ads adsense revenue advertising free tier earn upsell branding white label attribution credit'],
            ['page' => 'Monetization', 'section' => 'Consent and privacy', 'description' => 'Consent management, non-personalized ads and Global Privacy Control.', 'url' => $r['saas_monetization'].'#consent', 'category' => 'SaaS', 'keywords' => 'consent gdpr cmp privacy personalized ads sec-gpc eea uk cookie banner COOKIE_CONSENT_BANNER utm attribution cookies'],
            ['page' => 'Monetization', 'section' => 'Google AdSense', 'description' => 'Configure your publisher ID and ad slot for free-tier pages.', 'url' => $r['saas_monetization'].'#adsense', 'category' => 'SaaS', 'keywords' => 'adsense google publisher id ad slot ca-pub display unit'],
            ['page' => 'Monetization', 'section' => 'The promotions network', 'description' => 'Let paid schedules buy promotional placement on free schedules.', 'url' => $r['saas_monetization'].'#promotions', 'category' => 'SaaS', 'keywords' => 'promotions network cpm cpc budget placement advertise sponsored'],
            ['page' => 'Monetization', 'section' => 'Reviewing promotions', 'description' => 'Approve or reject campaigns before they run.', 'url' => $r['saas_monetization'].'#review', 'category' => 'SaaS', 'keywords' => 'review approve reject moderation queue refund'],
            ['page' => 'Monetization', 'section' => 'Where ads appear', 'description' => 'Which pages carry ads, and which never do.', 'url' => $r['saas_monetization'].'#where', 'category' => 'SaaS', 'keywords' => 'where ads appear checkout embed preview custom domain'],
            ['page' => 'Monetization', 'section' => 'Accommodation affiliate', 'description' => 'Let schedules show a map of nearby lodging, and set the fallback affiliate ID.', 'url' => $r['saas_monetization'].'#accommodation', 'category' => 'SaaS', 'keywords' => 'accommodation stay22 affiliate commission hotels lodging travel STAY22_ENABLED fallback affiliate id'],
            ['page' => 'Monetization', 'section' => 'Turning it on', 'description' => 'Switch on ads and the promotions network for the install.', 'url' => $r['saas_monetization'].'#enable', 'category' => 'SaaS', 'keywords' => 'enable ads_enabled monetization card'],
            ['page' => 'Monetization', 'section' => 'Turning it off', 'description' => 'What is safe to switch off, and what stops settling if ADS_ENABLED is removed with campaigns outstanding.', 'url' => $r['saas_monetization'].'#disable', 'category' => 'SaaS', 'keywords' => 'disable turn off ads_enabled'],
            ['page' => 'Monetization', 'section' => 'Environment variables', 'description' => 'Every monetization variable and its default.', 'url' => $r['saas_monetization'].'#environment', 'category' => 'SaaS', 'keywords' => 'env variables ads adsense promotions stay22'],
            ['page' => 'Monetization', 'section' => 'Opting out', 'description' => 'How any schedule keeps promotions and AdSense off its pages.', 'url' => $r['saas_monetization'].'#promotions-opt-out', 'category' => 'SaaS', 'keywords' => 'opt out promotions do not show'],
            ['page' => 'Federation', 'section' => 'Turning it on', 'description' => 'Enable the network and register your install for review.', 'url' => $r['saas_federation'].'#enable', 'category' => 'SaaS', 'keywords' => 'enable turn on register approve settings welcome email contact email list these schedules checklist status'],
            ['page' => 'Federation', 'section' => 'Per-schedule control', 'description' => 'Each schedule opts in for itself, in one click from the dashboard or its page; new schedules start undecided and share nothing.', 'url' => $r['saas_federation'].'#per-schedule', 'category' => 'SaaS', 'keywords' => 'opt in opt out per schedule toggle undecided co-listed veto list on the network prompt dashboard one click dismiss listed badge'],
            ['page' => 'Federation', 'section' => 'What a listing looks like', 'description' => 'Listings link straight back to the event on your site.', 'url' => $r['saas_federation'].'#listings', 'category' => 'SaaS', 'keywords' => 'listing card link backlink filter country language image needs an image preview'],
            ['page' => 'Federation', 'section' => 'Keeping it in sync', 'description' => 'Sharing runs hourly and removes events that stop qualifying.', 'url' => $r['saas_federation'].'#sync', 'category' => 'SaaS', 'keywords' => 'sync hourly cron federation:push verified reconnect rejected not recognised'],
            ['page' => 'Federation', 'section' => 'What is shared', 'description' => 'Every field that leaves your install, and where each one ends up.', 'url' => $r['saas_federation'].'#privacy', 'category' => 'SaaS', 'keywords' => 'privacy data shared attendees tickets what is sent fields schedule link contact email version online joining link'],
            ['page' => 'Federation', 'section' => 'Which Events Are Listed', 'description' => 'The conditions an event must meet to be shared with the network.', 'url' => $r['saas_federation'].'#what-qualifies', 'category' => 'SaaS', 'keywords' => 'qualify eligible public image listed'],
            ['page' => 'Federation', 'section' => 'When a Sync Does Not Complete', 'description' => 'The notices on the settings card and what to do about each.', 'url' => $r['saas_federation'].'#sync-errors', 'category' => 'SaaS', 'keywords' => 'sync error notice failed'],
            ['page' => 'Federation', 'section' => 'Turning It Off', 'description' => 'Switching off takes the listings down.', 'url' => $r['saas_federation'].'#turning-off', 'category' => 'SaaS', 'keywords' => 'disable turn off remove listings'],
            ['page' => 'Federation', 'section' => 'The Prompt on the Dashboard', 'description' => 'List on the network, in one click, from a schedule owner\'s dashboard.', 'url' => $r['saas_federation'].'#listing-prompt', 'category' => 'SaaS', 'keywords' => 'dashboard prompt list on the network'],
            ['page' => 'Federation', 'section' => 'Overview', 'description' => 'Share your public events with the eventschedule.com listings.', 'url' => $r['selfhost_federation'].'#overview', 'category' => 'Selfhost', 'keywords' => 'federation network listings discovery traffic backlink'],
            ['page' => 'Federation', 'section' => 'Turning it on', 'description' => 'Enable the network and register your install for review.', 'url' => $r['selfhost_federation'].'#enable', 'category' => 'Selfhost', 'keywords' => 'enable turn on register approve settings welcome email contact email list these schedules checklist status'],
            ['page' => 'Federation', 'section' => 'Per-schedule control', 'description' => 'Each schedule opts in for itself, in one click from the dashboard or its page; new schedules start undecided and share nothing.', 'url' => $r['selfhost_federation'].'#per-schedule', 'category' => 'Selfhost', 'keywords' => 'opt in opt out per schedule toggle undecided co-listed veto list on the network prompt dashboard one click dismiss listed badge'],
            ['page' => 'Federation', 'section' => 'What a listing looks like', 'description' => 'Listings link straight back to the event on your site.', 'url' => $r['selfhost_federation'].'#listings', 'category' => 'Selfhost', 'keywords' => 'listing card link backlink filter country language image needs an image preview'],
            ['page' => 'Federation', 'section' => 'Keeping it in sync', 'description' => 'Sharing runs hourly and removes events that stop qualifying.', 'url' => $r['selfhost_federation'].'#sync', 'category' => 'Selfhost', 'keywords' => 'sync hourly cron federation:push verified reconnect rejected not recognised'],
            ['page' => 'Federation', 'section' => 'What is shared', 'description' => 'Every field that leaves your install, and where each one ends up.', 'url' => $r['selfhost_federation'].'#privacy', 'category' => 'Selfhost', 'keywords' => 'privacy data shared attendees tickets what is sent fields schedule link contact email version online joining link'],
            ['page' => 'Federation', 'section' => 'Which Events Are Listed', 'description' => 'The conditions an event must meet to be shared with the network.', 'url' => $r['selfhost_federation'].'#what-qualifies', 'category' => 'Selfhost', 'keywords' => 'qualify eligible public image listed'],
            ['page' => 'Federation', 'section' => 'When a Sync Does Not Complete', 'description' => 'The notices on the settings card and what to do about each.', 'url' => $r['selfhost_federation'].'#sync-errors', 'category' => 'Selfhost', 'keywords' => 'sync error notice failed'],
            ['page' => 'Federation', 'section' => 'Turning It Off', 'description' => 'Switching off takes the listings down.', 'url' => $r['selfhost_federation'].'#turning-off', 'category' => 'Selfhost', 'keywords' => 'disable turn off remove listings'],
            ['page' => 'Federation', 'section' => 'The Prompt on the Dashboard', 'description' => 'List on the network, in one click, from a schedule owner\'s dashboard.', 'url' => $r['selfhost_federation'].'#listing-prompt', 'category' => 'Selfhost', 'keywords' => 'dashboard prompt list on the network'],

            // ===== DEVELOPER =====

            // API Reference
            ['page' => 'API Reference', 'section' => 'Authentication', 'description' => 'API key authentication for REST endpoints.', 'url' => $r['developer_api'].'#authentication', 'category' => 'Developer', 'keywords' => 'api key auth token bearer'],
            ['page' => 'API Reference', 'section' => 'Rate Limits', 'description' => 'API rate limiting and quotas.', 'url' => $r['developer_api'].'#rate-limits', 'category' => 'Developer', 'keywords' => 'rate limit throttle quota retry-after 429 window'],
            ['page' => 'API Reference', 'section' => 'Response Format', 'description' => 'JSON response structure and conventions.', 'url' => $r['developer_api'].'#response-format', 'category' => 'Developer', 'keywords' => 'json response format structure'],
            ['page' => 'API Reference', 'section' => 'Pagination', 'description' => 'Paginate through list endpoints.', 'url' => $r['developer_api'].'#pagination', 'category' => 'Developer', 'keywords' => 'pagination page per_page cursor'],
            ['page' => 'API Reference', 'section' => 'Register', 'description' => 'POST /api/register - Create an account and receive an API key.', 'url' => $r['developer_api'].'#register', 'category' => 'Developer', 'keywords' => 'register user create account send-code verification code'],
            ['page' => 'API Reference', 'section' => 'Login', 'description' => 'POST /api/login - Exchange an email and password for an API key.', 'url' => $r['developer_api'].'#login', 'category' => 'Developer', 'keywords' => 'login authenticate session api key 409 two-factor'],
            ['page' => 'API Reference', 'section' => 'List Schedules', 'description' => 'GET /api/schedules - List all schedules.', 'url' => $r['developer_api'].'#list-schedules', 'category' => 'Developer', 'keywords' => 'list schedules get index'],
            ['page' => 'API Reference', 'section' => 'Show Schedule', 'description' => 'GET /api/schedules/{subdomain} - Get schedule details.', 'url' => $r['developer_api'].'#show-schedule', 'category' => 'Developer', 'keywords' => 'show schedule get detail'],
            ['page' => 'API Reference', 'section' => 'Create Schedule', 'description' => 'POST /api/schedules - Create a new schedule.', 'url' => $r['developer_api'].'#create-schedule', 'category' => 'Developer', 'keywords' => 'create schedule post new'],
            ['page' => 'API Reference', 'section' => 'Update Schedule', 'description' => 'PUT /api/schedules/{subdomain} - Update a schedule.', 'url' => $r['developer_api'].'#update-schedule', 'category' => 'Developer', 'keywords' => 'update schedule put modify'],
            ['page' => 'API Reference', 'section' => 'Delete Schedule', 'description' => 'DELETE /api/schedules/{subdomain} - Delete a schedule.', 'url' => $r['developer_api'].'#delete-schedule', 'category' => 'Developer', 'keywords' => 'delete schedule remove destroy'],
            ['page' => 'API Reference', 'section' => 'List Sub-Schedules', 'description' => 'GET /api/schedules/{subdomain}/groups - List sub-schedules.', 'url' => $r['developer_api'].'#list-groups', 'category' => 'Developer', 'keywords' => 'list groups sub-schedules get'],
            ['page' => 'API Reference', 'section' => 'Create Sub-Schedule', 'description' => 'POST /api/schedules/{subdomain}/groups - Create a sub-schedule.', 'url' => $r['developer_api'].'#create-group', 'category' => 'Developer', 'keywords' => 'create group sub-schedule post'],
            ['page' => 'API Reference', 'section' => 'Update Sub-Schedule', 'description' => 'PUT /api/schedules/{subdomain}/groups/{group_id} - Update a sub-schedule.', 'url' => $r['developer_api'].'#update-group', 'category' => 'Developer', 'keywords' => 'update group sub-schedule put'],
            ['page' => 'API Reference', 'section' => 'Delete Sub-Schedule', 'description' => 'DELETE /api/schedules/{subdomain}/groups/{group_id} - Delete a sub-schedule.', 'url' => $r['developer_api'].'#delete-group', 'category' => 'Developer', 'keywords' => 'delete group sub-schedule remove'],
            ['page' => 'API Reference', 'section' => 'List Events', 'description' => 'GET /api/events - List all events.', 'url' => $r['developer_api'].'#list-events', 'category' => 'Developer', 'keywords' => 'list events get index'],
            ['page' => 'API Reference', 'section' => 'Show Event', 'description' => 'GET /api/events/{id} - Get event details.', 'url' => $r['developer_api'].'#show-event', 'category' => 'Developer', 'keywords' => 'show event get detail'],
            ['page' => 'API Reference', 'section' => 'Create Event', 'description' => 'POST /api/events/{subdomain} - Create a new event.', 'url' => $r['developer_api'].'#create-event', 'category' => 'Developer', 'keywords' => 'create event post new'],
            ['page' => 'API Reference', 'section' => 'Update Event', 'description' => 'PUT /api/events/{id} - Update an event.', 'url' => $r['developer_api'].'#update-event', 'category' => 'Developer', 'keywords' => 'update event put modify'],
            ['page' => 'API Reference', 'section' => 'Delete Event', 'description' => 'DELETE /api/events/{id} - Delete an event.', 'url' => $r['developer_api'].'#delete-event', 'category' => 'Developer', 'keywords' => 'delete event remove destroy'],
            ['page' => 'API Reference', 'section' => 'Cancel Event', 'description' => 'POST /api/events/{id}/cancel - Call an event off and keep its sales.', 'url' => $r['developer_api'].'#cancel-event', 'category' => 'Developer', 'keywords' => 'cancel event call off cancelled notify attendees sales is_cancelled'],
            ['page' => 'API Reference', 'section' => 'Restore Event', 'description' => 'POST /api/events/{id}/restore - Undo a cancellation.', 'url' => $r['developer_api'].'#restore-event', 'category' => 'Developer', 'keywords' => 'restore event undo cancellation uncancel'],
            ['page' => 'API Reference', 'section' => 'Upload Flyer', 'description' => 'POST /api/events/flyer/{event_id} - Upload an event flyer image.', 'url' => $r['developer_api'].'#upload-flyer', 'category' => 'Developer', 'keywords' => 'upload flyer image poster'],
            ['page' => 'API Reference', 'section' => 'List Categories', 'description' => 'GET /api/categories - List event categories.', 'url' => $r['developer_api'].'#list-categories', 'category' => 'Developer', 'keywords' => 'list categories get'],
            ['page' => 'API Reference', 'section' => 'List Sales', 'description' => 'GET /api/sales - List ticket sales.', 'url' => $r['developer_api'].'#list-sales', 'category' => 'Developer', 'keywords' => 'list sales tickets get'],
            ['page' => 'API Reference', 'section' => 'Show Sale', 'description' => 'GET /api/sales/{id} - Get sale details.', 'url' => $r['developer_api'].'#show-sale', 'category' => 'Developer', 'keywords' => 'show sale get detail'],
            ['page' => 'API Reference', 'section' => 'Create Sale', 'description' => 'POST /api/sales - Create a new sale.', 'url' => $r['developer_api'].'#create-sale', 'category' => 'Developer', 'keywords' => 'create sale post new'],
            ['page' => 'API Reference', 'section' => 'Update Sale Status', 'description' => 'PUT /api/sales/{id} - Mark a sale paid, refund it in full or in part, or cancel it.', 'url' => $r['developer_api'].'#update-sale', 'category' => 'Developer', 'keywords' => 'update sale put status mark_paid refund partial refund cancel idempotency_key'],
            ['page' => 'API Reference', 'section' => 'Delete Sale', 'description' => 'DELETE /api/sales/{id} - Delete a sale.', 'url' => $r['developer_api'].'#delete-sale', 'category' => 'Developer', 'keywords' => 'delete sale remove'],
            ['page' => 'API Reference', 'section' => 'List Feedback', 'description' => 'GET /api/feedback - Post-event ratings and comments.', 'url' => $r['developer_api'].'#list-feedback', 'category' => 'Developer', 'keywords' => 'feedback ratings reviews stars comments get'],
            ['page' => 'API Reference', 'section' => 'List Fan Content', 'description' => 'GET /api/fan-content - Approved fan comments, photos and videos.', 'url' => $r['developer_api'].'#list-fan-content', 'category' => 'Developer', 'keywords' => 'fan content comments photos videos approved get'],
            ['page' => 'API Reference', 'section' => 'List Appointment Types', 'description' => 'GET /api/schedules/{subdomain}/appointment-types - The appointment types a guest can book, with price, host and questions.', 'url' => $r['developer_api'].'#list-appointment-types', 'category' => 'Developer', 'keywords' => 'appointment types bookable get voice agent host questions next available'],
            ['page' => 'API Reference', 'section' => 'List Open Times', 'description' => 'GET /api/appointment-types/{id}/slots - Open times, each with a label in the schedule\'s timezone to read out.', 'url' => $r['developer_api'].'#list-appointment-slots', 'category' => 'Developer', 'keywords' => 'appointment slots open times availability label timezone get'],
            ['page' => 'API Reference', 'section' => 'Book an Appointment', 'description' => 'POST /api/appointment-types/{id}/bookings - Book a time for a guest; a taken time answers 409 with the nearest open ones.', 'url' => $r['developer_api'].'#create-appointment-booking', 'category' => 'Developer', 'keywords' => 'book appointment booking post voice agent slot_taken alternatives idempotency-key awaiting_payment pay_by summary'],
            ['page' => 'API Reference', 'section' => 'List Boosts', 'description' => 'GET /api/boosts - A boost partner\'s requests, with the exact ad, approval, payment and spend; updated_since keeps a checkout in step.', 'url' => $r['developer_api'].'#boosts', 'category' => 'Developer', 'keywords' => 'boosts partner network list updated_since version approval payment spend'],
            ['page' => 'API Reference', 'section' => 'Approve a Boost', 'description' => 'POST /api/boosts/{id}/approve - Approve the version reviewed; a stale version answers 409.', 'url' => $r['developer_api'].'#approve-boost', 'category' => 'Developer', 'keywords' => 'approve boost version stale decline note'],
            ['page' => 'API Reference', 'section' => 'Confirm a Payment', 'description' => 'POST /api/boosts/{id}/confirm-payment - Record your own payment reference; approved and paid launches the boost.', 'url' => $r['developer_api'].'#confirm-boost-payment', 'category' => 'Developer', 'keywords' => 'confirm payment reference amount stripe withdraw delete stop'],
            ['page' => 'API Reference', 'section' => 'Boost Statements', 'description' => 'GET /api/boost-statements - The monthly fee on actual ad spend, line by line.', 'url' => $r['developer_api'].'#boost-statements', 'category' => 'Developer', 'keywords' => 'statement fee spend adjustment monthly invoice'],
            ['page' => 'API Reference', 'section' => 'Error Handling', 'description' => 'HTTP status codes and error responses.', 'url' => $r['developer_api'].'#error-handling', 'category' => 'Developer', 'keywords' => 'error status code http'],
            ['page' => 'API Reference', 'section' => 'Ask for JSON', 'description' => 'Send Accept: application/json, or some errors come back as HTML.', 'url' => $r['developer_api'].'#accept-header', 'category' => 'Developer', 'keywords' => 'accept header json html error'],
            ['page' => 'API Reference', 'section' => 'Dates and Times', 'description' => 'The UTC, date and ISO 8601 formats the API uses.', 'url' => $r['developer_api'].'#dates-and-times', 'category' => 'Developer', 'keywords' => 'date time format utc iso 8601 timezone starts_at'],
            ['page' => 'API Reference', 'section' => 'The Schedule Object', 'description' => 'Every field the schedule endpoints return.', 'url' => $r['developer_api'].'#schedule-object', 'category' => 'Developer', 'keywords' => 'schedule object fields response'],
            ['page' => 'API Reference', 'section' => 'The Event Object', 'description' => 'Every field the event endpoints return.', 'url' => $r['developer_api'].'#event-object', 'category' => 'Developer', 'keywords' => 'event object fields response tickets members'],
            ['page' => 'API Reference', 'section' => 'The Sale Object', 'description' => 'Every field the sale endpoints return.', 'url' => $r['developer_api'].'#sale-object', 'category' => 'Developer', 'keywords' => 'sale object fields response tickets'],
            ['page' => 'API Reference', 'section' => 'What an Update Replaces or Removes', 'description' => 'What PUT /api/events/{id} resets or deletes when a field is left out.', 'url' => $r['developer_api'].'#update-event-removes', 'category' => 'Developer', 'keywords' => 'put update event removes promo codes tickets recurring'],
            ['page' => 'API Reference', 'section' => 'Refunds', 'description' => 'Full and partial refunds through PUT /api/sales/{id}.', 'url' => $r['developer_api'].'#refunds', 'category' => 'Developer', 'keywords' => 'refund sale partial amount stripe paypal'],
            ['page' => 'API Reference', 'section' => 'End Time', 'description' => 'Send ends_at instead of working out a duration in hours.', 'url' => $r['developer_api'].'#event-end-time', 'category' => 'Developer', 'keywords' => 'ends_at end time duration length hours utc daylight saving clock change'],
            ['page' => 'API Reference', 'section' => 'Flyer from an Address', 'description' => 'Send flyer_image_url with an event and the picture is fetched and stored as its flyer.', 'url' => $r['developer_api'].'#event-flyer-url', 'category' => 'Developer', 'keywords' => 'flyer_image_url flyer image url address picture poster remove flyer same call'],
            ['page' => 'API Reference', 'section' => 'External ID', 'description' => 'Give an event the id your own system knows it by, and find it again on the next sync.', 'url' => $r['developer_api'].'#event-external-id', 'category' => 'Developer', 'keywords' => 'external_id external id sync duplicate 409 conflict lookup source id import'],
            ['page' => 'API Reference', 'section' => 'Create or Update in One Call', 'description' => 'Send "upsert": true with an external_id: the first call creates the event, later calls update it.', 'url' => $r['developer_api'].'#event-upsert', 'category' => 'Developer', 'keywords' => 'upsert create or update external_id sync idempotent duplicate post events'],
            ['page' => 'API Reference', 'section' => 'Daily Cap', 'description' => 'The daily limit on events created per schedule and per account.', 'url' => $r['developer_api'].'#event-daily-cap', 'category' => 'Developer', 'keywords' => 'daily cap limit event_create_limit 422'],
            ['page' => 'API Reference', 'section' => 'Other ticket settings', 'description' => 'Ticket settings the API keeps but does not document: questions, seating band, passes.', 'url' => $r['developer_api'].'#event-ticket-settings', 'category' => 'Developer', 'keywords' => 'ticket custom fields seating band pass is_pass pass_ api'],

            // Webhooks (Developer)
            ['page' => 'Webhooks', 'section' => 'Overview', 'description' => 'Real-time notifications for events via HTTP callbacks.', 'url' => $r['developer_webhooks'].'#overview', 'category' => 'Developer', 'keywords' => 'webhooks callback http notifications retries retry timeout delivery log'],
            ['page' => 'Webhooks', 'section' => 'Setup', 'description' => 'Configure webhook endpoints and event types.', 'url' => $r['developer_webhooks'].'#setup', 'category' => 'Developer', 'keywords' => 'setup configure endpoint url settings developers tab all events secret'],
            ['page' => 'Webhooks', 'section' => 'Event Types', 'description' => 'Every webhook event type and exactly when each one fires.', 'url' => $r['developer_webhooks'].'#event-types', 'category' => 'Developer', 'keywords' => 'event types subscribe trigger sale.paid sale.refunded sale.cancelled installment.paid event.updated ticket.scanned'],
            ['page' => 'Webhooks', 'section' => 'Payload Format', 'description' => 'Webhook payload JSON structure.', 'url' => $r['developer_webhooks'].'#payload', 'category' => 'Developer', 'keywords' => 'payload json data format'],
            ['page' => 'Webhooks', 'section' => 'Request Headers', 'description' => 'HTTP headers sent with webhook requests.', 'url' => $r['developer_webhooks'].'#headers', 'category' => 'Developer', 'keywords' => 'headers http request'],
            ['page' => 'Webhooks', 'section' => 'Signature Verification', 'description' => 'Verify webhooks with HMAC-SHA256 signatures.', 'url' => $r['developer_webhooks'].'#verification', 'category' => 'Developer', 'keywords' => 'signature hmac sha256 verify security'],
            ['page' => 'Webhooks', 'section' => 'Best Practices', 'description' => 'Best practices for reliable webhook handling.', 'url' => $r['developer_webhooks'].'#best-practices', 'category' => 'Developer', 'keywords' => 'best practices reliability idempotent'],
            ['page' => 'Webhooks', 'section' => 'Testing', 'description' => 'Test webhook endpoints and payloads.', 'url' => $r['developer_webhooks'].'#testing', 'category' => 'Developer', 'keywords' => 'test debug webhook'],
            ['page' => 'Webhooks', 'section' => 'Managing a Webhook', 'description' => 'Enable or disable, test, edit, regenerate the secret, view deliveries and delete.', 'url' => $r['developer_webhooks'].'#managing', 'category' => 'Developer', 'keywords' => 'manage webhook enable disable test edit regenerate secret deliveries'],
            ['page' => 'Webhooks', 'section' => 'Group Orders', 'description' => 'One delivery per ticket holder, tied together by order_id and group_id.', 'url' => $r['developer_webhooks'].'#group-orders', 'category' => 'Developer', 'keywords' => 'group order order_id group_id individual tickets'],

            // ===== REFERRAL PROGRAM =====
            ['page' => 'Referral Program', 'section' => 'Overview', 'description' => 'Earn free months by referring other event organizers.', 'url' => $r['referral_program'].'#overview', 'category' => 'User Guide', 'keywords' => 'referral refer earn free credit discount'],
            ['page' => 'Referral Program', 'section' => 'How It Works', 'description' => 'They sign up, they subscribe, you earn a month, you apply it.', 'url' => $r['referral_program'].'#how-it-works', 'category' => 'User Guide', 'keywords' => 'referral steps share link sign up subscribe earn apply'],
            ['page' => 'Referral Program', 'section' => 'Your Referral Link', 'description' => 'Find and share your unique referral link.', 'url' => $r['referral_program'].'#referral-link', 'category' => 'User Guide', 'keywords' => 'referral link copy share'],
            ['page' => 'Referral Program', 'section' => 'Rewards', 'description' => 'Pro ('.plan_price($this->planPrice()).') and Enterprise ('.plan_price($this->planPrice(true)).') referral credits.', 'url' => $r['referral_program'].'#rewards', 'category' => 'User Guide', 'keywords' => 'referral reward credit pro enterprise free month'],
            ['page' => 'Referral Program', 'section' => 'Applying Credits', 'description' => 'Apply an earned credit to a schedule, and see what it will do there first.', 'url' => $r['referral_program'].'#applying-credits', 'category' => 'User Guide', 'keywords' => 'apply credit schedule referral invoice free month'],
            ['page' => 'Referral Program', 'section' => 'Referral Statuses', 'description' => 'Signed up, Subscribed, Earned, Applied and Expired statuses.', 'url' => $r['referral_program'].'#statuses', 'category' => 'User Guide', 'keywords' => 'referral status signed up subscribed earned applied expired pending qualified credited'],
            ['page' => 'Referral Program', 'section' => 'Referral History', 'description' => 'Every referral, its status and what comes next.', 'url' => $r['referral_program'].'#history', 'category' => 'User Guide', 'keywords' => 'referral history table list credit due expired'],
            ['page' => 'Referral Program', 'section' => 'Your Referrals', 'description' => 'How many of your referrals are at each of the four steps.', 'url' => $r['referral_program'].'#dashboard', 'category' => 'User Guide', 'keywords' => 'referrals dashboard steps signed up subscribed earned applied next credit due'],
        ];
    }
}
