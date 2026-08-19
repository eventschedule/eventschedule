<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Operator-authored legal documents (issue #116).
 *
 * The point of the feature is that a selfhost install stops sending its users to
 * eventschedule.com's privacy policy and terms, so the assertions below care as
 * much about where the CONSENT LINKS point as about the pages themselves.
 */
class LegalPagesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        // index() memoizes per request; the static outlives RefreshDatabase's
        // rollback, so without this a test sees the previous test's documents.
        LegalDocument::flush();
    }

    protected function tearDown(): void
    {
        LegalDocument::flush();

        parent::tearDown();
    }

    private function adminActing(): User
    {
        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    /**
     * marketing_url() returns a LOCAL url while app.is_testing is on, which makes
     * "the built-in fallback" and "our own page" the same string and hides any
     * precedence bug. Tests that assert on resolution turn it off.
     */
    private function withRemoteMarketingSite(): void
    {
        config(['app.is_testing' => false, 'app.marketing_url' => 'https://eventschedule.com']);
    }

    /**
     * A plain selfhost install: path-based routing, one tenant, the legal routes reachable on
     * the only host there is. A multi-tenant install shares is_nexus=false but not app.hosted,
     * and policy_url() answers it differently - see
     * test_on_a_multi_tenant_install_a_written_document_is_linked_on_the_app_host().
     *
     * app.url is pinned because turning off BOTH hosted and is_testing - every caller pairs
     * this with withRemoteMarketingSite(), which does the second - re-arms
     * selfhost_needs_setup(): it reads a blank APP_URL as a fresh install and has
     * EnsureSelfhostSetup redirect every request to the setup wizard, so the page under test
     * never renders. CI copies .env.example, where APP_URL is blank, so it cannot be left
     * ambient - that is why this passed locally and failed on the build.
     */
    private function selfhost(): void
    {
        config([
            'app.is_nexus' => false,
            'app.hosted' => false,
            'app.url' => config('app.url') ?: 'http://localhost',
        ]);
    }

    // ---------------------------------------------------------------- admin endpoint

    public function test_a_non_admin_cannot_save_a_legal_document(): void
    {
        $this->actingAs($this->createOwner());

        $this->post(route('admin.legal.update', ['type' => 'privacy']), ['content' => 'Sneaky']);

        $this->assertDatabaseCount('legal_documents', 0);
    }

    public function test_an_admin_can_save_a_document_and_the_html_is_derived_and_purified(): void
    {
        $this->adminActing();

        $this->post(route('admin.legal.update', ['type' => 'privacy']), [
            'content' => "## Who we are\n\nWe collect **very little**.\n\n<script>alert(1)</script>",
        ])->assertRedirect();

        $document = LegalDocument::where('type', 'privacy')->firstOrFail();

        $this->assertStringContainsString('<h2 id="who-we-are">Who we are</h2>', $document->content_html);
        $this->assertStringContainsString('<strong>very little</strong>', $document->content_html);
        $this->assertStringNotContainsString('<script', $document->content_html);
        $this->assertStringNotContainsString('alert(1)', $document->content_html);
    }

    public function test_an_admin_can_point_a_document_at_an_external_url(): void
    {
        $this->adminActing();

        $this->post(route('admin.legal.update', ['type' => 'terms']), [
            'url' => 'https://example.com/legal/terms',
        ])->assertRedirect();

        $this->assertSame('https://example.com/legal/terms', LegalDocument::where('type', 'terms')->value('url'));
    }

    /**
     * The URL is rendered straight into an href on public pages. The `url` rule
     * alone turns away javascript: and data:, but happily accepts ftp: and every
     * other exotic scheme, which is what the explicit scheme pin is for.
     */
    public function test_only_http_and_https_urls_are_accepted(): void
    {
        $this->adminActing();

        foreach (['javascript:alert(1)', 'javascript://example.com/%0aalert(1)',
            'data:text/html,<script>alert(1)</script>', 'ftp://example.com/policy.txt'] as $url) {
            $this->post(route('admin.legal.update', ['type' => 'privacy']), ['url' => $url])
                ->assertSessionHasErrors('url');
        }

        $this->assertDatabaseCount('legal_documents', 0);

        $this->post(route('admin.legal.update', ['type' => 'privacy']), ['url' => 'https://example.com/p'])
            ->assertSessionHasNoErrors();

        $this->assertSame('https://example.com/p', LegalDocument::where('type', 'privacy')->value('url'));
    }

    public function test_saving_one_document_leaves_the_others_alone(): void
    {
        $this->adminActing();

        $this->post(route('admin.legal.update', ['type' => 'privacy']), ['content' => 'Our privacy policy']);
        $this->post(route('admin.legal.update', ['type' => 'terms']), ['url' => 'https://example.com/terms']);
        $this->post(route('admin.legal.update', ['type' => 'cookies']), ['content' => 'Our cookie policy']);

        $this->post(route('admin.legal.update', ['type' => 'terms']), ['url' => 'https://example.com/terms-v2']);

        $this->assertSame('Our privacy policy', LegalDocument::where('type', 'privacy')->value('content'));
        $this->assertSame('https://example.com/terms-v2', LegalDocument::where('type', 'terms')->value('url'));
        $this->assertSame('Our cookie policy', LegalDocument::where('type', 'cookies')->value('content'));
    }

    /**
     * The saving hook is guarded on isDirty('content'), because a save that did
     * not load the body would otherwise null the rendered HTML out from under
     * the live page. Flip the guard off in the model and this fails.
     */
    public function test_a_save_that_does_not_touch_the_content_keeps_the_rendered_html(): void
    {
        LegalDocument::create(['type' => 'privacy', 'content' => '## Kept']);

        // How BackupService-style callers and any partial select behave: a model
        // hydrated without the content column, then saved.
        $partial = LegalDocument::query()->select(['id', 'type', 'url'])->where('type', 'privacy')->firstOrFail();
        $partial->url = null;
        $partial->save();

        $this->assertStringContainsString('Kept', LegalDocument::where('type', 'privacy')->value('content_html'));
    }

    // ---------------------------------------------------------------- resolution

    public function test_policy_url_prefers_the_external_url_then_the_document_then_the_default(): void
    {
        $this->withRemoteMarketingSite();

        $this->assertSame('https://eventschedule.com/privacy', policy_url('privacy'));

        LegalDocument::create(['type' => 'privacy', 'content' => 'Ours']);
        $this->assertSame(url('/privacy'), policy_url('privacy'));

        LegalDocument::where('type', 'privacy')->firstOrFail()->update(['url' => 'https://example.com/p']);
        $this->assertSame('https://example.com/p', policy_url('privacy'));
    }

    public function test_the_terms_fallback_argument_is_honoured(): void
    {
        $this->withRemoteMarketingSite();

        $this->assertSame(
            'https://eventschedule.com/self-hosting-terms-of-service',
            policy_url('terms', '/self-hosting-terms-of-service')
        );

        LegalDocument::create(['type' => 'terms', 'content' => 'Ours']);

        $this->assertSame(url('/terms-of-service'), policy_url('terms', '/self-hosting-terms-of-service'));
    }

    /**
     * There is no built-in cookie policy page, so until one is written the cookie
     * banner has to keep pointing where it always has: the privacy policy.
     */
    public function test_the_cookie_policy_falls_back_to_the_privacy_policy(): void
    {
        $this->withRemoteMarketingSite();

        $this->assertSame('https://eventschedule.com/privacy', policy_url('cookies'));

        LegalDocument::create(['type' => 'privacy', 'url' => 'https://example.com/p']);
        $this->assertSame('https://example.com/p', policy_url('cookies'));

        LegalDocument::create(['type' => 'cookies', 'content' => 'Our cookie policy']);
        $this->assertSame(url('/cookie-policy'), policy_url('cookies'));
    }

    // ---------------------------------------------------------------- public pages

    public function test_the_built_in_privacy_page_is_served_until_a_document_is_written(): void
    {
        // The bundled page, not ours: it carries the legal-instrument markup.
        $this->get('/privacy')->assertOk()->assertSee('es-fine-h', false);

        LegalDocument::create(['type' => 'privacy', 'content' => '## Our own policy']);

        $this->get('/privacy')->assertOk()
            ->assertSee('<h2 id="our-own-policy">Our own policy</h2>', false)
            ->assertDontSee('es-fine-h', false);
    }

    public function test_a_document_with_an_external_url_redirects(): void
    {
        LegalDocument::create(['type' => 'terms', 'url' => 'https://example.com/terms']);

        $this->get('/terms-of-service')->assertRedirect('https://example.com/terms');
    }

    public function test_the_cookie_policy_page_only_exists_once_it_is_written(): void
    {
        $this->get('/cookie-policy')->assertNotFound();

        LegalDocument::create(['type' => 'cookies', 'content' => '## Cookies we set']);

        $this->get('/cookie-policy')->assertOk()->assertSee('Cookies we set');
    }

    /**
     * The app mounts Vue with the runtime template compiler, so text rendered
     * inside a Vue mount is compiled as a template. The legal layout mounts no
     * Vue and the admin page must not either.
     */
    public function test_a_document_containing_vue_mustaches_renders_literally(): void
    {
        LegalDocument::create(['type' => 'privacy', 'content' => 'Retention is {{ 7*7 }} days.']);

        $this->get('/privacy')->assertOk()
            ->assertSee('{{ 7*7 }}', false)
            // Not a bare '49': the page carries a random 40-character CSRF token and a random
            // base64 CSP nonce, either of which can contain those two digits by chance. The
            // claim is that nothing evaluated the expression, so assert the sentence it would
            // have produced - and, directly, that nothing mounts Vue on this page.
            ->assertDontSee('id="app"', false)
            ->assertDontSee('Retention is 49 days');

        $this->adminActing();

        $this->get(route('admin.legal'))->assertOk()
            ->assertSee('{{ 7*7 }}', false)
            ->assertDontSee('id="app"', false);
    }

    /**
     * External URLs on purpose: marketing_url() is local while app.is_testing is
     * on, so a link to our own /privacy and a link to the marketing site's are the
     * same string and would prove nothing. An off-site URL can only have come from
     * the operator's document.
     */
    public function test_the_consent_links_follow_the_operators_documents(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->actingAs($owner);

        LegalDocument::create(['type' => 'privacy', 'url' => 'https://example.com/privacy']);
        LegalDocument::create(['type' => 'terms', 'url' => 'https://example.com/terms']);

        // The admin portal's About menu links both documents on every page.
        $this->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->assertSee('https://example.com/privacy', false)
            ->assertSee('https://example.com/terms', false);
    }

    public function test_the_cookie_banner_learn_more_link_follows_the_cookie_policy(): void
    {
        config(['app.cookie_consent_banner' => true]);

        LegalDocument::create(['type' => 'privacy', 'url' => 'https://example.com/privacy']);

        // No cookie policy yet: the banner keeps pointing at the privacy policy.
        $this->get('/login')->assertOk()
            ->assertSee('https://example.com/privacy', false)
            ->assertDontSee('https://example.com/cookies', false);

        LegalDocument::create(['type' => 'cookies', 'url' => 'https://example.com/cookies']);

        $this->get('/login')->assertOk()->assertSee('https://example.com/cookies', false);
    }

    /**
     * Both fields set. policy_url(), the model docblock, the admin UI copy and the
     * docs all say the external URL wins; LegalController used to say the opposite,
     * so links went off-site while the page here rendered the in-app document.
     */
    public function test_an_external_url_beats_an_in_app_document_for_both_the_link_and_the_page(): void
    {
        LegalDocument::create([
            'type' => 'privacy',
            'url' => 'https://example.com/privacy',
            'content' => '## Written here too',
        ]);

        $this->assertSame('https://example.com/privacy', policy_url('privacy'));
        $this->get('/privacy')->assertRedirect('https://example.com/privacy');
    }

    /**
     * A draft that HTML Purifier strips to nothing used to save happily, serve a
     * blank 200 page, and retarget every consent link on the install at it.
     */
    public function test_a_document_that_purifies_to_nothing_is_rejected(): void
    {
        $this->adminActing();

        $this->post(route('admin.legal.update', ['type' => 'privacy']), [
            'content' => '<!-- TODO: write this -->',
        ])->assertSessionHasErrors('content');

        $this->assertDatabaseCount('legal_documents', 0);

        // And the belt to that braces: were such a row to exist, it must not be
        // treated as published.
        LegalDocument::create(['type' => 'privacy', 'content' => '<!-- TODO -->']);

        $this->assertFalse(LegalDocument::index()['privacy']['has_content']);
        $this->get('/privacy')->assertOk()->assertSee('es-fine-h', false);
    }

    // ---------------------------------------------------------------- selfhost

    /**
     * phpunit pins IS_NEXUS=true, so the branch every selfhost install actually
     * runs - and the reported bug is about - is only reachable by flipping the
     * config, the way FederationSettingsTest and PromotionServingTest do.
     */
    public function test_on_a_selfhost_install_an_unwritten_document_goes_to_the_marketing_site(): void
    {
        $this->selfhost();
        $this->withRemoteMarketingSite();

        $this->get('/privacy')->assertRedirect('https://eventschedule.com/privacy');
        $this->get('/terms-of-service')->assertRedirect('https://eventschedule.com/terms-of-service');
        $this->get('/cookie-policy')->assertNotFound();
    }

    public function test_on_a_selfhost_install_a_written_document_is_served_locally(): void
    {
        $this->selfhost();
        $this->withRemoteMarketingSite();

        LegalDocument::create(['type' => 'privacy', 'content' => '## Our own policy']);

        $this->get('/privacy')->assertOk()
            ->assertSee('<h2 id="our-own-policy">Our own policy</h2>', false)
            ->assertDontSee('es-fine-h', false);

        $this->assertSame(url('/privacy'), policy_url('privacy'));
    }

    /**
     * marketing_url() returns a LOCAL url in testing mode, so the fallback branch
     * would otherwise redirect this route to itself.
     *
     * Deliberately not selfhost(): app.is_testing stays ON here, because that is the branch
     * under test - which also means selfhost_needs_setup() short-circuits and the helper's
     * app.url pin would be moot.
     */
    public function test_the_selfhost_fallback_does_not_redirect_to_itself(): void
    {
        config(['app.is_nexus' => false]);

        $this->get('/privacy')->assertOk()->assertSee('es-fine-h', false);
    }

    // ------------------------------------------------- which HOST the links point at

    /**
     * On a nexus the legal routes are registered inside Route::domain(_base_domain()), so a
     * consent link has to land on the marketing host whatever host the visitor is on.
     *
     * policy_url() used url(), which builds against the REQUEST host - so a buyer on
     * tenant.example.com was handed a URL that the tenant group's /{slug} catch-all answers
     * (the schedule's own page), and one on app.example.com a hard 404. That is the same
     * "consenting to the wrong document" failure issue #116 exists to fix, one layer up.
     *
     * phpunit takes the app.is_testing branch of routes/web.php, where these routes are
     * domain-less - so the host distinction does not exist here unless it is put back. Hence
     * the re-registration below: without it this test passes against the broken helper too.
     */
    public function test_on_a_nexus_a_written_document_is_linked_on_the_marketing_host(): void
    {
        $this->withRemoteMarketingSite();
        config(['app.is_nexus' => true]);

        Route::domain('marketing.example.test')
            ->get('/privacy', fn () => '')
            ->name('marketing.privacy');
        Route::getRoutes()->refreshNameLookups();

        // The visitor is on a tenant host, which is where every consent link is rendered.
        URL::setRequest(Request::create('https://tenant.example.test/some-schedule'));

        LegalDocument::create(['type' => 'privacy', 'content' => 'Ours']);

        $this->assertSame('https://marketing.example.test/privacy', policy_url('privacy'));
        $this->assertNotSame(url('/privacy'), policy_url('privacy'));
    }

    /**
     * A multi-tenant install with no marketing site (a self-hosted SaaS): the legal routes are
     * domain-less, but the Route::domain('{subdomain}...') group is registered ~1500 lines
     * earlier in routes/web.php, so on a tenant host its /{slug} catch-all still wins. The app
     * host is the one host that group excludes ('^(?!www|app).*').
     */
    public function test_on_a_multi_tenant_install_a_written_document_is_linked_on_the_app_host(): void
    {
        config([
            'app.is_nexus' => false,
            'app.hosted' => true,
            // Both of these send app_url() down its local branch, and neither describes the
            // install being modelled. Stated here rather than inherited so the test cannot
            // start passing for the wrong reason if phpunit.xml changes.
            'app.is_testing' => false,
            'app.env' => 'production',
        ]);

        LegalDocument::create(['type' => 'privacy', 'content' => 'Ours']);

        $this->assertSame(app_url('/privacy'), policy_url('privacy'));
        $this->assertStringStartsWith('https://app.', policy_url('privacy'));
    }

    // ---------------------------------------------------------------- cost

    /**
     * index() is read up to eight times on a guest ticket page, and every read
     * reaches the cache store. The suite runs on the array store where that is
     * free, so this pins the cost against the database store this project's .env
     * actually configures - there, each read is a SQL query. Remove the memo and
     * the count goes from 1 to 8.
     */
    public function test_the_index_is_resolved_once_per_request(): void
    {
        config(['cache.default' => 'database']);
        Cache::store('database')->clear();

        LegalDocument::create(['type' => 'privacy', 'content' => '## Our own policy']);
        LegalDocument::flush();

        $reads = 0;
        Event::listen(QueryExecuted::class, function (QueryExecuted $query) use (&$reads) {
            // Reads only: rememberForever's first miss also writes, and that
            // write is not what the memo is about.
            if (str_starts_with($query->sql, 'select') && str_contains($query->sql, 'cache')) {
                $reads++;
            }
        });

        // Eight policy_url() calls, the ceiling a guest ticket page reaches.
        for ($i = 0; $i < 4; $i++) {
            policy_url('privacy');
            policy_url('cookies');
        }

        $this->assertSame(1, $reads, 'index() should reach the cache store once per request, not once per call');
    }

    public function test_the_admin_page_is_reachable_and_lists_every_document(): void
    {
        $this->adminActing();

        $this->get(route('admin.legal'))->assertOk()
            ->assertSee('id="privacy"', false)
            ->assertSee('id="terms"', false)
            ->assertSee('id="cookies"', false)
            ->assertSee('class="html-editor', false);
    }

    /**
     * The Legal link lives in the System dropdown, so the System tab has to read as active
     * while you are on the page it contains. $systemActive in _navigation.blade.php is a
     * literal list of section keys, and 'legal' was simply never added to it.
     */
    public function test_the_admin_page_marks_the_system_tab_active(): void
    {
        $this->adminActing();

        $content = $this->get(route('admin.legal'))->assertOk()->getContent();

        // The System <button>'s own class attribute. [^>] is load-bearing: it cannot cross the
        // tag boundary, so this cannot accidentally match the active styling on a dropdown ITEM
        // further down - which is what made the first version of this test pass either way.
        $matched = preg_match(
            '/openDropdown === \'system\' \? null : \'system\'"[^>]*class="([^"]*)"/',
            $content,
            $m
        );

        $this->assertSame(1, $matched, 'Could not find the System tab button in the admin nav');
        $this->assertStringContainsString(
            'border-[var(--brand-blue)]',
            $m[1],
            'The System tab should be styled active on /admin/legal'
        );
    }
}
