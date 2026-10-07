<?php

namespace Tests\Feature;

use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use App\Models\NewsletterSegment;
use App\Models\NewsletterSegmentUser;
use App\Models\NewsletterTemplate;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The newsletters section of the admin portal: a schedule's own (newsletter/*) and the platform
 * admin's (admin/newsletters/*).
 *
 * Each test is something a person could see before the pages were rebuilt on the portal's page
 * kit (October 2026): four places that were three bordered buttons and a "Back" button each, a
 * Segments page that opened on a form with its list unnamed above it, an admin Templates page
 * with no sidebar and no admin navigation, a recipient's status printed as the raw database word.
 * What the pages look like belongs to the screenshots; these hold what they must say.
 *
 * Assertions count matches with preg_match()/substr_count() and never hand the whole page to a
 * pattern assertion: a failure would print 600 KB of HTML.
 */
class ApNewsletterPagesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // No page here may reach the network: the suite loads the developer's .env, where real
        // tokens live, and a page that asks an outside service (the Domains page asked
        // DigitalOcean) would do it for real on every run. A request nothing faked throws.
        \Illuminate\Support\Facades\Http::preventStrayRequests();
    }

    /** The four places of the section, by the route that opens each. */
    private const TABS = [
        'newsletters' => 'newsletter.index',
        'segments' => 'newsletter.segments',
        'templates' => 'newsletter.templates',
        'import' => 'newsletter.import',
    ];

    private function page(User $user, string $url): string
    {
        return $this->actingAs($user)->get($url)->assertOk()->getContent();
    }

    private function adminPage(User $admin, string $url): string
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)
            ->get($url)
            ->assertOk()
            ->getContent();
    }

    /** The addresses of a strip of tabs, in order: [address => whether it is the current one]. */
    private function stripTabs(string $html, string $id): array
    {
        $this->assertSame(1, preg_match('/<nav class="ap-tabs" id="'.$id.'"[^>]*>(.*?)<\/nav>/s', $html, $nav), 'the page has the strip of tabs');
        preg_match_all('/<a href="([^"]*)" class="ap-tab"([^>]*)>/', $nav[1], $links, PREG_SET_ORDER);

        $tabs = [];
        foreach ($links as $link) {
            $tabs[html_entity_decode($link[1])] = str_contains($link[2], 'aria-current="page"');
        }

        return $tabs;
    }

    /** The addresses the phone's dropdown offers: [address => whether it is selected]. */
    private function dropdownTabs(string $html, string $id): array
    {
        $this->assertSame(1, preg_match('/<select id="'.$id.'-select"[^>]*>(.*?)<\/select>/s', $html, $select), 'a phone has the dropdown');
        preg_match_all('/<option value="([^"]*)"([^>]*)>/', $select[1], $options, PREG_SET_ORDER);

        $tabs = [];
        foreach ($options as $option) {
            $tabs[html_entity_decode($option[1])] = str_contains($option[2], 'selected');
        }

        return $tabs;
    }

    private function newsletter(Role $role, User $user, array $attrs = []): Newsletter
    {
        return Newsletter::create($attrs + [
            'role_id' => $role->id,
            'user_id' => $user->id,
            'type' => 'schedule',
            'subject' => 'October news',
            'status' => 'draft',
            'template' => 'modern',
        ]);
    }

    public function test_the_four_places_are_tabs_on_all_four_pages(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $param = ['role_id' => UrlUtils::encodeId($role->id)];

        // With nothing written yet the empty list offers the same button as the title row.
        $this->newsletter($role, $owner);

        $addresses = array_map(fn ($route) => route($route, $param), self::TABS);

        foreach (self::TABS as $tab => $route) {
            $html = $this->page($owner, route($route, $param));

            $expected = [];
            foreach ($addresses as $key => $address) {
                $expected[$address] = $key === $tab;
            }

            $this->assertSame($expected, $this->stripTabs($html, 'newsletter-tabs'), "the strip on the {$tab} page");
            $this->assertSame($expected, $this->dropdownTabs($html, 'newsletter-tabs'), "the dropdown on the {$tab} page");

            // One title row, naming the section, with the button that writes a newsletter.
            $this->assertSame(1, substr_count($html, '<h1 class="page-title"'), "one title on the {$tab} page");
            $this->assertSame(1, substr_count($html, 'href="'.e(route('newsletter.create', $param)).'"'), "one way to write a newsletter on the {$tab} page");
        }
    }

    public function test_the_schedule_picker_is_offered_only_when_there_is_a_choice_and_on_every_tab(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $param = ['role_id' => UrlUtils::encodeId($role->id)];

        $this->assertSame(0, substr_count($this->page($owner, route('newsletter.index', $param)), 'id="role-filter"'));

        $second = $this->createRole($owner, 'talent', ['name' => 'Second Schedule']);

        foreach (self::TABS as $tab => $route) {
            $html = $this->page($owner, route($route, $param));

            $this->assertSame(1, substr_count($html, 'id="role-filter"'), "the picker on the {$tab} page");
            $this->assertSame(1, substr_count($html, '<option value="'.UrlUtils::encodeId($second->id).'"'), "the other schedule on the {$tab} page");
        }
    }

    public function test_the_newsletters_are_one_list_that_says_how_each_one_stands(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->newsletter($role, $owner, ['subject' => 'A draft']);
        $this->newsletter($role, $owner, ['subject' => 'A scheduled one', 'status' => 'scheduled', 'scheduled_at' => now()->addDays(2)]);
        $sent = $this->newsletter($role, $owner, ['subject' => 'A sent one', 'status' => 'sent', 'sent_at' => now()->subDay(), 'sent_count' => 40, 'open_count' => 10, 'click_count' => 4]);

        $html = $this->page($owner, route('newsletter.index', ['role_id' => UrlUtils::encodeId($role->id)]));

        $this->assertSame(1, substr_count($html, '<table class="page-table'), 'one list, not a table and a copy for a phone');
        $this->assertSame(3, substr_count($html, 'class="event-status'), 'a status for each newsletter');
        $this->assertSame(1, substr_count($html, 'class="event-status is-on"'));
        $this->assertSame(1, substr_count($html, 'class="event-status is-warn"'));
        $this->assertStringContainsString('25%', $html);
        $this->assertStringContainsString('10%', $html);

        // A sent newsletter leads to its figures, a draft to the builder.
        $this->assertSame(2, substr_count($html, e(route('newsletter.stats', ['hash' => UrlUtils::encodeId($sent->id), 'role_id' => UrlUtils::encodeId($role->id)]))));
    }

    public function test_the_segments_page_lists_what_there_is_before_the_form_that_adds_one(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $param = ['role_id' => UrlUtils::encodeId($role->id)];

        $empty = $this->page($owner, route('newsletter.segments', $param));
        $this->assertSame(1, substr_count($empty, 'class="page-empty"'));
        $this->assertStringContainsString(__('messages.no_segments'), $empty);

        $segment = NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Regulars', 'type' => 'manual']);
        NewsletterSegmentUser::create(['newsletter_segment_id' => $segment->id, 'email' => 'one@example.com', 'name' => 'One', 'created_at' => now()]);

        $html = $this->page($owner, route('newsletter.segments', $param));

        $this->assertSame(0, substr_count($html, 'class="page-empty"'));
        $this->assertSame(1, substr_count($html, '<table class="page-table'));
        $this->assertLessThan(strpos($html, 'id="create-segment-app"'), strpos($html, '<table class="page-table'), 'the list comes first');
        $this->assertStringContainsString('Regulars', $html);
        $this->assertStringContainsString(__('messages.manual_entries').': 1', $html);
    }

    public function test_the_import_page_is_not_an_alpine_island(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Regulars {{ 7 * 7 }}', 'type' => 'manual']);

        $html = $this->page($owner, route('newsletter.import', ['role_id' => UrlUtils::encodeId($role->id)]));

        $this->assertSame(1, substr_count($html, 'id="import-emails-app"'));
        $mount = substr($html, strpos($html, 'id="import-emails-app"'), strpos($html, 'Vue.createApp') - strpos($html, 'id="import-emails-app"'));

        // The shell around the page still has Alpine of its own; the page's island has none.
        $this->assertSame(0, preg_match('/\sx-(data|show|model|text|cloak|ref|if|for)[=\s>]/', $mount), 'no Alpine directive in the island');
        $this->assertSame(3, substr_count($mount, 'role="tab"'), 'form entry, paste and CSV');

        // A segment's name reaches the Vue mount as data, never as text Vue would compile.
        $this->assertStringNotContainsString('Regulars', $mount);
        $this->assertStringContainsString('Regulars', $html);
    }

    public function test_the_allowance_meter_says_what_the_plan_gives_and_what_is_used(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $this->assertFalse($role->fresh()->isPro());
        $param = ['role_id' => UrlUtils::encodeId($role->id)];

        $html = $this->page($owner, route('newsletter.index', $param));
        $this->assertSame(1, substr_count($html, 'role="progressbar"'));
        $this->assertStringContainsString(__('messages.newsletters_used', ['used' => 0, 'limit' => 10]), $html);

        $this->newsletter($role, $owner, ['status' => 'sent', 'sent_at' => now(), 'sent_count' => 4]);

        $html = $this->page($owner, route('newsletter.index', $param));
        $this->assertStringContainsString(__('messages.newsletters_used', ['used' => 4, 'limit' => 10]), $html);
        $this->assertStringContainsString(__('messages.newsletters_remaining', ['count' => 6]), $html);

        // The builder says it too, once.
        $this->assertSame(1, substr_count($this->page($owner, route('newsletter.create', $param)), 'role="progressbar"'));

        // An install with no plans has no allowance to show.
        config(['app.hosted' => false]);
        $this->assertSame(0, substr_count($this->page($owner, route('newsletter.index', $param)), 'role="progressbar"'));
    }

    public function test_the_stats_page_says_a_recipients_status_in_words(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newsletter = $this->newsletter($role, $owner, ['status' => 'sent', 'sent_at' => now()->subDay(), 'sent_count' => 2, 'open_count' => 1]);

        foreach (['sent' => 'reader@example.com', 'failed' => 'gone@example.com'] as $status => $email) {
            NewsletterRecipient::create([
                'newsletter_id' => $newsletter->id,
                'email' => $email,
                'name' => 'Reader',
                'token' => Str::random(40),
                'status' => $status,
                'opened_at' => $status === 'sent' ? now() : null,
                'open_count' => $status === 'sent' ? 1 : 0,
            ]);
        }

        $html = $this->page($owner, route('newsletter.stats', ['hash' => UrlUtils::encodeId($newsletter->id), 'role_id' => UrlUtils::encodeId($role->id)]));

        $this->assertSame(1, substr_count($html, 'page-stats is-auto'), 'the figures are one strip');
        $this->assertSame(1, substr_count($html, '<table class="page-table'));
        $this->assertSame(1, substr_count($html, '<span class="event-status is-on">'.__('messages.sent').'</span>'));
        $this->assertSame(1, substr_count($html, '<span class="event-status is-bad">'.__('messages.failed').'</span>'));

        // The owner sees who received it: these are the schedule's own followers.
        $this->assertStringContainsString('reader@example.com', $html);

        // The way back is named.
        $this->assertSame(1, preg_match('/class="page-back">.*?<bdi>'.preg_quote(__('messages.newsletters'), '/').'<\/bdi>/s', $html));
    }

    public function test_the_admin_templates_page_has_the_sidebar_and_the_admin_navigation(): void
    {
        $admin = $this->createOwner(admin: true);
        NewsletterTemplate::create(['role_id' => null, 'user_id' => $admin->id, 'name' => 'Product update', 'template' => 'bold', 'blocks' => [], 'style_settings' => ['accentColor' => 'red;}</style>'], 'is_system' => false]);

        $html = $this->adminPage($admin, route('admin.newsletters.templates'));

        // It rendered through the bare shell: no sidebar, no admin navigation.
        $this->assertSame(1, substr_count($html, 'id="sidebar"'), 'the sidebar');
        $this->assertSame(1, substr_count($html, 'id="admin-nav"'), 'the admin navigation');

        $this->assertStringContainsString('Product update', $html);
        $this->assertSame(1, substr_count($html, 'class="ap-card rounded-xl news-template"'));

        // A saved colour that is not a colour never reaches a style attribute.
        $this->assertStringNotContainsString('red;}', $html);
    }

    public function test_the_admin_pages_share_three_tabs_and_every_one_has_the_navigation(): void
    {
        $admin = $this->createOwner(admin: true);
        $draft = Newsletter::create(['role_id' => null, 'user_id' => $admin->id, 'type' => 'admin', 'subject' => 'Maintenance', 'status' => 'draft', 'template' => 'modern']);
        $segment = NewsletterSegment::create(['role_id' => null, 'name' => 'Everyone', 'type' => 'all_users']);

        $tabs = [
            'newsletters' => route('admin.newsletters.index'),
            'segments' => route('admin.newsletters.segments'),
            'templates' => route('admin.newsletters.templates'),
        ];

        foreach ($tabs as $tab => $url) {
            $html = $this->adminPage($admin, $url);

            $expected = [];
            foreach ($tabs as $key => $address) {
                $expected[$address] = $key === $tab;
            }

            $this->assertSame($expected, $this->stripTabs($html, 'admin-newsletter-tabs'), "the strip on the admin {$tab} page");
            // A phone keeps this strip: the admin navigation above it is already a dropdown there,
            // and a second one under it read the same word.
            $this->assertSame(0, substr_count($html, 'id="admin-newsletter-tabs-select"'), "no second dropdown on the admin {$tab} page");
            $this->assertSame(1, substr_count($html, 'class="ap-tabs-wrap is-always"'), "the strip stays on a phone on the admin {$tab} page");
            $this->assertSame(1, substr_count($html, 'id="admin-nav"'), "the admin navigation on the {$tab} page");
        }

        // The pages that hang from a tab: the builder had no admin navigation at all.
        $hanging = [
            route('admin.newsletters.create'),
            route('admin.newsletters.edit', ['hash' => UrlUtils::encodeId($draft->id)]),
            route('admin.newsletters.template.create'),
            route('admin.newsletters.segment.edit', ['hash' => UrlUtils::encodeId($segment->id)]),
        ];

        foreach ($hanging as $url) {
            $html = $this->adminPage($admin, $url);

            $this->assertSame(1, substr_count($html, 'id="admin-nav"'), "the admin navigation on {$url}");
            $this->assertSame(1, substr_count($html, 'class="page-back"'), "a named way back on {$url}");
        }
    }

    public function test_the_admin_segment_form_is_not_an_alpine_island_and_uses_the_portals_date_picker(): void
    {
        config(['app.hosted' => true]);
        $admin = $this->createOwner(admin: true);

        $html = $this->adminPage($admin, route('admin.newsletters.segments'));

        $this->assertSame(1, preg_match('/<form[^>]*id="create-segment-form".*?<\/form>/s', $html, $form));

        $this->assertSame(0, preg_match('/\sx-(data|show|model|bind|cloak)[=:\s>]/', $form[0]), 'no Alpine directive in the form');
        $this->assertSame(0, substr_count($form[0], 'type="date"'), 'Flatpickr, not the native date field');
        $this->assertSame(2, substr_count($form[0], 'js-segment-date"'), 'two dates');
        $this->assertSame(1, substr_count($form[0], 'value="plan_tier"'));
    }

    /**
     * The builder is a compiled Vue component, so the views dress it from outside
     * (newsletter/partials/_builder-styles), keyed on classes its markup carries. If one of those
     * leaves the component the rule that leaned on it silently stops applying: yellow and green
     * buttons come back, or Save stops being last.
     */
    public function test_the_builders_stylesheet_still_matches_the_component(): void
    {
        $component = file_get_contents(resource_path('js/components/NewsletterBuilder.vue'));

        foreach ([
            'class="flex border-b',
            'palette-item',
            'block-item',
            'bg-[var(--brand-button-bg)]',
            'bg-yellow-500',
            'bg-green-600',
            'border-gray-300',
            'class="mt-4 flex flex-wrap gap-3 justify-between"',
            'class="fixed inset-0',
        ] as $hook) {
            $this->assertStringContainsString($hook, $component, "the builder still carries {$hook}");
        }

        // Every page that holds the builder loads the sheet.
        foreach (['newsletter/create', 'newsletter/edit', 'newsletter/template-edit', 'admin/newsletters/create', 'admin/newsletters/edit', 'admin/newsletters/template-edit'] as $view) {
            $source = file_get_contents(resource_path("views/{$view}.blade.php"));

            $this->assertStringContainsString("@include('newsletter.partials._builder-styles')", $source, $view);
            $this->assertStringContainsString("@include('newsletter.partials._builder')", $source, $view);
        }
    }
}
