<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Federation is available to every non-nexus install, so per the repo convention the
 * docs are mirrored across both trees rather than living under one of them.
 */
class FederationDocsTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_docs_pages_render(): void
    {
        $this->get('/docs/selfhost/federation')->assertOk()->assertSee('Federation');
        $this->get('/docs/saas/federation')->assertOk()->assertSee('Federation');
    }

    /**
     * Asserted against the index itself rather than the rendered page, which is
     * host-dependent and cached.
     */
    public function test_both_pages_are_in_the_docs_search_index(): void
    {
        $controller = new \App\Http\Controllers\MarketingController;
        $method = new \ReflectionMethod($controller, 'getDocSearchIndex');
        $method->setAccessible(true);
        $urls = array_column($method->invoke($controller), 'url');

        $this->assertNotEmpty(array_filter($urls, fn ($u) => str_contains($u, '/docs/selfhost/federation')));
        $this->assertNotEmpty(array_filter($urls, fn ($u) => str_contains($u, '/docs/saas/federation')));
    }

    /**
     * The sitemap response is cached and host-dependent, so assert on the source the
     * repo convention actually asks you to update.
     */
    public function test_both_pages_are_in_the_sitemap_source(): void
    {
        $sitemap = file_get_contents(resource_path('views/sitemap.blade.php'));

        $this->assertStringContainsString('/docs/selfhost/federation', $sitemap);
        $this->assertStringContainsString('/docs/saas/federation', $sitemap);
    }

    public function test_the_saas_page_no_longer_promises_federation_as_coming_soon(): void
    {
        $this->get('/saas')
            ->assertOk()
            ->assertSee('Federation')
            ->assertDontSee('Federation</span> is on the way', false)
            ->assertDontSee('Coming Soon');
    }
}
