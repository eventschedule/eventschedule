<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Utils\RealtimeTracker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Fake /admin/realtime traffic for building the page and taking doc screenshots. Local and testing
 * only.
 *
 * It exists because nothing headless can create real rows: HeadlessChrome is a bot to
 * PageView::isBot(), so Dusk, the screenshot generator and a headless visual check all send beacons
 * that are dropped, and one private window cannot show the row caps, a spike, or a stuck new user.
 * Rows are timed relative to now, so run it again when "right now" goes stale (2.5 minutes).
 */
class SimulateRealtimeTraffic extends Command
{
    protected $signature = 'realtime:simulate
        {--visitors=40 : Visitors in the last 30 minutes who accepted cookies}
        {--signed-in=5 : How many of them are signed-in (fake) users}
        {--unidentified=60 : Count-only page views from visitors who did not accept cookies}
        {--spike : Put most of them on one schedule page}
        {--purge : Remove every realtime row and the fake users, then stop}';

    protected $description = 'Insert fake realtime visitors for local development (local and testing only)';

    private const EMAIL_DOMAIN = 'realtime-sim.example.test';

    private const MARKETING = [
        ['/', 'The simple way to share your event schedule'], ['/pricing', 'Pricing'], ['/features', 'Features'],
        ['/docs/getting-started', 'Getting started'], ['/for-venues', 'For venues'], ['/blog', 'Blog'],
    ];

    private const APP = [['/dashboard', 'Dashboard'], ['/new/venue', 'New > Venue'], ['/getting-started', 'Getting Started']];

    private const SOURCES = [
        ['search', 'google.com'], ['social', 'instagram.com'], ['ai', 'chatgpt.com'], ['direct', null],
        ['email', 'newsletter'], ['other', 'news.ycombinator.com'], ['social', 't.co'], ['direct', null],
    ];

    private const COUNTRIES = ['US', 'US', 'GB', 'DE', 'IL', 'FR', 'NL', 'CA', 'AU', 'RO', 'ES', 'IT'];

    private const DEVICES = [['mobile', 'Safari', 'iOS'], ['desktop', 'Chrome', 'macOS'], ['desktop', 'Edge', 'Windows'], ['mobile', 'Chrome', 'Android'], ['tablet', 'Safari', 'iOS']];

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing']) && ! config('app.is_testing')) {
            $this->error('realtime:simulate only runs in local or testing.');

