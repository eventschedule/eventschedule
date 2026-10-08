<?php

namespace Tests\Feature;

use App\Models\MarketingDailyStat;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DemoService;
use App\Services\GrowthExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The /admin/growth export.
 *
 * Every test forces app.hosted=true. phpunit leaves IS_HOSTED unset, and off-hosted
 * Role::actualPlanTier() short-circuits to 'enterprise' - so without this the free-tier
 * sections come back empty and the assertions pass while proving nothing.
 */
class GrowthExportTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    /** The admin group re-auths; without the session key every request bounces to confirm-password. */
    private function adminActing(User $admin)
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
    }

    /**
     * Re-verify after an email change. User::updating() nulls email_verified_at whenever
     * the email is dirty on a hosted install, which would silently drop the user out of
     * every cohort in this export and make an exclusion test pass for the wrong reason.
     */
    private function reverify(User $user): User
    {
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->fresh();
    }

    private function freeRole(?User $owner = null, string $type = 'venue'): Role
    {
        return $this->createRole($owner ?? $this->createOwner(), $type, [
            'plan_type' => 'free',
            'plan_expires' => now()->subYear()->format('Y-m-d'),
            'trial_ends_at' => null,
        ]);
    }

    private function build(): array
    {
        return app(GrowthExportService::class)->build(
            now()->subDays(30), now(), now()->subDays(60), now()->subDays(31)
        );
    }

    /**
     * app:send-activation-nudges is hand-run, and before this the only way to know whether it had
     * ever been was a production query against schedule_nudges.
     */
    public function test_the_page_says_whether_the_activation_nudges_ever_ran(): void
    {
        $admin = $this->createOwner(true);

        $this->adminActing($admin)->get('/admin/growth')->assertOk()
            ->assertSee(__('messages.growth_nudges_never_run'))
            ->assertSee(__('messages.growth_churn_and_trials'));

        $role = $this->freeRole();
        DB::table('schedule_nudges')->insert([
            ['role_id' => $role->id, 'nudge_key' => 'no_ticket_type_free', 'created_at' => now()->subDays(2)],
            ['role_id' => $role->id, 'nudge_key' => 'idle_30', 'created_at' => now()->subDays(20)],
        ]);

        $nudges = $this->build()['nudges'];
        $this->assertSame(1, $nudges['no_ticket_type_free']['last_7_days']);
        $this->assertSame(0, $nudges['idle_30']['last_7_days']);
        $this->assertSame(1, $nudges['idle_30']['total']);

        $this->adminActing($admin)->get('/admin/growth')->assertOk()
            ->assertDontSee(__('messages.growth_nudges_never_run'))
            ->assertSee('no_ticket_type_free');
    }

    /**
     * The download button is gone - the payload is pulled from /api/internal/growth (see
     * GrowthDataEndpointTest) - but the page still renders from the same build().
     */
    public function test_the_page_renders_without_a_download_and_the_payload_carries_its_schema(): void
    {
        $admin = $this->createOwner(true);
        $this->freeRole();

        $page = $this->adminActing($admin)->get('/admin/growth')->assertOk()
            ->assertDontSee('/admin/growth/export');

        // The page builds on every view, without the endpoint's lock, so it skips the
        // analysis-only sections it never renders - and keeps everything it does.
        $pageData = $page->viewData('data');
        foreach (['daily', 'buyers', 'reach', 'nudge_outcomes', 'usage', 'federation'] as $section) {
            $this->assertArrayNotHasKey($section, $pageData, "/admin/growth should not build {$section}");
        }
        foreach (['activation', 'free_pressure', 'monetization', 'nudges', 'owner_digests', 'churn', 'acquisition', 'funnel'] as $section) {
            $this->assertArrayHasKey($section, $pageData);
        }
        $this->assertArrayHasKey('hero_test', $pageData);

        $data = $this->build();
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('signups', $data);
        $this->assertArrayHasKey('schedules', $data);
        // 3 since the claims section landed, 6 since gmv_recent_by_currency and the demo-free
        // gmv_by_currency, 7 since mrr came from RecurringRevenue, 8 since paying meant billing,
        // sales went to the seller and attribution was anonymised, 9 since the daily, nudge
        // outcome, audience, reach and adoption data, 10 since outside ticket links and the
        // reachable placeholders, 11 since a headline variant reached sign-up on the link as well
        // as in the consented cookie, 12 since imports are counted by source and the two Google
        // fields read where the ids live, 14 since the event_form section (what hand-made events
        // are saved with), 15 since traffic[] counts the guest "Submit your event" page, 16 since
        // it counts the guest pages, 17 since events made by a feed are a source of their own
        // (imported_feed, which arrives with nobody at the keyboard). Bumping this is deliberate:
        // a reader diffing two pulls needs to know the shape (or the meaning) moved.
        $this->assertSame(17, $data['meta']['schema_version']);
        $this->assertSame(GrowthExportService::SCHEMA_VERSION, $data['meta']['schema_version']);
        $this->assertSame(now()->format('Y-m'), $data['meta']['partial_month']['month']);
        $this->assertSame(['funnel', 'funnel_trend'], $data['meta']['range_applies_to']);
    }

    /**
     * The read on the event form redesign: of the events people typed in, how many have somewhere
     * to be, a way to sign up and a flyer, first events apart from later ones.
     */
    public function test_event_form_counts_what_hand_made_events_are_saved_with(): void
    {
        $owner = $this->createOwner();
        $talent = $this->freeRole($owner, 'talent');
        $venue = $this->freeRole($owner, 'venue');
        $venue->forceFill(['email' => 'room@example.org'])->save();
        $bareVenue = $this->freeRole($owner, 'venue');
        $bareVenue->forceFill(['email' => null])->save();

        // The account's first event: bare.
        $first = $this->createEvent($talent, ['user_id' => $owner->id]);
        // Later ones: at a venue with tickets; online with a flyer; at a venue with a link elsewhere.
        $atVenue = $this->createEvent($talent, ['user_id' => $owner->id, 'tickets_enabled' => true]);
        $atVenue->roles()->attach($venue->id, ['is_accepted' => true]);
        $this->createEvent($talent, ['user_id' => $owner->id, 'event_url' => 'https://example.org/stream', 'flyer_image_url' => 'flyers/a.jpg']);
        $elsewhere = $this->createEvent($talent, ['user_id' => $owner->id, 'registration_url' => 'https://tickets.example.org/x']);
        $elsewhere->roles()->attach($bareVenue->id, ['is_accepted' => true]);

        // An import is not the form's doing, and neither is the venue it brought.
        $imported = $this->createEvent($talent, ['user_id' => $owner->id, 'rsvp_enabled' => true]);
        $imported->forceFill(['import_source' => \App\Models\Event::IMPORT_ICS])->save();
        $importedVenue = $this->freeRole($owner, 'venue');
        $imported->roles()->attach($importedVenue->id, ['is_accepted' => true]);

        // Somebody else's first event, with everything.
        $other = $this->createOwner();
        $otherTalent = $this->freeRole($other, 'talent');
        $this->createEvent($otherTalent, ['user_id' => $other->id, 'rsvp_enabled' => true, 'event_url' => 'https://example.org/live', 'flyer_image_url' => 'flyers/b.jpg']);

        $section = $this->build()['event_form'];
        $month = now()->format('Y-m');

        $this->assertSame(GrowthExportService::RECENT_MONTHS, count($section['by_month']));
        $this->assertSame(['events' => 2, 'with_location' => 1, 'with_signup' => 1, 'with_flyer' => 1], $section['by_month'][$month]['first']);
        $this->assertSame(['events' => 3, 'with_location' => 3, 'with_signup' => 2, 'with_flyer' => 1], $section['by_month'][$month]['later']);
        $this->assertSame(['created' => 2, 'with_email' => 1], $section['new_venues'][$month]);

        $oldest = array_key_first($section['by_month']);
        $this->assertSame(['events' => 0, 'with_location' => 0, 'with_signup' => 0, 'with_flyer' => 0], $section['by_month'][$oldest]['first'], 'a month with nothing in it is a real zero');
        $this->assertSame($first->id, \App\Models\Event::where('user_id', $owner->id)->min('id'), 'sanity check: the bare event is the first');
    }

    public function test_claims_reports_untracked_months_as_null_not_zero(): void
    {
        // Mirrors test_traffic_reports_untracked_months_as_null_not_zero. auto_created comes from
        // roles.created_at and is real for every month; claimed comes from schedule.claim audit
        // rows, which did not exist before the feature, so an earlier month must say "not measured"
        // rather than "nobody claimed anything".
        $claims = $this->build()['claims'];

        $this->assertArrayHasKey('unclaimed_total', $claims);
        $this->assertNotEmpty($claims['claimed']);

        $months = array_keys($claims['claimed']);
        $this->assertNull($claims['claimed'][$months[0]], 'the oldest month predates the audit action');
        $this->assertSame(0, $claims['claimed'][end($months)], 'the current month is measured, so a real zero');
        foreach ($claims['auto_created'] as $month => $count) {
            $this->assertIsInt($count, "auto_created is answerable for every month, including {$month}");
        }
    }

    public function test_the_export_funnel_matches_the_admin_users_page(): void
    {
        $admin = $this->createOwner(true);
        $owner = $this->createOwner();
        $role = $this->freeRole($owner);
        $this->createEvent($role);

        $usersPage = $this->adminActing($admin)->get('/admin/users?range=last_30_days');
        $usersPage->assertOk();
        $pageFunnel = $usersPage->viewData('funnel');

        $growthPage = $this->adminActing($admin)->get('/admin/growth?range=last_30_days');
        $growthPage->assertOk();
        $exportFunnel = $growthPage->viewData('data')['funnel'];

        // The whole reason the funnel lives in a shared service: if these can disagree,
        // the export quietly contradicts the page it is meant to explain.
        $this->assertSame(
            array_column($pageFunnel['stages'], 'count', 'key'),
            array_column($exportFunnel['stages'], 'count', 'key')
        );
        $this->assertSame($pageFunnel['cohort_size'], $exportFunnel['cohort_size']);
        $this->assertSame($pageFunnel['first_event_conv'], $exportFunnel['first_event_conv']);
    }

    /**
     * The funnel used to stop at saved_event, which hid the largest drop in the business:
     * across the install only ~21% of schedules ever get a ticket type and ~4% ever take money.
     * These four stages are what make that visible, and each must be a true subset of the one
     * above it or the funnel draws conversions above 100%.
     */
    public function test_the_funnel_carries_the_money_stages(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeRole($owner);
        $event = $this->createEvent($role);

        $stages = fn () => array_column($this->build()['funnel']['stages'], 'count', 'key');

        // An event with no ticket type at all: reaches saved_event and stops.
        $before = $stages();
        $this->assertSame(1, $before['saved_event']);
        $this->assertSame(0, $before['saved_ticket']);
        $this->assertSame(0, $before['saved_paid_ticket']);
        $this->assertSame(0, $before['reached_checkout']);
        $this->assertSame(0, $before['subscribed']);

        // A FREE ticket type advances saved_ticket only - it is not a monetization signal, and
        // counting it as one is how ticket_types came to overstate anything commercial.
        $this->createTicket($event, ['price' => 0]);
        $free = $stages();
        $this->assertSame(1, $free['saved_ticket']);
        $this->assertSame(0, $free['saved_paid_ticket']);

        // A priced ticket advances both.
        $this->createTicket($event, ['type' => 'Paid', 'price' => 20]);
        $paid = $stages();
        $this->assertSame(1, $paid['saved_ticket'], 'one user, not one ticket');
        $this->assertSame(1, $paid['saved_paid_ticket']);

        // Reaching checkout without buying is the whole point of the stamp: previously only
        // completed subscriptions were recorded, so this stage had no denominator.
        $owner->forceFill(['subscribe_form_viewed_at' => now()])->save();
        $reached = $stages();
        $this->assertSame(1, $reached['reached_checkout']);
        $this->assertSame(0, $reached['subscribed'], 'saw the form, did not buy');

        // The ticket stages continue the cohort chain, so they ARE subsets of saved_event.
        foreach (['saved_ticket', 'saved_paid_ticket'] as $key) {
            $this->assertLessThanOrEqual($reached['saved_event'], $reached[$key], $key);
        }
    }

    /**
     * The plan stages are NOT a continuation of the ticket stages - buying Pro has nothing to do
     * with selling tickets, and on the real install three of nine payers have no paid ticket type
     * and one has no events at all. Dividing one by the other rendered "300%" on the funnel with
     * a bar wider than the stage above it. The earlier version of the test above missed this
     * because its fixture happened to give every stage the same count.
     */
    public function test_the_plan_stages_do_not_draw_a_conversion_off_the_ticket_stages(): void
    {
        // A user who subscribes having never created an event, let alone a ticket type.
        $buyer = $this->createOwner();
        $role = $this->freeRole($buyer);
        $role->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'active',
            'stripe_price' => 'price_test_monthly',
            'quantity' => 1,
        ]);

        $stages = $this->build()['funnel']['stages'];
        $byKey = array_column($stages, null, 'key');
        $counts = array_column($stages, 'count', 'key');

        // The shape that used to produce a >100% conversion.
        $this->assertSame(0, $counts['saved_event']);
        $this->assertSame(0, $counts['saved_paid_ticket']);
        $this->assertSame(1, $counts['subscribed']);

        // No ratio is drawn across the boundary, exactly as 'account' is skipped where the
        // anonymous-traffic stages meet the cohort.
        $this->assertNull($byKey['reached_checkout']['step_conv'],
            'a conversion across the tickets -> plan boundary compares two different populations');

        // And the groups are distinct, which is what the admin funnel renders its headers from.
        $this->assertSame('tickets', $byKey['saved_paid_ticket']['group']);
        $this->assertSame('plan', $byKey['reached_checkout']['group']);

        // Whatever else is true, no stage may ever report more than 100% of the one above it.
        foreach ($stages as $stage) {
            if ($stage['step_conv'] !== null) {
                $this->assertLessThanOrEqual(100, $stage['step_conv'], $stage['key']);
            }
        }
    }

    /**
     * The emailed-code step is measured, and drawn as its own group.
     *
     * Never part of the cohort chain: a Google sign-up skips the code, so 'account' can exceed
     * 'signup_code_verified' and a ratio across that boundary would be meaningless. Inside the
     * group, verified/requests IS the code wall's conversion, while signup_code_invalid overlaps
     * both (a mistype then a success counts in each) and must get no ratio.
     */
    public function test_the_email_code_step_is_its_own_group(): void
    {
        $start = \Illuminate\Support\Carbon::parse('2026-09-26');
        $end = \Illuminate\Support\Carbon::parse('2026-09-30')->endOfDay();

        MarketingDailyStat::create([
            'date' => '2026-09-26',
            'signup_views' => 40,
            'signup_code_requests' => 10,
            'signup_code_verified' => 6,
            'signup_code_invalid' => 3,
        ]);

        $funnel = app(GrowthExportService::class)->funnelData($start, $end, $start->copy()->subDays(5), $start->copy()->subSecond());
        $byKey = array_column($funnel['stages'], null, 'key');

        $this->assertSame(['signup_code_requests', 'signup_code_verified', 'signup_code_invalid'],
            array_values(array_map(fn ($s) => $s['key'], array_filter($funnel['stages'], fn ($s) => $s['group'] === 'email_code'))));

        $this->assertSame(10, $byKey['signup_code_requests']['count']);
        $this->assertEquals(25.0, $byKey['signup_code_requests']['step_conv'], 'share of sign-up page visitors who asked for a code');
        $this->assertEquals(60.0, $byKey['signup_code_verified']['step_conv'], 'the code wall\'s own conversion');
        $this->assertSame(3, $byKey['signup_code_invalid']['count']);
        $this->assertNull($byKey['signup_code_invalid']['step_conv'], 'an overlap counter is not a step');
        $this->assertNull($byKey['account']['step_conv']);

        // The group sits between the sign-up page and the account, and the leak finder ignores it.
        $keys = array_column($funnel['stages'], 'key');
        $this->assertLessThan(array_search('account', $keys), array_search('signup_code_invalid', $keys));
        $this->assertGreaterThan(array_search('signup_view', $keys), array_search('signup_code_requests', $keys));
        $this->assertNotContains($funnel['biggest_drop']['from_key'] ?? null, ['signup_code_requests', 'signup_code_verified', 'signup_code_invalid']);
    }

    /** A window opening before a column existed reports it as n/a, not as its backfilled zero. */
    public function test_email_code_stages_are_null_before_their_column_existed(): void
    {
        MarketingDailyStat::create(['date' => '2026-07-10', 'signup_views' => 5]);

        // Opens after the table but before the code counters (2026-08-03).
        $early = app(GrowthExportService::class)->funnelData(
            \Illuminate\Support\Carbon::parse('2026-07-10'), \Illuminate\Support\Carbon::parse('2026-07-31'),
            \Illuminate\Support\Carbon::parse('2026-06-10'), \Illuminate\Support\Carbon::parse('2026-07-09'));
        $byKey = array_column($early['stages'], 'count', 'key');
        $this->assertSame(5, $byKey['signup_view']);
        $this->assertNull($byKey['signup_code_requests']);
        $this->assertNull($byKey['signup_code_invalid']);

        // Opens after the request counters but before signup_code_invalid (2026-09-25).
        $mid = app(GrowthExportService::class)->funnelData(
            \Illuminate\Support\Carbon::parse('2026-08-10'), \Illuminate\Support\Carbon::parse('2026-08-31'),
            \Illuminate\Support\Carbon::parse('2026-07-10'), \Illuminate\Support\Carbon::parse('2026-08-09'));
        $byKey = array_column($mid['stages'], 'count', 'key');
        $this->assertSame(0, $byKey['signup_code_requests']);
        $this->assertNull($byKey['signup_code_invalid']);
    }

    /** Selfhost has no code step, so no group for it. */
    public function test_email_code_stages_are_hosted_only(): void
    {
        config(['app.hosted' => false]);

        $keys = array_column($this->build()['funnel']['stages'], 'key');

        $this->assertNotContains('signup_code_requests', $keys);
        $this->assertNotContains('signup_code_invalid', $keys);
    }

    /**
     * Cashier's subscriptions() relation has no status filter, so "has a subscriptions row"
     * counted a declined card as a sale - the very population stripe_subscription_failed exists
     * to separate out.
     */
    public function test_an_incomplete_checkout_is_not_a_conversion(): void
    {
        $user = $this->createOwner();
        $role = $this->freeRole($user);
        $role->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'incomplete',
            'stripe_price' => 'price_test_monthly',
            'quantity' => 1,
        ]);

        $counts = array_column($this->build()['funnel']['stages'], 'count', 'key');

        $this->assertSame(0, $counts['subscribed'], 'a declined card is not a sale');
        $this->assertSame(0, $counts['reached_checkout'], 'nor does it imply the form was seen');
    }

    /**
     * biggest_drop must be able to name the ticket cliff. saved_event -> saved_ticket is the
     * largest drop in the business (438 schedules publish an event, 144 ever create a ticket
     * type), and scoping the loop to the cohort group made it the one transition that could
     * never be reported - while the stages were added precisely to surface it.
     *
     * The plan group stays excluded: reached_checkout is not a subset of saved_paid_ticket, so a
     * "drop" there can be negative and would let this pick a meaningless pair.
     */
    public function test_the_biggest_drop_can_name_the_ticket_cliff(): void
    {
        // Three users publish an event; only one of them adds a ticket type. The largest single
        // drop in this fixture is therefore saved_event -> saved_ticket.
        foreach (range(1, 3) as $n) {
            $owner = $this->createOwner();
            $event = $this->createEvent($this->freeRole($owner));

            if ($n === 1) {
                $this->createTicket($event, ['price' => 10]);
            }
        }

        $funnel = $this->build()['funnel'];

        $this->assertSame('saved_event', $funnel['biggest_drop']['from_key']);
        $this->assertSame('saved_ticket', $funnel['biggest_drop']['to_key']);
        $this->assertSame(2, $funnel['biggest_drop']['lost']);
    }

    /** A cancelled subscriber still converted once, which is what a conversion funnel counts. */
    public function test_a_cancelled_subscription_still_counts_as_a_conversion(): void
    {
        $user = $this->createOwner();
        $role = $this->freeRole($user);
        $role->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'canceled',
            'stripe_price' => 'price_test_monthly',
            'quantity' => 1,
        ]);

        $counts = array_column($this->build()['funnel']['stages'], 'count', 'key');

        $this->assertSame(1, $counts['subscribed']);
    }

    /**
     * reached_checkout is OR-defined against the subscription, like stages 4/6, so a subscriber
     * from before the column existed cannot make the stage below it exceed it.
     */
    public function test_reached_checkout_never_undercuts_subscribed(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeRole($owner);

        $role->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'active',
            'stripe_price' => 'price_test_monthly',
            'quantity' => 1,
        ]);

        // Deliberately NOT stamped - this is the pre-column subscriber.
        $this->assertNull($owner->fresh()->subscribe_form_viewed_at);

        $stages = array_column($this->build()['funnel']['stages'], 'count', 'key');
        $this->assertSame(1, $stages['subscribed']);
        $this->assertSame(1, $stages['reached_checkout'], 'must not fall below the stage under it');
    }

    public function test_the_payload_carries_no_personal_data(): void
    {
        $owner = $this->createOwner();
        $owner->name = 'Marina Delacroix';
        $owner->email = 'marina.delacroix@gmail.com';
        $owner->referrer_url = 'https://ref.example.org/land?token=SECRET123&email=leak@gmail.com';
        $owner->landing_page = 'https://eventschedule.com/for-musicians?utm_source=x';
        $owner->save();
        $owner = $this->reverify($owner);

        $role = $this->freeRole($owner);
        $role->email = 'boxoffice@gmail.com';
        $role->address1 = '19 Rue Lepic';
        $role->stripe_id = 'cus_TESTCUSTOMER';
        $role->save();

        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20]);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 20, 'email' => 'buyer@gmail.com'], $ticket, 1);

        $json = json_encode($this->build());

        foreach ([
            'Marina Delacroix', 'marina.delacroix@gmail.com', 'boxoffice@gmail.com',
            'buyer@gmail.com', 'leak@gmail.com', 'SECRET123', '19 Rue Lepic',
            'cus_TESTCUSTOMER', $role->subdomain,
        ] as $secret) {
            $this->assertStringNotContainsString($secret, $json, "the export leaked: {$secret}");
        }

        // Nothing anywhere may look like an email address or an IP.
        $this->assertDoesNotMatchRegularExpression('/[\w.+-]+@[\w-]+\.[\w.]+/', $json);
        $this->assertDoesNotMatchRegularExpression('/\b\d{1,3}(\.\d{1,3}){3}\b/', $json);

        // The landing page survives as a bare path, because it is one of our marketing pages. The
        // referrer is a host only one signup shares, so it does not survive at all: a site that
        // sent one person is as good as that person's name.
        $this->assertStringNotContainsString('ref.example.org', $json);
        $this->assertStringContainsString('/for-musicians', $json);
    }

    public function test_ids_are_hashed_stable_and_link_the_two_row_tables(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeRole($owner);

        $first = $this->build();
        $second = $this->build();

        $sid = $first['schedules']['rows'][0][array_flip($first['schedules']['columns'])['sid']];
        $uid = $first['schedules']['rows'][0][array_flip($first['schedules']['columns'])['uid']];

        $this->assertStringStartsWith('s:', $sid);
        $this->assertStringStartsWith('u:', $uid);
        $this->assertNotSame((string) $role->id, $sid);
        // 12 hex characters: 6 collide with even odds at a few thousand users, silently joining
        // one person's schedule to another person's signup row.
        $this->assertMatchesRegularExpression('/^s:[0-9a-f]{12}$/', $sid);
        $this->assertMatchesRegularExpression('/^u:[0-9a-f]{12}$/', $uid);

        // Stable across pulls, so two exports can be diffed.
        $this->assertSame($first['schedules']['rows'], $second['schedules']['rows']);

        // The schedule row joins to the signup row that owns it.
        $signupUids = array_column($first['signups']['rows'], array_flip($first['signups']['columns'])['uid']);
        $this->assertContains($uid, $signupUids);
    }

    public function test_signup_rows_include_users_who_never_created_a_schedule(): void
    {
        // The whole point of the signups table: a schedule-only export cannot see these.
        $this->createOwner();
        $withSchedule = $this->createOwner();
        $this->freeRole($withSchedule);

        $data = $this->build();
        $i = array_flip($data['signups']['columns']);

        $this->assertCount(2, $data['signups']['rows']);
        $saved = array_column($data['signups']['rows'], $i['saved_schedule']);
        $this->assertContains(true, $saved);
        $this->assertContains(false, $saved);
        $this->assertSame(1, $data['activation']['saved_schedule']);
        $this->assertSame(2, $data['activation']['accounts']);
    }

    public function test_free_pressure_buckets_the_peak_month_of_paid_tickets(): void
    {
        $quiet = $this->freeRole();
        $quietEvent = $this->createEvent($quiet);
        $quietTicket = $this->createTicket($quietEvent, ['price' => 10, 'quantity' => 500]);
        $this->createSale($quietEvent, $quiet, ['status' => 'paid', 'payment_amount' => 30], $quietTicket, 3);

        $busy = $this->freeRole();
        $busyEvent = $this->createEvent($busy);
        $busyTicket = $this->createTicket($busyEvent, ['price' => 10, 'quantity' => 500]);
        $this->createSale($busyEvent, $busy, ['status' => 'paid', 'payment_amount' => 300], $busyTicket, 30);

        $idle = $this->freeRole();

        $pressure = $this->build()['free_pressure'];

        $this->assertSame(3, $pressure['free_schedules']);
        $this->assertSame(1, $pressure['peak_month_paid_tickets']['1-5'], 'the 3-ticket schedule');
        $this->assertSame(1, $pressure['peak_month_paid_tickets']['16+'], 'the 30-ticket schedule');
        $this->assertSame(1, $pressure['peak_month_paid_tickets']['0'], 'the idle schedule');

        // Paid selling is Pro/Enterprise now, so this reads as a conversion list rather than a cap
        // meter: free schedules that have sold before and are sitting on Free.
        $this->assertSame(2, $pressure['ever_sold_paid']);
        $this->assertNotNull($idle->fresh());
    }

    public function test_rsvps_imports_addons_and_free_tickets_never_count_as_paid(): void
    {
        $role = $this->freeRole();
        $event = $this->createEvent($role);

        $paid = $this->createTicket($event, ['price' => 15, 'quantity' => 500]);
        $free = $this->createTicket($event, ['price' => 0, 'quantity' => 500]);
        $addon = $this->createTicket($event, ['price' => 5, 'quantity' => 500, 'is_addon' => true]);

        $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 15], $paid, 1);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_method' => 'rsvp'], $free, 9);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_method' => 'import'], $paid, 9);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 5], $addon, 9);
        $this->createSale($event, $role, ['status' => 'unpaid', 'payment_amount' => 15], $paid, 9);

        $data = $this->build();
        $i = array_flip($data['schedules']['columns']);
        $row = $data['schedules']['rows'][0];

        $this->assertSame(1, $row[$i['paid_tickets_total']], 'only the one real paid ticket counts');
    }

    /**
     * ticket_types counts every row in `tickets`, including the free RSVP/registration types
     * that most schedules use. Only paid_ticket_types says a schedule intends to take money -
     * the distinction that decides how large a paid-ticketing gate would actually be.
     */
    public function test_paid_ticket_types_excludes_free_and_rsvp_types(): void
    {
        $role = $this->freeRole();
        $event = $this->createEvent($role);

        $this->createTicket($event, ['price' => 15, 'quantity' => 100]);
        $this->createTicket($event, ['price' => 0, 'quantity' => 100]);
        $this->createTicket($event, ['price' => 0, 'quantity' => 100]);

        $data = $this->build();
        $i = array_flip($data['schedules']['columns']);
        $row = $data['schedules']['rows'][0];

        $this->assertSame(3, $row[$i['ticket_types']], 'every type counts toward ticket_types');
        $this->assertSame(1, $row[$i['paid_ticket_types']], 'only the priced type is commercial');

        $venue = collect($data['segments']['by_schedule_type'])->firstWhere('key', 'venue');
        $this->assertSame(1, $venue['with_ticket_type']);
        $this->assertSame(1, $venue['with_paid_ticket_type']);
    }

    /**
     * Retention used to count ANY page view in 90 days as "active", so every cohort scored
     * ~100% retained forever and the metric could never fall. It now means the owner did
     * something: touched an event, or sold a paid ticket.
     */
    public function test_retention_activity_ignores_page_views(): void
    {
        // A schedule whose only signal is traffic on its public page.
        $viewedOnly = $this->freeRole();
        \App\Models\AnalyticsDaily::create([
            'role_id' => $viewedOnly->id,
            'date' => now()->subDays(2)->toDateString(),
            'desktop_views' => 40,
        ]);

        $data = $this->build();
        $i = array_flip($data['schedules']['columns']);
        $row = collect($data['schedules']['rows'])->firstWhere($i['sid'], $data['schedules']['rows'][0][$i['sid']]);

        $this->assertGreaterThan(0, $row[$i['views_90d']], 'the page views are still recorded');
        $this->assertSame(0, $row[$i['events_recent_90d']], 'but nothing was published');

        $month = collect($data['retention'])->firstWhere('month', $viewedOnly->created_at->format('Y-m'));
        $this->assertSame(0, $month['active_recently'], 'views alone must not read as active');
        $this->assertSame(1, $month['visited_recently'], 'the audience-side reading is kept separately');

        // Publishing an event flips it to active.
        $this->createEvent($viewedOnly);

        $after = collect($this->build()['retention'])
            ->firstWhere('month', $viewedOnly->created_at->format('Y-m'));
        $this->assertSame(1, $after['active_recently']);
    }

    public function test_revenue_is_reported_per_currency_not_summed(): void
    {
        $role = $this->freeRole();

        $usd = $this->createEvent($role, ['ticket_currency_code' => 'USD']);
        $usdTicket = $this->createTicket($usd, ['price' => 10, 'quantity' => 100]);
        $this->createSale($usd, $role, ['status' => 'paid', 'payment_amount' => 100], $usdTicket, 1);

        $eur = $this->createEvent($role, ['ticket_currency_code' => 'EUR']);
        $eurTicket = $this->createTicket($eur, ['price' => 10, 'quantity' => 100]);
        $this->createSale($eur, $role, ['status' => 'paid', 'payment_amount' => 40], $eurTicket, 1);

        $gmv = $this->build()['monetization']['gmv_by_currency'];
        $byCurrency = [];
        foreach ($gmv as $line) {
            $byCurrency[$line['currency']] = ($byCurrency[$line['currency']] ?? 0) + $line['amount'];
        }

        // sales has no currency column - it comes from the events join. Summing across
        // currencies would produce a single meaningless number.
        $this->assertSame(100.0, $byCurrency['USD']);
        $this->assertSame(40.0, $byCurrency['EUR']);
    }

    /**
     * Grants and referral credits are plans, so they count in plan_counts and by_plan_source - but
     * nothing bills them, so MRR is the one subscription alone.
     */
    public function test_plan_counts_include_grants_but_mrr_is_only_what_is_billed(): void
    {
        config(['services.stripe_platform.price_monthly' => 'price_test_monthly']);

        $paying = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'pro', 'plan_expires' => null,
            'plan_term' => 'month', 'plan_source' => null,
        ]);
        $paying->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'active',
            'stripe_price' => 'price_test_monthly',
            'quantity' => 1,
        ]);
        $granted = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'pro', 'plan_expires' => now()->addYear()->format('Y-m-d'),
            'plan_term' => 'month', 'plan_source' => 'admin',
        ]);
        $referred = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'pro', 'plan_expires' => now()->addMonth()->format('Y-m-d'),
            'plan_term' => 'month', 'plan_source' => 'referral',
        ]);

        $money = $this->build()['monetization'];
        $monthly = (float) config('services.stripe_platform.price_monthly_amount', 5);

        $this->assertSame(3, $money['plan_counts']['pro']);
        $this->assertSame(round($monthly, 2), $money['mrr'], 'the grant and the referral credit pay nothing');
        $this->assertSame(round($monthly, 2), $money['arpu'], 'and do not dilute ARPU either');
        $this->assertSame(1, $money['by_plan_source']['admin']);
        $this->assertSame(1, $money['by_plan_source']['referral']);
        $this->assertSame(1, $money['by_plan_source']['stripe']);
        $this->assertNotNull($paying->fresh());
        $this->assertNotNull($granted->fresh());
        $this->assertNotNull($referred->fresh());
    }

    public function test_demo_data_is_excluded_everywhere(): void
    {
        $demoOwner = $this->createOwner();
        $demoOwner->email = DemoService::DEMO_EMAIL;
        $demoOwner->save();
        // Must stay verified, or this would prove exclusion-by-unverified instead of
        // exclusion-by-demo and the assertion below would be worthless.
        $demoOwner = $this->reverify($demoOwner);
        $this->assertNotNull($demoOwner->email_verified_at);
        $this->createRole($demoOwner, 'venue', ['subdomain' => 'demo-showcase']);

        $real = $this->createOwner();
        $this->freeRole($real);

        $data = $this->build();

        $this->assertCount(1, $data['signups']['rows'], 'the demo owner is excluded');
        $this->assertCount(1, $data['schedules']['rows'], 'the demo schedule is excluded');
    }

    public function test_the_row_cap_reports_truncation_rather_than_hiding_it(): void
    {
        config(['usage.growth_row_cap' => 1]);

        $this->freeRole();
        $this->freeRole();

        $meta = $this->build()['meta'];

        $this->assertTrue($meta['truncated']['schedules']['capped']);
        $this->assertSame(2, $meta['truncated']['schedules']['total'], 'the true total is still reported');
        $this->assertSame(1, $meta['row_cap']);
    }

    public function test_acquisition_groups_signups_by_landing_page(): void
    {
        // Three, because a utm value shared by fewer signups is exported as "(other)".
        $activating = $this->createOwner();
        $activating->landing_page = 'for-venues';
        $activating->utm_source = 'newsletter';
        $activating->save();
        $this->freeRole($activating);

        foreach (range(1, 2) as $n) {
            $bouncing = $this->createOwner();
            $bouncing->landing_page = 'for-venues';
            $bouncing->utm_source = 'Newsletter ';
            $bouncing->save();
        }

        $acquisition = $this->build()['acquisition'];
        $landing = collect($acquisition['by_landing_path'])->firstWhere('key', '/for-venues');

        $this->assertNotNull($landing);
        $this->assertSame(3, $landing['signups']);
        $this->assertSame(1, $landing['saved_schedule'], 'one of the three got as far as a schedule');

        // Trimmed and lowercased, so a capitalised tag does not split the group (and fall under
        // the group-size rule on its own).
        $source = collect($acquisition['by_utm_source'])->firstWhere('key', 'newsletter');
        $this->assertSame(3, $source['signups']);
    }

    public function test_non_admins_are_rejected(): void
    {
        $user = $this->createOwner();

        $this->actingAs($user)->get('/admin/growth')->assertRedirect();
    }

    public function test_the_page_is_absent_on_a_selfhosted_install(): void
    {
        config(['app.hosted' => false]);
        $admin = $this->createOwner(true);

        // A single-tenant selfhost has no tiers and no subscriptions, so every
        // monetization section would be empty or actively misleading.
        $this->adminActing($admin)->get('/admin/growth')->assertNotFound();
    }

    /**
     * A counter that did not exist yet must read null, never 0.
     *
     * Every column after the first three was added by a later migration with default(0), which
     * MySQL backfills onto the rows already there. Reporting those defaults as measurements is
     * how "pricing_views: 0" in the 2026-08-30 export looked like a broken counter when the
     * column was two days old, and how commercial_visitors silently equalled visitors for
     * thirteen months.
     */
    public function test_traffic_reports_untracked_months_as_null_not_zero(): void
    {
        config(['app.is_nexus' => true]);

        // One row in a month before docs_* and pricing_* existed, with real visitors.
        MarketingDailyStat::create([
            'date' => '2026-07-10',
            'visitors' => 40,
            'page_views' => 55,
            'signup_views' => 6,
        ]);

        $data = app(GrowthExportService::class)->build(
            now()->copy()->subDays(30), now(), now()->copy()->subDays(60), now()->copy()->subDays(30)
        );

        $july = collect($data['traffic'])->firstWhere('month', '2026-07');
        $this->assertNotNull($july);

        // Tracked from the create migration: real numbers.
        $this->assertSame(40, $july['visitors']);
        $this->assertSame(6, $july['signup_views']);

        // Not yet tracked in July: null, not 0.
        $this->assertNull($july['docs_visitors'], 'docs_visitors did not exist in 2026-07');
        $this->assertNull($july['pricing_views'], 'pricing_views did not exist until 2026-08-28');
        $this->assertNull($july['signup_code_requests']);

        // And the derived column must not republish the total as buyer intent by subtracting
        // a subset that was never counted.
        $this->assertNull($july['commercial_visitors']);
    }

    /**
     * reached_schedule_form is OR-defined, so it can never come out below the stage it contains.
     *
     * The timestamp alone is stamped only by RoleController::create() and only since
     * 2026-07-07, so a user who arrived by any other path read as "never reached the form"
     * while also reading as "saved a schedule" - 88 against 491 in the 2026-08-30 export.
     */
    public function test_reached_schedule_form_counts_anyone_who_saved_a_schedule(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['schedule_form_viewed_at' => null])->saveQuietly();
        $this->createRole($owner);

        $data = app(GrowthExportService::class)->build(
            now()->copy()->subDays(30), now(), now()->copy()->subDays(60), now()->copy()->subDays(30)
        );

        $this->assertSame(1, $data['activation']['saved_schedule']);
        $this->assertSame(
            1,
            $data['activation']['reached_schedule_form'],
            'saving a schedule implies reaching the form, whatever the timestamp says'
        );
        $this->assertGreaterThanOrEqual(
            $data['activation']['saved_schedule'],
            $data['activation']['reached_schedule_form']
        );
    }

    /**
     * Money has to be attributable to the schedule that earned it.
     *
     * gmv_by_currency is a platform total, so nothing connected revenue to a schedule - and
     * the whole ticketing business turns out to be a handful of accounts.
     */
    public function test_schedule_rows_carry_first_paid_sale_and_gmv(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['ticket_currency_code' => 'GBP']);
        $ticket = $this->createTicket($event, ['price' => 25]);

        $this->createSale($event, $role, [
            'payment_amount' => 50,
            'paid_at' => now()->copy()->startOfMonth()->addDay(),
        ], $ticket, 2);

        $data = app(GrowthExportService::class)->build(
            now()->copy()->subDays(30), now(), now()->copy()->subDays(60), now()->copy()->subDays(30)
        );

        $i = array_flip($data['schedules']['columns']);
        $row = collect($data['schedules']['rows'])
            ->first(fn ($r) => $r[$i['paid_tickets_total']] > 0);

        $this->assertNotNull($row, 'the selling schedule is in the export');
        $this->assertSame(now()->format('Y-m'), $row[$i['first_paid_sale_month']]);
        $this->assertSame('GBP', $row[$i['gmv_currency']]);
        $this->assertSame(50.0, (float) end($row[$i['gmv_recent']]));
    }

    /** A schedule paid in two currencies reports null rather than adding unlike things. */
    public function test_mixed_currency_schedules_report_no_gmv_sum(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        foreach (['GBP' => 50, 'EUR' => 40] as $currency => $amount) {
            $event = $this->createEvent($role, ['ticket_currency_code' => $currency]);
            $ticket = $this->createTicket($event, ['price' => 20]);
            $this->createSale($event, $role, [
                'payment_amount' => $amount,
                'paid_at' => now()->copy()->startOfMonth()->addDay(),
            ], $ticket, 1);
        }

        $data = app(GrowthExportService::class)->build(
            now()->copy()->subDays(30), now(), now()->copy()->subDays(60), now()->copy()->subDays(30)
        );

        $i = array_flip($data['schedules']['columns']);
        $row = collect($data['schedules']['rows'])
            ->first(fn ($r) => $r[$i['paid_tickets_total']] > 0);

        $this->assertNotNull($row);
        $this->assertNull($row[$i['gmv_currency']], 'GBP + EUR is not an amount of anything');
        $this->assertNull($row[$i['gmv_recent']]);
        // The ticket COUNT is still currency-free, so it keeps working.
        $this->assertSame(2, $row[$i['paid_tickets_total']]);

        // ...and each currency is still sizeable on its own. One of the four paying sellers in
        // the 2026-09-28 export sold in two currencies and was invisible without this.
        $byCurrency = $row[$i['gmv_recent_by_currency']];
        $this->assertSame(['EUR', 'GBP'], collect($byCurrency)->keys()->sort()->values()->all());
        $this->assertCount(GrowthExportService::RECENT_MONTHS, $byCurrency['GBP']);
        $this->assertSame(50.0, end($byCurrency['GBP']));
        $this->assertSame(40.0, end($byCurrency['EUR']));
    }

    /**
     * The demo's hourly re-seed creates paid Stripe sales stamped paid_at = now(), so they land
     * in the current month of gmv_by_currency. Before this, they were ~$12k-15k of the USD
     * "revenue" in every export - more than every real seller combined.
     */
    public function test_demo_sales_are_excluded_from_gmv_by_currency(): void
    {
        // Seeded the way DemoService seeds it: the schedule's contact address is DEMO_EMAIL.
        $demo = $this->createRole($this->createOwner(), 'venue', [
            'subdomain' => 'demo-springfield-hall', 'email' => DemoService::DEMO_EMAIL,
        ]);
        $demoEvent = $this->createEvent($demo, ['ticket_currency_code' => 'USD']);
        $demoTicket = $this->createTicket($demoEvent, ['price' => 50, 'quantity' => 100]);
        $this->createSale($demoEvent, $demo, ['status' => 'paid', 'payment_amount' => 5000], $demoTicket, 1);

        $real = $this->freeRole();
        $realEvent = $this->createEvent($real, ['ticket_currency_code' => 'USD']);
        $realTicket = $this->createTicket($realEvent, ['price' => 10, 'quantity' => 100]);
        $this->createSale($realEvent, $real, ['status' => 'paid', 'payment_amount' => 30], $realTicket, 3);

        // A real schedule named before cleanSubdomain() reserved the prefix. Its money is real,
        // and with only a handful of sellers, hiding one is the worse error.
        $legacy = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'demo-night']);
        $legacyEvent = $this->createEvent($legacy, ['ticket_currency_code' => 'USD']);
        $legacyTicket = $this->createTicket($legacyEvent, ['price' => 20, 'quantity' => 100]);
        $this->createSale($legacyEvent, $legacy, ['status' => 'paid', 'payment_amount' => 20], $legacyTicket, 1);

        $usd = collect($this->build()['monetization']['gmv_by_currency'])->where('currency', 'USD');

        $this->assertSame(50.0, $usd->sum('amount'), 'the demo sale is excluded, demo-night is not');
        $this->assertSame(2, $usd->sum('sales'));
    }

    /**
     * The edge cache moved the visit counters onto a JS beacon, and daily visitors fell ~4x
     * across that line. Each month says which way it was counted so nobody reads the step as
     * lost traffic (or as a fixed bot problem) without checking.
     */
    public function test_traffic_says_how_each_month_was_counted(): void
    {
        config(['app.is_nexus' => true]);

        foreach (['2026-08-10', '2026-09-10', '2026-10-10'] as $date) {
            MarketingDailyStat::create(['date' => $date, 'visitors' => 10, 'page_views' => 12, 'signup_views' => 1]);
        }

        $basis = collect($this->build()['traffic'])->pluck('visitors_basis', 'month');

        $this->assertSame('server', $basis['2026-08']);
        $this->assertSame('mixed', $basis['2026-09'], 'the rebase fell mid-September');
        $this->assertSame('beacon', $basis['2026-10']);
    }

    public function test_schedule_rows_carry_capture_counts(): void
    {
        // Without these the feature is invisible to measurement, and worse than invisible: a fully
        // successful capture change moves `followers` by exactly zero, because checkout capture
        // writes role_subscribers and never calls linkAccount(). `followers` is also all-time while
        // views_90d is a 90-day window, so the two cannot be divided into a rate at all.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        \App\Models\EventInterest::create([
            'event_id' => $event->id,
            'event_date' => $event->getStartDateTime(null, true, $event->scheduleTimezone())->format('Y-m-d'),
            'email' => 'fan@fans.test',
            'confirmed_at' => now(),
            'token' => \App\Models\EventInterest::newToken(),
        ]);

        \App\Models\RoleSubscriber::create([
            'role_id' => $role->id,
            'email' => 'reader@fans.test',
            'name' => 'A Reader',
            'confirmed_at' => now(),
            'token' => \App\Models\RoleSubscriber::newToken(),
        ]);

        $data = $this->build();
        $columns = $data['schedules']['columns'];

        // The only schedule in the fixture. `sid` is hashId('s', ...), not the raw id, so it cannot
        // be looked up by $role->id.
        $this->assertCount(1, $data['schedules']['rows']);
        $row = $data['schedules']['rows'][0];
        $this->assertSame(1, $row[array_search('subscribers', $columns)]);
        $this->assertSame(1, $row[array_search('interests_90d', $columns)]);
        $this->assertSame(1, $row[array_search('interests_total', $columns)]);
    }

    /**
     * The acquisition breakdowns used to stop at saved_event, which ranked landing pages by an
     * outcome that does not predict revenue: on this install a schedule that has sold recently
     * pays at 55.6% against 0.38% for one with no ticket type, so "which page produces sellers"
     * is the only version of the question worth asking of ~156 marketing pages.
     */
    public function test_the_landing_page_rollup_carries_the_ticket_stages(): void
    {
        $seller = $this->createOwner();
        $seller->update(['landing_page' => 'https://eventschedule.com/features/ticketing?utm_source=x']);
        $this->createTicket($this->createEvent($this->freeRole($seller)), ['price' => 20]);

        $browser = $this->createOwner();
        $browser->update(['landing_page' => 'https://eventschedule.com/for-musicians']);
        $this->createEvent($this->freeRole($browser));

        $byPath = collect($this->build()['acquisition']['by_landing_path']);

        // pathOf() strips the query string, so the two arrivals on one page group together
        // rather than splitting per campaign.
        $ticketing = $byPath->firstWhere('key', '/features/ticketing');
        $this->assertNotNull($ticketing, 'the landing path is not being recorded');
        $this->assertSame(1, $ticketing['signups']);
        $this->assertSame(1, $ticketing['saved_event']);
        $this->assertSame(1, $ticketing['saved_ticket']);
        $this->assertSame(1, $ticketing['saved_paid_ticket']);

        $musicians = $byPath->firstWhere('key', '/for-musicians');
        $this->assertSame(1, $musicians['saved_event']);
        $this->assertSame(0, $musicians['saved_ticket'], 'an event is not a ticket type');
        $this->assertSame(0, $musicians['saved_paid_ticket']);
    }

    /**
     * The per-user ticket map has to reproduce Event::tickets() - is_deleted = false AND
     * is_addon = false - because that is what the funnel's own saved_ticket stage goes through.
     * Without both clauses a schedule that only ever sold parking, or one whose ticket type was
     * deleted, would read here as a seller and not there, and the two rails would disagree about
     * the same person while both looked plausible.
     */
    public function test_the_rollup_applies_the_event_tickets_contract(): void
    {
        $owner = $this->createOwner();
        $owner->update(['landing_page' => 'https://eventschedule.com/pricing']);
        $event = $this->createEvent($this->freeRole($owner));

        $path = fn () => collect($this->build()['acquisition']['by_landing_path'])
            ->firstWhere('key', '/pricing');

        // A deleted type and a paid ADD-ON are both rows in `tickets`, and neither is a ticket type.
        $this->createTicket($event, ['type' => 'Gone', 'price' => 25, 'is_deleted' => true]);
        $this->createTicket($event, ['type' => 'Parking', 'price' => 30, 'is_addon' => true]);
        $this->assertSame(0, $path()['saved_ticket'], 'a deleted type or an add-on is not a ticket type');
        $this->assertSame(0, $path()['saved_paid_ticket']);

        // A free type advances saved_ticket alone - it carries no intent to take money.
        $this->createTicket($event, ['type' => 'RSVP', 'price' => 0]);
        $this->assertSame(1, $path()['saved_ticket']);
        $this->assertSame(0, $path()['saved_paid_ticket']);

        // A priced type advances both, and counts the USER once however many types they made.
        $this->createTicket($event, ['type' => 'Paid', 'price' => 20]);
        $this->createTicket($event, ['type' => 'Also paid', 'price' => 40]);
        $this->assertSame(1, $path()['saved_ticket'], 'one user, not one ticket type');
        $this->assertSame(1, $path()['saved_paid_ticket']);
    }

    /**
     * acquisition and funnel deliberately count DIFFERENT populations, and the ticket stages now
     * appear in both - so the tempting cross-check "the rollup should sum to the funnel stage"
     * is false on real data and must not be "fixed". cohort() keeps only organizer-intent
     * signups inside the selected window; signupRows() takes the most recent rowCap() verified
     * users whatever their intent or age.
     */
    public function test_acquisition_and_the_funnel_count_different_populations(): void
    {
        $follower = $this->createOwner();
        $follower->update([
            'signup_intent' => 'follow',
            'landing_page' => 'https://eventschedule.com/features/ticketing',
        ]);
        $this->createTicket($this->createEvent($this->freeRole($follower)), ['price' => 20]);

        $data = $this->build();

        $ticketing = collect($data['acquisition']['by_landing_path'])->firstWhere('key', '/features/ticketing');
        $this->assertSame(1, $ticketing['saved_paid_ticket'], 'acquisition counts every verified account');

        $stages = array_column($data['funnel']['stages'], 'count', 'key');
        $this->assertSame(0, $stages['saved_paid_ticket'], 'the funnel is the organizer cohort only');
    }

    // ---------------------------------------------------------------------
    // Schema 8: anonymised attribution
    // ---------------------------------------------------------------------

    /** A verified signup with raw attribution columns, the way the capture middleware leaves them. */
    private function signupWith(array $attrs): User
    {
        $user = $this->createOwner();
        $user->forceFill($attrs)->save();

        return $user;
    }

    /** @return array<string, int> exported value => signups */
    private function signupValues(array $data, string $column): array
    {
        $i = array_flip($data['signups']['columns']);

        return array_count_values(array_map(
            fn ($v) => (string) $v,
            array_filter(array_column($data['signups']['rows'], $i[$column]), fn ($v) => $v !== null)
        ));
    }

    /**
     * Landing paths in the shape production stores them - $request->path(), so no host and no
     * leading slash - which none of the older fixtures used: they all passed full URLs, which
     * normalise either way and so could not catch the leading-slash lookup against the sitemap.
     */
    public function test_landing_paths_keep_our_pages_and_hide_everything_rare(): void
    {
        DB::table('blog_posts')->insert([
            'title' => 'Sell tickets', 'slug' => 'how-to-sell-tickets-2026', 'content' => '...',
            'is_published' => true, 'published_at' => now()->subDay(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->signupWith(['landing_page' => 'for-musicians']);                // one signup, but our page
        $this->signupWith(['landing_page' => 'how-to-sell-tickets-2026']);     // a blog post, from blog.<domain>
        $this->signupWith(['landing_page' => 'summa-30th-anniversary-party']); // a tenant event slug, alone
        // One invisible format character (pasted from a chat app) used to split this group in two.
        $this->signupWith(['landing_page' => 'guest-add%E2%81%A0']);
        $this->signupWith(['landing_page' => 'guest-add']);
        $this->signupWith(['landing_page' => '/Guest-Add/']);
        // One buyer's ticket link, forwarded to friends who all signed up: three clears the group
        // size, and the secret in it must still never be exported.
        foreach (range(1, 3) as $n) {
            $this->signupWith(['landing_page' => 'ticket/view/x7Kq2/k3Jd9sLq2mZx8vB1nC4tY6wR0pE5hG7a']);
        }

        $data = $this->build();
        $paths = $this->signupValues($data, 'landing_path');
        $json = json_encode($data);

        $this->assertSame(1, $paths['/for-musicians'] ?? null, 'a marketing page is kept at any count, with its slash');
        $this->assertSame(1, $paths['/blog/how-to-sell-tickets-2026'] ?? null, 'a published blog post is named as one');
        $this->assertSame(3, $paths['/guest-add'] ?? null, 'normalised before counting, so the three are one group');
        $this->assertSame(3, $paths['/ticket/view/x7kq2/:token'] ?? null, 'the shared link survives, its secret does not');
        $this->assertSame(1, $paths['(other)'] ?? null, 'the lone tenant slug');

        $this->assertStringNotContainsString('summa-30th', $json);
        $this->assertStringNotContainsString('k3Jd9sLq2mZx8vB1nC4tY6wR0pE5hG7a', $json);
        $this->assertStringNotContainsString(strtolower('k3Jd9sLq2mZx8vB1nC4tY6wR0pE5hG7a'), $json);

        // The rollups are built from the same rows, so they inherit all of it.
        $byPath = collect($data['acquisition']['by_landing_path'])->pluck('signups', 'key');
        $this->assertSame(3, $byPath['/guest-add']);
        $this->assertSame(1, $byPath['(other)']);
    }

    public function test_referrers_are_canonicalised_and_only_shared_hosts_survive(): void
    {
        $role = $this->freeRole();
        // custom_domain always holds a URL. Written directly: the model's hooks would try to
        // provision the domain.
        DB::table('roles')->where('id', $role->id)->update(['custom_domain' => 'https://events.example-venue.com']);

        $this->signupWith(['referrer_url' => 'https://claude.ai/chat/0f3c']);                 // one, but a platform
        $this->signupWith(['referrer_url' => 'https://www.google.co.uk/']);
        $this->signupWith(['referrer_url' => 'android-app://com.google.android.googlequicksearchbox/']);
        $this->signupWith(['referrer_url' => 'https://johns-personal-blog.net/post?ref=me']);  // one, nobody's platform
        foreach (range(1, 3) as $n) {
            $this->signupWith(['referrer_url' => 'http://203.0.113.7:8080/']);
            $this->signupWith(['referrer_url' => 'https://events.example-venue.com/calendar']);
            $this->signupWith(['referrer_url' => 'https://www.bigvenue-partner.org/whats-on']);
        }

        $data = $this->build();
        $hosts = $this->signupValues($data, 'referrer_domain');
        $channels = $this->signupValues($data, 'referrer_channel');
        $json = json_encode($data);

        $this->assertSame(1, $hosts['claude'] ?? null, 'a rare AI referrer is still visible');
        $this->assertSame(2, $hosts['google'] ?? null, 'google.co.uk and the Android search app are one source');
        $this->assertSame(3, $hosts['(ip)'] ?? null, 'an IP never survives, however many share it');
        $this->assertSame(3, $hosts['(schedule)'] ?? null, 'a customer domain is a channel, not a name');
        $this->assertSame(3, $hosts['bigvenue-partner.org'] ?? null, 'a host three signups share is kept');
        $this->assertSame(1, $hosts['(other)'] ?? null, 'a site that sent one person is as good as their name');

        $this->assertSame(1, $channels['ai']);
        $this->assertSame(2, $channels['search']);
        $this->assertSame(3, $channels['schedule']);

        foreach (['johns-personal-blog', '203.0.113.7', 'example-venue.com'] as $leak) {
            $this->assertStringNotContainsString($leak, $json, "the export leaked: {$leak}");
        }

        $this->assertSame(1, collect($data['acquisition']['by_referrer_channel'])->firstWhere('key', 'ai')['signups']);
    }

    /**
     * A schedule's custom domain is not the only host that names its customer: the domain it sits
     * under and its siblings do too. But a public suffix must never become a "customer domain", or
     * one venue on venue.co.uk would fold every .co.uk referrer into "(schedule)".
     */
    public function test_hosts_under_a_customer_domain_are_schedule_referrers_but_public_suffixes_are_not(): void
    {
        $a = $this->freeRole();
        $b = $this->freeRole();
        DB::table('roles')->where('id', $a->id)->update(['custom_domain' => 'https://events.examplevenue.com']);
        DB::table('roles')->where('id', $b->id)->update(['custom_domain' => 'https://venue.co.uk']);

        foreach (range(1, 3) as $n) {
            $this->signupWith(['referrer_url' => 'https://tickets.examplevenue.com/']);   // a sibling
            $this->signupWith(['referrer_url' => 'https://www.examplevenue.com/']);       // the parent
            $this->signupWith(['referrer_url' => 'https://other-business.co.uk/']);       // shares only the suffix
            $this->signupWith(['referrer_url' => 'https://calendar.google.com/calendar']); // an invite, not a search
        }

        $data = $this->build();
        $hosts = $this->signupValues($data, 'referrer_domain');

        $this->assertSame(6, $hosts['(schedule)'] ?? null);
        $this->assertSame(3, $hosts['other-business.co.uk'] ?? null, 'co.uk is a public suffix, not a customer');
        $this->assertSame(3, $hosts['google-calendar'] ?? null);
        $this->assertSame(3, $this->signupValues($data, 'referrer_channel')['calendar'] ?? null);
        $this->assertArrayNotHasKey('search', $this->signupValues($data, 'referrer_channel'));
        $this->assertStringNotContainsString('examplevenue', json_encode($data));
    }

    /** An address in a path (an unsubscribe link, %40-encoded) never survives, however many share it. */
    public function test_a_landing_path_with_an_address_in_it_is_redacted(): void
    {
        foreach (range(1, 3) as $n) {
            $this->signupWith(['landing_page' => 'nl/manage/jane.doe%40gmail.com']);
        }

        $data = $this->build();

        $this->assertSame(3, $this->signupValues($data, 'landing_path')['(redacted)'] ?? null);
        $this->assertStringNotContainsString('jane.doe', json_encode($data));
    }

    public function test_a_utm_with_an_address_in_it_is_redacted_however_many_share_it(): void
    {
        foreach (range(1, 3) as $n) {
            $this->signupWith(['utm_source' => 'newsletter-jane.doe@gmail.com', 'utm_medium' => 'email']);
        }
        $this->signupWith(['utm_source' => 'one-off-campaign']);

        $data = $this->build();
        $sources = $this->signupValues($data, 'utm_source');

        $this->assertSame(3, $sources['(redacted)'] ?? null);
        $this->assertSame(1, $sources['(other)'] ?? null);
        $this->assertSame(3, $this->signupValues($data, 'utm_medium')['email'] ?? null);
        $this->assertStringNotContainsString('jane.doe', json_encode($data));
    }

    /**
     * A top-N cut with no remainder made a rollup read as "this is everyone". Marketing paths are
     * exempt from the group-size rule, so 52 of them, one signup each, make 52 groups.
     */
    public function test_rollups_fold_the_tail_into_a_rest_row_that_reconciles(): void
    {
        $pages = array_slice(array_keys(config('sitemap_lastmod')), 0, 52);
        $this->assertCount(52, $pages);
        foreach ($pages as $page) {
            $this->signupWith(['landing_page' => ltrim($page, '/') ?: '/']);
        }

        $byPath = $this->build()['acquisition']['by_landing_path'];

        $this->assertCount(51, $byPath);
        $rest = end($byPath);
        $this->assertSame('(rest)', $rest['key']);
        $this->assertSame(2, $rest['groups']);
        $this->assertSame(52, array_sum(array_column($byPath, 'signups')));
    }

    // ---------------------------------------------------------------------
    // Schema 8: corrected definitions
    // ---------------------------------------------------------------------

    /** The row for one schedule. sid is hashId('s', id), so recompute it rather than search by id. */
    private function scheduleRow(array $data, Role $role): array
    {
        $i = array_flip($data['schedules']['columns']);
        $sid = 's:'.substr(hash_hmac('sha256', (string) $role->id, (string) config('app.key')), 0, 12);
        $row = collect($data['schedules']['rows'])->firstWhere($i['sid'], $sid);
        $this->assertNotNull($row, "no schedule row for role {$role->id}");

        return array_combine($data['schedules']['columns'], $row);
    }

    /**
     * A venue's event with a talent booked on it: one sale, and before schema 8 BOTH schedules were
     * credited with it - so a talent that never sold anything read as a seller, and per-schedule
     * revenue summed to more than the platform total.
     */
    public function test_sales_and_ticket_types_are_credited_to_the_seller_not_every_listed_schedule(): void
    {
        $venue = $this->freeRole(null, 'venue');
        $talent = $this->freeRole(null, 'talent');

        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);
        $ticket = $this->createTicket($event, ['price' => 25, 'quantity' => 100]);
        $this->createSale($event, $venue, ['status' => 'paid', 'payment_amount' => 50], $ticket, 2);

        $data = $this->build();
        $seller = $this->scheduleRow($data, $venue);
        $listed = $this->scheduleRow($data, $talent);

        $this->assertSame(2, $seller['paid_tickets_total']);
        $this->assertSame(2, $seller['paid_tickets_90d']);
        $this->assertSame(1, $seller['paid_ticket_types']);
        $this->assertNotNull($seller['first_paid_sale_month']);

        $this->assertSame(0, $listed['paid_tickets_total'], 'listed on the event, but not the seller');
        $this->assertSame(0, $listed['paid_ticket_types']);
        $this->assertNull($listed['first_paid_sale_month']);
        $this->assertNull($listed['gmv_currency']);
        // ...though it is the talent's event too, so it is still on its page.
        $this->assertSame(1, $listed['events_total']);

        $gmv = array_sum(array_column($data['monetization']['gmv_by_currency'], 'amount'));
        $this->assertSame(50.0, $gmv);
        $this->assertSame(50.0, array_sum($seller['gmv_recent'] ?? []), 'the per-schedule sum is the platform total');
    }

    /**
     * Legacy events have no creator_role_id - createEvent() leaves it null, like rows written
     * before the column existed - and keep the old every-listed-schedule credit, rather than
     * crediting nobody.
     */
    public function test_an_event_with_no_creator_falls_back_to_its_listed_schedules(): void
    {
        $role = $this->freeRole();
        $event = $this->createEvent($role);
        $this->assertNull($event->creator_role_id);
        $ticket = $this->createTicket($event, ['price' => 10, 'quantity' => 100]);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 10], $ticket, 1);

        $this->assertSame(1, $this->scheduleRow($this->build(), $role)['paid_tickets_total']);
    }

    public function test_event_counts_only_include_events_the_schedule_created_or_accepted(): void
    {
        $venue = $this->freeRole(null, 'venue');
        $promoter = $this->freeRole(null, 'curator');

        // The promoter's own event, requested onto the venue and never accepted there.
        $event = $this->createEvent($promoter, ['creator_role_id' => $promoter->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => null]);
        $declined = $this->createEvent($promoter, ['creator_role_id' => $promoter->id]);
        $declined->roles()->attach($venue->id, ['is_accepted' => false]);

        $data = $this->build();

        $this->assertSame(0, $this->scheduleRow($data, $venue)['events_total'], 'pending and declined requests are not the venue\'s events');
        $this->assertSame(2, $this->scheduleRow($data, $promoter)['events_total']);
    }

    /**
     * events_recent_90d read updated_at, and system writes bump it: Translate::markChecked() touches
     * every event it checks, calendar sync rewrites rows. A schedule nobody had opened in a year
     * read as active.
     */
    public function test_an_old_event_touched_by_the_system_does_not_make_a_schedule_active(): void
    {
        $role = $this->freeRole();
        $event = $this->createEvent($role);
        DB::table('events')->where('id', $event->id)->update(['created_at' => now()->subDays(200), 'updated_at' => now()]);

        $data = $this->build();

        $this->assertSame(0, $this->scheduleRow($data, $role)['events_recent_90d']);
        $month = collect($data['retention'])->firstWhere('month', $role->created_at->format('Y-m'));
        $this->assertSame(0, $month['active_recently']);
    }

    /**
     * Paying is billing. A comp, a legacy plan_expires row and a Stripe trial all have a paid TIER,
     * and every "paid vs free" section used to count them as customers.
     */
    public function test_paying_means_billing_not_a_paid_tier(): void
    {
        config(['services.stripe_platform.price_monthly' => 'price_test_monthly']);

        $paying = $this->createRole($this->createOwner(), 'venue', ['plan_type' => 'pro', 'plan_expires' => null, 'plan_source' => null]);
        $paying->subscriptions()->create(['type' => 'default', 'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'active', 'stripe_price' => 'price_test_monthly', 'quantity' => 1]);
        $comp = $this->createRole($this->createOwner(), 'venue', ['plan_type' => 'pro',
            'plan_expires' => now()->addYear()->format('Y-m-d'), 'plan_source' => 'admin']);
        $legacy = $this->createRole($this->createOwner(), 'venue', ['plan_type' => 'pro',
            'plan_expires' => now()->addYear()->format('Y-m-d'), 'plan_source' => null]);
        $declined = $this->freeRole();
        $declined->subscriptions()->create(['type' => 'default', 'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'incomplete', 'stripe_price' => 'price_test_monthly', 'quantity' => 1]);

        $data = $this->build();

        $this->assertTrue($this->scheduleRow($data, $paying)['billing']);
        $this->assertTrue($this->scheduleRow($data, $paying)['ever_subscribed']);
        $this->assertFalse($this->scheduleRow($data, $comp)['billing']);
        $this->assertSame('pro', $this->scheduleRow($data, $comp)['plan'], 'the tier is still reported as the tier');
        $this->assertFalse($this->scheduleRow($data, $legacy)['billing']);

        // A declined first checkout is neither a subscription nor an upgrade.
        $this->assertFalse($this->scheduleRow($data, $declined)['ever_subscribed']);
        $this->assertNull($this->scheduleRow($data, $declined)['days_to_upgrade']);

        $this->assertSame(1, $data['payers_vs_free']['paid']['schedules']);
        $this->assertSame(3, $data['payers_vs_free']['free']['schedules'], 'the comp and the legacy plan are not payers');

        $venue = collect($data['segments']['by_schedule_type'])->firstWhere('key', 'venue');
        $this->assertSame(1, $venue['billing']);
        $this->assertSame(3, $venue['paid_plan'], 'paid_plan stays the tier count');

        $this->assertSame(1, array_sum(array_column($data['retention'], 'paid')));

        $this->assertSame(1, $data['monetization']['by_plan_source']['stripe'], '"stripe" matches billing_subscriptions');
        $this->assertSame(1, $data['monetization']['by_plan_source']['legacy']);
        $this->assertSame(1, $data['monetization']['by_plan_source']['admin']);
    }

    /**
     * Demo is demo CONTENT. The `demo-%` shape hid real schedules named before the prefix was
     * reserved, and kept the /examples showcase schedules, which sit on ordinary subdomains under
     * ordinary accounts and are recognisable only by their contact address.
     */
    public function test_demo_content_is_excluded_by_what_it_is_not_by_its_subdomain(): void
    {
        $real = $this->freeRole(null, 'venue');
        DB::table('roles')->where('id', $real->id)->update(['subdomain' => 'demo-night-'.Str::lower(Str::random(4))]);
        $showcase = $this->freeRole(null, 'venue');
        DB::table('roles')->where('id', $showcase->id)->update(['email' => DemoService::DEMO_EMAIL]);

        $data = $this->build();
        $i = array_flip($data['schedules']['columns']);
        $sids = array_column($data['schedules']['rows'], $i['sid']);

        $this->assertContains($this->scheduleRow($data, $real)['sid'], $sids, 'a real demo-something schedule is a real schedule');
        $this->assertCount(1, $sids, 'the showcase schedule is fabricated');
    }

    public function test_free_pressure_ever_sold_means_ever(): void
    {
        $role = $this->freeRole();
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 10, 'quantity' => 100]);
        $sale = $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 10], $ticket, 1);
        DB::table('sales')->where('id', $sale->id)->update(['paid_at' => now()->subYear()]);

        $pressure = $this->build()['free_pressure'];

        $this->assertSame(1, $pressure['ever_sold_paid'], 'a sale a year ago is still a sale');
        $this->assertSame(1, $pressure['peak_month_paid_tickets']['0'], 'but not in the last six months');
    }

    public function test_the_weekly_funnel_trend_is_keyed_by_iso_week(): void
    {
        $trend = app(GrowthExportService::class)->funnelTrendData(now()->subDays(60), now());

        $this->assertSame('weekly', $trend['granularity']);
        $this->assertSame(count($trend['labels']), count($trend['periods']));
        foreach ($trend['periods'] as $period) {
            $this->assertMatchesRegularExpression('/^\d{4}-W\d{2}$/', $period);
        }
    }

    // ---------------------------------------------------------------------
    // Schema 9: new data
    // ---------------------------------------------------------------------

    /** daily[]'s row for one date, as metric => count. */
    /**
     * Schema 12. An imported event was indistinguishable from one typed in, so the export could
     * not say whether importing is what fills a schedule. And two Google fields read columns that
     * nothing has written since the ids moved to calendar_syncs and the owner's pivot.
     */
    public function test_imports_are_counted_by_source_and_google_reads_where_the_ids_live(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeRole($owner, 'curator');
        $venue = $this->freeRole($owner);

        $byHand = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $flyer = $this->createEvent($role, ['creator_role_id' => $role->id, 'import_source' => 'ai']);
        $this->createEvent($role, ['creator_role_id' => $role->id, 'import_source' => 'ics']);
        $this->createEvent($role, ['creator_role_id' => $role->id, 'import_source' => 'ics']);
        $pulled = $this->createEvent($role, ['creator_role_id' => $role->id, 'import_source' => 'google']);
        // Listed on the venue's schedule too: the import is the curator's, not the venue's.
        $flyer->roles()->attach($venue->id, ['is_accepted' => true]);

        // The owner's sync links two events to Google entries: one pulled in, one pushed out.
        foreach ([$pulled, $byHand] as $event) {
            DB::table('calendar_syncs')->insert(['user_id' => $owner->id, 'event_id' => $event->id, 'role_id' => $role->id,
                'google_event_id' => 'g'.$event->id, 'created_at' => now(), 'updated_at' => now()]);
        }
        // A team member's own "sync to my calendar" is not the schedule's sync.
        $member = $this->createOwner();
        $role->users()->attach($member->id, ['level' => 'admin']);
        DB::table('calendar_syncs')->insert(['user_id' => $member->id, 'event_id' => $flyer->id, 'role_id' => $role->id,
            'google_event_id' => 'member', 'created_at' => now(), 'updated_at' => now()]);

        // The column the old flag read is set and the direction is not, and the other way round.
        DB::table('roles')->where('id', $venue->id)->update(['google_calendar_id' => 'legacy@group.calendar.google.com']);
        DB::table('roles')->where('id', $role->id)->update(['sync_direction' => 'from']);

        $data = $this->build();
        $sources = $this->scheduleRow($data, $role)['events_by_source'];

        $this->assertSame(5, $sources['created']);
        $this->assertSame(4, $sources['imported']);
        $this->assertSame(1, $sources['imported_ai']);
        $this->assertSame(2, $sources['imported_ics']);
        $this->assertSame(1, $sources['imported_google']);
        $this->assertSame(0, $sources['imported_eventbrite']);
        // Every source in the vocabulary has a key, used or not.
        foreach (\App\Models\Event::IMPORT_SOURCES as $source) {
            $this->assertArrayHasKey('imported_'.$source, $sources);
        }
        $this->assertSame(2, $sources['google']);

        $venueSources = $this->scheduleRow($data, $venue)['events_by_source'];
        $this->assertSame(1, $venueSources['other_schedules']);
        $this->assertSame(0, $venueSources['imported']);

        $this->assertContains('gcal', $this->scheduleRow($data, $role)['features']);
        $this->assertNotContains('gcal', $this->scheduleRow($data, $venue)['features']);

        $today = $this->dailyRow($data, now()->toDateString());
        $this->assertSame(5, $today['events_created']);
        $this->assertSame(4, $today['events_imported']);
    }

    private function dailyRow(array $data, string $date): array
    {
        $row = collect($data['daily']['rows'])->firstWhere(0, $date);
        $this->assertNotNull($row, "no daily row for {$date}");

        return array_combine($data['daily']['columns'], $row);
    }

    /**
     * Monthly figures could not place a conversion either side of a mid-month change. daily[] dates
     * each first step to the day, and only the FIRST: an older schedule's owner adding another
     * schedule today is not a first schedule.
     */
    public function test_daily_dates_each_first_step_to_the_day(): void
    {
        $role = $this->freeRole();
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 20], $ticket, 1);

        $veteran = $this->createOwner();
        $old = $this->freeRole($veteran);
        DB::table('roles')->where('id', $old->id)->update(['created_at' => now()->subDays(400)]);
        DB::table('users')->where('id', $veteran->id)->update(['created_at' => now()->subDays(400)]);
        $this->freeRole($veteran);

        $data = $this->build();
        $today = $this->dailyRow($data, now()->toDateString());

        $this->assertCount(180, $data['daily']['rows']);
        $this->assertSame(1, $today['signups_organizer'], 'the veteran signed up 400 days ago');
        $this->assertSame(1, $today['first_schedule'], 'the veteran\'s second schedule is not a first');
        $this->assertSame(1, $today['first_event']);
        $this->assertSame(1, $today['first_ticket_type']);
        $this->assertSame(1, $today['first_paid_ticket_type']);
        $this->assertSame(1, $today['first_paid_sale']);
        $this->assertSame(1, $today['paid_orders']);
    }

    /**
     * What followed a nudge, within 14 days, counted only once the 14 days have passed - so the
     * newest nudges cannot read as failures.
     */
    public function test_nudge_outcomes_count_only_what_followed_within_the_window(): void
    {
        $acted = $this->freeRole();
        $late = $this->freeRole();
        $fresh = $this->freeRole();
        foreach ([[$acted, 20], [$late, 20], [$fresh, 3]] as [$role, $daysAgo]) {
            DB::table('schedule_nudges')->insert(['role_id' => $role->id, 'nudge_key' => 'no_ticket_type_free', 'created_at' => now()->subDays($daysAgo)]);
        }

        $event = $this->createEvent($acted, ['creator_role_id' => $acted->id]);
        DB::table('events')->where('id', $event->id)->update(['created_at' => now()->subDays(10)]);
        $ticket = $this->createTicket($event, ['price' => 10, 'quantity' => 10]);
        DB::table('tickets')->where('id', $ticket->id)->update(['created_at' => now()->subDays(10)]);

        $tooLate = $this->createEvent($late, ['creator_role_id' => $late->id]);
        DB::table('events')->where('id', $tooLate->id)->update(['created_at' => now()->subDays(2)]);
        $this->createEvent($fresh, ['creator_role_id' => $fresh->id]);

        $outcome = $this->build()['nudge_outcomes']['no_ticket_type_free'];

        $this->assertSame(3, $outcome['sent']);
        $this->assertSame(2, $outcome['matured'], 'a 3-day-old nudge has not had its 14 days');
        $this->assertSame(1, $outcome['acted_event'], 'an event 18 days after the nudge is outside the window');
        $this->assertSame(1, $outcome['acted_ticket_type']);
        $this->assertSame(0, $outcome['acted_paid_sale']);
    }

    /**
     * The audience side: a fan buying again is a returning buyer, and an attendee whose account
     * goes on to make a schedule is the viral loop the payload had no way to see.
     */
    public function test_buyers_count_returning_fans_and_attendees_who_became_organizers(): void
    {
        $role = $this->freeRole();
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 15, 'quantity' => 100]);

        $earlier = $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 15, 'email' => 'fan@fans.test'], $ticket, 1);
        DB::table('sales')->where('id', $earlier->id)->update(['paid_at' => now()->startOfMonth()->subMonths(2)->addDays(3)]);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 15, 'email' => ' Fan@Fans.test'], $ticket, 1);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_method' => 'rsvp', 'email' => 'newbie@fans.test']);

        $newbie = $this->createOwner();
        DB::table('users')->where('id', $newbie->id)->update(['email' => 'newbie@fans.test']);
        $theirs = $this->freeRole($newbie);
        DB::table('roles')->where('id', $theirs->id)->update(['created_at' => now()->addMinutes(5)]);

        // An organizer already: test-bought on their own event, then added a second schedule. Not a
        // conversion - the loop is attendees whose FIRST schedule came after they attended.
        $organizer = $this->createOwner();
        DB::table('users')->where('id', $organizer->id)->update(['email' => 'organizer@fans.test']);
        $mine = $this->freeRole($organizer);
        DB::table('roles')->where('id', $mine->id)->update(['created_at' => now()->subYear()]);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_method' => 'rsvp', 'email' => 'organizer@fans.test']);
        $second = $this->freeRole($organizer);
        DB::table('roles')->where('id', $second->id)->update(['created_at' => now()->addMinutes(5)]);

        // A bulk-imported attendee list is not people who came.
        $this->createSale($event, $role, ['status' => 'paid', 'payment_method' => 'import', 'email' => 'imported@fans.test']);

        $month = collect($this->build()['buyers'])->firstWhere('month', now()->format('Y-m'));

        $this->assertSame(1, $month['paid_orders']);
        $this->assertSame(1, $month['buyers'], 'one person, however the address was typed');
        $this->assertSame(1, $month['returning_buyers']);
        $this->assertSame(0, $month['new_buyers']);
        $this->assertSame(2, $month['rsvps']);
        $this->assertSame(2, $month['new_attendees'], 'the newbie and the organizer; the fan first attended two months ago, the import never did');
        $this->assertSame(1, $month['attendees_who_became_organizers'], 'the newbie, not the organizer who already had a schedule');
    }

    public function test_reach_counts_this_weeks_audience(): void
    {
        $role = $this->freeRole();
        $this->followRole($this->createOwner(), $role);
        \App\Models\RoleSubscriber::create(['role_id' => $role->id, 'email' => 'reader@fans.test',
            'confirmed_at' => now(), 'token' => \App\Models\RoleSubscriber::newToken()]);
        \App\Models\AnalyticsDaily::create(['role_id' => $role->id, 'date' => now()->toDateString(), 'desktop_views' => 7]);

        $reach = $this->build()['reach'];
        $week = array_combine($reach['columns'], end($reach['rows']));

        $this->assertSame(now()->format('o-\WW'), $week['week']);
        $this->assertSame(1, $week['followers_added']);
        $this->assertSame(1, $week['subscribers_added']);
        $this->assertSame(7, $week['page_views']);
        $this->assertCount(26, $reach['rows']);
    }

    public function test_schedule_rows_carry_gateways_sources_and_the_features_actually_used(): void
    {
        $owner = $this->createOwner();
        DB::table('users')->where('id', $owner->id)->update(['stripe_account_id' => 'acct_TEST', 'stripe_completed_at' => now()]);
        $role = $this->freeRole($owner);

        $own = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $this->createTicket($own, ['price' => 50, 'quantity' => 10, 'is_pass' => true]);
        $this->createEvent($role, ['creator_role_id' => $role->id, 'is_guest_submission' => true]);

        $teammate = $this->createOwner();
        $role->users()->attach($teammate->id, ['level' => 'admin']);
        $this->followRole($this->createOwner(), $role);

        DB::table('dismissed_next_steps')->insert(['user_id' => $owner->id, 'role_id' => $role->id,
            'step_type' => 'next_step_tickets', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('usage_daily')->insert(['date' => now()->toDateString(), 'operation' => 'gemini_parse_event', 'role_id' => $role->id, 'count' => 2]);

        $plain = $this->freeRole();

        $data = $this->build();
        $row = $this->scheduleRow($data, $role);

        $this->assertSame(['stripe'], $row['gateways']);
        $this->assertSame(now()->format('Y-m'), $row['stripe_connected_month']);
        $this->assertSame(['tickets'], $row['dismissed_steps']);
        $this->assertSame(2, $row['events_by_source']['created']);
        $this->assertSame(1, $row['events_by_source']['guest']);
        foreach (['passes', 'team', 'ai_import'] as $flag) {
            $this->assertContains($flag, $row['features'], "{$flag} was used");
        }

        $this->assertSame([], $this->scheduleRow($data, $plain)['gateways']);
        $this->assertNotContains('team', $this->scheduleRow($data, $plain)['features'], 'the owner alone is not a team, and a follower is audience');

        $this->assertSame(['tickets' => ['total' => 1, 'by_month' => [now()->format('Y-m') => 1]]], $data['dismissed_steps']);
        $this->assertSame(['count' => 2, 'schedules' => 1], $data['usage']['gemini_parse_event'][now()->format('Y-m')]);
    }

    /**
     * Most live schedules have no ticket type, and the export could not say whether they have
     * nothing to sell or sell it on someone else's platform.
     */
    public function test_schedule_rows_count_events_linked_somewhere_else(): void
    {
        $venue = $this->freeRole(null, 'venue');
        $talent = $this->freeRole(null, 'talent');

        $elsewhere = ['creator_role_id' => $venue->id, 'tickets_enabled' => false, 'rsvp_enabled' => false];
        $sold = $this->createEvent($venue, $elsewhere + ['registration_url' => 'https://www.eventbrite.co.uk/e/a-night-out-123', 'ticket_price' => 12]);
        // On the venue's event, but where it is sold was the venue's choice.
        $sold->roles()->attach($talent->id, ['is_accepted' => true]);
        $this->createEvent($venue, $elsewhere + ['registration_url' => 'https://lu.ma/abc123']);
        // A price says what admission costs, not that the link sells it: neither of these two does.
        $this->createEvent($venue, $elsewhere + ['registration_url' => 'https://tickets.smalltownhall.org/buy', 'ticket_price' => 5]);
        $this->createEvent($venue, $elsewhere + ['registration_url' => 'https://someact.'._base_domain().'/a-gig', 'ticket_price' => 15]);
        // "Enter 0 for free" is a price, and not a priced event.
        $this->createEvent($venue, $elsewhere + ['registration_url' => 'https://www.facebook.com/events/123', 'ticket_price' => 0]);
        // Stored before the saving hook normalised links: the event page opens it with https://.
        $schemeless = $this->createEvent($venue, $elsewhere);
        DB::table('events')->where('id', $schemeless->id)->update(['registration_url' => 'www.dice.fm/event/an-old-row']);

        // None of these counts.
        // Our tickets or RSVP are on. (Where the plan blocks selling, the page does fall back to
        // this link; that event is left out on purpose, it is a seller who chose us.)
        $this->createEvent($venue, ['creator_role_id' => $venue->id, 'tickets_enabled' => true, 'registration_url' => 'https://www.eventbrite.com/e/sold-here-now']);
        $this->createEvent($venue, ['creator_role_id' => $venue->id, 'rsvp_enabled' => true, 'registration_url' => 'https://www.eventbrite.com/e/rsvp-here']);
        $this->createEvent($venue, $elsewhere);
        $this->createEvent($venue, $elsewhere + ['registration_url' => 'https://www.eventbrite.com/e/not-published', 'is_draft' => true]);
        // The submitter's link and price, not the venue's.
        $this->createEvent($venue, $elsewhere + ['registration_url' => 'https://www.eventbrite.com/e/from-a-guest', 'ticket_price' => 20, 'is_guest_submission' => true]);
        $old = $this->createEvent($venue, $elsewhere + ['registration_url' => 'https://dice.fm/event/last-spring']);
        DB::table('events')->where('id', $old->id)->update(['created_at' => now()->subDays(120)]);
        // Values the event page shows no button for. Priced, so that counting one of them cannot
        // be cancelled out by missing the scheme-less link above, which is not.
        foreach (['call the venue', 'javascript://eventbrite.com/%0aalert(1)'] as $notALink) {
            $event = $this->createEvent($venue, $elsewhere + ['ticket_price' => 9]);
            DB::table('events')->where('id', $event->id)->update(['registration_url' => $notALink]);
        }

        $data = $this->build();

        // One schedule carries each name, so every one of them folds into the bucket of its kind.
        // Eventbrite and Luma are sold through by the organizer alone; Dice is a box office.
        $this->assertSame([
            'events' => 6,
            'priced' => 3,
            'self_serve' => 2,
            'box_office' => 1,
            'platforms' => [
                'box_office' => ['events' => 1, 'priced' => 0],
                'event_schedule' => ['events' => 1, 'priced' => 1],
                'other' => ['events' => 2, 'priced' => 1],
                'other_ticketing' => ['events' => 2, 'priced' => 1],
            ],
        ], $this->scheduleRow($data, $venue)['external_tickets_90d']);
        $this->assertNull($this->scheduleRow($data, $talent)['external_tickets_90d'], 'listed on the event, but it did not choose the link');
        $this->assertStringNotContainsString('smalltownhall', json_encode($data), 'a host off the list is "other", never itself');
    }

    /**
     * The host is organizer-typed, so it leaves only as a name from the fixed list - and a name is
     * itself withheld until five schedules carry it, like a country.
     */
    public function test_registration_links_are_named_from_a_fixed_list_and_rare_names_fold(): void
    {
        $venues = collect(range(1, 5))->map(fn () => $this->freeRole(null, 'venue'));
        DB::table('roles')->where('id', $venues[0]->id)->update(['custom_domain' => 'https://events.bluenote.com']);

        $everyVenue = [
            'https://www.eventbrite.com.au/e/1',
            'https://buytickets.at/thehall/123',
            'https://lu.ma/abc',
            'https://fb.me/e/abc',
            'https://forms.gle/abc',
            // Box offices share one name, whichever they are.
            'https://www.ticketmaster.de/event/1',
            'https://link.dice.fm/abc',
            // Self-serve platforms too small to name.
            'https://ra.co/events/1',
            'https://eventer.co.il/abc',
        ];
        // Four schedules, one short of the threshold.
        $fourVenues = ['https://events.humanitix.com/a-show', 'https://www.meetup.com/a-group/events/1'];
        $firstVenueOnly = [
            // Five EVENTS on one schedule is still one schedule carrying the name.
            'https://buy.stripe.com/test_1', 'https://buy.stripe.com/test_2', 'https://buy.stripe.com/test_3',
            'https://buy.stripe.com/test_4', 'https://buy.stripe.com/test_5',
            // A brand name as somebody else's subdomain, or inside another name, is not the brand.
            'https://eventbrite.myvenue.com/tickets',
            'https://noteventbrite.com/e/1',
            // The exact host a schedule is served from is a page here. Its sibling is the
            // customer's own box office, which the parent-domain rule for referrers would hide.
            'https://events.bluenote.com/a-gig',
            'https://tickets.bluenote.com/buy',
        ];

        foreach ($venues as $n => $venue) {
            $links = array_merge($everyVenue, $n < 4 ? $fourVenues : [], $n === 0 ? $firstVenueOnly : []);
            foreach ($links as $link) {
                $this->createEvent($venue, ['creator_role_id' => $venue->id, 'registration_url' => $link]);
            }
        }

        $data = $this->build();
        $one = ['events' => 1, 'priced' => 0];

        $this->assertSame([
            'events' => 20,
            'priced' => 0,
            // The folded names still count as what they are.
            'self_serve' => 11,
            'box_office' => 2,
            'platforms' => [
                'box_office' => ['events' => 2, 'priced' => 0],
                'event_schedule' => $one,
                'eventbrite' => $one,
                'facebook' => $one,
                'form' => $one,
                'luma' => $one,
                // meetup (rare, sells nothing), the two near-misses and the customer's box office.
                'other' => ['events' => 4, 'priced' => 0],
                // ra.co and Eventer, humanitix (rare) and the five payment links (rare).
                'other_ticketing' => ['events' => 8, 'priced' => 0],
                'ticket_tailor' => $one,
            ],
        ], $this->scheduleRow($data, $venues[0])['external_tickets_90d']);

        $fifth = $this->scheduleRow($data, $venues[4])['external_tickets_90d'];
        $this->assertSame(9, $fifth['events']);
        $this->assertSame(5, $fifth['self_serve']);
        $this->assertSame(2, $fifth['box_office']);
        $this->assertSame(['events' => 2, 'priced' => 0], $fifth['platforms']['other_ticketing']);

        $payload = json_encode($data);
        foreach (['bluenote', 'myvenue', 'humanitix', 'meetup', 'payment_link', 'ticketmaster', 'dice'] as $withheld) {
            $this->assertStringNotContainsString('"'.$withheld, $payload);
            $this->assertStringNotContainsString($withheld.'.', $payload);
        }
    }

    /**
     * An imported event keeps the page it was read from as its link, so a schedule that imports
     * says nothing about where it sells. A curator that enters its own events is a promoter - the
     * type most schedules that sell have - and is counted like anyone else.
     */
    public function test_a_schedule_that_imports_events_is_left_out_and_other_curators_are_counted(): void
    {
        $promoter = $this->freeRole(null, 'curator');
        $importer = $this->freeRole(null, 'curator');
        $importer->import_config = ['urls' => ['https://whatson.cityguide.org/listings'], 'cities' => []];
        $importer->save();
        // The same column also holds a venue's request-form fields: that is not importing.
        $venue = $this->freeRole(null, 'venue');
        $venue->import_config = ['fields' => ['name'], 'urls' => [], 'cities' => []];
        $venue->save();

        foreach ([$promoter, $importer, $venue] as $role) {
            $this->createEvent($role, ['creator_role_id' => $role->id, 'registration_url' => 'https://www.eventbrite.com/e/a-show']);
        }

        $data = $this->build();

        $this->assertSame(1, $this->scheduleRow($data, $promoter)['external_tickets_90d']['self_serve']);
        $this->assertSame(1, $this->scheduleRow($data, $venue)['external_tickets_90d']['self_serve']);
        $this->assertNull($this->scheduleRow($data, $importer)['external_tickets_90d']);
    }

    /** "0 claimed" needs a pool to be read against: what the claim page can still hand over. */
    public function test_claims_count_the_placeholders_the_claim_page_can_still_reach(): void
    {
        $placeholder = function (array $attrs): Role {
            $role = new Role;
            $role->subdomain = 'act'.strtolower(Str::random(10));
            $role->type = 'talent';
            $role->name = 'The Wandering Few';
            $role->timezone = 'America/New_York';
            $role->plan_type = 'free';
            foreach ($attrs as $key => $value) {
                $role->{$key} = $value;
            }
            $role->save();

            return $role;
        };

        $placeholder(['email' => 'booking@gmail.com']);
        $placeholder(['phone' => '+15551230000']);
        // One placeholder, however many ways there are to reach it.
        $placeholder(['email' => 'both@gmail.com', 'phone' => '+15551230001']);
        $placeholder([]);
        $placeholder(['email' => '', 'phone' => '']);
        // Ownerless, with an address and a number - and closed: a verified stamp means
        // claimTarget() refuses it.
        $placeholder(['email' => 'verified@gmail.com', 'phone' => '+15551230002', 'email_verified_at' => now()]);
        // Owned, so not a placeholder however reachable it is. Both carry a phone so that the
        // either-contact group has to stay inside the claimable scope to keep them out.
        DB::table('roles')->where('id', $this->freeRole()->id)->update(['phone' => '+15551230003']);

        $claims = $this->build()['claims'];

        $this->assertSame(6, $claims['unclaimed_total']);
        $this->assertSame(3, $claims['claimable_with_contact']);
    }

    /** With type, month and plan beside it, a country few schedules share would name them. */
    public function test_a_country_fewer_than_five_schedules_share_is_hidden(): void
    {
        $common = [];
        foreach (range(1, 5) as $n) {
            $common[] = $this->freeRole(null, 'venue');
        }
        $rare = $this->freeRole(null, 'venue');
        DB::table('roles')->whereIn('id', array_map(fn ($r) => $r->id, $common))->update(['country_code' => 'us']);
        DB::table('roles')->where('id', $rare->id)->update(['country_code' => 'IS']);

        $data = $this->build();

        $this->assertSame('US', $this->scheduleRow($data, $common[0])['country']);
        $this->assertSame('(other)', $this->scheduleRow($data, $rare)['country']);
        $this->assertSame(5, collect($data['geography'])->firstWhere('country', 'US')['schedules']);
        $this->assertNull(collect($data['geography'])->firstWhere('country', 'IS'));
    }

    public function test_signup_rows_carry_bucketed_activity_and_the_variant_they_saw(): void
    {
        $referrer = $this->createOwner();
        $user = $this->createOwner();
        DB::table('users')->where('id', $user->id)->update(['hero_variant' => 'sell', 'referred_by_user_id' => $referrer->id]);

        foreach (range(1, 3) as $n) {
            DB::table('audit_logs')->insert(['user_id' => $user->id, 'action' => AuditService::AUTH_LOGIN,
                'ip_address' => '0.0.0.0', 'user_agent' => 'Mozilla/5.0', 'created_at' => now()->subDays($n)]);
        }
        DB::table('audit_logs')->insert(['user_id' => $user->id, 'action' => AuditService::AUTH_LOGIN,
            'ip_address' => '0.0.0.0', 'created_at' => now()->subDays(120)]);
        // An edit made by a sync job, not by the person.
        DB::table('audit_logs')->insert(['user_id' => $user->id, 'action' => AuditService::EVENT_UPDATE,
            'ip_address' => '0.0.0.0', 'user_agent' => 'Symfony', 'created_at' => now()->subDay()]);

        $data = $this->build();
        $i = array_flip($data['signups']['columns']);
        $uid = 'u:'.substr(hash_hmac('sha256', (string) $user->id, (string) config('app.key')), 0, 12);
        $row = collect($data['signups']['rows'])->firstWhere($i['uid'], $uid);

        $this->assertSame('sell', $row[$i['hero_variant']]);
        $this->assertTrue($row[$i['referred']]);
        $this->assertSame('2-5', $row[$i['logins_90d']], 'three recent sign-ins; the one 120 days ago is outside');
        $this->assertSame('0', $row[$i['event_edits_90d']], 'a system edit is not the owner doing anything');
    }

    /**
     * The setup guide's cohort. It began mid-month, so created_month cannot say who had one: the
     * row has to. The furthest stage reached, from a fixed vocabulary, and whether it was hidden.
     */
    public function test_a_signup_row_says_how_far_through_the_setup_guide_they_got(): void
    {
        $guides = [
            'none' => null,
            'started' => ['role_id' => 1, 'started_at' => now()->toIso8601String()],
            'live' => ['role_id' => 1, 'started_at' => now()->toIso8601String(), 'celebrated_at' => now()->toIso8601String()],
            'embedded' => ['role_id' => 1, 'started_at' => now()->toIso8601String(), 'celebrated_at' => now()->toIso8601String(),
                'shared_at' => now()->toIso8601String(), 'embedded_at' => now()->toIso8601String(), 'dismissed_at' => now()->toIso8601String()],
            'finished' => ['role_id' => 1, 'started_at' => now()->toIso8601String(), 'completed_at' => now()->toIso8601String()],
        ];

        $users = [];
        foreach ($guides as $label => $guide) {
            $users[$label] = User::factory()->create(['email_verified_at' => now()]);
            DB::table('users')->where('id', $users[$label]->id)
                ->update(['setup_guide' => $guide === null ? null : json_encode($guide)]);
        }

        $data = $this->build();
        $i = array_flip($data['signups']['columns']);
        $rows = collect($data['signups']['rows']);
        $rowOf = fn (User $user) => $rows->firstWhere(
            $i['uid'],
            'u:'.substr(hash_hmac('sha256', (string) $user->id, (string) config('app.key')), 0, 12)
        );

        $this->assertNull($rowOf($users['none'])[$i['setup_guide']]);
        $this->assertFalse($rowOf($users['none'])[$i['setup_guide_hidden']]);
        $this->assertSame('started', $rowOf($users['started'])[$i['setup_guide']]);
        $this->assertSame('live', $rowOf($users['live'])[$i['setup_guide']]);
        $this->assertSame('embedded', $rowOf($users['embedded'])[$i['setup_guide']], 'the furthest stage, not the first');
        $this->assertTrue($rowOf($users['embedded'])[$i['setup_guide_hidden']]);
        $this->assertSame('finished', $rowOf($users['finished'])[$i['setup_guide']]);

        // The account-wide "Turn off suggestions" switch, which is not the guide being hidden.
        $this->assertFalse($rowOf($users['embedded'])[$i['suggestions_off']]);

        \App\Utils\SetupGuide::suggest($users['started'], false);
        $data = $this->build();
        $off = collect($data['signups']['rows'])->firstWhere(
            $i['uid'],
            'u:'.substr(hash_hmac('sha256', (string) $users['started']->id, (string) config('app.key')), 0, 12)
        );

        $this->assertTrue($off[$i['suggestions_off']]);
        $this->assertFalse($off[$i['setup_guide_hidden']]);
    }

    /** Deploy dates, from the first scheduler tick after each release. */
    public function test_release_history_records_each_version_once_in_order(): void
    {
        config(['self-update.version_installed' => 'v1.0.140']);
        \App\Utils\ReleaseHistory::touch();
        \App\Utils\ReleaseHistory::touch();

        config(['self-update.version_installed' => 'v1.0.141']);
        \App\Utils\ReleaseHistory::touch();
        // A lost cache must not duplicate the entry it already recorded.
        \Illuminate\Support\Facades\Cache::forget('release_history.current');
        \App\Utils\ReleaseHistory::touch();

        $releases = $this->build()['meta']['releases'];

        $this->assertSame(['v1.0.140', 'v1.0.141'], array_column($releases, 'version'));
        $this->assertNotEmpty($releases[1]['first_seen_at']);
    }

    /**
     * touch() is a read-modify-write, so it reads the row, not Setting's cached map: with a worker
     * and a web container on separate file caches, the map one of them holds can be stale, and
     * appending to it would drop what the other container wrote.
     */
    public function test_release_history_appends_to_the_stored_row_not_a_stale_cached_map(): void
    {
        \App\Models\Setting::get('release_history');   // warm the map while the row is empty
        DB::table('settings')->insert(['key' => 'release_history', 'created_at' => now(), 'updated_at' => now(),
            'value' => json_encode([['version' => 'v1.0.150', 'first_seen_at' => now()->subWeek()->toIso8601String()]])]);

        config(['self-update.version_installed' => 'v1.0.151']);
        \App\Utils\ReleaseHistory::touch();

        $stored = json_decode(DB::table('settings')->where('key', 'release_history')->value('value'), true);
        $this->assertSame(['v1.0.150', 'v1.0.151'], array_column($stored, 'version'));

        // And the payload reads it the same way: the web container's cached map may be stale.
        \Illuminate\Support\Facades\Cache::forever('site_settings', ['release_history' => '[]']);
        $this->assertSame(['v1.0.150', 'v1.0.151'], array_column($this->build()['meta']['releases'], 'version'));
    }

    /** The breakdown of trial starts adds up to the trials it breaks down, deleted schedules aside. */
    public function test_ticket_trial_sources_sum_to_the_trials_started(): void
    {
        $kept = $this->freeRole();
        $deleted = $this->freeRole();
        DB::table('roles')->whereIn('id', [$kept->id, $deleted->id])->update(['ticket_trial_ends_at' => now()->addDays(5)]);
        DB::table('roles')->where('id', $deleted->id)->update(['is_deleted' => true]);
        foreach ([$kept, $deleted] as $role) {
            DB::table('audit_logs')->insert(['action' => AuditService::TICKET_TRIAL_START, 'model_type' => 'Role',
                'model_id' => $role->id, 'new_values' => json_encode(['source' => 'tickets']), 'ip_address' => '0.0.0.0',
                'created_at' => now()->subDays(2)]);
        }
        // A restart of the same trial is still one trial.
        DB::table('audit_logs')->insert(['action' => AuditService::TICKET_TRIAL_START, 'model_type' => 'Role',
            'model_id' => $kept->id, 'new_values' => json_encode(['source' => 'plan']), 'ip_address' => '0.0.0.0',
            'created_at' => now()->subDay()]);

        $trials = $this->build()['monetization']['ticket_trials'];

        $this->assertSame(1, $trials['started']);
        $this->assertSame(['tickets' => 1], $trials['started_from']);
    }

    /** An unrecognised range would quietly build the all-time window under the asked-for name. */
    public function test_export_growth_refuses_an_unknown_range(): void
    {
        $this->artisan('app:export-growth', ['--range' => 'last_60_days'])->assertFailed();
    }

    /**
     * audit:prune used to delete everything past 90 days, which silently turned claims, trial
     * starts and checkout sources into zeros that read as "nothing happened".
     */
    public function test_pruning_keeps_the_audit_rows_the_growth_history_depends_on(): void
    {
        $old = now()->subDays(400);
        foreach ([AuditService::SCHEDULE_CLAIM, AuditService::SUBSCRIPTION_CREATE, AuditService::TICKET_TRIAL_START, AuditService::AUTH_LOGIN] as $action) {
            DB::table('audit_logs')->insert(['action' => $action, 'ip_address' => '0.0.0.0', 'created_at' => $old]);
        }

        $this->artisan('audit:prune')->assertSuccessful();

        $left = DB::table('audit_logs')->pluck('action')->all();
        $this->assertEqualsCanonicalizing(
            [AuditService::SCHEDULE_CLAIM, AuditService::SUBSCRIPTION_CREATE, AuditService::TICKET_TRIAL_START],
            $left
        );
    }

    /**
     * The public "Submit your event" page's three counters (GuestSubmitFunnelTest holds how they
     * are written): carried per month beside the sign-up ones, and null, not zero, for a month
     * before the columns existed.
     */
    public function test_the_growth_export_carries_the_three_and_knows_when_they_began(): void
    {
        MarketingDailyStat::create(['date' => '2026-09-15', 'visitors' => 10]);
        MarketingDailyStat::create([
            'date' => '2026-10-07', 'guest_submit_views' => 40, 'guest_submit_code_requests' => 12, 'guest_submit_submissions' => 9,
            'booking_request_views' => 30, 'booking_request_submissions' => 7,
            'gp_event_visitors' => 500, 'gp_list_taps' => 210, 'gp_form_opens' => 90, 'gp_checkout_starts' => 40,
            'gp_checkouts_done' => 31, 'gp_follows' => 12, 'gp_calendar_adds' => 25,
        ]);

        $traffic = collect($this->build()['traffic'])->keyBy('month');

        $this->assertSame(40, $traffic['2026-10']['guest_submit_views']);
        $this->assertSame(12, $traffic['2026-10']['guest_submit_code_requests']);
        $this->assertSame(9, $traffic['2026-10']['guest_submit_submissions']);
        // And the booking request page's two, beside them.
        $this->assertSame(30, $traffic['2026-10']['booking_request_views']);
        $this->assertSame(7, $traffic['2026-10']['booking_request_submissions']);
        $this->assertNull($traffic['2026-09']['booking_request_views']);
        // And the guest pages' seven (GuestFunnelTest holds how they are written).
        foreach (['gp_event_visitors' => 500, 'gp_list_taps' => 210, 'gp_form_opens' => 90, 'gp_checkout_starts' => 40,
            'gp_checkouts_done' => 31, 'gp_follows' => 12, 'gp_calendar_adds' => 25] as $column => $expected) {
            $this->assertSame($expected, $traffic['2026-10'][$column], $column);
            $this->assertNull($traffic['2026-09'][$column], $column.' was not counted before October');
        }
        // A month before the columns existed was not counted, which is not the same as zero.
        $this->assertNull($traffic['2026-09']['guest_submit_views']);
        $this->assertNull($traffic['2026-09']['guest_submit_submissions']);
    }
}
