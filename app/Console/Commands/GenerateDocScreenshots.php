<?php

namespace App\Console\Commands;

use App\Models\Referral;
use App\Models\Role;
use App\Models\User;
use App\Services\DemoService;
use App\Utils\ImageUtils;
use App\Utils\UrlUtils;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;

class GenerateDocScreenshots extends Command
{
    protected $signature = 'app:generate-doc-screenshots {--page= : Generate screenshots for a single docs page} {--force : Overwrite existing files}';

    protected $description = 'Generate AP screenshots for user guide docs using browser automation';

    private const TEMP_PASSWORD = 'doc-screenshots-temp-pw-2024';

    private const TEMP_EMAIL = 'screenshots@temp.local';

    private const OUTPUT_DIR = 'public/images/docs';

    private array $serverPipes = [];

    public function handle(): int
    {
        // Find demo user
        $user = User::where('email', DemoService::DEMO_EMAIL)->first();
        if (! $user) {
            $this->error('Demo user not found. Run php artisan app:setup-demo first.');

            return 1;
        }

        $role = Role::where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)->first();
        if (! $role) {
            $this->error('Demo schedule not found. Run php artisan app:setup-demo first.');

            return 1;
        }

        $venueRole = Role::where('subdomain', 'demo-moestavern')->first();

        $demoEvent = $role ? \App\Models\Event::whereHas('roles', fn ($q) => $q->where('roles.id', $role->id))
            ->upcomingOrOngoing()
            ->orderBy('starts_at')
            ->first() : null;

        // Build screenshot definitions
        $pages = $this->getPages($role, $venueRole, $demoEvent, $user);

        // Filter to single page if requested
        $singlePage = $this->option('page');
        if ($singlePage) {
            if (! isset($pages[$singlePage])) {
                $this->error("Unknown page: {$singlePage}. Available pages: ".implode(', ', array_keys($pages)));

                return 1;
            }
            $pages = [$singlePage => $pages[$singlePage]];
        }

        // Guard selfhost-admin pages behind APP_TESTING (they require granting admin privileges)
        if (! config('app.is_testing')) {
            $adminPages = array_filter(array_keys($pages), fn ($key) => str_starts_with($key, 'selfhost-admin'));
            if (! empty($adminPages)) {
                $this->warn('Skipping selfhost-admin pages: APP_TESTING is not enabled.');
                $this->warn('Set APP_TESTING=true in .env to generate admin screenshots.');
                $pages = array_diff_key($pages, array_flip($adminPages));
                if (empty($pages)) {
                    return 0;
                }
            }
        }

        $force = $this->option('force');
        $outputDir = public_path('images/docs');

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Temporarily remove Vite hot file so @vite uses built assets
        $hotFile = public_path('hot');
        $hotFileBackup = null;
        if (file_exists($hotFile)) {
            $hotFileBackup = $hotFile.'.bak';
            rename($hotFile, $hotFileBackup);
            $this->line('Temporarily moved Vite hot file to use built assets.');
        }

        // Save original credentials and set temp ones (temp email avoids demo mode warnings)
        $originalPasswordHash = $user->password;
        $originalEmail = $user->email;
        $originalIsAdmin = $user->is_admin;
        $originalEmailVerifiedAt = $user->email_verified_at;
        $user->password = Hash::make(self::TEMP_PASSWORD);
        $user->email = self::TEMP_EMAIL;
        if (config('app.is_testing')) {
            $user->is_admin = true;
        }
        $user->save();

        // Re-verify email (the User model clears email_verified_at on email change)
        $user->email_verified_at = now();
        $user->saveQuietly();

        // Clear log file so local errors don't appear in screenshots
        $logFile = storage_path('logs/laravel.log');
        $logBackup = null;
        if (file_exists($logFile)) {
            $logBackup = $logFile.'.bak';
            copy($logFile, $logBackup);
            file_put_contents($logFile, '');
            $this->line('Cleared laravel.log for clean screenshots.');
        }

