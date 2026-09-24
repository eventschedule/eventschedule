<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\ForcesEnvironment;
use Tests\TestCase;

/**
 * The hosted blog lives on a host of its own, blog.{domain}, where a post's path is its bare slug.
 *
 * The marketing layout used to infer its BreadcrumbList from the path. On the blog host that meant a
 * post never reached the blog branch, and a post slugged for-* or *-alternative took a marketing
 * branch instead, whose url('/use-cases') was built on the BLOG host: 144 of 213 live posts put
 * https://blog.eventschedule.com/use-cases, a 404, into their structured data.
 *
 * BlogSeoTest cannot see that, because in the test env the blog is registered path-based at /blog:
 * routes/web.php registers the blog host only for `hosted && ! is_testing` outside local. So the
 * hosted half boots the app as the hosted install, the RouteLoadTest::test_hosted_gp_routes_load()
 * way, and the path-based half is checked alongside it so the two registrations cannot drift.
 */
class HostedBlogBreadcrumbTest extends TestCase
{
    use ForcesEnvironment;
    use RefreshDatabase;

    /** slug => title. One of each shape that used to be misrouted, and a control. */
    private const POSTS = [
        'for-solo-artists' => 'Booking Tips for Solo Artists',
        'best-eventbrite-alternative' => 'The Best Eventbrite Alternative for Small Venues',
        'a-plain-post' => 'A Plain Post About Calendars',
    ];

    public function test_blog_pages_on_the_blog_host_crumb_home_blog_post(): void
    {
        // Roll back RefreshDatabase's transaction to release its locks before the app is rebuilt.
        $this->app['db']->connection()->rollBack();

        $this->forceEnv('IS_HOSTED', 'true');
        $this->forceEnv('APP_TESTING', 'false');
        $this->refreshApplication();
        $this->app['db']->connection()->beginTransaction();

        try {
            $this->assertTrue(config('app.hosted'));
            $this->assertFalse(config('app.is_testing'), 'fixture: the blog host is registered only when not testing');

            $blogHost = 'https://blog.'._base_domain();
            $this->assertSame($blogHost, blog_url(), 'fixture: blog_url() names the blog host');

            // Before anything renders: these URLs must reach the BLOG routes. Without the refresh the
            // blog host is not registered and the /{subdomain} catch-all answers instead, so the
            // assertions below would pass or fail for the wrong reason.
            $routes = app('router')->getRoutes();
            foreach (array_keys(self::POSTS) as $slug) {
                $route = $routes->match(Request::create($blogHost.'/'.$slug));
                $this->assertSame('blog.show', $route->getName(), "{$slug} must reach the blog route");
                $this->assertSame($slug, $route->parameter('slug'));
            }
            $this->assertSame('blog.index', $routes->match(Request::create($blogHost.'/'))->getName());

            $this->assertBlogCrumbs($blogHost, $blogHost);

            // The stored slug is the canonical, not the requested spelling: slugs match
            // case-insensitively, so a case variant rendered the post and claimed to be it.
            $variant = $this->get($blogHost.'/FOR-SOLO-ARTISTS')->assertOk()->getContent();
            $this->assertStringContainsString('<link rel="canonical" href="'.$blogHost.'/for-solo-artists">', $variant);
        } finally {
            // refreshApplication() swapped the database manager, so RefreshDatabase's teardown cannot
            // see the transaction opened above. Left open it holds metadata locks and the next test
            // class's migrate:fresh hangs - see RouteLoadTest::test_hosted_gp_routes_load().
            $connection = $this->app['db']->connection();

            if ($connection->getPdo() && $connection->getPdo()->inTransaction()) {
                $connection->rollBack();
            }

            $connection->disconnect();
        }
    }

    /** The path-based registration (selfhost, local and the test env) answers the same way. */
    public function test_blog_pages_under_the_blog_path_crumb_home_blog_post(): void
    {
        $this->assertSame(url('/blog'), blog_url(), 'fixture: the blog is path-based here');

        $this->assertBlogCrumbs(url('/blog'), url('/blog'));
    }

    private function assertBlogCrumbs(string $blogUrl, string $requestBase): void
    {
        $home = config('app.url');

        foreach (self::POSTS as $slug => $title) {
            BlogPost::create([
                'title' => $title,
                'slug' => $slug,
                'content' => '<p>Body copy.</p>',
                'is_published' => true,
                'published_at' => now()->subWeek(),
            ]);

            $html = $this->get($requestBase.'/'.$slug)->assertOk()->getContent();
            $crumbs = $this->breadcrumbs($html);

            $this->assertSame(['Home', 'Blog', $title], array_column($crumbs, 'name'), "{$slug}: crumb names");
            $this->assertSame([$home, $blogUrl, $blogUrl.'/'.$slug], array_column($crumbs, 'item'), "{$slug}: crumb URLs");
            $this->assertSame(blog_url('/'.$slug), $blogUrl.'/'.$slug);
            $this->assertStringContainsString('<link rel="canonical" href="'.blog_url('/'.$slug).'">', $html);
        }

        $index = $this->get($requestBase)->assertOk()->getContent();
        $crumbs = $this->breadcrumbs($index);

        $this->assertSame(['Home', 'Blog'], array_column($crumbs, 'name'), 'the index: crumb names');
        $this->assertSame([$home, $blogUrl], array_column($crumbs, 'item'), 'the index: crumb URLs');

        // The blog's own card, not the homepage's: on the blog host the index's path is "/".
        $this->assertMatchesRegularExpression('~<meta property="og:image" content="[^"]+/images/social/blog\.jpg">~', $index);
    }

    /**
     * The BreadcrumbList's items, in order.
     *
     * @return array<int, array<string, mixed>>
     */
    private function breadcrumbs(string $html): array
    {
        preg_match_all('~<script type="application/ld\+json"[^>]*>(.*?)</script>~s', $html, $matches);

        $lists = array_values(array_filter(
            array_map(fn (string $raw) => json_decode($raw, true), $matches[1]),
            fn ($block) => is_array($block) && ($block['@type'] ?? null) === 'BreadcrumbList'
        ));

        $this->assertCount(1, $lists, 'expected exactly one BreadcrumbList');

        return $lists[0]['itemListElement'];
    }
}
