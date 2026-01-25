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
    public const DEMO_SOCIAL_LINKS_ALL = [
        '{"url":"https://www.facebook.com/appeventschedule"}',
        '{"url":"https://www.instagram.com/eventschedule/"}',
        '{"url":"https://youtube.com/@EventSchedule"}',
        '{"url":"https://x.com/ScheduleEvent"}',
        '{"url":"https://www.linkedin.com/company/eventschedule/"}',
    ];

    /**
     * Get a random subset of 2-3 demo social links
     */
    public static function getRandomDemoSocialLinks(): string
    {
        $links = self::DEMO_SOCIAL_LINKS_ALL;
        $count = rand(2, 3);
        $selected = array_rand($links, $count);
        $selectedLinks = array_map(fn ($i) => json_decode($links[$i]), (array) $selected);

        return json_encode(array_values($selectedLinks));
    }

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
            $role->description = '**Your guide to everything happening in America\'s most nuclear-adjacent town!**

From Duff-fueled nights at Moe\'s to cultural enlightenment at the Aztec Theater, we curate the finest events our beloved city has to offer. Whether you\'re looking for live music, community gatherings, or entertainment that doesn\'t involve monorails (well, maybe sometimes), we\'ve got you covered.

> "Springfield: A city on the grow!" - Mayor Quimby (probably)

---

## What We Offer
- **Live Music** - From jazz to rock to whatever Otto\'s listening to
- **Comedy** - Stand-up, improv, and Krusty (when he shows up sober)
- **Nightlife** - The finest Duff this side of Shelbyville 🍺
- **Community Events** - Town meetings, talent shows, and controlled chaos

*D\'oh-n\'t miss out on the action! Our tire fire burns eternal, and so does our event calendar.*';
            $role->accept_requests = false;
            $role->plan_type = 'pro';
            $role->plan_expires = now()->addYear()->format('Y-m-d');
            $role->trial_ends_at = now()->addYear();
            $role->social_links = self::getRandomDemoSocialLinks();
            $role->header_image_url = 'demo_header_town.jpg';
            $role->profile_image_url = 'demo_profile_donuts.jpg';
            $role->font_family = 'Bangers';
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

        // Ensure curator has header image set
        $role->header_image_url = 'demo_header_town.jpg';
        $role->save();

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

            // Demo header images - illustrated/artistic style (wide banner format)
            'demo_header_town.jpg' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&h=400&fit=crop',
            'demo_header_bar.jpg' => 'https://images.unsplash.com/photo-1572116469696-31de0f17cc34?w=1200&h=400&fit=crop',
            'demo_header_bowling.jpg' => 'https://images.unsplash.com/photo-1545232979-8bf68ee9b1af?w=1200&h=400&fit=crop',       // neon bowling alley
            'demo_header_theater.jpg' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1200&h=400&fit=crop',
            'demo_header_concert.jpg' => 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=1200&h=400&fit=crop',        // concert crowd silhouettes with stage lights
            'demo_header_donuts.jpg' => 'https://images.unsplash.com/photo-1551024601-bec78aea704b?w=1200&h=400&fit=crop',
            'demo_header_civic.jpg' => 'https://images.unsplash.com/photo-1577495508048-b635879837f1?w=1200&h=400&fit=crop',        // auditorium with rows of seats
            'demo_header_rock.jpg' => 'https://images.unsplash.com/photo-1564186763535-ebb21ef5277f?w=1200&h=400&fit=crop',

            // Talent header images (1200x400)
            'demo_header_jazz.jpg' => 'https://images.unsplash.com/photo-1415201364774-f6f0bb35f28f?w=1200&h=400&fit=crop',           // saxophone jazz
            'demo_header_circus.jpg' => 'https://images.unsplash.com/photo-1513151233558-d860c5398176?w=1200&h=400&fit=crop',        // colorful carnival lights
            'demo_header_dj.jpg' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=1200&h=400&fit=crop',             // DJ mixing board
            'demo_header_country.jpg' => 'https://images.unsplash.com/photo-1649639763329-dccbd16fbf4d?w=1200&h=400&fit=crop',        // cowboy boots and hat
            'demo_header_film.jpg' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=1200&h=400&fit=crop',           // movie theater interior
            'demo_header_science.jpg' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&h=400&fit=crop',        // blue circuit board
            'demo_header_secret.jpg' => 'https://images.unsplash.com/photo-1548248823-ce16a73b6d49?w=1200&h=400&fit=crop',         // dark gothic stone hall interior

            // Venue profile images (square 400x400)
            'demo_profile_moes.jpg' => 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=400&h=400&fit=crop',       // neon bar sign
            'demo_profile_bowling.jpg' => 'https://images.unsplash.com/photo-1556302132-40bb13638500?w=400&h=400&fit=crop',       // bowling pins
            'demo_profile_theater.jpg' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=400&h=400&fit=crop',    // theater seats
            'demo_profile_amphitheater.jpg' => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=400&h=400&fit=crop', // outdoor stage
            'demo_profile_donuts.jpg' => 'https://images.unsplash.com/photo-1551024601-bec78aea704b?w=400&h=400&fit=crop',        // pink donut
            'demo_profile_gym.jpg' => 'https://images.unsplash.com/photo-1504450758481-7338eba7524a?w=400&h=400&fit=crop',        // basketball court
            'demo_profile_beer.jpg' => 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=400&h=400&fit=crop',       // beer glass
            'demo_profile_bowling_ball.jpg' => 'https://images.unsplash.com/photo-1595177707511-1daccc734e8e?w=400&h=400&fit=crop', // bowling ball
            'demo_profile_popcorn.jpg' => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?w=400&h=400&fit=crop',      // vintage film projector
            'demo_profile_donut_box.jpg' => 'https://images.unsplash.com/photo-1527515545081-5db817172677?w=400&h=400&fit=crop',    // donut box

            // Talent profile images (square 400x400)
            'demo_profile_jazz.jpg' => 'https://images.unsplash.com/photo-1415201364774-f6f0bb35f28f?w=400&h=400&fit=crop',       // saxophone
            'demo_profile_clown.jpg' => 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=400&h=400&fit=crop',      // circus clown
            'demo_profile_dj.jpg' => 'https://images.unsplash.com/photo-1571266028243-e4733b0f0bb0?w=400&h=400&fit=crop',         // DJ turntable
            'demo_profile_rock.jpg' => 'https://images.unsplash.com/photo-1498038432885-c6f3f1b912ee?w=400&h=400&fit=crop',       // rock band
            'demo_profile_country.jpg' => 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?w=400&h=400&fit=crop',    // cowboy guitar
            'demo_profile_film.jpg' => 'https://images.unsplash.com/photo-1526045612212-70caf35c14df?w=400&h=400&fit=crop',       // vintage film camera
            'demo_profile_science.jpg' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=400&h=400&fit=crop',    // chemistry beakers
            'demo_profile_secret.jpg' => 'https://images.unsplash.com/photo-1509228627152-72ae9ae6848d?w=400&h=400&fit=crop',     // mysterious pyramid
            'demo_profile_vinyl.jpg' => 'https://images.unsplash.com/photo-1603048588665-791ca8aea617?w=400&h=400&fit=crop',      // vinyl records
            'demo_profile_circus.jpg' => 'https://images.unsplash.com/photo-1566577739112-5180d4bf9390?w=400&h=400&fit=crop',     // circus tent
            'demo_profile_eye.jpg' => 'https://images.unsplash.com/photo-1584286595398-a59f230e4585?w=400&h=400&fit=crop',           // gothic stone carving detail
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
                'accent_color' => '#FFD700',
                'profile_image' => 'demo_profile_jazz.jpg',
                'header_image' => 'demo_header_jazz.jpg',
                'font_family' => 'Playfair_Display',
                'description' => '**Smooth jazz for the sophisticated Springfieldian.**

Fronted by Springfield Elementary\'s most accomplished saxophonist and future President, we bring intellectual improvisation to venues across town. From Bleeding Gums Murphy\'s timeless influence to original compositions about vegetarianism, feminism, and the eternal quest for a pony.

> "The blues isn\'t about feeling better. It\'s about making other people feel worse." - Bleeding Gums Murphy

---

## 🎵 What We Play
- 🎷 *Classic jazz standards* - Because some of us have taste
- 🎶 *Original compositions* - "Sax on the Beach," "Pony Dream #7"
- 🎹 *Bleeding Gums Murphy tributes* - Tissues not included

