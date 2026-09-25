<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * No schedule may be named after a route at the top of the path space.
 *
 * Selfhost serves a schedule at /{subdomain}/..., in the same path space as the app's own routes,
 * so a schedule named after one lost pages to it: /new/{type} took /new/{slug} - the sub-schedules
 * and event short links of a schedule called "new" - and /feedback/{event_id}/{secret} every event
 * of one called "feedback". Role::RESERVED_SUBDOMAINS keeps a new schedule off those names. This
 * reads the route tables of both kinds of install and fails when a route arrives that the list does
 * not reserve.
 */
class ReservedSubdomainRoutesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * Route words no schedule can be named in the first place: cleanSubdomain(), generateSubdomain()
     * and the admin rename give only [a-z0-9] words joined by hyphens, three characters or more.
     */
    private const CANNOT_BE_A_SUBDOMAIN = [
        // An underscore.
        '_debugbar',
        '_dusk',
        'payment_url',
        'release_tickets',
        'test_database',
        'translate_data',
        'validate_address',
        // A dot.
        'manifest.webmanifest',
        'robots.txt',
        'sitemap.xml',
        'sitemap.xml.gz',
        // Two letters.
        'ai',
        'nl',
        'up',
        'wp',
    ];

    /**
     * The literal first segment of every route with no host of its own and outside a schedule's
     * address: the app's own path space, which selfhost shares with its schedules.
     *
     * @param  iterable<int, array{0: string, 1: string}>  $routes  [domain, uri] pairs
     * @return array<int, string>
     */
    private function topLevelWords(iterable $routes): array
    {
        $words = [];

        foreach ($routes as [$domain, $uri]) {
            if ($domain !== '' || str_starts_with($uri, '{subdomain}')) {
                continue;
            }

            $segment = explode('/', ltrim($uri, '/'))[0];

            if ($segment !== '' && ! str_contains($segment, '{')) {
                $words[strtolower($segment)] = true;
            }
        }

        return array_keys($words);
    }

    /**
     * An install's route table as `route:list` prints it, booted with $env. A child process,
     * because only a fresh boot registers the other half of routes/web.php - see
     * RouteLoadTest::test_the_schedule_takedown_has_an_undo_on_selfhost().
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function routeTable(array $env): array
    {
        $result = Process::path(base_path())->env($env)->run('php artisan route:list --json');

        $this->assertTrue($result->successful(), 'route:list failed: '.$result->errorOutput());

        return array_map(
            fn (array $route) => [(string) ($route['domain'] ?? ''), (string) $route['uri']],
            json_decode($result->output(), true) ?: []
        );
    }

    public function test_every_top_level_route_word_is_reserved(): void
    {
        $tables = [
            // The test environment's own: the selfhost path routes and every marketing page.
            'testing' => collect(app('router')->getRoutes()->getRoutes())
                ->map(fn ($route) => [(string) $route->getDomain(), $route->uri()])
                ->all(),
            'hosted' => $this->routeTable(['IS_HOSTED' => 'true', 'IS_NEXUS' => 'true', 'APP_TESTING' => 'false']),
            'selfhost' => $this->routeTable(['IS_HOSTED' => 'false', 'IS_NEXUS' => 'false', 'APP_TESTING' => 'false']),
        ];

        foreach ($tables as $install => $routes) {
            $words = $this->topLevelWords($routes);

            $this->assertContains('login', $words, "fixture: the {$install} table has the app's own routes");

            $this->assertSame(
                [],
                array_values(array_diff($words, Role::RESERVED_SUBDOMAINS, self::CANNOT_BE_A_SUBDOMAIN)),
                "a {$install} route owns a first path segment that Role::RESERVED_SUBDOMAINS does not reserve"
            );
        }

        // The allowlist can only hold what no schedule could be named anyway.
        foreach (self::CANNOT_BE_A_SUBDOMAIN as $word) {
            $this->assertFalse(
                strlen($word) >= 3 && preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $word) === 1,
                "{$word} could be a subdomain, so it belongs in Role::RESERVED_SUBDOMAINS"
            );
        }
    }

    /** A new schedule called after one is not handed it. */
    public function test_a_new_schedule_is_not_named_after_a_route(): void
    {
        foreach (['New' => 'new', 'Feedback' => 'feedback', 'Newsletters' => 'newsletters', 'Update Password' => 'update-password', 'Webhooks' => 'webhooks'] as $name => $word) {
            $this->assertNotSame($word, Role::generateSubdomain($name), $name);
        }
    }

    /**
     * A schedule that already holds a name the list now reserves keeps it. new_subdomain is posted
     * on every settings save, so only a CHANGE may meet the list: RoleController::update() cleans a
     * changed value only, and AdminScheduleDetailsRequest checks a changed value only.
     */
    public function test_a_schedule_already_named_after_a_route_keeps_saving(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['subdomain' => 'feedback', 'name' => 'Feedback']);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => 'feedback']), [
            'name' => 'Feedback Live',
            'email' => $role->email,
            'new_subdomain' => 'feedback',
            'timezone' => $role->timezone,
        ])->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertSame('Feedback Live', $role->name, 'the save did not go through');
        $this->assertSame('feedback', $role->subdomain);

        $this->actingAsAdmin()->put(route('admin.schedules.update_details', ['role' => $role->encodeId()]), [
            'name' => 'Feedback Again',
            'new_subdomain' => 'feedback',
            'email' => $role->email,
        ])->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertSame('Feedback Again', $role->name, 'the admin save did not go through');
        $this->assertSame('feedback', $role->subdomain);
    }

    /** A change onto one is not kept: the owner's is rewritten (cleanSubdomain()), an admin's refused. */
    public function test_a_schedule_cannot_move_onto_a_route_name(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['subdomain' => 'harbor-hall']);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => 'harbor-hall']), [
            'name' => $role->name,
            'email' => $role->email,
            'new_subdomain' => 'newsletters',
            'timezone' => $role->timezone,
        ])->assertSessionHasNoErrors();

        $this->assertNotSame('newsletters', $role->fresh()->subdomain);

        $role = $this->createRole($owner, 'venue', ['subdomain' => 'harbor-hall-two']);

        $this->actingAsAdmin()->put(route('admin.schedules.update_details', ['role' => $role->encodeId()]), [
            'name' => $role->name,
            'new_subdomain' => 'update-password',
            'email' => $role->email,
        ])->assertSessionHasErrors(['new_subdomain' => __('messages.subdomain_reserved')]);

        $this->assertSame('harbor-hall-two', $role->fresh()->subdomain);
    }

    /** EnsureUserIsAdmin also wants a password confirmed this session. */
    private function actingAsAdmin(): self
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->createOwner(admin: true));
    }
}
