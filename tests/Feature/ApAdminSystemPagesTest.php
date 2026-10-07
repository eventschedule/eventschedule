<?php

namespace Tests\Feature;

use App\Models\FederatedInstance;
use App\Models\LegalDocument;
use App\Models\User;
use App\Services\AdminAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The platform operator's System pages (/admin/audit-log, queue, logs, app-update, support,
 * settings, translations, legal, federation), rebuilt on the page kit in October 2026.
 *
 * What the pages look like belongs to the screenshots. These hold what was unified (one opening,
 * one strip of figures, one way of asking before something is destroyed) and the defects that
 * were found on the way: a refused legal save that came back on all three cards, an error copied
 * with its quotes turned into entities, conversations a keyboard could not reach.
 *
 * Assertions count with substr_count()/preg_match() and never hand a whole page to a pattern
 * assertion: a failure would print the page.
 */
class ApAdminSystemPagesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // No page here may reach the network: the suite loads the developer's .env, where real
        // tokens live, and a page that asks an outside service (the Domains page asked
        // DigitalOcean) would do it for real on every run. A request nothing faked throws.
        \Illuminate\Support\Facades\Http::preventStrayRequests();

        config(['app.hosted' => true, 'app.is_nexus' => true]);
        $this->admin = $this->createOwner(true);
    }

    private function page(string $url): string
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->admin)
            ->get($url)
            ->assertOk()
            ->getContent();
    }

    private function source(string $name): string
    {
        return file_get_contents(resource_path('views/admin/'.$name.'.blade.php'));
    }

    /** Every page opens the same way: the navigation, one line on what the page is for. */
    public function test_every_system_page_opens_with_one_lead_and_sits_in_the_page_shell(): void
    {
        foreach ([
            'admin.audit_log', 'admin.queue', 'admin.logs', 'admin.support', 'admin.settings',
            'admin.translations', 'admin.legal', 'admin.federation',
        ] as $route) {
            $html = $this->page(route($route));

            $this->assertSame(1, substr_count($html, '<div class="page-head">'), "{$route}: one head");
            $this->assertSame(1, substr_count($html, 'class="page-lead"'), "{$route}: one lead");
            $this->assertSame(1, preg_match('/class="page-shell[ "]/', $html), "{$route}: the page shell");
            // The older openings: a card whose h2 repeated the tab's name, and the capitals-and
            // -gradient danger button.
            $this->assertSame(0, substr_count($html, 'tracking-widest'), "{$route}: no capital-letter button");
        }
    }

    /** Audit log, Queue and Logs each had four tiles of their own making; now one strip each. */
    public function test_the_three_lists_show_their_figures_as_one_strip(): void
    {
        foreach (['admin.audit_log', 'admin.queue'] as $route) {
            $html = $this->page(route($route));

            $this->assertSame(1, substr_count($html, 'class="ap-card rounded-xl page-stats is-auto"'), "{$route}: one strip");
            $this->assertSame(4, substr_count($html, 'class="page-stat"'), "{$route}: four figures");
            $this->assertSame(0, substr_count($html, 'dashboard-stat-value'), "{$route}: no hand-rolled tile");
        }

        // The tiles the three pages drew for themselves: an icon, a label and a 2xl or 3xl figure.
        foreach (['audit-log', 'queue', 'logs'] as $name) {
            $this->assertSame(0, preg_match('/text-[23]xl font-bold/', $this->source($name)), "{$name}: no hand-rolled tile");
        }

        // The log page reads a file, so its markup is checked at the source.
        $logs = $this->source('logs');
        $this->assertSame(1, substr_count($logs, 'class="ap-card rounded-xl page-stats is-auto"'));
        $this->assertSame(4, substr_count($logs, 'class="page-stat"'));
        $this->assertSame(0, substr_count($logs, 'dashboard-stat-value'));
    }

    /** One handler asks before destroying (the layout's, on form[data-confirm]). */
    public function test_destroying_actions_ask_first_through_the_layouts_handler(): void
    {
        foreach (['queue', 'logs', 'app-update'] as $name) {
            $source = $this->source($name);

            $this->assertSame(0, substr_count($source, 'js-confirm-form'), "{$name}: the layout skips forms of that class, and the page's own handler is gone");
            $this->assertSame(0, substr_count($source, "confirm(form.getAttribute('data-confirm'))"), "{$name}: no second handler");
        }

        $this->assertSame(1, preg_match('/route\(\'admin\.logs\.clear\'\) }}" data-confirm="\{\{ __\(\'messages\.confirm_clear_log\'\) }}"/', $this->source('logs')), 'Clear log asks');
        $this->assertSame(1, substr_count($this->source('logs'), 'class="page-tool is-danger"'), 'and it is the red one');

        $queue = $this->source('queue');
        foreach (['clear-failed' => 'confirm_delete_all_failed', 'flush-pending' => 'confirm_flush_pending', 'retry-all' => 'confirm_retry_all_failed'] as $action => $key) {
            $this->assertSame(1, preg_match('/admin\.queue\.'.$action.'\'\) }}" data-confirm="\{\{ __\(\'messages\.'.$key.'\'/', $queue), "{$action} asks");
        }
    }

    /** {{ e($copyText) }} escaped twice, so the copied error read &quot;elseif&quot;. */
    public function test_the_copied_error_is_escaped_once(): void
    {
        $logs = $this->source('logs');

        $this->assertSame(0, substr_count($logs, 'e($copyText)'));
        $this->assertSame(2, substr_count($logs, 'data-copy="{{ $copyText }}"'));
        $this->assertSame(3, substr_count($logs, 'js-copy-error'), 'the copy button is kept on both lists, and its script');
    }

    /** "Never run" is the card's verdict: a status mark, not a headline. */
    public function test_the_queue_page_says_how_the_scheduler_stands_as_a_status_mark(): void
    {
        $html = $this->page(route('admin.queue'));

        $this->assertSame(1, preg_match('/<span class="event-status sys-verdict is-bad">\s*'.preg_quote(__('messages.scheduler_never_ran'), '/').'\s*<\/span>/', $html));
        $this->assertSame(1, substr_count($html, e(__('messages.queue_health_issues'))), 'the health notice');
        $this->assertSame(1, preg_match('/<div class="flex items-start gap-3 border rounded-lg p-3 bg-red-50[^"]*">/', $html), 'which is the kit\'s error notice');
        // It is part of the page, there on every load, so it is not a live region: as role="alert"
        // a screen reader read it out each time the page opened.
        $this->assertSame(0, substr_count($html, 'role="alert"'));
    }

    /** The three legal forms share their field names; a refused save came back on all three. */
    public function test_a_refused_legal_save_comes_back_on_its_own_card_only(): void
    {
        LegalDocument::create(['type' => 'terms', 'content' => 'The terms as saved.']);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->admin)
            ->from(route('admin.legal'))
            ->post(route('admin.legal.update', ['type' => 'privacy']), [
                '_card' => 'privacy',
                'url' => 'javascript:refused',
                'content' => 'Typed and refused.',
            ])
            ->assertRedirect(route('admin.legal'))
            ->assertSessionHasErrors('url');

        $html = $this->get(route('admin.legal'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'Typed and refused.'), 'what was typed is back in its own editor only');
        $this->assertSame(1, substr_count($html, 'value="javascript:refused"'), 'and its address in its own field only');
        $this->assertSame(1, substr_count($html, 'The terms as saved.'), 'the neighbour still shows what is saved');
        $this->assertSame(1, substr_count($html, '<ul class="text-sm text-red-600'), 'one message, on the card that was refused');
    }

    /** Each card says which of the three is in force. */
    public function test_each_legal_card_says_what_is_in_force(): void
    {
        LegalDocument::create(['type' => 'terms', 'content' => 'Our own terms.']);
        LegalDocument::create(['type' => 'cookies', 'url' => 'https://example.com/cookies', 'content' => 'Never shown.']);

        $html = $this->page(route('admin.legal'));

        $mark = fn (string $tone, string $key) => '<span class="event-status '.$tone.'">'.e(__('messages.'.$key)).'</span>';
        $this->assertSame(1, substr_count($html, $mark('', 'legal_status_builtin')), 'privacy: the built-in page');
        $this->assertSame(1, substr_count($html, $mark('is-on', 'legal_status_own')), 'terms: the document');
        $this->assertSame(1, substr_count($html, $mark('is-info', 'legal_document_url')), 'cookies: the address wins');
        // A document nobody is shown is said as a notice, not as amber text in the help line.
        $this->assertSame(1, substr_count($html, e(__('messages.legal_document_url_in_use'))));
        $this->assertSame(0, substr_count($html, 'text-amber-700 dark:text-amber-300">'.e(__('messages.legal_document_url_in_use'))));
    }

    /** Other pages link to the cards by id, and each card saves only itself. */
    public function test_the_settings_cards_keep_their_anchors_and_one_save_each(): void
    {
        $html = $this->page(route('admin.settings'));

        foreach (['plan-pricing', 'currency', 'realtime'] as $id) {
            // Side by side on a wide window, like every titled card of this page (AdminPortalKitTest).
            $this->assertSame(1, substr_count($html, '<section class="ap-card rounded-xl page-card is-beside scroll-mt-24" id="'.$id.'">'), "#{$id}");
        }

        preg_match_all('/<form method="POST" action="[^"]*\/admin\/settings[^"]*"/', $html, $forms);
        $this->assertGreaterThanOrEqual(4, count($forms[0]));
        $this->assertSame(count($forms[0]), substr_count($html, '<div class="page-form-actions">'), 'one Save row per form');

        // Selfhost gets the network card, and the dashboard prompt links to it.
        config(['app.hosted' => false, 'app.is_nexus' => false, 'app.url' => config('app.url') ?: 'http://localhost']);
        AdminAlertService::flush();
        $selfhost = $this->page(route('admin.settings'));
        $this->assertSame(1, substr_count($selfhost, '<section class="ap-card rounded-xl page-card is-beside scroll-mt-24" id="federation">'));
    }

    /** The four states are the kit's tabs, kept as a strip on a phone too. */
    public function test_the_federation_states_are_tabs_from_one_list(): void
    {
        FederatedInstance::create([
            'instance_id' => (string) Str::uuid(), 'site_url' => 'https://operator.test', 'name' => 'Operator',
            'contact_email' => 'ops@operator.test', 'secret' => Str::random(40), 'app_version' => 'v1.0.133',
            'status' => FederatedInstance::STATUS_PENDING,
        ]);

        $html = $this->page(route('admin.federation'));

        $this->assertSame(1, preg_match('/<nav class="ap-tabs" id="federation-tabs"[^>]*>(.*?)<\/nav>/s', $html, $nav));
        $this->assertSame(4, substr_count($nav[1], 'class="ap-tab"'), 'Pending, Approved, Suspended, All: Flagged only while something is flagged');
        $this->assertSame(1, substr_count($nav[1], 'aria-current="page"'));
        $this->assertSame(1, substr_count($nav[1], '<span class="ap-tab-count is-waiting">1</span>'), 'what waits is counted');

        // A phone keeps the strip: the admin navigation above it is already a dropdown there, and
        // a second one under it ("Federation (3)" over "Pending (2)") was two boxes to open.
        $this->assertSame(0, substr_count($html, 'id="federation-tabs-select"'));
        $this->assertSame(1, substr_count($html, 'class="ap-tabs-wrap is-always"'));

        // The row's actions in the kit's voice: the one that takes an install off the network is red.
        $this->assertSame(1, preg_match('/formaction="[^"]*\/suspend"\s+class="page-tool is-danger"/', $html));
    }

    /** A conversation in the list is a button, and the page's translated line is outside Vue. */
    public function test_the_support_inbox_can_be_used_from_a_keyboard_and_keeps_its_wiring(): void
    {
        $html = $this->page(route('admin.support'));

        $this->assertSame(1, substr_count($html, '<button type="button" v-for="conv in conversations"'));
        $this->assertSame(0, substr_count($html, '<div v-for="conv in conversations"'));
        $this->assertLessThan(strpos($html, 'id="support-admin-app"'), strpos($html, 'class="page-lead"'), 'the lead is not a text node inside the mount');

        // What the page's script and the sidebar popover rely on.
        foreach (['toggleAvailability($event.target)', 'ref="adminMessagesContainer"', "window.addEventListener('support-presence-changed'", 'this.startPolling();', '@click="mobileShowConversation = false"'] as $hook) {
            $this->assertGreaterThanOrEqual(1, substr_count($html, $hook), $hook);
        }
    }

    /** The way back from the suggestions names the page it leads to. */
    public function test_the_suggestions_page_hangs_from_translations_by_name(): void
    {
        $html = $this->page(route('admin.translations.suggestions'));

        $this->assertSame(1, preg_match('/<a href="[^"]*\/admin\/translations" class="page-back">/', $html));
        $this->assertSame(1, preg_match('/"back":'.preg_quote(json_encode(__('messages.translations')), '/').'/', $html));
        $this->assertSame(1, preg_match('/"outdated":'.preg_quote(json_encode(__('messages.suggestion_outdated')), '/').'/', $html), 'a short label, where the whole explanation stood in a pill');
    }

    /** Selfhost only: installed, latest and last checked as one strip, and the update asks first. */
    public function test_the_app_update_page_is_on_the_kit(): void
    {
        config([
            'app.is_nexus' => false, 'app.is_testing' => false, 'app.hosted' => false,
            'app.url' => config('app.url') ?: 'http://localhost',
            'self-update.version_installed' => 'v1.0.100',
        ]);
        AdminAlertService::flush();
        \Illuminate\Support\Facades\Cache::put(\App\Services\AppUpdateService::CACHE_KEY, 'v1.0.101', 600);

        $html = $this->page(route('admin.app_update'));

        $this->assertSame(1, substr_count($html, 'class="ap-card rounded-xl page-stats"'));
        $this->assertSame(3, substr_count($html, 'class="page-stat"'));
        $this->assertSame(1, preg_match('/action="[^"]*\/admin\/app-update\/run" data-confirm="[^"]+"/', $html), 'updating asks first');
        $this->assertSame(1, substr_count($html, '<span class="event-status is-warn">'.e(__('messages.app_update_available')).'</span>'));
    }
}