        // Create referral demo data so the referral page has content
        $referralUsers = [];
        $referralRecords = [];
        if (isset($pages['referral-program'])) {
            // The history prints each address masked to its first two letters, so the five
            // begin differently: they all began "ref-demo-" once, and the picture showed the
            // same "re***@temp.local" five times.
            foreach (['lisa', 'jake', 'sara', 'mike', 'emma'] as $name) {
                $referralUsers[] = User::create([
                    'name' => ucfirst($name),
                    'email' => $name.'.'.uniqid().'@example.com',
                    'password' => Hash::make('temp'),
                ]);
            }

            $referralRecords[] = Referral::create([
                'referrer_user_id' => $user->id,
                'referred_user_id' => $referralUsers[0]->id,
                'status' => 'pending',
            ]);
            $referralRecords[] = Referral::create([
                'referrer_user_id' => $user->id,
                'referred_user_id' => $referralUsers[1]->id,
                'status' => 'subscribed',
                'plan_type' => 'pro',
                'subscribed_at' => now()->subDays(10),
            ]);
            $referralRecords[] = Referral::create([
                'referrer_user_id' => $user->id,
                'referred_user_id' => $referralUsers[2]->id,
                'status' => 'qualified',
                'plan_type' => 'pro',
                'subscribed_at' => now()->subDays(45),
                'qualified_at' => now()->subDays(2),
            ]);
            $referralRecords[] = Referral::create([
                'referrer_user_id' => $user->id,
                'referred_user_id' => $referralUsers[3]->id,
                'status' => 'credited',
                'plan_type' => 'enterprise',
                'subscribed_at' => now()->subDays(90),
                'qualified_at' => now()->subDays(50),
                'credited_at' => now()->subDays(48),
                'credited_role_id' => $role->id,
            ]);
            $referralRecords[] = Referral::create([
                'referrer_user_id' => $user->id,
                'referred_user_id' => $referralUsers[4]->id,
                'status' => 'expired',
            ]);

            $this->line('Created referral demo data.');
        }

        // The demo schedule has never sent a newsletter, and an empty list shows none of the
        // list: one sent, one scheduled and one draft are made for the picture and removed.
        $newsletterRecords = [];
        if (isset($pages['newsletters'])) {
            $newsletterRows = [
                ['subject' => 'This weekend in Springfield', 'status' => 'sent', 'sent_at' => now()->subDays(9)->setTime(14, 0), 'sent_count' => 248, 'open_count' => 131, 'click_count' => 37],
                ['subject' => 'New Year\'s Eve: tickets are on sale', 'status' => 'scheduled', 'scheduled_at' => now()->addDays(3)->setTime(15, 0)],
                ['subject' => 'November at a glance', 'status' => 'draft'],
            ];
            foreach ($newsletterRows as $row) {
                $newsletterRecords[] = \App\Models\Newsletter::create($row + [
                    'role_id' => $role->id,
                    'user_id' => $user->id,
                    'type' => 'schedule',
                    'blocks' => [],
                ]);
            }
            $this->line('Created newsletter demo data.');
        }

        // The demo data has no appointment types, and the Appointments tab without one is only its
        // opening line: three are made for the pictures and removed.
        $appointmentRecords = [];
        $appointmentRole = isset($pages['appointments']) ? Role::where('subdomain', 'demo-lisajazz')->first() : null;
        if ($appointmentRole) {
            $weekdays = fn (string $start, string $end) => [
                '0' => [], '1' => [['start' => $start, 'end' => $end]], '2' => [['start' => $start, 'end' => $end]],
                '3' => [['start' => $start, 'end' => $end]], '4' => [['start' => $start, 'end' => $end]],
                '5' => [['start' => $start, 'end' => $end]], '6' => [],
            ];
            $appointmentRows = [
                ['name' => 'Intro call', 'slug' => 'intro-call', 'duration_minutes' => 15, 'weekly_windows' => $weekdays('10:00', '16:00')],
                ['name' => 'Private lesson', 'slug' => 'private-lesson', 'duration_minutes' => 60, 'weekly_windows' => $weekdays('12:00', '20:00')],
                ['name' => 'Booking enquiry', 'slug' => 'booking-enquiry', 'duration_minutes' => 30, 'weekly_windows' => $weekdays('09:00', '17:00')],
            ];
            foreach ($appointmentRows as $row) {
                $type = new \App\Models\AppointmentType;
                $type->role_id = $appointmentRole->id;
                $type->price = 0;
                $type->payment_method = 'cash';
                $type->is_active = true;
                foreach ($row as $key => $value) {
                    $type->{$key} = $value;
                }
                $type->save();
                $appointmentRecords[] = $type;
            }
            $this->line('Created appointment demo data.');
        }