*Jazz isn\'t dead—it just smells funny. And we\'re here to change that, one saxophone solo at a time.*',
            ],
            [
                'name' => 'Krusty Entertainment',
                'subdomain' => 'demo-krusty',
                'background_colors' => '#f44336, #ff9800',
                'background_rotation' => 45,
                'accent_color' => '#FFEB3B',
                'profile_image' => 'demo_profile_clown.jpg',
                'header_image' => 'demo_header_circus.jpg',
                'font_family' => 'Fredoka_One',
                'description' => '**Forty years of making children laugh (and occasionally causing lawsuits).**

From seltzer bottles to heart bypass surgery, Krusty the Clown and his team deliver the finest in family entertainment that lawyers will approve. We\'ve survived product recalls, tax evasion charges, and that incident with the monkey.

> "I heartily endorse this event or product!" - Krusty (for a fee)

---

## 🤡 Our Services
- 🎭 **Live Comedy Shows** - Sideshow Mel not included (extra fee)
- 🎈 **Birthday Parties** - Mr. Teeny available for appearances
- 📺 **TV Tapings** - Be part of the Krusty the Clown Show!
- 🍔 **Krusty Burger Catering** - Partially gelatinated non-dairy gum-based beverages

⚠️ *Warning: Not responsible for any product endorsements, merchandise defects, camp experiences gone wrong, or Sideshow Bob-related incidents.*',
            ],
            [
                'name' => 'DJ Sideshow Bob',
                'subdomain' => 'demo-djbob',
                'background_colors' => '#7b1fa2, #00bcd4',
                'background_rotation' => 160,
                'accent_color' => '#00E5FF',
                'profile_image' => 'demo_profile_vinyl.jpg',
                'font_family' => 'Space_Grotesk',
                'header_image' => 'demo_header_dj.jpg',
                'description' => '**Sophisticated beats for the refined listener... and reformed criminal.**

Former children\'s entertainer turned DJ, bringing an eclectic mix of Gilbert & Sullivan, classical compositions, and surprisingly catchy electronica. Each set is a masterpiece of carefully orchestrated revenge—er, *rhythm*.

> "No one who speaks German could be an evil man!" - Parole Board

---

## 🎵 The Sound
- 🎭 *Gilbert & Sullivan remixes* - H.M.S. Pinafore meets house music
- 🎹 *Classical drops* - Beethoven with bass
- 💀 *Dark electronica* - Revenge is a dish best served with a sick beat
- 🌹 *Romantic interludes* - Die Bart, Die (it\'s German for "The Bart, The")

⚠️ *Warning: All rakes have been removed from the premises for your safety.*

*The rake drops at midnight. So do the beats.*',
            ],
            [
                'name' => 'Springfield Rockers',
                'subdomain' => 'demo-rockers',
                'background_colors' => '#212121, #616161',
                'background_rotation' => 180,
                'accent_color' => '#FF1744',
                'profile_image' => 'demo_profile_rock.jpg',
                'header_image' => 'demo_header_rock.jpg',
                'font_family' => 'Anton',
                'description' => '**Rock and roll from the heart of America\'s favorite town.**

We\'ve played every venue from the Isotopes Stadium to the Springfield dump (great acoustics, actually). Our sound is as authentic as the three-eyed fish in our river and as powerful as whatever\'s going on at Sector 7-G.

> "Rock and roll had become stagnant. Then we came along." - Band Member

---

## 🔊 Our Style
- 🎸 **Raw Power** - Like a nuclear reactor, but musical
- 🥁 **Heavy Drums** - Louder than Homer\'s snoring (almost)
- 🎤 **Anthems** - Songs about Springfield, beer, and more beer
- ⚡ **Pure Energy** - Powered by Duff and questionable life choices

*We put the "rock" in "Springfield rocks!" and the "roll" in "let\'s roll to Moe\'s after the show."*',
            ],
            [
                'name' => 'Lurleen Lumpkin',
                'subdomain' => 'demo-lurleen',
                'background_colors' => '#8B4513, #D2691E',
                'background_rotation' => 120,
                'accent_color' => '#FFD54F',
                'profile_image' => 'demo_profile_country.jpg',
                'header_image' => 'demo_header_country.jpg',
                'font_family' => 'Bitter',
                'description' => '**Your wife don\'t understand you, but I do.**

Springfield\'s sweetheart of country music, discovered at the Beer-N-Brawl by her former manager Homer Simpson. From humble waitress origins to chart-topping success, Lurleen brings heartfelt honky-tonk to venues across the state.

> "I\'ve been down so long, it looks like up to me." - Lurleen Lumpkin

---

## Hit Songs
- "Your Wife Don\'t Understand You"
- "I\'m Basting a Turkey With My Tears"
- "Don\'t Look Up My Dress Unless You Mean It"
- "Bagged Me a Homer"

*Stand by your ma-aa-aan...*',
            ],
            [
                'name' => 'Troy McClure Productions',
                'subdomain' => 'demo-troymcclure',
                'background_colors' => '#b71c1c, #880e4f',
                'background_rotation' => 200,
                'accent_color' => '#FFD700',
                'profile_image' => 'demo_profile_film.jpg',
                'header_image' => 'demo_header_film.jpg',
                'font_family' => 'Bodoni_Moda',
                'description' => '**You might remember me from such events as "The Half-Assed Approach to Foundation Repair" and "Get Confident, Stupid!"**

Bringing Hollywood glamour to Springfield since the \'70s. From educational films to dinner theater, we produce entertainment that\'s technically professional. If it\'s got Troy McClure\'s name on it, it was definitely filmed.

> "Hi, I\'m Troy McClure! You might remember me from such nature films as \'Earwigs: Eww!\' and \'Man vs. Nature: The Road to Victory.\'"

---

## 🎥 Our Productions Include
- 📺 **Educational Films** - "Firecrackers: The Silent Killer"
- 🎭 **Dinner Theater** - "Stop the Planet of the Apes, I Want to Get Off!"
- 🎬 **Infomercials** - Selling products you never knew you needed
- 📽️ **Medical Videos** - "Alice\'s Adventures Through the Windshield Glass"

⚠️ *Currently not available for fish-related engagements or aquarium openings.*

*Get confident, stupid!*',
            ],
            [
                'name' => 'Professor Frink Presents',
                'subdomain' => 'demo-frink',
                'background_colors' => '#0d47a1, #00695c',
                'background_rotation' => 90,
                'accent_color' => '#00E676',
                'profile_image' => 'demo_profile_science.jpg',
                'header_image' => 'demo_header_science.jpg',
                'font_family' => 'IBM_Plex_Sans',
                'description' => '**Science! With the talking and the demonstrations and the GLAVIN!**

Educational entertainment from Springfield\'s foremost inventor (and Nobel Prize... almost-winner). Witness experiments that probably won\'t explode! Marvel at robots that definitely won\'t turn evil! Each presentation comes with a 73% safety guarantee—mm-hai!

> "The computer was speaking of LOVEMAKING, n\'hey!" - Professor Frink

---

## 🧪 What to Expect
- 🤖 **Robot Demonstrations** - 87% less murderous than last time
- ⚗️ **Chemistry Shows** - Explosions are a feature, not a bug
- 🧬 **Genetic Experiments** - Blinky the three-eyed fish says hi
- 🚀 **Rocket Science** - To the stars! Or at least past the tire fire

⚠️ *Side effects may include enlightenment, confusion, mild temporal displacement, and an inexplicable urge to say "GLAVIN!"*

*P.U.! I mean, Ph.D.!*',
            ],
            [
                'name' => 'Stonecutters Guild',
                'subdomain' => 'demo-stonecutters',
                'background_colors' => '#5d4037, #ff8f00',
                'background_rotation' => 225,
                'accent_color' => '#FFB300',
                'profile_image' => 'demo_profile_beer.jpg',
                'header_image' => 'demo_header_secret.jpg',
                'font_family' => 'Cinzel',
                'description' => '**Who controls the Springfield events scene? WE DO! WE DO!**

An ancient and totally-not-secret society bringing exclusive entertainment to our members and the occasional worthy guest. We\'ve kept the metric system down, made Steve Guttenberg a star, and rigged every Oscar night since 1957.

> "Who keeps Atlantis off the maps? Who keeps the Martians under wraps? WE DO! WE DO!"

---

## 🔮 What We Control
- 🎭 **Exclusive Events** - For members and Number 908 (Homer... unfortunately)
- 🎵 **Sacred Concerts** - Songs in the key of mystery
- 🥳 **Private Parties** - Robes optional, parchment required
- 🏆 **Award Ceremonies** - We decide who wins everything

📜 *Sacred Parchment required at door. No Chosen Ones allowed (we learned our lesson).*

*Who robs cavefish of their sight? Who rigs every Oscar night? WE DO! WE DO!*',
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
            $role->accent_color = $talentData['accent_color'] ?? null;
            $role->plan_type = 'pro';
            $role->plan_expires = now()->addYear()->format('Y-m-d');
            $role->social_links = self::getRandomDemoSocialLinks();
            $role->description = $talentData['description'];
            if (! empty($talentData['profile_image'])) {
                $role->profile_image_url = $talentData['profile_image'];
            }
            if (! empty($talentData['header_image'])) {
                $role->header_image_url = $talentData['header_image'];
            }
            if (! empty($talentData['font_family'])) {
                $role->font_family = $talentData['font_family'];
            }
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
                'accent_color' => '#F9A825',
                'header_image' => 'demo_header_bar.jpg',
                'profile_image' => 'demo_profile_beer.jpg',
                'font_family' => 'Alfa_Slab_One',
                'description' => '**Where everybody knows your name... and Moe pretends not to.**

Famous for our signature cocktails, prank call resilience, and the occasional alien sighting in the back room. Live entertainment every week—assuming the health inspector doesn\'t visit.

> "I\'m a well-wisher, in that I don\'t wish you any specific harm." - Moe Szyslak

---

## 🥃 What We Offer
- 🍺 **Duff Beer** - On tap, in bottles, in our hearts
- 🔥 **Flaming Moes** - Secret ingredient definitely NOT cough syrup
- 💔 **The Love Tester** - Currently stuck on "Cold Fish"
- 🎵 **Live Entertainment** - Every week (quality varies)

⚠️ *Warning: Do not ask for Amanda Hugginkiss, Ivana Tinkle, or Jacques Strap.*

*"We put the \'dive\' in \'dive bar,\' and we\'re proud of it."*',
            ],
            [
                'name' => "Barney's Bowl-A-Rama",
                'subdomain' => 'demo-bowlarama',
                'background_colors' => '#8B4513, #FF6B35', // Brown/orange
                'accent_color' => '#FF6B35',
                'header_image' => 'demo_header_bowling.jpg',
                'profile_image' => 'demo_profile_bowling.jpg',
                'font_family' => 'Bowlby_One',
                'description' => '**Strikes, spares, and questionable nachos since 1955.**

Springfield\'s premier bowling destination, where every lane tells a story and most of those stories involve Barney falling asleep in the ball return. Home of the legendary Pin Pals and Jacques\' famous bowling lessons.

> "To alcohol! The cause of, and solution to, all of life\'s problems." - Homer (at the bar here)

---

## 🏆 What We Offer
- 🎳 **32 Lanes** - Only 3 currently have gum in the gutters
- 🍕 **Snack Bar** - Nachos of questionable vintage
- 🍺 **Full Bar** - Barney\'s tab runs into six figures
- 🏅 **League Play** - Pin Pals, Holy Rollers, and more!

*Over six million games played. Approximately twelve of them sober.*

⚠️ *Warning: Jacques may offer to "feel your ball."*',
            ],
            [
                'name' => 'The Aztec Theater',
                'subdomain' => 'demo-aztectheater',
                'background_colors' => '#DAA520, #800020', // Gold/maroon art deco
                'accent_color' => '#DAA520',
                'header_image' => 'demo_header_theater.jpg',
                'profile_image' => 'demo_profile_popcorn.jpg',
                'font_family' => 'Josefin_Sans',
                'description' => '**Art deco elegance meets questionable carpet stains since 1927.**

From golden age Hollywood premieres to modern blockbusters about radioactive monsters, we\'ve been entertaining Springfield for generations. Our velvet curtains have witnessed first dates, last dates, and Homer falling asleep in every genre.

> "You call this a bicep? This is a bicep!" - McBain (shown exclusively here)

---

## 🎥 Now Showing
- 🦸 **McBain Films** - ICE TO MEET YOU
- 🦹 **Radioactive Man** - Up and atom!
- 🎭 **Troy McClure Classics** - "The President\'s Neck is Missing!"
- 👻 **Treehouse of Horror** - Marathon screenings every October

🍿 *The popcorn is fresh. The carpet is... experienced. The seats have memories they\'ll never tell.*

*"Ooh! A Gary Larson calendar!"*',
            ],
            [
                'name' => 'Springfield Amphitheater',
                'subdomain' => 'demo-amphitheater',
                'background_colors' => '#1E90FF, #228B22', // Blue/green outdoor
                'accent_color' => '#4FC3F7',
                'header_image' => 'demo_header_concert.jpg',
                'profile_image' => 'demo_profile_amphitheater.jpg',
                'font_family' => 'Concert_One',
                'description' => '**Open-air entertainment under (mostly) clear skies and minimal nuclear fallout.**

The finest outdoor venue in Springfield, host to legendary concerts, civic events, and that time the bees attacked (we don\'t talk about the bees). From Homerpalooza to Be Sharps reunions, if it can be enjoyed outdoors, we\'ve hosted it.

> "I used to rock and roll all night and party every day. Then it was every other day..." - Homer Simpson

---

## 🎸 Legendary Moments
- 🎵 **Homerpalooza** - Homer took a cannonball to the gut right here
- 🎤 **Be Sharps Reunion** - "Baby on Board" rooftop performance
- 🐝 **The Bee Incident** - We\'ve upgraded our security
- 🎭 **Every Major Concert** - Smashing Pumpkins, Cypress Hill, Peter Frampton

🎟️ *Seating for 10,000. Parking for significantly fewer. Tire fire views available from premium sections.*',
            ],
            [
                'name' => 'Lard Lad Donuts',
                'subdomain' => 'demo-lardlad',
                'background_colors' => '#FF69B4, #8B4513', // Pink/brown
                'accent_color' => '#FF69B4',
                'header_image' => 'demo_header_donuts.jpg',
                'profile_image' => 'demo_profile_donut_box.jpg',
                'font_family' => 'Fredoka_One',
                'description' => '**Making Springfield sweeter, one donut at a time.**

Home of the famous giant donut statue and even more famous actual donuts. Now hosting events! Because nothing pairs with entertainment like a Colossal Donut. Our venue is cozy, our coffee is hot, and our mascot definitely won\'t come to life again.

> "Donuts. Is there anything they can\'t do?" - Homer Simpson

---

## 🍩 Our Specialties
- 🎀 **The Big Pink** - Homer\'s favorite (pink frosted, sprinkled)
- 🍫 **Chocolate Thunder** - Thunder thighs included
- 🔥 **The Flaming Homer Donut** - New! Cough syrup-free
- 🌈 **Colossal Donut** - Actual size may vary

⚠️ *Disclaimer: Giant statue has NOT come to life since 1994. Probably won\'t happen again. Probably.*

*"Mmm... forbidden donut."*',
            ],
            [
                'name' => 'Springfield Community Center',
                'subdomain' => 'demo-communitycenter',
                'background_colors' => '#228B22, #FFFFFF', // Civic green/white
                'accent_color' => '#228B22',
                'header_image' => 'demo_header_civic.jpg',
                'profile_image' => 'demo_profile_gym.jpg',
                'font_family' => 'Public_Sans',
                'description' => '**Where Springfield comes together (for better or worse).**

Hosting town halls, talent shows, AA meetings, and everything in between since the town\'s founding. Our multipurpose room has seen it all—from beauty pageants to monorail votes to that incident we\'re legally not allowed to discuss.

> "Can\'t we have one meeting that doesn\'t end with us digging up a corpse?" - Marge Simpson

---

## 📋 Recent Events
- 🚝 **Monorail Vote** - We\'re still paying for that one
- 🎭 **Talent Shows** - Ralph Wiggum available for bookings
- 🗳️ **Town Halls** - Democracy in action (sort of)
- 🎪 **Community Events** - Controlled chaos since 1796

*Your tax dollars at work. Mostly. Sometimes. When the budget allows.*

⚠️ *Warning: Town meetings may result in spontaneous musical numbers and/or mob formation.*',
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
            $role->accept_requests = false;
            $role->plan_type = 'pro';
            $role->plan_expires = now()->addYear()->format('Y-m-d');
            $role->trial_ends_at = now()->addYear();
            $role->social_links = self::getRandomDemoSocialLinks();
            $role->description = $venueData['description'];
            if (! empty($venueData['header_image'])) {
                $role->header_image_url = $venueData['header_image'];
            }
            if (! empty($venueData['profile_image'])) {
                $role->profile_image_url = $venueData['profile_image'];
            }
            if (! empty($venueData['font_family'])) {
                $role->font_family = $venueData['font_family'];
            }
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
            $event->category_id = $eventData['category_id'] ?? null;

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
                'name' => '🎷 Jazz Night with Bleeding Gums Murphy',
                'description' => "## A Night of Smooth Jazz 🎷\n\nA tribute to Springfield's greatest jazz musician. Lisa Simpson and friends perform classic Bleeding Gums Murphy hits in this emotional evening of saxophone excellence. 🎵\n\n> \"The blues isn't about feeling better. It's about making other people feel worse.\" - Bleeding Gums Murphy\n\n### Tonight's Setlist\n- 🎷 *\"Sax on the Beach\"*\n- 🎶 *\"Jazzman\"*\n- 💔 *\"I Never Had an Italian Suit Blues\"*\n- ✨ Plus improvisational pieces that would make Lisa cry (in a good way)\n\n---\n\n⚠️ **Warning:** Bring tissues. This one hits different. 😢\n\n*\"When you hit a wrong note, it's the next note that makes it good or bad.\"*",
                'duration' => 3,
                'group' => 'Live Music',
                'category_id' => 4,
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
                'name' => '🚝 Monorail Karaoke Night',
                'description' => "## MONORAIL! MONORAIL! MONORAIL! 🚝\n\nI've sold monorails to Brockway, Ogdenville, and North Haverbrook, and by gum, it put them on the map! 🗺️\n\n> \"Is there a chance the track could bend?\" \"Not on your life, my Hindu friend!\"\n\n### 🎵 Tonight's Setlist\n- 🚝 *\"Monorail\"* (mandatory opener - crowd participation required)\n- 🌸 *\"We Put the Spring in Springfield\"*\n- 🧥 *\"See My Vest\"* (fur-free version)\n- 🏪 *\"Who Needs the Kwik-E-Mart\"*\n\n---\n\n**Karaoke Rules:** 📋\n1. 🎭 Lyle Lanley impressions get bonus points\n2. 👫 Singing with a partner encouraged\n3. 🙉 If Marge objects, we ignore it\n\n*What about us brain-dead slobs? You'll be given cushy jobs!* 💼",
                'duration' => 3,
                'group' => 'Open Mic',
                'category_id' => 8,
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_openmic.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0100000', // Monday
                'talent' => 'demo-lurleen',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 12, 'quantity' => 100],
                    ['type' => 'Lyle Lanley VIP', 'price' => 30, 'quantity' => 20, 'description' => 'Reserved seating + monorail conductor hat'],
                ],
            ],
            [
                'name' => '⚾ Isotopes Game Watch Party',
                'description' => "## GO ISOTOPES! ⚾\n\nWatch Springfield's beloved baseball team on the big screen! The only team that almost moved to Albuquerque!\n\n> \"Mattingly! I thought I told you to trim those sideburns! GO HOME! You're off the team!\" - Mr. Burns\n\n### Game Day Specials\n- **$3 Hot Dogs** - Made from 100% Grade-F meat (\"Mostly Circus Animals\")\n- **$2 Duffs** - During innings 1-3\n- **Free Nachos** - Every time the Isotopes score (so... rarely)\n\n---\n\n⚠️ *Warning: Team may relocate to Albuquerque at any moment. Dancin' Homer appearances NOT guaranteed but always hoped for.*\n\n*Remember: Capital City is still just a small market team with a minor league attitude.*",
                'duration' => 3,
                'group' => 'Special Events',
                'category_id' => 10,
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
                'name' => "📝 Poetry Slam: Moe's Haiku Hour",
                'description' => "## 📝 Words That Move You... To Tears 😢\n\nCompetitive spoken word poetry featuring Springfield's most melancholic verses. Hosted by the Sultan of Sadness himself, Moe Szyslak.\n\n> \"I'm better than dirt. Well, most kinds of dirt. Not that fancy store-bought dirt. That stuff's loaded with nutrients.\" - Moe Szyslak\n\n### 🎭 Sample Verse (by Moe)\n*\"My life is empty*\n*No one calls, the bar is dead*\n*Pass the rat poison\"*\n\n---\n\n### 🏆 Categories\n- 💔 **Most Depressing** - The Moe Special\n- 😭 **Most Tears Generated** - Audience vote\n- 🎭 **Best Performance** - Drama counts\n\n*Tissues provided. Bring your sad poems and your sadder life experiences.* 📜",
                'duration' => 2.5,
                'group' => 'Open Mic',
                'category_id' => 1,
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_openmic.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0010000', // Tuesday
                'talent' => 'demo-lurleen',
                'tickets' => [
                    ['type' => 'Audience', 'price' => 10, 'quantity' => 100],
                    ['type' => 'Poet Entry', 'price' => 5, 'quantity' => 20],
                ],
            ],
            [
                'name' => '🧠 Trivia: Springfield History',
                'description' => "## 🧠 Test Your Springfield IQ!\n\nTrivia night for true Springfieldians! Hosted by Professor Frink, with the questions and the answers and the GLAVIN!\n\n> \"A little knowledge is a dangerous thing. So is a lot.\" - Professor Frink\n\n### Sample Questions\n- Who *really* founded Springfield? (Jebediah or Hans?)\n- What's the tire fire's birthday? (Hint: It's older than you think)\n- How many times has Sideshow Bob tried to kill Bart?\n- What sector does Homer work in? (And what does he do there?)\n\n---\n\n### Prizes 🏆\n- **1st Place** - Flaming Moe pitcher + bragging rights\n- **2nd Place** - Duff 6-pack\n- **3rd Place** - A sense of adequacy\n\n*GLAVIN! May the smartest Springfieldian win!*",
                'duration' => 2,
                'group' => 'Special Events',
                'category_id' => 3,
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
                'name' => '🎙️ Open Mic Night - Springfield Edition',
                'description' => "## 🎙️ Share Your Talent with Springfield!\n\nOur weekly open mic welcomes musicians, comedians, poets, and performers of all kinds. Yes, even you. Yes, even *that* act. 🌟\n\n> \"I'm a star! A star, I tell you!\" - Krusty (before he bombed)\n\n### 🎭 Past Featured Acts\n- 📝 Homer's interpretive poetry (surprisingly emotional)\n- 🎩 Milhouse's magic tricks (everything vanishes, including the audience)\n- 🌙 Ralph's show and tell (\"I found a moon rock in my nose!\")\n- 🎸 Otto's air guitar (technically perfect)\n\n---\n\n**Sign-up starts at 6:30 PM** ⏰\n\n*Everybody's welcome! Even Shelbyville residents (we're watching you though).* 👀",
                'duration' => 3,
                'group' => 'Open Mic',
                'category_id' => 4,
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_openmic.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0000100', // Thursday
                'talents' => ['demo-lurleen', 'demo-lisajazz'],
                'tickets' => [
                    ['type' => 'Free Entry', 'price' => 0, 'quantity' => 100],
                ],
            ],
            [
                'name' => '🎤 Karaoke Night',
                'description' => "## 🎤 Grab the Mic!\n\nBelt out your favorites! \"Baby on Board\" performances strongly encouraged. Be Sharps reunions welcome!\n\n> \"Baby on board, how I've adored, that sign on my car's window pane...\" - The Be Sharps\n\n### Fan Favorites\n- *\"See My Vest\"* - Mr. Burns' fashion anthem\n- *\"We Put The Spring in Springfield\"* - Town pride!\n- *\"Happy Birthday, Lisa\"* - Michael Jackson (\"John Jay Smith\") approved\n- *\"It Was a Very Good Beer\"* - Homer's ballad 🍺\n\n---\n\n### Prizes Tonight\n- Best Performance: Free pitcher of Duff\n- Best Costume: Moe's respect (priceless)\n- Most Emotional: Tissues and a hug\n\n*Warning: \"Bawitdaba\" is banned after last week's incident.*",
                'duration' => 3,
                'group' => 'Open Mic',
                'category_id' => 8,
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_openmic.jpg',
                'hour' => 21,
                'minute' => 0,
                'days_of_week' => '0000100', // Thursday
                'talent' => 'demo-rockers',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 8, 'quantity' => 75],
                ],
            ],
            [
                'name' => '🤡 Stand-Up with Krusty',
                'description' => "## HEY HEY! Comedy Night! 🤡\n\nKrusty the Clown brings his legendary stand-up act to Moe's Tavern! Forty years of comedy... and counting! 🎭\n\n> \"I heartily endorse this event or product!\" - Krusty\n\n### 🎤 Tonight's Lineup\n- 🤡 **Krusty the Clown** - Headliner (if he shows up sober)\n- 🐒 **Mr. Teeny** - Opening Act (may smoke on stage)\n- 🦴 **Sideshow Mel** - Bone-in-hair comedy stylings\n\n---\n\n### ⚠️ Important Notes\n- 💊 Krusty's medication schedule has been adjusted\n- 🚫 No asking about Sideshow Bob\n- 📸 Photos allowed (Krusty needs the publicity)\n\n*This show is Krusty-approved! (Terms and conditions apply)* ✅",
                'duration' => 2.5,
                'group' => 'Comedy',
                'category_id' => 8,
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
                'name' => '🔥 Flaming Moe Night',
                'description' => "## 🔥 The Drink That Put Moe's on the Map!\n\nThe legendary Flaming Moe returns! Now with a secret ingredient that is *definitely* not children's cough syrup. 🍹\n\n> \"I invented this drink! Well, actually Homer told me... but I made it famous!\" - Moe\n\n### 🍸 Tonight's Specials\n- 🔥 **$5 Flaming Moes** - All night long\n- 🧯 **Fire extinguishers** - Provided at each table\n- 🎸 **Aerosmith** - NOT scheduled to appear (sorry)\n- 🎵 **Live Music** - To drown out the fire alarms\n\n---\n\n### 👔 Dress Code\nCasual (fire-resistant clothing *strongly* recommended) 👕\n\n⚠️ *Warning: Side effects may include: euphoria, dancing, and temporary belief that you can sing.* 🎤\n\n*\"Happiness is just a Flaming Moe away!\"* 🥳",
                'duration' => 5,
                'group' => 'Special Events',
                'category_id' => 8,
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
                'name' => '🎧 DJ Sideshow Bob',
                'description' => "## 🎧 The Cultured Criminal Spins!\n\nGet ready to dance! DJ Sideshow Bob brings his unique blend of Gilbert & Sullivan meets electronica. State-of-the-art sound system included. 🔊\n\n> \"Oh, I'll stay away from your son, all right. Stay away... FOREVER!\" - Sideshow Bob (about something else)\n\n### 🎵 Tonight's Vibe\n- ⚓ **Gilbert & Sullivan Remixes** - H.M.S. Pinafore goes HARD\n- 💀 **Dark Electronica** - Revenge beats\n- 🎹 **Classical Drops** - Beethoven would be proud (probably)\n\n---\n\n### ⚠️ IMPORTANT SAFETY NOTICE\n- 🚫 All rakes have been removed from the premises\n- 👦 Bart Simpson banned from attendance\n- 🪪 21+ event with valid Springfield ID\n\n*\"No one who speaks German could be an evil man!\" ...just saying.* 🇩🇪",
                'duration' => 4,
                'group' => 'DJ Nights',
                'category_id' => 8,
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
                'name' => '🔥 Comedy Roast: Principal Skinner',
                'description' => "## SKINNER! 🔥\n\nTonight we roast Springfield Elementary's finest principal! Hosted by Superintendent Chalmers, who has been waiting for this moment.\n\n> \"SKINNER! Why is there smoke coming out of your oven?\" \"That's not smoke, it's steam!\" - Classic Chalmers-Skinner\n\n### Tonight's Roasters\n- **Superintendent Chalmers** - Finally gets to say what he's been thinking\n- **Groundskeeper Willie** - \"Grease me up, woman!\"\n- **Mrs. Krabappel** - Via video tribute (RIP Marcia)\n- **Bart Simpson** - \"Eat my shorts, Skinner!\"\n- **Agnes Skinner** - The ultimate roast material\n\n---\n\n### Food & Drink\n⚠️ Steamed hams will NOT be served (it's an Albany expression)\n- Regular hamburgers available\n- Aurora Borealis viewing NOT included\n\n*\"May I see it?\" \"...No.\"*",
                'duration' => 2.5,
                'group' => 'Comedy',
                'category_id' => 8,
                'curator_group' => 'Entertainment',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talents' => ['demo-krusty', 'demo-lurleen'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 100],
                    ['type' => 'Aurora Borealis Package', 'price' => 40, 'quantity' => 20, 'description' => 'Front row + steamed ham (actually grilled)'],
                ],
            ],
            [
                'name' => '🕺 80s Night: Do The Bartman',
                'description' => "## Flashback to the Greatest Decade! 🕺\n\nDress in your best 80s/90s attire and dance to all the classics! Neon, leg warmers, and Marge wigs welcome!\n\n> \"Eat my shorts!\" - Bart Simpson (1989-forever)\n\n### Tonight's Events\n- **\"Do The Bartman\" Dance-Off** - 11 PM sharp\n- **Costume Contest** - Marge's hair encouraged (extra points for height)\n- **Deep Cuts** - From the Springfield Files and beyond\n- **Music Videos** - All the classics on the big screen\n\n---\n\n### Costume Categories\n- Best Marge Hair (must be at least 2 feet tall)\n- Best Bart Impression 🛹\n- Best Homer Belly\n- Best Lisa Nerd\n\n*Ay caramba! Don't have a cow, man!*",
                'duration' => 4,
                'group' => 'DJ Nights',
                'category_id' => 8,
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 21,
                'minute' => 0,
                'days_of_week' => '0000010', // Friday
                'talents' => ['demo-djbob', 'demo-rockers'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 150],
                ],
            ],
            [
                'name' => '🥤 Happy Hour: $1 Squishees',
                'description' => "## Who Needs the Kwik-E-Mart? WE DO!\n\nOur partnership with Apu brings the Squishee experience to Moe's!\n\n> \"Thank you, come again!\" - Apu Nahasapeemapetilon\n\n### Happy Hour Specials (4-6 PM)\n- **$1 Squishees** - All 47 flavors available 🥤\n- **Brain Freeze Competition** - 5 PM sharp\n- **Apu's Secret Recipe Nachos** - With cheese from... somewhere\n- **$2 Duffs** - To wash it all down\n\n---\n\n### Brain Freeze Rules\n1. Drink as fast as you can\n2. First to NOT get brain freeze wins\n3. Winner gets a free Squishee (more brain freeze potential)\n\n⚠️ *Note: Squishee machine may occasionally achieve sentience. Just ignore it.*\n\n*The syrup-to-ice ratio has been scientifically optimized by Professor Frink!*",
                'duration' => 2,
                'group' => 'Special Events',
                'category_id' => 6,
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 16,
                'minute' => 0,
                'days_of_week' => '0000100', // Thursday
                'talent' => 'demo-frink',
                'tickets' => [
                    ['type' => 'Free Entry', 'price' => 0, 'quantity' => 200],
                ],
            ],
            [
                'name' => '⚖️ Legal Sea Foods with Lionel Hutz',
                'description' => "## Works on Contingency? No, Money Down!\n\nLegal advice from Springfield's finest law-talking guy! Lionel Hutz, Attorney at Law (business card printer at large).\n\n> \"Mr. Simpson, this is the most blatant case of fraudulent advertising since my suit against the film 'The Neverending Story.'\" - Lionel Hutz\n\n### Tonight's Seminar\n- **How to Read Contracts** - (Hint: don't, just sign)\n- **\"That's Why You're the Judge and I'm the Law-Talking Guy\"**\n- **Business Card Printing Workshop** - We use the finest crayon\n- **Smoking in Court: Pros and Cons**\n- **Free Breadsticks** - Technically a loophole\n\n---\n\n### Lionel's Credentials ⚖️\n- Graduate of \"Harvard, Yale, MIT, Oxford, the Sorbonne, the Louvre\"\n- Never convicted of anything they could prove\n- Office located \"above that S&M place on Fourth Street\"\n\n*\"Care to join me in a belt of scotch?\" \"It's 9:30 in the morning.\" \"Yeah, but I haven't slept in days.\"*",
                'duration' => 2,
                'group' => 'Comedy',
                'category_id' => 8,
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
                'name' => '🦷 Dental Plan Night',
                'description' => "## Lisa Needs Braces! DENTAL PLAN! 🦷\n\nUnion Meeting & Celebration! Come for the dental plan, stay for the braces!\n\n> \"Lisa needs braces!\" \"DENTAL PLAN!\" \"Lisa needs braces!\" \"DENTAL PLAN!\" - Homer's brain, on loop\n\n### Tonight's Agenda\n- **Free Dental Checkups** - Real dentist (not Dr. Nick)\n- **Carl Carlson's PowerPoint** - \"Why Unions Matter\"\n- **Lenny's Eye Patch Station** - My eye! The goggles do nothing!\n- **\"Where's My Burrito?\" Snack Bar** - With mystery meat\n\n---\n\n### Sponsored by Local 643\n- Nuclear Workers Unite!\n- Free Duff for union members\n- Sign up for the dental plan (obviously)\n\n*\"First thing tomorrow morning, I'm gonna punch Lenny in the back of the head!\"*",
                'duration' => 3,
                'group' => 'Special Events',
                'category_id' => 3,
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
                'name' => '🚙 Canyonero Night',
                'description' => "## CAN YOU NAME THE TRUCK WITH FOUR-WHEEL DRIVE? 🚙\n\n**SMELLS LIKE A STEAK AND SEATS THIRTY-FIVE!**\n\n> \"Canyonero! Canyonero! She blinds everybody with her super high beams!\" - The Jingle\n\n### CANYONERO!\n\n**Tonight's Events:**\n- **SUV Parade** - In the parking lot (bring earplugs)\n- **Disclaimer Signing** - \"Unexplained fires are a matter for the courts\"\n- **12 Yards Long, 2 Lanes Wide Display** - Marvel at the size!\n- **Squirrel-Crushing Demonstration** - (No actual squirrels harmed... probably)\n\n---\n\n### The Specs\n- Top of the line in utility sports!\n- Smells like a steak and seats thirty-five!\n- Blinds everybody with her super high beams!\n- Unexplained fires are a matter for the courts! 🔥\n\n*Whoa, Canyonero! CANYONERO!*",
                'duration' => 3,
                'group' => 'Special Events',
                'category_id' => 8,
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0010000', // Tuesday
                'talent' => 'demo-rockers',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 10, 'quantity' => 100],
                    ['type' => 'F Series Package', 'price' => 40, 'quantity' => 20, 'description' => 'Test drive + bumper sticker'],
                ],
            ],
            [
                'name' => 'Sneed\'s Night (Formerly Chuck\'s)',
                'description' => "## Feed and Seed! (Formerly Chuck's)\n\nA night celebrating Springfield's agricultural heritage... and wordplay.\n\n> \"Feed and Seed\" - It says what it does!\n\n### What We Offer\n- **Quality Feed** - For all your livestock needs\n- **Premium Seed** - Plant the future!\n- **Agricultural Supplies** - The finest in Springfield\n- **Subtle Humor** - Ask about the previous owner's naming convention\n\n---\n\n### Tonight's Events\n- **Best Farm Pun Contest** - Winner gets free feed\n- **Duff on Tap** - $3 all night\n- **Country Music** - Live from the Springfield Rockers 🎵\n\n*Families welcome. Those who appreciate subtle humor extra welcome.*\n\n⚠️ *This event is exactly what it says it is. Nothing more.*",
                'duration' => 3,
                'group' => 'Special Events',
                'category_id' => 8,
                'curator_group' => 'Bars & Nightlife',
                'venue' => 'demo-moestavern',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0001000', // Wednesday
                'talent' => 'demo-stonecutters',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 12, 'quantity' => 75],
                ],
            ],
            [
                'name' => "Dr. Nick's Medical Seminar",
                'description' => "## Hi, Everybody! HI, DR. NICK!\n\nMedical advice from Springfield's most affordable doctor!\n\n> \"The coroner? I'm so sick of that guy!\" - Dr. Nick Riviera\n\n### Tonight's Topics\n- **The Knee Bone's Connected to the... Something** - Anatomy basics\n- **\"Call 1-600-DOCTORB\"** - The B is for bargain!\n- **Inflammable Means Flammable?!** - Who knew!\n- **Organ Identification** - Which one goes where?\n- **How to Perform Surgery** - On yourself, if necessary\n\n---\n\n### Dr. Nick's Credentials 👨‍⚕️\n- Hollywood Upstairs Medical College (Class of Whenever)\n- \"You've tried the best, now try the rest!\"\n- Zero malpractice suits (that stuck)\n\n⚠️ *Disclaimer: This is for entertainment purposes only. Please consult a real doctor. Like Dr. Hibbert.*\n\n*\"Well, if it isn't my old friend Mr. McGreg! With a leg for an arm and an arm for a leg!\"*",
                'duration' => 2,
                'group' => 'Comedy',
                'category_id' => 8,
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
                'name' => '🎳 Pin Pals Bowling Tournament',
                'description' => "## PIN PALS! PIN PALS! 🎳\n\nWe're the Pin Pals! Springfield's biggest bowling tournament returns! 🏆\n\n> \"It's like David and Goliath, only this time David won!\" - Homer (after bowling a 300)\n\n### 👥 Teams Welcome\n- 🍩 **Original Pin Pals** - Homer, Apu, Moe, Otto\n- ⛪ **The Holy Rollers** - Ned Flanders' church team\n- 🌍 **The Stereotypes** - Representing diversity in bowling\n- 📺 **Channel 6 Wastelanders** - Kent Brockman's crew\n- 💰 **The Fat Cats** - Mr. Burns' replacement team\n\n---\n\n### 🎁 Prizes\n- 🥇 **First Place:** Mr. Burns' actual bowling team trophy\n- 🥈 **Second Place:** Year supply of bowling shoe spray\n- 🥉 **Third Place:** Free nachos for a month\n- 🎀 **Last Place:** \"You Tried\" participation ribbon\n\n*Homer's bowling hand is ready. Is yours?* 🤚",
                'duration' => 4,
                'group' => 'Special Events',
                'category_id' => 10,
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
                'name' => '🍺 "Beer Baron" Prohibition League Night',
                'description' => "## To Alcohol! The Cause Of, And Solution To, All of Life's Problems! 🍺\n\nProhibition-era themed bowling league night!\n\n> \"Listen, rummy, I'm gonna say it plain and simple: Where'd you pinch the hooch?\" - Rex Banner\n\n### Tonight's Activities\n- **Prohibition-Era Bowling League** - Dress the part!\n- **Secret Speakeasy** - In the back (password: \"Duff\")\n- **Rex Banner** - NOT invited (please don't tell him)\n- **\"I Am the Beer Baron!\" Shouting Contest** - 10 PM\n\n---\n\n### What's Included\n- Bowling shoes (vintage style)\n- \"Bathtub gin\" (actually just regular gin)\n- Complimentary fedora rental 🎩\n- Lookout posted for Rex Banner\n\n*\"I'm the Beer Baron!\" - Homer (the actual Beer Baron)*\n\n⚠️ *Note: If you see a man in a gray suit asking about hooch, you didn't see anything.*",
                'duration' => 3,
                'group' => 'Special Events',
                'category_id' => 8,
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
                'name' => '🐕 "Two Dozen and One Greyhounds" Dog Show',
                'description' => "## See My Vest! See My Vest! 🐕\n\nA celebration of dogs! (Not the kind Mr. Burns had in mind.)\n\n> \"See my vest! See my vest! Made from real gorilla chest!\" - Mr. Burns (NOT invited)\n\n### Event Schedule\n- **Greyhound Racing** - No gambling... officially\n- **Santa's Little Helper Agility Course** - Can he do it? (Probably not)\n- **\"Good Dog\" vs \"Bad Dog\" Competition** - Who's a good boy?\n- **Best-Dressed Pet Contest** - Costumes encouraged!\n\n---\n\n### Rules & Regulations\n- All dogs welcome (except robotic ones)\n- Treats allowed\n- Mr. Burns NOT allowed as a judge\n- No discussion of greyhound fur applications\n\n⚠️ *Warning: Do not leave puppies unattended near wealthy industrialists.*\n\n*\"Smithers, release the hounds!\" - NOT happening here.*",
                'duration' => 3,
                'group' => 'Special Events',
                'category_id' => 3,
                'curator_group' => 'Community',
                'venue' => 'demo-bowlarama',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 14,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-lurleen',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 12, 'quantity' => 100],
                    ['type' => 'Dog Entry', 'price' => 20, 'quantity' => 30, 'description' => 'Includes dog treats + bandana'],
                ],
            ],
            [
                'name' => "Homer's Perfect Game Challenge",
                'description' => "## WOOHOO! Can You Beat Homer's 300? 🎳\n\nThe legendary perfect game challenge! Homer did it, can you?\n\n> \"Woohoo! Who's the greatest bowler in the world?\" - Homer Simpson\n\n### Challenge Rules\n- **Bowl your best game** - No pressure\n- **Beat 300** - Good luck (it's literally impossible to beat)\n- **Win a lifetime supply of donuts** - Valued at $47/year for Homer\n\n---\n\n### Secrets to Success\n- Mystery spots on the alley may or may not help\n- Optimal Duff consumption level: unknown\n- Visualization techniques: think of donuts rolling toward pins 🍩\n- Hope for \"the Homer Simpson luck\"\n\n*\"I thought I told you to trim those sideburns!\" - Mr. Burns (unrelated)*\n\n⚠️ *Note: Challenging Homer's record may result in feelings of inadequacy.*",
                'duration' => 4,
                'group' => 'Special Events',
                'category_id' => 10,
                'curator_group' => 'Entertainment',
                'venue' => 'demo-bowlarama',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '0001000', // Wednesday
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
                'name' => '🎬 Troy McClure Film Festival',
                'description' => "## Hi, I'm Troy McClure! 🎬\n\nYou may remember me from such film festivals as \"The Decaffeinated Man vs. The Case of the Missing Coffee\" and \"P is for Psycho.\" ☕\n\n> \"Get confident, stupid!\" - Troy McClure, motivational speaker\n\n### 🎥 Tonight's Features\n- 🔬 *\"The Contrabulous Fabtraption of Professor Horatio Hufnagel\"*\n- 📞 *\"Dial M for Murderousness\"*\n- 🗽 *\"The President's Neck is Missing\"*\n- 💪 *\"Hydro, the Man With the Hydraulic Arms\"*\n- 🏈 *\"Leper in the Backfield\"*\n\n---\n\n### 🎭 Theater Extras\n- 📸 **Troy McClure Photo Op** - $15 (autograph extra)\n- 📽️ **Vintage Trailers** - From Troy's extensive filmography\n- 🍿 **Concessions** - Popcorn, Buzz Cola, and Krusty-O's\n\n*\"Hi, I'm Troy McClure! You might remember me from tonight!\"* ⭐",
                'duration' => 4,
                'group' => 'Special Events',
                'category_id' => 1,
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
                'name' => '💥 McBain Double Feature',
                'description' => "## ICE TO MEET YOU!\n\nRainier Wolfcastle IS McBain in this explosive double feature!\n\n> \"Right now I'm thinking about holding another meeting... IN BED!\" - McBain\n\n### Tonight's Films\n- *\"McBain: Let's Get Silly\"* (1991) - The one with the ice cream van\n- *\"McBain IV: Fatal Discharge\"* - MENDOZA!\n\n---\n\n### What to Expect\n- **Commie-nazis** getting what they deserve\n- **One-liners** that don't make sense in context\n- **Senator Mendoza's** ultimate comeuppance\n- **Explosions** - Approximately 47 per film 💥\n- **Sunglasses** - McBain never takes them off\n\n### Included\n- Free popcorn (it's showtime)\n- One-liner cards for audience participation\n- Mendoza effigy (for throwing things at)\n\n*\"The film is just me in front of a brick wall for an hour and a half. It cost $80 million.\"* - Rainier Wolfcastle",
                'duration' => 4,
                'group' => 'Special Events',
                'category_id' => 1,
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
                'name' => '🎥 Hans Moleman Film Festival',
                'description' => "## \"Man Getting Hit by Football\" & More!\n\nAcademy Award-nominated cinema from Springfield's most enduring victim.\n\n> \"I was saying Boo-urns!\" - Hans Moleman\n\n### Tonight's Films\n- *\"Man Getting Hit by Football\"* - Academy Award nominee (lost to \"A Burns for All Seasons\") 🏈\n- *\"Hans Moleman Productions Presents: Hans Moleman Productions\"*\n- *\"The Trials of Hans Moleman\"* - A documentary of suffering\n- *\"Hans Moleman in 'Driving to the Store'\"* - Short film\n\n---\n\n### Fun Facts\n- Hans Moleman is **31 years old** (hard to believe, we know)\n- He's \"died\" 26 times on screen\n- Still waiting for that Oscar\n- \"Nobody's gay for Moleman\"\n\n*\"Drinking has ruined my life. I'm 31 years old!\"*\n\n⚠️ *Warning: Film may cause feelings of profound sadness and/or groin sympathy pain.*",
                'duration' => 2,
                'group' => 'Comedy',
                'category_id' => 1,
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
                'name' => '👻 Treehouse of Horror Marathon',
                'description' => "## The Following Program Contains Scenes of Extreme Violence... 👻\n\nViewer discretion is advised. Seriously. 💀\n\n> \"Quiet, you! Do you want to get sued?!\" - Homer (re: \"The Shining\")\n\n### 📺 Tonight's Episodes\n- ❄️ *\"The Shinning\"* - \"No TV and no beer make Homer go crazy?\" \"Don't mind if I do!\"\n- ⏰ *\"Time and Punishment\"* - DON'T TOUCH ANYTHING! (Homer touches everything)\n- 🍖 *\"Nightmare Cafeteria\"* - Grade F meat has never been scarier\n- 🤡 *\"Clown Without Pity\"* - \"The doll's trying to kill me and the toaster's been laughing at me!\"\n\n---\n\n### 🎃 What to Expect\n- 😱 **Jump scares** - Approximately 12 per hour\n- 🩸 **Gratuitous violence** - It's Halloween, baby!\n- 🐸 **Hypno-Toad** - Wait, wrong show\n- 🍬 **Candy** - Distributed between segments\n\n⚠️ *Warning: May cause nightmares, fear of dolls, and an irrational distrust of toasters.* 🍞",
                'duration' => 5,
                'group' => 'Special Events',
                'category_id' => 8,
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
                'name' => '🎭 Gabbo Night',
                'description' => "## GABBO! GABBO! GABBO! 🎭\n\nHe's here! Springfield's favorite ventriloquist dummy returns!\n\n> \"All the kids in Springfield are S.O.B.s!\" - Gabbo (on a hot mic)\n\n### Tonight's Show\n- **Gabbo and Arthur Crandall** perform their greatest hits\n- **\"Look, Smithers! Garbo is coming!\"** - Mr. Burns, confused as always\n- **Special Appearance** - That 2nd-rate ventriloquist, Krusty\n- **Musical Numbers** - \"Gabbo! Gabbo! GABBO!\"\n\n---\n\n### Famous Gabbo Quotes\n- \"I'm a bad wittle boy!\"\n- \"GABBO! GABBO! GABBOOOO!\"\n- \"All the kids in Springfield are S.O.B.s!\" (the incident)\n\n⚠️ *Warning: May contain phrases that sound like profanity. Ventriloquist will deny everything.*\n\n*\"Gabbo is great! Gabbo has all the answers!\"*",
                'duration' => 2,
                'group' => 'Comedy',
                'category_id' => 8,
                'curator_group' => 'Entertainment',
                'venue' => 'demo-aztectheater',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0100000', // Monday
                'talent' => 'demo-krusty',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 20, 'quantity' => 100],
                    ['type' => 'Gabbo VIP Package', 'price' => 45, 'quantity' => 25, 'description' => 'Meet Gabbo + photo op (ventriloquist not included)'],
                ],
            ],
            [
                'name' => '"A Burns for All Seasons" Documentary',
                'description' => "## 😈 Excellent...\n\nA cinematic journey through the life of Springfield's most \"beloved\" billionaire.\n\n> \"I'd trade it all for a little more.\" - Charles Montgomery Burns\n\n### 🎬 Documentary Highlights\n- 👶 **Young Burns: The Early Years** - \"I was a baby, Smithers... a BABY!\"\n- ☀️ **The Sun-Blocking Machine Incident** - Still his proudest moment\n- 🐕 **\"Release the Hounds\" Supercut** - 47 minutes of hound-releasing\n- 💕 **Smithers Loyalty Montage** - Set to romantic music\n- 💰 **The Trillion Dollar Bill** - That one time he had a trillion dollars\n\n---\n\n### 🎁 First 50 Attendees Receive\n- ✈️ Free Spruce Moose model (\"Hop in!\")\n- 📜 \"Excellent\" certificate signed by Burns\n- 🧸 Tiny Bobo replica (NOT the real one)\n\n*\"Since the beginning of time, man has yearned to destroy the sun...\"*",
                'duration' => 3,
                'group' => 'Special Events',
                'category_id' => 1,
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
                'name' => '☢️ Radioactive Man Premier Night',
                'description' => "## ☢️ UP AND ATOM!\n\nThe premiere of Springfield's greatest superhero film!\n\n> \"My eyes! The goggles do nothing!\" - Rainier Wolfcastle\n\n### 🦸 Featuring\n- ☢️ **Radioactive Man** - Rainier Wolfcastle in his finest role\n- 👦 **Fallout Boy Tryouts** - On stage! Say \"Jiminy jilikers\" correctly to win!\n- 🥽 **Goggles Giveaway** - \"The goggles do nothing!\" edition\n- 🎬 **Meet the Cast** - Those who survived production\n\n---\n\n### 🎬 Production Notes\n- 📍 Filmed on location in Springfield (until the town kicked them out)\n- 💰 Budget: $30 million (mostly acid pits)\n- 🤕 Injuries on set: 47 (all stunt-related... mostly)\n- 🎭 Milhouse's audition: \"Up and at them!\" - REJECTED\n\n*\"Jiminy jilikers!\" - Fallout Boy (correct pronunciation)*\n\n⚠️ *Warning: Real radioactive material was NOT used in the filming of this movie. Probably.*",
                'duration' => 4,
                'group' => 'Special Events',
                'category_id' => 8,
                'curator_group' => 'Entertainment',
                'venue' => 'demo-aztectheater',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talent' => 'demo-troymcclure',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 18, 'quantity' => 120],
                    ['type' => 'Fallout Boy Package', 'price' => 40, 'quantity' => 30, 'description' => 'Includes goggles + radioactive glow stick'],
                ],
            ],
            [
                'name' => '🔍 "Who Shot Mr. Burns?" Mystery Screening',
                'description' => "## 🔍 WHO SHOT MR. BURNS?!\n\nSpringfield's greatest mystery! An interactive screening event!\n\n> \"I'm Mr. Burns, blah blah blah, do this, do that, blah blah blah.\" - Homer's impression\n\n### 🎬 Interactive Mystery Night\n- 📺 **Watch Both Parts** - Back to back, as intended\n- 🤔 **Submit Your Guess** - During intermission (winner gets bragging rights)\n- 🔎 **Clue-Finding Contest** - Pause and analyze!\n- 🎉 **Reveal Party** - Even though we all know it was...\n\n---\n\n### 🕵️ Suspects Ranked (No Spoilers... Maybe)\n- 👶 The baby? Nah, too obvious\n- 🤡 Krusty? He had motive!\n- 🏫 Skinner? SKINNER!\n- 🍺 Moe? Always suspicious\n- 😤 Everyone Homer insulted? That's... everyone\n\n*Spoiler alert: It's always who you least suspect.*\n\n⚠️ *If you already know who did it, please don't ruin it for the one person who doesn't.*",
                'duration' => 3,
                'group' => 'Special Events',
                'category_id' => 8,
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
                'name' => '🍺 Duffapalooza',
                'description' => "## The Greatest Beer Festival This Side of Shelbyville! 🍺\n\nLive music, unlimited Duff samples, and good times guaranteed! 🎉\n\n> \"Ah, beer. The cause of, and solution to, all of life's problems.\" - Homer J. Simpson\n\n### 🎵 What to Expect\n- 🎤 **Live Performances** - All night long\n- 🍺 **Duff Varieties on Tap:**\n  - 🟡 Duff Classic\n  - ⚪ Duff Lite (same thing, different can)\n  - 🟤 Duff Dry (also the same thing)\n- 🍖 **Food Available** - Pork chops and donuts (Homer-approved)\n- 🎧 **Beer Garden DJ** - Spinning Duff-fueled hits\n\n---\n\n### 💪 Duffman Says\n- \"Oh yeah!\" 😎\n- \"Duff Beer for me, Duff Beer for you!\"\n- \"Can't get enough of that wonderful Duff!\"\n\n*D'oh-n't miss it! Woohoo!* 🎊",
                'duration' => 4,
                'group' => 'Live Music',
                'category_id' => 8,
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
                'name' => '🎸 Battle of the Bands',
                'description' => "## 🎸 Springfield's Biggest Musical Showdown!\n\nBands compete for glory, bragging rights, and a year's supply of Duff!\n\n> \"That's it! You people have stood in my way long enough. I'm going to clown college!\" - Homer (different career choice)\n\n### 🎤 Tonight's Lineup\n- 🚌 **School of Rock** - Starring Otto (man, he rocks hard)\n- 🔊 **Spinal Tap Tribute Band** - These go to eleven\n- 😢 **Sadgasm** - Featuring Homer's grunge phase\n- 💫 **The Party Posse** - Bart, Milhouse, Nelson, Ralph (subliminal messages removed)\n\n---\n\n### 🏆 How It Works\n- 👏 **Voting by applause** - The louder, the better\n- 🎸 **Each band plays 3 songs** - Choose wisely!\n- 🥇 **Winner gets:** Year supply of Duff + studio time\n- 🎤 **Judges:** Lisa (musical expertise), Krusty (celebrity), Disco Stu (groove factor)\n\n*May the best band win! Rock and/or roll!*",
                'duration' => 4,
                'group' => 'Live Music',
                'category_id' => 4,
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-amphitheater',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talents' => ['demo-rockers', 'demo-lisajazz', 'demo-djbob'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 25, 'quantity' => 150],
                    ['type' => 'Backstage Pass', 'price' => 65, 'quantity' => 30, 'description' => 'Meet the bands + exclusive merch'],
                ],
            ],
            [
                'name' => '🎸 Homerpalooza Festival',
                'description' => "## 🎸 AM I COOL, MAN? YES! 😎\n\nThe greatest rock festival Springfield has ever seen returns! 🤘\n\n> \"I used to rock and roll all night and party every day. Then it was every other day. Now I'm lucky if I can find half an hour a week in which to get funky.\" - Homer\n\n### 🎤 Festival Lineup\n- 🎃 **Smashing Pumpkins** - \"Homer Simpson wrecks my pig, Cypress Hill steals my orchestra...\"\n- 🌿 **Cypress Hill** - \"You gotta fight for your right to party!\"\n- 🎸 **Sonic Youth** - \"That guy's cool!\"\n- 🎹 **Peter Frampton** - \"I do believe I'm getting younger!\"\n\n---\n\n### 🎪 Festival Extras\n- 🎯 **Cannonball to the Gut Show** - Safety NOT guaranteed 💥\n- 📚 **\"Getting High\" Workshop** - It's an alternative definition\n- 🌲 **Hippie Tent** - Peace, love, and Duff ☮️\n- 👴 **\"Am I Cool?\" Booth** - Find out if you're hip!\n\n*\"What the hell is this? A freak show?!\" \"That's an unfair stereotype. It's actually a music festival.\"* 🎵",
                'duration' => 6,
                'group' => 'Live Music',
                'category_id' => 4,
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-amphitheater',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 14,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talents' => ['demo-rockers', 'demo-djbob'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 45, 'quantity' => 500],
                    ['type' => 'VIP Front Row', 'price' => 120, 'quantity' => 50, 'description' => 'Backstage access + meet the bands'],
                ],
            ],
            [
                'name' => '🎤 Be Sharps Reunion Concert',
                'description' => "## 🎤 BABY ON BOARD! 👶\n\nThe legendary Be Sharps reunite for one night only! ⭐\n\n> \"It's been done.\" - George Harrison (watching from a rooftop)\n\n### 🎵 Setlist Includes\n- 👶 *\"Baby on Board\"* - The #1 hit that swept the nation 🏆\n- 🌊 *\"Goodbye My Coney Island Baby\"* - Barbershop classic\n- 🎤 *\"Number 8... Number 8...\"* - A deep cut\n- 🏢 **Surprise Rooftop Finale** - Just like you-know-who 🎸\n\n---\n\n### 🎭 The Be Sharps\n- 🍩 **Homer Simpson** - Lead baritone, donut enthusiast\n- 🍺 **Barney Gumble** - The Yoko of the group\n- 🏫 **Skinner** - Principal by day, tenor by night\n- 🏪 **Apu** - \"Who needs the Kwik-E-Mart? Not meeeee!\"\n\n*\"Barney, you're just like Yoko!\" - Moe* 😤\n\n⭐ *Special appearance by George Harrison (hologram pending approval from his estate)* 🌟",
                'duration' => 3,
                'group' => 'Live Music',
                'category_id' => 4,
                'curator_group' => 'Arts & Culture',
                'venue' => 'demo-amphitheater',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0000001', // Saturday
                'talents' => ['demo-rockers', 'demo-lurleen'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 35, 'quantity' => 200],
                    ['type' => 'Barbershop Package', 'price' => 75, 'quantity' => 40, 'description' => 'Includes vintage album + bow tie'],
                ],
            ],
            [
                'name' => '🎷 Springfield Symphony with Lisa',
                'description' => "## 🎷 A Night of Classical Refinement ✨\n\nCulture comes to Springfield! Lisa Simpson leads an evening of sophisticated music. 🌟\n\n> \"I used to think classical music was boring. Then I realized I wasn't listening hard enough.\" - Lisa Simpson\n\n### 🎵 Tonight's Program\n- 🎷 **Lisa Simpson: Saxophone Solo** - The prodigy performs\n- 💔 **Bleeding Gums Murphy Tribute** - Tissues recommended 😢\n- 🎶 *\"Jazzman\" Orchestral Arrangement* - Full symphony version\n- 🎹 **Beethoven's 5th** - The 4th was too short anyway\n- 🏖️ *\"Sax on the Beach\"* - Lisa's original composition\n\n---\n\n### 👔 Dress Code\n- 🎩 Black tie **optional** (Homer will be in a muumuu 👗)\n- 🎭 Cultural appreciation **required**\n- 🍩 Donuts **confiscated at door** (sorry, Homer)\n\n*\"If anyone wants me, I'll be in my room.\" - Lisa, after an amazing performance* 🚪",
                'duration' => 3,
                'group' => 'Live Music',
                'category_id' => 4,
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
                'name' => "🎆 New Year's Eve: Springfield Countdown",
                'description' => "## 🎆 Ring in the New Year Springfield Style! 🥳\n\nLive music, DJ after midnight, Duff toast, and the best view of the tire fire! ✨\n\n> \"Another year gone, another year of being Homer Simpson.\" - Homer (optimistically)\n\n### 🎉 Package Includes\n- 🍺 **Open Duff Bar** - 9 PM to 2 AM (pace yourself!)\n- 🍔 **Krusty Burger Appetizers** - Partially gelatinated, non-dairy, gum-based\n- 🥂 **Duff Toast at Midnight** - The official Springfield tradition\n- 🎊 **Party Favors** - Itchy & Scratchy themed (mildly violent)\n- 🔥 **Tire Fire Viewing** - Best seats in the house!\n\n---\n\n### 📅 Countdown Schedule\n- 🎵 **9 PM:** Live music begins 🎤\n- 🎧 **11 PM:** DJ Sideshow Bob takes over\n- 🥂 **11:59 PM:** Champagne (Duff) toast prep\n- 🎆 **12:00 AM:** HAPPY NEW YEAR! 🎊\n- 🎉 **12:01 AM:** \"Don't have a cow, man!\" - Annual Bart quote 🐄\n\n*Happy New Year, Springfield! May your tire fire burn eternal!* 🔥",
                'duration' => 6,
                'group' => 'Special Events',
                'category_id' => 8,
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
                'name' => '🍩 Lard Lad Donut Eating Contest',
                'description' => "## 🍩 Mmm... Competitive Eating! 😋\n\nHomer Simpson's record: 47 donuts in one sitting. Can you beat it? 🏆\n\n> \"Donuts. Is there anything they can't do?\" - Homer Simpson\n\n### 📋 Contest Rules\n- 🙌 **No hands** for the first round (face-first eating only)\n- 🎀 **Pink frosted sprinkled donuts only** - Homer's favorite\n- 🗣️ **\"I can't believe I ate the whole thing\"** must be said after\n- ⏱️ **Time limit:** 30 minutes or until you give up\n\n---\n\n### 🏆 Prizes\n- 🥇 **1st Place:** Golden Lard Lad statue + year supply of donuts 🍩\n- 🥈 **2nd Place:** Giant pink donut pillow\n- 🥉 **3rd Place:** Antacids and our respect\n\n### ⚠️ Medical Disclaimer\n- 🩺 Dr. Hibbert will be on standby (\"Hehehehe!\")\n- 💊 Tums provided\n- 🤢 Bucket available (no shame)\n\n*\"I've eaten eight different kinds of honey!\" - Homer (training regimen)* 🍯",
                'duration' => 2,
                'group' => 'Special Events',
                'category_id' => 6,
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
                'name' => '🍅 Tomacco Tasting Night',
                'description' => "## 🍅 It Tastes Like Grandma! I Want More! 😋\n\nTry Homer's controversial hybrid crop: the legendary Tomacco! 🌱\n\n> \"This tomacco is refreshing and addictive! That makes it okay, right?\" - Ralph Wiggum\n\n### 🍽️ Tonight's Tasting Menu\n- 🍅 **Pure Tomacco Samples** - The original hybrid\n- 🥤 **Tomacco Juice** - Surprisingly smooth\n- 🫙 **Tomacco Salsa** - Highly addictive (we warned you)\n- 🥗 **Tomacco Salad** - It's healthy... ish\n\n---\n\n### ⚠️ Important Warnings\n- 🚨 **Product may be highly addictive** - Animals WILL break down fences\n- 🐄 **Keep away from farm animals** - They become obsessed\n- 🚫 **Tobacco company executives NOT invited** - We're watching\n- 💰 **Laramie sponsorship** - Pending legal review\n\n*\"This is horrible! More please!\" - Everyone who tries it* 🤤\n\n**Side effects may include:** Craving more tomacco, inexplicable happiness, and an urge to grow your own. 🌿",
                'duration' => 2,
                'group' => 'Special Events',
                'category_id' => 6,
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
                'description' => "## 🔥 What the Hell is That?! LE GRILLE?! 😤\n\nOur weekly BBQ competition! Assembly instructions NOT included. 📦\n\n> \"Why must I fail at every attempt at masonry?!\" - Homer Simpson, grill assembly expert\n\n### 🏆 Competition Categories\n- 🧇 **Homer's Moon Waffles** - Caramelized with a toothpaste glaze\n- 🔥 **Hibachi Grilling** - Build it yourself (good luck)\n- 🥓 **Best Burnt Offerings** - Sometimes charcoal is the goal\n- 🥓 **Bacon of the Gods** - As thick as Homer's... confidence\n\n---\n\n### 🔧 Grill Assembly Station\n- 🇫🇷 Instructions available in **French only** (Le Grille!)\n- 🔩 Extra parts provided (you'll need them)\n- 😤 Frustration counselor on site\n- 🍺 Duff provided to ease the pain\n\n### 🎁 Prizes\n- 🥇 **Best BBQ:** New grill (pre-assembled)\n- 🧯 **Best Assembly Meltdown:** Fire extinguisher\n- 🥉 **Participation:** Leftover charcoal\n\n*\"Stupid Lisa! Stupid hamburger-eater!\" - Homer (mid-assembly)* 🤬",
                'duration' => 4,
                'group' => 'Special Events',
                'category_id' => 6,
                'curator_group' => 'Community',
                'venue' => 'demo-lardlad',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 17,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-lurleen',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 12, 'quantity' => 80],
                    ['type' => 'Competitor Entry', 'price' => 25, 'quantity' => 20, 'description' => 'Includes grill station + propane'],
                ],
            ],
            [
                'name' => '🍔 Steamed Hams Cooking Class',
                'description' => "## Steamed Hams! An Albany Expression!\n\nWell, Seymour, you are an odd fellow, but I must say... you steam a good ham.\n\n> \"I said 'steamed hams!' It's an Albany expression!\" - Principal Skinner\n\n### Tonight's Menu\n- **Steamed Hams** - Obviously grilled, we call them steamed 🍔\n- **Aurora Borealis Flambé** - Localized entirely within your kitchen\n- **Superintendent's Surprise** - Chalmers-approved\n- **Side Salad** - To pretend this is healthy\n\n---\n\n### Cooking Class Includes\n- **Technique:** How to pass off grilled as steamed\n- **Fire safety:** Extinguishers serviced and ready\n- **Acting lessons:** \"Well, I should be— GOOD LORD, WHAT IS HAPPENING IN THERE?!\"\n- **Aurora Borealis tutorial:** \"At this time of year, at this time of day, in this part of the country, localized entirely within your kitchen?\"\n\n*\"May I see it?\" \"No.\"*",
                'duration' => 2,
                'group' => 'Special Events',
                'category_id' => 6,
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
                'description' => "## 🌟 Springfield's Finest Showcase Their Hidden Talents!\n\nYou've got talent! (Maybe. We'll see.) 🎭\n\n> \"I bent my wookie!\" - Ralph Wiggum, potential contestant\n\n### 🎤 Expected Performances\n- 🎩 **Mr. Burns:** Juggling (with hounds as backup)\n- 🏈 **Hans Moleman:** \"Football in the Groin\" reenactment (audience participation optional)\n- 🦸 **Comic Book Guy:** Worst. Performance. Ever. (his words)\n- 🖍️ **Ralph Wiggum:** TBD (probably glue-related)\n- 🎷 **Lisa Simpson:** Setting the bar impossibly high\n\n---\n\n### 👨‍⚖️ Judges Panel\n- 🎩 **Mayor Quimby** - \"Vote Quimby... er, I mean, I judge fairly!\"\n- 🤡 **Krusty** - \"Hey hey! This better not waste my time!\"\n- 🎷 **Lisa Simpson** - The only qualified judge\n\n### 🏆 Prizes\n- 🥇 **Winner:** $100 + glory\n- 🥈 **Runner-up:** Krusty Burger gift card\n- 🎀 **Everyone else:** Participated!\n\n*\"I'm Idaho!\" - Ralph, during his act* 🥔",
                'duration' => 3,
                'group' => 'Special Events',
                'category_id' => 8,
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 14,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talents' => ['demo-krusty', 'demo-lurleen', 'demo-lisajazz'],
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 15, 'quantity' => 120],
                    ['type' => 'Judges Table', 'price' => 40, 'quantity' => 15, 'description' => 'Best seats + voting privileges'],
                ],
            ],
            [
                'name' => '🏛️ The Stonecutters Secret Show',
                'description' => "## Who Controls the British Crown? WE DO! WE DO!\n\nExclusive Stonecutters members-only event!\n\n> \"Who keeps Atlantis off the maps? Who keeps the Martians under wraps? WE DO! WE DO!\" - The Sacred Song\n\n### Tonight's Agenda\n- **Sacred Songs** - Full \"We Do\" performance\n- **Secret Rituals** - (Can't tell you, it's secret) 🤫\n- **Brotherhood Bonding** - Duff, but fancy\n- **Parchment Reading** - Ancient wisdom or something\n\n---\n\n### Entry Requirements\n- **Sacred Parchment** - Required at door\n- **Member Number** - Know yours!\n- **Number 908 (Homer)** - Still NOT invited (he knows what he did)\n- **Dress Code:** Robes or business casual\n\n### Who Made Steve Guttenberg a Star?\n**WE DO! WE DO!**\n\n*\"Oh no, no, wait, wait. Let me explain about the car chase. I thought the cop was a ghost!\"*",
                'duration' => 4,
                'group' => 'Special Events',
                'category_id' => 3,
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
                'description' => "## 🧠 I Am So Smart! S-M-R-T!\n\nI mean S-M-A-R-T! Springfield's most entertaining spelling bee! 📝\n\n> \"I am so smart! I am so smart! S-M-R-T! I mean S-M-A-R-T!\" - Homer Simpson\n\n### 📋 Competition Rules\n- ✅ **Words like \"be\" and \"cat\" accepted** - We're inclusive\n- 🍩 **Homer Simpson as guest judge** - What could go wrong?\n- 🏆 **Prize:** A feeling of adequacy and a small trophy\n- 📚 **Practice words provided** - \"Cromulent\" and \"embiggen\" included\n\n---\n\n### 🔤 Sample Words by Difficulty\n- 🟢 **Easy:** Cat, dog, Duff\n- 🟡 **Medium:** Saxophone, nuclear, donut\n- 🔴 **Hard:** Nahasapeemapetilon, Terwilliger\n- 🍩 **Homer Level:** SMART (results may vary)\n\n*Sponsored by Springfield Elementary's \"No Child Left Behind\" Program* 🏫\n\n**Winner gets to say \"I am so smart!\" on stage!** (Correctly.) 🎤",
                'duration' => 2,
                'group' => 'Comedy',
                'category_id' => 3,
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
                'description' => "## 👴 Back in My Day, We Had to Walk 15 Miles!\n\nJust to play bingo! And we LIKED it! Hosted by Abraham \"Grampa\" Simpson. 🎯\n\n> \"We can't bust heads like we used to, but we have our ways. One trick is to tell 'em stories that don't go anywhere.\" - Grampa\n\n### 📖 Expect Long Stories About\n- 🧅 **The onion on his belt** - \"Which was the style at the time!\"\n- 🚗 **Shelbyville and their speed holes** - They make the car go faster\n- 📝 **\"Dear Mr. President, there are too many states nowadays\"** - Full reading\n- 🎩 **The time he was almost Taft** - Or was it Roosevelt?\n\n---\n\n### 🎁 Bingo Prizes\n- 🥇 **Full House:** $50 + one of Grampa's war stories\n- 🥈 **Five in a Row:** Subscription to the Springfield Shopper\n- 🍬 **Participation:** Hard candy from Grampa's pocket\n\n*\"Old Man Yells At Bingo Card\" - Headline waiting to happen* 📰\n\n⚠️ *Warning: Stories may take 45 minutes with no discernible point.* 😴",
                'duration' => 2,
                'group' => 'Special Events',
                'category_id' => 3,
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 14,
                'minute' => 0,
                'days_of_week' => '0010000', // Tuesday
                'talent' => 'demo-lurleen',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 8, 'quantity' => 50],
                    ['type' => 'Retirement Castle Package', 'price' => 15, 'quantity' => 20, 'description' => '3 bingo cards + pudding cup'],
                ],
            ],
            [
                'name' => 'Do It For Her Night',
                'description' => "## 💕 DON'T FORGET: YOU'RE HERE FOREVER\n\n...covered by Maggie's photos to spell \"DO IT FOR HER.\" 👶\n\n> \"This is the most emotional room at the nuclear plant.\" - Every visitor to Homer's workstation\n\n### 🎨 A Heartwarming Evening\n- 🖼️ **Photo Collage Workshop** - Create your own \"Do It For Her\" display\n- ✨ **Motivational Sign-Making** - Turn your dread into inspiration\n- 📋 **Templates Provided** - Make any workplace bearable\n- 🧅 **Onions Available** - For crying purposes (no judgment)\n\n---\n\n### 💖 Why This Works\n- 📝 **Homer had a sign** that said \"Don't Forget: You're Here Forever\"\n- 👶 **Maggie's photos** now cover it to spell \"Do It For Her\"\n- 😢 **It gets us every time** - Every. Single. Time.\n\n### 🎁 Take Home\n- 🖼️ Your completed collage\n- 💪 A renewed sense of purpose\n- 🧻 Tissues (you'll need them)\n\n*Maggie appearances not guaranteed but highly probable. Bring photos of your loved ones!* ❤️",
                'duration' => 2,
                'group' => 'Special Events',
                'category_id' => 3,
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-lurleen',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 10, 'quantity' => 60],
                    ['type' => 'Family Package', 'price' => 25, 'quantity' => 20, 'description' => 'Includes photo frame + tissue box'],
                ],
            ],
            [
                'name' => '"Monorail!" Town Meeting',
                'description' => "## MONORAIL! MONORAIL! MONORAIL! 🚝\n\nA re-creation of Springfield's most musical town meeting!\n\n> \"I've sold monorails to Brockway, Ogdenville, and North Haverbrook, and by gum, it put them on the map!\" - Lyle Lanley\n\n### Tonight's Agenda\n- **Should Springfield get a monorail?** (Spoiler: The answer is always yes)\n- **Lyle Lanley Presentation** - Watch the master at work\n- **\"But Main Street's still all cracked and broken!\" rebuttals** - Sorry, Marge\n- **Group Singing** - Encouraged! Required! MONORAIL!\n\n---\n\n### Song Lyrics (Practice These!)\n*\"Monorail! Monorail! Monorail!\"*\n*\"What about us brain-dead slobs?\"*\n*\"You'll be given cushy jobs!\"*\n\n### ⚠️ Disclaimers\n- **Marge's concerns** will be politely ignored\n- **No one from North Haverbrook** may attend\n- **Possum problems** are not our responsibility\n\n*Is there a chance the track could bend? NOT ON YOUR LIFE, MY HINDU FRIEND!*",
                'duration' => 2,
                'group' => 'Special Events',
                'category_id' => 3,
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0100000', // Monday
                'talent' => 'demo-lurleen',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 0, 'quantity' => 200],
                ],
            ],
            [
                'name' => 'Radioactive Man Auditions',
                'description' => "## ☢️ JIMINY JILIKERS! Open Auditions!\n\nThe search for the next Fallout Boy is ON!\n\n> \"Up and atom!\" \"Up and at them!\" \"...Up and ATOM!\" \"...better.\" - The audition process\n\n### 📋 Requirements\n- 🗣️ **Must be able to say \"Jiminy jilikers!\"** correctly (no \"Jiminy JILLIKERS\")\n- ☢️ **Radiation resistance** preferred but not required\n- 🚫 **No Milhouse** - He didn't say it right. Twice.\n- 👦 **Age:** Young enough to play a sidekick\n\n---\n\n### 🎭 Audition Process\n1. 📝 **Line reading:** \"Jiminy jilikers, Radioactive Man!\"\n2. 🎬 **Screen test:** Stand next to Rainier Wolfcastle\n3. 🥽 **Goggle test:** Put them on, look cool\n4. ☢️ **Radiation exposure:** (simulated... probably)\n\n### 🥽 Equipment Provided\n- Goggles (they do nothing)\n- Fallout Boy costume (one size fits most)\n- Radioactive glow stick (not actually radioactive)\n\n*\"Real acid?!\" - Milhouse, discovering the movie's budget*",
                'duration' => 4,
                'group' => 'Special Events',
                'category_id' => 8,
                'curator_group' => 'Entertainment',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_rock.jpg',
                'hour' => 10,
                'minute' => 0,
                'days_of_week' => '0100000', // Monday
                'talent' => 'demo-troymcclure',
                'tickets' => [
                    ['type' => 'Audition Entry', 'price' => 10, 'quantity' => 100],
                    ['type' => 'Spectator', 'price' => 5, 'quantity' => 50],
                ],
            ],
            [
                'name' => 'Springfield A&M College Fair',
                'description' => "## 🎓 Clown College! Bovine University! And More!\n\nExplore your higher education options at Springfield's finest college fair!\n\n> \"I call it Billy and the Cloneasaurus!\" - Apu (rejected thesis)\n\n### 🏫 Participating Schools\n- 🐄 **Springfield A&M** - Go Cow! (Lousy Smarch weather program)\n- 🐮 **Bovine University** - \"See? The cow doesn't look unhappy.\"\n- 🤡 **Clown College** - Homer's calling?\n- 🏥 **Hollywood Upstairs Medical College** - \"Hi, Dr. Nick!\"\n- 🔬 **Springfield Heights Institute of Technology** - (S.H.I.T.)\n\n---\n\n### 📚 Degree Programs Available\n- 🎪 **Clowning (AA, BA, PhD)** - From pie-throwing to advanced seltzer\n- 🐄 **Bovine Studies** - Moo.\n- 💉 **Hollywood Medicine** - \"The knee bone's connected to the... something\"\n- ☢️ **Nuclear Safety** - How hard could it be?\n\n*\"That's the college for me!\" - Homer, seeing the clown college ad*",
                'duration' => 4,
                'group' => 'Special Events',
                'category_id' => 5,
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_special.jpg',
                'hour' => 10,
                'minute' => 0,
                'days_of_week' => '0010000', // Tuesday
                'talent' => 'demo-frink',
                'tickets' => [
                    ['type' => 'Free Entry', 'price' => 0, 'quantity' => 200],
                ],
            ],
            [
                'name' => "Mr. Burns' Retirement Planning",
                'description' => "## 😈 Excellent... Financial Immortality Awaits!\n\nLearn from Springfield's oldest (and most excellent) billionaire!\n\n> \"I'd give it all up for a little more.\" - C. Montgomery Burns\n\n### 💰 Topics Covered\n- 👴 **How to Live Forever** - Almost! (Ask his doctor team)\n- 🐕 **Releasing the Hounds** on competitors - A business strategy\n- 🍫 **\"Ah yes, when candy bars cost a nickel\"** - Historical economics\n- ☀️ **Sun-blocking as Investment Strategy** - Think big! Block out the sun!\n- 💎 **Trust Funds for Dogs** - Just in case\n\n---\n\n### 📋 Seminar Rules\n- 📝 **Smithers will be taking attendance**\n- 🐕 **Hounds on standby** (for demonstration purposes)\n- 💼 **Business casual dress code** - No union t-shirts\n- 🚫 **Questions about Bobo** - Will NOT be answered\n\n### 💡 Burns' Business Tips\n- *\"I'd trade it all for a little more\"*\n- *\"Release the hounds!\"*\n- *\"Smithers, who is that man?\"*\n\n*Ahoy-hoy!*",
                'duration' => 2,
                'group' => 'Special Events',
                'category_id' => 2,
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
                'description' => "## 💕 Let's Bee Friends! I Choo-Choo-Choose You! 🚂\n\nSpringfield's premier singles event for lonely hearts! 💘\n\n> \"You choo-choo-choose me?\" - Ralph Wiggum (the origin of all this)\n\n### 💝 How It Works\n- ⏱️ **5 minutes per date** - Make it count!\n- 💌 **Free conversation hearts** - \"Bee Mine,\" \"U R 2 QT\"\n- 🚂 **\"I Choo-Choo-Choose You\" cards** - Provided\n- 📝 **Match cards** - Mark \"yes\" or \"Moe's level of loneliness\"\n\n---\n\n### ⚠️ Important Rules\n- 🚫 **Ralph Wiggum NOT in attendance** - (Restraining order, long story)\n- 🐱 **Please do not give valentines** to anyone whose cat's breath smells like cat food\n- 🙅 **No Lisa-shaming** - She said no, and that's okay\n- 🍺 **Moe will be bartending** - He understands your pain\n\n### 💑 Success Stories\n- 💒 Kirk & Luann (remarried!)\n- 👬 Lenny & Carl (best friends count)\n- 💕 Homer & Marge (they met here! Just kidding, they didn't)\n\n*\"So, do you like... stuff?\" - Your opening line, probably* 😅",
                'duration' => 3,
                'group' => 'Special Events',
                'category_id' => 3,
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 19,
                'minute' => 0,
                'days_of_week' => '0001000', // Wednesday
                'talent' => 'demo-lurleen',
                'tickets' => [
                    ['type' => 'Singles Entry', 'price' => 25, 'quantity' => 50],
                    ['type' => 'Milhouse Package', 'price' => 40, 'quantity' => 15, 'description' => 'Guaranteed second date (not really)'],
                ],
            ],
            [
                'name' => 'Max Power Networking Night',
                'description' => "## Nobody Snuggles with MAX POWER! 💪\n\nYou strap yourself in and FEEL THE Gs!\n\n> \"Max Power! He's the man whose name you'd love to touch! But you mustn't touch!\" - The Song\n\n### Network with Springfield's Elite\n- **Name Change Workshop** - Find YOUR power name\n- **Meet Trent Steel** and other successful people\n- **Power Handshakes** - Firm, meaningful, intimidating\n- **Business Card Exchange** - Make yours memorable\n\n---\n\n### Power Naming Tips\n- **Names inspired by hair dryers** - Welcome!\n- **Two power words** - Like \"Max Power\" or \"Rembrandt Q. Einstein\"\n- **Avoid:** Homer-like names (too relatable)\n- **Aim for:** \"Man whose name you'd love to touch\"\n\n### Featured Speakers\n- **Max Power** (née Homer Simpson)\n- **Rock Strongo** - Motivation specialist\n- **Trent Steel** - Success incarnate\n\n*\"His name sounds good in your ear, but when you say it, you mustn't fear!\"*",
                'duration' => 2.5,
                'group' => 'Special Events',
                'category_id' => 2,
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
                'description' => "## 🎉 Finally! Everything's Coming Up Milhouse! 🌈\n\nA celebration of mediocrity! Tonight, EVERYBODY likes Milhouse! 🤓\n\n> \"My feet are soaked, but my cuffs are bone dry! Everything's coming up Milhouse!\" - Milhouse Van Houten\n\n### 🥳 Tonight's Festivities\n- 🎮 **Thrillho Video Game Tournament** - Play as \"THRILLHO\" (name didn't fit)\n- 💪 **\"My Mom Says I'm Cool\" Affirmation Station** - She does!\n- 💇 **Vaseline-Based Hair Styling Tips** - Get that Milhouse sheen\n- 🍇 **Purple Fruit Drinks** - Milhouse's favorite!\n\n---\n\n### 📊 Milhouse Facts\n- 👓 His glasses are prescription AND style\n- 💔 Lisa has said no 47 times (and counting)\n- 👨‍👩‍👦 His parents are divorced (it's complicated)\n- 📱 He's a meme! (That's cool, right?)\n\n### 🏆 Prizes\n- 🥇 **Best Milhouse Impression:** Dignity\n- 🎮 **Thrillho Champion:** Video game (slightly used)\n- 🤗 **Participation:** Knowing you're not alone\n\n*\"Remember: Nobody likes Milhouse!\" - Tonight, that changes.* ✨",
                'duration' => 3,
                'group' => 'Special Events',
                'category_id' => 8,
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_party.jpg',
                'hour' => 18,
                'minute' => 0,
                'days_of_week' => '1000000', // Sunday
                'talent' => 'demo-frink',
                'tickets' => [
                    ['type' => 'General Admission', 'price' => 10, 'quantity' => 80],
                    ['type' => 'Thrillho Package', 'price' => 25, 'quantity' => 20, 'description' => 'Includes race car bed photo op'],
                ],
            ],
            [
                'name' => 'Inanimate Carbon Rod Appreciation Night',
                'description' => "## IN ROD WE TRUST! 🏆\n\nCelebrate the hero that saved the Corvair space shuttle!\n\n> \"In Rod We Trust!\" - The Nation\n\n### Tonight's Program\n- **Documentary Screening:** \"Rod: The Movie\"\n- **Meet and Greet** with THE Rod (inanimate carbon rod)\n- **Time Magazine \"Inanimate Object of the Year\"** ceremony\n- **Photo Ops** with the Rod (it won't move, don't worry)\n\n---\n\n### Rod's Accomplishments\n- **Saved the Corvair space shuttle** - By being a rod\n- **Time Magazine cover** - \"In Rod We Trust!\"\n- **Beat Homer Simpson** for recognition (he's still bitter)\n- **National hero status** - For rodding\n\n### ⚠️ Attendance Notes\n- **Homer Simpson NOT invited** - He's still upset about this\n- **The rod will be under glass** - Look, don't touch\n- **Inanimate carbon rod enthusiasts** welcome!\n\n*\"Huh?! An inanimate carbon rod?!\" - Homer (furiously)*",
                'duration' => 2,
                'group' => 'Special Events',
                'category_id' => 3,
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
                'description' => "## I Have to Go Now. My Planet Needs Me.\n\n*Note: Poochie died on the way back to his home planet.*\n\n> \"When are they gonna get to the fireworks factory?!\" - Milhouse\n\n### Tonight's Tribute\n- **Screening of Poochie's only episode** - The one. The only.\n- **\"Poochitize Me, Cap'n!\" Drink Specials** - Extreme!\n- **Rastafarian Surfer Costume Contest** - Proactive! Paradigm! 🏄\n- **Roy Appearance** - Maybe! (Who's Roy?)\n\n---\n\n### Who Was Poochie?\n- **Extreme dog** with attitude\n- **Surfed** and... rapped?\n- **\"One outrageous dude\"** - According to Homer's voice acting\n- **Died on the way back** to his home planet (RIP)\n\n### Focus Group Results\n- \"Cute! But I want a dog, not Fonzie in a dog suit.\"\n- \"The original dog... he's better than 10 Super Bowls!\"\n- \"So he's proactive, huh?\"\n\n*\"Rest in peace, brave Poochie. You were like a dog... to me.\"*",
                'duration' => 2,
                'group' => 'Special Events',
                'category_id' => 8,
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
                'description' => "## 🐵 PURPLE MONKEY DISHWASHER!\n\nThe championship telephone game tournament!\n\n> \"By the end of the meeting, the sentence 'we'll be negotiating our own contract' became 'purple monkey dishwasher.'\" - The Legend\n\n### 🎮 Tonight's Game\n- 📞 **Championship Telephone Tournament** - 10 teams compete!\n- 👥 **Teams of 10** - More people = more chaos\n- 🏆 **Prizes for Most Creative Misinterpretations** - The wronger, the better\n- 👨‍⚖️ **Lenny and Carl as Referees** - Impartial-ish\n\n---\n\n### 📋 How It Works\n1. 🗣️ First person gets a sentence\n2. 👂 Whisper it to the next person\n3. 🔄 Repeat until the end\n4. 🤣 Laugh at the result\n\n### 🏆 Scoring\n- 🥇 **Most Accurate:** 0 points (boring!)\n- 🥈 **Funniest Misinterpretation:** 100 points!\n- 🥉 **\"Purple Monkey Dishwasher\" Result:** AUTOMATIC WIN\n\n*\"We're sorry the teachers won't come back until you rehire Principal Skinner purple monkey dishwasher.\"*",
                'duration' => 2,
                'group' => 'Comedy',
                'category_id' => 8,
                'curator_group' => 'Community',
                'venue' => 'demo-communitycenter',
                'image' => 'demo_flyer_comedy.jpg',
                'hour' => 20,
                'minute' => 0,
                'days_of_week' => '0010000', // Tuesday
                'talent' => 'demo-krusty',
                'tickets' => [
                    ['type' => 'Player Entry', 'price' => 10, 'quantity' => 60],
                    ['type' => 'Team Registration (10 people)', 'price' => 80, 'quantity' => 6],
                ],
            ],
            [
                'name' => 'Comic Book Guy\'s Worst. Event. Ever.',
                'description' => "## 🦸 Worst. Event. EVER. (Or Best?)\n\nHosted by Springfield's most sarcastic resident, Comic Book Guy!\n\n> \"Ooh, a sarcasm detector. That's a REAL useful invention.\" - Comic Book Guy\n\n### 🎭 Tonight's Activities\n- 👎 **Worst Costume Contest** - Intentionally bad only\n- 💀 **Trivia:** Name characters who've died and returned (long list)\n- 🤖 **\"There Is No Emoticon for What I Am Feeling\" Workshop**\n- 🌮 **Taco Bell Dog Memorial** - \"Yo quiero Taco Bell\" (RIP)\n\n---\n\n### 😐 Comic Book Guy's Greatest Quotes\n- 📺 \"Worst episode ever.\"\n- 💔 \"I've wasted my life.\"\n- 🍩 \"Ooh, loneliness and cheeseburgers are a dangerous mix.\"\n- 🦸 \"I must get back to my comic book store, where I dispense the insults rather than absorb them.\"\n\n### 🏆 Prizes\n- 🥇 **Worst Costume:** Rare comic book (maybe)\n- 🥈 **Best Sarcasm:** A raised eyebrow from CBG\n- 🥉 **Participation:** \"Worst. Attendee. Ever.\" certificate\n\n*\"Loneliest guy in the world\" seeks company. No eye contact required.*",
                'duration' => 3,
                'group' => 'Comedy',
                'category_id' => 8,
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
