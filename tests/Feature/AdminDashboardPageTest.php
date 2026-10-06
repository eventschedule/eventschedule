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
 * /admin/dashboard as a page: which cards each kind of install gets, what a new install sees, and
 * what the page costs to load.
 *
 * What each number means is held by the tests of the thing that counts it (AdminDashboardSignupsTest,
 * AdminDashboardEventsTest, AdminDashboardRecentTest, AdminDashboardFederationTest, ActiveDaysTest,
 * RecurringRevenueTest). AdminInstallKindTest, AdminAlertsTest and BoostMarkupCurrencyTest read
 * this page too.
 */
class AdminDashboardPageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * The old page ran about a hundred queries and this one about forty. The ceiling sits between
     * the two: it is there to catch a query per row, not to forbid another card.
     */
    private const QUERY_CEILING = 50;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createOwner(true);
    }

    private function page(string $query = ''): TestResponse
    {
        // EnsureUserIsAdmin gates every /admin route on a confirmed password this session.
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->admin)
            ->get('/admin/dashboard'.$query)
            ->assertOk();
    }

    private function seedSomething(string $schedule = 'The Room'): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => $schedule]);
        $this->createEvent($role, ['name' => 'Opening Night']);
    }

    /**
     * No schedule, no event and nobody but the person looking: the numbers and one card, not a
     * column of cards each saying "nothing yet". The admin's own account is a sign-up from today,
     * which is why "no sign-ups" is not the test. Mutation: require an empty sign-up window.
     */
    public function test_a_new_install_gets_the_numbers_and_one_card(): void
    {
        config(['app.hosted' => false, 'app.is_nexus' => false]);

        $this->page()
            ->assertViewHas('dashboard', fn (array $dashboard) => $dashboard['firstRun'] === true)
            ->assertSee(__('messages.admin_dash_first_run_title'))
            ->assertSee(__('messages.admin_dash_new_organizers'))
            ->assertDontSee(__('messages.admin_dash_recent_schedules'))
            ->assertDontSee(__('messages.admin_dash_latest_signups'))
            // The cards a tile would jump to are not on this page, so no tile is a link.
            ->assertDontSee('href="#dash-', false);

        $this->seedSomething();

        $this->page()
            ->assertViewHas('dashboard', fn (array $dashboard) => $dashboard['firstRun'] === false)
            ->assertDontSee(__('messages.admin_dash_first_run_title'))
            ->assertSee(__('messages.admin_dash_recent_schedules'))
            ->assertSee('The Room')
            ->assertSee('Opening Night');
    }

    /**
     * Revenue where there is billing; the hub's numbers on eventschedule.com and this install's
     * own sharing anywhere else. Both halves of each, so a gate that hides a card everywhere
     * fails too.
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
        $this->page()
            ->assertSee(__('messages.admin_dash_events_added'))
            ->assertDontSee(__('messages.admin_dash_outside_stripe'))
            ->assertSee(__('messages.admin_dash_fed_off'))
            ->assertDontSee(__('messages.admin_dash_clicks_sent'));

        config(['app.hosted' => true, 'app.is_nexus' => false]);
        $this->page()
            ->assertDontSee(__('messages.admin_dash_events_added'))
            ->assertSee(__('messages.admin_dash_outside_stripe'))
            ->assertSee(__('messages.admin_dash_fed_off'))
            ->assertDontSee(__('messages.admin_dash_clicks_sent'));

        config(['app.hosted' => true, 'app.is_nexus' => true]);
        $this->page()
            ->assertSee(__('messages.admin_dash_outside_stripe'))
            ->assertDontSee(__('messages.admin_dash_fed_off'))
            ->assertSee(__('messages.admin_dash_clicks_sent'));
    }

    /**
     * Each headline number jumps to the card that explains it. Mutation: rename a card's id, or
     * leave a tile pointing at a card this kind of install does not get.
     */
    public function test_every_headline_number_leads_to_a_card_on_the_page(): void
    {
        $this->seedSomething();

        foreach ([true, false] as $hosted) {
            config(['app.hosted' => $hosted, 'app.is_nexus' => false]);

            $html = $this->page()->getContent();
            preg_match_all('/href="#(dash-[a-z-]+)"/', $html, $links);

            $this->assertGreaterThanOrEqual(4, count(array_unique($links[1])), 'the tiles are not links any more, so this test proves nothing');
            foreach (array_unique($links[1]) as $anchor) {
                $this->assertStringContainsString('id="'.$anchor.'"', $html, ($hosted ? 'hosted' : 'selfhost').": nothing on the page is #{$anchor}");
            }
        }
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
            $this->page($query)
                ->assertViewHas('dashboard', fn (array $dashboard) => count($dashboard['signups']['days']) === 30)
                ->assertDontSee('id="date-range"', false)
                // The label this figure has always had on the page.
                ->assertSee(__('messages.active_users_7_days'));
        }
    }

    /**
     * ?sample=1 renders invented data for the documentation screenshot, which must not publish a
     * developer's real schedules and people. It is for local and test installs: in production
     * the parameter does nothing. Mutation: drop the environment test.
     */
    public function test_sample_data_is_never_served_in_production(): void
    {
        config(['app.hosted' => true, 'app.is_nexus' => true]);
        $this->seedSomething('Zebra Crossing Hall');
        $sampleSchedule = AdminDashboard::sample()['schedules']['rows'][0]['name'];

        $this->page('?sample=1')->assertSee($sampleSchedule)->assertDontSee('Zebra Crossing Hall');
        $this->page()->assertDontSee($sampleSchedule)->assertSee('Zebra Crossing Hall');

        $this->app['env'] = 'production';

        $this->page('?sample=1')->assertDontSee($sampleSchedule)->assertSee('Zebra Crossing Hall');
    }

    /**
     * The service, and then the page that prints it, each asked twice: once with a few rows and
     * once with four times as many. Mutation: read an owner, a subscription or a flyer per row,
     * in AdminDashboard or in any partial.
     */
    public function test_the_page_costs_the_same_however_much_there_is_to_show(): void
    {
        config(['app.hosted' => true, 'app.is_nexus' => true]);

        $seed = function (int $count): void {
            foreach (range(1, $count) as $index) {
                $owner = User::factory()->create(['email_verified_at' => now(), 'landing_page' => 'pricing']);
                $role = $this->createRole($owner, $index % 2 ? 'venue' : 'talent', ['country_code' => 'us']);
                $this->createTicket($this->createEvent($role, ['creator_role_id' => $role->id]));
            }
        };
        $measure = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            (new AdminDashboard)->build();
            $count = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $count;
        };

        $measurePage = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->page();
            $count = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $count;
        };

        $seed(3);
        // Once, unmeasured: what is looked up once per process or per session (whether a table
        // exists, the platform currency, the alerts, today's active-day mark) is paid for here
        // and not counted against either side.
        (new AdminDashboard)->build();
        $this->page();
        $few = [$measure(), $measurePage()];

        $seed(9);
        $many = [$measure(), $measurePage()];

        $this->assertSame($few, $many);
        $this->assertLessThanOrEqual(self::QUERY_CEILING, $many[0]);
    }

    /**
     * A card whose query failed costs that card and nothing else: build() reports it and hands
     * the page null in its place. Every card in turn, on the install that has all of them, so a
     * partial that reads a figure without asking whether its card is there fails here.
     *
     * The revenue card is the one that used to take the page with it: the controller answered a
     * missing card by asking RecurringRevenue again, outside build()'s guard. Mutation: put that
     * second call back, and the page reports the 60 a year the failed card could not.
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

        $this->page()->assertViewHas('arr', 60.0)->assertDontSee(__('messages.admin_dash_card_failed'));

        foreach (['signups', 'active', 'revenue', 'events', 'federation', 'schedules', 'recentEvents', 'system'] as $card) {
            $this->app->bind(AdminDashboard::class, fn () => new class($card) extends AdminDashboard
            {
                public function __construct(private string $failed)
                {
                    parent::__construct();
                }

                public function build(): array
                {
                    return [$this->failed => null] + parent::build();
                }
            });

            $response = $this->page();

            // The foot of the page is one quiet line with nothing to put a notice in.
            if ($card !== 'system') {
                $response->assertSee(__('messages.admin_dash_card_failed'));
            }
            if ($card === 'revenue') {
                $response->assertViewHas('arr', 0.0);
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
        $this->page()->assertSee(__('messages.admin_dash_active_unavailable'));

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
