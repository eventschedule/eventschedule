<?php

namespace Tests\Feature;

use App\Models\FederatedInstance;
use App\Models\User;
use App\Services\ActiveDays;
use App\Services\AdminDashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * /admin/dashboard as a page: the shell that is sent at once, the three panels of cards it fetches
 * as they near the screen (AdminDashboardController::PANELS), the Revenue card in its corner,
 * which cards each kind of install gets, what a new install sees, and what each request costs.
 *
 * The live part of the page is AdminRealtimeTest's. What each number means is held by the tests of
 * the thing that counts it (AdminDashboardSignupsTest, AdminDashboardEventsTest,
 * AdminDashboardRecentTest, AdminDashboardFederationTest, ActiveDaysTest, RecurringRevenueTest).
 * AdminInstallKindTest and AdminAlertsTest read this page too.
 */
class AdminDashboardPageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * The old page ran about a hundred queries for every card at once. A panel is a third of the
     * cards: the ceiling is there to catch a query per row, not to forbid another card.
     */
    private const QUERY_CEILING = 50;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createOwner(true);
    }

    private function fetch(string $path): TestResponse
    {
        // EnsureUserIsAdmin gates every /admin route on a confirmed password this session.
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->admin)
            ->get($path);
    }

    private function page(string $query = ''): TestResponse
    {
        return $this->fetch('/admin/dashboard'.$query)->assertOk();
    }

    private function panel(string $panel, string $query = ''): TestResponse
    {
        return $this->fetch('/admin/dashboard/panels/'.$panel.$query)->assertOk();
    }

    /** Every panel's HTML, in the page's order. */
    private function cards(string $query = ''): string
    {
        return collect(['recent', 'growth', 'platform'])->map(fn ($panel) => $this->panel($panel, $query)->getContent())->implode("\n");
    }

    private function revenue(string $query = ''): TestResponse
    {
        return $this->fetch('/admin/dashboard/revenue'.$query);
    }

    private function seedSomething(string $schedule = 'The Room'): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => $schedule]);
        $this->createEvent($role, ['name' => 'Opening Night']);
    }

    /**
     * The page is sent before any card is built: each panel, and Revenue, is its own request.
     * Mutation: build the cards in index() again.
     */
    public function test_the_page_builds_no_card_and_names_the_three_panels(): void
    {
        config(['app.hosted' => true, 'app.is_nexus' => true]);
        $this->seedSomething();

        $this->app->bind(AdminDashboard::class, fn () => throw new \RuntimeException('the page built a card'));

        $html = $this->page()->getContent();
        foreach (['recent', 'growth', 'platform'] as $panel) {
            $this->assertStringContainsString('data-dash-panel="'.$panel.'"', $html);
        }
        $this->assertStringContainsString('data-panel-url="'.route('admin.dashboard.panel', ['panel' => '__panel__']).'"', $html);
        $this->assertStringNotContainsString('The Room', $html);

        $this->fetch('/admin/dashboard/panels/nope')->assertNotFound();
    }

    /**
     * The panels are fetched with the headers the page's script sends. Asked as a page, a lapsed
     * password confirmation answered with a redirect to the form, which fetch followed and the
     * script put where the panel goes. Mutation: send Accept: text/html from the script.
     */
    public function test_a_lapsed_password_confirmation_answers_a_panel_with_423(): void
    {
        $script = file_get_contents(resource_path('views/admin/dashboard/_cards-script.blade.php'));
        $this->assertStringContainsString("'Accept': '*/*'", $script);

        $this->actingAs($this->admin)
            ->get('/admin/dashboard/panels/recent', ['Accept' => '*/*', 'X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(423);
    }

    public function test_a_non_admin_gets_no_panel_and_no_revenue(): void
    {
        config(['app.hosted' => true]);
        $owner = $this->createOwner();

        // The admin middleware sends a page request away, and refuses one the script makes.
        $this->actingAs($owner)->get('/admin/dashboard/panels/recent')->assertRedirect();
        $this->actingAs($owner)->getJson('/admin/dashboard/panels/recent')->assertStatus(403);
        $this->actingAs($owner)->getJson('/admin/dashboard/revenue')->assertStatus(403);
    }

    /**
     * No schedule, no event and nobody but the person looking: one card, not a column of cards
     * each saying "nothing yet". The admin's own account is a sign-up from today, which is why
     * "no sign-ups" is not the test. Mutation: require an empty sign-up window.
     */
    public function test_a_new_install_gets_one_card(): void
    {
        config(['app.hosted' => false, 'app.is_nexus' => false]);

        $this->panel('recent')
            ->assertViewHas('dashboard', fn (array $dashboard) => $dashboard['firstRun'] === true)
            ->assertSee('data-first-run', false)
            ->assertSee(__('messages.admin_dash_first_run_title'))
            ->assertDontSee(__('messages.admin_dash_recent_schedules'));

        $this->seedSomething();

        $this->panel('recent')
            ->assertViewHas('dashboard', fn (array $dashboard) => $dashboard['firstRun'] === false)
            ->assertDontSee('data-first-run', false)
            ->assertDontSee(__('messages.admin_dash_first_run_title'))
            ->assertSee(__('messages.admin_dash_recent_schedules'))
            ->assertSee('The Room')
            ->assertSee('Opening Night');
    }

    /**
     * Revenue where there is billing, in the corner; the hub's numbers on eventschedule.com and
     * this install's own sharing anywhere else. Both halves of each, so a gate that hides a card
     * everywhere fails too.
     */
    public function test_each_kind_of_install_gets_the_cards_it_can_act_on(): void
    {
        $this->seedSomething();

        // The hub's card has figures only once an install has registered with it.
        (new FederatedInstance)->forceFill([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'name' => 'Operator',
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_APPROVED,
        ])->save();

        config(['app.hosted' => false, 'app.is_nexus' => false]);
        $corner = '/<div class="realtime-top[^"]*"\s+data-corner/';
        $this->assertDoesNotMatchRegularExpression($corner, $this->page()->assertDontSee('id="dash-revenue"', false)->getContent());
        $this->revenue()->assertNotFound();
        $this->panel('platform')
            ->assertSee(__('messages.admin_dash_fed_off'))
            ->assertDontSee(__('messages.admin_dash_clicks_sent'));

        config(['app.hosted' => true, 'app.is_nexus' => false]);
        $this->assertMatchesRegularExpression($corner, $this->page()->assertSee('id="dash-revenue"', false)->getContent());
        $this->revenue()->assertOk()->assertJsonPath('failed', false);
        $this->panel('platform')
            ->assertSee(__('messages.admin_dash_fed_off'))
            ->assertDontSee(__('messages.admin_dash_clicks_sent'));

        config(['app.hosted' => true, 'app.is_nexus' => true]);
        $this->panel('platform')
            ->assertDontSee(__('messages.admin_dash_fed_off'))
            ->assertSee(__('messages.admin_dash_clicks_sent'));
    }

    /**
     * The windows are fixed, so the date-range select of the other admin pages is not on this one
     * and a ?range= left in a bookmark changes nothing. Mutation: read the range again.
     */
    public function test_the_windows_are_fixed(): void
    {
        config(['app.hosted' => true, 'app.is_nexus' => true]);
        $this->seedSomething();

        foreach (['', '?range=last_7_days', '?range=all_time'] as $query) {
            $this->panel('growth', $query)
                ->assertViewHas('dashboard', fn (array $dashboard) => count($dashboard['signups']['days']) === 30)
                ->assertDontSee('id="date-range"', false)
                // The label this figure has always had on the page.
                ->assertSee(__('messages.active_users_7_days'));
        }
    }

    /**
     * ?sample=1 renders invented data for the documentation screenshot, which must not publish a
     * developer's real schedules and people. It is for local and test installs: in production
     * the parameter does nothing, on the page (which passes it on to the panels) or the panels.
     * Mutation: drop the environment test.
     */
    public function test_sample_data_is_never_served_in_production(): void
    {
        config(['app.hosted' => true, 'app.is_nexus' => true]);
        $this->seedSomething('Zebra Crossing Hall');
        $sampleSchedule = AdminDashboard::sample()['schedules']['rows'][0]['name'];

        $sampled = '/id="admin-dashboard"[^>]*\sdata-sample[\s>]/';
        $this->assertMatchesRegularExpression($sampled, $this->page('?sample=1')->getContent());
        $this->panel('recent', '?sample=1')->assertSee($sampleSchedule)->assertDontSee('Zebra Crossing Hall');
        $this->panel('recent')->assertDontSee($sampleSchedule)->assertSee('Zebra Crossing Hall');
        $this->assertSame(plan_price(AdminDashboard::sample()['revenue']['totals']['mrr']), $this->revenue('?sample=1')->json('mrr'));

        $this->app['env'] = 'production';

        $this->assertDoesNotMatchRegularExpression($sampled, $this->page('?sample=1')->getContent());
        $this->panel('recent', '?sample=1')->assertDontSee($sampleSchedule)->assertSee('Zebra Crossing Hall');
    }

    /**
     * Each panel's cards, and then the panel that prints them, each asked twice: once with a few
     * rows and once with four times as many. Mutation: read an owner, a subscription or a flyer
     * per row, in AdminDashboard or in any partial.
     */
    public function test_each_panel_costs_the_same_however_much_there_is_to_show(): void
    {
        config(['app.hosted' => true, 'app.is_nexus' => true]);

        $seed = function (int $count): void {
            foreach (range(1, $count) as $index) {
                $owner = User::factory()->create(['email_verified_at' => now(), 'landing_page' => 'pricing']);
                $role = $this->createRole($owner, $index % 2 ? 'venue' : 'talent', ['country_code' => 'us']);
                $this->createTicket($this->createEvent($role, ['creator_role_id' => $role->id]));
            }
        };
        $count = function (callable $run): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $run();
            $count = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $count;
        };
        $measure = fn () => [
            $count(fn () => (new AdminDashboard)->build()),
            $count(fn () => $this->panel('recent')),
            $count(fn () => $this->panel('growth')),
            $count(fn () => $this->panel('platform')),
            $count(fn () => $this->revenue()->assertOk()),
        ];

        $seed(3);
        // Once, unmeasured: what is looked up once per process or per session (whether a table
        // exists, the platform currency, the alerts, today's active-day mark) is paid for here
        // and not counted against either side.
        $measure();
        $few = $measure();

        $seed(9);
        $many = $measure();

        $this->assertSame($few, $many);
        $this->assertLessThanOrEqual(self::QUERY_CEILING, $many[0]);
    }

    /**
     * Each panel says how long each of its cards took, in a Server-Timing header, and builds no
     * card of another panel. Production's database cannot be reached from a developer's machine,
     * so the browser's network panel is where a slow card is found. Mutation: build a card outside
     * card(), drop the header, or build every card for every panel.
     */
    public function test_each_panel_says_how_long_each_card_took_and_builds_only_its_own(): void
    {
        config(['app.hosted' => true, 'app.is_nexus' => true]);
        $this->seedSomething();

        $panels = [
            'recent' => ['schedules', 'recentEvents', 'eventCounts'],
            'growth' => ['signups', 'active', 'events'],
            'platform' => ['federation', 'system'],
        ];
        $all = ['signups', 'active', 'revenue', 'events', 'eventCounts', 'federation', 'schedules', 'recentEvents', 'system'];

        foreach ($panels as $panel => $cards) {
            $header = (string) $this->panel($panel)->headers->get('Server-Timing');
            foreach ($all as $name) {
                $pattern = '/(^|, )'.$name.';dur=\d+(\.\d+)?(,|$)/';
                in_array($name, $cards, true)
                    ? $this->assertMatchesRegularExpression($pattern, $header, "{$panel}: {$name}")
                    : $this->assertDoesNotMatchRegularExpression($pattern, $header, "{$panel} built {$name}");
            }
        }

        $this->assertMatchesRegularExpression('/^revenue;dur=/', (string) $this->revenue()->headers->get('Server-Timing'));

        // Invented data is not timed: nothing was read to make it.
        $this->assertFalse($this->panel('recent', '?sample=1')->headers->has('Server-Timing'));
    }

    /**
     * A card whose query failed costs that card and nothing else: build() reports it and hands
     * the panel null in its place. Every card in turn, so a partial that reads a figure without
     * asking whether its card is there fails here. Revenue says it failed rather than showing a
     * zero. Mutation: read a failed card's figure anyway.
     */
    public function test_a_card_that_failed_costs_one_card(): void
    {
        config([
            'app.hosted' => true, 'app.is_nexus' => true,
            'services.stripe_platform.price_monthly' => 'price_pro_m',
            'services.stripe_platform.price_monthly_amount' => '5',
        ]);
        $this->seedSomething();
        $paying = $this->createRole($this->createOwner(), 'venue', ['plan_type' => 'free', 'plan_expires' => null]);
        $paying->subscriptions()->create([
            'type' => 'default', 'stripe_id' => 'sub_'.Str::random(14), 'stripe_status' => 'active',
            'stripe_price' => 'price_pro_m', 'quantity' => 1,
        ]);

        $this->assertStringNotContainsString(__('messages.admin_dash_card_failed'), $this->cards());
        $this->revenue()->assertJsonPath('failed', false)->assertJsonPath('arr_value', 60);

        // A card of the same panel that does not read the failed one.
        $siblings = [
            'signups' => ['dash-active', 'dash-upcoming'], 'active' => ['dash-signups', 'dash-upcoming'],
            'events' => ['dash-signups', 'dash-active'], 'federation' => [], 'system' => [],
            'schedules' => ['dash-recent-events'], 'recentEvents' => ['dash-recent'],
            'eventCounts' => ['dash-recent', 'dash-recent-events'],
        ];
        $panelOf = ['signups' => 'growth', 'active' => 'growth', 'events' => 'growth', 'federation' => 'platform', 'schedules' => 'recent', 'recentEvents' => 'recent', 'eventCounts' => 'recent', 'system' => 'platform'];

        foreach (['signups', 'active', 'revenue', 'events', 'eventCounts', 'federation', 'schedules', 'recentEvents', 'system'] as $card) {
            $this->app->bind(AdminDashboard::class, fn () => new class($card) extends AdminDashboard
            {
                public function __construct(private string $failed)
                {
                    parent::__construct();
                }

                public function build(?array $only = null): array
                {
                    $data = parent::build($only);

                    return array_key_exists($this->failed, $data) ? [$this->failed => null] + $data : $data;
                }
            });

            if ($card === 'revenue') {
                $this->revenue()->assertOk()->assertExactJson(['failed' => true]);

                continue;
            }

            $html = $this->panel($panelOf[$card])->getContent();
            // The foot of the page is one quiet line with nothing to put a notice in, and the two
            // event totals are a line in the Recent events header, which leaves them out.
            if (! in_array($card, ['system', 'eventCounts'], true)) {
                $this->assertStringContainsString(__('messages.admin_dash_card_failed'), $html, $card);
            }
            // The cards beside it in the same panel still stand.
            foreach ($siblings[$card] as $id) {
                $this->assertStringContainsString('id="'.$id.'"', $html, "{$card} took {$id} with it");
            }
        }
    }

    /**
     * /admin/users shows the same record, and says "Estimate" while the window still reaches back
     * before counting began. Before the tables exist, both pages say nothing of the kind and the
     * dashboard tells the operator to migrate. Mutation: print the figure bare on /admin/users.
     */
    public function test_an_estimate_is_labelled_and_a_missing_record_is_not_an_estimate(): void
    {
        config(['app.hosted' => false, 'app.is_nexus' => false]);
        $this->seedSomething();
        DB::table('user_active_days')->insert(['user_id' => $this->admin->id, 'date' => now()->toDateString(), 'counted' => true]);

        $users = fn () => $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->admin)->get('/admin/users')->assertOk();

        // Counting began today, so both windows are mostly days nobody recorded.
        $users()->assertSee(__('messages.admin_dash_estimate'));

        (new \ReflectionProperty(ActiveDays::class, 'tableExists'))->setValue(null, false);

        $users()->assertDontSee(__('messages.admin_dash_estimate'));
        $this->panel('growth')->assertSee(__('messages.admin_dash_active_unavailable'));

        ActiveDays::flush();
    }

    /**
     * The admin panel's Help link opens this page's own section of the guide, and that section is
     * there. Mutation: drop the admin/dashboard line from HelpUtils, or rename the section.
     */
    public function test_the_help_link_opens_the_dashboard_section_of_the_guide(): void
    {
        $this->page()->assertSee('/docs/selfhost/admin#dashboard', false);

        $this->assertStringContainsString(
            '<section id="dashboard"',
            file_get_contents(resource_path('views/marketing/docs/selfhost/admin.blade.php'))
        );
    }
}
