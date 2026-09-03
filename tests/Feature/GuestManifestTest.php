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

    /**
     * FAILS before the change: display_override was absent, so Chrome resolved the effective
     * display mode from display ('standalone') and happily minted a WebAPK.
     *
     * A WebAPK is the whole problem: Android hands it every link tapped on that host from another
     * app, and shows its launch splash before the page. Chrome resolves the effective display mode
     * from the first supported display_override entry BEFORE reading display, and 'browser' is not
     * one of the installable modes, so no home-screen app is ever created for a schedule.
     */
    public function test_a_schedule_is_not_installable_as_an_app(): void
    {
        $role = $this->role(['profile_image_url' => 'profile_bluenote.png']);

        $manifest = $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertOk()->json();

        $this->assertSame(['browser'], $manifest['display_override']);
    }

    /**
     * The other half of that: display stays installable so Safari, which ignores display_override,
     * still treats an iOS Add to Home Screen as a web app. iOS only exposes the Push API inside
     * one, and the ticket confirmation page offers that opt-in. iOS never showed the splash
     * anyway - it does no link capturing - so there is nothing to gain by breaking it.
     */
    public function test_the_schedule_manifest_keeps_ios_able_to_install_it(): void
    {
        $role = $this->role();

        $manifest = $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertOk()->json();

        $this->assertSame('standalone', $manifest['display']);
    }

    /**
     * Pins the asymmetry so the two manifests cannot drift into each other. Installing the ADMIN
     * portal is the owner's own choice about our app, and stays available.
     */
    public function test_the_platform_manifest_is_still_installable(): void
    {
        $manifest = $this->get('/manifest.webmanifest')->assertOk()->json();

        $this->assertSame('standalone', $manifest['display']);
        $this->assertArrayNotHasKey('display_override', $manifest);
    }

    /**
     * FAILS before the change: the meta tag fell back to #4E81FA while the manifest deliberately
     * omitted theme_color in exactly that case, so a schedule that had cleared its accent got OUR
     * brand blue in the Android address bar - on the one surface the manifest split exists to
     * keep unbranded.
     *
     * accent_color is NOT NULL with a '#007bff' default, so a cleared accent is an empty string.
     */
    public function test_a_schedule_without_an_accent_colour_tints_nothing(): void
    {
        $role = $this->role(['accent_color' => '']);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();
        $manifest = $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertOk()->json();

        $this->assertArrayNotHasKey('theme_color', $manifest);
        // Only the tag, not the hex: show-guest.blade.php legitimately falls back to #4E81FA for
        // BUTTON colour when the accent is cleared. A default button colour is UI; a tinted
        // address bar reads as the site's identity. See Role::manifestThemeColor().
        $this->assertStringNotContainsString('name="theme-color"', $content);
    }

    /** The positive half: whatever the manifest says, the tag says, and it is the schedule's. */
    public function test_the_theme_colour_meta_matches_the_manifest(): void
    {
        $role = $this->role(['accent_color' => '#123456']);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();
        $manifest = $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertOk()->json();

        $this->assertSame('#123456', $manifest['theme_color']);
        $this->assertStringContainsString('<meta name="theme-color" content="#123456">', $content);
    }

    /**
     * FAILS before the change: background_color was a hardcoded '#ffffff'.
     *
     * This is the colour Android paints the launch splash of a home-screen app installed before
     * v1.0.124, with the icon centred on it. White is what made the owner who reported this
     * describe it as a blank page that had not loaded. It cannot stop those installs existing -
     * only removing the icon does that - but it can stop them reading as a broken page, and the
     * schedule's own accent is the only colour here that is honestly theirs.
     */
    public function test_the_splash_background_is_the_schedules_own_colour(): void
    {
        $role = $this->role(['accent_color' => '#123456', 'profile_image_url' => 'profile_bluenote.png']);

        $manifest = $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertOk()->json();

        $this->assertSame('#123456', $manifest['background_color']);
    }

    /**
     * A guard, not a proof: passes before and after. A cleared accent reaches for nothing rather
     * than a default, for the same reason theme_color is omitted outright in that case. Our brand
     * blue is the one answer this whole manifest split exists to keep off someone else's audience.
     *
     * Carries a logo only so the fixture is unambiguous; since icons became unconditional there
     * is no longer a separate no-logo branch for it to fall into.
     */
    public function test_a_schedule_without_an_accent_colour_keeps_a_white_splash(): void
    {
        $role = $this->role(['accent_color' => '', 'profile_image_url' => 'profile_bluenote.png']);

        $manifest = $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertOk()->json();

        $this->assertSame('#ffffff', $manifest['background_color']);
    }

    /**
     * FAILS before the change: icons was omitted for a logo-less schedule, and background_color
     * was pinned to white for exactly that case.
     *
     * The case the whole splash bug lives in. Omitting icons reads like "no icon of ours", but an
     * Android WebAPK re-brands itself by re-reading this document - so an absent key leaves an
     * install minted while the static manifest was live holding OUR mark with nothing to replace
     * it with. That is why a schedule owner's audience still saw our logo full screen months
     * after the static file was deleted, and why the earlier attempt to fix this by colouring
     * background_color could not: it was gated on having a logo, which is precisely the branch
     * where our mark had already been replaced. Handing the install a different icon is the only
     * lever that works.
     *
     * The neutral mark carries no wordmark and none of the brand blue, so the accent behind it is
     * safe to use here - the reason background_color was ever gated was our mark standing on it.
     */
    public function test_a_schedule_with_no_logo_advertises_a_neutral_mark_not_ours(): void
    {
        $role = $this->role(['accent_color' => '#123456', 'profile_image_url' => null]);

        $manifest = $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertOk()->json();

        $this->assertSame('/images/schedule-icon.png', $manifest['icons'][0]['src']);
        $this->assertNotSame(self::PLATFORM_ICON, $manifest['icons'][0]['src']);
        $this->assertSame('#123456', $manifest['background_color']);
        $this->assertSame('#123456', $manifest['theme_color']);
    }

    /**
     * A guard, not a proof: passes before and after. This meta is the other route to a standalone
     * home-screen app, and it would sidestep the display_override above, so it must never appear.
     */
    public function test_a_guest_page_never_declares_itself_web_app_capable(): void
    {
        $role = $this->role();

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringNotContainsString('mobile-web-app-capable', $content);
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
     * Every schedule advertises an icon, and it is never the platform's.
     *
     * The pair to the test above: that one pins WHICH mark a logo-less schedule gets, this one
     * pins that carrying one does not buy back installability. The icon key was once hoped to be
     * the install gate - "no icons, so no install" - which was never true, since Chromium treats
     * sizes "any" as satisfying every size requirement. display_override: ['browser'] is the only
     * thing holding that line, so these two must stay independent: a future change that makes the
     * icon richer must not quietly make the page installable again.
     */
    public function test_advertising_an_icon_does_not_make_the_page_installable(): void
    {
        $role = $this->role(['profile_image_url' => null]);

        $manifest = $this->get('/'.$role->subdomain.'/manifest.webmanifest')->assertOk()->json();

        $this->assertArrayHasKey('icons', $manifest);
        $this->assertStringNotContainsString('logo', $manifest['icons'][0]['src']);
        $this->assertSame(['browser'], $manifest['display_override']);
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
