<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Concerns\ForcesEnvironment;
use Tests\TestCase;

/**
 * The blog is the marketing site's, so it exists on the nexus only.
 *
 * It used to follow `hosted`, which is also true of a selfhosted SaaS: that install got a public
 * blog.{its domain}, an admin Blog page, and the two daily generators, which published Event
 * Schedule's own SEO posts there on the operator's AI key. A plain selfhost served an empty /blog
 * with no screen to write a post.
 *
 * The marketing layout still renders off the nexus (the legal pages and the 404), and it linked
 * route('blog.feed'), so un-registering the blog without guarding that link would have made those
 * pages throw. The plain-selfhost test renders one of them for that reason.
 */
class BlogNexusOnlyTest extends TestCase
{
    use ForcesEnvironment;
    use RefreshDatabase;

    private const BLOG_ROUTES = ['blog.index', 'blog.feed', 'blog.show', 'blog.admin.index', 'blog.store'];

    public function test_a_plain_selfhost_has_no_blog(): void
    {
        $this->bootAs(hosted: 'false');

        try {
            foreach (self::BLOG_ROUTES as $name) {
                $this->assertFalse(Route::has($name), "{$name} is registered on a plain selfhost");
            }

            // Rendered through the marketing layout, which used to call route('blog.feed').
            $this->get('/privacy')->assertOk()->assertDontSee('application/rss+xml', false);

            $admin = User::factory()->create(['email_verified_at' => now()]);
            $admin->forceFill(['is_admin' => true])->save();

            $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
                ->actingAs($admin)
                ->get('/admin/dashboard')
                ->assertOk()
                ->assertDontSee('/admin/blog', false);
        } finally {
            $this->closeTransaction();
        }
    }

    public function test_a_selfhosted_saas_has_no_blog(): void
    {
        // APP_TESTING=false: the blog host is registered only outside the test env, so without it
        // this would pass whatever the nexus gate said.
        $this->bootAs(hosted: 'true', testing: 'false');

        try {
            $this->assertTrue(config('app.hosted'));
            $this->assertFalse(config('app.is_nexus'));

            foreach (self::BLOG_ROUTES as $name) {
                $this->assertFalse(Route::has($name), "{$name} is registered on a selfhosted SaaS");
            }

            $route = app('router')->getRoutes()->match(Request::create('https://blog.'._base_domain().'/a-post'));
            $this->assertNotSame('blog.show', $route->getName());
        } finally {
            $this->closeTransaction();
        }
    }

    public function test_the_generators_do_nothing_off_the_nexus(): void
    {
        config(['app.hosted' => true, 'app.is_nexus' => false]);

        $this->artisan('app:generate-daily-blog-post')->assertExitCode(0);
        $this->artisan('app:generate-sub-audience-blog')->assertExitCode(0);

        $this->assertSame(0, BlogPost::count());
    }

    private function bootAs(string $hosted, string $testing = 'true'): void
    {
        // Roll back RefreshDatabase's transaction to release its locks before the app is rebuilt.
        $this->app['db']->connection()->rollBack();

        $this->forceEnv('IS_HOSTED', $hosted);
        $this->forceEnv('IS_NEXUS', 'false');
        $this->forceEnv('APP_TESTING', $testing);
        $this->refreshApplication();
        $this->app['db']->connection()->beginTransaction();
    }

    /**
     * refreshApplication() swapped the database manager, so RefreshDatabase's teardown cannot see
     * the transaction bootAs() opened. Left open it holds metadata locks and the next test class's
     * migrate:fresh hangs - see RouteLoadTest::test_hosted_gp_routes_load().
     */
    private function closeTransaction(): void
    {
        $connection = $this->app['db']->connection();

        if ($connection->getPdo() && $connection->getPdo()->inTransaction()) {
            $connection->rollBack();
        }

        $connection->disconnect();
    }
}
