<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use App\Models\BlogPost;
use App\Utils\LegacyRedirects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * HomeController::landing() is the single-segment catch-all at the bottom of routes/web.php. It
 * used to redirect every miss to /dashboard, which needs auth and so forwarded to /login - a URL
 * robots.txt disallows. Nothing ever 404'd, so two classes of still-ranking URL dead-ended there:
 * the 187 blog posts left behind when the blog moved to blog.{domain}, and the old WordPress
 * marketing pages in LegacyRedirects.
 *
 * These drive the controller method directly rather than through the HTTP kernel, because the
 * catch-all is unreachable in the suite: app.is_testing swaps the domain-scoped tenant routes for
 * path-based ones, so '/{subdomain}' is registered ahead of '/{slug?}' and claims every
 * single-segment path before the catch-all sees it. Asserting via $this->get() here would pin the
 * tenant route's behaviour and pass no matter what this method does.
 */
class LegacyUrlRedirectTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function landing(?string $slug)
    {
        return app(HomeController::class)->landing($slug);
    }

    private function publishedPost(string $slug, bool $published = true): BlogPost
    {
        return BlogPost::create([
            'title' => 'Moved Post',
            'slug' => $slug,
            'content' => 'Body copy.',
            'is_published' => $published,
            // published() checks published_at <= now() as well as the flag.
            'published_at' => $published ? now()->subDay() : null,
        ]);
    }

    public function test_unknown_slug_is_404(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->landing('definitely-not-a-real-page-xyz');
    }

    public function test_published_blog_slug_redirects_to_the_blog_host(): void
    {
        $post = $this->publishedPost('moved-post');

        $response = $this->landing($post->slug);

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(blog_url('/moved-post'), $response->getTargetUrl());
    }

    public function test_unpublished_blog_slug_is_404(): void
    {
        // Scoped with the same published() scope the sitemap uses, so the set that redirects is
        // exactly the set that is advertised - a draft must not redirect.
        $this->publishedPost('draft-post', published: false);

        $this->expectException(NotFoundHttpException::class);

        $this->landing('draft-post');
    }

    public function test_blog_index_redirects_to_the_blog_host(): void
    {
        $response = $this->landing('blog');

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(blog_url(), $response->getTargetUrl());
    }

    public function test_legacy_marketing_urls_redirect(): void
    {
        foreach (['events-roles', 'who-we-help', 'help-center'] as $slug) {
            $target = LegacyRedirects::targetFor($slug);
            $this->assertNotNull($target, "$slug should be a known legacy URL");

            $response = $this->landing($slug);

            $this->assertSame(301, $response->getStatusCode(), "$slug should 301");
            $this->assertSame(marketing_url($target), $response->getTargetUrl(), "$slug target");
        }
    }

    public function test_schedule_slug_still_reaches_its_guest_portal(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        $response = $this->landing($role->subdomain);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString($role->subdomain, $response->getTargetUrl());
    }

    public function test_no_slug_still_reaches_the_dashboard(): void
    {
        // app.{domain}/ relies on this: it has no route of its own and falls through to the
        // catch-all, so a bare hit must keep redirecting home rather than 404.
        $response = $this->landing(null);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('home'), $response->getTargetUrl());
    }

    public function test_the_map_tolerates_slashes_and_unknown_slugs(): void
    {
        $this->assertSame('/use-cases', LegacyRedirects::targetFor('/who-we-help/'));
        $this->assertNull(LegacyRedirects::targetFor('not-legacy'));
        $this->assertNull(LegacyRedirects::targetFor(null));
    }

    public function test_tickets_is_deliberately_not_a_legacy_redirect(): void
    {
        // '/tickets' is a legacy marketing URL that still ranks, but it collides with the
        // authenticated "my tickets" route, which is registered first and wins. Reclaiming it would
        // need a host-scoped route that 301s a signed-in visitor away from their own tickets -
        // cached by the browser indefinitely - for a page averaging position 23. Pinned so nobody
        // adds it back without meeting that argument.
        $this->assertNull(LegacyRedirects::targetFor('tickets'));
    }
}