        // Realtime with nobody on the site is an empty page. realtime:simulate invents visitors and
        // refuses to run anywhere but a local or testing environment; its --purge takes them away,
        // along with every other row of realtime_hits (which holds about an hour of visits).
        $simulatedTraffic = false;
        if ((isset($pages['analytics']) || isset($pages['selfhost-admin'])) && app()->environment(['local', 'testing'])) {
            try {
                $simulatedTraffic = \Illuminate\Support\Facades\Artisan::call('realtime:simulate') === 0;
            } catch (\Throwable $e) {
                $this->warn('Could not simulate realtime traffic: '.$e->getMessage());
            }
        }

        try {
            return $this->generate($user, $pages, $force, $outputDir);
        } finally {
            if ($simulatedTraffic) {
                try {
                    \Illuminate\Support\Facades\Artisan::call('realtime:simulate', ['--purge' => true]);
                } catch (\Throwable $e) {
                    $this->warn('Could not remove the simulated realtime traffic: '.$e->getMessage());
                }
            }

            foreach ($appointmentRecords as $appointmentType) {
                $appointmentType->delete();
            }

            foreach ($newsletterRecords as $newsletter) {
                $newsletter->delete();
            }

            // Clean up referral demo data
            foreach ($referralRecords as $referral) {
                $referral->delete();
            }
            foreach ($referralUsers as $refUser) {
                $refUser->delete();
            }

            // Restore original credentials
            $user->password = $originalPasswordHash;
            $user->email = $originalEmail;
            $user->is_admin = $originalIsAdmin;
            $user->save();

            // Putting the email back clears email_verified_at again (the User model does that on
            // any email change), so the account was left unverified and bounced to /verify-email
            // on the next login. Same reason line 112 above re-verifies after switching to the
            // temp address.
            $user->email_verified_at = $originalEmailVerifiedAt;
            $user->saveQuietly();

            // Restore log file
            if ($logBackup && file_exists($logBackup)) {
                copy($logBackup, $logFile);
                unlink($logBackup);
            }

            // Restore hot file
            if ($hotFileBackup && file_exists($hotFileBackup)) {
                rename($hotFileBackup, $hotFile);
            }
        }
    }

    private function getPages(?Role $role, ?Role $venueRole, ?\App\Models\Event $demoEvent = null, ?User $user = null): array
    {
        $encodedRoleId = $role ? UrlUtils::encodeId($role->id) : null;

        // The seating screens all hang off the demo venue that actually has a plan. DemoService
        // seeds "Aztec Auditorium" on demo-aztectheater and attaches it to one of its events, so
        // these photograph a real room rather than an empty one.
        $seatingRole = Role::where('subdomain', 'demo-aztectheater')->first();
        $seatingPlan = $seatingRole
            ? \App\Models\SeatingPlan::where('role_id', $seatingRole->id)->where('is_deleted', false)->first()
            : null;
        $seatedEvent = $seatingPlan
            ? \App\Models\Event::where('seating_plan_id', $seatingPlan->id)->first()
            : null;

        // An event that sells tickets, opened from the schedule it belongs to, for the Tickets tab:
        // the one with the most ticket types, so the list has more than a single line. The demo
        // account only FOLLOWS some of the demo venues, and the form sends anyone who cannot edit
        // the schedule in the address somewhere else, so the schedule has to be one it edits.
        $ticketEventRoute = null;
        $ticketEvents = \App\Models\Event::where('tickets_enabled', true)
            ->whereNotNull('creator_role_id')
            ->withCount(['tickets' => fn ($q) => $q->where('is_deleted', false)])
            ->having('tickets_count', '>', 0)
            ->upcomingOrOngoing()
            ->orderByDesc('tickets_count')
            ->orderBy('starts_at')
            ->limit(50)
            ->get();
        foreach ($ticketEvents as $ticketEvent) {
            $ticketRole = Role::find($ticketEvent->creator_role_id);
            if ($ticketRole && $user && $user->isEditor($ticketRole->subdomain)) {
                $ticketEventRoute = '/'.$ticketRole->subdomain.'/edit-event/'.UrlUtils::encodeId($ticketEvent->id);
                break;
            }
        }

        // The check-in dashboard of an event nobody bought a ticket for is one line saying so:
        // open it on the event with the most paid sales.
        $checkinEvent = $user
            ? \App\Models\Event::managedBy($user)
                ->whereNull('appointment_type_id')
                ->withCount(['sales' => fn ($q) => $q->where('status', 'paid')->where('is_deleted', false)])
                ->orderByDesc('sales_count')
                ->first()
            : null;
        $checkinRoute = $checkinEvent && $checkinEvent->sales_count
            ? '/checkin?event='.UrlUtils::encodeId($checkinEvent->id)
            : '/checkin';

        $pages = [
            'getting-started' => [
                ['id' => 'getting-started--dashboard', 'route' => '/dashboard'],
                ['id' => 'getting-started--create-form', 'route' => '/new/venue'],
            ],
            'schedule-styling' => [
                ['id' => 'schedule-styling--section-style', 'route' => '/simpsons/edit', 'section' => 'section-style'],
                ['id' => 'schedule-styling--header-layout', 'route' => '/simpsons/edit', 'script' => "document.querySelector('a[data-section=\"section-style\"]').click(); var row = document.getElementById('style-tab-advanced'); row.click(); row.scrollIntoView({ block: 'start' }); window.scrollBy(0, -90);"],
            ],
            'allocated-seating' => [
                ['id' => 'allocated-seating--plans', 'route' => $seatingRole ? '/demo-aztectheater/seating' : null],
                ['id' => 'allocated-seating--designer', 'route' => $seatingPlan ? '/demo-aztectheater/seating/'.UrlUtils::encodeId($seatingPlan->id).'/design' : null, 'pause' => 2500],
                ['id' => 'allocated-seating--box-office', 'route' => $seatedEvent ? '/demo-aztectheater/seating/box-office/'.UrlUtils::encodeId($seatedEvent->id) : null, 'pause' => 2500],
                ['id' => 'allocated-seating--report', 'route' => $seatedEvent ? '/demo-aztectheater/seating/box-office/'.UrlUtils::encodeId($seatedEvent->id).'/report' : null, 'pause' => 1500],
                // The picker sits behind "Buy Tickets" and then "Choose your own seats", so it has
                // to be clicked open before it can be photographed.
                ['id' => 'allocated-seating--picker', 'route' => $seatedEvent ? '/demo-aztectheater/'.$seatedEvent->slug.'/'.UrlUtils::encodeId($seatedEvent->id) : null, 'public' => true, 'pause' => 2500, 'script' => "[...document.querySelectorAll('a,button')].filter(b => /buy tickets/i.test(b.textContent))[0]?.click(); setTimeout(() => [...document.querySelectorAll('button')].filter(b => /choose your own/i.test(b.textContent))[0]?.click(), 900)"],
            ],
            'creating-schedules' => [
                ['id' => 'creating-schedules--section-details', 'route' => '/simpsons/edit', 'section' => 'section-details'],
                ['id' => 'creating-schedules--section-address', 'route' => $venueRole ? '/demo-moestavern/edit' : null, 'section' => 'section-address'],
                ['id' => 'creating-schedules--section-contact-info', 'route' => '/simpsons/edit', 'script' => "document.querySelector('a[data-section=\"section-details\"]').click(); var row = document.querySelector('.details-tab[data-tab=\"contact\"]'); row.click(); row.scrollIntoView({ block: 'start' }); window.scrollBy(0, -90);"],
                ['id' => 'creating-schedules--section-subschedules', 'route' => '/simpsons/edit', 'section' => 'section-subschedules'],
                ['id' => 'creating-schedules--section-settings', 'route' => '/simpsons/edit', 'section' => 'section-settings'],
                ['id' => 'creating-schedules--section-engagement', 'route' => '/simpsons/edit', 'section' => 'section-engagement'],
                ['id' => 'creating-schedules--section-sources', 'route' => '/simpsons/edit', 'section' => 'section-sources'],
                ['id' => 'creating-schedules--section-auto-import', 'route' => '/simpsons/edit', 'section' => 'section-auto-import'],
                ['id' => 'creating-schedules--section-integrations', 'route' => '/simpsons/edit', 'section' => 'section-integrations'],
                // No picture of the Email Settings row: it exists only on a hosted install, and this
                // command's server runs with IS_HOSTED=false. The entry that used to be here clicked
                // a row that was not there and photographed the Integrations list a second time.
            ],
            'creating-events' => [
                ['id' => 'creating-events--schedule-tab', 'route' => '/simpsons/schedule'],
                ['id' => 'creating-events--add-event', 'route' => '/simpsons/add-event'],
                // A new event's Tickets tab: the three tiles, nothing chosen yet.
                ['id' => 'creating-events--tickets-tab', 'route' => '/simpsons/add-event', 'section' => 'section-tickets'],
                ['id' => 'creating-events--listing', 'route' => '/simpsons/add-event', 'section' => 'section-listing'],
                ['id' => 'creating-events--import', 'route' => '/simpsons/import/ai'],
            ],
            'fan-content' => [
                ['id' => 'fan-content--videos-tab', 'route' => '/simpsons/videos'],
            ],
            'sharing' => [
                ['id' => 'sharing--guest-portal', 'route' => '/simpsons', 'public' => true],
                // The dialog behind Actions, Embed Schedule.
                ['id' => 'sharing--embed-dialog', 'route' => '/simpsons/schedule', 'script' => "document.getElementById('embed-schedule-link').click();"],
            ],
            'event-graphics' => [
                ['id' => 'event-graphics--graphic-page', 'route' => '/simpsons/events-graphic', 'pause' => 3000],
                // The second one is the Caption row open (the page's rows carry aria-controls,
                // which is also what the Help link follows).
                ['id' => 'event-graphics--settings', 'route' => '/simpsons/events-graphic', 'pause' => 3000, 'script' => "document.querySelector('[aria-controls=\"graphic-pane-caption\"]').click()"],
            ],
            'newsletters' => [
                ['id' => 'newsletters--list', 'route' => '/newsletters?role_id='.$encodedRoleId],
                ['id' => 'newsletters--create', 'route' => '/newsletters/create?role_id='.$encodedRoleId],
            ],
            'tickets' => [
                ['id' => 'tickets--sales', 'route' => '/sales'],
                ['id' => 'tickets--tickets-tab', 'route' => $ticketEventRoute, 'section' => 'section-tickets'],
                ['id' => 'tickets--checkin', 'route' => $checkinRoute, 'pause' => 2500],
            ],
            'gift-cards' => [
                ['id' => 'gift-cards--settings', 'route' => $venueRole ? '/demo-moestavern/edit' : null, 'section' => 'section-gift-cards'],
                ['id' => 'gift-cards--sales-tab', 'route' => '/sales?tab=gift-cards'],
            ],
            // Three appointment types are made for the pictures and removed (see handle()).
            'appointments' => [
                ['id' => 'appointments--types', 'route' => '/demo-lisajazz/appointments'],
                ['id' => 'appointments--editor', 'route' => '/demo-lisajazz/appointments?new=1'],
                ['id' => 'appointments--booking-page', 'route' => '/demo-lisajazz/book', 'public' => true, 'pause' => 2500],
            ],
            'analytics' => [
                ['id' => 'analytics--dashboard', 'route' => '/analytics', 'pause' => 3000],
                // Live traffic is invented for the picture (realtime:simulate, see handle()).
                ['id' => 'analytics--realtime', 'route' => '/analytics?tab=realtime', 'pause' => 4000],
            ],
            'account-settings' => [
                ['id' => 'account-settings--settings', 'route' => '/settings'],
                ['id' => 'account-settings--payment-methods', 'route' => '/settings#section-payment-methods', 'script' => "document.querySelectorAll('#section-payment-methods button[data-row-group][aria-expanded=\"true\"]').forEach(function (b) { b.click(); }); window.scrollTo(0, 0);"],
                ['id' => 'account-settings--security', 'route' => '/settings#section-security', 'script' => 'window.scrollTo(0, 0);'],
                ['id' => 'account-settings--developers', 'route' => '/settings#section-developers', 'script' => 'window.scrollTo(0, 0);'],
            ],
            'managing-schedules' => [
                ['id' => 'managing-schedules--schedule-tab', 'route' => '/simpsons/schedule'],
                ['id' => 'managing-schedules--videos-tab', 'route' => '/simpsons/videos'],
                // Availability is a talent schedule's tab: on the curator the address redirects to
                // the calendar, which is what this image showed until 2026-10.
                ['id' => 'managing-schedules--availability', 'route' => '/demo-lisajazz/availability', 'pause' => 2000],
                // Needs a request waiting on the schedule: with none the tab redirects to the
                // calendar and this photographs that instead (it did, until 2026-10).
                ['id' => 'managing-schedules--requests-tab', 'route' => '/simpsons/requests'],
                ['id' => 'managing-schedules--team-tab', 'route' => '/simpsons/team'],
            ],
            'scan-agenda' => [
                ['id' => 'scan-agenda--page', 'route' => '/simpsons/scan-agenda'],
            ],
            'boost' => [
                ['id' => 'boost--page', 'route' => $demoEvent
                    ? '/boost/create?event_id='.UrlUtils::encodeId($demoEvent->id).'&role_id='.$encodedRoleId
                    : '/boost'],
            ],
            'referral-program' => [
                ['id' => 'referral-link', 'route' => '/referrals'],
                ['id' => 'referral-dashboard', 'route' => '/referrals', 'script' => "document.getElementById('referral-dashboard').scrollIntoView({block: 'start'})"],
                ['id' => 'referral-credits', 'route' => '/referrals', 'script' => "(document.getElementById('referral-credits') || document.getElementById('referral-how-it-works')).scrollIntoView({block: 'start'})"],
                ['id' => 'referral-history', 'route' => '/referrals', 'script' => "(document.getElementById('referral-history') || document.getElementById('referral-how-it-works')).scrollIntoView({block: 'start'})"],
            ],
            'selfhost-admin' => [
                // ?sample=1: invented data (AdminDashboardSample). The real page lists people,
                // schedules and events by name, and leaves demo content out.
                ['id' => 'selfhost-admin--dashboard', 'route' => '/admin/dashboard?sample=1', 'pause' => 3000],
                ['id' => 'selfhost-admin--realtime', 'route' => '/admin/realtime', 'pause' => 4000],
                ['id' => 'selfhost-admin--users', 'route' => '/admin/users', 'pause' => 2000],
                ['id' => 'selfhost-admin--revenue', 'route' => '/admin/revenue', 'pause' => 2000],
                ['id' => 'selfhost-admin--analytics', 'route' => '/admin/analytics', 'pause' => 2000],
                ['id' => 'selfhost-admin--usage', 'route' => '/admin/usage', 'pause' => 2000],
                ['id' => 'selfhost-admin--boost', 'route' => '/admin/boost'],
                ['id' => 'selfhost-admin--newsletters', 'route' => '/admin/newsletters'],
                ['id' => 'selfhost-admin--audit-log', 'route' => '/admin/audit-log'],
                ['id' => 'selfhost-admin--queue', 'route' => '/admin/queue'],
                ['id' => 'selfhost-admin--logs', 'route' => '/admin/logs'],
                ['id' => 'selfhost-admin--settings', 'route' => '/admin/settings'],
            ],
        ];

        // Remove address screenshot if no venue role
        if (! $venueRole) {
            $pages['creating-schedules'] = array_values(array_filter(
                $pages['creating-schedules'],
                fn ($s) => $s['id'] !== 'creating-schedules--section-address'
            ));
        }

        return $pages;
    }

    private function generate(User $user, array $pages, bool $force, string $outputDir): int
    {
        // Start temporary server with IS_HOSTED=false and DEBUGBAR_ENABLED=false
        $port = $this->findAvailablePort();
        $this->info("Starting temporary server on port {$port}...");

        $serverProcess = $this->startServer($port);
        if (! $serverProcess) {
            $this->error('Failed to start temporary server.');

            return 1;
        }

        if (! $this->waitForServer($port)) {
            $this->error('Server failed to start within timeout.');
            $this->stopServer($serverProcess);

            return 1;
        }

        $this->info('Server ready.');

        // Start ChromeDriver on a dynamic port
        $chromePort = $this->findAvailablePort();
        $chromeProcess = (new ChromeProcess)->toProcess(["--port={$chromePort}"]);
        // The browser takes the demo account's timezone: on another clock the new-schedule form
        // opens with a notice that the device and the account disagree.
        if ($user->timezone) {
            $chromeProcess->setEnv(['TZ' => $user->timezone]);
        }
        $chromeProcess->start();

        // Wait for ChromeDriver to be ready
        if (! $this->waitForServer($chromePort)) {
            $this->error('ChromeDriver failed to start. Run: php artisan dusk:chrome-driver');
            $this->error($chromeProcess->getErrorOutput());
            $this->stopServer($serverProcess);

            return 1;
        }

        $this->info("ChromeDriver ready on port {$chromePort}.");

        $baseUrl = "http://127.0.0.1:{$port}";

        // Create WebDriver
        $options = (new ChromeOptions)->addArguments([
            '--window-size=1280,900',
            '--disable-gpu',
            '--headless=new',
            '--disable-search-engine-choice-screen',
            '--force-device-scale-factor=1',
            '--hide-scrollbars',
        ]);

        $driver = RemoteWebDriver::create(
            "http://localhost:{$chromePort}",
            DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options)
        );

        Browser::$baseUrl = $baseUrl;
        Browser::$storeScreenshotsAt = $outputDir;

        $browser = new Browser($driver);

        try {
            // Login
            $this->info('Logging in...');
            $browser->visit('/login')
                ->waitFor('#email', 10)
                ->pause(500)
                ->type('email', $user->email)
                ->type('password', self::TEMP_PASSWORD);

            $browser->script("document.querySelector('form[method=\"POST\"]').requestSubmit()");
            $browser->waitForLocation('/dashboard', 15);
            $this->info('Logged in.');

            // Complete admin password confirmation so admin pages are accessible
            if (isset($pages['selfhost-admin']) && config('app.is_testing')) {
                $this->info('Confirming admin password...');
                $browser->visit('/admin/confirm-password')
                    ->waitFor('#password', 10)
                    ->pause(500)
                    ->type('password', self::TEMP_PASSWORD);
                $browser->script("document.querySelector('form[method=\"POST\"]').requestSubmit()");
                $browser->waitForLocation('/admin/dashboard', 15);
                $this->info('Admin password confirmed.');
            }

            // Force light mode for consistent screenshots.
            //
            // Via setTheme() rather than toggling the .dark class by hand: the AP stamps BOTH
            // .dark and data-theme="<palette>" on <html>, and :root[data-theme] outspecifies
            // .dark. Setting the class alone leaves data-theme on the previous palette, so the
            // token ramp renders light while every dark: utility fires - a page that is light
            // with a handful of dark chips floating in it.
            $browser->script('window.setTheme("light")');
            $browser->pause(300);

            $generated = 0;
            $skipped = 0;

            foreach ($pages as $pageName => $screenshots) {
                $this->newLine();
                $this->info("Page: {$pageName}");

                foreach ($screenshots as $screenshot) {
                    $id = $screenshot['id'];
                    $route = $screenshot['route'] ?? null;
                    $section = $screenshot['section'] ?? null;
                    $isPublic = $screenshot['public'] ?? false;
                    $pause = $screenshot['pause'] ?? 1500;

                    if (! $route) {
                        $this->warn("  Skipping {$id} (no route)");
                        $skipped++;

                        continue;
                    }

                    $pngPath = "{$outputDir}/{$id}.png";
                    $webpPath = "{$outputDir}/{$id}.webp";
                    $darkPngPath = "{$outputDir}/{$id}-dark.png";
                    $darkWebpPath = "{$outputDir}/{$id}-dark.webp";

                    if (! $force && file_exists($pngPath) && file_exists($webpPath) && file_exists($darkPngPath) && file_exists($darkWebpPath)) {
                        $this->line("  Skipping {$id} (already exists, use --force to overwrite)");
                        $skipped++;

                        continue;
                    }

                    $this->line("  Generating {$id}...");

                    $browser->visit($route);
                    $browser->pause($pause);

                    // Hide the testing indicator dot
                    $browser->script("document.querySelector('.fixed.bottom-4.right-4.z-50')?.remove()");

                    // Execute custom script if needed (e.g. click a tab)
                    $script = $screenshot['script'] ?? null;
                    if ($script) {
                        $browser->script($script);
                        $browser->pause(800);
                    }

                    // Click section nav if needed
                    if ($section) {
                        $browser->script("document.querySelector('a[data-section=\"{$section}\"]').click()");
                        $browser->pause(800);
                    }

                    // A schedule's or an event's address, wherever a page prints it (under the
                    // title, and on the schedule form's Settings tab), is this command's own
                    // temporary server: 127.0.0.1 and a port. The guide shows it as it reads on
                    // eventschedule.com, the schedule's subdomain and then whatever follows it.
                    // Only the text is replaced, so a link keeps its icon.
                    $browser->script("document.querySelectorAll('.event-url-text, #url-display a').forEach(function (el) { var walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT); var parts = []; while (walker.nextNode()) { parts.push(walker.currentNode); } var m = parts.map(function (n) { return n.nodeValue; }).join('').trim().match(/^[^\\/]+\\/([^\\/]+)(\\/.*)?$/); if (m && parts.length) { parts[0].nodeValue = m[1] + '.eventschedule.com' + (m[2] || ''); parts.slice(1).forEach(function (n) { n.nodeValue = ''; }); } });");

                    // The same for the other places an address is printed or sits in a field (the
                    // referral link, the text beside an events graphic): 127.0.0.1:port/schedule
                    // reads as the schedule's subdomain, and the bare address as eventschedule.com.
                    $browser->script("(function () { var local = /(https?:\\/\\/)?127\\.0\\.0\\.1:\\d+/; var fix = function (t) { return t.replace(/(?:https?:\\/\\/)?127\\.0\\.0\\.1:\\d+\\/([a-z0-9-]+)((?:\\/[^\\s]*)?)/g, '\$1.eventschedule.com\$2').replace(/https?:\\/\\/127\\.0\\.0\\.1:\\d+/g, 'https://eventschedule.com'); }; var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT); var nodes = []; while (walker.nextNode()) { nodes.push(walker.currentNode); } nodes.forEach(function (n) { if (local.test(n.nodeValue) && ! n.parentElement.closest('script, style')) { n.nodeValue = fix(n.nodeValue); } }); document.querySelectorAll('input[type=text], input:not([type]), input[type=url], textarea').forEach(function (el) { if (local.test(el.value)) { el.value = fix(el.value); } }); })();");

                    // A checkout form opens filled in with whoever is signed in, which here is
                    // this command's own temporary address; the new-schedule form and the settings
                    // page print it as text.
                    $browser->script("(function () { var temp = '".self::TEMP_EMAIL."'; document.querySelectorAll('input').forEach(function (el) { if (el.value === temp) { el.value = 'alex@example.com'; } }); var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT); var nodes = []; while (walker.nextNode()) { nodes.push(walker.currentNode); } nodes.forEach(function (n) { if (n.nodeValue.indexOf(temp) !== -1 && ! n.parentElement.closest('script, style')) { n.nodeValue = n.nodeValue.split(temp).join('alex@example.com'); } }); })();");

                    // Take light screenshot (Browser stores as PNG in the storeScreenshotsAt dir)
                    $browser->screenshot($id);

                    if (file_exists($pngPath)) {
                        ImageUtils::generateWebP($pngPath, $webpPath);
                        $generated++;
                        $this->line("  Generated {$id}");
                    } else {
                        $this->warn("  Failed to generate {$id}");
                    }

                    // Take dark screenshot
                    $browser->script('window.setTheme("dark")');
                    $browser->pause(800);
                    $browser->screenshot($id.'-dark');

                    if (file_exists($darkPngPath)) {
                        ImageUtils::generateWebP($darkPngPath, $darkWebpPath);
                        $generated++;
                        $this->line("  Generated {$id}-dark");
                    } else {
                        $this->warn("  Failed to generate {$id}-dark");
                    }

                    // Restore light mode for next screenshot
                    $browser->script('window.setTheme("light")');
                    $browser->pause(300);
                }
            }

            $this->newLine();
            $this->info("Done! Generated: {$generated}, Skipped: {$skipped}");

            return 0;
        } finally {
            $browser->quit();
            $chromeProcess->stop();
            $this->stopServer($serverProcess);
        }
    }

    private function findAvailablePort(): int
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($socket, '127.0.0.1', 0);
        socket_getsockname($socket, $addr, $port);
        socket_close($socket);

        return $port;
    }

    /**
     * @return resource|false
     */
    private function startServer(int $port)
    {
        $artisan = base_path('artisan');
        $cmd = sprintf(
            'IS_HOSTED=false DEBUGBAR_ENABLED=false PHP_CLI_SERVER_WORKERS=4 php %s serve --port=%d --host=127.0.0.1 --no-reload 2>/dev/null',
            escapeshellarg($artisan),
            $port
        );

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (is_resource($process)) {
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);
            $this->serverPipes = $pipes;

            return $process;
        }

        return false;
    }

    private function waitForServer(int $port, int $timeout = 10): bool
    {
        $start = time();
        while (time() - $start < $timeout) {
            $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
            if ($connection) {
                fclose($connection);

                return true;
            }
            usleep(200000);
        }

        return false;
    }

    /**
     * @param  resource  $process
     */
    private function stopServer($process): void
    {
        foreach ($this->serverPipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }

        $status = proc_get_status($process);
        if ($status['running']) {
            $pid = $status['pid'];
            if (PHP_OS_FAMILY === 'Windows') {
                exec("taskkill /F /T /PID {$pid} 2>/dev/null");
            } else {
                exec("kill {$pid} 2>/dev/null");
            }
        }

        proc_close($process);
    }
}
