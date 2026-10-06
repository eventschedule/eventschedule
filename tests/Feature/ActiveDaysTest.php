<?php

namespace Tests\Feature;

use App\Console\Commands\PrunePersonalData;
use App\Http\Middleware\RecordActiveDay;
use App\Models\User;
use App\Services\ActiveDays;
use App\Services\DemoService;
use App\Services\PersonalDataExportService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * "Active users" is a record of who used the app on which day, not a guess off users.updated_at
 * (which calendar sync moves and a sign-in does not).
 *
 * Two things here are easy to get wrong and invisible when they are. A tab left open polls in the
 * background every 30 seconds, so "any request" would mark its owner active every day. And the
 * first weeks were seeded from the security log, which runs low: drawn as if exact, the day real
 * counting began would read as a jump in usage.
 */
class ActiveDaysTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Every test here reasons in whole days. Noon keeps a run that starts a second before
        // midnight from reading "today" on one side of it and writing on the other.
        $this->travelTo(now()->setTime(12, 0));
    }

    /** What the middleware would see for one request by $user. */
    private function request(User $user, string $method = 'GET', array $headers = []): Request
    {
        $request = Request::create('/dashboard', $method);
        $request->setLaravelSession(app('session')->driver('array'));
        $request->setUserResolver(fn () => $user);

        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        return $request;
    }

    private function page(int $status = 200, string $type = 'text/html; charset=UTF-8'): \Closure
    {
        return fn () => response('<html></html>', $status, ['Content-Type' => $type]);
    }

    private function days(User $user): int
    {
        return DB::table('user_active_days')->where('user_id', $user->id)->count();
    }

    private function mark(User $user, CarbonImmutable $day, bool $counted = true): void
    {
        DB::table('user_active_days')->insert(['user_id' => $user->id, 'date' => $day->toDateString(), 'counted' => $counted]);
    }

    /**
     * One row, and one WRITE: the session remembers that today is done, so the hundredth page of
     * the day costs nothing. The row count alone cannot show that, because the primary key keeps
     * it at one whatever is written. Mutation: drop the session mark in ActiveDays::record().
     */
    public function test_opening_a_page_records_today_once(): void
    {
        $user = $this->createOwner();
        $request = $this->request($user);

        (new RecordActiveDay)->handle($request, $this->page());

        DB::flushQueryLog();
        DB::enableQueryLog();
        (new RecordActiveDay)->handle($request, $this->page());
        $queries = array_column(DB::getQueryLog(), 'query');
        DB::disableQueryLog();

        $this->assertSame([], array_values(array_filter($queries, fn ($sql) => str_contains($sql, 'user_active_days'))));
        $this->assertSame(1, $this->days($user));
        $this->assertSame(1, DB::table('user_active_days')->where('user_id', $user->id)
            ->where('date', now()->toDateString())->where('counted', true)->count());
    }

    /**
     * The mark names the account. A session that changes hands without being invalidated must
     * not leave the second person unrecorded. Mutation: mark the session with the date alone.
     */
    public function test_the_session_mark_belongs_to_one_account(): void
    {
        $first = $this->createOwner();
        $second = $this->createOwner();

        // request() hands every request the same session, which is the case being tested.
        (new RecordActiveDay)->handle($this->request($first), $this->page());
        (new RecordActiveDay)->handle($this->request($second), $this->page());

        $this->assertSame(1, $this->days($first));
        $this->assertSame(1, $this->days($second));
    }

    /** Through the real route group, so the middleware is proven to be on it. */
    public function test_a_page_of_the_app_records_the_person_who_opened_it(): void
    {
        $user = $this->createOwner();
        $this->createRole($user);

        $this->actingAs($user)->get(route('profile.edit'))->assertOk();

        $this->assertSame(1, $this->days($user));
    }

    /**
     * Mutation: drop any one of the four conditions in RecordActiveDay::isPageView().
     */
    public function test_what_the_app_does_in_the_background_is_not_a_visit(): void
    {
        $user = $this->createOwner();
        $middleware = new RecordActiveDay;

        // The support chat poll and /admin/realtime/data answer JSON.
        $middleware->handle($this->request($user), $this->page(200, 'application/json'));
        // An HTML fragment fetched by script: the browser says it is not a document.
        $middleware->handle($this->request($user, 'GET', ['Sec-Fetch-Dest' => 'empty']), $this->page());
        // Saving a form, and the redirect that answers it.
        $middleware->handle($this->request($user, 'POST'), $this->page());
        $middleware->handle($this->request($user), $this->page(302));
        // A page the browser loaded ahead on a guess, signed in and marked as a document like
        // any other: Chrome's address bar, an old Chrome, Firefox.
        $middleware->handle($this->request($user, 'GET', ['Sec-Fetch-Dest' => 'document', 'Sec-Purpose' => 'prefetch;prerender']), $this->page());
        $middleware->handle($this->request($user, 'GET', ['Purpose' => 'prefetch']), $this->page());
        $middleware->handle($this->request($user, 'GET', ['X-Moz' => 'prefetch']), $this->page());

        $this->assertSame(0, $this->days($user));

        // A navigation says "document", and an old client says nothing at all.
        $middleware->handle($this->request($user, 'GET', ['Sec-Fetch-Dest' => 'document']), $this->page());
        $this->assertSame(1, $this->days($user));
    }

    public function test_the_demo_account_is_never_recorded(): void
    {
        $demo = User::factory()->create(['email' => DemoService::DEMO_EMAIL, 'email_verified_at' => now()]);

        (new RecordActiveDay)->handle($this->request($demo), $this->page());

        $this->assertSame(0, $this->days($demo));
    }

    /** Mutation: count rows instead of distinct people, or drop the verified join. */
    public function test_the_seven_day_figure_is_distinct_verified_people(): void
    {
        $today = CarbonImmutable::now()->startOfDay();
        $organizer = $this->createOwner();
        $buyer = User::factory()->create(['email_verified_at' => now(), 'signup_intent' => 'ticket']);
        $unverified = User::factory()->create(['email_verified_at' => null]);
        $lastMonth = $this->createOwner();

        // The same person on three days is one person.
        $this->mark($organizer, $today);
        $this->mark($organizer, $today->subDays(2));
        $this->mark($organizer, $today->subDays(6));
        $this->mark($buyer, $today->subDay());
        $this->mark($unverified, $today);
        // Seven days ago is outside a window of seven days that includes today.
        $this->mark($lastMonth, $today->subDays(7));

        $stats = ActiveDays::stats();

        $this->assertSame(2, $stats['active_7d']);
        $this->assertSame(1, $stats['organizers_7d']);
        $this->assertSame(3, $stats['active_30d']);
    }

    /**
     * A day is exact once its whole week lies after the first recorded day, which is itself a part
     * day. Until both weeks are exact the change is withheld: an exact week against an estimate
     * would read as growth. Mutation: compare whenever a previous figure exists.
     */
    public function test_an_estimate_is_never_compared_with_an_exact_count(): void
    {
        $today = CarbonImmutable::now()->startOfDay();
        $user = $this->createOwner();

        // Seeded from the security log for three weeks, then recorded for real from 10 days ago.
        for ($back = 30; $back > 10; $back--) {
            $this->mark($user, $today->subDays($back), false);
        }
        for ($back = 10; $back >= 0; $back--) {
            $this->mark($user, $today->subDays($back));
        }

        $stats = ActiveDays::stats();
        $exact = collect($stats['series'])->pluck('exact', 'date');

        // First recorded day is 10 days back; exact from 7 days after it, 3 days ago.
        $this->assertSame($today->subDays(3)->toDateString(), $stats['exact_from']);
        $this->assertFalse($exact[$today->subDays(4)->toDateString()]);
        $this->assertTrue($exact[$today->subDays(3)->toDateString()]);
        $this->assertTrue($stats['exact']);
        // The week before last was still an estimate, so there is nothing honest to compare with.
        $this->assertNull($stats['change']);
        // And thirty days reach back into the seeded days long after seven have stopped.
        $this->assertFalse($stats['exact_30d']);

        $this->travelTo($today->addDays(8)->addHours(9));
        $this->mark($user, $today->addDays(8));

        $this->assertNotNull(ActiveDays::stats()['change']);
    }

    /**
     * The change is measured between finished days: the seven to yesterday against the seven
     * before those. Today so far against a whole week read as a fall every morning, because the
     * record holds dates and cannot be cut at the hour of the look.
     *
     * One person here was active on a single day, a week ago: inside the finished week, outside
     * the seven days that end today. Mutation: compare today's live figure with the stored one of
     * seven days ago, and this reads -50% where it is +100%.
     */
    public function test_the_change_is_measured_between_finished_weeks(): void
    {
        $today = CarbonImmutable::now()->startOfDay();
        $regular = $this->createOwner();
        $once = $this->createOwner();

        // Counting began three weeks ago, so every window below is exact. Nothing yet today.
        for ($back = 20; $back >= 1; $back--) {
            $this->mark($regular, $today->subDays($back));
        }
        $this->mark($once, $today->subDays(7));

        $stats = ActiveDays::stats();

        // To yesterday: both of them. The seven before: the regular alone.
        $this->assertSame(100.0, $stats['change']);
        // The headline is live, and the visit a week ago has left its window.
        $this->assertSame(1, $stats['active_7d']);
    }

    /**
     * Until the tables exist the page says so and nothing fails: a selfhost can run this code
     * before it migrates, and so can the scheduler's container during a deploy. The memo is what
     * every path asks, so a test can answer for it without dropping a table (which would commit
     * the test's transaction). Mutation: let stats() or record() query without asking.
     */
    public function test_nothing_fails_before_the_tables_exist(): void
    {
        $user = $this->createOwner();
        (new \ReflectionProperty(ActiveDays::class, 'tableExists'))->setValue(null, false);

        (new RecordActiveDay)->handle($this->request($user), $this->page());
        $stats = ActiveDays::stats();

        $this->assertFalse($stats['available']);
        $this->assertSame(0, $stats['active_7d']);
        $this->assertSame([], $stats['series']);
        $this->assertSame(0, ActiveDays::snapshot());
        $this->assertSame(0, ActiveDays::prune(120, 90));

        ActiveDays::flush();
        $this->assertSame(0, $this->days($user));
    }

    /**
     * The migration can be run again after a run that died while seeding, which leaves both
     * tables behind and the migration unrecorded. And the seed takes a sign-in or an edit made in
     * a browser as a day of use, never one made by the API, a webhook or a scheduled job.
     * Mutation: drop a Schema::hasTable() guard from up(), or seed every edit not made by "Symfony".
     */
    public function test_the_migration_can_be_run_again_and_seeds_only_what_a_person_did(): void
    {
        $signedIn = $this->createOwner();
        $edited = $this->createOwner();
        $viaApi = $this->createOwner();
        $imported = $this->createOwner();
        $longAgo = $this->createOwner();

        $log = fn (User $user, string $action, ?string $agent, int $daysAgo = 2) => DB::table('audit_logs')->insert([
            'user_id' => $user->id, 'action' => $action, 'ip_address' => '203.0.113.9',
            'user_agent' => $agent, 'created_at' => now()->subDays($daysAgo),
        ]);
        $browser = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15';

        $log($signedIn, 'auth.login', $browser);
        $log($edited, 'event.update', $browser);
        $log($viaApi, 'event.create', 'python-requests/2.31');
        $log($imported, 'event.create', 'Symfony');
        $log($longAgo, 'auth.login', $browser, 100);

        $migration = require database_path('migrations/2026_10_06_000002_create_user_active_days_table.php');
        $migration->up();
        $migration->up();

        $seeded = DB::table('user_active_days')->where('counted', false)->pluck('user_id')->all();
        sort($seeded);

        $this->assertSame([$signedIn->id, $edited->id], $seeded);
    }

    /**
     * The rows are pruned; the daily totals, which name nobody, are what remains. Mutation: prune
     * before the snapshot, or prune the seeded rows on the long clock.
     */
    public function test_the_totals_outlive_the_rows(): void
    {
        $today = CarbonImmutable::now()->startOfDay();
        $user = $this->createOwner();

        $old = $today->subDays(PrunePersonalData::ACTIVE_DAY_DAYS + 5);
        $seeded = $today->subDays(PrunePersonalData::SEEDED_ACTIVE_DAY_DAYS + 5);

        foreach (range(0, 9) as $offset) {
            $this->mark($user, $old->addDays($offset));
        }
        $this->mark($user, $seeded, false);
        $this->mark($user, $today->subDays(100));
        $this->mark($user, $today);

        $this->artisan('app:prune-personal-data')->assertSuccessful();

        // Past 120 days, and the seeded row past 90, are gone; the recorded row at 100 days stays.
        $this->assertSame(0, DB::table('user_active_days')->where('date', '<', $today->subDays(PrunePersonalData::ACTIVE_DAY_DAYS)->toDateString())->count());
        $this->assertSame(0, DB::table('user_active_days')->where('date', $seeded->toDateString())->count());
        $this->assertSame(1, DB::table('user_active_days')->where('date', $today->subDays(100)->toDateString())->count());

        // A day whose week was mostly pruned a moment later still has the figure it had. Prune
        // first and this day is never written: there is no week of rows left to draw it from.
        $kept = DB::table('active_users_daily')->where('date', $old->addDays(7)->toDateString())->first();
        $this->assertNotNull($kept);
        $this->assertSame(1, (int) $kept->active_7d);
        $this->assertSame(1, (int) $kept->counted);
    }

    public function test_the_export_lists_the_days_and_deleting_the_account_removes_them(): void
    {
        $user = $this->createOwner();
        $this->mark($user, CarbonImmutable::now()->startOfDay());

        $export = app(PersonalDataExportService::class)->build($user);
        $this->assertCount(1, $export['days_active']);

        $user->delete();
        $this->assertSame(0, DB::table('user_active_days')->count());
    }
}
