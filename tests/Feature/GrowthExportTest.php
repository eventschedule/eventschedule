<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\DemoService;
use App\Services\GrowthExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_the_page_renders_and_the_download_is_valid_json(): void
    {
        $admin = $this->createOwner(true);
        $this->freeRole();

        $this->adminActing($admin)->get('/admin/growth')->assertOk();

        $response = $this->adminActing($admin)->get('/admin/growth/export');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');

        // streamDownload writes to the output buffer, so getContent() would be empty.
        $decoded = json_decode($response->streamedContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('meta', $decoded);
        $this->assertArrayHasKey('signups', $decoded);
        $this->assertArrayHasKey('schedules', $decoded);
        $this->assertSame(1, $decoded['meta']['schema_version']);
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

        // The referrer survives as a bare host and the landing page as a bare path.
        $this->assertStringContainsString('ref.example.org', $json);
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

    public function test_free_pressure_buckets_the_peak_month_and_flags_the_cap(): void
    {
        $cap = (int) config('usage.ticket_sale_monthly_limit_free', 25);

        $quiet = $this->freeRole();
        $quietEvent = $this->createEvent($quiet);
        $quietTicket = $this->createTicket($quietEvent, ['price' => 10, 'quantity' => 500]);
        $this->createSale($quietEvent, $quiet, ['status' => 'paid', 'payment_amount' => 30], $quietTicket, 3);

        $busy = $this->freeRole();
        $busyEvent = $this->createEvent($busy);
        $busyTicket = $this->createTicket($busyEvent, ['price' => 10, 'quantity' => 500]);
        $this->createSale($busyEvent, $busy, ['status' => 'paid', 'payment_amount' => 300], $busyTicket, $cap);

        $idle = $this->freeRole();

        $pressure = $this->build()['free_pressure'];

        $this->assertSame(3, $pressure['free_schedules']);
        $this->assertSame(1, $pressure['peak_month_paid_tickets']['1-5'], 'the 3-ticket schedule');
        $this->assertSame(1, $pressure['peak_month_paid_tickets']['at_or_over_cap'], 'the capped schedule');
        $this->assertSame(1, $pressure['peak_month_paid_tickets']['0'], 'the idle schedule');
        $this->assertSame(1, $pressure['ever_hit_ticket_cap']);
        $this->assertSame($cap, $pressure['ticket_cap']);
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

    public function test_mrr_excludes_admin_granted_and_referral_plans(): void
    {
        $paying = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'pro', 'plan_expires' => now()->addYear()->format('Y-m-d'),
            'plan_term' => 'month', 'plan_source' => null,
        ]);
        $granted = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'pro', 'plan_expires' => now()->addYear()->format('Y-m-d'),
            'plan_term' => 'month', 'plan_source' => 'admin',
        ]);

        $money = $this->build()['monetization'];
        $monthly = (float) config('services.stripe_platform.price_monthly_amount', 5);

        $this->assertSame(2, $money['plan_counts']['pro']);
        $this->assertSame(round($monthly, 2), $money['mrr_usd'], 'the admin grant pays nothing');
        $this->assertSame(1, $money['by_plan_source']['admin']);
        $this->assertSame(1, $money['by_plan_source']['stripe']);
        $this->assertNotNull($paying->fresh());
        $this->assertNotNull($granted->fresh());
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
        $activating = $this->createOwner();
        $activating->landing_page = 'https://eventschedule.com/for-venues';
        $activating->utm_source = 'newsletter';
        $activating->save();
        $this->freeRole($activating);

        $bouncing = $this->createOwner();
        $bouncing->landing_page = 'https://eventschedule.com/for-venues';
        $bouncing->utm_source = 'newsletter';
        $bouncing->save();

        $acquisition = $this->build()['acquisition'];
        $landing = collect($acquisition['by_landing_path'])->firstWhere('key', '/for-venues');

        $this->assertNotNull($landing);
        $this->assertSame(2, $landing['signups']);
        $this->assertSame(1, $landing['saved_schedule'], 'one of the two got as far as a schedule');

        $source = collect($acquisition['by_utm_source'])->firstWhere('key', 'newsletter');
        $this->assertSame(2, $source['signups']);
    }

    public function test_non_admins_are_rejected(): void
    {
        $user = $this->createOwner();

        $this->actingAs($user)->get('/admin/growth')->assertRedirect();
        $this->actingAs($user)->get('/admin/growth/export')->assertRedirect();
    }

    public function test_the_page_is_absent_on_a_selfhosted_install(): void
    {
        config(['app.hosted' => false]);
        $admin = $this->createOwner(true);

        // A single-tenant selfhost has no tiers and no subscriptions, so every
        // monetization section would be empty or actively misleading.
        $this->adminActing($admin)->get('/admin/growth')->assertNotFound();
        $this->adminActing($admin)->get('/admin/growth/export')->assertNotFound();
    }
}
