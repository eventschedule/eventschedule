<?php

namespace Tests\Feature;

use App\Models\Referral;
use App\Models\UsageDaily;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The platform admin's reporting pages: users, revenue, analytics, usage, growth, referrals and
 * domains (October 2026, the night the rest of the admin portal was rebuilt on the page kit).
 *
 * Each test is something a person could see on those pages: seven ways to draw a figure, a
 * warning that was a stripe down one side, a state printed as its database column, English where
 * the reader's language should be, a chart drawn in the operating system's colours. What the
 * pages look like belongs to the screenshots; these hold what they must say.
 *
 * Assertions count matches with substr_count()/preg_match() and never hand a whole page to a
 * pattern assertion: a failure would print 300 KB of HTML.
 */
class ApAdminInsightPagesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const PAGES = ['users', 'revenue', 'analytics', 'usage', 'growth', 'referrals', 'domains'];

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Growth, referrals and domains exist only where there are plans to sell.
        config(['app.hosted' => true, 'app.is_nexus' => true]);

        // The Domains page asks DigitalOcean how each domain stands whenever a token is
        // configured, and the suite loads the developer's .env, where a real one lives: without
        // this every run of this file read the production app from their API. And no request
        // through the HTTP client may leave at all, so a later change that reaches for the
        // network fails here instead of going out.
        config(['services.digitalocean.api_token' => '', 'services.digitalocean.app_id' => '']);
        \Illuminate\Support\Facades\Http::preventStrayRequests();

        $this->admin = $this->createOwner(true);
    }

    private function page(string $path): string
    {
        // EnsureUserIsAdmin gates every /admin route on a confirmed password this session.
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->admin)
            ->get($path)
            ->assertOk()
            ->getContent();
    }

    private function source(string $page): string
    {
        return File::get(resource_path("views/admin/{$page}.blade.php"));
    }

    /**
     * One opening for all seven: the navigation, one line saying what the page shows, then the
     * page. No key left untranslated, and none of the looks these pages each had of their own.
     */
    public function test_every_page_opens_the_same_way_and_says_everything_in_words(): void
    {
        foreach (self::PAGES as $page) {
            $html = $this->page('/admin/'.$page);

            $this->assertSame(1, substr_count($html, 'class="page-lead"'), "{$page}: one line on what the page shows");
            $this->assertStringContainsString(e(__($page === 'growth' ? 'messages.growth_description' : 'messages.admin_'.$page.'_lead')), $html, "{$page}: its own lead");
            $this->assertSame(1, substr_count($html, 'class="page-shell page-stack"'), "{$page}: the page kit's shell");
            $this->assertSame(1, substr_count($html, 'class="ap-subtabs'), "{$page}: the navigation names the page");
            $this->assertSame(0, preg_match('/\bmessages\.[a-z0-9_]+/', $html, $raw), "{$page}: a language key was printed as it is: ".($raw[0] ?? ''));
            $this->assertStringNotContainsString('border-l-4', $html, "{$page}: a stripe down one side");
            $this->assertStringNotContainsString('bg-gray-800 dark:bg-gray-200', $html, "{$page}: the black capital-letter button");
        }
    }

    /**
     * The same, read from the files, for the branches a test database does not reach: the old
     * stat box, the old table, a state printed with ucfirst(), a chart asking the operating
     * system whether it is dark, and the key that never existed (messages.no_data, which two
     * empty lists on /admin/users printed as it stands).
     */
    public function test_the_views_keep_to_the_page_kit(): void
    {
        foreach (self::PAGES as $page) {
            $source = $this->source($page);

            $this->assertStringStartsWith("<x-app-admin-layout>\n    @include('admin.partials._navigation', ['active' => '{$page}'])", $source, "{$page}: the navigation is the first line");
            foreach (['<x-stat-panel', '<x-primary-button', 'border-l-4', 'min-w-full', 'ucfirst($sale', "messages.no_data'", 'prefers-color-scheme', 'x-data=', 'onclick='] as $old) {
                $this->assertStringNotContainsString($old, $source, "{$page}: {$old}");
            }
        }

        foreach (['users', 'revenue', 'analytics', 'usage', 'growth'] as $page) {
            $this->assertSame(1, substr_count($this->source($page), "@include('admin.partials._date-range-filter', ['range' => \$range])"), "{$page}: the period select");
        }
    }

    /** The period is chosen at the end of the line that says what the page shows. */
    public function test_the_period_select_sits_in_the_page_head(): void
    {
        $html = $this->page('/admin/revenue?range=last_7_days');

        $head = strpos($html, 'class="page-head"');
        $select = strpos($html, 'id="date-range"');
        $shell = strpos($html, 'class="page-shell page-stack"');

        $this->assertNotFalse($head);
        $this->assertTrue($head < $select && $select < $shell, 'the select belongs to the head, above the page');
        $this->assertSame(1, substr_count($html, 'id="date-range"'));
        $this->assertSame(1, preg_match('/<option value="last_7_days"\s+selected/', $html));
    }

    /**
     * A payment that needs a person is a notice and the list of which, under the id the admin
     * alerts link to. The sale's state is the word the rest of the portal uses, and the question
     * before approving is asked in the reader's language.
     */
    public function test_revenue_says_what_needs_a_person_as_a_notice(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);
        $this->createSale($event, $role, ['status' => 'amount_mismatch', 'payment_amount' => 60, 'name' => 'Sam Ortiz']);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 70]);

        $html = $this->page('/admin/revenue');

        $this->assertSame(1, substr_count($html, 'id="amount-mismatch"'));
        $this->assertStringContainsString(e(__('messages.amount_mismatch_help')), $html);
        $this->assertStringContainsString('data-confirm="'.e(__('messages.approve_sale_confirm')).'"', $html);
        $this->assertStringNotContainsString('Amount_mismatch', $html, 'the state was the column, capitalised');
        $this->assertSame(1, substr_count($html, 'class="event-status is-warn">'.__('messages.amount_mismatch')));
        // Seven figures in one strip where there were two rows of boxes.
        $this->assertSame(1, substr_count($html, 'page-stats is-auto insight-strip'));
        $this->assertSame(7, substr_count($html, 'class="page-stat"'));
        $this->assertSame(4, substr_count($html, 'class="ap-card rounded-xl p-4 sm:p-6 h-full flex flex-col items-center'), 'four headline tiles');
    }

    /**
     * /admin/usage spoke English whatever the language: its newsletter list, its translation
     * headings, its "today (limit: ...)". And the four kinds of stuck record are one list.
     */
    public function test_usage_is_in_the_readers_language_and_lists_stuck_records_once(): void
    {
        $role = $this->createRole($this->createOwner());
        UsageDaily::create(['date' => now()->toDateString(), 'operation' => 'email_newsletter', 'role_id' => $role->id, 'count' => 80]);
        UsageDaily::create(['date' => now()->toDateString(), 'operation' => 'gemini_parse', 'role_id' => $role->id, 'count' => config('usage.ai_daily_limit') + 5]);
        DB::table('roles')->where('id', $role->id)->update(['translation_attempts' => 9, 'name_en' => null]);

        $html = $this->page('/admin/usage');

        $this->assertStringContainsString(__('messages.top_newsletter_senders'), $html);
        $this->assertStringContainsString(__('messages.usage_anomaly_line', [
            'category' => 'AI / Gemini', 'today' => number_format(config('usage.ai_daily_limit') + 5), 'limit' => number_format(config('usage.ai_daily_limit')),
        ]), $html);
        foreach (['Top Newsletter Senders', 'Emails Sent', 'Never Attempted', 'Longest Waiting', 'Source Lang', 'EventRole', 'EventPart'] as $english) {
            $this->assertStringNotContainsString($english, $html, "{$english} was written into the page in English");
        }
        $this->assertSame(1, substr_count($html, 'data-col="plan"'), 'a hosted install says which plan the sender is on');
        $this->assertSame(1, substr_count($html, 'class="js-retry-translation event-link"'));
        $this->assertSame(1, substr_count($html, 'data-type="role" data-id="'.$role->id.'"'));
        // Past twice the threshold, so the count is in the colour of something failing.
        $this->assertSame(1, preg_match('/class="c-num usage-over"[^>]*>9</', $html));
    }

    /**
     * The funnel is laid out again and counts nothing again: every stage GrowthExportService
     * reports has its bar at the width it gave, and the one drop it calls the biggest leak is
     * marked once, where it is, and named by the tile above.
     */
    public function test_the_funnel_draws_what_the_service_counted(): void
    {
        foreach ([0, 1, 1, 2, 2, 2] as $step) {
            $owner = $this->createOwner();
            if ($step >= 1) {
                $role = $this->createRole($owner);
            }
            if ($step >= 2) {
                $this->createEvent($role);
            }
        }

        $response = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->admin)->get('/admin/users?range=last_30_days')->assertOk();
        $funnel = $response->viewData('funnel');
        $html = $response->getContent();

        $this->assertNotNull($funnel['biggest_drop'], 'the fixture has a leak to mark');
        $this->assertSame(count($funnel['stages']), substr_count($html, 'class="funnel-track"'), 'one bar a stage');
        foreach ($funnel['stages'] as $stage) {
            if ($stage['count'] > 0) {
                $this->assertStringContainsString('style="width: '.max(2, $stage['width']).'%;', $html, $stage['key']);
            }
        }

        $this->assertSame(1, substr_count($html, 'class="funnel-drop is-biggest"'));
        $marked = substr($html, strpos($html, 'class="funnel-drop is-biggest"'), 1200);
        $this->assertStringContainsString(number_format($funnel['biggest_drop']['lost']).' '.__('messages.funnel_lost'), $marked);
        $this->assertTrue(
            strpos($marked, __('messages.funnel_stage_'.$funnel['biggest_drop']['to_key'])) !== false,
            'the marker stands above the stage the drop leads into'
        );
        $this->assertStringContainsString('<span dir="ltr">-'.$funnel['biggest_drop']['drop_pct'].'%</span>', $html, 'the tile names the same drop, its sign on the left');
    }

    /**
     * PHP turns the array key '0' into the integer 0, so the bucket of free schedules that never
     * sold a paid ticket was drawn in the colour of the ones that had.
     */
    public function test_growth_draws_the_bucket_of_nothing_as_nothing(): void
    {
        $this->createFreeRole();

        $response = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->admin)->get('/admin/growth')->assertOk();
        $buckets = $response->viewData('data')['free_pressure']['peak_month_paid_tickets'];
        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, '<i class="is-quiet"'));
        $this->assertSame(count($buckets) - 1, substr_count($html, '<i class="is-warn"'));
    }

    /** The list is narrowed by one of five states, every one of them counted above it. */
    public function test_referrals_count_and_filter_by_every_state(): void
    {
        $referrer = $this->createOwner();
        foreach (['pending' => null, 'subscribed' => 'pro', 'expired' => null] as $status => $plan) {
            Referral::create([
                'referrer_user_id' => $referrer->id, 'referred_user_id' => $this->createOwner()->id,
                'status' => $status, 'plan_type' => $plan,
            ]);
        }

        $html = $this->page('/admin/referrals?status=expired');

        $this->assertSame(7, substr_count($html, 'class="page-stat"'), 'the total, five states and the rate');
        $this->assertSame(6, substr_count($html, 'class="page-pill"'));
        $this->assertSame(1, preg_match('/class="page-pill"\s+aria-current="true"\s*>'.preg_quote(__('messages.expired'), '/').'</', $html));
        $this->assertSame(1, substr_count($html, 'class="event-status is-bad">'.__('messages.expired')));
        $this->assertSame(0, substr_count($html, 'class="event-status is-info"'), 'only the expired one is listed');

        $html = $this->page('/admin/referrals');
        $this->assertSame(1, substr_count($html, 'class="event-chip">'.__('messages.pro')), 'the plan is the word the portal uses for it');

        $html = $this->page('/admin/referrals?status=credited');
        $this->assertStringContainsString(__('messages.no_referrals_found'), $html);
        $this->assertStringContainsString(__('messages.clear_filter'), $html);
        $this->assertStringNotContainsString('<table', $html);
    }

    /**
     * No domains at all and no domains for this search are two different things to be told, and
     * a redirect has no setup to be in or to run again.
     */
    public function test_domains_tell_nothing_yet_from_nothing_found(): void
    {
        $html = $this->page('/admin/domains');
        $this->assertStringContainsString(__('messages.no_custom_domains'), $html);
        $this->assertStringNotContainsString(__('messages.clear_filters'), $html);

        $failed = $this->createRole($this->createOwner());
        $redirect = $this->createRole($this->createOwner());
        DB::table('roles')->where('id', $failed->id)->update([
            'custom_domain' => 'https://events.example.org', 'custom_domain_host' => 'events.example.org',
            'custom_domain_mode' => 'direct', 'custom_domain_status' => 'failed', 'custom_domain_error' => 'No CNAME record',
        ]);
        DB::table('roles')->where('id', $redirect->id)->update([
            'custom_domain' => 'https://www.example.net/events', 'custom_domain_mode' => 'redirect',
        ]);

        $html = $this->page('/admin/domains');
        $this->assertSame(1, substr_count($html, 'class="event-status is-bad">'.__('messages.domain_failed')));
        $this->assertStringContainsString('No CNAME record', $html);
        $this->assertSame(1, substr_count($html, __('messages.reprovision_confirm')), 'only the direct domain can be provisioned again');
        $this->assertSame(2, substr_count($html, e(__('messages.domain_remove_confirm'))));
        $this->assertSame(1, preg_match_all('/<input[^>]*id="search"[^>]*data-subdomain-autocomplete/', $html), 'the search box still offers schedules');

        $html = $this->page('/admin/domains?search=no-such-schedule');
        $this->assertStringContainsString(__('messages.no_results_found'), $html);
        $this->assertStringContainsString(__('messages.clear_filters'), $html);
        $this->assertStringNotContainsString(__('messages.no_custom_domains'), $html);
    }

    /**
     * In Hebrew the pages are Hebrew, and a signed figure or a percentage is marked left-to-right
     * so its sign stays on the left of it.
     */
    public function test_the_pages_read_in_hebrew(): void
    {
        $this->admin->forceFill(['language_code' => 'he'])->save();

        foreach (self::PAGES as $page) {
            $html = $this->page('/admin/'.$page);

            $this->assertStringContainsString('dir="rtl"', $html, $page);
            $this->assertSame(0, preg_match('/\bmessages\.[a-z0-9_]+/', $html, $raw), "{$page}: ".($raw[0] ?? ''));
            if ($page !== 'growth') {
                $this->assertStringContainsString(e(__('messages.admin_'.$page.'_lead', [], 'he')), $html, $page);
            }
        }

        $html = $this->page('/admin/users');
        $this->assertSame(1, preg_match('/<span dir="ltr">[+-][0-9.,]+%<\/span>/', $html), 'the change in users carries its sign on the left');
    }
}