            return Command::FAILURE;
        }

        if ($this->option('purge')) {
            DB::table('realtime_hits')->delete();
            User::where('email', 'like', '%@'.self::EMAIL_DOMAIN)->get()->each->delete();
            $this->info('Removed every realtime row and the simulated users.');

            return Command::SUCCESS;
        }

        $now = RealtimeTracker::now();
        $roles = Role::where('is_deleted', false)->whereNotNull('email_verified_at')->inRandomOrder()->limit(8)->get(['id', 'name']);
        $users = $this->users((int) $this->option('signed-in'));
        $rows = [];

        for ($i = 0; $i < (int) $this->option('visitors'); $i++) {
            $visitorKey = Str::lower(Str::random(16));
            $visitorKey = substr(hash('sha256', $visitorKey), 0, 16);
            $user = $users[$i] ?? null;
            [$device, $browser, $os] = self::DEVICES[array_rand(self::DEVICES)];
            [$channel, $source] = self::SOURCES[array_rand(self::SOURCES)];
            $country = self::COUNTRIES[array_rand(self::COUNTRIES)];
            $isNow = $i % 3 !== 2;
            $pages = random_int(1, 5);
            $start = $now->subSeconds(random_int(60, 1700));

            for ($page = 0; $page < $pages; $page++) {
                $last = $page === $pages - 1;
                $started = $start->addSeconds($page * random_int(30, 200));
                if ($started->gt($now)) {
                    $started = $now->subSeconds(10);
                }
                $lastSeen = $last && $isNow ? $now->subSeconds(random_int(5, 50)) : $started->addSeconds(random_int(5, 120));
                if ($lastSeen->gt($now)) {
                    $lastSeen = $now;
                }

                $rows[] = $this->row([
                    'visitor_key' => $visitorKey,
                    'consented' => true,
                    // Four in five accepted cookies after the banner named the organizer, so a
                    // schedule's own Realtime page (/realtime) has both kinds to show: people it
                    // may list, and people it may only count.
                    'owner_visible' => $i % 5 !== 0,
                    'user_id' => $user && ($page > 0 || $pages === 1) ? $user->id : null,
                    'country' => $country,
                    'device' => $device,
                    'browser' => $browser,
                    'os' => $os,
                    'source_channel' => $channel,
                    'source_name' => $source,
                    'is_entrance' => $page === 0,
                    'started_at' => $started,
                    'last_seen_at' => $lastSeen,
                    'ended_at' => $last && $isNow ? null : $lastSeen,
                ] + $this->page($user && $page > 0, $roles, $page === 0 && $this->option('spike') && $i % 5 !== 0 ? $roles->first() : null));
            }
        }

        for ($i = 0; $i < (int) $this->option('unidentified'); $i++) {
            [$channel, $source] = self::SOURCES[array_rand(self::SOURCES)];
            $started = $now->subSeconds(random_int(5, 1790));
            $rows[] = $this->row([
                'consented' => false,
                'country' => self::COUNTRIES[array_rand(self::COUNTRIES)],
                'device' => self::DEVICES[array_rand(self::DEVICES)][0],
                'source_channel' => $i % 2 ? $channel : null,
                'source_name' => $i % 2 ? $source : null,
                'is_entrance' => (bool) ($i % 2),
                'started_at' => $started,
                'last_seen_at' => $started,
            ] + $this->page(false, $roles, $this->option('spike') && $i % 3 ? $roles->first() : null));
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('realtime_hits')->insert($chunk);
        }

        $this->info('Inserted '.count($rows).' realtime page views. They go stale as "right now" in about 2 minutes.');

        return Command::SUCCESS;
    }

    private function users(int $count): array
    {
        $users = [];
        $names = ['Dana Levi', 'Ari Cohen', 'Maya Rossi', 'Tom Becker', 'Lea Martin', 'Sam Okafor', 'Nina Petrova', 'Omar Haddad'];

        for ($i = 0; $i < $count; $i++) {
            $email = 'user'.($i + 1).'@'.self::EMAIL_DOMAIN;
            $user = User::where('email', $email)->first() ?? User::forceCreate([
                'name' => $names[$i % count($names)],
                'email' => $email,
                'password' => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
                'signup_intent' => 'organizer',
            ]);
            $users[] = $user;
        }

        return $users;
    }

    private function page(bool $signedIn, $roles, ?Role $spikeRole): array
    {
        if ($spikeRole) {
            return ['surface' => 'gp', 'path' => '/', 'role_id' => $spikeRole->id];
        }

        if ($signedIn) {
            [$path, $title] = self::APP[array_rand(self::APP)];

            return ['surface' => 'ap', 'path' => $path, 'title' => $title];
        }

        $roll = random_int(1, 10);
        if ($roll <= 4 && $roles->isNotEmpty()) {
            return ['surface' => 'gp', 'path' => '/', 'role_id' => $roles->random()->id];
        }
        if ($roll === 5) {
            return ['surface' => 'auth', 'path' => random_int(0, 1) ? '/sign_up' : '/login'];
        }

        [$path, $title] = self::MARKETING[array_rand(self::MARKETING)];

        return ['surface' => config('app.is_nexus') ? 'wp' : 'gp', 'path' => $path, 'title' => $title];
    }

    private function row(array $values): array
    {
        $row = array_merge([
            'hit_key' => bin2hex(random_bytes(16)),
            'visitor_key' => null,
            'consented' => false,
            'owner_visible' => false,
            'user_id' => null,
            'is_admin' => false,
            'is_demo' => false,
            'surface' => 'wp',
            'path' => '/',
            'title' => null,
            'role_id' => null,
            'event_id' => null,
            'source_channel' => null,
            'source_name' => null,
            'utm_campaign' => null,
            'is_entrance' => false,
            'country' => null,
            'device' => 'desktop',
            'browser' => null,
            'os' => null,
            'hb' => 60,
            'ended_at' => null,
        ], $values);

        foreach (['started_at', 'last_seen_at', 'ended_at'] as $column) {
            if ($row[$column] instanceof \DateTimeInterface) {
                $row[$column] = RealtimeTracker::ts($row[$column]);
            }
        }
        $row['engaged_at'] = $row['started_at'];

        return $row;
    }
}
