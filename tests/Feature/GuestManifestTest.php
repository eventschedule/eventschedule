<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The web app manifest a guest page advertises has to be the schedule's, never the platform's.
 *
 * layouts/app.blade.php is the shell for the guest portal as well as the admin portal, and it used
 * to link one static public/manifest.webmanifest naming "Event Schedule" with our logo. That made
 * every schedule's site installable as an app branded as ours, and Android shows an installed
 * app's icon as its launch splash - so a visitor who had added the schedule to their home screen
 * saw OUR logo for a couple of seconds on every link they opened. See AppController::manifest().
 *
 * Routes are registered at boot from the environment's IS_HOSTED, so these run path-based
 * (app.is_testing) whatever the assertions below say about deployment mode.
 */
class GuestManifestTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The platform icon. Its presence anywhere on a tenant surface is the bug. */
    private const PLATFORM_ICON = '/images/logo.png';

    private function role(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', $attrs);
    }

    public function test_a_guest_page_links_its_own_schedules_manifest(): void
    {
        $role = $this->role(['name' => 'Blue Note']);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString('/'.$role->subdomain.'/manifest.webmanifest', $content);
        // The platform manifest is what used to render here.
        $this->assertStringNotContainsString('rel="manifest" href="'.url('/manifest.webmanifest').'"', $content);
    }

    public function test_the_schedule_manifest_carries_the_schedules_identity_not_ours(): void
    {
        $role = $this->role([
            'name' => 'Blue Note',
            'profile_image_url' => 'profile_bluenote.png',
            'accent_color' => '#123456',
        ]);

        $manifest = $this->get('/'.$role->subdomain.'/manifest.webmanifest')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->json();

        $this->assertSame('Blue Note', $manifest['name']);
        $this->assertSame('#123456', $manifest['theme_color']);
        $this->assertStringNotContainsString('Event Schedule', json_encode($manifest));
        $this->assertStringNotContainsString(self::PLATFORM_ICON, json_encode($manifest));
        $this->assertStringContainsString('profile_bluenote.png', $manifest['icons'][0]['src']);
    }

    /**
     * Relative, so one document is correct on a subdomain, on a custom domain and on a path-routed
     * selfhost install. ResolveCustomDomain rewrites text/html and application/json bodies but not
     * application/manifest+json, so an absolute URL here would point a custom domain's installed
     * app back at the .eventschedule.com host.
     */
    public function test_the_schedule_manifest_scopes_itself_relatively(): void
    {
        $role = $this->role();

        $manifest = $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertOk()->json();

        $this->assertSame('./', $manifest['start_url']);
        $this->assertSame('./', $manifest['scope']);
    }

    /**
     * No logo, no icons - rather than falling back to ours. A browser may then decline to treat
     * the page as installable, which is the pre-manifest behaviour and the outcome that was asked
     * for; what it must never do is offer an install badged with the platform's logo.
     */
    public function test_a_schedule_without_a_logo_advertises_no_icons(): void
    {
        $role = $this->role(['profile_image_url' => null]);

        $manifest = $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertOk()->json();

        $this->assertArrayNotHasKey('icons', $manifest);
    }

    /** Not plan-gated, unlike the custom favicon: a free schedule's audience is owed this too. */
    public function test_a_free_schedule_still_gets_its_own_manifest(): void
    {
        config(['app.hosted' => true]);

        $role = $this->role([
            'name' => 'Free Venue',
            'profile_image_url' => 'profile_free.png',
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);

        $manifest = $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertOk()->json();

        $this->assertSame('Free Venue', $manifest['name']);
        $this->assertStringContainsString('profile_free.png', $manifest['icons'][0]['src']);
    }

    public function test_an_unknown_subdomain_is_not_served_the_platform_manifest(): void
    {
        $this->get('/nosuchschedule/manifest.webmanifest')->assertNotFound();
    }

    /**
     * An unclaimed placeholder has no guest page - viewGuest redirects it away and the sitemap
     * filters it out - but the manifest route did not filter at all, so it answered 200 with the
     * schedule's name and logo URL, cached publicly for an hour and enumerable by subdomain.
     */
    public function test_an_unclaimed_schedule_is_not_served_a_manifest(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Placeholder Venue']);
        \App\Models\Role::where('id', $role->id)->update(['user_id' => null, 'email_verified_at' => null]);

        $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertNotFound();
    }

    public function test_a_deleted_schedule_is_not_served_a_manifest(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Gone Venue']);
        \App\Models\Role::where('id', $role->id)->update(['is_deleted' => true]);

        $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertNotFound();
    }

    /** The apex keeps ours: the admin portal genuinely is the Event Schedule app. */
    public function test_the_platform_manifest_is_still_served_at_the_root(): void
    {
        config(['app.is_nexus' => true]);

        $manifest = $this->get('/manifest.webmanifest')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->json();

        $this->assertSame('Event Schedule', $manifest['name']);
        $this->assertSame(self::PLATFORM_ICON, $manifest['icons'][1]['src']);
    }

    /** An operator's install is theirs to brand, the same as the rest of the app. */
    public function test_the_platform_manifest_off_the_nexus_uses_the_operators_name(): void
    {
        config(['app.is_nexus' => false, 'app.name' => 'Acme Events']);

        $manifest = $this->get('/manifest.webmanifest')->assertOk()->json();

        $this->assertSame('Acme Events', $manifest['name']);
        $this->assertStringNotContainsString(self::PLATFORM_ICON, json_encode($manifest));
    }

    /**
     * A manifest fetch is the browser's, not a visitor's. It never reached Laravel while it was a
     * static file, so routing it through the web group would newly count it as a marketing visit
     * and attach a session cookie that stops the CDN caching it.
     */
    public function test_the_manifest_sets_no_session_cookie(): void
    {
        $role = $this->role();

        $response = $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertOk();

        $this->assertEmpty($response->headers->getCookies());
    }
}
